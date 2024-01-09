<div class="gridster accident_view accident" id="gridster_accident" style="display:none">
     <div style="background:url(img/glass_card_dark_long_2.png) left top repeat-y; padding:5px; width:1070px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
        <form id="accident_form" parsley-validate>
            <input id="table_name" name="table_name" type="hidden" value="accident" />
            <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
            <input id="accident_id" name="accident_id" type="hidden" value="<%= id %>" />
            <input id="accident_uuid" name="accident_uuid" type="hidden" value="<%= uuid %>" />
            <input id="accident_uuid" name="accident_uuid" type="hidden" value="<%= uuid %>" />
            <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
             <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
                <?php 
                $form_name = "accident"; 
                include("dashboard_view_navigation.php"); 
                ?>
            </div>
            <ul>
				<li class="top_grid gridster_border" data-row="1" data-col="9" data-sizex="1" data-sizey="9" style="display:none">
				</li>
				<li class="top_grid gridster_border" data-row="1" data-col="10" data-sizex="1" data-sizey="9" style="display:none">
				</li>
                <li id="client_speedGrid" data-row="1" data-col="1" data-sizex="3" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Client Speed</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="client_speedSave">
					
                    <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="client_speedSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="" name="client_speedInput" id="client_speedInput" class="input_class hidden" style="width:70px; margin-top:-25px; margin-left:85px" tabindex="0" />
                  <span id="client_speedSpan" class="accident accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:65px"></span>
                </li>
                <li id="client_directionGrid" data-row="1" data-col="4" data-sizex="3" data-sizey="1" class="accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Direction</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="client_directionSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="client_directionSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="" name="client_directionInput" id="client_directionInput" class="kase accident_view input_class hidden" placeholder="Direction" style="margin-top:-25px; margin-left:60px; width:80px; border:1px solid red" parsley-error-message="Req" required autocomplete="off" tabindex="1" />
                  <span id="client_directionSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-25px; margin-left:60px"></span>
                </li>
				<li id="client_laneGrid" data-row="1" data-col="7" data-sizex="2" data-sizey="1" class="person gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Lane</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="client_laneSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="client_laneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="client_laneInput" id="client_laneInput" class="kase accident_view input_class hidden" placeholder="Lane" style="margin-top:-25px; margin-left:30px; width:70px" tabindex="2" />
              <span id="client_laneSpan" class="kase person_view span_class form_span_vert" style="margin-top:-30px; margin-left:40px"></span>
            </li>
			<li id="defendant_speedGrid" data-row="2" data-col="1" data-sizex="3" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Defendant Speed</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="defendant_speedSave">
                <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="defendant_speedSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="defendant_speedInput" id="defendant_speedInput" class="input_class hidden" style="width:70px; margin-top:-25px; margin-left:85px" tabindex="0" />
              <span id="client_speedSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:0px"></span>
            </li>
            <li id="defendant_directionGrid" data-row="2" data-col="4" data-sizex="3" data-sizey="1" class="accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Direction</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="defendant_directionSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_directionSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="defendant_directionInput" id="defendant_directionInput" class="kase accident_view input_class hidden" placeholder="Direction" style="margin-top:-26px; margin-left:60px; width:80px; border:1px solid red" parsley-error-message="Req" required autocomplete="off" tabindex="1" />
              <span id="defendant_directionSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-25px; margin-left:60px"></span>
            </li>
			<li id="client_laneGrid" data-row="2" data-col="7" data-sizex="2" data-sizey="1" class="accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Lane</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="client_laneSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="client_laneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="defendant_laneInput" id="defendant_laneInput" class="kase accident_view input_class hidden" placeholder="Lane" style="margin-top:-25px; margin-left:30px; width:70px" tabindex="2" />
              <span id="defendant_laneSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-30px; margin-left:40px"></span>
            </li>
			<li id="accident_dateGrid" data-row="3" data-col="1" data-sizex="8" data-sizey="1" class="accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Date</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="accident_dateSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="accident_dateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= accident_date %>" name="accident_dateInput" id="accident_dateInput" class="kase accident_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:85px; width:370px" tabindex="2" />
              <span id="accident_dateSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-30px; margin-left:85px"><%= accident_date %></span>
            </li>
            <li id="accident_locationGrid" data-row="4" data-col="1" data-sizex="8" data-sizey="3" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Address</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="accident_locationSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="accident_locationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="" name="accident_locationInput" id="accident_locationInput" class="kase input_class hidden accident" style="margin-top:-26px; margin-left:85px; width:370px" />
            <span id="accident_locationSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:85px">
            
            </span>
			<div style="border:0px solid green">
                    	<div style="border:0px solid blue; position:absolute; left:90px; top:30px; margin-top:10px"><input class="kase accident_view input_class hidden" id="street_accident" value="" style="border: 0px solid red; width:370px;" /></div>&nbsp;&nbsp;<br />
                        <div style="border:0px solid yellow; position:absolute; left:90px; width:370px; margin-left:-85px; margin-top:31px"><input value="" name="suiteInput" id="suiteInput" class="kase accident_view input_class hidden" style="margin-top:-26px; margin-left:85px;border: 0px solid red; width:370px;" /></div><br />&nbsp;&nbsp;
                        <div style="border:0px solid purple; position:absolute; left:90px; width:200px; margin-top: 0px; margin-left:0px"><input class="kase accident_view input_class hidden" id="city_accident"style="width:100px; border: 0px solid red; " value="" /></div>&nbsp;<div style="border:0px solid pink; position:absolute; left:0px; top:0px; width:100px; margin-top:94px; margin-left:200px"><input class="kase accident_view input_class hidden"
              id="administrative_area_level_1_accident" style="width:30px;border: 0px solid red" value="" />&nbsp;&nbsp;<div style="border:0px solid orange; position:absolute; left:0px; top:0px; width:100px; margin-top:0px; margin-left:65px"><input class="kase accident_view input_class hidden" id="postal_code_accident" style="width:50px" value="" />
              </div>
                    </div>
            </li>
			<li id="client_streetGrid" data-row="5" data-col="1" data-sizex="8" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Client Street</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="client_streetSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="client_streetSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="client_streetInput" id="client_streetInput" class="kase accident_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:85px; width:370px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
              <span id="client_streetSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:85px"></span>
            </li>
			
			
			
            <li id="defendant_streetGrid" data-row="1" data-col="11" data-sizex="8" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Defendant Street</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="defendant_streetSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="defendant_streetSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="defendant_streetInput" id="defendant_streetInput" class="kase accident_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:85px; width:370px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
              <span id="defendant_streetSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:85px"></span>
            </li>
            <li id="road_conditionsGrid" data-row="2" data-col="11" data-sizex="8" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Road Conditions</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="road_conditionsSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="road_conditionsSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="road_conditionsInput" id="road_conditionsInput" class="kase accident_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:85px; width:370px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
              <span id="road_conditionsSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:85px"></span>
            </li>
			<li id="traffic_controlsGrid" data-row="3" data-col="11" data-sizex="8" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Traffic Controls</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="traffic_controlsSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="traffic_controlsSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="traffic_controlsInput" id="traffic_controlsInput" class="kase accident_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:85px; width:370px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
              <span id="traffic_controlsSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:85px"></span>
            </li>
            <li id="accident_weatherGrid" data-row="4" data-col="11" data-sizex="8" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Weather</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="accident_weatherSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="accident_weatherSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="accident_weatherInput" id="accident_weatherInput" class="kase accident_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:85px; width:370px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
              <span id="accident_weatherSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:85px"></span>
            </li>
            
            
            <li id="other_detailsGrid" data-row="5" data-col="11" data-sizex="8" data-sizey="3" class="accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Other Details</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-16px" class="hidden" id="accident_descriptionSave">
                <a class="save_field" style="margin:0px;" title="Click to save this field" id="accident_descriptionSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <textarea name="other_detailsInput" id="other_detailsInput" style="margin-top:0px; margin-left:0px; width:455px" rows="4" class="kase input_class hidden"></textarea>
            <span id="other_detailsSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:85px">
            
            </span>
            </li>
			<li id="accident_descriptionGrid" data-row="6" data-col="11" data-sizex="8" data-sizey="2" class="accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Accident Description</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-16px" class="hidden" id="accident_descriptionSave">
                <a class="save_field" style="margin:0px;" title="Click to save this field" id="accident_descriptionSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <textarea name="accident_descriptionInput" id="accident_descriptionInput" style="margin-top:0px; margin-left:0px; width:455px" rows="2" class="kase input_class hidden"></textarea>
            <span id="accident_descriptionSpan" class="kase accident_view span_class form_span_vert" style="margin-top:-28px; margin-left:105px">
            
            </span>
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="1" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="2" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="3" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="4" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="5" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="6" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="7" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="8" data-sizex="1" data-sizey="1" style="display:none">
            </li>
			
            <li class="top_grid gridster_border" data-row="9" data-col="11" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="12" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="13" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="14" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="15" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="16" data-sizex="1" data-sizey="1" style="display:none">
            </li>
            <li class="top_grid gridster_border" data-row="9" data-col="17" data-sizex="1" data-sizey="1" style="display:none">
            </li>
			<li class="top_grid gridster_border" data-row="9" data-col="18" data-sizex="1" data-sizey="1" style="display:none">
            </li>
			
            </ul>
        </form><br/>
	</div>
</div>
<div id="addressGrid" style="display:">
    <table id="address">
      <tr style="display:none">
        <td class="label">Street address</td>
        <td class="slimField"><input class="field" id="street_number_person"
              disabled="true"></input></td>
        <td class="wideField" colspan="2"><input class="field" id="route_person"
              disabled="true"></input></td>
      </tr>
      <tr>
        <td class="wideField" colspan="4" style="display:none">
            <input class="field" id="street_person" value=""></input>&nbsp;<input class="field" id="city_person"style="width:100px" value=""></input>&nbsp;<input class="field"
              id="administrative_area_level_1_person" style="width:30px" value=""></input>&nbsp;<input class="field" id="postal_code_person"
               style="width:50px" value=""></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">City</td>
        <td class="wideField" colspan="3">
            <input class="field" id="locality_person"
              disabled="true"></input>
            <input class="field" id="sublocality_person"
              disabled="true"></input>
              <input class="field" id="neighborhood_person"
              disabled="true"></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">Country</td>
        <td class="wideField" colspan="3"><input class="field"
              id="country_person" disabled="true"></input></td>
      </tr>
    </table>
</div>
<div id="accident_all_done"></div>
<script language="javascript">
$( "#accident_all_done" ).trigger( "click" );
</script>