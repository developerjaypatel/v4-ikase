<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");


function getKaseInfo($id, $customer_id, $db_name) {
	$db_name = "`" . $db_name . "`.";
	
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.special_instructions,ccase.case_description, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,inj.adj_number,
			ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type, ccase.sub_in,
			
			venue_corporation.corporation_id venue_id, venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
			venue_corporation.street venue_street, venue_corporation.city venue_city, 
			venue_corporation.state venue_state, venue_corporation.zip venue_zip,
			
			ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, IFNULL(app.full_name, '') `full_name`, app.first_name, app.last_name, app.middle_name, app.`aka`, 
			app.dob, app.gender, app.ssn, app.ein, app.ein, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
			
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.company_name employer, employer.full_name employer_full_name, employer.street employer_street, employer.city employer_city,
			employer.state employer_state, employer.zip employer_zip,
			
			IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.company_name defendant, defendant.full_name defendant_full_name, defendant.street defendant_street, defendant.city defendant_city,
			defendant.state defendant_state, defendant.zip defendant_zip,
			
			CONCAT(app.first_name,' ', IF(app.middle_name='','',CONCAT(app.middle_name,' ')),app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, ccase.case_name, 
			
			IFNULL(att.nickname, '') as attorney_name, 
			IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(att.user_email, '') as attorney_email, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name, IFNULL(user.user_email, '') as worker_email,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
			job.job_id worker_job_id, job.job_uuid worker_job_uuid, if(job.job IS NULL, '', job.job) worker_job
			
			FROM " . $db_name . "cse_case ccase ";

			
			$sql .= " 
			LEFT OUTER JOIN " . $db_name . "cse_case_person ccapp ON (ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
			LEFT OUTER JOIN ";
if (($customer_id==1033)) { 
	$sql .= "(" . str_replace("pers.customer_id = -1", "pers.customer_id = " . $customer_id, SQL_PERSONX) . ")";
} else {
	$sql .= "" . $db_name . "cse_person";
}
$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `ikase`.`cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_case_corporation` dcorp
			ON (ccase.case_uuid = dcorp.case_uuid AND ccorp.attribute = 'defendant' AND dcorp.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_corporation` defendant
			ON dcorp.corporation_uuid = defendant.corporation_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_case_corporation` ccorp_venue
			ON (ccase.case_uuid = ccorp_venue.case_uuid AND ccorp_venue.attribute = 'venue' AND ccorp_venue.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_corporation` venue_corporation
			ON ccorp_venue.corporation_uuid = venue_corporation.corporation_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury_fee` cif 
			ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN " . $db_name . "`cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN " . $db_name . "`cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			
			where ccase.case_id=:id
			AND ccase.customer_id = " . $customer_id . "";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($id > 0) {
			$stmt->bindParam("id", $id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		if ($kase->case_name != "") {
			$kase->name = $kase->case_name;
		}
		if ($kase->case_number != "" && $kase->file_number=="") {
			$kase->file_number = $kase->case_number;
			$kase->case_number = "";
		}
		//print_r($kase);
		
        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
	
$today = date("Y-m-d");
$note = "\r\nTrigger Log:" . date("Y-m-d") . "
";
							
$fp = fopen('trigger_data.txt', 'a');
fwrite($fp, $note);
fclose($fp);

$arrOperations = array("task", "event", "message");
$arrCaseTypes = array("WCAB",
			"NewPI",
			"social_security",
			"class_action",
			"civil",
			"employment_law",
			"immigration",
			"WCAB_Defense"
			);
$minus_days = "- 1 days";
$last_updated_date = date("Y-m-d H:i:s");

//we need all of the jetfile submissions
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'
#AND schema_name = 'ikase_goldberg2'";

$arrSQL = array();
$arrSQLString = array();
				
try {
	$schemas = DB::select($sql);
	
	//die(print_r($schemas));
	foreach($schemas as $schema) {
		//skip
		if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
			continue;
		}
		
		echo "SCHEMA:" . $schema->schema_name . "\r\n";
		
		//do we have a workflow
		$sql = "SELECT wf.*, trig.* 
		FROM `" . $schema->schema_name . "`.cse_workflow wf
		INNER JOIN `" . $schema->schema_name . "`.cse_trigger trig
		ON wf.workflow_uuid = trig.workflow_uuid
		WHERE wf.deleted = 'N'
		AND wf.active = 'Y'
		AND wf.case_type IN ('" . implode("','", $arrCaseTypes) . "')
		AND trig.deleted = 'N'
		#AND trig.action = 'event'";
		
		$trigs = DB::select($sql);
		
		if (count($trigs)==0) {
			continue;
		}
		
		//echo $schema->schema_name;
		//die(print_r($arrCaseTypes));
		
		foreach($arrCaseTypes as $case_type) {
			//do we have a workflow
			$sql = "SELECT wf.*, trig.*, wf.customer_id cus_id 
			FROM `" . $schema->schema_name . "`.cse_workflow wf
			INNER JOIN `" . $schema->schema_name . "`.cse_trigger trig
			ON wf.workflow_uuid = trig.workflow_uuid
			WHERE wf.deleted = 'N'
			AND wf.active = 'Y'
			AND wf.case_type = '" . $case_type . "'
			AND trig.deleted = 'N'
			#AND trig.action = 'event'";
			
			$trigs = DB::select($sql);
			
			if (count($trigs)==0) {
				//echo "No Trigger for " . $case_type . " in " . $schema->schema_name . "\r\n";
				continue;
			}
			
			//die(print_r($trigs));
			
			foreach($trigs as $trig) {
				$attach_table = "personal_";
				if($trig->case_type=="WCAB") {
					$attach_table = "";
				}
				$sql = "SELECT ccase.case_id, ccase.case_uuid, ccase.case_date, ccase.filing_date, ccase.case_status, ccase.case_type, supervising_attorney, attorney, worker, case_language,  
				cpi." . $attach_table . "injury_uuid, ";
				if ($attach_table=="") {
					$sql .= "
					cpi.start_date injury_date,";
				} else {
					$sql .= "
					cpi." . $attach_table . "injury_date injury_date,";
				}
				if ($trig->action=="task" || $trig->action=="event") {
					$sql .= "
					ct." . $trig->action . "_dateandtime, ct." . $trig->action . "_id, ct." . $trig->action . "_uuid, ct." . $trig->action . "_title, ct." . $trig->action . "_description,
					created." . $trig->action . "_create_date,";
				}
				if ($trig->action=="status") {
					$sql .= "
					status_change.status_date,";
				}
				if ($attach_table!="") {
					$sql .= "
					IF (
						cpi.statute_limitation = '0000-00-00', 
						CAST(DATE_ADD(cpi." . $attach_table . "injury_date, INTERVAL 2 YEAR) AS DATE) , 
						cpi.statute_limitation
					) statute_limitation,";
				} else {
					$sql .= "
					IF (
						cpi.statute_limitation = '0000-00-00', 
						CAST(DATE_ADD(cpi.start_date, INTERVAL 2 YEAR) AS DATE) , 
						cpi.statute_limitation
					) statute_limitation,";
				}
				
				//if ($trig->trigger_actual=="statute_date") {
				//	$sql .= "
				//	IF(stats.case_uuid IS NULL, 'Y', 'N') needs_action";
				//} else {
					$sql .= "
					'Y' needs_action";
				//}
				
				$sql .= "
				FROM `" . $schema->schema_name . "`.cse_" . $attach_table . "injury cpi
				
				#restrict to cases inserted via ikase
				INNER JOIN `" . $schema->schema_name . "`.`cse_" . $attach_table . "injury_track` cpit
				ON cpi." . $attach_table . "injury_id = cpit." . $attach_table . "injury_id AND cpit.operation = 'insert'";
				if ($attach_table!="") {
					$sql .= "
					INNER JOIN `" . $schema->schema_name . "`.cse_case ccase
					ON cpi.case_id = ccase.case_id";
				} else {
					$sql .= "
					INNER JOIN `" . $schema->schema_name . "`.cse_case_injury cci
					ON cpi.injury_uuid = cci.injury_uuid
					INNER JOIN `" . $schema->schema_name . "`.cse_case ccase
					ON cci.case_uuid = ccase.case_uuid";
				}
				$sql .= "
				LEFT OUTER JOIN `" . $schema->schema_name . "`.`cse_" . $attach_table . "injury_trigger` prig
				ON `cpi`.`" . $attach_table . "injury_uuid` = `prig`.`" . $attach_table . "injury_uuid`
				AND prig.trigger_uuid = '" . $trig->trigger_uuid . "' AND prig.deleted = 'N'";
				
				if ($trig->trigger_actual=="statute_date") {
					/*
					$sql .= "
					LEFT OUTER JOIN (
						SELECT DISTINCT cct.case_uuid 
						FROM `" . $schema->schema_name . "`.`cse_task` ct
						INNER JOIN `" . $schema->schema_name . "`.`cse_case_task` cct
						ON ct.task_uuid = cct.task_uuid
						WHERE INSTR(task_description, '2 YR STATUTE') > 0
						OR INSTR(task_title, '2 YR STATUTE') > 0
						OR INSTR(task_title, '2 YEAR STATUTE') > 0
						OR INSTR(task_title, 'YR BEFORE STATUTE') > 0
						AND ct.deleted = 'N'
					) stats
					ON ccase.case_uuid = stats.case_uuid 
					";
					*/
					//the IS NULL gives you cases that have not been triggered
				}
				
				if ($trig->action=="task" || $trig->action=="event") {
					$sql .= "
					
					INNER JOIN `" . $schema->schema_name . "`.`cse_case_" . $trig->action . "` cct
					ON ccase.case_uuid = cct.case_uuid
					INNER JOIN `" . $schema->schema_name . "`.`cse_" . $trig->action . "` ct
					ON cct." . $trig->action . "_uuid = ct." . $trig->action . "_uuid
					";
					if ($trig->action=="task") {
						$sql .= "
						INNER JOIN (
						SELECT task_uuid, `time_stamp` task_create_date
						FROM `" . $schema->schema_name . "`.cse_task_track
						WHERE type_of_task = '" . $trig->trigger_actual . "'
						AND operation = 'insert'";
					}
					if ($trig->action=="event") {
						$sql .= "
						INNER JOIN (
						SELECT event_uuid, `time_stamp` event_create_date
						FROM `" . $schema->schema_name . "`.cse_event_track
						WHERE event_type = '" . $trig->trigger_actual . "'
						AND operation = 'insert'";
					}
					$sql .= "
						) created
						ON ct." . $trig->action . "_uuid = created." . $trig->action . "_uuid
					";
				}
				if ($trig->action=="status") {
					$sql .= "
					INNER JOIN (
						SELECT case_uuid, MIN(time_stamp) status_date 
						FROM ikase.cse_case_track
						WHERE operation = 'update'
						AND case_status = '" . $trig->trigger_actual . "'
						GROUP BY case_uuid
					) status_change
					ON ccase.case_uuid = status_change.case_uuid";
				}
				
				$sql .= "
				WHERE 1
				#AND ccase.case_id = 6726 
				AND ccase.customer_id = '" . $trig->cus_id . "'
				AND prig.trigger_uuid IS NULL
				AND cpi.deleted = 'N'
				AND ccase.deleted = 'N'
				";
				if ($trig->action=="task") {
					$sql .= "
					AND ct.type_of_task = '" . $trig->trigger_actual . "'";
				}
				if ($trig->action=="event") {
					$sql .= "
					AND ct.event_type = '" . $trig->trigger_actual . "'";
				}
				if ($trig->trigger_actual=="statute_date") {
					$sql .= "
						AND ccase.case_id NOT IN (9538, 9690, 9735, 9629)";
				}
				if ($trig->action=="status") {
					$sql .= "
						AND ccase.case_status = '" . $trig->trigger_actual . "'";
				} else {
					$sql .= "
						AND INSTR(ccase.case_status, 'Closed') = 0
						AND INSTR(ccase.case_status, 'Dropped') = 0
						AND INSTR(ccase.case_status, 'REJECTED') = 0
						AND INSTR(ccase.case_status, 'Intake') = 0";
				}
				switch($trig->trigger_actual) {
					case "statute_date":
						$sql .= "
						AND (cpi.statute_limitation > '" . $today . "' OR cpi.statute_limitation = '0000-00-00')";
						if ($attach_table!="") {
							$sql .= "
							AND cpi." . $attach_table . "injury_date != '0000-00-00 00:00:00'";
						} else {
							$sql .= "
							AND cpi.start_date != '0000-00-00 00:00:00'";
						}
						break;
					case "injury_date":
						$injury_field = "personal_injury";
						if ($attach_table=="") {
							$injury_field = "start";
						}
						$sql .= "
						AND (cpi." . $injury_field . "_date != '0000-00-00')";
						break;
					case "intake_date":
						$sql .= "
						AND (ccase.case_date != '0000-00-00')";
						break;
					case "complaint_date":
						$sql .= "
						AND (ccase.filing_date != '0000-00-00')";
						break;
				}
				
				
				//find cases of each type
				switch($case_type) {
					case "NewPI":
						$sql .= "
						AND 
						(
							ccase.`case_type`= 'NewPI'
							OR
							ccase.`case_type` = 'Personal Injury'
                            OR 
							ccase.case_type = 'Personal Injury (UM)'
                            OR 
							ccase.case_type = 'slipandfall'
							OR 
							ccase.case_type = 'dogbite'
                            OR 
							ccase.case_type = 'carpass'
						)";
						break;
					case "WCAB":
						$sql .= "
						AND 
						(
							INSTR(ccase.`case_type`, 'Worker') > 0
							OR
							INSTR(ccase.`case_type`, 'WC') > 0
							OR
							INSTR(ccase.`case_type`, 'W/C') > 0
						)";
						
						break;
					default:
						$sql .= "
						AND INSTR(ccase.`case_type`, '" . $case_type . "') > 0";
				}
				
				$search_sql = $sql;
				//echo $sql;
				//die($sql);
				
				$injurys = DB::select($sql);
				
				if (count($injurys) == 0) {
					continue;
				}
				//die(print_r($injurys));
				foreach($injurys as $injury) {
					
					//HARD CODE statute_date
					if ($trig->operation == "task" && $injury->needs_action=="N") {
						continue;
					}
					echo "\r\nPROCESSING case id:" . $injury->case_id . " for " . $trig->operation . "\r\n";
					//we're going to generate a set of inserts for each $pi
					if (!isset($arrSQL[$injury->case_id])) {
						$arrSQL[$injury->case_id] = array();
					}
					
					if($case_type=="WCAB") {
						$injury_uuid = $injury->injury_uuid;
					} else {
						$injury_uuid = $injury->personal_injury_uuid;
					}
					//die(print_r($injury));
					//is there a trigger?
					$sql = "SELECT COUNT(`" . $attach_table . "injury_trigger_id`) `trigger_count`
					FROM `" . $schema->schema_name . "`.`cse_" . $attach_table . "injury_trigger`
					WHERE `trigger_uuid` = '" . $trig->trigger_uuid . "'
					AND `" . $attach_table . "injury_uuid` = '" . $injury_uuid . "'";
					//die($sql);
					$stmt = DB::run($sql);
					$trigcounter = $stmt->fetchObject();
					
					if ($trigcounter->trigger_count==0) {
						//get the kase info
						//echo $injury->case_id . " - \r\n";
						$kase = getKaseInfo($injury->case_id, $trig->cus_id, $schema->schema_name);
						
						//die(print_r($kase));		
					
					
						$time = $trig->trigger_time;
						//echo $time . " " . $trig->trigger_interval . "\r\n";
						//print_r($trig);
						if ($trig->action=="date") {
							switch($trig->trigger_actual) {
								case "statute_date":
									$time_string = strtotime($injury->statute_limitation);
									break;
								case "injury_date":
									$time_string = strtotime($injury->injury_date);
									break;
								case "intake_date":
									$time_string = strtotime($injury->case_date);
									break;
								case "complaint_date":
									$time_string = strtotime($injury->filing_date);
									//die(date("Y-m-d", $time_string));
									break;
							}
						}
						if ($trig->action=="task" && $time > 0) {
							$time_string = strtotime($injury->task_dateandtime);
						}
						if ($trig->action=="task" && $time < 0) {
							$time_string = strtotime($injury->task_create_date);
						}
						if ($trig->action=="event" && $time > 0) {
							$time_string = strtotime($injury->event_dateandtime);
						}
						if ($trig->action=="event" && $time < 0) {
							$time_string = strtotime($injury->event_create_date);
						}
						if ($trig->action=="status") {
							$time_string = strtotime($injury->status_date);
						}
						//die("year:" .  (date("Y", $time_string) + $time));
						
						switch($trig->trigger_interval) {
							case "years":
								if ($time > 0) {
									$year = $time;
									$month = 0;
									if (is_decimal($time)) {
										$year = floor($time);
										$month = 12 * ($time - floor( $time));
									}
									if ($trig->trigger=="B") {
										$year *= -1;
										$month *= -1;
									}
									$trigger_date = mktime(0, 0, 0, date("m", $time_string) + $month, date("d", $time_string), date("Y", $time_string) + $year);
								} else {
									//negative = 1 year after create date
									$year = 1;
									$time = 1;
									$trigger_date = mktime(0, 0, 0, date("m", $time_string), date("d", $time_string), date("Y", $time_string) + $year);
								}
								//$days = $time * 365;
								break;
							case "months":
								if ($time > 0) {
									if ($trig->trigger=="B") {
										$time *= -1;
									}
									$trigger_date = mktime(0, 0, 0, date("m", $time_string) + $time, date("d", $time_string), date("Y", $time_string));
									$days = $time * 31;
								} else {
									//negative = 1 month after create date
									$month = 1;
									$time = 1;
									$trigger_date = mktime(0, 0, 0, date("m", $time_string) + $month, date("d", $time_string), date("Y", $time_string));
								}
								break;
							case "days":
								if ($time > 0) {
									if ($trig->trigger=="B") {
										$time *= -1;
									}
									$trigger_date = mktime(0, 0, 0, date("m", $time_string), date("d", $time_string) + $time, date("Y", $time_string));
									$days = $time;
								} else {
									//print_r($injury);
									//die("strin:" . date("Y-m-d", $time_string));
									//negative = 1 day after create date
									$days = $time * (-1);
									$trigger_date = mktime(0, 0, 0, date("m", $time_string), date("d", $time_string) + $days, date("Y", $time_string));
								}
								break;
						}
						$calculated_date = date("m/d/Y", $trigger_date);
						$trigger_date = date("Y-m-d", $trigger_date);
						
						$subject = str_replace(".0", "", abs($time)) . " ";
						
						switch($trig->trigger_interval) {
							case "years":
								$subject .= "YR";
								break;
							case "months":
								$subject .= "MONTHS";
								break;
							case "weeks":
								$subject .= "WEEKS";
								break;
							case "days":
								$subject .= "DAYS";
								break;
							
						}
						if ($trig->trigger=="B") {
							$subject .= " BEFORE"; 
						} 
						if ($trig->trigger=="A" || $trig->trigger=="") {
							$subject .= " AFTER"; 
						}

						if ($trig->action=="date") {
							switch($trig->trigger_actual) {
								case "statute_date":
									$subject .= " STATUTE"; 
									break;
								case "injury_date":
									$subject .= " DOI"; 
									break;
								case "intake_date":
									$subject .= " INTAKE"; 
									break;
								case "complaint_date":
									$subject .= " COMPLAINT"; 
									break;
							}
						}
						if ($trig->action=="task") {
							if ($trig->trigger=="") {
								$subject .= " [" . strtoupper($trig->trigger_actual) . "] TASK CREATED"; 
							} else {
								$subject .= " [" . strtoupper($trig->trigger_actual) . "] TASK ASSIGNED"; 
							}
						}
						if ($trig->action=="event") {
							if ($trig->trigger=="") {
								$subject .= " [" . strtoupper($trig->trigger_actual) . "] EVENT CREATED"; 
							} else {
								$subject .= " [" . strtoupper($trig->trigger_actual) . "] EVENT SCHEDULED";
							}
						}
						$subject = $subject . " - AUTO " . strtoupper($trig->operation);
						
						//echo "time:" . $time . "\r\n";
						//die($injury->event_create_date . " // " . $trigger_date . " - " . $calculated_date);
						//make sure the date is a valid business date
						//var formValues = "days=+" + reminder_interval + "&date=" + current_date;
						$blnModified = false;
						while (confirm_holiday($trigger_date)) {
							$trigger_date = date("Y", strtotime($trigger_date . $minus_days))."-".date("m", strtotime($trigger_date . $minus_days))."-".date("d", strtotime($trigger_date . $minus_days));
							$blnModified = true;
						}
						
						//no weekends
						while (date("N", strtotime($trigger_date)) > 5) {
							$trigger_date = date("Y", strtotime($trigger_date . $minus_days))."-".date("m", strtotime($trigger_date . $minus_days))."-".date("d", strtotime($trigger_date . $minus_days));
							$blnModified = true;
							//die($injury->statute_limitation . " ==> trigger_date:" . $trigger_date);
						} 
						/*
						if ($blnModified) {
							die($injury->filing_date . " - " . $trigger_date);
						}
						*/
						//echo "sol_date:" . $injury->statute_limitation . "\r\n";
						//echo "trigger_date:" . $trigger_date . "\r\n";
						$trigger_date = date("Y-m-d", strtotime($trigger_date)) . " 05:06:07";
						
						//has the action been scheduled
						$table_name = $trig->operation;
						
						$compare = "CAST(`" . $table_name . "_dateandtime` AS DATE)";
						$priority = $table_name . "_priority = 'trigger'";
						if ($table_name=="message") {
							$compare = "CAST(`dateandtime` AS DATE)";
							$priority = "priority = 'trigger'";
						}
						
						$sql = "SELECT *
						FROM `" . $schema->schema_name . "`.cse_" . $table_name . " tab
						
						INNER JOIN `" . $schema->schema_name . "`.cse_case_" . $table_name . " ctab
						ON tab." . $table_name . "_uuid = ctab." . $table_name . "_uuid
						
						INNER JOIN `" . $schema->schema_name . "`.cse_case ccase
						ON ctab.case_uuid = ccase.case_uuid
						
						WHERE " . $compare . " = '" . $trigger_date . "'
						AND " . $priority . "
						AND tab.deleted = 'N'
						AND ccase.case_id = '" . $injury->case_id . "'";
						
						//print_r($injury);
						//die($sql);
						
						$actions = DB::select($sql);
						//die(print_r($actions));
						if (count($actions)==0) {
							
							$arrFields = array();
							$arrSet = array();
								
							//create task/event/message with that date/time, assign to case workers, notification employees
							//look up the case assignees
							$arrAssign = explode(",", $trig->assignee);
							//print_r($injury);
							//die();
							$arrAssignee = array();
							$arrAssigneeID = array();
							foreach($arrAssign as $assign) {
								$where = "WHERE customer_id = '" . $trig->cus_id . "'";
								switch($assign) {
									case "KASE_COORD":
										//get the case coordinator
										//make sure to get an id
										//add to arrAssignee array
										$coord = $injury->worker;
										//die("coord:" . $coord);
										if ($coord!="") {
											if (is_numeric($coord)) {
												$where .= "
												AND user_id = '" . $coord . "'";
											} else {
												$where .= "
												AND nickname = '" . $coord . "'";
											}
										}
										break;
									case "KASE_ATTY":
										//get the case coordinator
										//make sure to get an id
										//add to arrAssignee array
										$atty = $injury->attorney;
										if ($atty!="") {
											if (is_numeric($atty)) {
												$where .= "
												AND user_id = '" . $atty . "'";
											} else {
												$where .= "
												AND nickname = '" . $atty . "'";
											}
										}
										break;
									case "KASE_SATTY":
										//get the case coordinator
										//make sure to get an id
										//add to arrAssignee array
										$satty = $injury->supervising_attorney;
										if ($satty!="") {
											if (is_numeric($satty)) {
												$where .= "
												AND user_id = '" . $satty . "'";
											} else {
												$where .= "
												AND nickname = '" . $satty . "'";
											}
										}
										break;
									default:
										if ($assign!="") {
											$where .= "
											AND user_id = '" . $assign . "'";
									}
								}
								if (strpos($where, "AND")!==false) {
									$sql = "SELECT user_uuid, nickname 
									FROM ikase.`cse_user` user 
									" . $where;
									//die($sql);
									$stmt = DB::run($sql);
									$user = $stmt->fetchObject();
									
									if (is_object($user)) {
										$arrAssignee[] = $user->nickname;
										$arrAssigneeID[] = $user->user_uuid;
									} else {
										//die($sql);
									}
								}
							}
							
							if (count($arrAssignee)==0) {
								echo "no assignees";
								//print_r($trig);
								//die(print_r($injury));
								if ($table_name!="event") {
									echo " not event";
									//no one to assign to
									continue;
								}
							}
							
							
							$arrAssignee = array_unique($arrAssignee);
							$arrAssigneeID = array_unique($arrAssigneeID);
							
							//die(print_r($arrAssignee));
							
							//i have assignees
							if ($table_name=="message") {
								$message = $trig->trigger_description;
								
								if ($blnModified && $trig->trigger_interval=="days") {
									//it's not the exact number of days because of holiday/weekend
									$message = "IMPORTANT NOTE: The actual date is " . $calculated_date . " (holiday/weekend)\r\n\r\n" . $message;
								}
								$arrFields[] = "`message_to`";
								$arrSet[] = "'" . implode(";", $arrAssignee) . "'";
								$arrFields[] = "`message`";
								$arrSet[] = "'" . addslashes($message) . "'";
								$arrFields[] = "`subject`";
								//$arrSet[] = "'Workflow Automatic " . ucwords($table_name) . "'";
								$arrSet[] = "'" . $subject . "'";
								
								$arrFields[] = "`dateandtime`";
							} else {
								$arrFields[] = "`" . $table_name . "_dateandtime`";
							}
							$arrSet[] = "'" . $trigger_date . "'";
							
							if ($table_name!="message") {
								$arrFields[] = "`assignee`";
								$arrSet[] = "'" . implode(";", $arrAssignee) . "'";
								//create instance
								$arrFields[] = "`" . $table_name . "_description`";
								$arrSet[] = "'" . addslashes($trig->trigger_description) . "'";
								
								$arrFields[] = "`" . $table_name . "_first_name`";
								$arrSet[] = "'" . addslashes($kase->first_name) . "'";
								
								$arrFields[] = "`" . $table_name . "_last_name`";
								$arrSet[] = "'" . addslashes($kase->last_name) . "'";
								
								$arrFields[] = "`" . $table_name . "_title`";
								$arrSet[] = "'" . $subject . "'";
								
								$arrFields[] = "`" . $table_name . "_type`";
								$arrSet[] = "'open'";
								/*
								if (strtotime($today) > strtotime($trigger_date)) {
									$arrSet[] = "'closed'";
									
								} else {
									$arrSet[] = "'open'";
								}
								
								if (strtotime($today) > strtotime($trigger_date)) {
									//delete these right away
									$arrFields[] = "`deleted`";
									$arrSet[] = "'Y'";
								}
								*/
								$arrFields[] = "`end_date`";
								$arrSet[] = "'" . $trigger_date . "'";
								
								$arrFields[] = "`" . $table_name . "_date`";
								$arrSet[] = "'" . date("Y-m-d", strtotime($trigger_date)) . "'";
							}
							if ($table_name!="message") {
								$arrFields[] = "`" . $table_name . "_priority`";
							} else {
								$arrFields[] = "`priority`";
							}
							$arrSet[] = "'trigger'";
							
							$arrFields[] = "`from`";
							$arrSet[] = "'SYSTEM'";
							$arrFields[] = "`" . $table_name . "_from`";
							$arrSet[] = "'SYSTEM'";
							$arrFields[] = "`color`";
							$arrSet[] = "'orange'";
							
							$arrFields[] = "`customer_id`";
							$arrSet[] = $trig->cus_id;
						
							$table_uuid = uniqid("KS", false);
							$sql = "
							INSERT INTO `" . $schema->schema_name . "`.`cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
									VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ");";
							//echo $sql . "\r\n";
							//print_r($injury);
							//die(print_r($trig));
							//$arrSQL[$injury->case_id][] = $sql;
							$arrSQLString[] = $sql;
							
							//attach to case
							$case_table_uuid = uniqid("KA", false);
							$attribute_1 = "main";
							//now we have to attach the table to the case 
							$sql = "INSERT INTO `" . $schema->schema_name . "`.`cse_case_" . $table_name . "` (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
							VALUES ('" . $case_table_uuid  ."', '" . $kase->uuid . "', '" . $table_uuid . "', '" . $trig->trigger_uuid . "', '" . $last_updated_date . "', 'SYSTEM', '" . $trig->cus_id . "');";
							//echo $sql . "\r\n";
							//$arrSQL[$injury->case_id][] = $sql;
							$arrSQLString[] = $sql;
							//add to "tablename"_user
							$task_user_uuid = uniqid("TD", false);
							$sql = "INSERT INTO `" . $schema->schema_name . "`.`cse_" . $table_name . "_user` (`" . $table_name . "_user_uuid`, `" . $table_name . "_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`)
							VALUES ('" . $task_user_uuid  ."', '" . $table_uuid . "', 'SYSTEM', 'from', '" . $last_updated_date . "', 'SYSTEM', '" . $trig->cus_id . "');";
							//echo $sql . "\r\n";
							//$arrSQL[$injury->case_id][] = $sql;
							$arrSQLString[] = $sql;
							//assignees
							foreach($arrAssigneeID as $assign_uuid) {
								$sql = "INSERT INTO `" . $schema->schema_name . "`.`cse_" . $table_name . "_user` (`" . $table_name . "_user_uuid`, `" . $table_name . "_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`)
								VALUES ('" . $task_user_uuid  ."', '" . $table_uuid . "', '" . $assign_uuid . "', 'to', '" . $last_updated_date . "', 'SYSTEM', '" . $trig->cus_id . "');";
								//echo $sql . "\r\n";
								//$arrSQL[$injury->case_id][] = $sql;
							$arrSQLString[] = $sql;
							}
							
							//attach trigger
							$personal_injury_uuid = uniqid("KA", false);
							
							$sql = "INSERT INTO `" . $schema->schema_name . "`.`cse_" . $attach_table . "injury_trigger` (`" . $attach_table . "injury_trigger_uuid`, `" . $attach_table . "injury_uuid`, `trigger_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
								VALUES ('" . $personal_injury_uuid  ."', '" . $injury_uuid . "', '" . $trig->trigger_uuid . "', '" . $trig->trigger_actual . "', '" . $last_updated_date . "', 'SYSTEM', '" . $trig->cus_id . "');";
							
							//echo $search_sql . "\r\n";
							
							//$arrSQL[$injury->case_id][] = $sql;
							$arrSQLString[] = $sql;
							
							$note = $schema->schema_name . " - " . date("Y-m-d H:i:s") . ":" . $trig->trigger_uuid . "] applied to " . $injury->case_id . "
";;
							
							$fp = fopen('trigger_data.txt', 'a');
							fwrite($fp, $note);
							fwrite($fp, '\r\n');
							fclose($fp);
							//echo "\r\ninsert " . $table_name . " = '" . $trigger_date . "\r\n";
							
							//print_r($arrAssignee);
						}
					}
				}
				if (count($arrSQLString) > 0) {
					$sql = implode("\r\n\r\n", $arrSQLString);
								
					//reset
					$arrSQLString = array();
					echo "\r\nINSERTS " . $schema->schema_name . " case id:" . $injury->case_id . " for " . $case_type . "\r\n";
					//echo $sql . "\r\n\r\n";
					//die();
					$stmt = DB::run($sql);
				} else {
					echo "\r\nNO DATA for " . $schema->schema_name . " case id:" . $injury->case_id . " for " . $case_type . "\r\n";
					//die();
				}
			}
		}
	}
	/*
	echo "INSERTS:\r\n\r\n";
	
	foreach($arrSQL as $case_id=>$arrInserts) {
		echo implode("\r\n\r\n", $arrInserts);
	}
	*/
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}
die("done " . date("H:i:s"));
