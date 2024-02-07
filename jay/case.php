<?php
//Set Customer Information
//customer_id avilable in ikase database -> cse_customer table
//$customer_id=$_GET['customer_id']; // 1070
$customer_database_name='ikase_darshan'; // ikase_leyva
//$kustomweb_archive_folder_name=$_GET['kustomweb_archive_folder_name']; // F:/Alvandi
$source ='ikase.website'; 
// $hostname='ikase.website';
// $username='NewAdmin';
// $password='NewAccess527';
// $dbname='ikase_darshan';

//Archive Script code start

$xml =simplexml_load_file("case_file1.xml") or die("Error: Cannot create object");

$created=$xml->created=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->created));
$closeddate= $xml->{'date-closed'}=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->{'date-closed'}));
$enterdate= $xml->{'date-entered'}=='' ? '0000-00-00': gmdate('Y-m-d', strtotime($xml->{'date-entered'}));
$strreplace= str_replace("'", "''", $xml->type);


$uuid = uniqid('KS') ;
include("../api/connection.php");
$db = getConnection();
$sql="INSERT INTO `cse_case`(`case_number`, `file_number` , `case_name`, `source`, `case_date`, `filing_date`, `terminated_date`, `case_type` ,`file_location` ,`supervising_attorney`, `attorney`)
         VALUES ('". $xml->{'file-number'}."','". $xml->{'case-file-number'}."', '". $xml->name."','meruscase',
      '".$created."' , '".$enterdate."' , '".$closeddate."' ,'".$strreplace."' ,'".$xml->{'file-location'}."' ,'".$xml->{'attorney-handling-initials'}."' ,'".$xml->{'secretary-handling-initials'}."' )";

$stmt = $db->prepare($sql);
$stmt->execute();


?>
