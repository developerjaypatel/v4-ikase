<?php
require_once('../shared/legacy_session.php');
session_write_close();
?>
<table id="kase_listing_mobile" class="tablesorter kase_listing_mobile" border="0" cellpadding="0" cellspacing="1" width="100%" style="-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <thead>
    <tr>
        <th style="font-size:2em">
            Kase
        </th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody class="listing_item">
	<% 
       var current_letter = "";
       var the_letter = "";
       var letter_string = "";     
       _.each( kases, function(kase) {
            //we might have a new letter
            the_letter = kase.alpha_name.charAt(0);
            letter_string = kase.alpha_name.charAt(0).valueOf();
            var character_length = 30;
            
            if (current_letter != the_letter) {
                current_letter = the_letter;
        %>
        <tr class="<%=current_letter %> letter_row">
            <td colspan="2">
                <div style="width:100%; 
                    text-align:left; 
                    font-size:1.8em; 
                    background:#CFF; 
                    color:red;">
                    	<%= current_letter %>
				</div>
            </td>
        </tr>
        <% } %>
    <% var intCounter = 0;
    if (!kase.skip_me) {
    %>
    <tr class="kase_data_row injury_row_<%= kase.id %> kase_data_row_<%= kase.case_id %> <%=current_letter %>" style="height:35px; font-family:Arial, Helvetica, sans-serif; font-size:1.5em">
        <td style="border:0px solid red;"> 	
        	<div style="float:right">
        		<a id="injury_<%= kase.case_id %>" href='#injury/<%= kase.case_id %>/<%= kase.id %>' class="list-item_kase kase_link white_text"><%=kase.start_date %></a>
            </div>
        	<!--<a id="link_<%= kase.case_id %>" href="#" class="list-item_kase kase_link" onClick='showTabs(<%= kase.case_id %>)' style="font-size:1.5em"><%=highLight(kase.case_number, key) %><% if (kase.injury_number>1) { %>-<%=kase.injury_number %><% } %></a>&nbsp;<span class="kase_source"><%=kase.source %></span>-->
            <!-- solulab code start 15-04-2019-->
            <a id="link_<%= kase.case_id %>" href="#" class="list-item_kase kase_link" onClick='showTabs(<%= kase.case_id %>)' style="font-size:1.5em"><%=highLight(kase.file_number, key) %><% if (kase.injury_number>1) { %>-<%=kase.injury_number %><% } %></a>&nbsp;<span class="kase_source"><%=kase.source %></span>
            <!-- solulab code end 15-04-2019-->
            <br />
            <span class="search_kase_item">
            <!--<%=kase.full_name %>*-->
            <!-- solulab code start 15-04-2019-->
            <%=kase.case_name %>
            <!-- solulab code end 15-04-2019-->
                        <!--.substring(0, character_length) + "..."-->
            </span>
        </td>
        <td nowrap="nowrap">
            <a title="Click to compose a new note" href="#notemobile/<%= kase.case_id %>" class="compose_new_note" id="compose_note_<%= kase.case_id %>" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF"></i></a>&nbsp;&nbsp;
            <a title="Click to compose a new task" href="#taskmobile/<%= kase.case_id %>" class="compose_new_task" id="compose_task_<%= kase.case_id %>" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#00FF00"></i></a>&nbsp;&nbsp;
            <a title="Click to compose a new event" href="#eventmobile/<%= kase.case_id %>" class="compose_new_event" id="compose_event_<%= kase.case_id %>" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#3F3FFC"></i></a>
        </td>
    </tr>
    <% }
    }); %>
    </tbody>
</table>
<div id="kase_listing_mobile_all_done"></div>
<script language="javascript">
$( "#kase_listing_mobile_all_done" ).trigger( "click" );
</script>
