window.EventListView = Backbone.View.extend({

    tagName:'ul',

    className:'nav nav-list',

    initialize:function () {
        var self = this;
        this.model.bind("reset", this.render, this);
        this.model.bind("add", function (event) {
            $(self.el).append(new EventListItemView({model:event}).render().el);
        });
    },

    render:function () {
        var self = this;
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({occurences: this.model.toJSON()}));
		
		//setTimeout("tableSortIt()", 100);
		//setTimeout("listKaseCategories()", 100);
		
        return this;
    }
});

window.EventListItemView = Backbone.View.extend({

    tagName:"li",

    initialize:function () {
        this.model.bind("change", this.render, this);
        this.model.bind("destroy", this.close, this);
    },

    render:function () {
        $(this.el).html(this.template(this.model.toJSON()));
        return this;
    }

});