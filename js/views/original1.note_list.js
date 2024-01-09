window.NoteListView = Backbone.View.extend({

    tagName:'ul',

    className:'nav nav-list',

    initialize:function () {
        var self = this;
        this.model.bind("reset", this.render, this);
        this.model.bind("add", function (note) {
            $(self.el).append(new note_list_item_view({model:note}).render().el);
        });
    },

    render:function () {
        $(this.el).empty();
        _.each(this.model.models, function (note) {
            $(this.el).append(new note_list_item_view({model:note}).render().el);
        }, this);
        return this;
    }
});

window.note_list_item_view = Backbone.View.extend({

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