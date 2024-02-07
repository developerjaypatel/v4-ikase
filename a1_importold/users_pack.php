<?php
$app->get('/user', authorize('user'),	'getUsers');
$app->get('/currentusers', authorize('user'),	'getCurrentUsers');
$app->get('/contact', authorize('user'),	'getContacts');
$app->get('/users', authorize('user'),	'getAllUsers');
$app->get('/usercontact', authorize('user'),	'getMessageContacts');
$app->get('/attorney', authorize('user'),	'getAttorneys');
$app->get('/user/:id', authorize('user'),	'getUser');

$app->get('/userinfo/:id', authorize('user'),	'getUserInfo');
$app->post('/customerinfo', 'getCustomerID');
$app->get('/usernickname/:nickname', authorize('user'),	'getUserByNickname');
$app->get('/fetchnickname/:nickname', authorize('user'),	'fetchUserByNickname');
$app->get('/anytime', authorize('user'), 'verifyAnytime');

$app->get('/user/events/:event_id/:type', authorize('user'),	'getEventUsers');
$app->get('/user/messages/:message_id/:type', authorize('user'),	'getMessageUsers');
$app->get('/user/tasks/:task_id/:type', authorize('user'),	'getTaskUsers');
$app->get('/user/search/:search_term', authorize('user'),	'findUsers');
$app->get('/contact/search/:search_term', authorize('user'),	'findMessageUsers');

//login and activity track
$app->get('/user/tracksummary/:id', authorize('user'), 'loginTrackSummary');
$app->get('/user/tracksummarybydate/:id/:start_date/:end_date', authorize('user'), 'loginTrackSummaryByDate');

//posts
$app->post('/user/delete', authorize('user'), 'deleteUser');
$app->post('/user/imei', 'imeiUser');
$app->post('/user/add', authorize('user'), 'addUser');
$app->post('/user/update', authorize('user'), 'updateUser');

$app->get('/user/transferwork/:from_id/:to_id', authorize('user'), 'transferWork');

$app->post('/rankpassword', authorize('user'), 'rankPassword');

$app->post('/request/reset', 'requestReset');
$app->post('/request/act', 'performReset');
$app->post('/request/verify', 'verifyKey');

