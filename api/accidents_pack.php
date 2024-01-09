<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/accident', function (RouteCollectorProxy $app) {
		$app->get('', 'getAccidents');
		$app->get('/{id}', 'getAccident');
		$app->post('/add', 'addAccident');
		$app->get('/kase/{case_id}', 'getInjuryAccident');
		//$app->post('/delete', 'deleteAccident');
		//$app->post('/update', 'updateAccident');
	});

	$app->group('/disability', function (RouteCollectorProxy $app) {
		$app->get('/{id}', 'getDisability');
		$app->get('/injury/{injury_id}', 'getInjuryDisability');
		$app->post('/add', 'addDisability');
		$app->post('/update', 'updateDisability');
	});
	$app->get('/disabilities/{injury_id}', 'getDisabilities');

	$app->group('/surgery', function (RouteCollectorProxy $app) {
		$app->get('/{id}', 'getSurgery');
		$app->post('/save', 'saveSurgery');
	});
	$app->get('/surgeries/{case_id}', 'getKaseSurgeries');
})->add(Api\Middleware\Authorize::class);

function getSurgery($surgery_id) {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT clm.surgery_id, clm.surgery_info, clm.surgery_id id
		FROM cse_surgery clm
		INNER JOIN cse_case ccase
		ON clm.case_uuid = ccase.case_uuid
		WHERE clm.deleted = 'N'
		AND ccase.deleted = 'N'
		AND ccase.customer_id = :customer_id
		AND clm.surgery_id = :surgery_id_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("surgery_id", $surgery_id);
		
		$stmt->execute();
		$surgery = $stmt->fetchObject();
		
        echo json_encode($surgery);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseSurgeries($case_id) {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT clm.surgery_id, clm.surgery_info, clm.surgery_id id
		FROM cse_surgery clm
		INNER JOIN cse_case ccase
		ON clm.case_uuid = ccase.case_uuid
		WHERE clm.deleted = 'N'
		AND ccase.deleted = 'N'
		AND ccase.customer_id = :customer_id
		AND ccase.case_id = :case_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("case_id", $case_id);
		
		$stmt->execute();
		$surgeries = $stmt->fetchAll(PDO::FETCH_OBJ);
		
        echo json_encode($surgeries);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function saveSurgery() {
	session_write_close();
	
	$surgery_id = passed_var("table_id", "post");
	$case_id = passed_var("case_id", "post");
	$surgery_info = json_encode($_POST);
	$customer_id = $_SESSION['user_customer_id'];
	
	$kase = getKaseInfo($case_id);
	$case_uuid = $kase->uuid;
	
	if ($surgery_id=="-1") {
		$sql = "INSERT INTO cse_surgery (`case_uuid`, `surgery_info`, `customer_id`)
		VALUES (:case_uuid, :surgery_info, :customer_id)";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->bindParam("surgery_info", $surgery_info);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$surgery_id = $db->lastInsertId();
	} else {
		$sql = "UPDATE cse_surgery 
		SET `surgery_info` = :surgery_info
		WHERE `surgery_id` = :surgery_id
		AND `customer_id` = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("surgery_id", $surgery_id);
		$stmt->bindParam("surgery_info", $surgery_info);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
	}
	
	echo json_encode(array("success"=>true, "surgery_id"=>$surgery_id));
}
function getAccidents() {
	session_write_close();
	
    $sql = "SELECT acc.* 
			FROM `cse_accident` acc 
			WHERE acc.deleted = 'N'
			AND acc.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by acc.accident_id";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$accidents = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($accidents);
        } else {
            echo $_GET['callback'] . '(' . json_encode($accidents) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAccident($id) {
	session_write_close();
	
    $sql = "SELECT acc.*, acc.accident_id id, acc.accident_uuid uuid
			FROM `cse_accident` acc 
			INNER JOIN cse_injury_accident iacc
			ON acc.accident_uuid = iacc.accident_uuid AND iacc.deleted = 'N'
			WHERE acc.accident_id=:id
			AND acc.customer_id = " . $_SESSION['user_customer_id'] . "
			AND acc.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$accident = $stmt->fetchObject();

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
function getInjuryAccident($case_id) {
	session_write_close();
	
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
function getDisability($id) {
	session_write_close();
	
    $sql = "SELECT disa.*, disa.disability_id id, disa.disability_uuid uuid
			FROM `cse_disability` disa 
			INNER JOIN cse_case_disability idisa
			ON disa.disability_uuid = idisa.disability_uuid AND idisa.deleted = 'N'
			WHERE disa.disability_id=:id
			AND disa.customer_id = " . $_SESSION['user_customer_id'] . "
			AND disa.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$disability = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($disability);
        } else {
            echo $_GET['callback'] . '(' . json_encode($disability) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getInjuryDisability($case_id) {
	session_write_close();
    $sql = "SELECT disa.*, disa.disability_id id, disa.disability_uuid uuid, inj.injury_id
			FROM `cse_disability` disa 
			INNER JOIN `cse_injury_disability` cid
			ON disa.disability_uuid = cid.disability_uuid AND cid.deleted = 'N'
			INNER JOIN cse_injury inj
			ON cid.injury_uuid = inj.injury_uuid
			INNER JOIN cse_case_injury cci
			ON inj.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
			WHERE ccase.case_id=:case_id
			AND disa.customer_id = " . $_SESSION['user_customer_id'] . "
			AND disa.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$disability = $stmt->fetchObject();

		//die($disability->disability_details);
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($disability);
        } else {
            echo $_GET['callback'] . '(' . json_encode($disability) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDisabilities($case_id) {
	session_write_close();
    $sql = "SELECT disa.*, disa.disability_id id, disa.disability_uuid uuid
			FROM `cse_disability` disa 
			INNER JOIN `cse_case_disability` cid
			ON disa.disability_uuid = cid.disability_uuid AND cid.deleted = 'N'
			INNER JOIN cse_case ccase
			ON cid.case_uuid = ccase.case_uuid
			WHERE ccase.case_id=:case_id
			AND disa.customer_id = " . $_SESSION['user_customer_id'] . "
			AND disa.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$disabilities = $stmt->fetchAll(PDO::FETCH_OBJ);

		//die($disability->disability_details);
        // Include support for JSONP requests
        echo json_encode($disabilities);
        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addDisability() {
	$table_name = "disability";
	$ailment = "";

    $fields = [];
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name" || $fieldname=="table_uuid" || $fieldname=="disability_id") {
			continue;
		}
		if ($fieldname=="ailment") {
			$ailment = $value;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
			//die(print_r($injury));
			continue;
		}
		if ($fieldname=="table_id") {
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

		$fields[$fieldname] = $value;
	}
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	try {
        $new_id = DB::insert("cse_$table_name", [
            "{$table_name}_uuid" => $table_uuid,
            'customer_id'        => $_SESSION['user_customer_id'],
        ] + $fields);
		/*
		$sql = "UPDATE `cse_injury_disability`
		SET deleted = 'Y'
		WHERE `injury_uuid` = '" . $injury_uuid . "'
		AND `customer_id` = '" . $_SESSION['user_customer_id'] . "'";
		$stmt = DB::run($sql);
		
		$injury_disability_uuid = uniqid("IA", false);
		$sql = "INSERT INTO cse_injury_disability (`injury_disability_uuid`, `injury_uuid`, `disability_uuid`, `attribute`, `customer_id`)
		VALUES ('" . $injury_disability_uuid  ."', '" . $injury_uuid . "', '" . $table_uuid . "', 'main', '" . $_SESSION['user_customer_id'] . "')";
		//echo $sql;  
		$stmt = DB::run($sql);
		*/

        DB::insert('cse_case_disability', [
            'case_disability_uuid' => uniqid("IA", false),
            'case_uuid'            => $case_uuid,
            'disability_uuid'      => $table_uuid,
            'attribute'            => $ailment,
            'customer_id'          => $_SESSION['user_customer_id'],
        ]);

        echo json_encode(["success" => true, "id" => $new_id]);
		//track now
		trackDisability("insert", $case_id, $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updateDisability() {
	$arrSet = array();
	$where_clause = "";
	$table_name = "disability";
	$table_id = "";
	$case_id = -1;
	$fee_id = -1;
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);

		if ($fieldname=="table_name" || $fieldname=="table_uuid" || $fieldname=="disability_id") {
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			continue;
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
		$stmt = DB::run($sql);
		
		echo json_encode(array("id"=>$table_id)); 
		
		trackDisability("update", $case_id, $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function trackDisability($operation, $case_id, $disability_id) {
	
	$sql = "INSERT INTO cse_disability_track (`user_uuid`, `user_logon`, `operation`, `disability_id`, `disability_uuid`, `claim`, `description`, `ailment`, `severity`, `duration`, `duty`, `limits`, `treatment`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `disability_id`, `disability_uuid`, `claim`, `description`, `ailment`, `severity`, `duration`, `duty`, `limits`, `treatment`, `deleted`, `customer_id`
	FROM cse_disability
	WHERE 1
	AND disability_id = " . $disability_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
		$new_id = DB::lastInsertId();
		//new the case_uuid
		$kase = getKaseInfo($case_id);
		//die(print_r($kase));
		$activity_category = "Disability";
		switch($operation){
			case "insert":
				$operation .= "ed";
				break;
			case "update":
            case "delete":
				$operation .= "d";
				break;
		}
					
		$activity = "Disability Information was " . $operation . "  by " . $_SESSION['user_name'];
		recordActivity($operation, $activity, $kase->uuid, $new_id, $activity_category);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
}
function addAccident() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "accident";

	$fields = [];
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			$injury = getInjuriesInfo($case_id);
			$injury_uuid = $injury[0]->uuid;
			//die(print_r($injury));
			continue;
		}
		if ($fieldname=="table_id") {
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
		
		$fields[$fieldname] = $value;
	}	
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	try {
        $new_id = DB::insert("cse_$table_name", [
            "{$table_name}_uuid" => $table_uuid,
            'customer_id'        => $_SESSION['user_customer_id'],
        ] + $fields);
		
		DB::update('cse_injury_accident', ['deleted' => 'Y'], ['injury_uuid' => $injury_uuid, 'customer_id' => $_SESSION['user_customer_id']]);

        DB::insert('cse_injury_accident', [
            'injury_accident_uuid' => uniqid("IA", false),
            'injury_uuid'          => $injury_uuid,
            'accident_uuid'        => $table_uuid,
            'attribute'            => 'main',
            'customer_id'          => $_SESSION['user_customer_id'],
        ]);

        echo json_encode(["success" => true, "id" => $new_id]);
		//track now
		//trackPerson("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
