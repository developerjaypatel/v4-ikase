<?php
$app->get('/scanfiles/:search_term/:api_key/:customer_id', 'scanCaseSearch');
$app->get('/scanallfiles/:api_key/:customer_id', 'scanCases');
$app->get('/scancase/:api_key/:injury_id/:customer_id', 'scanCase');
$app->get('/scancustomers/:search_term/:api_key', 'scanCustomers');
$app->get('/scantypes/:api_key/:customer_id', 'scanDocumentTypes');

$app->post('/scanfiles/add', 'addScanFile');

function getDbName($customer_id) {
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	try {				
		$db = getConnection();
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		
		//die(print_r($customer));
		
		$cus_name = $customer->cus_name;
		$data_source = $customer->data_source;
		if ($data_source=="") {
			$return = "ikase";
		}
		if ($data_source!="") {
			$return = "ikase_" . $data_source;
		}
		$db = null;

		return $return;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function addScanFile() {
	session_write_close();
	$api_key = passed_var("api_key", "post");
	$customer_id = passed_var("customer_id", "post");
	
	$scan_db_name = getDbName($customer_id);
	
	if ($api_key!="f7e87145b17b20b962d7402830df976a") {
		$error = array("error"=> array("text"=>"api key error"));
		echo json_encode($error);
		die();
	}
	if (!is_numeric($customer_id) || $customer_id < 1033) {
		$error = array("error"=> array("text"=>"id invalid"));
		echo json_encode($error);
		die();
	}
	$case_id = "";
	$uploadDir = 'D:\\uploads\\' . $customer_id . '\\';
	
	//die($_SERVER['DOCUMENT_ROOT'] . $uploadDir . "<br />" . is_dir($uploadDir));
	if (!is_dir($uploadDir)) {
		mkdir($uploadDir, 0755, true);
	}
	
	//die($_SERVER['DOCUMENT_ROOT'] . $uploadDir);
	//Define the directory to store the PDF Preview Image
	$thumbDirectory = '/pdfimage/' . $_SESSION['user_customer_id'] . '/';
	if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $thumbDirectory)) {
		mkdir($_SERVER['DOCUMENT_ROOT'] . $thumbDirectory, 0755, true);
	}
	// Set the allowed file extensions
	//$fileTypes = array('jpg', 'jpeg', 'gif', 'png', 'pdf', 'fdf', 'rtf', 'txt', 'csv', 'doc', 'docx'); // Allowed file extensions
	$fileTypes = array('pdf');
	
	//die(print_r($_FILES));
	if (!empty($_FILES)) {
		//the fucking array may have different names...
		foreach($_FILES as $array_index=>$file_array) {
			$index_name = $array_index;
			break;
		}
		//die($index_name);
		$tempFile   = $_FILES[$index_name]['tmp_name'];
		$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
		$targetFile = $_FILES[$index_name]['name'];
		$document_counter = 1;
		
		$targetFile = $uploadDir. $targetFile;
		
		$arrFile = explode(".", $targetFile);
		$thumbFile = "";
		if (strtolower($arrFile[count($arrFile)-1])=="pdf") {
			$thumbFile = str_replace(".pdf", ".jpg", $targetFile);
			$thumbFile = str_replace("\\uploads\\", "\\pdfimage\\", $thumbFile);
		}
		$thumbnail_folder = str_replace("\\uploads\\", "\\pdfimage\\", $uploadDir);
		// Validate the filetype
		$fileParts = pathinfo($_FILES[$index_name]['name']);
		if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
			// Save the file
			//die( $tempFile . "<br />");
			move_uploaded_file($tempFile, $targetFile);
			
			//die($targetFile . "<br />" . $thumbFile . "<br />");
			if ($thumbFile!="") {
				//execute imageMagick's 'convert', setting the color space to RGB
				//This will create a jpg having the width of 200PX
				//exec("convert \"{$targetFile}[0]\" -colorspace RGB -geometry 200 \"$thumbFile\"");
				//die("convert \"{$targetFile}[0]\" -colorspace RGB -geometry 200 \"$thumbFile\"");				
				
				$image_magick = new imagick(); 
				$image_magick->setbackgroundcolor('white');
				$image_magick->readImage($targetFile[0]);
				$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
				$image_magick->setResolution(300,300);
				$image_magick->thumbnailImage(102, 102, true);
				$image_magick->setImageFormat('jpg');
				//$thumbnail_path = $upload_dir . "\\medium\\" . str_replace(".pdf", ".jpg", $file->name);
				$thumbnail_path = $thumbFile;
				$image_magick->writeImage($thumbnail_path);						
			}
			$arrFileDetails = explode("\\", $targetFile);
			$targetFile = $arrFileDetails[count($arrFileDetails) - 1];
			//echo $targetFile;
			
			$sql = "INSERT INTO `{$scan_db_name}`.cse_batchscan (`filename`, `customer_id`, `separated`, `stacked`, `processed`) 
			VALUES (:filename, :customer_id, 'Y', 'Y', '" . date("Y-m-d H:i:s") . "')";
			try {
				
				$db = getConnection();
				//die($sql); 
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("filename", $targetFile);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$batchscan_id = $db->lastInsertId();
				
				$sql = "INSERT INTO `{$scan_db_name}`.cse_batchscan_track (`user_uuid`, `user_logon`, `operation`, `batchscan_id`, `dateandtime`, `filename`, `time_stamp`, `pages`, `consideration`, `attempted`, `completion`, `match`, `separators`, `stacks`, `stitched`, `customer_id`, `readimage`, `processed`, `separated`, `stacked`, `deleted`)
				SELECT 'scanfiles', 'api call', 'insert', `batchscan_id`, `dateandtime`, `filename`, `time_stamp`, `pages`, `consideration`, `attempted`, `completion`, `match`, `separators`, `stacks`, `stitched`, `customer_id`, `readimage`, `processed`, `separated`, `stacked`, `deleted`
				FROM `{$scan_db_name}`.cse_batchscan
				WHERE 1
				AND batchscan_id = " . $batchscan_id . "
				AND customer_id = " . $customer_id . "
				LIMIT 0, 1";
				
				//add as document
				//add the stack as a document, unattached so far
				$table_uuid = uniqid("KS", false);
				$document_name = $targetFile;
				$document_date = date("Y-m-d H:i:s");
				$document_extension = ".pdf";
				$description = "scanfiled document";
				$description_html = $description;	//for now
				$type = "batchscan";
				$verified = "Y";
				
				$sql = "INSERT INTO `{$scan_db_name}`.cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, thumbnail_folder, description, description_html, type, verified, customer_id) 
						VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :thumbnail_folder, :description, :description_html, :type, :verified, :customer_id)";
						
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $table_uuid);
				//reason for batch uuid, use batch id for now
				$stmt->bindParam("parent_document_uuid", $batchscan_id);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("thumbnail_folder", $thumbnail_folder);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->bindParam("verified", $verified);
				$stmt->execute();
				$new_id = $db->lastInsertId();
				
				$notification_uuid = uniqid("KN", false);
				$sql = "INSERT INTO `{$scan_db_name}`.`cse_notification` (`document_uuid`, `notification_uuid`, `user_uuid`, `notification`, `notification_date`, `customer_id`)
				VALUES ('" . $table_uuid . "', '" . $notification_uuid . "', 'system', 'review', '" . date("Y-m-d H:i:s") . "', '" . $customer_id . "')";
				
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				
				$db = null;
				
				echo json_encode(array("batchscan_id"=>$batchscan_id, "document_id"=>$new_id, "filename"=>$targetFile)); 
			} catch(PDOException $e) {	
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		} else {
			// The file type wasn't allowed
			echo 'Invalid file type.';
		}
	}
}
function scanCustomers($search_term, $api_key) {
	session_write_close();
	
	if ($api_key!="f7e87145b17b20b962d7402830df976a") {
		$error = array("error"=> array("text"=>"api key error"));
		echo json_encode($error);
		die();
	}
	$search_term = clean_html($search_term);
	$arrSearch = explode("=", $search_term);
	$search_filter = $arrSearch[0];
	$search_term = str_replace("~", "%", $arrSearch[1]);
	
	if ($search_term=="" || $search_term=="%") {
		$error = array("error"=> array("text"=>"empty search term"));
		echo json_encode($error);
		die();
	}
	$sql = "SELECT customer_id FROM `ikase`.cse_customer WHERE 1
	AND cus_name LIKE '" . $search_term . "'";
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$customers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($customers);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function scanCaseSearch($search_term, $api_key, $customer_id){
	session_write_close();
	
	if ($api_key!="f7e87145b17b20b962d7402830df976a") {
		$error = array("error"=> array("text"=>"api key error"));
		echo json_encode($error);
		die();
	}
	if (!is_numeric($customer_id)) {
		$error = array("error"=> array("text"=>"id invalid"));
		echo json_encode($error);
		die();
	}
	
	$scan_db_name = getDbName($customer_id);
	
	//die("here");
	$search_term = clean_html($search_term);
	$arrSearch = explode("=", $search_term);
	$search_filter = strtolower($arrSearch[0]);
	$search_term = str_replace("~", "%", $arrSearch[1]);
	
	if ($search_term=="" || $search_term=="%") {
		$error = array("error"=> array("text"=>"empty search term"));
		echo json_encode($error);
		die();
	}
	$sql = "SELECT DISTINCT 
			inj.injury_id caseid, CONCAT(ccase.case_number, '-', inj.injury_number) case_number, 
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) IWFirst , IF(app.last_name IS NULL, '', app.last_name) IWLast, 
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, 
			SUBSTRING(app.ssn, 6, 4) ssn,
			employer.`company_name` defendant, 
			CONCAT(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') = '00/00/0000', '', CONCAT('-', DATE_FORMAT(inj.end_date, '%m/%d/%Y'), ' CT'))) doi
			FROM `{$scan_db_name}`.cse_case ccase
			INNER JOIN `ikase`.cse_customer cus
			ON ccase.customer_id = cus.customer_id
			LEFT OUTER JOIN `{$scan_db_name}`.cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "`{$scan_db_name}`.cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			INNER JOIN `{$scan_db_name}`.`cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			INNER JOIN `{$scan_db_name}`.`cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N' AND cinj.deleted = 'N'
			
			WHERE ccase.deleted ='N' 
			AND cus.customer_id = '" . $customer_id . "'";
	//search now
	switch($search_filter) {
		case "iwfirst":
			$sql .= " AND app.first_name LIKE '" . $search_term . "'";
			break;
		case "iwlast":
			$sql .= " AND app.last_name LIKE '" . $search_term . "'";
			break;
		case "casenumber":
			$arrCaseNumber = explode("-", $search_term);
			if (count($arrCaseNumber) > 0) {
				$case_number = $arrCaseNumber[0];
				$sql .= " AND ccase.case_number LIKE '" . $case_number . "'";
			}
			if (count($arrCaseNumber) > 1) {
				$injury_number = $arrCaseNumber[1];
				$sql .= " AND inj.injury_number = '" . $injury_number . "'";
			}
			break;
		case "defendant":
			$sql .= " AND employer.`company_name` LIKE '" . $search_term . "'";
			break;
		case "dob":
			$sql .= " AND app.`dob` = '" . date("Y-m-d", strtotime($search_term)) . "'";
			break;
		case "ssn":
			$sql .= " AND SUBSTRING(app.ssn, 6, 4) = '" . $search_term . "'";
			break;
		default:
			$error = array("error"=> array("text"=>"invalid filter"));
        	echo json_encode($error);
			die();
	}
	
	$sql .= " ORDER by ccase.case_number, inj.injury_number";
	//echo $sql;
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function scanDocumentTypes($api_key, $customer_id) {
	session_write_close();
	
	if ($api_key!="f7e87145b17b20b962d7402830df976a") {
		$error = array("error"=> array("text"=>"api key error"));
		echo json_encode($error);
		die();
	}
	if (!is_numeric($customer_id)) {
		$error = array("error"=> array("text"=>"id invalid"));
		echo json_encode($error);
		die();
	}
	
	$sql = "SELECT document_type_id, `name` `description`
	FROM cse_document_type 
	WHERE customer_id = " . $customer_id;
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$types = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($types);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function scanCases($api_key, $customer_id){
	session_write_close();
	
	if ($api_key!="f7e87145b17b20b962d7402830df976a") {
		$error = array("error"=> array("text"=>"api key error"));
		echo json_encode($error);
		die();
	}
	if (!is_numeric($customer_id)) {
		$error = array("error"=> array("text"=>"id invalid"));
		echo json_encode($error);
		die();
	}
	$scan_db_name = getDbName($customer_id);
	
	$sql = "SELECT DISTINCT 
			inj.injury_id caseid, CONCAT(ccase.case_number, '-', inj.injury_number) case_number, 
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) IWFirst , IF(app.last_name IS NULL, '', app.last_name) IWLast, 
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, 
			SUBSTRING(app.ssn, 6, 4) ssn,
			employer.`company_name` defendant, 
			CONCAT(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') = '00/00/0000', '', CONCAT('-', DATE_FORMAT(inj.end_date, '%m/%d/%Y'), ' CT'))) doi
			FROM `{$scan_db_name}`.cse_case ccase
			INNER JOIN `ikase`.cse_customer cus
			ON ccase.customer_id = cus.customer_id
			LEFT OUTER JOIN`{$scan_db_name}`.cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN ";
	if (($_SERVER['REMOTE_ADDR']=='173.55.229.70B' && $_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "`{$scan_db_name}`.cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			#JOIN TO a min corporation_id in case of multiple employers
			INNER JOIN
				(SELECT 
					MIN(corporation_id) `corporation_id`
				FROM
					{$scan_db_name}.cse_case ccase
				INNER JOIN {$scan_db_name}.cse_case_corporation ccorp ON ccase.case_uuid = ccorp.case_uuid
				INNER JOIN {$scan_db_name}.cse_corporation corp ON ccorp.corporation_uuid = corp.corporation_uuid
					AND `attribute` = 'employer'
				WHERE
					1
				GROUP BY case_id) single_employer 
				
			ON employer.corporation_id = single_employer.corporation_id
				
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N' AND cinj.deleted = 'N'
			
			WHERE ccase.deleted ='N' 
			AND ccase.case_status NOT LIKE '%close%'
			AND cus.customer_id = '" . $customer_id . "'";
	$sql .= " ORDER by ccase.case_number, inj.injury_number";
	
	//echo $sql . "\r\n";
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		echo json_encode($kases);
		
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function scanCase($api_key, $injury_id, $customer_id){
	session_write_close();
	
	if ($api_key!="f7e87145b17b20b962d7402830df976a") {
		$error = array("error"=> array("text"=>"api key error"));
		echo json_encode($error);
		die();
	}
	if (!is_numeric($customer_id)) {
		$error = array("error"=> array("text"=>"id invalid"));
		echo json_encode($error);
		die();
	}
	$scan_db_name = getDbName($customer_id);
	
	if (!is_numeric($injury_id)) {
		$error = array("error"=> array("text"=>"id invalid"));
		echo json_encode($error);
		die();
	}
	$sql = "SELECT DISTINCT 
			inj.injury_id caseid, CONCAT(ccase.case_number, '-', inj.injury_number) case_number, 
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) IWFirst , IF(app.last_name IS NULL, '', app.last_name) IWLast, 
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, 
			SUBSTRING(app.ssn, 6, 4) ssn,
			employer.`company_name` defendant, 
			CONCAT(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') = '00/00/0000', '', CONCAT('-', DATE_FORMAT(inj.end_date, '%m/%d/%Y'), ' CT'))) doi
			FROM `{$scan_db_name}`.cse_case ccase
			INNER JOIN `ikase`.cse_customer cus
			ON ccase.customer_id = cus.customer_id
			LEFT OUTER JOIN`{$scan_db_name}`.cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `{$scan_db_name}`.`cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			INNER JOIN `{$scan_db_name}`.`cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			INNER JOIN `{$scan_db_name}`.`cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N' AND cinj.deleted = 'N'
			
			WHERE ccase.deleted ='N' 
			AND cus.customer_id = '" . $customer_id . "'
			AND inj.injury_id = '" . $injury_id . "'";
	$sql .= " ORDER by ccase.case_number, inj.injury_number";
	//echo $sql;
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}

?>