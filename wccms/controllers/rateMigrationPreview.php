<?php
/** Saved-rate migration: preview is read-only; apply fills NULL fields atomically. */
function timesheetRateFields(): array
{
    return ['time_ov' => 'OV time', 'time_cso' => 'CSO time', 'travel_units' => 'Travel units',
        'travel_miles' => 'Mileage', 'certs' => 'Certificates', 'tanker_cert' => 'Tanker certificates',
        'sha_sa' => 'SHA/SA', 'courier' => 'Courier'];
}

function rateMigrationDate(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value && substr($value, 0, 4) !== '0000';
}

function collectTimesheetRateMigration(mysqli $connection, string $from = '', string $to = '', bool $lock = false): array
{
    foreach ([$from, $to] as $date) {
        if ($date !== '' && !rateMigrationDate($date)) {
            throw new InvalidArgumentException('Enter a valid date in YYYY-MM-DD format.');
        }
    }
    if ($from !== '' && $to !== '' && $from > $to) {
        throw new InvalidArgumentException('The start date must not be after the end date.');
    }
    $fields = timesheetRateFields();
    $report = ['from' => $from, 'to' => $to, 'rates' => [], 'issues' => [], 'total' => 0,
        'matched' => 0, 'outside' => 0, 'invalid' => [], 'empty' => 0, 'partial' => 0,
        'complete' => 0, 'different' => 0, 'candidates' => [], 'periods' => [0, 0, 0], 'missing' => array_fill_keys(array_keys($fields), 0)];
    $locking = $lock ? ' FOR UPDATE' : '';
    {
        $result = $connection->query("SELECT timeid, rate, units FROM npe_rates WHERE showonweb = 'Yes' AND archived = 0 ORDER BY id" . $locking);
        while ($row = $result->fetch_assoc()) {
            $key = $row['timeid'];
            if (!isset($fields[$key])) {
                continue;
            }
            if (isset($report['rates'][$key])) {
                $report['issues'][] = "More than one active rate exists for $key.";
            }
            if (!is_numeric($row['rate']) || (float)$row['rate'] < 0) {
                $report['issues'][] = "The current rate for $key is invalid.";
            }
            $report['rates'][$key] = $row;
        }
        foreach ($fields as $key => $label) {
            if (!isset($report['rates'][$key])) {
                $report['issues'][] = "No active rate exists for $label.";
            }
        }
        $columns = implode(', ', array_map(fn($key) => '`rate_' . $key . '`', array_keys($fields)));
        $result = $connection->query("SELECT id, date, $columns FROM npe_timesheets ORDER BY id" . $locking);
        while ($row = $result->fetch_assoc()) {
            $report['total']++;
            if (!rateMigrationDate((string)$row['date'])) {
                $report['invalid'][] = (int)$row['id'];
                continue;
            }
            if (($from !== '' && $row['date'] < $from) || ($to !== '' && $row['date'] > $to)) {
                $report['outside']++;
                continue;
            }
            $report['matched']++;
            $report['periods'][$row['date'] < '2026-05-01' ? 0 : ($row['date'] < '2026-09-01' ? 1 : 2)]++;
            $expected = migrationRatesForDate($report['rates'], $row['date']);
            $missing = 0;
            $different = false;
            foreach ($fields as $key => $label) {
                $saved = $row['rate_' . $key];
                if ($saved === null) {
                    $missing++;
                    $report['missing'][$key]++;
                } elseif (isset($report['rates'][$key]) && $saved !== $expected[$key]) {
                    $different = true;
                }
            }
            $report[$missing === count($fields) ? 'empty' : ($missing === 0 ? 'complete' : 'partial')]++;
            if ($missing > 0) {
                $report['candidates'][] = ['id' => (int)$row['id'], 'date' => $row['date']];
            }
            if ($different) {
                $report['different']++;
            }
        }
        return $report;
    }
}


function migrationRatesForDate(array $rates, string $date): array
{
    $values = [];
    foreach (timesheetRateFields() as $key => $label) {
        $values[$key] = $rates[$key]['rate'] ?? null;
    }
    if ($date < '2026-05-01') {
        $values['travel_miles'] = '0.50';
    }
    if ($date < '2026-09-01') {
        $values['time_ov'] = '2.49';
    }
    return $values;
}

function previewTimesheetRateMigration(mysqli $connection, string $from = '', string $to = ''): array
{
    $connection->begin_transaction(MYSQLI_TRANS_START_READ_ONLY);
    try {
        return collectTimesheetRateMigration($connection, $from, $to);
    } finally {
        $connection->rollback();
    }
}

function applyTimesheetRateMigration(mysqli $connection, string $from = '', string $to = '', ?string $expectedFingerprint = null): array
{
    $connection->begin_transaction();
    try {
        $report = collectTimesheetRateMigration($connection, $from, $to, true);
        if ($report['issues']) {
            throw new RuntimeException(implode(' ', $report['issues']));
        }
        if ($expectedFingerprint !== null && !hash_equals($expectedFingerprint, rateMigrationFingerprint($report))) {
            throw new RuntimeException('Rates or records changed since preview. Preview again before applying.');
        }
        $sets = [];
        foreach (timesheetRateFields() as $key => $label) {
            $sets[] = "`rate_$key` = COALESCE(`rate_$key`, ?)";
        }
        // A migration must not rewrite the original record modification timestamp.
        $stmt = $connection->prepare('UPDATE npe_timesheets SET ' . implode(', ', $sets) . ', modified = modified WHERE id = ?');
        $updated = 0;
        foreach ($report['candidates'] as $row) {
            $values = array_values(migrationRatesForDate($report['rates'], $row['date']));
            $values[] = $row['id'];
            $stmt->bind_param('ssssssssi', ...$values);
            $stmt->execute();
            $updated += $stmt->affected_rows;
        }
        $stmt->close();
        $connection->commit();
        return ['updated' => $updated, 'fields' => array_sum($report['missing']), 'invalid' => $report['invalid']];
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function rateMigrationFingerprint(array $report): string
{
    return hash('sha256', json_encode($report, JSON_THROW_ON_ERROR));
}
