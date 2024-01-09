window.Account = Backbone.Model.extend({
	initialize: function(options) {
		this.case_id = "";
		this.account_type = "";
		
		if (typeof options != "undefined") {
			if (typeof options.case_id != "undefined") {
				this.case_id = options.case_id;
			}
			if (typeof options.account_type != "undefined") {
				this.account_type = options.account_type;
			}
		}
	},
	defaults : {
		"id" : -1,
		"uuid": "",
		"account_type": "A",
		"bank":"",
		"account_number":"",
		"account_create_date":"",
		"customer_id":"",
		"account_info":"",
		"balance":0,
		"deposits":0,
		"withdrawals":0,
		"pendings":0,
		"adjustments":0,
		"pre_bills":0,
		"transfers":0,
		"deleted":"N"
	},
	url: function() {
		var api = "api/account/" + this.id;
		if (this.case_id!="") {
			api = "api/account/case/" + this.case_id;
			if (this.account_type!="") {
				api = "api/account/bytype/" + this.case_id + "/" + this.account_type;
			}
		} else {
			if (this.account_type!="") {
				api = "api/accounts/" + this.account_type;
			}
		}
		return api;
	}
});
window.AccountCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.account_type = "";
		if (typeof options != "undefined") {
			if (typeof options.account_type != "undefined") {
				this.account_type = options.account_type;
			}
		}
	 },
	model: Account,
    url: function() {
		var api = "";
		if (this.account_type!="") {
			//api = "api/accounts/" + this.account_type;
			api = "api/account/firmbalance/" + this.account_type;
		} else {
			api = "api/listaccounts";
		}
		return api;
	}
});