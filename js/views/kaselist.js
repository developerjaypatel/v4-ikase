window.KaseListView = Backbone.View.extend({

    tagName:'ul',

    className:'nav nav-list',

    initialize:function () {
        var self = this;
        this.model.bind("reset", this.render, this);
        this.model.bind("add", function (casing) {
            $(self.el).append(new KaseListItemView({model:casing}).render().el);
        });
    },

    render:function () {
        $(this.el).empty();
        _.each(this.model.models, function (casing) {
            $(this.el).append(new KaseListItemView({model:casing}).render().el);
        }, this);
        return this;
    }
});

window.KaseListItemView = Backbone.View.extend({

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