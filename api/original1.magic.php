<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

die();

$targetFile = 'C:\\inetpub\\wwwroot\\ikase.org\\uploads\\1121\\5644\\eams_forms\\coversheet.pdf';
$image_magick = new Imagick(); 
$image_magick->readImage($targetFile . "[0]");
$image_magick = $image_magick->flattenImages();
$image_magick->setResolution(300,300);
$image_magick->thumbnailImage(200, 200, true);
$image_magick->setImageFormat('jpg');

$thumbnail_path = '../images/goats_righways_th.jpg';
$image_magick->writeImage($thumbnail_path);
?>