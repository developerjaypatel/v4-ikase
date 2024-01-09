<div style="background:url(../img/glass_card_dark_long_2.png); margin-left:auto; margin-right:auto; padding:10px; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px; width:94%">    
        <div id="task_mobile">
            <div style="float:right">
                <button id="save_task_<%=case_id %>" onclick="saveMobileTask(event, <%=case_id %>)" name="save_task" class="btn btn-md btn-success">Save</button>
            </div>
            <form id="task_mobile_form">
                <input id="table_name" name="table_name" type="hidden" value="task" />
                <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
                <div style="color:white; font-size:1.5em">Case</div>
                <div style="color:white"><%=case_name %></div>
                <br />
                <br />
                <div style="color:white; font-size:1.5em">Due</div>
                <input name="task_dateandtimeInput" id="task_dateandtimeInput" class="" value="" style="width:99%; font-size:1.5em; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px;" />  
                <br />
                <br />
                <div style="color:white; font-size:1.5em">Subject</div>
                <input name="task_titleInput" id="task_titleInput" class="" value="" style="width:99%; font-size:1.5em; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px;" />     
                <br />
                <br />
                <div style="color:white; font-size:1.5em">Assignee</div>
                <input name="assigneeInput" id="assigneeInput" class="" value="" style="width:99%; font-size:1.5em; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px;" />     
                <br />
                <br />
                <div style="color:white; font-size:1.5em">Task</div>
                <textarea name="task_descriptionInput" id="task_descriptionInput" class="" style="width:99%; font-size:1.5em; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px;" rows="5"></textarea>          
            </form>
    	</div>
</div>
<div id="task_all_done"></div>
<script type="text/javascript">
$("#task_all_done").trigger("click");
</script>