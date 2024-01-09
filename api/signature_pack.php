<?php
$app->group('/signature', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->get('/{user_id}', 'getSignature');
	$app->post('/add', 'addSignature');
	$app->post('/update', 'updateSignature');
})->add(\Api\Middleware\Authorize::class);

function getSignature($user_id) {
    $sql = "SELECT signature.*, cusi.attribute signatures_number, cuser.user_id, cuser.user_uuid, signature.signature_id id, signature.signature_uuid uuid
			FROM `cse_signature` signature
			INNER JOIN cse_user_signature cusi
			ON signature.signature_uuid = cusi.signature_uuid
			INNER JOIN ikase.cse_user cuser
			ON (cusi.user_uuid = cuser.user_uuid
			AND `cuser`.`user_id` = :user_id)
			WHERE 1
			AND cusi.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cusi.deleted = 'N'";
			//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->execute();
		$signature = $stmt->fetchObject();
		echo json_encode($signature);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getSignatureInfo($user_id) {

    $sql = "SELECT signature.*, cusi.attribute signatures_number, cuser.user_id, cuser.user_uuid, signature.signature_id id, signature.signature_uuid uuid
			FROM `cse_signature` signature
			INNER JOIN cse_user_signature cusi
			ON signature.signature_uuid = cusi.signature_uuid
			INNER JOIN cse_user cuser
			ON (cusi.user_uuid = cuser.user_uuid
			AND `cuser`.`user_id` = :user_id)
			WHERE 1
			AND cusi.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cusi.deleted = 'N'";
			//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->execute();
		$signature = $stmt->fetchObject();
		
		return $signature;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addSignature() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	foreach($_POST as $fieldname=>$value) {
		//$value = passed_var($fieldname, "post");
		if ($fieldname!="signatureInput") {
			$value = passed_var($fieldname, "post");
		} else {
			$value = @processHTML($_POST["signatureInput"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="user_id") {
			//let's get user info
			$user_id = $value;
			$user = getUserInfo($user_id);
			$user_uuid = $user->uuid;
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="table_uuid" || $fieldname=="signature_uuid") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$arrFields[] = "`additional_text`";
	$arrSet[] = "''";
	$arrFields[] = "`image_path`";
	$arrSet[] = "''";
		
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
			//die(print_r($arrFields));
			//die($sql);
	try { 
		
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		$case_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the applicant to the case 
		$sql = "INSERT INTO cse_user_" . $table_name . " (`user_" . $table_name . "_uuid`, `user_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $user_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		try {
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		//track now
		$sql = "track";		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function updateSignature() {
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	//die(print_r($_POST));
	
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="signature") {
			$value = passed_var($fieldname, "post");
		} else {
			$value = $_POST["signature"];
			$value = str_replace("<script", "<!--<script", $value);
			$value = str_replace("</script>", "</script>-->", $value);
			$value = str_replace("<SCRIPT", "<!--<SCRIPT", $value);
			$value = str_replace("</SCRIPT>", "</SCRIPT>-->", $value);
		}
		
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="user_id" || $fieldname=="user_uuid" || $fieldname=="table_uuid" || $fieldname=="signature_uuid") {
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
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
	//$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>$table_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
