window.QME = Backbone.Model.extend({
	url: function() {
		
	},
	initialize: function(options) {
		
	}
});
window.QMECollection = Backbone.Collection.extend({
  initialize: function(options) {
   	this.scode = options.scode;
	this.radius = options.radius;
	this.zip = options.zip;
  },
  url: function() {
    return 'api/searchqme/' + this.scode + '/' + this.radius + '/' + this.zip;
  },
  model: QME
});
window.EAMS = Backbone.Model.extend({
	url: function() {
		
	},
	initialize: function(options) {
		
	}
});
window.EAMSCollection = Backbone.Collection.extend({
  initialize: function(options) {
   	
  },
  url: function() {
    return 'api/scrape/search';
  },
  model: EAMS
});