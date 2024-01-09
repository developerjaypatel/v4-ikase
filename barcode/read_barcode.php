<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	
	
/* Create new image object */
$image = new ZBarCodeImage("separator_barcode.jpg");

/* Create a barcode scanner */
$scanner = new ZBarCodeScanner();

/* Scan the image */
$barcode = $scanner->scan($image);

/* Loop through possible barcodes */
if (!empty($barcode)) {
    foreach ($barcode as $code) {
        printf("Found type %s barcode with data %s\n", $code['type'], $code['data']);
    }
}
