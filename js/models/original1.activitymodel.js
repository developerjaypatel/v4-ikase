window.Activity = Backbone.Model.extend({
	urlRoot:"api/activity",
	initialize:function () {
	},
	defaults : {
		"id" : -1,
		"activity_id" : -1,
		"activity_date": "",
		"activity": "",
		"activity_user_id": "",
		"activity_uuid": "",
		"kinvoice_id":"",
		"kinvoice_date":"",
		"kinvoice_number":"",
		"kinvoiceitem_id":"",
		"timekeeper":"",
		"duration":"15",
		"hours":"",
		"user_rate":"",
		"user_tax":"",
		"user_name":"",
		"by":"",
		"gridster_me": false
	}
});
/**/
window.SingleActivity = Backbone.Model.extend({
	url: function() {
		return "api/singleactivity/" + this.case_id + "/" + this.activity_id;
	},
	initialize:function (options) {
		if (typeof options != "undefined") {
			this.case_id = options.case_id;
			this.activity_id = options.activity_id;
		}
	},
	defaults : {
		"id" : -1,
		"activity_id" : -1,
		"activity_date": "",
		"activity": "",
		"activity_user_id": "",
		"activity_uuid": "",
		"timekeeper":"",
		"hours":"",
		"billing_date":"",
		"billing_status":"",
		"billing_rate":"",
		"billing_amount":"",
		"billing_unit":"",
		"activity_category":"",
		"description":"",
		"duration":"15",
		"timekeeper":"",
		"customer_id":"",
		"case_id":"",
		"deleted":"",
		"by":"",
		"gridster_me": false
	}
});
window.ActivityInvoice = Backbone.Model.extend({
	urlRoot:"api/invoice",
	initialize:function () {
	},
	defaults : {
		"id" : -1,
		"invoice_id" : -1,
		"invoice_date": "",
		"invoice_uuid": "",
		"total":"",
		"gridster_me": false
	}
});
window.InvoiceCollectionACT = Backbone.Collection.extend({
	initialize: function(models, options) {
		this.invoice_id = options.invoice_id;
	},
	url: function() {
		return 'api/activity/invoiceitem/' + this.invoice_id;
	},
	model:ActivityInvoice
});

window.ActivitiesCollection = Backbone.Collection.extend({
	initialize: function(models, options) {
		this.case_id = options.case_id;
		this.invoice_id = "";
		this.billing_only = false;
		this.file_access = true;
		this.kinvoice_id = "";
		if (typeof options.invoice_id != "undefined") {
			this.invoice_id = options.invoice_id;
		}
		if (typeof options.billing_only != "undefined") {
			this.billing_only = options.billing_only;
		}
		if (typeof options.file_access != "undefined") {
			this.file_access = options.file_access;
		}
		if (typeof options.kinvoice_id != "undefined") {
			this.kinvoice_id = options.kinvoice_id;
		}
	},
	url: function() {
		var thereturn = 'api/activity/kases/' + this.case_id;
		if (this.invoice_id != "") {
			thereturn += "/" + this.invoice_id;
		}
		if (this.kinvoice_id!="") {
			thereturn = 'api/activity/billing/' + this.case_id + '/' + this.kinvoice_id;
			this.file_access = false;
		}
		if (this.file_access) {
			thereturn = 'api/activity/file_access/' + this.case_id;
		}
		return thereturn;
	},
	model:Activity
});
window.ActivitiesCollectionCheckedInvoice = Backbone.Collection.extend({
	initialize: function(models, options) {
		this.case_id = options.case_id;
	},
	url: function() {
		return 'api/activity/invoiceitemfull/' + this.invoice_id;
	},
	model:ActivityInvoice
});
window.ActivityInvoiceCollection = Backbone.Collection.extend({
	initialize: function(models, options) {
		this.case_id = options.case_id;
	},
	url: function() {
		return 'api/activity/invoices/' + this.case_id;
	},
	model:Activity
});
window.ActivitiesBillingCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
	},
	url: function() {
		return 'api/activity/billing/' + this.case_id;
	},
	model:Activity
});
window.ActivityReportCollection = Backbone.Collection.extend({
	initialize: function(models, options) {
		this.user_id = options.user_id;
		this.start_date = options.start_date;
		this.end_date = options.end_date;
	},
	url: function() {
		this.start_date = moment(this.start_date).format("YYYY-MM-DD");
		this.end_date = moment(this.end_date).format("YYYY-MM-DD");
		return 'api/activity/report/' + this.user_id + '/' + this.start_date + '/' + this.end_date;
	},
	model:Activity
});
window.ActivityStacks = Backbone.Collection.extend({
	initialize: function(models, options) {
		if (typeof options.start_date == "undefined") {
			options.start_date = "0000-00-00";
		}
		if (typeof options.end_date == "undefined") {
			options.end_date = "0000-00-00";
		}
		this.end_date = options.end_date;
		this.start_date = options.start_date;
	},
	url: function() {
		return 'api/activity/stacks/' + this.start_date + '/' + this.end_date;
	},
	model:Activity
});
window.LastActivities = Backbone.Collection.extend({
	initialize: function(models, options) {
	},
	url: function() {
		return 'api/lastactivity';
	},
	model:Activity
});
window.SummaryActivities = Backbone.Collection.extend({
	initialize: function(options) {
		this.start_date = options.start_date;
		this.end_date = options.end_date;
	},
	url: function() {
		return 'api/activity/summary/' + this.start_date + '/' + this.end_date;
	},
	model:Activity
});
window.Demographic = Backbone.Model.extend({
	urlRoot:"api/activities/demographic",
	initialize:function () {
	},
	defaults : {
		"id" : -1,
		"activity_id" : -1,
		"activity_date": "",
		"activity": "",
		"activity_user_id":"",
		"user_name":"",
		"case_id":"",
		"case_name":"",
		"case_number":"",
		"file_number":"",
		"full_name":"",
		"person_id":"",
		"gridster_me": false
	}
});
window.DemographicCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		return 'api/activities/demographics';
	},
	model:Demographic
});
window.KaseRate = Backbone.Model.extend({
	url:function() {
		var api = "api/rate/" + this.id;
		if (this.case_type != "") {
			api = "api/ratebytype/" + this.case_type;
		}
		return api;
	},
	initialize: function(options) {	
		this.case_type = "";
		if (typeof options != "undefined") {
			if (typeof options.case_type != "undefined") {
				this.case_type = options.case_type;
			}
		}
	},
	defaults : {
		"id" : -1,
		"rate_id":-1,
		"create_date":"",
		"rate_description":"",
		"rate_name":"",
		"html":"",
		"rate_info":""
	}
});