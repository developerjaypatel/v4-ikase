<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->get('/limmail', 'limEmailInbox');
	$app->get('/gogetmymail', 'goGetMail');
	$app->get('/processmail', 'processlimEmailInbox');
	$app->get('/pingmail', 'pinglimEmailInbox');
	$app->get('/obtainmail/{customer_id}/{user_id}', 'webEmailInbox');
	$app->get('/getmail', 'getEmailInbox');
	$app->get('/syncmail', 'syncMail');

	$app->group('/webmail', function (RouteCollectorProxy $app) {
		$app->get('', 'webEmailInbox');

		$app->get('/read/{id}', 'readEmail');
		$app->get('/test', 'testEmail');

		$app->get('/verify/{direction}', '2');

		$app->post('/preview', 'previewAttachment');
		$app->post('/delete', 'deleteEmail');
		$app->post('/assign', 'assignEmail');
	});

	// $app->group('/gmail', function (RouteCollectorProxy $app) {
	// 	// $app->get('/token', 'gmailToken');
	// 	// $app->post('/settoken', 'gmailSetToken');
	// 	// $app->get('/refreshtoken', 'refreshToken');
	// 	// $app->post('/cleartoken', 'gmailClearToken');
	// 	// $app->post('/dontuse', 'emailAccountDisactivate');
	// 	// $app->post('/activate', 'emailAccountActivate');

	// 	// $app->post('/assign', 'assignGmail');
	// });
})->add(Api\Middleware\Authorize::class);
$app->post('/webmail/transferattach', 'transferAttach');
$app->post('/webmail/spamcheck', 'spamCheck');

$app->group('/gmail', function (RouteCollectorProxy $app) {
		$app->get('/token', 'gmailToken');
		$app->post('/token', 'gmailToken');
		$app->post('/settoken', 'gmailSetToken');
		$app->get('/refreshtoken', 'refreshToken');
		$app->post('/cleartoken', 'gmailClearToken');
		$app->post('/dontuse', 'emailAccountDisactivate');
		$app->post('/activate', 'emailAccountActivate');

		$app->post('/assign', 'assignGmail');
	});

include("receivemail.class.php");

