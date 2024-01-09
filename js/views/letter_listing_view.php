<?php
session_start();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}
?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this template?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
	<div class="glass_header">
	    <div style="float:right;">
        	<a id="show_generated" class="white_text" style="cursor:pointer">Show Filled-Out Letters</a><a id="show_all" class="white_text" style="display:none; cursor:pointer">Show All</a>&nbsp;
			
			<div class="btn-group">
				<input id="letter_searchList" type="text" class="search-field" placeholder="Search Templates" autocomplete="off">
				<a id="letter_clear_search" style="position: absolute;
				right: 2px;
				top: 0;
				bottom: 2px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
    	<div style="width:250px">
        	<span style="font-size:1.2em; color:#FFFFFF" id="document_form_title">Templates</span>
        </div>
    </div>
    <div id="preview_panel" style="position:absolute; width:800px; display:none; z-index:2"></div>
    <div id="view_document" style="position:absolute; width:950px; height:600px; display:none; z-index:2; border:1px solid black; background:black">
        <div style="float:right"><a href="javascript:closeDocument()" title="Click to close preview" style="color:white; text-decoration:none">close</a></div>
    </div>
    <div id="upload_documents" style="border:0px solid yellow"></div>
    <table id="letter_listing" class="tablesorter letter_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
        	<th width="2%">&nbsp;</th>
            <th width="10%">Name</th>
            <th width="10%">Type</th>
            <th width="10%">Category</th>
            <th width="10%">Description</th>
            <th width="20%">&nbsp;</th>
            <th width="10%">&nbsp;</th>
            <th>&nbsp;
            </th>
        </tr>
        </thead>
        <tbody>
       <% _.each( templates, function(template) {
       		template.document_filename = template.document_filename.replace("templates/", "");
       	%>
       	<tr class="letter_data_row letter_row_<%=template.document_id%>">
        	<td align="left" nowrap="nowrap">
            	<div style="float:left">
                    <div style="display:inline-block">
                	<a title="Click to compose a new letter" class="create_letter" id="compose_letter_<%=case_id%>_<%=template.document_id%>" data-toggle="modal" data-target="#myModal4" style="cursor:pointer"><i class="glyphicon glyphicon-file" style="color:#FFFF00">&nbsp;</i></a>
                    </div>
                </div>
            </td>
            <td align="left">
              <a id="thumbnail_<%=template.document_id%>" href="uploads/<?php echo $_SESSION['user_customer_id']; ?>/templates/<%= template.document_filename %>" target="_blank" class="list_link">
                <%= template.document_name %>
                </a>
                <input id="document_id_<%=template.document_id%>" name="document_id_<%=template.document_id%>" type="hidden" class="document_input" value="<%=template.document_id%>" />
                
            </td>
            <td align="left" nowrap="nowrap">
              <%=template.description_html %>
            </td>
            <td align="left">
            	<%=template.document_extension.replace("docx", "") %>
            </td>
            <td align="left" nowrap="nowrap">
            	<%= template.description %>
            </td>
            <td align="left" id="template_letter_<%=template.id%>" class="letter_generated_cell" nowrap>
            	<%= template.document_names %>&nbsp;&nbsp;<i class="glyphicon glyphicon-pencil" style="color:#FF0000;">&nbsp;</i>&nbsp;&nbsp;<i class="glyphicon glyphicon-trash" style="color:#FF0000;">&nbsp;</i>
            </td>
            <td align="left" nowrap>
            	**<%= template.source %>
            </td>
            <td align="right">
            	&nbsp;
            </td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>
