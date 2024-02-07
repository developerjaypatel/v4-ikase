<html>
<head>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
</head>
<body>
<div class="modal-content" style="background-image: url(&quot;img/glass_edit_header_new.png&quot;);">
<div class="modal-header" style="background-image: url(&quot;img/glass_edit_header_new.png&quot;);">
<input type="hidden" id="modal_type" value="">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>


<div id="modal_save_holder" style="float:right"><span id="apply_tasks_holder" class="white_text"><input type="checkbox" id="apply_tasks" onchange="showEventTaskDateBox()">&nbsp;Save as Task</span>&nbsp;&nbsp;<a title="Save Event" class="interoffice save" onclick="saveEventModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a></div>

<div align="center">
    <textarea id="reminder_set" rows="2" cols="50"></textarea>
</div>

<div id="gifsave" style="float:right; display:none">
    <i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>
</div>
<h4 class="modal-title" id="myModalLabel" style="color:#FFFFFF;">Edit Event - ID 6229&nbsp;<a href="javascript:setDeleteEvent()" title="Click to enable deletion of this event" style="font-weight:normal;color:red;font-size:0.7em;cursor:pointer">Delete this Event</a>&nbsp;&nbsp;<div style="float:right; border:0px solid red">&nbsp;<a href="javascript:showRecurrent()" id="reminder_tab" style="font-size:0.7em" class="white_text"><i style="font-size:1.5em; color:white; cursor:pointer" class="glyphicon glyphicon-refresh" id="show_recurrent" title="Click to make recurrent"></i></a>&nbsp;&nbsp;<a href="javascript:showEvent()" id="reminder_tab_event" style="font-size:0.7em; display:none" class="white_text reminder_stuff"><i style="font-size:1.5em; color:white; cursor:pointer" class="glyphicon glyphicon-calendar" id="show_event" title="Click to show Event Details"></i></a></div></h4>
<div id="modal_billing_holder"><div style="color:white; margin-top:-25px; margin-left:200px; z-index:9999;" id="billing_dropdown_holder"><table><tbody><tr><td align="left" valign="top">Minutes:</td><td align="left" valign="top"><select name="billing_time_dropdownInput" id="billing_time_dropdownInput" style="height:25px; width:50px; margin-top:0px; margin-left:0px; background:white" tabindex="0" placeholder="15"><option value="" selected="">0</option><option value="5">5</option><option value="10">10</option><option value="15">15</option><option value="20">20</option><option value="30">30</option><option value="45">45</option><option value="60">60</option></select></td></tr></tbody></table></div></div>
</div>
        <div class="modal-body" id="myModalBody" style="color: rgb(255, 255, 255); background-image: url(&quot;img/glass_edit_header_new.png&quot;); overflow-x: hidden;"><div>
<div class="event" style="margin-left:10px">
<form id="event_form" name="event_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="event">
<input id="table_id" name="table_id" type="hidden" value="6229">
<input id="injury_id" name="injury_id" type="hidden" value="">
<input id="calendar_id" name="calendar_id" type="hidden" value="2">
<input id="case_id" name="case_id" type="hidden" value="42">
<input id="user_id" name="user_id" type="hidden" value="">
<input id="event_kind" name="event_kind" type="hidden" value="">
<table width="600px" border="0" align="left" cellpadding="3" cellspacing="0" class="recurrent_stuff" style="display:none" id="recurrent_table_screen">
    <tbody><tr id="case_id_row">
        <th align="left" valign="top" scope="row">Repeats:</th>
        <td colspan="2" valign="top">
        <select name="recurrent_repeatInput" id="recurrent_repeatInput" class="modalInput event input_class" style="width:150px">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="weekdays">Every Weekday (Mon-Fri)</option>
            <option value="weekday_odd">Every Monday, Wednesday, and Friday</option>
            <option value="weekday_even">Every Tuesday, and Thursday</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
        </select>        </td>
