<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/manage_session.php");
set_time_limit(3000);

if (!isset($_SESSION["user_id"])) {
	header("location:https://v4.ikase.org/");
}
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("../../api/connection.php");

$db = getConnection();
include("../../api/customer_lookup.php");


$db = null;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Import from Tritek</title>
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
var updateCounter = function(total, key) {
	var feedback = document.getElementById("feedback");
	feedback.innerHTML = key + "/" + total + "=" + (key/total*100) + "%";
}
</script>
</head>

<body>
<div style="float:right">
	<div id="frame_title"></div>
    <div id="feedback"></div>
	<iframe id="processing_frame" width="600px" height="600px"></iframe>
</div>
<div style="vertical-align:top">
    <div style="display:inline-block; vertical-align:top">
    <h3>
        <strong>Import<br />
<?php echo $cus_name; ?></strong>
    </h3>
    <p><a href="javascript:setFrame('../../api/setup_tritek_index.php?customer_id=<?php echo $customer_id; ?>')">Setup Tables</a></p>
    <p><a href="javascript:setFrame('../../api/setup_tritek.php?customer_id=<?php echo $customer_id; ?>')">Setup DB</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek.php?customer_id=<?php echo $customer_id; ?>')">Main Data</a>&nbsp;<input type="checkbox" id="main_ok" checked="checked" /></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_venues.php?customer_id=<?php echo $customer_id; ?>')">Venues</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_activity.php?customer_id=<?php echo $customer_id; ?>')">Activity</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_notes.php?customer_id=<?php echo $customer_id; ?>')">Notes</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_notes2.php?customer_id=<?php echo $customer_id; ?>')">Notes 2</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_documents.php?customer_id=<?php echo $customer_id; ?>')">Documents</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_audit.php?customer_id=<?php echo $customer_id; ?>')">Audit</a></p>
<p><a href="javascript:setFrame('../../api/import_tritek_events.php?customer_id=<?php echo $customer_id; ?>')">Events</a></p>
<p><a href="javascript:setFrame('../../api/import_tritek_calendar_types.php?customer_id=<?php echo $customer_id; ?>')">Calendar Types</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_tasks.php?customer_id=<?php echo $customer_id; ?>')">Tasks</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_tasks_worker.php?customer_id=<?php echo $customer_id; ?>')">Task Workers</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_users.php?customer_id=<?php echo $customer_id; ?>')">Users</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_partie_types.php?customer_id=<?php echo $customer_id; ?>')">Partie Types</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_costs.php?customer_id=<?php echo $customer_id; ?>')">Costs</a></p>
    <hr />
    <p><a href="javascript:setFrame('../../api/import_tritek_costs_missing.php?customer_id=<?php echo $customer_id; ?>')">Missing Costs</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_activity_missing.php?customer_id=<?php echo $customer_id; ?>')">Missing Activity</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_tasks_missing.php?customer_id=<?php echo $customer_id; ?>')">Missing Tasks</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_events_missing.php?customer_id=<?php echo $customer_id; ?>')">Missing Events</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_badexams.php?customer_id=<?php echo $customer_id; ?>')">Exams</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_missing.php?customer_id=<?php echo $customer_id; ?>')">Missing</a></p>
        <p><a href="javascript:setFrame('../../api/import_tritek_notes_missing.php?customer_id=<?php echo $customer_id; ?>')">Missing Notes</a></p>
        <p><a href="javascript:setFrame('../../api/import_tritek_notes2_missing.php?customer_id=<?php echo $customer_id; ?>')">Missing Notes2</a></p>
    </div>
  <div style="display:inline-block; vertical-align:top">
    <h3>Transfer</h3>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer.php?customer_id=<?php echo $customer_id; ?>')">Main</a>
    </p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_activity.php?customer_id=<?php echo $customer_id; ?>')">Activity</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_partie_types.php?customer_id=<?php echo $customer_id; ?>')">Partie Types</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_notes.php?customer_id=<?php echo $customer_id; ?>')">Notes</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_notes2.php?customer_id=<?php echo $customer_id; ?>')">Notes 2</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_events.php?customer_id=<?php echo $customer_id; ?>')">Events</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_tasks.php?customer_id=<?php echo $customer_id; ?>')">Tasks</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_users.php?customer_id=<?php echo $customer_id; ?>')">Users</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_forms.php?customer_id=<?php echo $customer_id; ?>')">Forms</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_costs.php?customer_id=<?php echo $customer_id; ?>')">Costs</a></p>
    <p><a href="javascript:setFrame('../../api/update_tritek_docs.php?customer_id=<?php echo $customer_id; ?>')">Archives</a></p>
    <p><a href="javascript:setFrame('../../api/filters.php?customer_id=<?php echo $customer_id; ?>')">Filters</a></p>
  </div>
</div>
<script language="javascript">
function runMain(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runActivity(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_activity.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runNotes(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_notes.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runMissingNotes(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_notes_missing.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runEventsMissing(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_events_missing.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runTasksMissing(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_tasks_missing.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runDocuments(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_documents.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runEvents(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_events.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runTasks(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_tasks.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runTaskWorkers(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_tasks_worker.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runCosts(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_costs.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runVenues(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_venues.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runExams(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_badexams.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runMissings(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_missing.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runActivityMissing(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_activity_missing.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runCostsMissing(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_costs_missing.php?customer_id=<?php echo $customer_id; ?>');
	}
}
</script>
</body>
</html>