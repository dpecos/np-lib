<?
/** 
 * NPLib - PHP
 * 
 * Database API
 * 
 * @package np-lib
 * @subpackage 
 * @version 20090624
 * 
 * @author Daniel Pecos Martínez
 * @copyright Copyright (c) Daniel Pecos Martínez 
 * @license http://www.gnu.org/licenses/lgpl.html  LGPL License
 */
require_once("NPLib_Common.php");

class NP_DDBB {
   
   var $config;
   var $dbCon;
   
   var $dbMappings;
   var $dbTypes;
   var $dbTables;
   var $dbSQL;
   
   /**
    * Initializes an NP_DDBB instance with known object-relation data
    * @param array $dbconfig Array containing database connection settings
    * @param array $dbMappings Array containing mappings between object members and table fields
    * @param array $dbTypes Array containing class member SQL data types
    * @param array $dbTables Array containing mappings between object and tables
    * @param array $dbSQL Array containing object member SQL additional parameters
    */
   function __construct ($dbconfig = null, $dbMappings = null, $dbTypes = null, $dbTables = null, $dbSQL = null) {
	   $this->setDDBBConfig($dbconfig);
	   
	   if ($dbMappings == null) {
	      $this->dbMappings = array();
         $this->dbTypes = array();
         $this->dbTables = array();
         $this->dbSQL = array();
      } else {
         $this->dbMappings = $dbMappings;
         $this->dbTypes = $dbTypes;
         $this->dbTables = $dbTables;
         $this->dbSQL = $dbSQL;
      }
   }
   
   /**
    * Sets database connection data
    * @param array $dbconfig Array containing database connection settings
    */
   function setDDBBConfig($dbconfig) {
      $this->config = $dbconfig;
   }
   
   /**
    * Return if the object is initialized with database connection data
    * @return boolean
    */
   function isInitialized() {
      return $this->config != null;
   }
   
   /**
    * Adds object-relation data about $objType class
    * @param string $objType Class name of the object-relation data
    * @param string $sqlTable Table name used to store objectos of given class name type
    * @param array $dbMappings Array containing mappings between class members and table fields
    * @param array $dbTypes Array containing object member SQL data types
    * @param array $sqlInfo Array containing object member SQL additional parameters
    */
   function addConfig($objType, $sqlTable, $sqlMappings, $sqlTypes, $sqlInfo = null) {
      $this->dbMappings[$objType] = $sqlMappings;
      $this->dbTypes[$objType] = $sqlTypes;
      $this->dbTables[$objType] = $sqlTable;
      $this->dbSQL[$objType] = $sqlInfo === null ? array() : $sqlInfo;
   }
   
   /**
    * Associates one class name with a table name
    * @param string $objType Class name of the object-relation data
    * @param string $sqlTable Table name used to store objectos of given class name type
    */
   function addTable($objType, $sqlTable) {
      $this->addConfig($objType, $sqlTable, array(), array(), array());
   }
   
   /**
    * Adds object-relation data for a property of an already associated class
    * @param string $objType Class name of the object-relation data
    * @param string $fieldName Name of the property of the class
    * @param string $sqlFieldName Name of the field in the table for $fieldName
    * @param string $sqlType SQL type for field $sqlFieldName
    * @param array $sqlInfo Additions SQL info for field $sqlFieldName
    */
   function addField($objType, $fieldName, $sqlFieldName, $sqlType, $sqlInfo) {
      if (array_key_exists($objType, $this->dbMappings)) {
         $this->dbMappings[$objType][$fieldName] = $sqlFieldName != null ? $sqlFieldName : $fieldName;
         $this->dbTypes[$objType][$fieldName] = $sqlType;
         $this->dbSQL[$objType][$fieldName] = $sqlInfo;
      } else {
         die("Unknown type ".$objType);
      }
   }
   
   function getTable($objType) { 
      return $this->config["PREFIX"].$this->dbTables[$objType];
   }
    
   function getMapping($objType, $fieldName) { 
      if ($fieldName == null)
         return $this->dbMappings[$objType];
      else
         return $this->dbMappings[$objType][$fieldName];
   }
      
   function getType($objType, $fieldName) { 
      if ($fieldName == null)
         return $this->dbTypes[$objType];
      else
        return $this->dbTypes[$objType][$fieldName];
   }
   
   function getSQLInfo($objType, $fieldName) { 
      if ($fieldName == null)
         return $this->dbSQL[$objType];
      else
        return $this->dbSQL[$objType][$fieldName];
   }
     
