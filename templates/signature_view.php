<?php 
include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");
?>
<div style="background:url(img/glass_card_dark_long_4.png) left top repeat-y; padding:5px; width:945px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; border:0px solid green">

<div class="signature" id="user_panel">
    <form id="signature_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="signature" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
    <input id="signature_uuid" name="signature_uuid" type="hidden" value="<%= uuid %>" />
	<input id="user_id" name="user_id" type="hidden" value="<%= user_id %>" />
    <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
		<?php 
        $form_name = "signature"; 
        include("dashboard_view_navigation.php"); 
        ?>
    </div>
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="display:none">
    
        <ul style="margin-bottom:10px">
            <li id="signatureGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="12" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            	<!--
                <iframe src="templates/wysiwyg.php?field=signature" style="margin-left:10px; width:95%; height:520px; display:none" id="tiny_holder"></iframe>
            	-->
            	<textarea name="signatureInput" id="signatureInput" class="<?php echo $form_name; ?> " placeholder="" style="margin-top:10px; margin-left:10px; width:97%; height:520px; display:none" rows="8" required><%= signature %></textarea>
            	<%
                var signature_html = signature.replace(new RegExp(String.fromCharCode(13), 'g'), '\r\n');
                var signature_html = signature_html.replaceAll("\r\n", "<br>");
				signature_html = signature_html.replaceAll("\n", "<br>");
                
                if (signature_html.length > 1199) {
                	signature_html = signature_html.getComplete(1200) + " ...";
                }
                %>
                <span id="signatureSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:0px; margin-left:10px"><%= signature_html %></span>
        </li>
        <li id="titleGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Title</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="titleSave">
                <a class="save_field" title="Click to save this field" id="titleSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= title %>" name="titleInput" id="titleInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Title" style="margin-top:-28px; margin-left:60px" />
              <span id="titleSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= title %></span>
        </li>
        <li id="signs_forGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Signs For</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="signs_forSave">
                <a class="save_field" title="Click to save this field" id="signs_forSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	<input value="<%= signs_for %>" name="signs_forInput" id="signs_forInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Signs For" style="margin-top:-28px; margin-left:60px" />
              <span id="signs_forSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= signs_for %></span>
        </li>
        <!--
        <li id="additional_textGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Text</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="additional_textSave">
                <a class="save_field" title="Click to save this field" id="additional_textSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= additional_text %>" name="additional_textInput" id="additional_textInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:60px; width:385px" />
              <span id="additional_textSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= additional_text %></span>
        </li>
        <li id="image_pathGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Addl Text</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="image_pathSave">
                <a class="save_field" title="Click to save this field" id="image_pathSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= image_path %>" name="image_pathInput" id="image_pathInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:60px; width:385px" />
              <span id="image_pathSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= image_path %></span>
        </li>
        -->   
       </ul>
    </div>
    
    </form>
</div>
</div>
<div id="signature_view_all_done"></div>
<script language="javascript">
$("#signature_view_all_done").trigger( "click" );
</script>