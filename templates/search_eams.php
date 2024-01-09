<div class="eams white_text glass_header_no_padding" style="border:1px solid white; padding:15px">
    <table cellpadding="2" cellspacing="0" style="background:url(img/glass_card_fade_3.png) no-repeat; padding:10px" border="0">
        <tr>
            <th align="left" valign="top">
                <span style="color:#FFFFFF; font-size:1.6em; font-weight:lighter; margin-left:0px;">EAMS Case Search</span><br /><br />
            </th>
            <td colspan="2" align="left" valign="top">&nbsp;
                
            </td>
        </tr>
        <tr>
            <th align="left" valign="top" style="margin-left:10px">
                First Name
            </th>
            <td colspan="2" align="left" valign="top">
                <input name="first_name" id="first_name" value="<%= first_name %>" class="eams_required">&nbsp;*required
            </td>
        </tr>
        <tr>
            <th align="left" valign="top" style="margin-left:10px">
                Last Name
            </th>
            <td colspan="2" align="left" valign="top">
                <input name="last_name" id="last_name" value="<%= last_name %>" class="eams_required">&nbsp;*required
            </td>
        </tr>
        <tr>
            <th align="left" valign="top" style="margin-left:10px">
                DOB
            </th>
            <td colspan="2" align="left" valign="top">
                <input name="dob" id="dob" value="<%= dob %>"  onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy">
            </td>
        </tr>
        <% if (applicant_full_address!="") { %>
        <tr>
            <th align="left" valign="top" style="margin-left:10px">
                Address
            </th>
            <td colspan="2" align="left" valign="top">
                <%= applicant_full_address %>
            </td>
        </tr>
        <% } %>
        <% if (applicant_full_address!="") { %>
        <tr>
            <th align="left" valign="top" style="margin-left:10px">
                Employer
            </th>
            <td colspan="2" align="left" valign="top">
                <%= employer %>
            </td>
        </tr>
        <% } %>
        <% if (start_date!="") { %>
        <tr>
            <th align="left" valign="top" style="margin-left:10px">
                DOI
            </th>
            <td colspan="2" align="left" valign="top">
                <%= start_date %>
                <% if (end_date!="" && end_date!="00/00/0000") { %>
                	- <%= end_date %> CT
                <% } %>
            </td>
        </tr>
        <% } %>
        <tr>
          <th align="left" valign="top">&nbsp;</th>
          <td colspan="2" align="left" valign="top">
          	<input id="eams_button" type="button" value="Must Fill Required" disabled="disabled" />
          </td>
      </tr>
    </table>
</div>
<div id="eams_list"></div>