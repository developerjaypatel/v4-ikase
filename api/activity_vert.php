<?php
require_once('../shared/legacy_session.php');
session_write_close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body class="bar">
<div style="position:absolute; top:0px">
<?php
if (!isset($_SESSION['user_id'])) {
	die();
}
include("connection.php");

$user_id = passed_var("user_id", "get");

$sixmonths = mktime(0, 0, 0, date("m")-7, date("d"), date("Y"));
$sixmonths = date("Y-m", $sixmonths) . "-" . date("t", $sixmonths);

$sql = "SELECT activity_user_id, YEAR(activity_date) activity_year, MONTH(activity_date) activity_month, COUNT(activity_id) activity_count
FROM ikase_reino.cse_activity 
WHERE activity_user_id = :user_id
AND activity_date > '" . $sixmonths . "'
GROUP BY activity_user_id, YEAR(activity_date), MONTH(activity_date)
ORDER BY YEAR(activity_date), MONTH(activity_date), activity_user_id ASC";

//die($sql);
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("user_id", $user_id);
	$stmt->execute();
	$activities = $stmt->fetchAll(PDO::FETCH_OBJ);
	$days = array();
    $xOffset = 0;
    $xIncrement = 40; // width of bars
    $graphHeight = 500; // target height of graph
    $maxResult = 1;
    $scale = 1;
	
	foreach($activities as $activity) {
		//$values[$activity->activity_count] = $activity->activity_year . "/" . $activity->activity_month;
		$days[$activity->activity_month . "/" . substr($activity->activity_year, 2, 2)] = array( "act" => $activity->activity_count);
		
		$total = $activity->activity_count;
        if($maxResult < $total) $maxResult = $total;
	}
	
	// Set the scale
    $scale = $graphHeight / $maxResult / 4;
    //echo $scale;
    echo '<ul class="TGraph">';
    
	$max_height = 0;
    foreach($days as $date => $values){
        // Reverse sort the array
        arsort($values);
    	//die(print_r($values));    
        foreach($values as $priority => $num){ 
            // Scale the height to fit in the graph
            $height = ($num*$scale);
            if ($max_height < $height) {
				$max_height = $height;
			}
            // Print the Bar
            echo "<li class='$priority' style='height: ".$height."px; left: ".$xOffset."px;' title='$num'>$date</li>";
        }
        // Move on to the next column
        $xOffset = $xOffset + $xIncrement;
    }
    echo '</ul>';
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
</div>
<style>
.verticalBarGraph {
    border-bottom: 1px solid #FFF;
    height: 200px;
    margin: 0;
    padding: 0;
    position: relative;
    }
    
.verticalBarGraph li {
    border: 1px solid #555;
    border-bottom: none;
    bottom: 0;
    list-style:none;
    margin: 0;
    padding: 0;
    position: absolute;
    text-align: center;
    width: 39px;
    }
.bar ul.TGraph {
	border-bottom: 3px solid #333; 
	position: relative; 
	height: <?php echo $max_height + 10; ?>px;
	margin: 1em 0; 
	padding: 0;
	background: url(../img/bar_bg.png) bottom left;
	font: 11px Helvetica, Geneva, sans-serif;
	}
.bar .TGraph li {
	position: absolute; 
	background: #666 url(../img/bar_bg.png) repeat-y top right;
	bottom: 0; 
	margin: 0; 
	padding: 0 0 0 0;
	text-align: center; 
	list-style: none;
	width: 32px; 
	border: 1px solid #555; 
	border-bottom: none; 
	color: #FFF;
	}
.TGraph li p{
	font: 11px Helvetica, Geneva, sans-serif;
	}
.bar .TGraph li:hover {font-weight:bold;}
.bar .TGraph li.act{ background-color:#666666 }
.bar .TGraph li.p1{ background-color:#666666 }
.bar .TGraph li.p2{ background-color:#888888 }
.bar .TGraph li.p3{ background-color:#AAAAAA }
</style>
</body>
</html>
