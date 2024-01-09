<div class="gridster rental_view rental <%= accident_partie %>" id="gridster_rental" style="display:">
     <div style="background:url(img/glass_card_dark_2.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="rental_form" parsley-validate>
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="representing" name="representing" type="hidden" value="<%=accident_partie %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "rental"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
		  <li id="agencyGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Angecy">Agency</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="agencySave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="agencySaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="agencyInput" id="agencyInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:80px; width:355px ;z-index:3259;"  />
                <span id="agencySpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
          <li id="rentedGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">Rented a Car</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="rentedSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="rentedSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="Y" type="checkbox" name="rentedInput" id="rentedInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:18px; z-index:3259;"  />
                <span id="rentedSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
          <li id="completedGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">Finished</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="completedSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="completedSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="Y" type="checkbox" name="completedInput" id="completedInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:18px; z-index:3259;"  />
                <span id="completedSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
          <li id="paid_byGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="rental gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Paid By</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="paid_bySave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="paid_bySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="paid_byInput" id="paid_byInput" class="kase rental_view input_class hidden" placeholder="Paid by" style="margin-top:-26px; margin-left:80px; width:355px" parsley-error-message="" />
              <span id="paid_bySpan" class="kase rental_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"></span>
            </li>
            <li id="amount_billedGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
                <h6><div class="form_label_vert" style="margin-top:10px;">Billed</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="amount_billedSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_billedSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="amount_billedInput" id="amount_billedInput" class="kase input_class hidden rental_view" style="margin-top:-26px; margin-left:80px; width:120px" placeholder="$ Amount" />
                <span id="amount_billedSpan" class="kase rental_view span_class form_span_vert" style="overflow-x: hidden;overflow-y : auto; height:100px; margin-top:-26px; margin-left:90px">
                </span>
          </li>
          <li id="rental_paymentGrid" data-row="5" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">Payment</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="rental_paymentSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="rental_paymentSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="rental_paymentInput" id="rental_paymentInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:80px; width:120px;z-index:3259; width:119px" placeholder="$ Payment"  />
                <span id="rental_paymentSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		  
		  <li id="rental_balanceGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">Balance</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="rental_balanceSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="rental_balanceSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="rental_balanceInput" id="rental_balanceInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:80px; width:120px;z-index:3259; width:119px" placeholder="$ Balance"  />
                <span id="rental_balanceSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		</ul>
    </form>
</div>
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