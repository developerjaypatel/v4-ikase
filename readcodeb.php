<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');


//take an image
//shrink it
//crop it
$targetFile = "images/page.png";


$image_magick = new imagick(); 
$image_magick->readImage($targetFile);

$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
$image_magick->setResolution(150,150);
$image_magick->thumbnailImage(1300, 1700, true);

$image_magick->setImageFormat('png');
$image_magick->cropImage(1000, 380, 155, 450);
$thumbnail_path = "images/crop_top.png";
$image_magick->writeImage($thumbnail_path);

//upside down
$targetFile = "images/page.png";


$image_magick = new imagick(); 
$image_magick->readImage($targetFile);

$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
$image_magick->setResolution(150,150);
$image_magick->thumbnailImage(1300, 1700, true);

$image_magick->setImageFormat('png');
$image_magick->rotateimage("#00000000", 180);
$image_magick->cropImage(1000, 380, 140, 854);
$thumbnail_path2 = "images/crop_top2.png";
$image_magick->writeImage($thumbnail_path2);

/*
$date = passed_var("date", "get");
$thumbnail_path = passed_var("path", "get");
$counter = passed_var("counter", "get");
$batchscan_id = passed_var("batchscan_id", "get");
$pages = passed_var("pages", "get");
$uploaded = passed_var("uploaded", "get");
*/
?>
<html>
  <head>
    <title>Barcode recognition with JavaScript</title>
    <script type="text/javascript" src="lib/jquery.1.10.2.js"></script>
    <script type="text/javascript" src="lib/get_barcode_from_image.js"></script>
  </head>
  <body>
  	<div>
        <div style="float:right;display:">
            <div id="code_feedback" style=""></div>
            <input type="button" onClick="init()" value="Check">
        </div>
        <img id="barcode" src="<?php echo $thumbnail_path; ?>" style="display:" />
        <br>
        <?php echo $thumbnail_path; ?>
    </div>
    <div>
        <div style="float:right;display:">
            <div id="code_feedback" style=""></div>
            <input type="button" onClick="init2()" value="Check2">
        </div>
        <img id="barcode2" src="<?php echo $thumbnail_path2; ?>" style="display:" />
        <br>
        <?php echo $thumbnail_path2; ?>
    </div>
    <script type="application/javascript">
	function init() {
		var code = getBarcodeFromImage('barcode');
		alert(code);
		return;
	}
	function init2() {
		var code = getBarcodeFromImage('barcode2');
		alert(code);
		return;
	}
	function sendParents() {
		console.log("send");
	}
	</script>
  </body>
</html>