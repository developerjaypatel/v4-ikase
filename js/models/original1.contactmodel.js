window.PersonalContact = Backbone.Model.extend({
	urlRoot:"api/contacts/",
	initialize:function (options) {
		if (typeof options.contact_id != "undefined") {
			this.id = options.contact_id;
		}
	},
	defaults : {
		"id" : -1,
		"contact_id": -1,
		"contact_uuid":"" ,
		"email":"",
		"full_address":"",
		"first_name":"",
		"spam_status":"",
		"spam_status_ok":"",
		"spam_status_blocked":"",
		"messages_received":0,
		"last_email_received":"",
		"messages_sent":0,
		"last_email_sent":"",
		"last_name":"",
		"notes":"",
		"phone":"",
		"gridster_me": false
	}
});
window.PersonalContactsCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.spam_status = "OK";
		if (typeof options != "undefined") {
			if (options.spam_status != "undefined") {
				this.spam_status = options.spam_status;
			}
		}
	},
	url: function() {
		if (this.spam_status=="OK") {
			return 'api/contacts';
		} else {
			return 'api/contactsblocked';
		}
	},
	model:PersonalContact
});