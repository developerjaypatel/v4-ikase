<?php
include("manage_session.php");
set_time_limit(3000);
error_reporting(E_ALL);
ini_set('display_errors', '1');	
	
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

?>
<script language="javascript">
parent.setFeedback("check import started");
</script>
<?php
$db = getConnection();
try {
	include("customer_lookup.php");
	
	$sql = "SELECT cis.injury_uuid, COUNT(cis.settlement_uuid) settlement_count 
	FROM ikase_goldberg2.cse_injury_settlement cis
	
	LEFT OUTER JOIN ikase_goldberg2.cse_settlement sett
	ON cis.settlement_uuid = sett.settlement_uuid
		
	WHERE 1
	AND cis.deleted = 'N'
	 AND sett.deleted = 'N'
	AND sett.settlement_id IS NOT NULL
	#AND injury_uuid = 'KI5b6e128d76c32'
	GROUP BY cis.injury_uuid
	HAVING COUNT(cis.settlement_uuid) = 2
	LIMIT 0, 10";
	//AND mc.cpointer = '2031108'
	
	$stmt = $db->prepare($sql);
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt->execute();
	
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrCaseUUID =  array();
	if (count($cases)==0) {
		die("done");
	}
	//die(print_r($cases));
	foreach($cases as $key=>$case){
		//die(print_r($case));
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$injury_uuid = $case->injury_uuid;
		echo "Processing -> " . $key. " == " . $injury_uuid . "\r\n";
		if (in_array($injury_uuid, $arrCaseUUID)) {
			//one time per pointer
			continue;
		} 
		$arrCaseUUID[] = $injury_uuid;
		
		$sql = "SELECT DISTINCT sett.*
		FROM ikase_goldberg2.cse_injury_settlement cis
		
		LEFT OUTER JOIN ikase_goldberg2.cse_settlement sett
		ON cis.settlement_uuid = sett.settlement_uuid
		
		LEFT OUTER JOIN ikase_goldberg2.cse_settlement_fee csf
		ON sett.settlement_uuid = csf.settlement_uuid
		
		LEFT OUTER JOIN ikase_goldberg2.cse_fee fee
		ON csf.fee_uuid = fee.fee_uuid
		
		WHERE 1
		AND injury_uuid = '" . $injury_uuid . "'
		AND cis.deleted = 'N'
		ORDER BY sett.settlement_id";
		
		$stmt = $db->prepare($sql);
		//echo $sql . "\r\n\r\n";
		$stmt->execute();
		
		
		$setts = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		//die(print_r($setts));
		
		$orig = $setts[0];
		$new = $setts[1];
		
		$sql = "";
		
		if ($orig->amount_of_settlement==0 && $orig->amount_of_fee==0 && $orig->legacy_info=="") {
			//skip
		} else {
			$new_fee = $orig->amount_of_fee;
			if ($new_fee < $new->amount_of_fee) {
				$new_fee = $new->amount_of_fee;
			}
			
			$new_atty = $orig->attorney;
			if ($new_atty=="" && $new->attorney!="") {
				$new_atty = $new->attorney;
			}
			
			$sql .= "
			UPDATE ikase_goldberg2.cse_settlement sett
			SET deleted = 'Y'
			WHERE sett.settlement_id = '" . $orig->settlement_id . "';
			";
			
			$stmt = $db->prepare($sql);
			echo $sql . "\r\n\r\n";
			$stmt->execute();
		
			$sql = "UPDATE ikase_goldberg2.cse_settlement sett
			SET date_submitted = '" . $orig->date_submitted . "',
			amount_of_settlement = '" . $orig->amount_of_settlement . "',
			future_medical = '" . $orig->future_medical . "',
			amount_of_fee = '" . $new_fee . "',
			c_and_r = '" . $orig->c_and_r . "',
			stip = '" . $orig->stip . "',
			f_and_a = '" . $orig->f_and_a . "',
			date_approved = '" . $orig->date_approved . "',
			pd_percent = '" . $orig->pd_percent . "',
			date_fee_received = '" . $orig->date_fee_received . "',
			attorney = '" . $new_atty . "'
			WHERE sett.settlement_id = '" . $new->settlement_id . "';
			";
			
			$stmt = $db->prepare($sql);
			echo $sql . "\r\n\r\n";
			$stmt->execute();
			die();
		}
		
		$sql .= "
		UPDATE ikase_goldberg2.cse_settlement sett
		SET deleted = 'Y'
		WHERE sett.settlement_id = '" . $orig->settlement_id . "';
		";
		
		$stmt = $db->prepare($sql);
		//echo $sql . "\r\n\r\n";
		$stmt->execute();
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	echo "Time spent:" . $total_time . "<br />
<br />
";

	
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	echo "<br />" . $sql;
	die();
}	
?>