<?php
$url = 'https://eams.dwc.ca.gov/WebEnhancement/InformationCapture';
$fields = array("UAN"=>"", "requesterFirstName"=>"THOMAS", "requesterLastName"=>"SMITH", "email"=>"MATRIXDIS@GMAIL.COM", "reason"=>"CASESEARCH");
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
// curl_setopt ($ch, CURLOPT_SSLVERSION, 2);

curl_setopt ($ch, CURLOPT_CAINFO, 'cacert.pem'); 
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_POST, count($fields));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);

//execute post
$result = curl_exec($ch);
//preg_match_all('|Set-Cookie: (.*);|U', $result, $matches);
//die(print_r($matches));
if($result === false) {
    echo "Error Number:".curl_errno($ch)."<br>";
    echo "Error String:".curl_error($ch);
    die();
}
$headers = curl_getinfo($ch);
if ($headers["http_code"]==500) {
    $result = json_encode(array("error"=>"EAMS is down"));
    die($result);
}
echo "<pre>".$headers["http_code"] ."success";



?>