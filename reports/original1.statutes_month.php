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


$customer_id = $_SESSION['user_customer_id'];
$month = passed_var("month");



try {
	$sql = "SELECT user_id, nickname
	FROM ikase.cse_user
	WHERE customer_id = :customer_id";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$arrUsers = array();
	foreach($users as $user) {
		$arrUsers[$user->user_id] = $user-nickname;
	}
	
	$sql = "SELECT DISTINCT ccase.case_id, ccase.case_name, ccase.case_number, COUNT(ccase.case_number), ccase.file_number, ccase.supervising_attorney, ccase.attorney, ccase.worker, ccase.case_type,
	IFNULL(pers.full_name, '') applicant, IFNULL(plaintiff.company_name, '') plaintiff, 
	IFNULL(defendant.company_name, '') defendant, 
	cpi.personal_injury_date doi, cpi.statute_limitation sol
	FROM cse_case  ccase
	INNER JOIN cse_personal_injury cpi
	ON ccase.case_id = cpi.case_id
	
	LEFT OUTER JOIN cse_case_person cpers
	ON ccase.case_uuid = cpers.case_uuid AND cpers.attribute = 'main' AND cpers.deleted = 'N'
	LEFT OUTER JOIN cse_person pers
	ON cpers.person_uuid = pers.person_uuid
	
	LEFT OUTER JOIN cse_case_corporation cplaint
	ON ccase.case_uuid = cplaint.case_uuid AND cplaint.attribute = 'plaintiff' AND cplaint.deleted = 'N'
	LEFT OUTER JOIN cse_corporation plaintiff
	ON cplaint.corporation_uuid = plaintiff.corporation_uuid
	
	INNER JOIN cse_case_corporation cdef
	ON ccase.case_uuid = cdef.case_uuid AND cdef.attribute = 'defendant' AND cdef.deleted = 'N'
	INNER JOIN cse_corporation defendant
	ON cdef.corporation_uuid = defendant.corporation_uuid
	
	WHERE 1
	AND ccase.customer_id = :customer_id
	AND ccase.case_status NOT LIKE '%close%' AND ccase.case_status NOT LIKE 'CL-%' AND ccase.case_status NOT LIKE 'CLOSED%' AND ccase.case_status NOT LIKE 'Sub%' AND ccase.case_status != 'DROPPED' AND ccase.case_status != 'REJECTED'
	#AND ccase.case_id = 9414
	AND (
		INSTR(ccase.case_type, 'Personal Injury') > 0
		OR
		ccase.case_type = 'NewPI'
	)
	AND cpi.statute_limitation BETWEEN '" . date("Y-m-d", strtotime($month)) . "' AND '" . date("Y-m-d", strtotime($month . "+1 month")) . "'
	
	GROUP BY ccase.case_number
	HAVING COUNT(ccase.case_number) >= 1
	ORDER BY cpi.statute_limitation";
	//die($sql);
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$statutes = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}

