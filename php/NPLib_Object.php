<?php
require_once("NPLib_Common.php");

/*function _obtainKeyForValue($hash, $value) {
   if ($hash != null) {
	   foreach ($hash as $key1 => $val1) {
		   if (is_array ($val1)) {
			   foreach ($val1 as $key2 => $val2) {
			       if (is_array($val2)) {
			           foreach ($val2 as $key3 => $val3) {
			               if ($val3 == $value)
					           return array($key1, $key2, $key3);
					   }
				   } else if ($val2 == $value)
					   return array($key1, $key2);
			   }
		   } else if ($val1 == $value) {
			   return $key1;
		   }
	   }
	}
	return null;
}
*/

function NP_loadDataInto(&$obj, &$data, $prefix="", $ddbb_mapping = null) {
	$objectVars = array_keys(get_object_vars($obj));
  
    foreach ($data as $key => $value) {
		if (NP_startsWith($prefix, $key)) {
			$path = split("_", $key);
			$property = $path[1];
			
			if (in_array($property, $objectVars)) {
				if (is_array($obj->$property)) {
				    if (is_string($value))
				        $obj->$property = NP_set_i18n($obj->$property, $value);
				    else
			            $obj->$property = array_merge($obj->$property, array($path[2] => $value));
				} else {
					if ($ddbb_mapping != null && $ddbb_mapping[$property] == "STRING_I18N" || $ddbb_mapping[$property] == "TEXT_I18N") {
				            $value = NP_set_i18n(array(), $value);    
				    }
					$obj->$property = $value;
				}
			}
		}
	}
}

function NP_clone($object) {
    if (version_compare(phpversion(), '5.0') < 0) {
        return $object;
    } else {
        return @clone($object);
    }
}

?>
