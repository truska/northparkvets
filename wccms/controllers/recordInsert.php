<?php
/** Generic CMS inserts use schema defaults without sending empty numeric strings. */
function insertCmsRecord($connection, string $table, array $data): array
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/D', $table)) throw new InvalidArgumentException('Invalid record type.');
    unset($data['formnumber'], $data['submit']);
    $columns = [];
    $result = $connection->query("SHOW COLUMNS FROM `$table`");
    while ($column = $result->fetch_assoc()) $columns[$column['Field']] = $column;
    foreach ($data as $field => &$value) {
        if (!isset($columns[$field]) || !is_scalar($value) && $value !== null) {
            throw new InvalidArgumentException('Invalid form field.');
        }
        $column = $columns[$field];
        $label = ucfirst(str_replace('_', ' ', $field));
        $value = $value === null ? null : trim((string)$value);
        $numeric = preg_match('/^(tinyint|smallint|mediumint|int|bigint|decimal|float|double|bit)\b/', $column['Type']);
        $date = preg_match('/^(date|datetime|timestamp|time)\b/', $column['Type']);
        if (($value === '' || $value === null) && ($numeric || $date)) {
            if ($column['Null'] === 'YES') $value = null;
            elseif ($column['Default'] !== null) unset($data[$field]);
            // Blank display order means no priority; do not invent defaults for other required numbers.
            elseif ($field === 'sort' && $numeric) $value = '0';
            else throw new InvalidArgumentException("$label is required.");
        } elseif ($numeric && !is_numeric($value)) {
            throw new InvalidArgumentException("Enter a number for $label.");
        }
    }
    unset($value);
    foreach ($columns as $field => $column) {
        if ($column['Null'] === 'NO' && $column['Default'] === null
            && $column['Extra'] !== 'auto_increment' && !array_key_exists($field, $data)) {
            throw new InvalidArgumentException(ucfirst(str_replace('_', ' ', $field)) . ' is required.');
        }
    }
    $query = "INSERT INTO `$table` (`" . implode('`, `', array_keys($data)) . '`)
        VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')';
    $statement = $connection->prepare($query);
    try {
        $values = array_values($data);
        $statement->bind_param(str_repeat('s', count($values)), ...$values);
        $statement->execute();
        return ['status' => 'success', 'message' => 'Record added successfully',
            'recordID' => $connection->insert_id, 'query' => $query];
    } finally {
        $statement->close();
    }
}

function cmsRecordSaveError(Throwable $error): string
{
    if ($error instanceof InvalidArgumentException) return $error->getMessage();
    switch ((int)$error->getCode()) {
        case 1062: return 'A record with the same unique value (such as Code) already exists. Use a different value.';
        case 1406: return 'One of the values is too long. Shorten it and try again.';
        case 1366: case 1292: case 1265: case 1264:
            return 'A number, date or selected value is invalid. Check your entries and try again.';
        case 1048: case 1364: return 'A required value is missing. Check your entries and try again.';
        default: return 'Unable to save the record. Your entries are retained below. Please report this if it continues.';
    }
}
