<?php
//$app->get('/lien/:id', authorize('user'),	'getLien');
$app->get('/lien/:injury_id', authorize('user'),	'getLiens');

//posts
//$app->post('/lien/delete', authorize('user'), 'deleteLien');
$app->post('/lien/add', authorize('user'), 'addLien');
$app->post('/lien/update', authorize('user'), 'updateLien');

function getLiens($injury_id) {
	$sql = "SELECT DISTINCT IFNULL(`cs`.`lien_id`, '') `lien_id`, 
	IFNULL(`cs`.`lien_uuid`, '') `lien_uuid`, 
	IFNULL(`cs`.`date_filed`, '') `date_filed`, 
	IFNULL(`cs`.`date_paid`, '') `date_paid`, 
	IFNULL(`cs`.`amount_of_lien`, 0) `amount_of_lien`, 
	IFNULL(`cs`.`amount_of_fee`, 150) `amount_of_fee`,
	IFNULL(`cs`.`appearance_fee`, 0) `appearance_fee`,
	IFNULL(`cs`.`amount_paid`, 0) `amount_paid`, 
	IFNULL(`cs`.`worker`, '') `worker`, IFNULL(`user`.nickname, '') as worker_name, 
	IFNULL(`user`.user_name, '') as worker_full_name,
	IFNULL(`cs`.lien_id, -1) `id`, IFNULL(`cs`.lien_uuid, '') uuid, 
	cc.injury_id, cc.adj_number, cc.start_date, cc.end_date, `ccase`.`case_id`
	FROM `cse_injury` cc
	INNER JOIN `cse_case_injury` cci
	ON `cc`.`injury_uuid` = `cci`.`injury_uuid`
	INNER JOIN `cse_case` `ccase`
	ON `cci`.`case_uuid` = `ccase`.`case_uuid`
	LEFT OUTER JOIN `cse_injury_lien` cis 
	ON cc.injury_uuid = cis.injury_uuid
	LEFT OUTER JOIN `cse_lien` cs
	ON cis.lien_uuid = cs.lien_uuid
	LEFT OUTER JOIN ikase.`cse_user` `user`
	ON `cs`.`worker` = `user`.user_id
	WHERE `cc`.`deleted` = 'N'
	AND (`cis`.`deleted` = 'N' OR `cis`.`deleted` IS NULL)
	AND cc.injury_id = :injury_id
	AND `cc`.customer_id = " . $_SESSION['user_customer_id'] . "
	ORDER BY  `cs`.lien_id DESC ";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->execute();
		$liens = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
         echo json_encode($liens);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getLien($lien_id) {
    $sql = "SELECT `cs`.*, `cs`.`lien_id` `id`, 
	`cs`.`lien_uuid` `uuid`
	FROM  `cse_lien` 
	WHERE `cs`.`deleted` = 'N'
	AND `cs`.`lien_id` = :lien_id
	AND `cs`.customer_id = " . $_SESSION['user_customer_id'];

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("lien_id", $lien_id);
		$stmt->execute();
		$lien = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($lien);
        } else {
            echo $_GET['callback'] . '(' . json_encode($lien) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function deleteLien() {
	$id = passed_var("lien_id", "post");
	$sql = "UPDATE cse_lien cs
			SET cs.`deleted` = 'Y'
			WHERE `lien_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("lien_id", $id);
		$stmt->execute();
		$db = null;
		trackLien("delete", $lien_id);
		echo json_encode(array("success"=>"lien marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addLien() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$injury_id = -1;
	$lien_id = -1;
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="injury_id") {
			$injury_id = $value;
			continue;
		}
		if ($fieldname=="lien_id" || $fieldname=="table_id") {
			continue;
		}
		if (strpos($fieldname, "date_") > -1) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	$arrFields[] = "`customer_id`";
	$arrSet[] = $_SESSION['user_customer_id'];
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		//get the uuid of the injury so we can hook it up
		$sql = "SELECT injury_uuid uuid FROM cse_injury WHERE injury_id = :injury_id ";
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_id", $injury_id);
	
		$stmt->execute();
		$injury = $stmt->fetchObject();
		
		
		$injury_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the injury_number to the injury 
		$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $injury_table_uuid  ."', '" . $injury->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		//track now
		trackLien("insert", $new_id);
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function updateLien() {
	$request = Slim::getInstance()->request();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$injury_id = -1;
	$lien_id = -1;
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="lien_id") {
			$injury_id = $value;
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="injury_id") {
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
		
		trackLien("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function trackLien($operation, $lien_id) {
	
	$sql = "INSERT INTO cse_lien_track (`user_uuid`, `user_logon`, `operation`, `lien_id`,`lien_uuid`,`date_filed`,
	`amount_of_lien`, `amount_of_fee`, `amount_paid`,  `appearance_fee`,
	`worker`,`customer_id`,`deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', 
	`lien_id`,`lien_uuid`,`date_filed`,`amount_of_lien`,
	`amount_of_fee`, `amount_paid`,  `appearance_fee`,`worker`,`customer_id`,`deleted`
	FROM cse_lien
	WHERE 1
	AND lien_id = " . $lien_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		//new the case_uuid
		$kase = getKaseInfoByLien($lien_id);
		//die(print_r($kase));
		$activity_category = "Lien";
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
		$activity = "Lien Information  for [" . $doi . "] was " . $operation . "  by " . $_SESSION['user_name'];
		recordActivity($operation, $activity, $kase->uuid, $new_id, $activity_category);
		$db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
}
?>