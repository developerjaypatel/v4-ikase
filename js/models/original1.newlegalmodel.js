window.NewLegal = Backbone.Model.extend({
	url: function() {
		return "api/newlegal/" + this.case_id;
	},
	initialize: function(options) {
		this.case_id = options.case_id;
	},
	defaults : {
		"id" : -1,
		"uuid": "",
		"new_legal_date": "",
		"new_legal_description": "",
		"new_legal_info": "",
		"new_legal_details": "",
		"new_legal_other_details": "",
		"case_id": "",
		"deleted": "",
		"gridster_me": false
	}
});
