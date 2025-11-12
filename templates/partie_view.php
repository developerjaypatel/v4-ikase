<%
//we need to examine the adhoc_fields, break them up, and then generate gridster from it
if (typeof party.adhoc_fields != "undefined" && party.adhoc_fields != null) {
	var arrAdhoc = party.adhoc_fields.split(",");
} else {
	var arrAdhoc = [];
}
//carrier exception
blnClaimNumber = false;
if (arrAdhoc.indexOf("claim_number") > -1) { 
	blnClaimNumber = true;
}
blnMPN = false;
if (arrAdhoc.indexOf("mpn") > -1) { 
	blnMPN = true;
}

blnDefenseAttorneyEmail = false;
if (arrAdhoc.indexOf("attorney_email") > -1) { 
	blnDefenseAttorneyEmail = true;
}

blnDefenseAttorneySecretary = false;
if (arrAdhoc.indexOf("secretary") > -1) { 
	blnDefenseAttorneySecretary = true;
}
blnDefenseSecretaryEmail = false;
if (arrAdhoc.indexOf("secretary_email") > -1) { 
	blnDefenseSecretaryEmail = true;
}
blnDefenseAcceptFax = false;
if (arrAdhoc.indexOf("accept_fax") > -1) { 
	blnDefenseAcceptFax = true;
}

//referred out attorney
blnClaims = false;
if (arrAdhoc.indexOf("claims") > -1) { 
	blnClaims = true;
}
blnMedicalIntakeEmail = false;
if (arrAdhoc.indexOf("intake_email") > -1) { 
    blnMedicalIntakeEmail = true;
}

blnMedicalReportsEmail = false;
if (arrAdhoc.indexOf("reports_email") > -1) { 
    blnMedicalReportsEmail = true;
}
blnMedicalReferralsEmail = false;
if (arrAdhoc.indexOf("referrals_email") > -1) { 
    blnMedicalReferralsEmail = true;
}

