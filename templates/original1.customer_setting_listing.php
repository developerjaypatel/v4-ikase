<style>
#back-to-top {
    position: fixed;
    bottom: 40px;
    right: 40px;
    z-index: 9999;
    width: 32px;
    height: 32px;
    text-align: center;
    line-height: 30px;
    background: #f5f5f5;
    color: #444;
    cursor: pointer;
    border: 0;
    border-radius: 2px;
    text-decoration: none;
    transition: opacity 0.2s ease-out;
    opacity: 0;
}
#back-to-top:hover {
    background: #e9ebec;
}
#back-to-top.show {
    opacity: 1;
}
</style>
<a href="#" id="back-to-top" title="Back to top">&uarr;</a>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this setting?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
	<div class="glass_header">
        <div style="float:right;">
            <input id="customer_setting_searchList" type="text" class="search-field" placeholder="Search" autocomplete="off" onkeyup="findIt(this, 'customer_setting_listing', 'customer_setting')">
        </div>
       
        <span style="font-size:1.2em; color:#FFFFFF" class="list_title"><%=level.capitalizeWords() %> Settings</span>
         
        <a title="new setting" id="new_setting"  data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="color:#FFFFFF; text-decoration:none; margin-left:50px;cursor:pointer">
            <button class="kase edit btn btn-transparent" style="color:white; border:0px solid; width:20px">
                <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
            </button>
        </a>
    </div>
    <table id="customer_setting_listing" class="tablesorter customer_setting_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th style="font-size:1.5em; width:300px">
                Setting
            </th>
            <th style="font-size:1.5em; width:200px">
                Value
            </th>
            <th style="font-size:1.5em">
                Default
            </th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
       <% 
       var current_category = "";
       _.each( customers_setting, function(customer_setting) {
	       //we might have a new category
            var the_category = customer_setting.category;
            if (current_category != the_category) {
                current_category = the_category;
            %>
                <tr>
                    <td colspan="4">
                    <div style="width:100%; 
	text-align:left; 
	font-size:1.8em; 
	background:#CFF; 
	color:red;">
    				<div style="width:500px; text-align:left">
                        <div style="float:right; color:black; font-size:0.8em; font-weight:normal; padding-right:5px">
                            <a id="open<%=the_category %>" class="expand_category" style="font-size:0.8em; cursor:pointer; color:black">expand</a>
                            <a id="close<%=the_category %>" class="collapse_category" style="font-size:0.8em; cursor:pointer; color:black; display:none">collapse</a>
                        </div>
                        <%= the_category.replaceAll("_", " ").capitalizeWords() %>
                        &nbsp;
                        <a title="new <%= the_category.replaceAll("_", " ").capitalizeWords() %> setting" id="new_setting_<%=the_category %>" class="compose_new_category" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="color:#FFFFFF; text-decoration:none; margin-left:50px;cursor:pointer">
                            <button class="kase edit btn btn-transparent" style="color:white; border:0px solid; width:20px">
                                <i class="glyphicon glyphicon-plus-sign" style="color:#03F">&nbsp;</i>
                            </button>
                        </a>
                        </div>
                    </div>
                    </td>
                </tr>
            <% } %>
            <% if (customer_setting.setting_level == level) { %>
       	<tr class="customer_setting_data_row customer_setting_data_row_<%= customer_setting.uuid %> setting_rows_<%=the_category %>" style="display:none">
           <td style="font-size:1.5em">
                <a title="Click to edit setting" class="compose_new_setting white_text" id="compose_setting_<%= customer_setting.uuid %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><%=customer_setting.setting %></a>
            </td>
                <td style="font-size:1.5em">
                	<% 
                     if (the_category == "calendar_colors") {
                    %>
                    	<div id="color_swatch" style="background:<%=customer_setting.setting_value%>; border:#FFFFFF; height:20px; width:200px; margin-left:0px; margin-top:0px">&nbsp;</div>
                    <%  } else {  %>
                    	<%=customer_setting.setting_value %>
                    <%  }  %>
                </td>
                <td style="font-size:1.5em">
                	<% 
                     if (the_category == "calendar_colors" || the_category == "calendar_type") {
                    %>
                    	<div id="color_swatch" style="background:<%=customer_setting.default_value%>; border:#FFFFFF; height:20px; width:200px; margin-left:0px; margin-top:0px"><%=customer_setting.default_value%></div>
                    <%  } else {  %>
                    	<%=customer_setting.default_value%>
                    <%  }  %>
                </td>
				<td align="right">
                	<a class="delete_icon" id="confirmdelete_setting_<%= customer_setting.uuid %>" title="Click to delete setting" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FF0000;">&nbsp;</i></a>
                </td>
        </tr>
        <% } %>
        <% }); %>
        </tbody>
    </table>
</div>