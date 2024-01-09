window.Scrape = Backbone.Model.extend({
	url: function() {
		return "api/scrape/" + this.adj_number;
	},
	initialize: function(options) {
		if (typeof options != "undefined") {
			this.adj_number = options.adj_number;
		}
	}
});