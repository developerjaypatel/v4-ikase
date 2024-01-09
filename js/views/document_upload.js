window.document_upload_view = Backbone.View.extend({

    initialize:function () {

    },

    events:{
        
    },

    render:function () {
        $(this.el).html(this.template(this.model.toJSON()));
		
        return this;
    }
});
