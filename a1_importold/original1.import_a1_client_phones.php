<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
set_time_limit(30000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");
		
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	$stmt = null; $db = null;
	
$sql = "SELECT person_uuid, parent_person_uuid,
		IFNULL(card.`HOME`, '') HOME, IFNULL(card.CAR, '') CAR, 
		IFNULL(card.BEEPER, '') BEEPER, IFNULL(card.`FAX`, '') FAX, IFNULL(card.`BUSINESS`, '') BUSINESS, 
		IFNULL(card.EMAIL, '') EMAIL, IFNULL(card.BIRTH_DATE, '') BIRTH_DATE, 
		IFNULL(card.LANGUAGE, '') LANGUAGE, IFNULL(card.INTERPRET, '') INTERPRET
		FROM " . $data_source . ".card
		INNER JOIN 
		(
			SELECT `CARDCODE`, `FIRST`, `LAST`, `HOME`, `TYPE`, person_uuid, pers.phone, pers.work_phone, pers.parent_person_uuid
			FROM " . $data_source . ".card, " . $data_source . "." . $data_source . "_person pers
			WHERE 1
			#AND `FIRST` = 'FAUSTINO' AND `LAST` = 'Santos-Carmona'
			AND card.ikase_uuid = pers.person_uuid
		) allcards
		ON card.`FIRST` = allcards.`FIRST` AND card.`LAST` = allcards.`LAST` AND card.`HOME` = allcards.`HOME` AND card.`TYPE` != allcards.`TYPE`
		WHERE 1";
	
	$db = getConnection(); $stmt = $db->prepare($sql);
	$stmt->execute();
	$persons = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
	
	//die($sql); 
	foreach($persons as $person) {
		$sql = "UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
		SET pers.`phone` = '" . $person->HOME . "',
		pers.`other_phone` = '" . $person->BEEPER . "',
		pers.`cell_phone` = '" . $person->CAR . "',
		pers.`fax` = '" . $person->FAX . "',
		pers.`work_phone` = '" . $person->BUSINESS . "',
		pers.`email` = '" . $person->EMAIL . "',
		pers.`language` = '" . $person->LANGUAGE . "'
		WHERE pers.parent_person_uuid = '" . $person->parent_person_uuid . "'";
		//echo $sql . "\r\n"; 
		$db = getConnection(); 
		$stmt = $db->prepare($sql); 
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo $person->person_uuid . " done\r\n";
	}

	echo "done at " . date("H:i:s");
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}
?>