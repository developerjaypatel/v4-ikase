<div class="import" style="padding-left:20px; ">
    <span style="color:#FFFFFF; font-size:1.25em; font-weight:lighter; margin-left:3px;" id="import_title"><%= import_type.capitalizeWords() %> Upload</span>
    <div>
    <form>
		<div style="padding-top:10px; padding-bottom:10px; display: none">
        	<select id="import_type">
            	<option value="">Select Import Type</option>
                <option value="batchscan" <% if (import_type=="batchscan" || import_type=="") { %>selected<% } %>>Batch Scan</option>
                <option value="unassigned" <% if (import_type=="unassigned") { %>selected<% } %>>Unassigned Documents Upload</option>
            </select>
        </div>        
        <div class="import_div">
            <input id="file_upload" name="file_upload" type="file" multiple="true" style="border:1px solid red">
            <a id="upload_it_five" style="position: relative; top: 8px;color:white; cursor:pointer; display:none">Upload Files</a>
        </div>
        <div id="queue" style="width:50%; height:55px; overflow: hidden; margin-top:10px" class="import_div"></div>
    </form>
    </div>
    <div id="batch_indicator" style="margin-top:10px">&nbsp;</div>
</div>