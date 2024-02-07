window.Router = Backbone.Router.extend({
    routes: {
		"": 													"home",
		"company/setup":										"editCompany",
		"company/setup/taxes":									"manageCompanyTaxes",
		"employees":											"listEmployees",
		"employees/:id":										"editEmployee",
		"employees/checks/:id":									"listEmployeeChecks",
		"employees/reimbursments/:id":							"manageEmployeeReimbursments",
		"paycheck/create/:user_id":								"createPaycheck",
		"paycheck/edit/:user_id/:check_id":						"editPaycheck",
		"paychecks/list":										"listChecks",
		"paychecks/contractors/create":							"createContractorsChecks",
		"reimbursments":										"listReimbursments",
		"reimbursment/:id":										"editReimbursment"
	},
    initialize: function () {
		var self = this;
		
		data = new Backbone.Model();
		data.set("holder", ".app_header");
		$(".app_header").html(new navigation_view({model: data}).render().el);
		
		setTimeout(function() {
			GetClock();
			setInterval(GetClock,1000);
		}, 1000);
    },
	home: function() {
		var data = new Backbone.Model();
		data.set("holder", "content_top");
		$('#content_top').html(new home_view({model: data}).render().el);				
	},
	editCompany: function() {
		//fetch the js
		if (typeof window.company_edit == "undefined") {
			loadJsCssFile("js/views/company_details.js", "js");
			var self = this;
			setTimeout(function() {
				self.editCompany();
			}, 10);
			return;
		}
		//fetch the html
		var url = "api/company_edit.php";
		$.ajax({
			url:url,
			type:'GET',
			dataType:"text",
			data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "content_top");
					mod.set("html", data);
					$('#content_top').html(new company_edit({model: mod}).render().el);				
				}
			}
		});
	},
	listEmployees: function() {
		//fetch the js
		if (typeof window.employee_list == "undefined") {
			loadJsCssFile("js/views/employee_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.listEmployees();
			}, 10);
			return;
		}
		//fetch the html
		var url = "api/employee_list.php";
		$.ajax({
			url:url,
			type:'GET',
			dataType:"text",
			data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "content_top");
					mod.set("html", data);
					$('#content_top').html(new employee_list({model: mod}).render().el);				
				}
			}
		});
	},
	editEmployee: function(id) {
		//fetch the js
		if (typeof window.employee_edit == "undefined") {
			loadJsCssFile("js/views/employee_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.editEmployee(id);
			}, 10);
			return;
		}
		
		var formData = "user_id=" + id;
		//fetch the html
		if (id=="new") {
			var url = "api/employee_new.php";
		} else {
			var url = "api/employee_edit.php";
		}
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formData,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "content_top");
					mod.set("html", data);
					mod.set("user_id", id);
					$('#content_top').html(new employee_edit({model: mod}).render().el);				
				}
			}
		});
	},
	createPaycheck: function(user_id) {
		//fetch the js
		if (typeof window.paycheck_edit == "undefined") {
			loadJsCssFile("js/views/paycheck_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.createPaycheck(user_id);
			}, 10);
			return;
		}
		var formData = "user_id=" + user_id;
		//fetch the html
		var url = "api/paycheck_edit.php";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formData,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "content_top");
					mod.set("html", data);
					mod.set("user_id", user_id);
					$('#content_top').html(new paycheck_edit({model: mod}).render().el);				
				}
			}
		});
	},
	editPaycheck: function(user_id, check_id) {
		//fetch the js
		if (typeof window.paycheck_edit == "undefined") {
			loadJsCssFile("js/views/paycheck_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.editPaycheck(user_id, check_id);
			}, 10);
			return;
		}
		var formData = "user_id=" + user_id + "&check_id=" + check_id;
		//fetch the html
		var url = "api/paycheck_edit.php";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formData,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "content_top");
					mod.set("html", data);
					mod.set("user_id", user_id);
					mod.set("check_id", check_id);
					$('#content_top').html(new paycheck_edit({model: mod}).render().el);				
				}
			}
		});
	},
	listChecks: function() {
		//fetch the js
		if (typeof window.paycheck_edit == "undefined") {
			loadJsCssFile("js/views/paycheck_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.listChecks();
			}, 10);
			return;
		}
		//fetch the html
		var url = "api/paychecks_list.php";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "content_top");
					mod.set("html", data);
					$('#content_top').html(new paychecks_list({model: mod}).render().el);				
				}
			}
		});
	},
	listEmployeeChecks: function(id) {
		//fetch the js
		if (typeof window.paycheck_edit == "undefined") {
			loadJsCssFile("js/views/paycheck_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.listEmployeeChecks(id);
			}, 10);
			return;
		}
		var formData = "user_id=" + id;
		//fetch the html
		var url = "api/paychecks_list.php";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formData,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "content_top");
					mod.set("html", data);
					mod.set("user_id", id);
					$('#content_top').html(new paychecks_list({model: mod}).render().el);				
				}
			}
		});
	},
	createContractorsChecks: function() {
		//fetch the js
		if (typeof window.paycheck_edit == "undefined") {
			loadJsCssFile("js/views/paycheck_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.createContractorsChecks();
			}, 10);
			return;
		}
		var formData = "";
		//fetch the html
		var url = "api/contractors_paychecks_create.php";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formData,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "content_top");
					mod.set("html", data);
					$('#content_top').html(new contractors_paychecks_create({model: mod}).render().el);				
				}
			}
		});
	},
	listReimbursments: function() {
		//fetch the js
		if (typeof window.reimbursments_list == "undefined") {
			loadJsCssFile("js/views/reimbursment_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.listReimbursments();
			}, 10);
			return;
		}
		//fetch the html
		var url = "api/reimbursments_list.php";
		$.ajax({
			url:url,
			type:'GET',
			dataType:"text",
			data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "company_edit_holder");
					mod.set("html", data);
					$('#company_edit_holder').html(new reimbursments_list({model: mod}).render().el);				
				}
			}
		});
	},
	editReimbursment: function(id) {
		//fetch the js
		if (typeof window.reimbursments_list == "undefined") {
			loadJsCssFile("js/views/reimbursment_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.editReimbursment(id);
			}, 10);
			return;
		}
		var formData = "reimbursment_id=" + id;
		//fetch the html
		var url = "api/reimbursment_edit.php";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formData,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "content_top");
					mod.set("html", data);
					mod.set("reimbursment_id", id);
					$('#content_top').html(new reimbursment_edit({model: mod}).render().el);				
				}
			}
		});
	},
	manageEmployeeReimbursments: function(id) {
		//fetch the js
		if (typeof window.reimbursments_list == "undefined") {
			loadJsCssFile("js/views/reimbursment_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.manageEmployeeReimbursments(id);
			}, 10);
			return;
		}
		//fetch the html
		var url = "api/employee_reimbursments_list.php";
		var formData = "user_id=" + id;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formData,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "content_top");
					mod.set("html", data);
					mod.set("user_id", id);
					$('#content_top').html(new employee_reimbursments_list({model: mod}).render().el);				
				}
			}
		});
	},
	editPayschedule: function() {
		//fetch the js
		if (typeof window.payschedule_edit == "undefined") {
			loadJsCssFile("js/views/payschedule_views.js", "js");
			var self = this;
			setTimeout(function() {
				self.editPayschedule();
			}, 10);
			return;
		}
		//fetch the html
		var url = "api/payschedule_edit.php";
		$.ajax({
			url:url,
			type:'GET',
			dataType:"text",
			data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "company_edit_holder");
					mod.set("html", data);
					$('#company_edit_holder').html(new payschedule_edit({model: mod}).render().el);				
				}
			}
		});
	},
	manageCompanyTaxes: function() {
		//fetch the js
		if (typeof window.company_taxes_edit == "undefined") {
			loadJsCssFile("js/views/company_details.js", "js");
			var self = this;
			setTimeout(function() {
				self.manageCompanyTaxes();
			}, 10);
			return;
		}
		//fetch the html
		var url = "api/company_taxes_edit.php";
		$.ajax({
			url:url,
			type:'GET',
			dataType:"text",
			data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//put it in the template holder
					var mod = new Backbone.Model();
					mod.set("holder", "company_edit_holder");
					mod.set("html", data);
					$('#company_edit_holder').html(new company_taxes_edit({model: mod}).render().el);				
				}
			}
		});
	}
});

