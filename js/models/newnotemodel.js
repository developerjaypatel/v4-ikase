window.New_note = Backbone.Model.extend({
	initialize: function(options) {
		this.id = options.new_note_id;
	},
	defaults : {
		"id" : -1,
		"deleted":"N",
		"glass":"",
		"gridster_me": false
	},
  	urlRoot: function() {
		return 'api/notes';
	}
});
window.New_noteCollection = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: New_note,
	url: function() {
		return 'api/notes';
	  },
});