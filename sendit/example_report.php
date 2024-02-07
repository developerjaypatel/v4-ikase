<?php
require 'vendor/autoload.php';
require_once('vendor/sendgrid/sendreports/src/Report.php');
require_once('vendor/sendgrid/sendreports/src/SendGrid.php');

// Define credentials file
$params_file = 'sendreports/doc/example_params.json';

// Validate credentials exist;
if (!file_exists($params_file)) {
    echo "Oops! The file $params_file does not exist. This script requires a `example_params.json` file that contains your SendGrid credentials. The json file is in `.gitignore`, so no worries about accidental commits there. For your convenience, an example of this json file is included in `doc/example_params_placeholder.json` so that you can simply copy/paste that file and rename it to `example_params.json`.";
    die();
}

// Fetch vars from example_params.json file (you must create this file as descriped in the README)
$params = json_decode(file_get_contents($params_file),true);

//die(print_r($params));
// Initialize the SendGridReport object
$sendgrid = new Fcosrno\SendGridReport\SendGrid($params['api_user'],$params['api_key']);
$sendgrid->api_user = $params['api_user'];
$sendgrid->api_key = $params['api_key'];

// Initialize the Report object
$report = new Fcosrno\SendGridReport\Report();

// Spam Reports
$report->spamreports();
$spamresult = $sendgrid->report($report);

echo "Spam<br />";
//echo "<pre>";
//echo print_r($spamresult);
//echo "</pre>";
$arrWhere = array();
$arrBlockEmails = array();
$where = "";
foreach($spamresult as $block) {
	$block_email = $block->email;
	if (!in_array($block_email, $arrBlockEmails) && $block_email !="") {
		$arrBlockEmails[] = $block_email;
		$arrWhere[] = " `email` = '" . $block_email . "'";
	}
}
if (count($arrWhere) > 0) {
	$where = " WHERE 1 AND ("  . implode(" OR ", $arrWhere) . ")";
	$sql = "SELECT * FROM tbl_debtor spams" . $where;
	echo $sql . "<br />";
}

// Blocks
$report->blocks();
$blockresult = $sendgrid->report($report);
echo "Blocks<br />";
//echo "<pre>";
//echo print_r($blockresult);
//echo "</pre>";
$arrWhere = array();
$arrBlockEmails = array();
$where = "";
foreach($blockresult as $block) {
	$block_email = $block->email;
	if (!in_array($block_email, $arrBlockEmails) && $block_email !="") {
		$arrBlockEmails[] = $block_email;
		$arrWhere[] = " `email` = '" . $block_email . "'";
	}
}
if (count($arrWhere) > 0) {
	$where = " WHERE 1 AND ("  . implode(" OR ", $arrWhere) . ")";
	$sql = "SELECT * FROM tbl_debtor blocks" . $where;
	echo $sql . "<br />";
}
// Bounces
$report->bounces();
$result = $sendgrid->report($report);
echo "Bounces<br />";
//echo "<pre>";
//echo print_r($result);
//echo "</pre>";

// Bounces
$report->invalidemails();
$invalidresult = $sendgrid->report($report);
echo "Invalids<br />";
//echo "<pre>";
//echo print_r($invalidresult);
//echo "</pre>";
$arrWhere = array();
$arrBlockEmails = array();
$where = "";
foreach($invalidresult as $block) {
	$block_email = $block->email;
	if (!in_array($block_email, $arrBlockEmails) && $block_email !="") {
		$arrBlockEmails[] = $block_email;
		$arrWhere[] = " `email` = '" . $block_email . "'";
	}
}
if (count($arrWhere) > 0) {
	$where = " WHERE 1 AND ("  . implode(" OR ", $arrWhere) . ")";
	$sql = "SELECT * FROM tbl_debtor invalids" . $where;
	echo $sql . "<br />";
}

// Unsubscribes
$report->unsubscribes();
$unsubresult = $sendgrid->report($report);
echo "Unsubs<br />";
//echo "<pre>";
//echo print_r($unsubresult);
//echo "</pre>";

$arrWhere = array();
$arrBlockEmails = array();
$where = "";
foreach($unsubresult as $block) {
	$block_email = $block->email;
	if (!in_array($block_email, $arrBlockEmails) && $block_email !="") {
		$arrBlockEmails[] = $block_email;
		$arrWhere[] = " `email` = '" . $block_email . "'";
	}
}
if (count($arrWhere) > 0) {
	$where = " WHERE 1 AND ("  . implode(" OR ", $arrWhere) . ")";
	$sql = "SELECT * FROM tbl_debtor unsubs" . $where;
	echo $sql . "<br />";
}
?>
