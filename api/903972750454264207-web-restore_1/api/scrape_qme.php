<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$url = 'http://www.dir.ca.gov/databases/dwc/qmeN.asp';
$fields = array("scode"=>"DCH", "radius"=>"10", "zip"=>"91331");
//scode=DCH&radius=10&zip=91331
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

$doc = new DOMDocument();
@$html = $doc->loadHTML($result);

$tables = $doc->getElementsByTagName("table");

$ths = $doc->getElementsByTagName("th");
$arrFields1 = array();
foreach($ths as $cell_index=>$th) {
	if (trim($th->nodeValue) != "") {		
		//echo trim($th->nodeValue) . "<br />\r\n";
		$arrFields1[] = trim($th->nodeValue);
	}
}
$tds = $doc->getElementsByTagName("td");
$arrFirstValues = array();
$row_index = 0;
foreach($tds as $cell_index=>$td) {
	if (trim($td->nodeValue) != "") {
		if ($cell_index > 0 && ($cell_index%5)==0) {
			$row_index++;
		}
		$arrFirstValues[$row_index][] = trim($td->nodeValue);
	}
}
die(print_r($arrFirstValues));
?>