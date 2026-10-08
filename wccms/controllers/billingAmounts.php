<?php
/** All billing amounts are calculated per timesheet from saved rates. */
class BillingRateException extends RuntimeException {}

function billingFields(): array
{
    return ['time_ov','time_cso','travel_units','travel_miles','certs','tanker_cert','sha_sa','courier'];
}

function billingHundredths($value): int
{
    if (!is_scalar($value) || !preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/D', (string)$value, $parts)) {
        throw new BillingRateException('A timesheet contains an invalid quantity or saved rate.');
    }
    $scaled = (int)$parts[2] * 100 + (int)str_pad($parts[3] ?? '', 2, '0');
    return ($parts[1] === '-' ? -1 : 1) * $scaled;
}

function billingEntryAmounts(array $row): array
{
    $amounts = [];
    foreach (billingFields() as $field) {
        if (!array_key_exists('rate_'.$field, $row) || $row['rate_'.$field] === null) {
            throw new BillingRateException('Timesheet '.(int)($row['id'] ?? 0).' has missing saved rates. Run Saved Rates Migration before generating this report.');
        }
        $product = billingHundredths($row[$field]) * billingHundredths($row['rate_'.$field]);
        // Round each charge to pennies, half away from zero, using integer arithmetic.
        $pence = intdiv(abs($product) + 50, 100) * ($product < 0 ? -1 : 1);
        $amounts[$field] = $pence / 100;
    }
    return $amounts;
}

function addBillingEntryTotals(array &$totals, array $row): void
{
    foreach (billingFields() as $field) {
        $totals['numeric'][$field] += $row[$field];
        $totals['monetary'][$field] = ((int)round($totals['monetary'][$field] * 100)
            + (int)round($row['amounts'][$field] * 100)) / 100;
    }
}

function billingSavedRateRow(array $data): string
{
    $rates = array_fill_keys(billingFields(), []);
    foreach ($data as $group) {
        foreach ($group['entries'] as $entry) {
            foreach (billingFields() as $field) $rates[$field][(string)$entry['rate_'.$field]] = true;
        }
    }
    $basis = ['time_ov'=>'per minute','time_cso'=>'per minute','travel_units'=>'per unit',
        'travel_miles'=>'per mile','certs'=>'per cert','tanker_cert'=>'per daily dispatch',
        'sha_sa'=>'per cert','courier'=>'per £'];
    $html = "<tr class='table-info rate-row'><td colspan='4'>Saved rate(s) used</td>";
    foreach ($rates as $field => $values) {
        $values = array_keys($values);
        sort($values, SORT_NUMERIC);
        $label = $values ? implode(' / ', array_map(fn($rate) => '£ '.number_format((float)$rate, 2), $values)) : 'No entries';
        $html .= "<td class='text-right'>".htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'<br>'.$basis[$field].'</td>';
    }
    return $html.'</tr>';
}

function billingReportUnavailable(BillingRateException $exception): never
{
    if (ob_get_level() > 0) ob_clean();
    if (!headers_sent()) {
        http_response_code(409);
        header_remove('Content-Disposition');
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<p role="alert">'.htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8').'</p>';
    exit;
}
