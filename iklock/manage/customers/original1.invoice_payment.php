<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/manage_session.php");
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
$blnOwnerAdmin = ($_SESSION["user_role"] == "owner");
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
	AND inv.invoice_id = :invoice_id";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->bindParam("invoice_id", $invoice_id);
	$stmt->execute();
	$invoice = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	//echo $sql . "<br />";
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( print_r($error));
}	
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../../css/jquery.datetimepicker.css" />
<script language="javascript" src="../../lib/jquery.1.10.2.js"></script>
<script language="javascript" src="../../lib/jquery.datetimepicker.js"></script>
</head>
<body onLoad="init()">
<form action="invoice_payment_insert.php" method="post" enctype="multipart/form-data">
	<table width="850px" border="0" align="center" cellpadding="2" cellspacing="0">
	  <tr>
	    <td align="left" valign="top"><img src="https://v2.ikase.org/img/ikase_logo_login.png" alt="iKase" height="32" /><br />
	      support@ikase.org</td>
	    <td align="center" valign="top" nowrap="nowrap"><span style="font-weight:bold; font-size:1.5em">Payment</span></td>
	    <td align="right" valign="top"> Invoice #: <span id="invoice_number_holder"><?php echo $invoice->invoice_number; ?></span>
	      <input type="hidden" name="invoice_number" id="invoice_number" value="" />
	      <input type="hidden" name="invoice_id2" id="invoice_id2" value="<?php echo $invoice->invoice_id; ?>" />
	      <br />
	      Date:<?php echo date("m/d/Y", strtotime($invoice->invoice_date)); ?>
	      
	      <div> <a href="index.php?cus_id=<?php echo $cus_id; ?>">customers</a>&nbsp;|&nbsp;<a href="invoices.php?cus_id=<?php echo $cus_id; ?>">this customer invoices</a>&nbsp;|&nbsp;<a href="invoices_list.php">all ikase invoices</a> </div>
	      </td>
      </tr>
	  <tr align="left" valign="top">
	    <td colspan="3"><hr /></td>
      </tr>
	  <?php if ($invoice_id == "-1") { ?>
	  <?php } ?>
  </table>
  <input type="hidden" id="cus_id" name="cus_id" value="<?php echo $cus_id; ?>" />
    <input type="hidden" id="invoice_id" name="invoice_id" value="<?php echo $invoice_id; ?>" />
  <table border="0" cellspacing="0" cellpadding="2" align="center">
    <tr>
      <td align="left" valign="top">Invoice Number</td>
      <td align="left" valign="top"><a href="invoice.php?cus_id=<?php echo $cus_id; ?>&invoice_id=<?php echo $invoice->invoice_id; ?>"><?php echo $invoice->invoice_number; ?></a></td>
    </tr>
    <tr>
      <td align="left" valign="top">Invoice Date</td>
      <td align="left" valign="top"><?php echo date("m/d/Y", strtotime($invoice->invoice_date)); ?></td>
    </tr>
    <?php if ($invoice->paids >0) { ?>
    <tr>
      <td align="left" valign="top">Amount Paid So Far</td>
      <td align="left" valign="top">$<?php echo $invoice->paids; ?></td>
    </tr>
    <?php } ?>
    <tr>
      <td align="left" valign="top">Amount Due</td>
      <td align="left" valign="top" style="font-weight:bold">
      	$<?php echo number_format(($invoice->total - $invoice->paids), 2); ?>
        <input type="hidden" id="invoice_total" name="invoice_total" value="<?php echo $invoice->total - $invoice->paids; ?>" />
      </td>
    </tr>
    <tr>
      <td align="left" valign="top">Check Number</td>
      <td align="left" valign="top"><input type="text" name="check_number" id="check_number" autocomplete="off" class="required"></td>
    </tr>
    <tr>
      <td align="left" valign="top">Check Date</td>
      <td align="left" valign="top"><input type="text" name="check_date" id="check_date" autocomplete="off" class="required"></td>
    </tr>
    <tr>
      <td align="left" valign="top">Payment</td>
      <td align="left" valign="top"><input type="number" name="payment" id="payment" autocomplete="off" class="required"></td>
    </tr>
    <tr>
      <td align="left" valign="top">Memo</td>
      <td align="left" valign="top"><textarea name="memo" id="memo" cols="45" rows="5"></textarea></td>
    </tr>
    <tr>
      <td align="left" valign="top">Balance</td>
      <td align="left" valign="top">
      	<span id="balance"></span>
        <input type="hidden" id="balance_due" name="balance_due" value="" />
      </td>
    </tr>
    <tr>
      <td align="left" valign="top">&nbsp;</td>
      <td align="left" valign="top"><input type="submit" name="save_invoice" id="save_invoice" value="Save Payment" disabled></td>
    </tr>
  </table>
</form>
<?php $bottom_list = "yes"; include("invoice_payments.php"); ?>
<script language="javascript">
var init = function() {
	$('#check_date').datetimepicker({
		format:"m/d/Y",
		timepicker: false
	});
	
	//monitor payment
	$("#payment").on("keyup", function() {
		var payment = $("#payment").val();
		var invoice_total = $("#invoice_total").val();
		
		var balance = Number(invoice_total) - Number(payment);
		var new_balance = "$" + String(balance.toFixed(2));
		$("#balance").html(new_balance);
		
		if (balance == 0) {
			$("#balance").html(new_balance + "&nbsp;&#10003;");
		}
		if (balance < 0) {
			$("#balance").html(new_balance + "&nbsp;<span style='color:red'>over paying</span>");
		}
		if (balance > 0) {
			$("#balance").html(new_balance + "&nbsp;<span style='color:orange'>partial payment</span>");
		}
		$("#balance_due").val(balance);
	});
	
	$(".required").on("keyup", function() {
		var requireds = $(".required");
		var blnDisabled = false;
		for(var i = 0; i < requireds.length; i++) {
			var required = requireds[i];
			if (required.value=="") {
				blnDisabled = true;
				break;
			}
		}
		
		$("#save_invoice").prop("disabled", blnDisabled);
	});
	
	setTimeout(function() {
		document.getElementById("check_number").focus();
	}, 300);
}
</script>
</body>
</html>