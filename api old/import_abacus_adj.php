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
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_injury`
	WHERE type = 'IMPORT2';
	";
	$stmt = $db->prepare($sql); 		
	$stmt->execute();
	
	$sql = "SELECT REPLACE(IFNULL(law3.WCABNO1, law3.CRTCASENUM), ' ', '') ADJ_NUMBER, 
	IFNULL(law3.DOI1, law3.LDW) DOI, law3.*
	FROM ikase_" . $data_source . ".cse_case ccase
	INNER JOIN `" . $data_source . "`.law3
	ON ccase.case_uuid = law3.CASENUM
	LEFT OUTER JOIN ikase_" . $data_source . ".cse_case_injury cci
	ON ccase.case_uuid = cci.case_uuid
	WHERE cci.case_injury_id IS NULL
	AND CASECODE != 'PI'
	ORDER BY case_number ASC";
	//echo $sql . "\r\n<br>";

	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($cases));
	$found = count($cases);
	$arrCases = array();
	foreach($cases as $case_key=>$case){
		
		echo "Processing -> " . $case_key. " == " . $case->CASENUM . "  ";
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$process_start_time = $time;
		
		$case_no = $case->CASENUM;
		//insert the case
		$case_uuid = $case->CASENUM;
		
		if (in_array($case_uuid, $arrCases)) {
			continue;
		}
		$arrCases[] = $case_uuid;
		
		$injuries = array();
		$arrADJs = array();
		$injuries[] = array("DOI"=>$case->DOI, "ADJ"=>$case->ADJ_NUMBER, "HOW"=>$case->HOWHAP, "CLAIM"=>$case->CLAIMNO1);
		
		//resume normal import from here
		$arrInjuryID = array();	//keep track of uuids
		foreach($injuries as $injury_index=>$injury) {		
			$injury_uuid = uniqid("KI", false);
			$arrInjuryID[] = $injury_uuid;
			
			//die(print_r($injury));
			//doi dates
			if ($injury["DOI"]=="") {
				$injury["DOI"] = "0000-00-00";
			} else {
				$injury["DOI"] = date("Y-m-d", strtotime($injury["DOI"])); 
			}
			
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury` 
			(`injury_uuid`, `injury_number`, `adj_number`, `start_date`, `end_date`, `type`, `occupation`, `body_parts`, `ct_dates_note`,
			`full_address`, `street`, `suite`, `city`, `state`, `zip`, `customer_id`, `explanation`, `deleted`)
			VALUES ('" . $injury_uuid . "', " . ($injury_index + 1) . ", '" . $injury["ADJ"] . "', '" . $injury["DOI"] . "', '0000-00-00', 'IMPORT2', '" . addslashes($case->OCCUPATION) . "','" .  addslashes($case->BODYPARTS) . "','',
			'', '','','', '', '', " . $customer_id . ", '" . addslashes($injury["HOW"]) . "', 'N')";
			echo $sql . "\r\n<br>"; 
			//die();
			
			$stmt = $db->prepare($sql); 
			$stmt->execute();
			
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			
			//now we have to attach the injury to the case 
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
	
			echo $sql . "\r\n<br>";  

			$stmt = $db->prepare($sql); 
			$stmt->execute();
			
			if ($injury["CLAIM"]!="") {
				//add the claim number
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_number`
				(`injury_number_uuid`, `alternate_policy_number`, `customer_id`, `deleted`)
				VALUES ('" . $case_table_uuid  ."', '" . $injury["CLAIM"] . "', '" . $customer_id . "', 'N')";
				echo $sql . "\r\n<br>"; 
				//die();
				$stmt = $db->prepare($sql); 
				$stmt->execute();
			
				//$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				
				//now we have to attach the injury to the case 
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_injury_number` (`injury_injury_number_uuid`, `injury_uuid`, `injury_number_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $injury_uuid . "', '" . $case_table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
		
				echo $sql . "\r\n<br>";  
				$stmt = $db->prepare($sql); 
				$stmt->execute();
				//die();
			}
		}
		
		//die("parties");
		
		//parties
		$sql = "SELECT DISTINCT corp.corporation_uuid
		FROM `ikase_" . $data_source . "`.cse_corporation corp
		INNER JOIN `ikase_" . $data_source . "`.cse_case_corporation ccor
		ON corp.corporation_uuid = ccor.corporation_uuid
		WHERE 1
		AND ccor.case_uuid = '" . $case_uuid . "'
		ORDER BY corp.corporation_id";
		
		$stmt = $db->prepare($sql);
		$stmt = $db->query($sql);
		$parties = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		foreach($parties as $partie) {
			$table_uuid = $partie->corporation_uuid;
			
			foreach($arrInjuryID as $injury_uuid) {
				//attach to injury
				$injury_table_uuid = uniqid("KA", false);
				//now we have to attach the partie to the case 
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				echo $sql . "\r\n<br>";   		
				$stmt = $db->prepare($sql);
				$stmt->execute();
				//only firs one
				break;
			}
			//die(print_r($arrInjuryID));
			echo "\r\n";
		}
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $process_start_time), 4);
		echo " => row completed in " . $total_time . "\r\n<br>"; 
	}
	
	$db = null;
} catch(PDOException $e) {
	echo $sql . "\r\n";	// . "\r\n<br>";
	die();
	$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
	die( json_encode($error));
}


?>
</body>
</html>