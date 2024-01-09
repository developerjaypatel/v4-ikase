window.kaseListingView = Backbone.View.extend({

    initialize:function () {
        this.model.on("change", this.render, this);
		this.model.on("add", this.render, this);
    },

    render:function () {		
		var self = this;
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({kases: this.model.toJSON()}));
		
		tableSortIt("kase_listing");
		
		return this;
    }

});
