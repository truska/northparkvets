<?php
/** Explicit legacy defaults for consignment creation under strict SQL mode. */
function productInsertData(array $data): array
{
    $defaults = [
        'sku' => '', 'po' => '', 'odo' => '', 'notes' => '', 'slug' => '',
        'section' => 0, 'brand' => 0, 'manuf' => 0, 'supplier' => 0,
        'snag_text' => '', 'importbatch' => 0,
        'destination_customer' => '', 'product_type' => '', 'pallet_type' => '',
    ];
    $allowed = array_merge(array_keys($defaults), [
        'name', 'vet', 'expected_date', 'weeknum', 'expected_time', 'load_date',
        'sealed', 'ehc_ref', 'ehc_location', 'status', 'in_out', 'depot', 'account',
        'destination_country', 'pallet_count', 'tanker', 'pcloud', 'sort',
        'snag_title', 'snag_status', 'showonweb', 'archived',
    ]);
    $data = array_replace($defaults, array_intersect_key($data, array_flip($allowed)));
    foreach ($defaults as $field => $default) {
        if ($data[$field] === null || $data[$field] === '') $data[$field] = $default;
    }
    foreach (['vet', 'weeknum', 'ehc_location', 'depot', 'account', 'pallet_count',
              'load_date', 'sealed'] as $field) {
        if (array_key_exists($field, $data) && $data[$field] === '') $data[$field] = null;
    }
    foreach (['status' => 0, 'sort' => 50, 'archived' => 0, 'snag_status' => '0'] as $field => $default) {
        if (array_key_exists($field, $data) && ($data[$field] === '' || $data[$field] === null)) {
            $data[$field] = $default;
        }
    }
    if (trim((string)($data['name'] ?? '')) === '') {
        throw new InvalidArgumentException('A consignment name / PO is required.');
    }
    $date = (string)($data['expected_date'] ?? '');
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    if (!$parsed || $parsed->format('Y-m-d') !== $date) {
        throw new InvalidArgumentException('A valid expected date is required.');
    }
    if (empty($data['expected_time'])) $data['expected_time'] = '00:00:00';
    if (!isset($data['weeknum'])) $data['weeknum'] = (int)$parsed->format('W');
    return $data;
}

function insertProductRecord($connection, array $data): array
{
    $data = productInsertData($data);
    $query = 'INSERT INTO products (`' . implode('`, `', array_keys($data)) . '`)
              VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')';
    $stmt = $connection->prepare($query);
    try {
        $values = array_values($data);
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        if (!$stmt->execute()) throw new RuntimeException('Unable to save the consignment.');
        return ['status' => 'success', 'message' => 'Record added successfully',
                'recordID' => $connection->insert_id, 'query' => $query];
    } finally {
        $stmt->close();
    }
}
