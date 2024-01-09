<?php
date_default_timezone_set('America/Los_Angeles');

require '../sendit/vendor/autoload.php';

/* USER CREDENTIALS
/  Fill in the variables below with your SendGrid
/  username and password.
====================================================*/
$sg_username = "kustomweb";
$sg_password = "sendgrid1";


/* CREATE THE SENDGRID MAIL OBJECT
====================================================*/
$sendgrid = new SendGrid( $sg_username, $sg_password );
$mail = new SendGrid\Email();

/* SMTP API
====================================================*/
// ADD THE CATEGORIES
$categories = array (
    "outgoing"
);
foreach($categories as $category) {
    $mail->addCategory($category);
}


$mail_values = "";
$mail_values .= "Client: ";

$html_mail_values = "";
$html_mail_values .= "<strong>Client:</strong> ";

$subject = "SMS/Email DRIP Notification for Nick";

/* SEND MAIL
/  Replace the the address(es) in the setTo/setTos
/  function with the address(es) you're sending to.
====================================================*/

//need to send to both email and phone
$arrDestinations = array();
$arrDestinations[] = "nick@kustomweb.com";

try {	
	$mail->
	setFrom( "billing@doctor.com" )->
	setTos( $arrDestinations )->
	setSubject( $subject )->
	setText( $mail_values )->
	setHtml( $html_mail_values );
	
	//die(print_r($mail));
	$response = $sendgrid->send( $mail );
	
	die(print_r($response));
	$blnSent = false;
	if (!$response) {
		throw new Exception("Did not receive response.");
	} else if ($response->message && $response->message == "error") {
		throw new Exception("Received error: ".join(", ", $response->errors));
	} else {
		print_r($response);
		$blnSent = true;
		die("SENT");
	}
} catch ( Exception $e ) {
	var_export($e);
}
