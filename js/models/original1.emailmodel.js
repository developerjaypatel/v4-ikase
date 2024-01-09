window.Email = Backbone.Model.extend({
	initialize: function(options) {
		this.user_id = options.user_id;
	 },
	 urlRoot: function() {
		return 'api/email/' + this.user_id;
	 },
	 defaults : {
		"uuid": "",
		"user_uuid":"",
		"email_name":"", 
		"email_server":"",
		"email_port":"", 
		"email_method":"",
		"outgoing_server":"",
		"outgoing_port":"",
		"encrypted_connection":"",
		"ssl_required":"",
		"email_pwd":"", 
		"email_address":"",
		"email_phone":"",
		"cell_carrier":"",
		"read_messages":"",
		"active":""
	 }
});
window.ActiveEmail = Backbone.Model.extend({
	initialize: function(options) {
		this.user_id = options.user_id;
	 },
	 url: function() {
		return 'api/email/active/' + this.user_id;
	 },
	 defaults : {
		"uuid": "",
		"user_uuid":"",
		"email_name":"", 
		"email_server":"",
		"email_port":"", 
		"email_method":"",
		"outgoing_server":"",
		"outgoing_port":"",
		"encrypted_connection":"",
		"ssl_required":"",
		"email_pwd":"", 
		"email_address":"",
		"email_phone":"",
		"cell_carrier":"",
		"active":"",
		"id":-1
	 }
});

window.EmailCollection = Backbone.Collection.extend({
    initialize: function(options) {
		
	 },
	model: Email
});