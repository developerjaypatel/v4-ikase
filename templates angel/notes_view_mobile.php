

<div style="background:url(../img/glass_card_dark_long_1.png); margin-left:auto; margin-right:auto; padding:10px; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px; width:94%">
	<div id="task_mobile">
        <div style="float:right">
            <button id="save_notes_<%=case_id %>" onclick="saveMobileNote(event, <%=case_id %>)" name="save_task" class="btn btn-md btn-success">Save</button>
        </div>
        <form id="note_mobile_form">
            <input id="table_name" name="table_name" type="hidden" value="notes" />
            <input id="type" name="type" type="hidden" value="general_note" />
            <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
            <div id="note_mobile">
            	<div style="color:white; font-size:1.5em">Case</div>
                <div style="color:white"><%=case_name%></div>
                <br />
                <!--SoluLab code start 19-04-2019 -->
                <div style="color:white; font-size:1.5em">Subject</div>
                <input type="text" name="subjectInput" id="subjectInput" style="width:100%" parsley-error-message="" value="<%=subject%>" required />
                <br/>
                
                <br/>
                <div style="color:white; font-size:1.5em">Status</div>
                <select name="statusInput" id="statusInput" class="modal_input" parsley-error-message="" required style="margin-top:-2px; width:150px; height:28px" title="Notes statused as Most Important will be displayed on top of the Notes List">
                    <option value="STANDARD" <% if (status=="STANDARD" || status=="") { %>selected<% } %>>STANDARD</option>
                    <option value="IMPORTANT" <% if (status=="IMPORTANT") { %>selected<% } %>>MOST IMPORTANT</option>
                    <option value="URGENT" <% if (status=="URGENT") { %>selected<% } %>>URGENT</option>
                    <option value="REMINDER" <% if (status=="REMINDER") { %>selected<% } %>>REMINDER</option>
                </select>
                <br/>
                <br/>
                <!--SoluLab code  19-04-2019 -->
                <br/>
                <br/>
                <div style="color:white; font-size:1.5em">Note</div>
                <textarea name="noteInput" id="noteInput" class="" style="width:99%; font-size:1.5em; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px;" rows="5"></textarea>     
            </div>
        </form>
	</div>
</div>
<div id="notes_all_done"></div>
<script type="text/javascript">
$("#notes_all_done").trigger("click");
</script>