<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
<div>
	<div class="glass_header">
    	<input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <div style="display:inline-block; text-align:left; vertical-align:top"><span style="font-size:1.2em; color:#FFFFFF">Related Cases</span></div>
        <div style="display:inline-block">
        	<div style="border:0px solid green; text-align:left">
                <div style="display:inline-block; vertical-align:top; text-align:left; border:0px solid pink; margin-left:20px">
                	<!--<a title="Click for New Related Kase" id="add_case" style="color:#FFFFFF; text-decoration:none;">
                    <button class="btn btn-transparent" style="color:white; border:0px solid; width:20px">
                        <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
                    </button>
	                </a>-->
                    <button id="add_case" class="btn btn-sm btn-primary" title="Click for New Related Kase" style="margin-top:-5px">New Related Kase</button> 
                </div>
                <div class="white_text" style="display:inline-block; padding-left:5px">
                </div>
            </div> 
        </div>
    </div>
      <div class="gridster" id="gridster_related_cases">
      	<ul>
        <% 
        var row_counter = 1;
        var column_counter = 1;
        var column_max = 4;
        _.each( injuries, function(injury) {
            var glass = "";
            switch(injury.main_case_id) {
            	case injury.case_id:
                	glass = "_card_fade";
                    break;
                case injury.main_case_id:
                	glass = "_card_fade_1";
                    break;
            }
            /*
            if (injury.case_number=="" && injury.file_number!="") {
                injury.case_number = injury.file_number;
            }
            */
            var case_number = injury.case_number;
            var case_employer = injury.employer;
         %>
        	<li id="relatedGrid_<%=injury.injury_id %>" data-row="<%=row_counter %>" data-col="<%=column_counter %>" data-sizex="1" data-sizey="1" class="partie gridster_border gridster_holder" style="background:url(img/glass<%=glass %>.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-left:0px; font-size:1em">
                
                <%
                if (injury.start_date == "Invalid date" || injury.start_date == "0000-00-00") {
                    injury.start_date = "";
                }
                if (injury.start_date!="") {
                    injury.start_date = moment(injury.start_date).format('MM/DD/YYYY');
                }
                if (injury.end_date == "Invalid date" || injury.end_date == "0000-00-00") {
                    injury.end_date = "";
                }
                if (injury.end_date!="") {
                    injury.end_date = moment(injury.end_date).format('MM/DD/YYYY');
                }
                %>
                <div style="float:right">
                	<a href="#/injury/<%=injury.main_case_id %>/<%=injury.injury_id %>" class="white_text" title="Click here to review Injury information">
                    	<strong>DOI:</strong> <%=injury.start_date %>
                        <% if (injury.end_date!='') { %>
                        &nbsp;-&nbsp;<%=injury.end_date %>&nbsp;CT
                        <% } %>
                    </a>
                    <% if (injury.main_case_id != current_case_id) { %>
                    <br />
                    <a class="unrelate white_text" id="related_<%=injury.injury_id %>" style="font-size:0.8em">unrelate</a>
                    <% } %>
                </div>
                
                <h3 style="margin-top:0px; color:white; font-size:1.5em">Case: <a href="#kases/<%=injury.main_case_id %>" class="white_text" title="Click here to review Kase information"><%=case_number %><% if (injury.injury_number != "1") { %>-<%=injury.injury_number %> <% } %></a></h3>
                <hr/>
                <div><strong>ADJ Number:</strong> <%=injury.adj_number %></div>
                <div><strong>Employer:</strong> <%=case_employer %></div><br />
                <div><strong>Carrier:</strong> <span id="carrier_related_<%=injury.injury_id %>"></span></div>
                <div><strong>Examiner:</strong> <span id="examiner_related_<%=injury.injury_id %>"></span></div><br />
                <div><strong>Bodyparts:</strong> <span id="body_parts_related_<%=injury.injury_id %>"></span></div><br />
                <div><strong>Occupation:</strong> <%=injury.occupation %></div><br />
                <div style="width:98%; overflow-y:auto; height:100px; color:#CCCCCC"><strong>Explanation:</strong> <span style="color:white"><%=injury.explanation %></span></div>
			</li>
		<% column_counter++;
        	if ((column_counter%4)==0) {
            	column_counter = 1;
            	row_counter++;
            }
        }); %>
		</ul>
	</div>
</div>
<div id="related_list_all_done"></div>
<script language="javascript">
$( "#related_list_all_done" ).trigger( "click" );
</script>