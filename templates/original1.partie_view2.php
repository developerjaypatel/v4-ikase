<%
//we need to examine the adhoc_fields, break them up, and then generate gridster from it
if (typeof party.adhoc_fields != "undefined") {
	var arrAdhoc = party.adhoc_fields.split(",");
} else {
	var arrAdhoc = [];
}
//carrier exception
blnClaimNumber = false;
if (arrAdhoc.indexOf("claim_number") > -1) { 
	blnClaimNumber = true;
}

//medical provider exceptions
doctor_type = "";
assigned_to = "";
assigned_to_display = "";
if (arrAdhoc.indexOf("doctor_type") > -1) { 
	blnDoctorType = true;
    var adhoc = "doctor_type";
    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
    var adhoc_label = "Doctor Type";
    if (typeof adhoc_model != "undefined") {
        doctor_type = adhoc_model.get("adhoc_value");                        
    }
    
    adhoc = "assigned_to";
    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
    var adhoc_label = "Assigned To";
    if (typeof adhoc_model != "undefined") {
        assigned_to = adhoc_model.get("adhoc_value");  
        switch(assigned_to) {
            case "Applicant":
                assigned_to_display = "<span><i class='icon-emo-happy' style='color:green; font-size:15px; margin-top:10px'>&nbsp;</i></span>";
                break;
            case "Neutral":
                assigned_to_display = "<span><i class='icon-emo-sleep' style='color:yellow; font-size:15px; margin-top:10px;'>&nbsp;</i></span>";
                break;
            case "Defense":
                assigned_to_display = "<span><i class='icon-emo-unhappy' style='color:red; font-size:15px'>&nbsp;</i></span>";
                break;
        }                      
    }
}
var button_display = "";
if (!party.show_buttons) { 
	button_display = "none";
}
%>
<div id="confirm_apply" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_apply_decide" id="confirm_apply_decide" value="" />
    Do you want to apply changes to every case?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="apply_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="apply_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div class="gridster partie <%=party.partie %>" id="gridster_<%=party.partie %>" style="display:none; border:0px solid green">
     <div style="background:url(img/glass<%=party.color %>.png) left top repeat-y; padding:5px; width:490px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; border:0px solid red" class="col-md-6">
        <form id="<%=party.partie %>_form" parsley-validate>
            <input id="table_name" name="table_name" type="hidden" value="corporation" />
            <input id="table_id" name="table_id" type="hidden" value="<%=party.id %>" />
            <input id="corporation_id" name="corporation_id" type="hidden" value="<%=party.id %>" />
            <input id="corporation_uuid" name="corporation_uuid" type="hidden" value="<%=party.uuid %>" />
            <input id="parent_corporation_uuid" name="parent_corporation_uuid" type="hidden" value="<%=party.parent_corporation_uuid %>" />
            <input id="additional_partie" name="additional_partie" type="hidden" value="<%=party.additional_partie %>" />
            <input id="partie" name="partie" type="hidden" value="<%=party.partie %>" />
            <input id="type" name="type" type="hidden" value="<%=party.partie.toLowerCase() %>" />
            <input id="case_id" name="case_id" type="hidden" value="<%=party.case_id %>" />
            <input id="case_uuid" name="case_uuid" type="hidden" value="<%=party.case_uuid %>" />
            <input id="adhoc_fields" name="adhoc_fields" type="hidden" value="<%=party.adhoc_fields %>" />
            <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
                <div id="sub_category_holder_<%=party.partie %>" class="partie <%=party.partie %>" style="text-align:left; padding-bottom:5px;">
                    <span style="text-align:left;">
                    	<div style="float:right; margin-top:-5px">
                        <% if (party.id != "-1") { %>
                        <!--<button id="new_partie" class="new btn btn-transparent" style="color:white; border:0px solid; width:20px" title="Click for New Partie" border="0">
                                <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
                        </button>-->
                        <% } %>     
                        <!--
                        <a title="Click to compose a new note" class="compose_new_note" id="compose_<%= party.type %>_<%= party.corporation_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>                       	-->
                        <!--<img src="img/loading_spinner_1.gif" name="gifsave" id="gifsave" width="30" height="30" style="display:none; opacity:50%" />-->
                        <span id="gifsave" style="display:none; opacity:50%"><i class="icon-spin4 animate-spin"></i></span>
                        <span class="edit_row corporation <%=party.partie %>" style="display:inline-block; z-index:6234; margin-left:0px; margin-top:1px; margin-right:0px">
                           <button id="partie_edit" class="edit <%=party.partie %> btn btn-transparent border-blue" style="border:0px solid; width:20px"><i class="glyphicon glyphicon-edit" style="color:#0033FF">&nbsp;</i></button>
                           </span>
                           <span class="button_row corporation <%=party.partie %> hidden" style=" border:0px solid red; width:295px; text-align:right">
                                <table style="margin-top:0px; margin-right:0px" align="right">
                                	<tr>
                                    	<td>
			                                <button class="btn btn-transparent border-red delete" style="color:white; border:0px solid; width:20px"><i class="glyphicon glyphicon-trash" style="color:#FF0000">&nbsp;</i></button>
                                		</td>
                                        <td>
                                        	<button class="save btn btn-transparent border-green" style="border:0px solid; width:20px"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></button>
                                        </td>
                                        <td>
                                        	<button class="reset btn btn-transparent border-white" style="border:0px solid; width:20px"><i class="glyphicon glyphicon-repeat">&nbsp;</i></button>
                                        </td>
                                    </tr>
                                </table>
                           </span>
                           </div>
                        <% if (party.type == "medical_provider") { %>
                        <div style="float:right; color:white; margin-left:10px">
                        	<select id="assigned_toInput" name="assigned_toInput" class="kase partie <%=party.partie %> input_class hidden" style="width:90px; font-size:0.8em">
                            	<option value="" <% if (assigned_to=="") {%>selected="selected"<% } %>>Preferences</option>
                                <option value="Applicant" <% if (assigned_to=="Applicant") {%>selected="selected"<% } %>>Applicant</option>
                            	<option value="Neutral" <% if (assigned_to=="Neutral") {%>selected="selected"<% } %>>Neutral</option>
                            	<option value="Defense" <% if (assigned_to=="Defense") {%>selected="selected"<% } %>>Defense</option>
							</select>
                            <span id="assigned_toSpan" class="kase partie <%=party.partie %> span_class form_span_vert"><%=assigned_to_display %></span>
                        </div>
                        <div style="float:right; color:white; margin-left:10px">
                        	<select id="doctor_typeInput" name="doctor_typeInput" class="kase partie <%=party.partie %> input_class hidden" style="width:90px; font-size:0.8em">
                            	<option value="" <% if (doctor_type=="") {%>selected="selected"<% } %>>Doctor Type</option>
                                <option value="PTP" <% if (doctor_type=="PTP") {%>selected="selected"<% } %>>PTP</option>
                            	<option value="QME" <% if (doctor_type=="QME") {%>selected="selected"<% } %>>QME</option>
                            	<option value="AME" <% if (doctor_type=="AME") {%>selected="selected"<% } %>>AME</option>
                            	<option value="PERSONAL" <% if (doctor_type=="PERSONAL") {%>selected="selected"<% } %>>PERSONAL</option>
                                <option value="secondary physician" <% if (doctor_type=="secondary physician") {%>selected="selected"<% } %>>SECONDARY PHYSICIAN</option>
							</select>
                            <span id="doctor_typeSpan" class="kase partie <%=party.partie %> span_class form_span_vert"><%=doctor_type %></span>
                        </div>
                        <% } %>
                        <span style="color:#FFFFFF; font-size:1.25em; font-weight:lighter; margin-left:3px;" id="partie_type_holder">
                        <%=party.partie_type %>&nbsp;&nbsp; 
                       <span class="alert alert-success" style="display:none; height:25px; width:50px;font-size:14px; z-index:4251; margin-top:-45px; margin-left:-10px;">Saved</span></span>
                       <span class="alert alert-warning" style="display:none; height:25px; width:50px; font-size:14px; z-index:4251; margin-top:-45px; margin-left:-10px;"></span>
                       <div style="float:right; border:0px solid green; width:295px; text-align:right; display:<%=button_display %> " id="<%=party.partie %>_buttons">
                           
                       </div>
                    </span>   
                </div>
            </div>
            <ul>
            	<li id="company_nameGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Company</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="company_nameSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="company_nameSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input autocomplete="off" value="<%=party.company_name %>" name="company_nameInput" id="company_nameInput" class="kase partie <%=party.partie %> input_class hidden" placeholder="Company Name" parsley-error-message="" required style="margin-top:-26px; margin-left:70px; width:385px" />
                  <span id="company_nameSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-30px; margin-left:70px"><%=party.company_name %></span>
                </li>
				<% 
                var the_employee_title = "Employee";
                var employee_show = "";
                if (party.show_employee=="Y") {
                    if (party.employee_title!="") {
                        the_employee_title = party.employee_title;
                    }
                }
				%>
				<li id="full_nameGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:<%=employee_show %>">
                <h6><div class="form_label_vert" style="margin-top:10px;"><%=the_employee_title %></div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="full_nameSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="full_nameSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input autocomplete="off" value="<%=party.first_name + " " + party.last_name %>" name="full_nameInput" id="full_nameInput" class="kase partie <%=party.partie %> input_class employee_class hidden" placeholder="Full Name" style="margin-top:-26px; margin-left:70px; width:385px" />
                  <span id="full_nameSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%=party.full_name %></span>
              </li>	
                <%
                var emp_row ="3";
                %>
                <li id="phoneGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Phone</div></h6>
                    <div style="margin-top:-23px" class="save_holder hidden" id="phoneSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="phoneSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input autocomplete="off" value="<%=party.phone %>" name="phoneInput" id="phoneInput" class="kase partie <%=party.partie %> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
                    <span id="phoneSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:70px"><%=party.phone %></span>
                </li>
                <li id="faxGrid" data-row="<%=emp_row %>" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Fax</div></h6>
                    <div style="margin-top:-23px" class="save_holder hidden" id="faxSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="faxSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input autocomplete="off" value="" name="faxInput" id="faxInput" class="kase partie <%=party.partie %> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" parsley-error-message="" />
                    <span id="faxSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:70px"><%=party.fax %></span>
              	</li>
                <% if (blnClaimNumber) {
                    var adhoc = "claim_number";
                    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                    var adhoc_label = "Claim Number";
                    var adhoc_value = "";
                    if (typeof adhoc_model != "undefined") {
                    	adhoc_value = adhoc_model.get("adhoc_value");                        
                    }
                    emp_row++;
                    
                %>
                <li id="<%=party.partie.toLowerCase() %>_claim_numberGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                <div style="width:99%; background:url(img/glass_row.png) left top; border:#FFFFFF solid 0px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px;font-family: 'Open Sans', sans-serif; color:#FFFFFF; font-size:13px; height:18px; margin-top:-4px">
                <label><div style="margin-top:0px"><%=adhoc_label %></div></label>
                <div style="float:right; margin-right:0px; margin-top:-16px" class="hidden" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Save">
                    <a class="save_field" style="margin:0px;" title="Click to save this field" id="emailSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                </div>
                <input autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:1px; margin-left:3px; width:90%; height:20px" class="kase partie <%=party.partie %> input_class adhoc hidden" parsley-error-message="" />
                
                	<span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:5px; margin-left:0px">
                <%=adhoc_value %>
                </span>
                </li>
                <% if (party.type == "carrier") {
                	var adhoc = "coverage_date_start";
                    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                    var adhoc_value = "";
                    if (typeof adhoc_model != "undefined") {
                    	adhoc_value = adhoc_model.get("adhoc_value");                        
                    }
                    var coverage_date_start = adhoc_value;
                    
                    var adhoc = "coverage_date_end";
                    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                    var adhoc_value = "";
                    if (typeof adhoc_model != "undefined") {
                    	adhoc_value = adhoc_model.get("adhoc_value");                        
                    }
                    var coverage_date_end = adhoc_value;
                    
                	 %> 
                <li id="coverage_date_startGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                <h6><div class="form_label_vert" style="margin-top:10px;">Starts</div></h6>
                    <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="coverage_date_startSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="coverage_date_startSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                <input autocomplete="off" name="coverage_date_startInput" id="coverage_date_startInput" style="margin-top:-22px; margin-left:70px;" class="kase partie <%=party.partie %> adhoc input_class hidden" parsley-error-message="" value="<%=coverage_date_start %>" />
                
                	<span id="coverage_date_startSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-22px; margin-left:70px;">
    	            	<%=coverage_date_start %>
	                </span>
                </li>
                <li id="coverage_date_endGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; display:<% if (coverage_date_end=="") { %>none<% } %>">
                <h6><div class="form_label_vert" style="margin-top:10px;">Ends</div></h6>
                    <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="coverage_date_endSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="coverage_date_endSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                <input autocomplete="off" name="coverage_date_endInput" id="coverage_date_endInput" style="margin-top:-22px; margin-left:70px;" class="kase partie <%=party.partie %> adhoc input_class hidden" parsley-error-message="" value="<%=coverage_date_end %>" />
                
                	<span id="coverage_date_endSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-22px; margin-left:70px;">
            			<%=coverage_date_end %>    
        	        </span>
                </li>
                <% } %>
                <% 
                }
                emp_row; 
                %>
              	<li id="full_addressGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Address</div></h6>
                    <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="full_addressSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="full_addressSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input autocomplete="off" value="<%=party.full_address %>" id="full_addressInput" name="full_addressInput" placeholder="Enter your address" type="text" class="kase partie <%=party.partie %> input_class hidden" style="margin-top:-26px; margin-left:70px; width:385px" parsley-error-message=""  />
                    <span id="full_addressSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:70px">
                    <%=party.full_address %>
                    </span>
                    <div style="border:0px solid green">
                    	<div style="border:0px solid blue; position:absolute; left:75px; top:30px; margin-top:10px"><input class="kase partie <%=party.partie %>  input_class hidden" id="street_<%=party.partie %>" value="<%=party.street %>" style="border: 0px solid red; width:385px" /></div>&nbsp;&nbsp;<br />
                        <div style="border:0px solid yellow; position:absolute; left:75px; width:200px; margin-left:-90px; margin-top:31px"><input value="<%= party.suite %>" name="suiteInput" id="suiteInput" class="kase partie <%=party.partie %> input_class hidden" style="margin-top:-26px; margin-left:90px;border: 0px solid red; width:385px" /></div><br />&nbsp;&nbsp;
                        <div style="border:0px solid purple; position:absolute; left:65px; width:200px; margin-top: 0px; margin-left:10px"><input class="kase partie <%=party.partie %> input_class hidden" id="city_<%=party.partie %>"style="width:100px;border: 0px solid red" value="<%=party.city %>" /></div>&nbsp;<div style="border:0px solid pink; position:absolute; left:0px; top:0px; width:100px; margin-top:93px; margin-left:190px"><input class="kase partie <%=party.partie %>  input_class hidden"
              id="administrative_area_level_1_<%=party.partie %>" style="width:30px;border: 0px solid red" value="<%=party.state %>" />&nbsp;&nbsp;<div style="border:0px solid orange; position:absolute; left:0px; top:0px; width:100px; margin-top:0px; margin-left:45px"><input class="kase partie <%=party.partie %>  input_class hidden" id="postal_code_<%=party.partie %>" style="width:50px" value="<%=party.zip %>" />
                    </div>
                </li>
                <li id="company_siteGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
           			<h6><div class="form_label_vert" style="margin-top:10px;">Website</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="company_siteSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="company_siteSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                    <input autocomplete="off" value="<%=party.company_site %>" id="company_siteInput" name="company_siteInput" placeholder="Enter your Site" type="text" class="kase partie <%=party.partie %> input_class hidden" style="margin-top:-26px; margin-left:70px; width:385px" parsley-error-message="" />
                     <span id="company_siteSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%=party.company_site %></span>
				</li>
                <%
                var emp_row_0 = Number(emp_row);
                if (party.show_employee=="Y") {
                	var emp_row_0 = Number(emp_row);
                	var the_employee_title = "Employee";
                    var employee_show = "";
                    if (party.employee_title!="") {
                        the_employee_title = party.employee_title;
                    }
                %>
				<li id="salutationGrid" data-row="<%= emp_row_0 %>" data-col="2" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:<%=employee_show %>">
                <h6><div class="form_label_vert" style="margin-top:10px;">Salutation</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="salutationSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="salutationSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <select name="salutationInput" id="salutationInput" class="kase partie <%=party.partie %> input_class employee_class hidden" style="height:25px; width:100px; margin-top:-30px; margin-left:70px">
              <option value="">Select...</option>
              <option value="Mrs">Mrs</option>
              <option value="Mr">Mr</option>
              <option value="Ms">Ms</option>
              <option value="Miss">Miss</option>
              <option value="Dr">Dr</option>
            </select>
			<span id="salutationSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%= party.salutation %></span>
              </li>
                
              <li id="employee_phoneGrid" data-row="<%=emp_row_0 + 1 %>" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Phone</div></h6>
                    <div style="margin-top:-23px" class="save_holder hidden" id="employee_phoneSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="employee_phoneSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input autocomplete="off" value="<%=party.employee_phone %>" name="employee_phoneInput" id="employee_phoneInput" class="kase partie <%=party.partie %> input_class employee_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" parsley-error-message=""  />
                    <span id="employee_phoneSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-28px; margin-left:70px"><%=party.employee_phone %></span>
                </li>
                <li id="employee_faxGrid" data-row="<%=emp_row_0 + 1 %>" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Fax</div></h6>
                    <div style="margin-top:-23px" class="save_holder hidden" id="employee_faxSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="employee_faxSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input autocomplete="off" value="<%=party.employee_fax %>" name="employee_faxInput" id="employee_faxInput" class="kase partie <%=party.partie %> input_class employee_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" parsley-error-message=""  />
                    <span id="employee_faxSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-28px; margin-left:70px"><%=party.employee_fax %></span>
                </li>
                <li id="employee_emailGrid" data-row="<%=emp_row_0 + 2 %>" data-col="2" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Email</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-16px" class="hidden" id="employee_emailSave">
                    <a class="save_field" style="margin:0px;" title="Click to save this field" id="employee_emailSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input autocomplete="off" value="<%=party.employee_email %>" name="employee_emailInput" id="employee_emailInput" style="margin-top:-26px; margin-left:70px; width:385px" class="kase partie <%=party.partie %> input_class employee_class hidden" parsley-error-message="" />
                <span id="employee_emailSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-28px; margin-left:70px">
                <%=party.employee_email %>
                </span>
                </li>
                <% } %>
                <% 
                if(party.show_employee!="Y") {
                	 emp_row_0 = emp_row_0;
                %>
                <li id="empty2Grid" data-row="<%= emp_row_0%>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:none">
                </li>
                <% emp_row_0 = emp_row_0;
                } %>
                <% 
                var row_counter = emp_row_0;
                if (!party.grid_it) { 
                    var adhoc_counter = 1;
                    var data_col_value = 1;
                    _.each( arrAdhoc, function(adhoc) {
                        var adhoc_value = "";
                        var adhoc_type = "";
                        var adhoc_acceptable = "";
                        var adhoc_format = "";
                        
                        //now let's get the adhoc_settings
                        var this_setting = adhoc_settings.findWhere({"adhoc":adhoc});
                        if (typeof this_setting != "undefined") {
                            adhoc_type = this_setting.get("type");
                            adhoc_acceptable = this_setting.get("acceptable_values");
                            adhoc_format = this_setting.get("format");
                        }
                        var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc})
                        if (typeof adhoc_model != "undefined") {
                            adhoc_value = adhoc_model.get("adhoc_value");
                        }
                        //for Y/N, not displaying value, icon instead
                        var span_value = adhoc_value;
                        
                        //deal with checkboxes
                        var check_boxed = "";
                        if (adhoc_type=="checkbox") {
                        	if (adhoc_value=="Y") {
                            	check_boxed = " checked";
                            }
                            //check box value is always Y
                        	adhoc_value = "Y";
                        	if (span_value=="Y") {
                            	span_value = "<span style='font-family:Wingdings; color:white'>ü</span>";
                            } else {
                            	span_value = '';
                            }
                        }
                        var adhoc_label = adhoc.replaceAll("_"," ").capitalizeWords();
                        
                        if (adhoc_label!="" && adhoc!="claim_number" && adhoc!="doctor_type" && adhoc!="assigned_to" && adhoc!="coverage_date_start" && adhoc!="coverage_date_end") {
                 %>                
                <li id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Grid" data-row="<%=row_counter %>" data-col="<%=data_col_value %>" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                <div style="width:99%; background:url(img/glass_row.png) left top; border:#FFFFFF solid 0px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px;font-family: 'Open Sans', sans-serif; color:#FFFFFF; font-size:13px; height:18px; margin-top:-4px">
                    <label><div style="margin-top:0px"><%=adhoc_label %></div></label>
                    <div style="float:right; margin-right:0px; margin-top:-16px" class="hidden" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Save">
                        <a class="save_field" style="margin:0px;" title="Click to save this field" id="emailSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                </div>
                
                 <% if (adhoc_type!='select') { %> 
                <input type="<%=adhoc_type %>" autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:1px; margin-left:3px; width:90%; height:20px" class="kase partie <%=party.partie %> input_class adhoc hidden <% if (adhoc_format=="date") { %>datepicker<% } %>" <% if (adhoc_format=="date") { %>onkeyup="mask(this, mdate);" onblur="mask(this, mdate);"<% } %><% if (adhoc_format=="phone") { %>onkeypress="mask(this, mphone);" onblur="mask(this, mphone);"<% } %> <%=check_boxed %> />
                	<% } else { %>
                    <%
                    arrAcceptable = adhoc_acceptable.split(',');
                    
                    %>
                    <select style='margin-top:1px; margin-left:3px;' class='kase partie <%=party.partie %> input_class adhoc hidden' name='<%=party.partie.toLowerCase() %>_<%=adhoc %>Input' id='<%=party.partie.toLowerCase() %>_<%=adhoc %>Input'>
                    <% for(var i = 0; i < arrAcceptable.length; i++) { %>
                    	<option value='<%=arrAcceptable[i]%>' <% if (arrAcceptable[i]==span_value) { %>selected<% } %>><%=arrAcceptable[i]%></option>
                    <%
                    }
                    %>
                    </select>
                    <% } %>
                	<span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:5px; margin-left:0px">
                <%=span_value %>
                </span>
               
                </li>
                <% }
                	data_col_value++;
                    if (data_col_value > 2) {
	                    row_counter++;
                    	data_col_value = 1;
                    }
                    adhoc_counter++;
                }); %>
                <% } %>
                <% if (party.type == "medical_provider" && party.additional_partie=="p") { %>
                <li id="copyingInstructionsGrid" data-row="<%=row_counter %>" data-col="<%=data_col_value %>" data-sizex="2" data-sizey="2" class="partie <%=party.partie %> gridster_border" style="background:url(img/glass.png) left top">
                	<div style="float:right; border:0px yellow solid; width:125px; margin-top:10px" id="any_all_holder">
                    	<div style="display:inline; border:0px solid red;">
                        <input name="any_all" type="checkbox" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;width:20px" id="any_allInput" value="Y" <% if (party.any_all=="Y") { %>checked<% } %> /><span id="any_allSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px; color:#FFFFFF" ><% if (party.any_all=="Y") { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                        </div>
                        <div style="display:inline; border:0px solid green; color:#FFFFFF">Any&nbsp;and&nbsp;All</div>
                    </div>
                	<label><div style="margin-top:0px">Records Requested</div></label>
					<?php if ($_SERVER['REMOTE_ADDR'] == "71.116.242.3") { ?>
						<table border="0" style="margin-top:10px">
							<tr>
								<td style="width:30px; margin-bottom:-10px" valign="bottom">
									<input name="medical_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="medical_copyInput" class="kase partie <%=party.partie %> input_class hidden" value="M" <% if (party.copying_instructions.indexOf("M") > -1) { %>checked<% } %> /><span id="medical_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("M") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
								</td>
								<td style="width:60px; margin-top:-10px; color:#FFFFFF" align="left" valign="top">
									Medical
								</td>
								<td style="width:30px; margin-bottom:-10px" valign="bottom">
									<input name="billing_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="billing_copy" class="kase partie <%=party.partie %> input_class hidden" value="B" <% if (party.copying_instructions.indexOf("B") > -1) { %>checked<% } %> /><span id="billing_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("B") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
								</td>
								<td style="width:70px; margin-top:-10px; color:#FFFFFF" align="left" valign="top">
									Billing
								</td>
								<td style="width:30px; margin-bottom:-10px" valign="bottom">
									<input name="xray_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="xray_copy" class="kase partie <%=party.partie %> input_class hidden" value="X" <% if (party.copying_instructions.indexOf("X") > -1) { %>checked<% } %> /><span id="xray_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("X") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
								</td>
								<td style="width:90px; margin-top:-10px; color:#FFFFFF" valign="top">
									X-ray Films
								</td>
								<td style="width:30px; margin-bottom:-10px" valign="bottom">
									<input name="employment_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="employment_copy" class="kase partie <%=party.partie %> input_class hidden" value="E" <% if (party.copying_instructions.indexOf("E") > -1) { %>checked<% } %> /><span id="employment_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("E") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
								</td>
								<td style="width:90px; margin-top:-10px; color:#FFFFFF" valign="top">
									Employment
								</td>
							</tr>
							<tr>
								<td valign="bottom" style="margin-bottom:-10px">
									<input name="wage_copy" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="wage_copy" value="W" <% if (party.copying_instructions.indexOf("W") > -1) { %>checked<% } %> /><span id="wage_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("W") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
								</td>
								<td valign="top" style="margin-top:-10px; color:#FFFFFF">
									Wage
								</td>
								<td valign="bottom" style="margin-bottom:-10px">
									<input name="claim_copy" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="claim_copy" value="C" <% if (party.copying_instructions.indexOf("C") > -1) { %>checked<% } %> /><span id="claim_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("C") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
								</td>
								<td valign="top" style="margin-top:-10px; color:#FFFFFF">
									Claim File
								</td>
								<td valign="bottom" style="margin-bottom:-10px">
									<input name="other_copy" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="other_copy" value="O" <% if (party.copying_instructions.indexOf("O") > -1) { %>checked<% } %> /><span id="other_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("O") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
								</td>
								<td valign="top" style="margin-top:-10px; color:#FFFFFF">
									Other
								</td>
								<td>
									&nbsp;
								</td>
								<td>
									&nbsp;
								</td>
							</tr>
						</table>
					<?php } else { ?>
						<div style"border:1px solid yellow; display:inline-block;">
							<span style="margin-bottom:50px; border:0px solid green; width:100px">
								<input name="medical_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="medical_copyInput" class="kase partie <%=party.partie %> input_class hidden" value="M" <% if (party.copying_instructions.indexOf("M") > -1) { %>checked<% } %> /><span id="medical_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("M") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>Medical&nbsp;
							</span> 
							<span style="margin-bottom:50px; border:1px solid green; width:100px">
								<input name="billing_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="billing_copy" class="kase partie <%=party.partie %> input_class hidden" value="B" <% if (party.copying_instructions.indexOf("B") > -1) { %>checked<% } %> /><span id="billing_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("B") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>Billing&nbsp;
							</span>
							<span style="margin-bottom:50px; border:0px solid green; width:100px">
								<input name="xray_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="xray_copy" class="kase partie <%=party.partie %> input_class hidden" value="X" <% if (party.copying_instructions.indexOf("X") > -1) { %>checked<% } %> /><span id="xray_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("X") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>X-ray Films
							</span>
							<span style="margin-bottom:50px; border:0px solid green; width:100px">
								<input name="employment_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="employment_copy" class="kase partie <%=party.partie %> input_class hidden" value="E" <% if (party.copying_instructions.indexOf("E") > -1) { %>checked<% } %> /><span id="employment_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("E") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>Employment&nbsp;
							</span>
						</div>
					  <div style"border:1px solid orange; display:inline-block"> 
						<span style="margin-top:50px; border:0px solid green; width:100px"><input name="wage_copy" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="wage_copy" value="W" <% if (party.copying_instructions.indexOf("W") > -1) { %>checked<% } %> /><span id="wage_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("W") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>Wage&nbsp;</span>
						
						<span style="margin-top:50px; border:0px solid green; width:100px"><input name="claim_copy" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="claim_copy" value="C" <% if (party.copying_instructions.indexOf("C") > -1) { %>checked<% } %> /><span id="claim_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("C") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>Claim File&nbsp;</span>
						
						<span style="margin-top:50px; border:0px solid green; width:100px"><input name="other_copy" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="other_copy" value="O" <% if (party.copying_instructions.indexOf("O") > -1) { %>checked<% } %> /><span id="other_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("O") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>Other: &nbsp;</span>
						<input name="other_description" type="text" class="kase partie <%=party.partie %> input_class hidden" style="position:relative; margin-top:0px" id="other_description" value="<%=party.other_description %>" placeholder="Other Description" />
					  </div>
				  <?php } ?>
                </li>
                <li id="specialInstructionsGrid" data-row="<%=row_counter %>" data-col="<%=data_col_value %>" data-sizex="2" data-sizey="3" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                	<label><div style="margin-top:0px">Special Instructions</div></label>
                    	<textarea name="special_instructions" cols="50" rows="4" class="kase partie <%=party.partie %> input_class hidden" id="special_instructions" style="position:relative; width:441px; margin-left:10px; margin-top:6px"><%=party.special_instructions %></textarea>
                        <span id="special_instructionsSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative;" ><%=party.special_instructions %></span>
               </li>
                <% } %>
          </ul>
          <% if (!party.gridster_me) { %>
            <a href="#parties/<%=party.case_id %>/<%=party.id %>/<%=party.partie.toLowerCase() %>"><img src="img/glass_add.png" width="20" height="20" border="0" /></a>
           <% } %>
        </form>
    </div>
