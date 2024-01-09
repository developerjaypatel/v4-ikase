<?php 
require_once('../shared/legacy_session.php');
session_write_close();

include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$result = DB::runOrDie("SELECT * FROM `cse_venue` ORDER BY venue");
$venue_options = "<option value=''>Select from List</option>";
while ($row = $result->fetch()) {
    $venue_options .= "<option value='{$row->venue_uuid}'>{$row->venue_abbr}</option>";
}

$result = DB::runOrDie("SELECT * FROM `cse_casestatus` WHERE 1");
$casestatus_options = "<option value=''>Select from List</option>";
while ($row_status = $result->fetch()) {
    $casestatus_options .= "<option value='{$row_status->casestatus}'>{$row_status->casestatus}</option>";
}
///die($casestatus_options);
$result_sub = DB::runOrDie("SELECT * FROM `cse_casesubstatus` WHERE 1");
$option_sub = "<option value=''>Select from List</option>";
$casesubstatus_options = "" . $option_sub;
while ($row_substatus = $result_sub->fetch()) {
    $casesubstatus_options .= "<option value='{$row_substatus->casesubstatus}'>{$row_substatus->casesubstatus}</option>";
}

$blnIPad = isPad();
?>
<% var venue_options = "<? echo $venue_options; ?>"; %>
<% var casestatus_options = "<? echo $casestatus_options; ?>"; %>
<% var casesubstatus_options = "<? echo $casesubstatus_options; ?>"; %>
<% var rating_options = "<option value='A'>A</option><option value='B'>B</option><option value='C'>C</option><option value='D'>D</option><option value='F'>F</option>"; %>
<div class="glass_header kase">
<form id="kase_form" class="kase" parsley-validate>
<input id="id" name="id" type="hidden" value="<%= id %>" />
<input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
<input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
<div class="kase" style="padding-left:5px">
    <span style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;">
	    <span id="panel_title">Edit Kase</span>
    </span>
    <% if (id > 0) { %>
		<?php if (strpos($_SESSION['user_role'], "admin") !== false) { ?>
        <a class="delete" style="border:0px solid; width:20px" title="Click to Delete Kase">
        <i class="glyphicon glyphicon-trash" style="color:#FC221D"></i></a>
        <?php } ?>
    <% } %>
    <div class="gridster row-fluid" id="gridster_flat" style="display:none">
        <ul>
            <li class="glass" title="Double-Click this box to edit" id="case_numberGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;-ms-filter:'alpha(opacity=50)'; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
				<div style="float:right;">
                <% var additional_display = "";
                	if (id < 0) {
                    	additional_display = ""; %>
                    <a class="save" style="border:0px solid; width:20px" title="Click to Save Kase"><i class="glyphicon glyphicon-saved" style="color:#00FF00; width:10px; height:10px"></i></a>
                <% } %>
                </div>
              <h6><div class="form_label_vert">Case #</div></h6>
              <div style="float:right; margin-top:-5px" class="hidden" id="case_numberSave">
              &nbsp;<a class="save_field" title="Click to save" id="case_numberSaveLink"><i class="glyphicon glyphicon-saved" style="color:#00FF00"></i></a>
              </div>
              <input value="<%= case_number %>" name="case_numberInput" id="case_numberInput" class="kase input_class hidden floatlabel" parsley-error-message="Case#" required />
              <span id="case_numberSpan" class="kase span_class form_span_vert"><%= case_number %></span>
        </li>
        <li class="glass" title="Double-Click this box to edit" id="venueGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
              <h6><div class="form_label_vert">Venue</div></h6>
              <div style="float:right; margin-top:-5px" class="hidden" id="venueSave">
              &nbsp;<a class="save_field" venue="Click to save" id="venueSaveLink"><i class="glyphicon glyphicon-saved" style="color:#00FF00"></i></a>
              </div>
              <select name="venueInput" id="venueInput" class="kase input_class hidden" style="width:75px" parsley-error-message="Venue" required >
                  <% var select_options = venue_options;
                  select_options = select_options.replace("value='" + venue_uuid + "'",  "value='" + venue_uuid + "' selected");
                  %>
                  <%= select_options %>
              </select>
              <span class="kase span_class form_span_vert" id="venueSpan"><%= venue_abbr %></span>
        </li>
        <li class="glass" title="Double-Click this box to edit" id="adj_numberGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;-ms-filter:'alpha(opacity=50)'; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
              <h6><div class="form_label_vert">ADJ Number</div></h6>
              <div style="float:right; margin-top:-5px" class="hidden" id="adj_numberSave">
              &nbsp;<a class="save_field" title="Click to save" id="adj_numberSaveLink"><i class="glyphicon glyphicon-saved" style="color:#00FF00"></i></a>
              </div>
              <input value="<%= adj_number %>" name="adj_numberInput" id="adj_numberInput" class="kase input_class hidden floatlabel" required />
              <span id="adj_numberSpan" class="kase span_class form_span_vert"><%= adj_number %></span>
        </li>
        <li class="glass" title="Double-Click this box to edit" id="case_dateGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px;background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert">Case&nbsp;Date</div></h6>
                <div style="float:right; margin-top:-5px;" class="hidden" id="case_dateSave">
              &nbsp;<a class="save_field" title="Click to save" id="case_dateSaveLink"><i class="glyphicon glyphicon-saved" style="color:#00FF00"></i></a>
              </div>
                <input value="<%= moment(case_date).format('MM/DD/YYYY') %>" name="case_dateInput" id="case_dateInput" class="kase input_class hidden" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" required />
                <span id="case_dateSpan" class="kase span_class form_span_vert"><%= moment(case_date).format('MM/DD/YYYY') %></span>
            </li>
            <li class="glass" title="Double-Click this box to edit" id="case_typeGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert">Case&nbsp;Type</div></h6>
                <div style="float:right; margin-top:-5px;" class="hidden" id="case_typeSave">
              &nbsp;<a class="save_field" title="Click to save" id="case_typeSaveLink"><i class="glyphicon glyphicon-saved" style="color:#00FF00"></i></a>
              </div>
                <input value="<%= case_type %>" name="case_typeInput" id="case_typeInput" class="kase input_class hidden" required />
                <span id="case_typeSpan" class="kase span_class form_span_vert"><%= case_type %></span>
            </li>

            <li id="submittedonGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert">Submitted On</div></h6>
                <input value="<%= moment(submittedOn).format('MM/DD/YY') %>" name="submittedonInput" id="submittedonInput" class="hidden" />
                <span id="submittedonSpan" class="span_class kase form_span_vert">
                	<%= moment(submittedOn).format('MM/DD/YY') %>                
                </span>
            </li>
           <li class="glass" id="case_statusGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px;background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:<%=additional_display %>">
                <h6><div class="form_label_vert">Case&nbsp;Status</div></h6>
                <div style="float:right; margin-top:-5px;" class="hidden" id="case_statusSave">
              &nbsp;<a class="save_field" title="Click to save" id="case_statusSaveLink"><i class="glyphicon glyphicon-saved" style="color:#00FF00"></i></a>
              </div>
                <select name="case_statusInput" id="case_statusInput" class="kase input_class hidden" style="width:180px">
                	<% var status_options = casestatus_options;
                  status_options = status_options.replace("value='" + case_status + "'",  "value='" + case_status + "' selected");
                  %>
                  <%= status_options %>
                </select>
                <span id="case_statusSpan" class="kase span_class form_span_vert">
                    <%= case_status %>
                </span>
            </li>
            <li class="glass" id="ratingGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px;background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:<%=additional_display %>">
                <h6><div class="form_label_vert">Case&nbsp;Rating</div></h6>
                <div style="float:right; margin-top:-5px;" class="hidden" id="ratingSave">
              &nbsp;<a class="save_field" title="Click to save" id="ratingSaveLink"><i class="glyphicon glyphicon-saved" style="color:#00FF00"></i></a>
              </div>
                <select name="ratingInput" id="ratingInput" class="kase input_class hidden" style="width:180px">
                	<% var status_options = rating_options;
                  status_options = status_options.replace("value='" + rating + "'",  "value='" + rating + "' selected");
                  %>
                  <%= status_options %>
                </select>
                <span id="ratingSpan" class="kase span_class form_span_vert">
                    <%= rating %>
                </span>
            </li>
            <li class="glass" id="case_substatusGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px;background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:<%=additional_display %>">
                <h6><div class="form_label_vert">Case Sub Status</div></h6>
                <div style="float:right; margin-top:-5px;" class="hidden" id="case_substatusSave">
              &nbsp;<a class="save_field" title="Click to save" id="case_substatusSaveLink"><i class="glyphicon glyphicon-saved" style="color:#00FF00"></i></a>
              </div>
                <select name="case_substatusInput" id="case_substatusInput" class="kase input_class hidden" style="width:360px; overflow-y: scroll;">
                	<% var sub_status_options = casesubstatus_options;
                  sub_status_options = sub_status_options.replace("value='" + case_substatus + "'",  "value='" + case_substatus + "' selected");
                  %>
                  <%= sub_status_options %>
                </select>
                <span id="case_substatusSpan" class="kase span_class form_span_vert">
                    <%= case_substatus %>
                </span>
            </li>
            <li id="attorneyGrid" class="glass" data-row="6" data-col="1" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert">Attorney</div></h6>
                <div style="float:right; margin-top:-5px;" class="hidden" id="attorneySave">
              &nbsp;<a class="save_field" title="Click to save" id="attorneySaveLink"><i class="glyphicon glyphicon-saved" style="color:#00FF00"></i></a>
              </div>
                <input autocomplete="off" value="<%= attorney %>" name="attorneyInput" id="attorneyInput" class="kase input_class hidden" parsley-error-message="Atty" />
                <span id="attorneySpan" class="span_class kase form_span_vert">
                	  <%= attorney %>              
                </span>
            </li>
            <li id="workerGrid" class="glass" data-row="6" data-col="2" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert">Worker</div></h6>
                <div style="float:right; margin-top:-5px;" class="hidden" id="workerSave">
              &nbsp;<a class="save_field" title="Click to save" id="workerSaveLink"><i class="glyphicon glyphicon-saved" style="color:#00FF00"></i></a>
              </div>
                <input value="<%= worker %>" name="workerInput" id="workerInput" class="kase input_class hidden" required />
                <span id="workerSpan" class="span_class kase form_span_vert">
                	   <%= worker %>             
                </span>
            </li>
        </ul>
    </div>
</div>
</form>
</div>
