window.AdhocCollection = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
	this.corporation_id = options.corporation_id;
  },
  url: function() {
    return 'api/adhocs/' + this.case_id + '/' + corporation_id;
  },
  model:BodyPart
});