window.Specialty = Backbone.Model.extend({
	urlRoot:"api/specialty",
	initialize: function(options) {
		//this.case_id = options.case_id;
	  },
	defaults : {
		"specialty_id" : -1,
		"specialty":"",
		"description":""
	}
});

window.SpecialtyCollection = Backbone.Collection.extend({
    initialize: function(options) {
		
	 },
	model: Specialty,
    url:"api/specialties"
});