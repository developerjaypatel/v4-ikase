<?php
$app->post('/batch/report/run', authorize('user'), 'runBatchReport');
$app->get('/batch/report/:id', authorize('user'), 'getBatchReport');
$app->get('/batch/reports', authorize('user'), 'getBatchReports');
$app->get('/batch/report/tone/:id', authorize('user'), 'getToneReportBatch');
$app->get('/batch/report/tonegraph/:id', authorize('user'), 'getToneGraphBatch');
$app->get('/batch/report/emphasis/:id', authorize('user'), 'getEmphasisReportBatch');
$app->get('/batch/report/emphasisgraph/:id', authorize('user'), 'getEmphasisGraphBatch');
$app->get('/batch/report/pingnumber/:id', authorize('user'), 'getPingNumberReportBatch');
$app->get('/batch/report/pingnumbergraph/:id', authorize('user'), 'getPingNumberGraphBatch');
$app->get('/batch/report/summarymethods/:id', authorize('user'), 'summaryMethodsBatch');
$app->get('/batch/report/graphmethods/:id', authorize('user'), 'graphMethodsBatch');

function runBatchReport() {
    // To Run the Report on the batch_drops. First truncate the report table. Then all launched batches into the table.
    // Next Update the report table row to the corresponding batch with th proper stats
    // set the passed vars
    // url = http://rcsclientpage.com/developer/api/batch/report/run;
    // formValues = "status=launched";
    $status = passed_var("status" , "post");
    // create the queries before opening the connection 
	$sql_truncate = "TRUNCATE tbl_batch_drop_report"; 
	// die(print_r($_SESSION));
    $sql_insert = "INSERT INTO `tbl_batch_drop_report` (`report_uuid`, `batch_uuid`, `drip_uuid`, `drop_uuid`, `drop_number`, `batch_drop_id`, `customer_id`)";
    $sql_insert .= "SELECT REPLACE(tb.`batch_uuid`, 'DR', 'RE') `report_uuid`, tb.`batch_uuid`, tbd.`drip_uuid`, tbd.`drop_uuid`, tbd.`drop_number`, tbd.`batch_drop_id`, tb.customer_id
            FROM `tbl_batch_drop` tbd
            LEFT OUTER JOIN `tbl_batch` tb
            ON tbd.`batch_uuid` = tb.`batch_uuid` 
            WHERE tb.`status` = '" . $status . "'
            AND tb.`customer_id` = " . $_SESSION["user_customer_id"] . "
            AND tb.`deleted` = 'N'
			ORDER BY tb.batch_id, tbd.batch_drop_id";
            // die($sql_insert);
            
    $sql_launched = "SELECT tb.`batch_uuid`, tbd.`drip_uuid`, tbd.`drop_uuid`, tbd.`drop_number`, tbd.`batch_drop_id`, tbd.`drop_methods`, tb.`customer_id`
            FROM `tbl_batch_drop` tbd
            LEFT OUTER JOIN `tbl_batch` tb
            ON tbd.`batch_uuid` = tb.`batch_uuid` 
            WHERE tb.`status` = '" . $status . "'
            AND tb.`customer_id` = " . $_SESSION["user_customer_id"] . "
            AND tb.`deleted` = 'N'
			ORDER BY tb.batch_id, tbd.batch_drop_id";
		
     try {
        $db = getConnection();
        
        $stmt_truncate = $db->prepare($sql_truncate);
        $stmt_truncate->execute();
        
        $stmt_insert = $db->prepare($sql_insert);
        $stmt_insert->execute();
		//now all the batch_drops have been summarized

        $stmt_launched = $db->prepare($sql_launched);
        $stmt_launched->execute();
		
        $launched_batches = $stmt_launched->fetchAll(PDO::FETCH_OBJ);
        // die(print_r($launched_batches));
        
		foreach($launched_batches as $launched_batch) {
            // echo print_r($launched_batch);
            
            $batch_uuid = $launched_batch->batch_uuid;
            $drip_uuid = $launched_batch->drip_uuid;
            $drop_uuid = $launched_batch->drop_uuid;
            $drop_number = $launched_batch->drop_number;
            $batch_drop_id = $launched_batch->batch_drop_id;
            // based on each batch the number of attempts
            
            $temp = "SELECT tbda.`batch_uuid`, tbda.`drop_uuid`, tbda.`drop_number`, COUNT(tbda.`batch_debtor_attempt_id`) `attempt_count`
                     FROM `tbl_batch_debtor_attempt` tbda
                     LEFT OUTER JOIN (
                                    SELECT ti.`batch_uuid`, ti.`file_name`, COUNT(ti.`file_name`) `filen_name_count` 
                                    FROM `tbl_incoming` ti
                                    WHERE 1 GROUP BY ti.`batch_uuid`, ti.`file_name`
                                    ) `incoming`
                     ON tbda.`batch_uuid` = incoming.`batch_uuid`
                     GROUP BY tbda.`batch_uuid`, tbda.`drop_uuid`, tbda.`drop_number`";

            // attempts
			$sub_table = " (SELECT batch_uuid, drop_uuid, drop_number, COUNT(batch_debtor_attempt_id) attempt_count FROM `tbl_batch_debtor_attempt` GROUP BY batch_uuid, drop_uuid, drop_number) `sub_table`";
			
            $sql = "UPDATE tbl_batch_drop_report tbdr, " . $sub_table . "
			SET tbdr.`attempts` = `sub_table`.attempt_count
			WHERE tbdr.batch_uuid = `sub_table`.batch_uuid
			AND tbdr.drop_uuid = `sub_table`.drop_uuid
			AND tbdr.drop_number = `sub_table`.drop_number
			";
			// die($sql);  
            $stmt = $db->prepare($sql);  
            $stmt->execute();
			
            // email payments
			$sub_table = " (SELECT ti.batch_uuid, ti.drop_uuid, ti.drop_number, COUNT(ti.`file_name`) `attempt_count` FROM `tbl_incoming` ti 
			WHERE 1 AND ti.`file_name` = 'authorize_capture' 
			GROUP BY ti.`batch_uuid`) `sub_table`";
			
			$sql = "UPDATE tbl_batch_drop_report tbdr, " . $sub_table . "
			SET tbdr.`payment` = `sub_table`.attempt_count
			WHERE tbdr.batch_uuid = `sub_table`.batch_uuid
			AND tbdr.drop_uuid = `sub_table`.drop_uuid
			AND tbdr.drop_number = `sub_table`.drop_number
			";
			// die($sql);   OR 
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            // email payment plans
			$sub_table = " (SELECT ti.batch_uuid, ti.drop_uuid, ti.drop_number, COUNT(ti.`file_name`) `attempt_count` FROM `tbl_incoming` ti 
			WHERE 1 AND ti.`file_name` = 'recurr' 
			GROUP BY ti.`batch_uuid`) `sub_table`";
			
			$sql = "UPDATE tbl_batch_drop_report tbdr, " . $sub_table . "
			SET tbdr.`planned` = `sub_table`.attempt_count
			WHERE tbdr.batch_uuid = `sub_table`.batch_uuid
			AND tbdr.drop_uuid = `sub_table`.drop_uuid
			AND tbdr.drop_number = `sub_table`.drop_number
			";
			// die($sql);   OR 
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            // sms payments
			$sub_table = " (SELECT ti.batch_uuid, ti.drop_uuid, ti.drop_number, COUNT(ti.`file_name`) `attempt_count` FROM `tbl_incoming` ti 
			WHERE 1 AND ti.`file_name` = 'authorize_capture_sms' 
			GROUP BY ti.`batch_uuid`) `sub_table`";
			
			$sql = "UPDATE tbl_batch_drop_report tbdr, " . $sub_table . "
			SET tbdr.`payment` = `sub_table`.attempt_count
			WHERE tbdr.batch_uuid = `sub_table`.batch_uuid
			AND tbdr.drop_uuid = `sub_table`.drop_uuid
			AND tbdr.drop_number = `sub_table`.drop_number
			";
			// die($sql);   OR 
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            // sms payment plans
			$sub_table = " (SELECT ti.batch_uuid, ti.drop_uuid, ti.drop_number, COUNT(ti.`file_name`) `attempt_count` FROM `tbl_incoming` ti 
			WHERE 1 AND ti.`file_name` = 'recurr_sms' 
			GROUP BY ti.`batch_uuid`) `sub_table`";
			
			$sql = "UPDATE tbl_batch_drop_report tbdr, " . $sub_table . "
			SET tbdr.`planned` = `sub_table`.attempt_count
			WHERE tbdr.batch_uuid = `sub_table`.batch_uuid
			AND tbdr.drop_uuid = `sub_table`.drop_uuid
			AND tbdr.drop_number = `sub_table`.drop_number
			";
			// die($sql);   OR 
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            // email unsubscribed
            $sub_table = " (SELECT ti.batch_uuid, ti.drop_uuid, ti.drop_number, COUNT(ti.`file_name`) `attempt_count` FROM `tbl_incoming` ti 
			WHERE 1 AND ti.`file_name` = 'unsubscribe_email' 
			GROUP BY ti.`batch_uuid`) `sub_table`";
			
			$sql = "UPDATE tbl_batch_drop_report tbdr, " . $sub_table . "
			SET tbdr.`unsubscribed` = `sub_table`.attempt_count
			WHERE tbdr.batch_uuid = `sub_table`.batch_uuid
			AND tbdr.drop_uuid = `sub_table`.drop_uuid
			AND tbdr.drop_number = `sub_table`.drop_number
			";
			// die($sql);  
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            // sms unsubscribed
            $sub_table = " (SELECT ti.batch_uuid, ti.drop_uuid, ti.drop_number, COUNT(ti.`file_name`) `attempt_count` FROM `tbl_incoming` ti 
			WHERE 1 AND ti.`file_name` = 'unsubscribe_sms' 
			GROUP BY ti.`batch_uuid`) `sub_table`";
			
			$sql = "UPDATE tbl_batch_drop_report tbdr, " . $sub_table . "
			SET tbdr.`unsubscribed` = `sub_table`.attempt_count
			WHERE tbdr.batch_uuid = `sub_table`.batch_uuid
			AND tbdr.drop_uuid = `sub_table`.drop_uuid
			AND tbdr.drop_number = `sub_table`.drop_number
			";
			// die($sql);  
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            // cellphone unsubscribed
            $sub_table = " (SELECT ti.batch_uuid, ti.drop_uuid, ti.drop_number, COUNT(ti.`file_name`) `attempt_count` FROM `tbl_incoming` ti 
			WHERE 1 AND ti.`file_name` = 'gather' AND ti.`recipient_response` = 3 
			GROUP BY ti.`batch_uuid`) `sub_table`";
			
			$sql = "UPDATE tbl_batch_drop_report tbdr, " . $sub_table . "
			SET tbdr.`unsubscribed` = `sub_table`.attempt_count
			WHERE tbdr.batch_uuid = `sub_table`.batch_uuid
			AND tbdr.drop_uuid = `sub_table`.drop_uuid
			AND tbdr.drop_number = `sub_table`.drop_number
			";
			// die($sql);  
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            //Invalid
            /*
			$sub_table = " (SELECT tbdrop.`batch_uuid`, tbdrop.`drop_uuid`, tbdrop.`drop_number`, 
            SUM(IF(td.`bad_email`='Y', 1, 0) + IF(td.`bad_phone`='Y', 1, 0) + IF(td.`bad_cellphone`='Y', 1, 0) + IF(td.`bad_address`='Y', 1, 0)) attempt_count
            FROM `tbl_batch_drop` tbdrop
            LEFT OUTER JOIN `tbl_batch_debtor` tbd
            ON tbdrop.`batch_uuid` = tbd.`batch_uuid`
            LEFT OUTER JOIN `tbl_debtor` td
            ON tbd.`debtor_uuid` = td.`debtor_uuid`
            WHERE 1
            GROUP BY tbdrop.`batch_uuid`, tbdrop.`drop_uuid`, tbdrop.`drop_number`) `sub_table`";
			*/
            
			$sub_table = " (SELECT tbdrop.`batch_uuid`, tbdrop.`drop_uuid`, tbdrop.`drop_number`,
                            IF(tbdrop.drop_methods='email', SUM(IF(td.`bad_email`='Y', 1, 0)) , IF(tbdrop.drop_methods='phone', SUM(IF(td.`bad_phone`='Y', 1, 0)) , IF(tbdrop.drop_methods='cellphone', SUM(IF(td.`bad_cellphone`='Y', 1, 0)), IF(tbdrop.drop_methods='sms', SUM(IF(td.`bad_cellphone`='Y', 1, 0)), IF(tbdrop.drop_methods='mail', SUM(IF(td.`bad_address`='Y', 1, 0)), 0))))
                            ) attempt_count
                            FROM `tbl_batch_drop` tbdrop
                            INNER JOIN `tbl_batch_debtor` tbd
                            ON tbdrop.`batch_uuid` = tbd.`batch_uuid`
                            INNER JOIN `tbl_debtor` td
                            ON tbd.`debtor_uuid` = td.`debtor_uuid` AND td.`subscribe` = 'Y'
                            GROUP BY tbdrop.`batch_uuid`, tbdrop.`drop_uuid`, tbdrop.`drop_number`) `sub_table`";	
			
            $sql = "UPDATE tbl_batch_drop_report tbdr, " . $sub_table . "
			SET tbdr.`invalid` = `sub_table`.attempt_count
			WHERE tbdr.batch_uuid = `sub_table`.batch_uuid
			AND tbdr.drop_uuid = `sub_table`.drop_uuid
			AND tbdr.drop_number = `sub_table`.drop_number
			";
			// die($sql);  
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            //verified
            $sub_table = " (SELECT tv.batch_uuid, tv.drop_uuid, tv.drop_number, COUNT(tv.`eid_3630_resultcode`) `attempt_count` FROM `tbl_verified` tv
                            WHERE 1
                            AND tv.`eid_3630_resultcode` = 0
                            GROUP BY tv.`batch_uuid`) `sub_table`";
                            
            $sql = "UPDATE tbl_batch_drop_report tbdr, " . $sub_table . "
			SET tbdr.`verified` = `sub_table`.attempt_count
			WHERE tbdr.batch_uuid = `sub_table`.batch_uuid
			AND tbdr.drop_uuid = `sub_table`.drop_uuid
			AND tbdr.drop_number = `sub_table`.drop_number
			";
			// die($sql);  
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
        }
        $db = null;
        echo json_encode(array("success"=>true));
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
    }
}
function getBatchReport($id) {
    $sql = "SELECT * FROM `tbl_batch_drop_report` WHERE `report_id` = :id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $launched_batches = $stmt->fetchObject();
        $db = null;
        
        echo json_encode($launched_batches);
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
    }
}
function getBatchReports() {
    $sql = "SELECT * FROM `tbl_batch_drop_report` WHERE 1";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $launched_batches = $stmt->fetchObject();
        $db = null;
        
        echo json_encode($launched_batches);
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
    }
}
function summaryMethodsBatch($id) {
    $methods = summaryMethodsDataBatch($id);
    echo $methods;
}
function graphMethodsBatch($id) {
    $methods = summaryMethodsDataBatch($id);
    $methods = json_decode($methods);
    // die(print_r($methods));
    // $total_attempts = 0;
    // foreach ($methods as $key => $method) {
    //     $total_attempts = $total_attempts + $method->attempts;
    // }
    // die("count " . $total_attempts);
    include "../libchart/classes/libchart.php";
    $width = "450";
	$height = "190";
	// if ($id < 0) {
		$chart = new VerticalBarChart($width, $height);
        // $chart->getBound()->setLowerBound(2);
        $chart->getBound()->setUpperBound(80);
        // die(print_r($chart));
	// } else {
	// 	$chart = new VerticalBarChart();
	// }
    //$palette = new $chart->getPalette();
	
	//$palette->setBarColor(array(new Color(255,0,0),new Color(255,165,0),new Color(0,128,0),new Color(255,0,255),new Color(128,0,128),new Color(165,42,42)));
	
    $dataSet = new XYSeriesDataSet();
    
    //declare series
    // $verified = new XYDataSet();
    $received = new XYDataSet();
    $unsubscribed = new XYDataSet();
    $invalid = new XYDataSet();
    $payments = new XYDataSet();
    $planned = new XYDataSet();
    // die("hi");
    foreach($methods as $method) {
        // die(print_r($method));
        if($method->drop_methods != null){
            
            $drop_methods = $method->drop_methods;
            if($drop_methods == "cellphone"){
                $drop_methods = "cell";
            }
            $xpoint = $drop_methods . " (" . $method->attempts . ")";
            // die($xpoint);
            if($method->attempts <= 0){
                $unsubscribed->addPoint(new Point($xpoint, ""));
                $invalid->addPoint(new Point($xpoint, ""));
                $payments->addPoint(new Point($xpoint, ""));
                $planned->addPoint(new Point($xpoint, ""));
                $received->addPoint(new Point($xpoint, ""));
            } else {
                
                if ($method->unsubscribed > 0){
                    $unsubscribed_percent = number_format((($method->unsubscribed / $method->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $unsubscribed_percent = "";
                }
                
                if ($method->invalid > 0){
                    $invalid_percent = number_format((($method->invalid / $method->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $invalid_percent = "";
                }
                
                if ($method->payments > 0){
                    $payments_percent = number_format((($method->payments / $method->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $payments_percent = "";
                }
                
                if ($method->planned > 0){
                    $planned_percent = number_format((($method->planned / $method->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $planned_percent = "";
                }
               
                $received_attempts = (intval($method->attempts) - (intval($method->unsubscribed) + intval($method->invalid)));
                // die("received : " . $received_attempts);
                if($received_attempts > 0){
                    $received_percent = number_format((($received_attempts / $method->attempts) * 100), 0, ".", "") ."%";
                } else {
                    $received_percent = "";
                }
                
                // echo "unsub: " . $unsubscribed_percent . "; invalid: " . $invalid_percent . "; pay: " . $payments_percent . "; plan: " . $planned_percent . "; received: ";
                $unsubscribed->addPoint(new Point($xpoint, $unsubscribed_percent));
                $invalid->addPoint(new Point($xpoint, $invalid_percent));
                $payments->addPoint(new Point($xpoint, $payments_percent));
                $planned->addPoint(new Point($xpoint, $planned_percent));
                $received->addPoint(new Point($xpoint, $received_percent));
            }
        }
        //serie2
    }
    // die();
    // $dataSet->addSerie("Verified", $verified);
    // $dataSet->addSerie("Attempts", $attempts);
    $dataSet->addSerie("Received", $received);
    $dataSet->addSerie("Unsubscribed", $unsubscribed);
    $dataSet->addSerie("Invalid", $invalid);
    $dataSet->addSerie("Payments", $payments);
    $dataSet->addSerie("Planned", $planned);

    $padding = new Padding(50, 50, 10, 50);
    
    $chart->setDataSet($dataSet);
    $chart->getPlot()->setGraphCaptionRatio(0.9);
    $chart->getPlot()->setGraphPadding($padding);
    $chart->getPlot()->getText()->setXAxisAngle(0);
    $chart->getConfig()->setShowPointCaption(false);
	
	// die("config:" . print_r($chart->getConfig()));
    $chart->setTitle("");
    $chart->setTitle("           Summary by Method \n\rMethods vs % of Attempts");
    $chart->getPlot()->setTitleHeight(10);
    $upload_dir = "../graphs/" . $_SESSION["user_customer_id"];
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir);
    }
    $chart->render($upload_dir . "/methods_graph_" . $id . ".png");
    die(json_encode(array("file"=>"methods_graph_" . $id . ".png")));
    
}
function summaryMethodsDataBatch($id) {
    if($id > 0){
        $join = "INNER";
    } else {
        $join = "LEFT OUTER";
    }
    // die($join);
    $sql = "SELECT tbd.`drop_methods`, SUM(tbdr.`verified`) `verified`, SUM(tbdr.`attempts`) `attempts`, SUM(tbdr.`unsubscribed`) `unsubscribed`, 
                    SUM(tbdr.`invalid`) `invalid`, SUM(tbdr.`payment`) `payments`, SUM(tbdr.`planned`) `planned` 
            FROM `tbl_batch_drop_report` tbdr 
            LEFT OUTER JOIN `tbl_batch_drop` tbd 
            ON tbdr.`batch_drop_id` = tbd.`batch_drop_id`
            ";
            // the extra enter is above and below is to help when the querry is printed it is legiable.
    if($id > 0){
        $sql .= $join . " JOIN `tbl_batch` tb 
            ON tbdr.`batch_uuid` = tb.`batch_uuid`
            AND tb.`batch_id` = :id
            ";
    }
    
    $sql .= "WHERE 1 
            AND tbdr.`customer_id` = " . $_SESSION["user_customer_id"] . "
            AND tbdr.`deleted` = 'N'
            GROUP BY tbd.`drop_methods`";

    // die($sql);
    try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
        if($id > 0){
            $stmt->bindParam("id", $id);
        }
		$stmt->execute();
		$methods = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
	
		
		return json_encode($methods);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getToneReportBatch($id) {
    $toneDatas = getToneDataBatch($id);
    echo $toneDatas;
}
function getToneGraphBatch($id) {
    $toneDatas = getToneDataBatch($id);
    $toneDatas = json_decode($toneDatas);
    // die(print_r($toneDatas));
    // $total_attempts = 0;
    // foreach ($toneDatas as $toneData) {
    //     $total_attempts = $total_attempts + $toneData->attempts;
    // }
    // die("count: " .$total_attempts);
    include "../libchart/classes/libchart.php";
    $width = "450";
	$height = "190";
    $chart = new VerticalBarChart($width, $height);
    $chart->getBound()->setUpperBound(80);
   
    $dataSet = new XYSeriesDataSet();
    $received = new XYDataSet();
    $unsubscribed = new XYDataSet();
    $invalid = new XYDataSet();
    $payments = new XYDataSet();
    $planned = new XYDataSet();
    
    foreach($toneDatas as $toneData) {
        // die(print_r($toneData));
        if($toneData->tone != null){
            $tones = $toneData->tone;
            if($tones == "cellphone"){
                $tones = "cell";
            }
            $xpoint = $tones . " (" . $toneData->attempts . ")";
            // die($xpoint);
            if($toneData->attempts <= 0){
                $unsubscribed->addPoint(new Point($xpoint, ""));
                $invalid->addPoint(new Point($xpoint, ""));
                $payments->addPoint(new Point($xpoint, ""));
                $planned->addPoint(new Point($xpoint, ""));
                $received->addPoint(new Point($xpoint, ""));
            } else {
                
                if ($toneData->unsubscribed > 0){
                    $unsubscribed_percent = number_format((($toneData->unsubscribed / $toneData->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $unsubscribed_percent = "";
                }
                
                if ($toneData->invalid > 0){
                    $invalid_percent = number_format((($toneData->invalid / $toneData->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $invalid_percent = "";
                }
                
                if ($toneData->payments > 0){
                    $payments_percent = number_format((($toneData->payments / $toneData->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $payments_percent = "";
                }
                
                if ($toneData->planned > 0){
                    $planned_percent = number_format((($toneData->planned / $toneData->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $planned_percent = "";
                }
               
                $received_attempts = (intval($toneData->attempts) - (intval($toneData->unsubscribed) + intval($toneData->invalid)));
                // die("received : " . $received_attempts);
                if($received_attempts > 0){
                    $received_percent = number_format((($received_attempts / $toneData->attempts) * 100), 0, ".", "") ."%";
                } else {
                    $received_percent = "";
                }
                
                // echo "unsub: " . $unsubscribed_percent . "; invalid: " . $invalid_percent . "; pay: " . $payments_percent . "; plan: " . $planned_percent . "; received: ";
                $unsubscribed->addPoint(new Point($xpoint, $unsubscribed_percent));
                $invalid->addPoint(new Point($xpoint, $invalid_percent));
                $payments->addPoint(new Point($xpoint, $payments_percent));
                $planned->addPoint(new Point($xpoint, $planned_percent));
                $received->addPoint(new Point($xpoint, $received_percent));
            }
        }
        //serie2
    }
    // die();
    // $dataSet->addSerie("Verified", $verified);
    // $dataSet->addSerie("Attempts", $attempts);
    $dataSet->addSerie("Received", $received);
    $dataSet->addSerie("Unsubscribed", $unsubscribed);
    $dataSet->addSerie("Invalid", $invalid);
    $dataSet->addSerie("Payments", $payments);
    $dataSet->addSerie("Planned", $planned);

    $padding = new Padding(50, 50, 10, 50);
    
    $chart->setDataSet($dataSet);
    $chart->getPlot()->setGraphCaptionRatio(0.9);
    $chart->getPlot()->setGraphPadding($padding);
    $chart->getPlot()->getText()->setXAxisAngle(0);
    $chart->getConfig()->setShowPointCaption(false);
	$chart->getAxis()->Axis(0, 0);
	
    // die($chart->getPlot()->getTextColor());
    $chart->setTitle("          Summary by Tone \n\rTone vs % of Attempts");
    $upload_dir = "../graphs/" . $_SESSION["user_customer_id"];
    // die($upload_dir);
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir);
    }
    $chart->render($upload_dir . "/tones_graph_" . $id . ".png");
    die(json_encode(array("file"=>"tones_graph_" . $id . ".png")));
}
function getToneDataBatch($id) {
    $sql = "SELECT tt.`tone`, SUM(tbdr.`verified`) verified, SUM(tbdr.`attempts`) attempts, SUM(tbdr.`invalid`) invalid, SUM(tbdr.`unsubscribed`) unsubscribed, SUM(tbdr.`payment`) payments, SUM(tbdr.`planned`) planned
            FROM `tbl_tone` tt
            INNER JOIN `tbl_drop` td
            ON tt.`tone` = td.`tone`
            INNER JOIN `tbl_batch_drop` tbd
            ON tbd.`drop_uuid` = td.`drop_uuid`
            INNER JOIN `tbl_batch` tb
            ON tb.`batch_uuid` = tbd.`batch_uuid`
            INNER JOIN `tbl_batch_drop_report` tbdr
            ON tbdr.`batch_uuid` = tbd.`batch_uuid` AND tbdr.`drop_uuid` = tbd.`drop_uuid`
            WHERE 1 ";
    if($id > 0){ 
        $sql.= " AND tb.`batch_id` = :id ";
    }
    $sql .= " GROUP BY tt.`tone`";
    try {
       $db = getConnection();
       $stmt = $db->prepare($sql);
       if($id > 0){ 
           $stmt->bindParam("id", $id);
       }
       $stmt->execute();
       $toneDatas = $stmt->fetchAll(PDO::FETCH_OBJ);
       $db = null;
        
       return json_encode($toneDatas);
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
    }
}
function getEmphasisReportBatch($id){
    $emphasisDatas = getEmphasisDataBatch($id);
    echo $emphasisDatas;
}
function getEmphasisGraphBatch($id){
    $emphasisDatas = getEmphasisDataBatch($id);
    $emphasisDatas = json_decode($emphasisDatas);
    // $total_attempts = 0;
    // foreach ($emphasisDatas as $emphasisData) {
    //     $total_attempts = $total_attempts + $emphasisData->attempts;
    // }
    // die("count: " .$total_attempts);
    include "../libchart/classes/libchart.php";
    $width = "450";
	$height = "190";
    $chart = new VerticalBarChart($width, $height);
    $chart->getBound()->setUpperBound(80);
   
    $dataSet = new XYSeriesDataSet();
    $received = new XYDataSet();
    $unsubscribed = new XYDataSet();
    $invalid = new XYDataSet();
    $payments = new XYDataSet();
    $planned = new XYDataSet();
    
    foreach($emphasisDatas as $emphasisData) {
        // die(print_r($emphasisData));
        if($emphasisData->emphasis != null){
            $tones = $emphasisData->emphasis;
            if($tones == "cellphone"){
                $tones = "cell";
            }
            $xpoint = $tones . " (" . $emphasisData->attempts . ")";
            // die($xpoint);
            if($emphasisData->attempts <= 0){
                $unsubscribed->addPoint(new Point($xpoint, ""));
                $invalid->addPoint(new Point($xpoint, ""));
                $payments->addPoint(new Point($xpoint, ""));
                $planned->addPoint(new Point($xpoint, ""));
                $received->addPoint(new Point($xpoint, ""));
            } else {
                
                if ($emphasisData->unsubscribed > 0){
                    $unsubscribed_percent = number_format((($emphasisData->unsubscribed / $emphasisData->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $unsubscribed_percent = "";
                }
                
                if ($emphasisData->invalid > 0){
                    $invalid_percent = number_format((($emphasisData->invalid / $emphasisData->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $invalid_percent = "";
                }
                
                if ($emphasisData->payments > 0){
                    $payments_percent = number_format((($emphasisData->payments / $emphasisData->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $payments_percent = "";
                }
                
                if ($emphasisData->planned > 0){
                    $planned_percent = number_format((($emphasisData->planned / $emphasisData->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $planned_percent = "";
                }
               
                $received_attempts = (intval($emphasisData->attempts) - (intval($emphasisData->unsubscribed) + intval($emphasisData->invalid)));
                // die("received : " . $received_attempts);
                if($received_attempts > 0){
                    $received_percent = number_format((($received_attempts / $emphasisData->attempts) * 100), 0, ".", "") ."%";
                } else {
                    $received_percent = "";
                }
                
                // echo "unsub: " . $unsubscribed_percent . "; invalid: " . $invalid_percent . "; pay: " . $payments_percent . "; plan: " . $planned_percent . "; received: ";
                $unsubscribed->addPoint(new Point($xpoint, $unsubscribed_percent));
                $invalid->addPoint(new Point($xpoint, $invalid_percent));
                $payments->addPoint(new Point($xpoint, $payments_percent));
                $planned->addPoint(new Point($xpoint, $planned_percent));
                $received->addPoint(new Point($xpoint, $received_percent));
            }
        }
        //serie2
    }
    // die();
    // $dataSet->addSerie("Verified", $verified);
    // $dataSet->addSerie("Attempts", $attempts);
    $dataSet->addSerie("Received", $received);
    $dataSet->addSerie("Unsubscribed", $unsubscribed);
    $dataSet->addSerie("Invalid", $invalid);
    $dataSet->addSerie("Payments", $payments);
    $dataSet->addSerie("Planned", $planned);

    $padding = new Padding(50, 50, 10, 50);
    
    $chart->setDataSet($dataSet);
    $chart->getPlot()->setGraphCaptionRatio(0.9);
    $chart->getPlot()->setGraphPadding($padding);
    $chart->getPlot()->getText()->setXAxisAngle(0);
    $chart->getConfig()->setShowPointCaption(false);
	
	// die("config:" . print_r($chart->getConfig()));
    $chart->setTitle("           Summary by Emphasis \n\rEmphasis vs % of Attempts");
    $upload_dir = "../graphs/" . $_SESSION["user_customer_id"];
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir);
    }
    $chart->render($upload_dir . "/emphasis_graph_" . $id . ".png");
    die(json_encode(array("file"=>"emphasis_graph_" . $id . ".png")));
}
function getEmphasisDataBatch($id){
    $sql = "SELECT te.`emphasis`, SUM(tbdr.`verified`) verified, SUM(tbdr.`attempts`) attempts, SUM(tbdr.`invalid`) invalid, SUM(tbdr.`unsubscribed`) unsubscribed, SUM(tbdr.`payment`) payments, SUM(tbdr.`planned`) planned
            FROM `tbl_emphasis` te
            INNER JOIN `tbl_drop` td
            ON te.`emphasis` = td.`emphasis`
            INNER JOIN `tbl_batch_drop` tbd
            ON tbd.`drop_uuid` = td.`drop_uuid`
            INNER JOIN `tbl_batch` tb
            ON tb.`batch_uuid` = tbd.`batch_uuid`
            INNER JOIN `tbl_batch_drop_report` tbdr
            ON tbdr.`batch_uuid` = tbd.`batch_uuid` AND tbdr.`drop_uuid` = tbd.`drop_uuid`
            WHERE 1 ";
    if($id > 0){ 
        $sql.= " AND tb.`batch_id` = :id ";
    }
    $sql.= " GROUP BY te.`emphasis`";
    // die($sql);
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        if($id > 0){ 
            $stmt->bindParam("id", $id);
        }
        $stmt->execute();
        $emphasisDatas = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($emphasisDatas);
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
    }
}
function getPingNumberReportBatch($id) {
   $pingNumbersReport = getPingNumberDataBatch($id);
   echo $pingNumbersReport;
}
function getPingNumberGraphBatch($id) {
    $pingNumbersData = getPingNumberDataBatch($id);
    $pingNumbersData = json_decode($pingNumbersData);
    
    include "../libchart/classes/libchart.php";
    $width = "450";
	$height = "190";
    $chart = new VerticalBarChart($width, $height);
    $chart->getBound()->setUpperBound(80);
   
    $dataSet = new XYSeriesDataSet();
    $received = new XYDataSet();
    $unsubscribed = new XYDataSet();
    // $invalid = new XYDataSet();
    $payments = new XYDataSet();
    // $planned = new XYDataSet();
    
    foreach($pingNumbersData as $pingNumberData) {
        // die(print_r($pingNumberData));
        if($pingNumberData->ping_number != null){
            $ping_number = $pingNumberData->ping_number;
            $xpoint = $ping_number; // . " (" . $pingNumberData->attempts . ")";
            // die($xpoint);
            if($pingNumberData->attempts <= 0){
                $unsubscribed->addPoint(new Point($xpoint, ""));
                // $invalid->addPoint(new Point($xpoint, ""));
                $payments->addPoint(new Point($xpoint, ""));
                // $planned->addPoint(new Point($xpoint, ""));
                $received->addPoint(new Point($xpoint, ""));
            } else {
                
                if ($pingNumberData->unsubscribed > 0){
                    $unsubscribed_percent = number_format((($pingNumberData->unsubscribed / $pingNumberData->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $unsubscribed_percent = "";
                }
                
                // if ($pingNumberData->invalid > 0){
                //     $invalid_percent = number_format((($pingNumberData->invalid / $pingNumberData->attempts) * 100), 0, ".", "") . "%";
                // } else {
                //     $invalid_percent = "";
                // }
                
                if ($pingNumberData->payments > 0){
                    $payments_percent = number_format((($pingNumberData->payments / $pingNumberData->attempts) * 100), 0, ".", "") . "%";
                } else {
                    $payments_percent = "";
                }
                
                // if ($pingNumberData->planned > 0){
                //     $planned_percent = number_format((($pingNumberData->planned / $pingNumberData->attempts) * 100), 0, ".", "") . "%";
                // } else {
                //     $planned_percent = "";
                // }
               
                $received_attempts = intval($pingNumberData->attempts) - intval($pingNumberData->unsubscribed);
                // die("received : " . $received_attempts);
                if($received_attempts > 0){
                    $received_percent = number_format((($received_attempts / $pingNumberData->attempts) * 100), 0, ".", "") ."%";
                } else {
                    $received_percent = "";
                }
                
                // echo "unsub: " . $unsubscribed_percent . "; invalid: " . $invalid_percent . "; pay: " . $payments_percent . "; plan: " . $planned_percent . "; received: ";
                $unsubscribed->addPoint(new Point($xpoint, $unsubscribed_percent));
                // $invalid->addPoint(new Point($xpoint, $invalid_percent));
                $payments->addPoint(new Point($xpoint, $payments_percent));
                // $planned->addPoint(new Point($xpoint, $planned_percent));
                $received->addPoint(new Point($xpoint, $received_percent));
            }
        }
        //serie2
    }
    $dataSet->addSerie("Received", $received);
    $dataSet->addSerie("Unsubscribed", $unsubscribed);
    // $dataSet->addSerie("Invalid", $invalid);
    $dataSet->addSerie("Payments", $payments);
    // $dataSet->addSerie("Planned", $planned);

    $padding = new Padding(50, 50, 10, 50);
    
    $chart->setDataSet($dataSet);
    $chart->getPlot()->setGraphCaptionRatio(0.9);
    $chart->getPlot()->setGraphPadding($padding);
    $chart->getPlot()->getText()->setXAxisAngle(0);
    $chart->getConfig()->setShowPointCaption(false);
	
	// die("config:" . print_r($chart->getConfig()));
    $chart->setTitle("    Summary by Ping Number \n\rPing Number vs % of Attempts");
    $upload_dir = "../graphs/" . $_SESSION["user_customer_id"];
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir);
    }
 
    $chart->render($upload_dir . "/ping_number_graph_" . $id . ".png");
    die(json_encode(array("file"=>"ping_number_graph_" . $id . ".png")));
}
function getPingNumberDataBatch($id) {
    $sql = "SELECT tbd.`ping_number`, SUM(tbdr.`verified`) verified, SUM(tbdr.`attempts`) attempts, SUM(tbdr.`invalid`) invalid, SUM(tbdr.`unsubscribed`) unsubscribed, SUM(tbdr.`payment`) payments, SUM(tbdr.`planned`) planned 
            FROM `tbl_batch_drop` tbd
            INNER JOIN `tbl_batch_drop_report` tbdr
            ON tbd.`batch_uuid` = tbdr.`batch_uuid` AND tbd.`drop_uuid` = tbdr.`drop_uuid`";
    
    if($id > 0){
        $sql .= " INNER JOIN `tbl_batch` tb
                  ON tbd.`batch_uuid` = tb.`batch_uuid`";
    }
    $sql .= " WHERE 1 ";
   
    if($id > 0){
        $sql .= " AND tb.`batch_id` = :id";
    }
    $sql .= " GROUP BY tbd.`ping_number`";
  
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        if($id > 0){ 
            $stmt->bindParam("id", $id);
        }
        $stmt->execute();
        $pingNumbersReport = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($pingNumbersReport);
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
    }
}
?>