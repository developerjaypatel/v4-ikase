<?php
$app->get('/contacts', authorize('user'), 'getContacts');
$app->get('/contactsblocked', authorize('user'), 'getBlockedContacts');
$app->get('/contacts/:id', authorize('user'), 'getContact');
$app->get('/contact/cases/:id', authorize('user'), 'getContactCases');

$app->post('/contact/delete', authorize('user'), 'deleteContact');
$app->post('/contact/add', authorize('user'), 'addContact');
$app->post('/contact/update', authorize('user'), 'updateContact');

function getContactCases($contact_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT DISTINCT ccase.*
	FROM cse_case ccase
	INNER JOIN cse_case_message ccm
	ON ccase.case_uuid = ccm.case_uuid
	INNER JOIN cse_message mess
	ON ccm.message_uuid = mess.message_uuid
	INNER JOIN cse_message_contact cmc
	ON mess.message_uuid = cmc.message_uuid
	INNER JOIN cse_contact cont
	ON cmc.contact_uuid = cont.contact_uuid
	WHERE cont.contact_id = :contact_id
	AND ccase.customer_id = :customer_id
	ORDER BY IF (ccase.case_name = '', IF(ccase.file_number='', ccase.case_number, ccase.file_number), ccase.case_name)";
	try {		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("contact_id", $contact_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($cases);
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
/*
function getTokenContact() {
	if (!isset($_GET["q"])) {
		return false;
	}
	$search_term = passed_var("q", "get");
	
	//return a row if id is valid
	$sql = "SELECT `contact`.*, `contact`.`contact_id` `id` , `contact`.`contact_uuid` `uuid`
		FROM `cse_contact` `contact` 
		WHERE `contact`.`contact_id` = :id
		AND `contact`.`customer_id` = " . $_SESSION['user_customer_id'] . "
		AND `contact`.deleted = 'N'";
	
	if ($search_term != "") {	
		$sql .= " AND (";
		$arrSearch[] = " contact.`email` LIKE '%" . $search_term . "%' ";
		
		$sql .= implode(" OR ", $arrSearch);
		$sql .= ")";
	}
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		
		$stmt->execute();
		$contacts = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		//die($sql);

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($contacts);
        } else {
            echo $_GET['callback'] . '(' . json_encode($contacts) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	
	
}
*/
function getContact($id) {
	//return a row if id is valid
	$sql = "SELECT DISTINCT `contact`.*, `contact`.contact_id id , `contact`.contact_uuid uuid
			FROM `cse_contact` `contact`
			INNER JOIN cse_user cu
			ON `contact`.user_uuid = cu.user_uuid
			AND cu.deleted = 'N'
			AND contact.deleted = 'N'
			
			WHERE `contact`.`contact_id` = " . $id . "
			AND `contact`.`customer_id` = " . $_SESSION['user_customer_id'] . "
			AND `contact`.`deleted` = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		
		$stmt->execute();
		$contact = $stmt->fetchObject();
		$db = null;
		//die($sql);

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($contact);
        } else {
            echo $_GET['callback'] . '(' . json_encode($contact) . ');';
        }
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getContactInfo($id) {
	//return a row if id is valid

	$sql = "SELECT `contact`.*, `contact`.`contact_id` `id`, `contact`.`contact_uuid` `uuid`
		FROM `cse_contact` `contact` 
		WHERE `contact`.`contact_id` = :id
		AND `contact`.`customer_id` = " . $_SESSION['user_customer_id'] . "
		AND `contact`.`deleted` = 'N'";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		
		$stmt->execute();
		$contact = $stmt->fetchObject();
		$db = null;

        return $contact;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getContactInfoByEmail($email) {
	//return a row if id is valid

	$sql = "SELECT `contact`.*, `contact`.`contact_id` `id`, `contact`.`contact_uuid` `uuid`
		FROM `cse_contact` `contact` 
		WHERE `contact`.`email` = :email
		AND `contact`.`customer_id` = " . $_SESSION['user_customer_id'] . "
		AND `contact`.`deleted` = 'N'
		LIMIT 0, 1";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("email", $email);
		
		$stmt->execute();
		$contact = $stmt->fetchObject();
		$db = null;

        return $contact;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBlockedContacts() {
	getContacts(true);
}
function getContacts($blnBlocked = false) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$user_uuid = $_SESSION["user_id"];
	
    $sql = "SELECT DISTINCT `contact`.*, `contact`.contact_id id , `contact`.contact_uuid uuid,
			IF(tracks.init_count IS NULL, 0, 1) initialized,
            IFNULL(maxx_from.message_count, 0) messages_received, IFNULL(maxx_from.last_email, '') last_email_received,
            IFNULL(maxx_to.message_count, 0) messages_sent, IFNULL(maxx_to.last_email, '') last_email_sent
			FROM `cse_contact` `contact`
			INNER JOIN cse_user cu
			ON `contact`.user_uuid = cu.user_uuid AND cu.deleted = 'N'
			
			LEFT OUTER JOIN (
				SELECT contact_id, COUNT(contact_track_id) init_count
				FROM cse_contact_track
				WHERE operation = 'initialized'
                GROUP BY contact_id
			) tracks
			ON contact.contact_id = tracks.contact_id
			
            LEFT OUTER JOIN (
				SELECT cmc.contact_uuid, COUNT(cmc.message_uuid) message_count, MAX(mess.dateandtime) last_email 
				FROM cse_message mess
                INNER JOIN cse_message_contact cmc
                ON mess.message_uuid = cmc.message_uuid
                WHERE cmc.attribute = 'from'
				GROUP BY cmc.contact_uuid
            ) maxx_from
            ON contact.contact_uuid = maxx_from.contact_uuid
            
            LEFT OUTER JOIN (
				SELECT cmc.contact_uuid, COUNT(cmc.message_uuid) message_count, MAX(mess.dateandtime) last_email
				FROM cse_message mess
                INNER JOIN cse_message_contact cmc
                ON mess.message_uuid = cmc.message_uuid
                WHERE cmc.attribute = 'to'
				GROUP BY cmc.contact_uuid
            ) maxx_to
            ON contact.contact_uuid = maxx_to.contact_uuid
            
			WHERE 1
			AND cu.user_uuid = :user_uuid
			AND contact.customer_id = :customer_id
			AND contact.deleted = 'N'
			AND contact.email != ''";
	if (!$blnBlocked) {
		$sql .= "
		AND contact.spam_status = 'OK'";
	} else {
		$sql .= "
		AND contact.spam_status != 'OK'";
	}
	$sql .= "
	ORDER BY `contact`.`contact_id` ASC";
	//die($sql);
	try {
		$sql_contacts = $sql;
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$contacts = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//echo json_encode($contacts);
		$blnRefresh = false;
		if (!$blnBlocked) {
			//make sure contacts are associated with the messages
			foreach($contacts as $contact) {
				if ($contact->initialized==1) {
					continue;
				}
				
				$email = $contact->email;
				$contact_uuid = $contact->uuid;
				$contact_id = $contact->id;
				
				//fix messages
				$sql = "SELECT COUNT(mess.message_uuid) message_count
				FROM cse_message mess
				INNER JOIN cse_message_contact cmc
				ON mess.message_uuid = cmc.message_uuid
				WHERE INSTR(mess.`from`, :email) > 0
				OR INSTR(mess.`message_to`, :email) > 0 
				AND mess.customer_id = :customer_id";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("email", $email);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$counter = $stmt->fetchObject();
				$stmt->closeCursor(); $stmt = null; $db = null;
				
				if ($counter->message_count==0) {
					$blnRefresh = true;
					//loop and assign
					$sql = "SELECT message_id, message_uuid, message_type
					FROM cse_message mess
					WHERE INSTR(mess.`from`, :email) > 0
					OR INSTR(mess.`message_to`, :email) > 0
					AND mess.customer_id = :customer_id";
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("email", $email);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					$messages = $stmt->fetchAll(PDO::FETCH_OBJ);
					$stmt->closeCursor(); $stmt = null; $db = null;
					
					foreach($messages  as $message) {
						$attribute = "from";
						if ($message->message_type=="email") {
							$attribute = "to";
						}
						$message_contact_uuid = uniqid("MC", false);
						$message_id = $message->message_id;
						$message_uuid = $message->message_uuid;
						
						$last_updated_date = date("Y-m-d H:i:s");
						
						$sql = "INSERT INTO cse_message_contact (`message_contact_uuid`, `message_uuid`, `message_id`, `contact_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
						VALUES ('" . $message_contact_uuid . "', '" . $message_uuid . "', '" . $message_id . "', '" . $contact_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', :user_uuid, :customer_id)";
						//echo $sql . "\r\n";
						$db = getConnection();
						$stmt = $db->prepare($sql);  
						$stmt->bindParam("user_uuid", $user_uuid);
						$stmt->bindParam("customer_id", $customer_id);
						$stmt->execute();
						$stmt = null; $db = null;
					}
				}
				//initialized
				trackContact("initialized", $contact_id);
			}
			
			if ($blnRefresh) {
				$db = getConnection();
				$stmt = $db->prepare($sql_contacts);
				$stmt->bindParam("user_uuid", $user_uuid);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$contacts = $stmt->fetchAll(PDO::FETCH_OBJ);
				$stmt->closeCursor(); $stmt = null; $db = null;
			}
		}
		echo json_encode($contacts);
		
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addContact() {
	$request = Slim::getInstance()->request();
	$db = getConnection();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$case_id = "";
	$carrier_uuid = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		if ($table_name == "contacts") {
			$table_name= "contact";
		}
		$fieldname = str_replace("Input", "", $fieldname);
		//fix for defaults
		if ($fieldname=="amount_due" || $fieldname=="payment" || $fieldname=="balance") {
			if ($value=="") {
				$value = 0.00;
			}
		}
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		//FOR NOW
		if ($fieldname=="table_id") {
			continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}	
	$arrFields[] = "`customer_id`";
	$arrSet[] = $_SESSION['user_customer_id'];

	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
			//die(print_r($arrFields));
	//die($sql);		
	$last_updated_date = date("Y-m-d H:i:s");
	try { 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		//track now
		trackContact("insert", $new_id);
		
		$db = null;
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function updateContact() {
	$request = Slim::getInstance()->request();
	$db = getConnection();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$carrier_uuid = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");

		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_uuid") {
			continue;
		}
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	$my_contact = getContactInfo($table_id);
	$table_uuid = $my_contact->uuid;
	
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];

	try {		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		//track now
		trackContact("update", $table_id);
		
		$db = null;
		
		echo json_encode(array("success"=>$table_id)); 
		
		exit();
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function deleteContact() {
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_contact con
			SET con.`deleted` = 'Y'
			WHERE `contact_id`=:id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		//track now
		trackContact("delete", $id);
		
		$db = null;
		echo json_encode(array("success"=>"contact marked as deleted"));
		
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function trackContact($operation, $contact_id) {
	$sql = "INSERT INTO cse_contact_track (`track_user_uuid`, `user_logon`, `operation`, `time_stamp`, `contact_id`, `contact_uuid`, `user_uuid`, `email`, `first_name`, `last_name`, `phone`, `full_address`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', '". date("Y-m-d H:i:s") . "', `contact_id`, `contact_uuid`, `user_uuid`, `email`, `first_name`, `last_name`, `phone`, `full_address`, `customer_id`, `deleted`
	FROM cse_contact
	WHERE 1
	AND contact_id = " . $contact_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		exit();
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
?>