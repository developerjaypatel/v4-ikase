window.Adjustment = Backbone.Model.extend({
	url: function() {
		return "api/adjustment/" + this.id;
	},
	initialize: function(options) {
		this.id = options.id;
	  },
	defaults : {
		"adjustment_id":"",
		"adjustment_uuid":"",
		"amount":0,
		"adjustment_date":"0000-00-00",
		"adjustment_type":"A",
		"description":"",
		"deleted":"N"
	}
});
window.AdjustmentCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.account_id = options.account_id;
		this.type = "";
		if (typeof options.type != "undefined") {
			this.type = options.type;
		}
	},
	model: Adjustment,
	url: function() {
		if (this.type=="") {
			return "api/adjustments/" + this.account_id;
		} else {
			return "api/adjustmentbytype/" + this.account_id + "/" + this.type;
		}
	}
});