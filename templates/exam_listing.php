<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
<div class="exam">
	<div class="glass_header" style="width:100%">
        <input type="hidden" name="case_id" id="case_id" value="<%= exams.case_id %>" />
        <span style="font-size:1.2em; color:#FFFFFF">Medical Index</span>&nbsp;&nbsp;<span class="white_text">(<%=exams.length %>)</span>
        <span id="new_exam_holder">
            <button title="Click to create an Exam" id="compose_exam" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer">New Exam</button>
            &nbsp;
            <span id="medindex_holder" style="color:white"></span>
            <button title="Click to create Med Index Report" id="compose_report" class="btn btn-default btn-sm" style="display:none">Print Med Index Report</button>
            <span id="medindex_instructions" style="color:white; font-style:italic">Select Exam(s) below to create Med Index Report</span>
        </span>
        
        <div style="float:right; display:none">
            <a title="Click to create an Exam Report" id="med_inder_report" target="_blank" style="cursor:pointer; color:white" href="report.php#exams/<%= current_case_id %>">Print Med Index</a>
        </div>
    </div>
    <!--<div id="exam_preview_panel" style="position:absolute; width:auto; display:none; z-index:2; background:white; border:1px solid pink; padding:2px" class="attach_preview_panel"></div>
    -->
    <div style="float:right; width:55%; height:600px; padding-top:5px; padding-left:10px; display:none; background: url(img/glass_dark.png" id="preview_pane_holder">
    	<div>
            <div style="display:inline-block; width:97%" id="preview_block_holder">
                <div id="preview_title" style="
                    margin-bottom: 30px;
                    color: white;
                    font-size: 1.6em;
                ">
				</div>
                <div class="white_text" id="preview_pane"></div>
            </div>
        </div>
    </div>
    <div id="exam_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
    <div style="height:600px; overflow-y:scroll; width:100%" id="exam_list_outer_div">
    <table id="exam_listing" class="tablesorter exam_listing" border="1" cellpadding="0" cellspacing="0" style="width:100%">
        <thead>
        <tr>
            <th style="font-size:1.5em; width:4%" nowrap="nowrap">
            	<span style="font-size:0.8em">Select&nbsp;All&nbsp;</span><input type="checkbox" class="exam_report" id="select_all_exams" />
            </th>
            <th style="font-size:1.5em; width:15.1%">
				Provider
            </th>
            <th style="font-size:1.5em; width:15.1%">
				Type
            </th>
            <th style="font-size:1.5em; width:13%">
                Status
            </th>
            <th style="font-size:1.5em; width:10%">
                Specialty
            </th>
            <th style="font-size:1.5em; width:8.8%">
                Requestor
            </th>
            <th style="font-size:1.5em; width:10%">
            Exam&nbsp;Date </th>
			<th style="font-size:1.5em; width:15%">
                FS Date
            </th>
            <th style="font-size:1.5em; width:15%">
                P&S
            </th>
            <th style="font-size:1.5em">&nbsp;</th>
        </tr>
        </thead>
		
        <tbody>
		<% _.each( exams, function(exam) { %>
       	<tr class="exam_data_row exam_row_<%= exam.id %>">
                <td style="font-size:1.5em;" nowrap="nowrap">
                	<div style="float:right; margin-left:5px;">
                        <%= exam.attachment_link %>
                    </div>
					<div style="display:inline-block">
                    	<a id="edit_exam_<%= exam.id %>_<%= exam.corporation_id %>" class="white_text edit_exam" style="cursor:pointer" title="Click to edit this Index" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false"><i style="font-size:15px;color:#06f; cursor:pointer; visibility:visible" class="glyphicon glyphicon-edit" title="Click to Edit Med Index"></i></a>
                    </div>
                    <div style="display:inline-block">
                        <a name="document_save_<%= exam.id %>" id="document_save_<%= exam.id %>" class="save_icon" style="display:none; cursor:pointer" title="Click to Save"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></a>
                        <i class="glyphicon glyphicon-saved" style="color:#CCCCCC" id="disabled_save_<%= exam.id %>">&nbsp;</i>
                        &nbsp;
                    </div>
                </td>
                <td style="font-size:1.5em; width:">
					<%= exam.company_name %>
                </td>
                <td style="font-size:1.5em; width:">
					<%= exam.exam_type %>
                </td>
                <td style="font-size:1.5em; width:">
                    <%= exam.exam_status %>
               </td>
               <td style="font-size:1.5em; width:">
                    <%=exam.specialty %>
                </td>
                <td style="font-size:1.5em; width:;">
                	<%= exam.requestor %>
                </td>
				<td style="font-size:1.5em; width:;">
					<%= exam.exam_dateandtime%>
				</td>
				<td style="font-size:1.5em; width:;">
					<%= exam.fs_date %>
                </td>
				<td>
					<% if (exam.permanent_stationary == "Y") { 
						   exam.permanent_stationary = "<span>&#10003;</span>";
					   }
					%>
					<%= exam.permanent_stationary %>
				</td>
				<td>
                	<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_exam" id="delete_<%= exam.id %>" title="Click to delete"></i>
				</td>
		        </tr>
       	<tr class="exam_data_row exam_row_<%= exam.id %>">
       	  <td style="font-size:1.5em; width:">&nbsp;</td>
       	  <td colspan="8" style="font-size:1.5em;">
          		<span id="exam_comments_<%= exam.id %>" class="exam_comments">
                	<%= exam.comments %>
                </span>
                <textarea id="exam_edit_<%= exam.id %>" style="display:none; width:500px; height:50px" class="exam_edit"><%= exam.comments %></textarea>
                <div id="edit_instructions_<%= exam.id %>" style="color:white; font-style:italic; display:none">
                	<span>You may edit the Exam comments for publication in Med Index Report</span>
     		   </div>
          </td>
       	  <td>&nbsp;</td>
     	  </tr>
		<% }); %>
        </tbody>
		
    </table>
    </div>
</div>
