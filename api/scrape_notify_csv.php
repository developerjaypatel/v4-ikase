<?php
//import csv contents as events in ikase

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

//die(print_r($_SERVER));

//die(date("Y-m-d H:i:s", strtotime("Jun 15, 2017 1:30 PM")));
include("connection.php");


//now let's deal with the data
$sql = "SELECT DISTINCT cus.customer_id cus_id, cus.data_source, cer.firm_name, ccc.applicant_law_firm
FROM ikase.cse_eams_reps cer
INNER JOIN ikase.cse_courtcalendar ccc
ON cer.firm_name = ccc.applicant_law_firm
INNER JOIN ikase.cse_customer cus
ON cer.eams_ref_number = cus.eams_no
WHERE ccc.customer_id = 0
ORDER BY cer.firm_name";

try {
	$customers = DB::select($sql);
	
	foreach($customers as $customer) {
		$data_source = $calendar->data_source;
		$customer_id = $calendar->cus_id;
		
		$db_name = "`ikase`";
		if ($data_source!="") {
			$db_name = "`ikase_" . $data_source . "`";
		}
	}
	
	//now get all the new entries for that customer
	$sql = "SELECT ccase.case_id, ccase.case_uuid, ccase.case_name, ccase.case_number, ccase.file_number, eve.* 
	FROM ikase_moheban.cse_event eve
	INNER JOIN ikase_moheban.cse_case_event cce
	ON eve.event_uuid = cce.event_uuid
	INNER JOIN ikase_moheban.cse_case ccase
	ON cce.case_uuid = ccase.case_uuid
	WHERE eve.deleted = 'N'
	AND `attribute` = 'court_calendar'
	AND CAST(last_updated_date AS DATE) = '" . date("Y-m-d") . "'";
	
	$entries = DB::select($sql);
	
	foreach($events as $entry) {
		if ($entry->case_number=="") {
			$entry->case_number = $entry->file_number;
		}
		$body = $entry->hearing_type . " was added automatically to the case calendar for <a href='v8.php?n=#kases/" . $entry->case_id . "' target='_blank' class='white_text'>" . $entry->case_number . "</a> from the Court Calendar on " . date("m/d/y g:iA", strtotime($entry->hearing_time));
		$arrBody[] = $body;
	}
	
	if (count($arrBody) > 0) {
		$body = implode("\r\n\r\n", $arrBody);
		//send a notification to worker
		$message_uuid = uniqid("CR");
		
		//notify the user
		$result = addEmailMessage($db_name, $customer_id, $entry->case_uuid, $message_uuid, count($arrBody) . "Hearing(s) Automatically Added from Court Calendar", $body, $user_id, $user_uuid, $kase_worker);
	}
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
