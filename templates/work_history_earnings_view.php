<?php $form_name = "work_history_earnings"; ?>
<div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:500px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <div class="work_history work_history_earnings" id="work_history_panel">
        <form id="work_history_earnings_form" parsley-validate>
            <input id="table_name" name="table_name" type="hidden" value="work_history" />
            <input id="table_id" name="table_id" type="hidden" value="" />
            <input id="work_history_id" name="work_history_id" type="hidden" value="" />
            <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
                <?php 
                include("dashboard_view_navigation.php"); 
                ?>
            </div>
            <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">
            
                <ul style="margin-bottom:10px">
                    <li id="work_history_rateGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Pay Rate<br />($)</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_rateSave">
                            <a class="save_field" title="Click to save this field" id="work_history_rateSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="work_history_rateInput" id="work_history_rateInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px; width:55px" parsley-error-message="" />
                        <span id="work_history_rateSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
                    <li id="work_history_rateintervalGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Pay Interval</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_rateintervalSave">
                            <a class="save_field" title="Click to save this field" id="work_history_rateintervalSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <select name="work_history_rateintervalInput" id="work_history_rateintervalInput" class="input_class hidden" style="height:25px; width:100px; margin-top:-20px; margin-left:70px" tabindex="0">
                        	<option value="" selected="selected">Select from List</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Hourly">Hourly</option>
                        </select>
                        <span id="work_history_rateintervalSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
                    <li id="work_history_advantagesGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;" title="State value of tips, meals, lodging, or other advantages, regularly received $">Advantages<br />($)</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_advantagesSave">
                            <a class="save_field" title="Click to save this field" id="work_history_advantagesSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <input value="" name="work_history_advantagesInput" id="work_history_advantagesInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px; width:55px" parsley-error-message="" />
                        <span id="work_history_advantagesSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
                    <li id="work_history_advantageintervalGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                        <h6><div class="form_label_vert" style="margin-top:10px;">Advantages<br />Interval</div></h6>
                        <div style="float:right; margin-right:5px" class="hidden" id="work_history_advantageintervalSave">
                            <a class="save_field" title="Click to save this field" id="work_history_advantageintervalSaveLink">
                                <i class="glyphicon glyphicon-save"></i>
                            </a>
                        </div>
                        <select name="work_history_advantageintervalInput" id="work_history_advantageintervalInput" class="input_class hidden" style="height:25px; width:100px; margin-top:-35px; margin-left:70px" tabindex="0">
                        	<option value="" selected="selected">Select from List</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Hourly">Hourly</option>
                        </select>
                        <span id="work_history_advantageintervalSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:65px; border:0px solid black""></span>
                    </li>
               </ul>
                
            </div>        
        </form>
    </div>
</div>
<div id="work_history_earnings_done"></div>
<script language="javascript">
$("#work_history_earnings_done").trigger( "click" );
</script>