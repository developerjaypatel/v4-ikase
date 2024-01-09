<div class="gridster rental_view rental" id="gridster_rental" style="display:none">
     <div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="rental_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="rental" />
        <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
        <input id="rental_id" name="rental_id" type="hidden" value="<%= id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="billing_time" name="billing_time" type="hidden" value="" />
        <input id="representing" name="representing" type="hidden" value="" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%=injury_id %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "rental";
			//$kase_type_pi_confirm = "yes"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
        	<li id="companyGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Company</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="companySave">
                    <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="companySaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="" name="companyInput" id="companyInput" class="kase rental_view input_class hidden" placeholder="Rental Company" style="margin-top:-26px; margin-left:60px; width:385px" tabindex="1" />
                <span id="companySpan" class="kase rental_view span_class form_span_vert" style="margin-top:-28px; margin-left:0px"><%= company %></span>
            </li>
        </ul>
	</form>
</div>    