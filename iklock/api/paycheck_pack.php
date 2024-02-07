<?php
$app->group('/paycheck', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->post('/save', 'saveCheck');
	$app->post('/contractors/save', 'saveContractorsChecks');
	$app->post('/delete', 'deleteCheck');
})->add(\Api\Middleware\Authorize::class);

function saveCheck() {
	$user_id = passed_var("user_id", "post");
	
	$my_user = new systemuser();
	$my_user->id = $user_id;
	$my_user->fetch();
	
	$arrFields = array();
	$arrSet = array();
	$table_name = "paycheck";
	$arrReimbursments = array();
	
	//default attribute
	$table_attribute = "main";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		if (strpos($fieldname, "Field")!==false) {
			$fieldname = str_replace("Field", "", $fieldname);
			if ($fieldname=="user_id") {
				continue;
			}
			
			//hours to minutes
			if (strpos($fieldname, "hours")!==false) {
				$fieldname = str_replace("hours", "minutes", $fieldname);
				
				$value = $value * 60;
			}
			$arrFields[] = "`" . $fieldname . "`";
			if (strpos($fieldname, "date")!==false) {
				$value = formatSomeDate($value);
			}
			$arrSet[] = "'" . addslashes($value) . "'";
			
			continue;
		}
		if (strpos($fieldname, "Amount")!==false) {
			$fieldname = str_replace("Amount", "", $fieldname);
			$arrReimbursments[$fieldname] = $value;
		}
	}
	
	$arrFields[] = "`reimbursments`";
	$arrSet[] = "'" . addslashes(json_encode($arrReimbursments)) . "'";
	
	$arrFields[] = "`user_uuid`";
	$arrSet[] = "'" . $my_user->uuid . "'";
	
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `paycheck` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 
		
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("success"=>true, "id"=>$new_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
		die($sql);
	}	
}
function saveContractorsChecks() {
	//cycle through the post, every "memo" do save
	$pay_period_start_date = passed_var("pay_period_start_date", "post");
	$pay_period_end_date = passed_var("pay_period_end_date", "post");
	
	$arrCheck = array();
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Field", "", $fieldname);
		//$blnSave = (strpos($fieldname, "memo") !== false);
		
		if (strpos($fieldname, "date")!==false) {
			$value = formatSomeDate($value);
			
			$arrFields[$fieldname] = $value;
		} else {
			$arrFieldName = explode("_", $fieldname);
			$fieldname = $arrFieldName[0];
			$user_id = $arrFieldName[1];
			$arrCheck[$user_id][$fieldname] = $value;
		}
	}
	//die(print_r($arrCheck));
	foreach ($arrCheck as $user_id=>$payments) {
		$my_user = new systemuser();
		$my_user->id = $user_id;
		$my_user->fetch();
		
		$user_uuid = $my_user->uuid;
		$regular_minutes = $payments["payment"];
		$reimbursments = addslashes(json_encode(array("reimbursment"=>$payments["reimbursment"])));
		$memo = addslashes($payments["memo"]);
		$table_uuid = uniqid("KS", false);
		
		$sql = "INSERT INTO `paycheck` (`customer_id`, `paycheck_uuid`, `user_uuid`, `pay_date`, `pay_period_start_date`, `pay_period_end_date`, `regular_minutes`, `reimbursments`, `memo`) 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $table_uuid . "', '" . $user_uuid . "', '" . $arrFields["pay_date"] . "', '" . $arrFields["pay_period_start_date"] . "', '" . $arrFields["pay_period_end_date"] . "', '" . $regular_minutes . "', '" . $reimbursments . "','" . $memo . "')";
		
		//die($sql);	
		try { 		
			DB::run($sql);
	$new_id = DB::lastInsertId();
			
			$arrNewID[] = $new_id;
		} catch(PDOException $e) {	
			echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
			die($sql);
		}
	}
	
	echo json_encode(array("success"=>true, "id"=>$arrNewID, "error"=>"")); 
}
function deleteCheck() {
	$paycheck_id = passed_var("check_id", "post");
	
	$sql = "UPDATE `paycheck` 
	SET deleted = 'Y'
	WHERE `customer_id` = :customer_id
	AND `paycheck_id` = :paycheck_id";
	
	try { 
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->bindParam("paycheck_id", $paycheck_id);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "id"=>$paycheck_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>$paycheck_id, "error"=>$e->getMessage())); 
		die($sql);
	}	
}
