<?php $form_name = "new_legal"; ?>

<div class="glass_header">
            <div style="float:right">
            <a style="background:#CFF; color:black; padding:2px; cursor:pointer" id="search_qme" title="Click to search the EAMS database of QME Medical Provider">Import QMEs from EAMS</a>
            </div>
            <input id="case_id" name="case_id" type="hidden" value="<%=this.model.get('case_id') %>" />
        	<input id="case_uuid" name="case_uuid" type="hidden" value="<%=this.model.get('uuid') %>" />
    
            
            <div style="display:inline-block">
                <div style="border:0px solid green; text-align:left">
                    
                    <div class="white_text" style="display:inline-block; padding-left:5px">
                        <div style="float:right; display:none">
                            <span class='black_text'>&nbsp;|&nbsp;</span>
                        </div>
                        <span id="case_number_fill_in"></span><span id="adj_slot"><% if (this.model.get("adj_number")!="") { %><span class='black_text'>&nbsp;|&nbsp;</span><span id="adj_number_fill_in"></span><% } %></span><span class='black_text'>&nbsp;|&nbsp;</span><span id="case_type_fill_in"></span><span class='black_text'>&nbsp;|&nbsp;</span>Case&nbsp;Date:&nbsp;<span id="case_date_fill_in"></span><span class='black_text'>&nbsp;|&nbsp;</span>Claim&nbsp;#:&nbsp;<span id="claim_number_fill_in"></span><span id="claims_slot"><span class='black_text'>&nbsp;|&nbsp;</span>Claims&nbsp;:&nbsp;<span id="claims_fill_in"></span></span>
                        <br />
                        Status:&nbsp;<span id="case_status_fill_in"></span><% if (this.model.get("case_substatus")!="") { %><span class='white_text'>&nbsp;/&nbsp;</span><span class='white_text'><span id="case_substatus_fill_in"></span></span><% } %><% if (this.model.get("rating")!="") { %><span class='black_text'>&nbsp;|&nbsp;</span>Rating:&nbsp;<span id="rating_fill_in"></span><% } %><span id="language_slot"><% if (this.model.get("interpreter_needed")!="N") { %><span class='black_text'>&nbsp;|&nbsp;</span><span class="red_text white_background">Interpreter&nbsp;Needed&nbsp;for&nbsp;<span id="language_fill_in"></span></span><% } %></span>
                    </div>
                </div> 
            </div>
        </div>
        <br/>

<div style="display:inline-block" class="col-md-12">
<div style="float:right" class="col-md-6" id="coa_listing_holder"></div>
<div style="background:url(img/glass_<%=glass %>.png) left top repeat-y; padding:5px; width:500px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
<div class="new_legal" id="new_legal_panel">
    <form id="new_legal_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="new_legal" />
    <input id="table_id" name="table_id" type="hidden" value="<%= id %>" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="<%= uuid %>" />
    <input id="new_legal_id" name="new_legal_id" type="hidden" value="" />
    <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
		<?php 
        include("dashboard_view_navigation.php"); 
        ?>
    </div>
    <div class="gridster <?php echo $form_name; ?>" id="gridster_<?php echo $form_name; ?>" style="">
    
        <ul style="margin-bottom:10px">
            <li id="case_typeGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Case Type</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="case_typedateSave">
                    <a class="save_field" title="Click to save this field" id="case_typeSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <span id="case_typeSpan" class="kase <?php echo $form_name; ?> span_perm form_span_vert" style="margin-top:-26px; margin-left:70px"></span>
                  
                   <input type="hidden" value="" name="case_typeInput" id="case_typeInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px; width:385px" />
            </li>
            <li id="file_numberGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">File Number</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="file_numberdateSave">
                <a class="save_field" title="Click to save this field" id="file_numberSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <span id="file_numberSpan" class="kase <?php echo $form_name; ?> span_perm form_span_vert" style="margin-top:-26px; margin-left:70px"></span>
              
               <input type="hidden" value="" name="file_numberInput" id="file_numberInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px; width:385px" />
            </li>
            <li id="filing_dateGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Filing Date</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="filing_dateSave">
                    <a class="save_field" title="Click to save this field" id="filing_dateSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <span id="filing_dateSpan" class="kase <?php echo $form_name; ?> span_perm form_span_vert" style="margin-top:-26px; margin-left:70px"></span>
                  
                   <input type="hidden" value="" name="filing_dateInput" id="filing_dateInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px; width:385px" />
            </li>
            <li id="overideGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Overide</div></h6>
                <div style="float:right; margin-right:5px" class="hidden" id="overideSave">
                    <a class="save_field" title="Click to save this field" id="overideSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <span id="overideSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:70px"></span>
                  
                   <input type="text" value="" name="overideInput" id="overideInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px; width:385px" />
            </li>
            <li id="new_legal_dateGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">DOI</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="new_legal_dateSave">
                <a class="save_field" title="Click to save this field" id="new_legal_dateSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <input value="" name="new_legal_dateInput" id="new_legal_dateInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px; width:385px" />
              <span id="new_legal_dateSpan" class="kase <?php echo $form_name; ?> span_class form_span_vert" style="margin-top:-26px; margin-left:70px"></span>
        </li>
        
        <li id="new_legal_dayGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:none">
            <h6><div class="form_label_vert" style="margin-top:10px;">Day</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="new_legal_daySave">
                <a class="save_field" title="Click to save this field" id="new_legal_daySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <span id="new_legal_daySpan" class="<?php echo $form_name; ?> form_span_vert span_perm" style="margin-top:-26px; margin-left:70px; border:0px solid black""></span>
              <input type="hidden" value="" name="new_legal_dayInput" id="new_legal_dayInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Day" style="margin-left:70px;" />
              
        </li>
		<li id="new_legal_timeGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:none">
            <h6><div class="form_label_vert" style="margin-top:10px;">Time</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="new_legal_timeSave">
                <a class="save_field" title="Click to save this field" id="new_legal_timeSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <span id="new_legal_timeSpan" class="<?php echo $form_name; ?> form_span_vert span_perm" style="margin-top:-26px; margin-left:70px; border:0px solid black"></span>
              <input type="hidden" value="" name="new_legal_timeInput" id="new_legal_timeInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="Time" style="margin-left:70px" />
              
        </li>
        <li id="new_legal_countyGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">County</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="new_legal_countySave">
                <a class="save_field" title="Click to save this field" id="new_legal_countySaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <span id="new_legal_countySpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:70px; border:0px solid black"></span>
              <input type="text" value="" name="new_legal_countyInput" id="new_legal_countyInput" class="<?php echo $form_name; ?> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:70px; width:385px" />
              
        </li>
        <li id="new_legal_factsGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="3" class="gridster_border kase" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;">Facts</div></h6>
            <div style="float:right; margin-right:5px" class="hidden" id="new_legal_factsSave">
                <a class="save_field" title="Click to save this field" id="new_legal_factsSaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <span id="new_legal_factsSpan" class="<?php echo $form_name; ?> form_span_vert span_class" style="margin-top:-26px; margin-left:70px; border:0px solid black"></span>
              <textarea rows="5" name="new_legal_factsInput" id="new_legal_factsInput" class="<?php echo $form_name; ?> input_class hidden" style="margin-left:70px; width:385px"></textarea>
              
        </li>
       </ul>
        
    </div>
    
    
    </form>
</div>
</div>

<div id="new_legal_done"></div>
<script language="javascript">
$("#new_legal_done").trigger( "click" );
</script>
</div>
