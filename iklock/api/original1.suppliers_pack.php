<?php
//get
$app->get('/suppliers', authorize('user'), 'getCorporations');
$app->get('/supplier/:corporation_id', authorize('user'), 'getCorporation');
//'$app->get('/supplier/search/:query', authorize('user'), 'searchCorporation');

//posts
$app->post('/supplier/add', authorize('user'), 'addCorporation');
$app->post('/supplier/update', authorize('user'), 'updateCorporation');

function getCorporations() {
	
	if (isset($_GET["q"])) {
		$query = passed_var("q", "get");
		if ($query!="") {
			searchCorporation($query);
			return;
		}
	}
	
    $sql = "SELECT corp.*, corp.corporation_id id 
			FROM `rek_corporation` corp 
			WHERE corp.deleted = 'N'
			AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by corp.corporation_id";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$corporations = $stmt->fetchAll(PDO::FETCH_OBJ);
	
		$db = null;
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($corporations);
        } else {
            echo $_GET['callback'] . '(' . json_encode($corporations) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCorporation($corporation_id) {
    $sql = "SELECT corp.*, corp.corporation_id id 
			FROM `rek_corporation` corp 
			WHERE corp.corporation_id = :corporation_id
			AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
			AND corp.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("corporation_id", $corporation_id);
		$stmt->execute();
		$corporation = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($corporation);
        } else {
            echo $_GET['callback'] . '(' . json_encode($corporation) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function searchCorporation($query) {
	$query = clean_html($query);
	//$query = str_replace("_", " ", $query);
	$query = trim($query);
	
	if (strlen($query) < 2) {
			return false;
			//getKases();
	}
	//WHERE INSTR(firm_name,:search_term) > 0
    $sql = "SELECT DISTINCT  corp.*, corp.corporation_id id, corp.company_name name 
			FROM `rek_corporation` corp
			WHERE corp.deleted = 'N'
			AND (  INSTR(corp.company_name,:query) > 0
			OR corp.full_name LIKE '%" . $query . "%'
			OR corp.last_name LIKE '%" . $query . "%'
			OR corp.first_name LIKE '%" . $query . "%')";
			
	$sql .= " ORDER by IF (TRIM(corp.company_name) = '', TRIM(corp.full_name), TRIM(corp.first_name)), corp.last_name";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("query", $query);
		$stmt->execute();
		$corporations = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($corporations);
        } else {
            echo $_GET['callback'] . '(' . json_encode($corporations) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addCorporation() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "corporation";

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
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id));
		//track now
		//trackcorporation("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function updateCorporation() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "corporation";

	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		//$fieldname = str_replace("Input", "", $fieldname);
		//$license_number = "";
		if ($fieldname=="first_name") {
			$first_name = $value;
			continue;
		}
		if ($fieldname=="corporation_id") {
			$corporation_id = $value;
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
	$sql = "UPDATE `rek`.`rek_corporation`
			SET 
			   `first_name` = " . $first_name . ",
			   `last_name` = " . $last_name . ",
			   `license_number` = " . $license_number . ",
			   `dob` = " . $dob . ",
			   `phone` = " . $phone . ",
			WHERE `corporation_id` = '" . $corporation_id . "'";
	//$sql = "UPDATE `rek_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
		//VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id));
		//track now
		//trackcorporation("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
?>