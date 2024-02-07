<?php
$app->get('/negotiation/:id', authorize('user'), 'getNegotiation');
$app->get('/negotiations/:case_id', authorize('user'), 'getNegotiations');
$app->get('/negotiationsfirm/:case_id/:corporation_id', authorize('user'), 'getNegotiationsbyFirm');

$app->post('/negotiation/save', authorize('user'), 'saveNegotiation');
$app->post('/negotiation/delete', authorize('user'), 'deleteNegotiation');

function getNegotiationInfo($id) {
	return getNegotiation($id, true);
}
function getNegotiation($id, $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "
	SELECT ng.*, 
	IFNULL(corp.corporation_id, 0) corporation_id,
	IFNULL(corp.corporation_uuid, '') corporation_uuid,
	ccase.case_id, ccase.case_uuid, ng.negotiation_id id , ng.negotiation_uuid uuid 
	FROM cse_negotiation ng
	INNER JOIN cse_case_negotiation cng
	ON ng.negotiation_uuid = cng.negotiation_uuid
	INNER JOIN cse_case ccase
	ON cng.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN cse_corporation_negotiation ccn
	ON ng.negotiation_uuid = ccn.negotiation_uuid
	LEFT OUTER JOIN cse_corporation corp
	ON ccn.corporation_uuid = corp.corporation_uuid
	
	LEFT OUTER JOIN ikase.cse_user usr
	ON ng.worker = usr.nickname
		
	WHERE ng.negotiation_id = :id
	AND ccase.customer_id = :customer_id
	AND ng.deleted = 'N'";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$neg = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;       
		
		if ($blnReturn) { 
			//die(print_r($neg));
			return $neg;
		} else {
			echo json_encode($neg);
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getNegotiationsbyFirm($case_id, $corporation_id) {
	getNegotiations($case_id, $corporation_id); 
}
function getNegotiations($case_id, $corporation_id = "") {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "
	SELECT ng.*, 
	IFNULL(corp.corporation_id, 0) corporation_id,
	IFNULL(corp.corporation_uuid, '') corporation_uuid, 
	ng.negotiation_id id 
	FROM cse_negotiation ng
	INNER JOIN cse_case_negotiation cng
	ON ng.negotiation_uuid = cng.negotiation_uuid
	INNER JOIN cse_case ccase
	ON cng.case_uuid = ccase.case_uuid
	";
	if ($corporation_id != "") {
		$sql .= "		
		INNER JOIN cse_corporation_negotiation ccn
		ON ng.negotiation_uuid = ccn.negotiation_uuid
		INNER JOIN cse_corporation corp
		ON ccn.corporation_uuid = corp.corporation_uuid";
	} else {
		$sql .= "		
		LEFT OUTER JOIN cse_corporation_negotiation ccn
		ON ng.negotiation_uuid = ccn.negotiation_uuid
		LEFT OUTER JOIN cse_corporation corp
		ON ccn.corporation_uuid = corp.corporation_uuid";
	}
	$sql .= "
	WHERE ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id
	AND ng.deleted = 'N'";
	if ($corporation_id != "") {
		$sql .= "
		AND corp.corporation_id = :corporation_id";	
	}
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		if ($corporation_id != "") {
			$stmt->bindParam("corporation_id", $corporation_id);
		}
		$stmt->execute();
		$negs = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;       
		
		echo json_encode($negs);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function saveNegotiation() {
	session_write_close();
	
	$arrFields = array();
	$arrSet = array();
	$case_id = 0;
	$table_name = "";
	$table_id = passed_var("table_id", "post");
	$corporation_uuid = "";
	$case_uuid = "";
	$user_uuid = "";
	$corporation_id = "";
	//$case_id = passed_var("case_id", "post");
	//die($table_id);
	//$table_id = 1;
	//die(print_r($_POST));
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
			continue;
		}
		if ($fieldname=="worker"){
			$user_id = $value;
			if (is_numeric($user_id)) {
				$user = getUserInfo($user_id);
				$value = $user->nickname;
			}
		}
		if ($fieldname=="firm_select"){
			$corporation_id = $value;
			$corp = getCorporationInfo($corporation_id);
			
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="negotiation_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		//die("before");
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
		//die("after");
	}	
	
	
	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
		//die($sql);
		try {
			$db = getConnection();
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$new_id = $db->lastInsertId();
			
			$stmt = null; $db = null;
			
			//attach to case
			if ($case_uuid!="") {
				$last_updated_date = date("Y-m-d H:i:s");
				$case_negotiation_uuid = uniqid("KA", false);
				$attribute = "main";
				
				$sql = "INSERT INTO cse_case_negotiation (`case_negotiation_uuid`, `case_uuid`, `negotiation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_negotiation_uuid . "', '" . $case_uuid . "', '" . $table_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				//echo $sql . "\r\n";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
			}
			
			if ($corporation_id!="") {
				$corporation_uuid = $corp->uuid;
				$last_updated_date = date("Y-m-d H:i:s");
				$corporation_negotiation_uuid = uniqid("KA", false);
				$attribute = "main";
				
				$sql = "INSERT INTO cse_corporation_negotiation (`corporation_negotiation_uuid`, `corporation_uuid`, `negotiation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $corporation_negotiation_uuid . "', '" . $corporation_uuid . "', '" . $table_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				//echo $sql . "\r\n";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
			}
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			//trackNegotiation("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		//check if firm was assigned
		$neg = getNegotiationInfo($table_id);
		//die(print_r($neg));
		$prev_corporation_id = $neg->corporation_id;
		
		//where
		$where_clause = "= '" . $table_id . "'";
		$where_clause = "`" . $table_name . "_id`" . $where_clause . "
		AND `customer_id` = " . $_SESSION['user_customer_id'];

		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET " . implode(",", $arrSet) . "
		WHERE " . $where_clause;
		
		//die(implode(",", $arrSet));
		
		//die($sql);
		
		try {
			$db = getConnection();
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$stmt = null; $db = null;
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
			//track now	
			//trackNegotiation("update", $table_id);	
			
			if ($prev_corporation_id!=$corporation_id) {
				if ($corporation_id > 0) {
					$corporation_uuid = $corp->uuid;
					$last_updated_date = date("Y-m-d H:i:s");
					$corporation_negotiation_uuid = uniqid("KA", false);
					$attribute = "main";
					
					$sql = "INSERT INTO cse_corporation_negotiation (`corporation_negotiation_uuid`, `corporation_uuid`, `negotiation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $corporation_negotiation_uuid . "', '" . $corporation_uuid . "', '" . $neg->uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
					//echo $sql . "\r\n";
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$stmt = null; $db = null;
				}
			}
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
}
function deleteNegotiation() {
	$id = passed_var("id", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "UPDATE `cse_negotiation` 
			SET `deleted` = 'Y'
			WHERE `negotiation_id` = :id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		echo json_encode(array("success"=>"negotiation marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackNegotiation("delete", $id);
}
?>