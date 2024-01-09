<?php
die(print_r($_SERVER));
/*
function getConnection() {
	$dbhost="ikase.cgjydbkybgaq.us-west-1.rds.amazonaws.com";
	$dbuser="ikaseu";
	$dbpass="access527#";
	$dbname="ikase";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
function getConnection2() {
	$dbhost="localhost";
	$dbuser="gtg_caseuser";
	$dbpass="thecase";
	$dbname="gtg_thecase";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$performance_start_time = $time;

$db = getConnection2();
$sql = "SELECT * FROM `cse_user`";	
$sql .= " WHERE 1";

$sql2 = "SELECT * FROM `cse_eams_reps`";	
$sql2 .= " WHERE 1";

try {
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt = $db->prepare($sql2);
	$stmt->execute();
	$reps = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
$db = null;

if ($performance_start_time!="") {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $performance_start_time), 4);
	echo '<div style="font-size:1.2em; color:black; font-family:Arial">Local generated in '.$total_time.' seconds.'."</div>";
}

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$performance_start_time = $time;

echo "<p>______</p>";

$db = getConnection();

try {
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt = $db->prepare($sql2);
	$stmt->execute();
	$reps = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
$db = null;

if ($performance_start_time!="") {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $performance_start_time), 4);
	echo '<div style="font-size:1.2em; color:black; font-family:Arial">AWS generated in '.$total_time.' seconds.'."</div>";
}
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>AWS Test</title>
<style>
  #textbox {
	position:absolute;
    bottom:0; 
	right:0;
    z-index:2;
	border:1px solid red;
	width:200px;
	height:200px;
	
  }
  #textbox2 {
	position:absolute;
    bottom:0; 
	right:0;
    z-index:1;
	border:1px solid blue;
	background:blue;
	width:200px;
	height:200px;
  }
</style>
</head>

<body>
<a href="javascript:moveDiv()">move it</a>
<div id="textbox">move me</div>
<div id="textbox2">bottom</div>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script language="javascript">
function moveDiv() {
	$('#textbox').animate({'right' : "+=200px"});
	return;
	
	var theright = $('#textbox').css("right").replace("px", "");
	if(theright < 200) {
		$('#textbox').animate({'right' : "+=30px"}, 400, function() {
			//theright = $('#textbox').css("right").replace("px", "");
			moveDiv();
		});
	}
}
 $('#textbox').css("right", "-200px");
</script>
</body>
</html>