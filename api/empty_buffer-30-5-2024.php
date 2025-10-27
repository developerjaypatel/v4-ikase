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
		cse_message_user cmu ON mes.message_id = cmu.message_id" . $additional_on . "
			" . $joins . " JOIN
		`ikase`.cse_user user ON cmu.user_id = user.user_id
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
	ORDER BY buffer_id DESC
	LIMIT 0 , 1";
	//die($sql);
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
				$message_uuid = $original_message->uuid;
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
				// $pwd = decryptAES($buffer[0]->email_pwd);
				$pwd =$buffer[0]->email_pwd;

			}
			
			//die(print_r($buffer));
			//we have stuff to blast
			$from_name = trim($buffer[0]->from);
			$from_address = trim($buffer[0]->from_address);
			$text_message = $buffer[0]->message;
			$subject = $buffer[0]->subject;
			$recipients = $buffer[0]->recipients;
			$attachments = $buffer[0]->attachments;
			//die($recipients);
			
			//$attachments = str_replace("|", "####", $attachments);
			$arrAttachments = explode("|", $attachments);
			
			$blnInvoice = false;
			//if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') {
			if (count($arrAttachments)==1) {
				$file = $arrAttachments[0];
				if (strpos($file, "/invoices/")!==false) {
					$blnInvoice = true;
				}
			}
			//}
			if (!$blnInvoice) {
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
			}
			
			$tos = $buffer[0]->to;
			$ccs = $buffer[0]->cc;
			
			if(!filter_var($ccs, FILTER_VALIDATE_EMAIL)) {
				$ccs = "";
			}

			//die($ccs);
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
			
			$dateandtime = date("Y-m-d H:i:s");
			
			if ($blnSendSMTP) {
				//hard coded to be true
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
					
					$arrFinalAttach = array();
					foreach ($arrAttachments as $attachment_file) {
						//die($attachment_file);
						if ($attachment_file=="") {
							continue;
						}
						$the_file = $attachment_file;
						$the_file = urldecode($the_file);
						if ($blnInvoice) {
							$attachment_dir = UPLOADS_PATH . $customer_id . "\\invoices";
							$str_search_for = "D:/uploads/" . $customer_id . "/invoices/";
							$plain_file = str_replace($str_search_for, "", $attachment_file);
							$attachment_file = $attachment_dir . DC . $plain_file;
							//$arrFinalAttach[] = $attachment_file;
						} else {
							$attachment_dir = UPLOADS_PATH . $customer_id . DC;
							//clean up messy upload save, REALLY?
							$blnCaseFile = false;
							if ($case_id!="") {
								$str_search_for = "D:/uploads/" . $customer_id . "/" . $case_id . "/";
								//die($str_search_for);
								$strpos = strpos($attachment_file, $str_search_for);
								if ($strpos == 0) {
									//clean up any doubles that may be in there
									$plain_file = str_replace($str_search_for,"", $attachment_file);
									//$attachment_file = $str_search_for . "/" . $plain_file;
									$attachment_file = $attachment_dir . $case_id . DC . $plain_file;
									
									if (file_exists($attachment_dir . $case_id . DC . urldecode($plain_file))) {
										//die("here");
										
										$blnCaseFile = true;
									}
								}
							}
						//die($attachment_dir . $case_id . DC . urldecode($plain_file));
							//not a case file							
							if (!$blnCaseFile) {
								//die("not case");
								//Attach an image file or uploaded for new message	
								//die($attachment_dir . $the_file);						
								if (file_exists($attachment_dir . $the_file)) {
									//$attachment_file = "D:/uploads/" . $customer_id . "/" . $the_file;
									$attachment_file = $attachment_dir . $the_file;
								}
							}
						}
						//clean up
						
						//$attachment_file = str_replace("//", "/", $attachment_file);
						
						if ($blnInvoice) {
							$sql_update_invoice = "UPDATE `cse_message_kinvoice`
							SET `attribute_2` = 'sent',
							`last_updated_date` = '" . $dateandtime . "'
							WHERE `message_uuid` = '" . $message_uuid . "'
							AND `customer_id` = '" . $_SESSION['user_customer_id'] . "'";
							
							//die($sql_update_invoice);  
							
						}
						$blnAttach = true;
						
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
						//$attachment_file = str_replace("../uploads", "uploads", $attachment_file);
						//$attachment_file = "https://www.ikase.org/" . $attachment_file;
						
						$arrFinalAttach[] = $attachment_file;
						
					}
					$attachments = implode("|", $arrFinalAttach);
					$blnSendEmail = false;
					
					try {
						//"from_name"=>$from_name, "from_address"=>$from_address, "to_name"=>$tos, "cc_name"=>$ccs, "bcc_name"=>$bccs, "html_message"=>urlencode($html_message), "text_message"=>urlencode($text_message), "subject"=>urlencode($subject), "attachments"=>urlencode($attachments)
						$to_name = $tos;
						$cc_name = $ccs;
						$bcc_name = $bccs;
						//die($to_name);
						//die($from_name . "//" . $from_address);
						
						include("send_test.php");
						
						$result = $email_result;
						$blnSendEmail = ($email_result=="sent");
						//die("here");
						
					} catch ( Exception $e ) {
						//die("error");
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
						if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') {
						//	die($result);
						}
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
						//echo $sql . "\r\n";
						$stmt = DB::run($sql);
						
						//now let's notify the sender of the bounce
						//let's get message details for notification
						$sql = "SELECT usr.user_id, usr.user_uuid, usr.user_name, usr.nickname, mess.*
						FROM cse_message_user cmu
						INNER JOIN `ikase`.cse_user usr
						ON cmu.user_id = usr.user_id
						INNER JOIN cse_message mess
						ON cmu.message_id = mess.message_id
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
						
						$message_uuid = uniqid("MS", false);
						$thread_uuid = uniqid("TD", false);
						$user_uuid = $details->user_uuid;
						$user_id = $details->user_id;
						
						$case_worker = $details->nickname;
						
						$message = $notification_message;
						//i have the worker, i can send an interoffice message
						$sql = "INSERT INTO `cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
						VALUES('" . $customer_id . "', '" . $dateandtime . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
						//echo $sql . "<br />";
						
						$stmt = DB::run($sql);
						
						
						$sql = "INSERT INTO `cse_message`
						(`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `subject`, `priority`, `customer_id`)
						VALUES ('" . $message_uuid . "', '" . $message_type . "', '" . $dateandtime .  "', '" . $from . "', '" . $case_worker . "', '" . addslashes($message) . "', '" . addslashes($subject) . "', '" . $priority . "', '" . $customer_id . "')";
						echo $sql . "<br />";
						
						DB::run($sql);
	$message_id = DB::lastInsertId();
						
						$case_message_uuid = uniqid("TD", false);
						
						$sql = "INSERT INTO cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`, `message_id`)
						VALUES ('" . $case_message_uuid  ."', '" . $thread_uuid . "', '" . $message_uuid . "', 'main', '" . $dateandtime . "', 'system', '" . $customer_id . "', '" . $message_id . "')";
						
						$stmt = DB::run($sql);
						
						$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `thread_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, message_id, user_id";
						$sql .= ")";
						$sql .= " VALUES ('" . $case_message_uuid  ."', '" . $message_uuid . "', '" . $user_uuid . "', '" . $thread_uuid . "', 'to', '" . $dateandtime . "', 'system', '" . $customer_id . "','" . $message_id . "','" . $user_id . "')";
						
						$stmt = DB::run($sql);
						
						//i need the system user_id
						$sql = "SELECT user_id
						FROM ikase.cse_user
						WHERE user_name = 'system'
						AND customer_id = " . $customer_id;
						
						$stmt = DB::run($sql);
						$system_user = $stmt->fetchObject();
						
						//attach the from
						$message_user_uuid = uniqid("TD", false);
						$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`, message_id, user_id)
						VALUES ('" . $message_user_uuid  ."', '" . $message_uuid . "', 'system', 'from', '" . $dateandtime . "', 'system', '" . $customer_id . "', '". $thread_uuid . "','" . $message_id . "','" . $system_user->user_id . "')";
						//echo $sql . "<br />";	
		
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
				
				if ($blnInvoice) {
					$stmt = DB::run($sql_update_invoice);
					
				}
				header("location:https://" . $_SERVER['HTTP_HOST'] . "/api/buffer?customer_id=" . $customer_id);
				
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
