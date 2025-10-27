<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("connection.php");
include("manage_session.php");

$file = passed_var("file", "get");
$file = str_replace("../", "", $file);

$destination = "kase_bill__" . $file;
if (strpos($file, "kase_invoice")!==false) {
	$destination = $file;
}
$iframe = '<iframe id="invoice_frame" src="https://v2.ikase.org/uploads/' . $_SESSION["user_customer_id"] . "/invoices/" . $destination . '.pdf" width="100%" height="800px"></iframe>';
echo $iframe;

die();
?>