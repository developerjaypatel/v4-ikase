window.notes_list = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{

    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "notes_list";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		var notes = this.collection.toJSON();
		
		$(".initial_notes").remove();
		
		$(this.el).html(this.template({notes:notes}));
		
		return this;
    }
});
