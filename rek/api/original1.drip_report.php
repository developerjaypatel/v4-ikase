<?php 
$app->post('/sequence/report/run', authorize('user'), 'runSequenceReport');
$app->get('/sequence/report/tone/:id', authorize('user'), 'getToneReportSequence');
$app->get('/sequence/report/tonegraph/:id', authorize('user'), 'getToneGraphSequence');
$app->get('/sequence/report/emphasis/:id', authorize('user'), 'getEmphasisReportSequence');
$app->get('/sequence/report/emphasisgraph/:id', authorize('user'), 'getEmphasisGraphSequence');
$app->get('/sequence/report/pingnumber/:id', authorize('user'), 'getPingNumberReportSequence');
$app->get('/sequence/report/pingnumbergraph/:id', authorize('user'), 'getPingNumberGraphSequence');
$app->get('/sequence/report/summarymethods/:id', authorize('user'), 'summaryMethodsSequence');
$app->get('/sequence/report/graphmethods/:id', authorize('user'), 'graphMethodsSequence');
//$app->get('/sequence/report/nick/:id', 'getPingNumberDataSequence');

function runSequenceReport() {
    // To Run the Report on the drip_drops. First truncate the report table. Then all launched drips into the table.
    // Next Update the report table row to the corresponding batch with th proper stats
    // set the passed vars
    // url = http://rcsclientpage.com/developer/api/batch/report/run;
    // formValues = "status=launched";
    $attribute = passed_var("attribute" , "post");
    // create the queries before opening the connection 
	$sql_truncate = "TRUNCATE tbl_drip_report"; 
	// die(print_r($_SESSION));
    $sql_insert = "INSERT INTO `tbl_drip_report` (`report_uuid`, `drip_uuid`, `customer_id`) ";
    $sql_insert .= "SELECT REPLACE(td.`drip_uuid`, 'DR', 'RE') `report_uuid`, td.`drip_uuid`, td.customer_id
                    FROM `tbl_drip` td
            WHERE td.`customer_id` = " . $_SESSION["user_customer_id"] . "
            AND td.`deleted` = 'N'
			ORDER BY td.drip_id";
            // die($sql_insert);
            
    $sql_launched = "SELECT td.`drip_uuid`
                    FROM `tbl_drip` td
            WHERE td.`customer_id` = " . $_SESSION["user_customer_id"] . "
            AND td.`deleted` = 'N'
			ORDER BY td.drip_id";
		
     try {
        $db = getConnection();
        
        $stmt_truncate = $db->prepare($sql_truncate);
        $stmt_truncate->execute();
        
        $stmt_insert = $db->prepare($sql_insert);
        $stmt_insert->execute();
		//now all the batch_drops have been summarized

        $stmt_launched = $db->prepare($sql_launched);
        $stmt_launched->execute();
		
        $launched_drips = $stmt_launched->fetchAll(PDO::FETCH_OBJ);
        // die(print_r($launched_drips));
        
		foreach($launched_drips as $launched_drip) {
            // echo print_r($launched_batch);
            $drip_uuid = $launched_drip->drip_uuid;
            
            // get the batch count per drip.
            $sub_table = "(SELECT COUNT(tbd.`batch_uuid`) `batch_count`, tbd.`drip_uuid` 
            FROM `tbl_batch_drip` tbd
            WHERE 1
            AND tbd.`drip_uuid` = '" . $drip_uuid . "'
            GROUP BY tbd.`drip_uuid`) `sub_table` ";
			
            $sql = "UPDATE tbl_drip_report tddr, " . $sub_table . "
			SET tddr.`batch_count` = `sub_table`.batch_count
			WHERE tddr.drip_uuid = `sub_table`.drip_uuid
			";
			// die($sql);  
            $stmt = $db->prepare($sql);  
            $stmt->execute();
			
            // get the contact_methods and content
			$sql = "SELECT REPLACE(td.`content`, '\\\', '') `content`, td.`contact_methods` FROM `tbl_drip` td WHERE td.`drip_uuid` = '" . $drip_uuid . "'";
            // die($sql);
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            $container = $stmt->fetchObject();
            $content = $container->content;
            $content = json_decode($content);
            // die($contact_methods);
                           
            $count_drops = 0;
            $count_unique = 0;
            $arrUnique = array();
           
            // explode the contact_methods to get the count
            $contact_methods = $container->contact_methods;
            $arrContactMethods = explode("|", $contact_methods);
            $count_contacts = count($arrContactMethods);
        
            //Identify the drops and unique drops
            $length = count($content);
            for ($i=0; $i < $length; $i++) {
                if($content[$i]->id != "") {
                    $count_drops++;
                    if($i == 0){
                        array_push($arrUnique, $content[$i]->id);
                    } else {
                        if(!in_array($content[$i]->id, $arrUnique)) {
                            array_push($arrUnique, $content[$i]->id);
                        }
                    }
                }
            }
            $count_unique = count($arrUnique);
            
            $sql = "UPDATE tbl_drip_report tddr
            SET tddr.`drops_count` = " . $count_drops . ",
            tddr.`unique_drops` = " . $count_unique . ",
            tddr.`contacts_methods_count` = " . $count_contacts . "
			WHERE tddr.`drip_uuid` = '" . $drip_uuid . "'";
            
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
            //get the attempts
            $sub_table = "(SELECT drip_uuid, COUNT(batch_debtor_attempt_id) attempt_count FROM `tbl_batch_debtor_attempt` GROUP BY drip_uuid) `sub_table` "; 
            $sql = "UPDATE tbl_drip_report tddr, " . $sub_table . "
            SET tddr.`attempts_all_batches` = `sub_table`.attempt_count
			WHERE tddr.`drip_uuid` = `sub_table`.drip_uuid";
            // die($sql);
            $stmt = $db->prepare($sql);  
            $stmt->execute(); 
            
            $sub_table = "(SELECT ti.drip_uuid, COUNT(ti.`file_name`) `attempt_count` FROM `tbl_incoming` ti 
			WHERE 1 AND (ti.`file_name` = 'authorize_capture' OR ti.`file_name` = 'authorize_capture_sms')
			GROUP BY ti.`drip_uuid`) `sub_table`";
			
            $sql = "UPDATE tbl_drip_report tddr, " . $sub_table . "
            SET tddr.`payments_all_batches` = `sub_table`.attempt_count
			WHERE tddr.`drip_uuid` = `sub_table`.drip_uuid";
            
            $stmt = $db->prepare($sql);  
            $stmt->execute();            
            
            
            // die("drop count: ". $length . "; unique drops: ". $count_unique);
        }
        $db = null;
        echo json_encode(array("success"=>true));
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
    }
}


