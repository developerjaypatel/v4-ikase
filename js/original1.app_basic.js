window.Router = Backbone.Router.extend({
    routes: {
		"": 		"home"
	},
    initialize: function () {
		var self = this;
    },
	home: function(task_id) {
		data = new Backbone.Model();
		$('#content').html(new basic_view({model: data}).render().el);				
	}
});

//load templates
//get rid of interoffice view
templateLoader.load(["basic_view"],
    function () {
        app = new Router();
        Backbone.history.start();
	}
);