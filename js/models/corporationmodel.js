window.Corporation = Backbone.Model.extend({
	urlRoot: function() {
		if (typeof this.case_id != "undefined") {
			return 'api/kase/corporation/' + this.case_id;
		}
		if (typeof this.uuid != "undefined") {
			return 'api/kase/corporationbyid/' + this.uuid;
		}
		return 'api/corporation/' + this.type;
	},
	initialize: function(options) {
		this.case_id = options.case_id;
		this.type = options.type;
		this.partie_type = options.type;
		this.uuid = options.uuid;
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
		"employee_cell": "",
		"employee_fax": "",
		"employee_email": "",
		"email": "",
		"salutation":"",
		"glass":"_dark",
		"color":"_dark",
		"full_address":"",
		"additional_addresses":"",
		"suite":"",
		"street":"",
		"city":"",
		"state":"",
		"zip":"",
		"company_site":"",
		"full_name":"",
		"salutation":"",
		"copying_instructions":"",
		"claims":"",
		
		"phone_ext":"",
		"comments":"",
		"fee":"",
		"report_number":"",
		"officer":"",
		"date":"",
		"party_type_option":"",
		"party_defendant_option":"",
		
		"template_loaded":"",
		"referred_out_claim":"",
		"show_buttons":true,
		"show_employee":false,
		"gridster_me": false,
		"gridster_it": false
	},
	label: function () {
        return this.get("company_name") + "<br>" + this.get("full_address");
    },
	kaseInfo: function() {
		var url = "api/kases/corporation/" + this.case_id + "/" + this.id;
        var self = this;
        $.ajax({
            url:url,
            dataType:"json",
            success:function (data) {
				self.reset(data);
            }
        });
	},
    sortByField: function(field, direction){
            sorted = _.sortBy(this.models, function(model){
                return model.get(field);
            });

            if(direction === 'descending'){
                sorted = sorted.reverse()
            }

            this.models = sorted;
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
window.Employers = Backbone.Collection.extend({
     initialize: function() {
	 },
	model: Corporation,
	url: function() {
    	return 'api/employers';
  	},
});
window.PriorTreatments = Backbone.Collection.extend({
     initialize: function(models, options) {
		this.person_id = options.person_id;
	 },
	model: Corporation,
	url: function() {
    	return 'api/prior_treatments/' + this.person_id;
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