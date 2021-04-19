<?php
function encrypt($string) {

	$string_to_encrypt = $string;
	$password = "password";
	
	$encrypted_string = openssl_encrypt($string_to_encrypt,"AES-128-ECB",$password);
	
	return $encrypted_string;

}


function decrypt($string) {
	
	$encrypted_string = $string;
	$password = "password";

	$decrypted_string = openssl_decrypt($encrypted_string,"AES-128-ECB",$password);
	
	return $decrypted_string;
	
}	


/*
define('ENCRYPTION_KEY', '_91X:s+{a2Jwb6*J');
 
$txt = 'Тестируем обратимое шифрование на php';
$encrypted = encrypt($txt, ENCRYPTION_KEY);
echo $encrypted.'<br>';
$decrypted = decrypt($encrypted, ENCRYPTION_KEY);
echo $decrypted;
 
function encrypt($decrypted, $key) {
  $ekey = hash('SHA256', $key, true);
  srand(); $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
  if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
  $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $ekey, $decrypted . md5($decrypted), MCRYPT_MODE_CBC, $iv));
  return $iv_base64 . $encrypted;
}
 
function decrypt($encrypted, $key) {
  $ekey = hash('SHA256', $key, true);
  $iv = base64_decode(substr($encrypted, 0, 22) . '==');
  $encrypted = substr($encrypted, 22);
  $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $ekey, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv), "\0\4");
  $hash = substr($decrypted, -32);
  $decrypted = substr($decrypted, 0, -32);
  if (md5($decrypted) != $hash) return false;
  return $decrypted;
}
*/