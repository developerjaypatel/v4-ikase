<?php
//error_reporting(E_ALL);

// File and rotation
$filename = passed_var("fullpath", "post");
$degrees = passed_var("degrees", "post");

//$filename = '../images/goats_sideways.png';
$degrees = 90;
//echo $filename;

$arrFile = explode(".", $filename);
$extension = $arrFile[count($arrFile) - 1];

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
if ($extension == "png") {
	$image_done = imagepng($rotate, $filename);
} else {
	$image_done = imagejpeg($rotate, $filename);
}

//imagejpeg($image, "folder/file.jpg");
echo json_encode(array("image_done"=>true,"full_path"=>$filename));
// Free the memory
imagedestroy($source);
imagedestroy($rotate);


