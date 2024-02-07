<?php
if (!isset($bottom_list)) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	require_once('../../shared/legacy_session.php');
	session_write_close();
	
	if (!isset($_SESSION["user_plain_id"])) {
		die("no id");
	}
	if ($_SESSION["user_role"]!="owner") {
		die("no go");
	}
	include("../../api/connection.php");
	
	$cus_id = passed_var("cus_id");
	$invoice_id = -1;
	if (isset($_GET["invoice_id"])) {
		$invoice_id = passed_var("invoice_id", "get");
	}
	
	if (!is_numeric($invoice_id)) {
		die("no invoice");
	}
	if ($invoice_id < 1) {
		die("none invoice");
	}
}
try {
	$sql = "SELECT chk.*, inv.*, IFNULL(payments.paids, 0) paids 
	FROM ikase.cse_check chk
	INNER JOIN ikase.cse_invoice_check ich
	ON chk.check_uuid = ich.check_uuid
	INNER JOIN `ikase`.`cse_invoice` inv
	ON ich.invoice_uuid = inv.invoice_uuid
	LEFT OUTER JOIN (
		SELECT invoice_uuid, SUM(payment) paids
		FROM ikase.cse_check chk
		INNER JOIN ikase.cse_invoice_check ich
		ON chk.check_uuid = ich.check_uuid
		GROUP BY invoice_uuid
	) payments
	ON inv.invoice_uuid = payments.invoice_uuid
	WHERE id_collection = 'invoice'
	AND inv.customer_id = " . $cus_id . "
	AND inv.invoice_id = " . $invoice_id . "
	AND chk.deleted = 'N'
	ORDER BY check_date ASC";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	//$stmt->bindParam("customer_id", $cus_id);
	//$stmt->bindParam("invoice_id", $invoice_id);
	$stmt->execute();
	$checks = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//echo $sql . "<br />";
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
$arrRows = array();
if (count($checks)==0) {
	//echo "No payments have been made.";
	if (!isset($bottom_list)) {
		die("location:invoice_payment.php?cus_id=" . $cus_id . "&invoice_id=" . $invoice_id);
	} else {
		echo "";
	}
} else {
	
	foreach($checks as $check) {
		$bold = "";
		if ($check->balance > 0) {
			$bold = " style='font-weight:bold;color:red'";
		}
		$row = "
		<tr>
			<td align='left' valign='top'>" . $check->check_number . "</td>
			<td align='left' valign='top'>" . date("m/d/y", strtotime($check->check_date)) . "</td>
			<td align='left' valign='top'>$" . number_format($check->payment, 2) . "</td>
			<td align='left' valign='top' width='350px'>" . $check->memo . "</td>
			<td align='left' valign='top'>" . date("m/d/y", strtotime($check->transaction_date)) . "</td>
		</tr>";
		$arrRows[] = $row;
	}

	$paid = "";
	$payment_link = '<a href="invoice_payment.php?cus_id=' . $cus_id . '&invoice_id=' . $invoice_id . '">Make Another Payment</a>';
	if ( $checks[0]->balance == 0) {
		$paid = " - Paid";
		$payment_link = '';
	}
}
if (!isset($bottom_list)) {
?>
<h1>List of Checks for Invoice <a href="invoice.php?cus_id=<?php echo $cus_id; ?>&invoice_id=<?php echo $invoice_id; ?>"><?php echo $checks[0]->invoice_number . $paid; ?></a></h1>
<?php } else {
	echo "<hr>";
	$payment_link = "";
}
if ($payment_link!="") { ?>
<div  style="width:650px; margin-left:auto; margin-right:auto; text-align:left">Payments so far: $<?php echo $checks[0]->paids; ?>&nbsp;|&nbsp;<?php echo $payment_link; ?></div>
<?php } 
if (count($arrRows) > 0) {
?>
<table align="center" style="width:650px">
	<tr>
    	<th align="left" valign="top">Check #</th>
        <th align="left" valign="top">Check Date</th>
        <th align="left" valign="top">Payment</th>
        <th align="left" valign="top">Memo</th>
        <th align="left" valign="top">Entered On</th>
    </tr>
    <?php echo implode("\r\n", $arrRows); ?>
</table>
<?php } ?>
