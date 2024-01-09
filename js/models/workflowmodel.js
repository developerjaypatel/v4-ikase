window.Workflow = Backbone.Model.extend({
	url: function() {
		return "api/workflow/" + this.id;
	},
	initialize: function(options) {
		this.id = options.id;
	  },
	defaults : {
		"workflow_id":"",
		"workflow_date":"0000-00-00",
		"workflow_number":"",
		"description":"",
		"active":"Y",
		"activate_date":"",
		"activated_by":"",
		"activation_user":"",
		"deleted":"N"
	},
	activateFlow:function(active) {
		var url = "api/workflow/activate";
		var formValues = "workflow_id=" + this.get("id");
		formValues += "&active=" + active;
		$.ajax({
            url:url,
			type:'POST',
            dataType:"json",
			data: formValues,
            success:function (data) {
				if (data.success) {
					var current_background = $(".workflow_data_row_" + data.workflow_id).css("background");
					$(".workflow_data_row_" + data.workflow_id).css("background", "green")
					setTimeout(function() {
						window.Router.prototype.listWorkflows();
					}, 2500);
				}
            }
        });
	}
});
window.WorkflowCollection = Backbone.Collection.extend({
	initialize: function() {		
	},
	model: Workflow,
	url: function() {
		return "api/workflows";
	}
});
window.Trigger = Backbone.Model.extend({
	url: function() {
		return "api/trigger/" + this.id;
	},
	initialize: function(options) {
		this.id = options.id;
	  },
	defaults : {
		"trigger_id":"",
		"trigger_interval":"",
		"trigger_time":0,
		"trigger_actual":"",
		"trigger":"",
		"tracking_action":"",
		"operation":"",
		"action":"",
		"trigger_date":"0000-00-00",
		"deleted":"N"
	}
});
window.TriggerCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.workflow_id = options.workflow_id;
	},
	model: Trigger,
	url: function() {
		return "api/workflow/triggers/" + this.workflow_id;
	}
});