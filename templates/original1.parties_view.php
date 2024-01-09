<?php 
include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$query = "SELECT * FROM `cse_partie_type` 
WHERE 1
ORDER BY blurb ASC";

$result = mysql_query($query, $link) or die("unable to get parties type<br>" . $query);
$numbs = mysql_numrows($result);
$arrTypeOptions = array();
for ($x=0;$x<$numbs;$x++) {
	$partie_type_id = mysql_result($result, $x, "partie_type_id");
	$partie_type = mysql_result($result, $x, "partie_type");
	$blurb = mysql_result($result, $x, "blurb");
	$color = mysql_result($result, $x, "color");
	$option = "<option value='" . $blurb . "' <% if (type=='" . $blurb . "') { %>selected<% } %>>" . $partie_type . "</option>";
	//$option = "<option value='" . $blurb . "'>" . $partie_type . "</option>";
	$arrTypeOptions[] = $option;
}
$form_name = "parties"; include("edit_view_navigation.php"); 
?>  
<div class="active fade in glass_header_no_padding">
	<div style="text-align:left; margin-top:13px;">
         <div style="z-index:2356">
	        <span class="alert alert-success" style="display:none; float:right; height:35px; width:300px;font-size:14px; z-index:3356; margin-top:-45px;">Saved</span>
        </div>
	</div>
    <div style="">
        <div class="container" style="border:0px solid red; margin:0px; padding:0px">
            <div class="parties col-md-5" id="employer_holder" style="margin-top:10px; border:0px solid white">
            </div>	
        </div>
    </div>
