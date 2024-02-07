<table cellpadding="2" cellspacing="0" width="100%">
	<%  
    var iCounter = 1;
    var blnFound = false;
    _.each( previous_cases, function(previous_case) { %>
    <tr>
    	<td width="3%" align="left" nowrap="nowrap">
        	<a href="<?php echo $_SERVER['HTTP_REFERER']; ?>#parties/<%=previous_case.case_id %>" class="white_text" target="_blank"><%=previous_case.case_number %>-<%=previous_case.injury_number %></a>
        </td>
        <td width="26%" align="left" nowrap="nowrap"><%=previous_case.case_name %></td>
        <td width="26%" align="left"><%=previous_case.doi_start %></td>
        <td width="26%" align="left">
        <% if (previous_case.doi_start!=previous_case.doi_start) { %>
        	<%=previous_case.doi_end %>
        <% } %>
        </td>
        <% if (previous_case.same_adj_number!="Y") { %>
        <td  width="10%" align="left" nowrap="nowrap">
        	<% if (!blnFound) { %>
            Same Applicant? Y&nbsp;<input type="radio" name="previous_case_<%=previous_case.case_id %>" id="previous_case_yes_<%=previous_case.case_id %>" value="Y" />&nbsp;&nbsp;N&nbsp;<input type="radio" name="previous_case_<%=previous_case.case_id %>" id="previous_case_no_<%=previous_case.case_id %>" value="N" />
            <% } else  { %>
            &nbsp;
            <% } %>
        </td>
        <% } else { 
	        	blnFound = true;
        %>
        <td  width="1%" align="left" nowrap="nowrap"><input type="hidden" name="previous_case_id" id="previous_case_id" value="<%=previous_case.case_id %>" /><span class="white_text">&#10003;</span>
        </td>
        <% } %>
    </tr>
    <%	iCounter++;
    }); %>
</table>
<hr style="margin:5px" />