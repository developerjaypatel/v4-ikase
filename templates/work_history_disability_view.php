<?php $form_name = "work_history_disability"; ?>
<div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:500px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <div class="work_history work_history_disability" id="work_history_panel">
        <form id="work_history_disability_form" parsley-validate>
            <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
                <?php 
                include("dashboard_view_navigation.php"); 
                ?>
            </div>
            <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">
            
                <ul style="margin-bottom:10px">
                    <li id="work_history_last_dayoffGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Last Day Off</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_last_dayoffSave">
                            <a class="save_field" title="Click to save this field" id="work_history_last_dayoffSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="work_history_last_dayoffInput" id="work_history_last_dayoffInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:70px; width:95px" parsley-error-message="" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" />
                        <span id="work_history_last_dayoffSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
                    <li id="disability_percent_totalGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">% Total</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="disability_percent_totalSave">
                            <a class="save_field" title="Click to save this field" id="disability_percent_totalSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="disability_percent_totalInput" id="disability_percent_totalInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:70px; width:95px" parsley-error-message="" />
                        <span id="disability_percent_totalSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
                    <li id="work_history_firstdisablityGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="2" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;" title="State value of tips, meals, lodging, or other advantages, regularly received $">1st Disability</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_firstdisablitySave">
                            <a class="save_field" title="Click to save this field" id="work_history_firstdisablitySaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <div>
                            <span style="margin-left:20px">Start:&nbsp;</span><input value="" name="work_history_firstdisablity_startInput" id="work_history_firstdisablity_startInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:0px; margin-left:10px; width:95px" parsley-error-message="" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" />
                            <span id="work_history_firstdisablity_startSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:0px; margin-left:10px; border:0px solid black""></span>
                        </div>
                        <div>
                            <span style="margin-left:20px">End:&nbsp;</span><input value="" name="work_history_firstdisablity_endInput" id="work_history_firstdisablity_endInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:0px; margin-left:15px; width:95px" parsley-error-message="" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" />
                            <span id="work_history_firstdisablity_endSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:0px; margin-left:10px; border:0px solid black""></span>
                        </div>
                    </li>
                    <li id="work_history_seconddisablityGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="2" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;" title="State value of tips, meals, lodging, or other advantages, regularly received $">2nd Disability</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_seconddisablitySave">
                            <a class="save_field" title="Click to save this field" id="work_history_seconddisablitySaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <div>
                            <span style="margin-left:20px">Start:&nbsp;</span><input value="" name="work_history_seconddisablity_startInput" id="work_history_seconddisablity_startInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:0px; margin-left:10px; width:95px" parsley-error-message="" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" />
                            <span id="work_history_seconddisablity_startSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:0px; margin-left:10px; border:0px solid black""></span>
                        </div>
                        <div>
                            <span style="margin-left:20px">End:&nbsp;</span><input value="" name="work_history_seconddisablity_endInput" id="work_history_seconddisablity_endInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:0px; margin-left:15px; width:95px" parsley-error-message="" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" />
                            <span id="work_history_seconddisablity_endSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:0px; margin-left:10px; border:0px solid black""></span>
                        </div>
                    </li>
                    <li id="prior_permanent_disabledGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Prior Permanently Disabled</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="prior_permanent_disabledSave">
                            <a class="save_field" title="Click to save this field" id="prior_permanent_disabledSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <textarea name="prior_permanent_disabledInput" id="prior_permanent_disabledInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-left:70px; width:330px; height:100px" parsley-error-message=""></textarea>
                        <span id="prior_permanent_disabledSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-left:65px; border:0px solid black""></span>
                    </li>
                    <li id="preexisting_disabilityGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Pre-Existing Disability</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="preexisting_disabilitySave">
                            <a class="save_field" title="Click to save this field" id="preexisting_disabilitySaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <textarea name="preexisting_disabilityInput" id="preexisting_disabilityInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-left:70px; width:330px; height:100px" parsley-error-message=""></textarea>
                        <span id="preexisting_disabilitySpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-left:65px; border:0px solid black""></span>
                    </li>
               </ul>
                
            </div>        
        </form>
    </div>
</div>
<div id="work_history_disability_done"></div>
<script language="javascript">
$("#work_history_disability_done").trigger( "click" );
</script>
