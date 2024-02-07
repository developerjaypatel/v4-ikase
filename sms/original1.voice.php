<?php
//Generate the voice
$customer_id = 1033;
$reminder_id = 619;
$message_id = 3001;
$message = "Hello this is a test.";
$url = "http://kustomweb.xyz/ikase_voice/make_mp3.php?customer_id=" . $customer_id . "&reminder_id=" . $reminder_id . "&message_id=" . $message_id . "&message=" . urlencode($message);
// die($url);
$fields_string = array();

//open connection
$ch = curl_init();
//die($url);
//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
curl_setopt($ch, CURLOPT_POST, count($fields_string));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

//$content = "<Response><Speak>" . $message . "</Speak></Response>";
$result = curl_exec($ch);
die(print_r($result));
// echo "success \r\n";


// //Make the Call
// require_once 'plivo.php';

// $response = new Response();
// $threeLoops = array ('loop' => 3,);
// $oneLoop = array ('loop' => 1,);


// $linguatec_url = "http://kustomweb.xyz/speech/spoken/" . $customer_id . "/" . $batch_id . "/" . $batch_drop_id . "/output_" . $debtor_id . ".mp3";
// $response->addPlay($linguatec_url, $threeLoops);


// $body_message = 'Please go to www.rcs.com to pay.';
// if (isset($_GET["text"])) {
//     $body_message = urldecode(passed_var("text", "get"));
// }
// $response->addSpeak($body_message, $threeLoops); 
?>