<?php
if ($form_name!="kai") {
	$panel_title = ucwords($form_name);
} else {
	$panel_title = ucwords("addl info");
}
if ($form_name=="accident") { 
	$panel_title ="Accident";
} 

if ($form_name=="person") { 
	/*
	if ($kase_type_pi_confirm == "yes") {
		$panel_title ="Plaintiff";
	} else {
		$panel_title ="Applicant";
	}
	*/
	
	$panel_title ="Applicant";
}
 
if ($form_name=="event_dialog") { 
	$panel_title ="Event";
} 
if ($form_name=="new_legal") { 
	$panel_title ="Employment Law";
} 
if ($form_name=="kai") { 
	$panel_title ="Additional Information";
}
if ($form_name=="partie_kai") { 
	$panel_title ="Additional Information";
}
if ($form_name=="email") { 
	$panel_title ="Email Settings";
}
$blnShowButtons = true; 
if($form_name=="work_history_earnings") { 
	$panel_title ="<span class='sub_title'>Earnings</span>";
}
if($form_name=="work_history_disability") { 
	$panel_title ="<span class='sub_title'>Disability</span>";
	$blnShowButtons = false;
} 
if($form_name=="work_history_compensation") { 
	$panel_title ="<span class='sub_title'>Compensation</span>";
	$blnShowButtons = false;
} 
if ($form_name=="injury_number") { 
	$panel_title ="Policy / Addl Claim";
} 
if ($form_name=="additional_case_number") { 
	$panel_title ="Others Cases";
}
if ($form_name=="settlement") {
	$panel_title = "Workers Comp Attorney Fees";
}
if ($form_name=="settlement_list") {
	$panel_title = "Settlement";
}
if ($form_name=="settlement_sheet") {
	$panel_title = "Settlement";
}
if ($form_name=="settlement_fees") {
	$panel_title = "Fees";
	$blnShowButtons = false;
}
if ($form_name=="costs") {
	$panel_title = "Firm Costs";
}
if ($form_name=="fees") {
	$panel_title = "Depo Fees";
}
if ($form_name=="contacts") {
	$panel_title = "Contact";
}
if ($form_name=="personal_injury") {
	$panel_title = "Info";
}
if ($form_name=="slipandfall") {
	$panel_title = "Slip and Fall Info";
	$form_name = "personal_injury";
}
if ($form_name=="rental") {
	$panel_title = "Rental Car";
}
if ($form_name == "financial") {
	$panel_title = "Trust Escrow";
}
if ($form_name=="ssn_claim") {
	$panel_title = "SSN Claim";
}
?>
<?php
if ($form_name!="bodyparts") {
	require_once('../shared/legacy_session.php');
	session_write_close();
	$blnDeletePermission = true;
	if ($_SESSION["user_customer_id"]==1075) {
		//per steve g 4/3/2017
		$blnDeletePermission = false;
		if (strpos($_SESSION['user_role'], "admin") !== false) {
			$blnDeletePermission = true;
		}
	}
}
?>
<%
if (typeof kase_type == "undefined") {
    kase_type = "";
}
%>
<?php
if ($form_name=="ssn_claim") {
?>
<% kase_type = ""; %>
<?php } ?>

