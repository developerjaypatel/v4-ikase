<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");
$customer_id = 1062;

include("connection.php");
?>
<html>
<body style="font-size:0.7em">
<?php
try {
	$db = getConnection();
	
	//venues
	$sql = "select DISTINCT ccase.case_id, cuser.user_id oworker, newuser.user_id worker, newuser.customer_id
FROM ikase_hernandez.cse_case ccase
INNER JOIN ikase_hernandez.cse_user cuser
ON ccase.worker = cuser.user_id
AND cuser.customer_id = ccase.customer_id
INNER JOIN ikase.cse_user newuser
ON cuser.nickname = newuser.nickname
AND cuser.customer_id = newuser.customer_id
AND cuser.customer_id = " . $customer_id . "
ORDER BY case_id";
	echo $sql . "\r\n<br>";
	//#AND (CASENO = 19493 OR CASENO = 19481 OR CASENO = 19013) 
	//die();
	$cases = DB::select($sql);
	
	//die(print_r($cases));
	$found = count($cases);
	
	foreach($cases as $case_key=>$case){
		$worker = $case->worker;
		echo "Processing -> " . $case_key. " == " . $case->case_id . "\r\n";
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$process_start_time = $time;
		
		
		//now the kase
		$sql = "UPDATE ikase_hernandez.cse_case
		SET `worker` = '" . $case->worker . "'
		WHERE case_id = " . $case->case_id . "
		AND customer_id = " . $customer_id . "";
		//addslashes($case->CAPTION1) . "', '" . 
		echo $sql . "\r\n<br>"; 
		//die();
		$stmt = DB::run($sql);
	}
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}
include("cls_logging.php");
/*
SELECT * 
FROM a1.casecard acc
INNER JOIN a1.card ac
ON acc.CARDCODE = ac.CARDCODE
INNER JOIN a1.card2 ac2
ON ac.FIRMCODE = ac2.FIRMCODE
WHERE CASENO = 9662;

SELECT DISTINCT * 
FROM a1.injury
WHERE CASENO = 9662

//activity categories?
SELECT * FROM a1.actdeflt;

SELECT * FROM a1.bill1
WHERE CASENO = 9662

SELECT * FROM a1.bill2
WHERE CASENO = 9662

//events
SELECT * FROM a1.cal1;
//event assignee
SELECT * FROM a1.cal2;

//liens
SELECT * FROM a1.cc1;

//document track
SELECT * FROM a1.doctrk1;

//outgoing emails
SELECT * FROM a1.email;

//attachment
SELECT * FROM a1.email2;

//injury and bodyparts
SELECT DISTINCT * FROM a1.injury;


//intake
SELECT DISTINCT * FROM a1.intake;

//list of letters
SELECT DISTINCT * FROM a1.letters;

//deletes
SELECT * FROM a1.logtk;

//scans
SELECT * FROM a1.scanfi1;

//scan directories
SELECT * FROM a1.scanprof;

//users
SELECT * FROM a1.staff;

//tasks
SELECT * FROM a1.tasks;
SELECT * FROM a1.tasksbk;

//adhoc fields for forms
SELECT * FROM a1.user2;

*/

?>
</body>
</html>
