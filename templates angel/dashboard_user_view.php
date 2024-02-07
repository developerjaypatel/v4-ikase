<?php
include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$blnIPad = isPad();

?>
<div class="active fade in glass_header_no_padding">
	<div style="text-align:left; margin-top:13px;">
         <div style="z-index:2356">
	        <span class="alert alert-success" style="display:none; float:right; height:35px; width:300px;font-size:14px; z-index:3356; margin-top:-45px;">Saved</span>
        </div>
	</div>
    <div style="">
        <div class="container" style="border:0px solid red; margin:0px; padding:0px">
            <div class="dashboard_user col-md-7" id="info_holder" style="margin-top:10px; border:0px solid yellow">
            </div>
            <div class="dashboard_user col-md-1"></div>
            <?php if (!$blnIPad) { ?>
            <div class="dashboard_user col-md-4" id="email_holder" style="margin-top:10px; border:0px solid white; margin-top:25px">
            </div>
            <?php } else { ?>
            <div class="dashboard_user col-md-10" id="email_holder" style="margin-top:10px; border:0px solid white">
            </div>
            <?php } ?>
            
         </div>
         <div class="container" style="border:0px solid red; margin:0px; padding:0px">
            <div class="dashboard_user col-md-5" id="hours_holder" style="margin-top:10px; margin-left:15px; border:0px solid yellow; background:none">
            </div>
            <div class="dashboard_user col-md-1"></div>
            <?php if (!$blnIPad) { ?>
            <div class="dashboard_user col-md-5" id="identification_holder" style="margin-top:10px; margin-left:-15px; border:0px solid white">
            </div>
            <?php } else { ?>
            <div class="dashboard_user col-md-10" id="identification_holder" style="margin-top:10px; border:0px solid white">
            </div>
            <?php } ?>
         </div>
    </div>
    <div>&nbsp;</div>
</div>
<div>&nbsp;</div>