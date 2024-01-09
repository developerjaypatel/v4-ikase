window.Availability = Backbone.Model.extend({
	initialize: function(options) {
		this.user_id = options.user_id;
	 },
	 urlRoot: function() {
		return 'api/availability/' + this.user_id;
	 },
	 defaults : {
		"uuid": "",
		"user_uuid":"",
		"availability":"", 
		"title":"", 
		"signs_for":"",
		"additional_text":"",
		"image_path":""
	 }
});

window.AvailabilityCollection = Backbone.Collection.extend({
    initialize: function(options) {
		
	 },
	model: Availability
});