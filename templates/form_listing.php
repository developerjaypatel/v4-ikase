<div id="confirm_delete_form" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this form?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
		<!--
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
        -->
<div>
<div>
	<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
	<div class="glass_header" style="width:100%">
    	<div style="float:right">
        	<div class="btn-group">
            
            	<label for="forms_searchList" id="label_search_forms" style="font-size:1em; cursor:text; position:relative; top:0px; left:105px; width:100px; color:#999">Search Forms</label>
            
				<input id="forms_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'form_listing', 'form')" style="height:33px; line-height:32px; margin-top:-5px">
				<a id="forms_clear_search" style="position: absolute;
				right: 2px;
				top: 0;
				bottom: 9px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
        <span style="font-size:1.2em; color:#FFFFFF">Forms</span>
            <!--
            <a title="Click to create a form" id="compose_form" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-inbox" style="color:#66FF33">&nbsp;</i></a>
            -->
    </div>
    <div id="form_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
    <table id="form_listing" class="tablesorter form_listing" border="0" cellpadding="0" cellspacing="0" style="width:100%">
        <!--
        <thead>
        <tr>
            <th style="font-size:1.5em; width:">
                Form
            </th>
        </tr>
        </thead>
        -->
        <tbody>
		<% var intCounter = 0;
        var current_cat = "";
        _.each( forms, function(eams) {
        	var the_cat = eams.category;
            if (current_cat != the_cat) {
                current_cat = the_cat;
            %>
        	<tr class="date_row row_<%= the_cat.replaceAll(" ", "") %>">
                <td colspan="2">
                    <div style="width:100%; 
	text-align:left; 
	font-size:1.8em; 
	background:#CFF; 
	color:red;"><%= eams.category.replace(" forms", "").capitalizeWords() %></div>
                </td>
            </tr>
        <% } %>
       	<tr class="form_data_row form_row_">
        
        	<td colspan="">
				<a title="Click to fill out PDF" class="create_eams" id="eamsforms_<%=case_id%>_<%= intCounter %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer;color:white"><%= eams.display_name %></a>
                <input type="hidden" id="eamsname_<%=case_id%>_<%= intCounter %>" value="<%= eams.name %>" />
			</td>
            <td>
            	<%= eams.status %>
            </td>
        </tr>
        <% 	intCounter++;
        }); %>
        </tbody>
    </table>

</div>
