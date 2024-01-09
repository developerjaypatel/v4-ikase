<div class="gridster property_damage <%= accident_partie %>" id="gridster_property_damage" style="display:">
     <div style="background:url(img/glass_card_dark_1.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="property_damage_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="property_damage" />
        <input id="case_id" name="case_id" type="hidden" value="" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "Property Damage"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul class="property_damage">
		  <li id="damage_reportGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border property_damage" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Agency">Damage Per Police Report:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="damage_reportSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="damage_reportSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <label style="display: block; padding-left: 80px; margin-top: -20px; text-indent: -40px;"><input value="" type="radio" name="damage_reportInput" id="damage_report_slightInput" class="kase input_class hidden property_damage" style="margin-top:-26px; margin-left:100px; z-index:3259; width: 20px; height: 20px; padding: 0; position: relative; top: -1px; *overflow: hidden; vertical-align: bottom;"  />&nbsp;&nbsp;Slight&nbsp;&nbsp;
				<input value="" type="radio" name="damage_reportInput" id="damage_report_moderateInput" class="kase input_class hidden property_damage" style="margin-top:-26px; margin-left:10px; z-index:3259; width: 20px; height: 20px; padding: 0; position: relative; top: -1px; *overflow: hidden; vertical-align: bottom;"  />&nbsp;&nbsp;Moderate&nbsp;&nbsp;
				<input value="" type="radio" name="damage_reportInput" id="damage_report_severeInput" class="kase input_class hidden property_damage" style="margin-top:-26px; margin-left:10px; z-index:3259; width: 20px; height: 20px; padding: 0; position: relative; top: -1px; *overflow: hidden; vertical-align: bottom;"  />&nbsp;&nbsp;Severe&nbsp;&nbsp;</label>
                <span id="damage_reportSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-51px; margin-left:500px">
                </span>
          </li>
		  <li id="left_sideGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="3" class="gridster_border property_damage" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="">Left:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="left_sideSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="left_sideSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
				<label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="Fender" type="checkbox" name="left_fenderInput" id="left_fenderInput" class="kase input_class hidden property_damage" style="margin-top:-20px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"   />&nbsp;&nbsp;Fender</label>
				<span id="left_fenderSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-20px; margin-left:70px">
                </span>
				<label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="" type="checkbox" name="left_doorInput" id="left_doorInput" class="kase input_class hidden property_damage" style="margin-top:15px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"  />&nbsp;&nbsp;Door</label>
				<span id="left_doorSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-15px; margin-left:70px">
                </span>
				<label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="" type="checkbox" name="left_quarter_panelInput" id="left_quarter_panelInput" class="kase input_class hidden property_damage" style="margin-top:15px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"  />&nbsp;&nbsp;Quarter Panel</label>
                <span id="left_quarter_panelSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-10px; margin-left:70px">
                </span>
          </li>
		  <li id="front_sideGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="3" class="gridster_border property_damage" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="">Front:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="front_sideSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="front_sideSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="" type="checkbox" name="front_bumperInput" id="front_bumperInput" class="kase input_class hidden property_damage" style="margin-top:-20px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"  />&nbsp;&nbsp;Bumper</label>
				<span id="front_bumperSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
				<label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="" type="checkbox" name="grillInput" id="grillInput" class="kase input_class hidden property_damage" style="margin-top:15px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"  />&nbsp;&nbsp;Grill</label>
				<span id="grillSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
				<label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="" type="checkbox" name="hoodInput" id="hoodInput" class="kase input_class hidden property_damage" style="margin-top:15px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"  />&nbsp;&nbsp;Hood</label>
                <span id="hoodSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		  <li id="rear_sideGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="3" class="gridster_border property_damage" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="">Rear:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="rear_sideSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="rear_sideSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="" type="checkbox" name="rear_bumperInput" id="rear_bumperInput" class="kase input_class hidden property_damage"  style="margin-top:-20px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"  />&nbsp;&nbsp;Bumper</label>
				<span id="rear_bumperSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
				<label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="" type="checkbox" name="trunkInput" id="trunkInput" class="kase input_class hidden property_damage"  style="margin-top:15px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"  />&nbsp;&nbsp;Trunk</label>
				<span id="trunkSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-28px; margin-left:90px">
                </span>
          </li>
		  <li id="right_sideGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="3" class="gridster_border property_damage" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="">Right:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="right_sideSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="right_sideSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="Fender" type="checkbox" name="right_fenderInput" id="right_fenderInput" class="kase input_class hidden property_damage" style="margin-top:-20px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"   />&nbsp;&nbsp;Fender</label>
				<span id="right_fenderSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-20px; margin-left:70px">
                </span>
				<label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="" type="checkbox" name="right_doorInput" id="right_doorInput" class="kase input_class hidden property_damage" style="margin-top:15px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"  />&nbsp;&nbsp;Door</label>
				<span id="right_doorSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-15px; margin-left:70px">
                </span>
				<label style="display: block; padding-left: 15px; text-indent: -15px;"><input value="" type="checkbox" name="right_quarter_panelInput" id="right_quarter_panelInput" class="kase input_class hidden property_damage" style="margin-top:15px; margin-left:35px; z-index:3259; width: 20px; height: 20px; padding: 0; vertical-align: bottom; position: relative; top: -1px; *overflow: hidden;"  />&nbsp;&nbsp;Quarter Panel</label>
                <span id="right_quarter_panelSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-10px; margin-left:70px">
                </span>
          </li>
		  <li id="other_damageGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border property_damage" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="">Other Damage:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="other_damageSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="other_damageSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <textarea value="" name="other_damageInput" id="other_damageInput" class="kase input_class hidden property_damage" style="margin-top:5px; margin-left:20px; z-index:3259; width: 400px;" rows="4"></textarea>
				<span id="other_damageSpan" class="kase property_damage span_class form_span_vert" style="margin-top:-20px; margin-left:70px">
                </span>
          </li>
		</ul>
    </form>
</div>
</div>
<div class="property_damage" id="property_damage_all_done"></div>
<script language="javascript">
$( "#property_damage_all_done" ).trigger( "click" );
</script>