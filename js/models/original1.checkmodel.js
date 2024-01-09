window.Check = Backbone.Model.extend({
	url: function() {
		var api = "api/checks/" + this.id;
		if (this.case_id != "") {
			api = "api/checkbycase/" + this.id + "/" + this.case_id
		}
		return api;
	},
	initialize:function (options) {
		if (typeof options.check_id != "undefined") {
			this.id = options.check_id;
		}
		this.case_id = "";
		if (typeof options.case_id != "undefined") {
			this.case_id = options.case_id;
		}
	},
	defaults : {
		"id" : -1,
		"check_id": -1,
		"check_uuid":"" ,
		"parent_check_uuid":"" ,
		"check_number":"",
		"check_type":"",
		"check_status":"",
		"ledger":"OUT",
		"payable_id":"",
		"from_id":"",
		"thefrom":"",
		"name":"",
		"amount_due":"",
		"method":"",
		"payment":"",
		"adjustment":"",
		"balance":"",
		"check_date":"",
		"attachments":"",
		"check_attachments":"",
		"transaction_date":"",
		"parties":"",
		"payings":"",
		"memo":"",
		"printed_date":"",
		"printed_by":"",
		"account_id":-1,
		"account_name":"",
		"account_balance":"",
		"request_payable_type":"",
		"gridster_me": false
	}
});
window.ChecksCollection = Backbone.Collection.extend({
	initialize: function(models, options) {
		this.case_id = options.case_id;
		this.fee_id = "";
		this.kinvoice_id = "";
		this.account_id = "";
		this.ledger = "";
		this.recipient = "";
		this.check_status = "";
		if (typeof options.fee_id!="undefined") {
			this.fee_id = options.fee_id;
		}
		if (typeof options.kinvoice_id!="undefined") {
			this.kinvoice_id = options.kinvoice_id;
		}
		if (typeof options.account_id!="undefined") {
			this.account_id = options.account_id;
		}
		if (typeof options.ledger!="undefined") {
			this.ledger = options.ledger;
		}
		if (typeof options.recipient!="undefined") {
			this.recipient = options.recipient;
		}
		if (typeof options.check_status!="undefined") {
			this.check_status = options.check_status;
		}
	},
	url: function() {
		var api = "";
		if(this.ledger=="") {
			//default
			api = 'api/checks/kases/' + this.case_id;
			
			if (this.kinvoice_id!="") {
				api = 'api/checks/kinvoice/' + this.kinvoice_id;
			}
			
			if (this.account_id!="") {
				api = 'api/checks/account/' + this.account_id;
			}
			
			if (this.case_id!="" && this.account_id) {
				api = 'api/checks/kaseaccount/' + this.case_id + '/' + this.account_id;
			}
		} else {
			if (this.account_id=="") {
				if (this.recipient=="") {
					api = 'api/checks/' + this.ledger.toLowerCase() + '/' + this.case_id;
				} else {
					if (this.fee_id!="") {
						api = 'api/checks/fee/' + this.fee_id;
					} else {
						api = 'api/checks/settlement/' + this.case_id+ "/" + this.recipient;
					}
				}
			} else {
				api = "api/checks/accountbyledger/" + this.account_id + "/" + this.ledger.toLowerCase();
			}
		}
		
		//status request?
		if (this.check_status!="") {
			var api = "api/checks/uncleared"
			if (this.check_status == "C") {
				api = "api/checks/cleared"
			}
			//add ledger request
			if (this.ledger!="") {
				api += "byledger/" + this.ledger.toUpperCase();
				if (this.account_id!="") {
					api += '/' + this.account_id;
				} else {
					api += '/_';
				}
			}
			
			if (this.check_status.indexOf("printed") > -1) {
				api = "api/checks/" + this.check_status;
			}
		}
		return api;
	},
	model:Check
});
var arrDeletedCostCategory = [];
window.CostCategory = Backbone.Model.extend({
	urlRoot:"api/checks/categories",
	initialize:function (options) {
	},
	defaults : {
		"id" : -1,
		"cost_type_id":-1,
		"cost_type":""
	}
});
window.CostCategoryCollection = Backbone.Collection.extend({
	initialize: function(models, options) {
	},
	url: function() {
		var api = "api/checks/categories";
		return api;
	},
	model:CostCategory
});