<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	
/*
 * Copyright 2011 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
include_once "templates/base.php";
include("../../api/manage_session.php");
session_write_close();

$has_expired=true;

if( isset($_SESSION['access_token']) ){

	$token=json_decode($_SESSION['access_token'],true);

	$expires_in=$token['expires_in'];
	$created=$token['created'];

	if( ( $expires_in + $created - time() ) > 0){
		$has_expired=false;
	}   
} 

if ($has_expired) {
	die(json_encode(array("error"=>"token expired")));
}

require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

/************************************************
  ATTENTION: Fill in these values! Make sure
  the redirect URI is to this page, e.g:
  http://localhost:8080/user-example.php
 ************************************************/
 $client_id = '136098397658-eh1819rrc1nie49mrfljsbd5obnt6rlu.apps.googleusercontent.com';
 $client_secret = 'mU50PLlQPFTyrz1tBKilnFTZ';
 $redirect_uri = 'https://www.ikase.website/google-api-php-client-master/examples/user-gmail.php';

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
//$client->addScope("https://www.googleapis.com/auth/urlshortener");
//die(implode(' ', array(Google_Service_Gmail::GMAIL_READONLY)));

$client->addScope(implode(' ', array(Google_Service_Gmail::GMAIL_READONLY)));

/************************************************
  When we create the service here, we pass the
  client to it. The client then queries the service
  for the required scopes, and uses that when
  generating the authentication URL later.
 ************************************************/
//$service = new Google_Service_Urlshortener($client);

$service = new Google_Service_Gmail($client);
$client->setAccessToken($_SESSION['access_token']);

if (strpos($client_id, "googleusercontent") == false) {
  echo missingClientSecretsWarning();
  exit;
}

$list = $service->users_messages->listUsersMessages('me',['maxResults' => 5]);

$messageList = $list->getMessages();

//die(print_r($service));
$inboxMessage = [];

foreach($messageList as $mlist){

	$optParamsGet2['format'] = 'full';
	$single_message = $service->users_messages->get('me',$mlist->id, $optParamsGet2);

	//die(print_r($single_message));
	
	$message_id = $mlist->id;
	$message_body = "";
	$headers = $single_message->getPayload()->getHeaders();
	$snippet = $single_message->getSnippet();
	
	$message = $service->users_messages->get("nick.giszpenc@gmail.com", $message_id);
	foreach($headers as $single) {
		
		if ($single->getName() == 'Subject') {

			$message_subject = $single->getValue();

		}

		else if ($single->getName() == 'Date') {

			$message_date = $single->getValue();
			$message_date = date('M jS Y h:i A', strtotime($message_date));
		}

		else if ($single->getName() == 'From') {

			$message_sender = $single->getValue();
			$message_sender = str_replace('"', '', $message_sender);
		}
	}


	 $inboxMessage[] = [
		'messageId' => $message_id,
		'messageBody' => $message_body,
		'messageSnippet' => $snippet,
		'messageSubject' => $message_subject,
		'messageDate' => $message_date,
		'messageSender' => $message_sender
	];

}

die(print_r($inboxMessage));
?>