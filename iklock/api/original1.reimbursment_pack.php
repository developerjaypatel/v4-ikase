<?php
$app->post('/reimbursment/save', authorize('user'), 'saveReimbursment');
$app->post('/employee/reimbursments/save', authorize('user'), 'saveEmployeeReimbursments');

function saveReimbursment() {
	$reimbursment_id = passed_var("reimbursment_id", "post");
	
	if ($reimbursment_id > 0) {
		updateReimbursment();
		return;
	}
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "reimbursment";
	$reimbursment = "";
	$table_id = "";
	
	$pwd = "";
	
	//default attribute
	$table_attribute = "main";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Field", "", $fieldname);
		if ($fieldname=="reimbursment_id") {
			continue;
		}
		if ($fieldname=="reimbursment") {
			$reimbursment = $value;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	/*
	$arrFields[] = "`inine_filed`";
	$arrSet[] = "'" . addslashes($inine_filed) . "'";
	*/
	$table_uuid = uniqid("KS", false);
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "INSERT INTO `" . $table_name . "` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			SELECT '" . $customer_id . "', '" . $table_uuid . "', " . implode(",", $arrSet) . "
			FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `" . $table_name . "`
							WHERE `reimbursment` = :reimbursment
							AND customer_id = '" . $customer_id . "'
						)";
	
	try { 
		
		$db = getConnection();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("reimbursment", $reimbursment);
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
		die($sql);
	}	
}
function updateReimbursment() {
	$request = Slim::getInstance()->request();
	$arrSet = array();
	$where_clause = "";
	$table_name = "reimbursment";
	$table_id = "";
	$table_attribute = "";
	$pwd = "";
	//$inine_filed = "N";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Field", "", $fieldname);
		
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		
		if ($fieldname=="reimbursment_id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}

	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	//die( $sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$table_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>$table_id, "error"=>$e->getMessage())); 
	}
}
function saveEmployeeReimbursments() {
	$user_id = passed_var("user_id", "post");
	$my_user = new systemuser();
	$my_user->id = $user_id;
	$my_user->fetch();

	$customer_id = $_SESSION["user_customer_id"];
	
	try {
		//delete any reimbursment
		$sql = "UPDATE `user_reimbursment`
		SET deleted = 'Y'
		WHERE user_uuid = :user_uuid";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("user_uuid", $my_user->uuid);
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		//die(print_r($_POST));
		
		//cycle through the posts
		foreach($_POST as $fieldname=>$value) {
			$value = passed_var($fieldname, "post");
			
			if (strpos($fieldname, "user_reimbursment_")==0 && $value=="Y") {
				$arrID = explode("_", $fieldname);
				$reimbursment_id = $arrID[count($arrID) - 1];
				
				//get the uuid
				$sql = "SELECT reimbursment_uuid uuid
				FROM reimbursment
				WHERE reimbursment_id = :reimbursment_id";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("reimbursment_id", $reimbursment_id);
				$stmt->execute();
				$reimbursment_info = $stmt->fetchObject();
				
				//die(print_r($reimbursment_info));
				
				$stmt->closeCursor(); $stmt = null; $db = null;
				
				$last_updated_date = date("Y-m-d H:i:s");
				$last_update_user = $_SESSION["user_id"];
				$user_reimbursment_uuid = uniqid("UR");
				
				//rebuild the relationship
				$sql = "INSERT INTO `user_reimbursment` (`user_uuid`, `reimbursment_uuid`, `attribute`, `user_reimbursment_uuid`, `last_update_user`, `last_updated_date`, `customer_id`)
				SELECT :user_uuid, :reimbursment_uuid, 'main', :user_reimbursment_uuid, :last_update_user, :last_updated_date, :customer_id
				FROM dual
						WHERE NOT EXISTS (
							SELECT user_reimbursment_uuid 
							FROM `user_reimbursment`
							WHERE `user_uuid` = :user_uuid
							AND `reimbursment_uuid` = :reimbursment_uuid
							AND customer_id = :customer_id
						)";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("user_uuid", $my_user->uuid);
				$stmt->bindParam("reimbursment_uuid", $reimbursment_info->uuid);
				$stmt->bindParam("user_reimbursment_uuid", $user_reimbursment_uuid);
				$stmt->bindParam("last_update_user", $last_update_user);
				$stmt->bindParam("last_updated_date", $last_updated_date);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				
				$stmt = null; $db = null;
				
				//in case it was already in there
				//delete any reimbursment
				$sql = "UPDATE `user_reimbursment`
				SET deleted = 'N'
				WHERE user_uuid = :user_uuid
				AND `reimbursment_uuid` = :reimbursment_uuid";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("user_uuid", $my_user->uuid);
				$stmt->bindParam("reimbursment_uuid", $reimbursment_info->uuid);
				$stmt->execute();
				
				$stmt = null; $db = null;
			}
		}
		
		echo json_encode(array("success"=>true, "error"=>""));
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "error"=>$e->getMessage())); 
	}
}
?>