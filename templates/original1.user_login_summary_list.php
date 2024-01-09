<?php 
include("../api/manage_session.php");
session_write_close();

if (strpos($_SESSION['user_role'], "admin") === false) {
	die("<div style='text-align:center;padding-top:10px'>You lack permissions to be here, don't you?</div>");
}
?>
<div id="user_login_summary_list">
	<table border="0" cellpadding="2" cellspacing="0" style="width:90%" align="center">  		
        <thead>
        <tr>
            <td width="77"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
            <td align="left" colspan="6">
                <div style="float:right">
                    <em>as of <?php echo date("m/d/y g:iA"); ?></em>
                </div>
                <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span>
            </td>
          </tr>
        <tr>
            <th style="font-size:1.5em" align="center" colspan="6">
                <%=customer_name %> :: Employee Activity and Usage :: <%=employee.toLowerCase().capitalizeWords() %>
                <div style="font-size:1.0em">
	                From <input type="date" id="start_date" class="report_dates" value="<%=moment(start_date).format('YYYY-MM-DD') %>" /> through <input type="date" id="end_date" class="report_dates" value="<%=moment(end_date).format('YYYY-MM-DD') %>" />
                </div>
                <div id="non_consecutive" style="display:none"><span style="font-size:0.8em; border:1px solid red; padding:2px; ">Non Consecutive Work Days</span></div>
            </th>
        </tr>
        </thead>
    </table>
    
	<table border="0" cellpadding="2" cellspacing="0" style="width:90%; margin-top:50px;" align="center">  	
    	<thead>
    	<tr>
            <th align="left" valign="top">Login Date</th>
            <th align="left" valign="top">Login Day</th>
            <th align="left" valign="top">Login Time</th>
            <th align="left" valign="top">Logout Time</th>
            <th align="left" valign="top">Estimated Logout</th>
            <th align="left" valign="top">Hours Logged In</th>
            <th align="left" valign="top">Last Case View</th>
            <th align="left" valign="top">Last Activity</th>
            <th align="left" valign="top">Case Count</th>
            <th align="left" valign="top">Activity Count</th>
        </tr>
        </thead>
        <tbody>
        <% 
        var current_dow = -1;
        var previous_row = -1;
        var blnNonConsec = false;
        _.each( summaries, function(summary) {
        	if (current_dow!=summary.dow) {
            	//what was it before
                previous_row = current_dow;
                current_dow = summary.dow;
                 if (previous_row == -1) {
                	previous_row = current_dow - 1;
                }
            }
            
            //compare to previous
            //not mondays
            var warning_style = "";
            if (current_dow > 1) {
            	//consecutive
                if ((current_dow - previous_row) > 1) {
                	warning_style = "border:1px solid red; padding:2px; color:black";
                    blnNonConsec = true;
                }
            }
	        summary.login = moment(summary.login).format("h:mmA") ;
            summary.logout = moment(summary.logout).format("h:mmA") ;
            
        	if (summary.login_date!="") {
            	summary.login_date = moment(summary.login_date).format("MM/DD/YYYY") ;
            }
            if (summary.last_track!="") {
            	summary.last_track = moment(summary.last_track).format("h:mmA") ;
            }
            if (summary.estimated_logout!="") {
            	summary.estimated_logout = moment(summary.estimated_logout).format("h:mmA") ;
            }
            if (summary.last_view!="") {
            	summary.last_view = moment(summary.last_view).format("h:mmA") ;
            }
            %>
            <tr>
                <td align="left" valign="top" style="padding:2px">
                	<%=summary.login_date%>
                </td>
                <td align="left" valign="top" style="padding:2px">
                	<span style="<%=warning_style %>"><%=summary.dayw %></span>
                </td>
                <td align="left" valign="top" style="padding:2px">
                	<%=summary.login %>
                </td>
                <td align="left" valign="top" style="padding:2px">
                	<%=summary.logout %>
                </td>
                <td align="left" valign="top" style="padding:2px">
                	<%=summary.estimated_logout %>
                </td>
                <td align="left" valign="top" style="padding:2px">
                	<%=summary.spent_time %>
                </td>
                <td align="left" valign="top" style="padding:2px">
                	<%=summary.last_view %>
                </td>
                <td align="left" valign="top" style="padding:2px">
                	<%=summary.last_track %>
                </td>
                
                <td align="left" valign="top" style="padding:2px">
                	<%=summary.case_count %>
                </td>
                <td align="left" valign="top" style="padding:2px">
                	<%=summary.activity_count %>
                </td>     	
            </tr>
            <%
        });
        %>
        </tbody>
	</table>
</div>
<%if (blnNonConsec) {%>
<script type="application/javascript">
setTimeout(function() {
	document.getElementById("non_consecutive").style.display = "";
}, 500);
</script>
<% } %>