<style type="text/css">
a.disabled {
  pointer-events: none;
  cursor: default;
}
</style>

<div class="row-fluid">
	<div id="kase_nav" class="span12">
    <ul class="nav nav-pills pill_color">
    <li style="background:url(img/glass_calendar.png) left top; width:96px; height:26px" class="pills pill_color navpill" id="dash_pill"><a id="dash" class="misc" style="color:#FFFFFF; padding:1px; padding-left:2px; cursor:pointer"><!--<i class="glyphicon glyphicon-book" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:1.01em">Dash</span></a></li>
    <li style="background:url(img/glass_calendar.png) left top; width:96px; height:26px" class="pills pill_color navpill"><a id="parties" class="misc" style="color:#FFFFFF; padding:1px; padding-left:2px; cursor:pointer"><!--<i class="glyphicon glyphicon-book" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:1.01em">Parties</span></a></li>
    <li style="background:url(img/glass_calendar.png) left top; width:96px; height:26px" class="pills pill_color navpill"><a href="#notes/<%=case_id %>" class="note" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-th-list" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:1.01em">Notes</span></a></li>
    <li style="background:url(img/glass_calendar.png) left top; width:96px; height:26px" class="pills pill_color navpill"><a href="#documents/<%=case_id %>" class="note" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-upload" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:1.01em">Docs</span></a></li>
    <li style="background:url(img/glass_calendar.png) left top; width:96px; height:26px" class="pills pill_color navpill"><a href="#kalendarlist/<%=case_id %>" class="note" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-calendar" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:0.97em">Kalendar</span></a></li>
    <li style="background:url(img/glass_calendar.png) left top; width:96px; height:26px" class="pills pill_color navpill">
    	<div id="overdue_kase_tasks_indicator" style="position:absolute; z-index:539; left:<?php echo $indicator_left; ?>px; top:5px; font-size:0.75em; background:white; color:black; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px; left:70px" title="Overdue tasks on this Case"></div>
    	<a href="#tasks/<%=case_id %>" class="taskinbox" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-tasks" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:1.01em">Tasks</span></a>
    </li>
    <%= injury_link %>
    <li style="background:url(img/glass_calendar.png) left top; width:96px; height:26px" class="pills pill_color navpill" id="activity_kpanel_holder">
    	<div id="activity_kpanel_holder">
    		<a href="#activity/<%=case_id %>" class="activity" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-dashboard" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:0.97em">Activity</span></a>
        </div>
        <div style="background:url(img/glass_kai.png) left top; width:96px; height:26px; display:none; position:absolute; z-index:3; margin-left:-2px; margin-top:7px" class="pills pill_color navpill" id="kpanel_pill"><a href="#kontrol_panel/<%=case_id %>" class="misc" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-th" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:1.01em">K Panel</span></a></div>
    </li>
    <li id="financial_pill" style="background:url(img/glass_calendar.png) left top; width:96px; height:26px; display:none" class="pills pill_color navpill"><a class="finances source" style="color:#FFFFFF; padding:1px; padding-left:2px; cursor:pointer"><!--<i class="glyphicon glyphicon-usd" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:0.97em">Finances</span></a><</li>
    <li style="background:url(img/glass_calendar.png) left top; width:99px; height:26px" class="pills pill_color navpill">
    	<div id="kase_invoices_indicator" style="position:absolute; z-index:539; top:5px; font-size:0.75em; background:white; color:black; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px; left:73px" title="Outstanding Invoices on this Case"></div>
        <a href="#payments/<%=case_id %>" class="source" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-list-alt" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:0.97em">Books</span></a>
    </li>
    <li style="background:url(img/glass_calendar.png) left top; width:96px; height:26px" class="pills pill_color navpill"><a href="#eams_forms/<%=case_id %>" class="stuff <?php //if ($_SERVER['REMOTE_ADDR'] != "47.153.53.102" || $_SERVER['REMOTE_ADDR'] != "47.181.13.80") { ?><?php //} ?>" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-credit-card" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:0.95em">Legal&nbsp;Forms</span></a></li>
    <li style="background:url(img/glass_calendar.png) left top; width:96px; height:26px" class="pills pill_color navpill"><a href="#letters/<%=case_id %>" class="info" style="color:white; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-file" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:1.01em">Letters</span></a></li>
	<li style="background:url(img/glass_calendar.png) left top; width:96px; height:26px" class="pills pill_color navpill"><a href="#exams/<%=case_id %>" class="info" style="color:#FFFFFF; padding:1px; padding-left:5px;"><!--<i class="glyphicon glyphicon-heart" style="font-size:0.97em">&nbsp;</i>--><span style="font-size:0.97em">Med Index</span></a></li>
	
    
    </ul>
    <div style="height:10px"></div>
    </div>
    <div id="kase_header" class="span12"></div>
    <div style="height:10px"></div>
    <div id="kase_loading" class="span12"></div>
    <div id="kase_content" class="span12"></div>
</div>