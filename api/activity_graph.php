<?php
require_once('../shared/legacy_session.php');
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
	$values = array();
	foreach($activities as $activity) {
		$values[] = $activity->activity_count;
	}
	
	// Get the total number of columns we are going to plot

    $columns  = count($values);

// Get the height and width of the final image

    $width = 300;
    $height = 200;

// Set the amount of space between each column

    $padding = 5;

// Get the width of 1 column

    $column_width = $width / $columns ;

// Generate the image variables

    $im        = imagecreate($width,$height);
    $gray      = imagecolorallocate ($im,0xcc,0xcc,0xcc);
    $gray_lite = imagecolorallocate ($im,0xee,0xee,0xee);
    $gray_dark = imagecolorallocate ($im,0x7f,0x7f,0x7f);
    $white     = imagecolorallocate ($im,0xff,0xff,0xff);
    
// Fill in the background of the image

    imagefilledrectangle($im,0,0,$width,$height,$white);
    
    $maxv = 0;

// Calculate the maximum value we are going to plot

    for($i=0;$i<$columns;$i++)$maxv = max($values[$i],$maxv);

// Now plot each column
        
    for($i=0;$i<$columns;$i++)
    {
        $column_height = ($height / 100) * (( $values[$i] / $maxv) *100);

        $x1 = $i*$column_width;
        $y1 = $height-$column_height;
        $x2 = (($i+1)*$column_width)-$padding;
        $y2 = $height;

        imagefilledrectangle($im,$x1,$y1,$x2,$y2,$gray);

// This part is just for 3D effect

        imageline($im,$x1,$y1,$x1,$y2,$gray_lite);
        imageline($im,$x1,$y2,$x2,$y2,$gray_lite);
        imageline($im,$x2,$y1,$x2,$y2,$gray_dark);

    }

// Send the PNG header information. Replace for JPEG or GIF or whatever

    header ("Content-type: image/png");
    imagepng($im);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
