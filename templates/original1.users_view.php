<?php $form_name = "user"; include("edit_view_navigation.php"); ?>
    	
<div class="<?php echo $form_name; ?>">
    <form id="<?php echo $form_name; ?>_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="<?php echo $form_name; ?>" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="display:">
        <ul style="margin-bottom:10px">
            <li id="user_nameGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert">Name</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_nameSave">
                <a class="save_field" title="Click to save this field" id="user_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= user_name %>" name="user_nameInput" id="user_nameInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-2px;" required />
              <span id="user_logonSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert"><%= user_name %></span>
        </li>
        <li id="user_logonGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert">Logon</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_logonSave">
                <a class="save_field" title="Click to save this field" id="user_logonSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= user_logon %>" name="user_logonInput" id="user_logonInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="Tax ID is a required field" style="margin-top:-2px;" required />
              <span id="user_logonSpan" class="<?php echo $form_name; ?> span_class form_span_vert"><%= user_logon %></span>
            </li>       
       </ul>
    </div>
    </form>
</div>