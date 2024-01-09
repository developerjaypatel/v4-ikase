window.Corporation = Backbone.Model.extend({
	urlRoot: function() {
		return 'api/corporation/' + this.type;
	},
	initialize: function(options) {
		this.case_id = options.case_id;
		this.type = options.type;
		this.partie_type = options.type;
		this.adhocs = new AdhocCollection([], {case_id: this.case_id, corporation_id: this.id});
	},
	defaults : {
		"id":-1,
		"uuid": "",
		"parent_corporation_uuid": "",
		"adhoc_fields":"",
		"case_id": "",
		"case_uuid": "",
		"first_name": "",
		"company_name": "",
		"last_name": "",
		"party_type": "",
		"type": "",
		"dob": "",
		"ssn": "",
		"phone": "",
		"employee_phone": "",
		"employee_fax": "",
		"employee_email": "",
		"email": "",
		"salutation":"",
		"glass":"_edit",
		"color":"_edit",
		"full_address":"",
		"suite":"",
		"company_site":"",
		"full_name":"",
		"show_buttons":true,
		"show_employee":false,
		"gridster_me": false,
		"gridster_it": false
	},
	name: function () {
        return this.get("company_name") + "<br>" + this.get("full_address");
    }
});
window.Corporations = Backbone.Collection.extend({
     initialize: function(models, options) {
		this.case_id = options.case_id;
	 },
	model: Corporation,
	url: function() {
    	return 'api/corporation/kases/' + this.case_id;
  	},
});
window.Adhoc = Backbone.Model.extend({
});
window.AdhocCollection = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
	this.corporation_id = options.corporation_id;
  },
  url: function() {
    return 'api/adhocs/' + this.case_id + '/' + this.corporation_id;
  },
  model:Adhoc
});