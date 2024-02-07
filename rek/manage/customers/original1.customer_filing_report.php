<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$cus_id = -1;
include("../../logon_check.php");

$customer_id = passed_var("customer_id");
$activity = passed_var("activity");
$report_date = passed_var("report_date");
$end_date = passed_var("end_date");

$query = "SELECT  tbl_customer.`cus_id`, `cus_name`, `cus_name_first`, `cus_name_middle`, `cus_name_last`, `cus_email`
FROM tbl_customer 
WHERE 1
ORDER BY `cus_name`";
//echo $query;
$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);

if ($report_date=="") {
	$report_date = date("Y-m-d");
	$end_date = date("Y-m-d");
}
for ($int=0;$int<$numbs;$int++) {
	$the_cus_id = mysql_result($result, $int, "cus_id");
	$selected = "";
	if ($the_cus_id==$customer_id) {
		$selected = " selected";
	}
	$arrCus[] = $the_cus_id;
	//anything late for me
	$the_cus_name = mysql_result($result, $int, "cus_name");		
	$the_row = "<option value='" . $the_cus_id . "'" . $selected . ">" . $the_cus_name . "</option>";
	$arrCustomerRows[] = $the_row;
}
//might be looking for just one
if ($customer_id!="") {
	//reset the list
	$arrCus = array($customer_id);
}
$querylate = "SELECT DISTINCT cus.cus_id, cus.cus_name, thefiling.case_id, thefiling.date_served filing_date, inv.invoice_total, serve_check.check_id serve_check_id, serve_check.amount serve_check_amount, custodian_check.check_id custodian_check_id, custodian_check.amount custodian_check_amount
FROM tbl_serve thefiling
INNER JOIN tbl_case thecase
ON thefiling.case_id = thecase.case_id
INNER JOIN tbl_client theclient
ON thecase.client_id = theclient.client_id
INNER JOIN tbl_customer cus
ON theclient.cus_id = cus.cus_id";
$invoice_join = " LEFT OUTER JOIN ";
if ($activity=="invoiced") {
	$invoice_join = " INNER JOIN ";
}
$querylate .= $invoice_join. " tbl_invoice inv
ON thefiling.serve_id = inv.serve_id";

//Payments
$invoice_join = " LEFT OUTER JOIN ";
if ($activity=="paid") {
	$invoice_join = " INNER JOIN ";
}
$querylate .= $invoice_join . " tbl_check serve_check
	ON (thefiling.serve_id = serve_check.serve_id AND serve_check.name = 'serve' AND serve_check.deleted = 'N')
	" . $invoice_join . " tbl_check custodian_check
	ON (thefiling.serve_id = custodian_check.serve_id AND custodian_check.name = 'custodian' AND custodian_check.deleted = 'N')";

$querylate .= " WHERE 1 ";
if ($end_date==$report_date) {
	$querylate .= " AND thefiling.date_served = '" . $report_date . "'";
} else {
	$querylate .= " AND thefiling.date_served BETWEEN '" . date("Y-m-d", strtotime($report_date)) . "' AND '" . date("Y-m-d", strtotime($end_date)) . "'";
}
$querylate .= " AND theclient.cus_id IN (" . implode(",", $arrCus) . ")
ORDER BY cus.cus_name, thefiling.date_served";
//echo $querylate;
$resultlate = mysql_query($querylate, $r_link) or die("unable to get lates<br />" . $querylate.  "<br />" . mysql_error());
$numberlate = mysql_numrows($resultlate);

