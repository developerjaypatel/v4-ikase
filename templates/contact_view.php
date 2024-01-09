<?php 
require_once('../shared/legacy_session.php');
session_write_close();

?>
<div style="background:url(img/glass_card_dark.png) left top repeat; padding:5px; width:650px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;" id="main_contact_holder">
<div style="position:absolute; z-index:8888; left:750px; top:-10px" id="contact_kases_holder"></div>
<div class="contact" id="contact_panel" style="position:relative">
    <form id="contact_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="contact" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
    <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
		<?php 
        $form_name = "contact"; 
        include("dashboard_view_navigation.php");
        ?>
    </div>
    <div style="position:absolute;top: 5px;left: 350px;color: white;font-weight: normal;">
    	<button class="btn btn-xs review_kases" id="review_kases" title="Review Kases">Kases</button>
        &nbsp;&nbsp;
        <button class="btn btn-xs review_emails" id="review_emails" title="Review Emails">Emails</button>
        &nbsp;&nbsp;
    	<label for="spam_statusInput">SPAM Status</label>
        <select id="spam_statusInput" name="spam_statusInput">
        	<option value="OK" <%=spam_status_ok %>>OK</option>
            <option value="BLOCKED" <%=spam_status_blocked %>>Blocked</option>
		</select>
    </div>
    <div class="gridster <?php echo $form_name; ?> contact" id="gridster_<?php echo $form_name; ?>" style="display:none">
    
        <ul style="margin-bottom:10px">
            <li id="first_nameGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px; color:#FFF">First</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="first_nameSave">
                <a class="save_field" title="Click to save this field" id="first_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= first_name %>" name="first_nameInput" id="first_nameInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:55px; width:190px"  autocomplete="off" />
              <span id="first_nameSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= first_name %></span>
        </li>
        
        <li id="last_nameGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px; color:#FFF">Last</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="last_nameSave">
                <a class="save_field" title="Click to save this field" id="last_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= last_name %>" name="last_nameInput" id="last_nameInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-28px; margin-left:55px; width:190px" autocomplete="off" />
              <span id="last_nameSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= last_name %></span>
        </li>
		<li id="phoneGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px; color:#FFF">Phone</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="phoneSave">
                <a class="save_field" title="Click to save this field" id="phoneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= phone %>" name="phoneInput" id="phoneInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" style="margin-top:-28px; margin-left:55px; width:190px" autocomplete="off" />
              <span id="phoneSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= phone %></span>
        </li>
        <li id="emailGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px; color:#FFF">Email</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="emailSave">
                <a class="save_field" title="Click to save this field" id="emailSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= email %>" name="emailInput" id="emailInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Email" style="margin-top:-28px; margin-left:55px; width:190px" />
              <span id="emailSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= email %></span>
        </li>
		
        <li id="full_addressGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="2" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px; color:#FFF">Address</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="full_addressSave">
                <a class="save_field" title="Click to save this field" id="full_addressSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	
                
              <textarea name="full_addressInput" id="full_addressInput" class="<?php echo $form_name; ?> input_class hidden" rows="3" style="margin-top:-28px; margin-left:55px; width:495px"><%= full_address %></textarea>
              <span id="notesSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= full_address %></span>
        </li>
        <% if (notes == null) { 
        		notes = "";
           }
        %>
        <li id="notesGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px; color:#FFF">Notes</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="notesSave">
                <a class="save_field" title="Click to save this field" id="notesSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	
                
              <textarea name="notesInput" id="notesInput" class="<?php echo $form_name; ?> input_class hidden" rows="5" style="margin-top:-20px; margin-left:55px; width:495px"><%= notes %></textarea>
              <span id="notesSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"><%= notes %></span>
        </li>
       </ul>
        <% if (gridster_me) { %>
			<a href="#contacts/<%= contact_id %>"><img src="img/glass_add.png" width="20" height="20" border="0" /></a>
        <% } %>
    </div>
    
    </form>
</div></div>
<div id="contact_view_done"></div>
<script language="javascript">
$( "#contact_view_done" ).trigger( "click" );
</script>
