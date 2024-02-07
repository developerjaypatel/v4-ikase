<?php 
$app->get('/webmail', authorize('user'), 'getEmailInbox');
$app->get('/webmail/read/:id', authorize('user'), 'readEmail');

$app->post('/webmail/preview', authorize('user'), 'previewAttachment');
$app->post('/webmail/delete', authorize('user'), 'deleteEmail');
$app->post('/webmail/assign', authorize('user'), 'assignEmail');

include("receivemail.class.php");
function readEmail($id) {
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
		$error = array("error"=> array("text"=>"missing email info"));
        die( json_encode($error));
	}
	//receive it
	$pwd = decryptAES($email_info->email_pwd);
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	$obj= new receiveMail($email_info->email_address, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json");
	
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
	$obj= new receiveMail($email_info->email_address, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json");
	
	$obj->connect();
	$tot=$obj->getTotalMails();
	
	$arrAcceptable = array("jpg", "png", "pdf", "doc", "docx", "eml");
	//this will download the attachment
	$str=$obj->GetAttach($id,"C:\\inetpub\\wwwroot\\iKase.org\\uploads\\" . $_SESSION['user_customer_id'] . "\\webmail_previews\\", $arrAcceptable, true, $name); 
		
	$success = array("success"=> array("text"=>$id));
    die( json_encode($success));
}
function deleteEmail() {
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
	$obj= new receiveMail($email_info->email_address, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json");
	
	$obj->connect();
	$tot=$obj->getTotalMails();
	$body = "";
	
	$arrIDs = explode(", ", $id);
	foreach ($arrIDs as $id) {
		$i = $id;
		$head=$obj->getHeaders($i, 0);
		
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
function getEmailInbox() {
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
		$error = array("error"=> array("text"=>"missing email info"));
        die( json_encode($error));
	}
	
	//let's do this
	$pwd = decryptAES($email_info->email_pwd);
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	$obj= new receiveMail($email_info->email_address, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json");
	
	$obj->connect();
	$tot=$obj->getTotalMails();
	
	$result = array();
	$arrAcceptable = array("jpg", "png", "pdf", "doc", "docx", "eml");
	for($i=$tot;$i>0;$i--)
	{
		$str=$obj->GetAttach($i,"C:\\inetpub\\wwwroot\\iKase.org\\uploads\\" . $_SESSION['user_customer_id'] . "\\webmail_previews\\", $arrAcceptable); 
		
		$attach = 0;
		$attachFiles = array();
		if ($str!="") {
			//echo $i . " -- " . $str . "\r\n";
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
				$attachFiles[] = "<a id='webmailattach_" . $i . "' class='email_attach_link' class='white_text' style='cursor:pointer'>" . $attachment . "</a>";
			}
			$attach = count($arrAttach);
		}
		$head=$obj->getHeaders($i, $attach);  // Get Header Info Return Array Of Headers **Array Keys are (subject,to,toOth,toNameOth,from,fromName)
		
		$result[] = array("id"=>$head['id'], "message_id"=>$head['message_id'], "subject"=>$head['subject'], "to"=>$head['to'], "from"=>$head['from'], "date"=>$head['date'], "attachments"=>$head['attachments'], "attach_files"=>implode("; ", $attachFiles));
	}
	$obj->close_mailbox();   //Close Mail Box
	
	die(json_encode($result));
}
function assignEmail() {
	//die(json_encode($_POST));
	$case_id = passed_var("case_id", "post");
	$id = passed_var("id", "post");
	$webmail_message_id = $_POST["message_id"];
	
	$email_info = getEmailInfo($_SESSION['user_plain_id']);
	if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
		$error = array("error"=> array("text"=>"missing email info"));
        die( json_encode($error));
	}
	//receive it
	$pwd = decryptAES($email_info->email_pwd);
	$ssl = ($email_info->ssl_required == "Y");
	$method = strtolower($email_info->email_method);
	$obj= new receiveMail($email_info->email_address, $pwd, $email_info->email_address, $email_info->email_server,$method, $email_info->email_port, $ssl, "json");
	
	$obj->connect();
	$tot=$obj->getTotalMails();
	
	$i = $id;
	$head = $obj->getHeaders($i);
	
	//die($webmail_message_id . "!=" . $head["message_id"]);
	//double check
	if ($webmail_message_id!=$head["message_id"]) {
		//wrong one for some reason
		$error = array("error"=> array("text"=>"missing email"));
        //die(trim($webmail_message_id) . "\r\n" . trim($head["message_id"]));
		die( json_encode($error));
	}
	
	
	$body = $obj->getBody($i);
	
	$htmlpos = strpos($body, "<html");
	if ($htmlpos===false) {
		$body = str_replace("\r\n", "<br />", $body);
	}
	
	$arrAcceptable = array("jpg", "png", "pdf", "doc", "docx", "eml");
	//this will download the attachment
	$webmail_dir = "C:\\inetpub\\wwwroot\\iKase.org\\uploads\\" . $_SESSION['user_customer_id'] . "\\webmail_previews\\";
	$case_dir = "C:\\inetpub\\wwwroot\\iKase.org\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\";
	if (!is_dir($case_dir)) {
		mkdir($case_dir, 0755, true);
	}
	$str=$obj->GetAttach($i,$webmail_dir, $arrAcceptable, true);
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
		if ($fieldname=="case_file" || $fieldname=="id"  || $fieldname=="message_id" || $fieldname=="table_id" || $fieldname=="priority" || $fieldname=="source_message_id" || $fieldname=="from") {
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
	
	//note
	$message = @processHTML($body);
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
	

	$message_uuid = uniqid("KS", false);
	//message
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $message_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);		
	//note
	//note
	$note = @processHTML($body);
	//$note = addslashes($body);
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
			
	$notes_uuid = uniqid("KS", false);
	//combine 
	$sql_note = "INSERT INTO `cse_notes` (`customer_id`, `entered_by`, `notes_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $notes_uuid . "', " . implode(",", $arrSet) . ")";
		//die($sql_note);	
	try { 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$message_id = $db->lastInsertId();
		
		$stmt = $db->prepare($sql_note);  
		$stmt->execute();
		$notes_id = $db->lastInsertId();
		
		echo json_encode(array("message_id"=>$message_id, "notes_id"=>$notes_id, "email_id"=>$id)); 
		
		if ($case_uuid=="" && $case_id!="") {
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
		}
		$case_table_uuid = uniqid("KA", false);
		//attribute
		if ($table_attribute=="") {
			//default
			$table_attribute = "main";
		}
		
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the note to the case 
		$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $message_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		$case_table_uuid = uniqid("KA", false);
		//attribute
		if ($table_attribute=="") {
			//default
			$table_attribute = "main";
		}
		
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the note to the case 
		$sql_note = "INSERT INTO cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $notes_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		try {
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$stmt = $db->prepare($sql_note);  
			$stmt->execute();
		} catch(PDOException $e) {
			echo '{"error case notes insert":{"text":'. $e->getMessage() .'}}'; 
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
				$sql = "INSERT INTO cse_message_document (`message_document_uuid`, `notes_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $message_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";	
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				
				$sql = "INSERT INTO cse_notes_document (`notes_document_uuid`, `notes_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $notes_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = $db->prepare($sql);  
				$stmt->execute();
			}
		}
		
		trackMessage("insert", $message_id);
		$db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	//and then make the email a permanent inbox message
	//and then get rid of the webmail_previews/file
}
?> 