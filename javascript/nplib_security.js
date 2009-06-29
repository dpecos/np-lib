/*function np_init_security() {
   np_include("static/np-lib/security/AES.js");
   np_include("static/np-lib/security/Hash.js");   
}*/

function np_hash(algorithm, text) {
   var hash = null;
   
   if (algorithm == "MD5") {
      hash = hash_md5(text);
   } else if (algorithm == "SHA1") {
      hash = hash_sha1(text);
   } else if (algorithm == "SHA256") {
      hash = hash_sha256(text);
   } else if (algorithm == "CRC32") {
      hash = hash_crc32(text);
   } 
   
   return hash;        
}

function np_encrypt(algorithm, plain_text, key) {
   var ciphered_text = null;
   
   if (algorithm == "AES") {
      ciphered_text = AESEncryptCtr(plain_text, key, 256);
   }
   
   return ciphered_text;
}

function np_decrypt(algorithm, ciphered_text, key) {
   var plain_text = null;
   
   if (algorithm == "AES") {
      plain_text = AESEncryptCtr(ciphered_text, key, 256);
   }
   
   return plain_text;
}