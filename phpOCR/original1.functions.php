<?php
function getConnection() {
	if (isset($_SERVER['DOCUMENT_ROOT'])) {
		if ($_SERVER['DOCUMENT_ROOT']=="C:\\inetpub\\wwwroot\\iKase.website") {
			$dbhost = "52.34.166.217";
			$dbuser="root";
			$dbpass="admin527#";
			$dbname="ikase";
			//die(print_r($_SESSION));
			if (isset($_SESSION['user_data_source'])){
				if ($_SESSION['user_data_source']!="") {
					$dbname .= "_" . $_SESSION['user_data_source'];
				}
			}
			
			$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $dbh;
		}
	}
	
	//cstmwb default
	$dbhost="localhost";
	$dbuser="gtg_caseuser";
	$dbpass="thecase";
	$dbname="gtg_thecase";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
function curl_post_async($url, $params) {
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);
	
	//die(print_r($parts));
    $fp = fsockopen($parts['host'],
        isset($parts['port'])?$parts['port']:80,
        $errno, $errstr, 300);

    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;
	//die($out);
    fwrite($fp, $out);
    fclose($fp);
}
function noEmpty($var){
   return($var!="");
}
function getmicrotime(){ 
	$tmp = explode(" ",microtime());
    return ((float)$tmp[0] + (float)$tmp[1]); 
}
?>