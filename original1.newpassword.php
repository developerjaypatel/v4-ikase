<?php
if($_SERVER["HTTPS"]=="off") {
	header("location:https://v2.ikase.org/newpassword.php");
}

include("api/manage_session.php");
if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	//die(print_r($_SESSION));
	header("location:index.php?cusid=-1");
	die();
}
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
    <style>
	body, html {
	  height: 100%;
	  font-family: 'Open Sans', sans-serif;
	  color:white;
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
	</style>

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

<body onload="init()">
<div style="width:500px; margin-left:auto; margin-right:auto; text-align:left">
    <div class="form-signin-heading" style="color:white; font-size:2.2em; padding-bottom:50px; font-weight:lighter; margin-left:-35px">
        <p><i class="glyphicon glyphicon-briefcase"></i>&nbsp;iKase Password Reset</p>
    </div>
    
    <div id="center_div" class="center_div" style="display:; text-align:left; width:600px">
        <div style="float:right; text-align:left; font-size:0.8em">
            <span style="text-decoration:underline; font-size:1.5em">Requirements:</span>
            <br /><span id="min_length">At least 6 characters</span>
            <br /><span id="min_lowercase">At least 1 lowercase</span>
            <br /><span id="min_uppercase">At least 1 uppercase</span>
            <br /><span id="min_number">At least 1 number</span>
            <br /><span id="min_symbol">At least 1 symbol (~!@#$%^&amp;*)</span>
            <br /><br />Example:<br />Angels2!
        </div>
      <p><strong>You have been issued a temporary password. <br>
      Please enter a new Password</strong> below</p>
      <p>
        <div>
        	<input type="text" name="new_password" id="new_password" placeholder="password" onKeyUp="rankPassword()" />
            &nbsp;<span id="new_password_status"></span>
        </div>
        <div id="confirm_holder" style="padding-top:5px; padding-bottom:5px; visibility:hidden">
        	<input type="text" name="new_password2" id="new_password2" placeholder="repeat password" onKeyUp="comparePasswords()" />
        	&nbsp;<span id="confirm_password_status"></span>
        </div>
        <div>
	        <input type="button" id="ok_password" value="Submit" onClick="savePassword()" disabled />
        </div>
      </p>
    </div>
</div>
<script type="text/javascript" src="lib/jquery-1.7.2.min.js"></script>
<script language="javascript">
function savePassword() {
	if (document.getElementById("new_password").value == document.getElementById("new_password2").value) {
		var formValues = "table_name=user&table_id=<?php echo $_SESSION["user_plain_id"]; ?>&password=" + document.getElementById("new_password").value;
		var url = 'api/user/update';
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					
				}
				
				if (data.success==<?php echo $_SESSION["user_plain_id"]; ?>) {
					var href = "v<?php echo $version_number; ?>.php";
					document.location.href = href;
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				 
			} 
		});
	}
}
function comparePasswords() {
	document.getElementById("ok_password").disabled = true;
	var res = "<span style='background:red;color:white;padding:2px'>&times;</span>";
	if (document.getElementById("new_password").value == document.getElementById("new_password2").value) {
		res = "<span style='background:green;color:white;padding:2px'>&#10003;</span>";
		document.getElementById("ok_password").disabled = false;		
	}
	document.getElementById("confirm_password_status").innerHTML = res;
	
}
function rankPassword() {
	document.getElementById("ok_password").disabled = true;
	document.getElementById("confirm_holder").style.visibility = "hidden";
	
	var formValues = "password=" + document.getElementById("new_password").value;
	var url = 'api/rankpassword';
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				
			}
			
			if (!data.success) {
				var res = "<span style='background:red;color:white;padding:2px'>&times;</span>";
				
				var rank = data.rank;
				if (rank.length > 5) {
					document.getElementById("min_length").style.background = "green";
					document.getElementById("min_length").style.padding = "2px";
				} else {
					document.getElementById("min_length").style.background = "none";
				}
				if (rank.lowercase > 0) {
					document.getElementById("min_lowercase").style.background = "green";
					document.getElementById("min_lowercase").style.padding = "2px";
				} else {
					document.getElementById("min_lowercase").style.background = "none";
				}
				if (rank.uppercase > 0) {
					document.getElementById("min_uppercase").style.background = "green";
					document.getElementById("min_uppercase").style.padding = "2px";
				} else {
					document.getElementById("min_uppercase").style.background = "none";
				}
				if (rank.numbers > 0) {
					document.getElementById("min_number").style.background = "green";
					document.getElementById("min_number").style.padding = "2px";
				} else {
					document.getElementById("min_number").style.background = "none";
				}
				if (rank.symbols > 0) {
					document.getElementById("min_symbol").style.background = "green";
					document.getElementById("min_symbol").style.padding = "2px";
				} else {
					document.getElementById("min_symbol").style.background = "none";
				}
			} else {
				var res = "<span style='background:green;color:white;padding:2px'>&#10003;</span>";
				document.getElementById("confirm_holder").style.visibility = "visible";
			}
			document.getElementById("new_password_status").innerHTML = res;
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			 
		} 
	});
}
var init = function() {
	document.getElementById("new_password").focus();
}
</script>
</body>
</html>