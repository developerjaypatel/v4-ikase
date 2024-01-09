window.LostIncome = Backbone.Model.extend({
	url: function() {
		return "api/lostincome/" + this.id;
	},
	initialize: function(options) {
		this.corporation_id = "";
		this.case_id = options.case_id;
		if (typeof options.corporation_id != "undefined") {
			this.corporation_id = options.corporation_id;
		}
	},
	defaults : {
		"id" : -1,
		"uuid": "",
		"start_lost_date": "",
		"end_lost_date": "",
		"amount": 0,
		"wage":0,
		"per":"",
		"comments":"",
		"deleted": "",
		"corporation_id":"",
		"gridster_me": false
	}
});
window.LostIncomeCollection = Backbone.Collection.extend({
    url: function() {
		if (this.corporation_id=="") {
			return "api/kase/lostincome/" + this.case_id;
		} else {
			return "api/corporation/lostincome/" + this.case_id + "/" + this.corporation_id;
		}
	},
	initialize: function(options) {
		this.corporation_id = "";
		this.case_id = options.case_id;
		if (typeof options.corporation_id != "undefined") {
			this.corporation_id = options.corporation_id;
		}
	},
	model: LostIncome
});