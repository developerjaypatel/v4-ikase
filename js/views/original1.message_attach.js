window.message_attach = Backbone.View.extend({
    initialize:function () {

    },
    events:{
        "click .message_attach_form #upload_it_five": 	"uploadIt",
		"click #select_case_documents":					"showCaseDocuments",
		"click #manage_documents_button":				"manageDocuments",
		"click #message_attach_all_done":				"doTimeouts"
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
						var hash = document.location.hash;
						if (document.location.hash.indexOf("#kases/")==0 || document.location.hash.indexOf("#kase/")==0 || document.location.hash.indexOf("#parties/")==0 ) {
							//console.log(data);
							//save the upload to the documents
							self.saveFile(data);
						}
					}, 50);
				}
			});
		}, 500);
		
		setTimeout(function() {
			//i'm a coward
			if (document.location.hash.indexOf("#kases/")==0 || document.location.hash.indexOf("#kase/")==0 || document.location.hash.indexOf("#parties/")==0 || document.location.hash.indexOf("#partielist/")==0 || document.location.hash.indexOf("#exams/")==0) {
				$(".message_attach_form #queue").hide();
				$(".message_attach_form #queue").css("height", "70px");
				$(".message_attach_form #queue").css("width", "250px");
				$(".message_attach_form #queue").css("background", "#EDEDED");
				$(".message_attach_form #queue").css("color", "black");
			} else {
				$(".message_attach_form #queue").css("height", "100px");
				$(".message_attach_form #queue").css("width", "240px");
				$(".message_attach_form").css("border","0px #000000 solid");
			}
			if (document.location.hash.indexOf("#tasks/")==0) {
				$("#uploadifive-file_upload").css("padding-top", "20px");
				$("#uploadifive-file_upload").css("line-height", "20px");
				$("#uploadifive-file_upload").css("height", "70px");
				$("#uploadifive-file_upload").css("width", "190px");
				var caption = $("#uploadifive-file_upload").html();
				if (typeof caption != "undefined") {
					caption = caption.replace("Add File...", "Drop File or Click to Upload");
					$("#uploadifive-file_upload").html(caption);
				}
				$("#uploadifive-file_upload").show();
			}
		}, 2000);
        return this;
    },
	doTimeouts: function(event) {
		var self = this;
		if (document.location.hash.indexOf("#tasks/")==0) {
			$("#uploadifive-file_upload").hide();
		}
		if (document.location.hash.indexOf("#kases/")==0 || document.location.hash.indexOf("#kase/")==0 || document.location.hash.indexOf("#parties/")==0 || document.location.hash.indexOf("#payments/")==0 || document.location.hash.indexOf("#settlement/")==0 || document.location.hash.indexOf("#bankaccount/list/trust")==0) {
			$(".message_attach_form #queue").hide();
			$(".message_attach_form #queue").css("height", "70px");
			$(".message_attach_form #queue").css("width", "250px");
			$(".message_attach_form #queue").css("background", "#EDEDED");
			$(".message_attach_form #queue").css("color", "black");
		} else {
			$(".message_attach_form #queue").css("height", "100px");
			$(".message_attach_form #queue").css("width", "240px");
			$(".message_attach_form").css("border","0px #000000 solid");
		}
		if (document.location.hash.indexOf("#notes/")==0 || document.location.hash.indexOf("#accounts/")==0) {
			$(".message_attach_form #queue").css("border", "0px");
		}
		//is this a document send
		if (self.model.get("document_id") != "" && typeof self.model.get("document_id") != "undefined") {
			//it's an array
			if (typeof self.model.get("document_id") == "object") {
				var arrID = self.model.get("document_id").toString().split(",");
			} else {
				var arrID = [self.model.get("document_id")];
			}
			var case_id = self.model.get("case_id");
			_.each( arrID, function(document_id) {
				var send_document = new Document({case_id:  case_id, "id": document_id});
				send_document.fetch({
					success: function(data) {
						var kase_document = data.toJSON();
						
						$("#send_document_id").val(kase_document.document_id);
						
						kase_document.preview_href = "api/preview.php?case_id=" + current_case_id + "&id=" + kase_document.document_id + "&file=" + encodeURIComponent(kase_document.document_filename) + "&type=" + kase_document.type + "&thumbnail_folder=" + kase_document.thumbnail_folder;
						
						//$("#send_queue").html("<a href='../uploads/" + customer_id + "/" + data.get("document_filename") + "' target='_blank' title='Click to review attached document' class='white_text'>" + data.get("document_filename") + "</a>");
						$("#send_queue").append("<div><a href='" + kase_document.preview_href + "' target='_blank' title='Click to review attached document' class='white_text'>" + kase_document.document_filename + "</a></div>");
						
						if (self.model.get("reaction")=="sendstack") {
							$("#subjectInput").val("default_message", kase_document.document_filename + " has been uploaded as part of a batch import.");
						}
					}
				});
			});
			
			if (self.model.get("reaction")=="senddocument") {
				//auto subject
				//$(".interoffice #subjectInput").val(send_document.get("document_name")); 
				var suffix = "";
				if (arrID.length > 1) {
					suffix = "s";
				}
				$(".interoffice #subjectInput").val("Forwarding " + arrID.length + " document" + suffix); 
				$(".interoffice #token-input-message_toInput").focus();
			}
			if (self.model.get("reaction")=="sendstack") {
				//auto subject
				$(".interoffice #subjectInput").val("Batch import notification"); 
			}
		}
	},
	manageDocuments: function(event) {
		event.preventDefault();
		window.open("v8.php?n=#documents/" + current_case_id);
	},
	showCaseDocuments: function(event) {
		event.preventDefault();
		$("#select_case_documents").fadeOut();
		var the_width = Number($('.modal-dialog').css("width").replace("px", ""));
		var the_left = Number($('.modal-dialog').css("margin-left").replace("px", ""));
		if ($('#message_documents_list').css("display")=="none") {
			the_left -= 180;
			the_width += 410;
		}
		$('.modal-dialog').animate({width:the_width, marginLeft: the_left + "px"}, 1100, 'easeInSine', 
		function() {
			//run this after animation
			$('#message_documents_list').fadeIn(function() {
				
			});
		});		
	},
	uploadIt: function(event) {
		event.preventDefault();
		$('.message_attach_form #file_upload').uploadifive('upload');
	},
	saveFile: function(filename) {
		var self = this;
		
		var url = 'api/documents/add';
		formValues = "verified=Y&type=&description_html=&description=applicant&document_extension=&document_filename=" + filename + "&document_name=" + filename + "&case_id=" + this.model.get("case_id") + "&document_date=" + moment().format("YYYY-MM-DD") + "&parent_document_uuid=";
		
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
					$("#manage_documents_link_holder").css("display", "inline-block");
				}
			}
		});
	}
});