<?php
$app->get('/fee/:type/:injury_id', authorize('user'),	'getFee');
$app->get('/getfee/:id', authorize('user'),	'getIndividualFee');
$app->get('/fees/:type/:injury_id', authorize('user'),	'getFees');
$app->get('/settlement_fees/:settlement_id/:case_type', authorize('user'),	'getSettlementFees');
$app->get('/settlement_feecount/:settlement_id', authorize('user'),	'getSettlementFeeCount');
$app->get('/wc_fees/:settlement_id', authorize('user'),	'getWCFees');
$app->get('/wc_type_fees/:settlement_id/:fee_type', authorize('user'),	'getWCFeesByType');
$app->get('/referral_fee/:referral_id', authorize('user'),	'referralFee');
//posts

$app->post('/fee/add', authorize('user'), 'addFee');
$app->post('/fees/add', authorize('user'), 'addFeesFromList');
$app->post('/fee/delete', authorize('user'), 'deleteFee');
$app->post('/fee/update', authorize('user'), 'updateFee');

$app->post('/costs/add', authorize('user'), 'addCosts');

function referralFee($referral_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT referral_info 
	FROM cse_settlement sett
	INNER JOIN cse_injury_settlement cis
	ON sett.settlement_uuid = cis.settlement_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
	
	INNER JOIN cse_case_injury cci
	ON cis.injury_uuid = cci.injury_uuid
	
	INNER JOIN cse_case_corporation ccc
	ON cci.case_uuid = ccc.case_uuid AND ccc.deleted = 'N'
	
	INNER JOIN cse_corporation corp
	ON ccc.corporation_uuid = corp.corporation_uuid
	
	WHERE corp.corporation_id = :referral_id
	AND corp.deleted = 'N'
	AND INSTR(referral_info , '\"referral_id\":\"$referral_id\"') > 0
	AND sett.customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("referral_id", $referral_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$fee = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

         echo json_encode($fee);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addCosts() {
	session_write_close();
	
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$injury_id = -1;
	$injury_uuid = "";
	$settlement_id = -1;
	$fee_id = -1;
	$fee_type = "";
	$arrCosts = array();
	$arrDates = array();
	$arrComments = array();
	$arrIDs = array();
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="injury_id") {
			$injury_id = $value;
			$injury = getInjuryInfo($injury_id);
			$injury_uuid = $injury->uuid;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="fee_type") {
			$fee_type = $value;
		}
		//costs
		$pos = strpos($fieldname, "cost");
		if ($pos!==false) {
			if ($pos==0) {
				$cost_number = str_replace("cost", "", $fieldname);
				$arrCosts[$cost_number] = $value;
				continue;
			}
		}
		
		//dates
		$pos = strpos($fieldname, "date");
		if ($pos!==false) {
			if ($pos==0) {
				$date_number = str_replace("date", "", $fieldname);
				$arrDates[$date_number] = $value;
				continue;
			}
		}
		
		//comments
		$pos = strpos($fieldname, "comment");
		if ($pos!==false) {
			if ($pos==0) {
				$comment_number = str_replace("comment", "", $fieldname);
				$arrComments[$comment_number] = $value;
				continue;
			}
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	$db = getConnection();
	//die(print_r($arrDates));
	try { 
		foreach($arrCosts as $cost_number=>$cost) {
			//delete any previous relationship that may exist
			$sql = "UPDATE `cse_" . $table_name . "` fee, `cse_injury_" . $table_name . "` ifee
			SET `fee`.`deleted` = 'Y', ifee.deleted = 'Y' 
			WHERE 1
			AND fee.fee_uuid = ifee.fee_uuid
			AND ifee.injury_uuid = '" . $injury_uuid . "'
			AND `fee_type` = '" . $fee_type . "'
			AND `fee_check_number` = " . $cost_number . "
			AND fee.`customer_id` = " . $_SESSION['user_customer_id'];
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			if ($cost=="") {
				continue;
			}
			
			$fee_date = date("Y-m-d");
			if (isset($arrDates[$cost_number])) {
				if ($arrDates[$cost_number]!="") {
					$fee_date = date("Y-m-d", strtotime($arrDates[$cost_number]));
				}
			}
			
			$fee_comment = "";
			if (isset($arrComments[$cost_number])) {
				if ($arrComments[$cost_number]!="") {
					$fee_comment = $arrComments[$cost_number];
				}
			}
			
			$table_uuid = uniqid("KS", false);
			$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `fee_paid`, `fee_date`, `fee_recipient`, `fee_check_number`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
					VALUES('" . $_SESSION['user_customer_id'] . "', '" . $cost . "', '" . $fee_date . "', '" . $fee_comment . "', " . $cost_number . ", '" . $table_uuid . "', " . implode(",", $arrSet) . ")";
			//die($sql);
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$new_id = $db->lastInsertId();
			/*
			//delete any previous relationship that may exist
			$sql = "UPDATE `cse_injury_" . $table_name . "`
			SET `deleted` = 'Y' 
			AND `attribute` = '" . $fee_type . "'
			WHERE `injury_uuid` = '" . $injury_uuid . "'
			AND `customer_id` = " . $_SESSION['user_customer_id'];
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			*/
			$injury_table_uuid = uniqid("KA", false);
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the fee to the injury 
			$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $table_uuid . "', '" . $fee_type . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			/*
			//get the uuid of the settlement so we can hook it up
			$sql = "SELECT settlement_uuid uuid FROM cse_settlement WHERE settlement_id = :settlement_id ";
			$stmt = $db->prepare($sql);
			$stmt->bindParam("settlement_id", $settlement_id);
		
			$stmt->execute();
			$settlement = $stmt->fetchObject();
			
			//delete any previous relationship that may exist
			$sql = "UPDATE `cse_settlement_" . $table_name . "`
			SET `deleted` = 'Y' 
			WHERE `settlement_uuid` = '" . $settlement->uuid . "'";
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$settlement_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the fee to the settlement 
			$sql = "INSERT INTO cse_settlement_" . $table_name . " (`settlement_" . $table_name . "_uuid`, `settlement_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $settlement_table_uuid  ."', '" . $settlement->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			*/
			
			//track now
			//trackFee("insert", $new_id);
			$arrIDs[] = array("id"=>$new_id, "uuid"=>$table_uuid); 
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	//die(print_r($arrIDs));
	$db = null;
	echo json_encode($arrIDs);
}
function getWCFeesByType($settlement_id, $fee_type) {
	getWCFees($settlement_id, $fee_type);
}
function getWCFees($settlement_id, $fee_type = "") {
	session_write_close();
	
	//`fee_type`, SUM(`fee_billed`) sum_billed, SUM(`paid_fee`) sum_paid
	
	$sql = "SELECT `fee`.`fee_id`, `fee`.`fee_uuid`, `fee`.`fee_type`, `fee`.`fee_requested`, `fee`.`fee_date`, `fee`.`fee_billed`, `fee`.`fee_paid`,`fee`. `paid_fee`,
	`fee`.`fee_recipient`, 
	`fee`.`fee_check_number`, `fee`.`fee_memo`, `fee`.`fee_doctor_id`, `fee`.`fee_referral`, `fee`.`customer_id`, `fee`.`deleted`, `fee`.`fee_by`,
	sett.settlement_id, 
	IFNULL(pfee.fee_id, 999999) parent_fee_id, IFNULL(paid_fees.fee_total_paid, 0) fee_total_paid,
	`fee`.`fee_id` `id`, `fee`.`fee_uuid` `uuid`
	FROM `cse_settlement` sett
	
	INNER JOIN `cse_injury_settlement` sfee
	ON sett.settlement_uuid = sfee.settlement_uuid AND sfee.deleted = 'N' AND sfee.`attribute` = 'main'
	
	INNER JOIN `cse_injury_fee` ifee
	ON sfee.injury_uuid = ifee.injury_uuid AND ifee.deleted = 'N'
	
	INNER JOIN `cse_fee` fee
	ON ifee.fee_uuid = fee.fee_uuid
    
	LEFT OUTER JOIN `cse_fee` pfee
	ON fee.fee_parent_uuid = pfee.fee_uuid
	
	LEFT OUTER JOIN (
		SELECT fee.fee_uuid, SUM(cfees.paid_fee) fee_total_paid
		FROM cse_fee fee
		
		INNER JOIN cse_settlement_fee sfee
		ON fee.fee_uuid = sfee.fee_uuid
		
		INNER JOIN cse_settlement sett
		ON sfee.settlement_uuid = sett.settlement_uuid
		
		LEFT OUTER JOIN cse_fee cfees
		ON fee.fee_uuid = cfees.fee_parent_uuid AND cfees.fee_uuid != cfees.fee_parent_uuid
		WHERE 1
		AND sett.settlement_id = :settlement_id
		AND `sett`.customer_id = :customer_id
		AND fee.deleted = 'N'
		AND cfees.deleted = 'N'
		GROUP BY fee.fee_uuid
	) paid_fees
	ON fee.fee_uuid = paid_fees.fee_uuid
	
	WHERE 1
	AND (`sfee`.`deleted` = 'N' OR `sfee`.`deleted` IS NULL)
	AND fee.deleted = 'N'
	AND sett.settlement_id = :settlement_id
	AND `sett`.customer_id = :customer_id
	";
	if ($fee_type!="") {
		if ($fee_type=="ss" || $fee_type=="depo") {
			if ($fee_type=="ss") {
				$sql .= "
				AND (fee.fee_type = 'ss' OR fee.fee_type = 'Soc Sec')
				";
			}
			if ($fee_type=="depo") {
				$sql .= "
				AND (fee.fee_type = 'depo' OR fee.fee_type = 'deposition')
				";
			}
		} else {
			$sql .= "
			AND LOWER(fee.fee_type) = :fee_type
			";
		}
	}
	$sql .= "
		
	UNION
	
	SELECT `fee`.`fee_id`, `fee`.`fee_uuid`, `fee`.`fee_type`, `fee`.`fee_requested`, `fee`.`fee_date`, `fee`.`fee_billed`, `fee`.`fee_paid`,`fee`. `paid_fee`,
	`fee`.`fee_recipient`, 
	`fee`.`fee_check_number`,  
	`fee`.`fee_memo`, `fee`.`fee_doctor_id`, `fee`.`fee_referral`, `fee`.`customer_id`, `fee`.`deleted`, `fee`.`fee_by`,
	sett.settlement_id, 
	IFNULL(pfee.fee_id, 999999) parent_fee_id, IFNULL(paid_fees.fee_total_paid, 0) fee_total_paid,
	`fee`.`fee_id` `id`, `fee`.`fee_uuid` `uuid`
	
	FROM `cse_settlement` sett
	
	INNER JOIN `cse_settlement_fee` sfee
	ON sett.settlement_uuid = sfee.settlement_uuid
	
	INNER JOIN `cse_fee` fee
	ON sfee.fee_uuid = fee.fee_uuid
	
	LEFT OUTER JOIN `cse_fee` pfee
	ON fee.fee_parent_uuid = pfee.fee_uuid
    
	LEFT OUTER JOIN (
		SELECT fee.fee_uuid, SUM(cfees.paid_fee) fee_total_paid
		FROM cse_fee fee
		
		INNER JOIN cse_settlement_fee sfee
		ON fee.fee_uuid = sfee.fee_uuid
		
		INNER JOIN cse_settlement sett
		ON sfee.settlement_uuid = sett.settlement_uuid
		
		LEFT OUTER JOIN cse_fee cfees
		ON fee.fee_uuid = cfees.fee_parent_uuid AND cfees.fee_uuid != cfees.fee_parent_uuid
		WHERE 1
		AND sett.settlement_id = :settlement_id
		AND `sett`.customer_id = :customer_id
		AND fee.deleted = 'N'
		AND cfees.deleted = 'N'
		GROUP BY fee.fee_uuid
	) paid_fees
	ON fee.fee_uuid = paid_fees.fee_uuid
	
	WHERE 1
	AND (`sfee`.`deleted` = 'N' OR `sfee`.`deleted` IS NULL)
	AND fee.deleted = 'N'
	AND sett.settlement_id = :settlement_id
	AND `sett`.customer_id = :customer_id";
	if ($fee_type!="") {
		if ($fee_type=="ss" || $fee_type=="depo") {
			if ($fee_type=="ss") {
				$sql .= "
				AND (fee.fee_type = 'ss' OR fee.fee_type = 'Soc Sec')
				";
			}
			if ($fee_type=="depo") {
				$sql .= "
				AND (fee.fee_type = 'depo' OR fee.fee_type = 'deposition')
				";
			}
		} else {
			$sql .= "
			AND LOWER(fee.fee_type) = :fee_type
			";
		}
	}
	$sql .= "
	ORDER BY parent_fee_id, fee_requested";
	
	$customer_id = $_SESSION["user_customer_id"];
	// echo $customer_id." -- ".$fee_type."  == ";
	// echo $settlement_id;
	// die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("settlement_id", $settlement_id);
		$stmt->bindParam("customer_id", $customer_id);
		if ($fee_type!="") {
			$stmt->bindParam("fee_type", $fee_type);
		}
		$stmt->execute();
		$fees = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

        // Include support for JSONP requests
         echo json_encode($fees);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getSettlementFeeCount($settlement_id) {
	session_write_close();
	
	$sql = "SELECT COUNT(fee.fee_id) fee_count
	FROM cse_fee fee
	
	INNER JOIN cse_settlement_fee csf
	ON fee.fee_uuid = csf.fee_uuid
	
	INNER JOIN cse_settlement sett
	ON csf.settlement_uuid = sett.settlement_uuid
	
	INNER JOIN cse_injury_settlement isett
	ON sett.settlement_uuid = isett.settlement_uuid
	
	INNER JOIN cse_injury inj
	ON isett.injury_uuid = inj.injury_uuid
	
	WHERE sett.settlement_id = :settlement_id
	AND fee.deleted = 'N'
	AND fee.customer_id = :customer_id";
	
	$customer_id = $_SESSION["user_customer_id"];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("settlement_id", $settlement_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$fees = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
         echo json_encode($fees);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getSettlementFees($settlement_id, $case_type) {
	session_write_close();
	$case_type = str_replace("_", " ", $case_type);
	$blnWCAB = checkWCAB($case_type);
	
	$suffix = "";
	if (!$blnWCAB) {
		$suffix = "sheet";
	}
	$sql = "SELECT `fee`.`fee_id`, `fee`.`fee_uuid`, `fee_type`, `fee_requested`, `fee_date`, `fee_billed`,  `fee_paid`, `paid_fee`, `total_payments`, 
	`fee_recipient`, 
	`fee_check_number`,  
	`fee_memo`, `fee_doctor_id`, `fee_referral`, `fee`.`customer_id`, `fee`.`deleted`,
	sett.settlement" . $suffix . "_id settlement_id,
	`fee`.`fee_id` `id`, `fee`.`fee_uuid` `uuid`
	FROM `cse_settlement" . $suffix . "` sett
	INNER JOIN `cse_settlement_fee` sfee
	ON sett.settlement" . $suffix . "_uuid = sfee.settlement_uuid
	INNER JOIN `cse_fee` fee
	ON sfee.fee_uuid = fee.fee_uuid
    
    LEFT OUTER JOIN (
		SELECT cf.fee_id, SUM(cc.payment) total_payments
        FROM cse_check cc
        INNER JOIN cse_fee_check cfc
        ON cc.check_uuid = cfc.check_uuid
        INNER JOIN cse_fee cf
        ON cfc.fee_uuid = cf.fee_uuid
        INNER JOIN cse_settlement_fee csf
        ON cf.fee_uuid = csf.fee_uuid
        INNER JOIN cse_settlement" . $suffix . " cs
        ON csf.settlement_uuid = cs.settlement" . $suffix . "_uuid
        WHERE cs.settlement" . $suffix . "_id = :settlement_id
        AND cs.customer_id = :customer_id
        GROUP BY cf.fee_id
    ) checks
    ON fee.fee_id = `checks`.fee_id
    
	WHERE 1
	AND (`sfee`.`deleted` = 'N' OR `sfee`.`deleted` IS NULL)
	AND fee.deleted = 'N'
	AND sett.settlement" . $suffix . "_id = :settlement_id
	AND `sett`.customer_id = :customer_id
	ORDER BY  `fee`.fee_id DESC";
	
	$customer_id = $_SESSION["user_customer_id"];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("settlement_id", $settlement_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$fees = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

        // Include support for JSONP requests
         echo json_encode($fees);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getFees($type, $injury_id) {
	session_write_close();
	
	$sql = "SELECT `fee_id`, `fee`.`fee_uuid`, `fee_type`, `fee_date`, `fee_paid`, `fee_recipient`, `fee_check_number`, `fee_referral`, `fee`.`customer_id`, `fee`.`deleted`, `fee`.`paid_fee`, `fee`.`hourly_rate`, `fee`.`hours`, `fee`.`fee_by`,
	'-1' settlement_id,
	cc.injury_id, cc.adj_number, cc.start_date, cc.end_date, `ccase`.`case_id`,
	`fee_id` `id`, `fee`.`fee_uuid` `uuid`
	FROM `cse_injury` cc
	INNER JOIN `cse_case_injury` cci
	ON `cc`.`injury_uuid` = `cci`.`injury_uuid`
	INNER JOIN `cse_case` `ccase`
	ON `cci`.`case_uuid` = `ccase`.`case_uuid`
	INNER JOIN `cse_injury_fee` csf 
	ON cc.injury_uuid = csf.injury_uuid
	INNER JOIN `cse_fee` fee
	ON csf.fee_uuid = fee.fee_uuid
	WHERE `cc`.`deleted` = 'N'
	AND (`csf`.`deleted` = 'N' OR `csf`.`deleted` IS NULL)
	AND cc.injury_id = :injury_id
	AND fee.fee_type = :type
	AND `cc`.customer_id = " . $_SESSION['user_customer_id'] . "
	ORDER BY  `fee`.fee_id DESC ";

	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->bindParam("type", $type);
		$stmt->execute();
		$fees = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

        // Include support for JSONP requests
         echo json_encode($fees);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getIndividualFee($id) {
	//getFee("", "", $id);
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT `fee`.*,
	`fee_id` id, `fee`.`fee_uuid` uuid,
	IFNULL(sett.settlement_id, 0) settlement_id
	FROM  `cse_fee` fee
	
	LEFT OUTER JOIN `cse_settlement_fee` sfee
	ON fee.fee_uuid = sfee.fee_uuid
	
	LEFT OUTER JOIN `cse_settlement` sett
	ON sfee.settlement_uuid = sett.settlement_uuid
	
	WHERE `fee`.customer_id = :customer_id
	AND fee.fee_id = :fee_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("fee_id", $id);
		
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$fee = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($fee);
        } else {
            echo $_GET['callback'] . '(' . json_encode($fee) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getFee($type, $injury_id, $fee_id = "") {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
   $sql = "SELECT `fee_id`, `fee`.`fee_uuid`, `fee_type`, `fee_date`, `fee_paid`, `fee_recipient`, `fee_check_number`, `fee_referral`, `fee`.`customer_id`, `fee`.`deleted`, `fee`.`paid_fee`, `fee`.`hourly_rate`, `fee`.`hours`, `fee`.`fee_by`,
	'-1' settlement_id,
	cc.injury_id, cc.adj_number, cc.start_date, cc.end_date, `ccase`.`case_id`,
	`fee_id` `id`, `fee`.`fee_uuid` `uuid`
	FROM `cse_injury` cc
	INNER JOIN `cse_case_injury` cci
	ON `cc`.`injury_uuid` = `cci`.`injury_uuid`
	INNER JOIN `cse_case` `ccase`
	ON `cci`.`case_uuid` = `ccase`.`case_uuid`
	INNER JOIN `cse_injury_fee` csf 
	ON cc.injury_uuid = csf.injury_uuid
	INNER JOIN `cse_fee` fee
	ON csf.fee_uuid = fee.fee_uuid
	WHERE `cc`.`deleted` = 'N'
	AND (`csf`.`deleted` = 'N' OR `csf`.`deleted` IS NULL)
	AND `cc`.customer_id = :customer_id";
	if ($fee_id=="") {
		$sql .= "
		AND cc.injury_id = :injury_id
		AND fee.fee_type = :type";
	} else {
		$sql .= "
		AND fee.fee_id = :fee_id";
	}
	$sql .= "
	ORDER BY  `fee`.fee_id DESC ";
	//echo $sql;
	//die();
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($fee_id=="") {
			$stmt->bindParam("injury_id", $injury_id);
			$stmt->bindParam("type", $type);
		} else {
			$stmt->bindParam("fee_id", $fee_id);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$fee = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($fee);
        } else {
            echo $_GET['callback'] . '(' . json_encode($fee) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getFeeInfo($fee_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
   $sql = "SELECT fee.*,
	`fee_id` `id`, `fee`.`fee_uuid` `uuid`
	FROM  `cse_fee` fee
	WHERE 1
	AND fee.fee_id = :fee_id
	AND `fee`.customer_id = :customer_id";
	//echo $sql;
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("fee_id", $fee_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$fee = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

		return $fee;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addFeesFromList() {
	session_write_close();
	$table_name = "";
	
	$injury_id = passed_var("injury_id", "post");
	$injury = getInjuryInfo($injury_id);
	$injury_uuid = $injury->uuid;
	
	
	$settlement_id = "";
	$settlement_uuid = "";
	$customer_id = $_SESSION['user_customer_id'];
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="injury_id") {
			continue;
		}
		if ($fieldname=="settlement_id") {
			$settlement_id = $value;
			
			if ($settlement_id!="") {
				//get the uuid of the settlement so we can hook it up
				$sql = "SELECT settlement_uuid uuid 
				FROM cse_settlement 
				WHERE settlement_id = :settlement_id 
				AND customer_id = :customer_id";
				try { 
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("settlement_id", $settlement_id);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					$settlement = $stmt->fetchObject();
					
					$settlement_uuid = $settlement->uuid;
					
					$stmt->closeCursor(); $stmt = null; $db = null;
					
				} catch(PDOException $e) {	
					echo '{"error":{"text":'. $e->getMessage() .'}}'; 
				}
			} else {
				//create a settlement
				$sql = "INSERT INTO cse_settlement (`settlement_uuid`, `customer_id`)
				VALUES (:settlement_uuid, :customer_id)";
				
				$settlement_uuid = uniqid("ST", false);
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("settlement_uuid", $settlement_uuid);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$settlement_id = $db->lastInsertId();
				$stmt = null; $db = null;
				
				//delete any previous relationship that may exist
				$sql = "UPDATE `cse_injury_settlement`
				SET `deleted` = 'Y' 
				WHERE `injury_uuid` = :injury_uuid
				AND customer_id = :customer_id";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("injury_uuid", $injury_uuid);
				$stmt->bindParam("customer_id", $customer_id);  
				$stmt->execute();
				$stmt = null; $db = null;
				
				$injury_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the injury_number to the injury 
				$sql = "INSERT INTO cse_injury_settlement (`injury_settlement_uuid`, `injury_uuid`, `settlement_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $settlement_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				
				$stmt = null; $db = null;
				//track now
				trackSettlement("insert", $settlement_id);
			}
			continue;
		}
	}
	$arrFeeTypes = array("attorney", "deposition", "referral", "other", "rehabilitation", "ss");
	$arrIDs = array();	//return fee_ids to the screen
	foreach($arrFeeTypes as $fee_type) {
		$fee_id = passed_var("fee_id_" . $fee_type, "post");
		$fee = passed_var("fee_" . $fee_type, "post");
		
		if ($fee=="" || $fee == 0) {
			continue;
		}
		$date_requested = passed_var("date_requested_" . $fee_type, "post");
		if ($date_requested != "") {
			$date_requested = date("Y-m-d", strtotime($date_requested));
		} else {
			$date_requested = "0000-00-00";
		}
		$date_received = passed_var("date_received_" . $fee_type, "post");
		if ($date_received != "") {
			$date_received = date("Y-m-d", strtotime($date_received));
		} else {
			$date_received = "0000-00-00";
		}
		
		$memo = passed_var("memo_" . $fee_type, "post");
		$doctor_id = -1;
		if ($fee_type=="attorney") {
			$doctor_id = passed_var("doctor_" . $fee_type, "post");
			if ($doctor_id=="") {
				$doctor_id = -1;
			}
		}
		
		if ($fee_id=="") {
			$arrFields = array("`fee_type`", "`fee_requested`",  "`fee_date`", "`fee_paid`, `fee_memo`, `fee_doctor_id`, `customer_id`");
			$arrSet = array("'" . $fee_type . "'", "'" . $date_requested . "'", "'" . $date_received . "'", "'" . $fee . "'", "'" . addslashes($memo) . "'", "'" . $doctor_id . "'", "'" . $customer_id . "'");
			//insert the fee
			$table_uuid = uniqid("KS", false);
			$sql = "INSERT INTO `cse_fee` (`fee_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";

			try { 				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$new_id = $db->lastInsertId();
				$stmt = null; $db = null;
				
				$arrIDs[$fee_type] = $new_id;
				
				//attach the fees to the settlement
				$sql = "UPDATE `cse_settlement_fee`
				SET `deleted` = 'Y' 
				WHERE `settlement_uuid` = '" . $settlement_uuid . "'
				AND attribute = '" . $fee_type . "'";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
				
				$settlement_table_uuid = uniqid("KA", false);
				$attribute_1 = $fee_type;
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the fee to the settlement 
				$sql = "INSERT INTO cse_settlement_fee (`settlement_fee_uuid`, `settlement_uuid`, `fee_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $settlement_table_uuid  ."', '" . $settlement_uuid . "', '" . $table_uuid . "', '" . $fee_type . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
			} catch(PDOException $e) {	
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}	
		} else {
			$arrIDs[$fee_type] = $fee_id;
			
			$arrSet = array();
			$arrSet[] = "`fee_requested`='" . $date_requested . "'";
			$arrSet[] = "`fee_date`='" . $date_received . "'";
			$arrSet[] = "`fee_paid`='" . $fee . "'";
			$arrSet[] = "`fee_memo`='" . addslashes($memo) . "'";
			$arrSet[] = "`fee_doctor_id`='" . $doctor_id . "'";
			
			$where_clause = "`fee_id` = '" . $fee_id . "'";
			$sql = "UPDATE `cse_fee`
			SET " . implode(", ", $arrSet) . "
			WHERE " . $where_clause;
			$sql .= " AND `cse_fee`.customer_id = " . $customer_id;
			
			//die($sql);
			try { 
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
			} catch(PDOException $e) {	
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}	
		}
	}
	echo json_encode(array("success"=>true, "settlement_id"=>$settlement_id, "fee_ids"=>$arrIDs));
}
function addFee() {
	session_write_close();
	
	//die(print_r($_POST));
	
	//$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$injury_id = -1;
	$injury_uuid = "";
	$settlement_id = -1;
	$parent_table_id = -1;
	$parent_table_uuid = "";
	$fee_id = -1;
	$hourly_rate = 0;
	$fee_type = "";
	$fee_by = "";
	$paid_fee = "";
	$parent_table_uuid = "";
	$doctor_id = -1;
	
	$customer_id = $_SESSION['user_customer_id'];
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		//echo $fieldname . " - " . $value . "<br />";
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="hourly_rate") {
			$hourly_rate = $value;
			if ($hourly_rate=="") {
				$hourly_rate = 0;
			}
			$value = $hourly_rate;
		}
		if ($fieldname=="injury_id") {
			$injury_id = $value;
			$injury = getInjuryInfo($injury_id);
			$injury_uuid = $injury->uuid;
			continue;
		}
		if ($fieldname=="settlement_id") {
			$settlement_id = $value;
			continue;
		}
		if ($fieldname=="parent_table_id") {
			$parent_table_id = $value;
			
			if ($parent_table_id!="") {
				$parent = getFeeInfo($parent_table_id);
				$parent_table_uuid = $parent->uuid;
			}
			continue;
		}
		
		if ($fieldname=="paid_fee") {
			$paid_fee = $value;
		}
		if ($fieldname=="fee_by") {
			$fee_by = $value;
			
			if ($fee_by!=="") {
				$user = getUserInfo($fee_by);
				$user_uuid = $user->uuid;
				$value = $user->nickname;
			}
		}
		if ($fieldname=="fee_type") {
			$fee_type = $value;
		}
		if ($fieldname=="doctor_attorney") {
			$doctor_id = $value;
			if ($doctor_id=="") {
				$doctor_id = -1;
			}
			continue;
		}
		if ($fieldname=="fee_id" || $fieldname=="case_id" || $fieldname=="table_id" || $fieldname=="prior_referral_id") {
			continue;
		}
		if (strpos($fieldname, "date_") > -1 || strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	//die("stop");
	
	$arrFields[] = "`customer_id`";
	$arrSet[] = $customer_id;
	$table_uuid = uniqid("KS", false);
	
	if ($paid_fee > 0) {
		$arrFields[] = "`fee_date`";
		$arrSet[] = "'" . date("Y-m-d") . "'";
	}
	if ($parent_table_uuid != "") {
		$arrFields[] = "`fee_parent_uuid`";
		$arrSet[] = "'" .$parent_table_uuid . "'";
	} else {
		$arrFields[] = "`fee_parent_uuid`";
		$arrSet[] = "'" .$table_uuid . "'";
	}

	if ($doctor_id != -1) {
		$arrFields[] = "`fee_doctor_id`";
		$arrSet[] = "'" .$doctor_id . "'";
	}
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		//delete any previous relationship that may exist
		$sql = "UPDATE `cse_injury_" . $table_name . "`
		SET `deleted` = 'Y' 
		WHERE `injury_uuid` = '" . $injury->uuid . "'";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$stmt = null; $db = null;
		
		$injury_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the fee to the injury 
		$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $injury_table_uuid  ."', '" . $injury->uuid . "', '" . $table_uuid . "', '" . $fee_type . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		//echo $sql;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$stmt = null; $db = null;
		
		if ($fee_by!="") {
			$fee_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the fee to the fee 
			$sql = "INSERT INTO cse_fee_user (`fee_user_uuid`, `fee_uuid`, `user_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $fee_table_uuid  ."', '" . $table_uuid . "', '" . $user_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			//echo $sql;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		if ($settlement_id == "") {
			//create a settlement
			$sql = "INSERT INTO cse_settlement (`settlement_uuid`, `customer_id`)
			VALUES (:settlement_uuid, :customer_id)";
			
			$settlement_uuid = uniqid("ST", false);
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("settlement_uuid", $settlement_uuid);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$settlement_id = $db->lastInsertId();
			$stmt = null; $db = null;
			
			$injury_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the injury_number to the injury 
			$sql = "INSERT INTO cse_injury_settlement (`injury_settlement_uuid`, `injury_uuid`, `settlement_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $settlement_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$stmt = null; $db = null;
			//track now
			trackSettlement("insert", $settlement_id);
		}
		
		//get the uuid of the settlement so we can hook it up
		$sql = "SELECT settlement_uuid uuid FROM cse_settlement WHERE settlement_id = :settlement_id ";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("settlement_id", $settlement_id);
	
		$stmt->execute();
		$settlement = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		/*
		//delete any previous relationship that may exist
		$sql = "UPDATE `cse_settlement_" . $table_name . "`
		SET `deleted` = 'Y' 
		WHERE `settlement_uuid` = '" . $settlement->uuid . "'";
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		*/
		
		$settlement_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the fee to the settlement 
		$sql = "INSERT INTO cse_settlement_" . $table_name . " (`settlement_" . $table_name . "_uuid`, `settlement_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $settlement_table_uuid  ."', '" . $settlement->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$stmt = null; $db = null;
		
		//track now
		trackFee("insert", $new_id);
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
	} catch(PDOException $e) {	
		//echo "ERROR " . $sql;
		//die();
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function deleteFee() {
	$id = passed_var("id", "post");
	$sql = "UPDATE `cse_fee` 
			SET `deleted` = 'Y'
			WHERE `fee_id`=:id
			AND `cse_fee`.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$stmt = null; $db = null;
		echo json_encode(array("success"=>"fee marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	
	trackFee("delete", $id);
}
function updateFee() {
	$request = Slim::getInstance()->request();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$injury_id = -1;
	$fee_by = "";
	$original_fee_by = "";
	$paid_fee = "";
	$original_paid_fee = "";
	$fee_id = -1;
	$fee_uuid = "";
	$user_uuid = "";
	$parent_table_id = -1;
	$parent_table_uuid = "";
	$doctor_id = -1;
	$settlement_id = "";
	$settlement_uuid = "";
	$fee_type = "";
	$customer_id = $_SESSION["user_customer_id"];
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_id") {
			$fee = getFeeInfo($value);
			$original_fee_by = $fee->fee_by;
			$original_paid_fee = $fee->paid_fee;
			$original_doctor_id = $fee->fee_doctor_id;
			$fee_uuid = $fee->uuid;
		}
		if ($fieldname=="parent_table_id") {
			continue;
		}
		if ($fieldname=="paid_fee") {
			$paid_fee = $value;
		}
		if ($fieldname=="doctor_attorney") {
			$doctor_id = $value;
			if ($doctor_id=="") {
				$doctor_id = -1;
			}
			continue;
		}
		if ($fieldname=="fee_by") {
			$fee_by = $value;
			if ($fee_by!="") {
				$user = getUserInfo($fee_by);
				$user_uuid = $user->uuid;
				
				//we're storing the nickname in fee table
				$value = $user->nickname;
			}	
		}
		if ($fieldname=="hourly_rate" || $fieldname=="hours") {
			if ($value == "") {
				$value = 0;
			}
		}
		if ($fieldname=="fee_type") {
			$fee_type = $value;
		}
		if ($fieldname=="injury_id") {
			$injury_id = $value;
			continue;
		}
		if ($fieldname=="settlement_id") {
			$settlement_id = $value;
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="injury_id"  || $fieldname=="settlement_id") {
			continue;
		}

		if (strpos($fieldname, "date_") > -1) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}

		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	
	if ($original_paid_fee!=$paid_fee && $paid_fee > 0) {
		$arrSet[] = "`fee_date` = '" . date("Y-m-d") . "'";
	}
	if ($doctor_id!=$original_doctor_id) {
		$arrSet[] = "`fee_doctor_id`='" . $doctor_id . "'";
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		
		echo json_encode(array("id"=>$table_id)); 
		
		
		//make sure it's hooked up to the settlement
		$sql = "SELECT csf.settlement_fee_id
		FROM cse_settlement_fee csf
		
		INNER JOIN cse_settlement sett
		ON csf.settlement_uuid = sett.settlement_uuid
		
		INNER JOIN cse_fee fee
		ON csf.fee_uuid = fee.fee_uuid
		
		WHERE sett.settlement_id = :settlement_id
		AND fee.fee_id = :fee_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("settlement_id", $settlement_id);
		$stmt->bindParam("fee_id", $table_id);
		$stmt->execute();
		$fee_check = $stmt->fetchObject();
		
		$db = null;
		
		$blnSettled = true;
		if (!is_object($fee_check)) {
			$blnSettled = false;
		} else {
			if ($fee_check->settlement_fee_id=="") {
				$blnSettled = false;
			}
		}
		if (!$blnSettled) {
			$settlement = getSettlementInfo($settlement_id);
			//die(print_r($settlement));
			$settlement_uuid = $settlement->uuid;
			
			//attach the fees to the settlement
			$sql = "UPDATE `cse_settlement_fee`
			SET `deleted` = 'Y' 
			WHERE `settlement_uuid` = '" . $settlement_uuid . "'
			AND fee_uuid = '"  . $fee_uuid . "'
			AND attribute = '" . $fee_type . "'";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			$settlement_table_uuid = uniqid("KA", false);
			$attribute_1 = $fee_type;
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the fee to the settlement 
			$sql = "INSERT INTO cse_settlement_fee (`settlement_fee_uuid`, `settlement_uuid`, `fee_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $settlement_table_uuid  ."', '" . $settlement_uuid . "', '" . $fee_uuid . "', '" . $fee_type . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		if ($fee_by=="") {
			//clear out any prior relationship
			$sql = "UPDATE `cse_fee_user`
			SET `deleted` = 'Y' 
			WHERE `fee_uuid` = '" . $fee_uuid . "'";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		if ($fee_by!=$original_fee_by && $fee_by!="") {
			//clear out any prior relationship
			$sql = "UPDATE `cse_fee_user`
			SET `deleted` = 'Y' 
			WHERE `fee_uuid` = '" . $fee_uuid . "'";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;

			$fee_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the fee to the fee 
			$sql = "INSERT INTO cse_fee_user (`fee_user_uuid`, `fee_uuid`, `user_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $fee_table_uuid  ."', '" . $fee_uuid . "', '" . $user_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			//echo $sql;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		$operation = "update";
		if ($original_paid_fee!=$paid_fee) {
			$operation = "paid";
		}
		trackFee($operation, $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function trackFee($operation, $fee_id) {
	
	$sql = "INSERT INTO cse_fee_track (`user_uuid`, `user_logon`, `operation`, `fee_id`, `fee_uuid`, `fee_type`, `fee_requested`, `fee_date`, `fee_billed`, `fee_paid`, `fee_recipient`, `fee_memo`, `fee_doctor_id`, `fee_check_number`, `fee_referral`, `full_name`, `customer_id`, `deleted`, `paid_fee`, `hourly_rate`, `hours`, `fee_by`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `fee_id`, `fee_uuid`, `fee_type`, `fee_requested`, `fee_date`, `fee_billed`, `fee_paid`, `fee_recipient`, `fee_memo`, `fee_doctor_id`, `fee_check_number`, `fee_referral`, `full_name`, `customer_id`, `deleted`, `paid_fee`, `hourly_rate`, `hours`, `fee_by`
	FROM cse_fee
	WHERE 1
	AND fee_id = " . $fee_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		//new the case_uuid
		$kase = getKaseInfoByFee($fee_id);
		//die(print_r($kase));
		$activity_category = "Fee";
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
					
		$activity = "Fee Information was " . $operation . "  by " . $_SESSION['user_name'];
		recordActivity($operation, $activity, $kase->uuid, $new_id, $activity_category);
		$db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
}
?>