<?php 
require_once('../shared/legacy_session.php');
session_write_close();

include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$result = DB::runOrDie("SELECT * FROM `ikase`.`cse_venue` where deleted!=1 ORDER BY venue");
$venue_options = "<option value=''>Select from List</option>";
while ($row = $result->fetch()) {
    $venue_options .= "<option value='{$row->venue_uuid}'>{$row->venue_abbr}</option>";
}

$blnIPad = isPad();
?>
<% var venue_options = "<? echo $venue_options; ?>"; %>
<div class="glass_header kase">
<form id="kase_form" data-parsley-validate>
<input id="id" name="id" type="hidden" value="<%= case_id %>" />
<input id="table_id" name="table_id" type="hidden" value="<%= case_id %>" />
<input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
<div class="kase" style="padding-left:5px">    
    <div class="gridster row-fluid" id="gridster_flat" style="display:none">
        <ul>
            <li class="glass" id="case_numberGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;-ms-filter:'alpha(opacity=50)'; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
				<div style="float:right;">
                <% var additional_display = "none";
                	if (id < 0) {
                    	additional_display = ""; %>
                    <a class="save" style="border:0px solid; width:20px" title="Click to Save Kase"><i class="glyphicon glyphicon-saved" style="color:#00FF00; width:10px; height:10px"></i></a>
                <% } %>
                <% if (id > 0) { %>
					<?php if (strpos($_SESSION['user_role'], "admin") !== false) { ?>
                    <a class="delete" style="border:0px solid; width:20px" title="Click to Delete Kase">
                    <i class="glyphicon glyphicon-trash" style="color:#FC221D"></i></a>
                    <?php } ?>
                <% } %>
                </div>
              <h6><div class="form_label_vert">Case #</div></h6>
              <div style="float:right; margin-top:-5px" class="hidden" id="case_numberSave">
              &nbsp;<a class="save_field" title="Click to save" id="case_numberSaveLink"><i style="font-size:15px;color:#00FF00" class="glyphicon glyphicon-floppy-save"></i></a>
              </div>
              <input value="<%= case_number %>" name="case_numberInput" id="case_numberInput" class="kase input_class hidden floatlabel" required />
              <span id="case_numberSpan" class="kase span_class form_span_vert"><%= case_number %></span>
        </li>
        <li class="glass" id="venueGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
              <h6><div class="form_label_vert">Venue</div></h6>
              <div style="float:right; margin-top:-5px" class="hidden" id="venueSave">
              &nbsp;<a class="save_field" venue="Click to save" id="venueSaveLink"><i class="glyphicon glyphicon-floppy-save" style="color:#00FF00"></i></a>
              </div>
              <select name="venueInput" id="venueInput" class="kase input_class hidden" style="width:75px" parsley-error-message="" required >
                  <% var select_options = venue_options;
                  select_options = select_options.replace("value='" + venue_uuid + "'",  "value='" + venue_uuid + "' selected");
                  %>
                  <%= select_options %>
              </select>
              <span class="kase span_class form_span_vert" id="venueSpan"><%= venue_abbr %></span>
        </li>
        <li class="glass" id="adj_numberGrid" data-row="1" data-col="3" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;-ms-filter:'alpha(opacity=50)'; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
              <h6><div class="form_label_vert">ADJ Number</div></h6>
              <div style="float:right; margin-top:-5px" class="hidden" id="adj_numberSave">
              &nbsp;<a class="save_field" title="Click to save" id="adj_numberSaveLink"><i style="font-size:15px; color:#00FF00" class="glyphicon glyphicon-floppy-save"></i></a>
              </div>
              <input value="<%= adj_number %>" name="adj_numberInput" id="adj_numberInput" class="kase input_class hidden floatlabel" />
              <span id="adj_numberSpan" class="kase span_class form_span_vert"><%= adj_number %></span>
        </li>
        <li class="glass" id="case_dateGrid" data-row="1" data-col="4" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px;background:url(img/glass.png) left top;
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
              &nbsp;<a class="save_field" title="Click to save" id="case_dateSaveLink"><i class="glyphicon glyphicon-floppy-save" style="color:#00FF00"></i></a>
              </div>
                <input value="<%= moment(case_date).format('MM/DD/YYYY') %>" name="case_dateInput" id="case_dateInput" class="kase input_class hidden" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" />
                <span id="case_dateSpan" class="kase span_class form_span_vert"><%= moment(case_date).format('MM/DD/YYYY') %></span>
            </li>
            <?php if ($blnIPad) { ?>

            <li class="glass" id="case_typeGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
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
              &nbsp;<a class="save_field" title="Click to save" id="case_typeSaveLink"><i class="glyphicon glyphicon-floppy-save" style="color:#00FF00"></i></a>
              </div>
                <input value="<%= case_type %>" name="case_typeInput" id="case_typeInput" class="kase input_class hidden" />
                <span id="case_typeSpan" class="kase span_class form_span_vert"><%= case_type %></span>
            </li>
            <% if (id > 0) { %>
            <li id="submittedonGrid" data-row="2" data-col="3" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert">Submitted On</div></h6>
                <input value="<%= submittedOn %>" name="submittedonInput" id="submittedonInput" class="hidden" />
                <span id="submittedonSpan" class="span_class kase form_span_vert">
                	<%= moment(submittedOn).format('MM/DD/YY') %>                
                </span>
            </li>
            <% } %>
            <li id="attorneyGrid" class="glass" data-row="2" data-col="4" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
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
              &nbsp;<a class="save_field" title="Click to save" id="attorneySaveLink"><i class="glyphicon glyphicon-floppy-save" style="color:#00FF00"></i></a>
              </div>
                <input autocomplete="off" value="<%= attorney %>" name="attorneyInput" id="attorneyInput" class="kase input_class hidden" />
                <span id="attorneySpan" class="span_class kase form_span_vert">
                	  <%= attorney %>              
                </span>
            </li>
            <li id="workerGrid" class="glass" data-row="2" data-col="5" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
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
              &nbsp;<a class="save_field" title="Click to save" id="workerSaveLink"><i class="glyphicon glyphicon-floppy-save" style="color:#00FF00"></i></a>
              </div>
                <input value="<%= worker %>" name="workerInput" id="workerInput" class="kase input_class hidden" />
                <span id="workerSpan" class="span_class kase form_span_vert">
                	   <%= worker %>             
                </span>
            </li>
            <?php } else { ?>
            	<li class="glass" id="case_typeGrid" data-row="1" data-col="5" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
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
              &nbsp;<a class="save_field" title="Click to save" id="case_typeSaveLink"><i class="glyphicon glyphicon-floppy-save" style="color:#00FF00"></i></a>
              </div>
                <input value="<%= case_type %>" name="case_typeInput" id="case_typeInput" class="kase input_class hidden" />
                <span id="case_typeSpan" class="kase span_class form_span_vert"><%= case_type %></span>
            </li>
            <% if (id > 0) { %>
            <li id="submittedonGrid" data-row="1" data-col="6" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert">Submitted On</div></h6>
                <input value="<%= submittedOn %>" name="submittedonInput" id="submittedonInput" class="hidden" />
                <span id="submittedonSpan" class="span_class kase form_span_vert">
                	<%= moment(submittedOn).format('MM/DD/YY') %>                
                </span>
            </li>
            <% } %>
            <li class="glass" id="case_statusGrid" data-row="1" data-col="7" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px;background:url(img/glass.png) left top;
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
              &nbsp;<a class="save_field" title="Click to save" id="case_statusSaveLink"><i class="glyphicon glyphicon-floppy-save" style="color:#00FF00"></i></a>
              </div>
                <select name="case_statusInput" id="case_statusInput" class="kase input_class hidden" style="width:75px">
                	<option value="Active" <% if (case_status=="Active") { %>selected<% } %>>Active</option>
                    <option value="Open" <% if (case_status=="Open" || case_status=="open" || case_status=="") { %>selected<% } %>>Open</option>
                    <option value="ReOpened" <% if (case_status=="ReOpened") { %>selected<% } %>>ReOpened</option>
                    <option value="On Hold" <% if (case_status=="On Hold") { %>selected<% } %>>On Hold</option>
                    <option value="Sub Out" <% if (case_status=="Sub Out") { %>selected<% } %>>Sub'd Out</option>
                    <option value="Settled Waiting Fees" <% if (case_status=="Settled Waiting Fees") { %>selected<% } %>>Settled Waiting Fees</option>
                    <option value="Completed" <% if (case_status=="Completed") { %>selected<% } %>>Completed</option>
                    <option value="Closed" <% if (case_status=="Closed") { %>selected<% } %>>Closed</option>
                    <option value="Closed by C & R" <% if (case_status=="Closed by C & R") { %>selected<% } %>>Closed by C & R</option>
                    <option value="Closed by Stipulation" <% if (case_status=="Closed by Stipulation") { %>selected<% } %>>Closed by Stipulation</option>
                    <option value="Settled" <% if (case_status=="Settled") { %>selected<% } %>>Settled</option>
                </select>
                <span id="case_statusSpan" class="kase span_class form_span_vert">
                    <%= case_status %>
                </span>
            </li>
            <li id="attorneyGrid" class="glass" data-row="1" data-col="8" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:<%=additional_display %>">
                <h6><div class="form_label_vert">Attorney</div></h6>
                <div style="float:right; margin-top:-5px;" class="hidden" id="attorneySave">
              &nbsp;<a class="save_field" title="Click to save" id="attorneySaveLink"><i class="glyphicon glyphicon-floppy-save" style="color:#00FF00"></i></a>
              </div>
                <input value="<%= attorney %>" name="attorneyInput" id="attorneyInput" class="kase input_class hidden" />
                <span id="attorneySpan" class="span_class kase form_span_vert">
                	  <%= attorney %>              
                </span>
            </li>
            <li id="workerGrid" class="glass" data-row="1" data-col="9" data-sizex="1" data-sizey="1" style="border:0px #999999 solid;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:3px; background:url(img/glass.png) left top;
-ms-filter:'alpha(opacity=50)';
border:#FFFFFF solid 1px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
-khtml-border-radius: 3px;
border-radius: 3px;
padding:5px;
font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:<%=additional_display %>">
                <h6><div class="form_label_vert">Worker</div></h6>
                <div style="float:right; margin-top:-5px;" class="hidden" id="workerSave">
              &nbsp;<a class="save_field" title="Click to save" id="workerSaveLink"><i class="glyphicon glyphicon-floppy-save" style="color:#00FF00"></i></a>
              </div>
                <input value="<%= worker %>" name="workerInput" id="workerInput" class="kase input_class hidden" />
                <span id="workerSpan" class="span_class kase form_span_vert">
                	   <%= worker %>             
                </span>
            </li>
            <?php } ?>
        </ul>
    </div>
</div>
</form>
</div>