</div>
<!-- <div class="<?php echo $form_name; ?>">
    	<form id="<?php echo $form_name; ?>_form" parsley-validate>
        <input id="table_name" name="table_name" type="hidden" value="<?php echo $form_name; ?>" />
        <input id="table_id" name="table_id" type="hidden" value="<%= <?php echo $form_name; ?>_id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
        <div class="gridster kase" id="gridster_<?php echo $form_name; ?>" style="display:none">
            <ul>
                <li id="firmGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;"><% if (type=="attorney") { %>Firm<% } else { %>Company<% } %></div></h6>
                  <div style="" class="save_holder hidden" id="firmSave">
                    <a class="save_field" title="Click to save this field" id="firmSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="<%= firm %>" name="firmInput" id="firmInput" class="kase <?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Firm is a required field" style="margin-top:-26px; margin-left:60px" size="50" required />
                  <span id="firmSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%= firm %></span>            
                </li>
            <li id="taxidGrid" data-row="1" data-col="4" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Tax ID</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="taxidSave">
                    <a class="save_field" title="Click to save this field" id="taxidSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="<%= taxid %>" name="taxidInput" id="taxidInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Tax ID is a required field" style="margin-top:-26px; margin-left:60px" size="50" required />
                  <span id="taxidSpan" class="kase span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%= taxid %></span>
                </li>
                <li id="phoneGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Phone</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="phoneSave">
                    <a class="save_field" title="Click to save this field" id="phoneSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="<%= phone %>" name="phoneInput" style="margin-top:-26px; margin-left:60px" id="phoneInput" class="kase input_class hidden" />
                  <span id="phoneSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-30px; margin-left:60px">
                  <%= phone %>
                  </span>
                </li>                
                <li id="extensionGrid" data-row="2" data-col="4" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Extension</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="extensionSave">
                    <a class="save_field" title="Click to save this field" id="extensionSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= extension %>" name="extensionInput" id="extensionInput" style="margin-top:-26px; margin-left:60px" class="kase input_class hidden" />
                <span id="extensionSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-30px; margin-left:60px">
                <%= extension %>
                </span>
                </li>
                <li id="faxGrid" data-row="2" data-col="3" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Fax</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="faxSave">
                    <a class="save_field" title="Click to save this field" id="faxSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="<%= fax %>" name="faxInput" id="faxInput" style="margin-top:-26px; margin-left:60px" class="kase input_class hidden" />
                  <span id="faxSpan" class="kase span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%= fax %></span>
                </li>
                <li id="emailGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Email</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="emailSave">
                    <a class="save_field" title="Click to save this field" id="emailSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= email %>" name="emailInput" id="emailInput" style="margin-top:-26px; margin-left:60px" class="kase input_class hidden" />
                <span id="emailSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-30px; margin-left:60px">
                <%= email %>
                </span>
                </li>
                <li id="party_nameGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Party Name</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="party_nameSave">
                    <a class="save_field" title="Click to save this field" id="party_nameSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= party_name %>" name="party_nameInput" id="party_nameInput" style="margin-top:-26px; margin-left:60px" class="kase input_class hidden" />
                <span id="party_nameSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-30px; margin-left:60px">
                <%= party_name %>
                </span>
                </li>
                <li id="representGrid" data-row="1" data-col="3" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Represent</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="representSave">
                    <a class="save_field" title="Click to save this field" id="representSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= represent %>" name="representInput" id="representInput" style="margin-top:-26px; margin-left:60px" class="kase input_class hidden" />
                <span id="representSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-30px; margin-left:60px">
                <%= represent %>
                </span>
                </li>
                <li id="zipGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Zip</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="zipSave">
                    <a class="save_field" title="Click to save this field" id="zipSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= zip %>" name="zipInput" style="margin-top:-26px; margin-left:60px" id="zipInput" class="kase input_class hidden zip" />
                <span id="zipSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-30px; margin-left:60px">
                <%= zip %>
                </span>
                </li>
                <li id="streetGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Street</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="streetSave">
                    <a class="save_field" title="Click to save this field" id="streetSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= street %>" name="streetInput" id="streetInput" style="margin-top:-26px; margin-left:60px" class="kase input_class hidden" />
                <span id="streetSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-30px; margin-left:60px">
                <%= street %>
                </span></h6>
                </li>
                
                <li id="stateGrid" data-row="5" data-col="3" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">City/State</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="stateSave">
                    <a class="save_field" title="Click to save this field" id="stateSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <div style="display:inline-block; width:60%">
                <div style="float:left; margin-top:-12px">
                <input value="<%= city %>" name="cityInput" id="cityInput" style="margin-top:-26px; margin-left:60px; width:35px" class="kase input_class hidden"  />
                  <span id="citySpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-31px; margin-left:55px">
                  <%= city %>
                  </span>
                  </div>
                  <div style="float:right; margin-left:0px; margin-top:-12px; display:">
                <input value="<%= state %>" name="stateInput" id="stateInput" style="margin-top:-26px; margin-left:0px; width:35px" class="kase input_class hidden" />
                <span id="stateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-31px; margin-left:40px">
                <%= state %>
                </span>
                </div>
                </div>
                </li>
                
                <li id="typeGrid" data-row="4" data-col="2" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Type</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="typeSave">
                    <a class="save_field" title="Click to save this field" id="typeSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <select name="typeInput" id="typeInput" class="input_class hidden kase" style="margin-top:-31px; margin-left:60px; height:30px; width:180px">
                  <option value="" <% if (type=="") { %>selected<% } %>>Select from List</option>
                  <? echo implode("\r\n", $arrTypeOptions); ?>
                </select>
                <span id="typeSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-30px; margin-left:60px">
                <%= type %>
                </span>
                </li>
           </ul>
        </div>
        </form>
        <form id="<?php echo $form_name; ?>_kai_form" parsley-validate>
        <input id="table_name" name="table_name" type="hidden" value="<?php echo $form_name; ?>" />
        <input id="table_id" name="table_id" type="hidden" value="<%= <?php echo $form_name; ?>_id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
        <% if (type=="employer") { %>
        	<hr />
            <span style="color:#FFFFFF; font-size:1.6em; font-weight:lighter; margin-left:10px;">Employer</span>
            <div class="gridster kase" id="gridster_<?php echo $form_name; ?>" style="display:">
            <ul>
                <li id="supervisorGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert">Supervisor</div></h6>
                      <div style="" class="save_holder hidden" id="supervisorSave">
                        <a class="save_field" title="Click to save this field" id="supervisorSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="supervisorInput" id="supervisorInput" class="kase <?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Firm is a required field" style="margin-top:-2px;" size="50" required />
                      <span id="supervisorSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:0px"></span>            
                    </li>
                <li id="salutationGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert">Salutation</div></h6>
                    <div style="float:right; margin-right:10px" class="hidden" id="salutationSave">
                        <a class="save_field" title="Click to save this field" id="salutationSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="salutationInput" id="salutationInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Tax ID is a required field" style="margin-top:-2px;" size="50" required />
                      <span id="salutationSpan" class="kase span_class form_span_vert"></span>
                    </li>
                
                    <li id="emp_dateGrid" data-row="2" data-col="1" data-sizex="3" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert">Empl Dates</div></h6>
                    <div style="float:right; margin-right:10px" class="hidden" id="emp_dateSave">
                        <a class="save_field" title="Click to save this field" id="emp_dateSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input value="" name="emp_dateInput" id="emp_dateInput" style="margin-top:-2px" class="kase input_class hidden" />
                    <span id="emp_dateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:0px">
                    
                    </span>
                    </li>  
            </ul>
            </div>
        <% } %>
        <% if (type=="defentdant") { %>
        	<hr />
            <span style="color:#FFFFFF; font-size:1.6em; font-weight:lighter; margin-left:10px;">Defendant</span>
            <div class="gridster kase" id="gridster_<?php echo $form_name; ?>" style="display:">
            <ul>
                <li id="supervisorGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert">Insurance</div></h6>
                      <div style="" class="save_holder hidden" id="supervisorSave">
                        <a class="save_field" title="Click to save this field" id="supervisorSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="supervisorInput" id="supervisorInput" class="kase <?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Firm is a required field" style="margin-top:-2px;" size="50" required />
                      <span id="supervisorSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:0px"></span>            
                    </li>
                <li id="attorneyGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert">Attorney</div></h6>
                    <div style="float:right; margin-right:10px" class="hidden" id="attorneySave">
                        <a class="save_field" title="Click to save this field" id="attorneySaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="attorneyInput" id="attorneyInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Tax ID is a required field" style="margin-top:-2px;" size="50" required />
                      <span id="attorneySpan" class="kase span_class form_span_vert"></span>
                </li>  
            </ul>
            </div>
        <% } %>
        <% if (type=="attorney") { %>
        	<hr />
            <span style="color:#FFFFFF; font-size:1.6em; font-weight:lighter; margin-left:10px;">Attorney</span>
            <div class="gridster kase" id="gridster_<?php echo $form_name; ?>" style="display:">
            <ul>
                <li id="supervisorGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert">Attorney</div></h6>
                      <div style="" class="save_holder hidden" id="supervisorSave">
                        <a class="save_field" title="Click to save this field" id="supervisorSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="supervisorInput" id="supervisorInput" class="kase <?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Firm is a required field" style="margin-top:-2px;" size="50" required />
                      <span id="supervisorSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:0px"></span>            
                    </li>
                <li id="salutationGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert">Insurance Company</div></h6>
                    <div style="float:right; margin-right:10px" class="hidden" id="salutationSave">
                        <a class="save_field" title="Click to save this field" id="salutationSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="salutationInput" id="salutationInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Tax ID is a required field" style="margin-top:-2px;" size="50" required />
                      <span id="salutationSpan" class="kase span_class form_span_vert"></span>
                    </li>
                	<li id="bar_numberGrid" data-row="1" data-col="3" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert">Bar #</div></h6>
                <div style="float:right; margin-right:10px" class="hidden" id="bar_numberSave">
                    <a class="save_field" title="Click to save this field" id="bar_numberSaveLink">
	                    <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= bar_number %>" name="bar_numberInput" id="bar_numberInput" style="margin-top:-2px" class="kase input_class hidden" />
                <span id="bar_numberSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:0px">
                <%= bar_number %>
                </span>
                </li>
                    
            </ul>
            </div>
        <% } %>
        <% if (type=="witness") { %>
        	<hr />
            <span style="color:#FFFFFF; font-size:1.6em; font-weight:lighter; margin-left:10px;">Witness</span>
            <div class="gridster kase" id="gridster_<?php echo $form_name; ?>" style="display:">
            <ul>
                <li id="relationGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert">Relation</div></h6>
                      <div style="" class="save_holder hidden" id="relationSave">
                        <a class="save_field" title="Click to save this field" id="relationSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="relationInput" id="relationInput" class="kase <?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Firm is a required field" style="margin-top:-2px;" size="50" required />
                      <span id="relationSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:0px"></span>            
                    </li>
                <li id="representsGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert">Represents</div></h6>
                    <div style="float:right; margin-right:10px" class="hidden" id="representsSave">
                        <a class="save_field" title="Click to save this field" id="representsSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="representsInput" id="representsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Tax ID is a required field" style="margin-top:-2px;" size="50" required />
                      <span id="representsSpan" class="kase span_class form_span_vert"></span>
                    </li>
                	
                    <li id="emp_dateGrid" data-row="1" data-col="3" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert">Date</div></h6>
                    <div style="float:right; margin-right:10px" class="hidden" id="emp_dateSave">
                        <a class="save_field" title="Click to save this field" id="emp_dateSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input value="" name="emp_dateInput" id="emp_dateInput" style="margin-top:-2px" class="kase input_class hidden" />
                    <span id="emp_dateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:0px">
                    
                    </span>
                    </li>  
            </ul>
            </div>
        <% } %>
        </form>
	</div> -->  