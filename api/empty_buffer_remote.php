<?php 
include("email_message.php");

$app->get('/buffer', 'emptyBuffer');
$app->get('/reminders/buffer', 'emptyReminders');


function emptyReminders() {
	emptyBuffer(true);
}
function emptyBuffer($blnReminders = false) {
	session_write_close();
	//die();
	if (isset($_GET["customer_id"])) {
		$customer_id = $_GET["customer_id"];
	} else {
		$customer_id = $_SESSION['user_customer_id'];
	}
	
	if (!is_numeric($customer_id) || $customer_id==-1) {
		$error = array("error"=> array("text"=>"no go"));
		echo json_encode($error);
		die();
	}
	
	$joins = "INNER";
	$additional_on = " AND cmu.type = 'from'";
	$message_deleted = "mes.deleted = 'N'";
	if ($blnReminders) {
		$additional_on = "";
		$joins = "LEFT OUTER";
		$message_deleted = "1";
	}
	$sql = "SELECT 
		email.*, cb.`buffer_id`, IFNULL(mes.`message_id`, '') message_id, 
		IFNULL(mes.`message_uuid`, '') `message_uuid`, 
		cb.`from`, cb.`from_address`, 
		cb.`recipients`, cb.`to`, cb.`cc`, cb.`bcc`, cb.`subject`, cb.`message`, cb.`attachments`
	FROM
		`cse_buffer` cb
			LEFT OUTER JOIN
		`cse_sent` cs ON cb.buffer_id = cs.buffer_id
			" . $joins . " JOIN
		cse_message mes ON cb.message_uuid = mes.message_uuid
			" . $joins . " JOIN
		cse_message_user cmu ON mes.message_uuid = cmu.message_uuid" . $additional_on . "
			" . $joins . " JOIN
		`ikase`.cse_user user ON cmu.user_uuid = user.user_uuid
			LEFT OUTER JOIN
		cse_user_email cem ON user.user_uuid = cem.user_uuid
			LEFT OUTER JOIN
		cse_email email ON cem.email_uuid = email.email_uuid
	WHERE
			" . $message_deleted . "
			AND cb.deleted = 'N'
			AND cb.message != ''
			AND cb.recipients != ''";
	if ($customer_id!="-2") {
			$sql .= " 
			AND cb.customer_id = '" . $customer_id . "'";
	}
	$sql .= " 		
	AND cs.`buffer_id` IS NULL
	LIMIT 0 , 1";

	
	try { 		
		$buffer = DB::select($sql);
		
		$numberbuffer = count($buffer);
		//die( $sql . "<br />" . $numberbuffer);
		$blnBlaster = true;
		if ($numberbuffer==0) {
			$blnBlaster = false;
			$text_result = "BUFFER COMPLETED @" . date("Y-m-d H:i:s") ;
			
			//header("location:reminder_send.php");
			echo json_encode(array("data"=>$text_result, "sql"=>$sql));
			die();
		}
		
		if ($blnBlaster) {
			//echo $sql . "\r\n";
			
			$buffer_id = $buffer[0]->buffer_id;
			
			//get the message info
			$message_id = $buffer[0]->message_id;
			
			if ($customer_id!="-2" && $message_id!="") {
				$original_message = getMessageInfo($message_id);
				$case_id = $original_message->case_id;
			} else  {
				$case_id = "";
			}
			//can we use smtp
			//$outgoing_server = $buffer[0]->outgoing_server;
			//$outgoing_port = $buffer[0]->outgoing_port;
			
			$outgoing_server = "outgoing_server";
			$outgoing_port = "outgoing_port";
			
			//$encrypted_connection = $buffer[0]->encrypted_connection;
			$encrypted_connection = "";
			$outgoing_email = $buffer[0]->email_address;
			if ($outgoing_email=="") {
				$outgoing_email = $buffer[0]->from_address;
			}
			$pwd = "";
			if ($buffer[0]->email_pwd!="") {
				$pwd = decryptAES($buffer[0]->email_pwd);
			}
			//die(print_r($buffer));
			//we have stuff to blast
			$from_name = $buffer[0]->from;
			$from_address = $buffer[0]->from_address;
			$text_message = $buffer[0]->message;
			$subject = $buffer[0]->subject;
			$recipients = $buffer[0]->recipients;
			$attachments = $buffer[0]->attachments;
			
			$attachments = str_replace("|", ",", $attachments);
			$arrAttachments = explode(",", $attachments);
			
			foreach($arrAttachments as $aindex=>$attach) {
				$arrTemp = explode("../", $attach);
				if (count($arrTemp) > 2) {
					$file = $arrTemp[count($arrTemp) - 1];
					//now let's address the extension
					$arrFileInfo = explode(".", $file);	
					$blnPDF = (strtolower($arrFileInfo[count($arrFileInfo) - 1]) == "pdf");
					$blnDocx = (strtolower($arrFileInfo[count($arrFileInfo) - 1]) == "docx");
					
					if (!$blnPDF && !$blnDocx) {
						$extension = "docx";
						$file .= "." . $extension;
					}
					$arrAttachments[$aindex] = "../" . $file;
				}
			}
			
			//die(print_r($arrAttachments));	
			$tos = $buffer[0]->to;
			$ccs = $buffer[0]->cc;
			
			//special case
			if ($customer_id==1054) {
				if ($ccs=="") {
					$ccs = "lawofficeofsunil@sbcglobal.net";
				} else {
					$ccs .= ";lawofficeofsunil@sbcglobal.net";
				}
			}
			$bccs = $buffer[0]->bcc;
			$blnSendSMTP = true;
			
			//if ($outgoing_server!="" && $blnSendSMTP) {
			//if ($customer_id==1033) {
			if ($blnSendSMTP) {
				if($tos!=""){
					$html_message = $text_message; 
					$text_message = str_replace("<p>", "", $text_message);
					$text_message = str_replace("</p>", "\r\n\r\n", $text_message);
					$text_message = str_replace("</div>", "\r\n", $text_message);
					$text_message = str_replace("<br>", "\r\n", $text_message);
					$text_message = str_replace("<br />", "\r\n", $text_message);
					$text_message = str_replace("&nbsp;", " ", $text_message);
					$text_message = strip_tags($text_message);
					//Set who the message is to be sent to
					$tos = str_replace(",", ";", $tos);
					$tos = str_replace(",", ";", $tos);
					$arrTos = explode(";", $tos);
					/*
					foreach($arrTos as $to_index=>$to) {
						$arrTos[$to_index] = "<" . $to . ">";
					}
					*/
					$tos = implode(",", $arrTos);
					
					//die(print_r($arrAttachments));
					$arrFinalAttach = array();
					foreach ($arrAttachments as $attachment_file) {
						//die($attachment_file);
						if ($attachment_file=="") {
							continue;
						}
						$the_file = $attachment_file;
						$attachment_dir = UPLOADS_PATH . $customer_id . DC;
						//clean up messy upload save, REALLY?
						$blnCaseFile = false;
						if ($case_id!="") {
							$str_search_for = "D:/uploads/" . $customer_id . "/" . $case_id . "/";
							$strpos = strpos($attachment_file, $str_search_for);
							if ($strpos == 0) {
								//clean up any doubles that may be in there
								$plain_file = str_replace($str_search_for, "", $attachment_file);
								$attachment_file = $str_search_for . "/" . $plain_file;
								
								if (file_exists($attachment_dir . DC . $case_id . DC . urldecode($plain_file))) {
									
									$blnCaseFile = true;
								}
							}
						}
						
						if (!$blnCaseFile) {
							//Attach an image file or uploaded for new message	
							//die($attachment_dir . $the_file);						
							if (file_exists($attachment_dir . urldecode($the_file))) {
								$attachment_file = "D:/uploads/" . $customer_id . "/" . $the_file;
							}
						}
						//clean up
						
						$attachment_file = str_replace("//", "/", $attachment_file);
						/*$attachment_dir = UPLOADS_PATH . $customer_id . DC;	// . $case_id . DC;
						$attachment_file_path = str_replace("D:/uploads/" . $customer_id . "/", $attachment_dir, $attachment_file);
						$attachment_file_path = urldecode($attachment_file_path);
						if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
							echo "looking for :". $attachment_file_path . "<br />";
							if (!file_exists($attachment_file_path)) {
								die("not exist " . $attachment_file_path);
							} else  {
								die("does exist " . $attachment_file_path);
							}
						}
						//die("attachment_file:" . $attachment_file);
						*/
						$blnAttach = true;
						//if (!file_exists("../" . $attachment_file)) {
						/*if (!file_exists($attachment_file_path)) {
							
							if ($case_id!="" && !$blnCaseFile) {
								$attachment_file = "D:/uploads/" . $customer_id . "/" . $case_id . "/" . $the_file;
								if (!file_exists("../" . $attachment_file)) {
									$blnAttach = false;
									//die($the_file . "<br />" . $attachment_file . " NOT NOT exists");
								}
							} else {
								$blnAttach = false;
							}
						}
						*/
						
						if (!$blnAttach) {
							//die($attachment_file . " NOT exists");
							$db = getConnection();
							$sql = "UPDATE `cse_buffer` 
							SET deleted = 'E',
							`buffer_error` = '" . $attachment_file . " does not exist'
							WHERE buffer_id = '" . $buffer_id . "'";
							//echo $sql . "\r\n";
							$stmt = DB::run($sql);
						}
						$attachment_file = str_replace("../uploads", "uploads", $attachment_file);
						$attachment_file = "https://www.ikase.org/" . $attachment_file;
						
						$arrFinalAttach[] = $attachment_file;
					}
					
					$attachments = implode("|", $arrFinalAttach);
					/*
					if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
						$zip_dir = "D:/uploads/" . $customer_id . "/zips";
						if (!file_exists($zip_dir)) {
							mkdir($zip_dir, 0777);
						}
						$zip_dir .= "/" . date("Ymd");
						if (!file_exists($zip_dir)) {
							mkdir($zip_dir, 0777);
						}
						$zip_path = $zip_dir . "/attachments_" . $message_id . ".zip";
						$path = $zip_path;
						touch($path);  //<--- this line creates the file
						if (create_zip($arrFinalAttach,$zip_path,true)) {
							//die("zipped");
							$attachments = str_replace("../", "https://www.ikase.org/", $zip_path);
							
						} else {
							die("Error: not zipped");
						}
					}
					*/
					//die(print_r($arrFinalAttach));
					
					//die("text_message:" . $text_message);
					try {
						/*
						if ($customer_id=="1033") {
							$url = "https://gotdns.xyz/sendit.php";
						} else {
							$url = "https://www.matrixdocuments.com/dis/sendit.php";
						}
						*/
						//$url = "https://gotdns.xyz/sendit.php";
						//$url = "https://173.58.194.147/sendit.php";
						//die($url);
						$url = "https://www.matrixdocuments.com/dis/sendit.php";
						//$fields = array("from_name"=>$from_name, "from_address"=>$from_address, "to_name"=>$tos, "cc_name"=>$ccs, "bcc_name"=>$bccs, "html_message"=>urlencode($html_message), "text_message"=>urlencode($text_message), "subject"=>urlencode($subject), "attachments"=>$attachments);
						//caveat
						if ($attachments!="") {
							if (strpos($tos, "gmail") > -1) {
							//	$html_message = "";
							}
						}
						
						$fields = array("from_name"=>$from_name, "from_address"=>$from_address, "to_name"=>$tos, "cc_name"=>$ccs, "bcc_name"=>$bccs, "html_message"=>urlencode($html_message), "text_message"=>urlencode($text_message), "subject"=>urlencode($subject), "attachments"=>urlencode($attachments));
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
						$blnSendEmail = ($result=="sent");
					} catch ( Exception $e ) {
						//die(print_r($e));
						//not sent
					}

					/*
					require '../PHPMailer/PHPMailerAutoload.php';
					
					//Create a new PHPMailer instance
					$mail = new PHPMailer;
					
					if ($outgoing_server!="") {
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
						
						$mail->Host = "maserati.websitewelcome.com";
						$mail->Port = 26;
						$mail->Username = "ikase@kustomweb.com";
						$mail->Password = "%CQ1W*m9K~,J";
						$encrypted_connection = "None";
						
						
						//Set the hostname of the mail server
						$mail->Host = $outgoing_server;
						$mail->Port = $outgoing_port;
						//Username to use for SMTP authentication
						$mail->Username = $outgoing_email;
						//Password to use for SMTP authentication
						$mail->Password = $pwd;
						
						
						//Whether to use SMTP authentication
						$mail->SMTPAuth = true;
						$mail->SMTPAutoTLS = false;
						if ($encrypted_connection=="TLS") {
							$mail->SMTPAutoTLS = true;
							$mail->set('SMTPSecure', 'tls');
						}
						//die("enc:" . $encrypted_connection);
						if ($encrypted_connection=="SSL") {
							$mail->set('SMTPSecure', 'ssl');
						}
						if ($encrypted_connection=="None") {
							$mail->set('SMTPSecure', 'none');
						}
					} else {
						//server
						$mail->isSendmail();
					}
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
					$attachments = str_replace("|", ",", $attachments);
					$arrAttachments = explode(",", $attachments);
					//die(print_r($arrAttachments));
					foreach ($arrAttachments as $attachment_file) {
						if ($attachment_file=="") {
							continue;
						}
						//Attach an image file
						if (strpos($attachment_file, "uploads")===false) {
							$attachment_file = "D:/uploads/" . $customer_id . "/" . $attachment_file;
						}
						if (strpos($attachment_file, "uploads")==0) {
							$attachment_file = "../" . $attachment_file;
						}
						//die($attachment_file);
						if (file_exists($attachment_file)) {
							$mail->addAttachment($attachment_file);
						}
					}
					*/
					//die(print_r($mail));	
					//send the message, check for errors
					//if (!$mail->send()) {
					if (!$blnSendEmail) {
						//echo "error:<br />";
						//die($result);
						$result = str_replace("Warning: mail():", "", $result);
						$startpos = strpos($result, "in C:");
						$endpos = strpos($result, ".php", $startpos);
						$remove = substr($result, $startpos, ($endpos - $startpos) + 4);
						$error = str_replace($remove, "", $result);
						//die(trim($result));
						$blnSent = false;
						//echo "Mailer Error: " . $mail->ErrorInfo . "\r\n";
						//print_r($mail);
						//echo json_encode(array("error"=>$mail->ErrorInfo));
						//die();	
						$customer_id = $_SESSION["user_customer_id"];
						
						$db = getConnection();
				
						$sql = "UPDATE `cse_buffer` 
						SET deleted = 'E',
						`buffer_error` = '" . addslashes($error) . "' 
						WHERE buffer_id = '" . $buffer_id . "'
						AND customer_id = '" . $customer_id . "'";
						echo $sql . "\r\n";
						$stmt = DB::run($sql);
						
						//now let's notify the sender of the bounce
						//let's get message details for notification
						$sql = "SELECT usr.user_id, usr.user_uuid, usr.user_name, usr.nickname, mess.*
						FROM cse_message_user cmu
						INNER JOIN `ikase`.cse_user usr
						ON cmu.user_uuid = usr.user_uuid
						INNER JOIN cse_message mess
						ON cmu.message_uuid = mess.message_uuid
						INNER JOIN cse_buffer buf
						ON cmu.message_uuid = buf.message_uuid
						WHERE buf.buffer_id = '" . $buffer_id . "'
						AND buf.customer_id = '" . $customer_id . "'";	
						
						$stmt = DB::run($sql);
						$details = $stmt->fetchObject();
						
						$notification_message = "Your email submitted on " . date("m/d/y h:A", strtotime($details->dateandtime)) . " to " . $details->message_to . " could not be sent due to an error\r\n\r\n";
						$notification_message .= "Error:" . $error;
						$subject = "Outgoing Email Error Notification";
						$from = "system";
						$message_type = "reminder";	
						$priority = "";
						
						$dateandtime = date("Y-m-d H:i:s");
						$message_uuid = uniqid("MS", false);
						$thread_uuid = uniqid("TD", false);
						$user_uuid = $details->user_uuid;
						$case_worker = $details->nickname;
						
						$message = $notification_message;
						//i have the worker, i can send an interoffice message
						$sql = "INSERT INTO `cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
						VALUES('" . $customer_id . "', '" . $dateandtime . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
						echo $sql . "<br />";
						
						$stmt = DB::run($sql);
						
						
						$sql = "INSERT INTO `cse_message`
						(`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `subject`, `priority`, `customer_id`)
						VALUES ('" . $message_uuid . "', '" . $message_type . "', '" . $dateandtime .  "', '" . $from . "', '" . $case_worker . "', '" . addslashes($message) . "', '" . addslashes($subject) . "', '" . $priority . "', '" . $customer_id . "')";
						echo $sql . "<br />";
						
						$stmt = DB::run($sql);
						
						$case_message_uuid = uniqid("TD", false);
						
						$sql = "INSERT INTO cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
						VALUES ('" . $case_message_uuid  ."', '" . $thread_uuid . "', '" . $message_uuid . "', 'main', '" . $dateandtime . "', 'system', '" . $customer_id . "')";
						
						$stmt = DB::run($sql);
						
						$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `thread_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`";
						$sql .= ")";
						$sql .= " VALUES ('" . $case_message_uuid  ."', '" . $message_uuid . "', '" . $user_uuid . "', '" . $thread_uuid . "', 'to', '" . $dateandtime . "', 'system', '" . $customer_id . "')";
						
						$stmt = DB::run($sql);
						
						//attach the from
						$message_user_uuid = uniqid("TD", false);
						$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`)
						VALUES ('" . $message_user_uuid  ."', '" . $message_uuid . "', 'system', 'from', '" . $dateandtime . "', 'system', '" . $customer_id . "', '". $thread_uuid . "')";
						echo $sql . "<br />";	
		
						$stmt = DB::run($sql);
						
						die();
						
					} else {
						$blnSent = true;
						//echo "Message sent!";
						//die(print_r($mail));				
					}
					//die("\r\nbuffer done");
				}
			} else {
				die("no smtp");
				if($tos!=""){
					$email_message=new email_message_class;
					//$email_message->SetEncodedEmailHeader("To",$to_address,$to_name);
					$tos = explode(";", $tos);
					$arrEmailTo = array();
					$arrEmailCc = array();
					$arrEmailBcc = array();
					foreach($tos as $to) {
						$arrEmailTo[$to] = $to;
					}
					//die(print_r($arrEmailTo));
					$email_message->SetMultipleEncodedEmailHeader('To', $arrEmailTo);
					if($ccs!=""){
						$ccs = explode(";", $ccs);
						$arrEmailBcc = array();
						foreach($ccs as $cc) {
							$arrEmailCc[$cc] = $cc;
						}
						$email_message->SetMultipleEncodedEmailHeader('Cc', $arrEmailCc);
					}
					if($bccs!=""){
						$bccs = explode(";", $bccs);
						$arrEmailBcc = array();
						foreach($bccs as $bcc) {
							$arrEmailBcc[$bcc] = $bcc;
						}
						$email_message->SetMultipleEncodedEmailHeader('Bcc', $arrEmailBcc);
					}
					$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
					$email_message->SetEncodedEmailHeader("Reply-To",$from_address,$from_name);
					$email_message->SetHeader("Sender",$from_address);
				
				/*
				 *  Set the Return-Path header to define the envelope sender address to which bounced messages are delivered.
				 *  If you are using Windows, you need to use the smtp_message_class to set the return-path address.
				 */
					$error_delivery_name=$from_name;
					$error_delivery_address=$from_address;
					if(defined("PHP_OS")
					&& strcmp(substr(PHP_OS,0,3),"WIN"))
						$email_message->SetHeader("Return-Path",$error_delivery_address);
				
					$email_message->SetEncodedHeader("Subject",$subject);
				
				/*
				 *  A message with attached files usually has a text message part
				 *  followed by one or more attached file parts.
				 */

					//$email_message->AddHTMLPart($text_message);
					$email_message->AddEncodedQuotedPrintableHTMLPart($text_message, "utf-8");
					
					$text_message = str_replace("<p>", "", $text_message);
					$text_message = str_replace("</p>", "\r\n\r\n", $text_message);
					$text_message = str_replace("</div>", "\r\n", $text_message);
					$text_message = str_replace("<br>", "\r\n", $text_message);
					$text_message = str_replace("<br />", "\r\n", $text_message);
					$text_message = strip_tags($text_message);
					$email_message->AddQuotedPrintableTextPart($email_message->WrapText($text_message));
					
					$arrAttachments = explode(",", $attachments);
					if (count($arrAttachments) > 0) {
					
						foreach ($arrAttachments as $attachment_file) {
							if ($attachment_file!="") {
								//$attachment_file = $_SERVER["DOCUMENT_ROOT"] . DC . str_replace("/", "\\", $attachment_file);
								
								$attachment_file = "https://www.ikase.org/" . $attachment_file;
								//die($attachment_file);
								$attachment=array(
									"FileName"=>$attachment_file,
									"Content-Type"=>"automatic/name",
									"Disposition"=>"attachment"
								);
								$email_message->AddFilePart($attachment);
							}
						}
					}
				
				/*
				 *  The message is now ready to be assembled and sent.
				 *  Notice that most of the functions used before this point may fail due to
				 *  programming errors in your script. You may safely ignore any errors until
				 *  the message is sent to not bloat your scripts with too much error checking.
				 */
					//die(print_r($email_message));
					$error = $email_message->Send();
					//die($error);
					$blnSent = (!strcmp($error,""));
				}	//standard send
			}	
			
			//if (mail($emails, $subject, $mail_values, $headers)) { 
			if ($blnSent) {
				$db = getConnection();
				
				$sql = "UPDATE `cse_buffer` SET deleted = 'Y' WHERE buffer_id = '" . $buffer_id . "'";
				//echo $sql . "\r\n";
				$stmt = DB::run($sql);
				
				$db = getConnection();
				
				//move from buffer to log
				$sql = "INSERT INTO `cse_sent` 
				(`buffer_id`, `recipients`, `subject`, `message`, `message_uuid`, `timestamp`)
				SELECT `buffer_id`, `recipients`, `subject`, `message`, `message_uuid`, `timestamp` 
				FROM `cse_buffer` WHERE buffer_id = '" . $buffer_id . "'";
				//echo $sql . "\r\n";
						
				$stmt = DB::run($sql);
				//echo json_encode(array("buffer_id"=>$buffer_id));	
				
				//if ($customer_id==1033) {
				//	die("location:https://" . $_SERVER['HTTP_HOST'] . "/api/buffer?customer_id=" . $customer_id);
				//} else {
					header("location:https://" . $_SERVER['HTTP_HOST'] . "/api/buffer?customer_id=" . $customer_id);
				//}
				die();
			} else { // If sending mail fails.
				//no email sent
				if (isset($error)) {
					//die($error);
					$db = getConnection();
					
					$sql = "UPDATE `cse_buffer` 
					SET deleted = 'E',
					`buffer_error` = '" . addslashes($error) . "'
					WHERE buffer_id = '" . $buffer_id . "'";
					//echo $sql . "\r\n";
					$stmt = DB::run($sql);
					echo '{"error":{"text":"' . $error . '"}}'; 
				}
			}
		}
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
