<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/manage_session.php");
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


$db = null;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Import from Perfect</title>
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
        <strong>Import Perfect</strong>
    </h3>
    <p><a href="javascript:setFrame('../../api/import_perfect_prep.php?customer_id=<?php echo $customer_id; ?>')">Reset Data</a></p>
	<hr />
    <hr />
    <p><a href="javascript:setFrame('../../api/import_perfect.php?customer_id=<?php echo $customer_id; ?>')">Main Data</a>&nbsp;<input type="checkbox" id="main_ok" checked="checked" /></p>
    <p><a href="javascript:setFrame('../../api/import_basic_transfer_calendars.php?customer_id=<?php echo $customer_id; ?>')">Calendars</a></p>
    <p><a href="javascript:setFrame('../../api/import_basic_calendar_types.php?customer_id=<?php echo $customer_id; ?>')">Calendar Types</a></p>
    <p><a href="javascript:setFrame('../../api/import_basic_partie_types.php?customer_id=<?php echo $customer_id; ?>')">Partie Types</a>    </p>
    <p><a href="javascript:setFrame('../../api/import_tritek_transfer_forms.php?customer_id=<?php echo $customer_id; ?>')">Forms</a></p>
    <p><a href="javascript:setFrame('../../api/filters.php?customer_id=<?php echo $customer_id; ?>')">Filters</a></p>
    </div>
  <div style="display:inline-block; vertical-align:top">
    <h3>Transfer</h3>
    <p><a href="javascript:setFrame('../../api/import_perfect_transfer.php?customer_id=<?php echo $customer_id; ?>')">Main</a>
     <p><a href="javascript:setFrame('../../api/import_a1_transfer_body_parts.php?customer_id=<?php echo $customer_id; ?>')">Body Parts</a>
    </p>
  </div>
</div>
<p>&nbsp;</p>
<script language="javascript">
function runMain(completed_count, case_count) {
	setTimeout(function() {
		document.getElementById("feedback").innerHTML = completed_count + "/" + case_count + " = " + ((completed_count/case_count)*100).toFixed(3) + "%";
		var main_ok = document.getElementById("main_ok");
		if (!main_ok.checked) {
			setFrame('../../api/import_perfect.php?customer_id=<?php echo $customer_id; ?>');
		}
	}, 700);
}
</script>
</body>
</html>