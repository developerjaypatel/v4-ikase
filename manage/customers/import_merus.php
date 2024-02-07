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
<title>Merus Import</title>
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

<div style="display:inline-block; vertical-align:top">
<h3>
    <strong>Import<br />
<?php echo $cus_name; ?></strong>
</h3>
<p><a href="javascript:setFrame('../../api/import_merus_setup.php?customer_id=<?php echo $customer_id; ?>')">Setup DB</a></p>
<p><a href="javascript:setFrame('../../api/import_merus_xyz.php?data_source=<?php echo $data_source; ?>&customer_id=<?php echo $customer_id; ?>')">Main Data</a>&nbsp;<input type="checkbox" id="main_ok" checked="checked" /></p>
<p><a href="javascript:setFrame('../../api/import_merus_prep_inbox.php?data_source=<?php echo $data_source; ?>&customer_id=<?php echo $customer_id; ?>')">Prep Inbox</a></p>
<p><a href="javascript:setFrame('../../api/import_merus_inbox.php?data_source=<?php echo $data_source; ?>&customer_id=<?php echo $customer_id; ?>')">Inbox</a></p>

</div>
<div style="display:inline-block; vertical-align:top">
    <h3>Transfer</h3>
    <p><a href="javascript:setFrame('../../api/import_merus_transfer.php?customer_id=<?php echo $customer_id; ?>')">Main</a>
    <p><a href="javascript:setFrame('../../api/import_merus_transfer_events.php?customer_id=<?php echo $customer_id; ?>')">Events</a>
    <p><a href="javascript:setFrame('../../api/import_merus_transfer_tasks.php?customer_id=<?php echo $customer_id; ?>')">Tasks</a>
    <p><a href="javascript:setFrame('../../api/import_merus_transfer_documents.php?customer_id=<?php echo $customer_id; ?>')">Documents</a>
</div>    
<script language="javascript">
function runMain(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_merus_xyz.php?data_source=<?php echo $data_source; ?>&customer_id=<?php echo $customer_id; ?>');
	}
}
function runInbox(completed_count, case_count) {
	document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
	var main_ok = document.getElementById("main_ok");
	if (!main_ok.checked) {
		setFrame('../../api/import_merus_inbox.php?data_source=<?php echo $data_source; ?>&customer_id=<?php echo $customer_id; ?>');
	}
}
</script>
</body>
</html>
