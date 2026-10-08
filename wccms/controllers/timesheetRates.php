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
    // Browser forms do not submit an actor, and the generic add form hides vet.
    if (isset($_SESSION['useremail'])) {
        $actor = $connection->prepare('SELECT id FROM cms_adminlogin WHERE username=?');
        $actor->bind_param('s', $_SESSION['useremail']);
        $actor->execute();
        $account = $actor->get_result()->fetch_assoc();
        $actor->close();
        if (!$account) throw new InvalidArgumentException('Please log in again before saving.');
        $data['user'] = $account['id'];
    }
    if (empty($data['user'])) throw new InvalidArgumentException('Please log in before saving.');
    $productId = filter_var($data['productid'] ?? null, FILTER_VALIDATE_INT);
    if (!$productId || $productId < 1) throw new InvalidArgumentException('Select a consignment before saving.');
    $product = $connection->prepare('SELECT id, vet FROM products WHERE id=?');
    $product->bind_param('i', $productId);
    $product->execute();
    $consignment = $product->get_result()->fetch_assoc();
    $product->close();
    if (!$consignment) throw new InvalidArgumentException('The selected consignment no longer exists.');
    if (!isset($data['vet']) || $data['vet'] === '') {
        $data['vet'] = $consignment['vet'] ?? 0;
    }
    // Legacy timesheets use 0 for an unassigned vet.
    $data['vet'] = (int)$data['vet'];
    foreach (array_keys(timesheetRateFields()) as $field) {
        if (!isset($data[$field]) || $data[$field] === '') $data[$field] = '0';
        if (!is_scalar($data[$field]) || !is_numeric($data[$field])) {
            throw new InvalidArgumentException('Enter a number for ' . timesheetRateFields()[$field] . '.');
        }
    }
    $data['notes'] = $data['notes'] ?? '';
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
