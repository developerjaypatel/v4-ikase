<?php
$app->get('/adhocs/:case_id/:corporation_id', authorize('user'), 'getAdhocs');

function getAdhocs($case_id, $corporation_id) {
	session_write_close();
	
    $sql = "SELECT cad.*, cad.adhoc_id id, cad.adhoc_uuid uuid
			FROM `cse_corporation_adhoc` cad
			INNER JOIN cse_case ccase
			ON (cad.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = '" . $case_id . "')
			INNER JOIN `cse_corporation` corp
			ON (cad.corporation_uuid = corp.corporation_uuid
			AND corp.corporation_id = '" . $corporation_id . "')
			WHERE 1
			AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
			AND corp.deleted = 'N'
			AND cad.deleted = 'N'
			ORDER BY `adhoc` ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("case_id", $case_id);
		//$stmt->bindParam("corporation_id", $corporation_id);
		$stmt->execute();
		$adhocs = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($adhocs);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAdhocsInfo($case_id, $corporation_id, $adhoc = "") {
	session_write_close();
	
    $sql = "SELECT cad.*, cad.adhoc_id id, cad.adhoc_uuid uuid";
	if ($case_id != "") {
		$sql .= ", ccc.injury_uuid ";
	}
	$sql .= "
		FROM `cse_corporation_adhoc` cad";
	if ($case_id != "") {
		$sql .= " 
			INNER JOIN cse_case ccase
			ON (cad.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = :case_id)";
	}
	$sql .= " 
	INNER JOIN `cse_corporation` corp
	ON (cad.corporation_uuid = corp.corporation_uuid
	AND corp.corporation_id = :corporation_id)";
	
	if ($case_id != "") {
		$sql .= " 
		INNER JOIN cse_case_corporation ccc
		ON corp.corporation_uuid = ccc.corporation_uuid AND ccase.case_uuid = ccc.case_uuid AND ccc.deleted = 'N'";
	}
	$sql .= " 
	WHERE 1
	";
	if ($adhoc!="") {
		$sql .= " 
		AND cad.adhoc = '" . $adhoc . "'";
	}
	$sql .= " 
	AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
			AND corp.deleted = 'N'
			AND cad.deleted = 'N'
			ORDER BY `adhoc` ASC";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($case_id != "") {
			$stmt->bindParam("case_id", $case_id);
		}
		$stmt->bindParam("corporation_id", $corporation_id);
		$stmt->execute();
		$adhocs = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		return $adhocs;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>