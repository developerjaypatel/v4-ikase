<?php
include("../api/manage_session.php");
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

include ("../api/connection.php");

$sql = "SELECT COUNT(setting_id) the_count FROM cse_setting 
WHERE setting = 'letterhead' 
AND deleted = 'N'";
//LEFT OUTER JOIN cse_docucents ON cse_docucents.docucents_id = cse_setting.document_id
$db = getConnection();

try {
	$stmt = $db->query($sql);
	$letterhead = $stmt->fetchObject();
} catch(PDOException $e) {
	$error = array("error nav"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this letter?
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
<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
<div>
	<div class="glass_header">
	    <div style="float:right;">
        	<?php
			if ($letterhead->the_count == 0) {
				echo "<span style='background:red; color:white'>NO LETTER HEAD</span>&nbsp;|&nbsp;";
			}
			?>
        	<!--
            <a id="show_generated" class="white_text" style="cursor:pointer">Show Filled-Out Letters</a><a id="show_all" class="white_text" style="display:none; cursor:pointer">Show All</a>&nbsp;
			-->
            <div class="btn-group">
                <button id="show_generated" class="btn btn-sm btn-primary" style="width:150px; margin-top:-2px; margin-right:-120px">Show Filled-Out Letters</button>
                <button id="show_all" class="btn btn-sm btn-primary" style="width:150px; margin-top:-2px; margin-right:-120px; display:none">Show All</button>
                &nbsp;
            	 <label for="letter_searchList" id="label_search_letter" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search Letters</label>
            	
				<input id="letter_searchList" type="text" class="search-field" placeholder="" autocomplete="off">
				<a id="letter_clear_search" style="position: absolute;
				right: 2px;
				top: 0px;
				bottom: 2px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				border: 0px solid green;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
    	<div style="width:250px">
        	<span style="font-size:1.2em; color:#FFFFFF" id="document_form_title">Letters</span><span style="color:white">&nbsp;&nbsp;(<%=templates.length %>)</span>
        </div>
    </div>
    <div id="preview_panel" style="position:absolute; width:800px; display:none; z-index:2"></div>
    <div id="view_document" style="position:absolute; width:950px; height:600px; display:none; z-index:2; border:1px solid black; background:black">
        <div style="float:right"><a href="javascript:closeDocument()" title="Click to close preview" style="color:white; text-decoration:none">close</a></div>
    </div>
    <div id="upload_documents" style="border:0px solid yellow"></div>
    <table id="letter_listing" class="tablesorter letter_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
        	<th width="2%">&nbsp;</th>
            <th width="23%">Name</th>
            <th width="7%">Type</th>
            <th width="10%">Category</th>
            <th width="8%">Description</th>
            <th width="23%">Filled-out (Employee)</th>
            <th width="23%">Docucent API</th>
            <th>&nbsp;
            </th>
        </tr>
        </thead>
        <tbody>
       <% 
       var current_cat = "";
       _.each( templates, function(template) {
       		var the_cat = template.document_extension;
             if (current_cat != the_cat) {
                current_cat = the_cat;
            %>
        	<tr class="cat_row row_<%= the_cat.replaceAll(" ", "") %>">
                <td colspan="8">
                    <div style="width:100%; 
	text-align:left; 
	font-size:1.8em; 
	background:#CFF; 
	color:red;"><%= the_cat.capitalizeWords() %></div>
                </td>
            </tr>
        <% }
        template.document_filename = template.document_filename.replace("templates/", "");
       	%>
       	<tr class="letter_data_row letter_row_<%=template.document_id%>">
        	<td align="left" nowrap="nowrap">
            	<div style="float:left">
                    <div style="display:inline-block">
                	<a title="Click to compose a new letter" class="create_letter" id="compose_letter_<%=case_id%>_<%=template.document_id%>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-file" style="color:#FFFF00">&nbsp;</i></a>
                    </div>
                </div>
            </td>
            <td align="left" nowrap="nowrap" style="border:0px solid red">
            	<div style="float:right; padding-right:3px; border:0px solid green">
                <a id="thumbnail_<%=template.document_id%>" href="uploads/<?php echo $_SESSION['user_customer_id']; ?>/templates/<%= template.document_filename.replace("#", "%23") %>" target="_blank" class="list_link" style="font-size:1em">
                <i class='glyphicon glyphicon-fullscreen' style='color:white;'>&nbsp;</i>
                </a>
                </div>
            	<a title="Click to compose a new letter" class="create_letter white_text" id="compose_letter_<%=case_id%>_<%=template.document_id%>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer">
                <%= template.display_name %>
                </a>
                <input id="document_id_<%=template.document_id%>" name="document_id_<%=template.document_id%>" type="hidden" class="document_input" value="<%=template.document_id%>" />
                
            </td>
            <td align="left" nowrap="nowrap">
              <%=template.description_html %>
            </td>
            <td align="left">
            	<%=template.document_extension.replace("docx", "") %>
            </td>
            <td align="left" nowrap="nowrap">
            	<%= template.description %>
            </td>
            <td align="left" id="template_letter_<%=template.id%>" class="letter_generated_cell" nowrap>
            	<%= template.document_names %>
            </td>
            <td>
                <input type="hidden" name="caseid_<%=template.document_id%>" value="<%=case_id%>">
                <input type="hidden" name="letterpath_<%=template.document_id%>" value="uploads/<?php echo $_SESSION['user_customer_id']; ?>/templates/<%= template.document_filename.replace("#", "%23") %>">
                <input type="hidden" name="cusid_<%=template.document_id%>" value="<?=$_SESSION['user_customer_id']?>">
                <!-- Solulab code start 19-06-2019 -->
                <!-- <% 
                    if (!template.vendor_submittal_id) {
                %>
                <a title="Click to compose a new letter" ><button value="<%=template.document_id%>" onclick="docucent_upload_letter_button_click(<%=template.document_id%>)" name='lettersub'>Send to docucent</button></a>
                <% 
                    }else{
                %>
                App Created : <%=template.docucents_upload_date %>
                <a title="Click for Get POS" target='_blank' href="../docusent/getPOS.php?vendor_submittal_id=<%=template.vendor_submittal_id %>&user_customer_id=<?=$_SESSION['user_customer_id']?>">Get POS</a>
                <% 
                      }
                %> -->
                <!-- Solulab code end 19-06-2019 -->
            </td>
            <td align="right">
            	<a title="Click to compose a new letter" class="create_letter" id="compose_letter_<%=case_id%>_<%=template.document_id%>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-file" style="color:#FFFF00">&nbsp;</i></a>
            </td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>
<script>
function docucent_upload_letter_button_click(param_targetid)
{
        var targetid=param_targetid;
        $.ajax({
            url:'/api/docucents/lettersubmission',
            type: 'POST',
            data: {
                caseid: $("input[name='caseid_"+targetid+"']").val(),
                letterpath: $("input[name='letterpath_"+targetid+"']").val(),
                cusid: $("input[name='cusid_"+targetid+"']").val(),
                document_id: targetid,
                call_intension:'letter_upload',
            },
            dataType:"json",
            success: function(res) {
                alert(res);
                let name=Backbone.history.loadUrl(Backbone.history.getFragment());
               
            }
        });
// alert(targetid);
}
</script>