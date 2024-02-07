<?php

$app->get('/drop/:id', 'getDrop');
$app->get('/drops', 'getDrops');
$app->post('/drop/add', 'addDrop');
$app->post('/drop/update', 'updateDrop');
$app->post('/drop/delete', 'deleteDrop');

$app->get('/costs', 'getCosts');
$app->get('/pings/:debtor_id', 'getPings');
$app->get('/ping/report/:batch_id/:drip_id/:drop_id/:ping_number', 'getPingReport');
$app->get('/drips/summary', 'summarySequences');
$app->get('/drops/summary', 'summaryDrops');

$app->get('/drips', 'getDrips');
$app->get('/drip/:drip_id', 'getDrip');
$app->post('/drip/add', 'addDrip');
$app->post('/drip/delete', 'deleteDrip');
$app->post('/drip/update', 'updateDrip');

$app->post('/drip/confirm', 'confirmPings');

function summarySequences() {
	$sql = "SELECT IF(locked_drips.`drip_uuid` IS NULL, 'open', locked_drips.`status`) `status`, COUNT(drip_id) drip_count 
	FROM `tbl_drip` td 
	LEFT OUTER JOIN (
		SELECT DISTINCT `status`, tbd.drip_uuid 
		FROM `tbl_batch` tb 
		LEFT OUTER JOIN `tbl_batch_drip` tbd ON tb.batch_uuid = tbd.batch_uuid AND tbd.deleted = 'N' 
		LEFT OUTER JOIN  `tbl_drip` td
		ON tbd.drip_uuid = td.drip_uuid
		WHERE 1 
		AND tb.deleted = 'N' 
		AND td.deleted = 'N'
	) locked_drips
	ON `td`.drip_uuid = locked_drips.drip_uuid
	WHERE 1 AND td.deleted = 'N' 
	GROUP BY IF(locked_drips.`drip_uuid` IS NULL, 'open', locked_drips.`status`)
	UNION 
	SELECT 'total' `status`, COUNT(drip_id) drip_count 
	FROM `tbl_drip` td 
	WHERE 1 AND td.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$summaries = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		
		$db = null;
		
		echo json_encode($summaries);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function summaryDrops() {
	$sql = "SELECT IF(locked_drops.`drop_uuid` IS NULL, 'open', locked_drops.`status`) `status`, COUNT(drop_id) drop_count 
	FROM `tbl_drop` tdrop 
	LEFT OUTER JOIN (
		SELECT DISTINCT tb.`status`, tbd.drop_uuid 
		FROM `tbl_batch` tb 
		LEFT OUTER JOIN `tbl_batch_drop` tbd ON tb.batch_uuid = tbd.batch_uuid AND tbd.deleted = 'N' 
		LEFT OUTER JOIN  `tbl_drop` tdrop
		ON tbd.drop_uuid = tdrop.drop_uuid
		WHERE 1 
		AND tb.deleted = 'N' 
		AND tdrop.deleted = 'N'
	) locked_drops
	ON `tdrop`.drop_uuid = locked_drops.drop_uuid
	WHERE 1 AND tdrop.deleted = 'N' 
	GROUP BY IF(locked_drops.`drop_uuid` IS NULL, 'open', locked_drops.`status`)
	UNION 
	SELECT 'total' `status`, COUNT(drop_id) drop_count 
	FROM `tbl_drop` tdrop 
	WHERE 1 AND tdrop.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$summaries = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		
		$db = null;
		
		echo json_encode($summaries);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getDrop($id) {
	$sql = "SELECT *, tdrop.drop_id `id`, tdrop.drop_uuid `uuid`, 
	IF(locked_drops.`drop_uuid` IS NULL, 'open', 'locked') `drop_status`
	FROM `tbl_drop` tdrop
	LEFT OUTER JOIN (
		SELECT DISTINCT tbd.drop_uuid 
		FROM `tbl_batch` tb 
		LEFT OUTER JOIN `tbl_batch_drop` tbd 
		ON tb.batch_uuid = tbd.batch_uuid 
		AND tbd.deleted = 'N' 
		WHERE 1 AND tb.deleted = 'N' 
		AND (`status` = 'locked' OR `status` = 'launched')
	) locked_drops
	ON tdrop.drop_uuid = locked_drops.drop_uuid
	WHERE 1
	AND tdrop.drop_id = :id
	AND tdrop.deleted = 'N'";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$drop = $stmt->fetchObject();
		$db = null;
		
		
		if (isset($drop->content)) {
			$db_content = $drop->content;
			
			$arrContent = explode("\n", $db_content);
			foreach($arrContent as $index_c=>$the_content) {
				if ($the_content=="") {
					unset($arrContent[$index_c]);
				} else {
					//remove any tabs
					$arrContent[$index_c] = str_replace("	", "&#09;", $the_content);
				}
			}
			$db_content = implode("[CARRIAGE_RETURN]", $arrContent);
			$db_content = str_replace("[CARRIAGE_RETURN]", "<br>", $db_content);
			//echo $db_content;
			$drop->content = $db_content;
		}
		echo json_encode($drop);
		die();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDrops() {

	$sql = "SELECT `tbd`.`drop_id`, `tbd`.`drop_uuid`, `tbd`.`content`, LOWER(`tbd`.`tone`) `tone`, LOWER(`tbd`.`emphasis`) `emphasis`, 
			`tbd`.`description`, `tbd`.`short_description`, `tbd`.`drop_ivr`, `tbd`.`time_of_day`, `tbd`.`interval`, `tbd`.`status`, `tbd`.`deleted`,
			`tbd`.`drop_id` `id`, `tbd`.`drop_uuid` `uuid`, emph.color emphasis_color, emph.text_color emphasis_text_color,
			IF(locked_drops.`drop_uuid` IS NULL, 'open', 'locked') `drop_status`
			FROM `tbl_drop` `tbd`
			LEFT OUTER JOIN `tbl_emphasis` `emph`
			ON `tbd`.emphasis = emph.emphasis
			LEFT OUTER JOIN (
				SELECT DISTINCT tbd.drop_uuid 
				FROM `tbl_batch` tb 
				LEFT OUTER JOIN `tbl_batch_drop` tbd 
				ON tb.batch_uuid = tbd.batch_uuid 
				AND tbd.deleted = 'N' 
				WHERE 1 AND tb.deleted = 'N' 
				AND (`status` = 'locked' OR `status` = 'launched')
			) locked_drops
			ON `tbd`.drop_uuid = locked_drops.drop_uuid
			WHERE 1
			AND `tbd`.deleted = 'N'
			ORDER BY drop_id ASC";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$drops = $stmt->fetchAll(PDO::FETCH_OBJ);
        // die(print_r($drops));
		foreach($drops as $drop) {
			$sql = "SELECT COUNT(DISTINCT drip_id) `drip_count` 
			FROM `tbl_drip` 
			WHERE 1 
			AND deleted = 'N'
			AND REPLACE(`content`, '\\\', '') LIKE '%\"id\":\"" . $drop->drop_id . "\"%'";
			// for each drop isolate the drop_ivr inside the content and then remove the slashes.
            $content = $drop->content;
            $content = json_decode($content);
            // die(print_r($content));
            // echo $drop->drop_id;
            // echo print_r($content);
            // die($content->meta->drop_ivr);
            if (isset($content->meta->drop_ivr)) {
                $drop_ivr = $content->meta->drop_ivr;
                // echo $drop_ivr;
                // die();
                $drop_ivr = stripslashes($drop_ivr);
                
                $content->meta->drop_ivr = $drop_ivr;
                
                // $content->meta = $meta;
                $content = json_encode($content);
                $drop->content = $content;
                $drop->is_IVR_there = "yes";
            } else {
                $drop->is_IVR_there = "no";
            }
            
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$drips = $stmt->fetchObject();
			$drop->drip_count = $drips->drip_count;
		}
		$db = null;
		
		echo json_encode($drops);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDropInfo($id) {
	$sql = "SELECT *, drop_id `id`, drop_uuid `uuid`
	FROM `tbl_drop` 
	WHERE 1
	AND drop_id = :id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$drop = $stmt->fetchObject();
		$db = null;
		return $drop;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addDrop() {
	$id = passed_var("id", "post");
	// die($id);
	if (!is_numeric($id)) {
		die();
	}
	if ($id > 0) {
		// die("hello");
		updateDrop();
		return;
	}
	$table_uuid = uniqid("DR", false);
	
	$arrRes = array();	//"uuid"=>$table_uuid, "content"=>$content);
	$language = "";
	$method = "";
	$tone = "";
	$time_of_day = "";
	$description = "";
	$short_description = "";
	$drop_ivr = "";
	$status = "active";
	$emphasis = "";
	$cascade = "";
	$interval = "";
	$arrContent = array();
	$arrMethods = array();
	$drip_id = 0;

	foreach($_POST as $fieldname=>$value) {
		/*
		if ($fieldname == "email" || $fieldname == "mail" || $fieldname == "text" || $fieldname == "voicemail") {
			continue;
		}
		*/
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("meta_", "", $fieldname);
		
		if ($fieldname == "language") {
			//$language = $value;
			continue;
		}
		if ($fieldname == "tone") {
			$tone = $value;
		}
		if ($fieldname == "interval") {
			$interval = $value;
		}
		if ($fieldname=="time_of_day") {
			$time_of_day = $value;
		}
		if ($fieldname=="description") {
			$description = $value;
		}
		if ($fieldname=="short_description") {
			$short_description = $value;
		}
		if ($fieldname=="drop_ivr") {
			$drop_ivr = $value;
		}
		if ($fieldname=="status") {
			$status = $value;
		}
		if ($fieldname=="emphasis") {
			$emphasis = $value;
		}
		
		if ($fieldname == "drip_id") {
			$drip_id = $value;
			continue;
		}
		
		if (strpos($fieldname, "content_")===0) {
			$arrM = explode("_", $fieldname);
			$method = $arrM[1];
			$language = $arrM[2];
			//keep track of methods offered, all languages
			if (!in_array($method, $arrMethods)) {
				$arrMethods[] = $method;
			}
			//actual content of the text box
			$arrContent[$language][$method] = $value;
			continue;
		}
		if ($fieldname == "cascade" && $fieldname=="id") {
			//obsolete
			continue;
		}
		
		$arrRes[$fieldname] = $value;
	}
	
	$arrRes["cascade"] = $arrMethods;
	//die(print_r($arrContent));
	
	//$arrLanguageContent = array($language=>$arrContent);
	
	//die(print_r($arrLanguageContent));
	$result = json_encode(array("uuid"=>$table_uuid, "languages"=>$arrContent, "meta"=>$arrRes));
	
	//die($result);
	//$response = json_decode($result);
	
	//die(print_r($response));
	$sql = "INSERT INTO tbl_drop (`drop_uuid`, `content`, `description`, `short_description`, `drop_ivr`, `tone`, `emphasis`, `status`)
	VALUES ('" . $table_uuid . "', '" . addslashes($result) . "', '" . addslashes($description) . "','" . addslashes($short_description) . "','" . addslashes($drop_ivr) . "','" . addslashes($tone) . "','" . addslashes($emphasis) . "','" . addslashes($status) . "')";
	//die($sql);
	try { 
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		//do we have a drip id
		if ($drip_id > 0) {
			//attach it via drip drop
			//get your drip uuid first
			//and then attach with new $table_uuid
		}
		die(json_encode(array("id"=>$new_id, "uuid"=>$table_uuid))); 
		
		$db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	//die(json_encode(array("success"=>true)));
}
function updateDrop() {
	$id = passed_var("id", "post");
	if (!is_numeric($id)) {
		die();
	}
	$drop = getDropInfo($id);
	$post = $_POST;
	// die(print_r($drop));
	$table_uuid = $drop->uuid;
	$db_content = "";
	if($drop->content != ""){
		$db_content = $drop->content;
	}
	
	$meta = new stdClass();
	$db_languages = new stdClass();
	if($db_content != ""){
		$arrContent = explode("\n", $db_content);
		if (count($arrContent) > 0) {
			foreach($arrContent as $index_c=>$the_content) {
				if ($the_content=="") {
					unset($arrContent[$index_c]);
				} else {
					//remove any tabs
					$arrContent[$index_c] = str_replace("	", "[TAB]", $the_content);
				}
			}
			$db_content = implode("[CARRIAGE_RETURN]", $arrContent);
			$drop = json_decode($db_content);
			
			if($drop->meta != ""){
				$meta = $drop->meta;
			}
			
			$db_languages = $drop->languages;
		}
	}
	
	
	//die(print_r($drop));
	//update the json data
	$arrRes = array();	//"uuid"=>$table_uuid, "content"=>$content);
	$language = "";
	$tone = "";
	$description = "";
	$short_description = "";
	$drop_ivr = "";
	$time_of_day = "";
	$interval = "";
	$status = "";
	$emphasis = "";
	$arrMethods = array();
	$arrContent = array();
	$arrActualContent = array();
	//die($id);
	foreach($_POST as $fieldname=>$value) {
		$fieldname = str_replace("meta_", "", $fieldname);
		if ($fieldname=="id") {
			continue;
		}
		if ($fieldname == "language") {
			//$language = $value;
			continue;
		}
		if ($fieldname == "tone") {
			$tone = $value;
		}
		if ($fieldname == "interval") {
			$interval = $value;
		}
		if ($fieldname=="time_of_day") {
			$time_of_day = $value;
		}
		if ($fieldname=="description") {
			$description = $value;
		}
		if ($fieldname=="short_description") {
			$short_description = $value;
		}
		if ($fieldname=="drop_ivr") {
			$drop_ivr = $value;
		}
		if ($fieldname=="status") {
			$status = $value;
		}
		if ($fieldname=="emphasis") {
			$emphasis = $value;
		}
		if ($fieldname == "cascade") {
			$cascade = $value;
			$arrCascades = explode("|", $cascade);
			$meta->{$fieldname} =  json_decode(json_encode($arrCascades));
			continue;
		}
		if (strpos($fieldname, "content_")===0) {
			$arrM = explode("_", $fieldname);
			$method = $arrM[1];
			$language = $arrM[2];
			//keep track of methods offered, all languages
			if (!in_array($method, $arrMethods)) {
				$arrMethods[] = $method;
			}
			//actual content of the text box
			$arrContent[$method] = $value;
			$arrActualContent[$language][$method] = $value;
			continue;
		}
		
		if ($fieldname=="description" || $fieldname=="short_description") {
			$value = str_replace("'", "`", $value);
		}
		$meta->{$fieldname} = $value;
	}
	if ($interval=="") {
		//default
		$interval = "3";
	}
	//die(print_r($meta));
	foreach($meta as $meta_index=>$meta_value) {
		$arrRes[$meta_index] = $meta_value;
	}
	
	$arrRes["cascade"] = $arrMethods;
	//die(print_r($arrRes));
	
	//die(print_r($arrMethods));
	$arrLanguageContent = $arrActualContent;
	// die(print_r($arrLanguageContent));
	foreach($arrLanguageContent as $language=>$content_array) {
		//die(json_encode($content_array));
		//die($language);
		if (is_object($db_languages)) {
			$db_languages->{$language} = json_decode(json_encode($content_array), FALSE);
		} else {
			$db_languages = (object) array($language=>json_decode(json_encode($content_array), FALSE));
		}
	}
	//die(print_r($db_languages));
	$arrLanguages = array();
	foreach($db_languages as $language=>$content){
		//$content = json_decode(addslashes(json_encode($content)));
		foreach($content as $content_index=>$content_value) {
			$arrContentOutput[$content_index] = $content_value;
		}
		//die(print_r($arrContentOutput));
		$arrLanguages[$language] = $arrContentOutput;
	}

	$arrRes = array();
	foreach($meta as $index=>$value){
		if (is_array($value)) {
			$arrSubValues = array();
			foreach($value as $subvalue) {
				$subvalue = addslashes($subvalue);
				$arrSubValues[] = $subvalue;
			}
			$arrRes[$index] = $arrSubValues; 
		} else {
			$arrRes[$index] = addslashes($value);
		}
		
	}
	$content = json_encode(array("uuid"=>$table_uuid, "languages"=>$arrLanguages, "meta"=>$arrRes));
	
	//die($content);
	$sql = "UPDATE `tbl_drop` 
	SET `content` = '" . str_replace("'", "\'", $content) . "',
	`tone` = '" . addslashes($tone) . "',
	`status` = '" . $status . "',
	`description` = '" . addslashes($description) . "',
	`short_description` = '" . addslashes($short_description) . "',
	`drop_ivr` = '" . addslashes($drop_ivr) . "',
	`emphasis` = '" . addslashes($emphasis) . "'
	WHERE drop_id = :id";
	//die($sql);
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		
		echo json_encode(array("id"=>$id)); 
		
		$db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteDrop() {
	$id = passed_var("id", "post");
	//die(print_r($id));
	$sql = "UPDATE tbl_drop
			SET `deleted` = 'Y'
			WHERE `drop_id`=:id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode(array("success"=>"drop marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}	
}
function getDrips() {
	$sql = "SELECT `tbl_drip`.*,
	`drip_id` `id`, `tbl_drip`.`drip_uuid` `uuid`, 
	IFNULL(`batch_count`, 0) `batch_count`,
	IFNULL(`ping_count`, 0) `ping_count`,
	IF(locked_drips.`drip_uuid` IS NULL, 'open', 'locked') `drip_status`
	FROM `tbl_drip` 
	LEFT OUTER JOIN (
		SELECT tbdrip.`drip_uuid`, COUNT(tbdrip.`batch_uuid`)  batch_count
		FROM `tbl_batch_drip` tbdrip 
		INNER JOIN tbl_batch tb ON tbdrip.batch_uuid = tb.batch_uuid AND tb.deleted = 'N'
		WHERE 1 
		AND tbdrip.deleted = 'N'
		GROUP BY tbdrip.`drip_uuid`
	) batchinfo 
	ON `tbl_drip`.drip_uuid = batchinfo.drip_uuid
	LEFT OUTER JOIN (
		SELECT tbdrop.`drip_uuid`, COUNT(tbdrop.`drop_uuid`)  ping_count
		FROM `tbl_drip_drop` tbdrop 
		INNER JOIN tbl_drip td ON tbdrop.drip_uuid = td.drip_uuid AND tbdrop.deleted = 'N'
		WHERE 1
		GROUP BY tbdrop.`drip_uuid`
	) pinginfo
	ON `tbl_drip`.drip_uuid = pinginfo.drip_uuid
	
	LEFT OUTER JOIN (
		SELECT DISTINCT tbd.drip_uuid FROM `tbl_batch` tb LEFT OUTER JOIN `tbl_batch_drip` tbd ON tb.batch_uuid = tbd.batch_uuid AND tbd.deleted = 'N' WHERE 1 AND tb.deleted = 'N' AND (`status` = 'locked' OR `status` = 'launched')
	) locked_drips
	ON `tbl_drip`.drip_uuid = locked_drips.drip_uuid
	
	WHERE 1
	AND `tbl_drip`.deleted = 'N'
	ORDER BY `tbl_drip`.`name` ASC, `tbl_drip`.`drip_id` ASC";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("drip_id", $drip_id);
		$stmt->execute();
		$drips = $stmt->fetchAll(PDO::FETCH_OBJ);
		/*
		foreach($drops as $drop) {
			$sql = "SELECT COUNT(DISTINCT drip_id) `drip_count` 
			FROM `tbl_drip` 
			WHERE 1 
			AND deleted = 'N'
			AND REPLACE(`content`, '\\\', '') LIKE '%\"id\":\"" . $drop->drop_id . "\"%'";
			
			//die($sql);
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$drips = $stmt->fetchObject();
			$drop->drip_count = $drips->drip_count;
		}
		*/
		$db = null;
		/*
		foreach($drips as $drip) {
			echo stripslashes($drip->content);
		}
		*/
		echo json_encode($drips);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCostsInfo() {
	$sql2 = "SELECT `tbl_cascade`.`cascade`, `tbl_cascade`.`cascade_cost` 
	FROM `tbl_cascade` 
	WHERE 1";
	//die($sql. "/r/n");
	try {
		$db = getConnection();
		
		$stmt2 = $db->prepare($sql2);
		$stmt2->execute();
		$costs = $stmt2->fetchAll(PDO::FETCH_OBJ);
		
		$db = null;
		//die(print_r($costs));
		return $costs;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getCosts() {
	$sql2 = "SELECT `tbl_cascade`.`cascade`, `tbl_cascade`.`cascade_cost` 
	FROM `tbl_cascade` 
	WHERE 1";
	//die($sql. "/r/n");
	try {
		$db = getConnection();
		
		$stmt2 = $db->prepare($sql2);
		$stmt2->execute();
		$costs = $stmt2->fetchAll(PDO::FETCH_OBJ);
		
		$db = null;
		//die(print_r($costs));
		echo json_encode($costs);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getDrip($drip_id) {
	$sql = "SELECT `tbl_drip`.`drip_id`,
    `tbl_drip`.`drip_uuid`,
    `tbl_drip`.`name`,
    `tbl_drip`.`description`,
	`tbl_drip`.`drip_ivr`,
	`tbl_drip`.`drip_type`,
	`tbl_drip`.`contact_methods`,
    `tbl_drip`.`content`,
    `tbl_drip`.`max_pings`,
    `tbl_drip`.`signed_by`,
    `tbl_drip`.`weeks`,
	`tbl_drip`.`verification_costs`,
    `tbl_drip`.`override_IVR`,
    `tbl_drip`.`deleted`,
	`tbl_drip`.`drip_id` `id`, `tbl_drip`.`drip_uuid` `uuid`,
	IFNULL(`batch_count`, 0) `batch_count`,
	IFNULL(`ping_count`, 0) `ping_count`,
	IF(locked_drips.`drip_uuid` IS NULL, 'open', 'locked') `drip_status`
	FROM `tbl_drip` 	
	LEFT OUTER JOIN (
		SELECT tbdrip.`drip_uuid`, COUNT(tbdrip.`batch_uuid`)  batch_count
		FROM `tbl_batch_drip` tbdrip 
		INNER JOIN tbl_batch tb ON tbdrip.batch_uuid = tb.batch_uuid
		WHERE 1
		AND tbdrip.deleted = 'N'
		GROUP BY tbdrip.`drip_uuid`
	) batchinfo 
	ON `tbl_drip`.drip_uuid = batchinfo.drip_uuid
	LEFT OUTER JOIN (
		SELECT tbdrop.`drip_uuid`, COUNT(tbdrop.`drop_uuid`)  ping_count
		FROM `tbl_drip_drop` tbdrop 
		INNER JOIN tbl_drip td ON tbdrop.drip_uuid = td.drip_uuid
		WHERE 1
		AND tbdrop.deleted = 'N'
		GROUP BY tbdrop.`drip_uuid`
	) pinginfo
	ON `tbl_drip`.drip_uuid = pinginfo.drip_uuid
	LEFT OUTER JOIN (
		SELECT DISTINCT tbd.drip_uuid 
		FROM `tbl_batch` tb 
		LEFT OUTER JOIN `tbl_batch_drip` tbd 
		ON tb.batch_uuid = tbd.batch_uuid 
		AND tbd.deleted = 'N' 
		WHERE 1 AND tb.deleted = 'N' 
		AND (`status` = 'locked' OR `status` = 'launched')
	) locked_drips
	ON `tbl_drip`.drip_uuid = locked_drips.drip_uuid
	
	WHERE 1
	AND `tbl_drip`.deleted = 'N'
	AND drip_id = :drip_id";
	// die($sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);		
		$stmt->bindParam("drip_id", $drip_id);
		$stmt->execute();		
		$drip = $stmt->fetchObject();
		
		//get costs for each method
		$costs = json_encode(getCostsInfo());
		//die($costs);
		if (is_object($drip)) {
			$drip->content = str_replace(chr(92), '', $drip->content);
		}
		$drip->name = str_replace("\\", "", $drip->name);
		$drip->description = str_replace("\\", "", $drip->description);
        $drip->drip_ivr = str_replace("\\", "", $drip->drip_ivr);
		
		// read on stackoverflow array and JSON can't be merged. 
		// so first merge both objects as arrays then type cast the merged array 
		// back to an object so it can be read a JSON
		//$drips = (object)array_merge((array)$drip, (array)$costs);
		
		$db = null;
		echo json_encode($drip);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getDripInfo($drip_id) {
	$sql = "SELECT `tbl_drip`.`drip_id`,
    `tbl_drip`.`drip_uuid`,
    `tbl_drip`.`name`,
    `tbl_drip`.`description`,
	`tbl_drip`.`drip_ivr`,
    `tbl_drip`.`content`,
    `tbl_drip`.`max_pings`,
    `tbl_drip`.`signed_by`,
    `tbl_drip`.`weeks`,
    `tbl_drip`.`verification_costs`,
    `tbl_drip`.`deleted`,
	`drip_id` `id`, `drip_uuid` `uuid`
	FROM `tbl_drip` 
	WHERE 1
	AND `tbl_drip`.deleted = 'N'
	AND drip_id = :drip_id";
	
	//REPLACE(REPLACE(`tbl_drip`.`content`, '{', '`'), '}', '~') `content`,
	//echo $sql;
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("drip_id", $drip_id);
		$stmt->execute();
		$drip = $stmt->fetchObject();
		$db = null;
		if (is_object($drip)) {
			$drip->content = str_replace(chr(92), '', $drip->content);
		}
		return $drip;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addDrip() {
	$name = passed_var("name", "post");
	$name = addslashes($name);
	
	$description = passed_var("description", "post");
	$description = addslashes($description);
	
	$drip_ivr = passed_var("drip_ivr", "post");
	$drip_ivr = addslashes($drip_ivr);
	
	$content = passed_var("content", "post");
	$content = addslashes($content);
	
	$contact_methods = passed_var("contact_methods", "post");
	$contact_methods = addslashes($contact_methods);
	
    $max_pings = passed_var("max_pings", "post");
    $signed_by = passed_var("signed_by", "post");
    $weeks = passed_var("weeks", "post");
	$verification_costs = passed_var("verification_costs", "post");
	$drip_type = passed_var("drip_type", "post");
	$repeat_count = passed_var("repeat_count", "post");
	if ($drip_type=="repeat") {
		if ($repeat_count!="") {
			$drip_type .= "|" . $repeat_count;
		}
	}
	$overrideIVR = passed_var("override", "post");
	$drip_uuid = uniqid("DR", false);
	
	$sql = "INSERT INTO tbl_drip (`drip_uuid`, `name`, `description`, `drip_ivr`, `content`, `contact_methods`, `drip_type`, `max_pings`, `signed_by`, `weeks`, `verification_costs`, `override_IVR`) 
	                       VALUES (:drip_uuid, :name, :description, :drip_ivr, :content, :contact_methods, :drip_type, :max_pings, :signed_by, :weeks, :verification_costs, :override_IVR)";
	// die($sql . "\r\n");
	try { 
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("name", $name);
		$stmt->bindParam("content", $content);
		$stmt->bindParam("contact_methods", $contact_methods);
		$stmt->bindParam("description", $description);
		$stmt->bindParam("drip_ivr", $drip_ivr);
		$stmt->bindParam("drip_uuid", $drip_uuid);
		$stmt->bindParam("drip_type", $drip_type);
        $stmt->bindParam("max_pings", $max_pings);
        $stmt->bindParam("signed_by", $signed_by);
		$stmt->bindParam("weeks", $weeks);
		$stmt->bindParam("verification_costs", $verification_costs);
        $stmt->bindParam("override_IVR", $overrideIVR);
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		echo json_encode(array("id"=>$new_id)); 
		
		$db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updateDrip() {
	// die(print_r($_POST));
	
	$name = passed_var("name", "post");
	$name = addslashes($name);
	$description = passed_var("description", "post");
	$description = addslashes($description);
	$drip_ivr = passed_var("drip_ivr", "post");
	$drip_ivr = addslashes($drip_ivr);
	$contact_methods = passed_var("contact_methods", "post");
	$contact_methods = addslashes($contact_methods);
	$max_pings = passed_var("max_pings", "post");
    $signed_by = passed_var("signed_by", "post");
    $weeks = passed_var("weeks", "post");
	$drip_type = passed_var("drip_type", "post");
	$repeat_count = passed_var("repeat_count", "post");
	if ($drip_type=="repeat") {
		if ($repeat_count!="") {
			$drip_type .= "|" . $repeat_count;
		}
	}
	$content = passed_var("content", "post");
	// die($content);
	$arrContent = json_decode($content);
	// die(print_r($arrContent));
	$drip_id = passed_var("table_id", "post");
    // die($_POST["table_id"]);
    $overrideIVR = passed_var("override", "post");
	$verification_costs = passed_var("verification_costs", "post");
	
    
    $arrActions = array();
	//next actions
	foreach($_POST as $fieldname=>$value) {
		/*
		$strpos = strpos($fieldname, "nextaction_");
		if ($strpos === false) {
			continue;
		}
		*/
		$strpos = strpos($fieldname, "dropid_");
		if ($strpos !== false) {
			continue;
		}
		
		$value = passed_var($fieldname, "post");
		if ($value=="") {
			continue;
		}
		/*
		$arrField = explode("_", $fieldname);
		$theday = $arrField[1];
		$thedrip_id = $arrField[2];
		$thedrop_id = $arrField[3];
		
		if ($theday=="days") {
			$theday = $value; 
		}
		$arrActions[$thedrop_id]["nextaction"][] = $theday;
		*/
	}
	/*
	foreach($arrContent as $content_node){
		//get the action
		
		$action = $arrActions[$content_node->id];
		//die(print_r($action));
		$content_node->nextaction = $action["nextaction"];
	}
	*/
	$arrDropIDs = array();
	// die(print_r($arrContent));
	foreach($arrContent as $content_node){
		if($content_node->id!="") {
			$arrDropIDs[] = $content_node->id;
		}
	} 

	$content = json_encode($arrContent);
	
	// die($content);
	$content = addslashes($content);
	
	$sql = "UPDATE tbl_drip 
	SET `name` = :name, 
	`content` = :content,
	`description` = :description,
	`drip_ivr` = :drip_ivr,
	`contact_methods` = :contact_methods,
	`drip_type` = :drip_type,
    `max_pings` = :max_pings,
    `signed_by` = :signed_by,
    `weeks` = :weeks,
	`verification_costs` = :verification_costs,
    `override_IVR` = :override_IVR	
	WHERE drip_id = :drip_id";
	// die($sql ."\r\n");  
	try { 
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("name", $name);
		$stmt->bindParam("content", $content);
		$stmt->bindParam("description", $description);
		$stmt->bindParam("drip_ivr", $drip_ivr);
		$stmt->bindParam("drip_id", $drip_id);
		$stmt->bindParam("contact_methods", $contact_methods);
		$stmt->bindParam("drip_type", $drip_type);
        $stmt->bindParam("max_pings", $max_pings);
        $stmt->bindParam("signed_by", $signed_by);
        $stmt->bindParam("weeks", $weeks);
		$stmt->bindParam("verification_costs", $verification_costs);
        $stmt->bindParam("override_IVR", $overrideIVR);
		$stmt->execute();
		
		$drip = getDripInfo($drip_id);
		$drip_uuid = $drip->uuid;
		
		if (count($arrDropIDs) > 0) {
			$sql = "UPDATE tbl_drip_drop
			SET deleted = 'Y'
			WHERE drip_uuid = '" . $drip_uuid . "'
			AND drop_uuid NOT IN (SELECT drop_uuid FROM tbl_drop WHERE drop_id IN (" . implode(",", $arrDropIDs) . "))";
			$stmt = $db->prepare($sql); 
			$stmt->execute();

			foreach($arrDropIDs as $drop_id) {
				//can't double it up
				$sql = "SELECT COUNT(ddrop.drip_drop_id) drop_count 
				FROM tbl_drop `drop`
				INNER JOIN tbl_drip_drop ddrop
				ON `drop`.`drop_uuid` = `ddrop`.`drop_uuid`
				WHERE `drop_id` = $drop_id
				AND ddrop.drip_uuid = '" . $drip_uuid . "'";
				//die($sql);
				$stmt = $db->prepare($sql);
				//$stmt->bindParam("drop_id", $drop_id);
				$stmt->execute();
				$drop = $stmt->fetchObject();
				
				if ($drop->drop_count==0) {
					$drip_drop_uuid = uniqid("DR", false);
					$sql = "INSERT INTO tbl_drip_drop (`drip_drop_uuid`, `drip_uuid`, `drop_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					SELECT '" . $drip_drop_uuid . "', '" . $drip_uuid . "', `drop_uuid`, 'scheduled', '" . date("Y-m-d H:i:s") . "', '" . $_SESSION["user_plain_id"] . "', '" . $_SESSION["user_customer_id"] . "'
					FROM tbl_drop
					WHERE `drop_id` = $drop_id";
					
					//echo $sql . "\r\n";
					$stmt = $db->prepare($sql); 
					$stmt->execute();
				}
			}
		}
		
		echo json_encode(array("id"=>$drip_id)); 
		
		$db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function deleteDrip() {
	$id = passed_var("id", "post");
	//die(print_r($id));
	$sql = "UPDATE tbl_drip
			SET `deleted` = 'Y'
			WHERE `drip_id`=:id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		$db = null;
		echo json_encode(array("success"=>"drip marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}	
}
function getPings($debtor_id) {
	$sql = "SELECT
	tbda.`batch_debtor_attempt_id`, 
	tb.`batch_uuid`,
	tb.`batch_id`,
	tb.`batch_name`,
    tdrip.`drip_uuid`,
	tdrip.`drip_id`,
	tdrip.`name` drip_name,
    tdrop.`drop_uuid`,
	tdrop.`drop_id`,
	tdrop.`short_description` drop_description,
    tbda.`attempt_date`,
    tbda.`method`,
	tbda.`attempt_destination`,
    tbda.`attempt_status`,
	tbda.`message`
	FROM `tbl_batch_debtor_attempt` tbda 
	INNER JOIN `tbl_batch` tb
	ON tb.batch_uuid = tbda.batch_uuid AND tb.deleted = 'N'
	INNER JOIN `tbl_drip` tdrip
	ON tdrip.drip_uuid = tbda.drip_uuid AND tdrip.deleted = 'N'
	INNER JOIN `tbl_drop` tdrop
	ON tdrop.drop_uuid = tbda.drop_uuid AND tdrop.deleted = 'N'
	INNER JOIN `tbl_debtor` tdebtor
	ON tbda.debtor_uuid = tdebtor.debtor_uuid AND tdebtor.deleted = 'N'
	WHERE 1
	AND tdebtor.debtor_id = :debtor_id 
	AND tbda.deleted = 'N'";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("debtor_id", $debtor_id);
		$stmt->execute();
		$drip = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		// if (is_object($drip)) {
		// 	$drip->content = str_replace(chr(92), '', $drip->content);
		// }
		echo json_encode($drip);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPingReport($batch_id, $drip_id, $drop_id, $ping_number) {
    
    // die($batch_id . " " . $drip_id . " " . $drop_id);
    $sql_attempts = "SELECT tbda.`method`, COUNT(tbda.`method`) attempts 
            FROM `tbl_batch_debtor_attempt` tbda
            INNER JOIN `tbl_batch` tb
            ON tbda.`batch_uuid` = tb.`batch_uuid` 
            INNER JOIN `tbl_drip` drip
            ON tbda.`drip_uuid` = drip.`drip_uuid` 
            INNER JOIN `tbl_drop` td
            ON tbda.`drop_uuid` = td.`drop_uuid` 
            WHERE 1
            AND tb.`batch_id` = " . $batch_id . "
            AND drip.`drip_id` = " . $drip_id . "
            AND td.`drop_id` = " . $drop_id . "
            AND tbda.`ping_number` = " . $ping_number . "
            GROUP BY tbda.`method`";
    
    $sql_payments = "SELECT SUM(tp.`payment_amount`) total_payments
            FROM `tbl_payment` tp            
            INNER JOIN `tbl_batch` tb
            ON tp.`batch_uuid` = tb.`batch_uuid` 
            INNER JOIN `tbl_drip` drip
            ON tp.`drip_uuid` = drip.`drip_uuid` 
            INNER JOIN `tbl_drop` td
            ON tp.`drop_uuid` = td.`drop_uuid` 
            WHERE 1
            AND tb.`batch_id` = " . $batch_id . "
            AND drip.`drip_id` = " . $drip_id . "
            AND td.`drop_id` = " . $drop_id . "
            AND tp.`ping_number` = " . $ping_number . "
            GROUP BY tp.`batch_uuid`, tp.`drop_uuid`, tp.`drop_number`";
    
    $sql_incoming = "SELECT ti.`file_name`, COUNT(ti.`file_name`) responses 
            FROM `tbl_incoming` ti
            INNER JOIN `tbl_batch` tb
            ON ti.`batch_uuid` = tb.`batch_uuid` 
            INNER JOIN `tbl_drip` drip
            ON ti.`drip_uuid` = drip.`drip_uuid` 
            INNER JOIN `tbl_drop` td
            ON ti.`drop_uuid` = td.`drop_uuid` 
            WHERE 1
            AND tb.`batch_id` = " . $batch_id . "
            AND drip.`drip_id` = " . $drip_id . "
            AND td.`drop_id` = " . $drop_id . "
            AND ti.`ping_number` = " . $ping_number . "
            GROUP BY ti.`file_name`";
            
    $sql_planned = "SELECT tpp.`payment_plan_amount`, tpp.`installments`, tpp.`start_date`
            FROM `tbl_payment_plan` tpp
            INNER JOIN `tbl_batch` tb
            ON tpp.`batch_uuid` = tb.`batch_uuid` 
            INNER JOIN `tbl_drip` drip
            ON tpp.`drip_uuid` = drip.`drip_uuid` 
            INNER JOIN `tbl_drop` td
            ON tpp.`drop_uuid` = td.`drop_uuid` 
            WHERE 1
            AND tb.`batch_id` = " . $batch_id . "
            AND drip.`drip_id` = " . $drip_id . "
            AND td.`drop_id` = " . $drop_id . "
            AND tpp.`ping_number` = " . $ping_number . "
            GROUP BY tpp.`start_date`";
    // die($sql_attempts . "   " . $sql_payments . "   " . $sql_incoming);    
    try {
		$db = getConnection();
        
        $stmt_attempts = $db->prepare($sql_attempts);
		$stmt_attempts->execute();
        $attempts = $stmt_attempts->fetchAll(PDO::FETCH_OBJ);
          
        $stmt_payments = $db->prepare($sql_payments);
		$stmt_payments->execute();
        $payments = $stmt_payments->fetchAll(PDO::FETCH_OBJ);
 
        $stmt_incoming = $db->prepare($sql_incoming);
		$stmt_incoming->execute();
        $incoming = $stmt_incoming->fetchAll(PDO::FETCH_OBJ);
 
        $stmt_planned = $db->prepare($sql_planned);
		$stmt_planned->execute();
        $planned = $stmt_planned->fetchAll(PDO::FETCH_OBJ);
        // echo print_r($attempts);
        // echo print_r($payments);
        // echo print_r($planned); 
        // die();
              
        $db = null;
        echo json_encode(array("success"=>"true", "attempts"=>$attempts, "payments"=>$payments, "incoming"=>$incoming, "planned"=>$planned));
    } catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function confirmPings() {
    // die(print_r($_POST));
    
    $drip_id = passed_var("drip_id", "post");
    $understood = passed_var("understood", "post");
    $signed = addslashes(passed_var("signed", "post"));
    $max_pings = passed_var("max_pings", "post");
    
    $sql_customer = "SELECT customer_uuid FROM `tbl_customer` WHERE `customer_id` = '" . $_SESSION["user_customer_id"] . "'";
    $sql_drip = "SELECT drip_uuid FROM `tbl_drip` WHERE `drip_id` = '" . $drip_id . "'";
    
    try {
		$db = getConnection();
		$stmt_customer = $db->prepare($sql_customer);
		$stmt_customer->execute();
		$customer_uuid = $stmt_customer->fetchObject();
        // echo $customer_uuid->customer_uuid;
        
        $stmt_drip = $db->prepare($sql_drip);
		$stmt_drip->execute();
		$drip_uuid = $stmt_drip->fetchObject();
        // echo $drip_uuid->drip_uuid;
        
        $sql = "INSERT INTO `tbl_customer_drip`(`customer_uuid`, `drip_uuid`, `understood`, `signed`, `max_pings`) 
                VALUES ('" . $customer_uuid->customer_uuid . "', '" . $drip_uuid->drip_uuid . "', '" . $understood . "', '" . $signed . "', '" . $max_pings . "')";
      
        // die($sql);
        
        $stmt = $db->prepare($sql); 
        $stmt->execute();
        $new_id = $db->lastInsertId();
        
        $db = null;
        
        echo json_encode(array("success"=>"true", "signed_id"=>$new_id));
    } catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>