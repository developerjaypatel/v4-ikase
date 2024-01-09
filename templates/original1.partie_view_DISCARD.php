<div class="gridster partie <%=partie %>" id="gridster_tall" style="display:">
     <div style="background:url(img/glass_<%=glass %>.png) left top no-repeat; padding:5px; width:470px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
        <form id="partie_form" parsley-validate>
            <input id="id" name="id" type="hidden" value="<%= id %>" />
            <input id="uuid" name="uuid" type="hidden" value="<%= uuid %>" />
            <input id="case_id" name="case_id" type="hidden" value="<%= case_id %>" />
            <input id="case_uuid" name="case_uuid" type="hidden" value="<%= case_uuid %>" />
            <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
                <?php 
                //$form_name = "partie"; 
                //include("dashboard_view_navigation.php"); 
                ?>
                <div id="sub_category_holder_<%=partie %>" class="partie <%=partie %>" style="text-align:left; padding-bottom:5px;">
                    <span style="text-align:left;">
                        <span style="color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:5px;">
                        <%=partie %>&nbsp;<img src="img/loading_spinner_1.gif" name="gifsave" width="20" height="20" id="gifsave" style="display:none; opacity:50%" /> &nbsp; 
                       <span class="alert alert-success" style="display:none; height:25px; width:50px;font-size:14px; z-index:4251; margin-top:-35px; margin-left:-10px;">Saved</span></span>
                       <div style="float:right;">
                           <span class="edit_row <%=partie %>" style="display:inline-block; z-index:6234; margin-left:25px; margin-top:-10px"><button id="partie_edit" class="edit <%=partie %> btn btn-transparent border-blue"><i class="glyphicon glyphicon-edit">&nbsp;</i>Edit</button>
                           </span>
                           <span class="button_row <%=partie %> hidden" style="display:inline-block; margin-left:25px; margin-top:-10px">
                                <button class="btn btn-transparent border-red delete" style="color:white"><i class="glyphicon glyphicon-remove-sign">&nbsp;</i>Delete</button>&nbsp;<button class="save btn btn-transparent border-green"><i class="glyphicon glyphicon-save">&nbsp;</i>Save</button>&nbsp;<button class="reset btn btn-transparent border-white"><i class="glyphicon glyphicon-repeat">&nbsp;</i>Reset</button>
                           </span>
                       </div>
                    </span>   
                </div>
            </div>
            <ul>
                <li id="company_nameGrid" data-row="1" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Company</div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="company_nameSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="company_nameSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="<%= company_name %>" name="company_nameInput" id="company_nameInput" class="kase partie <%=partie %> input_class hidden" placeholder="First Name" style="margin-top:-26px; margin-left:60px" />
                  <span id="company_nameSpan" class="kase partie <%=partie %> span_class form_span_vert" style="margin-top:-30px; margin-left:60px"><%= company_name %></span>
                </li>
                <li id="addressGrid" data-row="2" data-col="1" data-sizex="2" data-sizey="1" class="gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Address</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-23px" class="hidden" id="addressSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="addressSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= address_1 %>" name="addressInput" id="addressInput" class="kase partie <%=partie %> input_class hidden" style="margin-top:-26px; margin-left:60px" />
                <span id="addressSpan" class="kase partie <%=partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:60px">
                <%= address_1 %>
                </span>
                </li>
                <%
                var employee_title = "Employee";
                if (partie=="Carrier") {
                    employee_title = "Examiner";
                }
                if (partie=="Defense Atty") {
                    employee_title = partie;
                }
                %>
                <li id="full_nameGrid" data-row="4" data-col="1" data-sizex="2" data-sizey="1" class="kai gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;"><%=employee_title %></div></h6>
                <div style="margin-top:-12px" class="save_holder hidden" id="full_nameSave">
                    <a class="save_field" style="margin-top:0px" title="Click to save this field" id="full_nameSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                  <input value="<%= first_name + " " + last_name %>" name="full_nameInput" id="full_nameInput" class="kase partie <%=partie %> input_class hidden" placeholder="Full Name" style="margin-top:-26px; margin-left:60px" />
                  <span id="full_nameSpan" class="kase partie <%=partie %> span_class form_span_vert" style="margin-top:-26px; margin-left:60px"><%= first_name + " " + last_name %></span>
                </li>
                <li id="phoneGrid" data-row="3" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Phone</div></h6>
                    <div style="margin-top:-23px" class="save_holder hidden" id="phoneSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="phoneSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input value="<%= office_phone %>" name="phoneInput" id="phoneInput" class="kase partie <%=partie %> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px"  />
                    <span id="phoneSpan" class="kase partie <%=partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= office_phone %></span>
                </li>
                <li id="faxGrid" data-row="3" data-col="2" data-sizex="1" data-sizey="1" class="gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Fax</div></h6>
                    <div style="margin-top:-23px" class="save_holder hidden" id="faxSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="faxSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input value="" name="faxInput" id="faxInput" class="kase partie <%=partie %> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px"  />
                    <span id="faxSpan" class="kase partie <%=partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:60px"></span>
              </li>
              <li id="phoneGrid" data-row="4" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                    <h6><div class="form_label_vert" style="margin-top:10px;">Phone</div></h6>
                    <div style="margin-top:-23px" class="save_holder hidden" id="phoneSave">
                        <a class="save_field" style="margin-top:0px" title="Click to save this field" id="phoneSaveLink">
                            <i class="glyphicon glyphicon-save"></i>
                        </a>
                    </div>
                    <input value="<%= office_phone %>" name="phoneInput" id="phoneInput" class="kase partie <%=partie %> input_class hidden" placeholder="" style="margin-top:-26px; margin-left:60px"  />
                    <span id="phoneSpan" class="kase partie <%=partie %> span_class form_span_vert" style="margin-top:-28px; margin-left:60px"><%= office_phone %></span>
                </li>
                <li id="office_emailGrid" data-row="4" data-col="2" data-sizex="1" data-sizey="1" class="kai gridster_border gridster_holder" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF">
                <h6><div class="form_label_vert" style="margin-top:10px;">Email</div></h6>
                <div style="float:right; margin-right:0px; margin-top:-16px" class="hidden" id="office_emailSave">
                    <a class="save_field" style="margin:0px;" title="Click to save this field" id="office_emailSaveLink">
                        <i class="glyphicon glyphicon-save"></i>
                    </a>
                </div>
                <input value="<%= office_email %>" name="office_emailInput" id="office_emailInput" style="margin-top:-26px; margin-left:60px" class="kase input_class hidden" />
                <span id="office_emailSpan" class="kase partie <%=partie %> span_class form_span_vert" style="margin-top:-25px; margin-left:60px">
                <%= office_email %>
                </span>
                </li>
                <li data-row="5" data-col="2" data-sizex="1" data-sizey="1" class="" style="background:url(img/glass_add.png) left top; -moz-border-radius: 10px; -webkit-border-radius: 10px; -khtml-border-radius: 10px; border-radius: 10px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; width:4.5%; height:20px">
                </li>
          </ul>
        </form>
    </div>
</div>