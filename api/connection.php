<?php
include(API_PATH.'chain_pack.php');

$blnDebug = false;

if (!isset( $_SESSION['user_customer_id'] )) {
	 $_SESSION['user_customer_id'] = -1;
	 $GLOBALS['GEN_DB_NAME'] = 'test_data6';
} else {
	if(isset($_GET['customer_id']) && $_GET['customer_id'] != '') {
		$db = getConnection();
		$query="SELECT import_db_source FROM `ikase`.cse_customer WHERE customer_id = '" . $_GET['customer_id'] . "'";
		$stmt = DB::run($query);
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if($res && $res['import_db_source'] != '') {
			$GLOBALS['GEN_DB_NAME'] = $res['import_db_source'];
		} else {
			$GLOBALS['GEN_DB_NAME'] = 'test_data6';
		}
	}
}
// Voice calling key (Plivo)
$auth_id = "MANGQYZMU3MWU4MZM2MT";
$auth_token = "MDFiMWQ1MjYwN2Q3N2Q1NDNiODE1ZDM3ZjNmNmRl";
$auth_phone = "+17472251107";
//$plivo_sms_number = "12132140456"; //turn off 7/10/17
$plivo_sms_number = "12132140442";

// Text messaging key (Nexmo) 
$api_key = "9bf77d58";
$api_secret = "9f3642052847f430";
//$from = "12133959868"; //turn off 7/10/17
//$from = "12034089735";	//turn off 8/25/2017
//$from = "12015471541";	//turn off 9/1/2017
//$from = "12015947026";	//turn off 11/9/2017
//$from = "12017628218";	//turn off 01/12/2018
//$from = "12088030810";	//turn off 03/15/2018
$from = "12017012123";
if (rand(0,1)==1) {
	//$from = "19892591833";	//turn off 11/9/2017
	//$from = "12017628226";	//turn off 01/12/2018
	//$from = "12088030812";	//turn off 03/15/2018
	$from = "12032661512";
}

//$blnDebug = ($_SERVER['REMOTE_ADDR']=='71.119.40.148' && $_SESSION['user_customer_id']==1033);	
//false;	
//($_SESSION['user_nickname']=="MA");
//($_SESSION['user_customer_id']=="1057");

DEFINE ("KASES_LIMIT", 2201);
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
			CAST(AES_DECRYPT(pers.`ein`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) COLLATE utf8_unicode_ci `ein`,
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
			$stmt = DB::run($query);
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
			$stmt = DB::run($query);
			
			//insert relationship
			$uuid = uniqid('KS') ;
			$last_updated_date = date("Y-m-d H:i:s");
			$query="insert into `cse_$tables` (`" . $table1 . "_uuid`,`" . $table2 . "_uuid`,`attribute`,`last_updated_date`, `last_update_user`, `customer_id`, `" . $table1 . "_" . $table2 . "_uuid`) 
			VALUES ('" . $table1_id . "','" . $table2_id . "','$attribute', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', " . $_SESSION['user_customer_id'] . ", '$uuid')";
			$stmt = DB::run($query);
		}
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
			$stmt = DB::run($query);
		}
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

/**
 * @deprecated avoid using it to run queries directly through PDO; use {@link DB} methods instead, they're much simpler.
 * @return PDO
 */
function getConnection($enableUserDataSource = true) {
    return DB::conn(DB::DB_LOCALHOST, $enableUserDataSource);
}

// created new getConnection_new & DB::conn_new function for batchscan, old getConnection function throwing Mysql server has gone away error
// old function working but batchscan issue was happen in only big pdfs(49pages, 15mb) so created new function
// created by jay on 7-march-2024
function getConnection_new($enableUserDataSource = true) {
    return DB::conn_new(DB::DB_LOCALHOST, $enableUserDataSource);
}

function getCustomerDocucentsAPIKey($cus_id) {
    $sql = "SELECT `customer_id`,`docucents_api_key`
	FROM `cse_customer`
	WHERE customer_id = :customer_id";
    try {
        $dbConn = getConnection(false);
        $stmt   = $dbConn->prepare($sql);
        $stmt->bindParam("customer_id", $cus_id);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        return $customer['docucents_api_key'];
    }
    catch (PDOException $e) {
        die("Error customer");
    }
}

