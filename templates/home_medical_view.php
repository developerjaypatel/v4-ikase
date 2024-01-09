<div class="homemedical">
<form id="homemedical_form" name="homemedical_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="homemedical" />
<input id="table_id" name="table_id" type="hidden" value="<%=homemedical_id %>" />
<input id="corporation_id" name="corporation_id" type="hidden" value="<%=corporation_id %>" />
<input id="case_id" name="case_id" class="homemedical" type="hidden" value="<%=case_id %>" />
Home Medical Section 
<table width="370px" border="0" style="margin-top:30px" align="left" cellpadding="3" cellspacing="0">
  <tr>
    <th width="40%" align="left" valign="top" id="recommended_by_label" scope="row">Recommended By:</th>
    <td width="60%" colspan="3"><input name="recommended_byInput" type="text" id="recommended_byInput" style="width:225px" class="modalInput homemedical input_class" value="<%=recommended_by %>" placeholder="Recommended By" /></td>
  </tr>
  <tr id="display_name_holder">
    <th align="left" valign="top" scope="row" id="provider_name_label">Provider Name:</th>
    <td colspan="3">
    	<input name="provider_nameInput" type="text" id="provider_nameInput" style="width:225px" class="modalInput homemedical input_class" autocomplete"off" value="<%=provider_name %>" placeholder="Provider Name" />
    </td>
    </tr>
    <tr class="provider_info" style="display:none">
    	<th>Address</th>
        <td>
        	<input id="full_addressInput" name="full_addressInput" type="text" style="width:225px" class="modalInput homemedical input_class" autocomplete"off" value="<%=full_address %>" placeholder="Provider Address" />
        </td>
    </tr>
    <tr class="provider_info" style="display:none">
    	<th>Phone</th>
        <td>
        	<input id="phoneInput" name="phoneInput" type="text" style="width:225px" class="modalInput homemedical input_class" autocomplete"off" value="<%=phone %>" placeholder="Provider Phone" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" />
        </td>
    </tr>
	<tr id="display_name_holder">
    <th align="left" valign="top" id="prescription_date_label" scope="row">Prescription Date:</th>
    <td><input name="prescription_dateInput" type="text" id="prescription_dateInput" style="width:225px" class="date_input modalInput homemedical input_class" value="<%=prescription_date %>" placeholder="Prescription Date" /></td>
    </tr>
	<tr>
    <th align="left" valign="top" scope="row" id="report_date_label" style="width:105px">Report Date:</th>
    <td align="left">
    	<input name="report_dateInput" type="text" id="report_dateInput" style="width:225px" class="date_input modalInput homemedical input_class" autocomplete"off" value="<%=report_date %>" placeholder="Report Date" />
    </td>
    </tr>
	<tr>
    <th align="left" valign="top" id="filling_fee_paid_date_label" scope="row">Filling Fee Paid Date:</th>
    <td><input name="filling_fee_paid_dateInput" type="text" id="filling_fee_paid_dateInput" style="width:225px" class="date_input modalInput homemedical input_class" value="<%=filling_fee_paid_date %>" placeholder="Filling Fee Paid Date" /></td>
    </tr>
	<tr>
    <th align="left" valign="top" scope="row" id="retainer_date_label">Retainer Date:</th>
    <td align="left">
    	<input name="retainer_dateInput" type="text" id="retainer_dateInput" style="width:225px" class="date_input modalInput homemedical input_class" autocomplete"off" value="<%=retainer_date %>" placeholder="Retainer Date" />
    </td>
    </tr>
	<tr>
    <th align="left" valign="top" id="lien_filled_date_label" scope="row">Lien Filled Date:</th>
    <td><input name="lien_filled_dateInput" type="text" id="lien_filled_dateInput" style="width:225px" class="date_input modalInput homemedical input_class" value="<%=lien_filled_date %>" placeholder="Lien Filled Date" /></td>
	
    </tr>
	<tr>
    
    <th align="left" valign="top" scope="row" id="reviewed_date_label">Reviewed Date:</th>
    <td align="left">
    	<input name="reviewed_dateInput" type="text" id="reviewed_dateInput" style="width:225px" class="date_input modalInput homemedical input_class" autocomplete"off" value="<%=reviewed_date %>" placeholder="Reviewed Date" />
    </td>
    </tr>
</table>
</form>
</div>
<div id="homemedical_all_done"></div>
<script language="javascript">
$( "#homemedical_all_done" ).trigger( "click" );
</script>