</tr>
<tr id="repeat_row">
    <th align="left" valign="top" scope="row">Repeat Every:</th>
    <td>
    <select name="recurrent_interval" id="recurrent_interval">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
        <option value="7">7</option>
        <option value="8">8</option>
        <option value="9">9</option>
        <option value="10">10</option>
        <option value="11">11</option>
        <option value="12">12</option>
        <option value="13">13</option>
        <option value="14">14</option>
        <option value="15">15</option>
        <option value="16">16</option>
        <option value="17">17</option>
        <option value="18">18</option>
        <option value="19">19</option>
        <option value="20">20</option>
        <option value="21">21</option>
        <option value="22">22</option>
        <option value="23">23</option>
        <option value="24">24</option>
        <option value="25">25</option>
        <option value="26">26</option>
        <option value="27">27</option>
        <option value="28">28</option>
        <option value="29">29</option>
        <option value="30">30</option>
    </select>    </td>
    </tr>
    <tr id="repeat_row_by" style="display:none">
    <th align="left" valign="top" scope="row">Repeat By:</th>
    <td><input type="radio" name="repeat_by_radio" id="recurrent_month_day" value="">&nbsp;&nbsp;day of the month&nbsp;&nbsp;<input type="radio" name="repeat_by_radio" id="recurrent_week_day" value="">&nbsp;&nbsp;day of the week</td>
    </tr>
<tr id="repeat_row_days" style="display: none;">
    <th align="left" valign="top" scope="row">Repeat On:</th>
    <td><input type="checkbox" name="recurrent_mon" id="recurrent_mon">&nbsp;&nbsp;M&nbsp;&nbsp;<input type="checkbox" name="recurrent_tue" id="recurrent_tue">&nbsp;&nbsp;T&nbsp;&nbsp;<input type="checkbox" name="recurrent_wed" id="recurrent_wed">&nbsp;&nbsp;W&nbsp;&nbsp;<input type="checkbox" name="recurrent_thurs" id="recurrent_thurs">&nbsp;&nbsp;Th&nbsp;&nbsp;<input type="checkbox" name="recurrent_fri" id="recurrent_fri">&nbsp;&nbsp;F&nbsp;&nbsp;<input type="checkbox" name="recurrent_sat" id="recurrent_sat">&nbsp;&nbsp;Sat&nbsp;&nbsp;<input type="checkbox" name="recurrent_sun" id="recurrent_sun">&nbsp;&nbsp;Sun</td>
    </tr>
<tr id="repeat_row_days_start">
    <th align="left" valign="top" scope="row">Starts On:</th>
    <td valign="top"><input name="recurrent_dateandtimeInput" class="modalInput event input_class" id="recurrent_dateandtimeInput" style="width:150px;display:" placeholder="12/12/2012" value=""></td>
    </tr>
<tr id="repeat_row_days_end">
    <th align="left" valign="top" scope="row">Ends:</th>
    <td><input type="radio" name="recurrent_end_radio" id="never" value="" class="end_radio"> 
    Never</td>
</tr>
<tr>
    <th align="left" valign="top" scope="row">&nbsp;</th>
    <td>
    <input type="radio" name="recurrent_end_radio" id="after_date" value="radio" class="end_radio">
    After 
    
    <input type="text" name="recurrent_end_after_dateInput" id="end_after_dateInput" style="width:30px; height:23px" value="1" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" class="end_radio"> 
    occurences
    </td>
</tr>

    <tr>
    <th align="left" valign="top" scope="row">&nbsp;</th>
    <td valign="top">
        <input type="radio" name="recurrent_end_radio" id="on_date" value="radio" class="end_radio">
        On 
        <input type="text" name="recurrent_end_on_dateInput" id="end_on_dateInput" style="width:100px; height:23px" value="" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" class="end_radio">
    </td>
    </tr>
    <tr>
    <th align="left" valign="top" scope="row">&nbsp;</th>
    <td valign="top">&nbsp;</td>
    </tr>
    <tr>
    <th align="left" valign="top" nowrap="nowrap" scope="row">Summary:</th>
    <td valign="top"><span id="summary_span">Daily</span></td>
    </tr>
