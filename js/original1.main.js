 
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

var employees;
var kases;

window.Router = Backbone.Router.extend({

    routes: {
        "": "home",
        "contact": "contact",
        "applicants": "applicantsList",
		"applicants/:id": "kompanyDetails",
		"employees/:id": "employeeDetails",
		"employee/events/:id": "employeeEvents",
		"list" : "employeesList",
		"kases/:id": "kaseDetails",
		"events": "eventsList",
		"kases" : "kasesList",
		"kases_new" : "kaseNew",
		"kase/events/:id": "kaseEvents",
		
        "login" : "login",
        "logout" : "logout"
    },

    initialize: function () {
        this.headerView = new HeaderView();
        $('.header').html(this.headerView.render().el);
		$(".header").show();

        // Close the search dropdown on click anywhere in the UI
        $('body').click(function () {
            $('.dropdown').removeClass("open");
        });
		$('.dropdown').click(function () {
			$('.dropdown-toggle').dropdown("toggle");										  
		})

		this.loginLogout();
    },

	loginLogout: function(){
		//look for an employee, to get login initialized
		var current_loggin = $("#logged_in").val();
		if(current_loggin.length == 0) {
			if (typeof login_username != "undefined") {
				doLogin();
			}
			current_loggin = $("#logged_in").val();
		}
		//if after that, nothing, gotta login
		if(current_loggin.length == 0) {
			//this.headerView.select('login-menu');
			$(".header").show();
			window.location.hash = 'login';
			return;
		} else {
			//make sure that the logout is showing
			$(".header").show();
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
            this.homeView.delegateEvents(); // delegate occurences when the view is recycled
        }
		//cleanup
        $("#main_content").show();
		$('#content').show();
		$('.content_half').hide();
		$('.sidebar').hide();
		//content
		$("#content").html(this.homeView.el);
		//nav
		
		this.headerView.select('home-menu');
    },

    contact: function () {
        this.loginLogout();
		
		if (!this.contactView) {
            this.contactView = new ContactView();
            this.contactView.render();
        }
		
		$("#main_content").show();
		$('#content').show();
		$('.content_half').hide();
		$('.sidebar').hide();
		
		$('#content').html(this.contactView.el);
        
		this.headerView.select('contact-menu');
    },
	
	employeesList: function (id) {
		this.loginLogout();
		
		if (typeof id == "undefined") {
			id = "";
		}

        $("#main_content").show();
		$("#content").show();
		$('.content_half').hide();
		
		if (typeof employees == "undefined") {
			//initial call
			employees = new EmployeeCollection();
		}
		if (employees.length==0) {
			//go get the data from server
			employees.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#left_sidebar').html(new EmployeeListPageView({model: data}).render().el);
					if (id!="") {
						window.history.pushState(null, null, "#employees/" + id);
					}
					
				}
			});
		} else {
			$('#left_sidebar').html(new EmployeeListPageView({model: employees}).render().el);
		}
		
        this.headerView.select('employees-list-menu');
    },
	
	employeeDetails: function (id) {
		this.loginLogout();
		var blnNewInstance = false;
		if (typeof employees == "undefined") {
			var employee = new Employee({id: id});
			blnNewInstance = true;
		} else {
			var employee = employees.get(id);	
			if (employee.toJSON().firstName.length==0) {
				var employee = new Employee({id: id});
				blnNewInstance = true;
			}
		}
		
		if (blnNewInstance) {
			employee.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$("#main_content").show();
			
					if (id > -1) {
						$('#content').html(new EmployeeView({model: data}).render().el);
						$('#file_upload').uploadify({
							'buttonText' 	: 'Browse',
							'swf'      : 'img/uploadify.swf',
							'uploader' : '../api/uploadify.php',
							'onUploadSuccess' : function(file, data, response) {
									//alert('The file was saved to: ' + data);
									var message = "<span>" + data + "</span>";
									$("#message_panel").html(message);
									saveDocument("employees", "main", data, employee.id, "y");
								}
							// Put your options here
						});
						//side bars
						$('.sidebar').show();
					} else {
						$('#logoutLink').show();
						$('#loginLink').hide();
						$('#logged_in').val(1);
					}
				}
			});
		} else {
			$("#main_content").show();
			$("#content").show();
			$(".content_half").hide();
			$('#content').html(new EmployeeView({model: employee}).render().el);			
			//side bars
			$('.sidebar').show();
		}
    },
	
	employeeEvents: function (id) {		
		$("#main_content").show();
		
		var occurences = new OccurenceCollection();
		occurences.fetch({
				success: function(data) {
					var occurencesView = new OccurencesView({el: $("#calendar"), collection: occurences}).render();
				}
			}
		);
	},
	
	kasesList: function (id) {
		this.loginLogout();
		
		if (typeof id == "undefined") {
			id = "";
		}
		$(".header").show();
        $("#main_content").show();
		$('.sidebar').show();
		
		if (typeof kases == "undefined") {
			//initial call
			kases = new KaseCollection();
		}
		if (kases.length==0) {
			//go get the data from server
			kases.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#left_sidebar').html(new KaseListPageView({model: data}).render().el);
					if (id!="") {
						window.history.pushState(null, null, "#kases/" + id);
					}
					
				}
			});
		} else {
			$('#left_sidebar').html(new KaseListPageView({model: kases}).render().el);
		}
		
        this.headerView.select('kases-list-menu');
    },
	applicantsList: function (id) {
		this.loginLogout();
		
		if (typeof id == "undefined") {
			id = "";
		}
		$(".header").show();
        $("#main_content").show();
		$('.sidebar').show();
		
		if (typeof applicants == "undefined") {
			//initial call
			applicants = new ApplicantCollection();
		}
		if (applicants.length==0) {
			//go get the data from server
			applicants.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#left_sidebar').html(new ApplicantListPageView({model: data}).render().el);
					if (id!="") {
						window.history.pushState(null, null, "#applicants/" + id);
					}
					
				}
			});
		} else {
			$('#left_sidebar').html(new ApplicantListPageView({model: kases}).render().el);
		}
		
        this.headerView.select('applicants-list-menu');
    },
	eventsList: function (id) {
		this.loginLogout();
		
		if (typeof id == "undefined") {
			id = "";
		}
		$(".header").show();
        $("#main_content").show();
		$('.sidebar').show();
		
		if (typeof events == "undefined") {
			//initial call
			events = new EventCollection();
		}
		if (events.length==0) {
			//go get the data from server
			events.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#left_sidebar').html(new EventListPageView({model: data}).render().el);
					if (id!="") {
						window.history.pushState(null, null, "#events/" + id);
					}
					
				}
			});
		} else {
			$('#left_sidebar').html(new EventListPageView({model: events}).render().el);
		}
		
        this.headerView.select('events-list-menu');
    },
	
	kaseNew: function() {
		var kase = new Kase({case_id: "", case_number: "", case_date: "", case_status: "open", case_type: "", submittedOn: "", title: ""});
		
		$(".header").show();
		$("#main_content").show();
		$('#content').hide();
		$('.content_half').show();
		
		$('#content_left').html(new KaseView({model: kase}).render().el);
		
		this.headerView.select('kases-new-menu');
	},
	
	kaseDetails: function (id) {
		this.loginLogout();
	
		//alert(id + " - this id");
        var kase = new Kase({id: id});
        kase.fetch({
            success: function (data) {
                // Note that we could also 'recycle' the same instance of EmployeeFullView
                // instead of creating new instances
				$("#main_content").show();
				$('#content').show();
				//$('.content_half').show();
				
                if (id > -1) {
					$('#content').html(new KaseView({model: data}).render().el);
					//can i access the subview of the view now?
					if (login_username=="nick") {
						var kompany = new Kompany({id: 1});
						$('#kase_content').html(new KompanyView({model: kompany}).render().el);
					}
				}
            }
        });
		
    },
	
	kaseEvents: function (id) {		
		var occurences = new OccurenceCollection();
		occurences.fetch({
				success: function(data) {
					$("#main_content").show();
					$('#content').show();
					$('.content_half').hide();
					
					var occurencesView = new OccurencesView({el: $("#content"), collection: occurences}).render();
				}
			}
		);
	},
	
	kompanyDetails: function (id) {
		this.loginLogout();
	
		//alert(id + " - this id");
        var kompany = new Kompany({id: id});
		
		$("#main_content").show();
		$('#content').show();
		$('.content_half').hide();
		
		if (id > -1) {
			$('#content').html(new ApplicantView({model: applicant}).render().el);
		}
		/*		
        kompany.fetch({
            success: function (data) {
                // Note that we could also 'recycle' the same instance of EmployeeFullView
                // instead of creating new instances
				$("#main_content").show();
				$('#content').hide();
				$('.content_half').show();
				
                if (id > -1) {
					$('#content_right').html(new KompanyView({model: data}).render().el);
				}
            }
        });
		*/
    },
	
    login: function() {
		$("#main_content").show();
		
        $('#content').html(new LoginView().render().el);
		$('#logoutLink').hide();
		$('#loginLink').show();
		$('#logged_in').val('');
		
		//no side bars
		$('.sidebar').hide();
		
		//this.headerView.select('login-menu');
		$(".header").hide();
    },
    logout: function() {
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = '../api/logout';
    
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: "",
            success:function (data) {
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

var checkOffline = function() {
	Offline.check();
	if (Offline.state=="up") {
		indicateOn();
	}
	if (Offline.state=="down") {
		indicateOff();
	}
}
var indicateOff = function() {
	//console.log( "Off - " + Date.now());
	$("#connected").hide();
	$("#disconnected").show();
}
var indicateOn = function() {
	//console.log( "On - " + Date.now());
	$("#connected").show();
	$("#disconnected").hide();
}
var intervalID = setInterval(checkOffline, 20000);

function sortIt(obj, page) {
	var $rows = $('.' + page + '_data_row');
	var theobj = $("#" + obj.id);
	var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();

	$rows.show().filter(function() {
		var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
		return !~text.indexOf(val);
	}).hide();
}

function gridsterIt (gridster_index) {
	if (typeof gridster_index == "undefined") {
		gridster_index = -1;
	}
	var gridster = [];
	$(function () {
		//kompany applicant gridster
		if (gridster_index < 0 || gridster_index==0) {
			gridster[0] = $("#gridster_tall ul").gridster({
				namespace: '#gridster_tall',
				widget_margins: [5, 5],
				widget_base_dimensions: [240, 40],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[0].serialize();
						console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
		}
		//kase gridster header
		if (gridster_index < 0 || gridster_index==1) {
			gridster[1] = $("#gridster_flat ul").gridster({
				namespace: '#gridster_flat',
				widget_margins: [5, 5],
				widget_base_dimensions: [180, 40],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[1].serialize();
						console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			gridster[1].options.max_rows = 1;
		}
		//next gridster
		if (gridster_index < 0 || gridster_index==2) {
			gridster[1] = $("#gridster_tab ul").gridster({
				namespace: '#gridster_tab',
				widget_margins: [5, 5],
				widget_base_dimensions: [180, 40],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[1].serialize();
						console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			gridster[1].options.max_rows = 1;
		}
	});
}

function isTouchDevice() {
	var ua = navigator.userAgent;
	var isTouchDevice = (
		ua.match(/iPad/i) ||
		ua.match(/iPhone/i) ||
		ua.match(/iPod/i) ||
		ua.match(/Android/i)
	);

	return isTouchDevice;
}

function editField(field_object) {
	var theclass = "";
	if (typeof field_object != "undefined") {
		//get the name from the object
		if (typeof field_object.id == "undefined") {
			var field_name = field_object.attr("id");
			field_name = field_name.replace("Grid", "");
			//let's get the class
			theclass = field_object.parent().parent().attr("class");
		} else {
			var field_name = field_object.id;
			field_name = field_name.replace("Grid", "");
			//let's get the class
			theclass = field_object.parentElement.parentElement.className;
		}
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + field_name;
		
	} else {
		//field_name = "#" + field_name;
		return;
	}
	//edit the field, only if it is not already in editing mode
	if ($(field_name + "Span").hasClass("editing")) {
		//turn it all off
		$(field_name + "Span").toggleClass("hidden");
		$(field_name + "Input").toggleClass("hidden");
		$(field_name + "Save").toggleClass("hidden");
		
		$(field_name + "Span").removeClass("editing");
		$(field_name + "Input").removeClass("editing");
		$(field_name + "Save").removeClass("editing");
		return;
	}
	//show the input, hide the span
	$(field_name + "Span").toggleClass("hidden");
	$(field_name + "Input").toggleClass("hidden");
	$(field_name + "Save").toggleClass("hidden");
	
	$(field_name + "Span").addClass("editing");
	$(field_name + "Input").addClass("editing");
	$(field_name + "Save").addClass("editing");
}

var saveDocument = function(tableName, attributeName, documentName, tableId, clearFirst) {
	//console.log(imagename + " " + employee_id);
	$('.alert-error').hide(); // Hide any errors on a new submit
    var url = '../api/' + tableName + '/documents/add';
	var formValues = {
		tableId: tableId,
		attribute: attributeName,
		name: documentName,
		tableId: tableId,
		uploaded_by: login_username,
		clearFirst: clearFirst
	};

	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
		   
			if(data.error) {  // If there is an error, show the error messages
				$('.alert-error').text(data.error.text).show();
			}
			else { // If not, send them back to the home page
				//Backbone.history.navigate("/#");
				$(".alert-success.employee").html("Document Saved");
				$(".alert-success.employee").fadeIn(function() { 
					setTimeout(function() {
							$(".alert-success.employee").fadeOut();
						},1500);
				});
			}
		}
	});
}

templateLoader.load(["HomeView", "ContactView", "HeaderView", "EmployeeView", "EmployeeSummaryView", "EmployeeListPageView", "EmployeeListItemView", "KaseView", "KaseHeaderView", "KaseSummaryView", "KaseListPageView", "KaseListItemView", "LoginView", "KompanyView", "KompanySummaryView", "ApplicantListPageView, EventListPageView"],
    function () {
        app = new Router();
        Backbone.history.start();
	}
);