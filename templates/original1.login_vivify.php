<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<style type="text/css">
input {
		font-family: 'Open Sans', sans-serif;
	}
	#labelEmail {
		color:#CCC;
	}
	#this_password_label {
		color:#CCC;
	}
	</style>
</head>

<body>

<div style="padding-top:5px">
    <label for="inputEmail" id="labelEmail" style="font-size:1em; cursor:text; position:relative; top:0px; left:55px">Email</label>
    <input type="text" class="form-control" id="inputEmail" name="inputEmail" onKeyPress="enterLogin(event)" required autocomplete="off" tabindex="1" style="width:210px; height:40px; font-size:1em; padding-bottom:0px; margin-bottom:0px; line-height: 40px; left:-50px" onblur="changeBack()">
</div>
<div style="margin-top:0px" id="password_holder">
    <label for="inputPassword" id="labelPassword" style="font-size:1em; cursor:text; position:relative; top:0px; left:55px">Password</label>
    <input type="password" class="form-control" id="inputPassword" name="inputPassword" onKeyPress="enterLogin(event)" required autocomplete="off" style="width:210px; height:40px; font-size:1em; padding-bottom:0px; margin-bottom:0px; line-height: 40px; left:-50px" onblur="changePasswordBack()">
</div>
                
</body>
<script language="javascript">
setTimeout(function() {
	(function ( $ ) {
	 
		$.fn.vivify = function() {
			var textbox_id = this.id;
			var label_id = textbox_id.replace("input", "label");
			var label_obj = $("#" + label_id);
			if (this.val != "") {
				label_obj.animate({top: "20px", fontSize: "0.58em"}, 250);
				//$('#inputEmail').focus();
			}
			this.click(function() {
				label_obj.animate({top: "20px", fontSize: "0.58em"}, 250);
			});
			label_obj.click(function() {
				label_obj.animate({top: "20px", fontSize: "0.58em"}, 250);
			});
			
			this.on('focus', function() {
				label_obj.animate({top: "20px", fontSize: "0.58em"}, 250);
				//$('label').css("", "top");
			});
			function changeBack() {
				var input_val = $('#inputEmail').val();
				if (input_val == "") {
					$('#labelEmail').animate({top: "35px", fontSize: "1em"}, 250);
				}
			};
			function changePasswordBack() {
				var input_password_val = $('#inputPassword').val();
				if (input_password_val == "") {
					$('#this_password_label').animate({top: "35px", fontSize: "1em"}, 250);
				}
			};
			return this;
		};
	 
	}( jQuery ));
}, 1000);
	</script>
</html>