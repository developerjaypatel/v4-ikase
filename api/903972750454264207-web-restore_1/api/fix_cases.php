<?php
include("connection.php");

$sql = "SELECT ccase.case_id, ccase.case_name, CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`
FROM ikase_dordulian2.cse_case ccase

LEFT OUTER JOIN ikase_dordulian2.cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
LEFT OUTER JOIN ikase_dordulian2.cse_person app ON ccapp.person_uuid = app.person_uuid
            
LEFT OUTER JOIN ikase_dordulian2.`cse_case_corporation` ccorp
ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
LEFT OUTER JOIN ikase_dordulian2.`cse_corporation` employer
ON ccorp.corporation_uuid = employer.corporation_uuid

WHERE INSTR(case_name, app.first_name) = 0
AND case_type LIKE 'W%'
AND ccase.case_name LIKE 'Maria Rodriguez%'";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	//die(print_r($cases));
	foreach($cases as $case) {
		//die(print_r($case));
		$sql_fix = "UPDATE ikase_dordulian2.cse_case
		SET case_name = '" . addslashes($case->name) . "'
		WHERE case_id = " . $case->case_id;
		//die($sql_fix);
		echo $case->case_id . " done<br />\r\n";
		
		$db = getConnection();
		$stmt = $db->prepare($sql_fix);
		$stmt->execute();
		$stmt = null; $db = null;
	}
	
	echo "all done";
} catch(PDOException $e) {
	$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
	echo json_encode($error);
}
?>