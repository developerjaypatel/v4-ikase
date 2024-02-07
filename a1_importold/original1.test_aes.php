<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");
$string = "Lopez, Kirsten scan F&S Petition for Benefits Under Labor Code § 132a 10-11-2017.pdf";
$string = noSpecialFilename($string);
die($string);

$search_term = passed_var("search_term", "get");

if (strlen($search_term)  < 3) {
	$error = array("error"=> array("text"=>"too short"));
	echo json_encode($error);
	die();
}
try {
	$db = getConnection();
	
	$sql = "SELECT *, AES_DECRYPT(`private_value`, '" . CRYPT_KEY . "')  `public` 
	FROM ikase.cse_private
	WHERE AES_DECRYPT(`private_value`, '" . CRYPT_KEY . "') LIKE '%" . $search_term . "%'";
	
	//echo $sql;
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$public = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	echo print_r($public);
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>