function post_curl($url, $fields) {
    $fields_string = "";
		
	foreach($fields as $key=>$value) { 
		$fields_string .= $key . '=' . urlencode($value) . '&'; 
	}
	rtrim($fields_string, '&');

	$ch = curl_init();
	/*
	if (strpos($url, "combine") > -1) {
		echo $fields_string . "\r\n";
		die();
	}
	*/
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, strlen($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	
	$result = curl_exec($ch);
	curl_close($ch);
	
	//echo $fields_string;
	
	return $result;
}
function post_curl_object($url, $fields) {
	$ch = curl_init();
	//set the url, number of POST vars, POST json data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	
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
	$post_params = array();
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
	//die($url);
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
	if (!isset($_GET[$var]) && !isset($_POST[$var])) {
		return "";
	}
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
	/*
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		die("[" . $email .  "] not valid");
	  return 0;
	}
	return 1;
	*/
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
function lastWeekDays() {
	//$last_week_start = date("Y-m-d", strtotime("last week monday"));
	//$last_week_end = date("Y-m-d", strtotime("last sunday"));
	$last_monday_time = strtotime("last monday");
	$last_week_start = date("Y-m-d", $last_monday_time);
	$last_week_end  = mktime(0, 0, 0, date("m", $last_monday_time)  , date("d", $last_monday_time)+6, date("Y", $last_monday_time));
	$last_week_end = date("Y-m-d", $last_week_end);
	
	return array("last_week_start"=>$last_week_start, "last_week_end"=>$last_week_end);
}
function cleanWord($string, $debug = false) {
	$new_string = "";
	$mpos = strpos($string, "MCM");
	for ($i=0;$i<strlen($string);$i++) {
		$letter = substr($string, $i, 1);
		if (ord($letter)==0) {
			continue;
		}
		if ($debug) {
			echo "Letter: " . $letter . "<BR>";
			echo "Code: " . ord($letter) . "<BR><BR>";
		}
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
			$new_string .= $letter;
			break;
		}
		
		if (ord($letter) > 127) {
			$letter = "&#0" . ord($letter) . ";";
		}
		
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
//the start_date will be generated this way
//$start_date = mktime(0, 0, 0, date("m"),   date("d") + 35,   date("Y"));
//then the function will make sure it falls on a good day
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
    return ($date == date($format, strtotime($date)));
}
function fiveOnly(&$string) {
	if (strlen($string)>5) {
		$string = substr($string, 0, 5);
	}
	return $string;
}
function removeElementsByTagName($tagName, $document) {
  $nodeList = $document->getElementsByTagName($tagName);
  for ($nodeIdx = $nodeList->length; --$nodeIdx >= 0; ) {
    $node = $nodeList->item($nodeIdx);
    $node->parentNode->removeChild($node);
  }
}
function html2text($Document) {
    $Rules = array ('@<script[^>]*?>.*?</script>@si',
                    '@<[\/\!]*?[^<>]*?>@si',
                    '@([\r\n])[\s]+@',
                    '@&(quot|#34);@i',
                    '@&(amp|#38);@i',
                    '@&(lt|#60);@i',
                    '@&(gt|#62);@i',
                    '@&(nbsp|#160);@i',
                    '@&(iexcl|#161);@i',
                    '@&(cent|#162);@i',
                    '@&(pound|#163);@i',
                    '@&(copy|#169);@i',
                    '@&(reg|#174);@i',
                    '@&#(d+);@e'
             );
    $Replace = array ('',
                      '',
                      '',
                      '',
                      '&',
                      '<',
                      '>',
                      ' ',
                      chr(161),
                      chr(162),
                      chr(163),
                      chr(169),
                      chr(174),
                      'chr()'
                );
  return preg_replace($Rules, $Replace, $Document);
}
function noSpecialAtAll(&$string) {
	$string = str_replace("&", "and", $string);
	$string = preg_replace("/[^A-Za-z0-9]/", " ", $string);
	return $string;
}
function noSpecial(&$string) {
	$string = str_replace("&", "and", $string);
	$string = preg_replace('/[^A-Za-z0-9.]/', ' ', $string);
	return $string;
}
function noSpecialFilename($string) {
	$string = str_replace("&", "_", $string);
	$string = str_replace("\\", "|", $string);
	$string = preg_replace('/[^A-Za-z0-9._:|]/', '_', $string);
	$string = str_replace("|", "\\", $string);
	return $string;
}
function noAmpersand($string) {
	$string = str_replace("&", "&amp;", $string);
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
function make_gl_url($url) {
	$gl = "https://www.googleapis.com/urlshortener/v1/url?fields=analytics%2Ccreated%2Cid%2Ckind%2ClongUrl%2Cstatus&key=AIzaSyATlRmX2YtxkZc5FrUT9i74BZZGiesxkfU";
	$params = '{"longUrl": "' . $url . '"}';
	$result = post_curl_object($gl, $params);
	
	$arrData = json_decode($result);
	return $arrData->id;
}
function make_bitly_url($url) {
	//die($url);
	$format = 'json';
	//create the URL
	$bitly = 'https://api-ssl.bitly.com/v3/shorten?access_token=32a1753df6a6a6ac67764772d9e3aabae50ae4f6&longUrl=' . urlencode($url);
	
	//die($bitly);
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
	return file_get_contents($url);
}

function addEmail() {
	session_write_close();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$user_uuid = "";
	$email_name = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="email_port" || $fieldname=="outgoing_port") {
			if ($value==""){
				continue;
			}
		}
		if ($fieldname=="email_pwd") {
			if ($value==""){
				//ignore unless filled out
				continue;
			}
			$value = encryptAES($value);
		}
		if ($fieldname=="user_id") {
			$user_id = $value;
			$user = getUserInfo($user_id);
			$user_uuid = $user->user_uuid;
			continue;
		}
		if ($fieldname=="user_id" || $fieldname=="user_uuid" || $fieldname=="table_id" || $fieldname=="email_uuid") {
			continue;
		}
		if ($fieldname=="email_name") {
			$email_name = $value;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	if (!in_array("`email_pwd`", $arrFields)) {
		$arrFields[] = "`email_pwd`";
		$arrSet[] = "''";
	}
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die(print_r($arrFields));
	//die($sql);
	try { 
		
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		$user_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the applicant to the case 
		$sql = "INSERT INTO cse_user_" . $table_name . " (`user_" . $table_name . "_uuid`, `user_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $user_table_uuid  ."', '" . $user_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
	//echo $sql;die;
		try {
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		//track now
		trackEmail("insert", $new_id);
		
		if ($email_name!="") {
			//update the email address for the user based on this
			$sql = "UPDATE ikase.cse_user
			SET user_email = :user_email
			WHERE user_uuid = :user_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql); 
			$stmt->bindParam("user_email", $email_name); 
			$stmt->bindParam("user_id", $_SESSION["user_id"]); 
			$stmt->execute();
		}	
	} catch(PDOException $e) {	
		echo json_encode(array("error"=>$e->getMessage(), "sql"=>$sql));
		die();
		//echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	$token_name = "../email_module/".$_SESSION["user_id"].".txt";
	$fp = fopen($token_name, "w");
	fwrite($fp, $email_name);
	fclose($fp);
//print_r($token_name);
	$_SESSION['user_email'] = $email_name;	
}
function getEmailInfo($user_id) {
	session_write_close();
    $sql = "SELECT e.*, cue.attribute emails_number, cuser.user_id, cuser.user_uuid, cuser.nickname,
			e.email_id id, e.email_uuid uuid
			FROM `cse_email` e
			INNER JOIN cse_user_email cue
			ON e.email_uuid = cue.email_uuid
			INNER JOIN ikase.cse_user cuser
			ON (cue.user_uuid = cuser.user_uuid
			AND `cuser`.`user_id` = :user_id)
			WHERE 1
			AND cue.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cue.deleted = 'N'";
			//echo $user_id . "\r\n";
			//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->execute();
		//$email = $stmt->fetchObject();
		$email = $stmt->fetchAll();
		//die(print_r($email));
		return $email;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updateEmail() {
	session_write_close();
		
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$email_name = "";
	$encrypted_pwd  = "";
	$read_messages = "N";
	$emails_pending = "N";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="user_id" || $fieldname=="user_uuid" || $fieldname=="email_uuid") {
			continue;
		}
		if ($fieldname=="email_pwd") {
			if ($value==""){
				//ignore unless filled out
				continue;
			}
			$value = encryptAES($value);
			//continue;
		}
		if ($fieldname=="email_name") {
			$email_name = $value;
		}
		if ($fieldname=="read_messages") {
			$read_messages = $value;
		}
		if ($fieldname=="emails_pending") {
			$emails_pending = $value;
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	// echo $_SESSION['user_email']." = 1204";
	if ($read_messages == "N") {
		$arrSet[] = "`read_messages` = 'N'";
	}
	if ($emails_pending == "N") {
		$arrSet[] = "`emails_pending` = 'N'";
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];
		//die($sql . "\r\n");
	try {
		// echo $_SESSION['user_email']." = 1222";
		$user_plain_id1 =$_SESSION["user_plain_id"];
		$sql1 = "DELETE FROM ikase.cse_gmail WHERE user_id = ".$user_plain_id1."";
		$db1 = getConnection();
		$stmt1 = $db1->prepare($sql1); 
		$stmt1->execute();
        // die($user_plain_id);

		$stmt = DB::run($sql);
		trackEmail("update", $table_id);
		
		echo json_encode(array("success"=>$table_id, "pwd"=>$encrypted_pwd));
		
		if ($email_name!="") {
			//update the email address for the user based on this
			$sql = "UPDATE ikase.cse_user
			SET user_email = :user_email
			WHERE user_uuid = :user_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql); 
			$stmt->bindParam("user_email", $email_name); 
			$stmt->bindParam("user_id", $_SESSION["user_id"]); 
			$stmt->execute();
			
			$db = null;
		}		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	// echo $_SESSION['user_email']." = 1253";
	$token_name = "../email_module/".$_SESSION["user_id"].".txt";
	$fp = fopen($token_name, "w");
	fwrite($fp, $email_name);
	fclose($fp);
//print_r($token_name);
	$_SESSION['user_email'] = $email_name;
	// echo $_SESSION['user_id']." = 1255";
//die();
}
function trackEmail($operation, $email_id) {
	session_write_close();
	$sql = "INSERT INTO cse_email_track (`user_uuid`, `user_logon`, `operation`, `email_id`, `email_uuid`, `email_name`, `email_server`, `email_pwd`, `email_address`, `email_phone`, `cell_carrier`, `customer_id`, `active`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `email_id`, `email_uuid`, `email_name`, `email_server`, `email_pwd`, `email_address`, `email_phone`, `cell_carrier`, `customer_id`, `active`, `deleted`
	FROM cse_email
	WHERE 1
	AND email_id = " . $email_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$stmt = DB::run($sql);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function checkWCAB($case_type) {
	
	$blnWCAB = ((strpos($case_type, "Worker") > -1) || (strpos($case_type, "WC") > -1) || (strpos($case_type, "W/C") > -1) || $_SESSION["user_customer_type"]=="Medical Office");
	
	return $blnWCAB;
}
function execInBackground($cmd) { 
    if (substr(php_uname(), 0, 7) == "Windows"){ 
        pclose(popen("start /B ". $cmd, "r"));  
    } 
    else { 
        exec($cmd . " > /dev/null &");   
    } 
}
function is_decimal( $val ) {
    return is_numeric( $val ) && floor( $val ) != $val;
}
function strip_word_html($text, $allowed_tags = '<a><ul><li><b><i><sup><sub><em><strong><u><br><br/><br /><p><h2><h3><h4><h5><h6>')
{
    mb_regex_encoding('UTF-8');
    //replace MS special characters first
    $search = array('/&lsquo;/u', '/&rsquo;/u', '/&ldquo;/u', '/&rdquo;/u', '/&mdash;/u');
    $replace = array('\'', '\'', '"', '"', '-');
    $text = preg_replace($search, $replace, $text);
    //make sure _all_ html entities are converted to the plain ascii equivalents - it appears
    //in some MS headers, some html entities are encoded and some aren't
    //$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    //try to strip out any C style comments first, since these, embedded in html comments, seem to
    //prevent strip_tags from removing html comments (MS Word introduced combination)
    if(mb_stripos($text, '/*') !== FALSE){
        $text = mb_eregi_replace('#/\*.*?\*/#s', '', $text, 'm');
    }
    //introduce a space into any arithmetic expressions that could be caught by strip_tags so that they won't be
    //'<1' becomes '< 1'(note: somewhat application specific)
    $text = preg_replace(array('/<([0-9]+)/'), array('< $1'), $text);
    $text = strip_tags($text, $allowed_tags);
    //eliminate extraneous whitespace from start and end of line, or anywhere there are two or more spaces, convert it to one
    $text = preg_replace(array('/^\s\s+/', '/\s\s+$/', '/\s\s+/u'), array('', '', ' '), $text);
    //strip out inline css and simplify style tags
    $search = array('#<(strong|b)[^>]*>(.*?)</(strong|b)>#isu', '#<(em|i)[^>]*>(.*?)</(em|i)>#isu', '#<u[^>]*>(.*?)</u>#isu');
    $replace = array('<b>$2</b>', '<i>$2</i>', '<u>$1</u>');
    $text = preg_replace($search, $replace, $text);
    //on some of the ?newer MS Word exports, where you get conditionals of the form 'if gte mso 9', etc., it appears
    //that whatever is in one of the html comments prevents strip_tags from eradicating the html comment that contains
    //some MS Style Definitions - this last bit gets rid of any leftover comments */
    $num_matches = preg_match_all("/\<!--/u", $text, $matches);
    if($num_matches){
        $text = preg_replace('/\<!--(.)*--\>/isu', '', $text);
    }
    $text = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $text);
return $text;
}
function truncateWords($input, $numwords, $padding="") {
	$output = strip_word_html($input);
	$output = strtok($output, " \n");
	while(--$numwords > 0) {
		$output .= " " . strtok(" \n");
	}
	if($output != $input) {
		$output .= $padding;
	}
	
	//$output = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $output);
	$output = preg_replace("/(<[^>]+) style='.*?'/i", "$1", $output);
	//die($output);
	return $output;
}
function myTruncate($string, $limit, $break=".", $pad="...") {
  // return with no change if string is shorter than $limit
  if(strlen($string) <= $limit) return $string;

  // is $break present between $limit and the end of the string?
  if(false !== ($breakpoint = strpos($string, $break, $limit))) {
    if($breakpoint < strlen($string) - 1) {
      $string = substr($string, 0, $breakpoint) . $pad;
    }
  }

  return $string;
}
function restoreTags($input) {
	$opened = array();
	
	// loop through opened and closed tags in order
	if(preg_match_all("/<(\/?[a-z]+)>?/i", $input, $matches)) {
	  foreach($matches[1] as $tag) {
		if(preg_match("/^[a-z]+$/i", $tag, $regs)) {
		  // a tag has been opened
		  if(strtolower($regs[0]) != 'br') $opened[] = $regs[0];
		} elseif(preg_match("/^\/([a-z]+)$/i", $tag, $regs)) {
			//echo array_pop(array_keys($opened, $regs[1])) . "\r\n";
			//print_r($regs);
			//die();
		  // a tag has been closed
		  //die(print_r(array_keys($opened, $regs[1])));
		 //unset($opened[array_pop(array_keys($opened, $regs[1]))]);
		}
	  }
	}
	
	// close tags that are still open
	if($opened) {
	  $tagstoclose = array_reverse($opened);
	  foreach($tagstoclose as $tag) $input .= "</$tag>";
	}
	//die($input);
	return $input;
}
function getSpanishMonth($month) {
	switch($month) {
		case "January":
			$month = "enero";
			break;
		case "February":
			$month = "febrero";
			break;
		case "March":
			$month = "marzo";
			break;
		case "April":
			$month = "abril";
			break;
		case "May":
			$month = "mayo";
			break;
		case "June":
			$month = "junio";
			break;
		case "July":
			$month = "julio";
			break;
		case "August":
			$month = "agosto";
			break;
		case "September":
			$month = "septiembre";
			break;
		case "October":
			$month = "octubre";
			break;
		case "November":
			$month = "noviembre";
			break;
		case "December":
			$month = "diciembre";
			break;
	}
	return $month;
}
function findDocumentFolder($customer_id, $case_id, $file, $type, $thumbnail_folder, $document_id) {
	$file = urldecode($file);
	$arrFileInfo = explode(".", $file);
	
	$blnPDF = (strtolower($arrFileInfo[count($arrFileInfo) - 1]) == "pdf");
	$blnJPG = (strtolower($arrFileInfo[count($arrFileInfo) - 1]) == "jpg" || strtolower($arrFileInfo[count($arrFileInfo) - 1]) == "png");
	$blnDocx = (strtolower($arrFileInfo[count($arrFileInfo) - 1]) == "docx");
	$blnSound = (strtolower($arrFileInfo[count($arrFileInfo) - 1]) == "wma" || strtolower($arrFileInfo[count($arrFileInfo) - 1]) == "mp3");
	
	if (!$blnPDF && !$blnJPG && !$blnDocx && !$blnSound) {
		$extension = "docx";
		$file .= "." . $extension;
	}
	//https://www.localhost/uploads/1042/6843/letters/1002%201ST%20THANK%20YOU%20LTR%20(1)_6843_0.docx
	
	$path = "../uploads/" . $customer_id . "/" . $case_id . "/" .  $file;
	//die($path);
	if (file_exists($path)) {
		return $path;
		die();
	}
	
	//does it have an extension
	
	if ($type=="jetfile" || $type=="DOR" || $type=="DORE" || $type=="LIEN") {
		
		$path = "../uploads/" . $customer_id . "/" . $case_id . "/jetfiler/" .  $filename;
	}
	if ($type=="eams_form") {
		$path = "../uploads/" . $customer_id . "/" . $case_id . "/eams_forms/" .  $file;
	}
	if (is_numeric($thumbnail_folder) && $extension!="docx" && $thumbnail_folder!="") {
		$path = "../uploads/" . $customer_id . "/imports/" . $file;
	}
	if ($type == "abacus") {
		//FIXME: non-existing variable
		$path = "https://www.ikase.xyz/ikase/abacus/" . $customer_data_source . "/" . $thumbnail_folder . "/" . $file;
	}
	
	if (strpos($file, "iKase.org\\scans") > -1) {
		$arrFile = explode("\\", $file);
		$filename = $arrFile[count($arrFile) - 1];
		$path = "../scans/" . $customer_id . "/" . $thumbnail_folder . "/imports/" . $filename;
		//die($path);
	}
	
	if (file_exists($path)) {
		return $path;
		die();
	} else {
		//probably on the main customer folder
		$path = "../uploads/" . $customer_id . "/" .  $file;
		if (file_exists($path)) {
			return $path;
			die();
		} 
		//maybe it's a jetfile
		$path = "../uploads/" . $customer_id . "/" . $case_id . "/jetfiler/" .  $file;
		if (file_exists($path)) {
			return $path;
			die();
		} else {
			//might be a jetfiler form?
			$path = "../uploads/" . $customer_id . "/" . $case_id . "/eams_forms/" .  $file;
			if (file_exists($path)) {
				return $path;
				die();
			} else {
				//try for batchscan3
				$path = "../uploads/" . $customer_id . "/" . $case_id . "/" . $file;
				if (file_exists($path)) {
					return $path;
					die();
				} else {
					//try for batchscan3
					/*
					$sql = "SELECT doc.`document_id`, doc.`document_uuid`, 
							doc.`parent_document_uuid`, doc.`document_name`, `document_date`, 
						doc.`received_date`, doc.`source`,
						doc.`document_filename`, doc.`document_extension`, doc.`thumbnail_folder`, 
						doc.`description`, `description_html`, 
						doc.`type`, doc.`verified`, doc.`deleted`, doc.`document_id` id, 
						doc.`document_uuid` uuid, doc.customer_id,
						IFNULL(ccase.case_id, '') case_id
						FROM `cse_document` doc
						LEFT OUTER JOIN `cse_case_document` ccd
						ON doc.document_uuid = ccd.document_uuid AND ccd.deleted = 'N'
						LEFT OUTER JOIN `cse_case` ccase
						ON ccd.case_uuid = ccase.case_uuid
						WHERE 1 ";
					$sql .= " AND document_id=:id";
					$sql .= " AND doc.customer_id = " . $_SESSION['user_customer_id'];
						
					//die($sql);
					try {
						$db = getConnection();
						$stmt = $db->prepare($sql);
						$stmt->bindParam("id", $document_id);
						$stmt->execute();
						$document = $stmt->fetchObject();
					} catch(PDOException $e) {
						$error = array("error"=> array("text"=>$e->getMessage()));
						echo json_encode($error);
					}
					//die(print_r($document));
					
					$document_date = $document->document_date;
					$date = date("Ymd", strtotime($document_date));
					
					$path = "../scans/" . $customer_id . "/" . $date . "/" . $file;
					*/
					
					$path = "../uploads/" . $customer_id . "/imports/" . $thumbnail_folder . "/" . $file;
					
					//die($path);
					if (file_exists($path)) {
						return $path;
						die();
					} else {
						//try for letters
						$path = "../uploads/" . $customer_id . "/" . $case_id . "/letters/" . $file;
						//echo $path . "<br />";
						//die();
						if (file_exists($path)) {
							return $path;
							die();
						}
					}
				}
			}
		}
	}
	
	//garbage at the bottom...
	return false;
}
function findDocumentThumbnail($customer_id, $case_id, $document) {
	
	//gather the stuff you need
	$filename = $document->document_filename;
	$type = $document->type;
	$document_type = $type;
	$document_date = $document->document_date;
	$thumbnail_folder = $document->thumbnail_folder;
	$parent_document_uuid = $document->parent_document_uuid;
	
	//ez first
	if ($thumbnail_folder!="") {
		$filepath = str_replace(".pdf", ".jpg", $filename);
		$filepath = str_replace(".PDF", ".jpg", $filepath);
		$path = "../uploads/" . $customer_id . "/" . $thumbnail_folder . "/" . $filepath;
		
		if (file_exists($path)) {
			return $path;
		} 
	}
	//fix the date if need be
	$arrDateTime = explode(" ", $document_date);
	$arrDateElements = explode("/", $arrDateTime[0]);
	if (strlen($arrDateElements[2]) == 2) {
		$arrDateElements[2] = "20" . $arrDateElements[2];
	}
	$new_date = implode("/", $arrDateElements) . " " . $arrDateTime[1];
	if (count($arrDateTime) == 3) {
		$new_date .= " "  . $arrDateTime[2];
	}
	$document_date = $new_date;
	
	$preview = "uploads/" . $customer_id . "/";
	
	
	if ($thumbnail_folder=="0" || strpos($thumbnail_folder, "pdfimage")!==false) {
		//echo "pdf";
		$preview = "pdfimage/" . $customer_id . "/";
		$arrFileName = explode(".", $filename);
		$extension = $arrFileName[count($arrFileName) - 1];
		$new_extension = $extension;
		if ($extension=="pdf" || $extension=="PDF" || $extension=="tif" || $extension=="TIF") {
			$new_extension = "jpg";
		}
		array_pop($arrFileName);
		$filename = implode(".", $arrFileName) . "." . $new_extension;
		$thumbnail_folder = "";
	}
	
	if ($thumbnail_folder!="") {
		if (strpos($filename, "_") !== false && strpos($thumbnail_folder, "/") === false && !is_numeric($thumbnail_folder)) {
			$arrFileName = explode(".", $filename);
			$first_page = $arrFileName[count($arrFileName) - 2] - 1;
			//get rid of last 2 members of array
			array_pop($arrFileName);
			array_pop($arrFileName);
			$filename = implode("_", $arrFileName) . "_" . $first_page . ".png";
		}
		if (strpos($thumbnail_folder, "/")!== false) {
			$arrFileName = explode(".", $filename);
			$extension = $arrFileName[count($arrFileName) - 1];
			$new_extension = $extension;
			if ($extension=="pdf" || $extension=="PDF" || $extension=="tif" || $extension=="TIF") {
				$new_extension = "jpg";
			}
			array_pop($arrFileName);
			$filename = implode(".", $arrFileName) . "." . $new_extension;
		}
		
		$preview .= $thumbnail_folder . "/";
		
		//batch scans are imported
		if (is_numeric($thumbnail_folder) && $thumbnail_folder!="") {
			$arrFileName = explode("_", $filename);
			$first_page = $arrFileName[count($arrFileName) - 2];
			
			$d1 = strtotime($document_date);
			$d2 = strtotime("2018-02-05 08:00 PM");
			
			if ($d1 > $d2) {
				//one less
				$first_page =  $first_page - 1;
			}
			//get rid of last 2 members of array
			array_pop($arrFileName);
			array_pop($arrFileName);
			$filename = implode("_", $arrFileName) . "-" . $first_page . ".png";
			$preview = "uploads/" . $customer_id . "/imports/" . $thumbnail_folder . "/";	
		}
		//identify batshcanned files
		if ($parent_document_uuid!="") {
			if (is_numeric($parent_document_uuid)) {
				//that's the original batchid
				//after jan 27 2017
				//$d1 = new Date(moment(document_date.split(" ")[0]).format("YYYY-MM-DD"));

				$d1 = strtotime($document_date);
				$d2 = strtotime("2018-01-27");
			
				if ($d1 > $d2) {
					$document_type = "batchscan3";
				}
			}
		}
		if ($document_type=="batchscan3") {
			$arrFileBreak = explode(".", $filename);
			$extension = $arrFileBreak[count($arrFileBreak) - 1];
			
			
			if ($extension!="png" && $extension!="jpg") {
				$arrFileName = explode("_", $filename);
				if (count($arrFileName) > 1) {
					$first_page = $arrFileName[count($arrFileName) - 2];
					//$d1 = new Date(moment(document_date).format("YYYY-MM-DD"));
					$d1 = strtotime($document_date);
					$d2 = strtotime("2018-02-05 08:00 PM");
					
					if ($d1 > $d2) {
						//one less
						$first_page =  $first_page - 1;
					}
					//get rid of last 2 members of array
					array_pop($arrFileName);
					array_pop($arrFileName);
					$filename = implode("_", $arrFileName) . "-" . $first_page . ".png";
				}
			}
			$filename = str_replace(".png", ".jpg", $filename);
			$preview = "scans/" . $customer_id . "/" . date("Ymd", strtotime($document_date)) . "/";
		}
	}
	$preview .= $filename;
	
	//it has to jpg/png
	$arrPreview = explode(".", $preview);
	$extension = $arrPreview[count($arrPreview) - 1];
	$extension = strtolower($extension);
	if ($extension!="jpg" && $extension!="png") {
		$preview = "img/no_preview.gif";
	}
	/*
	if ($document->document_id==66430) {
		die($preview);
		//
	}
	*/
	return $preview;
}
function create_zip($files = array(),$destination = '',$overwrite = false) {
	//if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) { return false; }
	//vars
	$valid_files = array();
	
	
	//if files were passed in...
	if(is_array($files)) {
		//die(print_r($files));
		//cycle through each file
		foreach($files as $file) {
			//replace the url
			$file = str_replace("https://www.localhost", "..", $file);
			//make sure the file exists
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}
	//die(print_r($valid_files));
	//if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		//die($destination);
		if($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			//die("no open");
			return false;
		}
		//add the files
		foreach($valid_files as $file) {
			//die(print_r($valid_files));
			$arrTemp = explode("/", $file);
			$newname = $arrTemp[count($arrTemp) - 1];
			//die($newname);
			$zip->addFile($file, $newname);
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		
		//close the zip -- done!
		$zip->close();
		//die("did open");
		//check to make sure the file exists
		return file_exists($destination);
	}
	else
	{
		return false;
	}
}
function convertNumberToWord($num = false)
{
    $num = str_replace(array(',', ' '), '' , trim($num));
    if(! $num) {
        return false;
    }
    $num = (int) $num;
    $words = array();
    $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
        'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
    );
    $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
    $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
        'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
        'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
    );
    $num_length = strlen($num);
    $levels = (int) (($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00' . $num, -$max_length);
    $num_levels = str_split($num, 3);
    for ($i = 0; $i < count($num_levels); $i++) {
        $levels--;
        $hundreds = (int) ($num_levels[$i] / 100);
        $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
        $tens = (int) ($num_levels[$i] % 100);
        $singles = '';
        if ( $tens < 20 ) {
            $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
        } else {
            $tens = (int)($tens / 10);
            $tens = ' ' . $list2[$tens] . ' ';
            $singles = (int) ($num_levels[$i] % 10);
            $singles = ' ' . $list1[$singles] . ' ';
        }
        $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
    } //end for loop
    $commas = count($words);
    if ($commas > 1) {
        $commas = $commas - 1;
    }
    return implode(' ', $words);
}
function setCityStreet(&$partie) {
	if ($partie->street=="" && $partie->full_address!="") {
		$arrAddress = explode(",", $partie->full_address);
			

		if (count($arrAddress) > 2) {
			//check for city zip
			$city_zip = trim($arrAddress[count($arrAddress)- 1]);
			$arrStateZip = explode(" ", $city_zip);
			
			if (count($arrStateZip) > 1 && is_numeric($arrStateZip[count($arrStateZip) - 1])) {
				//we have a zip
				$partie->zip = trim($arrStateZip[count($arrStateZip) - 1]);
				//remove the zip
				$partie->state = trim($arrStateZip[0]);
				$partie->city = trim($arrAddress[count($arrAddress)- 2]);
				unset($arrAddress[count($arrAddress)- 1]);
				unset($arrAddress[count($arrAddress)- 1]);
				$partie->street = implode(", ", $arrAddress);
				$partie->suite = "";
			}
		}
	}
}
function capWords($string) {
	$string = str_replace("-", " - ", $string);
	$string = ucwords(strtolower($string));
	$string = str_replace(" - ", "-", $string);
	
	return $string;
}
