<?php
include("manage_session.php");
set_time_limit(3000);

session_write_close();

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

function getNickConnection() {
	//$dbhost="54.149.211.191";
	$dbhost="ikase.org";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
function extractMultiple($data, $search, $link) {
	$arrReturn = array();
	//die(str_replace("><", ">\r\n<", $data));
	$function_data = $data;
	
	//die(str_replace("><", ">\r\n<", $function_data));
	
	$pos1 = strpos($function_data, "<" . $search);
	$intCounter = 0;
	while($pos1!==false) {
		$value = extractEntity($function_data, $search, $link);
		if (trim($value)=="") {
			$pos1 = false;
			continue;
		}
		$arrReturn[] = trim($value);
		
		$pos2 = strpos($function_data, $value);
		$function_data = substr($function_data, $pos2 + strlen($value));
		//die(str_replace("><", ">\r\n<", $function_data));
		//echo "<" . $search . ":" . $pos2 . "\r\n";
		$pos1 = strpos($function_data, "<" . $search);
		
		$intCounter++;
		if ($intCounter > 100) {
			//echo "out" . "\r\n";
			//die(str_replace("><", ">\r\n<", $function_data));
			$pos1 = false;
			continue;
		}
	}
	return $arrReturn;
}
function extractEntity($data, $search, $link) {
	$pos1 = strpos($data, "<" . $search);
	$entity = "";
	if ($pos1!==false) {
		$pos2 = strpos($data, "<", $pos1 + 1);
		$entity = substr($data, $pos1, ($pos2 - $pos1));	
		$entity = str_replace('<' . $search . ' link="' . $link . '">', '', $entity);
	}
	//final check
	if ($entity == "<" . $search . " />") {
		$entity = "";
	}
	echo $search . ": " . $entity . "\<br />\r\n";
	return $entity;
}
function extractDate($data, $search) {
	$start = '<' . $search . ' comp="">';
	$date = "0000-00-00 00:00:00";
	$pos1 = strpos($data, $start);
	if ($pos1!==false) {
		$pos2 = strpos($data, "</" . $search, $pos1);
		
		$date = substr($data, $pos1, ($pos2 - $pos1));
		$date = str_replace($start, "", $date);
	}
	echo $search . ": " . $date . "<br />\r\n";
	return $date;
}
function extractTag($data, $search) {
	$html = "";
	
	$pos1 = strpos($data, "<" . $search . ">");
	if ($pos1!==false) {
		$pos2 = strpos($data, "</" . $search, $pos1);
		$html = substr($data, $pos1, ($pos2 - $pos1));
		$html = str_replace("<" . $search . ">", "", $html);
	}
	echo $search . ": " . $html . "]<br />\r\n";
	return $html;
}
//WHERE cli.fileno = 1061
//die($sql);
try {
	$db = getNickConnection();
	
	include("customer_lookup.php");
	
	$sql = "SELECT * 
	FROM `gaylord`.`dbo_workproduct`
	WHERE `LOCK` IS NULL
	LIMIT 0, 1";

	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	//$products = $stmt->fetchAll(PDO::FETCH_OBJ);
	$product = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	if (!is_object($product)) {
		die("all done");
	}
	$id = $product->ID;
	$data = $product->DATA;
	
	//die(str_replace("><", ">\r\n<", $data));
	
	$wpid = extractTag($data, 'WPID');
	$status = extractTag($data, "Status");
	$type = extractTag($data, "Type");
	
	$own = "";
	
	$arrInjuries = extractMultiple($data, "Injuries", "WPInjury");
	
	//get wpid
	if (strpos($data, "<WPID own=") !== false) {
		$pos1 = strpos($data, "<WPID");
		$pos2 = strpos($data, "</WPID", $pos1);
		
		$wpid = substr($data, $pos1, ($pos2 - $pos1));
		
		//get values
		$arrWpid = explode(">", $wpid);
		$wpid = $arrWpid[1];
		
		//get the own?
		$own = substr($arrWpid[0], 11);
		$own = str_replace('"', '', $own);
	}
	
	echo "own:" . $own . "<br />\r\n";
	
	
	//get plaintiff
	$plaintiff_id = extractEntity($data, "Plaintiff", "WPEntity");
	if (strpos($data, "<Plaintiff own=") !== false) {
		$plaintiff_id = -1;
	}
	$defendant_id = extractEntity($data, "Defendant", "WPEntity");
	if (strpos($data, "<Defendant own=") !== false) {
		$defendant_id = -1;
	}
	$venue_id = extractEntity($data, "Venue", "Address");
	
	$dol = extractDate($data, "DOL");
	$sol = extractDate($data, "SOL");
	$quick = extractTag($data, 'QuickReview');
	$udf0 = extractTag($data, 'UDF0');
	$udf1 = extractTag($data, 'UDF1');
	$aoecoe = extractTag($data, 'AOECOE');
	//die();
	
	$sql = "INSERT INTO `gaylord`.`gaylord_workproduct`
	(`original_id`, `wpid`, `own`, `plaintiff_id`, `status`, `type`, `defendant_id`, `venue_id`, `dol`, `sol`, `quick`, `data`, `udf0`, `udf1`, `aoecoe`)
	VALUES (:id, :wpid, :own, :plaintiff_id, :status, :type, :defendant_id, :venue_id, :dol, :sol, :quick, :data, :udf0, :udf1, :aoecoe);
	";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->bindParam("wpid", $wpid);
	$stmt->bindParam("own", $own);
	$stmt->bindParam("udf0", $udf0);
	$stmt->bindParam("udf1", $udf1);
	$stmt->bindParam("aoecoe", $aoecoe);
	$stmt->bindParam("status", $status);
	$stmt->bindParam("type", $type);
	$stmt->bindParam("plaintiff_id", $plaintiff_id);
	$stmt->bindParam("defendant_id", $defendant_id);
	$stmt->bindParam("venue_id", $venue_id);
	$stmt->bindParam("dol", $dol);
	$stmt->bindParam("sol", $sol);
	$stmt->bindParam("quick", $quick);
	$stmt->bindParam("data", $data);
	
	$stmt->execute();
	$stmt = null; $db = null;
	
	foreach($arrInjuries as $inj_id) {
		$sql = "INSERT INTO `gaylord`.`gaylord_workproduct_injury`
		(`inj_id`, `workproduct_id`)
		VALUES (:inj_id, :wpid)";
		
		$db = getNickConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("inj_id", $inj_id);
		$stmt->bindParam("wpid", $id);
		
		$stmt->execute();
		$stmt = null; $db = null;
	}
	
	$sql = "UPDATE `gaylord`.`dbo_workproduct`
	SET `LOCK` = 'done'
	WHERE `ID` = :id";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->execute();
	$stmt = null; $db = null;
	
	$sql = "SELECT COUNT(`ID`) case_count
	FROM gaylord.dbo_workproduct
	WHERE `LOCK` IS NOT NULL";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$completed = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$case_count = 1207;
	$completed_count = $completed->case_count;
	
	if ($case_count - $completed_count > 0) {	
		echo "<script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script>";
	}
	die("all done");
	
} catch(PDOException $e) {
	echo "ERROR:<br />
";
	echo $sql;
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>