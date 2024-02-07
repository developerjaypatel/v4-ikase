<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../../api/manage_session.php");
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


require('UploadHandler_template.php');
$upload_handler = new UploadHandler();
