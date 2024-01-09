<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	
	
require_once('../shared/legacy_session.php');
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

include("../api/connection.php");
$db = getConnection();

$sql_customer = "SELECT *
FROM  `ikase`.`cse_customer` 
WHERE customer_id = :customer_id";

$stmt = $db->prepare($sql_customer);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$customer = $stmt->fetchObject();

$sql = "SELECT 'applicant', full_name, company_name, dob, full_address, street, suite, city, state, zip, dob, email 
FROM cse_person
WHERE 1
AND MONTH(dob) = '" . date("n") . "'
AND person_uuid = parent_person_uuid
AND customer_id = :customer_id
AND deleted = 'N'
UNION

SELECT 'plaintiff', full_name, company_name, dob, full_address, street, suite, city, state, zip, dob, email 
FROM cse_corporation
WHERE 1
AND MONTH(dob) = '" . date("n") . "'
AND corporation_uuid = parent_corporation_uuid
AND `type` = 'plaintiff'
AND customer_id = :customer_id
AND deleted = 'N'

ORDER BY IF(full_name='', company_name, full_name)";

$stmt = $db->prepare($sql);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_OBJ);

foreach ($clients as $client) {
	$full_name = $client->full_name;
	$dob = $client->dob;
	$email = $client->email;
	
	//nothing
	if ($client->email=="") {
		continue;
	}
	
	echo $full_name . "<br>" . $email . "<br>DOB:&nbsp;" . date("m/d", strtotime($dob)) . "<br><br>";
}
?>
