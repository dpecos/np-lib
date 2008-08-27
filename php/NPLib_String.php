<?php
function beginsWith( $str, $sub ) {
   return ( substr( $str, 0, strlen( $sub ) ) === $sub );
}
function endsWith( $str, $sub ) {
   return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
}

function NP_UTF8_encode($obj) {
	if (gettype($obj) == "array") {
		foreach ($obj as $k => $v) {
			$obj[$k] = NP_UTF8_encode($v);
		}
	} else if (gettype($obj) == "object") {
		foreach (get_object_vars($obj) as $k => $v) {
			$obj->$k = NP_UTF8_encode($v);
		}
	}
	if (gettype($obj) == "string") {
		return utf8_encode($obj);
	} else {
		return $obj;
	}
}

function NP_UTF8_decode($obj) {
	if (gettype($obj) == "array") {
		foreach ($obj as $k => $v) {
			$obj[$k] = NP_UTF8_decode($v);
		}
	} else if (gettype($obj) == "object") {
		foreach (get_object_vars($obj) as $k => $v) {
			$obj->$k = NP_UTF8_decode($v);
		}
	}
	if (gettype($obj) == "string") {
		return utf8_decode($obj);
	} else {
		return $obj;
	}
}

?>
