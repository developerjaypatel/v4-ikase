<div style="float:right; border:1px solid red; background:white; color:black; padding:2px; width:270px; font-size:1.2em; text-align:right">
    <table width="100%">
        <tr>
            <th align="left" valign="top">
                Penalty:
            </th>
            <td align="right" valign="top">
                $<%=info.penalties %>
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                Daily Interest:
            </th>
            <td align="right" valign="top">
                <%=info.daily_interest %>%
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                Last Service Date:
            </th>
            <td align="right" valign="top">
                <%=info.max_service_date %>
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                Days Service Date:
            </th>
            <td align="right" valign="top">
                <%=info.days_service_date %> days
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                POS 1st Date:
            </th>
            <td align="right" valign="top">
                <input type="text" id="post_first_date" name="post_first_date" style="width:85px" class="pos_date" />
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                POS 2nd Date:
            </th>
            <td align="right" valign="top">
                <input type="text" id="post_second_date" name="post_second_date" style="width:85px" class="pos_date" />
            </td>
        </tr>
         <tr>
            <th align="left" valign="top">
                Location Subpoenas:
            </th>
            <td align="right" valign="top">
                <input type="number" id="total_pos" name="total_pos" style="width:85px" value="<%=info.total_pos %>" />
            </td>
        </tr>
    </table>
</div>
<div style="border:1px solid red; background:white; color:black; padding:2px; width:300px; font-size:1.2em;">	
    <table width="100%">
        <tr>
            <td align="left" valign="top" colspan="2">
                <span style="font-weight:bold">MATRIX ORDER INVOICE SUMMARY</span>
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                Invoices:
            </th>
            <td align="right" valign="top">
                <%=info.invoice_count %>
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                First Invoice:
            </th>
            <td align="right" valign="top">
                <%=info.min_invoice_date %>
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                Last Invoice:
            </th>
            <td align="right" valign="top">
                <%=info.max_invoice_date %>
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                60-Day Date:
            </th>
            <td align="right" valign="top">
                <%=info.sixty_invoice_date %>
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                Due:
            </th>
            <td align="right" valign="top">
                $<%=info.sum_balance_due %>
            </td>
        </tr>
         <tr>
            <th align="left" valign="top">
                Review:
            </th>
            <td align="right" valign="top">
                <a href="https://www.matrixdocuments.com/dis/pws/quicks/reports/invoicepreprint_ledger_pos.php?id=<%=info.id %>" target="_blank" title="Click to review the Ledger+POS report">Ledger+Invoices+POS</a>
            </td>
        </tr>
        <tr>
        	<td align="left" valign="top" colspan="2">
            	<span style="font-style:italic">You must be logged-in to Matrix to review</span>
            </td>
        </tr>
    </table>
</div>
<div id="matrix_info_table_all_done"></div>
<script language="javascript">
$( "#matrix_info_table_all_done" ).trigger( "click" );
</script>