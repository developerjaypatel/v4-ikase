<?php
//clear the names
$arrInserts = array();

if (count($arrInserts)>0) {
	$query = "UPDATE cse_document doc
	SET deleted = 'Y'
	WHERE doc.document_uuid IN (SELECT document_uuid 
	FROM cse_injury_document ccd
	INNER JOIN cse_injury cc
	ON ccd.injury_uuid = cc.injury_uuid
	WHERE cc.injury_id = '" . $injury_id . "')
	AND (";
	
	foreach($arrNames as $name) {
		$insert  = "`doc`.`document_name` = '" . addslashes($name) . "'";
		$arrInserts[] = $insert;
	}
	$query .= implode(" OR ", $arrInserts);
	$query  .= ")";
	//echo $query . "\r\n<br>";
	try {
		$stmt = DB::run($query);
	} catch(PDOException $e) {
		$error = array("error"=> array("msg"=>"initial delete", "text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
//now associate each upload with the case
$query = "INSERT INTO cse_document (`document_uuid`, `document_date`, `type`, `document_filename`, `document_extension`, `document_name`, `description`, customer_id, verified) VALUES ";

$case_query = "INSERT INTO cse_case_document (case_document_uuid, case_uuid, document_uuid, attribute_1, attribute_2, last_updated_date, last_update_user, customer_id) VALUES ";

$injury_query = "INSERT INTO cse_injury_document (injury_document_uuid, injury_uuid, document_uuid, attribute_1, attribute_2, last_updated_date, last_update_user, customer_id) VALUES ";


$arrInserts = array();
$arrCaseInserts = array();
$arrInjuryInserts = array();

$kase = getKaseInfo($case_id);
$case_uuid = $kase->uuid;

$injury = getInjuryInfo($injury_id);
$injury_uuid = $injury->uuid;

$last_updated_date = date("Y-m-d H:i:s");

for($int=0;$int<count($arrUploads);$int++) {
	$upload = $arrUploads[$int];
	$name = $arrNames[$int];
	if ($name!="") {
		$document_uuid = uniqid("JF");
		$insert = "('" . $document_uuid . "','" . $last_updated_date . "','" . $form . "','" . addslashes($upload) . "',
		'pdf', '" . addslashes($name) . "', '" . addslashes($name) . "', '" . $cus_id .  "', 'Y'";
		$insert .= ")";
		$arrInserts[] = $insert;
		
		$case_document_uuid = uniqid("IF");
		$case_insert = "('" . $case_document_uuid . "', '" . $case_uuid . "', '" . $document_uuid . "', 'jetfiler', '" . $form . "', '" . $last_updated_date . "', '" . $_SESSION["user_id"] . "','" . $cus_id . "')";
		$arrCaseInserts[] = $case_insert;
		
		$injury_insert = "('" . $case_document_uuid . "', '" . $injury_uuid . "', '" . $document_uuid . "', 'jetfiler', '" . $form . "', '" . $last_updated_date . "', '" . $_SESSION["user_id"] . "','" . $cus_id . "')";
		$arrInjuryInserts[] = $injury_insert;
	}
	
	
}
if (count($arrInserts)>0) {
	$query .= implode(",\r\n", $arrInserts);
	/*
	echo $query . "\r\n<br>";
	die();
	*/
	try {
		$stmt = DB::run($query);
	} catch(PDOException $e) {
		$error = array("error"=> array("msg"=>"insert doc", "text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
	$final_case_query = $case_query . implode(",\r\n", $arrCaseInserts);
	$final_injury_query = $injury_query . implode(",\r\n", $arrInjuryInserts);
	
	//echo $final_case_query . "\r\n<br>";
	//echo $final_injury_query . "\r\n<br>";
	//die($case_query);
	//$result = DB::runOrDie($query);
try {
		////echo $case_query . "<br />";
		$stmt = DB::run($final_case_query);
		
		////echo $injury_query . "<br />";
		$stmt = DB::run($final_injury_query);
	} catch(PDOException $e) {
		$error = array("error"=> array("msg"=>"insert case doc", "text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
//reset
$arrCaseInserts = array();
$arrInjuryInserts = array();
//if we get here, associate docs with app
$injury_query = str_replace("VALUES ", "", $injury_query);

foreach($arrKaseDocs as $doc_index=>$document_id) {
	if (!is_numeric($document_id)) {
		continue;
	}
	$query = "SELECT COUNT(injury_document_id) document_count 
	FROM cse_injury_document idoc
	INNER JOIN cse_document doc
	ON idoc.document_uuid = doc.document_uuid
	WHERE doc.document_id = '" . $document_id . "'
	AND idoc.injury_uuid = '" .  $injury_uuid . "'
	AND doc.customer_id = '" . $cus_id . "'";
	$stmt = DB::run($query);
	$previous = $stmt->fetchObject();
	//echo "\r\n" . $query . " -> " . $previous->document_count . "\r\n\r\n";
	//die(print_r($previous));
	if ($previous->document_count == 0) {
		$case_document_uuid = uniqid("IF");
		$injury_insert = " SELECT '" . $case_document_uuid . "', '" . $injury_uuid . "', `document_uuid`, 'jetfiler', '" . $form . "', '" . $last_updated_date . "', '" . $_SESSION["user_id"] . "','" . $cus_id . "'
		FROM cse_document 
		WHERE document_id = '" . $document_id . "'
		AND customer_id = '" . $cus_id . "'";
		$arrInjuryInserts[] = $injury_insert;
	}
	/*
	if ($previous->document_count == 1) {
		//make sure the attributes are correct
		$query_update_attributes = "UPDATE cse_injury_document idoc, cse_document doc
		SET attribute_1 = 'jetfiler', 
		attribute_2 = '" . $form . "'
		WHERE doc.document_id = '" . $document_id . "'
		AND idoc.document_uuid = doc.document_uuid
		AND idoc.injury_uuid = '" .  $injury_uuid . "'
		AND idoc.customer_id = '" . $cus_id . "'";
		
		//echo $query_update_attributes . "\r\n";
		$stmt = DB::run($query_update_attributes);
	}
	*/
}
//die(print_r($arrInjuryInserts));
foreach($arrInjuryInserts as $injury_insert) {

	$final_injury_query = $injury_query . "
	" . $injury_insert;
	//die($final_injury_query);
	try {		
		//echo $injury_query . "\r\n";
		$stmt = DB::run($final_injury_query);
	} catch(PDOException $e) {
		$error = array("error"=> array("msg"=>"insert injury doc", "text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
//print_r($arrKaseDocs);
foreach($arrKaseDocs as $doc_index=>$document_id) {
	$name = $arrKaseNames[$doc_index];
	$sql = "UPDATE `cse_document`
	SET `type` = '" . $form . "',
	`document_name` = '" . addslashes($name) . "',
	`deleted` = 'N'
	WHERE `document_id` = '" . $document_id . "'
	AND `customer_id` = '" . $cus_id . "'";
	//echo "\r\n" . $sql . "\r\n";
	try {
		$stmt = DB::run($sql);
		
		$sql = "UPDATE cse_document cd, cse_case_document ccd
		SET ccd.attribute_1 = 'jetfiler', 
		ccd.attribute_2 = '" . $form . "'
		WHERE cd.document_uuid = ccd.document_uuid
		AND cd.document_id = '" . $document_id . "'
		AND cd.customer_id = '" . $cus_id . "'";
		//echo $sql . "\r\n";
			
		$stmt = DB::run($sql);
		
		$sql = "UPDATE cse_document cd, cse_injury_document ccd
		SET ccd.attribute_1 = 'jetfiler', 
		ccd.attribute_2 = '" . $form . "'
		WHERE cd.document_uuid = ccd.document_uuid
		AND ccd.injury_uuid = '" .  $injury_uuid . "'
		AND cd.document_id = '" . $document_id . "'
		AND cd.customer_id = '" . $cus_id . "'";
		//echo $sql . "\r\n";
		$stmt = DB::run($sql);
	} catch(PDOException $e) {
		$error = array("error"=> array("msg"=>"update injury doc", "text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
}

//die("\r\nprocessed");
?>
