<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/manage_session.php");
include("../../api/connection.php");

$suid = "";
$lastmonth = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));

$start_date = date("m", $lastmonth) . "/1/" . date("Y", $lastmonth);
$end_date = date("m/t/Y", $lastmonth);

if (isset($_GET["start_date"])) {
	$start_date = passed_var("start_date", "get");
	$end_date = passed_var("end_date", "get");
}
$cus_id = passed_var("cus_id");
$invoice_id = -1;
if (isset($_GET["invoice_id"])) {
	$invoice_id = passed_var("invoice_id", "get");
}
if (isset($_POST["invoice_id"])) {
	$invoice_id = passed_var("invoice_id", "post");
}
if ($invoice_id==-1) {
	try {
		$sql = "SELECT * 
		FROM ikase.cse_invoice
		WHERE id_collection = 'invoice'
		AND deleted = 'N'
		AND customer_id = :customer_id
		AND start_date = '" . date("Y-m-d", strtotime($start_date)) . "'
		AND end_date = '" . date("Y-m-d", strtotime($end_date)) . "'";
		//echo $sql . "<br />";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $cus_id);

		$stmt->execute();
		$check_invoice = $stmt->fetchObject();
		if (is_object($check_invoice)) {
			$invoice_id = $check_invoice->invoice_id;
		}
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//echo $sql . "<br />";
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		die( print_r($error));
	}
}
if (isset($_GET["suid"])) {
	$suid = passed_var("suid", "get");
}

$blnProceed = true;
if ($suid=="outstanding") {
	$blnProceed = (is_numeric($cus_id) && $invoice_id > -1);
	if ($blnProceed) {
		$_SESSION["user_plain_id"] = "12";
		$_SESSION["user_role"] = "owner";
	}
}
session_write_close();

if (!isset($_SESSION["user_plain_id"])) {
	die("no id");
}
if ($_SESSION["user_role"]!="owner") {
	die("no go");
}

$sql = "SELECT cus.*
FROM ikase.cse_customer cus
WHERE cus.customer_id  = :customer_id";

$dbPDO = getConnection();
$stmt = $dbPDO->prepare($sql);
$stmt->bindParam("customer_id", $cus_id);
$stmt->execute();
$cus = $stmt->fetchObject();

$stmt->closeCursor(); $stmt = null; $dbPDO = null;

$cus_email = $cus->cus_email;

$paids = 0;
$invoice_items = "&nbsp;";
$active_users = "&nbsp;";
$invoice_number = "";
$notification_date = "&nbsp;";
$invoice_total = 0;
$paids = "0";
if ($invoice_id > -1) {	
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
		AND inv.deleted = 'N'
		AND inv.customer_id = :customer_id
		AND inv.invoice_id = :invoice_id";

		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $cus_id);
		$stmt->bindParam("invoice_id", $invoice_id);
		//echo "checking" . $invoice_id;
		$stmt->execute();
		$invoice = $stmt->fetchObject();
		$paids = $invoice->paids;
		$invoice_items = $invoice->invoice_items;
		$invoice_total = $invoice->total;
		$active_users = $invoice->active_users;
		$invoice_number = $invoice->invoice_number;
		if ($invoice->start_date!="0000-00-00") {
			$start_date = date("m/d/Y", strtotime($invoice->start_date));
			$end_date = date("m/d/Y", strtotime($invoice->end_date));
		}
		$notification_date = $invoice->notification_date;
		if ($notification_date=="0000-00-00 00:00:00") {
			if ($cus_email!="") {
				$notification_date = "<span class='menu_nav'><a href='javascript:sendInvoice(" . $cus_id . "," . $invoice_id . ")'>Send Invoice</a>&nbsp;|&nbsp;</span>";
			} else {
				$notification_date = "CANNOT SEND - NO EMAIL ADDRESS";
			}
		} else {
			$notification_date = "Sent on " . date("m/d/y", strtotime($notification_date)) . "&nbsp;|&nbsp;";
		}
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//echo $sql . "<br />";
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		die( print_r($error));
	}
}

if (!is_numeric($invoice_id)) {
	die("no invoice");
}

$blnOwnerAdmin = ($_SESSION["user_role"] == "owner");

