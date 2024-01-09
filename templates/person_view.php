<div id="confirm_apply" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_apply_decide" id="confirm_apply_decide" value="" />
    Do you want to apply changes to every case?
    <div style="padding:5px; text-align:center"><a id="apply_person" class="apply_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_apply" class="apply_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div class="gridster person_view person" id="gridster_person" style="display:none">
     <div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="person_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="person" />
        <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
        <input id="person_id" name="person_id" type="hidden" value="<%= id %>" />
        <input id="person_uuid" name="person_uuid" type="hidden" value="<%= uuid %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
        <input id="billing_time" name="billing_time" type="hidden" value="" />
        <input id="representing" name="representing" type="hidden" value="" />
        <% if (typeof injury_id != "undefined") { %>
        <input id="injury_id" name="injury_id" type="hidden" value="<%=injury_id %>" />
        <% } %>
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "person";
			//$kase_type_pi_confirm = "yes"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <style>
		.top_grid {
			display:none;
		}
		</style>
        <ul id="person_gridster_ul">
        	<%
            if (dob == "Invalid date") {
                dob = "";
            }
            if (dob!="") {
            	dob = moment(dob).format('MM/DD/YYYY');
            }
            %>
            <%
            var ssn1 = "";
            var ssn2 = "";
            var ssn3 = "";
            ssn = ssn.numbersOnly();
            var ssn1Display = "";
            var ssn2Display = "";
            var ssn3Display = "";
            if (ssn.length == 9) {
            	ssn = String(ssn);
            	ssn1 = ssn.substr(0, 3);
                ssn2 = ssn.substr(3, 2);
                ssn3 = ssn.substr(5, 4);
                if (ssn != "XXXXXXXXX") {
	                ssn1Display = ssn1;
                    ssn2Display = ssn2;
                    ssn3Display = ssn3;
                }
            }
            %>
            <li id="salutationGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">&nbsp;</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="salutationSave">
                <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="salutationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <select name="salutationInput" id="salutationInput" class="input_class hidden" style="height:25px; width:100px; margin-top:-30px; margin-left:0px" tabindex="0">
              <% selected = ""
              if (salutation=="") {
              	selected = "selected"
              }
               %>
              <option value="" <%=selected %>>Select...</option>
              <% selected = ""
              if (salutation=="Mrs") {
              	selected = "selected"
              }
               %>
              <option value="Mrs" <%=selected %>>Mrs</option>
              <% selected = ""
              if (salutation=="Mr") {
              	selected = "selected"
              }
               %>
              <option value="Mr" <%=selected %>>Mr</option>
              <% selected = ""
              if (salutation=="Ms") {
              	selected = "selected"
              }
               %>
              <option value="Ms" <%=selected %>>Ms</option>
              <% selected = ""
              if (salutation=="Miss") {
              	selected = "selected"
              }
               %>
              <option value="Miss" <%=selected %>>Miss</option>
              <% selected = ""
              if (salutation=="Dr") {
              	selected = "selected"
              }
               %>
              <option value="Dr" <%=selected %>>Dr</option>
            </select>
              <span id="salutationSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:0px"><%= salutation %></span>
            </li>
            <li id="full_nameGrid" data-row="1" data-col="6" data-sizex="6" data-sizey="1" class="person gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Full Name</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="first_nameSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="first_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= full_name %>" name="full_nameInput" id="full_nameInput" class="kase person_view input_class hidden" placeholder="Full Name" style="margin-top:-26px; margin-left:65px; width:75%; border:1px solid red" parsley-error-message="Req" required autocomplete="off" tabindex="1" />
              <span id="full_nameSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:65px"><%= full_name %></span>
            </li>
            <li id="akaGrid" data-row="2" data-col="1" data-sizex="8" data-sizey="1" class="person gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">AKA</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="akaSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="akaSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= aka %>" name="akaInput" id="akaInput" class="kase person_view input_class hidden" placeholder="AKA" style="margin-top:-26px; margin-left:60px; width:385px" tabindex="2" />
              <span id="akaSpan" class="kase person_view span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%= aka %></span>
            </li>
            <li id="full_addressGrid" data-row="3" data-col="1" data-sizex="8" data-sizey="3" class="gridster_border person" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            	<h6><div class="form_label_vert" style="margin-top:10px;">Address</div></h6>
                <div style="margin-top:-26px; float:right">
                    <a style="font-size:0.7em; cursor:pointer; color:white; display:none" id="manual_address" title="Click to enter address without Google lookup, ie: for non-standard address">no&nbsp;lookup</a>
                    <a style="font-size:0.7em; cursor:pointer; color:white; display:none" id="lookup_address" title="Click to enter address with Google lookup, ie: for standard address">lookup</a>
                </div>
                <input type="text" value="<%= full_address %>" id="full_addressInput" name="full_addressInput" class="search_map kase input_class hidden person" style="margin-left:60px; width:325px; margin-top:-25px" placeholder="Enter location"  />
                <span id="full_addressSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px">
            		<%= display_address %>
            	</span>
                <div id="map_results_holder" style="margin-left:60px; background:#FFF; border:1px solid black; padding:2px; display:none; width:375px; position:absolute; z-index:9999"></div>
                <div style="margin-top:10px; display:; padding-left: 10px; padding-top:10px" id="address_fields_holder">
                	<div style="border:0px solid green">
                        <div style="border:0px solid blue; position:absolute; left:65px; top:38px; margin-top:10px"><input class="kase street person_view input_class hidden" id="street_person" value="<%=street %>" style="border: 0px solid red; width:385px;" /></div>&nbsp;&nbsp;<br />
                        <div style="border:0px solid yellow; position:absolute; left:65px; width:385px; margin-left:-90px; margin-top:31px"><input value="<%= suite %>" name="suiteInput" id="suiteInput" class="kase person_view input_class hidden" style="margin-top:-26px; margin-left:90px;border: 0px solid red; width:385px;" /></div><br />&nbsp;&nbsp;
                        <div style="border:0px solid purple; position:absolute; left:55px; width:200px; margin-top: 0px; margin-left:10px"><input class="kase city person_view input_class hidden" id="city_person"style="width:283px; border: 0px solid red; " value="<%=city %>" /></div>&nbsp;
                        <div style="border:0px solid pink; position:absolute; left:182px; top:10px; width:200px; margin-top:92px; margin-left:172px"><input class="kase state person_view input_class hidden"
              id="administrative_area_level_1_person" style="width:30px;border: 0px solid red" value="<%=state %>" />&nbsp;&nbsp;
                          <div style="border:0px solid orange; position:absolute; left:0px; top:0px; width:100px; margin-top:0px; margin-left:35px"><input class="kase postal_code person_view input_class hidden" id="postal_code_person" style="width:62px" value="<%=zip %>" />
                          </div>
                        </div>
                    </div>
                    <input type="text" id="street_number_person" class="address_fields">
                    <input type="text" id="route_person" class="address_fields">
                    <input type="text" id="locality_person" class="address_fields">
                    <input type="text" id="sublocality_person" class="address_fields">
                    <input type="text" id="sublocality_level_1_person" class="address_fields">
                    <input type="text" id="sublocality_level_2_person" class="address_fields">
                    <input type="text" id="neighborhood_person" class="address_fields">
                    <input type="text" id="administrative_area_level_2_person" class="address_fields">
                    <input type="text" id="postal_code_prefix_person" class="address_fields">
                    <input type="text" id="postal_code_suffix_person" class="address_fields">
                    <input type="text" id="country_person" class="address_fields">
                </div>
                <div id="map" style="display:none"></div>
            </li>
            <li id="ssnGrid" data-row="4" data-col="1" data-sizex="4" data-sizey="1" class="person gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">SSN</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="ssnSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="ssnSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="margin-top:-26px; margin-left:60px; width:385px" class="kase person_view input_class hidden" id="ssn_div">
              <input name="ssn1" type="text" id="ssn1" value="<%= ssn1Display %>" style="width:35px" hidVal="" onkeyup="if(value.length==3){ssn2.focus();}" maxlength="3" placeholder="XXX" />&nbsp;<input name="ssn2" type="text" id="ssn2" value="<%= ssn2Display %>" style="width:27px" hidVal="" onkeyup="if(value.length==2){ssn3.focus();}" maxlength="2" placeholder="XX" />&nbsp;<input name="ssn3" type="text" id="ssn3" value="<%= ssn3Display %>" style="width:42px" hidVal="" maxlength="4" placeholder="XXXX" onkeyup="if(value.length==4){dobInput.focus();}" />
                </div>
              <span id="ssnSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= String(ssn1) + "-" + String(ssn2) + "-" + String(ssn3) %></span>            
            </li>
            <li id="dobGrid" data-row="4" data-col="5" data-sizex="4" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">DOB</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="dobSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="dobSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= dob %>" name="dobInput" id="dobInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" />
            <span id="dobSpan" class="kase person_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= dob %></span>
            </li>
            <?php //if ($_SERVER['REMOTE_ADDR']!='47.153.51.181') { ?>
            <!--
            <li id="full_addressGrid" data-row="4" data-col="1" data-sizex="8" data-sizey="3" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Address</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="full_addressSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="full_addressSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="margin-top:-26px; float:right">
                <a style="font-size:0.7em; cursor:pointer; color:white; display:none" id="manual_address" title="Click to enter address without Google lookup, ie: for non-standard address">no&nbsp;lookup</a>
                <a style="font-size:0.7em; cursor:pointer; color:white; display:none" id="lookup_address" title="Click to enter address with Google lookup, ie: for standard address">lookup</a>
            </div>
            <input value="<%= full_address %>" name="full_addressInput" id="full_addressInput" class="kase input_class hidden person" style="margin-top:-26px; margin-left:60px; width:325px" />
            <span id="full_addressSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px">
            <%= display_address %>
            </span>
			<div style="border:0px solid green">
                <div style="border:0px solid blue; position:absolute; left:65px; top:30px; margin-top:10px"><input class="kase street person_view input_class hidden" id="street_person" value="<%=street %>" style="border: 0px solid red; width:385px;" /></div>&nbsp;&nbsp;<br />
                <div style="border:0px solid yellow; position:absolute; left:65px; width:385px; margin-left:-90px; margin-top:31px"><input value="<%= suite %>" name="suiteInput" id="suiteInput" class="kase person_view input_class hidden" style="margin-top:-26px; margin-left:90px;border: 0px solid red; width:385px;" /></div><br />&nbsp;&nbsp;
                <div style="border:0px solid purple; position:absolute; left:55px; width:200px; margin-top: 0px; margin-left:10px"><input class="kase city person_view input_class hidden" id="city_person" style="width:283px; border: 0px solid red; " value="<%=city %>" /></div>&nbsp;
                <div style="border:0px solid pink; position:absolute; left:182px; top:0px; width:200px; margin-top:92px; margin-left:172px"><input class="kase state person_view input_class hidden"
      id="administrative_area_level_1_person" style="width:30px;border: 0px solid red" value="<%=state %>" />&nbsp;&nbsp;
                  <div style="border:0px solid orange; position:absolute; left:0px; top:0px; width:100px; margin-top:0px; margin-left:35px"><input class="kase postal_code person_view input_class hidden" id="postal_code_person" style="width:62px" value="<%=zip %>" />
                  </div>
                </div>
            </div>
            </li>
            -->
            <li id="phoneGrid" data-row="5" data-col="1" data-sizex="4" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Phone</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="phoneSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="phoneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= phone %>" name="phoneInput" id="phoneInput" class="kase person_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
              <span id="phoneSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= phone %></span>
            </li>
            
            <li id="cell_phoneGrid" data-row="5" data-col="5" data-sizex="4" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Cell</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="cell_phoneSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="cell_phoneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= cell_phone %>" name="cell_phoneInput" id="cell_phoneInput" class="kase person_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
              <span id="cell_phoneSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= cell_phone %></span>
            </li>
            <li id="work_phoneGrid" data-row="6" data-col="1" data-sizex="4" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Work</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="work_phoneSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="work_phoneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= work_phone %>" name="work_phoneInput" id="work_phoneInput" class="kase person_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
              <span id="work_phoneSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= work_phone %></span>
            </li>
            <li id="faxGrid" data-row="6" data-col="5" data-sizex="4" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Fax</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="faxSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="faxSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= fax %>" name="faxInput" id="faxInput" class="kase person_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
              <span id="faxSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= fax %></span>
            </li>
            
            <li id="emailGrid" data-row="7" data-col="1" data-sizex="8" data-sizey="1" class="person gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Email</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-16px" class="hidden" id="emailSave">
                <a class="save_field" style="margin:0px;" title="Click to save this field" id="emailSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= email %>" name="emailInput" id="emailInput" style="margin-top:-26px; margin-left:60px; width:385px" class="kase input_class hidden" />
            <span id="emailSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px">
            <%= email %>
            </span>
            </li>
            <li id="work_emailGrid" data-row="8" data-col="1" data-sizex="8" data-sizey="1" class="person gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Work Email</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-16px" class="hidden" id="emailSave">
                <a class="save_field" style="margin:0px;" title="Click to save this field" id="emailSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= work_email %>" name="work_emailInput" id="work_emailInput" style="margin-top:-26px; margin-left:60px; width:385px" class="kase input_class hidden" />
            <span id="work_emailSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px">
            <%= work_email %>
            </span>
            </li>
           <li id="other_phoneGrid" data-row="9" data-col="1" data-sizex="4" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Other Phone</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="other_phoneSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="other_phoneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= other_phone %>" name="other_phoneInput" id="other_phoneInput" class="kase person_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
              <span id="other_phoneSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= other_phone %></span>
            </li>
            <li id="einGrid" data-row="9" data-col="5" data-sizex="4" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">EIN</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="einSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="einSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= ein %>" name="einInput" id="einInput" class="kase person_view input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px" onkeypress="mask(this, mein);" onblur="mask(this, mein);" />
              <span id="einSpan" class="kase person_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= ein %></span>
            </li>
            <li class="top_grid" data-row="9" data-col="2" data-sizex="1" data-sizey="1">
            </li>
            <li class="top_grid" data-row="9" data-col="3" data-sizex="1" data-sizey="1">
            </li>
            <li class="top_grid" data-row="9" data-col="4" data-sizex="1" data-sizey="1">
            </li>
            
            <li class="top_grid" data-row="9" data-col="6" data-sizex="1" data-sizey="1">
            </li>
            <li class="top_grid" data-row="9" data-col="7" data-sizex="1" data-sizey="1">
            </li>
            <li class="top_grid" data-row="9" data-col="8" data-sizex="0" data-sizey="1">
            </li>
            
		</ul>
        
        <% if (!gridster_me) { %>
			<a href="#applicant/<%= case_id %>"><img src="img/glass_add.png" width="20" height="20" border="0" /></a>
        <% } %>
    </form>
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
            <input class="field" id="street_person" value="<%=street %>"></input>&nbsp;<input class="field" id="city_person"style="width:100px" value="<%=city %>"></input>&nbsp;<input class="field"
              id="administrative_area_level_1_person" style="width:30px" value="<%=state %>"></input>&nbsp;<input class="field" id="postal_code_person"
               style="width:50px" value="<%=zip %>"></input>
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
<div id="all_done"></div>
<script language="javascript">
$( "#all_done" ).trigger( "click" );
</script>