$current_cus_id = -1;
$arrRows = array();
for($intL=0;$intL<$numberlate;$intL++) {
	$the_cus_id = mysql_result($resultlate, $intL, "cus_id");	
	
	if (!isset($arrTotals[$the_cus_id]["invoice_total"])) {
		$arrTotals[$the_cus_id]["invoice_total"] = 0;
		$arrTotals[$the_cus_id]["paid_total"] = 0;
	}
	
	$invoice_total = mysql_result($resultlate, $intL, "invoice_total");
	$serve_check_amount = mysql_result($resultlate, $intL, "serve_check_amount");
	$custodian_check_amount = mysql_result($resultlate, $intL, "custodian_check_amount");
	$arrTotals[$the_cus_id]["invoice_total"] += $invoice_total;
	$arrTotals[$the_cus_id]["paid_total"] += $serve_check_amount + $custodian_check_amount;
}
for($intL=0;$intL<$numberlate;$intL++) {
	$the_cus_id = mysql_result($resultlate, $intL, "cus_id");	
	$cus_name = mysql_result($resultlate, $intL, "cus_name");

	if ($current_cus_id != $the_cus_id) {
		if ($intL > 0) {
			$arrRows[] = "<div style='width:550px; padding-top:20px'></div>";
		}
		$arrRows[] = "<div style='width:550px; padding-top:1px solid black; font-weight:bold; width:580px; margin-left:auto; margin-right:auto'><div style='float:right' id='customer_paid_" . $the_cus_id . "'></div><div style='float:right' id='customer_invoiced_" . $the_cus_id . "'></div><div style='float:right' id='customer_count_" . $the_cus_id . "'></div>" . $cus_name . "</div><div class='case_row_" . $the_cus_id ."' style='display:none; width:580px; margin-left:auto; margin-right:auto'><div style='display:inline-block; padding:5px; width:100px'>Case ID</div><div style='display:inline-block; padding:5px; width:100px'>Filing Date</div><div style='display:inline-block; padding:5px; width:150px'>Invoiced</div><div style='display:inline-block; padding:5px; width:150px'>Paid</div></div>";
		$current_cus_id = $the_cus_id;
		$arrCases[$the_cus_id] = 0;
	}
	
	$the_case_id = mysql_result($resultlate, $intL, "case_id");
	$arrCases[$the_cus_id]++;
	$the_submitted_date = mysql_result($resultlate, $intL, "filing_date");
	$invoice_total = mysql_result($resultlate, $intL, "invoice_total");
	$serve_check_amount = mysql_result($resultlate, $intL, "serve_check_amount");
	$custodian_check_amount = mysql_result($resultlate, $intL, "custodian_check_amount");
	$paid_total = $serve_check_amount + $custodian_check_amount;
	if ($invoice_total > 0) {
		$invoice_total = "$" . $invoice_total;
	}
	if ($paid_total > 0) {
		$paid_total = "$" . number_format($paid_total, 2);
	} else {
		$paid_total = "&nbsp;";
	}
	$case_info = "<div style='display:inline-block; padding:5px; width:100px'><a href='../../form1_basic.php?cus_id=-1&suid=". $suid . "&case_id=" . $the_case_id . "' target='_blank'>" . $the_case_id . "</a></div><div style='display:inline-block; padding:5px; width:150px'>" . date("m/d/y", strtotime($the_submitted_date)) . "</div>";
	$case_info .= "<div style='display:inline-block; padding:5px; width:100px'>" . $invoice_total . "</div>";
	$case_info .= "<div style='display:inline-block; padding:5px; width:100px'>" . $paid_total . "</div>";
	
	$arrRows[] = "<div class='case_row_" . $the_cus_id ."' style='display:none; border-bottom:1px solid black; width:580px; margin-left:auto; margin-right:auto'>" . $case_info . "</div>";
}