$query = "SELECT  cus.`customer_id` cus_id, `parent_customer_id` parent_cus_id, `eams_no`, 
`cus_barnumber`,
`cus_name`, `cus_name_first`, `cus_name_middle`, `cus_name_last`, 
`cus_street`, `cus_city`, `cus_state`, `cus_zip`, `cus_county`, `start_date`,
`cus_ip`, `admin_client`, `password`, `cus_email`, `cus_type`, `cus_phone`, `cus_fax`, `data_source`, `data_path`, `permissions`, `inhouse_id`,`office_manager_first`, `office_manager_last`, `office_manager_middle`, `office_manager_phone`, `office_manager_email`, cus_fedtax_id, cus_uan, corporation_rate, user_rate, `user_count`, user_names
FROM ikase.cse_customer cus
INNER JOIN (
	SELECT customer_id, `user_count`, user_names
	FROM `ikase`.cse_active_users
	WHERE (active_month = '" . date("n", $lastmonth) . "' OR active_month = '" . date("n") . "')
	AND active_year = '" . date("Y", $lastmonth) . "'
	AND customer_id = :customer_id
	ORDER BY `user_count` DESC
	LIMIT 0, 1
	) user_count
ON cus.customer_id = user_count.customer_id
WHERE cus.customer_id = :customer_id";

//die($query);
$db = getConnection();
$stmt = $db->prepare($query);
$stmt->bindParam("customer_id", $cus_id);
$stmt->execute();
$customer = $stmt->fetchObject();
$stmt->closeCursor(); $stmt = null; $db = null;

//die(print_r($customer));

$cus_id = $customer->cus_id;
$eams_no = $customer->eams_no;
$parent_cus_id = $customer->parent_cus_id;
$cus_eams_no = $customer->eams_no;
$cus_barnumber = $customer->cus_barnumber;
$cus_name = $customer->cus_name;
$cus_name_first = $customer->cus_name_first;
$cus_name_middle = $customer->cus_name_middle;
$cus_name_last = $customer->cus_name_last;
$cus_street = $customer->cus_street;
$cus_city = $customer->cus_city;
$cus_state = $customer->cus_state;
$cus_zip = $customer->cus_zip;
$cus_county = $customer->cus_county;
$password = $customer->password;
$cus_email = $customer->cus_email;
$cus_phone = $customer->cus_phone;
$cus_fax = $customer->cus_fax;
$cus_type = $customer->cus_type;
$cus_ip = $customer->cus_ip;
$cus_fedtax_id = $customer->cus_fedtax_id;
$cus_uan = $customer->cus_uan;
$data_source = $customer->data_source;
$data_path = $customer->data_path;
$data_path = str_replace("/", "\\", $data_path);
$permissions = $customer->permissions;
$inhouse_id = $customer->inhouse_id;

$user_rate = $customer->user_rate;
$corporation_rate = $customer->corporation_rate;

$office_manager_first = $customer->office_manager_first;
$office_manager_middle = $customer->office_manager_middle;
$office_manager_last = $customer->office_manager_last;
$office_manager_email = $customer->office_manager_email;
$office_manager_phone = $customer->office_manager_phone;

$user_count = $customer->user_count;
$user_names = $customer->user_names;
$user_names = ucwords(strtolower(implode(", ", explode(",", $user_names))));

