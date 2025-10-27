<?php
$from_name = "Nic G";
$from_mail = "nick@kustomweb.com";
$replyto = "nick@kustomweb.com";

$message = "Test message!!!";

// header
$header = "From: ".$from_name." <".$from_mail.">\r\n";
$header .= "Reply-To: ".$replyto."\r\n";
$header .= "MIME-Version: 1.0\r\n";
// $header .= "Content-Type: multipart/mixed; boundary=\123\r\n\r\n";

// message & attachment
$nmessage = "\r\n";
$nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
$nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$nmessage .= $message."\r\n\r\n";
// $nmessage .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n";
$nmessage .= "Content-Transfer-Encoding: base64\r\n";
// $nmessage .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
// $nmessage .= $content."\r\n\r\n";
// $nmessage .= "--".$uid."--";

if (mail("developermukesh3@gmail.com", "Test", $nmessage, $header)) {
	echo "sent";
    //return true; // Or do something here
} else {
	echo "not sent";
  //return false;
}
?>