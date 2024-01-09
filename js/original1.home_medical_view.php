<div class="home_medical" style="margin-left:10px">
<form id="home_medical_form" name="home_medical_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="home_medical" />
<input id="table_id" name="table_id" type="hidden" value="<%=home_medical_id %>" />
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
    <th width="18%" align="left" valign="top" id="recommended_by_label" scope="row">Recommended By:</th>
    <td width="90%"><input name="recommended_byInput" type="text" id="recommended_byInput" style="width:433px" class="modalInput home_medical input_class" value="" placeholder="Recommended By" /></td>
  </tr>
  <tr id="display_name_holder">
    <th align="left" valign="top" scope="row" id="provider_name_label">Provider Name:</th>
    <td>
    	<input name="provider_nameInput" type="text" id="provider_nameInput" style="width:433px" class="modalInput home_medical input_class" autocomplete"off" value="" placeholder="Enter Form Display Name (ex: DOR)" />
    </td>
    </tr>
	<tr>
    <th align="left" valign="top" id="prescription_date_label" scope="row">Prescription Date:</th>
    <td><input name="prescription_dateInput" type="text" id="prescription_dateInput" style="width:200px" class="modalInput home_medical input_class" value="" placeholder="Recommended By" /></td>
	
    <th align="left" valign="top" scope="row" id="report_date_label">Report Date:</th>
    <td>
    	<input name="report_dateInput" type="text" id="report_dateInput" style="width:200px" class="modalInput home_medical input_class" autocomplete"off" value="" placeholder="Enter Form Display Name (ex: DOR)" />
    </td>
    </tr>
	<tr>
    <th align="left" valign="top" id="filling_fee_paid_date_label" scope="row">Filling Fee Paid Date:</th>
    <td><input name="filling_fee_paid_dateInput" type="text" id="filling_fee_paid_dateInput" style="width:200px" class="modalInput home_medical input_class" value="" placeholder="Recommended By" /></td>
	
    <th align="left" valign="top" scope="row" id="retainer_date_label">Retainer Date:</th>
    <td>
    	<input name="retainer_dateInput" type="text" id="retainer_dateInput" style="width:200px" class="modalInput home_medical input_class" autocomplete"off" value="" placeholder="Enter Form Display Name (ex: DOR)" />
    </td>
    </tr>
	<tr>
    <th align="left" valign="top" id="lien_filled_date_label" scope="row">Lien Filled Date:</th>
    <td><input name="lien_filled_dateInput" type="text" id="lien_filled_dateInput" style="width:200px" class="modalInput home_medical input_class" value="" placeholder="Recommended By" /></td>
	
    <th align="left" valign="top" scope="row" id="reviewed_date_label">Reviewed Date:</th>
    <td>
    	<input name="reviewed_dateInput" type="text" id="reviewed_dateInput" style="width:200px" class="modalInput home_medical input_class" autocomplete"off" value="" placeholder="Enter Form Display Name (ex: DOR)" />
    </td>
    </tr>
  <tr>
    <td colspan="2">
      <input type='hidden' id='send_document_id' name='send_document_id' value="" />
      <div id="home_medical_attachments" style="width:90%; border:#FFFFFF 0px solid; display:none"></div>
      </td>
  </tr>
</table>
</form>
</div>