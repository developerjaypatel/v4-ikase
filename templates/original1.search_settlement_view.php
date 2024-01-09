<?php 
include("../api/manage_session.php");
session_write_close();

include("../api/connection.php");

$customer_id = $_SESSION["user_customer_id"];

$sql = "SELECT * FROM `cse_bodyparts` 
WHERE 1
ORDER BY code ASC";
//echo $query;
$body_options = '';
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$bodyparts =  $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	foreach($bodyparts as $bodypart){
		// ($int=0;$int<$numbs;$int++) {
		$bodyparts_id = $bodypart->bodyparts_id;
		$bodyparts_uuid = $bodypart->bodyparts_uuid;
		$code = $bodypart->code;
		$description = $bodypart->description;
		$option = '<option value="' . $bodyparts_uuid . '">' . $code . ' - ' . $description . '</option>';
		$body_options .= $option;
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}

?>
<div class="settlement" style="margin-left:15px">
    <form id="settlement_form" parsley-validate>
        <table width="300px" border="0" align="left" cellpadding="3" cellspacing="0">
            <tr>
                <th width="19%" align="right" valign="top" nowrap="nowrap" scope="row">Doctor:</th>
                <td colspan="2" valign="top">
                    <input name="company_nameInput" type="text" id="company_nameInput" autocomplete="off" style="width:227px" class="settlement input_class floatlabel" />
                </td>
            </tr>
            <tr>
                <th width="19%" align="right" valign="top" nowrap="nowrap" scope="row">Body Parts:</th>
                <td colspan="2" valign="top" align="left">
                  <select name="bodypartSearchInput[]" id="bodypartSearchInput" multiple="multiple" style="width:280px; height:240px">
                  <?php echo $body_options; ?>
                  </select>
                 </td>
            </tr>
        </table>
    </form>
</div>