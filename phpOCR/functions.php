<?php

//FIXME: same code as UploadHandler... and it sounds quite weird to connect to two different databases in the same function. why does it happen?

/**
 * @deprecated avoid using it to run queries directly through PDO; use {@link DB} methods instead, they're much simpler.
 * @return PDO
 */
function getConnection() {
	if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] == "C:\\inetpub\\wwwroot\\iKase.website") {
	    return DB::conn(DB::DB_IP_52, true);
    }
	
	//cstmwb default
    return DB::conn(DB::DB_CASEUSER);
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

/** @deprecated just use microtime(true) */
function getmicrotime(){ 
	$tmp = explode(" ",microtime());
    return ((float)$tmp[0] + (float)$tmp[1]); 
}
