<div class="white_text" style="color:black">
	<span style="font-size:1.1em; font-weight:bold" class="white_text">Manage Kase Sub Type</span>
    &nbsp;
    <button class="btn btn-xs" id="new_kase_subtype_button">New Subtype</button>
    &nbsp;<a id="show_all_status" style="color:white; cursor:pointer" title="Click to show deleted status">Show Deleteds</a>
</div>
<div class="white_text" style="font-style: italic; margin-bottom: 10px;">
Click on items below to edit or delete
</div>
<div>
	<form  id="kase_status_form">
    <div id="new_kase_subtype_holder" style="display:none">
    	<input type="text" required id="new_kase_subtype" />&nbsp;<select name="caseTypeInput" id="caseTypeInput" required="">
            <option value="">Select from List</option>
            <option value="WCAB" selected="">WCAB</option>
            <option value="NewPI">DUI</option>
            <option value="civil">Civil</option>
            <option value="employment_law">Employment Law</option>
            <option value="immigration">Immigration</option>
            <option value="pi">Personal Injury</option>
            <option value="social_security">Social Security</option>
            <option value="WCAB_Defense">WCAB Defense</option>
            <option value="class_action">Class Action</option>
                      </select> <button class="btn btn-xs btn-success" id="save_kase_status">Save</button>
    </div>
	<%=html %>
    </form>
</div>
