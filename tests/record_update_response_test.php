<?php
// Real edit POST handlers, temporary tables only; headers intercepted before exit.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ini_set('error_log', '/tmp/record-update-response-test.log');
require dirname(__DIR__, 2) . '/private/db.php';
require dirname(__DIR__) . '/wccms/include/functions.php';
require dirname(__DIR__) . '/wccms/controllers/productInsert.php';
class UpdateResponseTestDB {
    public static mysqli $db;
    public static function connection() { return self::$db; }
    public static function query($sql) {
        try { return self::$db->query($sql); }
        catch (mysqli_sql_exception $error) { throw new RuntimeException('Test query failed', $error->getCode(), $error); }
    }
}
class UpdateTestRedirect extends RuntimeException {
    public function __construct(public string $location, public int $status) { parent::__construct(); }
}
function updateCheck($ok, $message) {
    if (!$ok) throw new RuntimeException($message);
    echo "PASS: $message\n";
}
$db = DB::connection();
UpdateResponseTestDB::$db = $db;
foreach (['products', 'cms_log', 'gallery'] as $table) {
    $ddl = $db->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
    $db->query(preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $ddl));
}
$db->query('CREATE TEMPORARY TABLE cms_form AS SELECT * FROM cms_form');
$root = dirname(__DIR__) . '/wccms';
foreach (['controllers/recordEdit.php', 'logrecord.php'] as $file) {
    $source = str_replace(['DB::', '__DIR__'], ['UpdateResponseTestDB::', var_export(dirname($root . '/' . $file), true)], file_get_contents($root . '/' . $file));
    $path = tempnam('/tmp', 'update-response-');
    file_put_contents($path, $source);
    require $path;
    unlink($path);
}
$created = insertProductRecord($db, ['name' => 'Before update', 'expected_date' => '2026-10-21']);
$recordId = $created['recordID'];
$_SESSION = ['useremail' => 'test@example.com'];
$_SERVER['HTTP_HOST'] = 'dev-npv.witecanvas.com:28086';
$_SERVER['HTTP_USER_AGENT'] = 'Test Chrome';
$_FILES = [];
foreach ([4, 5] as $version) {
    $page = file_get_contents($root . '/recordEditv' . $version . '.php');
    $page = substr($page, 0, strpos($page, "if (isset(\$_GET['remove']))"));
    $page = preg_replace('/include\s*\(\s*\x27setting\/main-top-files.php\x27\s*\);/', '', $page);
    $page = str_replace("header('Location: ' . \$redirectTo, true, 303);", "throw new UpdateTestRedirect(\$redirectTo, 303);", $page);
    foreach (['127.0.0.1', '2001:db8:1234:5678:1234:5678:1234:5678'] as $ip) {
        $_SERVER['REMOTE_ADDR'] = $ip;
        $_GET = ['frm' => '1', 'id' => (string)$recordId];
        $expected = "Updated v$version $ip";
        $_POST = ['submit' => '', 'formnumber' => '1', 'name' => $expected, 'notes' => "Customer's notes"];
        $redirect = null;
        ob_start();
        try {
            (function () use ($page, $db, $version) {
                $conn = $db;
                $BASE_URL = 'http://dev-npv.witecanvas.com:28086';
                $prefs = ['prefCMSVer' => (string)$version, 'prefCMSDebugOn' => 'No'];
                eval('?>' . $page);
            })();
        } catch (UpdateTestRedirect $response) { $redirect = $response; }
        $output = ob_get_clean();
        updateCheck($output === '', "v$version sends no output before redirect");
        updateCheck($redirect && $redirect->status === 303, "v$version redirects after save, including when audit logging fails");
        updateCheck($redirect->location === "recordEditv$version.php?frm=1&id=$recordId&updated=success", "v$version relative redirect preserves external port");
        $row = $db->query('SELECT name,notes FROM products WHERE id=' . (int)$recordId)->fetch_assoc();
        updateCheck($row['name'] === $expected && $row['notes'] === "Customer's notes", "v$version saved data survives audit failures");
    }
    // Failed writes must remain on the form and never report successful saving.
    $_POST['expected_date'] = 'not-a-date';
    $failedError = '';
    ob_start();
    try {
        $failedError = (function () use ($page, $db, $version) {
            $conn = $db;
            $BASE_URL = 'http://dev-npv.witecanvas.com:28086';
            $prefs = ['prefCMSVer' => (string)$version, 'prefCMSDebugOn' => 'No'];
            eval('?>' . $page);
            return $saveError;
        })();
    } catch (UpdateTestRedirect $response) {
        throw new RuntimeException('Invalid update redirected as successful.');
    }
    ob_end_clean();
    updateCheck($failedError !== '', "v$version failed update stays on the form with an error");
}
updateCheck((int)$db->query('SELECT COUNT(*) FROM cms_log')->fetch_row()[0] === 2, 'Valid audit entries are saved and repeated logging does not redeclare functions');
$db->close();
echo "All writes used temporary tables; application records unchanged.\n";
