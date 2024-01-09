<?php
include("../api/manage_session.php");
session_write_close();
?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this contact?
    <div style="padding:5px; text-align:center"><a id="delete_user" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_user" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
	<div class="glass_header">
        <div style="float:right;">
        	<label for="contacts_searchList" id="label_search_contacts" style="font-size:1em; cursor:text; position:relative; top:0px; left:105px; width:100px; color:#999">Search</label>
            
				<input id="contacts_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'contact_listing', 'contact')" style="height:25px; line-height:32px; margin-top:-5px">
				<a id="contact_clear_search" style="position: absolute;
				right: 2px;
				top: 0;
				bottom: 9px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
        </div>
       
        <span style="font-size:1.2em; color:#FFFFFF">Contacts</span>&nbsp;&nbsp;<span style="color:white">(<%=contacts.length %>)</span>
    </div>
    <table id="contact_listing" class="tablesorter contact_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th style="font-size:1.5em; width:500px">
                Contact
            </th>
            <th style="font-size:1.5em; width:200px">
                Name
            </th>
             <th style="font-size:1.5em;">&nbsp;
             </th>
            <th style="font-size:1.5em; width:200px">
                Phone
            </th>
            <th style="font-size:1.5em; width:200px">
                Address
            </th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
       <% _.each( contacts, function(contact) {
       			if (contact.last_email_received!="") {
                	contact.last_email_received = moment(contact.last_email_received).format("MM/DD/YYYY");
                }
                if (contact.last_email_sent!="") {
                	contact.last_email_sent = moment(contact.last_email_sent).format("MM/DD/YYYY");
                }
       %>
       	<tr class="contact_data_row contact_data_row_<%= contact.id %>">
                <td style="font-size:1.5em; width:400px" align="left" valign="top">
                	<div style="float:right">
                    	<a href='#contacts/<%= contact.contact_id %>' class="list-item_kase kase_link" style="color:white"><i style="font-size:15px;color:#06f; cursor:pointer;" class="glyphicon glyphicon-edit" title="Click to Edit Contact"></i></a>
                        &nbsp;|&nbsp;
                    	<a title="Click to compose a new message" class="compose_message" id="contact_<%= contact.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF"></i></a>
                    </div>
                	<a href='#contacts/<%= contact.contact_id %>' class="list-item_kase kase_link" style="color:white"><%= contact.email %></a>
                </td>
                <td style="font-size:1.5em; width:400px" align="left" valign="top">
                	<% if (contact.spam_status!="OK") { %>
                	<div style="float:right; background:red; color:white; padding:2px">
                    	BLOCKED
                    </div>
                    <% } %>
                    <%= contact.first_name %> <%=contact.last_name %>
                </td>
                <td style="font-size:1.5em; margin-left:0px" align="left" valign="top" nowrap="nowrap">
                <% if (contact.messages_sent > 0) { %>
                	<div>Sent: <%=contact.messages_sent %>&nbsp;<span title='last email sent date'>(<%=contact.last_email_sent %>)</span></div>
                <% } %>
                <% if (contact.messages_received > 0) { %>
                	<div>Received: <%=contact.messages_received %>&nbsp;<span title='last email received date'>(<%=contact.last_email_received %>)</span></div>
                <% } %>
                </td>
                <td style="font-size:1.5em; width:400px" align="left" valign="top">
                	<%= contact.phone %>
                </td>
                <td style="font-size:1.5em; width:300px; margin-left:0px" align="left" valign="top">
                	<%= contact.full_address %>
                </td>
				<td style="font-size:1.5em;" align="left" valign="top">
                	<a class="delete_icon" id="confirmdelete_contact_<%= contact.contact_id %>" title="Click to delete Contact" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FF0000;">&nbsp;</i></a>
                </td>
        </tr>
        
        <% }); %>
        </tbody>
    </table>
</div>