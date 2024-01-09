<?php
//$app->group('', function (\Slim\Routing\RouteCollectorProxy $app) {
	//$app->post('/modify', 'encryptAES');
	//$app->post('/unmodify', 'decryptAES');
//})->add(\ApiLib\Middleware\Authorize::class);

function encryptAES($string) {
	//$string = passed_var("string", "post");
	$key = md5(CRYPT_KEY);
    return base64_encode(@openssl_encrypt($string, OPENSSL_CIPHER_AES_256_CBC, $key, OPENSSL_RAW_DATA));
}

function decryptAES($string) {
    $key = md5(CRYPT_KEY);
    return @openssl_decrypt(base64_decode($string), OPENSSL_CIPHER_AES_256_CBC, $key, OPENSSL_RAW_DATA);
}

