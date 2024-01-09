<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');	

$session_id = session_id();

echo "sess:" . $session_id;

die(print_r($_SESSION));

$targetFile = "C:\\inetpub\\wwwroot\\ikase\\uploads\\1033\\42\\f132_16011294321.pdf";
$thumbFile = "C:\\inetpub\\wwwroot\\ikase\\pdfimage\\1033\\42\\f132_16011294321.jpg";
$thumbnail_path = "C:\\inetpub\\wwwroot\\ikase\\uploads\\1033\\42\\medium\\f132_16011294321.jpg";

$targetFile = "C:\\inetpub\\wwwroot\\iKase.org\\uploads\\1094\\6235\\jetfiler\\eams_combine_appcover_24815.pdf";
//echo "from " . $targetFile . " to " . $thumbFile;	// . "<br>" . $thumbnail_path . "<br />";


if (!file_exists($targetFile)) {
	die($targetFile . " -> no file");
} else {
	echo $targetFile . " exists<br />";
}
die();

$image_magick = new imagick();

//error here
$image_magick->readImage($targetFile . "[0]");

$image_magick = $image_magick->flattenImages();
$image_magick->setResolution(300,300);
$image_magick->thumbnailImage(800, 800, true);
$image_magick->setImageFormat('jpg');

$image_magick->writeImage($thumbnail_path);
?>