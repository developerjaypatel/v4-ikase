<?php
require_once('shared/legacy_session.php');

if (!isset($_SESSION["user_customer_id"])) {
	die("noNoNO");
}
session_write_close();

include("api/connection.php");

function getBatchscanInfo($id) {
    $sql = "SELECT `batchscan_id`, `dateandtime`, `filename`, `pages`, `separators`, `customer_id`, `processed`, `separated`, `stacked`, `deleted`, `time_stamp`
			FROM `cse_batchscan` 
			WHERE batchscan_id=:id
			AND cse_batchscan.customer_id = " . $_SESSION['user_customer_id'] . "
			AND deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$batchscan = $stmt->fetchObject();

        // Include support for JSONP requests
        return $batchscan;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

$batchscan_id = passed_var("batchscan_id", "get");
$customer_id = $_SESSION["user_customer_id"];

$batchscan = getBatchscanInfo($batchscan_id);
$date = date("Ymd", strtotime($batchscan->dateandtime));
$customer_dir = ROOT_PATH . "\\scans\\" . $customer_id . DC . $date;

$uploaded = $batchscan->filename;
	
$file_path = $uploaded;
//remove the extension
$thumbnail_path = str_replace(".pdf", ".jpg", $file_path);

$image_magick = new imagick();
$image_magick->readImage($file_path);
$pages = $image_magick->getNumberImages();
//die("found:" . $pages);
$image_magick->setResolution(300,300);
$image_magick->writeImages($thumbnail_path, false);

//die($thumbnail_path);
//update the batchscan record with pages
$sql = "UPDATE `cse_batchscan` 
		SET `pages` = :pages,
		separators = '',
		processed = '0000-00-00 00:00:00'
		WHERE batchscan_id = :batchscan_id
		AND customer_id = :customer_id";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("pages", $pages);
	$stmt->bindParam("batchscan_id", $batchscan_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	
	$sql = "INSERT INTO cse_batchscan_track (`user_uuid`, `user_logon`, `operation`, `batchscan_id`, `dateandtime`, `filename`, `time_stamp`, `pages`, `consideration`, `attempted`, `completion`, `match`, `separators`, `stacks`, `stitched`, `customer_id`, `readimage`, `processed`, `separated`, `stacked`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', 'prep', `batchscan_id`, `dateandtime`, `filename`, `time_stamp`, `pages`, `consideration`, `attempted`, `completion`, `match`, `separators`, `stacks`, `stitched`, `customer_id`, `readimage`, `processed`, `separated`, `stacked`, `deleted`
	FROM cse_batchscan
	WHERE 1
	AND batchscan_id = :batchscan_id
	AND customer_id = :customer_id
	LIMIT 0, 1";

	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("batchscan_id", $batchscan_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	
} catch(PDOException $e) {	
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}	

$filedir = "scans/" . $customer_id . "/" . $date;
$files = scandir($filedir);
$target = str_replace(".pdf", "", $file_path);
$arrTarget = explode("\\", $target);
$target = $arrTarget[count($arrTarget) - 1];
//echo $target;
//die(print_r($files));
$arrBs = array();

foreach($files as $file) {
	if (strpos($file, $target) !== false) {
		if (strpos($file, ".jpg")!==false) {
			$arrBs[] = $file;
		}
	}
}

foreach($arrBs as $bindex=>$bs){
	$arrFile = explode("-", $bs);
	$target = $arrFile[count($arrFile) - 1];
	$arrTarget = explode(".jpg", $target);
	if (strlen($arrTarget[0])==1) {
		$arrTarget[0] = "0" . $arrTarget[0];
		$arrFile[count($arrFile) - 1] = $arrTarget[0] . ".jpg";
		$arrBs[$bindex] = implode("-", $arrFile);
	}
}
sort($arrBs);
//print_r($arrBs);
//now come back and clean up
foreach($arrBs as $bindex=>$bs){
	$arrFile = explode("-", $bs);
	$target = $arrFile[count($arrFile) - 1];
	
	if (strpos($target, "0") === 0) {
		//die($target . " -> pos:" . strpos($target, "0"));
		$arrFile[count($arrFile) - 1] = substr($target, 1);
	}
	$arrBs[$bindex] = implode("-", $arrFile);
}
//die(print_r($arrBs));
//$arrBs = array("Batch 5-61.jpg");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Barcode recognition with JavaScript</title>
<script type="text/javascript" src="lib/jquery.1.10.2.js"></script>
<script type="application/x-javascript">
function getRandomArbitrary(min, max) {
    return Math.floor(Math.random() * (max - min) + min);
}

var batchscan_id = "<?php echo $batchscan_id; ?>";
var canvas;
var thumb;
var ctx;
var thx;

var doc;
var attempt = 0;
var rand = getRandomArbitrary(0, 1);

var scaling = 2;
var yval = 200;
var ymax = 250;
//second area
var yval1 = 445;
var ymax1 = 495;

var interval_id;
var sumBlack = 0;
var sumWhite = 0;

//images array
var arrBs = ['<?php echo $filedir; ?><?php echo "/" . implode("', '" . $filedir . "/", $arrBs); ?>'];
var arrSeparators = [];

var getIKASE = function(imgOrId){
	doc = document,
		img = "object" == typeof imgOrId ? imgOrId : doc.getElementById(imgOrId),
		width = img.width,
		height = img.height;
	
	document.getElementById("feedback_holder").style.width = Math.floor(width / scaling) + "px";
		
	canvas.width = width;
	canvas.height = height;
	
	ctx.drawImage(img, 0, 0);
	
	var current_page = current_index + 1;
	document.getElementById("feedback_page").innerHTML = "Page " + current_page + " of " + arrBs.length;
	
	var original = img;
	var scale = 1 / scaling;
	if (document.getElementById("thumbnail") == null) {
		thumb = document.createElement("canvas");
		thumb.id = "thumbnail";
	}
	thumb.width = original.width * scale;
	thumb.height = original.height * scale;	
	thumb.getContext("2d").drawImage(original, 0, 0, thumb.width, thumb.height);

	document.body.appendChild(thumb);
	
	//get it
	thumb = document.getElementById("thumbnail");
	thx = thumb.getContext("2d");
			
	interval_id = setInterval(function() {
		drawBackground();
		scanLine(yval);
		yval++;
	}, 2);
}
function previewImage(image_index) {
	document.getElementById("barcode").src = arrBs[image_index];
	thx.drawImage(img, 0, 0, thumb.width, thumb.height);
}
function drawBackground() {
	ctx.drawImage(img, 0, 0);
	thx.drawImage(img, 0, 0, thumb.width, thumb.height);
}
var scanLine = function(yval) {
	
	for (var col = 0; col < width; col++) {
		var c = ctx.getImageData(col, yval, 1, 1).data;
		var r = (c[0] + c[1] + c[2]) / 3;
		
		if (r < 200) {
			sumBlack++;
		} else {
			sumWhite++;
		}
	}
	
	//draw the scanning line
	ctx.beginPath();
	ctx.moveTo(0, yval);
	ctx.lineTo(width, yval);
	ctx.strokeStyle="#FF0000";
	ctx.stroke();
	
	thx.beginPath();
	thx.moveTo(0, yval / scaling);
	thx.lineTo(width, yval / scaling);
	thx.strokeStyle="#FF0000";
	thx.stroke();
	
	if (yval >  ymax) {
		clearInterval(interval_id);
		publishResults();
	}
}
var publishResults = function() {
	drawBackground();

	var ratio = (sumBlack / sumWhite);
	//console.log(current_index, ratio, arrBs[current_index]);
	//debugger;
	if (ratio < 0.5 || ratio > .75) {
		attempt++;
		//reset the sums
		sumBlack = 0;
		sumWhite = 0;

		if (attempt > 1) {
			ctx.fillStyle = "red";
			ctx.fillRect(0, 0, canvas.width, canvas.height);
			
			ctx.font = "30px Arial";
			ctx.fillStyle = 'white';
			ctx.fillText("No Barcode", (width / 2) - 50, height/ 2);
			
			thx.fillStyle = "red";
			thx.fillRect(0, 0, thumb.width, thumb.height);
			
			nextImage();

			return;
		}
		//try with new val
		yval = yval1;
		ymax = ymax1;
		
		interval_id = setInterval(function() {
			drawBackground();
			scanLine(yval);
			yval++;
		}, 2);
	} else {
		ctx.fillStyle = "lime";
		ctx.fillRect(0, 0, canvas.width, canvas.height);
		
		ctx.font = "30px Arial";
		ctx.fillStyle = 'white';
		ctx.fillText("Barcode Detected", (width / 2) - 120, height/ 2);
		
		thx.fillStyle = "lime";
		thx.fillRect(0, 0, thumb.width, thumb.height);
		
		if (current_index < arrBs.length - 1) {
			//page number
			var filename = img.src;
			var arrPage = filename.split("-");
			var last_section = arrPage[arrPage.length - 1];
			last_section = last_section.replace("_", "");
			last_section = last_section.replace(".jpg", "");
			
			arrSeparators.push(last_section);
		}
		console.log("sep:" + current_index);
		
		document.getElementById("feedback").innerHTML = "Found " + arrSeparators.length + " documents";	
		
		nextImage();
	}
}
function allDone() {
	clearInterval(interval_id);
	//console.table(arrSeparators);
	document.getElementById("feedback").innerHTML = "Found " + (arrSeparators.length + 1) + " documents";	
	var line_number = 0;
	var sub_width = 10;
	var linediv = document.createElement("div");
	linediv.style.width = width / scaling;
	linediv.style.marginBottom = "10px";
	linediv.style.width = width + "px";
	document.body.appendChild(linediv);  
	blnFirstPage = false;
	
	for (var i = 0; i < arrBs.length; i++) {
		if (arrSeparators.indexOf(i) > -1) {
			//next line
			/*
			var newline = document.createElement("div");
			newline.style.width = width + "px";
			newline.style.height = "2px";
			newline.style.background = "black";
			newline.style.display = "";
			document.body.appendChild(newline);  
			
			//create a new linediv
			linediv = document.createElement("div");
			linediv.style.width = width;
			document.body.appendChild(linediv);  
			*/
			line_number++;
			blnFirstPage = false;
			continue;
		}
		if (!blnFirstPage) {
			var newimg = document.createElement("div");
			newimg.style.display = "inline-block";
			newimg.style.marginLeft = "5px";
			newimg.style.marginRight = "5px";
			newimg.style.borderRight = "2px";
			newimg.innerHTML = "<img src='" + arrBs[i] + "' title='Preview of Document " + (line_number + 1) + "' onmouseover='previewImage(" + i + ")' class='mini' />";
			
			linediv.appendChild(newimg);
			
			blnFirstPage = true;
		}
	}
	
	var url = 'api/batchscan/addseparators';
	formValues = "id=<?php echo $batchscan_id; ?>";
	formValues += "&separators=" + arrSeparators.join("|");
	
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
					//document.getElementById("ok_send").style.display = "block";
					sendToParent();
				}
			}
		}
	});
}
function sendToParent() {
	parent.stitchBatchscan(batchscan_id);
}
function nextImage() {
	clearInterval(interval_id);
	
	current_index++;
		
	document.getElementById("feedback_bar").style.width = Math.floor(current_index / arrBs.length * width / scaling) + "px";
	//come back to top search area
	yval = 250;
	ymax = 350;
	attempt = 0;
	//return;
	if (current_index >= arrBs.length) {
		allDone();
		return;
	}
	
	setTimeout(function() {
		document.getElementById("barcode").src = arrBs[current_index];
		getIKASE('barcode');
	}, 100);
}
var init = function() {
	document.getElementById("start_link").style.display = "none";
	//set the initial image
	current_index = 0;
	
	canvas = document.getElementById("myCanvas");
	ctx = canvas.getContext("2d");
	document.getElementById("barcode").src = arrBs[current_index];
	
	<?php //if ($_SESSION["user_customer_id"]!=1033) { ?>
	setTimeout(function() {
		getIKASE('barcode');
	}, 1000);
	<?php //} ?>
}

	
</script>
<style>
.mini {
	width:50px;
	height:auto;
}
div#preload { display: none; }
</style>
</head>

<body onload="init()">
<a href="javascript:sendToParent()" id="ok_send" style="display:none; font-size:1.2em; background:blue; padding:3px; color:white">Save Documents</a>
<a href="javascript:init()" style="display:none" id="start_link">Start</a>
<div id="preload">
<?php
// $filedir . "/", $arrBs
foreach($arrBs  as $bindex=>$bs) { ?>
	<img src="<?php echo $filedir . "/". $bs; ?>" width="1" height="1" alt="Image <?php echo $bindex + 1; ?>" />
<?php } ?>
</div>
<div id="feedback_holder">
    <div id="feedback" style="float:right"></div>
    <div id="feedback_page"></div>
    <div id="feedback_bar" style="background:blue; height:10px; width:0px; margin-top:5px; margin-bottom:5px"></div>
</div>
<div style="display:none">
<canvas id="myCanvas" style="border:1px solid #d3d3d3;">
<img id="barcode" src="" style="border:1px solid blue" />
</div>
</body>
</html>
