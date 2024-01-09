var user_timeout_id;
window.user_view = Backbone.View.extend({
	events:{
		"dblclick .user .gridster_border": 		"editUsersField",
		"click .user .save":					"scheduleAddUser",
		"click .user .save_field":				"saveUsersField",
		"click .user .edit": 					"scheduleUsersEdit",
		"click .user .reset": 					"scheduleUsersReset",
		"click .user #activity_summary":		"listActivitySummary",
		"click .delete_cases":					"deleteEmployeeCases",
		"click #user_view_done":				"doTimeouts"
    },
	render: function () {
		var self = this;
		var mymodel = this.model.toJSON();
		var job = mymodel.job;
		
		if (job.length > 20) {
			$("#jobSpan").css("font-size", "0.9em");
			$("#jobSpan").css("margin-top", "-23px");
		}
		
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			var view = "user_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		//$(this.el).html(this.template(this.model.toJSON()));
		
        return this;
	},
	listActivitySummary:function(event) {
		event.preventDefault();
		var start_date = moment().subtract(2, 'weeks').startOf('isoWeek')._d;
		start_date = moment(start_date).format("YYYY-MM-DD");
		var end_date = moment().subtract(1, 'weeks').endOf('isoWeek')._d;
		end_date = moment(end_date).format("YYYY-MM-DD");
		var url = "report.php#user/tracksummarybydate/" + this.model.id + "/" + start_date + "/" + end_date;
		window.open(url);
	},
	doTimeouts: function() {
		var self = this;
		//we are not in editing mode initially
		this.model.set("editing", false);
		
		setTimeout(function() {
			if (self.model.id<0) {
				$( ".user .edit" ).trigger( "click" );
			} else {
				self.model.caseCount("attorney");
				self.model.caseCount("supervising_attorney");
				self.model.caseCount("worker");
			}
			jscolor.install();
		}, 600);
		
		$("#user_form .form_label_vert").css("font-size", "1em");
		$("#user_form .form_label_vert").css("color", "white");
		
		var adhoc = this.model.get("adhoc");
		if (adhoc!="") {
			var jdata = JSON.parse(adhoc);
			 for(var key in jdata) {
				if(jdata.hasOwnProperty(key)) {
					//console.log(key + " --> " + jdata[key]);
					if (jdata[key]=="Y") {
						$("#" + key + "Input").attr("checked", true);
						$("#" + key + "Span").html("Y");
					} else {
						$("#" + key + "Span").html("N");
					}
				 }
			}
		}
	},
	deleteEmployeeCases: function(event) {
		event.preventDefault();
		
		var element_id = event.currentTarget.id;
		var job = element_id.replace("delete_", "");
		job = job.replace("_cases", "");
		
		$("#delete_attorney_cases").fadeOut();
		
		if (!blnAdmin) {
			//one time try
			return;
		}
		
		var display_job = job.replace("_", " ").capitalizeWords();
		if (job=="worker") {
			display_job = "Coordinator";
		}
		$("#delete_" + job + "_cases_feedback").html("Confirm Request");
		if (confirm("Are you sure you want to delete ALL the cases for " + this.model.get("user_name") + " as " + display_job + "?") == true) {	
			var user = this.model;
			user.deleteAllKases(job);
			return;
		}
		
		//they cancelled
		$("#delete_attorney_cases_feedback").html("");
		$("#delete_attorney_cases").fadeIn();	
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
		clearTimeout(user_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.addUser(event);
		}, 200);
	},
	addUser:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		//turn off editing to toggle
		this.model.set("editing", false);
		addForm(event, "user");
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
		$(field_name + "Span").html(escapeHtml(element_value));
		
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
		clearTimeout(user_timeout_id);
		user_timeout_id = setTimeout(function() {
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
		clearTimeout(user_timeout_id);
		user_timeout_id = setTimeout(function() {
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
	}
});
window.user_listing_view = Backbone.View.extend({
    initialize:function () {
        this.model.on("change", this.render, this);
		this.model.on("add", this.render, this);
    },
	events: {
		"click .delete_icon":						"confirmdeleteUser",
		"click .delete_yes":						"deleteUser",
		"click .delete_no":							"canceldeleteUser",
		"click #print_users":						"printUsers",
		"click #label_search_users":				"Vivify",
		"click #users_searchList":					"Vivify",
		"focus #users_searchList":					"Vivify",
		"blur #users_searchList":					"unVivify",
		"click .activity_summary":					"listActivitySummary"
	},
    render:function () {		
		var self = this;
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({users: this.model.toJSON()}));
		
		tableSortIt("user_listing");
		
		return this;
    },
	listActivitySummary:function(event) {
		event.preventDefault();
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[arrID.length - 1];
		
		var start_date = moment().subtract(2, 'weeks').startOf('isoWeek')._d;
		start_date = moment(start_date).format("YYYY-MM-DD");
		var end_date = moment().subtract(1, 'weeks').endOf('isoWeek')._d;
		end_date = moment(end_date).format("YYYY-MM-DD");
		var url = "report.php#user/tracksummarybydate/" + id + "/" + start_date + "/" + end_date;
		window.open(url);
	},
	printUsers: function(event) {
		event.preventDefault;
		window.open("report.php#users");
	},
	unVivify: function(event) {
		var textbox = $("#users_searchList");
		var label = $("#label_search_users");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#users_searchList");
		var label = $("#label_search_users");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	confirmdeleteUser: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element, 450);	
		$("#confirm_delete").css({display: "none", top: arrPosition[0] - 50, left: arrPosition[1] + 50, position:'absolute'});
		$("#confirm_delete").fadeIn();
	},
	canceldeleteUser: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteUser: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "user");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteUser(event);
			$(".user_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".user_data_row_" + id).fadeOut();
			}, 2500);
		}
	}
});
window.user_listing_print_view = Backbone.View.extend({

    initialize:function () {
        this.model.on("change", this.render, this);
		this.model.on("add", this.render, this);
    },

    render:function () {		
		var self = this;
		
		$(this.el).html(this.template({users: this.model.toJSON()}));
		
		
		return this;
    }

});
window.user_login_summary_list = Backbone.View.extend({

    initialize:function () {
       
    },
	events: {
		"change .report_dates":				"refreshDate"
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			var view = "user_login_summary_list";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var summaries = this.collection.toJSON();
		var mymodel = this.model.toJSON();
		
		$("#content").html("Loading...");
		$(this.el).html(this.template({summaries: summaries, employee: mymodel.employee, start_date: mymodel.start_date, end_date: mymodel.end_date}));
		
		
		return this;
    },
	refreshDate: function() {
		var start_date = $("#start_date").val();
		var end_date = $("#end_date").val();
		var user_id = this.model.get("user_id");
		
		window.Router.prototype.listUserTrackSummaryByDate(user_id, start_date, end_date);
		
		window.history.replaceState(null, null, "#user/tracksummarybydate/" + user_id + "/" + start_date + "/" + end_date);
		app.navigate("#user/tracksummarybydate/" + user_id + "/" + start_date + "/" + end_date, {trigger: false});
	}
});
window.user_listing_message = Backbone.View.extend({
    initialize:function () {
    },
    render:function () {	
		var self = this;
		
		mymodel = this.model.toJSON();
		var contacts = new PersonalContactsCollection();
		this.model.set("contacts", contacts);
		try {
			var self = this;
			var workers = this.collection.toJSON();
			$(this.el).html(this.template({workers: workers, source:this.model.get("source")}));
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
		"click .message_user":			"selectUser",
		"click #user_link_contacts":	"showContacts"
	},
	showContacts: function (event) {
		var self = this;
		
		if ($(".contact_listing").length==0) {
			//initial call
			var contacts = new PersonalContactsCollection();
			contacts.fetch({
				success: function (data) {
					//contacts = new contact_listing_message({el: $("#content"), collection: contacts}).render();			
					var mymodel = new Backbone.Model();
					mymodel.set("holder", "message_users_list");
					mymodel.set("source", self.model.get("source"));
					$('#message_users_list').html(new contact_listing_message({collection: data, model: mymodel}).render().el);
				}
			});
		} else {
			$("#user_listing_message_holder").fadeOut(function() {
				$("#contact_listing_message_holder").fadeIn();
			});
		}
	},
	selectUser: function (event) {
		var current_input = event.currentTarget;
	
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var user_id = $("#user_id_" + theid).val();
		var user_name = $("#user_name_" + theid).val();
		var source_input = "#message_" + this.model.get("source").toLowerCase() + "Input";
		
		if(current_input.checked){
			$(source_input).tokenInput("add", {id: user_id, name: user_name});
		}else{
			$(source_input).tokenInput("remove", {id: user_id, name: user_name});
		}
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".message_user").prop("checked", element.checked);
		var users = $(".message_user");
		var array_length = users.length;
		var arrUsers = [];

		var source_input = "#message_" + this.model.get("source").toLowerCase() + "Input";
		for(var i = 0; i < array_length; i++) {
			var message_user = users[i];
			var id = message_user.id.split("_")[2];
			var check = document.getElementById("message_user_" + id);
			var user_id = $("#user_id_" + id).val();
			var user_name = $("#user_name_" + id).val();
			if (check.checked) {
				$(source_input).tokenInput("add", {id: user_id, name: user_name});		
			} else {
				$(source_input).tokenInput("remove", {id: user_id, name: user_name});		
			}
		}
	}
});
window.newpassword_view = Backbone.View.extend({
	events:{
		"click #ok_password":				"savePassword"
    },
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "newpassword_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		//$(this.el).html(this.template(this.model.toJSON()));
		
        return this;
	}
});
window.session_invalid_view = Backbone.View.extend({
	events:{
		"click #ok_go":				"logOut"
    },
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "session_invalid_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		try {
			$(this.el).html(this.template());
			/*
			setTimeout(function(){
				$(".modal-header .close").hide();
			}, 100);
			*/
		}
		catch(err) {
			alert(err)
			
			return "";
		}
		
		//$(this.el).html(this.template(this.model.toJSON()));
		
        return this;
	},
	logOut: function(event) {
		event.preventDefault();
		logOut();
	}
});
window.currentuser_listing_view = Backbone.View.extend({
    initialize:function () {
    },
	events: {
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "currentuser_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({users: this.collection.toJSON()}));
		
		return this;
    }
});

window.user_listing_message_short = Backbone.View.extend({
    initialize:function () {
    },
    render:function () {	
		var self = this;
		
		mymodel = this.model.toJSON();
		
		try {
			var self = this;
			var workers = this.collection.toJSON();
			$(this.el).html(this.template({workers: workers}));			
		}
		catch(err) {
			var view = "user_listing_message_short";
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