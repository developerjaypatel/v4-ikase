<?php
require_once('../shared/legacy_session.php');
session_write_close();
?>
<style>
.actual_data {
	/*display:none;*/
}
a:visited {
	color:blue;
}
</style>
<table width="1000" border="0" cellpadding="3" cellspacing="0" align="center">
  <tr>
    <td><img src="img/ikase_logo_login.png" alt="Logo" width="77" height="32" /></td>
    <td align="center" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em" colspan="3" nowrap="nowrap">
    	<div style="float:right; font-weight:normal; font-size:9px"><em>as of <?php echo date("m/d/y g:iA"); ?></em></div>
    Clients Assignment Report - <?php echo $_SESSION['user_customer_name']; ?><%=referring %></td>
  </tr>
</table>
<div style="width:100%" id="kases_list"></div>
<table cellpadding="2" cellspacing="0" border="0" id="summary_table" style="margin-top:10px" align="center">
	<thead>
    <tr>
    	<td colspan="4">
        	<div style="float:right"><%=showall %></div>
        	<span style="font-weight:bold; font-size:1.2em">Summary</span>
        </td>
    </tr>
    <tr>
      <th align="left">&nbsp;</th>
      <th align="left">&nbsp;</th>
      <th align="left">&nbsp;</th>
      <th colspan="2" align="left" bgcolor="#009900" style="color:white">Open</th>
      <th colspan="2" align="left" bgcolor="#FF0000" style="color:white">Closed</th>
      </tr>
    <tr>
    	<th align="left">
        	Client
        </th>
        <th align="left">
        	Year
        </th>
        <th align="left">
        	Month
        </th>
        <th align="left" bgcolor="#009900" style="color:white">
       	    Cases
       </th>
      <th align="left" bgcolor="#009900" style="color:white">
       	    Injuries
       </th>
        <th align="left" bgcolor="#FF0000" style="color:white">
       	    Cases
       </th>
        <th align="left" bgcolor="#FF0000" style="color:white">
       	    Injuries
       </th>
       <th align="left">
       		Total Cases
       </th>
    </tr>
    </thead>
    <tbody>
    <% _.each( kasesbymonth, function(kasebymonth) { 
        %>
    	<tr style="<%=kasebymonth.show_row %>;" class="year_row_<%=kasebymonth.case_year %>_<%=kasebymonth.row_id %> <%=kasebymonth.row_class %>">
        	<td style="<%=kasebymonth.top_border %>">
            	<%=kasebymonth.case_display_referring %>
                <input type="hidden" id="referring_name_<%=kasebymonth.referring_id %>" value="<%=kasebymonth.referring %>" />
            </td>
            <td style="<%=kasebymonth.top_border %>">
                <div style="width:45px; display:inline-block; border:0px solid red">
                <% if (kasebymonth.case_display_year!='') { %>
                    <a id="year_<%=kasebymonth.case_year %>_<%=kasebymonth.referring_id %>" class="open_year filter_link" style="text-decoration:underline; cursor:pointer; color:blue" title="Click to list <%=kasebymonth.referring %> <%=kasebymonth.case_display_year %> cases">
                        <%=kasebymonth.case_display_year %>
                    </a>
                <% } else { %>
                &nbsp;
                <% } %>
                </div>
              
            </td>
            <td style="<%=kasebymonth.top_border %>">
            	<span class="actual_data year_<%=kasebymonth.case_year %>_<%=kasebymonth.row_id %>"><%=kasebymonth.case_month_name %></span>
            </td>
            <td style="<%=kasebymonth.top_border %>">
            	<span class="actual_data year_<%=kasebymonth.case_year %>_<%=kasebymonth.row_id %>">
                	<% if (kasebymonth.case_count_open > 0) { %>
                	<a id="<%=kasebymonth.case_year %>_<%=kasebymonth.case_month %>_<%=kasebymonth.case_month_name %>_<%=kasebymonth.referring_id %>_open" class="open_cases filter_link" style="text-decoration:underline; cursor:pointer; color:blue" title="Click to list open cases">
                    	<%=kasebymonth.case_count_open %>
                    </a>
                    <% } else { %>
                    	<%=kasebymonth.case_count_open %>
                    <% } %>
                </span>
            </td>
            <td style="<%=kasebymonth.top_border %>">
            	<span class="actual_data year_<%=kasebymonth.case_year %>_<%=kasebymonth.row_id %>">
                	<%=kasebymonth.injury_count_open %>
                </span>
            </td>
            <td style="<%=kasebymonth.top_border %>">
            	<span class="actual_data year_<%=kasebymonth.case_year %>_<%=kasebymonth.row_id %>">
                	<% if (kasebymonth.case_count_closed > 0) { %>
                	<a id="<%=kasebymonth.case_year %>_<%=kasebymonth.case_month %>_<%=kasebymonth.case_month_name %>_<%=kasebymonth.referring_id %>_closed" class="open_cases filter_link" style="text-decoration:underline; cursor:pointer; color:blue" title="Click to list closed cases">
                    	<%=kasebymonth.case_count_closed %>
                    </a>
                    <% } else { %>
                    	<%=kasebymonth.case_count_closed %>
                    <% } %>
                </span>
            </td>
            <td style="<%=kasebymonth.top_border %>">
            	<span class="actual_data year_<%=kasebymonth.case_year %>_<%=kasebymonth.row_id %>">
                	<%=kasebymonth.injury_count_closed %>
                </span>
            </td>
            <td style="<%=kasebymonth.top_border %>">
            	<span class="actual_data year_<%=kasebymonth.case_year %>_<%=kasebymonth.row_id %>">
                	<% if (Number(kasebymonth.case_count_open) + Number(kasebymonth.case_count_closed) > 0) { %>
                	<a id="<%=kasebymonth.case_year %>_<%=kasebymonth.case_month %>_<%=kasebymonth.case_month_name %>_<%=kasebymonth.referring_id %>" class="open_cases filter_link" style="text-decoration:underline; cursor:pointer; color:blue" title="Click to list cases">
                    	<%=Number(kasebymonth.case_count_open) + Number(kasebymonth.case_count_closed) %>
                    </a>
                    <% } else { %>
                    	<%=Number(kasebymonth.case_count_open) + Number(kasebymonth.case_count_closed) %>
                    <% } %>
                </span>
            </td>
        </tr>
    <% }); %>
    </tbody>
</table>
