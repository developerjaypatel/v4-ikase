<div class="gridster priors <%=accident_partie %>" id="gridster_priors" style="display:">
     <div style="background:url(img/glass_card_dark_long_1.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="priors_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="priors" />
        <input id="case_id" name="case_id" type="hidden" value="" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "Priors"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
		  <li id="prior_injuryGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:;">
			<h6><div class="form_label_vert" style="margin-top:10px;">Prior Injury</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="prior_injurySave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="prior_injurySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <textarea value="" name="prior_injuryInput" id="prior_injuryInput" class="priors input_class" placeholder="" rows="7" style="margin-top:20px; margin-left:25px; width:400px" tabindex="2"></textarea>
            <span id="prior_injurySpan" class="priors span_class form_span_vert hidden" style="margin-top:-30px; margin-left:85px"></span>
		  </li>
		  <li id="prior_feloniesGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:;">
			<h6><div class="form_label_vert" style="margin-top:10px;">Prior Felonies</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="prior_feloniesSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="prior_feloniesSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <textarea value="" name="prior_feloniesInput" id="prior_feloniesInput" class="priors input_class" placeholder="" rows="7" style="margin-top:20px; margin-left:25px; width:400px" tabindex="2"></textarea>
            <span id="prior_feloniesSpan" class="priors span_class form_span_vert hidden" style="margin-top:-30px; margin-left:85px"></span>
		  </li>
		</ul>
    </form>
</div>
</div>
<div class="priors" id="priors_all_done"></div>
<script language="javascript">
$( "#priors_all_done" ).trigger( "click" );
</script>