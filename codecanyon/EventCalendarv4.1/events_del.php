<?php
require 'assets/functions/functions.php';
$var = $_GET['id'];


$sql1 = mysqli_query($conection, "select image FROM events WHERE id = '".$var."'");
$row = mysqli_fetch_assoc($sql1);
$image = $row['image'];

if($image == ''){
	$query = "DELETE FROM `$dbname`.`events` WHERE `events`.`id` = '$var' LIMIT 1";
}
if($image != ''){
	$query = "DELETE FROM `$dbname`.`events` WHERE `events`.`id` = '$var' LIMIT 1";
	// Select event image
	$sql = mysqli_query($conection, "SELECT image FROM events WHERE id = '".$var."'");
	$user = mysqli_fetch_object($sql);
	 
	// Removes the image from uploads folder
	unlink("assets/uploads/".$user->image."");
}


mysqli_query($conection, $query) or die(mysqli_error());

?>

<script>
location.href='./';
</script>


