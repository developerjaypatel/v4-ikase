window.EmployeeView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified

	initialize:function () {
		//this.template = templates['Employee'];
		_.bindAll(this, "toggleEdit", "resetForm");
    },
	
	 events:{
        "click .delete":		"deleteEmployee",
		"click .save":			"saveEmployee",
		"click .edit": 			"toggleEdit",
		"click .events": 		"showCalendar",
		"click .reset": 		"resetForm",
		"keyup .input_class": 	"valueChanged"
    },

    render: function () {
        
		/*
		_.templateSettings = {
		  interpolate: /\{\{(.+?)\}\}/g
		};
		*/
		$(this.el).html(this.template(this.model.toJSON()));

		$('#details', this.el).html(new EmployeeSummaryView({model:this.model}).render().el);
		
        this.model.reports.fetch({
            success:function (data) {
                if (data.length == 0) {
                    $('.no-reports').show();
				}
            }
        });
		
		
        $('#reports', this.el).append(new EmployeeListView({model:this.model.reports}).render().el);
		
        return this;
    },
	
	saveEmployee:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		
		$blnValid = $("#employee_form").parsley('validate');
		
		//get out if invalid			
		if (!$blnValid) {
			$(".alert-warning").show();
			$(".alert-text").html("Please fill in the required fields in the correct format.");
			return;
		}
		
		$(".alert-warning").hide();
		
		var id = $('#employeeId').val();
		if (id!="") {
			this.updateEmployee(event);
			return;
		}
		
		$('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/employees/add';

		var fullName = $("#nameInput").val();
		var arrName = fullName.split(" ");
        var formValues = {
			firstName: arrName[0],
			lastName: arrName[1],
			title: $("#titleInput").val(),
			officePhone: $("#phoneInput").val(),
			cellPhone: $("#cellphoneInput").val(),
			email: $("#emailInput").val(),
			twitterId: 0
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
					$( ".edit" ).trigger( "click" );
                }
            }
        });
    },
	
	showCalendar:function(event) {
		console.log("/#employee/events/" + $('#employeeId').val());
		window.location.hash = "#employee/events/" + $('#employeeId').val()
	},
	
	updateEmployee:function (event) {
        event.preventDefault(); // Don't let this button submit the form
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/employees/update';

		var fullName = $("#nameInput").val();
		var arrName = fullName.split(" ");
        var formValues = {
            id: $('#employeeId').val(),
			firstName: arrName[0],
			lastName: arrName[1],
			title: $("#titleInput").val(),
			officePhone: $("#phoneInput").val(),
			cellPhone: $("#cellphoneInput").val(),
			email: $("#emailInput").val(),
			twitterId: 0
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
					$( ".edit" ).trigger( "click" );
                }
            }
        });
    },
	
	deleteEmployee:function (event) {
        event.preventDefault(); // Don't let this button submit the form
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/employees/delete';

		var formValues = {
            id: $('#employeeId').val()
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
                    Backbone.history.navigate("/#");
                }
            }
        });
    },
	
	toggleEdit: function(e) {
		event.preventDefault(); // Don't let this button submit the form
		
		$(".span_class").toggleClass("hidden");
		$(".input_class").toggleClass("hidden");
		$(".button_row").toggleClass("hidden");
		$(".edit").toggleClass("hidden");
	},
	
	resetForm: function(e) {
		this.toggleEdit(e);
		this.render();
	},
	
	valueChanged: function(e) {
		var source = arguments[0].currentTarget.id;
		source = source.replace("Input", "");
		
		var newval = $("#" + source + "Input").val();
		if (newval==""){
			if ($("#" + source + "Input").hasClass("required")) {
			newval = "Please fill me in";	
				$("#" + source + "Span").toggleClass("hidden");
			}
		} else {
			if (!$("#" + source + "Span").hasClass("hidden")) {
				$("#" + source + "Span").addClass("hidden");
			}
		}
		$("#" + source + "Span").html(newval);
	}
});

window.EmployeeSummaryView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
    },

    render:function () {
        $(this.el).html(this.template(this.model.toJSON()));
		
        return this;
    }
});