<div id="sub_category_holder<?php echo "_" . $form_name; ?>" class="<?php echo $form_name; ?>" style="text-align:left; padding-bottom:5px">
	<span style="text-align:left;">
    	<span style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;">
		<span id="panel_title"><%	//kase_type.replace("_", " ").capitalizeWords() %>
        	<%  if (blnPiReady) { %>
            	<% if (kase_type == "NewPI" || kase_type == "social_security") { %>
					<?php if ($form_name=="person") { ?>
                        Plaintiff
                    <?php } else { ?>
                    	
                    	 <?php echo ucwords(str_replace("_", " ", $panel_title)); ?>
                    <?php } ?>
            	<% } else { %>
                	
            		<?php echo ucwords(str_replace("_", " ", $panel_title)); ?>
            	<% } %>
            <% } else { %>
            <?php echo ucwords(str_replace("_", " ", $panel_title)); ?>
            <% } %>
        </span>&nbsp;<img src="img/loading_spinner_1.gif" width="20" height="20" id="gifsave" class="<?php echo $form_name; ?>" style="display:none; opacity:50%" /> &nbsp; 
        
        <?php if ($form_name == "bodyparts") { ?>
        <a title="Click to compose a new note" class="compose_new_note" id="compose_bodyparts_<%=case_id %>_<%=injury_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="font-size:0.65em;cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>&nbsp;
        <a id='scrape_bodyparts_<%=injury_id %>' class='scrape_bodyparts white_text' style='font-size:0.6em;cursor:pointer' title='Click to import data from EAMS and update the body parts for this injury'>eams update</a>
        <?php  } ?>
       <span class="alert alert-success" style="display:none; height:25px; width:50px;font-size:14px; z-index:4251; margin-top:-35px; margin-left:-10px;">Saved</span>
       <span class="alert alert-warning" style="display:none; height:25px; width:50px; font-size:14px; z-index:4251; margin-top:-35px; margin-left:-10px;"></span>
       </span>
       
       <div style="float:right;border:0px solid red;" id="<?php echo $form_name; ?>_buttons">
	   		<?php if ($form_name=="injury") { ?>
        	&nbsp;&nbsp;&nbsp;<a title="Click to compose a new note" class="compose_new_note" id="compose_injurynote_<%=case_id %>_<%=injury_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>&nbsp;<a href="#newinjury/<%=case_id %>" class="white_text" style=" display:<% if (id == '-1') { %>none<% } %>;font-size:0.9em;" title="Click to add additional DOI to this case" id="new_doi_link">new DOI</a>
			<?php } ?>
            <?php 
				  // || $form_name=="injury_number" || $form_name=="bodyparts"
				  if ($form_name=="person" || $form_name=="personal_injury" || $form_name=="injury_number" || $form_name=="bodyparts" || $form_name=="injury") { ?>
            <!--<div style="float:left; color:white; font-size:.8em"><span id="time_timer_span"></span></div>&nbsp;-->
            <?php if ($form_name=="person") { ?>
        	<span id="applicant_row_links">
            	<a id="add_medical" href="#parties/<%=case_id %>/-2/medical_provider" class="medical_items" title="Click to add Prior Medical Provider to Applicant" style="background:#CFF; color:black; padding:2px">Add Prior Medical</a><span class="black_text medical_items">&nbsp;</span><a id="list_prior_medical" class="white_text medical_items" style="background:#CFF; color:black; padding:2px">List Prior Medical</a>
                <span class="black_text medical_items">&nbsp;</span><a id="list_rx" class="white_text medical_items" style="background:#CFF; color:black; padding:2px">Rx</a>
            </span>
            <?php } ?>
            
            <?php 
					$nameandid = "";
					if ($form_name=="person" || $form_name=="personal_injury" || $form_name=="injury") { 
						$nameandid = "billing_time_dropdownInput";
					}
					if ($form_name=="injury_number") { 
						$nameandid = "billing_time_dropdown_inInput";
					}
					if ($form_name=="bodyparts") { 
						$nameandid = "billing_time_dropdown_bpInput";
					}
			?>
            <div style="color:white; float:left; margin-top:0px; z-index:9999; margin-left:-10px" id="billing_dropdown_holder">
            </div>
			<?php } ?>
            &nbsp;&nbsp;&nbsp;
            <?php if ($form_name=="injury") { ?>
            	<div class="injury_links" style="display:inline-block; z-index:6234; margin-left:0px; margin-top:-10px; border:0px solid red; width: 100px">
            		<a id='scrape_injury_<%=injury_id %>' class='scrape_injury white_text' style='font-size:0.9em;cursor:pointer; margin-right:-10px;' title='Click to import data from EAMS and update this injury'>eams update</a><br />
		            <a href='reports/demographics_sheet.php?case_id=<%=case_id %>&injury_id=<%=injury_id %>' style='font-size:0.9em;cursor:pointer; color:white; margin-right:-10px;' title='Click to create a Demographics sheet for this DOI only' target="_blank" id="injury_demographics_link">demographics</a>
            	</div>
            <?php } ?>  
            
            <?php if ($blnShowButtons) { ?>
           <span class="edit_row <?php echo $form_name; ?>" style="display:inline-block; z-index:6234; margin-left:25px; margin-top:-10px">
           <button id="partie_edit" class="edit btn btn-transparent border-blue" style="border:0px solid; <?php if ($form_name=="injury") { ?>margin-top:-30px<?php } ?>; width:20px; display:"><i class="glyphicon glyphicon-plus" title="Add email account" style="color:#0033FF">&nbsp;</i></button>
           </span>
           <span class="button_row <?php echo $form_name; ?> hidden" style="display:inline-block; margin-left:25px; margin-top:-10px">
           		<?php //per steve at dordulian 3/31/32017
				if ($blnDeletePermission) { ?>
                <button class="btn btn-transparent border-red delete" style="color:white; width:20px; border:0px solid; <?php if ($form_name=="injury") { ?>margin-top:-30px<?php } ?>; display:<% if (id == "-1") { %>none<% } %>"><i class="glyphicon glyphicon-trash" style="color:#FC221D">&nbsp;</i></button>
                <?php } ?>
               &nbsp;<button class="reset btn btn-transparent border-white" style="width:20px; border:0px solid; <?php if ($form_name=="injury") { ?>margin-top:-30px<?php } ?>; display:<% if (id == "-1") { %>none<% } %>"><i class="glyphicon glyphicon-repeat">&nbsp;</i></button>
               &nbsp;<button class="save btn btn-transparent border-green" style="width:20px; border:0px solid; <?php if ($form_name=="injury") { ?>margin-top:-30px<?php } ?>"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></button>
           </span>
           <?php } ?>
       </div>
    </span>   
</div>
