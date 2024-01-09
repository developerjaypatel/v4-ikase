<?php
include("manage_session.php");
set_time_limit(3000);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");
/*
CREATE TABLE `goldberg2`.`badref` (
  `badref_id` INT NOT NULL AUTO_INCREMENT,
  `cpointer` VARCHAR(45) NULL,
  `case_uuid` VARCHAR(45) NULL,
  `processed` VARCHAR(45) NULL DEFAULT 'N',
  PRIMARY KEY (`badref_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

INSERT INTO badref (cpointer, case_uuid)
SELECT DISTINCT cli.cpointer, ccase.case_uuid
FROM goldberg2.`client` cli
INNER JOIN goldberg2.referral ref
ON cli.refpoint = ref.refpnt
INNER JOIN ikase_goldberg2.cse_case ccase
ON cli.cpointer = ccase.cpointer
LEFT OUTER JOIN ikase_goldberg2.cse_case_corporation ccorp
ON ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'referring'
WHERE ccorp.case_corporation_uuid IS NULL;

*/


try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$last_updated_date  = date("Y-m-d H:i:s");
	$sql = "SELECT gcase.* 
	FROM `" . $data_source . "`.`badref` gcase
	WHERE processed = 'N'";
	
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	if(count($cases)==0) {
		die("done");
	}
	foreach($cases as $key=>$case){
		echo "<br>Processing -> " . $key. " == " . $case->case_uuid . "<br />";
		
		
		$sql = "SELECT 
		 `cli`.cpointer,
    ref.lastname referral_last,
    ref.firstname referral_first,
    ref.midname referral_mid,
    ref.firm referral_firm,
    ref.add1 referral_add1,
    ref.add2 referral_add2,
    ref.city referral_city,
    ref.state referral_state,
    ref.zip referral_zip,
    ref.tel referral_tel,
    ref.ext referral_ext,
    ref.fax referral_fax,
    ref.email referral_email,
    ref.salutation referral_salutation,
    ref.comments referral_comments
		FROM `" . $data_source . "`.`client` cli
		
		LEFT OUTER JOIN `" . $data_source . "`.`referral` ref ON cli.refpoint = ref.refpnt
		WHERE 1 
		AND cli.cpointer = '" . $case->cpointer . "'
		ORDER BY cli.cpointer
		LIMIT 0, 1";
		//echo $sql . "\r\n\r\n";
		//die();
		
		$stmt = $db->prepare($sql);
		
		
		//echo "Processing -> " . $case->cpointer . "  ";
		$case_uuid = $case->case_uuid;
		
		$stmt->execute();
		$injuries = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		//echo " => QUERY completed in " . $total_time . "<br /><br />";
		
		//die("found:" . print_r($injuries));
		$arrCpointer = array();
		foreach($injuries as $key=>$injury){
			//referral source
			//insert the referral
			$table_uuid = uniqid("RF", false);
			$parent_table_uuid = uniqid("PF", false);
			
			$full_address = $injury->referral_add1;
			if ($injury->referral_add2!="") {
				$full_address .= ", " . $injury->referral_add2;
			}
			$full_address .= ", " . $injury->referral_city;
			$full_address .= ", " . $injury->referral_state;
			$full_address .= " " . $injury->referral_zip;
			
			if ($injury->referral_first . $injury->referral_last!="" && $injury->referral_firm=="") {
				$injury->referral_firm = $injury->referral_first . " " . $injury->referral_last;
			}
			if ($injury->referral_firm!="") {
				$arrSet = array();
				$arrSet[] = addslashes($injury->referral_first . " " . $injury->referral_last);
				$arrSet[] = addslashes($injury->referral_firm);
				$arrSet[] = "referring";
				$arrSet[] = addslashes($full_address);
				$arrSet[] = addslashes($injury->referral_add1);
				$arrSet[] = addslashes($injury->referral_city);
				$arrSet[] = $injury->referral_state;
				$arrSet[] = $injury->referral_zip;
				$arrSet[] = addslashes($injury->referral_add2);
				if ($injury->referral_ext=="") {
					$arrSet[] = addslashes($injury->referral_tel);
				} else {
					$arrSet[] = addslashes($injury->referral_tel . " " . $injury->referral_ext);
				}
				$arrSet[] = addslashes($injury->referral_email);
				$arrSet[] = addslashes($injury->referral_salutation);
				//print_r($arrSet);
				//look up in case already in
				$sql = "SELECT corporation_uuid
				FROM `ikase_" . $data_source . "`.`cse_corporation`
				WHERE customer_id = " . $customer_id . "
				AND corporation_uuid = parent_corporation_uuid
				AND type = 'referring'
				AND deleted = 'N'
				AND company_name = '" . addslashes($injury->referral_firm) . "'
				AND full_address = '" . addslashes($full_address) . "'";
				//die($sql);
				$stmt = $db->prepare($sql);
				$stmt->execute();
				$partie = $stmt->fetchObject();
				
				if (is_object($partie)) {
					$parent_table_uuid = $partie->corporation_uuid;
				}
				if (!is_object($partie)) {
					//insert the parent record first
					$sql_referral = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
							VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '');";
							
					$stmt = $db->prepare($sql_referral);  
					echo $sql_referral . "\r\n\r\n"; 
					$stmt->execute();
				}
				$sql_referral = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`)  
						VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '');";
						
				$stmt = $db->prepare($sql_referral); 
				//echo $sql_referral . "\r\n\r\n";  
				$stmt->execute();
				
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				//now we have to attach the referral to the case 
				$sql_referral = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'referring', '" . $last_updated_date . "', 'system', '" . $customer_id . "');";
						
				$stmt = $db->prepare($sql_referral);
				//echo $sql_referral . "\r\n\r\n";   
				$stmt->execute();
				//die();
			}
		}
		
		$sql = "UPDATE `" . $data_source . "`.`badref` 
		SET processed = 'Y'
		WHERE case_uuid = '" . $case->case_uuid . "'";
		echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
	}
} catch(PDOException $e) {
	echo $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>