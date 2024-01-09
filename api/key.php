<?php
$filename = __DIR__.DIRECTORY_SEPARATOR.'chain.config';

$handle   = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

$key = base64_decode($contents);
$key = base64_decode($key);
$key = base64_decode($key);

die($key);
