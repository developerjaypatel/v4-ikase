<?php
include("api/connection.php");

$g_recaptcha_response = passed_var($_POST['g-recaptcha-response']);

$secret = "6Ld5xncUAAAAAFOUKK3UoSqMM5hqXzuAswVUf3EC";
$verifyResponse = 
file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);

$responseData = json_decode($verifyResponse);
$register_result = '';
//die(json_encode($responseData));

if( $responseData->success ) {
	//$register_result = 'You are not a bot';
	
} else {
	//echo "here";
	
	header("location:contact.php?captcha=y");
	die("");
}


//send out the email
//it's an email, tack on the case name to the value
$name = passed_var("name", "post");
$email = passed_var("email", "post");
$phone = passed_var("phone", "post");
$message = passed_var("message", "post");
$existing = "N";
if (isset($_POST["existing"])) { 
	$existing = passed_var("existing", "post");
}
$firm = passed_var("firm", "post");
$username = passed_var("username", "post");

$arrMessage = array();
$arrMessage[] = "From:" . $name;
$arrMessage[] = "Email:" . $email;
$arrMessage[] = "Phone:" . $phone;
$arrMessage[] = "iKase Customer:" . $existing;
if ($firm!="") {
	$arrMessage[] = "Firm:" . $firm;
}
if ($username!="") {
	$arrMessage[] = "User:" . $username;
}
$arrMessage[] = "Message:\r\n" . $message;

$email_message = implode("\r\n", $arrMessage);
//die($email_message);
$from_address = "donotreply@ikase.org";
$from_name = "iKase System";
$subject = "Website Contact Form :: iKase";
$arrRecipients[] = $email;
$arrEmailTo = array();
$arrEmailCc = array();
$arrEmailBcc = array();
$request_uuid = uniqid("RQ");

$ccs = "";
$bccs = "";
//$blnSent = sendEmail($request_uuid, $from_address, $from_name, $arrRecipients, $arrRecipients, $arrEmailCc, $arrEmailBcc, $subject, $email_message, $db, "", $check->customer_id);
$html_message = str_replace("\r\n", "<br />", $email_message);
$html_message = "";
$text_message = $email_message;
$attachments = "";

$url = "https://www.matrixdocuments.com/dis/sendit.php";
$fields = array("from_name"=>$from_name, "from_address"=>$from_address, "to_name"=>"matrixdis@gmail.com", "cc_name"=>$ccs, "bcc_name"=>$bccs, "html_message"=>urlencode($html_message), "text_message"=>urlencode($text_message), "subject"=>urlencode($subject), "attachments"=>$attachments);
//die(print_r($fields));
$fields_string = "";
foreach($fields as $key=>$value) { 
	$fields_string .= $key.'='.$value.'&'; 
}
rtrim($fields_string, '&');
$timeout = 5;
//open connection
$ch = curl_init();
		
//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_HEADER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_POST, count($fields_string));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);

//execute post
$result = curl_exec($ch);

//die($result);
$blnSent = ($result=="sent");
			
if (!$blnSent) {
	$error = "Send Error";
	//$error = array("error"=> array("text"=>$error));
	//echo json_encode($error);
	die($error);
}

header("location:contact.php?thanks=");
?>