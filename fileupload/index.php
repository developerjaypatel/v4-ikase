<?php
require_once('../shared/legacy_session.php');
session_write_close(); //FIXME: WHY GOD, WHY
//echo $_SERVER['DOCUMENT_ROOT'];
?>
<!DOCTYPE HTML>
<!--
/*
 * jQuery File Upload Plugin Demo 9.0.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
-->
<html lang="en">
<head>
<!-- Force latest IE rendering engine or ChromeFrame if installed -->
<!--[if IE]>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<![endif]-->
<meta charset="utf-8">
<title>Manage Documents</title>
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap styles -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
<!-- Generic page styles -->
<link rel="stylesheet" href="css/style.css">
<!-- blueimp Gallery styles -->
<link rel="stylesheet" href="css/blueimp-gallery.min.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="css/jquery.fileupload.css">
<link rel="stylesheet" href="css/jquery.fileupload-ui.css">
<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript><link rel="stylesheet" href="css/jquery.fileupload-noscript.css"></noscript>
<noscript><link rel="stylesheet" href="css/jquery.fileupload-ui-noscript.css"></noscript>

<link href="../css/tablesorter_blue.css" rel="stylesheet">
<link href="../css/styles.css" rel="stylesheet">
<style>
 .contmodify{
    margin-left:0px;
    line-height: 1.128571;
 }
 .clrbtn{
    background-color: #00838f;
    color: white;
 }
</style>
</head>
<body class="glass_header_no_padding">
<div id="preview_panel" style="position:absolute; width:800px; display:none; z-index:2"></div>
<div id="view_document" style="position:absolute; width:950px; height:600px; display:none; z-index:2; border:1px solid black; background:black">
	<div style="float:right"><a href="javascript:closeDocument()" title="Click to close preview" style="color:white; text-decoration:none">close</a></div>
	<iframe id="view_frame" width="100%" height="500px" scrolling="no"; frameborder="0" src=""></iframe>
</div>
<!-- <div class="container contmodify"> -->
 <div style="width: 70%;margin-left: 10px;">   
    <br>
    <div class='row'>
        <div class='col-lg-2'>
            <button type="submit" class="btn clrbtn" title="view docs list">
                <i class="glyphicon glyphicon-list-alt"></i>
                <span>View Documents</span>
            </button>
        </div>
        <!-- 
        The file upload form used as target for the file upload widget
        //jquery-file-upload.appspot.com/
        -->
        <div class='col-lg-10'>
            <form id="fileupload" action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" value="document" id="type" name="type">
                <input type="hidden" value="<?php echo $_GET["case_id"]; ?>" id="case_id" name="case_id" class="upload_file">
                <!--<input type="hidden" value="<?php //echo $_GET["case_uuid"]; ?>" id="case_uuid" name="case_uuid">-->
                <input type="hidden" value="<?php echo $_SESSION['user_customer_id']; ?>" id="customer_id" name="customer_id">
                <input type="hidden" value="<?php echo $_SESSION['user_id']; ?>" id="user_id" name="user_id">
                <!-- Redirect browsers with JavaScript disabled to the origin page -->
                <noscript><input type="hidden" name="redirect" value="../fileupload"></noscript>
                
                <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
                <div class="row fileupload-buttonbar" style="margin-left:0px">
                    <div class="col-lg-7">
                        <!-- The fileinput-button span is used to style the file input field as button -->
                        <span class="btn btn-success fileinput-button">
                            <i class="glyphicon glyphicon-plus"></i>
                            <span>Add Files...</span>
                            <input type="file" name="files[]" multiple>
                        </span>
                <button type="submit" class="btn btn-primary start" title="Start Upload">
                            <i class="glyphicon glyphicon-upload"></i>
                            <span>Start Upload</span>
                        </button>
                        <button type="reset" class="btn btn-warning cancel" title="Cancel Upload">
                            <i class="glyphicon glyphicon-ban-circle"></i>
                            <span>Cancel Upload</span>
                        </button>
                        <button type="button" class="btn btn-danger delete" style="Delete">
                            <i class="glyphicon glyphicon-trash" style="color:white"></i>
                            <span style="color:white">Delete</span>
                        </button>
                        <input type="checkbox" class="toggle">
                        <!-- The global file processing state -->
                        <span class="fileupload-process"></span>
                    </div>
                    <div style="float:right; width:450px; margin-top:-20px">
                    <!-- The table listing the files available for upload/download -->
                    <table role="presentation" class="tablesorter presentation" style="display:none; border:1px solid white; padding-bottom:0px">
                        <tbody class="files"></tbody>
                    </table>
                </div>
                <!-- The global progress state -->
                    <div class="col-lg-5 fileupload-progress fade">
                        <!-- The global progress bar -->
                        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                        </div>
                        <!-- The extended global progress state -->
                        <div class="progress-extended">&nbsp;</div>
                    </div>
                </div>
            </form>
        </div>
        <br>
    </div>
</div>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) {
	var purefile = file.name;
	 %}
    <tr class="template-upload kase_document_data_row fade glass_header_no_padding">
        <td style="border:0px solid white;">
            <span class="preview">
			</span>
        </td>
        <td style="border:0px solid white;">
			<span class="name">{%=file.name%}</span>
            <strong class="error text-danger"></strong>
        </td>
        <td style="border:0px solid white; width:140px">
			<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="display:right"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
		<td style="border:0px solid white; width:80px">
            <span class="size">Processing...</span>
        </td>
        <td style="border:0px solid white; width:140px">
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" title="Click to Upload" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel" title="Cancel Upload">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                </button>
            {% } %}
        </td>
		<td colspan="3" style="border:0px solid white;">
			&nbsp;
		</td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
	<tr style="display:none">
		<th class="white_header">Preview</th>
		<th class="white_header"></th>
		<th class="white_header">File</th>
		<th class="white_header">Upload Date</th>
		<th class="white_header">Type</th>
		<th class="white_header">Category</th>
		<th class="white_header">&nbsp;</th>
	</tr>
{% for (var i=0, file; file=o.files[i]; i++) { 
	var purefile = file.name;
	%}
    <tr class="template-download kase_document_data_row fade glass_header_no_padding" style="display:none">
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="javascript:showDocument('{%=file.name%}')" title="Click to review upload"><img src="{%=file.thumbnailUrl%}" onmouseover="showPreview(event)" onmouseout="hidePreview()" id="thumbnail_{%=file.document_uuid%}"></a>
                {% } %}
            </span>
        </td>
		<td>
		{% if (file.url) { %}
        	<a href="javascript:openDocumentDetails('{%=file.document_uuid%}')" id="expand_document_{%=file.document_uuid%}"><i class="glyphicon glyphicon-plus"></i></a><a href="javascript:closeDocumentDetails('{%=file.document_uuid%}')" id="shrink_document_{%=file.document_uuid%}" style="display:none"><i class="glyphicon glyphicon-minus"></i></a>
		{% } %}			
		</td>
        <td>
            <p class="name">
				{% if (file.url) { %}
                <a href="{%=file.url%}" title="Review this upload" target="_blank" id="document_link_{%=file.document_uuid%}" class="list_link">{%=file.name%}</a>
					<input type="text" size="30" name="document_name_{%=file.document_uuid%}" id="document_name_{%=file.document_uuid%}" value="{%=file.name%}" onkeyup="scheduleSave('{%=file.name%}', '{%=file.document_uuid%}')" style="display:none">
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
		<td>
            <span class="size">{%=file.document_date%}</span>
			<div id="feedback_{%=file.document_uuid%}" style="text-align:center"></div>
        </td>
		<td>
			<select style="width:193px" name="type_{%=file.document_uuid%}" id="type_{%=file.document_uuid%}" onChange="setType('{%=purefile%}', '{%=file.document_uuid%}', '{%=file.document_id%}')">
                <option value="">Select Type</option>
<option value="Client" {% if (file.type=="Client") { %}selected{% } %}>Client</option>
<option value="Carrier Document" {% if (file.type=="Carrier Document") { %}selected{% } %}>Carrier Document</option>
<option value="Correspondence" {% if (file.type=="Correspondence") { %}selected{% } %}>Correspondence</option>
<option value="Defense Attorney" {% if (file.type=="Defense Attorney") { %}selected{% } %}>Defense Attorney</option>
<option value="Document" {% if (file.type=="Document" || file.type=="document" || file.type=="") { %}selected{% } %}>Document</option>
<option value="Employment" {% if (file.type=="Employment") { %}selected{% } %}>Employment</option>
<option value="Notes" {% if (file.type=="Notes") { %}selected{% } %}>Notes</option>
<option value="Medical" {% if (file.type=="Medical") { %}selected{% } %}>Medical</option>
           </select>
		</td>
		<td>
			<select style="width:193px" name="document_extension_{%=file.document_uuid%}" id="document_extension_{%=file.document_uuid%}" onChange="setType('{%=purefile%}', '{%=file.document_uuid%}')">
                    <option value="" {% if (file.document_extension=="") { %}selected{% } %}>Select Category</option>
                    <option value="AME Report" {% if (file.document_extension=="AME Report") { %}selected{% } %}>AME Report</option>
                    <option value="Copy Service Request" {% if (file.document_extension=="Copy Service Request") { %}selected{% } %}>Copy Service Request</option>
                    <option value="COR" {% if (file.document_extension=="COR") { %}selected{% } %}>COR</option>
                    <option value="COR - C" {% if (file.document_extension=="COR - C") { %}selected{% } %}>COR - C</option>
                    <option value="COR - DA" {% if (file.document_extension=="COR - DA") { %}selected{% } %}>COR - DA</option>
                    <option value="COR - IMR" {% if (file.document_extension=="COR - IMR") { %}selected{% } %}>COR - IMR</option>
                    <option value="COR - INS" {% if (file.document_extension=="COR - INS") { %}selected{% } %}>COR - INS</option>
                    <option value="COR - UR" {% if (file.document_extension=="COR - UR") { %}selected{% } %}>COR - UR</option>
                    <option value="Depo Transcript" {% if (file.document_extension=="Depo Transcript") { %}selected{% } %}>Depo Transcript</option>
                    <option value="Email Received" {% if (file.document_extension=="Email Received") { %}selected{% } %}>Email Received</option>
                    <option value="Email Sent" {% if (file.document_extension=="Email Sent") { %}selected{% } %}>Email Sent</option>
                    <option value="Fax Received" {% if (file.document_extension=="Fax Received") { %}selected{% } %}>Fax Received</option>
                    <option value="Fax Sent" {% if (file.document_extension=="Fax Sent") { %}selected{% } %}>Fax Sent</option>
                    <option value="Fee" {% if (file.document_extension=="Fee") { %}selected{% } %}>Fee</option>
                    <option value="Letter Received" {% if (file.document_extension=="Letter Received") { %}selected{% } %}>Letter Received</option>
                    <option value="Letter Sent" {% if (file.document_extension=="Letter Sent") { %}selected{% } %}>Letter Sent</option>
                    <option value="Manual Entry" {% if (file.document_extension=="Manual Entry") { %}selected{% } %}>Manual Entry</option>
                    <option value="Medical Report" {% if (file.document_extension=="Medical Report") { %}selected{% } %}>Medical Report</option>
                    <option value="Misc" {% if (file.document_extension=="Misc") { %}selected{% } %}>Misc</option>
                    <option value="MPN" {% if (file.document_extension=="MPN") { %}selected{% } %}>MPN</option>
                    <option value="Note" {% if (file.document_extension=="Note") { %}selected{% } %}>Note</option>
                    <option value="P & S Report" {% if (file.document_extension=="P & S Report") { %}selected{% } %}>P & S Report</option>
                    <option value="Payment" {% if (file.document_extension=="Payment") { %}selected{% } %}>Payment</option>
                    <option value="Pleadings" {% if (file.document_extension=="Pleadings") { %}selected{% } %}>Pleadings</option>
                    <option value="PQME Report" {% if (file.document_extension=="PQME Report") { %}selected{% } %}>PQME Report</option>
                    <option value="Proof Sent" {% if (file.document_extension=="Proof Sent") { %}selected{% } %}>Proof Sent</option>
                    <option value="Reviewed" {% if (file.document_extension=="Reviewed") { %}selected{% } %}>Reviewed</option>
                    <option value="SDT Records" {% if (file.document_extension=="SDT Records") { %}selected{% } %}>SDT Records</option>
                    <option value="Settlement Docs" {% if (file.document_extension=="Settlement Docs") { %}selected{% } %}>Settlement Docs</option>
                    <option value="Telephone Call" {% if (file.document_extension=="Telephone Call") { %}selected{% } %}>Telephone Call</option>
            </select>
		</td>
		<td>
			<select class="document_input" name="document_subcategory_{%=file.document_uuid%}" id="document_subcategory_{%=file.document_uuid%}">
				<option value="">Select Sub Category</option>
				<option value="doctor" {% if (file.description=="doctor") { %}selected{% } %}>Doctor</option>
				<option value="attorney" {% if (file.description=="attorney") { %}selected{% } %}>Attorney</option>
			</select>
		</td>
        <td>
            {% if (file.deleteUrl) { %}
                <button class="btn btn-transparent delete" title="Click to delete this document" style="width:30px; border:0px solid"data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                </button>
                <input type="checkbox" name="delete" value="1" class="toggle">
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                </button>
            {% } %}
        </td>
    </tr>
	<tr id="document_details_{%=file.document_uuid%}" style='display:none;'>
		<td colspan="7">
			<div>
					<div style='border:0px solid orange'>
						<div style='width:85px; border:0px #000000 solid; float:left; margin-left:10px; margin-top:1px'>Description: </div>
						<div style='margin-top:1px'><textarea style="width:90%" type="text" name="description_{%=file.document_uuid%}" id="description_{%=file.document_uuid%}" onkeyup="scheduleSave('{%=purefile%}', '{%=file.document_uuid%}')">{%=file.description%}</textarea>
						</div>
					</div>
					<div style='border:0px solid blue; margin-top:5px'>
						<div style='width:85px; border:0px #000000 solid; float:left; margin-left:10px; margin-top:1px'>Tags: </div>
						<div style='margin-top:1px'><textarea style="width:90%" type="text" name="tags_{%=file.document_uuid%}" id="tags_{%=file.document_uuid%}"></textarea></div>
						</div>
					</div>
				</div>
		</td>
	</tr>
{% } %}
</script>
<script src="../lib/jquery.min.1.10.2.js"></script>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="js/vendor/jquery.ui.widget.js"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="js/tmpl.min.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="js/load-image.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="js/canvas-to-blob.min.js"></script>
<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
<!-- blueimp Gallery script -->
<script src="js/jquery.blueimp-gallery.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="js/jquery.fileupload.js"></script>
<!-- The File Upload processing plugin -->
<script src="js/jquery.fileupload-process.js"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="js/jquery.fileupload-image.js"></script>
<!-- The File Upload audio preview plugin -->
<script src="js/jquery.fileupload-audio.js"></script>
<!-- The File Upload video preview plugin -->
<script src="js/jquery.fileupload-video.js"></script>
<!-- The File Upload validation plugin -->
<script src="js/jquery.fileupload-validate.js"></script>
<!-- The File Upload user interface plugin -->
<script src="js/jquery.fileupload-ui.js"></script>
<!-- The main application script -->
<script src="js/main.js"></script>
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
<!--[if (gte IE 8)&(lt IE 10)]>
<script src="js/cors/jquery.xdr-transport.js"></script>
<![endif]-->
<script language="javascript">
var timeout_id;
var hidePreview = function() {
	$("#preview_panel").css({display: "none"});
}
var showPreview = function(event) {
	var element = event.currentTarget;
	if (element!=null) {
		var rect = element.getBoundingClientRect();
		
		var scrollTop = document.documentElement.scrollTop?
						document.documentElement.scrollTop:document.body.scrollTop;
		var scrollLeft = document.documentElement.scrollLeft?                   
						 document.documentElement.scrollLeft:document.body.scrollLeft;
		elementTop = rect.top+scrollTop + - 70;
		elementLeft = rect.left+scrollLeft + 110;
		
		$("#preview_panel").css({display: "", top: elementTop, left: elementLeft, position:'absolute'});
		$("#preview_panel").html("<img src='" + element.src.replace("/thumbnail/", "/medium/") + "' style='border:1px solid black'>");
	}
}
var closeDocument = function() {
	$("#view_document").css({display: "none"});
}
var showDocument = function(filepath) {
	hidePreview();
	//$("#view_document").css({display: "", top: 0, left: 0, position:'absolute'});
	//$("#view_frame").attr('src', '../templates/preview.php?file=<?php echo $_GET["case_id"]; ?>/' + filepath);
	//, 'width=900, height=500, top=200, left=200'
	window.open('../templates/preview.php?file=<?php echo $_GET["case_id"]; ?>/' + filepath, 'Preview')
}
var openDocumentDetails = function(document_uuid) {
	$("#expand_document_" + document_uuid).fadeOut(function() {
		$("#shrink_document_" + document_uuid).fadeIn();
	});
	$("#document_link_" + document_uuid).fadeOut(function() {
		$("#description_" + document_uuid).fadeIn();
	});
	$("#document_details_" + document_uuid).fadeIn();
}
var closeDocumentDetails = function(document_uuid) {
	$("#shrink_document_" + document_uuid).fadeOut(function() {
		$("#expand_document_" + document_uuid).fadeIn();
	});
	$("#document_link_" + document_uuid).fadeIn(function() {
		$("#description_" + document_uuid).fadeOut();
	});
	$("#document_details_" + document_uuid).fadeOut();
}
var clearSave = function(document_uuid) {
	var feedback = $("#feedback_" + document_uuid);
	feedback.html("");
	feedback.removeClass("alert-success");
	feedback.removeClass("alert-warning");
}
var scheduleSave = function(filename, document_uuid) {
	clearTimeout(timeout_id);
	timeout_id = setTimeout(function() {
		setType(filename, document_uuid);
	}, 2000);
}
var setType = function(filename, document_uuid, document_id) {
	//alert(obj.value + " -> <?php echo $_GET["case_id"]; ?> -> " + filename);
	$("#feedback_" + document_uuid).html('<img src="../img/searching1.gif" width="100" height="13">');
	var type = $("#type_" + document_uuid).val();
	var description = $("#description_" + document_uuid).val();
	var document_name = $("#document_name_" + document_uuid).val();
	var document_extension = $("#document_extension_" + document_uuid).val();
	//description = encodeURI(description);
	var formValues = { 
		case_id: <?php echo $_GET["case_id"]; ?>, 
		document_uuid: document_uuid, 
		document_id: document_id,
		filename : filename, 
		type: type, 
		document_name: document_name, 
		document_extension: document_extension, 
		description: description
	};
	var url = "../../api/documents/type";
	$.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
                    //$('.alert-error').text(data.error.text).show();
					$("#feedback_" + document_uuid).html("Error: " + data.error.text);
					$("#feedback_" + document_uuid).addClass("alert-warning");
                }
                else { // If not
					//we're good
					$("#feedback_" + document_uuid).html("Saved!");
					$("#feedback_" + document_uuid).addClass("alert-success");
					setTimeout(function() {
						clearSave(document_uuid);
					}, 2500);
                }
            },
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				//console.log("Status: " + textStatus); 
				//console.log("Error: " + errorThrown); 
				$("#feedback_" + document_uuid).html("Error: " + errorThrown);
			} 
        });
}
var refreshdocument_id = false;
</script>
</body> 
</html>
