<?php
error_reporting(E_ALL ^ E_DEPRECATED);
ini_set('display_errors', '1');	

/**
 * This example shows making an SMTP connection with authentication.
 */

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('America/Los_Angeles');

require '../PHPMailer/PHPMailerAutoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer;
//Tell PHPMailer to use SMTP
$mail->isSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 2;
//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';
//Set the hostname of the mail server
//$mail->Host = "maserati.websitewelcome.com";
$mail->Host = "mail.smtp2go.com";
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = 2525;
//$mail->Port = 465;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication
$mail->Username = "nick@kustomweb.com";
//$mail->Username = "thomas@goldflamandbarth.com";
//Password to use for SMTP authentication
$mail->Password = "p1a2i3r4";
//$mail->Password = "admin0909";
//Set who the message is to be sent from
//$mail->setFrom('thomas@goldflamandbarth.com', 'Thomas Smith');
$mail->setFrom('donotreply@ikase.org', 'do not reply');
//Set an alternative reply-to address
$mail->addReplyTo('donotreplyto@kustomweb.com', 'Dont Reply');
//Set who the message is to be sent to
//$mail->addAddress('thomas@goldflamandbarth.com', 'Thomas iKase');
$mail->addAddress('jay@solulab.co', 'Jay Patel');
//Set the subject line
$mail->Subject = 'PHPMailer SMTP test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML(file_get_contents('../PHPMailer/examples/contents.html'), dirname(__FILE__));
//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
$mail->addAttachment('../PHPMailer/examples/images/phpmailer_mini.png');

//die(print_r($mail));
//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
