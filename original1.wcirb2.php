<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("api/connection.php");


$url = "https://www.caworkcompcoverage.com/SearchWebService.asmx/GetEmployerNameList";
$fields = array("prefixText"=>"matrix", "count"=>3);
$data_string = json_encode($fields);  

$fields_string = "";
		
foreach($fields as $key=>$value) { 
	$fields_string .= $key . '=' . urlencode($value) . '&'; 
}
rtrim($fields_string, '&');

$ch = curl_init($url);

//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);    
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(    	
	'Content-Type: application/json',    	
	'Content-Length: ' . strlen($data_string))    
);    

$result = curl_exec($ch);
$headers = curl_getinfo($ch);
//die(print_r($headers));
echo $result;

curl_close($ch);

$url = "https://www.caworkcompcoverage.com/SearchResults.aspx?name=MATRIX";
$ch = curl_init();
	
curl_setopt($ch, CURLOPT_URL, $url); 

//return the transfer as a string 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

$result = curl_exec($ch);

$headers = curl_getinfo($ch);
die(print_r($headers));
curl_close($ch);

die($result);

if ($headers["http_code"]==302) {
	//redirect
	die($headers["redirect_url"]);
	
	$url = $headers["redirect_url"];
	
	$ch = curl_init($url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);    
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(    	
		'Content-Type: application/json',    	
		'Content-Length: ' . strlen($data_string))    
	);    
	$result = curl_exec($ch);
	$headers = curl_getinfo($ch);	
	
	print_r($headers);
	
	if (strpos($result,"No results returned") > 0) {
		die(json_encode(array("empty"=>"No results returned")));
	}
	if($result === false) {
		echo "Error Number:".curl_errno($ch)."<br>";
		echo "Error String:".curl_error($ch);
	}
	
	die($result);
	
	//now search deeper
	
}

die("no redirect");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<script language="javascript">
//

function getEmployerName() {
	var url = "https://www.caworkcompcoverage.com/SearchWebService.asmx/GetEmployerNameList";
	var formData = new FormData();
	formData.append("prefixText", "matrix");
	formData.append("count", 3);
	
	var r = new XMLHttpRequest();
	r.open("POST", url, true);
	r.onreadystatechange = function () {
	  if (r.readyState != 4 || r.status != 200) {
		return;
	  } else {
		data = r.responseText;
				
		console.log(data);
	  }
	};
	r.send(formData);
}
</script>
</body>
</html>