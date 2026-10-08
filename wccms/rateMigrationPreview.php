<?php
require_once __DIR__ . '/setting/main-top-files.php';
$role = $USER->getUserRole();
if (!$user || !$role || (int)$role['level'] < 20) {
    http_response_code(403);
    exit('Administrator access is required.');
}
require_once __DIR__ . '/controllers/rateMigrationPreview.php';
header('Cache-Control: no-store');
$from = is_string($_GET['from'] ?? '') ? ($_GET['from'] ?? '') : 'invalid';
$to = is_string($_GET['to'] ?? '') ? ($_GET['to'] ?? '') : 'invalid';
$report = null;
$applied = null;
$_SESSION['rate_migration_csrf'] ??= bin2hex(random_bytes(32));
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from = is_string($_POST['from'] ?? '') ? $_POST['from'] : 'invalid';
    $to = is_string($_POST['to'] ?? '') ? $_POST['to'] : 'invalid';
    try {
        $token = $_POST['csrf'] ?? '';
        $preview = $_SESSION['rate_migration_preview'] ?? null;
        if (!is_string($token) || !hash_equals($_SESSION['rate_migration_csrf'], $token) || !$preview || $preview['from'] !== $from || $preview['to'] !== $to) {
            throw new RuntimeException('Please preview this date range before applying.');
        }
        $applied = applyTimesheetRateMigration($conn, $from, $to, $preview['fingerprint']);
        unset($_SESSION['rate_migration_preview']);
        $report = previewTimesheetRateMigration($conn, $from, $to);
        $_SESSION['rate_migration_preview'] = ['from' => $from, 'to' => $to, 'fingerprint' => rateMigrationFingerprint($report)];
    } catch (Throwable $exception) {
        error_log('Rate migration apply failed: ' . $exception->getMessage());
        $error = 'Migration not applied. Preview again and check the server log if the problem persists.';
    }
}
if (isset($_GET['preview'])) {
    try {
        $report = previewTimesheetRateMigration($conn, $from, $to);
        $_SESSION['rate_migration_preview'] = ['from' => $from, 'to' => $to, 'fingerprint' => rateMigrationFingerprint($report)];
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (Throwable $exception) {
        error_log('Rate migration preview failed: ' . $exception->getMessage());
        $error = 'Preview unavailable. Check that the saved-rate schema SQL has been applied and check the server log.';
    }
}
function ratePreviewEscape($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<?php require __DIR__ . '/include/header-code.php'; ?>
</head>
<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
<?php require __DIR__ . '/include/header.php'; require __DIR__ . '/include/sidebar.php'; ?>
<section id="main-content"><section class="wrapper site-min-height">
<h1>Saved rates migration</h1>
<div class="alert alert-info">Preview first, then apply to fill missing saved rates. The current rates table is never changed.</div>
<p>Select the work dates for one migration pass. Both dates are inclusive; leave either blank for no limit. Leave both blank to inspect all valid-dated records.</p>
<p>All records are included, including archived and hidden entries. Existing saved rates would be preserved; only missing values would be filled using current rates, with mileage £0.50 before 1 May 2026 and OV £2.49 before 1 September 2026.</p>
<form method="get" class="card card-body mb-3">
<div class="row g-3">
<div class="col-md-4"><label for="from" class="form-label">Work date from (inclusive)</label><input id="from" name="from" type="date" class="form-control" value="<?= ratePreviewEscape($from) ?>"></div>
<div class="col-md-4"><label for="to" class="form-label">Work date to (inclusive)</label><input id="to" name="to" type="date" class="form-control" value="<?= ratePreviewEscape($to) ?>"></div>
<div class="col-md-4 align-self-end"><button name="preview" value="1" class="btn btn-primary">Preview only</button></div>
</div>
</form>
<?php if ($applied !== null): ?><div class="alert alert-success">Migration applied: <?= (int)$applied['updated'] ?> records updated; <?= (int)$applied['fields'] ?> saved-rate fields filled.</div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert alert-danger"><?= ratePreviewEscape($error) ?></div><?php endif; ?>
<?php if ($report !== null): ?>
<h2>Selected work dates: <?= ratePreviewEscape($from ?: 'No lower limit') ?> to <?= ratePreviewEscape($to ?: 'No upper limit') ?></h2>
<p>Preview generated <?= ratePreviewEscape(date('d/m/Y H:i:s T')) ?>. Rates below are read afresh for every preview.</p>
<?php foreach ($report['issues'] as $issue): ?><div class="alert alert-danger"><?= ratePreviewEscape($issue) ?> Migration would be blocked.</div><?php endforeach; ?>
<table class="table table-striped"><thead><tr><th>Check</th><th>Records</th></tr></thead><tbody>
<?php foreach (['total'=>'Total database records','matched'=>'Records within selected dates','outside'=>'Valid-dated records outside selected dates','empty'=>'Records with all eight rates missing','partial'=>'Records with some saved rates missing','complete'=>'Records with all eight rates already saved','different'=>'Records with saved rates differing from the migration rates (preserved)'] as $key=>$label): ?>
<tr><td><?= ratePreviewEscape($label) ?></td><td><?= (int)$report[$key] ?></td></tr>
<?php endforeach; ?>
<tr><td>Invalid work dates, excluded from every date-range pass</td><td><?= count($report['invalid']) ?></td></tr>
</tbody></table>
<h2>Migration rules</h2>
<ul><li>Through 30 April 2026: mileage £0.50; OV £2.49 (<?= (int)$report['periods'][0] ?> selected records).</li>
<li>1 May–31 August 2026: current mileage; OV £2.49 (<?= (int)$report['periods'][1] ?> selected records).</li>
<li>From 1 September 2026: all current rates (<?= (int)$report['periods'][2] ?> selected records).</li></ul>
<p>All other rates come from the current table throughout. You can leave both dates blank to migrate all three periods in one operation.</p>
<h2>Current rates and missing values</h2>
<table class="table table-striped"><thead><tr><th>Charge</th><th>Saved column</th><th>Current rate (£)</th><th>Basis</th><th>Missing values in selected records</th></tr></thead><tbody>
<?php foreach (timesheetRateFields() as $key=>$label): $rate = $report['rates'][$key] ?? null; ?>
<tr><td><?= ratePreviewEscape($label) ?></td><td><code><?= ratePreviewEscape('rate_'.$key) ?></code></td><td><?= ratePreviewEscape($rate['rate'] ?? 'MISSING') ?></td><td><?= ratePreviewEscape($rate['units'] ?? '') ?></td><td><?= (int)$report['missing'][$key] ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<p>Records with missing rates: <strong><?= (int)($report['empty']+$report['partial']) ?></strong>. Missing rate fields: <strong><?= array_sum($report['missing']) ?></strong>. Previewing does not write values. Applying fills only missing values.</p>
<?php if (!$report['issues'] && ($report['empty'] + $report['partial']) > 0 && isset($_SESSION['rate_migration_preview'])): ?>
<form method="post" class="mb-3">
<input type="hidden" name="from" value="<?= ratePreviewEscape($from) ?>">
<input type="hidden" name="to" value="<?= ratePreviewEscape($to) ?>">
<input type="hidden" name="csrf" value="<?= ratePreviewEscape($_SESSION['rate_migration_csrf']) ?>">
<button class="btn btn-warning">Apply migration to <?= (int)($report['empty']+$report['partial']) ?> records</button>
</form>
<?php endif; ?>
<?php if ($report['invalid']): ?><div class="alert alert-warning">Invalid-date record IDs: <?= ratePreviewEscape(implode(', ', $report['invalid'])) ?>. Review their work dates before migration.</div><?php endif; ?>
<?php endif; ?>
</section></section>
<?php require __DIR__ . '/include/footer-code.php'; ?>
</body></html>
