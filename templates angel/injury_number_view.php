<% 
var ct_top = -7;
var margin_top = -26;
if (blnIE) {
	margin_top = -42;
    ct_top = -22;
}
%>
<div class="gridster injury_number_view injury_number" id="gridster_injury_number" style="display:">
     <div style="background:url(img/glass_card_fade_4.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="injury_number_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="injury_number" />
        <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />
        <input id="injury_number_uuid" name="injury_number_uuid" type="hidden" value="<%= uuid %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "injury_number"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
            <li id="insurance_policy_numberGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Policy Number</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="insurance_policy_numberSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="insurance_policy_numberSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= insurance_policy_number %>" name="insurance_policy_numberInput" id="insurance_policy_numberInput" class="kase injury_number_view input_class hidden" placeholder="Policy Number" style="margin-top:-26px; margin-left:90px" />
              <span id="insurance_policy_numberSpan" class="kase injury_number_view span_class form_span_vert" style="margin-top:-26px; margin-left:90px"><%= insurance_policy_number %></span>
            </li>
            <li id="alternate_policy_numberGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Add. Claim #:</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="alternate_policy_numberSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="alternate_policy_numberSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="display:inline-block">
             <div style="float:left; border:#0000CC 0px solid;z-index:3258">
              <input value="<%= alternate_policy_number %>" name="alternate_policy_numberInput" id="alternate_policy_numberInput" class="kase input_class hidden injury_number" placeholder="Claim #1" style="margin-top:-43px; margin-left:90px; width:100px;z-index:3259; width:65%" />
              <span id="alternate_policy_numberSpan" class="kase injury_number_view span_class form_span_vert" style="margin-top:-43px; margin-left:90px"><%= alternate_policy_number %></span>
             </div>
             </div>
            </li>
		</ul>
    </form>
	</div>
</div>
<div class="injury_number" id="all_done"></div>
<script language="javascript">
$( ".injury_number#all_done" ).trigger( "click" );
</script>