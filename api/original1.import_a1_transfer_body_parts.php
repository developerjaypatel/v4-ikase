<?php
include("manage_session.php");
session_write_close();
include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_injury_bodyparts`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql_bp = "SELECT * 
	FROM `ikase`.`cse_bodyparts` 
	WHERE 1
	ORDER BY code ASC";
	
	$stmt = $db->prepare($sql_bp);
	$stmt = $db->query($sql_bp);
	$bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrBodyParts = array();
	foreach($bodyparts as $bodypart){
		$arrBodyParts[$bodypart->code] = $bodypart->bodyparts_uuid;
	}

	$sql = "SELECT injury_id `id`, injury_uuid `uuid`, body_parts FROM  `ikase_" . $data_source . "`.`cse_injury`
	WHERE TRIM(body_parts) != ''
	ORDER BY injury_id DESC";
	//die($sql);	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$injurys = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($$injurys));
	$found = count($injurys);
	
	foreach($injurys as $injury_key=>$injury){
		$parts = $injury->body_parts;
		echo $injury->id . " >> found: " . $parts . "<br>\r\n";
		
		$arrParts = explode("; ", $parts);
		//die(print_r($arrParts));
		//counter
		$int = 1;
		foreach($arrParts as $part){
			$part = preg_replace('/\D/', '', $part);
			$bodyparts_uuid = $arrBodyParts[$part];
			//echo $part . " ==> " . $bodyparts_uuid . "<br>\r\n";
			$table_uuid = uniqid("KS", false);
			
			$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_injury_bodyparts (`injury_bodyparts_uuid`, `injury_uuid`, `bodyparts_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $table_uuid . "', '" . $injury->uuid . "','" . $bodyparts_uuid . "','" . $int . "', '" . date("Y-m-d H:i:s") . "', 'system', '" . $customer_id . "')";
				
			//echo $sql . "<br><br>\r\n\r\n";
			//die();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			//increment
			$int++;
		}
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}	
?>
<script language="javascript">
parent.setFeedback("body parts transfer completed");
</script>