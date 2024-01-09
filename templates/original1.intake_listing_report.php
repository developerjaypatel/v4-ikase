<div style="border:#000000; width:99%" align="center">

	<table border="0" cellpadding="2" cellspacing="0" style="width:80%; margin-bottom:20px" align="center">  		
        <thead>
        <tr>
            <td width="77"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
            <td align="center">
                <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">Intakes Report (<%=kase_collection.length %>)</span>
            </td>
            <td align="left"><span style="float:right"><em>As of <?php echo date("m/d/y g:iA"); ?></em></span></td>
          </tr>
        </thead>
    </table>
    <table id="kase_listing" class="tablesorter kase_listing" border="0" cellpadding="0" cellspacing="1" width="80%" style="-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; background:none" align="center">
        <thead>
        <tr>
            <th align="left" width="100">
                Kase
            </th>
            <th align="left" width="100">Intake Date</th>
            <th align="left" width="100">Injuy Date</th>
            <th align="left" width="100">
                Type
            </th>
            <th align="left" width="30%">
                Injuries
            </th>
            <th align="left">
                Status
            </th>
            <th align="left">
                Language
            </th>
            <th align="left" width="30%">
                Occupation
            </th>
            <th align="left">&nbsp;</th>
        </tr>
        </thead>
        <tbody class="listing_item">
        <% 
       var kaseCounter = 0;    
       _.each( kase_collection, function(kase) {
        %>
        <tr>
            <td style="border-bottom:1px solid black; padding-bottom:10px"> 
                <input type="checkbox" id="select_kase_<%=kase.case_id %>" class="select_kase" style="display:none" />
                <%=kase.file_number %>
               
            </td>
            <td style="border-bottom:1px solid black; padding-bottom:10px"><%=kase.case_date %></td>
            <td style="border-bottom:1px solid black; padding-bottom:10px"><%=kase.doi %></td>
            <td style="border-bottom:1px solid black; padding-bottom:10px"><span class="listing_item"><%=kase.case_type %></span></td>
            <td style="border-bottom:1px solid black; padding-bottom:10px"><span class="listing_item"><%=kase.explanation %></span></td>
            <td style="border-bottom:1px solid black; padding-bottom:10px" nowrap="nowrap">
                <span class="listing_item"><%=kase.case_status.capitalize() %></span>
            </td>
            <td style="border-bottom:1px solid black; padding-bottom:10px"><span class="listing_item"><%=kase.language %></span></td>
            <td style="border-bottom:1px solid black; padding-bottom:10px"><span class="listing_item attorney_name"><%=kase.occupation %></span></td>
        </tr>
        <% if (kase.special_instructions!="" && kase.special_instructions!="undefined") { %>
        <tr>
            <td style="border-bottom:1px solid black; padding-bottom:10px" colspan="9">
            <span style="background:white; color:red; font-weight:bold">SPECIAL INSTRUCTIONS:</span> 
            <%=kase.special_instructions %>
            </td>
        </tr>
        <% }
        }); %>
    </tbody>
    </table>
</div>    