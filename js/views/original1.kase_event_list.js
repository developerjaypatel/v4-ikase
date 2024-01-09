window.event_list_view = Backbone.View.extend({

    tagName:'ul',

    className:'nav nav-list',

    initialize:function () {
        var self = this;
        this.model.bind("reset", this.render, this);
        this.model.bind("add", function (event) {
            $(self.el).append(new event_list_item_view({model:event}).render().el);
        });
    },

    render:function () {
        var self = this;
		
		if (typeof this.template != "function") {
			var view = "event_list_view";
			var extension = "php";
			this.model.set("holder", "occurences_recent");
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({occurences: this.model.toJSON()}));
		
		//setTimeout("tableSortIt()", 100);
		//setTimeout("listKaseCategories()", 100);
		
        return this;
    }
});

window.event_list_item_view = Backbone.View.extend({

    tagName:"li",

    initialize:function () {
        this.model.bind("change", this.render, this);
        this.model.bind("destroy", this.close, this);
    },

    render:function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "event_list_item_view";
			var extension = "php";
			this.model.set("holder", "occurences_recent");
			loadTemplate(view, extension, this);
			return "";
	   	}
        $(this.el).html(this.template(this.model.toJSON()));
        return this;
    }

});