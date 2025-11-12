<?php
// die("here");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$cus_id = passed_var("cus_id");
$import_db_source = passed_var("import_db_source");
$cus_ip = passed_var("cus_ip");
$eams_no = passed_var("eams_no");
$cus_name = passed_var("cus_name");
$letter_name = passed_var("letter_name");
$parent_cus_id = passed_var("parent_cus_id");
$suid = passed_var("suid");
$admin_client = passed_var("admin_client");

$cus_name_first = passed_var("cus_name_first");
$cus_name_middle = passed_var("cus_name_middle");
$cus_name_last = passed_var("cus_name_last");
$cus_barnumber = passed_var("cus_barnumber");
$cus_street = passed_var("cus_street");
$cus_city = passed_var("cus_city");
$cus_county = passed_var("cus_county");
$cus_state = passed_var("cus_state");
$cus_zip = passed_var("cus_zip");
$cus_email = passed_var("cus_email");
$cus_phone = passed_var("cus_phone");
$cus_fax = passed_var("cus_fax");
$cus_type = passed_var("cus_type");
$password = passed_var("password");
$letter_office_code = passed_var("letter_office_code");
$pwd = "";
if ($password!="") {
	$pwd = encrypt($password, $crypt_key);
}
$data_source = passed_var("data_source");
$data_path = passed_var("data_path");
$data_path = str_replace("\\", "/", $data_path);
$read = passed_var("read");
$write = passed_var("write");
$export = passed_var("export");
$import = passed_var("import");
$billing = passed_var("billing");
$office_manager_first = passed_var("office_manager_first");
$office_manager_last = passed_var("office_manager_last");
$office_manager_middle = passed_var("office_manager_middle");
$office_manager_phone = passed_var("office_manager_phone");
$office_manager_email = passed_var("office_manager_email");
$cus_eams = passed_var("cus_eams");
$cus_fedtax_id = passed_var("cus_fedtax_id");
$cus_uan = passed_var("cus_uan");

