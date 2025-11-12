<div id="settlement_sheet">
	<div id="holders_holder" style="float:right; width:39vw; background:url(img/glass_dark.png); padding:10px; display:none">
    	<div id="settlement_notes_holder" class="holders" style="display:none"></div>
        <div id="settlement_negotiation_holder" class="holders" style="display:none"></div>
        <div id="settlement_negotiation_notes_holder" class="holders" style="display:none; margin-top:10px"></div>
        <div id="settlement_costs_holder" class="holders" style="display:none"></div>
        <div id="settlement_med_holder" class="holders" style="display:none"></div>
        <div id="settlement_subro_holder" class="holders" style="display:none"></div>
        <div id="settlement_deduct_holder" class="holders" style="display:none"></div>
        <div id="settlement_losses_holder" class="holders" style="display:none"></div>
        <div id="settlement_checkrequests_holder" class="holders" style="display:none"></div>
    </div>
    <div class="gridster settlement_list_view settlement" id="gridster_settlement_list" style="display:">
         <div style="background:url(img/glass_info.png) left top; padding:5px; width:1028px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; float:none;" class="col-md-6">
            <form id="settlement_form" parsley-validate>
                <input id="table_name" name="table_name" type="hidden" value="settlement" />
                <input id="table_id" name="table_id" type="hidden" value="<%= settlement_id %>" />
                <input id="settlement_id" name="settlement_id" type="hidden" value="<%= settlement_id %>" />
                <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />
                <div style="margin-top:0px; margin-right:10px; padding-top:5px">  
                    <div style="position:absolute; z-index:99; left:210px">
                        <div style="margin-bottom:20px; display:">
                            <button id="settlement_notes_button" title="Click to show Settlement Notes" class="settlement_button btn btn-xs">Settlement Notes</button>
                            &nbsp;
                            <button id="settlement_negotiation_button" title="Click to show Negotiations" class="settlement_button btn btn-xs">Negotiations</button>
                            &nbsp;
                            <button id="settlement_costs_button" title="Click to show case Costs Info" class="settlement_button btn btn-xs">Costs</button>	
                            &nbsp;
                            <button id="settlement_med_button" title="Click to show case Medical Summary" class="settlement_button btn btn-xs">Medical Summary</button>	
                            <button id="settlement_deduct_button" title="Click to show Deductions" class="settlement_button btn btn-xs">Deductions</button>
                            &nbsp;
                            <button id="settlement_losses_button"  class="settlement_button btn btn-xs" title="Click to show the Losses Summary">Losses Summary</button>
                        </div>
                    </div>          
                    <?php 
                    $form_name = "settlement_list"; 
                    include("dashboard_view_navigation.php"); 
                    ?>
                </div>
                <ul style="margin-bottom:3px;">
                    <li id="attorneyGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6>
                            <div class="form_label_vert" style="margin-top:10px;">Attorney</div>
                        </h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="attorneySave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="attorneySaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="<%=attorney_full_name %>" type="hidden" id="attorney_full_name" />
                        <input value="<%= attorney %>" name="attorneyInput" id="attorneyInput" class="kase settlement_list_view input_class hidden" placeholder="Attorney Who Settled" style="margin-top:-26px; margin-left:90px; width:125px" />
                        <span id="attorneySpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= attorney_full_name %></span>
                    </li>
                    <li id="date_submittedGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="settlement_list gridster_border non_ssi_boxes" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Submitted On:</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="date_submittedSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_submittedSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                            <input value="<%= date_submitted %>" name="date_submittedInput" id="date_submittedInput" class="kase settlement_list_view input_class date_input hidden" placeholder="Date Submitted" style="margin-top:-26px; margin-left:90px; width:125px" />
                        <span id="date_submittedSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= date_submitted %></span>
                    </li>
                    <li id="date_approvedGrid" data-row="1" data-col="3" data-sizex="1" data-sizey="1" class="settlement_list gridster_border non_ssi_boxes" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Approved On:</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="date_approvedSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_approvedSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="<%= date_approved %>" name="date_approvedInput" id="date_approvedInput" class="kase settlement_list_view input_class date_input hidden" placeholder="Date Approved" style="margin-top:-26px; margin-left:90px; width:125px" />
                        <span id="date_approvedSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= date_approved %></span>
                    </li>
                    <li id="amount_of_settlementGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Amount:</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="amount_of_settlementSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_of_settlementSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input type="text" value="<%= amount_of_settlement %>" name="amount_of_settlementInput" id="amount_of_settlementInput" class="kase settlement_list_view input_class hidden" placeholder="Amount of Settlement" style="margin-top:-26px; margin-left:90px; width:125px" />
                        <span id="amount_of_settlementSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= amount_of_settlement_span %></span>
                    </li>
                    <li id="pd_percentGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="settlement_list gridster_border non_ssi_boxes" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">% PD:</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="pd_percentSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="pd_percentSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                            <input type="number" value="<%= pd_percent %>" name="pd_percentInput" id="pd_percentInput" class="kase settlement_list_view input_class hidden" placeholder="PD Percentage" style="margin-top:-26px; margin-left:90px; width:125px" />
                        <span id="pd_percentSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= pd_percent %></span>
                    </li>
                    <li id="future_medicalGrid" data-row="2" data-col="3" data-sizex="1" data-sizey="1" class="settlement_list gridster_border non_ssi_boxes" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Future Medical:</div></h6>
                        <div style="margin-top:-12px" class="save_holder hidden" id="future_medicalSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="future_medicalSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                            <input type="checkbox" value="Y" name="future_medicalInput" id="future_medicalInput" class="kase settlement_list_view input_class hidden" style="margin-top:-26px; margin-left:90px;" <% if (future_medical=="Y") { %>checked<% } %> />
                        <span id="future_medicalSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= future_medical %></span>
                    </li>
                    <li id="amount_of_feeGrid" data-row="2" data-col="3" data-sizex="2" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Fee:</div></h6>
                        <div style="margin-left:95px; margin-top:-26px">
                            <table width="98%" cellspacing="0" cellpadding="2">
                                <tr>
                                    <td align="left" valign="top" width="50%" nowrap="nowrap">
                                       <div style="margin-top:-12px" class="save_holder hidden" id="amount_of_feeSave">
                                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_of_feeSaveLink">
                                                <i class="glyphicon glyphicon-save"></i>
                                            </a>
                                        </div>
                                        <input type="text" value="<%= amount_of_fee %>" name="amount_of_feeInput" id="amount_of_feeInput" class="kase settlement_list_view input_class hidden" placeholder="Amount of Fee" style="margin-top:0px; margin-left:-7px; width:125px" />
                                        <span id="amount_of_feeSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:0px; margin-left:-7px"><%= amount_of_fee_span %></span>
                                    </td>
                                    <td align="left" valign="top" width="50%">
                                        <span style="color:white;padding-right:5px">Payment Status:&nbsp;</span>
                                        <select id="fee_payment_statusInput" name="fee_payment_statusInput" class="input_class hidden" style="margin-top: 0px;margin-left: 10px;">
                                            <option value="">Select Status</option>
                                            <option value="Paid" <% if (fee_payment_status == "Paid") { %>selected<% } %>>Paid</option>
                                            <option value="Unpaid" <% if (fee_payment_status == "Unpaid") { %>selected<% } %>>Unpaid</option>
                                        </select>     
                                        <span id="fee_payment_statusSpan" class="white_text span_class" style="margin-top: 0px;margin-left: 10px;"><%= fee_payment_status %></span>
                                    </td> 
                                </tr>
                            </table>
                        </div>
                        <!-- <div style="margin-top:-12px" class="save_holder hidden" id="amount_of_feeSave">
                            <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_of_feeSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input type="text" value="<%= amount_of_fee %>" name="amount_of_feeInput" id="amount_of_feeInput" class="kase settlement_list_view input_class hidden" placeholder="Amount of Fee" style="margin-top:-26px; margin-left:90px; width:125px" />
                        <span id="amount_of_feeSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= amount_of_fee_span %></span> -->
                    </li>
                    <li id="referral_sourceGrid" data-row="2" data-col="2" data-sizex="3" data-sizey="1" class="settlement_list gridster_border non_ssi_boxes" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Referral Source</div></h6>
                        <div style="margin-left:95px; margin-top:-26px">
                            <input id="referral_info" name="referral_info" type="hidden" value="" />
                            <table width="98%" cellspacing="0" cellpadding="2">
                                <tr>
                                    <td align="left" valign="top" width="23%" nowrap="nowrap">
                                        <input type="text" value="" id="referral_partie" class="kase settlement_list_view input_class hidden" placeholder="Name of Referral" style="margin-top:0px; width:125px" />
                                        <span id="referral_sourceSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:0px"></span>
                                        <input id="referral_id" type="hidden" value="" />
                                        <button id="edit_referral" class="btn btn-sm btn-primary" role="button" style="display:none">Edit</button>
                                    </td>
                                    <td align="left" valign="top" width="22%">
                                        Fee:&nbsp;<input type="number" min="0.00" step="0.01" value="" id="referral_source_fee" class="kase settlement_list_view input_class hidden" placeholder="Fee" style="width:115px; margin-top:0px" />
                                        <span id="referral_source_feeSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:0px"></span>
                                    </td>
                                    <td align="left" valign="top" width="34%">
                                        <span style="color:white;padding-right:5px">Payment Status:&nbsp;</span>
                                        <select id="referral_fee_payment_statusInput" class="input_class hidden" style="margin-top: 0px;">
                                            <option value="">Select Status</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Unpaid">Unpaid</option>
                                        </select>     
                                        <span id="referral_fee_payment_statusSpan" class="white_text span_class" style="margin-top: 0px;"></span>
                                    </td>
                                    <td align="left" valign="top" width="21%">
                                        Date:&nbsp;<input type="date" value="" id="referral_source_date" class="kase settlement_list_view input_class hidden" placeholder="Date" style="width:132px; margin-top:0px" />
                                        <span id="referral_source_dateSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:0px"></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </li>
                </ul>
                <ul id="prior_attorney_container" style="padding-left:4px;"></ul>
            </form>
        </div>
    </div>
    <div id="settlement_header_holder">
    </div>
