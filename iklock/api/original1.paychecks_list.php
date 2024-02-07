<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("manage_session.php");
date_default_timezone_set('America/Los_Angeles');

include("connection.php");
include("../classes/cls_user.php");

$user_id = passed_var("user_id", "post");

if ($user_id!="") {
	$my_user = new systemuser();
	$my_user->id = $user_id;
	$my_user->fetch();
}
$sql = "SELECT pchk.*, usr.user_name, usr.user_id, usr.pay_rate, usr.pay_method
FROM `paycheck` pchk
INNER JOIN `user` usr
ON pchk.user_uuid = usr.user_uuid";
if ($user_id!="") {
	$sql .="
	AND usr.user_id = :user_id";
}
$sql .="
WHERE pchk.customer_id = :customer_id
AND pchk.deleted = 'N'
ORDER BY usr.user_name";

try {
	$customer_id = $_SESSION["user_customer_id"];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	if ($user_id!="") {
		$stmt->bindParam("user_id", $user_id);
	}
	$stmt->execute();
	$paychecks = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($paychecks));
	
	$stmt->closeCursor(); $stmt = null; $db = null;

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
$table_rows = array();
$arrAdditional = array();
$arrHead = array();
foreach($paychecks as $x=>$check) {
	if (($x%2)==0) { $bgcolor="#FFFFFF"; } else { $bgcolor="#ededed"; }
	$user_name = $check->user_name;
	$user_id = $check->user_id;
	$pay_method = $check->pay_method;
	$pay_rate = $check->pay_rate;
	$paycheck_id = $check->paycheck_id;
	$pay_date = $check->pay_date;
	
	$regular_minutes = $check->regular_minutes;
	$overtime_minutes = $check->overtime_minutes;
	$holiday_minutes = $check->holiday_minutes;
	$vacation_minutes = $check->vacation_minutes;
	$bereavment_minutes = $check->bereavment_minutes;
	
	$reimbursments = $check->reimbursments;
	$arrReimb = new stdClass();
	if (strpos($reimbursments, "{")!==false) {
		$arrReimb = json_decode($reimbursments);
	}
	$memo = $check->memo;
	
	$current_total = ($regular_minutes * $pay_rate / 60);
	$current_total += ($overtime_minutes * $pay_rate  * 1.5 / 60);
	$current_total += ($holiday_minutes * $pay_rate / 60);
	$current_total += ($vacation_minutes * $pay_rate / 60);
	$current_total += ($bereavment_minutes * $pay_rate / 60);
	
	foreach($arrReimb as $rindex=>$reimb) {
		$current_total += $reimb;
		$add = "<td align='right' valign='top' style='background:".  $bgcolor . "'>$" . number_format($reimb, 2) . "</td>";
		$arrAdditional[] = $add;
		$arrHead[] = "<th align='left' valign='top' style='background:".  $bgcolor . "'>" . ucwords($rindex) . "</a>";
	}
	
	$table_rows[] = "
	<tr style='background:".  $bgcolor . "' id='check_row_" . $paycheck_id . "'>
		<td align='left' valign='top' nowrap>
			<a href='#paycheck/edit/" . $user_id . "/" . $paycheck_id . "'>" . date("m/d/y", strtotime($pay_date)) . "</a>
		</td>
		<td align='left' valign='top' nowrap>
			<a href='#employees/" . $user_id . "'>" . $user_name . "</a>
		</td>
		<td align='left' valign='top' align='left'>" . $pay_method . "</td>
		<td valign='top' align='right'>$" . number_format(($regular_minutes * $pay_rate / 60), 2) . "</td>
		<td valign='top' align='right'>$" . number_format(($overtime_minutes * $pay_rate * 1.5 / 60), 2) . "</td>
		<td valign='top' align='right'>$" . number_format(($holiday_minutes * $pay_rate / 60), 2) . "</td>
		<td valign='top' align='right'>$" . number_format(($vacation_minutes * $pay_rate / 60), 2) . "</td>
		<td valign='top' align='right'>$" . number_format(($bereavment_minutes * $pay_rate / 60), 2) . "</td>
		" . implode("", $arrAdditional) . "
		<td valign='top' align='right' style='font-weight:bold'>$" . number_format($current_total, 2) . "</td>
		<td valign='top' align='right'><span class='glyphicon glyphicon-trash delete_paycheck' aria-hidden='true' style='color:red; cursor:pointer' id='delete_" . $paycheck_id . "'></span></td>
	</tr>";
}

$rows = implode("\r\n", $table_rows);

$response = "
<table border='0' cellspacing='0' width='30%' align='center'>
	<thead>
	<tr>
      <td colspan='" . (count($arrHead) + 10) . "' align='left' valign='top'>
	  	<button class='btn btn-primary new_paycheck' id='new_paycheck_" . $user_id . "'>New Check</button>
		</td>
	</tr>
	<tr>
      <td colspan='" . (count($arrHead) + 10) . "' align='left' valign='top' bgcolor='#000033' id='header_assign'>
	  	<span class='admintitle'>
			LIST CHECKS
		</span>";
if (isset($my_user)) {
	$response .= " - <a href='#employees/" . $user_id . "' style='color:white'>" . $my_user->user_name . "</a>
	<input type='hidden' id='user_id' name='user_id' value='" . $user_id . "'";
}
$response .= "
		</td>
	</tr>
	<tr>
		<th align='left'>Edit</th>
		<th align='left'>Employee</th>
		<th align='left'>Pay&nbsp;Method</th>
		<th align='left'>Regular</th>
		<th align='left'>Overtime</th>
		<th align='left'>Holiday</th>
		<th align='left'>Vacation</th>
		<th align='left'>Bereavment</th>
		" . implode("", $arrHead) . "
		<th align='left'>Total</th>
		<th align='left'>&nbsp;</th>
	</tr>
	</thead>
	<tbody>" . 
	$rows . "
	</tbody>
</table>";
echo $response;
?>
<script language="javascript">
	setTimeout(function() {
	window.paychecks_list.prototype.doTimeouts();
}, 100);
</script>