<?php 
require_once('../shared/legacy_session.php');
session_write_close();

if (strpos($_SESSION['user_role'], "admin") === false) {
	die("<div class='white_text' style='text-align:center;padding-top:10px'>You lack permissions to be here, don't you?</div>");
}

include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

//include("../api/connection.php");

$result = DB::runOrDie("SELECT * FROM `ikase`.`cse_job` ORDER BY blurb");
$job_options = "<option value=''>Select from List</option>";
while ($row = $result->fetch()) {
	/*
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
<div style="background:url(img/glass_dark.png) left top repeat; padding:5px; width:1200px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
<% var job_options = "<?php echo $job_options; ?>"; %>

<div class="user" id="user_panel" style="position:relative">
    <form id="user_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="user" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
    <input id="user_id" name="user_id" type="hidden" value="<%= user_id %>" />
    <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
		<?php 
        $form_name = "user"; 
        include("dashboard_view_navigation.php"); 
        ?>
    </div>
    <div style="position:absolute; top: 5px; left:100px">
    	<button class="btn btn-primary btn-sm" id="activity_summary">Activity & Usage Summary</button>
        <?php if ($_SESSION['user_role']=="admin" || $_SESSION['user_role']=="masteradmin") { ?>
         &nbsp;
         <div style="display:none; position:absolute; left:450px; top: 450px" id="delete_cases_holder">
             <div style="width:650px">
    	         <button class="btn btn-danger btn-sm delete_cases" id="delete_attorney_cases">Delete ATTY Cases</button>
                 &nbsp;
                 <button class="btn btn-danger btn-sm delete_cases" id="delete_supervising_attorney_cases">Delete SATTY Cases</button>
                 &nbsp;
                 <button class="btn btn-danger btn-sm delete_cases" id="delete_worker_cases">Delete COORD Cases</button>
                 &nbsp;
                 <button class="btn btn-danger btn-sm delete_cases" id="delete_all_cases">Delete ALL Cases</button>
             </div>
             <div>
	             <span id="delete_attorney_cases_feedback" class="white_text" style="font-size:0.8em"></span>
                 <span id="delete_supervising_attorney_cases_feedback" class="white_text" style="font-size:0.8em"></span>
                 <span id="delete_worker_cases_feedback" class="white_text" style="font-size:0.8em"></span>
                 <span id="delete_all_cases_feedback" class="white_text" style="font-size:0.8em"></span>
             </div>
         </div>
		<?php } ?> 
    </div>
    <div class="gridster_details" id="gridster_<?php echo $form_name; ?>_details" style="position:absolute; left:490px; margin-top:-31px">
    	<div style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px; margin-bottom:5px">Permissions</div>
        
        <!--IF YOU ADD PERMISSIONS HERE, UPDATE LINE 982 and 1123 in users_pack.php-->
        
    	<ul style="margin-bottom:10px">
        	<li id="anytimeGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Anytime Access</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="anytimeSave">
                <a class="save_field" title="Click to save this field" id="anytimeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="margin-top:-28px; width:215px; margin-left:140px">
              <input name="anytimeInput" id="anytimeInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" type="checkbox" value="Y"  />
              <span id="anytimeSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-left:55px; width:20px; height:20px"></span>
            </div>
            </li>
            
            <li id="courtcalendarGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:5px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Assigned Court Calendar</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="courtcalendarSave">
                    <a class="save_field" title="Click to save this field" id="courtcalendarSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <div style="margin-top:-28px; width:215px; margin-left:140px">
                      <input name="courtcalendarInput" id="courtcalendarInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" type="checkbox" value="Y" />
                      <span id="courtcalendarSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-left:55px; width:20px; height:20px"></span>
                </div>
            </li>
            <li id="employee_reportsGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:5px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Employee Reports</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="employee_reportsSave">
                    <a class="save_field" title="Click to save this field" id="employee_reportsSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <div style="margin-top:-28px; width:215px; margin-left:140px">
                      <input name="employee_reportsInput" id="employee_reportsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" type="checkbox" value="Y" />
                      <span id="employee_reportsSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-left:55px; width:20px; height:20px"></span>
                </div>
            </li>
            <li id="employee_reports_blockGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:5px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Block Employee Reports</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="employee_reports_blockSave">
                    <a class="save_field" title="Click to save this field" id="employee_reports_blockSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <div style="margin-top:-28px; width:215px; margin-left:140px">
                      <input name="employee_reports_blockInput" id="employee_reports_blockInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" type="checkbox" value="Y" />
                      <span id="employee_reports_blockSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-left:55px; width:20px; height:20px"></span>
                </div>
            </li>
            <li class="white_text" style="
    margin-top: 25px;
">
				Accounts
            </li>
            <li id="access_accountsGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:5px">
            <h6><div class="form_label_vert" style="margin-top:10px;">Access Bank Accounts</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="checkrequestSave">
                <a class="save_field" title="Click to save this field" id="access_accountsSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="margin-top:-28px; width:215px; margin-left:140px">
              <input name="access_accountsInput" id="access_accountsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" type="checkbox" value="Y" />
              <span id="access_accountsSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-left:55px; width:20px; height:20px"></span>
            </div>
            </li> 
            <li class="white_text" style="
    margin-top: 25px;
">
				Check Requests
            </li>
            <li id="checkrequestGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:5px">
            <h6><div class="form_label_vert" style="margin-top:10px;">Approve Check Request</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="checkrequestSave">
                <a class="save_field" title="Click to save this field" id="checkrequestSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="margin-top:-28px; width:215px; margin-left:140px">
              <input name="checkrequestInput" id="checkrequestInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" type="checkbox" value="Y" />
              <span id="checkrequestSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-left:55px; width:20px; height:20px"></span>
            </div>
            </li> 
            <li id="checkrequest_askGrid" data-row="5" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:5px">
            <h6><div class="form_label_vert" style="margin-top:10px;">Request Checks</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="checkrequest_askSave">
                <a class="save_field" title="Click to save this field" id="checkrequest_askSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="margin-top:-28px; width:215px; margin-left:140px">
              <input name="checkrequest_askInput" id="checkrequest_askInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" type="checkbox" value="Y" />
              <span id="checkrequest_askSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-left:55px; width:20px; height:20px"></span>
            </div>
            </li>
             <li id="checkrequest_settlementGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:5px">
            <h6><div class="form_label_vert" style="margin-top:10px;">Request Settlement Checks</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="checkrequest_settlementSave">
                <a class="save_field" title="Click to save this field" id="checkrequest_settlementSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="margin-top:-28px; width:215px; margin-left:140px">
              <input name="checkrequest_settlementInput" id="checkrequest_settlementInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" type="checkbox" value="Y" />
              <span id="checkrequest_settlementSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-left:55px; width:20px; height:20px"></span>
            </div>
            </li> 
        </ul>
    </div>
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="display:none">
    
        <ul style="margin-bottom:10px">
            <li id="user_nameGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Name</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_nameSave">
                <a class="save_field" title="Click to save this field" id="user_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= user_name %>" name="user_nameInput" id="user_nameInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:55px; width:385px" parsley-error-message="Req" required />
              <span id="user_nameSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= user_name %></span>
        </li>
        
        <li id="nicknameGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Nickname</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="nicknameSave">
                <a class="save_field" title="Click to save this field" id="nicknameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= nickname %>" name="nicknameInput" id="nicknameInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Nickname" style="margin-top:-28px; margin-left:55px" parsley-error-message="Req" required />
              <span id="nicknameSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= nickname %></span>
        </li>
		<li id="statusGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Status</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="statusSave">
                <a class="save_field" title="Click to save this field" id="statusSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= status %>" name="statusInput" id="statusInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Status" style="margin-top:-28px; margin-left:55px" />
              <span id="statusSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= status %></span>
        </li>
		
        <li id="jobGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
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
        
        
        <li id="user_typeGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Role</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="user_typeSave">
            <a class="save_field" title="Click to save this field" id="user_typeSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <div class="input_class hidden" style="margin-left:30px; border:#0000FF 0px solid">
                <div style="border:#FFFFFF 0px solid; margin-top:-23px">
                <input value="1" name="user_typeInput" id="user_typeInput_1" class="<?php echo $form_name; ?> input_class hidden" type="radio" parsley-error-message="" style="margin-top:0px; margin-left:7px" <% if (user_type==1) { %>checked<% } %> /><div style="border:#FF0000 0px solid; margin-top:0px; margin-left:60px">Admin</div>
                </div>
                <div style="height:20px;"></div>
                <div style="border:#FFFFFF 0px solid; margin-top:-23px">
                <input value="2" name="user_typeInput" id="user_typeInput_2" class="<?php echo $form_name; ?> input_class hidden" type="radio" parsley-error-message="" style="margin-top:0px; margin-left:7px" <% if (user_type==2) { %>checked<% } %> /><div style="border:#FF0000 0px solid; margin-top:0px; margin-left:60px">User</div>
                </div>
          </div>
          <span id="user_typeSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= role %></span>
        </li>
        <li id="rateGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Rate</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="rateSave">
                <a class="save_field" title="Click to save this field" id="rateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	<!-- value="<%= job %>" -->
               <input value="<%= rate %>" name="rateInput" id="rateInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:55px" /> 
              <span id="rateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= rate %></span>
        </li>
        
        
        <li id="taxGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Tax</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="taxSave">
            <a class="save_field" title="Click to save this field" id="taxSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="<%= tax %>" name="taxInput" id="taxInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:55px" />
          <span id="taxSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= tax %></span>
        </li>
		<li id="user_logonGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Logon</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_logonSave">
                <a class="save_field" title="Click to save this field" id="user_logonSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= user_logon %>" name="user_logonInput" id="user_logonInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:55px; width:385px" parsley-error-message="Req" required />
              <span id="user_logonSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= user_logon %></span>
        </li>
        <li id="passwordGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Pwd</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="passwordSave">
                <a class="save_field" title="Click to save this field" id="passwordSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="passwordInput" id="passwordInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Enter a password to reset it" style="margin-top:-28px; margin-left:55px; width:385px" />
              <span id="passwordSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px">Enter a password to reset it</span>
        </li>
        <li id="user_emailGrid" data-row="5" data-col="2" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Email</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="user_emailSave">
            <a class="save_field" title="Click to save this field" id="user_emailSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="<%= user_email %>" name="user_emailInput" id="user_emailInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:55px; width:385px" parsley-error-message="Req" />
          <span id="user_emailSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= user_email %></span>
        </li>
        <li id="user_cellGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Cell</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="user_cellSave">
            <a class="save_field" title="Click to save this field" id="user_cellSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input value="<%= user_cell %>" name="user_cellInput" id="user_cellInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-28px; margin-left:55px; width:385px" placeholder='555-555-5555' onkeyup='mask(this, mphone);' onblur='mask(this, mphone)' />
          <span id="user_cellSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= user_cell %></span>
        </li>
        <li id="personal_calendarGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Personal<br />Calendar</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="personal_calendarSave">
            <a class="save_field" title="Click to save this field" id="personal_calendarSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input name="personal_calendarInput" id="personal_calendarInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-35px; width:385px" type="checkbox" value="Y" <% if(personal_calendar=="Y") { %>checked<% } %> />
          <span id="personal_calendarSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-35px; margin-left:205px"><%= personal_calendar %></span>
        </li> 
        <li id="calendar_colorGrid" data-row="8" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Calendar<br />Color</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="calendar_colorSave">
            <a class="save_field" title="Click to save this field" id="calendar_colorSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input name="calendar_colorInput" id="calendar_colorInput" class="color <?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-35px; margin-left:55px;" type="text" value="<%= calendar_color %>" />
          <span id="calendar_colorSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-35px; margin-left:75px; background:<%= calendar_color %>; width:20px; height:20px"></span>
        </li> 
        <li id="activatedGrid" data-row="9" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Active Employee</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="activatedSave">
            <a class="save_field" title="Click to save this field" id="activatedSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
          <input name="activatedInput" id="activatedInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; width:385px" type="checkbox" value="Y" <% if(activated=="Y") { %>checked<% } %> />
          <span id="activatedSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:205px; width:20px; height:20px"><%= activated %></span>
        </li>
       </ul>
        <% if (gridster_me) { %>
			<a href="#users/<%= user_id %>"><img src="img/glass_add.png" width="20" height="20" border="0" /></a>
        <% } %>
    </div>
    
    </form>
</div></div>
<div id="user_view_done"></div>
<script language="javascript">
$( "#user_view_done" ).trigger( "click" );
</script>
<% if (gridster_me) { %>
<script language="javascript">
setTimeout("gridsterIt(8)", 10);
setTimeout("gridsterIt(81)", 20);
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
