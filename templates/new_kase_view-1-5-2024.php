<?php 
require_once('../shared/legacy_session.php');
session_write_close();

include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$list_reference = "";
if ($customer_id == "1072") {
  $list_reference = "`ikase`.";
}
$query_case_type = "SELECT DISTINCT case_type `case_type` 
FROM " . $list_reference . "cse_case
WHERE case_type != ''
AND case_type != 'WCAB'
ORDER BY case_type ASC";
$result_case_type = DB::runOrDie($query_case_type);
$case_type_options = "<option value=''>Select from List</option><option value='WCAB'>WCAB</option><option value='NewPI'>DUI</option>";
$arrCaseTypes = array(0=>"wcab", 1=>"wc");
while ($row = $result_case_type->fetch()) {
  $case_type = $row->case_type;
  if (in_array(strtolower($case_type), $arrCaseTypes)) {
    continue;
  } 
  $arrCaseTypes[] = strtolower($case_type);
  
  //skip personal injury, using newpi
  if ($case_type=="Personal Injury") {
    continue;
  }
  //no ready
  if ($case_type=="employment_law") {
    //continue;
  }
  $display_type = str_replace("_", " ", $case_type);
  if ($display_type=="NewPI") {
    $display_type = "personal injury";
  }
  $display_type = ucwords($display_type);
  if (substr($display_type, 0, 2) == "Wc") {
    $display_type = strtoupper($display_type);
  }
  
  $option = "<option value='" . $case_type . "'>" . $display_type . "</option>";
  $case_type_options .= "" . $option;
}
//if ($numbs_case_type==0) {
if (!in_array("wcab", $arrCaseTypes)){
  $option = "<option value='WCAB' selected>WCAB</option>";
  $case_type_options .= "" . $option;
}
if (!in_array("newpi", $arrCaseTypes)){
  $option = "<option value='NewPI'>Personal Injury</option>";
  $case_type_options .= "" . $option;
}
if (!in_array("class_action", $arrCaseTypes)){
  $option = "<option value='class_action'>Class Action</option>";
  $case_type_options .= "" . $option;
}
if (!in_array("civil", $arrCaseTypes)){
  $option = "<option value='civil'>Civil</option>";
  $case_type_options .= "" . $option;
}
if (!in_array("employment_law", $arrCaseTypes)){
  $option = "<option value='employment_law'>Employment</option>";
  $case_type_options .= "" . $option;
}
if (!in_array("immigration", $arrCaseTypes)){
  $option = "<option value='immigration'>Immigration</option>";
  $case_type_options .= "" . $option;
}
if (!in_array("wcab_defense", $arrCaseTypes)){
  $option = "<option value='WCAB_Defense'>WCAB Defense</option>";
  $case_type_options .= "" . $option;
}
if (!in_array("social_security", $arrCaseTypes)){
  $option = "<option value='social_security'>Social Security</option>";
  $case_type_options .= "" . $option;
}

//special case for cardio, maybe move to all doctor offices
if ($_SESSION["user_customer_type"]=="Medical Office") {
  $case_type_options = "<option value=''>Select from List</option><option value='WCAB' selected>WCAB</option>";
  $case_type_options .= "<option value='CALPERS'>CALPERS</option><option value='OCERA'>OCERA</option><option value='Sx CLEARANCE'>Sx CLEARANCE</option><option value='PQME'>PQME</option><option value='AME'>AME</option><option value='IME'>IME</option><option value='LACERA'>LACERA</option><option value='SBCERA'>SBCERA</option><option value='DEPO'>DEPO</option><option value='RECORD REVIEW'>RECORD REVIEW</option><option value='OTHER'>OTHER</option>";
}

$result = DB::runOrDie("SELECT * FROM `ikase`.`cse_venue` where deleted!=1 ORDER BY venue");
$venue_options = "<option value=''>Select from List</option>";
while ($row = $result->fetch()) {
    $venue_options .= "" ."<option value='" .$row->venue_uuid. "'>" .$row->venue_abbr. "</option>";
}
$query_cus = 'SELECT data_source FROM ikase.cse_customer WHERE customer_id = ?';
$data_source = DB::run($query_cus, [$_SESSION["user_customer_id"]])->fetchColumn();
/*
if ($_SESSION["user_customer_id"]=="1070") {
  $data_source = "_leyva";
}
if ($_SESSION["user_customer_id"]=="1075") {
  $data_source = "_dordulian3";
}
*/
$db_name = "ikase";
if ($data_source!="") {
  $db_name .= "_" . $data_source;
}
$order_by = "ORDER BY casestatus";
if ($_SESSION["user_customer_id"]=="1070" || $_SESSION["user_customer_id"]=="1075") {
  $order_by = "ORDER BY casestatus_id";
}
$query_status = "SELECT casestatus_id,  casestatus_uuid, casestatus, law, deleted
  FROM `" . $db_name . "`.cse_casestatus cstat
  WHERE 1
  AND deleted = 'N'
  " . $order_by;

if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
  //echo $query;
}
$result = DB::runOrDie($query_status);
if ($result->rowCount() ==0) {
  //fill it from ikase
    DB::runOrDie("INSERT INTO `" . $db_name . "`.cse_casestatus SELECT * FROM ikase.cse_casestatus");
    //requery
  $result = DB::runOrDie($query_status);
}
$casestatus_options = "<option value='' class='defaultselected'>Select from List</option>";
// $option_intake = "<option value='Intake'>Intake</option>";
// $casestatus_options .= "" . $option_intake;

