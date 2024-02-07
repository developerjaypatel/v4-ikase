<?php
$app->get('/batch/:id', authorize('user'), 'getBatch');
$app->get('/batches', authorize('user'), 'getBatches');
$app->get('/batch/stats/:id', authorize('user'), 'getBatchStats');
$app->get('/batch/graphinfo/:id', authorize('user'), 'graphLaunchedInfo');
$app->get('/batch/verified/:id', authorize('user'), 'getBatchVerifieds');
$app->get('/batchdrop/:batch_id/:scheduled_date', authorize('user'), 'getBatchDropDates');
$app->get('/batches/verifieds', authorize('user'), 'getAllVerifieds');
$app->get('/batch/reset/:id', authorize('user'), 'resetBatch');
$app->get('/batch/cancel/:id', authorize('user'), 'cancelBatch');
$app->get('/batches/summary', authorize('user'), 'summaryBatches');

$app->get('/batch/verify/:id', authorize('user'), 'requestBatchVerifications');

$app->post('/batch/add', authorize('user'), 'addBatch');
$app->post('/batch/delete', authorize('user'), 'deleteBatch');
$app->post('/batch/archive', authorize('user'), 'deleteBatch');
$app->post('/batch/priority', authorize('user'), 'updatePriorityContact');
$app->post('/batch/update', authorize('user'), 'updateBatch');
$app->post('/batch_run', authorize('user'), 'runBatch');

$app->post('/batch/set/:attribute', authorize('user'), 'setBatchDebtors');
$app->post('/batch/changestatus', authorize('user'), 'requestBatchDebtorStatus');

/*
SELECT batch_uuid, 'attempts' `action`, COUNT(batch_debtor_attempt_id) batch_count
FROM `tbl_batch_debtor_attempt`
WHERE attempt_status = 'test'
AND `deleted` = 'N'
GROUP BY batch_uuid

UNION

SELECT batch_uuid,  'payment_form_opened' `action`, COUNT(file_name) batch_count
FROM `tbl_incoming`
WHERE `file_name` = 'payment_form_opened'
AND batch_uuid != ''
GROUP BY batch_uuid
ORDER by batch_uuid, `action` ASC
*/
//HAVING COUNT(file_name) > 1

