<?php
$app->get('/neustars', authorize('user'), 'getNeustars');
$app->get('/neustar/:id', authorize('user'), 'getNeustar');
$app->post('/neustar/add', authorize('user'), 'addNeustar');

function getNeustar($id) {
    $sql = "SElECT tf.`filter_id`, tf.`filters`
            FROM `tbl_batch` tb
            LEFT OUTER JOIN `tbl_batch_filters` tbf
            ON tbf.`batch_uuid` = tb.`batch_uuid`
            LEFT OUTER JOIN `tbl_filters` tf
            ON tf.`filter_uuid` = tbf.`filter_uuid`
            WHERE tb.`batch_id` = :id";
            
            // die($sql);
     try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);  
		$stmt->execute();
        $filters = $stmt->fetchObject();
        
        $db = null;
        
        // die(print_r($filters));
        
        echo json_encode($filters);
    } catch (PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function getNeustars() {
    $sql = "SELECT * FROM `tbl_filters` WHERE 1";
    
     try {
        $db = getConnection();
        $stmt = $db->prepare($sql);  
		$stmt->execute();
        $filters = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $db = null;
        echo json_encode($filters);
    } catch (PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function addNeustar() {
    
    // $prepaid = passed_var("prepaid", "post");
    $batch_id = passed_var("batch_id", "post");
    $filters = passed_var("filters", "post");  
    
    $filter_uuid = uniqid("NE", false);
    $sql = "INSERT INTO tbl_filters(`filter_uuid`, `filters`, `customer_id`)
                                VALUES('" . $filter_uuid . "', '" . $filters . "', " . $_SESSION["user_customer_id"] . ")";
    // die($sql);  
        
    $sql_batch_uuid = "SELECT `batch_uuid` FROM `tbl_batch` WHERE `batch_id` = :batch_id";
    
    // echo $sql_batch_uuid;
    
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);  
		$stmt->execute();
        $new_id = $db->lastInsertId();
        
        $stmt_batch_uuid = $db->prepare($sql_batch_uuid);  
        $stmt_batch_uuid->bindParam("batch_id", $batch_id);  
		$stmt_batch_uuid->execute();
        $batch = $stmt_batch_uuid->fetchObject();
        
        $sql_batch_filter = "UPDATE `tbl_batch_filters`
                             SET `filter_uuid` = '" . $filter_uuid ."' 
                             WHERE `batch_uuid` = '" . $batch->batch_uuid ."'";
       
        // die($sql_batch_filter);
        $stmt_batch_filter = $db->prepare($sql_batch_filter);  
		$stmt_batch_filter->execute();
        
        $db = null;
        echo json_encode(array("id"=>$new_id));
    } catch (PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

?>