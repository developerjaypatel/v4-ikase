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
$size = passed_var("size", "get");

$sixmonths = mktime(0, 0, 0, date("m")-7, date("d"), date("Y"));
$sixmonths = date("Y-m", $sixmonths) . "-" . date("t", $sixmonths);

$sql = "SELECT activity_user_id, YEAR(activity_date) activity_year, MONTH(activity_date) activity_month, COUNT(activity_id) activity_count
FROM cse_activity 
INNER JOIN  `cse_case_activity` 
ON  `cse_activity`.`activity_uuid` = `cse_case_activity`.`activity_uuid`
INNER JOIN `cse_case` ON  (`cse_case_activity`.`case_uuid` = `cse_case`.`case_uuid` AND `cse_case`.`deleted` = 'N')
WHERE activity_user_id = :user_id
AND activity_date > '" . $sixmonths . "'
AND `cse_activity`.`deleted` = 'N'
AND `cse_activity`.customer_id = " . $_SESSION['user_customer_id'] . "
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
	if ($size=="mini") {
		$xIncrement = 30; // width of bars
		$graphHeight = 200; // target height of graph
	}
    $maxResult = 1;
    $scale = 1;
	
	foreach($activities as $activity) {
		//$values[$activity->activity_count] = $activity->activity_year . "/" . $activity->activity_month;
		$days[$activity->activity_month . "/" . substr($activity->activity_year, 2, 2)] = array( "act" => $activity->activity_count, "start_date"=>date("Y-m-d", strtotime($activity->activity_year . "-" . $activity->activity_month . "-1")), "end_date"=>date("Y-m-t", strtotime($activity->activity_year . "-" . $activity->activity_month . "-1")));
		
		$total = $activity->activity_count;
        if($maxResult < $total) $maxResult = $total;
	}
	
	//die(print_r($days));
	
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
		$start_date = $values["start_date"];
		$end_date = $values["end_date"]; 
		
		unset($values["start_date"]);
		unset($values["end_date"]);
		
        foreach($values as $priority => $num) { 
			// Scale the height to fit in the graph
            $height = ($num*$scale);
            if ($max_height < $height) {
				$max_height = $height;
			}
			$display_date = $date;
			$display_title = $num;
			$display_value = $display_date . "<br /><span style='color:yellow'>" . $num . "</span>";
			if ($size=="mini") {
				$display_date = "";
				$display_title = $date . "\r\n" . $num;
				$display_value = "<span style='color:yellow; font-size:0.7em'>" . $num . "</span>";
			}
            // Print the Bar
            echo "<li class='$priority' style='cursor:pointer;height: ".$height."px; left: ".$xOffset."px; text-align:left' title='$display_title'  onclick='reportActivity(" . $user_id . ", \"" . date("Y-m-d", strtotime($start_date)) . "\", \"" . date("Y-m-d", strtotime($end_date)) . "\")'>" . $display_value . "</span></li>";
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
	background: url(../img/bar_bg_blue2.png) bottom left;
	font: 11px Helvetica, Geneva, sans-serif;
	}
.bar .TGraph li {
	position: absolute; 
	background: #666 url(../img/bar_bg_blue2.png) repeat-y top right;
	bottom: 0; 
	margin: 0; 
	padding: 0 0 0 0;
	text-align: center; 
	list-style: none;
	<?php if ($size=="mini") { ?>
	width: 22px; 
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
