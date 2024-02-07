<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../../shared/legacy_session.php');
set_time_limit(3000);

if (!isset($_SESSION["user_id"])) {
	header("location:https://www.ikase.org/");
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
<title>Import from Tritek - PI</title>
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
	<iframe id="processing_frame" width="600px" height="800px"></iframe>
</div>
<div style="vertical-align:top">
    
  <div style="display:inline-block; vertical-align:top">
    <h3>Transfer</h3>
    <p>
      <input type="checkbox" name="main_ok" id="main_ok" checked="checked" />
      <label for="main_ok">Auto Run</label>
    </p>
    <p>
    	<a href="javascript:setFrame('../../api/import_tritek_transfer_pi.php?customer_id=<?php echo $customer_id; ?>')">Main PI</a>
    </p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_venues_pi.php?customer_id=<?php echo $customer_id; ?>')">Venues PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_defendants_pi.php?customer_id=<?php echo $customer_id; ?>')">Defendants PI</a></p>    
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_activity_pi.php?customer_id=<?php echo $customer_id; ?>')">Activity PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_intake_pi.php?customer_id=<?php echo $customer_id; ?>')">Intake PI</a></p>    
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_notes_pi.php?customer_id=<?php echo $customer_id; ?>')">Notes PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_notes2_pi.php?customer_id=<?php echo $customer_id; ?>')">Notes 2 PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_events_pi.php?customer_id=<?php echo $customer_id; ?>')">Events PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_tasks_fixcase.php?customer_id=<?php echo $customer_id; ?>')">Task Case PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_tasks_fix.php?customer_id=<?php echo $customer_id; ?>')">Task Type PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_tasks_users.php?customer_id=<?php echo $customer_id; ?>')">Task Users PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_tasks_pi.php?customer_id=<?php echo $customer_id; ?>')">Tasks PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_costs_pi.php?customer_id=<?php echo $customer_id; ?>')">Costs PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_negotiations_pi.php?customer_id=<?php echo $customer_id; ?>')">Negotiations PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_lostwages_pi.php?customer_id=<?php echo $customer_id; ?>')">Wages PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_settlement_pi.php?customer_id=<?php echo $customer_id; ?>')">Settlements PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_fees_pi.php?customer_id=<?php echo $customer_id; ?>')">Fees and awards PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_exams_pi.php?customer_id=<?php echo $customer_id; ?>')">Exams PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_deductions_pi.php?customer_id=<?php echo $customer_id; ?>')">Deductions PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_subros_pi.php?customer_id=<?php echo $customer_id; ?>')">Subrogations PI</a></p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_medicalbilling_pi.php?customer_id=<?php echo $customer_id; ?>')">Medical Billing PI</a></p>    
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_accidents_pi.php?customer_id=<?php echo $customer_id; ?>')">Accidents PI</a></p>        

  </div>
</div>
<p>&nbsp; </p>
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
function runSettlement(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_settlement.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runIntake(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_intake.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runQuicks(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_quicks.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runDefendants(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_defendants.php?customer_id=<?php echo $customer_id; ?>');
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
function runLostWages(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_lostwages.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runTaskUsers(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_tasks_users.php?customer_id=<?php echo $customer_id; ?>');
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
function runSubros(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_subro.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runDeductions(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_deductions.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runVenues(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_venues.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runTaskType(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_tasks_fix.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runTaskCase(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_tasks_fixcase.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runExams(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_badexams.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runAccidents(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_accident.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runMeds(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_medsumm.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runFees(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_tritek_fees.php?customer_id=<?php echo $customer_id; ?>');
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
