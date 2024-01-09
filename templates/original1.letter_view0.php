<form id="letter_form" class="letter" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="letter" />
    <input id="table_id" name="table_id" type="hidden" value="<%=letter_id %>" />
<table style="margin-top:0px">
    <tr>
   	  <td>
            <div id="droppable">
                <textarea name="letterInput" id="letterInput" class="jqte-test" style="width:100%" cols="80" rows="50" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <%=letter %>
				</textarea>
            </div>
		</td>
     	<td valign="top" style="padding-left:25px">
            <table width="275" border="1" align="right" cellpadding="3" cellspacing="0">
              <tr>
                <td>Field Name</td>
              </tr>
              <?php 
                $arrFields = array(
                    0=>array("field_id"=>0, "field_name"=>"Applicant"), 
                    1=>array("field_id"=>12, "field_name"=>"Employer"), 
                    2=>array("field_id"=>24, "field_name"=>"Attorney")
                );
                for ($int=0;$int<count($arrFields);$int++) { 
                    $field_id = $arrFields[$int]["field_id"];
                    $field_name = $arrFields[$int]["field_name"];
              ?>  
              <tr>
                <td>
                  <a id="drag<?php echo $field_id; ?>" draggable="true" ondragstart="drag(event)" ondragend="drag_end(event)" style="color:#FFFFFF">[[<?php echo $field_name; ?>]]</a>
                </td>
              </tr>
              <?php }	//end of for loop ?>
              <tr>
                <td><input type="text" id="focusme" /></td>
                </tr>
                <tr>
  	<td>
    	<input type='hidden' id='send_document_id' name='send_document_id' value="" />
    	<div id="letter_attachments" style="width:90%; border:#000000 1px solid"></div>    </td>
  </tr>
            </table>
		</td>
	</tr>
    
</table>    
</form>