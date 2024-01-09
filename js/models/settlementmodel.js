window.Settlement = Backbone.Model.extend({
	url: function() {
		return 'api/settlement/' + this.id;
	  },
	initialize: function(options) {
		if (typeof options != "undefined") {
			if (typeof options.injury_id != "undefined") {
				this.id = options.injury_id;
			}	
		}
	  },
	defaults : {
		"id" : -1,
		"settlement_id" : -1,
		"adj_number":"",
		"start_date":"",
		"end_date":"",
		"injury_id":-1,
		"settlement_uuid": "",
		"date_submitted":"",
		"status":"",
		"date_settled":"",
		"amount_of_settlement":"",
		"pd_percent":"",
		"future_medical":"",
		"amount_of_fee":"",
		"c_and_r":"",
		"stip":"",
		"f_and_a":"",
		"date_approved":"",
		"date_fee_received":"",
		"attorney":"",
		"attorney_full_name":"",
		"gridster_me":true
	}
});
window.SettlementCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Settlement,
	url: function() {
		var thereturn = 'api/settlements/' + this.case_id;
		return thereturn;
	}
});
window.SettlementReport = Backbone.Model.extend({
	defaults: {
		"corporation_id":"",
		"company_name":"",
		"bodyparts_id":0,
		"code":"",
		"description":"",
		"case_count":0,
		"avg_settlement":0,
		"min_settlement":0,
		"max_settlement":0
	}
});
window.SettlementsByDoctorCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.doctors = options.doctors;
	 },
	model: SettlementReport,
	url: function() {
		var thereturn = 'api/settlements/bydoctor/' + this.doctors;
		return thereturn;
	}
});
window.SettlementSheet = Backbone.Model.extend({
	url: function() {
		if (this.settlement_id=="") {
			return 'api/settlementsheet/' + this.id;
		} else {
			return 'api/settlementsheetid/' + this.settlement_id;
		}
	  },
	 
	initialize: function(options) {
		this.settlement_id = "";
		if (typeof options != "undefined") {
			this.id = options.injury_id;
			if (typeof options.settlement_id != "undefined") {
				this.settlement_id = options.settlement_id;
			}
		}
	  },
	defaults : {
		"id" : -1,
		"settlementsheet_id" : -1,
		"adj_number":"",
		"date_settled":"",
		"due":"",
		"injury_id":-1,
		"settlementsheet_uuid": "",
		"data":"",
		"gridster_me":true
	}
});