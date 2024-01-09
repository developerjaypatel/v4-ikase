<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");

$db = getConnection();
	
include("customer_lookup.php");
?>
<html>
<body style="font-size:0.95em">
<?php
$sql = "SELECT bc.case_uuid, bci.injury_uuid, 
inj.CASENO, E_NAME, E_ADDRESS, E_CITY, E_STATE, E_ZIP, E_PHONE, E_FAX, 
 E2_NAME, E2_ADDRESS, E2_CITY, E2_STATE, E2_ZIP, E2_PHONE, E2_FAX
FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.injury inj
INNER JOIN `ikase_" . $data_source . "`.`cse_case` bc
ON inj.CASENO = bc.cpointer
INNER JOIN `ikase_" . $data_source . "`.`cse_case_injury` bci
ON bc.case_uuid = bci.case_uuid
LEFT OUTER JOIN `ikase_" . $data_source . "`.`cse_case_corporation` bcc
ON bc.case_uuid = bcc.case_uuid AND bcc.`attribute` = 'employer'
LEFT OUTER JOIN (
	SELECT DISTINCT acc.CASENO
	FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.casecard acc
	WHERE acc.TYPE = 'EMPLOYER'
) acc_emp
ON inj.CASENO = acc_emp.CASENO
WHERE acc_emp.CASENO IS NOT NULL
AND (inj.E_NAME != '' OR inj.E2_NAME != '')
AND bcc.case_corporation_id IS NOT NULL
LIMIT 0, 100";

$cases = DB::select($sql);

