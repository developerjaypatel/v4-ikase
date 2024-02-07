<?php
//get
$app->get('/recs', authorize('user'), 'getRecs');
$app->get('/recs/patient/:person_id', authorize('user'), 'getPatientRecs');
$app->get('/rec/:person_id', authorize('user'), 'getRec');

//posts
$app->post('/rec/add', authorize('user'), 'addRec');
$app->post('/rec/update', authorize('user'), 'updateRec');

function getRecs() {
    $sql = "SELECT rec.*, rec.rec_id id 
			FROM `rek_rec` rec 
			WHERE rec.`status` = 'I'
			AND rec.customer_id = " . $_SESSION['user_customer_id'] . "
			AND rec.deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$recs = $stmt->fetchAll(PDO::FETCH_OBJ);
	
		$db = null;
        if (!isset($_GET['callback'])) {
            echo json_encode($recs);
        } else {
            echo $_GET['callback'] . '(' . json_encode($recs) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPatientRecs($person_id) {
    $sql = "SELECT rec.*, rec.rec_id id 
			FROM `rek_person` pers 
			INNER JOIN `rek_person_rec` perr
			ON pers.person_id = perr.person_id
			INNER JOIN `rek_rec` rec
			ON perr.rec_id = rec.rec_id
			WHERE pers.`person_id` = " . $person_id . "
			AND rec.customer_id = " . $_SESSION['user_customer_id'] . "
			AND rec.deleted = 'N'
			AND rec.`status` = 'I'";
			//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("person_id", $person_id);
		$stmt = $db->query($sql);
		$patientrecs = $stmt->fetchAll(PDO::FETCH_OBJ);
	
		$db = null;
        if (!isset($_GET['callback'])) {
            echo json_encode($patientrecs);
        } else {
            echo $_GET['callback'] . '(' . json_encode($patientrecs) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getRec($person_id) {
    $sql = "SELECT rec.*, rec.rec_id id 
			FROM `rek_person` pers 
			INNER JOIN `rek_person_rec` perr
			ON pers.person_id = perr.person_id
			INNER JOIN `rek_rec` rec
			ON perr.rec_id = rec.rec_id
			WHERE pers.`person_id` = :person_id
			AND rec.customer_id = " . $_SESSION['user_customer_id'] . "
			AND rec.deleted = 'N'
			AND rec.`status` = 'A'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("person_id", $person_id);
		$stmt->execute();
		$rec = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($rec);
        } else {
            echo $_GET['callback'] . '(' . json_encode($rec) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addRec() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "rec";

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
		
		$sql = "INSERT INTO `rek_person_rec` (`rec_id` ,`rec_uuid`, `person_id`, `customer_id`) 
		VALUES('" . $new_id . "', '" . $table_uuid . "', '" . $person_id . "', '" . $_SESSION['user_customer_id'] . "'";
		//die($sql);
		
		$db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id));
		//track now
		//trackcorporation("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function updateRek() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "rec";

	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		//$fieldname = str_replace("Input", "", $fieldname);
		//$license_number = "";
		if ($fieldname=="first_name") {
			$first_name = $value;
			continue;
		}
		if ($fieldname=="rek_id") {
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