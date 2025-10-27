<?php
$version = date("mdY") . "1259";

$session_save_path = 'C:\\inetpub\\wwwroot\\ikase.org\\sessions\\';
session_save_path($session_save_path);
$lifetime = 8*60*60; // 8 hours

ini_set('session.gc_maxlifetime', $lifetime);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.cookie_secure', false);
ini_set('session.use_only_cookies', true);

//$session_domain = ".ikase.org";

session_start();
session_set_cookie_params($lifetime, "/");

if (isset($_COOKIE["PHPSESSID"])) {
	setcookie("PHPSESSID", $_COOKIE["PHPSESSID"], time() + $lifetime, "/");
}

$current_session_id = "";
if (isset($_GET["session_id"])) {
	$current_session_id = $_GET["session_id"];
} else {
	if (isset($_SESSION["user"])) {
		$current_session_id = $_SESSION["user"];
	}
}

if ($current_session_id!="") {
	$filename = 'C:\\inetpub\\wwwroot\\ikase.org\\sessions\\data_' . $current_session_id . '.txt';
	if (!file_exists($filename)) {
		$fp = fopen($filename, 'w');
		fwrite($fp, json_encode($_SESSION));
		fclose($fp);
	}
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	$arrSession = json_decode($contents);
	if ($_SERVER['REMOTE_ADDR'] == "47.156.103.17") {
		//die(print_r($arrSession));
	}
	foreach ($arrSession as $sindex=>$session) {
		$_SESSION[$sindex] = $session;
	}
	
}
if (isset($_GET["old_session_id"])) {
	if ($_GET["old_session_id"]!="") {
		$filename = 'C:\\inetpub\\wwwroot\\ikase.org\\sessions\\data_' . $_GET["old_session_id"] . '.txt';
		
		$fp = fopen($filename, 'w');
		fwrite($fp, json_encode($_SESSION));
		fclose($fp);
	}
}

$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
	//echo "1." . session_status();
} else if (time() - $_SESSION['CREATED'] > 28000) {
    // session started more than 8 hours ago
	//die("2." . session_status());
	if (session_status() != "0" || session_status() != "1") { 
    	session_regenerate_id(); 
	}// change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}
if ($_SERVER['REMOTE_ADDR'] == "47.156.103.17") {
		//echo "3." . session_status();
		//die();
	}
?>