<?php   
$app->get('/drop/report', authorize('user'), 'getDropReport');


function getDropReport() {
    $sql = "SELECT td.`drop_id`, td.`drop_uuid`, td.`content`, td.`tone`, td.`emphasis`, td.`short_description`, IFNULL(ta.`attempt_count`, 0) `attempt_count`, IFNULL(tp.`payment_count`, 0) `payment_count`
            FROM `tbl_drop` td
            LEFT OUTER JOIN (
                SELECT tbda.`drop_uuid`,  COUNT(tbda.`batch_debtor_attempt_id`) attempt_count 
                FROM `tbl_batch_debtor_attempt` tbda 
                GROUP BY tbda.`drop_uuid`
                ) `ta`
            ON td.`drop_uuid` = ta.`drop_uuid`                
            LEFT OUTER JOIN (
                SELECT ti.`drop_uuid`, COUNT(ti.`file_name`) `payment_count` 
                FROM `tbl_incoming` ti 
                WHERE 1 AND (ti.`file_name` = 'authorize_capture' OR ti.`file_name` = 'authorize_capture_sms')
                GROUP BY ti.`drop_uuid`
                ) `tp`
            ON td.`drop_uuid` = tp.`drop_uuid`
            WHERE 1
            AND td.`deleted` = 'N'
            AND td.`customer_id` = :customer_id";
    
    try {
        $db = getConnection();
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
        $stmt->execute();
        $launched_drips = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $db = null;
        echo json_encode($launched_drips);
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
    }
}



?>