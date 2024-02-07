<div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:500px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
<div class="witness" id="witness_panel">
    <form id="witness_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="witness" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
    <input id="witness_id" name="witness_id" type="hidden" value="" />
    <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
		<?php 
        $form_name = "witness"; 
        include("dashboard_view_navigation.php"); 
        ?>
    </div>
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">
    
        <ul style="margin-bottom:10px">
            <li id="full_nameGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Full Name</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="full_nameSave">
                <a class="save_field" title="Click to save this field" id="full_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="full_nameInput" id="full_nameInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:55px; width:385px" parsley-error-message="Req" required />
              <span id="witness_dateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"><%= moment(witness_date).format('L') %></span>
        </li>
        
        <li id="witness_dayGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Day</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="witness_daySave">
                <a class="save_field" title="Click to save this field" id="witness_daySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= witness_day %>" name="witness_dayInput" id="witness_dayInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Day" style="margin-top:-26px; margin-left:55px" parsley-error-message="Req" required />
              <span id="witness_daySpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"><%= witness_day %></span>
        </li>
		<li id="witness_timeGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Time</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="witness_timeSave">
                <a class="save_field" title="Click to save this field" id="witness_timeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= witness_time %>" name="witness_timeInput" id="witness_timeInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Time" style="margin-top:-26px; margin-left:55px" />
              <span id="witness_timeSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"><%= witness_time %></span>
        </li>
		
        <li id="witness_locationGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Location</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="witness_locationSave">
                <a class="save_field" title="Click to save this field" id="witness_locationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="<%= witness_location %>" name="witness_locationInput" id="witness_locationInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Location" style="margin-top:-26px; margin-left:55px; width:385px" />
              <span id="witness_locationSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"><%= witness_location %></span>
        </li>
        
        
        <li id="witness_countyGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">County</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="witness_countySave">
            <a class="save_field" title="Click to save this field" id="witness_countySaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
                <input value="<%= witness_county %>" name="witness_countyInput" id="witness_countyInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:55px; width:385px" />
          <span id="witness_countySpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"><%= witness_county %></span>
        </li>
		<li id="witness_accident_descriptionGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Accident Desc.</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_logonSave">
                <a class="save_field" title="Click to save this field" id="user_logonSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <textarea name="witness_accident_descriptionInput" id="witness_accident_descriptionInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:0px; margin-left:3px; width:445px;" rows="4"><%= witness_accident_description %></textarea>
              <span id="witness_accident_descriptionSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"><%= witness_accident_description %></span>
        </li>
        <li id="witness_other_detailsGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Other Details</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_logonSave">
                <a class="save_field" title="Click to save this field" id="user_logonSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <textarea name="witness_other_detailsInput" id="witness_other_detailsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:0px; margin-left:3px; width:445px" rows="4"><%= witness_other_details %></textarea>
              <span id="witness_other_detailsSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:75px"><%= witness_other_details %></span>
        </li>
       </ul>
        <% if (gridster_me) { %>
			<a href="#users/<%= user_id %>"><img src="img/glass_add.png" width="20" height="20" border="0" /></a>
        <% } %>
    </div>
    
    </form>
</div></div>
<div id="witness_done"></div>
<script language="javascript">
$("#witness_done").trigger( "click" );
</script>