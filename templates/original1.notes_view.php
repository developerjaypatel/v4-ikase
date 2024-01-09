<?php $form_name = "notes"; include("edit_view_navigation.php"); ?>
    	
<div class="<?php echo $form_name; ?>">
    <form id="<?php echo $form_name; ?>_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="<?php echo $form_name; ?>" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
    <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
    <textarea style="display:none" name="noteInput" id="noteInput" parsley-error-message="" required><%= note %></textarea>
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="display:none">
        <ul style="margin-bottom:10px">
            <li id="typeGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert">Type</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="typeSave">
                <a class="save_field" title="Click to save this field" id="typeSaveLink">
                    <i class="glyphicon glyphicon-saved"></i>
                </a>
            </div>
              <select name="typeInput" id="typeInput" class="<?php echo $form_name; ?> input_class hidden" parsley-error-message="" required style="margin-top:-2px;">
                <option value="general" <% if (type=="general" || type=="") { %>selected<% } %>>General Note</option>
                <option value="billing" <% if (type=="billing") { %>selected<% } %>>Billing</option>
                <option value="hr" <% if (type=="hr") { %>selected<% } %>>Human Resources</option>
              </select>
              <span id="typeSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert"><%= type %></span>
        </li>
        <li id="dateandtimeGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert">Date</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="dateandtimeSave">
                <a class="save_field" title="Click to save this field" id="dateandtimeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= moment(dateandtime).format('MM/DD/YYYY hh:mmA') %>" name="dateandtimeInput" id="dateandtimeInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" parsley-error-message="" required style="margin-top:-2px;" />
              <span id="dateandtimeSpan" class="<?php echo $form_name; ?> span_class form_span_vert"><%= moment(dateandtime).format('MM/DD/YYYY hh:mmA') %></span>
            </li>
            <li id="statusGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert">Status</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="statusSave">
                <a class="save_field" title="Click to save this field" id="statusSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <select name="statusInput" id="statusInput" class="<?php echo $form_name; ?> input_class hidden" parsley-error-message="" required style="margin-top:-2px;">
                <option value="STANDARD" <% if (status=="STANDARD" || status=="") { %>selected<% } %>>STANDARD</option>
                <option value="URGENT" <% if (status=="URGENT") { %>selected<% } %>>URGENT</option>
                <option value="REMINDER" <% if (status=="REMINDER") { %>selected<% } %>>REMINDER</option>
              </select>
              <span id="statusSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert"><%= status %>
              </span>
            </li>
            <li id="entered_byGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert">Author</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="entered_bySave">
                <a class="save_field" title="Click to save this field" id="entered_bySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="entered_byInput" id="entered_byInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Author" parsley-error-message="" required style="margin-top:-2px;" />
              <span id="entered_bySpan" class="kase <?php echo $form_name; ?> span_class form_span_vert">
              </span>
            </li>  
            <li id="noteGrid" data-row="1" data-col="4" data-sizex="2" data-sizey="5" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert">Note</div></h6>
            <div style="float:right; margin-right:10px" class="hidden" id="noteSave">
                <a class="save_field" title="Click to save this field" id="noteSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
            <span id="noteInput" name="noteInput" class="note input_class hidden" style="width:94%; height:100%">
                <iframe src="text_editor/ed/text_editor.php?case_id=<%= case_id %>&<?php echo $form_name; ?>_id=<%= <?php echo $form_name; ?>_id %>" frameborder="0" style="width:100%; height:90%" allowtransparency="true"></iframe>
            </span>
            
              <span id="noteSpan" class="<?php echo $form_name; ?> kase form_span_vert span_class">
                <%= note %>
              </span>
            </li>       
                   
       </ul>
    </div>
    </form>
</div>