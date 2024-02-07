<?php 
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';
//create a Google OAuth client
$client = new Google_Client();
$client->setClientId('959578964327-i7nilkm6ge3l5k0frpjhhtlg65reglrp.apps.googleusercontent.com');
$client->setClientSecret('v3NXJMHj0rgqpvpa1IrJuFBX');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);
$client->setScopes(array('https://www.googleapis.com/auth/drive'));
if(empty($_GET['code']))
{
    $client->authenticate();
}

if(!empty($_FILES["fileToUpload"]["name"]))
{
  $target_file=$_FILES["fileToUpload"]["name"];
  // Create the Drive service object
  $accessToken = $client->authenticate($_GET['code']);
  $client->setAccessToken($accessToken);
  $service = new Google_DriveService($client);
  // Create the file on your Google Drive
  $fileMetadata = new Google_Service_Drive_DriveFile(array(
    'name' => 'My file'));
  $content = file_get_contents($target_file);
  $mimeType=mime_content_type($target_file);
  $file = $driveService->files->create($fileMetadata, array(
    'data' => $content,
    'mimeType' => $mimeType,
    'fields' => 'id'));
  printf("File ID: %s\n", $file->id);
}
?>