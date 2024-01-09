<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/financial', function (RouteCollectorProxy $app) {
		$app->get('/{case_id}', 'getFullFinancial');
		$app->post('/add', 'addFullFinancial');
	});

	$app->get('/financialtotals/{case_id}', 'getFinancialTotals');

	$app->group('/financialcarrier', function (RouteCollectorProxy $app) {
		$app->get('/{case_id}/{corporation_id}', 'getCarrierFinancial');
		$app->post('/add', 'addCarrierFinancial');
	});

	//$app->post('/personal_injury/update', 'updateFinancial');
})->add(Api\Middleware\Authorize::class);

function getCarrierFinancialInfo($case_id, $corporation_id) {
	return getFullFinancial($case_id, $corporation_id, true);
}
function getCarrierFinancial($case_id, $corporation_id) {
	getFullFinancial($case_id, $corporation_id);
}
function getFinancialTotals($case_id) {
	$financials = getFullFinancial($case_id, -1, true);
	$total_subro = 0;
	$total_subro_override = 0;
	$total_reduced = 0;
	$total_balance = 0;
	//die(print_r($financials));
	foreach($financials as $financial) {
		$jdata = json_decode($financial->financial_info);
		
		$blnSubro = false;
		$subro = 0;
		$subro_override = 0;
		$reduced = 0;
		$balance = 0;
		foreach($jdata as $datum) {
			//die(print_r($datum));
			for($int = 0; $int < count($datum); $int++) {
				$entry = $datum[$int];
				//die(print_r($entry));
				
				switch($entry->name) {
					case "financial_subro_select_Input":
						$blnSubro = ($entry->value == "Yes");
						break;
					case "financial_subroInput":
						$subro = $entry->value;
						break;
					case "financial_subro_overrideInput":
						$subro_override = $entry->value;
						break;
					case "reducedInput":
						$reduced = $entry->value;
						break;
					case "balanceInput":
						$balance = $entry->value;
						break;
				}
			}
		}
		$total_subro += $subro;
		$total_subro_override += $subro_override;
		$total_reduced += $reduced;
		$total_balance += $balance;
	}
	
	echo json_encode(array("success"=>true, "subrogation"=>$total_subro, "subrogation_override"=>$total_subro_override, "reduced"=>$total_reduced, "balance"=>$total_balance));
}
function getFullFinancial($case_id, $corporation_id = "", $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
    $sql = "SELECT fi.*, fi.financial_id id, fi.financial_uuid uuid";
	if ($corporation_id!="") {
		$sql .= "
		, corp.corporation_id, corp.corporation_uuid, corp.company_name";
	}
	$sql .= "
	FROM `cse_financial` fi ";
	
	if ($corporation_id!="") {
		$sql .= "
		INNER JOIN cse_corporation_financial ccf
		ON fi.financial_uuid = ccf.financial_uuid
		INNER JOIN cse_corporation corp
		ON ccf.corporation_uuid = corp.corporation_uuid";
	}
	
	$sql .= "		
	WHERE fi.case_id=:case_id
	AND fi.customer_id = :customer_id
	AND fi.deleted = 'N'";
	if ($corporation_id!="" && $corporation_id!=-1) {
		$sql .= "
		AND corp.corporation_id = :corporation_id
		";
	}
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		if ($corporation_id!="" && $corporation_id!=-1) {
			$stmt->bindParam("corporation_id", $corporation_id);
		}
		$stmt->execute();
		if (!$blnReturn) {
			$financial = $stmt->fetchObject();
		} else {
			$financials = $stmt->fetchAll(PDO::FETCH_OBJ);
		}

        // Include support for JSONP requests
        if (!$blnReturn) {
            echo json_encode($financial);
        } else {
            return $financials;
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getFinancial($case_id) {
    $sql = "SELECT pi.*, pi.personal_injury_id id, pi.personal_injury_uuid uuid
			FROM `cse_personal_injury` pi 
			WHERE pi.case_id=:case_id
			AND pi.customer_id = " . $_SESSION['user_customer_id'] . "
			AND pi.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$financial = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($financial);
        } else {
            echo $_GET['callback'] . '(' . json_encode($financial) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
/*
function getInjuryAccident($case_id) {
    $sql = "SELECT acc.*, acc.accident_id id, acc.accident_uuid uuid, inj.injury_id
			FROM `cse_accident` acc 
			INNER JOIN `cse_injury_accident` cia
			ON acc.accident_uuid = cia.accident_uuid AND cia.deleted = 'N'
			INNER JOIN cse_injury inj
			ON cia.injury_uuid = inj.injury_uuid
			INNER JOIN cse_case_injury cci
			ON inj.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
			WHERE ccase.case_id=:case_id
			AND acc.customer_id = " . $_SESSION['user_customer_id'] . "
			AND acc.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$accident = $stmt->fetchObject();

		//die($accident->accident_details);
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($accident);
        } else {
            echo $_GET['callback'] . '(' . json_encode($accident) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
*/
function addFinancial() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "personal_injury";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="table_uuid") {
			continue;
		}
		if ($fieldname=="personal_injury_id") {
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
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . str_replace("'", "\'", $value) . "'";
	}	
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("success"=>true, "id"=>$new_id));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function addCarrierFinancial() {
	$corporation_id = passed_var("corporation_id", "post");
	addFullFinancial($corporation_id);
}
function addFullFinancial($corporation_id = "") {
	session_write_close();
	
	$arrFields = array();
	$arrSet = array();
	$table_name = "financial";
	$table_id = passed_var("table_id", "post");
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
	$arrFinancialInfo = json_decode($_POST["financial_info"]);
	$arrDefendantInfo = json_decode($_POST["financial_defendant"]);
	$arrEscrowInfo = json_decode($_POST["financial_escrow"]);
	
	$arrFinancialInfo = json_encode(array("plaintiff"=>$arrFinancialInfo, "defendant"=>$arrDefendantInfo, "escrow"=>$arrEscrowInfo));
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="customer_id") {
			$customer_id = $value;
			continue;
		}
		if ($fieldname=="corporation_id") {
			continue;
		}
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			//die($case_id . " - case_id");
			$injury = getInjuriesInfo($case_id);
			$injury_uuid = $injury[0]->uuid;
			//die(print_r($injury));
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="financial_info" || $fieldname=="financial_escrow" || $fieldname=="financial_defendant") {
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
	if (!$blnUpdate) {
		$arrFields[] = "`financial_info`";
		$arrSet[] = "'" . addslashes($arrFinancialInfo) . "'";
	} else {
		$arrSet[] = "`financial_info` = " . "'" . addslashes($arrFinancialInfo) . "'";
	}
	//die("table:" . $table_id);
	//die(print_r($arrFields));
	//die(print_r($arrSet));
	
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
			
			if ($corporation_id != "") {
				$corporation = getCorporationInfo($corporation_id);
				
				$case_table_uuid = uniqid("RC", false);
				$attribute_1 = "main";
				$last_updated_date = date("Y-m-d H:i:s");
				
				//now we have to attach the check to the case 
				$sql = "INSERT INTO cse_corporation_" . $table_name . " (`corporation_" . $table_name . "_uuid`, `corporation_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $corporation->uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$stmt = DB::run($sql);
			}
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
		
		//die(implode(",", $arrSet));
		
		//die($sql);
		
		try {
			$stmt = DB::run($sql);
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
			//track now
			//trackPerson("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
}
function updateFinancial() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "personal_injury";
	$table_id = "";
	$info = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			continue;
		}
		if ($fieldname=="table_id") {
			$table_id = $value;
			continue;
		}
		if ($fieldname=="personal_injury_id") {
			continue;
		}
		if ($fieldname=="table_uuid") {
			$table_uuid = $value;
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
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}	
	
	//where
	$where_clause = "= '" . $table_id . "'";
	$where_clause = "`" . $table_name . "_id`" . $where_clause . "
	AND `customer_id` = " . $_SESSION['user_customer_id'];
	
	//actual query
	$sql = "UPDATE `cse_" . $table_name . "`
	SET " . implode(",
	", $arrSet) . "
	WHERE " . $where_clause;
	
	//die($sql);
	try {
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>true, "id"=>$table_id));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

