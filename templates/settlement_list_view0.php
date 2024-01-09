<div class="gridster settlement_list_view settlement" id="gridster_settlement_list" style="display:">
     <div style="background:url(img/glass_info.png) left top; padding:5px; width:1328px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;" class="col-md-6">
    <form id="settlement_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="settlement" />
        <input id="table_id" name="table_id" type="hidden" value="<%= settlement_id %>" />
        <input id="settlement_id" name="settlement_id" type="hidden" value="<%= settlement_id %>" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "settlement_list"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
            <li id="attorneyGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Attorney</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="attorneySave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="attorneySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	<input value="<%=attorney_full_name %>" type="hidden" id="attorney_full_name" />
              <input value="<%= attorney %>" name="attorneyInput" id="attorneyInput" class="kase settlement_list_view input_class hidden" placeholder="Attorney Who Settled" style="margin-top:-26px; margin-left:90px; width:125px" />
              <span id="attorneySpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= attorney_full_name %></span>
            </li>
            
            <li id="date_submittedGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Submitted On:</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="date_submittedSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_submittedSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	<input value="<%= date_submitted %>" name="date_submittedInput" id="date_submittedInput" class="kase settlement_list_view input_class date_input hidden" placeholder="Date Submitted" style="margin-top:-26px; margin-left:90px; width:125px" />
              <span id="date_submittedSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= date_submitted %></span>
            </li>
            
            <li id="date_approvedGrid" data-row="1" data-col="3" data-sizex="1" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Approved On:</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="date_approvedSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_approvedSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	<input value="<%= date_approved %>" name="date_approvedInput" id="date_approvedInput" class="kase settlement_list_view input_class date_input hidden" placeholder="Date Approved" style="margin-top:-26px; margin-left:90px; width:125px" />
              <span id="date_approvedSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= date_approved %></span>
            </li>
            
            <li id="amount_of_settlementGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Amount:</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="amount_of_settlementSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_of_settlementSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	<input type="number" value="<%= amount_of_settlement %>" name="amount_of_settlementInput" id="amount_of_settlementInput" class="kase settlement_list_view input_class hidden" placeholder="Amount of Settlement" style="margin-top:-26px; margin-left:90px; width:125px" />
              <span id="amount_of_settlementSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= amount_of_settlement %></span>
            </li>
            
            <li id="pd_percentGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">% PD:</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="pd_percentSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="pd_percentSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	<input type="number" value="<%= pd_percent %>" name="pd_percentInput" id="pd_percentInput" class="kase settlement_list_view input_class hidden" placeholder="PD Percentage" style="margin-top:-26px; margin-left:90px; width:125px" />
              <span id="pd_percentSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= pd_percent %></span>
            </li>
            
            <li id="future_medicalGrid" data-row="2" data-col="3" data-sizex="1" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Future Medical:</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="future_medicalSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="future_medicalSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	<input type="checkbox" value="Y" name="future_medicalInput" id="future_medicalInput" class="kase settlement_list_view input_class hidden" style="margin-top:-26px; margin-left:90px;" <% if (future_medical=="Y") { %>checked<% } %> />
              <span id="future_medicalSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= future_medical %></span>
            </li>
		</ul>
    </form>
	</div>
