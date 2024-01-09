window.Webmail = Backbone.Model.extend({
	initialize: function(options) {
		this.user_id = options.user_id;
	 },
	 defaults : {
		"subject": "",
		"from":"",
		"to":"", 
		"date":"",
		"message_id":"", 
		"attachments":""
	 }
});

window.WebmailCollection = Backbone.Collection.extend({
    initialize: function(options) {
		
	 },
	model: Webmail,
	 url: function() {
		//return 'api/webmail';
		return 'api/obtainmail/' + customer_id + "/" + login_user_id;
	 }
});