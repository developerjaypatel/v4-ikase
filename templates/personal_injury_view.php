<?php $form_name = "personal_injury"; ?>
<div style="display:inline-block" class="col-md-12">
<div style="float:right" id="personal_injury_right">
	<% if (!blnPiReady) { %>
        <div style="background:url(../img/glass_card_fade_12.png)" id="gridster_car_passenger" class="prev_car">
        <form id="car_passenger_form" parsley-validate>
            <input id="table_name" name="table_name" type="hidden" value="car_passenger" />
            
            <input id="left_outside"  name="left_outside" type="hidden" value="" class="input_class" />
            <input id="hood"  name="hood" type="hidden" value="" class="input_class" />
<input id="trunk"  name="trunk" type="hidden" value="" class="input_class" />
<input id="rear_left"  name="rear_left" type="hidden" value="" class="input_class" />
<input id="middle_left"  name="middle_left" type="hidden" value="" class="input_class" />
<input id="front_left"  name="front_left" type="hidden" value="" class="input_class" />
<input id="rear_middle"  name="rear_middle" type="hidden" value="" class="input_class" />
<input id="middle_middle"  name="middle_middle" type="hidden" value="" class="input_class" />
<input id="front_middle"  name="front_middle" type="hidden" value="" class="input_class" />
<input id="rear_right"  name="rear_right" type="hidden" value="" class="input_class" />
<input id="middle_right"  name="middle_right" type="hidden" value="" class="input_class" />
<input id="front_right"  name="front_right" type="hidden" value="" class="input_class" />
<input id="right_outside"  name="right_outside" type="hidden" value="" class="input_class" />
            <ul>
        <li id="carGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:;; margin-left:-33px">
                    <table width="100%">
                        <tr>
                            <td>
                                <table style="display: inline-table" border="0" cellpadding="0" cellspacing="0" width="395" align="left">
                                <!-- fwtable fwsrc="Untitled" fwpage="Page 1" fwbase="car_empty.jpg" fwstyle="Dreamweaver" fwdocid = "278803958" fwnested="0" -->
                                  <tr>
                                   <td><img src="../img/ui/spacer.gif" width="42" height="1" border="0" alt="" /></td>
                                   <td><img src="../img/ui/spacer.gif" width="65" height="1" border="0" alt="" /></td>
                                   <td><img src="../img/ui/spacer.gif" width="2" height="1" border="0" alt="" /></td>
                                   <td><img src="../img/ui/spacer.gif" width="59" height="1" border="0" alt="" /></td>
                                   <td><img src="../img/ui/spacer.gif" width="57" height="1" border="0" alt="" /></td>
                                   <td><img src="../img/ui/spacer.gif" width="63" height="1" border="0" alt="" /></td>
                                   <td><img src="../img/ui/spacer.gif" width="162" height="1" border="0" alt="" /></td>
                                   <td><img src="../img/ui/spacer.gif" width="1" height="1" border="0" alt="" /></td>
                                  </tr>
    
                                  <tr>
                                   <td rowspan="5" class="roll_over" id="rear_bumper_outside"><img name="car_empty_r1_c1" src="../img/ui/car_empty_rear_bumper_outside.png" width="42" height="180" border="0" id="car_empty_r1_c1" alt="" /></td>
                                   <td colspan="2"><img name="car_empty_r1_c2" src="../img/ui/car_empty_r1_c2.png" width="67" height="35" border="0" id="car_empty_r1_c2" alt="" /></td>
                                   <td><img name="car_empty_r1_c4" src="../img/ui/car_empty_r1_c4.png" width="59" height="35" border="0" id="car_empty_r1_c4" alt="" /></td>
                                   <td class="roll_over" id="left_outside"><img name="car_empty_r1_c5" src="../img/ui/car_empty_left_outside.png" width="57" height="35" border="0" id="car_empty_left_outside" alt="" /></td>
                                   <td><img name="car_empty_r1_c6" src="../img/ui/car_empty_r1_c6.png" width="63" height="35" border="0" id="car_empty_r1_c6" alt="" /></td>
                                   <td><img name="car_empty_r1_c7" src="../img/ui/car_empty_r1_c7.png" width="162" height="35" border="0" id="car_empty_r1_c7" alt="" /></td>
                                   <td><img src="../img/ui/spacer.gif" width="1" height="35" border="0" alt="" /></td>
                                  </tr>
                                  <tr>
                                   <td rowspan="3"  class="roll_over" id="trunk">
                                        <img name="car_empty_r2_c2" src="../img/ui/car_empty_trunk.jpg" width="65" height="105" border="0" id="car_empty_r2_c2" alt="" />
                                        
                                   </td>
                                   <td rowspan="3">
                                        <img name="car_empty_r2_c3" src="../img/ui/car_empty_r2_c3.jpg" width="2" height="105" border="0" id="car_empty_r2_c3" alt="" />
                                   </td>
                                   <td class="roll_over" id="rear_left">
                                        <img name="car_empty_r2_c4" src="../img/ui/car_empty_rear_left.jpg" width="59" height="37" border="0" id="car_empty_r2_c4" alt="" />
                                        
                                   </td>
                                   <td class="roll_over" id="middle_left">
                                        <img name="car_empty_r2_c5" src="../img/ui/car_empty_middle_left.jpg" width="57" height="37" border="0" id="car_empty_r2_c5" alt="" />
                                   </td>
                                   <td class="roll_over" id="front_left">
                                        <img name="car_empty_r2_c6" src="../img/ui/car_empty_front_left.jpg" width="63" height="37" border="0" id="car_empty_r2_c6" alt="" />
                                   </td>
                                   <td rowspan="3" class="roll_over" id="hood">
                                        <img name="car_empty_r2_c7" src="../img/ui/car_empty_hood.png" width="162" height="105" border="0" id="car_empty_r2_c7" alt="" />
                                        
                                   </td>
                                   <td><img src="../img/ui/spacer.gif" width="1" height="37" border="0" alt="" /></td>
                                  </tr>
                                  <tr>
                                   <td class="roll_over" style="" id="rear_middle">
                                        <img name="car_empty_r3_c4" src="../img/ui/car_empty_rear_middle.jpg" width="59" height="35" border="0" id="car_empty_r3_c4" alt="" />
                                        
                                   </td>
                                   
                                   <td class="roll_over" id="middle_middle">
                                        <img name="car_empty_r3_c5" src="../img/ui/car_empty_middle_middle.jpg" width="57" height="35" border="0" id="car_empty_r3_c5" alt="" />
                                        
                                   </td>
                                   <td class="roll_over" id="front_middle">
                                        <img name="car_empty_r3_c6" src="../img/ui/car_empty_front_middle.jpg" width="63" height="35" border="0" id="car_empty_r3_c6" alt="" />
                                        
                                   </td>
                                   <td><img src="../img/ui/spacer.gif" width="1" height="35" border="0" alt="" /></td>
                                  </tr>
                                  <tr>
                                   <td class="roll_over" id="rear_right">
                                        <img name="car_empty_r4_c4" src="../img/ui/car_empty_rear_right.jpg" width="59" height="33" border="0" id="car_empty_r4_c4" alt="" />
                                        
                                   </td>
                                   <td class="roll_over" id="middle_right">
                                        <img name="car_empty_r4_c5" src="../img/ui/car_empty_middle_right.jpg" width="57" height="33" border="0" id="car_empty_r4_c5" alt="" />
                                        
                                   </td>
                                   <td class="roll_over" id="front_right">
                                        <img name="car_empty_r4_c6" src="../img/ui/car_empty_front_right.jpg" width="63" height="33" border="0" id="car_empty_r4_c6" alt="" />
                                        
                                   </td>
                                   <td><img src="../img/ui/spacer.gif" width="1" height="33" border="0" alt="" /></td>
                                  </tr>
                                  <tr>
                                   <td colspan="2"><img name="car_empty_r5_c2" src="../img/ui/car_empty_r5_c2.png" width="67" height="40" border="0" id="car_empty_r5_c2" alt="" /></td>
                                   <td style="" ><img name="car_empty_r5_c4" src="../img/ui/car_empty_r5_c4.png" width="59" height="40" border="0" id="car_empty_r5_c4" alt="" /></td>
                                   <td class="roll_over" id="right_outside">
                                        <img name="car_empty_r5_c5" src="../img/ui/car_empty_right_outside.png" width="57" height="40" border="0" id="car_empty_r5_c5" alt="" />
                                        
                                   </td>
                                   <td><img name="car_empty_r5_c6" src="../img/ui/car_empty_r5_c6.png" width="63" height="40" border="0" id="car_empty_r5_c6" alt="" /></td>
                                   <td><img name="car_empty_r5_c7" src="../img/ui/car_empty_r5_c7.png" width="162" height="40" border="0" id="car_empty_r5_c7" alt="" /></td>
                                   <td><img src="../img/ui/spacer.gif" width="1" height="40" border="0" alt="" /></td>
                                  </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
              </li>
            </ul>
            </form>
        </div>
        <div>&nbsp;</div>
    	<div style="background:url(../img/glass_card_fade_19.png); padding:5px; border:0px solid white; width:auto" id="gridster_accident" class="gridster">
    	<form id="vehicle_form">
        <input id="table_name" name="table_name" type="hidden" value="vehicle" />
        <ul>
            <li id="makeGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Make</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="makeSave">
                
                <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="makeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="makeInput" id="makeInput" class="kase <?php echo $form_name; ?> vehicle input_class hidden" style="margin-top:-25px; margin-left:85px; width:290px" tabindex="0" placeholder="Make" />
              <span id="makeSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
            </li>
          <li id="yearGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> vehicle gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Year</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="yearSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="yearSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="yearInput" id="yearInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="Year" style="margin-top:-25px; margin-left:85px; width:290px; border:0px solid red" autocomplete="off" tabindex="1" />
              <span id="yearSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
            </li>
            <li id="modelGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> vehicle gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Model</div></h6>
        <div style="margin-top:-12px" class="save_holder hidden" id="modelSave">
            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="modelSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="" name="modelInput" id="modelInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" style="margin-top:-25px; margin-left:85px; width:290px" tabindex="2" placeholder="Model" />
          <span id="modelSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
        </li>
        <li id="license_plateGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> vehicle gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Tags</div></h6>
        <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="license_plateSave">
            <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="license_plateSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="" name="license_plateInput" id="license_plateInput" class="<?php echo $form_name; ?> vehicle input_class hidden" style="width:70px; margin-top:-25px; margin-left:85px; width:290px" tabindex="0" placeholder="Tags" />
          <span id="license_plateSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
        </li>
        <li id="vinGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">VIN</div></h6>
        <div style="margin-top:-12px" class="save_holder hidden" id="vinSave">
            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="vinSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="" name="vinInput" id="vinInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="VIN" style="margin-top:-25px; margin-left:85px; width:290px; border:0px solid red" autocomplete="off" tabindex="1" />
          <span id="vinSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
        </li>
        <li id="vehicle_descriptionGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Description</div></h6>
        <div style="margin-top:-12px" class="save_holder hidden" id="vehicle_descriptionSave">
            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="vehicle_descriptionSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="" name="vehicle_descriptionInput" id="vehicle_descriptionInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="VIN" style="margin-top:-25px; margin-left:85px; width:290px; border:0px solid red" autocomplete="off" tabindex="1" />
          <span id="vehicle_descriptionSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
        </li>
     </ul>
     </form>
    </div>
    <% } else { %>
    	<div style="border:0px pink solid; color:white">
        	<div class="col-md-6" style="font-size:1.5em; margin-bottom:5px">
            	<div style="float:right" class="injury_buttons">
                	<button class="btn btn-xs <% if (owner_id > -1) { %>btn-success<% } %>" id="vehicle_owner">Vehicle Owner</button>
                    &nbsp;
                    <button class="btn btn-xs <% if (rental_plaintiff != "") { %>btn-success<% } %>" id="vehicle_rental">Rental</button>
                    &nbsp;
                    <button class="btn btn-xs <% if (repair_plaintiff != "") { %>btn-success<% } %>" id="vehicle_repair">Repair</button>
                </div>
                PLAINTIFF
            </div>
            <div class="col-md-6" style="font-size:1.5em; margin-bottom:5px">
            	<div style="float:right" class="injury_buttons">
                	<button class="btn btn-xs <% if (defendant_owner_id > -1) { %>btn-success<% } %>" id="defendant_vehicle_owner">Vehicle Owner</button>
                    &nbsp;
                    <button class="btn btn-xs <% if (rental_defendant != "") { %>btn-success<% } %>" id="defendant_vehicle_rental">Rental</button>
                    &nbsp;
                    <button class="btn btn-xs <% if (repair_defendant != "") { %>btn-success<% } %>" id="defendant_vehicle_repair">Repair</button>
                </div>
            	DEFENDANT
            </div>
        </div>
        <div>&nbsp;</div>
    	<div style="background:url(../img/glass_card_fade_14_long.png); padding:5px; border:0px solid white; width:1000px; float:left;" id="gridster_acc_details" class="gridster">
    	<form id="personal_injury_other_form">
        <input id="table_name" name="table_name" type="hidden" value="personal_injury_other" />
        <div style="margin-top:0px; margin-left:10px; padding-top:5px">            
			<div style="font-size:1.4em; color:#FFFFFF;font-weight:lighter;">Accident Details</div>
    	</div>
        <ul>
        	<li id="client_streetGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Cl. Street</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="client_streetSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="client_streetSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input name="client_streetInput" id="client_streetInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px; width:245px" />
              <span id="client_streetSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-25px; margin-left:60px"></span>
            </li>
            <li id="client_directionGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Direction</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="client_directionSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="client_directionSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input name="client_directionInput" id="client_directionInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px; width:245px" />
              <span id="client_directionSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-25px; margin-left:60px"></span>
            </li>
            <li id="client_speedGrid" data-row="1" data-col="3" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Client Speed</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="client_speedSave">
                
                <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="client_speedSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="client_speedInput" id="client_speedInput" class="kase <?php echo $form_name; ?> accident_view input_class hidden" style="width:65px; margin-top:-25px; margin-left:85px" tabindex="0" />
              <span id="client_speedSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
            </li>
          
            <li id="client_laneGrid" data-row="2" data-col="3" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Lane</div></h6>
        <div style="margin-top:-12px" class="save_holder hidden" id="client_laneSave">
            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="client_laneSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="" name="client_laneInput" id="client_laneInput" class="<?php echo $form_name; ?> kase accident_view input_class hidden" placeholder="Lane" style="margin-top:-25px; margin-left:85px; width:65px" tabindex="2" />
          <span id="client_laneSpan" class="kase <?php echo $form_name; ?> person_view span_class form_span_vert" style="margin-top:-30px; margin-left:90px"></span>
        </li>
        
        <li id="defendant_streetGrid" data-row="1" data-col="4" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Def. Street</div></h6>
        <div style="margin-top:-12px" class="save_holder hidden" id="defendant_streetSave">
            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_streetSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
        <input name="defendant_streetInput" id="defendant_streetInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px; width:245px" />
          <span id="defendant_streetSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-25px; margin-left:60px"></span>
        </li>
        <li id="defendant_directionGrid" data-row="2" data-col="4" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Direction</div></h6>
        <div style="margin-top:-12px" class="save_holder hidden" id="defendant_directionSave">
            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_directionSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
        <input name="defendant_directionInput" id="defendant_directionInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px; width:245px" />
          <span id="defendant_directionSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-25px; margin-left:60px"></span>
        </li>
        
        <li id="defendant_speedGrid" data-row="1" data-col="6" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Def Speed</div></h6>
        <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="defendant_speedSave">
            <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="defendant_speedSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="" name="defendant_speedInput" id="defendant_speedInput" class="<?php echo $form_name; ?> input_class hidden" style="width:65px; margin-top:-25px; margin-left:85px" tabindex="0" />
          <span id="defendant_speedSpan" class="kase <?php echo $form_name; ?> accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
        </li>
        
        <li id="client_laneGrid" data-row="2" data-col="6" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Lane</div></h6>
        <div style="margin-top:-12px" class="save_holder hidden" id="client_laneSave">
            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="client_laneSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="" name="defendant_laneInput" id="defendant_laneInput" class="<?php echo $form_name; ?> kase accident_view input_class hidden" placeholder="Lane" style="margin-top:-25px; margin-left:85px; width:65px" tabindex="2" />
          <span id="defendant_laneSpan" class="kase <?php echo $form_name; ?> accident_view span_class form_span_vert" style="margin-top:-30px; margin-left:90px"></span>
        </li>
        </ul>
        </form>
    </div>
    	<div>&nbsp;</div>
    	<div style="background:url(../img/glass_card_fade_20_long.png); padding:5px; border:0px solid white; width:1000px; float:left;" id="gridster_acc_info_details" class="gridster">
            <form id="personal_injury_info_form">
            <input id="table_name" name="table_name" type="hidden" value="personal_injury_info" />
            <div style="margin-top:0px; margin-left:10px; padding-top:5px">
            	<div style="float:right" class="injury_buttons">
                	<button class="btn btn-xs <% if (witness_count > 0) { %>btn-success<% } %>" id="witnesses_button">Witnesses</button>
                </div>            
                <div style="font-size:1.4em; color:#FFFFFF;font-weight:lighter;">Accident Info</div>
            </div>
            <ul>
                <li id="clear_liabilityGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Clear Liab</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="clear_liabilitySave">
                    <a class="save_field" title="Click to save this field" id="clear_liabilitySaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="clear_liabilityInput" id="clear_liabilityInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px;margin-top:-20px;" />
                  <span id="clear_liabilitySpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:95px; border:0px solid black""></span>
            </li>
            <li id="police_calledGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Police Called</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="police_calledSave">
                    <a class="save_field" title="Click to save this field" id="police_calledSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="police_calledInput" id="police_calledInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
                  <span id="police_calledSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
            </li>
            <li id="no_notice_warningGrid" data-row="1" data-col="3" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">No Notice/Warning</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="no_notice_warningSave">
                    <a class="save_field" title="Click to save this field" id="no_notice_warningSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="no_notice_warningInput" id="no_notice_warningInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
                  <span id="no_notice_warningSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black""></span>
            </li>
            <li id="similar_priorGrid" data-row="1" data-col="4" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Similar Prior Acct</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="similar_priorSave">
                    <a class="save_field" title="Click to save this field" id="similar_priorSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="similar_priorInput" id="similar_priorInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
                  <span id="similar_priorSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
            </li>
            <li id="injury_erGrid" data-row="1" data-col="5" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">ER</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="injury_erSave">
                    <a class="save_field" title="Click to save this field" id="injury_erSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="injury_erInput" id="injury_erInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:65px; margin-top:-20px" />
                  <span id="injury_erSpan" class="<?php echo $form_name; ?> span_class form_span_vert span_perm" style="margin-top:-26px; margin-left:95px; border:0px solid black""></span>
            </li>
            <li id="injury_ambulanceGrid" data-row="1" data-col="6" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Ambulance</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="injury_ambulanceSave">
                    <a class="save_field" title="Click to save this field" id="injury_ambulanceSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="injury_ambulanceInput" id="injury_ambulanceInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
                  <span id="injury_ambulanceSpan" class="<?php echo $form_name; ?> span_class form_span_vert span_perm" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
            </li>
            <!-- <li id="consent_formGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Consent Form</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="consent_formSave">
                    <a class="save_field" title="Click to save this field" id="consent_formSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="consent_formInput" id="consent_formInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
                  <span id="consent_formSpan" class="<?php echo $form_name; ?> span_class form_span_vert span_perm" style="margin-top:-26px; margin-left:95px; border:0px solid black""></span>
            </li>
            <li id="adr_agreementGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">ADR Agreement</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="adr_agreementSave">
                    <a class="save_field" title="Click to save this field" id="adr_agreementSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="adr_agreementInput" id="adr_agreementInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
                  <span id="adr_agreementSpan" class="<?php echo $form_name; ?> span_class form_span_vert span_perm" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
            </li>
            <li id="scene_photosGrid" data-row="2" data-col="3" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Scene Photos</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="scene_photosSave">
                    <a class="save_field" title="Click to save this field" id="scene_photosSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="scene_photosInput" id="scene_photosInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
                  <span id="scene_photosSpan" class="<?php echo $form_name; ?> span_class form_span_vert span_perm" style="margin-top:-26px; margin-left:95px; border:0px solid black""></span>
            </li>
            <li id="injury_photosGrid" data-row="2" data-col="4" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Injury Photos</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="injury_photosSave">
                    <a class="save_field" title="Click to save this field" id="injury_photosSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="injury_photosInput" id="injury_photosInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
                  <span id="injury_photosSpan" class="<?php echo $form_name; ?> span_class form_span_vert span_perm" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
            </li>
            <li id="prop_damage_photosGrid" data-row="2" data-col="5" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Prop Damage Photos</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="prop_damage_photosSave">
                    <a class="save_field" title="Click to save this field" id="prop_damage_photosSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="prop_damage_photosInput" id="prop_damage_photosInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:65px; margin-top:-20px" />
                  <span id="prop_damage_photosSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black""></span>
            </li>
            <li id="scene_diagramGrid" data-row="2" data-col="6" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Scene Diagram</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="scene_diagramSave">
                    <a class="save_field" title="Click to save this field" id="scene_diagramSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                  <input type="checkbox" value="" name="scene_diagramInput" id="scene_diagramInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
                  <span id="scene_diagramSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
            </li> -->
            </ul>
            <div style="margin-top:0px; margin-left:10px; padding-top:5px">            
                <div style="font-size:1.4em; color:#FFFFFF;font-weight:lighter;">Police Report</div>
            </div>
            <ul>
            	<li id="report_numberGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Report #</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="report_numberSave">
                        <a class="save_field" title="Click to save this field" id="report_numberSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    
                      <input value="" name="report_numberInput" id="report_numberInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px; width:245px" />
                      <span id="report_numberSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
                </li>
                <li id="citation_numberGrid" data-row="1" data-col="3" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Citation #</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="citation_numberSave">
                        <a class="save_field" title="Click to save this field" id="citation_numberSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    
                      <input value="" name="citation_numberInput" id="citation_numberInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px; width:245px" />
                      <span id="citation_numberSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
                </li>
                <li id="citation_detailsGrid" data-row="1" data-col="6" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Details</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="citation_detailsSave">
                        <a class="save_field" title="Click to save this field" id="citation_detailsSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    
                      <input value="" name="citation_detailsInput" id="citation_detailsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px; width:245px" />
                      <span id="citation_detailsSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
                </li>
            </ul>
            </form>
    </div>
    	<div>&nbsp;</div>
    	<div style="border:0px solid green; color:#FFF">
        	
    		<!-- this is the plaintiff column -->
            <div style="float:left; padding-right: 20px; border:0px solid blue" id="plaintiff_car_information">
                
                <div style="background:url(../img/glass_card_fade_12.png); font-size:1em" id="gridster_car_passenger">
                <form id="car_passenger_form" parsley-validate>
                    <input id="table_name" name="table_name" type="hidden" value="car_passenger" />
                    
                    <input id="outside_rear_left_quarter_panel"  name="outside_rear_left_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="outside_rear_left_door"  name="outside_rear_left_door" type="hidden" value="" class="input_class" />
                    <input id="outside_middle_left_door"  name="outside_middle_left_door" type="hidden" value="" class="input_class" />
                    <input id="outside_front_left_door"  name="outside_front_left_door" type="hidden" value="" class="input_class" />
                    <input id="left_side_mirror"  name="left_side_mirror" type="hidden" value="" class="input_class" />
                    <input id="outside_front_left_quarter_panel"  name="outside_front_left_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="rear_left_corner"  name="rear_left_corner" type="hidden" value="" class="input_class" />
                    <input id="rear_left_quarter_panel"  name="rear_left_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="rear_left_door"  name="rear_left_door" type="hidden" value="" class="input_class" />
                    <input id="middle_left_door"  name="middle_left_door" type="hidden" value="" class="input_class" />
                    <input id="front_left_door"  name="front_left_door" type="hidden" value="" class="input_class" />
                    <input id="front_left_quarter_panel"  name="front_left_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="outside_rear_bumper"  name="outside_rear_bumper" type="hidden" value="" class="input_class" />
                    
                    <input id="rear_bumper"  name="rear_bumper" type="hidden" value="" class="input_class" />
                    <input id="trunk"  name="trunk" type="hidden" value="" class="input_class" />
                    <input id="rear_left_seat"  name="rear_left_seat" type="hidden" value="" class="input_class" />
                    <input id="middle_left_seat"  name="middle_left_seat" type="hidden" value="" class="input_class" />
                    <input id="front_left_seat"  name="front_left_seat" type="hidden" value="" class="input_class" />
                    <input id="windshield"  name="windshield" type="hidden" value="" class="input_class" />
                    <input id="hood"  name="hood" type="hidden" value="" class="input_class" />
                    <input id="outside_hood"  name="outside_hood" type="hidden" value="" class="input_class" />
                    <input id="rear_middle_seat"  name="rear_middle_seat" type="hidden" value="" class="input_class" />
                    <input id="middle_middle_seat"  name="middle_middle_seat" type="hidden" value="" class="input_class" />
                    <input id="front_middle_seat"  name="front_middle_seat" type="hidden" value="" class="input_class" />
                    <input id="rear_right_seat"  name="rear_right_seat" type="hidden" value="" class="input_class" />
                    <input id="middle_right_seat"  name="middle_right_seat" type="hidden" value="" class="input_class" />
                    
                    <input id="front_right_seat"  name="front_right_seat" type="hidden" value="" class="input_class" />
                    <input id="rear_right_corner"  name="rear_right_corner" type="hidden" value="" class="input_class" />
                    <input id="rear_right_quarter_panel"  name="rear_right_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="rear_right_door"  name="rear_right_door" type="hidden" value="" class="input_class" />
                    <input id="middle_right_door"  name="middle_right_door" type="hidden" value="" class="input_class" />
                    <input id="front_right_door"  name="front_right_door" type="hidden" value="" class="input_class" />
                    <input id="right_side_mirror"  name="right_side_mirror" type="hidden" value="" class="input_class" />
                    <input id="front_right_quarter_panel"  name="front_right_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="outside_rear_right_quarter_panel"  name="outside_rear_right_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="outside_rear_right_door"  name="outside_rear_right_door" type="hidden" value="" class="input_class" />
                    <input id="outside_middle_right_door"  name="outside_middle_right_door" type="hidden" value="" class="input_class" />
                    <input id="outside_front_right_door"  name="outside_front_right_door" type="hidden" value="" class="input_class" />
                    <input id="outside_front_right_quarter_panel"  name="outside_front_right_quarter_panel" type="hidden" value="" class="input_class" />
            
                    <ul>
                <li id="carGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:10px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:; margin-left:-33px">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding:0px">
                                <tr>
                                    <td>
                                        <table style="padding:0px; font-size:0px" border="0" cellpadding="0" cellspacing="0">
                                                
                                              <tr>
                                               <td style="padding:0px">&nbsp;</td>
                                               <td style="padding:0px">&nbsp;</td>
                                               <td class="roll_over" id="outside_rear_left_quarter_panel" style="padding:0px"><img name="new_car_empty_r1_c3" src="../images/new_car_parts/new_car_empty_outside_rear_left_quarter_panel.png" width="71" height="22" border="0" id="outside_rear_left_quarter_panel" alt="" /></td>
                                               <td class="roll_over" id="outside_rear_left_door" style="padding:0px"><img name="new_car_empty_r1_c4" src="../images/new_car_parts/new_car_empty_outside_rear_left_door.png" width="59" height="22" border="0" id="outside_rear_left_door" alt="" /></td>
                                               <td class="roll_over" id="outside_middle_left_door" style="padding:0px"><img name="new_car_empty_r1_c5" src="../images/new_car_parts/new_car_empty_outside_middle_left_door.png" width="64" height="22" border="0" id="outside_middle_left_door" alt="" /></td>
                                               <td class="roll_over" id="outside_front_left_door" style="padding:0px"><img name="new_car_empty_r1_c6" src="../images/new_car_parts/new_car_empty_outside_front_left_door.png" width="68" height="22" border="0" id="outside_front_left_door" alt="" /></td>
                                               <td rowspan="2" class="roll_over" id="left_side_mirror" style="padding:0px"><img name="new_car_empty_r1_c7" src="../images/new_car_parts/new_car_empty_left_side_mirror.png" width="33" height="39" border="0" id="left_side_mirror" alt="" /></td>
                                               <td class="roll_over" id="outside_front_left_quarter_panel" style="padding:0px"><img name="new_car_empty_r1_c8" src="../images/new_car_parts/new_car_empty_outside_front_left_quarter_panel.png" width="89" height="22" border="0" id="outside_front_left_quarter_panel" alt="" /></td>
                                               <td style="padding:0px">&nbsp;</td>
                                               <td style="padding:0px"><img src="../images/new_car_parts/spacer.gif" width="1" height="0" border="0" alt="" /></td>
                                              </tr>
                                              <tr style="height:17px">
                                               <td style="padding:0px">&nbsp;</td>
                                               <td class="roll_over" id="rear_left_corner" style="padding:0px"><img name="new_car_empty_r2_c2" src="../images/new_car_parts/new_car_empty_rear_left_corner.png" width="23" height="17" border="0" id="rear_left_corner" alt="" /></td>
                                               <td class="roll_over" id="rear_left_quarter_panel" style="padding:0px"><img name="new_car_empty_r2_c3" src="../images/new_car_parts/new_car_empty_rear_left_quarter_panel.png" width="71" height="17" border="0" id="rear_left_quarter_panel" alt="" /></td>
                                               <td class="roll_over" id="rear_left_door" style="padding:0px"><img name="new_car_empty_r2_c4" src="../images/new_car_parts/new_car_empty_rear_left_door.jpg" width="59" height="17" border="0" id="rear_left_door" alt="" /></td>
                                               <td class="roll_over" id="middle_left_door" style="padding:0px"><img name="new_car_empty_r2_c5" src="../images/new_car_parts/new_car_empty_middle_left_door.jpg" width="64" height="17" border="0" id="middle_left_door" alt="" /></td>
                                               <td class="roll_over" id="front_left_door" style="padding:0px"><img name="new_car_empty_r2_c6" src="../images/new_car_parts/new_car_empty_front_left_door.jpg" width="68" height="17" border="0" id="front_left_door" alt="" /></td>
                                               <td class="roll_over" id="front_left_quarter_panel" style="padding:0px"><img name="new_car_empty_r2_c8" src="../images/new_car_parts/new_car_empty_front_left_quarter_panel.png" width="89" height="17" border="0" id="front_left_quarter_panel" alt="" /></td>
                                               <td style="padding:0px">&nbsp;</td>
                                               <td style="padding:0px"><img src="../images/new_car_parts/spacer.gif" width="1" height="0" border="0" alt="" /></td>
                                              </tr>
                                              <tr>
                                               <td rowspan="3" class="roll_over" id="outside_rear_bumper"><img name="new_car_empty_r3_c1" src="../images/new_car_parts/new_car_empty_outside_rear_bumper.png" width="25" height="106" border="0" id="outside_rear_bumper" alt="" /></td>
                                               <td rowspan="3" class="roll_over" id="rear_bumper"><img name="new_car_empty_r3_c2" src="../images/new_car_parts/new_car_empty_rear_bumper.png" width="23" height="106" border="0" id="rear_bumper" alt="" /></td>
                                               <td rowspan="3" class="roll_over" id="trunk"><img name="new_car_empty_r3_c3" src="../images/new_car_parts/new_car_empty_trunk.jpg" width="71" height="106" border="0" id="trunk" alt="" /></td>
                                               <td class="roll_over" id="rear_left_seat"><img name="new_car_empty_r3_c4" src="../images/new_car_parts/new_car_empty_rear_left_seat.jpg" width="59" height="36" border="0" id="rear_left_seat" alt="" /></td>
                                               <td class="roll_over" id="middle_left_seat"><img name="new_car_empty_r3_c5" src="../images/new_car_parts/new_car_empty_middle_left_seat.jpg" width="64" height="36" border="0" id="middle_left_seat" alt="" /></td>
                                               <td class="roll_over" id="front_left_seat"><img name="new_car_empty_r3_c6" src="../images/new_car_parts/new_car_empty_front_left_seat.jpg" width="68" height="36" border="0" id="front_left_seat" alt="" /></td>
                                               <td rowspan="3" class="roll_over" id="windshield"><img name="new_car_empty_r3_c7" src="../images/new_car_parts/new_car_empty_windshield.jpg" width="33" height="106" border="0" id="windshield" alt="" /></td>
                                               <td rowspan="3" class="roll_over" id="hood"><img name="new_car_empty_r3_c8" src="../images/new_car_parts/new_car_empty_hood.png" width="89" height="106" border="0" id="hood" alt="" /></td>
                                               <td rowspan="3" class="roll_over" id="outside_hood"><img name="new_car_empty_r3_c9" src="../images/new_car_parts/new_car_empty_outside_hood.png" width="28" height="106" border="0" id="outside_hood" alt="" /></td>
                                               <td><img src="../images/new_car_parts/spacer.gif" width="1" height="0" border="0" alt="" /></td>
                                              </tr>
                                              <tr>
                                               <td class="roll_over" id="rear_middle_seat"><img name="new_car_empty_r4_c4" src="../images/new_car_parts/new_car_empty_rear_middle_seat.jpg" width="59" height="35" border="0" id="rear_middle_seat" alt="" /></td>
                                               <td class="roll_over" id="middle_middle_seat"><img name="new_car_empty_r4_c5" src="../images/new_car_parts/new_car_empty_middle_middle_seat.jpg" width="64" height="35" border="0" id="middle_middle_seat" alt="" /></td>
                                               <td class="roll_over" id="front_middle_seat"><img name="new_car_empty_r4_c6" src="../images/new_car_parts/new_car_empty_front_middle_seat.jpg" width="68" height="35" border="0" id="front_middle_seat" alt="" /></td>
                                               <td><img src="../images/new_car_parts/spacer.gif" width="1" height="35" border="0" alt="" /></td>
                                              </tr>
                                              <tr>
                                               <td class="roll_over" id="rear_right_seat"><img name="new_car_empty_r5_c4" src="../images/new_car_parts/new_car_empty_rear_right_seat.jpg" width="59" height="35" border="0" id="rear_right_seat" alt="" /></td>
                                               <td class="roll_over" id="middle_right_seat"><img name="new_car_empty_r5_c5" src="../images/new_car_parts/new_car_empty_middle_right_seat.jpg" width="64" height="35" border="0" id="middle_right_seat" alt="" /></td>
                                               <td class="roll_over" id="front_right_seat"><img name="new_car_empty_r5_c6" src="../images/new_car_parts/new_car_empty_front_right_seat.jpg" width="68" height="35" border="0" id="front_right_seat" alt="" /></td>
                                               <td><img src="../images/new_car_parts/spacer.gif" width="1" height="35" border="0" alt="" /></td>
                                              </tr>
                                              <tr>
                                               <td>&nbsp;</td>
                                               <td class="roll_over" id="rear_right_corner"><img name="new_car_empty_r6_c2" src="../images/new_car_parts/new_car_empty_rear_right_corner.png" width="23" height="14" border="0" id="rear_right_corner" alt="" /></td>
                                               <td class="roll_over" id="rear_right_quarter_panel"><img name="new_car_empty_r6_c3" src="../images/new_car_parts/new_car_empty_rear_right_quarter_panel.png" width="71" height="14" border="0" id="rear_right_quarter_panel" alt="" /></td>
                                               <td class="roll_over" id="rear_right_door"><img name="new_car_empty_r6_c4" src="../images/new_car_parts/new_car_empty_rear_right_door.jpg" width="59" height="14" border="0" id="rear_right_door" alt="" /></td>
                                               <td class="roll_over" id="middle_right_door"><img name="new_car_empty_r6_c5" src="../images/new_car_parts/new_car_empty_middle_right_door.jpg" width="64" height="14" border="0" id="middle_right_door" alt="" /></td>
                                               <td class="roll_over" id="front_right_door"><img name="new_car_empty_r6_c6" src="../images/new_car_parts/new_car_empty_front_right_door.jpg" width="68" height="14" border="0" id="front_right_door" alt="" /></td>
                                               <td rowspan="2" class="roll_over" id="right_side_mirror"><img name="new_car_empty_r6_c7" src="../images/new_car_parts/new_car_empty_right_side_mirror.png" width="33" height="35" border="0" id="right_side_mirror" alt="" /></td>
                                               <td class="roll_over" id="front_right_quarter_panel"><img name="new_car_empty_r6_c8" src="../images/new_car_parts/new_car_empty_front_right_quarter_panel.png" width="89" height="14" border="0" id="front_right_quarter_panel" alt="" /></td>
                                               <td>&nbsp;</td>
                                               <td><img src="../images/new_car_parts/spacer.gif" width="1" height="14" border="0" alt="" /></td>
                                              </tr>
                                              <tr>
                                               <td>&nbsp;</td>
                                               <td>&nbsp;</td>
                                               <td class="roll_over" id="outside_rear_right_quarter_panel"><img name="new_car_empty_r7_c3" src="../images/new_car_parts/new_car_empty_outside_rear_right_quarter_panel.png" width="71" height="21" border="0" id="outside_rear_right_quarter_panel" alt="" /></td>
                                               <td class="roll_over" id="outside_rear_right_door"><img name="new_car_empty_r7_c4" src="../images/new_car_parts/new_car_empty_outside_rear_right_door.png" width="59" height="21" border="0" id="outside_rear_right_door" alt="" /></td>
                                               <td class="roll_over" id="outside_middle_right_door"><img name="new_car_empty_r7_c5" src="../images/new_car_parts/new_car_empty_outside_middle_right_door.png" width="64" height="21" border="0" id="outside_middle_right_door" alt="" /></td>
                                               <td class="roll_over" id="outside_front_right_door"><img name="new_car_empty_r7_c6" src="../images/new_car_parts/new_car_empty_outside_front_right_door.png" width="68" height="21" border="0" id="outside_front_right_door" alt="" /></td>
                                               <td class="roll_over" id="outside_front_right_quarter_panel"><img name="new_car_empty_r7_c8" src="../images/new_car_parts/new_car_empty_outside_front_right_quarter_panel.png" width="89" height="21" border="0" id="outside_front_right_quarter_panel" alt="" /></td>
                                               <td>&nbsp;</td>
                                               <td><img src="../images/new_car_parts/spacer.gif" width="1" height="21" border="0" alt="" /></td>
                                              </tr>
                                      </table>
                                    </td>
                                </tr>
                            </table>
                      </li>
                    </ul>
                  </form>
                </div>
                <div>&nbsp;</div>
                <div style="background:url(../img/glass_card_fade_19.png); padding:5px; border:0px solid white; width:auto;" id="gridster_accident" class="gridster">
                    <form id="vehicle_form">
                    <input id="table_name" name="table_name" type="hidden" value="vehicle" />
                    <ul>
                        <li id="makeGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Make</div></h6>
                        <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="makeSave">
                            
                            <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="makeSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="makeInput" id="makeInput" class="kase <?php echo $form_name; ?> vehicle input_class hidden" style="margin-top:-25px; margin-left:85px; width:340px; " tabindex="0" placeholder="Make" />
                          <span id="makeSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                      
                        <li id="modelGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> vehicle gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Model</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="modelSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="modelSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="modelInput" id="modelInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" style="margin-top:-25px; margin-left:85px; width:340px" tabindex="2" placeholder="Model" />
                          <span id="modelSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                        <li id="yearGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> vehicle gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Year</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="yearSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="yearSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="yearInput" id="yearInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="Year" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                          <span id="yearSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                      </li>
                      <li id="license_plateGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> vehicle gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Tags</div></h6>
                        <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="license_plateSave">
                            <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="license_plateSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="license_plateInput" id="license_plateInput" class="<?php echo $form_name; ?> vehicle input_class hidden" style="width:70px; margin-top:-25px; margin-left:85px; width:100px" tabindex="0" placeholder="Tags" />
                          <span id="license_plateSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                        <li id="vinGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">VIN</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="vinSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="vinSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="vinInput" id="vinInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="VIN" style="margin-top:-25px; margin-left:85px; width:340px; border:0px solid red" autocomplete="off" tabindex="1" />
                          <span id="vinSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                        <li id="vehicle_descriptionGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Description</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="vehicle_descriptionSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="vehicle_descriptionSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="vehicle_descriptionInput" id="vehicle_descriptionInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="Description" style="margin-top:-25px; margin-left:85px; width:340px; border:0px solid red" autocomplete="off" tabindex="1" />
                          <span id="vehicle_descriptionSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                        
                        <li id="vehicle_colorGrid" data-row="5" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Color</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="vehicle_colorSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="vehicle_colorSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="vehicle_colorInput" id="vehicle_colorInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                          <span id="vehicle_colorSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                        <li id="vehicle_typeGrid" data-row="5" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Type</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="vehicle_typeSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="vehicle_typeSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <select name="vehicle_typeInput" id="vehicle_typeInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden"style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" tabindex="1">
                            <option value="">Select from list...</option>
                            <option value="car">Car</option>
                            <option value="truck">Truck</option>
                            <option value="motorcycle">Motorcycle</option>
                            <option value="bike">Bicycle</option>
                            <option value="bus">Bus</option>
                            <option value="rv">RV</option>
                            <option value="van">Van</option>
                            <option value="pedestrian">Pedestrian</option>
                            <option value="scooter">Scooter</option>
                            <option value="hoverboard">Hoverboard</option>
                            <option value="skateboard">Skateboard</option>
                            <option value="boat">Boat</option>
                          </select>
                          <span id="vehicle_typeSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                        <li id="vehicle_commentsGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="3" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Additional Info</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="vehicle_commentsSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="vehicle_commentsSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <textarea name="vehicle_commentsInput" id="vehicle_commentsInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="Any additional info..." style="margin-left:85px; width:340px; height:100px; border:0px solid red" autocomplete="off" tabindex="1"></textarea>
                          <span id="vehicle_commentsSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style=" margin-left:90px"></span>
                        </li>
                        <li id="damagesGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Damages ($)</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="damagesSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="damagesSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input type="number" value="" name="damagesInput" id="damagesInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                          <span id="damagesSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                        
                        <li id="towingGrid" data-row="7" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Towing ($)</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="towingSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="towingSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input type="number" value="" name="towingInput" id="towingInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                          <span id="towingSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                        
                        <li id="storageGrid" data-row="8" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Storage ($)</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="storageSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="storageSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input type="number" value="" name="storageInput" id="storageInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                          <span id="storageSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                        
                        <li id="lossesGrid" data-row="8" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Losses ($)</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="lossesSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="lossesSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input type="number" value="" name="lossesInput" id="lossesInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                          <span id="lossesSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                        
                        <li id="damage_detailsGrid" data-row="9" data-col="1" data-sizex="2" data-sizey="3" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Damage Details</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="damage_detailsSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="damage_detailsSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <textarea name="damage_detailsInput" id="damage_detailsInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="Details of damage" style="margin-left:85px; width:340px; height:100px; border:0px solid red" autocomplete="off" tabindex="1"></textarea>
                          <span id="damage_detailsSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                 </ul>
                 <!--
                 <ul style="display:none">
                    <li id="rental_coverageGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Rental Cov.</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="rental_coverageSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="rental_coverageSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="rental_coverageInput" id="rental_coverageInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:340px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="rental_coverageSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="rental_amountGrid" data-row="5" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Amount</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="rental_amountSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="rental_amountSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="rental_amountInput" id="rental_amountInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="rental_amountSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="rental_daysGrid" data-row="5" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Days</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="rental_daysSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="rental_daysSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="rental_daysInput" id="rental_daysInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="rental_daysSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="coverage_disputedGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Cov. Disputed</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="coverage_disputedSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="coverage_disputedLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" type="checkbox" name="coverage_disputedInput" id="coverage_disputedInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" style="margin-top:-25px; margin-left:45px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="coverage_disputedSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="liab_coll_onlyGrid" data-row="6" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Liab/Coll Only</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="liab_coll_onlySave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="liab_coll_onlySaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="liab_coll_onlyInput" id="liab_coll_onlyInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="liab_coll_onlySpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="vehicle_totaledGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Totaled</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="vehicle_totaledSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="vehicle_totaledSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input type="checkbox" value="" name="vehicle_totaledInput" id="vehicle_totaledInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:45px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="vehicle_totaledSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="coll_deductibleGrid" data-row="7" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Coll Deductible</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="coll_deductibleSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="coll_deductibleSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="coll_deductibleInput" id="coll_deductibleInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="coll_deductibleSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="umbrella_policyGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Umbrella</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="umbrella_policySave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="umbrella_policyLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="umbrella_policyInput" id="umbrella_policyInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="umbrella_policySpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="statement_recordedGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Stmnt Rec</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="statement_recordedSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="statement_recordedSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="statement_recordedInput" id="statement_recordedInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="statement_recordedSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="client_statementGrid" data-row="7" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Client Stmnt</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="client_statementSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="client_statementSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="client_statementInput" id="client_statementInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="client_statementSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    </ul>
                 -->
                    </form>
                </div>
            </div>
            <!-- this is the defendant column -->
            <div style="float:right; border:0px solid red" id="defendant_car_information">
            	
                <div style="background:url(../img/glass_card_fade_12.png); font-size:1em" id="gridster_car_passenger">
                <form id="defendant_car_passenger_form" parsley-validate>
                    <input id="table_name" name="table_name" type="hidden" value="defendant_car_passenger" />
                    
                    <input id="defendant_outside_rear_left_quarter_panel"  name="defendant_outside_rear_left_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_rear_left_door"  name="defendant_outside_rear_left_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_middle_left_door"  name="defendant_outside_middle_left_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_front_left_door"  name="defendant_outside_front_left_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_left_side_mirror"  name="defendant_left_side_mirror" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_front_left_quarter_panel"  name="defendant_outside_front_left_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="defendant_rear_left_corner"  name="defendant_rear_left_corner" type="hidden" value="" class="input_class" />
                    <input id="defendant_rear_left_quarter_panel"  name="defendant_rear_left_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="defendant_rear_left_door"  name="defendant_rear_left_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_middle_left_door"  name="defendant_middle_left_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_front_left_door"  name="defendant_front_left_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_front_left_quarter_panel"  name="defendant_front_left_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_rear_bumper"  name="defendant_outside_rear_bumper" type="hidden" value="" class="input_class" />
                    
                    <input id="defendant_rear_bumper"  name="defendant_rear_bumper" type="hidden" value="" class="input_class" />
                    <input id="defendant_trunk"  name="defendant_trunk" type="hidden" value="" class="input_class" />
                    <input id="defendant_rear_left_seat"  name="defendant_rear_left_seat" type="hidden" value="" class="input_class" />
                    <input id="defendant_middle_left_seat"  name="defendant_middle_left_seat" type="hidden" value="" class="input_class" />
                    <input id="defendant_front_left_seat"  name="defendant_front_left_seat" type="hidden" value="" class="input_class" />
                    <input id="defendant_windshield"  name="defendant_windshield" type="hidden" value="" class="input_class" />
                    <input id="defendant_hood"  name="defendant_hood" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_hood"  name="defendant_outside_hood" type="hidden" value="" class="input_class" />
                    <input id="defendant_rear_middle_seat"  name="defendant_rear_middle_seat" type="hidden" value="" class="input_class" />
                    <input id="defendant_middle_middle_seat"  name="defendant_middle_middle_seat" type="hidden" value="" class="input_class" />
                    <input id="defendant_front_middle_seat"  name="defendant_front_middle_seat" type="hidden" value="" class="input_class" />
                    <input id="defendant_rear_right_seat"  name="defendant_rear_right_seat" type="hidden" value="" class="input_class" />
                    <input id="defendant_middle_right_seat"  name="defendant_middle_right_seat" type="hidden" value="" class="input_class" />
                    
                    <input id="defendant_front_right_seat"  name="defendant_front_right_seat" type="hidden" value="" class="input_class" />
                    <input id="defendant_rear_right_corner"  name="defendant_rear_right_corner" type="hidden" value="" class="input_class" />
                    <input id="defendant_rear_right_quarter_panel"  name="defendant_rear_right_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="defendant_rear_right_door"  name="defendant_rear_right_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_middle_right_door"  name="defendant_middle_right_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_front_right_door"  name="defendant_front_right_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_right_side_mirror"  name="defendant_right_side_mirror" type="hidden" value="" class="input_class" />
                    <input id="defendant_front_right_quarter_panel"  name="defendant_front_right_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_rear_right_quarter_panel"  name="defendant_outside_rear_right_quarter_panel" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_rear_right_door"  name="defendant_outside_rear_right_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_middle_right_door"  name="defendant_outside_middle_right_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_front_right_door"  name="defendant_outside_front_right_door" type="hidden" value="" class="input_class" />
                    <input id="defendant_outside_front_right_quarter_panel"  name="defendant_outside_front_right_quarter_panel" type="hidden" value="" class="input_class" />
            
                    <ul>
                <li id="carGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:10px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:; margin-left:-33px">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding:0px">
                                <tr>
                                    <td>
                                        <table style="padding:0px; font-size:0px" border="0" cellpadding="0" cellspacing="0">
                                                
                                              <tr>
                                               <td style="padding:0px">&nbsp;</td>
                                               <td style="padding:0px">&nbsp;</td>
                                               <td class="roll_over" id="defendant_outside_rear_left_quarter_panel" style="padding:0px"><img name="new_car_empty_r1_c3" src="../images/new_car_parts/new_car_empty_outside_rear_left_quarter_panel.png" width="71" height="22" border="0" id="outside_rear_left_quarter_panel" alt="" /></td>
                                               <td class="roll_over" id="defendant_outside_rear_left_door" style="padding:0px"><img name="new_car_empty_r1_c4" src="../images/new_car_parts/new_car_empty_outside_rear_left_door.png" width="59" height="22" border="0" id="outside_rear_left_door" alt="" /></td>
                                               <td class="roll_over" id="defendant_outside_middle_left_door" style="padding:0px"><img name="new_car_empty_r1_c5" src="../images/new_car_parts/new_car_empty_outside_middle_left_door.png" width="64" height="22" border="0" id="outside_middle_left_door" alt="" /></td>
                                               <td class="roll_over" id="defendant_outside_front_left_door" style="padding:0px"><img name="new_car_empty_r1_c6" src="../images/new_car_parts/new_car_empty_outside_front_left_door.png" width="68" height="22" border="0" id="outside_front_left_door" alt="" /></td>
                                               <td rowspan="2" class="roll_over" id="defendant_left_side_mirror" style="padding:0px"><img name="new_car_empty_r1_c7" src="../images/new_car_parts/new_car_empty_left_side_mirror.png" width="33" height="39" border="0" id="left_side_mirror" alt="" /></td>
                                               <td class="roll_over" id="defendant_outside_front_left_quarter_panel" style="padding:0px"><img name="new_car_empty_r1_c8" src="../images/new_car_parts/new_car_empty_outside_front_left_quarter_panel.png" width="89" height="22" border="0" id="outside_front_left_quarter_panel" alt="" /></td>
                                               <td style="padding:0px">&nbsp;</td>
                                               <td style="padding:0px"><img src="../images/new_car_parts/spacer.gif" width="1" height="0" border="0" alt="" /></td>
                                              </tr>
                                              <tr style="height:17px">
                                               <td style="padding:0px">&nbsp;</td>
                                               <td class="roll_over" id="defendant_rear_left_corner" style="padding:0px"><img name="new_car_empty_r2_c2" src="../images/new_car_parts/new_car_empty_rear_left_corner.png" width="23" height="17" border="0" id="rear_left_corner" alt="" /></td>
                                               <td class="roll_over" id="defendant_rear_left_quarter_panel" style="padding:0px"><img name="new_car_empty_r2_c3" src="../images/new_car_parts/new_car_empty_rear_left_quarter_panel.png" width="71" height="17" border="0" id="rear_left_quarter_panel" alt="" /></td>
                                               <td class="roll_over" id="defendant_rear_left_door" style="padding:0px"><img name="new_car_empty_r2_c4" src="../images/new_car_parts/new_car_empty_rear_left_door.jpg" width="59" height="17" border="0" id="rear_left_door" alt="" /></td>
                                               <td class="roll_over" id="defendant_middle_left_door" style="padding:0px"><img name="new_car_empty_r2_c5" src="../images/new_car_parts/new_car_empty_middle_left_door.jpg" width="64" height="17" border="0" id="middle_left_door" alt="" /></td>
                                               <td class="roll_over" id="defendant_front_left_door" style="padding:0px"><img name="new_car_empty_r2_c6" src="../images/new_car_parts/new_car_empty_front_left_door.jpg" width="68" height="17" border="0" id="front_left_door" alt="" /></td>
                                               <td class="roll_over" id="defendant_front_left_quarter_panel" style="padding:0px"><img name="new_car_empty_r2_c8" src="../images/new_car_parts/new_car_empty_front_left_quarter_panel.png" width="89" height="17" border="0" id="front_left_quarter_panel" alt="" /></td>
                                               <td style="padding:0px">&nbsp;</td>
                                               <td style="padding:0px"><img src="../images/new_car_parts/spacer.gif" width="1" height="0" border="0" alt="" /></td>
                                              </tr>
                                              <tr>
                                               <td rowspan="3" class="roll_over" id="defendant_outside_rear_bumper"><img name="new_car_empty_r3_c1" src="../images/new_car_parts/new_car_empty_outside_rear_bumper.png" width="25" height="106" border="0" id="outside_rear_bumper" alt="" /></td>
                                               <td rowspan="3" class="roll_over" id="defendant_rear_bumper"><img name="new_car_empty_r3_c2" src="../images/new_car_parts/new_car_empty_rear_bumper.png" width="23" height="106" border="0" id="rear_bumper" alt="" /></td>
                                               <td rowspan="3" class="roll_over" id="defendant_trunk"><img name="new_car_empty_r3_c3" src="../images/new_car_parts/new_car_empty_trunk.jpg" width="71" height="106" border="0" id="trunk" alt="" /></td>
                                               <td class="roll_over" id="defendant_rear_left_seat"><img name="new_car_empty_r3_c4" src="../images/new_car_parts/new_car_empty_rear_left_seat.jpg" width="59" height="36" border="0" id="rear_left_seat" alt="" /></td>
                                               <td class="roll_over" id="defendant_middle_left_seat"><img name="new_car_empty_r3_c5" src="../images/new_car_parts/new_car_empty_middle_left_seat.jpg" width="64" height="36" border="0" id="middle_left_seat" alt="" /></td>
                                               <td class="roll_over" id="defendant_front_left_seat"><img name="new_car_empty_r3_c6" src="../images/new_car_parts/new_car_empty_front_left_seat.jpg" width="68" height="36" border="0" id="front_left_seat" alt="" /></td>
                                               <td rowspan="3" class="roll_over" id="defendant_windshield"><img name="new_car_empty_r3_c7" src="../images/new_car_parts/new_car_empty_windshield.jpg" width="33" height="106" border="0" id="windshield" alt="" /></td>
                                               <td rowspan="3" class="roll_over" id="defendant_hood"><img name="new_car_empty_r3_c8" src="../images/new_car_parts/new_car_empty_hood.png" width="89" height="106" border="0" id="hood" alt="" /></td>
                                               <td rowspan="3" class="roll_over" id="defendant_outside_hood"><img name="new_car_empty_r3_c9" src="../images/new_car_parts/new_car_empty_outside_hood.png" width="28" height="106" border="0" id="outside_hood" alt="" /></td>
                                               <td><img src="../images/new_car_parts/spacer.gif" width="1" height="0" border="0" alt="" /></td>
                                              </tr>
                                              <tr>
                                               <td class="roll_over" id="defendant_rear_middle_seat"><img name="new_car_empty_r4_c4" src="../images/new_car_parts/new_car_empty_rear_middle_seat.jpg" width="59" height="35" border="0" id="rear_middle_seat" alt="" /></td>
                                               <td class="roll_over" id="defendant_middle_middle_seat"><img name="new_car_empty_r4_c5" src="../images/new_car_parts/new_car_empty_middle_middle_seat.jpg" width="64" height="35" border="0" id="middle_middle_seat" alt="" /></td>
                                               <td class="roll_over" id="defendant_front_middle_seat"><img name="new_car_empty_r4_c6" src="../images/new_car_parts/new_car_empty_front_middle_seat.jpg" width="68" height="35" border="0" id="front_middle_seat" alt="" /></td>
                                               <td><img src="../images/new_car_parts/spacer.gif" width="1" height="35" border="0" alt="" /></td>
                                              </tr>
                                              <tr>
                                               <td class="roll_over" id="defendant_rear_right_seat"><img name="new_car_empty_r5_c4" src="../images/new_car_parts/new_car_empty_rear_right_seat.jpg" width="59" height="35" border="0" id="rear_right_seat" alt="" /></td>
                                               <td class="roll_over" id="defendant_middle_right_seat"><img name="new_car_empty_r5_c5" src="../images/new_car_parts/new_car_empty_middle_right_seat.jpg" width="64" height="35" border="0" id="middle_right_seat" alt="" /></td>
                                               <td class="roll_over" id="defendant_front_right_seat"><img name="new_car_empty_r5_c6" src="../images/new_car_parts/new_car_empty_front_right_seat.jpg" width="68" height="35" border="0" id="front_right_seat" alt="" /></td>
                                               <td><img src="../images/new_car_parts/spacer.gif" width="1" height="35" border="0" alt="" /></td>
                                              </tr>
                                              <tr>
                                               <td>&nbsp;</td>
                                               <td class="roll_over" id="defendant_rear_right_corner"><img name="new_car_empty_r6_c2" src="../images/new_car_parts/new_car_empty_rear_right_corner.png" width="23" height="14" border="0" id="rear_right_corner" alt="" /></td>
                                               <td class="roll_over" id="defendant_rear_right_quarter_panel"><img name="new_car_empty_r6_c3" src="../images/new_car_parts/new_car_empty_rear_right_quarter_panel.png" width="71" height="14" border="0" id="rear_right_quarter_panel" alt="" /></td>
                                               <td class="roll_over" id="defendant_rear_right_door"><img name="new_car_empty_r6_c4" src="../images/new_car_parts/new_car_empty_rear_right_door.jpg" width="59" height="14" border="0" id="rear_right_door" alt="" /></td>
                                               <td class="roll_over" id="defendant_middle_right_door"><img name="new_car_empty_r6_c5" src="../images/new_car_parts/new_car_empty_middle_right_door.jpg" width="64" height="14" border="0" id="middle_right_door" alt="" /></td>
                                               <td class="roll_over" id="defendant_front_right_door"><img name="new_car_empty_r6_c6" src="../images/new_car_parts/new_car_empty_front_right_door.jpg" width="68" height="14" border="0" id="front_right_door" alt="" /></td>
                                               <td rowspan="2" class="roll_over" id="defendant_right_side_mirror"><img name="new_car_empty_r6_c7" src="../images/new_car_parts/new_car_empty_right_side_mirror.png" width="33" height="35" border="0" id="right_side_mirror" alt="" /></td>
                                               <td class="roll_over" id="defendant_front_right_quarter_panel"><img name="new_car_empty_r6_c8" src="../images/new_car_parts/new_car_empty_front_right_quarter_panel.png" width="89" height="14" border="0" id="front_right_quarter_panel" alt="" /></td>
                                               <td>&nbsp;</td>
                                               <td><img src="../images/new_car_parts/spacer.gif" width="1" height="14" border="0" alt="" /></td>
                                              </tr>
                                              <tr>
                                               <td>&nbsp;</td>
                                               <td>&nbsp;</td>
                                               <td class="roll_over" id="defendant_outside_rear_right_quarter_panel"><img name="new_car_empty_r7_c3" src="../images/new_car_parts/new_car_empty_outside_rear_right_quarter_panel.png" width="71" height="21" border="0" id="outside_rear_right_quarter_panel" alt="" /></td>
                                               <td class="roll_over" id="defendant_outside_rear_right_door"><img name="new_car_empty_r7_c4" src="../images/new_car_parts/new_car_empty_outside_rear_right_door.png" width="59" height="21" border="0" id="outside_rear_right_door" alt="" /></td>
                                               <td class="roll_over" id="defendant_outside_middle_right_door"><img name="new_car_empty_r7_c5" src="../images/new_car_parts/new_car_empty_outside_middle_right_door.png" width="64" height="21" border="0" id="outside_middle_right_door" alt="" /></td>
                                               <td class="roll_over" id="defendant_outside_front_right_door"><img name="new_car_empty_r7_c6" src="../images/new_car_parts/new_car_empty_outside_front_right_door.png" width="68" height="21" border="0" id="outside_front_right_door" alt="" /></td>
                                               <td class="roll_over" id="defendant_outside_front_right_quarter_panel"><img name="new_car_empty_r7_c8" src="../images/new_car_parts/new_car_empty_outside_front_right_quarter_panel.png" width="89" height="21" border="0" id="outside_front_right_quarter_panel" alt="" /></td>
                                               <td>&nbsp;</td>
                                               <td><img src="../images/new_car_parts/spacer.gif" width="1" height="21" border="0" alt="" /></td>
                                              </tr>
                                      </table>
                                    </td>
                                </tr>
                            </table>
                      </li>
                    </ul>
                  </form>
                </div>
                <div>&nbsp;</div>
                <div style="background:url(../img/glass_card_fade_19.png); padding:5px; border:0px solid white; width:auto;	" id="gridster_accident" class="gridster">
                    <form id="vehicle_form">
                    <input id="table_name" name="table_name" type="hidden" value="vehicle" />
                    <ul>
                        <li id="defendant_makeGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Make</div></h6>
                        <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="defendant_makeSave">
                            
                            <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="defendant_makeSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="defendant_makeInput" id="defendant_makeInput" class="kase <?php echo $form_name; ?> vehicle input_class hidden" style="margin-top:-25px; margin-left:85px; width:340px" tabindex="0" placeholder="Make" />
                          <span id="defendant_makeSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                        </li>
                      
                        <li id="defendant_modelGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> vehicle gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Model</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_modelSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_modelSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_modelInput" id="defendant_modelInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" style="margin-top:-25px; margin-left:85px; width:340px" tabindex="2" placeholder="Model" />
                      <span id="defendant_modelSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_yearGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> vehicle gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Year</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="defendant_yearSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_yearSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="defendant_yearInput" id="defendant_yearInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="Year" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                          <span id="defendant_yearSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                      </li>
                    <li id="defendant_license_plateGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> vehicle gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Tags</div></h6>
                    <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="defendant_license_plateSave">
                        <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="defendant_license_plateSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_license_plateInput" id="defendant_license_plateInput" class="<?php echo $form_name; ?> vehicle input_class hidden" style="width:70px; margin-top:-25px; margin-left:85px; width:100px" tabindex="0" placeholder="Tags" />
                      <span id="defendant_license_plateSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_vinGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">VIN</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_vinSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_vinSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_vinInput" id="defendant_vinInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="VIN" style="margin-top:-25px; margin-left:85px; width:340px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_vinSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_vehicle_descriptionGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Description</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_vehicle_descriptionSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_vehicle_descriptionSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_vehicle_descriptionInput" id="defendant_vehicle_descriptionInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="Description" style="margin-top:-25px; margin-left:85px; width:340px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_vehicle_descriptionSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    
                    <li id="defendant_vehicle_colorGrid" data-row="5" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Color</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_vehicle_colorSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_vehicle_colorSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_vehicle_colorInput" id="defendant_vehicle_colorInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_vehicle_colorSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_vehicle_typeGrid" data-row="5" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Type</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_vehicle_typeSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_vehicle_typeSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <select name="defendant_vehicle_typeInput" id="defendant_vehicle_typeInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden"style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" tabindex="1">
                      	<option value="">Select from list...</option>
                        <option value="car">Car</option>
                        <option value="truck">Truck</option>
                        <option value="motorcycle">Motorcycle</option>
                        <option value="bike">Bicycle</option>
                        <option value="bus">Bus</option>
                        <option value="rv">RV</option>
                        <option value="van">Van</option>
                        <option value="pedestrian">Pedestrian</option>
                        <option value="scooter">Scooter</option>
                        <option value="hoverboard">Hoverboard</option>
                        <option value="skateboard">Skateboard</option>
                        <option value="boat">Boat</option>
                      </select>
                      <span id="defendant_vehicle_typeSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_vehicle_commentsGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="3" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Additional Info</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_vehicle_commentsSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_vehicle_commentsSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <textarea name="defendant_vehicle_commentsInput" id="defendant_vehicle_commentsInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="Any additional info..." style="margin-left:85px; width:340px; height:100px; border:0px solid red" autocomplete="off" tabindex="1"></textarea>
                      <span id="defendant_vehicle_commentsSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_damagesGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Damages ($)</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_damagesSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_damagesSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input type="number" value="" name="defendant_damagesInput" id="defendant_damagesInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_damagesSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    
                    <li id="defendant_towingGrid" data-row="7" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Towing ($)</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_towingSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_towingSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input type="number" value="" name="defendant_towingInput" id="defendant_towingInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_towingSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    
                    <li id="defendant_storageGrid" data-row="8" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Storage ($)</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_storageSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_storageSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input type="number" value="" name="defendant_storageInput" id="defendant_storageInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_storageSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    
                    <li id="defendant_lossesGrid" data-row="8" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Losses ($)</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_lossesSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_lossesSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input type="number" value="" name="defendant_lossesInput" id="defendant_lossesInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_lossesSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_damage_detailsGrid" data-row="9" data-col="1" data-sizex="2" data-sizey="3" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Damage Details</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_damage_detailsSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_damage_detailsSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <textarea name="defendant_damage_detailsInput" id="defendant_damage_detailsInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="Details of damage" style="margin-left:85px; width:340px; height:100px; border:0px solid red" autocomplete="off" tabindex="1"></textarea>
                      <span id="defendant_damage_detailsSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                 </ul>
                 <ul style="display:none">
                    <li id="defendant_rental_coverageGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Rental Cov.</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_rental_coverageSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_rental_coverageSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_rental_coverageInput" id="defendant_rental_coverageInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:340px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_rental_coverageSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_rental_amountGrid" data-row="5" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Amount</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_rental_amountSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_rental_amountSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_rental_amountInput" id="defendant_rental_amountInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_rental_amountSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_rental_daysGrid" data-row="5" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Days</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_rental_daysSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_rental_daysSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_rental_daysInput" id="defendant_rental_daysInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_rental_daysSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_coverage_disputedGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Cov. Disputed</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_coverage_disputedSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_coverage_disputedLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" type="checkbox" name="defendant_coverage_disputedInput" id="defendant_coverage_disputedInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" style="margin-top:-25px; margin-left:45px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_coverage_disputedSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_liab_coll_onlyGrid" data-row="6" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Liab/Coll Only</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_liab_coll_onlySave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_liab_coll_onlySaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_liab_coll_onlyInput" id="defendant_liab_coll_onlyInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_liab_coll_onlySpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_vehicle_totaledGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Totaled</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_vehicle_totaledSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_vehicle_totaledSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input type="checkbox" value="" name="defendant_vehicle_totaledInput" id="defendant_vehicle_totaledInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:45px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_vehicle_totaledSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_coll_deductibleGrid" data-row="7" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Coll Deductible</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_coll_deductibleSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_coll_deductibleSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_coll_deductibleInput" id="defendant_coll_deductibleInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_coll_deductibleSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_umbrella_policyGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Umbrella</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_umbrella_policySave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_umbrella_policyLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_umbrella_policyInput" id="defendant_umbrella_policyInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_umbrella_policySpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_statement_recordedGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Stmnt Rec</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_statement_recordedSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_statement_recordedSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input type="checkbox" value="" name="defendant_statement_recordedInput" id="defendant_statement_recordedInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:45px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_statement_recordedSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>
                    </li>
                    <li id="defendant_client_statementGrid" data-row="7" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Client Stmnt</div></h6>
                    <div style="margin-top:-12px" class="save_holder hidden" id="defendant_client_statementSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_client_statementSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="defendant_client_statementInput" id="defendant_client_statementInput" class="<?php echo $form_name; ?> kase vehicle input_class hidden" placeholder="" style="margin-top:-25px; margin-left:85px; width:100px; border:0px solid red" autocomplete="off" tabindex="1" />
                      <span id="defendant_client_statementSpan" class="kase <?php echo $form_name; ?> vehicle span_class form_span_vert" style="margin-top:-28px; margin-left:26px"></span>
                    </li>
                    </ul>
                    </form>
                </div>
            </div>
    	</div>
    <% } %>
    