</tbody></table>
    <iframe src="case_worker_reminder.php?users=1,2,1288"style="float: right; margin-right:100px;" height="800px" width="500px"></iframe><!--../../-->
    
    <div style="width: 350px; float: right; border: 1px solid white; " id="parties_list"><div><div>
    Kase Parties <span style="font-size:0.8em">(Select from list to set Event Location)</span>
    <table id="partie_listing" class="tablesorter partie_listing" border="0" cellpadding="0" cellspacing="1">
        <tbody>        
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_0"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_0">
                    iKase Demo Customer 
                    
                    <br>
                    In House
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_0"></span>
            </td>
        </tr>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_P11"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_P11">
                    
                    Louis Strongs
                    <br>
                    Applicant - <span id="event_partie_phone_P11">515-563-2233</span>
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_P11">93 Manhattan Avenue, <br>Brooklyn, NY, 11206</span>
            </td>
        </tr>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C4"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C4">
                    TRAVELERS DIAMOND BAR
                    
                    <br>
                    Carrier - <span id="event_partie_phone_C4">(800) 258-3710</span>
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C4">13248 Roscoe Blvd, <br>Los Angeles, CA, 91352</span>
            </td>
        </tr>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C7505"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C7505">
                    ABACUS LAW GROUP PASADENA
                    
                    <br>
                    Defense - <span id="event_partie_phone_C7505">(626) 204-4016</span>
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C7505">1055 E COLORADO BLVD, <br>PASADENA, CA, 91106</span>
            </td>
        </tr>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C24668"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C24668">
                    SALINAS FARM LABOR CONTRACTOR INC
                    
                    <br>
                    Employer
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C24668">235 East Route 66, <br>Glendora, CA, 91740</span>
            </td>
        </tr>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C52588"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C52588">
                    HAMLET  DAVARI , DDS
                    
                    <br>
                    Medical Provider - <span id="event_partie_phone_C52588">888-853-7944</span>
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C52588"></span>
            </td>
        </tr>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C52938"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C52938">
                    Williams Smith, MD
                    
                    <br>
                    Medical Provider
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C52938">1234 South Bronson Avenue, <br>Los Angeles, CA, 90019</span>
            </td>
        </tr>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C53137"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C53137">
                    Some Doctor
                    
                    <br>
                    Medical Provider
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C53137"></span>
            </td>
        </tr>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C8067"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C8067">
                    ABACUS LAW GROUP PASADENA
                    
                    <br>
                    Prior Attorney - <span id="event_partie_phone_C8067">(626) 204-4016</span>
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C8067">1055 E COLORADO BLVD, <br>PASADENA, CA, 91106</span>
            </td>
        </tr>
        
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C9747"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C9747">
                    Referred Attorney Outs
                    
                    <br>
                    Referredout Attorney
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C9747"></span>
            </td>
        </tr>
        
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C25161"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C25161">
                    Thomas  Smith
                    
                    <br>
                    Referring - <span id="event_partie_phone_C25161">626-966-9959</span>
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C25161">3211 East Garvey Ave N, <br>West Covina, CA, 91791</span>
            </td>
        </tr>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C9812"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C9812">
                    Riverside
                    
                    <br>
                    Venue - <span id="event_partie_phone_C9812">(951) 782-426</span>
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C9812">3737 Main Street, <br>Riverside, CA, 92501</span>
            </td>
        </tr>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_C16"></td>
            <td style="font-size:0.8em">
                <span id="event_partie_name_C16">
                    Drop this
                    
                    <br>
                    Wcab
                </span>
            </td>
            <td style="font-size:0.8em">
                <span id="event_partie_address_C16"></span>
            </td>
        </tr>
        </tbody>
    </table>
</div>
</div></div>
<table width="550px" border="0" align="left" cellpadding="3" cellspacing="0" class="event_stuff" style="display:" id="event_table_screen">
    <tbody><tr style="display:none" id="uneditable_row">
        <td colspan="3">
            <div id="uneditable"></div>
        </td>
    </tr>
    

    <tr id="case_id_row">
        <th align="left" valign="top" scope="row">Case:</th>
        <td colspan="2" valign="top" id="case_id_holder"><div class="case_input"><ul class="token-input-list-event" style="display: none;"><li class="token-input-token-event"><p>Louis Strongs vs SALINAS FARM LABOR CONTRACTOR INC 05/17/1988</p><span class="token-input-delete-token-event">×</span></li><li class="token-input-input-token-event"><input type="text" autocomplete="off" id="token-input-case_idInput" style="outline: none;"><tester style="position: absolute; top: -9999px; left: -9999px; width: auto; font-size: 12px; font-family: Verdana; font-weight: 400; letter-spacing: 0px; white-space: nowrap;"></tester></li></ul><input name="case_idInput" type="text" id="case_idInput" size="30" class="modal_input event_6229" value="" style="display: none;"></div>
        <span id="case_idSpan" style="">Louis Strongs vs SALINAS FARM LABOR CONTRACTOR INC 05/17/1988</span>        </td>
