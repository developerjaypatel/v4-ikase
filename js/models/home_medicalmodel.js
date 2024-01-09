window.HomeMedical = Backbone.Model.extend({
	url: function() {
		var thereturn = 'api/homemedical/' + this.case_id;
		return thereturn;
	},
	initialize: function(options) {
		if (typeof options != "undefined") {
			this.case_id = options.case_id;
			//this.id = options.homemedical_id;
		}
	  },
	defaults : {
		"homemedical_id" : "",
		"homemedical_uuid": "",
		"corporation_id":-1,
		"company_name":"",
		"full_address":"",
		"street":"",
		"suite":"",
		"city":"",
		"state":"",
		"zip":"",
		"phone":"",
		"recommended_by": "",
		"provider_name": "",
		"prescription": "",
		"homemedical_report": "",
		"prescription_date": "",
		"report_date": "",
		"filling_fee_paid_date": "",
		"retainer_date": "",
		"lien_filled_date": "",
		"reviewed_date": ""
	}
});
window.HomeMedicalCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: HomeMedical,
	url: function() {
		var thereturn = 'api/homemedicals/' + this.case_id;
		return thereturn;
	}
});