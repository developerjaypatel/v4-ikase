<?php $form_name = "slipandfall"; ?>
<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
<div style="display:inline-block" class="col-md-12">
<div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:600px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
<div class="personal_injury" id="personal_injury_panel" style="width:1350px">
    <form id="personal_injury_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="personal_injury" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
    <input id="personal_injury_id" name="personal_injury_id" type="hidden" value="" />
    <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
		<?php 
        
        include("dashboard_view_navigation.php"); 
        ?>
    </div>
    <div class="gridster accident_details" id="gridster_accident_details" style="float:right; width:750px">
    	<ul style="margin-bottom:10px">
        	<li id="personal_injury_other_detailsGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="8" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:600px">
                <h6><div class="form_label_vert" style="margin-top:10px;">Other Details</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="user_logonSave">
                    <a class="save_field" title="Click to save this field" id="user_logonSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <textarea name="personal_injury_other_detailsInput" id="personal_injury_other_detailsInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:0px; margin-left:3px; width:575px; height:650px"></textarea>
                  <span id="personal_injury_other_detailsSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-6px"></span>
            </li>
        </ul>
    </div>
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">
        <ul style="margin-bottom:10px">
            <li id="personal_injury_dateGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Date</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_dateSave">
                <a class="save_field" title="Click to save this field" id="personal_injury_dateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="personal_injury_dateInput" id="personal_injury_dateInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:55px; width:385px" parsley-error-message="Req" required tabindex="1" />
              <span id="personal_injury_dateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
        </li>
        
        <li id="personal_injury_dayGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Day</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_daySave">
                <a class="save_field" title="Click to save this field" id="personal_injury_daySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <span id="personal_injury_daySpan" class="<?php echo $form_name; ?> form_span_vert span_perm" style="margin-top:-26px; margin-left:55px; border:0px solid black""></span>
              <input type="hidden" value="" name="personal_injury_dayInput" id="personal_injury_dayInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Day" style="margin-left:55px;" />
              
        </li>
		<li id="personal_injury_timeGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Time</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_timeSave">
                <a class="save_field" title="Click to save this field" id="personal_injury_timeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <span id="personal_injury_timeSpan" class="<?php echo $form_name; ?> form_span_vert span_perm" style="margin-top:-26px; margin-left:55px; border:0px solid black"></span>
              <input type="hidden" value="" name="personal_injury_timeInput" id="personal_injury_timeInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Time" style="margin-left:55px" />
              
        </li>
		<li id="statute_limitationGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:">
          		<h6><div class="form_label_vert" style="margin-top:10px;" title="Statute of Limitation">Statute Limitation:</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="full_addressSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="full_addressSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= statute_limitation %>" name="statute_limitationInput" id="statute_limitationInput" class="kase input_class hidden injury" style="margin-top:-26px; margin-left:120px; width:100px;z-index:3259; width:119px"  tabindex="6" />
                <span id="statute_limitationSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:120px">
                <%= statute_limitation %>
                <% statute_interval = Number(statute_interval); %>
                </span>
                <select name="statute_intervalInput" id="statute_intervalInput" class="modalInput task input_class hidden injury" style="height:25px; width:150px; margin-top:-26px; margin-left:290px"  tabindex="7">
                    <option value="1" <% if (statute_interval==1) { %>selected<% } %>>Expires in 1 year</option>
                    <option value="2" <% if (statute_years=="" || statute_years==2) { %>selected<% } %>>2 years</option>
                    <option value="3" <% if (statute_years==3) { %>selected<% } %>>3 years</option>
                    <option value="4" <% if (statute_years==4) { %>selected<% } %>>4 years</option>
                    <option value="5" <% if (statute_years==5) { %>selected<% } %>>5 years</option>
                    <option value="6" <% if (statute_years==6) { %>selected<% } %>>6 years</option>
                    <option value="7" <% if (statute_years==7) { %>selected<% } %>>7 years</option>
                    <option value="8" <% if (statute_years==8) { %>selected<% } %>>8 years</option>
                    <option value="9" <% if (statute_years==9) { %>selected<% } %>>9 years</option>
                    <option value="10" <% if (statute_years==10) { %>selected<% } %>>10 years</option>
                    <option value="11" <% if (statute_years>10) { %>selected<% } %>>10+ years</option>
                    <option value="-99" <% if (statute_years==-99) { %>selected<% } %>>No Limit</option>
                </select>
                <span id="statute_intervalSpan" class="kase injury_view span_class form_span_vert" style="margin-top:-28px; margin-left:290px">
                <%= statute_years %> year<% if (statute_years > 1) { %>s<% } %>
                </span>
          </li>
        <li id="personal_injury_locationGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Location</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_locationSave">
                <a class="save_field" title="Click to save this field" id="personal_injury_locationSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="personal_injury_locationInput" id="personal_injury_locationInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Location" style="margin-top:-26px; margin-left:55px; width:385px" tabindex="2" />
              <span id="personal_injury_locationSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
        </li>
        
        
        <li id="personal_injury_countyGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
        <h6><div class="form_label_vert" style="margin-top:10px;">County</div></h6>
        <div style="float:right; margin-right:5px" class="hidden" id="personal_injury_countySave">
            <a class="save_field" title="Click to save this field" id="personal_injury_countySaveLink">
                <i class="glyphicon glyphicon-save"></i>
            </a>
        </div>
                <input value="" name="personal_injury_countyInput" id="personal_injury_countyInput" class="<?php echo $form_name; ?> input_class hidden" type="text" style="margin-top:-26px; margin-left:55px; width:385px" tabindex="3" />
          <span id="personal_injury_countySpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
        </li>
        <li id="personal_injury_accident_descriptionGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="10" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Accident Desc.</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="user_logonSave">
                <a class="save_field" title="Click to save this field" id="user_logonSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <textarea name="personal_injury_accident_descriptionInput" id="personal_injury_accident_descriptionInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:0px; margin-left:3px; width:520px; height:430px" tabindex="4"></textarea>
              <span id="personal_injury_accident_descriptionSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-6px;"></span>
        </li>
       </ul>
        
    </div>
    
    
    </form>
