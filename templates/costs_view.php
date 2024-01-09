<?php 
?>
<div class="gridster costs_<%=fee_type %> costs" id="gridster_costs" style="display:">
     <div style="background:url(img/glass_card_fade_2.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="costs_<%=fee_type %>_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="fee" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />
        <input id="table_id" name="table_id" type="hidden" value="-1" />
        <input id="fee_type" name="fee_type" type="hidden" value="<%=fee_type %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">    
         	<% if (fee_type=="firm_costs") { %>        
			<?php 
            $form_name = "costs"; 
            include("dashboard_view_navigation.php"); 
            ?>
            <% } %>
            <% if (fee_type=="depo_fees") { %>        
			<?php 
            $form_name = "fees"; 
            include("dashboard_view_navigation.php"); 
            ?>
            <% } %>
        </div>
        <ul> 
            <%
            var intC = 1;
            var rowCounter = 1;
            while(intC < 6) {
             %>
            <li id="cost<%=intC %>Grid" data-row="<%=rowCounter %>" data-col="1" data-sizex="1" data-sizey="5" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;"><%=intC %></div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="cost<%=intC %>Save">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="cost<%=intC %>SaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="border:red solid 0px; margin-top:-10px">
                <% if (fee_type=="depo_fees" || fee_type=="firm_costs") { %><span style="margin-left:10px; margin-top:25px; border:0px green solid">Fee:</span>&nbsp;<% } %><input name="cost<%=intC %>Input" id="cost<%=intC %>Input" class="costs_<%=fee_type %> input_class hidden cost" style="margin-top:0px; margin-left:25px; color:black" value="" />
                <span id="cost<%=intC %>Span" class="kase costs_<%=fee_type %> span_class form_span_vert" style="margin-top:0px; margin-left:40px"></span>
                <% if (fee_type=="depo_fees" || fee_type=="firm_costs") { %>
                <div>
                    <span style="margin-left:10px; margin-top:25px; border:0px green solid">Date:</span> <input name="date<%=intC %>Input" id="date<%=intC %>Input" class="costs_<%=fee_type %> date_field input_class hidden cost" style="margin-top:0px; margin-left:20px; color:black" value="" />
                    <span id="date<%=intC %>Span" class="kase costs_<%=fee_type %> span_class form_span_vert" style="margin-top:0px; margin-left:36px"></span>
                </div>
                <div>
                    <span style="margin-left:10px; margin-top:25px; border:0px green solid">Comment:</span><br /> <textarea name="comment<%=intC %>Input" id="comment<%=intC %>Input" class="costs_<%=fee_type %> input_class hidden cost" style="margin-top:0px; margin-left:10px; width:202px; color:black;" rows="5"></textarea>
                    <span id="comment<%=intC %>Span" class="kase costs_<%=fee_type %> span_class form_span_vert" style="margin-top:0px; margin-left:36px"></span>
                </div>
                
            </div>
            <div style="height:120px"></div>
            <div style="display:none">
                	<% if (fee_type=="depo_fees" || fee_type=="firm_costs") { %><span style="margin-left:10px; margin-top:25px; border:0px green solid">Assigned:</span>&nbsp;<% } %><input name="full_nameInput" id="full_nameInput" class="costs_<%=fee_type %> token input_class hidden cost" style="margin-top:0px; margin-left:15px; color:black; width:120px" value="" />
                <span id="full_nameSpan" class="kase costs_<%=fee_type %> span_class form_span_vert" style="margin-top:0px; margin-left:40px"></span>
                </div>
                <% } %>
            </li>
            <% nextC = intC+1; %>
            <li id="cost<%=nextC %>Grid" data-row="<%=rowCounter %>" data-col="2" data-sizex="1" data-sizey="5" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;"><%=nextC %></div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="cost<%=nextC %>Save">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="cost<%=nextC %>SaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            <div style="border:red solid 0px; margin-top:-10px">
                <% if (fee_type=="depo_fees" || fee_type=="firm_costs") { %>
                <span style="margin-left:10px; margin-top:25px; border:0px green solid">Fee:</span>&nbsp;<% } %><input name="cost<%=nextC %>Input" id="cost<%=nextC %>Input" class="costs_<%=fee_type %> input_class hidden cost" style="margin-top:0px; margin-left:25px; color:black" value="" />
                <span id="cost<%=nextC %>Span" class="kase costs_<%=fee_type %> span_class form_span_vert" style="margin-top:0px; margin-left:36px"></span>
                <% if (fee_type=="depo_fees" || fee_type=="firm_costs") { %>
                <div><span style="margin-left:10px; margin-top:25px; border:0px green solid">Date:</span> <input name="date<%=nextC %>Input" id="date<%=nextC %>Input" class="costs_<%=fee_type %> input_class hidden date_field cost" style="margin-top:0px; margin-left:20px; color:black" value="" />
                <span id="date<%=nextC %>Span" class="kase costs_<%=fee_type %> span_class form_span_vert" style="margin-top:0px; margin-left:36px"></span>
                </div>
                <div><span style="margin-left:10px; margin-top:25px; border:0px green solid">Comment:</span><br /> <textarea name="comment<%=nextC %>Input" id="comment<%=nextC %>Input" class="costs_<%=fee_type %> input_class hidden cost" style="margin-top:0px; margin-left:10px; width:202px; color:black" rows="5"></textarea>
                <span id="comment<%=nextC %>Span" class="kase costs_<%=fee_type %> span_class form_span_vert" style="margin-top:0px; margin-left:36px"></span>
                </div>
                <% } %>  
            </div>
            <div style="height:120px"></div>
            <div style="display:none">
                	<% if (fee_type=="depo_fees" || fee_type=="firm_costs") { %><span style="margin-left:10px; margin-top:25px; border:0px green solid">Assigned:</span>&nbsp;<% } %><input name="full_nameInput" id="full_nameInput" class="costs_<%=fee_type %> input_class hidden cost" style="margin-top:0px; margin-left:15px; color:black; width:120px" value="" />
                <span id="full_nameSpan" class="kase costs_<%=fee_type %> span_class form_span_vert" style="margin-top:0px; margin-left:40px"></span>
                </div>
            </li>
            <% 	intC = nextC + 1;
            	rowCounter++;
            } %>
		</ul>
    </form>
</div>
</div>
<div class="costs" id="all_done"></div>
<script language="javascript">
$( ".costs#all_done" ).trigger( "click" );
</script>