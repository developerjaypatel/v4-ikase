<?php
echo $_SERVER['REMOTE_ADDR'] . "<br>";
die(print_r($_SERVER));
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}
//owners (and administrators?) are redirected
if ($_SESSION['user_customer_id']==-1 && $_SESSION['user_role']=="owner") {
	header("location:../manage/customers/");
	die();
}

include("api/connection.php");
//include ("text_editor/ed/functions.php");
//include ("text_editor/ed/datacon.php");
include("browser_detect.php");

//$blnIPad = isPad();
$blnIPad = false;
$db = getConnection();
//get the list of adhocs
$adhoc_settings = array();
$sql = "SELECT `adhoc_id`, `adhoc_uuid`, `adhoc`, `type`, `acceptable_values`, `default_value`, `format`, `deleted` 
FROM `cse_adhoc` 
WHERE 1
ORDER BY adhoc ASC";

try {
	
	$stmt = $db->query($sql);
	$adhoc_settings = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

//get customer settings
/*
$sql = "SELECT setting, setting_value 
	FROM cse_customer_setting cset
	INNER JOIN cse_customer cus
	ON cset.customer_uuid = cus.customer_uuid
	WHERE 1 
	AND cus.customer_id = " . $_SESSION['user_customer_id'];
*/
$sql = "SELECT cs.*, cs.setting_id id, cs.setting_uuid uuid
			FROM  `cse_setting` cs
			INNER JOIN `cse_setting_customer` csc
			ON cs.setting_uuid = csc.setting_uuid
			WHERE 1
			AND `csc`.customer_uuid = '" . $_SESSION['user_customer_id'] . "'
			AND `cs`.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER BY cs.`category`";
try {

	if ($_SERVER['REMOTE_ADDR']=='71.116.242.3') {
	//	die($sql . "<br>");
	}

	$stmt = $db->query($sql);
	$customer_settings = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}
foreach($customer_settings as $setting_info) {
	$setting = $setting_info->setting;
	$setting_value = $setting_info->setting_value;
	$arrSettings[$setting] = $setting_value;
}

//basic defaults
if (!isset($arrSettings["case_number_prefix"])) {
	$arrSettings["case_number_prefix"] = "";
}
if (!isset($arrSettings["case_number_next"])) {
	$arrSettings["case_number_next"] = 1000;
	
	//update the setting so we don't have to do this again
	$table_uuid = uniqid("KS", false);
	$table_name = "setting";
	$arrFields = array("category", "setting", "setting_value");
	$arrSet = array("case_number", "case_number_next", 1000);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `" . $table_name . "_uuid`, `" . implode("`,`", $arrFields) . "`) 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $table_uuid . "', '" . implode("','", $arrSet) . "')";
	
	try { 
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		$table_attribute = "main";
		
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the applicant to the case 
		$sql = "INSERT INTO cse_" . $table_name . "_customer (`setting_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_uuid`, `customer_id`)
		VALUES ('" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
				
		//trackNote("insert", $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

//partie type info
$sql = "SELECT * 
FROM `cse_partie_type` 
WHERE 1
ORDER BY blurb ASC";
try {
	
	$stmt = $db->query($sql);
	$partie_settings = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

//workers/users
$sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.status, `user`.mru_number, `user`.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, `user`.user_id id, `user`.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job
		FROM `cse_user` user 
		LEFT OUTER JOIN `cse_user_job` cjob
		ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
		LEFT OUTER JOIN `cse_job` job
		ON cjob.job_uuid = job.job_uuid
		WHERE user.deleted = 'N'
		AND user.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER by user.user_id";
try {
	
	$stmt = $db->query($sql);
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

//attorneys
$sql = "SELECT * FROM `cse_attorney`";	
$sql .= " WHERE 1
AND deleted = 'N'
AND customer_id = " . $_SESSION['user_customer_id'] . "
ORDER by firm_name";
try {
	
	$stmt = $db->prepare($sql);
	//$stmt->bindParam("search_term", $search_term);
	$stmt->execute();
	$attorneys = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//eams carriers
$sql = "SELECT `carrier_id`, `eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, 
`phone`, `service_method`, `last_update`, `last_import_date`, `carrier_id` `id`, `carrier_uuid` `uuid`
FROM `cse_eams_carriers` ";
$sql .= " ORDER by firm_name";
try {
	
	$stmt = $db->prepare($sql);
	//$stmt->bindParam("search_term", $search_term);
	$stmt->execute();
	$eams_carriers = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//eams attorneys
$sql = "SELECT `rep_id`, `eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, `phone`, `service_method`, `last_update`, `rep_id` `id`, `rep_uuid` `uuid`
FROM `cse_eams_reps` ";
$sql .= " ORDER by firm_name";
try {
	
	$stmt = $db->prepare($sql);
	//$stmt->bindParam("search_term", $search_term);
	$stmt->execute();
	$eams_reps = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//medical specialties
$sql = "SELECT `specialty_id`, `specialty`, `description` FROM `cse_specialties` WHERE 1";
try {
	
	$stmt = $db->query($sql);
	$specialties = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//venues
$sql = "SELECT * FROM `cse_venue` 
WHERE 1
ORDER BY venue ASC";
try {
	
	$stmt = $db->query($sql);
	$venues = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
//kases
$sql = "SELECT DISTINCT 
			inj.injury_id id, ccase.case_id, inj.injury_number, ccase.case_uuid uuid, ccase.case_number, ccase.source, inj.injury_number, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) case_date , 
			ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.submittedOn, ccase.attorney, ccase.worker, 
			app.person_id applicant_id, app.person_uuid applicant_uuid,
			IF (app.first_name IS NULL, '', app.first_name) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, app.full_name, app.language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob,
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn,
			employer.`corporation_id` employer_id, employer.`corporation_uuid` employer_uuid, employer.`company_name` employer, 
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, 
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`,' - ', IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y'))) `name`, att.user_id attorney_id, user.user_id, att.nickname as attorney_name, att.user_name as attorney_full_name, user.nickname as worker_name, user.user_name as worker_full_name
			FROM cse_case ccase
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N' AND cinj.deleted = 'N'
			LEFT OUTER JOIN `cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN `cse_user` user
			ON ccase.worker = user.user_id
			WHERE ccase.deleted ='N' 
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by ccase.case_id, inj.injury_number
			LIMIT 0, 1000";
//die($sql);
try {
	
	$stmt = $db->query($sql);
	$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//recent kases
$sql = "SELECT DISTINCT 
		inj.injury_id id, ccase.case_id, inj.injury_number, ccase.case_uuid uuid, ccase.case_number, inj.injury_number, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) case_date , 
			ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.submittedOn, ccase.attorney, ccase.worker, 
			app.person_id applicant_id, app.person_uuid applicant_uuid,
			IF (app.first_name IS NULL, '', app.first_name) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, app.full_name, app.language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn,
			employer.`corporation_id` employer_id, employer.`corporation_uuid` employer_uuid, employer.`company_name` employer, 
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, 
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`,' - ', IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y'))) `name`
		FROM cse_case ccase

INNER JOIN (
SELECT cct.case_id, MAX( time_stamp ) time_stamp
FROM  `cse_case_track` cct
INNER JOIN cse_case ccase ON cct.case_id = ccase.case_id
WHERE operation =  'view'
AND user_uuid =  '" . $_SESSION['user_id'] . "'
AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
AND ccase.deleted =  'N'
GROUP BY cct.case_id
ORDER BY MAX( time_stamp ) DESC 
LIMIT 0 , 5
) recent
ON ccase.case_id = recent.case_id
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N' AND cinj.deleted = 'N'
		WHERE ccase.deleted ='N' 
		AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
		AND app.person_uuid IS NOT NULL
		ORDER by recent.time_stamp DESC";
//die($sql);
try {
	
	$stmt = $db->query($sql);
	$recent_kases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$sql = "SELECT * FROM `cse_task` 
		WHERE customer_id =  " . $_SESSION['user_customer_id'] . "
		AND deleted = 'N'
		ORDER BY task_id DESC";
try {
	$stmt = $db->query($sql);
	$recent_tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

$sql = "SELECT inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id
		FROM `cse_injury` inj 
		INNER JOIN cse_case_injury ccinj
		ON inj.injury_uuid = ccinj.injury_uuid
		INNER JOIN cse_case ccase
		ON (ccinj.case_uuid = ccase.case_uuid";
		$sql .= " )";
$sql .= " WHERE 1
		AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ccase.deleted = 'N'
		AND inj.deleted = 'N'";
//die($sql);
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$dois = $stmt->fetchAll(PDO::FETCH_OBJ);
			
	$db = null;
	//die($injury);   

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
$db = null;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="img/favicon.png">

    <title>iKase - Legal Case Management System. Fast, Mobile, Offline</title>

    
    <link rel="stylesheet" type="text/css" href="css/offline-theme-chrome.css" />
    <link rel="stylesheet" type="text/css" href="css/uploadifive.css">
    
    <link rel='stylesheet' type='text/css' href='css/jquery.gridster.css' />
    <link rel="stylesheet" type="text/css" href="css/local_gridster.css" />
    <link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-eams.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-facebook.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-chat.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-event.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-facebook.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-kase.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-message.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-person.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-task.css" />
    <link rel='stylesheet' type='text/css' href='css/fullcalendar.css' />
    
	
    <!-- Bootstrap core CSS -->
    <!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="css/bootstrap.3.0.3.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="css/bootstrap-theme.min.css">

    <!-- Custom styles for this template -->
    <link href="css/sticky-footer-navbar.css" rel="stylesheet">
	<link rel='stylesheet' type='text/css' href='css/jquery-ui-1.8.13.custom.css' />
    
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <link href="css/tablesorter_blue.css" rel="stylesheet">
    <link href="text_editor/jquery-te-1.4.0.css" rel="stylesheet">
    
    <link href="cleditor/jquery.cleditor.css" rel="stylesheet">
    
    <!--fonts-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Duru+Sans' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:200,400' rel='stylesheet' type='text/css'>
    <link type="text/css" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">
    <link rel="stylesheet" type="text/css" href="css/jquery.timepicker.css">
    <link rel="stylesheet" href="css/jquery-ui.css">
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_reps.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_attorney.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_kase.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_worker.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_worker_event.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_specialty.css' />
    <link rel="stylesheet" href="multilookup/styles/token-input.css" type="text/css" />    
    <link href="fonts/fontello-a1b266d9/css/fontawesome.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-codes.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-embedded.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-ie7-codes.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-ie7.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/animation.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet">
    
  </head>
  <style type="text/css">
	.modal-dialog {
		margin: 0;
		position: absolute;
		top: 50%;
		left: 50%;	
	}
	
	.modal-body {
		overflow-y: auto;
		overflow-y: hidden;
	}
	.modal-footer {
		margin-top: 0;
		background:url(../img/glass.png);
	}
	
	@media (max-width: 767px) {
	  .modal-dialog {
		width: 100%;
	  }
	}
	</style>
  <body>

    <!-- Wrap all page content here -->
    <div id="wrap">

      <!-- Fixed navbar -->
      <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container kase_header">
        </div>
      </div>
      <div style="width:100%; margin-left:auto; margin-right:auto; text-align:center; display:none" class="large_white_text" id="page_title"></div>
      <!-- Begin page content -->
      <div class="container kase_body">
      		<a id="left_side_show" style="cursor:pointer; display:none; position:absolute; left:5px; top:70px" onClick="showLeftSide();">
            <i style="font-size:1.5em;color:#FFFFFF" class="glyphicon glyphicon-chevron-right" title="Click to show Recent kases"></i></a>
            <a id="left_side_hide" style="cursor:pointer; display:none; position:absolute; left:5px; top:70px" onClick="hideLeftSide();">
            <i style="font-size:1.5em;color:#FFFFFF" class="glyphicon glyphicon-chevron-left" title="Click to hide Recent kases"></i></a>
      		<div id="left_sidebar" class="col-md-2 sidebar left_sidebar"></div>         
	        <div id="search_results" class="col-md-10"></div>
            <div id="content" class="col-md-10 kase_content"></div>
      </div>
    </div>
    
    <div id="footer" style="display:none">
        <div class="container">
	        <p class="text-muted">Place sticky footer content here.</p>
        </div>
    </div>
    
    <!-- Modal -->
  <div class="modal fade" id="myModal4" tabindex="-1" role="dialog" aria-labelledby="myModal4Label" aria-hidden="true" style="">
    <div class="modal-dialog" style="">
        <div class="modal-content">
          <div class="modal-header">
          	<input type="hidden" id="modal_type" value="">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <div id="modal_save_holder" style="float:right"></div>
            <div id="gifsave" style="float:right; display:none">
            	<i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>
            </div>
            <h4 class="modal-title" id="myModalLabel" style="color:#FFFFFF;">Modal title</h4>
          </div>
          <div class="modal-body" id="myModalBody" style="color:#FFFFFF;">
          <i class="icon-spin4 animate-spin"></i></div>
          <div class="modal-footer" style="color:#FFFFFF; display:none">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="interoffice btn btn-primary save" onClick="saveModal()">Save changes</button>
            <div style="float:left" id="apply_notes_holder">
            	<input type="checkbox" id="apply_notes">&nbsp;Apply to Notes
            </div>
          </div>
        </div>
      </div>
    <!-- /.modal-dialog -->
  </div><!-- /.modal -->
	<!--main dependencies-->
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>
    <script type="text/javascript" src="lib/underscore-min.js"></script>
    <script type="text/javascript" src="lib/backbone.js"></script>
    
    <!--widgets-->
    <script async type="text/javascript" src="lib/jquery.uploadifive.min.js"></script>
    <script type="text/javascript" src="lib/fullcalendar.js"></script>
    <script type="text/javascript" src="lib/jquery.tablesorter.js"></script> 
    <script type="text/javascript" src="lib/list.js"></script>
    <script type="text/javascript" src="lib/list.fuzzysearch.js"></script>
    <script type="text/javascript" src="lib/jquery.gridster.js"></script> 
    <script type="text/javascript" src="lib/jquery.tokeninput.js"></script>
    <script async type="text/javascript" src="lib/jquery.datetimepicker.js"></script>
    <script async type="text/javascript" src="lib/jquery.timepicker.js"></script>
    <script async type="text/javascript" src="lib/zipLookup.min.js"></script>
    <!--autocomplete-->
    <script async type="text/javascript" src="lib/backbone.autocomplete.js"></script>
    <script async type="text/javascript" src="lib/backbone.autocomplete_attorney.js"></script>
    <script async type="text/javascript" src="lib/backbone.autocomplete_kase.js"></script>
    <script async type="text/javascript" src="lib/backbone.autocomplete_worker.js"></script>
	<script async type="text/javascript" src="lib/backbone.autocomplete_reps.js"></script>    
    <script async type="text/javascript" src="lib/backbone.autocomplete_specialty.js"></script>
    
    <!--general utilities-->
    <script async type="text/javascript" src="lib/jquery.ui.touch-punch.js"></script>
    
    <script src="js/bootstrap-tab.js"></script>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Latest compiled and minified JavaScript 
	<script type="text/javascript" src="lib/offline.min.js"></script>-->
    <script type="text/javascript" src="lib/moment.min.js"></script> 
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script type="text/javascript" src="multilookup/src/jquery.tokeninput.js"></script>
    
    <script type="text/javascript" src="cleditor/jquery.cleditor.js"></script>
    
    <!--load templates-->
    <script src="js/utilities.js"></script>
    <script async src="js/mask_phone.js"></script>
    
    <!--cookies-->
    <script src="js/cookies.js"></script>
    <script src="js/md5.js"></script>
    
    <!--models for data access-->
	<script src="js/models/applicantmodel.js"></script>
    <script src="js/models/bodypartsmodel.js"></script>    
	<script src="js/models/chatsmodel.js"></script>
	<script src="js/models/corporationmodel.js"></script>
    <script src="js/models/customersettingmodel.js"></script>
	<script src="js/models/documentmodel.js"></script>
	<script src="js/models/employeemodel.js"></script>
	<script src="js/models/emailmodel.js"></script>
    <script src="js/models/eams_carriers_model.js"></script>
    <script src="js/models/eams_reps_model.js"></script>
    <script src="js/models/attorney_search_model.js"></script>
    <script src="js/models/worker_search_model.js"></script>
    <script src="js/models/specialty_model.js"></script>
	<script src="js/models/eventmodel.js"></script>
	<script src="js/models/injurymodel.js"></script>
    <script src="js/models/injurynumbermodel.js"></script>
	<script src="js/models/kasemodel.js"></script>
    <script src="js/models/messagesmodel.js"></script>
	<script src="js/models/formsmodel.js"></script>
    <script src="js/models/newnotemodel.js"></script>
	<script src="js/models/notemodel.js"></script>
	<script src="js/models/partiemodel.js"></script>
    <script src="js/models/personmodel.js"></script>
    <script src="js/models/rolodexmodel.js"></script>
    <script src="js/models/remindermodel.js"></script>
    <script src="js/models/signaturemodel.js"></script>
    <script src="js/models/tasksmodel.js"></script>
	<script src="js/models/usermodel.js"></script>
    <script src="js/models/usersettingmodel.js"></script>
    <!--views-->
    <script src="js/views/event_listing.js"></script>
    <script src="js/views/kase_nav_bar.js"></script>
    <script src="js/views/kase_nav_left.js"></script>
    <script src="js/views/kase_list_category.js"></script>
    <script src="js/views/kase_home.js"></script>
	<script src="js/views/kase_listing.js"></script>
    <script src="js/views/partie_listing.js"></script>
    
	<script src="js/views/kase_event_list.js"></script>
    <script src="js/views/kase_details.js"></script>
    <script src="js/views/kase_occurences.js"></script>
	<script src="js/views/kase_control_panel.js"></script>
    
	<script src="js/views/applicant_details.js"></script>
    <script src="js/views/kai_details.js"></script>
    <script src="js/views/parties_details.js"></script>
    
    <script src="js/views/chat_details.js"></script>
	<script src="js/views/customer_setting_listing.js"></script>
    <script src="js/views/bodyparts_details.js"></script>    
	<script src="js/views/dashboard_view.js"></script>
    <script src="js/views/dashboard_home_view.js"></script>
	<script src="js/views/dashboard_injury_view.js"></script>
    <script src="js/views/dashboard_person_view.js"></script>
    <script src="js/views/dashboard_user_view.js"></script>
	<script src="js/views/dialog_details.js"></script>
    <script src="js/views/document_details.js"></script>    
    <script src="js/views/document_import.js"></script>
    <script src="js/views/eams_forms_view.js"></script>        
    <script src="js/views/event_details.js"></script>        
    <script src="js/views/message_attach.js"></script>
	<script src="js/views/messages_details.js"></script>
    <script src="js/views/message_listing.js"></script>
	<script src="js/views/forms_listing.js"></script>
    <script src="js/views/new_note_details.js"></script>
    <script src="js/views/email_details.js"></script>
    <script src="js/views/injury_details.js"></script>
    <script src="js/views/injury_number_details.js"></script>    
    <script src="js/views/interoffice_details.js"></script>
    <script src="js/views/letter_attach.js"></script>
	<script src="js/views/letters_details.js"></script>
    <script src="js/views/kase_list_task.js"></script>
	<script src="js/views/partie_cards.js"></script>
    <script src="js/views/person_details.js"></script>    
	<script src="js/views/notes_details.js"></script>
    <script src="js/views/person_details.js"></script>
    <script src="js/views/rolodex_details.js"></script>
    <script src="js/views/setting_attach.js"></script>
	<script src="js/views/setting_details.js"></script>
	<script src="js/views/signature_details.js"></script>
    <script src="js/views/stacks_details.js"></script>
    <script src="js/views/task_listing.js"></script>
	<script src="js/views/tasks_details.js"></script>
    <script src="js/views/template_listing.js"></script>
    <script src="js/views/template_upload.js"></script>
	<script src="js/views/user_details.js"></script>
    <script src="js/views/user_setting_listing.js"></script>
    <script src="js/views/document_upload.js"></script>
    
    <!--modules-->
    <script src="js/chat_module.js"></script>
    <script src="js/event_module.js"></script>
    <script src="js/kase_module.js"></script>
    <script src="js/phone_message_module.js"></script>
    <script src="js/setting_module.js"></script>
    
    <script async src="text_editor/jquery-te-1.4.0.min.js"></script>
    <!--pagination-->
    <script async type="text/javascript" src="lib/jquery.tablesorter.pager.js"></script>
	
    <!--validation-->
    <script async type="text/javascript" src="lib/parsley.js"></script> 
    
    <script language="javascript" type="text/javascript">
		var appHost = "<?php 
		$script_filename = $_SERVER['SCRIPT_FILENAME'];
		$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
		echo $arrScript[count($arrScript)-1]; 
		?>";
		
		//who is logged in

		var blnAdmin;
		<?php if ($_SESSION['user_role']=="admin" ) { ?>
			blnAdmin = true;
		<?php } ?>
		var customer_id = '<?php echo $_SESSION['user_customer_id']; ?>';
		var customer_name = '<?php echo $_SESSION['user_customer_name']; ?>';
		var login_user_id = '<?php echo $_SESSION['user_plain_id']; ?>';
		var login_username = '<?php echo $_SESSION['user_name']; ?>';
		var login_nickname = '<?php echo $_SESSION['user_nickname']; ?>';
		var blnIE = <?php if ($blnIE) { echo 1; } else { echo 0; } ?>;
		var hrefHost = '<?php echo $_SERVER['HTTP_REFERER']; ?>';
		//bootstrapping background data, some of these need to be moved to indexedDB
       	
		//customer settings
		var customer_settings = new Backbone.Model;
        customer_settings.set(<?php echo json_encode($arrSettings); ?>);
		//adhocs type
        var adhoc_settings = new Backbone.Collection;
        adhoc_settings.reset(<?php echo json_encode($adhoc_settings); ?>);
        //partie settings (color, blurb, etc)
        var partie_settings = new Backbone.Collection;
        partie_settings.reset(<?php echo json_encode($partie_settings); ?>);
		
		//workers
		var worker_searches = new WorkerSearchCollection();
		worker_searches.reset(<?php echo json_encode($users); ?>);
		
		//attorneys
		var attorney_searches = new AttorneySearchCollection();
		attorney_searches.reset(<?php echo json_encode($attorneys); ?>);
		
		//eams carriers
		var eams_carriers = new EamsCarrierCollection();
		eams_carriers.reset(<?php echo json_encode($eams_carriers); ?>);
		
		//eams reps
		var eams_reps = new EamsRepCollection();
		eams_reps.reset(<?php echo json_encode($eams_reps); ?>);
		
		//recent kases
		var recent_kases = new KaseRecentCollection();
		recent_kases.reset(<?php echo json_encode($kases); ?>);
		
		//recent kases
		var recent_tasks = new TaskRecentCollection();
		recent_tasks.reset(<?php echo json_encode($recent_tasks); ?>);
		
		//medical specialties
		var medical_specialties = new SpecialtySearchCollection();
		medical_specialties.reset(<?php echo json_encode($specialties); ?>);
		
		//courts (venues)
		var venues = new Backbone.Collection();
		venues.reset(<?php echo json_encode($venues); ?>);
		
		//kases
		var kases = new KaseCollection();
		kases.reset(<?php echo json_encode($kases); ?>);
		blnKasesFetched = true;
		
		var dois = new InjuryCollection();
		dois.reset(<?php echo json_encode($dois); ?>);
		
		function showLeftSide() {
			$('#left_sidebar').show();
			$("#search_results").removeClass("col-md-12");
			$("#search_results").addClass("col-md-10");
			$("#content").removeClass("col-md-12");
			$("#content").addClass("col-md-10");
			$("#left_side_show").hide();
			$("#left_side_hide").show();
		}
		function hideLeftSide() {
			$("#search_results").removeClass("col-md-10");
			$("#search_results").addClass("col-md-12");
			$("#content").removeClass("col-md-10");
			$("#content").addClass("col-md-12");
			$("#left_side_hide").hide();
			$("#left_side_show").show();
			$('#left_sidebar').hide();
			
		}

    </script>
    
    <!--stored data -->
    <!--<script async language="javascript" src="data/eams_data.js"></script>-->
    
    <!--main app -->    
    <script src="js/app.js"></script>
    
  </body>
</html>
