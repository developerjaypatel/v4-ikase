var check_import_timeout_id = false;
window.import_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
        "click #upload_it_five": "uploadIt"
    },
    render:function () {
		var self = this;
        
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "import_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		
		setTimeout(function() {
			$(".import_div").fadeIn();
			var timestamp = moment().format('YYYY-MM-DD h:mm:ss a');
			//console.log("timestamp:" + timestamp);
			var token = md5('ikase_system' + timestamp);
			var uploadScript = 'api/uploadifive.php';
			
			$('#file_upload').uploadifive({
				'auto'             : false,
				'fileType'			: ['application/pdf'],
				'checkScript'      : 'check-exists.php',
				'formData'         : {
									   'timestamp' : timestamp,
									   'token'     : token
									 },
				'queueID'          : 'queue',
				'uploadScript'     : uploadScript,
				'onUploadStart' : function(file) {
					if ($("#batch_indicator").length > 0) {
						$("#batch_indicator").html("Starting to upload " + file.name + ", please do not close this window until upload(s) completed");
					}
				},
				'onUploadComplete' : function(file, data) { 
					//console.log(data);
					//return;
					//save the file info time
					setTimeout(function() {
						self.saveFile(data);
					}, 50);
					
					if ($("#batch_indicator").length > 0) {
						$("#batch_indicator").html("Processing Upload (this may take some time.)");
					}
				}
			});	
			
			$("#queue").css("height","250px");
		}, 500);
		
		
        return this;
    },
	uploadIt: function(event) {
		event.preventDefault();
		$('#file_upload').uploadifive('upload');
	},
	saveFile: function(filename) {
		var self = this;
		
		var url = 'api/batchscan/add';
		formValues = "filename=" + filename;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					if ($("#import_type").val()=="unassigned") {
						if ($("#batch_indicator").length > 0) {
							$("#batch_indicator").html("Your uploads are being saved to our server.  It will be ready in a few seconds.  Please stand by...");
						}
						setTimeout(function() {
							self.saveDocument(filename, data.id);
						}, 50);
					}
					if ($("#import_type").val()=="batchscan") {
						//console.log(data.toJSON);
						if ($("#batch_indicator").length > 0) {
							$("#batch_indicator").html("Your import is now scheduled for batch processing.  It will be ready in a few minutes.  Please stand by for Scan Process...<i class='icon-spin4 animate-spin' style='font-size:1em; color:white'></i><br>&nbsp;");
						}
						setTimeout(function() {
							//console.log("magicTime");
							//console.log(filename + ", " + data.id);;
							self.magicTime(filename, data.id);
						}, 50);
					}
				}
			}
		});
	},
	magicTime: function(filename, batchscan_id) {
		var self = this;
		
		filename = filename.replace(".pdf", "");
		var url = 'phpOCR/magic_sync.php';
		formValues = "uploaded=" + filename + "&batchscan_id=" + batchscan_id;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					setTimeout(function() {
						//no need to do it right away, it's going to take a while to get the scan processed
						checkRemoteImports();
					}, 2173);
				}
			},
			error: function(xhr, textStatus, errorThrown){
			   //alert('Process Error:' + errorThrown);
			   if ($("#batch_indicator").length > 0) {
			   	$("#batch_indicator").html("Process Error<br>&nbsp;");
			   }
			}
		});
	},
	checkImports: function() {
		//check_stack_id is in app.js, used to time check imports
		clearTimeout(check_stack_id);
		if ($("#batch_indicator").html().indexOf("Scan Process is now completed") > -1) {
			return;
		}
		var self = this;
		checkImports(true);
		check_stack_id = setTimeout(function() {
			self.checkImports();
		}, 2300);
	},
	saveDocument: function(filename, batchscan_id) {
		var self = this;
		
		filename = filename.replace(".pdf", "");
		var url = 'phpOCR/save.php';
		formValues = "uploaded=" + filename + "&batchscan_id=" + batchscan_id + "&customer_id=" + customer_id;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					if ($("#batch_indicator").length > 0) {
						var current_html = $("#batch_indicator").html();
						$("#batch_indicator").html("Your files are ready.  <a href='#unassigneds' style='cursor:pointer; text-decoration:underline' class='white_text'>Click here to review.</a>");
						clearTimeout(check_import_timeout_id);
						check_import_timeout_id = setTimeout(function(){
							checkUnassigneds();
						}, 12100);
					}
				}
			},
			error: function(xhr, textStatus, errorThrown){
			   //alert('Process Error:' + errorThrown);
			   if ($("#batch_indicator").length > 0) {
			   	$("#batch_indicator").html("Process Error");
			   }
			}
		});
	},
	batchTime: function(filename, pages) {
		//this is deprecated, now rolled in to magic
		var self = this;
		filename = filename.replace(".pdf", "");
		var url = 'phpOCR/batch80.php';
		formValues = "uploaded=" + filename + "&pages=" + pages;

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
					if ($("#batch_indicator").length > 0) {
						$("#batch_indicator").html("<span style='font-style:italic'>Import Completed</span>");
					}
					alert("The batch import you requested is now is ready");
				}
			}
		});
	}
});
window.import_remote_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
        
    },
    render:function () {
		var self = this;
        
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "import_remote_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}		
		
		setTimeout(function() {
			self.getQueueCount();
		}, 555);
		
		return this;
	},
	getQueueCount: function() {
		var url = 'api/batchscans/queuescount';
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					if (data.queue_count > 0) {
						$("#batch_indicator").html("There are " + data.queue_count + " scan(s) queued as of " + moment().format("h:mmA"));
					} else {
						$("#batch_indicator").html("No scans queued right now");
					}
				}
			}
		});
	}
});
function relateStatus(status) {
	alert(status);
	checkImports();
}