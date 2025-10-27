<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");
?>
<html>
<body style="font-size:0.95em">
<?php
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql = "SELECT dinj.ct_dates_note, ci.injury_id 
	FROM dordulian.dordulian_injury dinj
	INNER JOIN `ikase_" . $data_source . "`.cse_injury ci
	ON dinj.injury_uuid = ci.injury_uuid
	WHERE 1
	#and dinj.INJURY_ID = 163
	AND ci.start_date = '0000-00-00'
	AND dinj.ct_dates_note != ''
	ORDER BY injury_id";
	echo $sql . "\r\n<br>";
	//
	//die();
	$db = getConnection(); $stmt = $db->prepare($sql);
	$stmt->execute();
	$injuries = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
	
	//die(print_r($injuries));
	$found = count($injuries);
	
	foreach($injuries as $ikey=>$injury){
		
		echo "Processing -> " . $ikey. " == " . $injury->injury_id . "<br>\r\n";
		
		$ct_dates_note = $injury->ct_dates_note;
		
		if ($ct_dates_note=="'/  /    -'" || $ct_dates_note=="CT") {
			continue;
		}
		//see if there is a CT in front
		$arrCT = explode(":", $ct_dates_note);
		if (count($arrCT) > 1) {
			unset($arrCT[0]);
			$ct_dates_note = implode("", $arrCT);
		}
		
		//now split the dates
		$arrDates = explode("-", $ct_dates_note);
		$start_date = date("Y-m-d", strtotime(trim($arrDates[0])));
		$end_date = "0000-00-00";
		if (count($arrDates) == 2) {
			$end_date = date("Y-m-d", strtotime(trim($arrDates[1])));
		}
		
		$sql = "UPDATE `ikase_" . $data_source . "`.cse_injury
		SET start_date = '" . $start_date . "',
		end_date = '" . $end_date . "'
		WHERE injury_id = '" . $injury->injury_id . "'";
		echo $sql . "<br>\r\n";
		
		$db = getConnection(); $stmt = $db->prepare($sql);
		$stmt->execute();$stmt = null; $db = null;
	}
	$db = null;
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}

?>
</body>
</html>