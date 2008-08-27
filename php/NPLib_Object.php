<?php
function _obtainKeyForValue($hash, $value) {
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


function NP_loadDataInto(&$obj, &$data, $prefix="") {
	$objectVars = array_keys(get_object_vars($obj));
  
  foreach ($data as $key => $value) {
		if (beginsWith($key, $prefix)) {
			$path = split("_", $key);
			$property = $path[1];
			if (in_array($property, $objectVars)) {
				if (is_array($obj->$property)) {
			    $obj->$property = array_merge($obj->$property, array($path[2] => $value));
				} else {
					$obj->$property = $value;
				}
			}
		}
	}
}

?>
