<?php 
require_once('../shared/legacy_session.php');
session_write_close();

include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$customer_id = $_SESSION["user_customer_id"];
$list_reference = "";
if ($customer_id == "1072") {
	$list_reference = "`ikase`.";
}
$query_case_type = "SELECT case_type, COUNT(case_id) case_count 
FROM " . $list_reference . "cse_case
WHERE case_type != ''
AND deleted ='N' 
AND customer_id = '" . $customer_id . "'
GROUP BY case_type
ORDER BY case_type ASC";
$result_case_type = DB::runOrDie($query_case_type);
$case_type_options = "<option value=''>Select from List</option>";
$wcab_count = 0;
$pi_count = 0;
while ($row = $result_case_type->fetch()) {
	$case_type = $row->case_type;
	$case_count = $row->case_count;
	
	$blnWCAB = ((strpos($case_type, "Worker") > -1) || (strpos($case_type, "WC") > -1) || (strpos($case_type, "W/C") > -1));
	
	if ($blnWCAB) {
		$wcab_count += $case_count;
	} else {
		$pi_count += $case_count;
	}
	$display_type = ucwords(str_replace("_", " ", $case_type));
	$display_type = str_replace("NewPI", "PI", $display_type);
	$display_type .= " (" . $case_count . ")";
	$option = "<option value='{$case_type}'>{$display_type}</option>";
	$case_type_options .= "" . $option;
}
if ($result_case_type->rowCount() ==0) {
	$option = "<option value='WCAB' selected>WCAB</option>";
	$case_type_options .= "" . $option;
}
if ($wcab_count > 1) {
	$option = "<option value='WCAB All'>WCAB All (" . $wcab_count . ")</option>";
	$case_type_options .= "" . $option;
}
if ($pi_count > 1) {
	$option = "<option value='PI All'>PI All (" . $pi_count . ")</option>";
	$case_type_options .= "" . $option;
}
/*
if ($_SESSION["user_customer_id"]==1033) {
	$option = "<option value='NewPI'>New PI</option>";
	$case_type_options .= "" . $option;
}
*/
$result = DB::runOrDie("SELECT * FROM `cse_venue` ORDER BY venue");
$venue_options = "<option value=''>Select from List</option>";
while ($row = $result->fetch()) {
    $venue_options .= "<option value='{$row->venue_uuid}'>{$row->venue_abbr}</option>";
}

$data_source = "";
if ($_SESSION["user_customer_id"]=="1070") {
	$data_source = "_leyva";
}
$order_by = "ORDER BY casestatus";
if ($_SESSION["user_customer_id"]=="1070") {
	$order_by = "ORDER BY casestatus_id";
}
$result = DB::runOrDie("SELECT * FROM `ikase{$data_source}`.cse_casestatus cstat {$order_by}");
$casestatus_options = "<option value=''>Select from List</option>";
while ($row = $result->fetch()) {
    $casestatus_options .= "<option value='{$row->casestatus}'>{$row->casestatus}</option>";
}

$option_sub = "<option value=''>Select from List</option>";
$casesubstatus_options = "" . $option_sub;

$order_by = "ORDER BY casesubstatus";
if ($_SESSION["user_customer_id"]=="1070") {
	$order_by = "ORDER BY casesubstatus_id";
}
$result_sub = DB::runOrDie("SELECT * FROM `ikase{$data_source}`.cse_casesubstatus csubstat {$order_by}");
while ($row = $result_sub->fetch()) {
    if ($_SESSION["user_customer_id"]=="1070") {
		$abbr = $row->abbr;
	}
    $casesubstatus_options .= "<option value='{$row->casesubstatus}'>{$abbr} - {$row->casesubstatus}</option>";
}

$result = DB::runOrDie("SELECT * FROM `cse_bodyparts` ORDER BY code");
$body_options = '';
while ($row = $result->fetch()) {
    $body_options .= "<option value=\"{$row->bodyparts_uuid}\">{$row->code} - {$row->description}</option>";
}

