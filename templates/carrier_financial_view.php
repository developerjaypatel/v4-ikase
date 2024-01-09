<?php $form_name = "financial"; ?>

<div style="background:url(img/glass_card_dark_long_2.png) left top repeat-y; padding:5px; width:777px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
	<div style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;" id="section_title_<%=corporation_id %>">
    	<div style="float:right; display:none" id="distributed_status_holder">
        	<span style="color:lime;">DISTRIBUTED &#10003;</span>
            &nbsp;
            <span style="font-style:italic; color:white; font-size:0.8em">(No changes allowed)</span>
        </div>
    	Financial
    </div>
    <div class="financial" id="financial_panel">
        <form id="financial_form" parsley-validate>
        <button class="save hidden" style="width:20px; border:0px solid; display:none"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></button>
        <input id="table_name" name="table_name" type="hidden" value="financial" />
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
                <span id="financial_umSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:175px"></span>
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
                            <td style="width:25%">
                            	<input value="" name="med_pay_billedInput" id="med_pay_billedInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="width:100px; margin-left:-35px" />
                                <span id="med_pay_billedSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-12px; margin-left:-35px"></span>
                            </td>
                            <td>Exhausted:&nbsp;</td>
                            <td style="width:25%" align="left">
                            	<input value="Y" name="med_pay_exhaustedInput" id="med_pay_exhaustedInput" class="<?php echo $form_name; ?> input_class hidden" type="checkbox" style="margin-left:-50px; margin-top:-10px; width:20px" />
                                <span id="med_pay_exhaustedSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-12px; margin-left:-50px"></span>
                             </td>
                          </tr>
                        </table>
                    
                    </div>
                  
                  <span id="med_paySpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:165px"></span>
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
                    <input value="" name="financial_subroInput" id="financial_subroInput" class="<?php echo $form_name; ?> input_class financial_subro hidden" type="number" step="0.01" min="0" style="margin-top:-26px; margin-left:165px; width:150px;-webkit-appearance:;" />&nbsp;
                        <table width="58%" border="0" cellspacing="0" cellpadding="0" style="margin-left:350px; margin-top:-46px;">
                          <tr>
                            <td>Reduced:</td>
                            <td style="width:25%">
                            	<input value="" name="reducedInput" id="reducedInput" class="<?php echo $form_name; ?> input_class financial_subro hidden" type="number" step="0.01" min="0" style="width:80px; margin-left:-35px" />
                                <span id="reducedSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-12px; margin-left:-35px"></span>
                            </td>
                            <td>Balance:</td>
                            <td style="width:25%" align="left">
                            	<input value="" name="balanceInput" id="balanceInput" class="<?php echo $form_name; ?> input_class hidden" type="number" step="0.01" min="0" style="width:80px; margin-left:-35px; margin-top:-10px; display:none" />
                                <span id="balanceSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-12px; margin-left:-35px"></span>
                             </td>
                          </tr>
                        </table>
                    </div>
                  
                  <span id="financial_subroSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:165px"></span>
                <!--
                <div>
                	<div id="override_indicator" style="float:right; background:white; color: red; font-weight:bold; padding:3px; display:none">
                    	OVERRIDE
                    </div>
                    <h6><div class="form_label_vert" style="margin-top:10px;">Override</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="financial_subro_overrideSave">
                        <a class="save_field" title="Click to save this field" id="financial_subro_overrideSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                        <div style="margin-top:15px; margin-left:-10px">
                        <select name="financial_subro_override_select_Input" id="financial_subro_override_select_Input" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-32px; margin-left:75px">
                            <option value=""></option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <span id="financial_subro_override_select_Span" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-32px; margin-left:75px"></span>
                        <input value="" name="financial_subro_overrideInput" id="financial_subro_overrideInput" class="<?php echo $form_name; ?> input_class financial_subro_override hidden" type="number" step="0.01" min="0" style="margin-top:-26px; margin-left:165px; width:150px;-webkit-appearance:;" />&nbsp;
                        </div>
                      
                      <span id="financial_subro_overrideSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-50px; margin-left:165px"></span>
                </div>
                -->
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
                  
                  <span id="non_subroSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:165px"></span>
            </li>
           </ul>
            
        </div>
        
        
        </form>
    </div>
</div>
<div id="carrier_financial_done"></div>
<script language="javascript">
$("#carrier_financial_done").trigger( "click" );
</script>