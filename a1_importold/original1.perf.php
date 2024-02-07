<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
die(phpinfo());
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish_time = $time;
$total_time = round(($finish_time - $header_start_time), 4);

echo "include time:" . $total_time . "\r\n";
$header_start_time = $time;

//venues
$sql = "SELECT * 
FROM `ikase`.`cse_venue` 
WHERE 1
ORDER BY venue ASC";

$db = getConnection();

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish_time = $time;
$total_time = round(($finish_time - $header_start_time), 4);

echo "connection time:" . $total_time . "\r\n";
$header_start_time = $time;

$stmt = $db->prepare($sql);
$stmt = $db->query($sql);
$venues = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
$arrVenues = array();
foreach($venues as $venue){
	$arrVenues[$venue->venue_uuid] = $venue->venue_abbr;
}
//print_r($arrVenues);

for($i = 0; $i < 10000; $i++) {
	//echo $i * rand(0, $i * 2000) . "<br />";
}

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish_time = $time;
$total_time = round(($finish_time - $header_start_time), 4);

echo "total time:" . $total_time;
?>