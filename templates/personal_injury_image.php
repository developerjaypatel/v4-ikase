<span style="color:#FFFFFF; font-size:1.25em; font-weight:lighter; margin-left:3px;" id="import_title">Accident Picture</span>
<div>
<form class="personal_injury_image_form">
	<input type="hidden" id="attribute" name="attribute" value="personal_injury_picture" />
	<input id="file_upload" name="file_upload" type="file" multiple="true">
    <div style="border:0px solid green; width:40%">
        <div>
        	<select id="attribute_2" name="attribute_2">
            	<option value="">Choose One</option>
                <option value="scene_photo">Scene Photo</option>
                <option value="injury_photo">Injury Photo</option>
                <option value="prop_damamge_photo">Prop Damamge Photo</option>
                <option value="scene_diagram">Scene Diagram</option>
            </select>
        </div>
        <div><textarea id="upload_details" name="upload_details"></textarea></div>
    </div>
    <a id="upload_it_five" style="position: relative; top: 8px;color:white; cursor:pointer; display:none">Upload Files</a>
    
    <div style="display:inline; width:45%">
    	<div style="height:200px; width:100%">&nbsp;</div>
    	<iframe src="isotope-docs/personal_injury_image_iframe.php?case_id=<%= case_id %>&customer_id=<%= customer_id %>&attribute=personal_injury_picture" frameborder="0" style="width:98%; height:800px" allowtransparency="true"></iframe>   
    </div>
    <div id="queue" style="width:40%; border:0px solid blue"></div>
    
</form>
</div>
<div style="border:0px solid green; width:40%" id="batch_indicator"></div>