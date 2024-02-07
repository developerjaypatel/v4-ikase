<div id="settlement_sheet">
	<div id="holders_holder" style="float:right; width:45vw; background:url(img/glass_dark.png); padding:10px; display:none">
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
	<form id="settementsheet_form">
    	<input type="hidden" id="table_id" name="table_id" value="<%=id %>" />
        <div style="background:url(img/glass_dark.png) left top repeat-y; padding:5px; width:1010px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
             <div style="margin-top:0px; margin-right:10px; padding-top:5px">   
               <div style="margin-top:0px; margin-right:10px; padding-top:5px; position:relative">
               		<div style="position:absolute; z-index:99; left:157px" id="settlement_buttons_holder">
                    	<div style="margin-bottom:20px">
                            <button id="settlement_notes_button" title="Click to show Settlement Notes" class="settlement_button btn btn-xs">Settlement Notes</button>
                            &nbsp;
                            <button id="settlement_negotiation_button" title="Click to show Negotiations" class="settlement_button btn btn-xs">Negotiations</button>
                            &nbsp;
                            <button id="settlement_costs_button" title="Click to show case Costs Info" class="settlement_button btn btn-xs">Costs</button>	
                            &nbsp;
                            <button id="settlement_med_button" title="Click to show case Medical Summary" class="settlement_button btn btn-xs">Medical Summary</button>	
                            &nbsp;
                            <button id="settlement_subro_button" title="Click to show Settlement Subrogation Info" class="settlement_button btn btn-xs">Subrogation</button>
                            &nbsp;
                            <button id="settlement_deduct_button" title="Click to show Deductions" class="settlement_button btn btn-xs">Deductions</button>
                            &nbsp;
                            <button id="settlement_losses_button"  class="settlement_button btn btn-xs" title="Click to show the Losses Summary">Losses Summary</button>
                            &nbsp;
                            <button id="settlement_checkrequests_button"  class="settlement_button btn btn-xs" title="Click to show the Check Requests">Check Requests</button>
                        </div>
                    </div>          
                    <?php 
                    $form_name = "settlement_sheet"; 
                    include("dashboard_view_navigation.php"); 
                    ?>
                </div>
                <table width="100%" class="white_text" style="margin-top:20px">
                    <tr>
                      <th align="left" valign="top">&nbsp;</th>
                      <td align="center" valign="top" style="font-size:1.1em; font-weight:bold">Legal Fees 1</td>
                      <td align="center" valign="top" style="background: gray; font-size:1.1em; font-weight:bold; display:">
                      	<span  class="legal2">Legal Fees 2</span>
                        <a id="show_level_2" class="show_level" style="color:white; cursor:pointer; display:none">Addl Legal Fees</a>
                      </td>
                      <td align="center" valign="top" style="background:black; font-size:1.1em; font-weight:bold;display:"\>
                      	<span  class="legal3">Legal Fees 3</span>
                        <a id="show_level_3" class="show_level" style="color:white; cursor:pointer; display:none">More Legal Fees</a>
                        </td>
                      <td align="left" valign="top">&nbsp;</td>
                    </tr>
                    <tr>
                        <th align="left" valign="top">
                            Description
                        </th>
                        <td align="center" valign="top">
                            <div style="width:15px; display:inline-block">&nbsp;</div>
                            <input type="text" id="column_header_1" name="grossdesc1" style="width:170px; text-align:left" tabindex="1">
                        </td>
                        <td align="center" valign="top" bgcolor="gray" style="display:" class="legal2">                
                            <input type="text" id="column_header_2" name="grossdesc2" style="width:170px; text-align:left" tabindex="5">
                    </td>
                        <td align="center" valign="top" bgcolor="black" style="display:" class="legal3">
                          <input type="text" id="column_header_3" name="grossdesc3" style="width:170px; text-align:left" tabindex="9">
                        </td>
                        <td align="left" valign="top">&nbsp;</td>
                    </tr>
                    <tr>
                        <th align="left" valign="top">
                            Gross 
                        </th>
                      <td align="center" valign="top">
                            <div style="width:15px; display:inline-block">$</div>
                            <input type="number" step="0.01" min="0" value="0" id="gross_1" name="gross" style="width:170px; text-align:right" class="column_gross" tabindex="2">
                      </td>
                      <td align="center" valign="top" bgcolor="gray" style="display:" class="legal2">
                            
                            <input type="number" step="0.01" min="0" value="0" id="gross_2" name="gross2" style="width:170px; text-align:right" class="column_gross" tabindex="6">
                      </td>
                        <td align="center" valign="top" bgcolor="black" style="display:" class="legal3">
                            
                          <input type="number" step="0.01" min="0" value="0" id="gross_3" name="gross3" style="width:170px; text-align:right" class="column_gross" tabindex="10">
                        </td>
                        <td align="right" valign="top" style="font-size:1.2em">
                        <div style="width:15px; display:inline-block;">$</div>
                            <span id="total_gross"></span>
                        </td>
                    </tr>
                	<tr>
                        <th align="left" valign="top">
                           Fee 
                Pct</th>
                      <td align="center" valign="top">
                            <div style="width:15px; display:inline-block">%</div>
                            <input type="number" step="0.00001" min="0" value="0" id="column_percent_1" name="pct" style="width:170px; text-align:right" class="column_percent" tabindex="3">
                      </td>
                      <td align="center" valign="top" bgcolor="gray" style="display:" class="legal2">
                            
                            <input type="number" step="0.00001" min="0" value="0" id="column_percent_2" name="pct2" style="width:170px; text-align:right" class="column_percent" tabindex="7">
                      </td>
                        <td align="center" valign="top" bgcolor="black" style="display:" class="legal3">
                            
                          <input type="number" step="0.00001" min="0" value="0" id="column_percent_3" name="pct3" style="width:170px; text-align:right" class="column_percent" tabindex="11">
                        </td>
                        <th align="right" valign="top" style="text-align:right">Totals</th>
                    </tr>
                    <tr>
                        <th align="left" valign="top">
                        Legal Fees</th>
                      <td align="center" valign="top">
                            <div style="width:15px; display:inline-block">$</div>
                            <input type="number" step="0.01" min="0" value="0" id="fee_1" name="legalfees" style="width:170px; text-align:right" class="column_fee" tabindex="4">
                      </td>
                      <td align="center" valign="top" bgcolor="gray" style="display:" class="legal2">
                            
                            <input type="number" id="fee_2" name="legalfees2" style="width:170px; text-align:right" class="column_fee" tabindex="8">
                      </td>
                        <td align="center" valign="top" bgcolor="black" style="display:" class="legal3">
                            
                          <input type="number" step="0.01" min="0" value="0" id="fee_3" name="legalfees3" style="width:170px; text-align:right" class="column_fee" tabindex="12">
                        </td>
                        <td align="right" valign="top" style="color:white; font-size:1.2em">
                        <div style="width:15px; display:inline-block">-$</div>
                            <span id="total_fee"></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5"><hr style="height:3px"></td>
                    </tr>
                    <tr>
                        <th align="right" valign="top">
                            Medical Expenses
                        </th>
                        <td align="left" valign="top">
                            <div style="float:right; font-weight:bold">
                                Payments
                            </div>
                            $<span id="settlement_medical_expenses" style="font-size:1.2em; cursor:pointer" title="Click to Override"></span>
                            <input type="number" step="0.01" min="0" value="" id="total_medical_expenses" name="medtot" style="width:100px; text-align:right; display:none" class="medical_expense" />
                            <div id="medical_expense_override_indicator" style="float:left; margin-left:20px; background:red; color: white; font-size:0.8em; font-weight:bold; padding:3px; display:none">
                                OVERRIDE&nbsp;<input type="checkbox" id="medical_expense_override_checkbox" name="medtoto" value="1" />
                                <input type="hidden" value="" id="medical_expense_calc" />
                            </div>
                      </td>
                        <td align="left" valign="top">
                            <div style="float:right; font-weight:bold">
                                Adjustments
                            </div>
                            -$<span id="settlement_medical_payments" style="font-size:1.2em; cursor:pointer" title="Click to Override"></span>
                            <input type="number" step="0.01" min="0" value="" id="total_medical_payments" name="medpay" style="color:red; width:100px; text-align:right; display:none" class="medical_expense" />
                            <div id="medical_payment_override_indicator" style="float:left; margin-left:20px; background:red; color: white; font-size:0.8em; font-weight:bold; padding:3px; display:none">
                                OVERRIDE&nbsp;<input type="checkbox" id="medical_payment_override_checkbox" name="medpayo" value="1" />
                                <input type="hidden" value="" id="medical_payment_calc" />
                            </div>
                        </td>
                        <td align="left" valign="top">
                            <div style="float:right">
                            	<span style="font-weight:bold">
                                	<label id="medsumm_label" title="Click to review Medical Summary" style="cursor:pointer; text-decoration:underline;color:darkturquoise">Balance</label>
                                </span>
                          </div>
                            -$<span id="settlement_medical_adjustments" style="font-size:1.2em; cursor:pointer"></span>
                            <input type="number" step="0.01" min="0" value="" id="total_medical_adjustments" name="medadj" style="color:red; width:100px; text-align:right; display:none" class="medical_expense" />
                            <div id="medical_adjustment_override_indicator" style="float:left; margin-left:20px; background:red; color: white; font-size:0.8em; font-weight:bold; padding:3px; display:none">
                                OVERRIDE&nbsp;<input type="checkbox" id="medical_adjustment_override_checkbox" name="medadjo" value="1" />
                                <input type="hidden" value="" id="medical_adjustment_calc" />
                            </div>
                        </td>
                        <td align="right" valign="top" style="font-size:1.2em">
                            -$<span id="medicalSpan"></span>
                            <input type="hidden" value="" id="medical_calc" />
                        </td>
                  </tr>
                  <tr>
                        <td colspan="5"><hr style="height:3px"></td>
                    </tr>
                    <tr>
                        <td colspan="3">&nbsp;</td>
                        <th align="right" valign="top" style="text-align:right">
                            Refund
                        </th>
                        <td align="right" valign="top" style="font-size:1.2em">
                            $<input type="number" step="0.01" min="0" value="0" id="refund" name="reduction" class="column_subtotal" style="width:170px; text-align:right" />
                        </td>
                    </tr>
                    <tr>
                        <th colspan="3" style="border-top:1px solid white">
                        	Comments
                        </th>
                        <th align="right" valign="top" style="text-align:right">
                            <label id="cost_label" title="Click to review Costs" style="cursor:pointer; text-decoration:underline;color:darkturquoise">Cost</label>
                        </th>
                        <td align="right" valign="top" style="font-size:1.2em">
                           -$<input type="number" step="0.01" min="0" value="" id="cost" name="costs" style="color:red; width:170px; text-align:right; display:none" class="column_subtotal" />
                            <span id="costSpan" style="cursor:pointer" title="Click to Override Costs"></span>
                            <div id="cost_override_indicator" style="float:left; margin-left:20px; background:red; color: white; font-size:0.8em; font-weight:bold; padding:3px; display:none">
                                OVERRIDE&nbsp;<input type="checkbox" id="cost_override_checkbox" name="costo" value="1" />
                            </div>
                            <input type="hidden" value="" id="cost_calc" />
                            <div id="cost_instructions" style="display:none; font-size:0.8em; font-style:italic"><a id="cost_link" class="white_text" style="cursor:pointer; text-decoration:underline">Dbl-Click</a> for actual Costs</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" rowspan="2" align="left" valign="top">
                        	<textarea id="settlement_comments" name="settlement_comments" style="width:545px; height:60px"></textarea>
                        </td>
                        <th align="right" valign="top" style="text-align:right">
                            <label id="subro_label" title="Click to review Financials" style="cursor:pointer; text-decoration:underline;color:darkturquoise">Insurance Subrogation</label>
                        </th>
                        <td align="right" valign="top" style="font-size:1.2em" nowrap="nowrap">
                            -$<input type="number" step="0.01" min="0" value="" id="subrogation" name="subro" style="color:red; width:170px; text-align:right; display:none" class="column_subtotal" />
                            <span id="subrogationSpan" style="cursor:pointer" title="Click to Override Subrogations"></span>
                            <div id="subrogation_override_indicator" style="float:left; margin-left:20px; background:red; color: white; font-size:0.8em; font-weight:bold; padding:3px; display:none">
                                OVERRIDE&nbsp;<input type="checkbox" id="subrogation_override_checkbox" name="subroo" value="1" />
                            </div>
                            <input type="hidden" value="" id="subrogation_calc" />
                            <div id="subrogation_instructions" style="display:none; font-size:0.8em; font-style:italic"><a id="subrogation_link" class="white_text" style="cursor:pointer; text-decoration:underline">Dbl-Click</a> for actual Subrogations</div>
                        </td>
                    </tr>
                    <tr>
                        <th align="right" valign="top" style="text-align:right">
                            <label id="deduct_label" title="Click to review Deductions" style="cursor:pointer; text-decoration:underline;color:darkturquoise">Deductions</label>
                        </th>
                        <td align="right" valign="top" style="font-size:1.2em">
                            -$<input type="number" step="0.01" min="0" value="" id="deduction" name="other" style="color:red; width:170px; text-align:right; display:none" class="column_subtotal" />
                            <span id="deductionSpan" style="cursor:pointer" title="Click to Override Deductions"></span>
                            <div id="deduction_override_indicator" style="float:left; margin-left:20px; background:red; color: white; font-size:0.8em; font-weight:bold; padding:3px; display:none">
                                OVERRIDE&nbsp;<input type="checkbox" id="deduction_override_checkbox" name="othero" value="1" />
                            </div>
                            <input type="hidden" value="" id="deduction_calc" />
                            <div id="deduction_instructions" style="display:none; font-size:0.8em; font-style:italic"><a id="deduction_link" class="white_text" style="cursor:pointer; text-decoration:underline">Dbl-Click</a> for actual Deductions</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5"><hr style="height:3px"></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                        	<div style="display:none">
                                Referral Fee: 
                                <span style="font-size:1.2em">
                                    $ <input type="number" step="0.01" min="0" value="0" id="referral_fee" name="referral_fee" class="" style="width:110px; text-align:right" />
                                </span>
                            </div>
                            
                            <div>
                            	<h6>Referral Source</h6>
                                <div style="margin-left:95px; margin-top:-26px">
                                    <input id="referral_info" name="referral_info" type="hidden" value="" />
                                    <table width="98%" cellspacing="0" cellpadding="2">
                                        <tr>
                                            <td align="left" valign="top" width="33%" nowrap="nowrap">
                                                <input type="text" value="" id="referral_partie" class="kase settlement_list_view input_class" placeholder="Name of Referral" style="margin-top:0px; width:125px" />
                                                <span id="referral_sourceSpan" class="kase settlement_list_view span_class form_span_vert hidden" style="margin-top:0px"></span>
                                                <input id="referral_id" type="hidden" value="" />
                                                <button id="edit_referral" class="btn btn-sm btn-primary" role="button" style="display:none">Edit</button>
                                            </td>
                                            <td align="left" valign="top" width="33%">
                                                   Fee:&nbsp;$<input type="number" min="0.00" step="0.01" value="" id="referral_source_fee" class="kase settlement_list_view input_class" placeholder="Fee" style="width:115px; margin-top:0px" />
                                                  <span id="referral_feeSpan" class="kase settlement_list_view span_class form_span_vert hidden" style="margin-top:0px"></span>
                                            </td>
                                            <td align="left" valign="top" width="33%" nowrap="nowrap">
                                                   Date:&nbsp;<input type="date" value="" id="referral_source_date" class="kase settlement_list_view input_class" placeholder="Date" style="width:132px; margin-top:0px" />
                                                  <span id="referral_source_dateSpan" class="kase settlement_list_view span_class form_span_vert hidden" style="margin-top:0px"></span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                      	</td>
                        <th align="right" valign="top" style="text-align:right">
                            Total Due
                        </th>
                        <td align="right" valign="top" style="font-size:1.2em">
                            $<span id="total_due"></span>
                            <input type="hidden" id="total_due_input" name="due" />
                            <div style="margin-top:10px" id="statement_holder">
                            	<button class="btn btn-sm btn-primary" id="settlement_statement">Statement</button>
                                <div id="statement_feedback"></div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div style="background:url(img/glass_billing.png) left top repeat-y; padding:5px; width:1010px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-top:10px" id="settlement_dates_holder">
          <div style="margin-top:10px; margin-bottom:10px; margin-right:10px; padding-top:5px"> 
          		<div style="float:right; display:none" id="settlement_second_holder">
                	<button id="settlement_second_button" title="Click to freeze this Settlement and create a Second Settlement" class="settlement_second btn btn-sm">Create Second Settlement</button>
                </div>
                <div style="float:right; display:none" id="settlement_first_holder">
                	<button id="settlement_first_button" title="Click to look up the 1st Settlement" class="settlement_first btn btn-sm">Lookup 1st Settlement</button>
                </div>
                <div style="float:right; display:none" id="settlement_main_holder">
                	<button id="settlement_main_button" title="Click to look up the 2nd Settlement" class="settlement_main btn btn-sm">Lookup 2nd Settlement</button>
                </div>
          		<span style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;">
                    <span id="panel_title">Settlement Dates</span>
                </span>
             </div>
             <table width="100%" class="white_text">
                <tr>
                    <th width="25%" align="left">Settled</th>
                    <th width="25%" align="left" nowrap="nowrap">Draft Approved</th>
                    <th width="25%" align="left" nowrap="nowrap">Release Received</th>
                    <th width="25%" align="left" nowrap="nowrap">Approved for Distribution</th>
                </tr>
                <tr>
                    <td align="left">
                        <input type="date" id="settled_date" name="date_settled" class="settlement_date" tabindex="100" />
                    </td>
                    <td align="left"><input type="date" id="draft_approved_date" name="draftappr" class="settlement_date draft_date" tabindex="101" /></td>
                    <td align="left"><input type="date" id="release_received_date" name="release" class="settlement_date release_date" tabindex="103" /></td>
                    <td align="left"><input type="date" id="approved_distribution_date" name="distappr" class="settlement_date distribution_date" tabindex="105" /></td>
                </tr>
                <tr>
                  <th align="left" nowrap="nowrap"><span id="checkrequest_label" style="cursor:pointer; text-decoration:underline;color:darkturquoise; display:none">Check Requests</span></th>
                  <th align="left">Draft Received</th>
                  <th align="left" nowrap="nowrap">Release Returned</th>
                  <th align="left" nowrap="nowrap">Estimated Distribution</th>
                </tr>
                <tr>
                  <td rowspan="2" align="left" valign="top" id="checkrequest_feedback_cell">&nbsp;</td>
                  <td align="left" valign="top"><input type="date" id="draft_received_date" name="draftrcvd" class="settlement_date draft_date" tabindex="102" /></td>
                  <td align="left" valign="top"><input type="date" id="release_returned_date" name="relreturn" class="settlement_date release_date" tabindex="104" /></td>
                  <td align="left" valign="top"><input type="date" id="estimated_distribution_date" name="edistdate" class="settlement_date distribution_date" tabindex="106" /></td>
                </tr>
                <tr>
                  <th align="left" valign="top">&nbsp;</th>
                  <td align="left" valign="top">&nbsp;</td>
                  <th align="left" valign="top" nowrap="nowrap">Distributed</th>
                </tr>
                <tr>
                  <td align="left">&nbsp;</td>
                  <td align="left">&nbsp;</td>
                  <td align="left">&nbsp;</td>
                  <td align="left"><input type="date" id="distributed_date" name="distrib" class="settlement_date distribution_date" tabindex="107" /></td>
                </tr>
                <tr>
                  <td align="left">&nbsp;</td>
                  <td align="left">&nbsp;</td>
                  <td align="left">&nbsp;</td>
                  <td align="left">&nbsp;</td>
                </tr>
             </table>
        </div>
    </form>
</div>
<div id="settlementsheet_view_done"></div>
<script language="javascript">
$( "#settlementsheet_view_done" ).trigger( "click" );
</script>