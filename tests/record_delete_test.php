<?php
// Exercise the shared delete action using connection-local temporary tables only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__, 2) . '/private/db.php';
class DeleteTestDB {
    public static mysqli $db;
    public static function query($sql) { return self::$db->query($sql); }
}
$db = DB::connection();
DeleteTestDB::$db = $db;
$source = str_replace('DB::', 'DeleteTestDB::', file_get_contents(dirname(__DIR__) . '/wccms/controllers/recordView.php'));
$path = tempnam('/tmp', 'record-delete-test-');
file_put_contents($path, $source);
try { require $path; } finally { unlink($path); }
$view = (new ReflectionClass(RecordView::class))->newInstanceWithoutConstructor();
foreach ([true, false] as $hasVisibility) {
    $table = $hasVisibility ? 'delete_test_visible' : 'delete_test_legacy';
    $column = $hasVisibility ? ', showonweb VARCHAR(3)' : '';
    $db->query("CREATE TEMPORARY TABLE `$table` (id INT PRIMARY KEY, archived TINYINT DEFAULT 0$column)");
    $values = $hasVisibility ? "(1, 0, 'Yes'), (2, 0, 'Yes')" : '(1, 0), (2, 0)';
    $db->query("INSERT INTO `$table` VALUES $values");
    $result = $view->deleteContent(1, $table);
    $rows = $db->query("SELECT * FROM `$table` ORDER BY id")->fetch_all(MYSQLI_ASSOC);
    if (!$result['success'] || (int) $rows[0]['archived'] !== 1 || (int) $rows[1]['archived'] !== 0) {
        throw new RuntimeException('Delete must archive only the requested record');
    }
    if ($hasVisibility && ($rows[0]['showonweb'] !== 'No' || $rows[1]['showonweb'] !== 'Yes')) {
        throw new RuntimeException('Delete must hide only the requested record');
    }
    if (!$view->deleteContent(1, $table)['success']) {
        throw new RuntimeException('Repeated deletion must succeed');
    }
    $db->query("DROP TEMPORARY TABLE `$table`");
    echo 'PASS: delete ' . ($hasVisibility ? 'with showonweb' : 'without showonweb') . PHP_EOL;
}
