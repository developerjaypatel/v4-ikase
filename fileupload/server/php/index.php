<?php
error_reporting(E_ALL ^ E_DEPRECATED); 
ini_set('display_errors', '1');

require_once('../../../shared/legacy_session.php');
include("../../../api/connection.php");
session_write_close();

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

require('UploadHandler.php');
$upload_handler = new UploadHandler();
