window.EamsCarrier = Backbone.Model.extend({
	initialize: function(options) {
		//this.case_id = options.case_id;
	  },
	defaults : {
		"id" : -1,
		"uuid": "",
		"eams_ref_number":"", 
		"firm_name":"", 
		"street_1":"", 
		"street_2":"", 
		"city":"", 
		"state":"", 
		"zip_code":"", 
		"phone":"", 
		"service_method":"", 
		"last_update":"", 
		"last_import_date":"",
		"gridster_me": false
	},
	label: function () {
        return this.get("eams_ref_number") + " - " + this.get("firm_name");
    },
	address_phone: function () {
        return this.get("street_1") + ", " + this.get("city") + ", " + this.get("state") + ", " + this.get("zip_code") + " - " + this.get("phone");
    },
	display: function () {
        return this.get("firm_name");
    }
});

window.EamsCarrierCollection = Backbone.Collection.extend({
	model: EamsCarrier,
    url:"api/eams_carrier"
});