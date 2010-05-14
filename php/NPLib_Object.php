<?php
/** 
 * NPLib - PHP
 * 
 * General object manipulating functions
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

function NP_loadDataInto(&$obj, &$data, $prefix="", $ddbb_mapping = null) {
    $objectVars = array_keys(get_object_vars($obj));

    foreach ($data as $key => $value) {
        if (NP_startsWith($prefix, $key)) {
            $path = split("_", $key);
            $property = $path[1];

            if (in_array($property, $objectVars)) {
                if (is_array($obj->$property)) {
                    if (is_string($value) && ($ddbb_mapping[$property] == "STRING_I18N" || $ddbb_mapping[$property] == "TEXT_I18N")) {
                        $obj->$property = NP_set_i18n($obj->$property, $value);
                    } else {
                        $obj->$property = array_merge($obj->$property, array($path[2] => $value));
                    }
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
