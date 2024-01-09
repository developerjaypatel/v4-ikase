<?php $form_name = "coa"; ?>
<div class="coa" id="coa_panel">
    <form id="coa_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="coa" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
    <input id="coa_id" name="coa_id" type="hidden" value="" />
    <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
    <input id="new_legal_id" name="new_legal_id" type="hidden" value="<%= new_legal_id %>" />
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">
    <table width="100%" border="0" cellspacing="3" cellpadding="3">
      <tr>
        <td valign="top">COA Type</td>
        <td valign="top">
          <select name="coa_typeInput" id="coa_typeInput" class="<?php echo $form_name; ?> input_class" style="margin-left:70px; margin-top:-24px; width:385px">
            <option value="">Choose One</option>
            <option value="breach_contract">Contract Breach</option>
            <option value="erisha">ERISHA</option>
            <option value="feha">FEHA</option>
            <option value="general_advice">General Advice</option>
            <option value="ltd">LTD</option>
            <option value="title_seven">Title VII</option>
            <option value="tort">Tort</option>
            <option value="wrongful_term">Wrongful Term</option>
            </select>
        </td>
        </tr>
      <tr>
        <td valign="top">Disposition</td>
        <td valign="top">
          <div style="margin-top:-10px; margin-left:70px;">
            Open
            <input name="dispositionInput" id="disposition_openInput" class="<?php echo $form_name; ?> input_class" type="radio" value="Open" style="width:20px; margin-top:10px" />
            Closed
            <input name="dispositionInput" id="disposition_closedInput" class="<?php echo $form_name; ?> input_class" type="radio" value="Closed" style="width:20px; margin-top:10px" />
            </div>
        </td>
        </tr>
      <tr>
        <td valign="top">Explain</td>
        <td valign="top"><textarea name="disposition_explanationInput" id="disposition_explanationInput" class="<?php echo $form_name; ?> input_class" style="margin-top:0px; margin-left:70px; width:385px;" rows="4"></textarea></td>
        </tr>
      <tr>
        <td valign="top">Resolution</td>
        <td valign="top">
          <div style="margin-top:-10px; margin-left:70px;">
            Settled
            <input name="resolutionInput" id="resolution_settledInput" class="<?php echo $form_name; ?> input_class" type="radio" value="Settled" style="width:20px; margin-top:10px" />
            Other
            <input name="resolutionInput" id="resolution_otherInput" class="<?php echo $form_name; ?> input_class" type="radio" value="Other" style="width:20px; margin-top:10px" />
            None
            <input name="resolutionInput" id="resolution_noneInput" class="<?php echo $form_name; ?> input_class" type="radio" value="None" style="width:20px; margin-top:10px" />
            </div>
        </td>
        </tr>
      <tr>
        <td valign="top">Explain</td>
        <td valign="top"><textarea name="resolution_explanationInput" id="resolution_explanationInput" class="<?php echo $form_name; ?> input_class" placeholder="" style="margin-top:0px; margin-left:70px; width:385px;" rows="4"></textarea></td>
        </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        </tr>
    </table>

      <!--  <ul style="margin-bottom:10px">
        <li id="coa_typeGrid" data-row="4" data-col="2" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">COA Type</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="coa_typeSave">
                <a class="save_field" title="Click to save this field" id="coa_typeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <select name="coa_typeInput" id="coa_typeInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-left:70px; margin-top:-24px; width:145px">
                <option value="">Choose One</option>
                <option value="breach_contract">Contract Breach</option>
                <option value="erisha">ERISHA</option>
                <option value="feha">FEHA</option>
                <option value="general_advice">General Advice</option>
                <option value="ltd">LTD</option>
                <option value="title_seven">Title VII</option>
                <option value="tort">Tort</option>
                <option value="wrongful_term">Wrongful Term</option>
              </select>
          <span id="coa_typeSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
              
        </li>
		<li id="dispositionGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Disposition</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="dispositiondateSave">
                <a class="save_field" title="Click to save this field" id="dispositionSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="dispositionInput" id="dispositionInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px; width:385px" />
              <span id="dispositionSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
        </li>
        <li id="disposition_explanationGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Explination</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="disposition_explanationSave">
                <a class="save_field" title="Click to save this field" id="disposition_explanationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="disposition_explanationInput" id="disposition_explanationInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px; width:385px" />
              <span id="disposition_explanationSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
        </li>
        
        
        <li id="resolutionGrid" data-row="7" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">Resolution</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="resolutionSave">
            <a class="save_field" title="Click to save this field" id="resolutionSaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
                <input value="" name="resolutionInput" id="resolutionInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:70px; width:385px" />
          <span id="resolutionSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
        </li>
		<li id="resolution_explanationGrid" data-row="8" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Explination</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="resolution_explanationSave">
                <a class="save_field" title="Click to save this field" id="resolution_explanationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <textarea name="resolution_explanationInput" id="resolution_explanationInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:0px; margin-left:3px; width:445px;" rows="4"></textarea>
              <span id="resolution_explanationSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"></span>
        </li>
       </ul> -->
        
    </div>
    
    
    </form>
    </div>

<div id="coa_done"></div>
<script language="javascript">
$("#coa_done").trigger( "click" );
</script>
</div>
