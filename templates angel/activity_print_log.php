<div style="border:#000000; width:99%" align="center">
	<table border="0" cellpadding="2" cellspacing="0" style="width:90%" align="center">  		
        <thead>
        <tr>
            <td width="33%" valign="top" colspan="2">
            	<img src="img/ikase_logo_login.png" height="32" width="77"><br/>
            	<% if (invoice_id != "") { %>
				<br/><br/>
                <div style="display:none" id="billto_info_holder">
                    <div style="margin-top:0px;"><strong>Bill To:</strong></div><div style="display:inline-block" id="bill_to_info"></div>
                </div>
                <% } %>
          </td>
            <td align="left" colspan="2" valign="top">
            	<span style="font-weight:bold; font-size:1.5em;">
                	<%= customer_name %>
                </span>
                
            </td>
            <td align="left" colspan="2" valign="top">
            	<% if (invoice_id != "") { %>
                <div style="float:right">
                	<strong style="font-size:1.2em">Invoice #</strong>
                         <%=case_id %>-<%= invoice_number %>
                    <br/>
                    <span style="font-size:0.8em; font-style:italic">As of <strong><?php echo date("m/d/y g:iA"); ?></strong></span>
                    <br/>
                    <br/>
                    
                    <table width="100%" border="1" cellspacing="0" cellpadding="3">
                      <tr>
                        <td>Date</td>
                        <td align="right" style="font-weight:bold"><%= invoice_date %></td>
                      </tr>
                      <tr>
                        <td>Invoiced</td>
                        <td align="right">$<%= total_invoice_amount %></td>
                      </tr>
                      <tr>
                        <td>Paid</td>
                        <td align="right">$0.00</td>
                      </tr>
                      <tr>
                        <td>Due:</td>
                        <td align="right" style="font-weight:bold">$<%= total_invoice_amount %></td>
                      </tr>
                    </table>

                </div>
                <% } %>
            </td>
          </tr>
        <tr>
          <th align="left" colspan="6">
          	<div style="float:right; font-size:0.7em; font-weight:normal">
            	As of <?php echo date("m/d/Y h:iA"); ?>
            </div>
          	<span style="text-align:center; width:250px; border:0px solid green">
                Activity Log: <%= case_name %>
            </span>
          </th>
        </tr>
        <tr>
            <th style="font-size:1.5em" align="left" colspan="6">
            	<% if (case_id=="") { %>
                Activity: <%=activity_count %> entries 
                <% if (activity_user_name!="") { %>
                for <%=activity_user_name %> (<%=activity_nickname %>)
                <% } %>
                <div>
                    From <!--<%=activity_start %> through <%=activity_end %>-->
                    <input id="start_dateInput" class="range_dates" value="<%= moment(activity_start).format("MM/DD/YYYY") %>" style="width:80px" placeholder="From Date" /> through <input id="end_dateInput" class="range_dates" value="<%= moment(activity_end).format("MM/DD/YYYY") %>" style="width:80px" placeholder="Through Date" />
                    <input type="hidden" id="activity_user_id" value="<%=activity_user_id %>" />
                </div>
                <% } %>
            </th>
        </tr>
        </thead>
    </table>
    <table id="activity_listing" class="tablesorter activity_listing" border="1" cellpadding="5" cellspacing="0" style="width:90%" align="center">
        <thead style="background:#EBEBEB; border-bottom:1px solid black" valign="top">
        <tr>
            <th style="border-bottom:1px solid black" align="left">
            	Date
            </th>
            <th style="border-bottom:1px solid black" align="left">Hours</th>
            <!--
            <th style="border-bottom:1px solid black" align="left">Who</th>
            <th style="border-bottom:1px solid black" align="left">Rate</th>
            <th style="border-bottom:1px solid black" align="left">Amount</th>
            -->
            <th style="border-bottom:1px solid black" align="left">
            	<div style="float:right">
                Total Hours: <%=total_hours.toFixed(2) %>
                </div>
            	Activity/Description
            </th>
        </tr>
        </thead>
        <tbody valign="top">
        <%  var arrIDs = [];
        _.each( activities, function(activity) {
        	  if (arrIDs.indexOf(activity.activity_id) < 0) {
            	arrIDs.push(activity.activity_id);
                if (invoice_id != undefined) {
                    if (activity.rate == "") { 
                    activity.rate = "0.00";
                 } 
                 if (activity.rate.indexOf(".") < 0) {
              		activity.rate = activity.rate + ".00";
                 }
              }
              
              if (invoice_id != undefined) {
          		if (activity.rate == "") { 
                    activity.rate = "0.00";
                 } 
                 
                if (activity.rate.indexOf(".") < 0) {
              		activity.rate = activity.rate + ".00";
              	}
             }
             if (activity.billing_amount.indexOf(".") < 0) {
              	activity.billing_amount = activity.billing_amount + ".00";
             }
             
        %>
        <tr class="activity_data_row">
            <td style="border-bottom:1px solid black"><%= moment(activity.activity_date).format("MM/DD/YY") %></td>
            
            <td style="border-bottom:1px solid black"><%= activity.hours %></td>
            <!--
            <td style="border-bottom:1px solid black"><%= activity.activity_user_nickname %></td>
            <td style="border-bottom:1px solid black"><%= activity.rate.toLocaleString("us", "currency") %></td>
            <td style="border-bottom:1px solid black"><%= activity.billing_amount.toLocaleString("us", "currency") %></td>
            -->
            <td style="border-bottom:1px solid black">
            	<% if(activity.name!="" && typeof activity.case_name!="undefined") { %>
                <div style="margin-bottom:10px">
                	<span style="border:1px solid blue; margin:2px; padding:2px;">
                		<%=activity.case_name %>
                    </span>
                </div>
                <% } %>    	
                <%= activity.short_activity %>
          </td>
        </tr>
        <%	}
        }); %>
        </tbody>
    </table> 	
</div>