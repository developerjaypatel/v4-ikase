<?php
require_once('../shared/legacy_session.php');

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");

$recno = passed_var("recno", "get");
$doc = passed_var("doc", "get");

try {
	$db = getConnection();
	
	$data_source = "pacific";
	
	$query = "SELECT form_desc, `document`
	FROM `" . $data_source . "_docs`.`docs" . $doc . "`
	WHERE `recno` = " . $recno;
	//die($query);
	$stmt = DB::run($query);
	$docs = $stmt->fetchObject();
	//die(print_r($docs));
	
	$content = $docs->document;
	$form_desc = $docs->form_desc;
	
	$type = "application/msword";
	$extension = "doc";
	
	$type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
	$extension = "docx";
	
	if(strpos($content, "verypdf.com") > 0) {
		$type = "pdf";
		$extension = "pdf";
	}
	header("Content-type: " . $type);
	header("Content-Disposition: attachment; filename=" . $form_desc . "." . $extension);
	echo $content;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