$arrDeletedKaseStatus = array();
while ($row = $result->fetch()) {
  $casestatus_id = $row->casestatus_id;
  $casestatus_uuid = $row->casestatus_uuid;
  $casestatus = $row->casestatus;
  $law = $row->law;
  $deleted = $row->deleted;
  
  $option = "<option value='" . str_replace("'", "`", $casestatus) . "' class='" . $law . "_status_option'>" . $casestatus . "</option>";
  $casestatus_options .= "" . $option;
  if ($deleted=="Y") {
    $arrDeletedKaseStatus[] = $casestatus;
  }
}

// $option_intake = "<option value='REJECTED'>Rejected</option>";
// $casestatus_options .= "" . $option_intake;

$option_sub = "<option value='' class='defaultselected'>Select from List</option>";
$casesubstatus_options = "" . $option_sub;

$order_by = "ORDER BY casesubstatus";
if ($_SESSION["user_customer_id"]=="1070") {
  $order_by = "ORDER BY abbr";
}

$option_sub = "<option value='' class='defaultselected'>Select from List</option>";
$casesubstatus_options = "" . $option_sub;

$order_by = "ORDER BY casesubstatus";
if ($_SESSION["user_customer_id"]=="1070") {
  $order_by = "ORDER BY abbr";
}

$query_sub = "SELECT * 
  FROM `" . $db_name . "`.cse_casesubstatus csubstat
  WHERE 1
  AND deleted = 'N'
  " . $order_by;

$result_sub = DB::runOrDie($query_sub);

while ($row = $result_sub->fetch()) {
  $casesubstatus_id = $row->casesubstatus_id;
  
  $casesubstatus_uuid = $row->casesubstatus_uuid;
  $casesubstatus = $row->casesubstatus;
  $deleted = $row->deleted;
  $law =$row->law;
  if ($_SESSION["user_customer_id"]=="1070") {
    $abbr = $row->abbr . " - ";
  }
    $option_sub = "<option value='" . str_replace("'", "`", $casesubstatus) . "' class='" . $law . "_substatus_option'>" . $abbr . $casesubstatus . "</option>";
    $casesubstatus_options .= "" . $option_sub;
  if ($deleted=="Y") {
      $arrDeletedKaseStatus[] = $casesubstatus;
  }
}

$blnIPad = isPad();
?>


<!-- For SubSubStatus -->
<?php
$option_sub_sub = "<option value='' class='defaultselected'>Select from List</option>";
$casesubsubstatus_options = "" . $option_sub_sub;

$order_by = "ORDER BY casesubsubstatus";
if ($_SESSION["user_customer_id"]=="1070") {
  $order_by = "ORDER BY abbr";
}

$query_sub = "SELECT * 
  FROM `" . $db_name . "`.cse_casesubsubstatus csubsubstat
  WHERE 1
  AND deleted = 'N'
  " . $order_by;

$result_sub_sub = DB::runOrDie($query_sub);

while ($row = $result_sub->fetch()) {
  $casesubsubstatus = $row->casesubsubstatus;
  $deleted = $row->deleted;
  $law =$row->law;
  if ($_SESSION["user_customer_id"]=="1070") {
    $abbr = $row->abbr . " - ";
  }
    $option_sub_sub = "<option value='" . str_replace("'", "`", $casesubsubstatus) . "' class='" . $law . "_subsubstatus_option'>" . $abbr . $casesubsubstatus . "</option>";
    $casesubsubstatus_options .= "" . $option_sub_sub;
  if ($deleted=="Y") {
      $arrDeletedKaseStatus[] = $casesubsubstatus;
  }
}
//die($casestatus_options);
?>

<% var venue_options = "<?php echo $venue_options; ?>"; %>
<% var case_type_options = "<?php echo $case_type_options; ?>"; %>
<% var casestatus_options = "<?php echo $casestatus_options; ?>"; %>
<% var casesubstatus_options = "<?php echo $casesubstatus_options; ?>"; %>
<% var casesubsubstatus_options = "<?php echo $casesubsubstatus_options; ?>"; %>
<% var rating_options = "<option value='A'>A</option><option value='B'>B</option><option value='C'>C</option><option value='D'>D</option><option value='F'>F</option>"; %>

<script type="application/javascript" language="javascript">
var casestatus_options = "<?php echo $casestatus_options; ?>";
var casesubstatus_options = "<?php echo $casesubstatus_options; ?>";
var casesubsubstatus_options = "<?php echo $casesubsubstatus_options; ?>";
arrDeletedKaseStatus = ["<?php echo implode('", "', $arrDeletedKaseStatus); ?>"];
</script>
<div id="side_holder" style="margin-left:10px; float:right; width:375px; border-left:1px white solid; display:none; text-align:left; padding-left:10px">
    <div id="manage_status_holder" style="display:none; height:550px; width:350px; overflow-y:auto"></div>
    <div id="homemedical" style="display:none"></div>
    <div id="special_instructions_holder" style="width:370px;display:none">
        <div style="width:140px; display:inline-block"><strong>Special Instructions:</strong></div>
        <div>
          <textarea id='special_instructions' style="width:325px" rows="8"><%= special_instructions %></textarea>
        </div>
        <div  class="injury_fields">
            <hr />
            <div style="width:140px; display:inline-block"><strong>Suit:</strong></div>
            <div>
                <select name="suit" id="suit">
                  <option value="">Individual or Class?</option>
                    <option  value="Individual">Individual</option>
                    <option  value="Class">Class</option>
                </select>
            </div>
            <div style="width:140px; display:inline-block"><strong>Jurisdiction:</strong></div>
            <div>
                <select name="jurisdiction" id="jurisdiction">
                  <option value="">State or Federal?</option>
                    <option  value="State">State</option>
                    <option  value="Federal">Federal</option>
                </select>
            </div>
            <div style="width:140px; display:inline-block"><strong>Case Note:</strong></div>
            <div>
                <textarea id='case_note' name='case_note' style="width:325px" rows="8"></textarea>
            </div>
        </div>
    </div>
