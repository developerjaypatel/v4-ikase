//we need a model for kinvoice, and kinvoiceitem
window.KInvoice = Backbone.Model.extend({
	initialize: function() {
	},
	defaults : {
		"id" : -1,
		"case_id": -1,
		"case_name": "",
		"case_number": "",
		"file_number": "",
		"uuid": "",
		"kinvoice_date":"",
		"notification_date":"",
		"reminder_date":"",
		"paid_date":"",
		"start_date":"",
		"end_date":"",
		"kinvoice_number":"",
		"total":"",
		"payments":"",
		"deleted":"N",
		"template":"",
		"template_name":"",
		"document_id": 0,
		"document_filename":"",
		"parent_id": 0,
		"parent_filename":"",
		"sent_status": "",
		"sent_date": "",
		"customer_id": 0,
		"gridster_me": false
	},
  	urlRoot: function() {
		return 'api/kinvoice/';
	}
});
window.KInvoiceCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.account_type = "";
		this.case_id = "";
		if (typeof options.case_id !="undefined") {
			this.case_id = options.case_id;
		}
		if (typeof options.account_type !="undefined") {
			this.account_type = options.account_type;
		}
	 },
	model: KInvoice,
	url: function() {
		var api = 'api/firminvoices';
		if (this.account_type != "") {
			api += "/" + this.account_type;
		}
		if (this.case_id != "") {
			api = 'api/kinvoices/' + this.case_id;
		}
		
		return api;
	}
});
window.KaseDocumentKInvoices = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
		this.document_id = options.document_id;
	 },
	model: KInvoice,
	url: function() {
		return 'api/kinvoicecase/' + this.case_id + '/' + this.document_id;
	}
});
window.KInvoiceItem = Backbone.Model.extend({
	initialize: function() {
		
	 },
	defaults : {
		"id" : -1,
		"case_id": -1,
		"uuid": "",
		"kinvoice_id":-1,
		"hourly_rate":0,
		"item_name":"",
		"item_description":"",
		"amount":0,
		"unit":"",
		"template_name":"",
		"deleted":"N",
		"customer_id": 0,
		"gridster_me": false
	},
  	urlRoot: function() {
		return 'api/kinvoiceitem/';
	}
});
window.KInvoiceItemsCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.kinvoice_id = options.kinvoice_id;
		this.document_id = "";
		if (typeof options.document_id != "undefined") {
			this.document_id = options.document_id;
		}
	},
	model: KInvoiceItem,
	url: function() {
		var api = 'api/kinvoiceitems/' + this.kinvoice_id;
		if (this.document_id!="") {
			//dont think i need this?
		}
		return api;
	}
});
window.TemplateKInvoices = Backbone.Collection.extend({
	initialize: function(options) {
		this.document_id = "";
		if (typeof options.document_id!="undefined") {
			this.document_id = options.document_id;
		}
	 },
	model: KInvoice,
	url: function() {
		if (this.document_id!="") {
			return 'api/kinvoicetemplates/' + this.document_id;
		} else {
			return 'api/kinvoicetemplatelists';
		}
	}
});
