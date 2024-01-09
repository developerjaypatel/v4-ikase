<?php
$app->get('/corporation/kases/:id', authorize('user'), 'getCorporations');
$app->get('/kase/corporation/:case_id/:corporation_id', authorize('user'), 'getKaseCorporation');
$app->get('/kase/corporationbyid/:corporation_id/:case_id', authorize('user'), 'getKaseCorporationByID');
$app->get('/prior_treatments/:person_id', authorize('user'), 'getPriorTreatments');
$app->get('/corporation/tokeninput/:type', authorize('user'), 'getTokenCorporations');
$app->get('/corporation/recipient/:search_term/:case_id/:type', authorize('user'), 'getRecipients');
$app->get('/employees/tokeninput/:id', authorize('user'), 'getTokenCorporationEmployees');
$app->get('/corporation/:type/:id', authorize('user'), 'getCorporation');
$app->get('/corporationinfo/:id', authorize('user'), 'getCorporationInfo');
$app->get('/employers', authorize('user'), 'getEmployers');
$app->get('/corpcasecount/:corporation_id/:type', authorize('user'), 'getCorporationCaseCount');

$app->get('/lostincome/:id', authorize('user'), 'getLostIncome');
$app->get('/kase/lostincome/:case_id', authorize('user'), 'getKaseLostIncome');
$app->get('/kase/lostincometotal/:case_id', authorize('user'), 'getKaseLostIncomeTotal');
$app->get('/corporation/lostincome/:case_id/:corporation_id', authorize('user'), 'getEmployerLostIncome');

$app->post('/lostincome/save', authorize('user'), 'addLostIncome');
$app->post('/lostincome/delete', authorize('user'), 'deleteLostIncome');

$app->post('/judges', authorize('user'), 'getJudges');

//posts
$app->post('/corporation/delete', authorize('user'), 'deleteCorporation');
$app->post('/corporation/import', authorize('user'), 'importCorporation');
$app->post('/corporation/add', authorize('user'), 'addCorporation');
$app->post('/corporation/update', authorize('user'), 'updateCorporation');
$app->post('/corporationkai/update', authorize('user'), 'updateCorporationKai');

$app->post('/corporation/field/update', authorize('user'), 'updateCorporationField');

//qme
$app->post('/qme_check', authorize('user'), 'qmeCheck');

