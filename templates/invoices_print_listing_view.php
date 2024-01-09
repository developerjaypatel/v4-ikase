<div style="border:#000000; width:99%" align="center">

	<table border="0" cellpadding="2" cellspacing="0" style="width:90%" align="center">  		
        <thead>
        <tr>
            <td width="77"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
            <td align="left" colspan="6">
                <div style="float:right">
                    <em>As of <?php echo date("m/d/y g:iA"); ?></em>
                </div>
                <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span>
            </td>
          </tr>
        <tr>
            <th style="font-size:1.5em" align="left" colspan="6">
            	<% if (case_id=="") { %>
                Invoices: <%=activity_invoices.length %> entries for <%=activity_user_name %> (<%=activity_nickname %>) from <%=activity_start %> through <%=activity_end %>
                <input id="start_dateInput" class="range_dates" value="<%= moment(activity_start).format("MM/DD/YYYY") %>" style="width:80px" placeholder="From Date" /> through <input id="end_dateInput" class="range_dates" value="<%= moment(activity_end).format("MM/DD/YYYY") %>" style="width:80px" placeholder="Through Date" />
                <% } else { %>
                <%= case_name %><br />
                Invoices (<%=activity_invoices.length %>)
                <% } %>
                <br /><br />
            </th>
        </tr>
        </thead>
    </table>
    <select name="mass_change" id="mass_change" style="width:150px; display:none">
      <option value="" selected="selected">Choose Action</option>
      <option value="print">Print</option>
    </select>
    <table id="activity_invoice_listing" class="tablesorter activity_invoice_listing" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
        	<th width="3%">
            	<input type="checkbox" id="check_print" class="check_all" style="display:"  />
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
        <tr class="invoice_data_row_<%=activity_invoice.invoice_uuid %>">
            <td valign="top"><input type="checkbox" id="check_printone_<%=activity_invoice.invoice_id %>" class="check_thisone" style="display" /></td>
          <td valign="top"><%=case_id %></td>
          <td valign="top"><%= activity_invoice.invoice_id %></td>
          <td valign="top"><a id="invoice_edit" href="#" class="invoice_date_<%=activity_invoice.invoice_id %>" style="cursor:pointer; color:white" title="Click to edit Invoice"><%= moment(activity_invoice.invoice_date).format("MM/DD/YY h:mmA") %></a></td>
          <td valign="top"><%= activity_invoice.total %></td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>