</div>
</div>
<div>&nbsp;</div>
<div style="background:url(../img/glass_card_fade_14.png); padding:5px; border:0px solid white; width:480px; float:left" id="gridster_accident" class="gridster">
    	<form id="personal_injury_slipandfall_form">
        <input id="table_name" name="table_name" type="hidden" value="personal_injury_slipandfall" />
        <span style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;">
            <span id="panel_title" style="color: rgb(255, 255, 255);">
                Store
            </span>
        </span>
        <ul>
            <li id="store_numberGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident_view gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Number</div></h6>
            <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="store_numberSave">
                
                <a class="save_field" style="margin-top:0px;" title="Click to save this field" id="store_numberSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="store_numberInput" id="store_numberInput" class="kase <?php echo $form_name; ?> accident_view input_class hidden" style="width:150px; margin-top:-25px; margin-left:55px" tabindex="0" />
              <span id="store_numberSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-28px; margin-left:55px"></span>
            </li>
          <li id="store_nameGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="<?php echo $form_name; ?> accident gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Name</div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="store_nameSave">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="store_nameSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="store_nameInput" id="store_nameInput" class="<?php echo $form_name; ?> kase accident_view input_class hidden" placeholder="" style="margin-top:-25px; margin-left:55px; width:150px; border:1px solid red" autocomplete="off" tabindex="1" />
              <span id="store_nameSpan" class="<?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-25px; margin-left:55px"></span>
            </li>
        </ul>
        </form>
    </div>
<div id="personal_injury_slipandfall_done"></div>
<script language="javascript">
$("#personal_injury_slipandfall_done").trigger( "click" );
</script>
</div>
