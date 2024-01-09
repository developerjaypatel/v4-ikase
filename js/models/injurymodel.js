window.Injury = Backbone.Model.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
	  },
	defaults : {
		"id" : -1,
		"injury_id" : -1,
		"uuid": "",
		"adj_number":"",
		"injury_status":"",
		"case_id": "",
		"case_uuid": "",
		"venue_uuid": "",
		"venue": "",
		"venue_abbr": "",
		"occupation": "",
		"occupation_group":"",
		"start_date": "",
		"end_date": "",
		"statute_limitation":"0000-00-00",
		"statute_interval":"1825",
		"statute_years":"5",
		"explanation": "",
		"full_address":"",
		"suite":"",
		"deleted":"N",
		"gridster_me": false
	},
  	urlRoot: function() {
		return 'api/injury/' + this.case_id;
	  }
/*	  
	url: function() {
		return 'api/injury/' + this.case_id;
	  }
*/	  
});

//all dates of injuries for particular customer
window.InjuryCollection = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Injury,
	url: function() {
		return 'api/injury/-1/-1';
	  }
});
window.KaseInjuryCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
		/*
		if (typeof options.dob !="undefined") {
			this.dob = options.dob;
			this.ssn = options.ssn;
		}
		*/
	 },
	model: Injury,
	url: function() {
		return 'api/injury_kase/' + this.case_id;
	  },
});