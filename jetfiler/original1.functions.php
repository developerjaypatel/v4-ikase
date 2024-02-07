<?php
function getInjuryInfo($injury_id) {
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($injury_id=="" || $injury_id < 0) {
		return false;
		$error = array("error"=> array("text"=>"no valid id", "injury_id"=>$injury_id));
        echo json_encode($error);
		die();
	}

	//return a single record
	$blnSpecific = false;
	$sql = " SELECT inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id,
			IFNULL(occ.occupation_id, -1) occupation_id,
			IFNULL(main_case_id, ccase.case_id) `main_case_id`, IFNULL(main_case_uuid, ccase.case_uuid) `main_case_uuid`
			FROM `cse_injury` inj 
			LEFT OUTER JOIN `cse_injury_occupation` iocc
			ON inj.injury_uuid = iocc.injury_uuid
			LEFT OUTER JOIN `ikase`.`cse_occupation` occ
			ON iocc.occupation_uuid = occ.occupation_uuid
			INNER JOIN cse_case_injury ccinj
			ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.deleted = 'N'
			INNER JOIN cse_case ccase
			ON ccinj.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN (
				SELECT ccinj.attribute, ccasemain.case_uuid main_case_uuid, ccasemain.case_id main_case_id, 
				ccinj.case_uuid related_case_uuid, ccase.case_id related_case_id, inj.*   
				FROM `cse_injury` inj 
				
				INNER JOIN cse_case_injury ccinj
				ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.`deleted` = 'N' AND ccinj.attribute = 'related'
				INNER JOIN cse_case ccase
				ON ccinj.case_uuid = ccase.case_uuid
				
				INNER JOIN cse_case_injury ccmain
				ON inj.injury_uuid = ccmain.injury_uuid AND ccmain.`deleted` = 'N' AND ccmain.attribute = 'main'
				INNER JOIN cse_case ccasemain
				ON ccmain.case_uuid = ccasemain.case_uuid
			) maininjury
			ON inj.injury_uuid = maininjury.injury_uuid
			
            WHERE 1";

	$blnSpecific = false;
	 if ($injury_id>0) {
		$blnSpecific = true;
		$sql .= " AND inj.injury_id = :injury_id";
	}
	$sql .= " AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
	AND ccase.deleted = 'N'
	AND inj.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($injury_id>0) {
			$stmt->bindParam("injury_id", $injury_id);
		}
		$stmt->execute();
		$injury = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		return $injury;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfo($id) {
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.special_instructions, ccase.case_number, ccase.file_number, ccase.cpointer,inj.adj_number,
			ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type,
			
			venue_corporation.corporation_id venue_id, venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
			venue_corporation.street venue_street, venue_corporation.city venue_city, 
			venue_corporation.state venue_state, venue_corporation.zip venue_zip,
			
			ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.case_language `case_language`, 
			app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, IFNULL(app.full_name, '') `full_name`, app.first_name, app.last_name, app.middle_name, app.`aka`, 
			app.dob, app.gender, app.ssn, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
			
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
			IFNULL(settlement.settlement_id, -1) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
			job.job_id worker_job_id, job.job_uuid worker_job_uuid, if(job.job IS NULL, '', job.job) worker_job
			
			FROM cse_case ccase ";

			if (isset($_SESSION["restricted_clients"])) {
				$restricted_clients = $_SESSION["restricted_clients"];
				
				if ($restricted_clients!="") {
					//$restricted_clients = "'" . str_replace(",", "','", $restricted_clients) . "'";
					$sql .= " INNER JOIN (
							SELECT DISTINCT ccorp.case_uuid
							FROM cse_case_corporation ccorp
							INNER JOIN cse_corporation corp
							ON ccorp.corporation_uuid = corp.corporation_uuid
							where corp.parent_corporation_uuid IN (" . $restricted_clients . ")
						) restricteds
						ON ccase.case_uuid = restricteds.case_uuid";
				}
			}
			
			$sql .= " 
			LEFT OUTER JOIN cse_case_person ccapp ON (ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
			LEFT OUTER JOIN ";
if (($_SESSION['user_customer_id']==1033)) { 
	$sql .= "(" . SQL_PERSONX . ")";
} else {
	$sql .= "cse_person";
}
$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` dcorp
			ON (ccase.case_uuid = dcorp.case_uuid AND ccorp.attribute = 'defendant' AND dcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` defendant
			ON dcorp.corporation_uuid = defendant.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp_venue
			ON (ccase.case_uuid = ccorp_venue.case_uuid AND ccorp_venue.attribute = 'venue' AND ccorp_venue.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` venue_corporation
			ON ccorp_venue.corporation_uuid = venue_corporation.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			
			where ccase.case_id=:id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($id > 0) {
			$stmt->bindParam("id", $id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
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
?>