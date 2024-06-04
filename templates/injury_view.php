<?php 
require_once('../shared/legacy_session.php');
session_write_close();

include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$result = DB::runOrDie('SELECT * FROM `ikase`.`cse_venue` where deleted!=1 ORDER BY venue');
$venue_options = "<option value=''>Select from List</option>";
while ($row = $result->fetch()) {
    $venue_options .= "" ."<option value='{$row->venue_uuid}'>{$row->venue_abbr}</option>";
}
?>
<% 
var ct_top = -7;
var margin_top = -26;
if (blnIE) {
	margin_top = -42;
    ct_top = -22;
}
%>
<% 
var venue_options = "<?php echo $venue_options; ?>"; 
%>
<div class="gridster injury_view injury" id="gridster_injury" style="display:">
     <div style="background:url(img/glass_<%=glass%>.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="injury_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="injury" />
        <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
        <input id="injury_uuid" name="injury_uuid" type="hidden" value="<%= uuid %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "injury"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
            <% if (start_date=="0000-00-00" && ct_dates_note!="") { %>
            <li data-row="1" data-col="1" data-sizex="2" data-sizey="2" class="injury gridster_border" style="background:url(img/glass_card_missing.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <div>
                    <span>This is a legacy case, and the CT dates may not have been entered properly.  Please update the information now:</span>
                    <h6><div class="" style="margin-top:10px; color: black">CT Dates: <span class="ct_dates" style="width:50px: margin-left:90px; color:white; font-family:arial; size:1em"><%= ct_dates_note %></span></div></h6>
                    
                </div>
            </li>
            <% } %>
            <li id="adj_numberGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="injury gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;" id="adj_number_label">ADJ Number</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="adj_numberSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="adj_numberSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= adj_number %>" name="adj_numberInput" id="adj_numberInput" class="kase injury_view input_class hidden" placeholder="Workers Comp Case Number" style="margin-top:-26px; margin-left:90px; width:119px" parsley-error-message="" tabindex="1" autocomplete="off" />
              <span id="adj_numberSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px">
              	<%= adj_number %>
                <% if (adj_number=="") { %>
                	<a href="#eams_injury_search/<%= case_id %>/<%= id %>" class="list-item_kase kase_link white_text" style="padding:2px; border:0px solid #CCC; text-decoration:underline" target="_blank">
                    eams search
                    </a>
                <% } %>
              </span>
            </li>
            <li id="injury_statusGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="injury gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
	            <h6><div class="form_label_vert" style="margin-top:10px;">Status</div></h6>
                <select id="injury_statusInput" name="injury_statusInput" class="kase injury_view input_class hidden" style="height:25px; width:150px; margin-top:-30px; margin-left:60px">
                	<option value="" <% if (injury_status=="") { %>selected<% } %>>Select from List</option>
                	<option value="Denied" <% if (injury_status=="Denied") { %>selected<% } %>>Denied</option>
                    <option value="Accepted" <% if (injury_status=="Accepted") { %>selected<% } %>>Accepted</option>
                    <option value="Pending" <% if (injury_status=="Pending") { %>selected<% } %>>Pending</option>
                    <option value="Settled" <% if (injury_status=="Settled") { %>selected<% } %>>Settled</option>
                    <option value="Completed" <% if (injury_status=="Completed") { %>selected<% } %>>Completed</option>
                </select>
                <span id="injury_statusSpan" class="kase applicant span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= injury_status %></span>
            </li>
             <li id="venueGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
                <h6>
                	<div class="form_label_vert" style="margin-top:10px;">Injury Venue</div>
                </h6>
                <select name="venueInput" id="venueInput" class="kase input_class hidden" style="width:350px; margin-left:90px; margin-top:-18px" >
                    <% var select_options = venue_options;
                    select_options = select_options.replace("value='" + venue_uuid + "'",  "value='" + venue_uuid + "' selected");
                    %>
                    <%= select_options %>                
                </select>
                <span id="venueSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                <%=venue %>
                </span>
            </li>
            <li id="occupationGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="injury gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Occupation</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="occupationSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="occupationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <%
            var theoccupation = "";
            %>
              <input value="<%=theoccupation %>" name="occupationInput" id="occupationInput" class="kase injury_view input_class hidden" placeholder="Occupation" style="margin-top:-25px; margin-left:90px; width:355px" tabindex="2" />
              <input value="<%=occupation %>" type="hidden" id="occupation_title" />
              <span id="occupationSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-25px; margin-left:90px"><%=occupation %></span>
            </li>
            
            <li id="occupation_groupGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="injury gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">	
            	<div style="float:right">
                	<button class="btn btn-xs btn-primary" id="ratings_link" title="Click to review the SCHEDULE FOR RATING
