<?php

$dir = UPLOADS_PATH.$customer_id.DC.'announce'.DC;

$files = scandir($dir);
$prefix = date('Ymd');

foreach ($files as $file) {
	if (strpos($file, '.docx') !== false) {
		if (strpos($file, $prefix) === false) {
			unlink($dir.$file);
		}
	}
}

echo 'done';
