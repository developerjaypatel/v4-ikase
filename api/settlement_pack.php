<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/settlement', function (RouteCollectorProxy $app) {
		//$app->get('/{id}', 'getSettlement');
		$app->get('/{injury_id}', 'getSettlements');
		$app->post('/add', 'addSettlement');
		$app->post('/update', 'updateSettlement');
		//$app->post('/delete', 'deleteSettlement');
	});

	$app->get('/settlementfirst/{injury_id}', 'getFirstSettlement');
	$app->get('/settlementsheet/{injury_id}', 'getSettlementSheet');
	$app->get('/settlementsheetid/{id}', 'getSettlementSheetById');

	//posts
	$app->post('/settlements/bydoctor/{doctors}', 'settlementsByDoctor');

	$app->group('/settlementsheet', function (RouteCollectorProxy $app) {
		$app->post('/add', 'addSettlementSheet');
		$app->post('/freeze', 'freezeSettlementSheet');
		$app->post('/update', 'updateSettlementSheet');
	});
})->add(\Api\Middleware\Authorize::class);

function getFirstSettlement($injury_id) {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT sett.settlementsheet_id
	FROM cse_settlementsheet sett
	INNER JOIN cse_injury_settlement cis
	ON sett.settlementsheet_uuid = cis.settlement_uuid AND cis.deleted ='N' AND cis.attribute = 'settlement_1'
	INNER JOIN cse_injury inj
	ON cis.injury_uuid = inj.injury_uuid
	WHERE inj.injury_id = :injury_id
	AND inj.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$settlement = $stmt->fetchObject();

        // Include support for JSONP requests
         echo json_encode($settlement);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getSettlements($injury_id) {
	session_write_close();
	
	$sql = "SELECT DISTINCT IFNULL(`cs`.`settlement_id`, '') `settlement_id`, 
	IFNULL(`cs`.`settlement_uuid`, '') `settlement_uuid`, 
	IFNULL(`cs`.`date_settled`, '') `date_settled`, 
	IFNULL(`cs`.`amount_of_settlement`, 0) `amount_of_settlement`, 
	IFNULL(`cs`.`amount_of_fee`, 0) AS `amount_of_fee`,
	IFNULL(`cs`.`fee_payment_status`, '') AS `fee_payment_status`, 
	IFNULL(`cs`.`c_and_r`, '') AS `c_and_r`, 
	IFNULL(`cs`.`stip`, '') `stip`, 
	IFNULL(`cs`.`f_and_a`, '') `f_and_a`, 
	IFNULL(`cs`.`referral_info`, '') `referral_info`, 
	IFNULL(`cs`.`date_submitted`, '') `date_submitted`,
	IFNULL(`cs`.`pd_percent`, '') `pd_percent`,
	IFNULL(`cs`.`future_medical`, '') `future_medical`,
	IFNULL(`cs`.`date_approved`, '') `date_approved`, 
	IFNULL(`cs`.`date_fee_received`, '') `date_fee_received`, 
	IFNULL(`cs`.`attorney`, '') `attorney`, IFNULL(`user`.nickname, '') as attorney_name, 
	IFNULL(`user`.user_name, '') as attorney_full_name,
	IFNULL(`cs`.settlement_id, -1) `id`, IFNULL(`cs`.settlement_uuid, '') uuid, 
	cc.injury_id, cc.adj_number, cc.start_date, cc.end_date, `ccase`.`case_id`
	FROM `cse_injury` cc
	INNER JOIN `cse_case_injury` cci
	ON `cc`.`injury_uuid` = `cci`.`injury_uuid`
	INNER JOIN `cse_case` `ccase`
	ON `cci`.`case_uuid` = `ccase`.`case_uuid`
	LEFT OUTER JOIN `cse_injury_settlement` cis 
	ON cc.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.attribute = 'main'
	LEFT OUTER JOIN `cse_settlement` cs
	ON cis.settlement_uuid = cs.settlement_uuid
	LEFT OUTER JOIN ikase.`cse_user` `user`
	ON `cs`.`attorney` = `user`.user_id
	WHERE `cc`.`deleted` = 'N' 
	AND (`cs`.`deleted` = 'N' OR `cs`.`deleted` IS NULL)
	AND (`cis`.`deleted` = 'N' OR `cis`.`deleted` IS NULL)
	AND cc.injury_id = :injury_id
	AND `cc`.customer_id = " . $_SESSION['user_customer_id'] . "
	ORDER BY  `cs`.settlement_id DESC ";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->execute();
		$settlements = $stmt->fetchObject();

        // Include support for JSONP requests
         echo json_encode($settlements);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getSettlementSheetInfo($injury_id) {
	if ($_SERVER['REMOTE_ADDR'] == "47.153.49.248") {
		//die($injury_id . " - injury_id");
	}
	return getSettlementSheet($injury_id, "", true);
}
function getSettlementSheetById($settlement_id) {
	getSettlementSheet("", $settlement_id, false);
}
function getSettlementSheet($injury_id, $settlement_id = "", $blnReturn = false) {
	session_write_close();
	
	$sql = "SELECT DISTINCT IFNULL(`cs`.`settlementsheet_id`, '') `settlementsheet_id`, 
	IFNULL(`cs`.`settlementsheet_uuid`, '') `settlementsheet_uuid`, `cci`.`injury_uuid`,
	IFNULL(`cs`.`date_settled`, '') `date_settled`, 
	IFNULL(`cs`.`status`, '') `status`, 
	IFNULL(`cs`.`due`, 0) `due`,
	
	IFNULL(`cs`.`data`, '') `data`,
	IFNULL(`cs`.settlementsheet_id, -1) `id`, IFNULL(`cs`.settlementsheet_uuid, '') uuid, 
	cc.injury_id, cc.adj_number, cc.start_date, cc.end_date, `ccase`.`case_id`
	FROM `cse_injury` cc
	INNER JOIN `cse_case_injury` cci
	ON `cc`.`injury_uuid` = `cci`.`injury_uuid`
	INNER JOIN `cse_case` `ccase`
	ON `cci`.`case_uuid` = `ccase`.`case_uuid`
	LEFT OUTER JOIN `cse_injury_settlement` cis 
	ON cc.injury_uuid = cis.injury_uuid AND cis.deleted = 'N'";
	if ($settlement_id=="") {
		$sql .="
		AND cis.`attribute` = 'main'";
	}
	$sql .="
	LEFT OUTER JOIN `cse_settlementsheet` cs
	ON cis.settlement_uuid = cs.settlementsheet_uuid
	
	WHERE `cc`.`deleted` = 'N' 
	AND (`cs`.`deleted` = 'N' OR `cs`.`deleted` IS NULL)
	AND (`cis`.`deleted` = 'N' OR `cis`.`deleted` IS NULL)
	AND `cc`.customer_id = :customer_id";
	
	if ($injury_id!="") {
		$sql .= "
		AND cc.injury_id = :injury_id";
	}
	if ($settlement_id!="") {
		$sql .= "
		AND IFNULL(`cs`.`settlementsheet_id`, '') = :settlement_id";
	}
	if ($_SERVER['REMOTE_ADDR'] == "47.153.49.248") {
		//die($sql);
	}
	try {
		$customer_id = $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($injury_id!="") {
			$stmt->bindParam("injury_id", $injury_id);
		}
		if ($settlement_id!="") {
			$stmt->bindParam("settlement_id", $settlement_id);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$settlementsheet = $stmt->fetchObject();

        // Include support for JSONP requests
		if ($blnReturn) {
			return $settlementsheet;
		} else {
	         echo json_encode($settlementsheet);    
		}
		// Solulab code change start 06-08-2019 
		if (is_object($settlementsheet) && $settlement_id=="" && false) { 
			//get rid of anything else
			//selected
			$settlementsheet_uuid = $settlementsheet->settlementsheet_uuid;
			$injury_uuid = $settlementsheet->injury_uuid;
			
			//print_r($settlementsheet);
						
			$sql = "
			UPDATE cse_injury_settlement
			SET deleted = 'Y',
			attribute = 'duplicate_cancelled'
			WHERE injury_uuid = :injury_uuid
			AND settlement_uuid != :settlementsheet_uuid
			AND customer_id = :customer_id
			AND attribute = 'main'
			";		
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("injury_uuid", $injury_uuid);
			$stmt->bindParam("settlementsheet_uuid", $settlementsheet_uuid);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
		}
		// Solulab code change end 06-08-2019
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	 
}
function getSettlementInfo($settlement_id) {
	return getSettlement($settlement_id, true);
}
function getSettlement($settlement_id, $blnReturn = false) {
    $sql = "SELECT `cs`.*, `cs`.`settlement_id` `id`, 
	`cs`.`settlement_uuid` `uuid`
	FROM  `cse_settlement` cs
	WHERE `cs`.`deleted` = 'N'
	AND `cs`.`settlement_id` = :settlement_id
	AND `cs`.customer_id = " . $_SESSION['user_customer_id'];

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("settlement_id", $settlement_id);
		$stmt->execute();
		$settlement = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!$blnReturn) {
            echo json_encode($settlement);
        } else {
            return $settlement;
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function deleteSettlement() {
	$id = passed_var("settlement_id", "post");
	$sql = "UPDATE cse_settlement cs
			SET cs.`deleted` = 'Y'
			WHERE `settlement_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("settlement_id", $id);
		$stmt->execute();
		trackSettlement("delete", $settlement_id);
		echo json_encode(array("success"=>"settlement marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addSettlement() {
	$arrFields = array();
	$arrValues = array();
	$table_name = "";
	$injury_id = -1;
	$prior_attorney_payment_info;
	if(array_key_exists('prior_attorney_payment_info',$_POST)){
		$prior_attorney_payment_info = $_POST['prior_attorney_payment_info'];
		unset($_POST['prior_attorney_payment_info']);
	}

	// return json_encode($_POST);
	foreach($_POST as $fieldname => $value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);

		if ($fieldname == "table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname == "injury_id") {
			$injury_id = $value;
			continue;
		}
		if ($fieldname == "pd_percent" && $value == "") {
			$value = 0;
		}

		if ($fieldname == "fee_options") {
			if(!empty($value)){
				switch ($value) {
					case "F_A":
						$arrFields[] = "`f_and_a`"; $arrValues[] = "'Y'";
						$arrFields[] = "`c_and_r`"; $arrValues[] = "'N'";
						$arrFields[] = "`stip`";   $arrValues[] = "'N'";
						break;
					case "C_R":
						$arrFields[] = "`f_and_a`"; $arrValues[] = "'N'";
						$arrFields[] = "`c_and_r`"; $arrValues[] = "'Y'";
						$arrFields[] = "`stip`";   $arrValues[] = "'N'";
						break;
					case "STIP":
						$arrFields[] = "`f_and_a`"; $arrValues[] = "'N'";
						$arrFields[] = "`c_and_r`"; $arrValues[] = "'N'";
						$arrFields[] = "`stip`";   $arrValues[] = "'Y'";
						break;
				}
			}
			continue;
		}

		if (in_array($fieldname, ["settlement_id", "table_id", "nono"])) {
			continue;
		}

		if (strpos($fieldname, "date_") !== false) {
			if ($value != "") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}

		$arrFields[] = "`" . $fieldname . "`";
		$arrValues[] = "'" . addslashes($value) . "'";
	}

	$arrFields[] = "`customer_id`";
	$arrValues[] = $_SESSION['user_customer_id'];
	$table_uuid = uniqid("KS", false);

	$sql = "INSERT INTO `cse_" . $table_name . "` 
	(`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
	VALUES ('" . $table_uuid . "', " . implode(",", $arrValues) . ")";
	try { 
		
		DB::run($sql);
		$new_id = DB::lastInsertId();
		
		$injury = getInjuryInfo($injury_id);
		/*
		//get the uuid of the injury so we can hook it up
		$sql = "SELECT injury_uuid uuid FROM cse_injury WHERE injury_id = :injury_id ";
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_id", $injury_id);
	
		$stmt->execute();
		$injury = $stmt->fetchObject();
		*/
		//delete any previous relationship that may exist
		$sql = "UPDATE `cse_injury_" . $table_name . "`
		SET `deleted` = 'Y' 
		WHERE `injury_uuid` = '" . $injury->uuid . "'";
		$stmt = DB::run($sql);
		
		$injury_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the injury_number to the injury 
		$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $injury_table_uuid  ."', '" . $injury->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		$stmt = DB::run($sql);
		//track now
		trackSettlement("insert", $new_id);

		//new the case_uuid
		$kase = getKaseInfoBySettlement($new_id);
		$kase_uuid = $kase->uuid;
		// SAVE PRIOR ATTORNEY PAYMENTS
		savePriorAttorneyPayments($kase_uuid, $prior_attorney_payment_info, $_SESSION['user_customer_id']);

		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid, "message" => "Settlement added succesfully")); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function updateSettlement() {
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";

	$prior_attorney_payment_info;

	if(array_key_exists('prior_attorney_payment_info',$_POST)){
		$prior_attorney_payment_info = $_POST['prior_attorney_payment_info'];
		unset($_POST['prior_attorney_payment_info']);
	}


	// future medical is checkbox so it will not come if not check
	if(!isset($_POST['future_medical'])){
		$_POST['future_medical'] = 'N';
	}

	// Loop through POST fields
	foreach($_POST as $fieldname => $value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);

		if ($fieldname == "table_name") {
			$table_name = $value;
			continue;
		}

		// main record id
		if ($fieldname == "settlement_id" || $fieldname == "table_id" || $fieldname == "id") {
			$table_id = (int)$value;
			continue;
		}

		if (in_array($fieldname, ["case_id", "injury_id"])) {
			continue;
		}

		// handle date fields
		if (strpos($fieldname, "date_") !== false) {
			if ($value != "") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}

		// handle fee options group
		if ($fieldname == "fee_options") {
			$fee_options = $value;

			switch ($fee_options) {
				case "F_A":
					$arrSet[] = "`f_and_a` = 'Y'";
					$arrSet[] = "`c_and_r` = 'N'";
					$arrSet[] = "`stip` = 'N'";
					break;

				case "C_R":
					$arrSet[] = "`f_and_a` = 'N'";
					$arrSet[] = "`c_and_r` = 'Y'";
					$arrSet[] = "`stip` = 'N'";
					break;

				case "STIP":
					$arrSet[] = "`f_and_a` = 'N'";
					$arrSet[] = "`c_and_r` = 'N'";
					$arrSet[] = "`stip` = 'Y'";
					break;
			}

			continue;
		}

		// normal field update
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}

	// ensure table name and id are valid
	if ($table_name == "" || $table_id == "") {
		die("Missing table_name or record ID.");
	}

	// Build where clause
	$where_clause = "`" . $table_name . "_id` = " . (int)$table_id;

	// Build SQL
	$sql = "UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause . "
	AND `cse_" . $table_name . "`.customer_id = " . (int)$_SESSION['user_customer_id'];

	// For debugging
	// die($sql);

	try {
		$stmt = DB::run($sql);
		
		echo json_encode(array("id"=>$table_id, "message" => "Settlement Updated succesfully")); 
		
		trackSettlement("update", $table_id);

		//new the case_uuid
		$kase = getKaseInfoBySettlement($table_id);
		$kase_uuid = $kase->uuid;
		// SAVE PRIOR ATTORNEY PAYMENTS
		savePriorAttorneyPayments($kase_uuid, $prior_attorney_payment_info, $_SESSION['user_customer_id']);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function trackSettlement($operation, $settlement_id) {
	
	$sql = "INSERT INTO cse_settlement_track (`user_uuid`, `user_logon`, `operation`, `settlement_id`,`settlement_uuid`,`date_settled`,`amount_of_settlement`,`amount_of_fee`,`c_and_r`,`stip`,`f_and_a`,`date_approved`,`date_fee_received`,`attorney`,`customer_id`,`deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `settlement_id`,`settlement_uuid`,`date_settled`,`amount_of_settlement`,`amount_of_fee`,`c_and_r`,`stip`,`f_and_a`,`date_approved`,`date_fee_received`,`attorney`,`customer_id`,`deleted`
	FROM cse_settlement
	WHERE 1
	AND settlement_id = " . $settlement_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		//new the case_uuid
		$kase = getKaseInfoBySettlement($settlement_id);
		//die(print_r($kase));
		$activity_category = "Settlement";
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
		
		$doi = date("m/d/Y", strtotime($kase->start_date));
		$doi = $kase->adj_number . " - " . $doi;			
		$activity = "Settlement Information  for [" . $doi . "] was " . $operation . "  by " . $_SESSION['user_name'];
		recordActivity($operation, $activity, $kase->uuid, $new_id, $activity_category);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
}
/**
 * Save or update prior attorney payments
 * 
 * @param string $case_uuid  The settlement/case UUID
 * @param string $jsonData   JSON string of prior_attorney_payment_info from POST
 * @param int $customer_id   Current customer ID
 */
function savePriorAttorneyPayments($case_uuid, $jsonData, $customer_id) {
    if (empty($case_uuid) || empty($customer_id)) return;

    // Mark all existing payments for this case as deleted
    DB::run("
        UPDATE cse_prior_attorney_payments 
        SET deleted = 'Y', updated_date = NOW() 
        WHERE case_uuid = :case_uuid
    ", ['case_uuid' => $case_uuid]);

    if (empty($jsonData)) return;

    $payments = json_decode($jsonData, true);
    if (!is_array($payments) || count($payments) === 0) return;

    foreach ($payments as $p) {
        $prior_attorney_id = !empty($p['prior_attorney_id']) ? (int)$p['prior_attorney_id'] : null;
        if ($prior_attorney_id === null) continue;

        $payment_amount = isset($p['payment_amount']) && $p['payment_amount'] !== "" ? $p['payment_amount'] : null;
        $payment_status = !empty($p['payment_status']) ? $p['payment_status'] : null;
        $payment_date = !empty($p['payment_date']) ? date("Y-m-d", strtotime($p['payment_date'])) : null;
        $notes = !empty($p['notes']) ? addslashes($p['notes']) : null;

        // Check if record already exists (ignore customer_id)
        $existing = DB::run("
            SELECT prior_attorney_payments_id 
            FROM cse_prior_attorney_payments 
            WHERE case_uuid = :case_uuid 
              AND prior_attorney_id = :prior_attorney_id 
              AND deleted = 'Y'
            LIMIT 1
        ", [
            'case_uuid' => $case_uuid,
            'prior_attorney_id' => $prior_attorney_id
        ])->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing record
            DB::run("
                UPDATE cse_prior_attorney_payments
                SET payment_amount = :payment_amount,
                    payment_status = :payment_status,
                    payment_date = :payment_date,
                    notes = :notes,
                    deleted = 'N',
                    updated_date = NOW()
                WHERE prior_attorney_payments_id = :id
            ", [
                'payment_amount' => $payment_amount,
                'payment_status' => $payment_status,
                'payment_date' => $payment_date,
                'notes' => $notes,
                'id' => $existing['prior_attorney_payments_id']
            ]);
        } else {
            // Insert new record
            $uuid = uniqid("PAP_", true);
            DB::run("
                INSERT INTO cse_prior_attorney_payments
                (prior_attorney_payments_uuid, case_uuid, prior_attorney_id, payment_amount, 
                 payment_status, payment_date, notes, customer_id, deleted, updated_date)
                VALUES
                (:uuid, :case_uuid, :prior_attorney_id, :payment_amount, 
                 :payment_status, :payment_date, :notes, :customer_id, 'N', NOW())
            ", [
                'uuid' => $uuid,
                'case_uuid' => $case_uuid,
                'prior_attorney_id' => $prior_attorney_id,
                'payment_amount' => $payment_amount,
                'payment_status' => $payment_status,
                'payment_date' => $payment_date,
                'notes' => $notes,
                'customer_id' => $customer_id
            ]);
        }
    }
}

function settlementsByDoctor($doctors) {
	session_write_close();
	$sql = "SELECT corporation_id, company_name, bodyparts_id, `code`, `description`,
			COUNT(DISTINCT case_id) case_count, AVG(amount_of_settlement) avg_settlement, 
			MIN(amount_of_settlement) min_settlement, MAX(amount_of_settlement) max_settlement
			FROM (
				SELECT ccase.case_id, ccase.case_name, inj.start_date, inj.end_date, 
				sett.amount_of_settlement, 
				#fee.*, 
				corp.corporation_id,
				corp.company_name,
				parts.bodyparts_id, parts.`code`, parts.`description`  
				FROM cse_settlement sett
				INNER JOIN cse_injury_settlement iset
				ON sett.settlement_uuid = iset.settlement_uuid AND iset.deleted = 'N' AND iset.`attribute` = 'main'
				INNER JOIN cse_injury inj
				ON iset.injury_uuid = inj.injury_uuid
				INNER JOIN cse_case_injury cci
				ON inj.injury_uuid = cci.injury_uuid
				INNER JOIN cse_case ccase
				ON cci.case_uuid = ccase.case_uuid
			
				LEFT OUTER JOIN cse_settlement_fee sfee
				ON sett.settlement_uuid = sfee.settlement_uuid
				LEFT OUTER JOIN cse_fee fee
				ON sfee.fee_uuid = fee.fee_uuid
				LEFT OUTER JOIN cse_corporation corp
				ON fee.fee_doctor_id = corp.corporation_id
			
				LEFT OUTER JOIN cse_injury_bodyparts cib
				ON inj.injury_uuid = cib.injury_uuid
				LEFT OUTER JOIN cse_bodyparts parts
				ON cib.bodyparts_uuid = parts.bodyparts_uuid
			
				WHERE 1
				#AND parts.`code` IN (519)
				AND corp.corporation_id IN (:doctors)
				AND ccase.customer_id = :customer_id
			) settleds
			GROUP BY corporation_id, bodyparts_id
			GROUP BY company_name, `code`";
	$customer_id = $_SESSION["user_customer_id"];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("doctors", $doctors);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$settlements = $stmt->fetchObject();

        // Include support for JSONP requests
         echo json_encode($settlements);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addSettlementSheet($injury_id = "") {
	session_write_close();
	$table_name = "settlementsheet";
	if ($injury_id == "") {
		$injury_id = passed_var("injury_id", "post");
		$due = passed_var("due", "post");
		$date_settled = passed_var("date_settled", "post");
		$distributed_date = passed_var("distrib", "post");		
	} else {
		//creating a new settlement after a freeze
		$due = "0.00";
		$date_settled = "";
		$distributed_date = "";		
	}
	if ($date_settled!="") {
		$date_settled = date("Y-m-d", strtotime($date_settled));
	} else {
		$date_settled = "0000-00-00";
	}
	if ($distributed_date!="") {
		$distributed_date = date("Y-m-d", strtotime($distributed_date));
	} else {
		$distributed_date = "0000-00-00";
	}
	
	$customer_id = $_SESSION['user_customer_id'];
	$data = json_encode($_POST);
	$table_uuid = uniqid("SS", false);
	
	$sql = "
	INSERT INTO `cse_" . $table_name . "` (`settlementsheet_uuid`, `date_settled`, `due`, `data`, `customer_id`)
	VALUES (:table_uuid, :date_settled, :due, :data, :customer_id)";
	
	//die($sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("table_uuid", $table_uuid);
		$stmt->bindParam("date_settled", $date_settled);
		$stmt->bindParam("due", $due);
		$stmt->bindParam("data", $data);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		
		echo json_encode(array("id"=>$new_id)); 
		
		//attach to injury
		$injury_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$table_name = "settlement";
		$injury = getInjuryInfo($injury_id);
		$customer_id = $_SESSION['user_customer_id'];
		
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the injury_number to the injury 
		$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $injury_table_uuid  ."', '" . $injury->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
		
		$stmt = DB::run($sql);
		
		//probably don't need it on insert, but gotta do it just in case...
		if ($distributed_date != "0000-00-00") {
			//update the status
			$sql = "UPDATE  `cse_" . $table_name . "` 
			SET `status` = 'D'
			WHERE `settlementsheet_id` = :new_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("new_id", $new_id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			
		}
		
		trackSettlementSheet("insert", $new_id, $injury_id);
		
	} catch(PDOException $e) {	
		die($sql);
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	exit();
}
function updateSettlementSheet() {
	session_write_close();
	$table_name = "settlementsheet";
	$settlement_id = passed_var("table_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$due = passed_var("due", "post");
	$date_settled = passed_var("date_settled", "post");
	if ($date_settled!="") {
		$date_settled = date("Y-m-d", strtotime($date_settled));
	} else {
		$date_settled = "0000-00-00";
	}
	
	$distributed_date = passed_var("distrib", "post");
	if ($distributed_date!="") {
		$distributed_date = date("Y-m-d", strtotime($distributed_date));
	} else {
		$distributed_date = "0000-00-00";
	}
	
	$customer_id = $_SESSION['user_customer_id'];
	$data = json_encode($_POST);
	
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET `date_settled` = :date_settled,
	`due` = :due,
	`data` = :data
	WHERE `settlementsheet_id` = :settlement_id";
	$sql .= " AND `customer_id` = :customer_id";
	//die($sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("date_settled", $date_settled);
		$stmt->bindParam("due", $due);
		$stmt->bindParam("data", $data);
		$stmt->bindParam("settlement_id", $settlement_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		echo json_encode(array("id"=>$settlement_id)); 
		
		//probably don't need it on insert, but gotta do it just in case...
		if ($distributed_date != "0000-00-00") {
			//update the status
			$sql = "UPDATE  `cse_" . $table_name . "` 
			SET `status` = 'D'
			WHERE `settlementsheet_id` = :settlement_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("settlement_id", $settlement_id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			
		}
		
		trackSettlementSheet("update", $settlement_id, $injury_id);		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	exit();
}
function freezeSettlementSheet() {
	session_write_close();
	
	$id = passed_var("id", "post");
	$injury_id = passed_var("injury_id", "post");
	
	$customer_id = $_SESSION['user_customer_id'];
	$user_uuid = $_SESSION["user"];
	
	$sql = "SELECT settlementsheet_uuid
	FROM  `cse_settlementsheet`
	WHERE settlementsheet_id = :id
	AND customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$settlement = $stmt->fetchObject();
		
		$settlement_uuid = $settlement->settlementsheet_uuid;
		
		//update the relationship table
		$sql = "UPDATE cse_injury_settlement
		SET `attribute` = 'settlement_1',
		last_updated_date = '" . date("Y-m-d H:i:s") . "',
		last_update_user = :user_uuid
		WHERE settlement_uuid = :settlement_uuid
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("settlement_uuid", $settlement_uuid);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		//now let's add a new one
		addSettlementSheet($injury_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	exit();
}
function trackSettlementSheet($operation, $settlement_id, $injury_id) {
	
	$sql = "INSERT INTO cse_settlementsheet_track (`user_uuid`, `user_logon`, `operation`, 
	`settlementsheet_id`, `settlementsheet_uuid`, `date_settled`, `due`, `data`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', 
	`settlementsheet_id`, `settlementsheet_uuid`, `date_settled`, `due`, `data`, `deleted`, `customer_id`
	FROM cse_settlementsheet
	WHERE 1
	AND settlementsheet_id = " . $settlement_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		//new the case_uuid
		$injury = getInjuryInfo($injury_id);
		
		//die(print_r($injury));
		$activity_category = "Settlement";
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
		
		$doi = date("m/d/Y", strtotime($injury->start_date));
		$doi = $injury->adj_number . " - " . $doi;			
		$activity = "Settlement Information  for [" . $doi . "] was " . $operation . "  by " . $_SESSION['user_name'];
		recordActivity($operation, $activity, $injury->main_case_uuid, $new_id, $activity_category);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
}
