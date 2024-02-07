<div style="float:right; width:50%">
    <div>
        <div id="disability_listing_header" class="glass_header">
            <div style="float:right;">
            <input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
                
                <div class="btn-group">
                    
                     <label for="disabilities_searchList" id="label_search_disability" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
                    
                    <input id="disabilities_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'disability_listing', 'disability')">
                    <a id="disabilities_clear_search" style="position: absolute;
                    right: 2px;
                    top: 0px;
                    bottom: 2px;
                    height: 14px;
                    margin: auto;
                    cursor: pointer;
                    border: 0px solid green;
                    "><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
                </div>
            </div>
            <div style="width:312px">
                <div style="float:right">
                <button id="compose_new_disability" class="btn btn-sm btn-primary" title="Click to add a Disability entry" style="margin-top:-5px">Add Disability</button> 
                </div>
                <span style="font-size:1.2em; color:#FFFFFF"><%=page_title %></span>
                &nbsp;&nbsp;<span class="white_text">(<%=disabilities.length %>)</span>
            </div>        
             
        </div>
        <table id="disability_listing" class="tablesorter disability_listing disability_listing_<%=page_title %>" border="0" cellpadding="0" cellspacing="1" style="font-size:1.1em">
            <thead>
            <tr>
                <th width="15%">
                    Ailment
                </th>
                <th width="1%">
                    Duration
                </th>
                <th width="1%">
                    Severity
                </th>
                <th width="1%" style="text-align:left" nowrap="nowrap">
                    Work Duty
                </th>
                <th width="1%" style="text-align:left">
                    Limits
                </th>
                <th width="1%" style="text-align:left">
                    Treatment
                </th>
                <th>
                    Description
                </th>
            </tr>
            </thead>
            <tbody>
            <% 
            _.each( disabilities, function(disability) {
            %>
            <tr class="disability_data_row disability_data_row_<%= disability.id %>">
                <td align="left" valign="top" nowrap="nowrap">
                    <div style="float:right">
                        <a id="open_disability_<%=case_id %>_<%= disability.id %>" class="open_disability" style="; cursor:pointer"><i style="font-size:15px;color:#06f; cursor:pointer;" class="glyphicon glyphicon-edit" title="Click to Edit Disability"></i></a>
                    </div>
                    <a id="open_disability_<%=case_id %>_<%= disability.id %>" class="open_disability white_text" style="; cursor:pointer"><%=disability.ailment.capitalize() %></a>
                </td>
                <td align="left" valign="top" nowrap="nowrap"><%= disability.duration %></td>
                <td align="left" valign="top" nowrap="nowrap"><%= disability.severity %></td>
                <td align="left" valign="top" nowrap="nowrap"><%= disability.duty %></td>
                <td align="left" valign="top" nowrap="nowrap"><%= disability.limits %></td>
                <td align="left" valign="top" nowrap="nowrap"><%= disability.treatment %></td>
                <td align="left" valign="top"><%=disability.description %></td>
            </tr>
            <% }); %>
            </tbody>
        </table>
    </div>
    <div id="surgery_holder" style="display:none">
    </div>
</div>
<div style="width:45%">
	<div id="claim_holder" style="display:none; background:url('img/glass_dark.png'); padding:5px; color:white; border:1px solid white; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    </div>
</div>
<div id="disability_listing_all_done"></div>
<script language="javascript">
$( "#disability_listing_all_done" ).trigger( "click" );
</script>