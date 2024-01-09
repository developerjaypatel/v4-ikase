window.partie_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
  
    },
    render:function () {
		var self = this;
		mymodel = this.model.toJSON();
		//$(this.el).html(this.template(mymodel));
		$(this.el).html(this.template());
		return this;
    }
});
