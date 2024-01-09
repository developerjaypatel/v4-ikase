window.letter_attach = Backbone.View.extend({
    initialize:function () {

    },
    events:{
        "click .letter_attach_form #upload_it_five": "uploadIt"
    },
    render:function () {
		var self = this;
        $(this.el).html(this.template());
		setTimeout(function() {
			var timestamp = moment().format('YYYY-MM-DD h:mm:ss a');
			//console.log("timestamp:" + timestamp);
			var token = md5('ikase_system' + timestamp);
			$('#file_upload').uploadifive({
				'auto'             : false,
				'checkScript'      : 'check-exists.php',
				'formData'         : {
									   'timestamp' 	: timestamp,
									   'case_id'	: current_case_id,
									   'token'     	: token
									 },
				'queueID'          : 'queue',
				'uploadScript'     : 'api/uploadifive.php',
				'onUploadComplete' : function(file, data) { 
					setTimeout(function() {
						//self.saveFile(data);
						console.log(data);
					}, 50);
				}
			});
		}, 500);
		
        return this;
    },
	uploadIt: function(event) {
		event.preventDefault();
		$('.letter_attach_form #file_upload').uploadifive('upload');
	}/*,
	saveFile: function(filename) {
		var self = this;
		
		var url = 'api/documents/add';
		formValues = "verified=Y&type=&description_html=&description=applicant&document_extension=&document_filename=" + filename + "&document_name=" + filename + "&case_id=" + this.model.get("case_id") + "&case_uuid=" + this.model.get("case_uuid") + "&document_date=" + moment().format("YYYY-MM-DD") + "&parent_document_uuid=";
		
		return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error letters
					saveFailed(data.error.text);
				} else {
					//console.log(data.toJSON);
				}
			}
		});
	}
	*/
});