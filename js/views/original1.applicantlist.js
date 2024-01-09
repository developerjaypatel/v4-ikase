window.ApplicantListView = Backbone.View.extend({

    tagName:'ul',

    className:'nav nav-list',

    initialize:function () {
        var self = this;
        this.model.bind("reset", this.render, this);
        this.model.bind("add", function (applicant) {
            $(self.el).append(new ApplicantListItemView({model:applicant}).render().el);
        });
    },

    render:function () {
        $(this.el).empty();
        _.each(this.model.models, function (applicant) {
            $(this.el).append(new ApplicantListItemView({model:applicant}).render().el);
        }, this);
        return this;
    }
});

window.ApplicantListItemView = Backbone.View.extend({

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