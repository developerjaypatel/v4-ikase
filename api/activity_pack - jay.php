<?php
$app->get('/activity/:id', authorize('user'), 'getActivity');
$app->get('/activity/kases/:case_id', authorize('user'), 'getKaseActivities');
$app->get('/activity/file_access/:case_id', authorize('user'), 'getKaseActivitiesNoFile');
$app->get('/activity/invoices/:case_id', authorize('user'), 'getKaseInvoices');
$app->get('/activity/billing/:case_id', authorize('user'), 'getKaseBilling');

$app->get('/activity/invoiceitem/:invoice_id', authorize('user'), 'getKaseInvoiceItems');
$app->get('/activity/invoiceitemfull/:invoice_id', authorize('user'), 'getKaseInvoiceItemsFull');
$app->get('/activity/kases/:case_id/:invoice_id', authorize('user'), 'getKaseInvoice');

$app->get('/lastactivity', authorize('user'), 'lastActivity');
$app->get('/singleactivity/:case_id/:activity_id', authorize('user'), 'singleActivity');
$app->get('/activity/billing/:case_id/:kinvoice_id', authorize('user'), 'getActivityBilling');
$app->get('/activity/report/:user_id/:start_date/:end_date', authorize('user'), 'getReportActivities');
$app->get('/activity/summary/:start_date/:end_date', authorize('user'), 'getReportSummary');
$app->get('/activity/archive/:case_id', authorize('user'), 'getKaseArchivedActivities');
$app->get('/activity/archivecount/:case_id', authorize('user'), 'getKaseCountArchivedActivities');
$app->get('/activity/stacks/:start_date/:end_date', authorize('user'), 'stackActivity');
$app->get('/activity/billto/:case_id', authorize('user'), 'billTo');

$app->get('/activities/demographics', authorize('user'), 'getActivityDemographics');
$app->get('/activity/refvocational/:case_id', authorize('user'), 'hasVocationReferral');

$app->post('/activity/track', authorize('user'), 'trackActivity');
$app->post('/activity/update_hours', authorize('user'), 'updateHours');
$app->post('/activity/update_bulkhours', authorize('user'), 'updateBulkHours');
$app->post('/activity/update_by', authorize('user'), 'updateBy');

$app->post('/activity/insert_invoiceactivity', authorize('user'), 'insertInvoiceActivity');
$app->post('/activity/insert_kinvoiceactivity', authorize('user'), 'insertKInvoiceActivity');
$app->post('/activity/update_kinvoiceactivity', authorize('user'), 'updateKInvoiceActivity');

$app->post('/activity/update_activity', authorize('user'), 'updateActivity');
$app->post('/activity/insert_activity', authorize('user'), 'insertActivity');
$app->post('/activity/delete', authorize('user'), 'deleteActivity');

$app->post('/invoice/delete', authorize('user'), 'deleteInvoice');

$app->get('/rate/:id', authorize('user'), 'getRate');
$app->get('/ratebytype/:case_type', authorize('user'), 'getRateByCaseType');
$app->get('/rates', authorize('user'), 'getRates');
$app->post('/rate/save', authorize('user'), 'saveRate');

