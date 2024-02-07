<div class="gridster settlement_view settlement" id="gridster_settlement" style="display:">
     <div style="background:url(img/glass_card_fade_4.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;" class="col-md-6">
    <form id="settlement_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="settlement" />
        <input id="table_id" name="table_id" type="hidden" value="<%= settlement_id %>" />
        <input id="settlement_id" name="settlement_id" type="hidden" value="<%= settlement_id %>" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "settlement"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
            <li id="date_settledGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="settlement gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Date Settled</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="date_settledSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_settledSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= date_settled %>" name="date_settledInput" id="date_settledInput" class="kase settlement_view input_class hidden" placeholder="Date Settled" style="margin-top:-26px; margin-left:90px; width:55%" />
              <span id="date_settledSpan" class="kase settlement_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%=date_settled %></span>
            </li>
			<li id="date_approvedGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="settlement gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Date Approved</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="date_approvedSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_approvedSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= date_approved %>" name="date_approvedInput" id="date_approvedInput" class="kase settlement_view input_class hidden" placeholder="Date Approved" style="margin-top:-26px; margin-left:90px; width:55%" />
              <span id="date_approvedSpan" class="kase settlement_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%=date_approved %></span>
            </li>
            <li id="amount_of_settlementGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Settlement Amt.</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="amount_of_settlementSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_of_settlementSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="display:inline-block">
             <div style="float:left; border:#0000CC 0px solid;z-index:3258">
              <input value="<%= amount_of_settlement %>" name="amount_of_settlementInput" id="amount_of_settlementInput" class="kase input_class hidden settlement" placeholder="Amount" style="margin-top:-43px; margin-left:90px; width:100px;z-index:3259; width:55%" />
              <span id="amount_of_settlementSpan" class="kase settlement_view span_class form_span_vert" style="margin-top:-43px; margin-left:90px"><%= amount_of_settlement %></span>
             </div>
             </div>
            </li>
			<li id="amount_of_feeGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="settlement gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Fee Amount</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="amount_of_feeSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_of_feeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= amount_of_fee %>" name="amount_of_feeInput" id="amount_of_feeInput" class="kase settlement_view input_class hidden" placeholder="Amount of Fee" style="margin-top:-26px; margin-left:90px; width:55%" />
              <span id="amount_of_feeSpan" class="kase settlement_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= amount_of_fee %></span>
            </li>
			<li id="c_and_rGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="settlement gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">C&R </div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="c_and_rSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="c_and_rSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= c_and_r %>" name="c_and_rInput" id="c_and_rInput" class="kase settlement_view input_class hidden" placeholder="C&R" style="margin-top:-26px; margin-left:90px; width:55%" />
              <span id="c_and_rSpan" class="kase settlement_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= c_and_r %></span>
            </li>
			<li id="stipGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="settlement gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">STIP</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="stipSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="stipSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= stip %>" name="stipInput" id="stipInput" class="kase settlement_view input_class hidden" placeholder="STIP" style="margin-top:-26px; margin-left:90px; width:55%" />
              <span id="stipSpan" class="kase settlement_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= stip %></span>
            </li>
			<li id="f_and_aGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="settlement gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">F&A</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="f_and_aSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="f_and_aSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= f_and_a %>" name="f_and_aInput" id="f_and_aInput" class="kase settlement_view input_class hidden" placeholder="F&A" style="margin-top:-26px; margin-left:90px; width:55%" />
              <span id="f_and_aSpan" class="kase settlement_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= f_and_a %></span>
            </li>
			<li id="date_fee_receivedGrid" data-row="4" data-col="2" data-sizex="1" data-sizey="1" class="settlement gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Date Received</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="date_fee_receivedSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_fee_receivedSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= date_fee_received %>" name="date_fee_receivedInput" id="date_fee_receivedInput" class="kase settlement_view input_class hidden" placeholder="Date Fees Received" style="margin-top:-26px; margin-left:90px; width:55%" />
              <span id="date_fee_receivedSpan" class="kase settlement_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%=date_fee_received %></span>
            </li>
			<li id="attorneyGrid" data-row="5" data-col="1" data-sizex="1" data-sizey="1" class="settlement gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Attorney</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="attorneySave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="attorneySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            	<input value="<%=attorney_full_name %>" type="hidden" id="attorney_full_name" />
              <input value="<%= attorney %>" name="attorneyInput" id="attorneyInput" class="kase settlement_view input_class hidden" placeholder="Attorney Who Settled" style="margin-top:-26px; margin-left:90px; width:75%" />
              <span id="attorneySpan" class="kase settlement_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= attorney %></span>
            </li>
		</ul>
    </form>
	</div>
</div>
<div id="settlement_all_done"></div>
<script language="javascript">
$( "#settlement_all_done" ).trigger( "click" );
</script>