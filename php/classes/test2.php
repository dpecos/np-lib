<?
define("NP_LANG", "es_ES");
define("NPLIB_PATH", "../");

require_once NPLIB_PATH.'includes.php';

$connection = new NP_MySQL_Connection("localhost", null, "npadmin", "npadmin_user", "npadmin_password");

class Test2 extends NP_SQL_Object {
	function __construct() {	
		if (!self::isInitialized()) {
			self::addField("app_id", array("TYPE" => "INT", "PK" => true, "NULLABLE" => false, "AUTO_INCREMENT" => true));
			self::addField("name", array("TYPE" => "STRING", "NULLABLE" => false, "LENGTH" => 40));
			self::addField("version", array("TYPE" => "INT", "NULLABLE" => false));
		}
		
		global $connection;
		parent::__construct($connection, "NP_MySQL_Toolkit", false);
	}
}

try {

	$t2 = new Test2();
	print_r($t2); 

	$t2 = new Test2();
} catch (Exception $ex) {
	echo "ERROR: ".$ex->getMessage();
	echo $ex->getTraceAsString();
}

?>
