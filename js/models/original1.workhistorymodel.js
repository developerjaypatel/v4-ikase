window.WorkHistory = Backbone.Model.extend({
	url: function() {
		return "api/workhistory/" + this.case_id;
	},
	initialize: function(options) {
		this.case_id = options.case_id;
	},
	defaults : {
		"id" : -1,
		"uuid": "",
		"work_history_info": "",
		"case_id": "",
		"deleted": "",
		"gridster_me": false
	}
});