mysql_close($r_link);
$maincontent = implode("\r\n", $arrRows);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Customer Activity Report :: DMS Custodian</title>
</head>
<body class="yui-skin-sam">
<style>
div {
	padding:2px;
}
</style>
<div style="text-align:center"><h2>DMS Custodian - Activity Report <? if ($activity!="") { echo "- " . ucwords($activity); } ?></h2></div>
<form action="customer_filing_report.php" method="post" enctype="multipart/form-data">
<input type="hidden" value="<?php echo $suid; ?>" name="suid" />
<table align="center">
	<tr>
   	  <td>Report Date:</td>
      <td nowrap="nowrap">
            <input type="text" name="report_date" id="report_date" value="<?php echo date("m/d/Y", strtotime($report_date)); ?>" size="7" />&nbsp;<img src="../../images/calendar.jpg" width="20" height="20" alt="Calendar" onclick="showCalendar('report')" />
        <div id="calendar_holder_report" style="margin-left:100px; display:none;">
            	<div id="cal_reportContainer" style="position:absolute; z-index:10"></div>
          </div> 
        <span class="formobj"><a href="javascript:setDates('month');" title="Set Dates to Last Month">Last Month</a>&nbsp;|&nbsp;<a href="javascript:setDates('month6');" title="Set Dates to 6 Months Ago">Last 6 Months</a>&nbsp;|&nbsp;<a href="javascript:setDates('year');" title="Set Dates to Last Year">Last Year</a></span></td>
    </tr>
    <tr>
    	<td>Through Date:</td>
        <td>
            <input type="text" name="end_date" id="end_date" value="<?php echo date("m/d/Y", strtotime($end_date)); ?>" size="7" />&nbsp;<img src="../../images/calendar.jpg" width="20" height="20" alt="Calendar" onclick="showCalendar('end')" />
            <div id="calendar_holder_end" style="margin-left:100px; display:none;">
            	<div id="cal_endContainer" style="position:absolute; z-index:10"></div>
            </div> 
        </td>
    </tr>
    <tr>
    	<td>Customer:</td>
        <td>
            <select id="customer_id" name="customer_id" style="display:">
                <option value="">Select a Customer from the List</option>
                <?php echo implode("\r\n", $arrCustomerRows); ?>
            </select>
    	</td>
    </tr>
    <tr>
      <td>Activity</td>
      <td>
          <select name="activity" id="activity">
            <option value="" <? if ($activity=="") { ?>selected="selected"<? } ?>>Served</option>
            <option value="invoiced" <? if ($activity=="invoiced") { ?>selected="selected"<? } ?>>Invoiced</option>
            <option value="paid" <? if ($activity=="paid") { ?>selected="selected"<? } ?>>Paid</option>
          </select>
      </td>
    </tr>
    <tr>
      <td><input type="submit" value="Search" /></td>
      <td>&nbsp;</td>
    </tr>
</table>
</form>
<br />
<?php
echo $maincontent;
?>
<?php include ("yahoo.php"); ?>
<script language="javascript" type="text/javascript" src="../../calendar.js"></script>
<script language="javascript" type="text/javascript">
<?php foreach($arrCases as $the_customer_id=>$case_count) {
	echo "document.getElementById('customer_count_" . $the_customer_id . "').innerHTML = '<a href=\"javascript:showCases(" . $the_customer_id . ")\" id=\"show_cases_" . $the_customer_id . "\" title=\"Click to review case\">" . $case_count . " Cases</a><a href=\"javascript:hideCases(" . $the_customer_id . ")\" id=\"hide_cases_" . $the_customer_id . "\" style=\"display:none\">" . $case_count . " Cases</a>';";
	
	echo "document.getElementById('customer_invoiced_" . $the_customer_id . "').innerHTML = 'Invoiced:$" . number_format($arrTotals[$the_customer_id]["invoice_total"], 2) . "';";
	
	echo "document.getElementById('customer_paid_" . $the_customer_id . "').innerHTML = 'Paid:$" . number_format($arrTotals[$the_customer_id]["paid_total"], 2) . "';";
	
	echo "\r\n";
}
?>

