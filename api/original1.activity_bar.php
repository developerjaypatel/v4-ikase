<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<style>
span.bar {
    background: url(../img/bar_bg.png) 0 0 repeat-y;
    display: block;
    width: 200px;
    line-height: 20px;
}
</style>

</head>

<body>
<?php
include("manage_session.php");
session_write_close();

//die(print_r($_SESSION));
if (!isset($_SESSION['user_id'])) {
	die();
}
include("connection.php");

$user_id = passed_var("user_id", "get");

$sql = "SELECT activity_user_id, YEAR(activity_date) activity_year, MONTH(activity_date) activity_month, COUNT(activity_id) activity_count
FROM ikase_reino.cse_activity 
WHERE activity_user_id = :user_id
AND activity_date > '2015-06-31'
GROUP BY activity_user_id, YEAR(activity_date), MONTH(activity_date)
ORDER BY YEAR(activity_date), MONTH(activity_date), activity_user_id ASC";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("user_id", $user_id);
	$stmt->execute();
	$activities = $stmt->fetchAll(PDO::FETCH_OBJ);
	$db = null;
	$values = array();
	foreach($activities as $activity) {
		$values[$activity->activity_count] = $activity->activity_year . "/" . $activity->activity_month;
	}
	
	//die(print_r($values));
	// Find the maximum percentage
	$max = max(array_keys($values));
	
	foreach($values as $percentage => $label) {
		// Calculate the position, maximum value gets position 0, smaller values approach 200
		$pos = 200 - ($percentage / $max) * 200;
		// Output the label that shows the percentage
		echo '<label>'.$percentage.'%</label>';
		// Output the span, apply style rule for the background position
		echo '<span class="bar" style="background-position: -'.$pos.'px 0;">'.$label.'</span>';
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
</body>
</html>