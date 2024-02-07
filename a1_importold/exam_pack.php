<?php
$app->get('/exams/:case_id', authorize('user'),	'getExams');
$app->get('/exam/:exam_id', authorize('user'),	'getExam');

//posts
$app->post('/exam/delete', authorize('user'), 'deleteExam');
$app->post('/exams/add', authorize('user'), 'addExam');
$app->post('/exams/update', authorize('user'), 'updateExam');

function getExams($case_id, $blnReturn = false, $blnDocuments = false) {
	session_write_close();
    /*
	$sql = "SELECT DISTINCT cex.*, corp.corporation_id, corp.company_name, cex.exam_id id, cex.exam_uuid uuid, 
			cc.case_id, cc.case_number, app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`,
			IFNULL(doc.document_id, '') document_id, IFNULL(doc.document_filename, '') document_filename, 
			IFNULL(doc.document_name, '') document_name, IFNULL(doc.document_date, '') document_date
			FROM  `cse_exam` cex
			INNER JOIN `cse_corporation_exam` ccorpx
			ON cex.exam_uuid = ccorpx.exam_uuid AND ccorpx.deleted = 'N'
			INNER JOIN `cse_corporation` corp
			ON corp.corporation_uuid = ccorpx.corporation_uuid
			INNER JOIN `cse_case_corporation` ccorp
			ON ccorpx.corporation_uuid = ccorp.corporation_uuid
			INNER JOIN `cse_case` cc
			ON ccorp.case_uuid = cc.case_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON cc.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN ";
			
if (($_SESSION['user_customer_id']==1033)) { 
	$sql .= "(" . SQL_PERSONX . ")";
} else {
	$sql .= "cse_person";
}
$sql .= " app ON ccapp.person_uuid = app.person_uuid

			LEFT OUTER JOIN cse_exam_document ced
			ON cex.exam_uuid = ced.exam_uuid
			LEFT OUTER JOIN cse_document doc
			ON ced.document_uuid = doc.document_uuid
			LEFT OUTER JOIN cse_case_document ccd
            ON doc.document_uuid = ccd.document_uuid
			
			WHERE `cex`.`deleted` = 'N'
			AND cc.case_id = :case_id
			AND `cex`.customer_id = " . $_SESSION['user_customer_id'] . "
			AND IF (doc.document_uuid!='' AND ccd.case_uuid IS NULL, 'N', 'Y') = 'Y'";
if ($blnDocuments) {
	$sql .= " 
	AND ced.exam_uuid IS NOT NULL";
}
$sql .= " 
ORDER BY `cex`.exam_id ASC";
*/
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "SELECT DISTINCT cex.*, corp.corporation_id, corp.company_name, cex.exam_id id, cex.exam_uuid uuid, 
				corp.case_id, corp.case_number, app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
				IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`,
				IFNULL(doc.document_id, '') document_id, IFNULL(doc.document_filename, '') document_filename, 
				IFNULL(doc.document_name, '') document_name, IFNULL(doc.document_date, '') document_date
	
	FROM (
		SELECT cc.case_uuid, cc.case_id, cc.case_number, corp.corporation_id, corp.corporation_uuid, corp.company_name 
		FROM `cse_corporation` corp
	
		INNER JOIN cse_case_corporation ccorp
		ON corp.corporation_uuid = ccorp.corporation_uuid
	
		INNER JOIN `cse_case` cc
		ON ccorp.case_uuid = cc.case_uuid
	
		WHERE cc.case_id = :case_id
		AND `cc`.customer_id = :customer_id
	) corp
	INNER JOIN `cse_corporation_exam` ccorpx
	ON corp.corporation_uuid = ccorpx.corporation_uuid
	
	INNER JOIN `cse_exam` cex
	ON ccorpx.exam_uuid = cex.exam_uuid
	
	LEFT OUTER JOIN cse_case_person ccapp ON corp.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
	LEFT OUTER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
	
	LEFT OUTER JOIN cse_exam_document ced
	ON cex.exam_uuid = ced.exam_uuid
	
	LEFT OUTER JOIN cse_document doc
	ON ced.document_uuid = doc.document_uuid
	
	LEFT OUTER JOIN cse_case_document ccd
	ON doc.document_uuid = ccd.document_uuid
	
	WHERE 1
	AND cex.deleted = 'N'
	AND IF (doc.document_uuid!='' AND ccd.case_uuid IS NULL, 'N', 'Y') = 'Y'";

	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$exams = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if ($blnReturn) {
			return $exams;
		} else {
        	echo json_encode($exams);        
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getExam($exam_id) {
	session_write_close();
	
    $sql = "SELECT `cex`.*, `cex`.`exam_id` `id`, 
	`cex`.`exam_uuid` `uuid`, corp.corporation_id corp_id, ccase.case_id,
	IFNULL(doc.document_id, '') document_id, IFNULL(doc.document_filename, '') document_filename, 
	IFNULL(doc.document_name, '') document_name
	
	FROM  `cse_exam` cex
	
	INNER JOIN `cse_corporation_exam` ccsx
	ON cex.exam_uuid = ccsx.exam_uuid AND ccsx.deleted = 'N'
	INNER JOIN `cse_corporation` corp
	ON ccsx.corporation_uuid = corp.corporation_uuid
	INNER JOIN `cse_case_corporation` cccorp
	ON corp.corporation_uuid = cccorp.corporation_uuid
	INNER JOIN `cse_case` ccase
	ON cccorp.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN cse_exam_document ced
	ON cex.exam_uuid = ced.exam_uuid
	LEFT OUTER JOIN cse_document doc
	ON ced.document_uuid = doc.document_uuid
	LEFT OUTER JOIN cse_case_document ccd
	ON doc.document_uuid = ccd.document_uuid
	
	WHERE `cex`.`deleted` = 'N'
	AND `cex`.`exam_id` = :exam_id
	AND IF (doc.document_uuid!='' AND ccd.case_uuid IS NULL, 'N', 'Y') = 'Y'
	AND `cex`.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("exam_id", $exam_id);
		$stmt->execute();
		$exam = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($exam);
        } else {
            echo $_GET['callback'] . '(' . json_encode($exam) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getExamInfo($exam_id) {
    $sql = "SELECT `cex`.*, `cex`.`exam_id` `id`, 
	`cex`.`exam_uuid` `uuid`, corp.corporation_id corp_id, ccase.case_id
	FROM  `cse_exam` cex
	INNER JOIN `cse_corporation_exam` ccsx
	ON cex.exam_uuid = ccsx.exam_uuid AND ccsx.deleted = 'N'
	INNER JOIN `cse_corporation` corp
	ON ccsx.corporation_uuid = corp.corporation_uuid
	INNER JOIN `cse_case_corporation` cccorp
	ON corp.corporation_uuid = cccorp.corporation_uuid
	INNER JOIN `cse_case` ccase
	ON cccorp.case_uuid = ccase.case_uuid
	WHERE `cex`.`deleted` = 'N'
	AND `cex`.`exam_id` = :exam_id
	AND `cex`.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("exam_id", $exam_id);
		$stmt->execute();
		$exam = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $exam;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function deleteExam() {
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_exam cs
			SET cs.`deleted` = 'Y'
			WHERE `exam_id`=:id
			AND customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$stmt = null; $db = null;
		trackExam("delete", $id);
		echo json_encode(array("success"=>"exam marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addExam() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$case_id =  passed_var("case_id", "post");
	$corporation_id =  passed_var("corp_id", "post");
	$document_id = "";
	$send_document_id = "";
	
	$attachments = "";
	$arrAttachments = array();
	
	$primary =  passed_var("primary", "post");
	
	if ($primary!="") {
		$corporation_id =  $primary;
	}
	$exam_id = -1;
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="corp_id") {
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="primary") {
			continue;
		}
		if ($fieldname=="exam_id" || $fieldname=="table_id" || $fieldname=="case_id" || $fieldname=="send_document_id") {
			continue;
		}
		if ($fieldname=="document_id") {
			$document_id = $value;
			
			continue;
		}
		if ($fieldname=="attachments") {
			if ($value!="") {
				$attachments = $value;
				$arrAttachments[] = $value;
			}
			continue;
		}
		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	$arrFields[] = "`customer_id`";
	$arrSet[] = $_SESSION['user_customer_id'];
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		/*
		//get the uuid
		$sql = "SELECT corporation_uuid uuid FROM cse_corporation WHERE corporation_id = :corporation_id";
		$sql .= " AND `cse_corporation`.customer_id = " . $_SESSION['user_customer_id'];
		$stmt = $db->prepare($sql);
		$stmt->bindParam("corporation_id", $corporation_id);
	
		$stmt->execute();
		$corporation = $stmt->fetchObject();
		*/
		$corporation = getCorporationInfo($corporation_id);
		
		$corporation_exam_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the corporation to the exam 
		$sql = "INSERT INTO cse_corporation_" . $table_name . " (`corporation_" . $table_name . "_uuid`,`" . $table_name . "_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $corporation_exam_uuid  ."', '" . $table_uuid . "', '" . $corporation->uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		//die($sql);
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		//track now
		trackExam("insert", $new_id);
		
		if ($document_id != "") {
			$document = getDocumentInfo($document_id);
			
			$exam_document_uuid = uniqid("TD", false);
			
			$sql = "INSERT INTO cse_exam_document (`exam_document_uuid`, `exam_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $exam_document_uuid  ."', '" . $table_uuid . "', '" . $document->document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		foreach ($arrAttachments as $attachment) {
			$document_name = $attachment;
			//first check if this document is _already_ attached
			$sql = "SELECT COUNT(doc.document_id) thecount
			FROM `cse_document` doc
			INNER JOIN `cse_check_document` cnd
			ON doc.document_uuid = cnd.document_uuid
			WHERE doc.document_name = '" . $document_name . "'";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$document_count = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;	
			
			if ($document_count->thecount==0) {
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "check attachment";
				$description_html = "check attachment";
				$type = "check_attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$new_id = $db->lastInsertId();
				$stmt = null; $db = null;	
				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$exam_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_exam_document (`exam_document_uuid`, `exam_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $exam_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;	
				if ($case_id!="") {
					$kase = getKaseInfo($case_id);
					$case_uuid = $kase->uuid;
					
					$sql = "INSERT INTO cse_case_document (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $exam_document_uuid  ."', '" . $case_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$stmt = null; $db = null;	
				}
			}
		}
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function updateExam() {
	$request = Slim::getInstance()->request();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$injury_id = -1;
	$case_id = "";
	$exam_id = -1;
	$corporation_id =  passed_var("corp_id", "post");
	
	$attachments = "";
	$arrAttachments = array();
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="exam_id") {
			$exam_id = $value;
			continue;
		}
		if ($fieldname=="billing_time" || $fieldname=="document_id" || $fieldname=="send_document_id") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			continue;
		}
		if ($fieldname=="injury_id" || $fieldname=="corp_id" || $fieldname=="primary") {
			continue;
		}

		if (strpos($fieldname, "date_") > -1 || strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		if ($fieldname=="attachments") {
			if ($value!="") {
				$attachments = $value;
				$arrAttachments[] = $value;
			}
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
			
			//get the exam info
			$exam = getExamInfo($value);
			$exam_uuid = $exam->uuid;
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
	//die($sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		echo json_encode(array("id"=>$table_id)); 
		
		if ($corporation_id!="") {
			//update the corporation
			$sql = "UPDATE `cse_corporation_exam` SET deleted = 'Y' WHERE exam_uuid = '" . $exam_uuid . "'";
			$sql .= " AND `cse_corporation_exam`.customer_id = " . $_SESSION['user_customer_id'];
			$stmt = $db->prepare($sql);
			$stmt->execute();
			
			$corporation = getCorporationInfo($corporation_id);
			
			$corporation_exam_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the corporation to the exam 
			$sql = "INSERT INTO cse_corporation_" . $table_name . " (`corporation_" . $table_name . "_uuid`,`" . $table_name . "_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $corporation_exam_uuid  ."', '" . $exam_uuid . "', '" . $corporation->uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			//die($sql);
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		}
		
		$stmt = null; $db = null;
		trackExam("update", $table_id);
		
		foreach ($arrAttachments as $attachment) {
			$document_name = $attachment;
			//first check if this document is _already_ attached
			$sql = "SELECT COUNT(doc.document_id) thecount
			FROM `cse_document` doc
			INNER JOIN `cse_check_document` cnd
			ON doc.document_uuid = cnd.document_uuid
			WHERE doc.document_name = '" . $document_name . "'";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$document_count = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;	
			
			if ($document_count->thecount==0) {
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "check attachment";
				$description_html = "check attachment";
				$type = "check_attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$new_id = $db->lastInsertId();
				$stmt = null; $db = null;	
				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$exam_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_exam_document (`exam_document_uuid`, `exam_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $exam_document_uuid  ."', '" . $exam_uuid . "', '" . $document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;	
				if ($case_id!="") {
					$kase = getKaseInfo($case_id);
					$case_uuid = $kase->uuid;
					
					$sql = "INSERT INTO cse_case_document (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $exam_document_uuid  ."', '" . $case_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$stmt = null; $db = null;	
				}
			}
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function trackExam($operation, $exam_id) {
	
	$sql = "INSERT INTO cse_exam_track (`user_uuid`, `user_logon`, `operation`, `exam_id`, `exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "',`exam_id`, `exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, `customer_id`, `deleted`
	FROM cse_exam
	WHERE 1
	AND exam_id = " . $exam_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		//new the case_uuid
		$kase = getKaseInfoByExam($exam_id);
		//die(print_r($kase));
		$activity_category = "Exam";
		switch($operation){
			case "insert":
				$operation .= "ed";
				break;
			case "update":
				$operation .= "d";
				break;
			case "delete":
				$operation .= "d";
				break;
		}
		
		//$doi = date("m/d/Y", strtotime($kase->start_date));
		//$doi = $kase->adj_number . " - " . $doi;	
		$corporation_name = $kase->company_name;
		$activity = "Exam Information  for [" . $corporation_name . "] was " . $operation . "  by " . $_SESSION['user_name'];
		$stmt = null; $db = null;
		$billing_time = 0;
		if (isset($_POST["billing_time"])) {
			$billing_time = passed_var("billing_time", "post");
		}
		recordActivity($operation, $activity, $kase->uuid, $new_id, $activity_category, $billing_time);
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
}
?>