PERMANENT DISABILITIES 2005 (OCCUPATIONS AND GROUP NUMBERS)">Lookup Numbers</button>
                </div>
              	<h6><div class="form_label_vert" style="margin-top:10px;">Occupation Group</div></h6>
                <input value="<%=occupation_group %>" name="occupation_groupInput" id="occupation_groupInput" class="kase injury_view input_class hidden" placeholder="Group #" style="margin-top:-25px; margin-left:90px; width:105px" tabindex="2" />
                <span id="occupation_groupSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-25px; margin-left:90px"><%=occupation_group %></span>
            </li>
            <%
            if (start_date == "Invalid date" || start_date == "0000-00-00") {
                start_date = "";
            }
            if (start_date!="") {
            	start_date = moment(start_date).format('MM/DD/YYYY');
            }
            %>
            <li id="start_dateGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">DOI</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="start_dateSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="start_dateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="display:inline-block">
             <div style="float:left; border:#0000CC 0px solid;z-index:3258">
              <input value="<%=start_date %>" name="start_dateInput" autocomplete="off" id="start_dateInput" class="kase injury_view input_class hidden datepicker" placeholder="Start Date" style="margin-top:<%=(margin_top-17) %>px; margin-left:90px; width:100px;z-index:3259; width:119px" parsley-error-message="" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);checkStartEnd();" required tabindex="3" />
              <span id="start_dateSpan" class="kase injury_view span_class form_span_vert" style="margin-top:<%=(margin_top-17) %>px; margin-left:90px"><%=start_date %></span>
             </div>
             <% 
             	var show_end = "";
                var checkedCT = "";
                var ctHidden = "hidden";
                if (end_date == "0000-00-00" || end_date == "") {
                	show_end = "none";
                    end_date = "";
                    var input_end_date = "";
                } else {
                	end_date = moment(end_date).format('MM/DD/YYYY');
                    var input_end_date = moment(end_date).format('MM/DD/YYYY');
                    checkedCT = " checked";
                    ctHidden = "";
                }
             %>
             <div style="margin-top:-18px; margin-left:90px; border:#0000CC 0px solid; width:10px; z-index:1"> <input type="checkbox" id="checkCT" name="checkCT" class="kase injury_view input_class hidden no_immigration_info" value="Y" style="margin-top:<%=(ct_top-17) %>px;" parsley-validate tabindex="4" <%=checkedCT %> />
             <div style="border:#00FFFF 0px solid; margin-left:120px; margin-top:<%=(ct_top-17) %>px; z-index:2" class="input_class no_immigration_info <%= ctHidden %>">CT</div></div>
              <br />
             <div style="float:right; margin-top:-3px; margin-left:180px; border:#0000CC 0px solid; z-index:3257">
				<input value="<%= input_end_date %>" name="end_dateInput" autocomplete="off" id="end_dateInput" class="kase injury_view input_class hidden datepicker_1" placeholder="End Date" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);checkStartEnd();" style="margin-top:<%=(margin_top-17) %>px; margin-left:90px; width:119px" tabindex="5" />
              <span id="end_dateSpan" class="kase injury_view span_class form_span_vert" style="margin-top:<%=(margin_top-17) %>px; margin-left:90px; display:<%= show_end%>"><%= end_date %></span>
             </div>
            </div>
            </li>
            <li id="statute_limitationGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">Statute Limitation:</div></h6>
                <!--Add Statute to Calendar-->
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="full_addressSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="statute_limitationLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= statute_limitation %>" name="statute_limitationInput" autocomplete="off" id="statute_limitationInput" class="kase input_class hidden injury" style="margin-top:-26px; margin-left:90px; width:100px;z-index:3259; width:119px"  tabindex="6" />
                <span id="statute_limitationSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
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
            <li id="full_addressGrid" data-row="7" data-col="1" data-sizex="2" data-sizey="2" class="gridster_border no_immigration_info" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6>
                    <div class="form_label_vert" style="margin-top:10px;">
                        Injury Location:<% if (id!="" && full_address=="" && employer_address!="") { %>
                        <span  style="position:absolute; bottom: 0px; margin-top:0px; margin-left:10px" id="add_employer_holder"><a id="add_employer_address" class="white_text" style='cursor:pointer; font-size:1.2em' title='Click to import address from Employer'>Add Employer Address</a></span><% } %>
                    </div>
                </h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="full_addressSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="full_addressSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= full_address %>" name="full_addressInput" autocomplete="off" id="full_addressInput" class="kase input_class hidden injury" style="margin-top:-26px; margin-left:90px; width:353px" placeholder="Enter address for Bing lookup" tabindex="8" />&nbsp;<a class="input_class hidden same_as_employer">clear</a>
                <span id="full_addressSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                <%= full_address %>
                </span>
                <div id="bing_results" style="position: absolute;z-index: 9999;background: aliceblue;border: 1px solid black;padding: 5px;color: black;left: 95px; display:none; margin-top:-20px"></div>
            </li>
            <li id="suiteGrid" data-row="8" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border no_immigration_info" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
                <h6><div class="form_label_vert" style="margin-top:10px;">Suite</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="suiteSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="suiteSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= suite %>" name="suiteInput" autocomplete="off" id="suiteInput" class="kase input_class hidden" style="margin-top:-26px; margin-left:90px" tabindex="9" />
                <span id="suiteSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                <%= suite %>
                </span>
            </li>
            <li id="explanationGrid" data-row="9" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
                <h6>
                	<div style="float:right" class="no_immigration_info">
                    	<span id="explanation_length"><%= explanation.length %></span> characters (325 max for JetFile)
                    </div>
                    <div class="form_label_vert" style="margin-top:10px;" id="injury_explanation_label">The injury occurred as follows</div>
                </h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="explanationSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="explanationSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                
                <textarea name="explanationInput" id="explanationInput" class="kase input_class hidden" style="margin-top:-6px; margin-left:10px; width:430px; height:100px" tabindex="10" onkeyup="limitText(this, 325)"><%= explanation %></textarea>
                <div id="explanationSpan" class="kase injury_view span_class form_span_vert" style="overflow-x: hidden;overflow-y : auto; height:100px; margin-top:-8px; margin-left:10px">
                <%= explanation %>
                </div>
                
          </li>
		</ul>
    </form>
