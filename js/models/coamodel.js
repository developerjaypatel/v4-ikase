window.AccidentCause = Backbone.Model.extend({
	url: function() {
		return "api/coa/" + this.case_id + "/" + this.coa_id;
	},
	initialize: function(options) {
		this.case_id = options.case_id;
		this.coa_id = options.coa_id;
	},
	defaults : {
		"id" : -1,
		"uuid": "",
		"coa_date": "",
		"coa_description": "",
		"coa_info": "",
		"coa_details": "",
		"coa_other_details": "",
		"case_id": "",
		"deleted": "",
		"gridster_me": false
	}
});
window.AccidentCauseCollection = Backbone.Collection.extend({
  initialize: function(options) {
    this.case_id = options.case_id;
	this.new_legal_id = options.new_legal_id;
  },
  url: function() {
    return "api/coas/" + this.case_id + "/" + this.new_legal_id;
  },
  model: AccidentCause,
});

