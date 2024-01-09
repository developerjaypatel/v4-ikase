<?php
require_once('../shared/legacy_session.php');
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

require('../fileupload/server/php/UploadHandler.php');

$api_key = $_POST["api_key"];
if ($api_key!="f7e87145b17b20b962d7402830df976a") {
	$error = array("error"=> array("text"=>"api key error"));
	echo json_encode($error);
	die();
}
$upload_handler = new UploadHandler();
