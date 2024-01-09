<?php require_once('../shared/legacy_session.php');
session_write_close();
?>
<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-5">
        	<div id="checkrequest_holder" style="width:100%"></div>
            <div id="disbursement_holder" style="width:100%; border-top:1px solid white; padding-top:15px"></div>
            <div id="fees_holder" style="width:100%; border-top:1px solid white; padding-top:15px"></div>
            <div id="receipt_holder" style="width:100%; border-top:1px solid white; padding-top:15px"></div>
            <div id="deduction_holder" style="width:100%; border-top:1px solid white; padding-top:15px"></div>
        </div>
        <div class="col-md-7">
        	<div id="losses_holder" style="width:100%; padding-bottom:15px"></div>
            <div id="accounts_holder" style="width:100%; padding-bottom:15px; display:"></div>
            <div id="kinvoices_holder" style="width:100%; border-top:1px solid white; padding-top:15px"></div>
        </div>
    </div>
</div>
<div style="height:15px">&nbsp;</div>
<div id="billing_hours_table"></div>
<div id="dashboard_accounting_all_done"></div>
<script language="javascript">
$( "#dashboard_accounting_all_done" ).trigger( "click" );
</script>
