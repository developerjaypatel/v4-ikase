<div style="background:white; border:1px solid black; padding:2px; color:black; width:500px">
	<table id="matrix_order_listing" class="matrix_order_listing" border="0" cellpadding="2" cellspacing="0" style="width:100%; display:">
    	<% if (matrix_orders.length > 0) { %>
        <thead> 
        <tr>
            <th align="left" valign="top" style="font-size:1.1em; width:5%" nowrap="nowrap">Order ID</th>
            <th align="left" valign="top" style="font-size:1.1em; width:5%">Assigned</th>
            <th align="left" valign="top" style="font-size:1.1em; width:25%">
            	Applicant
            </th>
            <th align="left" valign="top" style="font-size:1.1em;">
            	<div style="float:right">
                    <a id="close_matrix_order_listing" style="color:black; font-size:1.2em; text-decoration:none; cursor:pointer" title="Click to close">&times;</a>
                </div>
            	Employer
            </th>
          </tr>
        </thead>
        <% } %>
        <tbody>
        <% if (matrix_orders.length==0) { %>
        <tr  class="matrix_order_data_row matrix_order_data_row_nodata">
        	<td align="left" valign="top" colspan="4">
            	<span style="font-weight:bold">Nothing Found in Matrix System for this Applicant</span>
            </td>
        </tr>
        <% } %>
       	<% _.each( matrix_orders, function(matrix_order) {
        %>
        <tr  class="matrix_order_data_row matrix_order_data_row_<%= matrix_order.order_id %>">
        	<td align="left" valign="top">
            	<a id="assign_order_<%=matrix_order.order_id %>" class="assign_order" style="cursor:pointer; text-decoration:underline" title="Click to link this Case to Matrix Order ID <%=matrix_order.order_id %>">
            		<%=matrix_order.order_id %>
                </a>
            </td>
            <td align="left" valign="top" nowrap="nowrap">
            	<%=matrix_order.assigned_date %>
                <span id="assigned_date_<%=matrix_order.order_id %>" style="display:none"><%=matrix_order.actual_assigned_date %></span>
            </td>
            <td align="left" valign="top" nowrap="nowrap">
            	<%=matrix_order.applicant %>
            </td>
            <td align="left" valign="top" nowrap="nowrap">
            	<%=matrix_order.employer %>
            </td>
          </tr>
        <% }); %>
        	<tr id="link_order_row">
            	<td align="left" valign="top">
                	<input type="number" id="new_matrix_order_id" style="width:60px" />
                </td>
                <td align="left" valign="top" colspan="3" id="link_order_feedback">
                	Enter Matrix Order ID to link to this Kase
                </td>
            </tr>
            <tr id="link_order_confirmation_row" style="display:none">
        		<td align="left" valign="top" id="order_confirm_order_id">
                </td>
                <td align="left" valign="top" nowrap="nowrap" id="order_confirm_assigned_date">
                </td>
                <td align="left" valign="top" nowrap="nowrap" id="order_confirm_applicant">
                </td>
                <td align="left" valign="top" nowrap="nowrap" id="order_confirm_employer">
                </td>
              </tr>
        </tbody>
    </table>
</div>
<div id="matrix_order_listing_all_done"></div>
<script language="javascript">
$( "#matrix_order_listing_all_done" ).trigger( "click" );
</script>