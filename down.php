<?php 
error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
  $application_logo = "logo-starlinkcms.png";
}
else
{
  $application_logo = "ikase_logo_login.png";
}

//get the prefix if any from the customer id
include ("text_editor/ed/functions.php");
include ("text_editor/ed/datacon.php");


$boolAllowMultipleDownload = true;	//download as many times as you wish until expiration date
if(!empty($_GET['key'])){
	//check the DB for the key
	$sql = "SELECT * FROM ikase.cse_downloads WHERE downloadkey = ? LIMIT 1";
    $arrCheck = DB::run($sql, [$_GET['key']])->fetch();
	//die(print_r($arrCheck));
	if(strtotime($arrCheck['expires'])>=time()){
		if(!$arrCheck['downloads'] OR $boolAllowMultipleDownload){
			//move through
			//update the DB to say this file has been downloaded
			DB::update('ikase.cse_downloads', ['downloads' => 'downloads + 1'], ['downloadkey' => $_GET['key']], true);
		} else {
			//this file has already been downloaded and multiple downloads are not allowed
			die( "This file has already been downloaded.");
		}
	} else {
		//this download has passed its expiry date
		die("This download has expired.");
	}
} else {
	die();
}
//die(print_r($arrCheck));

$injury_id = $arrCheck["injury_id"];
$customer_id = $arrCheck["customer_id"];

if (!is_numeric($injury_id)) {
	die();
}
if (!is_numeric($customer_id)) {
	die();
}

if (strpos($arrCheck["file"], "refervocational") !== false) {
	$path = $arrCheck["file"];
	$path = str_replace("../uploads", "uploads", $path);
	//die($path);
	$filename = explode("/", $path);
	$filename = $filename[count($filename) - 1];
	//die("filename:" . $filename);
	header('Content-Transfer-Encoding: binary');  // For Gecko browsers mainly
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');
	header('Accept-Ranges: bytes');  // Allow support for download resume
	header('Content-Length: ' . filesize($path));  // File size
	header('Content-Encoding: none');
	header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');  // Change the mime type if the file is not PDF
	header('Content-Disposition: attachment; filename=' . $filename);  // Make the browser display the Save As dialog
	readfile($path);  // This is necessary in order to get it to actually download the file, otherwise it will be 0Kb

	die();
}
$sql_customer = "SELECT cus_name, data_source, permissions, CONCAT(cus.cus_street, '<br> ', cus.cus_city, ', ', cus.cus_state, ' ', cus.cus_zip) customer_address
FROM  `ikase`.`cse_customer` cus
WHERE customer_id = " . $customer_id;
//echo $sql_customer . "<br>";
$db = "ikase";
$result = DB::runOrDie($sql_customer);
if ($result->rowCount() > 0) {
    $row         = $result->fetch();
    $cus_name    = $row->cus_name;
    $data_source = $row->data_source;
    if ($data_source != "") {
        $db .= "_".$row->data_source;
    }
}

$query = "SELECT pers.*, ccase.*, ccpers.*, cinj.full_address injury_full_address, cinj.start_date, cinj.end_date, cinj.explanation, cinj.occupation, cven.venue_abbr case_venue, 
employer.company_name employer , employer.full_address employer_address, ccase.adj_number, employer.employee_phone employer_phone, employer.employee_fax employer_fax, employer.email employer_email, employer.company_site employer_site
FROM `" . $db . "`.`cse_case` ccase
INNER JOIN `" . $db . "`.`cse_case_person` ccpers
ON ccase.case_uuid = ccpers.case_uuid
INNER JOIN `" . $db . "`.`cse_person` pers 
ON ccpers.person_uuid = pers.person_uuid
LEFT OUTER JOIN `" . $db . "`.`cse_case_corporation` ccorp
ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
LEFT OUTER JOIN `" . $db . "`.`cse_corporation` `employer`
ON ccorp.corporation_uuid = employer.corporation_uuid
LEFT OUTER JOIN `" . $db . "`.`cse_case_venue` ccven 
ON ccpers.case_uuid = ccven.case_uuid
LEFT OUTER JOIN `ikase`.`cse_venue` cven
ON ccven.venue_uuid = cven.venue_uuid
INNER JOIN `" . $db . "`.`cse_case_injury` ccinj 
ON ccase.case_uuid = ccinj.case_uuid
INNER JOIN `" . $db . "`.`cse_injury` cinj 
ON ccinj.injury_uuid = cinj.injury_uuid
WHERE cinj.injury_id = '" . $injury_id . "'
AND ccase.customer_id = " . $customer_id . "
ORDER BY injury_id DESC LIMIT 1";

