<?php 
include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$query = "SELECT * FROM `cse_bodyparts` 
WHERE 1
ORDER BY code ASC";

$result = mysql_query($query, $link) or die("unable to get codes<br />" . $query . "<br />" . mysql_error());
$numbs = mysql_numrows($result);
$body_options = '<option value="">Select from List</option>';
for ($int=0;$int<$numbs;$int++) {
	$bodyparts_id = mysql_result($result, $int, "bodyparts_id");
	$bodyparts_uuid = mysql_result($result, $int, "bodyparts_uuid");
	$code = mysql_result($result, $int, "code");
	$description = mysql_result($result, $int, "description");
	$option = '<option value="' . $bodyparts_uuid . '">' . $code . ' - ' . $description . '</option>';
	$body_options .= $option;
}
?>

<% var body_options = '<?php echo $body_options; ?>'; %>
<div class="gridster bodyparts_view bodyparts" id="gridster_bodyparts" style="display:">
     <div style="background:url(img/glass_card_fade_2.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="bodyparts_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="bodyparts" />
        <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%= injury_id %>" />
        <input id="table_id" name="table_id" type="hidden" value="-1" />
        <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 

            $form_name = "bodyparts"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <%  
	var bodypartsbox = "";
        var code = [];
        var description = [];
        var uuid = [];
        var bodyparts_status = [];
        var bodyparts_checked = [];
        _.each( bodyparts, function(bodypart) {
        	code[bodypart.bodyparts_number] = bodypart.code;
            description[bodypart.bodyparts_number] = bodypart.description.split(" - ")[0];
            uuid[bodypart.bodyparts_number] = bodypart.bodyparts_uuid;
            bodyparts_status[bodypart.bodyparts_number] = "<span title='Click to change Body Part Status' id='bodyparts_status_" + bodypart.injury_bodyparts_id + "' style='font-size: 1.4em; cursor:pointer;' class='bodyparts_status'>" + bodypart.bodyparts_status + "</span>";
            bodyparts_checked[bodypart.bodyparts_number] = bodypart.checked;
	    bodypartsbox = bodypart.bodyparts;
        });
        %>
        
        <ul> 
            <%
            var intC = 1;
            while(intC < 6) { %>
            <li id="bodypart<%=intC %>Grid" data-row="<%=intC %>" data-col="1" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <% if (typeof bodyparts_status[intC] != "undefined") { %>
            <div style="float:right;">
            	<%=bodyparts_status[intC] %>
            </div>
            <% } %>
            <h6><div class="form_label_vert" style="margin-top:10px;"><%=intC %></div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="bodypart<%=intC %>Save">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="bodypart<%=intC %>SaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
            
              <select name="bodypart<%=intC %>Input" id="bodypart<%=intC %>Input" class="bodyparts_view input_class hidden bodypart" onchange="checkBodyDoubles(this);" style="margin-top:-26px; margin-left:12px; color:black">
              <% 
              var select_options = body_options;
              if (typeof uuid[intC] != "undefined") {
              	select_options = select_options.replace('value="' + uuid[intC] + '"',  'value="' + uuid[intC] + '" selected="selected"');
              }
              %>
              <%= select_options %>
            </select>
            	<%  if (typeof description[intC] == "undefined") {
                	description[intC] = "";
                }
                %>
              <span id="bodypart<%=intC %>Span" class="kase bodyparts_view span_class form_span_vert" style="margin-top:-30px; margin-left:12px" title="<%=description[intC] %>">
              <% if (typeof code[intC] != "undefined") {
              	if (description[intC].length > 55) {
                	description[intC] = description[intC].substring(0, 55) + "...";
                } %>
              	<%= code[intC] %>&nbsp;&#8212;&nbsp;<span style="font-size:0.8em; line-height:80%"><%= description[intC] %></span>
              <% } %>
              </span>
            </li>
            <li id="bodypart<%=(intC+5) %>Grid" data-row="1" data-col="2" data-sizex="1" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
            <h6><div class="form_label_vert" style="margin-top:10px;"><%=(intC+5) %></div></h6>
            <div style="margin-top:-12px" class="save_holder hidden" id="bodypart<%=(intC+5) %>Save">
                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="bodypart<%=(intC+5) %>SaveLink">
                    <i class="glyphicon glyphicon-save"></i>
                </a>
            </div>
              <select name="bodypart<%=(intC+5) %>Input" id="bodypart<%=(intC+5) %>Input" class="kase bodyparts_view input_class hidden bodypart" onchange="checkBodyDoubles(this);" style="margin-top:-26px; margin-left:12px">
              <% var select_options = body_options;
              if (typeof uuid[intC+5] != "undefined") {
              	select_options = select_options.replace('value="' + uuid[intC+5] + '"',  'value="' + uuid[intC+5] + '" selected="selected"');
              }
              %>
              <%= select_options %>
            </select>
              <span id="bodypart<%=(intC+5) %>Span" class="kase bodyparts_view span_class form_span_vert" style="margin-top:-30px; margin-left:12px">
              <% if (typeof code[intC + 5] != "undefined") { %>
              	<%= code[intC + 5] %>&nbsp;&#8212;&nbsp;<span style="font-size:0.8em; line-height:80%"><%= description[intC + 5] %></span>
              <% } %>
              </span>
            </li>
            <% intC++;
            } %>
            <% 
                if(case_id == case_id) {
				  
                %>
                <!-- <li id="emptyGrid" data-row="5" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; visibility:hidden">
                </li>
                <li id="empty2Grid" data-row="6" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; visibility:hidden">
                </li> -->
                <% } %>

                <?php
			//$ids = location.hash;
				//die($ids);
				//echo "<%=$_SESSION['user_customer_id']%>";
				//$my_uri = $_SERVER['REQUEST_URI']; 
				//die("here: " . $_POST['case_id']);
				//$case_id = $_COOKIE["case_id"];
				//$injury_id = $_COOKIE["injury_id"];
				//$customer_id = $_COOKIE["customer_id"];

				$querybox = "SELECT * FROM `ikase_alvandi`.`cse_injury_bodypartsbox` Where `case_id` = '" . case_id . "' and `injury_id` = '" . injury_id . "' AND `customer_id` = '" .customer_id. "' AND `deleted` = 'N' order by `injury_bodyparts_box_id` DESC limit 0,1";
				//die($querybox);
				$resultbox = mysql_query($querybox, $link) or die("unable to get codes box<br />" . $querybox . "<br />" . mysql_error());
				$numbsbox = mysql_numrows($resultbox);
				//die($numbsbox . " - int");
				for ($intbox=0;$intbox<$numbsbox;$intbox++) {
					$injury_bodyparts_box_id = mysql_result($resultbox, $intbox, "injury_bodyparts_box_id");
					$bodypartsbox = mysql_result($resultbox, $intbox, "bodyparts");
				}
				//die($bodypartsbox . " - text - " . $injury_bodyparts_box_id);
				?>
                <?php //if ($_SERVER['REMOTE_ADDR'] == "172.119.227.47") { ?>
                <?php //die($_SESSION['user_customer_id'] . " - id"); ?>
					<?php //if ($cus_id == "1195") { ?>
                    <% if (customer_id == "1195") { %>
                         <li id="bodyparttextGrid" data-row="6" data-col="1" data-sizex="2" data-sizey="3" class="kai gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                            <h6><div class="form_label_vert" style="margin-top:10px;color:white;">Body Parts</div></h6>
                            <div style="margin-top:-12px" class="save_holder hidden" id="bodyparttextSave">
                                <a class="save_field" style="margin-top:0px" title="Click to save this field" id="bodyparttextSaveLink">
                                    <i class="glyphicon glyphicon-save"></i>
                                </a>
                            </div>
                            <% if (bodypartsbox == null) { bodypartsbox = ""; } %>
                            <textarea name="bodyparttextInput" id="bodyparttextInput" style="width:450px; height:145px; display:; margin-top:5px" class="kase bodyparts_view input_class hidden bodypart"><%=bodypartsbox%></textarea>
                              <span id="bodyparttextSpan" class="kase bodyparts_view span_class form_span_vert" style="margin-top:0px; margin-left:12px">
                              <!-- <?php //if ($bodypartsbox != "") { echo $bodypartsbox; } ?> -->
                              <%=bodypartsbox%>
                              </span>
                         </li>
                     <% } %>
                 <?php //} ?>
	         <?php //} ?>
		</ul>
    </form>
</div>
</div>
<div class="bodyparts" id="all_done"></div>
<script language="javascript">

$( ".bodyparts#all_done" ).trigger( "click" );
</script>