<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/chat', function (RouteCollectorProxy $app) {
		$app->get('/{chat_id}', 'getChat');

		$app->post('/add', 'addChat');
		$app->post('/save', 'saveChat');
		$app->post('/read', 'readChat');
		$app->post('/delete', 'deleteChats');

		$app->post('/create', 'createChat');
		$app->post('/poll', 'pollChat');
	});
	$app->get('/chatlatest', 'latestChatRequest');
	$app->get('/chatread/{thread_id}', 'getChatbox');
	$app->get('/chatnew', 'checkChatbox');

	$app->post('/thread/delete', 'deleteThread');
})->add(Api\Middleware\Authorize::class);

function getChatbox($thread_id) {
	session_write_close();
	$thread_id = clean_html($thread_id);
	if ($thread_id==-1) {
		//return a count instead
		checkChatbox();
		return;
	}
    $sql = "SELECT DISTINCT cht.*, cht.chat_id id, cht.chat_uuid uuid, 
		cht.chat_id id, cht.chat_uuid uuid, ccu.read_status, ccu.read_date, 
		ct.thread_id, ct.thread_uuid, use.user_uuid, use.user_id, use.nickname, `ccu`.`type`
		FROM `cse_chat` cht
		INNER JOIN `cse_chat_user` ccu
		ON cht.chat_uuid = ccu.chat_uuid AND ccu.type = 'from'
		INNER JOIN ikase.`cse_user` `use`
		ON ccu.user_uuid = use.user_uuid
		INNER JOIN cse_thread_chat ctc
		ON cht.chat_uuid = ctc.chat_uuid
		INNER JOIN cse_thread ct
		ON ctc.thread_uuid = ct.thread_uuid
		WHERE 1
		AND cht.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ct.thread_id = :thread_id
		AND cht.deleted = 'N'
		AND ct.deleted = 'N'
		ORDER BY cht.chat_id ASC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("thread_id", $thread_id);
		$stmt->execute();
		
		$chats = $stmt->fetchAll(PDO::FETCH_OBJ);
		$arrChats = array();
		
		foreach ($chats as $chat) {
			$arrChats[] = $chat->uuid;
		}
        echo json_encode($chats);  
		
		//update the chats to me as read
		//where type = to and user_uuid = me
		$sql = "UPDATE `cse_chat_user` 
		SET read_status = 'Y',
		read_date = '" . date("Y-m-d H:i:s") . "'
		WHERE chat_uuid IN ('" . implode("','", $arrChats) . "')
		AND user_uuid = '" . $_SESSION["user_id"] . "'";   
		
		$stmt = DB::run($sql);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function newChatbox($thread_id) {
	session_write_close();
	
    $sql = "SELECT DISTINCT cht.*, cht.chat_id id, cht.chat_uuid uuid, 
		cht.chat_id id, cht.chat_uuid uuid, ccu.read_status, ccu.read_date, ct.thread_id, ct.thread_uuid,
		use.user_uuid, use.user_id, use.nickname, `ccu`.`type`
		FROM `cse_chat` cht
		INNER JOIN `cse_chat_user` ccu
		ON cht.chat_uuid = ccu.chat_uuid
		INNER JOIN ikase.`cse_user` `use`
		ON ccu.user_uuid = use.user_uuid
		INNER JOIN cse_thread_chat ctc
		ON cht.chat_uuid = ctc.chat_uuid
		INNER JOIN cse_thread ct
		ON ctc.thread_uuid = ct.thread_uuid
		WHERE 1
		AND cht.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ccu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND ct.thread_id = :thread_id
		AND cht.deleted = 'N'
		AND ct.deleted = 'N'
		AND `ccu`.`type` = 'to'
		AND `ccu`.read_status = 'N'
		ORDER BY cht.chat_id DESC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("thread_id", $thread_id);
		$stmt->execute();
		$chats = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($chats);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function latestChatRequest() {
	$sql = "SELECT MAX(thread_id) thread_id
		FROM `cse_chat` cht
		INNER JOIN `cse_chat_user` ccu
		ON cht.chat_uuid = ccu.chat_uuid
		INNER JOIN cse_thread_chat ctc
		ON cht.chat_uuid = ctc.chat_uuid
		INNER JOIN cse_thread ct
		ON ctc.thread_uuid = ct.thread_uuid
		WHERE 1
		AND cht.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ccu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND cht.deleted = 'N'
		AND `ccu`.`type` = 'to'
		AND `ccu`.read_status = 'N'";
	//die($sql);	
	try {
		$stmt = DB::run($sql);
		$chat = $stmt->fetchObject();
		
		echo json_encode(array("id"=>$chat->thread_id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function checkChatbox() {
	//check for urgent chat first, then general chat if nothing found
	$sql = "SELECT COUNT( ct.thread_id) chat_count
		FROM `cse_chat` cht
		INNER JOIN `cse_chat_user` ccu
		ON cht.chat_uuid = ccu.chat_uuid
		INNER JOIN cse_thread_chat ctc
		ON cht.chat_uuid = ctc.chat_uuid
		INNER JOIN cse_thread ct
		ON ctc.thread_uuid = ct.thread_uuid
		WHERE 1
		AND cht.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ccu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND cht.deleted = 'N'
		AND ct.deleted = 'N'
		AND ct.subject = 'Urgent'
		AND `ccu`.`type` = 'to'
		AND `ccu`.read_status = 'N'
		ORDER BY cht.chat_id DESC";
	//die($sql);	
	try {
		$stmt = DB::run($sql);
		$chat_count = $stmt->fetchObject();
		if ($chat_count->chat_count > 0) {
			echo json_encode(array("count"=>$chat_count->chat_count, "urgency"=>"urgent"));
			die();
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	
	$sql = "SELECT COUNT( ct.thread_id) chat_count
		FROM `cse_chat` cht
		INNER JOIN `cse_chat_user` ccu
		ON cht.chat_uuid = ccu.chat_uuid
		INNER JOIN cse_thread_chat ctc
		ON cht.chat_uuid = ctc.chat_uuid
		INNER JOIN cse_thread ct
		ON ctc.thread_uuid = ct.thread_uuid
		WHERE 1
		AND cht.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ccu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND cht.deleted = 'N'
		AND ct.deleted = 'N'
		AND `ccu`.`type` = 'to'
		AND `ccu`.read_status = 'N'
		ORDER BY cht.chat_id DESC";
	//die($sql);	
	try {
		$stmt = DB::run($sql);
		$chat_count = $stmt->fetchObject();
		
		echo json_encode(array("count"=>$chat_count->chat_count, "urgency"=>"normal"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addThread($from, $subject) {
	$thread_uuid = uniqid("TD", false);
	$sql = "INSERT INTO `cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . date("Y-m-d H:i:s") . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
	//die($sql);			
	try { 
		$stmt = DB::run($sql);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function getChat($chat_id) {
	$sql = "SELECT cht.*, cht.chat_id id, cht.chat_uuid uuid
		FROM `cse_chat` cht
		WHERE cht.chat_id = :chat_id
		AND cht.customer_id = " . $_SESSION['user_customer_id'] . "
		AND cht.deleted = 'N'
		ORDER BY cht.chat_id DESC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("chat_id", $chat_id);
		$stmt->execute();
		$chat = $stmt->fetchObject();

        echo json_encode($chat);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getChatInfo($chat_id) {
	$sql = "SELECT cht.*, cht.chat_id id, cht.chat_uuid uuid
		FROM `cse_chat` cht
		WHERE cht.chat_id = :chat_id
		AND cht.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("chat_id", $chat_id);
		$stmt->execute();
		$chat = $stmt->fetchObject();

        return $chat;     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function saveChat() {
	$chat_id = passed_var("chat_id", "post");
	//if 0, create
	if ($chat_id == 0) {
		$chat_id = createChat();
		
		die(json_encode(array("chat_id"=>$chat_id)));
	}
	$chat_to = passed_var("chat_to", "post");
	$message = passed_var("message", "post");
	$from = $_SESSION["user_nickname"];
	
	$arrMessage = array("from"=>$from, "message"=>$message );
	//die(json_encode($arrMessage));
	try {
		$db = getConnection();
		
		//first look up the current content, and then add the new content
		$chat = getChatInfo($chat_id);
		
		$messages = $chat->chat;
		
		if ($messages!="") {
			$arrChats = json_decode($messages);
		} else {
			//no messages yet
			$arrChats = array();
			
			$chat_dir = $_SERVER['DOCUMENT_ROOT'] . '\\chats\\' . $_SESSION['user_customer_id'];
			if (!is_dir($chat_dir)) {
				mkdir($chat_dir, 0755, true);
			}
			//from
			$user_id = $_SESSION["user_plain_id"];		
			$new_content = "";
			$filename = $chat_dir . '\\changed_' . $user_id . '.txt';
			if (!file_exists($filename)) {
				if (!$handle = fopen($filename, 'w')) {
					$error = "Cannot open file ($filename)";
					echo json_encode($error);
					exit;
				}
				if (fwrite($handle, "") === FALSE) {
				//if (!file_put_contents($filename, $newcontent, FILE_APPEND)) {
				   $error = "Cannot write to file ($filename)";
				   echo json_encode($error);
				   exit;
				}
			}
			
			//to
			$user_id = $chat_to;
			$filename = $chat_dir . '\\changed_' . $user_id . '.txt';
			if (!file_exists($filename)) {
				$mode = 'w';
			} else {
				$mode = 'a';
			}		
			if (!$handle = fopen($filename, $mode)) {
				$error = "Cannot open file ($filename)";
				echo json_encode($error);
				exit;
			}
			if (fwrite($handle, $chat_id . "|" . $_SESSION["user_plain_id"] . "|" . $_SESSION["user_name"] . "\r\n") === FALSE) {
			   echo json_encode($error);
			   exit;
			}
		}
		$arrChats[] = array("from"=>$_SESSION["user_nickname"], "message"=>$message, "timestamp"=>date("Y-m-d H:i:s"));
		$message = json_encode($arrChats);
		
		$sql = "UPDATE cse_chat ct
		SET ct.`chat` = '" . addslashes($message) . "'
		WHERE `chat_id` = " . $chat_id;
		
		$stmt = DB::run($sql);
		
		$chat_dir = $_SERVER['DOCUMENT_ROOT'] . '\\chats\\' . $_SESSION['user_customer_id'];
		//now we need to update the chat text file
		$filename = $chat_dir . '\\chat_' . $chat_id . '.txt';
		if (!$handle = fopen($filename, 'w')) {
			$error = "Cannot open file ($filename)";
			echo json_encode($error);
			exit;
		}		
		if (fwrite($handle, $message) === FALSE) {
		   $error = "Cannot write to file ($filename)";
		   echo json_encode($error);
		   exit;
		}
		
		echo json_encode(array("success"=>$chat_id, "messages"=>$message));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function addChat() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$to = "";
	$from = "";	
	$subject = "";
	$table_id = "";
	$thread_id = "";
	$chat = "";
	$attachments = "";
	$db = getConnection();
	//die(print_r($_POST));
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		//part of a thread
		if ($fieldname=="thread_id") {
			$thread_id = $value;
			continue;
		}
		if ($fieldname=="table_id" ) {
			continue;
		}
		if ($fieldname=="attachments") {
			$attachments = $value;
			//continue;
		}
		if ($fieldname=="chat_to") {
			$arrTo = array();
			$arrToID = array();
			explodeRecipient($value, $arrTo, $arrToID, $db);
			$to = implode(";", $arrTo);
			$value = $to;
		}
		//part of a thread
		if ($fieldname=="subject") {
			$subject = $value;
			$subject = addslashes($subject);
		}
		if ($fieldname=="from") {
			$from = $value;
		}
		if ($fieldname=="chat") {
			$chat = $value;
			$chat = addslashes($chat);
		}
		if ($fieldname=="dateandtime") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			}
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$table_uuid = uniqid("KS", false);
	$last_updated_date = date("Y-m-d H:i:s");
	
	//insert the chat
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `dateandtime`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . date("Y-m-d H:i:s") . "','" . $table_uuid . "', " . implode(",", $arrSet) . ")";

	try { 
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		if ($thread_id < 0) {
			//mark other threads as deleted	FOR NOW
			$sql = "UPDATE `cse_thread`, cse_thread_chat, cse_chat_user
					SET `cse_thread`.deleted = 'Y'
					WHERE  `cse_thread`.thread_uuid = cse_thread_chat.thread_uuid
					AND cse_thread_chat.chat_uuid = cse_chat_user.chat_uuid
					AND cse_chat_user.user_uuid = '" . $_SESSION['user_id'] . "'";
			//die($sql);
			try { 
				DB::run($sql);
	$thread_id = DB::lastInsertId();
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
			
			//insert a thread
			$thread_uuid = uniqid("TD", false);
			$sql = "INSERT INTO `cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
					VALUES('" . $_SESSION['user_customer_id'] . "', '" . date("Y-m-d H:i:s") . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
			
			try { 
				DB::run($sql);
	$thread_id = DB::lastInsertId();
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		} else {
			$sql = "SELECT thread_uuid FROM cse_thread WHERE thread_id = " . $thread_id;
			$stmt = DB::run($sql);
			$thread = $stmt->fetchObject();
			$thread_uuid = $thread->thread_uuid;
		}
		//attach attachments
		if ($attachments!="") {
			$arrAttachments = explode("|", $attachments);
			foreach ($arrAttachments as $attachment) {
				$document_name = $attachment;
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "chat attachment";
				$description_html = "chat attachment";
				$type = "chat attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$new_id = $db->lastInsertId();

				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$chat_document_uuid = uniqid("TD", false);
				$sql = "INSERT INTO cse_chat_document (`chat_document_uuid`, `chat_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $chat_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
			}
		}
		//attach the from
		$chat_user_uuid = uniqid("TD", false);
		$sql = "INSERT INTO `cse_chat_user` (`chat_user_uuid`, `chat_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $chat_user_uuid  ."', '" . $table_uuid . "', '" . $_SESSION['user_id'] . "', 'from', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		//die($sql);
		try {
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			die('{"error":{"text":'. $e->getMessage() .'}}'); 
		}
		//attach recipients to chat
		attachRecipients("chat", $table_uuid, $last_updated_date, $arrToID, 'to', $db);
		
		$chat_table_uuid = uniqid("KA", false);
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the chat to the thread 
		$sql = "INSERT INTO cse_thread_" . $table_name . " (`thread_" . $table_name . "_uuid`, `thread_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $chat_table_uuid  ."', '" . $thread_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		//die($sql);
		try {
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		//track now
		//trackChat("insert", $new_id);	
	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	echo json_encode(array("success"=>$thread_id));
}
function readChat() {
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_chat mes, cse_chat_user ccu
			SET ccu.`read_status` = 'Y',
			ccu.read_date = '" . date("Y-m-d H:i:s") . "'
			WHERE mes.`chat_uuid`= ccu.chat_uuid
			AND ccu.type = 'to'
			AND mes.chat_id = :id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		echo json_encode(array("success"=>"chat marked as read"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteChats() {
	$id = passed_var("chat_id", "post");
	$sql = "UPDATE cse_chat cht
			SET cht.`deleted` = 'Y'
			WHERE `chat_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("chat_id", $id);
		$stmt->execute();
		echo json_encode(array("success"=>"chat marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteThread() {
	$thread_id = passed_var("id", "post");
	$sql = "UPDATE cse_thread ct
			SET ct.`deleted` = 'Y'
			WHERE `thread_id`=:thread_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("thread_id", $thread_id);
		$stmt->execute();
		echo json_encode(array("success"=>"thread marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function createChat($chat_to = "") {
	/*
	if ($chat_to == "") {
		$chat_to = passed_var("chat_to", "post");
	}
	if (!is_numeric($chat_to)) {
		$error = array("error"=> array("text"=>"no entry"));
        echo json_encode($error);
		die();
	}
	*/
	$chat_uuid = uniqid("KS", false);
	$sql = "INSERT INTO cse_chat (`chat_uuid`, `dateandtime`, `from`, `chat`, `customer_id`) 
	VALUES ('" . $chat_uuid . "',  '" . date("Y-m-d H:i:s") . "', '" . $_SESSION["user_name"] . "', '', '" . $_SESSION["user_customer_id"] . "')";
	try {
		DB::run($sql);
	$chat_id = DB::lastInsertId();
		
		$chat_file = "../chats/" . $_SESSION["user_customer_id"] . "/chat_" . $chat_id . ".txt";
		if (file_exists($chat_file)) {
			//delete the file, very powerful, be careful
			unlink($chat_file);
		}
		return $chat_id;
		/*
		$chat_dir = $_SERVER['DOCUMENT_ROOT'] . '\\chats\\' . $_SESSION['user_customer_id'];
		if (!is_dir($chat_dir)) {
			mkdir($chat_dir, 0755, true);
		}
		//from
		$user_id = $_SESSION["user_plain_id"];		
		$new_content = "";
		$filename = $chat_dir . '\\changed_' . $user_id . '.txt';
		if (!file_exists($filename)) {
			if (!$handle = fopen($filename, 'w')) {
				$error = "Cannot open file ($filename)";
				echo json_encode($error);
				exit;
			}
			if (fwrite($handle, "") === FALSE) {
			//if (!file_put_contents($filename, $newcontent, FILE_APPEND)) {
			   $error = "Cannot write to file ($filename)";
			   echo json_encode($error);
			   exit;
			}
		}
		
		//to
		$user_id = $chat_to;
		$filename = $chat_dir . '\\changed_' . $user_id . '.txt';
		if (!file_exists($filename)) {
			$mode = 'w';
		} else {
			$mode = 'a';
		}		
		if (!$handle = fopen($filename, $mode)) {
			$error = "Cannot open file ($filename)";
			echo json_encode($error);
			exit;
		}
		if (fwrite($handle, $chat_id . "|" . $_SESSION["user_plain_id"] . "|" . $_SESSION["user_name"] . "\r\n") === FALSE) {
		   echo json_encode($error);
		   exit;
		}
		
		//echo json_encode(array("chat_id"=>$chat_id, "from"=>$_SESSION["user_plain_id"]));
		return $chat_id;
		*/
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function pollChat() {
	$user_id = $_SESSION['user_plain_id'];
	$timestamp = passed_var("timestamp", "post");
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	$chat_dir = $_SERVER['DOCUMENT_ROOT'] . '\\chats\\' . $_SESSION['user_customer_id'];
	//die($_SERVER['DOCUMENT_ROOT'] . $chat_dir);
	if (!is_dir($chat_dir)) {
		mkdir($chat_dir, 0755, true);
	}
	$filename = $chat_dir . '\\changed_' . $user_id . '.txt';
	if (!file_exists($filename)) {
		$new_content = "";
		if (!$handle = fopen($filename, 'w')) {
			$error = "Cannot open file ($filename)";
			echo json_encode($error);
			exit;
		}
		if (fwrite($handle, $new_content) === FALSE) {
		   $error = "Cannot write to file ($filename)";
		   echo json_encode($error);
		   exit;
		}
	}
	if ($timestamp=="") {
		$lastmodif = 0;
	} else {
		$lastmodif = $timestamp;
	}
	$currentmodif = filemtime($filename);
	
	//die(json_encode(array("curr"=>$currentmodif, "timestamp"=>$timestamp)));
	
	while($currentmodif <= $lastmodif) {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		
		//i don't want to have this run forever
		if ($total_time > 20) {
			$response = array();
			$response['msg'] = "";
			$response['timestamp'] = $lastmodif;
			
			echo json_encode($response);
			die();
		}
		usleep(10000);
		clearstatcache();
		$currentmodif = filemtime($filename);
	}
	
	$response = array();
	
	if ($currentmodif <= $lastmodif) {
		//die($currentmodif . " <= " . $lastmodif);
		$response['msg'] = "";
		$response['timestamp'] = $lastmodif;
		$response['diff'] = 0;
	} else {
		$contents = file_get_contents($filename);
		if ($contents=="") {
			$response['msg'] = "";
			$response['timestamp'] = $lastmodif;
			$response['diff'] = 0;
		} else {
			$arrContents = explode("\r\n", $contents);
			//die(print_r($arrContents));
			if (count($arrContents) > 1) {
				$contents = $arrContents[count($arrContents) - 2];
			} else {
				$contents = "";
			}
			$response['msg'] = $contents;
			$response['timestamp'] = $currentmodif;
			$response['diff'] = $currentmodif - $lastmodif;
		}
	}
	echo json_encode($response);
}

