<?php
class NP_MySQL_Toolkit {

	public static function decodeI18NSqlValue($str) {
		$matches = array();
		if ((defined("NP_LANG") || defined("NP_DEFAULT_LANG")) &&
		preg_match_all('/#(.?.?_.?.?)@([^#]*)#/', $str, $matches)) {

			$langs = $matches[1];
			$strings = $matches[2];

			$translations = array();

			foreach ($langs as $idx => $lang) {
				$translations[$lang] = $strings[$idx];
			}

			return $translations;
		} else {
			return $str;
		}
	}

	public static function encodeI18NSqlValue($strings) {
		if (is_array($strings)) {
			$string = "";
			foreach ($strings as $lang => $value) {
				if (!is_null($value) && $value !== "")
				$string .= "#".$lang."@".$value."#";
			}
			if (trim($string) === "") {
				return null;
			} else {
				return $string;
			}
		} else {
			return $strings;
		}
	}

	public static function encodeSQLValue($strVal, $sqlType) {
		if (isset($strVal) && $strVal !== null) {
			if (!is_array($strVal)) {
				$strVal = trim($strVal);
			}

			if ($sqlType == "STRING" || $sqlType == "TEXT") {
				if (strlen(trim($strVal)) == 0) {
					return "NULL";
				} else {
					return "'".mysql_escape_string($strVal)."'";
				}
			} else if ($sqlType == "STRING_I18N" || $sqlType == "TEXT_I18N") {
				if (is_null($strVal) || is_array($strVal) && count($strVal) == 0) {
					return "NULL";
				} else {
					$val = self::encodeI18NSqlValue($strVal);
					if (!is_null($val)) {
						return "'".mysql_escape_string($val)."'";
					} else {
						return "NULL";
					}
				}
			} else if ($sqlType == "DATA") {
				return "'".mysql_escape_string(serialize($strVal))."'";
			} else if ($sqlType == "BOOL") {
				if (isset($strVal) && $strVal != "") {
					if (strtolower($strVal) == "true") {
						return 1;
					} else if (strtolower($strVal) == "false") {
						return 0;
					} else {
						return $strVal;
					}
				} else {
					return 0;
				}
			} else if ($sqlType == "DATE") {
				return "'".date("Y-m-d H:i:s", $strVal)."'";
			} else {
				if (isset($strVal) && !is_null($strVal) && $strVal !== "") {
					return $strVal;
				} else {
					return "NULL";
				}
			}
		} else {
			return "NULL";
		}
	}

	public static function decodeSQLValue($strVal, $sqlType) {
		if (isset($strVal) && $strVal !== null) {
			if ($sqlType == "STRING" || $sqlType == "TEXT") {
				return $strVal;
			} else if ($sqlType == "STRING_I18N" || $sqlType == "TEXT_I18N") {
				return self::decodeI18NSqlValue($strVal);
			} else if ($sqlType == "DATA") {
				return unserialize($strVal);
			} else if ($sqlType == "BOOL")
			if (isset($strVal) && $strVal != "") {
				return ($strVal == "1");
			} else {
				return false;
			} else if ($sqlType == "INT") {
				if (isset($strVal) && $strVal != "") {
					return intval($strVal);
				} else {
					return 0;
				}
			} else if ($sqlType == "FLOAT") {
				if (isset($strVal) && $strVal != "") {
					return number_format((float)$strVal, 2, '.', '');
				} else {
					return 0;
				}
			} else if ($sqlType == "DATE") {
				if (isset($strVal) && $strVal != "") {
					return date($strVal);
				} else
				return null;
			} else {
				return $strVal;
			}
		} else {
			return null;
		}
	}

	public static function describeTable($sqlClass) {
		$tableInfo = array();

		// Get table info
		//$structure = $sqlClass::getConnection()->q('DESCRIBE '.$sqlClass::getMetadata("tableName"));
		$structure = $sqlClass::getConnection()->q('SHOW FULL COLUMNS FROM '.$sqlClass::getMetadata("tableName"));

		// Get table description
		foreach ($structure as $fields) {
			//print_r($fields);
			$typeAndLength = NP_MySQL_Toolkit::convertSQLType($fields["Type"], $fields['Comment']);
			$tableInfo[$fields["Field"]] = array(
				"TYPE" => $typeAndLength[0],
				"LENGTH" => $typeAndLength[1],
				"NULLABLE" => ($fields["Null"] === "YES"),
				"PK" => ($fields["Key"] === "PRI"),
				"DEFAULT" => $fields["Default"],
				"AUTO_INCREMENT" => strpos($fields["Extra"], 'auto_increment') !== false
			);
			//$sqlObject->$fields["Field"] = null;
		}

		return $tableInfo;
	}

	private static function convertSQLType($type, $comment) {
		$result = null;

		$t = array();
		preg_match('/^([^()]+)(\(([^()]+)\))?$/', $type, $t);
		$length = count($t) > 2 ? $t[3] : null;
		$type = $t[1];

		if ($type === "int") {
			$result = array ("INT", $length);
		} else if ($type === "float") {
			$result = array ("FLOAT", null);
		} else if ($type === "varchar") {
			if (strpos($comment,"NP:I18N") !== false) {
				$result = array ("STRING_I18N", $lenght);
			} else {
				$result = array ("STRING", $lenght);
			}
		} else if ($type === "text" || $type === "longtext") {
			$type = null;
			if (strpos($comment,"NP:DATA") !== false) {
				$result = array("DATA", $length);
			} else {
				if (strpos($comment,"NP:I18N") !== false) {
					$result = array ("TEXT_I18N", $lenght);
				} else {
					$result = array("TEXT", $length);
				}
			}
		} else if ($type === "boolean") {
			$result = array("BOOL", null);
		} else if ($type === "timestamp") {
			$result = array("DATE", $length);
		}

		return $result;
	}

