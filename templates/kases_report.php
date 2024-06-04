<?php
require_once('../shared/legacy_session.php');
session_write_close();

if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
  $application_logo = "logo-starlinkcms.png";
}
else
{
  $application_logo = "ikase_logo_login.png";
}
?>
<style>
.actual_data {
	display:none;
}
</style>
<table width="1000px" border="0" cellpadding="3" cellspacing="0" align="center" class="main_header alpha_summary">
  <tr>
    <td><img src="img/<?php echo $application_logo;?>" alt="Logo" height="40" /></td>
    <td align="left" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em" colspan="3" nowrap="nowrap">
    	<div style="float:right; font-weight:normal; font-size:9px"><em>as of <?php echo date("m/d/y g:iA"); ?></em></div>
        Cases Report - <?php echo $_SESSION['user_customer_name']; ?></td>
  </tr>
</table>
<div style="width:100%" id="kases_list"></div>
<table cellpadding="2" cellspacing="0" border="0" id="summary_table" style="margin-top:10px" align="center">
	<thead>
    <tr>
    	<td colspan="3">
        	<span style="font-weight:bold; font-size:1.2em">Summary</span>
        </td>
    </tr>
    <tr>
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
        var show_row = 'display:none';
        var row_class = 'sub_row';
        var totals = '';
        if (kasebymonth.case_display_year!='') {
        	expand_link = '&nbsp;<a class="expand_year" id="expand_' + kasebymonth.case_year + '" style="text-decoration:none; cursor:pointer">+</a>';
            shrink_link = '&nbsp;<a class="shrink_year" id="shrink_' + kasebymonth.case_year + '" style="text-decoration:none; cursor:pointer; display:none">-</a>';
            show_row = "";
            row_class = '';
            totals = '&nbsp;<span style="font-size:0.8em">(' + arrTotals[kasebymonth.case_year] + ')</span>';
        }
        %>
    	<tr style="<%=show_row %>" class="year_row_<%=kasebymonth.case_year %> <%=row_class %>">
        	<td>
           	  <a id="year_<%=kasebymonth.case_year %>" class="open_year filter_link" style="text-decoration:underline; cursor:pointer" title="Click to list <%=kasebymonth.case_display_year %> cases"><%=kasebymonth.case_display_year %></a>&nbsp;<%=totals %><%=expand_link %><%=shrink_link %>
  </td>
            <td>
            	<span class="actual_data year_<%=kasebymonth.case_year %>"><%=kasebymonth.case_month_name %></span>
            </td>
            <td><span class="actual_data year_<%=kasebymonth.case_year %>"><a id="<%=kasebymonth.case_year %>_<%=kasebymonth.case_month %>_<%=kasebymonth.case_month_name %>" class="open_cases filter_link" style="text-decoration:underline; cursor:pointer" title="Click to list <%=kasebymonth.case_month %>/<%=kasebymonth.case_year %> cases"><%=kasebymonth.injury_count %></a></span></td>
        </tr>
    <% }); %>
    </tbody>
</table>
