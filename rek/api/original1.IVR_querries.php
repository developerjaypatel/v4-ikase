<?php
$app->get("/IVR", "getIVR");

$app->post('/IVR/add', 'addTone');
$app->post('/IVR/delete', 'deleteIVR');
$app->post('/IVR/update', 'updateIVR');

function getIVR() {
    $sql = "SELECT tc.`cus_default_IVR` 
            FROM `tbl_customer` tc
            WHERE tc.`customer_id` = " . $_SESSION["user_customer_id"];
    // echo $sql . "\r\n";            
    try{
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $IVR = $stmt->fetchObject();
		$stmt->closeCursor(); 
        $stmt = null; 
        $db = null;
		// die(print_r($IVR))
		if (!is_object($IVR)) {
			die(json_encode(array("empty"=>true)));
		}
        
        echo json_encode($IVR);
    } catch(PDOException $e) {
        $error = array("error" => array("text"=>$e->getMessage()));
        echo json_encode($error);
    }
}
function updateIVR() {
    
    $ivr = passed_var("ivr", "post");
    $sql = "UPDATE `tbl_customer`
            SET `cus_default_IVR` = '" . addslashes($ivr) . "'
            WHERE `customer_id` = " . $_SESSION["user_customer_id"];
    // die($sql);
    try{
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $stmt = null; 
        $db = null;
        echo json_encode(array("success"=>true));
    } catch(PDOException $e) {
        $error = array("error" => array("text"=>$e->getMessage()));
        echo json_encode($error);
    }
}
?>