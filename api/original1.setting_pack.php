<?php
$app->get('/settings/fresh', authorize('user'),	'refreshCustomerSettings');
$app->get('/setting/firm/:level', authorize('user'),	'getCustomerSettings');
$app->get('/setting/customer/:setting_id', authorize('user'),	'getTheCustomerSetting');
$app->get('/setting/getname/:name', authorize('user'),	'getTheCustomerSettingByName');
$app->post('/setting/byname', authorize('user'),	'postTheCustomerSettingByName');

//$app->get('/setting/customer/:category/:case_id', authorize('user'),	'getCustomerSettingByCategory');
$app->get('/documentfilters', authorize('user'), 'getDocumentFilters');
$app->get('/notefilters', authorize('user'), 'getNoteFilters');
$app->get('/calendarfilters', authorize('user'), 'getCalendarFilters');
$app->get('/calendaroptions', authorize('user'), 'calendarTypesOptions');
$app->get('/statusfilters', authorize('user'), 'getStatusFilters');
$app->get('/substatusfilters', authorize('user'), 'getSubStatusFilters');
$app->get('/subsubstatusfilters', authorize('user'), 'getSubSubStatusFilters');

$app->get('/setting/user', authorize('user'),	'getUserSettings');
$app->get('/setting/user/:user_setting_id', authorize('user'),	'getTheUserSetting');
$app->post('/setting/user/add', authorize('user'), 'addUserSetting');
$app->post('/setting/user/update', authorize('user'), 'updateSetting');

//posts
$app->post('/setting/delete', authorize('user'), 'deleteSetting');
$app->post('/setting/customer/add', authorize('user'), 'addCustomerSetting');
$app->post('/setting/customer/update', authorize('user'), 'updateSetting');
$app->post('/documentfilters/update', authorize('user'), 'updateDocumentFilters');
$app->post('/notefilters/update', authorize('user'), 'updateNoteFilters');
$app->post('/calendarfilter/update', authorize('user'), 'updateCalendarFilter');
$app->post('/calendarfilters/update', authorize('user'), 'updateCalendarFilters');

$app->post('/statusfilter/add', authorize('user'), 'saveStatusFilters');
$app->post('/statusfilter/update', authorize('user'), 'updateStatusFilters');

