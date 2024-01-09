window.vservice_view = Backbone.View.extend({
    initialize:function () {
        
    },

    events:{
       "click #open_vservice_form":			"openSendForm"
    },

    render:function () {
        mymodel = this.model.toJSON();
		$(this.el).html(this.template(mymodel));
        return this;
    },
	openSendForm: function() {
		$("#vservice_form_holder").fadeIn();
	}
});
