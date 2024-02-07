<?php $form_name = "financial"; ?>
<!--
<div class="glass_header">
    <div style="float:right">
    <a style="background:#CFF; color:black; padding:2px; cursor:pointer" id="search_qme" title="Click to search the EAMS database of QME Medical Provider">Import QMEs from EAMS</a>
    </div>
    <input id="case_id" name="case_id" type="hidden" value="<%=this.model.get('case_id') %>" />
    <input id="case_uuid" name="case_uuid" type="hidden" value="<%=this.model.get('uuid') %>" />

    
    <div style="display:inline-block">
        <div style="border:0px solid green; text-align:left">
            
            <div class="white_text" style="display:inline-block; padding-left:5px">
                <div style="float:right; display:none">
                    <span class='black_text'>&nbsp;|&nbsp;</span>
                </div>
                <span id="case_number_fill_in"></span><span id="adj_slot"><% if (this.model.get("adj_number")!="") { %><span class='black_text'>&nbsp;|&nbsp;</span><span id="adj_number_fill_in"></span><% } %></span><span class='black_text'>&nbsp;|&nbsp;</span><span id="case_type_fill_in"></span><span class='black_text'>&nbsp;|&nbsp;</span>Case&nbsp;Date:&nbsp;<span id="case_date_fill_in"></span><span class='black_text'>&nbsp;|&nbsp;</span>Claim&nbsp;#:&nbsp;<span id="claim_number_fill_in"></span><span id="claims_slot"><span class='black_text'>&nbsp;|&nbsp;</span>Claims&nbsp;:&nbsp;<span id="claims_fill_in"></span></span>
                <br />
                Status:&nbsp;<span id="case_status_fill_in"></span><% if (this.model.get("case_substatus")!="") { %><span class='white_text'>&nbsp;/&nbsp;</span><span class='white_text'><span id="case_substatus_fill_in"></span></span><% } %><% if (this.model.get("rating")!="") { %><span class='black_text'>&nbsp;|&nbsp;</span>Rating:&nbsp;<span id="rating_fill_in"></span><% } %><span id="language_slot"><% if (this.model.get("interpreter_needed")!="N") { %><span class='black_text'>&nbsp;|&nbsp;</span><span class="red_text white_background">Interpreter&nbsp;Needed&nbsp;for&nbsp;<span id="language_fill_in"></span></span><% } %></span>
            </div>
        </div> 
    </div>