   function buildSELECT($obj, $whereCondition) {
      $isFirst = true;
      $objName = get_class($obj);
      $sql = "SELECT ";
      $sql .= $this->__createSELECT_AllColumns($obj, null, $this->getMapping($objName, null), $this->getType($objName, null), $isFirst);
      $sql .= " FROM ".$this->getTable($objName)." WHERE ".$whereCondition;
    
      return $sql;
   }
   
   function __createSELECT_Column($colName, $sqlType) {
      if ($sqlType == "DATE") {
        return "UNIX_TIMESTAMP(".$colName.") AS ".$colName;
      } else
        return $colName;
   }    
   
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
            $vars = get_object_vars($obj);
            foreach (array_keys($vars) as $var) {
                if (array_key_exists($var, $ddbb_mapping)) {
                    $sql .= $this->__createSELECT_AllColumns($obj, $var, $ddbb_mapping[$var], $ddbb_types[$var], $first);
                }
            }
         } else if (is_array($obj)) {
            foreach (array_keys($obj) as $var) {
	            if (array_key_exists($var, $ddbb_mapping)) {
                    $sql .= $this->__createSELECT_AllColumns($obj, $var, $ddbb_mapping[$var], $ddbb_types[$var], $first);
	            }
            }
         } else {
            if (!$first) {
               $sql .= ", ";
            } else {
               $first = false;
            }
            $sql .= $this->__createSELECT_Column($ddbb_mapping, $ddbb_types);
         }

      }
      return $sql;
   }

   function loadData(&$obj, &$data, $ddbbMapping = null, $ddbbTypes = null) {
   
      $object_name = get_class($obj);
      if ($ddbbMapping != null && $ddbbTypes != null) {
         $ddbb_mapping = $ddbbMapping;
         $ddbb_types = $ddbbTypes;
      } else {
         $ddbb_mapping = $this->dbMappings[$object_name];
         $ddbb_types = $this->dbTypes[$object_name];
      }

	  foreach ($ddbb_mapping as $objectFieldName => $dbFieldName) {
	
		   /*$objectFieldName = _obtainKeyForValue($ddbb_mapping, $dbFieldName);

		   if ($objectFieldName == null) {
		       continue;
		   }*/
		
		   if (is_array($dbFieldName)) {
		       if (is_object($obj)) {
		           $this->loadData($obj->$objectFieldName, $data, $ddbb_mapping[$objectFieldName], $ddbb_types[$objectFieldName]);
		       } else {
		           $this->loadData($obj[$objectFieldName], $data, $ddbb_mapping[$objectFieldName], $ddbb_types[$objectFieldName]);
		       }
		       
		   } else {
		       if (is_array($data)) {  
		          if (is_object($obj)) {
		            if (in_array($dbFieldName, array_keys($data))) {
          			   $obj->$objectFieldName = NP_DDBB::decodeSQLValue($data[$dbFieldName], $ddbb_types[$objectFieldName]);	
          			} else {
             			$obj->$objectFieldName = NP_DDBB::decodeSQLValue(null, $ddbb_types[$objectFieldName]);	
          			}
			      } else if (is_array($obj)) {
                    if (in_array($dbFieldName, array_keys($data))) {
          			   $obj[$objectFieldName] = NP_DDBB::decodeSQLValue($data[$dbFieldName], $ddbb_types[$objectFieldName]);	
          			} else {
          				$obj[$objectFieldName] = NP_DDBB::decodeSQLValue(null, $ddbb_types[$objectFieldName]);	
          			}
			      }
			   }
			
			   unset($data[$dbFieldName]);
			
		   }
		
	   }
   }  
   
   
   function __createInsertValuesList($ddbb_mapping, $ddbb_types, $ddbb_sql, $object, $var, $value) {
       $varNames = array();
       $varValues = array();
       $pkNames = array();
       
       $object_name = get_class($object);
       
       if ($ddbb_mapping === null) {    
           
           $ddbb_mapping = $this->getMapping($object_name, null);
           $ddbb_types = $this->getType($object_name, null);
           $ddbb_sql = $this->getSQLInfo($object_name, null);
    	
    	   foreach (get_object_vars($object) as $var => $value) {
    		   if (array_key_exists($var, $ddbb_mapping)) {
    		       $data = $this->__createInsertValuesList($ddbb_mapping, $ddbb_types, $ddbb_sql, $object, $var, $value);
    		       $varNames = array_merge_recursive($varNames, $data[0]);
    		       $varValues = array_merge_recursive($varValues, $data[1]);
    		       $pkNames = array_merge_recursive($pkNames, $data[2]);
    		   }
    	   }
	   } else {
           if (is_array($ddbb_mapping[$var])) {
            
               $iter = is_object($value) ? get_object_vars($value) : $value;
    
               foreach ($iter as $objvar => $objvalue) {
    		      if (array_key_exists($objvar, $ddbb_mapping[$var])) {
        	        $data = $this->__createInsertValuesList($ddbb_mapping[$var], $ddbb_types[$var], $ddbb_sql, $value, $objvar, $objvalue);
    	            $varNames = array_merge_recursive($varNames, $data[0]);
    	            $varValues = array_merge_recursive($varValues, $data[1]);
    	            $pkNames = array_merge_recursive($pkNames, $data[2]);
    	          }
    	       } 
           } else {
        	   if ($value !== null) {
        	   	   if (!array_key_exists("FOREIGN_FIELD", $ddbb_sql[$var]) || array_key_exists("FOREIGN_FIELD", $ddbb_sql[$var]) && !$ddbb_sql[$var]["FOREIGN_FIELD"]) { 
	        		   $varNames[] = $ddbb_mapping[$var];
	        		   $varValues[] = NP_DDBB::encodeSQLValue($value, $ddbb_types[$var]);
	        		   if (strlen($object_name) > 0 &&
	        		       $ddbb_sql != null && 
	        		       array_key_exists($var, $ddbb_sql) && 
	        		       array_key_exists("PK", $ddbb_sql[$var]) && 
	        		       $ddbb_sql[$var]["PK"])
	        		      $pkNames[$var] = $ddbb_mapping[$var];
        	   	   }
        	   }
           }
       }
       return array($varNames, $varValues, $pkNames);
   }
   
   function insertObject($object, $returnSQL = false) {    
       $varNames = null;
       $varValues = null;
       
       $data = $this->__createInsertValuesList(null, null, null, $object, null, null);
       $varNames = $data[0];
       $varValues = $data[1];
       
		       
	   $object_name = get_class($object);
	   $sql = "INSERT INTO ".$this->getTable($object_name)." (";
	   for ($i=0; $i < count($varNames); $i++) {
	      if ($i !== 0) {
	          $sql .= ",";
	      } 
	      $sql .= "`".$varNames[$i]."`";
	   }
	   $sql .= ") VALUES (";
	   for ($i=0; $i < count($varValues); $i++) {
	      if ($i !== 0) {
	          $sql .= ", ";
	      } 
	      $sql .= $varValues[$i];
	   }
	   $sql .= ")";	
	  
	   if ($returnSQL) 
	      return $sql;
	   else
   	      return $this->executeInsertUpdateQuery($sql);
   }   
