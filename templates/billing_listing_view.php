<?php
require_once('../shared/legacy_session.php');
session_write_close();

//special cases
$blnGlauber = ($_SESSION["user_customer_id"]=='1049');
?>
<div class="billing">
	<div id="threadimage_preview_panel" style="position:absolute; width:20px; display:none; z-index:2; background:white;" class="attach_preview_panel"></div>
	<div class="glass_header">
        <div style="float:right;" class="white_text">
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="user_id" name="user_id" type="hidden" value="" />
            <!--<% //if () { %>
            <a href="report.php#billing/<%=case_id %>" target="_blank" title='Click to print billing' style='cursor:pointer; display:' class="white_text">Print billing</a>
            <% //} else { %>
            <a href="report.php#billings" target="_blank" title='Click to print billing' style='cursor:pointer; display:' class="white_text">Print billing</a>
            <% //} %>-->
			<div class="btn-group">            	
            	<label for="billings_searchList" id="label_search_billing" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search billing</label>
            	
				<input id="billings_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'billing_listing', 'billing')" style="width:190px; height:30px">
				<a id="billings_clear_search" style="position: absolute;
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
        
        <span style="font-size:1.2em; color:#FFFFFF">Billing Hours
            &nbsp;(<%=billings.length %>)&nbsp;&nbsp;&nbsp;&nbsp;
        <?php if ($blnGlauber) { ?>
        <a class="white_text restore_archives" style="cursor:pointer; background:red; color:white; display:none" title="This case was restored from Archives.  The billings have not yet been processed by our bot.&#13;Please click this link to initiate the Archive Restore Process.  You will only have to do this once.&#13;&#13;This process may take a few minutes, because some older cases have up to 5000 entries.">Restore from Archives</a>&nbsp;<span id="restore_archives_count" style="display:none"></span>
        <?php } ?>
        <select name="mass_change" id="mass_change" style="width:150px; display:none">
          <option value="" selected="selected">Choose Action</option>
          <option value="print">Print</option>
        </select>
    </div>
    <table id="billing_listing" class="tablesorter billing_listing" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
            <th>
            	Date
            </th>
            <th>
            	Description
            </th>
            <th>
            	Hours
            </th>
            <th>
            	Rate
            </th>
            <th>
            	Tax
            </th>
            <th>
            	Total
            </th>
        </tr>
        </thead>
        <tbody>
        <% _.each( billings, function(billing) {
        	var billing_total = Number(billing.hours);
            billing_total = ((billing_total * 10) * (billing.user_rate * 10)) / 100;
            billing_total = billing_total.toFixed(2);
            billing_total = '$' + billing_total;
            /*
            var user_id = billing.activity_user_id;
            var user_rate = "";
            var user_tax = "";
            
            var user_info = new User({user_id: user_id});
            user_info.fetch({
                success: function (user_info) {
                    user_rate = user_info.rate;
            		user_tax = user_info.rate;
                }
            });	
            */
            
        %>
        
        <tr class="billing_data_row">
            <td><%= moment(billing.activity_date).format("MM/DD/YY h:mmA") %></td>
            <td><%= billing.activity %></td>
            <td><%= billing.hours %></td>
            <td><%= billing.user_rate %></td>
            <td><%= billing.user_tax %></td>
            <td><%= billing_total %></td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>
