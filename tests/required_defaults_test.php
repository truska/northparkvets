<?php
// Integration test: connection-local temporary copies only; no real table writes.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../migrations/004_required_defaults.php';
require dirname(__DIR__, 2) . '/private/db.php';
function defaultsCheck004(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
    echo "PASS: $message\n";
}
$db = DB::connection();
try {
    $originalSchema = $db->query('SELECT TABLE_NAME,COLUMN_NAME,COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,ORDINAL_POSITION')->fetch_all(MYSQLI_ASSOC);
    $references = [];
    foreach (['npe_destination_status' => 3, 'npe_vet_type' => 2, 'npe_snag_status' => 1] as $table => $id) {
        $references[$table] = $db->query("SELECT * FROM `$table` WHERE id=$id")->fetch_assoc();
        defaultsCheck004((bool)$references[$table], "Reference fixture exists: $table");
    }
    foreach (array_unique(array_merge(array_keys(requiredDefaults004()), ['npe_vet_type'])) as $table) {
        $ddl = $db->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
        $db->query(preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $ddl));
    }
    foreach ($references as $table => $row) {
        $stmt = $db->prepare("INSERT INTO `$table` (`" . implode('`,`', array_keys($row)) . '`) VALUES (' . implode(',', array_fill(0, count($row), '?')) . ')');
        $values = array_values($row);
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        $stmt->execute();
        $stmt->close();
    }
    $sql = file_get_contents(__DIR__ . '/../migrations/004_required_defaults.sql');
    $sql = preg_replace('/^--.*\R/m', '', $sql);
    defaultsCheck004(trim($sql) === implode("\n\n", requiredDefaultsSql004()), 'SQL file matches runner exactly');
    requiredDefaultsPreflight004($db);
    requiredDefaultsApply004($db);
    requiredDefaultsApply004($db);
    defaultsCheck004(true, 'Migration can be rerun');
    foreach (requiredDefaults004() as $table => $fields) {
        $columns = [];
        $result = $db->query("SHOW COLUMNS FROM `$table`");
        while ($column = $result->fetch_assoc()) $columns[$column['Field']] = $column;
        foreach ($fields as $field => $value) {
            $literal = is_int($value) ? (string)$value
                : (str_contains($columns[$field]['Type'], 'text') ? "'" . $value . "'" : $value);
            defaultsCheck004($columns[$field]['Default'] === $literal, "$table.$field default verified");
        }
    }
    foreach ($references as $table => $row) {
        defaultsCheck004($row === $db->query("SELECT * FROM `$table` WHERE id=" . (int)$row['id'])->fetch_assoc(), "Existing $table row preserved");
    }
    foreach ([
        "INSERT INTO npe_depot_visits (name,depot,date,vet) VALUES ('audit',0,'2026-10-15',0)",
        "INSERT INTO npe_destination (name,code) VALUES ('audit','audit')",
        "INSERT INTO npe_destination_status (name,code) VALUES ('audit','audit')",
        "INSERT INTO npe_snags (name) VALUES ('audit')",
        "INSERT INTO npe_snag_status (name) VALUES ('audit')",
        "INSERT INTO npe_rates (name,rate,units) VALUES ('audit',1,'units')",
        "INSERT INTO gallery (record_id,form_id,name,image,date,folder_name,sort) VALUES (1,7,'audit','audit.jpg',NOW(),'audit',0)",
    ] as $statement) $db->query($statement);
    defaultsCheck004(true, 'Previously failing insert paths accept omitted optional fields');
    $db->query("ALTER TABLE npe_snag_status ALTER COLUMN code SET DEFAULT 'unexpected'");
    try {
        requiredDefaultsPreflight004($db);
        throw new LogicException('Conflicting default was accepted');
    } catch (RuntimeException $exception) {
        defaultsCheck004(str_contains($exception->getMessage(), 'Conflicting existing default'), 'Conflicting defaults block preflight');
    }
    $db->query("ALTER TABLE npe_snag_status ALTER COLUMN code SET DEFAULT 'TBC'");
    $db->query("UPDATE npe_destination_status SET code='WRONG' WHERE id=3");
    try {
        requiredDefaultsPreflight004($db);
        throw new LogicException('Wrong reference ID was accepted');
    } catch (RuntimeException $exception) {
        defaultsCheck004(str_contains($exception->getMessage(), 'missing or different'), 'Wrong reference ID meaning blocks preflight');
    }
    $afterSchema = $db->query('SELECT TABLE_NAME,COLUMN_NAME,COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,ORDINAL_POSITION')->fetch_all(MYSQLI_ASSOC);
    defaultsCheck004($originalSchema === $afterSchema, 'Real database schema unchanged by tests');
} finally {
    $db->close();
}
echo "All migration checks passed using temporary tables.\n";
