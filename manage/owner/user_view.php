<?php 
include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$result = DB::runOrDie("SELECT * FROM `cse_job` ORDER BY blurb");
$job_options = "<option value=''>Select from List</option>";
while ($row = $result->fetch()) {
	/*
	$job_id = $row->job_id;
	$job_uuid = uniqid("KS");
	$queryins = "UPDATE `cse_job` 
	SET job_uuid = '" . $job_uuid . "'
	WHERE job_id = " . $job_id;
	$resultins = DB::runOrDie($queryins);
    continue;
	*/
    $job_options .= "<option value='{$row->job_uuid}'>{$row->job}</option>";
}
?>
<!--
<div id="user_nav" class="span12">
    <ul class="nav nav-pills pill_color">
    	<li style="background:url(img/glass_info.png) left top" class="pills pill_color"><a href="#users/<%=id %>" class="misc" style="color:#FFFFFF; padding:1px; padding-left:5px;"><i class="glyphicon glyphicon-book">&nbsp;</i>Main</a></li>
        <li style="background:url(img/glass_kai.png) left top" class="pills pill_color"><a href="#users/email/<%=id %>" class="misc" style="color:#FFFFFF; padding:1px; padding-left:5px;"><i class="glyphicon glyphicon-book">&nbsp;</i>Email Setup</a></li>
        <li style="background:url(img/glass_situation.png) left top" class="pills pill_color"><a href="#users/schedule/<%=id %>" class="misc" style="color:#FFFFFF; padding:1px; padding-left:5px;"><i class="glyphicon glyphicon-book">&nbsp;</i>Work Hours</a></li>
        <li style="background:url(img/glass_misc.png) left top" class="pills pill_color"><a href="#users/signature/<%=id %>" class="misc" style="color:#FFFFFF; padding:1px; padding-left:5px;"><i class="glyphicon glyphicon-book">&nbsp;</i>Identification</a></li>
	</ul>
</div>
-->
<div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:500px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
<% var job_options = "<? echo $job_options; ?>"; %>

