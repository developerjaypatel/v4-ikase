<div class="chat" style="width:229px">
	<div style="position:absolute; width:95%; z-index:21; text-align:right; top:7px; border:0px solid yellow">
        <button type="button" class="close" style="margin-right:5px; margin-left:5px"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <button class="save btn btn-transparent border-green" style="width:20px; border:0px solid; margin-right:5px; margin-top:-7px"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></button>&nbsp;&nbsp;
    </div>
	<div style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;">
    	<div>
            <span id="panel_title">Chat <span id="thread_id_holder"><% if (thread_id > 0) { %><%=thread_id%><% } %></span></span>&nbsp;<img src="img/loading_spinner_1.gif" width="20" height="20" id="gifsave" class="chat" style="display:none; opacity:50%" /> &nbsp; 
           <span class="alert alert-success" style="display:none; height:25px; width:50px;font-size:14px; z-index:4251; margin-top:-35px; margin-left:-10px;">Saved</span>
           <span class="alert alert-warning" style="display:none; height:25px; width:50px; font-size:14px; z-index:4251; margin-top:-35px; margin-left:-10px;"></span>
       </div>
       
   </div>
   
<form id="chat_form" name="chat_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="chat" />
<input id="table_id" name="table_id" type="hidden" value="" />
<input id="thread_id" name="thread_id" type="hidden" value="<%=thread_id%>" />
<input name="fromInput" type="hidden" id="fromInput" value="<%=login_username%>" />
<table width="255" border="0" align="center" cellpadding="2" cellspacing="0">
  <tr>
    <th align="left" valign="top" scope="row" style="color:#FFFFFF">To:<span id="chat_toSpan" class="span_class"></span><br /><input name="chat_toInput" type="text" id="chat_toInput" autocomplete="off" class="modalInput" />
    </th>
  </tr>
  <!--
  <tr>
    <th align="left" valign="top" scope="row" style="color:#FFFFFF">Subject:<span id="subjectSpan" class="span_class"></span><br /><select name="subjectInput" type="text" id="subjectInput" class="modalInput"  style="width:210px">
    	<option value="">Select from List</option>
        <option value="General" selected="selected">General</option>
		<option value="Phone Call">Phone Call</option>
		<option value="Personal">Personal</option>
		<option value="Urgent">Urgent</option>
      </select>
    </th>
  </tr>
  -->
  <tr>
    <th align="left" valign="top" scope="row" style="color:#FFFFFF"><textarea name="chatInput" id="chatInput" class="modalInput" style="width:215px; height:100px"></textarea></th>
    </tr>
  <tr>
  	<td>
    	<div id="chat_attachments" style="width:250px"></div>    </td>
  </tr>
</table>
</form>
</div>