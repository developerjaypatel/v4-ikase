<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/patients', function (RouteCollectorProxy $app) {
		$app->get('', 'getPatients');
		$app->get('/search/{search_term}', 'searchPatients');
	});
	$app->group('/patient', function (RouteCollectorProxy $app) {
		$app->get('/{patient_id}', 'getPatient');
		$app->post('/add', 'addPatient');
		$app->post('/update', 'updatePatient');
	});
})->add(\Api\Middleware\Authorize::class);

function getPatients() {
    $sql = "SELECT pers.*, pers.person_id id 
			FROM `rek_person` pers 
			WHERE pers.deleted = 'N'
			AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by pers.person_id";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$persons = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($persons);
        } else {
            echo $_GET['callback'] . '(' . json_encode($persons) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPatient($person_id) {
    $sql = "SELECT pers.*, pers.person_id id 
			FROM `rek_person` pers 
			WHERE pers.person_id = :person_id
			AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
			AND pers.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("person_id", $person_id);
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
function addPatient() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "person";

	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		//$fieldname = str_replace("Input", "", $fieldname);
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . str_replace("'", "\'", $value) . "'";
	}	
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "INSERT INTO `rek_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
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
}
function updatePatient() {
	$arrFields = array();
	$arrSet = array();

	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		if ($fieldname=="first_name") {
			$first_name = $value;
			continue;
		}
		if ($fieldname=="person_id") {
			$person_id = $value;
			continue;
		}
		if ($fieldname=="last_name"){
			$last_name = $value;
			continue;
		}
		if ($fieldname=="license_number") {
			$license_number = $value;
			continue;
		}
		if ($fieldname=="dob") {
			$dob = $value;
			continue;
		}
		if ($fieldname=="phone") {
			$phone = $value;
			continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . str_replace("'", "\'", $value) . "'";
	}	
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "UPDATE `rek`.`rek_person`
			SET 
			   `first_name` = '" . $first_name . "',
			   `last_name` = '" . $last_name . "',
			   `license_number` = '" . $license_number . "',
			   `dob` = '" . $dob . "',
			   `phone` = '" . $phone . "'
			WHERE `person_id` = '" . $person_id . "'";
	//$sql = "UPDATE `rek_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
		//VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
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
}
function searchPatients($search_term, $modifier = "") {
	$search_term = clean_html($search_term);
	$search_term = str_replace("_", " ", $search_term);
	$search_term = trim($search_term);
	
	if (strlen($search_term) < 2) {
			return false;
			//getKases();
	}
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	//re-initialize the filters
	
	$sql = "SELECT DISTINCT 
			pers.*, pers.person_id id, pers.full_name name 
			FROM `rek_person` pers";

			$sql.="
			WHERE pers.deleted ='N'";
	$sql .= " AND pers.first_name LIKE '%" . addslashes($search_term) . "%'
			OR pers.last_name LIKE '%" . addslashes($search_term) . "%'
			OR pers.license_number LIKE '%" . addslashes($search_term) . "%'
			OR pers.full_name LIKE '%" . addslashes($search_term) . "%'
			OR pers.phone LIKE '%" . addslashes($search_term) . "%'";
			
	$sql .= " ORDER by IF (TRIM(pers.first_name) = '', TRIM(pers.full_name), TRIM(pers.first_name)), pers.last_name";
	//die($sql);
	session_write_close();
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$patients = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		echo json_encode($patients);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
