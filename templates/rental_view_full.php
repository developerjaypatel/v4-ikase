<div class="gridster rental_view rental <%= accident_partie %>" id="gridster_rental" style="display:">
     <div style="background:url(img/glass_card_dark_2.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="rental_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="rental" />
        <input id="case_id" name="case_id" type="hidden" value="" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "Accident Costs"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
				<div style="font-weight:bold; font-size:1.4em; border:1px solid blue; margin-top:13px; margin-left:-40px; background:#CCCCCC">Estimate</div><br/>
				<li id="examined_byGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="rental gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
				<h6><div class="form_label_vert" style="margin-top:10px;">Examined By</div></h6>
				<div style="margin-top:-12px" class="save_holder hidden" id="examined_bySave">
					<a class="save_field" style="margin-top:0px" title="Click to save this field" id="examined_bySaveLink">
						<i class="glyphicon glyphicon-save"></i>
					</a>
				</div>
				  <input value="" name="examined_byInput" id="examined_byInput" class="kase rental_view input_class hidden" placeholder="Examiner" style="margin-top:-26px; margin-left:80px; width:355px" parsley-error-message="" />
				  <span id="examined_bySpan" class="kase rental_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"></span>
				</li>
				<li id="requestedGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="rental gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
				<h6><div class="form_label_vert" style="margin-top:10px;">Requested</div></h6>
				<div style="margin-top:-12px" class="save_holder hidden" id="requestedSave">
					<a class="save_field" style="margin-top:0px" title="Click to save this field" id="requestedSaveLink">
						<i class="glyphicon glyphicon-save"></i>
					</a>
				</div>
				  <input value="" name="requestedInput" id="requestedInput" class="kase rental_view input_class hidden" placeholder="00/00/0000" style="margin-top:-26px; margin-left:80px; width:120px" parsley-error-message="" required />
				  <span id="requestedSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"></span>
				</li>
				<li id="receivedGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
				<h6><div class="form_label_vert" style="margin-top:10px;">Received</div></h6>
				  <div style="margin-top:-23px" class="save_holder hidden" id="receivedSave">
					<a class="save_field" style="margin-top:0px" title="Click to save this field" id="receivedSaveLink">
						<i class="glyphicon glyphicon-save"></i>
					</a>
				</div>
				  <input value="" name="receivedInput" id="receivedInput" class="kase rental_view input_class hidden datepicker" placeholder="00/00/0000" style="margin-top:-26px; margin-left:80px; z-index:3259; width:120px" parsley-error-message="" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);checkStartEnd();" required />
				  <span id="receivedSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"></span>
				</li>
				<li id="amountGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
				<h6><div class="form_label_vert" style="margin-top:10px;">Amount:</div></h6>
				<div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="amountSave">
					<a class="save_field" style="margin-top:0px" title="Click to save this field" id="amountSaveLink">
						<i class="glyphicon glyphicon-save"></i>
					</a>
				</div>
				<input value="" name="amountInput" id="amountInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:80px; width:120px" />
				<span id="amountSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
				</span>
				</li>
				<li id="totaledGrid" data-row="4" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
				<h6><div class="form_label_vert" style="margin-top:10px;">Totaled:</div></h6>
				<div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="totaledSave">
					<a class="save_field" style="margin-top:0px" title="Click to save this field" id="totaledSaveLink">
						<i class="glyphicon glyphicon-save"></i>
					</a>
				</div>
                <% var input_line = '<input value="" type="checkbox" onclick="clickIt()" name="totaledInput" id="totaledInput"  class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:18px;" />';
                var input_check = $('#totaledInput').val();
                var input_checked = '<input value="Y" type="checkbox" onclick="clickIt()" name="totaledInput" id="totaledInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:18px;" checked="checked" />';
                %>
                <% if (input_check == "Y") { %>
                	<%=input_checked %>
                <% } else { %>
                	<%=input_line %>
                <% } %>
                <% var span_line = '<span id="totaledSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px"></span>';
                var span_checked = '<span id="totaledSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">Y</span>';
                %>
                <% if (input_check == "Y") { %>
                	<%=span_checked %>
                <% } else { %>
                	<%=span_line %>
                <% } %>
				</li>
				<div style="font-weight:bold; font-size:1.4em; border:1px solid green; margin-top:150px; margin-left:-40px; background:#CCCCCC">Settlement</div><br/>
            <li id="paid_byGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Paid By:</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="paid_bySave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="paid_bySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="" name="paid_byInput" id="paid_byInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:80px; width:355px" required />
            <span id="paid_bySpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
            </span>
            </li>
            <li id="blue_bookGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
                <h6><div class="form_label_vert" style="margin-top:10px;">Blue Book</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="blue_bookSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="blue_bookSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="blue_bookInput" id="blue_bookInput" class="kase input_class hidden rental_view" style="margin-top:-26px; margin-left:80px; width:120px" />
                <span id="blue_bookSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
            </li>
            <li id="amount_paidGrid" data-row="7" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
                <h6><div class="form_label_vert" style="margin-top:10px;">Amount Paid</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="amount_paidSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="amount_paidSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="amount_paidInput" id="amount_paidInput" class="kase input_class hidden rental_view" style="margin-top:-26px; margin-left:80px; width:120px" />
                <span id="amount_paidSpan" class="kase rental_view span_class form_span_vert" style="overflow-x: hidden;overflow-y : auto; height:100px; margin-top:-26px; margin-left:90px">
                </span>
          </li>
          <li id="deductibleGrid" data-row="8" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">Deductible:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="deductibleSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="deductibleSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="deductibleInput" id="deductibleInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:80px; width:120px;z-index:3259; width:119px"  />
                <span id="deductibleSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		  
		  <li id="balanceGrid" data-row="8" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">Balance:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="balanceSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="balanceSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="balanceInput" id="balanceInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:80px; width:120px;z-index:3259; width:119px"  />
                <span id="balanceSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		  <div style="font-weight:bold; font-size:1.4em; border:1px solid yellow; margin-top:145px; margin-left:-40px; background:#CCCCCC">Rental</div><br/>
		  <li id="agencyGrid" data-row="10" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Angecy">Agency:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="agencySave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="agencySaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="agencyInput" id="agencyInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:80px; width:355px ;z-index:3259;"  />
                <span id="agencySpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		  <li id="pd_completeGrid" data-row="11" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">PD Complete:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="pd_completeSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="pd_completeSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" type="checkbox" name="pd_completeInput" id="pd_completeInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:30px; width:100px;z-index:3259; width:119px"  />
                <span id="pd_completeSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		  <li id="photosGrid" data-row="11" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Angecy">Photos:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="photosSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="photosSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" type="checkbox" name="photosInput" id="photosInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:30px; width:100px;z-index:3259; width:119px"  />
                <span id="photosSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		  <li id="settledGrid" data-row="12" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">Settled:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="settledSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="settledSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" type="checkbox" name="settledInput" id="settledInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:18px; z-index:3259;"  />
                <span id="settledSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		  <li id="photo_requiredGrid" data-row="12" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Agency">Photo Req.:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="photo_requiredSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="photo_requiredSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="photo_requiredInput" id="photo_requiredInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:80px; z-index:3259; width:120px"  />
                <span id="photo_requiredSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		  <li id="photo_receivedGrid" data-row="13" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border rental" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Agency">Photo Rcvd:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="photo_receivedSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="photo_receivedSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="photo_receivedInput" id="photo_receivedInput" class="kase input_class hidden rental rental_view" style="margin-top:-26px; margin-left:80px; width:355px; z-index:3259;"  />
                <span id="photo_receivedSpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
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