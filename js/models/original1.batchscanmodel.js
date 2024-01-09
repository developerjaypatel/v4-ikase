window.Batchscan = Backbone.Model.extend({
	urlRoot:"api/batchscan",
	initialize: function(options) {
		//this.case_id = options.case_id;
	  },
	defaults : {
		"id" : "",
		"uuid": "",
		"dateandtime": "",
		"filename": "",
		"time_stamp": "",
		"pages": 0,
		"separators": "",
		"processed": "",
		"separated": "",
		"stacked": "",
		"stacks": "",
		"completion": "",
		"user_name": "",
		"gridster_me": false
	}
});

window.BatchscanCollection = Backbone.Collection.extend({
    initialize: function(options) {
		//this.case_id = options.case_id;
	 },
	model: Batchscan,
    url:"api/batchscan"
});