<?php
$app->get('/employee/notes/:user_id', authorize('user'), 'getEmployeeNotes');

$app->post('/employee/save', authorize('user'), 'saveEmployee');
$app->post('/employee/savecontact', authorize('user'), 'saveContact');
$app->post('/employee/saveemployment', authorize('user'), 'saveEmployment');
$app->post('/employee/savenote', authorize('user'), 'saveEmployeeNote');
$app->post('/employee/savetaxfederal', authorize('user'), 'saveTaxFederal');
$app->post('/employee/savetaxstate', authorize('user'), 'saveTaxState');
$app->post('/employee/savecontractor', authorize('user'), 'saveContractor');

function saveContact() {
	$user_id = passed_var("user_id", "post");
	$my_user = new systemuser();
	$my_user->fetch($user_id);
	
	$address_uuid = passed_var("address_uuid", "post");
	$my_address = new address();
	$my_address->uuid = $address_uuid;
	$my_address->fetch();
	
	//die(print_r($my_address));
	$firm_street = passed_var("streetField", "post");
	$my_address->street = $firm_street;
	$firm_city = passed_var("cityField", "post");
	$my_address->city = $firm_city;
	$firm_state = passed_var("stateField", "post");
	$my_address->state = $firm_state;
	$firm_zip = passed_var("zipField", "post");
	$my_address->zip = $firm_zip;
	$my_address->update();
	//join address to company
	joinTables("user", "address", $my_user->uuid, $my_address->uuid, "home", true);
	
	//phones
	$phone_uuid = passed_var("phone_uuid", "post");
	$my_phone = new comm();
	$phone = passed_var("phoneField", "post");
	$my_phone->uuid = $phone_uuid;
	$my_phone->fetch_empire();
	
	$my_phone->comm = $phone;
	$my_phone->comm_type = "phone";
	$my_phone->update_empire("false");

	joinTables("user", "comm", $my_user->uuid, $my_phone->uuid, "home_phone", true);
	
	$cell_phone_uuid = passed_var("cell_phone_uuid", "post");
	$my_cell_phone = new comm();
	$cell_phone = passed_var("cell_phoneField", "post");
	$my_cell_phone->uuid = $cell_phone_uuid;
	$my_cell_phone->fetch_empire();
	
	$cell_phone = passed_var("cell_phoneField", "post");
	$my_cell_phone->comm = $cell_phone;
	$my_cell_phone->comm_type = "phone";
	$my_cell_phone->update_empire("false");
	joinTables("user", "comm", $my_user->uuid, $my_cell_phone->uuid, "cell_phone", true);
	
	$provider_uuid = passed_var("provider_uuid", "post");
	$my_provider = new comm();
	$my_provider->uuid = $provider_uuid;
	$my_provider->fetch_empire();
	
	$provider = passed_var("providerField", "post");
	$my_provider->comm = $provider;
	$my_provider->comm_type = "provider";
	$my_provider->update_empire("false");
	joinTables("user", "comm", $my_user->uuid, $my_provider->uuid, "provider", true);
	
	$notification_email_uuid = passed_var("notification_email_uuid", "post");
	$my_notification_email = new comm();
	$my_notification_email->uuid = $notification_email_uuid;
	$my_notification_email->fetch_empire();
	
	$notification_email = "";
	if (isset($_POST["notification_email"])) {
		$notification_email = passed_var("notification_email", "post");
	}
	if ($notification_email!="Y") {
		$notification_email = "N";
	}
	$my_notification_email->comm = $notification_email;
	$my_notification_email->comm_type = "notification_email";
	$my_notification_email->update_empire("false");
	joinTables("user", "comm", $my_user->uuid, $my_notification_email->uuid, "notification_email", true);
	
	$notification_sms = "";
	if (isset($_POST["notification_email"])) {
		$notification_sms = passed_var("notification_sms", "post");
	}
	$notification_sms_uuid = passed_var("notification_sms_uuid", "post");
	$my_notification_sms = new comm();
	$my_notification_sms->uuid = $notification_sms_uuid;
	$my_notification_sms->fetch_empire();

	if ($notification_sms!="Y") {
		$notification_sms = "N";
	}
	$my_notification_sms->comm = $notification_sms;
	$my_notification_sms->comm_type = "notification_sms";
	$my_notification_sms->update_empire("false");
	joinTables("user", "comm", $my_user->uuid, $my_notification_sms->uuid, "notification_sms", true);
	
	$notification_personnel_uuid = passed_var("notification_personnel_uuid", "post");
	$my_notification_personnel = new comm();
	$my_notification_personnel->uuid = $notification_personnel_uuid;
	$my_notification_personnel->fetch_empire();
	
	$notification_personnel = "";
	if (isset($_POST["notification_personnel"])) {
		$notification_personnel = passed_var("notification_personnel", "post");
	}
	if ($notification_personnel!="Y") {
		$notification_personnel = "N";
	}
	$my_notification_personnel->comm = $notification_personnel;
	$my_notification_personnel->comm_type = "notification_personnel";
	$my_notification_personnel->update_empire("false");
	joinTables("user", "comm", $my_user->uuid, $my_notification_personnel->uuid, "notification_personnel", true);
	
	$emergency_phone_uuid = passed_var("emergency_phone_uuid", "post");
	$my_emergency_phone = new comm();
	$emergency_phone = passed_var("emergency_phoneField", "post");
	$my_emergency_phone->uuid = $emergency_phone_uuid;
	$my_emergency_phone->fetch_empire();
	$emergency_phone = passed_var("emergency_phoneField", "post");
	$my_emergency_phone->comm = $emergency_phone;
	$my_emergency_phone->comm_type = "phone";
	$my_emergency_phone->update_empire("false");
	joinTables("user", "comm", $my_user->uuid, $my_emergency_phone->uuid, "emergency_phone", true);
	
	//email
	$email_uuid = passed_var("email_uuid", "post");
	$my_email = new comm();
	$email = passed_var("emailField", "post");
	$my_email->uuid = $email_uuid;
	$my_email->fetch_empire();
	$email = passed_var("emailField", "post");
	$my_email->comm = $email;
	$my_email->comm_type = "email";
	$my_email->update_empire("false");
	joinTables("user", "comm", $my_user->uuid, $my_email->uuid, "email", true);
	
	$emergency_contact_uuid = passed_var("emergency_contact_uuid", "post");
	$my_person = new person();
	$emergency_contact = passed_var("emergency_contactField", "post");
	$my_person->uuid = $emergency_contact_uuid;
	$my_person->fetch_empire();
	$my_person->full_name = $emergency_contact;
	$my_person->update_empire();
	joinTables("user", "person", $my_user->uuid, $my_person->uuid, "emergency_contact", true);
	
	die(json_encode(array("success"=>true, "user_id"=>$user_id)));
}
function saveEmployee() {
	$user_id = passed_var("user_id", "post");
	if ($user_id > 0) {
		updateEmployee();
		return;
	}
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "user";
	$table_id = "";
	$pwd = "";
	
	//default attribute
	$table_attribute = "main";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Field", "", $fieldname);
		if ($fieldname=="user_id") {
			continue;
		}
		if ($fieldname=="password") {
			$password = $value;
			
			if ($password!="") {
				$pwd = encrypt($password, CRYPT_KEY);
			}
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		if (strpos($fieldname, "date")!==false || $fieldname=="dob") {
			$value = formatSomeDate($value);
		}
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	/*
	$arrFields[] = "`inine_filed`";
	$arrSet[] = "'" . addslashes($inine_filed) . "'";
	*/
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `" . $table_name . "` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ", `user_groups`, `data`) 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $table_uuid . "', " . implode(",", $arrSet) . ", '', '')";
	
	try { 
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
		die($sql);
	}	
}
function updateEmployee() {
	$request = Slim::getInstance()->request();
	$arrSet = array();
	$where_clause = "";
	$table_name = "user";
	$table_id = "";
	$table_attribute = "";
	$pwd = "";
	//$inine_filed = "N";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Field", "", $fieldname);
		
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="password") {
			$password = $value;
			if ($password!="") {
				$pwd = encrypt($password, CRYPT_KEY);
			}
			continue;
		}
		if (strpos($fieldname, "date")!==false || $fieldname=="dob") {
			$value = formatSomeDate($value);
		}
		
		if ($fieldname=="user_id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	
	if ($pwd!="") {
		$arrSet[] = "`pwd` = '" . addslashes($pwd) . "'";
	}
	//$arrSet[] = "`inine_filed` = '" . addslashes($inine_filed) . "'";
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	//die( $sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$table_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>$table_id, "error"=>$e->getMessage())); 
	}
}
function saveEmployment() {
	$user_id = passed_var("user_id", "post");
	
	$my_user = getEmployeeInfo($user_id);
	$user_type = passed_var("user_typeField", "post");
	$shift = passed_var("shiftField", "post");
	$work_location = passed_var("work_locationField", "post");
	$hired_date = formatSomeDate(passed_var("hired_dateField", "post"));
	$pay_rate = passed_var("pay_rateField", "post");
	$pay_period = passed_var("pay_periodField", "post");
	$pay_schedule = passed_var("pay_scheduleField", "post");
	$pay_method = passed_var("pay_methodField", "post");
	$inine_filed = "N";
	if (isset($_POST["inine_filedField"])) {
		$inine_filed = passed_var("inine_filedField", "post");
	}
	
	$arrDepts = array();
	if (isset($_POST["department_select"])) {
		$arrDepts = $_POST["department_select"];
	}

	$employee_number = passed_var("employee_numberField", "post");
	
	$query = "UPDATE `user` 
	SET `shift` = :shift, 
	`work_location` = :work_location,
	`employee_number` = :employee_number, 
	`user_type` = :user_type, 
	`hired_date` = :hired_date, 
	`inine_filed` = :inine_filed,  
	`pay_rate` = :pay_rate, 
	`pay_period` = :pay_period, 
	`pay_schedule` = :pay_schedule, 
	`pay_method` = :pay_method
	WHERE user_id = :user_id";	
	
	try {
		$sql = $query;
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("shift", $shift);  
		$stmt->bindParam("work_location", $work_location);  
		$stmt->bindParam("employee_number", $employee_number);  
		$stmt->bindParam("user_type", $user_type);  
		$stmt->bindParam("inine_filed", $inine_filed);  
		$stmt->bindParam("hired_date", $hired_date);  
		$stmt->bindParam("pay_rate", $pay_rate);   
		$stmt->bindParam("pay_period", $pay_period);   
		$stmt->bindParam("pay_schedule", $pay_schedule);   
		$stmt->bindParam("pay_method", $pay_method);   
		$stmt->bindParam("user_id", $user_id);  
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		//departments
		//die(print_r($arrDepts));
		foreach($arrDepts as $dindex=>$department_id) {
			$my_department = new department();
			$my_department->fetch($department_id);
			//die(print_r($my_department));
			$blnClearFirst = false;
			if ($dindex==0) {
				$blnClearFirst = true;
			}
			joinTables("user", "department", $my_user->uuid, $my_department->uuid, "main", $blnClearFirst);
		}
		
		echo json_encode(array("success"=>true, "id"=>$user_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>$user_id, "error"=>$e->getMessage())); 
	}
}
function saveEmployeeNote() {
	$user_idField = passed_var("user_id", "post");
	$notesField = passed_var("notesField", "post");
	$assign_to = $_SESSION["user_nickname"];
	$time_stampField = date("Y-m-d H:i:s");
	$statusField = "";	//for now
	$customer_id = $_SESSION["user_customer_id"];
	//insert into new location
	$query="INSERT INTO employee_notes (user_id,notes,time_stamp,user_name,status, customer_id) 
	VALUES (:user_id, :notes,:time_stamp, :assign_to, :status, :customer_id)";
	
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("user_id", $user_idField);
		$stmt->bindParam("notes", $notesField);
		$stmt->bindParam("time_stamp", $time_stampField);
		$stmt->bindParam("assign_to", $assign_to);
		$stmt->bindParam("status", $statusField);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$notes_idField = $db->lastInsertId();
		$stmt = null; $db = null;
		
		$query="INSERT INTO employee_notes_track (user_name_track, operation_track, time_stamp_track,user_id,notes_id,
		 notes,time_stamp,user_name, status, customer_id) 
		VALUES (:user_name_track,'insert', :time_stamp_track, :user_id, :notes_id, 
		:notes,:time_stamp, :assign_to, :status, :customer_id)";
		
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("user_name_track", $_SESSION["user_logon"]);
		$stmt->bindParam("time_stamp_track", $time_stampField);
		
		$stmt->bindParam("user_id", $user_idField);
		$stmt->bindParam("notes_id", $notes_idField);
		$stmt->bindParam("notes", $notesField);
		$stmt->bindParam("time_stamp", $time_stampField);
		$stmt->bindParam("assign_to", $assign_to);
		$stmt->bindParam("status", $statusField);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$notes_idField, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
