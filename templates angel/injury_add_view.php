<% 
var ct_top = -7;
var margin_top = -26;
if (blnIE) {
	margin_top = -42;
    ct_top = -22;
}
%>
<div class="gridster additional_case_number" id="gridster_additional_case_number" style="display:">
     <div style="background:url(img/glass_card_fade_4.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="additional_case_number_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="injury_number" />
        <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />
        <input id="injury_number_uuid" name="injury_number_uuid" type="hidden" value="<%= uuid %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "additional_case_number"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
             <li id="carrier_claim_numberGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Case #1:</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="carrier_claim_numberSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="carrier_claim_numberSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= carrier_claim_number %>" name="carrier_claim_numberInput" id="carrier_claim_numberInput" class="kase input_class hidden additional_case_number" style="margin-top:-26px; margin-left:90px" />
            <span id="carrier_claim_numberSpan" class="kase additional_case_number span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
            <%= carrier_claim_number %>
            </span>
            </li>
            <li id="carrier_building_descriptionGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Case #2:</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="carrier_building_descriptionSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="carrier_building_descriptionSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="<%= carrier_building_description %>" name="carrier_building_descriptionInput" id="carrier_building_descriptionInput" class="kase input_class hidden additional_case_number" style="margin-top:-26px; margin-left:90px" />
            <span id="carrier_building_descriptionSpan" class="kase additional_case_number span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
            <%= carrier_building_description %>
            </span>
            </li>
            <li id="alternate_claim_numberGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
                <h6><div class="form_label_vert" style="margin-top:10px;">Case #3:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="alternate_claim_numberSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="alternate_claim_numberSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= alternate_claim_number %>" name="alternate_claim_numberInput" id="alternate_claim_numberInput" class="kase input_class hidden" style="margin-top:-26px; margin-left:90px" />
                <span id="alternate_claim_numberSpan" class="kase additional_case_number span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                <%= alternate_claim_number %>
                </span>
            </li>
            <li id="carrier_building_indentifierGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
                <h6><div class="form_label_vert" style="margin-top:10px;">Case #4:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="carrier_building_indentifierSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="carrier_building_indentifierSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= carrier_building_indentifier %>" name="carrier_building_indentifierInput" id="carrier_building_indentifierInput" class="kase input_class hidden" style="margin-top:-26px; margin-left:90px" />
                <span id="carrier_building_indentifierSpan" class="kase additional_case_number span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                <%= carrier_building_indentifier %>
                </span>
            </li>
		</ul>
    </form>
	</div>
</div>
<div class="additional_case_number" id="all_done"></div>
<script language="javascript">
$( ".additional_case_number#all_done" ).trigger( "click" );
</script>