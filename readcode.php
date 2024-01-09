<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('shared/legacy_session.php');
if (!isset($_SESSION["user_customer_id"])) {
	die("noNoNO");
}
include("api/connection.php");

//take an image
//shrink it
//crop it
/*$targetFile = "images/page.png";
$image_magick = new imagick(); 
$image_magick->readImage($targetFile);
//$image_magick = $image_magick->flattenImages();
$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
$image_magick->setResolution(50,50);
$image_magick->thumbnailImage(800, 800, true);

$image_magick->setImageFormat('png');
$image_magick->cropImage(800, 400, 0, 150);
$thumbnail_path = "images/crop_top.png";
$image_magick->writeImage($thumbnail_path);
*/
$date = passed_var("date", "get");
$thumbnail_path = passed_var("path", "get");
$counter = passed_var("counter", "get");
$batchscan_id = passed_var("batchscan_id", "get");
$pages = passed_var("pages", "get");
$uploaded = passed_var("uploaded", "get");
?>
<html>
  <head>
    <title>Barcode recognition with JavaScript</title>
    <script type="text/javascript" src="lib/jquery.1.10.2.js"></script>
    <script type="text/javascript" src="lib/get_barcode_from_image.js"></script>
  </head>
  <body onload="init()">
  	<div style="float:right;display:none">
    	<div id="code_feedback" style=""></div>
    	<input type="button" onClick="sendParents()" value="Ok">
    </div>
    <img id="barcode_preview" src="scans/<?php echo $_SESSION["user_customer_id"]; ?>/<?php echo $date; ?>/<?php echo $thumbnail_path; ?>" style="width:150px; height:auto" />
    <img id="barcode" src="scans/<?php echo $_SESSION["user_customer_id"]; ?>/<?php echo $date; ?>/<?php echo $thumbnail_path; ?>" style="display:none" />
    <br>
    <?php //echo $thumbnail_path; ?>
    <script type="application/javascript">
	function init() {
		var code = getBarcodeFromImage('barcode');
		if (!code) {
			document.getElementById("barcode_preview").style.border = "2px solid red";
			sendParents();
			return;
		}
		document.getElementById("code_feedback").innerHTML = code;
		if (code.indexOf("12") > -1 || code.indexOf("23") > -1) {
			document.getElementById("code_feedback").innerHTML = "&#10003;&nbsp;" + code
			var url = 'api/batchscan/addseparator';
			formValues = "id=<?php echo $batchscan_id; ?>";
			formValues += "&page=<?php echo $counter; ?>";
			
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						if (data.success) {
							document.getElementById("barcode_preview").style.border = "2px solid green";
							sendParents();
						}
					}
				}
			});
		} else {
			document.getElementById("barcode_preview").style.border = "2px solid red";
			sendParents();
		}
	}
	function sendParents() {
		setTimeout(function() {
			parent.checkBatchscanPNGs(<?php echo $batchscan_id; ?>, <?php echo $pages; ?>, "<?php echo $uploaded; ?>", "<?php echo $date; ?>", <?php echo $counter; ?>);
		}, 200);
	}
	</script>
  </body>
</html>