//medical provider exceptions
var doctor_type = "";
var intake_email = "";
var reports_email = "";
var referrals_email = "";
var assigned_to = "";
var assigned_to_display = "";
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
                assigned_to_display = "<span><img src='../img/thumbs_up.png' height='20' width='20'></span>";
                break;
            case "Neutral":
                assigned_to_display = "<span><img src='../img/spacer.gif' height='20' width='20'></span>";
                break;
            case "Defense":
                assigned_to_display = "<span><img src='../img/thumbs_down.png' height='20' width='20'></span>";
                break;
        }                      
    }
}
var primary_secondary = "primary";
if (arrAdhoc.indexOf("primary_secondary") > -1) { 
	var adhoc = "primary_secondary";
    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
    
    if (typeof adhoc_model != "undefined") {
        primary_secondary = adhoc_model.get("adhoc_value");                        
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
    <div style="padding:5px; text-align:center"><a id="apply_partie" class="apply_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="dontapply_partie" class="apply_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
<div class="gridster partie <%=party.partie %>" id="gridster_<%=party.partie %>" style="display:none; border:0px solid green">
     <div style="background:url(img/glass<%=party.color %>.png) left top repeat-y; padding:5px; width:490px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; border:0px solid red" class="col-md-6">
        <form id="<%=party.partie %>_form" parsley-validate  autocomplete="off">
            <input id="table_name" name="table_name" type="hidden" value="corporation" />
            <input id="table_id" name="table_id" type="hidden" value="<%=party.id %>" />
            <input id="corporation_id" name="corporation_id" type="hidden" value="<%=party.id %>" />
            <input id="corporation_uuid" name="corporation_uuid" type="hidden" value="<%=party.uuid %>" />
            <input id="parent_corporation_uuid" name="parent_corporation_uuid" type="hidden" value="<%=party.parent_corporation_uuid %>" />
            <input id="additional_partie" name="additional_partie" type="hidden" value="<%=party.additional_partie %>" />
            <input id="partie" name="partie" type="hidden" value="<%=party.partie %>" />
            <input id="type" name="type" type="hidden" value="<%=party.partie.toLowerCase() %>" />
			<input id="partie_type" name="partie_type" type="hidden" value="<%=party.partie_type %>" />
            <input id="case_id" name="case_id" type="hidden" value="<%=party.case_id %>" />
            <input id="case_uuid" name="case_uuid" type="hidden" value="<%=party.case_uuid %>" />
            <input id="adhoc_fields" name="adhoc_fields" type="hidden" value="<%=party.adhoc_fields %>" />
            <input id="party_representing_id" name="party_representing_id" type="hidden" value="" />
            <input id="party_representing_name" name="party_representing_name" type="hidden" value="" />
            <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
                <div id="sub_category_holder_<%=party.partie %>" class="partie <%=party.partie %>" style="text-align:left; padding-bottom:5px;">
                <!-- <div id="sub_category_holder_<%=party.partie %>" class="partie <%=party.partie %>" style="text-align:left; padding-bottom:30px;"> -->
                    <span style="text-align:left;">
                    	<div style="float:right; margin-top:-5px">
                        	
                        <% if (party.id > 0) { %>
                            <% if (party.type == "employer" || party.type == "carrier" || party.type == "defense") { %>
                            <span><a id="list_kases_link" title="Click to list kases associated with this company" class="parent_<%=party.parent_corporation_uuid %>_<%=party.type %>" style="color:white;cursor:pointer" href="#kaseslist/<%=party.corporation_id %>/<%=party.type %>" target="_blank">Kases</a></span> 
                            <% } %>
                        <% } %>
                        
                        <% if (party.id < 0) { %>
                            <% if (party.type == "employer") { %>
                            <span>
                            <a href="#eams_case_search/<%=party.case_id %>" class="list-item_kase kase_link white_text" style="padding:2px; border:0px solid #CCC; text-decoration:underline" target="_blank">
                            eams search
                            </a>
                            </span> 
                            <% } %>
                        <% } %>
                        
                        <% if (party.type == "venue") { %>
                            <div class="injury_links" style="display:inline-block; z-index:6234; margin-right:20px; margin-top:-10px; border:0px solid red;">
                                <a id='scrape_injury' class='scrape_injury white_text' style='font-size:0.9em;cursor:pointer; margin-right:-10px;' title='Click to import data from EAMS and update this injury'>eams update judge</a>
                            </div>
                        <% } %>
                        <span id="gifsave" style="display:none; opacity:50%"><i class="icon-spin4 animate-spin"></i></span>
                        <span class="edit_row corporation <%=party.partie %>" style="display:inline-block; z-index:6234; margin-left:0px; margin-top:1px; margin-right:0px">
                           <button id="partie_edit" class="edit <%=party.partie %> btn btn-transparent border-blue" style="border:0px solid; width:20px"><i class="glyphicon glyphicon-edit" style="color:#0033FF">&nbsp;</i></button>
                           </span>
                           <!--<span class="button_row corporation <%=party.partie %> hidden" style=" border:1px solid red; width:325px; text-align:right">-->
                           <span class="button_row corporation <%=party.partie %> hidden" style="display:inline-block; margin-left:25px; margin-top:-10px">
                           		
                                    <button class="save btn btn-transparent border-green" style="width:20px; border:0px solid"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></button>
                                    
                                &nbsp;<button class="reset btn btn-transparent border-white" style="border:0px solid; width:20px"><i class="glyphicon glyphicon-repeat">&nbsp;</i></button>
                           	<!--
                                <table style="margin-top:0px; margin-right:0px" align="right" border="1">
                                	<tr>
                                        <td>
                                        	<button class="save btn btn-transparent border-green" style="border:0px solid; width:20px"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></button>
                                        </td>
                                        <td>
                                        	<button class="reset btn btn-transparent border-white" style="border:0px solid; width:20px"><i class="glyphicon glyphicon-repeat">&nbsp;</i></button>
                                        </td>
                                    </tr>
                                </table>
                            -->
                           </span>
                           </div>
                           <!--party.type == "carrier"-->
                        <% if (party.type == "") { %>
                        <div style="float:right; color:white; margin-right:10px">
                        	<select id="primary_secondaryInput" name="primary_secondaryInput" class="kase partie <%=party.partie %> input_class hidden">
                            	<option value="primary" <% if (primary_secondary=="primary") { %>selected<% } %>>Primary</option>
                                <option value="secondary" <% if (primary_secondary=="secondary") { %>selected<% } %>>Secondary</option>
                            </select>
                            <span id="primary_secondarySpan" class="kase partie <%=party.partie %> span_class form_span_vert">
                            <%=primary_secondary.toUpperCase() %>
                            </span>
                            &nbsp;&nbsp;|
                        </div>
                        <% } %>
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
                                <option value="PQME" <% if (doctor_type=="PQME") {%>selected="selected"<% } %>>PQME</option>
                            	<option value="AME" <% if (doctor_type=="AME") {%>selected="selected"<% } %>>AME</option>
                                <option value="CLAIMANT" <% if (doctor_type=="CLAIMANT") {%>selected="selected"<% } %>>LIEN CLAIMANT</option>
                            	<option value="PERSONAL" <% if (doctor_type=="PERSONAL") {%>selected="selected"<% } %>>PERSONAL</option>
                                <option value="secondary physician" <% if (doctor_type=="secondary physician") {%>selected="selected"<% } %>>SECONDARY PHYSICIAN</option>
							</select>
                            <span id="doctor_typeSpan" class="kase partie <%=party.partie %> span_class form_span_vert"><%=doctor_type %></span>
                        </div>
                        <% } %>
                        <span style="color:#FFFFFF; font-size:1.25em; font-weight:lighter; margin-left:3px;" id="partie_type_holder">
                            <%=party.partie_display_title %>&nbsp;&nbsp;
                            <div style="color:white; margin-top:-25px; margin-left:200px; z-index:9999; font-size:.75em; display:<% if (party.show_dashboard=="Y" || party.sort_order <= 10) { %>none<% } %>" id="dashboard_selector_holder">
                            	<a id="add_to_dash" class="white_text" style="cursor:pointer; display:none">add to dashboard</a>
                            </div>
                            <% if (blnPiReady) { %>
            					<% if (!party.blnWCAB) { %>
                                    <!--<div style="color:white; margin-top:-25px; margin-left:200px; z-index:9999; font-size:.75em" id="billing_dropdown_holder">
                                        <table>
                                            
                                            <tr>
                                                <td align="left" valign="top"><select name="billing_time_dropdownInput" id="billing_time_dropdownInput" style="height:19px; width:45px; margin-top:0px; margin-left:0px; background:white" tabindex="0" placeholder="15"><option value="5">5</option><option value="10">10</option><option value="15">15</option><option value="20">20</option><option value="30">30</option><option value="45">45</option><option value="60">60</option></select>
                                                </td>
                                                <td align="left" valign="top">Minutes
                                                </td>
                                            </tr>
                                            
                                        </table>
                                    </div>-->
                                <% } %>
            				<% } %>
                                    <!--<span id="employer_applicants" class="employer_applicants_<%=party.parent_corporation_uuid %>_<%=party.type %>" style="color:#00FFFF; cursor:pointer; font-size:0.75em">Show Other Applicants</span>-->
                                
                           <span class="alert alert-success" style="display:none; height:25px; width:50px;font-size:14px; z-index:4251; margin-top:-45px; margin-left:-10px;">Saved</span>
                       </span>
                       <span class="alert alert-warning" style="display:none; height:25px; width:50px; font-size:14px; z-index:4251; margin-top:-45px; margin-left:-10px;"></span>
                       <div style="float:right; border:0px solid green; width:295px; text-align:right; display:<%=button_display %> " id="<%=party.partie %>_buttons">
                       <div id="kases_link_holder" style="float:right; display:none"></div>
                           
                       </div>
                    </span>   
                </div>
            </div>
            <ul id="partie_gridster_ul" style="margin-top: 25px">
            	<% if (party.type == "employer") { %>
                <li id="multipleEmployersGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="2" class="partie <%=party.partie %> gridster_border" style="background:url(img/glass_injury.png) left top; font-size:1.3em; display:none">
                	<label><div style="margin-top:0px; color:white"><span id="employer_count"></span> already attached to this case</div></label>
                    <div class="white_text">
                    	<input type="checkbox" value="O" class="override_partie" id="override_current" checked="checked" />&nbsp;Override Current Employer(s) in Parties
                    </div>
                    <div class="white_text">
                        <input type="checkbox" value="A" class="override_partie" id="addto_current" />&nbsp;Add this Employer to Parties
                    </div>
                </li>
                <% } %>
            	<% if (party.type == "referredout_attorney") {
                	if (blnClaims) {
                    	if (party.referred_out_claim=="") {
                            var adhoc = "claims";
                            var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                            var adhoc_label = "Claims";
                            var theclaims = "";
                            if (typeof adhoc_model != "undefined") {
                                theclaims = adhoc_model.get("adhoc_value");                        
                            }
                        } else {
                        	theclaims = party.referred_out_claim;
                        }
                    }    
                %>
                <li id="copyingInstructionsGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="2" class="partie <%=party.partie %> gridster_border" style="background:url(img/glass.png) left top">
                	<label><div style="margin-top:0px">Claims</div></label>
					<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
                      <tr>
                        <td width="20%" align="left" valign="top" nowrap="nowrap" style="visibility:<% if (party.claims.indexOf("3P") == -1) { %>hidden<% } %>">Third Party
                          <input type="radio" name="claims" id="third_partyInput" value="3P" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;margin-left:-30px" <% if (theclaims=="3P") {%>checked<% } %> />
                          <span id="third_partySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (theclaims=="3P") { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                        </td>
                        <td width="20%" align="left" valign="top" nowrap="nowrap" style="visibility:<% if (party.claims.indexOf("132a") == -1) { %>hidden<% } %>">132a
                        <input type="radio" name="claims" id="132aInput" value="132a" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;margin-left:-30px" <% if (theclaims=="132a") {%>checked<% } %> />
                        <span id="132aSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (theclaims=="132a") { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                        </td>
                        <td width="20%" align="left" valign="top" nowrap="nowrap" style="visibility:<% if (party.claims.indexOf("SER") == -1) { %>hidden<% } %>">Serious and Willful
                        <input type="radio" name="claims" id="seriousInput" value="SER" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;margin-left:-30px" <% if (theclaims=="SER") {%>checked<% } %> />
                        <span id="seriousSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (theclaims=="SER") { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                        </td>
                      </tr>
                      <tr>
                        <td width="20%" align="left" valign="top" nowrap="nowrap" style="visibility:<% if (party.claims.indexOf("ADA") == -1) { %>hidden<% } %>">ADA
                          <input type="radio" name="claims" id="adaInput" value="ADA" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;margin-left:-30px" <% if (theclaims=="ADA") {%>checked<% } %> />
                          <span id="adaSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (theclaims=="ADA") { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                        </td>
                        <td width="20%" align="left" valign="top" nowrap="nowrap" style="visibility:<% if (party.claims.indexOf("SS") == -1) { %>hidden<% } %>">SS
                        <input type="radio" name="claims" id="ssInput" value="SS" class="kase partie <%=party.partie %> input_class hidden" style="position:relative;margin-left:-30px" <% if (theclaims=="SS") {%>checked<% } %> />
                        <span id="ssSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (theclaims=="SS") { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                        </td>
                        <td>Accepted Y/N</td>
                      </tr>
                    </table>
                </li>
            	<% } %>
                <% if (party.type == "witnesses") { %>
                        <li id="full_nameGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Full Name</div></h6>
                        <input class="kase partie <%=party.partie %> input_class hidden" id="full_nameInput" name="full_nameInput" value="<%=party.full_name %>" style="margin-top:-26px; margin-left:80px; width:375px" />
                        <span id="full_nameSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.full_name %></span>
                        </li>
                	<% } %>
				
                <% if (party.type != "witnesses") { %>
                <li id="company_nameGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <% if (party.full_address!="") { %>
                <div style="float:right" class="lookup_holder" id="company_nameLookup">
                    <a class="lookup_field" style="margin-top:0px" title="Click to lookup company" id="company_nameLookupLink" href="https://www.google.com/search?q=<%=party.company_name.replaceAll(' ', '+') %>+<%= party.full_address.replaceAll(' ', '+') %>" target="_blank">
                        <img src="img/google_icon.gif" width="20" height="20" />
                    </a>
                </div>
                <% } %>
                <h6>
                	<div class="form_label_vert" style="margin-top:10px;" id="company_input_label">
                    	<% if ((party.type == "plaintiff" || party.type == "witness" || party.type == "child" || party.type == "defendant") && blnPiReady) { %>
              			Name        
                        <% } else { %>
                        Company
                        <% } %>
                   	</div>
                </h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="company_nameSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="company_nameSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <div style="float: right;margin-top: -10px;margin-right: 165px; background:orange; color:black; padding:2px; display:none;" class="jetfile_instructions">
                    <span id="jetfile_length"></span> characters (56 max for JetFile)
                </div>
                  <input value="<%=party.company_name %>" name="company_nameInput" id="company_nameInput" class="kase partie <%=party.partie %> input_class hidden" placeholder="Company Name" parsley-error-message="" required style="margin-top:-26px; margin-left:70px; width:345px" autocomplete="off" />
                  <span id="company_nameSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.company_name %></span>
                  
                </li>
                <% } %>
                <div id="token_add_link">
                	Click here to create a new location for this company
                </div>
                <li id="full_addressGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    
                    <h6>
                    	<div class="form_label_vert" style="margin-top:10px; width:60px">
                        	Address
                            <div id="clear_address_holder" style="display:none; position:absolute">
                            	<a id="clear_address" class="white_text" style="cursor:pointer; text-decoration:underline">clear</a>
                            </div>
                            <div id="add_address_holder" style="display:none; position:absolute; margin-top:13px">
                            	<a id="add_address" class="white_text" style="cursor:pointer; text-decoration:underline">add</a>
                            </div>
                        </div>
                    </h6>
                    <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="full_addressSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="full_addressSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    
                    <div style="margin-top:-26px; float:right; width:70px">
                    	<% if (party.full_address!="") { %>
                        <div class="lookup_holder" id="company_nameLookup" style="float:right">
                            <a id="map_partie_link_<%=party.corporation_id %>" title="Click for a Bing Map to this address" class="map_partie" style="color:white;cursor:pointer;"><i class="glyphicon glyphicon-map-marker" style="color:#FCF"></i></a>
                        </div>
                        <% } %>
                    	<a style="font-size:0.7em; cursor:pointer; color:white; display:none" id="manual_address" title="Click to enter address without Google lookup, ie: for non-standard address">no&nbsp;lookup</a>
                        <a style="font-size:0.7em; cursor:pointer; color:white; display:none" id="lookup_address" title="Click to enter address with Google lookup, ie: for standard address">lookup</a>
                    </div>
                    <input autocomplete="off" value="<%=party.full_address %>" id="full_addressInput" name="full_addressInput" placeholder="Enter address for Bing lookup" type="text" class="kase partie <%=party.partie %> partie_address input_class hidden" style="margin-top:-26px; margin-left:70px; width:300px" parsley-error-message=""  />
                    <span id="full_addressSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                    <%=party.display_address %>
                    </span>
                    <div id="bing_results" style="position: absolute;z-index: 9999;background: aliceblue;border: 1px solid black;padding: 5px;color: black;left: 75px; display:none"></div>
                    	<div style="border:0px solid blue; position:absolute; left:75px; top:30px; margin-top:10px">
                        	<input class="kase partie <%=party.partie %> street partie_address input_class partie_address hidden" id="street_<%=party.partie %>" value="<%=party.street %>" style="border: 0px solid red; width:375px" placeholder="Street" />
                        </div>
                        &nbsp;&nbsp;<br />
                        <div style="border:0px solid yellow; position:absolute; left:75px; width:200px; margin-left:-100px; margin-top:31px">
                        	<input value="<%= party.suite %>" name="suiteInput" id="suiteInput" class="kase partie <%=party.partie %> suite partie_address input_class hidden" style="margin-top:-26px; margin-left:100px;border: 0px solid red; width:375px" placeholder="Suite/Office/Appartment" autocomplete="off" />
                        </div>
                        <br />&nbsp;&nbsp;
                        <div style="border:0px solid purple; position:absolute; left:65px; width:200px; margin-top: 0px; margin-left:10px">
                        <input class="kase partie <%=party.partie %> city partie_address input_class hidden" id="city_<%=party.partie %>" style="width:273px;border: 0px solid red" value="<%=party.city %>" placeholder="City" autocomplete="off" />
                        </div>
                        &nbsp;
                        <div style="border:0px solid pink; position:absolute; left:175px; top:0px; width:200px; margin-top:92px; margin-left:179px"><input class="kase partie <%=party.partie %> state partie_address input_class hidden"
              id="administrative_area_level_1_<%=party.partie %>" style="width:30px;border: 0px solid red" value="<%=party.state %>" placeholder="State" autocomplete="off" />&nbsp;&nbsp;<div style="border:0px solid orange; position:absolute; left:0px; top:0px; width:100px; margin-top:0px; margin-left:35px"><input class="kase partie <%=party.partie %> postal_code partie_address input_class hidden" id="postal_code_<%=party.partie %>" style="width:60px" value="<%=party.zip %>" placeholder="Zip" autocomplete="off" />
                    	</div>
                    </div>
                </li>
                <li id="additional_addressGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:none">
                    
                    <h6>
                    	<div class="form_label_vert" style="margin-top:10px; width:60px">
                        	Additional Address
                            <div id="clear_address_holder" style="display:none; position:absolute">
                            	<a id="clear_additional_address" class="white_text" style="cursor:pointer; text-decoration:underline">clear</a>
                            </div>
                        </div>
                    </h6>
                    <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="additional_addressSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="additional_addressSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    
                    <div style="margin-top:-36px; float:right; width:70px">
                    	<% if (party.additional_address!="") { %>
                        <div class="lookup_holder" id="company_nameLookup" style="float:right">
                            <a id="map_partie_link_<%=party.corporation_id %>" title="Click to map to this company" class="map_partie" style="color:white;cursor:pointer;"><i class="glyphicon glyphicon-map-marker" style="color:#FCF"></i></a>
                        </div>
                        <% } %>
                    	<a style="font-size:0.7em; cursor:pointer; color:white; display:none" id="manual_address" title="Click to enter address without Google lookup, ie: for non-standard address">no&nbsp;lookup</a>
                        <a style="font-size:0.7em; cursor:pointer; color:white; display:none" id="lookup_address" title="Click to enter address with Google lookup, ie: for standard address">lookup</a>
                    </div>
                    <input autocomplete="off" value="" id="additional_full_addressInput" name="additional_full_addressInput" placeholder="Enter address for Google lookup" type="text" class="kase partie <%=party.partie %> partie_additional_address input_class hidden" style="margin-top:-36px; margin-left:80px; width:300px" parsley-error-message=""  />
                    <span id="additional_addressSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                    </span>
                    <div style="border:0px solid green">
                    	<div style="border:0px solid blue; position:absolute; left:75px; top:30px; margin-top:10px"><input class="kase partie <%=party.partie %> street input_class partie_additional_address hidden" id="additional_street_<%=party.partie %>" value="<%=party.additional_street %>" style="border: 0px solid red; width:375px" placeholder="Street" /></div>&nbsp;&nbsp;<br />
                        <div style="border:0px solid yellow; position:absolute; left:75px; width:200px; margin-left:-90px; margin-top:25px"><input value="<%= party.additional_suite %>" name="additional_suiteInput" id="additional_suiteInput" class="kase partie <%=party.partie %> suite partie_additional_address input_class hidden" style="margin-top:-26px; margin-left:90px;border: 0px solid red; width:375px" placeholder="Suite/Office/Appartment" autocomplete="off" /></div><br />&nbsp;&nbsp;
                        <div style="border:0px solid purple; position:absolute; left:65px; width:200px; margin-top: 0px; margin-left:10px"><input class="kase partie <%=party.partie %> city partie_additional_address input_class hidden" id="additional_city_<%=party.partie %>" style="width:283px;border: 0px solid red" value="<%=party.additional_city %>" placeholder="City" autocomplete="off" /></div>&nbsp;<div style="border:0px solid pink; position:absolute; left:185px; top:0px; width:200px; margin-top:105px; margin-left:179px"><input class="kase partie <%=party.partie %> state partie_additional_address input_class hidden"
              id="additional_administrative_area_level_1_<%=party.partie %>" style="width:30px;border: 0px solid red" value="<%=party.additional_state %>" placeholder="State" autocomplete="off" />&nbsp;&nbsp;<div style="border:0px solid orange; position:absolute; left:0px; top:0px; width:100px; margin-top:0px; margin-left:35px"><input class="kase partie <%=party.partie %> postal_code partie_additional_address input_class hidden" id="additional_postal_code_<%=party.partie %>" style="width:60px" value="<%=party.additional_zip %>" placeholder="Zip" autocomplete="off" />
                    	</div>
                    </div>
                </li>
                <% if (!party.blnWCAB && party.type != "venue" && current_case_id > -1) { 
                	//venue is agnostic 
                %>
                <% if ((party.type == "plaintiff") && blnPiReady) { %>   
                    <li id="ssnGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="kai gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">SSN</div></h6>
                      <div style="margin-top:-23px" class="save_holder hidden" id="ssnSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="ssnSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="<%= party.ssn %>" name="ssnInput" id="ssnInput" class="kase partie <%=party.partie %> input_class employee_class hidden" placeholder="XXX-XX-XXXX" style="margin-top:-26px; margin-left:65px; font-size: 1.3em;" onkeyup="mask(this, mssn);" onblur="mask(this, mssn);" parsley-error-message="" />
                      <span id="ssnSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-28px; margin-left:65px; font-size: 1.4em"><%= party.ssn %></span>            
                    </li>
                    <li id="dobGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">DOB</div></h6>
                        <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="dobSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="dobSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="dobInput" id="dobInput" class="kase partie <%=party.partie %> input_class employee_class hidden" style="margin-top:-26px; margin-left:65px" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" parsley-error-message="" />
                        <span id="dobSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:65px"><%= party.dob %></span>
                   </li>
               <% } %>
                <li id="party_type_optionGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; display:">
                <h6><div class="form_label_vert" style="margin-top:10px;">Partie Option</div></h6>
                    <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="party_type_optionSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="party_type_optionSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                <select autocomplete="off" name="party_type_optionInput" id="party_type_optionInput" style="margin-top:-22px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> adhoc input_class hidden" required >
                	<option value="" <%if (party.party_type_option=="") { %>selected="selected"<% } %>>Choose One</option>
                	<option id="plaintiff" value="plaintiff" <%if (party.party_type_option=="plaintiff") { %>selected="selected"<% } %>>Plaintiff</option>
                    <option id="defendant" value="defendant" <%if (party.party_type_option=="defendant") { %>selected="selected"<% } %>>Defendant</option>
                    <option id="health" value="health" <%if (party.party_type_option=="health") { %>selected="selected"<% } %>>Health</option>
                </select>
                
                	<span id="party_type_optionSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-22px; margin-left:80px;">
    	            	<%=party.party_type_option.capitalizeWords() %>
	                </span>
                </li>
                <% } %>
                <% if (party.type == "carrier" || party.type == "defense" || party.type == "prior_attorney") {
                	var adhoc = "letter_name";
                    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                    var adhoc_label = "Letter Name";
                    var adhoc_value = "";
                    if (typeof adhoc_model != "undefined") {
                    	adhoc_value = adhoc_model.get("adhoc_value");                        
                    }
                    %>
                    
                <li id="<%=party.partie.toLowerCase() %>_letter_nameGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                	<h6><div class="form_label_vert" style="margin-top:10px;">Letter Name</div></h6>
                    <input autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:-26px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> input_class adhoc hidden" parsley-error-message="" placeholder="Name of Company for Letters" />
                    <span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                    <%=adhoc_value %>
                    </span>
                </li>
                <% } %>
                
                <% if (party.type == "defendant" && blnPiReady) { %>
                        <li id="full_nameGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Contact</div></h6>
                        <input class="kase partie <%=party.partie %> input_class hidden" id="full_nameInput" name="full_nameInput" value="" style="margin-top:-26px; margin-left:80px" placeholder="Contact Person or Employee" />
                        <span id="full_nameSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px"></span>
                        </li>
                	<% } %>
				<% 
                var the_employee_title = "Employee";
                var employee_show = "";
                if (party.show_employee=="Y") {
                    if (party.employee_title!="") {
                        the_employee_title = party.employee_title;
                    }
				%>
				<li id="full_nameGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:<%=employee_show %>">
                <h6><div class="form_label_vert" style="margin-top:10px;"><%=the_employee_title %></div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="full_nameSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="full_nameSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input autocomplete="off" value="<%=party.first_name + " " + party.last_name %>" name="full_nameInput" id="full_nameInput" class="kase partie <%=party.partie %> input_class employee_class hidden" placeholder="Full Name" style="margin-top:-26px; margin-left:80px; width:375px" />
                  <span id="full_nameSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-26px; margin-left:80px"><%=party.full_name %></span>
              </li>	
              <li id="salutationGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:<%=employee_show %>">
                <h6><div class="form_label_vert" style="margin-top:10px;">Salutation</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="salutationSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="salutationSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <select name="salutationInput" id="salutationInput" class="kase partie <%=party.partie %> input_class employee_class hidden" style="height:25px; width:100px; margin-top:-30px; margin-left:80px">
                  <option value="">Select...</option>
                  <option value="Mrs">Mrs</option>
                  <option value="Mr">Mr</option>
                  <option value="Ms">Ms</option>
                  <option value="Miss">Miss</option>
                  <option value="Dr">Dr</option>
                </select>
				<span id="salutationSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-26px; margin-left:80px"><%= party.salutation %></span>
              </li>
              
              <li id="employee_phoneGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Phone</div></h6>
                <div style="margin-top:-23px" class="save_holder hidden" id="employee_phoneSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="employee_phoneSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input autocomplete="off" value="<%=party.employee_phone %>" name="employee_phoneInput" id="employee_phoneInput" class="kase partie <%=party.partie %> input_class employee_class hidden" placeholder="" style="margin-top:-26px; margin-left:80px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" parsley-error-message=""  />
                <span id="employee_phoneSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.employee_phone %></span>
            </li>
			<li id="employee_cellGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Cell</div></h6>
                <div style="margin-top:-23px" class="save_holder hidden" id="employee_cellSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="employee_cellSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input autocomplete="off" value="<%=party.employee_cell %>" name="employee_cellInput" id="employee_cellInput" class="kase partie <%=party.partie %> input_class employee_class hidden" placeholder="" style="margin-top:-26px; margin-left:80px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" parsley-error-message=""  />
                <span id="employee_cellSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.employee_cell %></span>
            </li>            
                
              <li id="employee_faxGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Fax</div></h6>
                    <div style="margin-top:-23px" class="save_holder hidden" id="employee_faxSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="employee_faxSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input autocomplete="off" value="<%=party.employee_fax %>" name="employee_faxInput" id="employee_faxInput" class="kase partie <%=party.partie %> input_class employee_class hidden" placeholder="" style="margin-top:-26px; margin-left:80px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" parsley-error-message=""  />
                    <span id="employee_faxSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.employee_fax %></span>
                </li>
             <%
                }
                var emp_row ="6";
                %>
                
                <% if (party.type == "defense") { %>
                 <% if (blnDefenseAttorneyEmail) {
                    var adhoc = "attorney_email";
                    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                    var adhoc_label = "Attorney Email";
                    var adhoc_value = "";
                    if (typeof adhoc_model != "undefined") {
                    	adhoc_value = adhoc_model.get("adhoc_value");                        
                    }
                    emp_row++;
                    
                %>
                <li id="<%=party.partie.toLowerCase() %>_attorney_emailGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                	<h6><div class="form_label_vert" style="margin-top:10px;">Atty Email</div></h6>
                    <input autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:-26px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> input_class adhoc hidden" parsley-error-message="" />
                    <span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                    <%=adhoc_value %>
                    </span>
                </li>
                <%
                } 
                }
                %>
                <% if (party.type == "law_enforcement" && customer_id == 1033) { %>
                <li id="feeGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
           			<h6><div class="form_label_vert" style="margin-top:10px;">Fee</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="feeSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="feeSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                    <input autocomplete="off" value="<%=party.fee %>" id="feeInput" name="feeInput" placeholder="0.00" type="text" class="kase partie <%=party.partie %> input_class hidden" style="margin-top:-26px; margin-left:80px; width:375px" parsley-error-message="" />
                     <span id="feeSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px"><%=party.fee %></span>
				</li>
                <li id="officerGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
           			<h6><div class="form_label_vert" style="margin-top:10px;">Officer</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="officerSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="officerSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                    <input autocomplete="off" value="<%=party.officer %>" id="officerInput" name="officerInput" placeholder="John Smith" type="text" class="kase partie <%=party.partie %> input_class hidden" style="margin-top:-26px; margin-left:80px; width:375px" parsley-error-message="" />
                     <span id="officerSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px"><%=party.officer %></span>
				</li>
                <li id="report_numberGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
           			<h6><div class="form_label_vert" style="margin-top:10px;">Report #</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="report_numberSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="report_numberSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                    <input autocomplete="off" value="<%=party.report_number %>" id="report_numberInput" name="report_numberInput" placeholder="######" type="text" class="kase partie <%=party.partie %> input_class hidden" style="margin-top:-26px; margin-left:80px; width:375px" parsley-error-message="" />
                     <span id="report_numberSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px"><%=party.report_number %></span>
				</li>
                
                <li id="dateGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
           			<h6><div class="form_label_vert" style="margin-top:10px;">Date</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="dateSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="dateSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                    <input autocomplete="off" value="<%=party.date %>" id="dateInput" name="dateInput" placeholder="00/00/0000" type="text" class="kase partie <%=party.partie %> input_class hidden" style="margin-top:-26px; margin-left:80px; width:150px" parsley-error-message="" />
                     <span id="dateSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px"><%=party.date %></span>
				</li>
                <li id="salutationGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
           			<h6><div class="form_label_vert" style="margin-top:10px;">Salutation</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="salutationSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="salutationSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                    <input autocomplete="off" value="<%=party.salutation %>" id="salutationInput" name="salutationInput" placeholder="Mr/Mrs" type="text" class="kase partie <%=party.partie %> input_class hidden" style="margin-top:-26px; margin-left:80px; width:150px" parsley-error-message="" />
                     <span id="salutationSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px"><%=party.salutation %></span>
				</li>
                
                <% } %>
                <% 
                var phone_prefix = "Comp. ";
                if (party.type == "witnesses") {
                	phone_prefix = "";
                }
                if (party.type != "defendant") { %>
                    <li id="phoneGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;"><%=phone_prefix %>Phone</div></h6>
                        <div style="margin-top:-23px" class="save_holder hidden" id="phoneSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="phoneSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input autocomplete="off" value="<%=party.phone %>" name="phoneInput" id="phoneInput" class="kase partie <%=party.partie %> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:80px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
                        <span id="phoneSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.phone %></span>
                    </li>
                    <% emp_row++; %>
                <% } %>
                <% if (party.show_employee=="N") { %>
                    <li id="employee_cellGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Cell</div></h6>
                        <div style="margin-top:-23px" class="save_holder hidden" id="employee_cellSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="employee_cellSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input autocomplete="off" value="<%=party.employee_cell %>" name="employee_cellInput" id="employee_cellInput" class="kase partie <%=party.partie %> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:80px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
                        <span id="employee_cellSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.employee_cell %></span>
                    </li>
                    <% emp_row++; %>
                <% } %>
                <li id="faxGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Comp. Fax</div></h6>
                    <div style="margin-top:-23px" class="save_holder hidden" id="faxSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="faxSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input autocomplete="off" value="<%=party.fax %>" name="faxInput" id="faxInput" class="kase partie <%=party.partie %> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:80px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" parsley-error-message="" />
                    <span id="faxSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.fax %></span>
              	</li>
                
                
                <% if (party.type == "defense") { %>
                 <% if (blnDefenseAttorneySecretary) {
                    var adhoc = "secretary";
                    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                    var adhoc_label = "Secretary";
                    var adhoc_value = "";
                    if (typeof adhoc_model != "undefined") {
                    	adhoc_value = adhoc_model.get("adhoc_value");                        
                    }
                    emp_row++;
                    
                %>
                
                <li id="<%=party.partie.toLowerCase() %>_secretaryGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                	<h6><div class="form_label_vert" style="margin-top:10px;">Secretary</div></h6>
                    <input autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:-26px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> input_class adhoc hidden" parsley-error-message="" />
                    <span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                    <%=adhoc_value %>
                    </span>
                </li>
                <%
                } 
                }
                %>
                
                
                <% if (party.type == "defense") { %>
                 <% if (blnDefenseSecretaryEmail) {
                    var adhoc = "secretary_email";
                    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                    var adhoc_label = "Secretary Email";
                    var adhoc_value = "";
                    if (typeof adhoc_model != "undefined") {
                    	adhoc_value = adhoc_model.get("adhoc_value");                        
                    }
                    emp_row++;
                    
                %>
                <li id="<%=party.partie.toLowerCase() %>_secretary_emailGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                	<h6><div class="form_label_vert" style="margin-top:10px;">Sec Email</div></h6>
                    <input autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:-26px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> input_class adhoc hidden" parsley-error-message="" />
                    <span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                    <%=adhoc_value %>
                    </span>
                </li>
                <%
                	} 
                }
                %>
                
                
                <% if ((party.type == "carrier" || party.type == "defense") && party.blnWCAB) { %>
                <li id="<%=party.partie.toLowerCase() %>_doiGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                <h6><div class="form_label_vert" style="margin-top:10px;">DOI</div></h6>
                <select class="kase partie <%=party.partie %> input_class hidden" id="injury_idInput" name="injury_idInput" style="margin-top:-26px; margin-left:80px">
                </select>
                <span id="injury_idSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px"></span>
                </li>
                <% } %>
                
                <% if (party.type == "defendant") { %>
                <li id="dobGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                <h6><div class="form_label_vert" style="margin-top:10px;">DOB</div></h6>
                <input value="<%=party.dob %>" class="kase partie <%=party.partie %> input_class hidden" id="dobInput" name="dobInput" style="margin-top:-26px; margin-left:80px" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" />
                <span id="dobSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.dob %></span>
                </li>
                <li id="ssnGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                <h6><div class="form_label_vert" style="margin-top:10px;">SSN</div></h6>
                <input value="<%=party.ssn %>" class="kase partie <%=party.partie %> input_class hidden" id="ssnInput" name="ssnInput" style="margin-top:-26px; margin-left:80px" onkeyup="mask(this, mssn);" onblur="mask(this, mssn);" />
                <span id="ssnSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.ssn %></span>
                </li>
                
                <% emp_row++; %>
                <li id="phoneGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                <h6><div class="form_label_vert" style="margin-top:10px;">Work Phone</div></h6>
                <input value="<%=party.phone %>" class="kase partie <%=party.partie %> input_class hidden" id="phoneInput" name="phoneInput" style="margin-top:-26px; margin-left:80px" />
                <span id="phoneSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.phone %></span>
                </li>
                
                <li id="employee_faxGrid" data-row="<%=emp_row %>" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Work Fax</div></h6>
                    <div style="margin-top:-23px" class="save_holder hidden" id="employee_faxSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="employee_faxSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input autocomplete="off" value="<%=party.employee_fax %>" name="employee_faxInput" id="employee_faxInput" class="kase partie <%=party.partie %> input_class employee_class hidden" placeholder="" style="margin-top:-26px; margin-left:80px" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" parsley-error-message=""  />
                    <span id="employee_faxSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-28px; margin-left:80px"><%=party.employee_fax %></span>
                </li>
                <% } %>
                <% if (blnClaimNumber) {
                    var adhoc = "claim_number";
                    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                    var adhoc_label = "Claim #";
                    var adhoc_value = "";
                    if (typeof adhoc_model != "undefined") {
                    	adhoc_value = adhoc_model.get("adhoc_value");                        
                    }
                    emp_row++;
                    
                %>
                
                <li id="<%=party.partie.toLowerCase() %>_claim_numberGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                	<h6><div class="form_label_vert" style="margin-top:10px;">Claim #</div></h6>
                    <input autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:-26px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> input_class adhoc hidden" parsley-error-message="" />
                    <span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
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
                <h6><div class="form_label_vert" style="margin-top:10px;">Coverage</div></h6>
                    <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="coverage_date_startSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="coverage_date_startSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                <div style="margin-top:-22px; margin-left:80px; width:380px">
                	<div style="display:inline-block" class=""><input autocomplete="off" name="coverage_date_startInput" id="coverage_date_startInput" style="width:80px;margin-top: -13px" class="kase partie <%=party.partie %> adhoc input_class hidden" parsley-error-message="" value="<%=coverage_date_start %>" placeholder="Start" />
                    </div>
                    <div style="display:inline-block;margin-left: 100px;">&nbsp;&nbsp;<input autocomplete="off" name="coverage_date_endInput" id="coverage_date_endInput" style="width:80px;margin-top: 2px;" class="kase partie <%=party.partie %> adhoc input_class hidden" parsley-error-message="" value="<%=coverage_date_end %>" placeholder="End" />
                    </div>
                </div>
                	<div id="coverage_date_startSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-22px; margin-left:80px; width:380px">
    	            	<div style="display:inline-block">Start:&nbsp;<%=coverage_date_start %>
	                &nbsp;&nbsp;</div>
            			<div style="display:inline-block">End:&nbsp;<%=coverage_date_end %></div>
        	        </div>
                </li>
                <% } %>
                <% 
                	emp_row++; 
                }
                %>
                
                <% if (blnMPN) {
                    var adhoc = "mpn";
                    var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                    var adhoc_label = "MPN";
                    var adhoc_value = "";
                    if (typeof adhoc_model != "undefined") {
                    	adhoc_value = adhoc_model.get("adhoc_value");                        
                    }
                    emp_row++;
                    
                %>
                
                <li id="<%=party.partie.toLowerCase() %>_mpnGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                	<h6><div class="form_label_vert" style="margin-top:10px;">MPN</div></h6>
                    <input autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:-26px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> input_class adhoc hidden" parsley-error-message="" />
                    <span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                    <%=adhoc_value %>
                    </span>
                </li>
                	
                <% 
                	emp_row++; 
                }
                %>
                
                <% if (party.type=="venue" && !party.blnWCAB) { %>
                <li id="<%=party.partie.toLowerCase() %>_jurisdictionGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Court Type</div></h6>
                    <select name="jurisdictionExtraInput" id="jurisdictionExtraInput" class="kase partie <%=party.partie %> input_class hidden" style="height:25px; width:100px; margin-top:-26px; margin-left:80px">
                        <option value="">Select...</option>
                        <option value="State">State</option>
                        <option value="Federal">Federal</option>
                    </select>
                    <span id="jurisdictionExtraSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px;"></span>
                </li>
                <li id="<%=party.partie.toLowerCase() %>_countyGrid" data-row="<%=emp_row %>" data-col="2" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">County</div></h6>
                    <input name="countyExtraInput" id="countyExtraInput" class="kase partie <%=party.partie %> input_class hidden" style="height:25px; width:100px; margin-top:-26px; margin-left:80px">
                    <span id="countyExtraSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px;"></span>
                </li>
                <% emp_row++; %>
                <li id="<%=party.partie.toLowerCase() %>_departmentGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Department</div></h6>
                    <input name="departmentExtraInput" id="departmentExtraInput" class="kase partie <%=party.partie %> input_class hidden" style="height:25px; width:100px; margin-top:-26px; margin-left:80px" />
                    <span id="departmentExtraSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px;"></span>
                </li>
                <li id="<%=party.partie.toLowerCase() %>_department_phoneGrid" data-row="<%=emp_row %>" data-col="2" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Dept Phone</div></h6>
                    <input name="department_phoneExtraInput" id="department_phoneExtraInput" class="kase partie <%=party.partie %> input_class hidden" style="height:25px; width:100px; margin-top:-26px; margin-left:80px">
                    <span id="department_phoneExtraSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px;"></span>
                </li>
                <% emp_row++; %>
                <li id="<%=party.partie.toLowerCase() %>_districtGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">District</div></h6>
                    <input name="districtExtraInput" id="districtExtraInput" class="kase partie <%=party.partie %> input_class hidden" style="height:25px; width:100px; margin-top:-26px; margin-left:80px" />
                    <span id="districtExtraSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px;"></span>
                </li>
                <li id="<%=party.partie.toLowerCase() %>_branchGrid" data-row="<%=emp_row %>" data-col="2" data-sizex="1" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Branch</div></h6>
                    <input name="branchExtraInput" id="branchExtraInput" class="kase partie <%=party.partie %> input_class hidden" style="height:25px; width:100px; margin-top:-26px; margin-left:80px">
                    <span id="branchExtraSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px;"></span>
                </li>
                <% emp_row++; 
                } %>
                <% 
                    if (typeof party.type == "undefined") {
                        party.partie_type_option = "";
                    }
                    var new_filler = party.partie_type_option;
            	%>
                <li id="party_defendant_optionGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                <h6><div class="form_label_vert" style="margin-top:10px;" id="party_defendant_label">Defense Opt.</div></h6>
                    <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="party_defendant_optionSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="party_defendant_optionSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    
                        <div id="party_defendant_optionDiv" style="border:0px solid white; z-index:9999; margin-top:-22px; margin-left:80px">
                        </div>
                	
                	<span id="party_defendant_optionSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:0px; margin-left:80px;">
    	            	<%=party.party_defendant_option %>
	                </span>
                </li>		
               
                <li id="company_siteGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
           			<h6><div class="form_label_vert" style="margin-top:10px;">Website</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="company_siteSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="company_siteSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                    <input autocomplete="off" value="<%=party.company_site %>" id="company_siteInput" name="company_siteInput" placeholder="Enter your Site" type="text" class="kase partie <%=party.partie %> input_class hidden" style="margin-top:-26px; margin-left:80px; width:375px" parsley-error-message="" />
                     <span id="company_siteSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:80px"><%=party.company_site %></span>
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
				
                 <% if (blnMedicalIntakeEmail) { 
                        var adhoc = "intake_email";
                        var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                        var adhoc_label = "Intake Email";
                        var adhoc_value = "";
                        if (typeof adhoc_model != "undefined") {
                            adhoc_value = adhoc_model.get("adhoc_value");                        
                        }
                        emp_row++;
                 %>
                <li id="<%=party.partie.toLowerCase() %>_intake_emailGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                	<h6><div class="form_label_vert" style="margin-top:10px;">Intake Email</div></h6>
                    <input autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:-26px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> input_class adhoc hidden" parsley-error-message="" />
                    <span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                    <%=adhoc_value %>
                    </span>
                </li>
                <%
                } 
                %>
                 <% if (blnMedicalReportsEmail) { 
                 		var adhoc = "reports_email";
                        var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                        var adhoc_label = "Report Email";
                        var adhoc_value = "";
                        if (typeof adhoc_model != "undefined") {
                            adhoc_value = adhoc_model.get("adhoc_value");                        
                        }
                        emp_row++;
                    
                 %>
                 
                <li id="<%=party.partie.toLowerCase() %>_reports_emailGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                	<h6><div class="form_label_vert" style="margin-top:10px;">Report Email</div></h6>
                    <input autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:-26px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> input_class adhoc hidden" parsley-error-message="" />
                    <span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                    <%=adhoc_value %>
                    </span>
                </li>
                <%
                } 
                %>
                 <% if (blnMedicalReferralsEmail) { 
                 		var adhoc = "referrals_email";
                        var adhoc_model = adhoc_name_values.findWhere({"adhoc": adhoc});
                        var adhoc_label = "Ref Email";
                        var adhoc_value = "";
                        if (typeof adhoc_model != "undefined") {
                            adhoc_value = adhoc_model.get("adhoc_value");                        
                        }
                        emp_row++;
                 %>
                <li id="<%=party.partie.toLowerCase() %>_referrals_emailGrid" data-row="<%=emp_row %>" data-col="1" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                	<h6><div class="form_label_vert" style="margin-top:10px;">Ref Email</div></h6>
                    <input autocomplete="off" value="<%=adhoc_value %>" name="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Input" style="margin-top:-26px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> input_class adhoc hidden" parsley-error-message="" />
                    <span id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Span" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                    <%=adhoc_value %>
                    </span>
                </li>
                <%
                } 
                %>
                <li id="employee_emailGrid" data-row="<%=emp_row_0 + 2 %>" data-col="2" data-sizex="2" data-sizey="1" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Email</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-16px" class="hidden" id="employee_emailSave">
                    <a class="save_field" style="margin:0px;" title="Click to save this field" id="employee_emailSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input autocomplete="off" value="<%=party.employee_email %>" name="employee_emailInput" id="employee_emailInput" style="margin-top:-26px; margin-left:80px; width:375px" class="kase partie <%=party.partie %> input_class employee_class hidden" parsley-error-message="" />
                <span id="employee_emailSpan" class="kase partie <%=party.partie %> span_class employee_span_class form_span_vert" style="margin-top:-28px; margin-left:80px">
                <%=party.employee_email %>
                </span>
                </li>
                <% } %>
                <li id="commentsGrid" data-row="<%=emp_row_0 + 3 %>" data-col="1" data-sizex="2" data-sizey="3" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Comments</div></h6>
                    <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="commentsSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="commentsSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <textarea autocomplete="off" name="commentsInput" id="commentsInput" cols="50" rows="4" class="kase partie <%=party.partie %> input_class hidden" style="position:relative; width:441px; margin-left:10px; margin-top:6px"><%=party.comments %></textarea>
                    
                	<span id="commentsSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="margin-top:-22px; margin-left:80px;">
    	            	<%=party.comments %>
	                </span>
                </li>
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
                        if (adhoc_label.length < 4) {
	                        adhoc_label = adhoc_label.toUpperCase();
                        }
                        if (adhoc_type=="checkbox") {
                        	adhoc_label += "&nbsp;&nbsp;<span style='font-size:0.8em'>(check for Y)</span>"; 
                        }
                        //all exempted adhocs
                        if (adhoc_label!="" && adhoc!="claims" && adhoc!="mpn" && adhoc!="claim_number" && adhoc!="doctor_type" && adhoc!="assigned_to" && adhoc!="coverage_date_start" && adhoc!="coverage_date_end" && adhoc!="letter_name" && adhoc!="attorney_email" && adhoc!="secretary" && adhoc!="secretary_email" && adhoc!="intake_email" && adhoc!="reports_email" && adhoc!="referrals_email" && adhoc!="primary_secondary") {
                 %>                
                <li id="<%=party.partie.toLowerCase() %>_<%=adhoc %>Grid" data-row="<%=row_counter %>" data-col="<%=data_col_value %>" data-sizex="<% if (adhoc == 'dl_number') { %>2<% } else { %>1<% } %>" data-sizey="1" class="partie gridster_border adhoc_grid" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px;">
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
                
                <% 
                var blnShowCopying = false;
                if (party.type == "medical_provider" && party.additional_partie=="p") {
                	blnShowCopying = true;
                }
                var display_copy = "none";
                if (blnShowCopying) {
	                display_copy = "";
                } 
                %>
                <li id="copyingRequestGrid" data-row="<%=row_counter %>" data-col="<%=data_col_value %>" data-sizex="2" data-sizey="2" class="partie <%=party.partie %> gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; color:#FFFFFF; display:none">
                	<div style="color:#FFFFFF">
                    	Do you want to have Matrix copy this Medical Provider as a Subpoena Location?
                    </div>
                    <div style="margin-top:10px; margin-left:10px">
                    	<div style="display:inline-block">
                        	<button class="btn btn-primary btn-sm" id="matrix_copy_yes">Yes. Click to enter Instructions</button>
                        </div>
                        <div style="display:inline-block; margin-left:10px">
                        	<button class="btn btn-default btn-sm" id="matrix_copy_no">No. Proceed with Save</button>
                        </div>
                    </div>
                </li>
                <% row_counter++ %>
                <li id="copyingInstructionsGrid" data-row="<%=row_counter %>" data-col="<%=data_col_value %>" data-sizex="2" data-sizey="2" class="partie <%=party.partie %> gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; display:<%=display_copy %>">
                	<h6><div class="form_label_vert" style="margin-top:10px;">Records Requested</div></h6>
					<table border="0" style="margin-top:10px; margin-left:10px">
                        <tr>
                            <td style="width:30px; margin-bottom:-10px" valign="bottom">
                                <input name="medical_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="medical_copyInput" class="kase partie <%=party.partie %> input_class copy_input hidden" value="M" <% if (party.copying_instructions.indexOf("M") > -1) { %>checked<% } %> /><span id="medical_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("M") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                            </td>
                            <td style="width:60px; margin-top:-10px; color:#FFFFFF" align="left" valign="top">
                                Medical
                            </td>
                            <td style="width:30px; margin-bottom:-10px" valign="bottom">
                                <input name="billing_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="billing_copy" class="kase partie <%=party.partie %> input_class copy_input hidden" value="B" <% if (party.copying_instructions.indexOf("B") > -1) { %>checked<% } %> /><span id="billing_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("B") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                            </td>
                            <td style="width:70px; margin-top:-10px; color:#FFFFFF" align="left" valign="top">
                                Billing
                            </td>
                            <td style="width:30px; margin-bottom:-10px" valign="bottom">
                                <input name="xray_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="xray_copy" class="kase partie <%=party.partie %> input_class copy_input hidden" value="X" <% if (party.copying_instructions.indexOf("X") > -1) { %>checked<% } %> /><span id="xray_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("X") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                            </td>
                            <td style="width:90px; margin-top:-10px; color:#FFFFFF" valign="top">
                                X-ray Films
                            </td>
                            <td style="width:30px; margin-bottom:-10px" valign="bottom">
                                <input name="other_copy" class="kase partie <%=party.partie %> input_class copy_input hidden" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="other_copy" value="O" <% if (party.copying_instructions.indexOf("O") > -1) { %>checked<% } %> /><span id="other_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("O") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                            </td>
                            <td style="width:90px; margin-top:-10px; color:#FFFFFF" valign="top">
                                Other
                            </td>
                        </tr>
                        <tr>
                            <td valign="bottom" style="margin-bottom:-10px">
                                <input name="wage_copy" class="kase partie <%=party.partie %> input_class copy_input copy_secondrow hidden" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="wage_copy" value="W" <% if (party.copying_instructions.indexOf("W") > -1) { %>checked<% } %> /><span id="wage_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("W") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                            </td>
                            <td valign="top" style="margin-top:-10px; color:#FFFFFF">
                                Wage
                            </td>
                            <td valign="bottom" style="margin-bottom:-10px">
                                <input name="claim_copy" class="kase partie <%=party.partie %> input_class copy_input copy_secondrow hidden" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="claim_copy" value="C" <% if (party.copying_instructions.indexOf("C") > -1) { %>checked<% } %> /><span id="claim_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("C") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                            </td>
                            <td valign="top" style="margin-top:-10px; color:#FFFFFF">
                                Claim File
                            </td>
                            <td valign="bottom" style="margin-bottom:-10px">
                                <input name="employment_copy" style="position:relative;width:20px;padding-top:30px;" type="checkbox" id="employment_copy" class="kase partie <%=party.partie %> input_class copy_input copy_secondrow hidden" value="E" <% if (party.copying_instructions.indexOf("E") > -1) { %>checked<% } %> /><span id="employment_copySpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px" ><% if (party.copying_instructions.indexOf("E") > -1) { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                            </td>
                            <td valign="top" style="margin-top:-10px; color:#FFFFFF">
                                Employment
                            </td>
                            <td>
                                <input name="any_all" type="checkbox" class="kase partie <%=party.partie %> input_class copy_input copy_secondrow hidden" style="position:relative;width:20px" id="any_allInput" value="Y" <% if (party.any_all=="Y") { %>checked<% } %> /><span id="any_allSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative; width:20px; color:#FFFFFF" ><% if (party.any_all=="Y") { %>&#x2713;<% } else { %>&nbsp;<% } %></span>
                            </td>
                            <td style="width:70px; margin-top:-10px; color:#FFFFFF" align="left" valign="top">
                            	Any&nbsp;and&nbsp;All
                            </td>
                        </tr>
                    </table>
                </li>
                <li id="specialInstructionsGrid" data-row="<%=row_counter %>" data-col="<%=data_col_value %>" data-sizex="2" data-sizey="3" class="partie gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; display:<%=display_copy %>">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Special Instructions</div></h6>
                    <textarea name="special_instructions" cols="50" rows="4" class="kase partie <%=party.partie %> input_class hidden" id="special_instructions" style="position:relative; width:441px; margin-left:10px; margin-top:6px"><%=party.special_instructions %></textarea>
                    <span id="special_instructionsSpan" class="kase partie <%=party.partie %> span_class form_span_vert" style="position:relative;" ><%=party.special_instructions %></span>
                    <div style="display:none; margin-top:10px; margin-left:10px" id="continue_copy_holder">
                    <button class="btn btn-primary btn-sm" id="continue_copy">Continue</button>
                    </div>
               </li>
               <% row_counter++ %>
               <li id="saveButtonGrid" data-row="<%=row_counter %>" data-col="<%=data_col_value %>" data-sizex="2" data-sizey="1" class="partie gridster_border" style="visibility:hidden; background:none; box-shadow: none; webkit-box-shadow: none; border:0px">
               		<div style="float:right">
               			<button class="backtotop btn btn-default">Top</button>
                        &nbsp;&nbsp;
                        <button class="save btn btn-success">Save</button>
                    </div>
               </li>
          </ul>
          <% if (!party.gridster_me) { %>
            <a href="#parties/<%=party.case_id %>/<%=party.id %>/<%=party.partie.toLowerCase() %>"><img src="img/glass_add.png" width="20" height="20" border="0" /></a>
           <% } %>
        </form>
    </div>
</div>

<% if (party.type == "plaintiff" || party.type == "defendant") { %>
    <div id="kai_holder" class="<%=party.blurb %> col-md-5" style="border:0px solid pink; margin-left:100px; margin-top:0px"></div>
<% } %>

<% if (party.type == "plaintiff" || party.type == "defendant") { %>
	<div id="partie_notes" class="<%=party.blurb %> col-md-5" style="display:none; border:0px solid pink; margin-left:100px; margin-top:30px"></div>
<% } else { %>
	<% if (party.type == "employer") { %>
    <div id="employer_lostincome" class="<%=party.blurb %> col-md-5" style="display:none; border:0px solid pink; margin-left:100px; margin-top:0px; margin-bottom:20px"></div>
    <% } %>
    <% if (party.type == "medical_provider") { %>
    <div id="medical_billings" class="<%=party.blurb %> col-md-5" style="display:none; border:0px solid pink; margin-left:100px; margin-top:0px; margin-bottom:20px"></div>
    <% } %>
    <% if (party.type == "other") { %>
    <div id="other_billings" class="<%=party.blurb %> col-md-5" style="display:none; border:0px solid pink; margin-left:100px; margin-top:0px; margin-bottom:20px"></div>
    <% } %>
    <% if (party.type == "referring") { %>
    <div id="referral_fee" class="<%=party.blurb %> col-md-5" style="display:none; background:url(img/glass_dark.png) left top repeat-y; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-left:115px; margin-top:0px; margin-bottom:20px">
    <table width="98%" cellspacing="0" cellpadding="2">
        <tr>
            <td align="left" valign="top" width="50%">
                   Settlement Fee:&nbsp;
                  $<span id="referral_source_feeSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:0px"></span>
            </td>
            <td align="left" valign="top" width="50%">
                   Fee Date:&nbsp;
                  <span id="referral_source_dateSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:0px"></span>
            </td>
        </tr>
    </table>
    </div>
    <% } %>
    <% if (party.type == "carrier") { %>
     <div id="carrier_financial" class="<%=party.blurb %> col-md-5" style="display:none; border:0px solid pink; margin-left:100px; margin-top:0px; margin-bottom:20px"></div>
     <div id="carrier_neg" class="<%=party.blurb %> col-md-5" style="display:none; border:0px solid pink; margin-left:100px; margin-top:0px; margin-bottom:20px"></div>
    <% } %>
	<div id="partie_notes" class="<%=party.blurb %> col-md-5" style="display:none; border:0px solid pink; margin-left:100px; margin-top:0px"></div>
<% } %>

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
            <input class="field" id="locality_<%=party.partie %>" disabled="true" />
            <input class="field" id="sublocality_<%=party.partie %>" disabled="true" />
            <input class="field" id="neighborhood_<%=party.partie %>" disabled="true" />
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">Country</td>
        <td class="wideField" colspan="3">
        	<input class="field" id="country_<%=party.partie %>" disabled="true"></input>
        </td>
      </tr>
    </table>
</div>
<div id="additional_addressGrid" style="display:">
    <table id="address">
      <tr style="display:none">
        <td class="label">Additional Street address</td>
        <td class="slimField"><input class="field" id="additional_street_number_<%=party.partie %>" disabled="true" />
        </td>
        <td class="wideField" colspan="2"><input class="field" id="additional_route_<%=party.partie %>" disabled="true" />
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">Additional City</td>
        <td class="wideField" colspan="3">
            <input class="field" id="additional_locality_<%=party.partie %>" disabled="true" />
            <input class="field" id="additional_sublocality_<%=party.partie %>" disabled="true" />
            <input class="field" id="additional_neighborhood_<%=party.partie %>" disabled="true" />
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">Additional Country</td>
        <td class="wideField" colspan="3">
        	<input class="field" id="additional_country_<%=party.partie %>" disabled="true"></input>
        </td>
      </tr>
    </table>
</div>
<div id="partie_all_done"></div>
<script language="javascript">
$( "#partie_all_done" ).trigger( "click" );
</script>

<% if (party.gridster_me || party.grid_it) { %>
<script language="javascript">
var trig = "1";

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