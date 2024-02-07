<?php
$session_save_path = 'C:\\inetpub\\wwwroot\\iKase.org\\iklock\\sessions\\';
session_save_path($session_save_path);
$lifetime = 8*60*60; // 8 hours

ini_set('session.gc_maxlifetime', $lifetime);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.cookie_secure', false);
ini_set('session.use_only_cookies', true);

//$session_domain = ".ikase.org";
session_set_cookie_params($lifetime, "/");
session_start();

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
	$filename = 'C:\\inetpub\\wwwroot\\iKase.org\\iklock\\sessions\\data_' . $current_session_id . '.txt';
	if (!file_exists($filename)) {
		$fp = fopen($filename, 'w');
		fwrite($fp, json_encode($_SESSION));
		fclose($fp);
	}
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	$arrSession = json_decode($contents);
	
	foreach ($arrSession as $sindex=>$session) {
		$_SESSION[$sindex] = $session;
	}
}
if (isset($_GET["old_session_id"])) {
	if ($_GET["old_session_id"]!="") {
		$filename = 'C:\\inetpub\\wwwroot\\iKase.org\\iklock\\sessions\\data_' . $_GET["old_session_id"] . '.txt';
		
		$fp = fopen($filename, 'w');
		fwrite($fp, json_encode($_SESSION));
		fclose($fp);
	}
}

$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 28000) {
    // session started more than 8 hours ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}
?>