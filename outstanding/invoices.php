<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../api/connection.php");

$seq = passed_var("seq");
$data = base64_decode($seq);
$json_data = json_decode($data);

$dms_auth = $json_data->dms_auth;
$cus_id = $json_data->cus_id;
$invoice_id = $json_data->invoice_id;
if (!is_numeric($invoice_id)) {
	die();	
}
$invoice_id = $invoice_id - 7;

$reroute = ($cus_id * 7) . "/invoice_" . $invoice_id . ".php";
header("location:" . $reroute);
