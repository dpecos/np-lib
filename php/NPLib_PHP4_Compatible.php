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

if ( !function_exists('array_combine')) {
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

if ( !function_exists('sys_get_temp_dir')) {
  function sys_get_temp_dir() {
    if (!empty($_ENV['TMP'])) { return realpath($_ENV['TMP']); }
    if (!empty($_ENV['TMPDIR'])) { return realpath( $_ENV['TMPDIR']); }
    if (!empty($_ENV['TEMP'])) { return realpath( $_ENV['TEMP']); }
    $tempfile=tempnam(__FILE__,'');
    if (file_exists($tempfile)) {
      unlink($tempfile);
      return realpath(dirname($tempfile));
    }
    return null;
  }
}

?>
