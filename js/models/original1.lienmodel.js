window.Lien = Backbone.Model.extend({
	url: function() {
		return 'api/lien/' + this.id;
	  },
	initialize: function(options) {
		if (typeof options != "undefined") {
			this.id = options.injury_id;
		}
	  },
	defaults : {
		"id" : -1,
		"lien_id" : -1,
		"adj_number":"",
		"start_date":"",
		"end_date":"",
		"injury_id":-1,
		"lien_uuid": "",
		"date_filed":"",
		"date_paid":"",
		"amount_of_lien":"",
		"amount_of_fee":"",
		"amount_paid":"",
		"appearance_fee":"",
		"worker":"",
		"gridster_me":true
	}
});
window.LienCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Lien,
	url: function() {
		var thereturn = 'api/liens/' + this.case_id;
		return thereturn;
	}
});