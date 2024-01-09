window.bulk_webmail_assign_view = Backbone.View.extend({
	render: function () {
		var self = this;
			try {
				$(self.el).html(self.template(self.model.toJSON()));
				}
			catch(err) {
				var view = "bulk_webmail_assign_view";
				var extension = "php";
				
				loadTemplate(view, extension, self);
				
				return "";
			}
		setTimeout(function() {
			var theme = {
				theme: "event",
				tokenLimit:1,
				minChars:3, 
				hintText: "Search for Kases",
				onAdd: function(item) {
					//show the notes box
					$("#notes_webmail_holder").fadeIn();
				}
			}
			$("#case_idInput").tokenInput("api/kases/tokeninput", theme);
			$("#token-input-case_idInput").focus();
		}, 500);
		return this;
	},
	triggerEdit:function() {
		$(".event .edit" ).trigger( "click" );
		
	},
	events:{
		"click .event .save": 					"addEvent",
		"change #select_all_attachments":		"selecAllAttach"
    },
	doTimeouts: function(event) {
		var self = this;
		
	},
	assignKase:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "bulk_webmail_assign", "bulk_webmail_assign");
		return;
    },
	selecAllAttach:function (event) {
		var self = this;
		$(".kase_attach_assign").prop("checked", $("#select_all_attachments").prop("checked"));
	}
});
window.webmail_assign_view = Backbone.View.extend({
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "webmail_assign_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
			try {
				$(self.el).html(self.template(self.model.toJSON()));
				}
			catch(err) {
				var view = "webmail_assign_view";
				var extension = "php";
				
				loadTemplate(view, extension, self);
				
				return "";
			}
		setTimeout(function() {
			var theme = {
				theme: "event",
				tokenLimit:1,
				minChars:3, 
				hintText: "Search for Kases",
				onAdd: function(item) {
					//show the notes box
					$("#notes_webmail_holder").fadeIn();
				}
			}
			$("#case_idInput").tokenInput("api/kases/tokeninput", theme);
		}, 500);
		
		blnMessageAssigning = false;
		
		return this;
	},
	triggerEdit:function() {
		$(".event .edit" ).trigger( "click" );
		
	},
	events:{
		"click .event .save": 					"addEvent",
		"change #select_all_attachments":		"selecAllAttach"
    },
	doTimeouts: function(event) {
		var self = this;
		
	},
	assignKase:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "webmail_assign", "webmail_assign");
		return;
    },
	selecAllAttach:function (event) {
		var self = this;
		$(".kase_attach_assign").prop("checked", $("#select_all_attachments").prop("checked"));
	}
});