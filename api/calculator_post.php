<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');

$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "https://www.matrixdocuments.com" || $http_origin == "https://www.cajetfile.com" || $http_origin == "https://www.ikase.xyz") {  
    header("Access-Control-Allow-Origin: $http_origin");
}
//die(print_r($_POST));
 
include("holidays.php"); 

function dateDiff($interval,$dateTimeBegin,$dateTimeEnd) {
 //Parse about any English textual datetime
 //$dateTimeBegin, $dateTimeEnd

 $dateTimeBegin=strtotime($dateTimeBegin);
 if($dateTimeBegin === -1) {
   return("..begin date Invalid");
 }

 $dateTimeEnd=strtotime($dateTimeEnd);
 if($dateTimeEnd === -1) {
   return("..end date Invalid");
 }

 $dif=$dateTimeEnd - $dateTimeBegin;
 switch($interval) {
   case "s"://seconds
	   return($dif);

   case "n"://minutes
	   return(floor($dif/60)); //60s=1m

   case "h"://hours
	   return(floor($dif/3600)); //3600s=1h

   case "d"://days
	   return(floor($dif/86400)); //86400s=1d

   case "ww"://Week
	   return(floor($dif/604800)); //604800s=1week=1semana

   case "m": //similar result "m" dateDiff Microsoft
	   $monthBegin=(date("Y",$dateTimeBegin)*12)+
		 date("n",$dateTimeBegin);
	   $monthEnd=(date("Y",$dateTimeEnd)*12)+
		 date("n",$dateTimeEnd);
	   $monthDiff=$monthEnd-$monthBegin;
	   return($monthDiff);

   case "yyyy": //similar result "yyyy" dateDiff Microsoft
	   return(date("Y",$dateTimeEnd) - date("Y",$dateTimeBegin));

   default:
	   return(floor($dif/86400)); //86400s=1d
 }

}

$start_date = $_POST["date"];
$start_date = date("Y-m-d", strtotime($start_date));
$holiday_validation = "N";
if (isset($_POST["holiday_validation"])) {
	$holiday_validation = $_POST["holiday_validation"];
}
$post_days = $_POST["days"];
$post_years = "";
if (isset($_POST["years"])) {
	$post_years = $_POST["years"];
	
	//might have to recalc days
	$next_year = mktime(0, 0, 0, date("m", strtotime($start_date))  , date("d", strtotime($start_date)), date("Y", strtotime($start_date)) + $post_years);
	$next_year = date("Y-m-d", $next_year);
	//echo "dates:" . $start_date . " -- " . $next_year . "\r\n";
	$post_days = dateDiff("d", $start_date, $next_year);
}
$blnWorkingDays = (isset($_POST["working_days"]));

$plus_days = "+ 1 days";
if ($post_days < 0) {
	$plus_days = "- 1 days";
}

//echo $start_date . " - date, days = " . $days;
$today  = mktime(0, 0, 0, date("m", strtotime($start_date))  , date("d", strtotime($start_date)), date("Y", strtotime($start_date)));

if ($blnWorkingDays) {
	$result_date = nextWorkingDay($post_days, $start_date);
	//die($result_date);
	$display_date = date("D M jS, Y", strtotime($result_date));
	$start_display_date = date("D M jS, Y", strtotime($start_date));
	$unix_display_date = date("Y-m-d", strtotime($result_date));
	$result_date = date("m/d/Y", strtotime($result_date));
	
	$arrResults[] = array("days"=>$post_days, "calculated_date"=>$unix_display_date, "display_date"=>$display_date, "start_date"=>$start_date, "start_display_date"=>$start_display_date, "unix_display_date"=>$unix_display_date);
	echo json_encode($arrResults);
	die();
}

$thedate = $today;
//list($month, $day, $year) = preg_split('|[/.-]|', $thedate);
//echo "Month:" . $month . " Day:" . $day . " Year:" . $year . "<br />\n";

$result_date = date("m/d/Y", mktime(0, 0, 0, date("m", strtotime($start_date)) , date("d", strtotime($start_date))+$post_days, date("Y", strtotime($start_date))));
$unix_display_date = date("Y-m-d", strtotime($result_date));
//echo "Today - " . date("m/d/Y", $today) . ", Date " . $days . " days from now - " . date("m/d/Y", strtotime($result_date)) . "<br/>";


if ($holiday_validation != "Y") { 
	while (confirm_holiday(date("Y-m-d", strtotime($result_date)))) {
		$result_date = date("m", strtotime($result_date . $plus_days))."/".date("d", strtotime($result_date . $plus_days))."/".date("Y", strtotime($result_date . $plus_days));
		$unix_display_date = date("Y-m-d", strtotime($result_date));
	}
	
	//no weekends
	while (date("N", strtotime($result_date)) > 5) {
		$result_date = date("m", strtotime($result_date . $plus_days))."/".date("d", strtotime($result_date . $plus_days))."/".date("Y", strtotime($result_date . $plus_days));
	} 
	
	$display_date = date("D M jS, Y", strtotime($result_date));
	$start_display_date = date("D M jS, Y", strtotime($start_date));
	$unix_display_date = date("Y-m-d", strtotime($result_date));
	
	//array_push($arrResults, $result_date);
	$arrResults[] = array("days"=>$post_days, "calculated_date"=>$unix_display_date, "display_date"=>$display_date, "start_date"=>$start_date, "start_display_date"=>$start_display_date, "unix_display_date"=>$unix_display_date);
			
	echo json_encode($arrResults);
} else {
	while (confirm_holiday(date("Y-m-d", strtotime($result_date)))) {
	$unix_display_date = date("Y-m-d", strtotime($result_date));
	$result_date = date("m", strtotime($result_date . $plus_days))."/".date("d", strtotime($result_date . $plus_days))."/".date("Y", strtotime($result_date . $plus_days));
}
	$display_date = date("D M jS, Y", strtotime($result_date));
	$start_display_date = date("D M jS, Y", strtotime($start_date));
	$formatted_date = date("m/d/Y", strtotime($result_date));
	$unix_display_date = date("Y-m-d", strtotime($result_date));
	
	$arrResults[] = array("days"=>$post_days, "calculated_date"=>$unix_display_date, "display_date"=>$display_date, "start_date"=>$start_date, "start_display_date"=>$start_display_date, "formatted_date"=>$formatted_date, "unix_display_date"=>$unix_display_date);
	echo json_encode($arrResults);
}