</tr>
<tr>
    <th align="left" valign="top" scope="row">Subject:</th>
    <td valign="top"><input name="event_titleInput" type="text" id="event_titleInput" style="width:470px" class="modalInput event input_class" value="for reminder" autocomplete="off">    </td>
</tr>
<tr>
    <th align="left" valign="top" scope="row">Assignee:</th>
    <td><ul class="token-input-list-event"><li class="token-input-token-event"><p>Angel Valero</p><span class="token-input-delete-token-event">×</span></li><li class="token-input-token-event"><p>Neal Bapat</p><span class="token-input-delete-token-event">×</span></li><li class="token-input-input-token-event"><input type="text" autocomplete="off" id="token-input-assigneeInput" style="outline: none;"><tester style="position: absolute; top: -9999px; left: -9999px; width: auto; font-size: 12px; font-family: Verdana; font-weight: 400; letter-spacing: 0px; white-space: nowrap;"></tester></li></ul><input name="assigneeInput" type="text" id="assigneeInput" style="width: 433px; display: none;" class="modalInput event input_class" value="NB;av"></td>
    </tr>
<tr style="display:">
    <th align="left" valign="top" scope="row">Location:</th>
    <td>
        <input name="full_addressInput" type="text" id="full_addressInput" style="width:470px" class="modalInput event input_class" placeholder="Enter event address" autocomplete"off"="" value="" autocomplete="off">
        <div id="google_map" style="display:inline; cursor:pointer; margin-left:5px; opacity:0">
            <i style="font-size:20px; color:#0FF; cursor:pointer" class="glyphicon glyphicon-map-marker" title="Click to show Map"></i>
        </div>
    </td>
</tr>
<tr>
    <th align="left" valign="top" scope="row">When:</th>
    <td valign="top">
        <!--<div style="float:right; margin-right:0px">
        <span style="font-weight:bold">Close:</span>&nbsp;
            <input class="modalInput event input_class" id="event_closedateInput" style="width:125px;" placeholder="mm/dd/yyyy" value="" />
        </div>
        -->
        <input name="event_dateandtimeInput" class="modalInput event input_class" id="event_dateandtimeInput" style="width:125px;display:" placeholder="mm/dd/yyyy" value="01/19/2017 07:00 am">
        <input class="modalInput event input_class original_date" style="width:125px;" placeholder="mm/dd/yyyy" type="hidden" value="01/19/2017 07:00 am">
        <div style="display:inline-block; border:0px solid red" id="reschedule_section">
            &nbsp; Or calculate &nbsp;            
            <input type="text" value="" name="number_of_days" id="number_of_days" style="width:40px;" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')">&nbsp;&nbsp; days
            &nbsp;&nbsp;<span id="calculated_dateSpan" class="span_class form_span_vert" style="position:relative; width:20px"></span>
        </div>
    </td>
