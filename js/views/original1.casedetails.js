window.KaseView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified

	initialize:function () {
		//this.template = templates['Employee'];
		_.bindAll(this, "toggleEdit", "resetForm");
    },
	
	 events:{
        "click .delete":		"deleteCase",
		"click .save":			"addCase",
		"click .edit": 			"toggleEdit",
		"click .reset": 		"resetForm",
		"keyup .input_class": 	"valueChanged"
    },

    render: function () {
		$(this.el).html(this.template(this.model.toJSON()));

		//console.log(this.model.toJSON());
        $('#details', this.el).html(new CaseSummaryView({model:this.model}).render().el);
		
        this.model.reports.fetch({
            success:function (data) {
                if (data.length == 0) {
                    $('.no-reports').show();
				}
            }
        });
		
		
        $('#reports', this.el).append(new KaseListView({model:this.model.reports}).render().el);
		
        return this;
    },
	
	addCase:function (event) {
        var id = $('#case_id').val();
		if (id!="") {
			this.updateCase(event);
			return;
		}
		
		event.preventDefault(); // Don't let this button submit the form
		
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/kases/add';
        console.log('Addin ... ');
        var formValues = {
			case_number: $("#casenumberInput").val(),
			case_date: $("#casedateInput").val(),
			title: $("#titleInput").val(),
			case_type: $("#casetypeInput").val(),
			case_status: $("#casestatusInput").val()
        };
		console.log("add" + formValues);
		return;
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                console.log(["Add request details: ", data]);
               
                if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
                    $('.alert-error').text(data.error.text).show();
                }
                else { // If not, send them back to the home page
                    //Backbone.history.navigate("/#");
					console.log("saved");
					$( ".edit" ).trigger( "click" );
                }
            }
        });
    },
	
	updateCase:function (event) {
        event.preventDefault(); // Don't let this button submit the form
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/kases/update';
        console.log('Updatin ... ');
		 var formValues = {
			case_number: $("#casenumberInput").val(),
			case_date: $("#casedateInput").val(),
			title: $("#titleInput").val(),
			case_type: $("#casetypeInput").val(),
			case_status: $("#casestatusInput").val()
        };
		console.log(formValues);

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                console.log(["Update request details: ", data]);
               
                if(data.error) {  // If there is an error, show the error messages
					console.log("error" + data.error.text);
                    $('.alert-error').text(data.error.text).show();
                }
                else { // If not, send them back to the home page
                    //Backbone.history.navigate("/#");
					console.log("saved");
					$( ".edit" ).trigger( "click" );
                }
            }
        });
    },
	
	deleteCase:function (event) {
        event.preventDefault(); // Don't let this button submit the form
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/kases/delete';
        console.log('Deletin ... ' + $('#case_id').val());
		var formValues = {
            id: $('#case_id').val()
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                console.log(["Delete request details: ", data]);
               
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
		//console.log(arguments[0].currentTarget.id);
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

window.KaseSummaryView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
    },

    render:function () {
        $(this.el).html(this.template(this.model.toJSON()));
		
        return this;
    }
});