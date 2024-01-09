<?php
if (!isset($from_name)) {
	if ($_SERVER['REMOTE_ADDR']!='47.153.51.181') {
		die("from name is required");
	}
	if (isset($_SERVER["HTTP_ORIGIN"])) {
		if ($_SERVER["HTTP_ORIGIN"]=="https://www.cajetfile.com" || $_SERVER["HTTP_ORIGIN"]=="https://www.ikase.org") {
			header("Access-Control-Allow-Origin: " . $_SERVER["HTTP_ORIGIN"]);
		}
	}
	// ^ E_NOTICE ^ E_DEPRECATED
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	/*
	 * test_attachment_message.php
	 *
	 * @(#) $Header: /opt2/ena/metal/mimemessage/test_attachment_message.php,v 1.4 2012/09/15 09:15:48 mlemos Exp $
	 *
	 */
	require("email_message.php");
	/*
	$from_name = "Vince  Guzman";
	$from_address = "vguzman@rplawcenter.com";
	$to_name = "<nick@kustomweb.com>";
	
	$to_address = "nick@kustomweb.com";
	$subject = "Test with attach from .org at " . date("H:i:s");
	$html_message = "";
	$text_message = "I am sending emails with attachments from ikase.org";
	*/
}

//first clean up
$to_name = str_replace("<", "", $to_name);
$to_name = str_replace(">", "", $to_name);
$to_name = str_replace(";", ",", $to_name);
$arrToName = explode(",", $to_name);
$to_name = "<" . implode(">,<", $arrToName) . ">";


//echo implode(">,<", $arrToName) . "\r\n";
//echo "to_name:" . $to_name;
//die(print_r($arrToName));

//first clean up
if ($cc_name!="") {
	$cc_name = str_replace("<", "", $cc_name);
	$cc_name = str_replace(">", "", $cc_name);
	$cc_name = str_replace(";", ",", $cc_name);
	$arrToName = explode(",", $cc_name);
	$cc_name = "<" . implode(">,<", $arrToName) . ">";
}

//first clean up
if ($bcc_name!="") {
	$bcc_name = str_replace("<", "", $bcc_name);
	$bcc_name = str_replace(">", "", $bcc_name);
	$bcc_name = str_replace(";", ",", $bcc_name);
	$arrToName = explode(",", $bcc_name);
	$bcc_name = "<" . implode(">,<", $arrToName) . ">";
}
$reply_name = $from_name;
$reply_address = $from_address;
$reply_address = $from_address;
$error_delivery_name = $from_name;
$error_delivery_address = $from_address;

/*
*  Change these lines or else you will be mailing the class author.
*/

$email_message=new email_message_class;
$email_message->headers["To"] = $to_name;
//die(print_r($email_message));
if ($cc_name!="") {
	$email_message->headers["Cc"] = $cc_name;
}
if ($bcc_name!="") {
	$email_message->headers["Bcc"] = $bcc_name;
}
$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
$email_message->SetEncodedEmailHeader("Reply-To",$from_address,$from_name);
$email_message->SetHeader("Sender",$from_address);

/*
*  Set the Return-Path header to define the envelope sender address to which bounced messages are delivered.
*  If you are using Windows, you need to use the smtp_message_class to set the return-path address.
*/
if(defined("PHP_OS")
&& strcmp(substr(PHP_OS,0,3),"WIN"))
	$email_message->SetHeader("Return-Path",$error_delivery_address);

$email_message->SetEncodedHeader("Subject",$subject);

$arrAttachments = explode("|", $attachments);
$arrUnique = array_unique($arrAttachments);
$arrAttachment =  $arrUnique;

foreach($arrAttachments as $attachment) {
	if (file_exists($attachment)!="") {
		
		//$attachment = str_replace(" ", "%20", $attachment);
		//$attachment=urldecode($attachment);
		//die($attachment);
		if (strpos($attachment, ".zip") === false) {
			$email_attachment=array(
				"FileName"=>$attachment,
				"Content-Type"=>"automatic/name",
				"Disposition"=>"attachment"
			);
		} else {
			$email_attachment=array(
				"FileName"=>$attachment,
				"Content-Type"=>"application/zip",
				"Disposition"=>"attachment"
			);
		}
		$added = $email_message->AddFilePart($email_attachment);
	}
}
/*
*  A message with attached files usually has a text message part
*  followed by one or more attached file parts.
*/
$email_message->AddQuotedPrintableTextPart($email_message->WrapText($text_message));

//die("html:" . $text_message);
if ($html_message!="") {
	$email_message->AddEncodedQuotedPrintableHTMLPart($html_message, "utf-8");
}

$email_message->debug = true;

//die();
//die(print_r($email_message->parts[0]));
//die(print_r($email_message));

/*
*  The message is now ready to be assembled and sent.
*  Notice that most of the functions used before this point may fail due to
*  programming errors in your script. You may safely ignore any errors until
*  the message is sent to not bloat your scripts with too much error checking.
*/
$error = $email_message->Send();


if(strcmp($error,"")) {
	//echo "Error: $error\n";
	$email_result = "failed";
} else {
	echo "sent to " . implode(",", $arrToName) . " at " . date("H:i:s");
	$email_result = "sent";
}
