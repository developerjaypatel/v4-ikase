<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/manage_session.php");
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


$db = null;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Import eCandidus</title>
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
            <strong>Format<br />
    <?php echo $cus_name; ?></strong></h3>
        <p><a href="javascript:setFrame('../../api/import_ecand.php?customer_id=<?php echo $customer_id; ?>')">Main Data</a>&nbsp;
          <input type="checkbox" id="main_ok" checked="checked" />
        </p>
        <p><a href="javascript:setFrame('../../api/import_ecand_injury.php?customer_id=<?php echo $customer_id; ?>')">Injuries</a>&nbsp;
        </p>
        <p><a href="javascript:setFrame('../../api/import_ecand_entities.php?customer_id=<?php echo $customer_id; ?>')">Entities</a>&nbsp;
        </p>
        <p><a href="javascript:setFrame('../../api/import_ecand_address.php?customer_id=<?php echo $customer_id; ?>')">Addresses</a>&nbsp;
        </p>
         <p><a href="javascript:setFrame('../../api/import_ecand_task.php?customer_id=<?php echo $customer_id; ?>')">Tasks</a>&nbsp;
        </p>
        <p><a href="javascript:setFrame('../../api/import_ecand_user.php?customer_id=<?php echo $customer_id; ?>')">Users</a>&nbsp;
        </p>
         <p><a href="javascript:setFrame('../../api/import_ecand_attach.php?customer_id=<?php echo $customer_id; ?>')">Docs</a>&nbsp;
        </p>
        <p><a href="javascript:setFrame('../../api/import_ecand_docs.php?customer_id=<?php echo $customer_id; ?>')">Archives</a>&nbsp;
        </p>
    </div>
    
    <div style="display:inline-block; vertical-align:top">
        <h3>
            <strong>Import<br />&nbsp;</strong>
        </h3>
        <p><a href="javascript:setFrame('../../api/import_ecand_cases.php?customer_id=<?php echo $customer_id; ?>')">Cases</a>&nbsp;
        </p>
        <p><a href="javascript:setFrame('../../api/import_basic_transfer_calendars.php?customer_id=<?php echo $customer_id; ?>')">Calendars</a></p>
        <p><a href="javascript:setFrame('../../api/import_basic_calendar_types.php?customer_id=<?php echo $customer_id; ?>')">Calendar Types</a></p>
        <p><a href="javascript:setFrame('../../api/import_basic_partie_types.php?customer_id=<?php echo $customer_id; ?>')">Partie Types</a>    </p>
        <p><a href="javascript:setFrame('../../api/import_tritek_transfer_forms.php?customer_id=<?php echo $customer_id; ?>')">Forms</a></p>
        <p><a href="javascript:setFrame('../../api/filters.php?customer_id=<?php echo $customer_id; ?>')">Filters</a></p>
    </div>
    
    <div style="display:inline-block; vertical-align:top; display:">
        <h3>Transfer</h3>
        <p>
            <a href="javascript:setFrame('../../api/import_ecand_transfer.php?customer_id=<?php echo $customer_id; ?>')">Main</a>
        </p>
        <p><a href="javascript:setFrame('../../api/import_tritek_transfer_users.php?customer_id=<?php echo $customer_id; ?>')">Users</a></p>
         <p><a href="javascript:setFrame('../../api/import_tritek_transfer_tasks.php?customer_id=<?php echo $customer_id; ?>')">Tasks</a></p>
    </div>
</div>
<script language="javascript">
function runMain(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_ecand.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runEntities(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_ecand_entities.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runAddresses(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_ecand_address.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runTasks(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_ecand_task.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runInjuries(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_ecand_injury.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runDocuments(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_ecand_attach.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runUsers(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_ecand_user.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runCases(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_ecand_cases.php?customer_id=<?php echo $customer_id; ?>');
	}
}
function runArchives(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_ecand_docs.php?customer_id=<?php echo $customer_id; ?>');
	}
}
</script>
</body>
</html>