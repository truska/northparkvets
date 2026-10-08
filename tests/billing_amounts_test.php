<?php
require_once __DIR__ . '/../wccms/controllers/billingAmounts.php';
function billingTest(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
function billingTestRow(): array {
    $row = ['id'=>1];
    foreach (billingFields() as $field) { $row[$field]='0'; $row['rate_'.$field]='0.00'; }
    return $row;
}
$old=billingTestRow();$old['travel_miles']='10';$old['rate_travel_miles']='0.50';
$new=$old;$new['rate_travel_miles']='0.55';
$amount=billingEntryAmounts($old)['travel_miles']+billingEntryAmounts($new)['travel_miles'];
billingTest($amount===10.5,'Mixed-rate mileage must sum individual charges to 10.50.');
$old['time_ov']='10';$old['rate_time_ov']='2.49';$new['time_ov']='10';$new['rate_time_ov']='2.67';
billingTest((int)round((billingEntryAmounts($old)['time_ov']+billingEntryAmounts($new)['time_ov'])*100)===5160,'Mixed OV rates must be retained.');
$old['courier']='0.01';$old['rate_courier']='1.50';
billingTest(billingEntryAmounts($old)['courier']===0.02,'Half-penny charges must round away from zero.');
$old['courier']='-0.01';billingTest(billingEntryAmounts($old)['courier']===-0.02,'Negative half-pennies must round consistently.');
$old['rate_time_ov']='0.00';billingTest(billingEntryAmounts($old)['time_ov']===0,'A saved zero rate is valid.');
$old['rate_time_ov']=null;
try { billingEntryAmounts($old); throw new LogicException('Missing saved rates must be rejected.'); }
catch (BillingRateException $exception) { billingTest(str_contains($exception->getMessage(),'Saved Rates Migration'),'Missing-rate error must identify the remedy.'); }
echo "Saved-rate arithmetic tests passed.\n";
