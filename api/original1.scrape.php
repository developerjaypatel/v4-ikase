<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$url = 'https://eams.dwc.ca.gov/WebEnhancement/InformationCapture';
$fields = array("UAN"=>"", "requesterFirstName"=>"THOMAS", "requesterLastName"=>"SMITH", "email"=>"webmaster@kustomweb.com", "reason"=>"CASESEARCH");

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
//die(print_r($headers));
if ($headers["http_code"]==302) {
   //redirect
	//die($headers["redirect_url"]);
	$url = $headers["redirect_url"];
	
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
	$result = curl_exec($ch);
	
	if($result === false) {
		echo "Error Number:".curl_errno($ch)."<br>";
		echo "Error String:".curl_error($ch);
	}
	
	$headers = curl_getinfo($ch);
	die($result);
	$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
	if(preg_match_all("/$regexp/siU", $result, $matches, PREG_SET_ORDER)) {
		foreach($matches as $match) {
		  // $match[2] = link address
		  if ($match[3] == "View cases") {
			  //https://eams.dwc.ca.gov/WebEnhancement/CaseFinder?partyId=-5205632355686940672&firstName=MARISSA&lastName=MORALES&caseNumber=ADJ9881786
			  
			  $filename = "https://eams.dwc.ca.gov/WebEnhancement/" . str_replace("'", "", $match[2]);
			  die($filename);
		  }
		}
	}
	die("too far");
	//die(print_r($headers));
	
	/*
	$url = "https://eams.dwc.ca.gov/WebEnhancement/InjuredWorkerFinder";
	$fields = array("caseNumber"=>"ADJ9881786", "firstName"=>"", "lastName"=>"", "dateOfBirth"=>"", "city"=>"", "zipCode"=>"");
	
	$url .= "?" . http_build_query($fields);
	*/
	$url = "https://eams.dwc.ca.gov/WebEnhancement/CaseFinder?partyId=-5205632355686940672&firstName=MARISSA&lastName=MORALES&caseNumber=ADJ9881786";
	
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
	$result = curl_exec($ch);
	
	if($result === false) {
		echo "Error Number:".curl_errno($ch)."<br>";
		echo "Error String:".curl_error($ch);
	}
	
	$headers = curl_getinfo($ch);
	//die(print_r($headers));
	
	//die($result );
	
	
	//$filename = "https://eams.dwc.ca.gov/WebEnhancement/" . str_replace("'", "", $match[2]);
	$url = "https://eams.dwc.ca.gov/WebEnhancement/CaseDetailFinder?arrayIndex=0&startIndex=0";
	
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
	$result = curl_exec($ch);
	
	if($result === false) {
		echo "Error Number:".curl_errno($ch)."<br>";
		echo "Error String:".curl_error($ch);
	}
	
	$headers = curl_getinfo($ch);
	//die(print_r($headers));
	
	echo $result . "<hr>";
	
	//http://ikase.org/api/CaseEventFinder?arrayIndex=0&startIndex=0
	$url = "https://eams.dwc.ca.gov/WebEnhancement/CaseEventFinder?arrayIndex=0&startIndex=0";
	
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
	$result = curl_exec($ch);
	
	if($result === false) {
		echo "Error Number:".curl_errno($ch)."<br>";
		echo "Error String:".curl_error($ch);
	}
	
	$headers = curl_getinfo($ch);
	//die(print_r($headers));
	
	die($result );
	
	$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
	if(preg_match_all("/$regexp/siU", $result, $matches, PREG_SET_ORDER)) {
		foreach($matches as $match) {
		  // $match[2] = link address
		  if ($match[3] == "View cases") {
			  //https://eams.dwc.ca.gov/WebEnhancement/CaseFinder?partyId=-5205632355686940672&firstName=MARISSA&lastName=MORALES&caseNumber=ADJ9881786
			  
			  $filename = "https://eams.dwc.ca.gov/WebEnhancement/" . str_replace("'", "", $match[2]);
			  /*
			  die("<script language='javascript'>window.open('" . $filename . "');</script>");
			  */
			  die($filename);
				$ctx = stream_context_create(array( 
						'http' => array( 
						'method'=>"GET",
						'timeout' => 5 
						) 
					) 
				); 
				$somecontent = file_get_contents($filename,0, $ctx);

			  die($somecontent);
			  $timeout = 5;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
				$result = curl_exec($ch);
				
				die($result );
			  break;
		  }
		}
	}
	die($result );
	
	$url = "https://eams.dwc.ca.gov/WebEnhancement/InjuredWorkerFinder";
	$fields = array("caseNumber"=>"ADJ9881786", "firstName"=>"", "lastName"=>"", "dateOfBirth"=>"", "city"=>"", "zipCode"=>"");


	$fields_string = "";
	foreach($fields as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
	
	$url .= "?" . http_build_query($fields);
	header("location:" . $url);
	die();
	$return = get($url);
}
//close connection
curl_close($ch);

function get($url) { 
	$this_headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg'; 
	$this_headers[] = 'Connection: Keep-Alive'; 
	$this_headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8'; 
	$this_user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)'; 
	$this_cookie_file = "cookies.txt";
	$this_compression = "gzip";
	$this_proxy = "";
	$this_cookies = TRUE;
	
	$process = curl_init($url); 
	curl_setopt($process, CURLOPT_HTTPHEADER, $this_headers); 
	curl_setopt($process, CURLOPT_HEADER, 0); 
	curl_setopt($process, CURLOPT_USERAGENT, $this_user_agent); 
	curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
	if ($this_cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this_cookie_file); 
	if ($this_cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this_cookie_file); 
	curl_setopt($process,CURLOPT_ENCODING , $this_compression); 
	curl_setopt($process, CURLOPT_TIMEOUT, 30); 
	if ($this_proxy) curl_setopt($process, CURLOPT_PROXY, $this_proxy); 
	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
	//curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1); 
	$return = curl_exec($process); 
	
	$headers = curl_getinfo($process);
	//die(print_r($headers));

	curl_close($process); 
	return $return; 
} 
die($return);
?>