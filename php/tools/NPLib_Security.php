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
require_once($NPLIB_PATH."extlib/security/AES.php");

function NP_encryption_algorithms() {
   //$algorithms = mcrypt_list_algorithms();
   $algorithms[] = "aes";
   return $algorithms;
}

function NP_hash_algorithms() {
   $algorithms = hash_algos();
   /*foreach (array(0,1,2,3,4,5,6,21,22) as $idx) {
      $algorithms[$idx] = strtoupper($algorithms[$idx]);
   }
   return $algorithms;*/
   return $algorithms;
}

function NP_encrypt($algorithm, $plain_text, $key) {
   $ciphered_text = null;
   if (strtoupper($algorithm) === "AES") {
      $ciphered_text = AESEncryptCtr($plain_text, $key, 256);
   }
     
   return $ciphered_text;
}

function NP_decrypt($algorithm, $ciphered_text, $key) {
   $plain_text = null;
   if (strtoupper($algorithm) === "AES") {
      $plain_text = AESDecryptCtr($ciphered_text, $key, 256);
   }
     
   return $plain_text;
}

function NP_hash($algorithm, $text) {
   $hash = null;
   /*switch ($algorithm) {
      case "MD5": $hash = md5($text); break;
      case "SHA1": $hash = sha1($text); break;
      case "CRC32": $hash = crc32($text); break;
   }*/
   $hash = hash($algorithm, $text);
   
   return $hash;
}

?>