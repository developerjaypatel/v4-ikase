<% if (customer_id == 1033) { %>
    	<div class="glass_header">
            <div style="float:right">
            <a style="background:#CFF; color:black; padding:2px; cursor:pointer" id="search_qme" title="Click to search the EAMS database of QME Medical Provider">Import QMEs from EAMS</a>
            </div>
            <input id="case_id" name="case_id" type="hidden" value="<%=this.model.get('case_id') %>" />
        	<input id="case_uuid" name="case_uuid" type="hidden" value="<%=this.model.get('uuid') %>" />
    
            
            <div style="display:inline-block">
                <div style="border:0px solid green; text-align:left">
                    
                    <div class="white_text" style="display:inline-block; padding-left:5px">
                        <div style="float:right; display:none">
                            <span class='black_text'>&nbsp;|&nbsp;</span>
                        </div>
                        <span id="case_number_fill_in"></span><span id="adj_slot"><% if (this.model.get("adj_number")!="") { %><span class='black_text'>&nbsp;|&nbsp;</span><span id="adj_number_fill_in"></span><% } %></span><span class='black_text'>&nbsp;|&nbsp;</span><span id="case_type_fill_in"></span><span class='black_text'>&nbsp;|&nbsp;</span>Case&nbsp;Date:&nbsp;<span id="case_date_fill_in"></span><span class='black_text'>&nbsp;|&nbsp;</span>Claim&nbsp;#:&nbsp;<span id="claim_number_fill_in"></span><span id="claims_slot"><span class='black_text'>&nbsp;|&nbsp;</span>Claims&nbsp;:&nbsp;<span id="claims_fill_in"></span></span>
                        <br />
                        Status:&nbsp;<span id="case_status_fill_in"></span><% if (this.model.get("case_substatus")!="") { %><span class='white_text'>&nbsp;/&nbsp;</span><span class='white_text'><span id="case_substatus_fill_in"></span></span><% } %><% if (this.model.get("rating")!="") { %><span class='black_text'>&nbsp;|&nbsp;</span>Rating:&nbsp;<span id="rating_fill_in"></span><% } %><span id="language_slot"><% if (this.model.get("interpreter_needed")!="N") { %><span class='black_text'>&nbsp;|&nbsp;</span><span class="red_text white_background">Interpreter&nbsp;Needed&nbsp;for&nbsp;<span id="language_fill_in"></span></span><% } %></span>
                    </div>
                </div> 
            </div>
        </div>
        <br/>
    <% } %>
    <table border="0" cellpadding="2" cellspacing="0" style="width:50%" align="center">  		
    <thead>
    <tr>
        <td width="77"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
        <td align="left">
            <div style="float:right">
                <em>Found <span id="found_count"><%=exams.length %></span></em>
            </div>
            <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span>
        </td>
      </tr>
    <tr>
        <th style="font-size:1.5em" align="center" colspan="2">
            <%=case_number %> - <%=applicant_name %><br/><br/>Med Index Report
        </th>
    </tr>
    </thead>
</table>
    <table id="exam_listing" class="tablesorter exam_listing" border="0" cellpadding="0" cellspacing="0" style="width:50%" align="center">
        <thead>
        <tr>
            
            <th style="font-size:1.3em; border-bottom:1px solid black" align="left">
				Provider
            </th>
            <th style="font-size:1.3em; border-bottom:1px solid black" align="left">
                Exam Date
            </th>
			<th style="font-size:1.3em; border-bottom:1px solid black" align="left">
                FS Date
            </th>
            
        </tr>
        </thead>
		
        <tbody>
		<% _.each( exams, function(exam) { %>
       	<tr class="exam_data_row exam_row_<%= exam.id %>">
          
                <td style="font-size:1.3em; border-top:1px solid black">
					<%= exam.company_name %>
                </td>
                <td style="font-size:1.3em; border-top:1px solid black">
					<%= exam.exam_dateandtime %><br/>
                </td>
				<td style="font-size:1.3em; border-top:1px solid black">
					<%= exam.fs_date %><br/>
                </td>
				
		        </tr>
       	<tr class="exam_data_row exam_row_<%= exam.id %>">
       	  <td style="font-size:1.3em;">&nbsp;</td>
       	  <td style="font-size:1.3em;" colspan="2"><%= exam.comments %></td>
       	  
     	  </tr>
		<% }); %>
        </tbody>
		
    </table>

