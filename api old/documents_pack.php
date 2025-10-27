<?php
$app->get('/document/:id', authorize('user'),	'getDocument');
$app->get('/documents/search/:name/:type/:start/:end', authorize('user'), 'searchDocuments');
$app->get('/documents/searchbycase/:case_id/:name', authorize('user'), 'searchCaseDocuments');
$app->get('/documents/:case_id', authorize('user'),	'getDocuments');

$app->get('/legacy/:case_id', authorize('user'), 'getLegacyArchive');
$app->get('/legacya1/:case_id', authorize('user'), 'getA1Archives');
$app->get('/legacya1_list/:case_id/:folder', authorize('user'), 'listA1Archives');
$app->get('/legacya1_read/:case_id/:folder/:file', authorize('user'), 'readA1Archive');
$app->get('/documents/attribute/:case_id/:attribute', authorize('user'), 'getDocumentsByAttribute');
$app->get('/documents/pi/attribute/:case_id/:attribute', authorize('user'), 'getDocumentsByAttributePi');
$app->get('/archives/:case_id', authorize('user'), 'getLargeArchives');
$app->get('/largearchives/:case_id', authorize('user'), 'getLargeArchives');
$app->get('/download_vocational/:id', authorize('user'), 'downloadVocational');

$app->get('/ecandlist/:case_id', authorize('user'), 'listCandidusArchive');
$app->get('/ecand/:doc_id', authorize('user'), 'getCandidusArchive');

$app->get('/attachments/:parent/:id', authorize('user'), 'getParentDocuments');
$app->get('/message_attachments/:case_id', authorize('user'), 'getAttachmentDocuments');
$app->get('/letters/:case_id', authorize('user'), 'getKaseLetters');
$app->get('/letterinvoices/:case_id', authorize('user'), 'getKaseInvoiceLetters');
$app->get('/notificationslist/:document_id', authorize('user'),	'listNotifications');
$app->get('/stacks', authorize('user'),	'getStacks');
$app->get('/stacks/notifications', authorize('user'), 'getStackNotifications');
$app->get('/stacks/type/:stack_type', authorize('user'), 'getStackByType');
$app->get('/stacks/test', 'speedTest');

$app->get('/mystacks/type/:stack_type', authorize('user'), 'getMyStackByType');
$app->get('/mystacks/new', authorize('user'), 'getNewScans');

$app->get('/templatesinv', authorize('user'),	'getInvoiceTemplates');
$app->get('/templatestype/:case_type', authorize('user'),	'getTemplatesByType');
$app->get('/templates', authorize('user'),	'getTemplates');

$app->post('/archivemsg', authorize('user'), 'openArchiveMsg');

$app->post('/document/delete', authorize('user'), 'deleteDocument');
$app->post('/document/read', authorize('user'), 'readDocument');
$app->post('/document/unread', authorize('user'), 'unreadDocument');

$app->post('/reports/add', authorize('user'), 'addReport');

$app->post('/stacks/add', authorize('user'), 'addStack');
$app->post('/stacks/complete', authorize('user'), 'completeStack');
$app->post('/unassigneds/add', authorize('user'), 'addUnassigned');

$app->post('/documents/categorize', authorize('user'), 'categorizeDocument');
$app->post('/documents/add', authorize('user'), 'addDocument');
$app->post('/documents/remoteadd', 'addRemoteDocument');
$app->post('/documents/abacusadd', 'addAbacusDocument');
$app->post('/documents/update', authorize('user'), 'updateDocument');
$app->post('/documents/download', authorize('user'), 'renameDocumentForDownload');
$app->post('/documents/type', authorize('user'), 'typeDocument');

//client uploaded document using macro
$app->post('/documents/processftp', 'processFTP');

$app->post('/templates/propagate', authorize('user'), 'propagateTemplate');

