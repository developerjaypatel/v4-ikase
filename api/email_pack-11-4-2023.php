<?php
$app->group('/email', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->get('/active/{user_id}', 'getActiveEmail');
	$app->get('/{user_id}/{email_id}', 'getEmail');
	//$app->get('/edit/{email_id}', 'getEmailbyId');
	$app->post('/add', 'addEmail');
	$app->post('/update', 'updateEmail');
	$app->post('/detach', 'detachEmail');
})->add(Api\Middleware\Authorize::class);

function getActiveEmail($user_id) {
	getEmail($user_id, '', true);
}

function getEmailbyId($email_id) {
	$user_id = $_SESSION["user_plain_id"];
	getEmail($user_id, $email_id, true, false);
}

/*
function getEmailInfoRepeat($user_id) {
	return getEmail($user_id, false, true);
}
*/
//getEmailInfo is in connection.php
function getEmail($user_id, $email_id, $blnActive = false, $blnReturn = false) {
	session_write_close();
	$sql = "SELECT e.*, cue.attribute emails_number, cuser.user_id, cuser.user_uuid, e.email_id id, e.email_uuid uuid
			FROM `cse_email` e
			INNER JOIN cse_user_email cue
			ON e.email_uuid = cue.email_uuid
			INNER JOIN ikase.cse_user cuser
			ON (cue.user_uuid = cuser.user_uuid
			AND `cuser`.`user_id` = :user_id)
			WHERE 1
			AND cue.customer_id = :customer_id
			AND cue.deleted = 'N' AND e.deleted = 'N'";
    /*$sql = "SELECT e.*, cue.attribute emails_number, e.email_id id, e.email_uuid uuid
			FROM `cse_email` e
			INNER JOIN cse_user_email cue
			ON e.email_uuid = cue.email_uuid
			WHERE 1
			AND cue.customer_id = :customer_id
			AND cue.deleted = 'N'";*/
			if($email_id == 'all1' || $email_id == 'all2' || $email_id == 'all3' || $email_id == 'all4' || $email_id == 'all5' || $email_id == 'all6'){
				
	}else{
		$sql .= " AND e.email_id = '$email_id'";
	}

	if ($blnActive) {
		$sql .= "
		AND e.active = 'Y'";
	}
	//die($sql); 
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		if($email_id == 'all1' || $email_id == 'all2' || $email_id == 'all3' || $email_id == 'all4' || $email_id == 'all5' || $email_id == 'all6'){
			$email = $stmt->fetchAll();
		}else{
			$email = $stmt->fetchObject();
			
		}
		
		
		if ($blnReturn) {
			return($email);
		} else {
			echo json_encode($email);
		}

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
/*this function soft deletes email record in cse_email table by updating deleted = Y, also insert record in cse_email_tract table*/
function detachEmail() {

	$id = passed_var("id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	session_write_close();
	
	$sql = "UPDATE `cse_email` 
			SET `deleted` = 'Y'
			WHERE `email_id`=$id AND customer_id = $customer_id";
		
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		echo json_encode(array("success"=>"email detached"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//calls this function for inserting record in cse_email_track table
	trackEmailDetach("update", $id);
}

/*This function will insert record in cse_email_track table after soft deleting email in cse_emil table by upading deleted = Y*/
function trackEmailDetach($operation, $email_id) {
	$sql = "INSERT INTO cse_email_track (`user_uuid`, `user_logon`, `operation`, `email_id`, `email_uuid`, `email_name`, `email_server`, `ssl_required`, `email_method` , `email_port` , `email_pwd`, `email_address`, `email_phone` , `cell_carrier` , `read_messages` , `emails_pending` , `customer_id`, `active`,`deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `email_id`, `email_uuid`, `email_name`, `email_server`, `ssl_required`, `email_method`, `email_port`, `email_pwd`, `email_address`, `email_phone`, `cell_carrier`, `read_messages`, `emails_pending`,`customer_id`,`active`,`deleted`
	FROM cse_email
	WHERE 1
	AND email_id = " . $email_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
		$new_id = DB::lastInsertId();		
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}