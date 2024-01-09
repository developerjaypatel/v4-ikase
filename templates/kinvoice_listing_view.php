<%
var display = "";
invoices_count = "<span class='white_text'>(" + kinvoices.length + ")</span>";
if (kinvoices.length==0) {
    display = "display:none";
}
%>
<div class="kinvoice" style="<%=div_witdh %>">
	<% if (blnDocumentInvoices) { %>
    <span style="font-size:1.2em; font-weight:bold"><%=page_title %></span>
    <% } else { %>
    <div id="kinvoice_listing_header" class="glass_header">
    	<div style="float:right;">
        	<input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
            
			<div class="btn-group">
            	
            	 <label for="kinvoices_searchList" id="label_search_kinvoice" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search Invoices</label>
            	
				<input id="kinvoices_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'kinvoice_listing', 'kinvoice')">
				<a id="kinvoices_clear_search" style="position: absolute;
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
        <span style="font-size:1.2em; color:#FFFFFF"><%=page_title %></span>&nbsp;<%= invoices_count %>&nbsp;&nbsp;
        <button id="new_kinvoice" class="btn btn-sm btn-primary" title="Click to create a new invoice" style="margin-top:-5px">New Invoice</button>
        &nbsp;
        <span style="display:none; color:white" id="kase_invoiced_holder"></span>
        &nbsp;
        <span style="display:none; color:white" id="kase_billables_holder">
        |&nbsp;&nbsp;<button id="kase_billables" class="btn btn-sm review_billable">Review&nbsp;Case&nbsp;Billables&nbsp;(<span id="kase_billables_amount"></span>)</button>
        </span>
         <!--<button id="print_kinvoices_<%=page_title %>" class="btn btn-sm btn-info btn_<%=page_title %>" title="Click to Print <%=page_title %>" style="margin-top:-5px">Print</button>-->
    </div>
    <% } %>
    <div id="kinvoice_preview_panel" style="position:absolute; width:35vw; display:none;"></div>
    <table id="kinvoice_listing" class="tablesorter kinvoice_listing" border="0" cellpadding="0" cellspacing="1" style="<%=display %>">
        <thead>
        <tr>
        	<th align="left" width="3%">&nbsp;
            	Inv&nbsp;#
            </th>
            <% if (!blnDocumentInvoices) { %>
        	<th align="left">&nbsp;
            		
            </th>
            <% if (blnAllInvoices) { %>
            <th align="left" class="kinvoice_case_cell">
            	Invoice
            </th>
            <th align="left" class="kinvoice_case_cell">
            	Case
            </th>
            <% } %>
            <th align="left">
            	Invoiced
            </th>
            <% } %>
            <th align="left">
            	Date
            </th>
            <th align="left">
            	Rate
            </th>
            <th align="left">
            	By
            </th>
            <th align="left" width="1%">
            	Due
            </th>
            <th align="left" width="1%">
            	Paid
            </th>
            <th align="left" width="1%">
            	Balance
            </th>
            <th align="left" width="1%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <% var blnFullyPaid = false;
        var blnSent = false;
        _.each( kinvoices, function(kinvoice) {
        	blnFullyPaid = false;
        	if (Number(kinvoice.total) - Number(kinvoice.payments) <= 0) {
	            blnFullyPaid = true;
            }
            blnSent = false;
        	if (kinvoice.sent_status == 'sent' || kinvoice.notification_date!="0000-00-00 00:00:00") {
            	blnSent = true;
            }
            var colspan = 6;
            if (blnAllInvoices) {
            	colspan = 7;
            }
            var balance = Number(kinvoice.total) - Number(kinvoice.payments);
            var balance_color = "background: red";
            if (blnFullyPaid) {
	            balance_color = "background: lime; color: black";
            }
            kinvoice.hourly_rate_value = kinvoice.hourly_rate;
            
            if (kinvoice.hourly_rate==-1) {
            	kinvoice.hourly_rate = "<span title='Not Applicable'>N/A</span>";
            } else {
            	kinvoice.hourly_rate += "&nbsp;$/hr";
            }
            var arrFile = kinvoice.document_filename.split("/");
            var file_path = arrFile[arrFile.length - 1];
            file_path = file_path.replace("kase_bill__", "");

            if (kinvoice.case_name == "") {
                kinvoice.case_name = kinvoice.case_number;
            }
            if (kinvoice.case_name == "") {
            	kinvoice.case_number = kinvoice.file_number;
                kinvoice.case_name = kinvoice.file_number;
            }
            var kinvoice_dates = "";
            if (kinvoice.notification_date=="0000-00-00 00:00:00") {
                var sent_dates = kinvoice.sent_dates;
                var arrSDates = sent_dates.split(",");
                
                for (var i = 0; i < arrSDates.length; i++) {
                    var sent_date = arrSDates[i];
                    if (sent_date!="") {
                        arrSDates[i] = moment(sent_date).format("MM/DD/YYYY");
                    }
                }
                if (arrSDates.length > 0) {
                	kinvoice_dates = "\r\n" + arrSDates.join("\r\n");
                }
            } else {
	            kinvoice_dates = moment(kinvoice.notification_date).format("MM/DD/YYYY");
            }
        %>
        <tr class="invoice_data_row_<%=kinvoice.kinvoice_id %>">
        	<td valign="top" align="left" nowrap="nowrap">
            	<input type="hidden" id="kinvoice_id_<%= kinvoice.kinvoice_id %>" value="<%= kinvoice.kinvoice_id %>" />
                <input type="hidden" id="document_id_<%= kinvoice.kinvoice_id %>" value="<%= kinvoice.document_id %>" />
                <input type="hidden" id="case_id_<%= kinvoice.kinvoice_id %>" value="<%= kinvoice.case_id %>" />
                <input type="hidden" id="case_number_<%= kinvoice.kinvoice_id %>" value="<%= kinvoice.case_number %>" />
                <input type="hidden" id="case_name_<%= kinvoice.kinvoice_id %>" value="<%= kinvoice.case_name %>" />
                <input type="hidden" id="balance_<%= kinvoice.kinvoice_id %>" value="<%= balance %>" />
                <input type="hidden" id="hourly_rate_<%= kinvoice.kinvoice_id %>" value="<%= kinvoice.hourly_rate_value %>" />
            	<span id="kinvoice_number_<%= kinvoice.kinvoice_id %>"><%= kinvoice.kinvoice_number %></span>
                <% if (blnDocumentInvoices) {
                	if (blnFullyPaid) { %>
                	<span style="background:lime; padding:2px; color:black">&#10003;</span>
                <% 	}
                } %>
                &nbsp;
                <% if (!blnFullyPaid) { //this will change to blnInvoiceSent
                	if (kinvoice.template_name == "Activity Bill") { %>
                    <a href="#billing/<%= kinvoice.case_id %>/<%= kinvoice.kinvoice_id %>" title="Click to edit invoice" id="editinvoice_<%= kinvoice.kinvoice_id %>">
                            <i style="font-size:15px; color:#a9bafd; cursor:pointer" class="glyphicon glyphicon-edit"></i>
                    </a>
                <% } else { %>
                    <a title="Click to edit invoice" class="edit_invoice_full" id="editinvoice_<%= kinvoice.kinvoice_id %>" style="cursor:pointer;">
                            <i style="font-size:15px; color:#a9bafd; cursor:pointer" class="glyphicon glyphicon-edit"></i>
                    </a>
                    
                <% }
                } %>
                &nbsp;
                <a href="api/preview_invoice.php?file=<%=file_path %>" class="invoice_number" id="invoice_number_<%= kinvoice.kinvoice_id %>" style="cursor:pointer; color:white; text-decoration:underline" title="Click to view Invoice" target="_blank"><i class="glyphicon glyphicon-save" style="color:white"></i></a>
            </td>
            <% if (!blnDocumentInvoices) { %>
            <td valign="top" align="left" nowrap="nowrap">
            	<% if (kinvoice.kinvoice_type=="I") { %>
                    <a title="Click to send invoice by mail (generate an envelope for mailing the invoice)" class="compose_pdf_envelope envelope_<%= kinvoice.kinvoice_id %>_<%= kinvoice.case_id %>" id="pdfenvelope_<%=kinvoice.company_type %>_<%= kinvoice.corporation_id %>" style="cursor:pointer">
                        <i class="glyphicon glyphicon-envelope" style="color:yellow"></i>
                    </a>
                    <span id="feedback_pdf_<%=kinvoice.company_type %>_<%= kinvoice.corporation_id %>"></span>
                    &nbsp;
                    <a title="Click to send invoice by email" class="compose_invoice" id="invoice_<%= kinvoice.case_id %>_<%= kinvoice.kinvoice_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-send" style="color:#00FFFF"></i></a>
                    &nbsp;
                <% } %>
                <% if (kinvoice.kinvoice_type=="P") { %>
                <span id="prebill_<%= kinvoice.kinvoice_id %>">PRE-BILL</span>
                <% } %>
                <!--<%=Number(kinvoice.total) - Number(kinvoice.payments) %>-->
                <% if (blnFullyPaid) { %>
                	<div style="float:right">
                    	<button id="review_transactions_<%= kinvoice.kinvoice_id %>" class="btn btn-xs review_transactions" title="Click to show Payments against Invoices" style="margin-top:-5px">Review Payments</button>  
                    </div>
                	<span style="background:lime; padding:2px; color:black">Paid&nbsp;&#10003;</span>
                <% } else { %>
                	<% if (kinvoice.kinvoice_type=="I") { %>
		                <button title="Click to add payment to invoice" class="btn btn-primary btn-xs pay_invoice_full" id="payment_<%= kinvoice.kinvoice_id %>">Add Payment</button>
                        <% if (kinvoice.trust_account_id!="") { %>
                        |&nbsp;<button title="Click to confirm transfer from Trust" class="btn btn-success btn-xs transfer_invoice" id="transfer_<%= kinvoice.kinvoice_id %>">Transfer From Trust</button>
                    	<% } %>    
                    <% } else { %>
                    	<div style="float:right; margin-right:10px">
                        	<button title="Click to change from Pre-Bill to Invoice" class="btn btn-success btn-xs change_invoice" id="change_<%= kinvoice.kinvoice_id %>">Change to Invoice</button> 
                        </div>
                    <% } %>
                <% } %>
                <% if (blnSent && !blnFullyPaid) { %>
                <span style="background:lime; padding:2px; color:black" title='Sent on <%=kinvoice_dates %>'>Sent&nbsp;&#10003;</span>
                <% } %>
            </td>
            <% if (blnAllInvoices) { %>
            <td valign="top" align="left">
            	<%= kinvoice.template_name %>
            </td>
            <td valign="top" align="left">
            	<div style="float:right; padding-right:10px">
                	<a href="#payments/<%= kinvoice.case_id %>" style="font-size:0.8em; text-decoration:underline" class="white_text">Invoices</a>
                </div>
                <a href="#kase/<%= kinvoice.case_id %>" class="white_text">
                	<%= kinvoice.case_name %>
                </a>
            </td>
            <% } %>
            <td valign="top" align="left" nowrap="nowrap">
                <span id="corporation_<%=kinvoice.kinvoice_id %>"><%=kinvoice.company_name %></span>
                <input type="hidden" id="corporationid_<%=kinvoice.kinvoice_id %>" value="<%=kinvoice.corporation_id %>" />
                <input type="hidden" id="corporationtype_<%=kinvoice.kinvoice_id %>" value="<%=kinvoice.company_type %>" />
            </td>
            <% } %>
            <td valign="top" align="left">
            	<%= moment(kinvoice.kinvoice_date).format("MM/DD/YY") %></a>
            </td>
            <td valign="top" align="left">
            	<%= kinvoice.hourly_rate %>
            </td>
            <td valign="top" align="left">
            	<%= kinvoice.assigned_nickname.toUpperCase() %>
            </td>
            <td valign="top" align="right">
            	$<span id="kinvoicetotal_<%=kinvoice.kinvoice_id %>"><%= numberWithCommas(Number(kinvoice.total).toFixed(2)) %></span>
            </td>
            <td valign="top" align="right">
            	$<span id="kinvoicepayments_<%=kinvoice.kinvoice_id %>"><%= numberWithCommas(Number(kinvoice.payments).toFixed(2)) %></span>
            </td>
            <td valign="top" align="right">
            	$<span id="kinvoicebalance_<%=kinvoice.kinvoice_id %>" style="<%=balance_color %>"><%= numberWithCommas(balance.toFixed(2)) %></span>
            </td>
            <td valign="top" align="right">
            	<% if (!blnFullyPaid) { %>
            	<a title="Click to delete invoice" class="delete_invoice" id="deleteinvoice_<%= kinvoice.kinvoice_id %>" style="cursor:pointer;">
                    <i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash"></i>
                </a>
                <% } else { %>
                &nbsp;
                <% } %>
            </td>
        </tr>
        <tr class="payments_row_holder invoice_data_row_<%=kinvoice.kinvoice_id %>" id="payments_row_<%=kinvoice.kinvoice_id %>" style="display:none; background:royalblue">
        	<td align="left" valign="top" colspan="4" id="payments_cell_<%=kinvoice.kinvoice_id %>" style="padding:0px">&nbsp;</td>
            <td colspan="<%=colspan %>">&nbsp;</td>
        </tr>
        <% }); %>
        </tbody>
    </table>
    <% if (!blnDocumentInvoices) { %>
    <div style="height:15px">&nbsp;</div>
    <% } %>
</div>
<div id="kinvoice_listing_view_done"></div>
<script language="javascript">
$( "#kinvoice_listing_view_done" ).trigger( "click" );
</script>