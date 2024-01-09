<?php
include("browser_detect.php");

if($blnMobile) {
	header("location:https://www.ikase.org/index_mobile.php");
}

if($_SERVER["HTTPS"]=="off") {
	header("location:https://www.ikase.org");
}
//include ("text_editor/ed/datacon.php");
//include("api/connection.php");
$version_number = 8;

$page_title = "References";
?>
<?php include("site_nav.php"); ?>
<style>
#form_holder div {
	margin-bottom:10px;
}
</style>
<!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron" style="border-bottom: 1px solid black;">
      <div class="container">
        <h1>iKase Legal Management Software :: References</h1>
        <p>iKase is used by many firms across the country.  We have enabled small firms to leverage the power of the web while growing their business, and we provided support to large firms who need the customization and employee-related functionality to manage the enterprise.</p>
      </div>
    </div>

    <div class="container">      
        <div>
       	  <p>Coming soon...</p>
        </div>
      <hr>
    </div> <!-- /container -->
    <?php include("site_footer.php"); ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="lib/jquery-1.7.2.min.js"></script>
    
    <script src="lib/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="lib/ie10-viewport-bug-workaround.js"></script>
    
    <script src="js/cookies.js"></script>
    <script src="js/utilities.js"></script>
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
			userLogin(e);
		}
		return;
	}
	function getWeather(city, state) {
		var url = 'weather_api.php?city=' + city + '&state=' + state;
		$.ajax({
            url:url,
            type:'GET',
            dataType:"text",
            data: "",
            success:function (data) {
				$("#weather").html(data);
				$("#weather").fadeIn();
            },
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				 console.log(errorThrown);
			} 
        });
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
					 return;
                }
				$('.alert-danger').text("Logout successful.").show();
				setTimeout(function() {
					 $('.alert-danger').fadeOut();
				 }, 1500);
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
					$("#this_email_label").hide();
					$("#button_holder").html("&nbsp;<br><span class='white_text'>A password reset link has been sent to your email.  Please check your inbox and follow the instructions.</span><div><button class='btn btn-lg btn-primary btn-block' onClick='document.location.href=\"https://www.ikase.org\"' style='width:210px'>Login</button></div>");
					emptyReminderBuffer(data.success.text);
					setTimeout(function() {
						//document.location.href = "https://www.ikase.org/";
					}, 5500);
				}
			}
		});
				
	}
	function userLogin(event) {
		event.preventDefault();
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
					writeCookie('sess_id', data.sess_id, 8*60*60);
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
                    document.location.href = href + "?session_id=" + data.session_id;
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
		<?php if (isset($_GET["logout"])) { ?>
		window.history.pushState('index', 'Welcome to iKase', '/index.php');
		logoutCurrentUser();
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
		
		var options = {
			enableHighAccuracy: true,
			timeout: 5000,
			maximumWait: 10000,     // max wait time for desired accuracy
			maximumAge: 0,          // disable cache
			desiredAccuracy: 30,    // meters
			fallbackToIP: true,     // fallback to IP if Geolocation fails or rejected
			addressLookup: true,    // requires Google API key if true
			timezone: true,         // requires Google API key if true
			//map: "map-canvas",      // interactive map element id (or options object)
			staticMap: true         // map image URL (boolean or options object)
		};
		geolocator.locate(options, function (err, location) {
			if (err) return console.log(err);
			
			var city = location.address.city;
			if (location.address.neighborhood!="") {
				city = location.address.neighborhood;
			}
			var state = location.address.state;
			
			getWeather(city, state);
			//console.log(location);
			/*
			current_lat = location.coords.latitude;
			current_long = location.coords.longitude;
			current_zip = location.address.postalCode;
			//document.getElementById("results").innerHTML = location;
			
			document.getElementById("lat").value = current_lat;
			document.getElementById("long").value = current_long;
			*/
			//getZipFromLatLong();
			
		});
	});
	
	var origin = originCookie();
	if (origin!="" && origin!="undefined"  && typeof origin != "undefined") {
		$(".alert-origin").html("You will be returned to your original page once you login successfully");
		$(".alert-origin").fadeIn();
	}
	
	function getZipFromLatLong() {
		var url = "api/getzip.php";
		var formData = new FormData();
		formData.append("lat", current_lat);
		formData.append("long", current_long);
		
		var r = new XMLHttpRequest();
		r.open("POST", url, true);
		r.onreadystatechange = function () {
			if (r.readyState != 4 || r.status != 200) {
				return;
			} else {
				data = r.responseText;
				var jdata = JSON.parse(data);
				console.log(jdata);
				/*
				document.getElementById("zip").value = jdata.postalCodes[0].postalCode;
				selectZip(jdata.postalCodes[0].postalCode, current_lat, current_long);
				*/
			}
		}
		r.send(formData);
	}
	geolocator.config({
		language: "en",
		google: {
			version: "3",
			key: "AIzaSyATlRmX2YtxkZc5FrUT9i74BZZGiesxkfU"
		}
	});
	</script>
  </body>
</html>
