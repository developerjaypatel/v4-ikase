<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("../../api/manage_session.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>iKase Webmail</title>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
<script language="javascript" src="countdown.js"></script>
<script type="text/javascript">
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
</script>
</head>
<style type="text/css">
body {
	background-color: #FFFFE5;
}
</style>
<body onload="MM_preloadImages('images/btn_google_signin_light_focus_web.png')"><div style="color:black">
<?php
include_once "templates/base.php";
//die(print_r($_REQUEST));

if (isset($_REQUEST['logout'])) {
	unset($_SESSION['access_token']);
	//header("location:../../v8.php");
}
session_write_close();

if (!isWebRequest()) {
	echo "To view this page on a webserver using PHP 5.4 or above run: \n\t
	php -S localhost:8080\n";
	exit();
}
//echo pageHeader("GMail Screen"); 

$has_expired=true;
if( isset($_SESSION['access_token']) ){

	$token=json_decode($_SESSION['access_token'],true);

	$expires_in=$token['expires_in'];
	$created=$token['created'];

	if( ( $expires_in + $created - time() ) > 0){
		$has_expired=false;
	}   
} 
if (!$has_expired) {
	//echo "token current as of " . date("H:i:s");
} else {
	if (!isset($_GET["logout"])) {
		echo "<script>document.location.href='index.php?expired=true&logout=y'</script>";
		die();
	}
}
?>
<style>
.white_text {
	color:white;
}
</style>
<div id="feedback"></div>
<div style="float:right; margin-right:5px" id="logout_holder">
    <a href="index.php?logout" style="font-size:0.7em; color:red" title="Click here to logout">
	    <span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>
    </a>
</div>
<div style="display:none; text-align:center; width:80%; margin-left:auto; margin-right:auto; margin-top:5px" id="list_holder"><a href="javascript:listMessages()" id="list_messages_link">List  Messages</a></div>
<div style="display:none; text-align:center; width:80%; margin-left:auto; margin-right:auto; margin-top:5px" id="authorize_holder">
	<!--<div class="g-signin2" data-onsuccess="onSignIn"></div>-->
	<a href="javascript:getToken()" class="white_text" id="authorize_link" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('signin_button','','images/btn_google_signin_light_focus_web.png',1)"><img src="images/btn_google_signin_light_normal_web.png" alt="Signin" name="signin_button" width="191" height="46" border="0" id="signin_button" /></a>&nbsp;<span id="pageTimer" style="font-size:0.7em"></span>
</div>
<ul style="list-style:none; margin-left:-40px">
	<li style="display:none">Welcome <?php echo $_SESSION['user_name']; ?></li>
    <li><a href="index.php?logout">Logout</a></li>
    <li><a href="get_token.php">Authorize GMail Access</a></li>
    <li><a href="refresh_token.php">Refresh GMail Access</a></li>
    <li><a href="user-gmail.php">List GMail Messages</a></li>
    <li style="display:none"><a href="javascript:refreshToken()" class="white_text">Refresh  Access</a></li>
</ul>
</div>
<div id="message_feedback" style="display:none; position:absolute; left:100px; color:black"></div>
<div id="progress_bar" style="background:#FCC; color:black; font-size:0.7em; width:0%; height:20px">
<div id="progress_holder"></div>
</div>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script language="javascript">
var logoutUser = function() {
	document.location.href = "index.php?forced=y&logout=yes";
}
var checkProgress = function() {
	clearTimeout(progress_id);
	var data = "";
	var url = "check_progress.php"
	
	$.ajax({
	  type: "POST",
	  url: url,
	  data: data
	})
	  .done(function( msg ) {
		  	var json = JSON.parse(msg);
			$("#progress_bar").css("width", json.progress);
			$("#progress_holder").html(json.progress);
			if (json.progress!="100%" && json.progress!=false) {
				progress_id = setTimeout(function(){
					checkProgress();
				}, 300);
				return;
			}
			
	}).fail(function (jqXHR, textStatus) {
		console.log(textStatus);
	});
}
var listMessages = function() {
	clearTimeout(timerId);
	$('#pageTimer').html("");
	clearTimeout(list_timeout_id);
	//no more refreshes for now
	clearTimeout(token_timeout_id);
	
	$("#list_holder").hide();
	//$("#logout_holder").hide();
	
	$("#progress_bar").css("width", "0%");
	$("#message_feedback").html("Acquiring emails...");
	$("#message_feedback").show();
	var data = "";	// + msg;
	var url = "list_messages.php";
	
	//return;
	
	//start watching for progress
	progress_id = setTimeout(function(){
		checkProgress();
	}, 1000);
	
	$.ajax({
	  type: "POST",
	  url: url,
	  data: data
	})
	  .done(function( msg ) {
		  	var json = JSON.parse(msg);
			
			if (typeof json.success != "undefined") {
				$("#message_feedback").html(json.count + " New Messages");
				clearTimeout(list_timeout_id);
				clearTimeout(token_timeout_id);
				
				setTimeout(function() {
					window.close();					
				}, 1500);
				
				token_timeout_id = setTimeout(function() {
					refreshToken();
				}, 60000);
			}
			/*
			if (json.count==0) {
				setTimeout(function() {
					window.close();
					var current_feedback = $("#message_feedback").html();
					$("#message_feedback").html(current_feedback + "<br>Closing..." + json.last);
				}, 500);
			
				return;
			} 
			*/
	}).fail(function (jqXHR, textStatus) {
		console.log(textStatus);
	});
}
var refreshToken = function() {
	clearTimeout(token_timeout_id);
	
	var data = "";	// + msg;
	var url = "refresh_token.php";
	$.ajax({
	  type: "POST",
	  url: url,
	  data: data
	})
	  .done(function( msg ) {
		  	var json = JSON.parse(msg);
			if (json.result!="token current") {
				//$("#feedback").html("Authorization Required");
				$("#list_holder").hide();
				$("#logout_holder").hide();
				$("#authorize_holder").fadeIn(function() {
					//$("#authorize_link").css("background", "blue");
				});
				
				//use this as init
				var timerId =
					countdown(
						new Date(),
						function(ts) {
							var the_value = 5 - ts.seconds;
							if (the_value <= 0) {
								window.close();
							}
							var plural = (the_value > 1) ? "s" : "";
						  $('#pageTimer').html("Closing&nbsp;in&nbsp;" + the_value + "&nbsp;sec" + plural);
						},
						countdown.HOURS|countdown.MINUTES|countdown.SECONDS
					);
				/*
				list_timeout_id = setTimeout(function(){
					window.close();
				}, 20000);
				*/
			} else {
				var theday = new Date();
				var thedate = (theday.getMonth() + 1) + "/" + theday.getDate() + "/" + theday.getFullYear();
				//$("#feedback").html("token valid as of " + thedate);
				
				//$("#list_holder").show();
				$("#logout_holder").show();
				
				list_timeout_id = setTimeout(function(){
					listMessages();
				}, 100);
			}
			return;
	}).fail(function (jqXHR, textStatus) {
		console.log(textStatus);
	});
}
var getToken = function() {
	$("#authorize_holder").hide();
	window.resizeTo(500, 500); 
	document.location.href = "get_token.php";
}
<?php if (isset($_GET["forced"])) { ?>
//document.location.href = "get_token.php";
getToken();
<?php } ?>

var token_timeout_id = setTimeout(function(){
	//refreshToken();
}, 100);
var list_timeout_id = false;
var progress_id = false;
var timerId = false;
<?php if (isset($_GET["logged_in"])) { ?>
list_timeout_id = setTimeout(function(){
	//alert("logged in list");
	//listMessages();
}, 500);
<?php } ?>

var thewidth = 320;
var theheight = 140;
					
window.resizeTo(thewidth, theheight); 
var theleft = screen.width - thewidth - 30;
var thetop = screen.height - theheight - 30;
window.moveTo(theleft, thetop);				
</script>
