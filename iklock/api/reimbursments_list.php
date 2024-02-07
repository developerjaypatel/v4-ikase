<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

require_once('../../shared/legacy_session.php');
date_default_timezone_set('America/Los_Angeles');

include("connection.php");

$sql = "SELECT *
FROM `reimbursment`
WHERE customer_id = :customer_id";

try {
	$customer_id = $_SESSION["user_customer_id"];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$reimbursments = $stmt->fetchAll(PDO::FETCH_OBJ);

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

foreach($reimbursments as $x=>$reimb) {
	if (($x%2)==0) { $bgcolor="#FFFFFF"; } else { $bgcolor="#ededed"; }
	
	$reimbursment_id = $reimb->reimbursment_id;
	$reimbursment = $reimb->reimbursment;
	$description = $reimb->description;
	
	$table_rows[] = "
	<tr>
		<td align='left' valign='top' style='background:".  $bgcolor . "' nowrap>
			<a href='#reimbursment/" . $reimbursment_id . "'>" . $reimbursment . "</a>
		</td>
		<td align='left' valign='top' style='background:".  $bgcolor . "' align='left'>" . $description . "</td>
	</tr>";
}

$rows = implode("\r\n", $table_rows);
echo "
<div style='width:30%; margin-left:auto; margin-right:auto; text-align:left; margin-bottom:5px'>
	<button class='btn btn-primary new_reimbursment'>New Reimbursment</button>
</div>";
echo "
<table border='1' cellspacing='0' width='30%' align='center'>
	<thead>
	<tr>
		<th align='left'>Reimbursment</th>
		<th align='left'>Description</th>
	</tr>
	</thead>
	<tbody>" . 
	$rows . "
	</tbody>
</table>";
?>
<script language="javascript">
	setTimeout(function() {
	window.reimbursments_list.prototype.doTimeouts();
}, 100);
</script>
