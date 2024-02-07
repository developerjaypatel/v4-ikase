<?php
// $auth_id = "MAOTIWMDI0YWQ4NTCXMM";
// $auth_token = "MzJkYWVlZGM2ZGJjOTY1MmU0ZGY5M2M2Zjc1NDgz";
// $auth_phone = "+13309462235";	

$auth_id = "MAMDMYODIZNWNLMZI4ZT";
$auth_token = "ZTEzNmRhNDU2OTg4YTVkMTMwNGQxZDRmNDVlMjEz";
$auth_phone = "+17472251107";

$neustar_server = 'download.targusinfo.com';
$neustar_username = 'realcontact';
$neustar_pwd = 'Gv663pE@';
$arrEnglishEquivalent_definition = array("gather" => "recipient answered the call and gave a response",
                             "hangup" => "recipient hung up after pressing 2 or 3",
                             "unsubscribe_sms" => "recipient unsubscribed from the system using sms",
                             "end_of_conversation" => "Customer heard the entire message",
                             "authorize_capture" => "Indicates the Credit Card used is approved", 
                             "authorize_capture_sms" => "Indicates the Credit Card used is approved through SMS", 
                             "payment_form_opened" => "Indicates the recipient opened the payment page", 
                             "payment_form_opened_sms" => "Indicates the recipient opened the payment page through SMS", 
                             "payment_plan_form_opened" => "Indicates the recipient opened the payment plan page", 
                             "payment_plan_form_opened_sms" => "Indicates the recipient opened the payment plan page through SMS",
                             "recurr" => "Indicates the recipient is approved to make payment plans",
                             "recurr_sms" => "Indicates the recipient is approved to make payment plans through SMS",
                             "zip_verification" => "The recipient has correctly inputted their zip code for authorization",  
                             "zip_sms" => "The recipient has correctly inputted their zip code for authorization through SMS",
                             "unsubscribe_email" => "The recipient has unsubscribed through email link",
                             "machine" => "the number of times the machine answered the call",
                             "cellphone" => "Indicates the number of attempts to the contact method",
                             "phone" => "Indicates the number of attempts to the contact method",
                             "sms" => "Indicates the number of attempts to the contact method",
                             "email" => "Indicates the number of attempts to the contact method",
                             "mail" => "Indicates the number of attempts to the contact method",
                             "voicemail" => "Indicates the number of attempts to the contact method"
                             );
$arrEnglishEquivalent = array("gather" => "Active Responses",
                             "hangup" => "Hangups",
                             "unsubscribe_sms" => "SMS Unsubscribed",
                             "end_of_conversation" => "Complete message heard",
                             "authorize_capture" => "Credit Card approved",
                             "authorize_capture_sms" => "Credit Card approved by SMS", 
                             "payment_form_opened" => "Payment Page opened",
                             "payment_form_opened_sms" => "Payment Page opened by SMS", 
                             "payment_plan_form_opened" => "Payment Plan Page opened",
                             "payment_plan_form_opened_sms" => "Payment Plan Page opened by SMS", 
                             "recurr" => "Approved payment plans",
                             "recurr_sms" => "Approved payment plans by SMS", 
                             "zip_verification" => "Zip code authorization",
                             "zip_sms" => "Zip code authoriztion by SMS",  
                             "unsubscribe_email" => "Unsubscribed by email link",
                             "machine" => "Machine answered",
                             "cellphone" => "Cellphone attempts",
                             "phone" => "Phone attempts",
                             "sms" => "SMS attempts",
                             "email" => "Email attempts",
                             "mail" => "Mail attempts",
                             "voicemail" => "Voicemail attempts"
);

if (isset($_SESSION["user_customer_id"])) {
	if ($_SESSION["user_customer_id"]==7) {
		DEFINE ("DOCTORS_FILTER", "(
				IFNULL(eve.judge, '') LIKE '%Anel%' 
				OR IFNULL(eve.judge, '') LIKE '%Harris%'
				OR IFNULL(eve.judge, '') LIKE '%Balian%'
				OR IFNULL(eve.judge, '') LIKE '%Tien%'
				OR IFNULL(eve.judge, '') LIKE '%Daneshrad%'
				OR IFNULL(eve.judge, '') LIKE '%Doan%'
				OR IFNULL(eve.judge, '') LIKE '%Russman%'
				OR IFNULL(eve.judge, '') LIKE '%Signorelli%'
				OR IFNULL(eve.judge, '') LIKE '%Kohan%'
				OR IFNULL(eve.judge, '') LIKE '%Hooman%'
				OR IFNULL(eve.judge, '') LIKE '%Hinze%'
				)");
	} else {
		DEFINE ("DOCTORS_FILTER", "1");
	}
} else {
	DEFINE ("DOCTORS_FILTER", "1");
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
	
	return $value;
}
function getConnection() {
	$prefix = "";
	
	$dbhost="ikase.org";
	$dbuser= "root"; // "_dripper"; dev
	
	$dbpass="admin527#";
	//$dbpass="Dev10!";
	$dbname = "md_reminder";   //"_collections"; developer
	
	//die("mysql:host=$dbhost;dbname=$dbname: " . $dbuser . ", " . $dbpass);	
	//The USPS NCOA Processing Acknowledgement Form (PAF) has been registered and your trial license key has been enabled: WS64-XVS2-CZJ4
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
			$var = $_GET[$var];
			break;
		case('post'):
			$var = $_POST[$var];
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
function invoiceText($invoiced) {
	if ($invoiced=="") {
		return $invoiced;
	}
	$arrInvoived = explode(".", $invoiced);
	
	if($arrInvoived[1] == "00"){
		$invoiced = $arrInvoived[0] . " dollars";
	} else {
		//replace . with dollars and, and then add the word cents
		$invoiced = str_replace(".", " dollars and ", $invoiced) . " cents";
	}
	return $invoiced;
}
function cleanPhoneNumber($phone_number) {
	$phone_number = preg_replace("/[^0-9]/","",$phone_number);
	
	return $phone_number;
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
function make_bitly_url($url) {
	$format = 'json';
	//create the URL
	$bitly = 'https://api-ssl.bitly.com/v3/shorten?access_token=32a1753df6a6a6ac67764772d9e3aabae50ae4f6&longUrl=' . urlencode($url);
	$response = get_data($bitly);
	
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
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') == $date;
}

function checkURL($url) {
	$handle = curl_init($url);
	curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
	
	/* Get the HTML or whatever is linked in $url. */
	$response = curl_exec($handle);
	
	/* Check for 404 (file not found). */
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	$return = "true";
	if($httpCode == 404) {
		/* Handle 404 here. */
		$return = "false";
	}
	
	curl_close($handle);
	
	return $return;
}
/*
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad'); 
$blnIPad = isPad();
$blnMobile = (isMobile() && !$blnIPad);
*/
function isPad(){
    return preg_match("/iPad;/i", $_SERVER["HTTP_USER_AGENT"]);
}
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
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
}
?>