</div>

<div id="intake_holder_top">
    <div id="claim_holder" class="intake_holder" style="margin-left:10px; float:right; width:601px; border-left:1px white solid; display:none; text-align:left; padding-left:10px"></div>
  
    <div id="intake_top_right_holder" class="intake_holder" style="margin-left:10px; float:right; width:501px; border-left:1px white solid; display:none; text-align:left; padding-left:10px; margin-top:50px;"></div>
        
    <div id="intake_top_center_holder" class="intake_holder" style="margin-left:10px; float:right; width:501px; border-left:1px white solid; display:none; text-align:left; padding-left:10px"></div>
    
    <div id="intake_title" style="font-size:2.6em; font-weight:bold; display:none; border-bottom:1px white solid">
      <div style="float:right;font-size: 0.5em;font-weight: normal;" id="phone_intake_feedback_div"></div>
        Phone Intake
        &nbsp;
        <button title="Save Kase" class="kase save_intake btn btn-primary" onClick="saveIntake(event)" style="cursor:pointer" id="intake_save">Save</button>
        <img src="img/loading_spinner_1.gif" width="20" height="20" id="intake_gifsave" class="intake" style="margin-top: -5px; display:none">
        
        <div id="intake_schedule_holder" style="display:none">
            &nbsp;
            &nbsp;
            |
            &nbsp;&nbsp;
            <button class="btn kase intake_buttons" id="intake_event">Schedule Event</button>
            &nbsp;&nbsp;
            <button class="btn kase intake_buttons" id="intake_task">Assign Task</button>
        </div>
        
    </div>
    <div class="kase">
        <form id="kase_form" parsley-validate>
        <input id="id" name="id" type="hidden" value="<%= case_id %>" />
        <input id="table_id" name="table_id" type="hidden" value="<%= case_id %>" />
        <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
        <input value="<%= submittedOn %>" name="submittedonInput" id="submittedonInput" class="hidden" />
        <table width="500px" border="0" align="left" cellpadding="3" cellspacing="0" id="new_kase_field_table">
            <tr class="kase_fields case_name_holder">
                <th align="right" valign="top" nowrap="nowrap" scope="row" id="case_name_label">
                  <span id="actual_case_label">Case:</span>
                </th>
                <td valign="top" style="font-weight:bold">
                    <div style="float:right">
                        <button class="btn btn-sm" id="ssn_intake_button" style="display:none">SSN Intake</button>
                        <button class="btn btn-sm" id="special_instructions_button" style="display:none">Special Instructions</button>
                    </div>
                    <% if (case_id > 0 && name!= null && name!= "") { %>
                    <!--<input type="text" id="case_nameInput" name="case_nameInput" value="<%= name %>" style="width:510px" required />-->
                    <%= name %>
                    <% } %>
                </td>
           </tr>
            <tr>
                <th align="right" valign="top" scope="row">Type:</th>
                <td valign="top">
                    <select name="case_typeInput" id="case_typeInput" class="kase input_class" style="width:510px" parsley-error-message="" required onchange="setCase()">
                          <% var select_type_options = case_type_options;
                          if (case_type=="Personal Injury") {
                            case_type = "NewPI";
                          }
                          select_type_options = select_type_options.replace("value='" + case_type + "'",  "value='" + case_type + "' selected");
                          %>
                          <%= select_type_options %>
                      </select>
                      <span id="case_typeSpan"></span>
                 </td>
            </tr>
            <tr id="kase_injury_description_holder" style="display:none">
              <th align="right" valign="top" scope="row">Injury Description</th>
              <td valign="top" id="kase_injury_description" style="background:white; color:black; font-weight:bold"></td>
            </tr>
            <% if (blnPiReady) { %>
            <tr id="pi_type_row" class="injury_fields">
                <th align="right" valign="top" scope="row">Injury Type:</th>
                <td valign="top">
                    <select name="injury_typeInput" id="injury_typeInput" class="kase input_class" style="width:510px" >
                          <option value="">Choose One</option>
                          <option value="carpass">Car Accident</option>
                          <option value="general" selected="selected">General</option>
                          <option value="slipandfall">Slip and Fall</option>
                          <option value="dogbite">Dog Bite</option>
                          <option id="disability_kase_option" value="disability">Disability</option>
                      </select>        
                </td>
            </tr>
            <tr id="pi_representing_row" class="injury_fields">
                <th align="right" valign="top" scope="row">Representing:</th>
                <td valign="top">
                    <select onchange="setCase()" name="representingInput" id="representingInput" class="kase input_class" style="width:510px" >
                        <option value="">Plaintiff or Defendant?</option>
                        <option id="plaintiff" value="plaintiff">Plaintiff</option>
                        <option id="defendant" value="defendant">Defendant</option>
                      </select> 
                      <span id="representingSpan"></span>       
                </td>
            </tr>
            <% } %>
            <tr id="file_number_row" class="kase_fields">
                <th align="right" valign="top" scope="row">File Number:</th>
                <td valign="top">
                    <% if (case_id > 0) { %>
                    <div style="float:right;margin-top: 2px;margin-left: 150px;">
                        <strong>Case ID:</strong> <span class="span_class form_span_vert"><%= case_id %></span>
                    </div>
                    <% } %>
                    <div style="float:right">
                        <% var case_width = "110px"; 
                            if (case_id > 0) { 
                                case_width = "70px";
                            }
                            %>
                        <span style="font-weight:bold" id="case_number_label">Case Number:</span>
                        <input value="<%= case_number %>" name="case_numberInput" id="case_numberInput" class="kase input_class floatlabel hidden" style="width:<%=case_width %>"  parsley-error-message="" placeholder="Court Legal Number" autocomplete="off" />
                        <span id="case_numberSpan" class="kase span_class form_span_vert" style="display:none"><%= case_number %></span>
                    </div>
                    <input value="<%= file_number %>" name="file_numberInput" id="file_numberInput" class="kase input_class floatlabel" style="width:110px" required placeholder="File Number" autocomplete="off" /> 
                    <span id="file_numberSpan" class="kase span_class form_span_vert" style="display:none"><%= file_number %></span>      
                </td>
            </tr>
            <tr class="kase_fields wcab_only">
                <th width="15%" align="right" valign="top" scope="row">Venue:</th>
                <td valign="top">
                    <select name="venueInput" id="venueInput" class="kase input_class" style="width:510px" >
                      <% var select_options = venue_options;
                      select_options = select_options.replace("value='" + venue_uuid + "'",  "value='" + venue_uuid + "' selected");
                      %>
                      <%= select_options %>
                      
                  </select>           </td>
              </tr>
          <tr style="display:none" class="kase_fields wcab_only">
            <th align="right" valign="top" scope="row" nowrap>ADJ Number:</th>
            <td valign="top">
                <input value="<%= adj_number %>" name="adj_numberInput" id="adj_numberInput" style="width:510px" class="kase input_class floatlabel"  parsley-error-message="" />        </td>
      </tr>
          <tr class="kase_fields">
            <th align="right" valign="top" scope="row" nowrap>Case Date:</th>
            <td width="46%" valign="top">
              <div style="float:right; display:none" id="filing_date_holder">
                  <strong>Filing Date:</strong>&nbsp;&nbsp;
                    <input type="date" id="filing_dateInput" name="filing_dateInput" value="<%=filing_date %>" />
                </div>
              <input value="<%= moment(case_date).format('MM/DD/YYYY') %>" name="case_dateInput" id="case_dateInput" class="kase input_class date_input" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" style="width:510px"  parsley-error-message="" required />        
            </td>
          </tr>  
          <tr class="kase_fields">
            <th align="right" valign="top" scope="row">Status:</th>
            <td valign="top">
                <select name="case_statusInput" id="case_statusInput" class="kase input_class" style="width:450px">
                    <% var status_options = casestatus_options;
                        if (case_id == "" || case_status == "OPEN" || case_status.indexOf("OP ")==0) {
                            case_status = "Open";
                        }
                      status_options = status_options.replace("value='" + case_status.replace("'", "`") + "'",  "value='" + case_status.replace("'", "`") + "' selected");
                      %>
                      <% console.log(status_options); %>
                  <%= casestatus_options %>
                </select>
                &nbsp;<button id="manage_status" class="btn btn-xs btn-primary manage_status hidden">manage</button>
                </td>
          </tr>
          <tr class="kase_fields case_sub_status">
            <th align="right" valign="top" scope="row">Sub Status:</th>
            <td valign="top" id="sub_status_td">
                <select name="case_substatusInput" id="case_substatusInput" class="kase input_class wcab_only_stat" style="width:450px; overflow-y: scroll; display:none">
                    <% var sub_status_options = casesubstatus_options;
                  sub_status_options = sub_status_options.replace("value='" + case_substatus.replace("'", "`") + "'",  "value='" + case_substatus.replace("'", "`") + "' selected");
                  %>
                  <%= sub_status_options %>
                </select>
                &nbsp;<button id="manage_status1" class="btn btn-xs btn-primary manage_status1">manage</button>
            </td>
          </tr>
          <tr class="kase_fields case_sub_sub_status">
            <th align="right" valign="top" scope="row">Sub Status 2:</th>
            <td valign="top" id="sub_sub_status_td">
                <select name="case_subsubstatusInput" id="case_subsubstatusInput" class="kase input_class" style="width:450px; overflow-y: scroll;">
                    <% var sub_sub_status_options = casesubsubstatus_options;
                  sub_sub_status_options = sub_sub_status_options.replace("value='" + case_subsubstatus.replace("'", "`") + "'",  "value='" + case_subsubstatus.replace("'", "`") + "' selected");
                  %>
                  <%= sub_sub_status_options %>
                </select>
                &nbsp;<button id="manage_status2" class="btn btn-xs btn-primary manage_status2">manage</button>
            </td>
          </tr>
          <tr class="kase_fields">
            <th align="right" valign="top" scope="row">Case&nbsp;Rating:</th>
            <td valign="top">
              <select name="ratingInput" id="ratingInput" class="kase input_class" style="width:110px">
                    <% var status_options = rating_options;
                  status_options = status_options.replace("value='" + rating + "'",  "value='" + rating + "' selected");
                  %>
                  <%= status_options %>
                </select>
                <div style="display:inline-block; margin-left:10px">
                    <strong>Sub In:</strong>&nbsp;&nbsp;<input type="checkbox" id="sub_in" name="sub_in" value="Y" <%if (sub_in=="Y") { %>checked="checked"<% } %> />
                </div>
                <div style="display:none; margin-top: 10px; margin-bottom: 5px;" id="sub_dates">
                  <div style="float:right">
                       <strong>Sub Out Date:</strong>&nbsp;&nbsp;
                      <input type="date" id="sub_out_date" name="sub_out_date" value="<%=sub_out_date %>" width="130px" />
                    </div>
                  <strong>Sub In Date:</strong>&nbsp;&nbsp;
                    <input type="date" id="sub_in_date" name="sub_in_date" value="<%=sub_in_date %>" width="130px" />
                </div>
            </td>
          </tr>
          <!--BELOW IS CONFUSING, AND I'M SORRY, TERMS CHANGED.  
          `supervising_attorney` in the database is the main firm attorney.
          `attorney` is the actual "supervising" attorney.  
          
          too much data already to make the change
           -->
          <tr class="kase_fields">
            <th align="right" valign="top" scope="row">Attorney:</th>
            <td valign="top">
              <div id="supervising_attorney_holder">
                    <input autocomplete="off" value="<%= supervising_attorney %>" name="supervising_attorneyInput" style="width:510px" id="supervising_attorneyInput" class="kase input_class" title="This Attorney is the main firm attorney" />
                    <span style="font-size:0.8em; font-style:italic; color:white">This Attorney is the main firm attorney</span>
                </div>
                <span id="supervising_attorneySpan" style="display:none"></span>
            </td>
          </tr>
          <tr class="kase_fields">
            <th align="right" valign="top" scope="row">Supv Atty:</th>
            <td valign="top">
              <div id="attorney_holder">
                    <input autocomplete="off" value="<%= attorney %>" name="attorneyInput" style="width:510px" id="attorneyInput" class="kase input_class" title="This Attorney handles the details of the Kase" />
                    <span style="font-size:0.8em; font-style:italic; color:white">This Attorney will be used to sign letters and forms</span>
                </div>
                <span id="attorneySpan" style="display:none"></span>
             </td>
          </tr>
          
          <tr class="kase_fields">
            <th align="right" valign="top" scope="row">
                <% if (customer_id==1088) { %>
                Paralegal:
                <% } else { %>
                Coordinator:
                <% } %>
            </th>
            <td valign="top">
              <input value="<%= worker %>" name="workerInput" id="workerInput" style="width:510px" class="kase input_class" />
                <span id="workderSpan" style="display:none"></span>
            </td>
          </tr>
          <tr class="kase_fields">
            <th align="right" valign="top" scope="row">Language:</th>
            <td valign="top">
                <select name="case_languageInput" id="case_languageInput" class="kase input_class">
                  <?php include("../api/language_options.php"); ?>
                </select>
                <div style="float:right; width:310px; border:0px solid white">
                    <div style="display:inline-block; width:200px; border:0px solid white">
                    <span style="width:100px">Interpreter&nbsp;&nbsp;<input title="Check this box if an interpreter is needed for this case" type="checkbox" value="Y" id="interpreter_neededInput" name="interpreter_neededInput" style="border:0px solid white; width:13px; height:13px" class="kase input_class" <%if (interpreter_needed=="Y") { %>checked="checked"<% } %> />
                    </span>
                    </div>
                </div>
                
            </td>
          </tr>
          <tr class="kase_fields">
            <th align="right" valign="top" scope="row">&nbsp;</th>
            <td valign="top">&nbsp;</td>
          </tr>
          <tr class="kase_fields">
            <th align="right" valign="top" scope="row">Benefits:</th>
            <td valign="top">
                <table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
                  <tr>
                    <td width="25%" align="left" valign="top">Medical
                      <input value="<%= medical %>" name="medicalInput" style="width:65px" id="medicalInput" class="kase input_class" /></td>
                    <td width="25%" align="left" valign="top" nowrap="nowrap" style="width:10%">TD $
                      <input value="<%= td %>" name="tdInput" style="width:65px" id="tdInput" class="kase input_class" /></td>
                    <td width="25%" align="left" valign="top">Voucher $
                      <input value="<%= rehab %>" name="rehabInput" style="width:65px" id="rehabInput" class="kase input_class" /></td>
                    <td width="25%" align="left" valign="top" nowrap="nowrap" style="width:10%">EDD $
                      <input value="<%= edd %>" name="eddInput" style="width:65px" id="eddInput" class="kase input_class" /></td>
                  </tr>
                </table>
            </td>
          </tr>
          <tr class="kase_fields">
            <th align="right" valign="top" scope="row">Claims:</th>
            <td valign="top">
              <% 
                var third_party_style = "";
                if (customer_id == 1064) { 
                  third_party_style = "background:red; padding:2px";
                }
                %>
                <table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
                  <tr>
                    <td width="20%" align="center" valign="top" nowrap="nowrap" class="left_right_border">
                      <span style="<%=third_party_style %>">
                        Third Party
                        <input type="checkbox" class="claims third_132" name="third_party_claimsInput" id="third_partyInput" value="3P" <% if (claims.indexOf('3P') > -1) { %>checked<% } %> />
                        </span>
                      <div id="third_partyInHouse" class="white_text inhouse_div" style="display:<% if (claims.indexOf('3P') == -1) { %>none<% } %>">
                        In House? <a id="third_partyInHouseY" class="yes_no_link <% if (claims.indexOf('3P~Y') > -1) { %>yes_no_selected<% } %>">Y</a> <a id="third_partyInHouseN" class="yes_no_link <% if (claims.indexOf('3P~Y') == -1) { %>yes_no_selected<% } %>">N</a>
                        <input type="hidden" name="third_partyInHouseChoice" id="third_partyInHouseChoice" value="<% if (claims.indexOf('3P~Y') > -1) { %>Y<% } else { %>N<% } %>" style="display:" />
                      </div>
                    </td>
                    <td width="20%" align="left" valign="top" nowrap="nowrap" class="left_right_border">132a
                    <input type="checkbox" class="claims third_132" name="132a_claimsInput" id="132aInput" value="132a" <% if (claims.indexOf('132a') > -1) { %>checked<% } %> />
                    <div id="132aInHouse" class="white_text inhouse_div" style="display:<% if (claims.indexOf('132a') == -1) { %>none<% } %>">
                        In House? <a id="132aInHouseY" class="yes_no_link <% if (claims.indexOf('132a~Y') > -1) { %>yes_no_selected<% } %>">Y</a> <a id="132aInHouseN" class="yes_no_link <% if (claims.indexOf('132a~Y') == -1) { %>yes_no_selected<% } %>">N</a>
                        <input type="hidden" name="132aInHouseChoice" id="132aInHouseChoice" value="<% if (claims.indexOf('132a~Y') > -1) { %>Y<% } else { %>N<% } %>" style="display:" />
                        <div>
                            Terminated:&nbsp;
                          <input value="<%= terminated_date %>" name="terminated_dateInput" id="terminated_dateInput" class="kase input_class date_input" placeholder="mm/dd/yyyy" style="width:75px" title="Terminated Date" />
                        </div>
                    </div>
                    </td>
                    <td width="20%" align="center" valign="top" nowrap="nowrap" class="left_right_border">Serious and Willful
                    <input type="checkbox" class="claims" name="serious_claimsInput" id="seriousInput" value="SER" <% if (claims.indexOf('SER') > -1) { %>checked<% } %> />
                    <div id="seriousInHouse" class="white_text inhouse_div" style="display:<% if (claims.indexOf('SER') == -1) { %>none<% } %>">
                        In House? <a id="seriousInHouseY" class="yes_no_link <% if (claims.indexOf('SER~Y') > -1) { %>yes_no_selected<% } %>">Y</a> <a id="seriousInHouseN" class="yes_no_link <% if (claims.indexOf('SER~Y') == -1) { %>yes_no_selected<% } %>">N</a>
                        <input type="hidden" name="seriousInHouseChoice" id="seriousInHouseChoice" value="<% if (claims.indexOf('SER~Y') > -1) { %>Y<% } else { %>N<% } %>" style="display:" />
                      </div>
                    </td>
                    <td width="20%" align="center" valign="top" nowrap="nowrap" class="left_right_border">ADA
                      <input type="checkbox" class="claims" name="ada_claimsInput" id="adaInput" value="ADA" <% if (claims.indexOf('ADA') > -1) { %>checked<% } %> />
                        <div id="adaInHouse" class="white_text inhouse_div" style="display:<% if (claims.indexOf('ADA') == -1) { %>none<% } %>">
                        In House? <a id="adaInHouseY" class="yes_no_link <% if (claims.indexOf('ADA~Y') > -1) { %>yes_no_selected<% } %>">Y</a> <a id="adaInHouseN" class="yes_no_link <% if (claims.indexOf('ADA~Y') == -1) { %>yes_no_selected<% } %>">N</a>
                        <input type="hidden" name="adaInHouseChoice" id="adaInHouseChoice" value="<% if (claims.indexOf('ADA~Y') > -1) { %>Y<% } else { %>N<% } %>" style="display:" />
                      </div>
                    </td>
                    <td width="20%" align="center" valign="top" nowrap="nowrap" class="left_right_border">SS
                    <input type="checkbox" class="claims" name="ss_claimsInput" id="ssInput" value="SS" <% if (claims.indexOf('SS') > -1) { %>checked<% } %> />
                    <div id="ssInHouse" class="white_text inhouse_div" style="display:<% if (claims.indexOf('SS') == -1) { %>none<% } %>">
                        In House? <a id="ssInHouseY" class="yes_no_link <% if (claims.indexOf('SS~Y') > -1) { %>yes_no_selected<% } %>">Y</a> <a id="ssInHouseN" class="yes_no_link <% if (claims.indexOf('SS~Y') == -1) { %>yes_no_selected<% } %>">N</a>
                        <input type="hidden" name="ssInHouseChoice" id="ssInHouseChoice" value="<% if (claims.indexOf('SS~Y') > -1) { %>Y<% } else { %>N<% } %>" style="display:" />
                      </div>
                    </td>
                  </tr>
                </table>
            </td>
          </tr>
    </table>
    </form>
    </div>
