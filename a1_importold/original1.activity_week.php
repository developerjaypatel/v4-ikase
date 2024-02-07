<?php
include("manage_session.php");
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
//die(print_r($_SESSION));
if (!isset($_SESSION['user_id'])) {
	die();
}
include("connection.php");

$user_id = passed_var("user_id", "get");
$size = passed_var("size", "get");

$start = mktime(0,0,0,date("m"),date("d")-date("w")-7);
$end = mktime(0,0,0,date("m"),date("d")-date("w")-1);

$sql = "SELECT CAST(activity_date AS DATE) activity_day, COUNT(activity_id) activity_count
FROM cse_activity  
INNER JOIN  `cse_case_activity` 
ON  `cse_activity`.`activity_uuid` = `cse_case_activity`.`activity_uuid`
INNER JOIN `cse_case` ON  (`cse_case_activity`.`case_uuid` = `cse_case`.`case_uuid` AND `cse_case`.`deleted` = 'N')
WHERE activity_user_id = :user_id
AND `cse_activity`.`deleted` = 'N'
AND `cse_activity`.customer_id = " . $_SESSION['user_customer_id'] . "
AND activity_date BETWEEN '" . date("Y-m-d", $start) . "' AND '" . date("Y-m-d", $end) . "'
GROUP BY activity_user_id, CAST(activity_date AS DATE)
ORDER BY activity_user_id ASC, CAST(activity_date AS DATE) ASC";

//die($sql);
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("user_id", $user_id);
	$stmt->execute();
	$activities = $stmt->fetchAll(PDO::FETCH_OBJ);
	$db = null;
	$days = array();
    $xOffset = 0;
    $xIncrement = 40; // width of bars
    $graphHeight = 500; // target height of graph
	if ($size=="mini") {
		$xIncrement = 20; // width of bars
		$graphHeight = 200; // target height of graph
	}
    $maxResult = 1;
    $scale = 1;
	$sum_total = 0;
	
	foreach($activities as $activity) {
		//$values[$activity->activity_count] = $activity->activity_year . "/" . $activity->activity_month;
		$days[$activity->activity_day] = array( "act" => $activity->activity_count);
		
		$total = $activity->activity_count;
		
		$sum_total += $total;
        if($maxResult < $total) $maxResult = $total;
	}
	
	// Set the scale
    $scale = $graphHeight / $maxResult / 4;
	if ($size=="mini") {
		$scale = $scale / 1.5;
	}
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
			$display_date = date("m/d/y", strtotime($date));
			$display_title = $num . "\r\n\r\nClick to list activities";
			$display_value = $display_date . "<br /><span style='color:yellow'>" . $num . "</span>";
			if ($size=="mini") {
				$display_title = $display_date . "\r\n" . $num . "\r\n\r\nClick to list activities";
				$display_date = "";
				$display_value = "<span style='color:yellow; font-size:0.7em'>" . $num . "</span>";
			}
            // Print the Bar
            echo "<li class='$priority' style='cursor:pointer;height: ".$height."px; left: ".$xOffset."px; text-align:left;' title='$display_title' onclick='reportActivity(" . $user_id . ", \"" . date("Y-m-d", strtotime($date)) . "\", \"" . date("Y-m-d", strtotime($date)) . "\")'>" . $display_value . "</li>";
		
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
<!--
<div>
Max: <?php echo $maxResult; ?><br />
Avg: <?php echo number_format($sum_total / count($days), 0); ?>
</div>
-->
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
	background: url(../img/bar_bg_blue.png) bottom left;
	font: 11px Helvetica, Geneva, sans-serif;
	}
.bar .TGraph li {
	position: absolute; 
	background: #666 url(../img/bar_bg_blue.png) repeat-y top right;
	bottom: 0; 
	margin: 0; 
	padding: 0 0 0 0;
	text-align: center; 
	list-style: none;
	<?php if ($size=="mini") { ?>
	width: 12px; 
	<?php } else { ?>
	width: 32px; 
	<?php } ?>
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
<script language="javascript">
function reportActivity(user_id, start_date, end_date) {
	var newlocation = window.top.location.href.split("#")[0];
	newlocation += "#activities/" + user_id + "/" + start_date + "/" + end_date;
	window.top.location.href = newlocation; 
}
</script>
</body>
</html>