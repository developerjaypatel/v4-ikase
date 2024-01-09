<span style="color:#FFFFFF; font-size:1.25em; font-weight:lighter; margin-left:3px;" id="import_title"><%=label %> Picture</span>
<div>
<form class="person_image_form">
	<input type="hidden" id="attribute" name="attribute" value="applicant_picture" />
    <!--<input id="file_upload" name="file_upload" type="file" multiple="true">-->
    <!-- solulab code start 19-04-2019-->
    <input id="file_upload" name="file_upload" type="file" multiple="false" accept="image/*">
    <!-- solulab code end 19-04-2019-->
    <a id="upload_it_five" style="position: relative; top: 8px;color:white; cursor:pointer; display:none">Upload Files</a>
    <div id="queue" style="width:50%; border:0px"></div>
</form>
</div>
<div id="batch_indicator"></div>