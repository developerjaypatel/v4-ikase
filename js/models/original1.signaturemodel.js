window.Signature = Backbone.Model.extend({
	initialize: function(options) {
		this.user_id = options.user_id;
	 },
	 urlRoot: function() {
		return 'api/signature/' + this.user_id;
	 },
	 defaults : { 
		"uuid": "",
		"user_uuid":"",
		"signature":"", 
		"title":"", 
		"signs_for":"",
		"additional_text":"",
		"image_path":""
	 }
});

window.SignatureCollection = Backbone.Collection.extend({
    initialize: function(options) {
		
	 },
	model: Signature
});