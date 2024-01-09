<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("connection.php");

$sql_statement = $_POST["sql"];
	
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";

try {
	$schemas = DB::select($sql);
	
	//die(print_r($schemas));
	
	foreach($schemas as $schema) {
		//skip
		if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
			continue;
		}
		$sql = "SELECT '" . $schema->schema_name . "' `database`, buff.* 
		FROM `" . $schema->schema_name . "`.`cse_buffer` buff
		WHERE deleted = 'E'
		AND CAST(timestamp AS DATE) = '" . date("Y-m-d") . "'";
		
		//echo $sql . "\r\n\r\n";
		$arrSQL[] = $sql . "
		";
	}
	$sql = implode(" UNION ", $arrSQL) . "
	ORDER BY `database`, `timestamp`";
	
	$error_buffers = DB::select($sql);
	
	if (count($error_buffers) == 0) {
		die("no errors in buffer");
	}
	$arrMessage = array();
	$arrHTMLMessage = array();
	foreach($error_buffers as $buffer) {
		$arrDatabase = explode("_", $buffer->database);
		if (count($arrDatabase) == 2) {
			$buffer->database = $arrDatabase[1];
		}
		$message = "Database: " . $buffer->database . "\r\n
		Date: " . date("m/d/Y H:iA", strtotime($buffer->timestamp)) . "\r\n
		From: " . $buffer->from . "\r\n
		To: " . $buffer->to . "\r\n
		Subject: " . $buffer->subject;
		$arrMessage[] = $message;
	}
	
	//email to developer
	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
	$headers .= "From: iKase System\r\n";
	$mail_values = implode("\r\n", $arrMessage);
	$subject = 'Buffer Emails with Errors';
	/*
	//$url = "https://www.matrixdocuments.com/dis/sendit.php";
	$url = "https://gotdns.xyz/sendit.php";
	$ccs = ""; $bccs = "";
	//die($html_values);
	$fields = array("from_name"=>"iKase System", "from_address"=>"donotreply@ikase.org", "to_name"=>"nick@kustomweb.com", "cc_name"=>$ccs, "bcc_name"=>$bccs, "html_message"=>urlencode($mail_values), "text_message"=>urlencode($mail_values), "subject"=>urlencode("iKase Buffer Errors"), "attachments"=>"");
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
	
	die($result);
	*/
	$from_name = "iKase System";
	$from_address = "donotreply@ikase.org";
	$to_name = "nick@kustomweb.com";
	$cc_name = "";
	$bcc_name = "";
	
	//die($from_name . "//" . $from_address);
	include("send_test.php");
	$blnSendEmail = ($result=="sent");
	
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
