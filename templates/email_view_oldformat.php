<div class="gridster email_view email" id="gridster_email" style="display:none">
     <div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:488px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="email_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="email" />
        <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
        <input id="email_uuid" name="email_uuid" type="hidden" value="<%= uuid %>" />
        <input id="user_id" name="user_id" type="hidden" value="<%= user_id %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "email"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
            <li data-row="1" data-col="1" data-sizex="2" data-sizey="1" >
            	<div style="float:right; margin-top:4px; margin-right:4px; display:none">
                	<a id="test_email" title="Click to Test Incoming Settings" style="background:#CFF; color:black; padding:2px; cursor:pointer">Test</a>
                </div>
            Incoming<hr />
            </li>
            <li id="email_nameGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Gmail Addr</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="email_nameSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= email_name %>" name="email_nameInput" id="email_nameInput" class="kase email_view input_class hidden" placeholder="Name" style="margin-top:-26px; margin-left:60px; width:380px" />
              <span id="email_nameSpan" class="kase email_view span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%= email_name %></span>
            </li>
            <li id="email_pwdGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Password</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="email_pwdSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_pwdSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" id="email_pwdInput" name="email_pwdInput" class="kase email_view input_class hidden" placeholder="Password" style="margin-top:-26px; margin-left:60px; width:140px" type="password" />
              <span id="email_pwdSpan" class="kase email_view span_class form_span_vert" style="margin-top:-28px; margin-left:60px">&bull;&bull;&bull;</span>
            </li>
            <!--
            <li id="email_methodGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Method</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="email_methodSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_methodSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= email_method %>" name="email_methodInput" id="email_methodInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:140px" />
            <span id="email_methodSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= email_method %></span>
            </li>
            <li id="email_serverGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" style="background:none; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            	<span class="white_text" style="font-size:0.8em; font-style:italic">Your email password is encrypted on our database.  It is accessible only by you.</span>
            </li>
            <li id="email_serverGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Server</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="email_serverSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_serverSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= email_server %>" name="email_serverInput" id="email_serverInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:380px" />
            <span id="email_serverSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= email_server %></span>
            </li>
            <li id="email_portGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Port</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="email_portSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_portSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= email_port %>" name="email_portInput" id="email_portInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:140px" />
            <span id="email_portSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= email_port %></span>
            </li>
            <li id="ssl_requiredGrid" data-row="6" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">SSL</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="ssl_requiredSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="ssl_requiredSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= ssl_required %>" name="ssl_requiredInput" id="ssl_requiredInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:140px" />
            <span id="ssl_requiredSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= ssl_required %></span>
            </li>
            -->
            <li data-row="4" data-col="1" data-sizex="2" data-sizey="1" >
            <div style="float:right; margin-top:4px; margin-right:4px;display:none">
            <a id="outgoing_email" title="Click to Test Outgoing Settings" style="background:#CFF; color:black; padding:2px; cursor:pointer">Test</a>
            </div>
            Outgoing<hr />
            </li>
            <li id="email_addressGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Email</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="email_addressSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_addressSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= email_address %>" name="email_addressInput" id="email_addressInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:380px" />
            <span id="email_addressSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= email_address %></span>
            </li>
            <li id="outgoing_serverGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Server</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="outgoing_serverSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="outgoing_serverSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= outgoing_server %>" name="outgoing_serverInput" id="outgoing_serverInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:380px" />
            <span id="outgoing_serverSpan" class="kase outgoing_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= outgoing_server %></span>
            </li>
            <li id="outgoing_portGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Port</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="outgoing_portSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="outgoing_portSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= outgoing_port %>" name="outgoing_portInput" id="outgoing_portInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:140px" />
            <span id="outgoing_portSpan" class="kase outgoing_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= outgoing_port %></span>
            </li>
            <li id="encrypted_connectionGrid" data-row="7" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">SSL</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="encrypted_connectionSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="encrypted_connectionSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <select name="encrypted_connectionInput" id="encrypted_connectionInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:140px">
            	<option value="None" <% if (encrypted_connection=="None") { %>selected<% } %>>None</option>
                <option value="Auto" <% if (encrypted_connection=="Auto") { %>selected<% } %>>Auto</option>
                <option value="SSL" <% if (encrypted_connection=="SSL") { %>selected<% } %>>SSL</option>
                <option value="TLS" <% if (encrypted_connection=="TLS") { %>selected<% } %>>TLS</option>
            </select>
            <span id="encrypted_connectionSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= encrypted_connection %></span>
            </li>
            <li data-row="8" data-col="1" data-sizex="2" data-sizey="1" >Contact Info<hr /></li>
            <li id="email_phoneGrid" data-row="9" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Cell</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="email_phoneSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_phoneSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= email_phone %>" name="email_phoneInput" id="email_phoneInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px" onkeyup="mask(this, mphone);" onblur="mask(this, mphone);" />
            <span id="email_phoneSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= email_phone %></span>
            </li>
            <li id="cell_carrierGrid" data-row="9" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Carrier</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="cell_carrierSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="cell_carrierSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= cell_carrier %>" name="cell_carrierInput" id="cell_carrierInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px" />
            <span id="cell_carrierSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= cell_carrier %></span>
            </li>
            <li id="emptyGrid" data-row="10" data-col="1" data-sizex="2" data-sizey="1" class=" gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; visibility:hidden">
                </li>
		</ul>
    </form>
</div>
</div>

<% if (gridster_me || grid_it) { %>
<script language="javascript">
setTimeout(function() {
	gridsterById('gridster_email');
}, 10);
</script>
<% } %>