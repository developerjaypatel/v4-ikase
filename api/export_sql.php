<?php
die("off");
require_once('../shared/legacy_session.php');
//die(print_r($_SERVER));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}
include("connection.php");

//i might have to filter further in the future
$arrSearches = array();

try {
	$customer_id = passed_var("customer_id", "post");
	$case_type = passed_var("case_type", "post");
	
	if ($case_type=="pi") {
		//all non wcab
		$arrSearches[] = "
		(
		`case_type` NOT LIKE 'WC%'
		AND `case_type` NOT LIKE 'Worker%'
		AND `case_type` NOT LIKE 'W/C%'
		)";
	}
	if ($case_type=="wcab") {
		//all wcab
		$arrSearches[] = "
		(
		INSTR(`case_type`, 'WC') = 1  
		OR INSTR(`case_type`, 'Worker') = 1
		OR INSTR(`case_type`, 'W/C') = 1
		)";
	}
	$export_dir = ROOT_PATH."exports".DC . $customer_id;
	if (!file_exists($export_dir)) {
		mkdir($export_dir, 0777);
	}
	
	$case_type = passed_var("case_type", "post");
	if (!is_numeric($customer_id)) {
		die("no id");
	}
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	
	$db = getConnection();
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	
	//die(print_r($customer));
	$cus_name = $customer->cus_name;
	$data_source = $customer->data_source;
	$permissions = $customer->permissions;

	//cases
	$schema = "ikase";
	if ($data_source!="") {
		$schema .= "_" . $data_source;
	}
	
	$sql = "SELECT user_id, user_uuid, user_name, user_logon
	FROM `ikase`.`cse_user`
	WHERE customer_id = :customer_id
	AND deleted = 'N'";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$sql = "SELECT *
	FROM `" . $schema . "`.`cse_case`
	WHERE customer_id = :customer_id
	AND deleted = 'N'";
	
	if (count($arrSearches) > 0) {
		$sql .= " 
		AND " . implode(" AND ", $arrSearches);
	}
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$arrTables = array("case", "injury", "notes", "event", "task", "document", "person", "corporation", "case_injury", "case_notes", "case_event", "case_task", "case_document", "case_corporation", "case_person", "corporation_notes", "person_notes", "activity", "case_activity");	
	$arrTables = array("case");
	$arrResults = array(
		"as of"=>date("m/d/y"),
		"users"=>$users
	);
	
	//die("found" . count($cases));
	$return = '';
	$arrValues = array();
	foreach($arrTables as $table) {
		//echo $table . "\r\n";
		$handle = fopen('../exports/1075/data/backup_' . $table . '.sql', 'w');
		fwrite($handle, "");
		fclose($handle);
		
		$sql = "SELECT main.* 
		FROM `" . $schema . "`.cse_" . $table . " main";
		
		if ($table=="activity") {			
			$sql .= "
			INNER JOIN  `" . $schema . "`.cse_case_activity cca
			ON main.activity_uuid = cca.activity_uuid";
		
			$sql .= "
				INNER JOIN  `" . $schema . "`.cse_case ccase
				ON cca.case_uuid = ccase.case_uuid";
				
		} else {
		//if ($table=="activity" || $table=="case_activity") {
			if (strpos($table, "case_") !== false) {
				$sql .= "
				INNER JOIN  `" . $schema . "`.cse_case ccase
				ON main.case_uuid = ccase.case_uuid";
			}
		}
		$sql .= "
		WHERE main.customer_id = :customer_id
		AND main.deleted = 'N'";
		
		//if ($table=="case" || $table=="activity" || $table=="case_activity") {
		if (strpos($table, "case") !== false || $table=="activity") {
			if (count($arrSearches) > 0) {
				$sql .= " 
				AND " . implode(" AND ", $arrSearches);
			}
		}
		//$sql .= " LIMIT 200001 OFFSET 200000";
		//echo $sql . "\r\n";
		
		//continue;
		
		//die();
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$num_fields = 0;
		$arrFields = array();
		foreach($result[0] as $field=>$value) {
			$arrFields[] = "`" . $field . "`";
			$num_fields++;
		}	
		$fields = implode(", ", $arrFields);
		$query_header = 'INSERT INTO cse_' . $table . ' (' . $fields . ') ';
		
		echo $query_header . "\r\n";
			
		//$return .= $query_header;
		foreach($result as $row) {
			$counter = 0;
			$return .= "VALUES ('";
			foreach($row as $j=>$value) {
				$return .= "'" . addslashes($value) . "'";
				if($counter < $num_fields-1){ 
					$return .= ','; 
				}
				$counter++;
			}
			$return .= ")";
			$arrValues[] = $return;
			$return = "";
		}
		$return = $query_header;
		foreach($arrValues as $counter=>$values) {
			$return .= "
			" . $values;
			if ($counter > 0 && $counter%100==0) {
				$return .= ';
				
' . $query_header . '
				'; 
			} else {
				$return .= ",\r\n";
			}
		}
		//die($return);
		//$return .= "\n\n\n";
		/*
		for ($i=0; $i < $num_fields; $i++) { 
			//while ($row = mysqli_fetch_row($result)) {
			foreach($result as $row) {
				//die(print_r($row));
				$return .= 'INSERT INTO '.$table.'VALUES(';
				for ($j=0; $j < $num_fields; $j++) { 
					$row[$j] = addslashes($row[$j]);
					if (isset($row[$j])) {
						$return .= '"'.$row[$j].'"';} else { $return .= '""';}
						if($j<$num_fields-1){ $return .= ','; }
					}
					$return .= ");\n";
				}
			}
			$return .= "\n\n\n";
		
		*/
		echo '../exports/1075/data/backup_' . $table . '.sql DONE
		';
		
		$handle = fopen('../exports/1075/data/backup_' . $table . '.sql', 'w+');
		fwrite($handle, $return);
		fclose($handle);
		
		//die();
		
		$return = "";
	}	
	
	echo "success";
} catch(PDOException $e) {
	echo "<br />ERR:<br />" . $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
