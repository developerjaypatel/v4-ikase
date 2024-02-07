<?php
$app->get('/lists', 'getLists');
$app->post('/list/add', 'addList');
$app->post('/list/delete', 'deleteList');

function getLists() {
	$sql = "SELECT `list_id`, `list_uuid`, `filename`, `dateandtime`, `valid`, `processed_date`, `deleted`,
	`list_id` `id`, `list_uuid` `uuid`
	FROM `list` 
	WHERE 1
	AND deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$lists = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($lists);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addList() {
    
    $directory = "/home1/infoaccount/public_html/uploads/unprocessed/";
    $moved_directory = "/home1/infoaccount/public_html/uploads/processed/";
    $filecount = 0;
    $files = glob($directory . "*.csv");
    // die(print_r($files));
    if ($files){
        $filecount = count($files);
    } else {
        die('{"error":{"text":no files to process}}');
    }
    // die("There were " . $filecount . " files");
    try {
        $db = getConnection();
        $ip_address = $_SERVER["REMOTE_ADDR"];
        foreach($files as $file=>$value) {
            $sheet_uuid = uniqid("SH", false);
            $arrFile = explode("/", $value);
            $item_count = count($arrFile);
            $filename = $arrFile[$item_count - 1];


            $sql = "INSERT INTO `tbl_sheets` (`sheet_uuid`, `filename`, `valid`, `ip_address`, `customer_id`)
                VALUES('" . $sheet_uuid . "', '" . $filename . "', 'Y',  '" . $ip_address . "', '" . $_SESSION["user_customer_id"] . "')";
            // die($sql);            
 
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            $new_id = $db->lastInsertId();
            
            addDebtors($new_id, $filename);
            
            rename($directory . $filename, $moved_directory . $filename);
        }		
        
		echo json_encode(array("success"=> "true")); 
		
	} catch(PDOException $e) {	
		echo '{"error":{"text1":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function getListInfo($id) {
	if ($id < 0) {
		$error = array("error"=> array("text"=>"No list id given"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT `sheet_id` `id`, `sheet_uuid` `uuid`, `filename`, `ip_address`, `valid`, `deleted`, `customer_id` 
	FROM `tbl_sheets` ts 
	WHERE 1 
    ";
    if($id > 0){
        $sql .= "AND ts.`sheet_id` = :id
        ";
    }
    
    $sql .= "AND ts.`customer_id` = :customer_id";
    // die($sql . "    " .$id);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($id > 0) {
			$stmt->bindParam("id", $id);
		}
        $stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->execute();
		$list = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

        return $list;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteList() {
	$id = passed_var("id", "post");
	$sql = "UPDATE `list`
			SET `deleted` = 'Y'
			WHERE `list_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode(array("success"=>"list marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackBatch("delete", $id);	
}
?>