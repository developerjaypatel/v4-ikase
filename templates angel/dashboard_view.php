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
    	<!--<span style="color:red; background:white">DO NOT SAVE - NOT READY FOR SAVING YET</span>-->
        <div class="container" style="border:0px solid red; margin:0px; padding:0px">
        <!--blue-->
            <div class="applicant col-md-5" id="employer_holder" style="margin-top:10px; border:0px solid yellow">
            </div>
            <div class="parties col-md-1"></div>
            <!--green-->
            <?php if (!$blnIPad) { ?>
            <div class="parties col-md-5" id="carrier_holder" style="margin-top:10px; border:0px solid white">
            </div>
            <?php } else { ?>
            <div class="parties col-md-10" id="carrier_holder" style="margin-top:10px; border:0px solid white">
            </div>
            <?php } ?>
            
         </div>
       <div class="container" style="border:0px solid red; margin:0px; padding:0px">
            <!--other color-->
            <div class="parties col-md-5" id="defense_holder" style="margin-top:10px;">
            </div>
            <div class="parties col-md-1"></div>
            <!--other color-->
            <?php if (!$blnIPad) { ?>
            <div class="parties col-md-5" id="injury_holder" style="margin-top:10px;">
            </div>
            <?php } else { ?>
            <div class="parties col-md-10" id="injury_holder" style="margin-top:10px;">
            </div>
            <?php } ?>
        </div>
        <div class="container" style="border:0px solid red; margin:0px; padding:0px">
            <!--other color
            <div class="parties col-md-5" id="venue_holder" style="margin-top:10px;">
            </div>
            <div class="parties col-md-1"></div>
            <!--other color
            <div class="parties col-md-5" id="empty_dashboard_holder" style="margin-top:10px;">
            </div>-->
        </div>
    </div>
    <div>&nbsp;</div>
</div>
<div>&nbsp;</div>