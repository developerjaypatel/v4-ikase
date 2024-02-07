<?php
ini_set('SMTP','localhost'); 
ini_set('sendmail_from', 'admin@ikase.org'); 

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
$headers .= "From: ikase <donotreply@ikase.org>\r\n";
$to = "nick@kustomweb.com,nick.giszpenc@gmail.com";
//,mccraney66@gmail.com
$mail_values = "first_name: \r\n";
$ip=@$REMOTE_ADDR; 
$mail_values .= "ip address: " . $_SERVER['REMOTE_ADDR'] . "\r\n";

$subject = 'Contact Info from ikase @ ' . date("H:i:s");
//die($mail_values);	
//;
if (@mail($to, $subject, $mail_values, $headers)) { 
	echo "sent-". date("h:i:s");;
}
?>