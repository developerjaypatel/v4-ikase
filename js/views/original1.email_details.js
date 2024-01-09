var email_timeout_id;
window.email_view = Backbone.View.extend({
	render: function () {
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "email_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		$(this.el).html(this.template(this.model.toJSON()));
		/*
		}
		catch(err) {
			var view = "email_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		*/
		
		//we are not in editing mode initially
		this.model.set("editing", false);
		
		if (this.model.id<0) {
			$( ".email .edit" ).trigger( "click" );
		}
		setTimeout(function() {
			self.checkWebProvider();
			
			$('#show_password').on("mousedown", function() {
				document.getElementById("email_pwdInput").type = "text";
				$('#show_password').css("color", "white");
			});
			$('#show_password').on("mouseup", function() {
				document.getElementById("email_pwdInput").type = "password";
				$('#show_password').css("color", "black");
			});
		}, 1000);
		
		setTimeout(function() {
			$(".email_view .form_label_vert").css("color", "white");
			$(".email_view .form_label_vert").css("font-size", "1.2em");
		}, 100);
		
        return this;
	},
	
	events:{
		"dblclick .email .gridster_border": 		"editEmailsField",
		"click .email .save":						"scheduleAddEmail",
		"click .email .save_field":					"saveEmailsField",
		"click .email .edit": 						"scheduleEmailsEdit",
		"click .email .reset": 						"scheduleEmailsReset",
		"click #test_email":						"testEmail",
		"click #outgoing_email":					"verifyOutgoingEmail",
		"keyup #email_nameInput":					"checkWebProvider",
		"keyup #email_pwdInput":					"showEye",
		"mousedown #show_password":					"showPassword",
		"mouseup #show_password":					"hidePassword",
		"mouseover #read_messagesLabel":			"showReadInstructions",
		"mouseout #read_messagesLabel":				"hideReadInstructions",
		"click #ping_mail":							"pingMail",
		"change #read_messagesInput":				"displayReadMessages"
    },
	displayReadMessages: function() {
		if ($("#read_messagesInput").attr("checked")) {
			$("#read_messagesSpan").html("&#10003;");
		} else {
			$("#read_messagesSpan").html("N");
		}
	},
	showEye: function() {
		$("#eye_holder").fadeIn();
	},
	showPassword: function() {
		document.getElementById("email_pwdInput").type = "text";
		$('#show_password').css("color", "white");
	},
	showReadInstructions: function() {
		$("#read_messages_instructions").fadeIn();
	},
	hideReadInstructions: function() {
		$("#read_messages_instructions").fadeOut();
	},
	hidePassword: function() {
		document.getElementById("email_pwdInput").type = "password";
		$('#show_password').css("color", "black");
	},
	verifyOutgoingEmail: function(event) {
		//perform an ajax call to track views by current user
			var url = 'api/webmail/verify/outgoing';
	
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				data: null,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						$("#outgoing_email").html("<span title='" + data.success + "'>Test &#10003;</span>");
					}
				}
			});
	},
	pingMail: function(event) {
		event.preventDefault();
		
		//check_boxes.js
		pingMail();
	},
	testEmail: function(event) {
		//perform an ajax call to track views by current user
			var url = 'api/webmail/verify/incoming';
	
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				data: null,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						$("#test_email").html("<span title='Found:" + data.success + "'>Test &#10003;</span>");
					}
				}
			});
	},
	checkWebProvider: function (event) {
		var email_address = $("#email_nameInput").val();
		arrEmail = email_address.split("@");
		var blnAutoPop = false;
		var blnHideServerFields = false;
		if (arrEmail.length==2) {
			if (arrEmail[1]=="gmail.com") {
				$("#email_serverInput").val("imap.gmail.com");
				blnAutoPop = true;
				blnHideServerFields = true;
			}
			if (arrEmail[1]=="yahoo.com") {
				blnAutoPop = true;
				$("#email_serverInput").val("imap.mail.yahoo.com");
			}
			if (arrEmail[1]=="aol.com") {
				blnAutoPop = true;
				$("#email_serverInput").val("imap.aol.com");
			}
			if (blnAutoPop) {
				$("#email_methodInput").val("IMAP");
				$("#email_portInput").val("993");
				$("#ssl_requiredInput").val("Y");
			}
			if (blnHideServerFields) {
				$(".hideme").fadeOut();
				$("#activeGrid").attr("data-row", "3");
				$("#activeGrid").attr("data-col", "1");
				
				$("#read_messagesGrid").attr("data-row", "3");
				$("#read_messagesGrid").attr("data-col", "2");
			} else {
				$(".hideme").fadeIn();
				$("#activeGrid").attr("data-row", "6");
				$("#activeGrid").attr("data-col", "2");
				$("#read_messagesGrid").attr("data-row", "7");
				$("#read_messagesGrid").attr("data-col", "1");
			}
			$("#email_addressInput").val($("#email_nameInput").val());
		}
	},
	editEmailsField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".email_" + field_name;
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
			master_class = ".email_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddEmail:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(email_timeout_id);
		email_timeout_id = setTimeout(function() {
			self.addEmail(event);
		}, 200);
	},
	addEmail:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		//turn off editing to toggle
		this.model.set("editing", false);
		
		//clean up
		$("#email_addressInput").val($("#email_nameInput").val());
		addForm(event, "email");
		//turn off editing altogether
		this.model.set("editing", false);
		
		if ($("#email_pwdInput").val()!="") {
			$(".webmail_menu").show();
		}
		
		return;
    },
	saveEmailsField: function (event) {
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
	scheduleEmailsEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(email_timeout_id);
		email_timeout_id = setTimeout(function() {
			self.toggleEmailsEdit(event);
		}, 200);
	},
	toggleEmailsEdit: function (event) {
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
		toggleFormEdit("email");
	},
	scheduleEmailsReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(email_timeout_id);
		email_timeout_id = setTimeout(function() {
			self.resetEmailsForm(event);
		}, 200);
	},
	
	resetEmailsForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleEmailsEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	}
});
window.email_remote_view = Backbone.View.extend({
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
			var view = "email_remote_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}		
		
		return this;
	}
});
