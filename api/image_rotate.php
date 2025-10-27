<?php
error_reporting(E_ALL);
//die("here");
// File and rotation
//die("here");
$filename = $_REQUEST["fullpath"];
$degrees = $_REQUEST["degrees"];

if (strpos($filename, "..") === false) {
	$filename = '../' . $filename;
}
//die($filename);
//$filename = '../images/goats_sideways.png';
//$filename = 'D:/uploads/1033/42/goats_sideways.png';
$degrees = 90;


$arrFile = explode(".", $filename);
$extension = $arrFile[count($arrFile) - 1];

//echo $extension;
// Content type
//header('Content-type: image/' . $extension);

// Load
if ($extension == "png") {
	$source = imagecreatefrompng($filename);
} else {
	$source = imagecreatefromjpeg($filename);
}
// Rotate
$rotate = imagerotate($source, $degrees, 0);

// Output
//die($extension  . "--" . str_replace(".png", "2.png", $filename));
if ($extension == "png") {
	$image_done = imagepng($rotate, str_replace(".png", "_copy.png", $filename));
} else {
	$image_done = imagejpeg($rotate, str_replace(".png", "_copy.png", $filename));
}

//echo $image_done;
//die();
//imagejpeg($image, "folder/file.jpg");


// Free the memory
imagedestroy($source);
imagedestroy($rotate);

rename(str_replace(".png", "_copy.png", $filename), $filename);

echo json_encode(array("image_done"=>true,"full_path"=>$filename,"degrees"=>$degrees));