var showCases = function(cus_id) {
	//get by class name
	var cases = Dom.getElementsByClassName("case_row_" + cus_id, "div");
	Dom.setStyle(cases, "display", "");
	Dom.setStyle("show_cases_" + cus_id, "display", "none");
	Dom.setStyle("hide_cases_" + cus_id, "display", "");
}
var hideCases = function(cus_id) {
	//get by class name
	var cases = Dom.getElementsByClassName("case_row_" + cus_id, "div");
	Dom.setStyle(cases, "display", "none");
	Dom.setStyle("show_cases_" + cus_id, "display", "");
	Dom.setStyle("hide_cases_" + cus_id, "display", "none");
}
YAHOO.example.calendar.init = function() {

	function handleSelect(type,args,obj) {
		var dates = args[0]; 
		var date = dates[0];
		var year = date[0], month = date[1], day = date[2];
		
		if (day < 10) {
			day = "0" + day;
		}
		if (month < 10) {
			month = "0" + month;
		}
		var txtDate1 = document.getElementById(current_date_field + "_date");
		txtDate1.value = month + "/" + day + "/" + year;
		hideCalendar();
	}

	function updateCal() {
		var txtDate1 = document.getElementById(current_date_field + "_date");
		var thecalendar = "";
		switch(current_date_field) {
			case "report":
				thecalendar = YAHOO.example.calendar.cal_report;
				break;
			case "end":
				thecalendar = YAHOO.example.calendar.cal_end;
				break;
		}
		if (txtDate1.value != "") {
			thecalendar.select(txtDate1.value);
			var selectedDates = thecalendar.getSelectedDates();
			if (selectedDates.length > 0) {
				var firstDate = selectedDates[0];
				thecalendar.cfg.setProperty("pagedate", (firstDate.getMonth()+1) + "/" + firstDate.getFullYear());
				thecalendar.render();
			} else {
				alert("Cannot select a date before 1/1/2009 or after 12/31/2013");
			}
		}
	}
	YAHOO.example.calendar.cal_report = new YAHOO.widget.Calendar("cal_report","cal_reportContainer", {navigator:true});
	YAHOO.example.calendar.cal_report.selectEvent.subscribe(handleSelect, YAHOO.example.calendar.cal_report, true);
	YAHOO.example.calendar.cal_report.render();
	
	YAHOO.example.calendar.cal_end = new YAHOO.widget.Calendar("cal_end","cal_endContainer", {navigator:true});
	YAHOO.example.calendar.cal_end.selectEvent.subscribe(handleSelect, YAHOO.example.calendar.cal_end, true);
	YAHOO.example.calendar.cal_end.render();
		YAHOO.util.Event.addListener("update", "click", updateCal);
	YAHOO.util.Event.addListener("dates", "submit", handleSubmit);

	// For this example page, stop the Form from being submitted, and update the cal instead
	function handleSubmit(e) {
		updateCal();
		YAHOO.util.Event.preventDefault(e);
	}
}
var setDates = function(date_range) {
	switch(date_range) {
		case "month":
			Dom.get("report_date").value = "<? 
			$lastmonth = mktime(0, 0, 0, date("m")-1, 1,   date("Y"));
			echo date("m/d/Y", $lastmonth); ?>";
			Dom.get("end_date").value = "<?
			$lastmonth = mktime(0, 0, 0, date("m")-1, 1,   date("Y"));
			echo date("m/t/Y", $lastmonth); ?>";
			break;
		case "month6":
			Dom.get("report_date").value = "<? 
			$lastmonth = mktime(0, 0, 0, date("m")-6, 1,   date("Y"));
			echo date("m/d/Y", $lastmonth); ?>";
			Dom.get("end_date").value = "<?
			$lastmonth = mktime(0, 0, 0, date("m"), 1,   date("Y"));
			echo date("m/t/Y", $lastmonth); ?>";
			break;
		case "year":
			Dom.get("report_date").value = "<? 
			$lastmonth = mktime(0, 0, 0, 1, 1,   date("Y")-1);
			echo date("m/d/Y", $lastmonth); ?>";
			Dom.get("end_date").value = "<?
			$lastmonth = mktime(0, 0, 0, 12, 31,   date("Y")-1);
			echo date("m/d/Y", $lastmonth); ?>";
			break;
	}
}
var init = function() {
	YAHOO.example.calendar.init();
}
Event.addListener(window, "load", init);
</script>
</body>
</html>
<?php
exit();
?>