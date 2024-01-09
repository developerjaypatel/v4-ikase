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
		<div style="float:right; cursor:pointer; color:white; margin-top:10px; margin-right:20px" class="save_accident_link" id="save_accident">Save</div>
            <div class="dashboard_accident col-md-10" id="accident_holder" style="margin-top:10px; border:0px solid yellow">
            </div>
        </div>  
		<div>
			<div class="col-md-10">
			<div class="col-md-6" style="font-size:1.9em; color:white; margin-left:-15px">Plaintiff</div><div class="col-md-5" style="font-size:1.9em; color:white; margin-left:-35px">Defendant</div>
			</div>
		</div>
		<br/>
		<form method="post" action="" id="accident_both">
			<div class="container" style="border:0px solid red; margin:0px; padding:0px">
                <div style="border:1px solid green; display:inline-block; margin-right:30px">
                    <div style="float:right; cursor:pointer; color:white; margin-top:10px" class="save_accident_link" id="save_defendant">Save</div>
                    <div style="cursor:pointer; color:white; margin-top:10px" id="back_to_top">Top</div>
                </div>
				<div class="dashboard_accident col-md-5" id="car_holder" style="margin-top:10px; border:0px solid white">
				</div>
				<div class="dashboard_accident col-md-1">
                	<div style="cursor:pointer; color:white; margin-top:10px; margin-right:20px" class="save_accident_link" id="save_plaitiff">Save</div>
                </div>
				<div class="dashboard_accident col-md-5 defendant" id="defendant_car_holder" style="margin-top:10px; border:0px solid white"></div>
			</div>
			
			<div class="container" style="border:0px solid red; margin:0px; padding:0px">
				<div style="float:right; cursor:pointer; color:white; margin-top:10px; margin-right:20px" class="save_accident_link" id="save_defendant">Save</div>
            	<div style="cursor:pointer; color:white; margin-top:10px; margin-right:10px" id="back_to_top">Top</div>
				<div class="dashboard_accident col-md-5" id="rental_holder" style="margin-top:10px; border:0px solid yellow"></div>
				<div class="dashboard_accident col-md-1"><div style="cursor:pointer; color:white; margin-top:10px; margin-right:20px" class="save_accident_link" id="save_plaitiff">Save</div></div>
				<div class="dashboard_accident col-md-5 defendant" id="defendant_rental_holder" style="margin-top:10px; border:0px solid yellow"></div>
			</div>
			<div class="container" style="border:0px solid red; margin:0px; padding:0px">
			<div style="float:right; cursor:pointer; color:white; margin-top:10px; margin-right:20px" class="save_accident_link" id="save_defendant">Save</div>
				<div class="dashboard_accident col-md-5" id="property_damage_holder" style="margin-top:10px; border:0px solid yellow"></div>
				<div class="dashboard_accident col-md-1"><div style="cursor:pointer; color:white; margin-top:10px; margin-right:20px" class="save_accident_link" id="save_plaitiff">Save</div></div>
				<div class="dashboard_accident col-md-5 defendant" id="defendant_property_damage_holder" style="margin-top:10px; border:0px solid yellow"></div>
			</div>
			<div class="container" style="border:0px solid red; margin:0px; padding:0px">
			<div style="float:right; cursor:pointer; color:white; margin-top:10px; margin-right:20px" class="save_accident_link" id="save_defendant">Save</div><div style="cursor:pointer; color:white; margin-top:10px; margin-right:10px" id="back_to_top">Top</div>
				<div class="dashboard_accident col-md-5" id="priors_holder" style="margin-top:10px; border:0px solid yellow"></div>
				<div class="dashboard_accident col-md-1"><div style="cursor:pointer; color:white; margin-top:10px; margin-right:20px" class="save_accident_link" id="save_plaitiff">Save</div></div>
				<div class="dashboard_accident col-md-5 defendant" id="defendant_priors_holder" style="margin-top:10px; border:0px solid yellow"></div>
			</div>
        </form>
    </div>    
</div>
<div>&nbsp;</div>
