<?php
/** CLI-only, repeatable MariaDB migration; defaults only, no row updates. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function requiredDefaults004(): array
{
    return [
        'products' => [
            'sku' => '', 'po' => '', 'odo' => '', 'notes' => '', 'slug' => '',
            'section' => 0, 'brand' => 0, 'manuf' => 0, 'supplier' => 0,
            'snag_text' => '', 'importbatch' => 0, 'destination_customer' => '',
            'product_type' => '', 'pallet_type' => '',
        ],
        'product_import_exceptions' => ['fieldname' => '', 'fieldvalue' => '', 'userid' => ''],
        'npe_depot_visits' => ['notes' => ''],
        'gallery' => ['alttag' => '', 'caption' => '', 'slug' => '', 'keyword' => ''],
        'npe_destination' => ['destination_status_id' => 3, 'vet_type' => 2],
        'npe_destination_status' => ['staff_type' => 'CSO', 'vet_type_id' => 2],
        'npe_snags' => ['vet' => 0, 'admin' => 0, 'notes' => '', 'status' => 1],
        'npe_snag_status' => ['code' => 'TBC'],
        'npe_rates' => ['timeid' => 'new_id'],
    ];
}

function requiredDefaultsSql004(): array
{
    $statements = [];
    foreach (requiredDefaults004() as $table => $fields) {
        $clauses = [];
        foreach ($fields as $field => $value) {
            $literal = is_int($value) ? (string)$value : "'" . str_replace("'", "''", $value) . "'";
            $clauses[] = "    ALTER COLUMN `$field` SET DEFAULT $literal";
        }
        $statements[$table] = "ALTER TABLE `$table`\n" . implode(",\n", $clauses) . ';';
    }
    return $statements;
}

function requiredDefaultsPreflight004(mysqli $db): void
{
    if (stripos((string)$db->query('SELECT VERSION()')->fetch_row()[0], 'MariaDB') === false) {
        throw new RuntimeException('This migration is intended for MariaDB.');
    }
    // Inspect all tables before applying any DDL. Never replace an unexpected default.
    foreach (requiredDefaults004() as $table => $fields) {
        $columns = [];
        $result = $db->query("SHOW COLUMNS FROM `$table`");
        while ($column = $result->fetch_assoc()) $columns[$column['Field']] = $column;
        foreach ($fields as $field => $value) {
            $column = $columns[$field] ?? null;
            if (!$column || $column['Null'] !== 'NO' || $column['Extra'] !== '') {
                throw new RuntimeException("Unexpected schema for $table.$field; nothing applied by preflight.");
            }
            $pattern = is_int($value) ? '/^(tinyint|smallint|mediumint|int|bigint)\b/'
                : '/^(varchar|char|tinytext|text|mediumtext|longtext)\b/';
            if (!preg_match($pattern, $column['Type'])) {
                throw new RuntimeException("Unexpected column type for $table.$field.");
            }
            if (is_string($value) && preg_match('/^(?:var)?char\((\d+)\)/', $column['Type'], $match)
                && strlen($value) > (int)$match[1]) {
                throw new RuntimeException("Default is too long for $table.$field.");
            }
            $expected = is_int($value) ? (string)$value
                : (str_contains($column['Type'], 'text') ? "'" . $value . "'" : $value);
            if ($column['Default'] !== null && (string)$column['Default'] !== $expected) {
                throw new RuntimeException("Conflicting existing default for $table.$field; review it manually.");
            }
        }
    }
    $references = [
        ['npe_destination_status', 3, 'code', 'OS'],
        ['npe_vet_type', 2, 'code', 'CSO'],
        ['npe_snag_status', 1, 'name', 'Snag Raised'],
    ];
    foreach ($references as [$table, $id, $field, $expected]) {
        $row = $db->query("SELECT `$field`, archived FROM `$table` WHERE id=$id")->fetch_assoc();
        if (!$row || $row[$field] !== $expected || (int)$row['archived'] !== 0) {
            throw new RuntimeException("Expected active $table ID $id ($expected) is missing or different.");
        }
    }
}

function requiredDefaultsApply004(mysqli $db): void
{
    requiredDefaultsPreflight004($db);
    // MariaDB DDL commits per statement. A failed run can be safely rerun after review.
    foreach (requiredDefaultsSql004() as $table => $sql) {
        $db->query($sql);
        echo "Applied defaults: $table\n";
    }
    requiredDefaultsPreflight004($db);
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $db = null;
    $locked = false;
    try {
        $apply = false;
        $expectedDatabase = null;
        foreach (array_slice($argv, 1) as $argument) {
            if ($argument === '--apply') $apply = true;
            elseif (str_starts_with($argument, '--database=')) $expectedDatabase = substr($argument, 11);
            else throw new InvalidArgumentException('Usage: php migrations/004_required_defaults.php [--apply --database=DATABASE_NAME]');
        }
        require dirname(__DIR__, 2) . '/private/db.php';
        $db = DB::connection();
        $database = (string)$db->query('SELECT DATABASE()')->fetch_row()[0];
        if ($database === '') throw new RuntimeException('No database selected.');
        echo "Connected database: $database\n";
        if ($expectedDatabase !== null && $database !== $expectedDatabase) {
            throw new RuntimeException('Selected database does not match --database.');
        }
        if ($apply && $expectedDatabase === null) {
            throw new RuntimeException('Applying requires --database=DATABASE_NAME to confirm the target.');
        }
        $lockName = 'npv:defaults004:' . substr(hash('sha256', $database), 0, 32);
        $lock = $db->prepare('SELECT GET_LOCK(?, 0)');
        $lock->bind_param('s', $lockName);
        $lock->execute();
        $locked = (int)$lock->get_result()->fetch_row()[0] === 1;
        $lock->close();
        if (!$locked) throw new RuntimeException('Another defaults migration is running.');
        $db->query('SET SESSION lock_wait_timeout = 10');
        requiredDefaultsPreflight004($db);
        echo "Preflight passed for all nine tables and reference IDs.\n";
        if ($apply) {
            $snapshot = "-- Schema before defaults migration 004; existing rows are not included.\n";
            foreach (array_keys(requiredDefaults004()) as $table) {
                $snapshot .= $db->query("SHOW CREATE TABLE `$table`")->fetch_row()[1] . ";\n\n";
            }
            $snapshotPath = tempnam(sys_get_temp_dir(), 'npv-defaults004-');
            if ($snapshotPath === false || file_put_contents($snapshotPath, $snapshot) !== strlen($snapshot)) {
                throw new RuntimeException('Cannot save the pre-migration schema snapshot; no ALTER statements applied.');
            }
            echo "Pre-migration schema snapshot: $snapshotPath\n";
            requiredDefaultsApply004($db);
            echo "Complete. Existing record values and password handling were preserved.\n";
        } else {
            echo "Preview only; no schema or record changes made.\n";
            echo implode("\n\n", requiredDefaultsSql004()), "\n";
        }
    } catch (Throwable $exception) {
        fwrite(STDERR, 'Migration stopped: ' . $exception->getMessage() . "\n");
        exit(1);
    } finally {
        // Closing the connection releases the advisory lock, including on failure.
        if ($db) $db->close();
    }
}
