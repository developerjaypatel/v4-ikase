<?php
$version_number = 8;
?>
<div style="width:500px; margin-left:auto; margin-right:auto; text-align:left; color:white;  font-family: 'Open Sans', sans-serif;">
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
      <p><strong>Please enter a new Password below</strong></p>
      <p>
        <div>
        	<input type="text" name="new_password" id="new_password" placeholder="password" />
            &nbsp;<span id="new_password_status"></span>
        </div>
        <div id="confirm_holder" style="padding-top:5px; padding-bottom:5px; visibility:hidden">
        	<input type="text" name="new_password2" id="new_password2" placeholder="repeat password" />
        	&nbsp;<span id="confirm_password_status"></span>
        </div>
        <div>
        	<input id="table_name" name="table_name" type="hidden" value="user" />
	        <input id="table_id" name="table_id" type="hidden" value="<%= user_id %>" />
	        <input type="button" id="ok_password" value="Submit" onClick="savePassword()" disabled />
        </div>
      </p>
    </div>
</div>
<!--
<div style="background:url(img/glass_edit.png) left top; padding:5px; width:400px; margin-left:auto; margin-right:auto; margin-top:100px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
	<div style="float:right">
    	<img src="../img/ikase_logo_login_white.png" width="77" height="32" alt="iKase">
    </div>
    <div class="password" id="password_panel">
        <form id="password_form" parsley-validate>
        <input id="table_name" name="table_name" type="hidden" value="user" />
        <input id="table_id" name="table_id" type="hidden" value="<%= user_id %>" />
        <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
            <span style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;">
                <span id="panel_title">Password Reset</span>
            </span>
        </div>
        <div class="gridster password" id="gridster_password" style="display:">
        	
            <div style="width:250px">
                <div style="padding-top:5px">
                    <input type="text" class="form-control input-sm password_field" placeholder="Password" id="inputPassword" name="inputPassword" required autocomplete="off" tabindex="1" style="width:210px">
                </div>
                <div style="padding-top:5px; border:0px solid red; width:100%" id="password_holder">
                	<div style="display:inline-block">
                    	<input type="password" class="form-control input-sm password_field" placeholder="Re-enter Password" id="inputPasswordTwice" name="inputPasswordTwice" required autofocus autocomplete="off" tabindex="2" style="width:210px">
                    </div>
                    <div style="display:inline-block">
                    	<span id="password_match"></span>
                    </div>
                </div>
               <div id="button_holder" style="padding-top:5px; padding-bottom:4px">
                    <button id="reset_password" class="btn btn-lg btn-primary btn-block" style="width:210px" disabled>Reset Password <?php if($_SERVER["HTTPS"]!="off") { ?>
                    &nbsp;&nbsp;<img src="img/secure_login.png" width="16" height="15" alt="Secure Login">
    <?php } ?>
                </button>
                </div>
                <div class="alert alert-error" style="margin-top:10px;display:none;"></div>
                <div class="alert-origin small_text" style="margin-top:90px;display:none;"></div>
            </div>
            
        </div>
        </form>
</div>
</div>
-->