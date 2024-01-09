window.Fee = Backbone.Model.extend({
	url: function() {
		if (this.type!="") {
			return 'api/fee/' + this.type + '/' + this.id;
		} else {
			return 'api/getfee/' + this.id;
		}
	  },
	initialize: function(options) {
		if (typeof options != "undefined") {
			if (options.id < 0) {
				this.id = options.injury_id;
				this.type = options.type;
			} else {
				this.id = options.id;
				this.type = "";
			}
		}
	  },
	defaults : {
		"id" : -1,
		"injury_id":"",
		"settlement_id":"",
		"fee_id" : -1, 
		"parent_table_id":"",
		"fee_total_paid":0,
		"fee_uuid":"", 
		"fee_type":"", 
		"fee_requested":"",
		"fee_date":"", 
		"fee_billed":0, 
		"fee_paid":0, 
		"paid_fee":0,
		"fee_recipient":"", 
		"fee_check_number":"",
		"fee_memo":"",
		"fee_doctor_id":"", 
		"fee_referral":"", 
		"hourly_rate":0,
		"hours":0,
		"fee_by":"",
		"customer_id":"", 
		"deleted":"", 
		"gridster_me":true
	}
});
window.FeesCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.injury_id = options.injury_id;
		this.type = options.type;
	 },
	model: Fee,
	url: function() {
		var thereturn = 'api/fees/' + this.type + '/' + this.injury_id;
		return thereturn;
	}
});
window.SettlementFeesCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.case_type = options.case_type;
		this.settlement_id = options.settlement_id;
	 },
	model: Fee,
	url: function() {
		var thereturn = 'api/settlement_fees/' + this.settlement_id + "/" + this.case_type.replace(" ", "_");
		return thereturn;
	}
});
window.SettlementWCFeesCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.settlement_id = options.settlement_id;
		this.fee_type = "";
		if (options.fee_type != "undefined") {
			this.fee_type = options.fee_type;
		}
	 },
	model: Fee,
	url: function() {
		var thereturn = 'api/wc_fees/' + this.settlement_id;
		if (this.fee_type != "") {
			thereturn = 'api/wc_type_fees/' + this.settlement_id + '/' + this.fee_type;
		}
		return thereturn;
	}
});