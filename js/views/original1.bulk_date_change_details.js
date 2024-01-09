window.bulk_date_change_view = Backbone.View.extend({
	render: function () {
		var self = this;
			try {
				$(self.el).html(self.template(self.model.toJSON()));
				}
			catch(err) {
				var view = "bulk_date_change_view";
				var extension = "php";
				
				loadTemplate(view, extension, self);
				
				return "";
			}
		setTimeout(function() {
			$("#task_dateInput").datetimepicker({ validateOnBlur:false, minDate: 0, format:'m/d/Y h:ia'});
		}, 500);
		return this;
	},
	triggerEdit:function() {
		$(".event .edit" ).trigger( "click" );
		
	},
	events:{
		"click .event .save": 					"addEvent"
    },
	doTimeouts: function(event) {
		var self = this;
		
	},
	assignKase:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "bulk_webmail_assign", "bulk_webmail_assign");
		return;
    }
});
window.bulk_task_transfer_view = Backbone.View.extend({
	events:{
		"click #bulk_task_transfer_all_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
			try {
				$(self.el).html(self.template(self.model.toJSON()));
				}
			catch(err) {
				var view = "bulk_task_transfer_view";
				var extension = "php";
				
				loadTemplate(view, extension, self);
				
				return "";
			}
		setTimeout(function() {
			var theme = {theme: "task", tokenLimit: 1};
			$(".bulk_task_transfer #assigneeInput").tokenInput("api/user", theme);
		}, 500);
		return this;
	},
	triggerEdit:function() {
		$(".event .edit" ).trigger( "click" );
	},
	doTimeouts: function(event) {
		var self = this;
		
		var from = $(".glass_header #user_id").val();
		var from_user = worker_searches.findWhere({id: from});
		$("#transfer_from").html(from_user.get("user_name").toLowerCase().capitalizeWords());
	}
});
window.bulk_kase_transfer_view = Backbone.View.extend({
	events:{
		"click #bulk_kase_transfer_all_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
			try {
				$(self.el).html(self.template(self.model.toJSON()));
				}
			catch(err) {
				var view = "bulk_kase_transfer_view";
				var extension = "php";
				
				loadTemplate(view, extension, self);
				
				return "";
			}
		setTimeout(function() {
			var theme = {theme: "kase", tokenLimit: 1};
			$(".bulk_kase_transfer #assigneeInput").tokenInput("api/user", theme);
		}, 500);
		return this;
	},
	triggerEdit:function() {
		$(".event .edit" ).trigger( "click" );
	},
	doTimeouts: function(event) {
		var self = this;
		
		var from = $("#user_id").val();
		var from_user = worker_searches.findWhere({id: from});
		$("#transfer_from").html(from_user.get("user_name").toLowerCase().capitalizeWords());
	}
});