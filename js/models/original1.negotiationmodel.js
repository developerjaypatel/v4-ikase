window.Negotiation = Backbone.Model.extend({
	url: function() {
		return "api/negotiation/" + this.id;
	},
	initialize: function(options) {
		this.id = options.id;
	  },
	defaults : {
		"negotiation_id":"",
		"negotiation_uuid":"",
		"negotiator":"",
		"firm":"",
		"worker":"",
		"negotiation_type":"",
		"amount":0,
		"negotiation_date":"0000-00-00",
		"corporation_id":"0",
		"comments":"",
		"deleted":"N"
	}
});
window.NegotiationCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
		this.corporation_id = "";
		if (typeof options.corporation_id != "undefined") {
			this.corporation_id = options.corporation_id;
		}
	},
	model: Negotiation,
	url: function() {
		if (this.corporation_id=="") {
			return "api/negotiations/" + this.case_id;
		} else {
			return "api/negotiationsfirm/" + this.case_id + "/" + this.corporation_id;
		}
	}
});