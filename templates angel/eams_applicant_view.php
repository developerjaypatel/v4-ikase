<table cellpadding="2" cellspacing="0" width="100%">
	<tr>
    	<th style="width:150px" align="left" valign="top">Case</th>
    	<td align="left" valign="top" nowrap>
            <%=case_name %>
            <input type="hidden" name="first_name" value="<%=first_name %>" />
            <input type="hidden" name="last_name" value="<%=last_name %>" />
            <input type="hidden" name="case_name" value="<%=first_name %> <%=last_name %> vs <%=employer %>" />             
            <input type="hidden" name="city" value="<%=city %>" />             
            <input type="hidden" name="zip" value="<%=zip %>" /> 
            <input type="hidden" name="employer" value="<%=employer %>" />  
            <input type="hidden" name="deu" value="<%=deu %>" />
            <input type="hidden" name="venue" value="<%=venue %>" />
            <input type="hidden" name="start_date" value="<%=start_date %>" />
            <input type="hidden" name="end_date" value="<%=end_date %>" />           
    </tr>
    <tr>
      <th align="left" valign="top"><strong>DOI&nbsp;</strong></th>
      <td align="left" valign="top"><%=doi %>
                &nbsp;<div id="add_doi" class="doi_eams_holder" style="display:none; background:blue; padding:1px">
                    <a href='javascript:addDOI("<%=doi %>")' class="white_text">add doi</a>
                </div></td>
    </tr>
    <tr>
    	<th align="left" valign="top">Venue</th>
        <td align="left" valign="top">
        	<% if (judge!="") { %>
        	<div style="float:right; width:275px">
            	<div style="display:inline-block; width:50px"><strong>Judge&nbsp;</strong></div><%=judge %>
            </div>
            <% } %>
            <% if (deu=="Y") { %>
            <div style="float:right; width:275px">
            	<div style="display:inline-block; width:50px"><strong>DEU&nbsp;&#10003;</strong></div>
            </div>
            <% } %>
            <%=venue %>
        </td>
    </tr>
</table>
<hr style="margin:5px" />