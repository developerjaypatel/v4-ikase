<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/billing', function (RouteCollectorProxy $app) {
		$app->get('', 'getBillings');
		$app->get('/{case_id}/{action_id}/{action_type}', 'getBilling');
		$app->post('/add', 'addFullBilling');
	});
//	$app->get('/personalinjury/{case_id}',	'getPersonalInjury');
//	$app->post('/personal_injury/update', 'updatePersonalInjury');

	$app->group('/medicalbilling', function (RouteCollectorProxy $app) {
		$app->get('/{id}', 'getMedicalBilling');
		$app->post('/add', 'saveMedicalBilling');
		$app->post('/update', 'saveMedicalBilling');
		$app->post('/delete', 'deleteMedicalBilling');
	});

	$app->get('/medicalbillings/{case_id}', 'getMedicalBillings');
	$app->get('/medicalbillingsummary/{case_id}', 'getMedicalBillingsLossSummary');
	$app->get('/medicalcorpsummary/{case_id}', 'getMedicalBillingsSummary');
	$app->get('/medsum/{case_id}', 'getMedicalSummary');
	$app->get('/corpbillings/{case_id}/{corporation_id}', 'getCorpMedicalBillings');

	$app->group('/deduction', function (RouteCollectorProxy $app) {
		$app->get('/{id}', 'getDeduction');
		$app->post('/save', 'saveDeduction');
		$app->post('/delete', 'deleteDeduction');
	});
	$app->get('/deductions/{case_id}', 'getDeductions');
	$app->get('/deductionstotal/{case_id}', 'getTotalDeductions');

	$app->group('/adjustment', function (RouteCollectorProxy $app) {
		$app->get('/{id}', 'getAdjustment');
		$app->post('/save', 'saveAdjustment');
		$app->post('/delete', 'deleteAdjustment');
	});
	$app->get('/adjustmentstotal/{account_id}', 'getTotalAdjustments');
	$app->get('/adjustmentbytype/{account_id}/{type}', 'getAdjustmentsByType');
	$app->get('/account/adjustments/{account_id}', 'getAdjustments');

	$app->group('/kinvoice', function (RouteCollectorProxy $app) {
		$app->get('/{kinvoice_id}', 'getKInvoice');
		$app->post('/delete', 'deleteKInvoice');
		$app->post('/update', 'updateKInvoice');
		$app->post('/transfer', 'transferKInvoice');
	});
	$app->get('/kinvoices/{case_id}', 'getKaseKInvoices');
	$app->get('/kinvoiceitems/{kinvoice_id}', 'getKInvoiceItems');
	$app->get('/kinvoicedoc/{document_id}', 'getKInvoiceByDocument');
	$app->get('/kinvoicetemplates/{document_id}', 'getInvoiceTemplatesByDocument');
	$app->get('/kinvoicetemplatelists', 'getFirmInvoiceTemplates');

	$app->get('/kinvoicecheck/{check_id}', 'getKInvoiceByCheck');
	$app->get('/kinvoicecase/{case_id}/{document_id}', 'getKaseKInvoicesByDocument');
	$app->get('/firminvoicescount', 'getOutstandingKInvoicesCount');
	$app->get('/prebillinvoicescount', 'getPreBillKInvoicesCount');
	$app->get('/paidinvoicescount', 'getPaidKInvoicesCount');
	$app->get('/firminvoices/{account_type}', 'getFirmKInvoices');
	$app->get('/kasekinvoicescount/{case_id}', 'getKaseKInvoicesCount');

	$app->group('/kinvoiceitem', function (RouteCollectorProxy $app) {
		$app->post('/add', 'saveKInvoiceItem');
		$app->post('/update', 'updateKInvoiceItem');
	});

	$app->group('/account', function (RouteCollectorProxy $app) {
//		$app->post('/update', 'updateBankAccount');
		$app->get('/{account_id}', 'getBankAccount');
		$app->get('/cases/{account_id}', 'getBankAccountKases');
		$app->get('/case/{case_id}', 'getKaseAccount');
		$app->get('/firmbalance/{account_type}', 'getFirmAccountBalance');
		$app->get('/displaybalance/{account_id}', 'displayAccountBalance');
		$app->get('/balance/{case_id}/{account_type}', 'getKaseAccountBalance');
		$app->get('/balanceall/{account_type}', 'getAllKasesAccountBalance');
		$app->get('/bytype/{case_id}/{account_type}', 'getKaseAccountByType');

		$app->post('/add', 'saveBankAccount');
		$app->post('/starting', 'setStartingAmount');
		$app->post('/attach', 'attachBankAccount');
		$app->post('/detach', 'detachBankAccount');
		$app->post('/clear', 'clearAttachBankAccount');
		$app->post('/delete', 'deleteBankAccount');
	});
	$app->get('/accounts/{account_type}', 'getBankAccountsByType');
	$app->get('/accountsno/{case_id}/{account_type}', 'getKaseNoAttach');
	$app->get('/listaccounts', 'getBankAccounts');
})->add(Api\Middleware\Authorize::class);

