<div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:600px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
<div class="disability" id="disability_panel">
    <form id="disability_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="disability" />
    <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="" />
    <input id="disability_id" name="disability_id" type="hidden" value="" />
    <input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
    <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
		<?php 
        $form_name = "disability"; 
        include("dashboard_view_navigation.php"); 
        ?>
    </div>
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="display:none">
    
        <ul style="margin-bottom:10px">
            <li id="ailmentGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Ailment</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="ailmentSave">
                <a class="save_field" title="Click to save this field" id="ailmentSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%=ailment %>" name="ailmentInput" id="ailmentInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:85px; width:465px" parsley-error-message="Req" />
              <span id="ailmentSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:85px"><%=ailment %></span>
        </li>
        <li id="durationGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Duration</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="durationSave">
                <a class="save_field" title="Click to save this field" id="durationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%=duration %>" name="durationInput" id="durationInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:85px;" parsley-error-message="Req" />
              <span id="durationSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:85px"><%=duration %></span>
        </li>
        <li id="severityGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Severity</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="severitySave">
                <a class="save_field" title="Click to save this field" id="severitySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%=severity %>" name="severityInput" id="severityInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-28px; margin-left:85px" parsley-error-message="Req" />
              <span id="severitySpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:85px"><%=severity %></span>
        </li>
		<li id="limitsGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Limits</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="limitsSave">
                <a class="save_field" title="Click to save this field" id="limitsSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%=limits %>" name="limitsInput" id="limitsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:85px" />
              <span id="limitsSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:85px"><%=limits %></span>
        </li>
        <li id="dutyGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Work Duty</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="dutySave">
                <a class="save_field" title="Click to save this field" id="dutySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%=duty %>" name="dutyInput" id="dutyInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:85px" />
              <span id="dutySpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:85px"><%=duty %></span>
        </li>
        <li id="treatmentGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="2" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Treatment</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="treatmentSave">
                <a class="save_field" title="Click to save this field" id="treatmentSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
               <textarea name="treatmentInput" id="treatmentInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:85px; width:465px"><%=treatment %></textarea> 
              <span id="treatmentSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:85px"><%=treatment %></span>
        </li>
        <li id="descriptionGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="2" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Description</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="descriptionSave">
                <a class="save_field" title="Click to save this field" id="descriptionSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
               <textarea name="descriptionInput" id="descriptionInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:85px; width:465px"><%=description %></textarea> 
              <span id="descriptionSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:85px"><%=description %></span>
        </li>
       </ul>
    </div>
    </form>
</div></div>
<div id="disability_view_done"></div>
<script language="javascript">
$( "#disability_view_done" ).trigger( "click" );
</script>
