<?php
function cvf_convert_object_to_array($data) {

    if (is_object($data)) {
        $data = get_object_vars($data);
    }

    if (is_array($data)) {
        return array_map(__FUNCTION__, $data);
    }
    else {
        return $data;
    }
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Report all PHP errors
error_reporting(-1);


$long_url = 'https://stackoverflow.com/questions/ask';
$apiv4 = 'https://api-ssl.bitly.com/v4/bitlinks';
$genericAccessToken = '32a1753df6a6a6ac67764772d9e3aabae50ae4f6';

$data = array(
    'long_url' => $long_url
);
$payload = json_encode($data);

$header = array(
    'Authorization: Bearer ' . $genericAccessToken,
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload)
);

$ch = curl_init($apiv4);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//Disable CURLOPT_SSL_VERIFYHOST and CURLOPT_SSL_VERIFYPEER by
//setting them to false.
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);
echo "<pre>";
$result = json_decode($result);
$result =  cvf_convert_object_to_array($result);
print_r($result['link']);
print_r(curl_error($ch));
die();
$long_url = "https%3A%2F%2Fwww.ikase.website%2Fapi%2Fsync_calendar_kase.php%3F%26token%3De1e61d48ad7f05e47030c5a0510b55cc.TS5acfc8a0f1cab";
$apiv4 = 'https://api-ssl.bitly.com/v4/bitlinks';
$genericAccessToken = '32a1753df6a6a6ac67764772d9e3aabae50ae4f6';

$data = array(
    'long_url' => $long_url
);
$payload = json_encode($data);
echo $payload;
$payload = "{'long_url':'https:\/\/stackoverflow.com\/questions\/ask'}";
$header = array(
    'Authorization: Bearer ' . $genericAccessToken,
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload)
);

$ch = curl_init($apiv4);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
// curl_setopt($ch, CURLOPT_VERBOSE, true);

//Disable CURLOPT_SSL_VERIFYHOST and CURLOPT_SSL_VERIFYPEER by
//setting them to false.
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);
echo "<pre>";
$result = json_decode($result);
print_r(curl_error($ch));
print_r($result);


// die();



// function callAPI($method, $url, $data){
//     $curl = curl_init();
//     switch ($method){
//        case "POST":
//           curl_setopt($curl, CURLOPT_POST, 1);
//           if ($data)
//              curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
//           break;
//        case "PUT":
//           curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
//           if ($data)
//              curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
//           break;
//        default:
//           if ($data)
//              $url = sprintf("%s?%s", $url, http_build_query($data));
//     }
//     // OPTIONS:
//     curl_setopt($curl, CURLOPT_URL, $url);
//     curl_setopt($curl, CURLOPT_HTTPHEADER, array(
//     //    'Authorization: 32a1753df6a6a6ac67764772d9e3aabae50ae4f6',
//        'Content-Type: application/json',
//     ));
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//     // EXECUTE:
//     $result = curl_exec($curl);
//     if(!$result){die("Connection Failure");}
//     curl_close($curl);
//     return $result;
//  }
//  $get_data = callAPI('GET', "https://api-ssl.bitly.com/v3/shorten?access_token=32a1753df6a6a6ac67764772d9e3aabae50ae4f6&longUrl=https%3A%2F%2Fwww.ikase.website%2Fapi%2Fsync_calendar_kase.php%3F%26token%3De1e61d48ad7f05e47030c5a0510b55cc.TS5acfc8a0f1cab", false);
//  $response = json_decode($get_data, true);
//  $errors = $response['response']['errors'];
//  $data = $response['response']['data'][0];
// die();
// // include("connection.php");
// 	// die("JAY JAY JP");
//     // die("JAY - ".$url);
//     // $url = "https://www.ikase.website/api/sync_calendar_kase.php?&token=e1e61d48ad7f05e47030c5a0510b55cc.TS5acfc8a0f1cab";
//     $url = "https://www.ikase.website/api/sync_calendar_kase.php?&token=e1e61d48ad7f05e47030c5a0510b55cc.TS5acfc8a0f1cab";    
// 	$format = 'json';
// 	//create the URL
// 	$bitly = 'https://api-ssl.bitly.com/v4/shorten?longUrl=' . urlencode($url);
	
//     //die($bitly);
    

//     echo "API Calling Start<br>";
//     $header = array(
//         'Accept: application/json',
//         'Content-Type: application/x-www-form-urlencoded',
//         'Authorization: 32a1753df6a6a6ac67764772d9e3aabae50ae4f6'
//     );
// 	$ch = curl_init();
//     $timeout = 5;
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
// 	curl_setopt($ch, CURLOPT_URL, $url);
// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
//     $data = curl_exec($ch);
//     curl_close($ch);
//     die($data);
//     echo "<br>API Calling End";

//     $response = $data;
    
// 	die("response:" . $response);
// 	//parse depending on desired format
// 	if(strtolower($format) == 'json') {
// 		$json = @json_decode($response,true);
// 		die("if JAY - ".$json);
// 		return $json;
// 	}
// 	else //xml
// 	{
// 		$xml = simplexml_load_string($response);
// 		die("else JAY - ".$xml->results->nodeKeyVal->hash);
// 		return 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
// 	}


?>