function getKaseKInvoicesByDocument($case_id, $document_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT DISTINCT ccase.case_id, ccase.case_name, ccase.case_number, ccase.file_number, 
	corp.corporation_id, corp.company_name, corp.`type`, 
	usr.user_id assigned_to, usr.nickname assigned_nickname, usr.user_name assigned_name, 
	doc.document_id, doc.document_filename,
	ki.*
	FROM cse_kinvoice ki
	
	INNER JOIN cse_case_kinvoice cck
	ON ki.kinvoice_uuid = cck.kinvoice_uuid
	
	INNER JOIN cse_case ccase
	ON cck.case_uuid = ccase.case_uuid
	
    INNER JOIN ikase.cse_user usr
    ON cck.last_update_user = usr.user_uuid
	
	INNER JOIN cse_case_document ccd
	ON ccase.case_uuid = ccd.case_uuid
	
	INNER JOIN cse_document doc
	ON ccd.document_uuid = doc.document_uuid AND doc.deleted = 'N'
	
	INNER JOIN cse_document_kinvoice cdk
    ON ki.kinvoice_uuid = cdk.kinvoice_uuid AND doc.document_uuid = cdk.document_uuid AND cdk.deleted = 'N'
	
	INNER JOIN cse_document par
	ON doc.parent_document_uuid = par.document_uuid
	
    INNER JOIN cse_corporation_kinvoice corpinv
    ON ki.kinvoice_uuid = corpinv.kinvoice_uuid AND corpinv.deleted = 'N'
    
    INNER JOIN cse_corporation corp
    ON corpinv.corporation_uuid = corp.corporation_uuid
	
	WHERE ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id
	AND par.document_id = :document_id
	AND ki.deleted = 'N'
	AND ki.total > 0
	ORDER BY ki.kinvoice_id";
	
	//die($sql);
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("document_id", $document_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kinvoices = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		echo json_encode($kinvoices);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPreBillKInvoicesCount() {
	$kinvoices = getFirmKInvoices("prebill", "", true);
	
	echo json_encode(array("success"=>true, "count"=>count($kinvoices)));
}
function getPaidKInvoicesCount() {
	$kinvoices = getFirmKInvoices("paid", "", true);
	
	echo json_encode(array("success"=>true, "count"=>count($kinvoices)));
}
function getOutstandingKInvoicesCount() {
	$kinvoices = getFirmKInvoices("receivable", "", true);
	
	echo json_encode(array("success"=>true, "count"=>count($kinvoices)));
}
function getFirmKInvoices($account_type, $case_id = "", $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT DISTINCT ccase.case_id, ccase.case_name, ccase.case_number, ccase.file_number, 
	usr.user_id assigned_to, usr.nickname assigned_nickname, usr.user_name assigned_name, 
	corp.corporation_id, corp.company_name, corp.`type` company_type,
	doc.document_id, doc.document_filename,
	IFNULL(cmk.attribute_2, 'N') sent_status,
	IFNULL(cmk.last_updated_dates, '') sent_dates,
	IFNULL(trust.account_id, '') trust_account_id,
	IFNULL(trust.account_name, '') trust_account_name,
	IFNULL(operating.account_id, '') operating_account_id,
	IFNULL(operating.account_name, '') operating_account_name,
	ki.*, ki.kinvoice_id id, ki.kinvoice_uuid uuid
	FROM cse_kinvoice ki
	
	INNER JOIN cse_case_kinvoice cck
	ON ki.kinvoice_uuid = cck.kinvoice_uuid
	
	INNER JOIN cse_case ccase
	ON cck.case_uuid = ccase.case_uuid
	
    INNER JOIN ikase.cse_user usr
    ON cck.last_update_user = usr.user_uuid
	
    INNER JOIN cse_document_kinvoice cdk
    ON ki.kinvoice_uuid = cdk.kinvoice_uuid AND cdk.deleted = 'N'
    
    INNER JOIN cse_document doc
    ON cdk.document_uuid = doc.document_uuid AND doc.deleted = 'N'
	
	INNER JOIN cse_corporation_kinvoice corpinv
    ON ki.kinvoice_uuid = corpinv.kinvoice_uuid AND corpinv.deleted = 'N'
    
    INNER JOIN cse_corporation corp
    ON corpinv.corporation_uuid = corp.corporation_uuid
	
	LEFT OUTER JOIN cse_case_account cca_trust
	ON ccase.case_uuid = cca_trust.case_uuid AND cca_trust.attribute = 'trust' AND cca_trust.deleted = 'N'
	LEFT OUTER JOIN cse_account trust
	ON cca_trust.account_uuid = trust.account_uuid
	
	LEFT OUTER JOIN cse_case_account cca_operating
	ON ccase.case_uuid = cca_operating.case_uuid AND cca_operating.attribute = 'operating' AND cca_operating.deleted = 'N'
	LEFT OUTER JOIN cse_account operating
	ON cca_operating.account_uuid = operating.account_uuid
	
	LEFT OUTER JOIN (
		SELECT mk.*, last_updated_dates
		FROM cse_message_kinvoice mk
		INNER JOIN (
			SELECT kinvoice_uuid, MAX(message_kinvoice_id) max_id, GROUP_CONCAT(last_updated_date) last_updated_dates 
			FROM ikase.cse_message_kinvoice
			WHERE deleted = 'N'
			AND attribute_2 = 'sent'
			GROUP BY kinvoice_uuid
		) max_k
		ON mk.message_kinvoice_id = max_k.max_id
    ) cmk
	ON ki.kinvoice_uuid = cmk.kinvoice_uuid
	
	WHERE 1";
	if ($case_id!="") {
		$sql .= "
		AND ccase.case_id = :case_id";
	}
	if ($account_type!="prebill") {
		if ($account_type=="receivable") {
			$sql .= "
			AND ki.total > ki.payments";
		}
		if ($account_type=="paid") {
			$sql .= "
			AND (ki.total - ki.payments) <= 0";
		}
		if ($case_id=="") {
			$sql .= "
			AND ki.kinvoice_type = 'I'";
		}
	} else {
		//prebill
		$sql .= "
			AND ki.kinvoice_type = 'P'";
	}
	$sql .= "
	AND ccase.customer_id = :customer_id
	AND ki.deleted = 'N'
	ORDER BY ccase.case_id ASC, ki.invoice_counter ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($case_id!="") {
			$stmt->bindParam("case_id", $case_id);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kinvoices = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		if ($blnReturn) {
			return $kinvoices;
		} else {
			echo json_encode($kinvoices);
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseKinvoiceNextCounter($case_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT COUNT(DISTINCT cck.kinvoice_uuid) invoice_count
	FROM cse_case_kinvoice cck
	INNER JOIN cse_kinvoice ki
	ON cck.kinvoice_uuid = ki.kinvoice_uuid AND ki.deleted = 'N'
	INNER JOIN cse_case ccase
	ON cck.case_uuid = ccase.case_uuid
	WHERE cck.deleted = 'N'
	AND ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id";
	
	$db = getConnection();

	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$counter = $stmt->fetchObject();
	
	$invoice_counter = 1;
	if (is_object($counter)) {
		$invoice_counter = $counter->invoice_count + 1;
	}
	return $invoice_counter;
}
function getKaseKInvoicesCount($case_id) {
	$kinvoices = getFirmKInvoices("receivable", $case_id, true);
	
	echo json_encode(array("success"=>true, "count"=>count($kinvoices)));
}
function getKaseKInvoices($case_id) {
	getFirmKInvoices("", $case_id, false);
}
function getKInvoice($kinvoice_id, $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT IFNULL(ccase.case_id, '') case_id, 
	IFNULL(usr.user_id, '') assigned_to, IFNULL(usr.nickname, '') assigned_nickname, IFNULL(usr.user_name, '') assigned_name, 
	IFNULL(corpinv.attribute, 'carrier') invoiced_firm, IFNULL(corp.corporation_id, '') corporation_id, IFNULL(corp.company_name, '') company_name, 
	doc.document_id, doc.document_filename, 
	par.document_id parent_id, par.document_filename parent_filename, 
	ki.*, ki.kinvoice_id id
	
	FROM cse_kinvoice ki
	
	INNER JOIN cse_document_kinvoice cdk
	ON ki.kinvoice_uuid = cdk.kinvoice_uuid
	
	INNER JOIN cse_document doc
	ON cdk.document_uuid = doc.document_uuid AND doc.deleted = 'N'
	
	INNER JOIN cse_document par
	ON doc.parent_document_uuid = par.document_uuid
	
	LEFT OUTER JOIN cse_case_kinvoice cck
	ON ki.kinvoice_uuid = cck.kinvoice_uuid
	
	LEFT OUTER JOIN cse_case ccase
	ON cck.case_uuid = ccase.case_uuid
	
    LEFT OUTER JOIN ikase.cse_user usr
    ON cck.last_update_user = usr.user_uuid
	
	LEFT OUTER JOIN cse_corporation_kinvoice corpinv
    ON ki.kinvoice_uuid = corpinv.kinvoice_uuid AND corpinv.deleted = 'N'
    
    LEFT OUTER JOIN cse_corporation corp
    ON corpinv.corporation_uuid = corp.corporation_uuid
	
	WHERE ki.kinvoice_id = :kinvoice_id
	AND ki.customer_id = :customer_id";
	//deleted?
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoice_id", $kinvoice_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kinvoice = $stmt->fetchObject();
		
		if ($blnReturn) {
			return $kinvoice;
		} else {
			echo json_encode($kinvoice);
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKInvoiceByCheck($check_id, $blnReturn = true) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT `kinv`.*, `kinv`.`kinvoice_id` `id`
	FROM cse_kinvoice kinv
	INNER JOIN cse_kinvoice_check cdk
	ON kinv.kinvoice_uuid = cdk.kinvoice_uuid
	INNER JOIN cse_check chck
	ON cdk.check_uuid = chck.check_uuid 
	WHERE chck.check_id = :check_id
	AND kinv.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("check_id", $check_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kinvoice = $stmt->fetchObject();
		
		if ($blnReturn) {
			return $kinvoice;
		} else {
			if (is_object($kinvoice)) {
				$items = getKInvoiceItems($kinvoice->id, true);
						
				echo json_encode(array("success"=>true, "kinvoice_id"=>$kinvoice->id, "items"=>$items));
			} else {
				echo json_encode(array("success"=>false, "kinvoice_id"=>-1, "items"=>array()));
			}
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getFirmInvoiceTemplates() {
	getInvoiceTemplatesByDocument("");
}
function getInvoiceTemplatesByDocument($document_id = "") {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT `kinv`.*, 
	doc.document_id, doc.document_uuid, 
	`kinv`.`kinvoice_id` `id`
	FROM cse_kinvoice kinv
	INNER JOIN cse_document_kinvoice cdk
	ON kinv.kinvoice_uuid = cdk.kinvoice_uuid
	INNER JOIN cse_document doc
	ON cdk.document_uuid = doc.document_uuid 
	WHERE 1
	AND doc.customer_id = :customer_id
	AND doc.deleted = 'N'
	AND kinv.template = 'Y'
	AND kinv.deleted = 'N'";
	if ($document_id!="") {
		$sql .= "
		AND doc.document_id = :document_id";
	}
	$sql .= "
	ORDER BY kinv.template_name";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($document_id!="") {
			$stmt->bindParam("document_id", $document_id);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kinvoices = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		echo json_encode($kinvoices);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKInvoiceByDocument($document_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT `kinv`.`kinvoice_id` `id`
	FROM cse_kinvoice kinv
	INNER JOIN cse_document_kinvoice cdk
	ON kinv.kinvoice_uuid = cdk.kinvoice_uuid
	INNER JOIN cse_document doc
	ON cdk.document_uuid = doc.document_uuid 
	WHERE doc.document_id = :document_id
	AND doc.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("document_id", $document_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kinvoice = $stmt->fetchObject();
		
		if (is_object($kinvoice)) {
			$items = getKInvoiceItems($kinvoice->id, true);
					
			echo json_encode(array("success"=>true, "kinvoice_id"=>$kinvoice->id, "items"=>$items));
		} else {
			echo json_encode(array("success"=>false, "kinvoice_id"=>-1, "items"=>array()));
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKInvoiceInfo($kinvoice_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT ki.*, 
	IFNULL(corp.corporation_id, '') corporation_id, 
	IFNULL(corp.corporation_uuid, '') corporation_uuid, 
	IFNULL(corp.company_name, '') company_name,
	IFNULL(usr.user_id, '') assigned_to, 
	IFNULL(usr.nickname, '') assigned_nickname, 
	IFNULL(usr.user_name, '') assigned_name, 
	ki.kinvoice_id id, ki.kinvoice_uuid uuid
	
	FROM `cse_kinvoice` ki
	
	LEFT OUTER JOIN `cse_corporation_kinvoice` cck
	ON ki.kinvoice_uuid = cck.kinvoice_uuid AND cck.deleted = 'N'
	
	LEFT OUTER JOIN `cse_corporation` corp
	ON cck.corporation_uuid = corp.corporation_uuid
	
    LEFT OUTER JOIN cse_case_kinvoice casek
	ON ki.kinvoice_uuid = casek.kinvoice_uuid
	
	LEFT OUTER JOIN ikase.cse_user usr
    ON casek.last_update_user = usr.user_uuid
	
	WHERE ki.kinvoice_id = :kinvoice_id
	AND ki.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoice_id", $kinvoice_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kinvoice = $stmt->fetchObject();
		
        return $kinvoice;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKInvoiceItems($kinvoice_id, $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	if ($kinvoice_id=="N") {
		$kinvoice_id = -1;
	}
	$sql = "SELECT `kinvoiceitem_id`,
		kinv.kinvoice_id, kinv.kinvoice_uuid, kinv.template, kinv.template_name, kinv.hourly_rate, 
		`kinvoiceitem_uuid`, `item_name`, `item_description`, `exact`, `minutes`, `kinvi`.`amount`, `kinvi`.`unit`, 
		`kinvi`.`customer_id`, `kinvi`.`deleted`, `kinvoiceitem_id` `id`, `kinvoiceitem_uuid`	`uuid`
		FROM `cse_kinvoiceitem` kinvi
		
		INNER JOIN `cse_kinvoice` kinv
		ON `kinvi`.kinvoice_uuid = kinv.kinvoice_uuid
		
		WHERE kinv.customer_id = :customer_id
		AND kinv.kinvoice_id = :kinvoice_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoice_id", $kinvoice_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kinvoice_items = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		if (!$blnReturn) {
        	echo json_encode($kinvoice_items);
		} else {
			return $kinvoice_items;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKInvoiceItem($kinvoiceitem_id, $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT `kinvoiceitem_id`,
		kinv.kinvoice_id,
		kinv.hourly_rate,
		`kinvoiceitem_uuid`, `item_name`, `item_description`, `unit`, `exact`, `kinvi`.`customer_id`, `kinvi`.`deleted`, `kinvoiceitem_id` `id`, `kinvoiceitem_uuid`	`uuid`
		FROM `cse_kinvoiceitem` kinvi
		INNER JOIN `cse_kinvoice` kinv
		ON `kinvi`.kinvoice_uuid = kinv.kinvoice_uuid
		WHERE kinv.customer_id = :customer_id
		AND `kinvoiceitem_id` = :kinvoiceitem_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoiceitem_id", $kinvoiceitem_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kinvoice_item = $stmt->fetchObject();
		
		if (!$blnReturn) {
        	echo json_encode($kinvoice_item);
		} else {
			return $kinvoice_item;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function saveKInvoiceAndItems() {
	session_write_close();
	
	$user_uuid = $_SESSION["user_id"];
	$customer_id = $_SESSION["user_customer_id"];
	$right_now = date("Y-m-d H:i:s");
	
	$kinvoice_id = passed_var("kinvoice_id", "post");
	
	$hourly_rate = passed_var("hourly_rate", "post");
	$document_id = passed_var("document_id", "post");
	$parent_kinvoice_id = passed_var("parent_kinvoice_id", "post");
	$item_name = passed_var("item_name", "post");
	$item_description = passed_var("item_description", "post");

	//add an invoice, link it to document
	$kinvoice_uuid = uniqid("KI", false);		
	$parent_kinvoice = getKInvoiceInfo($parent_kinvoice_id);
	$parent_kinvoice_uuid = $parent_kinvoice->uuid;

	$sql = "INSERT INTO cse_kinvoice (`kinvoice_uuid`, `parent_kinvoice_uuid`, `hourly_rate`, `customer_id`, `template`, `template_name`)
	VALUES (:kinvoice_uuid, :parent_kinvoice_uuid, :hourly_rate, :customer_id, :template, :template_name)";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
		$stmt->bindParam("parent_kinvoice_uuid", $parent_kinvoice_uuid);
		$stmt->bindParam("hourly_rate", $hourly_rate);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("template", $template);
		$stmt->bindParam("template_name", $template_name);
		$stmt->execute();
		
		$kinvoice_id = $db->lastInsertId();
		
		trackKInvoice("insert", $kinvoice_id);
		
	} catch(PDOException $e) {
		$error = array("error doc"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
	
	$table_uuid = uniqid("II", false);
	
	$sql = "INSERT INTO `cse_kinvoiceitem` (kinvoiceitem_uuid, kinvoice_uuid, item_name, item_description, customer_id)
	SELECT '" . $table_uuid . "', :kinvoice_uuid, :item_name, :item_description, :customer_id
						FROM dual
						WHERE NOT EXISTS (
							SELECT `kinvi`.* 
							FROM `cse_kinvoiceitem` kinvi
							INNER JOIN cse_kinvoice kinv
							ON `kinvi`.kinvoice_uuid = kinv.kinvoice_uuid
							WHERE kinv.customer_id = :customer_id
							AND kinv.kinvoice_id = :kinvoice_id
							AND item_name = :item_name
						)";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoice_id", $kinvoice_id);
		$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
		$stmt->bindParam("item_name", $item_name);
		$stmt->bindParam("item_description", $item_description);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		//get the new id
		$rowcount = $stmt->rowCount();
		if ($rowcount==0) {
			//go get the id
			$sql = "SELECT kinvoiceitem_id id
			FROM `cse_kinvoiceitem`";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("kinvoice_id", $kinvoice_id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$kinvoiceitem = $stmt->fetchObject();
			
			$kinvoiceitem_id = $kinvoiceitem->id;
		} else {
			$kinvoiceitem_id = $db->lastInsertId();
		}
		
		echo json_encode(array("success"=>true, "kinvoice_id"=>$kinvoice_id, "kinvoiceitem_id"=>$kinvoiceitem_id));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function saveKInvoiceItem() {
	session_write_close();
	
	$user_uuid = $_SESSION["user_id"];
	$customer_id = $_SESSION["user_customer_id"];
	$right_now = date("Y-m-d H:i:s");
	
	$kinvoice_id = passed_var("kinvoice_id", "post");
	
	$hourly_rate = passed_var("hourly_rate", "post");
	$document_id = passed_var("document_id", "post");
	$item_name = passed_var("item_name", "post");
	$item_description = passed_var("item_description", "post");
	
	$amount = passed_var("amount", "post");
	$unit = passed_var("unit", "post");
	
	$exact = passed_var("exact", "post");
	$template_name = passed_var("template_name", "post");
	
	if ($kinvoice_id <= 0 || $kinvoice_id == "N" || $kinvoice_id == "") {
		$document = getDocumentInfo($document_id);
		$template = "Y";
		if ($template_name == "") {
			$template_name = $document->document_name;
		}
		//add an invoice, link it to document
		$kinvoice_uuid = uniqid("KI", false);
		$parent_kinvoice_uuid = "";
		if (isset($_POST["parent_kinvoice_id"])) {
			$parent_kinvoice_id = passed_var("parent_kinvoice_id", "post");
			$parent_kinvoice = getKInvoiceInfo($parent_kinvoice_id);
			$parent_kinvoice_uuid = $parent_kinvoice->uuid;
		}
		$sql = "INSERT INTO cse_kinvoice (`kinvoice_uuid`, `parent_kinvoice_uuid`, `hourly_rate`, `customer_id`, `template`, `template_name`)
		VALUES (:kinvoice_uuid, :parent_kinvoice_uuid, :hourly_rate, :customer_id, :template, :template_name)";
		
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
			$stmt->bindParam("parent_kinvoice_uuid", $parent_kinvoice_uuid);
			$stmt->bindParam("hourly_rate", $hourly_rate);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("template", $template);
			$stmt->bindParam("template_name", $template_name);
			$stmt->execute();
			
			$kinvoice_id = $db->lastInsertId();
			
			trackKInvoice("insert", $kinvoice_id);
			
			$document_kinvoice_uuid = uniqid("KI", false);
			
			//attach to document
			$sql = "INSERT INTO `cse_document_kinvoice`
(`document_kinvoice_uuid`, `document_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
VALUES
(:document_kinvoice_uuid, :document_uuid, :kinvoice_uuid, 'main', :right_now, :user_uuid, :customer_id)";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
			$stmt->bindParam("document_uuid", $document->uuid);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("document_kinvoice_uuid", $document_kinvoice_uuid);
			$stmt->bindParam("right_now", $right_now);
			$stmt->bindParam("user_uuid", $user_uuid);
			$stmt->execute();
			
		} catch(PDOException $e) {
			$error = array("error doc"=> array("text"=>$e->getMessage()));
			die(json_encode($error));
		}	
		
	} else {
		//lookup
		$kinvoice = getKInvoiceInfo($kinvoice_id);
		$kinvoice_uuid = $kinvoice->uuid;
		
		//let's update the invoice itself with top elements
		$sql = "UPDATE cse_kinvoice
		SET hourly_rate = :hourly_rate,
		template_name = :template_name
		WHERE kinvoice_id = :kinvoice_id
		AND customer_id = :customer_id";
		
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("kinvoice_id", $kinvoice_id);
			$stmt->bindParam("template_name", $template_name);
			$stmt->bindParam("hourly_rate", $hourly_rate);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			
			trackKInvoice("update", $kinvoice_id);
		} catch(PDOException $e) {
			$error = array("error3"=> array("text"=>$e->getMessage()));
			die(json_encode($error));
		}	
	}
	$table_uuid = uniqid("II", false);
	
	$sql = "INSERT INTO `cse_kinvoiceitem` (kinvoiceitem_uuid, kinvoice_uuid, item_name, item_description, amount, unit, exact, customer_id)
	SELECT '" . $table_uuid . "', :kinvoice_uuid, :item_name, :item_description, :amount, :unit, :exact, :customer_id
						FROM dual
						WHERE NOT EXISTS (
							SELECT `kinvi`.* 
							FROM `cse_kinvoiceitem` kinvi
							INNER JOIN cse_kinvoice kinv
							ON `kinvi`.kinvoice_uuid = kinv.kinvoice_uuid
							WHERE kinv.customer_id = :customer_id
							AND kinv.kinvoice_id = :kinvoice_id
							AND item_name = :item_name
						)";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoice_id", $kinvoice_id);
		$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
		$stmt->bindParam("item_name", $item_name);
		$stmt->bindParam("item_description", $item_description);
		$stmt->bindParam("amount", $amount);
		$stmt->bindParam("unit", $unit);
		$stmt->bindParam("exact", $exact);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		//get the new id
		$rowcount = $stmt->rowCount();
		if ($rowcount==0) {
			//go get the id
			$sql = "SELECT kinvoiceitem_id id
			FROM `cse_kinvoiceitem`";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("kinvoice_id", $kinvoice_id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$kinvoiceitem = $stmt->fetchObject();
			
			$kinvoiceitem_id = $kinvoiceitem->id;
		} else {
			$kinvoiceitem_id = $db->lastInsertId();
		}
		
		echo json_encode(array("success"=>true, "kinvoice_id"=>$kinvoice_id, "kinvoiceitem_id"=>$kinvoiceitem_id));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}

function updateKInvoice() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$kinvoice_id = passed_var("kinvoice_id", "post");
	$fieldname = passed_var("fieldname", "post");
	$value = passed_var("value", "post");
	
	//let's update the invoice itself with top elements
	$sql = "UPDATE cse_kinvoice
	SET `" . $fieldname . "` = '" . addslashes($value) . "'
	WHERE kinvoice_id = :kinvoice_id
	AND customer_id = :customer_id";
		
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoice_id", $kinvoice_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		trackKInvoice("update", $kinvoice_id);
		
		echo json_encode(array("success"=>true, "kinvoice_id"=>$kinvoice_id));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function transferKInvoice() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$case_id = passed_var("case_id", "post");
	$kinvoice_id = passed_var("kinvoice_id", "post");
	$invoice_total = passed_var("invoice_total", "post");
	
	$paid_date = date("Y-m-d H:i:s");
	//get the account id
	$account = getBankAccount("", $case_id, "trust", true);
	//die(print_r($account));
	
	$account_id = $account->id;
	
	//update the account balance
	try {
		$sql = "UPDATE cse_kinvoice
		SET payments = total,
		fund_transfer = 'C',
		paid_date = :paid_date
		WHERE kinvoice_id = :kinvoice_id
		AND customer_id = :customer_id";
		
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("kinvoice_id", $kinvoice_id);
		$stmt->bindParam("paid_date", $paid_date);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		trackKInvoice("paid", $kinvoice_id);
		
		//die("updated");
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
		
		trackAccount("transfer", $account_id);
		
		echo json_encode(array("success"=>true, "account_id"=>$account_id));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		die();
	}
}

function updateKInvoiceItem() {
	session_write_close();
	
	$table_uuid = uniqid("SF", false);
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	
	$id = passed_var("item_id", "post");
	$deleted = passed_var("deleted", "post");
	$item_name = passed_var("item_name", "post");
	$exact = passed_var("exact", "post");
	$item_description = passed_var("item_description", "post");
	$amount = passed_var("amount", "post");
	$unit = passed_var("unit", "post");
		
	$sql = "UPDATE `cse_kinvoiceitem` 
	SET `item_name` = :item_name,
	`exact` = :exact,
	`item_description` = :item_description,
	`amount` = :amount,
	`unit` = :unit,
	deleted = :deleted
	WHERE `kinvoiceitem_id` = :id
	AND customer_id = :customer_id";
	//die($sql);
	try {
		$customer_id = $_SESSION["user_customer_id"];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("item_name", $item_name);
		$stmt->bindParam("item_description", $item_description);
		$stmt->bindParam("exact", $exact);
		$stmt->bindParam("amount", $amount);
		$stmt->bindParam("unit", $unit);
		$stmt->bindParam("deleted", $deleted);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		echo json_encode(array("success"=>true));
		
		trackKInvoice("update", $id);
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function deleteKInvoice($kinvoice_id = "") {
	if ($kinvoice_id == "") {
		$id = passed_var("id", "post");
	} else {
		$id = $kinvoice_id;
	}
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "UPDATE `cse_kinvoice` 
			SET `deleted` = 'Y'
			WHERE `kinvoice_id` = :id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		if ($kinvoice_id == "") {
			echo json_encode(array("success"=>"invoice marked as deleted"));
		}
		
		trackKInvoice("delete", $id);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackKInvoice("delete", $id);
}
function getMedicalSummary($case_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT corp.corporation_id, corp.company_name, 
	SUM(billed) billed, SUM(paid) paid, SUM(adjusted) adjusted
	FROM cse_medicalbilling mb
	INNER JOIN cse_case_medicalbilling ccm
	ON mb.medicalbilling_uuid = ccm.medicalbilling_uuid
	INNER JOIN cse_case ccase
	ON ccm.case_uuid = ccase.case_uuid
	INNER JOIN cse_corporation corp
	ON mb.corporation_uuid = corp.corporation_uuid
	WHERE ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id
	GROUP BY corp.corporation_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		if ($corporation_id!="") {
			$stmt->bindParam("corporation_id", $corporation_id);
		}
		$stmt->execute();
		$medsum = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($medsum);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getMedicalBillingsSummaryInfo($case_id) {
	return getMedicalBillingsSummary($case_id, true);
}
function getMedicalBillingsSummary($case_id, $blnReturn = false) {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT corp.corporation_id, corp.corporation_id id, corp.company_name, corp.phone, corp.employee_phone, corp.employee_email, corp.full_name,
	SUM(IFNULL(billed, 0)) billed, SUM(IFNULL(paid, 0)) paid, SUM(IFNULL(adjusted, 0)) adjusted,
	SUM(IFNULL(billed, 0) - IFNULL(paid, 0) + IFNULL(adjusted, 0)) balance

	FROM cse_corporation corp
    LEFT OUTER JOIN cse_medicalbilling mb
	ON corp.corporation_uuid = mb.corporation_uuid AND mb.deleted = 'N'
	INNER JOIN cse_case_corporation gcc
	ON corp.corporation_uuid = gcc.corporation_uuid AND attribute = 'medical_provider'
	INNER JOIN cse_case ccase
	ON gcc.case_uuid = ccase.case_uuid
		
	WHERE corp.customer_id = :customer_id
	AND ccase.case_id = :case_id
    AND corp.deleted = 'N'
    GROUP BY corp.corporation_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$billings = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		if (!$blnReturn) {
			echo json_encode($billings);
		} else {
			return $billings;
		}
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getMedicalBillingsLossSummary($case_id) {
	getMedicalBillings($case_id, "", true);
}
function getCorpMedicalBillings($case_id, $corporation_id) {
	getMedicalBillings($case_id, $corporation_id);
}
function getMedicalBillings($case_id, $corporation_id = "", $blnSummary = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
		
	$sql = "SELECT mb.*, 
	mb.medicalbilling_id id, mb.medicalbilling_uuid uuid, 
	usr.user_id, usr.user_name, usr.nickname,
	corp.corporation_id, corp.company_name  
	FROM cse_medicalbilling mb
	INNER JOIN cse_corporation corp
	ON mb.corporation_uuid = corp.corporation_uuid
	INNER JOIN cse_case_corporation gcc
	ON corp.corporation_uuid = gcc.corporation_uuid
	INNER JOIN cse_case ccase
	ON gcc.case_uuid = ccase.case_uuid
	LEFT OUTER JOIN ikase.cse_user usr
	ON mb.user_uuid = usr.user_uuid
		
	WHERE mb.customer_id = :customer_id
	AND mb.deleted = 'N'
	AND ccase.case_id = :case_id";
	if ($corporation_id!="") {
		$sql .= "
		AND corp.corporation_id = :corporation_id";
	}
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		if ($corporation_id!="") {
			$stmt->bindParam("corporation_id", $corporation_id);
		}
		$stmt->execute();
		$billings = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		if ($blnSummary) {
			//get the medical total
			$sql = "SELECT ccase.case_id, 
			SUM(chk.amount_due - chk.payment - ABS(chk.adjustment)) totals
			FROM cse_case ccase
			
			LEFT OUTER JOIN cse_case_check ccc
			ON ccase.case_uuid = ccc.case_uuid AND ccc.deleted = 'N'
			
			LEFT OUTER JOIN cse_check chk
			ON ccc.check_uuid = chk.check_uuid AND chk.deleted = 'N'

			WHERE ccase.case_id = :case_id
			AND ccase.customer_id = :customer_id
			AND chk.check_status != 'V'
			AND chk.ledger = 'OUT'
			";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("case_id", $case_id);
			$stmt->bindParam("customer_id", $customer_id);
			
			$stmt->execute();
			$costs = $stmt->fetchObject();
			
			$arrSummary = array();
			$billed_total = 0;
			$paid_total = 0;
			$adjusted_total = 0;
			$override_total = 0;
			foreach($billings as $billing) {
				$billed_total += $billing->billed;
				$paid_total += $billing->paid;
				$adjusted_total += $billing->adjusted;
				$override_total += $billing->override;
			}
			
			$balance = number_format($billed_total - $paid_total + $adjusted_total, 2);
			if ($balance > -0.1 && $balance < 0.1) {
				$balance = 0;
			}
			$arrSummary = array(
				"billed_total"		=>	$billed_total,
				"paid_total"		=>	$paid_total,
				"adjusted_total"	=>	$adjusted_total,
				"override_total"	=>	$override_total,
				"balance"			=>	$balance,
				"costs"				=>	$costs->totals
			);
			
			echo json_encode($arrSummary);
		} else  {
        	echo json_encode($billings);
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getMedicalBilling($id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
		
	$sql = "SELECT mb.*, 
	mb.medicalbilling_id id, mb.medicalbilling_uuid uuid, 
	usr.user_id, usr.user_name, usr.nickname,
	corp.corporation_id, corp.company_name, ccase.case_id  
	FROM cse_medicalbilling mb
	INNER JOIN cse_case_medicalbilling ccm
	ON mb.medicalbilling_uuid = ccm.medicalbilling_uuid
	INNER JOIN cse_case ccase
	ON ccm.case_uuid = ccase.case_uuid
	INNER JOIN cse_corporation corp
	ON mb.corporation_uuid = corp.corporation_uuid
	LEFT OUTER JOIN ikase.cse_user usr
	ON mb.user_uuid = usr.user_uuid
	WHERE mb.customer_id = :customer_id
	AND mb.deleted = 'N'
	AND mb.medicalbilling_id = :id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$billing = $stmt->fetchObject();
        echo json_encode($billing);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDeductions($case_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
		
	$sql = "SELECT deduct.*, 
	deduct.deduction_id id, deduct.deduction_uuid uuid
	FROM cse_deduction deduct
	INNER JOIN cse_case_deduction gcc
	ON deduct.deduction_uuid = gcc.deduction_uuid
	INNER JOIN cse_case ccase
	ON gcc.case_uuid = ccase.case_uuid
			
	WHERE deduct.customer_id = :customer_id
	AND deduct.deleted = 'N'
	AND ccase.case_id = :case_id";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$deducts = $stmt->fetchAll(PDO::FETCH_OBJ);
		echo json_encode($deducts);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTotalDeductions($case_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
		
	$sql = "SELECT SUM(deduct.amount) total_amount,
	SUM(deduct.payment) total_payment,
	SUM(deduct.adjustment) total_adjustment
	FROM cse_deduction deduct
	INNER JOIN cse_case_deduction gcc
	ON deduct.deduction_uuid = gcc.deduction_uuid
	INNER JOIN cse_case ccase
	ON gcc.case_uuid = ccase.case_uuid
			
	WHERE deduct.customer_id = :customer_id
	AND deduct.deleted = 'N'
	AND ccase.case_id = :case_id";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$deducts = $stmt->fetchObject();
		echo json_encode($deducts);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDeductionInfo($id) {
	return getDeduction($id, true);
}
function getDeduction($id, $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
		
	$sql = "SELECT deduct.*, 
	deduct.deduction_id id, deduct.deduction_uuid uuid, ccase.case_id  
	FROM cse_deduction deduct
	INNER JOIN cse_case_deduction ccm
	ON deduct.deduction_uuid = ccm.deduction_uuid
	INNER JOIN cse_case ccase
	ON ccm.case_uuid = ccase.case_uuid
	WHERE deduct.customer_id = :customer_id
	AND deduct.deleted = 'N'
	AND deduct.deduction_id = :id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$deduct = $stmt->fetchObject();
		
		if (!$blnReturn) {
        	echo json_encode($deduct);
		} else {
			return $deduct;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function saveDeduction() {
	session_write_close();
	
	$arrFields = array();
	$arrSet = array();
	$case_id = 0;
	$table_name = "";
	$table_id = passed_var("table_id", "post");
	$corporation_uuid = "";
	$case_uuid = "";
	$user_uuid = "";
	//$case_id = passed_var("case_id", "post");
	//die($table_id);
	//$table_id = 1;
	//die(print_r($_POST));
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="deduction_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		//die("before");
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
		//die("after");
	}	
	//$case_id = passed_var("case_id", "post");
	//die("case:" . $case_id);
	//die("table:" . $table_id);
	//die(print_r($arrFields));
	//die(print_r($arrSet));
	
	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
		//die($sql);
		try {
			DB::run($sql);
			$new_id = DB::lastInsertId();
			
			//attach to case
			if ($case_uuid!="") {
				$last_updated_date = date("Y-m-d H:i:s");
				$case_deduction_uuid = uniqid("KA", false);
				$attribute = "main";
				
				$sql = "INSERT INTO cse_case_deduction (`case_deduction_uuid`, `case_uuid`, `deduction_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_deduction_uuid . "', '" . $case_uuid . "', '" . $table_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				//echo $sql . "\r\n";
				$stmt = DB::run($sql);
			}
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			trackDeduction("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		//where
		$where_clause = "= '" . $table_id . "'";
		$where_clause = "`" . $table_name . "_id`" . $where_clause . "
		AND `customer_id` = " . $_SESSION['user_customer_id'];

		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET " . implode(",", $arrSet) . "
		WHERE " . $where_clause;
		
		//die(implode(",", $arrSet));
		
		//die($sql);
		
		try {
			$stmt = DB::run($sql);
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
			//track now	
			trackDeduction("update", $table_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
}
function deleteDeduction() {
	$id = passed_var("id", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "UPDATE `cse_deduction` 
			SET `deleted` = 'Y'
			WHERE `deduction_id` = :id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		echo json_encode(array("success"=>"deduction marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackDeduction("delete", $id);
}
function getBillings() {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
    $sql = "SELECT bill.* 
			FROM `cse_billing` bill 
			WHERE bill.deleted = 'N'
			AND bill.customer_id = :customer_id
			ORDER by bill.billing_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$billings = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($billings);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getFullBilling($case_id) {
    $sql = "SELECT cc.case_id caseid, bill.*, bill.billing_id id, bill.billing_uuid uuid
			FROM `cse_case` cc
			LEFT OUTER JOIN `cse_billing` bill 
			ON cc.case_id = bill.case_id AND bill.deleted = 'N'
			WHERE cc.case_id=:case_id
			AND cc.customer_id = " . $_SESSION['user_customer_id'] . "
			";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$billing = $stmt->fetchObject();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBilling($case_id, $action_id, $action_type) {
    $sql = "SELECT bill.*, bill.status billing_status, bill.billing_id id, bill.billing_uuid uuid, user_bill.*
			FROM `cse_billing` bill 
			INNER JOIN `cse_user` user_bill
			ON bill.timekeeper = user_bill.user_id
			WHERE bill.case_id=:case_id
			AND bill.action_id =:action_id 
			AND bill.action_type =:action_type 
			AND bill.customer_id = " . $_SESSION['user_customer_id'] . "
			AND bill.deleted = 'N'
			ORDER BY id DESC
			LIMIT 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("action_id", $action_id);
		$stmt->bindParam("action_type", $action_type);
		$stmt->execute();
		$billing = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($billing);
        } else {
            echo $_GET['callback'] . '(' . json_encode($billing) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addBilling() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "billing";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="table_uuid") {
			continue;
		}
		if ($fieldname=="billing_id") {
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
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . str_replace("'", "\'", $value) . "'";
	}	
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "INSERT INTO `cse_" . $table_name . "` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try {
		DB::run($sql);
		$new_id = DB::lastInsertId();
		
		echo json_encode(array("success"=>true, "id"=>$new_id));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function saveMedicalBilling() {
	session_write_close();
	
	$arrFields = array();
	$arrSet = array();
	$case_id = 0;
	$table_name = "";
	$table_id = passed_var("table_id", "post");
	$corporation_uuid = "";
	$case_uuid = "";
	$user_uuid = "";
	//$case_id = passed_var("case_id", "post");
	//die($table_id);
	//$table_id = 1;
	//die(print_r($_POST));
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
			continue;
		}
		if ($fieldname=="corporation_id") {
			if ($value!=-1) {
				$corporation = getCorporationInfo($value);
				//die(print_r($corporation));
				$corporation_uuid = $corporation->uuid;
			}
			$fieldname = "corporation_uuid";
			$value = $corporation_uuid;
		}
		if ($fieldname=="user_id") {
			if ($value!=-1) {
				$user = getUserInfo($value);
				$user_uuid = $user->uuid;
			}
			$fieldname = "user_uuid";
			$value = $user_uuid;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="finalized") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		//die("before");
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
		//die("after");
	}	
	//$case_id = passed_var("case_id", "post");
	//die("case:" . $case_id);
	//die("table:" . $table_id);
	//die(print_r($arrFields));
	//die(print_r($arrSet));
	
	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
		//die($sql);
		try {
			DB::run($sql);
			$new_id = DB::lastInsertId();
			
			//attach to case
			if ($case_uuid!="") {
				$last_updated_date = date("Y-m-d H:i:s");
				$case_medicalbilling_uuid = uniqid("KA", false);
				$attribute = "main";
				
				$sql = "INSERT INTO cse_case_medicalbilling (`case_medicalbilling_uuid`, `case_uuid`, `medicalbilling_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_medicalbilling_uuid . "', '" . $case_uuid . "', '" . $table_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				//echo $sql . "\r\n";
				$stmt = DB::run($sql);
			}
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			trackMedicalBilling("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		//nos if not present
		$arrYesNo = array("still_treating", "lien", "prior");
		foreach($arrYesNo as $fieldname) {
			if (!isset($_POST[$fieldname])) {
				$arrSet[] = "`" . $fieldname . "` = 'N'";
			}
		}
		//where
		$where_clause = "= '" . $table_id . "'";
		$where_clause = "`" . $table_name . "_id`" . $where_clause . "
		AND `customer_id` = " . $_SESSION['user_customer_id'];

		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET " . implode(",", $arrSet) . "
		WHERE " . $where_clause;
		
		//die(implode(",", $arrSet));
		
		if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
		//	die($sql);	
		}
		
		try {
			$stmt = DB::run($sql);
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
			//track now	
			trackMedicalBilling("update", $table_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
}
function deleteMedicalBilling() {
	$id = passed_var("id", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "UPDATE `cse_medicalbilling` 
			SET `deleted` = 'Y'
			WHERE `medicalbilling_id` = :id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		echo json_encode(array("success"=>"billing marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackMedicalBilling("delete", $id);
}
function addFullBilling() {
	session_write_close();
	$arrFields = array();
	$arrSet = array();
	$case_id = 0;
	$table_name = "billing";
	$table_id = passed_var("table_id", "post");
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="billing_id") {
			continue;
		}
		if ($fieldname=="billing_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		//die("before");
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
		//die("after");
	}	
	//$case_id = passed_var("case_id", "post");
	//die("case:" . $case_id);
	//die("table:" . $table_id);
	//die(print_r($arrFields));
	//die(print_r($arrSet));
	
	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ", `case_id`) 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ", '" . $case_id . "')";
		//die($sql);
		try {
			DB::run($sql);
			$new_id = DB::lastInsertId();
			
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			//trackPerson("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		
		//where
		$where_clause = "= '" . $table_id . "'";
		$where_clause = "`" . $table_name . "_id`" . $where_clause . "
		AND `customer_id` = " . $_SESSION['user_customer_id'];

		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET " . implode(",", $arrSet) . "
		WHERE " . $where_clause;
		
		//die(implode(",", $arrSet));
		
		//die($sql);
		
		try {
			$stmt = DB::run($sql);
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
			//track now	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
}
function updateBilling() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "billing";
	$table_id = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			continue;
		}
		if ($fieldname=="table_id") {
			$table_id = $value;
			continue;
		}
		if ($fieldname=="billing_id") {
			continue;
		}
		if ($fieldname=="table_uuid") {
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
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}	
	
	//where
	$where_clause = "= '" . $table_id . "'";
	$where_clause = "`" . $table_name . "_id`" . $where_clause . "
	AND `customer_id` = " . $_SESSION['user_customer_id'];
	
	//actual query
	$sql = "UPDATE `cse_" . $table_name . "`
	SET " . implode(",
	", $arrSet) . "
	WHERE " . $where_clause;
	
	die($sql);
	try {
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>true, "id"=>$table_id));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function trackMedicalBilling($operation, $medicalbilling_id) {
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "INSERT INTO `cse_medicalbilling_track`
	(`track_user_uuid`, `user_logon`, `operation`, `time_stamp`, `medicalbilling_id`, `medicalbilling_uuid`, `corporation_uuid`, `user_uuid`, `billed`, `paid`, `adjusted`, `balance`, `finalized`, `still_treating`, `prior`, `lien`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', :operation, '". date("Y-m-d H:i:s") . "', `medicalbilling_id`, `medicalbilling_uuid`, `corporation_uuid`, `user_uuid`, `billed`, `paid`, `adjusted`, `balance`, `finalized`, `still_treating`, `prior`, `lien`, `deleted`, `customer_id`
	FROM cse_medicalbilling
	WHERE 1
	AND medicalbilling_id = :medicalbilling_id
	AND customer_id = :customer_id
	LIMIT 0, 1";
	//echo $sql;
	

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("medicalbilling_id", $medicalbilling_id);
		$stmt->bindParam("operation", $operation);
		
		$stmt->execute();

        return $db->lastInsertId();
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		return false;
	}
}
function trackKInvoice($operation, $kinvoice_id) {
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "INSERT INTO `cse_kinvoice_track`
	(`user_uuid`,  `user_logon`,  `operation`,  `time_stamp`,  `kinvoice_id`,  `kinvoice_uuid`,  `parent_kinvoice_uuid`,  `kinvoice_date`,  `notification_date`,  `reminder_date`,  `paid_date`,  `start_date`,  `end_date`,  `kinvoice_number`,  `hourly_rate`,  `total`,  `payments`,  `customer_id`,  `deleted`,  `template`,  `template_name`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', :operation, '". date("Y-m-d H:i:s") . "', 
	`kinvoice_id`,  `kinvoice_uuid`,  `parent_kinvoice_uuid`,  `kinvoice_date`,  `notification_date`,  `reminder_date`,  `paid_date`,  `start_date`,  `end_date`,  `kinvoice_number`,  `hourly_rate`,  `total`,  `payments`,  `customer_id`,  `deleted`,  `template`,  `template_name`
	FROM cse_kinvoice
	WHERE 1
	AND kinvoice_id = :kinvoice_id
	AND customer_id = :customer_id
	LIMIT 0, 1";
	//echo $sql;
	

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("kinvoice_id", $kinvoice_id);
		$stmt->bindParam("operation", $operation);
		
		$stmt->execute();

        return $db->lastInsertId();
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		return false;
	}
}
function saveBankAccount() {
	session_write_close();
	
	$table_name = "account";
	$table_id = passed_var("table_id", "post");
	$account_info = passed_var("account_info", "post");
	$account_type = passed_var("account_type", "post");
	$account_name = passed_var("account_name", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	//die($table_id . " - table");
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
		
	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `account_name`, `customer_id`, `account_type`, `account_info`) 
			VALUES('" . $table_uuid . "', :account_name, :customer_id, :account_type, :account_info)";
		//die($sql);
		try {
			$db = getConnection();
			
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("account_name", $account_name);
			$stmt->bindParam("account_type", $account_type);
			$stmt->bindParam("account_info", $account_info);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$new_id = $db->lastInsertId();
			
			if ($account_type=="trust") {
				$setting_uuid = uniqid("AC", false);
				$sql = "
				DELETE FROM cse_setting 
				WHERE `category` = 'checks'
				AND `setting` = 'trust_account_required'
				AND customer_id = :customer_id;
				
				INSERT INTO cse_setting (setting_uuid, customer_id, category, setting, setting_value)
				VALUES (:setting_uuid, :customer_id, 'checks', 'trust_account_required', 'Y');";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("setting_uuid", $setting_uuid);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
			}
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			trackAccount("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		
		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET `account_name` = :account_name,
		`account_info` = :account_info
		WHERE account_id = :account_id
		AND customer_id = :customer_id";
		
		try {
			$db = getConnection();
			
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("account_id", $table_id);
			$stmt->bindParam("account_name", $account_name);
			$stmt->bindParam("account_info", $account_info);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
			//track now
			trackAccount("update", $table_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
}
function getKaseNoAttach($case_id, $account_type) {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$attribute = 'no_' . $account_type;
	
	$sql = "SELECT cca.* 
	FROM cse_case_account cca
	INNER JOIN cse_case ccase
	ON cca.case_uuid = ccase.case_uuid
	WHERE ccase.case_id = :case_id
	AND attribute = :attribute
	AND ccase.customer_id = :customer_id
	AND cca.deleted = 'N'";
	//die($sql);
	try {	
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("attribute", $attribute);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$no_trust = $stmt->fetchObject();
		
		
		
		$blnDetached = true;
		if (is_object($no_trust)) {
			$blnDetached = false;
		}
		echo json_encode(array("success"=>true, "detached"=>$blnDetached));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function attachBankAccount() {
	session_write_close();
	
	if ($_SERVER['REMOTE_ADDR']!='47.153.49.248') {
		//echo '{"success":false}'; 
		//return;
	}
	$customer_id = $_SESSION["user_customer_id"];
	$case_id = passed_var("case_id", "post");
	$account_type = passed_var("account_type", "post");
	$account_id = passed_var("account_id", "post");
	
	$attribute = $account_type;
	
	$kase = getKaseInfo($case_id);
	$case_uuid = $kase->uuid;
	$account = getBankAccountInfo($account_id);
	$account_uuid = $account->uuid;
	
	try {	
		//first clear out any previous attachment
		$sql = "UPDATE `cse_case_account`
		SET `deleted` = 'Y'
		WHERE `case_uuid` = :case_uuid
		AND `attribute` = :account_type
		AND `customer_id` = :customer_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->bindParam("account_type", $account_type);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$last_updated_date = date("Y-m-d H:i:s");
		$case_account_uuid = uniqid("KA", false);
		$attribute = $account_type;
		
		$sql = "INSERT INTO cse_case_account (`case_account_uuid`, `case_uuid`, `account_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_account_uuid . "', '" . $case_uuid . "', '" . $account_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>true, "message"=>$account_type . " attached"));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function detachBankAccount() {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$case_id = passed_var("case_id", "post");
	$account_type = passed_var("account_type", "post");
	
	$attribute = 'no_' . $account_type;
	$kase = getKaseInfo($case_id);
	$case_uuid = $kase->uuid;
	
	try {	
		//first clear out any previous attachment
		$sql = "UPDATE cse_case_account
		SET deleted = 'Y'
		WHERE case_uuid = :case_uuid
		AND attribute = :account_type
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->bindParam("account_type", $account_type);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$last_updated_date = date("Y-m-d H:i:s");
		$case_account_uuid = uniqid("KA", false);
		$attribute = 'no_' . $account_type;
		
		$sql = "INSERT INTO cse_case_account (`case_account_uuid`, `case_uuid`, `account_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_account_uuid . "', '" . $case_uuid . "', 'no_attach', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>true, "message"=>$account_type . " detached"));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function clearAttachBankAccount() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$case_id = passed_var("case_id", "post");
	$account_type = passed_var("account_type", "post");
	
	$attribute = 'no_' . $account_type;
	$kase = getKaseInfo($case_id);
	$case_uuid = $kase->uuid;
	$last_updated_date = date("Y-m-d H:i:s");
	$last_update_user = $_SESSION['user_id'];
	
	try {
		//clear any other association
		$sql = "UPDATE cse_case_account
		SET deleted = 'Y',
		last_updated_date = :last_updated_date,
		last_update_user = :last_update_user
		WHERE case_uuid = :case_uuid
		AND attribute = :attribute
		AND customer_id = :customer_id";
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->bindParam("attribute", $attribute);
		$stmt->bindParam("last_updated_date", $last_updated_date);
		$stmt->bindParam("last_update_user", $last_update_user);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "message"=>$account_type . " cleared"));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function getBankAccountsByType($account_type) {
	getBankAccounts($account_type);
}
function getBankAccounts($account_type = "", $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
    $sql = "SELECT acct.*, 
			acctrack.time_stamp account_create_date, acct.account_id id, acct.account_uuid uuid
			FROM `cse_account` acct
			INNER JOIN `cse_account_track` acctrack
			ON acct.account_id = acctrack.account_id AND acctrack.operation = 'insert'
			WHERE acct.deleted = 'N'";
			if ($account_type != "") {
				$sql .= "
				AND acct.account_type = :account_type";
			}
			$sql .= "
			AND acct.customer_id = :customer_id
			ORDER BY acct.account_id";
	if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($account_type != "") {
			$stmt->bindParam("account_type", $account_type);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		if ($account_type == "") {
			$accounts = $stmt->fetchAll(PDO::FETCH_OBJ);
		} else {
			$accounts = $stmt->fetchObject();
		}
		
		if (!$blnReturn) {
			echo json_encode($accounts);
		} else {
			return $accounts;
		}
		
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBankAccountKases($account_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT ccase.case_id id, ccase.case_type, 
	(IFNULL(account_ins.amounts, 0) - IFNULL(account_outs.amounts, 0)) balance,
	IF (ccase.case_name = '', IF (ccase.case_number = '', ccase.file_number, ccase.case_number), ccase.case_name) case_name
		
	FROM cse_case ccase
	
	INNER JOIN cse_case_account cca
	ON ccase.case_uuid = cca.case_uuid
	INNER JOIN cse_account acct
	ON cca.account_uuid = acct.account_uuid
	
	LEFT OUTER JOIN (
		SELECT ccase.case_id, SUM(receipts.amount_due) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cac
		ON acct.account_uuid = cac.account_uuid AND cac.deleted = 'N'
	
		INNER JOIN cse_check receipts
		ON cac.check_uuid = receipts.check_uuid AND receipts.ledger = 'IN'
	
		INNER JOIN cse_case_check ccc
		ON receipts.check_uuid = ccc.check_uuid
	
		INNER JOIN cse_case ccase
		ON ccc.case_uuid = ccase.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_id = :account_id
		AND receipts.deleted = 'N'
	
		GROUP BY ccase.case_id
	
	) account_ins
	ON ccase.case_id = account_ins.case_id
	
	LEFT OUTER JOIN (
		SELECT ccasew.case_id, SUM(withdraws.amount_due) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cacw
		ON acct.account_uuid = cacw.account_uuid AND cacw.deleted = 'N'
	
		INNER JOIN cse_check withdraws
		ON cacw.check_uuid = withdraws.check_uuid AND withdraws.ledger = 'OUT'
	
		INNER JOIN cse_case_check ccw
		ON withdraws.check_uuid = ccw.check_uuid AND ccw.deleted = 'N'
	
		INNER JOIN cse_case ccasew
		ON ccw.case_uuid = ccasew.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_id = :account_id
		AND withdraws.deleted = 'N'
	
		GROUP BY ccasew.case_id
	) account_outs
	ON ccase.case_id = account_outs.case_id
	
	WHERE 1
	AND acct.account_id = :account_id
	AND ccase.customer_id = :customer_id
	GROUP BY ccase.case_id
	ORDER BY IF (ccase.case_name = '', IF (ccase.case_number = '', ccase.file_number, ccase.case_number), ccase.case_name)";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("account_id", $account_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$account_kases = $stmt->fetchAll(PDO::FETCH_OBJ);

		echo json_encode($account_kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function displayAccountBalance($account_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT acc.*, 
IFNULL(adjustments.total_adjusted, 0) total_adjusted, IFNULL(interests.total_interest, 0) total_interest,
IFNULL(cleared_receipts.total_cleared_receipts, 0) total_cleared_receipts,
IFNULL(uncleared_receipts.total_uncleared_receipts, 0) total_uncleared_receipts,
IFNULL(cleared_disburs.total_cleared_disburs, 0) total_cleared_disburs,
IFNULL(uncleared_disburs.total_uncleared_disburs, 0) total_uncleared_disburs
FROM cse_account acc

LEFT OUTER JOIN (
	SELECT acc.account_uuid, SUM(chk.amount_due) amount_totals, SUM(chk.payment) payment_totals, SUM(ABS(chk.adjustment)) adjustment_totals,
	COUNT(DISTINCT ccase.case_id) case_count, SUM(chk.amount_due - chk.payment - ABS(chk.adjustment)) total_cleared_receipts
	FROM `cse_check` `chk` 
	INNER JOIN cse_case_check ccc
	ON chk.check_uuid = ccc.check_uuid
	INNER JOIN cse_case ccase
	ON ccc.case_uuid = ccase.case_uuid
    INNER JOIN cse_account_check cac
	ON chk.check_uuid = cac.check_uuid
	INNER JOIN cse_account acc
	ON cac.account_uuid = acc.account_uuid
	WHERE 1
    AND chk.ledger = 'IN'
	AND ccase.customer_id = :customer_id
	AND chk.check_status != 'V'
	AND chk.check_status = 'C'
    AND acc.account_id = :account_id
	GROUP BY acc.account_uuid
) cleared_receipts
ON acc.account_uuid = cleared_receipts.account_uuid


LEFT OUTER JOIN (
	SELECT acc.account_uuid, SUM(chk.amount_due) amount_totals, SUM(chk.payment) payment_totals, SUM(ABS(chk.adjustment)) adjustment_totals,
	COUNT(DISTINCT ccase.case_id) case_count, SUM(chk.amount_due - chk.payment - ABS(chk.adjustment)) total_uncleared_receipts
	FROM `cse_check` `chk` 
	INNER JOIN cse_case_check ccc
	ON chk.check_uuid = ccc.check_uuid
	INNER JOIN cse_case ccase
	ON ccc.case_uuid = ccase.case_uuid
    INNER JOIN cse_account_check cac
	ON chk.check_uuid = cac.check_uuid
	INNER JOIN cse_account acc
	ON cac.account_uuid = acc.account_uuid
	WHERE 1
    AND chk.ledger = 'IN'
	AND ccase.customer_id = :customer_id
	AND chk.check_status != 'V'
	AND chk.check_status != 'C'
    AND acc.account_id = :account_id
	GROUP BY acc.account_uuid
) uncleared_receipts
ON acc.account_uuid = uncleared_receipts.account_uuid


LEFT OUTER JOIN (
	SELECT acc.account_uuid, SUM(chk.amount_due) amount_totals, SUM(chk.payment) payment_totals, SUM(ABS(chk.adjustment)) adjustment_totals,
	COUNT(DISTINCT ccase.case_id) case_count, SUM(chk.amount_due - chk.payment - ABS(chk.adjustment)) total_cleared_disburs
	FROM `cse_check` `chk` 
	INNER JOIN cse_case_check ccc
	ON chk.check_uuid = ccc.check_uuid
	INNER JOIN cse_case ccase
	ON ccc.case_uuid = ccase.case_uuid
    INNER JOIN cse_account_check cac
	ON chk.check_uuid = cac.check_uuid
	INNER JOIN cse_account acc
	ON cac.account_uuid = acc.account_uuid
	WHERE 1
    AND chk.ledger = 'OUT'
	AND ccase.customer_id = :customer_id
	AND chk.check_status != 'V'
	AND chk.check_status = 'C'
    AND acc.account_id = :account_id
	GROUP BY acc.account_uuid
) cleared_disburs
ON acc.account_uuid = cleared_disburs.account_uuid


LEFT OUTER JOIN (
	SELECT acc.account_uuid, SUM(chk.amount_due) amount_totals, SUM(chk.payment) payment_totals, SUM(ABS(chk.adjustment)) adjustment_totals,
	COUNT(DISTINCT ccase.case_id) case_count, SUM(chk.amount_due - chk.payment - ABS(chk.adjustment)) total_uncleared_disburs
	FROM `cse_check` `chk` 
	INNER JOIN cse_case_check ccc
	ON chk.check_uuid = ccc.check_uuid
	INNER JOIN cse_case ccase
	ON ccc.case_uuid = ccase.case_uuid
    INNER JOIN cse_account_check cac
	ON chk.check_uuid = cac.check_uuid
	INNER JOIN cse_account acc
	ON cac.account_uuid = acc.account_uuid
	WHERE 1
    AND chk.ledger = 'OUT'
	AND ccase.customer_id = :customer_id
	AND chk.check_status != 'V'
	AND chk.check_status != 'C'
    AND acc.account_id = :account_id
	GROUP BY acc.account_uuid
) uncleared_disburs
ON acc.account_uuid = uncleared_disburs.account_uuid

LEFT OUTER JOIN (
	SELECT 
		caa.account_uuid, 'Adjustments' adjust_action, SUM(amount) total_adjusted
	FROM
		cse_adjustment ca
			INNER JOIN
		cse_account_adjustment caa ON ca.adjustment_uuid = caa.adjustment_uuid
	WHERE
		adjustment_type = 'A'
	GROUP BY caa.account_uuid
) adjustments
ON acc.account_uuid = adjustments.account_uuid

LEFT OUTER JOIN (
	SELECT 
		caa.account_uuid, 'Interests' adjust_action, SUM(amount) total_interest
	FROM
		cse_adjustment ca
			INNER JOIN
		cse_account_adjustment caa ON ca.adjustment_uuid = caa.adjustment_uuid
	WHERE
		adjustment_type = 'I'
	GROUP BY caa.account_uuid
) interests
ON acc.account_uuid = interests.account_uuid

WHERE acc.account_id = :account_id
AND acc.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("account_id", $account_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$display_account = $stmt->fetchObject();

		echo json_encode($display_account);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	
}
function getFirmAccountBalance($account_type) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT acct.*, 
	acctrack.time_stamp account_create_date, 
	IFNULL(account_ins.amounts, 0) - IFNULL(adjustments.adjusted, 0) - IFNULL(account_outs.amounts, 0) balance,
	IFNULL(account_ins.amounts, 0) deposits,
	IFNULL(account_outs.amounts, 0) withdrawals,
	IFNULL(adjustments.adjusted, 0) adjustments,
	IFNULL(account_pendings.pendings, 0) pendings,
	IFNULL(account_prebills.pre_bills, 0) pre_bills,
	IFNULL(account_transfers.transfers, 0) transfers,
	/*
	SUM((IFNULL(account_ins.amounts, 0) + IFNULL(adjustments.adjusted, 0) - IFNULL(account_outs.amounts, 0))) balance,
	SUM(IFNULL(account_pendings.pendings, 0)) pendings,
	SUM(IFNULL(account_prebills.pre_bills, 0)) pre_bills,
	SUM(IFNULL(account_transfers.transfers, 0)) transfers,
	*/
    acct.account_id id
	
	FROM cse_account acct
	
	INNER JOIN `cse_account_track` acctrack
	ON acct.account_id = acctrack.account_id AND acctrack.operation = 'insert'
	/*
    LEFT OUTER JOIN cse_case_account cca
	ON acct.account_uuid = cca.account_uuid AND cca.deleted = 'N'
    
    LEFT OUTER JOIN cse_case ccase
    ON cca.case_uuid = ccase.case_uuid
    */
	LEFT OUTER JOIN (
		SELECT account_uuid, SUM(amount) adjusted
		FROM cse_adjustment adj
		INNER JOIN cse_account_adjustment caa
		ON adj.adjustment_uuid = caa.adjustment_uuid
		WHERE adj.deleted = 'N'
		AND adj.customer_id = :customer_id
		GROUP BY caa.account_uuid
	) adjustments
	ON acct.account_uuid = adjustments.account_uuid
	
	LEFT OUTER JOIN (
		SELECT acct.account_uuid, SUM(receipts.amount_due) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cac
		ON acct.account_uuid = cac.account_uuid AND cac.deleted = 'N'
	
		INNER JOIN cse_check receipts
		ON cac.check_uuid = receipts.check_uuid AND receipts.ledger = 'IN'
	
		INNER JOIN cse_case_check ccc
		ON receipts.check_uuid = ccc.check_uuid
	
		INNER JOIN cse_case ccase
		ON ccc.case_uuid = ccase.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_type = :account_type
		AND receipts.deleted = 'N'
		
		GROUP BY acct.account_uuid
	
	) account_ins
	ON acct.account_uuid = account_ins.account_uuid
	
	LEFT OUTER JOIN (
		SELECT acct.account_uuid, SUM(withdraws.amount_due) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cacw
		ON acct.account_uuid = cacw.account_uuid AND cacw.deleted = 'N'
	
		INNER JOIN cse_check withdraws
		ON cacw.check_uuid = withdraws.check_uuid AND withdraws.ledger = 'OUT'
	
		INNER JOIN cse_case_check ccw
		ON withdraws.check_uuid = ccw.check_uuid AND ccw.deleted = 'N'
	
		INNER JOIN cse_case ccase
		ON ccw.case_uuid = ccase.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_type = :account_type
		AND withdraws.deleted = 'N'
		
		
		GROUP BY acct.account_uuid
	) account_outs
	ON acct.account_uuid = account_outs.account_uuid
	
	LEFT OUTER JOIN (
		SELECT acct.account_uuid, SUM(inv.total) pendings
		FROM cse_case ccase
		
		INNER JOIN cse_case_account cca
		ON ccase.case_uuid = cca.case_uuid AND cca.attribute = :account_type AND cca.deleted = 'N'
		
        INNER JOIN cse_account acct
        ON cca.account_uuid = acct.account_uuid
		
        INNER JOIN cse_case_kinvoice cck
		ON ccase.case_uuid = cck.case_uuid AND cck.deleted = 'N'
		
		INNER JOIN cse_kinvoice inv
		ON cck.kinvoice_uuid = inv.kinvoice_uuid
		
		INNER JOIN cse_account_kinvoice cak
		ON inv.kinvoice_uuid = cak.kinvoice_uuid
		
		WHERE 1
		AND inv.deleted = 'N'
		AND inv.kinvoice_type = 'I'
		AND inv.fund_transfer = 'P'
		AND acct.account_type = :account_type
		AND ccase.customer_id = :customer_id
		GROUP BY acct.account_uuid
		
	) account_pendings
	ON acct.account_uuid = account_pendings.account_uuid
	
	LEFT OUTER JOIN (
		SELECT acct.account_uuid, SUM(inv.total) transfers
		FROM cse_case ccase
		
		INNER JOIN cse_case_account cca
		ON ccase.case_uuid = cca.case_uuid AND cca.attribute = :account_type AND cca.deleted = 'N'
		
        INNER JOIN cse_account acct
        ON cca.account_uuid = acct.account_uuid
		
        INNER JOIN cse_account_check cac
        ON cca.account_uuid = cac.account_uuid AND cac.deleted = 'N'
        
		INNER JOIN cse_case_kinvoice cck
		ON ccase.case_uuid = cck.case_uuid AND cck.deleted = 'N'
		
		INNER JOIN cse_kinvoice inv
		ON cck.kinvoice_uuid = inv.kinvoice_uuid
		
		INNER JOIN cse_kinvoice_check ckc
        ON inv.kinvoice_uuid = ckc.kinvoice_uuid AND cac.check_uuid = ckc.check_uuid AND ckc.deleted = 'N'
		
		INNER JOIN cse_account_kinvoice cak
		ON inv.kinvoice_uuid = cak.kinvoice_uuid
		
		WHERE 1
		AND inv.deleted = 'N'
		AND inv.kinvoice_type = 'I'
		AND inv.fund_transfer = 'C'
		AND acct.account_type = :account_type
		AND ccase.customer_id = :customer_id
		GROUP BY acct.account_uuid
		
	) account_transfers
	ON acct.account_uuid = account_transfers.account_uuid
	
	LEFT OUTER JOIN (
		SELECT acct.account_uuid, SUM(inv.total) pre_bills
		FROM cse_case ccase
		
		INNER JOIN cse_case_account cca
		ON ccase.case_uuid = cca.case_uuid AND cca.attribute = :account_type AND cca.deleted = 'N'
		
        INNER JOIN cse_account acct
        ON cca.account_uuid = acct.account_uuid
		
		INNER JOIN cse_case_kinvoice cck
		ON ccase.case_uuid = cck.case_uuid AND cck.deleted = 'N'
		
		INNER JOIN cse_kinvoice inv
		ON cck.kinvoice_uuid = inv.kinvoice_uuid
		
		INNER JOIN cse_account_kinvoice cak
		ON inv.kinvoice_uuid = cak.kinvoice_uuid
		
		WHERE 1
		AND inv.deleted = 'N'
		AND inv.kinvoice_type = 'P'
		AND inv.fund_transfer = 'P'
		AND acct.account_type = :account_type
		AND ccase.customer_id = :customer_id
		GROUP BY acct.account_uuid
	) account_prebills
	ON acct.account_uuid = account_prebills.account_uuid
	
	WHERE 1
	AND acct.deleted = 'N'
	AND acct.customer_id = :customer_id
    AND acct.account_type = :account_type
    
    #GROUP BY acct.account_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("account_type", $account_type);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$firm_accounts = $stmt->fetchAll(PDO::FETCH_OBJ);

		echo json_encode($firm_accounts);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAllKasesAccountBalance($account_id) {
	//getKaseAccountBalance(-1,$account_type);
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT 
	acct.account_id, IFNULL(ins.amounts, 0) deposits, IFNULL(outs.amounts, 0) withdrawals, 
	(IFNULL(ins.amounts, 0) - IFNULL(outs.amounts, 0) + IFNULL(total_adjusted, 0) + IFNULL(total_interest, 0)) balance,
	IFNULL(pendings.amounts, 0) pendings,
    IFNULL(total_adjusted, 0) adjusteds,
    IFNULL(total_interest, 0) interest
	
	FROM cse_account acct
	
	LEFT OUTER JOIN (	
		SELECT 'A' operation, acct.account_id, SUM(IFNULL(amount, 0)) total_adjusted
		FROM cse_adjustment adj
		INNER JOIN cse_account_adjustment aadj
		ON adj.adjustment_uuid = aadj.adjustment_uuid
		INNER JOIN cse_account acct
		ON aadj.account_uuid = acct.account_uuid
		WHERE 1
		AND acct.account_id = :account_id
        AND acct.customer_id = :customer_id
		AND adj.adjustment_type = 'A'
		GROUP BY acct.account_id
	)
	adjusts
	ON acct.account_id = adjusts.account_id
    
	LEFT OUTER JOIN (	
		SELECT 'I' operation, acct.account_id, SUM(IFNULL(amount, 0)) total_interest
		FROM cse_adjustment adj
		INNER JOIN cse_account_adjustment aadj
		ON adj.adjustment_uuid = aadj.adjustment_uuid
		INNER JOIN cse_account acct
		ON aadj.account_uuid = acct.account_uuid
		WHERE 1
		AND acct.account_id = :account_id
        AND acct.customer_id = :customer_id
		AND adj.adjustment_type = 'I'
		GROUP BY acct.account_id
	)
	interests
	ON acct.account_id = interests.account_id
	
	LEFT OUTER JOIN (
		SELECT receipts.ledger ledger, acct.account_id, SUM(receipts.amount_due) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cac
		ON acct.account_uuid = cac.account_uuid AND cac.deleted = 'N'
	
		INNER JOIN cse_check receipts
		ON cac.check_uuid = receipts.check_uuid AND receipts.ledger = 'IN'
		
		INNER JOIN (
			SELECT check_id 
			FROM cse_check_track cct
			WHERE operation = 'insert'
		) cct
		ON `receipts`.check_id = cct.check_id
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_id = :account_id
		AND receipts.deleted = 'N' 
		GROUP BY acct.account_id
	) ins
	ON acct.account_id = ins.account_id
	
	LEFT OUTER JOIN (
		SELECT withdraws.ledger ledger, acct.account_id, SUM(withdraws.amount_due) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cacw
		ON acct.account_uuid = cacw.account_uuid AND cacw.deleted = 'N'
	
		INNER JOIN cse_check withdraws
		ON cacw.check_uuid = withdraws.check_uuid AND withdraws.ledger = 'OUT'
		
		INNER JOIN (
			SELECT check_id 
			FROM cse_check_track cct
			WHERE operation = 'insert'
		) cct
		ON `withdraws`.check_id = cct.check_id
		
		INNER JOIN cse_case_check ccw
		ON withdraws.check_uuid = ccw.check_uuid AND ccw.deleted = 'N'
	
		INNER JOIN cse_case ccasew
		ON ccw.case_uuid = ccasew.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_id = :account_id
		AND withdraws.deleted = 'N'
		GROUP BY acct.account_id
	) outs
	ON acct.account_id = outs.account_id
	
	LEFT OUTER JOIN (
		SELECT '" . $account_id . "' account_id, SUM(cr.amount) amounts
	 
		FROM cse_checkrequest cr
		
		INNER JOIN cse_case_checkrequest case_check
		ON cr.checkrequest_uuid = case_check.checkrequest_uuid AND case_check.deleted = 'N'
		INNER JOIN cse_case ccase
		ON case_check.case_uuid = ccase.case_uuid
		
		LEFT OUTER JOIN cse_account_checkrequest apc
		ON cr.checkrequest_uuid = apc.checkrequest_uuid AND apc.deleted = 'N'
		LEFT OUTER JOIN cse_account acct
		ON apc.account_uuid = acct.account_uuid
		
		INNER JOIN ikase.cse_user usr
		ON cr.requested_by = usr.user_uuid
		
		LEFT OUTER JOIN ikase.cse_user reviewer
		ON cr.reviewed_by = reviewer.user_uuid
		
		WHERE 1
		AND cr.deleted = 'N'
		AND cr.customer_id = :customer_id
		AND cr.approved = 'P'
		AND INSTR(cr.checkrequest_uuid, 'KS') > 0
		AND acct.account_id IS NULL
	
	) pendings
	ON acct.account_id = pendings.account_id

	WHERE acct.customer_id = :customer_id
	AND acct.deleted = 'N'
	AND acct.account_id = :account_id";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("account_id", $account_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$account = $stmt->fetchObject();

		echo json_encode($account);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}

}
function getKaseAccountBalance($case_id,$account_type) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
    $sql = "SELECT (IFNULL(account_ins.amounts, 0) - IFNULL(account_outs.amounts, 0)) balance,
	IFNULL(account_pendings.pendings, 0) pendings,
	IFNULL(account_prebills.pre_bills, 0) pre_bills,
	IFNULL(account_transfers.transfers, 0) transfers, 
	IFNULL(billable, 0) billable, IFNULL(invoiced, 0) invoiced
	FROM cse_case ccase
	
	LEFT OUTER JOIN (		
		SELECT DISTINCT ccase.case_id,  SUM(( act.hours * IFNULL(user.rate, 0)) + (IFNULL(act.billing_rate, 0) * act.billing_amount)) billable
		FROM cse_case ccase 
		INNER JOIN  `cse_case_activity` cca
		ON ccase.case_uuid = cca.case_uuid
		INNER JOIN cse_activity act
		ON  `cca`.`activity_uuid` = act.`activity_uuid`
		LEFT OUTER JOIN  (
		 SELECT itm.activity_uuid
		 FROM cse_kinvoiceitem itm
		 INNER JOIN cse_kinvoice inv
		 ON itm.kinvoice_uuid = inv.kinvoice_uuid
		 INNER JOIN cse_case_kinvoice cck
         ON inv.kinvoice_uuid = cck.kinvoice_uuid
         INNER JOIN cse_case ccase
         ON cck.case_uuid = ccase.case_uuid
		 
		 WHERE inv.deleted = 'N'";
		 if ($case_id != -1) {
			 $sql .= "
			 AND ccase.case_id = :case_id";
		 }
		 $sql .= "
		 AND itm.activity_uuid != ''
		 AND inv.customer_id = :customer_id
		) ck
		ON  `act`.`activity_uuid` = `ck`.`activity_uuid`
	
		LEFT OUTER JOIN `ikase`.`cse_user` user
		ON act.activity_user_id = user.user_id
			
		WHERE 1";
		if ($case_id != -1) {
			 $sql .= "
			 AND ccase.case_id = :case_id";
		}
		$sql .= " 
		AND `ck`.`activity_uuid` IS NULL
		AND act.deleted = 'N'
		AND ccase.deleted = 'N'
		AND ccase.customer_id = :customer_id
		GROUP BY ccase.case_id
	) billables
	ON ccase.case_id = billables.case_id
	
	LEFT OUTER JOIN (		
		SELECT DISTINCT ccase.case_id,  SUM(( act.hours * IFNULL(user.rate, 0)) + (IFNULL(act.billing_rate, 0) * act.billing_amount)) invoiced
		FROM cse_case ccase 
		INNER JOIN  `cse_case_activity` cca
		ON ccase.case_uuid = cca.case_uuid
		INNER JOIN cse_activity act
		ON  `cca`.`activity_uuid` = act.`activity_uuid`
		
		LEFT OUTER JOIN  (
		 SELECT itm.activity_uuid
		 FROM cse_kinvoiceitem itm
		 INNER JOIN cse_kinvoice inv
		 ON itm.kinvoice_uuid = inv.kinvoice_uuid
		 INNER JOIN cse_case_kinvoice cck
         ON inv.kinvoice_uuid = cck.kinvoice_uuid
         INNER JOIN cse_case ccase
         ON cck.case_uuid = ccase.case_uuid
		 
		 WHERE inv.deleted = 'N'";
		 if ($case_id != -1) {
			 $sql .= "
			 AND ccase.case_id = :case_id";
		 }
		 $sql .= "
		 AND itm.activity_uuid != ''
		 AND inv.customer_id = :customer_id
		) ck
		ON  `act`.`activity_uuid` = `ck`.`activity_uuid`
	
		LEFT OUTER JOIN `ikase`.`cse_user` user
		ON act.activity_user_id = user.user_id
			
		WHERE 1";
		if ($case_id != -1) {
			 $sql .= "
			 AND ccase.case_id = :case_id";
		}
		$sql .= " 
		AND `ck`.`activity_uuid` IS NOT NULL
		AND act.deleted = 'N'
		AND ccase.deleted = 'N'
		AND ccase.customer_id = :customer_id
		GROUP BY ccase.case_id
	) invoiceds
	ON ccase.case_id = invoiceds.case_id
	
	LEFT OUTER JOIN (
		SELECT ccase.case_id, SUM(receipts.amount_due) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cac
		ON acct.account_uuid = cac.account_uuid AND cac.deleted = 'N'
	
		INNER JOIN cse_check receipts
		ON cac.check_uuid = receipts.check_uuid AND receipts.ledger = 'IN'
	
		INNER JOIN cse_case_check ccc
		ON receipts.check_uuid = ccc.check_uuid
	
		INNER JOIN cse_case ccase
		ON ccc.case_uuid = ccase.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_type = :account_type
		AND receipts.deleted = 'N'";
		if ($case_id != -1) {
			 $sql .= "
			 AND ccase.case_id = :case_id";
		}
		$sql .= " 
		GROUP BY ccase.case_id
	
	) account_ins
	ON ccase.case_id = account_ins.case_id
	
	LEFT OUTER JOIN (
		SELECT ccasew.case_id, SUM(withdraws.amount_due) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cacw
		ON acct.account_uuid = cacw.account_uuid AND cacw.deleted = 'N'
	
		INNER JOIN cse_check withdraws
		ON cacw.check_uuid = withdraws.check_uuid AND withdraws.ledger = 'OUT'
	
		INNER JOIN cse_case_check ccw
		ON withdraws.check_uuid = ccw.check_uuid AND ccw.deleted = 'N'
	
		INNER JOIN cse_case ccasew
		ON ccw.case_uuid = ccasew.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_type = :account_type
		AND withdraws.deleted = 'N'";
		if ($case_id != -1) {
			 $sql .= "
			 AND ccasew.case_id = :case_id";
		}
		$sql .= "
		GROUP BY ccasew.case_id
	) account_outs
	ON ccase.case_id = account_outs.case_id
	
	LEFT OUTER JOIN (
		SELECT ccase.case_id, SUM(inv.total) pendings
		FROM cse_case ccase
		
		INNER JOIN cse_case_account cca
		ON ccase.case_uuid = cca.case_uuid AND cca.attribute = :account_type AND cca.deleted = 'N'
		
        INNER JOIN cse_account acct
        ON cca.account_uuid = acct.account_uuid
		
        INNER JOIN cse_case_kinvoice cck
		ON ccase.case_uuid = cck.case_uuid AND cck.deleted = 'N'
		
		INNER JOIN cse_kinvoice inv
		ON cck.kinvoice_uuid = inv.kinvoice_uuid
		
		INNER JOIN cse_account_kinvoice cak
		ON inv.kinvoice_uuid = cak.kinvoice_uuid
		
		WHERE 1
		AND inv.deleted = 'N'
		AND inv.kinvoice_type = 'I'
		AND inv.fund_transfer = 'P'
		AND acct.account_type = :account_type
		AND ccase.customer_id = :customer_id";
		if ($case_id != -1) {
			 $sql .= "
			 AND ccase.case_id = :case_id";
		}
		$sql .= "
	) account_pendings
	ON ccase.case_id = account_pendings.case_id
	
	LEFT OUTER JOIN (
		SELECT ccase.case_id, SUM(inv.total) transfers
		FROM cse_case ccase
		
		INNER JOIN cse_case_account cca
		ON ccase.case_uuid = cca.case_uuid AND cca.attribute = :account_type AND cca.deleted = 'N'
		
        INNER JOIN cse_account acct
        ON cca.account_uuid = acct.account_uuid
		
        INNER JOIN cse_account_check cac
        ON cca.account_uuid = cac.account_uuid AND cac.deleted = 'N'
        
		INNER JOIN cse_case_kinvoice cck
		ON ccase.case_uuid = cck.case_uuid AND cck.deleted = 'N'
		
		INNER JOIN cse_kinvoice inv
		ON cck.kinvoice_uuid = inv.kinvoice_uuid
		
		INNER JOIN cse_kinvoice_check ckc
        ON inv.kinvoice_uuid = ckc.kinvoice_uuid AND cac.check_uuid = ckc.check_uuid AND ckc.deleted = 'N'
		
		INNER JOIN cse_account_kinvoice cak
		ON inv.kinvoice_uuid = cak.kinvoice_uuid
		
		WHERE 1
		AND inv.deleted = 'N'
		AND inv.kinvoice_type = 'I'
		AND inv.fund_transfer = 'C'
		AND acct.account_type = :account_type
		AND ccase.customer_id = :customer_id";
		if ($case_id != -1) {
			 $sql .= "
			 AND ccase.case_id = :case_id";
		}
		$sql .= " 
	) account_transfers
	ON ccase.case_id = account_transfers.case_id
	
	LEFT OUTER JOIN (
		SELECT ccase.case_id, SUM(inv.total) pre_bills
		FROM cse_case ccase
		
		INNER JOIN cse_case_account cca
		ON ccase.case_uuid = cca.case_uuid AND cca.attribute = :account_type AND cca.deleted = 'N'
		
        INNER JOIN cse_account acct
        ON cca.account_uuid = acct.account_uuid
		
		INNER JOIN cse_case_kinvoice cck
		ON ccase.case_uuid = cck.case_uuid AND cck.deleted = 'N'
		
		INNER JOIN cse_kinvoice inv
		ON cck.kinvoice_uuid = inv.kinvoice_uuid
		
		INNER JOIN cse_account_kinvoice cak
		ON inv.kinvoice_uuid = cak.kinvoice_uuid
		
		WHERE 1
		AND inv.deleted = 'N'
		AND inv.kinvoice_type = 'P'
		AND inv.fund_transfer = 'P'
		AND acct.account_type = :account_type
		AND ccase.customer_id = :customer_id";
		if ($case_id != -1) {
			 $sql .= "
			 AND ccase.case_id = :case_id";
		}
		$sql .= " 
	) account_prebills
	ON ccase.case_id = account_prebills.case_id
	
	WHERE 1";
	if ($case_id != -1) {
		 $sql .= "
		 AND ccase.case_id = :case_id";
	}
	$sql .= " 
	AND ccase.customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("account_type", $account_type);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$kase_account = $stmt->fetchObject();
		
		if (!is_object($kase_account)) {
			$kase_account = new stdClass();
			$kase_account->balance = 0;
			$kase_account->billable = 0;
			$kase_account->pendings = 0;
			$kase_account->invoiced = 0;
			$kase_account->transfers = 0;
			$kase_account->invoiced = 0;
		}
		echo json_encode(array("success"=>true, "balance"=>$kase_account->balance, "billable"=>$kase_account->billable, "invoiced"=>$kase_account->invoiced, "pendings"=>$kase_account->pendings, "pres"=>$kase_account->invoiced, "transfers"=>$kase_account->transfers));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseAccountByType($case_id, $account_type) {
	getBankAccount("", $case_id, $account_type);
}
function getKaseAccount($case_id, $account_type = "") {
	getBankAccount("", $case_id, $account_type);
}
function getBankAccountInfo($id) {
	return getBankAccount($id, "", "", true);
}
function getBankAccount($id, $case_id = "", $account_type = "", $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
    $sql = "SELECT acct.*, 
		acctrack.time_stamp account_create_date, acct.account_id id, acct.account_uuid uuid
		FROM `cse_account` acct
		INNER JOIN `cse_account_track` acctrack
		ON acct.account_id = acctrack.account_id AND acctrack.operation = 'insert'";
		
	if ($case_id!="") {
		$sql .= "
		INNER JOIN cse_case_account cca
		ON acct.account_uuid = cca.account_uuid AND cca.deleted = 'N'
		INNER JOIN cse_case ccase
		ON cca.case_uuid = ccase.case_uuid";
	}
	
	$sql .= "
		WHERE 1";
	
	if ($id!="") {
		$sql .= "		
		AND acct.account_id=:id";
	}
	if ($case_id!="") {
		$sql .= "
		AND ccase.case_id=:case_id";
	}
	if ($account_type!="") {
		$sql .= "
		AND acct.account_type=:account_type";
	}
	$sql .= "
		AND acct.customer_id = :customer_id
		AND acct.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($id!="") {
			$stmt->bindParam("id", $id);
		}
		if ($case_id!="") {
			$stmt->bindParam("case_id", $case_id);
		}
		if ($account_type!="") {
			$stmt->bindParam("account_type", $account_type);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$account = $stmt->fetchObject();

		if ($blnReturn) {
			if (!is_object($account)) {
				$account = new stdClass();
				$account->id = "";
				$account->uuid = "";
			}
			return $account;
		} else {
			if (!is_object($account)) {
				//is even associated via a trust deposit
				if ($account_type=="trust" && $case_id!="") {
					$kase = getKaseInfo($case_id);
					$case_uuid = $kase->uuid;
											
					$sql = "SELECT DISTINCT DISTINCT acct.*, 
					acctrack.time_stamp account_create_date, acct.account_id id, acct.account_uuid uuid
					FROM cse_check chk
					INNER JOIN cse_account_check cchk
					ON chk.check_uuid = cchk.check_uuid
					INNER JOIN cse_case_check casechk
					ON chk.check_uuid = casechk.check_uuid
					
					INNER JOIN cse_account acct
					ON cchk.account_uuid = acct.account_uuid
					
					INNER JOIN `cse_account_track` acctrack
					ON acct.account_id = acctrack.account_id AND acctrack.operation = 'insert'

					INNER JOIN cse_case ccase
					ON casechk.case_uuid = ccase.case_uuid
					
					WHERE ledger = 'IN'
					AND acct.account_type = 'trust'
					AND ccase.case_id = :case_id
					AND ccase.customer_id = :customer_id
					AND acct.customer_id = :customer_id
					AND acct.deleted = 'N'";
					
					//die($sql);
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("case_id", $case_id);
					
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					$account = $stmt->fetchObject();
					
					if (is_object($account)) {
						$account_uuid = $account->uuid;
						
						//attach it to the case, accounting purposes
						$last_updated_date = date("Y-m-d H:i:s");
						$case_account_uuid = uniqid("KA", false);
						$attribute = $account_type;
						
						$sql = "INSERT INTO cse_case_account (`case_account_uuid`, `case_uuid`, `account_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
						VALUES ('" . $case_account_uuid . "', '" . $case_uuid . "', '" . $account_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
						
						$stmt = DB::run($sql);
					} else {
						//maybe just a settlement
						$sql = "SELECT sett.settlementsheet_id
						FROM cse_settlementsheet sett
						INNER JOIN cse_injury_settlement cis
						ON sett.settlementsheet_uuid = cis.settlement_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
						INNER JOIN cse_case_injury cci
						ON cis.injury_uuid = cci.injury_uuid
						INNER JOIN cse_case ccase
						ON cci.case_uuid = ccase.case_uuid
						INNER JOIN cse_injury inj
						ON cci.injury_uuid = inj.injury_uuid
						WHERE ccase.case_id = :case_id
						AND ccase.customer_id = :customer_id";
						
						$db = getConnection();
						$stmt = $db->prepare($sql);  
						$stmt->bindParam("case_id", $case_id);
						$stmt->bindParam("customer_id", $customer_id);
						$stmt->execute();
						$settlement = $stmt->fetchObject();
						
						if (is_object($settlement)) {
							//get the trust account
							$account = getBankAccounts("trust", true);
							
							if (is_object($account)) {
								$account_uuid = $account->uuid;
								
								//attach it to the case, accounting purposes
								$last_updated_date = date("Y-m-d H:i:s");
								$case_account_uuid = uniqid("KA", false);
								$attribute = $account_type;
								
								$sql = "INSERT INTO cse_case_account (`case_account_uuid`, `case_uuid`, `account_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
								VALUES ('" . $case_account_uuid . "', '" . $case_uuid . "', '" . $account_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
								
								$stmt = DB::run($sql);
							}
						}
					}
				}
			}
        	echo json_encode($account);
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function setStartingAmount() {
	$id = passed_var("id", "post");
	$starting_amount = passed_var("starting_amount", "post");
	$statement_date = passed_var("statement_date", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "UPDATE `cse_account` 
			SET `starting_statement_date` = :statement_date,
			`starting_amount` = :starting_amount
			WHERE `account_id`= :id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("starting_amount", $starting_amount);
		$stmt->bindParam("statement_date", $statement_date);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		echo json_encode(array("success"=>"account starting amount set to " . $starting_amount));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackAccount("starting", $id);
}
function deleteBankAccount($account_id) {
	$id = passed_var("id", "post");
	$sql = "UPDATE `cse_account` 
			SET `deleted` = 'Y'
			WHERE `account_id`=:id
			AND `cse_account`.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		echo json_encode(array("success"=>"account marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackAccount("delete", $id);
}
function trackAccount($operation, $account_id) {
	$sql = "INSERT INTO cse_account_track (`user_uuid`, `user_logon`, `operation`, `account_id`, `account_uuid`, `account_balance`, `starting_amount`, `starting_statement_date`,
	`account_type`, `account_info`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `account_id`, `account_uuid`, `account_balance`, `starting_amount`, `starting_statement_date`, 
	`account_type`, `account_info`, `customer_id`, `deleted`
	FROM cse_account
	WHERE 1
	AND account_id = " . $account_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function trackAdjustment($operation, $adjustment_id) {
	$sql = "INSERT INTO cse_adjustment_track (`user_uuid`, `user_logon`, `operation`, `adjustment_id`,  `adjustment_uuid`,  `adjustment_date`,  `amount`,  `adjustment_type`,  `description`,  `deleted`,  `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `adjustment_id`,  `adjustment_uuid`,  `adjustment_date`,  `amount`,  `adjustment_type`,  `description`,  `deleted`,  `customer_id`
	FROM cse_adjustment
	WHERE 1
	AND adjustment_id = " . $adjustment_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function trackDeduction($operation, $deduction_id) {
	$sql = "INSERT INTO cse_deduction_track ( `user_uuid`, `user_logon`, `operation`, `time_stamp_track`, `deduction_id`, `deduction_uuid`, `deduction_date`, `tracking_number`, `deduction_description`, `amount`, `payment`, `adjustment`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', '". date("Y-m-d H:i:s") . "', `deduction_id`, `deduction_uuid`, `deduction_date`, `tracking_number`, `deduction_description`, `amount`, `payment`, `adjustment`, `deleted`, `customer_id`
	FROM cse_deduction
	WHERE 1
	AND deduction_id = " . $deduction_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	
	try {
		DB::run($sql);
	    $new_id = DB::lastInsertId();
	
		$deduction = getDeductionInfo($deduction_id);
		//die(print_r($check));
		//new the case_uuid
		$kase = getKaseInfoByDeduction($deduction_id);
		$case_uuid = "";
		if (is_object($kase)) {
			$case_uuid = $kase->uuid;
		}
		$activity_category = "Deduction";
		switch($operation){
			case "insert":
				$operation .= "ed";
				break;
			case "update":
			case "delete":
				$operation .= "d";
				break;
		}
		$activity_uuid = uniqid("KS", false);
		$title = "Deduction";
		$activity = $title  . " was " . $operation . "  by " . $_SESSION['user_name'] . "
		Description:" . $deduction->deduction_description . "
		Amount: $" . $deduction->amount;
		if ($deduction->payment!=0) {
			$activity .= "
			Payment: $" . $deduction->payment;
		}
		if ($deduction->adjustment!=0) {
			$activity .= "
			Adjustment: $" . $deduction->adjustment;
		}
		if ($deduction->tracking_number!=0) {
			$activity .= "
			Track: " . $deduction->tracking_number;
		}
		recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category);
	
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function getAdjustmentsByType($account_id, $type) {
	getAdjustments($account_id, $type);
}
function getAdjustments($account_id, $type = "") {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
		
	$sql = "SELECT adjust.*, 
	adjust.adjustment_id id, adjust.adjustment_uuid uuid
	FROM cse_adjustment adjust
	INNER JOIN cse_account_adjustment gcc
	ON adjust.adjustment_uuid = gcc.adjustment_uuid
	INNER JOIN cse_account ccase
	ON gcc.account_uuid = ccase.account_uuid
			
	WHERE adjust.customer_id = :customer_id
	AND adjust.deleted = 'N'
	AND ccase.account_id = :account_id";
	
	if ($type!="") {
		$sql .= "
		AND adjust.adjustment_type = :type";
	}
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("account_id", $account_id);
		$stmt->bindParam("customer_id", $customer_id);
		if ($type!="") {
			$stmt->bindParam("type", $type);
		}
		$stmt->execute();
		$adjusts = $stmt->fetchAll(PDO::FETCH_OBJ);
		echo json_encode($adjusts);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTotalAdjustments($account_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
		
	$sql = "SELECT SUM(adjust.amount) total_amount,
	SUM(adjust.payment) total_payment,
	SUM(adjust.adjustment) total_adjustment
	FROM cse_adjustment adjust
	INNER JOIN cse_account_adjustment gcc
	ON adjust.adjustment_uuid = gcc.adjustment_uuid
	INNER JOIN cse_account ccase
	ON gcc.account_uuid = ccase.account_uuid
			
	WHERE adjust.customer_id = :customer_id
	AND adjust.deleted = 'N'
	AND  ccase.account_id = :account_id";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$adjusts = $stmt->fetchObject();
		echo json_encode($adjusts);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAdjustmentInfo($id) {
	return getAdjustment($id, true);
}
function getAdjustment($id, $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
		
	$sql = "SELECT adjust.*, 
	adjust.adjustment_id id, adjust.adjustment_uuid uuid, acc.account_id
	FROM cse_adjustment adjust
	INNER JOIN cse_account_adjustment gcc
	ON adjust.adjustment_uuid = gcc.adjustment_uuid
	INNER JOIN cse_account acc
	ON gcc.account_uuid = acc.account_uuid
	WHERE adjust.customer_id = :customer_id
	AND adjust.deleted = 'N'
	AND adjust.adjustment_id = :id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$adjust = $stmt->fetchObject();
		
		if (!$blnReturn) {
        	echo json_encode($adjust);
		} else {
			return $adjust;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function saveAdjustment() {
	session_write_close();
	
	//die();
	$arrFields = array();
	$arrSet = array();
	$account_id = 0;
	$table_name = "";
	$table_id = passed_var("table_id", "post");
	$corporation_uuid = "";
	$account_uuid = "";
	$user_uuid = "";
	//$case_id = passed_var("case_id", "post");
	//die($table_id);
	//$table_id = 1;
	//die(print_r($_POST));
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="account_id"){
			$account_id = $value;
			$kase = getBankAccountInfo($account_id);
			$account_uuid = $kase->uuid;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="adjustment_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		//die("before");
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
		//die("after");
	}	
	//$case_id = passed_var("case_id", "post");
	//die("case:" . $case_id);
	//die("table:" . $table_id);
	//die(print_r($arrFields));
	//die(print_r($arrSet));
	
	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
		//die($sql);
		try {
			DB::run($sql);
	    $new_id = DB::lastInsertId();
			
			//attach to case
			if ($account_uuid!="") {
				$last_updated_date = date("Y-m-d H:i:s");
				$account_adjustment_uuid = uniqid("KA", false);
				$attribute = "main";
				
				$sql = "INSERT INTO cse_account_adjustment (`account_adjustment_uuid`, `account_uuid`, `adjustment_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $account_adjustment_uuid . "', '" . $account_uuid . "', '" . $table_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				//echo $sql . "\r\n";
				DB::run($sql);
			}
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			trackAdjustment("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		//where
		$where_clause = "= '" . $table_id . "'";
		$where_clause = "`" . $table_name . "_id`" . $where_clause . "
		AND `customer_id` = " . $_SESSION['user_customer_id'];

		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET " . implode(",", $arrSet) . "
		WHERE " . $where_clause;
		
		//die(implode(",", $arrSet));
		
		//die($sql);
		
		try {
			$stmt = DB::run($sql);
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
			//track now	
			trackAdjustment("update", $table_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
}
function deleteAdjustment() {
	$id = passed_var("id", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "UPDATE `cse_adjustment` 
			SET `deleted` = 'Y'
			WHERE `adjustment_id` = :id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		echo json_encode(array("success"=>"adjustment marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	
	trackAdjustment("delete", $id);
}