function getEmployeeInfo($user_id) {
	$my_user = new systemuser();
	$my_user->id = $user_id;
	$my_user->fetch();
	
	return $my_user;
}
function saveTaxFederal() {
	$user_id = passed_var("user_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	$employee = getEmployeeInfo($user_id);
	$data = $employee->data;
	$tax_state_info = "";
	$contractor_info = "";
	if ($data!="") {
		$arrData = json_decode($data);
		if (isset($arrData->tax_state_info)) {
			$tax_state_info = $arrData->tax_state_info;
		}
		if (isset($arrData->contractor_info)) {
			$contractor_info = $arrData->contractor_info;
		}
	}
	$arrData = array("tax_state_info"=>$tax_state_info, "contractor_info"=>$contractor_info, "tax_federal_info"=>$_POST);
	$data = json_encode($arrData);
	
	//insert into new location
	$query="UPDATE `user` 
	SET `data` = :data
	WHERE user_id = :user_id
	AND customer_id = :customer_id";
	
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("data", $data);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$user_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}

function saveTaxState() {
	$user_id = passed_var("user_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	$employee = getEmployeeInfo($user_id);
	$data = $employee->data;
	$tax_federal_info = "";
	$contractor_info = "";
	if ($data!="") {
		$arrData = json_decode($data);
		if (isset($arrData->tax_federal_info)) {
			$tax_federal_info = $arrData->tax_federal_info;
		}
		if (isset($arrData->contractor_info)) {
			$contractor_info = $arrData->contractor_info;
		}
	}
	$arrData = array("tax_federal_info"=>$tax_federal_info, "contractor_info"=>$contractor_info, "tax_state_info"=>$_POST);
	$data = json_encode($arrData);
	
	//insert into new location
	$query="UPDATE `user` 
	SET `data` = :data
	WHERE user_id = :user_id
	AND customer_id = :customer_id";
	
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("data", $data);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$user_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
function saveContractor() {
	$user_id = passed_var("user_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	$employee = getEmployeeInfo($user_id);
	$data = $employee->data;
	$tax_state_info = "";
	$tax_federal_info = "";
	if ($data!="") {
		$arrData = json_decode($data);
		if (isset($arrData->tax_state_info)) {
			$tax_state_info = $arrData->tax_state_info;
		}
		if (isset($arrData->tax_federal_info)) {
			$tax_federal_info = $arrData->tax_federal_info;
		}
	}
	$arrData = array("tax_state_info"=>$tax_state_info, "tax_federal_info"=>$tax_federal_info, "contractor_info"=>$_POST);
	$data = json_encode($arrData);
	
	//insert into new location
	$query="UPDATE `user` 
	SET `data` = :data
	WHERE user_id = :user_id
	AND customer_id = :customer_id";
	
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("data", $data);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$user_id, "error"=>"")); 
	} catch(PDOException $e) {	
		echo json_encode(array("success"=>false, "id"=>-1, "error"=>$e->getMessage())); 
	}
}
function getEmployeeNotes($user_id) {
	$my_notes = new notes();
	$notes = $my_notes->fetch_employee_notes($user_id);
	
	echo json_encode($notes);
}
?>