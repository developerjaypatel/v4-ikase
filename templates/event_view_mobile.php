<div style="background:url(../img/glass_card_dark_long_3.png); margin-left:auto; margin-right:auto; padding:10px; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px; width:94%">
    <div id="event_mobile">
    	<div style="float:right">
            <button id="save_event_<%=case_id %>" onclick="saveMobileEvent(event, <%=case_id %>)" name="save_event" class="btn btn-md btn-success">Save</button>
        </div>
    	<form id="event_mobile_form">
        <input id="table_name" name="table_name" type="hidden" value="event" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
    	<div style="color:white; font-size:1.5em">Case</div>
        <div style="color:white"><%=case_name %></div>
        <br />
        <br />
        <div style="color:white; font-size:1.5em">Date</div>
        <input name="event_dateandtimeInput" id="event_dateandtimeInput" class="" value="" style="width:99%; font-size:1.5em; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px;" />  
        <br />
        <br />
    	<div style="color:white; font-size:1.5em">Subject</div>
        <input name="event_titleInput" id="event_titleInput" class="" value="" style="width:99%; font-size:1.5em; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px;" />     
        <br />
        <br />
        <div style="color:white; font-size:1.5em">Assignee</div>
        <input name="assigneeInput" id="assigneeInput" class="" value="" style="width:99%; font-size:1.5em; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px;" />     
        <br />
        <br />
        <div style="color:white; font-size:1.5em">Event</div>
        <textarea name="event_descriptionInput" id="event_descriptionInput" class="" style="width:99%; font-size:1.5em; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px;" rows="5"></textarea>     
    </form>
    </div>
</div>
<div id="event_view_all_done_mobile"></div>
<script type="text/javascript">
$("#event_view_all_done_mobile").trigger("click");
</script>