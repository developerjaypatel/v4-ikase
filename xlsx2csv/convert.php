<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

function dateSerialToDateTime($dateserial) {
	$arrDate = explode(".", $dateserial);
	$n = $arrDate[0];
	$decimal = "." . $arrDate[1];
	$duration = 86400 * $decimal;
	
	$dateTime = new DateTime("1899-12-30 + $n days");
	$converted = $dateTime->format("Y-m-d") . " " . gmdate("H:i:s", $duration);
	
	return $converted;
}

$result=0;
$throttle=0;

$file = 'court.xlsx';
require_once 'xlsx2csv.php';

$newcsvfile  = str_replace(".xlsx",".csv",$file);
$newcsvfile ="csv/$newcsvfile";

die($newcsvfile);
?>