<?php

$targetFile = ROOT_PATH.'xlsx2csv'.DC.'csv'.DC.'courtcalendar.csv';

if (($handle = fopen($targetFile, "r")) !== false) {
	$arrFields = [];
	$row_count = 0;
	while (($data = fgetcsv($handle, 5000, ",")) !== false) {
		if ($row_count > 3) {
			die(print_r($data));
		}
		$row_count++;
	}
}
