window.vService = Backbone.Model.extend({
	urlRoot:"api/vservice",
	initialize: function(options) {
		//this.case_id = options.case_id;
	  },
	defaults : {
		"vservice_id" : -1,
		"vservice_uuid": "",
		"type":"",
		"name":"",
		"full_address":"",
		"phone":"",
		"fax":"",
		"email":"",
		"company_site":"",
		"deleted": ""
	}
});

window.vServiceCollection = Backbone.Collection.extend({
    initialize: function(options) {
		
	 },
	model: vService,
    url:"api/vservices"
});