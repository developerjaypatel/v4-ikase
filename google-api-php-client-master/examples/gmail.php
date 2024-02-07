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
session_start();

require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

define('APPLICATION_NAME', 'Gmail API Quickstart');
define('CREDENTIALS_PATH', 'client_secret_136098397658-eh1819rrc1nie49mrfljsbd5obnt6rlu.apps.googleusercontent.com.json');
define('CLIENT_SECRET_PATH', 'client_secret_136098397658-eh1819rrc1nie49mrfljsbd5obnt6rlu.apps.googleusercontent.com.json');
define('SCOPES', implode(' ', array(
  Google_Service_Gmail::GMAIL_READONLY)
));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfigFile(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  //$credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  //if (file_exists($credentialsPath)) {
	if (isset($_SESSION['access_token'])) {
	    //$accessToken = file_get_contents($credentialsPath);
		$accessToken = $_SESSION['access_token'];
	} else {
		// Request authorization from the user.
		$authUrl = $client->createAuthUrl();
		printf("Open the following link in your browser:\n%s\n", $authUrl);
		print 'Enter verification code: ';
		$authCode = trim(fgets(STDIN));
	
		// Exchange authorization code for an access token.
		$accessToken = $client->authenticate($authCode);
	
		// Store the credentials to disk.
		if(!file_exists(dirname($credentialsPath))) {
		  mkdir(dirname($credentialsPath), 0700, true);
		}
		file_put_contents($credentialsPath, $accessToken);
		printf("Credentials saved to %s\n", $credentialsPath);
	  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->refreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, $client->getAccessToken());
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Gmail($client);

// Print the labels in the user's account.
$user = 'me';
$results = $service->users_labels->listUsersLabels($user);

if (count($results->getLabels()) == 0) {
  print "No labels found.\n";
} else {
  print "Labels:\n";
  foreach ($results->getLabels() as $label) {
    printf("- %s\n", $label->getName());
  }
}