function emailAccountDisactivate() {
	$user_id = passed_var("user_id", "post");
	$customer_id = passed_var("customer_id", "post");
	try {
		$customer = getCustomerInfo($customer_id);
		$datasource = $customer->data_source;
		
		$db_name = "ikase";
		if ($datasource!="") {
			$db_name .= "_" . $datasource;
		}
		
		$sql = "SELECT ema.email_id 
		FROM `" . $db_name . "`.`cse_user_email` cue
		INNER JOIN `ikase`.`cse_user` usr
		ON cue.user_uuid = usr.user_uuid
		INNER JOIN `" . $db_name . "`.`cse_email` ema
		ON cue.email_uuid = ema.email_uuid
		WHERE usr.user_id = :user_id
		AND usr.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$email = $stmt->fetchObject();
		//die(print_r($adhoc_settings));
		
		if (is_object($email)) {
			$sql = "UPDATE `" . $db_name . "`.`cse_email` 
			SET `active` = 'N'
			WHERE `email_id` = '" . $email->email_id . "'";
			
			//die($sql);
			$db = getConnection();
			$stmt = $db->prepare($sql);
			//$stmt->bindParam("email_id", $email->email_id);
			$stmt->execute();
		}
		
		echo json_encode(array("success"=>true, "sql"=>$sql, "user_id"=>$user_id));
		
	} catch(PDOException $e) {
		$error = array("error1"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}

}
function emailAccountActivate() {
	session_write_close();
	$user_id = $_SESSION["user_plain_id"];
	$customer_id = $_SESSION["user_customer_id"];
	try {
		
		$sql = "SELECT ema.email_id 
		FROM `cse_user_email` cue
		INNER JOIN `ikase`.`cse_user` usr
		ON cue.user_uuid = usr.user_uuid
		INNER JOIN `cse_email` ema
		ON cue.email_uuid = ema.email_uuid
		WHERE usr.user_id = :user_id
		AND usr.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$email = $stmt->fetchObject();
		//die(print_r($adhoc_settings));
		
		if (is_object($email)) {
			$sql = "UPDATE `cse_email` 
			SET `active` = 'Y'
			WHERE `email_id` = '" . $email->email_id . "'";
			
			//die($sql);
			$db = getConnection();
			$stmt = $db->prepare($sql);
			//$stmt->bindParam("email_id", $email->email_id);
			$stmt->execute();
		}
		
		echo json_encode(array("success"=>true, "sql"=>$sql, "user_id"=>$user_id));
		
	} catch(PDOException $e) {
		$error = array("error1"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}

}
function gmailSetToken() {
	$gtoken = passed_var("gtok", "post");
	$gtoken = json_decode($gtoken,true);
	$gtoken = $gtoken['access_token'];
	$user_id = passed_var("user_id", "post");
	$origin = passed_var("origin", "post");
	$refresh_token = passed_var("refresh_token", "post");
	$email_type = passed_var("email_type", "post");
	$user_email_id = passed_var("user_email_id", "post");
	$customer_id = passed_var("customer_id", "post");

	// added by mukesh on 4-5-2023 for getting DB based of customer id
	$data_source = "ikase";
	$sql = "SELECT data_source FROM ikase.cse_customer 
	WHERE customer_id = :customer_id";

	$db = getConnection();

	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$customer = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if(isset($customer) && is_array($customer) && count($customer) > 0) {
        foreach ($customer as $cust) {
        	if(!empty($cust['data_source']))
        	{
        		$data_source .= "_" . $cust['data_source'];
        	}
        }
    }
    // end added by mukesh


	if ($gtoken=="") {
		die("no empties");
	}
	/*
	try {
		$sql = "DELETE FROM cse_gmail WHERE user_id = :user_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $_SESSION["user_plain_id"]);
		$stmt->execute();
		
		$sql = "INSERT INTO cse_gmail (user_id, token)
		VALUES(:user_id, :token)";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $_SESSION["user_plain_id"]);
		$stmt->bindParam("token", $gtoken);
		$stmt->execute();
	} catch(PDOException $e) {
		$error = array("error1"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
	*/
	$sql = "SELECT * FROM $data_source.cse_gmail 
	WHERE user_id = :user_id AND user_email_id = :user_email_id";
	// $db = getConnection();

	$stmt = $db->prepare($sql);
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("user_email_id", $user_email_id);
	$stmt->execute();
	$user = $stmt->fetchObject();	

	if (!is_object($user)) {
		
		$sql = "INSERT INTO $data_source.cse_gmail (user_id, token, origin,refresh_token,email_type,refresh_token_at, user_email_id)
		VALUES(:user_id, :token, :origin, :refresh_token, :email_type, :refresh_token_at, :user_email_id)";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("token", $gtoken);
		$stmt->bindParam("origin", $origin);
		$stmt->bindParam("refresh_token",$refresh_token);
		$stmt->bindParam("email_type",$email_type);
		$stmt->bindParam("refresh_token_at",date("Y-m-d H:i:s"));
		$stmt->bindParam("user_email_id",$user_email_id);
		$stmt->execute();
		
	} else {
		$sql = "UPDATE $data_source.cse_gmail 
		SET token = :token, 
		origin = :origin,
		token_date = '" . date("Y-m-d H:i:s") . "',
		refresh_token = :refresh_token,
		email_type = :email_type,
		refresh_token_at = '" . date("Y-m-d H:i:s") . "'
		WHERE user_id = :user_id AND user_email_id = :user_email_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("token", $gtoken);
		$stmt->bindParam("origin", $origin);
		$stmt->bindParam("refresh_token", $refresh_token);
		$stmt->bindParam("email_type",$email_type);
		$stmt->bindParam("user_email_id",$user_email_id);
		$stmt->execute();
		//die($stmt->execute());
	}
	//die($user_id);
	echo "done at " . date("Y-m-d H:i");
	exit();
}
function refreshToken() {
	session_write_close();
	$gtoken = "";
	$user_id =  $_SESSION["user_plain_id"];
	try {	
		//refresh the token
		$url = "https://www.ikase.xyz/ikase/gmail/ui/refresh_token.php?logout=";
		$fields = array('case_id'=>-1, 'customer_id'=>$customer_id, 'user_id'=>$user_id);;
		//die(print_r($fields));
		$result = post_curl($url, $fields);
		echo $result;
	} catch(PDOException $e) {
		$error = array("error1"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
	exit();	
}
function gmailToken() {
	//die("here");
	session_write_close();
	$gtoken = "";
	$user_id =  $_SESSION["user_plain_id"];
	try {
		$sql = "SELECT * 
		FROM cse_gmail
		WHERE user_id = :user_id
		AND DATE_ADD(token_date, INTERVAL 1 HOUR) > '".date("Y-m-d H:i:s")."'";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		//die($sql);
		$stmt->execute();
		$tok = $stmt->fetchObject();
		// die(var_dump($tok));
		
		if (is_object($tok)) {
			$gtoken = $tok->token;
		}
	} catch(PDOException $e) {
		$error = array("error1"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
	
	//die($gtoken." Token");
	echo json_encode(array("access_token"=>$gtoken));
}
function gmailClearToken() {
	
	$fp = fopen('gmail.txt', 'w');
	fwrite($fp, '\r\n');
	fwrite($fp, json_encode($_REQUEST));
	fwrite($fp, '\r\n');
	fwrite($fp, json_encode($_SERVER));
	fclose($fp);
	
	
	session_write_close();
	$user_id = passed_var("user_id", "post");
	$customer_id = passed_var("customer_id", "post");
	$origin = passed_var("origin", "post");
	
	//die(print_r($_POST));
	session_write_close();
	$gtoken = "";
	
	try {
		$sql = "UPDATE cse_gmail
		SET token = '',
		origin = :origin
		WHERE user_id = :user_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("origin", $origin);
		$stmt->execute();
	} catch(PDOException $e) {
		$error = array("error1"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
	
	$url = "https://www.ikase.xyz/ikase/gmail/ui/index.php?logout=";
	$fields = array('customer_id'=>$customer_id, 'user_id'=>$user_id);;
//	die(print_r($fields));
	
	$result = post_curl($url, $fields);
	
	//echo $result;
	
	die(json_encode(array("success"=>true)));
}
function readEmail($id) {
	session_write_close();
	
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	
	if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
		$error = array("error"=> array("text"=>"missing email info"));
        die( json_encode($error));
	}
	//receive it
	$pwd = decryptAES($email_info->email_pwd);
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	
	$obj= new receiveMail($email_info->email_name, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json", $email_info->certificate);
	
	$obj->connect();
	$tot=$obj->getTotalMails();
	$body = "";
	$i = $id;
	$head=$obj->getHeaders($i);
	$body = $obj->getBody($i);
	
	$htmlpos = strpos($body, "<html");
	if ($htmlpos===false) {
		$body = str_replace("\r\n", "<br />", $body);
	}
	die($body);
}
function previewAttachment() {
	session_write_close();
	//die(print_r($_POST));
	$id = passed_var("id", "post");
	$name = passed_var("name", "post");
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
		$error = array("error"=> array("text"=>"missing email info"));
        die( json_encode($error));
	}
	//receive it
	$pwd = decryptAES($email_info->email_pwd);
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	$obj= new receiveMail($email_info->email_name, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json", $email_info->certificate);
	
	$obj->connect();
	$tot=$obj->getTotalMails();
	
	$arrAcceptable = array("jpg", "png", "pdf", "doc", "docx", "eml");
	//this will download the attachment
	$str=$obj->GetAttach($id,UPLOADS_PATH. $_SESSION['user_customer_id'] . "\\webmail_previews\\", $arrAcceptable, true, $name);
		
	$success = array("success"=> array("text"=>$id));
    die( json_encode($success));
}
function deleteEmail() {
	session_write_close();
	$id = passed_var("id", "post");
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
		$error = array("error"=> array("text"=>"missing email info"));
        die( json_encode($error));
	}
	//receive it
	$pwd = decryptAES($email_info->email_pwd);
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	$obj= new receiveMail($email_info->email_name, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json", $email_info->certificate);
	
	$obj->connect();
	$tot=$obj->getTotalMails();
	$body = "";
	
	$arrIDs = explode(", ", $id);
	foreach ($arrIDs as $id) {
		$i = $id;
		$head=$obj->getHeaders($i, 0);
		$body = $obj->getBody($i);
		
		$from = $head["from"];
		$to = $head["to"];
		$subject = $head["subject"];
		$webmail_message_id = $head["message_id"];
		$message_date = $head["date"];
		$the_uuid = uniqid("TD", false);
		die('here-1');
		$sql = "INSERT INTO cse_webmail (`webmail_uuid`, `message_id`, `user_id`, `message_date`, `from`, `subject`, `message`, `customer_id`, `deleted`)
		VALUES ('" . $the_uuid . "', '" . $webmail_message_id . "', '" . $_SESSION['user_plain_id'] . "','" . date("Y-m-d H:i:s", strtotime($message_date)) . "','" . $from . "','" . addslashes($subject) . "','" . addslashes($body) . "', '" . $_SESSION['user_customer_id'] . "', 'Y')";
		// die($sql);
		try {
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		$obj->deleteMails($i); 
	}
	$obj->close_mailbox();   //Close Mail Box
	$success = array("success"=> array("text"=>implode(", ", $arrIDs)));
    die( json_encode($success));
}
function decode_qprint($str) {
    $str = preg_replace("/\=([A-F][A-F0-9])/","%$1",$str);
    $str = urldecode($str);
    $str = utf8_encode($str);
    echo $str;
}
function verifyEmail($direction) {
	session_write_close();
	
	if ($direction=="incoming") {
		testEmail();
	}
	if ($direction=="outgoing") {
		testOutgoing();
	}
}
function testOutgoing() {
	session_write_close();
	
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	
	//can we use smtp
	$outgoing_server = $email_info->outgoing_server;
	$outgoing_port = $email_info->outgoing_port;
	$encrypted_connection = $email_info->encrypted_connection;
	$outgoing_email = $email_info->email_address;
	$pwd = decryptAES($email_info->email_pwd);
	
	//we have stuff to blast
	$from_name = $email_info->email_name; //$pwd, 
	$from_address = $email_info->email_address;
	$text_message = "Test from system";
	$subject = "iKase Test";
	//$recipients = "nick@kustomweb.com";
	$recipients = "tsmith@glauberberenson.com";
	
	$attachments = "";
	//$tos = "nick@kustomweb.com";
	$tos = "tsmith@glauberberenson.com";
	$ccs = "";
	$bccs = "";
	if ($outgoing_server=="") {
		$error = array("error"=>"No Outgoing");
		die(json_encode($error));
	}
	if ($outgoing_server!="") {
	//if ($customer_id==1033) {
		if($tos!=""){
			//die(print_r($buffer));
			date_default_timezone_set('America/Los_Angeles');
			require '../PHPMailer/PHPMailerAutoload.php';
			
			//Create a new PHPMailer instance
			$mail = new PHPMailer;
			
			
			//smtp
			//Tell PHPMailer to use SMTP
			$mail->isSMTP();
			//Enable SMTP debugging
			// 0 = off (for production use)
			// 1 = client messages
			// 2 = client and server messages
			$mail->SMTPDebug = 0;
			//Ask for HTML-friendly debug output
			//error_log
			$mail->Debugoutput = 'html';
			//Set the hostname of the mail server
			$mail->Host = $outgoing_server;
			//Set the SMTP port number - likely to be 25, 465 or 587
			$mail->Port = $outgoing_port;
			//Whether to use SMTP authentication
			$mail->SMTPAuth = true;
			$mail->SMTPAutoTLS = false;
			//die("enc:" . $encrypted_connection);
			if ($encrypted_connection=="SSL") {
				$mail->set('SMTPSecure', 'ssl');
			}
			if ($encrypted_connection=="None") {
				$mail->set('SMTPSecure', 'none');
			}
			//Username to use for SMTP authentication
			$mail->Username = $outgoing_email;
			//Password to use for SMTP authentication
			$mail->Password = $pwd;

			$mail->setFrom($outgoing_email, $from_name);
			//Set an alternative reply-to address
			$mail->addReplyTo($from_address, $from_name);
			//Set who the message is to be sent to
			$tos = str_replace(",", ";", $tos);
			$arrTos = explode(";", $tos);
			foreach($arrTos as $to) {
				$mail->addAddress($to, $to);
			}
			//Set the subject line
			$mail->Subject = $subject;
			//Read an HTML message body from an external file, convert referenced images to embedded,
			//convert HTML into a basic plain-text alternative body
			$mail->msgHTML($text_message);
			//Replace the plain text body with one created manually
			$mail->AltBody = strip_tags($text_message);
			$arrAttachments = explode(",", $attachments);
			//die(print_r($arrAttachments));
			foreach ($arrAttachments as $attachment_file) {
				//Attach an image file
				if (strpos($attachment_file, "uploads")==0) {
					$attachment_file = "../" . $attachment_file;
				}
				if (file_exists($attachment_file)) {
					$mail->addAttachment($attachment_file);
				}
			}
			//die(print_r($mail));	
			//send the message, check for errors
			if (!$mail->send()) {
				$blnSent = false;
				$error = array("error"=>"Mailer Error: " . $mail->ErrorInfo);
				die(json_encode($error));
							
			} else {
				$blnSent = true;
				die(json_encode(array("success"=>"Message sent!")));
				//die(print_r($mail));				
			}
			//die("\r\nbuffer done");
		}
	}
}
function testEmail() {
	session_write_close();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	//die(print_r($email_info));
	if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
		$error = array("error"=> array("text"=>"missing email info"));
        die( json_encode($error));
	}
	
	//let's do this
	$pwd = decryptAES($email_info->email_pwd);
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	/*
	if ($_SERVER['REMOTE_ADDR']!='71.119.40.148') {
		die("not ready");
	}
	*/
	$obj= new receiveMail($email_info->email_name, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json", $email_info->certificate);
	$obj->connect();
	$tot=$obj->getTotalMails();
	
	$obj->close_mailbox();   //Close Mail Box
	
	$result_json = json_encode(array("success"=>$tot));
	die($result_json);
}
function getEmailInbox() {
	session_write_close();
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	if(is_object($email_info)) {
		if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
			$error = array("error"=> array("text"=>"missing email info"));
			die( json_encode($error));
		}
	} else {
		$error = array("error"=> array("text"=>"no email info"));
		die( json_encode($error));
	}
	//let's do this
	$pwd = decryptAES($email_info->email_pwd);
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	/*
	if ($_SERVER['REMOTE_ADDR']!='71.119.40.148') {
		die("not ready");
	}
	*/
	$obj= new receiveMail($email_info->email_name, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json", $email_info->certificate);
	$obj->connect();
	$tot = $obj->getTotalMails();
	//die("found:" . $tot);
	//die(print_r($obj));
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$initial_time = round(($finish_time - $header_start_time), 4);
	
	//echo $initial_time . " to open and count\r\n";
	
	$start_date  = mktime(0, 0, 0, date("m")  , date("d")-4, date("Y"));
	
	//echo "found: " . $tot . " messages\r\n";
	//die();
	$arrTheResult = array();
	$arrAcceptable = array("jpg", "png", "pdf", "doc", "docx", "eml", "xls", "xlsx", "csv");
	$intCounter = 0;
	//$maxCounter = 4056;
	$maxCounter = 40;
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	if ($tot > $maxCounter) {
		$blnMaxCheck = true;
		$min_for = $tot - $maxCounter;
	} else {
		$blnMaxCheck = false;
		$min_for = 0;
	}
	
	for($i=$tot;$i>$min_for;$i--) {
	//for($i=$maxCounter;$i>($maxCounter-1);$i--) {
		// Get Header Info Return Array Of Headers **Array Keys are (subject,to,toOth,toNameOth,from,fromName)
		$head=$obj->getHeaders($i, 0);
		
		if (count($head)==0) {
			//something is wrong, skip it
			//echo "skipped " . $i . "\r\n";
			//die();
			continue;
		}
		//die(print_r($head));
		//if ($head["size"] < 500000) {
		if ($head["size"] < 3000) {
			continue;
		}
		$webmail_message_id = $head["message_id"];

		//maybe we have it already
		try {
			//first check if this $webmail_message_id is already in use
			$sql = "SELECT COUNT(`webmail_uuid`) web_count
			FROM cse_webmail web
			WHERE `message_id` = :webmail_message_id
			AND customer_id = :customer_id";
			//echo $sql . "\r\n";
			//die();
			$db = getConnection();
			$stmt = $db->prepare($sql); 
			$stmt->bindParam("webmail_message_id", $webmail_message_id);
			$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
			$stmt->execute();
			$webmail_result = $stmt->fetchObject();
			$blnAlreadyIn = true;
			
			//die("ss:" . $webmail_result->web_count);
			//if (!is_object($webmail_result)) {
			if ($webmail_result->web_count == 0) {
				$blnAlreadyIn = false;
			} else {
				//already in
				/*
				if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
					//die(print_r($head));
					
				}
				*/
				//echo $i . " is already in\r\n";
				//	die();
				continue;
			}
			//die("stop");
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			echo json_encode(array("error"=>array("i"=>$i, "sql"=>$sql, "text"=>$e->getMessage())));
			die();
		}
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$attach_time = $time;
		
		$arrAcceptable = array("jpg", "png", "pdf", "doc", "docx", "eml");
		//this will download the attachment
		$webmail_dir = UPLOADS_PATH. $_SESSION['user_customer_id'] . "\\webmail_previews\\";
		//$webmail_dir .= $head["message_id"] . DC;
		$str = $obj->GetAttach($i,$webmail_dir, $arrAcceptable, true, "");
		
		$body = cleanWord($obj->getBody($i));
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$attached_time = $time;
		$total_attach_time = round(($attached_time - $attach_time), 4);
	
		$attach = 0;
		$attachFiles = array();
		
		if ($str!="") {
			//echo $i . " -- " . $str . "\r\n";
			//die();
			$arrAttach = explode(",",$str);
			//die(print_r($arrAttach));
			//eliminate anything that is not a jpg, doc, docx, or pdf
			foreach($arrAttach as $attach_index=>$attachment) {
				//find the extension
				$arrFilename = explode(".", $attachment);
				$extension = $arrFilename[count($arrFilename) - 1];
				$extension = strtolower($extension);
				if (!in_array($extension, $arrAcceptable)) {
					unset($arrAttach[$attach_index]);
				}
				//$attachFiles[] = "<a id='webmailattach_" . $i . "' class='email_attach_link white_text' style='cursor:pointer'>" . $attachment . "</a>";
				$attachFiles[] = $attachment;
			}
			$attach = count($arrAttach);
		}
		
		$head['subject'] = cleanWord($head['subject']);
		
		$arrTheResult[] = array("id"=>$head['id'], "size"=>$head["size"], "total_attach_time"=>$total_attach_time, "message_id"=>$head['message_id'], "subject"=>$head['subject'], "to"=>$head['to'], "from"=>$head['from'], "date"=>$head['date'], "attachments"=>$attach, "attach_files"=>implode("; ", $attachFiles));
		
		//insert into database
		$webmail_message_id = $head['message_id'];
		$from = $head['from'];
		$to = $head['to'];
		$subject = $head['subject'];
		$message_date = $head["date"];
		$the_uuid = uniqid("TD", false);
				
		try {
			$db = getConnection();
			die('here-2');
			//we already checked, this is a brand new insert			
			$sql = "INSERT INTO cse_webmail (`webmail_uuid`, `message_id`, `user_id`, `message_date`, `to`, `from`, `subject`, `message`, `customer_id`, `attachments`, `deleted`)
			VALUES ('" . $the_uuid . "', '" . $webmail_message_id . "', '" . $_SESSION['user_plain_id'] . "',
			'" . date("Y-m-d H:i:s", strtotime($message_date)) . "',
			'" . str_replace("'", "\'", $to) . "',
			'" . str_replace("'", "\'", $to) . "',
			'" . addslashes($subject) . "','" . addslashes($body) . "', '" . $_SESSION['user_customer_id'] . "', '" . implode("|", $attachFiles) . "', 'N')";
			//echo "i:" . $i . "\r\n" . $sql . "\r\n\r\n";
			
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			//echo json_encode(array("error"=>array("i"=>$i, "sql"=>$sql, "text"=>$e->getMessage())));
			echo '{"error2":{"sql":'. $sql .',"text":'. $e->getMessage() .'}}'; 
			
			die();
		}
		
		$intCounter++;
		if ($blnMaxCheck) {
			if(strtotime($head["date"]) < $start_date) {
				echo date("m/d/Y H:i:s", strtotime($head["date"])) . "\r\n";
				$intCounter = $maxCounter + 1;
			}
		}
		//break;
		
		if ($intCounter > $maxCounter) {
			die("too many " );
			break;
			//
		}	
	}
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	//print_r($arrTheResult);
	$obj->close_mailbox();   //Close Mail Box
	
	//echo $intCounter . " -- cutoff_date:" . date("m/d/y", $start_date) . ", initial_time" . $initial_time;
	//print_r($arrTheResult);
	if (count($arrTheResult)==0) {
		$arrTheResult[] = array("success"=>"no data");
	}
	$result_json = json_encode($arrTheResult);
	die($result_json);
	
	$arrResult = array("messages"=>$intCounter, "cutoff_date"=>date("m/d/y", $start_date), "total_time"=>$total_time, "result"=>$arrTheResult);
	//print_r($arrResult);
	die(json_encode($arrResult));
}
function goGetMail() {
	session_write_close();
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	if(is_object($email_info)) {
		if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
			$error = array("error"=> array("text"=>"missing email info"));
			die( json_encode($error));
		}
	} else {
		$error = array("error"=> array("text"=>"no email info"));
		die( json_encode($error));
	}

	//$error = array("error"=> array("text"=>"test error"));
	//die( json_encode($error));
	
	//let's do this
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	
	//echo $email_info->email_pwd . "\r\n";
	
	$email_info->ssl = $ssl;
	$email_info->method = $method;
	
	//encrypt on the way out
	$authorize_key = "ikase.org";
	
	$authorize_key = encryptAES($authorize_key);
	$credentials = json_encode($email_info);
	//die($authorize_key);
	//now submit to 173
	// NISHIT REPLACE IP FROM 173.58.194.150 TO ikase.xyz
	$url = "http://ikase.xyz/ikase/limapi/email.php/checkmail";
	//$url = "http://173.58.194.150/ikase/limapi/email.php/checkmail";
	//$url = "https://www.ikase.xyz/ikase/limapi/email.php/checkmail";
	
	$fields = array("customer_id"=>urlencode($_SESSION['user_customer_id']), "user_id"=>urlencode($_SESSION['user_plain_id']), "user_name"=>urlencode($_SESSION['user_name']), "authorize_key"=>urlencode($authorize_key), "credentials"=>urlencode($credentials), "through"=>urlencode($email_info->email_pwd));
	//die(print_r($fields));
	$fields_string = "";
	foreach($fields as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
	$timeout = 5;
	//die($fields_string);
	
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
	die($result);
}
function pinglimEmailInbox() {
	session_write_close();
	$email_infos = getEmailInfo($_SESSION['user_plain_id']);
	foreach($email_infos as $email_info){
		if(is_object($email_info)) {
			if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
				$error = array("error"=> array("text"=>"missing email info - ".$email_info->email_address));
				die( json_encode($error));
			}
		} else {
			$error = array("error"=> array("text"=>"no email info"));
			die( json_encode($error));
		}

		//$error = array("error"=> array("text"=>"test error"));
		//die( json_encode($error));
		
		//let's do this
		$ssl = ($email_info->ssl_required == "Y");
		$method = strtolower($email_info->email_method);
		
		//echo $email_info->email_pwd . "\r\n";
		
		$email_info->ssl = $ssl;
		$email_info->method = $method;
		
		//encrypt on the way out
		$authorize_key = "ikase.org";
		
		$authorize_key = encryptAES($authorize_key);
		$credentials = json_encode($email_info);
		// die($authorize_key);
		//now submit to 173
		// NISHIT REPLACE IP FROM 173.58.194.150 TO ikase.xyz
		$url = "http://ikase.xyz/ikase/limapi/email.php/pingmail";
		//$url = "http://173.58.194.150/ikase/limapi/email.php/pingmail";
		
		$fields = array("customer_id"=>urlencode($_SESSION['user_customer_id']), "user_id"=>urlencode($_SESSION['user_plain_id']), "user_name"=>urlencode($_SESSION['user_name']), "authorize_key"=>urlencode($authorize_key), "credentials"=>urlencode($credentials), "through"=>urlencode($email_info->email_pwd));
		//die(print_r($fields));
		$fields_string = "";
		foreach($fields as $key=>$value) { 
			$fields_string .= $key.'='.$value.'&'; 
		}
		rtrim($fields_string, '&');
		$timeout = 5;
		//die($fields_string);
		
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
		echo $result;
		//die($result);
	}
	die("PingEmail");
}
function processlimEmailInbox() {
	session_write_close();
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	if(is_object($email_info)) {
		if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
			$error = array("error"=> array("text"=>"missing email info"));
			die( json_encode($error));
		}
	} else {
		$error = array("error"=> array("text"=>"no email info"));
		die( json_encode($error));
	}

	//let's do this
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	
	//echo $email_info->email_pwd . "\r\n";
	
	$email_info->ssl = $ssl;
	$email_info->method = $method;
	
	//encrypt on the way out
	$authorize_key = "ikase.org";
	
	$authorize_key = encryptAES($authorize_key);
	$credentials = json_encode($email_info);
	//die($authorize_key);
	//now submit to 173
	// NISHIT REPLACE IP FROM 173.58.194.150 TO ikase.xyz
	$url = "http://ikase.xyz/ikase/limapi/email.php/processmail";
	//$url = "http://173.58.194.150/ikase/limapi/email.php/processmail";
	
	$fields = array("customer_id"=>urlencode($_SESSION['user_customer_id']), "user_id"=>urlencode($_SESSION['user_plain_id']), "user_name"=>urlencode($_SESSION['user_name']), "authorize_key"=>urlencode($authorize_key), "credentials"=>urlencode($credentials), "through"=>urlencode($email_info->email_pwd));
	//die(print_r($fields));
	$fields_string = "";
	foreach($fields as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
	$timeout = 5;
	//die($fields_string);
	
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
	die($result);
}
function limEmailInbox() {
	session_write_close();
	require_once '../spam/spamfilter.php';
	$filter = new SpamFilter();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	//die(print_r($email_info));
	if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
		$error = array("error"=> array("text"=>"missing email info"));
        die( json_encode($error));
	}
	
	//let's do this
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	
	//echo $email_info->email_pwd . "\r\n";
	
	$email_info->ssl = $ssl;
	$email_info->method = $method;
	
	//encrypt on the way out
	$authorize_key = "ikase.org";
	$authorize_key = encryptAES($authorize_key);
	//die($authorize_key);
	$credentials = json_encode($email_info);
	//die(json_encode(array("success"=>true)));
	
	//now submit to 173
	// NISHIT REPLACE IP FROM 173.58.194.150 TO ikase.xyz
	$url = "http://ikase.xyz/ikase/limapi/email.php/getmail";
	//$url = "http://173.58.194.150/ikase/limapi/email.php/getmail";
	
	$fields = array("customer_id"=>urlencode($_SESSION['user_customer_id']), "user_id"=>urlencode($_SESSION['user_plain_id']), "user_name"=>urlencode($_SESSION['user_name']), "authorize_key"=>urlencode($authorize_key), "credentials"=>urlencode($credentials), "through"=>urlencode($email_info->email_pwd));
	//die(print_r($fields));
	$fields_string = "";
	foreach($fields as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
	$timeout = 5;
	//die($fields_string);
	
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
	die($result);
	
	//	STOP HERE
	//
	//no need for below anymore, the script on xyz transfers the emails to ikase.org via messages/add_email
	//
	//
	
	$messages = json_decode($result);
	//die(print_r($messages));
	if (isset($messages->error)) {
		die($result);
	}
	if(count($messages)==0) {
		echo '[""]';
		die();
	}
	foreach($messages as $message) {
		$sql = "SELECT DISTINCT case_id  
		FROM cse_case ccase
		INNER JOIN cse_case_notes cnotes
		ON ccase.case_uuid = cnotes.case_uuid
		WHERE attribute = '" . $message->message_id . "'
		AND ccase.customer_id = '" . $_SESSION['user_customer_id'] . "'";
		//die($sql);
		$case_id = "";
		try {	
			$stmt = DB::run($sql);
			$kase = $stmt->fetchObject();
			if (is_object($kase)) {
				$case_id = $kase->case_id;
			}
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		$message->case_id = $case_id;
	}
	die(json_encode($messages));
}
function transferAttach() {
	session_write_close();
	//die(print_r($_POST));
	if (!isset($_POST["authorize_key"])) {
		die();
	} else {
		$authorize_key = passed_var("authorize_key", "post");
	}
	if (trim($authorize_key)!="ikase.org") {
		die("autho");
	}
	$customer_id = passed_var("customer_id", "post");
	$user_id = passed_var("user_id", "post");
	$server_file = passed_var("filename", "post");
	$server_file = trim($server_file);
	$path = UPLOADS_PATH. $customer_id;
	if (!is_dir($path)) {
		mkdir($path, 0755, true);
	}
	$path .= "\\webmail_previews\\";
	if (!is_dir($path)) {
		mkdir($path, 0755, true);
	}
	$path .= $user_id . DC;
	if (!is_dir($path)) {
		mkdir($path, 0755, true);
	}
	$local_file = $path . $server_file;
	
	$url = "https://www.ikase.xyz/ikase/gmail/ui/get_document.php";
	$fields = array("filename"=>$server_file, 'case_id'=>-1, 'customer_id'=>$customer_id, 'user_id'=>$user_id);;
	//die(print_r($fields));
	$result = post_curl($url, $fields);
	$json = json_decode($result);
	/*
	//die($local_file);
	
	$ftp_server = "173.58.194.150";
	$ftp_username = "nick";
	$ftp_pwd = "access9090";
	$conn_id = ftp_connect($ftp_server); 
		
	// login with username and password 
	$login_result = ftp_login($conn_id, $ftp_username, $ftp_pwd); 
	if (!$login_result) {
		die('Login Failed');
	}
	// check connection
	if ((!$conn_id) || (!$login_result)) {
		die("FTP connection has failed !");
	}
	//die("connected");
	// turn passive mode on
	ftp_pasv($conn_id, true);
	ftp_chdir($conn_id, "ikase");
	$arrFiles = ftp_rawlist($conn_id, ".");
	die(print_r($arrFiles));
	
	ftp_chdir($conn_id, "gmail");
	ftp_chdir($conn_id, "ui");
	ftp_chdir($conn_id, "attachments");
	ftp_chdir($conn_id, $customer_id);
	ftp_chdir($conn_id, $user_id);
	
	
	//die($server_file);
	// try to download $server_file and save to $local_file
	if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
		//echo "Successfully written to $local_file\n";
		echo json_encode(array("success"=>"true", "filename"=>$local_file));
	} else {
		echo json_encode(array("success"=>"false", "filename"=>$local_file));
	}
	*/
	
	//if pdf, make a thumbnail
	$path_parts = pathinfo($local_file);
	$extension = $path_parts['extension'];
	if ($extension=="pdf") {
		$thumbFile = str_replace(".pdf", ".jpg", $local_file);
		
		$image_magick = new imagick(); 
		$image_magick->setBackgroundColor("white");
		$image_magick->readImage($local_file . "[0]");
		$image_magick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
		$image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
	
		$image_magick->setResolution(300,300);
		$image_magick->thumbnailImage(800, 800, true);
		$image_magick->setImageFormat('jpg');
		
		$image_magick->writeImage($thumbFile);
	}
	echo json_encode(array("success"=>"true", "filename"=>$local_file));
}
function webEmailInbox() {
	session_write_close();
	require_once '../spam/spamfilter.php';
	$filter = new SpamFilter();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	//die(print_r($email_info));
	if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
		$error = array("error"=> array("text"=>"missing email info"));
        die( json_encode($error));
	}
	
	//let's do this
	$pwd = decryptAES($email_info->email_pwd);
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	/*
	if ($_SERVER['REMOTE_ADDR']!='71.119.40.148') {
		die("not ready");
	}
	*/
	$obj= new receiveMail($email_info->email_name, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json", $email_info->certificate);
	$obj->connect();
	$tot=$obj->getTotalMails();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$initial_time = round(($finish_time - $header_start_time), 4);
	
	//echo $initial_time . " to open and count\r\n";
	
	$start_date  = mktime(0, 0, 0, date("m")  , date("d")-4, date("Y"));
	
	//echo "found: " . $tot . " messages\r\n";
	//die();
	$arrTheResult = array();
	$arrAcceptable = array("jpg", "png", "pdf", "doc", "docx", "eml", "xls", "xlsx", "csv");
	$intCounter = 0;
	//$maxCounter = 4056;
	$maxCounter = 40;
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	if ($tot > $maxCounter) {
		$blnMaxCheck = true;
		$min_for = $tot - $maxCounter;
	} else {
		$blnMaxCheck = false;
		$min_for = 0;
	}
	$db = getConnection();
	
	for($i=$tot;$i>$min_for;$i--) {
	//for($i=$maxCounter;$i>($maxCounter-1);$i--) {
		// Get Header Info Return Array Of Headers **Array Keys are (subject,to,toOth,toNameOth,from,fromName)
		$head=$obj->getHeaders($i, 0);
		//die(print_r($head));
		if (count($head)==0) {
			//something is wrong, skip it
			//echo "skipped " . $i . "\r\n";
			//die();
			continue;
		}
		
		$body = $obj->getBody($i);
		$spam = "";
		/*
		$check_result = $filter->check_text($body);
		if ($check_result) {
			$spam = "SPAM (" . $check_result . ")";
		}
		*/
		//is it attached to a kase
		$sql = "SELECT DISTINCT case_id  
		FROM cse_case ccase
		INNER JOIN cse_case_notes cnotes
		ON ccase.case_uuid = cnotes.case_uuid
		WHERE attribute = '" . $head['message_id'] . "'
		AND ccase.customer_id = " . $_SESSION['user_customer_id'];
		
		$case_id = "";
		try {
			
			$stmt = DB::run($sql);
			$kase = $stmt->fetchObject();
			if (is_object($kase)) {
				$case_id = $kase->case_id;
			}
					
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$attach_time = $time;

			$str = "";
			$blnAttachmentFile = false;
			if ($head["size"] <  500000) {
				$str = $obj->GetAttach($i,UPLOADS_PATH. $_SESSION['user_customer_id'] . "\\webmail_previews\\", $arrAcceptable, false, "");
				$blnAttachmentFile = true;
			} else {
				//maybe we downloaded it already
				$sql = "SELECT `attachments` 
				FROM `cse_webmail` 
				WHERE `message_id` = '" . $head['message_id'] . "'";
				
				$stmt = DB::run($sql);
				$webmail = $stmt->fetchObject();
				if (is_object($webmail)) {
					$str = str_replace("|", ",", $webmail->attachments);
					$blnAttachmentFile = true;
				}
			}
			//$body = $obj->getBody($i);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$attached_time = $time;
		$total_attach_time = round(($attached_time - $attach_time), 4);
	
		$attach = 0;
		$attachFiles = array();
		
		if ($str!="") {
			//echo $i . " -- " . $str . "\r\n";
			//die();
			$arrAttach = explode(",",$str);
			//die(print_r($arrAttach));
			//eliminate anything that is not a jpg, doc, docx, or pdf
			foreach($arrAttach as $attach_index=>$attachment) {
				//find the extension
				$arrFilename = explode(".", $attachment);
				$extension = $arrFilename[count($arrFilename) - 1];
				$extension = strtolower($extension);
				if (!in_array($extension, $arrAcceptable)) {
					unset($arrAttach[$attach_index]);
				}
				$attachFiles[] = "<a id='webmailattach_" . $i . "' class='email_attach_link white_text' style='cursor:pointer'>" . $attachment . "</a>";
			}
			$attach = count($arrAttach);
		}
		
		if (!$blnAttachmentFile) {
			if ($head["size"] >  500000) {
				$attach = "large.file";				
				$attachFiles = array($attach);
			}
		}
		$head['subject'] = cleanWord($head['subject']);
		
		$arrTheResult[] = array("id"=>$head['id'], "size"=>$head["size"], "total_attach_time"=>$total_attach_time, "case_id"=>$case_id, "message_id"=>$head['message_id'], "subject"=>$head['subject'], "to"=>$head['to'], "from"=>$head['from'], "date"=>$head['date'], "attachments"=>$attach, "spam"=>$spam, "attach_files"=>implode("; ", $attachFiles));
		
		$intCounter++;
		if ($blnMaxCheck) {
			if(strtotime($head["date"]) < $start_date) {
				//echo date("m/d/Y H:i:s", strtotime($head["date"])) . "\r\n";
				$intCounter = $maxCounter + 1;
			}
		}
		
		if ($intCounter > $maxCounter) {
			break;
			//die("too many " );
		}
		
	}
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	//print_r($arrTheResult);
	$obj->close_mailbox();   //Close Mail Box
	
	//echo $intCounter . " -- cutoff_date:" . date("m/d/y", $start_date) . ", initial_time" . $initial_time;
	$result_json = json_encode($arrTheResult);
	die($result_json);
	
	$arrResult = array("messages"=>$intCounter, "cutoff_date"=>date("m/d/y", $start_date), "total_time"=>$total_time, "result"=>$arrTheResult);
	//print_r($arrResult);
	die(json_encode($arrResult));
}
function assignGmail() {
	session_write_close();
	
	//die(json_encode($_POST));
	//die(json_encode(array("post"=>$_POST, "email_id"=>$_POST["id"])));
	
	$case_id = passed_var("case_id", "post");
	$id = passed_var("id", "post");
	$table_attribute = passed_var("table_attribute", "post");
	$case_note = passed_var("case_note", "post");
	$case_attach = passed_var("case_attach", "post");
	$arrAttachments = explode("|", $case_attach);
	//die(print_r($arrAttachments));
	try {
		$kase = getKaseInfo($case_id);
		
		if (!is_object($kase)) {
			echo '{"error":{"text":"no kase"}}'; 
			die();
		}
		$case_uuid = $kase->uuid;
		$message = getMessageInfo($id);
		if (!is_object($message)) {
			echo '{"error":{"text":"no message"}}'; 
			die();
		}
		$message_uuid = $message->uuid;
		$email_contact = $message->from;
		$arrContact = explode("|", $email_contact);
		$arrLength = count($arrContact) - 1;
		for($int = $arrLength; $int >= 0; $int--) {
			$thecontact = $arrContact[$int];
			if ($thecontact=="") {
				unset($arrContact[$int]);
			}
		}
		$email_contact = implode("", $arrContact);
		
		$case_table_uuid = uniqid("CA", false);			
		$last_updated_date = date("Y-m-d H:i:s");
		$table_name = "message";
		
		//now we have to move attachments to docs
		$arrAttachedFiles = array();

		foreach($arrAttachments as $attachment) {
			if ($attachment=="") {
				continue;
			}
			$attachment = trim($attachment);
			//break up the filename
			$arrFilename = explode("/", $attachment);		
			
			if (count($arrFilename)==4) {
				$attach_customer_id = $arrFilename[1];
				$attach_user_id = $arrFilename[2];
				$attach_file = $arrFilename[3];
			} else {
				$attach_customer_id = $_SESSION["user_customer_id"];
				$attach_user_id = $_SESSION["user_plain_id"];
				$attach_file = $attachment;
			}
			//make sure it's in the sub folder
			$strpos = strpos($attachment, "attachments/");
			
			if ($strpos === false) {
				//die($strpos . " - " . $attachment);
				//element = "attachments/" + customer_id + "/" + login_user_id + "/" + element;
				$attachment = $attach_customer_id . "/webmail_previews/" . $attach_user_id . "/" . $attachment;
				
				$arrExt = explode(".", $attach_file);
				$extension = $arrExt[1];
				$thumb_file = str_replace(".pdf", ".jpg", $attach_file);
				if($extension=="jpg" || $extension=="png") {
					$thumb_file = $attach_file;
				}
								
				array_push($arrAttachedFiles, "<a href='https://www.ikase.org/uploads/" . $attachment . "' target='_blank' onmouseover='showImportedPreview(this, \"uploads/" . $attachment . "\", \"\", \"\", " . $attach_customer_id . ", \"activity_\")' onmouseout='hidePreview()'>" . $attach_file . "</a>");
				
			} else {
				array_push($arrAttachedFiles, "<a href='https://www.ikase.xyz/ikase/gmail/ui/" . $attachment . "' target='_blank' style='background:yellow;color:black'>" . $attach_file . "</a>");
			}
			
			//die(print_r($arrAttachedFiles));
			//final check
			if ($attach_customer_id==$_SESSION["user_customer_id"] && $attach_user_id==$_SESSION["user_plain_id"]) {
				/*
				//get document from xyz
				$url = "https://www.ikase.xyz/ikase/gmail/ui/get_document.php";
				$fields = array("filename"=>$attach_file, 'case_id'=>$case_id, 'customer_id'=>$_SESSION["user_customer_id"], 'user_id'=>$_SESSION["user_plain_id"]);;
				//die(print_r($fields));
				$result = post_curl($url, $fields);
				$json = json_decode($result);
				*/
				
				//we need to move the files from uploads/customer_id/webmail_previews/user_id to uploads/customer_id/case_id 				
				$uploadDir = "\\uploads\\" . $attach_customer_id;
				if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
					mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
				}
				$uploadDir = "\\uploads\\" . $attach_customer_id . DC . $case_id;
				if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
					mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
				}
				$uploadDir = "\\uploads\\" . $attach_customer_id . DC . $case_id . "\\medium";
				if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
					mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
				}
				$uploadDir = "\\uploads\\" . $attach_customer_id . DC . $case_id . "\\thumbnail";
				if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
					mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
				}
				$dest = "../uploads/" . $attach_customer_id . "/" . $case_id . "/" . $attach_file;
				
				//move thumbnails if any
				$arrExt = explode(".", $attach_file);
				$extension = $arrExt[1];
				//die($attachment .  "<br />" . $dest . "<br />" . $extension);
				$thumbnail = "";
				$thumb_dest = "";
				if($extension=="pdf") {
					$thumbnail = str_replace(".pdf", ".jpg", $attachment);
					$thumb_dest = "../uploads/" . $attach_customer_id . "/" . $case_id . "/medium/" . str_replace(".pdf", ".jpg", $attach_file);
				}
				if($extension=="jpg" || $extension=="png") {
					$thumbnail = $attachment;
					$thumb_dest = "../uploads/" . $attach_customer_id . "/" . $case_id . "/medium/" . $attach_file;
				}
				if ($thumbnail!="") {
					$thumbnail = "../" . $thumbnail;
				}
				$thumbnail_folder = "";
				//die($attachment .  "<br />" . $dest . "<br />" . $thumbnail .  "<br />" . $thumb_dest);
				if (file_exists($thumbnail) && $thumb_dest!="") {
					copy($thumbnail, $thumb_dest);
					$thumb_dest = str_replace("medium/", "thumbnail/", $thumb_dest);
					copy($thumbnail, $thumb_dest);
					$thumbnail_folder = $case_id . "/medium";
				}
				$attachment = "../" . $attachment;
				if (file_exists($attachment) && $dest!="") {
					copy($attachment, $dest);
				}
				
				//die($thumbnail_folder);
				//die($result);
				//if (is_object($json)) {
					//if ($json->success=="true") {
						//echo "attach " . $attach_file . "<br />";
						//attachment is a document
						$document_uuid = uniqid("KS");
						$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, thumbnail_folder, type, verified, customer_id) 
					VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :thumbnail_folder, :type, :verified, :customer_id)";
						
						$description = "email attachment";
						$description_html = "email attachment";
						$type = "email attachment";
						$document_extension = explode(".", $attach_file);
						$document_extension = $document_extension[count($document_extension) - 1];
						$verified = "Y";
						
						$db = getConnection();
						
						$stmt = $db->prepare($sql);  
						$stmt->bindParam("document_uuid", $document_uuid);
						$stmt->bindParam("parent_document_uuid", $document_uuid);
						$stmt->bindParam("document_name", $attach_file);
						$stmt->bindParam("document_date", $message->dateandtime);
						$stmt->bindParam("document_filename", $attach_file);
						$stmt->bindParam("document_extension", $document_extension);
						$stmt->bindParam("description", $description);
						$stmt->bindParam("description_html", $description_html);
						$stmt->bindParam("thumbnail_folder", $thumbnail_folder);
						$stmt->bindParam("type", $type);
						$stmt->bindParam("verified", $verified);
						$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
						$stmt->execute();
						$new_id = $db->lastInsertId();
						
						$case_document_uuid = uniqid("EM", false);		
						$sql = "INSERT INTO cse_case_document (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
						VALUES ('" . $case_document_uuid  ."', '" . $case_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
						
						$stmt = DB::run($sql);
						
						trackDocument("insert", $new_id, "", false);
					//}
				//}
			}
		}
		//die("nnn");
		if (count($arrAttachedFiles) > 0) {
			//activity for all uploaded documents
			// print_r($arrAttachedFiles);
			// die("here");
			$operation = "assigned";
			$activity = "Document(s) assigned to case";
			$activity .= "\r\n";
			$activity .= implode(", ", $arrAttachedFiles);
			$activity_category = "Documents";
			recordActivity($operation, $activity, $case_uuid, $_POST["id"], $activity_category);
		}
		
		$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $message_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		//echo $sql . "\r\n";	
		$stmt = DB::run($sql);
		
		if ($case_note!="") {
			$case_note = "Note:&nbsp;" . $case_note . "<br /><br />";
		}
		$sql = "INSERT INTO `cse_notes` 
		(`notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `customer_id`)
		SELECT `message_uuid`, `message_type`, `subject`, 
		CONCAT('" . addslashes($case_note) . "', 'Date:', '" . date("m/d/Y g:iA", strtotime($message->dateandtime)) . "', '<br>', 'From:', `from`, '<br>', 'To:', `message_to`, '<br>', 'Subject:', `subject`, '<br>', `message`) message,
		`subject`, `attachments`, `from`, 'STANDARD', '". date("Y-m-d H:i:s") . "',  '" . $_SESSION['user_customer_id'] . "'
		FROM cse_message 
		WHERE message_uuid = '" . $message_uuid . "'";
		//`dateandtime`
		
		$db = getConnection();
		//echo $sql . "\r\n";	
		$stmt = DB::run($sql);
		
		//get the new id
		$sql = "SELECT notes_id 
		FROM cse_notes 
		WHERE notes_uuid = '" . $message_uuid . "'
		ORDER BY notes_id DESC
		LIMIT 0, 1";
		
		$stmt = DB::run($sql);
		$new_note = $stmt->fetchObject();
		
		
		//some clean up
		$sql = "UPDATE cse_notes
		SET entered_by = REPLACE(`entered_by`, '|', ''),
		`note` = REPLACE(`note`, '|', '')
		WHERE notes_id = :notes_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("notes_id", $new_note->notes_id);
		$stmt->execute(); 
		
		$table_name = "notes";
		$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $message_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		//echo $sql . "\r\n";
		
		$stmt = DB::run($sql);
		
		//activity		
		$activity_uuid = uniqid("KS", false);
		$activity_category = "email";
		$billing_time = 0;
		if (isset($_POST["billing_time"])) {
			$billing_time = passed_var("billing_time", "post");
			if (!is_numeric($billing_time)) {
				$billing_time = 0;
			}
		}
		
		$activity = $case_note . "Date:" . date("m/d/Y g:iA", strtotime($message->dateandtime)) . "<br />From:" . $message->from . "<br />To:" . $message->message_to . 
			"<br />Subject:" . $message->subject . "<br /><br />" . $message->message;
		$operation = "email assign";
		recordActivity($operation, $activity, $case_uuid, $new_note->notes_id, $activity_category, $billing_time);
		
		//now attach case_note
		/*
		if ($case_note!="") {
			//note
			$arrFields = array();
			$arrSet = array();
			
			$arrFields[] = "`note`";
			$arrSet[] = "'" . addslashes($case_note) . "'";
			$arrFields[] = "`title`";
			$arrSet[] = "'Webmail Assigned: " . addslashes($message->subject) . "'";
			$arrFields[] = "`subject`";
			$arrSet[] = "'" . addslashes($message->subject) . "'";
			$arrFields[] = "`attachments`";
			$arrSet[] = "''";
			
			$notes_uuid = uniqid("CN", false);
			//combine 
			$sql_note = "INSERT INTO `cse_notes` (`customer_id`, `entered_by`, `notes_uuid`, " . implode(",", $arrFields) . ") 
					VALUES('" . $_SESSION['user_customer_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $notes_uuid . "', " . implode(",", $arrSet) . ")";
			//echo $sql_note . "\r\n";	
			
			DB::run($sql_note);
	$new_id = DB::lastInsertId();
			
			//activity		
			$activity_uuid = uniqid("KS", false);
			$activity_category = "notes";
			$billing_time = 0;
			$activity = addslashes($case_note);
			$operation = "insert";
			recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category, $billing_time);
			
			$case_table_uuid = uniqid("CA", false);			
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the note to the case 
			$sql_note = "INSERT INTO cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $notes_uuid . "', 'webmail_note', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			//echo $sql_note . "\r\n";
			
			$stmt = DB::run($sql_note);
		}
		*/
		
		//the email is no longer pending
		$sql = "UPDATE cse_message mes
		SET mes.`status` = ''
		WHERE 1
		AND mes.message_id = :id
		AND mes.customer_id = '" . $_SESSION["user_customer_id"] . "'";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		$message_prefix = "RE: " . $kase->name . " // ID " . $kase->id . "
		
		";
		
		//the email needs indicator of assign, for replies
		$sql = "UPDATE cse_message mes
		SET mes.`message` = CONCAT('" . addslashes($message_prefix) . "', mes.`message`)
		WHERE 1
		AND mes.message_id = :id
		AND mes.customer_id = '" . $_SESSION["user_customer_id"] . "'
		AND INSTR(mes.`message`, '" . addslashes($message_prefix) . "') = 0";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		//is it already in the database
		$sql = "SELECT COUNT(contact_id) contact_count
		FROM cse_contact
		WHERE `email` = '" . $email_contact . "'
		AND user_uuid = '" . $_SESSION["user_id"] . "'
		AND customer_id = " . $_SESSION["user_customer_id"];
		
		$stmt = DB::run($sql);
		$contact = $stmt->fetchObject();
		
		//sender is part of contacts
		$contact_uuid = uniqid("EM", false);
		//insert emails in contacts for this user
		$sql = "INSERT INTO `cse_contact`
		(`contact_uuid`, `user_uuid`, `email`, `customer_id`)
		SELECT '" . $contact_uuid . "', '" . $_SESSION["user_id"] . "', '" . $email_contact . "', '" . $_SESSION["user_customer_id"] . "'
		FROM dual
		WHERE NOT EXISTS (
				SELECT * 
				FROM `cse_contact` 
				WHERE `email` = '" . $email_contact . "'
				AND `user_uuid` = '" . $_SESSION["user_id"] . "'
				AND customer_id = '" . $_SESSION["user_customer_id"] . "'
			)";
		//die($sql);
		DB::run($sql);
		
		$contact_id = -1;
		if (!is_object($contact)) {
			$contact_id = DB::lastInsertId();
		}
		
		if ($contact_id > 0) {
			trackContact("insert", $contact_id);
			
			$message_contact_uuid = uniqid("MC", false);
			$message_id = $id;
			$attribute = "from";
			if ($message->message_type=="email") {
				$attribute = "to";
			}
			$last_updated_date = date("Y-m-d H:i:s");
			
			$sql = "INSERT INTO cse_message_contact (`message_contact_uuid`, `message_uuid`, `message_id`, `contact_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $message_contact_uuid . "', '" . $message_uuid . "', '" . $message_id . "', '" . $contact_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', :user_uuid, :customer_id)";
			//echo $sql . "\r\n";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_uuid", $_SESSION["user_id"]);
			$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
			$stmt->execute();
		}
		
		echo json_encode(array("success"=>"true", "email_id"=>$_POST["id"], "case_name"=>$kase->name));
	} catch(PDOException $e) {
		echo '{"error case_note insert":{"text":'. $e->getMessage() .'}}'; 
		die($sql);
	}
}
function assignEmail() {
	session_write_close();
	
	//die(json_encode($_POST));
	$case_id = passed_var("case_id", "post");
	$id = passed_var("id", "post");
	$webmail_message_id = $_POST["message_id"];
	$case_note = passed_var("case_note", "post");
	
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
		$error = array("error"=> array("text"=>"missing email info"));
        die( json_encode($error));
	}
	//receive it
	$pwd = decryptAES($email_info->email_pwd);
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	$obj= new receiveMail($email_info->email_name, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json", $email_info->certificate);
	
	$obj->connect();
	$tot=$obj->getTotalMails();
	
	$i = $id;
	$head = $obj->getHeaders($i);
	
	//die(print_r($head));
	//double check
	if ($webmail_message_id!=$head["message_id"]) {
		//wrong one for some reason
		$error = array("error"=> array("text"=>"missing email"));
        //die(trim($webmail_message_id) . "\r\n" . trim($head["message_id"]));
		die( json_encode($error));
	}
	
	
	$body = $obj->getBody($i);
	$body = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">', '', $body);
	$body = str_replace('<html xmlns="http://www.w3.org/1999/xhtml">', '<html>', $body);
	//die($body);
	$arrAcceptable = array("jpg", "png", "pdf", "doc", "docx", "eml");
	//this will download the attachment
	$webmail_dir = UPLOADS_PATH. $_SESSION['user_customer_id'] . "\\webmail_previews\\";
	$case_dir = UPLOADS_PATH. $_SESSION['user_customer_id'] . DC . $case_id . DC;
	if (!is_dir($case_dir)) {
		mkdir($case_dir, 0755, true);
	}
	$str=$obj->GetAttach($i,$webmail_dir, $arrAcceptable, true, "");
	$attachFiles = array();
	$attachments = "";
	if ($str!="") {
		$arrAttach = explode(",",$str);
		//eliminate anything that is not a jpg, doc, docx, or pdf
		foreach($arrAttach as $attach_index=>$attachment) {
			//find the extension
			$arrFilename = explode(".", $attachment);
			$extension = $arrFilename[count($arrFilename) - 1];
			$extension = strtolower($extension);
			if (!in_array($extension, $arrAcceptable)) {
				unset($arrAttach[$attach_index]);
			}
			//now move it from webmail_preview to main folder
			rename($webmail_dir . $attachment, $case_dir . $attachment);
		}
		$attachments = implode("|", $arrAttach);
	}
	//save the email	
	$from = $head["from"];
	$to = $head["to"];
	$subject = $head["subject"];
	
	//update the body
	$htmlpos = strpos($body, "<html");
	if ($htmlpos===false) {
		$body = "From:" . $from . "\r\nTo:" . $to . "\r\nSubject:" . $subject . "\r\n\r\n" . $body;
		$body = str_replace("\r\n", "<br />", $body);
	} else {
		$body = "From:" . $from . "<br />To:" . $to . "<br />Subject:" . $subject . "<br /><br />" . $body;
	}
	
	$webmail_message_id = $head["message_id"];
	$message_date = $head["date"];
	try {
		$db = getConnection();
		
		//first check if this $webmail_message_id is already in use
		$sql = "SELECT `webmail_uuid` `uuid`, mess.message_id, notes.notes_id
		FROM cse_webmail web
		INNER JOIN cse_message mess
		ON web.webmail_uuid = mess.message_uuid
		INNER JOIN cse_notes notes
		ON web.webmail_uuid = notes.notes_uuid
		WHERE `message_id` = '" . $webmail_message_id . "'";
		$stmt = $db->prepare($sql);  
		$webmail_result = $stmt->fetchObject();
		
		$blnAlreadyIn = true;
		if (!is_object($webmail_result)){
			$the_uuid = uniqid("TD", false);
			
			$blnAlreadyIn = false;
			die('here->3');
			$sql = "INSERT INTO cse_webmail (`webmail_uuid`, `message_id`, `user_id`, `message_date`, `from`, `subject`, `message`, `customer_id`, `deleted`)
			VALUES ('" . $the_uuid . "', '" . $webmail_message_id . "', '" . $_SESSION['user_plain_id'] . "','" . date("Y-m-d H:i:s", strtotime($message_date)) . "','" . $from . "','" . addslashes($subject) . "','" . addslashes($body) . "', '" . $_SESSION['user_customer_id'] . "', 'N')";
			
			$stmt = DB::run($sql);
		} else {
			//$the_uuid = $webmail_result->uuid;
			echo json_encode(array("email_id"=>$id, "message_id"=>$webmail_result->message_id, "notes_id"=>$webmail_result->notes_id)); 
			die();
		}
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	//add the email as a note attached to the case
	//and attachments if any
	
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$partie_id = "";
	$injury_id = "";
	$injury_uuid = "";
	$case_uuid = "";
	$type = "";
	$send_document_id = "";
	
	$blnAttachments = true;
	$arrTo = array();
	$arrToID = array();
	$arrCc = array();
	$arrCcID = array();
	$arrBcc = array();
	$arrBccID = array();				
	
	//default attribute
	$table_attribute = "main";
	$db = getConnection();
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			
			continue;
		}
		if ($fieldname=="type") {
			$type = $value;
			continue;
		}
		if ($fieldname=="partie_id") {
			$partie_id = $value;
			continue;
		}

		// || $fieldname=="message_to" || $fieldname=="message_cc" || $fieldname=="message_bcc"
		if ($fieldname=="case_file" || $fieldname=="id"  || $fieldname=="message_id" || $fieldname=="table_id" || $fieldname=="priority" || $fieldname=="source_message_id" || $fieldname=="from" || $fieldname=="case_note") {
			continue;
		}
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		if ($fieldname=="dateandtime" || $fieldname=="start_date" || $fieldname=="end_date" || $fieldname=="callback_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	if ($case_uuid=="" && $case_id!="") {
		$kase = getKaseInfo($case_id);
		$case_uuid = $kase->uuid;
	}
	//note
	$message = @processHTML($body);
	if ($case_note!="") {
		$message = $case_note . "<br /><br />" . $message;
	}
	//die($message);
	//$note = addslashes($body);
	$arrFields[] = "`message`";
	$arrSet[] = "'" . addslashes($message) . "'";
	$subject = $head['subject'];
	$arrFields[] = "`subject`";
	$arrSet[] = "'" . addslashes($subject) . "'";
	$arrFields[] = "`attachments`";
	$arrSet[] = "'" . $attachments . "'";
	
	$to = $head['to'];
	$arrFields[] = "`message_to`";
	$arrSet[] = "'" . $to . "'";
	$from = $head['from'];
	$arrFields[] = "`from`";
	$arrSet[] = "'" . $from . "'";
	$date = $head['date'];
	$date = date("Y-m-d H:i:s", strtotime($date));
	$arrFields[] = "`dateandtime`";
	$arrSet[] = "'" . $date . "'";
	$arrFields[] = "`status`";
	$arrSet[] = "'sent'";
	
	//$message_uuid = uniqid("KS", false);
	$message_uuid = $the_uuid;
	
	//message
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $message_uuid . "', " . implode(",", $arrSet) . ")";
	/*
	if ($case_note!="") {
		//note
		$arrFields = array();
		$arrSet = array();
		
		$arrFields[] = "`note`";
		$arrSet[] = "'" . addslashes($case_note) . "'";
		$subject = $head['subject'];
		$arrFields[] = "`title`";
		$arrSet[] = "'Webmail Assigned: " . addslashes($subject) . "'";
		$arrFields[] = "`subject`";
		$arrSet[] = "'" . addslashes($subject) . "'";
		$arrFields[] = "`attachments`";
		$arrSet[] = "''";
		
		$notes_uuid = uniqid("CN", false);
		//combine 
		$sql_note = "INSERT INTO `cse_notes` (`customer_id`, `entered_by`, `notes_uuid`, " . implode(",", $arrFields) . ") 
				VALUES('" . $_SESSION['user_customer_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $notes_uuid . "', " . implode(",", $arrSet) . ")";
		//die($sql_note);	
		try { 
			$stmt = DB::run($sql_note);
			
			$case_table_uuid = uniqid("CA", false);			
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the note to the case 
			$sql_note = "INSERT INTO cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $notes_uuid . "', 'webmail_note', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$stmt = DB::run($sql_note);
		} catch(PDOException $e) {
			echo '{"error case_note insert":{"text":'. $e->getMessage() .'}}'; 
		}
	}
	*/
	$arrFields = array();
	$arrSet = array();
	
	$arrFields[] = "`note`";
	$arrSet[] = "'" . addslashes($message) . "'";
	$subject = $head['subject'];
	$arrFields[] = "`title`";
	$arrSet[] = "'" . addslashes($subject) . "'";
	$arrFields[] = "`subject`";
	$arrSet[] = "'" . addslashes($subject) . "'";
	$arrFields[] = "`attachments`";
	$arrSet[] = "'" . $attachments . "'";
	//explodeRecipient($head['to'], $arrTo, $arrToID, $db);
	//explodeRecipient($head['cc'], $arrCc, $arrCcID, $db);
			
	$notes_uuid = $the_uuid;	//uniqid("KS", false);
	//combine 
	$sql_note = "INSERT INTO `cse_notes` (`customer_id`, `entered_by`, `notes_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $notes_uuid . "', " . implode(",", $arrSet) . ")";
		//die($sql_note);	
	try { 
		DB::run($sql);
	$message_id = DB::lastInsertId();
		$last_updated_date = date("Y-m-d H:i:s");
		$arrToID[] = $_SESSION["user_id"];
		
		//pass a read_status of Y so that user does not unread message in inbox
		attachRecipients('message', $message_uuid, $last_updated_date, $arrToID, 'to', $db, "Y");
		
		DB::run($sql_note);
	$notes_id = DB::lastInsertId();
		
		echo json_encode(array("message_id"=>$message_id, "notes_id"=>$notes_id, "email_id"=>$id)); 
		
		if ($case_uuid!="") {
			$case_table_uuid = uniqid("KA", false);
			//attribute
			$table_attribute = $webmail_message_id;
			
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the note to the case 
			$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $message_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$case_table_uuid = uniqid("KA", false);
			
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the note to the case 
			$sql_note = "INSERT INTO cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $notes_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			try {
				$stmt = DB::run($sql);
				
				$stmt = DB::run($sql_note);
			} catch(PDOException $e) {
				echo '{"error case notes insert":{"text":'. $e->getMessage() .'}}'; 
			}
		}
		//attachments
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
				$description = "email attachment";
				$description_html = "email attachment";
				$type = "email attachment";
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
				
				$message_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_message_document (`message_document_uuid`, `message_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $message_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";	
				$stmt = DB::run($sql);
				
				$sql = "INSERT INTO cse_notes_document (`notes_document_uuid`, `notes_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $notes_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
				
				$sql = "INSERT INTO cse_case_document (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $case_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
			}
		}
		
		trackMessage("insert", $message_id, true);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	//and then make the email a permanent inbox message
	//and then get rid of the webmail_previews/file
}
function spamCheck() {
	$raw = passed_var("raw", "post");
	
	//die(print_r($_SERVER));
	//$cmd = "PowerShell.exe -ExecutionPolicy Bypass -File .\\test_email.ps1";
	//$cmd = API_PATH."log.bat";
	$cmd = "test_email_process.bat";
	//execInBackground($cmd);
	passthru($cmd);
	
	//die($cmd);
}
function syncMail() {
	//die("function call");
	error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
	ini_set('display_errors', '1');
	
	session_write_close();
	$user_name = "sync";

	$pwd = "=d3TkJZkP=jO";
	$email_name = "sync@kustomweb.com";
	
	$obj= new receiveMail($email_name, $pwd, $email_name, "mail.kustomweb.com","POP3", 110, false, "json", "Y");
	//die(print_r($obj));
	if (!$obj->connect()) {
		die(json_encode(array("error"=>"could not connect")));
	}
	$tot = $obj->getTotalMails();
	
	$arrSuccess = json_encode(array("found"=>$tot));
	//die($arrSuccess);
	$arrTheResult = array();
	$arrAcceptable = array("jpg", "png", "pdf", "doc", "docx", "eml", "xls", "xlsx", "csv");
	$intCounter = 0;
	//$maxCounter = 4056;
	$maxCounter = 20;
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	if ($tot > $maxCounter) {
		$blnMaxCheck = true;
		$min_for = $tot - $maxCounter;
	} else {
		$blnMaxCheck = false;
		$min_for = 0;
	}
	
	$arrMessageIDs = array();
	
	$arrCompleted = array();
	for($i=$tot;$i>$min_for;$i--) {
		
		// Get Header Info Return Array Of Headers **Array Keys are (subject,to,toOth,toNameOth,from,fromName)
		$head=$obj->getHeaders($i, 0);

		if (count($head)==0) {
			//something is wrong, skip it
			continue;
		}
		
		if ($head['message_id']=="" && $head["date"]=="") {
			//something is wrong, skip it
			continue;
		}
		if ($head['message_id']=="" && $head["date"]!="") {
			//die("msse:" . strtotime($head["date"]));
			$head['message_id'] = strtotime($head["date"]);
		}
		
		$message_uuid = $head['message_id'];
		$message_uuid = str_replace("<", "", $message_uuid);
		$message_uuid = str_replace(">", "", $message_uuid);
		
		if ($message_uuid=="") {
			//something is wrong, skip it
			continue;
		}
		
		//the from gives us the user
		$sql = "SELECT user_id, customer_id 
		FROM ikase.cse_user_track
		WHERE user_email = '" . $head["from"] . "'
		ORDER BY user_track_id 
		LIMIT 0, 1";
		
		try { 
			$stmt = DB::run($sql);
			$user = $stmt->fetchObject();
			
			$customer_id = $user->customer_id;
			$user_id = $user->user_id;
			
			//now get the database
			$sql = "SELECT DISTINCT customer_id, data_source 
			FROM ikase.cse_customer
			WHERE customer_id = '" . $customer_id . "'";
			
			$stmt = DB::run($sql);
			$customer = $stmt->fetchObject();
			
			if (!is_object($customer)) {
				$obj->deleteMails($i);
				continue;
				die("no go");
			}
			
			$result = checkEmailMessage($customer_id, $user_id, $message_uuid, $head['from']) ;

			$json = json_decode($result);
			
			//die(print_r($json));
			if (is_object($json)) {
				if($json->count > 0) {
					//found it, look no longer
					continue;
				}
			} else {
				continue;
			}
			
			$data_source = $customer->data_source;
			$db_name = "ikase";
			
			if ($data_source!="") {
				$db_name = "ikase_" . $data_source;
			}
			
			//ok now we're ready to look for case
			$subject = $head["subject"];
			
			$arrWords = explode(" ", $subject);
			//die(print_r($arrWords));
			$arrSearch = array();
			foreach($arrWords as $word) {
				if (strlen($word) > 3) {
					//no special characters
					if ($word==noSpecial($word)) {
						$arrSearch[] = "'" . $word . "'";
					}
				}
			}
			//die(print_r($arrSearch));
			$sql = "NO SYNC";
			
			if (count($arrSearch) > 0) {
				$search = implode(",", $arrSearch);
				
				$sql = "SELECT case_id, case_number, file_number, case_name
				FROM `" . $db_name . "`.`cse_case` ccase
				WHERE case_number IN (" . $search . ")
				OR file_number IN (" . $search . ")";
				
				$cases = DB::select($sql);
				//die(print_r($cases));
				if (count($cases)==0) {
					die("delete " . $i);
					continue;
				}
				if (count($cases) > 1) {
					//conflict, can't do it
					die("dups " . count($cases));
				}
				//found 1
				//import
				
				$head['subject'] = cleanWord($head['subject']);
		
				$body = $obj->getBody($i);
				$body = str_replace("<o:p></o:p>", "", $body);
				$body = str_replace("<o:p>&nbsp;</o:p>", "", $body);
				$body = str_replace("<p class=MsoNormal></p>", "", $body);
				
				//$body = str_replace("<o:", "<", $body);
				//$body = str_replace("</o:", "</", $body);
				//die($body);
				if ($body != strip_tags($body)) {
					//remove any styles
					// create a new DomDocument object
					$doc = new DOMDocument();
					
					// load the HTML into the DomDocument object (this would be your source HTML)
					$doc->loadHTML($body);
					
					//die(print_r($doc));
					
					removeElementsByTagName('head', $doc);
					removeElementsByTagName('style', $doc);
					
					$body = $doc->saveHtml();
					
					//remove any comments
					while(strpos($body, "<!--") > -1) {
						$start_pos = strpos($body, "<!--");
						$end_pos = strpos($body, "-->", $start_pos);
						
						if ($end_pos > -1) {
							$comment = substr($body, $start_pos, ($end_pos - $start_pos) + 3);
							$body = str_replace($comment, "", $body);
						}
						
					}
					
					//clean office stuff
					$body = str_replace('<p class="MsoNormal">', '<p>', $body);
					
					$body = str_replace("</p><p>", "~~", $body);
					$body = str_replace("<BR", "<br", $body);
					$body = str_replace("<br>", "~~", $body);
					$body = str_replace("<br />", "~~", $body);
					
					$body = html2text($body);
					$body = str_replace("~~", "<br />", $body);
					
					$body = str_replace("\r\n", "<br />", $body);
					$body = str_replace("\n", "<br />", $body);
					$body = str_replace(chr(13), "<br />", $body);
				}
				
				$kase = $cases[0];
				//add the kase info
				/*
				$message_prefix = "RE: " . $kase->case_name . " // ID " . $kase->case_id . "
		
		";
				$body = $message_prefix . $body;
				*/
				
				//die($body);
				$subject = $head['subject'];
				
				$str = "";
				$str = $obj->ListAttach($i, $customer_id, $arrAcceptable, false, ""); 
		
				$blnAttachmentFile = true;
		
				$attach = 0;
				$attachFiles = array();
				$arrAttach = array();
				
				if ($str!="") {
					$arrAttach = explode(",",$str);
					//eliminate anything that is not a jpg, doc, docx, or pdf
					foreach($arrAttach as $attach_index=>$attachment) {
						//find the extension
						$arrFilename = explode(".", $attachment);
						$extension = $arrFilename[count($arrFilename) - 1];
						$extension = strtolower($extension);
						if (!in_array($extension, $arrAcceptable)) {
							unset($arrAttach[$attach_index]);
						}
						$attachFiles[] = "<a id='webmailattach_" . $i . "' class='email_attach_link white_text' style='cursor:pointer'>" . $attachment . "</a>";
					}
					$attach = count($arrAttach);
				}
				
				$the_result = array("id"=>$head['id'], "size"=>$head["size"], "message_id"=>$head['message_id'], "subject"=>$subject, "to"=>$head['to'], "from"=>$head['from'], "date"=>$head['date'], "attachments"=>$attach, "spam"=>$spam, "attach_files"=>implode("; ", $attachFiles));
				
				//die(print_r($the_result));
				
				$arrTheResult[] = $the_result;
				$arrMessageIDs[] = $message_uuid;
				
				$sender = $head['from'];
				if (isset($head['fromName'])) {
					$sender = $head['fromName'] . "|" . $sender;
				}
				$arrMessage = array(
					'case_id' => $kase->case_id,
					'messageId' => $message_uuid,
					'threadId' => $message_uuid,
					'messageBody' => urlencode($body),
					'messageSnippet' => "",
					'messageSubject' => $subject,
					'messageDate' => $head['date'],
					'messageSender' => $sender,
					'attachments' => implode(";", $arrAttach),
					'customer_id'=>$customer_id, 'user_id'=>$user_id, 'user_name'=>$user_name, 'destination'=>$email_info->email_address
				);			
				//die(print_r($arrMessage));
				addEmailMessage($arrMessage);
				
				$arrCompleted[] = $message_uuid;
			}
			$obj->deleteMails($i);
			//die($sql);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
		
	}
	$obj->close_mailbox();   //Close Mail Box
	
	die(json_encode(array("success"=>true, "completed"=>$arrCompleted)));
}
