<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/purchases', function (RouteCollectorProxy $app) {
		$app->get('', 'getPurchases');
		$app->get('/supplier/{supplier_id}/{purchase_id}', 'getSupplierPurchases');
	});
	$app->group('/purchase', function (RouteCollectorProxy $app) {
		$app->get('/{purchase_id}', 'getPurchase');
		$app->post('/add', 'addPurchase');
		$app->post('/add/init', 'addPurchaseInit');
		$app->post('/update/pending/{purchase_id}', 'updatePurchasePending');
		$app->post('/update', 'updatePurchase');
	});
})->add(\Api\Middleware\Authorize::class);

function getPurchases() {
    $sql = "SELECT purs.*, purs.purchase_id id 
			FROM `rek_purchase` purs 
			WHERE purs.deleted = 'N'
			AND purs.customer_id = " . $_SESSION['user_customer_id'] . "
			AND purs.pending = 'N'
			ORDER by purs.purchase_id DESC";
			
			//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$purchases = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($purchases);
        } else {
            echo $_GET['callback'] . '(' . json_encode($purchases) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getSupplierpurchases($supplier_id, $purchase_id) {
    $sql = "SELECT pro.*, pro.purchase_id id 
			FROM `rek_purchase` pro 
			INNER JOIN `rek_corporation_purchase` cpro
			ON pro.purchase_id = cpro.purchase_id
			INNER JOIN `rek_corporation` corp
			ON cpro.corporation_id = corp.corporation_id
			WHERE pro.purchase_id != " . $purchase_id . "
			AND cpro.`corporation_id` = " . $supplier_id . "
			AND pro.customer_id = " . $_SESSION['user_customer_id'] . "
			AND pro.deleted = 'N'";
			//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("person_id", $person_id);
		$stmt = $db->query($sql);
		$supplier_purchases = $stmt->fetchAll(PDO::FETCH_OBJ);
        if (!isset($_GET['callback'])) {
            echo json_encode($supplier_purchases);
        } else {
            echo $_GET['callback'] . '(' . json_encode($supplier_purchases) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPurchase($purchase_id) {
    $sql = "SELECT prod.*, prod.purchase_id id 
			FROM `rek_purchase` prod 
			WHERE prod.purchase_id = :purchase_id
			AND prod.customer_id = " . $_SESSION['user_customer_id'] . "
			AND prod.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("purchase_id", $purchase_id);
		$stmt->execute();
		$purchase = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($purchase);
        } else {
            echo $_GET['callback'] . '(' . json_encode($purchase) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPurchaseInfo($purchase_id) {
    $sql = "SELECT prod.*, prod.purchase_id id 
			FROM `rek_purchase` prod 
			WHERE prod.purchase_id = :purchase_id
			AND prod.customer_id = " . $_SESSION['user_customer_id'] . "
			AND prod.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("purchase_id", $purchase_id);
		$stmt->execute();
		$purchase = $stmt->fetchObject();
		return $purchase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updatePurchasePending($purchase_id) {
	$arrFields = array();
	$arrSet = array();

	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		if ($fieldname=="total_cost") {
			$total_cost = $value;
			continue;
		}
		if ($fieldname=="purchase_id") {
			$purchase_id = $value;
			continue;
		}
		if ($fieldname=="total_product"){
			$total_product = $value;
			continue;
		}
		if ($fieldname=="supplier") {
			$supplier = $value;
			continue;
		}
		if ($fieldname=="batch_ids") {
			$batch_ids = $value;
			continue;
		}
		if ($fieldname=="purchase_info_bulk") {
			$purchase_info_bulk = $value;
			continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . str_replace("'", "\'", $value) . "'";
	}	
	
	//insert the parent record first
	$sql = "UPDATE `rek`.`rek_purchase`
			SET 
			   `total_cost` = '" . $total_cost . "',
			   `total_product` = '" . $total_product . "',
			   `supplier` = '" . $supplier . "',
			   `batch_ids` = '" . $batch_ids . "',
			   `purchase_info_bulk` = '" . $purchase_info_bulk . "',
			   `pending` = 'N'
			WHERE `purchase_id` = '" . $purchase_id . "'";
	//die($sql);
	try {
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>true, "id"=>$purchase_id));
		//track now
		//trackproduct("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function addPurchaseInit() {
	$table_name = "purchase";

	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "INSERT INTO `rek_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`) 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "')";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("success"=>true, "id"=>$new_id, "uuid"=>$table_uuid));
		//track now
		//trackpurchase("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function addPurchase() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "purchase";

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
		//trackpurchase("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updatePurchase() {
	$arrFields = array();
	$arrSet = array();

	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		//$fieldname = str_replace("Input", "", $fieldname);
		//$license_number = "";
		if ($fieldname=="first_name") {
			$first_name = $value;
			continue;
		}
		if ($fieldname=="purchase_id") {
			$purchase_id = $value;
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
	$sql = "UPDATE `rek`.`rek_purchase`
			SET 
			   `first_name` = '" . $first_name . "',
			   `last_name` = '" . $last_name . "',
			   `license_number` = '" . $license_number . "',
			   `dob` = '" . $dob . "',
			   `phone` = '" . $phone . "'
			WHERE `purchase_id` = '" . $purchase_id . "'";
	//$sql = "UPDATE `rek_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
		//VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("success"=>true, "id"=>$new_id));
		//track now
		//trackpurchase("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