</div>
<div id="partie_notes" class="<%=party.blurb %> col-md-5" style="display:none; border:0px solid pink; margin-left:100px; margin-top:0px">
</div>
<div id="addressGrid" style="display:">
    <table id="address">
      <tr style="display:none">
        <td class="label">Street address</td>
        <td class="slimField"><input class="field" id="street_number_<%=party.partie %>" disabled="true" />
        </td>
        <td class="wideField" colspan="2"><input class="field" id="route_<%=party.partie %>" disabled="true" />
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">City</td>
        <td class="wideField" colspan="3">
            <input class="field" id="locality_<%=party.partie %>"
              disabled="true"></input>
            <input class="field" id="sublocality_<%=party.partie %>"
              disabled="true"></input>
              <input class="field" id="neighborhood_<%=party.partie %>"
              disabled="true"></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">Country</td>
        <td class="wideField" colspan="3"><input class="field"
              id="country_<%=party.partie %>" disabled="true"></input></td>
      </tr>
    </table>
</div>
<% if (party.gridster_me || party.grid_it) { %>
<script language="javascript">
var trig = "1";
//setTimeout("gridsterIt(0)", 10);
setTimeout(function() {
	gridsterById('gridster_<%=party.partie %>');
}, 10);
//initializeGoogleAutocomplete('<%=party.partie %>');
function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function countit() {
	var address_full_length = document.getElementById("full_addressSpan").innerHTML;
	var address_char_count = address_full_length.length;
	//alert(address_char_count);
	if (address_char_count > 30) {
  		 //console.log(address_full_length);
		 //console.log(address_char_count);

  		 document.getElementById("full_addressSpan").style.fontSize = "15px";
		 
	}
	//alert(address_full_length);
}

if (trig = "1") {
	countit();
}
</script>
<% } %>