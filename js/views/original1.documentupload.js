window.documentUploadView = Backbone.View.extend({

    initialize:function () {

    },

    events:{
        
    },

    render:function () {
        $(this.el).html(this.template(this.model.toJSON()));
		
        return this;
    }
});
