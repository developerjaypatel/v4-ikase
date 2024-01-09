<?php
include("manage_session.php");

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");

$recno = passed_var("recno", "get");
$doc = passed_var("doc", "get");
$customer_id = $_SESSION['user_customer_id'];

if (!is_numeric($customer_id)) {
	die();
}
try {
	$db = getConnection();
	
	//lookup the customer name
	$sql_customer = "SELECT data_source
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	$data_source = $customer->data_source;
	
	$query = "SELECT cpointer, form_desc, `document`
	FROM `" . $data_source . "_docs`.`docs" . $doc . "`
	WHERE `recno` = " . $recno;
	//die($query);
	$stmt = $db->prepare($query);
	$stmt->execute();
	$docs = $stmt->fetchObject();
	
	//new procedure going forward for tritek imports 1/7/2019
	if ($customer_id >= 1131) {
		//die(print_r($docs));
		$cpointer = $docs->cpointer;
		
		$url = "http://kustomweb.xyz/tritek/get_doc.php?db=" . $data_source . "&recno=" . $recno . "&doc=" . $doc . "&cpointer=" . $cpointer . "&sess_id=" . $_SESSION["user"];
		header("location:" . $url);
		die();
	}
	
	$content = $docs->document;
	//die($content);
	$form_desc = $docs->form_desc;
	
	$type = "application/msword";
	$extension = "doc";

	if(strpos($content, "verypdf.com") > 0 || strpos($content, "PDF-") !== false) {
		$type = "pdf";
		$extension = "pdf";
	}
	header("Content-type: " . $type);
	header("Content-Disposition: inline; filename=".basename($form_desc . "." . $extension)); //change by angel from attatchment to inline
	
	//echo "<script>window.open('$content', '_blank');
	//echo < /script>";
	
	echo $content; //done by angel
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>