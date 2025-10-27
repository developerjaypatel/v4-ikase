<?php
include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

if($_SERVER['SERVER_NAME']=="starlinkcms.com")
{
  $domain = "starlinkcms.com";
  $application = "StarLinkCMS";
  
}
else
{
  $domain = "ikase.org";
  $application = "iKase";
}

$blnIPad = isPad();

?>
<div id="welcome_to_ikase" style="text-align:center; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;border:#FFFFFF solid 1px; font-size:2em; margin-bottom:10px" class="white_text">
<%= customer_name %> :: Welcome to <?php echo $application; ?>
</div>
<div id="ikase_announcements" style="position: absolute; top:0; bottom: 0; left: 0; right: 0; margin: auto;z-index:9999; background:url(img/glass_calendar.png) left top; padding:10px; width:800px; height:400px; text-align:left; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;border:#FFFFFF solid 1px; overflow-y:scroll;" class="white_text">
	<div style="float:right">
    	<a id="got_it" style="cursor:pointer; font-size:1.6em" class="white_text" title="Click here to close Announcements">&times;</a>
    </div>
    <div style="width:100%; font-size:1.6em">
    	System Announcements
    </div>
    <hr />
    <div style="width:100%; border-bottom:1px solid #CCC; padding-bottom:5px; padding-top:5px">
	    Batchscan functionality is ready. Please go to Documents|Batchscan.<br />Please click here for the <a href="D:/uploads/ikase_separator.pdf" target="_blank" title="Click here to print the Separator Sheet" class="white_text" style="text-decoration:underline"><span style="font-weight:bold; color:yellow"><?php echo $application; ?> Separator Sheet</span></a>
    </div>
    <div style="width:100%;  border-bottom:1px solid #CCC; padding-bottom:5px; padding-top:5px">
    As of 7/21/2016, <?php echo $application; ?> provides <span style="font-weight:bold; color:yellow">Personal Injury functionality</span>.  
    <br />
    Please send any issue to Support so it can be addressed right away.
    </div>
    <div style=" border-bottom:1px solid #CCC; padding-bottom:5px; padding-top:5px">
            As of 8/4/2016, <span style="font-weight:bold; color:yellow"><?php echo $application; ?> Email Integration</span> is ready.  You will now be able to attach emails to cases along with attachments.   <br />
            <span style="background:white; color:black">We strongly recommend you use Gmail, due to its <a href="http://www.informationweek.com/software/information-management/google-blocks-spam-using-ai-tools/d/d-id/1321255" target="_blank">superior security functionality.</a></span>
            <br /><br />
            1) If you have a Gmail account, please click on Tools|Email Settings, and enter your email address and Activate your email.
            <br />
            2) If you host your email, please contact support with your host settings, so that we can establish your host's requirements for incoming email.
   </div>
   <div style=" border-bottom:1px solid #CCC; padding-bottom:5px; padding-top:5px">
   		For support, please email us at support@<?php echo $domain; ?>
   </div>
</div>
<div class="active fade in glass_header_no_padding" style="border:0px solid blue;">
	<!--
    <div style="text-align:left; margin-top:13px;">
         <div style="z-index:2356">
	        <span class="alert alert-success" style="display:none; float:right; height:35px; width:300px;font-size:14px; z-index:3356; margin-top:-45px;">Saved</span>
        </div>
	</div>
    -->
    <div style="border:0px solid purple">
        <div class="container" style="width:100%; border:0px solid green; margin:0px; padding:0px">
            <div class="dashboard_home col-md-6" id="row_1_col_1" style="margin-top:10px; border:0px solid yellow; overflow-y:auto;overflow-x:hidden;  height:300px; width:49.5%">
            </div>
            <!--<div class="dashboard_home col-md-1" style="border:0px solid pink"></div>-->
            <div class="dashboard_home col-md-6 span_class" id="row_1_col_2" style="margin-top:10px; overflow-y:auto; overflow-x:hidden; height:300px; width:50%; margin-left:auto; margin-right:auto;  border-left:1px solid white; float: right; ">
				<a title="Click to view assigned tasks" id="show_assigned" style="cursor:pointer" href="#taskoutbox"><i class="glyphicon glyphicon-tasks" style="color:#66FF33">&nbsp;</i></a>
            </div>
        </div>
        <div class="container" style="border-top:1px solid white; width:100%; margin:0px; padding:0px">
            <div class="dashboard_home col-md-6" id="row_2_col_1" style="margin-top:10px; border:0px solid white; overflow-y:auto;overflow-x:hidden;  height:500px;; width:49.5%">

            </div>
            <div class="dashboard_home col-md-6" id="row_2_col_2" style="margin-top:10px; overflow-y:auto; overflow-x:hidden; height:500px; width:50%; margin-left:auto; margin-right:auto;  border-left:1px solid white; float: right;">

            </div>
        </div>
    </div>
    <div style="float:right">
    	<div class="small_text" style="margin-top:100px">Version NEO 1.0</div>
    </div>
</div>
<div>&nbsp;</div>
<div id="home_view_all_done"></div>
<script language="javascript">
$( "#home_view_all_done" ).trigger( "click" );
</script>