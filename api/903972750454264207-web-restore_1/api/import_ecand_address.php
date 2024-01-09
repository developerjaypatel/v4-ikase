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
	FROM `gaylord`.`dbo_address`
	WHERE 1
	AND `LOCK` IS NULL
	LIMIT 0, 1";

	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	//$addresss = $stmt->fetchAll(PDO::FETCH_OBJ);
	$address = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	//die(print_r($address));
	if (!is_object($address)) {
		die("all done");
	}
	$id = $address->ID;
	$data = $address->DATA;
	
	//echo str_replace("><", ">\r\n<", $data);
	//die();
	$main_phone = "";
	$arrPhone = extractMultiple($data, "Phone", "");
	foreach($arrPhone as $pindex=>$phone) {
		$phone = trim(str_replace("<Phone>", "", $phone));
		if ($main_phone == "") {
			$main_phone = $phone;
		}
		$arrPhone[$pindex] = $phone;
	}
	/*
	if (count($arrPhone) > 0) {
		print_r($arrPhone);
		die();
	}
	*/
	//$arrLawOffice = extractMultiple($data, "LawOffice", "Address");
	$parent_id = extractEntity($data, "Parent", "WPEntity");
	$name = extractTag($data, "Name");
	$entry_id = extractTag($data, "EntryID");
	$dob = extractTag($data, "Birthdate");
	if ($dob=="") {
		$dob = '0000-00-00 00:00:00';
	}
	$ssn = extractTag($data, "SS");
	$language = extractTag($data, "Language");
	$city = extractTag($data, "City");
	$state = extractTag($data, "State");
	$street = extractTag($data, "Address");
	$suite = extractTag($data, "AptSuite");
	$zipcode = extractTag($data, "ZipCode");
	$spouse = extractTag($data, "Spouse");
	$title = extractTag($data, "Title");
	$status = extractTag($data, "Status");
	$company = extractTag($data, "Company");
	
	$forclosest = extractTag($data, "ForClosest");
	$legal_status = extractTag($data, "LegalStatus");
	$salutation = extractTag($data, "Salutation");
	$email = extractTag($data, "EMail");
	$county = extractTag($data, "County");
	$position = extractTag($data, "Position");
	
	
	//die();
	
	$sql = "INSERT INTO `gaylord`.`gaylord_address`
	(`original_id`, `parent_id`, `entry_id`, `name`, `street`, `suite`, `city`, `state`, `zipcode`, `ssn`, `dob`, `status`, `company`, `phone`, `spouse`, `language`, `title`, 
	`forclosest`, `legal_status`, `salutation`, `email`, `position`, `county`)
	VALUES (:id, :parent_id, :entry_id, :name, :street, :suite, :city, :state, :zipcode, :ssn, :dob, :status, :company, :phone, :spouse, :language, :title, 
	:forclosest, :legal_status, :salutation, :email, :position, :county);
	";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->bindParam("parent_id", $parent_id);
	$stmt->bindParam("entry_id", $entry_id);
	$stmt->bindParam("name", $name);
	$stmt->bindParam("street", $street);
	$stmt->bindParam("suite", $suite);
	$stmt->bindParam("city", $city);
	$stmt->bindParam("state", $state);
	$stmt->bindParam("zipcode", $zipcode);
	$stmt->bindParam("ssn", $ssn);
	$stmt->bindParam("dob", $dob);
	
	$stmt->bindParam("forclosest", $forclosest);
	$stmt->bindParam("legal_status", $legal_status);
	$stmt->bindParam("salutation", $salutation);
	$stmt->bindParam("email", $email);
	$stmt->bindParam("position", $position);
	$stmt->bindParam("county", $county);
	
	$stmt->bindParam("status", $status);
	$stmt->bindParam("company", $company);
	$stmt->bindParam("phone", $main_phone);
	$stmt->bindParam("spouse", $spouse);
	$stmt->bindParam("language", $language);
	$stmt->bindParam("title", $title);
	
	$stmt->execute();
	$stmt = null; $db = null;
	
	foreach($arrPhone as $phone) {
		$sql = "INSERT INTO `gaylord`.`gaylord_address_phone`
		(`phone`, `add_id`)
		VALUES (:phone, :add_id)";
		
		$db = getNickConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("phone", $phone);
		$stmt->bindParam("add_id", $id);
		
		$stmt->execute();
		$stmt = null; $db = null;
	}
	
	
	$sql = "UPDATE `gaylord`.`dbo_address`
	SET `LOCK` = 'done'
	WHERE `ID` = :id";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->execute();
	$stmt = null; $db = null;
	
	$sql = "SELECT COUNT(`ID`) case_count
	FROM gaylord.dbo_address
	WHERE `LOCK` IS NOT NULL";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$completed = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$case_count = 17460;
	$completed_count = $completed->case_count;
	
	if ($case_count - $completed_count > 0) {	
		echo "<script language='javascript'>parent.runAddresses(" . $completed_count . "," . $case_count . ")</script>";
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