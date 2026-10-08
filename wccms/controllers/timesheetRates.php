<?php
require_once __DIR__ . '/rateMigrationPreview.php';

function currentTimesheetRates(mysqli $connection): array
{
    $rates = [];
    $result = $connection->query("SELECT timeid, rate FROM npe_rates WHERE showonweb='Yes' AND archived=0 ORDER BY id");
    while ($row = $result->fetch_assoc()) {
        if (!array_key_exists($row['timeid'], timesheetRateFields())) continue;
        if (isset($rates['rate_'.$row['timeid']]) || !is_numeric($row['rate']) || (float)$row['rate'] < 0) {
            throw new RuntimeException('Current timesheet rates are invalid or duplicated.');
        }
        $rates['rate_'.$row['timeid']] = $row['rate'];
    }
    if (count($rates) !== 8) throw new RuntimeException('All eight current timesheet rates must be configured.');
    return $rates;
}

function insertTimesheetWithRates(mysqli $connection, array $data): array
{
    $allowed = ['name','vet','productid','date','time_ov','time_cso','travel_units','travel_miles','certs',
        'sha_sa','tanker_cert','courier','notes','approved','user','showonweb','archived'];
    $data = array_intersect_key($data, array_flip($allowed));
    if (!rateMigrationDate((string)($data['date'] ?? ''))) throw new InvalidArgumentException('A valid work date is required.');
    $connection->begin_transaction();
    try {
        // No historical lookup: new entries always receive the current rates.
        $data = array_merge($data, currentTimesheetRates($connection));
        $columns = array_keys($data);
        $query = 'INSERT INTO npe_timesheets (`'.implode('`, `',$columns).'`) VALUES ('.implode(', ',array_fill(0,count($data),'?')).')';
        $stmt = $connection->prepare($query);
        $values = array_values($data);
        $stmt->bind_param(str_repeat('s',count($values)), ...$values);
        $stmt->execute();
        $id = $connection->insert_id;
        $stmt->close();
        $connection->commit();
        return ['status'=>'success','message'=>'Record added successfully','recordID'=>$id,'query'=>$query];
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function copyTimesheetWithCurrentRates(mysqli $connection, int $id): array
{
    $stmt = $connection->prepare('SELECT * FROM npe_timesheets WHERE id=?');
    $stmt->bind_param('i',$id);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();
    if (!$row) throw new RuntimeException('Timesheet not found.');
    $row['showonweb']='No';
    return insertTimesheetWithRates($connection,$row);
}
