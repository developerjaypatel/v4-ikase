<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../../shared/legacy_session.php');
set_time_limit(3000);
if (!isset($_SESSION["user_id"])) {
	header("location:https://v2.ikase.org/");
}
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("../../api/connection.php");

$db = getConnection();
include("../../api/customer_lookup.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Import from A1</title>
<script language="javascript">
var setFrame = function(src) {
	var title = src.replace('../../api/', '');
	var processing_frame = document.getElementById("processing_frame");
	processing_frame.src = src;
	var frame_title = document.getElementById("frame_title");
	frame_title.innerHTML = title;
}
var setFeedback = function(msg) {
	var feedback = document.getElementById("feedback");
	feedback.innerHTML = msg;
}
</script>
</head>

<body>
<div style="float:right">
	<div id="frame_title"></div>
	<iframe id="processing_frame" width="1200px" height="700px"></iframe>
</div>
<div id="feedback"></div>
<div style="vertical-align:top">
    <div style="display:inline-block; vertical-align:top">
    <h3>
        <strong>Import A1</strong>
    </h3>
    <p><a href="javascript:setFrame('../../api/reset_a1.php?customer_id=<?php echo $customer_id; ?>')">Reset Data</a></p>
	<hr />
    <p><a href="javascript:setFrame('../../api/prep_a1.php?customer_id=<?php echo $customer_id; ?>')">Prepare database</a></p>
    <hr />
	<p><a href="javascript:setFrame('../../api/import_a1_users.php?customer_id=<?php echo $customer_id; ?>')">Users</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1.php?customer_id=<?php echo $customer_id; ?>')">Main Data</a>&nbsp;<input type="checkbox" id="main_ok" checked="checked" /></p>
    <p><a href="javascript:setFrame('../../api/import_a1_activity.php?customer_id=<?php echo $customer_id; ?>&reset=y')">Reset Activity</a><br><a href="javascript:setFrame('../../api/import_a1_activity.php?customer_id=<?php echo $customer_id; ?>')">Activity</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_documents.php?customer_id=<?php echo $customer_id; ?>')">Documents</a></p>
	<p><a href="javascript:setFrame('../../api/import_a1_calendar_types.php?customer_id=<?php echo $customer_id; ?>')">Calendar Types</a></p>
	<p><a href="javascript:setFrame('../../api/import_a1_events.php?customer_id=<?php echo $customer_id; ?>&amp;reset=y')">Reset Events</a><br /><a href="javascript:setFrame('../../api/import_a1_events.php?customer_id=<?php echo $customer_id; ?>')">Events</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_tasks.php?customer_id=<?php echo $customer_id; ?>&amp;reset=y')">Reset Tasks</a><br />
      <a href="javascript:setFrame('../../api/import_a1_tasks.php?customer_id=<?php echo $customer_id; ?>')">Tasks</a></p>
	<p><a href="javascript:setFrame('../../api/import_a1_notes.php?customer_id=<?php echo $customer_id; ?>')">Notes</a></p>
<p>
	<a href="javascript:setFrame('../../api/import_a1_rolodex_reset.php?customer_id=<?php echo $customer_id; ?>&amp;reset=y')">Reset Rolodex</a>
	<br />
	<a href="javascript:setFrame('../../api/import_a1_rolodex_companies.php?customer_id=<?php echo $customer_id; ?>')">Rolodex</a>
</p>
<p><a href="javascript:setFrame('../../api/import_a1_partie_types.php?customer_id=<?php echo $customer_id; ?>')">Partie Types</a></p>
<p><a href="javascript:setFrame('../../api/import_a1_employers.php?customer_id=<?php echo $customer_id; ?>')">Employers</a></p>
<!--<p><a href="javascript:setFrame('../../api/import_a1_applicant_phones.php?customer_id=<?php echo $customer_id; ?>')">Fix Phones</a></p>-->
<p><a href="javascript:setFrame('../../api/import_a1_corporation_insert.php?customer_id=<?php echo $customer_id; ?>')">Fix Corps</a></p>
<p><a href="javascript:setFrame('../../api/import_a1_missing_injuries.php?customer_id=<?php echo $customer_id; ?>')">Missing Injuries</a></p>
    </div>
  <div style="display:inline-block; vertical-align:top">
    <h3>Transfer</h3>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer.php?customer_id=<?php echo $customer_id; ?>')">Main</a>
    </p>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer_activity.php?customer_id=<?php echo $customer_id; ?>')">Activity</a><br><a href="javascript:setFrame('../../api/import_a1_transfer_quicknotes.php?customer_id=<?php echo $customer_id; ?>')">Quick Notes</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer_rolodex_companies.php?customer_id=<?php echo $customer_id; ?>')">Rolodex</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_rolodex.php?customer_id=<?php echo $customer_id; ?>')">Rolodex Updates</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer_partie_types.php?customer_id=<?php echo $customer_id; ?>')">Partie Types</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer_notes.php?customer_id=<?php echo $customer_id; ?>')">Notes</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer_events.php?customer_id=<?php echo $customer_id; ?>')">Events</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer_tasks.php?customer_id=<?php echo $customer_id; ?>')">Tasks</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer_users.php?customer_id=<?php echo $customer_id; ?>')">Users</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer_forms.php?customer_id=<?php echo $customer_id; ?>')">Forms</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer_body_parts.php?customer_id=<?php echo $customer_id; ?>')">Body Parts</a></p>
    <p><a href="javascript:setFrame('../../api/filters.php?customer_id=<?php echo $customer_id; ?>')">Filters</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_transfer_missing_injuries.php?customer_id=<?php echo $customer_id; ?>')">Missing Injuries</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_create_injuries.php?customer_id=<?php echo $customer_id; ?>')">New Injuries</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_create_applicant.php?customer_id=<?php echo $customer_id; ?>')">Applicants</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_applicant_insert.php?customer_id=<?php echo $customer_id; ?>')">Fix Clients</a></p>
    <p><a href="javascript:setFrame('../../api/import_a1_applicant_address.php?customer_id=<?php echo $customer_id; ?>')">Fix Phones</a></p>
  </div>
