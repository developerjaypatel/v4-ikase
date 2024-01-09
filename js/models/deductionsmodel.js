window.Deduction = Backbone.Model.extend({
	url: function() {
		return "api/deduction/" + this.id;
	},
	initialize: function(options) {
		this.id = options.id;
	  },
	defaults : {
		"deduction_id":"",
		"deduction_uuid":"",
		"amount":0,
		"payment":0,
		"adjustment":0,
		"tracking_number":"",
		"deduction_date":"0000-00-00",
		"deduction_description":"",
		"deleted":"N"
	}
});
window.DeductionCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
	},
	model: Deduction,
	url: function() {
		return "api/deductions/" + this.case_id;
	}
});