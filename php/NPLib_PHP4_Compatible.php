<?php
/** 
 * NPLib - PHP
 * 
 * Backwards compatibility functions
 * 
 * @package np-lib
 * @subpackage 
 * @version 20090624
 * 
 * @author Daniel Pecos Martínez
 * @copyright Copyright (c) Daniel Pecos Martínez 
 * @license http://www.gnu.org/licenses/lgpl.html  LGPL License
 */
if (version_compare(phpversion(), '5.0') < 0) {

function array_combine($arr1, $arr2) {
    $out = array();
   
    $arr1 = array_values($arr1);
    $arr2 = array_values($arr2);
   
    foreach($arr1 as $key1 => $value1) {
        $out[(string)$value1] = $arr2[$key1];
    }
   
    return $out;
}


}
?>
