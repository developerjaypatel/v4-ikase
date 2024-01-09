window.contact_view = Backbone.View.extend({

    initialize:function () {
        
    },
	events: {
		"click .contact .save":						"addContact",
		"click .contact .edit":						"editContact",
		"click .contact .reset":					"resetContact",
		"click #review_kases":						"reviewKases",
		"click #review_emails":						"reviewEmails",
		"click #contact_view_done":					"doTimeouts"
	},
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "contact_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
        var self = this;
		var spam_status = this.model.get("spam_status");
		
		this.model.set("spam_status_ok", "");
		this.model.set("spam_status_blocked", "");
		if (spam_status=="OK") {
			this.model.set("spam_status_ok", "selected");
		} else {
			this.model.set("spam_status_blocked", "selected");
		}
		$(this.el).html(this.template(this.model.toJSON()));
		
        return this;
    },
	editContact: function (event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		this.model.set("editing", true);
		//going forward we are in editing mode until reset or save
		toggleFormEdit("contact");
	},
	resetContact: function(event) {
		event.preventDefault();
		this.model.set("editing", true);
		this.editContact(event);
	},
	addContact:function(event) {
		event.preventDefault();
		var self = this;
		setTimeout(function() {
			self.saveContact(event);
		}, 200);
	},
	saveContact:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		$("#edit_row").hide();
		$("#gifsave").css("margin-top", "-5px");
		$("#gifsave").show();
		
		this.model.set("editing", false);
		addForm(event, "contact");
		return;
	},
	reviewKases: function(event) {
		event.preventDefault();
		
		var contact_id = this.model.id;
		var holder = "contact_kases_holder";
		
		var url = "api/contact/cases/" + contact_id;
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				var mymodel = new Backbone.Model({
					"holder": holder, 
					"embedded": true,
					"page_title": "Contact Kases"
				});
	
				$('#' + holder).html(new account_kases_listing_view({collection: data, model: mymodel}).render().el);
			}
		});
	},
	reviewEmails: function(event) {
		event.preventDefault();
		
		var contact_id = this.model.id;
		var holder = "content";
		
		var messages = new MessageCollection({contact_id: contact_id});
		messages.fetch({
			success: function (data) {
				var message_listing_info = new Backbone.Model;
				message_listing_info.set("title", "Contact Messages");
				message_listing_info.set("receive_label", "Sent");
				message_listing_info.set("first_column_label", "");
				message_listing_info.set("holder", holder);
				message_listing_info.set("contact_id", contact_id);
				$('#' + holder).html(new message_listing({collection: data, model: message_listing_info}).render().el);
			}
		});	
	},
	doTimeouts: function() {
		gridsterById('gridster_contact');
	}
});
window.contact_listing_view = Backbone.View.extend({

    initialize:function () {

    },
	events: {
		"click .compose_message":						"newMessage",
		"click .delete_icon":							"confirmdeleteContact",
		"click .delete_yes":							"deleteContact",
		"click .delete_no":								"canceldeleteContact",
		"click #note_clear_search":						"clearSearch",
		"click #label_search_contact":					"Vivify",
		"click #contacts_searchList":						"Vivify",
		"focus #contacts_searchList":						"Vivify",
		"blur #contacts_searchList":						"unVivify",
	},
    render:function () {	
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "contact_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}	
		var self = this;
		
		$(this.el).html(this.template({contacts: this.collection.toJSON()}));
		
		tableSortIt("contact_listing");
		
		return this;
    },
	confirmdeleteContact: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element, 450);	
		$("#confirm_delete").css({display: "none", top: arrPosition[0] - 50, left: arrPosition[1] + 50, position:'absolute'});
		$("#confirm_delete").fadeIn();
	},
	canceldeleteContact: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteContact: function(event) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		this.model.set("editing", true);
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "contact");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteContact(event);
			$(".user_data_row_" + id).css("background", "red");
			this.model.set("editing", false);
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".user_data_row_" + id).fadeOut();
			}, 2500);
		}
	},
	newMessage: function(event) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		this.model.set("editing", true);
		var element = event.currentTarget;
		event.preventDefault();
		composeMessage(element.id);
	},
	clearSearch: function() {
		$("#contacts_searchList").val("");
		$( "#contacts_searchList" ).trigger( "keyup" );
	},
	unVivify: function(event) {
		var textbox = $("#contacts_searchList");
		var label = $("#label_search_contacts");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#contacts_searchList");
		var label = $("#label_search_contacts");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#contacts_searchList').focus();
		}
	}
});
window.contact_listing_message = Backbone.View.extend({
    initialize:function () {
    },
    render:function () {	
		var self = this;
		if (typeof this.template != "function") {
			
			var view = "contact_listing_message";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		mymodel = this.model.toJSON();
		try {
			var self = this;
			var contacts = this.collection.toJSON();
			$(this.el).html(this.template({contacts: contacts, source:this.model.get("source")}));
			//$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			loadTemplate(view, extension, self);
			
			return "";
		}	
		
		
		return this;
	},
    events:{
  		"click #message_contacts":			"selectAll",
		"click .message_contact":			"selectContact",
		"click #user_link_employees":		"showWorkers"
	},
	showWorkers: function (event) {
		var self = this;
		if ($(".user_listing").length==0) {
			var mymodel = new Backbone.Model();
			mymodel.set("holder", "message_users_list");
			mymodel.set("source", self.model.get("source"));
			$('#message_users_list').html(new user_listing_message({collection: worker_searches, model: mymodel}).render().el);
		} else {
			$("#contact_listing_message_holder").fadeOut(function() {
				$("#user_listing_message_holder").fadeIn();
			});
		}
	},
	selectContact: function (event) {
		var current_input = event.currentTarget;
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var user_id = $("#contact_id_" + theid).val();
		var user_name = $("#contact_name_" + theid).val();
		var source_input = "#message_" + this.model.get("source").toLowerCase() + "Input";
		$(source_input).tokenInput("add", {id: user_id, name: user_name});
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".message_contact").prop("checked", element.checked);
		var users = $(".message_contact");
		var array_length = users.length;
		var arrUsers = [];

		var source_input = "#message_" + this.model.get("source").toLowerCase() + "Input";
		for(var i = 0; i < array_length; i++) {
			var message_contact = users[i];
			var id = message_contact.id.split("_")[2];
			var check = document.getElementById("message_contact_" + id);
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