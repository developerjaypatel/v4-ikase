 <?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("manage_session.php");

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}
date_default_timezone_set('America/Los_Angeles');

include("connection.php");

if (!isset($_POST["from_date"])) {
	$from_date = "";
} else {
	$from_date = passed_var("from_date", "post");
	$to_date = passed_var("to_date", "post");
}
if ($from_date=="") {
	//are we at the beginning of the month or the end
	$day_of_month =date("j");
	if ($day_of_month < 16) {
		$from_date = date("m") . "/01/" . date("Y");
		$to_date = date("m") . "/15/" . date("Y");
	} else {
		$from_date = date("m") . "/16/" . date("Y");
		$to_date = date("m") . "/" . date("t") . "/" . date("Y");
	}
}
//do not go beyond yesterday
$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-0, date("Y")));
if (strtotime($to_date) > strtotime($yesterday) ) {
	//restrict the to date
	$to_date = date("m/d/Y", strtotime($yesterday) );
}
$pay_date = date("Y-m-d");

//look up user
$sql = "SELECT user.*
FROM user 
WHERE customer_id = :customer_id
AND user_type = 2
ORDER BY user_status, user_type, user_logon";

//$resultshift = mysql_query($query, $link) or die(mysql_error());
//$numbershift = mysql_numrows( $resultshift );

try {
	$customer_id = $_SESSION["user_customer_id"];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$employees = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
//die(print_r($employees));
$GroupID = 1;
$currentGroupID = $GroupID;
$filtergroup = $GroupID;
$table_rows = array();
$fired_title = "";
//for($x = 0; $x < $numbershift; $x++) {
foreach($employees as $x=>$employee) {
	if (($x%2)==0) { $bgcolor="#FFFFFF"; } else { $bgcolor="#ededed"; }
	
	$UserName = $employee->user_logon;
	$shift = $employee->shift;
	$user_id = $employee->user_id;
	$user_status = $employee->user_status;
	$user_type = $employee->user_type;
	
	$actual_user_name = $employee->user_name;
	
	$pay_rate = $employee->pay_rate;
	$pay_period = $employee->pay_period;
	 switch($pay_period) {
		case "H":
			$pay_period = " per hour";
			break;
		case "D":
			$pay_period = " per day";
			break;
		case "M":
			$pay_period = " per month";
			break;
	}
	$pay_schedule = $employee->pay_schedule;
	switch($pay_schedule) {
		case "D":
			$pay_schedule = " daily";
			break;
		case "W":
			$pay_schedule = " weekly";
			break;
		case "BW":
			$pay_schedule = " bi-weekly";
			break;
		case "M":
			$pay_schedule = " monthly";
			break;
		case "TM":
			$pay_schedule = " twice monthly";
			break;
	}
	$pay_method = $employee->pay_method;
	switch($pay_method) {
		case "DD":
			$pay_method = " via direct deposit";
			break;
		case "CK":
			$pay_method = " by check";
			break;
		case "CS":
			$pay_method = " cash";
			break;
	}
	
	$table_rows[] = "
	<tr>
		<td style='background:".  $bgcolor . "' align='left' valign='top' nowrap>
			<a href='#employees/" . $user_id . "'>" . $actual_user_name . "</a>
		</td>
		<td style='background:".  $bgcolor . "' align='right' valign='top'>$" . $pay_rate . "</td>
		<td style='background:".  $bgcolor . "' align='left' valign='top' nowrap>" . $pay_period . "</td>
		<td style='background:".  $bgcolor . "' align='left' valign='top' nowrap>" . $pay_schedule . "</td>
		<td style='background:".  $bgcolor . "' align='left' valign='top' nowrap>" . $pay_method . "</td>
		<td style='background:".  $bgcolor . "' align='right' valign='top' nowrap>
			$<input type='number' id='payment_" . $user_id . "' name='payment_" . $user_id . "' style='width:75px' />
		</td>
		<td style='background:".  $bgcolor . "' align='right' valign='top' nowrap>
			$<input type='number' id='reimbursment_" . $user_id . "' name='reimbursment_" . $user_id . "' style='width:75px' />
		</td>
		<td style='background:".  $bgcolor . "' align='right' valign='top'>
			<textarea id='memo_" . $user_id . "' name='memo_" . $user_id . "' style='width:275px; height:55px'></textarea>
		</td>
	</tr>";
}
//die(print_r($table_rows));

if ($filtergroup=="") {
	$display = "none";
} else {
	$display = "";
}

$user_rows = implode("\r\n", $table_rows);
?>
<form id='contractors_checks_form'>
	<table border='0' cellspacing='0' width='40%' align='center'>
		<thead>
        <tr>
		  <td colspan='8' align='left' valign='top' bgcolor='lightsalmon' id='header_contractors_checks'>
			<div style='float:right'>
				<button class='btn btn-primary btn-xs save_checks'>Save</button>
			</div>
			<span class='admintitle'>
				CREATE CONTRACTORS CHECKS
			</span>
		   </td>
		</tr>
        <tr>
		  <td colspan='8'>&nbsp;</td>
        </tr>
        <tr>
          <td align="left" valign="top" class="td_label">
            <span style='font-weight:bold'>Pay Period</span>
          </td>
          <td align="left" valign="top" colspan="7">
          	<input type="text" id="pay_period_start_dateField" name="pay_period_start_dateField" value="<?php echo $from_date; ?>" class="check edit_field check_range_date" />
            <input type="text" id="pay_period_end_dateField" name="pay_period_end_dateField" value="<?php echo $to_date; ?>" class="check edit_field check_range_date" />
            <span id="pay_period_start_dateSpan" class="hide_me edit_span"><?php echo $from_date; ?></span>
            <span class="hide_me edit_span">&nbsp;-&nbsp;</span>
            <span id="pay_period_end_dateSpan" class="hide_me edit_span"><?php echo $to_date; ?></span>
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" class="td_label">
            <span style='font-weight:bold'>Pay Date</span>
          </td>
          <td align="left" valign="top" colspan="7">
            <input type="text" id="pay_dateField" name="pay_dateField" value="<?php echo date("m/d/Y", strtotime($pay_date)); ?>" class="check edit_field" />
            <span id="pay_dateSpan" class="hide_me edit_span"><?php echo date("m/d/Y"); ?></span>
          </td>
        </tr>
        <tr>
		  <td colspan='8'>&nbsp;</td>
        </tr>
		<tr>
			<th align='left'>&nbsp;</th>
			<th align='right' valign='top'>Pay&nbsp;Rate</th>
			<th align='right' valign='top'>Per</th>
			<th align='right' valign='top'>When</th>
			<th align='right' valign='top'>How</th>
			<th align='right' valign='top'>Payment</th>
			<th align='right' valign='top'>Reimbursment</th>
			<th align='right' valign='top'>Memo</th>
		</tr>
		</thead>
		<tbody>" . 
		<?php echo $user_rows; ?>
		</tbody>
	</table>
</form>

<script language="javascript">
setTimeout(function() {
	window.contractors_paychecks_create.prototype.doTimeouts();
}, 100);
</script>