function getCorporationCaseCount($corporation_id, $type) {
	session_write_close();
	
	try {
		$customer_id = $_SESSION["user_customer_id"];
		
		$corp = getCorporationInfo($corporation_id);
		$company_name =  $corp->company_name;
		
		$sql = "SELECT COUNT(ccase.case_id) case_count
		FROM cse_corporation corp
		INNER JOIN cse_case_corporation ccorp
		ON corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.deleted = 'N'
		INNER JOIN cse_case ccase
		ON ccorp.case_uuid = ccase.case_uuid
		WHERE corp.`type` = :type
		AND corp.company_name = :company_name
		AND corp.customer_id = :customer_id
		GROUP BY corp.company_name";	
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("company_name", $company_name);
		$stmt->bindParam("type", $type);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$refer = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($refer);
		exit();
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getEmployers() {
	session_write_close();
	$sql = "SELECT corporation_id, company_name, full_address, IF(phone='', employee_phone, phone) phone
	FROM cse_corporation
	WHERE `type` = 'employer'
	AND corporation_uuid = parent_corporation_uuid
	AND company_name != ''
	AND customer_id = :customer_id
	ORDER BY TRIM(company_name)";
	
	try {
		$customer_id = $_SESSION["user_customer_id"];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$employers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($employers);
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getJudges() {
	session_write_close();
	$venue = passed_var("venue_abbr", "post");
	$venue = str_replace("_", "", $venue);
	
	$sql = "SELECT DISTINCT full_name, preferred_name 
	FROM (
		SELECT DISTINCT full_name, preferred_name 
		FROM ikase.cse_corporation ccorp
		WHERE `type` = 'venue'
		AND full_name != ''
		AND preferred_name != ''
		AND customer_id = :customer_id";
	if ($venue!="") {
		$sql .= " AND `preferred_name` = :venue";
	}
	$sql .= " UNION
		SELECT presiding full_name, venue_abbr preferred_name
		FROM ikase.cse_venue";
		if ($venue!="") {
			$sql .= " WHERE `venue_abbr` = :venue";
		}
		$sql .= " 
	) judges
	ORDER BY full_name, preferred_name";
	
	try {
		$customer_id = $_SESSION["user_customer_id"];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($venue!="") {
			$stmt->bindParam("venue", $venue);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$judges = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

		$arrOptions = array();
        foreach($judges as $judge) {
			$option = "<option value='" . str_replace("'", "~", $judge->full_name) . "'>" . $judge->full_name . " - " . $judge->preferred_name . "</option>";
			$arrOptions[] = $option;
		}
		
		$output = '<select id="judge_dropdown" name="judge_dropdown" style="height:19px; width:418px; margin-top:0px; margin-left:0px; background:white" tabindex="0" placeholder="Judge">' . implode("", $arrOptions) . '</select>';
		
		echo $output;
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function qmeCheck() {
	$name = passed_var("name", "post");
	$name = str_replace("&nbsp;", " ", $name);
	$name = trim($name);
	$phone = trim(passed_var("phone", "post"));
	
	$sql = "SELECT corp.* 
			FROM `cse_corporation` corp  
           
            WHERE corp.deleted = 'N'
            AND corp.corporation_uuid = corp.parent_corporation_uuid
            AND TRIM(`company_name`) = :name
            AND TRIM(`phone`) = :phone
            AND `type` = 'medical_provider'
            ORDER BY corporation_id DESC";
			
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("name", $name);
		$stmt->bindParam("phone", $phone);
		$stmt->execute();
		$corporations = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($kases));
        // Include support for JSONP requests
        echo json_encode($corporations);
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getCorporations($case_id) {
    $sql = "SELECT corp.*, corp.corporation_id id , corp.corporation_uuid uuid, cpt.partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, cpt.adhoc_fields    
			FROM `cse_corporation` corp ";
	$sql .= " INNER JOIN `cse_partie_type` cpt
			ON corp.type = cpt.blurb
			INNER JOIN cse_case_corporation ccp ON corp.corporation_uuid = ccp.corporation_uuid AND ccp.deleted = 'N'
			INNER JOIN cse_case cse ON ccp.case_uuid = cse.case_uuid";	
	$sql .= " WHERE corp.deleted = 'N'";
	$sql .= " AND cse.case_id = :case_id";
	$sql .= " AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by ccp.case_corporation_id";
	//the order by is based on the relationship id so that the first of each kind will easily selected
	if ($_SESSION['user_customer_id'] == "1033") {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$corporations = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($kases));
        // Include support for JSONP requests
        echo json_encode($corporations);
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseLostIncome($case_id) {
	getEmployerLostIncome($case_id, "");
}
function getLostIncome($id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'] ;
	
	$sql = "SELECT linc.*, linc.lostincome_id id 
	FROM cse_lostincome linc
	INNER JOIN cse_case_lostincome cli
	ON linc.lostincome_uuid = cli.lostincome_uuid AND cli.deleted = 'N'
	INNER JOIN cse_case ccase
	ON cli.case_uuid = ccase.case_uuid";
	
	$sql .= "
	WHERE linc.lostincome_id = :id
	AND ccase.customer_id = :customer_id
	AND linc.deleted = 'N'";
	
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$lostincome = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        echo json_encode($lostincome);
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEmployerLostIncome($case_id, $corporation_id = "") {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'] ;
	
	$sql = "SELECT linc.*, linc.lostincome_id id 
	FROM cse_lostincome linc
	INNER JOIN cse_case_lostincome cli
	ON linc.lostincome_uuid = cli.lostincome_uuid
	INNER JOIN cse_case ccase
	ON cli.case_uuid = ccase.case_uuid";
	
	if ($corporation_id!="") {
		$sql .= "
		INNER JOIN cse_case_corporation ccc
		ON ccase.case_uuid = ccc.case_uuid
		INNER JOIN cse_corporation_lostincome ccl
		ON linc.lostincome_uuid = ccl.lostincome_uuid AND ccc.corporation_uuid = ccl.corporation_uuid
		INNER JOIN cse_corporation corp
		ON ccl.corporation_uuid = corp.corporation_uuid";
	}
	
	$sql .= "
	WHERE ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id
	AND linc.deleted = 'N'";
	
	if ($corporation_id!="") {
		$sql .= "
		AND corp.corporation_id = :corporation_id";
	}
	$sql .= "
	ORDER BY linc.start_lost_date";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		if ($corporation_id!="") {
			$stmt->bindParam("corporation_id", $corporation_id);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$lostincomes = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        echo json_encode($lostincomes);
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseLostIncomeTotal($case_id) {
	session_write_close();
	
	$customer_id = $_SESSION['user_customer_id'] ;
	
	$sql = "SELECT IFNULL(SUM(linc.amount), 0) losses
	FROM cse_lostincome linc
	INNER JOIN cse_case_lostincome cli
	ON linc.lostincome_uuid = cli.lostincome_uuid
	INNER JOIN cse_case ccase
	ON cli.case_uuid = ccase.case_uuid
	WHERE ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id
	AND linc.deleted = 'N'";

	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$wage = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        echo json_encode($wage);
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteLostIncome() {
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_lostincome li 
			SET li.`deleted` = 'Y'
			WHERE `lostincome_id`=:id
			AND `customer_id` = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$stmt = null; $db = null;
		echo json_encode(array("success"=>"lostincome marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackLostIncome("delete", $id);
}
function addLostIncome($corporation_id = "") {
	session_write_close();
	
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "lostincome";
	$lostincome_id = passed_var("lostincome_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$corporation_id = "";
	
	//die($table_id . " - table");
	$blnUpdate = (is_numeric($lostincome_id) && $lostincome_id!="" && $lostincome_id > 0);
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="customer_id") {
			continue;
		}
		if ($fieldname=="corporation_id") {
			$corporation_id = $value;
			continue;
		}
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			//die($case_id . " - case_id");
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
			continue;
		}
		if ($fieldname=="lostincome_id") {
			continue;
		}
		
		//if ($fieldname=="dateandtime") {
		if (strpos($fieldname, "_date")!==false || strpos($fieldname, "date_")!==false) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		//die("before");
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
		//die("after");
	}	
	
	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', '" . $customer_id. "', " . implode(",", $arrSet) . ")";
		//die($sql);
		try {
			$db = getConnection();
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$new_id = $db->lastInsertId();
			$stmt = null; $db = null;
			
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			//trackPerson("insert", $new_id);	
			
			$case_table_uuid = uniqid("LI", false);
			$attribute_1 = "main";
			$last_updated_date = date("Y-m-d H:i:s");
			
			//now we have to attach the check to the case 
			$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $kase->uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			$db = getConnection();
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
			$stmt = null; $db = null;
			
			if ($corporation_id != "") {
				$corporation = getCorporationInfo($corporation_id);
				
				$case_table_uuid = uniqid("RC", false);
				$attribute_1 = "main";
				$last_updated_date = date("Y-m-d H:i:s");
				
				//now we have to attach the wage to the corporation
				$sql = "INSERT INTO cse_corporation_" . $table_name . " (`corporation_" . $table_name . "_uuid`, `corporation_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $corporation->uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  	
				$stmt->execute();
				$stmt = null; $db = null;
			}
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		
		//where
		$where_clause = "= '" . $lostincome_id . "'";
		$where_clause = "`" . $table_name . "_id`" . $where_clause . "
		AND `customer_id` = " . $_SESSION['user_customer_id'];

		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET " . implode(",", $arrSet) . "
		WHERE " . $where_clause;
		
		//die(implode(",", $arrSet));
		
		//die($sql);
		
		try {
			$db = getConnection();
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$stmt = null; $db = null;
			
			echo json_encode(array("success"=>true, "id"=>$lostincome_id));
			//track now
			//trackPerson("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
		$db = null;
	}
}
function getKaseCorporationByID($corporation_id) {
	getKaseCorporation("", "", $corporation_id);
}
function getKaseCorporation($case_id, $corporation_id, $corporation_uuid = "") {
	session_write_close();
	
    $sql = "SELECT corp.*, corp.corporation_id id , corp.corporation_uuid uuid, 
	IFNULL(dash.setting_value, 'N') show_dashboard,  
	IFNULL(inj.injury_id, -1) `injury_id`, IFNULL(inj.injury_uuid, '') `injury_uuid`, 
	cpt.partie_type, cpt.employee_title, cpt.color, 
	cpt.blurb, cpt.show_employee, cpt.adhoc_fields, cpt.sort_order    
			FROM `cse_corporation` corp ";
	
	$sql .= " 
	LEFT OUTER JOIN cse_setting dash
	ON corp.type = dash.setting AND dash.category = 'dashboard' AND dash.customer_id = " . $_SESSION['user_customer_id'] . "";
	
	$sql .= " 
	LEFT OUTER JOIN `cse_partie_type` cpt
	ON corp.type = cpt.blurb
	LEFT OUTER JOIN cse_case_corporation ccp 
	ON corp.corporation_uuid = ccp.corporation_uuid AND ccp.deleted = 'N'
	LEFT OUTER JOIN cse_case cse 
	ON ccp.case_uuid = cse.case_uuid
	LEFT OUTER JOIN cse_injury inj
	ON ccp.injury_uuid = inj.injury_uuid";	
	
	$sql .= " WHERE corp.deleted = 'N'";
	if ($case_id!="") {
		$sql .= " AND cse.case_id = :case_id";
	}
	if ($corporation_id!="") {
		$sql .= " AND corp.corporation_id = :corporation_id";
	}
	if ($corporation_uuid!="") {
		$sql .= " AND corp.corporation_uuid = :corporation_uuid";
	}
	$sql .= " AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by ccp.case_corporation_id";
	//the order by is based on the relationship id so that the first of each kind will easily selected
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($case_id!="") {
			$stmt->bindParam("case_id", $case_id);
		}
		if ($corporation_id!="") {
			$stmt->bindParam("corporation_id", $corporation_id);
		}
		if ($corporation_uuid!="") {
			$stmt->bindParam("corporation_uuid", $corporation_uuid);
		}
		$stmt->execute();
		$corporation = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        echo json_encode($corporation);
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseCorporationInfo($case_id, $corporation_id) {
	session_write_close();
	
    $sql = "SELECT corp.*, corp.corporation_id id , corp.corporation_uuid uuid, 
	IFNULL(inj.injury_id, -1) `injury_id`, IFNULL(inj.injury_uuid, '') `injury_uuid`, 
	cpt.partie_type, cpt.employee_title, cpt.color, 
	cpt.blurb, cpt.show_employee, cpt.adhoc_fields    
			FROM `cse_corporation` corp ";
	$sql .= " INNER JOIN `cse_partie_type` cpt
			ON corp.type = cpt.blurb
			INNER JOIN cse_case_corporation ccp ON corp.corporation_uuid = ccp.corporation_uuid AND ccp.deleted = 'N'
			INNER JOIN cse_case cse ON ccp.case_uuid = cse.case_uuid
			LEFT OUTER JOIN cse_injury inj
			ON ccp.injury_uuid = inj.injury_uuid";	
	$sql .= " WHERE 1";
	$sql .= " AND cse.case_id = :case_id";
	$sql .= " AND corp.corporation_id = :corporation_id";
	$sql .= " AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by ccp.case_corporation_id";
	//the order by is based on the relationship id so that the first of each kind will easily selected
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("corporation_id", $corporation_id);
		$stmt->execute();
		$corporation = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $corporation;
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPriorTreatments($person_id) {
	session_write_close();
	
    $sql = "SELECT corp.*, corp.corporation_id id , corp.corporation_uuid uuid, cpt.partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, cpt.adhoc_fields    
			FROM `cse_corporation` corp ";
	$sql .= " INNER JOIN `cse_partie_type` cpt
			ON corp.type = cpt.blurb
			INNER JOIN cse_person_corporation ccp ON corp.corporation_uuid = ccp.corporation_uuid
			INNER JOIN  ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= "  cse ON ccp.person_uuid = cse.person_uuid";	
	$sql .= " WHERE corp.deleted = 'N'";
	$sql .= " AND cse.person_id = :person_id";
	$sql .= " AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by ccp.person_corporation_id";
	//the order by is based on the relationship id so that the first of each kind will easily selected
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("person_id", $person_id);
		$stmt->execute();
		$corporations = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($kases));
        // Include support for JSONP requests
        echo json_encode($corporations);
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getRecipients($search_term, $case_id, $type) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$search_term = str_replace("_", " ", $search_term);
	
	$sql = "SELECT IF(ccase.case_id IS NULL, 99, 0) case_sort,
	IF (corp.corporation_uuid = corp.parent_corporation_uuid, 1, 0) parent_sort,
	ccase.case_id, corp.*
	FROM cse_corporation corp
	
	LEFT OUTER JOIN cse_case_corporation ccorp
	ON corp.corporation_uuid = ccorp.corporation_uuid
	
	LEFT OUTER JOIN cse_case ccase
	ON ccorp.case_uuid = ccase.case_uuid
	
	WHERE INSTR(corp.full_name, :search_term) > 0
	AND corp.`type` = 'recipient'
	AND corp.company_name = :type
	AND corp.customer_id = :customer_id
	ORDER BY IF(ccase.case_id IS NULL, 99, 0), IF (corp.corporation_uuid = corp.parent_corporation_uuid, 1, 0), corp.full_name ASC";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("search_term", $search_term);
		$stmt->bindParam("type", $type);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$recipients = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$arrSkips = array();
        foreach($recipients as $rec) {
			if (in_array($rec->corporation_uuid, $arrSkips)) {
				continue;
			}
			if ($rec->case_id == $case_id) {
				//don't need the parent
				$arrSkips[] = $rec->parent_corporation_uuid;
			}
			//don't need other cases
			if ($rec->case_id != "" && $rec->case_id != $case_id) {
				//don't need it, only parent
				$arrSkips[] = $rec->corporation_uuid;
			}
		}
		
		for($int = count($recipients) - 1; $int > -1; $int--) {
			$rec = $recipients[$int];
			
			if (in_array($rec->corporation_uuid, $arrSkips)) {
				unset($recipients[$int]);
			}
		}
		//we should end up with only parents or corp already associated with this case
		echo json_encode($recipients);
		
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTokenCorporations($type) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$blnReferring = ($type=="referring" || $type=="referral_source");
	
	$search_term = passed_var("q", "get");
    $sql = "SELECT corp.*, corp.corporation_id id , corp.corporation_uuid uuid, cpt.partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, cpt.adhoc_fields, 
	CONCAT(
	corp.`company_name`, '<br>', IFNULL(`street`, `full_address`), ', ', IFNULL(`city`, ''), 
	IF(`full_name`!='' OR `employee_phone`!='', '<br>', ''), 
	IF(`full_name`!='', CONCAT(`full_name`, '&nbsp;'), ''), 
	IF(`employee_phone`!='', `employee_phone`, '')) `name`   
			FROM `cse_corporation` corp ";
	$sql .= " INNER JOIN `cse_partie_type` cpt
			ON corp.type = cpt.blurb";
	$sql .= " INNER JOIN 
			((SELECT 
            MIN(corporation_id) corporation_id
        FROM
            cse_corporation
        WHERE
            corporation_uuid = parent_corporation_uuid
			AND (
			";
			if ($blnReferring) {
				$sql .= "`type` = 'referral_source' OR `type` = 'referring'";	
			} else {
				$sql .= "`type` = :type";	
			}
			$sql .= 
			")
        GROUP BY `type` , company_name , full_address , employee_phone)) min_ids
        ON corp.corporation_id = min_ids.corporation_id";
	$sql .= " WHERE corp.deleted = 'N'";
	if ($blnReferring) {
		$sql .= "AND (corp.`type` = 'referral_source' OR corp.`type` = 'referring')";	
	} else {
		$sql .= " AND corp.type = :type";
	}
	$sql .= " AND corp.customer_id = :customer_id";
	if ($search_term != "") {	
		$sql .= " AND (";
		$arrSearch[] = " corp.`company_name` LIKE '%" . $search_term . "%' ";
		$company_searches = implode(" OR ", $arrSearch);
		$sql .= $company_searches;
		$sql .= ")";
	}
	//rolodex only, uuid=parent_uuid
	$sql .= " AND corp.parent_corporation_uuid = corp.corporation_uuid";
	/*
	$sql .= " AND corp.corporation_id IN (SELECT 
				MIN(corporation_id) corporation_id
				FROM ikase_reino.cse_corporation 
				WHERE corporation_uuid = parent_corporation_uuid
				AND `type` = :type";
	if ($search_term != "") {	
		$sql .= " AND (";
		$sql .= $company_searches;
		$sql .= ")";
	}
		$sql .= " GROUP BY `type`, company_name, full_address, employee_phone)"; 
	*/
	$sql .= " ORDER by `corp`.`company_name`";
	//the order by is based on the relationship id so that the first of each kind will easily selected
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if (!$blnReferring) {
			$stmt->bindParam("type", $type);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$token_corporations = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        echo json_encode($token_corporations);
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTokenCorporationEmployees($id) {
	session_write_close();
	
	if (!isset($_GET["q"])) {
		return false;
	}
	$search_term = passed_var("q", "get");
    $sql = "SELECT DISTINCT corp.corporation_id id, 
	CONCAT(corp.`full_name`, ' (', parent.company_name, ')') `name`
	FROM `cse_corporation` corp 
	INNER JOIN `cse_corporation` parent 
	ON corp.parent_corporation_uuid = parent.corporation_uuid";
	$sql .= " WHERE corp.deleted = 'N'";
	$sql .= " AND parent.corporation_id = :id";
	$sql .= " AND corp.full_name != ''";	
	$sql .= " AND corp.customer_id = " . $_SESSION['user_customer_id'];
	if ($search_term != "") {	
		$sql .= " AND (";
		$arrSearch[] = " corp.`full_name` LIKE '%" . $search_term . "%' ";
		
		$sql .= implode(" OR ", $arrSearch);
		$sql .= ")";
	}
	$sql .= " ORDER by `corp`.`full_name`";
	//the order by is based on the relationship id so that the first of each kind will easily selected
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$token_employees = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        echo json_encode($token_employees);
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCorporation($type, $id) {
	session_write_close();
	$inner = "INNER";
	if ($type=="recipient") {
		$inner = "LEFT OUTER";
	}
	//return a row if id is valid
	if ($id > 0) {
    	$sql = "SELECT corp.*, corp.corporation_id id , corp.corporation_uuid uuid, cpt.partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, cpt.adhoc_fields  ";
		if ($type=="carrier") {
			$sql .= ", IFNULL(`ndoc`.`adhoc_value`, '') `claim_number`";
		}
		$sql .= " FROM `cse_corporation` corp
			" . $inner . " JOIN `cse_partie_type` cpt
			ON corp.type = cpt.blurb";
		if ($type=="carrier") {
			$sql .= " LEFT OUTER JOIN `cse_corporation_adhoc` ndoc
			ON (corp.corporation_uuid = ndoc.corporation_uuid AND ndoc.`deleted` =  'N' 
					AND ndoc.adhoc = 'claim_number')";
		}
		$sql .= " WHERE corp.corporation_id=:id
			AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
			AND corp.deleted = 'N'";
	} else {
		$sql = "SELECT * 
			FROM  `cse_partie_type` 
			WHERE blurb = :type";
	}
	if ($_SERVER['REMOTE_ADDR'] == "172.119.228.204") {
		die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($id > 0) {
			$stmt->bindParam("id", $id);
		} else {
			$stmt->bindParam("type", $type);
		}
		
		$stmt->execute();
		$corporation = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		if ($_SERVER['REMOTE_ADDR'] == "47.153.49.248") {
			//die($sql);
		}
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($corporation);
        } else {
            echo $_GET['callback'] . '(' . json_encode($corporation) . ');';
        }

			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCorporationInfo($id) {
	session_write_close();
	$blnUUID = false;
	if (!is_numeric($id)) {
		if (strlen($id) != 15 && $id!="") {
			die("no no:" . $id);
		} else {
			$blnUUID = true;
		}
	}
	//return a row if id is valid
	$sql = "SELECT corp.*, corp.corporation_id id , corp.corporation_uuid uuid, cpt.partie_type, 
	cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, cpt.adhoc_fields, 
	IFNULL(cad.adhoc_value, '') venue_choice
	FROM `cse_corporation` corp 
	LEFT OUTER JOIN `cse_partie_type` cpt
	ON corp.type = cpt.blurb
	LEFT OUTER JOIN `cse_corporation_adhoc` cad
    ON corp.corporation_uuid = cad.corporation_uuid AND adhoc = 'ven_choice'
	WHERE corp.customer_id = " . $_SESSION['user_customer_id'];
	
	if (!$blnUUID) {
		$sql .= "
		AND corp.corporation_id=:id";
	} else {
		$sql .= "
		AND corp.corporation_uuid=:id";
	}
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		
		$stmt->execute();
		$corporation = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
//die(print_r($corporation));

        return $corporation;
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteCorporation() {
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_corporation corp 
			SET corp.`deleted` = 'Y'
			WHERE `corporation_id`=:id
			AND `customer_id` = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$stmt = null; $db = null;
		echo json_encode(array("success"=>"partie marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackCorporation("delete", $id);
}
function importCorporation() {
	//check if the corporation already exists in the system
	$company_name = passed_var("company_name", "post");
	$type = passed_var("type", "post");
	$street = passed_var("street", "post");
	$city = passed_var("city", "post");
	$state = passed_var("state", "post");
	$zip = passed_var("zip", "post");
	
	$sql = "SELECT corporation_id
	FROM cse_corporation corp
	WHERE `type` = :type
	AND company_name = :company_name
	AND street = :street
	AND city = :city
	AND state = :state
	AND zip = :zip
	AND customer_id = :customer_id
	AND corporation_uuid != parent_corporation_uuid
	LIMIT 0, 1";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("type", $type);
		$stmt->bindParam("company_name", $company_name);
		$stmt->bindParam("street", $street);
		$stmt->bindParam("city", $city);
		$stmt->bindParam("state", $state);
		$stmt->bindParam("zip", $zip);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->execute();
		$corporation = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

		$corporation_id = -1;
        if (is_object($corporation)) {
			$corporation_id = $corporation->corporation_id;
		}
		$_POST["corporation_id"] = $corporation_id;
		
		addCorporation();
		
			exit();
} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addCorporation() {
	session_write_close();
	
	
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	//die(print_r($arrFields));
	$arrAdhocSet = array();
	$table_name = "corporation";
	$table_id = "";
	$injury_uuid = "";
	//KS for kase, RD for rolodex
	$table_uuid = uniqid("KS", false);
	$adhoc_fields = passed_var("adhoc_fields", "post");
	$type = passed_var("type", "post");
	$partie = passed_var("partie", "post");
	$additional_partie = "";
	//$case_uuid = passed_var("case_uuid", "post");
	$arrAdhoc = explode(",", $adhoc_fields);
	$blnChild = false;
	//copying instructions are kept in array and then stored in copying_instuctions field
	$arrCopying = array("medical_copy", "billing_copy", "xray_copy", "claim_copy", "employment_copy", "wage_copy", "other_copy", "other_description", "any_all", "special_instructions");	
	$arrCopyingValues = array();
	$other_description = "";
	$homemedical_uuid = "";
	$any_all = "N";
	$special_instructions = "";
	$full_address = "";
	$street = "";
	$city = "";
	$state = "";
	$zip = "";
	$case_uuid = "";
	$arrExtra = array();
	$case_name = "";
	$additional_full_address = "";
	$arrAdditionalAddress = array();
	//need the kase right away
	if (isset($_POST["case_id"])) {
		$case_id = passed_var("case_id", "post");
		if ($case_id > 0) {
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
			$case_type = $kase->case_type;
			$full_name = $kase->full_name;
			$case_name = $kase->case_name;
		}
	}
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		
		if ($partie!="") {
			if (strpos($fieldname, "_"  . $partie)!==false) {
				continue;
			}
		}
		if (strpos($fieldname, "token-")!==false) {
			continue;
		}
		if (strpos($fieldname, "Extra") > -1) {
			$fieldname = str_replace("Extra", "", $fieldname);
			$arrExtra[$fieldname] = $value; 
			continue;
		}
		if (strpos($fieldname, "additional_") > -1 && $fieldname!="additional_partie") {
			if  ($fieldname=="additional_full_address") {
				$additional_full_address = $value;
			}
			$arrAdditionalAddress["address_2"][] = $value;
			continue;
		}
		
		//if ($fieldname=="company_name") {
		if  ($fieldname=="corporation_id") {
			//wcab only
			if ($case_id > 0) {
				$blnWCAB = checkWCAB($kase->case_type);
			} else {
				$blnWCAB = false;
			}
			
			//if it's numeric, it's a look up
			if (is_numeric($value) && $blnWCAB) {
				if ($value > -1) {
					switch ($type) {
						case "claim":
						case "carrier":
							$parent_corporation = getEamsCarrierInfo($value);	
							break;
						case "defense":
						case "applicant_attorney":
						case "prior_attorney":
							$parent_corporation = getEamsRepInfo($value);
							break;
						default:
							$parent_corporation = getCorporationInfo($value);
					}
					if (!is_object($parent_corporation) || count($parent_corporation)==0) {
						$parent_corporation = getCorporationInfo($value);
					}
					//die(print_r($parent_corporation));
					$value = $parent_corporation->company_name;
					$blnChild = true;
				}
			}
		}
		
		//special case for eams lookup
		/*
		if ($type=="carrier") {
			if ($fieldname=="company_name") {
				//if it's numeric, it's a look up
				if (is_numeric($value)) {
					if ($value > -1) {
						$parent_corporation = getEamsCarrierInfo($value);	
						$value = $parent_corporation->company_name;
						$blnChild = true;
					}
				}
			}
		}
		*/
		
		if ($fieldname=="case_id"){
			$case_id = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="additional_partie") {
			$additional_partie = $value;
			continue;
		}
		if ($fieldname=="full_address") {
			$full_address = $value;
			continue;
		}
		if ($fieldname=="street") {
			$street = $value;
		}
		if ($fieldname=="city") {
			$city = $value;
		}
		if ($fieldname=="state") {
			$state = $value;
		}
		if ($fieldname=="zip") {
			$zip = $value;
		}
		if ($fieldname=="company_name") {
			$company_name = $value;
		}
		if (in_array($fieldname, $arrCopying)) {
			if (strpos($fieldname, "_copy") > -1) {
				if ($value!="") {
					$arrCopyingValues[] = $value;
				}	
			}
			if ($fieldname == "other_description") {
				$other_description = $value;
			}
			if ($fieldname == "any_all") {
				$any_all = $value;
			}
			if ($fieldname == "special_instructions") {
				$special_instructions = $value;
			}
			continue;
		}
		if ($fieldname=="adhoc_fields" || $fieldname=="case_uuid" || $fieldname=="parent_corporation_uuid" || $fieldname=="table_id" || $fieldname=="corporation_id" || $fieldname==$table_name . "_uuid" || $fieldname=="partie" || $fieldname=="partie_type" || $fieldname=="addto_current" || $fieldname=="override_current") {
			//but all the other "id" and adhoc fields must be skipped, they will be used later below
			continue;
		}
		
		if (in_array(str_replace( $type . "_", "", $fieldname), $arrAdhoc)) {
			//we will save these later
			if ($value!="") {
				$adhoc_uuid = uniqid("KS", false);
				$arrAdhocSet[] = "'" . $adhoc_uuid . "','" . $kase->uuid . "','" . $table_uuid . "','". str_replace( $type . "_", "", $fieldname) . "','" . addslashes($value) . "'";
			}
			continue;
		}
		
		if ($fieldname=="homemedical_uuid") {
			$homemedical_uuid = addslashes($value);
			continue;
		}
		if ($fieldname == "injury_id") {
			if ($value!="") {
				$injury = getInjuryInfo($value);
				$injury_uuid = $injury->uuid;
			}
			continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	if (count($arrExtra) > 0) {
		$arrFields[] = "`copying_instructions`";
		$arrSet[] = "'" . addslashes(json_encode($arrExtra)) . "'";
	}
	if ($full_address=="") {
		//maybe we have street, city, zip
		$arrAddress = array();
		if ($street != "") {
			$arrAddress[] = $street;
		}
		if ($city != "") {
			$arrAddress[] = $city;
		}
		if ($state != "") {
			$arrAddress[] = $state;
		}
		if ($zip != "") {
			$arrAddress[] = $zip;
		}
		if (count($arrAddress) > 0) {
			$full_address = implode(", ", $arrAddress);
		}
	}
	if ($full_address!="") {
		$arrFields[] = "`full_address`";
		$arrSet[] = "'" . addslashes($full_address) . "'";
	}
	
	if ($additional_full_address !="") {
		$arrFields[] = "`additional_addresses`";
		//$arrSet[] = "'" . addslashes(json_encode(array("address_1"=>$additional_full_address))) . "'";
		$arrSet[] = "'" . addslashes(json_encode($arrAdditionalAddress)) . "'";
	}
	//ok we start
	$db = getConnection();
	
	//prior medical
	if ($type=="medical_provider" && ($additional_partie=="p" || $additional_partie=="c")) {
		//final check for other instructions
		if (!in_array("O", $arrCopyingValues)) {
			$other_description = "";
		}
		//set the copying instructions
		$copying_instructions = implode("", $arrCopyingValues) . "|" . $other_description . "|" . $any_all . "|" . $special_instructions;
		$arrFields[] = "`copying_instructions`";
		$arrSet[] = "'" . addslashes($copying_instructions) . "'";
	} else {
		if (count($arrExtra) == 0) {
			//blank default
			$arrFields[] = "`copying_instructions`";
			$arrSet[] = "''";
		}
	}

	if ($blnChild) {
		$arrFields[] = "`parent_corporation_uuid`";
		$arrSet[] = "'" . $parent_corporation->corporation_uuid . "'";
	} else {
		$parent_table_uuid = uniqid("RD", false);
		if ($type!="venue") {
			//insert the parent record first
			$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ", `parent_corporation_uuid`) 
				VALUES('" . $parent_table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ", '" . $parent_table_uuid . "')";
			try { 		
				$stmt = $db->prepare($sql);  
				$stmt->execute();
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		} 
		
		$arrFields[] = "`parent_corporation_uuid`";
		$arrSet[] = "'" . $parent_table_uuid . "'";
	}
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
			
	
	try { 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		//if this is a prior treatment, no case relationship, only the applicant
		
			//if this is not just a rolodex addition		
			if ($case_uuid!="") {
				//reset the case corporation relationship, unless specifically requested that more than one be added
				$blnClearOthers = true;
				$sqlclear = "";
				//if (isset($_POST["additional_partie"])) {
				if ($additional_partie=="y" || $additional_partie=="c") {
					$blnClearOthers = false;
				}
				
				if ($blnClearOthers && $type!="") {
					$corporation_attribute = $type;
					if ($additional_partie!="p") {
						//$corporation_attribute = 'prior_medical';
					//}
						$sqlclear = "UPDATE cse_case_corporation
						SET deleted = 'Y'
						WHERE `case_uuid` = '" . $case_uuid . "'
						AND `attribute` = '" . $corporation_attribute . "'
						AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
					}
				}
				$case_table_uuid = uniqid("KA", false);
				if ($additional_partie!="p" && $additional_partie!="c") {
					$attribute_1 = $type;
				} else {
					$attribute_1 = "prior_medical";
				}
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the corporation to the case 
				$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				/*
				if ($_SERVER['REMOTE_ADDR']=='47.153.56.2') {
					//echo "additional_partie:" . $additional_partie . "<br />";
					echo $sql . "\r\n" . $sqlclear . "\r\n";
				}
				*/
				if ($sqlclear!="") {
					$stmt = $db->prepare($sqlclear);  
					$stmt->execute();
				}

				$stmt = $db->prepare($sql);  
				$stmt->execute();
			}
		
		if ($additional_partie=="p" || $additional_partie=="c") {
			//look up the person
			$person_uuid = $kase->applicant_uuid;
			//attach the corporation to the person
			$person_table_uuid = uniqid("PT", false);
			$attribute_1 = "medical_provider";
			$attribute_2 = "prior";
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the corporation to the case 
			$sql = "INSERT INTO cse_person_" . $table_name . " (`person_" . $table_name . "_uuid`, `person_uuid`, `" . $table_name . "_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $person_table_uuid  ."', '" . $person_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $attribute_2 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		}
		$adhoc_where_clause = "`corporation_uuid` = '" . $table_uuid . "'";
		
		
		//do we have adhocs
		if (count($arrAdhocSet)>0) {
			//inserts
			$sql = "INSERT INTO `cse_" . $table_name . "_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`) VALUES ";
			$arrValues = array();
			foreach($arrAdhocSet as $adhoc_set) {		
				$arrValues[] = "(" . $adhoc_set . ", '" . $_SESSION['user_customer_id'] . "')"; 
			}
			$sql .= implode(",\r\n", $arrValues);

			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$track_adhock_id = $db->lastInsertId();
			trackAdhoc("insert", $track_adhock_id);
		}
		
		//home medical is a mix of data and corporation
		if ($homemedical_uuid!="") {
			$homemedical_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the corporation to the case 
			$sql = "INSERT INTO cse_corporation_homemedical (`corporation_homemedical_uuid`, `corporation_uuid`, `homemedical_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $homemedical_table_uuid  ."', '" . $table_uuid . "', '" . $homemedical_uuid . "', 'homemedical', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		}
				
		if ($case_id!="") {
			if (strpos($case_type, "WC") === false && strpos($case_type, "W/C") === false  && strpos($case_type, "Worker") === false) {
				if ($type == "defendant") {
					//already in?
					$vs_pos = strpos($case_name, " vs ");
					
					if ($vs_pos===false) {
						$sql = "
						UPDATE cse_case 
						SET case_name = CONCAT(`case_name`, ' vs " . addslashes(trim($company_name)) . "')
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
						$stmt = $db->prepare($sql);  
						$stmt->execute();
					} else {
						//if the defendant name is not in the case name
						if (strpos($case_name, $company_name)===false) {
							//break it up
							$arrCaseName = explode(" vs ", $case_name);
							$arrCaseName[1] = addslashes($company_name);
							
							$sql = "
							UPDATE cse_case 
							SET case_name = '" . implode(" vs ", $arrCaseName) . "'
							WHERE case_id = " . $case_id;
							$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
							//echo $sql . "\r\n";   
							$stmt = $db->prepare($sql);  
							$stmt->execute();
						}
					}
				}
				//if immigration
				//if this is the first partie to be added to the case
				//cse_case_" . $table_name . "
				$blnFirstImm = false;
				if ($case_type=="immigration") {
					//how many parties so far?
					$sql = "SELECT COUNT(ccc.corporation_uuid) corp_count
					FROM cse_case_corporation ccc
					INNER JOIN cse_corporation corp
					ON ccc.corporation_uuid = corp.corporation_uuid
					INNER JOIN cse_case ccase
					ON ccc.case_uuid = ccase.case_uuid
					WHERE ccase.case_id = :case_id
					AND ccc.deleted = 'N'
					AND corp.deleted = 'N'";

					$stmt = $db->prepare($sql);
					$stmt->bindParam("case_id", $case_id);
					$stmt->execute();
					$counter = $stmt->fetchObject();
					
					//die(print_r($counter));
					if ($counter->corp_count==1) {
						$blnFirstImm = true;
						$sql = "
						UPDATE cse_case 
						SET case_name = '" . addslashes(trim($company_name)) . "'
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id']; 
						$stmt = $db->prepare($sql);  
						$stmt->execute();
					}
				}
				if ($type == "plaintiff" || ($type=="claimant" && $case_type=="SSDI")) {
					//already in?
					$vs_pos = strpos($case_name, " vs ");
					
					if ($vs_pos===false || $case_name=="") {
						$sql = "
						UPDATE cse_case 
						SET case_name = '" . addslashes(trim($company_name)) . "'
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
						//echo $sql . "\r\n";  
						$stmt = $db->prepare($sql);  
						$stmt->execute();
					} else {
						if ($vs_pos==0 ) {
							$sql = "
							UPDATE cse_case 
							SET case_name = CONCAT('" . addslashes(trim($company_name)) . "', `case_name`)
							WHERE case_id = " . $case_id;
							$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
							//echo $sql . "\r\n";   
							$stmt = $db->prepare($sql);  
							$stmt->execute();
						} else {
							//if the plaintiff name is not in the case name
							if (strpos($case_name, $company_name)===false) {
								//break it up
								$arrCaseName = explode(" vs ", $case_name);
								$arrCaseName[0] = addslashes($company_name);
								
								$sql = "
								UPDATE cse_case 
								SET case_name = '" . implode(" vs ", $arrCaseName) . "'
								WHERE case_id = " . $case_id;
								$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
								//echo $sql . "\r\n";   
								$stmt = $db->prepare($sql);  
								$stmt->execute();
							}
						}
					}
					//echo "name done\r\n";
				}
			}
		} else {
			if ($type == "employer") {
				if ($case_name!="") {
					if (strpos($case_name, $company_name)===false) {
						//break it up
						$arrCaseName = explode(" vs ", $case_name);
						$arrCaseName[1] = addslashes($company_name);
						$sql = "
						UPDATE cse_case 
						SET case_name = '" . implode(" vs ", $arrCaseName) . "'
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
						//echo $sql . "\r\n";   
						$stmt = $db->prepare($sql);  
						$stmt->execute();
					}
				}
			}
		}
		
		$stmt = null; $db = null;
		trackCorporation("insert", $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$stmt = null; $db = null;
}
function updateCorporation() {
	session_write_close();
	$request = Slim::getInstance()->request();
	$arrSet = array();
	//die(print_r($arrSet));
	$where_clause = "";
	$adhoc_where_clause = "";
	$table_name = "";
	$table_id = "";
	$injury_uuid = "";
	$blnApplyToChildren = false;
	$adhoc_fields = passed_var("adhoc_fields", "post");
	$type = passed_var("type", "post");
	$case_uuid = passed_var("case_uuid", "post");
	$corporation_uuid = passed_var("corporation_uuid", "post");
	$partie = passed_var("partie", "post");
	$homemedical_uuid = "";
	$additional_partie = "";
	$arrAdhoc = explode(",", $adhoc_fields);
	$arrAdhocSet = array();
	//copying instructions are kept in array and then stored in copying_instuctions field
	$arrCopying = array("medical_copy", "billing_copy", "xray_copy", "claim_copy", "employment_copy", "wage_copy", "other_copy", "other_description", "any_all", "special_instructions");	
	$arrCopyingValues = array();
	$other_description = "";
	$any_all = "N";
	$special_instructions = "";
	$full_address = "";
	$additional_full_address = "";
	$arrAdditionalAddress = array();
	$street = "";
	$suite = "";
	$city = "";
	$state = "";
	$zip = "";
	
	//extra info for pi venue
	$arrExtra = array();
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if (strpos($fieldname, "_"  . $partie)!==false) {
			continue;
		}
		if (strpos($fieldname, "token-")!==false) {
			continue;
		}
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		
		if ($fieldname=="case_id"){
			$case_id = $value;
			if ($case_id > 0) {
				$kase = getKaseInfo($case_id);
				$case_uuid = $kase->uuid;
				$case_name = $kase->case_name;
				$vs_pos = strpos($case_name, " vs ");
				//die($case_name . " -> vs_pos:" . $vs_pos);
			}
			continue;
		}
		
		if (strpos($fieldname, "Extra") > -1) {
			$fieldname = str_replace("Extra", "", $fieldname);
			$arrExtra[$fieldname] = $value; 
			continue;
		}
		
		if (strpos($fieldname, "additional_") > -1 && $fieldname!="additional_partie") {
			if  ($fieldname=="additional_full_address") {
				$additional_full_address = $value;
			}
			$arrAdditionalAddress["address_2"][] = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="additional_partie") {
			$additional_partie = $value;
			continue;
		}
		if ($fieldname=="full_address") {
			$full_address = $value;
			continue;
		}
		if ($fieldname=="street") {
			$street = $value;
		}
		if ($fieldname=="suite") {
			$suite = $value;
		}
		if ($fieldname=="city") {
			$city = $value;
		}
		if ($fieldname=="state") {
			$state = $value;
		}
		if ($fieldname=="company_name") {
			$company_name = $value;
		}
		if ($fieldname=="zip") {
			$zip = $value;
		}
		if (in_array($fieldname, $arrCopying)) {
			if (strpos($fieldname, "_copy") > -1) {
				if ($value!="") {
					$arrCopyingValues[] = $value;
				}	
			}
			if ($fieldname == "other_description") {
				$other_description = $value;
			}
			if ($fieldname == "any_all") {
				$any_all = $value;
			}
			if ($fieldname == "special_instructions") {
				$special_instructions = $value;
			}
			continue;
		}
		if ($fieldname=="case_uuid" || $fieldname=="adhoc_fields" || $fieldname=="partie" || $fieldname=="partie_type" || $fieldname=="addto_current" || $fieldname=="override_current") {
			continue;
		}
		if ($fieldname=="homemedical_uuid") {
			$homemedical_uuid = addslashes($value);
			continue;
		}
		if ($fieldname == "injury_id") {
			if ($value!="") {
				$injury = getInjuryInfo($value);
				$injury_uuid = $injury->uuid;
			}
			continue;
		}
		if (in_array(str_replace( $type . "_", "", $fieldname), $arrAdhoc)) {
			//we will save these later
			//if ($value!="") {
				$adhoc_uuid = uniqid("KS", false);
				$arrAdhocSet[] = "'" . $adhoc_uuid . "','" . $case_uuid . "','" . $corporation_uuid . "','". str_replace( $type . "_", "", $fieldname) . "','" . addslashes($value) . "'";
			//}
			continue;
		}
		if ($fieldname=="corporation_uuid") {
			$adhoc_where_clause = "`corporation_uuid` = '" . $value . "'";
			continue;
		}
		//apply changes to children if this is a paretn
		if ($fieldname == "confirm_apply_decide") {
			$blnApplyToChildren = true;
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="id" || $fieldname=="corporation_id") {
			if ($table_id=="") {
				$table_id = $value;
				$parent_corporation = getCorporationInfo($value);	
				$where_clause = " = " . $value;
			}
			continue;
		} else {
			if ($fieldname!="full_name") {
				$arrSet[] = "`" . $table_name . "`.`" . $fieldname . "` = '" . addslashes($value) . "'";
			} else {
				//full name
				$arrSet[] = "`" . $table_name . "`.`" . $fieldname . "` = '" . addslashes($value) . "'";
				$arrName = explode(" ", $value);
				$arrSet[] = "`" . $table_name . "`.`first_name` = '" . addslashes($arrName[0]) . "'";
				if (count($arrName)>1) {
					unset($arrName[0]);
					$value = implode(" ", $arrName);
					$arrSet[] = "`" . $table_name . "`.`last_name` = '" . addslashes($value) . "'";
				}
			}
		}
	}

	//prior medical
	if ($type=="medical_provider" && ($additional_partie=="p" || $additional_partie=="c")) {
		//final check for other instructions
		if (!in_array("O", $arrCopyingValues)) {
			$other_description = "";
		}
		//set the copying instructions
		$copying_instructions = implode("", $arrCopyingValues) . "|" . $other_description . "|" . $any_all . "|" . $special_instructions;
		$arrSet[] = "`" . $table_name . "`.`copying_instructions` = '" . addslashes($copying_instructions) . "'";
	}
	
	//extra info
	if (count($arrExtra) > 0) {
		$arrSet[] = "`" . $table_name . "`.`copying_instructions` = '" . addslashes(json_encode($arrExtra)) . "'";
	}
	
	//maybe we have street, city, zip
	$arrAddress = array();
	if ($street != "") {
		$arrAddress[] = $street;
	}
	if ($suite != "") {
		$arrAddress[] = $suite;
	}
	if ($city != "") {
		$arrAddress[] = $city;
	}
	if ($state != "") {
		$arrAddress[] = $state;
	}
	if ($zip != "") {
		$arrAddress[] = $zip;
	}
	if ($full_address == "") {
		if (count($arrAddress) > 0) {
			$full_address = implode(", ", $arrAddress);
		} else {
			$full_address = "";
		}
	}
	
	$arrSet[] = "`" . $table_name . "`.`full_address` = '" . addslashes($full_address) . "'";
	
	if ($additional_full_address!="") {
		$arrSet[] = "`" . $table_name . "`.`additional_addresses` = '" . addslashes(json_encode($arrAdditionalAddress)) . "'";
	}
	$where_clause = "`" . $table_name . "`.`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "` " . $table_name . "
	SET " . implode(", ", $arrSet);
	if ($adhoc_where_clause=="") {
		$sql .= " WHERE " . $where_clause;
	} else {
		$sql .= " WHERE " . $adhoc_where_clause;
	}
	$sql .= " AND `" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];
	
	if ($blnApplyToChildren == true) {
		
			$sql .= ";
			UPDATE cse_corporation child, cse_corporation parent
			SET child.full_name = parent.full_name,
			child.company_name = parent.company_name,
			child.phone = parent.phone,
			child.fax = parent.fax,
			child.employee_phone = parent.employee_phone,
			child.employee_cell = parent.employee_cell,
			child.employee_fax = parent.employee_fax,
			child.full_address = parent.full_address,
			child.street = parent.street,
			child.city = parent.city,
			child.state = parent.state,
			child.zip = parent.zip
			WHERE child.parent_corporation_uuid = parent.corporation_uuid
			AND child.corporation_uuid != child.parent_corporation_uuid
			AND child.parent_corporation_uuid = '" . $parent_corporation->uuid . "'
			AND `child`.customer_id = " . $_SESSION['user_customer_id'];
			//die($sql);
	}
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		if ($injury_uuid!="") {
			//first check if the injury uuid has changed
			$corp = getKaseCorporationInfo($case_id, $table_id);
			if ($corp->injury_uuid != $injury_uuid) {
				$sqlclear = "UPDATE cse_case_corporation
				SET deleted = 'Y'
				WHERE `case_uuid` = '" . $case_uuid . "'
				AND `corporation_uuid` = '" . $corp->uuid . "'
				AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
	
				$stmt = $db->prepare($sqlclear);  
				$stmt->execute();
				
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the corporation to the case 
				$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $corp->uuid. "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";

				$stmt = $db->prepare($sql);  
				$stmt->execute();
			}
		}
		//update case name for pi cases
		
		//if newpi and type = defendant
		if ($case_id > 0) {		
			//update cse_case set case_name = applicant vs defendant
			$case_type = $kase->case_type;
			$case_name = $kase->case_name;
			$full_name = $kase->full_name;
			if (strpos($case_type, "WC") === false && strpos($case_type, "W/C") === false  && strpos($case_type, "Worker") === false) {
				
				if ($type == "defendant") {
					//already in?
					$vs_pos = strpos($case_name, " vs ");
					
					if ($vs_pos===false) {
						$sql = "
						UPDATE cse_case 
						SET case_name = CONCAT(REPLACE(`case_name`, ' vs " . addslashes(trim($company_name)) . "', ''), ' vs " . addslashes(trim($company_name)) . "')
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
						$stmt = $db->prepare($sql);  
						$stmt->execute();
					} else {
						//if the defendant name is not in the case name
						//if (strpos($case_name, $company_name)===false) {
							//break it up
							$arrCaseName = explode(" vs ", $case_name);
							$arrCaseName[1] = addslashes($company_name);
							
							$sql = "
							UPDATE cse_case 
							SET case_name = '" . implode(" vs ", $arrCaseName) . "'
							WHERE case_id = " . $case_id;
							$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
							//echo $sql . "\r\n";   
							$stmt = $db->prepare($sql);  
							$stmt->execute();
						//}
					}
				}
				if ($type == "plaintiff" || ($type=="claimant" && $case_type=="SSDI")) {
					//already in?
					$vs_pos = strpos($case_name, " vs ");
					
					if ($vs_pos===false || $case_name=="") {
						$sql = "
						UPDATE cse_case 
						SET case_name = '" . addslashes(trim($company_name)) . "'
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
						//echo $sql . "\r\n";  
						$stmt = $db->prepare($sql);  
						$stmt->execute();
					} else {
						if ($vs_pos==0 ) {
							$sql = "
							UPDATE cse_case 
							SET case_name = '" . addslashes(trim($company_name) . trim($case_name)) . "'
							WHERE case_id = " . $case_id;
							$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
							//echo $sql . "\r\n";   
							$stmt = $db->prepare($sql);  
							$stmt->execute();
						} else {
							//if the plaintiff name is not in the case name
							//if (strpos($case_name, $company_name)===false) {
								//break it up
								$arrCaseName = explode(" vs ", $case_name);
								$arrCaseName[0] = addslashes($company_name);
								
								$sql = "
								UPDATE cse_case 
								SET case_name = '" . implode(" vs ", $arrCaseName) . "'
								WHERE case_id = " . $case_id;
								$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
								//echo $sql . "\r\n";   
								$stmt = $db->prepare($sql);  
								$stmt->execute();
							//}
						}
					}
					//echo "name done\r\n";
				}
			} else {
				if ($type == "employer") {
					if ($case_name!="") {
						if (strpos($case_name, $company_name)===false) {
							//break it up
							$arrCaseName = explode(" vs ", $case_name);
							$arrCaseName[1] = addslashes($company_name);
							$sql = "
							UPDATE cse_case 
							SET case_name = '" . implode(" vs ", $arrCaseName) . "'
							WHERE case_id = " . $case_id;
							$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
							//echo $sql . "\r\n";   
							$stmt = $db->prepare($sql);  
							$stmt->execute();
						}
					}
				}
			}
			$stmt = null; $db = null;
		}
		
		//need a trackBulkCorporation($id_array)
		trackCorporation("update", $table_id);
				
	
		//one more fix for orphan rolodex entries
		$sql = "SELECT corp.* 
		FROM cse_corporation corp
		LEFT OUTER JOIN 
			((SELECT 
				MIN(corporation_id) min_id
			FROM
				cse_corporation
			WHERE
				corporation_uuid = parent_corporation_uuid
				AND `customer_id` = " . $_SESSION['user_customer_id'] . "
			GROUP BY `type` , company_name , full_address , employee_phone)) min_ids
		ON corp.corporation_id = min_ids.min_id
		WHERE min_id IS NULL
		AND corp.corporation_uuid = corp.parent_corporation_uuid
		AND corp.company_name = '" . addslashes($parent_corporation->company_name) . "'
		AND corp.parent_corporation_uuid != '" . $parent_corporation->uuid . "'
		AND `corp`.customer_id = " . $_SESSION['user_customer_id'];
		//die($sql);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$corps = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//reset the parent, since it was updated
		$parent_corporation = getCorporationInfo($table_id);	
		
		//die(print_r($parent_corporation));
		foreach($corps as $subcorp) {
			//there are other rolodex entries, orphaned, so update to this one
			
			$subparent_corporation_uuid = $subcorp->parent_corporation_uuid;
			
			$sql = "
			UPDATE cse_corporation
			SET full_name = '" . addslashes($parent_corporation->full_name) . "',
			company_name = '" . addslashes($parent_corporation->company_name) . "',
			phone = '" . addslashes($parent_corporation->phone) . "',
			fax = '" . addslashes($parent_corporation->fax) . "',
			employee_phone = '" . addslashes($parent_corporation->employee_phone) . "',
			employee_cell = '" . addslashes($parent_corporation->employee_cell) . "',
			employee_fax = '" . addslashes($parent_corporation->employee_fax) . "',
			street = '" . addslashes($parent_corporation->street) . "',
			city = '" . addslashes($parent_corporation->city) . "',
			state = '" . addslashes($parent_corporation->state) . "',
			zip = '" . addslashes($parent_corporation->zip) . "',
			parent_corporation_uuid = '" . $parent_corporation->uuid . "'
			WHERE 1
			AND `type` = '" . addslashes($parent_corporation->type) . "'
			AND parent_corporation_uuid = '" . $subparent_corporation_uuid . "'
			AND customer_id = " . $_SESSION['user_customer_id'];
			
			//die($sql);
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		
		//do we have adhocs
		if (count($arrAdhocSet)>0) {
			$sql = "
			UPDATE `cse_" . $table_name . "_adhoc`
			SET deleted = 'Y'
			WHERE " . $adhoc_where_clause;
			$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			//now inserts
			$sql = "INSERT INTO `cse_" . $table_name . "_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`) VALUES ";
			$arrValues = array();
			foreach($arrAdhocSet as $adhoc_set) {		
				$arrValues[] = "(" . $adhoc_set . ", " . $_SESSION['user_customer_id'] . ")"; 
			}
			$sql .= implode(",\r\n", $arrValues);
				
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$new_id = $db->lastInsertId();
			trackAdhoc("insert", $new_id);
			$stmt = null; $db = null;
		}
		//all done...
		echo json_encode(array("success"=>$table_id)); 	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	exit();
}
function updateCorporationField() {
	session_write_close();
	$id = passed_var("table_id", "post");
	$fieldname = passed_var("fieldname", "post");
	$value = passed_var("value", "post");
	$customer_id = $_SESSION['user_customer_id'];
	$case_id = passed_var("case_id", "post");
	$type = passed_var("type", "post");
	
	$kase = getKaseInfo($case_id);
	
	//address
	$arrAddress = array("street", "suite", "city", "administrative_area_level_1", "postal_code");
	foreach($arrAddress as $add) {
		if (strpos($fieldname, $add)===0) {
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
	
	$sql = "UPDATE cse_corporation
	SET `" . $fieldname . "` = :value
	WHERE corporation_id = :id
	AND customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("value",  $value);
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("id"=>$id)); 
		
		trackCorporation("update", $id);
		
		if ($case_id > 0 && $fieldname=="company_name") {
			$db = getConnection();
			
			$company_name = $value;
			//update cse_case set case_name = applicant vs defendant
			$case_type = $kase->case_type;
			$case_name = $kase->case_name;
			$full_name = $kase->full_name;
			
			//die($type );
			if (strpos($case_type, "WC") === false && strpos($case_type, "W/C") === false  && strpos($case_type, "Worker") === false) {
				
				if ($type == "defendant") {
					//already in?
					$vs_pos = strpos($case_name, " vs ");
					
					if ($vs_pos===false) {
						$sql = "
						UPDATE cse_case 
						SET case_name = CONCAT(REPLACE(`case_name`, ' vs " . addslashes(trim($company_name)) . "', ''), ' vs " . addslashes(trim($company_name)) . "')
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
						$stmt = $db->prepare($sql);  
						$stmt->execute();
					} else {
						//if the defendant name is not in the case name
						//if (strpos($case_name, $company_name)===false) {
							//break it up
							$arrCaseName = explode(" vs ", $case_name);
							$arrCaseName[1] = addslashes($company_name);
							
							$sql = "
							UPDATE cse_case 
							SET case_name = '" . implode(" vs ", $arrCaseName) . "'
							WHERE case_id = " . $case_id;
							$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
							//echo $sql . "\r\n";   
							$stmt = $db->prepare($sql);  
							$stmt->execute();
						//}
					}
				}
				if ($type == "plaintiff" || ($type=="claimant" && $case_type=="SSDI")) {
					//already in?
					$vs_pos = strpos($case_name, " vs ");
					
					if ($vs_pos===false || $case_name=="") {
						$sql = "
						UPDATE cse_case 
						SET case_name = '" . addslashes(trim($company_name)) . "'
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
						//echo $sql . "\r\n";  
						$stmt = $db->prepare($sql);  
						$stmt->execute();
					} else {
						if ($vs_pos==0 ) {
							$sql = "
							UPDATE cse_case 
							SET case_name = '" . addslashes(trim($company_name) . trim($case_name)) . "'
							WHERE case_id = " . $case_id;
							$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
							//echo $sql . "\r\n";   
							$stmt = $db->prepare($sql);  
							$stmt->execute();
						} else {
							//if the plaintiff name is not in the case name
							//if (strpos($case_name, $company_name)===false) {
								//break it up
								$arrCaseName = explode(" vs ", $case_name);
								$arrCaseName[0] = addslashes($company_name);
								
								$sql = "
								UPDATE cse_case 
								SET case_name = '" . implode(" vs ", $arrCaseName) . "'
								WHERE case_id = " . $case_id;
								$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
								//echo $sql . "\r\n";   
								$stmt = $db->prepare($sql);  
								$stmt->execute();
							//}
						}
					}
					//echo "name done\r\n";
				}
			} else {
				if ($type == "employer") {
					if ($case_name!="") {
						if (strpos($case_name, $company_name)===false) {
							//break it up
							$arrCaseName = explode(" vs ", $case_name);
							$arrCaseName[1] = addslashes($company_name);
							$sql = "
							UPDATE cse_case 
							SET case_name = '" . implode(" vs ", $arrCaseName) . "'
							WHERE case_id = " . $case_id;
							$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
							//echo $sql . "\r\n";   
							$stmt = $db->prepare($sql);  
							$stmt->execute();
						}
					}
				}
			}
			$stmt = null; $db = null;
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updateCorporationKai() {
	$request = Slim::getInstance()->request();
	$arrSet = array();
	//die(print_r($arrSet));
	$where_clause = "";
	$adhoc_where_clause = "";
	$table_name = "";
	$table_id = "";
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="partie_kai_info") {
			$partie_kai_info = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			if ($case_id > 0) {
				$kase = getKaseInfo($case_id);
				$case_uuid = $kase->uuid;
				$case_name = $kase->case_name;
				$vs_pos = strpos($case_name, " vs ");
				//die($case_name . " -> vs_pos:" . $vs_pos);
			}
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="partie_id") {
			$partie_id = $value;
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		
		if ($fieldname=="table_id" || $fieldname=="id" || $fieldname=="corporation_id") {
			if ($table_id=="") {
				$table_id = $value;
				$parent_corporation = getCorporationInfo($value);	
				$where_clause = " = " . $value;
			}
			continue;
		} else {
			if ($fieldname!="full_name") {
				$arrSet[] = "`" . $table_name . "`.`" . $fieldname . "` = '" . addslashes($value) . "'";
			} else {
				//full name
				$arrSet[] = "`" . $table_name . "`.`" . $fieldname . "` = '" . addslashes($value) . "'";
				$arrName = explode(" ", $value);
				$arrSet[] = "`" . $table_name . "`.`first_name` = '" . addslashes($arrName[0]) . "'";
				if (count($arrName)>1) {
					unset($arrName[0]);
					$value = implode(" ", $arrName);
					$arrSet[] = "`" . $table_name . "`.`last_name` = '" . addslashes($value) . "'";
				}
			}
		}
	}

	
	//$where_clause = "`corporation`.`corporation_id`" . $where_clause;
	$sql = "
	UPDATE `cse_corporation`
	SET `kai_info` = '" . $partie_kai_info . "'";
	$sql .= " WHERE `corporation_id` = " . $partie_id;
	$sql .= " AND `customer_id` = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$injury_uuid = "";
		/*if ($injury_uuid!="") {
			//first check if the injury uuid has changed
			$corp = getKaseCorporationInfo($case_id, $table_id);
			if ($corp->injury_uuid != $injury_uuid) {
				$sqlclear = "UPDATE cse_case_corporation
				SET deleted = 'Y'
				WHERE `case_uuid` = '" . $case_uuid . "'
				AND `corporation_uuid` = '" . $corp->uuid . "'
				AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
	
				$stmt = $db->prepare($sqlclear);  
				$stmt->execute();
				
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the corporation to the case 
				$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $corp->uuid. "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";

				$stmt = $db->prepare($sql);  
				$stmt->execute();
			}
		}
		//update case name for pi cases
		
		//if newpi and type = defendant
		
		//update cse_case set case_name = applicant vs defendant
		$case_type = $kase->case_type;
		$case_name = $kase->case_name;
		$full_name = $kase->full_name;
		if (strpos($case_type, "WC") === false && strpos($case_type, "W/C") === false  && strpos($case_type, "Worker") === false) {
			
			if ($type == "defendant") {
				//already in?
				$vs_pos = strpos($case_name, " vs ");
				
				if ($vs_pos===false) {
					$sql = "
					UPDATE cse_case 
					SET case_name = CONCAT(`case_name`, ' vs " . addslashes($company_name) . "')
					WHERE case_id = " . $case_id;
					$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
					$stmt = $db->prepare($sql);  
					$stmt->execute();
				}
			}
			if ($type == "plaintiff") {
				//already in?
				$vs_pos = strpos($case_name, " vs ");
				
				if ($vs_pos===false || $case_name=="") {
					$sql = "
					UPDATE cse_case 
					SET case_name = '" . addslashes($company_name) . "'
					WHERE case_id = " . $case_id;
					$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
					//echo $sql . "\r\n";  
					$stmt = $db->prepare($sql);  
					$stmt->execute();
				} else {
					if ($vs_pos==0 ) {
						$sql = "
						UPDATE cse_case 
						SET case_name = '" . addslashes($company_name . $case_name) . "'
						WHERE case_id = " . $case_id;
						$sql .= " AND customer_id = " . $_SESSION['user_customer_id'];
						//echo $sql . "\r\n";   
						$stmt = $db->prepare($sql);  
						$stmt->execute();
					}
				}
				//echo "name done\r\n";
			}
		}*/
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>$partie_id)); 
		//need a trackBulkCorporation($id_array)
		//trackCorporation("update", $table_id);

	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function trackCorporation($operation, $corporation_id) {
	$sql = "INSERT INTO cse_corporation_track (`user_uuid`, `user_logon`, `operation`, `corporation_id`, `corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_cell`, `employee_fax`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `copying_instructions`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `corporation_id`, `corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_cell`, `employee_fax`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `copying_instructions`, `last_updated_date`, `last_update_user`, `deleted`, " . $_SESSION['user_customer_id'] . "
	FROM cse_corporation
	WHERE 1
	AND corporation_id = " . $corporation_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		//new the case_uuid
		$kase = getKaseInfoByPartie($corporation_id);
			
		$case_uuid = "";
		if (!is_object($kase)) {			
			//might be a prior medical?
			$kase = getKaseInfoPriorMedical($corporation_id);
		}
		
		if (!is_object($kase)) {
			//fall back to the corporation itself
			$corporation = getCorporationInfo($corporation_id);
			$attribute = $corporation->company_name . " (" . ucwords(str_replace("_", " ", $corporation->type)) . ")";
		} else {
			$case_uuid = $kase->uuid;
			$attribute = $kase->company_name . " (" . ucwords(str_replace("_", " ", $kase->attribute)) . ")";
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
		$activity = $attribute . " Information was " . $operation . "  by " . $_SESSION['user_name'];
		
		$billing_time = 0;
		if (isset($_POST["billing_time"])) {
			$billing_time = passed_var("billing_time", "post");
		}
		recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category, $billing_time);
			
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function trackAdhoc($operation, $corporation_adhoc_id) {
	$sql = "INSERT INTO cse_corporation_adhoc_track (`user_uuid`, `user_logon`, `operation`, `adhoc_id`, `adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `adhoc_id`, `adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, " . $_SESSION['user_customer_id'] . "
	FROM cse_corporation_adhoc
	WHERE 1
	AND adhoc_id = " . $corporation_adhoc_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
	
		$stmt->execute();
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
?>