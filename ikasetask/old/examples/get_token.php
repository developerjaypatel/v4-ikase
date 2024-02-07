<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("../../api/connection.php");
include_once "templates/base.php";

//die($_SERVER['PHP_SELF']);
include("../../api/manage_session.php");

$blnDoNotPass = true;
if(!empty($_SESSION['user'])) {
	// Next, validate the role to make sure they can access the route
	// We will assume admin role can access everything
	if($_SESSION['user_role'] == "user" || $_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'masteradmin' || $_SESSION['user_role'] == 'owner') {
		$blnDoNotPass = false;
	}
}

if ($blnDoNotPass) {
	header("location:https://www.ikase.website");
	die();
}
if (isset($_REQUEST['logout'])) {
	unset($_SESSION['access_token']);
}
/*
foreach($_SESSION as $session_index=>$session) {
	if (!is_array($session)) {
		echo $session_index . ": " . $session . "<br />";
	}
}
*/

require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

/************************************************
  ATTENTION: Fill in these values! Make sure
  the redirect URI is to this page, e.g:
  http://localhost:8080/user-example.php
 ************************************************/
 //app Web client 1 on google api console
 $client_id = '136098397658-eh1819rrc1nie49mrfljsbd5obnt6rlu.apps.googleusercontent.com';
 $client_secret = 'mU50PLlQPFTyrz1tBKilnFTZ';
 $redirect_uri = 'https://www.ikase.website/google-api-php-client-master/examples/get_token.php';

/************************************************
  Make an API request on behalf of a user. In
  this case we need to have a valid OAuth 2.0
  token for the user, so we need to send them
  through a login flow. To do this we need some
  information from our API console project.
 ************************************************/
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setAccessType('offline');
$client->setApprovalPrompt('force');
$client->addScope(implode(' ', array(Google_Service_Gmail::GMAIL_READONLY)));
//$client->addScope(implode(' ', array(Google_Service_Gmail::GMAIL_READONLY, Google_Service_Gmail::GMAIL_MODIFY)));

//die(print_r($client));
/************************************************
  When we create the service here, we pass the
  client to it. The client then queries the service
  for the required scopes, and uses that when
  generating the authentication URL later.
 ************************************************/
$service = new Google_Service_Gmail($client);

/************************************************
  If we're logging out we just need to clear our
  local access token in this case
 ************************************************/
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}

/************************************************
  If we have a code back from the OAuth 2.0 flow,
  we need to exchange that with the authenticate()
  function. We store the resultant access token
  bundle in the session, and redirect to ourself.
 ************************************************/
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  
  
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

/************************************************
  If we have an access token, we can make
  requests, else we generate an authentication URL.
 ************************************************/
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
} else {
  $authUrl = $client->createAuthUrl();
  //echo $authUrl . "<br />";
}

//die(print_r($client));
/************************************************
  If we're signed in and have a request to shorten
  a URL, then we create a new URL object, set the
  unshortened URL, and call the 'insert' method on
  the 'url' resource. Note that we re-store the
  access_token bundle, just in case anything
  changed during the request - the main thing that
  might happen here is the access token itself is
  refreshed if the application has offline access.
 ************************************************/
/*
if ($client->getAccessToken() && isset($_GET['url'])) {
  $url = new Google_Service_Urlshortener_Url();
  $url->longUrl = $_GET['url'];
  $short = $service->url->insert($url);
  $_SESSION['access_token'] = $client->getAccessToken();
}
*/
session_write_close();

if (strpos($client_id, "googleusercontent") == false) {
  echo missingClientSecretsWarning();
  exit;
}
if (isset($authUrl)) {
  echo "<a class='login' href='" . $authUrl . "'>Click here Authorize GMail Access for iKase</a>";
} else {
	
	$db = getConnection();
	$sql = "UPDATE `ikase`.`cse_user` 
	SET `access_token` = '" . str_replace("'", "\'", $_SESSION["access_token"]) . "'
	WHERE customer_id = " . $_SESSION['user_customer_id'] . "
	AND user_id = " . $_SESSION['user_plain_id'];
	try {	
		$stmt = $db->query($sql);
		$stmt->execute();
	} catch(PDOException $e) {
		$error = array("error1"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
	$db = null;
	$token = json_decode($_SESSION["access_token"]);
	
	header("location:../../v8.php");
	/*
	echo "<br />&nbsp;<br />TOKEN<br />";
	foreach($token as $token_index=>$toke) {
		echo $token_index . ": " . $toke . "<br />";
	}
	
	echo "<br />&nbsp;<br />GET<br />";
	foreach($_GET as $get_index=>$get) {
		echo $get_index . ": " . $get . "<br />";
	}
	
	echo "<br />&nbsp;<br />SESSION<br />";
	foreach($_SESSION as $session_index=>$session) {
		echo $session_index . ": " . $session . "<br />";
	}
	*/
}
?>