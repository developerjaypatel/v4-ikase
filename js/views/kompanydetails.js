window.KompanyView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
		_.bindAll(this, "toggleKompanyEdit", "resetKompanyForm", "saveSuccessful");
    },
	
	 events:{
        "click .kompany .delete":		"deleteKompany",
		"click .kompany .save":			"addKompany",
		"click .kompany .save_field":	"saveKompanyField",
		"click .kompany .edit": 		"toggleKompanyEdit",
		"click .kompany .reset": 		"resetKompanyForm",
		"click .kompany .calendar": 	"showCalendar",
		"keyup .kompany .input_class": 	"valueChanged"
    },
	
	
    render: function () {
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		mymodel = this.model.toJSON();
		$(this.el).html(this.template(mymodel));
		
        //$('#details', this.el).html(new KompanySummaryView({model:this.model}).render().el);
		
		this.model.set("editing", false);
		
		if(this.model.id==""){
			//editing mode right away
			this.model.set("editing", true);
			setTimeout('$( ".kompany .edit" ).trigger( "click" )', 500);
		}		
        
		setTimeout("gridsterIt()", 100);
        return this;
    },
	
	saveKompanyField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		
		//restore the read look
		editField(element);
		
		var element_value = $("#" + element + "Input").val();
		$("#" + element + "Span").html(element_value);
		
		this.model.set(element, element_value);
		
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		this.addKompany(event);
	},
	
	addKompany:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		$blnValid = $("#kase_form").parsley('validate');
		
		//get out if invalid			
		if (!$blnValid) {
			$(".alert-warning").show();
			$(".alert-text").html("Please fill in the required fields in the correct format.");
			return;
		}
		
		$(".alert-warning").hide();
		
		var id = $('#id').val();
		if (id!="") {
			this.updateKompany(event);
			return;
		}
		
		$('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/kases/add';
        console.log('Addin ... ');
        var formValues = {
			case_number: $("#case_numberInput").val(),
			case_date: $("#case_dateInput").val(),
			title: $("#titleInput").val(),
			case_type: $("#case_typeInput").val(),
			case_status: $("#case_statusInput").val()
        };
		console.log(formValues);
		//return;
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                console.log(["Add request details: ", data]);
               
                if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
                    self.saveFailed(data.error.text);
                }
                else { // If not, go back to read mode
                    $( ".edit" ).trigger( "click" );
					self.saveSuccessful();
                }
            }
        });
		//make sure the collection is updated
		this.model.set(formValues);
		if (typeof kases != "undefined") {
			kases.add(formValues);
		}
    },
	
	updateKompany:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		
		var self = this;
        $('.alert-error').hide(); // Hide any errors on a new submit

        var url = 'api/kases/update';
        console.log('Updatin ... ');
		 var formValues = {
			id: $('#id').val(),
			case_number: $("#case_numberInput").val(),
			case_date: $("#case_dateInput").val(),
			title: $("#titleInput").val(),
			case_type: $("#case_typeInput").val(),
			case_status: $("#case_statusInput").val()
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                if(data.error) {  // If there is an error, show the error messages
					console.log("error" + data.error.text);
                    self.saveFailed(data.error.text);
                }
                else { // If not, go  back to read mode
                    $( ".edit" ).trigger( "click" );
					self.saveSuccessful();
                }
            }
        });
		
		//make sure the collection is updated
		this.model.set(formValues);
		if (typeof kases != "undefined") {
			kases.get(this.model.id).set(formValues);
		}
    },
	
	saveSuccessful: function() {
		$(".alert-success").fadeIn(function() { 
			setTimeout(function() {
					$(".alert-success").fadeOut();
				},1500);
		});
	},
	
	saveFailed:  function(text) {
		$('.alert-error').text(text)
		$(".alert-error").fadeIn(function() { 
			setTimeout(function() {
					$(".alert-error").fadeOut();
				},1500);
		});
		
	},
	
	deleteKompany:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		var self = this;
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/kases/delete';
        console.log('Deletin ... ' + $('#id').val());
		var formValues = {
            id: $('#id').val()
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                console.log(["Delete request details: ", data]);
               
                if(data.error) {  // If there is an error, show the error messages
                    self.saveFailed(data.error.text);
                }
                else { // If not, send them back to the home page
                    Backbone.history.navigate("/#kases");
                }
            }
        });
    },
	
	toggleKompanyEdit: function(e) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
			
		//get all the editing fields, and toggle them back
		$(".kompany .editing").toggleClass("hidden");
		$(".kompany .span_class").removeClass("editing");
		$(".kompany .input_class").removeClass("editing");
		
		$(".kompany .span_class").toggleClass("hidden");
		$(".kompany .input_class").toggleClass("hidden");
		$(".kompany .input_holder").toggleClass("hidden");
		$(".button_row.kompany").toggleClass("hidden");
		$(".edit_row.kompany").toggleClass("hidden");
	},
	
	showCalendar:function(event) {
		window.location.hash = "#kase/events/" + $('#id').val()
	},
	
	resetKompanyForm: function(e) {
		this.toggleKompanyEdit(e);
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
/*
window.KompanySummaryView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
    },

    render:function () {
        $(this.el).html(this.template(this.model.toJSON()));
		
        return this;
    }
});
*/
window.KompanySummaryView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
		_.bindAll(this, "toggleKompanyEdit", "resetForm", "saveSuccessful");
    },
	
	 events:{
        "click .delete":		"deleteKompany",
		"click .save":			"addKompany",
		"click .save_field":	"saveKompanyField",
		"click .edit": 			"toggleKompanyEdit",
		"click .reset": 		"resetForm",
		"click .calendar": 		"showCalendar",
		"keyup .input_class": 	"valueChanged"
    },
	
	
    render: function () {
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		mymodel = this.model.toJSON();
		$(this.el).html(this.template(mymodel));
		
        //$('#details', this.el).html(new KompanySummaryView({model:this.model}).render().el);
		
		this.model.set("editing", false);
		
		if(this.model.id==""){
			//editing mode right away
			this.model.set("editing", true);
			setTimeout('$( ".edit" ).trigger( "click" )', 500);
		}		
        
		setTimeout("gridsterIt()", 100);
        return this;
    },
	
	saveKompanyField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		
		//restore the read look
		editField(element);
		
		var element_value = $("#" + element + "Input").val();
		$("#" + element + "Span").html(element_value);
		
		this.model.set(element, element_value);
		
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		this.addKompany(event);
	},
	
	addKompany:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		$blnValid = $("#kase_form").parsley('validate');
		
		//get out if invalid			
		if (!$blnValid) {
			$(".alert-warning").show();
			$(".alert-text").html("Please fill in the required fields in the correct format.");
			return;
		}
		
		$(".alert-warning").hide();
		
		var id = $('#id').val();
		if (id!="") {
			this.updateKompany(event);
			return;
		}
		
		$('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/kases/add';
        console.log('Addin ... ');
        var formValues = {
			case_number: $("#case_numberInput").val(),
			case_date: $("#case_dateInput").val(),
			title: $("#titleInput").val(),
			case_type: $("#case_typeInput").val(),
			case_status: $("#case_statusInput").val()
        };
		console.log(formValues);
		//return;
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                console.log(["Add request details: ", data]);
               
                if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
                    self.saveFailed(data.error.text);
                }
                else { // If not, go back to read mode
                    $( ".edit" ).trigger( "click" );
					self.saveSuccessful();
                }
            }
        });
		//make sure the collection is updated
		this.model.set(formValues);
		if (typeof kases != "undefined") {
			kases.add(formValues);
		}
    },
	
	updateKompany:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		
		var self = this;
        $('.alert-error').hide(); // Hide any errors on a new submit

        var url = 'api/kases/update';
        console.log('Updatin ... ');
		 var formValues = {
			id: $('#id').val(),
			case_number: $("#case_numberInput").val(),
			case_date: $("#case_dateInput").val(),
			title: $("#titleInput").val(),
			case_type: $("#case_typeInput").val(),
			case_status: $("#case_statusInput").val()
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                if(data.error) {  // If there is an error, show the error messages
					console.log("error" + data.error.text);
                    self.saveFailed(data.error.text);
                }
                else { // If not, go  back to read mode
                    $( ".edit" ).trigger( "click" );
					self.saveSuccessful();
                }
            }
        });
		
		//make sure the collection is updated
		this.model.set(formValues);
		if (typeof kases != "undefined") {
			kases.get(this.model.id).set(formValues);
		}
    },
	
	saveSuccessful: function() {
		$(".alert-success").fadeIn(function() { 
			setTimeout(function() {
					$(".alert-success").fadeOut();
				},1500);
		});
	},
	
	saveFailed:  function(text) {
		$('.alert-error').text(text)
		$(".alert-error").fadeIn(function() { 
			setTimeout(function() {
					$(".alert-error").fadeOut();
				},1500);
		});
		
	},
	
	deleteKompany:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		var self = this;
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/kases/delete';
        console.log('Deletin ... ' + $('#id').val());
		var formValues = {
            id: $('#id').val()
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                console.log(["Delete request details: ", data]);
               
                if(data.error) {  // If there is an error, show the error messages
                    self.saveFailed(data.error.text);
                }
                else { // If not, send them back to the home page
                    Backbone.history.navigate("/#kases");
                }
            }
        });
    },
	
	toggleKompanyEdit: function(e) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
			
		//get all the editing fields, and toggle them back
		$(".kompany.editing").toggleClass("hidden");
		$(".kompany.span_class").removeClass("editing");
		$(".kompany.input_class").removeClass("editing");
		
		$(".kompany.span_class").toggleClass("hidden");
		$(".kompany.input_class").toggleClass("hidden");
		$(".kompany.input_holder").toggleClass("hidden");
		$(".kompany.button_row").toggleClass("hidden");
		$(".kompany.edit").toggleClass("hidden");
	},
	
	showCalendar:function(event) {
		window.location.hash = "#kase/events/" + $('#id').val()
	},
	
	resetForm: function(e) {
		this.toggleKompanyEdit(e);
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