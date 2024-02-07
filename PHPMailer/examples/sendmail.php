<?php
/**
 * This example shows sending a message using a local sendmail binary.
 */
// die(print_r($_SERVER));

require '../PHPMailerAutoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer;
// Set PHPMailer to use the sendmail transport
$mail->isSendmail();
//Set who the message is to be sent from
$mail->setFrom('bookings@kustomweb.com', 'Bookings');
//Set an alternative reply-to address
$mail->addReplyTo('replyto@kustomweb.com', 'Reply to');
//Set who the message is to be sent to
$mail->addAddress('nick@kustomweb.com', 'Nick Giszpenc');
//Set the subject line
$mail->Subject = 'PHPMailer sendmail test ' . date('H:i:s');
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
