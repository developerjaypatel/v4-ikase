<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("connection.php");
include("manage_session.php");

$file = passed_var("file", "get");
$case_id = passed_var("case_id", "get");
$extension = "pdf";

$destination = $file;

$iframe = '<iframe id="refvocational_frame" src="https://v2.ikase.org/uploads/' . $_SESSION["user_customer_id"] . "/" . $case_id . "/refervocational/" . $file . '" width="100%" height="800px"></iframe>';
echo $iframe;

die();
?>