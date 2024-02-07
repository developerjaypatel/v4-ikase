<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("manage_session.php");

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}
date_default_timezone_set('America/Los_Angeles');

include("connection.php");

//look up user
$sql = "SELECT user.*
FROM user 
WHERE customer_id = :customer_id
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
	
	switch($user_type) {
		case "1":
			$user_type = "Employee";
			break;
		case "2":
			$user_type = "Contractor";
			break;
		case "3":
			$user_type = "Admin";
			break;
	}
	
	$fired = "";
	if ($user_status=="FIRED") {
		$fired = "<span style='color:red'>FIRED<span>";
		if ($fired_title=="") {
			$fired_title = "<tr>
			<td style='background:pink;font-weight:bold' colspan='3'>
				FIRED EMPLOYEES
				 </td>
			</tr>";
			$table_rows[] = $fired_title;
		}
	}
	$actual_user_name = $employee->user_name;
	$clock_in_time = $employee->clock_in_time;
	$work_location = $employee->work_location;
	$clock_in_time = date("Y-m-d") . " " . $clock_in_time;
	$clock_in_time = date("g:iA", strtotime($clock_in_time));
	
	$clock_out_time = $employee->clock_out_time;
	$clock_out_time = date("Y-m-d") . " " . $clock_out_time;
	$clock_out_time = date("g:iA", strtotime($clock_out_time));
	
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
		<td style='background:".  $bgcolor . "' nowrap>
			<div style='float:right'>" . $fired . "
			</div>
			<a href='#employees/" . $user_id . "'>" . $actual_user_name . "</a>
		</td>
		<td style='background:".  $bgcolor . "' align='left'>" . $user_type . "</td>
		<td style='background:".  $bgcolor . "' align='left'>" . $user_status . "</td>
		<!--
		<td style='background:".  $bgcolor . "' align='right'>" . $clock_in_time . "</td>
		<td style='background:".  $bgcolor . "' align='right'>" . $clock_out_time . "</td>
		-->
		<td style='background:".  $bgcolor . "' align='right'>$" . $pay_rate . "</td>
		<td style='background:".  $bgcolor . "' align='left' nowrap>" . $pay_period . "</td>
		<td style='background:".  $bgcolor . "' align='left' nowrap>" . $pay_schedule . "</td>
		<td style='background:".  $bgcolor . "' align='left' nowrap>" . $pay_method . "</td>
		<td style='background:".  $bgcolor . "' align='right'>
			<button id='create_check_" . $user_id . "' class='btn btn-xs btn-primary create_check'>Create Check</button>
		</td>
		<td style='background:".  $bgcolor . "' align='right'>
			<button id='list_checks_" . $user_id . "' class='btn btn-xs btn-info list_checks'>List Checks</button>
		</td>
		<td style='background:".  $bgcolor . "' align='right'>
			<button id='manage_reimbursments_" . $user_id . "' class='btn btn-xs manage_reimbursments'>Reimbursments</button>
		</td>
		
	</tr>";
}
//die(print_r($table_rows));