$app->post('/refervocation', authorize('user'), 'referVocation');
$app->post('/docucents/filesubmission', 'docuFileupload');
$app->post('/docucents/addAPIKey','addDocucentsAPIKey');
$app->post('/docucents/lettersubmission', 'letterFileupload');
function readA1Archive($case_id, $folder, $file) {
	//return;
	$kase = getKaseInfo($case_id);
	$folder = str_replace("~~", " ", $folder);
	$file = str_replace("~~", " ", $file);
	//die(print_r($kase));
	$params = base64_encode($kase->cpointer . '|' . $folder . '|' . $file . '|' . $_SESSION["user_data_source"]);
	$path = 'http://kustomweb.xyz/a1_archive/legacy_read.php?db=' . $_SESSION["user_data_source"] . '&params=' . $params;
	//die($path);
	$homepage = file_get_contents($path);
	echo $homepage;
}
function listA1Archives($case_id, $folder) {
	//return;
	$kase = getKaseInfo($case_id);
	$folder = str_replace("~~", " ", $folder);
	//die(print_r($kase));
	$params = base64_encode($kase->cpointer . '|' . $folder . '|' . $_SESSION["user_data_source"]);
	$path = 'http://kustomweb.xyz/a1_archive/legacy_list.php?db=' . $_SESSION["user_data_source"] . '&params=' . $params;
	//die($path);
	$homepage = file_get_contents($path);
	echo $homepage;
}
function getA1Archives($case_id) {
	//return;
	$kase = getKaseInfo($case_id);
	//die(print_r($kase));
	$params = base64_encode($kase->cpointer . '|' . $kase->first_name . '|' . $kase->last_name . '|' . $kase->employer . '|' . $_SESSION["user_data_source"]);
	$path = 'http://kustomweb.xyz/a1_archive/legacy.php?db=' . $_SESSION["user_data_source"] . '&params=' . $params;
	//die($path);
	$homepage = file_get_contents($path);
	echo $homepage;
}
function getLegacyArchive($case_id) {
	$kase = getKaseInfo($case_id);
	
	$params = base64_encode($kase->cpointer . '|' . $_SESSION["user_data_source"]);
	$path = 'http://kustomweb.xyz/a1_archive/archives.php?db=' . $_SESSION["user_data_source"] . '&params=' . $params;
	//die($path);
	$homepage = file_get_contents($path);
	echo $homepage;
}
function listCandidusArchive($case_id) {
	session_write_close();
	
	$kase = getKaseInfo($case_id);
	$case_uuid = $kase->uuid;
	
	$sql = "SELECT docs.doc_id, docs.folder, docs.subfolder, docs.filename document_name, docs.filename document_filename, 
	'docs' path, doc_id id 
	FROM `" . $_SESSION["user_data_source"] ."`.`docs` 
	WHERE case_uuid = :case_uuid
	ORDER BY folder, subfolder, filename";
	
	try{		
		$dbConn = getConnection();
		$stmt = $dbConn->prepare($sql);
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->execute();
		$ecands = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $dbConn = null;
		
		echo json_encode($ecands);	
	} catch (PDOException $e) {
		die("Error customer");
	}
	exit();
}
function getCandidusArchive($doc_id) {
	$path = 'http://kustomweb.xyz/ecand/get_doc.php?customer_id=' . $_SESSION["user_customer_id"] . '&doc_id=' . $doc_id . '&sess_id=' . $_SESSION["user"];
	//die($path);
	$homepage = file_get_contents($path);
	echo $homepage;
}
function getAttachmentDocuments($case_id) {
	session_write_close();
	//all the documents, EXCEPT applicant picture AND letters
    $sql = "SELECT DISTINCT doc.`document_id` , doc.`document_uuid` , doc.`parent_document_uuid` , 
	REPLACE(  `document_name` ,  '/home/cstmwb/public_html/autho/web/fileupload/server/php/file_container/" . $case_id . "/',  '' ) `document_name` ,  
	IF (DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p'))`document_date` , 
	IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p'))`received_date` , `doc`.`source`,	
	`document_filename` ,  `document_extension`, `thumbnail_folder` ,  `description` ,  IF (`description_html` IS NULL, '', `description_html`) `description_html` ,  `type` ,  `verified` , doc.`deleted` , doc.`document_id`  `id` , doc.`document_uuid`  `uuid`, doc.customer_id, cu.user_name,  `cse_case`.`case_uuid`, `cse_case`.`case_id`
	FROM  `cse_document` doc
	INNER JOIN  `cse_case_document` ON  `doc`.`document_uuid` =  `cse_case_document`.`document_uuid`
	LEFT OUTER JOIN  ikase.`cse_user` cu ON cse_case_document.last_update_user = cu.user_uuid 
	INNER JOIN  `cse_case` ON (  `cse_case_document`.`case_uuid` =  `cse_case`.`case_uuid` 
	AND  `cse_case`.`case_id` = :case_id ) 
	WHERE doc.customer_id = :customer_id
	AND doc.deleted =  'N'
	AND `cse_case_document`.deleted =  'N'
	ORDER BY doc.document_date DESC, doc.document_id DESC";
	//AND (`cse_case_document`.attribute_1 = 'letter' OR `cse_case_document`.attribute_1 = 'uploaded')
	//die($sql);
	
	$customer_id = $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getDocuments($case_id, $attribute = "") {
	session_write_close();
	//all the documents, EXCEPT applicant picture AND letters
    $sql = "SELECT DISTINCT doc.`document_id` , doc.`document_uuid` , doc.`parent_document_uuid` , 
	REPLACE(  `document_name` ,  '/home/cstmwb/public_html/autho/web/fileupload/server/php/file_container/" . $case_id . "/',  '' ) `document_name` ,  
	IF (DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p'))`document_date` , 
	IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p'))`received_date` , `doc`.`source`,	
	`document_filename` ,  `document_extension`, `thumbnail_folder` ,  `description` ,  IF (`description_html` IS NULL, '', `description_html`) `description_html` ,  `doc`.`type` ,  `doc`.`verified` , doc.`deleted` , doc.`document_id`  `id` , doc.`document_uuid`  `uuid`, doc.customer_id, 
	IF (`cse_case_document`.attribute_2 = 'scanfiles', 'Scanfile', document_users.last_user_names) user_name,  
	IFNULL(document_users.last_user_attributes, '') `last_user_attributes`,
	`cse_case`.`case_uuid`, `cse_case`.`case_id`, '' preview_path,
	IFNULL(ced.exam_uuid, '') exam_uuid,
	IFNULL(inj.injury_id, '') doi_id, IFNULL(inj.start_date, '') doi_start, IFNULL(inj.end_date, '') doi_end
	
	FROM  `cse_document` doc
	INNER JOIN  `cse_case_document` ON  `doc`.`document_uuid` =  `cse_case_document`.`document_uuid`
	INNER JOIN  `cse_case` ON (  `cse_case_document`.`case_uuid` =  `cse_case`.`case_uuid` 
	AND  `cse_case`.`case_id` = :case_id ) 
				
	LEFT OUTER JOIN `cse_injury_document` `cidocument`
	ON `doc`.`document_uuid` = `cidocument`.`document_uuid` AND cidocument.deleted = 'N'
	LEFT OUTER JOIN `cse_injury` inj
	ON cidocument.injury_uuid = inj.injury_uuid
	
	LEFT OUTER JOIN  (
		SELECT document_uuid, 
        GROUP_CONCAT(ccd.last_update_user SEPARATOR '|') last_users, 
        GROUP_CONCAT(cu1.user_name SEPARATOR '|') last_user_names,
        GROUP_CONCAT(ccd.attribute_1 SEPARATOR '|') last_user_attributes
        FROM `cse_case_document` ccd
		INNER JOIN ikase.`cse_user` cu1
        ON ccd.last_update_user = cu1.user_uuid 
        INNER JOIN `cse_case` ON (  `ccd`.`case_uuid` =  `cse_case`.`case_uuid` 
	AND  `cse_case`.`case_id` = :case_id )
        WHERE ccd.customer_id = :customer_id
		AND ccd.attribute_1 != 'assigned'
		AND ccd.attribute_1 != 'attach'";
		if ($_SESSION["user_customer_id"]!=1072) {
			$sql .= " AND `ccd`.attribute_1 != 'letter'";
		}
    $sql .= " GROUP BY ccd.document_uuid
    ) document_users 
    ON `doc`.`document_uuid` =  document_users.`document_uuid`
	
	LEFT OUTER JOIN cse_exam_document ced
	ON `doc`.`document_uuid` =  ced.`document_uuid` AND ced.deleted = 'N'
			
	LEFT OUTER JOIN  ikase.`cse_user` cu ON cse_case_document.last_update_user = cu.user_uuid 
	
	WHERE doc.customer_id = :customer_id
	AND doc.deleted =  'N'
	AND `cse_case_document`.deleted =  'N'
	AND doc.document_filename != ''";
	
	if ($attribute == "") {
		$sql .= " 
		AND `cse_case_document`.attribute_1 != 'applicant_picture'";
	} else {
		$sql .= " 
		AND `cse_case_document`.attribute_1 = '" . $attribute . "'";
	}
	if ($_SESSION["user_customer_id"]!=1072) {
		$sql .= " 
		AND `cse_case_document`.attribute_1 != 'letter'";
	}
	$sql .= " 
	ORDER BY IF(doc.received_date='0000-00-00 00:00:00', doc.document_date, doc.received_date) DESC, doc.document_id DESC";
	/*
	if ($_SESSION["user_customer_id"]==1033) {
		$sql .= "
		LIMIT 0, 50";
	}*/
	if ($_SERVER['REMOTE_ADDR']=='172.119.228.204') {
		//die($sql);
	}
	
	$customer_id = $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') {
			$arrLength = count($documents);
			for($int=0; $int<$arrLength; $int++) {
				$doc = $documents[$int];
				
				if (strpos($doc->thumbnail_folder, "medium")!==false) {
					$arrFile = explode(".", $doc->document_filename);
					
					$arrFile[count($arrFile) - 1] = "jpg";
					
					$doc_filename = implode(".", $arrFile);
					
					$preview_path = "uploads/" . $customer_id . "/" . str_replace("medium", "thumbnail", $doc->thumbnail_folder) . "/" . $doc_filename;
				} else {
					$preview_path = findDocumentThumbnail($customer_id, $case_id, $doc);
				}
				
				$documents[$int]->preview_path = $preview_path;
			}
			//die(print_r($documents));
			if ($_SERVER['REMOTE_ADDR']=='172.119.228.204') {
				//die(print_r($documents));
			}
		//}
		echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function searchCaseDocuments($case_id, $name) {
	searchDocuments($name, "", "", "", $case_id);
}
function searchDocuments($name, $type, $start, $end, $case_id = "") {
	session_write_close();
	/*
	$name = passed_var("name", "get");
	$type = passed_var("type", "get");
	$start = passed_var("start", "get");
	$end = passed_var("end", "get");
	*/
	//all the documents, EXCEPT applicant picture AND letters
    $sql = "SELECT doc.`document_id` , doc.`document_uuid` , doc.`parent_document_uuid` , 
	`document_name` ,  
	IF (DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p'))`document_date` , 
	IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p'))`received_date` , `doc`.`source`,	
	`document_filename` ,  `document_extension`, `thumbnail_folder` ,  `description` ,  IF (`description_html` IS NULL, '', `description_html`) `description_html` ,  doc.`type` ,  doc.`verified` , doc.`deleted` , doc.`document_id`  `id` , doc.`document_uuid`  `uuid`, doc.customer_id, cu.user_name,  `cse_case`.`case_uuid`, `cse_case`.`case_id`, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, cse_case.case_number
	FROM  `cse_document` doc
	INNER JOIN  `cse_case_document` ON  `doc`.`document_uuid` =  `cse_case_document`.`document_uuid`
	INNER JOIN  ikase.`cse_user` cu ON cse_case_document.last_update_user = cu.user_uuid 
	INNER JOIN  `cse_case` ON (  `cse_case_document`.`case_uuid` =  `cse_case`.`case_uuid`) 
	LEFT OUTER JOIN cse_case_person ccapp ON cse_case.case_uuid = ccapp.case_uuid
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
	WHERE doc.customer_id = :customer_id
	AND doc.deleted =  'N'
	AND `cse_case_document`.deleted =  'N'";
	
	if ($name!="~") {
		$name = str_replace("_", " ", $name);
		$sql .= " AND (`doc`.`document_name` LIKE '%" . addslashes($name) . "%'
		OR `doc`.`document_filename` LIKE '%" . addslashes($name) . "%'
		OR `doc`.`description` LIKE '%" . addslashes($name) . "%'
		OR `doc`.`description_html` LIKE '%" . addslashes($name) . "%'
		)";
	}
	if ($type!="" && $type!="~" && $type!="letters") {
		$sql .= " AND `doc`.`type` = '" . $type . "'";
	}
	if ($type=="letters") {
		$sql .= " AND `cse_case_document`.attribute_1 = 'letter'";
	}
	if ($start!=$end) {
		if ($start!="~") {
			$sql .= " AND CAST(`doc`.`document_date` AS DATE) >= '" . $start . "'";
		}
		if ($end!="~") {
			$sql .= " AND CAST(`doc`.`document_date` AS DATE) <= '" . $end . "'";
		}
	}
	/*
	if ($_SESSION["user_customer_id"]!=1072) {
		$sql .= " AND `cse_case_document`.attribute_1 != 'letter'";
	}
	*/
	$sql .= " 
	ORDER BY `cse_case`.`case_id` ASC, doc.document_date DESC, doc.document_id DESC";
	//die($sql);
	
	$customer_id = $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDocumentsByAttribute($case_id, $attribute) {
	getDocuments($case_id, $attribute);
	return;
	session_write_close();
	
	$kase = getKaseInfo($case_id);
	
    /*
	$sql = "SELECT doc.`document_id` , doc.`document_uuid` , doc.`parent_document_uuid` , 
	REPLACE(  `document_name` ,  '/home/cstmwb/public_html/autho/web/fileupload/server/php/file_container/" . $case_id . "/',  '' ) `document_name` ,  
	IF (DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p'))`document_date` , 
	IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p'))`received_date` , `doc`.`source`, 	
	`document_filename` ,  `document_extension`, `thumbnail_folder` ,  `description` ,  IF (`description_html` IS NULL, '', `description_html`) `description_html` ,  `type` ,  `verified` , doc.`deleted` , doc.`document_id`  `id` , doc.`document_uuid`  `uuid`, doc.customer_id, cu.user_name,  `cse_case`.`case_uuid`, `cse_case`.`case_id`
	FROM  `cse_document` doc
	INNER JOIN  `cse_case_document` ON  (`doc`.`document_uuid` =  `cse_case_document`.`document_uuid` AND `cse_case_document`.`attribute_1` = :attribute)
	INNER JOIN  ikase.`cse_user` cu ON cse_case_document.last_update_user = cu.user_uuid 
	INNER JOIN  `cse_case` ON (  `cse_case_document`.`case_uuid` =  `cse_case`.`case_uuid` 
	AND  `cse_case`.`case_id` = :case_id ) 
	WHERE doc.customer_id = :customer_id
	AND doc.deleted =  'N'
	AND `cse_case_document`.deleted =  'N'
	ORDER BY doc.document_date DESC, doc.document_id DESC";
	*/
	
	//REPLACE(  `document_name` ,  '/home/cstmwb/public_html/autho/web/fileupload/server/php/file_container/" . $case_id . "/',  '' ) 
	//IF (DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p'))`document_date` , 
	//IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p'))`received_date`
	$customer_id = $_SESSION['user_customer_id'];
	$sql = "
	SELECT `cse_case_document`.`attribute_1`, doc.`document_id` , doc.`document_uuid` , doc.`parent_document_uuid` , 
	`document_name` ,  
	DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p') `document_date` , 
	DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') `received_date` , `doc`.`source`, 	
	`document_filename` ,  `document_extension`, `thumbnail_folder` ,  `description` ,  IF (`description_html` IS NULL, '', `description_html`) `description_html` ,  `type` ,  `verified` , doc.`deleted` , doc.`document_id`  `id` , doc.`document_uuid`  `uuid`, doc.customer_id, cu.user_name,  `cse_case_document`.`case_uuid`, '" . $case_id . "'
	FROM  `cse_document` doc
	INNER JOIN  `cse_case_document` 
	ON  `doc`.`document_uuid` =  `cse_case_document`.`document_uuid`
	#INNER JOIN  `cse_case`
	#ON `cse_case_document`.`case_uuid` =  `cse_case`.`case_uuid`
    INNER JOIN  `ikase`.`cse_user` cu 
	ON cse_case_document.last_update_user = cu.user_uuid 
	WHERE 1
	AND doc.customer_id = '" . $customer_id . "'
	AND doc.deleted =  'N'
    AND `cse_case_document`.`attribute_1` = '" . $attribute . "'
	AND `cse_case_document`.`case_uuid` = '" . $kase->uuid . "'
	#AND  `cse_case`.`case_id` = '" . $case_id . "' 
	AND `cse_case_document`.deleted =  'N'
	ORDER BY doc.document_date DESC, doc.document_id DESC";
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.56.2') {
		die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		/*
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("attribute", $attribute);
		*/
		
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}

function getDocumentsByAttributePi($case_id, $attribute) {
	session_write_close();
    $sql = "SELECT doc.`document_id` , doc.`document_uuid` , doc.`parent_document_uuid` , 
	REPLACE(  `document_name` ,  '/home/cstmwb/public_html/autho/web/fileupload/server/php/file_container/" . $case_id . "/',  '' ) `document_name` ,  
	IF (DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p'))`document_date` , 
	IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p'))`received_date` , `doc`.`source`, 	
	`document_filename` ,  `document_extension`, `thumbnail_folder` ,  `description` ,  IF (`description_html` IS NULL, '', `description_html`) `description_html` ,  `type` ,  `verified` , doc.`deleted` , doc.`document_id`  `id` , doc.`document_uuid`  `uuid`, doc.customer_id, cu.user_name,  `cse_case`.`case_uuid`, `cse_case`.`case_id`
	FROM  `cse_document` doc
	INNER JOIN  `cse_pi_document` ON  (`doc`.`document_uuid` =  `cse_pi_document`.`document_uuid` AND `cse_pi_document`.`attribute_1` = :attribute)
	INNER JOIN  ikase.`cse_user` cu ON cse_pi_document.last_update_user = cu.user_uuid 
	INNER JOIN  `cse_case` ON (  `cse_pi_document`.`case_uuid` =  `cse_case`.`case_uuid` 
	AND  `cse_case`.`case_id` = :case_id ) 
	WHERE doc.customer_id = :customer_id
	AND doc.deleted =  'N'
	AND `cse_pi_document`.deleted =  'N'
	ORDER BY doc.document_date DESC, doc.document_id DESC";
	//die($sql);
	
	$customer_id = $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("attribute", $attribute);
		
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getParentDocuments($parent, $id) {
	session_write_close();
    $sql = "SELECT doc.`document_id` , doc.`document_uuid` , doc.`parent_document_uuid` , 
	REPLACE(  `document_name` ,  '/home/cstmwb/public_html/autho/web/fileupload/server/php/file_container/" . $id . "/',  '' ) `document_name` ,  
	IF (DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p'))`document_date`, 
	IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p'))`received_date` , `source`,
	`document_filename` ,  `document_extension`, `thumbnail_folder` ,  `description` ,  IF (`description_html` IS NULL, '', `description_html`) `description_html` ,  doc.`type` ,  doc.`verified` , doc.`deleted` , doc.`document_id`  `id` , doc.`document_uuid`  `uuid`, doc.customer_id, cu.user_name,  `cse_" . $parent . "`.`" . $parent . "_uuid`, `cse_" . $parent . "`.`" . $parent . "_id`
	FROM  `cse_document` doc
	INNER JOIN  `cse_" . $parent . "_document` ON  `doc`.`document_uuid` =  `cse_" . $parent . "_document`.`document_uuid`
	INNER JOIN  ikase.`cse_user` cu ON cse_" . $parent . "_document.last_update_user = cu.user_uuid 
	INNER JOIN  `cse_" . $parent . "` ON (  `cse_" . $parent . "_document`.`" . $parent . "_uuid` =  `cse_" . $parent . "`.`" . $parent . "_uuid` 
	AND  `cse_" . $parent . "`.`" . $parent . "_id` = :id ) 
	WHERE doc.customer_id = :customer_id
	AND doc.deleted =  'N'
	ORDER BY doc.document_id DESC";
	//die($sql);
	
	$customer_id = $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDocument($id) {
	session_write_close();
    $sql = "SELECT doc.`document_id`, doc.`document_uuid`, doc.`parent_document_uuid`, doc.`document_name`, doc.`document_date`, 
		doc.`received_date`, doc.`source`,
		doc.`document_filename`, doc.`document_extension`, doc.`thumbnail_folder`, doc.`description`, doc.`description_html`, 
		doc.`type`, doc.`verified`, doc.`deleted`, 
		IFNULL(inj.injury_id, '') doi_id, IFNULL(inj.start_date, '') doi_start, IFNULL(inj.end_date, '') doi_end,
		doc.`document_id` id, doc.`document_uuid` uuid, doc.customer_id 
		FROM `cse_document` doc
				
		LEFT OUTER JOIN `cse_injury_document` `cidocument`
		ON `doc`.`document_uuid` = `cidocument`.`document_uuid` AND cidocument.deleted = 'N'
		LEFT OUTER JOIN `cse_injury` inj
		ON cidocument.injury_uuid = inj.injury_uuid
		
		WHERE doc.document_id=:id
		AND doc.customer_id = " . $_SESSION['user_customer_id'] . " 
		AND doc.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$document = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($document);
        } else {
            echo $_GET['callback'] . '(' . json_encode($document) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDocumentInfo($id = "", $uuid = "") {
    $sql = "SELECT doc.`document_id`, doc.`document_uuid`, 
			doc.`parent_document_uuid`, doc.`document_name`, `document_date`, 
		doc.`received_date`, doc.`source`,
		doc.`document_filename`, doc.`document_extension`, doc.`thumbnail_folder`, 
		doc.`description`, `description_html`, 
		doc.`type`, doc.`verified`, doc.`deleted`, doc.`document_id` id, 
		doc.`document_uuid` uuid, doc.customer_id,
		IFNULL(ccase.case_id, '') case_id,
		IFNULL(inj.injury_id, '') doi_id, IFNULL(inj.start_date, '') doi_start, IFNULL(inj.end_date, '') doi_end
		
		FROM `cse_document` doc
				
		LEFT OUTER JOIN `cse_injury_document` `cidocument`
		ON `doc`.`document_uuid` = `cidocument`.`document_uuid` AND cidocument.deleted = 'N'
		LEFT OUTER JOIN `cse_injury` inj
		ON cidocument.injury_uuid = inj.injury_uuid
		
		LEFT OUTER JOIN `cse_case_document` ccd
		ON doc.document_uuid = ccd.document_uuid AND ccd.deleted = 'N'
		LEFT OUTER JOIN `cse_case` ccase
		ON ccd.case_uuid = ccase.case_uuid
		WHERE 1 ";
	if ($uuid!="") {
		$sql .= " AND document_uuid=:uuid";
	} else {
		$sql .= " AND document_id=:id";
	}
	$sql .= " AND doc.customer_id = " . $_SESSION['user_customer_id'];
		
		//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($uuid!="") {
			$stmt->bindParam("uuid", $uuid);
		} else {
			$stmt->bindParam("id", $id);
		}
		$stmt->execute();
		$document = $stmt->fetchObject();
		//die(print_r($document));
		return $document;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getStacks() {
	session_write_close();
	
    $sql = "SELECT DISTINCT `document_id`, `document_uuid`, `parent_document_uuid`, `document_name`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, 
	cd.`source`, IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p')) `received_date`,
	`type`, `verified`, cd.`deleted`, `document_id` id, `document_uuid` uuid, REPLACE(cb.filename, '.pdf', '') filename, time_stamp, cd.customer_id, '' `case_id`, '' `case_name`
	FROM `cse_document` cd
	LEFT OUTER JOIN cse_batchscan cb
	ON cd.parent_document_uuid = cb.batchscan_id AND cb.deleted ='N'
	WHERE cd.customer_id = " . $_SESSION['user_customer_id'] . " 
	AND cd.deleted ='N' 
	AND cb.filename IS NOT NULL
	AND (cd.`type` = 'batchscan' OR cd.`type` = 'imported' OR cd.`type` = 'unassigned')";
	
	$sql .= " UNION SELECT DISTINCT `document_id`, cd.`document_uuid`, `parent_document_uuid`, `document_name`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, 
	cd.`source`, IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p')) `received_date`,
	`type`, `verified`, cd.`deleted`, `document_id` id, cd.`document_uuid` uuid, `document_filename` filename, `document_date` time_stamp, cd.customer_id, IFNULL(ccase.`case_id`, '') `case_id`, IFNULL(ccase.`case_name`, CONCAT(ccase.case_number, '-', app.first_name,' ',app.last_name)) `case_name`
	FROM `cse_document` cd
	INNER JOIN `cse_case_document` cdoc
	ON cd.document_uuid = cdoc.document_uuid
	INNER JOIN `cse_case` ccase
	ON cdoc.case_uuid = ccase.case_uuid
	LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
	LEFT OUTER JOIN `cse_person` app ON ccapp.person_uuid = app.person_uuid
	WHERE cd.customer_id = " . $_SESSION['user_customer_id'] . " 
	AND ccase.customer_id = " . $_SESSION['user_customer_id'] . " 
	AND cd.deleted ='N' 
	AND cd.verified = 'N'
	AND cdoc.`attribute_1` = 'uploaded'
	AND cdoc.`attribute_2` = 'scanfiles'
	ORDER BY `document_date` DESC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        
        echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getStackNotifications() {
	getStackByType("batchscan", true);
}
function getStackByType($stack_type = 'batchscan', $blnNotifications = false, $blnUnassigned = false) {
	session_write_close();
	
	//die();
	
    $sql = "SELECT DISTINCT cd.`document_id`, cd.`document_uuid`, cd.`parent_document_uuid`, `document_name`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, 
	cd.`source`, IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p')) `received_date`,
	`type`, `verified`, cd.`deleted`, `document_id` id, cd.`document_uuid` uuid, REPLACE(cb.filename, '.pdf', '') filename, cb.time_stamp, cd.customer_id, IFNULL(`case_id`, '') `case_id`, IFNULL(`case_name`, '') `case_name`,
	CAST(cn.notification_date AS DATE) notification_date, cn.read_date, cn.`notifier`, 
	cn.instructions,
	cu.nickname `uploader_nickname`, 
	cbt.user_logon `uploader`, cbt.dateandtime `upload_time`
	
	FROM `cse_document` cd
	
	INNER JOIN `cse_notification` cn
	ON cd.document_uuid = cn.document_uuid AND notification = 'review' AND cn.deleted = 'N'
	
	INNER JOIN cse_batchscan cb
	ON cd.parent_document_uuid = cb.batchscan_id AND cb.deleted ='N'
	
	INNER JOIN cse_batchscan_track cbt
	ON cb.batchscan_id = cbt.batchscan_id AND cbt.operation = 'insert'
	
	LEFT OUTER JOIN `cse_case_document` cdoc
	ON cd.document_uuid = cdoc.document_uuid AND cdoc.case_uuid != ''
	
	LEFT OUTER JOIN `cse_case` ccase
	ON cdoc.case_uuid = ccase.case_uuid AND cdoc.deleted = 'N'
	
	LEFT OUTER JOIN `ikase`.`cse_user` cu
	ON cbt.user_uuid = cu.user_uuid
	
	WHERE cd.customer_id = " . $_SESSION['user_customer_id'] . " 
	AND cd.deleted ='N'
	";
	if (strpos($stack_type, "orphan") === false) {
		//special case for dholakia, per thomas 4/4/2018
		if ($_SESSION["user_customer_id"]!=1117) {
			$sql .= " 
			AND cn.user_uuid = '"  . $_SESSION['user_id'] . "'";
		}
	}
	$sql .= " 
	AND cb.filename IS NOT NULL";
	
	if (strpos($stack_type, 'batchscan') > -1) {
		//$sql .= " AND (cd.`type` = 'batchscan' OR cd.`type` = 'imported')";
		$sql .= " 
		AND cb.stitched != 'unassigned'";
	}
	if (strpos($stack_type, 'unassigned') > -1) {
		//$sql .= " AND cd.`type` = 'unassigned'";
		$sql .= " 
		AND cb.stitched = 'unassigned'";
	}
	if ($blnNotifications) {
		$sql .= " 
		AND cn.notifier != ''";
	}
	if ($blnUnassigned) {
		$sql .= " 
		AND ccase.case_id IS NULL";
	}
	if (strpos($stack_type, "orphan") > -1) {
		$two_days_ago  = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
		$sql .= " 
		AND cn.`notification_date` < '" . date("Y-m-d", $two_days_ago) . "'";
	}
	if (strpos($stack_type, 'batchscan') > -1) {
		$sql .= " 
		
		UNION 
		
		SELECT DISTINCT `document_id`, cd.`document_uuid`, `parent_document_uuid`, `document_name`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`,
		cd.`source`, IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p')) `received_date`, 
		`type`, `verified`, cd.`deleted`, `document_id` id, cd.`document_uuid` uuid, `document_filename` filename, `document_date` time_stamp, cd.customer_id, IFNULL(ccase.`case_id`, '') `case_id`, IFNULL(ccase.`case_name`, CONCAT(ccase.case_number, '-', app.first_name,' ',app.last_name)) `case_name`,
		CAST(cn.notification_date AS DATE) notification_date, cn.read_date, cn.`notifier`, 
		cn.instructions,
		'system' `uploader_nickname`, 'system' `uploader`, cd.document_date `upload_time`
		
		FROM `cse_document` cd
		
		INNER JOIN `cse_notification` cn
		ON cd.document_uuid = cn.document_uuid AND notification = 'review' AND cn.deleted = 'N'
		
		#INNER JOIN `ikase`.`cse_user` cu
		#ON cn.user_uuid = cu.user_uuid
		INNER JOIN `cse_case_document` cdoc
		ON cd.document_uuid = cdoc.document_uuid AND cdoc.deleted = 'N'
		LEFT OUTER JOIN `cse_case` ccase
		ON cdoc.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
		LEFT OUTER JOIN `cse_person` app ON ccapp.person_uuid = app.person_uuid
		WHERE cd.customer_id = " . $_SESSION['user_customer_id'] . " 
		AND ccase.customer_id = " . $_SESSION['user_customer_id'] . " 
		AND cd.deleted ='N' 
		AND cd.verified = 'N'
		AND cdoc.case_uuid != ''";
		//special case for dholakia, per thomas 4/4/2018
		if ($_SESSION["user_customer_id"]!=1117) {
			$sql .= " 
			AND cn.user_uuid = '"  . $_SESSION['user_id'] . "'";
		}
		if ($blnNotifications) {
			$sql .= " 
			AND cn.notification = 'review'";
		}
		if ($blnUnassigned) {
			$sql .= " 
			AND ccase.case_id IS NULL";
		}
		$sql .= " 
		AND cdoc.`attribute_1` = 'uploaded'
		AND cdoc.`attribute_2` = 'scanfiles'
		ORDER BY `document_id` DESC
		LIMIT 0, 500";
	}
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        
        echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function speedTest($stack_type = 'batchscan') {
	session_write_close();
	die();
    $sql = "SELECT DISTINCT cd.`document_id`, cd.`document_uuid`, cd.`parent_document_uuid`, `document_name`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, 
	cd.`source`, IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p')) `received_date`,
	`type`, `verified`, cd.`deleted`, `document_id` id, cd.`document_uuid` uuid, REPLACE(cb.filename, '.pdf', '') filename, cb.time_stamp, cd.customer_id, IFNULL(`case_id`, '') `case_id`, IFNULL(`case_name`, '') `case_name`,
	cn.notification_date, cn.read_date, cn.`notifier`, cbt.user_logon `uploader`, cbt.dateandtime `upload_time`
	FROM ikase.`cse_document` cd
	INNER JOIN ikase.`cse_notification` cn
	ON cd.document_uuid = cn.document_uuid AND notification = 'review' AND cn.deleted = 'N'
	INNER JOIN `ikase`.`cse_user` cu
	ON cn.user_uuid = cu.user_uuid
	INNER JOIN ikase.cse_batchscan cb
	ON cd.parent_document_uuid = cb.batchscan_id AND cb.deleted ='N'
	INNER JOIN ikase.cse_batchscan_track cbt
	ON cb.batchscan_id = cbt.batchscan_id AND cbt.operation = 'insert'
	LEFT OUTER JOIN (SELECT * FROM ikase.`cse_case_document` WHERE case_uuid != '') cdoc
	ON cd.document_uuid = cdoc.document_uuid
	LEFT OUTER JOIN ikase.`cse_case` ccase
	ON cdoc.case_uuid = ccase.case_uuid AND cdoc.deleted = 'N'
	WHERE cd.customer_id = 1033 
	AND cd.deleted ='N'
	 AND cn.user_uuid = 'dakfjaalkdfj' AND cb.filename IS NOT NULL AND cb.stitched != 'unassigned' 
		
		UNION 
		
		SELECT DISTINCT `document_id`, cd.`document_uuid`, `parent_document_uuid`, `document_name`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`,
		cd.`source`, IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p')) `received_date`, 
		`type`, `verified`, cd.`deleted`, `document_id` id, cd.`document_uuid` uuid, `document_filename` filename, `document_date` time_stamp, cd.customer_id, IFNULL(ccase.`case_id`, '') `case_id`, IFNULL(ccase.`case_name`, CONCAT(ccase.case_number, '-', app.first_name,' ',app.last_name)) `case_name`,
		CAST(cn.notification_date AS DATE) notification_date, cn.read_date, cn.`notifier`, 'system' `uploader`, cd.document_date `upload_time`
		FROM ikase.`cse_document` cd
		INNER JOIN ikase.`cse_notification` cn
		ON cd.document_uuid = cn.document_uuid AND notification = 'review' AND cn.deleted = 'N'
		INNER JOIN `ikase`.`cse_user` cu
		ON cn.user_uuid = cu.user_uuid
		INNER JOIN ikase.`cse_case_document` cdoc
		ON cd.document_uuid = cdoc.document_uuid AND cdoc.deleted = 'N'
		INNER JOIN ikase.`cse_case` ccase
		ON cdoc.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN ikase.cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
		LEFT OUTER JOIN ikase.`cse_person` app ON ccapp.person_uuid = app.person_uuid
		WHERE cd.customer_id = 1033 
		AND ccase.customer_id = 1033 
		AND cd.deleted ='N' 
		AND cd.verified = 'N'
		AND cdoc.case_uuid != ''
		AND cn.user_uuid = 'dakfjaalkdfj'
		AND cdoc.`attribute_1` = 'uploaded'
		AND cdoc.`attribute_2` = 'scanfiles'
		ORDER BY `document_date` DESC";

	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        
        echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getNewScans() {
	session_write_close();
	
	$user_uuid = $_SESSION["user_id"];
	//echo $user_uuid;
	$customer_id = $_SESSION["user_customer_id"];
	
	$three_months = date("Y-m-d", mktime(0, 0, 0, date("m")-3, date("d"),   date("Y")));
	
	$sql = "SELECT DISTINCT cd.`document_id`, cd.`document_uuid`, cd.`parent_document_uuid`, `document_name`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, 
	cd.`source`, IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p')) `received_date`,
	`type`, `verified`, cd.`deleted`, `document_id` id, cd.`document_uuid` uuid, REPLACE(cb.filename, '.pdf', '') filename, cb.time_stamp, cd.customer_id, IFNULL(`case_id`, '') `case_id`, IFNULL(`case_name`, '') `case_name`,
	'' notification_date, cn.read_date, 
	IF(cn.`notifier`='', cu.nickname,  cn.`notifier`) `notifier`, cn.instructions,
	usr.nickname `uploader_nickname`,
	cbt.user_logon `uploader`, cbt.dateandtime `upload_time`
   
	FROM `cse_document` cd
    
    INNER JOIN (
		SELECT cn.document_uuid, COUNT(cn.notification_id) the_count, GROUP_CONCAT(cn.notification)

		FROM cse_notification cn

		INNER JOIN `cse_document` cd
		ON cn.document_uuid = cd.document_uuid AND cd.deleted = 'N'

		INNER JOIN `cse_batchscan` cb
		ON cd.parent_document_uuid = cb.batchscan_id

		INNER JOIN cse_batchscan_track cbt
		ON cb.batchscan_id = cbt.batchscan_id
			
		WHERE 1
		AND cbt.operation = 'insert' AND cbt.user_uuid = :user_uuid
        AND cd.customer_id = :customer_id
		  
		GROUP BY cn.document_uuid
		HAVING COUNT(cn.notification_id) = 1 AND GROUP_CONCAT(cn.notification) != 'completed'
    ) new_scans
    ON cd.document_uuid = new_scans.document_uuid
    
    INNER JOIN `cse_batchscan` cb
    ON cd.parent_document_uuid = cb.batchscan_id
    
    INNER JOIN cse_batchscan_track cbt
	ON cb.batchscan_id = cbt.batchscan_id AND cbt.user_uuid = :user_uuid AND cbt.operation = 'insert'
    
    INNER JOIN ikase.cse_user usr
    ON cbt.user_uuid = usr.user_uuid AND usr.user_uuid = :user_uuid
    
	LEFT OUTER JOIN `cse_notification` cn
	ON cd.document_uuid = cn.document_uuid
	
    LEFT OUTER JOIN ikase.cse_user cu
    ON cn.user_uuid = cu.user_uuid
    
    LEFT OUTER JOIN `cse_case_document` cdoc
	ON cd.document_uuid = cdoc.document_uuid AND cdoc.case_uuid != ''
	LEFT OUTER JOIN `cse_case` ccase
	ON cdoc.case_uuid = ccase.case_uuid AND cdoc.deleted = 'N'
	
	WHERE cd.document_date > :three_months
	";
	
	try {
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("three_months", $three_months);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        
        echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	
	//getStackByType("batchscan", false, true);
}
function getMyStackByType($stack_type = 'batchscan') {
	session_write_close();
	
	//die();
	
    $sql = "SELECT distinct cd.`document_id`, cd.`document_uuid`, `parent_document_uuid`, `document_name`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, 
	cd.`source`, IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p')) `received_date`,
	`type`, `verified`, cd.`deleted`, `document_id` id, cd.`document_uuid` uuid, REPLACE(cb.filename, '.pdf', '') filename, cb.time_stamp, cd.customer_id, IFNULL(ccase.case_id, '') `case_id`, IFNULL(ccase.case_name, '') `case_name`,
	CAST(cn.notification_date AS DATE) notification_date, cn.read_date, cn.`notifier`, 
	cu.nickname `uploader_nickname`, 
	cbt.user_logon `uploader`, cbt.dateandtime `upload_time`
	FROM `cse_notification` cn
	INNER JOIN `cse_document` cd
	ON cn.document_uuid = cd.document_uuid AND cn.notification = 'review' AND cn.deleted = 'N'";
	if (strpos($stack_type, "orphan") === false) {
	$sql .= " 
		INNER JOIN `ikase`.`cse_user` cu
		ON cn.user_uuid = cu.user_uuid
		AND cu.user_uuid = '"  . $_SESSION['user_id'] . "'";
	}
	$sql .= " 
	INNER JOIN cse_batchscan cb
	ON cd.parent_document_uuid = cb.batchscan_id AND cb.deleted ='N'
	INNER JOIN cse_batchscan_track cbt
	ON cb.batchscan_id = cbt.batchscan_id
	LEFT OUTER JOIN cse_case_document ccd
	ON ccd.document_uuid = cd.document_uuid
	LEFT OUTER JOIN cse_case ccase
	ON ccd.case_uuid = ccase.case_uuid
	WHERE 
		cd.customer_id = " . $_SESSION['user_customer_id'] . " 
		AND cd.deleted ='N' 
		AND cbt.operation = 'insert'";

	$sql .= "
		AND cb.filename IS NOT NULL";
	if ($stack_type == 'batchscan') {
		//$sql .= " AND (cd.`type` = 'batchscan' OR cd.`type` = 'imported')";
		$sql .= "
			AND cb.stitched != 'unassigned'";
	}
	if ($stack_type == 'unassigned') {
		//$sql .= " AND cd.`type` = 'unassigned'";
		$sql .= "
			AND cb.stitched = 'unassigned'";
	}
	if (strpos($stack_type, "orphan") > -1) {
		$two_days_ago  = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
		$sql .= " 
		AND cn.`notification_date` < '" . date("Y-m-d", $two_days_ago) . "'";
	}
	$sql .= " 
	ORDER BY cn.read_date ASC, cd.document_date DESC";
	if ($_SERVER['REMOTE_ADDR'] == "47.153.50.152") {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        
        echo json_encode($documents);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function openArchiveMsg() {
	$filename = passed_var("filename", "post");
	$content = file_get_contents($filename);
	
	die($content);
}
function getLargeArchives($case_id) {
	session_write_close();
	try {
		$db = getConnection();
		
		//lookup the customer name
		$sql_customer = "SELECT data_source
		FROM  `ikase`.`cse_customer` 
		WHERE customer_id = :customer_id AND deleted = 'N'";
		
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		//die(print_r($customer));
		$data_source = $customer->data_source;
		
		if ($data_source!="") {
			$sql = "show databases
			WHERE `Database` = '" . $data_source . "_docs'";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$databases = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			if (count($databases) == 0) {
				$documents = new stdClass();
				echo json_encode($documents);
				die();
			}
			
			$sql = "SELECT DISTINCT `TABLE_SCHEMA`, `TABLE_NAME`
			FROM `INFORMATION_SCHEMA`.`COLUMNS`
			WHERE `TABLE_SCHEMA` = '" . $data_source . "_docs'
			AND `TABLE_NAME` LIKE 'docs%'
			AND `TABLE_NAME` NOT LIKE '%lib'
			AND `TABLE_NAME` NOT LIKE '%32'
			ORDER BY `TABLE_NAME`";
			
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$tables = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			//die(print_r($tables));
			$arrTables = array();
			foreach($tables as $table) {
				$table_number = $table->TABLE_NAME;
				$table_number = str_replace("docs", "", $table_number);
				
				$sql = "SELECT DATE_FORMAT(`date`, '%m/%d/%Y') `document_date`, `date`,
				`form_desc` `document_name`,
				`docs`.`cpointer` `uuid`,
				`docs`.`recno`, '' `id`,
				'" . $table_number . "' doc_number
				FROM `" . $data_source . "_docs`.`" . $table->TABLE_NAME . "` `docs`
				INNER JOIN cse_case ccase 
				ON docs.cpointer = ccase.cpointer
				WHERE `ccase`.`case_id` = :case_id
				AND  `ccase`.`customer_id` = :customer_id";	
				
				$arrTables[] = $sql;
			}
			
			$sql = implode("
			UNION
			", $arrTables);
			
			$sql_archive = "SELECT DISTINCT `TABLE_SCHEMA`, `TABLE_NAME`
			FROM `INFORMATION_SCHEMA`.`COLUMNS`
			WHERE `TABLE_SCHEMA` = '" . $data_source . "_docs'
			AND `TABLE_NAME` = 'archive'
			ORDER BY `TABLE_NAME`";
			
			$stmt = $db->prepare($sql_archive);
			$stmt->execute();
			$archive = $stmt->fetchObject();
			
			if (is_object($archive)) {
				$sql .= "
				UNION
				
				SELECT DATE_FORMAT(arch.archive_date, '%m/%d/%Y') `document_date`, arch.archive_date `date`,
				`description` `document_name`, arch.cpointer `uuid`,
				arch.`recno`, '' `id`,
				CONCAT('A', arch.archive_number) doc_number
				FROM " . $data_source . "_docs.archive arch
				INNER JOIN ikase_" . $data_source . ".cse_case ccase
				ON arch.cpointer = ccase.cpointer
				
				WHERE `ccase`.case_id = :case_id
				AND  `ccase`.`customer_id` = :customer_id";
				
			}
		
			$sql .= "
			ORDER BY `date` DESC";
			if ($_SERVER['REMOTE_ADDR'] == "47.153.49.248") {
				//die($sql);
			}
			$stmt = $db->prepare($sql);
			$stmt->bindParam("case_id", $case_id);
			$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
			$stmt->execute();
			
			$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			foreach($documents as $dindex=>$doc) {
				$documents[$dindex]->id = $dindex;
			}
			echo json_encode($documents);
			
		}
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getArchives($case_id) {
	session_write_close();
	$db = getConnection();
	
	//lookup the customer name
	$sql_customer = "SELECT data_source
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	//die(print_r($customer));
	$data_source = $customer->data_source;
	
	
	try {
		$sql = "show databases
		WHERE `Database` = '" . $data_source . "_docs'";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$databases = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		if (count($databases) == 0) {
			$documents = new stdClass();
			echo json_encode($documents);
			die();
		}
		$sql = "SELECT DATE_FORMAT(`date`, '%m/%d/%Y') `document_date`, `date`,
		`form_desc` `document_name`,
		`docs`.`cpointer` `uuid`,
		`docs`.`recno` `id`,
		'' doc_number
		FROM `" . $data_source . "_docs`.`docs` `docs`
		INNER JOIN cse_case ccase 
		ON docs.cpointer = ccase.cpointer
		WHERE `ccase`.`case_id` = :case_id
		AND  `ccase`.`customer_id` = :customer_id
		
		UNION
		
		SELECT DATE_FORMAT(`date`, '%m/%d/%Y') `document_date`, `date`,
		`form_desc` `document_name`,
		`docs`.`cpointer` `uuid`,
		`docs`.`recno` `id`,
		'2' doc_number
		FROM `" . $data_source . "_docs`.`docs2` `docs`
		INNER JOIN cse_case ccase 
		ON docs.cpointer = ccase.cpointer
		WHERE `ccase`.`case_id` = :case_id
		AND  `ccase`.`customer_id` = :customer_id
		
		UNION
		
		SELECT DATE_FORMAT(`date`, '%m/%d/%Y') `document_date`, `date`,
		`form_desc` `document_name`,
		`docs`.`cpointer` `uuid`,
		`docs`.`recno` `id`,
		'3' doc_number
		FROM `" . $data_source . "_docs`.`docs3` `docs`
		INNER JOIN cse_case ccase 
		ON docs.cpointer = ccase.cpointer
		WHERE `ccase`.`case_id` = :case_id
		AND  `ccase`.`customer_id` = :customer_id
		
		UNION
		
		SELECT DATE_FORMAT(`date`, '%m/%d/%Y') `document_date`, `date`,
		`form_desc` `document_name`,
		`docs`.`cpointer` `uuid`,
		`docs`.`recno` `id`,
		'4' doc_number
		FROM `" . $data_source . "_docs`.`docs4` `docs`
		INNER JOIN cse_case ccase 
		ON docs.cpointer = ccase.cpointer
		WHERE `ccase`.`case_id` = :case_id
		AND  `ccase`.`customer_id` = :customer_id
		
		UNION
		
		SELECT DATE_FORMAT(`date`, '%m/%d/%Y') `document_date`, `date`,
		`form_desc` `document_name`,
		`docs`.`cpointer` `uuid`,
		`docs`.`recno` `id`,
		'5' doc_number
		FROM `" . $data_source . "_docs`.`docs5` `docs`
		INNER JOIN cse_case ccase 
		ON docs.cpointer = ccase.cpointer
		WHERE `ccase`.`case_id` = :case_id
		AND  `ccase`.`customer_id` = :customer_id
		
		UNION
		
		SELECT DATE_FORMAT(`date`, '%m/%d/%Y') `document_date`, `date`,
		`form_desc` `document_name`,
		`docs`.`cpointer` `uuid`,
		`docs`.`recno` `id`,
		'6' doc_number
		FROM `" . $data_source . "_docs`.`docs6` `docs`
		INNER JOIN cse_case ccase 
		ON docs.cpointer = ccase.cpointer
		WHERE `ccase`.`case_id` = :case_id
		AND  `ccase`.`customer_id` = :customer_id
		
		ORDER BY `date` DESC";
		//die($sql);
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($documents));
		
		// Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($documents);
        } else {
            echo $_GET['callback'] . '(' . json_encode($documents) . ');';
        }
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	$stmt->closeCursor(); $stmt = null; $db = null;
}
function getTemplateByFileName($filename) {
	session_write_close();
	
	$sql = "SELECT * FROM cse_document
	WHERE document_filename = :filename
	AND `type` = 'template'
	AND customer_id = :customer_id";
	
	$customer_id = $_SESSION['user_customer_id'];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("filename", $filename);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$template = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
        
        echo json_encode($template);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getInvoiceTemplates() {
	getTemplates(true);
}
function getTemplatesByType($case_type) {
	getTemplates(false, $case_type);
}
function getTemplates($blnInvoices = false, $case_type = "") {
	session_write_close();
	/*
	IFNULL(kinv.template_name, 'N') kinvoice_name,
		IFNULL(kinv.kinvoice_id, 'N') kinvoice_id,
	*/
	/*
	LEFT OUTER JOIN cse_document_kinvoice cdk
	ON cd.document_uuid = cdk.document_uuid
	LEFT OUTER JOIN cse_kinvoice kinv
	ON cdk.kinvoice_uuid = kinv.kinvoice_uuid
	*/
		//Solulab code start 24-06-2019
		
		$sql = "
		SELECT cse_docucents.`docucents_id`,cse_docucents.`case_id`,cse_docucents.`vendor_submittal_id`, cse_docucents.`customer_id`, cse_docucents.`document_submitted_by`,cse_docucents.`docucents_upload_date`, cd.`document_id` `id`, cd.`document_id`, cd.`document_uuid`, `parent_document_uuid`, 		
			`document_name`, `document_filename`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, 
			IF(`document_extension`='Any', 'Letters to Any Party', document_extension) document_extension, 
			`thumbnail_folder`, `description`, IFNULL(description_html, '') `description_html`, `received_date`, `source`,
			`type`, `verified`, cd.`deleted`, cd.`document_id` id, cd.`document_uuid` uuid, cd.customer_id, 
			'' document_uuids, '' document_names, '' document_dates, '' document_users
		FROM `cse_document` cd
		LEFT JOIN cse_docucents ON cd.document_id = cse_docucents.document_id 
		WHERE cd.customer_id = :customer_id
		AND cd.deleted ='N' 
		AND cd.`type` = 'template'";
		//Solulab code end 24-06-2019
    $sql = "
	SELECT cd.`document_id` `id`, cd.`document_id`, cd.`document_uuid`, `parent_document_uuid`, 		
		`document_name`, `document_filename`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, 
		IF(`document_extension`='Any', 'Letters to Any Party', document_extension) document_extension, 
		`thumbnail_folder`, `description`, IFNULL(description_html, '') `description_html`, `received_date`, `source`,
		`type`, `verified`, cd.`deleted`, cd.`document_id` id, cd.`document_uuid` uuid, cd.customer_id, 
		'' document_uuids, '' document_names, '' document_dates, '' document_users
	FROM `cse_document` cd

	WHERE cd.customer_id = :customer_id
	AND cd.deleted ='N' 
	AND cd.`type` = 'template'";
	if ($blnInvoices) {
		$sql .= "
		AND cd.`document_extension` = 'Invoice'
		";
	}
	if ($case_type!="") {
		$sql .= "
		AND (cd.`description_html` = :case_type OR cd.`description_html` IS NULL OR cd.`description_html` = '')
		";
	}
	$sql .= "
	ORDER BY IF(`document_extension`='Any', 'Letters to Any Party', document_extension), document_name";

	$customer_id = $_SESSION['user_customer_id'];
	
	if ($_SERVER['REMOTE_ADDR']=="47.153.49.248") {
	//	die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($case_type!="") {
			$stmt->bindParam("case_type", $case_type);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$templates = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        
        echo json_encode($templates);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function propagateTemplate() {
	session_write_close();
	
	$document_id = passed_var("document_id", "post");
	$destination_cus_id = passed_var("destination_cus_id", "post");
	
	//we need basic document info to copy it to the uploads folder
	$document = getDocumentInfo($document_id);
	$document_filename = $document->document_filename;
	if (!is_numeric($destination_cus_id)) {
		if (strtolower($destination_cus_id)!="all") {
			die("not all");
		}
	}
	
	if (!is_numeric($destination_cus_id)) {
		$sql = "SELECT DISTINCT customer_id, data_source 
		FROM ikase.cse_customer
		WHERE deleted = 'N'
		AND customer_id != '1033'
		ORDER BY customer_id";
	} else {
		$sql = "SELECT DISTINCT customer_id, data_source 
		FROM ikase.cse_customer
		WHERE customer_id = '" . $destination_cus_id . "'";
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$customers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		die();
	}
	//die(print_r($customers));
	foreach($customers as $cus) {
		$destination_cus_id = $cus->customer_id;
		//make sure the file does not exist
		$destination_filename = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $destination_cus_id . "\\templates\\" . $document_filename;
		if (file_exists($destination_filename)) {
			continue;
		}
		$destdir = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $destination_cus_id;
		if (!file_exists($destdir)) {
			mkdir($destdir, 0777);
		}
		$destdir .=  "\\templates\\";
		if (!file_exists($destdir)) {
			mkdir($destdir, 0777);
		}
		//get the database for this customer
		$sql_customer = "SELECT cus_name, data_source, permissions
		FROM  `ikase`.`cse_customer` 
		WHERE customer_id = :customer_id AND deleted = 'N'";
		try {				
			$db = getConnection();
			$stmt = $db->prepare($sql_customer);
			$stmt->bindParam("customer_id", $destination_cus_id);
			$stmt->execute();
			$customer = $stmt->fetchObject();
			
			//echo print_r($customer);
			if (!is_object($customer)) {
				die("no go");
			}
			$cus_name = $customer->cus_name;
			$data_source = $customer->data_source;
			$db_name = "ikase";
			
			if ($data_source!="") {
				$db_name = "ikase_" . $data_source;
			}
			$db = null;
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		$sql = "INSERT INTO `" . $db_name . "`.cse_document (
		`document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, 
		`thumbnail_folder`, `description`, `description_html`, `source`, `received_date`, `type`, `verified`, `customer_id`, `deleted`
		)
		SELECT doc.`document_uuid`, doc.`parent_document_uuid`, doc.`document_name`, 
		doc.`document_date`, doc.`document_filename`, doc.`document_extension`, doc.`thumbnail_folder`, 
		doc.`description`, doc.`description_html`, doc.`source`, doc.`received_date`, doc.`type`, 
		doc.`verified`, '" . $destination_cus_id . "', doc.`deleted`
		FROM `ikase`.cse_document doc
		LEFT OUTER JOIN `" . $db_name . "`.cse_document odoc
		ON doc.document_uuid = odoc.document_uuid
		WHERE doc.customer_id = 1033
		AND doc.document_id = '" . $document_id . "'
		AND odoc.document_id IS NULL";
		
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			//make sure the folder exists
			
			//now copy the file
			copy($_SERVER['DOCUMENT_ROOT'] . "\\uploads\\1033\\templates\\" . $document_filename, $destination_filename);
			 	
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
	echo json_encode(array("success"=>"true", "id"=>$document_id, "filename"=>$document_filename));
}
function getKaseLettersList($case_id, $blnInvoices = false) {
	session_write_close();
	if (!is_numeric($case_id)) {
		$error = array("error"=> array("text"=>"invalid id"));
        echo json_encode($error);
		die();
	}
	$sql = "SELECT letters.document_id, letters.document_uuid, letters.parent_document_uuid, letters.document_date, letters.document_name, letters.document_filename, letters.description, IFNULL(letters.description_html, '') `description_html`, letters.document_extension, cdt.user_logon document_user,
	IFNULL(track_count, 0) `macro_updates`, IFNULL(track_date, '') `macro_dates`
	FROM  `cse_document` template
	INNER JOIN `cse_document` letters ON 
	template.document_uuid = letters.parent_document_uuid AND letters.deleted ='N'
	INNER JOIN `cse_document_track` cdt ON 
	letters.document_uuid = cdt.document_uuid AND cdt.operation = 'insert'
	INNER JOIN `cse_case_document` ccd  ON 
	(ccd.document_uuid = letters.document_uuid AND ccd.attribute_1 =  'letter') 
	INNER JOIN `cse_case` ccase ON 
	ccd.case_uuid = ccase.case_uuid
	LEFT OUTER JOIN (
		SELECT document_uuid, COUNT(document_track_id) track_count, MAX(time_stamp) track_date
		FROM cse_document_track  
		WHERE operation = 'macro_update'
		GROUP BY document_uuid
	) macros
	ON letters.document_uuid = macros.document_uuid
	WHERE template.customer_id = " . $_SESSION['user_customer_id'] . " 
	AND ccase.`case_id` = " . $case_id;
	if ($blnInvoices) {
		$sql .= "
		AND template.document_extension = 'Invoice'";
	}
	$sql .= "
	ORDER BY letters.document_date DESC";

	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		//$stmt->bindParam("case_id", $case_id);
		$letters = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        
        echo json_encode($letters);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInvoiceLetters($case_id) {
	getKaseLetters($case_id, true);
}
function getKaseLetters($case_id, $blnInvoices = false) {
	session_write_close();
	if (!is_numeric($case_id)) {
		$error = array("error"=> array("text"=>"invalid id"));
        echo json_encode($error);
		die();
	}
	
	//if ($_SERVER['REMOTE_ADDR']=='71.116.242.3') {
		getKaseLettersList($case_id, $blnInvoices);
		return;
	//}
	$sql = "SELECT @curRow := @curRow + 1 AS id, ccd.`case_uuid` , letters.parent_document_uuid, template.document_id, template.document_uuid, template.document_filename, template.document_name, template.document_extension, template.description, IFNULL(template.description_html, '') `description_html`, GROUP_CONCAT( letters.document_uuid
SEPARATOR  '|' ) document_uuids, GROUP_CONCAT( letters.document_name
SEPARATOR  '|' ) document_names, GROUP_CONCAT( letters.document_date
SEPARATOR  '|' ) document_dates,
GROUP_CONCAT( cdt.user_logon
SEPARATOR  '|' ) document_users
FROM  `cse_document` template
JOIN    (SELECT @curRow := 0) r
LEFT OUTER JOIN `cse_document` letters ON 
template.document_uuid = letters.parent_document_uuid AND letters.deleted ='N'
LEFT OUTER JOIN `cse_case_document` ccd  ON 
(ccd.document_uuid = letters.document_uuid AND ccd.attribute_1 =  'letter') 
LEFT OUTER JOIN `cse_document_track` cdt ON 
(letters.document_uuid = cdt.document_uuid AND cdt.operation = 'insert')
LEFT OUTER JOIN `cse_case` ccase ON (ccd.case_uuid = ccase.case_uuid AND ccase.`case_id` = " . $case_id . ")
WHERE 1
AND template.deleted ='N'
AND letters.deleted ='N' 
AND template.type =  'template'
AND template.customer_id = " . $_SESSION['user_customer_id'] . " 
GROUP BY ccd.`case_uuid` , letters.parent_document_uuid, template.document_id
ORDER BY template.document_name, ccd.`case_uuid` DESC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		//$stmt->bindParam("case_id", $case_id);
		$letters = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        
        echo json_encode($letters);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function listNotifications($document_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT usr.user_name, usr.user_id, noti.notification_id id, UPPER(noti.notifier) notifier, UPPER(usr.nickname) notifiee, noti.notification_date, noti.notification
	FROM cse_notification noti
	INNER JOIN cse_document doc
	ON noti.document_uuid = doc.document_uuid
	
	INNER JOIN ikase.cse_user usr
	ON noti.user_uuid = usr.user_uuid
	
	WHERE doc.document_id = :document_id
	AND doc.customer_id = :customer_id
	ORDER BY notification_date ASC, notification_id ASC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("document_id", $document_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$notifs = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        
        echo json_encode($notifs);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function readDocument() {
	session_write_close();
	$id = passed_var("id", "post");
	$document = getDocumentInfo($id);
	
	$sql = "UPDATE cse_notification 
			SET read_date = '" . date("Y-m-d H:i:s") . "'
			WHERE document_uuid= :uuid
			AND user_uuid = '" . $_SESSION["user_id"] . "'
			AND customer_id = " . $_SESSION["user_customer_id"];
		//die($sql);
	try {
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("uuid", $document->document_uuid);
		$stmt->execute();
		$stmt = null; $db = null;
		trackDocument("read", $id, "");
		echo json_encode(array("success"=>"document marked as read"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function unreadDocument() {
	$id = passed_var("id", "post");
	$document = getDocumentInfo($id);
	
	$sql = "UPDATE cse_notification 
			SET read_date = '0000-00-00 00:00:00'
			WHERE document_uuid= :uuid
			AND user_uuid = '" . $_SESSION["user_id"] . "'
			AND customer_id = " . $_SESSION["user_customer_id"];
		//die($sql);
	try {
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("uuid", $document->document_uuid);
		$stmt->execute();
		$stmt = null; $db = null;
		trackDocument("unread", $id, "");
		echo json_encode(array("success"=>"document marked as unread"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteDocument() {
	$ids = passed_var("id", "post");
	$arrIDs = explode(", ", $ids);
	
	foreach($arrIDs as $id) {
		$document = getDocumentInfo($id);
		//$document_path = "uploads/1033/templates/1000%20General%20Letter.docx";
		
		$sql = "UPDATE cse_document 
				SET deleted = 'Y'
				WHERE document_id= :id
				AND customer_id = " . $_SESSION["user_customer_id"];
			//die($sql);
		try {
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("id", $id);
			$stmt->execute();
			$stmt = null; $db = null;
			trackDocument("delete", $id, "");
			
			if (isset($_POST["remove_item"])) {
				$remove_item = passed_var("remove_item", "post");
				if ($remove_item=="letter") {
					$letter_folder = $_SERVER["DOCUMENT_ROOT"] . "\\uploads\\" . $_SESSION["user_customer_id"] . "\\templates\\";
					
					$document_path = $letter_folder . $document->document_filename;
					//echo $document_path;
					unlink($document_path);
					//die("unlink(" . $document_path . ")");
				}
	
			}
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
				echo json_encode($error);
		}
	}
	echo json_encode(array("success"=>"document marked as deleted"));
}
function addAbacusDocument() {
	if ($_SERVER['REMOTE_ADDR']!="173.58.194.150" && $_SERVER['REMOTE_ADDR']!="173.58.194.146" && $_SERVER['REMOTE_ADDR']!="173.58.194.148" && $_SERVER['REMOTE_ADDR']!="71.106.134.58") {
		echo $_SERVER['REMOTE_ADDR'] . "\r\n";
		die("no go...");
	}
	
	$customer_id = passed_var("customer_id", "post");
	$document_filename = passed_var("document_filename", "post");
	$thumbnail_folder = passed_var("thumbnail_folder", "post");

	if (!is_numeric($customer_id)) {
		die("no id");
	}
	$_SESSION['user_customer_id'] = $customer_id;
	$_SESSION['mode'] = 'abacus';
	$_SESSION['user_id'] = 'system';
	
	session_write_close();
	
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id AND deleted = 'N'";
	try {				
		$db = getConnection();
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		
		//die(print_r($customer));
		if (!is_object($customer)) {
			die("no go");
		}
		$cus_name = $customer->cus_name;
		$data_source = $customer->data_source;
		if ($data_source=="") {
			$return = "ikase";
		}
		if ($data_source!="") {
			$return = "ikase_" . $data_source;
		}
		$db = null;
		
		if (strlen($thumbnail_folder) > 0) {
			//look up the case based on applicant first and last name
			$kase = getAbacusInfo($thumbnail_folder, $data_source);
			
			$case_uuid = $kase->uuid;
			$_POST["case_uuid"] = $case_uuid;
		}
		
		addDocument($return);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function addRemoteDocument() {
	if ($_SERVER['REMOTE_ADDR']!="173.58.194.150" && $_SERVER['REMOTE_ADDR']!="173.58.194.146" && $_SERVER['REMOTE_ADDR']!="173.58.194.148" && $_SERVER['REMOTE_ADDR']!="71.106.134.58") {
		//echo $_SERVER['REMOTE_ADDR'] . "\r\n";
		if ($_SERVER["HTTP_X_ORIGINAL_URL"] != "/api/jetfile/acceptpdf") {
			die("no go...");
		}
	}
	
	$customer_id = passed_var("customer_id", "post");
	$user_id = passed_var("user_id", "post");
	$document_filename = passed_var("document_filename", "post");
	$parent_document_uuid = passed_var("parent_document_uuid", "post");
	
	if (!is_numeric($customer_id)) {
		die("no id");
	}
	$_SESSION['user_customer_id'] = $customer_id;
	if (is_numeric($user_id)) {
		//look up the uuid
		$user = getUserInfo($user_id);
		//die(print_r($user));
		if (!is_object($user)) {
			die("no user");
		}
		$user_id = $user->user_uuid;
	}
	$_SESSION['user_id'] = $user_id;
	
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id AND deleted = 'N'";
	try {				
		$db = getConnection();
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		
		//die(print_r($customer));
		if (!is_object($customer)) {
			die("no go");
		}
		$cus_name = $customer->cus_name;
		$data_source = $customer->data_source;
		if ($data_source=="") {
			$return = "ikase";
		}
		if ($data_source!="") {
			$return = "ikase_" . $data_source;
		}
		$_SESSION["return"] = $return;
		$db = null;
		
		//is the document already in the system?
		$sql = "SELECT document_id 
		FROM `" . $return . "`.`cse_document`
		WHERE `document_filename` = :document_filename
		AND `parent_document_uuid` = :parent_document_uuid
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("parent_document_uuid", $parent_document_uuid);
		$stmt->bindParam("document_filename", $document_filename);
		$stmt->execute();
		$document = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if (is_object($document)) {
			echo json_encode(array("success"=>"true", "id"=>$document->document_id, "filename"=>$document_filename)); 
		} else {
			addDocument($return);
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function addDocument($db_name = "") {
	if ($db_name == "" && isset($_SESSION["return"])) {
		unset($_SESSION["return"]);
	}
	session_write_close();
	$request = Slim::getInstance()->request();
	if (isset($_POST["document_uuid"])) {
		$document_uuid = $_POST["document_uuid"];
	} else {
		$document_uuid = uniqid("KS");
	}
	$parent_document_uuid = "";
	if (isset($_POST["parent_document_uuid"])) {
		$parent_document_uuid = $_POST["parent_document_uuid"];
		if ($parent_document_uuid=="") {
			$parent_document_uuid = $document_uuid;
		}
	}
	$thumbnail_folder = "";
	
	$sql = "INSERT INTO ";
	if ($db_name!="") {
		$thumbnail_folder = $_POST["thumbnail_folder"];
		$sql .= "`" . $db_name . "`.";
	}
	$sql .= "cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, thumbnail_folder, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :thumbnail_folder, :verified, :customer_id)";
	$sql_insert = $sql;
	try {
		$customer_id = $_SESSION['user_customer_id'];
		$document_filename = $_POST["document_filename"];
		
		//we need to clear out any previous document of same type and description if jetfile
		$type = $_POST["type"];
		$blnJetfileAdd = ($type=="App_for_ADJ" || $type=="DOR" || $type=="DORE" || $type=="LIEN");
		if ($blnJetfileAdd) {
			$db = getConnection();
			$sql_clear = "UPDATE 
				cse_document doc,
				cse_injury_document ccd,
				cse_injury inj 
			SET 
				doc.deleted = 'Y'
			WHERE
				doc.document_uuid = ccd.document_uuid
				AND ccd.injury_uuid = inj.injury_uuid
				AND `doc`.`type` = :type
				AND `description` = :description
				AND `doc`.customer_id = :customer_id
				AND `doc`.deleted = 'N'
				AND inj.injury_id = :injury_id";
			$stmt = $db->prepare($sql_clear);  
			
			$stmt->bindParam("type", $type);
			$stmt->bindParam("description", $_POST["description"]);
			$stmt->bindParam("injury_id", $_POST["injury_id"]);
			$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
			
			$stmt->execute();
			$stmt = null; $db = null;
		}
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("document_uuid", $document_uuid);
		$stmt->bindParam("parent_document_uuid", $parent_document_uuid);
		$stmt->bindParam("document_name", $_POST["document_name"]);
		$stmt->bindParam("document_date", $_POST["document_date"]);
		$stmt->bindParam("document_filename", $document_filename);
		$document_extension = $_POST["document_extension"];
		if ($document_extension=="") {
			$document_extension = "Document";
		}
		$stmt->bindParam("document_extension", $document_extension);
		$stmt->bindParam("description", $_POST["description"]);
		$stmt->bindParam("description_html", $_POST["description_html"]);
		if (!isset($_POST["attribute"])) {
			$_POST["attribute"] = "";
		}
		if ($_POST["attribute"] == "personal_injury_picture") {
			$stmt->bindParam("type", $_POST["attribute_2"]);
		} else {
			$stmt->bindParam("type", $type);
		}
		//$thumbnail_folder = $_POST["thumbnail_folder"];
		$thumbnail_folder = passed_var("thumbnail_folder", "post");
		$stmt->bindParam("thumbnail_folder", $thumbnail_folder);
		$stmt->bindParam("verified", $_POST["verified"]);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		//we're only passing dbname from remote
		if ($db_name=="") {
			trackDocument("insert", $new_id);
		}
		// && $_SESSION['mode']!="abacus"
		//let's notify if dbname is passed
		if ($db_name!="") {
			$notification_uuid = uniqid("KN", false);
			$sql = "INSERT INTO `" . $db_name . "`.`cse_notification` 
			(`document_uuid`, `notification_uuid`, `user_uuid`, `notification`, `notification_date`, `customer_id`)
			VALUES ('" . $document_uuid . "', '" . $notification_uuid . "', '" . $_SESSION['user_id'] . "','review', '" . date("Y-m-d H:i:s") . "', '" . $customer_id . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$stmt = null; $db = null;
		}
		echo json_encode(array("success"=>"true", "id"=>$new_id, "filename"=>$document_filename)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	//attach to case
	$case_uuid = "";
	if ( isset($_POST["case_uuid"])) {
		//$case_uuid = $_POST["case_uuid"];
		$case_uuid = passed_var("case_uuid", "post");
	}
	$case_id = passed_var("case_id", "post");
	if ($case_uuid=="" && $case_id > 0) {
		$kase = getKaseInfo($case_id);
		//die(print_r($kase));
		$case_uuid = $kase->uuid;
	}
		
	if ($case_uuid != "") {
		$cd_uuid = uniqid("JK");
		$attribute = passed_var("attribute", "post");
		$attribute_2 = passed_var("attribute_2", "post");
		$upload_details = passed_var("upload_details", "post");
		$sql = "";
		if ($attribute=="applicant_picture") {
			$sql = "UPDATE ";
			if ($db_name!="") {
				$sql .= "`" . $db_name . "`.";
			}
			$sql .= "`cse_case_document` 
			SET deleted = 'Y'
			WHERE `case_uuid` = '" . addslashes($case_uuid) . "'
			AND `attribute_1` = 'applicant_picture';
			";
		}
		if ($customer_id == 1033 && $attribute=="personal_injury_picture") {
			$sql .= "INSERT INTO `cse_pi_document`
			( `pi_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $cd_uuid . "','" . addslashes($case_uuid) . "','" . $document_uuid . "', '" . $attribute . "', '" . $attribute_2 . "', '" . date("Y-m-d H:i:s") . "','" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			//die($sql);
			try {
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				
				$stmt = null; $db = null;
				//die(print_r($newEmployee));
				
				//echo json_encode(array("success"=>11)); 
			} catch(PDOException $e) {	
				//echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		} else {
			$sql .= "INSERT INTO ";
			if ($db_name!="") {
				$sql .= "`" . $db_name . "`.";
			}
			$sql .= "`cse_case_document`
			( `case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $cd_uuid . "','" . addslashes($case_uuid) . "','" . $document_uuid . "', '" . $attribute . "', '" . date("Y-m-d H:i:s") . "','" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			//die($sql);
			try {
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				
				$stmt = null; $db = null;
				//die(print_r($newEmployee));
				
				//echo json_encode(array("success"=>11)); 
			} catch(PDOException $e) {	
				//echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
	}
	//attach to case
	if ( isset($_POST["injury_id"])) {
		$injury_id = passed_var("injury_id", "post");
		if ($injury_id > 0) {
			$injury = getInjuryInfo($injury_id);
			//die(print_r($kase));
			$injury_uuid = $injury->uuid;
		}
		$cd_uuid = uniqid("JK");
		$attribute = passed_var("attribute", "post");
		$attribute_2 = passed_var("attribute_2", "post");
		$upload_details = passed_var("upload_details", "post");
		$sql = "INSERT INTO ";
		if ($db_name!="") {
			$sql .= "`" . $db_name . "`.";
		}
		$sql .= "`cse_injury_document`
		( `injury_document_uuid`, `injury_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $cd_uuid . "','" . addslashes($injury_uuid) . "','" . $document_uuid . "', '" . $attribute . "', '" . date("Y-m-d H:i:s") . "','" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$stmt = null; $db = null;
			//die(print_r($newEmployee));
			
			//echo json_encode(array("success"=>11)); 
		} catch(PDOException $e) {	
			//echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	
	}
}
function addReport() {
	//create a report file for feedback from customer
	$client_id = passed_var("client_id", "post");
	$report = passed_var("report", "post");
	$content = $_POST["content"];
	//clean up
	$content = str_replace("||", "&", $content);
	$content = str_replace('id="send_kases"', 'id="send_kases" style="display:none"', $content);
	$content = str_replace('id="filters_holder"', 'id="filters_holder" style="display:none"', $content);
	$content = str_replace('class="save_button_holder" style="display:none"', 'class="save_button_holder" style="display:"', $content);
	$content = str_replace('class="kase_list_header" style="display: none;"', 'class="kase_list_header" style="display:;"', $content);
	
	
	$content = str_replace('<a', '<span', $content);
	$content = str_replace('/a>', '/span>', $content);
	//$content = str_replace('class="note_holder">&nbsp;</div>', 'class="note_holder"><textarea rows="3" style="width:100%"></textarea></div>', $content);
	
	$content .= '<script language="javascript">var customer_id = "' . $_SESSION["user_customer_id"] . '";</script><script language="javascript" src="../../../feedback.js"></script>';
	
	$uploadDir = '\\feedback\\' . $_SESSION["user_customer_id"] . '\\';
	if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
		mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
	}
	
	$uploadDir .= $client_id . '\\';
	if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
		mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
	}
	
	$uploadDir .= "clientreports";
	if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
		mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
	}
	
	
	//now create a text file and insert contents into it
	$fp = fopen($_SERVER['DOCUMENT_ROOT'] . $uploadDir . '\\data_' . date("Ymd") . '.php', 'w');
	fwrite($fp, $content);
	fclose($fp);
	
	//one-time link
	$sql = "INSERT IN";
	
	echo json_encode(array("success"=>true, "filename"=>$_SESSION["user_customer_id"] . "/" . $client_id . "/clientreports/data_" . date("Ymd") . ".php"));
}
function completeStack() {
	$document_id = passed_var("document_id", "post");
	$document = getDocumentInfo($document_id);
	
	$kase = getKaseInfo($document->case_id);
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') {
		//die(print_r($document));
	}
	
	try {
		$db = getConnection();
		//remove notification for me, completed as far as I'm concerned
		$sql = "UPDATE cse_notification
		SET notification = 'completed',
		notification_date = '" . date("Y-m-d H:i:s") . "'
		WHERE document_uuid = '" . $document->uuid . "'
		AND user_uuid = '" . $_SESSION["user_id"] . "'
		AND notification = 'review'";
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true));
		
		$destination = "api/preview.php?case_id=6949&amp;file=" . urlencode($document->document_filename) . "&amp;id=" . $document->document_id;
		$activity = "Scanned Document [<a href='" . $destination . "' target='_blank'>" . $document->document_name . "</a>] was marked as completed by " . $_SESSION["user_name"];
		
		recordActivity("complete", $activity, $kase->uuid, $document_id, "Documents");
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function addStack() {
	$request = Slim::getInstance()->request();
	$cd_uuid = uniqid("JK");
	$customer_id = $_SESSION['user_customer_id'];
	$attribute = "import_assigned";
	$last_update_user = $_SESSION['user_id'];
	$last_updated_date = date("Y-m-d H:i:s");
	$document_id = passed_var("document_id", "post"); 
	$document_uuid = "";
	
	$source = passed_var("source", "post");
	$received_date = passed_var("received_date", "post");
	if ($received_date!="") {
		$received_date = date("Y-m-d H:i:s", strtotime($received_date));
	} else {
		$received_date = "0000-00-00 00:00:00";
	}
	
	$case_id = passed_var("case_id", "post"); 
	//die(print_r($_POST));
	$case_uuid = "";
	if ($case_id!="") {
		$kase = getKaseInfo($case_id);
		//die(print_r($kase));
		//die($document);
		$case_uuid = $kase->uuid;
	}
	if ($document_id!="") {
		$document = getDocumentInfo($document_id, "");
		$type = $document->type;
		
		//die(print_r($document));
		$document_uuid = $document->uuid;
		
		//move document into case folder
		$case_folder = $_SERVER["DOCUMENT_ROOT"] . "\\uploads\\" . $customer_id . "\\" . $case_id . "\\";
		if (!is_dir($case_folder)) {
			mkdir($case_folder, 0755, true);
		}
		$file_folder = $_SERVER["DOCUMENT_ROOT"] . "\\uploads\\" . $customer_id . "\\imports\\";
		if ($type=="batchscan2") {
			$file_folder = $_SERVER["DOCUMENT_ROOT"] . "\\scans\\" . $customer_id . "\\" . $document->thumbnail_folder . "\\imports\\";
			
		} 
		$source = $document->type;
		if ($type=="batchscan3") {
			$file_folder .= $document->thumbnail_folder . "\\";
			$source = "batchscan3";
			
		} 
		if (file_exists($file_folder . $document->document_filename)) {
			copy($file_folder . $document->document_filename, $case_folder . $document->document_filename);
		}
		
		//die("copied -> " . $case_folder . $document->document_filename);
	}
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		if ($fieldname == "case_uuid") {
			continue;
		}
		${$fieldname} = $value;
	}
	$sql = "INSERT INTO `cse_case_document`
	( `case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
	VALUES (:cd_uuid, :case_uuid, :document_uuid, :attribute, :last_updated_date, :last_update_user, :customer_id)";
	//die($case_uuid . " - 1");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("cd_uuid", $cd_uuid);
		$stmt->bindParam("case_uuid", $case_uuid);
		$document_uuid = $document->uuid;
		$stmt->bindParam("document_uuid", $document_uuid);
		$stmt->bindParam("attribute", $attribute);
		$stmt->bindParam("last_updated_date", $last_updated_date);
		$stmt->bindParam("last_update_user", $last_update_user);
		//$stmt->bindParam("source", $source);
		//$stmt->bindParam("received_date", $received_date);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$stmt = null; $db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	/*
	type: type, 
	document_extension: category, 
	description: subcategory, 
	*/
	
	$sql = "UPDATE `cse_document`
			SET `document_name` = '" . addslashes($name) . "',
			`source` = '" . addslashes($source) . "',
			`type` = '" . addslashes($type) . "',
			document_extension  = '" . addslashes($category) . "',
			description  = '" . addslashes($subcategory) . "',
			description_html  = '" . addslashes($note) . "'
			WHERE document_uuid = '" . $document_uuid . "' 
			AND customer_id = " . $customer_id;
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$stmt = null; $db = null;
		trackDocument("stack", $document_id, "");
		echo json_encode(array("success"=>"Y")); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function addUnassigned() {
	$request = Slim::getInstance()->request();
	$cd_uuid = uniqid("JK");
	$customer_id = $_SESSION['user_customer_id'];
	$attribute = "assigned";
	$last_update_user = $_SESSION['user_id'];
	$last_updated_date = date("Y-m-d H:i:s");
	$document_id = passed_var("document_id", "post"); 
	$document_uuid = "";
	
	$source = passed_var("source", "post");
	$received_date = passed_var("received_date", "post");
	if ($received_date!="") {
		$received_date = date("Y-m-d H:i:s", strtotime($received_date));
	} else {
		$received_date = "0000-00-00 00:00:00";
	}
	
	$case_id = passed_var("case_id", "post"); 
	//die($received_date);
	$case_uuid = "";
	if ($case_id!="") {
		$kase = getKaseInfo($case_id);
		//die(print_r($kase));
		//die($document);
		$case_uuid = $kase->uuid;
	}
	if ($document_id!="") {
		$document = getDocumentInfo($document_id, "");
		//die(print_r($document));
		$document_uuid = $document->uuid;
		
		//move document into case folder
		$case_folder = $_SERVER["DOCUMENT_ROOT"] . "\\uploads\\" . $customer_id . "\\" . $case_id . "\\";
		if (!is_dir($case_folder)) {
			mkdir($case_folder, 0755, true);
		}
		$file_folder = $_SERVER["DOCUMENT_ROOT"] . "\\uploads\\" . $customer_id . "\\";
		
		copy($file_folder . $document->document_filename, $case_folder . $document->document_filename);
		//die("copied -> " . $case_folder . $document->document_filename);
	}
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		if ($fieldname == "case_uuid" || $fieldname == "received_date") {
			continue;
		}
		${$fieldname} = $value;
	}
	$sql = "INSERT INTO `cse_case_document`
	( `case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
	VALUES (:cd_uuid, :case_uuid, :document_uuid, :attribute, :last_updated_date, :last_update_user, :customer_id)";
	//die($case_uuid . " - 1");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("cd_uuid", $cd_uuid);
		$stmt->bindParam("case_uuid", $case_uuid);
		$document_uuid = $document->uuid;
		$stmt->bindParam("document_uuid", $document_uuid);
		$stmt->bindParam("attribute", $attribute);
		$stmt->bindParam("last_updated_date", $last_updated_date);
		$stmt->bindParam("last_update_user", $last_update_user);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$stmt = null; $db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	/*
	type: type, 
	document_extension: category, 
	description: subcategory, 
	*/
	if ($customer_id == 1121) {
		if (!isset($category)) {
			$category = "";
			$subcategory = "";
		}
	}
	//die($type . " - the type");
	$sql = "UPDATE `cse_document`
			SET `document_name` = '" . addslashes($name) . "',
			`document_date` = '" . date("Y-m-d H:i:s") . "',
			`type` = '" . addslashes($type) . "',
			document_extension  = '" . addslashes($category) . "',
			description  = '" . addslashes($subcategory) . "',
			description_html  = '" . addslashes($note) . "',
			`received_date` = '" . $received_date . "',
			`source` = '" . addslashes($source) . "'
			WHERE document_uuid = '" . $document_uuid . "' 
			AND customer_id = " . $customer_id;
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$stmt = null; $db = null;
		trackDocument("assign", $document_id, "");
		echo json_encode(array("success"=>"Y")); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updateDocument() {
	$request = Slim::getInstance()->request();
	session_write_close();
	$sql = "UPDATE `cse_document` 
			SET `document_uuid` = :document_uuid,
			`parent_document_uuid` = :parent_document_uuid,
			`document_name` = :document_name,
			`document_date` = :document_date,
			`document_filename` = :document_filename,
			`document_extension` = :document_extension,
			`thumbnail_folder` = :thumbnail_folder,
			`description` = :description,
			`description_html` = :description_html,
			`type` = :type,
			`verified` = :verified
			WHERE document_id = :document_id
			AND cse_document.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("document_id", $_POST["document_id"]);
		$stmt->bindParam("document_uuid", $_POST["document_uuid"]);
		$stmt->bindParam("parent_document_uuid", $_POST["parent_document_uuid"]);
		$stmt->bindParam("document_name", $_POST["document_name"]);
		$stmt->bindParam("document_date", $_POST["document_date"]);
		$stmt->bindParam("document_filename", $_POST["document_filename"]);
		$stmt->bindParam("document_extension", $_POST["document_extension"]);
		$stmt->bindParam("thumbnail_folder", $_POST["thumbnail_folder"]);
		$stmt->bindParam("description", $_POST["description"]);
		$stmt->bindParam("description_html", $_POST["description_html"]);
		$stmt->bindParam("type", $_POST["type"]);
		$stmt->bindParam("verified", $_POST["verified"]);
		$stmt->execute();
		
		$stmt = null; $db = null;
		//die(print_r($newEmployee));
		trackDocument("update", $_POST["document_id"]);
		echo json_encode(array("success"=>$_POST["document_id"])); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function categorizeDocument() {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	$document_name = passed_var("document_name", "post");
	$type = passed_var("type", "post");
	$document_id = passed_var("document_id", "post");
	$document_extension = passed_var("document_extension", "post");
	if ($document_extension=="undefined") {
		$document_extension = "Invoice";
	}
	if ($document_extension=="undefined") {
		$document_extension = "-1";
	}
	$source = passed_var("source", "post");
	$injury_id = passed_var("doi_id", "post");
	$injury_uuid = "";
	if ($injury_id!="") {
		$injury = getInjuryInfo($injury_id);
		$injury_uuid = $injury->uuid;
	}
	$received_date = passed_var("received_date", "post");
	$description = passed_var("description", "post");
	if ($description=="undefined") {
		$description = "-1";
	}
	$description_html = passed_var("description_html", "post");
	//die("dd:" . $description  . " -- " . $document_extension);
	/*
	type: type, 
	document_extension: category, 
	description: subcategory, 
	description_html: note
	*/
	$sql = "UPDATE `cse_document` 
			SET 
			`document_name` = :document_name, ";
	//,	`document_date` = :document_date
	if ($document_extension!="-1") {
		$sql .= "
		`document_extension` = :document_extension,";
	}
	if ($description != "-1") {
		$sql .= "
		`description` = :description,";
	}
	$sql .= "
			`source` = :source,
			`received_date` = :received_date,
			`description_html` = :description_html,
			`type` = :type
			WHERE document_id = :document_id
			AND cse_document.customer_id = :customer_id";
	try {
		//die($sql); 
		$db = getConnection();
		$stmt = $db->prepare($sql); 
		
		
		if ($received_date!="") {
			$received_date = date("Y-m-d H:i:s", strtotime($received_date));
		} else {
			$received_date = "0000-00-00 00:00:00";
		}
		
		$stmt->bindParam("document_id", $document_id);
		$stmt->bindParam("document_name", $document_name);
		if ($document_extension!="-1") {
			$stmt->bindParam("document_extension", $document_extension);
		}
		$stmt->bindParam("source", $source);
		$stmt->bindParam("received_date", $received_date);
		if ($description != "-1") {
			$stmt->bindParam("description", $description);
		}
		$stmt->bindParam("description_html", $description_html);
		$stmt->bindParam("type", $type);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		
		$stmt = null; $db = null;

		if ($injury_uuid!="") {
			$document = getDocumentInfo($document_id);
			
			if ($document->doi_id!=$injury_id) {
				$document_uuid = $document->uuid;
				
				//is it already attached?
				$sql = "UPDATE cse_injury_document
				SET deleted = 'Y'
				WHERE document_uuid = :document_uuid
				AND injury_uuid != :injury_uuid
				AND customer_id = :customer_id";
				
				$db = getConnection();
				$stmt = $db->prepare($sql); 
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();			
				$stmt = null; $db = null;
							
				$last_updated_date = date("Y-m-d H:i:s");
				$injury_table_uuid = uniqid("KA", false);
				//attribute
				$table_attribute = "main";
				
				$table_name = "document";
				//now we have to attach the note to the case 
				$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $document_uuid . "', '" . $table_attribute . "', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				//echo $sql . "\r\n";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
			}
		}
		trackDocument("categorize", $document_id);
		echo json_encode(array("success"=>$document_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function renameDocumentForDownload() {
	$case_id = passed_var("case_id", "post");
	$document_id = passed_var("document_id", "post");
	
	$document = getDocumentInfo($document_id);
	$source = $document->document_filename;
	
	$destination = str_replace(".docx", "_" . $case_id . "_0.docx", $source);
	
	$upload_dir = $_SERVER["DOCUMENT_ROOT"] . "\\uploads\\" . $_SESSION["user_customer_id"] . "\\" . $case_id . "\\";
	
	try {
		$sql = "UPDATE cse_document
		SET document_filename = :destination
		WHERE document_id = :document_id
		AND customer_id = :customer_id";
		
		$customer_id = $_SESSION["user_customer_id"];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("destination", $destination);
		$stmt->bindParam("document_id", $document_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		rename($upload_dir . $source, $upload_dir . $destination);
		
		echo json_encode(array("success"=>"true", "document_id"=>$document_id, "destination"=>$destination)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function typeDocument() {
	$request = Slim::getInstance()->request();
	if ($_POST["document_uuid"]=="") {
		$sql = "UPDATE `cse_case_document` ccd, `cse_document` cd, `cse_case` cse
			SET cd.`type` = '" . $_POST["type"] . "',
			cd.description = '" . addslashes($_POST["description"]) . "',
			cd.document_name = '" . addslashes($_POST["document_name"]) . "',
			cd.document_extension = '" . addslashes($_POST["document_extension"]) . "',
			cd.thumbnail_folder = '" . addslashes($_POST["thumbnail_folder"]) . "'
			WHERE ccd.document_uuid = cd.document_uuid
			AND ccd.case_uuid = cse.case_uuid
			AND cd.document_filename = '" . $_POST["filename"] . "'
			AND cse.case_id = '" . $_POST["case_id"] . "'
			AND cd.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cd.deleted != 'Y'";
	} else {
		$sql = "UPDATE `cse_document` cd
			SET cd.`type` = '" . addslashes($_POST["type"]) . "',
			cd.description  = '" . addslashes($_POST["description"]) . "',
			cd.document_extension  = '" . addslashes($_POST["document_extension"]) . "'
			WHERE cd.document_uuid = '" . $_POST["document_uuid"] . "' AND cd.customer_id = " . $_SESSION['user_customer_id'];
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$stmt = null; $db = null;
		trackDocument("type", $_POST["document_id"]);
		echo json_encode(array("success"=> $_POST["case_id"] . "-" . $_POST["filename"])); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function processFTP() {
	//customer id is in macro
	$customer_id = passed_var("customer_id", "get");
	
	if (!is_numeric($customer_id)) {
		die();
	}
	//look into folder
	$upload_dir = $_SERVER["DOCUMENT_ROOT"] . "\\fromclient";
	$arrUploads = scandir($upload_dir);
	//echo $upload_dir . "\r\n";
	//die(print_r($arrUploads));
	$arrNotFound = array();
	$arrSuccess = array();
	foreach($arrUploads as $upload) {
		if ($upload=="." || $upload=="..") {
			continue;
		}

		if ($upload=="index.html") {
			continue;
		}
		//only docx allowed
		$arrFilepath = explode(".", $upload);
		$extension = $arrFilepath[count($arrFilepath) - 1];
		
		if (strpos($upload, ".") > -1) {
			if ($extension!="docx") {
				unlink($upload_dir . "\\" . $upload);
				continue;
			}
		}
		//remove any download (
		$original_upload = $upload;
		$arrFile = explode(" (", $upload);
		if (count($arrFile) > 1 && strpos($upload, ").docx")!==false) {
			$last_one = $arrFile[count($arrFile) - 1];
			unset($arrFile[count($arrFile) - 1]);
			//echo $original_upload . "\r\n" . $last_one . "\r\n";
			//die(print_r($arrFile));
			$original_upload = implode(" (", $arrFile) . "." . $extension;
		}
		//echo "\r\nprocessing:" . $upload . "\r\n" . $original_upload . "\r\n";
		//extract case id
		$arrFile = explode("_", $upload);
		//die(print_r($arrFile));
		if (count($arrFile) > 2) {
			//die($upload . "\r\ncount:" . count($arrFile));
			$case_id = $arrFile[count($arrFile)-2];			
			if ($case_id > 0) {
				//echo $upload . " -> " . $case_id . "\r\n";
				//we have case_id, now confirm customer
				$dbname = "ikase";
				
				try {
					$db = getConnection();
					$sql_customer = "SELECT data_source, permissions
					FROM  `ikase`.`cse_customer` 
					WHERE customer_id = :customer_id AND deleted = 'N'";
					
					$stmt = $db->prepare($sql_customer);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					$customer = $stmt->fetchObject();
					
					if (!is_object($customer)) {
						die();
					}
					$data_source = $customer->data_source;
					
					if ($data_source!="") {
						$dbname .= "_" . $data_source;
					}
					
					$sql = "SELECT customer_id 
					FROM `" . $dbname . "`.`cse_case`
					WHERE case_id = :case_id
					AND customer_id = :customer_id";
					//die($sql);
					$stmt = $db->prepare($sql);
					$stmt->bindParam("case_id", $case_id);
					$stmt->bindParam("customer_id", $customer_id);
					
					$stmt->execute();
					$kase = $stmt->fetchObject();
					//echo $sql . "\r\n";
					
					if (is_object($kase)) {					
						//die(print_r($kase));
						//look for document
						//'../uploads/1033/3062/letters/10111 notice rep with claimform_3062_0'
						$document_filename = '../uploads/' . $customer_id . '/' . $case_id . '/letters/' . str_replace(".docx", "", $original_upload);
						//see if the file exists
						$customer_dir = $_SERVER["DOCUMENT_ROOT"] . "\\uploads\\" . $customer_id . "\\" . $case_id . "\\letters";
						$upload_path = $customer_dir . "\\" . $original_upload;
						//die($upload_path);
						
						//die($upload_path . ".docx");
						if (!file_exists($upload_path)) {
							//die($upload_path . " does not exist");
							//try a simpler path
							$upload_path = $_SERVER["DOCUMENT_ROOT"] . "\\uploads\\" . $customer_id . "\\" . $case_id . "\\" . $original_upload;
							
							if (!file_exists($upload_path)) {
								continue;
							} else {
								$customer_dir = $_SERVER["DOCUMENT_ROOT"] . "\\uploads\\" . $customer_id . "\\" . $case_id;
								$document_filename = $original_upload;
							}
						} else {
							//echo $upload_path . ".docx exists\r\n";
						}
						
						$sql = "SELECT document_id `id`, document_uuid `uuid`
						FROM `" . $dbname . "`.`cse_document`
						WHERE customer_id = " . $customer_id . "
						AND `document_filename` = '" . $document_filename . "'";
						$stmt = $db->prepare($sql);							
						$stmt->execute();
						$document = $stmt->fetchObject();
						//echo $sql . "\r\n";
						//die(print_r($document));
						if (is_object($document)) {
							//insert tracking
							//trackDocument("macro_update", $document->id, $document->uuid);
							$sql = "INSERT INTO `" . $dbname . "`.cse_document_track (`user_uuid`, `user_logon`, `operation`, `document_id`, `document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `type`, `verified`, `deleted`, `customer_id`)
							SELECT 'system', 'system', 'macro_update', `document_id`, `document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `type`, `verified`, `deleted`, `customer_id`
							FROM `" . $dbname . "`.cse_document
							WHERE 1";
							$sql .= " AND document_id = " . $document->id;
							$sql .= " AND customer_id = " . $customer_id . "
							LIMIT 0, 1";
							//die($sql);
							$stmt = $db->prepare($sql);							
							$stmt->execute();
						}
						
						//move the file to the correct folder
						
						$initmodif = 0;
						if (file_exists($customer_dir . "\\" . $original_upload)) {
							$initmodif = filemtime($customer_dir . "\\" . $original_upload);
						}
						if ($initmodif==0) {
							//echo  $customer_dir . "\\" . $original_upload . " does not exist\r\n";
							//die();
							continue;
						}
						//echo $initmodif . " --> move from: " . $upload_dir . "\\" . $upload . " --- " . $customer_dir . "\\" . $original_upload . "\r\n\r\n";
						//die();
						
						if (!rename($upload_dir . "\\" . $upload, $customer_dir . "\\" . $original_upload)) {
							$error = array("error"=> "could not rename " . $upload);
							die(json_encode($error));
						} 
						$currentmodif = filemtime($customer_dir . "\\" . $original_upload);
						//echo "init:" . date("m/d/y H:i:s", $initmodif) . "\r\nthen:" . date("m/d/y H:i:s", $currentmodif) . "\r\n";
						//die("init:" . $initmodif . "\r\nthen:" . $currentmodif);
						$timedif = $currentmodif - $initmodif;
						
						$success = array("success"=>true, "filename"=>$original_upload, "timedif"=>$timedif, "modified"=>date("H:i:s", $currentmodif));
						//echo json_encode($success);
						$arrSuccess[] = $success;
						//die();
					} else {
						$error = array("error"=> array("text"=>"kase " . $case_id . " not found"));
						//echo $error["error"]["text"] . "\r\n";
						$arrNotFound[] = $error;
						continue;
						//echo json_encode($error);
						//echo $error["error"]["text"];
					}
					$stmt = null; $db = null;
				} catch(PDOException $e) {
					$error = array("error"=> array("text"=>$e->getMessage()));
					echo json_encode($error);
				}
			} else {
				//the file is not proper, delete it too
				unlink($upload_dir . "/" . $upload);
				continue;
			}
		}
	}
	//$arrUploads = scandir($upload_dir);
	
	//echo json_encode(array("success"=>$arrSuccess, "errors"=>$arrNotFound));
	
	foreach($arrSuccess as $outsuccess) {
		echo $outsuccess["filename"] . " has been uploaded\r\n";
	}
	if (count($arrSuccess)==0) {
		echo "no files were uploaded\r\n";
	}
	//clean up any mess
	/*
	foreach($arrUploads as $upload) {
		if ($upload=="." || $upload=="..") {
			continue;
		}

		if ($upload=="index.html") {
			continue;
		}
		unlink($upload_dir . "\\" . $upload);
	}
	*/
}
function trackDocument($operation, $document_id = "", $document_uuid = "", $blnRecordActivity = true) {
	$sql = "INSERT INTO cse_document_track (`user_uuid`, `user_logon`, `operation`, `document_id`, `document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `type`, `verified`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `document_id`, `document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `type`, `verified`, `deleted`, `customer_id`
	FROM cse_document
	WHERE 1";
	if ($document_uuid!="" && $document_id=="") {
		$document = getDocumentInfo("", $document_uuid);
		$document_id = $document->document_id;
	}
	if ($document_id!="" && $document_uuid=="") {
		$document = getDocumentInfo($document_id, "");
		$document_uuid = $document->uuid;
	}
	if ($document_id!="") {
		$sql .= " AND document_id = " . $document_id;
	}
	$sql .= " AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		if ($operation!="macro_update" && $blnRecordActivity) {
			//new the case_uuid
			$kase = getKaseInfoByDocument($document_id);
			$case_uuid = "";
			$case_id = "";
			if (is_object($kase)) {
				$case_uuid = $kase->uuid;
				$case_id = $kase->id;
			}
			$activity_category = "Documents";
			switch($operation){
				case "assign":
				case "insert":
					$operation .= "ed";
					break;
				case "categorize":
				case "update":
				case "delete":
					$operation .= "d";
					break;
			}
			$activity_uuid = uniqid("KS", false);
			$destination = "";
			if (isset($document)) {
				if ($document->document_name=="") {
					$document->document_name = $document->document_filename;
				}
				$destination = $document->document_filename;
			}
			$arrDestination = explode("/", $destination);
			$destination = $arrDestination[count($arrDestination) - 1];
			
			//now rebuild
			$prefix = "../uploads/" . $_SESSION["user_customer_id"];
			if ($case_id!="") {
				$prefix .= "/" . $case_id;
			}
			$destination = $prefix . "/" . $destination;
			if (isset($document)) {
				$activity = "Document [<a href='" . $destination . "' target='_blank'>" . $document->document_name . "</a>] was " . $operation . "  by " . $_SESSION['user_name'];
				
				if ($operation == "categorized") {
					/*
					type: type, 
					document_extension: category, 
					description: subcategory, 
					description_html: note
					*/
					$type = $document->type;
					$category = $document->document_extension;
					$subcategory = $document->description;
					$note = $document->description_html;
					$arrDetails = array();
					if ($type!="") {
						$arrDetails[] = "Type:" . $type;
					}
					if ($category!="") {
						$arrDetails[] = "Category:" . $category;
					}
					if ($subcategory!="") {
						$arrDetails[] = "Sub Category:" . ucwords($subcategory);
					}
					if ($note!="") {
						//is it residual from batchscan?
						$arrNote = explode("_", $note);
						$blnShowNote = true;
						if (count($arrNote) == 2) {
							if (is_numeric($arrNote[0]) && is_numeric($arrNote[1])) {
								$blnShowNote = false;
							}
						}
						if ($blnShowNote) {
							$arrDetails[] = "<br />" . $note;
						}
					}
					if (count($arrDetails) > 0) {
						$activity .= "<br />" . implode("<br />", $arrDetails);
					}
				}
				recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category);
			}
		}
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function assignImport() {
	//die(json_encode($_POST));
	$case_id = passed_var("case_id", "post");
	$id = passed_var("id", "post");
	$import_document_id = $_POST["document_id"];
	
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$partie_id = "";
	$case_uuid = "";
	$type = "";	
	
	//default attribute
	$table_attribute = "main";
	$db = getConnection();
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			continue;
		}
		if ($fieldname=="type") {
			$type = $value;
			continue;
		}
		if ($fieldname=="partie_id") {
			$partie_id = $value;
			continue;
		}

		// || $fieldname=="message_to" || $fieldname=="message_cc" || $fieldname=="message_bcc"
		if ($fieldname=="case_file" || $fieldname=="id"  || $fieldname=="document_id" || $fieldname=="table_id" || $fieldname=="priority" || $fieldname=="source_message_id" || $fieldname=="from") {
			continue;
		}
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
	}
	

	$message_uuid = uniqid("KS", false);
	//message
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $message_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);		
		//die($sql_note);	
	try { 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		echo json_encode(array("document_id"=>$document_id)); 
		
		if ($case_uuid=="" && $case_id!="") {
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
		}
		$case_table_uuid = uniqid("KA", false);
		//attribute
		if ($table_attribute=="") {
			//default
			$table_attribute = "main";
		}
		
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the note to the case 
		$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $document_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		$case_table_uuid = uniqid("KA", false);
		//attribute
		if ($table_attribute=="") {
			//default
			$table_attribute = "main";
		}
		
		$last_updated_date = date("Y-m-d H:i:s");
		try {
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		} catch(PDOException $e) {
			echo '{"error case notes insert":{"text":'. $e->getMessage() .'}}'; 
		}
		
		trackMessage("insert", $document_id);
		$stmt = null; $db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	//and then make the email a permanent inbox message
	//and then get rid of the webmail_previews/file
}
function referVocation() {
	session_write_close();
	
	$customer_id = $_SESSION['user_customer_id'];
	$case_id = passed_var("case_id", "post");
	$max_med_date = passed_var("max_med_date", "post");
	$voucher = passed_var("voucher", "post");
	
	//die(json_encode(array("files"=>$_FILES, "post"=>$_POST)));
	if (isset($_FILES)) {
		
		if (count($_FILES) > 0) {
			$accepted_type = "application/pdf";
			$file = $_FILES["fileName"];
			//die(print_r($file));	
			if ($file["type"]!=$accepted_type) {
				die(json_encode(array("success"=> false, "error"=>"PDF Only")));
			}
			
			$case_dir = "C:\\inetpub\\wwwroot\\iKase.org\\uploads\\" . $customer_id . "\\" . $case_id . "\\";
			if (!is_dir($case_dir)) {
				mkdir($case_dir, 0755, true);
			}
			
			$uploadDir = $case_dir . "refervocational\\";
			if (!is_dir($uploadDir)) {
				mkdir($uploadDir, 0755, true);
			}
			
			$name = $file["name"];
			$name = str_replace(" ", "_", $name);
			$name = str_replace("&", "_", $name);
			$name = str_replace("(", "_", $name);
			$name = str_replace(")", "_", $name);
			$name = strtolower($name);
			
			$arrDir = explode("\\", $uploadDir);
			unset($arrDir[count($arrDir) - 1]);
			$uploadDir = implode("\\", $arrDir) . "\\";
			$targetFile = $name;
			
			$original_file = $targetFile;
			$targetFile = noSpecialFilename($targetFile);		
			$targetFile = str_replace("__", "_", $targetFile);
			$targetFile = str_replace(",", "_", $targetFile);
			$targetFile = str_replace("_.pdf", ".pdf", $targetFile);
						
			$document_counter = 0;
			$blnFound = true;
			while ($blnFound) {
				$arrFile = explode(".", $original_file);
				$arrFile[count($arrFile)-1] = "_" . $document_counter . "." .  $arrFile[count($arrFile)-1];
				
				$targetFile = implode("", $arrFile);
				$targetFile = str_replace("__", "_", $targetFile);
				$targetFile = str_replace(",", "_", $targetFile);
				$targetFile = str_replace("_.pdf", ".pdf", $targetFile);
				
				if (!file_exists($uploadDir . $targetFile)) {
					$blnFound = false;
					//echo "it DOES NOT exists: ";
					//die($uploadDir . $targetFile . " new file name");
				}
				$document_counter++;
			}
			
			$file_path = $uploadDir . $targetFile;
			//die("file:" . $file_path );
			if (move_uploaded_file($file["tmp_name"], $file_path)) {
				//add to activity
				$kase = getKaseInfo($case_id);
				$case_uuid = $kase->uuid;
				$injury_id = $kase->injury_id;
				
				$operation = "refer vocational";
				$activity = "Voucher: " . $voucher;
				$activity .= "\r\n";
				$activity .= "Max Medical Improvement Date: " . date("m/d/Y", strtotime($max_med_date));
				$activity .= "\r\n";
				$activity .= "Attachment: <a href='api/preview_refervocational.php?case_id=" . $case_id . "&file=" . urlencode($targetFile) . "' target='_blank' class='white_text'>" . $targetFile . "</a>";
				$track_id = "-1";
				$billing_time = 0;
				$category = "Vocational";
				
				$activity_id = recordActivity($operation, $activity, $case_uuid, $track_id, $category, $billing_time);
				/*
				$plain_params = json_encode(array("activity_id"=>$activity_id, "customer_id"=>$customer_id));
				$params = base64_encode($plain_params);
				*/
				$path = "../uploads/" . $customer_id . "/" . $case_id . "/refervocational/" . $targetFile;
				$key = md5(microtime());
				$sql = "INSERT INTO ikase.cse_downloads (`downloadkey`, `sent_by`, `injury_id`, `file`, `expires`, `customer_id`) 
				VALUES ('" . $key . "', '" . $_SESSION['user_plain_id'] . "', '" . $injury_id . "', '" . $path . "', '" . date("Y-m-d H:i:s", (time()+(60*60*24*7))) ."', '" . $customer_id ."')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
				
				
				$url = "https://v2.ikase.org/vocational.php?key=" . $key;
				$short_url = make_bitly_url($url);
				
				//demographics
				$path = $customer_id . "/" . $case_id;
				
				$key = md5(microtime());
				$sql = "INSERT INTO ikase.cse_downloads (`downloadkey`, `sent_by`, `injury_id`, `file`, `expires`, `customer_id`) 
				VALUES ('" . $key . "', '" . $_SESSION['user_plain_id'] . "', '" . $injury_id . "', '" . $path . "', '" . date("Y-m-d H:i:s", (time()+(60*60*24*7))) ."', '" . $customer_id ."')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
				
				$url = "https://v2.ikase.org/demos.php?key=" . $key;
				$demo_url = make_bitly_url($url);
				
				die(json_encode(array("success"=>true, "short_url"=>$short_url, "demo_url"=>$demo_url, "activity_id"=>$activity_id, "filepath"=>$targetFile)));
			}
		}
	}
}
function downloadVocational($id) {
	$arrParams = explode("a", $id);
	$customer_id = $arrParams[0] / 3;
	$activity_id = $arrParams[1] / $customer_id;
	
	//die($customer_id . " -- " . $activity_id);
	$loc = "download_vocational.php?id=" . $id;
	echo "<a href='https://v2.ikase.org/api/" . $loc . "'>Click here to Download</a>";
}

//solulab code start - 27-05-2019
/* upload document to docucents */
function docuFileupload(){
	//echo '<pre>'; print_r($_SESSION);die;
	$db = getConnection();
	$file = $_POST['file'];
	include('../docusent/cls_docucents.php');
	$api_key = getCustomerDocucentsAPIKey($_POST['customer_id']);
	if(!file_exists($file)){
		$vendor = ["status"=>"404","message"=>"File not found!!"];
		echo json_encode($vendor);die;
	}
	if($api_key){
	$obj = new docucents("cmd",$api_key);
	$billingcode = $_POST['jetfile_case_id'].uniqid();
	$poswording = "Document uploaded to docucents";
	$obj->Submittals_AddForDelivery($_POST['jetfile_case_id'],$billingcode , "APPFULLPDF_-" . uniqid(), "None", "comment:" . uniqid(), $poswording);
	$obj->PartyData_AddForDelivery("ikase.org", "testSolulab2", "testSolulab2", "", 'address1', "", "testSolula2b", "testSolulab2", "test", "test");
	$origpdf = file_get_contents($file);
	$filecontent = base64_encode($origpdf);
	$filedate = date("Ymd", time());

	xmlrpc_set_type($filecontent, "base64");

	xmlrpc_set_type($filedate, "datetime");
	$attachment = array(
		"file_name" => "APPFULLPDF_".$_POST['jetfile_case_id']."_".$_POST['customer_id']."_" . rand(1, 5) . ".pdf",
		"type" => "APPFULLPDF_-" . uniqid(),
		"title" => "APPFULLPDF_-" . uniqid(),
		"unit" => "ADJ",
		"date" => $filedate,
		"author" => "author-".$_POST['customer_id']."-" . uniqid(),
		"base64" => $filecontent
	);
	$temp = $obj->AddAttachment($attachment, false);

	$post_result = $obj->SetSubmittalStatus();
	$vendor = ["status"=>"200","message"=>"Document Uploaded Successfully!!","vendor_submittal_id" => $obj->vendor_submittal_id];
	/*try{	
		$sql1 = "INSERT INTO cse_docucents_track (`user_uuid`, `user_logon`,`user_role`, `operation`, `docucents_vendor_submittal_id`,`customer_id`, `case_id`) 
				VALUES ('" . $_SESSION['user_id'] . "',
				 '" . $_SESSION['user_logon'] . "',
				 '" . $_SESSION['user_role'] . "',
				 '" . $poswording . "',
				 '" .$obj->vendor_submittal_id. "',
				 '".(int) $_POST['customer_id']."',
				 '" . (int)$_POST['jetfile_case_id']. "')";	
				$db = getConnection();
				$stmt1 = $db->prepare($sql1);  
				$stmt1->execute();
				//echo json_encode($vendor);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}*/

	/*Solulab Changes #04/06/2019*/
	$activity_uuid = uniqid("KS", false);
	$activity = passed_var("activity", "post");
	$kase = getKaseInfo((int)$_POST['jetfile_case_id']);
	$case_uuid = $kase->uuid;
	$category = 'document';	
	$activity .= " for case " . $kase->case_number . " // " . $kase->name;

	$sql_cse_act = "INSERT INTO cse_activity (`activity_uuid`, `activity`,  `activity_category`, `activity_user_id`, `customer_id`)
	VALUES ('" . $activity_uuid . "', '" . addslashes($activity) . "',  '" . addslashes($category) . "', '" . $_SESSION['user_plain_id'] . "', " . $_SESSION['user_customer_id'] . ")";
	//echo $sql . "\r\n";
	//echo $sql_cse_act; exit;
	$stmt_cse_act = $db->prepare($sql_cse_act);  
	$stmt_cse_act->execute();
	$activity_id = $db->lastInsertId();
	
	//if we passed a valid case
	if ($case_uuid!="") {
		$last_updated_date = date("Y-m-d H:i:s");
		$case_activity_uuid = uniqid("KA", false);
		$attribute = "EAMS Submission";
		$track_id = '-1';
		$last_updated_date = date("Y-m-d H:i:s");
		$sql_cse_case_act = "INSERT INTO cse_case_activity (`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_activity_uuid . "', '" . $case_uuid . "', '" . $activity_uuid . "', '" . $attribute . "', " . $track_id . ", '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		//echo $sql . "\r\n";
		$stmt_cse_case_act = $db->prepare($sql_cse_case_act);  
		$stmt_cse_case_act->execute();
	}	

	try{	
		$sql = "INSERT INTO cse_docucents (`case_id`, `billing_code`, `pos_wording`, `customer_id`,`document_submitted_by`, `vendor_submittal_id`) 
				VALUES ('" . (int)$_POST['jetfile_case_id'] . "',
				 '" . $billingcode . "',
				 '" . $poswording . "',
				 '" .(int) $_POST['customer_id'] . "',
				 '".$_SESSION['user_name']."',
				 '" .  $obj->vendor_submittal_id . "')";
				//$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				echo json_encode($vendor);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	

	
}else{
	$vendor = ["status"=>"201","message"=>"Please Enter Docucents API key to upload Documents."];
	echo json_encode($vendor);
}
}
function addDocucentsAPIKey(){
	$apiKey = $_POST['apikey'];
	try{
	$sql = "UPDATE cse_customer SET docucents_api_key=? WHERE customer_id=?";
	$dpo = getConnectionIkaseMaster();
$stmt= $dpo->prepare($sql);
$save = $stmt->execute([$apiKey, $_SESSION['user_customer_id']]);
echo $save;
} catch(PDOException $e) {	
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}	
}


function letterFileupload(){
	$db = getConnection();
	include('../docusent/cls_docucents.php');

	$caseid=$_POST['caseid'];
	$document_id=$_POST['document_id'];
	$cusid=$_POST['cusid'];

		//Convert DOC_TO_PDF code
		$file_path = '../'.$_POST['letterpath'];
		$secret = 'FvHxVbtzz7O0TxHH';

		if (file_exists($file_path)) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		$verbose = fopen('php://temp', 'w+');
		curl_setopt($curl, CURLOPT_STDERR, $verbose);
		curl_setopt($curl, CURLOPT_USERAGENT, 'ConvertAPI-PHP/1.1.0');
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/octet-stream'));
		curl_setopt($curl, CURLOPT_URL, "https://v2.convertapi.com/docx/to/pdf?secret=".$secret);
		curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CurlFile($file_path)));
		$result = curl_exec($curl);  

			if ($result === FALSE) {
				//printf("cUrl error (#%d): %s<br>\n", curl_errno($curl), htmlspecialchars(curl_error($curl)));
			}

			rewind($verbose);
			$verboseLog = stream_get_contents($verbose);

			//echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";


		if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
			file_put_contents("../uploads/".$_SESSION['user_customer_id']."/".$document_id.".pdf", $result);
		} else {
			//print("Server returned error:\n".$result."\n");
			$vendor = "Pdf Conversion Server error";
			echo json_encode($vendor);
			die;
		}
		} else {
		//print('File does not exist: '.$file_path."\n");
		$vendor = "Letter Not found";
		echo json_encode($vendor);
		die;
		}



		$file = "../uploads/".$_SESSION['user_customer_id']."/".$document_id.".pdf";
		$api_key = getCustomerDocucentsAPIKey($cusid);
		if(!file_exists($file)){
			$vendor = file_exists($file);
			echo json_encode($vendor);
			die;
		}
		if($api_key){
		$obj = new docucents("cmd",$api_key);
		$billingcode = $document_id.uniqid();
		$poswording = "Document uploaded to docucents";
		$obj->Submittals_AddForDelivery($_POST['caseid'].uniqid(),$billingcode , "LETTERID_-" . $document_id.uniqid(), "None", "comment:" . uniqid(), $poswording);
		$obj->PartyData_AddForDelivery("ikase.org", "testSolulab2", "testSolulab2", "", 'address1', "", "testSolula2b", "testSolulab2", "test", "test");
		$origpdf = file_get_contents($file);
		$filecontent = base64_encode($origpdf);
		$filedate = date("Ymd", time());
	
		xmlrpc_set_type($filecontent, "base64");
	
		xmlrpc_set_type($filedate, "datetime");
		$attachment = array(
			"file_name" => "APPFULLPDF_".$_POST['document_id']."_".$cusid."_" . rand(1, 5) . ".pdf",
			"type" => "APPFULLPDF_-" . uniqid(),
			"title" => "APPFULLPDF_-" . uniqid(),
			"unit" => "ADJ",
			"date" => $filedate,
			"author" => "author-".$cusid."-" . uniqid(),
			"base64" => $filecontent
		);
		$temp = $obj->AddAttachment($attachment, false);
	
		$post_result = $obj->SetSubmittalStatus();
		
		try{	
			$sql = "INSERT INTO cse_docucents (`document_id`, `case_id`, `billing_code`, `pos_wording`, `customer_id`, `vendor_submittal_id`, `docucents_upload_date`, `document_submitted_by`) VALUES ('".$_POST['document_id']."','".$caseid . "', '" . $billingcode . "','Letter uploaded to docucents','" . $cusid . "','" .  $obj->vendor_submittal_id . "','" . date("Y-m-d")." ".date("h:i:s"). "','Admin')";
			$db = getConnection();
			//$stmt = $db->query($sql);
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$vendor = "Document Uploaded Successfully!!";
		} catch(PDOException $e) {	
			$vendor="Please try again"; 
		}	
		echo json_encode($vendor);
	}else{
		$vendor ="Docucents API key Invalid";
		echo json_encode($vendor);
	}
}
//solulab code end - 27-05-2019
?>

