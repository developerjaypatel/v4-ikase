window.basic_view = Backbone.View.extend({
    initialize:function () {
        console.log('Initializing Home View');
    },

    events:{
       
    },

    render:function () {
        $(this.el).html(this.template());
        return this;
    }
});
