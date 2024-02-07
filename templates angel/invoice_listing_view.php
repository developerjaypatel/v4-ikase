<?php
include("../api/manage_session.php");
session_write_close();

//special cases
$blnGlauber = ($_SESSION["user_customer_id"]=='1049');
?>
<div class="activity">
	<div id="threadimage_preview_panel" style="position:absolute; width:20px; display:none; z-index:2; background:white;" class="attach_preview_panel"></div>
	<div class="glass_header">
        <div style="float:right;" class="white_text">
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
			<div class="btn-group">            	
            	<label for="activities_searchList" id="label_search_activity" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search Activity</label>
            	
				<input id="activities_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'activity_listing', 'activity')" style="width:190px; height:30px">
				<a id="activities_clear_search" style="position: absolute;
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
        
        <span style="font-size:1.2em; color:#FFFFFF">Invoices</span>
            &nbsp;(<%=activity_invoices.length %>)&nbsp;&nbsp;&nbsp;&nbsp;<a title="Click to compose a new activity" class="compose_new_activity" id="compose_activity" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>&nbsp;&nbsp;&nbsp;
        <?php if ($blnGlauber) { ?>
        <a class="white_text restore_archives" style="cursor:pointer; background:red; color:white; display:none" title="This case was restored from Archives.  The activities have not yet been processed by our bot.&#13;Please click this link to initiate the Archive Restore Process.  You will only have to do this once.&#13;&#13;This process may take a few minutes, because some older cases have up to 5000 entries.">Restore from Archives</a>&nbsp;<span id="restore_archives_count" style="display:none"></span>
        <?php } ?>
        <select name="mass_change" id="mass_change" style="width:150px; display:none">
          <option value="" selected="selected">Choose Action</option>
          <option value="print">Print</option>
          <option value="bill">Invoice</option>
        </select>
        &nbsp;&nbsp;
        <i class="glyphicon glyphicon-ok" style="color:#32CD32; cursor:pointer; display:none" id="saved_invoice">&nbsp;</i>
    </div>
    <table id="activity_invoice_listing" class="tablesorter activity_invoice_listing" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
        	<th width="3%">&nbsp;
            	
            </th>
            <th width="3%">
            	Case
            </th>
            <th width="5%">
            	Invoice ID
            </th>
            <th width="10%">
            	Date
            </th>
            <th>
            	Total
            </th>
        </tr>
        </thead>
        <tbody>
        <% _.each( activity_invoices, function(activity_invoice) {
        %>
        <tr class="invoice_data_row_<%=activity_invoice.invoice_id %>">
            <td valign="top"><a title="Click to edit invoice" class="edit_invoice_full" id="editinvoice_<%= activity_invoice.invoice_id %>" style="cursor:pointer;">
					<i style="font-size:15px; color:blue; cursor:pointer" class="glyphicon glyphicon-edit"></i></a>&nbsp;&nbsp;<a title="Click to delete invoice" class="delete_invoice" id="deleteinvoice_<%= activity_invoice.invoice_id %>" style="cursor:pointer;">
					<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash"></i></a>
                    
            </td>
          <td valign="top"><%=case_id %></td>
          <td valign="top"><%= activity_invoice.invoice_id %></td>
          <td valign="top"><a id="invoice_edit" href="#" class="invoice_date_<%=activity_invoice.invoice_id %>" style="cursor:pointer; color:white" title="Click to edit Invoice"><%= moment(activity_invoice.invoice_date).format("MM/DD/YY h:mmA") %></a></td>
          <td valign="top"><%= activity_invoice.total %></td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>