<div id="confirm_delete_form" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this form?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
<div>
	<div class="glass_header" style="width:100%">
    	<div style="float:right">
        	<div class="btn-group">
            
            	<label for="form_searchList" id="label_search_form" style="font-size:1em; cursor:text; position:relative; top:0px; left:105px; width:100px; color:#999">Search Forms</label>
            
				<input id="form_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'form_listing', 'form')" style="height:33px; line-height:32px; margin-top:-5px">
				<a id="form_clear_search" style="position: absolute;
				right: 2px;
				top: 0;
				bottom: 9px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
        <span style="font-size:1.2em; color:#FFFFFF">Forms</span>
            
            <a title="Click to create a form" id="composeform" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-credit-card" style="color:#66FF33">&nbsp;</i></a>
            
    </div>
    <div id="form_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
    <table id="form_listing" class="tablesorter form_listing" border="0" cellpadding="0" cellspacing="0" style="width:100%">
        <tbody>
		<% var intCounter = 0;
        var current_cat = "";
        _.each( forms, function(eams) {
        	var the_cat = eams.category;
             if (current_cat != the_cat) {
                current_cat = the_cat;
            %>
        	<tr class="date_row row_<%= the_cat.replaceAll(" ", "") %>">
                <td colspan="7">
                    <div style="width:100%; 
	text-align:left; 
	font-size:1.8em; 
	background:#CFF; 
	color:red;"><%= eams.category.replace(" forms", "").capitalizeWords() %></div>
                </td>
            </tr>
        <% } %>
       	<tr class="form_data_row form_row_<%= eams.id %>">
        
        	<td style="width:250px" align="left">
				<%= eams.display_name %>
			</td>
            <td align="left">
            	<a title="Click to edit Form" class="edit_eams" id="eamsforms_<%= eams.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer;color:white"><i class="glyphicon glyphicon-edit" style="color:#0033FF"></i></a>
            </td>
            <td align="left" nowrap="nowrap">
				<a href="eams_forms/<%= eams.name.trim() %>.pdf" target="_blank" title="Click to review PDF" class="white_text">Review <%= eams.name.trim() %>.pdf</a>
			</td>
            <td>
            	<%= eams.status %>
            </td>
            <td>
            	<input class="eams_category" type="text" value="<%= eams.category %>" id="eams_category_<%= eams.id %>" />
                <div id="eams_category_button_<%= eams.id %>" style="display:inline-block; visibility:hidden">&nbsp;<button id="eams_category_save_<%= eams.id %>" class="save btn btn-transparent border-green" style="width:20px; border:0px solid"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></button></div>
            </td>
            <td>
            	<a class="delete_icon" id="confirmdelete_eamsform_<%=eams.eams_form_id%>" title="Click to delete <%= eams.display_name %> form" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FF0000;">&nbsp;</i></a>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>

</div>