while ($row = DB::runOrDie($query)->fetch()) {
	$arrEmployerComm = array();
	$arrComm = array();
	$person_id = $row->person_id;
	
	//die("did:" . $person_id);
	$case_id = $row->case_id;
	$case_status = $row->case_status;
	$person_uuid = $row->person_uuid;
	$full_name = $row->full_name;
	$language = $row->language;
	$ssn = $row->ssn;
	$employer = $row->employer;
	$employer_address = $row->employer_address;
	$employer_phone = $row->employer_phone;
	$employer_fax = $row->employer_fax;
	$employer_site = $row->employer_site;
	$employer_email = $row->employer_email;
	if ($employer_phone!=""){
		$arrEmployerComm[] = "Phone: " . $employer_phone;
	}
	if ($employer_fax!=""){
		$arrEmployerComm[] = "Fax: " . $employer_fax;
	}
	if ($employer_site!=""){
		$arrEmployerComm[] = 'Website: <a href="http://' . str_replace("http://", "", $employer_site) . '" target="_blank">' . $employer_site . '</a>';
	}
	if ($employer_email!=""){
		$arrEmployerComm[] = "Email: <a href='mailto:" . $employer_email . "'>" . $employer_email . "</a>";
	}
	$intake_date = $row->submittedOn;
	$first_name = $row->first_name;
	$last_name = $row->last_name;
	$aka = $row->aka;
	$preferred_name = $row->preferred_name;
	$full_address = $row->full_address;
	$street = $row->street;
	$city = $row->city;
	$state = $row->state;
	$phone = $row->phone;
	if ($phone!="") {
		$arrComm[] = "Phone: " . $phone;
	}
	$email = $row->email;
	if ($email!="") {
		$arrComm[] = "Email: <a href='mailto:" . $email . "'>" . $email . "</a>";
	}
	$fax = $row->fax;
	if ($fax!="") {
		$arrComm[] = "Fax: " . $fax;
	}
	$work_phone = $row->work_phone;
	if ($work_phone!="") {
		$arrComm[] = "Work: " . $work_phone;
	}
	$cell_phone = $row->cell_phone;
	if ($cell_phone!="") {
		$arrComm[] = "Cell: " . $cell_phone;
	}
	$work_email = $row->work_email;
	$ssn_last_four = $row->ssn_last_four;
	$dob = $row->dob;
	$license_number = $row->license_number;
	$title = $row->title;
	$venue = $row->case_venue;
	
	$case_number = $row->case_number;
	$attorney = $row->attorney;
	$worker = $row->worker;
		
	//die($attorney . " - attorney_id");
	if ($attorney != "") {
		if (is_numeric($attorney)) {
			$query_att = "SELECT user_id, user_first_name, user_last_name 
						  FROM ikase.cse_user
						  WHERE user_id = " . $attorney;
			$result_att = DB::runOrDie($query_att);

			while ($row = $result_att->fetch()) {
				$att_first_name = $row->user_first_name;
				$att_last_name = $row->user_last_name;
			}
			$attorney_name = $att_first_name . " " . $att_last_name;
		} else {
			$attorney_name = $attorney;
		}
	}
	if ($worker != "") {
		if (is_numeric($worker)) {
			$query_work = "SELECT * 
					  FROM ikase.cse_user
					  WHERE customer_id = " . $customer_id . "
					  AND user_id = " . $worker;
		} else {
			$query_work = "SELECT * 
					  FROM ikase.cse_user
					  WHERE customer_id = " . $customer_id . "
					  AND nickname = '" . $worker . "'";
		}
		$result_work = DB::runOrDie($query_work);

		while ($row = $result_work->fetch()) {
			$work_first_name = $row->user_first_name;
			$work_last_name = $row->user_last_name;
		}
		$worker_name = $work_first_name . " " . $work_last_name;
	}

	
	$occupation = $row->occupation;
	$start_date = $row->start_date;
	$end_date = $row->end_date;
	$injury_location = $row->injury_full_address;
	//$age = $row->age;
	$age = "";
//	if ($age==0) {
		if (validateDate($dob)) {
			$age = age(date("m/d/Y", strtotime($dob)));
		}
//	}
	$explanation = $row->explanation;
	$adj_number = $row->adj_number;
	
	$arrLanguageOccupation = array();
	if ($language!="") {
		$arrLanguageOccupation[] = "Language: " . $language;
	}
	if ($occupation!="") {
		$arrLanguageOccupation[] = "Occupation: " . $occupation;
	}
}

$query_bod = "SELECT DISTINCT bp.*, cib.attribute bodyparts_number, ccase.case_id, ccase.case_uuid 
			FROM `" . $db . "`.`cse_bodyparts` bp
			INNER JOIN `" . $db . "`.cse_injury_bodyparts cib
			ON bp.bodyparts_uuid = cib.bodyparts_uuid
			INNER JOIN `" . $db . "`.cse_injury ci
			ON (cib.injury_uuid = ci.injury_uuid)
			INNER JOIN `" . $db . "`.cse_case_injury cci
			ON ci.injury_uuid = cci.injury_uuid
			INNER JOIN `" . $db . "`.cse_case ccase
			ON (cci.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = '" . $case_id . "')
			WHERE 1
			AND cci.customer_id = " . $customer_id . "
			AND cci.deleted = 'N'
			AND cib.deleted = 'N'
			ORDER BY `code` ASC";