</div>

<div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:600px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
<div class="personal_injury" id="personal_injury_panel">
    <form id="personal_injury_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="personal_injury" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
    <input id="personal_injury_id" name="personal_injury_id" type="hidden" value="" />
    <input id="billing_time" name="billing_time" type="hidden" value="" />
    <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
    	<% kase_type = case_type; %>
		<?php 
        include("dashboard_view_navigation.php"); 
        ?>
    </div>
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">
    
        <ul style="margin-bottom:10px">
            <li id="personal_injury_dateGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Date/Time</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_dateSave">
                <a class="save_field" title="Click to save this field" id="personal_injury_dateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="personal_injury_dateInput" id="personal_injury_dateInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:75px; width:385px" autocomplete="off" parsley-error-message="Req" required />
              <span id="personal_injury_dateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:85px"></span>
        </li>
        
        <li id="personal_injury_dayGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Day</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_daySave">
                <a class="save_field" title="Click to save this field" id="personal_injury_daySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <span id="personal_injury_daySpan" class="<?php echo $form_name; ?> form_span_vert span_perm" style="margin-top:-26px; margin-left:75px; border:0px solid black""></span>
              <input type="hidden" value="" name="personal_injury_dayInput" id="personal_injury_dayInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Day" style="margin-left:75px;" />
              
        </li>
		<li id="personal_injury_timeGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Time</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_timeSave">
                <a class="save_field" title="Click to save this field" id="personal_injury_timeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <span id="personal_injury_timeSpan" class="<?php echo $form_name; ?> form_span_vert span_perm" style="margin-top:-26px; margin-left:75px; border:0px solid black"></span>
              <input type="hidden" value="" name="personal_injury_timeInput" id="personal_injury_timeInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Time" style="margin-left:75px" />
              
        </li>
		<li id="statute_limitationGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">Statute Limitation:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="full_addressSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="full_addressSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= statute_limitation %>" name="statute_limitationInput" id="statute_limitationInput" class="kase input_class hidden injury" style="margin-top:-26px; margin-left:120px; width:100px;z-index:3259; width:119px"  tabindex="6" />
                <span id="statute_limitationSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:120px">
                <%= statute_limitation %>
                <% statute_interval = Number(statute_interval); %>
                </span>
                <select name="statute_intervalInput" id="statute_intervalInput" class="modalInput task input_class hidden injury" style="height:25px; width:150px; margin-top:-26px; margin-left:290px"  tabindex="7">
                    <option value="1" <% if (statute_interval==1) { %>selected<% } %>>Expires in 1 year</option>
                    <option value="2" <% if (statute_years=="" || statute_years==2) { %>selected<% } %>>2 years</option>
                    <option value="3" <% if (statute_years==3) { %>selected<% } %>>3 years</option>
                    <option value="4" <% if (statute_years==4) { %>selected<% } %>>4 years</option>
                    <option value="5" <% if (statute_years==5) { %>selected<% } %>>5 years</option>
                    <option value="6" <% if (statute_years==6) { %>selected<% } %>>6 years</option>
                    <option value="7" <% if (statute_years==7) { %>selected<% } %>>7 years</option>
                    <option value="8" <% if (statute_years==8) { %>selected<% } %>>8 years</option>
                    <option value="9" <% if (statute_years==9) { %>selected<% } %>>9 years</option>
                    <option value="10" <% if (statute_years==10) { %>selected<% } %>>10 years</option>
                    <option value="11" <% if (statute_years>10) { %>selected<% } %>>10+ years</option>
                    <option value="-99" <% if (statute_years==-99) { %>selected<% } %>>No Limit</option>
                </select>
                <span id="statute_intervalSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:290px">
                <%= statute_years %> year<% if (statute_years > 1) { %>s<% } %>
                </span>
          </li>
          <!--
        <li id="personal_injury_loss_dateGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">DOL</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_loss_dateSave">
                <a class="save_field" title="Click to save this field" id="personal_injury_loss_dateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="personal_injury_loss_dateInput" id="personal_injury_loss_dateInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Date of Loss" style="margin-top:-26px; margin-left:75px; width:100px" />
              <span id="personal_injury_loss_dateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"></span>
        </li>
        -->
        <li id="personal_injury_locationGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Intersect 1</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_locationSave">
                <a class="save_field" title="Click to save this field" id="personal_injury_locationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="personal_injury_locationInput" id="personal_injury_locationInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Location" style="margin-top:-26px; margin-left:75px; width:385px" />
              <span id="personal_injury_locationSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:65px"></span>
              <div id="bing_results_1" style="position: absolute;z-index: 9999;background: aliceblue;border: 1px solid black;padding: 5px;color: black;left: 75px; display:none"></div>
        </li>
        
        <li id="personal_injury2_locationGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Intersect 2</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury2_locationSave">
                <a class="save_field" title="Click to save this field" id="personal_injury2_locationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="personal_injury2_locationInput" id="personal_injury2_locationInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Location" style="margin-top:-26px; margin-left:75px; width:385px" />
              <span id="personal_injury2_locationSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:65px"></span>
              <div id="bing_results_2" style="position: absolute;z-index: 9999;background: aliceblue;border: 1px solid black;padding: 5px;color: black;left: 75px; display:none"></div>
        </li>
        
        <li id="personal_injury_countyGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">County</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_countySave">
            <a class="save_field" title="Click to save this field" id="personal_injury_countySaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
                <input value="" name="personal_injury_countyInput" id="personal_injury_countyInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:75px; width:100px" />
          <span id="personal_injury_countySpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"></span>
        </li>
        <li id="personal_injury_mapGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Map</div></h6>
        <div id="show_accident_map" style="cursor:pointer;margin-top:-26px; margin-left:75px;"><img src="img/map.jpg" /></div>
        </li>
		<li id="personal_injury_accident_descriptionGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="10" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Accident Desc.</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_logonSave">
                <a class="save_field" title="Click to save this field" id="user_logonSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <textarea name="personal_injury_accident_descriptionInput" id="personal_injury_accident_descriptionInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:0px; margin-left:3px; width:445px; height:420px" ></textarea>
              <span id="personal_injury_accident_descriptionSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-6pxx"></span>
        </li>
        
        <li id="personal_injury_other_detailsGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="5" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Other Details</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_logonSave">
                <a class="save_field" title="Click to save this field" id="user_logonSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <textarea name="personal_injury_other_detailsInput" id="personal_injury_other_detailsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:0px; margin-left:3px; width:445px" rows="7"></textarea>
              <span id="personal_injury_other_detailsSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-6px;"></span>
        </li>
        <li id="personal_injury_accident_injuriesGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="5" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Injuries</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_logonSave">
                <a class="save_field" title="Click to save this field" id="user_logonSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <textarea name="personal_injury_accident_injuriesInput" id="personal_injury_accident_injuriesInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:0px; margin-left:3px; width:445px; height:190px" tabindex="4"></textarea>
              <span id="personal_injury_accident_injuriesSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-6px;"></span>
        </li>
        
        
        
        
        <li id="client_fault_percentageGrid" data-row="11" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Cl. Fault %</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="client_fault_percentageSave">
                <a class="save_field" title="Click to save this field" id="client_fault_percentageSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <input type="number" value="" name="client_fault_percentageInput" id="client_fault_percentageInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:70px; margin-top:-24px; width:145px" />
              <span id="client_fault_percentageSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:70px; border:0px solid black""></span>
        </li>
		<li id="premises_conditionGrid" data-row="11" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Conditions</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="premises_conditionSave">
                <a class="save_field" title="Click to save this field" id="premises_conditionSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <input type="text" value="" name="premises_conditionInput" id="premises_conditionInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:115px; margin-top:-24px; width:100px" />
              <span id="premises_conditionSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:70px; border:0px solid black"></span>
        </li>
        <li id="personal_injury_typeGrid" data-row="12" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Type</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_typeSave">
                <a class="save_field" title="Click to save this field" id="personal_injury_typeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <input type="text" value="" name="personal_injury_typeInput" id="personal_injury_typeInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:70px; margin-top:-24px; width:145px" />
              <span id="personal_injury_typeSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:70px; border:0px solid black""></span>
        </li>
		<li id="lightingGrid" data-row="12" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Lighting</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="lightingSave">
                <a class="save_field" title="Click to save this field" id="lightingSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <input type="text" value="" name="lightingInput" id="lightingInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:115px; margin-top:-24px; width:100px" />
              <span id="lightingSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:70px; border:0px solid black"></span>
        </li>
        <li id="injury_location_typeGrid" data-row="13" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Loc. Type</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="injury_location_typeSave">
                <a class="save_field" title="Click to save this field" id="injury_location_typeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <select name="injury_location_type" id="injury_location_type" class="<?php echo $form_name; ?> input_class hidden" style="margin-left:70px; margin-top:-24px; width:145px">
                <option value="">Choose One</option>
                <option value="commercial">Commercial Space</option>
                <option value="workplace">Workplace</option>
                <option value="health_facility">Health Facility</option>
                <option value="residence">Residence</option>
              </select>
              <span id="injury_location_typeSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black""></span>
        </li>
        <li id="government_propGrid" data-row="13" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Govt Prop</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="government_propSave">
                <a class="save_field" title="Click to save this field" id="government_propSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <input type="checkbox" value="" name="government_propInput" id="government_propInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
              <span id="government_propSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black""></span>
        </li>
        <li id="natureGrid" data-row="14" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Summary</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="natureSave">
                <a class="save_field" title="Click to save this field" id="natureSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input type="text" value="" name="natureInput" id="natureInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:65px; margin-top:-24px; width:145px" />
            <span id="natureSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px; border:0px solid black"></span>
              
              
        </li>
		<li id="controlsGrid" data-row="14" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Controls</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="controlsSave">
                <a class="save_field" title="Click to save this field" id="controlsSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input type="text" value="" name="controlsInput" id="controlsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:65px; margin-top:-24px; width:145px" />
            <span id="controlsSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px; border:0px solid black"></span>
              
              
        </li>
        
        <li id="weatherGrid" data-row="15" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Weather</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="weatherSave">
                <a class="save_field" title="Click to save this field" id="weatherSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input type="text" value="" name="weatherInput" id="weatherInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:65px; margin-top:-24px; width:145px" />
            <span id="weatherSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px; border:0px solid black"></span>
              
              
        </li>
        <li id="pi_otherGrid" data-row="15" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Other</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="pi_otherSave">
                <a class="save_field" title="Click to save this field" id="pi_otherSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input type="text" value="" name="pi_otherInput" id="pi_otherInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-left:65px; margin-top:-24px; width:145px" />
            <span id="pi_otherSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px; border:0px solid black"></span>
              
              
        </li>
       </ul>
        
    </div>
    
    
    </form>
