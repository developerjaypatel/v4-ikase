window.Message = Backbone.Model.extend({
	initialize: function(options) {
		this.id = options.message_id;
		this.case_id = options.case_id;
	},
	defaults : {
		"id" : -1,
		"case_id": -1,
		"uuid": "",
		"from": "",
		"message_to": "",
		"message_cc": "",
		"message_bcc": "",
		"message":"",
		"subject":"",
		"dateandtime":"",
		"attachments":"",
		"priority":"",
		"deleted":"N",
		"read_status":"",
		"read_date":"",
		"reply_date":"",
		"forward_date":"",
		"glass":"",
		"customer_id": 0,
		"gridster_me": false
	},
  	urlRoot: function() {
		return 'api/messages/';
	}
});
window.MessageCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.contact_id = "";
		if (typeof options != "undefined") {
			if (typeof options.contact_id != "undefined") {
				this.contact_id = options.contact_id;
			}
		}
	 },
	model: Message,
	url: function() {
		if (this.contact_id == "") {
			return 'api/messages';
		} else {
			return 'api/contactmessages/' + this.contact_id;
		}
	  },
});
window.InboxCollection = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Message,
	url: function() {
		return 'api/inbox';
	  },
});
window.ThreadInboxCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.pending = false;
		if (typeof options != "undefined") {
			if (typeof options.pending != "undefined") {
				this.pending = (options.pending=="y");
			}
		}
		this.isNew = false;
		if (typeof options != "undefined") {
			if (typeof options.isNew != "undefined") {
				this.isNew = options.isNew;
			}
		}
	 },
	model: Message,
	url: function() {
		var api = 'api/thread/inbox';
		if (this.pending) {
			api = 'api/thread/pendings';
		}
		if (this.isNew) {
			api = 'api/inboxnew';
		}
		return api;
	  },
});
window.ThreadOutboxCollection = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Message,
	url: function() {
		return 'api/thread/outbox';
	  },
});
window.ThreadMessagesCollection = Backbone.Collection.extend({
	initialize: function(options) {
		//default
		this.thread_id = options.thread_id;
	 },
	model: Message,
	url: function() {
		theurl = 'api/threads/' + this.thread_id;
	
		return theurl;
	  },
});
window.NewMessages = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Message,
	url: function() {
		return 'api/inboxnew';
	  },
});
window.PendingMessages = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Message,
	url: function() {
		return 'api/thread/pendings';
	  },
});
window.OutboxCollection = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Message,
	url: function() {
		return 'api/outbox';
	  },
});
window.DraftsCollection = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Message,
	url: function() {
		return 'api/drafts';
	  },
});
window.DraftsCount = Backbone.Model.extend({
	initialize: function(options) {
		
	 },
	url: function() {
		return 'api/draftcount';
	  },
});
window.MessagesDayCollection = Backbone.Collection.extend({
	initialize: function(options) {
		//default
		this.day = options.day;
		this.box = "in";
		if (typeof options.box != "undefined") {
			this.box = options.box;
		}
	 },
	model: Message,
	url: function() {
		theurl = 'api/' + this.box + 'boxday/' + this.day;
	
		return theurl;
	  },
});