while ($row = DB::runOrDie($query_bod)->fetch()) {
    $arrBodInfo[] = ["code" => $row->code, "description" => $row->description];
}

$sol_date = new DateTime("+12 months $intake_date");
//die($sol_date->format('Y-m-d') . "\n");

//injuries
$sql = "SELECT `inj`.`injury_id`, `inj`.`injury_uuid`, `injury_number`, `inj`.`adj_number`, `inj`.`type`, `occupation`, `start_date`, `end_date`, `ct_dates_note`, `body_parts`, `statute_limitation`, `explanation`, `inj`.`full_address`, `inj`.`street`, `inj`.`city`, `inj`.`state`, `inj`.`zip`, `inj`.`suite`, `inj`.`customer_id`, `inj`.`deleted`
FROM `" . $db . "`.`cse_injury` inj 
INNER JOIN `" . $db . "`.cse_case_injury ccinj
ON inj.injury_uuid = ccinj.injury_uuid
INNER JOIN `" . $db . "`.cse_case ccase
ON (ccinj.case_uuid = ccase.case_uuid";
//$sql .= " AND `ccase`.`case_id` = '" . $case_id . "')";
$sql .= " AND `inj`.`injury_id` = '" . $injury_id . "')";
$sql .= " WHERE 1
AND inj.customer_id = " . $customer_id . "
AND ccase.deleted = 'N'
AND inj.deleted = 'N'";

$result_inj = DB::runOrDie($sql);
$arrInjuries = array();
while ($row = $result_inj->fetch()) {
	$injury_id = $row->injury_id;
	$adj_number = $row->adj_number;
	$start_date = $row->start_date;
	$end_date = $row->end_date;
	$injury_location = $row->full_address;
	$explanation = $row->explanation;
	$end_date = $row->end_date;
	$ct = date("m/d/Y", strtotime($start_date));
	if ($end_date!="0000-00-00") {
		$ct .= " - " . date("m/d/Y", strtotime($end_date)) . " CT";
	}
	$arrInjuries[$adj_number] = array("ct"=>$ct, "explanation"=>$explanation, "location"=>$injury_location);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
td {
	color:#000000;
	line-height:95%;
}
.highlight {
	 background:#9FF
}
.applicant_img {
	max-width:128px;
	max-height:128px;
}
</style>
<title>Demographics Report</title>
<script language="javascript">
var openSendForm = function() {
	var picture_holder = document.getElementById("picture_holder");
	if (typeof picture_holder != "undefined" && picture_holder != null) {
		picture_holder.style.display = "none";
	}
	document.getElementById("form_holder").style.display = "";
}
</script>
</head>

<body style="color:#EDEDED;">
<table width="900" border="0" align="center" cellpadding="3" cellspacing="0" style="margin-top:0px">
  <tr>
  	<td width="20%" valign="top"><img src="https://<?php echo $_SERVER['SERVER_NAME'];?>/img/<?php echo $application_logo; ?>" height="40" /></td>
    <td colspan="3" align="left" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">
    	Referral from <?php echo $cus_name; ?>
    </td>
  </tr>
  <tr>
    <td colspan="4">&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Case Number</strong></td>
    <td colspan="3"><?php echo $case_number; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Case Name</strong></td>
    <td colspan="3"><?php echo $first_name . " " . $last_name . " vs " . $employer; ?></td>
  </tr>
   <tr>
    <td valign="top" nowrap><strong>Case Status</strong></td>
    <td width="35%"><?php echo $case_status; ?></td>
    <td width="40%">&nbsp;</td>
    <td width="30%">&nbsp;</td>
  </tr>
	<tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <tr>
    <td valign="top" nowrap class="highlight"><strong>Applicant</strong></td>
    <td colspan="3" class="highlight"><?php echo $first_name . " " . $last_name; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $full_address; ?></td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php for($bodyindex=0; $bodyindex < 5; $bodyindex++) { ?>
  <tr>
    <td valign="top" nowrap ><?php if ($bodyindex==0) { ?><strong>Body Parts</strong><?php } ?></td>
    <td colspan="2">
		<?php if (isset($arrBodInfo[$bodyindex])) { ?>
		<?php echo $arrBodInfo[$bodyindex]["code"]; ?>&nbsp; - &nbsp;<?php echo $arrBodInfo[$bodyindex]["description"]; ?>
        <?php } ?>    </td>
    <td nowrap>
		<?php if (isset($arrBodInfo[$bodyindex + 5])) { ?>
        <?php echo $arrBodInfo[$bodyindex + 5]["code"]; ?>&nbsp; - &nbsp;<?php echo $arrBodInfo[$bodyindex + 5]["description"]; ?>
        <?php } ?>    </td>
  </tr>
  <?php } ?>
  <tr valign="top">
    <td colspan="4"><hr/></td>
  </tr>
</table>
</body>
</html>
