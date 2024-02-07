<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	

require_once('../../../shared/legacy_session.php');
session_write_close();

include("sec.php");
include("../../api/connection.php");

$invoice_id = passed_var("invoice_id", "post");
$cus_id = passed_var("cus_id", "post");

$sql = "SELECT cus_name, cus_email 
FROM ikase.cse_customer
WHERE customer_id = :customer_id";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( print_r($error));
}
$php_content = '<?php
if (!isset($_GET["dmsauth"])) {
	die("not allowed");
} 
$dmsauth = $_GET["dmsauth"];
$dmsauth = strrev($dmsauth);
$dmsauth = $dmsauth / 3;
$now = mktime(0, 0, 0, date("m"), date("d"),   date("Y"));
if ($now > $dmsauth) {
	die("not allowed");
}
?>';
//the invoice is valid for 1 month online
$dmsauth = mktime(0, 0, 0, date("m")+1, date("d"),   date("Y"));
$dmsauth = $dmsauth * 3;
$dmsauth = strrev($dmsauth);

//let's get the invoice, and then mail it
$filename = "https://v2.ikase.org/manage/customers/invoice.php?invoice_id=" . $invoice_id . "&cus_id=" . $cus_id . "&suid=outstanding";

$somecontent = file_get_contents($filename);

$somecontent = $php_content . $somecontent;

$customer_dir = $_SERVER['DOCUMENT_ROOT'] . '\\outstanding\\' . ($cus_id * 7) . '\\';

if (!is_dir($customer_dir)) {
	mkdir($customer_dir, 0755, true);
}
$output_filename = "outstanding/" . ($cus_id * 7) . "/invoices_" . $invoice_id . ".php";

$seq = "";
$arrFiles[] = "https://v2.ikase.org/" . $output_filename . "?dmsauth=" . urlencode($dmsauth);
/*
$seq = json_encode(array("cus_id"=>$cus_id, "invoice_id"=>$invoice_id, "dmsauth"=>urlencode($dmsauth)));
$arrFiles[] = "https://v2.ikase.org/invoice.php?seq=" . base64_encode($seq);
*/
$arrFilesHTML[] = "<a href='https://v2.ikase.org/outstanding/invoices.php?seq=" . base64_encode($seq) . "'>Click here for Print-Ready Invoice</a><br />
https://v2.ikase.org/invoice.php?seq=" . base64_encode($seq);

$output_filename = "../../" . $output_filename;

if (!$handle = fopen($output_filename, 'w')) {
	 echo "Cannot open file ($output_filename)";
	 exit;
}
// Write $somecontent to our opened file.
if (fwrite($handle, $somecontent) === FALSE) {
   echo "Cannot write to file ($output_filename)";
   exit;
}

$email_subject = "iKase :: " . date("F Y") . " Invoice";

$html_letter = "Please find a link below for the " . date("F Y") . " invoice for your iKase account.  (This link will expire in a month)";
$html_letter .= "<br />";
$html_letter .= "<br />";
$html_letter .= implode("<br /><br />", $arrFilesHTML);
$html_letter .= "<br />";
$html_letter .= "<br />";
$html_letter .= "Thank you for your prompt attention to this email, we appreciate it.";
$html_letter .= "<br />";
$html_letter .= "<br />";
$html_letter .= "iKase Support";

$letter = "Please find a link below for the " . date("F Y") . " invoice for your iKase account.  This link will expire in a month.";
$letter .= "\r\n";
$letter .= "\r\n";
$letter .= implode("\r\n", $arrFiles);
$letter .= "\r\n";
$letter .= "\r\n";
$letter .= "Thank you for your prompt attention to this email, we appreciate it.";
$letter .= "\r\n";
$letter .= "\r\n";
$letter .= "iKase Support";


$url = "https://www.matrixdocuments.com/dis/sendit.php";
$ccs = "latommy1@gmail.com"; $bccs = "";
//die($html_values);
$email = $customer->cus_email;
$email = "nick@kustomweb.com";
$fields = array("from_name"=>"iKase Billing", "from_address"=>"donotreply@ikase.org", "to_name"=>$email, "cc_name"=>$ccs, "bcc_name"=>$bccs, "html_message"=>"", "text_message"=>$letter, "subject"=>urlencode("iKase Invoice - " . date("F Y")), "attachments"=>"");

$fields_string = "";
foreach($fields as $key=>$value) { 
	$fields_string .= $key.'='.$value.'&'; 
}
rtrim($fields_string, '&');
$timeout = 5;
//open connection
$ch = curl_init();
		
//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_HEADER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_POST, count($fields_string));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);

//execute post
$result = curl_exec($ch);

$blnSendEmail = ($result=="sent");

if (!$blnSendEmail) {
	die("not sent");
}
try {
	//insert a note, attach to customer
	$notes_uuid = uniqid("NT");
	$sql = "INSERT INTO ikase.`cse_notes` (`notes_uuid`, `type`, `subject`, `title`, `note`, `entered_by`, `customer_id`)
	VALUES ('" . $notes_uuid . "', 'invoice_notification', 'Invoice Notification :: " . $customer->cus_name . "', '', 'Invoice " . $invoice_id . " sent to " . addslashes($customer->cus_name) . " at " . $email . "', 'SYSTEM', '" . $cus_id . "');";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase`.cse_customer_notes (`customer_notes_uuid`, `customer_uuid`, `notes_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $notes_uuid  ."', '" . $cus_id . "', '" . $notes_uuid . "', 'invoice_notification', '', '" . date("Y-m-d H:i:s") . "', 'SYSTEM', '" . $cus_id . "')";
	$stmt = DB::run($sql);
	
	//update invoice
	$sql = "UPDATE `ikase`.`cse_invoice`
	SET notification_date = '" . date("Y-m-d H:i:s") . "'
	WHERE invoice_id = '" . $invoice_id . "'
	AND customer_id = '" . $cus_id . "'";
	$stmt = DB::run($sql);
	
	echo "sent";
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( print_r($error));
}
?>