</div>
<div id="intake_holder_bottom" style="position:absolute; width:100%">
  <div id="intake_bottom_left_holder" class="intake_holder" style="margin-left:10px; width:501px; border-left:1px white solid; display:none; text-align:left; padding-left:10px; margin-top:50px;"></div>
    
    <div id="intake_bottom_right_holder" style="margin-left:10px; float:right; width:501px; border-left:1px white solid; border-top:1px white solid; display:none; text-align:left; padding-left:10px; margin-top:10px;">
      <div id="panel_title" style="font-weight: normal; font-size: 1.25em; margin-top:10px">
          <div style="float:right; font-size:0.8em">
              <input type="checkbox" id="intake_quick" class="intake_notes" value="Y" /> Add as Quick Notes
            </div>
          Notes
        </div>
        <div>
          <input type="text" id="intake_notes_subject" class="intake_notes" value="Phone Intake" placeholder="Subject" style="width:90%;" />
        </div>
        <div style="margin-top:20px">
          <textarea id="intake_notes" style="width:90%; height:150px" class="intake_notes"></textarea>
            <input type="hidden" id="intake_notes_id" value="" />
        </div>
    </div>
    
    <div id="intake_bottom_center_holder" class="intake_holder" style="margin-left:10px; float:right; width:501px; border-left:1px white solid; display:none; text-align:left; padding-left:10px; margin-top:10px; overflow-y:scroll; overflow-x:hidden"></div>
    
