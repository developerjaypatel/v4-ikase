<?php
error_reporting(0);
$app->group('', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->group('/person', function (\Slim\Routing\RouteCollectorProxy $app) {
		$app->get('', 'getPersons');
		$app->get('/tokeninput', 'getTokenPersons');
		$app->get('/{id}', 'getPerson');
		$app->post('/delete', 'deletePerson');
		$app->post('/add', 'addPerson');
		$app->post('/update', 'updatePerson');
		$app->post('/field/update', 'updatePersonField');
	});

	$app->get('/personemail', 'getPersonEmails');
	$app->get('/workhistory/{case_id}', 'workHistory');
	$app->get('/clientemails', 'getClientDOBEmails');
	$app->get('/clientemailsbymonth/{month}', 'getClientDOBEmailsByMonth');

	$app->post('/workhistory/add', 'addFullWorkHistory');
})->add(\Api\Middleware\Authorize::class);

function addFullWorkHistory() {
	session_write_close();
	$arrFields = array();
	$arrSet = array();
	$case_id = 0;
	$table_name = "work_history";
	$table_id = passed_var("table_id", "post");
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		$fieldname = str_replace("applicant_", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		
		if ($fieldname=="case_id"){			
			$case_id = $value;
			$injury = getInjuriesInfo($case_id);
			$injury_uuid = $injury[0]->uuid;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if (strpos($fieldname, "_date")!==false || strpos($fieldname, "date_")!==false) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
	}	
	
	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ", `case_id`) 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ", '" . $case_id . "')";
		//die($sql);
		try {
			DB::run($sql);
	$new_id = DB::lastInsertId();
			
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			//trackPerson("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		
		//where
		$where_clause = "= '" . $table_id . "'";
		$where_clause = "`" . $table_name . "_id`" . $where_clause . "
		AND `customer_id` = " . $_SESSION['user_customer_id'];

		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET " . implode(",", $arrSet) . "
		WHERE " . $where_clause;
		
		try {
			$stmt = DB::run($sql);
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
}
function workHistory($case_id) {
	session_write_close();
	$sql = "SELECT wh.*, wh.work_history_id id
			FROM `cse_work_history` wh 
			WHERE wh.deleted = 'N'
			AND wh.case_id = '" . $case_id . "'
			AND wh.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		//$stmt->bindParam("case_id", $case_id);
		$person = $stmt->fetchObject();
		if (is_object($person)) {
			$person->work_history_info = str_replace("\r\n", " ", $person->work_history_info);
			$person->work_history_info = str_replace("\n", " ", $person->work_history_info);
			$person->work_history_info = str_replace(chr(13), " ", $person->work_history_info);
		}
		echo json_encode($person);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getWorkHistory($case_id) {
	session_write_close();
	$sql = "SELECT wh.*, wh.work_history_id id
			FROM `cse_work_history` wh 
			WHERE wh.deleted = 'N'
			AND wh.case_id = '" . $case_id . "'
			AND wh.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		//$stmt->bindParam("case_id", $case_id);
		$person = $stmt->fetchObject();
		if (is_object($person)) {
			$person->work_history_info = str_replace("\r\n", " ", $person->work_history_info);
			$person->work_history_info = str_replace("\n", " ", $person->work_history_info);
			$person->work_history_info = str_replace(chr(13), " ", $person->work_history_info);
		}
		return json_encode($person);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPersonEmails() {
	getPersons("email");
}
function getPersons($filter = "") {
	session_write_close();
	if (($_SESSION['user_customer_id']==1033)) {
		getPersonXs();
		return;
	}
	if ($filter!="") {
		$filter = " AND `pers`.`" . $filter . "` != ''";
	}
    $sql = "SELECT pers.*, pers.person_uuid uuid  
			FROM `cse_person` pers 
			WHERE pers.deleted = 'N'
			AND pers.customer_id = " . $_SESSION['user_customer_id'] . $filter . "
			ORDER by pers.last_name, first_name";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$person = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($person);
        } else {
            echo $_GET['callback'] . '(' . json_encode($person) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTokenPersons() {
	session_write_close();
	if (($_SESSION['user_customer_id']==1033)) {
		getTokenPersonXs();
		return;
	}
	$search_term = passed_var("q", "get");
    $sql = "SELECT pers.*, pers.person_uuid uuid, pers.person_id id  
			FROM `cse_person` pers 
			WHERE pers.deleted = 'N'
			AND pers.person_uuid = pers.parent_person_uuid
			AND pers.customer_id = " . $_SESSION['user_customer_id'];
			if ($search_term != "") {	
				$sql .= " AND (";
				$arrSearch[] = " pers.`full_name` LIKE '%" . $search_term . "%' ";
			
				$sql .= implode(" OR ", $arrSearch);
				$sql .= ")";
			} 
	$sql .=" ORDER by pers.full_name";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$person = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($person);
        } else {
            echo $_GET['callback'] . '(' . json_encode($person) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getPerson($id) {
	session_write_close();
	if (($_SESSION['user_customer_id']==1033)) {
		getPersonX($id);
		return;
	}
    $sql = "SELECT pers.*, pers.person_id id, pers.person_uuid uuid
			FROM `cse_person` pers 
			WHERE pers.person_id=:id
			AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
			AND pers.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$person = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($person);
        } else {
            echo $_GET['callback'] . '(' . json_encode($person) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPersonInfo($id) {
	session_write_close();
	if (($_SESSION['user_customer_id']==1033)) {
		return getPersonXInfo($id);
	}
	//return a row if id is valid
	$sql = "SELECT pers.*, pers.person_id id , pers.person_uuid uuid 
		FROM `cse_person` pers 
		WHERE pers.person_id=:id
		AND pers.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$person = $stmt->fetchObject();
		//die($sql);

        return $person;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deletePerson() {
	if (($_SESSION['user_customer_id']==1033)) {
		deletePersonX();
		return;
	}
	$id = passed_var("person_id", "post");
	$sql = "UPDATE cse_person pers
			SET pers.`deleted` = 'Y'
			WHERE `person_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("person_id", $id);
		$stmt->execute();
		
		trackPerson("delete", $id);
		
		echo json_encode(array("success"=>"person marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addPerson() {
	if (($_SESSION['user_customer_id']==1033)) {
		addPersonX();
		return;
	}
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$case_id = "";
	$injury_id = "";
	$injury_uuid = "";
	$representing = "";
	$salutation = "";
	$first_name = "";
	$last_name = "";
	$full_name = "";
	$ssn1 = "";
	$ssn2 = "";
	$ssn3 = "";
	$blnChild = false;
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		$fieldname = str_replace("applicant_", "", $fieldname);
		if ($fieldname=="person_id") {
			//if it's numeric, it's a look up
			if (is_numeric($value)) {
				if ($value > -1) {
					$parent_person = getPersonInfo($value);	
					$value = $parent_person->full_name;
					$blnChild = true;
				}
			}
		}
		if (strpos($fieldname, "_person")!==false) {
			continue;
		}
		if (strpos($fieldname, "token-")!==false) {
			continue;
		}
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="representing") {
			$representing = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="first_name") {
			$first_name = $value;
		}
		if ($fieldname=="last_name") {
			$last_name = $value;
		}
		if ($fieldname=="full_name") {
			$full_name = $value;
		}
		if ($fieldname=="last_name") {
			$last_name = $value;
		}
		if ($fieldname=="age") {
			if ($value=="" || $value=="Please enter DOB"){
				$value = 0;
			}
		}
		if ($fieldname=="priority_flag") {
			if ($value==""){
				$value = "N";
			}
		}
		if ($fieldname=="salutation") {
			$salutation = $value;
		}
		if ($fieldname=="gender") {
			if ($salutation!="") {
				continue;
			}
		}
		//ssn
		if ($fieldname=="ssn1"){
			//encrypt here
			$ssn1 = $value;
			continue;
		}
		if ($fieldname=="ssn2"){
			//encrypt here
			$ssn2 = $value;
			continue;
		}
		if ($fieldname=="ssn3"){
			$ssn3 = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			if ($case_id!="") {
				$kase = getKaseInfo($case_id);
			}
			continue;
		}
		if ($fieldname=="injury_id") {
			$injury_id = $value;
			if ($value!="" && $value!="-1") {
				$injury = getInjuryInfo($value);
				
				$injury_uuid = $injury->uuid;
			}
			continue;
		}
		if ($fieldname=="case_uuid" || $fieldname=="table_id" || $fieldname=="person_uuid" || $fieldname=="person_id" || $fieldname=="injury_id") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	if ($full_name=="") {
		//no fullname
		$arrFields[] = "`full_name`";
		$arrSet[] = "'" . addslashes($first_name . " " . $last_name) . "'";
	}
	if ($full_name!="") {
		//fullname only
		$arrNames = explode(" ", trim($full_name));
		$arrFields[] = "`first_name`";
		$arrSet[] = "'" . $arrNames[0] . "'";
		unset($arrNames[0]);
		if (count($arrNames) > 1) {
			$arrFields[] = "`middle_name`";
			$arrSet[] = "'" . $arrNames[1] . "'";
			unset($arrNames[1]);
		}
		$arrFields[] = "`last_name`";
		$arrSet[] = "'" .implode(" ", $arrNames) . "'";
	}
	//gender
	if ($salutation!="") {
		$gender = "F";
		if ($salutation == "Mr") {
			$gender = "M";
		}
		$arrFields[] = "`gender`";
		$arrSet[] = "'" . $gender . "'";
	}
	//ssn
	while(strlen($ssn1) < 3) {
		$ssn1 .= "X";
	}
	while(strlen($ssn2) < 2) {
		$ssn2 .= "X";
	}
	while(strlen($ssn3) < 4) {
		$ssn3 .= "X";
	}
	$ssn = $ssn1 . $ssn2 . $ssn3;
	$arrFields[] = "`ssn`";
	$arrSet[] = "'" . $ssn . "'";
	
	$arrFields[] = "`ssn_last_four`";
	$arrSet[] = "'" . $ssn3 . "'";
	
	//now we start saving
	$db = getConnection();
	$table_uuid = uniqid("KS", false);
	
	//die($kase->uuid);
	
	if ($blnChild) {
		$arrFields[] = "`parent_person_uuid`";
		$arrSet[] = "'" . $parent_person->person_uuid . "'";
	} else {
		$table_uuid = uniqid("RD", false);
		//insert the parent record first
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ", `parent_person_uuid`) 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ", '" . $table_uuid . "')";
		try { 		
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		//now we create the actual record
		$parent_table_uuid = $table_uuid;
		
		$table_uuid = uniqid("KS", false);
		
		$arrFields[] = "`parent_person_uuid`";
		$arrSet[] = "'" . $parent_table_uuid . "'";
	}
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(", ", $arrFields) . ") 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "'," . implode(",
			", $arrSet) . ")";
			
	//die($sql);
	try { 
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		if ($case_id != "") {
			$case_table_uuid = uniqid("KA", false);
			$last_updated_date = date("Y-m-d H:i:s");
			//clear out any previously attached
			$sql = "UPDATE `cse_case_person` 
			SET  `deleted` =  'Y' 
			WHERE `case_uuid` LIKE  '" . $kase->uuid . "'
			AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
			//die($sql);
			try {
				$stmt = DB::run($sql);
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
			//now we have to attach the applicant to the case 
			$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $kase->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			try {
				$stmt = DB::run($sql);
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
		
		//vehicle owner
		if ($injury_id != "" && $representing!="") {
			$injury_table_uuid = uniqid("KA", false);
			$last_updated_date = date("Y-m-d H:i:s");
			//clear out any previously attached
			$sql = "UPDATE `cse_injury_person` 
			SET  `deleted` =  'Y' 
			WHERE `injury_uuid` LIKE  '" . $injury->uuid . "'
			AND `attribute_1` = 'owner'
			AND `attribute_2` = :representing
			AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
			//die($sql);
			try {
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("representing", $representing);
				$stmt->execute();
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
			//now we have to attach the applicant to the case 
			$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $table_uuid . "', 'owner', :representing, '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			try {
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("representing", $representing);
				$stmt->execute();
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
		
		//track now
		trackPerson("insert", $new_id);	
	} catch(PDOException $e) {	
		echo "ERROR
		" . $sql;
		//echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function updatePersonField() {
	if (($_SESSION['user_customer_id']==1033)) {
		updatePersonXField();
		return;
	}
	session_write_close();
	$id = passed_var("id", "post");
	$fieldname = passed_var("fieldname", "post");
	$value = passed_var("value", "post");
	$case_id = passed_var("case_id", "post");
	
	$kase = getKaseInfo($case_id);
	
	$customer_id = $_SESSION['user_customer_id'];
	
	if ($fieldname=="dob") {
		if ($value!="") {
			$value = date("Y-m-d", strtotime($value));
		}
	}
	//address
	$arrAddress = array("street", "suite", "city", "administrative_area_level_1", "postal_code");
	foreach($arrAddress as $add) {
		if ($fieldname == $add . "_person") {
			$fieldname = $add;
			if ($add=="administrative_area_level_1") {
				$fieldname = "state";
			}
			if ($add=="postal_code") {
				$fieldname = "zip";
			}
			break;
		}
	}
	
	$arrSet = array();
	if ($fieldname=="full_name") {
		$full_name = $value;
		
		$arrNames = explode(" ", trim($full_name));
		//$arrFields[] = "`first_name`";
		$arrSet[] = "`first_name` = '" . addslashes(trim($arrNames[0])) . "'";
		//$arrFields[] = "`last_name`";
		$arrSet[] = "`last_name` = '" . addslashes(trim($arrNames[count($arrNames)-1])) . "'";
		unset($arrNames[count($arrNames)-1]);
		unset($arrNames[0]);
		if (count($arrNames) > 0) {
			//$arrFields[] = "`middle_name`";
			$arrSet[] = "`middle_name` = '" . addslashes(implode(" ", $arrNames)) . "'";
		}
	}
	$sql = "UPDATE cse_person 
	SET `" . $fieldname . "` = :value";
	if (count($arrSet) > 0) {
		$sql .= ",
		" . implode(", ", $arrSet);	
	}
	$sql .= "
	WHERE person_id = :id
	AND customer_id = :customer_id";
	
	//die($sql);
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("value",  $value);
		$stmt->execute();
		
		echo json_encode(array("id"=>$id)); 
		
		trackPerson("update", $id);
		
		if ($case_id!="" && $fieldname=="full_name") {
			//update the case name
			$case_type = $kase->case_type;
			$case_name = $kase->case_name;
			if (strpos($case_type, "WC") === false && strpos($case_type, "W/C") === false  && strpos($case_type, "Worker") === false) {
				//pi
				$vs_pos = strpos($case_name, " vs ");
				$v_sep = "vs";
				if ($vs_pos===false) {
					//try again
					$vs_pos = strpos($case_name, " v. ");
					$v_sep = "v.";
				}
				//vestigial cases from tritek
				if ($vs_pos===false || $case_name=="") {
					$sql = "
					UPDATE cse_case 
					SET case_name = '" . addslashes($full_name) . "'
					WHERE case_id = " . $case_id;
					$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
					
				} else {
					$arrCaseName = explode(" " . $v_sep . " ", $case_name);

					$new_case_name = $full_name . " vs " . $arrCaseName[1];
					$sql = "
					UPDATE cse_case 
					SET case_name = '" . addslashes($new_case_name) . "'
					WHERE case_id = " . $case_id;
					$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
				}
				echo $sql . "\r\n";  
				$stmt = DB::run($sql);
			} else {
				//maybe not quite right			
				if (strpos($case_name, $full_name)===false) {
					$vs_pos = strpos($case_name, " vs ");
					$v_sep = "vs";
					if ($vs_pos===false) {
						//try again
						$vs_pos = strpos($case_name, " v. ");
						$v_sep = "v.";
					}
					//brand new or update?
					if ($vs_pos===false) {
						$sql = "
						UPDATE cse_case 
						SET case_name = '" . addslashes($full_name) . "'
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
						//echo $sql . "\r\n";  
					} else {
						$arrCaseName = explode(" " . $v_sep . " ", $case_name);
						$new_case_name = $full_name . " vs " . $arrCaseName[1];
						$sql = "
						UPDATE cse_case 
						SET case_name = '" . addslashes($new_case_name) . "'
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
					}
					$stmt = DB::run($sql);
				}
			}
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	exit();
}
function updatePerson() {
	if (($_SESSION['user_customer_id']==1033)) {
		updatePersonX();
		return;
	}
	
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$full_name = "";
	$first_name = "";
	$last_name = "";
	$salutation = "";
	$ssn1 = "";
	$ssn2 = "";
	$ssn3 = "";
	$blnSSN = false;
	$blnFullName = false;
	$blnLastName = false;
	$blnFirstName = false;
	$blnApplyToChildren = false;
	$person_uuid = "";
	$case_uuid = "";
	$case_id = "";
	$injury_id = "";
	$injury_uuid = "";
	$representing = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if (strpos($fieldname, "_person")!==false) {
			continue;
		}
		if (strpos($fieldname, "token-")!==false) {
			continue;
		}
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			if ($value!="" && $value!="-1") {
				$kase = getKaseInfo($value);
				
				$case_uuid = $kase->uuid;
			}
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="representing") {
			$representing = $value;
			continue;
		}
		//apply changes to children if this is a paretn
		if ($fieldname == "confirm_apply_decide") {
			$blnApplyToChildren = ($value=="Y");
			continue;
		}
		if ($fieldname=="case_uuid" || $fieldname=="person_uuid" || $fieldname=="person_id" || $fieldname=="injury_id") {
			continue;
		}
		if ($fieldname=="full_name") {
			$full_name = $value;
			$blnFullName = true;
		}
		if ($fieldname=="first_name") {
			$first_name = $value;
			//$blnFullName = true;
			$blnFirstName = true;
		}
		if ($fieldname=="last_name") {
			$last_name = $value;
			$blnLastName = true;
		}
		if ($fieldname=="salutation") {
			$salutation = $value;
		}
		//ssn
		if ($fieldname=="ssn1"){
			$blnSSN = true;
			//encrypt here
			$ssn1 = $value;
			continue;
		}
		if ($fieldname=="ssn2"){
			//encrypt here
			$ssn2 = $value;
			continue;
		}
		if ($fieldname=="ssn3"){
			$ssn3 = $value;
			continue;
		}
		if ($fieldname=="dob") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			}
		}
		if ($fieldname=="age") {
			if ($value=="" || $value=="Please enter DOB"){
				$value = 0;
			}
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			//let's look up for uuid
			$person = getPersonInfo($table_id);
			$person_uuid = $person->uuid;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	if (!$blnFullName && $blnFirstName && $blnLastName) {
		//fullname
		$arrSet[] = "`full_name` = '" . addslashes(trim($first_name) . " " . trim($last_name)) . "'";
	}
	if ($blnLastName || $blnFirstName) {
		if ($first_name.$last_name=="" && $full_name!="") {
			//fullname only
			$arrNames = explode(" ", trim($full_name));
			//$arrFields[] = "`first_name`";
			$arrSet[] = "`first_name` = '" . addslashes(trim($arrNames[0])) . "'";
			//$arrFields[] = "`last_name`";
			$arrSet[] = "`last_name` = '" . addslashes(trim($arrNames[count($arrNames)-1])) . "'";
			unset($arrNames[count($arrNames)-1]);
			unset($arrNames[0]);
			if (count($arrNames) > 0) {
				//$arrFields[] = "`middle_name`";
				$arrSet[] = "`middle_name` = '" . addslashes(implode(" ", $arrNames)) . "'";
			}
		}
	}
	
	if ($blnFullName && !$blnFirstName && !$blnLastName) {
		$arrNames = explode(" ", trim($full_name));
		
		$arrSet[] = "`first_name` = '" . addslashes($arrNames[0]) . "'";
		unset($arrNames[0]);
		if (count($arrNames) > 1) {
			$arrSet[] = "`middle_name` = '" . addslashes($arrNames[1]) . "'";
			unset($arrNames[1]);
		}
		$arrSet[] = "`last_name` = '" . addslashes(implode(" ", $arrNames)) . "'";
	}
	//gender
	if ($salutation!="") {
		$gender = "F";
		if ($salutation == "Mr") {
			$gender = "M";
		}
		$arrSet[] = "`gender` = '" . $gender . "'";
	}
	if ($blnSSN) {
		//ssn
		while(strlen($ssn1) < 3) {
			$ssn1 .= "X";
		}
		while(strlen($ssn2) < 2) {
			$ssn2 .= "X";
		}
		while(strlen($ssn3) < 4) {
			$ssn3 .= "X";
		}
		$ssn = $ssn1 . $ssn2 . $ssn3;
		$arrSet[] = "`ssn` = '" . $ssn . "'";
	}
	$arrSet[] = "`ssn_last_four` = '" . $ssn3 . "'";
	//where
	$where_clause = "`" . $table_name . "_id`" . $where_clause . "
	AND `customer_id` = " . $_SESSION['user_customer_id'];
	
	//actual query
	$sql = "UPDATE `cse_" . $table_name . "`
	SET " . implode(",
	", $arrSet) . "
	WHERE " . $where_clause;
	
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("person_id", $table_id);
		$stmt->execute();
		
		if ($case_uuid!="" && $person_uuid!="") {
			//delete all other people
			//clear out any previously attached
			$sql = "UPDATE `cse_case_person` 
			SET  `deleted` =  'Y' 
			WHERE `case_uuid` LIKE  '" . $case_uuid . "'
			AND `person_uuid` NOT LIKE '" . $person_uuid . "'
			AND `attribute` = 'main'
			AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
			
			$stmt = DB::run($sql);
		}
		if ($blnApplyToChildren == true) {
		
			$sql = "UPDATE cse_person child, cse_person parent
			SET child.full_name = parent.full_name,
			child.company_name = parent.company_name,
			child.phone = parent.phone,
			child.fax = parent.fax,
			child.full_address = parent.full_address
			WHERE child.parent_person_uuid = parent.person_uuid
			AND child.person_uuid != child.parent_person_uuid
			AND child.parent_person_uuid = '" . $person->uuid . "'";
			//die($sql);
			$stmt = DB::run($sql);
		}
		
		if ($case_id!="") {
			//update the case name
			$case_type = $kase->case_type;
			$case_name = $kase->case_name;
			if (strpos($case_type, "WC") === false && strpos($case_type, "W/C") === false  && strpos($case_type, "Worker") === false) {
				//pi
				$vs_pos = strpos($case_name, " vs ");
				$v_sep = "vs";
				if ($vs_pos===false) {
					//try again
					$vs_pos = strpos($case_name, " v. ");
					$v_sep = "v.";
				}
				//vestigial cases from tritek
				if ($vs_pos===false || $case_name=="") {
					$sql = "
					UPDATE cse_case 
					SET case_name = '" . addslashes($full_name) . "'
					WHERE case_id = " . $case_id;
					$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
					//echo $sql . "\r\n";  
				} else {
					$arrCaseName = explode(" " . $v_sep . " ", $case_name);
					/*
					$sql = "
					UPDATE cse_case 
					SET case_name = CONCAT('" . addslashes($full_name) . "', `case_name`)
					WHERE case_id = " . $case_id;
					$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
					*/
					$new_case_name = $full_name . " vs " . $arrCaseName[1];
					$sql = "
					UPDATE cse_case 
					SET case_name = '" . addslashes($new_case_name) . "'
					WHERE case_id = " . $case_id;
					$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
				}
				$stmt = DB::run($sql);
			} else {
				//maybe not quite right			
				if (strpos($case_name, $full_name)===false) {
					$vs_pos = strpos($case_name, " vs ");
					$v_sep = "vs";
					if ($vs_pos===false) {
						//try again
						$vs_pos = strpos($case_name, " v. ");
						$v_sep = "v.";
					}
					//brand new or update?
					if ($vs_pos===false) {
						$sql = "
						UPDATE cse_case 
						SET case_name = '" . addslashes($full_name) . "'
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
						//echo $sql . "\r\n";  
					} else {
						$arrCaseName = explode(" " . $v_sep . " ", $case_name);
						$new_case_name = $full_name . " vs " . $arrCaseName[1];
						$sql = "
						UPDATE cse_case 
						SET case_name = '" . addslashes($new_case_name) . "'
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
					}
					$stmt = DB::run($sql);
				}
			}
		}
		trackPerson("update", $table_id);
		echo json_encode(array("success"=>$table_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	exit();	
}
function trackPerson($operation, $person_id) {
	$sql = "INSERT INTO cse_person_track (`user_uuid`, `user_logon`, `operation`, `person_id`, `person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `other_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `person_id`, `person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `other_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`
	FROM cse_person
	WHERE 1
	AND person_id = " . $person_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		//new the case_uuid
		$kase = getKaseInfoByApplicant($person_id);
		$kase_uuid = "";
		if (is_object($kase)) {
			$kase_uuid = $kase->uuid;	
		}
		$activity_category = "Parties";
		switch($operation){
			case "insert":
				$operation .= "ed";
				break;
			case "update":
				$operation .= "d";
				break;
			case "delete":
				$operation .= "d";
				break;
		}
		$activity_uuid = uniqid("KS", false);
		$activity = "Applicant Information was " . $operation . "  by " . $_SESSION['user_name'];
		//die($activity);
		recordActivity($operation, $activity, $kase_uuid, $new_id, $activity_category);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function getClientDOBEmailsByMonth($month) {
	getClientDOBEmails($month);
}
function getClientDOBEmails($month = "") {
	session_write_close();
	
	$customer_id = $_SESSION['user_customer_id'];
	if ($month=="") {
		$month = date("n");
	}
	$sql = "SELECT DISTINCT pers.person_id id, 'applicant' `type`, pers.full_name, pers.first_name, pers.last_name, 
	pers.full_address, IF(pers.language='English', '', pers.language) language,
	REPLACE(pers.email, ' ', '') email, dob
	FROM cse_case ccase
	LEFT OUTER JOIN cse_case_person ccp
	ON ccase.case_uuid = ccp.case_uuid
	LEFT OUTER JOIN ";
	if ($customer_id==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " pers
	ON ccp.person_uuid = pers.person_uuid
	WHERE ccase.customer_id = :customer_id
	AND INSTR(pers.full_name, '*No Name') = 0 
	AND INSTR(pers.email, '@') > 0 
	AND IFNULL(pers.email, '') != ''
	AND MONTH(dob) = '" . $month . "'
	UNION
	
	SELECT DISTINCT corp.corporation_id id, 'plaintiff' `type`, corp.full_name, corp.first_name, corp.last_name, 
	corp.full_address, '' language,
	REPLACE(corp.email, ' ', '') email, dob
	FROM cse_case ccase
	LEFT OUTER JOIN cse_case_corporation ccc
	ON ccase.case_uuid = ccc.case_uuid AND ccc.attribute = 'plaintiff'
	LEFT OUTER JOIN cse_corporation corp
	ON ccc.corporation_uuid = corp.corporation_uuid
	WHERE ccase.customer_id = :customer_id
	AND INSTR(corp.full_name, '*No Name') = 0 
	AND INSTR(corp.email, '@') > 0 
	AND IFNULL(corp.email, '') != ''
	AND MONTH(dob) = '" . $month . "'
	ORDER BY TRIM(last_name) ASC, TRIM(first_name) ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
