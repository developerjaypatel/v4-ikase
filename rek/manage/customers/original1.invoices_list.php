<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/manage_session.php");
session_write_close();

include("sec.php");
include("../../api/connection.php");

$filter = passed_var("filter", "get");

try {
	$sql = "SELECT inv.*, IFNULL(payments.paids, 0) paids, cus.customer_id, cus.cus_name, cus.cus_email 
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
	WHERE id_collection = 'invoice'
	AND inv.deleted = 'N'";
	$subtitle = "";
	$sublink = "<a href='invoices_list.php'>show outstanding</a>&nbsp;|&nbsp;<a href='index.php'>customers</a>";
	if ($filter!="all") {
		$subtitle = " Outstanding ";
		$sublink = "<a href='invoices_list.php?filter=all'>show all</a>&nbsp;|&nbsp;<a href='index.php'>customers</a>";
		$sql .= " AND IFNULL(payments.paids, 0) < total";
	}
	$sql .= "
	ORDER BY cus.cus_name, invoice_date ASC";
	//die($sql);
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->execute();
	$invoices = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	//echo $sql . "<br />";
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
if (count($invoices)==0) {
	die("There are no invoices for this customer");
} else {
	$arrRows = array();
	$totals = 0;
	$paids = 0;
	$balances = 0;
	
	foreach($invoices as $invoice) {
		$balance = $invoice->total - $invoice->paids;
		
		$bold = " style='font-weight:bold;color:green'";
		$payment_link = "Paid&nbsp;&#10003;";
		if ($balance > 0) {
			$bold = " style='font-weight:bold;color:red'";
			$payment_link = "<a href='invoice_payment.php?cus_id=" . $invoice->customer_id . "&invoice_id=" . $invoice->invoice_id . "'>Payments</a>";
		}
		//send email
		$notification_date = $invoice->notification_date;
		if ($notification_date=="0000-00-00 00:00:00") {
			$send_mail_link = "No email";
			if ($invoice->cus_email!="") {
				$send_mail_link = "<a href='javascript:sendInvoice(" . $invoice->invoice_id . "," . $invoice->customer_id . ")'>Email Invoice</a>";
			}
		} else {
			$send_mail_link = "Sent on " . date("m/d/y", strtotime($notification_date));
		}
		
		$row = "
		<tr>
			<td align='left' valign='top'>" . $invoice->cus_name . "
			</td>
			<td align='left' valign='top'>
				<a href='invoices.php?cus_id=" . $invoice->customer_id . "'>List Invoices
				</a>
			</td>
			<td align='left' valign='top'>
				<a href='invoice.php?cus_id=" . $invoice->customer_id . "&invoice_id=" . $invoice->invoice_id . "'>" . $invoice->invoice_number . "</a>
			</td>
			<td align='left' valign='top'>" . date("m/d/y", strtotime($invoice->invoice_date)) . "</td>
			<td align='left' valign='top' id='feedback_" . $invoice->invoice_id . "'>" . $send_mail_link . "</td>
			<td align='right' valign='top'>$" . number_format($invoice->total, 2) . "</td>
			<td align='right' valign='top'>$" . number_format($invoice->paids, 2) . "</td>
			<td align='right' valign='top' " . $bold . ">$" . number_format(($balance), 2) . "</td>
			<td align='left' valign='top'>" . $payment_link . "</td>
		</tr>";
		$totals += $invoice->total;
		$paids += $invoice->paids;
		$balances += $balance;
		
		$arrRows[] = $row;
	}
	//totals
	$row = "
	<tr>
		<td align='right' valign='top' colspan='5' style='font-weight:bold; background:#EDEDED; border:1px solid black'>Totals:</td>
		<td align='right' valign='top' style='font-weight:bold; background:#EDEDED; border:1px solid black'>$" . number_format($totals, 2) . "</td>
		<td align='right' valign='top' style='font-weight:bold; background:#EDEDED; border:1px solid black'>$" . number_format($paids, 2) . "</td>
		<td align='right' valign='top' style='font-weight:bold; background:#EDEDED; border:1px solid black'>$" . number_format(($balances), 2) . "</td>
		<td align='left' valign='top'>&nbsp;</td>
	</tr>";
	
	$arrRows[] = $row;
}
?>
<html>
<head>
<title>List of Invoices</title>
</head>
<body>
<div style="width:1082px; margin-left:auto; margin-right:auto">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td width="1%"><img src="../../img/ikase_logo_login.png" alt="iKase" height="32" /></td>
    <td><span style="font-weight:bold; font-size:1.5em">List of <?php echo $subtitle; ?>Invoices</span></td>
    <td width="1%" nowrap="nowrap">
    	as of <?php echo date("m/d/Y"); ?>
        <div>
        	<?php echo $sublink; ?>
        </div>
    </td>
  </tr>
</table>
<hr />
<table border="0" cellpadding="2" cellspacing="0">
	<tr>
    	<th align="left" valign="top">Customer</th>
        <th align="left" valign="top">Invoices</th>
        <th align="left" valign="top">#</th>
        <th align="left" valign="top">Date</th>
        <th align="left" valign="top">Notification</th>
        <th align="left" valign="top">Invoiced</th>
        <th align="left" valign="top">Paid</th>
        <th align="left" valign="top">Due</th>
        <th align="left" valign="top">&nbsp;</th>
  </tr>
    <?php echo implode("\r\n", $arrRows); ?>
</table>
</div>
<script language="javascript">
var sendInvoice = function (invoice_id, cus_id) {
	var sendUrl = "invoice_send.php";
	
	mysentData = "invoice_id=" + invoice_id;
	var formData = new FormData();
	formData.append("invoice_id", invoice_id);
	formData.append("cus_id", cus_id);
	if (mysentData!='') {	
		var r = new XMLHttpRequest();
		r.open("POST", sendUrl, true);
		r.onreadystatechange = function () {
		  if (r.readyState != 4 || r.status != 200) {
			return;
		  } else {
			  var res = r.responseText;
			  if (res=="sent") {
				  res += "&nbsp;&#10003;";
			  }
			  document.getElementById("feedback_" + invoice_id).innerHTML = r.responseText;
		  }
		};
		r.send(formData);
	}
}
</script>
</body>
</html>