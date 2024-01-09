<?php
$app->group('', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->get('/eams_rep', 'getEamsReps');
	$app->get('/eams_rep/{id}', 'getEamsRep');
	$app->get('/eams_claimant/{id}', 'getEamsClaimant');
	$app->get('/eams_repnumber/{eams_number}', 'getEamsRepByNumber');

	$app->get('/eams/search/{search_term}', 'searchEAMSCompanies');

	$app->get('/eams_reptoken', 'getTokenRepEams');
	$app->get('/eams_defense_token', 'getTokenRepDefenseEams');

	$app->get('/eams_claimanttoken', 'getTokenClaimantEams');
	$app->get('/eams_claimant_rep_token', 'getTokenClaimantRepEams');
	$app->get('/attorneys/tokeninput/{id}', 'getTokenEamsAttorney');

	//OBSOLETE
	$app->get('/defense/tokeninput/{id}', 'getTokenEamsAttorney');
})->add(Api\Middleware\Authorize::class);

//$app->post('/eams_rep_fetch', 'getEAMSRepRemote');

function searchEAMSCompanies($search_term) {
	session_write_close();
	
	if (strlen($search_term) < 3) {
		die(json_encode(array("success"=>false, "error"=>"too short")));
	}

	$sql = "SELECT 'attorney' `firm_type`, eams_ref_number, firm_name, CONCAT(street_1, IF(street_2='', '', CONCAT(', ', street_2)), ', ', city, ', ', state, ' ', zip_code) full_address, phone
	FROM ikase.cse_eams_reps
	WHERE INSTR(firm_name,:search_term) > 0
	
	UNION
	
	SELECT 'claimant' `firm_type`, eams_ref_number, firm_name, CONCAT(street_1, IF(street_2='', '', CONCAT(', ', street_2)), ', ', city, ', ', state, ' ', zip_code) full_address, phone
	FROM ikase.cse_eams_claimants
	WHERE INSTR(firm_name,:search_term) > 0
	
	UNION
	
	SELECT 'carrier' `firm_type`, eams_ref_number, firm_name, CONCAT(street_1, IF(street_2='', '', CONCAT(', ', street_2)), ', ', city, ', ', state, ' ', zip_code) full_address, phone
	FROM ikase.cse_eams_carriers
	WHERE INSTR(firm_name,:search_term) > 0
	
	ORDER BY `firm_type`, `firm_name`";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("search_term", $search_term);
		$stmt->execute();
		$eams_companies = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($eams_carriers));
        // Include support for JSONP requests
        echo json_encode($eams_companies);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTokenClaimantRepEams() {
	session_write_close();
	
	$search_term = passed_var("q", "get");
	$sql = "SELECT `claimant_id` `id`, 'claimant' `eams_type`, `eams_ref_number`, `firm_name`,
	CONCAT(`firm_name`, ' ', `street_1`, ', ', `city`) `company_name`, 
	
	CONCAT(
	`firm_name`, '<br>', `street_1`, ', ', `city`, 
	IF(`phone`!='', CONCAT('<br>', `phone`), '')) `name`,
	
	`street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`
	FROM `ikase`.`cse_eams_claimants` 
	WHERE 1 AND active = 'Y' ";
	if ($search_term != "") {	
		$sql .= " AND (";
		$arrSearch[] = " `firm_name` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_1` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_2` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `city` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_1` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `zip_code` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `phone` LIKE '%" . $search_term . "%' ";
		
		$sql .= implode(" OR ", $arrSearch);
		$sql .= ")";
	}
	
	$sql .= "
	UNION
	
	SELECT `rep_id` `id`, 'rep' `eams_type`, `eams_ref_number`, `firm_name`,
	CONCAT(`firm_name`, ' ', `street_1`, ', ', `city`) `company_name`, 
	
	CONCAT(
	`firm_name`, '<br>', `street_1`, ', ', `city`, 
	IF(`phone`!='', CONCAT('<br>', `phone`), '')) `name`,
	
	`street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`
	FROM `ikase`.`cse_eams_reps` 
	WHERE 1 AND active = 'Y' ";
	if ($search_term != "") {	
		$sql .= " AND (";
		$arrSearch[] = " `firm_name` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_1` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_2` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `city` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_1` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `zip_code` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `phone` LIKE '%" . $search_term . "%' ";
		
		$sql .= implode(" OR ", $arrSearch);
		$sql .= ")";
	}
	
	$sql .= " ORDER by firm_name";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("search_term", $search_term);
		$stmt->execute();
		$eams_claimants = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($eams_carriers));
        // Include support for JSONP requests
        echo json_encode($eams_claimants);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTokenClaimantEams() {
	session_write_close();
	
	$search_term = passed_var("q", "get");
	$sql = "SELECT `claimant_id` `id`, `eams_ref_number`, `firm_name`,
	CONCAT(`firm_name`, ' ', `street_1`, ', ', `city`) `company_name`, 
	
	CONCAT(
	`firm_name`, '<br>', `street_1`, ', ', `city`, 
	IF(`phone`!='', CONCAT('<br>', `phone`), '')) `name`,
	
	`street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `last_update`, `last_import_date`, `claimant_id` `id`, `claimant_uuid` `uuid`
	FROM `ikase`.`cse_eams_claimants` 
	WHERE 1 AND active = 'Y' ";
	if ($search_term != "") {	
		$sql .= " AND (";
		$arrSearch[] = " `firm_name` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_1` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_2` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `city` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_1` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `zip_code` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `phone` LIKE '%" . $search_term . "%' ";
		
		$sql .= implode(" OR ", $arrSearch);
		$sql .= ")";
	}
	$sql .= " ORDER by firm_name";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("search_term", $search_term);
		$stmt->execute();
		$eams_claimants = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($eams_carriers));
        // Include support for JSONP requests
        echo json_encode($eams_claimants);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTokenRepDefenseEams() {
	$_SESSION["rep_type"] = "defense";
	getTokenRepEams();
}
function getTokenRepEams() {
	$rep_type = "";
	if (isset($_SESSION["rep_type"])) {
		if ($_SESSION["rep_type"] != "") {
			$rep_type = $_SESSION["rep_type"];
		}
		$_SESSION["rep_type"] = "";
		unset($_SESSION["rep_type"]);
	}
	session_write_close();
	
	$search_term = passed_var("q", "get");
	$sql = "SELECT `rep_id` `id`, `eams_ref_number`, `firm_name`,
	CONCAT(`firm_name`, ' ', `street_1`, ', ', `city`) `company_name`, 
	
	CONCAT(
	`firm_name`, '<br>', `street_1`, ', ', `city`, 
	IF(`phone`!='', CONCAT('<br>', `phone`), '')) `name`,
	
	`street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`, `rep_uuid` `uuid`
	FROM `ikase`.`cse_eams_reps` 
	WHERE 1 AND active = 'Y' ";
	if ($search_term != "") {	
		$sql .= " AND (";
		$arrSearch[] = " `firm_name` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_1` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_2` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `city` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `street_1` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `zip_code` LIKE '%" . $search_term . "%' ";
		$arrSearch[] = " `phone` LIKE '%" . $search_term . "%' ";
		
		$sql .= implode(" OR ", $arrSearch);
		$sql .= ")";
	}
	
	if ($rep_type=="defense") {
		$sql .= "		
			UNION
	
			SELECT corporation_id id, '-1', company_name firm_name, company_name, 
			CONCAT(company_name, '<br>', `street`, ',', `city`, 
				IF(`phone`!='', CONCAT('<br>', `phone`), '')) `name`, `street`, `suite` `street1`, `city`, `state`, `zip`, `phone`, '' `service_method`,
				'' `last_update`, '' `last_import_date`, `corporation_uuid` `uuid`
			FROM cse_corporation
			WHERE company_name LIKE '%" . $search_term . "%'
			AND `type` = 'defense'
			AND deleted = 'N'
			AND customer_id = '" . $_SESSION["user_customer_id"] . "'
			AND corporation_uuid = parent_corporation_uuid";
	}
	
	$sql .= " ORDER by firm_name";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("search_term", $search_term);
		$stmt->execute();
		$eams_reps = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($eams_carriers));
        // Include support for JSONP requests
        echo json_encode($eams_reps);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEamsClaimant($id) {
	session_write_close();
	//return a row if id is valid
	$sql = "SELECT `claimant_id`, `eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`, `claimant_id` `id`, `claimant_uuid` `uuid`
		FROM `ikase`.`cse_eams_claimants`
		WHERE claimant_id=:id
		";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$eams_claimant = $stmt->fetchObject();
	
        echo json_encode($eams_claimant);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEamsReps() {
	session_write_close();
	
	$sql = "SELECT `rep_id`, `eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`, `rep_id` `id`, `rep_uuid` `uuid`
	FROM `ikase`.`cse_eams_reps` 
	WHERE 1 AND active = 'Y' ";
	$sql .= " ORDER by firm_name";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("search_term", $search_term);
		$stmt->execute();
		$eams_reps = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($eams_reps));
        // Include support for JSONP requests
        echo json_encode($eams_reps);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getEamsRep($id) {
	session_write_close();
	//return a row if id is valid
	$sql = "SELECT `rep_id`, `eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`, `rep_id` `id`, `rep_uuid` `uuid`
		FROM `ikase`.`cse_eams_rep`
		WHERE rep_id=:id
		";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$eams_rep = $stmt->fetchObject();
	
        echo json_encode($eams_rep);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEamsRepInfo($id) {
	session_write_close();
	//return a row if id is valid
	$sql = "SELECT `rep_id`, `eams_ref_number`, `firm_name`, 
	`street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`, 
	`rep_id` `id`, `rep_uuid` `corporation_uuid`, `firm_name` `company_name`
	FROM `ikase`.`cse_eams_reps`
	WHERE rep_id=:id ";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$eams_rep = $stmt->fetchObject();
	
        return $eams_rep;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEamsRepByNumber($eams_number) {
	session_write_close();
	//return a row if id is valid
	$sql = "SELECT `rep_id`, `eams_ref_number`, `firm_name`, 
	`street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`, 
	`rep_id` `id`, `rep_uuid` `corporation_uuid`, `firm_name` `company_name`
	FROM `ikase`.`cse_eams_reps`
	WHERE eams_ref_number = '" . trim($eams_number) . "'";
	//
	if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("eams_number", $eams_number);
		$stmt->execute();
		$eams_rep = $stmt->fetchObject();
	
        return $eams_rep;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTokenEamsAttorney($id) {
	session_write_close();
	
	if (!isset($_GET["q"])) {
		return false;
	}
	$search_term = passed_var("q", "get");
    $sql = "SELECT @curRow := @curRow + 1 AS `id`, 
	corp.salutation, corp.phone, corp.fax, corp.email,
	CONCAT( corp.`full_name` ,  ' (', parent.firm_name, ')' )  `name` 
	FROM  `cse_corporation` corp
	INNER JOIN  `ikase`.`cse_eams_reps` parent ON corp.parent_corporation_uuid = parent.rep_uuid
	JOIN (SELECT @curRow := 0) r";
	$sql .= " WHERE corp.deleted = 'N'";
	$sql .= " AND parent.rep_id = :id";
	$sql .= " AND corp.full_name != ''";	
	$sql .= " AND corp.customer_id = " . $_SESSION['user_customer_id'];
	if ($search_term != "") {	
		$sql .= " AND (";
		$arrSearch[] = " corp.`full_name` LIKE '%" . $search_term . "%' ";
		
		$sql .= implode(" OR ", $arrSearch);
		$sql .= ")";
	}
	$sql .= " GROUP BY corp.salutation, corp.phone, corp.fax, corp.email,
	corp.`full_name`, parent.firm_name
	ORDER by `corp`.`full_name`";
	//the order by is based on the relationship id so that the first of each kind will easily selected
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$token_attorneys = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($token_attorneys);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEAMSRepRemote() {
	session_write_close();
	
	if (!isset($_POST["q"])) {
		return false;
	}
	$search_term = passed_var("q", "post");
	
    $sql = "SELECT eams_ref_number, firm_name, street_1
	FROM  `ikase`.`cse_eams_reps`";
	$sql .= " 
	WHERE INSTR(firm_name, '" . addslashes($search_term) . "') > 0
	ORDER by `firm_name`";
	//the order by is based on the relationship id so that the first of each kind will easily selected
	//die($sql);
	try {
		$token_attorneys = DB::select($sql);

        echo json_encode($token_attorneys);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
