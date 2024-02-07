<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

require_once('../../shared/legacy_session.php');

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}

date_default_timezone_set('America/Los_Angeles');

include("connection.php");

$reimbursment_id = passed_var("reimbursment_id", "post");

$verb = "CREATE";
$reimbursment = "";
$description = "";

if ($reimbursment_id!="new") {
	$sql = "SELECT *
	FROM reimbursment
	WHERE customer_id = :customer_id
	AND reimbursment_id = :reimbursment_id";
	
	try {
		$customer_id = $_SESSION["user_customer_id"];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("reimbursment_id", $reimbursment_id);
		$stmt->execute();
		$reimburs = $stmt->fetchObject();
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	$reimbursment = $reimburs->reimbursment;
	$description = $reimburs->description;
	$verb = "EDIT";
}
?>
<form id="reimbursment_form">
    <input type="hidden" name="reimbursment_id" id="reimbursment_id" value="<?php echo $reimbursment_id; ?>" />
    <table width="900" border="0" align="center" cellpadding="2" cellspacing="1" bordercolor="#000000">
        <tr>
          <td colspan="2" align="left" valign="top" style="background:#000033" id="header_reimbursment">
            <div style="float:right">
                <button class="btn btn-xs btn-primary" id="edit_reimbursment">Edit</button>
                <button class="btn btn-xs btn-primary hide_me" id="save_reimbursment">Save</button>
            </div>
            <span class="admintitle"><?PHP echo $verb; ?> REIMBURSMENT</span>
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" colspan="2">&nbsp;
            
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" class="td_label" width="1%" nowrap="nowrap">
            <span style='font-weight:bold'>Reimbursment</span>
          </td>
          <td align="left" valign="top">
            <input type="text" id="reimbursmentField" name="reimbursmentField" value="<?php echo $reimbursment; ?>" class="reimbursment edit_field hide_me" />
            <span id="reimbursmentSpan" class="reimbursment edit_span"><?php echo $reimbursment; ?></span>
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" class="td_label">
            <span style='font-weight:bold'>Description</span>
          </td>
          <td align="left" valign="top">
            <textarea id="descriptionField" name="descriptionField" class="reimbursment edit_field hide_me" rows="2" style="width:815px"><?php echo $description; ?></textarea>
            <span id="descriptionSpan" class="reimbursment edit_span"><?php echo $description; ?></span>
          </td>
        </tr>
    </table>
</form>
<script language="javascript">
setTimeout(function() {
	window.reimbursment_edit.prototype.doTimeouts();
}, 100);
</script>