</div>
<div id="kase_all_done"></div>
<script language="javascript">
$( "#kase_all_done" ).trigger( "click" );
</script>
<script type="application/javascript" language="javascript">
call_for_remove_drop_value();

async function call_for_remove_drop_value() 
{
  var drop_down_options_list = document.getElementById("case_substatusInput");
  var selected_case_type = $("#case_typeInput :selected").val();
  var class_name_compare= selected_case_type + "_substatus_option";
  class_name_compare = class_name_compare.toLowerCase();
  var drop_len =drop_down_options_list.length;
  // console.log(drop_len);
  // console.log(drop_down_options_list);
  // console.log(class_name_compare);
  for(var i=0;i<=drop_len-1;i++)
  {
    // console.log(i);
    // || (( drop_down_options_list[i].className == "pi_substatus_option" || drop_down_options_list[i].className == "newpi_substatus_option") && (class_name_compare=="newpi_substatus_option" || class_name_compare=="pi_substatus_option"))
    if(!(( drop_down_options_list[i].className.toLowerCase() == "pi_substatus_option" || drop_down_options_list[i].className.toLowerCase() == "newpi_substatus_option") && (class_name_compare.toLowerCase()=="newpi_substatus_option" || class_name_compare.toLowerCase()=="pi_substatus_option")))
    {
      if(drop_down_options_list[i].className.toLowerCase()!=class_name_compare.toLowerCase())
      {
        if(drop_down_options_list[i].className.toLowerCase()!="defaultselected")
        {
          // console.log(drop_down_options_list[i].className.toLowerCase());
          // console.log(drop_down_options_list[i].className.toLowerCase()+'!='+class_name_compare.toLowerCase()+' || (('+ drop_down_options_list[i].className.toLowerCase() +'!= pi_substatus_option ||');
          // console.log(drop_down_options_list[i].className.toLowerCase() +"!= newpi_substatus_option) && (");
          // console.log(class_name_compare.toLowerCase()+'!=newpi_substatus_option || '+class_name_compare.toLowerCase()+'!=pi_substatus_option)))');
          
          
          
        // console.log(drop_down_options_list[i].className+'=='+class_name_compare +' -> False');
        // drop_down_options_list[i].style.display = 'none';
        // $("#case_substatusInput").options[i].hide();
        var x =await $("#case_substatusInput option[class='"+drop_down_options_list[i].className+"']").remove();
        await call_for_remove_drop_value();
        break;
        // i=0;
        // drop_len =drop_down_options_list.length;
        // await sleep(0.500);
        // var x = document.getElementById("case_substatusInput").options[i].disabled = true;
        // var x = document.getElementById("case_substatusInput").options[i].remove(0);
        }
      }
    }
  }
  // console.log(document.getElementById("case_substatusInput").options);
}
</script>
<script type="application/javascript" language="javascript">
call_for_remove_drop_value1();

