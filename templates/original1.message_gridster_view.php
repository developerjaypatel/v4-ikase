<% location_data_row = 1;
	next_data_row = 1;
%>
<div class="gridster message" id="gridster_message" style="display:none">
     <div style="background:url(img/glass_card_fade_4.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="message_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="message" />
        <input id="table_id" name="table_id" type="hidden" value="<%= message_id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "message"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
             <li id="subjectGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="message gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Subject:</div></h6>
            <div style="" class="message save_holder hidden" id="subjectSave">
                <a class="save_field" from="Click to save this field" id="subjectSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= subject %>" id="subjectInput" name="subjectInput" placeholder="Subject" type="text" class="kase message input_class hidden" style="margin-top:-26px; margin-left:60px; width:384px" />
          <span id="subjectSpan" class="message span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%= subject %></span>
            </li>
            <li id="message_toGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="message gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">To:</div></h6>
            <div style="" class="message save_holder hidden" id="event_fromSave">
                <a class="save_field" from="Click to save this field" id="message_toSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input value="<%=message_to %>" name="message_toInput" id="message_toInput" class="message input_class hidden" placeholder="To" style="margin-top:-26px; margin-left:60px; width:384px" />
          <span id="message_toSpan" class="message span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%=message_to %></span>
            </li>
            <li id="message_ccGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="message gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Cc:</div></h6>
            <div style="" class="message save_holder hidden" id="message_ccSave">
                <a class="save_field" from="Click to save this field" id="message_ccSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input value="<%= message_cc %>" id="message_ccInput" name="message_ccInput" placeholder="Cc" type="text" class="kase partie input_class hidden" style="margin-top:-26px; margin-left:60px; width:150px" />
          <span id="message_ccSpan" class="message span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%= message_cc %></span>
            </li>
            <li id="message_bccGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="message gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Bcc:</div></h6>
            <div style="" class="message save_holder hidden" id="message_bccSave">
                <a class="save_field" from="Click to save this field" id="message_bccSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <input value="<%=message_bcc %>"  name="message_bccInput" id="message_bccInput" class="message input_class hidden" placeholder="Bcc" style="margin-top:-26px; margin-left:60px" />
          <span id="message_bccSpan" class="message span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%=message_bcc  %></span>
            </li>
            <li id="message_priorityGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="message gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Priority:</div></h6>
            <div style="" class="message save_holder hidden" id="message_prioritySave">
                <a class="save_field" from="Click to save this field" id="message_prioritySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <select name="message_priorityInput" id="message_priorityInput" class="message input_class hidden" style="height:25px; width:145px; margin-top:-30px; margin-left:65px">
          <option value="" selected>Select from List</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          </select>
          <span id="message_prioritySpan" class="message span_class form_span_vert" style="margin-top:-30px; margin-left:60px"></span>
           </li>
            <li id="colleaguesGrid" data-row="4" data-col="2" data-sizex="1" data-sizey="1" class="message gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Colleagues:</div></h6>
            <div style="" class="message save_holder hidden" id="colleaguesSave">
                <a class="save_field" from="Click to save this field" id="colleaguesSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <select name="colleaguesInput" id="colleaguesInput" class="message input_class hidden" style="height:25px; width:145px; margin-top:-30px; margin-left:65px">
          <option value="">Select from List</option>
          </select>
          <span id="colleaguesSpan" class="message span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%= colleagues %></span>
            </li>
            <li id="messageGrid" data-row="4" data-col="2" data-sizex="2" data-sizey="4" class="message gridster_border" style="background:url(img/glass.png) left top;">
            <h6><div class="form_label_vert" style="margin-top:10px;">Message:</div></h6>
            <div style="" class="message save_holder hidden" id="messageSave">
                <a class="save_field" from="Click to save this field" id="messageSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
          <textarea name="messageInput" id="messageInput" class="message input_class hidden" style="height:155px; width:384px; margin-top:-20px; margin-left:65px"></textarea>
          <span id="messageSpan" class="message span_class form_span_vert" style="margin-top:-30px; margin-left:60px"></span>
            </li>
        </ul>
     </form>
  </div>
</div>
<% if (gridster_me) { %>
<script language="javascript">
setTimeout(function() {
	gridsterById('gridster_message');
}, 10);


</script>
<% } %>