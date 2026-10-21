<?php
// CLI integration checks: all inserts use connection-local temporary tables.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ini_set('error_log', '/tmp/record-add-test.log');
require dirname(__DIR__, 2) . '/private/db.php';
require dirname(__DIR__) . '/wccms/controllers/recordInsert.php';
class RecordAddTestConnection {
    public function __construct(public mysqli $db) {}
    public function __call($method, $args) { return $this->db->$method(...$args); }
    public function __get($key) { return $this->db->$key; }
    public function close() {}
}
class RecordAddTestDB {
    public static $connection;
    public static function connection() { return self::$connection; }
    public static function query($sql) { return self::$connection->query($sql); }
}
function addCheck($ok, $message) {
    if (!$ok) throw new RuntimeException($message);
    echo "PASS: $message\n";
}
$db = DB::connection();
RecordAddTestDB::$connection = new RecordAddTestConnection($db);
$root = dirname(__DIR__) . '/wccms/controllers';
$source = str_replace(['DB::', '__DIR__'], ['RecordAddTestDB::', var_export($root, true)], file_get_contents($root . '/recordAdd.php'));
$path = tempnam('/tmp', 'record-add-test-');
file_put_contents($path, $source);
require $path;
unlink($path);
$record = (new ReflectionClass(RecordAdd::class))->newInstanceWithoutConstructor();
foreach ([4,5,6,7,8,9,10,14,15,16,17,18,19] as $formId) {
    $form = new RecordAdd($formId);
    $table = $form->getTable()['name'];
    $ddl = $db->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
    $db->query(preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $ddl));
    $columns = [];
    $result = $db->query("SHOW COLUMNS FROM `$table`");
    while ($column = $result->fetch_assoc()) $columns[$column['Field']] = $column;
    $data = ['formnumber' => $formId, 'submit' => 'Save'];
    foreach ($form->getFormFields(true) as $field) {
        $name = $field['name'];
        if (!isset($columns[$name])) continue;
        $type = $columns[$name]['Type'];
        $value = '';
        if ($name === 'name') $value = "Test's record";
        elseif ($name === 'code') $value = 'ADDTEST';
        elseif ($name === 'notes' || $name === 'text') $value = "Depot's notes <b>retained</b>";
        elseif (preg_match('/^enum\(/', $type, $match)) continue; // Browser may omit untouched radios.
        elseif (preg_match('/^date\b/', $type)) $value = '2026-10-21';
        elseif (preg_match('/^(int|decimal)\b/', $type) && $name !== 'sort'
            && $columns[$name]['Null'] === 'NO' && $columns[$name]['Default'] === null) $value = '1';
        $data[$name] = $value;
    }
    if ($table === 'npe_depot') {
        try {
            $db->query("INSERT INTO npe_depot (name,code,siteapproval,notes,sort) VALUES ('Legacy','LEGACY','','','')");
            throw new RuntimeException('Legacy empty Sort unexpectedly saved.');
        } catch (mysqli_sql_exception $error) {
            addCheck($error->getCode() === 1366, 'Reproduced original depot failure with empty Sort under strict mode');
        }
    }
    $record->table = ['name' => $table];
    $response = $record->insertTableContent($data);
    addCheck($response['status'] === 'success', "$table form saves with blank optional values: " . $response['message']);
    $row = $db->query("SELECT * FROM `$table` WHERE id=" . (int)$response['recordID'])->fetch_assoc();
    addCheck($row['name'] === "Test's record", "$table preserves apostrophes and returns its own inserted ID");
    if ($table === 'npe_depot') {
        addCheck($row['sort'] === '0' && $row['production'] === 'No' && $row['showonweb'] === 'Yes', 'Depot blank sort and omitted radio defaults work');
        addCheck($row['notes'] === "Depot's notes <b>retained</b>", 'Depot rich text is stored without SQL escapes');
        $before = $db->query('SELECT COUNT(*) FROM npe_depot')->fetch_row()[0];
        $duplicate = $record->insertTableContent($data);
        addCheck($duplicate['status'] === 'error' && str_contains($duplicate['message'], 'already exists'), 'Duplicate depot code returns a readable form error');
        addCheck($db->query('SELECT COUNT(*) FROM npe_depot')->fetch_row()[0] === $before, 'Rejected duplicate does not insert a depot');
        $data['sort'] = 'invalid';
        $invalid = $record->insertTableContent($data);
        addCheck($invalid['status'] === 'error' && str_contains($invalid['message'], 'number for Sort'), 'Invalid sort returns validation error');
        $data['sort'] = '1';
        $data['code'] = 'OTHER';
        $data['name'] = str_repeat('x', 33);
        $long = $record->insertTableContent($data);
        addCheck($long['status'] === 'error' && str_contains($long['message'], 'too long'), 'Overlong depot name returns readable error');
    }
}
// Exercise the real v4 POST handler and form markup, omitting site chrome and gallery.
require dirname(__DIR__) . '/wccms/include/functions.php';
require dirname(__DIR__) . '/wccms/controllers/formField.php';
$page = file_get_contents(dirname(__DIR__) . '/wccms/recordAddv4.php');
$page = substr($page, 0, strpos($page, '<!-- Trigger the modal with a button -->'));
$page = preg_replace('/\binclude\([^;]+\);/', '', $page);
$page = preg_replace('/\brequire_once [^;]+;/', '', $page);
$_GET = ['frm' => '15'];
$_POST = ['submit' => '', 'formnumber' => '15', 'name' => "Depot's <script>test</script>",
    'code' => 'ADDTEST', 'address' => "Depot's address", 'siteapproval' => '',
    'notes' => '</textarea><script>alert(1)</script>', 'sort' => '', 'production' => 'Yes', 'showonweb' => 'No'];
ob_start();
(function () use ($page, $db) {
    $conn = $db;
    $BASE_URL = '';
    $prefs = ['prefCMSDebugOn' => 'No', 'prefCMSVer' => '4'];
    $userlevel = 0;
    eval('?>' . $page);
})();
$html = ob_get_clean();
addCheck(str_contains($html, 'role="alert"') && str_contains($html, 'already exists'), 'Actual v4 failed save renders an error instead of a blank page');
addCheck(str_contains($html, 'Depot&#039;s &lt;script&gt;test&lt;/script&gt;'), 'Actual v4 page retains and escapes submitted name');
addCheck(str_contains($html, '&lt;/textarea&gt;&lt;script&gt;alert(1)&lt;/script&gt;'), 'Actual v4 page safely retains rich text');
addCheck(str_contains($html, "checked value='Yes'") && str_contains($html, "checked value='No'"), 'Actual v4 page retains radio choices');
$db->close();
echo "All writes used temporary tables; application records unchanged.\n";