// Tell jQuery to watch for any 401 or 403 errors and handle them appropriately
$.ajaxSetup({
    statusCode: {
        401: function(){
            logOut();
         
        },
        403: function() {
            // 403 -- Access denied
            logOut();
        }
    }
});
function logOut() {
	var formData = "user_id=" + login_user_id;
	formData += "&sess_id=" + current_session_id;
	formData += "&customer_id=" + customer_id;
	console.log("time: " + $(".navbar-brand").prop("title"));
	var url = 'api/relogin';

	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formData,
		success:function (data) {
			if (data.success) {
				writeCookie('sess_id', data.sess_id, 60*60*8);
				current_session_id = data.sess_id;
				
			} else {
				fullLogOut();
			}
		}
	});
}
function fullLogOut() {
	$('.alert-error').hide(); // Hide any errors on a new submit
	var url = 'api/logout';

	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: "",
		success:function (data) {
			$("#logged_in").val('');
			//clear the cookie
			writeCookie('sess_id', '');
			if (typeof current_url == "undefined") {
				current_url = '';
			}
			writeCookie('origin', current_url);
			if(data.error) {  // If there is an error, show the error messages
				$('.alert-error').text(data.error.text).show();
			}
			else { // If not, send them back to the home page
				document.location.href = "index.php";
			}
		}
	});
}