</div>
<br/>
--> 
<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
<div style="width:85vw">
    <div style="position:absolute; left:450px; padding:5px; width:477px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px">
    	<div style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px; display:none;">Plaintiff</div>
        <div style="background:url(img/glass_card_dark_long_2.png) left top repeat-y; padding:5px; width:777px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; display:none;">
        <div class="financial" id="financial_panel">
            <form id="financial_form" parsley-validate>
            <input id="table_name" name="table_name" type="hidden" value="financial" />
            <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
            <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
            <input id="financial_id" name="financial_id" type="hidden" value="" />
            <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">  
                <ul style="margin-bottom:10px">
                    <li id="policy_limitsGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Policy Limit</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="policy_limitsSave">
                        <a class="save_field" title="Click to save this field" id="policy_limitsSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="policy_limitsInput" id="policy_limitsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:75px; width:274px" />
                      <span id="policy_limitsSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:85px"></span>
                </li>
                <li id="policy_limits_textGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">PL Text</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="policy_limits_textSave">
                        <a class="save_field" title="Click to save this field" id="policy_limits_textSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="policy_limits_textInput" id="policy_limits_textInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:75px; width:274px" />
                      <span id="policy_limits_textSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
                </li>
                <li id="financial_umGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:750px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">UM</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="financial_umSave">
                        <a class="save_field" title="Click to save this field" id="financial_umSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <div style="margin-top:15px; margin-left:-10px">
                    <select name="financial_um_select_Input" id="financial_um_select_Input" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-32px; margin-left:75px">
                        <option value=""></option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                    <span id="financial_um_select_Span" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-32px; margin-left:75px"></span>
                    <input value="" name="financial_umInput" id="financial_umInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:165px; width:150px;-webkit-appearance:;" placeholder="Limit/Paid" />
                    <span id="financial_umSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-32px; margin-left:55px"></span>
                    </div>
                </li>
                <li id="med_payGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:750px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Med Pay</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="med_paySave">
                        <a class="save_field" title="Click to save this field" id="med_paySaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                        <div style="margin-top:15px; margin-left:-10px">
                        <select name="med_pay_select_Input" id="med_pay_select_Input" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-32px; margin-left:75px">
                        	<option value=""></option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <span id="med_pay_select_Span" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-32px; margin-left:75px"></span>
                        <input value="" name="med_payInput" id="med_payInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:165px; width:150px;-webkit-appearance:;" placeholder="Limit/Paid" />
                        &nbsp;
                            <table width="60%" border="0" cellspacing="0" cellpadding="0" style="margin-left:350px; margin-top:-46px;">
                              <tr>
                                <td>Billed:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td style="width:25%"><input value="" name="med_pay_billedInput" id="med_pay_billedInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="width:100px; margin-left:-35px" /></td>
                                <td>Exhausted:&nbsp;</td>
                                <td style="width:25%" align="left"><input value="" name="med_pay_exhaustedInput" id="med_pay_exhaustedInput" class="<?php echo $form_name; ?> input_class hidden" type="checkbox" style="margin-left:-280px; margin-top:2px" />&nbsp;</td>
                              </tr>
                            </table>
                        
                        </div>
                      
                      <span id="med_paySpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"></span>
                </li>
                <li id="financial_subroGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:750px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Subro</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="financial_subroSave">
                        <a class="save_field" title="Click to save this field" id="financial_subroSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                        <div style="margin-top:15px; margin-left:-10px">
                        <select name="financial_subro_select_Input" id="financial_subro_select_Input" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-32px; margin-left:75px">
                        	<option value=""></option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <span id="financial_subro_select_Span" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-32px; margin-left:75px"></span>
                        <input value="" name="financial_subroInput" id="financial_subroInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:165px; width:150px;-webkit-appearance:;" />&nbsp;
                            <table width="58%" border="0" cellspacing="0" cellpadding="0" style="margin-left:350px; margin-top:-46px;">
                              <tr>
                                <td>Reduced:</td>
                                <td style="width:25%"><input value="" name="med_pay_billedInput" id="med_pay_billedInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="width:80px; margin-left:-35px" /></td>
                                <td>Balance:</td>
                                <td style="width:25%" align="left"><input value="" name="med_pay_billedInput" id="med_pay_billedInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="width:80px; margin-left:-35px; margin-top:2px" />&nbsp;</td>
                              </tr>
                            </table>
                        </div>
                      
                      <span id="financial_subroSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"></span>
                </li>
                <li id="non_subroGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:750px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Non-Subro</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="non_subroSave">
                        <a class="save_field" title="Click to save this field" id="non_subroSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                        <div style="margin-top:15px; margin-left:-10px">
                        <span style="width:40px;">
                        
                        </span>
                        <input value="" name="non_subroInput" id="non_subroInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:165px; width:150px;-webkit-appearance:;" />
                        </div>
                      
                      <span id="non_subroSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"></span>
                </li>
               </ul>
                
            </div>
            
            
            </form>
        </div>
        </div>
        <div>&nbsp;</div>
        <div style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px; display:none">Defendant</div>
        <div style="background:url(img/glass_card_dark_long_1.png) left top repeat-y; padding:5px; width:777px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; display:none;">
            <div class="financial defendant_financial" id="defendant_financial_panel">
                <form id="defendant_financial_form" parsley-validate>
                <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
                    <span id="panel_title" style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;"><?php echo ucwords(str_replace("_", " ", $form_name)); ?></span>
                </div>
                <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">    
                    <ul style="margin-bottom:10px">
                        <li id="defendant_policy_limitsGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Policy Limit</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="defendant_policy_limitsSave">
                            <a class="save_field" title="Click to save this field" id="defendant_policy_limitsSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="defendant_policy_limitsInput" id="defendant_policy_limitsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:75px; width:274px" />
                          <span id="defendant_policy_limitsSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:85px"></span>
                    </li>
                    <li id="defendant_policy_limits_textGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                        <h6><div class="form_label_vert" style="margin-top:10px;">PL Text</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="defendant_policy_limits_textSave">
                            <a class="save_field" title="Click to save this field" id="defendant_policy_limits_textSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                          <input value="" name="defendant_policy_limits_textInput" id="defendant_policy_limits_textInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:75px; width:274px" />
                          <span id="defendant_policy_limits_textSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
                    </li>
                    <li id="defendant_financial_umGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:750px">
                        <h6><div class="form_label_vert" style="margin-top:10px;">UM</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="defendant_financial_umSave">
                            <a class="save_field" title="Click to save this field" id="defendant_financial_umSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <div style="margin-top:15px; margin-left:-10px">
                        <select name="defendant_financial_um_select_Input" id="defendant_financial_um_select_Input" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-32px; margin-left:75px">
                            <option value=""></option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <span id="defendant_financial_um_select_Span" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-32px; margin-left:75px"></span>
                        <input value="" name="defendant_financial_umInput" id="defendant_financial_umInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:165px; width:150px;-webkit-appearance:;" placeholder="Limit/Paid" />
                        <span id="defendant_financial_umSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-32px; margin-left:55px"></span>
                        </div>
                    </li>
                    <li id="defendant_med_payGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:750px">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Med Pay</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="defendant_med_paySave">
                            <a class="save_field" title="Click to save this field" id="defendant_med_paySaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                            <div style="margin-top:15px; margin-left:-10px">
                            <select name="defendant_med_pay_select_Input" id="defendant_med_pay_select_Input" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-32px; margin-left:75px">
                        	<option value=""></option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <span id="defendant_med_pay_select_Span" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-32px; margin-left:75px"></span>
                        <input value="" name="defendant_med_payInput" id="defendant_med_payInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:165px; width:150px;-webkit-appearance:;" placeholder="Limit/Paid" />
                            &nbsp;
                                <table width="60%" border="0" cellspacing="0" cellpadding="0" style="margin-left:350px; margin-top:-46px;">
                                  <tr>
                                    <td>Billed:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="width:25%"><input value="" name="defendant_med_pay_billedInput" id="defendant_med_pay_billedInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="width:100px; margin-left:-35px" /></td>
                                    <td>Exhausted:&nbsp;</td>
                                    <td style="width:25%" align="left"><input value="" name="defendant_med_pay_exhaustedInput" id="defendant_med_pay_exhaustedInput" class="<?php echo $form_name; ?> input_class hidden" type="checkbox" style="margin-left:-280px; margin-top:2px" />&nbsp;</td>
                                  </tr>
                                </table>
                            
                            </div>
                          
                          <span id="defendant_med_paySpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"></span>
                    </li>
                    <li id="defendant_financial_subroGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:750px">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Subro</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="defendant_financial_subroSave">
                            <a class="save_field" title="Click to save this field" id="defendant_financial_subroSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                            <div style="margin-top:15px; margin-left:-10px">
                            <select name="defendant_financial_subro_select_Input" id="defendant_financial_subro_select_Input" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-32px; margin-left:75px">
                        	<option value=""></option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <span id="defendant_financial_subro_select_Span" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-32px; margin-left:75px"></span>
                        <input value="" name="defendant_financial_subroInput" id="defendant_financial_subroInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:165px; width:150px;-webkit-appearance:;" />&nbsp;
                                <table width="58%" border="0" cellspacing="0" cellpadding="0" style="margin-left:350px; margin-top:-46px;">
                                  <tr>
                                    <td>Reduced:</td>
                                    <td style="width:25%"><input value="" name="defendant_financial_subro_billedInput" id="defendant_financial_subro_billedInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="width:80px; margin-left:-35px" /></td>
                                    <td>Balance:</td>
                                    <td style="width:25%" align="left"><input value="" name="defendant_financial_subro_billedInput" id="defendant_financial_subro_billedInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="width:80px; margin-left:-35px; margin-top:2px" />&nbsp;</td>
                                  </tr>
                                </table>
                            </div>
                          
                          <span id="defendant_financial_subroSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"></span>
                    </li>
                    <li id="defendant_non_subroGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:750px">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Non-Subro</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="defendant_non_subroSave">
                            <a class="save_field" title="Click to save this field" id="defendant_non_subroSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                            <div style="margin-top:15px; margin-left:-10px">
                            <span style="width:40px;">
                            
                            </span>
                            <input value="" name="defendant_non_subroInput" id="defendant_non_subroInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:165px; width:150px;-webkit-appearance:;" />
                            </div>
                          
                          <span id="defendant_non_subroSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"></span>
                    </li>
                   </ul>
                    
                </div>
                
                
                </form>
            </div>
        </div>
    </div>
    <div class="financial defendant_financial" id="escrow_panel" style="background:url(img/glass_info.png) left top repeat-y; width:390px">
        <form id="escrow_form" parsley-validate>
        <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
            <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
                <?php 
                include("dashboard_view_navigation.php"); 
                ?>
            </div>
        </div>
        
        <div class="gridster escrow" id="gridster_<?php echo $form_name; ?>" style="">  
            <ul style="margin-bottom:10px">
            	<li id="bank_selectGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Bank</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="bank_selectSave">
                        <a class="save_field" title="Click to save this field" id="bank_selectSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <select value="" name="bank_selectInput" id="bank_selectInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:125px; width:230px">
                      	<%=trust_options.join("") %>
                      </select>
                      <span id="bank_selectSpan" class="kase bank_select span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
                </li>
                <!--
                <li id="bank_selectGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Bank</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="bank_selectSave">
                        <a class="save_field" title="Click to save this field" id="bank_selectSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="bank_selectInput" id="bank_selectInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:75px; width:274px" />
                      <span id="bank_selectSpan" class="kase bank_select span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
                </li>
                <li id="escrow_accountGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Account</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="escrow_accountSave">
                        <a class="save_field" title="Click to save this field" id="escrow_accountSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input value="" name="escrow_accountInput" id="escrow_accountInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:75px; width:274px" />
                      <span id="escrow_accountSpan" class="kase escrow_account span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
                </li>
                -->
                <li id="escrow_amountGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Amount</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="escrow_amountSave">
                        <a class="save_field" title="Click to save this field" id="escrow_amountSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <input type="number" value="" name="escrow_amountInput" id="escrow_amountInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:125px; width:130px" />
                      <span id="escrow_amountSpan" class="kase escrow_amount span_class form_span_vert" style="margin-top:-26px; margin-left:125px"></span>
                </li>
                <li id="billing_methodGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Billing Method</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="billing_methodSave">
                        <a class="save_field" title="Click to save this field" id="billing_methodSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                   	<div style="margin-top:-19px; margin-left:125px" class="input_class hidden">
                    	<select name="billing_methodInput" id="billing_methodInput" class="<?php echo $form_name; ?> input_class hidden" style="width:130px">
                        	<option value="">Select from list...</option>
                            <option value="Hourly">Hourly</option>
                            <option value="Flat Fee">Flat Fee</option>
                            <option value="Contigency">Contigency</option>
                        </select>
                	</div>
                    <span id="billing_methodSpan" class="kase billing_method span_class form_span_vert" style="margin-top:-29px; margin-left:125px"></span>
                </li>
                <li id="statute_limitationGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Statute of Limitations</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="statute_limitationSave">
                        <a class="save_field" title="Click to save this field" id="statute_limitationSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                   	<div style="margin-top:-19px; margin-left:125px" class="input_class hidden">
                    	<select name="statute_limitationInput" id="statute_limitationInput" class="<?php echo $form_name; ?> input_class hidden" style="width:130px">
                        	<option value="">Select from list...</option>
                            <option value="Do not expire">Do not expire</option>
                            <option value="Expire on">Expire on</option>
                        </select>
                	</div>
                    <span id="statute_limitationSpan" class="kase statute_limitation span_class form_span_vert" style="margin-top:-29px; margin-left:125px"></span>
                </li>
                <li id="statute_dateGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px; display:none">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Statute Date</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="statute_dateSave">
                        <a class="save_field" title="Click to save this field" id="statute_dateSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                   	<div style="margin-top:-19px; margin-left:125px" class="input_class hidden">
                    	<input type="text" value="" name="statute_dateInput" id="statute_dateInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="width:130px" />
                	</div>
                    <span id="statute_dateSpan" class="kase statute_date span_class form_span_vert" style="margin-top:-19px; margin-left:125px"></span>
                </li>
            </ul>
        </div>
        </form>
    </div>
    
</div>
<%=prefix %>
<% if (prefix=="") { %>
<div id="financial_done"></div>
<script language="javascript">
$("#financial_done").trigger( "click" );
</script>
<% } %>
