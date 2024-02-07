<?php
function searchKases($search_term, $modifier = "") {
	$search_term = clean_html($search_term);
	$search_term = str_replace("_", " ", $search_term);
	$search_term = trim($search_term);
	
	if ($modifier != "closed") {
		if (strlen($search_term) < 2 && strpos($modifier, "starts_with") === false) {
			return false;
			//getKases();
		}
	} else {
		//there are much more closed than opened
		if (strlen($search_term) < 2) {
			return false;
			//getKases();
		}
	}
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	//re-initialize the filters
	$_SESSION["filter_attorney"] = "";
	$_SESSION["filter_worker"] = "";
	
	$blnModifiedSearch = false;
	$sql = "SELECT DISTINCT 
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, -1) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(jet.jetfile_case_id, '-1') jetfile_case_id,
			IFNULL(jet.jetfile_dor_id, '-1') jetfile_dor_id,
			IFNULL(jet.jetfile_dore_id, '-1') jetfile_dore_id,
			IFNULL(jet.jetfile_lien_id, '-1') jetfile_lien_id,
			IFNULL(jet.app_filing_id, '-1') app_filing_id,
			IFNULL(jet.dor_filing_id, '-1') dor_filing_id,
			IFNULL(jet.dore_filing_id, '-1') dore_filing_id,
			IFNULL(jet.lien_filing_id, '-1') lien_filing_id
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
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
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
			LEFT OUTER JOIN `cse_corporation` `employer`
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp2
			ON (ccase.case_uuid = ccorp2.case_uuid  AND ccorp2.attribute = 'carrier' AND ccorp2.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `carrier`
			ON ccorp2.corporation_uuid = carrier.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp3
			ON (ccase.case_uuid = ccorp3.case_uuid  AND ccorp3.attribute = 'defense' AND ccorp3.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `defense`
			ON ccorp3.corporation_uuid = defense.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp4
			ON (ccase.case_uuid = ccorp4.case_uuid  AND ccorp4.attribute = 'client' AND ccorp4.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `client`
			ON ccorp4.corporation_uuid = client.corporation_uuid";
			
			if ($modifier=="doctors" || $modifier=="medical_provider" || $modifier=="employee") {
				$sql .= "
				INNER JOIN `cse_case_corporation` ccorp_medical
				ON (
					ccase.case_uuid = ccorp_medical.case_uuid  
					AND ccorp_medical.attribute = 'medical_provider' 
					AND ccorp_medical.deleted = 'N'
				)
				INNER JOIN `cse_corporation` `medical_provider`
				ON ccorp_medical.corporation_uuid = medical_provider.corporation_uuid
				";
			}
	if ($modifier!="return_query" && $modifier!="employer" && $modifier!="carrier" && $modifier!="employee" && $modifier!="") {
		$sql .= "
		LEFT OUTER JOIN `cse_case_corporation` `ccorp_" . $modifier . "`
		ON (ccase.case_uuid = `ccorp_" . $modifier . "`.case_uuid  AND `ccorp_" . $modifier . "`.attribute = '" . $modifier . "' AND `ccorp_" . $modifier . "`.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` `" . $modifier . "`
		ON `ccorp_" . $modifier . "`.corporation_uuid = `" . $modifier . "`.corporation_uuid";
	}
	$sql .= "
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			WHERE ccase.deleted ='N' 
			AND IF (inj.deleted IS NULL, 'N' , inj.deleted) = 'N'";
	if ($modifier!="") {
		switch($modifier) {
			case "open":
				$sql .= " 
				AND ccase.case_status LIKE '%OP%'";
				break;
			case "closed":
			case "close":
				$sql .= " 
				AND ccase.case_status LIKE '%CLOSE%'";
				break;
			case "sol":
				$sql .= " 
				AND inj.statute_limitation = '" . date("Y-m-d", strtotime($search_term)) . "'";
				break;
			case "starts_with_first":
				$sql .= " 
				AND app.first_name LIKE '" . addslashes($search_term) . "%'";
				break;
			case "starts_with_last":
				$sql .= " 
				AND app.last_name LIKE '" . addslashes($search_term) . "%'";
				break;
			case "subout":
				$sql .= " 
				AND ccase.case_status LIKE '%SUB%'";
				$sql .= " 
				AND ccase.case_status NOT LIKE '%CLOSE%'";
				break;
			case "doctors":
				$sql .= " 
				AND (medical_provider.company_name LIKE '" . addslashes($search_term) . "%'";
				$sql .= " 
				OR medical_provider.full_name LIKE '" . addslashes($search_term) . "%'";
				$sql .= " 
				OR medical_provider.last_name LIKE '" . addslashes($search_term) . "%')";
				break;
			default:
				if ($modifier!="return_query" && $modifier!="employee" && $modifier!="") {
					//look up the company name
					if ($modifier!="applicant") {
						$corp = getCorporationInfo($search_term);
						if (is_object($corp)) {
							$sql .= " 
							AND (`" . $modifier . "`.company_name = '" . addslashes($corp->company_name) . "')";
						} else {
							$sql .= " 
							AND (`" . $modifier . "`.parent_corporation_uuid = '" . $search_term . "')";
						}
					} else {
						$sql .= " 
							AND (`app`.parent_person_uuid = '" . addslashes($search_term) . "')";
					}
					$blnModifiedSearch = true;
				}
				break;
		}
	} else {	
		//per steve, search all even closed 3/31/2017
		if ($_SESSION['user_customer_id']!=1075) {
			$sql .= " 
			AND ccase.case_status NOT LIKE '%CLOSE%'";
		}
	}
	$sql .= " 
	AND ccase.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " 
	AND (inj.customer_id = " . $_SESSION['user_customer_id'] . " OR inj.customer_id IS NULL) ";
	//search now
	
	//check if posted that this is a parent_corporation_uuid search
	if ($modifier!="employer" && $modifier!="medical_provider" && $modifier!="doctors" && $modifier!="carrier" && $modifier!="defense" && $modifier!="employee" && $modifier!="client" && $modifier!="sol" && !$blnModifiedSearch) {
		$sql .= " AND (";
		
		$blnValidDate = false;
		if (strlen($search_term)==10) {
			$date = str_replace("-", "/", $search_term);
			$arrDate = explode("/", $date);
			//die(print_r($arrDate));
			$blnValidDate = false;
			if (count($arrDate) > 2) {
				if (strlen($arrDate[2])==4) {
					$date = $arrDate[2] . "-" . $arrDate[0] . "-" . $arrDate[1];
				}
				$blnValidDate = isValidDate($date, "Y-m-d");
			}
		}
		
		if (strlen($search_term)==10 && $blnValidDate) {
			$sql .= "(
				app.`dob` = '" . date("Y-m-d", strtotime(str_replace("-", "/", $search_term))) . "'
				OR
				app.`dob` = '" . $search_term . "'
				OR
				app.`dob` = '" . date("m/d/Y", strtotime(str_replace("-", "/", $search_term))) . "'
			)
			OR inj.`start_date` = '" . date("Y-m-d", strtotime(str_replace("-", "/", $search_term))) . "'
			OR inj.`end_date` = '" . date("Y-m-d", strtotime(str_replace("-", "/", $search_term))) . "')";
		} else {
			$arrFullName = explode(" ", $search_term);
			$first_name = $search_term;
			$last_name = $search_term;
			if (count($arrFullName) > 1) {
				$first_name = $arrFullName[0];
				$last_name = $arrFullName[count($arrFullName) - 1];
				$sql .= "
				(app.first_name LIKE '%" . addslashes($first_name) . "%'
				AND app.last_name LIKE '%" . addslashes($last_name) . "%')";
			} else {
				$sql .= "
				app.first_name LIKE '%" . addslashes($search_term) . "%'
				OR app.last_name LIKE '%" . addslashes($search_term) . "%'";			
			}
			$sql .= "
			OR app.aka LIKE '%" . addslashes($search_term) . "%'
			OR app.full_name LIKE '%" . addslashes($search_term) . "%'
			OR app.phone LIKE '%" . addslashes($search_term) . "%'
			OR app.work_phone LIKE '%" . addslashes($search_term) . "%'
			OR app.cell_phone LIKE '%" . addslashes($search_term) . "%'
			OR app.email LIKE '%" . addslashes($search_term) . "%'
			OR app.work_email LIKE '%" . addslashes($search_term) . "%'
			OR app.full_address LIKE '%" . addslashes($search_term) . "%'
			OR employer.`company_name` LIKE '%" . addslashes($search_term) . "%'
			OR defense.`company_name` LIKE '%" . addslashes($search_term) . "%'
			OR inj.`occupation` LIKE '%" . addslashes($search_term) . "%'";
			if (is_numeric($search_term)) {
				$sql .= " 
				OR ccase.case_id = '" . addslashes($search_term) . "'
				OR app.ssn_last_four LIKE '%" . addslashes($search_term) . "%'
				OR ccase.cpointer LIKE '%" . addslashes($search_term) . "%'";
			}
			if ($modifier!="return_query" && $modifier!="employee" && $modifier!="") {
				$sql .= " 
				OR `" . $modifier . "`.parent_corporation_uuid = '" . $search_term . "'";
			}
			$sql .= " 
			OR ccase.case_number LIKE '%" . addslashes($search_term) . "%'
			OR ccase.case_name LIKE '%" . addslashes($search_term) . "%'
			OR ccase.file_number LIKE '%" . addslashes($search_term) . "%'
				OR inj.adj_number LIKE '%" . addslashes($search_term) . "%'
			)";
		}
		
		if (strlen($search_term)==9) {
			$sql .= " 
			OR app.ssn = '" . addslashes($search_term) . "'";
		}
	}
	
	if (isset($_SESSION["search_employee_name"])) {
		$sql .= " 
		 AND `" . $_SESSION["search_partie_type"] . "`.full_name = '" . addslashes($_SESSION["search_employee_name"]) . "'";
		unset($_SESSION["search_employee_name"]);
		unset($_SESSION["search_partie_type"]);
		//die($sql);
	}
	if ($modifier=="medical_provider") {
		$sql .= "
		UNION 
		SELECT DISTINCT 
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, -1) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(jet.jetfile_case_id, '-1') jetfile_case_id,
			IFNULL(jet.jetfile_dor_id, '-1') jetfile_dor_id,
			IFNULL(jet.jetfile_dore_id, '-1') jetfile_dore_id,
			IFNULL(jet.jetfile_lien_id, '-1') jetfile_lien_id,
			IFNULL(jet.app_filing_id, '-1') app_filing_id,
			IFNULL(jet.dor_filing_id, '-1') dor_filing_id,
			IFNULL(jet.dore_filing_id, '-1') dore_filing_id,
			IFNULL(jet.lien_filing_id, '-1') lien_filing_id
			FROM cse_case ccase  
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `employer`
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp2
			ON (ccase.case_uuid = ccorp2.case_uuid  AND ccorp2.attribute = 'carrier' AND ccorp2.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `carrier`
			ON ccorp2.corporation_uuid = carrier.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp3
			ON (ccase.case_uuid = ccorp3.case_uuid  AND ccorp3.attribute = 'defense' AND ccorp3.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `defense`
			ON ccorp3.corporation_uuid = defense.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp4
			ON (ccase.case_uuid = ccorp4.case_uuid  AND ccorp4.attribute = 'client' AND ccorp4.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `client`
			ON ccorp4.corporation_uuid = client.corporation_uuid
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
            
            INNER JOIN (
				SELECT DISTINCT ccase.case_uuid
				FROM cse_person_corporation cpc
				INNER JOIN cse_corporation corp
				ON cpc.corporation_uuid = corp.corporation_uuid

				INNER JOIN cse_case_corporation ccc
				ON corp.corporation_uuid = ccc.corporation_uuid

				INNER JOIN cse_case ccase
				ON ccc.case_uuid = ccase.case_uuid
				WHERE corp.parent_corporation_uuid = '" . addslashes($search_term) . "'
            ) prior_cases
            ON ccase.case_uuid = prior_cases.case_uuid
			WHERE ccase.deleted ='N' 
			AND IF (inj.deleted IS NULL, 'N' , inj.deleted) = 'N' 
            AND ccase.customer_id = " . $_SESSION['user_customer_id'] . " AND (inj.customer_id = " . $_SESSION['user_customer_id'] . " OR inj.customer_id IS NULL)  
            ORDER BY IF (TRIM(first_name) = '', TRIM(full_name), first_name), last_name, case_id, injury_number";
		//die($sql);
	} else {
		//$sql .= " ORDER BY IF (TRIM(app.last_name) = '', TRIM(app.full_name), TRIM(app.last_name)), app.last_name, ccase.case_id, inj.injury_number";
		if ($_SESSION["user_customer_id"]!=1121) {	
			$sql .= " ORDER BY 
				TRIM(IFNULL(
					CONCAT(app.first_name,
					' ',
					app.last_name,
					' vs ',
					IFNULL(employer.`company_name`, ''),
					' - ',
					REPLACE(IF(DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL,
							'',
							DATE_FORMAT(inj.start_date, '%m/%d/%Y')),
						'00/00/0000',
						'')),
					ccase.case_name)), 
				case_id, injury_number
			";
		} else {
			//special case goldberg 08/29/2018 per thomas
			$sql .= " ORDER BY 
			TRIM(CONCAT(app.first_name, ' ', app.last_name)), case_id,
			IF(INSTR(file_number, '*') > 0, 'A', 'B'), injury_number";
		}
	}
	if ($modifier=="return_query") {
		//just return the query
		return $sql;
	}
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.59.9') {
		//echo $thirtyfive_days . "\r\n";
		//die($sql);
	}	
	return $sql;
}
function getKases($limit = " LIMIT 0, 1000", $filter = "", $output = "json") {
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	
	$script_filename = $_SERVER['SCRIPT_FILENAME'];
	$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
	$host = $arrScript[count($arrScript)-1]; 
	
	//specific ids
	$search_ids = "";
	if (isset($_SESSION["kase_ids"])) {
		$search_ids = $_SESSION["kase_ids"];
		$_SESSION["kase_ids"] = "";
		unset($_SESSION["kase_ids"]);
	}
	
	$wcab_only = false;
	$pi_only = false;
	$subout_equal = "";
	if (isset($_SESSION["inactive_type"])) {
		$subout_equal = " != ";
		$wcab_only = ($_SESSION["inactive_type"] == "wcab");
		$pi_only = ($_SESSION["inactive_type"] == "pi");
		
		unset($_SESSION["inactive_type"]);
		
		if (isset($_SESSION["inactive_subout"])) {
			$subout_equal = " = ";
			unset($_SESSION["inactive_subout"]);
		}
	}
	
	$blnIntake = false;
    $sql = "SELECT DISTINCT 
			inj.injury_id id, '-2' `previous_kases`, '-2' `start_kases`, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.injury_number, inj.adj_number, ccase.rating, ccase.injury_type, ccase.sub_in, inj.`type` main_injury_type,
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, IFNULL(venue_abbr, '') venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.`corporation_uuid` defendant_uuid, defendant.`company_name` defendant, defendant.`full_address` defendant_full_address,
			IFNULL(plaintiff.`corporation_id`,-1) plaintiff_id, plaintiff.`corporation_uuid` plaintiff_uuid, plaintiff.`company_name` plaintiff, plaintiff.`full_address` plaintiff_full_address,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, TRIM(ccase.case_name) case_name, 
			att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, -1) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(referring.`corporation_id`,-1) referring_id, referring.`corporation_uuid` referring_uuid, referring.`company_name` referring, referring.`full_address` referring_full_address, 			
			IFNULL(REPLACE(`referring`.`company_name`, ' ', ''), '') referring_search,
			IFNULL(closed_cases.closed_date, '') closed_date";
			
			if ($filter=="recent") {
				$sql .= ", recent.time_stamp recent_time_stamp";
			}
			$sql .= "
			FROM cse_case ccase ";
			/*
			
			*/
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
			
			if (isset($_SESSION["unattended_query"])) {
				$sql .= $_SESSION["unattended_query"];
				unset($_SESSION["unattended_query"]);
			}
			if (isset($_SESSION["inactive_query"])) {
				$sql .= $_SESSION["inactive_query"];
				unset($_SESSION["inactive_query"]);
			}
			if ($filter=="examiner") {
				$filter = "";
				if (isset($_SESSION["examiner_case_id"])) {
					$examiner_case_id = $_SESSION["examiner_case_id"];
					
					if ($examiner_case_id!="") {
						//$restricted_clients = "'" . str_replace(",", "','", $restricted_clients) . "'";
						$sql .= " INNER JOIN (
								SELECT 
									ccorp.case_uuid
								FROM
									`cse_corporation` corp
										INNER JOIN
									cse_case_corporation ccorp ON corp.corporation_uuid = ccorp.corporation_uuid
								WHERE
									corp.deleted = 'N'
										AND corp.corporation_id = " . $_SESSION["examiner_carrier_id"] . "
										AND corp.customer_id = '" . $_SESSION['user_customer_id'] . "'
										AND corp.`full_name` = '" . addslashes($_SESSION["examiner_name"]) . "'
							) restricteds
							ON ccase.case_uuid = restricteds.case_uuid";
					}
				}
			}
			if ($search_ids == "") {
				$sql .= " 
				INNER JOIN (
					SELECT case_id 
					FROM cse_case ";
				
				if ($filter!="closed" && $filter!="show_all") {
					$sql .= "WHERE case_status NOT LIKE '%close%' AND case_status NOT LIKE 'CL%' AND case_status != 'DROPPED' AND case_status != 'REJECTED'";
					
				}
				if ($filter=="intake") {
					$blnIntake = true;
					$sql .= " AND case_status = 'Intake' ";
				}
				if ($filter=="closed") {
					$sql .= "WHERE (case_status LIKE '%close%' OR case_status LIKE 'CL%' OR case_status = 'DROPPED')";
				}
				if ($filter=="pi") {
					$sql .= " AND case_type NOT LIKE 'WC%' AND case_type NOT LIKE 'W/C%' AND case_type NOT LIKE 'Worker%' ";
				}
				if ($filter=="wc") {
					$sql .= " AND (case_type LIKE 'WC%' OR case_type LIKE 'W/C%' OR case_type LIKE 'Worker%') ";
				}
				if ($filter=="show_all") {
					$sql .= "WHERE 1";
				}
				if ($filter=="closed" || $filter=="show_all" || $filter=="pi" || $filter=="wc") {
					//we don't want it going forward
					$filter = "";
				}
				$sql .= "
				) climit
				ON ccase.case_id = climit.case_id
				";
			}
			
			$sql .= "
			LEFT OUTER JOIN (
				SELECT cct.case_id, cct.case_status, MIN(time_stamp) closed_date
				FROM cse_case_track cct
				WHERE (case_status LIKE '%close%' OR case_status LIKE 'CL%' OR case_status = 'DROPPED') 
				AND operation = 'update'
				GROUP BY cct.case_id
            ) closed_cases
            ON ccase.case_id = closed_cases.case_id
			
			LEFT OUTER JOIN `cse_case_corporation` rcorp
			ON (ccase.case_uuid = rcorp.case_uuid AND rcorp.attribute = 'referring' AND rcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` referring
			ON rcorp.corporation_uuid = referring.corporation_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
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
			LEFT OUTER JOIN `cse_case_corporation` ecorp
			ON (ccase.case_uuid = ecorp.case_uuid AND ecorp.attribute = 'defendant' AND ecorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` defendant
			ON ecorp.corporation_uuid = defendant.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` pcorp
			ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` plaintiff
			ON pcorp.corporation_uuid = plaintiff.corporation_uuid
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			";
	$recent_sort = "";
	$blnRecent = false;
	if ($filter=="recent") {
		$blnRecent = true;
		$sql .= " 
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
		LIMIT 0 , 15
		) recent
		ON ccase.case_id = recent.case_id";
		$filter = "";
		$recent_sort = " ORDER BY recent.time_stamp DESC";	
		$limit = " LIMIT 0, 25";
	}
	$additional_where = "";
	if ($filter=="no_tasks") {
		$sql .= " LEFT OUTER JOIN (
			SELECT DISTINCT ccase.case_id
			FROM cse_case ccase
			INNER JOIN cse_case_task cct
			ON ccase.case_uuid = cct.case_uuid
			INNER JOIN cse_task task
			ON cct.task_uuid = task.task_uuid
			WHERE task.deleted = 'N'
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
			) case_tasks
			ON ccase.case_id = case_tasks.case_id";
			$additional_where = $filter;
			$filter = "";
	}
	$blnIntake = false;
	$sql .= " 
	WHERE ccase.deleted ='N' ";
	if ($filter=="intake") {
		$blnIntake = true;
		$sql .= " AND ccase.case_status = 'Intake' ";
		$filter = "";
	}
	$sql .= " AND ccase.customer_id = " . $_SESSION['user_customer_id'] . $filter;
	if ($additional_where=="no_tasks") {
		$sql .= " 
		AND case_tasks.case_id IS NULL
		";	
	}
	
	if ($search_ids != "") {
		$sql .= " 
		AND ccase.case_id IN (" . $search_ids . ")
		";	
	}
	if ($pi_only) {
		$sql .= " AND ccase.case_type NOT LIKE 'WC%' 
		AND ccase.case_type NOT LIKE 'W/C%' 
		AND ccase.case_type NOT LIKE 'Worker%' ";
	}
	if ($wcab_only) {
		$sql .= " AND (ccase.case_type LIKE 'WC%' 
		OR ccase.case_type LIKE 'W/C%' 
		OR ccase.case_type LIKE 'Worker%') ";
		
		if ($subout_equal!="") {
			if ($subout_equal == " = ") {
				$sql .= " AND (ccase.case_status ". $subout_equal . " 'OP-SUBOUT'";
				$sql .= " OR ccase.case_status ". $subout_equal . " 'Sub')";
			}
			if ($subout_equal == " != ") {
				$sql .= " AND ccase.case_status ". $subout_equal . " 'OP-SUBOUT'";
				$sql .= " AND ccase.case_status ". $subout_equal . " 'Sub'";
			}
		}
	}
	
	$sort_by = "";
	if (isset($_GET["sort_by"])){
		$sort_by = $_GET["sort_by"];
		if ($sort_by=="last_name") {
			$sql .= " ORDER BY TRIM(app.last_name), TRIM(app.first_name), ccase.case_id, inj.injury_number";
		}
	}

	if ($recent_sort != "") {
		$sql .= $recent_sort;
		//die($sql);
	} else {
		if ($sort_by == "" && $search_ids == "") {
			$sql .= " ORDER BY IFNULL(IF (TRIM(IFNULL(app.last_name, '')) = '', IFNULL(TRIM(app.full_name), ccase.case_name), TRIM(app.last_name)), IFNULL(plaintiff.`company_name`, '')), 
			ccase.case_id, inj.injury_number";
		}
	}
	
	if ($search_ids != "") {
		$sql .= " ORDER BY ccase.case_id DESC";
	}
	
	$sql .= "
	" . $limit;
	
	return $sql;
}
?>