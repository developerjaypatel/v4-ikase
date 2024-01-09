<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('shared/legacy_session.php');
if (!isset($_SESSION["user_customer_id"])) {
	die("noNoNO");
}
include("api/connection.php");

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
  <body onLoad="init()">
  	<a href="javascript:init()" style="display:">init</a>
    <div style="display:none">
        <img id="barcode_preview" src="scans/<?php echo $_SESSION["user_customer_id"]; ?>/<?php echo $date; ?>/<?php echo $thumbnail_path; ?>" style="width:150px; height:auto" />
        <img id="barcode" src="scans/<?php echo $_SESSION["user_customer_id"]; ?>/<?php echo $date; ?>/<?php echo $thumbnail_path; ?>" style="display:none" />
        <div id="code_feedback"></div>
        <br>
        <?php 
		//echo $thumbnail_path; 
		//rename for upside down version
		$thumbnail_path = str_replace("_crop.jpg", "_ucrop.jpg", $thumbnail_path);
		?>
    </div>
    <div style="display:none">
        <img id="barcode_preview2" src="scans/<?php echo $_SESSION["user_customer_id"]; ?>/<?php echo $date; ?>/<?php echo $thumbnail_path; ?>" style="width:150px; height:auto" />
        <img id="barcode2" src="scans/<?php echo $_SESSION["user_customer_id"]; ?>/<?php echo $date; ?>/<?php echo $thumbnail_path; ?>" style="display:none" />
        <div id="code_feedback2"></div>
        <br>
        <?php //echo $thumbnail_path; ?>
    </div>
    <script type="application/javascript">
	var last_page;
	function init() {
		var code = getBarcodeFromImage('barcode');
		if (!code) {
			code = "NO_CODE";
		}
		document.getElementById("code_feedback").innerHTML = code;
		
		
		var code2 = getBarcodeFromImage('barcode2');
		if (!code2) {
			code2 = "NO_CODE";
		}
		document.getElementById("code_feedback2").innerHTML = code2;
		var blnCodePass = (code.indexOf("12") > -1 || code.indexOf("23") > -1);
		var blnCodePass2 = (code2.indexOf("12") > -1 || code2.indexOf("23") > -1);
		
		if (blnCodePass || blnCodePass2) {
			last_page = "<?php echo $counter; ?>";
			if (blnCodePass) {
				document.getElementById("code_feedback").innerHTML = "&#10003;&nbsp;";
			}
			if (blnCodePass2) {
				document.getElementById("code_feedback2").innerHTML = "&#10003;&nbsp;";
			}
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
							document.getElementById("barcode_preview").style.padding = "2px";
							document.getElementById("barcode_preview2").style.padding = "2px";
							
							if (blnCodePass) {
								document.getElementById("barcode_preview").style.border = "2px solid green";
							} else {
								document.getElementById("barcode_preview").style.border = "2px solid red";
							}
							if (blnCodePass2) {
								document.getElementById("barcode_preview2").style.border = "2px solid green";
							} else {
								document.getElementById("barcode_preview2").style.border = "2px solid red";
							}
							sendParents();
						}
					}
				}
			});
		} else {
			document.getElementById("barcode_preview").style.padding = "2px";
			document.getElementById("barcode_preview2").style.padding = "2px";
			if (!blnCodePass) {
				document.getElementById("barcode_preview").style.border = "2px solid red";
			}
			if (!blnCodePass2) {
				document.getElementById("barcode_preview2").style.border = "2px solid red";
			}
			sendParents();
		}
	}
	function sendParents() {
		setTimeout(function() {
			parent.checkBatchscanPNGs(<?php echo $batchscan_id; ?>, <?php echo $pages; ?>, "<?php echo $uploaded; ?>", "<?php echo $date; ?>", last_page);
		}, 100);
	}
	</script>
  </body>
</html>
