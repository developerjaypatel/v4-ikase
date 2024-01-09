<div class="white_text" style="color:black">
	<span style="font-size:1.1em; font-weight:bold" class="white_text">Manage Check Request Record Categories</span>
    &nbsp;
    <button class="btn btn-xs" id="new_category_record_button">New Category</button>
    &nbsp;<a id="show_all_record" style="color:white; cursor:pointer" title="Click to show deleted record">Show Deleteds</a>
</div>
<div class="white_text" style="font-style: italic; margin-bottom: 10px;">
Click on items below to edit or delete
</div>
<div>
	<form  id="category_record_form">
    <div id="new_category_record_holder" style="display:none">
    	<input type="text" id="new_category_record" />&nbsp; <button class="btn btn-xs btn-success" id="save_category_record">Save</button>
    </div>
	<%=html %>
    </form>
</div>
