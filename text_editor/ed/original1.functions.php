<?php
function getPDOConnection() {
	$dbhost="ikase.website";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	if (isset($_SESSION['user_data_source'])){
		if ($_SESSION['user_data_source']!="") {
			$dbname .= "_" . $_SESSION['user_data_source'];
		}
	}
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
	/*	
	if (isset($_SERVER['DOCUMENT_ROOT'])) {
		if ($_SERVER['DOCUMENT_ROOT']=="C:\\inetpub\\wwwroot\\ikase.org") {
			//$dbhost="54.149.211.191";
			//$dbhost="52.34.166.217";
			$dbhost="ikase.org";
			$dbuser="root";
			$dbpass="admin527#";
			$dbname="ikase";
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
	*/
}
function age($birthday){
	return intval(substr(date('Ymd') - date('Ymd', strtotime($birthday)), 0, -4));
}
function validateDate($date, $format = 'Y-m-d')
{
    return ($date ."==". date($format, strtotime($date)));
	/*
	$d = DateTime::createFromFormat($format, $date);
	
	die("dd:" . $d);
    return $d && $d->format($format) == $date;
	*/
}
function isPad(){

    return preg_match("/iPad;/i", $_SERVER["HTTP_USER_AGENT"]);

}
function isMobile() {

    return
preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hipt
op|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
$_SERVER["HTTP_USER_AGENT"]);

}
/*============================================================================*/

// Parameters:
// $text = The text that you want to encrypt.
// $key = The key you're using to encrypt.
// $alg = The algorithm.
// $crypt = 1 if you want to crypt, or 0 if you want to decrypt. 
function cryptare($text, $key, $alg, $crypt)
{
    $encrypted_data="";
    switch($alg)
    {
        case "3des":
            $td = mcrypt_module_open('tripledes', '', 'ecb', '');
            break;
        case "cast-128":
            $td = mcrypt_module_open('cast-128', '', 'ecb', '');
            break;   
        case "gost":
            $td = mcrypt_module_open('gost', '', 'ecb', '');
            break;   
        case "rijndael-128":
            $td = mcrypt_module_open('rijndael-128', '', 'ecb', '');
            break;       
        case "twofish":
            $td = mcrypt_module_open('twofish', '', 'ecb', '');
            break;   
        case "arcfour":
            $td = mcrypt_module_open('arcfour', '', 'ecb', '');
            break;
        case "cast-256":
            $td = mcrypt_module_open('cast-256', '', 'ecb', '');
            break;   
        case "loki97":
            $td = mcrypt_module_open('loki97', '', 'ecb', '');
            break;       
        case "rijndael-192":
            $td = mcrypt_module_open('rijndael-192', '', 'ecb', '');
            break;
        case "saferplus":
            $td = mcrypt_module_open('saferplus', '', 'ecb', '');
            break;
        case "wake":
            $td = mcrypt_module_open('wake', '', 'ecb', '');
            break;
        case "blowfish-compat":
            $td = mcrypt_module_open('blowfish-compat', '', 'ecb', '');
            break;
        case "des":
            $td = mcrypt_module_open('des', '', 'ecb', '');
            break;
        case "rijndael-256":
            $td = mcrypt_module_open('rijndael-256', '', 'ecb', '');
            break;
        case "xtea":
            $td = mcrypt_module_open('xtea', '', 'ecb', '');
            break;
        case "enigma":
            $td = mcrypt_module_open('enigma', '', 'ecb', '');
            break;
        case "rc2":
            $td = mcrypt_module_open('rc2', '', 'ecb', '');
            break;   
        default:
            $td = mcrypt_module_open('blowfish', '', 'ecb', '');
            break;                                           
    }
   
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    $key = substr($key, 0, mcrypt_enc_get_key_size($td));
    mcrypt_generic_init($td, $key, $iv);
   
    if($crypt)
    {
        $encrypted_data = mcrypt_generic($td, $text);
    }
    else
    {
        $encrypted_data = mdecrypt_generic($td, $text);
    }
   
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
   
    return $encrypted_data;
} 
function encrypt($v1,$v2=''){
    $token = md5(sha1(md5(base64_decode($v1.$v2)).$v2).$v1);
    return $token;
}
function mysql_aes_decrypt($val,$ky) 
{ 
    
	$key="\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"; 
    for($a=0;$a<strlen($ky);$a++) 
      $key[$a%16]=chr(ord($key[$a%16]) ^ ord($ky[$a])); 
    $mode = MCRYPT_MODE_ECB; 
    $enc = MCRYPT_RIJNDAEL_128; 
    $dec = @mcrypt_decrypt($enc, $key, $val, $mode, @mcrypt_create_iv( @mcrypt_get_iv_size($enc, $mode), MCRYPT_DEV_URANDOM ) ); 
	//die("dec:" . $dec);
    $return = rtrim($dec,(( ord(substr($dec,strlen($dec)-1,1))>=0 and ord(substr($dec, strlen($dec)-1,1))<=16)? chr(ord( substr($dec,strlen($dec)-1,1))):null)); 
	
	//echo $val .  "] " . $ky . "<br />[" . $return . "]<br />";
	
	return $return;
} 

function mysql_aes_encrypt($val,$ky) 
{ 
    $key="\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"; 
    for($a=0;$a<strlen($ky);$a++) 
      $key[$a%16]=chr(ord($key[$a%16]) ^ ord($ky[$a])); 
    $mode=MCRYPT_MODE_ECB; 
    $enc=MCRYPT_RIJNDAEL_128; 
    $val=str_pad($val, (16*(floor(strlen($val) / 16)+(strlen($val) % 16==0?2:1))), chr(16-(strlen($val) % 16))); 
    return mcrypt_encrypt($enc, $key, $val, $mode, mcrypt_create_iv( mcrypt_get_iv_size($enc, $mode), MCRYPT_DEV_URANDOM)); 
} 
function DateAdd($interval, $number, $date) {

    $date_time_array = getdate($date);
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
function cleanWord($string) {
	$new_string = "";
	for ($i=0;$i<strlen($string);$i++) {
		$letter = substr($string, $i, 1);
		if (ord($letter)=="146") {
			$letter = "&acute;";
		}
		if (ord($letter)=="233") {
			$letter = "&eacute;";
		}
		if (ord($letter)=="147" || ord($letter)=="148") {
			$letter = "&quot;";
		}
		if (ord($letter)=="151") {
			$letter = "&#8211;";
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
function passed_var($var, $from='both', $cast='string')	{
	set_default_null($cast, 'text');
	switch($from)	{
		case('both'):
			$var = $_GET[$var] != '' ? $_GET[$var]:$_POST[$var];
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
function set_default(&$var, $default='') {
	if(! isset($var)) {
		$var = $default;
		return true;
	}	else return false;
}

function set_default_null(&$var, $default='') {
	if($var == '') {
		$var = $default;
		return true;
	}	else return false;
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
?>