window.BodyPart = Backbone.Model.extend({
	urlRoot:"api/bodyparts",
	initialize:function () {
	},
	defaults : {
		"id" : -1,
		"injury_id" : -1,
		"uuid": "",
		"case_id": "",
		"case_uuid": "",
		"code": "",
		"description":"",
		"gridster_me": false
	}
});
window.BodyPartsCollection = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.injury_id = options.injury_id;
	this.case_id = options.case_id;
	this.case_uuid = options.case_uuid;
  },
  url: function() {
    return 'api/bodyparts/' + this.case_id + '/' + this.injury_id;
  },
  model:BodyPart
});