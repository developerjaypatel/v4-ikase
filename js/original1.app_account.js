var current_calendar_id;
window.Router = Backbone.Router.extend({
    routes: {
		"reset/:key":								"resetPassword"
	},
    initialize: function () {
		var self = this;
    },
	resetPassword: function (key) { 
		var url = 'api/request/verify';
		formValues = "key=" + key;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					alert(data.error.text);
					return;
				} else {
					var reset_model = new Backbone.Model;
					reset_model.set("key", key);
					reset_model.set("user_id", data.user_id);
					reset_model.set("id", data.user_id);
					//verified
					$('#content').html(new reset_password_view({model: reset_model}).render().el);					
				}
			}
		});
			
	}
});

//load templates
//get rid of interoffice view
//"event_print_view", 
templateLoader.load(["reset_password_view"],
    function () {
        app = new Router();
        Backbone.history.start();
	}
);