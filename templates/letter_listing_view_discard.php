<?php
require_once('../shared/legacy_session.php');
?>
<div id="confirm_delete" style="display:none; position:absolute; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051;">
	<div style="width:350px; margin-left:auto; margin-right:auto;">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this letter?
    <div style="padding:5px; text-align:center"><a id="delete_letter" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_letter" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div class="letter_listing">
	<div class="glass_header">
        <div style="float:right;">
        	<select id="typeFilter" class="modal_input" style="margin-top:-2px;">
                <option value="">Filter by Type</option>
              </select>
            
            <input id="case_id" name="case_id" type="hidden" value="" />
            <input id="letters_searchList" type="text" class="search-field" placeholder="Search Letters" autocomplete="off" onkeyup="findIt(this, 'letter_listing', 'letter')">
        </div>
       
        <span style="font-size:1.2em; color:#FFFFFF">Letters</span>
        <a title="Click to compose a new letter" class="compose_new_letter" id="compose_letter" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-plus-sign" style="color:#99FFFF">&nbsp;</i></a>          
        
    </div>
    <div id="letter_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white; border:0px solid pink" class="attach_preview_panel"></div>
    <table id="letter_listing" class="tablesorter letter_listing" border="1" cellpadding="0" cellspacing="0" width="100%">
        <thead>
        <tr>
        	<th style="font-size:1.5em; width:45%; text-align:left">
                Date
            </th>
            <th style="font-size:1.5em; width:25%; text-align:left">
                Title</th>
            <th style="font-size:1.5em; text-align:left; width:25px">&nbsp;    
            </th>
        </tr>
        </thead>
        <tbody>
       <% 
       var current_type;
       _.each( letters, function(letter) {
       	document_name = letter.document_name;
        arrDocument = document_name.split("/");
        document_name = arrDocument[arrDocument.length-1]; %>
       	<tr class="letter_data_row letter_data_row_<%=letter.id %>">
        	<td colspan="3">
            	<table style="width:100%" border="1">
                <tr>
                    <td style="font-size:1.5em; width:45%;" align="left">
                        <%=letter.document_date %>
                    </td>
                    <td style="font-size:1.5em; width:25%;" align="left">
                    	<a href="<%=letter.document_filename %>.docx" target="_blank"><%=document_name %></a>
                    </td>
                    <td align="right">
						<?php if (strpos($_SESSION['user_role'], "admin") !== false) { ?>
                        	<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_letter" id="delete_<%= letter.id %>" title="Click to delete"></i>
						<?php } else { ?>
                        &nbsp;
                        <?php } ?>
                   </td>
		        </tr>
            </table>
          </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
    <div id="pager" class="pager">
        <form>
            <img src="img/first.png" class="first"/>
            <img src="img/prev.png" class="prev"/>
            <input type="text" class="pagedisplay"/>
            <img src="img/next.png" class="next"/>
            <img src="img/last.png" class="last"/>
            <select class="pagesize">
                <option selected="selected" value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
                <option  value="40">40</option>
            </select>
        </form>
    </div>

</div>
