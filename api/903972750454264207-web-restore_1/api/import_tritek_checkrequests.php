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

?>
<script language="javascript">
parent.setFeedback("checkrequests import started");
</script>
<?php
$db = getConnection();
try {
	include("customer_lookup.php");
	
	//FOR NEXT IMPORT, MAKE SURE ALL THE SOURCE TABLES ARE *NOT* FROM ikase_
	
	$sql = "SELECT nickname, user_uuid
	FROM ikase.cse_user
	WHERE customer_id = $customer_id";
	
	$stmt = $db->prepare($sql);
	//echo $sql . "\r\n\r\n";
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrUser = array();
	foreach($users as $user) {
		$arrUser[$user->nickname] = $user->user_uuid;
	}
	
	$sql = "SELECT DISTINCT gcase.cpointer, ccase.case_uuid
	FROM " . $data_source . ".trstreq gcase
	
	INNER JOIN `ikase_" . $data_source . "`.`cse_case` ccase
	ON gcase.cpointer = ccase.cpointer AND ccase.deleted = 'N'
	
	WHERE 1 
	#AND gcase.cpointer = '1024198'
	
	ORDER BY gcase.cpointer";
	
	$stmt = $db->prepare($sql);
	//echo $sql . "\r\n\r\n";
	//die();
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
		
	//die(print_r($cases));
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$cpointer = $case->cpointer;
		$case_uuid = $case->case_uuid;
		
		echo "Processing -> " . $key. " == " . $cpointer . "<br /><br />\r\n";
		
		//first get all reqs
		$sql = "SELECT CONCAT(cpointer, '_', batchno, '_', recno) request_uuid, 
		gcase.*
		FROM " . $data_source . ".trstreq gcase
		WHERE gcase.cpointer = '" . $cpointer . "'";
		
		$stmt = $db->prepare($sql);
		//echo $sql . "\r\n\r\n";
		//die();
		$stmt->execute();
		$reqs = $stmt->fetchAll(PDO::FETCH_OBJ);
		$last_updated_date = date("Y-m-d H:i:s");
		
		//die(print_r($reqs));
		
		foreach($reqs as $req) {
			$request_uuid = trim($req->request_uuid);
			if (isset($arrUser[trim($req->whoreq)])) {
				$requested_by = $arrUser[trim($req->whoreq)];
			} else {
				$requested_by = $arrUser["DI2"];
			}
			if ($req->approvedby!="") {
				if (isset($arrUser[trim($req->approvedby)])) {
					$approved_by = $arrUser[trim($req->approvedby)];
				} else {
					$approved_by = $arrUser["DI2"];
				}
			} else {
				$approved_by = "";
			}
			$blnApproved = (trim($req->approvedyn)=="Yes");
			$approved = 'N';
			if ($blnApproved) {
				$approved = 'Y';
			}
			$request_date = date("Y-m-d", strtotime(trim($req->date)));
			$amount = trim($req->checkamt);
			$reason = trim($req->comments);
			$req_payee = trim($req->payee);
			
			//echo "req_payee:" . $req_payee . "<br />\r\n\r\n";
			
			//is it the firm
			$blnFirm = (
				strpos($req_payee, 'Goldberg & Ibarra') !== false 
				|| 
				strpos($req_payee, 'Michael Goldberg') !== false
				|| 
				strpos($req_payee, 'THE GOLDBERG LAW FIRM') !== false
			);
			//default
			$payable_type = "C";
			
			if($blnFirm) {
				$payable_type = "F";
			}
			if (!$blnFirm) {
				//get payee
				$sql = "SELECT corp.*, corp.corporation_uuid uuid 
		
				FROM ikase_" . $data_source . ".cse_corporation corp
				
				INNER JOIN ikase_" . $data_source . ".cse_case_corporation ccorp
				ON corp.corporation_uuid = ccorp.corporation_uuid
				
				INNER JOIN ikase_" . $data_source . ".cse_case ccase
				ON ccorp.case_uuid = ccase.case_uuid
				
				WHERE corp.company_name = '". addslashes($req_payee) . "'
				AND ccase.cpointer = '" . $cpointer . "'
				AND ccase.deleted = 'N'";
				
				$stmt = $db->prepare($sql);
				//echo $sql . "\r\n\r\n";
				$stmt->execute();
				$payee = $stmt->fetchObject();
				
				if (is_object($payee)) {
					//associate the checkrqeuest with the corporation
					$payable_type = "C";
				} else {
					//could be the applicant
					$sql = "SELECT corp.*, corp.person_uuid uuid 

					FROM ikase_" . $data_source . ".cse_person corp
					
					INNER JOIN ikase_" . $data_source . ".cse_case_person ccorp
					ON corp.person_uuid = ccorp.person_uuid
					
					INNER JOIN ikase_" . $data_source . ".cse_case ccase
					ON ccorp.case_uuid = ccase.case_uuid
					
					WHERE corp.full_name = '" . addslashes($req_payee) . "'
					AND ccase.cpointer = '" . $cpointer . "'
					AND ccase.deleted = 'N'";
					
					$stmt = $db->prepare($sql);
					//echo $sql . "\r\n\r\n";
					$stmt->execute();
					$payee = $stmt->fetchObject();
					if (is_object($payee)) {
						//associate the checkrqeuest with the person
						$payable_type = "P";
					}
				}
				
				if (is_object($payee)) {
					//print_r($payee);
				}
			}
			
			//insert the req
			$payable_to = $req_payee;
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_checkrequest` 
			(`checkrequest_uuid`, `requested_by`, `payable_to`, `payable_type`, `rush_request`, `request_date`, `amount`, `needed_date`, `reason`, `reviewed_by`, `review_date`, `approved`, `customer_id`)
			VALUES ('" . $request_uuid . "', '" . $requested_by . "', '" . addslashes($payable_to) . "', '" . $payable_type . "', 'N', '" . $request_date . "', '" . $amount . "', '" . $request_date . "', '" . addslashes($reason) . "', '" . $approved_by . "', '" . $request_date . "', '" . $approved . "', '" . $customer_id . "');";
			
			//echo $sql . "\r\n\r\n";
			
			//die();
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
			
			if ($payable_type == "F") {
				//attach to customer
				$payable_to = $cus_name;
			}
			$attach = "";
			
			if ($payable_type == "C") {
				if (is_object($payee)) {
					//attach to corporation
					$payable_to = $payee->company_name;
					$attach = "corporation";
				}
			}
			if ($payable_type == "P") {
				//attach to person
				$payable_to = $payee->full_name;
				$attach = "person";
			}
			
			$case_table_uuid = uniqid("KR", false);
			$attribute_1 = "main";
			
			if ($payable_type == "F") {
				$attribute_1 = "firm";
			}
			
			//now we have to attach the checkrequest to the case 
			$sql = "INSERT INTO " . $data_source . "." . $data_source . "_case_checkrequest (`case_checkrequest_uuid`, `case_uuid`, `checkrequest_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $request_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $requested_by . "', '" . $customer_id . "')";
			
			//echo $sql . "\r\n\r\n";
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
			if (!$blnFirm) {
				if (is_object($payee)) {
					$case_table_uuid = uniqid("RC", false);
					$attribute_1 = "main";
					
					//now we have to attach the check to the case 
					$sql = "INSERT INTO " . $data_source . "." . $data_source . "_" . $attach . "_checkrequest (`" . $attach . "_checkrequest_uuid`, `" . $attach . "_uuid`, `checkrequest_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $payee->uuid . "', '" . $request_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $requested_by . "', '" . $customer_id . "')";
					
					//echo $sql . "\r\n\r\n";
					$stmt = $db->prepare($sql);  	
					$stmt->execute();
				}
			}
		}
	}
	
	
	$db = null;
} catch(PDOException $e) {
	
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die("\r\n" . $sql);
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`

?>