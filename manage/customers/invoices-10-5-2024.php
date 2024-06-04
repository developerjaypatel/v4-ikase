<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../../shared/legacy_session.php');
session_write_close();

if($_SERVER['SERVER_NAME']=="starlinkcms.com")
{
  $application = "StarLinkCMS";
  $application_logo = "logo-starlinkcms.png";
  $application_url = "https://starlinkcms.com/";
}
else
{
  $application = "iKase";
  $application_logo = "ikase_logo_login.png";
  $application_url = "https://v2.ikase.org/";
}

include("sec.php");

include("../../api/connection.php");

$cus_id = passed_var("cus_id", "get");
$filter = "";
if (isset($_GET["filter"])) {
	$filter = passed_var("filter", "get");
}
if (isset($_POST["filter"])) {
	$filter = passed_var("filter", "post");
}

$today = mktime(0, 0, 0, date("m") - 1, date("d"),   date("Y"));
$six_months = mktime(0, 0, 0, date("m") + 5, date("d"),   date("Y"));
$twelve_months = mktime(0, 0, 0, date("m") + 11, date("d"),   date("Y"));

try {
	$sql = "SELECT inv.*, IFNULL(payments.paids, 0) paids 
	FROM ikase.cse_invoice inv
	LEFT OUTER JOIN (
		SELECT invoice_uuid, SUM(payment) paids
		FROM ikase.cse_check chk
		INNER JOIN ikase.cse_invoice_check ich
		ON chk.check_uuid = ich.check_uuid
		GROUP BY invoice_uuid
	) payments
	ON inv.invoice_uuid = payments.invoice_uuid
	WHERE id_collection = 'invoice'
	AND inv.customer_id = :customer_id
	AND inv.deleted = 'N'";
	if ($filter!="all" && $filter!="paid") {
		$sql .= " AND IFNULL(payments.paids, 0) < total";
	}
	if ($filter=="paid") {
		$sql .= " AND IFNULL(payments.paids, 0) >= total";
	}
	$sql .= "
	ORDER BY invoice_date ASC";
	//die($sql);
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->execute();
	$invoices = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$sql = "SELECT cus_name, cus_email 
	FROM ikase.cse_customer
	WHERE customer_id = :customer_id";
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	
	//echo $sql . "<br />";
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
$arrRows = array();
if (count($invoices)>0) {	
	$totals = 0;
	$paids = 0;
	$balances = 0;
	
	foreach($invoices as $invoice) {
		$balance = $invoice->total - $invoice->paids;
		$bold = " style='font-weight:bold;color:green'";
		$payment_link = "<span style='background:green;color:white;padding:2px'>paid&nbsp;&#10003;</span>";
		$delete_link = "<span id='feedback_delete_" . $invoice->invoice_id . "' style='padding:2px'><a href='javascript:invoiceDelete(" . $invoice->invoice_id . ")' style='background:red;color:white'>delete</a></span>";
		if ($balance > 0) {
			$bold = " style='font-weight:bold;color:red'";
			$payment_link = "<a href='invoice_payment.php?cus_id=" . $cus_id . "&invoice_id=" . $invoice->invoice_id . "'>Payments</a>";
		}
		
		//send email
		$notification_date = $invoice->notification_date;
		
		if ($notification_date=="0000-00-00 00:00:00") {
			$send_mail_link = "No email";
			if ($customer->cus_email!="") {
				$send_mail_link = "<a href='javascript:sendInvoice(" . $invoice->invoice_id . "," . $invoice->customer_id . ")'>Email Invoice</a>";
			}
		} else {
			$send_mail_link = "Sent on " . date("m/d/y", strtotime($notification_date)) . "<br><a href='javascript:sendInvoice(" . $invoice->invoice_id . "," . $invoice->customer_id . ")'>Email Invoice</a>";
		}
		$row = "
		<tr>
			<td align='left' valign='top' style='border-right:1px solid black'>
				<a href='invoice.php?cus_id=" . $cus_id . "&invoice_id=" . $invoice->invoice_id . "'>" . $invoice->invoice_number . "
				</a>
			</td>
			<td align='left' valign='top' style='border-right:1px solid black'>" . date("m/d/y", strtotime($invoice->invoice_date)) . "</td>
			<td align='left' valign='top' style='border-right:1px solid black' id='feedback_" . $invoice->invoice_id . "'>" . $send_mail_link . "</td>
			<td align='left' valign='top' style='border-right:1px solid black'>" . date("m/d/y", strtotime($invoice->start_date)) . "</td>
			<td align='left' valign='top' style='border-right:1px solid black'>" . date("m/d/y", strtotime($invoice->end_date)) . "</td>
			<td align='right' valign='top' style='border-right:1px solid black'>$" . number_format($invoice->total, 2) . "</td>
			<td align='right' valign='top' style='border-right:1px solid black'>$" . number_format($invoice->paids, 2) . "</td>
			<td align='right' valign='top' style='border-right:1px solid black' " . $bold . ">$" . number_format(($balance), 2) . "</td>
			<td align='left' valign='top'>" . $payment_link . "</td>
			<td align='left' valign='top'>" . $delete_link . "</td>
		</tr>";
		
		$totals += $invoice->total;
		$paids += $invoice->paids;
		$balances += $balance;
		
		$arrRows[] = $row;
	}
	
	//totals
	$row = "
	<tr>
		<td align='left' valign='top' colspan='5' style='font-weight:bold; background:#EDEDED; border:1px solid black'>Totals:</td>
		<td align='right' valign='top' style='font-weight:bold; background:#EDEDED; border:1px solid black'>$" . number_format($totals, 2) . "</td>
		<td align='right' valign='top' style='font-weight:bold; background:#EDEDED; border:1px solid black'>$" . number_format($paids, 2) . "</td>
		<td align='right' valign='top' style='font-weight:bold; background:#EDEDED; border:1px solid black'>$" . number_format(($balances), 2) . "</td>
		<td align='left' valign='top'>&nbsp;</td>
		<td align='left' valign='top'>&nbsp;</td>
	</tr>";
	
	$arrRows[] = $row;
}
$invoice_status = "Outstanding";
if ($filter=="all") {
	$invoice_status = "All";
}
if ($filter=="paid") {
	$invoice_status = "Paid";
}

?>
<html>
<head>
<title>List of <?php echo $invoice_status; ?> Invoices :: <?php echo $customer->cus_name; ?></title>
</head>
<body>
<div style="width:1082px; margin-left:auto; margin-right:auto">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td width="1%"><img src="../../img/<?= $application_logo; ?>" alt="<?= $application; ?>" height="32" /></td>
    <td nowrap><span style="font-weight:bold; font-size:1.5em">List of <?php echo $invoice_status; ?> Invoices :: <?php echo $customer->cus_name; ?></span></td>
    <td width="1%" nowrap="nowrap" align="right">
    	as of <?php echo date("m/d/Y"); ?>
    </td>
  </tr>
  <tr>
  	<td colspan="3" align="right">
    <div>
        	<a href="invoices.php?cus_id=<?php echo $cus_id; ?>&filter=paid">paid invoices</a>&nbsp;|&nbsp;<a href="invoices.php?cus_id=<?php echo $cus_id; ?>">outstanding invoices</a>&nbsp;|&nbsp;<a href="invoice.php?cus_id=<?php echo $cus_id; ?>">new invoice</a>&nbsp;|&nbsp;<a href="invoices_list.php">list all <?=$application;?> invoices</a>&nbsp;|&nbsp;<a href='index.php'>customers</a>
        </div>
    </td>
  </tr>
</table>
<hr />
<table width="60%" align="center">
	<tr>
    	<td width="33%" align="center"><a href="invoice.php?cus_id=<?php echo $cus_id; ?>">New Monthly Invoice</a>
        </td>
        <td width="33%" align="center">
        	<a href="invoice.php?cus_id=<?php echo $cus_id; ?>&start_date=<?php echo date("m", $today) . "/1/" . date("Y", $today); ?>&end_date=<?php echo date("m", $six_months) . "/" . date("t", $six_months) . "/" . date("Y", $six_months); ?>">New 6-Months Invoice</a>
        </td>
        <td width="33%" align="center">
        	<a href="invoice.php?cus_id=<?php echo $cus_id; ?>&start_date=<?php echo date("m", $today) . "/1/" . date("Y", $today); ?>&end_date=<?php echo date("m", $twelve_months) . "/" . date("t", $twelve_months) . "/" . date("Y", $twelve_months); ?>">New Yearly Invoice</a>
       </td>
    </tr>
</table>
<hr />
<?php if (count($invoices)==0) {
	echo "There are no invoices for this customer";
} else { ?>
<table cellpadding="2" cellspacing="0">
	<tr>
    	<th align="left" valign="top">Invoice #</th>
        <th align="left" valign="top">Invoice Date</th>
        <th align="left" valign="top"></th>
        <th align="left" valign="top">Start Date</th>
        <th align="left" valign="top">End Date</th>
        <th align="left" valign="top">Invoiced</th>
        <th align="left" valign="top">Paid</th>
        <th align="left" valign="top">Due</th>
        
        <th align="left" valign="top"></th>
        <th align="left" valign="top"></th>
  </tr>
    <?php echo implode("\r\n", $arrRows); ?>
</table>
<?php } ?>
</div>
<script language="javascript">
var invoiceDelete = function(invoice_id) {
	var r = confirm("Are you sure you want to delete this invoice?");
	if (r != true) {
		return;
	}
	var sendUrl = "invoice_delete.php";
	var cus_id = "<?php echo $cus_id; ?>";
	
	var formData = new FormData();
	formData.append("invoice_id", invoice_id);
	formData.append("cus_id", cus_id);
	
	var r = new XMLHttpRequest();
	r.open("POST", sendUrl, true);
	r.onreadystatechange = function () {
	  if (r.readyState != 4 || r.status != 200) {
		return;
	  } else {
		  var res = r.responseText;
		  if (res=="deleted") {
			  res = "<span style='background:red; color:white; padding:1px'>" + res + "&nbsp;&#10003;</span>";
		  }
		  document.getElementById("feedback_delete_" + invoice_id).innerHTML = res;
		  setTimeout(function() {
			  document.location.href = "invoices.php?cus_id=<?php echo $cus_id; ?>";
		  }, 1500);
	  }
	};
	r.send(formData);
}
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
			  document.getElementById("feedback_" + invoice_id).innerHTML = res;
		  }
		};
		r.send(formData);
	}
}
</script>
</body>
</html>