async function call_for_remove_drop_value1() 
{
  var drop_down_options_list = document.getElementById("case_subsubstatusInput");
  var selected_case_type = $("#case_typeInput :selected").val();
  var class_name_compare= selected_case_type + "_subsubstatus_option";
  class_name_compare = class_name_compare.toLowerCase();
  var drop_len =drop_down_options_list.length;
  // console.log(drop_len);
  // console.log(drop_down_options_list);
  // console.log(class_name_compare);
  for(var i=0;i<=drop_len-1;i++)
  {
    // console.log(i);
    // || (( drop_down_options_list[i].className == "pi_substatus_option" || drop_down_options_list[i].className == "newpi_substatus_option") && (class_name_compare=="newpi_substatus_option" || class_name_compare=="pi_substatus_option"))
    if(!(( drop_down_options_list[i].className.toLowerCase() == "pi_subsubstatus_option" || drop_down_options_list[i].className.toLowerCase() == "newpi_subsubstatus_option") && (class_name_compare.toLowerCase()=="newpi_subsubstatus_option" || class_name_compare.toLowerCase()=="pi_subsubstatus_option")))
    {
      if(drop_down_options_list[i].className.toLowerCase()!=class_name_compare.toLowerCase())
      {
        if(drop_down_options_list[i].className.toLowerCase()!="defaultselected")
        {
          // console.log(drop_down_options_list[i].className.toLowerCase()+'!='+class_name_compare.toLowerCase()+' || (('+ drop_down_options_list[i].className.toLowerCase() +'!= pi_substatus_option ||');
          // console.log(drop_down_options_list[i].className.toLowerCase() +"!= newpi_substatus_option) && (");
          // console.log(class_name_compare.toLowerCase()+'!=newpi_substatus_option || '+class_name_compare.toLowerCase()+'!=pi_substatus_option)))');
          
          
          
        // console.log(drop_down_options_list[i].className+'=='+class_name_compare +' -> False');
        // drop_down_options_list[i].style.display = 'none';
        // $("#case_substatusInput").options[i].hide();
        var x =await $("#case_subsubstatusInput option[class='"+drop_down_options_list[i].className+"']").remove();
        await call_for_remove_drop_value1();
        break;
        // i=0;
        // drop_len =drop_down_options_list.length;
        // await sleep(0.500);
        // var x = document.getElementById("case_substatusInput").options[i].disabled = true;
        // var x = document.getElementById("case_substatusInput").options[i].remove(0);
        }
      }
    }
  }
  // console.log(document.getElementById("case_substatusInput").options);
}
</script>
<script type="application/javascript" language="javascript">
call_for_remove_drop_value0();