/*
function summaryMethodsSequence($id) {
    $methods = summaryMethodsDataSequence($id);
    echo $methods;
}
function graphMethodsSequence($id) {
    $methods = summaryMethodsDataSequence($id);
    $methods = json_decode($methods);
    include "../libchart/classes/libchart.php";
    $width = "450";
	$height = "190";
    $chart = new VerticalBarChart($width, $height);
    $chart->getBound()->setUpperBound(80);
	
    $dataSet = new XYSeriesDataSet();
    
    //declare series
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
function summaryMethodsDataSequence($id) {
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
function getToneReportSequence($id) {
    $toneDatas = getToneDataSequence($id);
    echo $toneDatas;
}
function getToneGraphSequence($id) {
    $toneDatas = getToneDataSequence($id);
    $toneDatas = json_decode($toneDatas);
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
    $chart->setTitle("          Summary by Tone \n\rTone vs % of Attemts");
    $upload_dir = "../graphs/" . $_SESSION["user_customer_id"];
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir);
    }
    $chart->render($upload_dir . "/tones_graph_" . $id . ".png");
    die(json_encode(array("file"=>"tones_graph_" . $id . ".png")));
}
function getToneDataSequence($id) {
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
function getEmphasisReportSequence($id){
    $emphasisDatas = getEmphasisDataSequence($id);
    echo $emphasisDatas;
}
function getEmphasisGraphSequence($id){
    $emphasisDatas = getEmphasisDataSequence($id);
    $emphasisDatas = json_decode($emphasisDatas);
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
    $chart->setTitle("           Summary by Emphasis \n\rEmphasis vs % of Attemts");
    $upload_dir = "../graphs/" . $_SESSION["user_customer_id"];
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir);
    }
    $chart->render($upload_dir . "/emphasis_graph_" . $id . ".png");
    die(json_encode(array("file"=>"emphasis_graph_" . $id . ".png")));
}
function getEmphasisDataSequence($id){
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
*/
function getPingNumberReportSequence($id) {
   $pingNumbersReport = getPingNumberDataSequence($id);
   echo $pingNumbersReport;
}
function getPingNumberGraphSequence($id) {
    $pingNumbersData = getPingNumberDataSequence($id);
    $pingNumbersData = json_decode($pingNumbersData);
    // die(print_r($pingNumbersData));
    include "../libchart/classes/libchart.php";
    $width = "450";
	$height = "190";
    $chart = new VerticalBarChart($width, $height);
    $chart->getBound()->setUpperBound(20);
   
    $dataSet = new XYSeriesDataSet();
    $payments = new XYDataSet();
    
    foreach($pingNumbersData as $pingNumberData) {
        // die(print_r($pingNumberData));
        if($pingNumberData->ping_number != null){
            $ping_number = $pingNumberData->ping_number;
            $xpoint = $ping_number . " (" . $pingNumberData->attempt_count . ")";
            // die($xpoint);
            if($pingNumberData->attempt_count <= 0){
                $payments->addPoint(new Point($xpoint, ""));
            } else {
                if ($pingNumberData->payment_count > 0){
                    $payments_percent = number_format((($pingNumberData->payment_count / $pingNumberData->attempt_count) * 100), 0, ".", "") . "%";
                } else {
                    $payments_percent = "";
                }
                $payments->addPoint(new Point($xpoint, $payments_percent));
            }
        }
        //serie2
    }
    $dataSet->addSerie("Payments", $payments);

    $padding = new Padding(50, 50, 10, 50);
    
    $chart->setDataSet($dataSet);
    $chart->getPlot()->setGraphCaptionRatio(0.9);
    $chart->getPlot()->setGraphPadding($padding);
    $chart->getPlot()->getText()->setXAxisAngle(0);
    $chart->getConfig()->setShowPointCaption(false);
	
	// die("config:" . print_r($chart->getConfig()));
    $chart->setTitle("    Summary by Ping Number \n\rPing Number vs % of Payments");
    $upload_dir = "../graphs/" . $_SESSION["user_customer_id"];
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir);
    }
 
    $chart->render($upload_dir . "/ping_number_graph_" . $id . ".png");
    die(json_encode(array("file"=>"ping_number_graph_" . $id . ".png")));
}
function getPingNumberDataSequence($id) {
    $sql = "SELECT tdrop.`drop_id`, tdrop.`short_description`, REPLACE(td.`content`, '\\\', '') `content`, tdr.`batch_count`,tdr.`contacts_methods_count`, IFNULL(ta.`attempt_count`, 0) `attempt_count`, IFNULL(tp.`payment_count`, 0) `payment_count`, tbd.`ping_number`, tbd.`drop_methods`
            FROM `tbl_drip_drop` tdd
            INNER JOIN `tbl_drip_report` tdr
            ON tdd.`drip_uuid` = tdr.`drip_uuid`
            INNER JOIN `tbl_drip` td
            ON tdd.`drip_uuid` = td.`drip_uuid`
            INNER JOIN `tbl_drop` tdrop
            ON tdd.`drop_uuid` = tdrop.`drop_uuid`
            INNER JOIN `tbl_batch_drop` tbd
            ON tdd.`drip_uuid` = tbd.`drip_uuid` AND tdd.`drop_uuid` = tbd.`drop_uuid`
            LEFT OUTER JOIN (
                SELECT tbda.`drip_uuid`, tbda.`drop_uuid`, COUNT(tbda.`batch_debtor_attempt_id`) attempt_count 
                FROM `tbl_batch_debtor_attempt` tbda 
                GROUP BY tbda.`drip_uuid`, tbda.`drop_uuid`
            ) `ta`
            ON tdd.`drip_uuid` = ta.`drip_uuid` AND tdd.`drop_uuid` = ta.`drop_uuid`
            LEFT OUTER JOIN (
                SELECT ti.`drip_uuid`, ti.`drop_uuid`, COUNT(ti.`file_name`) `payment_count` 
                FROM `tbl_incoming` ti 
                WHERE 1 AND (ti.`file_name` = 'authorize_capture' OR ti.`file_name` = 'authorize_capture_sms')
                GROUP BY ti.`drip_uuid`, ti.`drop_uuid`
            ) `tp`
            ON tdd.`drip_uuid` = tp.`drip_uuid` AND tdd.`drop_uuid` = tp.`drop_uuid`
            WHERE td.`drip_id` = :id
            AND tdr.`customer_id` = :customer_id
            AND tdr.`deleted` = 'N'
            GROUP BY tdd.`drop_uuid`";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
        $stmt->execute();
        $pingNumbersReport = $stmt->fetchAll(PDO::FETCH_OBJ);
        // die(print_r($pingNumbersReport));
		$db = null;
        foreach($pingNumbersReport as $ping_report) {
            // die(print_r($ping_report));
            $drop_id = $ping_report->drop_id;
            $content = $ping_report->content;
            $content = json_decode($content);
            $repeat_count = 0;
            $length = count($content);
            for ($i=0; $i < $length; $i++) {
                if($content[$i]->id == $drop_id) {
                    $repeat_count++;
                }
            }
            
            
            $ping_report->repeat_count = $repeat_count;
        	unset($ping_report->content);
            // die(print_r($ping_report));
		}
		//die(print_r($pingNumbersReport));
        return json_encode($pingNumbersReport);
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
    }
}
?>