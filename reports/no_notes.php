<?php 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

require_once('../shared/legacy_session.php');

session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

include("../api/connection.php");


$start_date = date("Y-m-d");
$end_date = date("Y-m-d");

if (isset($_GET["start"])) {
	$start_date = passed_var("start", "get");
	$end_date = passed_var("end", "get");
}

$list = array();
$customer_id = $_SESSION['user_customer_id'];

$sql = "SELECT IF(ccase.case_number='', ccase.file_number, ccase.case_number) case_number, ccase.case_name, track.*
FROM cse_case ccase
INNER JOIN (
	SELECT user_logon, case_id, COUNT(case_track_id) count_action, MAX(time_stamp) last_action 
	FROM cse_case_track
	WHERE operation = 'no_note'
	AND customer_id = :customer_id
	GROUP BY user_logon, case_id
) track
ON ccase.case_id = track.case_id
ORDER BY user_logon, ccase.case_name";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$employees = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$arrRows = array();
$current_user = "";
//print_r($cases);
foreach ($employees as $cindex=>$employee) {
	$display_date = date("m/d/Y", strtotime($employee->last_action));
	if ($current_date != date("m/d/Y", strtotime($employee->last_action))) {
		$current_date = date("m/d/Y", strtotime($employee->last_action));
	} else {
		$display_date = "";
	}
	$border = "";
	$display_user = $employee->user_logon;
	if ($current_user != $employee->user_logon) {
		$current_user = $employee->user_logon;
		$border = "border-top: 1px solid black";
		$display_date = date("m/d/Y", strtotime($employee->last_action));
	} else {
		$display_user = "";
	}
	$row = "
	<tr>
		<td align='left' valign='top' style='" . $border . "'>
			" . $display_user . "
		</td>
		<td align='left' valign='top' style='" . $border . "'>
			<a href='../v8.php?n#kase/" . $employee->case_id . "' target='_blank'>" . $employee->case_number . "</a> :: " . $employee->case_name . "
		</td>
		<td align='left' valign='top' style='" . $border . "'>
			" . $employee->count_action . "
		</td>
		<td align='right' valign='top' style='" . $border . "'>
			" . $display_date . "
		</td>
		<td align='left' valign='top' style='" . $border . "'>
			" . date("g:iA", strtotime($employee->last_action)) . "
		</td>
	</tr>";
	$arrRows[] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Left Kases  Report w/o Notes</title>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<table width="900" border="0" align="center" cellpadding="0" cellspacing="0" style="margin-top:0px">
  <tr>
    <td width="16%" valign="top"><img src="https://www.ikase.website/img/ikase_logo_login.png" height="32" width="77"></td>
    <td align="left">
        <div style="float:right">
            <em>as of <?php echo date("m/d/y g:iA"); ?></em>
        </div>
        <span  style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">
            VIEWED KASES w/o NOTES REPORT
        </span>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        <hr/>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>
        From 
        <input type="date" name="start_date" id="start_date" onChange="setDates()" value="<?php echo date("Y-m-d", strtotime($start_date)); ?>"> 
        Through 
        <input type="date" name="end_date" id="end_date" onChange="setDates()" value="<?php echo date("Y-m-d", strtotime($end_date)); ?>"> 
        &nbsp;
        <input type="button" id="update_dates" value="Update Report" style="visibility:hidden" onClick="updateDates()" />
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
<table width="70%" align="center">
<tr>
        <th align="left">Employee</th>
        <th align="left">Case</th>
        <th align="left">Actions</th>
        <th align="left">Last Action</th>
  </tr>
    <?php echo implode("", $arrRows); ?>
</table>
<script type="application/javascript">
function setDates() {
	var start = document.getElementById("start_date").value;
	var end = document.getElementById("end_date").value;
	
	var d = new Date(start);
	var d1 = new Date(end);
	
	if (d > d1) {
		document.getElementById("end_date").value = document.getElementById("start_date").value;
	}
	
	document.getElementById("update_dates").style.visibility = "visible";
}
function updateDates() {
	var start = document.getElementById("start_date").value;
	var end = document.getElementById("end_date").value;
	document.location.href = "no_notes.php?start=" + start + "&end=" + end;
}
</script>
</body>
</html>