var tday = new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
var tmonth = new Array("January","February","March","April","May","June","July","August","September","October","November","December");

function GetClock(){
	var d = new Date();
	var nday = d.getDay(),nmonth=d.getMonth(),ndate=d.getDate(),nyear=d.getFullYear();
	var nhour = d.getHours(),nmin=d.getMinutes(),nsec=d.getSeconds(),ap;
	
	if(nhour==0){ap=" AM";nhour=12;}
	else if(nhour<12){ap=" AM";}
	else if(nhour==12){ap=" PM";}
	else if(nhour>12){ap=" PM";nhour-=12;}
			
	if(nhour<=9) nhour="0"+nhour;
	if(nmin<=9) nmin="0"+nmin;
	if(nsec<=9) nsec="0"+nsec;
	
	setTimeout(function() {
		document.getElementById('clockbox').innerHTML = "" + tday[nday] + ", " + tmonth[nmonth] + " " + ndate + ", " + nyear + " " + nhour + ":" + nmin + ":" + nsec + ap + "";
	}, 0);
}

//load templates

templateLoader.load([],
    function () {
        app = new Router();
        Backbone.history.start();
	}
);
String.prototype.cleanString = function() {
	return this.replace(/[\[\]|&;$%@"<>()+,]/g, "");
}
String.prototype.replaceAll = function(find, replace) {
  return this.replace(new RegExp(find, 'g'), replace);
}
function showContent() {
	setTimeout(function() {
		$('#content').fadeIn();
	}, 100);
}
function showContentTop() {
	$('#content_top').fadeIn();
}
function showSearchResults() {
	$('#search_results').fadeIn();
}
function hideSearchResults() {
	$('#search_results').hide();
}
function hideContent() {
	$('#content').hide();
}
function showPopup(number) {
	//setTimeout(function() {
		$("#pop_up_button_" + number).hide(function() {
			$("#popupLogin" + number).fadeIn(function() {
				var product_number_model = "product_" + number;
				
				product_number_model = new Product({product_id: -1});
				product_number_model.fetch({
					success: function (product_number_model) {
						product_number_model.set("model_number", "product_" + number);
						$('#popupLogin' + number).html(new purchase_product_view({model:product_number_model}).render().el);
					}
				});
			});
		});
	//}, 100);
}
