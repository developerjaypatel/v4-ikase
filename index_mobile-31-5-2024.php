<?php

//die("mobile");

if($_SERVER["HTTPS"]=="off") {
	header("location:https://v4.ikase.org");
}
//include ("text_editor/ed/datacon.php");
//include("api/connection.php");
$version_number = 8;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=0.789, maximum-scale=1.0, user-scalable=0">
    <meta name="description" content="">
    <meta name="theme-color" content="#428bca">
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
    <style>
	body, html {
	  height: 100%;
	  font-family: 'Open Sans', sans-serif;
	}
	.container {
		position:absolute;
		top: 0%;
		left: 50%;
		width: 100%;
		height: 100%;
		z-index:99;
	}
	.login_please {
		color:white; 
		font-size:1.5em;
	}
	#this_email_label {
		color:#CCC;
	}
	#this_password_label {
		color:#CCC;
	}
	input[type=text], input[type=url], input[type=email], input[type=password], input[type=tel], input[type=button] {
	  -webkit-appearance: none; -moz-appearance: none;
	  display: block;
	  margin: 0;
	  width: 95%; height: 65px;
	  line-height: 55px; font-size: 2.1em;
	  border: 1px solid #bbb;
	  padding-left:2px;
	  font-family: 'Open Sans', sans-serif;
	}
	input:placeholder-shown {
		padding:5px;
		font-family: 'Open Sans', sans-serif;
	}
	</style>

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container">
    	<div style="background:url(img/translucent_med.png) repeat-y top left; padding-left:0px; padding-top:20px; height:100%; width:100%; margin-left:-50.5%; border:0px solid white;">
            <div class="form-signin-heading" style="color:white; font-size:2.9em; font-weight:lighter; margin-left:14px">
                <p><i class="glyphicon glyphicon-briefcase"></i>&nbsp;Welcome to iKase</p>
                <p style="color:red; display:none; font-size:1.2em">DO NOT USE RIGHT NOW</p>
        </div>
            <!--<div>
            	<span class="login_please" style="margin-left:11px">
	                Please login
                </span>
            </div>
            -->
            <div style="width:395px; margin-left:11px; margin-top:0px">
                <div>
                	<!--<label for="inputEmail" id="this_email_label" style="font-size:2.1em; cursor:text; position:relative; top:25px; left:5px">Email</label>-->
                    <input type="text" class="form-control" id="inputEmail" name="inputEmail" onKeyPress="enterLogin(event)" required autocomplete="off" tabindex="1" style="padding-bottom:0px; margin-bottom:0px" onblur="changeBack()" placeholder="Username">
                </div>
                <div style="margin-top:5px" id="password_holder">
                <!--<label for="inputPassword" id="this_password_label" style="font-size:2.1em; cursor:text; position:relative; top:45px; left:5px">Password</label>-->
                <input type="password" class="form-control" id="inputPassword" name="inputPassword" onKeyPress="enterLogin(event)" required autocomplete="off" style="padding-bottom:0px; margin-bottom:0px;" onblur="changePasswordBack()" placeholder="Password">
                </div>
               <div id="button_holder" style="margin-top:25px; width:375px">
                    <button class="btn btn-lg btn-primary btn-block" onClick="userLogin()" style="font-size:2.1em;">Sign in <?php if($_SERVER["HTTPS"]!="off") { ?>
            		&nbsp;&nbsp;<img src="img/secure_login.png" width="25" height="25" alt="Secure Login">
<?php } ?>
				</button>
                <div class="alert" role="alert" id="capsLockWarning" style="display:none; margin-top:5px; text-align:left;"><i class="glyphicon glyphicon-warning-sign"></i>&nbsp;<span style="font-weight:bold">Caps Lock On!</span></div>
                </div>
                <div style="padding-top:30px">
                	<a id="forgot_password" class="small_text" href="javascript:forgotPassword()" style="font-size:1em">Forgot Password?</a>
                </div>
                <div style="padding-top:125px" class="white_text">
                <span style='font-size:1.6em'><?php echo date('h:i A'); ?></span><br>
				<?php echo date('l F jS'); ?>
                <div class="small_text" style="margin-top:3px; float:right; margin-right:23px;">v 0.8a.10012015</div>
                <div style="padding-top:30px">
                <div class="alert alert-danger" style="margin-top:10px;display:none; color:black"></div>
                <div class="alert-origin small_text" style="margin-top:90px;display:none; background:#F60;">We will be down for maintenance this evening 07/09/2015 8-9pm.<br>&nbsp;<p>We appologize for any inconvenience this may cause.</p></div>
                
            </div>
		</div>
    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="lib/jquery-1.7.2.min.js"></script>
    <script src="lib/jquery.fs.wallpaper.js"></script>
    
    <script src="js/cookies.js"></script>
    <script src="js/utilities_mobile.js"></script>
    <script language="javascript">
	$('#inputPassword').keypress(function(e) {
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
	/*
	if ($('#inputEmail').val != "") {
		$('#this_email_label').animate({top: "25px", fontSize: "1em"}, 250);
		//$('#inputEmail').focus();
	}
	$('#inputEmail, #this_email_label').click(function() {
		$('#this_email_label').animate({top: "25px", fontSize: "1em"}, 250);
	});
	$('#inputPassword, #this_password_label').click(function() {
		//$('#this_password_label').animate({top: "25px", fontSize: "1em"}, 250);
		$('#inputPassword').focus();
		//$('label').css("", "top");
	});
	$('#inputPassword').on('focus', function() {
		//$('#this_password_label').css("top", "20px");
		$('#this_password_label').animate({top: "25px", fontSize: "1em"}, 250);
		$("#password_holder").css("margin-top", "-20px");
		//$('#this_password_label').animate('{font-size: "0.58em"}', 500);
		//$('#this_password_label').css("font-size", "0.58em");
		//$('label').css("", "top");
	});
	$('#inputEmail').on('focus', function() {
		$('#this_email_label').animate({top: "25px", fontSize: "1em"}, 250);
		//$('label').css("", "top");
	});
	*/
	var field_value = $('#inputPassword').val();
	if (field_value != "") {
		$('#this_password_label').animate({top: "25px", fontSize: "1em"}, 250);
	}
	function changeBack() {
		var input_val = $('#inputEmail').val();
		if (input_val == "") {
			$('#this_email_label').animate({top: "55px", fontSize: "2.1em"}, 250);
			//$('#this_email_label').css("top", "35px");
			//$('#this_email_label').css("font-size", "1em");
			//$('label').css("", "top");
		}
	};
	function changePasswordBack() {
		var input_password_val = $('#inputPassword').val();
		if (input_password_val == "") {
			$('#this_password_label').animate({top: "55px", fontSize: "2.1em"}, 250);
			//$('#this_password_label').css("top", "35px");
			//$('#this_password_label').css("font-size", "1em");
			//$('label').css("", "top");
		}
	};
	</script>
    <script language="javascript">
	var wallpaper_image = "img/office.jpg";
	var rand = Math.floor((Math.random() * 2) + 1);
	if (rand > 1) {
		wallpaper_image = "img/library.jpg";
	}
	$("body").wallpaper({
		source: wallpaper_image
	});
	var enterLogin = function(e) {
		if(window.event) {
			// IE
			keynum = e.keyCode;
		}
		else if(e.which) {
			// Netscape/Firefox/Opera
			keynum = e.which;
		}
		if(keynum==13) {
			userLogin();
		}
		return;
	}
	function logoutCurrentUser() {
		var url = 'api/logout';
		$.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: "",
            success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
                    $('.alert-danger').text("Logout failed.  Please try again.").show();
					setTimeout(function() {
						 $('.alert-danger').fadeOut();
					 }, 1500);
                }
            },
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				 $('.alert-danger').text("Logout failed.").show();
				 setTimeout(function() {
					 $('.alert-danger').fadeOut();
				 }, 1500);
			} 
        });
	}
	function forgotPassword() {
		//hide password
		$("#password_holder").fadeOut(function(){
			//change button
			$("#inputEmail").focus();
			$(".btn-primary").html("Send Reset Link");
			$(".login_please").html("Please enter your email address");
			$("#forgot_password").fadeOut();
		});
		
	}
	function sendReset() {
		var url = 'api/request/reset';

        var formValues = {
            email: $('#inputEmail').val()
        };
		$("#button_holder").html("<i class='icon-spin4 animate-spin' style='font-size:2em; color:white'></i>");
		$.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					$(".alert-danger").html(data.error.text);
				} else {
					$("#inputEmail").hide();
					$(".login_please").hide();
					$("#button_holder").html("&nbsp;<br><span class='white_text'>A password reset link has been sent to your email.  Please check your inbox and follow the instructions.</span>");
					emptyBuffer(data.success.text);
					setTimeout(function() {
						//document.location.href = "https://v4.ikase.org/";
					}, 5500);
				}
			}
		});
				
	}
	function userLogin() {
		if ($(".btn-primary").html()=="Send Reset Link") {
			sendReset();
			return;
		}
		var url = 'api/login';
		//if ($("#remember_me").is(":checked")) {
			writeCookie('user_name', $('#inputEmail').val(), 24*60*60*1000);
		//}
        var formValues = {
            email: $('#inputEmail').val(),
            password: $('#inputPassword').val()
        };
		$("#button_holder").html("<i class='icon-spin4 animate-spin' style='font-size:2em; color:white'></i>");
		$.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
                    $('.alert-danger').text("Login failed.  Please try again.").show();
					$("#button_holder").html('<button class="btn btn-lg btn-primary btn-block" onClick="userLogin()">Sign in</button>');
					$('#inputPassword').val("");
					$('#inputPassword').focus();
					
					if ($("#inputEmail").val()!="") {
						//$('#inputPassword').focus();
						//$('#inputPassword').select();
					} else {
						//$('#inputEmail').focus();
						//$('#inputEmail').select();
					}
					logoutCurrentUser();
					setTimeout(function() {
						 $('.alert-danger').fadeOut();
					 }, 1500);
                }
                else { // If not, send them back to the home page
					//write cookie with session id
					$('#logged_in').val(data.sess_id);
					writeCookie('sess_id', data.sess_id, 60);
					writeCookie('logged_in_as', data.user_name, 60);
					
					var origin = originCookie();
					var href = "mobilev2.php";
					if (typeof origin != "undefined") {
						if (origin=="undefined") {
							origin = "";
						}
						if (origin!="") {
							href += origin;
						}
					}
                    document.location.href = href;
                }
            },
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				 $('.alert-danger').text("Logon failed.").show();
				 //$('#inputPassword').focus();
				 //$('#inputPassword').select();
				 logoutCurrentUser();
				 setTimeout(function() {
					 $('.alert-danger').fadeOut();
				 }, 1500);
			} 
        });
	}
	rememberCookie();
	$( document ).ready(function() {
		setTimeout(function() {
			$('#inputEmail').focus();
			if ($("#inputEmail").val()!="") {
				$('#inputEmail').focus();
				changePasswordBack();
			}
		}, 50);
	});
	
	var origin = originCookie();
	if (origin!="" && origin!="undefined"  && typeof origin != "undefined") {
		$(".alert-origin").html("You will be returned to your original page once you login successfully");
		$(".alert-origin").fadeIn();
	}
	</script>
    
  </body>
</html>