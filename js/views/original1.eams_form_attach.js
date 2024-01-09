window.eams_form_attach = Backbone.View.extend({
    initialize:function () {

    },
    events:{
        "click .eams_form_attach_form #upload_it_five": "uploadIt"
    },
    render:function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "eams_form_attach";
			var extension = "php";
			this.model.set("holder", "eams_form_attachments");
			loadTemplate(view, extension, this);
			return "";
	   	}
		
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
									   'token'     	: token,
									   'upload_dir'	: 'eams_forms'
									 },
				'queueID'          : 'queue',
				'uploadScript'     : 'api/uploadifive.php',
				'onUploadComplete' : function(file, data) { 
					setTimeout(function() {
						//self.saveFile(data);
					}, 50);
				}
			});
		}, 500);
		
        return this;
    },
	uploadIt: function(event) {
		event.preventDefault();
		$('.eams_form_attach_form #file_upload').uploadifive('upload');
	}
});