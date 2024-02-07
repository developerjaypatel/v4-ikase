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
            <div class="dashboard_settlement col-md-5" id="attorneyfees_holder" style="margin-top:10px; border:0px solid yellow">
            </div>
            <div class="dashboard_settlement col-md-1"></div>
            <?php if (!$blnIPad) { ?>
            <div class="dashboard_settlement col-md-5" id="priorreferral_holder" style="margin-top:10px; border:0px solid white"></div>
            <?php } else { ?>
            <div class="dashboard_settlement col-md-10" id="priorreferral_holder" style="margin-top:10px; border:0px solid white"></div>
            <?php } ?>
            
        </div>
        <div class="container" style="border:0px solid red; margin:0px; padding:0px">
            <div id="firmcosts_holder" class="dashboard_settlement col-md-5" style="margin-top:10px; border:0px solid yellow">
            </div>
            <div class="dashboard_settlement col-md-1"></div>
            <div class="dashboard_settlement col-md-5" id="depofees_holder" style="margin-top:10px; border:0px solid white">
            </div>
        </div>
        <div class="container" style="border:0px solid red; margin:0px; padding:0px">
            <div class="dashboard_settlement col-md-13" id="settlement_notes_holder" style="margin-top:10px; border:0px solid yellow">
            </div>
            </div>
        </div>
    </div>
</div>