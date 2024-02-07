<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/manage_session.php");
session_write_close();


include("../../api/connection.php");

$lastmonth = date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d") + 2,   date("Y")));

try {
	$sql = "SELECT (total - IFNULL(payments.paids, 0)) due, inv.*, IFNULL(payments.paids, 0) paids, cus.customer_id, cus.cus_name, cus.cus_email  
	FROM ikase.cse_invoice inv
	INNER JOIN ikase.cse_customer cus
	ON inv.customer_id = cus.customer_id
	LEFT OUTER JOIN (
		SELECT invoice_uuid, SUM(payment) paids
		FROM ikase.cse_check chk
		INNER JOIN ikase.cse_invoice_check ich
		ON chk.check_uuid = ich.check_uuid
		GROUP BY invoice_uuid
	) payments
	ON inv.invoice_uuid = payments.invoice_uuid
	WHERE inv.deleted = 'N'
	AND id_collection = 'invoice'
	AND (total - IFNULL(payments.paids, 0)) > 0
	AND inv.notification_date != '0000-00-00 00:00:00'
	AND CAST(inv.notification_date AS DATE) < '" . $lastmonth . "'";
	$sql .= "
	ORDER BY cus.cus_name, invoice_date ASC";
	//die($sql);
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$invoices = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	//die(print_r($invoices));
	//echo $sql . "<br />";
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
if (count($invoices)==0) {
	die("There are no oustanding invoices at this time");
} else {
	$arrRows = array();
	$totals = 0;
	$paids = 0;
	$balances = 0;
	$arrInvoices = array();
	$arrCustomers = array();
	foreach($invoices as $invoice) {
		if (!in_array($invoice->cus_name, $arrCustomers)) {
			$arrCustomers[$invoice->customer_id] = $invoice->cus_name;
		}
		$arrInvoices[$invoice->customer_id][] = $invoice->invoice_id;
		$balance = $invoice->total - $invoice->paids;
		
		$bold = " style='font-weight:bold;color:black'";
		
		//send email
		$notification_date = $invoice->notification_date;
		$send_mail_link = "Sent on " . date("m/d/y", strtotime($notification_date));
		
		
		$row = "<tr><td align='left' valign='top'>" . $invoice->cus_name . "</td><td align='left' valign='top'><a href='https://www.ikase.org/manage/customers/invoice.php?cus_id=" . $invoice->customer_id . "&invoice_id=" . $invoice->invoice_id . "'>" . $invoice->invoice_number . "</a></td><td align='left' valign='top'>" . date("m/d/y", strtotime($invoice->invoice_date)) . "</td><td align='left' valign='top' id='feedback_" . $invoice->invoice_id . "'>" . $send_mail_link . "</td><td align='right' valign='top'>$" . number_format($invoice->total, 2) . "</td><td align='right' valign='top'>$" . number_format($invoice->paids, 2) . "</td><td align='right' valign='top' " . $bold . ">$" . number_format(($balance), 2) . "</td></tr>";
		
		$row = $invoice->cus_name . "<br><a href='https://www.ikase.org/manage/customers/invoice.php?cus_id=" . $invoice->customer_id . "&invoice_id=" . $invoice->invoice_id . "'>" . $invoice->invoice_number . "</a><br>
		$" . number_format(($balance), 2);
		
		$totals += $invoice->total;
		$paids += $invoice->paids;
		$balances += $balance;
		
		$arrRows[] = $row;
	}
	//totals
	//$row = "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td align='right' valign='top' style='font-weight:bold; background:#EDEDED; border:1px solid black'>Totals:</td><td align='right' valign='top' style='font-weight:bold; background:#EDEDED; border:1px solid black'>$" . number_format($totals, 2) . "</td><td align='right' valign='top' style='font-weight:bold; background:#EDEDED; border:1px solid black'>$" . number_format($paids, 2) . "</td><td align='right' valign='top' style='font-weight:bold; background:#EDEDED; border:1px solid black'>$" . number_format(($balances), 2) . "</td><td align='left' valign='top'>&nbsp;</td></tr>";
	
	$row = "Total Owed: $" . number_format($balances, 2);
	$arrRows[] = $row;
}

$content = "<table cellpadding='2' cellspacing='0' border='1'><tr><th>Customer</th><th>Invoice</th><th>Invoice Date</th><th>&nbsp;</th><th>Invoiced</th><th>Paid</th><th>Due</th></tr>" . implode("<br><br>", $arrRows) . "</table>";
$content = implode("<br><br>", $arrRows);

//die($content);
//die(print_r($arrInvoices));
$html_message = "List of outstanding invoices per customer as of " . date("m/d/Y");
$html_message .= "<br><br>";
$html_message .= $content;

$html_message = urlencode($html_message);

//die($html_message);
$url = "https://www.matrixdocuments.com/dis/sendit.php";

//die($html_values);

$email = "nick@kustomweb.com";
$ccs = "latommy1@gmail.com"; $bccs = "";
$ccs = "";	//for now
$fields = array("from_name"=>"iKase Billing", "from_address"=>"donotreply@ikase.org", "to_name"=>$email, "cc_name"=>$ccs, "bcc_name"=>$bccs, "html_message"=>$html_message, "text_message"=>"", "subject"=>urlencode("iKase Outstanding Invoices - " . date("F Y")), "attachments"=>"");

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

die($result);
$blnSendEmail = ($result=="sent");

if (!$blnSendEmail) {
	die("not sent");
}
try {
	foreach($arrInvoices as $cus_id=>$invoice) {
		$cus_name = $arrCustomers[$cus_id];
		foreach($invoice as $invoice_id) {
			//insert a note, attach to customer
			$notes_uuid = uniqid("NT");
			$sql = "INSERT INTO ikase.`cse_notes` (`notes_uuid`, `type`, `subject`, `title`, `note`, `entered_by`, `customer_id`)
			VALUES ('" . $notes_uuid . "', 'invoice_reminder', 'Invoice Reminder :: " . $cus_name . "', '', 'Invoice Reminder " . $invoice_id . " sent for " . addslashes($cus_name) . " to iKase Admin', 'SYSTEM', '" . $cus_id . "');";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$stmt = null; $db = null;
			
			$sql = "INSERT INTO `ikase`.cse_customer_notes (`customer_notes_uuid`, `customer_uuid`, `notes_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $notes_uuid  ."', '" . $cus_id . "', '" . $notes_uuid . "', 'invoice_reminder', '', '" . date("Y-m-d H:i:s") . "', 'SYSTEM', '" . $cus_id . "')";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$stmt = null; $db = null;
			
			//update invoice
			$sql = "UPDATE `ikase`.`cse_invoice`
			SET reminder_date = '" . date("Y-m-d H:i:s") . "'
			WHERE invoice_id = '" . $invoice_id . "'
			AND customer_id = '" . $cus_id . "'";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$stmt = null; $db = null;
		}
	}
	echo "sent";
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( print_r($error));
}
?>