if ($filtergroup=="") {
	$display = "none";
} else {
	$display = "";
}
//last group
/*
if ($currentGroupID!="") {
	if (isset($table_rows[$currentGroupID])) {
		//create a row for the layer
		$user_rows .= "<tr><td colspan='6'>
		<div id='lyr" . $currentGroupID . "' style='display:" . $display . "'>
		<table width='100%' border='0' cellpadding='0' cellspacing='0'>" . implode("", $table_rows[$currentGroupID]) . "</table></div>
		</td></tr>";
	}
}
*/
/*
if ($filtergroup=="" || $filtergroup=="FIRE") {
	//fired employees
	$GroupID = "FIRE";
	$currentGroupID = "FIRE";
	$user_rows .= "<tr><td width='1%' nowrap>&nbsp;
	<span id='lyrSymb" . $GroupID . "' style='display:'><a href=\"javascript:;\" onClick=\"{$function}Layer('" . $GroupID . "'); {$revfunction}Layer('Symb" . $GroupID . "'); {$function}Layer('revSymb" . $GroupID . "') \">$symbol</a></span>
	<span id='lyrrevSymb" . $GroupID . "' style='display:none'><a href=\"javascript:;\" onClick=\"{$revfunction}Layer('" . $GroupID . "'); {$function}Layer('Symb" . $GroupID . "'); {$revfunction}Layer('revSymb" . $GroupID . "') \">$revsymbol</a></span>
	&nbsp;</td><td colspan='6' bgcolor='#cccccc' width='10%' nowrap><b>";
	$user_rows .= "<a href='" . $PHP_SELF . "?filtergroup=" . $GroupID . "'>";
	$user_rows .= "Terminated Employees";
	$user_rows .= "</a>";
	$user_rows .= "</b></td></tr>";
	
	//now get the fired users
	$queryfire = "SELECT DISTINCT user.user_logon, user.user_name, enotes.time_stamp
	FROM user inner join employee_notes enotes on user.user_id = enotes.user_id
	WHERE enotes.status = '" . $currentGroupID . "'
	ORDER BY user.user_logon";
	$resultfire = mysql_query($queryfire, $link) or die("unable to get fired employee<br>" . mysql_error());
	$numberfire = mysql_numrows($resultfire);
	
	for ($intF=0;$intF<$numberfire;$intF++) {
		if (($intF%2)==0) { $bgcolor="#FFFFFF"; } else { $bgcolor="#ededed"; }
		$UserName = mysql_result($resultfire, $intF, "user_name");
		$user_logon = mysql_result($resultfire, $intF, "user_logon");
		$fired_date = mysql_result($resultfire, $intF, "time_stamp");
		$fired_date = date("m/d/Y", strtotime($fired_date));
		$table_rows[$currentGroupID] .= "<tr><td style='background:".  $bgcolor . "' colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td style='background:".  $bgcolor . "' nowrap colspan='2'><a href='edit.php?user=" . $user_logon . "'>" . $UserName . "</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td style='background:".  $bgcolor . "' align='right' nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Terminated on&nbsp;</td>
		<td style='background:".  $bgcolor . "'>" . $fired_date . "</td>
		</tr>";				
	}
	
	$user_rows .= "<tr><td colspan='6'>
	<div id='lyr" . $currentGroupID . "'";
	if ($filtergroup=="FIRE") {
		$user_rows .= " style='display:'>";
	} else {
		$user_rows .= " style='display:none'>";
	}
	$user_rows .= " <table width='100%' border='0' cellpadding='0' cellspacing='0'>" . $table_rows[$currentGroupID] . "</table></div>
	</td></tr>";
}
*/
$user_rows = implode("\r\n", $table_rows);
echo "
<table border='0' cellspacing='0' width='30%' align='center'>
	<thead>
	<tr>
      <td colspan='10' align='left' valign='top' bgcolor='#000033' id='header_assign'>
	  	<div style='float:right'>
			<button class='btn btn-primary btn-xs new_employee'>New Employee</button>
		</div>
	  	<span class='admintitle'>
			LIST EMPLOYEES
		</span>
	   </td>
	</tr>
	<tr>
		<th align='left'>&nbsp;</th>
		<th align='right'>Type</th>
		<th align='right'>Status</th>
		<!--
		<th align='right'>&nbsp;&nbsp;&nbsp;In</th>
		<th align='right'>Out</th>
		-->
		<th align='right'>Pay&nbsp;Rate</th>
		<th align='right'>Per</th>
		<th align='right'>When</th>
		<th align='right'>How</th>
		<th align='right' colspan='3'>&nbsp;</th>
	</tr>
	</thead>
	<tbody>" . 
	$user_rows . "
	</tbody>
</table>";
?>
<script language="javascript">
setTimeout(function() {
	window.employee_list.prototype.doTimeouts();
}, 100);
</script>