</div>
</div>
<div id="addressGrid" style="display:none">
    <table id="address">
      <tr style="display:none">
        <td class="label">Street address</td>
        <td class="slimField"><input class="field" id="street_number_injury"
              disabled="true"></input></td>
        <td class="wideField" colspan="2"><input class="field" id="route_injury"
              disabled="true"></input></td>
      </tr>
      <tr>
        <td class="wideField" colspan="4">
            <input class="field" id="street_injury"></input>&nbsp;<input class="field" id="city_injury"style="width:100px"></input>&nbsp;<input class="field"
              id="administrative_area_level_1_injury" disabled="true" style="width:30px"></input>&nbsp;<input class="field" id="postal_code_injury"
              disabled="true" style="width:50px"></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">City</td>
        <td class="wideField" colspan="3">
            <input class="field" id="locality_injury"
              disabled="true"></input>
            <input class="field" id="sublocality_injury"
              disabled="true"></input>
              <input class="field" id="neighborhood_injury"
              disabled="true"></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">Country</td>
        <td class="wideField" colspan="3"><input class="field"
              id="country_injury" disabled="true"></input></td>
      </tr>
    </table>
</div>
<div class="injury_view" id="all_done"></div>
<script language="javascript">
$( "#all_done" ).trigger( "click" );
</script>
