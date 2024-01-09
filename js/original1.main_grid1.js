 
 // Tell jQuery to watch for any 401 or 403 errors and handle them appropriately
$.ajaxSetup({
    statusCode: {
        401: function(){
            // Redirec the to the login page.
            window.location.replace('#login');
			$('#logoutLink').hide();
         
        },
        403: function() {
            // 403 -- Access denied
            window.location.replace('#denied');
        }
    }
});

window.Router = Backbone.Router.extend({

    routes: {
        "": "home",
        "contact": "contact",
        "employees/:id": "employeeDetails",
        "login" : "login",
        "logout" : "logout",
		"list" : "employeesList"
    },

    initialize: function () {
        this.headerView = new HeaderView();
        $('.header').html(this.headerView.render().el);
		
		$('#sidebar_right').html("<div></div>");

        // Close the search dropdown on click anywhere in the UI
        $('body').click(function () {
            $('.dropdown').removeClass("open");
        });
		this.loginLogout();
    },

	loginLogout: function(){
		//look for an employee, to get login initialized
		var current_loggin = $("#logged_in").val();
		console.log("curr:" + current_loggin);
		if(current_loggin.length == 0) {
			this.headerView.select('login-menu');
			window.location.hash = 'login';
			return;
		} else {
			//make sure that the logout is showing
			$('#logoutLink').show();
		}
	},
	
    home: function () {
        this.loginLogout();
		
		// Since the home view never changes, we instantiate it and render it only once
        if (!this.homeView) {
            this.homeView = new HomeView();
            this.homeView.render();
        } else {
            this.homeView.delegateEvents(); // delegate events when the view is recycled
        }
        $("#content").html(this.homeView.el);
		
		$('.sidebar').html("<div></div>");
		
		this.headerView.select('home-menu');
    },

    contact: function () {
        this.loginLogout();
		
		if (!this.contactView) {
            this.contactView = new ContactView();
            this.contactView.render();
        }
        $('#content').html(this.contactView.el);
		
		$('#sidebar_right').html("<div></div>");
		$('#sidebar_left').html("<div></div>");
		
        this.headerView.select('contact-menu');
    },
	employeesList: function (id) {
		this.loginLogout();
		
		if (typeof id == "undefined") {
			id = "";
		}
		console.log("gridding");
		/*
		if (!this.employeeGridView) {
            this.employeeGridView = new EmployeeGrid();
            this.employeeGridView.render();
        }
		$('#sidebar_left').html(this.employeeGridView.el);
		*/
        var employees = new EmployeeCollection();

		employees.fetch({
            success: function (data) {
				console.log("success list view:" + id);
				//console.log(data.toJSON()[0]);
                // Note that we could also 'recycle' the same instance of EmployeeFullView
                // instead of creating new instances
                $('#sidebar_left').html(new EmployeeListPageView({model: data}).render().el);
				if (id=="") {
					$('#content').html("<div></div>");
				} else {
					window.history.pushState(null, null, "#employees/" + id);
				}
				$('#sidebar_right').html("<div></div>");
            }
        });
		
        this.headerView.select('employees-list-menu');
    },

    employeeDetails: function (id) {
		this.loginLogout();
		
		//first make sure the list is up
		console.log("starting details");
        var employee = new Employee({id: id});
        employee.fetch({
            success: function (data) {
                // Note that we could also 'recycle' the same instance of EmployeeFullView
                // instead of creating new instances
                if (id > -1) {
                	$('#content').html(new EmployeeView({model: data}).render().el);
					$('#sidebar_right').html("<img src='http://cstmwb.com/autho/web/img/calendar_icon.png' width='150' height='122'>");
					//need a list on the side, keeping current id in mind
					console.log("entering details area");
					if ($('#sidebar_left').html()=="") {
						console.log("generating list");
						window.location.replace('#list/' + id);
					}
				} else {
					$('#logoutLink').show();
					$('#loginLink').hide();
					$('#logged_in').val(1);
				}
            }
        });
    },
	
    login: function() {
        $('#content').html(new LoginView().render().el);
		$('#logoutLink').hide();
		$('#loginLink').show();
		$('#logged_in').val('');
		
		$('#sidebar_right').html("<div></div>");
		$('#sidebar_left').html("<div></div>");
		
		this.headerView.select('login-menu');
    },
    logout: function() {
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = '../api/logout';
        console.log('Loggin out... ');
    
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: "",
            success:function (data) {
                console.log(["Logout request details: ", data]);
				
				$("#logged_in").val('');
				
                if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
                else { // If not, send them back to the home page
                    //Backbone.history.navigate("/#login");
					 window.location.hash = "#login";
                }
            }
        });
    }

});
console.log("loader");
templateLoader.load(["HomeView", "ContactView", "HeaderView", "EmployeeView", "EmployeeSummaryView", "EmployeeListPageView", "EmployeeListItemView", "LoginView"],
    function () {
        app = new Router();
        Backbone.history.start();
    });