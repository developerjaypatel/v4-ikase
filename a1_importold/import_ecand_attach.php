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
		//remove any "own"
		$pos2 = strpos($data, "<", $pos1 + 1);
		$entity = substr($data, $pos1, ($pos2 - $pos1));	
		
		$arrOwn = explode("own", $entity);
		if (count($arrOwn) > 1) {
			$arrStuff = explode(">", $arrOwn[1]);
			$entity = $arrStuff[1]; 
		}
	
		$entity = str_replace('<' . $search . '">', '', $entity);
		
		if ($link!="") {
			$entity = str_replace('<' . $search . ' link="' . $link . '">', '', $entity);
		}
	}
	//final check
	if ($entity == "<" . $search . ">" || $entity == "<" . $search . " />") {
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
	FROM `gaylord`.`dbo_attach`
	WHERE 1
	AND `LOCK` IS NULL
	LIMIT 0, 1";

	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	//$attachs = $stmt->fetchAll(PDO::FETCH_OBJ);
	$attach = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	//die(print_r($attach));
	if (!is_object($attach)) {
		die("all done");
	}
	$id = $attach->ID;
	$data = $attach->DATA;
	
	//echo str_replace("><", ">\r\n<", $data);
	
	$ext = extractTag($data, "EXT");
	//$ext = str_replace("\\", "|", $ext);
	//die();
	
	$sql = "INSERT INTO `gaylord`.`gaylord_document`
	(`original_id`, `ext`)
	VALUES (:id, :ext);
	";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->bindParam("ext", $ext);
	
	$stmt->execute();
	$stmt = null; $db = null;
	
	$sql = "UPDATE `gaylord`.`dbo_attach`
	SET `LOCK` = 'done'
	WHERE `ID` = :id";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->execute();
	$stmt = null; $db = null;
	
	$sql = "SELECT COUNT(`ID`) case_count
	FROM gaylord.dbo_attach
	WHERE `LOCK` IS NOT NULL";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$completed = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$case_count = 915;
	$completed_count = $completed->case_count;
	
	if ($case_count - $completed_count > 0) {	
		echo "<script language='javascript'>parent.runDocuments(" . $completed_count . "," . $case_count . ")</script>";
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