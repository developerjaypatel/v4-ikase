<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$to      = 'terriel@documentgeeks.com, latommy1@gmail.com, mccraney66@gmail.com';
$subject = 'Test Email from v2.ikase.org';
$message = 'hello world This email was sent form Ikase.org: to ' . $to;
$headers = 'From: webmaster@v2.ikase.org' . "\r\n" .
    'Reply-To: webmaster@ikase.website' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

if (mail($to, $subject, $message, $headers)) {
	echo "tried to send From ikase.org sent to " . $to;
} else {
	$error = error_get_last();
	print_r($error);
	echo "<br />Email was not sent with errors.";
}
?>