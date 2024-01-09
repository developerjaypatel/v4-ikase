<div class="gridster applicant_view" id="gridster_tall" style="display:">
     <div style="background:url(img/glass_<%=glass %>.png) left top no-repeat; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="applicant_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="person" />
        <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
        <input id="person_uuid" name="person_uuid" type="hidden" value="<%= uuid %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "applicant"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
            <li id="fullnameGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; border:#FF0000">
            <h6><div class="form_label_vert" style="margin-top:10px;">Full Name</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="full_nameSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="full_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= full_name %>" name="full_nameInput" id="full_nameInput" class="kase applicant_view input_class hidden" placeholder="Full Name" style="margin-top:-26px; margin-left:65px"  parsley-error-message="" required />
              <span id="full_nameSpan" class="kase applicant_view span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%= full_name %></span>
            </li>
            <li id="salutationGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Salutation</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="salutationSave">
                <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="salutationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <select name="salutationInput" id="salutationInput" class="input_class hidden" style="height:25px; width:150px; margin-top:-30px; margin-left:65px">
              <option value="" selected="selected">Select from List</option>
              <option value="Mr">Mr</option>
              <option value="Ms">Miss</option>
              <option value="Mrs">Mrs</option>
              <option value="Mrs">Dr</option>
            </select>
              <span id="salutationSpan" class="kase applicant_view span_class form_span_vert" style="margin-top:-28px; margin-left:65px"><%= salutation %></span>
            </li>
            <li id="ssnGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="kai gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">SSN</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="ssnSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="ssnSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= ssn %>" name="ssnInput" id="ssnInput" class="kai input_class kase hidden" placeholder="XXX-XX-XXXX" style="margin-top:-26px; margin-left:65px" parsley-error-message="" required />
              <span id="ssnSpan" class="kase applicant_view span_class form_span_vert" style="margin-top:-28px; margin-left:65px"><%= ssn %></span>            
            </li>
            <li id="dobGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">DOB</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="dobSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="dobSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="" name="dobInput" id="dobInput" class="kase kai input_class hidden" style="margin-top:-26px; margin-left:65px" parsley-error-message="" required />
            <span id="dobSpan" class="kase applicant_view span_class form_span_vert" style="background:url(img/glass.png) left top; border:#FFFFFF solid 0px; margin-top:-28px; margin-left:65px"></span></li>
            
            <li id="company_nameGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Company</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="company_nameSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="company_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= company_name %>" name="company_nameInput" id="company_nameInput" class="kase applicant_view input_class hidden" placeholder="Company Name" style="margin-top:-26px; margin-left:65px"parsley-error-message="" required />
              <span id="company_nameSpan" class="kase applicant_view span_class form_span_vert" style="margin-top:-28px; margin-left:65px"><%= company_name %></span></li>
              <li id="emailGrid" data-row="4" data-col="2" data-sizex="1" data-sizey="1" class="kai gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Email</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-16px" class="hidden" id="emailSave">
                <a class="save_field" style="margin:0px;" title="Click to save this field" id="emailSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= email %>" name="emailInput" id="emailInput" style="margin-top:-26px; margin-left:65px" class="kase input_class hidden" parsley-error-message="" required />
            <span id="emailSpan" class="kase applicant_view span_class form_span_vert" style="margin-top:-25px; margin-left:65px">
            <%= email %>
            </span>
            </li>
            <li id="phoneGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Phone</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="phoneSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="phoneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= phone %>" name="phoneInput" id="phoneInput" class="kase applicant_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:65px" parsley-error-message="" required />
              <span id="phoneSpan" class="kase applicant_view span_class form_span_vert" style="margin-top:-28px; margin-left:65px"><%= phone %></span></li>
              <li id="workGrid" data-row="5" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Work</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="phoneSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="phoneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= phone %>" name="phoneInput" id="phoneInput" class="kase applicant_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:65px" parsley-error-message="" required />
              <span id="phoneSpan" class="kase applicant_view span_class form_span_vert" style="margin-top:-28px; margin-left:65px"><%= phone %></span></li>
              <li id="cellGrid" data-row="5" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Cell</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="phoneSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="phoneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= phone %>" name="phoneInput" id="phoneInput" class="kase applicant_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:65px" parsley-error-message="" required />
              <span id="phoneSpan" class="kase applicant_view span_class form_span_vert" style="margin-top:-28px; margin-left:65px"><%= phone %></span></li>
              
              
              <li id="addressGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Address</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="addressSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="addressSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
            <input value="<%= full_address %>" name="full_addressInput" id="full_addressInput" class="kase input_class hidden" style="margin-top:-26px; margin-left:65px; width:325px" />
            <span id="full_addressSpan" class="kase applicant_view span_class form_span_vert" style="margin-top:-28px; margin-left:65px">
            <%= full_address %>
            </span>
            </li>
            <li data-row="5" data-col="2" data-sizex="1" data-sizey="1" class="" style="background:url(img/glass_add.png) left top; -moz-border-radius: 10px; -webkit-border-radius: 10px; -khtml-border-radius: 10px; border-radius: 10px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:4.5%; height:20px">
                    	</li>
            </ul>
    </form>
</div>
</div>