<?php 

//die(print_r($_POST));

include("holidays.php"); 
$start_date = $_POST["date"];
$days = $_POST["days"];

//die($start_date . " - date, days = " . $days);
$today  = mktime(0, 0, 0, date("m", strtotime($start_date))  , date("d", strtotime($start_date)), date("Y", strtotime($start_date)));

$thedate = $today;
list($month, $day, $year) = split('[/.-]', $thedate);
//echo "Month:" . $month . " Day:" . $day . " Year:" . $year . "<br />\n"

$result_date = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($start_date)) , date("d", strtotime($start_date))+$days, date("Y", strtotime($start_date))));

//echo "Today - " . date("m/d/Y", $today) . ", Date " . $days . " days from now - " . date("m/d/Y", strtotime($result_date)) . "<br/>";

while (confirm_holiday(date("Y-m-d", strtotime($result_date)))) {
	$result_date = date("m", strtotime($result_date . "+ 1 days"))."/".date("d", strtotime($result_date . "+ 1 days"))."/".date("Y", strtotime($result_date . "+ 1 days"));
}
//no weekends
while (date("N", strtotime($result_date)) > 5) {
	$result_date = date("m", strtotime($result_date . "+ 1 days"))."/".date("d", strtotime($result_date . "+ 1 days"))."/".date("Y", strtotime($result_date . "+ 1 days"));
} 

echo json_encode(array("result_date"=>$result_date));

?>