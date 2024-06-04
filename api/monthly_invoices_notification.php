<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");

$arrRows = array();
$arrPlainRows = array();

if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
  $application = "StarLinkCMS";
  $application_domain = "starlinkcms.com";
}
else
{
  $application = "iKase";
  $application_domain = "ikase.org";
}

try {
	$sql = "SELECT cus.cus_name, cus.cus_email, inv.* 
	FROM ikase.cse_invoice inv
	INNER JOIN ikase.cse_customer cus
	ON inv.customer_id = cus.customer_id
	WHERE inv.notification_date = '0000-00-00 00:00:00'
	AND inv.customer_id != 1033
	AND inv.invoice_date > '2018-12-01'
	ORDER BY inv.invoice_date ASC";
	
	$invoices = DB::select($sql);
	
	$cus_email = "";
	
	foreach($invoices as $invoice) {
		if ($cus_email=="") {
			$cus_email = $invoice->cus_email;
		}
		$row = "
		<tr>
			<td align='left' valign='top'>
				" . $invoice->invoice_number . "
			</td>
			<td align='left' valign='top'>" . $invoice->cus_name . "
			</td>
			<td align='left' valign='top'>" . date("m/d/Y", strtotime($invoice->invoice_date)) . "
			</td>
			<td align='left' valign='top'>$" . number_format($invoice->total, 2) . "
			</td>
		</tr>
		";
		$arrRows[] = $row;
		
		$row_plain = "Invoice " . $invoice->invoice_number . " (" . $invoice->cus_name . ")	...	$" . number_format($invoice->total, 2);
		$arrPlainRows[] = $row_plain;
	}
	
} catch(PDOException $e) {	
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}


$html_message = "<div>There are " . count($invoices) . " invoice(s) ready for you to send on ". $application ." Customer Management System</div>";
$html_message .= "<div>Please login ". $application ." Customer Management System as Matrix Admin, and then click the Ready to Send Invoices link</div>";
$html_message .= "
<div>
	<table width='100%' cellspacing='0' cellpadding='0'>
		<tr>
			<th align='left'>Invoice</th>
			<th align='left'>Customer</th>
			<th align='left'>Date</th>
			<th align='left'>Invoiced</th>
		</tr>
		" . implode("", $arrRows) . "
	</table>
</div>";

//
$text_message = "There are " . count($invoices) . " invoice(s) ready for you to send on ". $application ." Customer Management System";
$text_message .= "\r\n";
$text_message .= "Please login ". $application ." Customer Management System as Matrix Admin, and then click the Ready to Send Invoices link";
$text_message .= "\r\n";
$text_message .= implode("", $arrPlainRows);
//die($text_message);

$subject = "Invoices Ready to Send";
$error_delivery_address = "nick@kustomweb.com";
$attachments = "";


$from_name = $application . " System";
$from_address = "donotreply@" . $application_domain;
$tos = "latommy1@gmail.com";
$ccs = "";
$bccs = "nick@kustomweb.com";

$blnSendEmail = false;
try {
	//"from_name"=>$from_name, "from_address"=>$from_address, "to_name"=>$tos, "cc_name"=>$ccs, "bcc_name"=>$bccs, "html_message"=>urlencode($html_message), "text_message"=>urlencode($text_message), "subject"=>urlencode($subject), "attachments"=>urlencode($attachments)
	$to_name = $tos;
	$cc_name = $ccs;
	$bcc_name = $bccs;
	
	//die($from_name . "//" . $from_address);
	$blnMonthlyNotif = true;
	
	$url = "https://www.matrixdocuments.com/dis/sendit.php";
	$ccs = ""; $bccs = "nick@kustomweb.com";
	//die($html_values);
	$email = $tos;
	$fields = array("from_name"=> $application . " Billing Reminder", "from_address"=>"donotreply@". $application_domain, "to_name"=>$email, "cc_name"=>$ccs, "bcc_name"=>$bccs, "html_message"=>"", "text_message"=>$text_message, "subject"=>urlencode($application . " Invoice Reminder - " . date("m/d/Y")), "attachments"=>"");
	
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
	
	if ($blnSendEmail) {
		echo "mail sent to " . $tos . " at " . date("m/d/Y H:i:s");
	} else {
		echo "mail NOT sent to " . $tos . " at " . date("m/d/Y H:i:s");
	}
} catch ( Exception $e ) {
	die(print_r($e));
	//not sent
}
