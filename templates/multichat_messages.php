<table style="" border="0" width="100%" cellspacing="0" cellpadding="1">
<% _.each( chats, function(chat) { %>
	<!--
    <tr style="font-size:1em; border:0px solid red; margin-top:-20px; padding-top:0px" align="<%=chat.text_alignment %>" valign="top">
		<td style="border:0px solid green; width:100%; margin-top:-20px; padding-top:0px" align="<%=chat.text_alignment %>" valign="top" title="<%=chat.timestamp %>">
			<div style="vertical-align:top; margin-top:0px; margin-<%=chat.text_alignment %>:25px;" class="<%=chat.bubble_class %>">
				<%=chat.message %>	
			</div>&nbsp;
			<div style="border:1px solid orange; margin-top:-15px; width:25px; padding:0px; vertical-align:top; position:relative"><%=chat.display_user %></div>
			<div style="vertical-align:top; border:0px solid yellow; width:40px; height:15px" align="<%=chat.text_alignment %>"><div align="<%=chat.text_alignment %>" valign="top" style="font-size:.5em; text-align:<%=chat.text_alignment %>; height:15px; margin-top:-5px"><%=chat.timestamp %></div></div>
		</td>
	</tr>
    -->
    <tr><td><%=chat.row %></td></tr>
<% }); %>
</table>
<div id="multichat_messages_done"></div>
<script language="javascript">
$( "#multichat_messages_done" ).trigger( "click" );
</script>