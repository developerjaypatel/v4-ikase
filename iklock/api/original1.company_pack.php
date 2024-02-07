<?php
$app->post('/company/general/save', authorize('user'), 'saveCompanyGeneralTaxes');
$app->post('/company/federal/save', authorize('user'), 'saveCompanyFederalTaxes');
$app->post('/company/state/save', authorize('user'), 'saveCompanyStateTaxes');
$app->post('/company/payschedule/save', authorize('user'), 'saveCompanyPayschedule');

function saveCompanyPayschedule() {
	$setup_id = passed_var("setup_id", "post");
	
	if ($setup_id != "") {
		updateCompanyPayschedule($setup_id);
		return;
	}
	
	$customer_id = $_SESSION["user_customer_id"];
	$data = json_encode($_POST);
	
	$query = "INSERT INTO `setup` (`customer_id`, `payschedule`, `general`, `federal`, `state`)
	VALUES (:customer_id, :payschedule, '', '', '')";
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("payschedule", $data);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
function updateCompanyPayschedule($setup_id) {
	$customer_id = $_SESSION["user_customer_id"];
	$data = json_encode($_POST);
	
	$query = "UPDATE `setup` 
	SET `payschedule` = :payschedule
	WHERE setup_id = :setup_id
	AND customer_id = :customer_id";
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("payschedule", $data);
		$stmt->bindParam("setup_id", $setup_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$setup_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
function saveCompanyGeneralTaxes() {
	$setup_id = passed_var("setup_id", "post");
	
	if ($setup_id != "") {
		updateCompanyGeneralTaxes($setup_id);
		return;
	}
	
	$customer_id = $_SESSION["user_customer_id"];
	$data = json_encode($_POST);
	
	$query = "INSERT INTO `setup` (`customer_id`, `payschedule`, `general`, `federal`, `state`)
	VALUES (:customer_id, '', :general, '', '')";
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("general", $data);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
function updateCompanyGeneralTaxes($setup_id) {
	$customer_id = $_SESSION["user_customer_id"];
	$data = json_encode($_POST);
	
	$query = "UPDATE `setup` 
	SET `general` = :general
	WHERE setup_id = :setup_id
	AND customer_id = :customer_id";
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("general", $data);
		$stmt->bindParam("setup_id", $setup_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$setup_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
function saveCompanyFederalTaxes() {
	$setup_id = passed_var("setup_id", "post");
	
	if ($setup_id != "") {
		updateCompanyFederalTaxes($setup_id);
		return;
	}
	
	$customer_id = $_SESSION["user_customer_id"];
	$data = json_encode($_POST);
	
	$query = "INSERT INTO `setup` (`customer_id`, `payschedule`, `general`, `federal`, `state`)
	VALUES (:customer_id, '', '', :federal, '')";
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("federal", $data);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
function updateCompanyFederalTaxes($setup_id) {
	$customer_id = $_SESSION["user_customer_id"];
	$data = json_encode($_POST);
	
	$query = "UPDATE `setup` 
	SET `federal` = :federal
	WHERE setup_id = :setup_id
	AND customer_id = :customer_id";
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("federal", $data);
		$stmt->bindParam("setup_id", $setup_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$setup_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
function saveCompanyStateTaxes() {
	$setup_id = passed_var("setup_id", "post");
	
	if ($setup_id != "") {
		updateCompanyStateTaxes($setup_id);
		return;
	}
	
	$customer_id = $_SESSION["user_customer_id"];
	$data = json_encode($_POST);
	
	$query = "INSERT INTO `setup` (`customer_id`, `payschedule`, `general`, `state`, `federal`)
	VALUES (:customer_id, '', '', :state, '')";
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("state", $data);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
function updateCompanyStateTaxes($setup_id) {
	$customer_id = $_SESSION["user_customer_id"];
	$data = json_encode($_POST);
	
	$query = "UPDATE `setup` 
	SET `state` = :state
	WHERE setup_id = :setup_id
	AND customer_id = :customer_id";
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("state", $data);
		$stmt->bindParam("setup_id", $setup_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$setup_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
?>