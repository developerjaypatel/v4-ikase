<?php 
$todays_date  = date("m/d/y");
?>
<div class="gridster event_dialog event_dialog" id="gridster_event_dialog" style="display:none">
     <div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="event_dialog_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="event" />
        <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
        <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
        <!--<input id="event_kind" name="event_kind" type="hidden" value="<%= event_kind %>" />-->
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "event_dialog"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
        		<%  var letter = title.length;
                	var letter_size = "";
                	if (letter > "50") {
                		letter_size = "14px";
                	} 
                %>
             <li id="titleGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Title:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="titleSave">
                <a class="save_field" title="Click to save this field" id="titleSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input value="<%=title %>" name="event_titleInput" id="event_titleInput" class="event_dialog input_class hidden" placeholder="Title" style="margin-top:-26px; margin-left:65px; width:385px" />
          <span id="event_titleSpan" class="event_dialog span_class form_span_vert" style="margin-top:-29px; margin-left:65px; font-size:<%=letter_size%>"><%= title %></span>
            </li>
            <li id="event_fromGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">From:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="event_fromSave">
                <a class="save_field" title="Click to save this field" id="event_fromSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input value="<%=event_from %>" name="event_fromInput" id="event_fromInput" class="event_dialog input_class hidden" placeholder="Call From" style="margin-top:-26px; margin-left:65px" parsley-error-message="" />
          <span id="event_fromSpan" class="event_dialog span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%=event_from %></span>
            </li>
            <% 
            //if we hide the location
            var next_data_row = 4;
            var location_display = "";
            var location_data_row = 3;
            //hide the location
                //var location_display = "none";
                //next_data_row = 3;
                //location_data_row = 9;
            if(event_kind!="phone_call") { %>
            	
            <li id="whereGrid" data-row="<%=location_data_row %>" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; display: <%=location_display%>">
            <h6><div class="form_label_vert" style="margin-top:10px;">Location:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="whereSave">
                <a class="save_field" title="Click to save this field" id="whereSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input value="<%= full_address %>" id="full_addressInput" name="full_addressInput" placeholder="Enter event address" type="text" class="kase partie input_class hidden" style="margin-top:-26px; margin-left:65px; width:385px" />
          <span id="whereSpan" class="event_dialog span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%= full_address %></span>
            </li>
            <% } else {
            	next_data_row--; %>
            <% } %>
            <%
            if (id != "") {
                var start_date = event_dateandtime;
                var input_date = event_dateandtime;
                
                if (start_date!="") {
                    start_date = moment(event_dateandtime).format('MM/DD/YY hh:mm a');
                    input_date = moment(event_dateandtime).format('MM/DD/YYYY hh:mm a');
                }
            }
            
            %>
            <li id="start_dateGrid" data-row="<%=next_data_row %>" data-col="1" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">When:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="start_dateSave">
                <a class="save_field" title="Click to save this field" id="start_dateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input value="<%=input_date %>"  name="event_dateandtimeInput" id="event_dateandtimeInput" class="event_dialog input_class hidden" placeholder="Time" style="margin-top:-26px; margin-left:65px" parsley-error-message="" required />
          <span id="event_dateandtimeSpan" class="event_dialog span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%=start_date %></span>
            </li>
            <li id="typeGrid" data-row="<%=next_data_row %>" data-col="2" data-sizex="1" data-sizey="1" class="event_dialog gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Type:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="typeSave">
                <a class="save_field" title="Click to save this field" id="typeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <%
            if (event_kind!="phone_call") {
            %>
          <select name="event_typeInput" id="event_typeInput" class="event_dialog input_class hidden" style="height:25px; width:150px; margin-top:-30px; margin-left:65px">
          <option value="" <% if(event_type=="") {%>selected<%} %>>Select from List</option>
          <option value="appointment" <% if(event_type=="appointment") {%>selected<% } %>>Appointment</option>
          <option value="hearing" <% if(event_type=="hearing") {%>selected<% } %>>Hearing</option>
          <option value="phone_call" <% if(event_kind=="phone_call" || event_type=="phone_call") {%>selected<% } %>>Phone Call</option>
          </select>
          <% } else { %>
          <select name="event_typeInput" id="event_typeInput" class="event_dialog input_class hidden" style="height:25px; width:150px; margin-top:-30px; margin-left:65px">
          <option value="phone_call" <% if(event_kind=="phone_call" || event_type=="phone_call") {%>selected<% } %>>Phone Call</option>
          </select>
          <% } %>
          <span id="event_typeSpan" class="event_dialog span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%= event_type.capitalize() %></span>
            </li>
            <li id="event_descriptionGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="3" class="kai gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Details:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="event_descriptionSave">
                <a class="save_field" title="Click to save this field" id="event_descriptionSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <textarea name="event_descriptionInput" id="event_descriptionInput" type="text" class="event_dialog input_class hidden" style="width:450px" rows="5" tabindex="3"><%= event_description %></textarea>
          <span id="event_descriptionSpan" class="event_dialog span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%= event_description %></span>
            </li>
            <% next_data_row++; %>
            <li id="assigneeGrid" data-row="<%=next_data_row %>" data-col="1" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Assignee:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="assigneeSave">
                <a class="save_field" title="Click to save this field" id="assigneeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input autocomplete="off" value="<%=assignee %>" name="assigneeInput" id="assigneeInput" class="event_dialog input_class hidden" placeholder="Assignee" style="margin-top:-26px; margin-left:65px" />
          <span id="assigneeSpan" class="event_dialog span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%=assignee %></span>
            </li>
            <li id="event_nameGrid" data-row="<%=next_data_row %>" data-col="2" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; display:">
            <h6><div class="form_label_vert" style="margin-top:10px;">Entered By:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="event_nameSave">
                <a class="save_field" title="Click to save this field" id="event_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input value="<%= event_name %>" name="event_nameInput" id="event_nameInput" class="event_dialog input_class hidden" placeholder="Who" style="margin-top:-26px; margin-left:65px;" />
          <span id="event_nameSpan" class="event_dialog span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%= event_name %></span>
            </li>
            <li id="event_priorityGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Priority:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="event_prioritySave">
                <a class="save_field" title="Click to save this field" id="event_prioritySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <select name="event_priorityInput" id="event_priorityInput" class="event_dialog input_class hidden" style="height:25px; width:150px; margin-top:-30px; margin-left:65px">
          <option value="" selected>Select from List</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          </select>
          <span id="event_prioritySpan" class="event_dialog span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%= event_priority %></span>
            </li>
            <%  if (end_date == "0000-00-00 00:00:00") { 
            		end_date = "";
            	}
                if (end_date == "Invalid date") { 
            		end_date = "";
            	}
                if (end_date!="") {
                	end_date = moment(end_date).format('MM/DD/YY hh:mm a')
                }
            %>
            <%
            if (event_kind!="phone_call") {
            %>
            <li id="end_dateGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">End Date:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="end_dateSave">
                <a class="save_field" title="Click to save this field" id="end_dateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input value="<%=end_date %>" name="end_dateInput" id="end_dateInput" class="event_dialog input_class hidden" placeholder="12/12/2012" style="margin-top:-26px; margin-left:65px" parsley-error-message="" />
          <span id="end_dateSpan" class="event_dialog span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%= end_date %></span>
            </li>
            <% } %>
           
            <%  if (callback_date == "0000-00-00 00:00:00") { 
            		callback_date = "";
            	}
                if (callback_date == "Invalid date") { 
            		callback_date = "";
            	}
                if (callback_date!="") {
                	callback_date = moment(callback_date).format('MM/DD/YY hh:mm a')
                }
            %>
            <li id="callback_dateGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Callback:</div></h6>
            <div style="" class="event_dialog save_holder hidden" id="callback_dateSave">
                <a class="save_field" title="Click to save this field" id="callback_dateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input value="<%=callback_date %>" name="callback_dateInput" id="callback_dateInput" class="event_dialog input_class hidden" placeholder="12/12/2012" style="margin-top:-26px; margin-left:65px" parsley-error-message="" />
          <span id="callback_dateSpan" class="event_dialog span_class form_span_vert" style="margin-top:-30px; margin-left:65px"><%= callback_date %></span>
            </li>
            <% if (event_kind=="phone_call") { %>
            <li id="whereGrid" data-row="10" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; display: none">
                <input value="office" id="full_addressInput" name="full_addressInput" placeholder="Enter event address" type="text" class="kase partie input_class hidden" style="margin-top:-26px; margin-left:65px; width:385px" />
                </li>
                <% } %>
           </ul>
        </form>
	</div>
