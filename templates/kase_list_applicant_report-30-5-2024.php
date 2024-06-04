<% if (month!="" || year!="") { %>
<input id="kases_attorney_filter" value="<%=filter_attorney %>" type="hidden" />
<input id="kases_worker_filter" value="<%=filter_worker %>" type="hidden" />
<table border="0" cellpadding="2" cellspacing="0" style="width:90%" align="center">  		
    <thead>
    <tr class="kase_list_header">
        <td valign="top"><img src="https://www.ikase.website/img/ikase_logo_login.png" height="32" width="77"></td>
        <td align="left" colspan="6">
        
            <div style="float:right">
                <em>Found <span id="found_count"><%=kaseslist.length %></span></em>
            </div>
            <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span>
        </td>
      </tr>
    <tr class="kase_list_header">
        <th style="font-size:1.5em" align="center" colspan="6">
           <%=title %>
        </th>
    </tr>
    </thead>
</table>
<% } %>
<table cellpadding="3" cellspacing="0" border="0" style="margin-top:20px; width:90%" align="center">
  	<thead>
    <tr>
      <th colspan="6" align="left">
      	<% if ((month!="" && year!="") || referring!="") { %>
        <div style="float:right" id="filters_holder">
        	&#8592;&nbsp;<a id="hide_list" style="text-decoration:underline; cursor:pointer; font-weight:normal; font-size:0.9em" title="Click to return to Year/Month Summary">Summary</a>
            &nbsp;
            <a id="closed_kases_filter" style="background:red;color:white;cursor:pointer" title="Click to show closed cases only">Closed Kases</a>
            &nbsp;
            <a id="open_kases_filter" style="background:green;color:white;cursor:pointer" title="Click to show open cases only">Open Kases</a>
            &nbsp;
            <div id="send_kases_holder" style="display:inline-block"><button id="send_kases" class="btn btn-success btn-xs">Send Kases to <%=user_customer_name %></button></div>
            &nbsp;
        </div>
        
        <span style="font-weight:bold; font-size:1.2em"><div id="table_month_year" style="display:inline-block"></div><%=month %> <%=year %></span>:<span style="font-weight:normal"> (<%=kaseslist.length %>)</span>
        <% } %>
      </th>
    </tr>
    
    <tr>
    <th align="left" style="font-size:1em; border-bottom:1px solid black;">Applicant</th>
    <th align="left" style="font-size:1em; border-bottom:1px solid black;">
       	Address/Phone
    </th>
    <th align="left" style="font-size:1em; border-bottom:1px solid black;">
       	Case Name
    </th>
    <!--
    <th align="left" style="font-size:1em; border-bottom:1px solid black;" id="corporation_type_header">
       	Referrer
    </th>
    -->
    <th align="left" style="font-size:1em; border-bottom:1px solid black;">
       	ADJ
    </th>
    <th align="left" style="font-size:1em; border-bottom:1px solid black;; width:100px">
       	DOI
    </th>
    <th align="left" style="font-size:1em; border-bottom:1px solid black;">
    	Atty</th>
      <th align="left" style="font-size:1em; border-bottom:1px solid black; width:100px">
       	Case Type
    </th>
    
    <th align="left" style="font-size:1em; border-bottom:1px solid black;">
       	Status
    </th>
    
    
    
    </tr>
    </thead>
    <tbody>
    <% _.each( kaseslist, function(kase) { %>
    	<tr style="border-top:1px solid black" class="kase_data_row injury_row_<%= kase.id %> kase_data_row_<%= kase.case_id %> kase_status_<%=kase.case_status.toLowerCase() %>">
        	<td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><%=kase.full_name %></td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black">
            	<%=kase.applicant_full_address %>
                <br />
                <%=kase.applicant_phone %>
            </td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black">
	            <div style="float:right; font-size:0.8em; padding-right:10px; width:250px; text-align:left">
	                Submitted: <%=moment(kase.submittedOn).format("MM/DD/YY") %><%=kase.months_diff %>
                </div>
                Case Number: <a href="v8.php?n=#kase/<%=kase.case_id %>" target="_blank" id="case_number_<%=kase.case_id %>"><%=kase.case_number %></a>
                <br />
                <%=kase.name.trim() %>
            </td>
            <!--<td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap"><%=kase.referring %></td>-->
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><%=kase.adj_number %></td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><%=kase.doi %></td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><span class="listing_item attorney_name"><%=kase.attorney_name.toUpperCase() %></span></td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><%=kase.case_type %></td>
            
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black">
            	<span style="<%=kase.closed_indicator %>"><%=kase.case_status.toLowerCase() %></span>
                <% if (kase.case_substatus != "") { %>
                &nbsp;/&nbsp;<%=kase.case_substatus %>
                <% } %>
              <% if (kase.case_subsubstatus != "") { %>
                &nbsp;/&nbsp;<%=kase.case_subsubstatus %>
                <% } %>
            </td>
        </tr>
    	<tr style="border-top:1px solid black" class="kase_data_row injury_row_<%= kase.id %> kase_data_row_<%= kase.case_id %> kase_status_<%=kase.case_status.toLowerCase() %>">
    	  <th align="left" valign="top" style="">Notes:
          	<div class="save_button_holder" style="display:none">
            	<button id="notes_button_<%= kase.id %>" onclick="sendKase(event)">Send</button>
            </div>
          </th>
    	  <td colspan="7" align="left" valign="top" style="">
          	<div style="width:100%; height:50px; border:1px solid #999" id="note_holder_<%= kase.id %>" class="note_holder">&nbsp;</div>
          </td>
   	  </tr>
    <% }); %>
    </tbody>
</table>
<div id="kase_list_applicant_report_all_done"></div>
<script language="javascript">
$("#kase_list_applicant_report_all_done" ).trigger( "click" );
</script>