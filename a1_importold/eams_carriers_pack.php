<?php
$app->get('/eams_carrier', authorize('user'),	'getEamsCarriers');
$app->get('/eams_carrier/:id', authorize('user'),	'getEamsCarrier');
$app->get('/eams_token', authorize('user'), 'getTokenEams');
$app->get('/examiners/tokeninput/:id', authorize('user'), 'getTokenEamsEmployees');

function getTokenEams() {
	session_write_close();
	$search_term = passed_var("q", "get");
	/*
	$sql = "SELECT `carrier_id` `id`, `eams_ref_number`, `firm_name` `name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, 
	*/
	//, `firm_name` `name`
	$sql = "SELECT `carrier_id` `id`, `eams_ref_number`, 
	phone,
	CONCAT(`firm_name`, ' ', `street_1`, ', ', `city`) `company_name`, 
	
	CONCAT(
	`firm_name`, '<br>', `street_1`, ', ', `city`, 
	IF(`phone`!='', CONCAT('<br>', `phone`), '')) `name`,
	
	`street_1`, `street_2`, `city`, `state`, `zip_code`, `carrier_id` `id`, `carrier_uuid` `uuid`
	FROM `ikase`.`cse_eams_carriers` 
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
	ORDER by firm_name";
	if ($_SERVER['REMOTE_ADDR'] == "98.112.195.202") {
		//die($sql);
	}
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("search_term", $search_term);
		$stmt->execute();
		$eams_carriers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		//die(print_r($eams_carriers));
        // Include support for JSONP requests
        echo json_encode($eams_carriers);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEamsCarriers() {
	session_write_close();
	
	$sql = "SELECT `carrier_id`, `eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`, `carrier_id` `id`, `carrier_uuid` `uuid`
	FROM `ikase`.`cse_eams_carriers` 
	WHERE 1  AND active = 'Y'";
	$sql .= " ORDER by firm_name";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("search_term", $search_term);
		$stmt->execute();
		$eams_carriers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		//die(print_r($eams_carriers));
        // Include support for JSONP requests
        echo json_encode($eams_carriers);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getEamsCarrier($id) {
	session_write_close();
	//return a row if id is valid
	$sql = "SELECT `carrier_id`, `eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`, `carrier_id` `id`, `carrier_uuid` `uuid`
		FROM `ikase`.`cse_eams_carriers`
		WHERE carrier_id=:id
		";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$eams_carrier = $stmt->fetchObject();
		$db = null;
	
        echo json_encode($eams_carrier);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEamsCarrierInfo($id) {
	session_write_close();
	
	//return a row if id is valid
	$sql = "SELECT `carrier_id`, `eams_ref_number`, `firm_name`, 
	`street_1`, `street_2`, `city`, `state`, `zip_code`, 
	`phone`, `service_method`, `last_update`, `last_import_date`, 
	`carrier_id` `id`, `carrier_uuid` `corporation_uuid`, `firm_name` `company_name`
	FROM `ikase`.`cse_eams_carriers`
	WHERE carrier_id=:id ";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$eams_carrier = $stmt->fetchObject();
		$db = null;
	
        return $eams_carrier;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTokenEamsEmployees($id) {
	if (!isset($_GET["q"])) {
		return false;
	}
	$search_term = passed_var("q", "get");
	
	//
	/*
    $sql = "SELECT @curRow := @curRow + 1 AS `id`, 
	corp.salutation, corp.phone, corp.fax, corp.email,
	CONCAT( corp.`full_name` ,  ' (', parent.firm_name, ')' )  `name` 
	FROM  `cse_corporation` corp
	INNER JOIN  `ikase`.`cse_eams_carriers` parent ON corp.parent_corporation_uuid = parent.carrier_uuid
	JOIN (SELECT @curRow := 0) r";
	$sql .= " 
	WHERE corp.deleted = 'N'";
	$sql .= " 
	AND parent.carrier_id = :id";
	$sql .= " 
	AND corp.full_name != ''";	
	$sql .= " 
	AND corp.customer_id = " . $_SESSION['user_customer_id'];
	if ($search_term != "") {	
		$sql .= " 
		AND (";
		$arrSearch[] = " corp.`full_name` LIKE '%" . $search_term . "%' ";
		
		$sql .= implode(" OR ", $arrSearch);
		$sql .= ")";
	}
	$sql .= " 
	GROUP BY corp.salutation, corp.phone, corp.fax, corp.email,
	corp.`full_name`, parent.firm_name
	ORDER by `corp`.`full_name`";
	//the order by is based on the relationship id so that the first of each kind will easily selected
	//die($sql);
	*/
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "
	SELECT @curRow := @curRow + 1 AS `id`, 
	carriers.salutation, carriers.phone, carriers.fax, carriers.email,
	CONCAT( carriers.`full_name` ,  ' (', cec.firm_name, ')' )  `name` 
    
	FROM ikase.cse_eams_carriers cec
	
	INNER JOIN cse_corporation corp
	ON cec.carrier_uuid = corp.parent_corporation_uuid AND corp.corporation_id = :id
	
	INNER JOIN cse_corporation carriers
	ON cec.carrier_uuid = carriers.parent_corporation_uuid
	
	WHERE 1
	AND carriers.deleted = 'N' 
	AND carriers.full_name != '' 
	AND carriers.customer_id = :customer_id 
	AND INSTR( carriers.`full_name`, :search_term) > 0
	GROUP BY carriers.salutation, carriers.phone, carriers.fax, carriers.email,
	carriers.`full_name`, cec.firm_name
	ORDER by `carriers`.`full_name`";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("search_term", $search_term);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$token_employees = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

        echo json_encode($token_employees);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>