window.Civil = Backbone.Model.extend({
	url: function() {
		return "api/civil/" + this.case_id;
	},
	initialize: function(options) {
		this.case_id = options.case_id;
	},
	defaults : {
		"id" : -1,
		"uuid": "",
		"civil_info": "",
		"civil_details": "",
		"case_id": "",
		"deleted": "",
		"gridster_me": false
	}
});
