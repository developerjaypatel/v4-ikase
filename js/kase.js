//global collections
var kases;

window.Router = Backbone.Router.extend({

    routes: {
        "": "home",
		"kases" : "kasesList"
	},
	
	kasesList: function () {
		if (!this.KaseLoadingView) {
			this.kaseLoadView = new kaseLoadingView().render().el;
			$('#content').html(this.kaseLoadView);
		}
		if (!this.kaseListView) {
			kases = new KaseCollection();
			kases.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of kaseListingView
					// instead of creating new instances
					this.kaseListView = new kaseListingView({model: data}).render().el;
					$('#content').html(this.kaseListView);
				}
			});
		} else {
			this.kaseListView.delegateEvents(); // delegate occurences when the view is recycled
		}
    }
});

//load templates
templateLoader.load(["kaseHomeView", "kaseListingView", "kaseLoadingView"],
    function () {
        app = new Router();
        Backbone.history.start();
	}
);