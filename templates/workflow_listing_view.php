<?php require_once('../shared/legacy_session.php');
session_write_close();
?>
<div>
	<% if (!embedded) { %>
    <div id="workflow_listing_header" class="glass_header">
        <div style="float:right;">
        <input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
            
            <div class="btn-group">
                
                 <label for="workflows_searchList" id="label_search_workflow" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
                
                <input id="workflows_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'workflow_listing', 'workflow')">
                <a id="workflows_clear_search" style="position: absolute;
                right: 2px;
                top: 0px;
                bottom: 2px;
                height: 14px;
                margin: auto;
                cursor: pointer;
                border: 0px solid green;
                "><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
            </div>
        </div>
        <span style="font-size:1.2em; color:#FFFFFF"><%=page_title %>s</span>
        &nbsp;&nbsp;<span class="white_text">(<%=workflows.length %>)</span>
        
        &nbsp;
        <button id="new_workflow" class="btn btn-sm btn-primary btn_workflow" title="Click to add a new Workflow" style="margin-top:-5px">New Workflow</button> <!--<a href="screens/statute/statutes.jpg" target="_blank" class="white_text">Example</a>-->
        <span style="font-style:italic" class="white_text" id="workflow_feedback">&nbsp;Workflows are applied overnight as a Batch Job.  All cases affected will be updated at that time.</span>
    </div>
    <% } %>
    <table id="workflow_listing" class="tablesorter workflow_listing workflow_listing_<%=page_title %>" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
        	<th width="1%">&nbsp;
                
            </th>
        	<th width="1%">
                Case&nbsp;Type
            </th>
            <th width="1%">
                ID
            </th>
            <th align="left" width="1%">&nbsp;</th>
            <th align="left">
                Description
            </th>
            <th width="10%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <% 
        _.each( workflows, function(workflow) {
        	var activate_style = "";
            var active_label = "De-activated";
            var deactivate_style = "display:none";
            if (workflow.active=="Y") {
            	active_label = "Activated";
    	        activate_style = "display:none";
	            deactivate_style = "";
            }
            if (workflow.activate_date!="") {
	            workflow.activate_date = moment(workflow.activate_date).format("MM/DD/YYYY");
                
                active_label += " by " + workflow.activation_user + " on " + workflow.activate_date;
            }
        %>
        <tr class="workflow_data_row workflow_data_row_<%= workflow.id %>">
        	<td align="left" valign="top">
            	<a id="edit_workflow_<%=workflow.id %>" class="edit_workflow">
                	<i style="font-size:15px; color:#a9bafd; cursor:pointer" class="glyphicon glyphicon-edit"></i>
                </a>
            </td>
            <td align="left" valign="top" nowrap id="worflow_case_type_<%=workflow.id %>"><%= workflow.case_type %></td>
            <td align="left" valign="top" nowrap><%= workflow.workflow_number %></td>
            <td align="left" valign="top">
            	<button id="list_kases_<%=workflow.id %>" class="btn btn-sm btn-primary list_kases" title="List Kases that were affected by this Workflow">Kases</button>
            </td>
            <td align="left" valign="top"><%= workflow.description %></td>
            <td align="left" valign="top" nowrap="nowrap">
            	<%=active_label %>
            	<button class="btn btn-sm btn-primary workflow_activate" id="workflow_activate_<%=workflow.id %>" style="<%=activate_style %>">Activate</button>
                <button class="btn btn-sm btn-warning workflow_deactivate" id="workflow_deactivate_<%=workflow.id %>" style="<%=deactivate_style %>">De-Activate</button>
            	<!--<a style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_workflow" id="activate_<%= workflow.id %>" title="Click to delete"></i>-->
            </td>
        </tr>
        <tr class="workflow_kases_row_<%=workflow.id %>" style="display:none">
        	<td colspan="6" id="workflow_kases_holder_<%=workflow.id %>">&nbsp;
            	
            </td>
        </tr>
        <tr style="display:none">
        	<td colspan="6">&nbsp;
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>
<div id="workflow_listing_all_done"></div>
<script language="javascript">
$( "#workflow_listing_all_done" ).trigger( "click" );
</script>
