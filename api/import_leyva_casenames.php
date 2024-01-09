<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");

$sql = "SELECT DISTINCT ccase.case_id, ccase.case_number, ccase.case_name, cp.full_name, corp.company_name,
CONCAT(cp.full_name, ' vs ', corp.company_name) new_case_name
FROM ikase_leyva.cse_case ccase
INNER JOIN ikase_leyva.cse_case_person ccp
ON ccase.case_uuid = ccp.case_uuid
INNER JOIN ikase_leyva.cse_person cp
ON ccp.person_uuid = cp.person_uuid
INNER JOIN ikase_leyva.cse_case_corporation ccc
ON ccase.case_uuid = ccc.case_uuid AND ccc.attribute = 'defendant'
INNER JOIN ikase_leyva.cse_corporation corp
ON ccc.corporation_uuid = corp.corporation_uuid AND corp.`type` = 'defendant'
WHERE ccase.case_number LIKE 'PI%'
AND case_name = ''
ORDER BY ccase.case_id DESC";

try {
	$cases = DB::select($sql);
	
	foreach($cases as $case) {
		$sql = "UPDATE ikase_leyva.cse_case
		SET case_name = '" . addslashes($case->new_case_name) . "'
		WHERE case_id = '" . $case->case_id . "'";
		
		echo $sql . "<br>";
		$stmt = DB::run($sql);
	}
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}

