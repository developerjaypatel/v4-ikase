<?php
//Set Customer Information
//customer_id avilable in ikase database -> cse_customer table
$customer_id=$_GET['customer_id']; // 1070
$customer_database_name=$_GET['customer_db_name']; // ikase_leyva
$kustomweb_archive_folder_name=$_GET['kustomweb_archive_folder_name']; // F:/Alvandi
$source = $_GET['source']; // ikase.website





//Archive Script code start
include("../api/connection.php");
$db = getConnection();
$sql="UPDATE ikase.cse_customer
SET archive_type = 'manual', data_path = 'A1'
WHERE customer_id = $customer_id";
$stmt = $db->prepare($sql);
$stmt->execute();


$sql="select case_id, cpointer, source from $customer_database_name.cse_case";
$stmt = $db->prepare($sql);
$stmt->execute();
$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
$stmt->closeCursor(); $stmt = null;
$arrLength = count($documents);
for($int=0; $int<$arrLength; $int++) {
	$doc = $documents[$int];
	// if(($doc->case_id)=="9122"){
		$sql="UPDATE `$customer_database_name`.cse_case
		SET cpointer = $doc->case_id, source = 'a1'
		WHERE case_id = $doc->case_id";
		$stmt = $db->prepare($sql);
		$stmt->execute();
	// }
}	



$url = 'http://kustomweb.xyz/archive/script.php';
$data = array("customer_id" => $customer_id, "folder"=>$kustomweb_archive_folder_name, "customer_database_name"=>$customer_database_name, "source"=>$source);

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
if ($result === FALSE) { /* Handle error */ die('no data '); }

var_dump($result);
?>
