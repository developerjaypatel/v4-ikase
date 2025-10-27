<?php
if($_SERVER['REMOTE_ADDR']=="172.112.170.113") {
	//die(phpinfo());
}
//die('"here" - Angel');
if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
	header('Location:index_starlinkcms.php');
}
setcookie('samesite-test', '1', 0, '/', 'v4.ikase.org', 'SameSite=None; Secure');

if ($_SERVER['REMOTE_ADDR'] == "47.153.49.83") {
	//phpinfo();
	//die();
}
include("browser_detect.php");

//$blnDebug = ($_SERVER['REMOTE_ADDR']=='47.153.51.181');
$blnDebug = false;
if($blnMobile) {
	header("location:https://". $_SERVER['SERVER_NAME'] ."/index_mobile.php");
}

if($_SERVER["HTTPS"]=="off") {
	header("location:https://" . $_SERVER['SERVER_NAME']);
}

$sixo = strtotime("2017-09-01 18:00:00");
$rightnow = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
$blnOpen = true;

//if($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
	if ($sixo < $rightnow) {
		//$blnOpen = false;
	}
//}
//include ("text_editor/ed/datacon.php");
//include("api/connection.php");
$version_number = 8;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ikase.org / kustomweb.com">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="img/favicon.png">

    <title>iKase :: Legal Case Management Software</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="css/bootstrap.3.0.3.min.css">

    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">
    
    <link rel='stylesheet' type='text/css' href='css/jquery.fs.wallpaper.css' />
    <link rel='stylesheet' type='text/css' href='css/styles.css' />
    <link href="fonts/fontello-a1b266d9/css/fontawesome.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-codes.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-embedded.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-ie7-codes.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-ie7.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/animation.css" rel="stylesheet" />
    <!--fonts-->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet">
    <?php $color = "white";
	$ikase_logo = "img/ikase_logo_briefcase_home.png";
	$top = "100px";
	if($blnDebug) {
		$color = "black";
		
		$top = "100px";
	}
	
	$ikase_logo = "img/ikase_logo_briefcase_blue.png";
	?>
    <style>
	body, html {
	  height: 100%;
	  font-family: 'Open Sans', sans-serif;
	}
	#announcements {
		position:absolute;
		display:none;
		top: 5%;
		left: 40%;
		width: 50%;
		height: 90%;
		z-index:99;
		/*
		-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;border:#FFFFFF solid 1px; overflow-y:auto;
		*/
		color:<?php echo $color; ?>;
	}
	.jumbotron {
		position:absolute;
		top: <?php echo $top; ?>;
		z-index:98;
		background:none;
		color:<?php echo $color; ?>;
		width:100%;
	}
	
	.login_please {
		color:<?php echo $color; ?>;
		font-size:1.1em;
	}
	.forgot_password  {
		color:<?php echo $color; ?>;
	}
	input {
		font-family: 'Open Sans', sans-serif;
	}
	#this_email_label {
		color:#CCC;
	}
	#this_password_label {
		color:#CCC;
	}
	.announcement_highlight {
		font-weight:bold; 
		color:yellow;
	}
	#quote_holder {
		margin-top:20px;
		color:<?php echo $color; ?>;
		background:url(img/glass_grey.png);
		font-size:0.8em;
		font-weight:100;
		padding:5px;
	}
	#main_container {
		width:90%; 
		margin-left:auto; 
		margin-right:auto
	}
	.small_text {
		font-size:0.7em;
	}
	#quote_holder {
		line-height:14px;
	}
	@media screen and (max-width: 1400px) {
		#main_container {
			width:90%; 
		}
		#quote_holder {
			font-size:0.7em;
			line-height:16px;
		}
	}
	@media screen and (max-width: 940px) {
		h1 {
			font-size:1.6em
		}
		#ikase_logo {
			width: 150px;
		    height: auto;

		}
		
	}
	@media screen and (max-width: 760px) {
		.jumbotron p {
			font-size:0.8em
		}
		.btn-lg {
			font-size:12px;
			border-radius: 3px;
		}
	}
	</style>

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    
  </head>

  <body onLoad="getQuote()">
  	<?php include("website_nav.php"); ?>
    <div class="forgot_password_holder" style="position: absolute;width: 368px; z-index: 9999;top: 50px;left: 65.5%;"><a class="forgot_password small_text" href="javascript:forgotPassword()">Forgot Password?</a></div>
    <div class="alert" role="alert" id="capsLockWarning" style="display:none; position: absolute;width: 268px; font-size: 0.9em; z-index: 9999;top: 75px;left: 65.5%; text-align:left;"><i class="glyphicon glyphicon-warning-sign"></i>&nbsp;<span style="font-weight:bold">Caps Lock On!</span></div>
    <div class="alert alert-danger" style="display:none; position: absolute;width: 368px;margin-top: 10px;color: black;font-size: 0.9em;z-index: 9999;top: 50px;left: 55.5%;"></div>
    <div class="jumbotron">
      <div class="container" id="main_container">
      	<div style="background:orange; color:black; font-weight:bold; font-size:1.2em; margin-bottom:10px; text-align:center; line-height:20px; padding:5px; display:none">	
        IKASE IS  DOWN FOR MAINTENANCE - WEEKEND OF 9/8-9/9/2018<br>
      	</div>
      	<div  style="float:left; padding-right:50px; padding-bottom:10px" align="left">
        	<img src="<?php echo $ikase_logo; ?>" width="215" height="197" alt="iKase" id="ikase_logo">
            <!-- images/3d_logo.jpg -->
            </div>
        <h1 style="font-weight:200; font-family: 'Source Sans Pro', sans-serif;">
        	Welcome to iKase
        </h1>
        <p>
        <div style="font:Arial, Helvetica, sans-serif; background:#6FF; color:#000; font-size:35px">Welcome to our new and improved version of Ikase</div></p>
        <div id="main_text_content" style="opacity:0">
            <p>&nbsp;</p>
            
            <p <?php if ($blnDebug) { ?> style="font-weight:normal"<?php } ?>>
                iKase is a Cloud-based Legal Case Management System, dedicated to supporting the legal case worker. Our software maximizes your firm's productivity by facilitating workflows and providing targeted reports.  We adapt to your needs, <em>always</em>.
           </p>
            <p>The system is customized to match your firm's exact specifications. Your data resides in its own database on the Amazon Cloud, the screens and reports can all be optimized/created to your firm's specifications. </p>
            <p>We specialize in importing legacy databases into iKase, including A1, Tritek, eCandidus, Meruscase, and Abacus. Everything is imported, including archived documents.
            </p>
            <p>
                <a class="btn btn-primary btn-lg" href="features.php" role="button">Learn more &raquo;</a>
            </p>
            <hr />
            <p>
                <div style="text-align:center; font-size:1.8em">BE MORE PRODUCTIVE</div>
                <div style="text-align:center; font-size:0.9em; margin-top:-25px">TRIM COST AND SAVE TIME WITH IKASE</div>
    
            </p>
        </div>
        <div class="small_text" style="color:<?php echo $color; ?>">
        	<div style="float:right">
            	&copy; iKase.website 2010 - <?php echo date("Y"); ?>
            </div>
        	Version TRINITY 2.0
        </div>
        <div id="quote_holder">
        	<div style="float:right;font-size:0.8em; display:"><span style="font-style:italic" id="artist_info_span"></span></div>
        </div>
      </div>
      
      <div id="weather" style="display:none;width: 60%;
    margin-left: auto;
    margin-right: auto;
    font-size: 0.7em;"></div>
      
    <div style="padding-top:30px; width: 60%;
    margin-left: auto;
    margin-right: auto;">
            <div class="alert-origin small_text" style="margin-top:90px;display:none; background:#F60;"></div>
      </div>
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="lib/jquery-3.7.0.min.js"></script>
    <script src="lib/jquery.fs.wallpaper.js"></script>
    
    <script src="js/cookies.js"></script>
    <script src="js/utilities.js"></script> .
  <script language="javascript">
	/*
	<?php if(!$blnDebug) { ?>
		var wallpaper_image = "img/office_dark.jpg";
		var rand = Math.floor((Math.random() * 2) + 1);
		if (rand > 1) {
			wallpaper_image = "img/library.jpg";
		}
	<?php } else { ?>
		var wallpaper_image = "img/office_trends_1.jpg";
		var rand = Math.floor((Math.random() * 2) + 1);
		if (rand > 1) {
			wallpaper_image = "img/office_decor_1.jpg";
		}
	<?php } ?>
	*/
	var rand = Math.floor((Math.random() * 178) + 1);
	rand = 505;
	wallpaper_image = "images/home/" + rand + ".jpg";
	$("body").wallpaper({
		source: wallpaper_image
	});
	//$("#artist_info_span").html("Tom Lovell");
	
	setTimeout(function() {
		getWeather();
		
		$("#password_holder").prepend('<div style="position:absolute; top:-27px; font-size:1.2em; width:20px; visibility:hidden; z-index: 9999;top: 17px;margin-left:-20px;" id="eye_holder"><i class="glyphicon glyphicon-eye-close" style="color:black; cursor:pointer" id="show_password" title="Show Password"></i></div>');
		
		var added = $("#inputPassword").css("width").replace("px", "");
		var started = $("#eye_holder").css("left").replace("px", "");
		
		$("#eye_holder").css("left", (Number(started) + Number(added) - 1) + "px");
		
		// $('#show_password').on("mousedown", function() {
		// 	document.getElementById("inputPassword").type = "text";
		// 	$('#show_password').css("color", "black");
		// });
		// $('#show_password').on("mouseup", function() {
		// 	document.getElementById("inputPassword").type = "password";
		// 	$('#show_password').css("color", "black");
		// });

		// added this code for show/hide password till click again with changing eye open/close icon 
		var flag = 0;
		$("#show_password").on("click", function() {
			if(flag == 0)
			{
				document.getElementById("inputPassword").type = "text";
				flag = 1;
				$("#show_password").removeClass("glyphicon-eye-close");
				$("#show_password").addClass("glyphicon-eye-open");
			}
			else
			{
				document.getElementById("inputPassword").type = "password";
				flag = 0;
				$("#show_password").removeClass("glyphicon-eye-open");
				$("#show_password").addClass("glyphicon-eye-close");
			}
		});
		
	}, 200);
	
	$('.form-control').keypress(function(e) {
		e = e || window.event;
	
		// An empty field resets the visibility.
		if (this.value === '') {
			$('#capsLockWarning').hide();
			return;
		}
	
		// We need alphabetic characters to make a match.
		var character = String.fromCharCode(e.keyCode || e.which);
		if (character.toUpperCase() === character.toLowerCase()) {
			return;
		}
	
		// SHIFT doesn't usually give us a lowercase character. Check for this
		// and for when we get a lowercase character when SHIFT is enabled. 
		if ((e.shiftKey && character.toLowerCase() === character) ||
			(!e.shiftKey && character.toUpperCase() === character)) {
			$('#capsLockWarning').show();
		} else {
			$('#capsLockWarning').hide();
		}
	});
	$('#inputPassword').on('focus', function() {
		$("#eye_holder").css("visibility", "visible");
	});
	
	$("body").on('mousemove', function() {
		var main_text_content = document.getElementById("main_text_content");
		main_text_content.style.transition = "opacity 3s linear 0s";
		main_text_content.style.opacity = 1;
	});
	function getQuote() {
		var url = "api/quote";
						
		$.ajax({
			url:url,
			type:'GET',
			dataType:"text",
			data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#quote_holder").html(data);
				}
			}
		});
	}
	</script>
  </body>
</html>
