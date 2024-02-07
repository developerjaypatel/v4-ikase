<div class="recurrent" style="margin-left:10px">
<form id="recurrent_form" name="recurrent_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="recurrent" />
<input id="table_id" name="table_id" type="hidden" value="" />
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0" class="event_stuff">
	<tr id="case_id_row">
        <th align="left" valign="top" scope="row">Repeats:</th>
        <td colspan="2" valign="top" id="case_id_holder">
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
  <tr>
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
  <tr style="display:<% if (event_kind=='phone_call') { %>none<% } %>">
    <th align="left" valign="top" scope="row">Repeat On:</th>
    <td><input type="checkbox" name="mon" id="mon" />&nbsp;&nbsp;M&nbsp;&nbsp;<input type="checkbox" name="tue" id="tue" />&nbsp;&nbsp;T&nbsp;&nbsp;<input type="checkbox" name="wed" id="wed" />&nbsp;&nbsp;W&nbsp;&nbsp;<input type="checkbox" name="thurs" id="thurs" />&nbsp;&nbsp;Th&nbsp;&nbsp;<input type="checkbox" name="fri" id="fri" />&nbsp;&nbsp;F&nbsp;&nbsp;<input type="checkbox" name="sat" id="sat" />&nbsp;&nbsp;Sat&nbsp;&nbsp;<input type="checkbox" name="sun" id="sun" />&nbsp;&nbsp;Sun</td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row">Starts On:</th>
    <td valign="top"><input name="recurrent_dateandtimeInput" class="modalInput event input_class" id="recurrent_dateandtimeInput" style="width:150px;display:" placeholder="12/12/2012" value="" /></td>
    </tr>
    <% if (event_id =="-1") { %>
  <tr>
    <th align="left" valign="top" scope="row">Ends:</th>
    <td class="reminder_stuff"><input type="radio" name="end_radio" id="never" value="" /></td>
  </tr>
  <% } %>
  <tr>
    <th align="left" valign="top" scope="row">&nbsp;</th>
    <td>
      <input type="radio" name="end_radio" id="after_date" value="radio" />
    
      <input type="text" name="end_after_dateInput" id="end_after_dateInput" style="width:30px; height:23px" value="1" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" />
    </td>
  </tr>
  
    <tr>
      <th align="left" valign="top" scope="row">&nbsp;</th>
      <td valign="top">
        <input type="radio" name="end_radio" id="on_date" value="radio" />
      <input type="text" name="end_on_dateInput" id="end_on_dateInput" style="width:30px; height:23px" value="1" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" />
      </td>
    </tr>
    <tr style="display:<% if (event_kind=='phone_call') { %>none<% } %>">
      <th align="left" valign="top" scope="row">&nbsp;</th>
      <td valign="top">&nbsp;</td>
    </tr>
    <tr>
      <th align="left" valign="top" nowrap="nowrap" scope="row">Summary:</th>
      <td valign="top">&nbsp;</td>
    </tr>
    
  <tr>
  	<td colspan="2">
    	<input type='hidden' id='send_document_id' name='send_document_id' value="" />
    	<div id="message_attachments" style="width:90%"></div>    </td>
  </tr>
</table>
</form>
</div>
<div id="addressGrid" style="display:none">
    <table id="address">
      <tr style="display:none">
        <td class="label">Street address</td>
        <td class="slimField"><input class="field" id="street_number_event"
              disabled="true"></input></td>
        <td class="wideField" colspan="2"><input class="field" id="route_event"
              disabled="true"></input></td>
      </tr>
      <tr>
        <td class="wideField" colspan="4">
            <input class="field" id="street_event"></input>&nbsp;<input class="field" id="city_event"style="width:100px"></input>&nbsp;<input class="field"
              id="administrative_area_level_1_event" disabled="true" style="width:30px"></input>&nbsp;<input class="field" id="postal_code_event"
              disabled="true" style="width:50px"></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">City</td>
        <td class="wideField" colspan="3">
            <input class="field" id="locality_event"
              disabled="true"></input>
            <input class="field" id="sublocality_event"
              disabled="true"></input>
              <input class="field" id="neighborhood_event"
              disabled="true"></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">Country</td>
        <td class="wideField" colspan="3"><input class="field"
              id="country_event" disabled="true"></input></td>
      </tr>
    </table>
</div>