<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
$filedir = "scans/1033/20180129";
$files = scandir($filedir);

$arrBs = array();

foreach($files as $file) {
	if (strpos($file, "Batch 2") !== false) {
		if (strpos($file, ".jpg")!==false) {
			if (strpos($file, "crop")===false) {
				$arrBs[] = $file;
			}
		}
	}
	
	if (count($arrBs) > 8) {
		break;
	}
}

//die(print_r($arrBs));
//$arrBs = array("Batch 2-28.jpg");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script type="application/x-javascript">
function getRandomArbitrary(min, max) {
    return Math.floor(Math.random() * (max - min) + min);
}


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
	if (ratio < 0.5) {
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
		
		arrSeparators.push(current_index);
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
	//set the initial image
	current_index = 0;
	
	canvas = document.getElementById("myCanvas");
	ctx = canvas.getContext("2d");
	document.getElementById("barcode").src = arrBs[current_index];
	
	setTimeout(function() {
		getIKASE('barcode');
	}, 1000);
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