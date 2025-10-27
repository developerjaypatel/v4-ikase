<?php
include("manage_session.php");
session_write_close();

if (!isset($_SESSION["user_customer_id"])) {
	die("not logged in");
}
$url = 'https://www.cajetfile.com/ikase/login.php';
$fields = array("customer_id"=>$_SESSION["user_customer_id"], "user_id"=>$_SESSION["user_plain_id"]);

$fields_string = "";
foreach($fields as $key=>$value) { 
	$fields_string .= $key.'='.$value.'&'; 
}
rtrim($fields_string, '&');

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_HEADER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt ($ch, CURLOPT_COOKIEFILE, "cookies.txt");
curl_setopt($ch, CURLOPT_POST, count($fields_string));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);

//execute post
$result = curl_exec($ch);
//preg_match_all('|Set-Cookie: (.*);|U', $result, $matches);
//die(print_r($matches));
if($result === false) {
	echo "Error Number:".curl_errno($ch)."<br>";
	echo "Error String:".curl_error($ch);
}
$headers = curl_getinfo($ch);
//close connection
curl_close($ch);
die($result);
?>