<?PHP
include("../api/manage_session.php");
include("../api/connection.php");
session_write_close();
$users_id = passed_var("users", "get");
if($users_id == ""){
    die("<span style='font-family: Open Sans, sans-serif; font-size:14px; color:white; font-weight:bold;'>Please add Assignees</span>");
}
$event_id = passed_var("event_id", "get");
$event_date = passed_var("event_date", "get");
$event_type = passed_var("event_type", "get");
// die($event_date);
// $users_id = array(1, 2, 1288);
// die(print_r($users_id));
// $arrUsersId = implode(",", $users_id);
// die(print_r($arrUsersId));
// die(print_r($_SESSION));
//Check for if reminders are stored
$blnNoReminders = false;
$sql = "SELECT ce.`event_id`, ce.event_dateandtime, ce.full_address, ce.event_title, cc.case_name case_stored_name, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, cr.`reminder_id`, cr.`reminder_number`, cr.`reminder_type`, cr.`reminder_interval`, 
        cr.`reminder_datetime`, cm.`message_id`, cm.`message_to`, cm.`message`, 
        IF(cu.`user_id` IS NULL, IF(pers.`person_id` IS NULL, IF(corp.`corporation_id` IS NULL, 'nothing', corp.`corporation_id`), pers.`person_id`), cu.`user_id`) `user_id`, 
        IF(cu.`user_name` IS NULL, IF(pers.`last_name` IS NULL, IF(corp.`company_name` IS NULL or corp.`company_name` = '', corp.`full_name`, corp.`company_name`), CONCAT(pers.`first_name`,' ', pers.`last_name`)), cu.`user_name`)`user_name`, 
        IF(cu.`user_cell` IS NULL, IF(pers.`cell_phone` IS NULL, IF(corp.`phone` IS NULL, 'nothing', corp.`phone`), pers.`cell_phone`), cu.`user_cell`) `user_cell`,
        IF(cu.`user_email` IS NULL, IF(pers.`email` IS NULL, IF(corp.`email` IS NULL, 'nothing', corp.`email`), pers.`email`), cu.`user_email`) `user_email`,
        IF(cmu.`user_type` IS NULL, IF(cmpu.`user_type` IS NULL, IF(cmcu.`user_type` IS NULL, 'nothing', cmcu.`user_type`), cmpu.`user_type`), cmu.`user_type`) `user_type`, 
        IFNull(crs.`remindersent_id`, 0) `remindersent_id`
        FROM cse_event ce
        LEFT OUTER JOIN cse_case_event cce
        ON ce.event_uuid = cce.event_uuid
        LEFT OUTER JOIN cse_case cc
        ON cce.case_uuid = cc.case_uuid
        LEFT OUTER JOIN `cse_case_corporation` ccorp
        ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
        LEFT OUTER JOIN `cse_corporation` employer
        ON ccorp.corporation_uuid = employer.corporation_uuid
        LEFT OUTER JOIN cse_case_person ccapp 
        ON cc.case_uuid = ccapp.case_uuid
        LEFT OUTER JOIN ";
        if ($_SESSION['user_customer_id']==1033) {
            $sql .= "(" . SQL_PERSONX . ")";
        } else {
            $sql .= "cse_person";
        }
        $sql .= " app 
        ON ccapp.person_uuid = app.person_uuid         
        LEFT OUTER JOIN cse_event_reminder cer
        ON ce.`event_uuid` = cer.`event_uuid`
        LEFT OUTER JOIN cse_reminder cr
        ON cer.`reminder_uuid` = cr.`reminder_uuid`
        LEFT OUTER JOIN cse_reminder_message crm
        ON cr.`reminder_uuid` = crm.`reminder_uuid`
        LEFT OUTER JOIN cse_message cm
        ON crm.`message_uuid` = cm.`message_uuid`

        LEFT OUTER JOIN cse_message_user cmu
        ON cm.`message_uuid` = cmu.`message_uuid` AND cmu.user_type = 'user'
        LEFT OUTER JOIN `ikase`.cse_user cu
        ON cmu.`user_uuid` = cu.`user_uuid`

        LEFT OUTER JOIN cse_message_user cmpu
        ON cm.`message_uuid` = cmpu.`message_uuid` AND cmpu.user_type = 'person'
        LEFT OUTER JOIN cse_person pers
        ON cmpu.`user_uuid` = pers.`person_uuid`

        LEFT OUTER JOIN cse_message_user cmcu
        ON cm.`message_uuid` = cmcu.`message_uuid` AND cmcu.user_type = 'corporation'
        LEFT OUTER JOIN cse_corporation corp
        ON cmcu.`user_uuid` = corp.`corporation_uuid`

        LEFT OUTER JOIN cse_remindersent crs
        ON cm.`message_uuid` = crs.`message_uuid` AND cr.`reminder_uuid` = crs.`reminder_uuid`
        WHERE 1
        AND ce.event_id = '" . $event_id . "' AND cr.deleted = 'N' AND ce.customer_id = " . $_SESSION["user_customer_id"];
       
		// die($sql);
