<?php 
require_once('../shared/legacy_session.php');
session_write_close();

include("../api/connection.php");

$customer_id = $_SESSION["user_customer_id"];

$body_options = '';
try {
	$db = getConnection();
	$stmt = $db->prepare("SELECT * FROM `cse_bodyparts` ORDER BY code");
	$stmt->execute();
	$bodyparts =  $stmt->fetchAll(PDO::FETCH_OBJ);

    foreach ($bodyparts as $bodypart) {
        $body_options .= "<option value='$bodypart->bodyparts_uuid'>$bodypart->code - $bodypart->description</option>";
    }
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=> "SELECT * FROM `cse_bodyparts` ORDER BY code"));
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
