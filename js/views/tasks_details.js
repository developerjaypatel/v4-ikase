window.task_view = Backbone.View.extend({
    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	initialize:function () {
		_.bindAll(this);
    },
	 events:{
		"click .task .save":					"addTaskView",
		"click #show_history":					"showTaskHistory",
		"blur #number_of_days":					"posting",		
		"click #task_all_done":					"doTimeouts",
		"click #view_task": 					"displayMain",
		"click #save_billing_modal": 			"addBill",
		"click #view_billable":  				"displayBillable"
    },
	addBill: function(event) {
		var self = this;
		var element = event.currentTarget;
		
		if (blnShowBilling) {	
			var billing_date = $("#billing_dateInput").val();
			var duration = $("#durationInput").val();
			var status = $("#billing_form #statusInput").val();
			var billing_rate = $("#billing_rateInput").val();
			var activity_code = $("#activity_codeInput").val();
			var timekeeper = $("#timekeeperInput").val();
			var description = $("#billing_form #descriptionInput").val();
			var table_id = $("#billing_form #table_id").val();
			var action_id = $("#action_id").val();
			var action_type = $("#action_type").val();
				
			var formValues = "case_id=" + current_case_id + "&billing_date=" + billing_date + "&table_id=" + table_id + "&status=" + status;
				formValues += "&duration=" + duration + "&billing_rate=" + billing_rate + "&activity_code=" + activity_code + "&timekeeper=" + timekeeper + "&description=" + description + "&action_id=" + action_id + "&action_type=" + action_type;
			
			var modal_bg = $(".modal-dialog").css('background-image');
			modal_bg = modal_bg.replace('"', "'");
			modal_bg = modal_bg.replace('"', "'");
			//console.log(modal_bg);
			//alert(modal_bg);
			//return;
			$.ajax({
			  method: "POST",
			  url: "api/billing/add",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
					  alert("error");
				  } else {
					  $("#myModalBody").css('background', "#0C3");
					  //rgb(255, 255, 255)
					  setTimeout(function() {
						  $("#myModalBody").css('background-color', '');
						  $("#myModalBody").css('background-image', modal_bg);
						  setTimeout(function() {
							  //self.displayEvent();
						  }, 700);
					  }, 2000);
				  }
			  }
			});
		}
	},
    render: function () {
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "task_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
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
			alert(err);
			return "";
		}
		
		return this;
    },
	displayMain: function(event){
		if (customer_id == 1033) {
			$("#task_screen").fadeIn();
			$("#billing_holder").fadeOut();
			$("#modal_save_holder").fadeIn();
			$('.modal-dialog').animate({width:1000, marginLeft:"-600px"}, 1100, 'easeInSine');
		}
	},
	displayBillable: function(event){
		//return;
		event.preventDefault(); // Don't let this button submit the form
		var event_id = $("#table_id").val();

		if (customer_id == 1033) {
			
			setTimeout(function() {
				$("#task_screen").fadeOut();
				$('.modal-dialog').animate({width:460, marginLeft:"-250px"}, 700, 'easeInSine');
				$("#billing_holder").fadeIn();
				$('#billing_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
				$("#modal_save_holder").fadeOut();
				//$("#timekeeperInput").tokenInput("api/user");
			}, 400);
		}
		
	},
	posting: function() {
		
		setTimeout( function() {
			var pre_save_date = $(".original_date").val();
			var current_date = $("#task_dateandtimeInput").val();
			var days_val = $("#number_of_days").val();
			
			var arrDate = current_date.split(" ");
			arrDate.splice(0, 1);
			var formValues = "days=" + days_val + "&date=" + current_date;
			
			$.ajax({
			  method: "POST",
			  url: "api/calculator_post.php",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
					  alert("error");
				  } else {
					  //alert(data[0].calculated_date + " - " + data[0].display_date + " - " + data[0].days);
					  //$("#reschedule_section").hide()
					  	  if (data[0].days == "") {
						  	  $("#task_dateandtimeInput").val(pre_save_date);
							  /*
							  if (moment(pre_save_date).format("ddd, MMM Do YYYY") != "Invalid date") { 
						  	  	$("#calculated_dateSpan").html(moment(pre_save_date).format("ddd, MMM Do YYYY"));
							  } else {
							  	$("#calculated_dateSpan").html(pre_save_date);
							  }
							  */
						  } else {
							  $("#task_dateandtimeInput").val(data[0].calculated_date + " " + arrDate.join(" "));
							  //$("#calculated_dateSpan").html(data[0].display_date);
						  }
				  }
			  }
			});
		}, 900);
	},	
	doTimeouts: function(event) {
		var self = this;
		$(".task .edit").trigger("click"); 
		$(".task .delete").hide();
		$(".task .reset").hide();
		
		//we need to upload attachments
		$('#message_attachments').html(new message_attach({model: self.model}).render().el);
		
			var field_width = 930;
			
			if ($("#preview_pane").length > 0) {
				field_width = 492;
			}
			$(".task #queue").css("height", "70px");
			$(".task #queue").css("width", field_width + "px");
			
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
		/*
		if (customer_id == "1033") {
			var bill_task_id = self.model.get('id');
			//console.log("case: " + current_case_id + "action: " + bill_event_id + "type: event");
			//return;
			$("#timekeeperInput").tokenInput("api/user");
			$(".token-input-list").css("width", "310px");
			
			
			var bill = new BillingMain({ case_id: current_case_id, action_id: bill_task_id, action_type: "task"});
			bill.fetch({
				success: function(bill) {
					bill = bill.toJSON();
					bill.billing_date = moment(bill.billing_date).format("MM/DD/YYYY hh:mma");
					$("#billing_dateInput").val(bill.billing_date);
					//return;
					$("#durationInput").val(bill.duration);
					$("#billing_form #statusInput").val(bill.billing_status);
					$("#billing_rateInput").val(bill.billing_rate);
					$("#activity_codeInput").val(bill.activity_code);
					$("#billing_form #descriptionInput").val(bill.description);
					$("#billing_id").val(bill.billing_id);
					$("#billing_form #table_id").val(bill.billing_id);
					
					if (bill.billing_id != "") {
						//setTimeout(function() {
							$("#timekeeperInput").tokenInput("add", {id: bill.timekeeper, name: bill.user_name});
							$(".token-input-list").css("width", "310px");
						//}, 2000);
					}
				}
			});
		}
		*/
		//$("#assignedInput").tokenInput("api/user", theme2);
		$(".task #case_fileInput").tokenInput("api/kases/tokeninput", theme2);
		//$(".token-input-list-task").css("width", "433px");
		$('.task #task_dateandtimeInput').datetimepicker({ validateOnBlur:false, minDate: 0, format:'m/d/Y h:ia'});
		$('.task #end_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, timepicker:false, format:'m/d/Y h:ia'});
		$('.task #callback_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
		
		$(".task #assigneeInput").tokenInput("api/user", theme);
		$(".task #ccInput").tokenInput("api/user", theme);
		
		var assigned_users = new TaskUsers([], {task_id: self.model.id, type: "to"});
		assigned_users.fetch({
			success: function (data) {
				_.each( data.toJSON(), function(task_user) {
					$(".task #assigneeInput").tokenInput("add", {
						id: task_user.user_id, 
						name: task_user.user_name
					});		
				});
			}
		});
		
		var assigned_users = new TaskUsers([], {task_id: self.model.id, type: "cc"});
		assigned_users.fetch({
			success: function (data) {
				_.each( data.toJSON(), function(task_user) {
					$(".task #ccInput").tokenInput("add", {
						id: task_user.user_id, 
						name: task_user.user_name
					});		
				});
			}
		});
		//correct the width
		$(".token-input-list-facebook").css("width", "171px");
		$(".token-input-dropdown-facebook").css("width", "171px");
		
		$(".task #task_descriptionInput").cleditor({
			width: 492,
			height: 230,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
		/*
		setTimeout(function() {
			$(".cleditorMain").css("width", "492px");
		}, 777);
		*/
		//display the case if passed
		if (self.model.case_id > 0) {
			var casing_file = $(".task #case_fileInput").val();
			if (casing_file == "") {
				var kase = kases.findWhere({case_id: self.model.case_id});				
				if (typeof kase != "undefined") {
					$(".task #case_fileInput").tokenInput("add", {
						id: self.model.case_id, 
						name: kase.name(),
						tokenLimit:1
					});
					$(".task .token-input-list-task").hide();
					$(".task #case_idSpan").html(kase.name());
				} else {
					var kase =  new Kase({id: self.model.case_id});
					kase.fetch({
						success: function (kase) {
							//add the kase to kases for faster lookup later on
							kases.add(kase);
							$(".task #case_fileInput").tokenInput("add", {
								id: self.model.case_id, 
								name: kase.name(),
								tokenLimit:1
							});
							$(".task .token-input-list-task").hide();
							$(".task #case_idSpan").html(kase.name());
							
							return;		
						}
					});
				}
			}
			//modify the task first
			$(".task #task_titleInput").select();
		} else {
			setTimeout(function() {
				//give them a chance to enter the case
				$(".task #token-input-case_fileInput").focus();
			}, 1122);
		}
		
		
		if (blnPiReady) { 
			/*
			$("#billing_time_dropdownInput").editableSelect({
				onSelect: function (element) {
					var billing_time = $("#billing_time_dropdownInput").val();
					$("#billing_time").val(billing_time);
					//alert(billing_time);
				}
			});
			*/
		}
	},
	moveModal: function(event) {
		//$('.modal-dialog').css('top', '390px');
	},
	showTaskHistory: function(event) {
		var task_tracks = new TaskTracks({task_id: this.model.id});
		task_tracks.fetch({
			success: function (data) {
				var mymodel = new Backbone.Model();
				mymodel.set("holder", "task_history");
				$('#task_history').html(new task_track_listing({collection: data, model: mymodel}).render().el);
			}
		});
	},
	editTaskViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".task_" + field_name;
		}
		editField(element, master_class);
	},
	editTaskViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".task_" + field_name;
		}
		//editField(element, master_class);
	},
	
	saveTaskViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".task_" + field_name;
		}
		element_id = element_id.replace("SaveLink", "");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		if (master_class != "") {
			//hide all the subs
			$(".span_class" + master_class).toggleClass("hidden");
			$(".input_class" + master_class).toggleClass("hidden");
			//$(field_name + "Save").toggleClass("hidden");
			
			$(".span_class" + master_class).addClass("editing");
			$(".input_class" + master_class).addClass("editing");
			$(field_name + "Save").addClass("editing");
		} else {
			//get the parent to get the class
			var theclass = element.parentElement.parentElement.parentElement.parentElement.parentElement.className;
			var arrClass = theclass.split(" ");
			theclass = "." + arrClass[0] + "." + arrClass[1];
			field_name = theclass + " #" + element_id;
			
			//restore the read look
			editField($(field_name + "Grid"));
			
			var element_value = $(field_name + "Input").val();
			$(field_name + "Span").html(escapeHtml(element_value));
		}			
		
		//this should not redraw, it will update if no id
		this.addTaskView(event);
	
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
    },
	deleteTaskView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "task");
		return;
    },
	
	toggleTaskEdit: function(event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//$("#address").show();
		//get all the editing fields, and toggle them back
		$(".task .editing").toggleClass("hidden");
		$(".task .span_class").removeClass("editing");
		$(".task .input_class").removeClass("editing");
		
		$(".task .span_class").toggleClass("hidden");
		$(".task .input_class").toggleClass("hidden");
		$(".task .input_holder").toggleClass("hidden");
		$(".button_row.task").toggleClass("hidden");
		$(".edit_row.task").toggleClass("hidden");
	},
	
	resetTaskForm: function(event) {
		event.preventDefault();
		this.toggleInjuryEdit(event);
		//this.render();
		//$("#address").hide();
	},
	valueTaskViewChanged: function(event) {
		event.preventDefault();
		//console.log(arguments[0].currentTarget.id);
		var source = arguments[0].currentTarget.id;
		source = source.replace("Input", "");
		
		var newval = $("#" + source + "Input").val();
		if (newval==""){
			if ($("#" + source + "Input").hasClass("required")) {
			newval = "Please fill me in";	
				$("#" + source + "Span").toggleClass("hidden");
			}
		} else {
			if (!$("#" + source + "Span").hasClass("hidden")) {
				$("#" + source + "Span").addClass("hidden");
			}
		}
		$("#" + source + "Span").html(escapeHtml(newval));
	}
});
var posting_timeout = false;
window.task_view_pane = Backbone.View.extend({
    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	initialize:function () {
		_.bindAll(this);
    },
	 events:{
		"click .task .save":					"addTaskView",
		"click #show_history":					"showTaskHistory",
		"change #number_of_days":				"schedulePosting",
		"click #view_task": 					"displayMain",
		"click #save_billing_modal": 			"addBill",
		"click #view_billable":  				"displayBillable",
		"click #cancel_billable":				"cancelBillable",
		"change #type_of_taskInput":			"checkForManage",
		"click .manage_type":					"manageTaskType",		
		"click #task_all_done":					"doTimeouts"
    },
	addBill: function(event) {
		var self = this;
		var element = event.currentTarget;
		
		if (customer_id == 1033) {
			
			var billing_date = $("#billing_dateInput").val();
			var duration = $("#durationInput").val();
			var status = $("#billing_form #statusInput").val();
			var billing_rate = $("#billing_rateInput").val();
			var activity_code = $("#activity_codeInput").val();
			var timekeeper = $("#timekeeperInput").val();
			var description = $("#billing_form #descriptionInput").val();
			var table_id = $("#billing_form #table_id").val();
			var action_id = $("#action_id").val();
			var action_type = $("#action_type").val();
				
			var formValues = "case_id=" + current_case_id + "&billing_date=" + billing_date + "&table_id=" + table_id + "&status=" + status;
				formValues += "&duration=" + duration + "&billing_rate=" + billing_rate + "&activity_code=" + activity_code + "&timekeeper=" + timekeeper + "&description=" + description + "&action_id=" + action_id + "&action_type=" + action_type;
			
			var modal_bg = $(".modal-dialog").css('background-image');
			modal_bg = modal_bg.replace('"', "'");
			modal_bg = modal_bg.replace('"', "'");
			//console.log(modal_bg);
			//alert(modal_bg);
			//return;
			$.ajax({
			  method: "POST",
			  url: "api/billing/add",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
					  alert("error");
				  } else {
					  $("#myModalBody").css('background', "#0C3");
					  //rgb(255, 255, 255)
					  setTimeout(function() {
						  $("#myModalBody").css('background-color', '');
						  $("#myModalBody").css('background-image', modal_bg);
						  setTimeout(function() {
							  //self.displayEvent();
						  }, 700);
					  }, 2000);
				  }
			  }
			});
		}
	},
    render: function () {
		var self = this;
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
			var view = "task_view_pane";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
    },
	checkForManage: function() {
		if ($("#type_of_taskInput").val()=="manage_task_types") {
			$("#type_of_taskInput").val("");
			composeEditTaskTypes();
		}
	},
	displayMain: function(event){
		if (blnShowBilling) {
			//hide the button
			$("#view_task").fadeOut(function() {
				var billing_employee = $("#timekeeperInput").val();
				if (billing_employee!="") {
					$("#view_billable").val("Bill Ready ✓");
					$("#view_billable").fadeIn();
					$("#cancel_billable_holder").css("display", "inline");
				} else {
					$("#view_billable").val("Not Billed");
					$("#view_billable").fadeIn();
					
					setTimeout(function() {
						$("#view_billable").val("Bill This");
					}, 2500);
				}
			});
			$("#billing_holder").fadeOut();
			//$("#modal_save_holder").fadeIn();
			$(".save").show();
			$("#task_screen").fadeIn();
		}
	},
	manageTaskType: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		composeEditTaskTypes();
	},
	cancelBillable: function(event){
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		if (blnShowBilling) {
			$("#billing_holder").html("");
			$("#myModalLabel").html("New Task");
			$("#cancel_billable_holder").fadeOut();
			$("#view_billable").val("Bill This");
		}
	},
	displayBillable: function(event){
		//return;
		event.preventDefault(); // Don't let this button submit the form
		var event_id = $("#table_id").val();

		if (blnShowBilling) {
			this.model.set("modal_title", $("#myModalLabel").html());
			$("#task_screen").fadeOut();
			$("#myModalLabel").html("Bill this Task");
			$("#cancel_billable_holder").css("display", "none");
			//hide the button
			$("#view_billable").fadeOut(function() {
				$("#view_task").val("Return to Form");
				$("#view_task").fadeIn();
			});
			
			$("#billing_holder").fadeIn();
			//$('#billing_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
			//$("#modal_save_holder").fadeOut();
			$(".save").hide();
			//already in?
			if ($("#billing_holder").html().trim() == "") {
				var bill = new BillingMain({ case_id: current_case_id, action_id: -1, action_type: "task"});
				bill.set("holder", "billing_holder");
				bill.set("billing_date", moment().format("MM/DD/YYYY"));
				bill.set("activity_category", "Task");
				bill.set("activity_id", -1);
				bill.set("case_id", $(".task #case_id").val());
				
				bill.set("activity", this.model.get("modal_title"));
				
				$("#billing_holder").html(new activity_bill_view({model: bill}).render().el);
			}
		}
	},
	schedulePosting: function() {
		var self = this;
		clearTimeout(posting_timeout);
		posting_timeout = setTimeout(function() {
			self.posting();
		}, 1000);
	},
	posting: function() {
		var pre_save_date = $(".original_date").val();
		var current_date = $("#task_dateandtimeInput").val();
		var days_val = $("#number_of_days").val();
		
		var arrDate = current_date.split(" ");
		arrDate.splice(0, 1);
		var formValues = "days=" + days_val + "&date=" + current_date;
		
		$.ajax({
		  method: "POST",
		  url: "api/calculator_post.php",
		  dataType:"json",
		  data: formValues,
		  success:function (data) {
			  if(data.error) {  // If there is an error, show the error tasks
				  alert("error");
			  } else {
				  //alert(data[0].calculated_date + " - " + data[0].display_date + " - " + data[0].days);
				  //$("#reschedule_section").hide()
					  if (data[0].days == "") {
						  $("#task_dateandtimeInput").val(pre_save_date);
						  /*
						  if (moment(pre_save_date).format("ddd, MMM Do YYYY") != "Invalid date") { 
							$("#calculated_dateSpan").html(moment(pre_save_date).format("ddd, MMM Do YYYY"));
						  } else {
							$("#calculated_dateSpan").html(pre_save_date);
						  }
						  */
					  } else {
						  $("#task_dateandtimeInput").val(data[0].calculated_date + " " + arrDate.join(" "));
						  //$("#calculated_dateSpan").html(data[0].display_date);
					  }
			  }
		  }
		});
	},	
	doTimeouts: function(event) {
		var self = this;
		$(".task .edit").trigger("click"); 
		$(".task .delete").hide();
		$(".task .reset").hide();
		
		//we need to upload attachments
		$('#message_attachments').html(new message_attach({model: self.model}).render().el);
		
			var field_width = 492;
			
			$(".task #queue").css("height", "70px");
			
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
		//var theme2 = {theme: "task"};
		var theme2 = {
			theme: "task", 
			onAdd: function(item) {
				$(".task #case_id").val(item.id);
				$("#view_billable").fadeIn();
			}
		}
		/*
		if (customer_id == "1033") {
			var bill_task_id = self.model.get('id');
			//console.log("case: " + current_case_id + "action: " + bill_event_id + "type: event");
			//return;
			$("#timekeeperInput").tokenInput("api/user");
			$(".token-input-list").css("width", "310px");
			
			
			var bill = new BillingMain({ case_id: current_case_id, action_id: bill_task_id, action_type: "task"});
			bill.fetch({
				success: function(bill) {
					bill.set("holder", "billing_holder");
					var the_bill = bill.toJSON();
					the_bill.billing_date = moment(the_bill.billing_date).format("MM/DD/YYYY hh:mma");
					bill.set("billing_date", the_bill.billing_date);
					$("#billing_dateInput").val(the_bill.billing_date);
					//return;
					$("#durationInput").val(the_bill.duration);
					$("#billing_form #statusInput").val(the_bill.billing_status);
					$("#billing_rateInput").val(the_bill.billing_rate);
					$("#activity_codeInput").val(the_bill.activity_code);
					$("#billing_form #descriptionInput").val(the_bill.description);
					$("#billing_id").val(the_bill.billing_id);
					$("#billing_form #table_id").val(the_bill.billing_id);
					
					if (the_bill.billing_id != "") {
						//setTimeout(function() {
							$("#timekeeperInput").tokenInput("add", {id: the_bill.timekeeper, name: the_bill.user_name});
							$(".token-input-list").css("width", "310px");
						//}, 2000);
					}
					
					$("#billing_holder").html(new billing_view({model: bill}).render().el);
					//$("#billing_holder").fadeIn();
				}
			});
		}
		*/
		//$("#assignedInput").tokenInput("api/user", theme2);
		$("#case_fileInput").tokenInput("api/kases/tokeninput", theme2);
	
		$('.task #task_dateandtimeInput').datetimepicker({ 
			validateOnBlur:false, 
			minDate: 0, 
			format:'m/d/Y',
			timepicker:false
		});
		$('.task #end_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, timepicker:false, format:'m/d/Y'});
		$('.task #callback_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
		
		$(".task #assigneeInput").tokenInput("api/user", theme);
		$(".task #ccInput").tokenInput("api/user", theme);
		
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
		
		var assigned_users = new TaskUsers([], {task_id: self.model.id, type: "cc"});
		assigned_users.fetch({
			success: function (data) {
				_.each( data.toJSON(), function(task_user) {
					$("#ccInput").tokenInput("add", {
						id: task_user.user_id, 
						name: task_user.user_name
					});		
				});
			}
		});
		//correct the width
		$(".token-input-list-facebook").css("width", "171px");
		$(".token-input-dropdown-facebook").css("width", "171px");
		
		$(".task #task_descriptionInput").cleditor({
			width:field_width,
			height: 130,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
		// alert(self.model.case_id);
		//display the case if passed
		if (self.model.case_id > 0) {
			var casing_file = $("#case_fileInput").val();
			if (casing_file == "") {
				// alert(self.model.case_id);
				var kase = kases.findWhere({case_id: self.model.case_id});				
				if (typeof kase != "undefined") {
					$("#case_fileInput").tokenInput("add", {
						id: self.model.case_id, 
						name: kase.name(),
						tokenLimit:1
					});
					$(".token-input-list-task").hide();
					$(".task #case_idSpan").html(kase.name());
				} else {
					// alert(self.model.case_id);
					var kase =  new Kase({id: self.model.case_id});
					kase.fetch({
						success: function (kase) {
							//add the kase to kases for faster lookup later on
							kases.add(kase);
							$("#case_fileInput").tokenInput("add", {
								id: self.model.case_id, 
								name: kase.name(),
								tokenLimit:1
							});
							
							$(".token-input-list-task").hide();
							$(".task #case_idSpan").html(kase.name());
							
							return;		
						}
					});
				}
			}
			
			var task_injury_id = self.model.get("doi_id");
			//get dois
			var kase_dois = new KaseInjuryCollection({case_id: self.model.case_id});
			kase_dois.fetch({
				success: function(kase_dois) {
					var dois = kase_dois.toJSON();
					
					var arrID = [];
					var arrOptions = [];
					var blnSelected = false;
					
					_.each( dois, function(doi) {
						if (arrID.indexOf(doi.injury_id) < 0) {
							arrID.push(doi.injury_id);
							var doi_date = moment(doi.start_date).format("MM/DD/YYYY");
							if (doi.end_date!="0000-00-00") {
								doi_date += "-" +  moment(doi.end_date).format("MM/DD/YYYY") + " CT";
							}
							var selected = "";//console.log(doi.injury_id);
							if (task_injury_id==doi.injury_id) { //console.log("939");
								selected = " selected";
								blnSelected = true;
							}
							var option = "<option value='" + doi.injury_id + "'" + selected + ">" + doi_date + "</option>";
							arrOptions.push(option);	
						}
					});
					
					if (arrOptions.length >= 1) {
						var selected = "";
						if (!blnSelected) {
							selected = " selected";
						}
						var option = "<option value='' " + selected + ">Select DOI from List - optional</option>";
						arrOptions.unshift(option);
						
						$("#doi_id").html(arrOptions.join(""));
						$("#doi_row").show();
					}
					
				},error: function(c, r) {
					console.error("Failed to fetch:", r.status, r.responseText);
				}
			}); 
			/* var kase_dois = new KaseInjuryCollection({ case_id: self.model.case_id });

			kase_dois.fetch({
				success: function (collection) { 
					var dois = collection.toJSON(); //console.log(dois);
					var arrID = [];
					var arrOptions = [];
					var blnSelected = false;

					// Replace with actual selected injury id if available
					var task_injury_id = self.model.task_injury_id || null;

					dois.forEach(function (doi) {  //console.log(doi);
						
						if (!arrID.includes(doi.injury_id)) {
							arrID.push(doi.injury_id);

							let doi_date = moment(doi.start_date).format("MM/DD/YYYY");

							if (doi.end_date && doi.end_date !== "0000-00-00") {
								doi_date += " - " + moment(doi.end_date).format("MM/DD/YYYY") + " CT";
							}

							let selected = (task_injury_id == doi.injury_id) ? " selected" : "";
							if (selected) blnSelected = true;

							let option = `<option value="${doi.injury_id}"${selected}>${doi_date}</option>`;
							arrOptions.push(option);
						}
					});
					//console.log(arrOptions.length);
					if (arrOptions.length <= 1) { //console.log("995 test");
						// Add default placeholder option
						let defaultSelected = (!blnSelected) ? " selected" : "";
						arrOptions.unshift(`<option value=""${defaultSelected}>Select DOI from List - optional</option>`);
						//console.log(arrOptions);
						// Populate dropdown
						$("#doi_id").html(arrOptions.join(""));
						$("#doi_row").show();
					}
				},
				error: function () {
					console.error("Failed to fetch KaseInjuryCollection");
				}
			});
 */
			//modify the task first
			$(".task #task_titleInput").select();
		} else {
			setTimeout(function() {
				//give them a chance to enter the case
				$(".task #token-input-case_fileInput").focus();
			}, 1122);
		}
		
		if (blnPiReady) { 
			/*
			$("#billing_time_dropdownInput").editableSelect({
				onSelect: function (element) {
					var billing_time = $("#billing_time_dropdownInput").val();
					$("#billing_time").val(billing_time);
					//alert(billing_time);
				}
			});
			*/
		}
		
		$(".task #queue").css("border", "0px");
		$(".task #message_attach_holder").css("text-align", "left");
		$(".task #message_attach_holder").css("width", "427px");
		
		//task type
		if (this.model.get("type_of_task")=="") {
			this.model.set("type_of_task", "standard");
		}
		$("#type_of_taskInput").val(this.model.get("type_of_task"));
	},
	moveModal: function(event) {
		//$('.modal-dialog').css('top', '390px');
	},
	showTaskHistory: function(event) {
		var task_tracks = new TaskTracks({task_id: this.model.id});
		task_tracks.fetch({
			success: function (data) {
				var mymodel = new Backbone.Model();
				mymodel.set("holder", "task_history");
				$('#task_history').html(new task_track_listing({collection: data, model: mymodel}).render().el);
			}
		});
	},
	editTaskViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".task_" + field_name;
		}
		editField(element, master_class);
	},
	editTaskViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".task_" + field_name;
		}
		//editField(element, master_class);
	},
	
	saveTaskViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".task_" + field_name;
		}
		element_id = element_id.replace("SaveLink", "");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		if (master_class != "") {
			//hide all the subs
			$(".span_class" + master_class).toggleClass("hidden");
			$(".input_class" + master_class).toggleClass("hidden");
			//$(field_name + "Save").toggleClass("hidden");
			
			$(".span_class" + master_class).addClass("editing");
			$(".input_class" + master_class).addClass("editing");
			$(field_name + "Save").addClass("editing");
		} else {
			//get the parent to get the class
			var theclass = element.parentElement.parentElement.parentElement.parentElement.parentElement.className;
			var arrClass = theclass.split(" ");
			theclass = "." + arrClass[0] + "." + arrClass[1];
			field_name = theclass + " #" + element_id;
			
			//restore the read look
			editField($(field_name + "Grid"));
			
			var element_value = $(field_name + "Input").val();
			$(field_name + "Span").html(escapeHtml(element_value));
		}			
		
		//this should not redraw, it will update if no id
		this.addTaskView(event);
	
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
    },
	deleteTaskView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "task");
		return;
    },
	
	toggleTaskEdit: function(event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//$("#address").show();
		//get all the editing fields, and toggle them back
		$(".task .editing").toggleClass("hidden");
		$(".task .span_class").removeClass("editing");
		$(".task .input_class").removeClass("editing");
		
		$(".task .span_class").toggleClass("hidden");
		$(".task .input_class").toggleClass("hidden");
		$(".task .input_holder").toggleClass("hidden");
		$(".button_row.task").toggleClass("hidden");
		$(".edit_row.task").toggleClass("hidden");
	},
	
	resetTaskForm: function(event) {
		event.preventDefault();
		this.toggleInjuryEdit(event);
		//this.render();
		//$("#address").hide();
	},
	valueTaskViewChanged: function(event) {
		event.preventDefault();
		//console.log(arguments[0].currentTarget.id);
		var source = arguments[0].currentTarget.id;
		source = source.replace("Input", "");
		
		var newval = $("#" + source + "Input").val();
		if (newval==""){
			if ($("#" + source + "Input").hasClass("required")) {
			newval = "Please fill me in";	
				$("#" + source + "Span").toggleClass("hidden");
			}
		} else {
			if (!$("#" + source + "Span").hasClass("hidden")) {
				$("#" + source + "Span").addClass("hidden");
			}
		}
		$("#" + source + "Span").html(escapeHtml(newval));
	}
});
window.task_print_view = Backbone.View.extend({
	render: function(){
		var mymodel = this.model.toJSON();
		
		mymodel.task_dateandtime = moment(mymodel.task_dateandtime).format('MM/DD/YYYY hh:mma');
		if (mymodel.end_date!="0000-00-00 00:00:00" && mymodel.end_date!="") {
			mymodel.end_date = moment(mymodel.end_date).format('MM/DD/YYYY');
		} else {
			mymodel.end_date = "";
		}
		if (mymodel.callback_date!="0000-00-00 00:00:00" && mymodel.callback_date!="") {
			mymodel.callback_date = moment(mymodel.callback_date).format('MM/DD/YYYY');
		} else {
			mymodel.callback_date = "";
		}
		$(this.el).html(this.template(mymodel));
		
		return this;
	}
});
window.task_type_listing = Backbone.View.extend({
	initialize:function () {
    },
	events: {
		"click #new_task_type_button":			"newTaskType",
		"click #save_task_type":				"saveTaskType",
		"click .task_type_checkbox":			"activateTaskType",
		"click .task_type_cell":				"editTaskType",
		"click .task_type_save":				"updateTaskType",
		"click #select_all_types":				"showAllTypes"
	},
    render:function () {	
		if (typeof this.template != "function") {
			var view = "task_type_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   	
		var self = this;
		
		//what are we looking for?
		
		//let's cycle through the types
		var mymodel = this.model.toJSON();
		var category_level = mymodel.category_level;
		var arrCats = this.collection.toJSON();
		var arrRows = [];
		var current_type = $("#type_of_taskInput").val();
		
		var arrOptions = [];
		var option_selected = "";
		if (current_type=="") {
			option_selected = " selected";
		}
		var option = '<option value="" class="category_option"' + option_selected + '>Select from List</option>';
		arrOptions.push(option);
		
		//arrCats.forEach(function(element, index, array) {
		
		_.each(arrCats , function(task_type) {
			if (typeof task_type.id != "undefined") {
				var index = task_type.id
				var checked = " checked";
				var row_display = "";
				var cell_display = "color:white";
				var row_class = "active_filter";
				if (task_type.deleted=="Y") {
					checked = "";
					row_display = "display:none";
					cell_display = "color:red; text-decoration:line-through;";
					row_class = "deleted_filter";
				}
				var input = "<input class='hidden' type='text' id='task_type_value_" + index + "' value='" + task_type.task_type + "' />&nbsp;<button class='btn btn-xs btn-success task_type_save hidden' id='task_type_save_" + index + "'>Save</button>";
				var therow = "<tr class='" + row_class + "' style='" + row_display + "'><td class='task_type'><input type='checkbox' class='task_type_checkbox hidden' value='Y' title='Uncheck to stop using this task_type.  Old records currently using this task_type will not be affected' id='task_type_" + index + "' name='task_type_" + index + "'" + checked + "></td><td class='task_type' style='" + cell_display + "'><span id='task_type_span_" + index + "' class='task_type_cell' style='cursor:pointer' title='Click to edit this task_type'>" + task_type.task_type + "</span>" + input + "</td></tr>";
				arrRows.push(therow);
				
				//are we using a deleted task_type
				var current_task_type = self.model.get("type_of_task");
				var blnUsingDeleted = (arrDeletedTaskType.indexOf(current_task_type) > -1);
				
				if (task_type.deleted!="Y" || blnUsingDeleted) {
					//the drop down has to match
					var option_selected = "";
					if (task_type.type_of_task == current_task_type) {
						option_selected = " selected";
					}
					var option = '<option value="' + task_type.task_type + '" class="task_type_option"' + option_selected + '>' + task_type.task_type + '</option>';
					arrOptions.push(option);
				}
			}
		});
		
		var option = '<option style="font-size: 1pt; background-color: #000000;" disabled="">&nbsp;</option>';
		arrOptions.push(option);
        var option = '<option value="manage_task_types">Manage List</option>';
		arrOptions.push(option);
		
		var html = "<table id='task_type_table'>" + arrRows.join("") + "</table>";
		try {
			$(this.el).html(this.template({html: html}));
			$("#type_of_taskInput").html(arrOptions.join("\r\n"));
		}
		catch(err) {
			alert(err);
			
			return false;
		}
		return this;
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".document_filter_checkbox").prop("checked", element.checked);
	},
	editTaskType:function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var tasktype_id = arrID[arrID.length - 1];
		
		//not open nor closed
		var current = $("#task_type_value_" + tasktype_id).val();
		if (current=="Open" || current=="Closed") {
			return;
		}
		$("#task_type_span_" + tasktype_id).fadeOut();
		$("#task_type_value_" + tasktype_id).toggleClass("hidden");
		$("#task_type_save_" + tasktype_id).toggleClass("hidden");
		$("#task_type_" + tasktype_id).toggleClass("hidden");
	},
	showAllTypes: function() {
		$("#select_all_types").hide();
		$(".deleted_filter").show();
	},
	newTaskType:function(event) {
		event.preventDefault();
		$("#new_task_type_button").fadeOut();
		$("#new_task_type_holder").fadeIn(function() {
			$("#new_task_type").focus();
		});
	},
	saveTaskType:function(event) {
		var self = this;
		
		event.preventDefault();
		var url = 'api/task_type/add';
		var mymodel = this.model.toJSON();
		
		var task_type = $("#new_task_type").val();
		var formValues = "task_type=" + task_type;
		
		var title = $(".modal-title#myModalLabel").html();
		$(".modal-title#myModalLabel").html("Saving...");
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".modal-title#myModalLabel").html("<span style='color:lime'>Saved&nbsp;&#10003;</span>");
					
					//repop the select
					var cells = $(".task_type_cell");
					var arrLength = cells.length;
					var arrOptions = [];
					var option = '<option value="">Select from List</option>';
					arrOptions.push(option);
					
					var option = '<option value="' + task_type + '">' + task_type + '</option>';
					arrOptions.push(option);
							
					for(var i =0; i < arrLength; i++) {
						var id = cells[i].id.split("_")[3];
						if (cells[i].style.display!="none" || (!$("#task_type_value_" + id).hasClass("hidden") && document.getElementById("task_type_" + id).checked)) {
							var option = '<option value="' + cells[i].innerText.toLowerCase() + '">' + cells[i].innerText + '</option>';
							arrOptions.push(option);
						}
					}
					var option = '<option style="font-size: 1pt; background-color: #000000;" disabled="">&nbsp;</option>';
					arrOptions.push(option);
					var option = '<option value="manage_task_types">Manage List</option>';
					arrOptions.push(option);
					
					$("#type_of_taskInput").html(arrOptions.join(""));
					
					setTimeout(function() {
						$(".modal-title#myModalLabel").html(title);
						var task_types = new TaskTypesCollection();
						task_types.fetch({
							success: function (data) {
								self.collection = data;
								self.render();
							}
						});
					}, 2500);
				}
			}
		});
		//
	},
	activateTaskType: function(event) {
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var tasktype_id = arrID[arrID.length - 1];
		
		$("#task_type_save_" + tasktype_id).trigger("click");
	},
	updateTaskType:function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var tasktype_id = arrID[arrID.length - 1];
		var checkbox = document.getElementById("task_type_" + tasktype_id);
		var deleted = "Y";
		if (checkbox.checked) {
			deleted = "N";
		}
		var casecategory = $("#task_type_value_" + tasktype_id).val();
		var mymodel = this.model.toJSON();
		
		var url = 'api/task_type/update';
		var formValues = "task_type_id=" + tasktype_id + "&task_type=" + casecategory + "&deleted=" + deleted;
		
		var title = $(".modal-title#myModalLabel").html();
		$(".modal-title#myModalLabel").html("Saving...");
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".modal-title#myModalLabel").html("<span style='color:lime'>Saved&nbsp;&#10003;</span>");
					
					$("#manage_category").trigger("click");
					
					//repop the select
					var cells = $(".task_type_cell");
					var arrLength = cells.length;
					var arrOptions = [];
					var option = '<option value="">Select from List</option>';
					arrOptions.push(option);
					for(var i =0; i < arrLength; i++) {
						var id = cells[i].id.split("_")[3];
						if (cells[i].style.display!="none" || (!$("#task_type_value_" + id).hasClass("hidden") && document.getElementById("task_type_" + id).checked)) {
							var option = '<option value="' + cells[i].innerText.toLowerCase() + '">' + cells[i].innerText + '</option>';
							arrOptions.push(option);
						}
					}
					var option = '<option style="font-size: 1pt; background-color: #000000;" disabled="">&nbsp;</option>';
					arrOptions.push(option);
					var option = '<option value="manage_task_types">Manage List</option>';
					arrOptions.push(option);
					$("#type_of_taskInput").html(arrOptions.join(""));
					
					setTimeout(function() {
						$(".modal-title#myModalLabel").html(title);
					}, 2500);
				}
			}
		});
		//
	}
});