<?php
require_once('../shared/legacy_session.php');

session_write_close();

include ("../api/connection.php");
include ("../browser_detect.php");

$sql_customer = "SELECT *
FROM  `ikase`.`cse_customer` 
WHERE customer_id = :customer_id";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
	$stmt->execute();
	$customer = $stmt->fetchObject();
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}

$blnDeletePermission = true;
if ($_SESSION["user_customer_id"]==1075) {
	//per steve g 4/3/2017
	$blnDeletePermission = false;
	if (strpos($_SESSION['user_role'], "admin") !== false) {
		$blnDeletePermission = true;
		//echo $_SESSION['user_role'] . " true";
	}
}
?>
<%
var blnImm = (kase.get("case_type")=="immigration");
var blnSSN = (kase.get("case_type")=="social_security");
var blnQuickNotes = false;
%>
<div>
	<div id="kase_abstract_holder">
    <div class="glass_header">
        <% 
       
        if (blnUploadDash) { %>
        <div style="float:right">
               <!-- <div id="message_attachments_one" style="width:90%">Upload</div> -->
               <!-- <input id="file_upload1" name="file_upload1" type="file" multiple="true"> -->
        </div>
        <div style="float:right; margin-right:15px; display: none">
        	<button id="compose_task" class="btn btn-sm btn-success" title="Click to add new Task">New Task</button>
        </div>
        <% } %>
        <div style="float:right; margin-right:20px">
        	<a href="http://ec2-35-91-52-48.us-west-2.compute.amazonaws.com/pdr" target="_blank"><button class="btn btn-sm btn-success" title="Click to open Matrix PD / TD Calc">Matrix PD / TD Calc</button></a>
        </div>
        <% if (blnWCAB) { %>
    	<div style="float:right; margin-right:20px">
        	<button id="search_qme" class="btn btn-sm btn-primary" title="Click to search the EAMS database of QME Medical Provider">Import QMEs from EAMS</button>
        </div>
        <% } %>
        <% if (blnMedicalBilling) { %>
        <div style="float:right; margin-right:20px">
        	<!--<button id="medsum_button" class="btn btn-sm" title="Click to see Medical Listing">Medical Billing List</button>-->
            <button id="medsum_summary" class="btn btn-sm" title="Click to see Medical Summary">Medical Summary</button>
        </div>
        <% } %>
        <div style="float:right; margin-right:20px">
        	<button id="file_location_button" class="btn btn-sm btn-primary" title="Click to set the File Location">File Location</button>
            <select id="file_location" style="display:none">
            	<option value="">Select from List</option>
                <option value="Attorney Court">Attorney Court</option>                
                <option value="Attorney Desk">Attorney Desk</option>
                <option value="Coordinator Desk">Coordinator Desk</option>
            	<option value="File Cabinet">File Cabinet</option>                                
            </select>
            <span id="file_location_feedback" style="color:white"></span>
        </div>
        <% if (blnWCAB) { %>
        <div style="float:right; margin-right:20px">
        	<button id="refer_vocational" class="btn btn-sm" title="Refer for Vocational Services">Refer for Vocational Services</button>
        </div>
        <% } %>
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%=kase.get('uuid') %>" />
    	<div style="float:right; display:none">
        	<input id="corporation_searchList" type="text" class="search-field" placeholder="Search" autocomplete="off" onkeyup="findIt(this, 'partie_listing', 'partie')">
        </div>

        <div style="display:inline-block; text-align:left; vertical-align:top">
            <span style="font-size:1.2em; color:#FFFFFF">
                <%=panel_title %>
            </span>
            <div style="">
                <button title="Click for New Partie" id="new_partie" class="btn btn-sm btn-primary">
                    New Party
                </button>
            </div>
        </div>
        <div style="display:inline-block">
        	<div style="border:0px solid green; text-align:left">
                <!--
                <div style="display:inline-block; vertical-align:top; text-align:left; border:0px solid pink">
                <a title="Click for New Partie" id="new_partie" href='#parties/<%=case_id %>/-1/new' style="color:#FFFFFF; text-decoration:none;">
                    <button class="btn btn-transparent" style="color:white; border:0px solid; width:20px">
                        <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
                    </button>
                </a>
                </div>
                -->
                <div class="white_text" style="display:inline-block; padding-left:50px">
                	<div style="float:right; display:<%=kase.get("claims_display") %>">
                    	<span class='black_text'>&nbsp;|&nbsp;</span><%= kase.get("claims_values") %>
                    </div>
		    <%if (kase.get("case_number")!=null && kase.get("case_number")!="") {%>
                    	<%= kase.get("case_number") %>
		    <%} else if (kase.get("file_number")!=null && kase.get("file_number")!="") {%>
			<%=kase.get("file_number") %>
		    <%} else if (kase.get("cpointer")!=null && kase.get("cpointer")!="") {%>
			<%=kase.get("cpointer") %>
		    <%} else {%>
			<%="" %>
		    <%}%>
                    <%
                    if (kase.get("case_type")=="NewPI") {
	                    kase.set("case_type", "Personal Injury");
                    } 
                    var sol = kase.get("statute_limitation");
                    %>
                    <% if (blnWCAB || blnImm) { %>
                    <span class='black_text'>&nbsp;|&nbsp;</span><%= kase.get("adj_number") %><span class='black_text'>&nbsp;|&nbsp;</span>
                    <% } %>
                    <%= kase.get("case_type").replaceAll("_", " ").capitalizeWords().replace("Wcab", "WCAB") %>
                    <span class='black_text'>&nbsp;/&nbsp;</span><%= kase.get("case_sub_type") %>
                    <span class='black_text'>&nbsp;|&nbsp;</span>
                    Case&nbsp;Date:&nbsp;<%= kase.get("case_date") %>
                    <span class='black_text'>&nbsp;|&nbsp;</span>
                    Claim&nbsp;#:&nbsp;<%= claim_number %>
                    <% //if (sol!="") { %>
                    <!--<span class='black_text'>&nbsp;|&nbsp;</span>
                    SOL:&nbsp;<%=sol %>
                    -->
                    <% //} %>
                    <div style="margin-top:5px">
                        <% if (kase.get("sub_in")=="Y") { %>Sub-In<span class='black_text'>&nbsp;|&nbsp;</span><% } %>
                        Status:&nbsp;<%= case_status %><% if (kase.get("case_substatus")!="") { %><span class='white_text'>&nbsp;/&nbsp;</span><%= kase.get("case_substatus") %><% } %><% if (kase.get("case_subsubstatus")!="") { %><span class='white_text'>&nbsp;/&nbsp;</span><%= kase.get("case_subsubstatus") %><% } %><% if (rating!="") { %><span class='black_text'>&nbsp;|&nbsp;</span>Rating:&nbsp;<%=rating %><% } %><% if (kase.get("interpreter_needed")!="N") { %><span class='black_text'>&nbsp;|&nbsp;</span><span class="red_text white_background">Interpreter&nbsp;Needed&nbsp;for&nbsp;<%=kase.get("case_language") %></span><% } %>
                    </div>
                </div>
            </div> 
        </div>
        <div id="bodyparts_warning" style="position:absolute; display:none; left:1440px; color:white; margin-top:20px; width:250px; border:1px solid orange; padding:1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; "></div>
    </div>
    </div>
    	<% if (blnUseRightPane) { %>
        <div id="dashboard_right_pane" style="color:white;border:1px solid white;float: right;width: 350px;margin-top: 10px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:none">
        Right Pane
        </div>
        <% } %>
      <div class="gridster" id="gridster_parties_cards" style="margin-left:-5px; padding-top:5px">
      	<ul>
        <% 
		var kase_type = this.model.get("case_type");
        //var carrier_insurance_type_option = this.model.get("carrier_insurance_type_option");
        //console.log(carrier_insurance_type_option);
		var blnWCAB = ((kase_type.indexOf("Worker") > -1) || (kase_type.indexOf("WC") > -1));
        var blnWCABDefense = (kase_type.indexOf("WCAB_Defense") == 0);
        var row_counter = 1;
        var employer_count = 0;
        var plaintiff_count = 0;
        var defendant_count = 0;
        <?php //per steve g 4/5/2017
		if ($_SESSION["user_customer_id"]==1075) { ?>
        var employer_count = 1;
        <?php } ?>
        var column_counter = 1;
        var blnDemographics = false;
        
        if (typeof column_max == "undefined") {
	        var column_max = 4;
        }
        var last_party_type_option = "plaintiff";
        var parties_count = parties.length;
        _.each( parties, function(partie) {
        	var thehref = "#parties/" + case_id + "/" + partie.corporation_id + "/" + partie.type.replaceAll(" ", "_");
            partie.partie_id = partie.corporation_id;
            if (partie.type=="applicant") { 
                thehref = "#applicant/" + case_id;
                partie.partie_id = "applicant";
            }

            var additional_addresses = partie.additional_addresses;
            if (additional_addresses!="") {
                var jadd = JSON.parse(additional_addresses);
                if (typeof jadd.address_2 != "undefined") {
                    var arrAdditionalAddress = [];
                    arrAdditionalAddress.push(jadd.address_2[2]);
                    if (jadd.address_2[1]!="") {
                        arrAdditionalAddress.push(jadd.address_2[1]);
                    }
                    var city_state_zip = jadd.address_2[3] + ", " + jadd.address_2[4] + " " + jadd.address_2[5];
                    arrAdditionalAddress.push(city_state_zip)
                   
                    additional_addresses = arrAdditionalAddress.join("<br />");
                } else {
                	additional_addresses = "";
                }
            }
			partie.additional_addresses = additional_addresses;
            
            var arrAddress = [];
            if (partie.street!="") {
            	arrAddress.push(partie.street);
            }
            if (partie.city!="") {
            	arrAddress.push(partie.city);
            }
            if (partie.state!="") {
            	arrAddress.push(partie.state);
            }
            address = arrAddress.join(", ");
            
            var partie_type = partie.partie_type;
            if (partie_type == null) {
            	if (partie.type == null) {
            		partie_type = "";
                } else {
	                partie_type = partie.type;
                }
            }
            partie_type = partie_type.replaceAll("_", " ");
            if (partie_type.length < 5) {
            	partie_type = partie_type.toUpperCase();
            } else {
	            partie_type = partie_type.capitalize();
            }
            if (partie_type=="Applicant") { 
				if (!blnWCAB) {
                	partie_type = "Plaintiff";
                    if (blnSSN) {
	                    partie_type = "Claimant";
                    }
					thehref = "#applicant/" + case_id;
				}
            }
            if (!blnWCAB && blnImm) { 
            	if (partie.type=="plaintiff") {
            		//plaintiff == applicant for immigration
            		partie_type = "Applicant";
                }
            }
            if (blnSSN) {	
	            if (partie.type=="plaintiff") {
	                partie_type = "Claimant";
                }
            }
            if (!blnWCAB) { 
            	if (partie_type=="Venue") { 
                	partie_type = "Court";
                }
            }
            var glass = "";
            switch(partie.type) {
            	case "applicant":
                	glass = "_card_fade";
                    break;
                case "carrier":
                	glass = "_card_fade_7";
                    break;
                case "defendant":
                	glass = "_card_fade_2";
                	break;
                case "employer":
                	glass = "_card_fade_7";
                	break;
                case "enforcement":
                	glass = "_card_fade_4";
                	break;
                case "expert":
                	glass = "_card_fade_5";
                	break;
                case "referral":
                	glass = "_card_fade_6";
                	break;
                case "voc_rehab":
                	glass = "_card_fade_7";
                	break;
                case "witness":
                	glass = "_card_fade";
                	break;
                
                
            }
            var employer_show_employee = "yes"; 
        %>
        
        <%
        if (applicant_language=="" && kase.get("case_language")!="") {
            applicant_language = kase.get("case_language");
        }
        if (typeof partie.party_type_option == "undefined") {
            partie.party_type_option = "";
        }
        if (partie.party_type_option == null) {
            partie.party_type_option = "";
        }
        switch(partie.assigned_to) {
        	case "Applicant":
                partie.assigned_to = "<span><img src='../img/thumbs_up.png' height='20' width='20' title='Assigned to Applicant'></span>";
                break;
            case "Neutral":
                partie.assigned_to = "<span><img src='../img/spacer.gif' height='20' width='20'  title='Neutral Assignment'></span>";
                break;
            case "Defense":
                partie.assigned_to = "<span><img src='../img/thumbs_down.png' height='20' width='20' title='Assigned to Defense'></span>";
                break;
        }
        var new_filler = partie.new_filler;

        if (panel_title== "Dashboard" && !blnDemographics) {
            if ((blnWCAB && partie.type=="applicant") || (!blnWCAB && partie.type=="plaintiff") || (!blnWCAB && partie.type=="applicant") || (!blnWCAB && !blnPlaintiff)) {
            	blnDemographics = true;
                blnPlaintiff = true;
         %>
        	<li id="demographicsGrid" data-row="<%=row_counter %>" data-col="<%=column_counter %>" data-sizex="1" data-sizey="1" class="partie gridster_border gridster_holder" style="background:url(img/glass_card_dark_7.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-left:0px">
            	<div style="float:right; display:" id="matrix_imported">
                
                <button id="send_matrix_link" title="Click to email this Kase to Matrix Document Imaging" class="btn btn-xs" style="linear-gradient(to bottom,#6a42ca 0,#2d31a2 100%); border-color: #2b399a;">Matrix</button>
                
                </div>
                <h3 style="margin-top:0px">
                	<% if (partie.type=="applicant") { %>
                		<a href="reports/demographics_sheet.php?case_id=<%=case_id %>" title="Click to view print-ready Demographics" style="color:white; text-decoration:underline; font-size:1.5em; font-family: 'Open Sans', sans-serif" target="_blank">Demographics</a>
                    <% } else { %>
                    	<a href="reports/demographics_pi_sheet.php?case_id=<%=case_id %>" title="Click to view print-ready Demographics" style="color:white; text-decoration:underline; font-size:1.5em; font-family: 'Open Sans', sans-serif" target="_blank">Demographics</a>
                    <% } %>
                </h3>
                <div class="demographic_highlight">
                
                <% if (!blnWCAB && !blnImm && !blnPatient && !blnSSN) { %>Plaintiff: <% } %>
                <% if (!blnWCAB && blnImm && !blnPatient) { %>Applicant: <% } %>
                <% if (blnPatient) { %>Patient: <% } %>
                <% if (blnSSN) { %>Claimant: <% } %>
                <% if (blnWCAB) { %>
                <div style='float:right'><a id="eams_search_link" title="Click to search Applicant in EAMS" class="parent_<%=partie.parent_uuid %>_<%=partie.type %>" style="color:black;cursor:pointer" target="_blank" href="#eams_case_search/<%=current_case_id %>">EAMS Search</a></div>
                <% } %>
                <%= applicant_name.capitalizeAllWords() %>
                </div>
                <% if (dashboard_dob!="") { %>
                <div class="demographic_highlight">DOB: <%= dashboard_dob %>&nbsp;&nbsp;<%= dashboard_age %></div>
                <% } %>
                <% if (applicant_ssn!="") { %>
                <div class="demographic_highlight">SSN: <%= applicant_ssn %></div>
                <% } %>
                <% 
                
                if (applicant_language!="") { %>
                <div>Language: <%= applicant_language %></div>
                <% } %>
                <div class="defendant_demographic_info">&nbsp;</div>
                <div class="demographic_highlight defendant_demographic_info" id="defendant_name_demo">Defendant: </div>
                <div class="demographic_highlight defendant_demographic_info" id="defendant_dob_demo">DOB:</div>
                <div class="demographic_highlight defendant_demographic_info" id="defendant_ssn_demo">SSN:</div>
                <div class="demographic_highlight defendant_demographic_info" id="defendant_language_demo">Language:</div>

                <div>&nbsp;</div>
                <% if (!blnWCAB && kase.get('personal_injury_date')!='') { %>
                <div>DOI:&nbsp;<%=moment(kase.get('personal_injury_date')).format("MM/DD/YYYY") %></div>
                
                 <% if (!blnWCAB && kase.get('personal_statute_limitation')!='0000-00-00') { %>
                <div>SOL: <%= moment(kase.get('personal_statute_limitation')).format("MM/DD/YYYY") %></div>
                <% } %>
                
                <% } else { %>
                <div>DOI: <%= start_date %><%= end_date %></div>
                <div>SOL: <%= statute_limitation %></div>
                <% } %>
                
                <div class="demographic_highlight">Claim #: <%= claim_number %></div>
                <% if (partie.type=="applicant") { %>
                    <div style="margin-bottom:0px; position: absolute; bottom: 5px; width: 90%">
                    <div style="float:right">
                    <a id="add_case" class="white_text" title="Click to add Related Case to this Kase">add related case</a>
                    </div>
                    <span style="margin-top:0px" class="partie_type_title" id="related_cases" title="Click to see related Cases">
                    Related Cases</span>
                </div>
                <% } %>
                <% if (!blnWCAB && kase.get('personal_injury_date')!='') { %>
                	<div style="padding-top:5px" class="demographic_highlight">
                        <div style="float:right">Time:&nbsp;<%=moment(kase.get('personal_injury_date')).format("h:mma") %></div>
                        <div>DOI:&nbsp;<%=moment(kase.get('personal_injury_date')).format("MM/DD/YYYY") %></div>
                    </div>
                <% } %>
                <% if (sub_in_date!="") { %>
                <div style="padding-top:5px">
                	Sub In:&nbsp;<%=moment(sub_in_date).format("MM/DD/YYYY") %>
                </div>
                <% } %>
                <% if (sub_out_date!="") { %>
                <div>
                	Sub Out:&nbsp;<%=moment(sub_out_date).format("MM/DD/YYYY") %>
                </div>
                <% } %>
			</li>
        <%
        	column_counter++;        	
            }
        }
        var the_company_name = partie.company_name;
        if (the_company_name == "" && partie.full_name!="") {
        	the_company_name = '<a href="' + thehref + '" title="Click to Review ' + partie.type.capitalize() + '" class="white_text"  style="font-size:1.1em">' + partie.full_name + '</a>';
        }
        if (the_company_name == "") {
        	the_company_name = "Please click here to enter Partie Information";
        }
        
        var the_employee_title = "";
        if (partie.employee_title!="" && partie.employee_title!=null) {
        	the_employee_title = partie.employee_title + ":";
        }
        if (partie.color == null) {
	        partie.color = "_card_fade";
        }
        if (typeof partie.claim == "undefined") {
        	partie.claim = "";
        }
        %>
            <% if (blnPiReady) { %>
                <% if (!blnWCAB) { %>
                    <% 
                    if (partie.party_type_option=="xplaintiff") {
	                    partie.party_type_option = "plaintiff";
                    }
                    if(last_party_type_option != partie.party_type_option) {
                    	 %>
                    	</ul>
                        </div>
                        <hr />
  <div class="gridster" id="gridster_parties_cards2">
                        <ul>
                        <% row_counter = 1; 
                        column_counter = 1; 
                        //if no quick notes?
                        if (!blnQuickNotes && panel_title== "Dashboard") {
                            blnQuickNotes = true;
                            %>
                            <li id="notesGrid" data-row="<%=row_counter %>" data-col="<%=column_counter %>" data-sizex="2" data-sizey="1" class="partie gridster_border gridster_holder" style="background:url(img/glass_card_dark_7b.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-left:0px; cursor:default;">
                                <div style="float:right">
                                   <a title="Click to compose a new note" class="compose_new_note" id="compose_quick" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>
                                   
                                   <!--<button id="compose_quick" class="compose_new_note btn btn-sm btn-primary" title="Click to create a new Quick Note">New Quick Note</button>-->
                                </div>
                                <h3 style="margin-top:0px; cursor:pointer" class="partie_type_title" id="quick_notes" title="Click to list all Quick Notes">
                                    Quick Notes
                                </h3>
                                <div id="noteSpan" class="span_class" style="margin-top:5px; overflow-y:auto; height:170px; width:95%; margin-left:auto; margin-right:auto; font-size:1.2em"><%=quick_note %></div>
                            </li>
                            <%	
                            column_counter = column_counter + 2;	
                        }
                        %>

                    <% } %>
                <% } %>
            <% } 
            last_party_type_option = partie.party_type_option; %>
        	<li id="partie_nameGrid_<%= partie.corporation_id %>" data-row="<%=row_counter %>" data-col="<%=column_counter %>" data-sizex="1" data-sizey="1" class="partie gridster_border gridster_holder" style="background:url(img/glass<%=partie.color %>.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-left:0px">
            <!--
            <% if (login_user_id=="1891") { %>
            <%=row_counter %> - <%=column_counter %>
            <% } %>
            -->
            <div style="float:right">
            <% if ((blnWCAB && (partie.type =="applicant" || (partie.type =="employer" && employer_count < 1))) || (!blnWCAB && ((partie.type =="plaintiff" && plaintiff_count < 1) || (partie.type =="defendant" && defendant_count < 1)))) {
		            
            		if (partie.type =="employer") {
                    	employer_count++;
                    }
                    if (partie.type =="plaintiff") {
                    	plaintiff_count++;
                    }
                    if (partie.type =="defendant") {
                    	defendant_count++;
                    } %>
            	&nbsp;
            <% } else { %>
            	<?php //per steve at dordulian 3/31/32017
				if ($blnDeletePermission) { ?>
            	<a class="delete_icon" id="confirmdelete_partie_<%= partie.corporation_id %>" title="Click to delete partie from this kase"><i class="glyphicon glyphicon-trash" style="color:#FF0000; font-size:1.5em"></i></a>
                <?php } ?>
            <% } %>
            </div>
            <% if (partie.type=="carrier"){ %>
            	<div style="margin-top:-4px; padding-left:2px; padding-right:2px; float:right; font-size:1.5em"><a href="mpn.html" target="_blank" class="white_icon">MPN</a></div>
            <% } %>
            <% if (partie.type=="medical_provider"){ %>
                <% if (partie.doctor_type!="") { %>
                  <div style="margin-top:-4px; padding-left:2px; padding-right:2px; float:right; font-size:1.5em"><%=partie.doctor_type %></div>
                <% } %>
                <% if (assigned_to!="") { %>
                  <div style="margin-top:-5px; padding-left:2px; padding-right:2px; float:right"><%=partie.assigned_to %></div>
                <% } %>
          	<% } %>
          <% if (partie_type=="Applicant") { %>
          <div style="float:right; display:none" id="applicant_picture"></div>
          <% }
          var partie_size = "";
          if (partie_type=="Carrier") {
          	partie_size = ' style="font-size:0.9em"';
          }
          %>
          <h3 style="margin-top:0px">
          	<a href="<%= thehref %>" title="Click to Review <%= partie.type.capitalizeWords() %> Information" class="partie_type_title" <%=partie_size %>>
            <% if (partie_type=="Applicant") { %>
                <%if (blnPatient) { %>
                    Patient<% } else { %>
                    <%=partie_type.capitalizeWords() %>
                <% } %>
            <% } else { %>
            	<%=partie_type.capitalizeWords() %>
            <% } %>
            </a>
            <% if (partie.type =="employer" || partie.type =="carrier") { %>
            <div style="display:inline; color:white" id="primary_secondary_<%= partie.corporation_id %>" title="(P)rimary or (S)econdary">
                &nbsp;(P)
            </div>
            <% } %>
            <% if (partie.type=="medical_provider") {
                if (partie.medical_prior=="prior") { %>
                    <div style="display:inline; color:white" id="primary_secondary_<%= partie.corporation_id %>">
                        &nbsp;(Prior)
                    </div>
                <% }
            }
            %>
          </h3>
            	
                <div id="confirm_delete_<%= partie.corporation_id %>" style="display:none; background:black; font-size:1.2em; padding:10px">
                	Are you sure you want to delete?
                    <div style="padding:5px; text-align:center"><a id="delete_partie_<%= partie.corporation_id %>" class="delete_yes white_icon">YES</a></div>
                    <div style="padding:5px; text-align:center"><a id="cancel_partie_<%= partie.corporation_id %>" class="delete_no white_icon">NO</a></div>
                </div>
            	<div id="data_list_<%= partie.corporation_id %>">
                 <% if (partie.type=="employer" || partie.type=="carrier" || partie.type=="medical_provider" || partie.type=="defense" || partie.type=="client"){ %>
                 <div style="float:right">
                 	<span>
                    	<button id="list_kases_link_<%= partie.corporation_id %>_<%=partie.type %>" title="Click to list kases associated with this company" class="list_kases_link parent_<%=partie.parent_uuid %>_<%=partie.type %> btn btn-xs">Kases</button>
                    </span>
                 </div>
                 <% }  %>
                 <% if (partie.type=="applicant"){ %>
                 <div style="float:right">
                 	<span>
                    	<a id="eams_search_link" title="Click to search Applicant in EAMS" class="parent_<%=partie.parent_uuid %>_<%=partie.type %>" style="color:white;cursor:pointer" target="_blank" href="#eams_case_search/<%=current_case_id %>">EAMS Search</a>
                    </span>
                 </div>
                 <% }  %>
              <div style="font-weight:bold; text-transform:capitalize;">
              	<span id="partie_name_<%= partie.corporation_id %>" class="partie_info_<%= partie.corporation_id %>" style="font-size:1.3em"><%= the_company_name.replace("&", " & ").replace(",", " , ") %></span>
                <% if (partie.type=="applicant" && applicant_language!="") { %>
                &nbsp;(<%= applicant_language %>)
                <% } %>
                <% if (typeof partie.letter_name!="undefined") {
                	if (partie.letter_name!="") { %>
                <br />Send Mail To: <%= partie.letter_name %>
                <% }
                } %>
              </div>
              <% if (partie.full_address!="" && partie.full_address!=null) { %>
              	<% if (partie.street!=""){
                		if (partie.suite!="") {
                        	partie.street += ", " + partie.suite;
                        }
                %>
              	<div style="padding-bottom:10px">
                	<div style="position: absolute;left: 230px;top: 65px; z-index:99;">
                        <a class="copy_info btn btn-sm" id="copy_info_<%= partie.corporation_id %>" style="color:white" title="Click to copy information to clipboard so you can paste it into other document (Word, XL, ...)">Copy</a>
                    </div>
                	<span class="partie_info_<%= partie.corporation_id %>"><%= partie.street %><br /><%= partie.city %>, <%= partie.state %> <%= partie.zip %></span>
                    <%
                    var carr = String.fromCharCode(11);
                    var copy_address = removeHtml(the_company_name) + carr + partie.street + carr + partie.city + ", " + partie.state + " " + partie.zip;
                    %>
                    <textarea id="copy_partie_<%= partie.corporation_id %>" style="display:none"><%= copy_address %></textarea>
                    <% 
                    if (blnWCAB) {
	                    if (partie.type!="applicant"){ 
                    %>
                	&nbsp;<a id="map_partie_link_<%=partie.corporation_id %>" title="Click to map to this company" class="map_partie" style="color:white;cursor:pointer;"><i class="glyphicon glyphicon-map-marker" style="color:#FCF"></i></a>
                  	<% }
                    }
                    %>
                </div>
                <% } else { %>
                <div style="padding-bottom:10px"><%= partie.full_address.replace(",,", ",") %></div>
                <% } %>
              <% } %>
              <% if (partie.additional_addresses!="") { %>
              <div style="padding-bottom:10px">
              <%=partie.additional_addresses %>&nbsp;<a title="Click to generate an envelope" class="compose_new_envelope" id="htmlenvelope_<%= partie.type %>_<%= partie.corporation_id %>_y" style="cursor:pointer">
              		<span style="position:absolute; margin-top:-8px; margin-left:5px; z-index:9999; color:blue">W</span>
                	<i class="glyphicon glyphicon-envelope" style="color:yellow">&nbsp;</i>
                </a><span id="feedback_html_<%= partie.type %>_<%= partie.corporation_id %>_y"></span>&nbsp;
                <a title="Click to generate a PDF envelope" class="compose_pdf_envelope" id="pdfenvelope_<%= partie.type %>_<%= partie.corporation_id %>_y" style="cursor:pointer">
                	<span style="position:absolute; margin-top:-8px; margin-left:5px; z-index:9999; color:white">PDF</span>
                	<i class="glyphicon glyphicon-envelope" style="color:yellow">&nbsp;</i>
                </a><span id="feedback_pdf_<%= partie.type %>_<%= partie.corporation_id %>_y"></span>
              </div>
              <% } %>
              <% if (partie.type=="applicant" && partie.dob!="") { %>
              	<%
                var applicant_dob = partie.dob;
                var applicant_age = "";
                if (applicant_dob == "Invalid date" || applicant_dob == "0000-00-00") {
                    applicant_dob = "";
                }
                if (applicant_dob!="") {
                    applicant_dob = moment(applicant_dob).format('MM/DD/YYYY');
                    applicant_age = " (" + applicant_dob.getAge() + " years old)";
                }
                %>
                <div style="padding-bottom:0px">DOB: <%= applicant_dob %><%= applicant_age %></div>
              <% } %>
              <%
                var site = partie.company_site;
                site = site.replace("http://", "");
                site = "<a href='http://" + site + "' target='_blank' title='Click to visit site' class='white_text'>" + partie.company_site + "</a>";
                %>
              <% if (partie.type=="applicant" && partie.ssn!="" && partie.ssn!="XXXXXXXXX") { %>
              	<div style="padding-bottom:0px">SSN: <%= applicant_ssn %></div>
              <% } %>
              <% if (partie.type=="applicant" && partie.language!="") { %>
              	<div style="padding-bottom:0px">Language: <%= partie.language %></div>
              <% } %>
              <% if (partie.phone!="" && partie.phone!=null) { %><div style="padding-bottom:0px">Phone: <%= partie.phone %></div><% } %>
              <% 
              if (partie.cell_phone=="" && partie.employee_cell!="") { 
              	partie.cell_phone = partie.employee_cell;
              }
              if (partie.cell_phone!="" && partie.cell_phone!=null) { %><div style="padding-bottom:0px">Cell: <%= partie.cell_phone %></div><% } %>
              <% if (partie.fax!="" && partie.fax!=null) { %><div style="padding-bottom:0px;">Fax: <%= partie.fax %></div><% } %>
              <% if (partie.email!="" && partie.email!=null) { %><div style="padding-bottom:0px; ">Email: <%= partie.email %>&nbsp;<a title="Click to Email this Partie" class="compose_message compose_<%=partie.type %>" id="partie_<%= partie.partie_id %>" value="<%= partie.email %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF"></i></a></div><% } %>
              <% if (partie.company_site!="" && partie.company_site!=null) { %><div style="padding-bottom:0px; ">Site: <%=site %></div><% } %>
              <% if (partie.type!="applicant") { %>
                  <div>
                  <% if ((partie.type!=null && partie.full_name!=null && partie.full_name.trim()!="") || partie.employee_phone!="" || partie.employee_fax!="" || partie.dob!="" || partie.ssn!="") { %>
                  <div style="margin-top:-15px;"><hr/></div>
                      <% if (partie.type!=null && partie.full_name!=null && partie.full_name.trim()!="") { %>
                      	<div style="margin-top:-20px; text-transform:capitalize;">
                        	<% if (partie.type=="carrier"){ %>
                            <div style="float:right">
                                <span>
                                    <a id="list_kases_link" title="Click to list kases associated with this examiner" class="parent_<%=partie.parent_uuid %>_<%=partie.type %>" style="color:white;cursor:pointer" target="_blank" href="#kasesexaminerlist/<%=current_case_id %>/<%=partie.corporation_id %>/<%= partie.full_name.trim() %>">Kases</a>
                                </span>
                            </div>
                            <% }  %>
                        	<%=the_employee_title %> <%= partie.full_name.trim() %>
                        </div>
                      <% } %>
                  <% } %>
                  <% if (!blnWCAB && partie.type=="plaintiff") { %>
                        <% if (partie.dob!="") { %>
                        <div>DOB: <%= partie.dob %></div>
                        <% } %>
                        <% if (partie.ssn!="") { %>
                        <div class="demographic_highlight">SSN: <%= partie.ssn %></div>
                        <% } %>
                  <% } %>
                  <% if (partie.employee_phone!="" || partie.employee_fax!="") { %>
                  <div>
                      <% if (partie.employee_phone!="") { %>Phone: <%= partie.employee_phone %>&nbsp;&nbsp;<% } %>
                      <% if (partie.employee_fax!="") { %>Fax: <%= partie.employee_fax %><% } %>
                  </div>
                  <% } %>
                  <% if (!blnWCAB && partie.type=="plaintiff") { %>
                  <% if (partie.employee_email!="") { %><div>Email: <%= partie.employee_email %></div><% } %>
                  <% } %>
                  </div>
              <% } %>
              <div style="margin-top:-15px;"><hr/></div>
              <% if (partie.type=="referredout_attorney" && partie.claim_number!="") { %>
            	<div style="margin-top:-20px;">Claim: <%=partie.claim %></div>
            <% } %>
              <% if (partie.type=="medical_provider") {
              		if (partie.specialty!="" || partie.rating!="") { %>
              		<div style="margin-top:-20px;">
              		<% if (partie.specialty!="") { %>
		              <div>Specialty: <%=partie.specialty %></div>
              		<% } %>
                    <% if (partie.rating!="") { %>
		              <div>Rating: <%=partie.rating %></div>
              		<% } %>
                    </div>
              <% }
              } %>
              <div>
              <% if (partie.type=="carrier" || partie.type=="defense"){
              		var partie_start_date = "";
                    var partie_end_date = "";
					if (partie.color != "_card_missing") {
                         if (partie.start_date == "" || partie.start_date == "0000-00-00") {
                            partie_start_date = "";
                        } else {
                            partie_start_date = moment(partie.start_date).format('MM/DD/YYYY');
                        }
                
                        if (partie.end_date == "" || partie.end_date == "0000-00-00") {
                            partie_end_date = "";
                        } else {
                            partie_end_date = moment(partie.end_date).format('MM/DD/YYYY');
                            partie_end_date = " - " + partie_end_date + " CT";
                        }
                    }
                    partie_start_date += partie_end_date;
                    
              	 %>
                 
                 
              		<% if (partie.type=="carrier") {
                    	if (typeof partie.claim_number!="undefined") {
                            if ( partie.claim_number!="") { %>
                                <div style="margin-top:-20px;">Claim #: <%= partie.claim_number %></div>
                        <% 	}
                    	}
                      } %>
                    <% if (partie_start_date!="") { %>
                    <div>DOI: <%= partie_start_date %></div>
                    <% } %>
                    <% if (partie.type=="carrier") {
                    	if (typeof partie.mpn == "undefined") {
                        	partie.mpn = "";
                        }
                    	if ( partie.mpn!="") { %>
                    		<div>MPN: <%= partie.mpn %></div>
                    <% 	}
                      } %>
              <% } %>
              </div>
              <div style="margin-bottom:0px; position: absolute; bottom: 5px; right: 0; <% if (partie.type =="employer" || partie.type =="applicant" || (partie.type =="medical_provider" && partie.medical_prior=="prior") || partie.type =="carrier") { %>width:100%<% } %>">
              <% 
              var pdf_left = 65;
              if (partie.type =="employer") { 
              	pdf_left = 247;
                %>
              <div style="float:left; margin-left:5px">
                	<a href="https://www.caworkcompcoverage.com/" target="blank" class="white_text" title="Click to Search WCIRB">WCIRB</a>&nbsp;
              </div>
              <% } %>
              <% if (partie.type=="medical_provider") {
                if (partie.medical_prior=="prior") {
                	pdf_left = 227; %>
                    <div id="add_on_holder_<%= partie.corporation_id %>" class="add_on_location" style="float:left; display:none; margin-left:5px"></div>
                <% }
            }
            %>
              <% 
              if (partie.type =="carrier") {
              	pdf_left = 247; 
              }
              if (partie.type =="applicant") {
              	pdf_left = 247; 
                %>
              <div style="float:left; margin-left:5px">
                	<a id="review_rx" class="white_text" title="Click to review Prescriptions" style="cursor:pointer">Rx</a>&nbsp;
              </div>
              <% } %>
              <div style="float:right; margin-bottom:0px; margin-right:10px">
              	<%
                var note_partie_id = partie.corporation_id;
                if (partie.type=="applicant") {
                	note_partie_id = partie.person_id;
                }
                %>
                <% if (blnUseRightPane) { %>
                	<a title="Click to compose a new note" class="compose_new_note" id="compose_<%= partie.type %>_<%= note_partie_id %>" style="cursor:pointer">
                	<i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i>
                </a>
                <% } else { %>
              	<a title="Click to compose a new note" class="compose_new_note" id="compose_<%= partie.type %>_<%= note_partie_id %>" style="cursor:pointer" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false">
                	<i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i>
                </a>
                <% } %>
            &nbsp;
            
            <% if (partie.type!="applicant") { %>
            	<a title="Click to generate an envelope" class="compose_new_envelope" id="htmlenvelope_<%= partie.type %>_<%= partie.corporation_id %>" style="cursor:pointer">
                	<span style="position:absolute; margin-top:-8px; margin-left:5px; z-index:9999; color:aquamarine">W</span>
                	<i class="glyphicon glyphicon-envelope" style="color:yellow">&nbsp;</i>
                </a><span id="feedback_html_<%= partie.type %>_<%= partie.corporation_id %>"></span>&nbsp;
                <a title="Click to generate a PDF envelope" class="compose_pdf_envelope" id="pdfenvelope_<%= partie.type %>_<%= partie.corporation_id %>" style="cursor:pointer">
                	<div style="position:absolute; z-index:9999; left:<%=pdf_left %>px; top:-6px; font-weight:bold; color:white" id="pdf_<%= partie.corporation_id %>">PDF</div>
                	<i class="glyphicon glyphicon-envelope" style="color:yellow">&nbsp;</i>
                </a><span id="feedback_pdf_<%= partie.type %>_<%= partie.corporation_id %>"></span>
            <% } else {  %>
            	<a title="Click to generate an envelope" class="compose_new_envelope" id="htmlenvelope_<%= partie.type %>_<%= partie.person_id %>" style="cursor:pointer">
                	<span style="position:absolute; margin-top:-8px; margin-left:5px; z-index:9999; color:aquamarine">W</span>
                	<i class="glyphicon glyphicon-envelope" style="color:yellow">&nbsp;</i>
                </a><span id="feedback_html_<%= partie.type %>_<%= partie.person_id %>"></span>&nbsp;
                <a title="Click to generate a PDF envelope" class="compose_pdf_envelope" id="pdfenvelope_<%= partie.type %>_<%= partie.person_id %>" style="cursor:pointer">
                	<div style="position:absolute; z-index:9999; left:<% if (partie.type!="applicant") { %>65<% } else { %><%=pdf_left %><% } %>px; top:-6px; font-weight:bold; color:white" id="pdf_<%= partie.corporation_id %>">PDF</div>
                	<i class="glyphicon glyphicon-envelope" style="color:yellow">&nbsp;</i>
                </a><span id="feedback_pdf_<%= partie.type %>_<%= partie.person_id %>"></span>
            <% } %>
			<% if (partie.type=="medical_provider"){ %>
              	<a title="Click to add a Medical Index" class="compose_new_exam" id="composeExam_<%= partie.corporation_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer;"><img src="../images/status_256.png" height="20px" width="20px" /></a>
            <% } %>
            </div>
            </div>
			
            <% if (new_filler != "") {
            	if (new_filler == "xplaintiff") { 
                	new_filler = "plaintiff"; 
                } 
                if (new_filler=="plaintiff" && blnImm) {
                	new_filler = "applicant"; 
                }
                var new_filler_width = 80;
                if (new_filler == 'plaintiff') {
                	new_filler_width = 65;
                }
                if (new_filler == 'applicant') {
                	new_filler_width = 75;
                }
                if (new_filler.length > 10) {
	                new_filler_width = 8 * new_filler.length;
                }
            %>
            <div style="background:white; border:1px solid red; color:red; width:<%=new_filler_width %>px; margin-bottom:0px; position:absolute; bottom:5px; float:left; padding-left:3px; font-size:1.2em" id="new_filler"><%=new_filler.toUpperCase() %>
            </div>
            
            <% } %>
            </li>
            <% if (partie_type=="Applicant") {
            	//column_counter++; %>
            <li id="applicant_pictureGrid" data-row="" data-col="" data-sizex="1" data-sizey="1" class="partie gridster_border gridster_holder" style="background:url(img/glass_card_fade_2.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-left:0px; display:none">
            <div style="display:none;text-align:center; padding-top:12px" id="applicant_picture"></div>
            </li>
            <% } %>
            
        <%	
        	if ((column_counter % column_max) == 0) {
                //new row
                row_counter++;
                column_counter = 1;
            } else {
            	column_counter++;
            }    
            if (panel_title== "Dashboard") {
            	if (parties_count == 1) {
                    //new row
                    row_counter++;
                    column_counter = 1;        
                }
                if (parties_count > 1 && parties_count < column_max && column_counter > parties_count) {
	                row_counter++;
                    column_counter = 1;        
                }
                if ((row_counter==2 && !blnQuickNotes)) {
                	blnQuickNotes = true;
                    %>
                    <li id="notesGrid" data-row="<%=row_counter %>" data-col="<%=column_counter %>" data-sizex="2" data-sizey="1" class="partie gridster_border gridster_holder" style="background:url(img/glass_card_dark_7b.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-left:0px; cursor:default;">
                        <div style="float:right">
                           <a title="Click to compose a new note" class="compose_new_note" id="compose_quick" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>
                           
                           <!--<button id="compose_quick" class="compose_new_note btn btn-sm btn-primary" title="Click to create a new Quick Note">New Quick Note</button>-->
                        </div>
                        <h3 style="margin-top:0px; cursor:pointer" class="partie_type_title" id="quick_notes" title="Click to list all Quick Notes">
                            Quick Notes
                        </h3>
                        <div id="noteSpan" class="span_class" style="margin-top:5px; overflow-y:auto; height:170px; width:95%; margin-left:auto; margin-right:auto; font-size:1.2em"><%=quick_note %></div>
                    </li>
                    <%	
                    column_counter = column_counter + 2;	
                }
            }
 %>
        <% 
        }); %>
        	
			<% if (this.collection.panel_title=="Dashboard") { %>
            	<!--<li id="kase_events_card_holder" data-row="<%=(row_counter+1) %>" data-col="1" data-sizex="4" data-sizey="3" class="partie gridster_border gridster_holder" style="display:none;background:url(img/glass_card_dark_wide.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-left:0px;">
                    <h3 style="margin-top:0px"><a href="#kalendarbydate/<%=case_id %>" title="Click to view print-ready Events" style="color:white; text-decoration:underline; font-size:1.5em; font-family: 'Open Sans', sans-serif" target="_blank">Events</a></h3>
                    <div id="kase_events_card" style="margin-top:10px; border:0px solid yellow; overflow-y:auto; height:620px; ">
    
                	</div>
                </li>
                <li id="kase_exam_card_holder" data-row="<%=(row_counter+2) %>" data-col="1" data-sizex="4" data-sizey="3" class="partie gridster_border gridster_holder" style="display:none;background:url(img/glass_card_dark_wide.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-left:0px">
                    <h3 style="margin-top:0px"><a href="#exams/<%=case_id %>" title="Click to view Medical Index" style="color:white; text-decoration:underline; font-size:1.5em; font-family: 'Open Sans', sans-serif" target="_blank">Medical Index</a></h3>
                    <div id="kase_exam_card" style="margin-top:10px; border:0px solid yellow; overflow-y:auto; height:320px; ">
    
                	</div>
                </li>
                -->
            <% } %>
		</ul>
        
	</div>
    <div id="cards_all_done"></div>
	<script language="javascript">
    $( "#cards_all_done" ).trigger( "click" );
    </script>
    <hr />
    <% if (panel_title== "Dashboard") { %>
            <div class="container" style="width:100%; border:0px solid green; margin:35px; padding:0px; margin-left:0px;  margin-top:10px">
                <div class="dashboard_home col-md-6" id="upcoming_events" style="margin-top:0px; overflow-y:auto; width:50%; border:0px solid yellow; height:400px">
                </div>
                <div class="dashboard_home col-md-6" id="my_tasks" style="margin-top:0px; overflow-y:auto; width:50%; margin-left:auto; margin-right:auto; border: 0px solid green; float: right; height:400px">
                </div>
        	</div>
            <hr />
            <div class="container" style="width:100%; border:0px solid green; margin:0px; padding:0px;">
                <div class="dashboard_home" id="kase_notes" style="margin-top:0px; overflow-y:auto; height:1050px; width:99%; border:0px solid yellow">
                </div>
        	</div>
            <% } %>
</div>
