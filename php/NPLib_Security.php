<?php
/** 
 * NPLib - PHP
 * 
 * Security related functions
 * 
 * @package np-lib
 * @subpackage 
 * @version 20090624
 * 
 * @author Daniel Pecos Martínez
 * @copyright Copyright (c) Daniel Pecos Martínez 
 * @license http://www.gnu.org/licenses/lgpl.html  LGPL License
 */
require_once("security/AES.php");

function NP_encrypt($algorithm, $plain_text, $key) {
   $ciphered_text = null;
   if ($algorithm === "AES") {
      $ciphered_text = AESEncryptCtr($plain_text, $key, 256);
   }
     
   return $ciphered_text;
}

function NP_decrypt($algorithm, $ciphered_text, $key) {
   $plain_text = null;
   if ($algorithm === "AES") {
      $plain_text = AESDecryptCtr($ciphered_text, $key, 256);
   }
     
   return $plain_text;
}

function NP_hash($algorithm, $text) {
   $hash = null;
   if ($algorithm == "SHA1") {
      $hash = sha1($text);
   }
   
   return $hash;
}
?>