async function call_for_remove_drop_value0() 
{
  var drop_down_options_list = document.getElementById("case_statusInput");
  var selected_case_type = $("#case_typeInput :selected").val();
  var class_name_compare = selected_case_type+"_status_option";
  class_name_compare = class_name_compare.toLowerCase();
  var drop_len =drop_down_options_list.length;
  // console.log(drop_len);
  // console.log(drop_down_options_list);
  console.log(class_name_compare);
  for(var i=0;i<=drop_len-1;i++)
  {
    // console.log(i);
    // console.log(drop_down_options_list[i].className);
    
    // || (( drop_down_options_list[i].className == "pi_substatus_option" || drop_down_options_list[i].className == "newpi_substatus_option") && (class_name_compare=="newpi_substatus_option" || class_name_compare=="pi_substatus_option"))
    if(!(( drop_down_options_list[i].className.toLowerCase() == "pi_status_option" || drop_down_options_list[i].className.toLowerCase() == "newpi_status_option") && (class_name_compare.toLowerCase()=="newpi_status_option" || class_name_compare.toLowerCase()=="pi_status_option")))
    {
      if(drop_down_options_list[i].className.toLowerCase()!=class_name_compare.toLowerCase())
      {
        if(drop_down_options_list[i].className.toLowerCase()!="defaultselected")
        {
        //   console.log(drop_down_options_list[i].className.toLowerCase()+'!='+class_name_compare.toLowerCase()+' || (('+ drop_down_options_list[i].className.toLowerCase() +'!= pi_substatus_option ||');
        //   console.log(drop_down_options_list[i].className.toLowerCase() +"!= newpi_substatus_option) && (");
        //   console.log(class_name_compare.toLowerCase()+'!=newpi_substatus_option || '+class_name_compare.toLowerCase()+'!=pi_substatus_option)))');
          
          
          
        // console.log(drop_down_options_list[i].className+'=='+class_name_compare +' -> False');
        // drop_down_options_list[i].style.display = 'none';
        // $("#case_substatusInput").options[i].hide();
        var x =await $("#case_statusInput option[class='"+drop_down_options_list[i].className+"']").remove();
        await call_for_remove_drop_value0();
        break;
        // i=0;
        // drop_len =drop_down_options_list.length;
        // await sleep(0.500);
        // var x = document.getElementById("case_substatusInput").options[i].disabled = true;
        // var x = document.getElementById("case_substatusInput").options[i].remove(0);
        }
      }
    }
  }
  // console.log(document.getElementById("case_substatusInput").options);
}

