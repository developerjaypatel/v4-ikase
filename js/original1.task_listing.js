window.task_print_listing = Backbone.View.extend({
	render: function(){
		var mymodel = this.model.toJSON();
		$(this.el).html(this.template({tasks: this.collection.toJSON(), title: this.model.get("title"), receive_label: this.model.get("receive_label")}));
		
		return this;
	}
});
window.task_listing = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
    },
	events: {
		"click #compose_task": 					"composeTasks",
		"click .task_action": 					"reactTask",
		"click .delete_task": 					"confirmdeleteTask",
		"click .delete_yes":					"deleteTask",
		"click .delete_no":						"canceldeleteTask",
		//"click .read_holders":				"readTask",
		"mouseover #task_preview_panel": 		"freezePreview",
		"mouseover .task_preview_link": 		"freezePreview",
		"click .edit_task":						"newTask"
	},
	composeTasks: function(event) {
		event.preventDefault();
		composeTask();
	} ,
	newTask: function(event) {
		var element = event.currentTarget;
		if (element.id != "new_task") {
			this.readTask(event);
		}
		event.preventDefault();
		composeTask(element.id);
	} ,
	reactTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeTask(element.id);
	},
	readTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var id = element.id.split("_")[2];
		if ($("#read_holder_" + id).css("display")!="none") {
			$("#read_holder_" + id).fadeOut(
				function() {
					//$("#task_row_" + id).fadeIn();
					$("#action_holder_" + id).fadeIn();
					//mark the row as read
					var url = 'api/task/read';
					formValues = "id=" + id;
			
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error tasks
								saveFailed(data.error.text);
							} else {
								//console.log(data);
								//refresh the new task indicator
								checkTaskInbox();
							}
						}
					});
				}
			);
		}
	},
	confirmdeleteTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrPosition = showDeleteConfirm(element);	
		$("#confirm_delete_task").css({display: "none", top: arrPosition[0] - 20, left: arrPosition[1] - 350, position:'absolute'});
		$("#confirm_delete_task").fadeIn();
		
	},
	canceldeleteTask: function(event) {
		event.preventDefault();
		$("#confirm_delete_task").fadeOut();
	},
	deleteTask: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "task");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".task_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".task_row_" + id).fadeOut();
			}, 2500);
		}
	},
	freezePreview: function() {
		 freezeTaskPreview();
	},
    render:function () {		
		var self = this;
		
		this.collection.bind("reset", this.render, this);
		
		if (typeof this.model.get("homepage") == "undefined") {
			this.model.set("homepage", false);
		}
		/*
		var arrToUserNames = [];
		var task = this.collection.toJSON()[0];
		var arrToUsers = task.assignee.split(";");
		arrayLength = arrToUsers.length;
		for (var i = 0; i < arrayLength; i++) {
			var theworker = worker_searches.findWhere({"nickname": arrToUsers[i]});
			if (typeof theworker != "undefined") {
				if (this.model.get("homepage") == true) {
					arrToUserNames[arrToUserNames.length] = theworker.get("nickname");
				} else {
					arrToUserNames[arrToUserNames.length] = theworker.get("user_name");
				}
			} else {
				arrToUserNames[arrToUserNames.length] = arrToUsers[i];
			}
		}
		*/
		$(this.el).html(this.template({tasks: this.collection.toJSON(), title: this.model.get("title"), receive_label: this.model.get("receive_label"), homepage: this.model.get("homepage")}));
				
		setTimeout(function() {
			tableSortIt();
		}, 100);

		return this;
    }

});