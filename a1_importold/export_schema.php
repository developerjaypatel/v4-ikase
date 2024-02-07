<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');
//die("off");
include("manage_session.php");
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
	$export_dir = "c:\\inetpub\\wwwroot\\iKase.org\\exports\\" . $customer_id;
	if (!file_exists($export_dir)) {
		mkdir($export_dir, 0777);
	}
	
	$case_type = passed_var("case_type", "post");
	if (!is_numeric($customer_id)) {
		die("no id");
	}
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id AND deleted = 'N'";
	
	$db = getConnection();
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;	
	
	//die(print_r($customer));
	$cus_name = $customer->cus_name;
	$data_source = $customer->data_source;
	$permissions = $customer->permissions;

	//cases
	$schema = "ikase";
	if ($data_source!="") {
		$schema .= "_" . $data_source;
	}
	
	$table_name = "cse_case";
	$backup_file  = "../exports/" . $customer_id . "/case.sql";
	$sql = "SELECT * INTO OUTFILE '" . $backup_file . "' FROM `" . $schema . "`.`" . $table_name . "`
	WHERE customer_id = :customer_id
	AND (
		`case_type` NOT LIKE 'WC%'
		AND `case_type` NOT LIKE 'Worker%'
		AND `case_type` NOT LIKE 'W/C%'
		)";
	
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	die("DONE:" . $sql);
	$sql = "SELECT user_id, user_uuid, user_name, user_logon
	FROM `ikase`.`cse_user`
	WHERE customer_id = :customer_id
	AND deleted = 'N'";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;	
	
	$sql = "SELECT *
	FROM `" . $schema . "`.`cse_case`
	WHERE customer_id = :customer_id
	AND deleted = 'N'";
	
	if (count($arrSearches) > 0) {
		$sql .= " 
		AND " . implode(" AND ", $arrSearches);
	}
	die($sql);
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;	
	
	$arrTables = array("injury", "notes", "event", "task", "document", "activity", "person", "corporation");	
	
	$arrResults = array(
		"as of"=>date("m/d/y"),
		"users"=>$users
	);
	
	die("found" . count($cases));
	
	foreach($arrTables as $table) {
		foreach($cases as $case) {
			$case_id = $case->case_id;
			$arrResults[$case_id] = array();
			
			$sql = "SELECT `main`.*, `conn`.*";
			
			if ($table=="corporation" || $table=="person") {
				$sql .= "
				, notes.*, tnotes.*";
			}
			if ($table=="corporation") {
				$sql .= "
				, thoc.adhoc prop, thoc.adhoc_value prop_value";
			}
			
			$sql .= "
			FROM `" . $schema . "`.cse_" . $table . " `main`
			INNER JOIN `" . $schema . "`.`cse_case_" . $table . "` `conn`
				ON (`main`." . $table . "_uuid = `conn`.`" . $table . "_uuid`
				AND `conn`.deleted = 'N')
			INNER JOIN `" . $schema . "`.`cse_case` `cse`
				ON `conn`.`case_uuid` = `cse`.`case_uuid`";
			
			if ($table=="corporation" || $table=="person") {
				$sql .= "
				LEFT OUTER JOIN `" . $schema . "`.cse_" . $table . "_notes tnotes
				ON `main`." . $table . "_uuid = `tnotes`.`" . $table . "_uuid`
				LEFT OUTER JOIN `" . $schema . "`.cse_notes notes
				ON tnotes.notes_uuid = notes.notes_uuid AND tnotes.deleted = 'N'
				";
			}
			if ($table=="corporation") {
				$sql .= "
				LEFT OUTER JOIN `" . $schema . "`.cse_" . $table . "_adhoc thoc
				ON `main`." . $table . "_uuid = `thoc`.`" . $table . "_uuid` AND thoc.deleted = 'N'
				";
			}
			$sql .= "
			WHERE `main`.customer_id = :customer_id
			AND `cse`.case_id = :case_id
			AND `main`.deleted = 'N'";
			
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("case_id", $case_id);
			
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_OBJ);
			$stmt->closeCursor(); $stmt = null; $db = null;	
			
			if (count($data) > 0) {
				$arrResults[$case_id][$table] = $data;
				//die(print_r($data));
			}
			
		}
		$json = json_encode($arrResults);
		//write to a file
		
		$fp = fopen($export_dir . '\\' . $table . '.ikase', 'w');
		fwrite($fp, $json);
		fclose($fp);
		
		echo $table . " done\r\n";
	}	
} catch(PDOException $e) {
	echo "<br />ERR:<br />" . $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>