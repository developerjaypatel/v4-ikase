<?php
error_reporting(E_ALL ^ E_NOTICE);
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
include("../../api/connection.php");
include_once "templates/base.php";

//die($_SERVER['PHP_SELF']);
include("../../api/manage_session.php");

session_write_close();

$has_expired=true;

//die(print_r($_SESSION));
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$customer_id = $_SESSION['user_customer_id'];

$sql = "SELECT user_id, nickname, email_name 
FROM ikase.cse_email email
INNER JOIN ikase.cse_user_email euser
ON email.email_uuid = euser.email_uuid
INNER JOIN ikase.cse_user usr
ON euser.user_uuid = usr.user_uuid
WHERE usr.user_uuid = :user_uuid
AND usr.customer_id = :customer_id";

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->bindParam("user_uuid", $user_id);
$stmt->bindParam("customer_id", $customer_id);
$stmt->execute();
$recipient = $stmt->fetchObject();
$stmt->closeCursor(); $stmt = null; $db = null;

$destination = $recipient->email_name;

$attach_dir = "attachments/" . $customer_id . "/";
if (!file_exists($attach_dir)) {
	mkdir($attach_dir, 0777);
}
$attach_dir .= $user_id . "/";
if (!file_exists($attach_dir)) {
	mkdir($attach_dir, 0777);
}
if( isset($_SESSION['access_token']) ){

	$token=json_decode($_SESSION['access_token'],true);
	
	//die(print_r($token));
	
	$expires_in=$token['expires_in'];
	$created=$token['created'];
	$interval = $expires_in + $created - time();
	if( ( $interval ) > 0){
		$has_expired=false;
	}   
} 

if ($has_expired) {
	//die(json_encode(array("error"=>"token expired", "interval"=>$interval)));
	header("location:refresh_token.php?list=");
	exit();
}

require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

/************************************************
  ATTENTION: Fill in these values! Make sure
  the redirect URI is to this page, e.g:
  http://localhost:8080/user-example.php
 ************************************************/
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
//$client->addScope("https://www.googleapis.com/auth/urlshortener");
//die(implode(' ', array(Google_Service_Gmail::GMAIL_READONLY)));

$client->addScope(implode(' ', array(Google_Service_Gmail::GMAIL_READONLY, Google_Service_Gmail::GMAIL_MODIFY)));

/************************************************
  When we create the service here, we pass the
  client to it. The client then queries the service
  for the required scopes, and uses that when
  generating the authentication URL later.
 ************************************************/
//$service = new Google_Service_Urlshortener($client);

$service = new Google_Service_Gmail($client);
//die(print_r($service));

$client->setAccessToken($_SESSION['access_token']);

if (strpos($client_id, "googleusercontent") == false) {
  echo missingClientSecretsWarning();
  exit;
}

$list = $service->users_messages->listUsersMessages('me',['maxResults' => 25]);

$messageList = $list->getMessages();

//die(print_r($messageList));
$inboxMessage = array();
$arrIds = array();

//track progress
$progress_file = "progress.txt";
$fp = fopen($progress_file, 'w');
//reset
fwrite($fp, "");
fclose($fp);

