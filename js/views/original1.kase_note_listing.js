window.kase_note_listing_view = Backbone.View.extend({

    initialize:function () {
        this.model.on("change", this.render, this);
		this.model.on("add", this.render, this);
    },

    render:function () {		
		var self = this;
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({notes: this.model.toJSON()}));
		
		tableSortIt("kase_note_listing");
		
		return this;
    }

});