function getBatch($id) {
	$sql = "SELECT tb.`batch_id`, tb.`batch_uuid`, tb.`batch_name`, tb.`batch_description`, tb.`batch_ivr`, tb.`batch_time`, tb.`filter`, tb.`run_time`, tb.`batch_run`, tb.`sent`, tb.`status`, tb.`cascade_order`,
	IFNULL(drip.drip_id, -1) drip_id, IFNULL(`drip`.`name`, '') `drip_name`, (IFNULL(sub_debtor.sub_count, 0) + IFNULL(non_debtor.non_count, 0))  debtor_count, 
	IFNULL(sub_debtor.sub_count, 0) subscribe, 
	IFNULL(non_debtor.non_count, 0) no_contact
	
	FROM `tbl_batch` tb
	LEFT OUTER JOIN `tbl_batch_drip` tbd
	ON tb.batch_uuid = tbd.batch_uuid AND tbd.deleted = 'N'
	LEFT OUTER JOIN `tbl_drip` drip
	ON tbd.drip_uuid = drip.drip_uuid
	LEFT OUTER JOIN (
		SELECT batch.batch_uuid, COUNT(DISTINCT batch_debtor.`debtor_uuid`) sub_count
		FROM `tbl_batch_debtor` batch_debtor
		INNER JOIN `tbl_batch` batch
		ON batch.batch_uuid = batch_debtor.batch_uuid AND batch_debtor.deleted = 'N'
		INNER JOIN tbl_debtor debt
		ON batch_debtor.debtor_uuid = debt.debtor_uuid
		WHERE 1 
		AND debt.`customer_id` = 1
        AND debt.`deleted` = 'N'
        AND debt.`subscribe` = 'Y'
		GROUP BY batch.batch_uuid
	) sub_debtor
	ON tb.batch_uuid = sub_debtor.batch_uuid
	
	LEFT OUTER JOIN (
		SELECT batch.batch_uuid, COUNT(DISTINCT batch_debtor.`debtor_uuid`) non_count
		FROM `tbl_batch_debtor` batch_debtor
		INNER JOIN `tbl_batch` batch
		ON batch.batch_uuid = batch_debtor.batch_uuid AND batch_debtor.deleted = 'N'
		INNER JOIN tbl_debtor debt
		ON batch_debtor.debtor_uuid = debt.debtor_uuid
		WHERE 1 
		AND debt.`customer_id` = 1
        AND debt.`deleted` = 'N'
        AND debt.`subscribe` = 'N'
		GROUP BY batch.batch_uuid
    ) non_debtor
    ON tb.batch_uuid = non_debtor.batch_uuid
	WHERE 1
	AND tb.batch_id = :id
	AND tb.deleted = 'N'";
	//die($sql);
	/*
	SELECT `batch_uuid`, `method` `metric`, count(attempt_date) `batch_count` FROM `tbl_batch_debtor_attempt` WHERE 1 GROUP BY `batch_uuid`, `method` UNION SELECT `batch_uuid`, `file_name` `metric`, COUNT(file_name) `batch_count` FROM `tbl_incoming` WHERE 1 GROUP BY `batch_uuid`, `file_name` UNION SELECT `batch_uuid`, 'machine' `metric`, COUNT(machine) `batch_count` FROM `tbl_incoming` WHERE 1 GROUP BY `batch_uuid`, `machine` ORDER BY `batch_uuid`, `metric
	*/
	//Neal added the debtor count and the outer join. It is needed for getting th contact Cost
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$batch = $stmt->fetchObject();
		$db = null;
		$batch_status = $batch->status;
        $batch_uuid = $batch->batch_uuid;
        
		$summaries = "";
        if($batch_status == "locked" || $batch_status == "launched"){
            $summaries = getLaunchedInfo($batch_uuid);
        }
		$batch->summaries = $summaries;
		
		echo json_encode($batch);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function graphLaunchedInfo($id) {
	//die(print_r($_SESSION));
	if ($id < 0) {
		return false;
	}
	$summaries = getLaunchedInfo($id, true);
	if (count($summaries) == 0) {
		return false;
	}
	
	include "../libchart/classes/libchart.php";
    $chartCellphone = new VerticalBarChart(450, 190);
    $chartSMS = new VerticalBarChart(450, 190);
    $chartEmail = new VerticalBarChart(450, 190);
    $chartMail = new VerticalBarChart(450, 190);
    $chartVoice = new VerticalBarChart(450, 190);
    $chartPhone = new VerticalBarChart(450, 190);
    
	// die(print_r());
	$dataSetCellphone = new XYSeriesDataSet();
	$dataSetEmail = new XYSeriesDataSet();
	$dataSetSMS = new XYSeriesDataSet();
    $dataSetVoice = new XYSeriesDataSet();
    $dataSetMail = new XYSeriesDataSet();
    $dataSetPhone = new XYSeriesDataSet();
    
	//declare series
	$seriesCellphone = new XYDataSet();
    $seriesEmail = new XYDataSet();
    $seriesSMS = new XYDataSet();
    $seriesVoice = new XYDataSet();
    $seriesMail = new XYDataSet();
    $seriesPhone = new XYDataSet();
    
	foreach($summaries as $summary) {
        $metric = $summary->metric;
        switch ($metric) {
            case 'authorize_capture':
                $metric = "Pay by Email";
                $seriesEmail->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'unsubscribe_email':
                $metric = "Unsub by Email";
                $seriesEmail->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'payment_form_opened':
                $metric = "Open Pay by Email";
                $seriesEmail->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'payment_plan_form_opened':
                $metric = "Open Pay Plan by Email";
                $seriesEmail->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'recurr':
                $metric = "Pay Plan by Email";
                $seriesEmail->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'zip_verification':
                $metric = "Verif Add. by Email";
                $seriesEmail->addPoint(new Point($metric, $summary->batch_count));
                break;
                
            case 'gather':
                $metric = "Picked up";
                $seriesCellphone->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'hangup':
                $metric = "Hung Up";
                $seriesCellphone->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'machine':
                $metric = "Reached Machine";
                $seriesCellphone->addPoint(new Point($metric, $summary->batch_count));
                break;

            case 'authorize_capture_sms':
                $metric = "Pay by SMS";
                $seriesSMS->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'payment_form_opened_sms':
                $metric = "Open Pay by SMS";
                $seriesSMS->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'payment_plan_form_opened_sms':
                $metric = "Open Pay Plan by SMS";
                $seriesSMS->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'recurr_sms':
                $metric = "Pay Plan by SMS";
                $seriesSMS->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'zip_sms':
                $metric = "Verif Add. by SMS";
                $seriesSMS->addPoint(new Point($metric, $summary->batch_count));
                break;
            case 'unsubscribe_sms':
                $metric = "Unsub by SMS";
                $seriesSMS->addPoint(new Point($metric, $summary->batch_count));
                break;        
        }
	}
	
    $padding = new Padding(0, 90, 90, 20);
    
	$upload_dir = "../graphs/" . $_SESSION["user_customer_id"];
    
	if (!file_exists($upload_dir)) {
		mkdir($upload_dir);
	}
    
    //Cellphone
    $dataSetCellphone->addSerie("", $seriesCellphone);
	$chartCellphone->setDataSet($dataSetCellphone);
	$chartCellphone->getPlot()->setGraphCaptionRatio(0.9);
    $chartCellphone->getConfig()->setShowPointCaption(false);
    $chartCellphone->getPlot()->getText()->setXAxisAngle(-45);
    $chartCellphone->getPlot()->setGraphPadding($padding);
	$chartCellphone->setTitle("Batch Summary of Cellphone Pings");
	$chartCellphone->render($upload_dir . "/batch_cellphone_summary_" . $id . ".png");
	
    //Email
    $dataSetEmail->addSerie("", $seriesEmail);
	$chartEmail->setDataSet($dataSetEmail);
	$chartEmail->getPlot()->setGraphCaptionRatio(0.9);
    $chartEmail->getConfig()->setShowPointCaption(false);
    $chartEmail->getPlot()->getText()->setXAxisAngle(-45);
    $chartEmail->getPlot()->setGraphPadding($padding);
	$chartEmail->setTitle("Batch Summary of Email Pings");
    $chartEmail->render($upload_dir . "/batch_email_summary_" . $id . ".png");
    
    //SMS
    $dataSetSMS->addSerie("", $seriesSMS);
	$chartSMS->setDataSet($dataSetSMS);
	$chartSMS->getPlot()->setGraphCaptionRatio(0.9);
    $chartSMS->getConfig()->setShowPointCaption(false);
    $chartSMS->getPlot()->getText()->setXAxisAngle(-45);
    $chartSMS->getPlot()->setGraphPadding($padding);
	$chartSMS->setTitle("Batch Summary of SMS Pings");
    $chartSMS->render($upload_dir . "/batch_sms_summary_" . $id . ".png");
	
	die(json_encode(array("cellphone_file"=>"batch_cellphone_summary_" . $id . ".png", "email_file"=>"batch_email_summary_" . $id . ".png", "sms_file"=>"batch_sms_summary_" . $id . ".png")));
}
function getLaunchedInfo($batch_uuid, $blnReturn = true) {
    session_write_close();
	if ($batch_uuid=="") {
		return false;
		die();
	}
	$batch_where = "1";

	if (!is_numeric($batch_uuid)) {
		$batch_where = "bat.`batch_uuid` = '" . $batch_uuid . "'";
	} else {
		$batch_where = "bat.`batch_id` = '" . $batch_uuid . "'";
	}

    /*
    $sql = "SELECT bda.`batch_uuid`, `method`  `metric`, count(attempt_date) `batch_count` 
	FROM `tbl_batch_debtor_attempt` bda";
	$sql .= " INNER JOIN `tbl_batch` bat
	ON bda.batch_uuid = bat.batch_uuid";
	$sql .= " WHERE " . $batch_where . "
	GROUP BY bda.`batch_uuid`, `method`
    
    UNION";
    */
    $sql = "SELECT ti.`batch_uuid`, `file_name` `metric`, COUNT(file_name) `batch_count` 
    FROM `tbl_incoming` ti";
	$sql .= " INNER JOIN `tbl_batch` bat
	ON ti.batch_uuid = bat.batch_uuid";
    $sql .= " WHERE " . $batch_where . "
    GROUP BY ti.`batch_uuid`, `file_name`
    
    UNION
    
    SELECT ti.`batch_uuid`, 'machine' `metric`, COUNT(machine) `batch_count` 
    FROM `tbl_incoming` ti";
	$sql .= " INNER JOIN `tbl_batch` bat
	ON ti.batch_uuid = bat.batch_uuid";
    $sql .= " 
    WHERE " . $batch_where . "
    AND ti.`machine` = 'Y'
    GROUP BY ti.`batch_uuid`, `machine`
	
	ORDER BY `batch_uuid`, `metric`";
  
    // die($sql);
    
    //#attempt_date, attempt_status
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
		
		$summaries = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
		if (!$blnReturn) {
			die(json_encode($summaries));
		} else {
        	return $summaries;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBatchInfo($id) {
	$sql = "SELECT tb.`batch_id`, tb.`batch_uuid`, tb.`batch_time`,
	IFNULL(drip.drip_id, -1) drip_id, IFNULL(`drip`.`name`, '') `drip_name`
	FROM `tbl_batch` tb
	LEFT OUTER JOIN `tbl_batch_drip` tbd
	ON tb.batch_uuid = tbd.batch_uuid
    AND tbd.deleted = 'N'
	LEFT OUTER JOIN `tbl_drip` drip
	ON tbd.drip_uuid = drip.drip_uuid
	WHERE 1
	AND tb.batch_id = " . $id . "
	AND tb.deleted = 'N'";
	// die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$batch = $stmt->fetchObject();
		$db = null;
		
		return $batch;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAllVerifieds() {
	$sql = "SELECT tv.batch_uuid, batch_id, drop_number, COUNT(verified_id) verified_count 
	FROM `tbl_verified` tv 
	INNER JOIN tbl_batch tb ON tv.batch_uuid = tb.batch_uuid AND tb.deleted = 'N'
	GROUP BY tv.batch_uuid, batch_id, drop_number";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$verifieds = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$db = null;
		
		echo json_encode($verifieds);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBatchVerifieds($id) {
	$sql = "SELECT tv.batch_uuid, drop_number, COUNT(verified_id) verified_count FROM `tbl_verified` tv INNER JOIN tbl_batch tb ON tv.batch_uuid = tb.batch_uuid WHERE tb.batch_id = 3 
	GROUP BY tv.batch_uuid, drop_number";
}
function getBatchStats($id) {
	$sql = "SELECT 'attempts' `action`, COUNT(`batch_debtor_attempt_id`) stat_count
		 FROM `tbl_batch_debtor_attempt` tbda
		 INNER JOIN `tbl_batch` tb
		 ON tb.`batch_uuid` = tbda.`batch_uuid` AND tb.`deleted` = 'N'
		 WHERE tb.`batch_id` = " . $id . "
		 AND tbda.customer_id = " . $_SESSION["user_customer_id"] . "

	 UNION

	 SELECT 'unsubscribes' `action`, COUNT(`debtor_id`) stat_count
		FROM `tbl_debtor`td
		LEFT OUTER JOIN `tbl_batch_debtor` tbd
		ON tbd.`debtor_uuid` = td.`debtor_uuid` AND tbd.`deleted` = 'N'
		LEFT OUTER JOIN `tbl_batch` tb
		ON tb.`batch_uuid` = tbd.`batch_uuid` AND tb.`deleted` = 'N'
		WHERE 1
		AND tb.`batch_id` = " . $id . " 
		AND td.deleted = 'Y' AND td.subscribe = 'N'
		AND td.customer_id = " . $_SESSION["user_customer_id"] . "

	UNION	

	SELECT 'invalids' `action`, COUNT(debtor_id) stat_count
		FROM `tbl_debtor` td
		INNER JOIN `tbl_batch_debtor` tbd
		ON td.debtor_uuid = tbd.debtor_uuid AND tbd.`deleted` = 'N'
		INNER JOIN `tbl_batch` tb
		ON tb.`batch_uuid` = tbd.`batch_uuid` AND tb.`deleted` = 'N'
		WHERE 1
		AND tb.`batch_id` = " . $id . " 
		AND (cellphone LIKE '00%' OR `phone` LIKE '00%')
		AND td.customer_id = " . $_SESSION["user_customer_id"];
//echo $sql . "\r\n";
						   
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
function summaryBatches() {
	$sql = "SELECT `tb`.`status`, COUNT(batch_id) batch_count
	FROM `tbl_batch` tb
	WHERE 1
	AND tb.deleted = 'N'
	GROUP BY `tb`.`status`
	UNION 
	SELECT 'total' `status`, COUNT(batch_id)  batch_count
	FROM `tbl_batch` tb
	WHERE 1
	AND tb.deleted = 'N'";
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
function getBatches() {
	$sql = "SELECT tb.`batch_id`, tb.`batch_uuid`, tb.`batch_time`, tb.`batch_name`, tb.`batch_description`, tb.`batch_ivr`, tb.`filter`,
	tb.`batch_id` `id`, tb.`batch_uuid` `uuid`, tb.`run_time`, tb.`batch_run`, tb.`sent`, tb.`status`, tb.`cascade_order`,
	IFNULL(drip.`drip_id`, -1) drip_id, IFNULL(drip.`name`, '') drip_name, 
    'Invalid' as invalid_debtors, IFNULL(invalid_debtors.`attempts`, 0) invalid_count,
	'Attempts' as debtor_attempts, IFNULL(debtor_attempts.`attempt_method_counts`, 0) attempt_count,
	 (IFNULL(sub_debtor.sub_count, 0) + IFNULL(non_debtor.non_count, 0))  debtor_count, 
	IFNULL(sub_debtor.sub_count, 0) subscribe, 
	IFNULL(non_debtor.non_count, 0) no_contact,
    IFNULL(sub_payments.`payment_amount`, 0) total_payments
	FROM `tbl_batch` tb
	LEFT OUTER JOIN (
		SELECT `batch_uuid`, COUNT(DISTINCT `debtor_uuid`) debtor_count
		FROM `tbl_batch_debtor` 
		WHERE `customer_id` = " . $_SESSION["user_customer_id"] . " 
		AND `deleted` = 'N' 
		GROUP BY `batch_uuid`
	) batch_debtors
	ON tb.batch_uuid = batch_debtors.`batch_uuid`
	LEFT OUTER JOIN (
		SELECT batch.batch_uuid, COUNT(DISTINCT batch_debtor.`debtor_uuid`) sub_count
		FROM `tbl_batch_debtor` batch_debtor
		INNER JOIN `tbl_batch` batch
		ON batch.batch_uuid = batch_debtor.batch_uuid AND batch_debtor.deleted = 'N'
		INNER JOIN tbl_debtor debt
		ON batch_debtor.debtor_uuid = debt.debtor_uuid
		WHERE 1 
		AND debt.`customer_id` = " . $_SESSION["user_customer_id"] . "
        AND debt.`deleted` = 'N'
        AND debt.`subscribe` = 'Y'
		GROUP BY batch.batch_uuid
	) sub_debtor
	ON tb.batch_uuid = sub_debtor.batch_uuid
	
	LEFT OUTER JOIN (
		SELECT batch.batch_uuid, COUNT(DISTINCT batch_debtor.`debtor_uuid`) non_count
		FROM `tbl_batch_debtor` batch_debtor
		INNER JOIN `tbl_batch` batch
		ON batch.batch_uuid = batch_debtor.batch_uuid AND batch_debtor.deleted = 'N'
		INNER JOIN tbl_debtor debt
		ON batch_debtor.debtor_uuid = debt.debtor_uuid
		WHERE 1 
		AND debt.`customer_id` = " . $_SESSION["user_customer_id"] . "
        AND debt.`deleted` = 'N'
        AND debt.`subscribe` = 'N'
		GROUP BY batch.batch_uuid
    ) non_debtor
    ON tb.batch_uuid = non_debtor.batch_uuid
	LEFT OUTER JOIN (
    	SELECT `batch_uuid`, 'Invalid' as `file_name`, COUNT(recipient_response) `attempts`
        FROM `tbl_incoming` ti
        WHERE 1
        AND `recipient_response` = 2
        AND `customer_id` = 1" . $_SESSION["user_customer_id"] . " 
        GROUP BY `batch_uuid`
    ) invalid_debtors
	ON tb.`batch_uuid` = invalid_debtors.`batch_uuid`
	LEFT OUTER JOIN (
        SELECT batch_uuid, GROUP_CONCAT(CONCAT(method, '|',attempts)) attempt_method_counts
		FROM(
		SELECT batch_uuid, method, COUNT(batch_debtor_attempt_id) attempts
				FROM `tbl_batch_debtor_attempt` tbda 
				WHERE 1 
				AND tbda.`customer_id` = " . $_SESSION["user_customer_id"] . " 
				AND tbda.`deleted` = 'N'
		GROUP BY batch_uuid, method) method_summary
    ) debtor_attempts
	ON tb.`batch_uuid` = debtor_attempts.`batch_uuid`
    LEFT OUTER JOIN (
        SELECT batch_uuid, SUM(tp.`payment_amount`) `payment_amount` 
        FROM `tbl_payment` tp
        WHERE 1
        GROUP BY tp.`batch_uuid`
    ) `sub_payments`
    ON tb.`batch_uuid` = sub_payments.`batch_uuid`
    LEFT OUTER JOIN `tbl_batch_drip` tbd
	ON tb.`batch_uuid` = tbd.`batch_uuid`
    AND tbd.`deleted` = 'N'
	LEFT OUTER JOIN `tbl_drip` drip
	ON tbd.`drip_uuid` = drip.`drip_uuid`
	WHERE 1
	AND tb.`deleted` = 'N'

	ORDER BY batch_id DESC";
    
//    die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$batches = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($batches);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addBatch() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$arrFilter = array();
	$table_name = "";
	$table_id = "";
	$drip_id = "";
	//default attribute
	$table_attribute = "main";
	$batch_time = "";
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
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="batch_id") {
			continue;
		}
        if ($fieldname=="zip_sort") {
			$arrFilter[$fieldname] = $value;
			continue;
		}
		if ($fieldname=="drip_id") {
			$drip_id = $value;
			continue;
		}
		if ($fieldname=="batch_time") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
			
		}
		if (strpos($fieldname, "filter_") === 0) {
			//$arrSet[] = "'" . addslashes($value) . "'";
			$fieldname = str_replace("filter_", "", $fieldname);
			$arrFilter[$fieldname] = $value;
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	$arrFields[] = "`filter`";
	$arrSet[] = "'" . addslashes(json_encode($arrFilter)) . "'";
	// die(print_r($arrSet));
	
	$table_uuid = uniqid("DR", false);
	$sql = "INSERT INTO `tbl_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	// die($sql . "\r\n");
	$db = getConnection();
	try { 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		if ($drip_id!="") {
			$drip = getDripInfo($drip_id);
			//for now
			$last_update_user = "system";	
			$last_updated_date = date("Y-m-d H:i:s");
			$sql = "INSERT INTO `tbl_batch_drip` (`batch_uuid`, `drip_uuid`, `attribute`, `last_updated_date`, `last_update_user`)
			VALUES ('" . $table_uuid . "', '" . $drip->uuid . "', 'main', '" . $last_updated_date . "', '" . $last_update_user . "')";
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		}
        $batch_filter_uuid = uniqid("DR", false);
        $filter_uuid = uniqid("NE", false);
        
        $sql_batch_filter = "INSERT INTO `tbl_batch_filters` (`batch_filter_uuid`, `batch_uuid`, `filter_uuid`, `customer_id`)
                                                      VALUES ('" . $batch_filter_uuid . "', '" . $table_uuid ."', '" . $filter_uuid . "', " . $_SESSION["user_customer_id"] . ")";
                                                      
        $stmt_batch_filter = $db->prepare($sql_batch_filter);  
		$stmt_batch_filter->execute();
        
        // this $filters below is hardcoded for demos tomorrow to ensure they dont break
        //$filters = '[{"prepaid":"Y"},{"business_phones":"Y"},{"verification":"Y"}]';
		$filters = json_encode($arrFilter);
        
        $sql_filters = "INSERT INTO `tbl_filters` (`filter_uuid`, `filters`, `customer_id`, `deleted`)
                                            VALUES('" . $filter_uuid . "', '" . $filters . "', " . $_SESSION["user_customer_id"] . ", 'N')";
        //die($sql_filters);
        $stmt_filters = $db->prepare($sql_filters);  
		$stmt_filters->execute();
        
		$stmt = null; $db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function updateBatch() {
	// die(print_r($_POST));
	$request = Slim::getInstance()->request();
	$table_id = passed_var("batch_id", "post");
	if ($table_id < 0 || !is_numeric($table_id)) {
		// die("Hello");
		addBatch();
		return;
	}
	$arrSet = array();
	$where_clause = "";
	$table_name = "batch";
	$table_attribute = "";
	$batch_time = "";
	$drip_id = "";
	$arrFilter = array();
	//die(print_r($_POST));
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
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
        if ($fieldname=="zip_sort") {
			$arrFilter[$fieldname] = $value;
			continue;
		}
		if ($fieldname=="drip_id") {
			$drip_id = $value;
			continue;
		}
		if ($fieldname=="batch_time") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
		}
		if ($fieldname=="table_id" || $fieldname=="batch_id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			if (strpos($fieldname, "filter_") === 0) {
				//$arrSet[] = "'" . addslashes($value) . "'";
				$fieldname = str_replace("filter_", "", $fieldname);
				$arrFilter[$fieldname] = $value;
				continue;
			}
			if ($fieldname!="cascade_order") {
				$value = addslashes($value);
			}
			
			$arrSet[] = "`" . $fieldname . "` = '" . $value . "'";
		}
	}
	if (count($arrFilter) > 0) {
		$arrSet[] = "`filter` = '" . json_encode($arrFilter) . "'";
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	
	$arrFilter["prepaid"] = "Y";
	$arrFilter["business_phones"] = "Y";
	$arrFilter["verification"] = "Y";
	
	$sql = "
	UPDATE `tbl_" . $table_name . "`
	SET " . implode(", ", $arrSet) . ",
	`filter` = '" . json_encode($arrFilter) . "'
	WHERE " . $where_clause;
	// die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$sql2 = "SELECT `tbl_cascade`.`cascade`, `tbl_cascade`.`cascade_cost` 
		FROM `tbl_cascade` 
		WHERE 1
		ORDER BY cascade_id ASC
		";
		
		$stmt = $db->prepare($sql2);
		$stmt->execute();
		$method_costs = $stmt->fetchAll(PDO::FETCH_OBJ);
		$arrMethods = array();
		foreach($method_costs as $method_cost) {
			$arrMethods[] = strtolower($method_cost->cascade);
		}
		if ($drip_id!="") {
			$batch = getBatchInfo($table_id);
			//die(print_r($batch));
			//if ($batch->batch_time!="0000-00-00 00:00:00") {
            $themonday = $batch->batch_time;
            $thedate = date_create($themonday);
            
            //next monday
            $dow = date("N", strtotime($themonday));
            //echo $themonday. " - " . $dow . "\r\n";
            while ($dow != 1) {
                $thedate = date_create($themonday);
                date_add($thedate, date_interval_create_from_date_string('1 days'));
                
                $dow = date("N", strtotime(date_format($thedate, 'Y-m-d')));
                $themonday = date_format($thedate, 'Y-m-d');
                //die(date_format($thedate, 'Y-m-d') . " -- " . $dow . "<br />" . $themonday);
            }
            $first_monday = date_format($thedate, 'Y-m-d');
            //die("first_monday:" . $first_monday);
            $table_uuid = $batch->batch_uuid;
            $drip = getDripInfo($drip_id);
            
            //die(print_r($drip));
            $arrContent = json_decode($drip->content);
            //
            $arrDropIDs = array();
            $arrUniqueIDs = array();
            $arrNodeNumber = array();
            $arrDrops = array();
            
            //each drop has its own cascade of contact methods
            //$arrCascadeMethods = array();
            // die(print_r($arrContent));
				
            foreach($arrContent as $content_index=>$content_node){
                $node_id = "";
                $ping_date = "0000-00-00";
                $arrDropMethods = array();
                if($content_node->id!="") {
                    $node_id = $content_node->id;
                    $ping_number = $content_index + 1;
                    $arrNodeNumber[$node_id] = 0;
                    // die($node_id);
                    if (!isset($arrDrops[$node_id])) {
                        $arrDrops[$node_id] = getDropInfo($node_id);
                        $drop = $arrDrops[$node_id];
                        $drop_content = json_decode($drop->content);
                        
                        //$arrCascadeMethods[$node_id] = $drop_content->languages->english;
                        //die(print_r($arrCascadeMethods[$node_id]));
                    }
                    
                    //echo print_r($arrUniqueIDs);
                    if (!in_array($node_id, $arrUniqueIDs)) {
                        $arrUniqueIDs[] = $node_id;
                        $arrNodeNumber[$node_id] = 1;
                        //echo "top";
                    } else {
                        $arrNodeNumber[$node_id]++;
                        //echo "bottom";
                    }
                    
                    $row = $content_node->row;
                    $col = $content_node->col;
                    if ($batch->batch_time!="0000-00-00 00:00:00") {
                        $days_add = (($row - 1) * 7);
                        $increment = ($days_add + ($col - 1));
                        
                        $themonday = date_create($first_monday);
                        if ($increment > 0) {
                            //echo $node_id . " --> " . $row . ", " . $col . " --> " . $increment . " days\r\n";
                            date_add($themonday, date_interval_create_from_date_string($increment . ' days'));
                        }
                        //$ping_date = themonday.setDate(themonday.getDate() + days_add + (col - 1));
                        $ping_date = date_format($themonday, 'Y-m-d');
                    }
                    //echo $first_monday . " + " . $increment ." days = " . $ping_date . "\r\n\r\n";
                    
                    //let's get the allowable contact methods
                    $arrOnOffs = $content_node->drop_contact_methods;
                    
                    foreach($arrOnOffs as $onoff_index=>$onoff) {
                        $method = "";
                        if($onoff== "on"){
                            $method = $arrMethods[$onoff_index];
                            $arrDropMethods[] = $method;
                        }
                        
                    }
					
					$arrDropIDs[] = array("id"=>$node_id, "ping_date"=>$ping_date, "drop_number"=>$arrNodeNumber[$node_id], "drop_methods"=>$arrDropMethods, "ping_number"=>$ping_number);
                }
            }
            
            //die(print_r($arrNodeNumber));
            
            //die(print_r($drip));
            //attach batch to drip
            /*
            $sql = "UPDATE `tbl_batch_drip` 
            SET deleted = 'Y' 
            WHERE batch_uuid = '" . $table_uuid . "'
            AND drip_uuid != '" . $drip->uuid . "'";
            */
            
            $sql = "DELETE FROM `tbl_batch_drip` 
            WHERE batch_uuid = '" . $table_uuid . "'
            AND drip_uuid = '" . $drip->uuid . "'";
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            //can't double it up
            $sql = "SELECT COUNT(bdrip.batch_drip_id) drip_count 
            FROM tbl_drip `drip`
            INNER JOIN tbl_batch_drip bdrip
            ON `drip`.`drip_uuid` = `bdrip`.`drip_uuid` AND `bdrip`.`deleted` = 'N'
            WHERE `drip_id` = $drip_id
            AND `drip`.`deleted` = 'N'
            AND bdrip.batch_uuid = '" . $table_uuid . "'";
            //die($sql);
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $batch_drip = $stmt->fetchObject();
				
            if ($batch_drip->drip_count==0) {
                //for now
                $last_update_user = "system";	
                $last_updated_date = date("Y-m-d H:i:s");
                $sql = "INSERT INTO `tbl_batch_drip` (`batch_uuid`, `drip_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
                VALUES ('" . $table_uuid . "', '" . $drip->uuid . "', 'main', '" . $last_updated_date . "', '" . $last_update_user . "', '" . $_SESSION["user_customer_id"] . "')";
                //echo $sql . "\r\n";
                $stmt = $db->prepare($sql);  
                $stmt->execute();
            }
            
            //attach batch to drop
            /*
            $sql = "UPDATE `tbl_batch_drop` 
            SET deleted = 'Y' 
            WHERE batch_uuid = '" . $table_uuid . "'
            AND drip_uuid = '" . $drip->uuid . "'
            AND customer_id = '" . $_SESSION["user_customer_id"] . "'";
            */
            $sql = "DELETE FROM `tbl_batch_drop` 
            WHERE batch_uuid = '" . $table_uuid . "'
            AND drip_uuid = '" . $drip->uuid . "'
            AND customer_id = '" . $_SESSION["user_customer_id"] . "'";
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            //die(print_r($arrDropIDs));
            //now we must schedule the batch_drop
            $arrDropUUID = array();
            foreach($arrDropIDs as $thedrop){
                $drop_id = $thedrop["id"];
                $ping_date = $thedrop["ping_date"];
                $drop_number = $thedrop["drop_number"];
                $drop_methods = $thedrop["drop_methods"];
                $ping_number = $thedrop["ping_number"];
                
                $drop_methods = implode("|", $drop_methods);
                if ($drop_id!="") {
                    if (!isset($arrDropUUID[$drop_id])){
                        //$drop = getDropInfo($drop_id);
                        $drop = $arrDrops[$drop_id];
                        $drop_uuid = $drop->uuid;
                        $arrDropUUID[$drop_id] = $drop_uuid;
                    } else {
                        $drop_uuid = $arrDropUUID[$drop_id];
                    }
                    
                    //for now
                    $last_update_user = "system";	
                    $last_updated_date = date("Y-m-d H:i:s");
                    $sql = "INSERT INTO `tbl_batch_drop` 
                    (`batch_uuid`, `drip_uuid`, `drop_uuid`, `ping_number`, `drop_number`, `drop_methods`, 
                    `attribute`, `scheduled_date`, 
                    `last_updated_date`, `last_update_user`, `customer_id`, `deleted`)
                    VALUES ('" . $table_uuid . "', '" . $drip->uuid . "', '" . $drop_uuid . "', '" . $ping_number . "', '" . $drop_number . "', '" . $drop_methods . "', '" . $table_attribute . "', '" . $ping_date . "', '" . $last_updated_date . "', '" . $last_update_user . "', '" . $_SESSION["user_customer_id"] . "', 'N')";
                    // echo $sql . "\r\n";
                    $stmt = $db->prepare($sql);  
                    $stmt->execute();
                
                }
            }
		}
		//$filters = '[{"prepaid":"Y"},{"business_phones":"Y"},{"verification":"Y"}]';
		$arrFilter["prepaid"] = "Y";
		$arrFilter["business_phones"] = "Y";
		$arrFilter["verification"] = "Y";
		//filters
		$sql = "UPDATE tbl_filters tf, tbl_batch_filters tbf
		SET `filters` = '" . json_encode($arrFilter) . "'
		WHERE tf.filter_uuid = tbf.filter_uuid
		AND tbf.batch_uuid = '" . $table_uuid . "'";
		
		$stmt = $db->prepare($sql);  
        $stmt->execute();
					
		echo json_encode(array("success"=>$table_id)); 
		$db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function deleteBatch() {
    // die(print_r($_POST));
	$id = passed_var("id", "post");
    $deleted_status = passed_var("deleted_status", "post");
	$batch = getBatchInfo($id);
	if (!is_numeric($id)) {
		die();
	}
	$sql = "UPDATE tbl_batch
			SET `deleted` = :deleted_status
			WHERE `batch_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
        $stmt->bindParam("deleted_status", $deleted_status);
		$stmt->execute();
        
		$sql = "UPDATE tbl_batch_drop
			SET `deleted` = :deleted_status
			WHERE `batch_uuid`=:uuid";
		$stmt = $db->prepare($sql);
		$stmt->bindParam("uuid", $batch->batch_uuid);
        $stmt->bindParam("deleted_status", $deleted_status);
		$stmt->execute();	
        
		$sql = "UPDATE tbl_batch_drip
			SET `deleted` = :deleted_status
			WHERE `batch_uuid`=:uuid";
		$stmt = $db->prepare($sql);
		$stmt->bindParam("uuid", $batch->batch_uuid);
        $stmt->bindParam("deleted_status", $deleted_status);
		$stmt->execute();	
        
		$sql = "UPDATE tbl_batch_debtor
			SET `deleted` = :deleted_status
			WHERE `batch_uuid`=:uuid";
		$stmt = $db->prepare($sql);
		$stmt->bindParam("uuid", $batch->batch_uuid);
        $stmt->bindParam("deleted_status", $deleted_status);
		$stmt->execute();	
        	
		$db = null;
		echo json_encode(array("success"=>"batch marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackBatch("delete", $id);	
}
function updatePriorityContact() {
	$id = passed_var("id", "post");
	if (!is_numeric($id)) {
		die();
	}
	$priority = passed_var("cascade_order", "post");
	$sql = "UPDATE tbl_batch
			SET `cascade_order` = '". addslashes($priority) ."'
			WHERE `batch_id` = :id";
	// die($sql);
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode(array("success"=>"batch priority updated"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function requestBatchDebtorStatus() {
	$batch_id = passed_var("batch_id", "post");
	$new_attribute = passed_var("new_attribute", "post");
	$old_attribute = passed_var("old_attribute", "post");
	changeBatchDebtorStatus($batch_id, $new_attribute, $old_attribute);
	
	echo json_encode(array("success"=>$batch_id, "attribute"=>$new_attribute));
}
function changeBatchDebtorStatus($batch_id, $new_attribute, $old_attribute) {

	$batch = getBatchInfo($batch_id);
	try {
		$db = getConnection();
		
		$themonday = $batch->batch_time;
		$thedate = date_create($themonday);
		
		//next monday
		$dow = date("N", strtotime($themonday));
		//echo $themonday. " - " . $dow . "\r\n";
		while ($dow != 1) {
			$thedate = date_create($themonday);
			date_add($thedate, date_interval_create_from_date_string('1 days'));
			
			$dow = date("N", strtotime(date_format($thedate, 'Y-m-d')));
			$themonday = date_format($thedate, 'Y-m-d');
		}
		$first_monday = date_format($thedate, 'Y-m-d');
		
		$sql = "UPDATE tbl_batch 
		SET `status` = '" . $new_attribute . "',
		`run_time` = '" . $first_monday . "'
		WHERE `batch_uuid` = '" . $batch->batch_uuid . "'
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "UPDATE tbl_batch_debtor 
		SET `attribute` = '" . $new_attribute . "'
		WHERE `batch_uuid` = '" . $batch->batch_uuid . "'
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'
		AND attribute = '" . $old_attribute . "'";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$db = null;
		
		//echo json_encode(array("success"=>$batch->batch_id, "attribute"=>$new_attribute));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function setBatchDebtors($attribute = "reserved") {
	session_write_close();
	
	// die(print_r($_POST));
	$filters = passed_var("originalfilters", "post");
	$batch_id = passed_var("batch_id", "post");
	//die($batch_id);
	//$batch = array();
	$batch = getBatchInfo($batch_id);
	if($batch_id < 0){
		die("batch_id not set");
	}
	
	if ($attribute=="locked") {
		$old_attribute = "reserved";
		//die("hello");
		changeBatchDebtorStatus($batch_id, $attribute, $old_attribute);
		//die();
	}
	
	if ($attribute=="launched") {
		$old_attribute = "locked";
		//die("hello");
		changeBatchDebtorStatus($batch_id, $attribute, $old_attribute);
		//die();
	}
	
	$allany = passed_var("allany", "post");
	if ($allany=="all") {
		$allany = " AND ";
	} else {
		$allany = " OR ";
	}
	
	$invoicedamount = passed_var("invoicedamount", "post");
	if($invoicedamount == "~"){
		$invoicedamount = "";
	}
	$arrInvoicedmount = array();
	if($invoicedamount != ""){
		$arrInvoicedRanges = explode("|", $invoicedamount);

		foreach($arrInvoicedRanges as $invoiced_range) {
			$arrRange = explode("~", $invoiced_range);
			$arrRange[0] = str_replace("$", "", $arrRange[0]);
			$arrRange[1] = str_replace("$", "", $arrRange[1]);
			//$arrInvoicedAmounts[] = "(`invoiced` >= " . $arrRange[0] . " AND `invoiced` < " . $arrRange[1] . ")";
			
			$arrThisFilter = array();
			if ($arrRange[0]!="") {
				$arrThisFilter[] = "`invoiced` >= " . $arrRange[0];
			}
			if ($arrRange[1]!="") {
				$arrThisFilter[] = " `invoiced` <= " . $arrRange[1];
			}
			if (count($arrThisFilter) > 0) {
				$arrInvoicedAmounts[] = implode(" AND ", $arrThisFilter);
			}
		}
	}
	
	$invoiceddate = passed_var("invoiceddate", "post");
	if($invoiceddate == "~"){
		$invoiceddate = "";
	}
	$arrInvoicedDates = array();
	if($invoiceddate != ""){
		$arrInvoicedDateRanges = explode("|", $invoiceddate);
		foreach($arrInvoicedDateRanges as $invoiced_date_range) {
            $arrDateRange = explode("~", $invoiced_date_range);
            /*
            if ($arrDateRange[0]!="" && $arrDateRange[0]!="__/__/____") {
                $arrInvoicedDates[] = "(`invoice_date` >= '" . date("Y-m-d", strtotime($arrDateRange[0])) . "' AND `invoice_date` < '" . date("Y-m-d", strtotime($arrDateRange[1])) . "')";
            }
            */
            
            if ($arrDateRange[0]!="" && $arrDateRange[1]!="") {
                $arrInvoicedDates[] = "(`invoice_date` >= '" . date("Y-m-d", strtotime($arrDateRange[0])) . "' AND `invoice_date` <= '" . date("Y-m-d", strtotime($arrDateRange[1])) . "')";
            }
            if ($arrDateRange[0]!="" && $arrDateRange[1]=="") {
                $arrInvoicedDates[] = "(`invoice_date` >= '" . date("Y-m-d", strtotime($arrDateRange[0])) . "')";
            }
            if ($arrDateRange[0]=="" && $arrDateRange[1]!="") {
                $arrInvoicedDates[] = "(`invoice_date` <= '" . date("Y-m-d", strtotime($arrDateRange[1])) . "')";
            }
		}
	}
	
	//die(print_r($arrInvoicedDates));
	$arrFilters = explode("|", $filters);
	
	$arrDebtorFilter = array();
	$arrZipFilter = array();
	foreach($arrFilters as $filter) {
		$arrFilter = explode("~", $filter);
		$fieldname = $arrFilter[0];
		$value = $arrFilter[1];
		
		if ($value=="") {
			continue;
		}
		$operator = " = ";
		$suffix = "";
		$blnFiltered = false;
		switch($fieldname) {
			case "invoiced":
				//break up the value into min/max
				$invoiced = str_replace("$", "", $value);
				$arrInvoiced = explode(" - ", $invoiced);
				$arrDebtorFilter[] = "`invoiced` >= " . $arrInvoiced[0] . " AND `invoiced` <= " . $arrInvoiced[1];
				$blnFiltered = true;
				break;
			case "from_invoice_date":
				$arrFilter[0] = "invoice_date";
				$value = date("Y-m-d", strtotime($value));
				$operator = " >= ";
				break;
			case "to_invoice_date":
				$arrFilter[0] = "invoice_date";
				$value = date("Y-m-d", strtotime($value));
				$operator = " <= ";
				break;
			case "state":
				//break up states by comma
				$arrStates = explode(",", $value);
				foreach($arrStates as $state_index=>$us_state) {
					$us_state = "'" . trim($us_state) . "'";
					$arrStates[$state_index] = $us_state;
				}
				$arrDebtorFilter[] = "`state` IN (" . implode(", ", $arrStates) . ")";
				$blnFiltered = true;
				break;
			case "zip":
				//break up zip, 3 ways
				$arrZips = explode(",", $value);
				foreach($arrZips as $us_zip) {
					$suffix = "";
					$us_zip = trim($us_zip);
					//partial
					if (strlen($us_zip) < 5) {
						$suffix = "%";
					}
					//dash
					$strpos = strpos($us_zip, "-");
					if ($strpos !== false) {
						//sub array
						$arrSubZip = explode("-", $us_zip);
						foreach($arrSubZip  as $sub_zip) {
							$suffix = "";
							if (strlen($sub_zip) < 5) {
								$suffix = "%";
							}
							$arrZipFilter[] = "`zip` LIKE '" . $sub_zip . $suffix . "'";
						}
					} else {
						$arrZipFilter[] = "`zip` LIKE '" . $us_zip . $suffix . "'";
					}
					$blnFiltered = true;
				}
				break;
		}
		if (!$blnFiltered) {
			$arrDebtorFilter[] = "`" . $arrFilter[0] . "`" . $operator .  "'" . $value . $suffix . "'";
		}
	}
	$sql = "SELECT debtor.debtor_id id
	FROM tbl_debtor debtor
	WHERE 1
	AND debtor.deleted = 'N'";
	
	//zip filter
	if (count($arrZipFilter) > 0) {
		$arrDebtorFilter[] = "(" . implode(" OR ", $arrZipFilter) . ")";
	}
	if (count($arrDebtorFilter) > 0) {
		$sql .= " AND (" . implode($allany, $arrDebtorFilter) . ")";
	}
	if (count($arrInvoicedAmounts) > 0) {
		$sql .= " AND (" . implode(" OR ", $arrInvoicedAmounts) . ")";
	}
	if (count($arrInvoicedDates) > 0) {
		$sql .= " AND (" . implode(" OR ", $arrInvoicedDates) . ")";
	}
	// die($sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$debtor_ids = array();
		foreach ($debtors as $debtor) {
            $debtor_ids[] = $debtor->id;
        }
		// die(print_r($debtor_ids));
		
		$sql = "UPDATE tbl_batch 
		SET `status` = '" . $attribute . "'
		WHERE `batch_uuid` = '" . $batch->batch_uuid . "'
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "DELETE FROM tbl_batch_debtor 
		WHERE `batch_uuid` = '" . $batch->batch_uuid . "'
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'
		AND attribute = '" . $attribute . "'";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		//echo $sql . "\r\n";
        $batch_debtor_uuid = uniqid("BD", false);
		$debtor_ids = implode(", ", $debtor_ids);
		$sql = "INSERT INTO tbl_batch_debtor (`batch_debtor_uuid`, `batch_uuid`, `debtor_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`, `deleted`)
		SELECT '" . $batch_debtor_uuid . "', '" . $batch->batch_uuid . "', debtor_uuid, '" . $attribute . "', '" . date("Y-m-d H:i:s") . "' `last_updated_date`, '" . $_SESSION['user_name'] . "' `last_update_user`, '" . $_SESSION['user_customer_id'] . "', 'N'
		FROM tbl_debtor
		WHERE debtor_id IN (" . $debtor_ids . ")
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
		// die($sql);
		$stmt = $db->prepare($sql);
		$stmt->execute();
		//echo $sql . "\r\n";
		
		$db = null;
		
		echo json_encode(array("success"=>$batch_id, "attribute"=>$attribute)); 
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function resetBatch($id){
	//die($id);
	$sql = "SELECT `tbl_batch`.`batch_uuid` FROM `tbl_batch` WHERE `tbl_batch`.`batch_id` = ". $id;
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$batch_uuid_object = $stmt->fetchObject();
		$batch_uuid = $batch_uuid_object->batch_uuid;
		
		$sql2 = "UPDATE `tbl_batch` SET `status` = 'planned' WHERE `tbl_batch`.`batch_id` = ". $id;
		$stmt2 = $db->prepare($sql2);
		$stmt2->execute();
		
		$sql3 = "DELETE  FROM `tbl_batch_debtor` WHERE `batch_uuid` LIKE '" . $batch_uuid . "'"; 
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		
		$sql4 = "DELETE  FROM `tbl_batch_drop` WHERE `batch_uuid` LIKE '" . $batch_uuid . "'"; 
		$stmt4 = $db->prepare($sql4);
		$stmt4->execute();
		
		$sql5 = "DELETE  FROM `tbl_batch_debtor_attempt` WHERE `batch_uuid` LIKE '" . $batch_uuid . "'";
		$stmt5 = $db->prepare($sql5);
		$stmt5->execute();
		$db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function cancelBatch($id){
	//die($id);
	$sql = "SELECT `tbl_batch`.`batch_uuid` FROM `tbl_batch` WHERE `tbl_batch`.`batch_id` = ". $id;
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$batch_uuid_object = $stmt->fetchObject();
		$batch_uuid = $batch_uuid_object->batch_uuid;
		
		$sql2 = "UPDATE `tbl_batch` SET `status` = 'cancel' WHERE `tbl_batch`.`batch_id` = ". $id;
		$stmt2 = $db->prepare($sql2);
		$stmt2->execute();
		
		$sql3 = "UPDATE `tbl_batch_debtor` SET `deleted` = 'A' WHERE `tbl_batch_debtor`.`batch_uuid` = '" . $batch_uuid . "'"; 
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		
		$sql4 = "UPDATE  `tbl_batch_drop` SET `deleted` = 'A' WHERE `tbl_batch_drop`.`batch_uuid` = '" . $batch_uuid . "'"; 
		$stmt4 = $db->prepare($sql4);
		$stmt4->execute();
		
		$sql5 = "UPDATE  `tbl_batch_debtor_attempt` SET `deleted` = 'A' WHERE `tbl_batch_debtor_attempt`.`batch_uuid` = '" . $batch_uuid . "'";
		$stmt5 = $db->prepare($sql5);
		$stmt5->execute();
		$db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBatchDropDates($batch_id, $scheduled_date) {
	$sql = "SELECT verified_date, voice_date, run_date 
	FROM `tbl_batch_drop` tbd
	INNER JOIN tbl_batch tb
	ON tbd.batch_uuid = tb.batch_uuid
	WHERE scheduled_date = :scheduled_date
	AND tb.batch_id = :batch_id
	AND tb.deleted = 'N'
	AND tbd.deleted = 'N'
	AND tb.customer_id = " . $_SESSION["user_customer_id"];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("batch_id", $batch_id);
		$stmt->bindParam("scheduled_date", $scheduled_date);
		$stmt->execute();
		$batch_drop = $stmt->fetchObject();
		
		echo json_encode($batch_drop);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>