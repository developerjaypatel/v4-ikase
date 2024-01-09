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
          <th style="font-size:1.5em" align="left" colspan="6">
          	<span style="text-align:center; font-size:1.1em; width:250px; border:0px solid green">
                Case Activity: <%= case_name %>
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
            
            <td style="border-bottom:1px solid black">
            	<div style="float:right">
                	<div>By: <%= activity.activity_user_nickname %></div>
                    <div>Hours: <%= activity.hours %></div>
                    <% if (customer_id!=1121) { %>
                    <div>Rate: $<%= activity.rate.toLocaleString("us", "currency") %>/hr</div>
                    <div>Amount: $<%= activity.billing_amount.toLocaleString("us", "currency") %></div>
                    <% } %>
                </div>
            	<% if(activity.name!="" && typeof activity.case_name!="undefined") { %>
                <div style="margin-bottom:10px">
                	<span style="border:1px solid blue; margin:2px; padding:2px;">
                		<%=activity.case_name %>
                    </span>
                </div>
                <% } %>    	
                <%= activity.activity.replaceAll("\r\n", "<br />") %>
          </td>
        </tr>
        <%	}
        }); %>
        </tbody>
    </table>
    <% if (customer_id!=1121) { %>
    <br/><br/>
    <table width="25%" border="1" cellspacing="0" cellpadding="3" align="left" style="margin-left:5%">
    <thead style="background:#EBEBEB; border-bottom:1px solid black" valign="top">
        <tr>
            <th style="border-bottom:1px solid black" align="left">
            	Billed By
            </th>
            <th style="border-bottom:1px solid black" align="left">
            	Hours
            </th>
            <th style="border-bottom:1px solid black" align="left">
            	Rate ($/hr)
            </th>
            <th style="border-bottom:1px solid black" align="left">
            	Fees ($)
            </th>
        </tr>
        </thead>
        <tbody valign="top">
        <% 
        _.each( user_names, function(user_name) {
        	  var user_hours = hours[user_name];
              var user_rate = userrates[user_name];
              var user_init = userinits[user_name];
              var user_total = user_hours * user_rate;
              user_hours = user_hours.toFixed(2);
              
              user_rate = user_rate.toLocaleString("us", "currency");
              user_total = user_total.toLocaleString("us", "currency");
              
              //alert(parseInt(user_rate));
              if (user_rate.indexOf(".") < 0) {
              	user_rate = user_rate + ".00";
              }
              //alert(user_total.indexOf("."));
              if (user_total.indexOf(".") < 0) {
              	user_total = user_total + ".00";
              }
        %>
        <tr>
        
            <td align="left" nowrap="nowrap"><%= user_name %><% if (user_init != undefined) { %>&nbsp;(<%= user_init %>)<% } %></td>
        
        
            <td align="right"><%= user_hours %></td>
        
            <td align="right"><%=user_rate %></td>
            <td align="right"><%= user_total %></td>
        </tr>
        <% }); 
        %>
        </tbody>
    </table>
    <% } %>    	
</div>