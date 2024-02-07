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
    	<td colspan="4" style="height:50px">&nbsp;</td>
    </tr>
    </thead>
</table>
<table cellpadding="2" cellspacing="0" border="0" id="summary_table" style="margin-top:10px" align="center" width="80%">
	<thead>
    <tr>
    	<th align="left" width="1%">
        	Referrer
        </th>
        <th align="left">
        	Year
        </th>
    </tr>
    </thead>
    <tbody>
    <% 
    var current_year = "";
    var current_referring = "";
    var row_count = -1;
    var year_count = -1;
    _.each( kasesbymonth, function(kasebymonth) {
	    kasebymonth.case_display_referring = "";
        kasebymonth.referring_count = "";
        kasebymonth.referring_year_count = "";
        var blnSummaryRow = false;
    	if (current_referring!=kasebymonth.referring) {
	        current_year = "";
            current_referring = kasebymonth.referring;
            kasebymonth.case_display_referring = current_referring;
            row_count++;
            kasebymonth.referring_count = '&nbsp;<span id="totals_<%=row_count %>">(<%=arrTotals[row_count] %>)</span>';
            blnSummaryRow = true;
        }
        if (current_year!=kasebymonth.case_year) {
        	current_year = kasebymonth.case_year;
            year_count++;
            kasebymonth.referring_year_count = '&nbsp;<span id="year_totals_<%=row_count %>">(<a class="expand_cases_year" style="cursor:pointer; text-decoration:underline" id="expand_cases_year_<%=kasebymonth.case_year %>_<%=kasebymonth.referring_id %>"><%=arrYearTotals[year_count] %></a>)</span>';
    	 %>
    	<tr class="year_row_<%=kasebymonth.case_year %>_<%=kasebymonth.referring_id %>">
        	<!--
            <td align="left">
            	<%=kasebymonth.referring_id %><br /><%=current_year %>
            </td>
            -->
            <td align="left" valign="top" nowrap="nowrap">
            	<a id="referring_<%=kasebymonth.case_year %>_<%=kasebymonth.referring_id %>" style="cursor:pointer; text-decoration:underline" class="referring_cases"><%=kasebymonth.case_display_referring %></a>
                <%=kasebymonth.referring_count %>
                <input type="hidden" id="referring_name_<%=kasebymonth.case_year %>_<%=kasebymonth.referring_id %>" value="<%=kasebymonth.referring %>" />
            </td>
            <td align="left" valign="top">
           	  <a class="expand_year" style="cursor:pointer; text-decoration:underline; color:#428bca" id="expandyear_<%=kasebymonth.case_year %>_<%=kasebymonth.referring_id %>"><%=kasebymonth.case_year %></a><%=kasebymonth.referring_year_count %>
              <div id="months_<%=kasebymonth.case_year %>_<%=kasebymonth.referring_id %>" style="display:none" class="data_cells">
              </div>
  			</td>
        </tr>
        <% if (blnSummaryRow) { %>
        <tr class="summary_info" id="cases_<%=kasebymonth.referring_id %>" style="display:none">
        	<td align="left" valign="top" colspan="2" id="cell_<%=kasebymonth.referring_id %>" class="data_cells">
            </td>
        </tr>
        <% } %>
        <% 
        } %>
    <% }); %>
    </tbody>
</table>