</tr>
    <tr>
    <th align="left" valign="top" scope="row">Duration:</th>
    <td valign="top">
    <select name="event_durationInput" class="modalInput event input_class" id="event_durationInput" style="width:75px;display:">
                <option value="15">15 min</option>
                <option value="30">30 min</option>
                <option value="45">45 min</option>
                <option value="60" selected="">1h</option>
                <option value="75">1h 15 min</option>
                <option value="90">1h 30 min</option>
                <option value="105">1h 45 min</option>
                <option value="120">2h</option>
                <option value="240">4h</option>
            </select>
        
    
        </td>
    </tr>
    <tr>
    <th align="left" valign="top" scope="row">Type:</th>
    <td valign="top">
        <div style="float:right">
            <button class="btn btn-primary btn-xs" id="show_reminder">Reminder</button>
            <button class="btn btn-primary btn-xs" id="show_second_reminder" title="Click to enter a 2nd reminder" style="display:none">2nd Reminder</button>
        </div>
        <div id="event_type_drop">
        
            <select name="event_typeInput" id="event_typeInput" class="modalInput event input_class" style="height:25px; width:110px; margin-top:-30px; margin-left:0px">
            
                <option value="" selected="">Filter By Type</option><option value="Appt with Attorney">Appt with Attorney</option><option value="Deposition">Deposition</option><option value="Deposition Prep">Deposition Prep</option><option value="Doctor Cross Examination">Doctor Cross Examination</option><option value="Expedited Hearing">Expedited Hearing</option><option value="Hearing">Hearing</option><option value="Lien Conference">Lien Conference</option><option value="MSC">MSC</option><option value="phone_call">phone_call</option><option value="Status Conference">Status Conference</option><option value="Trial">Trial</option>
                
            </select>
            
            
            
            </div>
    </td>
    </tr>
    
<tr style="display:none" id="first_reminder_row">
    <th align="left" valign="top" scope="row" nowrap="nowrap">1st Reminder:</th>
    <td class="reminder_stuff">
        <input type="hidden" name="reminder_id1" id="reminder_id1" value="-1">
        <select name="reminder_type1" id="reminder_type1" class="reminder_field">
        <!--<option value="text">Text</option>-->
        <!--<option value="email" >Email</option>-->
        <option value="interoffice">Interoffice</option>
        </select>
        <input type="text" name="reminder_interval1" id="reminder_interval1" style="width:30px; height:23px" value="" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" class="reminder_field">
    <select name="reminder_span1" id="reminder_span1" class="reminder_field">
        <option value="minutes">Minutes</option>
        <option value="hours">Hours</option>
        <option value="days">Days</option>
        <option value="weeks">Weeks</option>
    </select>
    <span id="reminder_datetime1" style="text-align:left"></span>
    </td>
</tr>
<tr style="display:none" id="second_reminder_row">
    <th align="left" valign="top" scope="row" nowrap="nowrap">2nd Reminder:</th>
    <td class="reminder_stuff">
        <input type="hidden" name="reminder_id2" id="reminder_id2" value="-1">
        <select name="reminder_type2" id="reminder_type2" class="reminder_field">
        <!--<option value="text">Text</option>
        <option value="email" >Email</option>-->
        <option value="interoffice">Interoffice</option>
        </select>      
        <input type="text" name="reminder_interval2" id="reminder_interval2" style="width:30px; height:23px" value="" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" class="reminder_field">
    <select name="reminder_span2" id="reminder_span2" class="reminder_field">
        <option value="minutes">Minutes</option>
        <option value="hours">Hours</option>
        <option value="days">Days</option>
        <option value="weeks">Weeks</option>
    </select>
    <span id="reminder_datetime2" style="text-align:left"></span>
    </td>
</tr>

<tr>
    <th align="left" valign="top" scope="row">Details:</th>
    <td>&nbsp;</td>