function transferWork($from_id, $to_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$from_user = getUserInfo($from_id);
	$from_user_uuid = $from_user->uuid;
	$from_user_nickname = $from_user->nickname;
	$from_user_id = $from_user->id;
	
	$to_user = getUserInfo($to_id);
	$to_user_uuid = $to_user->uuid;
	$to_user_nickname = $to_user->nickname;
	$to_user_id = $to_user->id;
	
	$notes_uuid = uniqid("TF");
	$case_notes_uuid = uniqid("CN");
	
	$last_updated_date = date("Y-m-d H:i:s");
	$last_update_user = $_SESSION["user_id"];
	
	$last_nickname = $_SESSION["user_nickname"];
	
	$arrSQL = array();
	
	//messages
	$sql = "
	UPDATE cse_message_user tuser, cse_message message
	SET tuser.user_uuid = :to_user_uuid,
	tuser.last_updated_date = :last_updated_date,
	tuser.last_update_user = :last_update_user
	WHERE tuser.message_uuid = message.message_uuid
	AND `message_id` IN (
		SELECT cse_message.message_id 
		FROM cse_message
		WHERE INSTR(message_to, :from_user_nickname) > 0
		AND INSTR(message_to, :to_user_nickname) = 0
	)
	AND tuser.user_uuid = :from_user_uuid
	AND `type` = 'to'
	AND  tuser.customer_id = :customer_id"
	;
	$arrSQL[] = $sql;
	
	$sql = "
	UPDATE cse_message
	SET message_to = REPLACE(message_to, :from_user_nickname, :to_user_nickname)
	WHERE INSTR(message_to, :from_user_nickname) > 0
	AND INSTR(message_to, :to_user_nickname) = 0";
	$arrSQL[] = $sql;
	
	//events
	$sql = "
	UPDATE cse_event_user tuser, cse_event event
	SET tuser.user_uuid = :to_user_uuid,
	tuser.last_updated_date = :last_updated_date,
	tuser.last_update_user = :last_update_user
	WHERE tuser.event_uuid = event.event_uuid
	AND `event_id` IN (
		SELECT cse_event.event_id 
		FROM cse_event
		WHERE INSTR(assignee, :from_user_nickname) > 0
		AND INSTR(assignee, :to_user_nickname) = 0
	)
	AND tuser.user_uuid = :from_user_uuid
	AND `type` = 'to'
	AND  tuser.customer_id = :customer_id"
	;
	$arrSQL[] = $sql;
	
	$sql = "
	UPDATE cse_event
	SET assignee = REPLACE(assignee, :from_user_nickname, :to_user_nickname)
	WHERE INSTR(assignee, :from_user_nickname) > 0
	AND INSTR(assignee, :to_user_nickname) = 0";
	$arrSQL[] = $sql;
	
	//tasks
	$sql = "
	UPDATE cse_task_user tuser, cse_task task
	SET tuser.user_uuid = :to_user_uuid,
	tuser.last_updated_date = :last_updated_date,
	tuser.last_update_user = :last_update_user
	WHERE tuser.task_uuid = task.task_uuid
	AND `task_id` IN (
		SELECT cse_task.task_id 
		FROM cse_task
		WHERE INSTR(assignee, :from_user_nickname) > 0
		AND INSTR(assignee, :to_user_nickname) = 0
	)
	AND tuser.user_uuid = :from_user_uuid
	AND `type` = 'to'
	AND  tuser.customer_id = :customer_id"
	;
	$arrSQL[] = $sql;
	
	$sql = "
	UPDATE cse_task
	SET assignee = REPLACE(assignee, :from_user_nickname, :to_user_nickname)
	WHERE INSTR(assignee, :from_user_nickname) > 0
	AND INSTR(assignee, :to_user_nickname) = 0";
	$arrSQL[] = $sql;

	//add a note to the case	
	$note = "Case Transferred from " . $from_user->user_name . " to " . $to_user->user_name . " by " . $_SESSION["user_name"];
	$sql = "INSERT INTO cse_notes (`notes_uuid`, `type`, `subject`, `note`, `entered_by`, `status`, `customer_id`)
	VALUES(:notes_uuid, 'notication', 'Case Transferred', :note, :last_nickname, 'TRANSFER', :customer_id)";
	$arrSQL[] = $sql;
	
	//attach to all the cases
	$sql = "INSERT INTO cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	SELECT DISTINCT :case_notes_uuid, `case_uuid`, :notes_uuid, 'transfer', :last_updated_date, :last_update_user, `customer_id`
	FROM cse_case
	WHERE (supervising_attorney = :from_user_id OR attorney = :from_user_id OR worker = :from_user_id)";
	$arrSQL[] = $sql;
	
	//change the assignments by number
	$sql = "
	UPDATE cse_case
	SET supervising_attorney = :to_user_id
	WHERE supervising_attorney = :from_user_id;
	
	UPDATE cse_case
	SET attorney = :to_user_id
	WHERE attorney = :from_user_id;
	
	UPDATE cse_case
	SET worker = :to_user_id
	WHERE worker = :from_user_id;";
	
	$arrSQL[] = $sql;
	
	//change the assignments by number
	$sql = "
	UPDATE cse_case
	SET supervising_attorney = :to_user_nickname
	WHERE supervising_attorney = :from_user_nickname;
	
	UPDATE cse_case
	SET attorney = :to_user_nickname
	WHERE attorney = :from_user_nickname;
	
	UPDATE cse_case
	SET worker = :to_user_nickname
	WHERE worker = :from_user_nickname;";
	
	$arrSQL[] = $sql;
	
	$sql = implode(";
	", $arrSQL);
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("to_user_nickname", $to_user_nickname);
		$stmt->bindParam("from_user_nickname", $from_user_nickname);
		$stmt->bindParam("from_user_id", $from_user_id);
		$stmt->bindParam("to_user_id", $to_user_id);
		$stmt->bindParam("from_user_uuid", $from_user_uuid);
		$stmt->bindParam("to_user_uuid", $to_user_uuid);
		$stmt->bindParam("note", $note);
		$stmt->bindParam("notes_uuid", $notes_uuid);
		$stmt->bindParam("case_notes_uuid", $case_notes_uuid);
		$stmt->bindParam("last_nickname", $last_nickname);
		
		$stmt->bindParam("last_updated_date", $last_updated_date);
		$stmt->bindParam("last_update_user", $last_update_user);
		$stmt->bindParam("customer_id", $customer_id);
		//die($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "note"=>$note));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function rankPassword() {
	$password = passed_var("password", "post");
	
	$rank = rank_password($password);
	
	$rank['length'] = strlen($password);
	if ($rank['lowercase']>0 && $rank['uppercase']>0 && $rank['numbers']>0 && $rank['symbols']>0 && $rank['length'] > 5) {
		$success = true;
	} else {
		$success = false;
	}
	echo json_encode(array("success"=>$success, "rank"=>$rank));
}
function performReset() {
	$id = passed_var("id", "post");
	$password = passed_var("password", "post");
	$crypt_key = CRYPT_KEY;
	$password = encrypt($password, $crypt_key);
	
	$sql = "UPDATE ikase.`cse_user` `user`
			SET user.`pwd` = '" . $password . "'
			WHERE `user_id` = :id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode(array("success"=>"password updated"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function verifyKey() {
	$boolAllowMultipleDownload = true;	//reset as many times as you wish until expiration date
	$error = "";
	if(!empty($_POST['key'])){
		$key = passed_var("key", "post");
		//check the DB for the key
		$sql = "SELECT * FROM ikase.cse_resets WHERE resetkey = '" . $key . "' LIMIT 1";
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$reset = $stmt->fetchObject();
			
			if(strtotime($reset->expires)>=time()){
				if(!$reset->resets OR $boolAllowMultipleDownload){
					//move through
					//update the DB to say this file has been reseted
					$sql = "UPDATE ikase.cse_resets SET resets = resets + 1 WHERE resetkey = '". $key . "' LIMIT 1";
					$stmt = $db->prepare($sql);
					$stmt->execute();
				} else {
					//this file has already been reseted and multiple resets are not allowed
					$error = "This password has already been reset.";
				}
			} else {
				//this reset has passed its expiry date
				$error = "This reset has expired.";
			}
			$db = null;
			
			if ($error!="") {
				$error = array("error"=> array("text"=>$error));
				echo json_encode($error);
			} else {	
				//$reset->resets++;
				//echo json_encode($reset);
				$success = array("success"=> array("text"=>"verified"), "user_id"=>$reset->user_id, "end"=>$reset->expires);
				echo json_encode($success);
			}
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>"Key has expired"));
			echo json_encode($error);
		}
	}
}
function requestReset() {
	$key = md5(microtime());
	$email = passed_var("email", "post");
	
	//first check if the email is in our system
	$sql = "SELECT user_id, customer_id, COUNT(user_id) user_count 
	FROM `ikase`.`cse_user`
	WHERE (user_email = :email OR user_logon = :email)
	GROUP BY user_id, customer_id";
	
	try {
		/*
		$db = getConnection();
		$stmt = $db->query($sql);
		$check = $stmt->fetchObject();
		*/
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("email", $email);
		$stmt->execute();
		$check = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if (!is_object($check)) {
			$error = "This email address or user logon is not in our system.  Please contact sales for support.";
			$error = array("error"=> array("text"=>$error));
			echo json_encode($error);
			die();
		}
		if ($check->user_count==1) {
			
			$_SESSION["user_id"] = $check->user_id;
			$_SESSION["user_customer_id"] = $check->customer_id;
			
			$user = getUserInfo($check->user_id);
			$email = $user->user_email;
			
			$sql = "INSERT INTO ikase.cse_resets (`resetkey`, `user_id`, `resetemail`, `expires`, `customer_id`) 
	VALUES ('" . $key . "', '" . $check->user_id . "', '" . addslashes($email) . "', '" . date("Y-m-d H:i:s", (time()+(60*60*2))) ."', '" . $check->customer_id ."')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			//send out the email
			//it's an email, tack on the case name to the value
			$email_message = "Password reset requested.  If you did not request a password reset, please ignore this email";
			$email_message .= "\r\n\r\nPlease click on the link below to reset your password.";
			$email_message .= "\r\n\r\nhttps://www.ikase.org/account.php#reset/" . $key;
			
			//die($email_message);
			$from_address = "donotreply@ikase.org";
			$from_name = "iKase System";
			$subject = "Password Reset Request :: iKase";
			$arrRecipients[] = $email;
			$arrEmailTo = array();
			$arrEmailCc = array();
			$arrEmailBcc = array();
			$request_uuid = uniqid("RQ");
			
			$ccs = "";
			$bccs = "";
			//$blnSent = sendEmail($request_uuid, $from_address, $from_name, $arrRecipients, $arrRecipients, $arrEmailCc, $arrEmailBcc, $subject, $email_message, $db, "", $check->customer_id);
			$html_message = str_replace("\r\n", "<br />", $email_message);
			$text_message = $email_message;
			$attachments = "";
			
			$url = "https://www.matrixdocuments.com/dis/sendit.php";
			$fields = array("from_name"=>$from_name, "from_address"=>$from_address, "to_name"=>$email, "cc_name"=>$ccs, "bcc_name"=>$bccs, "html_message"=>urlencode($html_message), "text_message"=>urlencode($text_message), "subject"=>urlencode($subject), "attachments"=>$attachments);
			//die(print_r($fields));
			$fields_string = "";
			foreach($fields as $key=>$value) { 
				$fields_string .= $key.'='.$value.'&'; 
			}
			rtrim($fields_string, '&');
			$timeout = 5;
			//open connection
			$ch = curl_init();
					
			//set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HEADER, false); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_POST, count($fields_string));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
			
			//execute post
			$result = curl_exec($ch);
			
			//die($result);
			$blnSent = ($result=="sent");
						
			if (!$blnSent) {
				$error = "Send Error";
				$error = array("error"=> array("text"=>$error));
	        	echo json_encode($error);
				die();
			}
		} else {
			if ($check->user_count==0) {
				$error = "This email address is not in our system.  Please contact sales for support.";
				$error = array("error"=> array("text"=>$error));
	        	echo json_encode($error);
				die();
			}
			if ($check->user_count>1) {
				$error = "This email address has current issues.  Please contact sales for support.";
				$error = array("error"=> array("text"=>$error));
	        	echo json_encode($error);
				die();
			}
		}
		
		$success = array("success"=> array("text"=>$check->customer_id));
        echo json_encode($success);
			
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	
}
function findUsers($search_term, $user_job) {
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.`status`, `user`.`calendar_color`, `user`.`personal_calendar`, `user`.access_token, `user`.user_type, `user`.rate, IF(`user`.user_type='1', 'admin', 'user') `role`, `user`.user_id id, `user`.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job, user.user_name name
			FROM ikase.`cse_user` user 
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN `cse_job` job
			ON cjob.job_uuid = job.job_uuid";
	if ($user_job!="") {
		$sql .= " AND user.job LIKE '" . $user_job . "%'";
	}
	//AND user.user_type < 3
	$sql .= " WHERE user.deleted = 'N'
			AND user.activated = 'Y'
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "
			AND (user.user_first_name LIKE '%" . addslashes($search_term) . "%'
			OR user.user_last_name LIKE '%" . addslashes($search_term) . "%'
			OR user.user_name LIKE '%" . addslashes($search_term) . "%'
			OR user.nickname LIKE '%" . addslashes($search_term) . "%'
			)";
	$sql .= " ORDER by user.user_id";
	//echo $sql;
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($users);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function findMessageUsers($search_term, $blnOnlyActive = true) {
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.`status`, `user`.`calendar_color`, `user`.`personal_calendar`, `user`.access_token, `user`.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, `user`.user_id id, `user`.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) `job`, `user`.`user_name` `name`
			FROM ikase.`cse_user` user 
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN `cse_job` job
			ON cjob.job_uuid = job.job_uuid";
	
	//AND user.user_type < 3
	$sql .= " WHERE user.deleted = 'N'
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "
			AND (user.user_first_name LIKE '%" . addslashes($search_term) . "%'
			OR user.user_last_name LIKE '%" . addslashes($search_term) . "%'
			OR user.user_name LIKE '%" . addslashes($search_term) . "%'
			OR user.nickname LIKE '%" . addslashes($search_term) . "%'
			)";
	if ($blnOnlyActive) {
		$sql .= " 
			AND user.activated = 'Y'";
	}
	$sql .= " UNION
            SELECT contact.contact_id user_id, contact.contact_uuid user_uuid, 
			contact.email user_name, '' user_logon, 
            contact.first_name user_first_name, contact.last_name user_last_name, 
            '' nickname, `contact`.email user_email, '' `dateandtime`, '' `status`, 
			'' `calendar_color`, '' `personal_calendar`, '' access_token, 
            '' user_type, '' `role`, contact.contact_id id, contact.contact_uuid uuid, 
			'' job_id, '' job_uuid, '' job, `contact`.email `name`
			FROM `cse_contact` `contact`
			WHERE contact.email LIKE '%" . addslashes($search_term) . "%'
			AND contact.customer_id = '" . $_SESSION["user_customer_id"] . "'
			AND contact.user_uuid = '" . $_SESSION["user_id"] . "'";
			
	$sql .= " ORDER by `name`, IF(user_first_name='', 'ZZZ', user_first_name)";
	//echo $sql;
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($users);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function chatUsers($search_term, $user_job) {
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.`status`, `user`.`calendar_color`, `user`.`personal_calendar`, `user`.access_token, `user`.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, `user`.user_id id, `user`.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job, user.user_name name, IF(users_in.user_id IS NULL, 0, 1) in_or_out
			FROM ikase.`cse_user` user 
			LEFT OUTER JOIN (
				select user_id from ikase.`cse_user`
				WHERE CAST(`dateandtime` AS DATE) = '2015-06-22'
				AND `dateandtime` > '2015-06-22 14:30:00'
				) users_in
			ON user.user_id = users_in.user_id

			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN `cse_job` job
			ON cjob.job_uuid = job.job_uuid";
	if ($user_job!="") {
		$sql .= " AND user.job LIKE '" . $user_job . "%'";
	}
	$sql .= " WHERE user.deleted = 'N'
			AND user.user_type < 3
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "
			AND (user.user_first_name LIKE '%" . addslashes($search_term) . "%'
			OR user.user_last_name LIKE '%" . addslashes($search_term) . "%'
			OR user.user_name LIKE '%" . addslashes($search_term) . "%'
			OR user.nickname LIKE '%" . addslashes($search_term) . "%'
			)";
	$sql .= " ORDER by user.user_id";
	//echo $sql;
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($users);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAttorneys() {
	if (isset($_GET["q"])) {
		$query = passed_var("q", "get");
		if ($query!="") {
			findUsers($query, "Attorney");
			return;
		}
	}
}
function getMessageContacts() {
	if ($_SERVER['REMOTE_ADDR']=='98.112.195.202') {
//		die(print_r($_SESSION));
	}
	if (isset($_GET["q"])) {
		$query = passed_var("q", "get");
		if ($query!="") {
			findMessageUsers($query, false);
			return;
		}
	}
}
function getCurrentUsers() {
	session_write_close();
	$today = date("Y-m-d");
	$customer_id = $_SESSION["user_customer_id"];
	/*
	$sql = "SELECT DISTINCT us.user_id, us.user_name, us.nickname, us.sess_id, us.dateandtime 
	FROM ikase.cse_userlogin ul
	INNER JOIN ikase.cse_user us
	ON ul.user_uuid = us.user_uuid
	WHERE ul.customer_id = :customer_id
	AND ul.status = 'IN'
	AND ul.login_date = :today
	ORDER BY user_name";
	*/
	$sql = "SELECT DISTINCT us.user_id, us.user_name, us.nickname, us.dateandtime, ul.ip_address, ul.timestamp
	FROM ikase.cse_userlogin ul
	INNER JOIN (
		SELECT ul.user_uuid, ul.user_name, MAX(ul.userlogin_id) max_userlogin_id
		FROM ikase.cse_userlogin ul
		WHERE ul.customer_id = :customer_id
		AND ul.login_date = :today
		GROUP BY user_uuid
	) max_login
	ON ul.userlogin_id = max_userlogin_id
	
	INNER JOIN ikase.cse_user us
	ON ul.user_uuid = us.user_uuid
	
	WHERE ul.customer_id = :customer_id
	AND ul.status = 'IN'";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("today", $today);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        echo json_encode($users);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getUsers() {
	session_write_close();
	//activate only
	/*
	if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
		die(print_r($_GET));
	}
	*/
	if (isset($_GET["q"])) {
		$query = passed_var("q", "get");
		if ($query!="") {
			findUsers($query, "");
			return;
		}
	}
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, 
			user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.`status`, 
			`user`.`calendar_color`, `user`.`personal_calendar`, `user`.access_token, `user`.user_type, `user`.rate, 
			IF(`user`.user_type='1', 'admin', 'user') `role`, `user`.user_id id, `user`.user_uuid uuid, 
			job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job, user.adhoc,
			CONCAT(user.user_first_name, ' ', user.user_last_name) name,
			cus.pwd token, `user`.activated
			FROM ikase.`cse_user` user 
			INNER JOIN ikase.`cse_customer` cus
			ON user.customer_id = cus.customer_id
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			WHERE user.deleted = 'N'
			AND user.activated = 'Y'
			AND user.user_type < 3
			AND user.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= "
	ORDER BY IF(user.user_first_name='', user.user_name, user.user_first_name),  user.user_last_name";
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($users);
        } else {
            echo $_GET['callback'] . '(' . json_encode($users) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAllUsers() {
	session_write_close();
	/*
	if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
		die(print_r($_GET));
	}
	*/
	if (isset($_GET["q"])) {
		$query = passed_var("q", "get");
		if ($query!="") {
			findUsers($query, "");
			return;
		}
	}
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.`status`, `user`.`calendar_color`, `user`.`personal_calendar`, `user`.access_token, `user`.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, `user`.user_id id, `user`.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job, CONCAT(user.user_first_name, ' ', user.user_last_name) name, user.adhoc,
			cus.pwd token, `user`.activated
			FROM ikase.`cse_user` user 
			INNER JOIN ikase.`cse_customer` cus
			ON user.customer_id = cus.customer_id
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			WHERE user.deleted = 'N'";
	if ($_SESSION['user_customer_id'] != 1033) {
			$sql .= "
			AND user.user_type < 3";
	}
	$sql .= "
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER BY IF(user.user_first_name='', user.user_name, user.user_first_name),  user.user_last_name";
	if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
	//	die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($users);
        } else {
            echo $_GET['callback'] . '(' . json_encode($users) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function verifyAnytime() {
	//if they are on the whitelisted ip, they can come in because they are still at the office
	$customer = getCustomerInfo($_SESSION["user_customer_id"]);
	$id = $_SESSION["user_plain_id"];
	
	//die(print_r($customer));
	//echo $customer->cus_ip;
	//default
	$blnAnytimeOnly = true;
	if (strpos($customer->cus_ip, $_SERVER['REMOTE_ADDR'])!==false) {	// || $_SERVER['REMOTE_ADDR']=='47.153.51.248'
		//they are at work
		$blnAnytimeOnly = false;
		//die($_SERVER['REMOTE_ADDR']."==47.153.51.248");
	}
	//die($_SERVER['REMOTE_ADDR']."==".$customer->cus_ip);
	getUser($id, $blnAnytimeOnly);
}
function getUser($id, $blnAnytime = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, 
	user.nickname, user.user_email,user.user_cell, `user`.`dateandtime`, `user`.`status`, `user`.`calendar_color`, 
	`user`.`personal_calendar`, `user`.cis_id, `user`.access_token, user.user_type, 
	IF(`user`.user_type='1', 'admin', 'user') `role`, user.user_id id, user.adhoc, 
	user.user_uuid uuid, job.job_id, job.job_uuid, 
			IF(job.job IS NULL, '', job.job) job, user.activated, IFNULL(user.rate, '') rate, IFNULL(user.tax, '') tax
			FROM ikase.`cse_user` user 
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			WHERE user.user_id=:id
			AND user.customer_id = :customer_id
			AND user.deleted = 'N'";
	if ($_SESSION["user_role"]!="masteradmin") {
		$sql .= "
		AND user.user_type < 3";
	}
	if ($blnAnytime) {
		$sql .= "
		AND INSTR(user.adhoc, '\"anytime\":\"Y\"') > 0";
	}
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$user = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($user);
        } else {
            echo $_GET['callback'] . '(' . json_encode($user) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerID() {
	$sql = "INSERT INTO `ikase`.`cse_batchscan_calls` (`request`, `uri`)
			VALUES ('" .  addslashes(json_encode($_REQUEST)) . "', '" . $_SERVER['REQUEST_URI'] . "')";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null; $stmt = null;
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
		return false;
	}
	
	$id = passed_var("id", "post");
	if (!is_numeric($id)) {
		die();
	}
	$sql = "SELECT customer_id 
	FROM ikase.cse_user 
	WHERE user_id = :user_id";
	try {
		$db = getConnection();				
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("user_id", $id);
		$stmt->execute();
		$usr = $stmt->fetchObject();
		$db = null; $stmt->closeCursor(); $stmt = null;
		
		$customer_id = $usr->customer_id;
		
		echo $customer_id;
		die();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function loginTrackSummaryByDate($id, $start_date = '', $end_date) {
	loginTrackSummary($id, $start_date, $end_date);
}
function loginTrackSummary($id, $start_date = '', $end_date = '') {
	if (!is_numeric($id)) {
		die();
	}
	session_write_close();
	
	$user = getUserInfo($id);
	$user_uuid = $user->uuid;
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT thelogs.user_name, thelogs.login_date, 
	DAYOFWEEK(thelogs.login_date) dow, 
	DAYNAME(thelogs.login_date) dayw, thelogs.min_time login, thelogs.max_time logout,  
		IFNULL(trax.last_track, '') last_track, IFNULL(lastview.logout, '') estimated_logout, IFNULL(lastview.last_view, '') last_view, 
		IFNULL(TIMEDIFF(lastview.logout, thelogs.min_time), '') spent_time,
		IFNULL(trax.case_count, 0) case_count, IFNULL(trax.activity_count, 0) activity_count, 
		IFNULL(notes_trax.notes_count, 0) notes_count,
		IFNULL(task_trax.task_count, 0) task_count
	FROM
	(
		SELECT ulog.user_uuid, ulog.user_name, CAST(ulog.dateandtime AS DATE) login_date, 
		MIN(ulog.dateandtime) min_time, MAX(ulog.dateandtime) max_time
		
		FROM ikase.cse_userlogin ulog
	   
		
		WHERE 1
		AND ulog.user_uuid = :user_uuid
		AND ulog.customer_id = :customer_id
		GROUP BY CAST(ulog.dateandtime AS DATE), ulog.user_uuid
		
	) thelogs
	 
		LEFT OUTER JOIN (
			SELECT CAST(cct.time_stamp AS DATE) view_date, cct.user_uuid, MAX(cct.time_stamp) last_view, ADDTIME(MAX(cct.time_stamp), '00:15:00') logout
			FROM cse_case_track cct
			INNER JOIN cse_case ccase
			ON cct.case_id = ccase.case_id
			WHERE user_uuid = :user_uuid
			GROUP BY CAST(cct.time_stamp AS DATE), cct.user_logon
		) lastview
		
		ON thelogs.user_uuid = lastview.user_uuid AND thelogs.login_date = lastview.view_date
		
		LEFT OUTER JOIN (
			SELECT CAST(cca.last_updated_date AS DATE) track_date, cca.last_update_user user_uuid, MAX(cca.last_updated_date) last_track,
			COUNT(DISTINCT cca.case_uuid) case_count,
			COUNT(DISTINCT cca.activity_uuid) activity_count
			FROM cse_case_activity cca
			WHERE last_update_user = :user_uuid
			GROUP BY CAST(cca.last_updated_date AS DATE), cca.last_update_user
		) trax
		ON thelogs.user_uuid = trax.user_uuid AND thelogs.login_date = trax.track_date
		
		LEFT OUTER JOIN (
			SELECT CAST(caa.last_updated_date AS DATE) notes_date, last_update_user user_uuid, COUNT(caa.activity_uuid) notes_count
			FROM cse_case_activity caa
			INNER JOIN cse_case ccase
			ON caa.case_uuid = ccase.case_uuid
			INNER JOIN cse_activity act
			ON caa.activity_uuid = act.activity_uuid
			WHERE 1
			AND last_update_user = :user_uuid
			AND activity_category = 'Notes'
			GROUP BY activity_category,  CAST(last_updated_date AS DATE), last_update_user
		)notes_trax
		ON thelogs.user_uuid = notes_trax.user_uuid AND thelogs.login_date = notes_trax.notes_date
		
		
		LEFT OUTER JOIN (
			SELECT CAST(caa.last_updated_date AS DATE) task_date, last_update_user user_uuid, COUNT(caa.activity_uuid) task_count
			FROM cse_case_activity caa
			INNER JOIN cse_case ccase
			ON caa.case_uuid = ccase.case_uuid
			INNER JOIN cse_activity act
			ON caa.activity_uuid = act.activity_uuid
			WHERE 1
			AND last_update_user = :user_uuid
			AND activity_category = 'task'
			GROUP BY activity_category,  CAST(last_updated_date AS DATE), last_update_user
		)task_trax
		ON thelogs.user_uuid = task_trax.user_uuid AND thelogs.login_date = task_trax.task_date
		
		WHERE 1";
		if ($start_date!="") {
			$sql .= "
			AND thelogs.login_date BETWEEN :start_date AND :end_date";
		}
		$sql .= "
		ORDER BY thelogs.login_date ASC";
	try {
		$db = getConnection();				
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		if ($start_date!="") {
			$stmt->bindParam("start_date", $start_date);
			$stmt->bindParam("end_date", $end_date);
		}
		$stmt->execute();
		$trax = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null; $stmt->closeCursor(); $stmt = null;
		
		echo json_encode($trax);
		
		exit();
	} catch(PDOException $e) {
		//die($sql);
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getUserInfo($id) {
	if (!is_numeric($id)) {
		die();
	}
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, user.user_email,user.user_cell, `user`.`dateandtime`, `user`.`status`, `user`.`personal_calendar`, `user`.`calendar_color`, `user`.access_token, user.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, user.user_id id, user.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job,
			IFNULL(ce.email_name, '') email_name
			FROM ikase.`cse_user` user 
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			LEFT OUTER JOIN cse_user_email cue
			ON user.user_uuid = cue.user_uuid
			LEFT OUTER JOIN cse_email ce
			ON cue.email_uuid = ce.email_uuid
			WHERE user.user_id=:id
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$user = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $user;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function fetchUserByNickname($nickname) {
	getUserByNickname($nickname, true);
}
function getUserByNickname($nickname, $blnEcho = false) {
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, user.user_email,user.user_cell, `user`.`dateandtime`, `user`.`status`, `user`.`personal_calendar`, `user`.`calendar_color`, `user`.access_token, user.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, user.user_id id, user.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job, IFNULL(ce.email_name, '') email_name
			FROM ikase.`cse_user` user 
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			LEFT OUTER JOIN cse_user_email cue
			ON user.user_uuid = cue.user_uuid
			LEFT OUTER JOIN cse_email ce
			ON cue.email_uuid = ce.email_uuid
			WHERE user.nickname=:nickname
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("nickname", $nickname);
		$stmt->execute();
		$user = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

		if (!$blnEcho) {
        	return $user;
		} else {
			echo json_encode($user);
		}
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEventUsers($event_id, $type) {
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.`status`, `user`.`personal_calendar`, `user`.access_token, `user`.user_type, CONCAT(user.user_first_name, ' ', user.user_last_name) name
			FROM ikase.`cse_user` user 
			INNER JOIN ikase.cse_event_user ctu
			ON (user.user_uuid = ctu.user_uuid AND ctu.type = :type)
			INNER JOIN ikase.cse_event mes
			ON ctu.event_uuid = mes.event_uuid
			WHERE user.deleted = 'N'
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "
			AND mes.event_id = :event_id
			ORDER by user.user_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_id", $event_id);
		$stmt->bindParam("type", $type);
		$stmt->execute();
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($kases));
        // Include support for JSONP requests
        echo json_encode($users);
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getMessageUsers($message_id, $type) {
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.`status`, `user`.`personal_calendar`, `user`.access_token, `user`.user_type, CONCAT(user.user_first_name, ' ', user.user_last_name) name
			FROM ikase.`cse_user` user 
			INNER JOIN cse_message_user ctu
			ON (user.user_id = ctu.user_id AND ctu.type = :type)
			INNER JOIN cse_message mes
			ON ctu.message_id = mes.message_id
			WHERE user.deleted = 'N'
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "
			AND mes.message_id = :message_id
			ORDER by user.user_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("message_id", $message_id);
		$stmt->bindParam("type", $type);
		$stmt->execute();
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($kases));
        // Include support for JSONP requests
        echo json_encode($users);
		
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskUsers($task_id, $type) {
    $sql = "SELECT DISTINCT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.`status`, `user`.`personal_calendar`, `user`.access_token, `user`.user_type, CONCAT(user.user_first_name, ' ', user.user_last_name) name
			FROM ikase.`cse_user` user 
			INNER JOIN cse_task_user ctu
			ON (user.user_uuid = ctu.user_uuid AND ctu.type = :type) AND ctu.deleted = 'N'
			INNER JOIN cse_task tsk
			ON ctu.task_uuid = tsk.task_uuid
			WHERE user.deleted = 'N'
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "
			AND tsk.task_id = :task_id
			ORDER by user.user_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("task_id", $task_id);
		$stmt->bindParam("type", $type);
		$stmt->execute();
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($kases));
        // Include support for JSONP requests
        echo json_encode($users);
		
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteUser() {
	$id = passed_var("id", "post");
	$sql = "UPDATE ikase.`cse_user` `user`
			SET user.`deleted` = 'Y'
			WHERE `user_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode(array("success"=>"partie marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackUser("delete", $id);
	exit();
}

function addUser() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$arrAdhoc = array();	//catchall
	$arrAdhocNames = array("courtcalendar", "anytime", "checkrequest", "checkrequest_ask", "checkrequest_settlement", "access_accounts", "employee_reports", "employee_reports_block");
	$table_name = "";
	$table_id = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="user_id" || $fieldname=="case_id" || $fieldname=="table_uuid" || $fieldname=="case_uuid" || $fieldname=="table_id") {
			continue;
		}
		if (in_array($fieldname, $arrAdhocNames)) {
			$arrAdhoc[$fieldname] = $value;
			continue;
		}
		if ($fieldname=="password") {
			$crypt_key = CRYPT_KEY;
			if ($value=="") {
				$value = "password1";
			}
			$value = encrypt($value, $crypt_key);
			$fieldname = "pwd";
		}
		if ($fieldname=="dateandtime") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			}
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$adhoc = json_encode($arrAdhoc);
	$arrFields[] = "`adhoc`";
	$arrSet[] = "'" . addslashes($adhoc) . "'";
		
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO ikase.`cse_" . $table_name ."` (`customer_id`, `dateandtime`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . date("Y-m-d H:i:s") . "','" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	$db = getConnection();
	try { 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		//loop for settings
		//get all the settings for customer
		/*
		$sql = "SELECT * FROM cse_setting WHERE customer_id = " . $_SESSION['user_customer_id'];
		$settings = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		foreach ($settings as $setting) {
			//create new setting 1 by 1
			$new_uuid = uniqid("KS");
			//insert
			
			//attach new setting to user
			$setting_user_uuid = uniqid("KS");
			$sql = "INSERT INTO cse_setting_user (`setting_user_uuid`, `setting_uuid`, `user_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $setting_user_uuid  ."', '" . $table_uuid . "', '" . $setting->uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		}
		*/
		
		//track now
		trackUser("insert", $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	
	//job
	if (isset($_POST["job"])) {
		
		if ($_POST["job"]!="") {
			//now we have to attach the job to the user
			$user_job_uuid = uniqid("KS", false);
			$last_updated_date = date("Y-m-d H:i:s");
			
			$sql = "INSERT INTO ikase.cse_user_job (`user_job_uuid`, `user_uuid`, `job_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $user_job_uuid  . "', '" . $table_uuid . "', '" . passed_var("job", "post") . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			try {
				$stmt = $db->prepare($sql);  	
				$stmt->execute();
				
				//store the job as a value
				$sql = "UPDATE ikase.cse_user user, ikase.cse_user_job ujob, ikase.cse_job job
				SET user.job = job.job
				WHERE user.user_uuid = '" . $table_uuid . "'
				AND user.user_uuid = ujob.user_uuid 
				AND ujob.job_uuid = job.job_uuid";
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
	}
	$db = null;
}
function imeiUser() {
	$request = Slim::getInstance()->request();
	
	$customer_id = passed_var("customer_id", "post");
	$imei = passed_var("imei", "post");
	$user_id = passed_var("user_id", "post");
	
	try {
		//store the job as a value
		$sql = "UPDATE ikase.cse_user user
		SET user.imei_number = :imei
		WHERE user.user_id = :user_id
		AND user.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("imei", $imei);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		//echo $sql;
		echo json_encode(array("success"=>true, "user_id"=>$user_id, "sql"=>$sql));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .', "sql":'. $sql .'}}'; 
	}
	
	trackUser("update", $user_id);
	$db = null;
	exit();
}
function updateUser() {
	$request = Slim::getInstance()->request();
	$arrSet = array();
	$arrAdhoc = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$arrAdhocNames = array("courtcalendar", "anytime", "checkrequest", "checkrequest_ask", "checkrequest_settlement", "access_accounts", "employee_reports", "employee_reports_block");
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if (in_array($fieldname, $arrAdhocNames)) {
			$arrAdhoc[$fieldname] = $value;
			continue;
		}
		//skip fields in update
		if ($fieldname=="case_id" || $fieldname=="user_id" || $fieldname=="case_uuid" || $fieldname=="table_uuid" || $fieldname=="job") {
			continue;
		}
		if ($fieldname=="password") {
			if ($value=="") {
				continue;
			}
			$crypt_key = CRYPT_KEY;
			$value = encrypt($value, $crypt_key);
			$fieldname = "pwd";
			//just in case weak password
			$_SESSION["need_password"] = ($value=="n1ck23" || $value=="password1");;
		}
		if ($fieldname=="dateandtime") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			}
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	$arrSet[] = "`adhoc` = '" . addslashes(json_encode($arrAdhoc)) . "'";
	
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE ikase.`cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];
	
	//die($sql);
	
	$db = getConnection();
	try {
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		echo json_encode(array("success"=>$table_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	//job
	if (isset($_POST["job"])) {
		
		//clear out any previous job
		$sql = "UPDATE ikase.cse_user_job 
		SET deleted = 'Y'
		WHERE user_uuid = '" . passed_var("table_uuid", "post") . "'";
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		if ($_POST["job"]!="") {
			//now we have to attach the job to the user
			$user_job_uuid = uniqid("KS", false);
			$last_updated_date = date("Y-m-d H:i:s");
			
			$sql = "INSERT INTO ikase.cse_user_job (`user_job_uuid`, `user_uuid`, `job_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $user_job_uuid  . "', '" . passed_var("table_uuid", "post") . "', '" . passed_var("job", "post") . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			try {
				$stmt = $db->prepare($sql);  	
				$stmt->execute();
				
				//store the job as a value
				$sql = "UPDATE ikase.cse_user user, ikase.cse_user_job ujob, ikase.cse_job job
				SET user.job = job.job
				WHERE user.user_uuid = '" . passed_var("table_uuid", "post") . "'
				AND user.user_uuid = ujob.user_uuid 
				AND ujob.job_uuid = job.job_uuid";
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
	}
	trackUser("update", $table_id);
	$db = null;
	exit();
}
function trackUser($operation, $user_id) {
	$sql = "INSERT INTO ikase.cse_user_track (`user_uuid`, `user_logon`, `operation`, `user_id`, `customer_id`, `cis_id`, `cis_uid`, `user_type`, `user_name`, `user_first_name`, `user_last_name`, `user_email`, `nickname`, `pwd`, `level`, `job`, `status`, `access_token`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `sess_id`, `dateandtime`, `ip_address`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `user_id`, `customer_id`, `cis_id`, `cis_uid`, `user_type`, `user_name`, `user_first_name`, `user_last_name`, `user_email`, `nickname`, `pwd`, `level`, `job`, `status`, `access_token`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `sess_id`, `dateandtime`, `ip_address`, `deleted`
	FROM ikase.cse_user
	WHERE 1
	AND user_id = " . $user_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
	
		$stmt->execute();
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
?>