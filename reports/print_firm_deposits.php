<?php 
error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

require_once('../shared/legacy_session.php');
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

$id = "";
if (isset($_GET["id"])) {
	$id = passed_var("id", "get");
}
$ids = $id;
if (isset($_GET["ids"])) {
	$ids = passed_var("ids", "get");
}
$arrIDs = explode("|", $ids);

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
$sql = "SELECT DISTINCT `check`.*, `check`.`check_id` `id` , `check`.`check_uuid` `uuid`

FROM `cse_check` `check` 

WHERE `check`.`check_id` IN (" . implode(",", $arrIDs) . ") AND `check`.`customer_id` = " . $_SESSION["user_customer_id"] . " AND `check`.deleted = 'N'";
	//die($sql);
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	
	$stmt->execute();
	$checks = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($checks));
	
	foreach($checks as $check) {
	
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
<style>
	body {
		/*
		background:url(../images/<?php echo $copy; ?>_template.jpg) no-repeat top left;
		*/
		font-family:Arial, Helvetica, sans-serif;
		font-size:1.1em;
	}
	
</style>
</head>
<body>
	<div style="float:right; font-size:0.8em">
    As of <?php echo date("m/d/Y g:iA"); ?>
    </div>
	<div style="font-size:1.2em; font-weight:bold"><?php echo $account_name; ?> DEPOSIT REGISTER</div>
    <div>
    	<table width="100%">
        	<tr>
            	<td colspan="8"  style="border-top:black 1px solid">&nbsp;
                
                </td>
            </tr>
            <tr>
            	<th align="left" valign="top">Date</th>
                <th align="left" valign="top" width="30%">Source</th>
                <th align="left" valign="top">Check #</th>
                <th align="left" valign="top">Type</th>
                <th align="left" valign="top">File #</th>
                <th align="left" valign="top">Amount</th>
                <th align="left" valign="top" width="30%">Comments</th>
            </tr>
            <tr>
            	<td colspan="8"  style="border-top:black 1px solid">&nbsp;
                
                </td>
            </tr>
            <?php foreach($checks as $check) { ?>
        	<tr>
            	<td align="left" valign="top"><?php echo date("m/d/Y", strtotime($check->check_date)); ?></td>
                <td align="left" valign="top"><?php echo $check->payable_full_name; ?></td>
                <td align="left" valign="top"><?php echo $check->check_number; ?></td>
                <td align="left" valign="top" nowrap="nowrap"><?php echo $check->check_type; ?></td>
                <td align="left" valign="top" nowrap="nowrap"><?php echo $check->file_number; ?></td>
                <td align="left" valign="top">$<?php echo number_format($check->amount_due, 2); ?></td>
                <td align="left" valign="top"><?php echo $check->memo; ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
