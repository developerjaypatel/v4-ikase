window.Person = Backbone.Model.extend({
	urlRoot:"api/person",
	initialize: function(options) {
		//this.case_id = options.case_id;
	  },
	defaults : {
		"id" : -1,
		"uuid": "",
		"aka": "",
		"full_name": "",
		"first_name": "",
		"last_name": "",
		"company_name": "",
		"dob": "",
		"ssn": "",
		"ein":"",
		"phone": "",
		"cell_phone":"",
		"other_phone":"",
		"work_phone":"",
		"fax": "",
		"language": "",
		"email": "",
		"work_email": "",
		"title": "",
		"salutation":"",
		"glass":"card_dark_4",
		"full_address":"",
		"age":"",
		"priority_flag":"",
		"gender":"",
		"license_number":"",
		"birth_state":"",
		"birth_city":"",
		"legal_status":"",
		"marital_status":"",
		//"rating_age":"",
		//"dash_age":"",
		"spouse":"",
		"spouse_contact":"",
		"ref_source":"",
		"emergency":"",
		"emergency_contact":"",
		"template_loaded":"",
		"street":"",
		"city":"",
		"state":"",
		"zip":"",
		"suite":"",
		"gridster_me": false
	},
	name: function () {
        return this.get("full_name");
    }
});

window.PersonCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Person,
    url:"api/person"
});