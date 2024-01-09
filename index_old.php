<?php
if($_SERVER["HTTPS"]=="off") {
	header("location:https://v2.ikase.org");
}
include ("text_editor/ed/datacon.php");
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

    <title>:: iKase Legal Case Management Software</title>

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
		font-size:1.1em;
	}
	input {
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
    	<div style="background:url(img/translucent.png) repeat-y top left; padding-left:50px; padding-top:100px; height:100%">
            <div class="form-signin-heading" style="color:white; font-size:2.2em; padding-bottom:50px; font-weight:lighter; margin-left:-35px">
                <p><i class="glyphicon glyphicon-briefcase"></i>&nbsp;Welcome to iKase</p>
                <p style="color:red; display:none; font-size:1.2em">DO NOT USE RIGHT NOW</p>
        </div>
            <div>
            	<span class="login_please">
	                Please login
                </span>
            </div>
            <div style="width:210px">
                <div style="padding-top:5px">
                    <input type="text" class="form-control input-sm" placeholder="Email address" id="inputEmail" name="inputEmail" onKeyPress="enterLogin(event)" required autocomplete="off" tabindex="1" style="width:210px">
                </div>
                <div style="padding-top:5px" id="password_holder">
                <input type="password" class="form-control input-sm" placeholder="Password" id="inputPassword" name="inputPassword" onKeyPress="enterLogin(event)" required autofocus autocomplete="off" tabindex="2" style="width:210px">
                </div>
               <div id="button_holder" style="padding-top:5px">
                    <button class="btn btn-lg btn-primary btn-block" onClick="userLogin()" style="width:210px">Sign in <?php if($_SERVER["HTTPS"]!="off") { ?>
            		&nbsp;&nbsp;<img src="img/secure_login.png" width="16" height="15" alt="Secure Login">
<?php } ?>
				</button>
                </div>
                <div style="padding-top:30px">
                	<a id="forgot_password" class="small_text" href="javascript:forgotPassword()">Forgot Password?</a>
                </div>
                <div class="alert alert-error" style="margin-top:10px;display:none;"></div>
                <div class="alert-origin small_text" style="margin-top:90px;display:none; background:#F60;">We will be down for maintenance this evening 07/09/2015 8-9pm.<br>&nbsp;<p>We appologize for any inconvenience this may cause.</p></div>
                <div class="small_text" style="margin-top:100px">v 0.8a.10012015</div>
            </div>
		</div>
    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="lib/jquery-1.7.2.min.js"></script>
    <script src="lib/jquery.fs.wallpaper.js"></script>
    
    <script src="js/cookies.js"></script>
    <script src="js/utilities.js"></script>
    
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
                    $('.alert-error').text("Logout failed.  Please try again.").show();
					setTimeout(function() {
						 $('.alert-error').fadeOut();
					 }, 1500);
                }
            },
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				 $('.alert-error').text("Logout failed.").show();
				 setTimeout(function() {
					 $('.alert-error').fadeOut();
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
					$(".alert-error").html(data.error.text);
				} else {
					$("#inputEmail").hide();
					$(".login_please").hide();
					$("#button_holder").html("&nbsp;<br><span class='white_text'>A password reset link has been sent to your email.  Please check your inbox and follow the instructions.</span>");
					emptyBuffer(data.success.text);
					setTimeout(function() {
						//document.location.href = "https://v2.ikase.org/";
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
                    $('.alert-error').text("Login failed.  Please try again.").show();
					$("#button_holder").html('<button class="btn btn-lg btn-primary btn-block" onClick="userLogin()">Sign in</button>');
					if ($("#inputEmail").val()!="") {
						$('#inputPassword').focus();
						$('#inputPassword').select();
					} else {
						$('#inputEmail').focus();
						$('#inputEmail').select();
					}
					logoutCurrentUser();
					setTimeout(function() {
						 $('.alert-error').fadeOut();
					 }, 1500);
                }
                else { // If not, send them back to the home page
					//write cookie with session id
					$('#logged_in').val(data.sess_id);
					writeCookie('sess_id', data.sess_id, 60);
					writeCookie('logged_in_as', data.user_name, 60);
					
					var origin = originCookie();
					var href = "v<?php echo $version_number; ?>.php";
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
				 $('.alert-error').text("Logon failed.").show();
				 $('#inputPassword').focus();
				 $('#inputPassword').select();
				 logoutCurrentUser();
				 setTimeout(function() {
					 $('.alert-error').fadeOut();
				 }, 1500);
			} 
        });
	}
	rememberCookie();
	$( document ).ready(function() {
		setTimeout(function() {
			$('#inputEmail').focus();
			if ($("#inputEmail").val()!="") {
				$('#inputPassword').focus();
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