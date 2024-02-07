<?php 
error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

include("../api/manage_session.php");
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
	//die(print_r($_SESSION));
}

include("../api/connection.php");
include ("../text_editor/ed/datacon.php");

//include("../api/email_message.php");

$id = passed_var("id");
$copy = "check";
if (isset($_GET["copy"])) {
	$copy = "copy";
}
$customer_id = $_SESSION['user_customer_id'];

$sql_customer = "SELECT *
FROM  `ikase`.`cse_customer` 
WHERE customer_id = :customer_id";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}
/*
$sql_account =  "SELECT acct.*
FROM `cse_account` acct
WHERE acct.deleted = 'N'
AND acct.account_type = 'operating'
AND acct.customer_id = :customer_id";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql_account);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$account = $stmt->fetchObject();
	
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$account_name = "";
	if (is_object($account)) {
		$account_name = $account->account_name;
		$account_address = "";
		$routing_number = "";
		$account_number = "";
		$account_info = json_decode($account->account_info);
		foreach($account_info as $info) {
			if ($info->name == "branch_addressInput") {
				$account_address = str_replace("\r\n", "<br />", $info->value);
			}
			if ($info->name == "routing_numberInput") {
				$routing_number = $info->value;
			}
			if ($info->name == "account_numberInput") {
				$account_number = $info->value;
			}
		}
		//die(print_r($account_info));
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}
*/
$sql = "SELECT `check`.*, `check`.`check_id` `id` , `check`.`check_uuid` `uuid`, 
	
	IF(`corp`.`company_name` IS NULL, `pers`.`full_name`, `corp`.`company_name`) payable_full_name,
	IF (`corp`.`company_name` IS NOT NULL, IF(`corp`.`type` = 'recipient', 'records', 'standard'), 'standard') payable_type,
	IF(`corp`.`company_name` IS NULL, 'person', 'corporation') payable_table,
	
	IF(`corp`.`street` IS NULL, `pers`.`street`, `corp`.`street`) payable_street,
	IF(`corp`.`city` IS NULL, `pers`.`street`, `corp`.`street`) payable_city,
	IF(`corp`.`state` IS NULL, `pers`.`street`, `corp`.`street`) payable_state,
	IF(`corp`.`zip` IS NULL, `pers`.`street`, `corp`.`street`) payable_zip,
	
	IF(`corp`.`company_name` IS NULL, pers.person_id, corp.corporation_id) payable_id,
	ccase.case_id, ccase.case_name, ccase.file_number, ccase.case_number,
	IFNULL(`prints`.print_date, '') print_date, IFNULL(`prints`.print_by, '') print_by,
	IFNULL(acc.account_id, -1) account_id, IFNULL(acc.account_name, '') account_name, IFNULL(acc.account_info, '') account_info,
	IFNULL(creq.payable_type, '') request_payable_type

	FROM `cse_check` `check` 
	
	INNER JOIN cse_case_check ccheck
	ON check.check_uuid = ccheck.check_uuid
	INNER JOIN cse_case ccase
	ON ccheck.case_uuid = ccase.case_uuid
		
	LEFT OUTER JOIN cse_checkrequest creq
	ON `check`.check_uuid = creq.check_uuid
	
	LEFT OUTER JOIN cse_corporation_check ccc
	ON `check`.check_uuid = ccc.check_uuid AND ccc.deleted = 'N'
	LEFT OUTER JOIN cse_corporation corp
	ON ccc.corporation_uuid = corp.corporation_uuid
	
	
	LEFT OUTER JOIN cse_person_check cpc
	ON `check`.check_uuid = cpc.check_uuid AND cpc.deleted = 'N'
	LEFT OUTER JOIN ";
			
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " pers
	ON cpc.person_uuid = pers.person_uuid
	
	LEFT OUTER JOIN cse_account_check cac
	ON `check`.check_uuid = cac.check_uuid AND cac.deleted = 'N'
	LEFT OUTER JOIN cse_account acc
	ON cac.account_uuid = acc.account_uuid
		
	LEFT OUTER JOIN (
		SELECT check_uuid, `time_stamp` print_date, `user_logon` print_by
		FROM cse_check_track
		WHERE operation = 'printed'
	)	`prints`
	ON `check`.check_uuid = `prints`.check_uuid
	
	WHERE `check`.`check_id` = :id
	AND `check`.`customer_id` = " . $_SESSION['user_customer_id'] . "
	AND `check`.deleted = 'N'";
