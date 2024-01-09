window.Letter = Backbone.Model.extend({
	urlRoot:"api/letters",
	initialize: function(options) {
		this.id = options.letter_id;
		this.case_id = options.case_id;
	},
	defaults: {
		"letter_id":  -1,
		"case_id":  -1,
		"letter":  "",
		"document_extension":""
	}
});