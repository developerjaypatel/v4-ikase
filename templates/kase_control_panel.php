<?php
include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$blnIPad = isPad();

?>

    	<div class="glass_header">
            <div style="float:right">
            <a style="background:#CFF; color:black; padding:2px; cursor:pointer" id="search_qme" title="Click to search the EAMS database of QME Medical Provider">Import QMEs from EAMS</a>
            </div>
            <input id="case_id" name="case_id" type="hidden" value="<%=this.model.get('case_id') %>" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%=this.model.get('uuid') %>" />
    
            <div style="display:inline-block; text-align:left; vertical-align:top"><span style="font-size:1.2em; color:#FFFFFF">Kontrol Panel: </span></div>
            <div style="display:inline-block">
                <div style="border:0px solid green; text-align:left">
                    
                    <div class="white_text" style="display:inline-block; padding-left:5px">
                        <div style="float:right; display:none">
                            <span class='black_text'>&nbsp;|&nbsp;</span>
                        </div>
                        <%=this.model.get('case_number') %><% if (this.model.get("adj_number")!="") { %><span class='black_text'>&nbsp;|&nbsp;</span><%=this.model.get('adj_number') %><% } %><span class='black_text'>&nbsp;|&nbsp;</span><%=this.model.get('case_type') %><span class='black_text'>&nbsp;|&nbsp;</span>Case&nbsp;Date:&nbsp;<%=this.model.get('case_date') %><span class='black_text'>&nbsp;|&nbsp;</span>Claim&nbsp;#:&nbsp;<span id="claim_number_fill_in"></span><span class='black_text'>&nbsp;|&nbsp;</span>Claims&nbsp;:&nbsp;
                        <br />
                        Status:&nbsp;<%=this.model.get('case_status') %><% if (this.model.get("case_substatus")!="") { %><span class='white_text'>&nbsp;/&nbsp;</span><span class='white_text'><%=this.model.get('case_substatus') %></span><% } %><% if (this.model.get("rating")!="") { %><span class='black_text'>&nbsp;|&nbsp;</span>Rating:&nbsp;<%=this.model.get('rating') %><% } %><% if (this.model.get("interpreter_needed")!="N") { %><span class='black_text'>&nbsp;|&nbsp;</span><span class="red_text white_background">Interpreter&nbsp;Needed&nbsp;for&nbsp;<%=this.model.get('language') %></span><% } %>
                    </div>
                </div> 
            </div>
        </div>
        <br/>
    
<div class="active fade in glass_header_no_padding" style="border:0px solid blue;">
	
    <div style="border:0px solid purple">
        <div class="container" style="width:100%; border:0px solid green; margin:0px; padding:0px">
            <div class="dashboard_home col-md-6" id="unread_messages" style="margin-top:10px; overflow-y:auto; height:300px; border:0px solid yellow">
            </div>
			<div class="dashboard_home col-md-6 span_class" id="my_tasks" style="margin-top:5px; overflow-y:auto; width:50%; height:300px; margin-left:auto; margin-right:auto; border: 0px solid green; float: right">
            </div>
        </div>
        <div class="container" style="border-top:1px solid white; width:100%; margin:0px; padding:0px">
            <div class="dashboard_home col-md-6" id="upcoming_events" style="margin-top:10px; overflow-y:auto; height:300px; border:0px solid yellow">

            </div>
            <div class="dashboard_home col-md-6" id="assigned_tasks" style="margin-top:10px; overflow-y:auto; height:300px; border-left:1px solid white">
            </div>
        </div>
    </div>
    <div>&nbsp;</div>
</div>
<div>&nbsp;</div>