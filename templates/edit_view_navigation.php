<div id="sub_category_holder<? if ($form_name=="applicant") { echo "_" . $form_name; } ?>" class="<?php echo $form_name; ?>" style="text-align:left; margin-top:13px;">
	<span style="text-align:left;">
    	<span style="color:#FFFFFF; font-size:1.6em; font-weight:lighter; margin-left:10px;">
            <% if (!isNaN(id) && id!=-1) { %>Edit<% } else { %>New<% } %> <?php if ($form_name=="parties") { ?><%= type.capitalize() %><?php } else { 
				$display_name = ucwords($form_name); 
				if ($form_name=="notes") {
					$display_name = "Note";
				}
				echo $display_name;
			} ?>&nbsp;<img src="img/loading_spinner_1.gif" name="gifsave" width="20" height="20" id="gifsave" style="display:none; opacity:50%" /> &nbsp; 
           <span class="<?php echo $form_name; ?> alert alert-success" style="display:none; height:25px; width:50px;font-size:14px; z-index:4251; margin-top:-35px; margin-left:-10px;">Saved</span>
           <span class="<?php echo $form_name; ?> alert alert-warning" style="display:none; height:25px; width:50px;font-size:14px; z-index:4251; margin-top:-35px; margin-left:-10px;">Warning</span>
       </span>
       
       <div class="<?php echo $form_name; ?> alert alert-warning" style="display:none">
        <h4 class="<?php echo $form_name; ?> alert-heading">Warning</h4>
        <span class="<?php echo $form_name; ?> alert-text"></span>
    </div>
       
        <span class="edit_row <?php echo $form_name; ?>" style="display:inline-block; z-index:6234; margin-left:15px; margin-top:-10px"><button id="<?php echo $form_name; ?>_edit_button" style="border:0px solid; width:20px" class="edit btn btn-transparent border-blue"><i class="glyphicon glyphicon-edit" style="color:#0033FF">&nbsp;</i></button>
        </span>
        <span class="button_row <?php echo $form_name; ?> hidden" style="display:inline-block; margin-left:15px">
            <button class="reset btn btn-transparent border-white" style="border:0px solid; width:20px"><i class="glyphicon glyphicon-repeat">&nbsp;</i></button>&nbsp;<button class="save btn btn-transparent border-green" style="border:0px solid; width:20px"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></button>&nbsp;<button class="btn btn-transparent border-red delete" style="color:white; border:0px solid; width:20px"><i class="glyphicon glyphicon-trash" style="color:#FF0000">&nbsp;</i></button>&nbsp;
        </span>
        <?php if ($form_name == "notes") {?>
        <a title="Click for new note" id="new_note" href='#notes/<%=case_id %>/-1/new' style="color:#FFFFFF; text-decoration:none; margin-left:10px">
            <button class="btn btn-transparent" style="color:white; border:0px solid; width:20px">
                <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
            </button>
        </a>
        <?php } ?>
    </span>   
</div>