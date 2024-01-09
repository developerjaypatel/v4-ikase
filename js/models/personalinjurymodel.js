window.PersonalInjury = Backbone.Model.extend({
	url: function() {
		return "api/personalinjury/" + this.case_id;
	},
	initialize: function(options) {
		this.case_id = options.case_id;
	},
	defaults : {
		"id" : -1,
		"uuid": "",
		"personal_injury_date": "",
		"personal_injury_description": "",
		"personal_injury_info": "",
		"personal_injury_details": "",
		"personal_injury_other_details": "",
		"rental_info":"",
		"repair_info":"",
		"statute_limitation":"0000-00-00",
		"statute_interval":"730",
		"loss_date":"0000-00-00",
		"statute_years":"2",
		"case_id": "",
		"owner_id":"-1",
		"witness_count":0,
		"defendant_owner_id":"-1",
		"deleted": "",
		"gridster_me": false
	}
});
