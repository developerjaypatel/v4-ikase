<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

/*
include("api/manage_session.php");
session_write_close();

include("api/connection.php");

$sql = "SELECT * FROM ikase.cse_cost_type
WHERE cost_type_id > 5";

$db = getConnection(); $stmt = $db->prepare($sql);
$stmt->execute();
$types = $stmt->fetchAll(PDO::FETCH_OBJ); 
$stmt->closeCursor(); $stmt = null; $db = null;
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Server Profile</title>
<script type="text/javascript" src="lib/jquery.1.10.2.js"></script>
</head>

<body>
<img src="images/goats_righways.png" width="400" height="600" alt="Goats" />
<?php 
//print_r($types);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish_time = $time;
$total_time = round(($finish_time - $header_start_time), 6);

echo "loaded in " . $total_time;
?>