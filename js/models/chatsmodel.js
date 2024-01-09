window.Chat = Backbone.Model.extend({
	initialize: function(options) {
		this.id = options.chat_id;
		this.thread_id = options.thread_id;
	},
	defaults : {
		"id" : -1,
		"thread_id": -1,
		"uuid": "",
		"from": "",
		"chat_to": "",
		"chat":"",
		"subject":"",
		"dateandtime":"",
		"attachments":"",
		"deleted":"N",
		"glass":"",
		"gridster_me": false
	},
  	urlRoot: function() {
		return 'api/chat/';
	}
});
window.ChatCollection = Backbone.Collection.extend({
	initialize: function(models, options) {
		this.thread_id = options.thread_id;
	 },
	model: Chat,
	url: function() {
		return 'api/chatread/' + this.thread_id;
	  },
});
window.NewChats = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Chat,
	url: function() {
		return 'api/chatnew';
	  },
});