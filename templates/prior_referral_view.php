<div class="gridster prior_referral_view prior_referral" id="gridster_prior_referral" style="display:">
     <div style="background:url(img/glass_card_fade_4.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;" class="col-md-6">
    <form id="prior_referral_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="fee" />
        <input id="table_id" name="table_id" type="hidden" value="<%= fee_id %>" />
        <input id="prior_referral_id" name="prior_referral_id" type="hidden" value="<%= fee_id %>" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />
        <input id="settlement_id" name="settlement_id" type="hidden" value="<%= settlement_id %>" />
        <input id="fee_type" name="fee_type" type="hidden" value="prior_referral" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "prior_referral"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
            <li id="fee_dateGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Fee Date</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="fee_dateSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="fee_dateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= fee_date %>" name="fee_dateInput" id="fee_dateInput" class="kase prior_referral_view input_class hidden" placeholder="Fee Date" style="margin-top:-26px; margin-left:70px; width:55%" />
              <span id="fee_dateSpan" class="kase prior_referral_view span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%=fee_date %></span>
            </li>
            <li id="fee_paidGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Fee Paid</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="fee_paidSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="fee_paidSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="display:inline-block">
             <div style="float:left; border:#0000CC 0px solid;z-index:3258">
              <input value="<%= fee_paid %>" name="fee_paidInput" id="fee_paidInput" class="kase input_class hidden prior_referral" placeholder="Amount" style="margin-top:-43px; margin-left:70px; width:100px;z-index:3259; width:55%" />
              <span id="fee_paidSpan" class="kase prior_referral_view span_class form_span_vert" style="margin-top:-43px; margin-left:70px"><%= fee_paid %></span>
             </div>
             </div>
            </li>
			<li id="fee_recipientGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Recipient</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="fee_recipientSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="fee_recipientSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= fee_recipient %>" name="fee_recipientInput" id="fee_recipientInput" class="kase prior_referral_view input_class hidden" placeholder="Recipient" style="margin-top:-26px; margin-left:70px; width:55%" />
              <span id="fee_dateSpan" class="kase prior_referral_view span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%= fee_recipient %></span>
            </li>
			<li id="fee_check_numberGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Check #</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="fee_check_numberSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="fee_check_numberSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= fee_check_number %>" name="fee_check_numberInput" id="fee_check_numberInput" class="kase prior_referral_view input_class hidden" placeholder="Fee Check #" style="margin-top:-26px; margin-left:70px; width:55%" />
              <span id="fee_check_numberSpan" class="kase prior_referral_view span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%= fee_check_number %></span>
            </li>
			<li id="fee_referralGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Referral</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="referralSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="fee_referralSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= fee_referral %>" name="fee_referralInput" id="fee_referralInput" class="kase prior_referral_view input_class hidden" placeholder="Referral" style="margin-top:-26px; margin-left:70px; width:365px" />
              <span id="fee_referralSpan" class="kase prior_referral_view span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%= fee_referral %></span>
            </li>
		</ul>
    </form>
	</div>
</div>
<div id="prior_referral_all_done"></div>
<script language="javascript">
$( "#prior_referral_all_done" ).trigger( "click" );
</script>