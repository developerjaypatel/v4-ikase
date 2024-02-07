<!--
<div class="white_text" style="color:black">
	<input type="checkbox" id="select_all_types" />&nbsp;Select All
</div>
<div>
	<form  id="tasktypes_form">
	<%=html %>
    </form>
</div>
-->
<div class="white_text" style="color:black">
	<button class="btn btn-xs" id="new_task_type_button">New Type</button>
    &nbsp;<a id="show_all_type" style="color:white; cursor:pointer" title="Click to show deleted type">Show Deleteds</a>
</div>
<div class="white_text" style="font-style: italic; margin-bottom: 10px;">
Click on items below to edit or delete
</div>
<div>
	<form  id="task_type_form">
    <div id="new_task_type_holder" style="display:none">
    	<input type="text" id="new_task_type" />&nbsp; <button class="btn btn-xs btn-success" id="save_task_type">Save</button>
    </div>
	<%=html %>
    </form>
</div>