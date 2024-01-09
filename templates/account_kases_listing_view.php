<% blnWorkflowKases = (page_title=="Workflow Kases" || page_title=="Contact Kases"); %>
<div>
    <table id="account_kases_listing" class="tablesorter account_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th align="left" valign="top" style="font-size:1.5em; width:200px">
                    Case
                </th>
                <th align="left" valign="top" style="font-size:1.5em; width:200px">
                    Type
                </th>
                <% if (!blnWorkflowKases) { %>
                <th align="left" valign="top" style="font-size:1.5em" width="1%">
                    Current&nbsp;Balance
                </th>
                <% } %>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <% _.each( account_kases, function(kase) {
        		kase.case_type = kase.case_type.replace("_", " ").capitalizeWords();
				kase.case_type = kase.case_type.replace(" ", "&nbsp;");
				if (kase.case_type=="Newpi" || kase.case_type=="Pi") {
					kase.case_type = "PI";
				}
                if (kase.case_type=="Wcab") {
                	kase.case_type = "WCAB";
                }
                if (kase.case_name=="") {
	                kase.case_name = kase.file_number;
                }
                if (kase.case_name=="") {
	                kase.case_name = kase.case_number;
                }
         %>
        <tr>
	        <td align="left" valign="top" style="font-size:1.5em;" nowrap="nowrap">
        		<a href="#kase/<%=kase.id %>" class="white_text"><%=kase.case_name %></a>
    	    </td>
            <td align="left" valign="top" style="font-size:1.5em;" nowrap="nowrap">
        		<%=kase.case_type %>
    	    </td>
            <% if (!blnWorkflowKases) { %>
            <td align="right" valign="top" style="font-size:1.5em" width="1%">
            	<% if (kase.balance > -1) { %>
            	$<%=formatDollar(kase.balance) %>
                <% } else { 
                	kase.balance *= -1;
                %>
                ($<%=formatDollar(kase.balance) %>)
                <% } %>
            </td>
            <% } %>
             <td align="left" valign="top" style="font-size:1.5em;">
             	<% if (!blnWorkflowKases) { %>
            	<button class="btn btn-xs btn-primary review_transactions" id="review_<%= kase.id %>">Review Kase Transactions</button>
                &nbsp;|&nbsp;
                <button class="btn btn-xs review_books" id="books_<%= kase.id %>">Books</button>
                <% } else { %>
                <button class="btn btn-xs review_tasks" id="tasks_<%= kase.id %>">Tasks</button>
                &nbsp;|&nbsp;
                <button class="btn btn-xs review_notes" id="notes_<%= kase.id %>">Notes</button>
                <% } %>
            </td>
        </tr>
        <% }); %>
    	</tbody>
	</table>
</div>
<div id="account_kases_listing_all_done"></div>
<script language="javascript">
$( "#account_kases_listing_all_done" ).trigger( "click" );
</script>