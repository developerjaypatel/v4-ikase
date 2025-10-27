<?php
include("../../text_editor/ed/datacon.php");
include("../../text_editor/ed/functions.php");

$fieldname = passed_var("fieldname");
$cus_id = passed_var("cus_id");
$suid = passed_var("suid");
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Upload</title>
<script src="../../lib/jquery.min.1.10.2.js"></script>
<script src="../../lib/jquery.uploadify.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="uploadify.css">
<style type="text/css">
body {
	font: 13px Arial, Helvetica, Sans-serif;
}
</style>
</head>

<body style="margin-top:0px">
	<form>
		<div id="queue"></div>
		<span id="title_panel"></span>
       	<table width="100%" border="0" cellpadding="2" cellspacing="0">
        	<tr>
                <td align="left" valign="top">                	
                    <label for="folder_name">Upload Type:</label>
                    <select id="folder_name">
                        <option value="admin" selected>Standard</option>
                        <option value="contracts">Contracts</option>
                        <option value="forms">Forms</option>
                    </select>
                </td>
          </tr>
          <tr>
            <td align="left" valign="top">
                <input id="file_upload" name="file_upload" type="file" multiple="true" />
            </td>
          </tr>
          <tr>
            	<td align="left" valign="top">
                	<span id="message_panel"></span>
            	</td>
          </tr>
      </table>
	</form>

	<script type="text/javascript">
		<?php $timestamp = time();?>
		$(function() {
			$('#file_upload').uploadify({
				'buttonText' 	: 'Browse for Documents',
				'swf'      : '../../api/uploadify.swf',
				'uploader' : '../../api/uploadify.php',
				'onUploadStart' : function(file) {
					var formData = {
						'timestamp' : '<?php echo $timestamp;?>',
						'token'     : '<?php echo md5('unique_salt' . $timestamp);?>',
						'id'		: '<?php echo $timestamp;?>',
						'cus_id'	: '<?php echo $cus_id;?>',
						'folder_name': $("#folder_name").val()
					};
					$('#file_upload').uploadify("settings", 'formData', formData);
				},
				'onUploadSuccess' : function(file, data, response) {
						if (data!="Invalid file type.") {
							var message = "<span><a href='D:/uploads/<?php echo $cus_id;?>/" + $("#folder_name").val() + "/" + data + "' target='_blank' title='Click to view uploaded file'>Review&nbsp;Upload</a>&nbsp;|&nbsp;<a id='indicator_link' href='javascript:parent.saveUpload(\"" + $("#folder_name").val() + "\")' title='Click here to save the upload'>Save</a></span>";
							//var message = data;
							$("#message_panel").html(message);
							parent.storeRecords(data, "<?php echo $fieldname; ?>");
						} else {
							$("#message_panel").html("You uploaded a file of forbidden type.");
						}
					}
			});
		});
	</script>
</body>
</html>