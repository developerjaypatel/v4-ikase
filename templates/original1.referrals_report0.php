<?php
include("../api/manage_session.php");
session_write_close();
?>
<style>
.actual_data {
	display:none;
}
</style>
<% if (window.location.href.indexOf("#referralreport") < 0) { %>
<table width="100%" border="0" cellpadding="3" cellspacing="0">
  <tr>
    <td><img src="img/ikase_logo_login.png" alt="Logo" width="77" height="32" /></td>
    <td align="left" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em" colspan="3" nowrap="nowrap">
    	<div style="float:right; font-weight:normal; font-size:9px"><em>as of <?php echo date("m/d/y g:iA"); ?></em></div>
        Referred Cases Report - <?php echo $_SESSION['user_customer_name']; ?><%=referring %></td>
  </tr>
</table>
<% } %>
<div style="width:100%" id="kases_list"></div>
<table cellpadding="2" cellspacing="0" border="0" id="summary_table" style="margin-top:10px" align="center">
	<thead>
    <tr>
    	<td><img src="img/ikase_logo_login.png" alt="Logo" width="77" height="32" /></td>
    	<td colspan="3" valign="middle">
        	<div style="float:right; padding-left:10px"><%=showall %></div>
        	<span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">Referrals Summary - <?php echo $_SESSION['user_customer_name']; ?></span>
        </td>
    </tr>
    <tr>
    	<th>
        	Referrer
        </th>
        <th>
        	Year
        </th>
        <th>
        	Month
        </th>
        <th>
        	Cases
        </th>
    </tr>
    </thead>
    <tbody>
    <% _.each( kasesbymonth, function(kasebymonth) { 
    	var expand_link = '';
        var shrink_link = '';
        //var show_row = 'border-bottom:1px solid black';
        var show_row = '';
        var row_class = 'sub_row';
        var totals = '';
        var totals_referring = '';
        var top_border = '';
        if (kasebymonth.case_display_referring!='') {
	        totals_referring = '&nbsp;<span style="font-size:0.8em">(' + arrRefTotals[kasebymonth.referring] + ')</span>';
            top_border = "border-top:1px solid black";
        }
        if (kasebymonth.case_display_year!='') {
        	expand_link = '&nbsp;<a class="expand_year" id="expand_' + kasebymonth.case_year + '_' + kasebymonth.row_id + '" style="text-decoration:none; cursor:pointer">+</a>';
            shrink_link = '&nbsp;<a class="shrink_year" id="shrink_' + kasebymonth.case_year + '_' + kasebymonth.row_id + '" style="text-decoration:none; cursor:pointer; display:none">-</a>';
            show_row = "";
            row_class = '';
            totals = '&nbsp;<span style="font-size:0.8em">(' + arrTotals[kasebymonth.referring + '-' + kasebymonth.case_year] + ')</span>';
        }
        %>
    	<tr style="<%=show_row %>;" class="year_row_<%=kasebymonth.case_year %>_<%=kasebymonth.row_id %> <%=row_class %>">
        	<td style="<%=top_border %>"><%=kasebymonth.case_display_referring %><%=totals_referring %></td>
            <td style="<%=top_border %>">
           	  <a id="year_<%=kasebymonth.case_year %>_<%=kasebymonth.referring.replaceAll(" ", "|") %>" class="open_year filter_link" style="text-decoration:underline; cursor:pointer" title="Click to list _<%=kasebymonth.referring %> <%=kasebymonth.case_display_year %> cases"><%=kasebymonth.case_display_year %></a><%=totals %>&nbsp;<%=expand_link %><%=shrink_link %>
  </td>
            <td style="<%=top_border %>">
            	<span class="actual_data year_<%=kasebymonth.case_year %>_<%=kasebymonth.row_id %>"><%=kasebymonth.case_month_name %></span>
            </td>
            <td style="<%=top_border %>"><span class="actual_data year_<%=kasebymonth.case_year %>_<%=kasebymonth.row_id %>"><a id="<%=kasebymonth.case_year %>_<%=kasebymonth.case_month %>_<%=kasebymonth.case_month_name %>_<%=kasebymonth.row_id %>" class="open_cases filter_link" style="text-decoration:underline; cursor:pointer" title="Click to list cases"><%=kasebymonth.injury_count %></a></span></td>
        </tr>
    <% }); %>
    </tbody>
</table>