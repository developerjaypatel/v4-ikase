<?php
$app->group('', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->group('/personx', function (\Slim\Routing\RouteCollectorProxy $app) {
		$app->get('', 'getPersonXs');
		$app->get('/tokeninput', 'getTokenPersonXs');
		$app->get('/{id}', 'getPersonX');

		$app->post('/delete', 'deletePersonX');
		$app->post('/add', 'addPersonX');
		$app->post('/update', 'updatePersonX');
	});
	$app->get('/personemailx', 'getPersonXEmails');
})->add(\Api\Middleware\Authorize::class);

function getPersonXEmails() {
	getPersonXs("email");
}
function getPersonXs($filter = "") {
	if ($filter != "") {
		$filter = " AND AES_DECRYPT(pers.`" . $filter . "`, '" . CRYPT_KEY . "') != '' ";
	}
    $sql = "SELECT 
			pers.`personx_id`,
			pers.`personx_uuid`,
			pers.`parent_personx_uuid`,
			AES_DECRYPT(pers.`full_name`, '" . CRYPT_KEY . "') `full_name`,
			AES_DECRYPT(pers.`company_name`, '" . CRYPT_KEY . "') `company_name`,
			AES_DECRYPT(pers.`first_name`, '" . CRYPT_KEY . "') `first_name`,
			AES_DECRYPT(pers.`middle_name`, '" . CRYPT_KEY . "') `middle_name`,
			AES_DECRYPT(pers.`last_name`, '" . CRYPT_KEY . "') `last_name`,
			AES_DECRYPT(pers.`aka`, '" . CRYPT_KEY . "') `aka`,
			AES_DECRYPT(pers.`preferred_name`, '" . CRYPT_KEY . "') `preferred_name`,
			AES_DECRYPT(pers.`full_address`, '" . CRYPT_KEY . "') `full_address`,
			pers.`longitude`,
			pers.`latitude`,
			AES_DECRYPT(pers.`street`, '" . CRYPT_KEY . "') `street`,
			pers.`city`,
			pers.`state`,
			pers.`zip`,
			AES_DECRYPT(pers.`suite`, '" . CRYPT_KEY . "') `suite`,
			AES_DECRYPT(pers.`phone`, '" . CRYPT_KEY . "') `phone`,
			AES_DECRYPT(pers.`email`, '" . CRYPT_KEY . "') `email`,
			AES_DECRYPT(pers.`fax`, '" . CRYPT_KEY . "') `fax`,
			AES_DECRYPT(pers.`work_phone`, '" . CRYPT_KEY . "') `work_phone`,
			AES_DECRYPT(pers.`cell_phone`, '" . CRYPT_KEY . "') `cell_phone`,
			AES_DECRYPT(pers.`other_phone`, '" . CRYPT_KEY . "') `other_phone`,
			AES_DECRYPT(pers.`work_email`, '" . CRYPT_KEY . "') `work_email`,
			AES_DECRYPT(pers.`ssn`, '" . CRYPT_KEY . "') `ssn`,
			AES_DECRYPT(pers.`ssn_last_four`, '" . CRYPT_KEY . "') `ssn_last_four`,
			AES_DECRYPT(pers.`dob`, '" . CRYPT_KEY . "') `dob`,
			AES_DECRYPT(pers.`license_number`, '" . CRYPT_KEY . "') `license_number`,
			pers.`title`,
			AES_DECRYPT(pers.`ref_source`, '" . CRYPT_KEY . "') `ref_source`,
			AES_DECRYPT(pers.`salutation`, '" . CRYPT_KEY . "') `salutation`,
			pers.`age`,
			pers.`priority_flag`,
			pers.`gender`,
			pers.`language`,
			AES_DECRYPT(pers.`birth_state`, '" . CRYPT_KEY . "') `birth_state`,
			AES_DECRYPT(pers.`birth_city`, '" . CRYPT_KEY . "') `birth_city`,
			pers.`marital_status`,
			pers.`legal_status`,
			AES_DECRYPT(pers.`spouse`, '" . CRYPT_KEY . "') `spouse`,
			AES_DECRYPT(pers.`spouse_contact`, '" . CRYPT_KEY . "') `spouse_contact`,
			AES_DECRYPT(pers.`emergency`, '" . CRYPT_KEY . "') `emergency`,
			AES_DECRYPT(pers.`emergency_contact`, '" . CRYPT_KEY . "') `emergency_contact`,
			pers.`last_updated_date`,
			pers.`last_update_user`,
			pers.`deleted`,
			pers.`customer_id`,
			pers.personx_id id, pers.personx_uuid uuid
			  
			FROM `cse_personx` pers 
			WHERE pers.deleted = 'N'
			AND pers.customer_id = " . $_SESSION['user_customer_id'] . $filter . "
			ORDER by pers.personx_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$personxs = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($personxs);
        } else {
            echo $_GET['callback'] . '(' . json_encode($personxs) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTokenPersonXs() {
	$search_term = passed_var("q", "get");
    $sql = "SELECT 
			pers.`personx_id`,
			pers.`personx_uuid`,
			pers.`parent_personx_uuid`,
			AES_DECRYPT(pers.`full_name`, '" . CRYPT_KEY . "') `full_name`,
			AES_DECRYPT(pers.`company_name`, '" . CRYPT_KEY . "') `company_name`,
			AES_DECRYPT(pers.`first_name`, '" . CRYPT_KEY . "') `first_name`,
			AES_DECRYPT(pers.`middle_name`, '" . CRYPT_KEY . "') `middle_name`,
			AES_DECRYPT(pers.`last_name`, '" . CRYPT_KEY . "') `last_name`,
			AES_DECRYPT(pers.`aka`, '" . CRYPT_KEY . "') `aka`,
			AES_DECRYPT(pers.`preferred_name`, '" . CRYPT_KEY . "') `preferred_name`,
			AES_DECRYPT(pers.`full_address`, '" . CRYPT_KEY . "') `full_address`,
			pers.`longitude`,
			pers.`latitude`,
			AES_DECRYPT(pers.`street`, '" . CRYPT_KEY . "') `street`,
			pers.`city`,
			pers.`state`,
			pers.`zip`,
			AES_DECRYPT(pers.`suite`, '" . CRYPT_KEY . "') `suite`,
			AES_DECRYPT(pers.`phone`, '" . CRYPT_KEY . "') `phone`,
			AES_DECRYPT(pers.`email`, '" . CRYPT_KEY . "') `email`,
			AES_DECRYPT(pers.`fax`, '" . CRYPT_KEY . "') `fax`,
			AES_DECRYPT(pers.`work_phone`, '" . CRYPT_KEY . "') `work_phone`,
			AES_DECRYPT(pers.`cell_phone`, '" . CRYPT_KEY . "') `cell_phone`,
			AES_DECRYPT(pers.`other_phone`, '" . CRYPT_KEY . "') `other_phone`,
			AES_DECRYPT(pers.`work_email`, '" . CRYPT_KEY . "') `work_email`,
			AES_DECRYPT(pers.`ssn`, '" . CRYPT_KEY . "') `ssn`,
			AES_DECRYPT(pers.`ssn_last_four`, '" . CRYPT_KEY . "') `ssn_last_four`,
			AES_DECRYPT(pers.`dob`, '" . CRYPT_KEY . "') `dob`,
			AES_DECRYPT(pers.`license_number`, '" . CRYPT_KEY . "') `license_number`,
			pers.`title`,
			AES_DECRYPT(pers.`ref_source`, '" . CRYPT_KEY . "') `ref_source`,
			AES_DECRYPT(pers.`salutation`, '" . CRYPT_KEY . "') `salutation`,
			pers.`age`,
			pers.`priority_flag`,
			pers.`gender`,
			pers.`language`,
			AES_DECRYPT(pers.`birth_state`, '" . CRYPT_KEY . "') `birth_state`,
			AES_DECRYPT(pers.`birth_city`, '" . CRYPT_KEY . "') `birth_city`,
			pers.`marital_status`,
			pers.`legal_status`,
			AES_DECRYPT(pers.`spouse`, '" . CRYPT_KEY . "') `spouse`,
			AES_DECRYPT(pers.`spouse_contact`, '" . CRYPT_KEY . "') `spouse_contact`,
			AES_DECRYPT(pers.`emergency`, '" . CRYPT_KEY . "') `emergency`,
			AES_DECRYPT(pers.`emergency_contact`, '" . CRYPT_KEY . "') `emergency_contact`,
			pers.`last_updated_date`,
			pers.`last_update_user`,
			pers.`deleted`,
			pers.`customer_id`,
			pers.personx_id id, pers.personx_uuid uuid
			  
			FROM `cse_personx` pers 
			WHERE pers.deleted = 'N'
			AND pers.personx_uuid = pers.parent_personx_uuid
			AND pers.customer_id = " . $_SESSION['user_customer_id'];
			if ($search_term != "") {	
				$sql .= " AND (";
				$arrSearch[] = " AES_DECRYPT(pers.`full_name`, '" . CRYPT_KEY . "') LIKE '%" . $search_term . "%' ";
			
				$sql .= implode(" OR ", $arrSearch);
				$sql .= ")";
			} 
	$sql .=" ORDER by AES_DECRYPT(pers.`full_name`, '" . CRYPT_KEY . "')";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$personxs = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($personxs);
        } else {
            echo $_GET['callback'] . '(' . json_encode($personxs) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getPersonX($id) {
    $sql = "SELECT 
			pers.`personx_id`,
    pers.`personx_uuid`,
    pers.`parent_personx_uuid`,
    AES_DECRYPT(pers.`full_name`, '" . CRYPT_KEY . "') `full_name`,
    AES_DECRYPT(pers.`company_name`, '" . CRYPT_KEY . "') `company_name`,
    AES_DECRYPT(pers.`first_name`, '" . CRYPT_KEY . "') `first_name`,
    AES_DECRYPT(pers.`middle_name`, '" . CRYPT_KEY . "') `middle_name`,
    AES_DECRYPT(pers.`last_name`, '" . CRYPT_KEY . "') `last_name`,
    AES_DECRYPT(pers.`aka`, '" . CRYPT_KEY . "') `aka`,
    AES_DECRYPT(pers.`preferred_name`, '" . CRYPT_KEY . "') `preferred_name`,
    AES_DECRYPT(pers.`full_address`, '" . CRYPT_KEY . "') `full_address`,
    pers.`longitude`,
    pers.`latitude`,
    AES_DECRYPT(pers.`street`, '" . CRYPT_KEY . "') `street`,
    pers.`city`,
    pers.`state`,
    pers.`zip`,
    AES_DECRYPT(pers.`suite`, '" . CRYPT_KEY . "') `suite`,
    AES_DECRYPT(pers.`phone`, '" . CRYPT_KEY . "') `phone`,
    AES_DECRYPT(pers.`email`, '" . CRYPT_KEY . "') `email`,
    AES_DECRYPT(pers.`fax`, '" . CRYPT_KEY . "') `fax`,
    AES_DECRYPT(pers.`work_phone`, '" . CRYPT_KEY . "') `work_phone`,
    AES_DECRYPT(pers.`cell_phone`, '" . CRYPT_KEY . "') `cell_phone`,
	AES_DECRYPT(pers.`other_phone`, '" . CRYPT_KEY . "') `other_phone`,
	AES_DECRYPT(pers.`other_phone`, '" . CRYPT_KEY . "') `other_phone`,
    AES_DECRYPT(pers.`work_email`, '" . CRYPT_KEY . "') `work_email`,
    AES_DECRYPT(pers.`ssn`, '" . CRYPT_KEY . "') `ssn`,
    AES_DECRYPT(pers.`ssn_last_four`, '" . CRYPT_KEY . "') `ssn_last_four`,
    AES_DECRYPT(pers.`dob`, '" . CRYPT_KEY . "') `dob`,
    AES_DECRYPT(pers.`license_number`, '" . CRYPT_KEY . "') `license_number`,
    pers.`title`,
    AES_DECRYPT(pers.`ref_source`, '" . CRYPT_KEY . "') `ref_source`,
    AES_DECRYPT(pers.`salutation`, '" . CRYPT_KEY . "') `salutation`,
    pers.`age`,
    pers.`priority_flag`,
    pers.`gender`,
    pers.`language`,
    AES_DECRYPT(pers.`birth_state`, '" . CRYPT_KEY . "') `birth_state`,
    AES_DECRYPT(pers.`birth_city`, '" . CRYPT_KEY . "') `birth_city`,
    pers.`marital_status`,
    pers.`legal_status`,
    AES_DECRYPT(pers.`spouse`, '" . CRYPT_KEY . "') `spouse`,
    AES_DECRYPT(pers.`spouse_contact`, '" . CRYPT_KEY . "') `spouse_contact`,
    AES_DECRYPT(pers.`emergency`, '" . CRYPT_KEY . "') `emergency`,
    AES_DECRYPT(pers.`emergency_contact`, '" . CRYPT_KEY . "') `emergency_contact`,
    pers.`last_updated_date`,
    pers.`last_update_user`,
    pers.`deleted`,
    pers.`customer_id`,
			pers.personx_id id, pers.personx_uuid uuid
			FROM `cse_personx` pers 
			WHERE pers.personx_id=:id
			AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
			AND pers.deleted = 'N'";
	//echo $sql . "\r\n";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$personx = $stmt->fetchObject();

		//die(print_r($personx));
		
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($personx);
        } else {
            echo $_GET['callback'] . '(' . json_encode($personx) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPersonXInfo($id) {
	//return a row if id is valid
	$sql = "SELECT 
			pers.`personx_id`,
    pers.`personx_uuid`,
    pers.`parent_personx_uuid`,
    AES_DECRYPT(pers.`full_name`, '" . CRYPT_KEY . "') `full_name`,
    AES_DECRYPT(pers.`company_name`, '" . CRYPT_KEY . "') `company_name`,
    AES_DECRYPT(pers.`first_name`, '" . CRYPT_KEY . "') `first_name`,
    AES_DECRYPT(pers.`middle_name`, '" . CRYPT_KEY . "') `middle_name`,
    AES_DECRYPT(pers.`last_name`, '" . CRYPT_KEY . "') `last_name`,
    AES_DECRYPT(pers.`aka`, '" . CRYPT_KEY . "') `aka`,
    AES_DECRYPT(pers.`preferred_name`, '" . CRYPT_KEY . "') `preferred_name`,
    AES_DECRYPT(pers.`full_address`, '" . CRYPT_KEY . "') `full_address`,
    pers.`longitude`,
    pers.`latitude`,
    AES_DECRYPT(pers.`street`, '" . CRYPT_KEY . "') `street`,
    pers.`city`,
    pers.`state`,
    pers.`zip`,
    AES_DECRYPT(pers.`suite`, '" . CRYPT_KEY . "') `suite`,
    AES_DECRYPT(pers.`phone`, '" . CRYPT_KEY . "') `phone`,
    AES_DECRYPT(pers.`email`, '" . CRYPT_KEY . "') `email`,
    AES_DECRYPT(pers.`fax`, '" . CRYPT_KEY . "') `fax`,
    AES_DECRYPT(pers.`work_phone`, '" . CRYPT_KEY . "') `work_phone`,
    AES_DECRYPT(pers.`cell_phone`, '" . CRYPT_KEY . "') `cell_phone`,
	AES_DECRYPT(pers.`other_phone`, '" . CRYPT_KEY . "') `other_phone`,
	AES_DECRYPT(pers.`other_phone`, '" . CRYPT_KEY . "') `other_phone`,
    AES_DECRYPT(pers.`work_email`, '" . CRYPT_KEY . "') `work_email`,
    AES_DECRYPT(pers.`ssn`, '" . CRYPT_KEY . "') `ssn`,
    AES_DECRYPT(pers.`ssn_last_four`, '" . CRYPT_KEY . "') `ssn_last_four`,
    AES_DECRYPT(pers.`dob`, '" . CRYPT_KEY . "') `dob`,
    AES_DECRYPT(pers.`license_number`, '" . CRYPT_KEY . "') `license_number`,
    pers.`title`,
    AES_DECRYPT(pers.`ref_source`, '" . CRYPT_KEY . "') `ref_source`,
    AES_DECRYPT(pers.`salutation`, '" . CRYPT_KEY . "') `salutation`,
    pers.`age`,
    pers.`priority_flag`,
    pers.`gender`,
    pers.`language`,
    AES_DECRYPT(pers.`birth_state`, '" . CRYPT_KEY . "') `birth_state`,
    AES_DECRYPT(pers.`birth_city`, '" . CRYPT_KEY . "') `birth_city`,
    pers.`marital_status`,
    pers.`legal_status`,
    AES_DECRYPT(pers.`spouse`, '" . CRYPT_KEY . "') `spouse`,
    AES_DECRYPT(pers.`spouse_contact`, '" . CRYPT_KEY . "') `spouse_contact`,
    AES_DECRYPT(pers.`emergency`, '" . CRYPT_KEY . "') `emergency`,
    AES_DECRYPT(pers.`emergency_contact`, '" . CRYPT_KEY . "') `emergency_contact`,
    pers.`last_updated_date`,
    pers.`last_update_user`,
    pers.`deleted`,
    pers.`customer_id`,
			pers.personx_id id, pers.personx_uuid uuid
			FROM `cse_personx` pers 
			WHERE pers.personx_id=:id
			AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
			AND pers.deleted = 'N'";
	//echo $sql . "\r\n";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$personx = $stmt->fetchObject();

		//die(print_r($personx));
		
        // Include support for JSONP requests
        return $personx;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deletePersonX() {
	$id = passed_var("personx_id", "post");
	$sql = "UPDATE cse_personx pers
			SET pers.`deleted` = 'Y'
			WHERE `personx_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("personx_id", $id);
		$stmt->execute();
		
		trackPersonX("delete", $id);
		
		echo json_encode(array("success"=>"personx marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addPersonX() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$case_id = "";
	$salutation = "";
	$first_name = "";
	$last_name = "";
	$full_name = "";
	$ssn1 = "";
	$ssn2 = "";
	$ssn3 = "";
	$representing = "";
	$blnChild = false;
	
	$arrEncrypted = array("full_name", "company_name", "first_name", "middle_name", "last_name", "aka", "preferred_name", "full_address", "street", "suite", "suite", "email", "phone", "fax", "work_phone", "cell_phone", "other_phone", "work_email", "dob", "license_number", "ref_source", "salutation", "birth_state", "birth_city", "spouse", "spouse_contact", "emergency", "emergency_contact");
	
	foreach($_POST as $fieldname=>$value) {
		$fieldname = str_replace("Input", "", $fieldname);
		$fieldname = str_replace("applicant_", "", $fieldname);
		if ($fieldname=="person_id") {
			//if it's numeric, it's a look up
			if (is_numeric($value)) {
				if ($value > -1) {
					$parent_personx = getPersonXInfo($value);	
					$value = $parent_personx->full_name;
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
		$value = passed_var($fieldname, "post");
		$blnEncrypted = false;
		
		if ($fieldname=="representing") {
			$representing = $value;
			continue;
		}
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			if ($table_name=="person") {
				$table_name = "personx";
			}
			continue;
		}
		if ($fieldname=="first_name") {
			$first_name = $value;
			if ($first_name=="") {
				//we will extract from full_name later
				continue;
			}
		}
		if ($fieldname=="last_name") {
			$last_name = $value;
			if ($last_name=="") {
				//we will extract from full_name later
				continue;
			}
		}
		if ($fieldname=="full_name") {
			$full_name = $value;
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
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
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
			$kase = getKaseInfo($case_id);
			continue;
		}
		if ($fieldname=="case_uuid" || $fieldname=="table_id" || $fieldname=="person_uuid" || $fieldname=="person_id" || $fieldname=="injury_id") {
			continue;
		}
		//encrypt
		if (in_array($fieldname, $arrEncrypted)) {
			$value = " AES_ENCRYPT('" . addslashes($value) . "', '" . CRYPT_KEY . "')";
			$blnEncrypted = true;
		}
		
		
		$arrFields[] = "`" . $fieldname . "`";
		if (!$blnEncrypted) {
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = $value;
		}
	}
	
	if ($full_name=="") {
		//no fullname
		$arrFields[] = "`full_name`";
		$arrSet[] = "AES_ENCRYPT('" . addslashes($first_name . " " . $last_name) . "', '" . CRYPT_KEY . "')";
		$full_name = $first_name . " " . $last_name;
	}
	//die($first_name.$last_name . " && " . $full_name);
	if ($first_name.$last_name=="" && $full_name!="") {
		//fullname only
		$arrNames = explode(" ", trim($full_name));
		$arrFields[] = "`first_name`";
		$arrSet[] ="AES_ENCRYPT('" . addslashes($arrNames[0]) . "', '" . CRYPT_KEY . "')";
		$arrFields[] = "`last_name`";
		$arrSet[] ="AES_ENCRYPT('" . addslashes($arrNames[count($arrNames)-1]) . "', '" . CRYPT_KEY . "')";
		unset($arrNames[count($arrNames)-1]);
		unset($arrNames[0]);
		if (count($arrNames) > 0) {
			$arrFields[] = "`middle_name`";
			$arrSet[] ="AES_ENCRYPT('" . addslashes(implode(" ", $arrNames)) . "', '" . CRYPT_KEY . "')";
		}
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
	$ssn = "AES_ENCRYPT('" . addslashes($ssn) . "', '" . CRYPT_KEY . "')";
	$arrSet[] = $ssn;
	
	$arrFields[] = "`ssn_last_four`";
	$ssn3 = "AES_ENCRYPT('" . addslashes($ssn3) . "', '" . CRYPT_KEY . "')";
	$arrSet[] = $ssn3;
	
	//we need defaults
	foreach($arrEncrypted as $encrypted) {
		if (!in_array("`" . $encrypted . "`", $arrFields)){
			$arrFields[] = "`" . $encrypted . "`";
			$arrSet[] = " AES_ENCRYPT('', '" . CRYPT_KEY . "')";
		}
	}
	
	//now we start saving
	$db = getConnection();
	$table_uuid = uniqid("KS", false);
	
	//die($kase->uuid);
	
	if ($blnChild) {
		$arrFields[] = "`parent_personx_uuid`";
		$arrSet[] = "'" . $parent_personx->personx_uuid . "'";
	} else {
		$table_uuid = uniqid("RD", false);
		//insert the parent record first
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ", `parent_personx_uuid`) 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ", '" . $table_uuid . "')";
		//echo $sql . "\r\n<br />";
		try { 		
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		//now we create the actual record
		$parent_table_uuid = $table_uuid;
		
		$table_uuid = uniqid("KS", false);
		
		$arrFields[] = "`parent_personx_uuid`";
		$arrSet[] = "'" . $parent_table_uuid . "'";
	}
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(", ", $arrFields) . ") 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "'," . implode(",
			", $arrSet) . ")";
			
	//echo $sql . "\r\n<br />";
	//die();
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
			//echo $sql . "\r\n<br />";
			
			try {
				$stmt = DB::run($sql);
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
			
			//now we have to attach the applicant to the case 
			$sql = "INSERT INTO cse_case_person (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $kase->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			//echo $sql . "\r\n<br />";
			try {
				$stmt = DB::run($sql);
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
		//track now
		trackPersonX("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updatePersonXField() {
	session_write_close();
	$arrEncrypted = array("full_name", "company_name", "first_name", "middle_name", "last_name", "aka", "preferred_name", "full_address", "street", "suite", "suite", "email", "fax", "phone", "work_phone", "cell_phone", "other_phone", "work_email", "dob", "license_number", "ref_source", "salutation", "birth_state", "birth_city", "spouse", "spouse_contact", "emergency", "emergency_contact");
	
	$id = passed_var("id", "post");
	$fieldname = passed_var("fieldname", "post");
	$value = passed_var("value", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	if ($fieldname=="dob") {
		if ($value!="") {
			$value = date("Y-m-d", strtotime($value));
		}
	}
	
	if (in_array($fieldname, $arrEncrypted)) {
		$value = " AES_ENCRYPT('" . addslashes($value) . "', '" . CRYPT_KEY . "')";
		$blnEncrypted = true;
	}
	
	//address
	$arrAddress = array("street", "suite", "city", "state", "zip");
	foreach($arrAddress as $add) {
		if ($fieldname = $add . "_person") {
			$fieldname = $add;
			break;
		}
	}
	
	$sql = "UPDATE cse_personx 
	SET `" . $fieldname . "` = :value
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
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	exit();
}
function updatePersonX() {
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$first_name = "";
	$last_name = "";
	$full_name = "";
	$salutation = "";
	$ssn1 = "";
	$ssn2 = "";
	$ssn3 = "";
	$blnSSN = false;
	$blnFullName = false;
	$blnFirstName = false;
	$blnLastName = false;
	$blnApplyToChildren = false;
	$personx_uuid = "";
	$case_uuid = "";

	$arrEncrypted = array("full_name", "company_name", "first_name", "middle_name", "last_name", "aka", "preferred_name", "full_address", "street", "suite", "suite", "email", "fax", "phone", "work_phone", "cell_phone", "other_phone", "work_email", "dob", "license_number", "ref_source", "salutation", "birth_state", "birth_city", "spouse", "spouse_contact", "emergency", "emergency_contact");
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		$fieldname = str_replace("applicant_", "", $fieldname);
		
		if (strpos($fieldname, "_person")!==false) {
			continue;
		}
		if (strpos($fieldname, "token-")!==false) {
			continue;
		}
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			if ($table_name=="person") {
				$table_name = "personx";
			}
			continue;
		}
		if ($fieldname=="case_id") {
			if ($value!="" && $value=="-1") {
				$kase = getKaseInfo($value);
				$case_uuid = $kase->uuid;
			}
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
		if ($fieldname=="case_uuid" || $fieldname=="personx_uuid" || $fieldname=="personx_id" || $fieldname=="person_uuid" || $fieldname=="person_id" || $fieldname=="injury_id") {
			continue;
		}
		if ($fieldname=="first_name") {
			$first_name = $value;
			$blnFirstName = true;
			continue;
		}
		if ($fieldname=="last_name") {
			$last_name = $value;
			$blnLastName = true;
			continue;
		}
		if ($fieldname=="full_name") {
			$full_name = $value;
			$blnFullName = false;
		}
		if ($fieldname=="salutation") {
			$salutation = $value;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
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
		$blnEncrypted = false;
		//encrypt
		if (in_array($fieldname, $arrEncrypted)) {
			$value = " AES_ENCRYPT('" . addslashes($value) . "', '" . CRYPT_KEY . "')";
			$blnEncrypted = true;
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			//let's look up for uuid
			$personx = getPersonXInfo($table_id);
			$personx_uuid = $personx->uuid;
			$where_clause = " = " . $value;
		} else {
			if (!$blnEncrypted) {
				$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
			} else {
				$arrSet[] = "`" . $fieldname . "` = " . $value;
			}
			$arrFields[] = "`" . $fieldname . "`";
		}
	}
	if (!$blnFullName && $blnFirstName && $blnLastName) {
		//fullname
		//$arrSet[] = "`full_name` = '" . trim(addslashes($first_name . " " . $last_name)) . "'";
		 $arrSet[] = "`full_name` =  AES_ENCRYPT('" . trim(addslashes($first_name . " " . $last_name)) . "', '" . CRYPT_KEY . "')";
	}
	if ($blnLastName || $blnFirstName) {
		if ($first_name.$last_name=="" && $full_name!="") {
			//fullname only
			$arrNames = explode(" ", trim($full_name));
			//$arrFields[] = "`first_name`";
			$arrFields[] = "`first_name`";
			$arrSet[] = "`first_name` = AES_ENCRYPT('" . $arrNames[0] . "', '" . CRYPT_KEY . "')";
			//$arrFields[] = "`last_name`";
			$arrFields[] = "`last_name`";
			$arrSet[] = "`last_name` = AES_ENCRYPT('" . $arrNames[count($arrNames)-1] . "', '" . CRYPT_KEY . "')";
			unset($arrNames[count($arrNames)-1]);
			unset($arrNames[0]);
			if (count($arrNames) > 0) {
				$arrFields[] = "`middle_name`";
				$arrSet[] = "`middle_name` = AES_ENCRYPT('" . implode(" ", $arrNames) . "', '" . CRYPT_KEY . "')";
			}
		} else {
			if ($blnFirstName){
				$arrFields[] = "`first_name`";
				$arrSet[] = "`first_name` = AES_ENCRYPT('" . $first_name . "', '" . CRYPT_KEY . "')";
			}
			if ($blnLastName){
				$arrFields[] = "`last_name`";
				$arrSet[] = "`last_name` = AES_ENCRYPT('" . $last_name . "', '" . CRYPT_KEY . "')";
			}
		}
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
		$ssn = "AES_ENCRYPT('" . addslashes($ssn) . "', '" . CRYPT_KEY . "')";
		$arrSet[] = "`ssn` = " . $ssn;
	
	
		$ssn3 = "AES_ENCRYPT('" . addslashes($ssn3) . "', '" . CRYPT_KEY . "')";
		$arrSet[] = "`ssn_last_four` = " . $ssn3;
	}
	//where
	$where_clause = "`" . $table_name . "_id`" . $where_clause . "
	AND `customer_id` = " . $_SESSION['user_customer_id'];

	//actual query
	$sql = "UPDATE `cse_" . $table_name . "`
	SET " . implode(",
	", $arrSet) . "
	WHERE " . $where_clause;
	
	$sql_update = $sql;
	//die($sql . "\r\n");
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("personx_id", $table_id);
		$stmt->execute();
		
		if ($case_uuid!="" && $personx_uuid!="") {
			//delete all other people
			//clear out any previously attached
			$sql = "UPDATE `cse_case_person` 
			SET  `deleted` =  'Y' 
			WHERE `case_uuid` LIKE  '" . $case_uuid . "'
			AND `person_uuid` NOT LIKE '" . $personx_uuid . "'
			AND `attribute` = 'main'";
			
			$stmt = DB::run($sql);
		}
		if ($blnApplyToChildren == true) {
		
			$sql = "UPDATE cse_personx child, cse_personx parent
			SET child.full_name = parent.full_name,
			child.company_name = parent.company_name,
			child.phone = parent.phone,
			child.fax = parent.fax,
			child.full_address = parent.full_address
			WHERE child.parent_personx_uuid = parent.personx_uuid
			AND child.personx_uuid != child.parent_personx_uuid
			AND child.parent_personx_uuid = '" . $personx->uuid . "'";
			//die($sql);
			$stmt = DB::run($sql);
		}
		trackPersonX("update", $table_id);
		echo json_encode(array("success"=>$table_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function trackPersonX($operation, $personx_id) {
	$sql = "INSERT INTO cse_personx_track (`user_uuid`, `user_logon`, `operation`, `personx_id`, `personx_uuid`, `parent_personx_uuid`, `full_name`, `company_name`, `first_name`, `last_name`, `middle_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `other_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `personx_id`, `personx_uuid`, `parent_personx_uuid`, `full_name`, `company_name`, `first_name`, `last_name`, `middle_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `other_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`
	FROM cse_personx
	WHERE 1
	AND personx_id = " . $personx_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		//new the case_uuid
		$kase = getKaseInfoByApplicant($personx_id);
		$case_uuid = "";
		if (is_object($kase)) {
			$case_uuid = $kase->uuid;
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
		
		$billing_time = 0;
		if (isset($_POST["billing_time"])) {
			$billing_time = passed_var("billing_time", "post");
		}
		recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category, $billing_time);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
