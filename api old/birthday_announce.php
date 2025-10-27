<?php
include("email_message.php");

include("manage_session.php");
session_write_close();

set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

$sql = "SELECT * 
FROM  ";
if (($_SESSION['user_customer_id']==1033)) { 
	$sql .= "(" . SQL_PERSONX . ")";
} else {
	$sql .= "cse_person";
}
$sql .= " app
WHERE customer_id = " . $_SESSION['user_customer_id'] . "
AND person_uuid = parent_person_uuid
AND MONTH(dob) = '" . date("m") . "' AND DAY(dob) = '" . date("d") . "'";
/*
if (($_SESSION['user_customer_id']==1033)) { 
	die($sql);
}
*/
try {
	$db = getConnection();
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$dobs = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//echo json_encode($dobs);
	$arrDOB = array();
	foreach($dobs as $dob) {
		$arrDOB[] = $dob->full_name . "; Phone: " . $dob->phone . "; Cell Phone: " . $dob->cell_phone . "; Email: " . $dob->email;	
	}
	
	if (count($arrDOB) > 0) {
		$mail_values = "Clients have a birthday today";
		
		$email_message=new email_message_class;
		//$email_message->SetEncodedEmailHeader("To",$to_address,$to_name);
		$tos = "nick@kustomweb.com";
		$from_address = "donotreply@ikase.org";
		$from_name = "iKase Client Birthday Announcements";
		$subject = "iKase Client Birthday Announcement";
		$attachments = "";
		$tos = explode(";", $tos);
		$arrEmailTo = array();
		$arrEmailCc = array();
		$arrEmailBcc = array();
		foreach($tos as $to) {
			$arrEmailTo[$to] = $to;
		}
		//die(print_r($arrEmailTo));
		$email_message->SetMultipleEncodedEmailHeader('To', $arrEmailTo);
		
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
		//$text_message="Hello ".strtok($to_name," ")."\n\nThis message is just to let you know that the MIME E-mail message composing and sending PHP class is working as expected.\n\nYou may find attached to this messages a text file and and image file.\n\nThank you,\n$from_name";
		
		$text_message = $mail_values . "\r\n\r\n" . implode("\r\n", $arrDOB);
		$text_message = str_replace("<p>", "", $text_message);
		$text_message = str_replace("</p>", "\r\n\r\n", $text_message);
		$text_message = str_replace("<br>", "\r\n", $text_message);
		$text_message = str_replace("<br />", "\r\n", $text_message);
		$text_message = strip_tags($text_message);
		
		//die($text_message);
		$email_message->AddQuotedPrintableTextPart($email_message->WrapText($text_message));
		
		$arrAttachments = explode(",", $attachments);
		//die(print_r($arrAttachments ));				
		foreach ($arrAttachments as $attachment_file) {
			if ($attachment_file!="") {
				$attachment=array(
					"FileName"=>$attachment_file,
					"Content-Type"=>"automatic/name",
					"Disposition"=>"attachment"
				);
				$email_message->AddFilePart($attachment);
			}
		}
	
	/*
	 *  The message is now ready to be assembled and sent.
	 *  Notice that most of the functions used before this point may fail due to
	 *  programming errors in your script. You may safely ignore any errors until
	 *  the message is sent to not bloat your scripts with too much error checking.
	 */
		//die(print_r($email_message));
		$error=$email_message->Send();
		//die($error);
		$blnSent = (!strcmp($error,""));
		if ($blnSent) {
			$success = array("success"=> array("text"=>"sent"));
			echo json_encode($success);
		} else {
			$error = array("error"=> array("text"=>$error));
			echo json_encode($error);
		}
	}
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
?>