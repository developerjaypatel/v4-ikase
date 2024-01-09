<div style="float:right; width:40%; height:600px; padding-top:0px; padding-left:10px; display:none; background: url(img/glass_edit_header_kase.png;" id="preview_pane_holder">
    <div>
        <div style="display:inline-block; width:97%; height:600px; overflow-y:scroll" id="preview_block_holder">
            <div id="preview_title" style="
                margin-bottom: 30px;
                color: white;
                font-size: 1.60em;
            ">
            </div>
            <div class="white_text" id="preview_pane" style="display:none"></div>
        </div>
    </div>
</div>
<div style="height:600px; overflow-y:scroll; width:100%" id="kase_summary_list_outer_div">
	<div class="glass_header" style="width:100%; height:45px">
    	<div style="float:right; padding-right:10px">
        	<a id="print_kase_summary" style="cursor:pointer" class="white_text">Print</a>
        </div>
    	<span style="font-size:1.2em; color:#FFFFFF" id="report_title">Employee Open Kases Summary</span>
    </div>
    <table id="kase_summary_listing" class="tablesorter kase_summary_listing" border="1" cellpadding="0" cellspacing="0" style="width:500px">
        <thead>
        <tr>
            <th align="left" width="1%">User</th>
            <th align="left">Kases</th>
            <th align="left">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
            <%  _.each( kases, function(kase) {
             %>
                <tr class="user_kase_summary_row user_kase_row_<%=kase.user_id %>">
                    <td width="1%" nowrap>
                    	<%=kase.user_name.trim().toLowerCase().capitalizeAllWords() %>
                    </td>
                    <td align="left">
                    	<div style="float:right">
	                    	<button id="user_<%=kase.user_id %>" class="btn btn-sm kase_user">List</button>
                        </div>
                        <%=kase.kase_count %>
                    </td>
                    <td align="left">
                    	<button class="btn btn-sm btn-primary workload" id="workload_<%=kase.user_id %>">Workload</button>
                    </td>
                </tr>
            <% }); %>
        </tbody>
    </table>
</div>