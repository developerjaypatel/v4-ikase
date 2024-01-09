window.CheckRequest = Backbone.Model.extend({
	urlRoot:"api/checkrequest",
	initialize:function (options) {
		if (typeof options.checkrequest_id != "undefined") {
			this.id = options.checkrequest_id;
		}
	},
	defaults : {
		"id" : -1,
		"checkrequest_id": -1,
		"checkrequest_uuid":"" ,
		"payable_to":"",
		"payable_full_name":"",	
		"payable_type":"",	
		"rush_request":"N",
		"request_date":"",
		"needed_date":"",
		"amount":"",
		"reason":"",
		"case_id":"",
		"case_name":"",
		"case_type":"",
		"case_status":"",
		"corp_id":"-1",
		"check_uuid":"",
		"check_id":"-1",
		"company_name":"",
		"case_settled":"",
		"gridster_me": false
	}
});
window.CheckRequestsCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = "";
		this.corp_id = "";
		this.approved = "P";
		this.byuser = "";
		this.blnAll = false;
		this.account = "";
		
		if (typeof options != "undefined") {
			if (typeof options.case_id!="undefined") {
				this.case_id = options.case_id;
			}
			if (typeof options.blnAll!="undefined") {
				this.blnAll = options.blnAll;
			}
			if (typeof options.approved!="undefined") {
				this.approved = options.approved;
			}
			
			if (typeof options.byuser!="undefined") {
				this.byuser = options.byuser;
			}
			
			if (typeof options.corp_id!="undefined") {
				this.corp_id = options.corp_id;
			}
			if (typeof options.account!="undefined") {
				this.account = options.account;
			}
		}
	},
	model:CheckRequest,
	url: function() {
		var api = "api/checkrequests";
		
		if (this.case_id!="") {
			//default
			api = 'api/checkrequests/kases/' + this.case_id;
		}		

		if (this.approved=="Y") {
			api = 'api/checkrequests/approved';
		}
		if (this.approved=="N") {
			api = 'api/checkrequests/denied';
		}
	
		
		if (this.corp_id!="") {
			api = 'api/checkrequests/corporation/' + this.corp_id;
		}
			
		if (this.byuser!="") {
			if (this.approved=="P") {
				api = 'api/checkrequests/mine/pending';
			} else {
				//my requests
				api = api.replace('api/checkrequests/', 'api/checkrequests/mine/');
			}
		}
		
		if (this.account!="") {
			api = 'api/accountrequests/' + this.account  + "/" + this.approved;
		}
		
		if (this.blnAll) {
			api = 'api/checkrequests/all';
		}
		return api;
	}
});
var arrDeletedCheckRequestCategory = [];
window.CheckRequestCategoryCollection = Backbone.Collection.extend({
	initialize: function(models, options) {
	},
	url: function() {
		var api = "api/checkrequests/categories";
		return api;
	},
	model:CostCategory
});