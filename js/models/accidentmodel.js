window.Accident = Backbone.Model.extend({
	url: function() {
		return "api/accident/kase/" + this.case_id;
	},
	initialize: function(options) {
		this.case_id = options.case_id;
	  },
	defaults : {
		"id" : -1,
		"uuid": "",
		"accident_date": "",
		"accident_info":"",
		"accident_details":"",
		"customer_id": "",
		"gridster_me": true,
		"deleted": ""
	}
});
window.Disability  = Backbone.Model.extend({
	url: function() {
		if (typeof this.injury_id != "undefined") {
			return 'api/disability/injury/' + this.injury_id;
		}
		return 'api/disability/' + this.id;
	},
	initialize: function(options) {
		if (typeof options.injury_id != "undefined") {
			this.injury_id = options.injury_id;
		}
	  },
	defaults : {
		"id" : -1,
		"uuid": "",
		"claim": "",
		"description":"",
		"ailment":"",
		"duration":"",
		"severity":"",
		"duty":"",
		"limits":"",
		"treatment":"",
		"deleted":"",
		"customer_id": "",
		"gridster_me": true,
		"deleted": ""
	}
});
window.DisabilitiesCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Disability,
	url: function() {
		var thereturn = 'api/disabilities/' + this.case_id;
		return thereturn;
	}
});
window.Surgery  = Backbone.Model.extend({
	url: function() {
		
		return 'api/surgery/' + this.id;
	},
	initialize: function(options) {
		
	  },
	defaults : {
		"id" : -1,
		"case_uuid": "",
		"surgery_info":"",
		"surgery_date":"",
		"procedure":"",
		"memo":"",
		"deleted":"",
		"customer_id": "",
		"gridster_me": true,
		"deleted": ""
	}
});
window.SurgeryCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Surgery,
	url: function() {
		var thereturn = 'api/surgeries/' + this.case_id;
		return thereturn;
	}
});