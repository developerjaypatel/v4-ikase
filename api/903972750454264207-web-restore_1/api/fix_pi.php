<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");
die();
session_write_close();
$sql = "SELECT cpi.case_id, MIN(cpi.personal_injury_id) min_id, COUNT(personal_injury_id) pi_count
FROM ikase_goldberg2.cse_personal_injury cpi
INNER JOIN ikase_goldberg2.cse_case ccase
ON cpi.case_id = ccase.case_id
WHERE ccase.deleted = 'N'
AND cpi.deleted = 'N'
AND ccase.case_type NOT LIKE 'W%'
AND ccase.case_type != 'social_security'
GROUP BY case_id
HAVING COUNT(personal_injury_id) > 1";

$arrSQL = array();

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	foreach($cases as $case) {
		$sql = "UPDATE ikase_goldberg2.cse_personal_injury
		SET deleted = 'Y'
		WHERE case_id = " . $case->case_id . "
		AND personal_injury_id != " . $case->min_id . ";";
		
		$arrSQL[] = $sql;
		/*
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		*/
	}
	
	echo implode("\r\n\r\n", $arrSQL);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}	
?>