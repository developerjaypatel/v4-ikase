window.task_view_mobile = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	 events:{
		"click .task .save":					"addTaskView",
		"click #task_all_done":					"doTimeouts",
    },
    render: function () {
		var self = this;
		
		var kase = kases.findWhere({case_id: current_case_id});
		this.model.set("case_name","");
		
		if (typeof kase != "undefined") {
			var case_name = kase.toJSON().name
			this.model.set("case_name", case_name);
		}
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		if (this.model.id < 0) {
			var tomorrow = new Date(+new Date() + 86400000);
			this.model.set("task_dateandtime", moment(tomorrow).format('MM/DD/YYYY'));
			tomorrow = new Date(+new Date() + (86400000 * 2));
			this.model.set("end_date", moment(tomorrow).format('MM/DD/YYYY'));
		}
		
		if (this.model.get("task_name") != "" && this.model.get("task_title") == "") {
			this.model.set("task_title", this.model.get("task_name"));
		}
		if (typeof this.model.get("task_title") == "undefined") {
			this.model.set("task_title", "");
		}
		if (this.model.get("task_title").length > 75) {
			this.model.set("task_title", this.model.get("task_title").substr(0, 75) + "...");
		}
		if (typeof this.model.get("task_name") == "undefined") {
			this.model.set("task_name", "");
		}
		if (this.model.get("from") == "") {
			this.model.set("from", this.model.get("originator"));
		}
		if (typeof this.model.get("task_description") == "undefined") {
			this.model.set("task_description", "");
		}
		if (this.model.get("task_description") == "") {
			this.model.set("task_description", this.model.get("task_name"));
		}
		mymodel = this.model.toJSON();
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			var view = "task_view_mobile";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
    },	
	doTimeouts: function(event) {
		var self = this;
		$(".task .edit").trigger("click"); 
		$(".task .delete").hide();
		$(".task .reset").hide();
		
		//we need to upload attachments
		$('#message_attachments').html(new message_attach({model: self.model}).render().el);
		
			$(".task #queue").css("height", "70px");
			$(".task #queue").css("width", "550px");
			
			//does this note have any attachments
			if (self.model.id > 0) {
				var task_documents = new AttachmentCollection([], { parent_id: self.model.id, parent_table: "task" });
				task_documents.fetch({
					success: function(data) {
						var arrTaskDocuments = [];
						var arrTaskDocumentsFilename = [];
						_.each( data.toJSON(), function(task_document) {
							arrTaskDocuments[arrTaskDocuments.length] = task_document.document_id;
							arrTaskDocumentsFilename[arrTaskDocumentsFilename.length] = task_document.document_filename;
						});
						$(".task #send_document_id").val(arrTaskDocuments.join("|"));
						$(".task #send_queue").html(arrTaskDocumentsFilename.join("; "));
					}
				});	
			}
		
		
		var theme = {theme: "facebook"};
		var theme2 = {theme: "task"};
		//$("#assignedInput").tokenInput("api/user", theme2);
		//$("#case_fileInput").tokenInput("api/kases/tokeninput", theme2);
	
		$('#task_dateandtimeInput').datetimepicker({ validateOnBlur:false, minDate: 0, format:'m/d/Y h:ia'});
		$('#end_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, timepicker:false, format:'m/d/Y h:ia'});
		$('#callback_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
		
		var theme = {theme: "task"};
		//$(".task #case_idInput").tokenInput("api/kases/tokeninput", theme);					
		var theme_3 = {theme: "event"};
		$("#assigneeInput").tokenInput("api/user", theme2);
		
		var assigned_users = new TaskUsers([], {task_id: self.model.id, type: "to"});
		assigned_users.fetch({
			success: function (data) {
				_.each( data.toJSON(), function(task_user) {
					$("#assigneeInput").tokenInput("add", {
						id: task_user.user_id, 
						name: task_user.user_name
					});		
				});
			}
		});
		
		$("#task_descriptionInput").cleditor({
			width:530,
			height: 130,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
		
		//modify the task first
		$("#task_titleInput").select();
	},
	addTaskView:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		if(this.model.id==-1){
			var blnValid = $("#task").parsley('validate');
			if (blnValid) {
				setTimeout(function() {
					$(".task .save").trigger("click");
				}, 1000);
			}
		}
		addForm(event, "task");
		return;
    }
});
