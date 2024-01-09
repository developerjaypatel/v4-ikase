window.Jetfile = Backbone.Model.extend({
	urlRoot:"api/jetfile",
	initialize: function(options) {
		this.injury_id;
	},
	defaults : {
		"id" : -1,
		"case_id":"",
		"injury_number":"",
		"injury_id":"",
		"adj_number":"",
		"start_date":"",
		"end_date":"",
		"info":"",
		"jetfile_case_id":"",
		"app_filing_id":"",
		"app_filing_date":"0000-00-00 00:00:00",
		"app_status":"",
		"dor_info":"",
		"jetfile_dor_id":"",
		"dor_filing_id":"",
		"dor_filing_date":"0000-00-00 00:00:00",
		"dore_info":"",
		"jetfile_dore_id":"",
		"dore_filing_id":"",
		"dore_filing_date":"0000-00-00 00:00:00",
		"lien_info":"",
		"jetfile_lien_id":"",
		"lien_filing_id":"",
		"lien_filing_date":"0000-00-00 00:00:00",
		"app_document_count":"",
		"full_name":"",
		"submitted_by":"",
		"submitted_date":"",
		"deleted":"N"
	}
});
window.JetfileCollection = Backbone.Collection.extend({
  initialize: function(options) {
	  this.injury_id = "";
	  if (typeof options != "undefined") {
		  if (typeof options.injury_id != "undefined") {
	  		this.injury_id = options.injury_id;
		  }
	  }
  },
  url: function() {
	if (this.injury_id == "") {
		return 'api/jetfiles';
	} else {
		return 'api/jetfile/fetch/' + this.injury_id;
	}
  },
  model: Jetfile,
  searchErrors: function() {
		var url = 'api/jetfiles/errors';
		var self = this;
		$.ajax({
			url:url,
			dataType:"json",
			success:function (data) {
				self.reset(data);
				var mymodel = new Backbone.Model();
				mymodel.set("holder", "content");
				mymodel.set("main_filter", "errors");
				//mymodel.set("sort_by", "last_name");
				$('#content').html(new jetfile_listing_view({collection: self, model:mymodel}).render().el);
			}
		});
  },
  searchRecent: function() {
	  	$("#content").html(loading_image);
		var url = 'api/jetfiles/recent';
		var self = this;
		$.ajax({
			url:url,
			dataType:"json",
			success:function (data) {
				self.reset(data);
				var mymodel = new Backbone.Model();
				mymodel.set("holder", "content");
				mymodel.set("main_filter", "recent");
				//mymodel.set("sort_by", "last_name");
				$('#content').html(new jetfile_listing_view({collection: self, model:mymodel}).render().el);
			}
		});
  },
  searchDB:function(key) {
	  	$("#content").html(loading_image);
		key = key.trim();
		key = key.replaceAll("/", "-");
		key = key.replaceAll(" ", "_");
		var url = "api/jetfiles/search/" + key;
		
		if (key.length > 0 && key.length < 3) {
			return;
		}
		if (key.length == 0) {
			var url = 'api/jetfiles';
		}
		var self = this;
		$.ajax({
			url:url,
			dataType:"json",
			success:function (data) {
				self.reset(data);
				var mymodel = new Backbone.Model();
				mymodel.set("holder", "content");
				mymodel.set("main_filter", "");
				//mymodel.set("sort_by", "last_name");
				$('#content').html(new jetfile_listing_view({collection: self, model:mymodel}).render().el);
			}
		});
	}
});
window.JetfileKaseCollection = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
  },
  url: function() {
    return 'api/jetfile/list/' + this.case_id;
  },
  model: Jetfile,
});