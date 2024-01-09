window.accident_new_view = Backbone.View.extend({
	initialize:function () {
    },
	
    render:function () {		
		var self = this;
		
		try {
			$(this.el).html(this.template(self.model.toJSON()));
		}
		catch(err) {
			var view = "accident_new_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		setTimeout(function() {
			//gridster the parties_new tab
			gridsterById("gridster_accident_new");
		}, 700);
        return this;
	}
});