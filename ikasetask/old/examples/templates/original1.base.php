<?php
/* Ad hoc functions to make the examples marginally prettier.*/
function isWebRequest()
{
  return isset($_SERVER['HTTP_USER_AGENT']);
}

function pageHeader($title)
{
  $ret = "";
  if (isWebRequest()) {
    $ret .= "<!doctype html>
    <html>
    <head>
      <title>" . $title . "</title>
      <link href='styles/style.css' rel='stylesheet' type='text/css' />
    </head>
    <body>\n";
    if ($_SERVER['PHP_SELF'] != "/index.php") {
      $ret .= "<p><a href='index.php'>Back</a></p>";
    }
    $ret .= "<header><h1>" . $title . "</h1></header>";
  }
  return $ret;
}


function pageFooter($file = null)
{
  $ret = "";
  if (isWebRequest()) {
    // Echo the code if in an example.
    if ($file) {
      $ret .= "<h3>Code:</h3>";
      $ret .= "<pre class='code'>";
      $ret .= htmlspecialchars(file_get_contents($file));
      $ret .= "</pre>";
    }
    $ret .= "</html>";
  }
  return $ret;
}

function missingApiKeyWarning()
{
  $ret = "";
  if (isWebRequest()) {
    $ret = "
      <h3 class='warn'>
        Warning: You need to set a Simple API Access key from the
        <a href='http://developers.google.com/console'>Google API console</a>
      </h3>";
  } else {
    $ret = "Warning: You need to set a Simple API Access key from the Google API console:";
    $ret .= "\nhttp://developers.google.com/console\n";
  }
  return $ret;
}

function missingClientSecretsWarning()
{
  $ret = "";
  if (isWebRequest()) {
    $ret = "
      <h3 class='warn'>
        Warning: You need to set Client ID, Client Secret and Redirect URI from the
        <a href='http://developers.google.com/console'>Google API console</a>
      </h3>";
  } else {
    $ret = "Warning: You need to set Client ID, Client Secret and Redirect URI from the";
    $ret .= " Google API console:\nhttp://developers.google.com/console\n";
  }
  return $ret;
}

function missingServiceAccountDetailsWarning()
{
  $ret = "";
  if (isWebRequest()) {
    $ret = "
      <h3 class='warn'>
        Warning: You need to set Client ID, Email address and the location of the Key from the
        <a href='http://developers.google.com/console'>Google API console</a>
      </h3>";
  } else {
    $ret = "Warning: You need to set Client ID, Email address and the location of the Key from the";
    $ret .= " Google API console:\nhttp://developers.google.com/console\n";
  }
  return $ret;
}
function modifyMessage($service, $userId, $messageId, $labelsToAdd, $labelsToRemove) {
  $mods = new Google_Service_Gmail_ModifyMessageRequest();
  $mods->setAddLabelIds($labelsToAdd);
  $mods->setRemoveLabelIds($labelsToRemove);
  try {
	//die("userid:" . $userId);
    $message = $service->users_messages->modify($userId, $messageId, $mods);
    print 'Message with ID: ' . $messageId . ' successfully modified.';
    //return $message;
	return true;
  } catch (Exception $e) {
   	print 'An error occurred: ' . $e->getMessage() . "\r\n";
	return false;
  }
}
function getThread($service, $userId, $threadId) {
  try {
    $thread = $service->users_threads->get($userId, $threadId);
    $messages = $thread->getMessages();
    $msgCount = count($messages);
    //print 'Number of Messages in the Thread: ' . $msgCount;
    return $thread;
  } catch (Exception $e){
    //print 'An error occurred: ' . $e->getMessage();
	die(json_encode(array("error"=>$e->getMessage())));
  }
}
function getProtectedValue($obj,$name) {
  $array = (array)$obj;
  $prefix = chr(0).'*'.chr(0);
  return $array[$prefix.$name];
}
