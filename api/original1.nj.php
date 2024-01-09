<?php
include("connection.php");

$sql = "SELECT * FROM ikase_patel.zip_code
WHERE state_prefix = 'NJ'";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("user_id", $user_id);
	$stmt->execute();
	$cities = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	echo json_encode($cities);

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
?>