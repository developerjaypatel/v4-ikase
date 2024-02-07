<?php
include("chain_pack.php");

$blnDebug = false;

if (!isset( $_SESSION['user_customer_id'] )) {
	 $_SESSION['user_customer_id'] = -1;
}
// Voice calling key (Plivo)
$auth_id = "MANGQYZMU3MWU4MZM2MT";
$auth_token = "MDFiMWQ1MjYwN2Q3N2Q1NDNiODE1ZDM3ZjNmNmRl";
$auth_phone = "+17472251107";
// Text messaging key (Nexmo) 
$api_key = "9bf77d58";
$api_secret = "9f3642052847f430";
$from = "12133959868";

//$blnDebug = ($_SERVER['REMOTE_ADDR']=='71.119.40.148' && $_SESSION['user_customer_id']==1033);	
//false;	
//($_SESSION['user_nickname']=="MA");
//($_SESSION['user_customer_id']=="1057");

DEFINE ("KASES_LIMIT", 1201);
DEFINE ("SQL_PERSONX", "SELECT 
			pers.`personx_id` `person_id`,
			pers.`personx_uuid` `person_uuid`,
			pers.`parent_personx_uuid` `parent_person_uuid`,
			CAST(AES_DECRYPT(pers.`full_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `full_name`,
			CAST(AES_DECRYPT(pers.`company_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `company_name`,
			CAST(AES_DECRYPT(pers.`first_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `first_name`,
			CAST(AES_DECRYPT(pers.`middle_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `middle_name`,
			CAST(AES_DECRYPT(pers.`last_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `last_name`,
			CAST(AES_DECRYPT(pers.`aka`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `aka`,
			CAST(AES_DECRYPT(pers.`preferred_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `preferred_name`,
			CAST(AES_DECRYPT(pers.`full_address`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `full_address`,
			pers.`longitude`,
			pers.`latitude`,
			CAST(AES_DECRYPT(pers.`street`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `street`,
			pers.`city`,
			pers.`state`,
			pers.`zip`,
			CAST(AES_DECRYPT(pers.`suite`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `suite`,
			CAST(AES_DECRYPT(pers.`phone`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `phone`,
			CAST(AES_DECRYPT(pers.`email`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `email`,
			CAST(AES_DECRYPT(pers.`fax`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `fax`,
			CAST(AES_DECRYPT(pers.`work_phone`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `work_phone`,
			CAST(AES_DECRYPT(pers.`cell_phone`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `cell_phone`,
			CAST(AES_DECRYPT(pers.`other_phone`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `other_phone`,
			CAST(AES_DECRYPT(pers.`work_email`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `work_email`,
			CAST(AES_DECRYPT(pers.`ssn`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `ssn`,
			CAST(AES_DECRYPT(pers.`ssn_last_four`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `ssn_last_four`,
			CAST(AES_DECRYPT(pers.`dob`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `dob`,
			CAST(AES_DECRYPT(pers.`license_number`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `license_number`,
			pers.`title`,
			CAST(AES_DECRYPT(pers.`ref_source`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `ref_source`,
			CAST(AES_DECRYPT(pers.`salutation`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `salutation`,
			pers.`age`,
			pers.`priority_flag`,
			pers.`gender`,
			pers.`language`,
			CAST(AES_DECRYPT(pers.`birth_state`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `birth_state`,
			CAST(AES_DECRYPT(pers.`birth_city`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `birth_city`,
			pers.`marital_status`,
			pers.`legal_status`,
			CAST(AES_DECRYPT(pers.`spouse`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `spouse`,
			CAST(AES_DECRYPT(pers.`spouse_contact`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `spouse_contact`,
			CAST(AES_DECRYPT(pers.`emergency`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `emergency`,
			CAST(AES_DECRYPT(pers.`emergency_contact`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `emergency_contact`,
			pers.`last_updated_date`,
			pers.`last_update_user`,
			pers.`deleted`,
			pers.`customer_id`,
			pers.personx_id id, pers.personx_uuid uuid
			  
			FROM `cse_personx` pers 
			WHERE pers.deleted = 'N'
			AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by pers.personx_id");
			
function isPad(){

    return preg_match("/iPad;/i", $_SERVER["HTTP_USER_AGENT"]);

}
function isMobile() {

    return
preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hipt
op|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
$_SERVER["HTTP_USER_AGENT"]);

}
function rank_password($x) {
	$rank = Array();

	$rank['length'] = strlen($x);
	
	$matches = Array();
	preg_match_all("/([a-z]+)/", $x, $matches);
	$rank['lowercase'] = 0;
	if (count($matches[0])>0) {
		$rank['lowercase'] = strlen(implode('', $matches[0]))/count($matches[0]);
	}
	$matches = Array();
	preg_match_all("/([A-Z]+)/", $x, $matches);
	$rank['uppercase'] = 0;
	if (count($matches[0])>0) {
		$rank['uppercase'] = strlen(implode('', $matches[0]))/count($matches[0]);
	}
	$matches = Array();
	preg_match_all("/([0-9]+)/", $x, $matches);
	$rank['numbers'] = 0;
	if (count($matches[0])>0) {
		$rank['numbers'] = strlen(implode('', $matches[0]))/count($matches[0]);
	}
	$matches = Array();
	preg_match_all("/([^a-zA-Z0-9]+)/", $x, $matches);
	$rank['symbols'] = 0;
	if (count($matches[0])>0) {
		$rank['symbols'] = strlen(implode('', $matches[0]))/count($matches[0]);
	}
	return $rank;
}
function joinTables($table1, $table2, $table1_id,$table2_id,$attribute, $clearfirst = false) {
	$result = false;
	//rebuild the table name
	$tables = $table1 . "_" .$table2;
	try {
		$db = getConnection();
		
		if ($clearfirst == true) {
			//kill all the existing relationships for the first uuid and this attribute
			$query="delete from `cse_$tables` where `" . $table1 . "_uuid` = '" . $table1_id . "' and attribute = '$attribute'";
			//echo "kill relationships from jointable: " . $query . "<br>";
			$stmt = $db->prepare($query);  
			$stmt->execute();
		}
		if ($table1_id != "" && $table2_id != "") {
			//kill the existing relationship if any
			$query="delete from `cse_$tables` where `" . $table1 . "_uuid` = '" . $table1_id . "' and `" . 
				$table2 . "_uuid` = '" . $table2_id . "'  ";
			/*
			if ($attribute!="") {
				$query .= " and attribute = '$attribute'";
			}
			*/
			//echo "kill the existing jointable relationship if any" . $query . "<br>";
			$stmt = $db->prepare($query);  
			$stmt->execute();
			
			//insert relationship
			$uuid = uniqid('KS') ;
			$last_updated_date = date("Y-m-d H:i:s");
			$query="insert into `cse_$tables` (`" . $table1 . "_uuid`,`" . $table2 . "_uuid`,`attribute`,`last_updated_date`, `last_update_user`, `customer_id`, `" . $table1 . "_" . $table2 . "_uuid`) 
			VALUES ('" . $table1_id . "','" . $table2_id . "','$attribute', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', " . $_SESSION['user_customer_id'] . ", '$uuid')";
			$stmt = $db->prepare($query);  
			$stmt->execute();
		}
		$db = null;
		return;
		
		
		//track now
		//trackNote("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function unjoinTables($table1, $table2, $table1_id,$table2_id,$attribute) {
	$result = false;
	//rebuild the table name
	$tables = $table1 . "_" .$table2;
	try {
		$db = getConnection();
		
		if ($table1_id != "" && $table2_id != "") {
			//kill the existing relationship if any
			$query="delete from `cse_$tables` where `" . $table1 . "_uuid` = '" . $table1_id . "' and `" . 
				$table2 . "_uuid` = '" . $table2_id . "' ";
			if ($attribute!="") {
				$query .= " and attribute = '$attribute'";
			}
			//echo "kill the existing jointable relationship if any" . $query . "<br>";
			$stmt = $db->prepare($query);  
			$stmt->execute();
		}
		$db = null;
		return;
		
		
		//track now
		//trackNote("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function processHTML($strValue) {
	$doc = new DOMDocument();

	// load the HTML string we want to strip
	$strValue = str_replace("<o:p>", "<p>", $strValue);
	$strValue = str_replace("</o:p>", "</p>", $strValue);
	$doc->loadHTML($strValue);
	
	// get all the script tags
	$script_tags = $doc->getElementsByTagName('script');
	
	$length = $script_tags->length;
	
	// for each tag, remove it from the DOM
	for ($i = 0; $i < $length; $i++) {
	  $script_tags->item($i)->parentNode->removeChild($script_tags->item($i));
	}
	
	// get the HTML string back
	$value = $doc->saveHTML();
	$spos = strpos($value, "loose.dtd");
	$value = substr($value, $spos + 12);
	$value = str_replace('<html><body>', '', $value);
	$value = str_replace('</body></html>', '', $value);
	
	//remove weird characters
	$arrForbidden = array("&Atilde;","&#131;","&AElig;","&#146;","&#134;","&acirc;","&#128;","&#153;","&Acirc;","&cent;","&#154;","&not;","&#133;","&iexcl;","&#130;","&#132;", "&Aring;", "&brvbar;", "&frac34;");
	
	foreach($arrForbidden as $forbidden) {
		$value = str_replace($forbidden, '', $value);
	}
	
	//final check for double spaces on first line
	if(strpos($value, "<p>")==0 && strpos($value, "</p><div>") > 0) {
		$value = trim(substr($value, 3));
		$endpos = strpos($value, "</p><div>");
		$value = "<div>" . substr($value, 0, $endpos) . "</div><div>" . substr($value, $endpos + 9);
	}
	return $value;
}
function getConnection() {
	if (isset($_SERVER['DOCUMENT_ROOT'])) {
		if ($_SERVER['DOCUMENT_ROOT']=="C:\\inetpub\\wwwroot\\iKase.website") {
			//$dbhost = "52.34.166.217";
			$dbhost = "ikase.website";
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
function post_curl($url, $fields) {
	$fields_string = "";
		
	foreach($fields as $key=>$value) { 
		$fields_string .= $key . '=' . urlencode($value) . '&'; 
	}
	rtrim($fields_string, '&');

	$ch = curl_init();
	if (strpos($url, "combine") > -1) {
		echo $fields_string . "\r\n";
		die();
	}
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, count($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	
	$result = curl_exec($ch);
	curl_close($ch);
	
	return $result;
}
function get_curl($url) {
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, $url); 

	//return the transfer as a string 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	
	$result = curl_exec($ch);
	curl_close($ch);
	
	return $result;
}
function curl_post_async($url, $params) {
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);

    $fp = fsockopen($parts['host'],
        isset($parts['port'])?$parts['port']:80,
        $errno, $errstr, 300);

    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;

    fwrite($fp, $out);
    fclose($fp);
}
function set_default_null(&$var, $default='') {
	if($var == '') {
		$var = $default;
		return true;
	}	else return false;
}
function clean_html($text)	{
	$search = array("'<script[^>]*?><title>Processing your feedback...</title>.*?</script>'si",	// strip out javascript
					"'<[\/\!]*?[^<>]*?>'si",			// strip out html tags
					"'([\r\n])[\s]+'"						// strip out white space
					);
	$replace = array("",
						"",
						"\\1"
						);

	$text = preg_replace($search, $replace, $text);

	return $text;
}
function passed_var($var, $from='both', $cast='string')	{
	set_default_null($cast, 'text');
	switch($from)	{
		case('both'):
			$var = $_GET[$var] != '' ? $_GET[$var]:$_POST[$var];
//						echo "vars: " . $var . "<br>";
			break;
		default:
		case('get'):
			if (isset($_GET[$var])) {
				$var = $_GET[$var];
			} else {
				$var = "";
			}
			break;
		case('post'):
			if (isset($_POST[$var])) {
				$var = $_POST[$var];
			} else {
				$var = "";
			}
			break;
	}
	
	$var = clean_html($var);
	settype($var, $cast);
	return $var;
}
function isValidEmail($email){

   $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
   $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
   $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
	   '\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
   $quoted_pair = '\\x5c\\x00-\\x7f';
   $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
   $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
   $domain_ref = $atom;
   $sub_domain = "($domain_ref|$domain_literal)";
   $word = "($atom|$quoted_string)";
   $domain = "$sub_domain(\\x2e$sub_domain)*";
   $local_part = "$word(\\x2e$word)*";
   $addr_spec = "$local_part\\x40$domain";

   return preg_match("!^$addr_spec$!", $email) ? 1 : 0;
} 
function isValidDate($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
	//echo $date . " - " . $d . "\r\n";
	//die();
    return $d && $d->format($format) == $date;
}
function pdfReplacement($place_holder, $replacement, &$string, &$arrReplace) {
	//spanish cleanup
	$string = str_replace("&ntilde;", "N", $string);
	$string = str_replace("ñ", "N", $string);
	
	$string = str_replace("&Ntilde;;", "N", $string);
	$string = str_replace("Ñ", "N", $string);
	
	//if (strlen($replacement)>0){
		$replacement = trim($replacement);
		if ($replacement!="On" && $replacement!="Off" && $replacement!="Yes" && $replacement!="No" && $place_holder!="DESTINATION") {
			$replacement = strtoupper($replacement);
		}
		$string = str_replace("$" . $place_holder . "$", $replacement, $string);
		
		$arrReplace[$place_holder] = $replacement;
}
function pdfReplacementJetFile($place_holder, $replacement, &$string, &$arrReplace) {
		$replacement = trim($replacement);
		if ($replacement!="On" && $replacement!="Off" && $replacement!="Yes" && $replacement!="No" && $place_holder!="DESTINATION") {
			$replacement = strtoupper($replacement);
		}
		$string = str_replace($place_holder, $replacement, $string);
		
		$arrReplace[$place_holder] = $replacement;
}
function DateAdd($interval, $number, $date) {

    $date_time_array = getdate($date);
	//die(print_r($date_time_array));
	
    $hours = $date_time_array["hours"];
    $minutes = $date_time_array["minutes"];
    $seconds = $date_time_array["seconds"];
    $month = $date_time_array["mon"];
    $day = $date_time_array["mday"];
    $year = $date_time_array["year"];

    switch ($interval) {
    
        case "yyyy":
            $year+=$number;
            break;
        case "q":
            $year+=($number*3);
            break;
        case "m":
            $month+=$number;
            break;
        case "y":
        case "d":
        case "w":
            $day+=$number;
            break;
        case "ww":
            $day+=($number*7);
            break;
        case "h":
            $hours+=$number;
            break;
        case "n":
            $minutes+=$number;
            break;
        case "s":
            $seconds+=$number; 
            break;            
    }
//		echo "day:" . $day;
       $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
    return $timestamp;
}
//////////////////////////////////////////////////////////////////////
//PARA: Date Should In YYYY-MM-DD Format
//RESULT FORMAT:
// '%y Year %m Month %d Day %h Hours %i Minute %s Seconds'        =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
// '%y Year %m Month %d Day'                                    =>  1 Year 3 Month 14 Days
// '%m Month %d Day'                                            =>  3 Month 14 Day
// '%d Day %h Hours'                                            =>  14 Day 11 Hours
// '%d Day'                                                        =>  14 Days
// '%h Hours %i Minute %s Seconds'                                =>  11 Hours 49 Minute 36 Seconds
// '%i Minute %s Seconds'                                        =>  49 Minute 36 Seconds
// '%h Hours                                                    =>  11 Hours
// '%a Days                                                        =>  468 Days
//////////////////////////////////////////////////////////////////////
function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' ) {
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
    
    $interval = date_diff($datetime1, $datetime2);
    //die("int:" . $interval);
    return $interval->format($differenceFormat);
    
}
function dateDiff($interval,$dateTimeBegin,$dateTimeEnd) {
 //Parse about any English textual datetime
 //$dateTimeBegin, $dateTimeEnd

 $dateTimeBegin=strtotime($dateTimeBegin);
 if($dateTimeBegin === -1) {
   return("..begin date Invalid");
 }

 $dateTimeEnd=strtotime($dateTimeEnd);
 if($dateTimeEnd === -1) {
   return("..end date Invalid");
 }

 $dif=$dateTimeEnd - $dateTimeBegin;
 switch($interval) {
   case "s"://seconds
	   return($dif);

   case "n"://minutes
	   return(floor($dif/60)); //60s=1m

   case "h"://hours
	   return(floor($dif/3600)); //3600s=1h

   case "d"://days
	   return(floor($dif/86400)); //86400s=1d

   case "ww"://Week
	   return(floor($dif/604800)); //604800s=1week=1semana

   case "m": //similar result "m" dateDiff Microsoft
	   $monthBegin=(date("Y",$dateTimeBegin)*12)+
		 date("n",$dateTimeBegin);
	   $monthEnd=(date("Y",$dateTimeEnd)*12)+
		 date("n",$dateTimeEnd);
	   $monthDiff=$monthEnd-$monthBegin;
	   return($monthDiff);

   case "yyyy": //similar result "yyyy" dateDiff Microsoft
	   return(date("Y",$dateTimeEnd) - date("Y",$dateTimeBegin));

   default:
	   return(floor($dif/86400)); //86400s=1d
 }

}
function days_diff($d1, $d2) {
	$d1 = date_create($d1);
	$d2 = date_create($d2);
	
    $x1 = days($d1);
    $x2 = days($d2);
    
    if ($x1 && $x2) {
        return $x1 - $x2;
    }
}
function days($x) {
    if (get_class($x) != 'DateTime') {
        return false;
    }
    
    $y = $x->format('Y') - 1;
    $days = $y * 365;
    $z = (int)($y / 4);
    $days += $z;
    $z = (int)($y / 100);
    $days -= $z;
    $z = (int)($y / 400);
    $days += $z;
    $days += $x->format('z');

    return $days;
}
function cleanWord($string) {
	$new_string = "";
	$mpos = strpos($string, "MCM");
	for ($i=0;$i<strlen($string);$i++) {
		$letter = substr($string, $i, 1);
		$blnSkip = false;
		if (ord($letter)=="146") {
			$letter = "&acute;";
			$blnSkip = true;
		}
		if (ord($letter)=="233") {
			$letter = "&eacute;";
			$blnSkip = true;
		}
		if (ord($letter)=="147" || ord($letter)=="148") {
			$letter = "&quot;";
			$blnSkip = true;
		}
		if (ord($letter)=="151") {
			$letter = "&#8211;";
			$blnSkip = true;
		}
		if ($blnSkip) {
			break;
		}
		
		if (ord($letter) > 127) {
			$letter = "&#0" . ord($letter) . ";";
		}
		//echo "Letter: " . $letter . "<BR>";
		//echo "Code: " . ord($letter) . "<BR><BR>";
		$new_string .= $letter;
	}
	if ($new_string!="") {
		$string = $new_string;
	}
	$string = str_replace("\r\n", "<BR>", $string);
	return $string;
	//die("here:<BR>". $string);
}

function format_date($year, $month, $day) {
    // pad single digit months/days with a leading zero for consistency (aesthetics)
    // and format the date as desired: YYYY-MM-DD by default
    if (strlen($month) == 1) {
        $month = "0". $month;
    }

    if (strlen($day) == 1) {
        $day = "0". $day;
    }
    $date = $year ."-". $month ."-". $day;
    return $date;
}

// the following function get_holiday() is based on the work done by
// Marcos J. Montes: http://www.smart.net/~mmontes/ushols.html

//

// if $week is not passed in, then we are checking for the last week of the month

function get_holiday($year, $month, $day_of_week, $week="") {
                //echo $year.", ".$month.", ".$day_of_week . " -><br>";
    if ( (($week != "") && (($week > 5) || ($week < 1))) || ($day_of_week >
6) || ($day_of_week < 0) ) {
        // $day_of_week must be between 0 and 6 (Sun=0, ... Sat=6); $week must be between 1 and 5
        return FALSE;
    } else {
        if (!$week || ($week == "")) {
            $lastday = date("t", mktime(0,0,0,$month,1,$year));
            $temp = (date("w",mktime(0,0,0,$month,$lastday,$year)) -
$day_of_week) % 7;
        } else {
            $temp = ($day_of_week - date("w",mktime(0,0,0,$month,1,$year)))
% 7;
        }

        if ($temp < 0) {
            $temp += 7;
        }

        if (!$week || ($week == "")) {
            $day = $lastday - $temp;
        } else {
            $day = (7 * $week) - 6 + $temp;
        }
		//echo $year.", ".$month.", ".$day ."<br><br>";
        return format_date($year, $month, $day);
    }
}

function observed_day($year, $month, $day) {
    // sat -> fri & sun -> mon, any exceptions?
    //
    // should check $lastday for bumping forward and $firstday for bumping back,
    // although New Year's & Easter look to be the only holidays that potentially
    // move to a different month, and both are accounted for.
                //echo "Year: " . $year . "<br>";
    $dow = date("w", mktime(0, 0, 0, $month, $day, $year));
    
    if ($dow == 0) {
        $dow = $day + 1;
    } elseif ($dow == 6) {
        if (($month == 1) && ($day == 1)) {    // New Year's on a Saturday
            $year--;
            $month = 12;
            $dow = 31;
        } else {
            $dow = $day - 1;
        }
    } else {
        $dow = $day;
    }
 
    return format_date($year, $month, $dow);
}

function calculate_easter($y) {
    // In the text below, 'intval($var1/$var2)' represents an integer division neglecting
    // the remainder, while % is division keeping only the remainder. 
    //So 30/7=4, and 30%7=2
    // This algorithm is from Practical Astronomy With Your Calculator, 2nd Edition by Peter
    // Duffett-Smith. It was originally from Butcher's Ecclesiastical Calendar, published in
    // 1876. This algorithm has also been published in the 1922 book General Astronomy by
    // Spencer Jones; in The Journal of the British Astronomical Association (Vol.88, page
    // 91, December 1977); and in Astronomical Algorithms (1991) by Jean Meeus. 
	
    $a = $y%19;
	$b = intval($y/100);
	$c = $y%100;
	$d = intval($b/4);
	$e = $b%4;
	$f = intval(($b+8)/25);
	$g = intval(($b-$f+1)/3);
	$h = (19*$a+$b-$d-$g+15)%30;
	$i = intval($c/4);
	$k = $c%4;
	$l = (32+2*$e+2*$i-$h-$k)%7;
	$m = intval(($a+11*$h+22*$l)/451);
	$p = ($h+$l-7*$m+114)%31;
	$EasterMonth = intval(($h+$l-7*$m+114)/31);    // [3 = March, 4 = April]
	$EasterDay = $p+1;    // (day in Easter Month)
	
	return format_date($y, $EasterMonth, $EasterDay);

}

 

/////////////////////////////////////////////////////////////////////////////
// end of calculation functions; place the dates you wish to calculate below
/////////////////////////////////////////////////////////////////////////////

 

function confirm_holiday($somedate="") {
	if ($somedate=="") {
		$somedate = date("Y-m-d");
	}

	$year = date("Y", strtotime($somedate));

	$blnHoliday = false;

	//newyears
	if ($somedate == observed_day($year, 1, 1)) {
		$blnHoliday = true;
	}

	if ($somedate == format_date($year, 1, 1)) {
		$blnHoliday = true;
	}

	if ($somedate == format_date($year, 12, 31)) {
		$blnHoliday = true;
	}

	//Martin Luther King
	if ($somedate == get_holiday($year, 1, 1, 3)) {
		$blnHoliday = true;
	}

	//President's
	if ($somedate == get_holiday($year, 2, 1, 3)) {
		$blnHoliday = true;
	}

	//easter
	if ($somedate == calculate_easter($year)) {
		$blnHoliday = true;
	}

	//Memorial
	if ($somedate == get_holiday($year, 5, 1)) {
		$blnHoliday = true;
	}

	//july4
	if ($somedate == observed_day($year, 7, 4)) {
		$blnHoliday = true;
	}

	//labor
	if ($somedate == get_holiday($year, 9, 1, 1)) {
		$blnHoliday = true;
	}

	//columbus
	if ($somedate == get_holiday($year, 10, 1, 2)) {
		$blnHoliday = true;
	}

	//thanks
	//die($somedate." == ".get_holiday($year, 11, 4, 4));
	if ($somedate == get_holiday($year, 11, 4, 4)) {
		$blnHoliday = true;
	}

	//xmas
	if ($somedate == format_date($year, 12, 24)) {
		$blnHoliday = true;
	}

	if ($somedate == format_date($year, 12, 25)) {
		$blnHoliday = true;
	}
	return $blnHoliday;
}
function firstAvailableDay($start_date) {
	$result_date  = $start_date;

	while (confirm_holiday(date("Y-m-d", strtotime($result_date)))) {
		$result_date = date("m", strtotime($result_date . "+ 1 days"))."/".date("d", strtotime($result_date . "+ 1 days"))."/".date("Y", strtotime($result_date . "+ 1 days"));
	}
	//die($result_date);
	//no weekends
	while (date("N", strtotime($result_date)) > 5) {
		$result_date = date("m", strtotime($result_date . "+ 1 days"))."/".date("d", strtotime($result_date . "+ 1 days"))."/".date("Y", strtotime($result_date . "+ 1 days"));
	} 
	
	$linux_date = date("Y-m-d", strtotime($result_date));
	$display_date = date("D M jS, Y", strtotime($result_date));
	$start_display_date = date("D M jS, Y", strtotime($result_date));
	
	$arrResults = array("calculated_date"=>$result_date, "linux_date"=>$linux_date, "display_date"=>$display_date, "start_date"=>$start_date, "start_display_date"=>$start_display_date);

	return $arrResults;
}
function url_exists($url) {
	//return false;
	$file_headers = @get_headers($url);
	if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
		return false;
	} else {
		return true;
	}
	
	//die("seek:" . $url);
    // Version 4.x supported
	/*
	$handle   = curl_init($url);
    if (false === $handle)
    {
        //die("nogo " . $url);
		return false;
    }
	
    curl_setopt($handle, CURLOPT_HEADER, false);
    curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
    curl_setopt($handle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") ); // request as if Firefox    
    curl_setopt($handle, CURLOPT_NOBODY, true);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);

    $connectable = curl_exec($handle);
	//echo $url . ": " . $connectable . "<BR>";
    curl_close($handle);   
    return $connectable;
	*/
}
function age($birthday){
	return intval(substr(date('Ymd') - date('Ymd', strtotime($birthday)), 0, -4));
}

function validateDate($date, $format = 'Y-m-d') {
    return ($date ."==". date($format, strtotime($date)));
}
function fiveOnly(&$string) {
	if (strlen($string)>5) {
		$string = substr($string, 0, 5);
	}
	return $string;
}
function noSpecialAtAll(&$string) {
	$string = str_replace("&", "and", $string);
	$string = ereg_replace("[^A-Za-z0-9]", " ", $string);
	return $string;
}
function noSpecial(&$string) {
	$string = str_replace("&", "and", $string);
	//allow period
	//$string = ereg_replace("[^A-Za-z0-9.]", " ", $string);
	$string = preg_replace('/[^A-Za-z0-9.]/', ' ', $string);
	return $string;
}
function base64_encode_pdf ($pdf_file) {
	$pdftype = array('pdf','fdf');
	$filename = file_exists($pdf_file) ? htmlentities($pdf_file) : die('file name does not exist:' . $pdf_file);
	$filetype = pathinfo($filename, PATHINFO_EXTENSION);
	if (in_array($filetype, $pdftype)){
		$pdfbinary = fread(fopen($filename, "r"), filesize($filename));
	} else {
		die ('Invalid file type, PDF/FDF only:' . $filename);
	}
	return base64_encode($pdfbinary);
}
function make_bitly_url($url) {
	$format = 'json';
	//create the URL
	$bitly = 'https://api-ssl.bitly.com/v3/shorten?access_token=32a1753df6a6a6ac67764772d9e3aabae50ae4f6&longUrl=' . urlencode($url);
	$response = get_data($bitly);
	//die("response:" . $response);
	//parse depending on desired format
	if(strtolower($format) == 'json') {
		$json = @json_decode($response,true);
		return $json['data']['url'];
	}
	else //xml
	{
		$xml = simplexml_load_string($response);
		return 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
	}
}
function get_data($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
?>