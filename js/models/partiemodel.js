window.Partie = Backbone.Model.extend({
	urlRoot:"api/parties",
	initialize:function () {
	}
});
window.Parties = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
	this.case_uuid = options.case_uuid;
	this.panel_title = options.panel_title;
	this.partie_id = "";
	this.type = "";
	if (typeof options.type != "undefined") {
		this.type = options.type;
	}
	if (typeof options.partie_id != "undefined") {
		this.partie_id = options.partie_id;
	}
  },
  url: function() {
	  var api = 'api/parties/' + this.case_id;
	  if (this.panel_title=="Dashboard") {
		api = 'api/dashboard/' + this.case_id + '/' + this.panel_title.toLowerCase();
	  }
	  if (this.panel_title=="settlementpriorpayment") {
		api = 'api/settlement/' + this.case_id + '/' + this.panel_title.toLowerCase() + '/' + this.partie_id;
	  }
	  if (this.type!="") {
		  api = 'api/partielist/' + this.case_id + '/' + this.type;
	  }
    return api;
  },
  model: Partie,
});
window.Offices = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
  },
  url: function() {
	  var api = 'api/offices/' + this.case_id;	  
    return api;
  },
  model: Partie,
});