<?php
include("manage_session.php");
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}

include("connection.php");

$sql = "SHOW FULL PROCESSLIST";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$procs = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt = null; $db = null;

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
$arrRows = array();
foreach($procs as $proc) {
	if ($proc->Info=="SHOW FULL PROCESSLIST") {
		continue;
	}
	$row = "
	<tr>
		<td align='left' valign='top'>" . $proc->Id . "</td>
		<td align='left' valign='top'>" . $proc->Host . "</td>
		<td align='left' valign='top'>" . $proc->db . "</td>
		<td align='left' valign='top'>" . $proc->Command . "</td>
		<td align='left' valign='top'>" . $proc->Time . "</td>
		<td align='left' valign='top'>" . $proc->State . "</td>
		<td align='left' valign='top' style='font-size:0.7em'>" . $proc->Info . "</td>
	</tr>
	";
	$arrRows[] = $row;
}
?>
As of <?php echo date("H:i:s"); ?>
<br />
<input type="checkbox" id="reload" checked="checked" />Auto-Reload in 2 seconds
<?php if (count($arrRows)==0) { ?>
<div>No Processes Running at this time</div>
<?php } else { ?>
<table border="1">
	<tr>
		<th align='left' valign='top'>ID</th>
		<th align='left' valign='top'>Host</th>
		<th align='left' valign='top'>DB</th>
		<th align='left' valign='top'>Command</th>
		<th align='left' valign='top'>Time</th>
		<th align='left' valign='top'>State</th>
		<th align='left' valign='top'>Info</th>
	</tr>
	<?php echo implode("
	", $arrRows); ?>
</table>
<?php } ?>
<script type="text/javascript">
function reloadData() {
	if (document.getElementById("reload").checked) {
		document.location.reload();
	}
}
setTimeout(function() {
	 reloadData();
}, 2000);
</script>