$blnIPad = isPad();
?>
<% var venue_options = "<?php echo $venue_options; ?>"; %>
<% var case_type_options = "<?php echo $case_type_options; ?>"; %>
<% var casestatus_options = "<?php echo $casestatus_options; ?>"; %>
<% var casesubstatus_options = "<?php echo $casesubstatus_options; ?>"; %>
<% var rating_options = "<option value='A'>A</option><option value='B'>B</option><option value='C'>C</option><option value='D'>D</option><option value='F'>F</option>"; %>
<%
if (typeof language == "undefined") {
	language = "english";
}
%>
<div id="homemedical" style="margin-right:10px; float:right; width:300px; border:0px green solid; display:none"></div>
<div class="kase" style="margin-left:15px">
    <form id="kase_form" parsley-validate>
    <input id="id" name="id" type="hidden" value="" />
    <input id="table_id" name="table_id" type="hidden" value="" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="" />
    <input value="" name="submittedonInput" id="submittedonInput" class="hidden" />
    <div style="float:right">
    	<table width="250px" border="0" align="left" cellpadding="3" cellspacing="0">
        	<tr>
            	<th width="19%" align="right" valign="top" nowrap="nowrap" scope="row">Body Parts:</th>
                <td colspan="2" valign="top" align="left">
                  <select name="bodypartSearchInput[]" id="bodypartSearchInput" multiple="multiple" style="width:280px; height:240px">
                  <?php echo $body_options; ?>
                  </select>
                 </td>
            </tr>
            <tr>
                <th width="19%" align="right" valign="top" nowrap="nowrap" scope="row">Special Instructions:</th>
                <td colspan="2" valign="top" align="left">
                  <input type="checkbox" value="Y" name="special_instructionsInput" id="special_instructionsInput" class="kase input_class floatlabel" style="width:auto" />
                 </td>
            </tr>
            <tr>
              <th align="right" valign="top" nowrap="nowrap" scope="row">Last Name Starts w/:</th>
              <td colspan="2" valign="top" align="left">
              	<?php 
				$arrOptions = array();
				for($int = 65; $int < 91; $int++) {
					$option = "<option value='" . chr($int) . "'>" . "&#" . $int . "</option>";
					$arrOptions[] = $option;
				}
				?>
                <select id="starts_with" name="starts_with">
                	<option value="" selected="selected">Select Letter...</option>
                    <?php
					echo implode("", $arrOptions);
					?>
                </select>
              </td>
            </tr>
       </table>
    </div>
    <table width="300px" border="0" align="left" cellpadding="3" cellspacing="0">
    	<tr style="display:none" class="hideme">
    		<th width="19%" align="right" valign="top" nowrap="nowrap" scope="row">Case Number:</th>
		    <td colspan="2" valign="top">
           	  <input value="" name="case_numberInput" id="case_numberInput" class="kase input_class floatlabel" style="width:227px"  parsley-error-message="" />
             </td>
        </tr>
        <tr>
    		<th width="19%" align="right" valign="top" nowrap="nowrap" scope="row">Employee:</th>
		    <td colspan="2" valign="top">
        		<input name="employeeInput" type="text" id="employeeInput" autocomplete="off" style="width:227px" class="kase input_class floatlabel" />
        	</td>
        </tr>
        
        <tr style="display:none" class="hideme">
    		<th width="19%" align="right" valign="top" nowrap="nowrap" scope="row">Applicant:</th>
		    <td colspan="2" valign="top">
           	  <input value="" name="full_nameInput" id="full_nameInput" class="kase input_class floatlabel" style="width:227px"  parsley-error-message="" />
             </td>
        </tr>
          <tr>
            <th width="19%" align="right" valign="top" scope="row">Venue:</th>
            <td colspan="2" valign="top">
                <select name="venueInput" id="venueInput" class="kase input_class" style="width:227px" parsley-error-message=""  >
                  <% var select_options = venue_options; %>
                  <%= select_options %>
              </select>           </td>
          </tr>
      <tr style="display:none" class="hideme">
        <th align="right" valign="top" scope="row" nowrap>ADJ Number:</th>
        <td colspan="2" valign="top">
        	<input value="" name="adj_numberInput" id="adj_numberInput" style="width:227px" class="kase input_class floatlabel"  parsley-error-message="" />        </td>
  </tr>
      <tr>
        <th align="right" valign="top" scope="row" nowrap>Case Date:</th>
        <td colspan="2" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td width="20%"><input value="" name="case_dateInput" id="case_dateInput" class="kase input_class date_input" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="From" style="width:110px"  parsley-error-message="" /></td>
              <td width="20%"><input value="" name="case_throughdateInput" id="case_throughdateInput" class="kase input_class date_input" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="Through" style="width:110px"  parsley-error-message="" /></td>
              <td width="60%">&nbsp;</td>
            </tr>
        </table></td>
      </tr>  
      <tr>
        <th align="right" valign="top" scope="row">&nbsp;</th>
        <td colspan="2" valign="top">
        	<a id="this_month_cases" style="cursor:pointer" class="set_cases_dates white_text">this month</a>
            &nbsp;|&nbsp;
            <a id="last_month_cases" style="cursor:pointer" class="set_cases_dates white_text">last month</a>
            &nbsp;|&nbsp;
            <a id="six_month_cases" style="cursor:pointer" class="set_cases_dates white_text">last six months</a>
        </td>
      </tr>
      <tr>
        <th align="right" valign="top" scope="row">SOL:</th>
        <td colspan="2" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td width="20%"><input value="" name="sol_startdateInput" id="sol_startdateInput" class="kase input_class date_input" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="From" style="width:110px"  parsley-error-message="" /></td>
              <td width="20%"><input value="" name="sol_enddateInput" id="sol_enddateInput" class="kase input_class date_input" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="Through" style="width:110px"  parsley-error-message="" /></td>
              <td width="60%"></td>
            </tr>
        </table></td>
      </tr>
      <tr>
        <th align="right" valign="top" scope="row">&nbsp;</th>
        <td colspan="2" valign="top">
        	<a id="this_month_statute" style="cursor:pointer" class="set_statute_dates white_text">this month</a>
            &nbsp;|&nbsp;
            <a id="next_month_statute" style="cursor:pointer" class="set_statute_dates white_text">next month</a>
            &nbsp;|&nbsp;
            <a id="six_month_statute" style="cursor:pointer" class="set_statute_dates white_text">six months</a>
        </td>
      </tr>
      <tr>
        <th align="right" valign="top" scope="row">Type:</th>
        <td colspan="2" valign="top">
            <select name="case_typeInput" id="case_typeInput" class="kase input_class" style="width:227px" parsley-error-message=""  >
                  <% var select_type_options = case_type_options; %>
                  <%= select_type_options %>
              </select>        </td>
      </tr>
      <tr>
        <th align="right" valign="top" scope="row">Sub Outs:</th>
        <td colspan="2" valign="top">
        	<input type="radio" name="subouts" id="subouts_include" value="Y" />&nbsp;Include
            &nbsp;|&nbsp;
            <input type="radio" name="subouts" id="subouts_exclude" value="N" />&nbsp;Exclude
        </td>
      </tr>
      <tr>
      	<th align="right" valign="top" scope="row">Status:</th>
        <td width="44%" valign="top">
        	<select name="case_statusInput" id="case_statusInput" class="kase input_class" style="width:227px">
                	<% var status_options = casestatus_options; %>
                      <%= status_options %>
                </select>        </td>
        <td width="37%" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="2" id="status_dates_holder" style="display:none">
              <tr>
                <td width="37%"><strong>Dates:</strong></td>
                <td width="37%"><input value="" name="status_startdateInput" id="status_startdateInput" class="kase input_class date_input" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="From" style="width:110px"  parsley-error-message="" /></td>
                <td width="63%"><input value="" name="status_enddateInput" id="status_enddateInput" class="kase input_class date_input" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="Through" style="width:110px"  parsley-error-message="" /></td>
                </tr>
            </table>
        </td>
      </tr>
      <tr>
        <th align="right" valign="top" scope="row">Sub Status:</th>
        <td colspan="2" valign="top">
        	<select name="case_substatusInput" id="case_substatusInput" class="kase input_class" style="width:227px; overflow-y: scroll;">
                <% var sub_status_options = casesubstatus_options; %>
              <%= sub_status_options %>
            </select>
        </td>
      </tr>
      <tr>
        <th align="right" valign="top" scope="row" nowrap="nowrap">Sub Status 2:</th>
        <td colspan="2" valign="top">
        	<select name="case_subsubstatusInput" id="case_subsubstatusInput" class="kase input_class" style="width:227px; overflow-y: scroll;">
                <% var sub_status_options = casesubstatus_options; %>
              <%= sub_status_options %>
            </select>
        </td>
      </tr>
	  <tr style="display:none" class="hideme">
        <th align="right" valign="top" scope="row">Case&nbsp;Rating:</th>
        <td colspan="2" valign="top">
        	<select name="ratingInput" id="ratingInput" class="kase input_class" style="width:227px">
            	<option value="" selected="selected">Select from List</option>
                <% var status_options = rating_options; %>
              <%= status_options %>
            </select>
        </td>
      </tr>
      <!--BELOW IS CONFUSING, AND I'M SORRY, TERMS CHANGED.  
      `supervising_attorney` in the database is the main firm attorney.
      `attorney` is the actual "supervising" attorney.  
      
      too much data already to make the change
      
      <tr>
      	<th align="right" valign="top" scope="row">Attorney:</th>
        <td valign="top">
        	<input autocomplete="off" value="" name="supervising_attorneyInput" style="width:227px" id="supervising_attorneyInput" class="kase input_class" title="This Attorney is the main firm attorney" />
            <br /><span style="font-size:0.8em; font-style:italic; color:white">This Attorney is the main firm attorney</span>
        </td>
      </tr>
      <tr>
      	<th align="right" valign="top" scope="row">Supv Atty:</th>
        <td valign="top">
        	<input autocomplete="off" value="" name="attorneyInput" style="width:227px" id="attorneyInput" class="kase input_class" title="This Attorney handles the details of the Kase" />
            <br /><span style="font-size:0.8em; font-style:italic; color:white">This Attorney will be used to sign letters and forms</span>
         </td>
      </tr>
      
      <tr>
        <th align="right" valign="top" scope="row">Coordinator:</th>
        <td valign="top"><input value="" name="workerInput" id="workerInput" style="width:227px" class="kase input_class" /></td>
      </tr>
      <tr>
        <th align="right" valign="top" scope="row">Language:</th>
        <td valign="top">
			<select name="case_languageInput" id="case_languageInput" class="kase input_class">
			  <?php include("../api/language_options.php"); ?>
            </select>
        	<div style="float:right; width:310px; border:0px solid white">
            	<div style="display:inline-block; width:200px; border:0px solid white">
                <span style="width:100px">Interpreter&nbsp;&nbsp;<input title="Check this box if an interpreter is needed for this case" type="checkbox" value="Y" id="interpreter_neededInput" name="interpreter_neededInput" style="border:0px solid white; width:13px; height:13px" class="kase input_class" />
                </span>
                </div>
            </div>
        	
        </td>
      </tr>
      <tr>
        <th align="right" valign="top" scope="row">&nbsp;</th>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr>
        <th align="right" valign="top" scope="row">Benefits:</th>
        <td valign="top">
            <table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
              <tr>
                <td width="25%" align="left" valign="top">Medical
                  <input value="" name="medicalInput" style="width:65px" id="medicalInput" class="kase input_class" /></td>
                <td width="25%" align="left" valign="top" nowrap="nowrap" style="width:10%">TD $
                  <input value="" name="tdInput" style="width:65px" id="tdInput" class="kase input_class" /></td>
                <td width="25%" align="left" valign="top">Voucher $
                  <input value="" name="rehabInput" style="width:65px" id="rehabInput" class="kase input_class" /></td>
                <td width="25%" align="left" valign="top" nowrap="nowrap" style="width:10%">EDD $
                  <input value="" name="eddInput" style="width:65px" id="eddInput" class="kase input_class" /></td>
              </tr>
            </table>
		</td>
      </tr>
      <tr>
      	<th align="right" valign="top" scope="row">Claims:</th>
        <td valign="top">
	        <table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
              <tr>
                <td width="20%" align="center" valign="top" nowrap="nowrap" class="left_right_border">Third Party
                  <input type="checkbox" class="claims" name="third_party_claimsInput" id="third_partyInput" value="3P" />
                  <div id="third_partyInHouse" class="white_text" style="display:">
                  	In House? <a id="third_partyInHouseY" class="yes_no_link">Y</a> <a id="third_partyInHouseN" class="yes_no_link">N</a>
                    <input type="hidden" name="third_partyInHouseChoice" id="third_partyInHouseChoice" value="" style="display:" />
                  </div>
                </td>
                <td width="20%" align="left" valign="top" nowrap="nowrap" class="left_right_border">132a
                <input type="checkbox" class="claims" name="132a_claimsInput" id="132aInput" value="132a" />
                <div id="132aInHouse" class="white_text" style="display:">
                  	In House? <a id="132aInHouseY" class="yes_no_link">Y</a> <a id="132aInHouseN" class="yes_no_link">N</a>
                    <input type="hidden" name="132aInHouseChoice" id="132aInHouseChoice" value="" style="display:" />
                    <div>
                    	Terminated:&nbsp;
                      <input value="" name="terminated_dateInput" id="terminated_dateInput" class="kase input_class date_input" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" style="width:75px" title="Terminated Date" />
                    </div>
                </div>
                </td>
                <td width="20%" align="center" valign="top" nowrap="nowrap" class="left_right_border">Serious and Willful
                <input type="checkbox" class="claims" name="serious_claimsInput" id="seriousInput" value="SER" />
                <div id="seriousInHouse" class="white_text" style="display:">
                  	In House? <a id="seriousInHouseY" class="yes_no_link">Y</a> <a id="seriousInHouseN" class="yes_no_link">N</a>
                    <input type="hidden" name="seriousInHouseChoice" id="seriousInHouseChoice" value="" style="display:" />
                  </div>
                </td>
                <td width="20%" align="center" valign="top" nowrap="nowrap" class="left_right_border">ADA
                  <input type="checkbox" class="claims" name="ada_claimsInput" id="adaInput" value="ADA" />
                	<div id="adaInHouse" class="white_text" style="display:">
                  	In House? <a id="adaInHouseY" class="yes_no_link">Y</a> <a id="adaInHouseN" class="yes_no_link">N</a>
                    <input type="hidden" name="adaInHouseChoice" id="adaInHouseChoice" value="" style="display:" />
                  </div>
                </td>
                <td width="20%" align="center" valign="top" nowrap="nowrap" class="left_right_border">SS
                <input type="checkbox" class="claims" name="ss_claimsInput" id="ssInput" value="SS" />
                <div id="ssInHouse" class="white_text" style="display:">Y</a> <a id="ssInHouseN" class="yes_no_link">N</a>
                    <input type="hidden" name="ssInHouseChoice" id="ssInHouseChoice" value="" style="display:" />
                  </div>
                </td>
              </tr>
            </table>
        </td>
      </tr>
       -->
</table>
</form>
</div>
<hr />
<div>There are no required fields; you must select at least one (1) field above for an Advanced Search.</div>
<div id="search_kase_all_done"></div>
<script language="javascript">
$( "#search_kase_all_done" ).trigger( "click" );
</script>
