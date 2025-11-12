window.BillingMain = Backbone.Model.extend({
	url: function() {
		return "api/billing/" + this.case_id + "/" + this.action_id + "/" + this.action_type;
	},
	initialize: function(options) {
		if (typeof options != "undefined") {
			this.case_id = options.case_id;
			this.action_id = options.action_id;
			this.action_type = options.action_type;
		}
	  },
	defaults : {
		"id" : -1,
		"uuid": "",
		"billing_id": "",
		"duration":"15",
		"hours":"",
		"billing_date":"",
		"billing_status":"",
		"billing_rate":"",
		"billing_unit":"",
		"billing_amount":"",
		"activity_code":"",
		"description":"",
		"timekeeper":"",
		"customer_id":"",
		"case_id":"",
		"action_id":"",
		"action_type":"",
		"deleted":""
	}
});
window.BillingMainCollection = Backbone.Collection.extend({
    initialize: function(options) {
		//this.case_id = options.case_id;
	 },
	model: BillingMain,
    url:"api/billings"
});
window.MedicalBilling = Backbone.Model.extend({
	url: function() {
		return "api/medicalbilling/" + this.id;
	},
	initialize: function(options) {
		this.id = options.medicalbilling_id;
	  },
	defaults : {
		"medicalbilling_id":"",
		"medicalbilling_uuid":"",
		"corporation_uuid":"",
		"user_uuid":"",
		"bill_date":"",
		"billed":0,
		"paid":0,
		"adjusted":0,
		"balance":0,
		"override":0,
		"finalized":"0000-00-00",
		"still_treating":"N",
		"prior":"N",
		"lien":"N",
		"user_id":-1, 
		"user_name":"", 
		"nickname":"",
		"corporation_id":-1,
		"company_name":"",
		"deleted":"N"
	}
});
window.MedicalBillingCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
		this.corporation_id = "";
		if (typeof options.corporation_id != "undefined") {
			this.corporation_id = options.corporation_id;
		}
	},
	model: MedicalBilling,
	url: function() {
		if (this.corporation_id=="") {
			return "api/medicalbillings/" + this.case_id;
		} else {
			return "api/corpbillings/" + this.case_id + "/" + this.corporation_id;
		}
	}
});
window.OtherBilling = Backbone.Model.extend({
	url: function() {
		return "api/otherbilling/" + this.id;
	},
	initialize: function(options) {
		this.id = options.otherbilling_id;
	  },
	defaults : {
		"otherbilling_id":"",
		"otherbilling_uuid":"",
		"corporation_uuid":"",
		"user_uuid":"",
		"bill_date":"",
		"billed":0,
		"paid":0,
		"adjusted":0,
		"balance":0,
		"override":0,
		"finalized":"0000-00-00",
		"still_treating":"N",
		"prior":"N",
		"lien":"N",
		"user_id":-1, 
		"user_name":"", 
		"nickname":"",
		"corporation_id":-1,
		"company_name":"",
		"deleted":"N"
	}
});
window.OtherBillingCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
		this.corporation_id = "";
		if (typeof options.corporation_id != "undefined") {
			this.corporation_id = options.corporation_id;
		}
	},
	model: MedicalBilling,
	url: function() {
		if (this.corporation_id=="") {
			return "api/otherbillings/" + this.case_id;
		} else {
			return "api/corpotherbillings/" + this.case_id + "/" + this.corporation_id;
		}
	}
});
window.MedicalSummary = Backbone.Model.extend({
	url: function() {
		return "";
	},
	initialize: function() {
	  },
	defaults : {
		"id":-1,
		"bill_date":"0000-00-00",
		"billed":0,
		"paid":0,
		"adjusted":0,
		"balance":0,
		"corporation_id":-1,
		"company_name":"",
		"deleted":"N"
	}
});
window.MedicalSummaryCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
	},
	model: MedicalSummary,
	url: function() {
		return "api/medicalcorpsummary/" + this.case_id;
	}
});