$found = count(json_decode(json_encode($cases), true));
// die("Found:" . $found);
foreach($cases as $case_key=>$injury){
	//die(print_r($injury));
	$case_uuid = $injury->case_uuid;
	$injury_uuid = $injury->injury_uuid;
	$last_updated_date = date("Y-m-d H:i:s");
	
	if ($injury->E_NAME!="") {
		$table_uuid = uniqid("EM", false);
		$parent_table_uuid = uniqid("ED", false);
	
		$employer_address = $injury->E_ADDRESS . ", " . $injury->E_CITY . ", " . $injury->E_STATE . " " . $injury->E_ZIP;
		
		$arrSet = array();
		$company_name = $injury->E_NAME; $arrSet[] = addslashes($company_name);
		$type = "employer";
		$arrSet[] = $type;
		$full_address = $employer_address; $arrSet[] = addslashes($employer_address);
		$street = $injury->E_ADDRESS; $arrSet[] = addslashes($street);
		$city = $injury->E_CITY; $arrSet[] = addslashes($city);
		$state = $injury->E_STATE; $arrSet[] = addslashes(substr($state, 0, 2));
		$zip = $injury->E_ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
		$phone = $injury->E_PHONE; $arrSet[] = addslashes($phone);
		$fax = $injury->E_FAX; $arrSet[] = addslashes($fax);
		
		//look up in case already in
		$sql = "SELECT corporation_uuid
		FROM `ikase_" . $data_source . "`.`cse_corporation`
		WHERE customer_id = " . $customer_id . "
		AND corporation_uuid = parent_corporation_uuid
		AND type = 'employer'
		AND deleted = 'N'
		AND company_name = '" . addslashes($injury->E_NAME) . "'
		AND full_address = '" . addslashes($employer_address) . "'";
		//echo $sql . "\r\n<br>";
		$stmt = DB::run($sql);
		$rolodex = $stmt->fetchObject();
		
		$sql = "";
		$blnRolodex = false;
		if (is_object($rolodex)) {
			$parent_table_uuid = $rolodex->corporation_uuid;
			$blnRolodex = true;
		}
		if (!$blnRolodex) {
			//insert the parent record first
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
			(`corporation_uuid`, `customer_id`, `company_name`, `type`, `full_address`, 
			`street`, `city`, `state`, `zip`, `phone`, `fax`, 
			`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
			VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
			'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
			'N', '" . $parent_table_uuid . "','');";
			echo $sql . "\r\n<br>"; 		
			//die();
			/*
			$stmt = DB::run($sql);
			*/
		}
		//actual record now
		$sql .= "
		
		INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
		(`corporation_uuid`, `customer_id`, `company_name`, `type`, `full_address`, 
			`street`, `city`, `state`, `zip`, `phone`, `fax`,  
			`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
		VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','');";
		echo $sql . "\r\n<br>"; 		
		//die();
		/*
		$stmt = DB::run($sql);
		*/
		
		//attach to case
		$injury_table_uuid = uniqid("KA", false);
		//now we have to attach the partie to the case 
		$sql .= "
		
		INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "');";
		
		echo $sql . "\r\n<br>";   
				
		$stmt = DB::run($sql);
	}
	
	if ($injury->E2_NAME!="") {
		$table_uuid = uniqid("EM", false);
		$parent_table_uuid = uniqid("ED", false);
		
		$employer_address = $injury->E2_ADDRESS . ", " . $injury->E2_CITY . ", " . $injury->E2_STATE . " " . $injury->E2_ZIP;
		
		$arrSet = array();
		$company_name = $injury->E2_NAME; $arrSet[] = addslashes($company_name);
		$type = "employer";
		$arrSet[] = $type;
		$full_address = $employer_address; $arrSet[] = addslashes($employer_address);
		$street = $injury->E2_ADDRESS; $arrSet[] = addslashes($street);
		$city = $injury->E2_CITY; $arrSet[] = addslashes($city);
		$state = $injury->E2_STATE; $arrSet[] = addslashes(substr($state, 0, 2));
		$zip = $injury->E2_ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
		$phone = $injury->E2_PHONE; $arrSet[] = addslashes($phone);
		$fax = $injury->E2_FAX; $arrSet[] = addslashes($fax);
		
		
		//look up in case already in
		$sql = "SELECT corporation_uuid
		FROM `ikase_" . $data_source . "`.`cse_corporation`
		WHERE customer_id = " . $customer_id . "
		AND corporation_uuid = parent_corporation_uuid
		AND type = 'employer'
		AND deleted = 'N'
		AND company_name = '" . addslashes($injury->E2_NAME) . "'
		AND full_address = '" . addslashes($employer_address) . "'";
		//echo $sql . "\r\n<br>";
		$stmt = DB::run($sql);
		$rolodex = $stmt->fetchObject();
		
		$blnRolodex = false;
		if (is_object($rolodex)) {
			$parent_table_uuid = $rolodex->corporation_uuid;
			$blnRolodex = true;
		}
		if (!$blnRolodex) {
			//insert the parent record first
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
			(`corporation_uuid`, `customer_id`, `company_name`, `type`, `full_address`, 
			`street`, `city`, `state`, `zip`, `phone`, `fax`, 
			`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
			VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
			'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
			'N', '" . $parent_table_uuid . "','')";
			//echo $sql . "\r\n<br>"; 		
			$stmt = DB::run($sql);
		}
		//actual record now
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
		(`corporation_uuid`, `customer_id`, `company_name`, `type`, `full_address`, 
			`street`, `city`, `state`, `zip`, `phone`, `fax`,  
			`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
		VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
		//echo $sql . "\r\n<br>"; 		
		$stmt = DB::run($sql);
		
		//attach to case
		$injury_table_uuid = uniqid("KA", false);
		//now we have to attach the partie to the case 
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
		//echo $sql . "\r\n<br>";   		
		$stmt = DB::run($sql);
	}
	
	$sql = "SELECT COUNT(DISTINCT case_uuid) case_count
	FROM ikase_" . $data_source . ".cse_case_corporation
	WHERE corporation_uuid LIKE 'EM%';";
	
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject(); 
	
	$completed = $cases->case_count;
	
	if ($completed <  7351) {
	?>
		<script language='javascript'>parent.runEmployers(<?php echo $completed; ?>,7351)</script>
	<?php
	}
}
?>
</body>
</html>
