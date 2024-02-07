<?php 
error_reporting(E_ALL);
$sql = "SELECT CONTACTID
      ,FULLNAME
      ,CUST_EAMS01_095807683
      ,CUST_CaseStatus_094003650
      ,CUST_WCAB_094426540
      ,CUST_DOI01_095107818
      ,CUST_Claim01_095258261
      ,BIRTHDATE
  FROM tbl_contact";
  
$link = mysql_connect("127.0.0.1", "root", "admin527#") or die("Cannot connect to db</br>" . mysql_error());
mysql_select_db("nat", $link);

$result = mysql_query($sql, $link);
//die($result);
$arrRows = array();
$numbs = mysql_num_rows($result);
for ($i = 0; $i < $numbs; $i++) {
	$contact_id = mysql_result($result, $i, "CONTACTID");
	$name = mysql_result($result, $i, "FULLNAME");
	if (strpos($name, "'")) {
		$name = str_replace("'", '|', $name);
	}
	$adj = mysql_result($result, $i, "CUST_EAMS01_095807683");
	$status = mysql_result($result, $i, "CUST_CaseStatus_094003650");
	if (strpos($status, "'")) {
		$status = str_replace("'", '-', $status);
	}
	$venue = mysql_result($result, $i, "CUST_WCAB_094426540");
	$doi = mysql_result($result, $i, "CUST_DOI01_095107818");
	if (strpos($doi, "'")) {
		$doi = str_replace("'", ';', $doi);
	}
	$claim = mysql_result($result, $i, "CUST_Claim01_095258261");
	$dob = mysql_result($result, $i, "BIRTHDATE");
	//echo $contact_id .'</br>';
	$row = $contact_id . ", " . $name . ", " . $adj . ", " . $status . ", " . $venue . ", " . $doi . ", " . $claim . ", " . $dob; 
	array_push($arrRows, $row);
	$sql_insert = "INSERT INTO `nat_ikase`.`contact_adj_import` (`contact_id`, `full_name`, `adj_number`, `case_status`, `venue`, `doi`, `claim`, `contact_dob`) 
	VALUES
	('" . $contact_id . "', '" . $name . "', '" . $adj . "', '" . $status . "', '" . $venue . "', '" . $doi . "', '" . $claim . "', '" . $dob . "');";
	//die($sql_insert);
	//$link_insert = mysql_connect("127.0.0.1", "terriel", "tdm1966tdm") or die("Cannot connect to db</br>" . mysql_error());
	echo $contact_id . ' - ' . $name . ' - ' . $adj . ' - ' . $status . ' - ' . $venue . ' - ' . $doi . ' - ' . $claim . ' - ' . $dob;
	$result_insert = mysql_query($sql_insert, $link) or die(mysql_error());
}
$sql_count = "SELECT *
  FROM `nat_ikase`.`contact_adj_import`";
  
$result_count = mysql_query($sql_count, $link);
//die($result);
//$arrRows = array();
$numbs_count = mysql_num_rows($result_count);
  
//echo "Done - " . $numbs_count; 
die(print_r($arrRows));
?>