//die("count:" . count($messageList));
$arrThreads = array();
foreach($messageList as $mlist){
	//echo "opening " . $mlist->id . "\r\n";
	
	$optParamsGet2['format'] = 'full';
	$single_message = $service->users_messages->get('me',$mlist->id, $optParamsGet2);
	//die(print_r($single_message));
	$arrLabels = $single_message->labelIds;
	//die(print_r($arrLabels));
	$continue = false;
	//CATEGORY_UPDATES
	foreach($arrLabels as $label_index=>$label) {
		if ($label=="UNREAD") {
			unset($arrLabels[$label_index]);
		}
		if ($label=="CATEGORY_PROMOTIONS") {
			$continue = true;
		}
	}
	if (in_array("SENT", $arrLabels)) {
		unset($arrLabels["SENT"]);
	}
	$threads = getThread($service, 'me', $single_message->threadId);
	if (in_array($single_message->threadId, $arrThreads)) {
		$continue = true;
	}
	$arrThreads[] = $single_message->threadId;
	
	if ($continue) {
		//echo "skipped " . $label . "\r\n";
		//die(print_r($mlist));
		
		//echo $destination . ", " . $label . "\r\n";
		//no thank you
		//modifyMessage($service, "me", $mlist->id, $arrLabels, array("UNREAD"));
		
		//skip this one
		continue;	
	}
	//die(print_r($threads));
	if(count($threads->messages) > 1) {
		$arrThreadMessages = $threads->messages;
		//echo "count:" . count($arrThreadMessages);
		//die(print_r($arrThreadMessages));
		foreach($arrThreadMessages as $thread_message) {
			if (in_array($thread_message->id, $arrIds)) {
				continue;
			}
			$arrIds[] = $thread_message->id;
			//die(print_r($thread_message));
			//if ($thread_message->id = $mlist->id) {
			$progress = number_format((count($arrIds) / count($messageList) * 100), 0) . "%";
	
			$fp = fopen($progress_file, 'w');
			fwrite($fp, $progress);
			fclose($fp);
			
			//if it has been processed, then every thing before it is also in
			$url = "https://www.ikase.website/api/messages/check_email";
			$fields = array("id"=>$thread_message->id, 'customer_id'=>$customer_id, 'user_id'=>$user_id);
		
			$result = post_curl($url, $fields);
			$json = json_decode($result);
			
			//print_r($fields);
			//die(print_r($json));
			if($json->count > 0) {
				//found it, look no longer
				//echo $thread_message->id . " was already in\r\n";
				//die();
				//break;
				continue;
			}

			$message_parts = $thread_message->getPayload()->getParts();				
			$headers = $thread_message->getPayload()->getHeaders();				
			$message_sender = "";
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
					if (strpos($message_sender, "<") > -1) {
						$arrSender = explode("<", $message_sender);
						$message_sender = str_replace(">", "", $arrSender[1]);
					}
				}
			}
			
			$arrMessagePart = $message_parts[0];
			$modelData = getProtectedValue($arrMessagePart, "modelData");
			$body = $modelData["body"];
			if (isset($body["data"])) {
				$body_data = $body["data"];
			} else {
				//look for secondary parts
				$parts = $modelData["parts"];
				$body_data = $parts[0]["body"]["data"];
				//die(print_r($arrMessagePart));
			}
			
			//die($body_data);
			$body_data = strtr($body_data, '-_', '+/=');
	
			$message_body = base64_decode($body_data);
			if ($message_body != strip_tags($message_body)) {
				$message_body = strip_tags($message_body);
			}
			$snippet = $thread_message->snippet;
			$threadId = $thread_message->threadId;
			
			$arrAttachments = array();
			//let's get attachments
			//die(print_r($message_parts));
			foreach($message_parts as $part) {
				$filename = $part->getFilename();
				if ($filename!="") {
					//die(print_r($part));
					$part_body = $part->getBody();
					$part_headers = $part->getHeaders();
					$encoding = "";
					foreach($part_headers as $part_header) {
						if ($part_header->name == "Content-Type") {
							$file_type = $part_header->value;
							$arrType = explode("; name=", $file_type);
							$format = $arrType[0];
						}
						if ($part_header->name == "Content-Transfer-Encoding") {
							$encoding = $part_header->value;
							break;
						}
					}
					$attId = $part_body->getAttachmentId();
					
					$data = $service->users_messages_attachments->get('me', $mlist->id, $attId);
					//$data = strtr($data->data, array('-' => '+', '_' => '/'));
					if ($encoding!="") {
						if ($encoding == "base64") {
							//die($data->data);
							//$attachment_data = base64_decode($data->data);	//base64_decode
							$attachment_data = strtr($data->data, '-_', '+/=');
							$attachment_data = base64_decode($attachment_data);
							
							//die($attach_dir . $filename);
							$fh = fopen($attach_dir . $filename , "w+");
							fwrite($fh, $attachment_data);
							fclose($fh);
							
							//echo $attach_dir . $filename . " saved\r\n";
							$arrAttachments[] = $attach_dir . $filename;
						}
					}
				}
			}
			$arrMessage = array(
				'messageId' => $thread_message->id,
				'threadId' => $threadId,
				'messageBody' => urlencode($message_body),
				'messageSnippet' => $snippet,
				'messageSubject' => $message_subject,
				'messageDate' => $message_date,
				'messageSender' => $message_sender,
				'attachments' => implode(";", $arrAttachments),
				'customer_id'=>$customer_id, 'user_id'=>$user_id, 'user_name'=>$user_name, 'destination'=>$destination
			);			
			//}
			$inboxMessage[] = $arrMessage;
			
			//print_r($arrMessage);
			$url = "https://www.ikase.website/api/messages/add_email";
			$fields = $arrMessage;
		
			$result = post_curl($url, $fields);
			
			//die($result);
		}
		//die(print_r($inboxMessage));
		//continue;
	} else {
		//die("no parts");
		$arrIds[] = $mlist->id;
		$threadId = $single_message->threadId;
		$progress = number_format((count($arrIds) / count($messageList) * 100), 0) . "%";
		
		$fp = fopen($progress_file, 'w');
		fwrite($fp, $progress);
		fclose($fp);
	
		//if it has been processed, then every thing before it is also in
		$url = "https://www.ikase.website/api/messages/check_email";
		$fields = array("id"=>$mlist->id, 'customer_id'=>$customer_id, 'user_id'=>$user_id);;
	
		$result = post_curl($url, $fields);
		$json = json_decode($result);
		
		//die(print_r($json));
		if($json->count > 0) {
			//found it, look no longer
			//echo $mlist->id . " was already in\r\n";
			break;
		}
		
		
		//die("go");
		$headers = $single_message->getPayload()->getHeaders();
		//die(print_r($headers));
		$message_sender = "";
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
				if (strpos($message_sender, "<") > -1) {
					$arrSender = explode("<", $message_sender);
					$message_sender = str_replace(">", "", $arrSender[1]);
				}
			}
		}
		//die($message_sender);
		$message_id = $mlist->id;
		$parts = $single_message->getPayload()->getParts();
		//die(print_r($parts));
		
		$arrAttachments = array();
		//let's get attachments
		foreach($parts as $part) {
			$filename = $part->getFilename();
			if ($filename!="") {
				//die(print_r($part));
				$part_body = $part->getBody();
				$part_headers = $part->getHeaders();
				$encoding = "";
				foreach($part_headers as $part_header) {
					if ($part_header->name == "Content-Type") {
						$file_type = $part_header->value;
						$arrType = explode("; name=", $file_type);
						$format = $arrType[0];
					}
					if ($part_header->name == "Content-Transfer-Encoding") {
						$encoding = $part_header->value;
						break;
					}
				}
				$attId = $part_body->getAttachmentId();
				
				$data = $service->users_messages_attachments->get('me', $mlist->id, $attId);
				//$data = strtr($data->data, array('-' => '+', '_' => '/'));
				if ($encoding!="") {
					if ($encoding == "base64") {
						//die($data->data);
						//$attachment_data = base64_decode($data->data);	//base64_decode
						$attachment_data = strtr($data->data, '-_', '+/=');
						$attachment_data = base64_decode($attachment_data);
						
						//die($attachment_data);
						$fh = fopen($attach_dir . $filename , "w+");
						fwrite($fh, $attachment_data);
						fclose($fh);
						
						//echo $attach_dir . $filename . " saved\r\n";
						$arrAttachments[] = $attach_dir . $filename;
					}
				}
			}
		}
		//die(print_r($parts));
		//die("so far");
		$message_body = "";
		if (count($parts) > 0) {
			if (isset($parts[0]["modelData"]["body"]["data"])) {
				$body_data = strtr($parts[0]["modelData"]["body"]["data"], '-_', '+/=');
			} else {
				//look for secondary parts
				$body_data = strtr($parts[0]["modelData"]["parts"][0]["body"]["data"], '-_', '+/=');
			}
			
			$message_body = base64_decode($body_data);
			//die($message_body);
		} else {
			$body = $single_message->getPayload()->getBody();
			
			$body_data = strtr($body->data, '-_', '+/=');
			
			$message_body = base64_decode($body_data);
			if ($message_body != strip_tags($message_body)) {
				$message_body = strip_tags($message_body);
			}
			//$message_body = @processHTML($message_body);
		}
		//die(print_r($single_message));
		$snippet = $single_message->getSnippet();
		$threadId = $single_message->threadId;
		
		$message = $service->users_messages->get($_SESSION["email"], $message_id);
		
		$arrMessage = array(
			'messageId' => $message_id,
			'threadId' => $threadId,
			'threads' => json_encode($threads),
			'messageBody' => urlencode($message_body),
			'messageSnippet' => $snippet,
			'messageSubject' => $message_subject,
			'messageDate' => $message_date,
			'messageSender' => $message_sender,
			'attachments' => implode(";", $arrAttachments),
			'customer_id'=>$customer_id, 'user_id'=>$user_id, 'user_name'=>$user_name, 'destination'=>$destination
		);
		
		print_r($arrMessage);
		$inboxMessage[] = $arrMessage;
		$url = "https://www.ikase.website/api/messages/add_email";
		$fields = $arrMessage;
	
		$result = post_curl($url, $fields);
		
		die($result);
	}
	foreach($arrAttachments as $name) {
		//transfer attachments
		$url = "https://www.ikase.website/api/webmail/transferattach";	
		$fields = array("customer_id"=>$customer_id, "authorize_key"=>urlencode($authorize_key), "filename"=>$name);
		//die(print_r($fields));
		$fields_string = "";
		foreach($fields as $key=>$value) { 
			$fields_string .= $key.'='.$value.'&'; 
		}
		rtrim($fields_string, '&');
		$timeout = 5;
		//die($fields_string);
		
		//open connection
		$ch = curl_init();
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_POST, count($fields_string));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
		
		//execute post
		$result = curl_exec($ch);
	}
}

$fp = fopen($progress_file, 'w');
fwrite($fp, "100%");
fclose($fp);
//die(print_r($inboxMessage));
//die("There are now " . count($inboxMessage) . " new messages in your inbox from your emails");
//header("location:index.php?count=" . count($inboxMessage));
die(json_encode(array("success"=>"true", "last"=>$mlist->id, "ids"=>$arrIds, "count"=>count($inboxMessage))));
?>