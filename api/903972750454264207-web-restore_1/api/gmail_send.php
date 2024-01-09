<?php
date_default_timezone_set('America/Toronto');

//require_once('class.phpmailer.php');

require '../PHPMailer/PHPMailerAutoload.php';
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

$mail             = new PHPMailer();

$body             = "gdssdh";
//$body             = eregi_replace("[\]",'',$body);

$user_name = "nick.giszpenc@gmail.com";
$user_pwd = "G00gles1";

$user_name = "thekons23@gmail.com";
$user_pwd = "pcmg_pnk";

$user_name = "cavoucher@gmail.com";
$user_pwd = "Access527#";
/*
*/

$mail->IsSMTP(); // telling the class to use SMTP
//$mail->Host       = "ssl://smtp.gmail.com"; // SMTP server
$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only
$mail->SMTPAuth   = true;                  // enable SMTP authentication
//$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
$mail->Host       = "mail.smtp2go.com";      // sets GMAIL as the SMTP server
$mail->Port       = 2525;                   // set the SMTP port for the GMAIL server
$mail->Username   = $user_name;  // GMAIL username
$mail->Password   = $user_pwd;            // GMAIL password

$mail->SetFrom($user_name, $user_name);

//$mail->AddReplyTo("user2@gmail.com', 'First Last");

$mail->Subject    = "test @ " . date("D H:i:s");

//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($body);

$address = "mccraney66@gmail.com";
$mail->AddAddress($address, "user2");

//$mail->AddAttachment("images/phpmailer.gif");      // attachment
//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
  die(print_r($mail));
} else {
  echo "Message sent!";
}

?>