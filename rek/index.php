<?php
include("../browser_detect.php");
//include("manage_session.php");

if($blnMobile) {
	//header("location:https://www.ikase.org/rek/manage");
}

if($_SERVER["HTTPS"]=="off") {
	//header("location:https://www.ikase.org/rek/manage");
}
include ("../text_editor/ed/datacon_rek.php");
//include("../../api/connection.php");
$version_number = 8;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    
    <meta name="robots" content="noindex, nofollow">
    

    <title>REK :: Patient Management Software</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="../css/bootstrap.3.0.3.min.css">

    <!-- Custom styles for this template -->
    <link href="../css/signin.css" rel="stylesheet">
    
    <link rel='stylesheet' type='text/css' href='../../css/jquery.fs.wallpaper.css' />
    <link rel='stylesheet' type='text/css' href='../../css/styles.css' />
    <link href="../../fonts/fontello-a1b266d9/css/fontawesome.css" rel="stylesheet" />
    <link href="../../fonts/fontello-a1b266d9/css/fontawesome-codes.css" rel="stylesheet" />
    <link href="../../fonts/fontello-a1b266d9/css/fontawesome-embedded.css" rel="stylesheet" />
    <link href="../../fonts/fontello-a1b266d9/css/fontawesome-ie7-codes.css" rel="stylesheet" />
    <link href="../../fonts/fontello-a1b266d9/css/fontawesome-ie7.css" rel="stylesheet" />
    <link href="../../fonts/fontello-a1b266d9/css/animation.css" rel="stylesheet" />
    <!--fonts-->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
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
		color:white;
	}
	.container {
		position:absolute;
		top: 0%;
		left: 10%;
		width: 100%;
		height: 100%;
		z-index:98;
	}
	.login_please {
		color:white; 
		font-size:1.1em;
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
		position:absolute;
		bottom:20px;
		left: 40%;
		width:50%;
		z-index:99;
		color:white;
		background: #333;
		font-size:1em;
		font-weight:100;
		padding:5px;
	}
	</style>

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/geolocator/2.1.1/geolocator.js"></script>
  </head>

  <body>
    <div class="container">
    	<div style="background:url(../../img/translucent.png) repeat-y top left; padding-left:50px; padding-top:50px; height:100%">
            <div class="form-signin-heading" style="color:white; font-size:2.2em; padding-bottom:50px; font-weight:lighter; margin-left:-35px">
                <p><i class="glyphicon glyphicon-briefcase"></i>&nbsp;Welcome to REK</p>
                <p style="color:red; display:none; font-size:1.2em">DO NOT USE RIGHT NOW</p>
        	</div>
            <div>
            	<span class="login_please">
	                Please login
                </span>
            </div>
            <div style="width:210px">
                <div style="padding-top:5px">
                	<label for="inputEmail" id="this_email_label" style="font-size:1em; cursor:text; position:relative; top:35px; left:5px">Email</label>
                    <input type="text" class="form-control" id="inputEmail" name="inputEmail" onKeyPress="enterLogin(event)" required autocomplete="off" tabindex="1" style="width:210px; height:40px; font-size:1em; padding-bottom:0px; margin-bottom:0px; line-height: 40px" onblur="changeBack()">
                </div>
                <div style="margin-top:-10px" id="password_holder">
                    <label for="inputPassword" id="this_password_label" style="font-size:1em; cursor:text; position:relative; top:35px; left:5px">Password</label>
                    <input type="password" class="form-control" id="inputPassword" name="inputPassword" onKeyPress="enterLogin(event)" required autocomplete="off" tabindex="2" style="width:210px; height:40px; font-size:1em; padding-bottom:0px; margin-bottom:0px; line-height: 40px" onblur="changePasswordBack()">
                    <div style="position:relative; left:220px; top:-27px; font-size:1.2em; width:20px; display:none" id="eye_holder">
                        <i class="glyphicon glyphicon-eye-open" style="color:white; cursor:pointer" id="show_password" title="Show Password"></i>
                    </div>
                </div>
               <div id="button_holder" style="margin-top:15px">
                    <button class="btn btn-lg btn-primary btn-block" onClick="userLogin()" style="width:210px">Sign in <?php if($_SERVER["HTTPS"]!="off") { ?>
            		&nbsp;&nbsp;<img src="../../img/secure_login.png" width="16" height="15" alt="Secure Login">
<?php } ?>
				</button>
                <div class="alert" role="alert" id="capsLockWarning" style="display:none; margin-top:5px; text-align:left;"><i class="glyphicon glyphicon-warning-sign"></i>&nbsp;<span style="font-weight:bold">Caps Lock On!</span></div>
                </div>
                <div style="padding-top:30px">
                	<a id="forgot_password" class="small_text" href="javascript:forgotPassword()">Forgot Password?</a>
                </div>
                <div style="padding-top:30px" class="white_text">
                <span style='font-size:1.6em'><?php echo date('h:i A'); ?></span><br>
				<?php echo date('l F jS'); ?>
                <div id="weather" style="display:none;border-top: 1px solid white; margin-top: 15px;"></div>
                <div style="padding-top:30px">
                    <div class="alert alert-danger" style="margin-top:10px;display:none; color:black"></div>
                    <div class="alert-origin small_text" style="margin-top:90px;display:none; background:#F60;">We will be down for maintenance this evening 07/09/2015 8-9pm.<br>&nbsp;<p>We appologize for any inconvenience this may cause.</p></div>
                    <div class="small_text" style="margin-top:10px">Version NEO 1.0</div>
                </div>
            </div>
            </div>
		</div>
    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="../../lib/jquery-1.7.2.min.js"></script>
    <script src="../../lib/jquery.fs.wallpaper.js"></script>
    
    <script src="../../js/cookies.js"></script>
    <script src="../../js/utilities.js"></script>
    <script language="javascript">
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
	if ($('#inputEmail').val != "") {
		$('#this_email_label').animate({top: "20px", fontSize: "0.58em"}, 250);
		//$('#inputEmail').focus();
	}
	$('#inputEmail, #this_email_label').click(function() {
		$('#this_email_label').animate({top: "20px", fontSize: "0.58em"}, 250);
	});
	$('#inputPassword, #this_password_label').click(function() {
		$('#this_password_label').animate({top: "20px", fontSize: "0.58em"}, 250);
		$('#inputPassword').focus();
		//$('label').css("", "top");
	});
	$('#inputPassword').on('focus', function() {
		//$('#this_password_label').css("top", "20px");
		$('#this_password_label').animate({top: "20px", fontSize: "0.58em"}, 250);
		$("#eye_holder").fadeIn();
		//$('#this_password_label').animate('{font-size: "0.58em"}', 500);
		//$('#this_password_label').css("font-size", "0.58em");
		//$('label').css("", "top");
	});
	$('#inputEmail').on('focus', function() {
		$('#this_email_label').animate({top: "20px", fontSize: "0.58em"}, 250);
		//$('label').css("", "top");
	});
	$('#show_password').on("mousedown", function() {
		document.getElementById("inputPassword").type = "text";
		$('#show_password').css("color", "black");
	});
	$('#show_password').on("mouseup", function() {
		document.getElementById("inputPassword").type = "password";
		$('#show_password').css("color", "white");
	});
	function changeBack() {
		var input_val = $('#inputEmail').val();
		if (input_val == "") {
			$('#this_email_label').animate({top: "35px", fontSize: "1em"}, 250);
			//$('#this_email_label').css("top", "35px");
			//$('#this_email_label').css("font-size", "1em");
			//$('label').css("", "top");
		}
	};
	function changePasswordBack() {
		var input_password_val = $('#inputPassword').val();
		if (input_password_val == "") {
			$('#this_password_label').animate({top: "35px", fontSize: "1em"}, 250);
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
	
	function userLogin() {
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
					//logoutCurrentUser();
					setTimeout(function() {
						 $('.alert-danger').fadeOut();
					 }, 1500);
                }
                else { // If not, send them back to the home page
					//write cookie with session id
					$('#logged_in').val(data.sess_id);
					writeCookie('sess_id', data.sess_id, 8*60*60);
					writeCookie('logged_in_as', data.user_name, 60);
					
					var origin = originCookie();
					
					if ($_SESSION['user_customer_id']==-1 && $_SESSION['user_role']=="owner") {
						url = "manage/customers/";
						//if (isset($_GET["session_id"])) {
							url += "index.php?session_id=" + data.session_id;
						//}
						//header($url);
						//die();
					} else {
						url = "v1.php?session_id=" + data.session_id;
					}
                    document.location.href = url;
                }
            },
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				 $('.alert-danger').text("Logon failed.").show();
				 //$('#inputPassword').focus();
				 //$('#inputPassword').select();
				 //logoutCurrentUser();
				 setTimeout(function() {
					 $('.alert-danger').fadeOut();
				 }, 1500);
			} 
        });
	}
	rememberCookie();
	
	$( document ).ready(function() {
		<?php if (isset($_GET["logout"])) { ?>
		//window.history.pushState('index', 'Welcome to REK', '/index.php');
		//logoutCurrentUser();
		<?php } ?>
		
		setTimeout(function() {
			$('#inputEmail').focus();
			if ($("#inputEmail").val()!="") {
				$('#inputEmail').focus();
				changePasswordBack();
			}
		}, 50);
		setTimeout(function() {
			//$("#announcements").fadeIn("slow");
		}, 1500);
	});
	</script>
    <div id="quote_holder">
	<?php $filename = "https://www.matrixdocuments.com/dis/pws/quicks/orders/quote.php?remote=";
    $quote = file_get_contents($filename);
    echo str_replace("-", "<br /><br />", $quote); ?>
    </div>
  </body>
</html>