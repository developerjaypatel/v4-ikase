window.kase_nav_left_view = Backbone.View.extend({
    initialize:function () {
//        console.log('Initializing Home View');
//        this.template = _.template(directory.utils.templateLoader.get('home'));
//        this.template = templates['Home'];
    },

    events:{
        "click #new_kase":"newCase"
    },

    render:function () {
        $(this.el).html(this.template());
        return this;
    },

    newCase:function (event) {
        composeKase(event);
    }
});