$app->post('/substatusfilter/add', authorize('user'), 'saveSubStatusFilters');
$app->post('/subsubstatusfilter/add', authorize('user'), 'saveSubSubStatusFilters');
$app->post('/subsubstatusfilter/update', authorize('user'), 'updateSubSubStatusFilters');
function refreshCustomerSettings() {
	session_write_close();
	
	$sql = "SELECT cs.*, cs.setting_id id, cs.setting_uuid uuid
			FROM  `cse_setting` cs
			WHERE 1
			AND `cs`.`deleted` = 'N'
			AND `cs`.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cs.setting_uuid NOT IN (SELECT `setting_uuid` FROM `cse_setting_user`)
			
			UNION
			
			SELECT cs.*, cs.setting_id id, cs.setting_uuid uuid
			FROM  `cse_setting` cs
			INNER JOIN `cse_setting_user` csu
			ON cs.setting_uuid = csu.setting_uuid
			WHERE 1
			AND `cs`.`deleted` = 'N'
			AND `cs`.customer_id = " . $_SESSION['user_customer_id'] . "
			AND csu.user_uuid = '" . $_SESSION['user_id'] . "'
			
			ORDER BY `category`";
	try {
		$db = getConnection();
			
		$stmt = $db->query($sql);
		//die($sql);
		$customer_settings = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error2"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
	
	$arrSettingValues = array();
	$arrSettings = array();
	
	foreach($customer_settings as $setting_info) {
		$category = $setting_info->category;
		$setting = $setting_info->setting;
		$setting_value = $setting_info->setting_value;
		$default_value = $setting_info->default_value;
		if ($setting_value=="" && $default_value!="") {
			$setting_value = $default_value;
		}
		$arrSettings[$setting] = $setting_value;
		$arrSettingValues[$category][$setting] = $setting_value;
	}
	
	if (!isset($arrSettings["case_number_prefix"])) {
		$arrSettings["case_number_prefix"] = "";
	}
	
	echo json_encode($arrSettings);
}
function getCalendarFilters() {
	session_write_close();
	$sql = "SELECT DISTINCT setting_id, setting, setting_value, default_value, category the_category, deleted
		FROM `cse_setting` 
		WHERE `category` = 'calendar_type'
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'
        ORDER by `setting`";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$event_types = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($event_types);
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function getStatusFilters() {
	session_write_close();
	$sql = "SELECT casestatus_id id, casestatus `status`, law, deleted
		FROM `cse_casestatus` 
		WHERE 1
        ORDER by `casestatus`";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$casestatuss = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($casestatuss);
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function saveStatusFilters() {
	session_write_close();
	$table_uuid = uniqid("SF", false);
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$casetype = passed_var("casetype", "post");
	$casestatus = passed_var("casestatus", "post");
	$status_level = passed_var("status_level", "post");
	$table_name = "case" . $status_level . "status";
	
	$sql = "INSERT INTO `cse_" . $table_name . "` (" . $table_name . "_uuid, " . $table_name . ", last_change_user, last_change_date, law)
	SELECT '" . $table_uuid . "', :casestatus, :user_uuid, :right_now,'" . $casetype . "'
						FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_" . $table_name . "` 
							WHERE " . $table_name . " = :casestatus AND law = '" . $casetype . "'
						)";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("casestatus", $casestatus);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function saveSubStatusFilters() {
	session_write_close();
	$table_uuid = uniqid("SF", false);
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$casestatus = passed_var("casestatus", "post");
	$casetype = passed_var("casetype", "post");
	$status_level = passed_var("status_level", "post");
	// $table_name = "case" . $status_level . "status";
	$table_name="casesubstatus";
	
	$sql = "INSERT INTO `cse_" . $table_name . "` (" . $table_name . "_uuid, " . $table_name . ", last_change_user, last_change_date, law)
	SELECT '" . $table_uuid . "', :casestatus, :user_uuid, :right_now, '" . $casetype . "'
						FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_" . $table_name . "` 
							WHERE " . $table_name . " = :casestatus  AND law = '" . $casetype . "'
						)";
	//die($sql." = ".$casestatus);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("casestatus", $casestatus);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}

function saveSubSubStatusFilters() {
	session_write_close();
	$table_uuid = uniqid("SF", false);
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$casestatus = passed_var("casestatus", "post");
	$casetype = passed_var("casetype", "post");
	$status_level = passed_var("status_level", "post");
	// $table_name = "case" . $status_level . "status";
	$table_name="casesubsubstatus";
	
	$sql = "INSERT INTO `cse_" . $table_name . "` (" . $table_name . "_uuid, " . $table_name . ", last_change_user, last_change_date, law)
	SELECT '" . $table_uuid . "', :casestatus, :user_uuid, :right_now, '" . $casetype . "'
						FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_" . $table_name . "` 
							WHERE " . $table_name . " = :casestatus AND law = '" . $casetype . "'
						)";
	//die($sql." = ".$casestatus);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("casestatus", $casestatus);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}

function updateStatusFilters() {
	session_write_close();
	$table_uuid = uniqid("SF", false);
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$id = passed_var("casestatus_id", "post");
	$deleted = passed_var("deleted", "post");
	$casestatus = passed_var("casestatus", "post");
	$status_level = passed_var("status_level", "post");
	$casetype = passed_var("casetype", "post");
	$table_name = "case" . $status_level . "status";
	
	$sql = "UPDATE `cse_" . $table_name . "` 
	SET `" . $table_name . "` = :casestatus,
	deleted = :deleted, 
	last_change_user = :user_uuid, 
	last_change_date = :right_now";
	if($casetype!="" && $casetype!=null){
		$sql = $sql.", law = :casetype";
	}
	$sql = $sql." WHERE `" . $table_name . "_id` = :id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("casestatus", $casestatus);
		$stmt->bindParam("deleted", $deleted);
		$stmt->bindParam("user_uuid", $user_uuid);
		if($casetype!="" && $casetype!=null){
			$stmt->bindParam("casetype", $casetype);
		}
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}

function updateSubSubStatusFilters() {
	session_write_close();
	$table_uuid = uniqid("SF", false);
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$id = passed_var("casestatus_id", "post");
	$deleted = passed_var("deleted", "post");
	$casestatus = passed_var("casestatus", "post");
	$status_level = passed_var("status_level", "post");
	$casetype = passed_var("casetype", "post");
	$table_name = "casesubsubstatus";
	
	$sql = "UPDATE `cse_" . $table_name . "` 
	SET `" . $table_name . "` = :casestatus,
	deleted = :deleted, 
	last_change_user = :user_uuid, 
	last_change_date = :right_now";
	if($casetype!="" && $casetype!=null){
		$sql = $sql.", law = :casetype";
	}
	$sql = $sql." WHERE `" . $table_name . "_id` = :id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("casestatus", $casestatus);
		$stmt->bindParam("deleted", $deleted);
		$stmt->bindParam("user_uuid", $user_uuid);
		if($casetype!="" && $casetype!=null){
			$stmt->bindParam("casetype", $casetype);
		}
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}

function getSubStatusFilters() {
	session_write_close();
	$sql = "SELECT casesubstatus_id id, casesubstatus `status`, law, deleted
		FROM `cse_casesubstatus` 
		WHERE 1
        ORDER by `casesubstatus`";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$casesubstatuss = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($casesubstatuss);
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function getSubSubStatusFilters() {
	session_write_close();
	$sql = "SELECT casesubsubstatus_id id, casesubsubstatus `status`, law, deleted
		FROM `cse_casesubsubstatus` 
		WHERE 1
        ORDER by `casesubsubstatus`";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$casesubstatuss = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($casesubstatuss);
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}

function calendarTypesOptions() {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	
	//event types
	$setting_options = "";
	
	$sql = "SELECT DISTINCT *
			FROM `cse_setting` 
			WHERE `category` = 'calendar_type'
			AND customer_id = :customer_id
			AND deleted = 'N'
			ORDER by `setting`";
	//die($sql);		
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$event_types = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
	
	$setting_options = "<option value=''>Filter By Type</option>";
	
	foreach($event_types as $event_type) {
		$setting_id = $event_type->setting_id;
		$setting = $event_type->setting;
		
		$option = "<option value='" . $setting . "'>" . ucwords(str_replace("_", " ", $setting)) . "</option>";
		$setting_options .= "" . $option;
	}
	
	$setting_options .= "<option style='font-size: 1pt; background-color: #999999;' disabled>&nbsp;</option><option value='case_type_wc'>WC</option><option value='case_type_pi'>PI</option>";
	if (strpos($_SESSION['user_role'], "admin") !== false) {
		$filter_option = "<option style='font-size: 1pt; background-color: #000000;' disabled>&nbsp;</option><option value='new_filter'>Manage List</option>";
		$setting_options .= $filter_option;
	}
	
	die($setting_options);
}
function getCustomerSettings($level) {
	session_write_close();
    /*
	$sql = "
	SELECT * FROM
		(SELECT cs.*, cs.setting_uuid uuid, 'customer' setting_level
			FROM  `cse_setting` cs
			WHERE 1
			AND `cs`.`deleted` = 'N'
			AND `cs`.customer_id = :customer_id
			AND `cs`.`category` != 'document_type'
			AND `cs`.`category` != 'document_types'
			AND cs.setting_uuid NOT IN (SELECT `setting_uuid` FROM `cse_setting_user`)
			
			UNION
			
		SELECT cs.*, cs.setting_uuid uuid, 'user' setting_level
			FROM  `cse_setting` cs
			INNER JOIN `cse_setting_user` csu
			ON cs.setting_uuid = csu.setting_uuid
			WHERE 1
			AND `cs`.`deleted` = 'N'
			AND `cs`.`category` != 'document_type'
			AND `cs`.`category` != 'document_types'
			AND `cs`.customer_id = :customer_id
			AND csu.user_uuid = :user_id
			
			ORDER BY `category`
		) settings
		
		WHERE setting_level = :level";
	*/
	if ($level=="customer") {
		$sql = "SELECT cs.*, cs.setting_uuid uuid, 'customer' setting_level
			FROM  `cse_setting` cs
			WHERE 1
			AND `cs`.`deleted` = 'N'
			AND `cs`.customer_id = :customer_id
			AND `cs`.`category` != 'document_type'
			AND `cs`.`category` != 'document_types'
			AND cs.setting_uuid NOT IN (SELECT `setting_uuid` FROM `cse_setting_user`)
			ORDER BY `category`";
	} else {
		$sql = "SELECT cs.*, cs.setting_uuid uuid, 'user' setting_level
			FROM  `cse_setting` cs
			INNER JOIN `cse_setting_user` csu
			ON cs.setting_uuid = csu.setting_uuid
			WHERE 1
			AND `cs`.`deleted` = 'N'
			AND `cs`.`category` != 'document_type'
			AND `cs`.`category` != 'document_types'
			AND `cs`.customer_id = :customer_id
			AND csu.user_uuid = :user_id
			ORDER BY `category`";
	}
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		if ($level=="user") {
			$stmt->bindParam("user_id", $_SESSION['user_id']);
		}
		//$stmt->bindParam("level", $level);
		$stmt->execute();
		$customers_settings = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//die(print_r($customers_settings));
		if ($level=="customer") {
			$sqlfilters = "SELECT * 
			FROM ikase.cse_customer_document_filters 
			WHERE customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sqlfilters);
			$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
			$stmt->execute();
			$filter = $stmt->fetchObject();
			
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			if (is_object($filter)) {
				$filter = json_decode($filter->document_filters);
				$filter_types = $filter->types;
				$filter_categories = $filter->categories;
				$filter_subcategories = $filter->subcategories;
			} else {
				$filter_types = "";
				$filter_categories = "";
				$filter_subcategories = "";
			}
			//next id
			//$next_id = $customers_settings[count($customers_settings) - 1]->setting_id + 1;
			$next_id = 1;
			foreach ($customers_settings as &$customer_setting) {
				$customer_setting->id = $next_id;
				$customer_setting->setting_id = $next_id;
				$next_id++;
			}
			foreach($filter_types as $filter_type) {
				$filter_name = $filter_type;
				if (strpos($filter_type, "COR") === false) {
					$filter_name = ucwords(strtolower($filter_type));
				}
				$filter_name = str_replace("Mpn", "MPN", $filter_name);
				$filter_name = str_replace("Ame Report", "AME Report", $filter_name);
				$filter_name = str_replace("SDT RECORDS", "SDT Records", $filter_name);
				/*
				$option = '<option value="' . trim($filter_name) . '">' . trim($filter_name) . '</option>';
				$arrOptions["types"][] = $option;
				*/
				
				
				$setting = new stdClass();
				$setting->setting_id = $next_id;
				$setting->setting_uuid = $next_id;
				$setting->customer_id = $_SESSION['user_customer_id'];
				$setting->category = "document_types";
				$setting->setting = trim($filter_name);
				$setting->setting_value =  "";
				$setting->setting_type = "";
				$setting->default_value = "";
				$setting->deleted = "N";
				$setting->id = $next_id;
				$setting->uuid = $next_id;
				
				$customers_settings[] = $setting;
				$next_id++;
			}
			//die(print_r($customers_settings));
			
			foreach($filter_categories as $filter_category) {
				$filter_name = $filter_category;
				if (strpos($filter_category, "EAMS") === false) {
					$filter_name = ucwords(strtolower($filter_category));
				}
				/*
				$option = '<option value="' . trim($filter_name) . '">' . trim($filter_name) . '</option>';
				$arrOptions["categories"][] = $option;
				*/
				$setting = new stdClass();
				$setting->setting_id = $next_id;
				$setting->setting_uuid = $next_id;
				$setting->customer_id = $_SESSION['user_customer_id'];
				$setting->category = "document_categories";
				$setting->setting = trim($filter_name);
				$setting->setting_value =  "";
				$setting->setting_type = "";
				$setting->default_value = "";
				$setting->deleted = "N";
				$setting->id = $next_id;
				$setting->uuid = $next_id;
				
				$customers_settings[] = $setting;
				$next_id++;
			}
			
			foreach($filter_subcategories as $filter_subcategory) {
				$filter_name = $filter_subcategory;
				//if (strpos($filter_subcategory, "EAMS") === false) {
					$filter_name = ucwords(strtolower($filter_subcategory));
				//}
				/*
				$option = '<option value="' . trim($filter_name) . '">' . trim($filter_name) . '</option>';
				$arrOptions["subcategories"][] = $option;
				*/
				$setting = new stdClass();
				$setting->setting_id = $next_id;
				$setting->setting_uuid = $next_id;
				$setting->customer_id = $_SESSION['user_customer_id'];
				$setting->category = "document_subcategories";
				$setting->setting = trim($filter_name);
				$setting->setting_value =  "";
				$setting->setting_type = "";
				$setting->default_value = "";
				$setting->deleted = "N";
				$setting->id = $next_id;
				$setting->uuid = $next_id;
				
				$customers_settings[] = $setting;
				$next_id++;
			}
		}
		/*
		$setting_counter = 1;
		foreach($customers_settings as &$customers_setting) {
			$customers_setting->id = $setting_counter;
			$setting_counter++;
		}
		*/
        // Include support for JSONP requests
         echo json_encode($customers_settings);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updateCalendarFilters() {
	session_write_close();
	try {
		$customer_id = $_SESSION["user_customer_id"];
		$sql = "SELECT DISTINCT setting_id, setting, setting_value, default_value, category the_category, deleted
		FROM `cse_setting` 
		WHERE `category` = 'calendar_type'
		AND customer_id = :customer_id
        ORDER by `setting`";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$arrFilterValues = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//die(print_r($arrFilterValues));
		
		foreach($arrFilterValues as $filter) {
			$index = $filter->setting_id;
			if (isset($_POST["calendar_type_" .$index])) {
				$value = passed_var("calendar_type_" .$index, "post");
				//remove any deleted on this filter
				$sql_delete = "UPDATE cse_setting SET deleted = 'N' WHERE setting_id = :setting_id";
			} else {
				$sql_delete = "UPDATE cse_setting SET deleted = 'Y' WHERE setting_id = :setting_id";
			}
			//echo $index . " ==> " . $sql_delete . "<br />";
			$db = getConnection();
			$stmt = $db->prepare($sql_delete);
			$stmt->bindParam("setting_id", $index);
			$stmt->execute();
			$stmt = null; $db = null;
		}
		//do we have anything new
		foreach($_POST as $index=>$value) {
			if (strpos($index, "_new")===false) {
				//the top of the list is all new, if not new, get out
				continue;
			}
			//break out the row number
			$row_number = str_replace("calendar_type_new_", "", $index);
			$setting = passed_var("new_setting_" . $row_number, "post");
			$setting_value = passed_var("new_setting_value_" . $row_number, "post");
			$default_value = passed_var("new_default_value_" . $row_number, "post");
			$setting_uuid = uniqid("ST");
			//insert if new
			$str_SQL = "INSERT INTO `cse_setting` (`setting_uuid`, `category`, `setting`, `setting_value`, `default_value`, `customer_id`) 
						SELECT '" . $setting_uuid . "', 'calendar_type', '" . $setting . "', '" . $setting_value . "', '" . $default_value . "', '" . $customer_id . "'
						FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_setting` 
							WHERE setting = '" . $setting . "'
							AND `category` = 'calendar_type'
							AND customer_id = '" . $customer_id . "'
						)";

			// echo $str_SQL . ";\r\n";
			$db = getConnection();
			$stmt = $db->prepare($str_SQL);
			$stmt->execute();
			$inserted_id = $db->lastInsertId();
			$db = null; $stmt = null;
		}
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updateCalendarFilter() {
	session_write_close();
	
	$setting_id = passed_var("setting_id", "post");
	$setting = passed_var("setting", "post");
	$setting_value = passed_var("settingdefault", "post");
	
	try {
		$sql = "UPDATE cse_setting 
		SET setting = :setting,
		setting_value = :setting_value  
		WHERE setting_id = :setting_id";

		//echo $index . " ==> " . $sql_delete . "<br />";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("setting_id", $setting_id);
		$stmt->bindParam("setting", $setting);
		$stmt->bindParam("setting_value", $setting_value);
		$stmt->execute();
		$stmt = null; $db = null;

		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updateNoteFilters() {
	session_write_close();
	try {
		$db = getConnection();
		
		$sqlfilters = "SELECT cdf.*, cdf.filter_id id 
		FROM ikase.cse_customer_document_filters  cdf
		WHERE customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sqlfilters);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$filter = $stmt->fetchObject();
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$filter_id = $filter->id;
		
		$arrFilters = json_decode($filter->document_filters);
		//die(print_r($_POST));
		$jdata = json_decode($filter->document_filters);
		if (isset($jdata->notes)) {
			$arrFilterValues = $jdata->notes;
			foreach($arrFilterValues as $index=>&$filter) {
				if (isset($_POST["document_type_" .$index])) {
					$value = passed_var("document_type_" .$index, "post");
					//remove any deleted on this filter
					$filter = str_replace("|deleted", "", $filter);
					
					//was it edited
					if (isset($_POST["edit_element_" .$index])) {
						$filter = passed_var("edit_element_" .$index, "post");
						$filter = str_replace(" ", "_", $filter);
						$filter = strtolower($filter);
					}
				} else {
					$value = "N";
				}
				if ($value == "N") {
					if (strpos($filter, "|deleted")===false) {
						//die("pos:" . strpos($filter, "|deleted"));
						$filter .= "|deleted";
					} else {
						//die($filter . " not del");
					}
				}
			}
		}
		//do we have anything new
		foreach($_POST as $index=>$value) {
			if (strpos($index, "_new")===false) {
				//the top of the list is all new, if not new, get out
				continue;
			}
			//break out the row number
			$row_number = str_replace("document_type_new_", "", $index);
			$new_filter = passed_var("new_type_" . $row_number, "post");
			//echo $new_filter . "<br />";
			$arrFilterValues[] = $new_filter;
		}
		sort($arrFilterValues);
		//die(print_r($arrFilterValues));
		$types = json_encode($arrFilterValues);
		$arrFilters->notes = $arrFilterValues;
		//die(print_r($arrFilters));
		
		$document_filters = json_encode($arrFilters);
		//die($document_filters );
		$sqlfilters = "UPDATE ikase.cse_customer_document_filters  cdf
		SET `document_filters` = :document_filters
		WHERE customer_id = :customer_id
		AND filter_id = :filter_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sqlfilters);
		$stmt->bindParam("document_filters", $document_filters);
		$stmt->bindParam("filter_id", $filter_id);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "filter_id"=>$filter_id));
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updateDocumentFilters() {
	session_write_close();
	try {
		$db = getConnection();
		
		$sqlfilters = "SELECT cdf.*, cdf.filter_id id 
		FROM ikase.cse_customer_document_filters  cdf
		WHERE customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sqlfilters);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$filter = $stmt->fetchObject();
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$filter_id = $filter->id;
		$filter_type = passed_var("filter_type", "post");
		$arrFilters = json_decode($filter->document_filters);
		$jfilters = json_decode($filter->document_filters);
		switch ($filter_type) {
			case "types":
				$arrFilterValues = $jfilters->types;
				break;
			case "categories":
				$arrFilterValues = $jfilters->categories;
				break;
			case "subcategories":
				$arrFilterValues = $jfilters->subcategories;
				break;
		}
		
		foreach($arrFilterValues as $index=>&$filter) {
			if (isset($_POST["document_type_" .$index])) {
				$value = passed_var("document_type_" .$index, "post");
				//remove any deleted on this filter
				$filter = str_replace("|deleted", "", $filter);
			} else {
				$value = "N";
			}
			if ($value == "N") {
				if (strpos($filter, "|deleted")===false) {
					//die("pos:" . strpos($filter, "|deleted"));
					$filter .= "|deleted";
				} else {
					//die($filter . " not del");
				}
			}
		}
		
		//do we have anything new
		foreach($_POST as $index=>$value) {
			if (strpos($index, "_new")===false) {
				//the top of the list is all new, if not new, get out
				continue;
			}
			//break out the row number
			$row_number = str_replace("document_type_new_", "", $index);
			$new_filter = passed_var("new_type_" . $row_number, "post");
			//echo $new_filter . "<br />";
			$arrFilterValues[] = $new_filter;
		}
		sort($arrFilterValues);
		//die(print_r($arrFilterValues));
		$types = json_encode($arrFilterValues);
		switch ($filter_type) {
			case "types":
				$arrFilters->types = $arrFilterValues;
				break;
			case "categories":
				$arrFilters->categories = $arrFilterValues;
				break;
			case "subcategories":
				$arrFilters->subcategories = $arrFilterValues;
				break;
		}
		//die(print_r($arrFilters));
		
		$document_filters = json_encode($arrFilters);
		//die($document_filters );
		$sqlfilters = "UPDATE ikase.cse_customer_document_filters  cdf
		SET `document_filters` = :document_filters
		WHERE customer_id = :customer_id
		AND filter_id = :filter_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sqlfilters);
		$stmt->bindParam("document_filters", $document_filters);
		$stmt->bindParam("filter_id", $filter_id);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "filter_id"=>$filter_id));
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDocumentFilters() {
	session_write_close();
	try {
		$db = getConnection();
		
		$sqlfilters = "SELECT cdf.*, cdf.filter_id id 
		FROM ikase.cse_customer_document_filters  cdf
		WHERE customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sqlfilters);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$filter = $stmt->fetchObject();
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($filter);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getNoteFilters() {
	session_write_close();
	try {
		$db = getConnection();
		
		$sqlfilters = "SELECT cdf.*, cdf.filter_id id 
		FROM ikase.cse_customer_document_filters  cdf
		WHERE customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sqlfilters);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$filter = $stmt->fetchObject();
		die(print_r(json_decode($filter->document_filters)));
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($filter);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getUserSettings() {
	session_write_close();
    $sql = "SELECT cs.`setting_id`, cs.`setting_uuid`, cs.`customer_id`, 
			cs.`category`, cs.`setting`, REPLACE( cs.`setting_value` ,  '\r',  '<br>' )  `setting_value` , cs.`setting_type`, 
			cs.`default_value`, cs.setting_id id, cs.setting_uuid uuid
			FROM  `cse_setting` cs
			INNER JOIN `cse_setting_user` csc
			ON cs.setting_uuid = csc.setting_uuid
			WHERE 1
			AND `cs`.`deleted` = 'N'
			AND `csc`.user_uuid = '" . $_SESSION['user_id'] . "'
			AND `cs`.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER BY cs.`category`";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$users_setting = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

        // Include support for JSONP requests
         echo json_encode($users_setting);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTheCustomerSettingByName($name) {
	$name = urldecode($name);
	 postTheCustomerSettingByName($name);
}
function postTheCustomerSettingByName($name = "") {
	session_write_close();
	if ($name == "") {
		$name = passed_var("name", "post");
	}
	$name = str_replace("|", " ", $name);
    $sql = "SELECT cs.*, cs.setting_id id, cs.setting_uuid uuid
			FROM  `cse_setting` cs
			WHERE `cs`.customer_id = :customer_id
			AND `cs`.`deleted` = 'N'";
	$sql .= " AND `cs`.setting = :name";
	
	$customer_id = $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("name", $name);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$the_customer_setting = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
        echo json_encode($the_customer_setting); 
		
		//special (only?) case
		if ($name=="case_number_next") {
			//increment the case_number_next
			$sql = "UPDATE cse_setting cset
			SET cset.setting_value = cset.setting_value + 1
			WHERE cset.setting = 'case_number_next'
			AND cset.customer_id = " . $_SESSION['user_customer_id'];
			
			//echo $sql . "\r\n";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTheCustomerSetting($setting_id) {
	session_write_close();
    $sql = "SELECT cs.*, cs.setting_id id, cs.setting_uuid uuid
			FROM  `cse_setting` cs
			WHERE `cs`.customer_id = " . $_SESSION['user_customer_id'] . "
			AND `cs`.`deleted` = 'N'";
	if(strpos($setting_id, "UUID")===false) {
			$sql .= " AND `cs`.setting_id = '" . $setting_id . "'";
	} else {
		$sql .= " AND `cs`.setting_uuid = '" . str_replace("UUID", "", $setting_id) . "'";
	}
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $setting_id);
		$stmt->execute();
		$the_customer_setting = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
         echo json_encode($the_customer_setting);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTheUserSetting($user_setting_id) {
	session_write_close();
	
    $sql = "SELECT cs.`setting_id`, cs.`setting_uuid`, cs.`customer_id`, 
			cs.`category`, cs.`setting`, REPLACE( cs.`setting_value` ,  '\r',  '\\r\\n' )  `setting_value` , cs.`setting_type`, 
			cs.`default_value`, cs.setting_id id, cs.setting_uuid uuid
			FROM  `cse_setting` cs
			INNER JOIN `cse_setting_user` csu 
			ON cs.setting_uuid = csu.setting_uuid
			INNER JOIN cse_user cu
			ON csu.user_uuid = cu.user_uuid
			WHERE `cu`.user_id = " . $_SESSION['user_plain_id'] . "
			AND `cs`.setting_id = '" . $user_setting_id . "'
			AND `cs`.`deleted` = 'N'
			AND cs.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $user_setting_id);
		$stmt->execute();
		$the_user_setting = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
         echo json_encode($the_user_setting);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addCustomerSetting() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$partie_id = "";
	$case_uuid = "";
	$send_document_id = "";
	//default attribute
	$table_attribute = "main";
	$setting_type = "customer";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="category") {
			$table_attribute = $value;
			//continue;
		}
		if ($fieldname=="setting_type") {
			$setting_type = $value;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="source_message_id") {
			$source_message_id = $value;
			continue;
		}
		if ($fieldname=="send_document_id") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
		}
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$db = null;
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		if ($table_attribute == "") {
			$table_attribute = "main";
		}
		//it might be a user setting, just one for now
		$arrUserSettings = array("email");
		$foreign_table = "customer";
		$foreign_uuid = $_SESSION['user_customer_id'];
		foreach ($arrUserSettings as $user_setting) {
			if (in_array($table_attribute, $arrUserSettings)) {
				$setting_type = "user";
				$foreign_table = "user";
				$foreign_uuid =  $_SESSION['user_id'];
			}
		}
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the customer to the setting 
		$sql = "INSERT INTO cse_setting_" . $setting_type . " (`setting_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `" .$foreign_table . "_uuid`, `customer_id`)
		VALUES ('" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $foreign_uuid . "', '" . $_SESSION['user_customer_id'] . "')";
		
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		//trackNote("insert", $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function updateSetting() {
	$request = Slim::getInstance()->request();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$table_attribute = "";
	$blnColors = false;
	$source_message_id = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="setting_type") {
			continue;
		}
		if ($fieldname=="send_document_id") {
			continue;
		}
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		//category
		if ($fieldname=="category") {
			if (strpos($value, "_colors") > -1) {
				$blnColors = true;
			}
		}
		
		if ($fieldname=="setting_value" || $fieldname=="default_value") {
			if ($blnColors) {
				$value = "#" . str_replace("#", "", $value);
			}
		}
		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
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
	//die( $sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		
		echo json_encode(array("success"=>$table_id)); 
		
		//track now
		//trackNote("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function addUserSetting() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$partie_id = "";
	$case_uuid = "";
	$send_document_id = "";
	//default attribute
	$table_attribute = "main";
	$setting_type = "user";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="category") {
			$table_attribute = $value;
			//continue;
		}
		if ($fieldname=="setting_type") {
			$setting_type = $value;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="source_message_id") {
			$source_message_id = $value;
			continue;
		}
		if ($fieldname=="send_document_id") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
		}
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$db = null;
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		if ($table_attribute == "") {
			$table_attribute = "main";
		}
		
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the applicant to the case 
		$sql = "INSERT INTO cse_setting_" . $setting_type . " (`setting_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `user_uuid`, `customer_id`)
		VALUES ('" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		//trackNote("insert", $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function deleteSetting() {
	$id = passed_var("id", "post");
	$sql = "UPDATE `cse_setting`
			SET `deleted` = 'Y'
			WHERE `setting_uuid` = :id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		
		echo json_encode(array("success"=>"setting marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>