try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $reminders = $stmt->fetchAll(PDO::FETCH_OBJ);
    // $reminders = $stmt->fetchObject();
    $db = null; $stmt = null;
    // die(print_r($reminders));

    $event_title = $reminders[0]->event_title;
    $event_dateandtime = $reminders[0]->event_dateandtime;
    $event_location = $reminders[0]->full_address;
    if($reminders[0]->case_name != ""){       
        $case_name = $reminders[0]->case_name;
    } else {
        $case_name = $reminders[0]->case_stored_name;
    }

    if(count($reminders) > 0) {
        foreach ($reminders as $key => $reminder) {
            $reminder->message = str_replace($case_name . "\n" . $event_title . "\n" . date("m/d/y h:iA", strtotime($event_dateandtime)) . "\n" . $event_location, "", $reminder->message);
        }
    }

    // die(print_r($reminders));
    // die(" event title: " . $event_title . " event location: " . $event_location . " event time: " . $event_dateandtime . " case name: " . $case_name);
    if(count($reminders) == 0) {
        // There are no reminders so set it to true
        $blnNoReminders = true;
        
        // Get the Users and applicant side of the case:
        // are we representing plaintff or defendant        
        $strSQL = "SELECT ce.event_title, ce.event_dateandtime, ce.full_address, 
                          cc.injury_type, cc.case_name case_stored_name, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name` 
                   FROM cse_event ce
                   LEFT OUTER JOIN cse_case_event cce
                   ON ce.event_uuid = cce.event_uuid
                   LEFT OUTER JOIN cse_case cc
                   ON cce.case_uuid = cc.case_uuid
                   LEFT OUTER JOIN `cse_case_corporation` ccorp
                   ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
                   LEFT OUTER JOIN `cse_corporation` employer
                   ON ccorp.corporation_uuid = employer.corporation_uuid
                   LEFT OUTER JOIN cse_case_person ccapp 
                   ON cc.case_uuid = ccapp.case_uuid
                   LEFT OUTER JOIN ";
                   if ($_SESSION['user_customer_id']==1033) {
                       $strSQL .= "(" . SQL_PERSONX . ")";
                   } else {
                       $strSQL .= "cse_person";
                   }
                   $strSQL .= " app 
                   ON ccapp.person_uuid = app.person_uuid                   
                   WHERE 1
                   AND ce.event_id = '" . $event_id . "' AND ce.customer_id = " . $_SESSION["user_customer_id"];
        // die($strSQL);
        $db = getConnection();
        $stmt = $db->prepare($strSQL);
        $stmt->execute();
        $injury_type_object = $stmt->fetchObject();
        $stmt->closeCursor();
        $db = null; $stmt = null;
        // die(print_r($injury_type_object));

        $event_title = $injury_type_object->event_title;
        $event_dateandtime = $injury_type_object->event_dateandtime;
        $event_location = $injury_type_object->full_address;
        if($injury_type_object->case_name != ""){       
            $case_name = $injury_type_object->case_name;
        } else {
            $case_name = $injury_type_object->case_stored_name;
        }

        // explode it to ignore the "general|"
        $injury_type = $injury_type_object->injury_type;
        $arrInjuryType = explode("|", $injury_type);
        $user_position = $arrInjuryType[1];
        // die($user_position);

        // die($users_id);
        $arrUsers = explode("][", $users_id);

        //die(print_r($arrUsers));
        if(count($arrUsers) > 1){
            $users_json = $arrUsers[0] . "," . $arrUsers[1];
            $users_id = json_decode($users_json);
        } else {
            $users_json = $arrUsers[0];
            $users_id = json_decode($users_json);
        }        
        // die($users_id);

        $assignees = array();        
        // die("count: " . count($users_id));
        //die(print_r($users_id));
        for ($i=0; $i < count($users_id); $i++) { 
            $user_id = $users_id[$i];
            // die(print_r($user_id));
            $case_attorney = $user_id[1];
			
			if (trim($user_id[1])=="") {
				continue;
			}
            // echo "case attorney: " . $case_attorney . "\r\n";
            if (is_numeric($case_attorney)) {
                $attorney = getUserInfo($case_attorney, $_SESSION["user_customer_id"]);
                // echo "id \r\n";
            } else {
                $attorney = getUserByNickname($case_attorney, $_SESSION["user_customer_id"]);
                // echo "nickname \r\n";
            }

            $assignee = array("user_id"=>$attorney->user_id, "user_name"=>$attorney->user_name, "user_email"=>$attorney->user_email, "user_cell"=>$attorney->user_cell, "type"=>'user', "table"=> 'user');
            $assignees[] = (object)$assignee;
        }
        // $assignees = (object)$assignees;
        // die();
        // die(print_r($assignees));

/*
		//there are no stored reminders, who is assigned?
        $sql = "SELECT `user_id`, `user_name`, `user_email`, `user_cell`, 'user' `table` 
		FROM ikase.`cse_user` 
		WHERE `user_id` IN (" . $users_id . ")";
        
		
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $assignees = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt->closeCursor();
        $db = null; $stmt = null;
		// die(print_r($assignees));
*/    
		//is there an applicant
        $query = "SELECT app.person_id `user_id`, 
					app.full_name `user_name`,
					app.email `user_email`, app.cell_phone `user_cell`
                  FROM cse_event ce
                  LEFT OUTER JOIN cse_case_event cce
                  ON ce.event_uuid = cce.event_uuid
                  LEFT OUTER JOIN cse_case cc
                  ON cce.case_uuid = cc.case_uuid
                  LEFT OUTER JOIN cse_case_person ccapp 
                  ON cc.case_uuid = ccapp.case_uuid
                  LEFT OUTER JOIN cse_person app 
                  ON ccapp.person_uuid = app.person_uuid
                  WHERE 1
                  AND ce.event_id = '" . $event_id . "' AND ce.customer_id = " . $_SESSION["user_customer_id"];

        $db = getConnection();
        $stmt = $db->prepare($query);
        $stmt->execute();
        $applicants = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt->closeCursor();
        $db = null; $stmt = null;
        // die(print_r($applicants));
        if(!is_null($applicants[0]->user_id)) {
            foreach($applicants as &$applicant) {
                $applicant->table = "person";
            } 
            // echo print_r($assignees) . "\r\n";
            // die(print_r($applicants));
			//merge assignes and applicant
        	$users = (object)array_merge((array)$assignees, (array)$applicants);
		} else {
			//no applicant
            // die("no applicants");
			$users = $assignees;
		}

        // Are there corporations
        $str_SQL = "SELECT employer.`corporation_id` `user_id`, IF(employer.`company_name` IS NULL or employer.`company_name` = '', employer.`full_name`, employer.`company_name`) as `user_name`,
                           employer.`email` `user_email`, employer.`phone` `user_cell`, employer.`type`
                    FROM cse_event ce
                    LEFT OUTER JOIN cse_case_event cce
                    ON ce.event_uuid = cce.event_uuid
                    LEFT OUTER JOIN cse_case cc
                    ON cce.case_uuid = cc.case_uuid
                    LEFT OUTER JOIN `cse_case_corporation` ccorp
                    ON (cc.case_uuid = ccorp.case_uuid AND ccorp.deleted = 'N')
                    INNER JOIN `cse_corporation` employer
                    ON ccorp.corporation_uuid = employer.corporation_uuid ";
                    
                    if($user_position == ""){
                        $str_SQL .= "AND (employer.party_type_option IS NOT NULL AND employer.party_type_option != '') ";
                    } else {
                        $str_SQL .= "AND employer.party_type_option = '" . $user_position . "' ";
                    }
                    $str_SQL .= "WHERE 1
                    AND ce.event_id = '" . $event_id . "' AND ce.customer_id = " . $_SESSION["user_customer_id"];   
        // die($str_SQL);
        $db = getConnection();
        $stmt = $db->prepare($str_SQL);
        $stmt->execute();
        $corporations = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt->closeCursor();
        $db = null; $stmt = null;
        // die(print_r($corporations));

        //For corporations
        if(!is_null($corporations[0]->user_id)) {
            foreach($corporations as &$corporation) {
                $corporation->table = "corporation";
            }        
            // die(print_r($corporations));
			//merge assignes and applicant
        	$users = (object)array_merge((array)$users, (array)$corporations);
		} 
    }
    // echo $sql . "\r\n";
    // die(print_r($users));
// echo $event_location . "<br>";
// preg_match('/(?P<number>\d+) (?P<address>\D+) (?P<numberAdd>\D*)/', $event_location, $arrLocation);
// die(print_r($arrLocation));

} catch(PDOException $e) {
    $error = array("error"=> array("text"=>$e->getMessage()));
    echo json_encode($error);
}

$count = 1;
// die(print_r($users[0]));
preg_match('/(?P<number>\d+) (?P<address>\D+) (?P<numberAdd>\D*)/', $event_location, $arrLocation);
// die(print_r($arrLocation));

$number = $arrLocation["number"];
$arrNumber = str_split($number);
// die(print_r($arrNumber) . "&nbsp;count: " . count($arrNumber));
$final_number = "";
$count = count($arrNumber);
for($i = 0; $i < $count; $i++){
    $digit = $arrNumber[$i];
    // echo "digit is " . $digit . "<br>";
    switch ($digit) {
        case 0:
            $final_number .= "zero ";
            break;
        case 1:
            $final_number .= "one ";
            break;    
        case 2:
            $final_number .= "two ";
            break;
        case 3:
            $final_number .= "three ";
            break;                        
        case 4:
            $final_number .= "four ";
            break;
        case 5:
            $final_number .= "five ";
            break;
        case 6:
            $final_number .= "six ";
            break;
        case 7:
            $final_number .= "seven ";
            break;                                                
        case 8:
            $final_number .= "eigth ";
            break;
        case 9:
            $final_number .= "nine ";
            break;   
    }
}
// die($final_number);
$house_number = $final_number;
$arrAddress = explode(",", $arrLocation["address"]);
$address = $arrAddress[0];
$city = $arrAddress[1];
// die("house: " . $house_number . "&nbsp;" . $address . "&nbsp;" . $city);

function getUserInfo($id, $user_customer_id) {
	if (!is_numeric($id)) {
		die();
	}
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, user.user_email,user.user_cell, `user`.`dateandtime`, `user`.`status`, `user`.`personal_calendar`, `user`.`calendar_color`, `user`.access_token, user.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, user.user_id id, user.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job,
			IFNULL(ce.email_name, '') email_name
			FROM ikase.`cse_user` user 
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			LEFT OUTER JOIN cse_user_email cue
			ON user.user_uuid = cue.user_uuid
			LEFT OUTER JOIN cse_email ce
			ON cue.email_uuid = ce.email_uuid
			WHERE user.user_id=:id
			AND user.customer_id = " . $user_customer_id . "";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$user = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $user;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getUserByNickname($nickname, $user_customer_id) {
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, user.user_email,user.user_cell, `user`.`dateandtime`, `user`.`status`, `user`.`personal_calendar`, `user`.`calendar_color`, `user`.access_token, user.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, user.user_id id, user.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job, IFNULL(ce.email_name, '') email_name
			FROM ikase.`cse_user` user 
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			LEFT OUTER JOIN cse_user_email cue
			ON user.user_uuid = cue.user_uuid
			LEFT OUTER JOIN cse_email ce
			ON cue.email_uuid = ce.email_uuid
			WHERE user.nickname=:nickname
			AND user.customer_id = " . $user_customer_id . "";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("nickname", $nickname);
		$stmt->execute();
		$user = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $user;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name=viewport content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?php echo $page_title; ?></title>
<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
<script src="../js/jquery.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/css/base/jquery-ui.css">
<link rel="stylesheet" href="../css/jquery.datetimepicker.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script type="text/javascript" src="../lib/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="../lib/moment.min.js"></script>
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
<style>
body, html {
  height: 100%;
  font-family: 'Open Sans', sans-serif;
  font-size:14px;
  color:white;
  font-weight:bold;
}
</style>
</head>
<body>
<input type="hidden" id="event_id" name="event_id" value="<?php echo $event_id; ?>" />
<input type="hidden" id="event_type" name="event_type" value="<?php echo $event_type; ?>" />
<table>
    <tr>
        <td>            
            <table style="border-spacing:5px; width:95%;">
                <tr>
                    <td>
                        <!--<label style="width:125px; display:inline-block">Reminder Date: </label>-->
                        <input type="hidden" id="event_date" name="event_date" value="<?php echo $event_date; ?>"/>
                        <span style="font-size:1.2em">Default Message:</span>
                        <br>
                        <span style="font-size:.8em">Case Name: <?php echo $case_name; ?></span>
                        <br>
                        <span style="font-size:.8em">Event Time: <?php echo date("m/d/Y h:i:s a", strtotime($event_dateandtime)); ?></span>
                        <br>
                        <span style="font-size:.8em">Event Title: <?php echo $event_title; ?></span>
                        <br>
                        <span style="font-size:.8em">Event Location: <?php echo $event_location; ?></span>
                        <br>
                    </td>   
                    <td valign="top" align="right" style="display:<?php if(!isset($_GET["event_id"])){ echo "none"; } ?>">
                        <input type="submit" id="submit_reminder" name="submit_reminder" value="Set Reminders" />
                        <span id="saving" style="color:green;font-weight:bold;display:none">Saving...</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <hr>
                    </td>
                </tr>                  
<?php 
if($blnNoReminders){
    //Use $users because there are no stored reminders
    // die(print_r($users));
    foreach ($users as $key => $user) { 
        // die(print_r($user));
        $type = $user->type; 
        // die($type);
        $person_type = ""; 
        if($type == "person") { 
            $person_type = "(Applicant)"; 
        } 
        if($type == "witnesses") { 
            $person_type = "(Witness)"; 
        } 
        if($type == "defendant") { 
            $person_type = "(Defendant)"; 
        } 
        if($type == "user") { 
            $person_type = ""; 
        } 
        // die($person_type);
?>
                <form id="reminder_form_<?php echo $count; ?>" class="reminder_form">
                    <tr>
                        <td valign="top">
                            <input type="checkbox" id="case_worker_<?php echo $count; ?>" name="case_worker_<?php echo $count; ?>" class="selected_worker" />&nbsp;<span class="user_name"><?php echo $user->user_name; ?>&nbsp;&nbsp;&nbsp;<?php echo $person_type; ?></span>
                            <input type="hidden" id="case_worker_id_<?php echo $count; ?>" name="case_worker_id_<?php echo $count; ?>" value="<?php echo $user->user_id; ?>" />
                            <input type="hidden" id="case_worker_table_<?php echo $count; ?>" name="case_worker_table_<?php echo $count; ?>" value="<?php echo $user->table; ?>" />
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr id="reminder_row_<?php echo $count; ?>" >
                        <td align="left" valign="top" scope="row" nowrap="nowrap"class="reminder_stuff">
                        	<label style="width:125px; display:inline-block">1st Reminder:</label>
                            <input type="hidden" name="reminder_number_<?php echo $count; ?>_first" id="reminder_number_<?php echo $count; ?>_first" value="1">
                            <select name="reminder_type_<?php echo $count; ?>_first" id="reminder_type_<?php echo $count; ?>_first" class="reminder_field">
                                <option value="">Select One...</option>
                                <?php if($user->user_cell != "") {?><option value="text">Text</option><?php } ?>
                                <?php if($user->user_cell != "") {?><option value="voice">Voice</option><?php } ?>                             
                                <?php if($user->user_email != "") {?><option value="email" selected>Email</option><?php } ?>
<?php if($user->table == "user") { ?>                                
                                <option value="interoffice">Interoffice</option>
                                <option value="popup">Popup</option>                            
<?php } ?>
                            </select>
                            <!--<input type="number" name="reminder_interval_<?php //echo $count; ?>" id="reminder_interval_<?php //echo $count; ?>" min='1' step='1' style="width:40px" value="1" class="reminder_field reminder_interval">--><!--onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')"-->
                            <select name="reminder_interval_<?php echo $count; ?>_first" id="reminder_interval_<?php echo $count; ?>_first" class="reminder_interval">
                                <option value="">Select One...</option>
                                <option value="5">5 Minutes</option>
                                <option value="10">10 Minutes</option>
                                <option value="15">15 Minutes</option>
                                <option value="20">20 Minutes</option>
                                <option value="25">25 Minutes</option>
                                <option value="30">30 Minutes</option>
                                <option value="45">45 Minutes</option>
                                <option value="60">60 Minutes</option>
                                <option value="120">2 Hours</option>
                                <option value="180">3 Hours</option>
                                <option value="240">4 Hours</option>
                                <option value="300">5 Hours</option>
                                <option value="360">6 Hours</option>
                                <option value="420">7 Hours</option>
                                <option value="480">8 Hours</option>
                                <option value="540">9 Hours</option>
                                <option value="600">10 Hours</option>
                                <option value="660">11 Hours</option>
                                <option value="720">12 Hours</option>
                                <option value="1440" selected>1 Day</option>
                                <option value="2880">2 Days</option>
                                <option value="4320">3 Days</option>
                                <option value="5760">4 Days</option>
                                <option value="7200">5 Days</option>
                                <option value="8640">6 Days</option>
                                <option value="10080">1 Week</option>
                                <option value="20160">2 Weeks</option>
                            </select>
                            </br>
                            <span id="reminderspan_datetime_<?php echo $count; ?>_first" style="text-align:left"></span>
                            <input type="hidden" id="reminder_datetime_<?php echo $count; ?>_first" name="reminder_datetime_<?php echo $count; ?>_first" value="" />
                        </td>
                        <td align="left" valign="top" scope="row" nowrap="nowrap"class="reminder_stuff">
                            <label style="width:125px; display:inline-block">2nd Reminder:</label>
                            <input type="hidden" name="reminder_number_<?php echo $count; ?>_second" id="reminder_number_<?php echo $count; ?>_second" value="1">
                            <select name="reminder_type_<?php echo $count; ?>_second" id="reminder_type_<?php echo $count; ?>_second" class="reminder_field">
                                <option value="" >Select One...</option>
                                <?php if($user->user_cell != "") {?><option value="text" selected>Text</option><?php } ?>
                                <?php if($user->user_cell != "") {?><option value="voice">Voice</option><?php } ?>
                                <?php if($user->user_email != "") {?><option value="email">Email</option><?php } ?>
<?php if($user->table == "user") { ?>                                   
                                <option value="interoffice">Interoffice</option>
                                <option value="popup">Popup</option>   
<?php } ?>                                                            
                            </select>
                            <!--<input type="number" name="reminder_interval_<?php //echo $count; ?>_second" id="reminder_interval_<?php //echo $count; ?>_second" min='1' step='1' style="width:40px" value="1" class="reminder_field reminder_interval">--><!--onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')"-->
                            <select name="reminder_interval_<?php echo $count; ?>_second" id="reminder_interval_<?php echo $count; ?>_second" class="reminder_interval">
                                <option value="" >Select One...</option>
                                <option value="5">5 Minutes</option>
                                <option value="10">10 Minutes</option>
                                <option value="15">15 Minutes</option>
                                <option value="20">20 Minutes</option>
                                <option value="25">25 Minutes</option>
                                <option value="30">30 Minutes</option>
                                <option value="45">45 Minutes</option>
                                <option value="60">60 Minutes</option>
                                <option value="120" selected>2 Hours</option>
                                <option value="180">3 Hours</option>
                                <option value="240">4 Hours</option>
                                <option value="300">5 Hours</option>
                                <option value="360">6 Hours</option>
                                <option value="420">7 Hours</option>
                                <option value="480">8 Hours</option>
                                <option value="540">9 Hours</option>
                                <option value="600">10 Hours</option>
                                <option value="660">11 Hours</option>
                                <option value="720">12 Hours</option>
                                <option value="1440">1 Day</option>
                                <option value="2880">2 Days</option>
                                <option value="4320">3 Days</option>
                                <option value="5760">4 Days</option>
                                <option value="7200">5 Days</option>
                                <option value="8640">6 Days</option>
                                <option value="10080">1 Week</option>
                                <option value="20160">2 Weeks</option>
                            </select>
                            </br>
                            <span id="reminderspan_datetime_<?php echo $count; ?>_second" style="text-align:left"></span>
                            <input type="hidden" id="reminder_datetime_<?php echo $count; ?>_second" name="reminder_datetime_<?php echo $count; ?>_second" value="" />
                        </td>
                    </tr>
                    <tr id="message_row_<?php echo $count; ?>">
                        <td valign="top">
                            <input type="checkbox" id="add_message_<?php echo $count; ?>_first" name="add_message_<?php echo $count; ?>_first" class="add_message" /><label for="add_message_<?php echo $count; ?>_first" id="label_worker_<?php echo $count; ?>_first" class="add_message_label">Additional Message</label>
                            <br />
                            <textarea id="message_worker_<?php echo $count; ?>_first" name="message_worker_<?php echo $count; ?>_first" rows="2" cols="50" class="worker_message" disabled></textarea>
                            <br />
                            <span id="message_count_<?php echo $count; ?>_first" class="message_count"></span>
                        </td> 
                        <td valign="top">
                            <input type="checkbox" id="add_message_<?php echo $count; ?>_second" name="add_message_<?php echo $count; ?>_second" class="add_message" /><label for="add_message_<?php echo $count; ?>_second" id="label_worker_<?php echo $count; ?>_second" class="add_message_label">Additional Message</label>
                            <br />
                            <textarea id="message_worker_<?php echo $count; ?>_second" name="message_worker_<?php echo $count; ?>_second" rows="2" cols="50" class="worker_message" disabled></textarea>
                            <br />
                            <span id="message_count_<?php echo $count; ?>_second" class="message_count"></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <hr>
                        </td>
                    </tr>                    
                </form>
<?php 
    $count = $count + 1; 
    } 
} else {
    //Use $reminders because there are stored reminders
    // die(print_r($reminders));
    for ($i=0; $i < count($reminders); $i++) { 
        $arrReminders = array();
        // die("first message is user: " . )
        if($reminders[$i]->user_id == $reminders[$i + 1]->user_id){
            $arrReminders["first"] = (array)$reminders[$i];
            $arrReminders["second"] = (array)$reminders[$i + 1];
            $reminders_array[] = $arrReminders;
            $i = $i + 1;
        } else {
            $arrReminders["first"] = (array)$reminders[$i];
            // $arrReminders["second"] = [];
            $reminders_array[] = $arrReminders;
        }
    }
    // die(print_r($reminders_array));
    foreach ($reminders_array as $key => $reminder) {
        // die(print_r($reminder));
        $first = $reminder["first"];
        $second = $reminder["second"];
        
        if($first == ""){
            $first["reminder_id"] = "-1";
            $first["reminder_type"] = "text";
            $first["reminder_number"] = "1";
            $first["reminder_interval"] = "";
            $first["reminder_datetime"] = "";
            $first['remindersent_id'] = '0';
            $first["message"] = "";
            $first["message_id"] = "";
            $first["user_type"] = "";
        }
        
        if($second == ""){
            $second["reminder_id"] = "-1";
            $second["reminder_type"] = "text";
            $second["reminder_number"] = "1";
            $second["reminder_interval"] = "";
            $second["reminder_datetime"] = "";
            $second['remindersent_id'] = '0';
            $second["message"] = "";
            $second["message_id"] = "";
            $second["user_type"] = "";
        }
        $type = $first["user_type"]; 
        $person_type = ""; 
        if($type == "person") { 
            $person_type = "(Applicant)"; 
        } 
        if($type == "witnesses") { 
            $person_type = "(Witness)"; 
        } 
        if($type == "defendant") { 
            $person_type = "(Defendant)"; 
        } 
        if($type == "user") { 
            $person_type = ""; 
        } 
        // echo print_r($first) . "\r\n";
        // die(print_r($second));
        // die("reminder type 1:" . $first["reminder_type"] . "; reminder type 2:" . $second["reminder_type"]);
?>      
                <form id="reminder_form_<?php echo $count; ?>" class="reminder_form">
                    <tr>
                        <td valign="top">
                            <input type="checkbox" id="case_worker_<?php echo $count; ?>" name="case_worker_<?php echo $count; ?>" class="selected_worker" style="display:" checked/>&nbsp;<span class="user_name" style="display:"><?php echo $first["user_name"]; ?>&nbsp;&nbsp;&nbsp;<?php echo $$person_type; ?></span>
                            <input type="hidden" id="case_worker_id_<?php echo $count; ?>" name="case_worker_id_<?php echo $count; ?>" value="<?php if($first["user_id"] != "") { echo $first["user_id"]; } else { echo $second["user_id"]; } ?>" />
                            <input type="hidden" id="case_worker_table_<?php echo $count; ?>" name="case_worker_table_<?php echo $count; ?>" value="<?php if($first["user_type"] != "") { echo $first["user_type"]; } else { echo $second["user_type"]; } ?>" />
                            <input type="hidden" id="reminder_id_<?php echo $count; ?>_first" name="reminder_id_<?php echo $count; ?>_first" value="<?php echo $first["reminder_id"]; ?>" />
                            <input type="hidden" id="reminder_id_<?php echo $count; ?>_second" name="reminder_id_<?php echo $count; ?>_second" value="<?php echo $second["reminder_id"]; ?>" />                            
                        </td>
                    </tr>
                    <tr id="reminder_row_<?php echo $count; ?>" style="display:">
                        <td align="left" valign="top" scope="row" nowrap="nowrap"class="reminder_stuff">
                        	<label style="width:125px; display:inline-block;<?php if($first['remindersent_id'] != '0') { echo 'background-color:green'; } ?>;">1st Reminder:</label>
                            <input type="hidden" name="reminder_number_<?php echo $count; ?>_first" id="reminder_number_<?php echo $count; ?>_first" value="<?php echo $first["reminder_number"]; ?>">
                            <select name="reminder_type_<?php echo $count; ?>_first" id="reminder_type_<?php echo $count; ?>_first" class="reminder_field">
                                <option value="text" <?php if($first["reminder_type"] == "text") { echo "selected"; } ?>>Text</option>
                                <option value="voice" <?php if($first["reminder_type"] == "voice") { echo "selected"; } ?>>Voice</option>
                                <option value="email" <?php if($first["reminder_type"] == "email") { echo "selected"; } ?>>Email</option>
<?php if($first["user_type"] == "user") { ?>                                   
                                <option value="interoffice" <?php if($first["reminder_type"] == "interoffice") { echo "selected"; } ?>>Interoffice</option>
                                <option value="popup"<?php if($first["reminder_type"] == "popup") { echo "selected"; } ?>>Popup</option>
<?php } ?>                                   
                            </select>
                            <!--<input type="number" name="reminder_interval_<?php //echo $count; ?>_first" id="reminder_interval_<?php //echo $count; ?>_first" min='1' step='1' style="width:40px" value="<?php //echo $first["reminder_interval"]; ?>" class="reminder_field reminder_interval">--><!--onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')"-->
                            <select name="reminder_interval_<?php echo $count; ?>_first" id="reminder_interval_<?php echo $count; ?>_first" class="reminder_interval"> 
                                <option value="" <?php if($first["reminder_interval"] == "") { echo "selected"; } ?>>Select One...</option>                      
                                <option value="5" <?php if($first["reminder_interval"] == "5") { echo "selected"; } ?>>5 Minutes</option>
                                <option value="10" <?php if($first["reminder_interval"] == "10") { echo "selected"; } ?>>10 Minutes</option>
                                <option value="15" <?php if($first["reminder_interval"] == "15") { echo "selected"; } ?>>15 Minutes</option>
                                <option value="20" <?php if($first["reminder_interval"] == "20") { echo "selected"; } ?>>20 Minutes</option>
                                <option value="25" <?php if($first["reminder_interval"] == "25") { echo "selected"; } ?>>25 Minutes</option>                                
                                <option value="30" <?php if($first["reminder_interval"] == "30") { echo "selected"; } ?>>30 Minutes</option>
                                <option value="45" <?php if($first["reminder_interval"] == "45") { echo "selected"; } ?>>45 Minutes</option>
                                <option value="60" <?php if($first["reminder_interval"] == "60") { echo "selected"; } ?>>60 Minutes</option>
                                <option value="120" <?php if($first["reminder_interval"] == "120") { echo "selected"; } ?>>2 Hours</option>
                                <option value="180" <?php if($first["reminder_interval"] == "180") { echo "selected"; } ?>>3 Hours</option>
                                <option value="240" <?php if($first["reminder_interval"] == "240") { echo "selected"; } ?>>4 Hours</option>
                                <option value="300" <?php if($first["reminder_interval"] == "300") { echo "selected"; } ?>>5 Hours</option>
                                <option value="360" <?php if($first["reminder_interval"] == "360") { echo "selected"; } ?>>6 Hours</option>
                                <option value="420" <?php if($first["reminder_interval"] == "420") { echo "selected"; } ?>>7 Hours</option>
                                <option value="480" <?php if($first["reminder_interval"] == "480") { echo "selected"; } ?>>8 Hours</option>
                                <option value="540" <?php if($first["reminder_interval"] == "540") { echo "selected"; } ?>>9 Hours</option>
                                <option value="600" <?php if($first["reminder_interval"] == "600") { echo "selected"; } ?>>10 Hours</option>
                                <option value="660" <?php if($first["reminder_interval"] == "660") { echo "selected"; } ?>>11 Hours</option>
                                <option value="720" <?php if($first["reminder_interval"] == "720") { echo "selected"; } ?>>12 Hours</option>
                                <option value="1440" <?php if($first["reminder_interval"] == "1440") { echo "selected"; } ?>>1 Day</option>
                                <option value="2880" <?php if($first["reminder_interval"] == "2880") { echo "selected"; } ?>>2 Days</option>
                                <option value="4320" <?php if($first["reminder_interval"] == "4320") { echo "selected"; } ?>>3 Days</option>
                                <option value="5760" <?php if($first["reminder_interval"] == "5760") { echo "selected"; } ?>>4 Days</option>
                                <option value="7200" <?php if($first["reminder_interval"] == "7200") { echo "selected"; } ?>>5 Days</option>
                                <option value="8640" <?php if($first["reminder_interval"] == "8640") { echo "selected"; } ?>>6 Days</option>
                                <option value="10080" <?php if($first["reminder_interval"] == "10080") { echo "selected"; } ?>>1 Week</option>
                                <option value="20160" <?php if($first["reminder_interval"] == "20160") { echo "selected"; } ?>>2 Weeks</option>
                            </select>
                            </br>
                            <span id="reminderspan_datetime_<?php echo $count; ?>_first" style="text-align:left;<?php if($first['remindersent_id'] != '0') { echo 'background-color:green'; } ?>;"><?php if($first["reminder_datetime"] != ""){ echo date("m/d/Y h:ia", strtotime($first["reminder_datetime"])); } ?></span>
                            <input type="hidden" id="reminder_datetime_<?php echo $count; ?>_first" name="reminder_datetime_<?php echo $count; ?>_first" value="<?php echo $first["reminder_datetime"]; ?>" />
                        </td>
                        <td align="left" valign="top" scope="row" nowrap="nowrap"class="reminder_stuff">
                        	<label style="width:125px; display:inline-block;<?php if($second['remindersent_id'] != '0') { echo 'background-color:green'; } ?>;">2nd Reminder:</label>
                            <input type="hidden" name="reminder_number_<?php echo $count; ?>_second" id="reminder_number_<?php echo $count; ?>_second" value="<?php echo $user->reminder_number; ?>">
                            <select name="reminder_type_<?php echo $count; ?>_second" id="reminder_type_<?php echo $count; ?>_second" class="reminder_field">
                                <option value="text" <?php if($second["reminder_type"] == "text") { echo "selected"; } ?>>Text</option>
                                <option value="email" <?php if($second["reminder_type"] == "email") { echo "selected"; } ?>>Email</option>
                                <option value="voice" <?php if($second["reminder_type"] == "voice") { echo "selected"; } ?>>Voice</option>
<?php if($second["user_type"] == "user") { ?>                                   
                                <option value="interoffice" <?php if($second["reminder_type"] == "interoffice") { echo "selected"; } ?>>Interoffice</option>
                                <option value="popup"<?php if($second["reminder_type"] == "popup") { echo "selected"; } ?>>Popup</option>
<?php } ?>                                   
                            </select>
                            <!--<input type="number" name="reminder_interval_<?php //echo $count; ?>_second" id="reminder_interval_<?php //echo $count; ?>_second>" min='1' step='1' style="width:40px" class="reminder_field reminder_interval" value="<?php //echo intval($user->reminder_interval); ?>">--><!--onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')"-->
                            <select name="reminder_interval_<?php echo $count; ?>_second" id="reminder_interval_<?php echo $count; ?>_second" class="reminder_interval">  
                                <option value="" <?php if($second["reminder_interval"] == "") { echo "selected"; } ?>>Select One...</option>                      
                                <option value="5" <?php if($second["reminder_interval"] == "5") { echo "selected"; } ?>>5 Minutes</option>
                                <option value="10" <?php if($second["reminder_interval"] == "10") { echo "selected"; } ?>>10 Minutes</option>
                                <option value="15" <?php if($second["reminder_interval"] == "15") { echo "selected"; } ?>>15 Minutes</option>
                                <option value="20" <?php if($second["reminder_interval"] == "20") { echo "selected"; } ?>>20 Minutes</option>
                                <option value="25" <?php if($second["reminder_interval"] == "25") { echo "selected"; } ?>>25 Minutes</option>                                   
                                <option value="30" <?php if($second["reminder_interval"] == "30") { echo "selected"; } ?>>30 Minutes</option>
                                <option value="45" <?php if($second["reminder_interval"] == "45") { echo "selected"; } ?>>45 Minutes</option>
                                <option value="60" <?php if($second["reminder_interval"] == "60") { echo "selected"; } ?>>60 Minutes</option>
                                <option value="120" <?php if($second["reminder_interval"] == "120") { echo "selected"; } ?>>2 Hours</option>
                                <option value="180" <?php if($second["reminder_interval"] == "180") { echo "selected"; } ?>>3 Hours</option>
                                <option value="240" <?php if($second["reminder_interval"] == "240") { echo "selected"; } ?>>4 Hours</option>
                                <option value="300" <?php if($second["reminder_interval"] == "300") { echo "selected"; } ?>>5 Hours</option>
                                <option value="360" <?php if($second["reminder_interval"] == "360") { echo "selected"; } ?>>6 Hours</option>
                                <option value="420" <?php if($second["reminder_interval"] == "420") { echo "selected"; } ?>>7 Hours</option>
                                <option value="480" <?php if($second["reminder_interval"] == "480") { echo "selected"; } ?>>8 Hours</option>
                                <option value="540" <?php if($second["reminder_interval"] == "540") { echo "selected"; } ?>>9 Hours</option>
                                <option value="600" <?php if($second["reminder_interval"] == "600") { echo "selected"; } ?>>10 Hours</option>
                                <option value="660" <?php if($second["reminder_interval"] == "660") { echo "selected"; } ?>>11 Hours</option>
                                <option value="720" <?php if($second["reminder_interval"] == "720") { echo "selected"; } ?>>12 Hours</option>
                                <option value="1440" <?php if($second["reminder_interval"] == "1440") { echo "selected"; } ?>>1 Day</option>
                                <option value="2880" <?php if($second["reminder_interval"] == "2880") { echo "selected"; } ?>>2 Days</option>
                                <option value="4320" <?php if($second["reminder_interval"] == "4320") { echo "selected"; } ?>>3 Days</option>
                                <option value="5760" <?php if($second["reminder_interval"] == "5760") { echo "selected"; } ?>>4 Days</option>
                                <option value="7200" <?php if($second["reminder_interval"] == "7200") { echo "selected"; } ?>>5 Days</option>
                                <option value="8640" <?php if($second["reminder_interval"] == "8640") { echo "selected"; } ?>>6 Days</option>
                                <option value="10080" <?php if($second["reminder_interval"] == "10080") { echo "selected"; } ?>>1 Week</option>
                                <option value="20160" <?php if($second["reminder_interval"] == "20160") { echo "selected"; } ?>>2 Weeks</option>
                            </select>
                            </br>
                            <span id="reminderspan_datetime_<?php echo $count; ?>_second" style="text-align:left;<?php if($second['remindersent_id'] != '0') { echo 'background-color:green'; } ?>;"><?php if($second["reminder_datetime"] != ""){ echo date("m/d/Y h:ia", strtotime($second["reminder_datetime"])); } ?></span>
                            <input type="hidden" id="reminder_datetime_<?php echo $count; ?>_second" name="reminder_datetime_<?php echo $count; ?>_second" value="<?php echo $second["reminder_datetime"]; ?>" />
                        </td>                        
                    </tr>
                    <tr id="message_row_<?php echo $count; ?>" style="display:">
                        <td valign="top">
                            <textarea id="message_worker_<?php echo $count; ?>_first" name="message_worker_<?php echo $count; ?>_first" rows="2" cols="50" class="worker_message"><?php echo $first["message"]; ?></textarea>
                            <input type="hidden" id="message_id_<?php echo $count; ?>_first" name="message_id_<?php echo $count; ?>_first" class="message_ids" value="<?php echo $first["message_id"]; ?>" />
                            <br />
                            <span id="message_count_<?php echo $count; ?>" class="message_count"></span>
                        </td> 
                        <td valign="top" style="">
                            <textarea id="message_worker_<?php echo $count; ?>_second" name="message_worker_<?php echo $count; ?>_second" rows="2" cols="50" class="worker_message"><?php echo $second["message"]; ?></textarea>
                            <input type="hidden" id="message_id_<?php echo $count; ?>_second" name="message_id_<?php echo $count; ?>_second" class="message_ids" value="<?php echo $second["message_id"]; ?>" />
                            <br />
                            <span id="message_count_<?php echo $count; ?>_second" class="message_count"></span>
                        </td>                         
                    </tr>
                    <tr>
                        <td colspan="2">
                            <hr>
                        </td>
                    </tr>                    
                </form>
<?php
    $count = $count + 1; 
    } 
}
?>     
            </table>
        </td>
    </tr>
</table>
<div style="height:500px; width:100%">&nbsp;</div>
<script>
function newReminderDate(reminder_number, reminder_position){
    // var reminder_span = $("#reminder_span_" + reminder_number).val();
    var reminder_interval = $("#reminder_interval_" + reminder_number + "_" + reminder_position).val();
    if (reminder_interval=="") {
        //set to blank
        $("#reminderspan_datetime_" + reminder_number + "_" + reminder_position).html("");
        $("#reminder_datetime_" + reminder_number + "_" + reminder_position).val("")
        return;
    }
    // if (reminder_span=="minutes" || reminder_span=="hours") {
        
    var current_date = $("#event_date").val();
    
    var formValues = "span=minutes&interval=" + reminder_interval + "&date=" + current_date;
    
    $.ajax({
        method: "POST",
        url: "../api/reminders/newtime",
        dataType:"text",
        data: formValues,
        success:function (data) {
            var formatted_date = moment(data, "ddd MMM DDo, YYYY hh:mma").format("MM/DD/YYYY hh:mma")
            $("#reminderspan_datetime_" + reminder_number + "_" + reminder_position).html(formatted_date);
            $("#reminder_datetime_" + reminder_number + "_" + reminder_position).val(formatted_date);
        }
    });
    // }
    /*
    if (reminder_span=="weeks") {
        reminder_span = "days";
        reminder_interval = reminder_interval * 7;
    }
    if (reminder_span=="days") {
        var current_date = $("#event_date").val();
        var arrDate = current_date.split(" ");
        current_date = arrDate[0];
        
        var formValues = "days=-" + reminder_interval + "&date=" + current_date;
        
        $.ajax({
            method: "POST",
            url: "../api/calculator_post.php",
            dataType:"json",
            data: formValues,
            success:function (data) {
                if(data.error) {  // If there is an error, show the error tasks
                    alert("error");
                } else {
                    $("#reminderspan_datetime_" + reminder_number).html(data[0].calculated_date + " " + arrDate[1]);
                    $("#reminder_datetime_" + reminder_number).val(data[0].calculated_date + " " + arrDate[1]);
                }
            }
        });
    }
    */
}
$(document).ready(function(){ 
    $(".worker_message").on("keyup", function(event){
        event.preventDefault();
        var element = event.currentTarget;
        var element_id = element.id;
        var arrElementId = element_id.split("_");
        var id_value = arrElementId[2];
        var reminder_position = arrElementId[3];
        var message = element.value;
        var count = message.length;

        if(count > 0){
            $("#message_count_" + id_value + "_" + reminder_position).html("Count: " + count);
        } else {
            $("#message_count_" + id_value + "_" + reminder_position).html("");
        }
    });
    $(".selected_worker").on("change", function(event){
        event.preventDefault();
        var element = event.currentTarget;
        var element_id = element.id

        if(element.checked) {
            var next_row_id = element.parentElement.parentElement.nextElementSibling.id;
            $("#" + next_row_id).fadeIn();
            var arrNextRowId = next_row_id.split("_");
            var id_value = arrNextRowId[2];
            $("#message_row_" + id_value).fadeIn();
            $("#submit_row").fadeIn();
        } else {
            var next_row_id = element.parentElement.parentElement.nextElementSibling.id;
            $("#" + next_row_id).fadeOut();  
            var arrNextRowId = next_row_id.split("_");
            var id_value =   arrNextRowId[2]; 
            $("#message_row_" + id_value).fadeOut();             
            if($(".selected_worker:checked").length == 0){
                $("#submit_row").fadeOut(); 
            }
        } 

		//extract the reminder number and position
        var arrElementId = element_id.split("_");
		var reminder_number = arrElementId[2];
        var reminder_position = arrElementId[3];
		newReminderDate(reminder_number, reminder_position);
    });
    $(".reminder_interval").on("change", function(event){
        var element = event.currentTarget;
        var element_id = element.id;
        var arrElementId = element_id.split("_");
		var reminder_number = arrElementId[2];
        var reminder_position = arrElementId[3];
		newReminderDate(reminder_number, reminder_position);
    });
    /*
    $(".reminder_interval").on("change", function(event){
        var element = event.currentTarget;
        var element_id = element.id       
		var reminder_number = element_id.substr(element_id.length - 1);
		newReminderDate(reminder_number);
    });
     $(".reminder_interval").on("keyup", function(event){
        var element = event.currentTarget;
        var element_id = element.id       
		var reminder_number = element_id.substr(element_id.length - 1);
		newReminderDate(reminder_number);
    });
    */
    $(".reminder_field").on("change", function(event){
        event.preventDefault();
        var element = event.currentTarget;
        var element_id = element.id;
        var arrElementId = element_id.split("_");
        var id_value = arrElementId[2];
        var reminder_position = arrElementId[3];
        var event_type = document.getElementById("event_type").value;
       
        if(element.value == "voice"){
            switch (event_type) {
                case "Deposition":
                    $("#message_worker_" + id_value + "_" + reminder_position).val("This is a Reminder for your Deposition appointment on <?php echo date("m/d/Y h:i a", strtotime($event_dateandtime)); ?> at <?php echo $house_number; ?> <?php echo $address; ?> in <?php echo $city; ?> for the case of <?php echo $case_name; ?>. In regards to <?php echo $event_title; ?>.");
                    break;
                default:
                    $("#message_worker_" + id_value + "_" + reminder_position).val("This is a Reminder for your appointment on <?php echo date("m/d/Y h:i a", strtotime($event_dateandtime)); ?> at <?php echo $house_number; ?> <?php echo $address; ?> in <?php echo $city; ?> for the case of <?php echo $case_name; ?>. In regards to <?php echo $event_title; ?>.");
                    break;
            }
            document.getElementById("message_worker_" + id_value + "_" + reminder_position).disabled = false;
            if(document.getElementById("add_message_" + id_value + "_" + reminder_position) != null) {
                document.getElementById("add_message_" + id_value + "_" + reminder_position).checked = true;
            }
        } else {
            document.getElementById("message_worker_" + id_value + "_" + reminder_position).disabled = true;
            $("#message_worker_" + id_value + "_" + reminder_position).val("");
            if(document.getElementById("add_message_" + id_value + "_" + reminder_position) != null) {
                document.getElementById("add_message_" + id_value + "_" + reminder_position).checked = false;
            }
        }
    });
    $(".add_message").on("change", function(event){
        var element = event.currentTarget;
        var element_id = element.id;
        var arrElementId = element_id.split("_");
        var id_value = arrElementId[2];
        var reminder_position = arrElementId[3];
        if(element.checked){
            document.getElementById("message_worker_" + id_value + "_" + reminder_position).disabled = false;
        } else {
            document.getElementById("message_worker_" + id_value + "_" + reminder_position).disabled = true;
        }
        
    });
    $("#submit_reminder").on("click", function(event){
        event.preventDefault();
        var formValues = "";

        if($("#event_date").val() == ""){
            alert("Please enter a Date and Time for the Reminder.");
            return;
        }
        $("#saving").fadeIn();
        for(var i = 0; i < $(".selected_worker").length; i++){
            var item = $(".selected_worker")[i];
            if(item.checked){
                var item_id = item.id;
                var arrItemID = item_id.split("_");
                var add_blank_spot = "";
                if($("#message_worker_" + arrItemID[2] + "_first").val() == ""){
                    add_blank_spot = "message_worker_" + arrItemID[2] + "_first=&"
                } 
                if($("#message_worker_" + arrItemID[2] + "_second").val() == ""){
                    add_blank_spot += "message_worker_" + arrItemID[2] + "_second=&"
                }               
                formValues += $("#reminder_form_" + arrItemID[2]).serialize() + "&" + add_blank_spot;
            }
        }
        formValues += "event_date=" + $("#event_date").val();

        if($("#event_id").val() != "-1"){
            formValues += "&event_id=" + $("#event_id").val();
        }


        $.ajax({
            url: "process_reminders.php",
            type: "POST",
            dataType: "json",
            data: formValues,
            success:function(data){
                var json_data = data.data;
                parent.getMyStuff(json_data);
                $("#saving").fadeOut();
            }
        });
    });
    $(".reminder_interval").trigger("change");
});
</script>
</body>
</html>