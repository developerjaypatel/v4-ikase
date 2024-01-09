window.EAMSForm = Backbone.Model.extend({
	urlRoot:"api/forms",
	initialize: function(options) {
		if (typeof options != "undefined") {
			this.id = options.eams_form_id;
		}
	  },
	defaults : {
		"eams_form_id" : "",
		"name": "",
		"display_name": "",
		"status": "not ready",
		"category":""
	}
});
window.EAMSFormCollection = Backbone.Collection.extend({
    initialize: function(options) {
		//this.case_id = options.case_id;
	 },
	model: EAMSForm,
    url:"api/forms"
});