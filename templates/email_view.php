


<div class="gridster email_view email" id="gridster_email" style="display:none">
     <div style="background:url(img/glass_card_dark_6.png) left top repeat-y; padding:5px; width:488px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
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
        <div id="email-setting-form" style="display: none;">
        <ul>
            <!--
            <li data-row="1" data-col="1" data-sizex="2" data-sizey="1" >
            	<div style="float:right; margin-top:4px; margin-right:4px; display:none">
                	<a id="test_email" title="Click to Test Incoming Settings" style="background:#CFF; color:black; padding:2px; cursor:pointer">Test</a>
                </div>
            Settings<hr />
            </li>
            -->
            <li id="email_nameGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Email Addr</div></h6>
              <input value="<%= email_name %>" name="email_nameInput" id="email_nameInput" class="kase email_view input_class hidden" placeholder="someone@somesite.com" style="margin-top:-26px; margin-left:100px; width:280px" onchange="checkAllField()" />
              <input value="<%= email_address %>" name="email_addressInput" id="email_addressInput" class="kase input_class kase hidden" style="display:none" onchange="checkAllField()" />
              <span id="email_nameSpan" class="kase email_view span_class form_span_vert" style="margin-top:-30px; margin-left:100px"><%= email_name %></span>
            </li>
            <li id="email_pwdGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="hideme gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Password</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="email_pwdSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_pwdSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="position:absolute; font-size:1.2em; width:20px; display:none; z-index: 9999;top: 10px;left: 220px;" id="eye_holder">
                <i class="glyphicon glyphicon-eye-open" style="color:black; cursor:pointer" id="show_password" title="Show Password"></i>
            </div>
            <div style="float:right;font-size:0.8em; font-style:italic; margin-top:-26px">
            	Enter password to change
            </div>
              <input value="" id="email_pwdInput" name="email_pwdInput" class="kase email_view input_class hidden" placeholder="Password" style="margin-top:-26px; margin-left:100px; width:140px" type="password" onchange="checkAllField()" />
              <span id="email_pwdSpan" class="kase email_view span_class form_span_vert" style="margin-top:-28px; margin-left:100px">&bull;&bull;&bull;</span>
            </li>
            <li id="email_serverGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" style="background:none; font-family: 'Open Sans', sans-serif; color:#FFFFFF" class="hideme">
            	<span class="white_text" style="font-size:0.8em; font-style:italic">Your email password is encrypted on our database.  It is accessible only by you.</span>
            </li>
            <li id="email_methodGrid" data-row="4" data-col="2" data-sizex="1" data-sizey="1" class="hideme gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Method</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="email_methodSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_methodSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <select name="email_methodInput" id="email_methodInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:140px" onchange="checkAllField()">
            	<option value="" <% if (email_method=="") { %>selected<% } %>>Select from List</option>
                <option value="POP3" <% if (email_method=="POP3") { %>selected<% } %>>POP3</option>
                <option value="IMAP" <% if (email_method=="IMAP") { %>selected<% } %>>IMAP</option>
            </select>
            <span id="email_methodSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= email_method %></span>
            </li>
            <li id="email_portGrid" data-row="4" data-col="2" data-sizex="1" data-sizey="1" class="hideme gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Port</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="email_portSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_portSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <!--
            <input value="<%= email_port %>" name="email_portInput" id="email_portInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:140px" />
            -->
            <select name="email_portInput" id="email_portInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:140px" onchange="checkAllField()">
                <option value="25" <% if (email_port=="25" || email_port=="") { %>selected<% } %>>25</option>
                <option value="465" <% if (email_port=="465") { %>selected<% } %>>465</option>
                <option value="587" <% if (email_port=="587") { %>selected<% } %>>587</option>              
            </select>
            
            <span id="email_portSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= email_port %></span>
            </li>
            <li id="email_serverGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="hideme gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Server</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="email_serverSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="email_serverSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <!-- <input value="<%= email_server %>" name="email_serverInput" id="email_serverInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:380px" />
            <span id="email_serverSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= email_server %></span> -->
            <!-- <select name="email_serverInput" id="email_serverInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px">
            	<option value="imap.gmail.com" <% if (ssl_required=="imap.gmail.com" || ssl_required=="") { %>selected<% } %>>imap.gmail.com</option>
                <option value="server2" <% if (ssl_required=="server2") { %>selected<% } %>>server2</option>
            </select>
            <span id="email_serverSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= ssl_required %></span> -->
            
            <select name="email_serverInput" id="email_serverInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px; width:140px">
            	<!-- <option value="" <% if (email_server=="") { %>selected<% } %>>Select from List</option> -->
                <option value="imap.gmail.com" <% if (email_server=="imap.gmail.com" || email_server=="") { %>selected<% } %>>imap.gmail.com</option>
                <option value="outlook.office365.com" <% if (email_server=="outlook.office365.com") { %>selected<% } %>>outlook.office365.com</option>
                <option value="imap.mail.yahoo.com" <% if (email_server=="imap.mail.yahoo.com") { %>selected<% } %>>imap.mail.yahoo.com</option>
            </select>
            <span id="email_serverSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= email_server %></span>
            
            </li>
            <li id="ssl_requiredGrid" data-row="5" data-col="2" data-sizex="1" data-sizey="1" class="hideme gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">SSL</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="ssl_requiredSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="ssl_requiredSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <select name="ssl_requiredInput" id="ssl_requiredInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:60px">
            	<option value="N" <% if (ssl_required=="N" || ssl_required=="") { %>selected<% } %>>Not Required</option>
                <option value="Y" <% if (ssl_required=="Y") { %>selected<% } %>>Required</option>
            </select>
            <span id="ssl_requiredSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:60px"><%= ssl_required %></span>
            </li>
            <li id="activeGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Active</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="activeSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="activeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <select name="activeInput" id="activeInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:100px">
            	<option value="N" <% if (active=="N" || active=="") { %>selected<% } %>>Not Active</option>
                <option value="Y" <% if (active=="Y") { %>selected<% } %>>Active</option>
            </select>
            <span id="activeSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:100px"><%= active %></span>
            </li>
            <li id="read_messagesGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;" id="read_messagesLabel">Mark Messages as Read</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="read_messagesSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="read_messagesSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input type="checkbox" name="read_messagesInput" id="read_messagesInput" value="Y" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:120px" <% if (read_messages=="Y") { %>checked<% } %> >
            <span id="read_messagesSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:185px"><%= read_messages %></span>
            <div style="font-style:italic; color:white; padding-top:15px; display:none" id="read_messages_instructions">When you read an email via iKase, it will marked as READ on GMail</div>
            </li>
            <li id="emails_pendingGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;" id="emails_pendingLabel">Emails Go</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="emails_pendingSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="emails_pendingSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
           <% 
            var display_pending = ""; 
            
            %>
            <select name="emails_pendingInput" id="emails_pendingInput" class="kase input_class kase hidden" style="margin-top:-26px; margin-left:100px">
            	<option value="N" >To Inbox</option>
                <option value="Y" >To Pending</option>
            </select>
            
            <span id="emails_pendingSpan" class="kase email_view span_class form_span_vert" style="border:#FFFFFF solid 0px; margin-top:-28px; margin-left:85px"><%= display_pending %></span>
            <div style="font-style:italic; color:white; padding-top:15px; display:none" id="emails_pending_instructions">When you receive an Email, it will appear in the Pending folder until you process it</div>
            </li>
		</ul>
        <div class="hideme">
            <div style="float:right">
                <div class="test_feedback" style="color:white; font-size:1.2em; margin-top:7px; margin-right:10px"></div>
            </div>
            <!-- <button class="btn btn-primary" id="ping_mail">Test Connection</button>
			<button class="btn btn-success addEmail" style="display: none" type="button">Add Email</button> -->
            <script>
                // function checkAllField()
                // {
                //     if($("#email_nameInput").val()=="" || $("#email_pwdInput").val()=="" || $("#email_portInput").val()=="" || $("#email_methodInput").val()=="")
                //     {

                //         $(".addEmail").hide();
                //     }
                //     else
                //     {
                //         $(".addEmail").show();
                //     }
                // }

            </script>
        </div>
        </div>
		<table style="width: 100%; color: white; border: 1px solid #838383; margin-top: 15px;">
			<thead style="border: 1px solid #838383;">
				<tr>
					<td>Email</td>
					<td>Active</td>
				</tr>
			</thead>
			
			<tbody class="emailTable"></tbody>
		</table>
    </form>
  
</div>
<div style="color:white; margin-top:15px ;background:red ;font-size:larger;margin-right: -225px;">
            <lable>IMAP Synchronizes! Changes to messages, such as Reading, Marking `Unread`, and Deleting messages in Ikase will be reflected on All Devices, everywhere.

Likewise, messages deleted on other devices will automatically be removed, forever, from your Ikase inbox.</lable>
</div>
</div>

<% if (gridster_me || grid_it) { %>
<script language="javascript">
setTimeout(function() {
	gridsterById('gridster_email');
}, 10);

setTimeout(function() {
	$("#email_form #sub_category_holder_email .delete").click(function(event) {
		event.preventDefault();
		var string = 'operation_=delete-email&';
		$('#email_form input:hidden').each(function() {
			string  += $(this).attr('name')+"="+$(this).val()+"&";
			// do something with the value
		});
		location.href = "v8.php?"+string
	});
}, 1000);
</script>
<% } %>