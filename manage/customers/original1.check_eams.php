<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$search_term = passed_var("query");
$type = passed_var("type");
if (strlen($query)<3) {
	mysql_close($r_link);
	$the_row = "||||||||";
	die($the_row);
}
$query = "SELECT `eams_ref_number`, `firm_name`, `street_1`, `street_2`, ecarr.`city`, ecarr.`state`, ecarr.`zip_code`, `phone`, `service_method`, `last_update`
	FROM `tbl_eams_" . $type . "` ecarr
	WHERE 1 AND firm_name LIKE '%" . $search_term . "%'
	OR `eams_ref_number` LIKE '%" . $search_term . "%'
	OR `street_1` LIKE '%" . $search_term . "%'
	OR `street_2` LIKE '%" . $search_term . "%'
	OR `zip_code` = '" . $search_term . "'
	OR `phone` LIKE '%" . $search_term . "%'";
//die($query);
$resultall = mysql_query($query.$sortby, $r_link) or die("unable to get carriers<br>" . mysql_error());
$numbs = mysql_numrows($resultall);
for($int=0;$int<$numbs;$int++) {
	$eams_ref_number=mysql_result($resultall,$int,"eams_ref_number"); 
	$firm_name=mysql_result($resultall,$int,"firm_name"); 
	$street_1=mysql_result($resultall,$int,"street_1"); 
	$street_2=mysql_result($resultall,$int,"street_2"); 
	$city=mysql_result($resultall,$int,"city"); 
	$state=mysql_result($resultall,$int,"state"); 
	$zip=mysql_result($resultall,$int,"zip_code"); 
	$phone=mysql_result($resultall,$int,"phone"); 		
	
	$the_row = $eams_ref_number . "|" . $firm_name . "|" . $street_1 . "|" . $street_2 . "|" . $city . "|" . $state . "|" . $zip . "|" . $phone;	
	$arrRows[] = $the_row;
}
mysql_close($r_link);
$maincontent = implode("\n", $arrRows);
echo $maincontent;
exit();
?>