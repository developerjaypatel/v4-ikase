<?php
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");
include ("../classes/cls_zipcode.php");


$query = passed_var("query");
if (strlen($query)<3) {
	mysql_close($r_link);
	die();
}
$my_zip = new zipcode_class($r_link);
$arrCities =  $my_zip->get_zip_details($query);
if (count($arrCities)>0) {
	//die(print_r($arrCities));
	$city = $arrCities["city"];
	$state = $arrCities["state_prefix"];
	$county = $arrCities["county"];			
	
	$the_row = $city . "|" . $state . "|" . $county;
}
mysql_close($r_link);

$maincontent = $the_row;
echo $maincontent;
exit(); 
?>