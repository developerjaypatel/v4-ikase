<?php

$app->get('/email/active/:user_id', authorize('user'), 'getActiveEmail');
$app->get('/email/:user_id', authorize('user'), 'getEmail');
$app->post('/email/add', authorize('user'), 'addEmail');
$app->post('/email/update', authorize('user'), 'updateEmail');

function getActiveEmail($user_id) {
	getEmail($user_id, true);
}
/*
function getEmailInfoRepeat($user_id) {
	return getEmail($user_id, false, true);
}
*/
//getEmailInfo is in connection.php
function getEmail($user_id, $blnActive = false, $blnReturn = false) {
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
			AND cue.deleted = 'N'";
	
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
		$email = $stmt->fetchObject();
		$db = null;
		
		if ($blnReturn) {
			return($email);
		} else {
			echo json_encode($email);
			return($email);
		}

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
?>