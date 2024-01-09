<?php $form_name = "work_history_compensation"; ?>
<div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:500px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <div class="work_history work_history_compensation" id="work_history_panel">
        <form id="work_history_compensation_form" parsley-validate>
            <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
                <?php 
                include("dashboard_view_navigation.php"); 
                ?>
            </div>
            <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">
            
                <ul style="margin-bottom:10px">
                	<li id="work_history_was_paidGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Compensation Paid?</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_was_paidSave">
                            <a class="save_field" title="Click to save this field" id="work_history_was_paidSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <select name="work_history_waspaidInput" id="work_history_waspaidInput" class="input_class hidden" style="height:25px; width:100px; margin-top:-20px; margin-left:70px" tabindex="0">
                        	<option value="" selected="selected">Select from List</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <span id="work_history_waspaidSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:105px; border:0px solid black""></span>
                    </li>
                    <li id="work_history_total_paidGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Total Paid</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_total_paidSave">
                            <a class="save_field" title="Click to save this field" id="work_history_total_paidSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="work_history_total_paidInput" id="work_history_total_paidInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:70px; width:135px" parsley-error-message="" />
                        <span id="work_history_total_paidSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
                    <li id="work_history_weekly_rateGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Weekly Rate(s)</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_weekly_rateSave">
                            <a class="save_field" title="Click to save this field" id="work_history_weekly_rateSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="work_history_weekly_rateInput" id="work_history_weekly_rateInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:70px; width:135px" parsley-error-message="" />
                        <span id="work_history_weekly_rateSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:105px; border:0px solid black""></span>
                    </li>
                    <li id="work_history_last_paymentGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Last Payment</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_last_paymentSave">
                            <a class="save_field" title="Click to save this field" id="work_history_last_paymentSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="work_history_last_paymentInput" id="work_history_last_paymentInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:70px; width:135px" parsley-error-message="" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" />
                        <span id="work_history_last_paymentSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
                    <li id="work_history_insurance_benefitsGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Ins. Benefits?</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_insurance_benefitsSave">
                            <a class="save_field" title="Click to save this field" id="work_history_insurance_benefitsSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <select name="work_history_insurance_benefitsInput" id="work_history_insurance_benefitsInput" class="input_class hidden" style="height:25px; width:100px; margin-top:-20px; margin-left:70px" tabindex="0">
                        	<option value="" selected="selected">Select from List</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <span id="work_history_insurance_benefitsSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:105px; border:0px solid black""></span>
                    </li>
                    <li id="previous_claim_numberGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Previous Case #</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="previous_claim_numberSave">
                            <a class="save_field" title="Click to save this field" id="previous_claim_numberSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="previous_claim_numberInput" id="previous_claim_numberInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:70px; width:135px" parsley-error-message="" />
                        <span id="previous_claim_numberSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
                    <li id="SSD_benefitsGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">SSD Date</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="previous_claim_numberSave">
                            <a class="save_field" title="Click to save this field" id="previous_claim_numberSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="SSD_benefitsInput" id="SSD_benefitsInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:70px; width:135px" parsley-error-message="" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" />
                        <span id="previous_claim_numberSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
                    <li id="SSD_paymentsGrid" data-row="4" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">SSD Pay</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="previous_claim_numberSave">
                            <a class="save_field" title="Click to save this field" id="previous_claim_numberSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="SSD_paymentsInput" id="SSD_paymentsInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:70px; width:135px" parsley-error-message="" placeholder="" />
                        <span id="previous_claim_numberSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
               </ul>
                
            </div>        
        </form>
    </div>
</div>
<div id="work_history_compensation_done"></div>
<script language="javascript">
$("#work_history_compensation_done").trigger( "click" );
</script>
