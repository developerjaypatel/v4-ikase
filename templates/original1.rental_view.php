<div class="rental_view rental <%= accident_partie %>" style="display:">
    <form id="rental_form" parsley-validate>
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="representing" name="representing" type="hidden" value="<%=accident_partie %>" />
        <table width="100%" cellpadding="2" cellspacing="0">
            <tr>
                <th align="left" valign="top" nowrap="nowrap">Car Rented</th>
                <td align="left" valign="top">
                    <input value="Y" type="checkbox" name="rentedInput" id="rentedInput" class="kase rental rental_view"  />
                    <span id="rentedSpan" class="kase rental_view span_class form_span_vert hidden" style="margin-top:-28px; margin-left:90px"></span>
                </td>
                <th align="right" valign="top" style="text-align:right">Rental Completed</th>
                <td align="left" valign="top">
                	<input value="Y" type="checkbox" name="completedInput" id="completedInput" class="kase rental rental_view"  />
                	<span id="completedSpan" class="kase rental_view span_class form_span_vert hidden" style="margin-top:-28px; margin-left:90px"></span>
                </td>
            </tr>
            <tr>
                <th align="left" valign="top">Agency</th>
                <td colspan="3" align="left" valign="top">
                    <input value="" name="agencyInput" id="agencyInput" class="kase input_class rental rental_view" style="width:355px "  />
                    <span id="agencySpan" class="kase rental_view span_class form_span_vert hidden" style="margin-top:-28px; margin-left:90px"></span>
                </td>
            </tr>
            <tr>
                <th align="left" valign="top" nowrap="nowrap">Paid By</th>
                <td colspan="3" align="left" valign="top">
                    <input value="" name="paid_byInput" id="paid_byInput" class="kase rental_view input_class" placeholder="Paid by" style="width:355px" parsley-error-message="" />
                    <span id="paid_bySpan" class="kase rental_view span_class form_span_vert hidden" style="margin-top:-26px; margin-left:90px"></span>
                </td>
            </tr>
            <tr>
                <th align="left" valign="top" nowrap="nowrap">Billed</th>
                <td colspan="3" align="left" valign="top">
                    <input type="number" step="0.01" min="0" value="" name="amount_billedInput" id="amount_billedInput" class="kase input_class rental_view rental_amount" style="width:120px" placeholder="$ Amount" />
                	<span id="amount_billedSpan" class="kase rental_view span_class form_span_vert hidden" style="overflow-x: hidden;overflow-y : auto; height:100px; margin-top:-26px; margin-left:90px"></span>
                </td>
            </tr>
            <tr>
                <th align="left" valign="top" nowrap="nowrap">Paid</th>
                <td colspan="3" align="left" valign="top">
                    <input type="number" step="0.01" min="0" value="" name="rental_paymentInput" id="rental_paymentInput" class="kase input_class rental rental_view rental_amount" style="width:119px" placeholder="$ Payment"  />
                	<span id="rental_paymentSpan" class="kase rental_view span_class form_span_vert hidden" style="margin-top:-28px; margin-left:90px"></span>
                </td>
            </tr>
            <tr>
                <th align="left" valign="top" nowrap="nowrap">Balance</th>
                <td colspan="3" align="left" valign="top">
                    <input type="number" step="0.01" min="0" value="" name="rental_balanceInput" id="rental_balanceInput" class="kase input_class hidden rental rental_view" style="width:119px" placeholder="$ Balance"  />
                	<span id="rental_balanceSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px;"></span>
                </td>
            </tr>
        </table>
    </form>
</div>
<div class="rental_view" id="rental_all_done"></div>
<script language="javascript">
$( "#rental_all_done" ).trigger( "click" );

function clickIt(event) {
	//event.preventDefault();	
	//var element = event.currentTarget;
	//console.log(element.id);
	$("#totaledInput" ).val( "Y" );
	//console.log($("#totaledInput").val());
	//return;
}
</script>