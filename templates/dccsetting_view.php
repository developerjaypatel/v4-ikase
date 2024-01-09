<?php
require_once('../shared/legacy_session.php');
include("../api/connection.php");
session_write_close();

$sql = "SELECT `customer_id`,`docucents_api_key`
	FROM `cse_customer`
	WHERE customer_id = :customer_id";
	try{
		$dbConn =  getConnection(false);
		$stmt = $dbConn->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$customer = $stmt->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		die("Error customer");
	}

?>

<div style="background:url(../img/glass_dark.png); margin-bottom:10px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <div style="font-size:1.2em; color:#FFFFFF; font-weight:bold; margin-left:10px; margin-top:10px; margin-bottom:10px">Docucents API Key</div>
    <table width="95%" cellpadding="0" cellspacing="0" align="center" style="font-size:1.2em; color:white">
        <tr style="">
        <td style="padding: 10px;"><input size="60" input="text" class="docucents_apikey" value=<?php echo $customer['docucents_api_key'];?>>
        </td></tr>
        <tr style=""><td style="padding: 10px;"><button class="btn btn-default save_dccapi_key">Save</button></td></tr>
    </table>
    <div>&nbsp;</div>
</div>
<div id="losses_list_view_done"></div>
<script language="javascript">
  $("#losses_list_view_done").trigger("click")
</script>