</div>
<div style="height:20px; width:20px">&nbsp;</div>
<div class="settlement_list_fees settlement_fees" id="settlement_fees" style="display:">
     <div style="background:url(img/glass_dark.png) left top; padding:5px; width:1328px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;" class="col-md-6">
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

     	<table width="99%">
        	<tr>
            	<th align="left" valign="top">&nbsp;</th>
            	<th align="left" valign="top">Fee</th>
                <th align="left" valign="top">Due</th>
            	<th align="left" valign="top">Requested</th>
            	<th align="left" valign="top">Received</th>
                <th align="left" valign="top">&nbsp;</th>
            </tr>
            <?php
			$arrFees = array("Attorney", "Social Security", "Rehabilitation", "Deposition", "Other");
			foreach ($arrFees as $settle_fee) {
				$fee = strtolower(str_replace("Social Security", "ss", $settle_fee));
			?>
            <tr style="height:40px; border-top:1px solid white;">
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
                <td align="left" valign="top" style="padding-top:10px">
                	<textarea class="input_class" placeholder='Memo' id="memo_<?php echo $fee; ?>" name="memo_<?php echo $fee; ?>"   rows="2" cols="30"></textarea>
                    <span class="span_class hidden" id="memo_<?php echo $fee; ?>Span"></span>
                </td>
                <td align="left" valign="top" style="padding-top:10px" nowrap="nowrap">
                	<div style="float:right" id="payment_button_holder_<?php echo $fee; ?>">
                    	<button id="payment_<?php echo $fee; ?>" style="display:none" class="input_button btn btn-primary btn-sm payment">Add Payment</button>
                        <div style="display:inline-block">
                        	<button id="save_<?php echo $fee; ?>" style="display:none" class="input_button btn btn-success btn-sm fee_save">Save Fee</button>
                        </div>
                        <div style="display:inline-block">
                        	<button id="delete_<?php echo $fee; ?>" style="display:none; margin-top:5px" class="input_button btn btn-danger btn-sm fee_delete">Delete Fee</button>
                        </div>
                    </div>
                    <?php if ($fee=="attorney") { ?>
                	<select id="doctor_attorney" name="doctor_attorney" class="input_class">
                    </select>
                    <span class="span_class hidden" id="doctor_<?php echo $fee; ?>Span"></span>
                    <?php } ?>
                </td>
            </tr>
            <tr id="row_payments_holder_<?php echo $fee; ?>" style="display:none">
              <th align="left" valign="top">&nbsp;</th>
              <td colspan="5" align="left" valign="top" id="payments_holder_<?php echo $fee; ?>">&nbsp;</td>
            </tr>
            <?php } ?>
            <!--
            <tr>
            	<th align="left" valign="top">
                	Attorney
                	<input type="hidden" id="fee_id_attorney" name="fee_id_attorney" />
                </th>
            	<td align="left" valign="top">
                	<input type="number" class="input_class" style="width:75px" id="fee_attorney" name="fee_attorney" />
                    <input type="hidden" id="balance_attorney" />
                    <span class="span_class hidden" id="fee_attorneySpan"></span>
                </td>
            	<td align="left" valign="top">
                	<input value="" name="date_requested_attorney" id="date_requested_attorney" class="input_class date_input" placeholder="Date Requested" style="width:95px" />
                    
                </td>
            	<td align="left" valign="top"><input value="" name="date_received_attorney" id="date_received_attorney" class="input_class date_input" placeholder="Date Received" style="width:95px" /></td>
                <td align="left" valign="top">
                	<textarea class="input_class" placeholder='Memo' id="memo_attorney" name="memo_attorney"   rows="2" cols="30"></textarea>
                    <span class="span_class hidden" id="memo_attorneySpan"></span>
                </td>
                <td align="left" valign="top">
                	<div style="float:right" id="payment_button_holder_attorney">
                    	<button id="payment_attorney" style="display:none" class="input_class btn btn-primary btn-sm payment">Add Payment</button>
                        <button id="save_attorney" style="display:none" class="input_class btn btn-success btn-sm fee_save">Save Fee</button>
                        <button id="delete_attorney" style="display:none; margin-top:5px" class="input_class btn btn-danger btn-sm fee_delete">Delete Fee</button>
                    </div>
                	<select id="doctor_attorney" name="doctor_attorney">
                    </select>
                </td>
            </tr>
            <tr id="row_payments_holder_attorney" style="display:none">
              <th align="left" valign="top">&nbsp;</th>
              <td colspan="5" align="left" valign="top" id="payments_holder_attorney">&nbsp;</td>
            </tr>
            <tr><td colspan="7"><hr style="margin-top:3px" /></td></tr>
            <tr class="referring_settlement_row">
            	<th align="left" valign="top">Referral
                	<input type="hidden" id="fee_id_referral" name="fee_id_referral" />
                </th>
            	<td align="left" valign="top"><input type="number" class="input_class" style="width:75px" id="fee_referral" name="fee_referral" />
                    <input type="hidden" id="balance_referral" />
                </td>
            	<td align="left" valign="top"><input value="" name="date_requested_referral" id="date_requested_referral" class="input_class date_input" placeholder="Date Requested" style="width:95px" /></td>
            	<td align="left" valign="top"><input value="" name="date_received_referral" id="date_received_referral" class="input_class date_input" placeholder="Date Received" style="width:95px" /></td>
                <td align="left" valign="top"><textarea class="input_class" placeholder='Memo' id="memo_referral" name="memo_referral" rows="2" cols="30"></textarea></td>
                <td align="left" valign="top">
                	<div style="float:right" id="payment_button_holder_referral">
                    	<button id="payment_referral" style="display:none" class="input_class btn btn-primary btn-sm payment">Add Payment</button>
                        <button id="save_referral" style="display:none" class="input_class btn btn-success btn-sm fee_save">Save Fee</button>
                        <button id="delete_referral" style="display:none; margin-top:5px" class="input_class btn btn-danger btn-sm fee_delete">Delete Fee</button>
                    </div>
                    &nbsp;
                </td>
            </tr>
            <tr id="row_payments_holder_referral" style="display:none">
              <th align="left" valign="top">&nbsp;</th>
              <td colspan="5" align="left" valign="top" id="payments_holder_referral">&nbsp;</td>
            </tr>
            <tr class="referring_settlement_row">
            	<td colspan="7"><hr style="margin-top:3px" /></td>
            </tr>
            <tr class="prior_settlement_row">
            	<th align="left" valign="top">Prior Atty
                	<input type="hidden" id="fee_id_prior" name="fee_id_prior" />
                </th>
            	<td align="left" valign="top">
                	<input type="number" class="input_class" style="width:75px" id="fee_prior" name="fee_prior" />
                	<input type="hidden" id="balance_prior" />
                </td>
            	<td align="left" valign="top"><input value="" name="date_requested_prior" id="date_requested_prior" class="input_class date_input" placeholder="Date Requested" style="width:95px" /></td>
            	<td align="left" valign="top"><input value="" name="date_received_prior" id="date_received_prior" class="input_class date_input" placeholder="Date Received" style="width:95px" /></td>
                <td align="left" valign="top"><textarea class="input_class" placeholder='Memo' id="memo_prior" name="memo_prior" rows="2" cols="30"></textarea></td>
                <td align="left" valign="top">
                	<div style="float:right" id="payment_button_holder_prior">
                    	<button id="payment_prior" style="display:none" class="input_class btn btn-primary btn-sm payment">Add Payment</button>
                        <button id="save_prior" style="display:none" class="input_class btn btn-success btn-sm fee_save">Save Fee</button>
                        <button id="delete_prior" style="display:none; margin-top:5px" class="input_class btn btn-danger btn-sm fee_delete">Delete Fee</button>
                    </div>
                	&nbsp;
                </td>
            </tr>
            <tr class="prior_settlement_row">
            	<td colspan="7"><hr style="margin-top:3px" /></td>
            </tr>
            <tr>
            	<th align="left" valign="top">Social Security
                	<input type="hidden" id="fee_id_ss" name="fee_id_ss" />
                </th>
            	<td align="left" valign="top">
                	<input type="number" class="input_class" style="width:75px" id="fee_ss" name="fee_ss" />
                    <input type="hidden" id="balance_ss" />
                </td>
            	<td align="left" valign="top"><input value="" name="date_requested_ss" id="date_requested_ss" class="input_class date_input" placeholder="Date Requested" style="width:95px" /></td>
            	<td align="left" valign="top"><input value="" name="date_received_ss" id="date_received_ss" class="input_class date_input" placeholder="Date Received" style="width:95px" /></td>
                <td align="left" valign="top"><textarea class="input_class" placeholder='Memo' id="memo_ss" name="memo_ss" rows="2" cols="30"></textarea></td>
                <td align="left" valign="top">
                	<div style="float:right" id="payment_button_holder_ss">
                    	<button id="payment_ss" style="display:none" class="input_class btn btn-primary btn-sm payment">Add Payment</button>
                        <button id="save_ss" style="display:none" class="input_class btn btn-success btn-sm fee_save">Save Fee</button>
                        <button id="delete_ss" style="display:none; margin-top:5px" class="input_class btn btn-danger btn-sm fee_delete">Delete Fee</button>
                    </div>
                	&nbsp;
                </td>
            </tr>
            <tr id="row_payments_holder_ss" style="display:none">
              <th align="left" valign="top">&nbsp;</th>
              <td colspan="5" align="left" valign="top" id="payments_holder_ss">&nbsp;</td>
            </tr>
            <tr><td colspan="7"><hr style="margin-top:3px" /></td></tr>
            <tr>
            	<th align="left" valign="top">Rehabilitation
                	<input type="hidden" id="fee_id_rehab" name="fee_id_rehab" />
                </th>
            	<td align="left" valign="top">
                	<input type="number" class="input_class" style="width:75px" id="fee_rehab" name="fee_rehab" />
                    <input type="hidden" id="balance_rehab" />
                </td>
            	<td align="left" valign="top"><input value="" name="date_requested_rehab" id="date_requested_rehab" class="input_class date_input" placeholder="Date Requested" style="width:95px" /></td>
            	<td align="left" valign="top"><input value="" name="date_received_rehab" id="date_received_rehab" class="input_class date_input" placeholder="Date Received" style="width:95px" /></td>
                <td align="left" valign="top"><textarea class="input_class" placeholder='Memo' id="memo_rehab" name="memo_rehab" rows="2" cols="30"></textarea></td>
                <td align="left" valign="top">
                	<div style="float:right" id="payment_button_holder_rehab">
                    	<button id="payment_rehab" style="display:none" class="input_class btn btn-primary btn-sm payment">Add Payment</button>
                        <button id="save_rehab" style="display:none" class="input_class btn btn-success btn-sm fee_save">Save Fee</button>
                        <button id="delete_rehab" style="display:none; margin-top:5px" class="input_class btn btn-danger btn-sm fee_delete">Delete Fee</button>
                    </div>
                	&nbsp;
                </td>
            </tr>
            <tr id="row_payments_holder_rehab" style="display:none">
              <th align="left" valign="top">&nbsp;</th>
              <td colspan="5" align="left" valign="top" id="payments_holder_rehab">&nbsp;</td>
            </tr>
            <tr><td colspan="7"><hr style="margin-top:3px" /></td></tr>
            <tr>
            	<th align="left" valign="top">Deposition
                	<input type="hidden" id="fee_id_depo" name="fee_id_depo" />
                </th>
            	<td align="left" valign="top">
                	<input type="number" class="input_class" style="width:75px" id="fee_depo" name="fee_depo" />
                    <input type="hidden" id="balance_depo" />
                </td>
            	<td align="left" valign="top"><input value="" name="date_requested_depo" id="date_requestedd_depo" class="input_class date_input" placeholder="Date Requested" style="width:95px" /></td>
            	<td align="left" valign="top"><input value="" name="date_received_depo" id="date_received_depo" class="input_class date_input" placeholder="Date Received" style="width:95px" /></td>
                <td align="left" valign="top"><textarea class="input_class" placeholder='Memo' id="memo_depo" name="memo_depo" rows="2" cols="30"></textarea></td>
                <td align="left" valign="top">
                	<div style="float:right" id="payment_button_holder_depo">
                    	<button id="payment_depo" style="display:none" class="input_class btn btn-primary btn-sm payment">Add Payment</button>
                        <button id="save_depo" style="display:none" class="input_class btn btn-success btn-sm fee_save">Save Fee</button>
                        <button id="delete_depo" style="display:none; margin-top:5px" class="input_class btn btn-danger btn-sm fee_delete">Delete Fee</button>
                    </div>
                	&nbsp;
                </td>
            </tr>
            <tr id="row_payments_holder_depo" style="display:none">
              <th align="left" valign="top">&nbsp;</th>
              <td colspan="5" align="left" valign="top" id="payments_holder_depo">&nbsp;</td>
            </tr>
            <tr><td colspan="7"><hr style="margin-top:3px" /></td></tr>
            <tr>
            	<th align="left" valign="top">Other
                	<input type="hidden" id="fee_id_other" name="fee_id_other" />
                </th>
            	<td align="left" valign="top">
                	<input type="number" class="input_class" style="width:75px" id="fee_other" name="fee_other" />
                    <input type="hidden" id="balance_other" />
                </td>
            	<td align="left" valign="top"><input value="" name="date_requested_other" id="date_requested_other" class="input_class date_input" placeholder="Date Requested" style="width:95px" /></td>
            	<td align="left" valign="top"><input value="" name="date_received_other" id="date_received_other" class="input_class date_input" placeholder="Date Received" style="width:95px" /></td>
                <td align="left" valign="top"><textarea class="input_class" placeholder='Memo' id="memo_other" name="memo_other" rows="2" cols="30"></textarea></td>
                <td align="left" valign="top">
                	<div style="float:right" id="payment_button_holder_other">
                    	<button id="payment_other" style="display:none" class="input_class btn btn-primary btn-sm payment">Add Payment</button>
                        <button id="save_other" style="display:none" class="input_class btn btn-success btn-sm fee_save">Save Fee</button>
                        <button id="delete_other" style="display:none; margin-top:5px" class="input_class btn btn-danger btn-sm fee_delete">Delete Fee</button>
                    </div>
                	&nbsp;
                </td>
            </tr>
            -->
            <tr id="row_payments_holder_other" style="display:none">
              <th align="left" valign="top">&nbsp;</th>
              <td colspan="5" align="left" valign="top" id="payments_holder_other">&nbsp;</td>
            </tr>
        </table>
        </form>
     </div>
</div>
<div id="settlement_list_all_done"></div>
<script language="javascript">
$( "#settlement_list_all_done" ).trigger( "click" );
</script>