</div></div>
<div>&nbsp;</div>
<div id="image_holder" style="margin-top:10px; border:0px solid yellow"></div>

<div id="personal_injury_done"></div>
<script language="javascript">
$("#personal_injury_done").trigger( "click" );
/* 

<li id="commercial_spaceGrid" data-row="13" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Commercial Space</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="commercial_spaceSave">
                <a class="save_field" title="Click to save this field" id="commercial_spaceSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <input type="checkbox" value="" name="commercial_spaceInput" id="commercial_spaceInput" class="input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
              <span id="commercial_spaceSpan" class="span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black""></span>
        </li>
		<li id="workplaceGrid" data-row="13" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Workplace</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="workplaceSave">
                <a class="save_field" title="Click to save this field" id="workplaceSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <input type="checkbox" value="" name="workplaceInput" id="workplaceInput" class="input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
              <span id="workplaceSpan" class="span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
        </li>
        <li id="health_facilityGrid" data-row="14" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Health Facility</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="health_facilitySave">
                <a class="save_field" title="Click to save this field" id="health_facilitySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <input type="checkbox" value="" name="health_facilityInput" id="health_facilityInput" class="input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
              <span id="health_facilitySpan" class="span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black""></span>
        </li>
		<li id="residenceGrid" data-row="14" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Residence</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="residenceSave">
                <a class="save_field" title="Click to save this field" id="residenceSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <input type="checkbox" value="" name="residenceInput" id="residenceInput" class="input_class hidden" placeholder="" style="margin-left:75px; margin-top:-20px" />
              <span id="residenceSpan" class="span_class form_span_vert" style="margin-top:-26px; margin-left:95px; border:0px solid black"></span>
        </li>

*/
</script>
</div>
