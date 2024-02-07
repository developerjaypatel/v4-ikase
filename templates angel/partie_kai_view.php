<div class="gridster partie_kai_view partie_kai" id="gridster_kai">
     <div style="background:url(img/glass_card_dark_4.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    	<form id="partie_kai_form" parsley-validate>
        <input id="id" name="id" type="hidden" value="<%= id %>" />
        <input id="table_name" name="table_name" type="hidden" value="person" />
        <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
        <input id="person_uuid" name="person_uuid" type="hidden" value="<%= uuid %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">       
			<?php 
            $form_name = "partie_kai"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <div class="gridster partie_kai kase" id="gridster_kai">
            <ul>
				<li id="rating_ageGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Rating Age</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="rating_ageSave">
                    <a class="save_field" title="Click to save this field" id="rating_ageSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <span id="rating_ageSpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px"></span>
                </li>
                <li id="ageGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Age</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="ageSave">
                    <a class="save_field" title="Click to save this field" id="ageSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="ageInput" style="margin-top:-26px; margin-left:60px" id="ageInput" class="kai input_class hidden kase" placeholder="Please enter DOB" />
                <span id="ageSpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px"></span>
                </li>
                 <li id="genderGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ;">
                <h6><div class="form_label_vert" style="margin-top:10px;">Gender</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="genderSave">
                    <a class="save_field" title="Click to save this field" id="genderSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <select name="genderInput" id="genderInput" class="kai input_class hidden kase" style="height:25px; width:150px; margin-top:-30px; margin-left:60px">
                  <option value="" selected="selected">Gender</option>
                  <option value="F">Female</option>
                  <option value="M">Male</option>
                </select>
                  <span id="genderSpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px"></span>
                </li>
               	 <li id="marital_statusGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1"  class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ;">
                <h6><div class="form_label_vert" style="margin-top:10px;">Marital</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="marital_statusSave">
                    <a class="save_field" title="Click to save this field" id="marital_statusSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <select name="marital_statusInput" id="marital_statusInput" class="kai input_class hidden kase" style="height:25px; width:150px; margin-top:-28px; margin-left:60px">
                  <option value="" selected="selected">Select from List</option>
                  <option value="Single">Single</option>
                  <option value="Married">Married</option>
                  <option value="Seperated">Seperated</option>
                  <option value="Divorced">Divorced</option>
                  <option value="Widowed">Widowed</option>
                </select>
                <span id="marital_statusSpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px">
                </span>
                </li> 
                <li id="license_numberGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ;">
                
                <h6><div class="form_label_vert" style="margin-top:10px;">License #</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="marital_statusSave">
                    <a class="save_field" title="Click to save this field" id="marital_statusSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="license_numberInput" id="license_numberInput" style="margin-top:-26px; margin-left:60px" class="kai input_class hidden" />
                <span id="license_numberSpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px"></span></h6>
                </li>
                <li id="languageGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ;">
                <h6><div class="form_label_vert" style="margin-top:10px;">Language</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="languageSave">
                    <a class="save_field" title="Click to save this field" id="languageSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <select name="languageInput" id="languageInput" class="kai input_class hidden kase" style="height:25px; width:150px; margin-top:-28px; margin-left:60px">
                  <?php include("../api/partie_language_options.php"); ?>
                </select>
                  <span id="languageSpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px"></span>
                </li>
                
                <li id="birth_cityGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Birth City</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="birth_citySave">
                    <a class="save_field" title="Click to save this field" id="birth_citySaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="birth_cityInput" id="birth_cityInput" style="margin-top:-26px; margin-left:60px" class="kai input_class hidden kase" />
                <span id="birth_citySpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px">
                </span>
                </li>
                
                                
                <li id="birth_stateGrid" data-row="4" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Birth State</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="birth_stateSave">
                    <a class="save_field" title="Click to save this field" id="birth_stateSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="birth_stateInput" id="birth_stateInput" style="margin-top:-26px; margin-left:60px" class="kai input_class hidden kase" />
                <span id="birth_stateSpan" class="kai form_span_vert span_class kase" style="margin-top:-25px; margin-left:70px">
                </span>
                </li>
                <li id="legal_statusGrid" data-row="5" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ;">
                <h6><div class="form_label_vert" style="margin-top:10px;">Legal</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="legal_statusSave">
                    <a class="save_field" title="Click to save this field" id="legal_statusSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <select name="legal_statusInput" id="legal_statusInput" class="kai input_class hidden kase" style="height:25px; width:150px; margin-top:-30px; margin-left:60px">
                  <option value="" selected="selected">Select from List</option>
                  <option value="Citizen">Citizen</option>
                  <option value="Dual Citizen">Dual Citizen</option>
                  <option value="Naturalized">Naturalized</option>
                  <option value="Resident">Resident</option>
                  <option value="Alien">Alien</option>
                  <option value="Illegal Immigrant">Illegal Immigrant</option>
                  <option value="Migrant Worker">Migrant Worker</option>
                  <option value="Refugee">Refugee</option>
                </select>
                  <span id="legal_statusSpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px"></span>
                </li>
                <li id="priority_flagGrid" data-row="5" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ;">
                <h6><div class="form_label_vert" style="margin-top:10px;">Status</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="priority_flagSave">
                    <a class="save_field" title="Click to save this field" id="marital_statusSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="priority_flagInput" id="priority_flagInput" style="margin-top:-26px; margin-left:60px" class="kai input_class hidden" />
                <span id="priority_flagSpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px">
                </span>
                </li>
                <li id="spouseGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Spouse</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="spouseSave">
                    <a class="save_field" title="Click to save this field" id="spouseSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="spouseInput" id="spouseInput" style="margin-top:-26px; margin-left:60px" class="kase kai input_class hidden" />
                <span id="spouseSpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px">
                </span>
                </li>
                <li id="spouse_contactGrid" data-row="6" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Contact</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="spouse_contactSave">
                    <a class="save_field" title="Click to save this field" id="spouse_contactSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="spouse_contactInput" id="spouse_contactInput" style="margin-top:-26px; margin-left:60px" class="kase kai input_class hidden" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
                <span id="spouse_contactSpan" class="kai form_span_vert span_class kase" style="margin-top:-25px; margin-left:70px">
                </span>
                </li>
                
                <li id="emergencyGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Emergency</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="emergencySave">
                    <a class="save_field" title="Click to save this field" id="emergencySaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="emergencyInput" id="emergencyInput" style="margin-top:-26px; margin-left:60px" class="kase kai input_class hidden" />
                <span id="emergencySpan" class="kase kai span_class kase form_span_vert" style="margin-top:-25px; margin-left:70px">
                </span>
                </li>
                <li id="emergency_contactGrid" data-row="7" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Emer. CTC</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="emergency_contactSave">
                    <a class="save_field" title="Click to save this field" id="emergency_contactSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="emergency_contactInput" id="emergency_contactInput" style="margin-top:-26px; margin-left:60px" class="kase kai input_class hidden" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
                <span id="emergency_contactSpan" class="kai form_span_vert span_class kase" style="margin-top:-25px; margin-left:70px">
                </span>
                </li>
           </ul>
        </div>
        </form>
	</div>
</div>    
<% if (gridster_me || grid_it) { %>
<script language="javascript">
setTimeout(function() {
	gridsterById('gridster_kai');
}, 10);
</script>
<% } %>