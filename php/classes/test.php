<?
define("NP_LANG", "es_ES");
define("NPLIB_PATH", "../");

require_once NPLIB_PATH.'includes.php';

$connection = new NP_MySQL_Connection("localhost", null, "npadmin", "npadmin_user", "npadmin_password");

class Test extends NP_SQL_Object {
	function __construct() {
		global $connection;
		parent::__construct($connection, "NP_MySQL_Toolkit");
	}
}

try {
	
	
	$test1 = new Test();
	//print_r($test1);
	$test1->id = 1;
	$test1->load();
	echo $test1->msg."\n";
	
	$test1->msg = "Hola ".$test1->fecha;
	$test1->fecha = null;
	if ($test1->update()) {
		echo "Mensaje actualizado\n";
	} else {
		echo "No se actualizo el mensaje (posiblemente ya tuviera el valor que se pretendia asignar ;-))\n";
	}

	$test2 = new Test();
	$test2->msg = "Adios";
	$test2->store();
	echo "Id asignado: ".$test2->id."\n";

	$test3 = new Test();
	//print_r($test3);
	$test3->id = $test2->id;
	if ($test3->load()) {
		echo $test3->msg."\n";
		echo $test3->fecha."\n";
		if ($test3->delete() == 1) {
			echo "Borrado!\n";
		} else {
			echo "No borrado :-(";
		}

	}
	if (!$test3->load()) {
		echo "Borrado confirmado! :-)\n";
	}
	
	print_r(Test::listObjects());

} catch (Exception $ex) {
	echo "ERROR: ".$ex->getMessage();
	echo $ex->getTraceAsString();
}

?>
