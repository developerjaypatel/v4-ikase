window.InjuryNumber = Backbone.Model.extend({
	urlRoot:"api/injury_number",
	initialize:function (options) {
		this.injury_id = options.injury_id;
	},
	defaults : {
		"id" : -1,
		"uuid": "",
		"case_id": "",
		"case_uuid": "",
		"injury_id":-1,
		"insurance_policy_number": "",
		"alternate_policy_number": "",
		"carrier_claim_number": "",
		"alternate_claim_number": "",
		"carrier_building_indentifier":"",
		"carrier_building_description":"",
		"deleted":"N",
		"gridster_it": false,
		"gridster_me": true
	}
});
window.InjuryNumbersCollection = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.injury_id = options.injury_id;
	this.case_id = options.case_id;
	this.case_uuid = options.case_uuid;
  },
  url: function() {
    return 'api/injury_number/' + this.case_id + '/' + this.injury_id;
  },
  model:InjuryNumber
});
