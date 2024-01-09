window.template_upload_view = Backbone.View.extend({

    initialize:function () {

    },

    events:{
        
    },

    render:function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "template_upload_view";
			var extension = "php";
			this.model.set("holder", "upload_documents");
			loadTemplate(view, extension, this);
			return "";
	   	}
		
        $(this.el).html(this.template(this.model.toJSON()));
		
        return this;
    }
});
