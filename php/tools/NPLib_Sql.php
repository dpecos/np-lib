<?php
/** 
 * NPLib - PHP
 * 
 * Database API - Deprecated!
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
 * @ignore
 */
$npsql_dbconfig = null;

/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function __createSELECT_Column($colName, $sqlType) {
    if ($sqlType == "DATE") {
        return "DATE_FORMAT(".$colName.", '%Y%m%d%H%i%s') AS ".$colName;
    } else
        return $colName;
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function __createSELECT_AllColumns($obj, $field, $ddbb_mapping, $ddbb_types, &$first) {
    $sql = "";
  
    if ($obj != null) {      
        
        if ($field != null) {
            if (is_object($obj)) 
                $obj = $obj->$field;   
            else
                $obj = $obj[$field];
        }
        
        if (is_object($obj)) {
            //echo "O - ".gettype($obj)."<br>\n";
            $vars = get_object_vars($obj);
            foreach (array_keys($vars) as $var) {
                if (array_key_exists($var, $ddbb_mapping)) {
                    $sql .= __createSELECT_AllColumns($obj, $var, $ddbb_mapping[$var], $ddbb_types[$var], $first);
                }
            }
        } else if (is_array($obj)) {
            //echo "A - ".$obj."<br>\n";
            foreach (array_keys($obj) as $var) {
        		if (array_key_exists($var, $ddbb_mapping)) {
        	        $sql .= __createSELECT_AllColumns($obj, $var, $ddbb_mapping[$var], $ddbb_types[$var], $first);
        		}
        	}
        } else {
            //echo ". - ".$obj."<br>\n";
            if (!$first) {
				$sql .= ", ";
			} else
				$first = false;
			$sql .= __createSELECT_Column($ddbb_mapping, $ddbb_types);
        }
        
    }
	return $sql;
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function NP_createSELECT($obj, $ddbb_table, $ddbb_mapping, $ddbb_types, $whereCondition) {
    
   $isFirst = true;
	$sql = "SELECT ";
	$sql .= __createSELECT_AllColumns($obj, null, $ddbb_mapping, $ddbb_types, $isFirst);
	$sql .= " FROM ".$ddbb_table." WHERE ".$whereCondition;
    
   return $sql;
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function NP_loadData(&$obj, &$data, $ddbb_mapping, $ddbb_types) {
   //$object_name = get_class($object);
	foreach (array_values($ddbb_mapping) as $dbFieldName) {
	
		$objectFieldName = _obtainKeyForValue($ddbb_mapping, $dbFieldName);

		if ($objectFieldName == null) {
		    return;
		}
		
		if (is_array($objectFieldName)) {
		    
		    if (is_object($obj)) {
		        NP_loadData($obj->$objectFieldName[0], $data, $ddbb_mapping[$objectFieldName[0]], $ddbb_types[$objectFieldName[0]]);
		    } else {
		        NP_loadData($obj[$objectFieldName[0]], $data, $ddbb_mapping[$objectFieldName[0]], $ddbb_types[$objectFieldName[0]]);
		    }
		    
		} else {
		    if (is_array($data)) {  
		       if (is_object($obj)) {
		         if (in_array($dbFieldName, array_keys($data))) {
       			   $obj->$objectFieldName = decodeSQLValue($data[$dbFieldName], $ddbb_types[$objectFieldName]);	
       			} else {
          			$obj->$objectFieldName = decodeSQLValue(null, $ddbb_types[$objectFieldName]);	
       			}
			   } else if (is_array($obj)) {
               if (in_array($dbFieldName, array_keys($data))) {
       			   $obj[$objectFieldName] = decodeSQLValue($data[$dbFieldName], $ddbb_types[$objectFieldName]);	
       			} else {
       				$obj[$objectFieldName] = decodeSQLValue(null, $ddbb_types[$objectFieldName]);	
       			}
			   }
			}
			
			unset($data[$dbFieldName]);
			
		}
		
	}
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function NP_insertObject($object, $ddbb_table, $ddbb_mapping, $ddbb_types) {    	
		$varNames = "";
		$varValues = "";
		$first = true;	
		$object_name = get_class($object);
		
		foreach (get_object_vars($object) as $var => $value) {
			if (array_key_exists($var, $ddbb_mapping[$object_name])) {
				if (is_array($ddbb_mapping[$object_name][$var])) {
					foreach (get_object_vars($this->$var) as $objvar => $objvalue) {
						if (array_key_exists($objvar, $ddbb_mapping[$object_name][$var])) {
							if (is_array($ddbb_mapping[$object_name][$var][$objvar])) {
								foreach ($this->$var->$objvar as $subobjvar => $subobjvalue) {
									if (!$first) {
										$varNames .= ", ";
										$varValues .= ", ";
									} else
										$first = false;
									$varNames .= $ddbb_mapping[$object_name][$var][$objvar][$subobjvar];
									$varValues .= encodeSQLValue($subobjvalue, $ddbb_types[$object_name][$var][$objvar][$subobjvar]);
								}
							} else {
								if (!$first) {
									$varNames .= ", ";
									$varValues .= ", ";
								} else
									$first = false;
								$varNames .= $ddbb_mapping[$object_name][$var][$objvar];
								$varValues .= encodeSQLValue($objvalue, $ddbb_types[$object_name][$var][$objvar]);
							}
						}
					}
				} else {
					if ($value !== null) {
						if (!$first) {
							$varNames .= ", ";
							$varValues .= ", ";
						} else
							$first = false;
						$varNames .= $ddbb_mapping[$object_name][$var];
						$varValues .= encodeSQLValue($value, $ddbb_types[$object_name][$var]);
					}
				}
			} else {
				//TODO: ERROR
			}
		}
		$sql = "INSERT INTO ".$ddbb_table[$object_name]." ($varNames) VALUES ($varValues)";	
		
		return NP_executeInsertUpdate($sql);
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function encodeSQLValue($strVal, $sqlType) {
	if (isset($strVal) && $strVal !== null) {
		if ($sqlType == "STRING") {
		   if (strlen(trim($strVal)) == 0)
		      return "NULL";
		   else
   			return "'".$strVal."'";
		} else if ($sqlType == "BOOL") {
			if (isset($strVal) && $strVal != "") {
			    if (strtolower($strVal) == "true")
			        return 1;
			    else if (strtolower($strVal) == "false")
				    return 0;
				else 
				    return $strVal;
				  
			} else
				return 0;
		} else if ($sqlType == "DATE") {
		    return "'".date("Y-m-d H:i:s", $strVal)."'";
		} else {
			if (isset($strVal) && $strVal != "")
				return $strVal;
			else 
				return 0;
		}
	} else {
		return "NULL";
	}
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function decodeSQLValue($strVal, $sqlType) {
	if (isset($strVal) && $strVal !== null) {
		if ($sqlType == "STRING") 
			return $strVal;
		else if ($sqlType == "BOOL") 
			if (isset($strVal) && $strVal != "")
				return ($strVal == "1");
			else
				return false;
		else if ($sqlType == "INT")
			if (isset($strVal) && $strVal != "")
				return intval($strVal);
			else 
				return 0;
	    else if ($sqlType == "FLOAT")
	    	if (isset($strVal) && $strVal != "")
				return number_format((float)$strVal, 2, '.', '');
			else 
				return 0;
	    else if ($sqlType == "DATE") {
	        if (isset($strVal) && $strVal != "") {
	             $year = substr($strVal,0,4);
                $mon  = substr($strVal,5,2);
                $day  = substr($strVal,8,2);
                $hour = substr($strVal,11,2);
                $min  = substr($strVal,14,2);
                $sec  = substr($strVal,17,2);
                //echo $year.$mon.$day.$hour.$min.$sec;
                //return date("l F dS, Y h:i A",mktime($hour,$min,$sec,$mon,$day,$year));	            
                return mktime($hour,$min,$sec,$mon,$day,$year);	            
	        } else
	            return null;
	    } else 
	        return $strVal;
	} else {
		return null;
	}
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function __NP_initDDBB($dbconfig) {
	global $npsql_dbconfig;
	
	if ($npsql_dbconfig == null)
		$npsql_dbconfig = $dbconfig;
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function __NP_connectSQL () {
	global $npsql_dbconfig;

	$db_con = mysql_connect ($npsql_dbconfig ["HOST"], $npsql_dbconfig ["USER"], $npsql_dbconfig ["PASSWD"])
		or die ("No se pudo conectar con la BBDD: ".mysql_error());
	mysql_select_db ($npsql_dbconfig ["NAME"])
		or die ("No se encontró la BBDD en el servidor.");
	return $db_con;
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function __NP_disconnectSQL ($db_con) {
	//mysql_close($db_con);
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function NP_executePKSelect($sql) {
	$con = __NP_connectSQL();

	$resultado = mysql_query($sql);

	if (!$resultado) {
		echo "No pudo ejecutarse satisfactoriamente la consulta ($sql) en la BD: " . mysql_error();
		exit;
	}

	$datos = mysql_fetch_assoc ($resultado);

	mysql_free_result($resultado);
		
	__NP_disconnectSQL($con);
	return $datos;
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function NP_executeSelect($sql, $f, $params = null) {
	if ($params == null)
		$params = array();

	$con = __NP_connectSQL();

	$resultado = mysql_query($sql);

	if (!$resultado) {
		echo "No pudo ejecutarse satisfactoriamente la consulta ($sql) en la BD: " . mysql_error();
		exit;
	}

	while ($datos = mysql_fetch_assoc ($resultado)) {
		$func = new ReflectionFunction($f);
		$p = array_merge(array($datos), $params);
		$func->invokeArgs($p);
		//$f($datos, $params);
	}

	mysql_free_result($resultado);
		
	__NP_disconnectSQL($con);
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function NP_executeInsertUpdate($sql) {
	$con = __NP_connectSQL();
		
	$resultado = mysql_query($sql);
	
	if (!$resultado) {
		echo "No pudo ejecutarse satisfactoriamente la consulta ($sql) en la BD: " . mysql_error();
		exit;
	}
	
	$id = mysql_insert_id();
	
	__NP_disconnectSQL($con);
	
	return $id;
}
/**
 * @ignore
 * @deprecated Use NP_DDBB object instead
 */
function NP_executeDelete($sql) {
	$con = __NP_connectSQL();
		
	mysql_query($sql);
	
	$resultado = mysql_affected_rows($con); 
	
	__NP_disconnectSQL($con);
	
	return $resultado;
}
?>
