
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
    
<div>
	<div class="glass_header">
    	<input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <div style="display:inline-block; text-align:left; vertical-align:top"><span style="font-size:1.2em; color:#FFFFFF">vServices</span></div>
        
    </div>
    <div class="gridster" id="gridster_vservices_cards" style="padding-top:5px">
      	<ul>
        <% 
        var row_counter = 1;
        var column_counter = 1;
        var column_max = 4;
        if (window.innerWidth > 1090) {
        	column_max = 5;
        }
        _.each( vservices, function(vservice) {
        %>
        	<li id="vservice_nameGrid_<%= vservice.vservice_id %>" data-row="<%=row_counter %>" data-col="<%=column_counter %>" data-sizex="1" data-sizey="1" class="vservice gridster_border gridster_holder" style="background:url(img/<%=vservice.backcolor %>) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-left:0px">
				<div style="float:right; padding:3px">
                	<%=vservice.brochure %>
                </div>			
                <div style="font-weight:bold; font-size:1.3em; padding-bottom:10px"><%=vservice.name.toUpperCase() %></div>
                <%=vservice.description %>
                <div style="float:right">
                	
                    <%= vservice.dois %>
                </div>
                <%=vservice.phone %>
                <%=vservice.fax %>
                <%=vservice.email %>
                <%=vservice.company_site %>
                
                <%=vservice.company_name %>
            </li>
        <%	   	
		column_counter = 1 + column_counter;
       }); 
       %>
		</ul>
	</div>
</div>
<div id="vservices_all_done"></div>
<script language="javascript">
$( "#vservices_all_done" ).trigger( "click" );
</script>