$user_rate = passed_var("user_rate");
if ($user_rate=="") {
	$user_rate = 0;
}
$corporation_rate = passed_var("corporation_rate");
if ($corporation_rate=="") {
	$corporation_rate = 0;
}
$inhouse_id = passed_var("inhouse_id");
if ($inhouse_id=="") {
	$inhouse_id = "0";
}
$jetfile_id = passed_var("jetfile_id");
if ($jetfile_id=="") {
	$jetfile_id = "0";
}
//put them together to store
$permissions = $read . $write . $export . $import . $billing;
$query = "";
if ($cus_id=="" || $cus_id==-1) {
	$query = "INSERT INTO cse_customer (`eams_no`, `cus_name`, `letter_name`, `cus_name_first`, `cus_name_middle`, `cus_name_last`, `cus_street`, `cus_city`, `cus_county`, `cus_state`, `cus_zip`, `cus_email`, `cus_phone`, `cus_fax`, `cus_type`, `cus_ip`, `pwd`, `admin_client`,`data_source`,`data_path`,`permissions`,`inhouse_id`, `jetfile_id`, `ddl_venue`,`office_manager_first`, `office_manager_last`, `office_manager_middle`, `office_manager_phone`, `office_manager_email`, `cus_fedtax_id`, `cus_uan`, `cus_barnumber`, `user_rate`, `corporation_rate`, `start_date`, `import_db_source`)
	VALUES ('" . addslashes($eams_no) . "', '" . addslashes($letter_name) . "', '" . addslashes($cus_name) . "', '" . addslashes($cus_name_first) . "', '" . addslashes($cus_name_middle) . "', '" . addslashes($cus_name_last) . "', '" . addslashes($cus_street) . "', '" . addslashes($cus_city) . "', '" . addslashes($cus_county) . "', '" . addslashes($cus_state) . "', '" . addslashes($cus_zip) . "', '" . addslashes($cus_email) . "', '" . addslashes($cus_phone) . "', '" . addslashes($cus_fax) . "', '" . addslashes($cus_type) . "', '" . addslashes($cus_ip) . "', '" . addslashes($pwd) . "', '" . addslashes($admin_client) . "','" . $data_source . "','" . $data_path . "','" . $permissions . "','" . $inhouse_id . "','" . $jetfile_id . "', '" . $letter_office_code . "','" . addslashes($office_manager_first) . "','" . addslashes($office_manager_last) . "','" . addslashes($office_manager_middle) . "','" . addslashes($office_manager_phone) . "','" . addslashes($office_manager_email) . "','" . addslashes($cus_fedtax_id) . "','" . addslashes($cus_uan) . "','" . addslashes($cus_barnumber) . "','" . addslashes($user_rate) . "','" . addslashes($corporation_rate) . "', '" . date("Y-m-d") . "', '".$import_db_source."')";
	//die($query);
	//$result = DB::runOrDie($query);
	DB::run($query);
	$cus_id = DB::lastInsertId();
	//die($cus_id . " - cus");
	//now update the uuid
	$query = "UPDATE ikase.cse_customer
	SET customer_uuid = customer_id
	WHERE customer_id = " . $cus_id;
	//$result = DB::runOrDie($query);
	DB::run($query);

	//add masteradmin for matrix
	$uuid = uniqid("TS");
	$password = "Monster51";
	$pwd = encrypt($password, $crypt_key);
	
	$user_name = "Matrix Admin";
	$user_logon = "matrix_" . $cus_id;
	$user_email = "matrixdis@gmail.com";
	$user_type = 3;
	$level = "masteradmin";
	$day_start = "00:01AM";
	$day_end = "11:59PM";
	$job = "attorney";
	$arrDOW = array();
	for ($int=1;$int<7;$int++) {
		$arrDOW[] = "Y";
		$arrDOWTimes[] = "";
	}
	$days_of_week = implode("|", $arrDOW);
	$dow_times = implode("|", $arrDOWTimes);
	$user_first_name = "Matrix";
	$user_last_name = "Admin";
	
	//insert masteradmin user
	$query = "INSERT INTO `ikase`.`cse_user` (`user_uuid`, `customer_id`, `user_name`,`user_email`, `pwd`, `level`, `job`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `user_logon`, `user_first_name`, `user_last_name`, `user_type`)
	VALUES ('" . $uuid . "','" . $cus_id . "', '" . addslashes($user_name) . "', '" . addslashes($user_email) . "', '" . addslashes($pwd) . "','" . addslashes($level) . "','" . addslashes($job) . "','" . addslashes($day_start) . "','" . addslashes($day_end) . "','" . addslashes($days_of_week) . "','" . addslashes($dow_times) . "','" . addslashes($user_logon) . "','" . addslashes($user_first_name) . "','" . addslashes($user_last_name) . "', " . $user_type . ")";
	//$result = DB::runOrDie($query);
	DB::run($query);

	//add default settings
	$table_name = "setting";
	$arrFields = array("category", "setting", "setting_value");
	
	$arrSet = array();
	$arrSet[] = array("calendar_colors", "hearing", "#f4f4b9");
	$arrSet[] = array("calendar_colors", "conference", "#FFF399");
	$arrSet[] = array("calendar_colors", "msc", "#95f476");
	$arrSet[] = array("calendar_colors", "trial", "#12FAFF");
	$arrSet[] = array("calendar_colors","lien_conference", "#F7BE81");
	
	$arrSet[] = array("case_number", "case_number_next", 1000);
	$arrSet[] = array("case_number", "case_number_prefix", "");
	
	$arrSet[] = array("delay", "inbox_delay", "12000");
	$arrSet[] = array("delay", "task_delay", "67000");
	$arrSet[] = array("delay", "chat_delay", "7700");
	
	$arrSet[] = array("time", "event_time", "10:00");
	
	$table_attribute = "main";	
	$last_updated_date = date("Y-m-d H:i:s");
	
	//cycle through the default settings
	foreach($arrSet as $set_index=>$set) {
		$table_uuid = uniqid("K" . $set_index, false);

        try {
            $sql = "INSERT INTO ikase.`cse_" . $table_name ."` (`customer_id`, `" . $table_name . "_uuid`, `" . implode("`, `", $arrFields) . "`) 
				VALUES('" . $cus_id . "', '" . $table_uuid . "', '" . implode("', '", $set) . "')";
            //die($sql);
			DB::run($sql);

			//now we have to attach the applicant to the case 
			$sql = "INSERT INTO ikase.cse_" . $table_name . "_customer (`" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_uuid`, `customer_id`)
			VALUES ('" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $cus_id . "', '" . $cus_id . "')";
			DB::run($sql);
			//trackNote("insert", $new_id);
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
		
} else {
	$query = "UPDATE ikase.cse_customer
	SET eams_no = '" . $eams_no . "',
	cus_name = '" . addslashes($cus_name) . "',
	letter_name = '" . addslashes($letter_name) . "',
	cus_name_first = '" . addslashes($cus_name_first) . "',
	cus_name_middle = '" . addslashes($cus_name_middle) . "',
	cus_name_last = '" . addslashes($cus_name_last) . "',
	cus_street = '" . addslashes($cus_street) . "',
	cus_city = '" . addslashes($cus_city) . "',
	cus_county = '" . addslashes($cus_county) . "',
	cus_state = '" . addslashes($cus_state) . "',
	cus_zip = '" . addslashes($cus_zip) . "',
	cus_email= '" . addslashes($cus_email) . "',
	cus_phone= '" . addslashes($cus_phone) . "',
	cus_fax= '" . addslashes($cus_fax) . "',
	cus_ip= '" . addslashes($cus_ip) . "',
	cus_fedtax_id= '" . addslashes($cus_fedtax_id) . "',
	cus_uan= '" . addslashes($cus_uan) . "',
	user_rate = '" . addslashes($user_rate) . "',
	corporation_rate = '" . addslashes($corporation_rate) . "',
	cus_barnumber= '" . addslashes($cus_barnumber) . "',
	`office_manager_first` = '" . addslashes($office_manager_first) . "',
	`office_manager_middle` = '" . addslashes($office_manager_middle) . "',
	`office_manager_last` = '" . addslashes($office_manager_last) . "',
	`office_manager_phone` = '" . addslashes($office_manager_phone) . "',
	`office_manager_email` = '" . addslashes($office_manager_email) . "',		
	cus_type= '" . addslashes($cus_type) . "',";
	if($pwd!="") {
		$query .= "pwd = '" . addslashes($pwd) . "',";
	}
	$query .= "`data_source` = '" . $data_source . "',
	`data_path` = '" . $data_path . "',
	`inhouse_id`  = '" . $inhouse_id . "',
	`jetfile_id`  = '" . $jetfile_id . "',
	`permissions`  = '" . $permissions . "',
	`ddl_venue` = '" . $letter_office_code . "',
	`import_db_source` = '".$import_db_source."'
	WHERE customer_id = " . $cus_id;
	
	//die($query);
	//$result = DB::runOrDie($query);
	DB::run($query);
}

//need to update right away to set the parent cus id 
if ($parent_cus_id=="") {
	$parent_cus_id = $cus_id;
}
$query = "UPDATE ikase.cse_customer SET parent_customer_id = " . $parent_cus_id . " WHERE customer_id = " . $cus_id;
DB::run($query);

//attorneys
/*$query = "SELECT  `attorney_id`, `first_name`, `middle_initial`, `last_name`, `phone`, `fax`, `email`, `active`, `default_attorney`
FROM cse_attorney 
WHERE cus_id = '" . $cus_id . "'";
$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);
*/
$attorney_firm_name = passed_var("attorney_firm_name");
$attorney_name_first = passed_var("attorney_name_first");
$attorney_name_middle = passed_var("attorney_name_middle");
$attorney_name_last = passed_var("attorney_name_last");
$attorney_phone = passed_var("attorney_phone");
$attorney_fax = passed_var("attorney_fax");
$attorney_email = passed_var("attorney_email");
$attorney_username = passed_var("attorney_username");
$attorney_password = passed_var("attorney_password");

$default_attorney = passed_var("default_attorney");

//first one
if (isset($numbs) && $numbs==0) {
	$default_attorney = "Y";
}
if ($default_attorney!="Y") {
	$default_attorney = "N";
}
$blnReturnEditor = false;

if ($attorney_name_first!="") {
	//FIXME: missing two columns: user_id and aka
	$query = "INSERT INTO ikase.cse_attorney (`customer_id`, `firm_name`, `first_name`, `middle_initial`, `last_name`, `phone`, `fax`, `email`, `default_attorney`, `attorney_username`, `attorney_password`)
	VALUES ('" . $cus_id . "','" . addslashes($attorney_firm_name) . "','" . addslashes($attorney_name_first) . "', '" . addslashes($attorney_name_middle) . "', '" . addslashes($attorney_name_last) . "', '" . $attorney_phone . "', '" . $attorney_fax . "', '" . $attorney_email . "', '" . $default_attorney . "', '" . $attorney_username . "', '" . $attorney_password . "')";
	
	DB::run($query);
}
// die("here");
//default for now
$blnReturnEditor = true;

if ($blnReturnEditor) {
	header("location:editor.php?admin_client=" . $admin_client . "&cus_id=" . $cus_id. "&suid=" . $suid);
} else {
	header("location:index.php?admin_client=" . $admin_client . "&suid=" . $suid);
}
