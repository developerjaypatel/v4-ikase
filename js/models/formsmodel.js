window.Form = Backbone.Model.extend({
	initialize: function(options) {
		//this.id = options.message_id;
		//this.case_id = options.case_id;
	},
	defaults : {
		
	},
  	urlRoot: function() {
		return 'api/forms/';
	}
});
window.FormCollection = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Form,
	url: function() {
		return 'api/forms';
	  },
});
