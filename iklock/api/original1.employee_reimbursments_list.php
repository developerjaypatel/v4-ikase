<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("manage_session.php");

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}

date_default_timezone_set('America/Los_Angeles');

include("connection.php");
include("../classes/cls_user.php");

$user_id = passed_var("user_id", "post");

$sql = "SELECT rei.*,
IFNULL(user_reimbursment_id, -1) user_reimbursment_id
FROM `reimbursment` rei
LEFT OUTER JOIN `user_reimbursment` ure
ON rei.reimbursment_uuid = ure.reimbursment_uuid AND ure.deleted = 'N'
LEFT OUTER JOIN `user` usr
ON ure.user_uuid = usr.user_uuid AND usr.user_id = :user_id
WHERE rei.customer_id = :customer_id";

try {
	$customer_id = $_SESSION["user_customer_id"];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->bindParam("user_id", $user_id);
	$stmt->execute();
	$reimbursments = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($reimbursments));
	$stmt->closeCursor(); $stmt = null; $db = null;

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

$my_user = new systemuser();
$my_user->id = $user_id;
$my_user->fetch();
//ie(print_r($my_user));
$table_rows = array();
foreach($reimbursments as $x=>$reimb) {
	if (($x%2)==0) { $bgcolor="#FFFFFF"; } else { $bgcolor="#ededed"; }
	
	$reimbursment_id = $reimb->reimbursment_id;
	$user_reimbursment_id = $reimb->user_reimbursment_id;
	$reimbursment = $reimb->reimbursment;
	$description = $reimb->description;
	
	$checked = "";
	if ($user_reimbursment_id > 0) {
		$checked = "checked";
	}
	$table_rows[] = "
	<tr>
		<td align='left' valign='top' style='background:".  $bgcolor . "' nowrap>
			<input type='checkbox' name='user_reimbursment_" . $reimbursment_id . "' id='user_reimbursment_" . $reimbursment_id . "' class='user_reimbursment' value='Y' " . $checked . " />
		</td>
		<td align='left' valign='top' style='background:".  $bgcolor . "' nowrap>
			" . $reimbursment . "
		</td>
		<td align='left' valign='top' style='background:".  $bgcolor . "' align='left'>" . $description . "</td>
	</tr>";
}

$rows = implode("\r\n", $table_rows);
echo "
<div>
	<div style='width:30%; margin-left:auto; margin-right:auto; text-align:left; margin-bottom:5px'>
		<button class='btn btn-primary new_reimbursment'>New Reimbursment</button>
	</div>
</div>";
echo "
<form id='user_reimbursment_form'>
<input type='hidden' id='user_id' name='user_id' value='" . $user_id . "' />
<table border='1' cellspacing='0' width='30%' align='center'>
	<thead>
	<tr>
      <td colspan='3' align='left' valign='top' bgcolor='#000033' id='header_assign'>
	  	<div style='float:right'>
			<button class='btn btn-xs btn-primary' id='edit_user_reimbursment'>Edit</button>
			<button class='btn btn-xs btn-primary hide_me' id='save_user_reimbursment'>Save</button>
		</div>
	  	<span class='admintitle'>
			ASSIGN REIMBURSMENTS
		</span>
		 - <a href='#employees/" . $user_id . "' style='color:white'>" . $my_user->user_name . "</a>
	  </td>
	</tr>
	
	<tr>
		<th align='left'>
			<input type='checkbox' id='user_reimbursment_select_all' />&nbsp;Select
		</th>
		<th align='left'>Reimbursment</th>
		<th align='left'>Description</th>
	</tr>
	</thead>
	<tbody>" . 
	$rows . "
	</tbody>
</table>
</form>";
?>
<script language="javascript">
setTimeout(function() {
	window.employee_reimbursments_list.prototype.doTimeouts();
}, 100);
</script>