//die($user_names);
if ($invoice_items=="" || $invoice_items=="&nbsp;") {
	$invoice_total = "";	//$user_count * $customer->user_rate;
	$active_users = "";	//'<span style="font-weight:bold">Active Users</span> - ' . $user_count . '<div style="margin-top:10px;">' . $user_names . '</div>';
	
	$invoice_items = "";
	/*'
	<tr class="invoice_row">
		<td align="left" valign="top" style=" border-top:1px solid black">' . $user_count. ' users</td>
		<td align="center" valign="top" style=" border-top:1px solid black">$' . $customer->user_rate . '</td>
		<td align="right" valign="top" style="font-weight:bold; border-top:1px solid black">$' . number_format($invoice_total, 2) . '</td>
	</tr>'
	*/;
	//die($invoice_items);
} else {
	//die($invoice_items . ":not");
}
/*
//users
$query = "SELECT COUNT(`user_id`) `user_count`
FROM cse_user WHERE customer_id = " . $cus_id;
$result_users = mysql_query($query, $r_link) or die("unable to run query<br />" .$query . "<br>" .  mysql_error());
$user_count = mysql_result($result_users, 0, "user_count");
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex, nofollow" />
<title>iKase Invoice</title>
<link rel="stylesheet" type="text/css" href="../../css/jquery.datetimepicker.css" />
<script language="javascript" src="../../lib/jquery.1.10.2.js"></script>
<script language="javascript" src="../../lib/jquery.datetimepicker.js"></script>
<script language="javascript" src="../../lib/moment.min.js"></script>
<?php if ($suid=="outstanding") { ?>
<script language="javascript">
	window.history.replaceState({}, "nick", "../../invoice.php");
</script>
<?php } ?>
</head>

<body onload="init()">
<table width="800px" border="0" cellspacing="0" cellpadding="2" align="center">
  <tr>
    <td align="left" valign="top"><img src="https://www.ikase.org/img/ikase_logo_login.png" alt="iKase" height="32" /><br />
      support@ikase.org</td>
    <td align="center" valign="top" nowrap="nowrap">
    	<span style="font-weight:bold; font-size:1.5em"><span id="invoice_qualifier">New </span>Invoice</span>
    </td>
    <td align="right" valign="top">
        Invoice #: <span id="invoice_number_holder"><?php echo $invoice_number; ?></span>
      <input type="hidden" name="invoice_number" id="invoice_number" value="" />
      <input type="hidden" name="invoice_id" id="invoice_id" value="<?php echo $invoice_id; ?>" />
      <textarea name='invoice_items' id='invoice_items' style="display:none"><?php echo $invoice_items; ?></textarea>
      <textarea name='active_users' id='active_users' style="display:none"><?php echo $active_users; ?></textarea>
        <br />
        Date:<?php echo date("m/d/Y"); ?>
        <?php if ($suid!="outstanding") { ?>
            <div class="menu_nav" style="display:">
                <a href="javascript:printPage()">print</a>&nbsp;|&nbsp;<a href="index.php?cus_id=<?php echo $cus_id; ?>">customers</a>&nbsp;|&nbsp;<a href="invoice.php?cus_id=<?php echo $cus_id; ?>">new invoice</a>&nbsp;|&nbsp;<a href="invoices.php?cus_id=<?php echo $cus_id; ?>">this customer invoices</a>&nbsp;|&nbsp;<a href="invoices_list.php">all invoices</a>
                <?php if ($invoice_id > 0) { ?>
                <br />
                <span id="feedback_delete"><a href="javascript:invoiceDelete()" style="color:red">delete</a></span>
                <?php } ?>
            </div>
            <?php if ($blnOwnerAdmin) { ?>
            <br />
                <?php 
                $blnPaidOff = (($user_count * $user_rate) == $paids);
                if (isset($invoice)) { ?>
                <?php echo $notification_date; ?>Payments:&nbsp;$<?php echo $paids; ?>
                <?php if ($blnPaidOff ) { ?>
                <span style="color:green">Paid &#10003;</span>
                <?php } else { ?>
                <span class='menu_nav'>&nbsp;|&nbsp;
                <a href="invoice_payment.php?cus_id=<?php echo $cus_id; ?>&invoice_id=<?php echo $invoice_id; ?>">Payments</a></span>
                <?php } ?>
                
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </td>
    </td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3"><hr /></td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3"><div>
      <div id="date_range_holder" style="display:none"> Date Range:
      	<?php 
		echo $start_date. " through " . $end_date;  
		?>
        <div id="invoices_count"></div>
      </div>
      <div class="menu_nav">
       Date Range: <input name="start_date" id="start_date" type="<?php echo $input_type; ?>" class="date_input" size="8" value="<?php echo $start_date; ?>" /> through
        <input name="end_date" id="end_date" type="<?php echo $input_type; ?>" class="date_input" size="8" value="<?php echo $end_date; ?>" />
        </div>
      <?php if ($suid!="outstanding") { ?>
    <div class="menu_nav">
	    <a href="#" onclick="setRange(event,6)">6 months</a>&nbsp;|&nbsp;<a href="#" onclick="setRange(event,12)">1 year</a>
    </div>
      <?php } ?>
    </div></td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3"><hr /></td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3">
    	Bill To: <?php echo "<b>" . $cus_name . "</b><br />" . ucwords(strtolower($cus_street . ", " . $cus_city)) . ", " . $cus_state . " " . $cus_zip; ?>
    </td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3"><hr /></td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3" id="invoice_users"><?php echo $active_users; ?></td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3"><hr /></td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3">
        <table width="100%" border="0" cellspacing="0" cellpadding="2" id="invoice_table">
          <tr>
            <td nowrap="nowrap" width="600"><strong>Item</strong></td>
            <td nowrap="nowrap"><strong>Monthly Rate</strong></td>
            <td nowrap="nowrap" align="right"><strong> Total</strong></td>
          </tr>
          <?php echo $invoice_items; ?>
        </table>
    </td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="3"><hr /></td>
  </tr>
  <?php if ($suid!="outstanding") { ?>
  <tr align="left" valign="top" style="display:" class="menu_nav">
    <td colspan="3" id="save_holder">
    	<input type="button" name="save_invoice" id="save_invoice" value="Save Invoice" />
    </td>
  </tr>
  <?php } ?>
  <tr align="left" valign="top">
    <td colspan="3"><font size="-1">Please remit copy of invoice with payment.<b><br />
 Balance Due is subject to 10% penalty and 7% interest on any unpaid balance 
  after 60 days</b></font></td>
  </tr>
</table>
<script language="javascript">
var invoice_total = "<?php echo $invoice_total; ?>";
var saveInvoice = function() {
	var url = 'invoice_save.php';
	var invoice_id = $("#invoice_id").val();
	var start_date = $("#start_date").val();
	var end_date = $("#end_date").val();
	var invoice_number = $("#invoice_number").val();
	var invoice_items = $("#invoice_items").val();
	var active_users = $("#active_users").val();
	formValues = "cus_id=<?php echo $cus_id; ?>&invoice_id=" + invoice_id + "&invoice_number=" + invoice_number + "&start_date=" + start_date + "&end_date=" + end_date + "&invoice_items=" + encodeURIComponent(invoice_items) + "&active_users=" + encodeURIComponent(active_users);
	formValues += "&total=" + invoice_total;
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			//if(data.indexOf("error") > -1) {  // If there is an error, show the error messages
			if (data.error) {
				console.log(data.text);
			} else {
				$("#invoice_id").val(data.invoice_id);
				var cus_id = "<?php echo $cus_id; ?>";
				var send_invoice = "&nbsp;|&nbsp;<span id='feedback'><input type='button' name='send_invoice' id='send_invoice' value='Send Invoice' onclick='sendInvoice(" + data.invoice_id + "," + cus_id + ")' /></span>";
				$("#save_holder").html(data.result + send_invoice);
				$("#invoice_qualifier").html("Edit&nbsp;");
				var invoice_id = data.invoice_id;
				var start_date = $("#start_date").val();
				var end_date = $("#end_date").val();
				
				var formValues = "cus_id=" + cus_id;
				formValues += "&start_date=" + start_date;
				formValues += "&end_date=" + end_date;
				formValues += "&invoice_id=" + invoice_id;
				window.history.replaceState({}, "nick", "invoice.php?" + formValues)
			}
		}
	});
}
var generateInvoiceItems = function() {
	var url = 'invoice_generate_rows.php';
	var start_date = $("#start_date").val();
	var end_date = $("#end_date").val();
	formValues = "cus_id=<?php echo $cus_id; ?>&start_date=" + start_date + "&end_date=" + end_date;
	
	window.history.replaceState({}, "nick", "invoice.php?" + formValues);
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			//if(data.indexOf("error") > -1) {  // If there is an error, show the error messages
			if (data.error) {
				console.log(data.text);
			} else {
				invoice_total = data.total;
				$(".invoice_row").remove();
				$("#invoice_table").append(data.html);
				$("#invoice_items").val(data.html);
				$("#invoice_users").html(data.users);
				$("#active_users").html(data.users);
			}
		}
	});
}
var verifyInvoiceItems = function() {
	//return;
	
	var url = 'invoice_verify_dates.php';
	var start_date = $("#start_date").val();
	var end_date = $("#end_date").val();
	formValues = "cus_id=<?php echo $cus_id; ?>&start_date=" + start_date + "&end_date=" + end_date;
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			//if(data.indexOf("error") > -1) {  // If there is an error, show the error messages
			if (data.error) {
				console.log(data.text);
			} else {
				
				//not even one
				if (data.invoices > 0 && data.invoice_ids!=<?php echo $invoice_id; ?>) {
					var arrInvoiceIds = data.invoice_ids.split(",");
					document.location.href = 'invoice.php?cus_id=<?php echo $cus_id; ?>&invoice_id=' + arrInvoiceIds[0] + '&start_date=' + start_date + '&end_date=' + end_date;
					return;
					
					var arrLinks = [];
					arrInvoiceIds.forEach(function(element) {
						if (element!=<?php echo $invoice_id; ?>) {
							//add link to match	
							arrLinks.push("<div><a href='invoice.php?cus_id=<?php echo $cus_id; ?>&invoice_id=" + element + "'>Review</a></div>");
						}
					});
					
					$("#date_range_holder").css("background", "red");
					$("#invoices_count").html("<span style='font-style:italic; color:white'>There is already an invoice covering this date range</span>" + arrLinks.join(""));
					$("#save_button").attr("disabled", true);
				} else {
					$("#invoice_id").val(-1);
					$("#date_range_holder").css("background", "none");
					$("#save_button").attr("disabled", false);
					$("#date_range_holder").html("Date Range: " + start_date + " through " + end_date);
					setInvoiceNumber();
					generateInvoiceItems();
				}
			}
		}
	});
}
var setInvoiceNumber = function() {
	var start_date = $("#start_date").val();
	var start_month = moment(start_date).format("YYM");
	var end_date = $("#end_date").val();
	var end_month = moment(end_date).format("YYM");
	
	months = Number(end_month) - Number(start_month);
	
	var invoice_number = "<?php echo $cus_id; ?>-" + moment(start_date).format("YYMM") + moment(end_date).format("YYMM");
	if (months == 0) {
		invoice_number = "<?php echo $cus_id; ?>-" + moment(start_date).format("YYMM");
	}
	$("#invoice_number").val(invoice_number);
	$("#invoice_number_holder").html(invoice_number);
}
var setRange = function(event, months) {
	if (event!=0) {
		event.preventDefault();
	} else {
		months = 0;
	}
	var start_date = $("#start_date").val();
	var year = moment(start_date).format("YYYY");
	var month = moment(start_date).format("M");
	var end_date = moment([year, month]).add(months,"month")._d;
	end_date = moment(end_date).add(-1, "day");
	end_date = moment(end_date).format("MM/DD/YYYY");
	//set the date
	$("#end_date").val(end_date);
	
	setInvoiceNumber();
	
	verifyInvoiceItems();
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
				  res = "<span style='background:green; color:white; padding:1px'>" + res + "&nbsp;&#10003;</span>";
			  }
			  document.getElementById("feedback").innerHTML = res;
		  }
		};
		r.send(formData);
	}
}
var invoiceDelete = function() {
	var r = confirm("Are you sure you want to delete this invoice?");
	if (r != true) {
		return;
	}
	var sendUrl = "invoice_delete.php";
	var cus_id = "<?php echo $cus_id; ?>";
	var invoice_id = $("#invoice_id").val();
	
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
		  document.getElementById("feedback_delete").innerHTML = res;
		  setTimeout(function() {
			  document.location.href = "invoices.php?cus_id=<?php echo $cus_id; ?>";
		  }, 1500);
	  }
	};
	r.send(formData);
}
var init = function() {
	$("#save_invoice").on("click", saveInvoice);
	$(".date_input").on("blur", generateInvoiceItems);
	$('.date_input').datetimepicker({
		format:"m/d/Y",
		timepicker: false,
		onChangeDateTime:
			function(dp,$input){
				var start_date = $("#start_date").val();
				var end_date = $("#end_date").val();
				var d1 =  new Date(moment(start_date));
				var d2 =  new Date(moment(end_date));
				var diff = d2.getTime() - d1.getTime();
				if (diff < 0) {
					end_date = start_date;
					$("#end_date").val(end_date);
				}
				if ($input[0].name=="start_date") {
					//in this case, we only allow first and last
					var start_date = moment(start_date).format("MM") + "/1/" + moment(start_date).format("YYYY");
					var end_date = moment(start_date).endOf('month').format("MM/DD/YYYY");
					$("#start_date").val(start_date);
					$("#end_date").val(end_date);
				}
				verifyInvoiceItems();
		}
	});
	
	<?php if ($invoice_id != "-1") { ?>
		$("#invoice_qualifier").html("Customer&nbsp;");
	<?php } else { 
		if (!isset($_GET["start_date"])) { ?>
		setRange(0);
	<?php } ?>
		setInvoiceNumber();
		verifyInvoiceItems();
	<?php
	}
	?>
}
function printPage() {
	$(".menu_nav").hide();
	$("#date_range_holder").show();
	window.print();
}
</script>
</body>
</html>