<div class="exam_view exam" id="gridster_exam" style="width:100%">
    <form id="exam_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="exam" />
        <input id="table_id" name="table_id" type="hidden" value="<%= exam_id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
		<input id="corp_id" name="corp_id" type="hidden" value="<%= corp_id %>" />
        <input id="document_id" name="document_id" type="hidden" value="<%= document_id %>" />
<input id="billing_time" name="billing_time" type="hidden" value="" />
    	
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
        </div>
		<% if (exam_dateandtime=="Invalid date" || exam_dateandtime=="") {
				exam_dateandtime = "";
		   }
		   if (fs_date=="Invalid date" || fs_date=="" || fs_date=="1969-12-31") {
				fs_date = "";
		   }
		%>
		<table border="0" cellpadding="3" style="width:100%">
            <tr class='partie_row attach_row' style="display:none">
                <th align="left" valign="top">Case</th>
                <td valign="top" id="case_name_holder">&nbsp;</td>
            </tr>
            <tr class='partie_row attach_row' style="display:none">
                <th align="left" valign="top">Document</th>
                <td valign="top" id="document_name_holder">
                	<a href="<%=attachment_link %>" target="_blank" class="white_text"><%= decodeURIComponent(document_name) %></a>
                </td>
            </tr>
            <tr class='partie_row'>
                <th width="18%" align="left" valign="top">Medical</th>
                <td valign="top">
                  <select style="width:389px" class="modalInput" id="primary" name="primary">
                        <option value="">Select a Primary Physician from List</option>
                        <%=medical_providers %>
                    </select>
                </td>
            </tr>
			<tr>
			   <th style="width:18%" align="left" valign="top">
				  Exam Date
			   </th>
			   <td>
				  <input value="<%= exam_dateandtime %>" name="exam_dateandtimeInput" id="exam_dateandtimeInput" class="kase exam_view" placeholder="MM/DD/YYYY" style="margin-top:-26px;width:389px" autocomplete="off" />
			   </td>
			</tr>
			<tr>
			   <th align="left" valign="top">
				  Status
			   </th>
			   <td>
				  <input value="<%= exam_status %>" name="exam_statusInput" id="exam_statusInput" class="kase exam_view" placeholder="Status" style="margin-top:-43px;z-index:3259; width:389px" />
               </td>
			</tr>
			<tr>
			   <th align="left" valign="top">
				  Type
			   </th>
			   <td>
				  <input value="<%= exam_type %>" name="exam_typeInput" id="exam_typeInput" class="kase exam_view" placeholder="Type" style="margin-top:-26px;width:389px" />
			   </td>
			</tr>
			<tr>
			   <th align="left" valign="top">
				  Specialty
			   </th>
			   <td>
				  <input value="<%= specialty %>" name="specialtyInput" id="specialtyInput" class="kase exam_view" placeholder="Specialty" style="margin-top:-26px;width:389px" />
			   </td>
			</tr>
			<tr>
			   <th align="left" valign="top">
				  Requestor
			   </th>
			   <td>
				  <input value="<%= requestor %>" name="requestorInput" id="requestorInput" class="kase exam_view" placeholder="Requestor" style="margin-top:-26px;width:389px" />
		       </td>
			</tr>
			<tr>
			   <th align="left" valign="top">
				  P&S
			   </th>
			   <td align="left" valign="top">
				  <input value="Y" type="checkbox" name="permanent_stationaryInput" id="permanent_stationaryInput" class="kase exam_view" placeholder="Permanent Stationary" <%= permanent_stationary_checked %> />
			   </td>
			</tr>
			<tr>
			   <th align="left" valign="top">
				  Filed/Serve Date
			   </th>
			   <td align="left" valign="top">
				  <input value="<%=fs_date %>" name="fs_dateInput" id="fs_dateInput" class="kase exam_view" placeholder="MM/DD/YYYY" style="margin-top:-26px;width:389px" autocomplete="off" />
			   </td>
			</tr>
			<tr>
			   <th colspan="2" align="left" valign="top">
				  Records Type
				    <textarea name="commentsInput" id="commentsInput" class="kase exam_view" placeholder="Comments" style="margin-top:-26px;width:389px"><%= comments %></textarea>
				</th>
			</tr>
            <% if (document_id!="") { %>
            <!--
            <tr>
			   <th align="left" valign="top">
				  Document
			   </th>
			   <td align="left" valign="top">
				  <a href="<%=attachment_link %>" target="_blank" class="white_text"><%= document_name %></a>
			   </td>
			</tr>
            -->
            <% } %>
			<tr>
			  <th colspan="2">
                <input type='hidden' id='send_document_id' name='send_document_id' value="" />
                <div id="message_attachments" style="width:90%"></div>
              </th>
		  </tr>
		</table>
    </form>
</div>
<div id="exam_all_done"></div>
<script language="javascript">
$( "#exam_all_done" ).trigger( "click" );
</script>