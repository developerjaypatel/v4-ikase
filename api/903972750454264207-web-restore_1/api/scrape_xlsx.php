<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$targetFile = 'c:\\inetpub\\wwwroot\\ikase.org\\xlsx2csv\\csv\\courtcalendar.csv';

if (($handle = fopen($targetFile, "r")) !== FALSE) {
	$arrFields = array();
	$row_count = 0;
	while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
		if ($row_count > 3) {
		die(print_r($data));
		}
		$row_count++;
	}
}
?>