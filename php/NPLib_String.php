<?php
require_once("NPLib_Common.php");

function NP_startsWith($sub, $str) {
   return (substr($str, 0, strlen($sub)) === $sub);
}
function NP_endsWith($sub, $str) {
   return (substr($str, strlen($str) - strlen($sub)) === $sub);
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

function NP_random_string($length)
{
	$random= "";
	srand((double)microtime()*1000000);
	$char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$char_list .= "abcdefghijklmnopqrstuvwxyz";
	$char_list .= "1234567890";
	// Add the special characters to $char_list if needed

	for($i = 0; $i < $length; $i++)
	{
		$random .= substr($char_list,(rand()%(strlen($char_list))), 1);
	}
	return $random;
}

function NP_get_i18n($strings) {
    if (is_array($strings)) {
        if (array_key_exists(NP_LANG, $strings))
            return $strings[NP_LANG];
        else if (array_key_exists(NP_DEFAULT_LANG, $strings))
            return "** Untranslated ** (".NP_DEFAULT_LANG.") ".$strings[NP_DEFAULT_LANG];
        else {
            $lang = array_keys($strings);
            $lang = $lang[0];
            return "** Untranslated ** (".$lang.") ".$strings[$lang];
        }
    } else if ($strings != null)
        return "** Incorrect i18N format ** ".$strings;
    else
        return null;
}

function NP_set_i18n($strings, $val = null, $lang = null) {
    if (is_string($strings)) {
        $tmp = NP_DDBB::decodeI18NSqlValue($strings);
        if (is_array($tmp))
            $strings = $tmp;
    } else if (is_null($strings)) {
        $strings = array();
    }
   
    if (is_array($strings)) {
        if ($val === null && $lang !== null)
            unset($strings[$lang]);
        else {
            if ($lang === null) 
                $strings[NP_LANG] = $val;
            else
                $strings[$lang] = $val;
        }
        return $strings;   
    } else {
        if (!is_null($strings))
            return $strings;
        else
            return $val;
    }
}
?>
