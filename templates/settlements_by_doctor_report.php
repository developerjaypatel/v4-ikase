<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
//die(print_r($_POST));
require_once('../shared/legacy_session.php');
session_write_close();

if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
  $application_logo = "logo-starlinkcms.png";
}
else
{
  $application_logo = "ikase_logo_login.png";
}

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	//die(print_r($_SESSION));
	header("location:index.php?cusid=-1");
	die();
}

include("../api/connection.php");

$doctor_ids = passed_var("doctor_ids", "post");
$bodyparts = passed_var("bodyparts", "post");
$customer_id = $_SESSION["user_customer_id"];

if ($doctor_ids=="" && $bodyparts=="") {
	die("no search terms");
}
//let's run the query
$sql = "SELECT corporation_id, company_name, bodyparts_id, `code`, `description`,
		COUNT(DISTINCT case_id) case_count, AVG(amount_of_settlement) avg_settlement, 
		MIN(amount_of_settlement) min_settlement, MAX(amount_of_settlement) max_settlement
		FROM (
			SELECT ccase.case_id, ccase.case_name, inj.start_date, inj.end_date, 
			sett.amount_of_settlement, 
			#fee.*, 
			corp.corporation_id,
			corp.company_name,
			parts.bodyparts_id, parts.`code`, parts.`description`  
			FROM cse_settlement sett
			INNER JOIN cse_injury_settlement iset
			ON sett.settlement_uuid = iset.settlement_uuid
			INNER JOIN cse_injury inj
			ON iset.injury_uuid = inj.injury_uuid
			INNER JOIN cse_case_injury cci
			ON inj.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
		
			LEFT OUTER JOIN cse_settlement_fee sfee
			ON sett.settlement_uuid = sfee.settlement_uuid
			LEFT OUTER JOIN cse_fee fee
			ON sfee.fee_uuid = fee.fee_uuid
			LEFT OUTER JOIN cse_corporation corp
			ON fee.fee_doctor_id = corp.corporation_id
			LEFT OUTER JOIN cse_corporation parent
			ON corp.parent_corporation_uuid = parent.corporation_uuid
		
			LEFT OUTER JOIN cse_injury_bodyparts cib
			ON inj.injury_uuid = cib.injury_uuid
			LEFT OUTER JOIN cse_bodyparts parts
			ON cib.bodyparts_uuid = parts.bodyparts_uuid
		
			WHERE 1 AND (";
$arrSearch = array();
if ($doctor_ids!="") {
	$arrSearch[] = "
			 parent.corporation_id IN (" . $doctor_ids . ")";
}
if ($bodyparts!="") {
	$arrSearch[] = "
			 parts.`bodyparts_uuid` IN (" . $bodyparts . ")";
}

$sql .= implode(" AND ", $arrSearch) . "
			)	
			AND ccase.customer_id = '" . $customer_id . "'
		) settleds
		GROUP BY corporation_id, bodyparts_id
		ORDER BY company_name, `code`";
		//die($sql);
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$settlements =  $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
?>
<table width="1000" border="0" cellpadding="3" cellspacing="0" align="center">
  <tr>
    <td><img src="../img/<?php echo $application_logo; ?>" alt="Logo" height="40" /></td>
    <td align="center" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em" colspan="3" nowrap="nowrap">
    	<div style="float:right; font-weight:normal; font-size:9px"><em>as of <?php echo date("m/d/y g:iA"); ?></em></div>
    Doctor Settlements by Bodypart - <?php echo $_SESSION['user_customer_name']; ?></td>
  </tr>
</table>
<?php if (!isset($settlements)) {
	die("No Settlements for this search");
}
?>
<table cellpadding="2" cellspacing="0" border="0" id="summary_table" style="margin-top:50px" align="center">
	<thead>
    <tr>
    	<th align="left">
        	Doctor
        </th>
        <th align="left">
        	Cases
        </th>
        <th align="left">
        	Body Part
        </th>
        <th align="left" bgcolor="#009900" style="color:white">
       	    Avg Settle
       </th>
      <th align="left" bgcolor="#009900" style="color:white">
       	    Min Settle
       </th>
        <th align="left" bgcolor="#FF0000" style="color:white">
       	    Max Settle
       </th>
    </tr>
    </thead>
    <tbody>
    <?php 
	$current_doctor = "";
	foreach($settlements as $settlement) { 
		$display_doctor = "";
		if ($current_doctor != $settlement->company_name) {
			$display_doctor = $settlement->company_name;
			$current_doctor = $settlement->company_name;
		}
        ?>
        <tr>
        	<td align="left">
            	<?php  echo $display_doctor; ?>
            </td>
            <td align="left">
            	<?php  echo $settlement->case_count; ?>
            </td>
            <td align="left">
            	<?php  echo $settlement->code . ") " . $settlement->description; ?>
            </td>
            <td align="left">
            	$<?php  echo number_format($settlement->avg_settlement, 2); ?>
            </td>
            <td align="left">
            	$<?php  echo number_format($settlement->min_settlement, 2); ?>
            </td>
            <td align="left">
            	$<?php  echo number_format($settlement->max_settlement, 2); ?>
            </td>
        </tr>
	<?php } ?>
    </tbody>
</table>
