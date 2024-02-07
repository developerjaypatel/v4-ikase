<!DOCTYPE html>
<html>
<body>
<form action="googleapi.php" method="post" enctype="multipart/form-data">
  Select image to upload:
  <input type="file" name="fileToUpload" id="fileToUpload">
  <input type="submit" value="Upload Image" name="submit">
</form>
</body>
</html>

<?php
require_once 'vendor/autoload.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';

//create a Google OAuth client
$client = new Google_Client();
$client->setClientId('372223860191-qspuvddhii83bh5g88bmp21ac8ik6gtg.apps.googleusercontent.com');
$client->setClientSecret('JlSaEIvkvkEufHp_Yq6O0uyk');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);
$client->setScopes(array('https://www.googleapis.com/auth/drive'));
if(empty($_GET['code']) )
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