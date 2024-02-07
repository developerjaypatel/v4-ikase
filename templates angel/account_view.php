<?php 
$blnAdmin = (strpos($_SESSION['user_role'], "admin")!==false);
if ($blnAdmin) {
	die("no no");
}
$form_name = "account"; ?>
<div style="position:absolute; left:500px">
	<img src="images/check_sample.jpg" width="834" height="335" alt="Check Sample" />
</div>
<div class="account" id="account_panel" style="background:url(img/glass_info.png) left top repeat-y; width:390px">
    <form id="account_form" parsley-validate>
    <input type="hidden" name="table_id" id="table_id" value="<%=id %>" />
    <input type="hidden" name="account_type" id="account_type" value="<%=account_type %>" />
    <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
        <div style="margin-top:0px; margin-right:10px; padding-top:5px; position:relative">            
            <?php 
            include("dashboard_view_navigation.php"); 
            ?>
            <div id="return_list" style="position:absolute; top:7px; left:180px">
            	<button class="btn btn-primary btn-xs" id="return_summary">Return to Summary View</button>
            </div>
        </div>
    </div>
    
    <div class="gridster account" id="gridster_<?php echo $form_name; ?>" style="">  
        <ul style="margin-bottom:10px; width:100%">
            <li id="account_bankGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Name</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="account_bankSave">
                    <a class="save_field" title="Click to save this field" id="account_bankSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="" name="account_bankInput" id="account_bankInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:115px; width:234px" />
                  <span id="account_bankSpan" class="kase account_bank span_class form_span_vert" style="margin-top:-26px; margin-left:115px"></span>
            </li>
            <li id="account_numberGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Account #</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="account_numberSave">
                    <a class="save_field" title="Click to save this field" id="account_numberSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="" name="account_numberInput" id="account_numberInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:115px; width:234px" />
                  <span id="account_numberSpan" class="kase account_number span_class form_span_vert" style="margin-top:-26px; margin-left:115px"></span>
            </li>
            <li id="routing_numberGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Routing #</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="routing_numberSave">
                    <a class="save_field" title="Click to save this field" id="routing_numberSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="" name="routing_numberInput" id="routing_numberInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:115px; width:234px" />
                  <span id="routing_numberSpan" class="kase routing_number span_class form_span_vert" style="margin-top:-26px; margin-left:115px"></span>
            </li>
            <li id="swift_codeGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Swift Code</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="swift_codeSave">
                    <a class="save_field" title="Click to save this field" id="swift_codeSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="" name="swift_codeInput" id="swift_codeInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:115px; width:234px" />
                  <span id="swift_codeSpan" class="kase swift_code span_class form_span_vert" style="margin-top:-26px; margin-left:115px"></span>
            </li>
            <li id="account_holderGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Account Holder</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="account_holderSave">
                    <a class="save_field" title="Click to save this field" id="account_holderSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="" name="account_holderInput" id="account_holderInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:115px; width:234px" />
                  <span id="account_holderSpan" class="kase account_holder span_class form_span_vert" style="margin-top:-26px; margin-left:115px"></span>
            </li>
            <li id="branchGrid" data-row="5" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Branch</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="branchSave">
                    <a class="save_field" title="Click to save this field" id="branchSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="" name="branchInput" id="branchInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:115px; width:234px" />
                  <span id="branchSpan" class="kase branch span_class form_span_vert" style="margin-top:-26px; margin-left:115px"></span>
            </li>
            <li id="branch_addressGrid" data-row="6" data-col="1" data-sizex="1" data-sizey="2" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Address</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="branch_addressSave">
                    <a class="save_field" title="Click to save this field" id="branch_addressSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <textarea name="branch_addressInput" id="branch_addressInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:115px; width:234px; height:50px"></textarea>
                  <span id="branch_addressSpan" class="kase branch_address span_class form_span_vert" style="margin-top:-26px; margin-left:115px"></span>
            </li>
            <li id="current_check_numberGrid" data-row="7" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Current Check #</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="current_check_numberSave">
                    <a class="save_field" title="Click to save this field" id="current_check_numberSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="" name="current_check_numberInput" id="current_check_numberInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:115px; width:234px" />
                  <span id="current_check_numberSpan" class="kase current_check_number span_class form_span_vert" style="margin-top:-26px; margin-left:115px"></span>
            </li>
            <li id="approval_conditionGrid" data-row="8" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Approval</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="approval_conditionSave">
                    <a class="save_field" title="Click to save this field" id="approval_conditionSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <select value="" name="approval_conditionInput" id="approval_conditionInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:115px; width:234px">
                  	<option value="">Select from List</option>
                    <option value="balance">Balance (Deposits - Withdrawals) is Greater Than Requested Amount</option>
                    <option value="available">Available (Balance - Pending) is Greater Than Requested Amount</option>
                    <option value="none">Approval without Balance/Available Minimum</option>
                  </select>
                  <span id="approval_conditionSpan" class="kase approval_condition span_class form_span_vert" style="margin-top:-26px; margin-left:115px"></span>
            </li>
            <li id="disclaimerGrid" data-row="9" data-col="1" data-sizex="1" data-sizey="2" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:374px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Disclaimer</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="disclaimerSave">
                    <a class="save_field" title="Click to save this field" id="disclaimerSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <textarea name="disclaimerInput" id="disclaimerInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:115px; width:234px; height:50px"></textarea>
                  <span id="disclaimerSpan" class="kase disclaimer span_class form_span_vert" style="margin-top:-26px; margin-left:115px"></span>
            </li>
        </ul>
    </div>
    </form>
</div>

<div id="account_all_done"></div>
<script language="javascript">
$("#account_all_done").trigger( "click" );
</script>