<?php
//get
$app->get('/purchases', authorize('user'), 'getPurchases');
$app->get('/purchase/:purchase_id', authorize('user'), 'getPurchase');
$app->get('/purchases/supplier/:supplier_id/:purchase_id', authorize('user'), 'getSupplierPurchases');

//posts
$app->post('/purchase/add', authorize('user'), 'addPurchase');
$app->post('/purchase/add/init', authorize('user'), 'addPurchaseInit');
$app->post('/purchase/update/pending/:purchase_id', authorize('user'), 'updatePurchasePending');
$app->post('/purchase/update', authorize('user'), 'updatePurchase');

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
	
		$db = null;
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
	
		$db = null;
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
		$db = null;

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
		$db = null;
		return $purchase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updatePurchasePending($purchase_id) {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "purchase";

	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		//$fieldname = str_replace("Input", "", $fieldname);
		//$license_number = "";
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
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		
		echo json_encode(array("success"=>true, "id"=>$purchase_id));
		//track now
		//trackproduct("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function addPurchaseInit() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "purchase";

	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "INSERT INTO `rek_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`) 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "')";
	//die($sql);
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id, "uuid"=>$table_uuid));
		//track now
		//trackpurchase("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function addPurchase() {
	$request = Slim::getInstance()->request();
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
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id));
		//track now
		//trackpurchase("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function updatePurchase() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "purchase";

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
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id));
		//track now
		//trackpurchase("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
?>