	public static function loadObject($sqlObject) {
		// build SQL SELECT query
		$q = "SELECT ";

		// get only non-PK fields
		$sqlObject->walk(function($fName, $fValue, $fType, &$q) {
			if (!($fType["PK"])) {
				if ($sqlType == "DATE") {
					$q .= "UNIX_TIMESTAMP(".$fName.") AS ".$fName;
				} else {
					$q .= $fName.", ";
				}
			}
		}, array(&$q));
		$q = substr($q, 0, strlen($q) - 2);

		$q .= " FROM ".$sqlObject->getMetadata("tableName")." WHERE ";

		// restricted to PK fields
		$sqlObject->walk(function($fName, $fValue, $fType, &$q) {
			if (($fType["PK"])) {
				$q .= $fName." = ".NP_MySQL_Toolkit::encodeSQLValue($fValue, $fType["TYPE"])." AND ";
			}
		}, array(&$q));
		$q = substr($q, 0, strlen($q) - 4);

		// launch query
		$sqlObject->getConnection()->connect();
		$data = $sqlObject->getConnection()->query($q);
		$sqlObject->getConnection()->disconnect();

		// fill object with results
		if ($data !== null && count($data) > 0) {
			$objectStructure = $sqlObject->getMetadata("structure");
			foreach ($data[0] as $name => $value) {
				$sqlObject->$name = NP_MySQL_Toolkit::decodeSQLValue($value, $objectStructure[$name]["TYPE"]);
			}
			return true;
		} else {
			return false;
		}
	}

	public static function insertObject($sqlObject) {
		// build SQL INSERT query
		$q = "INSERT INTO ".$sqlObject->getMetadata("tableName")." (";

		$sqlObject->walk(function($fName, $fValue, $fType, &$q) {
			$q .= $fName.", ";
		}, array(&$q));
		$q = substr($q, 0, strlen($q) - 2);

		$q .= ") VALUES (";

		$sqlObject->walk(function($fName, $fValue, $fType, &$q) {
			$q .= NP_MySQL_Toolkit::encodeSQLValue($fValue, $fType["TYPE"]).", ";
		}, array(&$q));
		$q = substr($q, 0, strlen($q) - 2);
		$q .= ")";

		// launch query
		$sqlObject->getConnection()->connect();
		$auto_increment = $sqlObject->getConnection()->query($q);
		$sqlObject->getConnection()->disconnect();

		if ($auto_increment) {
			$objectStructure = $sqlObject->getMetadata("structure");
			foreach ($objectStructure as $name => $fStructure) {
				if ($fStructure["AUTO_INCREMENT"]) {
					$sqlObject->$name = $auto_increment;
				}
			}
			return $auto_increment;
		} else {
			return true;
		}
	}

	public static function updateObject($sqlObject) {
		// build SQL UPDATE query
		$q = "UPDATE ".$sqlObject->getMetadata("tableName")." SET ";

		// get only non-PK fields
		$sqlObject->walk(function($fName, $fValue, $fType, &$q) {
			if (!($fType["PK"])) {
				$q .= $fName." = ".NP_MySQL_Toolkit::encodeSQLValue($fValue, $fType["TYPE"]).", ";
			}
		}, array(&$q));
		$q = substr($q, 0, strlen($q) - 2);

		$q .= " WHERE ";

		// restricted to PK fields
		$sqlObject->walk(function($fName, $fValue, $fType, &$q) {
			if (($fType["PK"])) {
				$q .= $fName." = ".NP_MySQL_Toolkit::encodeSQLValue($fValue, $fType["TYPE"])." AND ";
			}
		}, array(&$q));
		$q = substr($q, 0, strlen($q) - 4);

		// launch query
		$sqlObject->getConnection()->connect();
		$result = $sqlObject->getConnection()->query($q);
		$sqlObject->getConnection()->disconnect();

		return $result === 1;
	}

	public static function deleteObject($sqlObject) {
		// build SQL DELETE query
		$q = "DELETE FROM ".$sqlObject->getMetadata("tableName")." WHERE ";

		// restricted to PK fields
		$sqlObject->walk(function($fName, $fValue, $fType, &$q) {
			if (($fType["PK"])) {
				$q .= $fName." = ".NP_MySQL_Toolkit::encodeSQLValue($fValue, $fType["TYPE"])." AND ";
			}
		}, array(&$q));
		$q = substr($q, 0, strlen($q) - 4);

		// launch query
		$sqlObject->getConnection()->connect();
		$result = $sqlObject->getConnection()->query($q);
		$sqlObject->getConnection()->disconnect();

		return $result === 1;
	}

	public static function listObjects($sqlClass, $query = null) {
		// if not query given, return all records
		if ($query == null) {
			$query = "SELECT * FROM ".$sqlClass::getMetadata("tableName");
		}

		// launch query
		$conn = $sqlClass::getConnection();
		$conn->connect();
		$data = $conn->query($query);
		$conn->disconnect();

		// fill list of object with results
		if ($data !== null && count($data) > 0) {
			$result = array();
			$structure = $sqlClass::getMetadata("structure");
			foreach ($data as $row) { 
				$obj = new $sqlClass();
				foreach ($row as $name => $value) {
					$obj->$name = NP_MySQL_Toolkit::decodeSQLValue($value, $structure[$name]["TYPE"]);
				}
				$result[] = $obj;
			}
			return $result;
		} else {
			return null;
		}
	}
}
?>