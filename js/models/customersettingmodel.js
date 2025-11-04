window.CustomerSetting = Backbone.Model.extend({
	 url:function() {
		 if (typeof this.name == "undefined") {
		 	return "api/setting/customer/" + this.id;
		 } else {
			 return "api/setting/getname/" + encodeURIComponent(this.name);
		 }
	 },
	initialize: function(options) {
		if (typeof options != "undefined") {
			if (typeof options.setting_id != "undefined") {
				this.id = options.setting_id;
			}
			if (typeof options.setting_uuid != "undefined") {
				this.id = "UUID" + options.setting_uuid;
			}
			if (typeof options.name != "undefined") {
				this.name = options.name;
			}
		}
	  },
	defaults : {
		"setting_id" : "",
		"the_category" : "",
		"setting": "",
		"setting_value": "",
		"default_value": "", 
		"deleted":"N"
	}
});
window.CustomerSettingCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.level = "";
		if (typeof options.level != "undefined") {
			this.level = options.level;
		}
		/*
		this.category = "";
		if (typeof options.category != "undefined") {
			this.category = options.category;
		}
		*/
	 },
	model: CustomerSetting,
    url:function() {
		var params = "";
		/*
		if (this.category!="") {
			params = "/" + this.category + "/" + this.case_id;
		}
		*/
		params = "/customer";
		if (this.level!="") {
			params = "/" + this.level;
		}
		return "api/setting/firm" + params;
	}
});
window.DocumentFilters = Backbone.Model.extend({
	url:function() {
		return "api/documentfilters";
	},
	initialize: function(options) {
		
	  },
	defaults : {
		"id" : -1,
		"document_filters": ""
	}
});
window.CalendarFilters = Backbone.Collection.extend({
    initialize: function(options) {
		//this.case_id = options.case_id;
	 },
	model: CustomerSetting,
    url:function() {
		return "api/calendarfilters";
	}
});
window.SubTypeFilters = Backbone.Model.extend({
	url:function() {
		return "api/subtypefilters";
	},
	initialize: function() {	
	}
});
window.StatusFilters = Backbone.Model.extend({
	url:function() {
		return "api/statusfilters";
	},
	initialize: function() {	
	}
});
window.SubStatusFilters = Backbone.Model.extend({
	url:function() {
		return "api/substatusfilters";
	},
	initialize: function(options) {
		
	  },
	defaults : {
	}
});
window.SubSubStatusFilters = Backbone.Model.extend({
	url:function() {
		return "api/subsubstatusfilters";
	},
	initialize: function(options) {
		
	  },
	defaults : {
	}
});