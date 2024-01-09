<?php
require_once('../shared/legacy_session.php');
session_write_close();

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
			<div class="btn-group">
            	
            	 <label for="template_searchList" id="label_search_template" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search <%=title %>s</label>
            	
				<input id="template_searchList" type="text" class="search-field" placeholder="" autocomplete="off">
				<a id="template_clear_search" style="position: absolute;
				right: 2px;
				top: 0px;
				bottom: 2px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
    	<div style="width:250px">
        	<span style="font-size:1.2em; color:#FFFFFF" id="document_form_title"><%=title %> Templates</span><span style="color:white">&nbsp;&nbsp;(<%=templates.length %>)</span>
        </div>
    </div>
    <div id="preview_panel" style="position:absolute; width:800px; display:none; z-index:2"></div>
    <div id="view_document" style="position:absolute; width:950px; height:600px; display:none; z-index:2; border:1px solid black; background:black">
        <div style="float:right"><a href="javascript:closeDocument()" title="Click to close preview" style="color:white; text-decoration:none">close</a></div>
    </div>
  <div id="upload_documents" style="border:0px solid yellow"></div>
    <table id="template_listing" class="tablesorter template_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
        	<th width="2%" style="font-size:1.5em">&nbsp;</th>
            <th width="10%" style="font-size:1.5em">Name</th>
            <th width="10%" style="font-size:1.5em">Type</th>
            <% if (!blnInvoices) { %>
            <th width="10%" style="font-size:1.5em">Category</th>
            <th width="10%" style="font-size:1.5em">Description</th>
            <th width="10%">&nbsp;</th>
            <% } else { %>
            <th width="10%">&nbsp;</th>
            <% }%>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
       <% 
       var current_cat = "";
       _.each( templates, function(template) {
       		var the_cat = template.document_extension;
            if (!blnInvoices) {
            if (current_cat != the_cat) {
            	current_cat = the_cat;
            %>
        	<tr class="cat_row row_<%= the_cat.replaceAll(" ", "") %>">
                <td colspan="7">
                    <div style="width:100%; 
	text-align:left; 
	font-size:1.8em; 
	background:#CFF; 
	color:red;"><%= the_cat.capitalizeWords() %></div>
                </td>
            </tr>
        <% }
        }
        template.document_filename = template.document_filename.replace("templates/", "");
       	%>
       	<tr class="template_data_row template_row_<%=template.document_id%>">
        	<td align="left" nowrap="nowrap">
            	<div style="float:left">
                	<?php if (strpos($_SESSION['user_role'], "admin") !== false) { ?>
                	<div style="display:inline-block">
                    <a name="document_save_<%=template.document_id%>" id="document_save_<%=template.document_id%>" class="save_icon" style="display:none; cursor:pointer" title="Click to Save"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></a>
                <i class="glyphicon glyphicon-saved" style="color:#CCCCCC" id="disabled_save_<%=template.document_id%>">&nbsp;</i>
                	</div>
                	<?php } ?>
                    <!--display:inline-block; -->
                    <div style="display:none">
                	<a title="Click to compose a new letter" class="create_letter" id="compose_letter_<%=case_id%>_<%=template.document_id%>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-file" style="color:#FFFF00">&nbsp;</i></a>
                    </div>
                </div>
            </td>
            <td align="left">
            	<?php if (strpos($_SESSION['user_role'], "admin") !== false ) { ?>
                <input id="document_name_<%=template.document_id%>" name="document_name_<%=template.document_id%>" type="text" class="document_input" value="<%=template.document_name%>" style="width:350px" /><br />
                <?php } ?>
              <a id="thumbnail_<%=template.document_id%>" href="uploads/<?php echo $_SESSION['user_customer_id']; ?>/templates/<%= template.document_filename.replace("#", "%23") %>" target="_blank" class="list_link">
                <span class="search_template_item"><%= template.document_name %></span>
                </a>
                <input id="document_id_<%=template.document_id%>" name="document_id_<%=template.document_id%>" type="hidden" class="document_input" value="<%=template.document_id%>" />
                <% if (template.document_extension=="Invoice" && template.document_name!="Activity Bill") { %>
                <div>
                	<button class="invoice_items btn btn-xs btn-primary" id="invoice_items_<%=template.document_id%>_0">New Invoice Template</button>
                </div>
                <% } %>  
            </td>
            <td align="left" nowrap="nowrap">
            	<?php if (strpos($_SESSION['user_role'], "admin") !== false ) { ?>
            	<select class="document_input description_html" name="description_html_<%=template.document_id%>" id="description_html_<%=template.document_id%>" style="width:90px">
                <option value="">Select Type</option>
                <option value="WCAB" <% if (template.description_html=="WCAB") { %>selected<% } %>>WCAB</option>
                <option value="PI" <% if (template.description_html=="PI") { %>selected<% } %>>PI</option>
              </select>
              <?php } else { ?>
              <%=template.description_html %>
              <?php } ?>
            </td>
            <% if (!blnInvoices) { %>
            <td align="left">
            	<?php if (strpos($_SESSION['user_role'], "admin") !== false ) { ?>
            	<select class="document_input" name="document_category_<%=template.document_id%>" id="document_category_<%=template.document_id%>" style="width:120px">
                    <option value="" <% if (template.document_extension=="") { %>selected<% } %>>Select Category</option>
                    <option value="Adjuster" <% if (template.document_extension=="Adjuster") { %>selected<% } %>>Adjuster</option>
                    <option value="Applicant" <% if (template.document_extension=="Applicant") { %>selected<% } %>>Applicant</option>
                    <option value="Carrier" <% if (template.document_extension=="Carrier") { %>selected<% } %>>Carrier</option>
                    <option value="Defense" <% if (template.document_extension=="Defense") { %>selected<% } %>>Defense Attorney</option>
                    <option value="Employer" <% if (template.document_extension=="Employer") { %>selected<% } %>>Employer</option>
                    <option value="Invoice" <% if (template.document_extension=="Invoice") { %>selected<% } %>>Invoice</option>
                    <option value="Judge" <% if (template.document_extension=="Judge") { %>selected<% } %>>Judge</option>
                    <option value="Medical" <% if (template.document_extension=="Medical") { %>selected<% } %>>Medical Provider</option>
                    <option value="Miscellaneous" <% if (template.document_extension=="Miscellaneous") { %>selected<% } %>>Miscellaneous</option>
                    <option value="New Kases" <% if (template.document_extension=="New Kases") { %>selected<% } %>>New Kases</option>
                    <option value="Pleading" <% if (template.document_extension=="Pleading") { %>selected<% } %>>Pleading</option>
                    <option value="Subin" <% if (template.document_extension=="Subin") { %>selected<% } %>>Sub In Attorney</option>
                    <option value="Any" <% if (template.document_extension=="Any") { %>selected<% } %>>Letter to Any Party</option>
                </select>
                <?php } else { ?>
                <%=template.document_extension.replace("docx", "") %>
                <?php } ?>
            </td>
            <td align="left" nowrap="nowrap">
            	<?php if (strpos($_SESSION['user_role'], "admin") !== false ) { ?>
            	<textarea name="document_description_<%=template.document_id%>" id="document_description_<%=template.document_id%>" rows="3" style="width:392px"><%=template.description %></textarea>
                <?php } else { ?>
                <%= template.description %>
                <?php } ?>
            </td>
            <td align="left" nowrap="nowrap">
            	<label style="width:100px; font-weight:bold; display:inline-block">Lettehead</label>
                &nbsp;
            	<input type="radio" name="source_<%=template.document_id%>" id="letterhead_yes_<%=template.document_id%>" value="Y" class="document_input" <% if (template.source=="" || template.source=="Y") {%>checked="checked"<% } %> /> Y
                &nbsp;&nbsp;&nbsp;
                <input type="radio" name="source_<%=template.document_id%>" id="letterhead_no_<%=template.document_id%>" value="N" class="document_input" <% if (template.source=="no_letterhead") {%>checked="checked"<% } %> /> N
                <br />
                <input title="Click for Client's Name header" type="radio" name="source_<%=template.document_id%>" id="letterhead_client_<%=template.document_id%>" value="N" class="document_input" <% if (template.source=="clientname_letterhead") {%>checked="checked"<% } %> /> 
                Client's Name
            </td>
            <% } else { %>
            <td align="left" nowrap>
            	<% if (template.document_extension=="Invoice" && template.document_name!="Activity Bill") { %>
                 <div style="margin-top:5px">
                 	<div id="template_invoices_holder_<%=template.document_id%>" style="padding-top:10px"></div>
                 </div>
                 <% } %>
            </td>
            <% } %>
            <td align="right">
            	<a class="delete_icon" id="confirmdelete_document_<%=template.document_id%>" title="Click to delete document from this kase" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FF0000;">&nbsp;</i></a>
                <?php if (strpos($_SESSION['user_role'], "admin") !== false ) { ?>
                <div style="float:left">
                <%=template.propagate_link %>
                </div>
                <?php } ?>
            </td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>
<div id="template_listing_all_done"></div>
<script language="javascript">
$( "#template_listing_all_done" ).trigger( "click" );
</script>
