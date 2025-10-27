<?php
//scrape court calendar
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");

$first_name = "THOMAS";
$last_name = "SMITH";
$email = "latommy1@gmail.com";

$url = 'https://www.dir.ca.gov/DWC/CourtCalendar/CourtCAL.asp';
$fields = array("submit1"=>"Submit", "UAN"=>"", "Firstname"=>$first_name, "Lastname"=>$last_name, "emailaddr"=>$email, "reason"=>"HEARING");
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
//preg_match_all('|Set-Cookie: (.*);|U', $result, $matches);
//die(print_r($matches));
if($result === false) {
	echo "Error Number:".curl_errno($ch)."<br>";
	echo "Error String:".curl_error($ch);
	die();
}
$headers = curl_getinfo($ch);
//die(print_r($headers));
//$result = str_replace("gf.asp", "https://www.dir.ca.gov/DWC/CourtCalendar/gf.asp", $result);
//$result = str_replace('name="Submit"', ' id="Submit"', $result);
//die($result);	

$url = 'https://www.dir.ca.gov/DWC/CourtCalendar/gf.asp';
$fields = array("Submit"=>"Download", "filename"=>"Court-Calendar.xlsx");
//die(print_r($fields));
$fields_string = "";
foreach($fields as $key=>$value) { 
	$fields_string .= $key.'='.$value.'&'; 
}
rtrim($fields_string, '&');
$timeout = 5;

// file handler
//$outputfilename = '../xlsx2csv/courtcalendar.xlsx';

$outputfilename = "../uploads/courtcalendar/courtcalendar.xlsx";
$archivefilename = "../uploads/courtcalendar/" . date("Ymd") . ".xlsx";

if (file_exists($outputfilename)) {
	rename($outputfilename, $archivefilename);
}
//clear out old files
$yesterday  = mktime(0, 0, 0, date("m") , date("d") - 3, date("Y"));

$check_dir = '../uploads/courtcalendar';
$wdir = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\courtcalendar";
$ccals = scandir($check_dir);
foreach($ccals as $ccal_index=>$ccal) {
	if ($ccal=="." || $ccal==".." || $ccal=="bin" || $ccal=="csv" || $ccal=="courtcalendar.xlsx") {
		continue;
	}
	$filepath = $wdir . "\\" . $ccal;
	$filemtime  = filemtime ($filepath);
	
	if ($filemtime < $yesterday) {
		//die("del:" . $filepath . " -- " . $yesterday . " < " . date("m/d/Y H:i", $filemtime));
		unlink($filepath);
	}
}

//die();

$fileoutput = fopen($outputfilename, 'w');
//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_POST, count($fields_string));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
// set file handler option
//curl_setopt($ch, CURLOPT_FILE, $fileoutput);


$file = curl_exec($ch);
curl_close($ch);

// close file
//fclose($fileoutput);
//die('<a href="' . $outputfilename . '">' . $outputfilename . '</a>');

$file_array = explode("\n\r", $file, 2);
$header_array = explode("\n", $file_array[0]);

foreach($header_array as $header_value) {
    $header_pieces = explode(':', $header_value);
    if(count($header_pieces) == 2) {
        $headers[$header_pieces[0]] = trim($header_pieces[1]);
    }
}

fwrite($fileoutput, substr($file_array[1], 1));
fclose($fileoutput);
/*
$fp = fopen('scrape_data.txt', 'a+');
fwrite($fp, 'court calendar  @ ' . date('m/d/y H:i:s') . chr(10));
fclose($fp); 
*/
$params = array();
curl_post_async("https://v2.ikase.org/api/scrape_convert_xl.php", $params);
?>