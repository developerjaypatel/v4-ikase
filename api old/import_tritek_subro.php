<?php
include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

function getNickConnection() {
	//$dbhost="54.149.211.191";
	$dbhost="ikase.org";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

//WHERE cli.fileno = 1061
//die($sql);
try {
	$db = getNickConnection();
	
	include("customer_lookup.php");
	
	$sql = "SELECT ccase.case_id, gcase.* 
	FROM `" . $data_source . "`.`badsubro` gcase
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` ccase
	ON gcase.case_uuid = ccase.case_uuid
	WHERE processed = 'N'
	#AND gcase.cpointer = '1020550'
	LIMIT 0, 1";

	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
		//echo " => QUERY completed in " . $total_time . "<br /><br />";
	//die(print_r($cases));
	$found = count($cases);
	
	foreach($cases as $case_key=>$case){
		$case_id = $case->case_id;
		$case_uuid = $case->case_uuid;
		
		echo "Processing " . $case->cpointer . "\r\n<br />";
		
		$sql_subro = "SELECT DISTINCT insr.iname, insp.`subroflag`, insp.`subrofno`, insp.`subroamt`, insp.`subrored`, insp.`inspreppnt`,
		insp.`recno`, insp.`nosubroamt`

		FROM " . $data_source . ".ins, " . $data_source . ".insprep insp, 
		`" . $data_source . "`.`client` cli, 
		`" . $data_source . "`.`insure` insr 
		
		WHERE 1
		AND (insp.subroamt > 0 OR insp.nosubroamt > 0)
		AND ins.inspointer = cli.ipoint
		AND TRIM(SUBSTR(TRIM(inspreppnt), INSTR(TRIM(inspreppnt), '   ')))  = ins.insprepcnt
		AND ins.ipointer = insr.ipointer
		
		AND cli.cpointer = '" . $case->cpointer . "'
		ORDER BY insp.recno ASC";
		
		$stmt = $db->prepare($sql_subro);
		//echo $sql_subro . "\r\n\r\n<br><br>";  
		//die();
		$stmt = $db->query($sql_subro);
		$subros = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($subros));			
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		//echo " => QUERY completed in " . $total_time . "<br /><br />";
		
		$last_updated_date = date("Y-m-d H:i:s");

		foreach($subros as $subro) {
			//find the corporation_uuid
			$sql = "
			SELECT corp.corporation_id, corp.corporation_uuid, company_name 
			FROM " . $data_source . "." . $data_source . "_corporation corp
			INNER JOIN " . $data_source . "." . $data_source . "_case_corporation ccorp
			ON corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.attribute = 'carrier'
			INNER JOIN " . $data_source . "." . $data_source . "_case ccase
			ON ccorp.case_uuid = ccase.case_uuid
			WHERE ccase.case_uuid = '" . $case_uuid . "'
			AND corp.company_name = '" . addslashes($subro->iname) . "'";
			
			$stmt = $db->prepare($sql);
			$stmt = $db->query($sql);
			$corporation = $stmt->fetchObject();
			//print_r($subro);
			//die(print_r($corp));
			/*
			$arrInfo["financial_subro_select_Input"] = $subro->subroflag;
			$arrInfo["financial_subroInput"] = $subro->subroamt;
			$arrInfo["reducedInput"] = $subro->subrored;
			$arrInfo["non_subroInput"] = $subro->nosubroamt;
			*/
			
			$info = array();
			$info[] = array("name"=>"financial_subro_select_Input", "value"=>$subro->subroflag);
			$info[] = array("name"=>"financial_subroInput", "value"=>$subro->subroamt);
			$info[] = array("name"=>"reducedInput", "value"=>$subro->subrored);
			$info[] = array("name"=>"non_subroInput", "value"=>$subro->nosubroamt);
			
			$financial_info = json_encode(array("plaintiff"=>$info));
			//die($financial_info);
			$table_uuid = uniqid("FI", false);
			$corporation_financial_uuid = uniqid("CF", false);
			
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_financial`
			(`financial_uuid`, `financial_info`, `case_id`, `deleted`, `customer_id`, `financial_defendant`)
			VALUES ('" . $table_uuid . "', '" . addslashes($financial_info) . "', '" . $case_id . "', 'N', '" . $customer_id . "', '')";
			
			$stmt = $db->prepare($sql); 
			echo $sql . "\r\n\r\n<br><br>";  
			//die();
			$stmt->execute();
						
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_financial` 
			(`corporation_financial_uuid`, `corporation_uuid`, `financial_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $corporation_financial_uuid  ."', '" . $corporation->corporation_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
			$stmt = $db->prepare($sql); 
			echo $sql . "\r\n\r\n<br><br>";  
			$stmt->execute();			
			//die();
		}
		
		
		$db = null;
	}
	$db = getNickConnection();
	
	$sql = "UPDATE `" . $data_source . "`.`badsubro` 
	SET processed = 'Y'
	WHERE cpointer = '" . $case->cpointer . "'";
	echo $sql . "\r\n\r\n<br><br>";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	//die("done");
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//completeds
	$sql = "SELECT COUNT(*) case_count
	FROM `" . $data_source . "`.`badsubro` gcase
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	echo " => QUERY completed in " . $total_time . "<br /><br />"; 
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`badsubro` ggc
	WHERE processed = 'Y'";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;

	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	echo $total_time . "<br />";
	//echo json_encode($success);
	if ($total_time > 5) {
		//die("too long");
	}
	if (($case_count - $completed_count) != 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runSubros(" . $completed_count . "," . $case_count . ")</script>";
	}

	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}

//include("cls_logging.php");
?>