//die(print_r($statutes));
$arrRows = array();
$first_date = "";
$len = count($statutes);
$counter = 0;
foreach($statutes as $index=>$statute){
	$current_date = date("m", strtotime($statute->sol));
	if($index == 0) {
		$first_case_number = $statute->case_number;
	}
	/*if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
		$next_case_number = $statute->case_number;
		if ($next_case_number == $previous_case_number && $index != 0 || strpos($previous_case_number, "-") === false) {
			
			if (strpos($previous_case_number, "-") === false || strpos($previous_case_number, "-1") === false) {
				$counter++;
				$statute->case_number = $statute->case_number .  "-" . $counter;
			} else {
				$statute->case_number = $statute->case_number .  "-" . $counter;;
			}
			//$statute->case_number = $statute->case_number .  "-";
			if (strpos($previous_case_number, "-") === false) {
				$counter = 0;
			}
		}
		
	}*/
	if($index == 0) {
		$previous_date = 0;
   		$first_date = date("m/d/Y", strtotime($statute->sol));
		$first_date_month = date("M", strtotime($statute->sol));
		$first_date_year = date("Y", strtotime($statute->sol));
	}
	if($index == $len - 1) {
   		$last_date = date("m/d/Y", strtotime($statute->sol));
	}
	
	$background = "#FFFFFF";
	if ($index%2==0) {
		$background = "#EDEDED";
	}
	$applicant = $statute->applicant;
	if ($applicant=="") {
		$applicant = $statute->plaintiff;
	}
	
	if (is_numeric($statute->attorney)) {
		$statute->attorney = $arrUsers[$statute->attorney];
	}
	if (is_numeric($statute->supervising_attorney)) {
		$statute->supervising_attorney = $arrUsers[$statute->supervising_attorney];
	}
	if (is_numeric($statute->worker)) {
		$statute->worker = $arrUsers[$statute->worker];
	}
	if ($statute->sol=="0000-00-00") {
		$sol = "NO SOL";
	} else {
		$sol =  date("m/d/Y", strtotime($statute->sol));
	}
	$case_type = $statute->case_type;
	if ($case_type == "NewPI" || strpos($case_type, "Personal Injury")!==false) {
		$case_type = "PI";
	}
	
	if ($current_date > $previous_date && $previous_date !=0) {
		$row = "
		<tr>
			<td align='left' colspan='8' valign='top' style='background:#999999; font-size:2em; color:blue'>"
				. date("M Y", strtotime($statute->sol)) .
			"</td>
			
		</tr>
		";
	} else {
		$row = "
		<tr>
			<td align='left' valign='top' style='background: " . $background . "; border-bottom:1px solid black'>
				<!--<div style='float:right'>" . $case_type . "</div>-->
				" . capWords($statute->case_name) . "
			</td>
			<td align='left' valign='top' style='background: " . $background . "; border-bottom:1px solid black'>
				" . $statute->file_number. "
			</td>
			<td align='left' valign='top' style='background: " . $background . "; border-bottom:1px solid black'>
				" . capWords($statute->defendant) . "
			</td>
			<td align='left' valign='top' style='background: " . $background . "; border-bottom:1px solid black'>
				" . $statute->attorney . "
			</td>
			<td align='left' valign='top' style='background: " . $background . "; border-bottom:1px solid black'>
				" . date("m/d/Y", strtotime($statute->doi)) . "
			</td>
			<td align='left' valign='top' style='background: " . $background . "; color:red; border-bottom:1px solid black'>
				" . $sol . "
			</td>
		</tr>
		";
	}
	if ($previous_date == 0) {
		$row = "
		<tr>
			<td align='left' colspan='8' valign='top' style='background:#999999; font-size:2em; color:blue'>"
				. $first_date_month . " " . $first_date_year .
			"</td>
			
		</tr>
		<tr>
			<td align='left' valign='top' style='background: " . $background . "; border-bottom:1px solid black'>
				<!--<div style='float:right'>" . $case_type . "</div>-->
				" . capWords($statute->case_name) . "
			</td>
			<td align='left' valign='top' style='background: " . $background . "; border-bottom:1px solid black'>
				" . $statute->file_number. "
			</td>
			<td align='left' valign='top' style='background: " . $background . "; border-bottom:1px solid black'>
				" . capWords($statute->defendant) . "
			</td>
			<td align='left' valign='top' style='background: " . $background . "; border-bottom:1px solid black'>
				" . $statute->attorney . "
			</td>
			<td align='left' valign='top' style='background: " . $background . "; border-bottom:1px solid black'>
				" . date("m/d/Y", strtotime($statute->doi)) . "
			</td>
			<td align='left' valign='top' style='background: " . $background . "; color:red; border-bottom:1px solid black'>
				" . $sol . "
			</td>
		</tr>
		";
	}
	if ($current_date > $previous_date && $previous_date !=0 && date("m", strtotime($statute->sol)) == 06 && date("y", strtotime($statute->sol)) < 2020) {
		$row = "";
	}
	$arrRows[] = $row;
	$previous_date = date("m", strtotime($statute->sol));
	$previous_case_number = $statute->case_number;
	
} ?>
<?php
if (count($arrRows) == 0) {
	die("NO STATUTES DATA");
}
if (count($arrRows) > 0) { ?>
<title>STATUTE REPORT</title>

<div>
  <div style="float:right">
    As of <?php echo date("m/d/Y g:iA"); ?>
    </div>
	<span style="font-size:1.6em; font-weight:bold">PI DEPARTMENT - STATUTE REPORT</span>&nbsp;&nbsp;<span style="font-size:1.4em; font-weight:bold; margin-left:100px">From: <?php echo $first_date; ?> - <?php echo $last_date; ?></span>
</div>
<br/>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
        <th align="left" valign="top" style="border-bottom:1px solid black">
                <strong>Case</strong>
        </th>
        <th align="left" valign="top" style="border-bottom:1px solid black">
                <strong>Defendant</strong>
        </th>
        <th align="left" valign="top" style="border-bottom:1px solid black">
                <strong>File #</strong>
        </th>
        <th align="left" valign="top" style="border-bottom:1px solid black">
                <strong>Atty</strong>
        </th>
        <th align="left" valign="top" style="border-bottom:1px solid black">
                <strong>DOI</strong>
        </th>
        <th align="left" valign="top" style="border-bottom:1px solid black">
                <strong>SOL</strong>
        </th>
    </tr>
    <?php echo implode("", $arrRows); ?>
</table>
<?php	
}
?>
<script type="text/javascript">
window.onload = function() { window.print(); }
</script>