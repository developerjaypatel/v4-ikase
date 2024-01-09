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
	
		$entity = str_replace('<' . $search . ' link="' . $link . '">', '', $entity);
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
	FROM `gaylord`.`dbo_wpentity`
	WHERE 1
	#AND `ID` = 'D7D96B9BFFFF4C778D3766591C7FCEA3'
	AND `LOCK` IS NULL
	LIMIT 0, 1";

	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	//$products = $stmt->fetchAll(PDO::FETCH_OBJ);
	$product = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	//die(print_r($product));
	if (!is_object($product)) {
		die("all done");
	}
	$id = $product->ID;
	$data = $product->DATA;
	
	echo str_replace("><", ">\r\n<", $data);
	
	$arrInsurance = extractMultiple($data, "Insurance", "WPInsurance");
	$arrLawOffice = extractMultiple($data, "LawOffice", "Address");
	
	$name_id = extractEntity($data, "Name", "Address");
	$parent_id = extractEntity($data, "Parent", "WORKPRODUCT");
	$entity_id = extractEntity($data, "Entity", "Address");
	$records_id = extractEntity($data, "RecordsNeeded", "");
	
	$payrate = extractTag($data, "PayRate");
	$fees_due = extractTag($data, "FeesDue");
	$costs_due = extractTag($data, "CostsDue");
	
	if (strpos($data, "<SubdInAtty link=") !== false) {
		$subd_in = extractEntity($data, "SubdInAtty", "Address");
	} else {
		$subd_in = extractTag($data, "SubdInAtty");
	}
	$nogood = '<Name /><Address /><AptSuite /><City /><State /><ZIPCode />';
	$nogoodatall = '<Name /><Address /><AptSuite /><City /><State /><ZIPCode /><Salutation />';
	
	if ($subd_in==$nogood) {
		$subd_in = "";
	}
	if (strpos($data, "<SubdInAtty link=") !== false) {
		$subd_out = extractEntity($data, "SubdOutAtty", "Address");
	} else {
		$subd_out = extractTag($data, "SubdOutAtty");
	}
	if ($subd_out==$nogood || $subd_out==$nogoodatall) {
		$subd_out = "";
	}
	//die();
	
	$sql = "INSERT INTO `gaylord`.`gaylord_entities`
	(`original_id`, `entity_id`, `name_id`, `parent_id`, `records_id`, `subd_in`, `subd_out`, `fees_due`, `costs_due`, `payrate`)
	VALUES (:id, :entity_id, :name_id, :parent_id, :records_id, :subd_in, :subd_out, :fees_due, :costs_due, :payrate);
	";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->bindParam("entity_id", $entity_id);
	$stmt->bindParam("name_id", $name_id);
	$stmt->bindParam("parent_id", $parent_id);
	$stmt->bindParam("records_id", $records_id);
	$stmt->bindParam("subd_in", $subd_in);
	$stmt->bindParam("subd_out", $subd_out);
	$stmt->bindParam("fees_due", $fees_due);
	$stmt->bindParam("costs_due", $costs_due);
	$stmt->bindParam("payrate", $payrate);
	
	$stmt->execute();
	$stmt = null; $db = null;
	
	foreach($arrInsurance as $ins_id) {
		$sql = "INSERT INTO `gaylord`.`gaylord_entity_insurance`
		(`ins_id`, `entity_id`)
		VALUES (:ins_id, :entity_id)";
		
		$db = getNickConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("ins_id", $ins_id);
		$stmt->bindParam("entity_id", $id);
		
		$stmt->execute();
		$stmt = null; $db = null;
	}
	
	foreach($arrLawOffice as $law_id) {
		$sql = "INSERT INTO `gaylord`.`gaylord_entity_lawoffice`
		(`law_id`, `entity_id`)
		VALUES (:law_id, :entity_id)";
		
		$db = getNickConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("law_id", $law_id);
		$stmt->bindParam("entity_id", $id);
		
		$stmt->execute();
		$stmt = null; $db = null;
	}
	
	$sql = "UPDATE `gaylord`.`dbo_wpentity`
	SET `LOCK` = 'done'
	WHERE `ID` = :id";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->execute();
	$stmt = null; $db = null;
	
	$sql = "SELECT COUNT(`ID`) case_count
	FROM gaylord.dbo_wpentity
	WHERE `LOCK` IS NOT NULL";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$completed = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$case_count = 2610;
	$completed_count = $completed->case_count;
	
	if ($case_count - $completed_count > 0) {	
		echo "<script language='javascript'>parent.runEntities(" . $completed_count . "," . $case_count . ")</script>";
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