<?php
$arrTypes = array("AME REPORT", "COPY SERVICE REQUEST", "COR", "COR - C", "COR - DA", "COR - IMR", "COR-INS", "COR-UR", "COR - OTHER ", "DEPO TRANSCRIPT", "EMAIL RECEIVED", "EMAIL SENT", "FAX RECEIVED", "FAX SENT", "FEE", "LETTER RECEIVED ", "LETTER SENT", "MANUAL ENTRY ", "MEDICAL REPORT", "MISC", "MPN", "MONTHLY STATUS", "NOTE", "P & S REPORT", "PAYMENT", "PLEADINGS", "POA/ATTY MEETING", "PQME REPORT", "PROOF SENT", "RATING CHART", "REVIEWED", "SCANNED MAIL", "SDT RECORDS", "SETTLEMENT DOCS", "TELEPHONE CALL");
/*
$arrTypes = array("COPY SERVICE REQUEST", "COR", "COR - C", "COR - DA", "COR-INS", "COR - OTHER ", "DEPO TRANSCRIPT", "EMAIL RECEIVED", "EMAIL SENT", "FAX RECEIVED", "FAX SENT", "FEE", "LETTER RECEIVED ", "LETTER SENT", "MANUAL ENTRY ", "MEDICAL REPORT", "MISC", "MONTHLY STATUS", "NOTE", "PAYMENT", "PLEADINGS", "POA/ATTY MEETING", "PROOF SENT", "REVIEWED", "SCANNED MAIL", "SDT RECORDS", "SETTLEMENT DOCS", "TELEPHONE CALL", "INVOICE", "COSTS", "PROOF OF SERVICE", "ATTORNEY NOTES", "DEPO SUMMARY ", "PERSONNEL RECORDS", "SUBPOENA", "ORDER ", "NOTICE", "STIPULATION", "REPORT ", "MEET & CONFER ", "DECLARATION", "RETAINER", "INTAKE", "EXPENSE REPORT", "LODESTAR");


$arrCategories = array("CLIENT", "CARRIER DOCUMENT", "CORRESPONDENCE", "DEFENSE ATTORNEY", "DOCUMENT ", "EMPLOYMENT", "NOTES", "MEDICAL", "DISCOVERY", "WRITTEN DISCOVERY", "SUBPOENA", "DEPOSITION", "INVESTIGATION", "DOCUMENT  REVIEW", "PLEADINGS", "LAW AND MOTION", "MEDIATION", "SETTLEMENT", "REPORT ", "COSTS", "MISC/OTHER", "RECORDS", "FEDERAL CASE", "STATE CASE");

$arrSubs = array("DOCTOR", "ATTORNEY", "PLAINTIFF", "DEFENDANT", "COURT", "OTHER ", "CO-COUNSEL", "DEFENSE ATTORNEY", "EMPLOYER", "EXPERT WITNESS");
*/
$arrSubs = array("DOCTOR", "ATTORNEY");
$arrCategories = array("CLIENT", "CARRIER DOCUMENT", "CORRESPONDENCE", "DEFENSE ATTORNEY", "DOCUMENT ", "EMPLOYMENT", "NOTES", "MEDICAL");



$arrDoc = array("types"=>$arrTypes, "categories"=>$arrCategories, "subcategories"=>$arrSubs);

error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");



	
try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");	
	
	$sql = "SELECT *
	FROM ikase.cse_customer
	WHERE customer_id = '" . $customer_id . "'
	ORDER BY customer_id ASC";
	//echo $sql . "\r\n<br>";

	$stmt = $db->prepare($sql);
	$stmt->execute();
	$customers = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	foreach($customers as $customer) {
		echo "Processing " . $customer->customer_id . "<br>";
		$sql = "INSERT INTO ikase.cse_customer_document_filters (document_filters, customer_id)
		VALUES ('" . json_encode($arrDoc) . "', '" . $customer->customer_id . "')";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
	}
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}

?>