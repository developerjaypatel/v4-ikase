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
	/*
	$sql = "UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`phone` = IFNULL(card.HOME, '') , 
	pers.`work_phone` = IFNULL(card.BUSINESS, '') ,
	pers.`fax` = IFNULL(card.FAX, '') , 
	pers.`cell_phone` = IFNULL(card.CAR, '') , 
	pers.`other_phone` = IFNULL(card.BEEPER, '') 
	
	WHERE card.`LAST` = pers.last_name
	AND card.`FIRST` = pers.first_name
	AND card.`BIRTH_DATE` = pers.dob
	
	AND pers.`work_phone` = ''
    AND pers.`fax` = ''
    AND pers.`cell_phone` = ''
    AND pers.`other_phone` = '';";
	*/
	$sql = "UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`phone` = IFNULL(card.HOME, '')
	
	
	WHERE REPLACE(card.`SOCIAL_SEC`, '-', '') = pers.ssn;
    
    
UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`work_phone` = IFNULL(card.BUSINESS, '')
	
	
	WHERE REPLACE(card.`SOCIAL_SEC`, '-', '') = pers.ssn;
    

UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`fax` = IFNULL(card.FAX, '') 
	
	
	WHERE REPLACE(card.`SOCIAL_SEC`, '-', '') = pers.ssn;
    
UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`cell_phone` = IFNULL(card.CAR, '')
	
	
	WHERE REPLACE(card.`SOCIAL_SEC`, '-', '') = pers.ssn;
    
UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`other_phone` = IFNULL(card.BEEPER, '') 
	
	WHERE REPLACE(card.`SOCIAL_SEC`, '-', '') = pers.ssn;
	
	UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`phone` = IFNULL(card.HOME, '')
	
	
	WHERE card.`LAST` = pers.last_name
	AND card.`FIRST` = pers.first_name
	AND card.`BIRTH_DATE` = IF(pers.dob = '', '0000-00-00', pers.dob);
    
    
UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`work_phone` = IFNULL(card.BUSINESS, '')
	
	
	WHERE card.`LAST` = pers.last_name
	AND card.`FIRST` = pers.first_name
	AND card.`BIRTH_DATE` = IF(pers.dob = '', '0000-00-00', pers.dob);
    

UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`fax` = IFNULL(card.FAX, '') 
	
	
	WHERE card.`LAST` = pers.last_name
	AND card.`FIRST` = pers.first_name
	AND card.`BIRTH_DATE` = IF(pers.dob = '', '0000-00-00', pers.dob);
    
UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`cell_phone` = IFNULL(card.CAR, '')
	
	
	WHERE card.`LAST` = pers.last_name
	AND card.`FIRST` = pers.first_name
	AND card.`BIRTH_DATE` = IF(pers.dob = '', '0000-00-00', pers.dob);
    
UPDATE " . $data_source . ".card,  " . $data_source . "." . $data_source . "_person pers
	
	SET pers.`other_phone` = IFNULL(card.BEEPER, '') 
	
	WHERE card.`LAST` = pers.last_name
	AND card.`FIRST` = pers.first_name
	AND card.`BIRTH_DATE` = IF(pers.dob = '', '0000-00-00', pers.dob);";
    
	//ND REPLACE(IFNULL(SOCIAL_SEC, ''), '-', '') = pers.ssn
	//AND pers.ssn != '';
	
	$sql .= "
	
	UPDATE " . $data_source . "." . $data_source . "_person bpers, ikase_" . $data_source . ".cse_person pers
	SET pers.phone = bpers.phone
	WHERE bpers.person_uuid = pers.person_uuid
	AND bpers.phone!='';
	
	UPDATE " . $data_source . "." . $data_source . "_person bpers, ikase_" . $data_source . ".cse_person pers
	SET pers.work_phone = bpers.work_phone
	WHERE bpers.person_uuid = pers.person_uuid
	AND bpers.work_phone!='';
	
	UPDATE " . $data_source . "." . $data_source . "_person bpers, ikase_" . $data_source . ".cse_person pers
	SET pers.fax = bpers.fax
	WHERE bpers.person_uuid = pers.person_uuid
	AND bpers.fax!='';
	
	UPDATE " . $data_source . "." . $data_source . "_person bpers, ikase_" . $data_source . ".cse_person pers
	SET pers.cell_phone = bpers.cell_phone
	WHERE bpers.person_uuid = pers.person_uuid
	AND bpers.cell_phone!='';
	
	UPDATE " . $data_source . "." . $data_source . "_person bpers, ikase_" . $data_source . ".cse_person pers
	SET pers.other_phone = bpers.other_phone
	WHERE bpers.person_uuid = pers.person_uuid
	AND bpers.other_phone!='';
	
	UPDATE " . $data_source . "." . $data_source . "_person bpers, ikase_" . $data_source . ".cse_person pers
	SET pers.phone = bpers.phone
	WHERE bpers.parent_person_uuid = pers.parent_person_uuid
	AND bpers.phone!='';
	
	UPDATE " . $data_source . "." . $data_source . "_person bpers, ikase_" . $data_source . ".cse_person pers
	SET pers.work_phone = bpers.work_phone
	WHERE bpers.parent_person_uuid = pers.parent_person_uuid
	AND bpers.work_phone!='';
	
	UPDATE " . $data_source . "." . $data_source . "_person bpers, ikase_" . $data_source . ".cse_person pers
	SET pers.fax = bpers.fax
	WHERE bpers.parent_person_uuid = pers.parent_person_uuid
	AND bpers.fax!='';
	
	UPDATE " . $data_source . "." . $data_source . "_person bpers, ikase_" . $data_source . ".cse_person pers
	SET pers.cell_phone = bpers.cell_phone
	WHERE bpers.parent_person_uuid = pers.parent_person_uuid
	AND bpers.cell_phone!='';
	
	UPDATE " . $data_source . "." . $data_source . "_person bpers, ikase_" . $data_source . ".cse_person pers
	SET pers.other_phone = bpers.other_phone
	WHERE bpers.parent_person_uuid = pers.parent_person_uuid
	AND bpers.other_phone!='';
";
	//die($sql); 
	$db = getConnection(); 
	$stmt = $db->prepare($sql); 
	$stmt->execute();
	$stmt = null; $db = null;

	echo "done at " . date("H:i:s");
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}
?>