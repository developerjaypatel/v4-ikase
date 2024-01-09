<?php
include("../api/manage_session.php");
session_write_close();

$day = date('w');
$week_start = date('Y-m-d', strtotime('-'.$day.' days'));
$week_end = date('Y-m-d', strtotime('+'.(6-$day).' days'));

if (strpos($_SESSION['user_role'], "admin") === false) {
	die("<div class='white_text' style='text-align:center;padding-top:10px'>You lack permissions to be here, don't you?</div>");
}
?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this user?
    <div style="padding:5px; text-align:center"><a id="delete_user" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_user" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
	<div class="glass_header">
        <div style="float:right;">
        <span style="font-size:0.7em;background:orange">Inactive Users - Do not show up in lookups</span>
        &nbsp;
        <a id="print_users" style="cursor:pointer" class="white_text">PRINT USERS</a>
        &nbsp;
        <label for="users_searchList" id="label_search_users" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search Users</label>
            <input id="users_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'user_listing', 'user')">
        </div>
       
        <span style="font-size:1.2em; color:#FFFFFF">Users</span>
         
        <a title="new user" id="new_user" href='#users/-1' style="color:#FFFFFF; text-decoration:none; margin-left:50px">
            <button class="kase edit btn btn-transparent" style="color:white; border:0px solid; width:20px">
                <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
            </button>
        </a>
        &nbsp;|&nbsp;<a href="report.php#activity_summary/<?php echo $week_start; ?>/<?php echo $week_end; ?>" target="_blank" title="Click here for a summary of activity for all employees" class="white_text">Activity Summary</a>
    </div>
    <table id="user_listing" class="tablesorter user_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th style="font-size:1.5em; width:200px">
                User
            </th>
            <th style="font-size:1.5em">
                Email
            </th>
            <th>
            	Productivity
            </th>
            <th></th>
            <th>Permissions</th>
            <th>Active</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
       <% _.each( users, function(user) {
       			user.linkstyle = "";
                if (user.activated=="N") {
                	user.linkstyle = "background:orange; color:white; font-weight:bold; padding:2px";
                }
                
                var adhoc = user.adhoc;
                var arrAdhoc = [];
                if (adhoc!="") {
                    var jdata = JSON.parse(adhoc);
                     for(var key in jdata) {
                        if(jdata.hasOwnProperty(key)) {
                            //console.log(key + " --> " + jdata[key]);
                            if (jdata[key]=="Y") {
                            	key = key.replace("_", " ");
                                key = key.replace("checkrequest", "check request");
                                arrAdhoc.push(key)
                            }
                         }
                    }
                    user.adhoc = arrAdhoc.join(", ");
                }
                var blnShow = true;
                if (customer_id==1121) {
                	blnShow = (user.activated != 'N');
                }
                if (blnShow) {
       	%>
       	<tr class="user_data_row user_data_row_<%= user.id %>">
                <td style="font-size:1.5em; min-width:500px" nowrap="nowrap">
                <span style="float:right">
                <a href="#" title="Click to manage documents"><i style="font-size:15px;color:#FFFFFF" class="glyphicon glyphicon-upload"></i></a>
                &nbsp;<a href="#"><i style="font-size:15px;color:#3C9" class="glyphicon glyphicon-earphone" title="Click to add phone message"></i></a>
            	</span>
                <div style="float:right" class="white_text">
                    <a href="#activities/<%= user.id %>/<?php echo $week_start; ?>/<?php echo date("Y-m-d"); ?>" class="white_text" title="Click to search activities for this user" style="font-size:0.8em">Search Activity</a>&nbsp;|&nbsp;
                    <a class="activity_summary white_text" id="activity_summary_<%= user.id %>" style="font-size:0.8em; cursor:pointer">Activity & Usage</a>&nbsp;
                </div>
                <a href='#users/<%= user.id %>' class="list-item_kase kase_link" style="<%= user.linkstyle %>"><%= user.user_name.capitalizeWords() %></a>&nbsp;(<%= user.nickname %>)
                <br />
            <span style="font-size:10px;">Role:</span> <a href='#users/<%= user.id %>' class="list-item_kase" style="color:white; font-size:10px"><%=user.role %></a>&nbsp;&nbsp; <span style="font-size:10px;">Job:</span> <a href='#users/<%= user.id %>' class="list-item_kase" style="color:white; font-size:10px"><%=user.job %></a>
                </td>
                <td style="font-size:1.5em"><%= user.user_email %>
                	<div style="float:right; margin-right:10px">
                    	<!--<a href="#" style="color:#FFFFFF">Email</a>-->
                        <span style="background:<%= user.calendar_color %>; width:20px; height:20px">&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    </div>
                </td>
                <td>
                	<div class="white_text">Activity per Month</div>
                	<iframe src="https://www.ikase.website/api/activity_month.php?user_id=<%= user.id %>&size=mini" frameborder="0" height="80px" width="100%"></iframe>
                </td>
                <td>
                	<div class="white_text">Activity Last Week</div>
                	<iframe src="https://www.ikase.website/api/activity_week.php?user_id=<%= user.id %>&size=mini" frameborder="0" height="80px" width="100%"></iframe>
                </td>
                <td style="font-size:1.5em"><%= user.adhoc %></td>
				<td style="font-size:1.5em">
                	<%= user.activated %>
                </td>
                <td>
                	<a class="delete_icon" id="confirmdelete_user_<%= user.user_id %>" title="Click to delete user" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FF0000;">&nbsp;</i></a>
                </td>
        </tr>
        <% 	}
        }); %>
        </tbody>
    </table>
</div>