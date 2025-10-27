window.personal_injury_image = Backbone.View.extend({
    initialize:function () {

    },
    events:{
        "click #upload_it_five": "uploadIt"
    },
    render:function () {
		var self = this;
        try {
			var mymodel = this.model.toJSON();
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			var view = "personal_injury_image";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		
		
		setTimeout(function() {
			var timestamp = moment().format('YYYY-MM-DD h:mm:ss a');
			//console.log("timestamp:" + timestamp);
			var token = md5('ikase_system' + timestamp);
			$('#file_upload').uploadifive({
				'auto'             : false,
				'checkScript'      : 'check-exists.php',
				'formData'         : {
									   'timestamp' : timestamp,
									   'token'     : token,
									   'case_id' : self.model.get("case_id")
									 },
				'queueID'          : 'queue',
				'uploadScript'     : 'api/uploadifive.php',
				'onUploadComplete' : function(file, data) { 
					setTimeout(function() {
						self.saveFile(data);
					}, 50);
				}
			});
		}, 500);
		
        return this;
    },
	uploadIt: function(event) {
		event.preventDefault();
		$('#file_upload').uploadifive('upload');
	},
	saveFile: function(filename) {
		var self = this;
		var theattribute = $(".personal_injury_image_form #attribute").val();
		var theattribute_2 = $(".personal_injury_image_form #attribute_2").val();
		var upload_details = $(".personal_injury_image_form #upload_details").val();
		var url = 'api/documents/add';
		formValues = "verified=Y&type=&description_html=&description=personal_injury&document_extension=&document_filename=" + filename + "&document_name=" + filename + "&case_id=" + this.model.get("case_id") + "&case_uuid=" + this.model.get("case_uuid") + "&document_date=" + moment().format("YYYY-MM-DD") + "&parent_document_uuid=&attribute=" + theattribute + "&attribute_2=" + theattribute_2 + "&upload_details=" + upload_details + "&thumbnail_folder=" + this.model.get("case_id") + "/medium";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//console.log(data.toJSON);
					$('#picture_holder').html("<img src='D:/uploads/" + self.model.toJSON().customer_id + '/' + current_case_id + '/' + filename + "' class='personal_injury_img'>");
				}
			}
		});
	}
});