<?php
include("connection.php");
set_time_limit(3000);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$sql = "SELECT * 
FROM torres.medbill
WHERE 1
#AND medbill_id = '2872'";

$db = getConnection();

$stmt = $db->prepare($sql);
$stmt->execute();
$cases = $stmt->fetchAll(PDO::FETCH_OBJ);

$last_date = '04/03/1991';
foreach($cases as $case) {
	$blnUpdate = false;
	if (strlen($case->date)=="") {
		$blnUpdate = true;
		$case->date = $last_date;
	}
	
	
	
	if (!validateDate($case->date, "m/d/Y")) {
		$case->date = $last_date;
		$blnUpdate = true;
	}
	//die($case->date . " - " . date("m/d/Y", strtotime($case->date)));
	if ($blnUpdate) {
		$sql = "UPDATE torres.medbill
		SET date = '" . $last_date . "'
		WHERE medbill_id = '" . $case->medbill_id . "'";
		
		echo $sql . "<br>";
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		//die($sql);
	}
	$last_date = $case->date;
}
?>