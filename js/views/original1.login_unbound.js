/*
model = new Backbone.Model();
model.bind('change', function () {
	$('#modelData').html(JSON.stringify(model.toJSON()));
});
*/
window.LoginView = Backbone.View.extend({
    initialize:function () {
        console.log('Initializing Login View');
    },
	
	events: {
        "click #loginButton": "login"
    },
     viewBindings: {
		stringValue: {
			text: 'td.stringValue',
			value: [
				'input.stringValue',
				{selector: 'input.stringValueKeyup', event: 'keyup'},
				'textarea.stringValue'
			]
		}
	 },
	 
    render:function () {
		//$(this.el).html(this.template());
		//return this;
		this.vmodel = new Backbone.Model();
        Bindem.on.call(this, this.viewBindings, {model: this.vmodel});
    },
	
    login:function (event) {
        event.preventDefault(); // Don't let this button submit the form
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/login';
        console.log('Loggin in... ');
        var formValues = {
            email: $('#inputEmail').val(),
            password: $('#inputPassword').val()
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
				console.log(data);
                console.log("logged in:");
				$('#logoutLink').show();
				$('#loginLink').hide();
				$('#logged_in').val(data.sess_id);
                if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
                else { // If not, send them back to the home page
                    Backbone.history.navigate("/#");
                }
            }
        });
    }
});

var myloginView = new LoginView({el: $('.liveExample')});
myloginView.render();