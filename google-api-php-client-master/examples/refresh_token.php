<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("../../api/connection.php");
include_once "templates/base.php";

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
	header("location:https://v4.ikase.org");
	die();
}

$has_expired=true;
if( isset($_SESSION['access_token']) ){

	$token=json_decode($_SESSION['access_token'],true);

	$expires_in=$token['expires_in'];
	$created=$token['created'];

	if( ( $expires_in + $created - time() ) > 0){
		$has_expired=false;
	}   
} 

if (!$has_expired) {
	if (isset($_GET["list"])) {
		header("location:list_messages.php");
	} else {
		die(json_encode(array("result"=>"token current")));
	}
}

require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

/************************************************
  ATTENTION: Fill in these values! Make sure
  the redirect URI is to this page, e.g:
  http://localhost:8080/user-example.php
 ************************************************/
 $client_id = '136098397658-eh1819rrc1nie49mrfljsbd5obnt6rlu.apps.googleusercontent.com';
 $client_secret = 'mU50PLlQPFTyrz1tBKilnFTZ';
 $redirect_uri = 'https://v4.ikase.org/google-api-php-client-master/examples/refresh_token.php';

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

//get the refresh token
$db = getConnection();
$sql = "SELECT `access_token` FROM `ikase`.`cse_user` 
WHERE customer_id = " . $_SESSION['user_customer_id'] . "
AND user_id = " . $_SESSION['user_plain_id'];
try {	
	$stmt = $db->query($sql);
	$stmt->execute();
	$user = $stmt->fetchObject();
	
	$access_token = $user->access_token;
	$token = json_decode($access_token);
	
	$client->refreshToken($token->refresh_token);
	
	$new_token = $client->getAccessToken();
	$new_token_json = json_decode($new_token);
	$token->access_token = $new_token_json->access_token;
	$token->created = $new_token_json->created;
	
	$token = json_encode($token);
	
	$_SESSION["access_token"] = $token;
		
	$sql = "UPDATE `ikase`.`cse_user` 
	SET `access_token` = '" . str_replace("'", "\'", $token) . "'
	WHERE customer_id = " . $_SESSION['user_customer_id'] . "
	AND user_id = " . $_SESSION['user_plain_id'];
	$stmt = $db->query($sql);
	$stmt->execute();
	//
	if (isset($_GET["list"])) {
		header("location:list_messages.php");
	} else {
		die(json_encode(array("result"=>"token refreshed")));
	}
} catch(PDOException $e) {
	$error = array("error1"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}
session_write_close();
$db = null;
?>