<div class="user" id="user_panel">
    <form id="user_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="user" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
    <input id="user_id" name="user_id" type="hidden" value="<%= user_id %>" />
    <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
		<?php 
        $form_name = "user"; 
        include("templates/dashboard_view_navigation.php"); 
        ?>
    </div>
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="display:none">
    
        <ul style="margin-bottom:10px">
            <li id="user_nameGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(templates/img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Name</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_nameSave">
                <a class="save_field" title="Click to save this field" id="user_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= user_name %>" name="user_nameInput" id="user_nameInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:55px" parsley-error-message="Req" required />
              <span id="user_nameSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= user_name %></span>
        </li>
        
        <li id="nicknameGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(templates/img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Nickname</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="nicknameSave">
                <a class="save_field" title="Click to save this field" id="nicknameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= nickname %>" name="nicknameInput" id="nicknameInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Nickname" style="margin-top:-28px; margin-left:55px" parsley-error-message="Req" required />
              <span id="nicknameSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= nickname %></span>
        </li>
        <li id="jobGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(templates/img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Job</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="jobSave">
                <a class="save_field" title="Click to save this field" id="jobSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	<!-- value="<%= job %>" -->
                
              <select name="jobInput" id="jobInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-28px; margin-left:55px; width:150px" parsley-error-message="Req" required >
              <% var select_options = job_options;
              select_options = select_options.replace("value='" + job_uuid + "'",  "value='" + job_uuid + "' selected");
              %>
              <%= select_options %>
              </select>
              <span id="jobSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= job %></span>
        </li>
        <li id="user_logonGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(templates/img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Logon</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_logonSave">
                <a class="save_field" title="Click to save this field" id="user_logonSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= user_logon %>" name="user_logonInput" id="user_logonInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:55px" parsley-error-message="Req" required />
              <span id="user_logonSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= user_logon %></span>
        </li>
        <li id="user_typeGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(templates/img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Role</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="user_typeSave">
            <a class="save_field" title="Click to save this field" id="user_typeSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <div class="input_class hidden" style="margin-left:30px; border:#0000FF 0px solid">
                <div style="border:#FFFFFF 0px solid; margin-top:-28px">
                <input value="1" name="user_typeInput" id="user_typeInput_1" class="<?php echo $form_name; ?> input_class hidden" type="radio" parsley-error-message="" style="margin-top:0px; margin-left:7px" <% if (user_type==1) { %>checked<% } %> /><div style="border:#FF0000 0px solid; margin-top:0px; margin-left:60px">Admin</div>
                </div>
                <div style="height:20px;"></div>
                <div style="border:#FFFFFF 0px solid; margin-top:-23px">
                <input value="2" name="user_typeInput" id="user_typeInput_2" class="<?php echo $form_name; ?> input_class hidden" type="radio" parsley-error-message="" style="margin-top:0px; margin-left:7px" <% if (user_type==2) { %>checked<% } %> /><div style="border:#FF0000 0px solid; margin-top:0px; margin-left:60px">User</div>
                </div>
          </div>
          <span id="user_typeSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= role %></span>
        </li>
        <li id="statusGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(templates/img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Status</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="statusSave">
                <a class="save_field" title="Click to save this field" id="statusSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= status %>" name="statusInput" id="statusInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Status" style="margin-top:-28px; margin-left:55px" />
              <span id="statusSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= status %></span>
        </li>
        <li id="passwordGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(templates/img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Pwd</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="passwordSave">
                <a class="save_field" title="Click to save this field" id="passwordSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="passwordInput" id="passwordInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Enter a password to reset it" style="margin-top:-28px; margin-left:55px; width:385px" />
              <span id="passwordSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px">Enter a password to reset it</span>
        </li>
        <li id="user_emailGrid" data-row="5" data-col="2" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(templates/img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Email</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="user_emailSave">
            <a class="save_field" title="Click to save this field" id="user_emailSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="<%= user_email %>" name="user_emailInput" id="user_emailInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:55px; width:385px" parsley-error-message="Req" required />
          <span id="user_emailSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= user_email %></span>
        </li>
        <!--
        <li id="authorized_timesGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Autho. Times</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="authorized_timesSave">
            <a class="save_field" title="Click to save this field" id="authorized_timesSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          
                    <input name="authorized_timesInput" type="text" class="form_field <?php echo $form_name; ?> input_class hidden" id="authorized_timesInput" style="width:56px" onFocus="collapseIt();showDropDown('authorized_timesInput')" value="<?php echo $start_time; ?>" tabindex="4">
                <div id="bContainer_start_time" style="display:none" class="drop_down_container">
                    <select name="time_select_start" id="time_select_start" onchange="assignTime(this, 'authorized_timesInput')">
                      <option value="">Select Time</option>
                      <option value="06:00">6:00AM</option>
                      <option value="06:30">6:30AM</option>
                      <option value="07:00">7:00AM</option>
                      <option value="07:30">7:30AM</option>
                      <option value="08:00">8:00AM</option>
                      <option value="08:30">8:30AM</option>
                      <option value="09:00">9:00AM</option>
                      <option value="09:30">9:30AM</option>
                      <option value="10:00">10:00AM</option>
                      <option value="10:30">10:30AM</option>
                      <option value="11:00">11:00AM</option>
                      <option value="11:30">11:30AM</option>
                      <option value="12:00">12:00PM</option>
                      <option value="12:30">12:30PM</option>
                      <option value="13:00">01:00PM</option>
                      <option value="13:30">01:30PM</option>
                      <option value="14:00">02:00PM</option>
                      <option value="14:30">02:30PM</option>
                      <option value="15:00">03:00PM</option>
                      <option value="15:30">03:30PM</option>
                      <option value="16:00">04:00PM</option>
                      <option value="16:30">04:30PM</option>
                      <option value="17:00">05:00PM</option>
                      <option value="17:30">05:30PM</option>
                      <option value="18:00">06:00PM</option>
                      <option value="18:30">06:30PM</option>
                      <option value="19:00">07:00PM</option>
                      <option value="19:30">07:30PM</option>
                      <option value="20:00">08:00PM</option>
                    </select>
          <span id="authorized_timesSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= user_email %></span>
        </li>
        <li id="throughGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Through</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="throughSave">
            <a class="save_field" title="Click to save this field" id="throughSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input name="throughInput" type="text" class="form_field <?php echo $form_name; ?> span_class form_span_vert" id="throughInput" style="width:56px" onFocus="collapseIt();showDropDown('throughInput')" value="<?php echo $end_time; ?>" tabindex="5">
                <div id="bContainer_end_time" style="display:none" class="drop_down_container">
                    <select name="time_select_end" id="time_select_end" onchange="assignTime(this, 'end_time')">
                      <option value="">Select Time</option>
                      <option value="06:00">6:00AM</option>
                      <option value="06:30">6:30AM</option>
                      <option value="07:00">7:00AM</option>
                      <option value="07:30">7:30AM</option>
                      <option value="08:00">8:00AM</option>
                      <option value="08:30">8:30AM</option>
                      <option value="09:00">9:00AM</option>
                      <option value="09:30">9:30AM</option>
                      <option value="10:00">10:00AM</option>
                      <option value="10:30">10:30AM</option>
                      <option value="11:00">11:00AM</option>
                      <option value="11:30">11:30AM</option>
                      <option value="12:00">12:00PM</option>
                      <option value="12:30">12:30PM</option>
                      <option value="13:00">01:00PM</option>
                      <option value="13:30">01:30PM</option>
                      <option value="14:00">02:00PM</option>
                      <option value="14:30">02:30PM</option>
                      <option value="15:00">03:00PM</option>
                      <option value="15:30">03:30PM</option>
                      <option value="16:00">04:00PM</option>
                      <option value="16:30">04:30PM</option>
                      <option value="17:00">05:00PM</option>
                      <option value="17:30">05:30PM</option>
                      <option value="18:00">06:00PM</option>
                      <option value="18:30">06:30PM</option>
                      <option value="19:00">07:00PM</option>
                      <option value="19:30">07:30PM</option>
                      <option value="20:00">08:00PM</option>
                    </select>
          <span id="throughSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= user_email %></span>
        </li>
        -->   
       </ul>
        <% if (gridster_me) { %>
			<a href="#users/<%= user_id %>"><img src="templates/img/glass_add.png" width="20" height="20" border="0" /></a>
        <% } %>
    </div>
    
    </form>
</div></div>
<% if (gridster_me) { %>
<script language="javascript">
setTimeout("gridsterIt(8)", 10);

var assignTime = function(obj, destination) {

	var the_time = document.getElementById(destination);
	var the_value = obj.value;
	var arrTime = the_value.split(":");
	var the_hour = arrTime[0];
	var the_minute = arrTime[1];
	var the_ampm = "AM";
	if (the_hour>12) {
		the_hour = the_hour - 12;
		the_ampm = "PM";
	}
	if (the_hour==12) {
		the_ampm = "PM";
	}
	
	the_time.value = the_hour + ":" + the_minute + the_ampm;
	collapseIt(destination);
}
var showDropDown = function(destination) {
	$("#bContainer_" + destination).css("display", "");
	$("#" + destination + "_holder").css("height", "45px");
	var oRange;
	var the_destination = document.getElementById(destination);
	var the_value = the_destination.value;
	
	oRange = the_destination.createTextRange();
	oRange.moveStart("character", 0);
	oRange.moveEnd("character", the_value.length);
	oRange.select();
	the_destination.focus();
	return;
}
var collapseIt = function(destination) {
	var elements =  document.getElementsByClassName('drop_down_holder');
	/*
	Dom.setStyle(elements, "height", "30px");
	var elements =  Dom.getElementsByClassName('drop_down_container', 'div');
	Dom.setStyle(elements, "display", "none");
	*/
}

</script>
<% } %>
