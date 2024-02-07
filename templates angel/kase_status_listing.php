<div class="white_text" style="color:black">
	<span style="font-size:1.1em; font-weight:bold" class="white_text">Manage Kase <%=status_level.capitalize() %> Status</span>
    &nbsp;
    <button class="btn btn-xs" id="new_kase_status_button">New Status</button>
    &nbsp;<a id="show_all_status" style="color:white; cursor:pointer" title="Click to show deleted status">Show Deleteds</a>
</div>
<div class="white_text" style="font-style: italic; margin-bottom: 10px;">
Click on items below to edit or delete
</div>
<div>
	<form  id="kase_status_form">
    <div id="new_kase_status_holder" style="display:none">
    	<input type="text" id="new_kase_status" />&nbsp; <button class="btn btn-xs btn-success" id="save_kase_status">Save</button>
    </div>
	<%=html %>
    </form>
</div>
