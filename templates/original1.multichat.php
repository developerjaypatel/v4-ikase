<script type="text/javascript" src="../lib/expanding.js"></script>
<div id="chat_holder_<%=chat_id%>" class="chat_<%=chat_id%>" style="width:300px; border:0px solid blue; font-size:1.2em">
    <form id="chat_form" name="chat_form" method="post" action="">
        <input id="table_name" name="table_name" type="hidden" value="chat" />
        <input id="chat_id" name="chat_id" type="hidden" value="<%=chat_id%>" />
        <input name="fromInput" type="hidden" id="fromInput" value="<%=login_username%>" />
        <table width="300" border="0" align="center" cellpadding="2" cellspacing="0" style="border:0px solid green">
          <tr>
            <th align="left" valign="top" scope="row" style="color:#000">To:<span id="chat_toSpan" class="span_class" style="border:0px solid red"></span>
			<br />
			<input name="chat_toInput" type="text" id="chat_toInput" autocomplete="off" class="modalInput" />
            </th>
          </tr>
          <tr>
            <th align="left" valign="top" scope="row" style="color:#000; height:150px"><textarea name="chatInput" id="chatInput" class="modalInput expanding" style="width:278px; border:1px solid blue; resize: vertical; max-height: 150px; max-width: 278px; height: 25px;"></textarea></th>
          </tr>
        </table>
    </form>
</div>
