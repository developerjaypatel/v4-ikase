<?php
$app->get('/injury/:case_id/:injury_id', authorize('user'),	'getInjury');
$app->get('/injuryinfo/:case_id/:injury_id', authorize('user'),	'getInjuryInfo');
$app->get('/injury_kase/:case_id', authorize('user'), 'getKaseInjuries');
$app->get('/occupation', authorize('user'), 'searchOccupation');

//posts
$app->post('/injury/delete', authorize('user'), 'deleteInjury');
$app->post('/injury/add', authorize('user'), 'addInjury');
$app->post('/injury/update', authorize('user'), 'updateInjury');

$app->post('/injury/field/update', authorize('user'), 'updateInjuryField');

function getAllDOIS() {
	session_write_close();
	if (isset($_SESSION["dois_sql"])) {
		$sql = $_SESSION["dois_sql"];
		//die($sql);
	} else {
		$sql = "SELECT DISTINCT inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, -1) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
			IFNULL(main_case_id, ccase.case_id) `main_case_id`, IFNULL(main_case_uuid, ccase.case_uuid) `main_case_uuid`,
			IFNULL(main_case_number, IF(ccase.case_number='', ccase.file_number, ccase.case_number)) `case_number`,
			ccase.file_number,
			FORMAT((DATEDIFF(inj.statute_limitation, IF(inj.end_date='0000-00-00', inj.start_date, inj.end_date)) / 365), 0) statute_years,
			IFNULL(ven.venue_uuid, '') venue_uuid, IFNULL(ven.venue, '') venue, IFNULL(ven.venue_abbr, '') venue_abbr
			FROM `cse_injury` inj 
					
			LEFT OUTER JOIN `cse_injury_venue` iven
			ON inj.injury_uuid = iven.injury_uuid AND iven.deleted = 'N'
			
			LEFT OUTER JOIN `cse_venue` ven
			ON iven.venue_uuid = ven.venue_uuid
			
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid AND cil.deleted = 'N'
			
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N'
			
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid	
			
			INNER JOIN cse_case_injury ccinj
			ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.`deleted` = 'N'
			
			INNER JOIN cse_case ccase
			ON ccinj.case_uuid = ccase.case_uuid ";
	
		if (isset($_SESSION["restricted_clients"])) {
			$restricted_clients = $_SESSION["restricted_clients"];
			
			if ($restricted_clients!="") {
				//$restricted_clients = "'" . str_replace(",", "','", $restricted_clients) . "'";
				$sql .= " 
				INNER JOIN (
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
			INNER JOIN (
				SELECT case_id 
				FROM cse_case 
				WHERE case_status != 'Closed'
				AND case_status != 'Closed by C & R'
				AND case_status != 'Closed by Stipulation'
				AND deleted ='N' 
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				ORDER BY case_date DESC 
				LIMIT 0, 2000
			) climit
			ON ccase.case_id = climit.case_id";
			
		$sql .= " LEFT OUTER JOIN (
					SELECT ccinj.attribute, ccasemain.case_uuid main_case_uuid, ccasemain.case_id main_case_id, 
					ccasemain.case_number main_case_number, 
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
					WHERE ccasemain.deleted = 'N'
				) maininjury
				ON inj.injury_uuid = maininjury.injury_uuid";	
				
		$sql .= " WHERE 1
				AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
				AND ccase.deleted = 'N'
				AND ccase.case_status NOT LIKE '%close%'
				AND inj.deleted = 'N'
				ORDER BY main_case_id, inj.injury_number ASC";
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$injuries = $stmt->fetchAll(PDO::FETCH_OBJ);
		echo json_encode($injuries);     				
		$stmt->closeCursor(); $stmt = null; $db = null;
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getInjury($case_id, $injury_id) {
	session_write_close();
	if ($case_id < 0 && $injury_id < 0) {
		getAllDOIS();
		return;
	}
	if ($case_id < 0) {
		$error = array("error"=> array("text"=>"no valid id", "case_id"=>$case_id, "injury_id"=>$injury_id));
        echo json_encode($error);
		die();
	}
		
	$sql = " SELECT inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id,
			IFNULL(occ.occupation_id, -1) occupation_id,
			IFNULL(main_case_id, ccase.case_id) `main_case_id`, 
			IFNULL(main_case_uuid, ccase.case_uuid) `main_case_uuid`,
			IFNULL(main_case_number, ccase.case_number) `case_number`,
			IFNULL(main_file_number, ccase.file_number) `file_number`,
			FORMAT((DATEDIFF(inj.statute_limitation, IF(inj.end_date='0000-00-00', inj.start_date, inj.end_date)) / 365), 0) statute_years,
			IFNULL(ven.venue_uuid, '') venue_uuid, IFNULL(ven.venue, '') venue, IFNULL(ven.venue_abbr, '') venue_abbr
			FROM `cse_injury` inj 
			
			LEFT OUTER JOIN `cse_injury_occupation` iocc
			ON inj.injury_uuid = iocc.injury_uuid
			
			LEFT OUTER JOIN `ikase`.`cse_occupation` occ
			ON iocc.occupation_uuid = occ.occupation_uuid
			
			LEFT OUTER JOIN `cse_injury_venue` iven
			ON inj.injury_uuid = iven.injury_uuid AND iven.deleted = 'N'
			
			LEFT OUTER JOIN `cse_venue` ven
			ON iven.venue_uuid = ven.venue_uuid
			
			INNER JOIN cse_case_injury ccinj
			ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.deleted = 'N'
			
			INNER JOIN cse_case ccase
			ON ccinj.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN (
				SELECT ccinj.attribute, ccasemain.case_uuid main_case_uuid, ccasemain.case_id main_case_id, 
				ccasemain.case_number main_case_number, ccasemain.file_number main_file_number,
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
				WHERE ccasemain.deleted = 'N'
			) maininjury
			ON inj.injury_uuid = maininjury.injury_uuid
			
            WHERE 1";
	 if ($case_id>0) {
		$sql .= " AND `ccase`.`case_id` = :case_id";
	}
	$blnSpecific = false;
	 if ($injury_id>0) {
		$blnSpecific = true;
		$sql .= " AND inj.injury_id = :injury_id";
	}
	 $sql .= " AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
	AND ccase.deleted = 'N'
	AND inj.deleted = 'N'";
	
	if ($case_id > 0 && $injury_id < 0) {
		$sql .= " 
		ORDER BY inj.start_date
		LIMIT 0, 1";
	}
	if ($case_id < 0 && $injury_id < 0) {
		$sql .= " 
		ORDER BY main_case_id, inj.injury_number ASC";
	}
	if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($case_id>0) {
			$stmt->bindParam("case_id", $case_id);
		}
		if ($injury_id>0) {
			$stmt->bindParam("injury_id", $injury_id);
		}
		$stmt->execute();
		if ($blnSpecific) {
			$injury = $stmt->fetchObject();
			echo json_encode($injury);     
		} else {
			$injuries = $stmt->fetchAll(PDO::FETCH_OBJ);
			echo json_encode($injuries);     
		}
				
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die($injury);   

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInjuriesInfo($case_id) {
	return getKaseInjuries($case_id, true);
}
function getKaseInjuries($case_id, $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	if ($case_id=="" || $case_id < 0) {
		$error = array("error"=> array("text"=>"no case_id"));
        echo json_encode($error);
		die();
	}
	
	try {
		$case_uuid = "";
		$injury_uuid = "";
		
		if ($case_id>0) {
			//get main injury_id
			$sql = "SELECT cci.injury_uuid 
			FROM cse_case_injury cci
			
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
			
			WHERE ccase.case_id = :case_id
			AND ccase.customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("case_id", $case_id);
			
			$stmt->execute();		
			$injury = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			$injury_uuid = $injury->injury_uuid;
			
			$kase = getKaseInfo($case_id);
			
			$case_uuid = $kase->uuid;
		}
		//we can return a specific injury, or return all injuries
		$blnSpecific = false;
		$sql = "SELECT DISTINCT IF(IFNULL(main_case_id, ccase.case_id)=6706, 0, 1) sorter, inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id, 
				IFNULL(employer.`company_name`, '') employer,  
				IFNULL(main_case_id, ccase.case_id) `main_case_id`, IFNULL(main_case_uuid, ccase.case_uuid) `main_case_uuid`, 
				IFNULL(main_case_number, IF(ccase.case_number='', ccase.file_number, ccase.case_number)) `case_number`,
				ccase.file_number, 
				IFNULL(settlement.settlement_id, -1) settlement_id,
				IFNULL(ven.venue_uuid, '') venue_uuid, IFNULL(ven.venue, '') venue, IFNULL(ven.venue_abbr, '') venue_abbr
				FROM `cse_injury` inj 
				INNER JOIN cse_case_injury ccinj
				ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.deleted = 'N'
				INNER JOIN cse_case ccase
				ON (ccinj.case_uuid = ccase.case_uuid";
				if ($case_id > 0) {
					$sql .= " AND `ccase`.`case_id` = :case_id";
					$sql .= " )";
				} else {
					$sql .= ")
					INNER JOIN (
						SELECT case_id 
						FROM cse_case 
						WHERE case_status NOT LIKE '%close%'
					) climit
					ON ccase.case_id = climit.case_id";
				}
				
		$sql .= " LEFT OUTER JOIN `cse_case_corporation` ccorp
				ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
				LEFT OUTER JOIN `cse_corporation` employer
				ON ccorp.corporation_uuid = employer.corporation_uuid
				LEFT OUTER JOIN (
					SELECT ccinj.attribute, ccasemain.case_uuid main_case_uuid, ccasemain.case_id main_case_id, 
					IF(ccasemain.case_number = '', ccasemain.file_number, ccasemain.case_number) main_case_number,
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
					WHERE ccasemain.deleted = 'N'
				) maininjury
				ON inj.injury_uuid = maininjury.injury_uuid
				
				LEFT OUTER JOIN `cse_injury_settlement` cis
				ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N'
				LEFT OUTER JOIN `cse_settlement` settlement
				ON cis.settlement_uuid = settlement.settlement_uuid		
								
				LEFT OUTER JOIN `cse_injury_venue` iven
				ON inj.injury_uuid = iven.injury_uuid AND iven.deleted = 'N'
				
				LEFT OUTER JOIN `cse_venue` ven
				ON iven.venue_uuid = ven.venue_uuid
				
				WHERE 1
					AND inj.customer_id = :customer_id
					AND ccase.deleted = 'N'
					AND inj.deleted = 'N'
					AND inj.injury_id NOT IN (
					SELECT injury_id 
					FROM cse_injury inj
					INNER JOIN cse_case_injury cinj
					ON inj.injury_uuid = cinj.injury_uuid
					INNER JOIN cse_case ccase
					ON cinj.case_uuid = ccase.case_uuid
					WHERE ccase.deleted = 'Y'
				)";
		if ($case_uuid=="") {
			$sql .= "
			ORDER BY IF(IFNULL(main_case_id, ccase.case_id)=:case_id, 0, 1), main_case_id ASC, inj.injury_number ASC";
		}
		if ($case_uuid!="") {
			$sql .= "
			
            UNION
            
            SELECT DISTINCT IF(IFNULL(main_case_id, ccase.case_id)=:case_id, 0, 1) sorter, inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id, 
			IFNULL(employer.`company_name`, '') employer,  
			IFNULL(main_case_id, ccase.case_id) `main_case_id`, IFNULL(main_case_uuid, ccase.case_uuid) `main_case_uuid`, 
			IFNULL(main_case_number, IF(ccase.case_number='', ccase.file_number, ccase.case_number)) `case_number`,
            ccase.file_number, 
			IFNULL(settlement.settlement_id, -1) settlement_id,
			IFNULL(ven.venue_uuid, '') venue_uuid, IFNULL(ven.venue, '') venue, IFNULL(ven.venue_abbr, '') venue_abbr
			FROM `cse_injury` inj 
			INNER JOIN cse_case_injury ccinj
			ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.deleted = 'N'
			INNER JOIN cse_case ccase
			ON (ccinj.case_uuid = ccase.case_uuid) 
            LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
            
            INNER JOIN (
				 SELECT cci.case_uuid 
                FROM cse_case_injury cci
				WHERE 1
                AND cci.case_uuid != '" . $case_uuid . "'
                AND cci.deleted = 'N'
                AND cci.injury_uuid = '" . $injury_uuid . "'
            ) relateds
            ON ccase.case_uuid = relateds.case_uuid
            
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
	 		LEFT OUTER JOIN (
				SELECT ccinj.attribute, ccasemain.case_uuid main_case_uuid, ccasemain.case_id main_case_id, 
				IF(ccasemain.case_number = '', ccasemain.file_number, ccasemain.case_number) main_case_number,
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
				WHERE ccasemain.deleted = 'N'
			) maininjury
			ON inj.injury_uuid = maininjury.injury_uuid
			
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N'
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid		
							
			LEFT OUTER JOIN `cse_injury_venue` iven
			ON inj.injury_uuid = iven.injury_uuid AND iven.deleted = 'N'
			
			LEFT OUTER JOIN `cse_venue` ven
			ON iven.venue_uuid = ven.venue_uuid
			
			WHERE 1
				AND IFNULL(main_case_id, ccase.case_id) != :case_id
				AND inj.customer_id = :customer_id
				AND ccase.deleted = 'N'
				AND inj.deleted = 'N'
				AND inj.injury_id NOT IN (
				SELECT injury_id 
                FROM cse_injury inj
                INNER JOIN cse_case_injury cinj
                ON inj.injury_uuid = cinj.injury_uuid
                INNER JOIN cse_case ccase
                ON cinj.case_uuid = ccase.case_uuid
                WHERE ccase.deleted = 'Y'
            )


			ORDER BY sorter ASC, injury_number ASC";
		}
		/*
		if ($_SERVER['REMOTE_ADDR']=='71.119.40.148b') {
			$sql = " SELECT DISTINCT inj.*, inj.injury_id id, inj.injury_uuid uuid,
				IFNULL(main_case_id, ccase.case_id) `main_case_id`, IFNULL(main_case_uuid, ccase.case_uuid) `main_case_uuid`
				FROM `cse_injury` inj 
				INNER JOIN cse_case_injury ccinj
				ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.deleted = 'N'
				INNER JOIN cse_case ccase
				ON ccinj.case_uuid = ccase.case_uuid
				INNER JOIN (
					SELECT DISTINCT ccase.case_id, ccase.case_uuid 
					FROM  cse_case_injury cci
					INNER JOIN cse_case ccase
					ON cci.case_uuid = ccase.case_uuid
						
					INNER JOIN (
					SELECT injury_uuid 
						FROM cse_case_injury cinj 
						INNER JOIN cse_case ccase
						ON cinj.case_uuid = ccase.case_uuid
						where case_id = :case_id
					) main_injury
					ON cci.injury_uuid = main_injury.injury_uuid            
				) related_cases
				on ccase.case_uuid = related_cases.case_uuid
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
				WHERE ccasemain.deleted = 'N'
			) maininjury
			ON inj.injury_uuid = maininjury.injury_uuid
			WHERE 1
				AND IFNULL(main_case_id, ccase.case_id)  != :case_id
				AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
				AND ccase.deleted = 'N'
				AND inj.deleted = 'N'
				ORDER BY main_case_id ASC, inj.injury_number ASC";
			//
		}
		*/
		/*
		if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
			die($sql);
		}
		*/
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		if ($case_id>0) {
			$stmt->bindParam("case_id", $case_id);
		}
		$stmt->execute();		
		$injuries = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		
		if ($blnReturn) {
			return $injuries;
		} else {
			echo json_encode($injuries);   
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getInjuriesInfo($case_id) {
	session_write_close();
	if ($case_id < 0) {
		$error = array("error"=> array("text"=>"no valid id", "case_id"=>$case_id));
        echo json_encode($error);
		die();
		//return false;
	}
	//we can return a specific injury, or return all injuries
	$blnSpecific = false;
    $sql = "SELECT inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id,
			IFNULL(main_case_id, ccase.case_id) `main_case_id`, IFNULL(main_case_uuid, ccase.case_uuid) `main_case_uuid`,
			FORMAT((DATEDIFF(inj.statute_limitation, inj.start_date) / 365), 0) statute_years
			FROM `cse_injury` inj 
			INNER JOIN cse_case_injury ccinj
			ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.deleted = 'N'
			INNER JOIN cse_case ccase
			ON (ccinj.case_uuid = ccase.case_uuid";
			if ($case_id > 0) {
				$sql .= " AND `ccase`.`case_id` = :case_id";
				$sql .= " )";
			} else {
				$sql .= ")
				INNER JOIN (
					SELECT case_id 
					FROM cse_case 
					WHERE case_status NOT LIKE '%close%'
				) climit
				ON ccase.case_id = climit.case_id";
			}
			
	$sql .= " LEFT OUTER JOIN (
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
			WHERE ccasemain.deleted = 'N'
        ) maininjury
        ON inj.injury_uuid = maininjury.injury_uuid
		WHERE 1
			AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
			AND ccase.deleted = 'N'
			AND inj.deleted = 'N'
			ORDER BY main_case_id, inj.injury_number ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($case_id>0) {
			$stmt->bindParam("case_id", $case_id);
		}
		$stmt->execute();		
		$injuries = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die($injury);   
		return $injuries; 
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getInjuryInfo($injury_id) {
	if ($injury_id=="" || $injury_id < 0) {
		return false;
		$error = array("error"=> array("text"=>"no valid id", "injury_id"=>$injury_id));
        echo json_encode($error);
		die();
	}
	$db_name = "";
	if (isset($_SESSION["return"])) {
		$db_name = "`" . $_SESSION["return"]. "`.";
	}
	session_write_close();
	//return a single record
	$blnSpecific = false;
	/*
    $sql = "SELECT inj.*, inj.injury_id id, inj.injury_uuid uuid
			FROM `cse_injury` inj ";
	$sql .= " WHERE 1 AND inj.customer_id = " . $_SESSION['user_customer_id'];
	if ($injury_id>0) {
		$blnSpecific = true;
		$sql .= " AND inj.injury_id = :injury_id";
	}
	*/
	$sql = " SELECT inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id,
			IFNULL(occ.occupation_id, -1) occupation_id,
			IFNULL(main_case_id, ccase.case_id) `main_case_id`, IFNULL(main_case_uuid, ccase.case_uuid) `main_case_uuid`
			FROM " . $db_name . "`cse_injury` inj 
			LEFT OUTER JOIN " . $db_name . "`cse_injury_occupation` iocc
			ON inj.injury_uuid = iocc.injury_uuid
			LEFT OUTER JOIN `ikase`.`cse_occupation` occ
			ON iocc.occupation_uuid = occ.occupation_uuid
			INNER JOIN " . $db_name . "cse_case_injury ccinj
			ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.deleted = 'N'
			INNER JOIN " . $db_name . "cse_case ccase
			ON ccinj.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN (
				SELECT ccinj.attribute, ccasemain.case_uuid main_case_uuid, ccasemain.case_id main_case_id, 
				ccinj.case_uuid related_case_uuid, ccase.case_id related_case_id, inj.*   
				FROM " . $db_name . "`cse_injury` inj 
				
				INNER JOIN " . $db_name . "cse_case_injury ccinj
				ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.`deleted` = 'N' AND ccinj.attribute = 'related'
				INNER JOIN " . $db_name . "cse_case ccase
				ON ccinj.case_uuid = ccase.case_uuid
				
				INNER JOIN " . $db_name . "cse_case_injury ccmain
				ON inj.injury_uuid = ccmain.injury_uuid AND ccmain.`deleted` = 'N' AND ccmain.attribute = 'main'
				INNER JOIN " . $db_name . "cse_case ccasemain
				ON ccmain.case_uuid = ccasemain.case_uuid
				WHERE ccasemain.deleted = 'N'
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
		//if(!$stmt->execute()) echo $stmt->error;
		$injury = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		return $injury;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteInjury() {
	$id = passed_var("injury_id", "post");
	if ($id=="") {
		$id = passed_var("id", "post");
	}
	$customer_id = $_SESSION["user_customer_id"];
	//get the uuid
	$injury = getInjuryInfo($id);
	
	//see how many injuries we have with this case
	$sql = "SELECT COUNT(injury.injury_id) injury_count
	FROM cse_case_injury case_inj
	INNER JOIN cse_injury injury
	ON case_inj.injury_uuid = injury.injury_uuid
	INNER JOIN (
		SELECT cinj.case_uuid
		FROM (
		SELECT ci.injury_uuid
		FROM `cse_injury` ci
		WHERE ci.`injury_id` = :id
		AND ci.deleted = 'N' ) inj
	
		INNER JOIN (
			SELECT cci.injury_uuid, cci.case_uuid 
			FROM cse_case_injury cci
			WHERE cci.deleted = 'N' AND cci.`attribute` = 'main'
		) cinj
	
		ON inj.injury_uuid = cinj.injury_uuid
	) injury_case
	ON case_inj.case_uuid = injury_case.case_uuid
	AND injury.deleted = 'N'
	AND injury.customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$counter = $stmt->fetchObject();
		
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
		die();
	}	
	
	//die($sql);
	if ($counter->injury_count == 1) {
		//get rid of the case too
		$kase = getKaseInfoByInjury($id);
		$bulk_ids = array($kase->id);
		
		//if this is the _only_ injury, then we just clear it to "delete" it
		//the information will be part of the tracking
		$sql = "UPDATE `cse_injury`
		SET
		`deleted` = 'Y',
		`adj_number` = '',
		`type` = '',
		`injury_status` = '',
		`occupation` = '',
		`start_date` = '0000-00-00',
		`end_date` = '0000-00-00',
		`ct_dates_note` = '',
		`body_parts` = '',
		`statute_limitation` = '0000-00-00',
		`explanation` = '',
		`deu` = 'N',
		`full_address` = '',
		`street` = '',
		`city` = '',
		`state` = '',
		`zip` = '',
		`suite` = ''
		WHERE `injury_id` = :id";
		
		$where = "WHERE injury_uuid = '" . $injury->uuid . "'
		AND customer_id = '" . $_SESSION["user_customer_id"] . "';";
		
		$arrTables = array("cse_injury_accident", "cse_injury_bodyparts", "cse_injury_corporation", "cse_injury_event", "cse_injury_injury_number", "cse_injury_lien", "cse_injury_notes", "cse_injury_occupation", "cse_injury_other_dates", "cse_injury_settlement");
		$arrSQL = array();
		foreach($arrTables as $table) {
			$arrSQL[] = "UPDATE `" . $table . "`
			SET deleted = 'Y' " . $where;
		}
		
		$sql2 = implode("", $arrSQL);
		
		$db = getConnection();
		$stmt = $db->prepare($sql2);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		deleteKase($bulk_ids);
	} else {
		$sql = "UPDATE cse_injury inj
				SET inj.`deleted` = 'Y'
				WHERE `injury_id`=:id";
		$sql2 = "";
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		if ($sql2!=="") {
			$db = getConnection();
			$stmt = $db->prepare($sql2);
			$stmt->execute();
			$stmt = null; $db = null;
		}
		trackInjury("delete", $id);
		
		echo json_encode(array("success"=>"injury marked as deleted", "sql"=>$sql));
		
		//if this is the _only_ injury, then we just clear it and undelete it
		//the information will be part of the tracking
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addInjury() {
	//$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$case_id = "";
	$statute_limitation = "";
	$venue = "";
	$last_updated_date = date("Y-m-d H:i:s");
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="venue") {
			$venue = $value;
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown_in") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown_bp") {
			continue;
		}
		if ($fieldname=="case_uuid" || $fieldname=="table_id" || $fieldname=="table_uuid" || $fieldname=="injury_uuid" || $fieldname=="checkCT" || $fieldname=="id" || $fieldname=="statute_interval") {
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			$kase = getKaseInfo($case_id);
			continue;
		}
		if ($fieldname=="start_date") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else  {
				$value = "0000-00-00";
			}
		}
		if ($fieldname=="end_date") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else  {
				$value = "0000-00-00";
			}
		}
		if ($fieldname=="statute_limitation") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else  {
				$value = "0000-00-00";
			}
			$statute_limitation = $value;
		}
		if ($fieldname=="occupation") {
			//is it a lookup
			if (is_numeric($value)) {
				//get the actual occupation
				$occupation = getOccupation($value);
				$value = $occupation->title;
			}
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	$arrFields[] = "`customer_id`";
	$arrSet[] = $_SESSION['user_customer_id'];
	
	if (!in_array("`explanation`", $arrFields)) {
		$arrFields[] = "`explanation`";
		$arrSet[] = "''";
	}
	$table_uuid = uniqid("KS", false);

	try { 
		$db = getConnection();
		//maximum injury number
		$injury_number = 1;
		$sql = "SELECT MAX(injury_number) injury_number
		FROM cse_injury ci
		INNER JOIN cse_case_injury cci
		ON ci.injury_uuid = cci.injury_uuid
		WHERE cci.case_uuid = '" . $kase->uuid . "'
		AND ci.deleted = 'N'";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$injury = $stmt->fetchObject();
		if (count($injury) > 0) {
			$injury_number = $injury->injury_number + 1;
		}
		
		//insert injury
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `injury_number`, " . implode(",", $arrFields) . ")
		VALUES('" . $table_uuid . "', " . $injury_number . ", " . implode(",", $arrSet) . ")";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		$case_table_uuid = uniqid("KA", false);
		
		$attribute_1 = "main";
		//now we have to attach the injury to the case 
		$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $kase->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  	
		$stmt->execute();
		$stmt = null; $db = null;
		//track now
		trackInjury("insert", $new_id);
		
		if ($statute_limitation!="") {
			//create an event
			$event_uuid = uniqid("KS", false);	
			$sql = "INSERT INTO `cse_event` (`event_uuid`, `event_dateandtime`, `event_date`, `event_hour`, `event_title`, `full_address`, `event_type`, `color`, `event_description`, `customer_id`)
				VALUES('" . $event_uuid . "', '" . $statute_limitation . " 08:00:00', '" . $statute_limitation . "' , '08:00:00', 'Statute of Limitation for Case' , '' , 'statute_limitation', 'purple', 'Statute of Limitation for Case', " . $_SESSION['user_customer_id'] . ")";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();	
			$event_id = $db->lastInsertId();
			
			$stmt = null; $db = null;
			
			trackEvent("insert", $event_id);
			
			//attach the event to the injury
			$injury_table_uuid = uniqid("KA", false);
			$attribute_1 = "statute_limitation";
			$sql = "INSERT INTO cse_injury_event (`injury_event_uuid`, `injury_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $injury_table_uuid  ."', '" . $table_uuid . "', '" . $event_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();	
			$stmt = null; $db = null;	
			
			//reminders				
			$reminder_uuid = uniqid("RM", false);
			$reminder_type = "interoffice";
			$reminder_interval = 20;
			$reminder_span = "days";
			$reminder_number = 1;
			$reminder_datetime = date("Y-m-d H:i:s", strtotime($statute_limitation . " 08:00:00" . " - " . $reminder_interval . " " . $reminder_span));
			$values = "'" . $reminder_uuid . "', '" . $reminder_number . "', '" . $reminder_type . "', '" . $reminder_interval . "', '" . $reminder_span . "', '"  . $reminder_datetime . "', '" . $_SESSION['user_customer_id'] . "'"; 
			
			//insert the reminder
			$sql = "INSERT cse_reminder (`reminder_uuid`, `reminder_number`, `reminder_type`, `reminder_interval`, `reminder_span`,`reminder_datetime`, `customer_id`) 
			VALUES(" . $values . ")";
			//echo $sql . "\r\n";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();	
			$stmt = null; $db = null;	
			
			$event_reminder_uuid = uniqid("ER", false);
			//attach each one to the event
			$sql = "INSERT INTO cse_event_reminder (`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $event_reminder_uuid  ."', '" . $event_uuid . "', '" . $reminder_uuid . "', '" . $reminder_number . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			//echo $sql . "\r\n";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();	
			$stmt = null; $db = null;
		}
		
		if ($venue!="") {
			$injury_venue_uuid = uniqid("KS", false);
			$last_updated_date = date("Y-m-d H:i:s");
			
			$sql = "INSERT INTO cse_injury_venue (`injury_venue_uuid`, `injury_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $injury_venue_uuid  . "', '" . $table_uuid . "', '" . $venue . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
					
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function getOccupation($occupation_id) {
	if (!is_numeric($occupation_id)) {
		echo '{"error":{"text":"invalid occupation id"}}'; 
		die();
	}
	$sql = "SELECT `occupation_id` `id`, `title`, `description`
	FROM `ikase`.`cse_occupation`
	WHERE `occupation_id` = :occupation_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("occupation_id", $occupation_id);
		$stmt->execute(); 
		
		$occupation = $stmt->fetchObject();
						
		$stmt->closeCursor(); $stmt = null; $db = null;  
		return $occupation; 
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updateInjury() {
	$request = Slim::getInstance()->request();
	
	//first check on the id, if negative, this is an insert
	if (isset($_POST["id"])) {
		if ($_POST["id"] < 0) {
			return;
		}
	}
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$statute_limitation = "0000-00-00";
	$last_updated_date = date("Y-m-d H:i:s");
	$venue = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="venue") {
			$venue = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown_in") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown_bp") {
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="case_uuid" || $fieldname=="checkCT" || $fieldname=="injury_uuid") {
			continue;
		}
		if (count($_POST)!=4) {
			if ($fieldname=="statute_interval") {
				continue;
			}			
		}
		if ($fieldname=="occupation") {
			//is it a lookup
			if (is_numeric($value)) {
				//get the actual occupation
				$occupation = getOccupation($value);
				$value = $occupation->title;
			}
		}
		if ($fieldname=="start_date" || $fieldname=="end_date" || $fieldname=="statute_limitation") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
			if ($fieldname=="statute_limitation") {
				$statute_limitation = $value;
			}
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			//get the uuid
			$injury = getInjuryInfo($table_id);
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];
	//echo $sql . "\r\n";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		$arrOutput = array("id"=>$table_id); 
		
		if ($statute_limitation!="0000-00-00") {
			//if we have a statute_limitation, we need matching event
			$sql = "SELECT ev.event_id, ev.event_uuid 
			FROM cse_injury_event cie
			INNER JOIN cse_event ev
			ON cie.event_uuid = ev.event_uuid AND ev.deleted = 'N'
			WHERE cie.injury_uuid = '" . $injury->uuid . "'
			AND cie.`attribute` = 'statute_limitation'
			AND cie.`deleted` = 'N'
			AND cie.customer_id = " . $_SESSION['user_customer_id'];
			
			$arrOutput["inj_event"] = $sql;
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute(); 
			$inj_event = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			if (!is_object($inj_event)) {
				//create an event
				$event_uuid = uniqid("KS", false);	
				$sql = "INSERT INTO `cse_event` (`event_uuid`, `event_dateandtime`, `event_date`, `event_hour`, `event_title`, `full_address`, `event_type`, `color`, `event_description`, `customer_id`)
					VALUES('" . $event_uuid . "', '" . $statute_limitation . " 08:00:00', '" . $statute_limitation . "' , '08:00:00', 'Statute of Limitation for Case' , '' , 'statute_limitation', 'purple', 'Statute of Limitation for Case', " . $_SESSION['user_customer_id'] . ")";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();	
				$event_id = $db->lastInsertId();
				
				$stmt = null; $db = null;
				
				trackEvent("insert", $event_id);
				
				//attach the event to the injury
				$injury_table_uuid = uniqid("KA", false);
				$attribute_1 = "statute_limitation";
				$sql = "INSERT INTO cse_injury_event (`injury_event_uuid`, `injury_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $injury->uuid . "', '" . $event_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();	
				$stmt = null; $db = null;	
				
				//attach the event to the CASE
				$injury_table_uuid = uniqid("KS", false);
				$attribute_1 = "statute_limitation";
				$sql = "INSERT INTO cse_case_event (`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $injury->main_case_uuid . "', '" . $event_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();	
				$stmt = null; $db = null;	
				
				//reminders				
				$reminder_uuid = uniqid("RM", false);
				$reminder_type = "interoffice";
				$reminder_interval = 20;
				$reminder_span = "days";
				$reminder_number = 1;
				$reminder_datetime = date("Y-m-d H:i:s", strtotime($statute_limitation . " 08:00:00" . " - " . $reminder_interval . " " . $reminder_span));
				$values = "'" . $reminder_uuid . "', '" . $reminder_number . "', '" . $reminder_type . "', '" . $reminder_interval . "', '" . $reminder_span . "', '"  . $reminder_datetime . "', '" . $_SESSION['user_customer_id'] . "'"; 
				
				//insert the reminder
				$sql = "INSERT cse_reminder (`reminder_uuid`, `reminder_number`, `reminder_type`, `reminder_interval`, `reminder_span`,`reminder_datetime`, `customer_id`) 
				VALUES(" . $values . ")";
				//echo $sql . "\r\n";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();	
				$stmt = null; $db = null;	
				
				$event_reminder_uuid = uniqid("ER", false);
				//attach each one to the event
				$sql = "INSERT INTO cse_event_reminder (`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $event_reminder_uuid  ."', '" . $event_uuid . "', '" . $reminder_uuid . "', '" . $reminder_number . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				//echo $sql . "\r\n";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();	
				$stmt = null; $db = null;
			} else {
				//change the date on the event itself
				//$injury was obtained _before_ the update, so it holds old info
				if ($injury->statute_limitation != $statute_limitation) {
					$sql = "UPDATE cse_event
					SET event_dateandtime = '" . $statute_limitation . " 08:00:00'
					WHERE event_uuid = '" . $inj_event->event_uuid . "'
					AND customer_id = " . $_SESSION['user_customer_id'];
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->execute(); 
					$stmt = null; $db = null;
					
					$arrOutput["update"] = $sql;
					
					//and of course update the reminder itself
					$sql = "SELECT reminder_uuid 
					FROM cse_event_reminder
					where `event_uuid` = '" . $inj_event->event_uuid . "'
					AND `deleted` = 'N'
					AND `customer_id` = " . $_SESSION['user_customer_id'];
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->execute(); 
					$event_reminder = $stmt->fetchObject();
					$stmt->closeCursor(); $stmt = null; $db = null;
					
					$reminder_interval = 20;
					$reminder_span = "days";
					$reminder_number = 1;
					$reminder_datetime = date("Y-m-d H:i:s", strtotime($statute_limitation . " 08:00:00" . " - " . $reminder_interval . " " . $reminder_span));
					
					$sql = "UPDATE cse_reminder
					SET reminder_datetime = '" . $reminder_datetime . "',
					buffered = 'N'
					WHERE reminder_uuid = '" . $event_reminder->reminder_uuid . "'
					AND customer_id = " . $_SESSION['user_customer_id'];
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->execute(); 
					$stmt = null; $db = null;
					
					trackEvent("update", $inj_event->event_id);
				}
			}
		}
		
		
		if ($venue!="") {
			$sql = "UPDATE cse_injury_venue
			SET deleted = 'Y'
			WHERE injury_uuid = '" . $injury->uuid . "'
			AND venue_uuid != '" . $venue . "'
			AND customer_id = " . $_SESSION['user_customer_id'];
			//die($sql);
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute(); 
			$stmt = null; $db = null;
			
			$injury_venue_uuid = uniqid("KS", false);
			$last_updated_date = date("Y-m-d H:i:s");
			
			$sql = "INSERT INTO cse_injury_venue (`injury_venue_uuid`, `injury_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			SELECT'" . $injury_venue_uuid  . "', '" . $injury->uuid . "', '" . $venue . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "'
			FROM dual
						WHERE NOT EXISTS (
							SELECT injury_venue_id
							FROM cse_injury_venue
							WHERE `injury_uuid` = '" . $injury->uuid . "'
							AND `venue_uuid` = '" . $venue . "'
						)";
					
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		/*
		//now we have to attach the event to the injury 
		$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		*/
		
		trackInjury("update", $table_id);
		
		echo json_encode($arrOutput);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function updateInjuryField() {
	session_write_close();
	$id = passed_var("id", "post");
	$fieldname = passed_var("fieldname", "post");
	$value = passed_var("value", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	if (strpos($fieldname, "date")!==false) {
		if ($value!="") {
			$value = date("Y-m-d", strtotime($value));
		} else {
			$value = "0000-00-00 00:00:00";
		}
	}
	$sql = "UPDATE cse_injury
	SET `" . $fieldname . "` = :value
	WHERE injury_id = :id
	AND customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("value",  $value);
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>$id)); 
		
		trackInjury("update", $id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function searchOccupation() {
	$search_term = passed_var("q", "get");
	
	$search_term = clean_html($search_term);
	//search in ikase, as this is a straight lookup
	$sql = "SELECT `occupation_id` `id`, `title`
	FROM `ikase`.`cse_occupation`
	WHERE `title` LIKE '%" . $search_term . "%'
	ORDER BY `title`";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute(); 
		
		$occupations = $stmt->fetchAll(PDO::FETCH_OBJ);
		echo json_encode($occupations);     
						
		$stmt->closeCursor(); $stmt = null; $db = null;  
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function trackInjury($operation, $injury_id) {
	$sql = "INSERT INTO cse_injury_track (`user_uuid`, `user_logon`, `operation`, `injury_id`, `injury_uuid`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `explanation`, `full_address`, `suite`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `injury_id`, `injury_uuid`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `explanation`, `full_address`, `suite`, `customer_id`, `deleted`
	FROM cse_injury
	WHERE 1
	AND injury_id = " . $injury_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		//new the case_uuid
		$kase = getKaseInfoByInjury($injury_id);
		$activity_category = "Injury";
		switch($operation){
			case "insert":
				$operation .= "ed";
				break;
			case "update":
				$operation .= "d";
				break;
			case "delete":
				$operation .= "d";
				break;
			case "eams_adj_update":
				$operation = "updated via EAMS Jetfile [ADJ: " . $kase->adj_number . "]";
				break;
		}
		$doi = "";
		if ($kase->start_date != "0000-00-00") {
			$doi = date("m/d/Y", strtotime($kase->start_date));
		}
		if ($kase->end_date != "0000-00-00") {
			$doi .= " - " . date("m/d/Y", strtotime($kase->end_date)) . " CT";
		}
		$doi = $kase->adj_number . " - " . $doi;
		$activity_uuid = uniqid("KS", false);
		$activity = "Injury Information for [" . $doi . "] was " . $operation . "  by " . $_SESSION['user_name'];
		
		$billing_time = 0;
		if (isset($_POST["billing_time"])) {
			$billing_time = passed_var("billing_time", "post");
		}
		recordActivity($operation, $activity, $kase->uuid, $new_id, $activity_category, $billing_time);
	
	} catch(PDOException $e) {
		echo '{"error":{"text":'. 'track:' . $e->getMessage() .'}}'; 
	}
}
?>