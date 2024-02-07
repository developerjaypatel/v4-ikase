<?php
//get
$app->get('/products', authorize('user'), 'getProducts');
$app->get('/product/:product_id', authorize('user'), 'getProduct');
$app->get('/products/supplier/:supplier_id/:product_id', authorize('user'), 'getSupplierProducts');

//posts
$app->post('/product/add', authorize('user'), 'addProduct');
$app->post('/product/purchase/add/:purchase_id', authorize('user'), 'addPurchaseProduct');
$app->post('/product/update', authorize('user'), 'updateProduct');
$app->post('/product/update/pending/:product_id', authorize('user'), 'updateProductPending');
$app->post('/product/batch/join/:product_id', authorize('user'), 'addProductBatch');

function getProducts() {
	if (isset($_GET["q"])) {
		$query = passed_var("q", "get");
		if ($query!="") {
			searchProduct($query);
			return;
		}
	}
    $sql = "SELECT prod.*, prod.product_id id 
			FROM `rek_product` prod 
			WHERE prod.deleted = 'N'
			AND prod.customer_id = " . $_SESSION['user_customer_id'] . "
			AND prod.purchase_activated = 'N'
			ORDER by prod.product_id";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$products = $stmt->fetchAll(PDO::FETCH_OBJ);
	
		$db = null;
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($products);
        } else {
            echo $_GET['callback'] . '(' . json_encode($products) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addBatch($product_uuid, $arrProductPurchase, $purchase_uuid) {
	$request = Slim::getInstance()->request();
	$table_name = "batch";
	
	$table_uuid = uniqid("RKB", false);
	
	$batch_number = uniqid("BATCH", false);
	
	//insert the parent record first
	$sql = "INSERT INTO `rek_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, `batch_number`, `product_uuid`) 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "','" .  $batch_number . "','" .  $product_uuid . "')";
	//die($sql);
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$db = null;
		
		//echo json_encode(array("success"=>true, "id"=>$new_id));
		//track now
		//trackproduct("insert", $new_id);	
		
		addProductBatch($product_uuid, $table_uuid, $arrProductPurchase);
		addPurchaseBatch($purchase_uuid, $table_uuid, $arrProductPurchase);
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function addProductBatch($product_uuid, $batch_uuid, $arrProductPurchase) {
	$request = Slim::getInstance()->request();
	$table_name = "product_batch";

	$table_uuid = uniqid("RK", false);
	//insert the parent record first
	$sql = "INSERT INTO `rek_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, `batch_uuid`, `product_uuid`) 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', '" . $batch_uuid . "', '" . $product_uuid . "')";
	//die($sql);
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$db = null;
		
		//echo json_encode($arrProductPurchase); 
		//echo json_encode(array("success"=>true, "id"=>$new_id));
		//track now
		//trackproduct("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function addPurchaseBatch($purchase_uuid, $batch_uuid, $arrProductPurchase) {
	$request = Slim::getInstance()->request();
	$table_name = "purchase_batch";

	$table_uuid = uniqid("RK", false);
	//insert the parent record first
	$sql = "INSERT INTO `rek_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, `batch_uuid`, `purchase_uuid`) 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', '" . $batch_uuid . "', '" . $purchase_uuid . "')";
	//die($sql);
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$db = null;
		//echo json_encode(array("success"=>true, "id"=>$new_id));
		echo json_encode($arrProductPurchase); 
		//track now
		//trackproduct("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function searchProduct($query) {
	$query = clean_html($query);
	//$query = str_replace("_", " ", $query);
	$query = trim($query);
	
	if (strlen($query) < 2) {
			return false;
			//getKases();
	}
	//WHERE INSTR(firm_name,:search_term) > 0
    $sql = "SELECT DISTINCT  pro.*, pro.product_id id, pro.product_name name 
			FROM `rek_product` pro
			WHERE pro.deleted = 'N'
			AND pro.product_name LIKE '%" . $query . "%'
			ORDER BY pro.product_name";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("query", $query);
		$stmt->execute();
		$products = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($products);
        } else {
            echo $_GET['callback'] . '(' . json_encode($products) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getSupplierProducts($supplier_id, $product_id) {
    $sql = "SELECT pro.*, pro.product_id id 
			FROM `rek_product` pro 
			INNER JOIN `rek_corporation_product` cpro
			ON pro.product_id = cpro.product_id
			INNER JOIN `rek_corporation` corp
			ON cpro.corporation_id = corp.corporation_id
			WHERE pro.product_id != " . $product_id . "
			AND cpro.`corporation_id` = " . $supplier_id . "
			AND pro.customer_id = " . $_SESSION['user_customer_id'] . "
			AND pro.deleted = 'N'
			AND prod.purchase_activated = 'N'";
			//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("person_id", $person_id);
		$stmt = $db->query($sql);
		$supplier_products = $stmt->fetchAll(PDO::FETCH_OBJ);
	
		$db = null;
        if (!isset($_GET['callback'])) {
            echo json_encode($supplier_products);
        } else {
            echo $_GET['callback'] . '(' . json_encode($supplier_products) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getProduct($product_id) {
    $sql = "SELECT prod.*, prod.product_id id 
			FROM `rek_product` prod 
			WHERE prod.product_id = :product_id
			AND prod.customer_id = " . $_SESSION['user_customer_id'] . "
			AND prod.deleted = 'N'
			AND prod.purchase_activated = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("product_id", $product_id);
		$stmt->execute();
		$product = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($product);
        } else {
            echo $_GET['callback'] . '(' . json_encode($product) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addProduct() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "product";

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
		//trackproduct("insert", $new_id);	
		
		
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function addPurchaseProduct($purchase_uuid) {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "product";

	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		//$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="quantity"){
			$quantity = $value;
			//continue;
		}
		
		if ($fieldname=="product_name"){
			//$fieldname="product_name";
			$product_name = $value;
			//continue;
		}
		
		if ($fieldname=="cost"){
			$cost = $value;
			//continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . str_replace("'", "\'", $value) . "'";
	}	
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "INSERT INTO `rek_" . $table_name ."` (`" . $table_name . "_uuid`, `purchase_activated`, `customer_id`, " . implode(",", $arrFields) . ") 
		VALUES('" . $table_uuid . "', 'Y', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$product_id = $db->lastInsertId();
		
		$db = null;
		
		//echo json_encode(array("success"=>true, "id"=>$new_id, "quantity"=>$quantity, "cost"=>$cost));
		$arrProductPurchase = array("success"=>true, "id"=>$product_id, "name"=>$product_name, "quantity"=>$quantity, "cost"=>$cost);
		//track now
		//trackproduct("insert", $new_id);	
		
		addBatch($table_uuid, $arrProductPurchase, $purchase_uuid);
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function updateProductPending($product_uuid) {
	$request = Slim::getInstance()->request();

	$sql = "UPDATE `rek`.`rek_product`
			SET 
			   `purchase_activated` = 'N'
			WHERE `product_id` = '" . $product_uuid . "'";
	//die($sql);
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		
		echo json_encode(array("success"=>true, "id"=>$product_uuid));
		//track now
		//trackproduct("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function updateProduct() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "product";

	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		//$fieldname = str_replace("Input", "", $fieldname);
		//$license_number = "";
		if ($fieldname=="product_name") {
			$product_name = $value;
			continue;
		}
		if ($fieldname=="product_id") {
			$product_id = $value;
			continue;
		}
		if ($fieldname=="category") {
			$category = $value;
			continue;
		}
		if ($fieldname=="supplier") {
			$supplier = $value;
			continue;
		}
		if ($fieldname=="cost") {
			$cost = $value;
			continue;
		}
		if ($fieldname=="quantity") {
			$quantity = $value;
			continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . str_replace("'", "\'", $value) . "'";
	}	
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "UPDATE `rek`.`rek_product`
			SET 
			   `product_name` = '" . $product_name . "',
			   `category` = '" . $category . "',
			   `supplier` = '" . $supplier . "',
			   `cost` = '" . $cost . "',
			   `quantity` = '" . $quantity . "'
			WHERE `product_id` = '" . $product_id . "'";
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
		//trackproduct("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
?>