window.LogoutView = Backbone.View.extend({

    initialize:function () {
        console.log('Initializing Logout View');
		this.logout();
    },

    events: {
        "click #logoutButton": "logout"
    },

    render:function () {
        $(this.el).html(this.template());
        return this;
    },

    logout:function (event) {
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/logout';
        console.log('Loggin out... ');
        /*
		var formValues = {
            email: $('#inputEmail').val(),
            password: $('#inputPassword').val()
        };
		*/
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: "",
            success:function (data) {
                console.log(["Logout request details: ", data]);
				$('#logoutLink').hide();
				$('#loginLink').show();
				$('#logged_in').val('');
                if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
                else { // If not, send them back to the home page
                    Backbone.history.navigate("/#login");
                }
            }
        });
    }
});