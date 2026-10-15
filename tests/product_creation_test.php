<?php
// CLI integration test. All writes target connection-local temporary tables.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ini_set('error_log', '/tmp/product-creation-test.log');
require dirname(__DIR__, 2) . '/private/db.php';
$controllerRoot = dirname(__DIR__) . '/wccms/controllers';
class TestConnection {
    public $db;
    function __construct($db) {$this->db=$db;}
    function __call($method,$args) {return $this->db->$method(...$args);}
    function __get($key) {return $this->db->$key;}
    function close() {}
}
class TestDB {
    static $connection;
    static function connection() {return self::$connection;}
    static function query($sql) {return self::$connection->query($sql);}
}
$db=DB::connection();
TestDB::$connection=new TestConnection($db);
foreach (['products','products_import','products_processed','product_import_log','product_import_exceptions'] as $table) {
    $ddl=$db->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
    $db->query(preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $ddl));
}
foreach (['import1.php','recordAdd.php'] as $file) {
    $source=file_get_contents("$controllerRoot/$file");
    $source=str_replace(['DB::','__DIR__'],['TestDB::',var_export($controllerRoot,true)],$source);
    $path=tempnam('/tmp','product-test-');file_put_contents($path,$source);require $path;unlink($path);
}
function check($ok,$message) {if(!$ok)throw new Exception($message); echo "PASS: $message\n";}
$log=$db->prepare("INSERT INTO product_import_log (name,type,status,source,notes,user) VALUES ('test','HF',2,'test.csv','test',0)");$log->execute();$logID=$db->insert_id;
$root = dirname(__DIR__) . '/wccms/importfiles/done/';
foreach (['HF_week_42_import_Batch_402.csv','NT_week_42_import_Batch_401.csv'] as $file) {
    check(truncateTempTable(),'Prepare isolated staging table');
    check(importDataToTempTable($root.$file,'HF')['success'],"Read $file with header");
    ob_start();$result=processImportedData($logID);ob_end_clean();check($result['success'],'Process all CSV rows and lookup exceptions');
    ob_start();$result=updateLiveTable($logID);ob_end_clean();check($result['success'],'Insert all processed products under strict mode');
}
$before=(int)$db->query('SELECT COUNT(*) FROM products')->fetch_row()[0];
ob_start();$result=updateLiveTable($logID);ob_end_clean();check($result['success'],'Duplicate PO roll-forward and exception logging');
check((int)$db->query('SELECT COUNT(*) FROM products')->fetch_row()[0]>$before,'Duplicate import inserted replacements');
$record=(new ReflectionClass(RecordAdd::class))->newInstanceWithoutConstructor();$record->table=['name'=>'products'];
$result=$record->insertTableContent(['name'=>'Manual test', 'expected_date'=>'2026-10-15','expected_time'=>'','manuf'=>'7','notes'=>"Customer's note",'pallet_count'=>'','formnumber'=>1,'submit'=>'Save']);
check($result['status']==='success','Actual manual add path accepts omitted unused fields and quoted text');
$row=$db->query('SELECT * FROM products WHERE id='.(int)$result['recordID'])->fetch_assoc();
check($row['sku']==='' && $row['section']==='0' && $row['importbatch']==='0' && $row['manuf']==='7' && $row['notes']==="Customer's note" && $row['expected_time']==='00:00:00' && $row['pallet_count']===null,'Defaults, submitted values, empty optional numbers and unknown time');
$values = productInsertData(['name' => 'Schema audit', 'expected_date' => '2026-10-15']);
$columns = $db->query('SHOW COLUMNS FROM products');
while ($column = $columns->fetch_assoc()) {
    if ($column['Null'] === 'NO' && $column['Default'] === null && $column['Extra'] !== 'auto_increment') {
        check(array_key_exists($column['Field'], $values), 'Required field covered: ' . $column['Field']);
    }
}
$before=$db->query('SELECT id,status FROM products ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$db->query("UPDATE products_processed SET po='' ORDER BY id DESC LIMIT 1");
ob_start();$result=updateLiveTable($logID);ob_end_clean();
check(!$result['success'],'Invalid required name gives controlled import failure');
check($before===$db->query('SELECT id,status FROM products ORDER BY id')->fetch_all(MYSQLI_ASSOC),'Failed batch rolls back inserted rows and duplicate-status updates');
$result=$record->insertTableContent(['name'=>'Bad date','expected_date'=>'2026-02-30']);
check($result['status']==='error','Manual invalid date returns a form error');
$db->close();echo "All database tests used connection-local temporary tables; original tables untouched.\n";
