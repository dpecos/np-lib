<?

class NP_DDBB {
   
   private $config;
   private $dbCon;
   
   private $dbMappings;
   private $dbTypes;
   private $dbTables;
   
   public function __construct ($dbconfig) {
	   $this->config = $dbconfig;
	   
	   $this->dbMappings = array();
      $this->dbTypes = array();
      $this->dbTables = array();
   }
   
   public function addConfig($objType, $sqlTable, $sqlMappings, $sqlTypes) {
      $this->dbMappings[$objType] = $sqlMappings;
      $this->dbTypes[$objType] = $sqlTypes;
      if (isset($this->config["PREFIX"]))
         $this->dbTables[$objType] = $this->config["PREFIX"].$sqlTable;
      else
         $this->dbTables[$objType] = $sqlTable;
   }
   
   public function getTable($objType) { 
      return $this->dbTables[$objType];
   }
    
   public function getMapping($objType, $fieldName) { 
      return $this->dbMappings[$objType][$fieldName];
   }
      
   public function getType($objType, $fieldName) { 
      return $this->dbTypes[$objType][$fieldName];
   }
     
   private function __createSELECT_Column($colName, $sqlType) {
      if ($sqlType == "DATE") {
        return "DATE_FORMAT(".$colName.", '%Y%m%d%H%i%s') AS ".$colName;
      } else
        return $colName;
   }    
   
   private function __createSELECT_AllColumns($obj, $field, $ddbb_mapping, $ddbb_types, &$first) {
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

   public function loadData(&$obj, &$data, $ddbbMapping = null, $ddbbTypes = null) {
   
      $object_name = get_class($obj);
      if ($ddbbMapping != null && $ddbbTypes != null) {
         $ddbb_mapping = $ddbbMapping;
         $ddbb_types = $ddbbTypes;
      } else {
         $ddbb_mapping = $this->dbMappings[$object_name];
         $ddbb_types = $this->dbTypes[$object_name];
      }
      
	   foreach (array_values($ddbb_mapping) as $dbFieldName) {
	
		   $objectFieldName = _obtainKeyForValue($ddbb_mapping, $dbFieldName);

		   if ($objectFieldName == null) {
		       return;
		   }
		
		   if (is_array($objectFieldName)) {
		       
		       if (is_object($obj)) {
		           $this->loadData($obj->$objectFieldName[0], $data, $ddbb_mapping[$objectFieldName[0]], $ddbb_types[$objectFieldName[0]]);
		       } else {
		           $this->loadData($obj[$objectFieldName[0]], $data, $ddbb_mapping[$objectFieldName[0]], $ddbb_types[$objectFieldName[0]]);
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
   
   public function insertObject($object) {    
   
      $object_name = get_class($object);
      
      $ddbb_mapping = $this->dbMappings;
      $ddbb_types = $this->dbTypes;
      $ddbb_table = $this->dbTables;
   	
	   $varNames = "";
	   $varValues = "";
	   $first = true;	
	
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
								   $varValues .= NP_DDBB::encodeSQLValue($subobjvalue, $ddbb_types[$object_name][$var][$objvar][$subobjvar]);
							   }
						   } else {
							   if (!$first) {
								   $varNames .= ", ";
								   $varValues .= ", ";
							   } else
								   $first = false;
							   $varNames .= $ddbb_mapping[$object_name][$var][$objvar];
							   $varValues .= NP_DDBB::encodeSQLValue($objvalue, $ddbb_types[$object_name][$var][$objvar]);
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
					   $varValues .= NP_DDBB::encodeSQLValue($value, $ddbb_types[$object_name][$var]);
				   }
			   }
		   } else {
			   //TODO: ERROR
		   }
	   }
	   $sql = "INSERT INTO ".$ddbb_table[$object_name]." ($varNames) VALUES ($varValues)";	
	
	   return $this->executeInsertUpdateQuery($sql);
   }

   public static function encodeSQLValue($strVal, $sqlType) {
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

   public static function decodeSQLValue($strVal, $sqlType) {
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

   private function connectServer () {

	   $this->dbCon = mysql_connect ($this->config ["HOST"], $this->config ["USER"], $this->config ["PASSWD"])
		   or die ("No se pudo conectar con la BBDD: ".mysql_error());
	   mysql_select_db ($this->config ["NAME"])
		   or die ("No se encontró la BBDD en el servidor.");

   }

   private function disconnectServer () {
	   mysql_close($this->dbCon);
	   $this->dbCon = null;
   }

   public function executePKSelectQuery($sql) {
	   $this->connectServer();

	   $resultado = mysql_query($sql);

	   if (!$resultado) {
		   echo "No pudo ejecutarse satisfactoriamente la consulta ($sql) en la BD: " . mysql_error();
		   exit;
	   }

	   $datos = mysql_fetch_assoc ($resultado);

	   mysql_free_result($resultado);
		
	   $this->disconnectServer();
	   return $datos;
   }

   public function executeSelectQuery($sql, $f, $params = null) {
	   if ($params == null)
		   $params = array();

	   $this->connectServer();

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
		
	   $this->disconnectServer();
   }

   public function executeInsertUpdateQuery($sql) {
	   $this->connectServer();
		
	   $resultado = mysql_query($sql);
	
	   if (!$resultado) {
		   echo "No pudo ejecutarse satisfactoriamente la consulta ($sql) en la BD: " . mysql_error();
		   exit;
	   }
	
	   $id = mysql_insert_id();
	
	   $this->disconnectServer();
	
	   return $id;
   }

   public function executeDeleteQuery($sql) {
	   $this->connectServer();
		
	   mysql_query($sql);
	
	   $resultado = mysql_affected_rows($this->dbCon); 
	
	   $this->disconnectServer();
	
	   return $resultado;
   }    
   
}

?>
