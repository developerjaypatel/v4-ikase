<?php
include("manage_session.php");
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}
include("connection.php");

$sql_statement = $_POST["sql"];

if (strpos(strtoupper($sql_statement), "DROP") !== false) {
	die("no biggie");
}
if (strpos(strtoupper($sql_statement), "TRUNCATE") !== false) {
	die("no biggieS");
}
	
try {
	$sql = "SELECT DISTINCT customer_id, data_source 
	FROM ikase.cse_customer
	WHERE data_source != ''
    AND deleted = 'N'
	ORDER BY data_source";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$customers = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	foreach($customers as $customer) {
		$customer_id = $customer->customer_id;
		$data_source = $customer->data_source;
		
		if ($data_source!="") {
			$db_name = "`ikase_" . $data_source . "`";
		} else {
			continue;
		}
		
		$sql = "DELETE FROM " . $db_name . ".`cse_document`
		WHERE 1
		AND document_extension = 'Invoice';";
		/*
		$sql = "
		TRUNCATE " . $db_name . ".`cse_kinvoice`;
		INSERT INTO " . $db_name . ".`cse_kinvoice`
		(`kinvoice_uuid`, `parent_kinvoice_uuid`, `kinvoice_date`, `notification_date`, `reminder_date`, `paid_date`, `start_date`, `end_date`, `kinvoice_number`, `invoice_counter`, `hourly_rate`, `total`, `payments`, `customer_id`, `deleted`, `template`, `template_name` )
		
		SELECT `kinvoice_uuid`, `parent_kinvoice_uuid`, `kinvoice_date`, `notification_date`, `reminder_date`, `paid_date`, `start_date`, `end_date`, `kinvoice_number`, `invoice_counter`, `hourly_rate`, `total`, `payments`, '" . $customer_id . "', `deleted`, `template`, `template_name` 
		
		FROM ikase.cse_kinvoice
		WHERE template = 'Y'
		AND deleted = 'N'
		AND kinvoice_uuid IN ('KI5b22e8f5427ec', 'KI5b315bbbb47c4');
		
		TRUNCATE " . $db_name . ".`cse_kinvoiceitem`;
		INSERT INTO " . $db_name . ".`cse_kinvoiceitem`
		(`kinvoiceitem_uuid`, `kinvoice_uuid`, `activity_uuid`, `item_name`, `item_description`, `exact`, `minutes`, `rate`, `amount`, `unit`, `customer_id`, `deleted`)
		SELECT `kinvoiceitem_uuid`, `kinvoice_uuid`, `activity_uuid`, `item_name`, `item_description`, `exact`, `minutes`, `rate`, `amount`, `unit`, '" . $customer_id . "', `deleted` 
		FROM ikase.cse_kinvoiceitem
		WHERE 1
		AND kinvoice_uuid IN ('KI5b22e8f5427ec', 'KI5b315bbbb47c4');
		
		#DELETE FROM " . $db_name . ".`cse_document`
		#WHERE 1
		#AND document_extension = 'Invoice';
		
		#INSERT INTO " . $db_name . ".`cse_document`
		#(`document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `source`, `received_date`, `type`, `verified`, `customer_id`, `deleted`)
		#SELECT `document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `source`, `received_date`, `type`, `verified`, '" . $customer_id . "', `deleted`
		#FROM ikase.cse_document
		#WHERE 1
		#AND document_extension = 'Invoice';
		
		TRUNCATE " . $db_name . ".`cse_document_kinvoice`;
		INSERT INTO " . $db_name . ".`cse_document_kinvoice`
		(`document_kinvoice_uuid`, `document_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		
		SELECT `document_kinvoice_uuid`, `document_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, '" . $customer_id . "'
		FROM ikase.cse_document_kinvoice
		WHERE 1
		AND kinvoice_uuid IN ('KI5b22e8f5427ec', 'KI5b315bbbb47c4');";
		//die($sql);
		*/
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo $customer_id . " done\r\n";
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>