<?php $form_name = "lostincome"; ?>

<div style="background:none; padding:5px; width:582px;-webkit-box-shadow: none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
	<div style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px; display:none" id="section_title_<%=corporation_id %>">Employment Details</div>
    <div class="lostincome" id="lostincome_panel">
        <form id="lostincome_form" parsley-validate>
        <button class="save hidden" style="width:20px; border:0px solid; display:none"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></button>
        <input id="table_name" name="table_name" type="hidden" value="lostincome" />
        <input name="case_id" type="hidden" value="<%=current_case_id %>" />
        <input id="lostincome_id" name="lostincome_id" type="hidden" value="<%=id %>" />
        <input id="corporation_id" name="corporation_id" type="hidden" value="<%=corporation_id %>" />
        <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">  
            <ul style="margin-bottom:10px">
                <li id="start_lost_dateGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border lostincome" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -webkit-box-shadow: none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Start Date</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="start_lost_dateSave">
                        <a class="save_field" title="Click to save this field" id="start_lost_dateSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input type="date" value="<%=start_lost_date %>" name="start_lost_dateInput" id="start_lost_dateInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:75px; width:174px" />
                      <span id="start_lost_dateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:85px"></span>
                </li>
                <li id="end_lost_dateGrid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border lostincome" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -webkit-box-shadow: none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">End Date</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="end_lost_dateSave">
                        <a class="save_field" title="Click to save this field" id="end_lost_dateSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input type="date" value="<%=end_lost_date %>" name="end_lost_dateInput" id="end_lost_dateInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:75px; width:174px" />
                      <span id="end_lost_dateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
                </li>
                
                <li id="wageGrid" data-row="2" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border lostincome" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -webkit-box-shadow: none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Rate</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="wageSave">
                        <a class="save_field" title="Click to save this field" id="wageSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input type="number" min="0.00" step="0.01" value="<%=wage %>" name="wageInput" id="wageInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:75px; width:174px" />
                      <span id="wageSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:85px"></span>
                </li>
                <li id="perGrid" data-row="2" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border lostincome" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -webkit-box-shadow: none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Pay Interval</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="perSave">
                        <a class="save_field" title="Click to save this field" id="perSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                      <select name="perInput" id="perInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:75px; width:174px">
                      	<option value="" <% if (per=="") { %>selected<% } %>>Select from List ...</option>
                        <option value="H" <% if (per=="H") { %>selected<% } %>>Hourly</option>
                        <option value="D" <% if (per=="D") { %>selected<% } %>>Daily</option>
                        <option value="W" <% if (per=="W") { %>selected<% } %>>Weekly</option>
                        <option value="M" <% if (per=="M") { %>selected<% } %>>Monthly</option>
                        <option value="Y" <% if (per=="Y") { %>selected<% } %>>Yearly</option>
                      </select>
                      <span id="perSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
                </li>
                
                <li id="amountGrid" data-row="3" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border lostincome" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -webkit-box-shadow: none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Lost Wages</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="amountSave">
                        <a class="save_field" title="Click to save this field" id="amountSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input type="number" min="0.00" step="0.01" value="<%=amount %>" name="amountInput" id="amountInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:75px; width:174px" />
                      <span id="amountSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:85px"></span>
                </li>
                <li id="commentsGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="2" class="gridster_border lostincome" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -webkit-box-shadow: none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF;">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Comments</div></h6>
                    <div style="float:right; margin-right:5px" class="hidden" id="perSave">
                        <a class="save_field" title="Click to save this field" id="perSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <textarea name="commentsInput" id="commentsInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-top:-26px; margin-left:75px; width:374px"><%=comments %></textarea>
                      <span id="commentsSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:55px"></span>
                </li>
           </ul>
        </div>
        </form>
    </div>
</div>
<div id="lostincome_done"></div>
<script language="javascript">
$("#lostincome_done").trigger( "click" );
</script>