/*   function insertObject($object, $returnSQL = false) {    
   
       $object_name = get_class($object);
       
       $ddbb_mapping = $this->getMapping($object_name, null);
       $ddbb_types = $this->getType($object_name, null);
   	
	   $varNames = "";
	   $varValues = "";
	   $first = true;	
	
	   foreach (get_object_vars($object) as $var => $value) {
		   if (array_key_exists($var, $ddbb_mapping)) {
			   if (is_array($ddbb_mapping[$var])) {
				   foreach (get_object_vars($value) as $objvar => $objvalue) {
					   if (array_key_exists($objvar, $ddbb_mapping[$var])) {
						   if (is_array($ddbb_mapping[$var][$objvar])) {
							   foreach ($object->$var->$objvar as $subobjvar => $subobjvalue) {
								   if (!$first) {
									   $varNames .= ", ";
									   $varValues .= ", ";
								   } else
									   $first = false;
								   $varNames .= "`".$ddbb_mapping[$var][$objvar][$subobjvar]."`";
								   $varValues .= NP_DDBB::encodeSQLValue($subobjvalue, $ddbb_types[$var][$objvar][$subobjvar]);
							   }
						   } else {
							   if (!$first) {
								   $varNames .= ", ";
								   $varValues .= ", ";
							   } else
								   $first = false;
							   $varNames .= "`".$ddbb_mapping[$var][$objvar]."`";
							   $varValues .= NP_DDBB::encodeSQLValue($objvalue, $ddbb_types[$var][$objvar]);
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
					   $varNames .= "`".$ddbb_mapping[$var]."`";
					   $varValues .= NP_DDBB::encodeSQLValue($value, $ddbb_types[$var]);
				   }
			   }
		   } else {
			   //TODO: ERROR
		   }
	   }
	   $sql = "INSERT INTO ".$this->getTable($object_name)." ($varNames) VALUES ($varValues)";	

	   if ($returnSQL) 
	      return $sql;
	   else
   	      return $this->executeInsertUpdateQuery($sql);
   }*/

   function updateObject($object, $returnSQL = false) {    
       global $ddbb;
       $varNames = null;
       $varValues = null;
       
       $data = $this->__createInsertValuesList(null, null, null, $object, null, null);
       $varNames = $data[0];
       $varValues = $data[1];
       $pkNames = $data[2];
         
       $fields = array_combine($varNames, $varValues);
		       
	   $object_name = get_class($object);
	   $sql = "UPDATE ".$this->getTable($object_name)." SET ";
	   $first = true;
	   $sqlWhere = "";
	   $firstWhere = true;
	   
	   foreach ($fields as $name => $value) {
          $pkFieldName = array_search ($name, $pkNames);
	      if (strlen($pkFieldName) > 0) {
	        if (!$firstWhere) {
                $sqlWhere .= " AND ";
            } 
            $firstWhere = false;
            $sqlWhere .= "`".$name."`=".$value;
	      } else {
            if (!$first) {
                $sql .= ", ";
            } 
            $first = false;
            $sql .= "`".$name."`=".$value;
	      }
	   }
	   $sql .= " WHERE ".$sqlWhere;

	   if ($returnSQL) 
	      return $sql;
	   else
   	      return $this->executeInsertUpdateQuery($sql);
   }
   
   function deleteObject($object, $returnSQL = false) {
	   $varNames = null;
       $varValues = null;
       
       $data = $this->__createInsertValuesList(null, null, null, $object, null, null);
       $varNames = $data[0];
       $varValues = $data[1];
       $pkNames = $data[2];
         
       $fields = array_combine($varNames, $varValues);
		       
	   $object_name = get_class($object);
	   $sql = "DELETE FROM ".$this->getTable($object_name);
	   $first = true;
	   $sqlWhere = "";
	   $firstWhere = true;
	   
	   foreach ($fields as $name => $value) {
          $pkFieldName = array_search ($name, $pkNames);
	      if (strlen($pkFieldName) > 0) {
	        if (!$firstWhere) {
                $sqlWhere .= " AND ";
            } 
            $firstWhere = false;
            $sqlWhere .= "`".$name."`=".$value;
	      } else {
            if (!$first) {
                $sql .= ", ";
            } 
            $first = false;
            $sql .= "`".$name."`=".$value;
	      }
	   }
	   $sql .= " WHERE ".$sqlWhere;

	   if ($returnSQL) 
	      return $sql;
	   else
   	      return $this->executeDeleteQuery($sql);
   }

   static function decodeI18NSqlValue($str) {
        $matches = array();
        if ((defined("NP_LANG") || defined("NP_DEFAULT_LANG")) && preg_match_all('/#(.?.?_.?.?)@([^#]*)#/', $str, $matches)) {
            $langs = $matches[1];
            $strings = $matches[2];
            
            $translations = array(); 
            
            foreach ($langs as $idx => $lang) {
                $translations[$lang] = $strings[$idx];
            }
            
            return $translations;
          
        	/*$i_lang = array_search(NP_LANG, $langs);
        	$i_default_lang = array_search(NP_DEFAULT_LANG, $langs);
        	
        	if ($i_lang !== FALSE)
        	    return $strings[$i_lang];
        	else if ($i_default_lang !== FALSE)
        	    return $strings[$i_default_lang];
        	else 
        	    return $str;*/
        	
        } else 
            return $str;
   }
    
   static function encodeI18NSqlValue($strings) {
        if (is_array($strings)) {
            $string = "";
            foreach ($strings as $lang => $value) {
                if (!is_null($value) && $value !== "")
                    $string .= "#".$lang."@".$value."#";
            }
            if (trim($string) === "")
                return null;
            else
                return $string;
        } else
            return $strings;
   }

   static function encodeSQLValue($strVal, $sqlType) {
       if (!is_array($strVal))
           $strVal = trim($strVal);

	   if (isset($strVal) && $strVal !== null) {
		   if ($sqlType == "STRING" || $sqlType == "TEXT") {
		      if (strlen(trim($strVal)) == 0)
		         return "NULL";
		      else
      			return "'".mysql_escape_string($strVal)."'";
      	   } else if ($sqlType == "STRING_I18N" || $sqlType == "TEXT_I18N") {
      	      if (is_null($strVal) || is_array($strVal) && count($strVal) == 0)
		         return "NULL";
		      else {
		        $val = NP_DDBB::encodeI18NSqlValue($strVal);
		        if (!is_null($val))
      			    return "'".mysql_escape_string($val)."'";
      			else 
      			    return "NULL";
      		  }
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
			   if (isset($strVal) && !is_null($strVal) && $strVal !== "")
				   return $strVal;
			   else 
				   return "NULL";
		   }
	   } else {
		   return "NULL";
	   }
   }
   
   static function decodeSQLValue($strVal, $sqlType) {
	   if (isset($strVal) && $strVal !== null) {
		   if ($sqlType == "STRING" || $sqlType == "TEXT") {
		       return $strVal;
		   } else if ($sqlType == "STRING_I18N" || $sqlType == "TEXT_I18N") {
		       return NP_DDBB::decodeI18NSqlValue($strVal);
		   } else if ($sqlType == "BOOL") 
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
                   return date($strVal);       
	           } else
	               return null;
	       } else 
	           return $strVal;
	   } else {
		   return null;
	   }
   }

   function connectServer () {
      if ($this->config != null) {
	      $this->dbCon = mysql_connect ($this->config ["HOST"], $this->config ["USER"], $this->config ["PASSWD"])
		      or die ("No se pudo conectar con la BBDD: ".mysql_error());
	      mysql_select_db ($this->config ["NAME"])
		      or die ("No se encontro la BBDD en el servidor.");
      }
   }

   function disconnectServer () {
      if ($this->dbCon != null) {
   	   mysql_close($this->dbCon);
   	   $this->dbCon = null;
	   }
   }

   function executePKSelectQuery($sql) {
	   $this->connectServer();

	   $resultado = mysql_query($sql);

	   if (!$resultado) {
		   die("No pudo ejecutarse satisfactoriamente la consulta ($sql) en la BD: " . mysql_error());
		   //exit;
	   }

	   $datos = mysql_fetch_assoc ($resultado);
	   $count = mysql_affected_rows();

	   mysql_free_result($resultado);
		
	   $this->disconnectServer();
	   if ($count > 0) 
	       return $datos;
	   else
	       return null;
   }

	function executeSelectQuery($sql, $f = null, $params = null) {
		if ($params == null)
		$params = array();

		$this->connectServer();

		$queryId = null;
		if (Logger::isEnabled()) {
			$queryId = NPLogger::prepareSQLQuery("nplib", $sql);
		}

		$resultado = mysql_query($sql);

		if (!$resultado) {
			$msg = "No pudo ejecutarse satisfactoriamente la consulta ($sql) en la BD: " . mysql_error();
			NPLogger::error("nplib", $msg);
			die($msg);
			//exit;
		}

		if (Logger::isEnabled()) {
			NPLogger::logSQLQuery("nplib", $sql, $queryId, null);
			//$queryId = Logger::logSQLQuery($sql, $queryId, $this->$dbCon);
		}

		$data = null;
		if (isset($f) && $f != null && function_exists($f)) {
			while ($datos = mysql_fetch_assoc ($resultado)) {
				$func = new ReflectionFunction($f);
				$p = array_merge(array($datos), $params);
				$func->invokeArgs($p);
				//$f($datos, $params);
			}
		} else {
			$data = array();
			while ($datos = mysql_fetch_assoc ($resultado)) {
				$data = array_merge($data, array($datos));
			}
		}

		mysql_free_result($resultado);

		$this->disconnectServer();

		if ($data != null)
			return $data;
		else
			return null;
	}


   function executeInsertUpdateQuery($sql) {
	   $this->connectServer();
		
	   $resultado = mysql_query($sql);
	
	   if (!$resultado) {
		   die("No pudo ejecutarse satisfactoriamente la consulta ($sql) en la BD: " . mysql_error());
		   //exit;
	   }
	
	   $id = mysql_insert_id();
	
	   $this->disconnectServer();
	
	   return $id;
   }

   function executeDeleteQuery($sql) {
	   $this->connectServer();
		
	   mysql_query($sql);
	
	   $resultado = mysql_affected_rows($this->dbCon); 
	
	   $this->disconnectServer();
	
	   return $resultado;
   }    
  
   function createSQLCreateTable($data = false, $type = null) {
      if (isset($type)) {
         if ($this->dbSQL[$type] != null) {
            $sql = "CREATE TABLE IF NOT EXISTS `".$this->getTable($type)."` ("."\n";
            $pk = array();
            foreach ($this->dbMappings[$type] as $key => $fName) {
               $fType = $this->dbTypes[$type][$key];
               if (isset($this->dbSQL[$type][$key]["PK"]) && $this->dbSQL[$type][$key]["PK"]===true)
                  $pk = array_merge($pk, array("`".$fName."`"));
               switch ($fType) {
                  case "INT": $fType = "int"; break;
                  case "FLOAT": $fType = "float"; break;
                  case "STRING": $fType = "varchar"; break;
                  case "TEXT": $fType = "text"; break;
                  case "BOOL": $fType = "boolean"; break;             
                  case "DATE": $fType = "timestamp"; break;
               }
               $sql .= "  `".$fName."` ".$fType;
               $sql .= array_key_exists("LENGTH", $this->dbSQL[$type][$key]) ? "(".$this->dbSQL[$type][$key]["LENGTH"].") " : " ";
               $sql .= array_key_exists("NULLABLE", $this->dbSQL[$type][$key]) ? $this->dbSQL[$type][$key]["NULLABLE"]===true ? "NULL " : "NOT NULL " : "";
               $sql .= (array_key_exists("AUTO_INCREMENT", $this->dbSQL[$type][$key]) && $this->dbSQL[$type][$key]["AUTO_INCREMENT"] === true) ? "auto_increment " : "";
               $sql .= array_key_exists("DEFAULT", $this->dbSQL[$type][$key]) ? $this->dbSQL[$type][$key]["DEFAULT"] === null ? "default NULL " : ($this->dbSQL[$type][$key]["DEFAULT"] === "CURRENT_TIMESTAMP" ? "default CURRENT_TIMESTAMP " : "default '".$this->dbSQL[$type][$key]["DEFAULT"]."' ") : "";
               $sql .= ",\n"; 
            }
            $sql .= "  PRIMARY KEY(".implode(",",$pk).")\n";
            $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1;\n";
            if ($data) {
               $sql .= $this->createSQLDataTable($type);
            }
            return $sql;
         } else {
            return "-- NP_SQL: No SQL data for type '$type'";
         }
      } else {
         $sql = "";
         foreach (array_keys($this->dbSQL) as $type) {
            $tmp = $this->createSQLCreateTable($data, $type);
            if ($tmp != null) {
               $sql .= $tmp;
               if (!$data)
                  $sql .= "\n\n-- -----------------\n\n";
            }
         }
         return $sql;
      }
   }
   
   function createSQLTruncateTable($type = null) {
      $sql = "";
      if (isset($type)) {
         if ($this->dbSQL[$type] != null) {
            $sql = "TRUNCATE TABLE `".$this->getTable($type)."`;"."\n";
         } else {
            return "-- NP_SQL: No SQL data for type '$type'";
         }
      } else {
         foreach (array_keys($this->dbSQL) as $type) {
            $sql .= $this->createSQLTruncateTable($type);
         }
      }
      return $sql;
   }
   
   function createSQLDataTable($type = null) {
      $sql = "";
      if (isset($type)) {
         if ($this->dbSQL[$type] != null) {     
            $sql .= "\n-- Data for '".$this->getTable($type)."' --\n";
           
            $inserts = array();
            $handler = create_function('$data, $type, $inserts, $ddbb', '$obj = new $type(); $ddbb->loadData($obj, $data); $inserts = array_merge($inserts, array($ddbb->insertObject($obj, true)));');
            $this->executeSelectQuery("SELECT * FROM ".$this->getTable($type), $handler, array($type, &$inserts, $this));
            if (count($inserts) > 0)
               $sql .= implode(";\n", $inserts).";";
           
            $sql .= "\n\n-- -----------------\n\n";
         } else {
            $sql .= "-- NP_SQL: No SQL data for type '$type'\n";
         }
      } else {
         foreach (array_keys($this->dbSQL) as $type) {
            $sql .= $this->createSQLDataTable($type);
         }  
      }
      return $sql;
   }
}

?>