</div>

<p>Instructions<br />
  Go to import screen after setting import permissions and  setting the legacy to A1</p>
<ol>
  <li>Import the data via Full Convert Pro, put it in new db `clientname`</li>
  <li>prep</li>
  <li>On kustomweb.xyz, we must process the folders in  the CLIENTS folder where the injury and activity information is stored</li>
  <li>Move the data to F://customer_name/clients from  CLIENTS directory</li>
  <li>Update inetpub/wwwroot/a1_import/settings with  directory where injury content is and the import database name</li>
  <li>Run folders.php script <br />
    localhost/a1_import/folders.php?db=customer_name</li>
  <li>Run extract.php?db=customer_name</li>
  <li>Run extract_activity.php?db=customer_name (can  run in parallel), much slower</li>
  <li>Enter the archives folder in ikase.cse_customer  for documents</li>
  <li>Read <a href="../../api/A1 Protocol.docx">Protocol</a>, run queries to prep database</li>
  <li>Run through Main Data and below, then Transfer section</li>
</ol>
<script language="javascript">
function runMain(completed_count, case_count) {
	setTimeout(function() {
		document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
		var main_ok = document.getElementById("main_ok");
		if (!main_ok.checked) {
			setFrame('../../api/import_a1.php?customer_id=<?php echo $customer_id; ?>');
		}
	}, 100);
}
function runActivity(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_a1_activity.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runEvents(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_a1_events.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runTasks(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_a1_tasks.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runCorp(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_a1_corporation_insert.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runEmployers(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_a1_employers.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runNotes(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_a1_notes.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runApp(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_a1_applicant_insert.php?customer_id=<?php echo $customer_id; ?>');
	}
}
</script>
</body>
</html>
