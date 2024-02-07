<?php
$app->get('/reports/batch', authorize('user'), 'getReportsBatches');
$app->get('/reports/sequence', authorize('user'), 'getReportsSequences');
$app->get('/reports/drop', authorize('user'), 'getReportsDrops');

$app->get('/report/batch/:id', authorize('user'), 'getReportBatch');
$app->get('/report/drip/:id', authorize('user'), 'getReportSequence');
$app->get('/report/drop/:id', authorize('user'), 'getReportDrop');

function getReportsBatches(){
    $sql = "SELECT * FROM `tbl_report_batch`
            WHERE 1
            AND `customer_id` = " . $_SESSION["user_customer_id"];;
    
    try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		// $stmt->bindParam("id", $id);
		$stmt->execute();
		$reports = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($reports);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getReportsSequences(){
    $sql = "SELECT * FROM `tbl_report_drip`
            WHERE 1
            AND `customer_id` = " . $_SESSION["user_customer_id"];;
    
    try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		// $stmt->bindParam("id", $id);
		$stmt->execute();
		$reports = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($reports);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getReportsDrops(){
    $sql = "SELECT * FROM `tbl_report_drop`
            WHERE 1
            AND `customer_id` = " . $_SESSION["user_customer_id"];;
    
    try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		// $stmt->bindParam("id", $id);
		$stmt->execute();
		$reports = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($reports);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getReportBatch($id){
    $sql = "SELECT tbd.`drop_methods`, SUM(tbdr.`verified`) `verified`, SUM(tbdr.`attempts`) `attempts`, SUM(tbdr.`unsubscribed`) `unsubscribed`, 
                        SUM(tbdr.`invalid`) `invalid`, SUM(tbdr.`payment`) `payments`, SUM(tbdr.`planned`) `planned`, tc.`cascade_cost`, tp.`payments_amount`, tpp.`payment_plan_id` `plan_count`
            FROM `tbl_batch_drop_report` tbdr 
            LEFT OUTER JOIN `tbl_batch_drop` tbd 
            ON tbdr.`batch_drop_id` = tbd.`batch_drop_id`
            INNER JOIN `tbl_batch` tb 
            ON tbdr.`batch_uuid` = tb.`batch_uuid`
            AND tb.`batch_id` = :id
            LEFT OUTER JOIN `tbl_cascade` tc
            ON LOWER(tbd.`drop_methods`) = LOWER(tc.`cascade`)
            LEFT OUTER JOIN (
                SELECT `batch_uuid`, `drop_uuid`, SUM(`payment_amount`) `payments_amount`
                FROM `tbl_payment`
                GROUP BY `batch_uuid`, `drop_uuid`
            ) tp 
            ON tbd.`batch_uuid` = tp.`batch_uuid` AND tbd.`drop_uuid` = tp.`drop_uuid`
            LEFT OUTER JOIN (
                SELECT `batch_uuid`, `drop_uuid`, COUNT(`payment_plan_id`) `payment_plan_id`
                FROM `tbl_payment_plan`
                GROUP BY `batch_uuid`, `drop_uuid`
            ) tpp
            ON tbd.`batch_uuid` = tpp.`batch_uuid` AND tbd.`drop_uuid` = tpp.`drop_uuid`
            WHERE 1 
            AND tbdr.`customer_id` = " . $_SESSION["user_customer_id"] . "
            AND tbdr.`deleted` = 'N'
            GROUP BY tbd.`drop_methods`";
            // die($sql);
    try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$report = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($report);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getReportSequence($id){
    
    $sql="SELECT tdr.`batch_count`, tdr.`drops_count`, tdr.`unique_drops`, tdr.`contacts_methods_count`, tdr.`attempts_all_batches`, tdr.`payments_all_batches`
          FROM `tbl_drip_report` tdr
          INNER JOIN `tbl_drip` td
          ON tdr.`drip_uuid` = td.`drip_uuid`
          WHERE td.`drip_id` = :id";
    // die($sql);
    try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$report = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($report);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

    /* Save incase of later use. this is the query of getBatchReport except it is with respect to drip and not batch. 
    $sql = "SELECT tbd.`drop_methods`, SUM(tbdr.`verified`) `verified`, SUM(tbdr.`attempts`) `attempts`, SUM(tbdr.`unsubscribed`) `unsubscribed`, 
                        SUM(tbdr.`invalid`) `invalid`, SUM(tbdr.`payment`) `payments`, SUM(tbdr.`planned`) `planned`, tc.`cascade_cost`, tp.`payments_amount`, tpp.`payment_plan_id` `plan_count`
            FROM `tbl_batch_drop_report` tbdr 
            LEFT OUTER JOIN `tbl_batch_drop` tbd 
            ON tbdr.`batch_drop_id` = tbd.`batch_drop_id`
            INNER JOIN `tbl_drip` td 
            ON tbdr.`drip_uuid` = td.`drip_uuid`
            AND td.`drip_id` = :id
            LEFT OUTER JOIN `tbl_cascade` tc
            ON LOWER(tbd.`drop_methods`) = LOWER(tc.`cascade`)
            LEFT OUTER JOIN (
                SELECT `drip_uuid`, `drop_uuid`, SUM(`payment_amount`) `payments_amount`
                FROM `tbl_payment`
                GROUP BY `drip_uuid`, `drop_uuid`
            ) tp 
            ON tbd.`drip_uuid` = tp.`drip_uuid` AND tbd.`drop_uuid` = tp.`drop_uuid`
            LEFT OUTER JOIN (
                SELECT `drip_uuid`, `drop_uuid`, COUNT(`payment_plan_id`) `payment_plan_id`
                FROM `tbl_payment_plan`
                GROUP BY `drip_uuid`, `drop_uuid`
            ) tpp
            ON tbd.`drip_uuid` = tpp.`drip_uuid` AND tbd.`drop_uuid` = tpp.`drop_uuid`
            WHERE 1 
            AND tbdr.`customer_id` = " . $_SESSION["user_customer_id"] . "
            AND tbdr.`deleted` = 'N'
            GROUP BY tbd.`drop_methods`";
            */
            
function getReportDrop($id){
    $sql = "SELECT tbd.`drop_methods`, SUM(tbdr.`verified`) `verified`, SUM(tbdr.`attempts`) `attempts`, SUM(tbdr.`unsubscribed`) `unsubscribed`, 
                        SUM(tbdr.`invalid`) `invalid`, SUM(tbdr.`payment`) `payments`, SUM(tbdr.`planned`) `planned`, tc.`cascade_cost`, tp.`payments_amount`, tpp.`payment_plan_id` `plan_count`
            FROM `tbl_batch_drop_report` tbdr 
            LEFT OUTER JOIN `tbl_batch_drop` tbd 
            ON tbdr.`batch_drop_id` = tbd.`batch_drop_id`
            INNER JOIN `tbl_drop` td 
            ON tbdr.`drop_uuid` = td.`drop_uuid`
            AND td.`drop_id` = :id
            LEFT OUTER JOIN `tbl_cascade` tc
            ON LOWER(tbd.`drop_methods`) = LOWER(tc.`cascade`)
            LEFT OUTER JOIN (
                SELECT `drop_uuid`, SUM(`payment_amount`) `payments_amount`
                FROM `tbl_payment`
                GROUP BY `drop_uuid`
            ) tp 
            ON tbd.`drop_uuid` = tp.`drop_uuid`
            LEFT OUTER JOIN (
                SELECT `drop_uuid`, COUNT(`payment_plan_id`) `payment_plan_id`
                FROM `tbl_payment_plan`
                GROUP BY `drop_uuid`
            ) tpp
            ON tbd.`drop_uuid` = tpp.`drop_uuid`
            WHERE 1 
            AND tbdr.`customer_id` = " . $_SESSION["user_customer_id"] . "
            AND tbdr.`deleted` = 'N'
            GROUP BY tbd.`drop_methods`";
            // die($sql);
    try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$report = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($report);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>