<div class="gridster bodyparts_view bodyparts" id="gridster_bodyparts" style="display:">
     <div style="background:url(img/glass.png) left top no-repeat; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="injury_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="injury" />
        <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
        <input id="injury_uuid" name="injury_uuid" type="hidden" value="<%= uuid %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "bodyparts"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
            <li id="bodypart1Grid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Body Part 1</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="bodypart1Save">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="bodypart1SaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="bodypart1Input" id="bodypart1Input" class="kase injury_view input_class hidden" placeholder="Occupation" style="margin-top:-26px; margin-left:48px" />
              <span id="bodypart1Span" class="kase injury_view span_class form_span_vert" style="margin-top:-30px; margin-left:48px"></span>
            </li>
            <li id="bodypart2Grid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Start Date</div></h6>
              <div style="margin-top:-23px" class="save_holder hidden" id="bodypart2Save">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="bodypart2SaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="bodypart2Input" id="bodypart2Input" class="kase injury_view input_class hidden" placeholder="Start Date" style="margin-top:-26px; margin-left:48px" />
              <span id="bodypart2Span" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:48px"></span>
            </li>
            <li id="bodypart3Grid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">End Date</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="bodypart3Save">
                <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="bodypart3SaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="bodypart3Input" id="bodypart3Input" class="kase injury_view input_class hidden" placeholder="End Date" style="margin-top:-26px; margin-left:48px" />
              <span id="bodypart3Span" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:48px"></span>
            </li>
            
              <li id="bodypart4Grid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Address</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="bodypart4Save">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="bodypart4SaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="" name="bodypart4Input" id="bodypart4Input" class="kase input_class hidden injury" style="margin-top:-26px; margin-left:48px" />
            <span id="bodypart4Span" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:48px">
            </span>
            </li>
            <li id="bodypart5Grid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Suite</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="bodypart5Save">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="bodypart5SaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <input value="" name="bodypart5Input" id="bodypart5Input" class="kase input_class hidden" style="margin-top:-26px; margin-left:48px" />
            <span id="bodypart5Span" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:48px">
            </span>
            </li>
		</ul>
        <% if (!gridster_me) { %>
			<a href="#applicant/<%= case_id %>"><img src="img/glass_add.png" width="20" height="20" border="0" /></a>
        <% } %>
    </form>
</div>
</div>

<% if (gridster_me || grid_it) { %>
<script language="javascript">
setTimeout(function() {
	gridsterById('gridster_injury');
}, 10);
</script>
<% } %>