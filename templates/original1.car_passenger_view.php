<div class="gridster car_passenger <%=accident_partie %>" id="gridster_car_passenger" style="display:">
     <div style="background:url(img/glass_card_dark_long_1.png) left top repeat-y; padding:5px; width:480px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <form id="car_passenger_form" parsley-validate>
    	<input id="table_name" name="table_name" type="hidden" value="car_passenger" />
        <input id="case_id" name="case_id" type="hidden" value="" />
        
        <input id="left_outside"  name="left_outside" type="hidden" value="" class="input_class" />
        <input id="hood"  name="hood" type="hidden" value="" class="input_class" /><input id="trunk"  name="trunk" type="hidden" value="" class="input_class" /><input id="rear_left"  name="rear_left" type="hidden" value="" class="input_class" /><input id="middle_left"  name="middle_left" type="hidden" value="" class="input_class" /><input id="front_left"  name="front_left" type="hidden" value="" class="input_class" /><input id="rear_middle"  name="rear_middle" type="hidden" value="" class="input_class" /><input id="middle_middle"  name="middle_middle" type="hidden" value="" class="input_class" /><input id="front_middle"  name="front_middle" type="hidden" value="" class="input_class" /><input id="rear_right"  name="rear_right" type="hidden" value="" class="input_class" /><input id="middle_right"  name="middle_right" type="hidden" value="" class="input_class" /><input id="front_right"  name="front_right" type="hidden" value="" class="input_class" /><input id="right_outside"  name="right_outside" type="hidden" value="" class="input_class" />
         <div style="margin-top:0px; margin-right:10px; padding-top:5px">            
			<?php 
            $form_name = "Car Passengers"; 
            include("dashboard_view_navigation.php"); 
            ?>
        </div>
        <ul>
		  <li id="carGrid" data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="gridster_border" style="background:url(img/glass.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; display:;">
				<table width="100%">
					<tr>
						<td>
							<table style="display: inline-table;" border="0" cellpadding="0" cellspacing="0" width="395">
							<!-- fwtable fwsrc="Untitled" fwpage="Page 1" fwbase="car_empty.jpg" fwstyle="Dreamweaver" fwdocid = "278803958" fwnested="0" -->
							  <tr>
							   <td><img src="../img/ui/spacer.gif" width="42" height="1" border="0" alt="" /></td>
							   <td><img src="../img/ui/spacer.gif" width="65" height="1" border="0" alt="" /></td>
							   <td><img src="../img/ui/spacer.gif" width="2" height="1" border="0" alt="" /></td>
							   <td><img src="../img/ui/spacer.gif" width="59" height="1" border="0" alt="" /></td>
							   <td><img src="../img/ui/spacer.gif" width="57" height="1" border="0" alt="" /></td>
							   <td><img src="../img/ui/spacer.gif" width="63" height="1" border="0" alt="" /></td>
							   <td><img src="../img/ui/spacer.gif" width="162" height="1" border="0" alt="" /></td>
							   <td><img src="../img/ui/spacer.gif" width="1" height="1" border="0" alt="" /></td>
							  </tr>

							  <tr>
							   <td rowspan="5" class="roll_over" id="rear_bumper_outside"><img name="car_empty_r1_c1" src="../img/ui/car_empty_rear_bumper_outside.png" width="42" height="180" border="0" id="car_empty_r1_c1" alt="" /></td>
							   <td colspan="2"><img name="car_empty_r1_c2" src="../img/ui/car_empty_r1_c2.png" width="67" height="35" border="0" id="car_empty_r1_c2" alt="" /></td>
							   <td><img name="car_empty_r1_c4" src="../img/ui/car_empty_r1_c4.png" width="59" height="35" border="0" id="car_empty_r1_c4" alt="" /></td>
							   <td class="roll_over" id="left_outside"><img name="car_empty_r1_c5" src="../img/ui/car_empty_left_outside.png" width="57" height="35" border="0" id="car_empty_left_outside" alt="" /></td>
							   <td><img name="car_empty_r1_c6" src="../img/ui/car_empty_r1_c6.png" width="63" height="35" border="0" id="car_empty_r1_c6" alt="" /></td>
							   <td><img name="car_empty_r1_c7" src="../img/ui/car_empty_r1_c7.png" width="162" height="35" border="0" id="car_empty_r1_c7" alt="" /></td>
							   <td><img src="../img/ui/spacer.gif" width="1" height="35" border="0" alt="" /></td>
							  </tr>
							  <tr>
							   <td rowspan="3"  class="roll_over" id="trunk">
                               		<img name="car_empty_r2_c2" src="../img/ui/car_empty_trunk.jpg" width="65" height="105" border="0" id="car_empty_r2_c2" alt="" />
                               		
                               </td>
							   <td rowspan="3">
                               		<img name="car_empty_r2_c3" src="../img/ui/car_empty_r2_c3.jpg" width="2" height="105" border="0" id="car_empty_r2_c3" alt="" />
                               </td>
							   <td class="roll_over" id="rear_left">
                               		<img name="car_empty_r2_c4" src="../img/ui/car_empty_rear_left.jpg" width="59" height="37" border="0" id="car_empty_r2_c4" alt="" />
                               		
                               </td>
							   <td class="roll_over" id="middle_left">
                               		<img name="car_empty_r2_c5" src="../img/ui/car_empty_middle_left.jpg" width="57" height="37" border="0" id="car_empty_r2_c5" alt="" />
                               </td>
							   <td class="roll_over" id="front_left">
                               		<img name="car_empty_r2_c6" src="../img/ui/car_empty_front_left.jpg" width="63" height="37" border="0" id="car_empty_r2_c6" alt="" />
                               </td>
							   <td rowspan="3" class="roll_over" id="hood">
                               		<img name="car_empty_r2_c7" src="../img/ui/car_empty_hood.png" width="162" height="105" border="0" id="car_empty_r2_c7" alt="" />
                               		
                               </td>
							   <td><img src="../img/ui/spacer.gif" width="1" height="37" border="0" alt="" /></td>
							  </tr>
							  <tr>
							   <td class="roll_over" style="" id="rear_middle">
                               		<img name="car_empty_r3_c4" src="../img/ui/car_empty_rear_middle.jpg" width="59" height="35" border="0" id="car_empty_r3_c4" alt="" />
                               		
                               </td>
							   
                               <td class="roll_over" id="middle_middle">
                               		<img name="car_empty_r3_c5" src="../img/ui/car_empty_middle_middle.jpg" width="57" height="35" border="0" id="car_empty_r3_c5" alt="" />
                                    
                               </td>
							   <td class="roll_over" id="front_middle">
                               		<img name="car_empty_r3_c6" src="../img/ui/car_empty_front_middle.jpg" width="63" height="35" border="0" id="car_empty_r3_c6" alt="" />
                                    
                               </td>
							   <td><img src="../img/ui/spacer.gif" width="1" height="35" border="0" alt="" /></td>
							  </tr>
							  <tr>
							   <td class="roll_over" id="rear_right">
                               		<img name="car_empty_r4_c4" src="../img/ui/car_empty_rear_right.jpg" width="59" height="33" border="0" id="car_empty_r4_c4" alt="" />
                                    
                               </td>
							   <td class="roll_over" id="middle_right">
                               		<img name="car_empty_r4_c5" src="../img/ui/car_empty_middle_right.jpg" width="57" height="33" border="0" id="car_empty_r4_c5" alt="" />
                                    
                               </td>
							   <td class="roll_over" id="front_right">
                               		<img name="car_empty_r4_c6" src="../img/ui/car_empty_front_right.jpg" width="63" height="33" border="0" id="car_empty_r4_c6" alt="" />
                                    
                               </td>
							   <td><img src="../img/ui/spacer.gif" width="1" height="33" border="0" alt="" /></td>
							  </tr>
							  <tr>
							   <td colspan="2"><img name="car_empty_r5_c2" src="../img/ui/car_empty_r5_c2.png" width="67" height="40" border="0" id="car_empty_r5_c2" alt="" /></td>
							   <td style="" ><img name="car_empty_r5_c4" src="../img/ui/car_empty_r5_c4.png" width="59" height="40" border="0" id="car_empty_r5_c4" alt="" /></td>
							   <td class="roll_over" id="right_outside">
                               		<img name="car_empty_r5_c5" src="../img/ui/car_empty_right_outside.png" width="57" height="40" border="0" id="car_empty_r5_c5" alt="" />
                                    
                               </td>
							   <td><img name="car_empty_r5_c6" src="../img/ui/car_empty_r5_c6.png" width="63" height="40" border="0" id="car_empty_r5_c6" alt="" /></td>
							   <td><img name="car_empty_r5_c7" src="../img/ui/car_empty_r5_c7.png" width="162" height="40" border="0" id="car_empty_r5_c7" alt="" /></td>
							   <td><img src="../img/ui/spacer.gif" width="1" height="40" border="0" alt="" /></td>
							  </tr>
							</table>
						</td>
					</tr>
				</table>
          </li>
		</ul>
    </form>
</div>
</div>
<div class="car_passenger" id="car_passenger_all_done"></div>
<script language="javascript">
$( "#car_passenger_all_done" ).trigger( "click" );
</script>