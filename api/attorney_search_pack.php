<?php
$app->group('/attorney_search', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->get('', 'getAttorneySearches');
	$app->get('/{id}', 'getAttorneySearch');
})->add(Api\Middleware\Authorize::class);

function getAttorneySearches() {
    $sql = "SELECT `attorney_id`, `customer_id`, `firm_name`, `first_name`, `last_name`, `middle_initial`, `aka`, `phone`, `fax`, `email`, `active`, `default_attorney`, CONCAT(first_name, ' ', last_name, ', ', firm_name) name
	FROM `cse_attorney`";	
	$sql .= " WHERE 1
	AND deleted = 'N'
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	ORDER by firm_name";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("search_term", $search_term);
		$stmt->execute();
		$eams_carriers = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($eams_carriers));
        // Include support for JSONP requests
        echo json_encode($eams_carriers);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getAttorneySearch($id) {
	//return a row if id is valid
	$sql = "SELECT * FROM `cse_attorney`
		WHERE attorney_id=:id
		AND deleted = 'N'
		AND customer_id = " . $_SESSION['user_customer_id'] . "
		";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$eams_carrier = $stmt->fetchObject();
	
        echo json_encode($eams_carrier);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
