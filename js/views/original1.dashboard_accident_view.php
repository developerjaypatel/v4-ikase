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
            <div class="dashboard_accident col-md-5" id="accident_holder" style="margin-top:10px; border:0px solid yellow">
            </div>
            <div class="dashboard_accident col-md-1"></div>
            <?php if (!$blnIPad) { ?>
            <div class="dashboard_accident col-md-5" id="rental_holder" style="margin-top:10px; border:0px solid white">
            </div>
            <?php } else { ?>
            <div class="dashboard_accident col-md-10" id="rental_holder" style="margin-top:10px; border:0px solid white">
            </div>
            <?php } ?>
            
        </div>
        <div class="container" style="border:1px solid red; margin:0px; padding:0px">
            <div class="dashboard_accident col-md-10" id="property_damage_holder" style="margin-top:10px; border:1px solid yellow"></div>
        </div>
        <div class="container" style="border:0px solid red; margin:0px; padding:0px">
            <div class="dashboard_accident col-md-5" id="picture_holder" style="margin-top:10px; border:0px solid yellow"></div>
        </div>
    </div>    
</div>
<div>&nbsp;</div>
<div id="dashboard_accident_all_done"></div>
<script language="javascript">
$( "#dashboard_accident_all_done" ).trigger( "click" );
</script>