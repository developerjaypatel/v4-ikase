var users_timeout_id;
window.users_view = Backbone.View.extend({
	render: function () {
		var self = this;
		$(this.el).html(this.template(this.model.toJSON()));
		
		return this;
	},
	
	events:{
		"dblclick .user .gridster_border": 		"editUsersField",
		"click .user .save":					"scheduleAddUser",
		"click .user .save_field":				"saveUsersField",
		"click .user .edit": 					"scheduleUsersEdit",
		"click .user .reset": 					"scheduleUsersReset",
		"click #delete_attorney_cases":			"deleteAttorneyCases",
		"click #user_form_done":				"doTimeouts"
    },
	doTimeouts: function() {
		//we are not in editing mode initially
		this.model.set("editing", false);
		
		//gridster the users tab
		setTimeout("gridsterIt(8)", 10);
		
		if (this.model.id < 0) {
			$( ".user .edit" ).trigger( "click" );
		} else {
			this.model.caseCount("attorney");
		}
	},
	editUsersField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".user_" + field_name;
		}
		editField(element, master_class);
	},
	editdialogField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".user_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddUser:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(users_timeout_id);
		users_timeout_id = setTimeout(function() {
			self.addUser(event);
		}, 200);
	},
	addUser:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		//turn off editing to toggle
		this.model.set("editing", false);
		addForm(event);
		//turn off editing altogether
		this.model.set("editing", false);
		return;
    },
	saveUsersField: function (event) {
		console.log("save_function_start");
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		console.log("save_function_next");
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		console.log("save_function_after");
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(element_value);
		
		this.model.set(element, element_value);
		console.log("save_function_model_start");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		addForm(event);
	},
	scheduleUsersEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(users_timeout_id);
		users_timeout_id = setTimeout(function() {
			self.toggleUsersEdit(event);
		}, 200);
	},
	toggleUsersEdit: function (event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//going forward we are in editing mode until reset or save
		this.model.set("editing", true);
		toggleFormEdit("user");
	},
	scheduleUsersReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(users_timeout_id);
		users_timeout_id = setTimeout(function() {
			self.resetUsersForm(event);
		}, 200);
	},
	resetUsersForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleUsersEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	deleteAttorneyCases: function(event) {
		event.preventDefault();
		
		$("#delete_attorney_cases").fadeOut();
		
		if (!blnAdmin) {
			//one time try
			return;
		}
		
		$("#delete_attorney_cases_feedback").html("Confirm Request");
		if (confirm("Are you sure you want to delete ALL the cases for " + this.model.get("user_name") + "?") == true) {	
			var user = this.model;
			user.deleteAllKases();
			return;
		}
		
		//they cancelled
		$("#delete_attorney_cases_feedback").html("");
		$("#delete_attorney_cases").fadeIn();	
	}
});
window.user_listing_view = Backbone.View.extend({

    initialize:function () {
        this.model.on("change", this.render, this);
		this.model.on("add", this.render, this);
    },
	events: {
		"click #print_users":			"printUsers"
	},
    render:function () {		
		var self = this;
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({users: this.model.toJSON()}));
		
		tableSortIt("user_listing");
		
		return this;
    },
	printUsers: function(event) {
		event.preventDefault;
		window.open("report.php#users");
	}
});
window.user_listing_message = Backbone.View.extend({
    initialize:function () {
    },
    render:function () {	
		var self = this;
		
		mymodel = this.model.toJSON();
		
		try {
			var self = this;
			var workers = this.collection.toJSON();
			$(this.el).html(this.template({workers: workers}));
			//$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			var view = "user_listing_message";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}	
		
		
		return this;
	},
    events:{
  		"click #message_users":			"selectAll",
		"click .message_user":			"selectUser"
	},
	selectUser: function (event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var user_id = $("#user_id_" + theid).val();
		var user_name = $("#user_name_" + theid).val();
		var source_input = "#message_" + this.model.get("source").toLowerCase() + "Input";
		
			$(source_input).tokenInput("add", {id: user_id, name: user_name});		
		
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".message_user").prop("checked", element.checked);
	}
});