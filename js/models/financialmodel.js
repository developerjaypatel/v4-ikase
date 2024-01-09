window.Financial = Backbone.Model.extend({
	url: function() {
		if (this.corporation_id=="") {
			return "api/financial/" + this.case_id;
		} else {
			return "api/financialcarrier/" + this.case_id + "/" + this.corporation_id;
		}
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
		"financial_info": "",
		"financial_defendant": "",
		"case_id": "",
		"deleted": "",
		"corporation_id":"",
		"gridster_me": false
	}
});