</tr>
<tr>
    <td colspan="2" align="left" valign="top" scope="row" id="description_holder">
    <div class="cleditorMain" style="width: 545px; height: 130px;"><div class="cleditorToolbar" style="height: 27px;"><div class="cleditorGroup" style="width: 73px;"><div class="cleditorButton" title="Bold"></div><div class="cleditorButton" title="Italic" style="background-position: -24px center;"></div><div class="cleditorButton" title="Underline" style="background-position: -48px center;"></div><div class="cleditorDivider"></div></div><div class="cleditorGroup" style="width: 73px;"><div class="cleditorButton" title="Font" style="background-position: -144px center;"></div><div class="cleditorButton" title="Font Size" style="background-position: -168px center;"></div><div class="cleditorButton" title="Style" style="background-position: -192px center;"></div><div class="cleditorDivider"></div></div><div class="cleditorGroup" style="width: 49px;"><div class="cleditorButton" title="Font Color" style="background-position: -216px center;"></div><div class="cleditorButton" title="Text Highlight Color" style="background-position: -240px center;"></div></div></div><textarea name="event_descriptionInput" id="event_descriptionInput" class="modalInput event input_class" style="border: none; margin: 0px; padding: 0px; display: none; width: 545px; height: 80px;">save the event</textarea><iframe frameborder="0" src="javascript:true;" id="cleditor_frame" style="width: 545px; height: 80px;"></iframe></div></td>
    </tr>
    <tr>
    <th align="left" valign="top" nowrap="nowrap" scope="row">Entered By:</th>
    <td valign="top"><div style="float:right; margin-right:0px"><span style="font-weight:bold">Priority:</span>&nbsp;
        <select name="event_priorityInput" id="event_priorityInput" class="modalInput event input_class" style="width:150px">
        <option value="high">High</option>
        <option value="normal" selected="">Normal</option>
        <option value="low">Low</option>
        </select>
        </div>
        <input name="event_fromInput" type="text" id="event_fromInput" style="width:433px" class="modalInput event input_class hidden" value="Nick Giszpenc">
        <span class="span_class">Nick Giszpenc</span></td>
    </tr>
    <tr style="display:none">
    <th align="left" valign="top" scope="row">End Date:</th>
    <td valign="top">
        <input name="end_dateInput" class="modalInput event input_class" id="end_dateInput" value="01/19/17 8:00 am" style="width:150px" placeholder="12/12/2030 10:15am">	</td>
    </tr>
    <tr style="display:none" id="follow_up_row">
    <th colspan="2" align="left" valign="top" nowrap="nowrap" scope="row">
        <div id="follow_up_holder"><span id="follow_up_label">Follow Up Date:</span>        <input name="callback_dateInput" class="modalInput event input_class" id="callback_dateInput" value="" style="width:150px" placeholder="12/12/2030 10:15am">	
        </div>
        </th>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    </tr>
<tr>
    <td colspan="2">
        <input type="hidden" id="send_document_id" name="send_document_id" value="">
        <div id="message_attachments" style="width:90%"></div>    </td>
</tr>
</tbody></table>
</form>
</div>
<div id="addressGrid" style="display:none">
    <table id="address">
    <tbody><tr style="display:none">
        <td class="label">Street address</td>
        <td class="slimField"><input class="field" id="street_number_event" disabled="true"></td>
        <td class="wideField" colspan="2"><input class="field" id="route_event" disabled="true"></td>
    </tr>
    <tr>
        <td class="wideField" colspan="4">
            <input class="field" id="street_event">&nbsp;<input class="field" id="city_event" style="width:100px">&nbsp;<input class="field" id="administrative_area_level_1_event" disabled="true" style="width:30px">&nbsp;<input class="field" id="postal_code_event" disabled="true" style="width:50px">
        </td>
    </tr>
    <tr style="display:none">
        <td class="label">City</td>
        <td class="wideField" colspan="3">
            <input class="field" id="locality_event" disabled="true">
            <input class="field" id="sublocality_event" disabled="true">
            <input class="field" id="neighborhood_event" disabled="true">
        </td>
    </tr>
    <tr style="display:none">
        <td class="label">Country</td>
        <td class="wideField" colspan="3"><input class="field" id="country_event" disabled="true"></td>
    </tr>
    </tbody></table>
</div>
<div id="event_view_all_done"></div>
</div></div>
        <div class="modal-footer" style="color: rgb(255, 255, 255); display: none; background-image: url(&quot;img/glass_edit_header_new.png&quot;);">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="interoffice btn btn-primary save" onclick="saveModal()">Save changes</button>
            <div style="float:left" id="apply_notes_holder">
                <input type="checkbox" id="apply_notes">&nbsp;Apply to Notes
            </div>
        </div>
    </div>

<script language="javascript">
$("#event_view_all_done").trigger( "click" );
$(".xdsoft_time ").click(function() {
    var cal_date_val = $(".xdsoft_date").val();
    //console.log(cal_date_val);
    return;
});
function getMyStuff(stuff) {	
    document.querySelector('#reminder_set').value = stuff;
}
</script>
</body>
</html>