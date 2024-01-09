<div>
	<div style="float:right; font-style:italic">Required</div>
    <div style="float:right">
        <button class="btn btn-xs btn-success save_kinvoice" id="save_kinvoice_template" style="display:none">Save</button>
    </div>
	<label style="width:100px; display:inline-block" id="label_template_name">Template Name</label><input type="text" id="template_name" value="<%=template_name %>" class="required" style="width:235px" placeholder="Descriptive Name" />
</div>
<div>
	<div style="float:right; font-style:italic">Required</div>
    <div style="float:right">
        <button class="btn btn-xs btn-success save_kinvoice" id="save_kinvoice_rate" style="display:none">Save</button>
    </div>
	<label style="width:100px; display:inline-block" id="label_hourly_rate">Default Rate</label><input type="number" id="hourly_rate" value="<%=hourly_rate %>" class="required" style="width:155px" /> $ per hour
    <hr />
</div>
<div id="kinvoiceitems_holder" style="display:none">
    <div class="white_text" style="color:black; margin-top:10px">
        <div style="float:right">
            <button class="btn btn-xs" id="new_kinvoice_items_button">New Item</button>
        </div>
        <label style="width:100px; display:inline-block; font-size:1.1em; font-weight:bold" class="white_text">Invoice Items</label>
    </div>
    <div>
        <form  id="kinvoice_items_form">
            <input type="hidden" id="kinvoice_id" value="<%=kinvoice_id %>" />
            <input type="hidden" id="document_id" value="<%=document_id %>" />
            <div id="new_kinvoice_items_holder" style="display:none">
                <div style="float:right">
                    <button class="btn btn-xs btn-success" id="save_kinvoice_item" tabindex="5">Save</button>
                </div>
                <div style="float:right; margin-right:20px">
                    <input type="checkbox" id="new_kinvoice_exact" value="Y" />&nbsp;<label style="display:inline-block">Amount Only</label>
                    <br />
                    <input type="checkbox" id="new_kinvoice_cost" value="Y" />&nbsp;<label style="display:inline-block">Cost</label>
                </div>
                <label style="width:100px; display:inline-block">Item Name</label>&nbsp;<input type="text" id="new_kinvoice_item" placeholder="Item Name" class="required" autocomplete="off" tabindex="1" style="width:235px" />
                <div id="kinvoice_cost_holder" style="display:none">
                	<label style="width:100px; display:inline-block">Cost</label>&nbsp;<input type="number" id="new_kinvoice_rate" placeholder="Rate" class="required" autocomplete="off" tabindex="2" style="width:55px" />$&nbsp;per&nbsp;<input type="text" id="new_kinvoice_rateunit" placeholder="Unit" class="required" autocomplete="off" tabindex="3" style="width:148px" />
                    <div style="margin-left: 110px; font-style:italic">Ex: $0.15 per Mile</div>
                </div>
                <div>
                    <label style="width:100px; display:inline-block">Description</label><br />
        <textarea id="new_kinvoice_description" rows="2" style="width:357px" placeholder="Item Description" tabindex="4"></textarea>
                </div>
            </div>
            <%=html %>
        </form>
    </div>
</div>
<div id="kinvoice_items_listing_all_done"></div>
<script language="javascript">
$( "#kinvoice_items_listing_all_done" ).trigger( "click" );
</script>