<?php 
$filename = "C:\\inetpub\\wwwroot\\iKase.org\\api\\chain.config";

$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

$key = base64_decode($contents);
$key = base64_decode($key);
$key = base64_decode($key);

define("CRYPT_KEY", $key);
echo $key;
?>
