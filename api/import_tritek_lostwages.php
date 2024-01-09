<?php
require_once('../shared/legacy_session.php');
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
	
	$sql = "SELECT DISTINCT cli.cpointer, gcase.case_uuid, lw.*, corp.corporation_uuid employer_uuid
	FROM `" . $data_source . "`.`lostwage` lw
        INNER JOIN
    `" . $data_source . "`.`employer` emp ON lw.lostpoint = emp.lostpoint
        INNER JOIN
    `" . $data_source . "`.`client` cli ON emp.epointer = cli.cpointer
		INNER JOIN 
	`" . $data_source . "`.`" . $data_source . "_case` gcase ON cli.cpointer = gcase.cpointer
		LEFT OUTER JOIN (
			SELECT case_uuid, MIN(corp.corporation_id) employer_id
			FROM " . $data_source . "." . $data_source . "_case_corporation ccc
			INNER JOIN " . $data_source . "." . $data_source . "_corporation corp
			ON ccc.corporation_uuid = corp.corporation_uuid
			WHERE 1
			AND attribute = 'employer'
			AND ccc.deleted = 'N'
			AND corp.deleted = 'N'
			GROUP BY case_uuid
        ) employer ON gcase.case_uuid = employer.case_uuid
        LEFT OUTER JOIN " . $data_source . "." . $data_source . "_corporation corp
        ON employer.employer_id = corp.corporation_id
		
		LEFT OUTER JOIN 
	`" . $data_source . "`.`" . $data_source . "_case_lostincome` gcl ON gcase.case_uuid = gcl.case_uuid
	WHERE 1
	AND gcase.deleted = 'N'
	AND gcl.case_uuid IS NULL
	AND lw.lostpoint > 0
	ORDER BY gcase.cpointer ASC
	";
	//echo $sql . "\r\n<br>";
	
	//die();
	$cases = DB::select($sql);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
	die(print_r($cases));
	//$found = count($cases);
	$last_updated_date = date("Y-m-d H:i:s");
	foreach($cases as $case_key=>$case){
		//die(print_r($case));
		$case_uuid = $case->case_uuid;
		/*
		//lost wages
		$sql_wage = "SELECT lw.* 
		FROM `" . $data_source . "`.`lostwage` lw
		INNER JOIN `" . $data_source . "`.`employer` emp
		ON lw.lostpoint = emp.lostpoint
		
		INNER JOIN `" . $data_source . "`.`client` cli
		ON emp.epointer = cli.cpointer
		 
		WHERE 1
		AND cli.cpointer = '" . $case->cpointer . "'
		ORDER BY `lostdate`";
		$stmt = $db->prepare($sql_wage);
		echo $sql_wage . "\r\n\r\n<br><br>";
		//die();
		$stmt->execute();
		$wages = $stmt->fetchAll(PDO::FETCH_OBJ);
		foreach ($wages as $wage) {
			*/
			//die(print_r($wage));
			
			$table_uuid = uniqid("NG", false);
			$lost_date = $case->lostdate;
			if ($lost_date!="") {
				$lost_date = date("Y-m-d", strtotime($case->lostdate));
			}  else {
				$lost_date = "0000-00-00";
			}
			$sql_wage = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_lostincome` 
			(`lostincome_uuid`, `start_lost_date`, `end_lost_date`, `comments`, `amount`, `customer_id`)
			VALUES ('" . $table_uuid . "', '" . $lost_date . "', '" . $lost_date . "', '" . addslashes($case->lostdesc) . "', '" . $case->lostamt . "', '" . $customer_id . "')";
			//echo $sql_wage . "\r\n\r\n<br><br>";
			$stmt = DB::run($sql_wage);
			
			//attach to case
			$case_table_uuid = uniqid("NG", false);
			//now we have to attach the opposing to the case 
			$sql_wage = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_lostincome` (`case_lostincome_uuid`, `case_uuid`, `lostincome_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
			//echo $sql_wage . "\r\n\r\n<br><br>";
			$stmt = DB::run($sql_wage);
			
			//now we have to attach the wage to the corporation
			$sql_wage = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_lostincome` (`corporation_lostincome_uuid`, `corporation_uuid`, `lostincome_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case->employer_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			//echo $sql_wage . "\r\n\r\n<br><br>";
			$stmt = DB::run($sql_wage);
			
			//die();
			/*
		}
		*/	
	}
	
	echo "done " . date("H:i:s");
} catch(PDOException $e) {
	echo "ERROR:<br />
";
	echo $sql;
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//include("cls_logging.php");

