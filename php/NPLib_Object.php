<?php

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