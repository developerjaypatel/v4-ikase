<?php $form_name = "account_selection"; ?>
<div id="accounts_header" class="glass_header" style="height:49px; margin-bottom:10px">
	<div style="float:right">
    	<div style="display:inline-block">
    		<button id="new_trust_account" class="btn btn-sm btn-primary new_trust_account" title="Click to create a new Trust Account" style="margin-top:-5px">New Trust Account</button> 
        </div>
        <div style="display:inline-block">
        	<button id="new_operating_account" class="btn btn-sm btn-primary new_operating_account" title="Click to create a new Trust Cost Account" style="margin-top:-5px">New Cost Account</button> 
        </div>
    </div>
	<span style="font-size:1.2em; font-weight:bold; color:#FFFFFF">Accounts</span>
</div>

<div style="background:url(img/glass_card_dark_long_2.png) left top repeat-y; padding:5px; width:100%;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
        <div class="account_selection" id="account_selection_panel">
            <form id="account_selection_form" parsley-validate>
           <!-- <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">  -->
                <div style="margin-bottom:10px; width:100%">
                    <div id="trust_accountGrid" class="gridster_border account_selection" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:49%; display:inline-block; margin-right:10px; vertical-align:top">
                    	<div style="float:right; display:none" id="trust_balance_holder" class="white_text"></div>
                        <div style="display:inline-block; width:85px; vertical-align:top">
                            <h6><div class="form_label_vert" style="margin-top:10px; color:white; font-size:1.4em">Trust</div></h6>
                        </div>
                        <div style="display:inline-block">
                            <select name="trust_accountInput"  id="trust_accountInput" class="account_select modal_input"></select>
                            <span id="trust_accountSpan" class="account_selection <?php echo $form_name; ?> span_class form_span_vert" style=""></span>
                            <div style="margin-top:15px">
                                <button id="add_trust_check" class="btn btn-sm btn-primary add_check" title="Click to deposit funds into Trust Account" style="margin-top:-5px; display:none">Deposit</button>
                                <span id="new_trust_holder">
                                    <button class="btn btn-sm btn-success save_field" title="Click to save Trust" id="trust_accountSaveLink" style="display:none">Attach Kase to Trust</button>
                                     <div style="float:left; margin-right:15px; display:none" id="trust_accountDoNotUse_holder">
                                         <input type="checkbox" id="trust_accountDoNotUse" value="Y" class="dont_use_account" /> Don't use Trust Account
                                     </div>
                                </span>
                            </div>
                        </div>
                        <div id="trust_not_attached" style="font-style:italic; margin-top:20px; display:none" class="white_text">This Kase is not Attached to the Trust Account</div>
                        <div id="trust_transactions_link_holder" style="margin-top:25px; display:none">
                        	<a id="trust_transactions" class="review_transactions white_text" style="cursor:pointer; text-decoration:underline">Transactions</a>
                        </div>
                        <div id='trust_account_checks_div' style='display:none'>
                        	<div id='trust_account_checks'></div>
                        </div>
                    </div>
                    <div id="operating_accountGrid" class="gridster_border account_selection" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:49%; display:none; vertical-align:top">
                        <div style="float:right; display:none" id="operating_balance_holder" class="white_text"></div>
                        <div style="display:inline-block; width:85px; vertical-align:top">
                            <h6><div class="form_label_vert" style="margin-top:10px; color:white; font-size:1.4em">Cost</div></h6>
                        </div>
       					<div style="display:inline-block">
                            <select name="operating_accountInput"  id="operating_accountInput" class="account_select modal_input"></select>
                            <span id="operating_accountSpan" class="account_selection <?php echo $form_name; ?> span_class form_span_vert" style=""></span>
                            <div style="margin-top:15px">
                                <button id="add_operating_check" class="btn btn-sm btn-primary add_check" title="Click to Deposit funds into Trust Cost Account" style="margin-top:-5px; display:none">Deposit</button>
                                <span id="new_operating_holder">
                                    <button class="btn btn-sm btn-success save_field" title="Click to save Operating Account" id="operating_accountSaveLink" style="display:none">Attach Kase to Cost Acct</button>
                                     <div style="float:left; margin-right:15px" id="operating_accountDoNotUse_holder">
                                         <input type="checkbox" id="operating_accountDoNotUse" value="Y" class="dont_use_account" /> Don't use Trust Cost Account
                                     </div>
                                </span>
                            </div>
                        </div>
                        <div id="operating_not_attached" style="font-style:italic; margin-top:20px; display:none" class="white_text">This Kase is not Attached to the Trust Cost Account</div>
                        <div id="operating_transactions_link_holder" style="margin-top:25px; display:none">
                            <a id="operating_transactions" class="review_transactions white_text" style="cursor:pointer; text-decoration:underline">Transactions</a>
                        </div>
                        <div id='operating_account_checks_div' style='display:none'>
                            <div id='operating_account_checks'></div>
                        </div>
                    </div>
             </div>
        </form>
      </div>
</div>                
<div id="account_selection_done"></div>
<script language="javascript">
$("#account_selection_done").trigger( "click" );
</script>