</div>
<div id="addressGrid" style="display:none">
    <table id="address">
      <tr style="display:none">
        <td class="label">Street address</td>
        <td class="slimField"><input class="field" id="street_number_event_dialog"
              disabled="true"></input></td>
        <td class="wideField" colspan="2"><input class="field" id="route_event_dialog"
              disabled="true"></input></td>
      </tr>
      <tr>
        <td class="wideField" colspan="4">
            <input class="field" id="street_event_dialog"></input>&nbsp;<input class="field" id="city_event_dialog"style="width:100px"></input>&nbsp;<input class="field"
              id="administrative_area_level_1_event_dialog" disabled="true" style="width:30px"></input>&nbsp;<input class="field" id="postal_code_event_dialog"
              disabled="true" style="width:50px"></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">City</td>
        <td class="wideField" colspan="3">
            <input class="field" id="locality_event_dialog"
              disabled="true"></input>
            <input class="field" id="sublocality_event_dialog"
              disabled="true"></input>
              <input class="field" id="neighborhood_event_dialog"
              disabled="true"></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">Country</td>
        <td class="wideField" colspan="3"><input class="field"
              id="country_event_dialog" disabled="true"></input></td>
      </tr>
    </table>
</div>
<% if (gridster_me || grid_it) { %>
<script language="javascript">
setTimeout(function() {
	gridsterById('gridster_event_dialog');
}, 10);


</script>
<% } %>