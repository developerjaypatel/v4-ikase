window.Document = Backbone.Model.extend({
	urlRoot:"api/document",
	initialize: function(options) {
		this.case_id = options.case_id;
	  },
	defaults : {
		"id" : -1,
		"uuid": "",
		"parent_document_uuid": "",
		"document_name": "",
		"document_date": "",
		"document_filename": "",
		"document_type" : "Document",
		"description": "",
		"thumbnail_folder": "",
		"preview_path":"",
		"path":"",
		"folder":"",
		"subfolder":"",
		"kinvoice_id":"",
		"kinvoice_name":"",
		"type": "",
		"user_name": "",
		"exam_uuid":"",
		"doi_id":"",
		"doi_start":"",
		"doi_end":"",
		"deleted": "N",
		"gridster_me": false
	}
});
window.DocumentCollection = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.case_id = options.case_id;
		if (typeof options.attribute != "undefined") {
			this.attribute = options.attribute;
		}
	 },
	model: Document,
	url: function() {
		var thereturn = 'api/documents/' + this.case_id;
		if (typeof this.attribute=="undefined") {
			this.attribute = "";
		}
		if (this.attribute!="") {
			thereturn = 'api/documents/attribute/' + this.case_id + '/' + this.attribute;
		}
		return thereturn;
	}
});
window.DocumentCaseSearch = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.case_id = options.case_id;
		this.search_term = options.search_term;
	 },
	model: Document,
	url: function() {
		var thereturn = 'api/documents/searchbycase/' + this.case_id + '/' + encodeURIComponent(this.search_term);

		return thereturn;
	}
});
window.DocumentCollectionPi = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.case_id = options.case_id;
		if (typeof options.attribute != "undefined") {
			this.attribute = options.attribute;
		}
	 },
	model: Document,
	url: function() {
		var thereturn = 'api/documents/pi/attribute/' + this.case_id + '/' + this.attribute;
		return thereturn;
	}
});
window.MessageAttachments = Backbone.Collection.extend({
    initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Document,
	url: function() {
		var thereturn = 'api/message_attachments/' + this.case_id;
		
		return thereturn;
	}
});

window.DocumentSearchCollection = Backbone.Collection.extend({
    initialize: function(models, options) {
		if (typeof options.document_name != "undefined") {
			this.document_name = options.document_name;
		}
		if (typeof options.document_start_date != "undefined") {
			this.document_start_date = options.document_start_date;
		}
		if (typeof options.document_end_date != "undefined") {
			this.document_end_date = options.document_end_date;
		}
		if (typeof options.document_type != "undefined") {
			this.document_type = options.document_type;
		}
	 },
	model: Document,
	url: function() {
		var thereturn = 'api/documents/search/' + this.document_name + '/' + this.document_type + '/' + this.document_start_date + '/' + this.document_end_date;
	
		return thereturn;
	}
});

window.ArchiveCollection = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.case_id = options.case_id;
	 },
	model: Document,
	url: function() {
		return 'api/archives/' + this.case_id;
	}
});
window.MerusArchiveCollection = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.case_id = options.case_id;
	 },
	model: Document,
	url: function() {
		return 'api/merusarchives/' + this.case_id;
	}
});
window.EcandArchiveCollection = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.case_id = options.case_id;
	 },
	model: Document,
	url: function() {
		return 'api/ecandlist/' + this.case_id;
	}
});
window.LegacyArchiveCollection = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.case_id = options.case_id;
	 },
	model: Document,
	url: function() {
		return 'api/legacy/' + this.case_id;
	}
});
window.LegacyA1Folders = Backbone.Collection.extend({
    initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Document,
	url: function() {
		return 'api/legacya1/' + this.case_id;
	}
});

window.AttachmentCollection = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.parent_id = options.parent_id;
		this.parent_table = options.parent_table;
	 },
	model: Document,
	url: function() {
		return 'api/attachments/' + this.parent_table + '/' + this.parent_id;
	}
});

window.Stacks = Backbone.Collection.extend({
    initialize: function(models, options) {
	 },
	model: Document,
    url:"api/stacks"
});
window.StacksByType = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.stack_type = options.stack_type;
		this.blnNotifications = false;
		this.blnUnassigned = false;
		if (typeof options.blnNotifications != "undefined") {
			this.blnNotifications = options.blnNotifications;
		}
		if (typeof options.blnUnassigned != "undefined") {
			this.blnUnassigned = options.blnUnassigned;
		}
	 },
	model: Document,
    url: function() {
		var api = "api/stacks/type/" + this.stack_type;
		if (this.blnNotifications) {
			api = "api/stacks/notifications";
		}
		if (this.blnUnassigned) {
			api = "api/mystacks/new";
			//api = "";
		}
		return api;
	}
});
window.MyStacksByType = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.stack_type = options.stack_type;
	 },
	model: Document,
    url: function() {
		return "api/mystacks/type/" + this.stack_type;
	}
});
window.NewScans = Backbone.Collection.extend({
    initialize: function(options) {
		
	 },
	model: Document,
    url: function() {
		return "api/mystacks/new";
	}
});
window.WordTemplates = Backbone.Collection.extend({
    initialize: function(models, options) {
		this.blnInvoices = false;
		this.case_type = "";
		if (typeof options != "undefined") {
			if (typeof options.blnInvoices != "undefined") {
				this.blnInvoices = options.blnInvoices;
			}
			if (typeof options.case_type != "undefined") {
				this.case_type = options.case_type;
			}
		}
	 },
	model: Document,
	url: function() {
    	var api = "api/templates";
		if (this.blnInvoices) {
			api = "api/templatesinv";
		}
		if (this.case_type!="") {
			api = "api/templatestype/" + this.case_type;
		}
		return api;
	}
});
window.KaseLetters = Backbone.Collection.extend({
	initialize: function(models, options) {
		this.case_id = options.case_id;
		this.blnInvoices = false;
		if (typeof options.blnInvoices != "undefined") {
			this.blnInvoices = options.blnInvoices;
		}
	},
	model: Document,
	url: function() {
		var api = 'api/letters/';
		if (this.blnInvoices) {
			api = 'api/letterinvoices/';
		} 
	   	return api + this.case_id;
  }
});
window.LetterCollection = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
  },
  model: Document,
  url: function() {
    return 'api/documents/attribute/' + this.case_id + '/letter';
  }
});
window.KaseEams = Backbone.Collection.extend({
	initialize: function(models, options) {
		this.case_id = options.case_id;
	},
	model: Document,
	url: function() {
    return 'api/eams/' + this.case_id;
  }
});
window.EamsCollection = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
  },
  model: Document,
  url: function() {
    return 'api/documents/attribute/' + this.case_id + '/eams';
  }
});