// for selecting status, sub status and sub status2 in drop down
 $("#case_statusInput").val("<%= case_status %>");
 $("#case_substatusInput").val("<%= case_substatus %>");
 $("#case_subsubstatusInput").val("<%= case_subsubstatus %>");
</script>
<script>
/* 
  By: Mukesh
  Date: 23-January-2024
  Description: added below code for showing status, sub status and sub status 2 according to case type (like WCAB)
*/
var case_type = "<%= case_type %>"; 
  $(document).ready(function() {
  setTimeout(function() {  
    loadStatus();
    loadSubStatus();
    loadSubSubStatus();   
  }, 3000);
});

var type = "";
function setCase()
{
  type = $("#case_typeInput").val();
  setTimeout(function() {  
    loadStatus();
    loadSubStatus();
    loadSubSubStatus();   
  }, 3000);
}

// retrieves status from "cse_casestatus" table according to case type and deleted = 'N'
async function loadStatus()
{  
  var case_type = "<%= case_type %>";
  if(type!="")
  {
    case_type = type;
  }
  var status = "<%= case_status %>";
  $.get("api/statusfilters",{},function(data){
    data = JSON.parse(data);
    $("#case_statusInput").html("<option value=''>Select from List</option>");
    for(i=0;i<data.length;i++){      
      if(case_type.toUpperCase() == data[i].law.toUpperCase()  && data[i].deleted.toUpperCase() == "N") 
      {
        if(status == data[i].status)
        {
          var option = "<option selected value='"+ data[i].status +"'>"+ data[i].status +"</option>";
        }
        else
        {
          var option = "<option value='"+ data[i].status +"'>"+ data[i].status +"</option>";
        }
        $("#case_statusInput").append(option);
      }
    }
  });  
}

// retrieves sub status from "cse_casesubstatus" table according to case type and deleted = 'N'
async function loadSubStatus()
{
  var case_type = "<%= case_type %>";
  if(type!="")
  {
    case_type = type;
  }
  var status = "<%= case_substatus %>";
  $.get("api/substatusfilters",{},function(data){
    data = JSON.parse(data);
    $("#case_substatusInput").html("<option value=''>Select from List</option>");
    for(i=0;i<data.length;i++){       
      if(case_type.toUpperCase() == data[i].law.toUpperCase()  && data[i].deleted.toUpperCase() == "N") 
      {
        if(status == data[i].status)
        {
          var option = "<option selected value='"+ data[i].status +"'>"+ data[i].status +"</option>";
        }
        else
        {
          var option = "<option value='"+ data[i].status +"'>"+ data[i].status +"</option>";
        }
        $("#case_substatusInput").append(option);
      }
    }
  });  
}

// retrieves sub status 2 from "cse_casesubsubstatus" table according to case type and deleted = 'N'
async function loadSubSubStatus()
{
  var case_type = "<%= case_type %>";
  if(type!="")
  {
    case_type = type;
  }
  var status = "<%= case_subsubstatus %>";
  $.get("api/subsubstatusfilters",{},function(data){
    data = JSON.parse(data);
    $("#case_subsubstatusInput").html("<option value=''>Select from List</option>");
    for(i=0;i<data.length;i++){      
      if(case_type.toUpperCase() == data[i].law.toUpperCase() && data[i].deleted.toUpperCase() == "N") 
      {
        if(status == data[i].status)
        {
          var option = "<option selected value='"+ data[i].status +"'>"+ data[i].status +"</option>";
        }
        else
        {
          var option = "<option value='"+ data[i].status +"'>"+ data[i].status +"</option>";
        }
        $("#case_subsubstatusInput").append(option);
      }
    }
  });  
}
</script>