</div>
<div style="height:20px; width:20px">&nbsp;</div>
<div class="settlement_list_fees settlement_fees" id="settlement_fees" style="display:">
     <div style="background:url(img/glass_dark.png) left top; padding:5px; width:1028px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;" class="">
     	<div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "settlement_fees"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <form id="settlement_fees_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="fee" />
        <input id="settlement_id" name="settlement_id" type="hidden" value="<%= settlement_id %>" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />

     	<table width="99%" border="0">
        	<tr>
            	<th style="text-align:left;vertical-align:top; width:104px">&nbsp;</th>
                <th style="text-align:left;vertical-align:top; width:50px">By</th>
                <th style="text-align:center;vertical-align:top; width:104px">Billed</th>
                <th style="text-align:center;vertical-align:top; width:104px">Paid</th>
                <!--
                <th style="text-align:center;vertical-align:top; width:104px">Due</th>
                -->
            	<th style="text-align:left;vertical-align:top; width:84px">Requested</th>
            	<th style="text-align:left;vertical-align:top; width:84px">Received</th>
                <th align="left" valign="top">&nbsp;</th>
            </tr>
            <?php
			
			$arrFees = array("Attorney", "Social Security", "Rehabilitation", "Deposition", "Other");
			foreach ($arrFees as $settle_fee) {
				$fee = strtolower(str_replace("Social Security", "ss", $settle_fee));
				$row_class = ' class="non_ssi_boxes"';
				if ($fee=="ss") {
					$row_class = "";
				}
			?>
            <tr id="row_info_holder_<?php echo $fee; ?>" style="height:40px; border-top:1px solid white; display:none">
            	<th align="left" valign="top" style="padding-top:10px">
                	<span  id="fee_holder_<?php echo $fee; ?>"><?php echo $settle_fee; ?></span>
                	<input type="hidden" id="fee_id_<?php echo $fee; ?>" name="fee_id_<?php echo $fee; ?>" />
                </th>
            	<td align="left" valign="top" style="padding-top:10px">
                	$ <input type="number" step="0.01" min="0" class="input_class" style="width:75px" id="fee_<?php echo $fee; ?>" name="fee_<?php echo $fee; ?>" />
                    <input type="hidden" id="balance_<?php echo $fee; ?>" />
                    <span class="span_class hidden" id="fee_<?php echo $fee; ?>Span"></span>
                </td>
                <td align="left" valign="top" style="padding-top:10px">
                    <span class="span_class" id="paid_<?php echo $fee; ?>Span"></span>
                </td>
                <td align="left" valign="top" style="padding-top:10px">
                    <span class="span_class" id="due_<?php echo $fee; ?>Span"></span>
                </td>
            	<td align="left" valign="top" style="padding-top:10px">
                	<input type="date" value="" name="date_requested_<?php echo $fee; ?>" id="date_requested_<?php echo $fee; ?>" class="input_class" placeholder="" style="width:125px" />
                     <span class="span_class hidden" id="date_requested_<?php echo $fee; ?>Span"></span>
                </td>
            	<td align="left" valign="top" style="padding-top:10px">
                	<input type="date" value="" name="date_received_<?php echo $fee; ?>" id="date_received_<?php echo $fee; ?>" class="input_class" placeholder="" style="width:125px" />
                    <span class="span_class hidden" id="date_received_<?php echo $fee; ?>Span"></span>
                </td>
                <td align="left" valign="top" style="padding-top:10px">&nbsp;
                
                </td>
                <td align="left" valign="top" style="padding-top:10px">
                	<textarea class="input_class" placeholder='Memo' id="memo_<?php echo $fee; ?>" name="memo_<?php echo $fee; ?>"   rows="2" cols="30"></textarea>
                    <span class="span_class hidden" id="memo_<?php echo $fee; ?>Span"></span>
                </td>
                <td align="left" valign="top" style="padding-top:10px" nowrap="nowrap">
                	<!--
                    <div style="float:right" id="payment_button_holder_<?php echo $fee; ?>">
                    	<button id="add_<?php echo $fee; ?>" style="display:" class="input_button btn btn-primary btn-sm add_fee">Add Fee</button>
                    </div>
                    -->
                </td>
            </tr>
            <tr id="row_fees_holder_<?php echo $fee; ?>"<?php echo $row_class; ?> style="border-top:1px solid white; display:">
                <th align="left" valign="top"><?php echo $settle_fee; ?></th>
                <td colspan="7" align="left" valign="top" id="fees_holder_<?php echo $fee; ?>">&nbsp;</td>
                <td align="left" valign="top" style="padding-top:10px; width:90px" nowrap="nowrap">
                	<div style="float:right" id="payment_button_holder_<?php echo $fee; ?>">
                    	<button id="add_<?php echo $fee; ?>" style="display:" class="input_button btn btn-primary btn-sm btn_fee_button add_fee">Add Bill</button>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>
        </form>
     </div>
</div>

<div id="settlement_list_all_done"></div>
<script language="javascript">
$( "#settlement_list_all_done" ).trigger( "click" );
</script>