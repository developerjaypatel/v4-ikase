window.LoginView = Backbone.View.extend({
    initialize:function () {
        console.log('Initializing Login View');
    },

    events: {
        "click #loginButton": "login"
    },

    render:function () {
        $(this.el).html(this.template());
        return this;
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
				$('#logoutLink').show();
				$('#loginLink').hide();
				$('#logged_in').val(data.sess_id);
                if(data.error) {  // If there is an error, show the error messages
                    login_username = "";
					$('.alert-error').text(data.error.text).show();
                }
                else { // If not, send them back to the home page
					login_username = $('#inputEmail').val();
					$('.alert-success').text("Login Success").show();
                    window.location.hash = "#";
                }
            },
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				console.log("Status: " + textStatus); 
				console.log("Error: " + errorThrown); 
			} 
        });
    }
});