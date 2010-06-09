<?php
/** 
 * NPLib - PHP
 * 
 * Logging API
 * 
 * @package np-lib
 * @subpackage 
 * @version 20090624
 * 
 * @author Daniel Pecos Martínez
 * @copyright Copyright (c) Daniel Pecos Martínez 
 * @license http://www.gnu.org/licenses/lgpl.html  LGPL License
 */


/**
 * TEST
 * @param $name
 * @param $msg
 * @return unknown_type
 */
function NP_default_log_formatter($name, $msg) {
	$session = "-";
	if (isset($_SESSION))
		$session = "PHPSESSID: ".session_id();
	return "[".date("Y/m/d - H:i:s")." +".sprintf("%.3f", microtime(true) - NPLogger::$loggers[$name]["time"])." (".$session." - REQID: ".$_REQUEST["NP_log_rnd"].")] ".$msg."\n";	
}
	
class NPLogger {
	static $loggers = array();
	static $files = array();
	static $defaultFile = null;
	//static $defaultLogger = null;
    
	static function default_log_formatter($name, $msg) {
		$session = "-";
		if (isset($_SESSION))
			$session = "PHPSESSID: ".session_id();
		return "[".date("Y/m/d - H:i:s")." +".sprintf("%.3f", microtime(true) - NPLogger::$loggers[$name]["time"])." (".$session." - REQID: ".$_REQUEST["NP_log_rnd"].")] ".$msg."\n";	
	}
	
	static function init($name, $file, $level, $formatter = null, $enable_sql_explain_plan = true) {
		if (!array_key_exists($name, NPLogger::$loggers) && (file_exists($file) && is_writable($file) || !file_exists($file))) {
			NPLogger::$loggers[$name] = array();
			NPLogger::$loggers[$name]["time"] = microtime(true);

			$func = null;
			if (function_exists($formatter)) {
				$func = new ReflectionFunction($formatter);
			} else {
				$func = new ReflectionFunction("NP_default_log_formatter");
			}
			NPLogger::$loggers[$name]["formatter"] = $func;

			if ($file !== null) {
				if (!array_key_exists($file, NPLogger::$files)) {
					//NPLogger::$loggers[$name]["logfile"] = fopen($file, 'a');
					NPLogger::$files[$file] = fopen($file, 'a');
				}
			} else {
				$file = NPLogger::$defaultFile;
			}
			NPLogger::$loggers[$name]["logfile"] = $file;

			NPLogger::$loggers[$name]["level"] = $level;
			
			NPLogger::$loggers[$name]["enable_sql_explain_plan"] = $enable_sql_explain_plan;
						
			if (!array_key_exists("NP_log_rnd", $_REQUEST)) {
				$request_id = NP_random_string(6);
				$_REQUEST["NP_log_rnd"] = $request_id;
			}

			//NPLogger::info("GET: ".str_replace("\n","", print_r($_GET, true)));
			//NPLogger::info("POST: ".str_replace("\n","", print_r($_POST, true)));
		}
	}
	
	function __destruct()  {
		if (NPLogger::isEnabled())
			fclose(NPLogger::$loggers[$name]["logfile"]);
	}
	
	static function __log($name, $level, $msg) {
		if (NPLogger::isEnabled()) {
			if (NPLogger::$loggers[$name]["formatter"] !== null)
				fwrite(NPLogger::$files[NPLogger::$loggers[$name]["logfile"]], NPLogger::$loggers[$name]["formatter"]->invokeArgs(array($name, $msg)));
			else
				fwrite(NPLogger::$files[NPLogger::$loggers[$name]["logfile"]], $msg);
			//flush(NPLogger::$loggers[$name]["logfile"]);
		}
	}
	
	static function debug($name, $msg) { NPLogger::__log($name, "DEBUG", $msg); }
	static function info($name, $msg) { NPLogger::__log($name, "INFO", $msg); }
	static function error($name, $msg) { NPLogger::__log($name, "ERROR", $msg); }
	
	static function logSQLQuery($name, $sql, $queryId = null, $dbCon = null) {
		if ($queryId !== null) {
			$qData = NPLogger::$loggers[$name]["sqlData"][$queryId];
			unset(NPLogger::$loggers[$name]["sqlData"]);
			if ($dbCon != null)
				NPLogger::__log($name, "SQL", "Rows: ".mysql_affected_rows($dbCon)." -> ".$sql);
			else
				NPLogger::__log($name, "SQL", "Rows: ".mysql_affected_rows()." -> ".$sql);
		} else {
			NPLogger::__log($name, "SQL", $sql);
		} 
	}
	static function prepareSQLQuery($name, $sql) {
		if (NPLogger::$loggers[$name]["enable_sql_explain_plan"] === true) {
			try {
				$rs = mysql_query('EXPLAIN '.$sql);
			} catch(Exception $e) { 
			}
			$id = null;
			if ($rs) {
				$row = mysql_fetch_array($rs, MYSQL_ASSOC);
				if (!array_key_exists("sqlData", NPLogger::$loggers[$name]))
					NPLogger::$loggers[$name]["sqlData"] = array();
				$id = NP_random_string(10);
				NPLogger::$loggers[$name]["sqlData"][$id] = $row;
			}
			return $id;
		} else {
			return null;
		}
	}
	
	static function isEnabled() { return count(NPLogger::$loggers) > 0; }
	
	static function loggerInfo($name) {
		if (array_key_exists($name, NPLogger::$loggers))
			return NPLogger::$loggers[$name];
		else
			return null;
	}
}
?>