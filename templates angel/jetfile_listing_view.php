<?php
include("../api/manage_session.php");
session_write_close();
if ($_SESSION['user_jetfile_id'] < 0 || $_SESSION['user_jetfile_id']=="") {
	die("Please contact Support to create your JetFile ID");
}
?>

<div>
	<div class="glass_header" style="width:100%; height:45px">
    	<div style="float:right; display:none" id="show_all_holder">
        <a id="show_all_jetfiles" class="white_text" style="cursor:pointer">Show All</a>
        </div>
        <div style="float:right">
        	<div class="btn-group">
            	<label for="jetfiles_searchList" id="label_search_jetfiles" style="font-size:1em; cursor:text; position:relative; top:0px; left:105px; width:100px; color:#999">Search Filings</label>
            
				<input id="jetfiles_searchList" type="text" class="search-field" placeholder="" autocomplete="off" style="height:33px; line-height:32px; margin-top:-5px">
				<a id="jetfile_clear_search" style="position: absolute;
				right: 2px;
				top: 0;
				bottom: 9px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
        <% if (main_filter!="errors") { %>
        <div style="float:right; padding-right:10px">
        	<button id="show_errors" class="btn btn-xs btn-danger">Errors</button>
        </div>
        <% } %>
        <% if (main_filter!="recent") { %>
        <div style="float:right; padding-right:10px">
        	<button id="show_recent" class="btn btn-xs btn-primary">Last 25</button>
        </div>
        <% } %>
        <div style="float:right; padding-right:10px">
        	<button id="show_all" class="btn btn-xs btn-success">All</button>
        </div>
        <span style="font-size:1.2em; color:#FFFFFF"><%=main_filter.capitalize() %><% if (main_filter=="") { %>EAMS<% } %> Submissions</span>&nbsp;<div style="position: relative;left: 160px; padding-left:3px; margin-top:-25px; color:white; font-size:0.8em; width:20px">(<%=jetfiles.length %>)</div>
    </div>
    <div id="jetfile_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
    <a id="jetfiles_top" name="jetfiles_top"></a>
    <table id="jetfile_listing" class="tablesorter jetfile_listing" border="1" cellpadding="0" cellspacing="0" style="width:100%; display:">
        <thead>
        <tr>
            <th style="font-size:1.17em; width:5%"></th>
            <th style="font-size:1.17em; width:5%">Case</th>
            <th style="font-size:1.17em; width:5%">Jetfile ID</th>
            <th style="font-size:1.17em; width:5%"">Forms</th>
            <th style="font-size:1.17em; width:5%"">PDFs</th>
            <th style="font-size:1.17em;">Filings</th>
            <th style="font-size:1.17em;">Status</th>
            <?php if($_SESSION['user_customer_id'] == 1100){?>
            <!-- <th style="font-size:1.17em;">Docusents</th> -->
            <th style="font-size:1.17em;">&nbsp;</th>
            <?php }?>
            <th style="font-size:1.17em;">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
       	<% _.each( jetfiles, function(jetfile) {
        %>
        <tr  class="jetfile_data_row jetfile_data_row_<%= jetfile.id %>">
        	<td align="left" valign="top"><%=jetfile.jetfile_id %></td>
            <td align="left" valign="top" nowrap="nowrap">
            	<a id="jetfile_case_<%=jetfile.case_id %>" class="jetfile_case_link white_text" style="cursor:pointer; text-decoration:underline"><%=jetfile.case_id + "-" + jetfile.injury_number %></a>
                <div>
                	<%=jetfile.full_name %>
                </div>
                <div>
                	DOI:&nbsp;<%=jetfile.doi %>
                </div>
                <div>
                	<button class="btn btn-sm btn-primary eams_search" id="eams_search_<%=jetfile.case_id %>_<%=jetfile.injury_id %>">EAMS Search</button>
                </div>
            </td>
            <td id="jetfile_case_id_<%=jetfile.case_id %>_<%=jetfile.injury_id %>" align="left" valign="top" nowrap="nowrap"><%=jetfile.jetfile_case_id %>
            </td>
            <td nowrap="nowrap">
            	<div id="app_holder_<%=jetfile.case_id %>_<%=jetfile.injury_id %>"><%=jetfile.app %></div>
                <div id="dor_holder_<%=jetfile.case_id %>_<%=jetfile.injury_id %>"><%=jetfile.dor %></div>
                <div id="dore_holder_<%=jetfile.case_id %>_<%=jetfile.injury_id %>"><%=jetfile.dore %></div>
                <div id="lien_holder_<%=jetfile.case_id %>_<%=jetfile.injury_id %>"><%=jetfile.lien %></div>
                <div id="unstruc_holder_<%=jetfile.case_id %>_<%=jetfile.injury_id %>"><%=jetfile.unstruc %></div>
            </td>
            <td align="left" valign="top">
            	<div style="display:">       	
                    <div><%=jetfile.app_pdf %></div>
                    <div><%=jetfile.dor_pdf %></div>
                    <div><%=jetfile.dore_pdf %></div>
                    <div><%=jetfile.lien_pdf %></div>
                    <div><%=jetfile.unstruc_pdf %></div>
                </div>
            </td>
            <td align="left" valign="top" nowrap="nowrap">
            	<div id="app_action_holder_<%=jetfile.case_id %>_<%=jetfile.injury_id %>"><%=jetfile.app_action %></div>
                <div id="dor_action_holder_<%=jetfile.case_id %>_<%=jetfile.injury_id %>"><%=jetfile.dor_action %></div>
                <div id="dore_action_holder_<%=jetfile.case_id %>_<%=jetfile.injury_id %>"><%=jetfile.dore_action %></div>
                <div id="lien_action_holder_<%=jetfile.case_id %>_<%=jetfile.injury_id %>"><%=jetfile.lien_action %></div>
                <div id="unstruc_action_holder_<%=jetfile.case_id %>_<%=jetfile.injury_id %>"><%=jetfile.unstruc_action %></div>
            </td>
            <td align="left" valign="top" style="max-width:800px">
            	<%=jetfile.app_status %><%=jetfile.app_message %>
                <div style="padding-top:5px">
                	<%=jetfile.submitted_by %>
                </div>
            </td>
            <?php if($_SESSION['user_customer_id'] == 1100){?>
            <td align="left" valign="top" style="min-width: 200px;max-width:800px">
                <div style="position: relative;float: left;margin: 0 5px;font-size: 14px;">
                <a target="_blank" class="white_text" href="/uploads/<?php echo $_SESSION['user_customer_id']?>/<%= jetfile.case_id %>/eams_forms/app_cover_final.pdf">Download Document</a>
                <br>
                <% if (jetfile.vendor_submittal_id != null) { 
                    %>uploaded by <%= jetfile.document_submitted_by %> on <br><%= 
                    jetfile.docucents_upload_date %> <%
                } %>
                <% if (jetfile.vendor_submittal_id != null) { 
                    %> <br><a target="_blank" class="white_text" target="_blank" href="/docusent/getPOS.php?vendor_submittal_id=<%= jetfile.vendor_submittal_id %>&user_customer_id=<?php echo $_SESSION['user_customer_id'];?>">get POS</a><br><%
                } %>
            </div>
                <% if (jetfile.vendor_submittal_id == null) { %>
                <div><button class="submission-button" id="<%= 'casefile_'+ jetfile.case_id +'_'+ jetfile.jetfile_case_id %><?php echo '_'. $_SESSION['user_customer_id'];?>_<%=jetfile.injury_id%>" >Send to Docucents</button>
                <% } %>
        </div>
            </td>
           <?php } ?>
            <td align="left" valign="top">
            	<div><%=jetfile.app_errors %></div>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>
<div id="jetfile_listing_all_done"></div>
<script language="javascript">
$( "#jetfile_listing_all_done" ).trigger( "click" );
</script>