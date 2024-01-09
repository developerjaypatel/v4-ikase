window.Rx = Backbone.Model.extend({
	urlRoot:"api/rx",
	initialize: function() {
	  },
	defaults : {
		"id" : -1,
		"uuid": "",
		"start_date": "",
		"end_date": "",
		"doctor_id":"",
		"doctor_name":"",
		"notes":"",
		"medication":"",
		"dosage":"",
		"regimen":"",
		"refills":"N",
		"customer_id": "",
		"deleted": "N"
	}
});
window.RxCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.person_id = options.person_id;
	},
	url: function() {
		return 'api/rx/person/' + this.person_id;
	},
	model:Rx
});