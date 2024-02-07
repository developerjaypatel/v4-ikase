window.home_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "home_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		$(this.el).html(this.template());
		
		return this;
    },
	doTimeouts: function() {
		var self = this;
	}
});
