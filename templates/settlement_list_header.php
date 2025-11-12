<div class="gridster settlement_list_view settlement" id="gridster_settlement_list_<%= doi_id %>" style="display:">
     <div style="background:url(img/glass_injury.png) left top; padding:5px; width:1028px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;" class="col-md-6">
     <button id="settlement_show_<%= doi_id %>" title="Click to hide this Related Settlement" class="settlement_show btn btn-xs" style="display:none">Show Related Settlement</button>
    <form id="settlement_form_<%= doi_id %>" parsley-validate>
        <!--
        <input id="table_name" name="table_name" type="hidden" value="settlement" />
        <input id="table_id" name="table_id" type="hidden" value="<%= settlement_id %>" />
        <input id="settlement_id" name="settlement_id" type="hidden" value="<%= settlement_id %>" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />
        -->
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">  
            <div style="position:absolute; z-index:99; left:210px">
                <div style="margin-top:3px">
                    <!--
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
                    -->
                    <button id="settlement_hide_<%= doi_id %>" title="Click to hide this Related Settlement" class="settlement_hide btn btn-xs">Hide</button>
                </div>
            </div>          
           <div style="margin-bottom:10px">
               <div style="float:right;color:white; font-size:1.2em" id="related_doi_div">
               	<%=doi %>
               </div>
                <span style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;">Related Settlement</span>
           </div>
        </div>
        <ul id="related_settlement_<%= doi_id %>">
            <li id="attorneyGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Attorney</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="attorneySave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="attorneySaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <!--<input value="<%=attorney_full_name %>" type="hidden" id="attorney_full_name" />-->
            	<!--<input value="<%= attorney %>" name="attorneyInput" id="attorneyInput" class="kase settlement_list_view input_class hidden" placeholder="Attorney Who Settled" style="margin-top:-26px; margin-left:90px; width:125px" />-->
              <span id="attorneySpan" class="kase settlement_list_view span_perm form_span_vert" style="margin-top:-26px; margin-left:90px"><%= attorney_full_name %></span>
            </li>
            <li id="date_submittedGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="settlement_list gridster_border non_ssi_boxes" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Submitted On:</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="date_submittedSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_submittedSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <!--<input value="<%= date_submitted %>" name="date_submittedInput" id="date_submittedInput" class="kase settlement_list_view input_class date_input hidden" placeholder="Date Submitted" style="margin-top:-26px; margin-left:90px; width:125px" />-->
              <span id="date_submittedSpan" class="kase settlement_list_view span_perm form_span_vert" style="margin-top:-26px; margin-left:90px"><%= date_submitted %></span>
            </li>
            
            <li id="date_approvedGrid" data-row="1" data-col="3" data-sizex="1" data-sizey="1" class="settlement_list gridster_border non_ssi_boxes" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Approved On:</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="date_approvedSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_approvedSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <!--<input value="<%= date_approved %>" name="date_approvedInput" id="date_approvedInput" class="kase settlement_list_view input_class date_input hidden" placeholder="Date Approved" style="margin-top:-26px; margin-left:90px; width:125px" />-->
              <span id="date_approvedSpan" class="kase settlement_list_view span_perm form_span_vert" style="margin-top:-26px; margin-left:90px"><%= date_approved %></span>
            </li>
            <li id="amount_of_settlementGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Amount:</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="amount_of_settlementSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_of_settlementSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <!--<input type="text" value="<%= amount_of_settlement %>" name="amount_of_settlementInput" id="amount_of_settlementInput" class="kase settlement_list_view input_class hidden" placeholder="Amount of Settlement" style="margin-top:-26px; margin-left:90px; width:125px" />-->
                <span id="amount_of_settlementSpan" class="kase settlement_list_view span_perm form_span_vert" style="margin-top:-26px; margin-left:90px"><%= amount_of_settlement_span %></span>
            </li>
            <li id="pd_percentGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="settlement_list gridster_border non_ssi_boxes" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">% PD:</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="pd_percentSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="pd_percentSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <!--<input type="number" value="<%= pd_percent %>" name="pd_percentInput" id="pd_percentInput" class="kase settlement_list_view input_class hidden" placeholder="PD Percentage" style="margin-top:-26px; margin-left:90px; width:125px" />-->
                <span id="pd_percentSpan" class="kase settlement_list_view span_perm form_span_vert" style="margin-top:-26px; margin-left:90px"><%= pd_percent %></span>
            </li>
            
            <li id="future_medicalGrid" data-row="2" data-col="3" data-sizex="1" data-sizey="1" class="settlement_list gridster_border non_ssi_boxes" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Future Medical:</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="future_medicalSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="future_medicalSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <!--<input type="checkbox" value="Y" name="future_medicalInput" id="future_medicalInput" class="kase settlement_list_view input_class hidden" style="margin-top:-26px; margin-left:90px;" <% if (future_medical=="Y") { %>checked<% } %> />-->
                <span id="future_medicalSpan" class="kase settlement_list_view span_perm form_span_vert" style="margin-top:-26px; margin-left:90px"><%= future_medical %></span>
            </li>
            <li id="amount_of_feeGrid" data-row="1" data-col="3" data-sizex="2" data-sizey="1" class="settlement_list gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
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
                                <!-- <input type="text" value="<%= amount_of_fee %>" name="amount_of_feeInput" id="amount_of_feeInput" class="kase settlement_list_view input_class hidden" placeholder="Amount of Fee" style="margin-top:0px; margin-left:-7px; width:125px" /> -->
                                <span id="amount_of_feeSpan" class="kase settlement_list_view span_class form_span_vert" style="margin-top:0px; margin-left:-7px"><%= amount_of_fee_span %></span>
                            </td>
                            <td align="left" valign="top" width="50%">
                                <span style="color:white;padding-right:5px">Payment Status:&nbsp;</span>
                                <!-- <select id="fee_payment_statusInput" name="fee_payment_statusInput" class="input_class hidden" style="margin-top: 0px;margin-left: 10px;">
                                        <option value="">Select Status</option>
                                        <option value="Paid" <% if (fee_payment_status == "Paid") { %>selected<% } %>>Paid</option>
                                        <option value="Unpaid" <% if (fee_payment_status == "Unpaid") { %>selected<% } %>>Unpaid</option>
                                </select>   -->
                                <span id="fee_payment_statusSpan" class="white_text span_class" style="margin-top: 0px;margin-left: 10px;"><%= fee_payment_status %></span>
                            </td> 
                        </tr>
                    </table>
                </div>
            </li>
        </ul>
    </form>
    </div>
</div>
<div id="settlement_list_header_all_done" class="list_header_<%= settlement_id %>"></div>
<script language="javascript">
$( "#settlement_list_header_all_done" ).trigger( "click" );
</script>