function saveRate() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$rate_id = passed_var("rate_id", "post");
	/*
	$case_id = passed_var("case_id", "post");
	$kase = getCaseInfo($case_id);
	$case_uuid = $kase->uuid;
	*/
	$fee_name = passed_var("fee", "post");
	$fee_minutes = passed_var("minutes", "post");
	$deleted = passed_var("deleted", "post");
	
	if ($rate_id < 0) {
		$rate_description = passed_var("rate_description", "post");
		$rate_name = passed_var("rate_name", "post");
		$case_type = passed_var("case_type", "post");
		$rate_info = json_encode(array(array("fee_name"=>$fee_name, "fee_minutes"=>$fee_minutes, "deleted"=>"N")));
		
		$rate_uuid = uniqid("RT", false);
		
		$sql = "INSERT INTO cse_rate (`rate_uuid`, `case_type`, `rate_description`, `rate_name`, `rate_info`, `customer_id`)
		VALUES (:rate_uuid, :case_type, :rate_description, :rate_name, :rate_info, :customer_id);";
		
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("rate_uuid", $rate_uuid);
			$stmt->bindParam("rate_description", $rate_description);
			$stmt->bindParam("rate_name", $rate_name);
			$stmt->bindParam("rate_info", $rate_info);
			$stmt->bindParam("case_type", $case_type);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			
			$rate_id = $db->lastInsertId();
			$stmt = null; $db = null;       
			
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
				echo json_encode($error);
		}
		
		echo json_encode(array("success"=>true, "rate_id"=>$rate_id, "operation"=>"insert"));
	} else {
		$rate = getRateInfo($rate_id);
		//die(print_r($rate));
		$original_info = $rate->rate_info;
		$arrInfo = json_decode($original_info);
		$blnFound = false;
		foreach($arrInfo as $iindex=>$info) {
			if ($info->fee_name==$fee_name) {
				$arrInfo[$iindex]->fee_minutes = $fee_minutes;
				$arrInfo[$iindex]->deleted = $deleted;
				$blnFound = true;
				break;
			}
		}
		if (!$blnFound) {
			$arrInfo[] = array("fee_name"=>$fee_name, "fee_minutes"=>$fee_minutes, "deleted"=>$deleted);
		}
		$rate_info = json_encode($arrInfo);
		
		//now update
		$sql = "UPDATE cse_rate
		SET rate_info = :rate_info
		WHERE rate_id = :rate_id
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		
		//$stmt->bindParam("rate_name", $rate_name);
		$stmt->bindParam("rate_info", $rate_info);
		//$stmt->bindParam("rate_description", $rate_description);
		$stmt->bindParam("rate_id", $rate_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$stmt = null; $db = null;   
		
		echo json_encode(array("success"=>true, "rate_id"=>$rate_id, "operation"=>"update", "rate_info"=>$rate_info));
	}
}
function getRateInfo($rate_id) {
	return getRate($rate_id, true);
}
function getRateByCaseType($case_type) {
	return getRate("", false, $case_type);
}
function getRate($rate_id, $blnReturn = false, $case_type = "") {
	session_write_close();
	
	if ($case_type=="all") {
		$case_type = "";
	}
	
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT rat.*, rat.rate_id id , rat.rate_uuid uuid 
	FROM cse_rate rat
	WHERE 1 ";
	if ($rate_id != "") {
		$sql .= "
		AND rat.rate_id = :rate_id";
	}
	if ($case_type != "") {
		$sql .= "
		AND rat.case_type = :case_type";
	}
	$sql .= "
	AND customer_id = :customer_id";
	if ($case_type != "") {
		//get the last one
		$sql .= "
		ORDER BY rate_id DESC
		LIMIT 0, 1";
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($rate_id != "") {
			$stmt->bindParam("rate_id", $rate_id);
		}
		if ($case_type != "") {
			$stmt->bindParam("case_type", $case_type);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$rate = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;       

		if ($blnReturn) {
			return $rate;
		} else {
			echo json_encode($rate);
		}
		
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	
}
function getRates() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT rat.*, rat.rate_id id 
	FROM cse_rate rat
	WHERE 1
	AND customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$rates = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;       

		echo json_encode($rates);
		
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function billTo($case_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT DISTINCT partie.corporation_id, partie.type corporation_type, partie.corporation_uuid, partie.company_name, partie.preferred_name, partie.full_address, cpt.partie_type, partie.phone partie_phone, partie.fax partie_fax, partie.full_name partie_full_name, partie.company_site partie_company_site, partie.email partie_email, cpt.employee_title partie_employee_title, partie.employee_phone partie_employee_phone,
		partie.employee_email partie_employee_email,
		partie.employee_fax partie_employee_fax, ccase.adj_number, cdoc.adhoc_value `doctor_type`, ccase.injury_type
	FROM cse_case ccase
	INNER JOIN `cse_case_corporation` ccorp
	ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute != 'employer' AND ccorp.deleted = 'N')
	INNER JOIN `cse_corporation` partie
	ON ccorp.corporation_uuid = partie.corporation_uuid
	INNER JOIN `cse_partie_type` cpt
	ON partie.type = cpt.blurb
	LEFT OUTER JOIN `cse_corporation_adhoc` cdoc
	ON (partie.corporation_uuid = cdoc.corporation_uuid AND cdoc.`deleted` =  'N' AND cdoc.adhoc = 'doctor_type')
	WHERE ccase.case_id = '" . $case_id . "'
	AND partie.deleted = 'N'
	AND ccase.customer_id = " . $customer_id . "
	ORDER BY cpt.sort_order, partie.company_name ";
	//echo $query_noemp;
	//die($query_noemp);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$billtos = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;       
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	
	$arrPartieInfo = array();
	//for ($int=0;$int<$numbs_noemp;$int++) {
	foreach($billtos as $billto) {
		$partie_id = $billto->corporation_id;
		$partie_uuid = $billto->corporation_uuid;
		$partie_type = $billto->partie_type;
		$doctor_type = $billto->doctor_type;
		$partie_name = $billto->company_name;
		$partie_preferred_name = $billto->preferred_name;
		$partie_address = $billto->full_address;
		$partie_phone = $billto->partie_phone;
		$partie_fax = $billto->partie_fax;
		$partie_full_name = $billto->partie_full_name;
		$partie_company_site = $billto->partie_company_site;
		$partie_email = $billto->partie_email;
		$injury_type = $billto->injury_type;
		
		
		//every thing same below
		if ($injury_type != "") {
			$arrBillingTarget = explode('|', $injury_type);
		}
		
		//
		if ($partie_type != ucfirst(strtolower($arrBillingTarget[1]))) {
			continue;
		}
		
		$arrPartieComm = array();
		if ($partie_name!=""){
			$arrPartieComm[] = $partie_name;
		}
		if ($partie_full_name!=""){
			$arrPartieComm[] = "ATTN: " . $partie_full_name;
		}
		if ($partie_address!=""){
			$arrPartieComm[] = $partie_address;
		}
		
		if ($partie_email!=""){
			$arrPartieComm[] = "<a href='mailto:" . $partie_email . "'>" . $partie_email . "</a>";
		}
		break;
		//die(print_r($arrPartieComm));
	}
	die(implode("<br />", $arrPartieComm));
}
function singleActivity($case_id, $activity_id) {
	session_write_close();
	
	$sql = "SELECT `ca`.`activity_id`, `ca`.`activity_uuid`,  
	`activity_category`,  `ca`.`billing_rate`,  `ca`.`billing_date`,  `ca`.`billing_amount`,  `ca`.`billing_unit`,
	`ca`.`activity`, `ca`.`activity_status`, `ca`.`activity_date`, `ca`.`hours`, 
	IF (`ca`.`activity_user_id`=0, `ca`.`timekeeper`, `ca`.`activity_user_id`) `activity_user_id`, `ca`.`customer_id`, 
	`ca`.`activity_id` `id`, `ca`.`activity_uuid` `uuid`, '' `name`,
	`cse_case`.case_id, `cse_case`.case_number, IFNULL(user.nickname, '') `by`, IFNULL(user.user_name, '') `user_name`
	
	FROM  `cse_activity` `ca`
	
	INNER JOIN  `cse_case_activity` `cca`
	ON  `ca`.`activity_uuid` = `cca`.`activity_uuid`
	
	INNER JOIN `cse_case` 
	ON  (
		`cca`.`case_uuid` = `cse_case`.`case_uuid`
		AND `cse_case`.`case_id` = :case_id
	)
	
	LEFT OUTER JOIN `ikase`.`cse_user` user
	ON ca.activity_user_id = user.user_id
	
	WHERE `ca`.`deleted` = 'N'
	AND `ca`.`activity_id` = :activity_id
	AND `ca`.customer_id = :customer_id
	ORDER BY `ca`.activity_date DESC";
	try {
		$customer_id = $_SESSION['user_customer_id'];
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("activity_id", $activity_id);
		$stmt->bindParam("customer_id", $customer_id);
		// echo json_encode($case_id);
		// echo json_encode($activity_id);
		// echo json_encode($customer_id);
		// die($sql);

		$stmt->execute();
		$single_activity = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
         echo json_encode($single_activity);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function insertInvoiceActivity() {
	session_write_close();
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$arrIds = array();
	$table_name = "activity";
	$table_id = "";
	$info = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		//$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="id") {
			$arrIds = explode(",", str_replace(" ", "", $value));
			continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}		
	
	//actual query
	$invoice_uuid = uniqid("RD", false);
	
	if (!isset($dbname)) {
		$dbname = "";
	}
	if ($dbname=="ikase") {
		$sql = "INSERT INTO cse_invoice (`invoice_uuid`, `invoice_date`, `invoice_items`, `customer_id`, `invoice_items`, `active_users`)
		VALUES ('" . $invoice_uuid . "', '" . date("Y-m-d H:i:s") . "', '', " . $_SESSION['user_customer_id'] . ", '', '')";
	} else {
		$sql = "INSERT INTO cse_invoice (`invoice_uuid`, `invoice_date`, `invoice_items`, `customer_id`)
		VALUES ('" . $invoice_uuid . "', '" . date("Y-m-d H:i:s") . "', '', " . $_SESSION['user_customer_id'] . ")";
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$invoice_id = $db->lastInsertId();
		$stmt = null; $db = null;	
			
		//if we passed a valid case
		$last_updated_date = date("Y-m-d H:i:s");
		$attribute = "activity";
		foreach ($arrIds as $activity_uuid) {
			$invoice_activity_uuid = uniqid("KA", false);
			$sql = "INSERT INTO cse_invoice_activity (`invoice_activity_uuid`, `invoice_uuid`, `activity_uuid`, `attribute`,`last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $invoice_activity_uuid . "', '" . $invoice_uuid . "', '" . $activity_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			//echo $sql . "\r\n";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;	
		}
		
		echo json_encode(array("success"=>true, "id"=>$invoice_id));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function insertKInvoiceActivity() {
	session_write_close();
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$arrIds = array();
	$table_name = "activity";
	$table_id = "";
	$info = "";
	$customer_id = $_SESSION["user_customer_id"];
	$user_id = $_SESSION["user_id"];
	$case_id = passed_var("case_id", "post");
	$ids = passed_var("ids", "post");
	$carrier_id = passed_var("carrier_id", "post");
	$start_date = passed_var("start_date", "post");
	$end_date = passed_var("end_date", "post");
	$kinvoice_type = passed_var("kinvoice_type", "post");
	$kinvoice_number = passed_var("kinvoice_number", "post");
	$transfer_funds = passed_var("transfer_funds", "post");
	
	
	$kase = getKaseInfo($case_id);
	$case_uuid = $kase->uuid;
	$arrIds = explode(",", str_replace(" ", "", $ids));
	//look for the activities
	$search_ids = "'" . implode("','", $arrIds) . "'";
	$right_now = date("Y-m-d H:i:s");
	
	$sql = "SELECT act.*, 
	usr.user_uuid, usr.user_name, usr.nickname, IFNULL(usr.rate, 0) rate
	FROM cse_activity act
	INNER JOIN ikase.cse_user usr
	ON act.activity_user_id = usr.user_id
	WHERE activity_uuid IN (" . $search_ids . ")
	AND usr.customer_id = :customer_id";
	//echo $sql . "\r\n";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$activities = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;    
		//die(print_r($activities));
		
		//costs
		$kinvoice_uuid = uniqid("LI", false);
		$invoice_total = 0;
		$unit = "";
		
		$arrInvoiceItems = array();
		$arrInvoiceRates = array();
		$arrInvoiceMinutes = array();
		$arrInvoiceAmounts = array();
		$arrInvoiceActualAmounts = array();
		$arrInvoiceItemDesc = array();
		$arrInvoiceUnits = array();
		$arrInvoiceDates = array();
		
		$invoice_minutes = 0;
		$arrItemValues = array();
		foreach($activities as $act) {
			if ($act->billing_amount == 0 && $act->hours == 0) {
				continue;
			}
			$activity_uuid = $act->activity_uuid;
			$act_user_id = $act->activity_user_id;
			$act_user_uuid = $act->user_uuid;
			$act_user_name = $act->user_name;
			$act_hours = $act->hours;
			if ($act_hours > 0) {
				$act_rate = $act->rate;
				$act_date = $act->activity_date;
				$amount = $act->hours * $act->rate;
				$minutes = $act_hours * 60;
				$exact = 'N';
				$unit = "Hour";	
				
				$arrInvoiceMinutes[] = $act_hours . " hrs";
				$invoice_minutes += floatval($minutes);
			
			} else {
				$act_rate = $act->billing_rate;
				if ($act_rate=="") {
					$act_rate = 0;
				}
				$act_date = $act->billing_date;
				if ($act_date=="") {
					$act_date = $act->activity_date;
				}
				$unit = $act->billing_unit;
				$amount = $act->billing_amount * $act_rate;
				//$amount = $act->billing_amount;
				$minutes = $act->billing_amount;	//minutes is vestigial, storing qty
				$exact = 'N';
				$suffix = "";
				if ($amount > 1) {
					$suffix = "s";
				}
				$arrInvoiceMinutes[] = $minutes . " " . $unit . $suffix;
			}
			
			$rate_description = "Rate :$" . number_format($act_rate, 2) . " per " . $unit;
			$item_name = $act->activity;
			$item_name = strip_tags($item_name);
			
			if (strlen($item_name) > 1055) {
				$item_name = substr($item_name, 0, 1055);
			}
			$description = "";
			$kinvoiceitem_uuid = uniqid("II", false);
			
			$values = "('" . $kinvoiceitem_uuid . "', '" . $kinvoice_uuid . "', '" . $activity_uuid . "', '" . addslashes($item_name) . "', '" . $description . "', '" . $minutes . "', '" . $act_rate . "', '" . $amount . "', '" . $unit . "', '" . $exact . "', '" . $customer_id . "')";
			
			$arrInvoiceItems[] = $item_name;
			$arrInvoiceRates[] = array("rate"=>$rate_description, "employee"=>"By:" . $act_user_name);
			$arrInvoiceItemDesc[] = $description;
			$arrItemValues[] = $values;
			$arrInvoiceAmounts[] = "$" . number_format(floatval($amount), 2);
			$arrInvoiceActualAmounts[] = $amount;
			$arrInvoiceDates[] = $act_date;	
			$invoice_total += $amount;
		}
		
		//die(print_r($arrItemValues));
		//get parent info			
		$template_name = "Activity Bill";
		$sql = "SELECT kin.*, doc.* 
		FROM cse_document doc
		INNER JOIN cse_document_kinvoice cdk
		ON doc.document_uuid = cdk.document_uuid
		INNER JOIN cse_kinvoice kin
		ON cdk.kinvoice_uuid = kin.kinvoice_uuid
		WHERE doc.document_name = :document_name
		AND doc.customer_id = :customer_id
		AND doc.`type` = 'template'
		AND doc.deleted = 'N'";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("document_name", $template_name);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$template_parent = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null; 
		//die(print_r($template_parent));
		$parent_kinvoice_uuid = $template_parent->kinvoice_uuid;
		$kinvoice_date = date("Y-m-d H:i:s");
		if ($kinvoice_number=="") {
			//generate kinvoice_number, this is an insert
			$invoice_counter = getKaseKinvoiceNextCounter($case_id); 
			$kinvoice_number = $case_id . "-" . $invoice_counter;
		} else {
			$arrKInvoiceNumb = explode("-", $kinvoice_number);
			$invoice_counter = $arrKInvoiceNumb[1];
		}
		$template_val = "N";
		$fund_transfer = "P";
		if ($transfer_funds=="Y") {
			//confirmed
			$fund_transfer = "C";
		}
		$sql = "INSERT INTO cse_kinvoice (`kinvoice_uuid`, `parent_kinvoice_uuid`, `kinvoice_date`, `kinvoice_type`, `fund_transfer`, `kinvoice_number`, `invoice_counter`, 
		`hourly_rate`, `total`, `customer_id`, `template`, `template_name`)
		VALUES (:kinvoice_uuid, :parent_kinvoice_uuid, :kinvoice_date, :kinvoice_type, :fund_transfer, :kinvoice_number, :invoice_counter, 
		:hourly_rate, :total, :customer_id, :template, :template_name)";
		
		$hourly_rate = -1;
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
		$stmt->bindParam("parent_kinvoice_uuid", $parent_kinvoice_uuid);
		$stmt->bindParam("kinvoice_type", $kinvoice_type);
		$stmt->bindParam("kinvoice_number", $kinvoice_number);
		$stmt->bindParam("fund_transfer", $fund_transfer);
		$stmt->bindParam("kinvoice_date", $kinvoice_date);
		$stmt->bindParam("invoice_counter", $invoice_counter);
		$stmt->bindParam("hourly_rate", $hourly_rate);
		$stmt->bindParam("total", $invoice_total);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("template",$template_val);
		$stmt->bindParam("template_name", $template_name);
		$stmt->execute();
		
		$kinvoice_id = $db->lastInsertId();
		
		$stmt = null; $db = null;
		
		//attach to case
		$sql = "INSERT INTO `cse_case_kinvoice`
		(`case_kinvoice_uuid`, `case_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES
		(:case_kinvoice_uuid, :case_uuid, :kinvoice_uuid, 'bill', :right_now, :user_uuid, :customer_id)";
		$case_kinvoice_uuid = uniqid("KI", false);
				
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->bindParam("case_kinvoice_uuid", $case_kinvoice_uuid);
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
		$stmt->bindParam("right_now", $right_now);
		$stmt->bindParam("user_uuid", $user_id);	//we can use the user_id field to show who the invoice was assigned to
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		//attach invoice to invoiced
		$sql = "INSERT INTO `cse_corporation_kinvoice`
(`corporation_kinvoice_uuid`, `corporation_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
VALUES
(:corporation_kinvoice_uuid, :corporation_uuid, :kinvoice_uuid, :attribute, :right_now, :user_uuid, :customer_id)";
		
		$carrier = getCorporationInfo($carrier_id);
		$invoiced_corporation_uuid = $carrier->uuid;
		$attribute = "carrier";
		$corporation_kinvoice_uuid = uniqid("CK", false);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->bindParam("corporation_kinvoice_uuid", $corporation_kinvoice_uuid);
		$stmt->bindParam("corporation_uuid", $invoiced_corporation_uuid);
		$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
		$stmt->bindParam("attribute", $attribute);
		$stmt->bindParam("right_now", $right_now);
		$stmt->bindParam("user_uuid", $user_id);	//we can use the user_uuid field to show who the invoice was assigned to
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		//items
		$sql_insert = "INSERT INTO `cse_kinvoiceitem` (kinvoiceitem_uuid, kinvoice_uuid, activity_uuid, item_name, item_description, minutes, rate, amount, unit, exact, customer_id)";
		foreach($arrItemValues as $values) {
			$sql = $sql_insert . "
			VALUES " . $values;
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$stmt = null; $db = null;	
		}
		
		//get the account id
		$account = getBankAccount("", $case_id, "trust", true);
		$account_id = $account->id;
		
		//attach the invoice to the account
		if ($account_id!="") {
			//attach invoice to invoiced
			$sql = "INSERT INTO `cse_account_kinvoice`
			(`account_kinvoice_uuid`, `account_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES
			(:account_kinvoice_uuid, :account_uuid, :kinvoice_uuid, :attribute, :right_now, :user_uuid, :customer_id)";
			
			$account_kinvoice_uuid = uniqid("CK", false);
			$account_uuid = $account->uuid;
			$attribute = $account->account_type;
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("account_kinvoice_uuid", $account_kinvoice_uuid);
			$stmt->bindParam("account_uuid", $account_uuid);
			$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
			$stmt->bindParam("attribute", $attribute);
			$stmt->bindParam("right_now", $right_now);
			$stmt->bindParam("user_uuid", $user_id);	//we can use the user_uuid field to show who the invoice was assigned to
			$stmt->bindParam("customer_id", $customer_id);
			
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		//do we transfer funds
		if ($transfer_funds=="Y" && $account_id!="") {
			//update the account balance
			$sql = "UPDATE cse_kinvoice
			SET payments = total,
			paid_date = kinvoice_date
			WHERE kinvoice_id = :kinvoice_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("kinvoice_id", $kinvoice_id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$stmt = null; $db = null;
			
			//update the account balance
			$sql = "UPDATE cse_account
			SET account_balance = (account_balance - " . $invoice_total . ")
			WHERE account_id = :account_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("account_id", $account_id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$stmt = null; $db = null;
			
			trackAccount("transfer", $account_id);
		}
		
		trackKInvoice("insert", $kinvoice_id);
		
		$kinvoice_document_id = "";
		
		if ($kinvoice_type=="P") {
			$kinvoice_number .= "\\n";
			$kinvoice_number .= "DRAFT";
		}
		$destination = createKInvoiceDocument("create", $kase, $case_id, $case_uuid, $kinvoice_uuid, $kinvoice_date, $kinvoice_number, $kinvoice_document_id, $invoice_total, $arrInvoiceItems, $arrInvoiceRates, $arrInvoiceMinutes, $arrInvoiceAmounts, $arrInvoiceDates, $carrier, $template_parent, $start_date, $end_date, $user_id, $customer_id);
		
		echo json_encode(array("success"=>true, "kinvoice_id"=>$kinvoice_id, "account_id"=>$account_id, "invoice_number"=>$kinvoice_number, "invoice_total"=>$invoice_total, "destination"=>$destination));
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		die();
	}
	
	die();
	/*
	//actual query
	$invoice_uuid = uniqid("RD", false);
	
	if (!isset($dbname)) {
		$dbname = "";
	}
	if ($dbname=="ikase") {
		$sql = "INSERT INTO cse_invoice (`invoice_uuid`, `invoice_date`, `invoice_items`, `customer_id`, `invoice_items`, `active_users`)
		VALUES ('" . $invoice_uuid . "', '" . date("Y-m-d H:i:s") . "', '', " . $_SESSION['user_customer_id'] . ", '', '')";
	} else {
		$sql = "INSERT INTO cse_invoice (`invoice_uuid`, `invoice_date`, `invoice_items`, `customer_id`)
		VALUES ('" . $invoice_uuid . "', '" . date("Y-m-d H:i:s") . "', '', " . $_SESSION['user_customer_id'] . ")";
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$invoice_id = $db->lastInsertId();
		$stmt = null; $db = null;	
			
		//if we passed a valid case
		$last_updated_date = date("Y-m-d H:i:s");
		$attribute = "activity";
		foreach ($arrIds as $activity_uuid) {
			$invoice_activity_uuid = uniqid("KA", false);
			$sql = "INSERT INTO cse_invoice_activity (`invoice_activity_uuid`, `invoice_uuid`, `activity_uuid`, `attribute`,`last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $invoice_activity_uuid . "', '" . $invoice_uuid . "', '" . $activity_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			//echo $sql . "\r\n";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;	
		}
		
		echo json_encode(array("success"=>true, "id"=>$invoice_id));
		
		
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	*/
}
function updateKInvoiceActivity() {
	$kinvoice_id = passed_var("kinvoice_id", "post");
	
	deleteKInvoice($kinvoice_id);
	
	//now insert a new one
	insertKInvoiceActivity();
}
function createKInvoiceDocument($operation = "create", $kase, $case_id, $case_uuid, $kinvoice_uuid, $kinvoice_date, $kinvoice_number, $kinvoice_document_id, $invoice_total, $arrInvoiceItems, $arrInvoiceRates, $arrInvoiceMinutes, $arrInvoiceAmounts, $arrInvoiceDates, $carrier, $template_parent, $start_date, $end_date, $user_id, $customer_id) {
	$arrReplace = array();
		
	require_once '../phpdocx_pro/classes/CreateDocx.inc';
	
	//now we want to get the adj numbers
	$sql = "SELECT inj.adj_number, inj.start_date, inj.end_date, 
	cin.alternate_policy_number claim_number 
	FROM cse_injury inj
	
	INNER JOIN cse_injury_injury_number ccin
	ON inj.injury_uuid = ccin.injury_uuid
	
	INNER JOIN cse_injury_number cin
	ON ccin.injury_number_uuid = cin.injury_number_uuid 
	
	INNER JOIN cse_case_injury cci
	ON inj.injury_uuid = cci.injury_uuid
	
	WHERE cci.case_uuid = :case_uuid
	ORDER BY inj.start_date";
	
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->execute();
		$adj_injuries = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$arrADJs = array();
		foreach($adj_injuries as $adj_injury) {
			$arrADJs[] = $adj_injury->adj_number;
			$arrDOIDates[] = array("start_date"=>$adj_injury->start_date, "end_date"=>$adj_injury->end_date);
			$arrClaims[] = $adj_injury->claim_number;
		}
		$blnClaimsFilled = true;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
		die();
	}
	
	$arrReplace['ALLCASENUMBER'] = implode("; ", $arrADJs);
	$arrReplace['CASENUMBER'] = implode("; ", $arrADJs);
	
	$arrReplace['CASENAME'] = str_replace("&", "&amp;", $kase->name);
	
	$sql_claims = "SELECT alternate_policy_number claim_number
	FROM cse_case ccase
	INNER JOIN cse_case_injury cci
	ON ccase.case_uuid = cci.case_uuid AND cci.deleted = 'N'
	INNER JOIN cse_injury ci
	ON cci.injury_uuid = ci.injury_uuid AND ci.deleted = 'N'
	INNER JOIN cse_injury_injury_number ccin
	ON ci.injury_uuid = ccin.injury_uuid AND ccin.deleted = 'N'
	INNER JOIN cse_injury_number cin
	ON ccin.injury_number_uuid = cin.injury_number_uuid AND cin.deleted = 'N'
	WHERE 1
	AND ccase.case_id = " . $case_id . "
	AND ccase.customer_id = '" . $customer_id . "'";

	$sql_claims .= "
	UNION
	SELECT DISTINCT adhoc_value claim_number
	FROM cse_corporation_adhoc cadhoc
	INNER JOIN cse_corporation corp
	ON cadhoc.corporation_uuid = corp.corporation_uuid AND corp.deleted = 'N'
	INNER JOIN cse_case_corporation ccorp
	ON corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.deleted = 'N'
	INNER JOIN cse_case ccase
	ON ccorp.case_uuid = ccase.case_uuid AND ccase.deleted = 'N'
	INNER JOIN cse_case_injury cci
	ON ccase.case_uuid = cci.case_uuid AND cci.deleted = 'N'
	INNER JOIN cse_injury ci
	ON cci.injury_uuid = ci.injury_uuid
	WHERE cadhoc.adhoc = 'claim_number'
	AND cadhoc.deleted = 'N'
	AND ccase.case_id = '" . $case_id . "'
	AND ccase.customer_id = '" . $customer_id . "'";
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql_claims);
		$stmt->execute();
		$claim_numbers = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		$arrClaimNumbers = array();
		foreach($claim_numbers as $the_claim) {
			if ($the_claim->claim_number!="") {
				if (!in_array($the_claim->claim_number, $arrClaimNumbers)) {
					$arrClaimNumbers[] = $the_claim->claim_number;
				}
			}
		}
		array_unique($arrClaimNumbers);
		$all_claims = implode("; ", $arrClaimNumbers);
		$all_claims = trim($all_claims);
		if (substr($all_claims, 0, 1)==";") {
			$all_claims = substr($all_claims, 1);
			$all_claims = trim($all_claims);
		}
		$arrReplace['ALLCLAIMNO'] = $all_claims;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
		die();
	}
	
	$injuries = getInjuriesInfo($case_id);
	$arrInjuries = array();
	foreach($injuries as $list_injury) {
		if ($list_injury->end_date!="0000-00-00") {
			$list_injury->start_date = date("m/d/Y", strtotime($list_injury->start_date)) . " - " . date("m/d/Y", strtotime($list_injury->end_date)) . " CT";
		} else {
			$list_injury->start_date = date("m/d/Y", strtotime($list_injury->start_date));
		}
		$arrInjuries[] = $list_injury->start_date;
	}
	
	$arrReplace['ALLINJURYDATES'] = implode("\r\n", $arrInjuries);
	$arrReplace['ALLINJURYDATESINLINE'] = implode(", ", $arrInjuries);
	
	$arrReplace['INVDATE'] = date("m/d/Y", strtotime($kinvoice_date));
	$arrReplace['INVNUMB'] = $kinvoice_number;
	$arrReplace['INVTOTAL'] = "$" . number_format($invoice_total, 2);
	$fileno = $kase->case_number;
	if ($fileno=="") {
		$fileno = $kase->file_number;
	}
	$arrReplace['FILENO'] = $fileno;
	$arrReplace['INVSTART'] = $start_date;
	$arrReplace['INVEND'] = $end_date;
	
	$carrier_salutation = "Sir/Madam";
	if (trim($carrier->full_name)!="") {
		$carrier_salutation = $carrier->full_name;
		if ($carrier->salutation!="") {
			$carrier_salutation = 	$carrier->salutation . " " . $carrier_salutation;
		}	
	}

	//create the actual document
	$arrReplace['OPPCSALUT1'] = $carrier_salutation;
	if ($carrier->full_name!="") {
		$arrReplace['OPPCNAME1'] = $carrier->full_name . "\\n";
	} else {
		$arrReplace['OPPCNAME1'] = "";
	}
	$arrReplace['OPPCFIRM1'] = $carrier->company_name;
	$arrReplace['OPPCSELECT'] = $carrier->company_name;
	$arrReplace['OPPCADD11'] = $carrier->street;
	$arrReplace['OPPCADD12'] = $carrier->suite;
	$arrReplace['OPPCADD21'] = $carrier->suite;
	$arrReplace['OPPCCITYSTATEZIP1'] = $carrier->city . ", " . $carrier->state . " " . $carrier->zip;
	$arrReplace['OPPCCITY'] = $carrier->city;
	$arrReplace['OPPCSTATE'] =$carrier->state;
	$arrReplace['OPPCZIP'] = $carrier->zip;
	$arrReplace['OPPCPHONE1'] = $carrier->phone;
	$arrReplace['OPPCTEL1'] = $carrier->phone;
	$arrReplace['OPPCFAX1'] = $carrier->fax;
	
	$variables = $arrReplace;
	$prefix = "/templates";
	
	$destination = $template_parent->document_filename;
	$destination = str_replace("templates/", "", $destination);
	$destination = str_replace(".docx", "", $destination);
	$destination .= "_" . $carrier_id . "_" . date("YmdHis");
	$destination_folder = '../uploads/' . $customer_id . '/invoices/';
	$destination_folder_path = 'C:\\inetpub\\wwwroot\\iKase.org\\uploads\\' . $customer_id . '\\invoices\\';
	if (!is_dir($destination_folder)) {
		mkdir($destination_folder, 0755, true);
	}
	$destination_path = $destination_folder_path . $destination;
	
	$destination = $destination_folder . $destination;
	
	$final_destination = $destination;
	
	$customer = getCustomerInfo();
	$customer_full_name = $customer->cus_name_first;
	if ($customer->cus_name_middle!="") {
		$customer_full_name .= " " . $customer->cus_name_middle;
	}
	$customer_full_name .= " " . $customer->cus_name_last;
	$arrReplace['FIRMATTY'] = $customer_full_name;
	$arrReplace['FIRMNAME'] = str_replace("&", "&amp;", $_SESSION['user_customer_name']);
	$arrReplace['FIRMNUMBER'] = $customer->eams_no;
	$arrReplace['UAN'] = $customer->cus_uan;
	$arrReplace['TAXID'] = $customer->cus_fedtax_id;
	
	$arrReplace['FIRMADD1'] = $customer->cus_street;
	$arrReplace['FIRMADD2'] = "";
	$arrReplace['FIRMATTYFNAME'] = $customer->cus_name_first;
	$arrReplace['FIRMATTYLNAME'] = $customer->cus_name_last;
	$arrReplace['FIRMATTYMIDDLEINITIAL'] = $customer->cus_name_middle;
	$arrReplace['FIRMCITY'] = $customer->cus_city;
	$arrReplace['FIRMSTATE'] = $customer->cus_state;
	$arrReplace['FIRMZIP'] = $customer->cus_zip;
	$arrReplace['FIRMTEL'] = $customer->cus_phone;
	$arrReplace['FIRMEMAIL'] = $customer->cus_email;
	$arrReplace['BARNUMBER'] = $customer->cus_barnumber;
	$arrReplace['BARNO'] = $customer->cus_barnumber;
	$arrReplace['FIRMFAX'] = $customer->cus_fax; 
	$arrReplace['ADDTELFIRMNAME'] = $customer->cus_street . ", " . $customer->cus_city . ", " . $customer->cus_state . " " . $customer->cus_zip . ", " . $customer->cus_phone;
	$arrReplace['CUSCOUNTY'] = $customer->cus_county;
	
	$arrReplace['DATE'] = date('F j, Y');
	//if spanish
	if (strpos(strtolower($template_name), "spanish")!==false) {
		$month = getSpanishMonth(date('F'));
		
		$arrReplace['DATE'] = $month . " " . date('j, Y');
	}
	
	$arrReplace['LETTERMONTH'] = date("F");
	$arrReplace['LETTERDAY'] = date("j");
	$arrReplace['LETTERYEAR'] = date("Y");
	
	$arrReplace['LETTER'] = $letter;
	$arrReplace['SIGNATURE'] = $_SESSION['user_name'];
	
	$arrReplace['letterhead'] = "";
	
	foreach($arrReplace as $replace_index=>$replace) {
		if (strpos($replace, "&amp;")===false && strpos($replace, "&")!==false) {
			$replace = str_replace("&", "&amp;", $replace);
			//die($replace_index . " = " . $replace);
			$arrReplace[$replace_index] = $replace;
		}
	}
	
	//get letterhead
	$sql_letterhead = "SELECT `setting_value` `value`
	FROM  `cse_setting` 
	WHERE `cse_setting`.customer_id = :customer_id
	AND `cse_setting`.setting = 'letterhead'
	AND `cse_setting`.deleted = 'N'
	AND `cse_setting`.`setting_value` != ''
	ORDER BY setting_id DESC";
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql_letterhead);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$letterhead = $stmt->fetchObject();

		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if ($template->source!="no_letterhead" && $template->source!="clientname_letterhead") {
			if(!is_object($letterhead)) {
				die(json_encode(array("error"=>"no letterhead")));
			}
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
	//die(print_r($arrReplace));
	$variables = $arrReplace;
	//die('../uploads/' . $customer_id . $prefix . '/' . $template_parent->document_filename);
	$docx = new CreateDocxFromTemplate('../uploads/' . $customer_id . $prefix . '/' . $template_parent->document_filename);
	
	if ($template_parent->source!="no_letterhead" && $template_parent->source!="clientname_letterhead") {
		$docx ->importHeadersAndFooters('../uploads/' . $customer_id . "/" . $letterhead->value);
	}		
	$options = array('parseLineBreaks' =>true);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$pre_time = round(($finish_time - $header_start_time), 4);
	
	$docx->replaceVariableByText($variables, $options);
	
	$data = array();
	foreach($arrInvoiceItems as $kindex=>$item) {
		$rate = $arrInvoiceRates[$kindex]["rate"];
		$employee = $arrInvoiceRates[$kindex]["employee"];
		$minutes = $arrInvoiceMinutes[$kindex];
		$amount = $arrInvoiceAmounts[$kindex];
		$act_date = $arrInvoiceDates[$kindex];
		
		$data[] = array(
			'INVITEM'		=>	$item,
			'INVRATE'		=>	$rate,
			'INVEMP'		=>	$employee,
			'INVDATE'		=>	$act_date,
			'INVQTY'		=>	$minutes,
			'INVAMNT'		=>	$amount,
			'INVSPACER'		=>	' '
		);
	}
	
	$docx->replaceTableVariable($data);

	$docx->createDocx($destination); 
	
	//$cmd = "PowerShell.exe -ExecutionPolicy Bypass -File c:\\bat\\topdf.ps1 '" . $destination . ".docx'";
	//passthru($cmd);
	
	$cmd = "PowerShell.exe -ExecutionPolicy Bypass -File c:\\bat\\topdf.ps1 '" . $destination_path . ".docx'";
	$ps_file = 'C:\\inetpub\\wwwroot\\iKase.org\\uploads\invoices\\pdf_' . $_SESSION["user_plain_id"] . '.ps1';
	if (file_exists($ps_file)) {
		unlink($ps_file);
	}
	$fp = fopen($ps_file, 'w');
	fwrite($fp, $cmd);
	fclose($fp);

	$document_uuid = uniqid("KS");

	$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
	try {
		$db = getConnection();
		
		$document_filename = $destination;
		$document_date = date("Y-m-d H:i:s");
		$document_extension = "docx";
		$description = "";
		//$subject = 'Invoice <a title="Click to edit invoice" class="edit_invoice_full" id="editinvoice_' . $kinvoice_id . '" style="cursor:pointer;">' . $kinvoice_number . '</a>';
		$subject = "";
		
		$description_html = "Activity Billed Items";
		$type = "invoice";
		
		
		$verified = "Y";
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("document_uuid", $document_uuid);
		$stmt->bindParam("parent_document_uuid", $template_parent->document_uuid);
		$stmt->bindParam("document_name", $subject);
		$stmt->bindParam("document_date", $document_date);
		$stmt->bindParam("document_filename", $document_filename);
		$stmt->bindParam("document_extension", $document_extension);
		$stmt->bindParam("description", $description);
		$stmt->bindParam("description_html", $description_html);
		$stmt->bindParam("type", $type);
		$stmt->bindParam("verified", $verified);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		$stmt = null; $db = null;
		
		trackDocument("insert", $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}

	//attach to case
	$cd_uuid = uniqid("JK");
	$attribute = "invoice";
	$sql = "INSERT INTO `cse_case_document`
	( `case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
	VALUES ('" . $cd_uuid . "','" . $case_uuid . "','" . $document_uuid . "', '" . $attribute . "', '" . $document_date . "','" . $user_id . "', '" . $customer_id . "')";
	//die($sql);
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		//if invoice
		if ($kinvoice_document_id!="") {
			$sql = "UPDATE cse_document
			SET deleted = 'Y'
			WHERE document_id = :document_id
			AND customer_id = :customer_id";
			//echo $sql . "\r\n";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("document_id", $kinvoice_document_id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$stmt = null; $db = null;
			
			trackDocument("delete", $kinvoice_document_id, "");
		}
		$document_kinvoice_uuid = uniqid("KI", false);
			
		//attach to document
		$sql = "INSERT INTO `cse_document_kinvoice`
(`document_kinvoice_uuid`, `document_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
VALUES
(:document_kinvoice_uuid, :document_uuid, :kinvoice_uuid, 'main', :right_now, :user_uuid, :customer_id)";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
		$stmt->bindParam("document_uuid", $document_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("document_kinvoice_uuid", $document_kinvoice_uuid);
		$stmt->bindParam("right_now", $document_date);
		$stmt->bindParam("user_uuid", $user_id);
		$stmt->execute();
		$stmt = null; $db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		die();
	}
	
	recordActivity($operation, "Invoice [<a href='" . $destination . ".docx' target='_blank' class='white_text'>" . $kinvoice_number . "</a>] generated by " . $_SESSION['user_name'], $case_uuid, 0, "Invoices", 0);
	
	return $destination;
}
function getKaseCountArchivedActivities($case_id) {
	session_write_close();
	//first get the cpointer
	$data_source = $_SESSION["user_data_source"];
	$customer_id = $_SESSION["user_customer_id"];
	$kase = getKaseInfo($case_id);
	
	//then request data from kustomweb.xyz
	$url = "http://kustomweb.xyz/a1_archive/activity_count.php";
	$fields = array("params"=>$kase->cpointer . "|" . $_SESSION["user_data_source"]);
	
	$fields_string = "";
	
	foreach($fields as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
	
	//echo $url . "?" . $fields_string . "\r\n";
	//open connection
	$ch = curl_init();
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, count($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	
	$activity_count = curl_exec($ch);
	
	if ($activity_count < 0) {
		//if it's negative, it means the import was done but not yet transferred
		$success = transferActivity($data_source, $customer_id, $kase);
	}
	//if we return a negative number, 
	echo json_encode(array("count"=>$activity_count));
}
function getKaseArchivedActivities($case_id) {
	session_write_close();
	//first get the cpointer
	$kase = getKaseInfo($case_id);
	
	//then request data from kustomweb.xyz
	$url = "http://kustomweb.xyz/a1_archive/activity.php";
	$fields = array("params"=>$kase->cpointer . "|" . $_SESSION["user_data_source"]);
	
	$fields_string = "";
	
	foreach($fields as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
	
	//die($url . "?" . $fields_string);
	//open connection
	$ch = curl_init();
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, count($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	
	$result = curl_exec($ch);

	//die($url);
	//$main_folder = file_get_contents($url);
	$main_folder = $result;
	
	$data_source = $_SESSION["user_data_source"];
	$customer_id = $_SESSION["user_customer_id"];
	
	
	//how many records
	$url = "http://kustomweb.xyz/a1_archive/activity_count.php?params=12000|11661|glauber3&";
	$fields = array("params"=>$main_folder . "|" . $kase->cpointer . "|" . $data_source);
	
	$fields_string = "";
	
	foreach($fields as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
	
	//die($url . "?" . $fields_string);
	//open connection
	$ch = curl_init();
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, count($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	
	$activity_count = curl_exec($ch);
	
	//now request for the first extraction step to proceed
	//$url = "http://kustomweb.xyz/a1_archive/extract_activity.php?params=" . $main_folder . "|" . $kase->cpointer . "|" . $data_source;
	$url = "http://kustomweb.xyz/a1_archive/extract_activity.php";
	$fields = array("params"=>$main_folder . "|" . $kase->cpointer . "|" . $data_source);
	
	$fields_string = "";
	
	foreach($fields as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
	
	//die($url . "?" . $fields_string);
	//open connection
	$ch = curl_init();
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, count($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	
	$result = curl_exec($ch);
	$sub_complete = $result;
	
	//actually extract
	$url = "http://kustomweb.xyz/a1_archive/extract_activity_events.php";
	$fields = array("params"=>$main_folder . "|" . $kase->cpointer . "|" . $data_source);
	
	$fields_string = "";
	
	foreach($fields as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
	
	//open connection
	$ch = curl_init();
	//die($url . "?" . $fields_string);
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, count($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	
	$result = curl_exec($ch);
	
	$complete = $result;
	
	$success = transferActivity($data_source, $customer_id, $kase);
	die($success);
	//die(json_encode(array("success"=>true, "sub_complete"=>$sub_complete, "complete"=>$complete)));	
}
function transferActivity($data_source, $customer_id, $kase) {
	try {
		$db = getConnection();
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_activity` 
		(`case_activity_uuid`, `case_uuid`,  `activity_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $kase->uuid . "', '" . $kase->uuid . "', 
		CONCAT(`CASENO`, '_', `ACTNO`, '_', @curRow := @curRow + 1) AS `activity_uuid`, 
		'main', 
		`DATE` activitydate, 
		'system', 'N', " . $customer_id . "
		FROM `" . $data_source . "`.`caseact`
		JOIN    (SELECT @curRow := 0) r
		WHERE `CASENO` = '" . $kase->cpointer . "'
		AND `EVENT` != ''
		ORDER BY ACTNO ASC";
		
		//echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "
		INSERT INTO `" . $data_source . "`.`" . $data_source . "_activity` 
		(`activity_uuid`, `activity`, `activity_category`, `activity_date`, `flag`, `hours`, `timekeeper`, `initials`, `attorney`, `activity_user_id`, `customer_id`)
		SELECT CONCAT(`CASENO`, '_', `ACTNO`, '_', @curRow := @curRow + 1) AS `activity_uuid`, 
		`EVENT` activity, IFNULL(actdeflt.ACTNAME, '') `activity_category`,
		`DATE` activitydate, IF(`REDALERT`=1, 'red', '') flag,
		(`MINUTES` / 60) `hours`, 
		`INITIALS0` `timekeeper`, `INITIALS` `initials`, `ATTY` `attorney`, 0, '" . $customer_id . "' 
		FROM `" . $data_source . "`.`caseact`
		LEFT OUTER JOIN `" . $data_source . "`.`actdeflt`
		ON caseact.CATEGORY = actdeflt.CATEGORY
		JOIN    (SELECT @curRow := 0) r
		WHERE 1
		AND `CASENO` = '" . $kase->cpointer . "'
		AND `EVENT` != ''
		ORDER BY ACTNO ASC";
		
		//echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_activity` (`activity_uuid`, `activity`, `activity_category`, `activity_date`, `hours`, `timekeeper`, `activity_user_id`, `customer_id`, `deleted`)
		SELECT ca.`activity_uuid`, `activity`, IF(`flag`='red', 'REDFLAG', `activity_category`) `activity_category`, `activity_date`, `hours`, `timekeeper`, `activity_user_id`, ca.`customer_id`, ca.`deleted`
		FROM `" . $data_source . "`.`" . $data_source . "_activity` ca
		INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_activity` cca
		ON ca.activity_uuid = cca.activity_uuid
		WHERE 1 AND cca.case_uuid = '" . $kase->uuid . "' AND ca.customer_id = " . $customer_id . "
		ORDER BY ca.activity_id";
		//echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_activity`
		(`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `last_updated_date`, 
		`last_update_user`, `deleted`, `customer_id`)
		SELECT `case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `last_updated_date`, 
		`last_update_user`, `deleted`, `customer_id` 
		FROM `" . $data_source . "`.`" . $data_source . "_case_activity` 
		WHERE 1 AND case_uuid = '" . $kase->uuid . "' AND customer_id = " . $customer_id;
		
		//echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "INSERT INTO ikase_" . $data_source . ".cse_notes 
		(`notes_uuid`, `note`, `type`, `dateandtime`, `entered_by`, `customer_id`)
		SELECT activity_uuid, activity, 'quick', activity_date, timekeeper, `customer_id`
		FROM ikase_" . $data_source . ".cse_activity ca
		INNER JOIN (
		SELECT cca.case_uuid, MIN(activity_id) activity_id FROM ikase_" . $data_source . ".cse_activity ca
		INNER JOIN ikase_" . $data_source . ".cse_case_activity cca
		ON ca.activity_uuid = cca.activity_uuid AND cca.deleted = 'N'
		WHERE activity != 'Applicant Entered Into Computer'
		AND ca.activity_user_id = 0 AND ca.activity_uuid LIKE '" . $kase->cpointer . "%'
		AND activity NOT LIKE 'File Accessed%'
		AND cca.case_uuid = '" . $kase->uuid . "'
		GROUP BY cca.case_uuid) min_act
		ON ca.activity_id = min_act.activity_id";
		
		//echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$last_updated_date = date("Y-m-d H:i:s");
		$last_update_user = "system";
		
		$sql = "INSERT INTO ikase_" . $data_source . ".cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		SELECT activity_uuid, case_uuid, activity_uuid, 'quick', '" . $last_updated_date . "', '" . $last_update_user . "', `customer_id`
		FROM ikase_" . $data_source . ".cse_activity ca
		INNER JOIN (
		SELECT cca.case_uuid, MIN(activity_id) activity_id FROM ikase_" . $data_source . ".cse_activity ca
		INNER JOIN ikase_" . $data_source . ".cse_case_activity cca
		ON ca.activity_uuid = cca.activity_uuid AND cca.deleted = 'N'
		WHERE activity != 'Applicant Entered Into Computer'
		AND activity NOT LIKE 'File Accessed%'
		AND ca.activity_user_id = 0 AND ca.activity_uuid LIKE '" . $kase->cpointer . "%'
		AND cca.case_uuid = '" . $kase->uuid . "'
		GROUP BY cca.case_uuid) min_act
		ON ca.activity_id = min_act.activity_id";
		
		//echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "INSERT INTO ikase_" . $data_source . ".cse_notes (`notes_uuid`, `note`, `type`, `dateandtime`, `entered_by`, `customer_id`)
		SELECT ca.activity_uuid, ca.activity, 'redflag', activity_date, timekeeper, ca.`customer_id`
		FROM ikase_" . $data_source . ".cse_activity ca
		INNER JOIN ikase_" . $data_source . ".cse_case_activity cca
		ON ca.activity_uuid = cca.activity_uuid AND cca.deleted = 'N'
		WHERE `activity_category` = 'REDFLAG' 
		AND cca.case_uuid = '" . $kase->uuid . "'";
		
		//echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "INSERT INTO ikase_" . $data_source . ".cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		SELECT ca.activity_uuid, cca.case_uuid, ca.activity_uuid, ca.`activity_category`, '" . $last_updated_date . "', '" . $last_update_user . "', ca.`customer_id`
		FROM ikase_" . $data_source . ".cse_activity ca
		INNER JOIN ikase_" . $data_source . ".cse_case_activity cca
		ON ca.activity_uuid = cca.activity_uuid AND cca.deleted = 'N'
		WHERE ca.`activity_category` = 'REDFLAG'
		AND cca.case_uuid = '" . $kase->uuid . "'";
		
		//echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
	
		$stmt = null; $db = null;
		
		$success = array("success"=>true);
		return json_encode($success);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function getKaseInvoice($case_id, $invoice_id) {
	$_SESSION["invoice_id"] = $invoice_id;
	$_SESSION["method"] = "get";
	getKaseActivities($case_id);
}
function getKaseBilling($case_id) {
	//$_SESSION["billing_only"] = "Y";
	//$_SESSION["billing_only"] = "N";
	getKaseActivities($case_id, "", "Y");
}
function getKaseActivitiesNoFile($case_id) {
	//$_SESSION["file_access"] = "N";
	getKaseActivities($case_id, "N", "");
}
function getKaseActivities($case_id, $file_access = "N", $kinvoice_id = "N") {
	$join = "LEFT OUTER";
	/*
	$invoice_id = "";
	if (isset($_SESSION["invoice_id"])) {
		//if ($_SESSION["method"]=="get") {
			$join = "INNER";	
		//}
		$invoice_id = $_SESSION["invoice_id"];	
		
		//remove from session so that next query does not have default
		unset($_SESSION["invoice_id"]);
		unset($_SESSION["method"]);
	}
	$blnBillingOnly = false;
	if (isset($_SESSION["billing_only"])) {
		$blnBillingOnly = true;
		unset($_SESSION["billing_only"]);
	}
	*/
	$blnNoFileAccess = false;
	if (isset($_SESSION["file_access"])) {
		if ($file_access=="N") {
			$blnNoFileAccess = true;
		}
		unset($_SESSION["file_access"]);
	}
	
	session_write_close();
	
	$sql = "SELECT DISTINCT `cse_activity`.`activity_id`, `cse_activity`.`activity_uuid`, 
			IFNULL(ck.kinvoiceitem_id, '') kinvoiceitem_id, IFNULL(ck.kinvoice_id, '') kinvoice_id, IFNULL(ck.kinvoice_date, '') kinvoice_date,
			IFNULL(ck.kinvoice_number, '') kinvoice_number,
			`activity_category`,  
			`cse_activity`.`activity`, `cse_activity`.`activity_date`, `cse_activity`.`hours`, 
			`cse_activity`.`billing_date`, `cse_activity`.`billing_rate`, `cse_activity`.`billing_amount`, `cse_activity`.`billing_unit`,  
			IF (`cse_activity`.`activity_user_id`=0, `cse_activity`.`timekeeper`, `cse_activity`.`activity_user_id`) `activity_user_id`, `cse_activity`.`customer_id`, 
			`cse_activity`.`activity_id` `id`, `cse_activity`.`activity_uuid` `uuid`, '' `name`,
			`cse_case`.case_id, `cse_case`.case_number, 
			IFNULL(user.nickname, `cse_activity`.`timekeeper`) `by`,
			IFNULL(user.rate, 0) `rate`,
			IFNULL(ck.kinvoice_id, '') invoice_id, IFNULL(ck.kinvoice_number, '') invoice_number,
            IF(fee_tracks.operation IS NULL, '', 
            CONCAT(fee_tracks.fee_date, '|', fee_tracks.fee_billed, '|', fee_tracks.paid_fee, '|', fee_tracks.fee_memo, '|', fee_tracks.fee_check_number))	fee_summary
			
			FROM  `cse_activity` 
			
			INNER JOIN  `cse_case_activity` 
			ON  `cse_activity`.`activity_uuid` = `cse_case_activity`.`activity_uuid` AND `cse_case_activity`.deleted = 'N'
			
			INNER JOIN `cse_case` ON  (`cse_case_activity`.`case_uuid` = `cse_case`.`case_uuid`
			AND `cse_case`.`case_id` = '" . $case_id . "')
			
			LEFT OUTER JOIN `ikase`.`cse_user` `user`
			ON cse_activity.activity_user_id = user.user_id
			
			LEFT OUTER JOIN (
				SELECT ca.activity_id, tfee.operation, tfee.fee_date, 
				IF(tfee.fee_billed = 0, fee.fee_billed, tfee.fee_billed) fee_billed,
				tfee.paid_fee, tfee.fee_memo, tfee.fee_check_number 
				FROM cse_activity ca
				INNER JOIN cse_case_activity cca
				ON ca.activity_uuid = cca.activity_uuid
				INNER JOIN cse_case ccase
				ON cca.case_uuid = ccase.case_uuid

				INNER JOIN cse_fee_track tfee
				ON cca.case_track_id = tfee.fee_track_id
                
                INNER JOIN cse_fee fee
				ON tfee.fee_id = fee.fee_id

				WHERE ccase.case_id = '" . $case_id . "'
            ) fee_tracks
            ON  `cse_activity`.`activity_id` = `fee_tracks`.`activity_id`
			LEFT OUTER JOIN (
				SELECT kinvoiceitem_id, activity_uuid, kinvoice_id, kinvoice_number, kinvoice_date
				FROM cse_kinvoiceitem ckitem
				INNER JOIN cse_kinvoice kin
				ON ckitem.kinvoice_uuid = kin.kinvoice_uuid
				INNER JOIN cse_case_kinvoice cck
				ON kin.kinvoice_uuid = cck.kinvoice_uuid
				INNER JOIN cse_case ccase
				ON cck.case_uuid = ccase.case_uuid
				WHERE kin.deleted = 'N'
				AND ccase.case_id = '" . $case_id . "'";
		if ($kinvoice_id!="N" && $kinvoice_id!="") {
				$sql .= "
				AND kin.kinvoice_id = '" . $kinvoice_id . "'";
			}
		$sql .= "
			) ck
			ON  `cse_activity`.`activity_uuid` = `ck`.`activity_uuid`";
		if ($kinvoice_id!="N" && $kinvoice_id!="") {
			$sql .= "
			LEFT OUTER JOIN (
				SELECT kinvoiceitem_id, activity_uuid, kinvoice_id, kinvoice_number, kinvoice_date
				FROM cse_kinvoiceitem ckitem
				INNER JOIN cse_kinvoice kin
				ON ckitem.kinvoice_uuid = kin.kinvoice_uuid
				INNER JOIN cse_case_kinvoice cck
				ON kin.kinvoice_uuid = cck.kinvoice_uuid
				INNER JOIN cse_case ccase
				ON cck.case_uuid = ccase.case_uuid
				WHERE kin.deleted = 'N'
				AND ccase.case_id = '" . $case_id . "'";
		
				$sql .= "
				AND kin.kinvoice_id != '" . $kinvoice_id . "'
			) ck2
			ON  `cse_activity`.`activity_uuid` = `ck2`.`activity_uuid`";
		}
		$sql .= " WHERE `cse_activity`.`deleted` = 'N'";
		if ($kinvoice_id!="N" && $kinvoice_id!="") {
			$sql .= "
			AND (hours > 0 OR billing_amount > 0)
			AND `ck2`.`activity_uuid` IS NULL";
		}
	
	//file access permission
	$blnFileAccess = true;
	if (isset($_SESSION['user_job'])) {
		if ($_SESSION['user_role']!="admin" || $_SESSION['user_role']!="masteradmin") {	
			if($_SESSION['user_job'] != "Administrator") {
				$sql .= " 
				AND `activity_category` != 'File Accessed'";
				$blnFileAccess = false;
			}
		} else {
			$sql .= " 
			AND `activity_category` != 'File Accessed'";
			$blnFileAccess = false;
		}
	} else {
		//default
		$sql .= " 
		AND `activity_category` != 'File Accessed'";
		$blnFileAccess = false;
	}
	$sql .= " 
	AND `cse_activity`.customer_id = '" . $_SESSION['user_customer_id'] . "'";
	
	if ($blnNoFileAccess) {
		$blnFileAccess = false;
		
		if (strpos($sql, " AND `activity_category` != 'File Accessed'")===false) {
			$sql .= " 
			AND `activity_category` != 'File Accessed'";
		}
	}
	
	if ($blnFileAccess) {
		$sql .= " UNION
		SELECT (case_track_id * 100000) activity_id, case_track_id activity_uuid, 
		'' kinvoiceitem_id, '' kinvoice_id, '' kinvoice_date,
		'' kinvoice_number,
		'File Accessed' `activity_category`, 
		CONCAT('File Accessed: ',  `nickname`) `activity`, `time_stamp` `activity_date`, '0.00' `hours`, 
		'' `billing_date`, '' `billing_rate`, 0 `billing_amount`, '' `billing_unit`,  
		'' `activity_user_id`, cct.customer_id, 
		(case_track_id * 100000) id, case_track_id uuid, '' `name`,
		case_id, case_number, '' `by`,
			IFNULL(usr.rate, 0) `rate`,
		'' invoice_id, '' invoice_number, '' fee_summary
		
		FROM cse_case_track cct
		
		INNER JOIN ikase.cse_user usr
		ON cct.user_uuid = usr.user_uuid
		
		WHERE case_id = '" . $case_id . "'
		AND operation = 'view'
		AND cct.customer_id = '" . $_SESSION['user_customer_id'] . "'";
		$sql .= " 
		ORDER BY  activity_date DESC, `activity_id` DESC ";
		//
	} else {
		$sql .= " 
		ORDER BY  `cse_activity`.`activity_date` DESC, `cse_activity`.`activity_id` DESC ";
	}
	if ($_SERVER['REMOTE_ADDR']='47.153.49.248') {
		//die($sql);
	}
	$_SESSION["last_activity_sql"] = $sql;
	session_write_close();
		
	try {
		// die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$invoice_activities = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        echo json_encode($invoice_activities);        
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerInvoices() {
	$sql = "SELECT ci.*, ci.invoice_id id, CONCAT(ccase.case_id, '-', ci.invoice_id) case_invoice_id
			FROM  `cse_invoice` ci
			INNER JOIN `cse_invoice_activity` cia 
			ON `ci`.`invoice_uuid` = `cia`.`invoice_uuid`
			INNER JOIN cse_case_activity cca
			ON cia.activity_uuid = cca.activity_uuid AND `cca`.deleted = 'N'
			INNER JOIN cse_case ccase
			ON cca.case_uuid = ccase.case_uuid
			WHERE 1
			AND `ci`.customer_id = '" . $_SESSION['user_customer_id'] . "'";
			$sql .= " ORDER BY `ci`.invoice_date DESC ";
			
	session_write_close();
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$activity_invoices = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
         echo json_encode($activity_invoices);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInvoices($case_id) {
	if (!is_numeric($case_id)) {
		die();
	}
	/*
	$sql = "SELECT *
			FROM  `cse_invoice` 
			WHERE `cse_invoice`.`deleted` = 'N'
			AND `cse_invoice`.customer_id = '" . $_SESSION['user_customer_id'] . "'";
			$sql .= " ORDER BY `cse_invoice`.invoice_date DESC ";
	*/		
	$sql = "SELECT DISTINCT ci.*, ci.invoice_id id, CONCAT(ccase.case_id, '-', ci.invoice_id) case_invoice_id
			FROM  `cse_invoice` ci
			INNER JOIN `cse_invoice_activity` cia 
			ON `ci`.`invoice_uuid` = `cia`.`invoice_uuid`
			INNER JOIN cse_case_activity cca
			ON cia.activity_uuid = cca.activity_uuid AND `cca`.deleted = 'N'
			INNER JOIN cse_case ccase
			ON cca.case_uuid = ccase.case_uuid
			WHERE ccase.case_id = '" . $case_id . "'
			AND ci.deleted = 'N'
			AND `ci`.customer_id = '" . $_SESSION['user_customer_id'] . "'";
			$sql .= " ORDER BY `ci`.invoice_date DESC ";
			
	session_write_close();
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$activity_invoices = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
         echo json_encode($activity_invoices);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInvoiceItems($invoice_id) {
	if (!is_numeric($invoice_id)) {
		die();
	}
	$sql = "SELECT DISTINCT ca.*, ci.*, ccase.*, user.*, ca.activity_id id, ca.activity_uuid uuid, IFNULL(user.nickname, '') `by`, IFNULL(user.user_name, '') `user_name`,
	 `ci`.`invoice_uuid` invoice_uuid, `cia`.`invoice_uuid` cia_invoice_uuid
			FROM  `cse_activity` ca
			INNER JOIN `cse_invoice_activity` cia
			ON `ca`.`activity_uuid` = `cia`.`activity_uuid`
			INNER JOIN `cse_invoice` ci
			ON `cia`.`invoice_uuid` = `ci`.`invoice_uuid`
			INNER JOIN cse_case_activity cca
			ON cia.activity_uuid = cca.activity_uuid AND `cca`.deleted = 'N'
			INNER JOIN cse_case ccase
			ON cca.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `ikase`.`cse_user` user
			ON ca.activity_user_id = user.user_id
			WHERE `ci`.`invoice_id` = '" . $invoice_id . "'
			AND `ci`.customer_id = '" . $_SESSION['user_customer_id'] . "'";
            $sql .= " ORDER BY `ci`.invoice_date DESC ";
			
	//die($sql); 
	//$_SESSION["last_activity_sql"] = $sql;
	session_write_close();
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$invoice_activity = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
         echo json_encode($invoice_activity);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInvoiceItemsFull($invoice_id) {
	if (!is_numeric($invoice_id)) {
		die();
	}
	$sql = "SELECT DISTINCT ca.*, ci.*, ccase.*, user.*, ca.activity_id id, ca.activity_uuid uuid, IFNULL(user.nickname, '') `by`, IFNULL(user.user_name, '') `user_name`, 
			IFNULL(`cia`.`invoice_uuid`, '') cia_invoice_uuid
			FROM  `cse_activity` ca
			LEFT OUTER JOIN `cse_invoice_activity` cia
			ON `ca`.`activity_uuid` = `cia`.`activity_uuid`
			LEFT OUTER JOIN `cse_invoice` ci
			ON `cia`.`invoice_uuid` = `ci`.`invoice_uuid`
			INNER JOIN cse_case_activity cca
			ON cia.activity_uuid = cca.activity_uuid AND `cca`.deleted = 'N'
			INNER JOIN cse_case ccase
			ON cca.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `ikase`.`cse_user` user
			ON ca.activity_user_id = user.user_id
			WHERE `ci`.`invoice_id` = :invoice_id
			AND `ci`.customer_id = :customer_id";
            $sql .= " ORDER BY `ci`.invoice_date DESC ";
			
	//die($sql); 
	//$_SESSION["last_activity_sql"] = $sql;
	session_write_close();
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("invoice_id", $invoice_id);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$invoice_activity = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
         echo json_encode($invoice_activity);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getReportSummary($start_date, $end_date){
	$sql = "SELECT cu.user_id, cu.user_name, COUNT(ca.activity_id) activity_count
	FROM cse_activity ca
	INNER JOIN ikase.cse_user cu
	ON ca.activity_user_id = cu.user_id
	WHERE 1 
	AND `ca`.activity_date BETWEEN '" . $start_date . " 00:00:00' AND '" . $end_date . " 23:59:59'
	GROUP BY cu.user_id";
	
	$_SESSION["last_activity_sql"] = $sql;
	session_write_close();
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$activity = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
         echo json_encode($activity);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getReportActivities($user_id, $start_date, $end_date) {
	$sql = "SELECT DISTINCT IFNULL(user.nickname, '') `by`, IFNULL(user.user_name, '') `user_name`, `cse_activity`.`activity_id`, `cse_activity`.`activity_uuid`,  
	`activity_category`,  
	`cse_activity`.`activity`, `cse_activity`.`activity_date`, 
	IF (`cse_activity`.`activity_user_id`=0, `cse_activity`.`timekeeper`, `cse_activity`.`activity_user_id`) `activity_user_id`, `cse_activity`.`customer_id`, 
	`cse_activity`.`activity_id` `id`, `cse_activity`.`activity_uuid` `uuid`,
	`cse_case`.case_id, `cse_case`.case_name,
	CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, `cse_case`.case_number,
	IFNULL(`user`.`rate`,0) rate, IFNULL(`user`.`tax`,0) user_tax, `hours`
			FROM  `cse_activity` 
			LEFT OUTER JOIN ikase.cse_user `user`
            ON `cse_activity`.activity_user_id = user.user_id
			INNER JOIN  `cse_case_activity` 
			ON  `cse_activity`.`activity_uuid` = `cse_case_activity`.`activity_uuid` AND `cse_case_activity`.deleted = 'N'
			INNER JOIN `cse_case` ON  (`cse_case_activity`.`case_uuid` = `cse_case`.`case_uuid` AND `cse_case`.`deleted` = 'N')
			LEFT OUTER JOIN cse_case_person ccapp ON cse_case.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN ";		
			if (($_SESSION['user_customer_id']==1033)) { 
				$sql .= "(" . SQL_PERSONX . ")";
			} else {
				$sql .= "cse_person";
			}
			$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (cse_case.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			WHERE `cse_activity`.`deleted` = 'N'
			/*AND `user`.`level` != 'masteradmin'*/
			AND `cse_activity`.customer_id = " . $_SESSION['user_customer_id'];
		if ($user_id!="all") {
			$sql .= " 
			AND `cse_activity`.activity_user_id = " . $user_id;
		}
		
		//$sql .= " AND CAST(`cse_activity`.activity_date AS DATE) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
		$sql .= " 
		AND `cse_activity`.activity_date BETWEEN '" . $start_date . " 00:00:00' AND '" . $end_date . " 23:59:59'";
		if ($user_id!="all") {
			$sql .= " 
			ORDER BY  `cse_activity`.activity_id DESC ";
		} else {
			$sql .= " 
			ORDER BY  IFNULL(user.nickname, ''), `cse_activity`.activity_id DESC ";
		}
	//echo $sql; exit;		
	$_SESSION["last_activity_sql"] = $sql;
	session_write_close();
	/*
	INNER JOIN `cse_case_injury` cinj
			ON cse_case.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
	*/
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$activities= $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
        echo json_encode($activities);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function lastActivity() {
	if (!isset($_SESSION["last_activity_sql"])) {
		die("no activity");
	}
	$sql = $_SESSION["last_activity_sql"];
	session_write_close();
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$activity = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
         echo json_encode($activity);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getActivity($activity_id) {
	session_write_close();
	$sql = "SELECT `cse_activity`.`activity_id`, `cse_activity`.`activity_uuid`,  
	`activity_category`,
	`cse_activity`.`activity`, `cse_activity`.`activity_date`, 
	`cse_activity`.`activity_user_id`, `cse_activity`.`customer_id`
	FROM  `cse_activity` 
	INNER JOIN  `cse_case_activity` 
	ON  `cse_activity`.`activity_uuid` = `cse_case_activity`.`activity_uuid` AND `cse_case_activity`.deleted = 'N'
	INNER JOIN `cse_case` 
	ON (`cse_case_activity`.`case_uuid` = `cse_case`.`case_uuid`)
	WHERE `cse_activity`.`deleted` = 'N'
	AND `cse_activity`.`activity_id` = :activity_id
	AND `cse_activity`.customer_id = " . $_SESSION['user_customer_id'];

	//die($case_id);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("activity_id", $activity_id);
		$stmt->execute();
		$note = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($note);
        } else {
            echo $_GET['callback'] . '(' . json_encode($note) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getActivityBilling($case_id, $kinvoice_id) {
	
	getKaseActivities($case_id, "N", $kinvoice_id);
	
	return;
	/*
	session_write_close();
	$sql = "SELECT `hours`, `activity_id`, `billing_total`, `activity`, `activity_date`, `activity_user_id`, IFNULL(`cse_user`.`rate`,0) user_rate, IFNULL(`cse_user`.`tax`,0) user_tax
        FROM  `cse_activity`
        INNER JOIN  `cse_case_activity`
        ON  `cse_activity`.`activity_uuid` =
`cse_case_activity`.`activity_uuid`
		INNER JOIN  `ikase`.`cse_user`
        ON  `cse_activity`.`activity_user_id` =
`cse_user`.`user_id`
        INNER JOIN `cse_case`
        ON (`cse_case_activity`.`case_uuid` = `cse_case`.`case_uuid`)
    
    INNER JOIN (
                SELECT `case_id`, SUM(`hours`) billing_total
                FROM  `cse_activity`
                INNER JOIN  `cse_case_activity`
                ON  `cse_activity`.`activity_uuid` =
`cse_case_activity`.`activity_uuid`
                INNER JOIN `cse_case`
                ON (`cse_case_activity`.`case_uuid` =
`cse_case`.`case_uuid`)
                WHERE `cse_activity`.`deleted` = 'N'
                AND `case_id` = '" . $case_id . "'
                AND `cse_activity`.`customer_id` = '" . $_SESSION['user_customer_id'] . "'
                GROUP BY `case_id`

    ) billing_summ
        ON `cse_case`.`case_id` = `billing_summ`.`case_id`
        WHERE `cse_activity`.`deleted` = 'N'
        AND `cse_case`.`case_id` = '" . $case_id . "'
        AND `cse_activity`.`customer_id` = '" . $_SESSION['user_customer_id'] . "'
    AND `hours` > 0
    ORDER BY `cse_case`.`case_id`";

	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$billings = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($billings);
        } else {
            echo $_GET['callback'] . '(' . json_encode($billings) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	*/
}
function trackActivity() {
	$operation = passed_var("operation", "post");
	$activity = passed_var("activity", "post");
	$case_id = passed_var("case_id", "post");
	$track_id = passed_var("track_id", "post");
	$billing_time = passed_var("billing_time", "post");
	$activity = passed_var("activity", "post");
	$category = passed_var("category", "post");
	$initials = passed_var("initials", "post");
	
	if ($case_id=="undefined") {
		$case_id = -1;
	}
	if ($case_id > -1) {
		$kase = getKaseInfo($case_id);
		$case_uuid = $kase->uuid;
		
		$activity .= " for case " . $kase->case_number . " // " . $kase->name;
	
		recordActivity($operation, $activity, $case_uuid, $track_id, $category, $billing_time);
		
		echo json_encode(array("success"=>"true"));
	}
}
function recordActivity($operation, $activity, $case_uuid, $track_id, $category = "", $billing_time = 0) {
	try {
		$db = getConnection();
		
		$activity_uuid = uniqid("KS", false);
		//fractions of an hour
		if ($billing_time > 0) {
			$billing_time = $billing_time / 60;
		}
		if ($billing_time == "") { 
			$billing_time = "0.00";
		}
		
		//maybe there is a fee schedule
		if ($_SESSION['user_customer_id']=='1033' || $_SESSION['user_customer_id']=='1121') {
			$rate = getRateInfo(1);
			if (is_object($rate)) {
				$original_info = $rate->rate_info;
				$arrInfo = json_decode($original_info);
				$blnFound = false;
				foreach($arrInfo as $iindex=>$info) {
					if ($info->fee_name==$category) {
						$billing_time = $info->fee_minutes / 60;
						$blnFound = true;
						break;
					}
				}
			}
		}
		$sql = "INSERT INTO cse_activity (`activity_uuid`, `activity`, `hours`, `activity_category`, `activity_user_id`, `customer_id`)
		VALUES ('" . $activity_uuid . "', '" . addslashes($activity) . "', '" . $billing_time . "', '" . addslashes($category) . "', '" . $_SESSION['user_plain_id'] . "', " . $_SESSION['user_customer_id'] . ")";
		//echo $sql . "\r\n";
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$activity_id = $db->lastInsertId();
		
		//if we passed a valid case
		if ($case_uuid!="") {
			$last_updated_date = date("Y-m-d H:i:s");
			$case_activity_uuid = uniqid("KA", false);
			$attribute = "main";
			if ($category != "") {
				$attribute = $category;
			}
			$sql = "INSERT INTO cse_case_activity (`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_activity_uuid . "', '" . $case_uuid . "', '" . $activity_uuid . "', '" . $attribute . "', " . $track_id . ", '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			//echo $sql . "\r\n";
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		}
		$stmt = null; $db = null;
		
		return $activity_id;
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .', "sql":'. $sql .'}}'; 
	}
}
function updateBy() {
	//die(print_r($_POST));
	//if ($_POST[0]) {
	//}
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "activity";
	$table_id = "";
	$info = "";
	$apply = "";
	$activity_uuid = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		//$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="activity_uuid") {
			$activity_uuid = $value;
			continue;
		}
		if ($fieldname=="apply") {
			$apply = $value;
			continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}	
	
	//where
	$where_clause = "= '" . $activity_uuid . "'";
	$where_clause = "`" . $table_name . "_uuid`" . $where_clause . "
	AND `customer_id` = " . $_SESSION['user_customer_id'];
	
	//actual query
	$sql = "UPDATE `cse_" . $table_name . "`
	SET " . implode(",
	", $arrSet) . "
	WHERE " . $where_clause;
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		echo json_encode(array("success"=>true,));
		$db = null;	
		
		//apply
		/*
		if ($apply!="") {
			if ($apply=="all") {
				
			}
		}
		*/
		trackActivityUpdates("update", "", $activity_uuid);
		exit();
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function updateHours() {
	//die(print_r($_POST));
	//if ($_POST[0]) {
	//}
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "activity";
	$table_id = "";
	$info = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		//$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="activity_uuid") {
			$activity_uuid = $value;
			continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}	
	
	//where
	$where_clause = "= '" . $activity_uuid . "'";
	$where_clause = "`" . $table_name . "_uuid`" . $where_clause . "
	AND `customer_id` = " . $_SESSION['user_customer_id'];
	
	//actual query
	$sql = "UPDATE `cse_" . $table_name . "`
	SET " . implode(",
	", $arrSet) . "
	WHERE " . $where_clause;
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "id"=>$table_id));
		$db = null;	
		
		trackActivityUpdates("update", "", $activity_uuid);
		
		exit();
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function updateBulkHours() {
	$customer_id = $_SESSION['user_customer_id'];
	
	$activities = passed_var("activities", "post");
	$activities = json_decode($activities);
	
	//die(print_r($activities));
	
	$case_id = passed_var("case_id", "post");
	
	//actual query
	$sql = "UPDATE `cse_activity`
	SET hours = :hours
	WHERE `activity_uuid` = :activity_uuid
	AND customer_id = :customer_id";
	
	foreach($activities as $activity) {
		$hours = $activity->hours;
		$activity_uuid = $activity->activity_uuid;
		
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("activity_uuid", $activity_uuid);
			$stmt->bindParam("hours", $hours);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			
			$db = null;	
			
			trackActivityUpdates("update", "", $activity_uuid);
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}	
	echo json_encode(array("success"=>true, "case_id"=>$case_id));
	exit();
}
function updateActivity() {
	//$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "activity";
	$table_id = "";
	$info = "";
	
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="activity") {
			$value = passed_var($fieldname, "post");
		} else {
			$value = @processHTML($_POST["activity"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="status") {
			$fieldname = "activity_status";
		}
		if ($fieldname=="category") {
			$fieldname = "activity_category";
		}
		if ($fieldname=="table_id") {
			$activity_id = $value;
			continue;
		}
		if ($fieldname=="billing_date" || $fieldname=="activity_date") {
			$value = date("Y-m-d H:i:s", strtotime($value));
		}
		if ($fieldname=="table_uuid" || $fieldname=="modal_type" || $fieldname=="action_id" || $fieldname=="case_id" || $fieldname=="billing_id") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}	
	
	//where
	$where_clause = "`activity_id` = '" . $activity_id . "'";
	//$where_clause = "`" . $table_name . "_uuid`" . $where_clause . "
	$where_clause .= " 
	AND `customer_id` = " . $_SESSION['user_customer_id'];
	
	//actual query
	$sql = "UPDATE `cse_" . $table_name . "`
	SET " . implode(",
	", $arrSet) . "
	WHERE " . $where_clause;
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "id"=>$activity_id));
		$db = null;	$stmt = null;
		
		trackActivityUpdates("update", $activity_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function insertActivity() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "activity";
	$table_id = "";
	$info = "";
	$activity_status = "";
	$billing_amount = 0;
	$billing_rate = 0;
	$billing_date = "";
	$billing_unit = "";
	$timekeeper = "";
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="activity") {
			$value = passed_var($fieldname, "post");
		} else {
			$value = @processHTML($_POST["activity"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="activity") {
			$activity = $value;
		}
		if ($fieldname=="status") {
			$activity_status = $value;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
		}
		if ($fieldname=="hours") {
			$hours = $value;
		}
		if ($fieldname=="billing_date") {
			$billing_date = date("Y-m-d H:i:s", strtotime($value));
		}
		if ($fieldname=="billing_rate") {
			$billing_rate = $value;
		}
		if ($fieldname=="billing_amount") {
			$billing_amount = $value;
		}
		if ($fieldname=="billing_unit") {
			$billing_unit = $value;
		}
		if ($fieldname=="activity_uuid") {
			$activity_uuid = $value;
		}
		if ($fieldname=="modal_type") {
			$modal_type = $value;
		}
		if ($fieldname=="action_id") {
			$action_id = $value;
		}
		if ($fieldname=="category") {
			$category = $value;
		}
		if ($fieldname=="timekeeper") {
			$timekeeper = $value;
		}
		/*
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		*/
	}	
	//die($_SESSION['user_id']);
	//actual query
	
	//die(print_r($_SESSION));	
	//die($sql);
	//if we passed a valid case
	
	try {
		$activity_uuid = uniqid("RD", false);
		//$category = "Activity";
		$sql = "INSERT `cse_" . $table_name . "` 
		(`activity_uuid`, `activity`, `activity_category`, `hours`, `customer_id`, activity_user_id, `billing_date`, `billing_amount`, `billing_rate`, `billing_unit`, `activity_status`, `timekeeper`)
		VALUES
		(:activity_uuid, :activity, :category, :hours, :customer_id, :user_id, :billing_date, :billing_amount, :billing_rate, :billing_unit, :activity_status, :timekeeper)";
		
		$db = getConnection();
		
		$user_id = $_SESSION['user_plain_id'];
		if ($timekeeper!="" && $timekeeper > 0) {
			$user_id = $timekeeper;
		}
		$stmt = $db->prepare($sql);
		$stmt->bindParam("activity_uuid", $activity_uuid);
		$stmt->bindParam("activity", $activity);
		$stmt->bindParam("category", $category);
		$stmt->bindParam("hours", $hours);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("billing_date", $billing_date);
		$stmt->bindParam("billing_amount", $billing_amount);
		$stmt->bindParam("billing_rate", $billing_rate);
		$stmt->bindParam("billing_unit", $billing_unit);
		$stmt->bindParam("activity_status", $activity_status);
		$stmt->bindParam("timekeeper", $timekeeper);
		$stmt->execute();
		
		$track_id = $db->lastInsertId();
		$stmt = null; $db = null;			
	
		
		$kase = getKaseInfo($case_id);
		$case_uuid = $kase->uuid;
		if ($case_uuid!="") {
			$last_updated_date = date("Y-m-d H:i:s");
			$case_activity_uuid = uniqid("KA", false);
			$attribute = "main";
			if ($category != "") {
				$attribute = $category;
			}
			$sql = "INSERT INTO cse_case_activity (`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES (:case_activity_uuid, :case_uuid, :activity_uuid, :attribute, :track_id, :last_updated_date, :user_id, :user_customer_id)";
			
			$user_id = $_SESSION['user_id'];
			$user_customer_id = $_SESSION['user_customer_id'];
			
			$db = getConnection();
			//echo $sql . "\r\n";
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("case_activity_uuid", $case_activity_uuid);
			$stmt->bindParam("case_uuid", $case_uuid);
			$stmt->bindParam("activity_uuid", $activity_uuid);
			$stmt->bindParam("attribute", $attribute);
			$stmt->bindParam("track_id", $track_id);
			$stmt->bindParam("last_updated_date", $last_updated_date);
			$stmt->bindParam("user_id", $user_id);
			$stmt->bindParam("user_customer_id", $user_customer_id);
			$stmt->execute();
			$stmt = null; $db = null;			
		}
		
		trackActivityUpdates("insert", $track_id);
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {	
		//echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		echo json_encode(array("error"=>$e->getMessage(), "sql"=>$sql));
	}	
	$db = null;
}
function stackActivity($start_date, $end_date) {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT DISTINCT IFNULL(notifieds, '') notifieds, ccase.case_id, IF(ccase.case_number='', ccase.file_number, ccase.case_number) case_number, act.*, 
	IF(ccase.case_name='', 
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, '')), ccase.case_name) `name`, ccase.case_name
	FROM cse_activity act
	INNER JOIN cse_case_activity cac
	ON act.activity_uuid = cac.activity_uuid AND `cac`.deleted = 'N'
	
	LEFT OUTER JOIN (
		SELECT activity_uuid, notifieds FROM (
			SELECT act.activity_id, cac.activity_uuid, act.activity_date, GROUP_CONCAT(DISTINCT usr.user_name SEPARATOR ', ') notifieds
			FROM cse_case_activity cac
			INNER JOIN cse_activity act
			ON cac.activity_uuid = act.activity_uuid AND `cac`.deleted = 'N'
			INNER JOIN cse_document_track cdt
			ON cac.case_track_id = cdt.document_track_id
			INNER JOIN cse_notification notif
			ON cdt.document_uuid = notif.document_uuid
			INNER JOIN ikase.cse_user usr
			ON notif.user_uuid = usr.user_uuid
			WHERE notif.notification = 'review'
			AND act.activity LIKE '%was stack%'
			GROUP BY cac.activity_uuid
			) notif_group
		WHERE 1
		AND activity_date BETWEEN '2017-11-01 00:00:00' AND '2017-11-30 23:59:59'
    ) notifs
	ON act.activity_uuid = notifs.activity_uuid
	
	INNER JOIN cse_case ccase
	ON cac.case_uuid = ccase.case_uuid
	
	INNER JOIN `cse_case_injury` cinj
	ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
	INNER JOIN `cse_injury` inj
	ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
	
	LEFT OUTER JOIN `cse_case_corporation` ecorp
	ON (ccase.case_uuid = ecorp.case_uuid AND ecorp.attribute = 'employer' AND ecorp.deleted = 'N')
	LEFT OUTER JOIN `cse_corporation` employer
	ON ecorp.corporation_uuid = employer.corporation_uuid
	
	LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
	LEFT OUTER JOIN ";
	
	if (($_SESSION['user_customer_id']==1033)) { 
	$sql .= "(" . SQL_PERSONX . ")";
	} else {
	$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid

	WHERE act.customer_id = " . $customer_id . "
	AND act.deleted = 'N'
	AND ccase.deleted = 'N'
	AND activity_category = 'Documents'
	AND activity LIKE '%was stack%'";
	if ($start_date!="0000-00-00") {
		$sql .= " AND activity_date >= '" . date("Y-m-d", strtotime($start_date)) . " 00:00:00'";
	}
	if ($end_date!="0000-00-00") {
		$sql .= " AND activity_date <= '" . date("Y-m-d", strtotime($end_date)) . " 23:59:59'";
	}
	$sql .= " ORDER BY activity_id DESC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$activities = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($activities);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteActivity() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$id = passed_var("activity_id", "post");
	if ($id=="") {
		$id = passed_var("id", "post");
	}
	$sql = "UPDATE cse_activity act
				SET act.`deleted` = 'Y'
				WHERE `activity_id`=:id
				AND `customer_id` = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		trackActivityUpdates("delete", $id);
		
		echo json_encode(array("success"=>"activity marked as deleted", "sql"=>$sql));
		
		//if this is the _only_ injury, then we just clear it and undelete it
		//the information will be part of the tracking
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteInvoice() {
	$id = passed_var("invoice_id", "post");
	if ($id=="") {
		$id = passed_var("id", "post");
	}
	$sql = "UPDATE cse_invoice inv
				SET inv.`deleted` = 'Y'
				WHERE `invoice_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		//trackInjury("delete", $id);
		
		echo json_encode(array("success"=>"invoice marked as deleted", "sql"=>$sql));
		
		//if this is the _only_ injury, then we just clear it and undelete it
		//the information will be part of the tracking
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getActivityDemographics() {
	session_write_close();
	
	$sql = "SELECT ccase.case_id, IF(ccase.case_number = '', ccase.file_number, ccase.case_number) case_number, ccase.file_number, ca.activity, ca.activity_date, 
	usr.user_name, ca.activity_user_id, 
	IFNULL(cp.person_id, 0) person_id, IFNULL(cp.full_name, '') full_name, IF(ccase.case_name = '', IFNULL(cp.full_name, ''), ccase.case_name) case_name
	FROM cse_activity ca
	INNER JOIN ikase.cse_user usr
	ON ca.activity_user_id = usr.user_id
	INNER JOIN cse_case_activity cca
	ON ca.activity_uuid = cca.activity_uuid AND `cca`.deleted = 'N'
	INNER JOIN cse_case ccase
	ON cca.case_uuid = ccase.case_uuid
	INNER JOIN cse_case_person ccp
	ON ccase.case_uuid = ccp.case_uuid AND ccp.deleted = 'N'
	LEFT OUTER JOIN cse_person cp
	ON ccp.person_uuid = cp.person_uuid
	WHERE ca.activity_category = 'Matrix Referral exported'
	AND ca.customer_id = :customer_id
	ORDER BY YEAR(activity_date) DESC, MONTH(activity_date) DESC, IF(ccase.case_name = '', IFNULL(cp.full_name, ''), ccase.case_name)";
	// LIKE 'Demographics Sheet for Case%'
	
	$customer_id = $_SESSION["user_customer_id"];
	
	try {
		$db = getConnection();	
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$demographics = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($demographics);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function trackActivityUpdates($operation, $activity_id, $activity_uuid = "") {
	$sql = "INSERT INTO cse_activity_track (`user_uuid`, `user_logon`, `operation`, `activity_id`, `activity_uuid`, `activity`, `activity_category`, `activity_date`, `hours`, `timekeeper`, `initials`, `attorney`, `activity_user_id`, `customer_id`, `deleted`, `activity_status`, `billing_rate`, `billing_date`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `activity_id`, `activity_uuid`, `activity`, `activity_category`, `activity_date`, `hours`, `timekeeper`, `initials`, `attorney`, `activity_user_id`, `customer_id`, `deleted`, `activity_status`, `billing_rate`, `billing_date`
	FROM cse_activity
	WHERE 1";
	if ($activity_id!="") {
		$sql .= "
		AND activity_id = '" . $activity_id . "'";
	}
	if ($activity_uuid!="") {
		$sql .= "
		AND activity_uuid = '" . $activity_uuid . "'";
	}
	$sql .= "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function hasVocationReferral($case_id) {
	session_write_close();;
	$customer_id =  $_SESSION['user_customer_id'];
	
	$sql = "SELECT COUNT(act.activity_id) activity_count
	FROM cse_activity act
	INNER JOIN cse_case_activity cact
	ON act.activity_uuid = cact.activity_uuid AND `cact`.deleted = 'N'
	INNER JOIN cse_case ccase
	ON cact.case_uuid = ccase.case_uuid
	WHERE act.activity_category = 'Vocational'
	AND act.deleted = 'N'
	AND ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$activity = $stmt->fetchObject();
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($activity);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
       	echo json_encode($error);
	}
	die();
}
?>