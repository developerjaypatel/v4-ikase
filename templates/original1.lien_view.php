<div class="gridster lien_view lien" id="gridster_lien" style="display:">
     <div style="background:url(img/glass_card_fade_4.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;" class="col-md-6">
    <form id="lien_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="lien" />
        <input id="table_id" name="table_id" type="hidden" value="<%= lien_id %>" />
        <input id="lien_id" name="lien_id" type="hidden" value="<%= lien_id %>" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "lien"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
            <li id="date_filedGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="lien gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Date Filed</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="date_filedSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_filedSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= date_filed %>" name="date_filedInput" id="date_filedInput" class="kase lien_view input_class hidden" placeholder="mm/dd/yyyy" style="margin-top:-26px; margin-left:70px" />
              <span id="date_filedSpan" class="kase lien_view span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%=date_filed %></span>
            </li>
            <li id="workerGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="lien gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">By</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="workerSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="workerSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="workerInput" id="workerInput" class="kase lien_view input_class hidden" placeholder="Approved By" style="margin-top:-26px; margin-left:70px; width:105px" />
              <input value="<%=worker_full_name %>" type="hidden" id="worker_full_name" />
              <span id="workerSpan" class="kase lien_view span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%= worker_full_name %></span>
            </li>
			<li id="amount_of_lienGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Lien Amount</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="amount_of_lienSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_of_lienSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="display:inline-block">
             <div style="float:left; border:#0000CC 0px solid;z-index:3258">
              <input value="<%= amount_of_lien %>" name="amount_of_lienInput" id="amount_of_lienInput" class="kase input_class hidden lien" placeholder="Amount of Lien" style="margin-top:-43px; margin-left:70px; width:105px;z-index:3259; width:105px" />
              <span id="amount_of_lienSpan" class="kase lien_view span_class form_span_vert" style="margin-top:-43px; margin-left:70px"><%= amount_of_lien %></span>
             </div>
             </div>
            </li>
            <li id="amount_of_feeGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="lien gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Fee Amount</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="amount_of_feeSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_of_feeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= amount_of_fee %>" name="amount_of_feeInput" id="amount_of_feeInput" class="kase lien_view input_class hidden" placeholder="Amount of Fee" style="margin-top:-26px; margin-left:70px; width:105px" />
              <span id="amount_of_feeSpan" class="kase lien_view span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%= amount_of_fee %></span>
            </li>
            <li id="date_paidGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="lien gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Date Paid</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="date_paidSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="date_paidSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= date_paid %>" name="date_paidInput" id="date_paidInput" class="kase lien_view input_class hidden" placeholder="mm/dd/yyyy" style="margin-top:-26px; margin-left:70px; width:105px" />
              <span id="date_paidSpan" class="kase lien_view span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%=date_paid %></span>
            </li>
			<li id="amount_paidGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="lien gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Amount Paid</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="amount_paidSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_paidSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= amount_paid %>" name="amount_paidInput" id="amount_paidInput" class="kase lien_view input_class hidden" placeholder="Amount Paid" style="margin-top:-26px; margin-left:70px; width:105px" />
              <span id="amount_paidSpan" class="kase lien_view span_class form_span_vert" style="margin-top:-26px; margin-left:70px"><%=amount_paid %></span>
            </li>
            <li id="appearanceGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="lien gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            	<div style="margin-top:6px; text-align:center; width:100%"><a id="new_appearance" style="cursor:pointer" class="white_text">Add Appearance to Kalendar</a></div>
            </li>
		</ul>
    </form>
	</div>
</div>
<div id="lien_events" class="lien col-md-5" style="display:none; border:0px solid pink; margin-left:100px; margin-top:0px">
</div>
<div id="lien_notes" class="lien col-md-5" style="display:none; border:0px solid pink; margin-left:100px; margin-top:0px">
</div>
<div id="lien_all_done"></div>
<script language="javascript">
$( "#lien_all_done" ).trigger( "click" );
</script>