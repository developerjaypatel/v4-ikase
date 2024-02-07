	<h1>Upload Documents</h1>
	<form>
		<div id="queue"></div>
		<input id="file_upload" name="file_upload" type="file" multiple="true">
		<a style="position: relative; top: 8px;" href="javascript:$('#file_upload').uploadifive('upload')">Upload Files</a>
	</form>

	<script type="text/javascript">
		<?php $timestamp = time();?>
		$(function() {
			var timestamp = moment().format('YYYY-MM-DD h:mm:ss a');
			console.log("timestamp:" + timestamp);
			var token = md5('ikase_system' + timestamp);
			//				'checkScript'      : 'check-exists.php',
			$('#file_upload').uploadifive({
				'auto'             : false,
				'formData'         : {
									   'timestamp' : timestamp,
									   'token'     : token
				                     },
				'queueID'          : 'queue',
				'uploadScript'     : '../../api/uploadifive.php',
				'onUploadComplete' : function(file, data) { console.log(data); }
			});
		});
	</script>