//die($sql);
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	
	$stmt->execute();
	$check = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	//check on copy
	if ($copy=="check") {
		if ($check->print_date!="") {
			$copy = "copy";
		}
	}
	if ($check->file_number=="") {
		$check->file_number = $check->case_number;
	}
	$city_state = "";
	if ($check->payable_city!="") {
		$city_state = $check->payable_city . ", " . $check->payable_state . " " . $check->payable_zip;
	}
	
	$decimal = "00";
	$arrNumber = explode(".", $check->amount_due);
	if (count($arrNumber)==2) {
		$decimal = $arrNumber[1];
	}
	/*
	echo floatval($check->amount_due) . "<br />";
	echo floor(floatval($check->amount_value)) . "<br />";
	
	die("dec:" . $decimal);
	*/
	if ($decimal=="00") {
		$cents = " Only";
	} else {
		$cents = " and " . $decimal . " cents";
	}
	$written_amount = ucwords(convertNumberToWord($check->amount_due)) . $cents;
	
	$check->check_number = str_replace("TRNSFR:", "", $check->check_number);
	
	//account info
	$account_name = "";
	if ($check->account_id > 0) {
		$account_name = $check->account_name;
		$account_address = "";
		$routing_number = "";
		$account_number = "";
		$account_info = json_decode($check->account_info);
		foreach($account_info as $info) {
			if ($info->name == "branch_addressInput") {
				$account_address = str_replace("\r\n", "<br />", $info->value);
			}
			if ($info->name == "routing_numberInput") {
				$routing_number = $info->value;
			}
			if ($info->name == "account_numberInput") {
				$account_number = $info->value;
			}
		}
		//die(print_r($account_info));
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>

<!-- Remove the protocol prefix (i.e., "http:") if hosting on a server instead of from localhost. -->
<script type="text/javascript" src="../lib/jquery.1.10.2.js"></script>

<script>
	$(document).ready(function() {
		var url = "../api/printcheck";
		var formValues = "id=<?php echo $check->id; ?>&copy=<?php echo $copy; ?>";
		$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					
				}
				if (data.success) {
					//console.log("printed");
				}
			}
		});
		return;
	})
</script>

<style>
	body {
		background:url(../images/<?php echo $copy; ?>_template.jpg) no-repeat top left;
		font-family:Arial, Helvetica, sans-serif;
		font-size:0.9em;
	}
	div
	{
		position: absolute;
	}
	
	div#main_check_from
	{
		left: 15px;
		top: 45px;
		width:100%;
	}
	div#main_check_account_name {
		left: 385px;
		top: 45px;
		width:300px;
	}
	div#main_check_check_number {
		left: 605px;
		top: 45px;
		font-weight:bold;
		font-size:1.2em
	}
	div#main_check_date_box
	{
		left: 655px;
		top: 80px;
	}
	
	div#main_check_pay-to_box
	{
		left: 95px;
		top: 125px;
	}
	
	div#main_check_amount-nbr_box
	{
		left: 680px;
		top: 125px;
	}
	
	div#main_check_amount-txt_box
	{
		left: 30px;
		top: 167px;
	}
	
	div#main_check_pay-to-address_box
	{
		left: 30px;
		top: 98px;
		display:none;
	}
	
	div#main_check_memo_box
	{
		left: 20px;
		top: 217px;
	}
	
	div#main_check_acct_number
	{
		left: 100px;
		top: 299px;
	}
	
	div#main_check_routing_number
	{
		left: 260px;
		top: 299px;
	}
	
	div#main_check_bottomcheck_number
	{
		left: 460px;
		top: 299px;
	}
	@font-face {
		font-family: 'bankFont';
		src: url('micrenc.ttf'); 
	}
	.bank_font {
		font-family: "bankFont";
		font-size:2em;
	}
</style>
</head>
<body>
	<div id="main_check_from">
    	<div style="font-size:1.2em; font-weight:bold">
			<?php echo $customer->cus_name; ?>
		</div>
		<div style="margin-top:20px">
			<?php echo $customer->cus_street . "<br>" . $customer->cus_city . ", " . $customer->cus_state . " " . $customer->cus_zip; ?>
		</div>
    </div>
    <div id="main_check_check_number">
		<?php echo $check->check_number; ?>
	</div>
	<div id="main_check_date_box">
		<?php echo date("m/d/Y", strtotime($check->check_date)); ?>
	</div>
	<div id="main_check_pay-to_box">
		<?php 
		if ($check->request_payable_type=="F") { 
			$check->payable_full_name = $_SESSION["user_customer_name"];
		}
		echo $check->payable_full_name; ?>
	</div>
	<div id="main_check_amount-nbr_box">
		<?php echo number_format($check->amount_due, 2); ?>
	</div>
	<div id="main_check_amount-txt_box">
		<?php echo $written_amount; ?>
	</div>
	<div id="main_check_pay-to-address_box">
	<pre>
		<?php echo $check->payable_street; ?>
	</pre>
	<pre>
		<?php echo $city_state; ?>
	</pre>
	</div>
	<div id="main_check_memo_box">
		<?php echo "<div style='width:40px; position:initial; display:inline-block'>&nbsp;</div>" . $check->case_name . " - " . $check->file_number; ?>
        <?php if ($check->memo!="" && strpos($check->memo, "Check issued to")===false) { 
			echo "<div style='width:40px; position:initial; display:inline-block'>&nbsp;</div><div>" . $check->memo . "</div>";
		}
		?>
	</div>
	<?php if ($account_name!="") { ?>
	<div id="main_check_account_name">
		<div style="font-size:1.2em; font-weight:bold"><?php echo $account_name; ?></div>
		<div style="margin-top:20px">
			<?php echo $account_address; ?>
		</div>
	</div>
	<?php } ?>
    <div id="main_check_acct_number" class="bank_font">
    <?php echo $account_number; ?>
    </div>
    <div id="main_check_routing_number" class="bank_font">
    	<?php echo $routing_number; ?>
    </div>
    <div id="main_check_bottomcheck_number" class="bank_font">
    	<?php echo $check->check_number; ?>
    </div>
</body>
</html>