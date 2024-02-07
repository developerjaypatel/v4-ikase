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
$sql = "SELECT DISTINCT `check`.*, `check`.`check_id` `id` , `check`.`check_uuid` `uuid`, 
	
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
	IFNULL(casepers.full_name, '') full_name

	FROM `cse_check` `check` 
	
	INNER JOIN cse_case_check ccheck
	ON `check`.check_uuid = ccheck.check_uuid
	INNER JOIN cse_case ccase
	ON ccheck.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN cse_corporation_check ccc
	ON `check`.check_uuid = ccc.check_uuid AND ccc.deleted = 'N'
	LEFT OUTER JOIN cse_corporation corp
	ON ccc.corporation_uuid = corp.corporation_uuid
	
	LEFT OUTER JOIN cse_case_person ccp
	ON ccase.case_uuid = ccp.case_uuid AND ccp.deleted = 'N'
	LEFT OUTER JOIN ";
			
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " casepers
	ON ccp.person_uuid = casepers.person_uuid
	
	
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
		SELECT cct.check_uuid, cct.`time_stamp` print_date, cct.`user_logon` print_by, last_track.track_count
		FROM cse_check_track cct
        INNER JOIN (
			SELECT `cct`.`check_id`, MAX(check_track_id) max_track_id, COUNT(check_track_id) track_count
			FROM cse_check_track cct
			WHERE operation = 'printed'
			AND `cct`.`check_id` IN (" . implode(",", $arrIDs) . ")
			GROUP BY `cct`.`check_id`
        ) last_track
        ON cct.check_track_id = last_track.max_track_id
		WHERE operation = 'printed'
        AND `cct`.`check_id` IN (" . implode(",", $arrIDs) . ")
	)	`prints`
	ON `check`.check_uuid = `prints`.check_uuid
	
	WHERE `check`.`check_id` IN (" . implode(",", $arrIDs) . ")
	AND `check`.`customer_id` = " . $_SESSION['user_customer_id'] . "
	AND `check`.deleted = 'N'";
	//die($sql);
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	
	$stmt->execute();
	$checks = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($checks));
	$firm_dep = "";
	if (empty($checks)) {
		$firm_dep = "Y";
	}
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
	<input type="hidden" value="<?php echo $firm_dep; ?>" id="firm_dep" />
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
                <th align="left" valign="top" width="30%">Case</th>
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
                <td align="left" valign="top">
					<?php 
						echo $check->case_name; 
						if ($_SESSION["user_customer_id"]=="1121") {
							//per elizabeth martinez 3/19/19
							if ($check->full_name!="") {
								if (strpos($check->case_name, $check->full_name) === false) {
									echo "<br />(Plaintiff:&nbsp;" . $check->full_name . ")";
								}
							}
						}
					?>
                </td>
                <td align="left" valign="top" nowrap="nowrap"><?php echo $check->file_number; ?></td>
                <td align="left" valign="top">$<?php echo number_format($check->amount_due, 2); ?></td>
                <td align="left" valign="top"><?php echo $check->memo; ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php if ($firm_dep == "Y") { ?>
        	<a href="print_firm_deposits.php?id=<?php echo implode(",", $arrIDs); ?>" id="firm_dep_click">Firm Deposits</a>
        <?php } ?>
    </div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript">
	var firm_dep = "<?php echo $firm_dep; ?>";
	//var link_firm = $("#firm_dep_click");
	$(document).ready(function(){
		//
			if (firm_dep == "Y") {
				window.location = document.getElementById('firm_dep_click').href;
				//setTimeout(function(){
				//$("#firm_dep_click").trigger("click");
				//}, 500);
			}
		//}, 500);
	});
</script>
</body>
</html>
