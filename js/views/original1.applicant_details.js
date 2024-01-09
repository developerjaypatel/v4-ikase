window.applicant_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
		_.bindAll(this, "toggleApplicantEdit", "resetApplicantForm", "saveSuccessful");
    },
	
	 events:{
        "click .applicant .delete":				"deleteApplicantView",
		"click .applicant .save":				"addApplicantView",
		"click .applicant .save_field":			"saveApplicantViewField",
		"click .applicant .edit": 				"toggleApplicantEdit",
		"click .applicant .reset": 				"resetApplicantForm",
		"click .kase .calendar": 				"showCalendar",
		"keyup .applicant .input_class": 		"valueApplicantViewChanged",
		"dblclick .applicant .gridster_border": "editApplicantViewField",
		"dblclick #notesGrid": 					"editApplicantViewNotesField"
    },
	
    render: function () {
		if (typeof this.template != "function") {
			if (typeof this.model.get("holder") == "undefined") {
				this.model.set("holder", "kase_content");
			}
			var view = "applicant_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		mymodel = this.model.toJSON();
		$(this.el).html(this.template(mymodel));
		
        //$('#details', this.el).html(new ApplicantSummaryView({model:this.model}).render().el);
		
		this.model.set("editing", false);
		
		if(this.model.id==""){
			//editing mode right away
			this.model.set("editing", true);
			setTimeout('$( ".applicant .edit" ).trigger( "click" )', 500);
		}
		
        //gridster the edit tab
		setTimeout("gridsterIt(0)", 100);
		
        return this;
    },
	
	editApplicantViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".applicant_" + field_name;
		}
		editField(element, master_class);
	},
	editApplicantViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".applicant_" + field_name;
		}
		//editField(element, master_class);
	},
	
	saveApplicantViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".applicant_" + field_name;
			
		}
		element_id = element_id.replace("SaveLink", "");
		//tell the model you are in editing mode, do not toggle
		
		this.model.set("editing", true);
		if (master_class != "") {
			//hide all the subs
			$(".span_class" + master_class).toggleClass("hidden");
			$(".input_class" + master_class).toggleClass("hidden");
			//$(field_name + "Save").toggleClass("hidden");
			
			$(".span_class" + master_class).addClass("editing");
			$(".input_class" + master_class).addClass("editing");
			$(field_name + "Save").addClass("editing");
		} else {
			//get the parent to get the class
			var theclass = element.parentElement.parentElement.parentElement.parentElement.parentElement.className;
			var arrClass = theclass.split(" ");
			theclass = "." + arrClass[0] + "." + arrClass[1];
			field_name = theclass + " #" + element_id;
			
			//restore the read look
			editField($(field_name + "Grid"));
			
			var element_value = $(field_name + "Input").val();
			$(field_name + "Span").html(escapeHtml(element_value));
		}			
		
		//this should not redraw, it will update if no id
		this.addApplicantView(event);
	
	},
	
	addApplicantView:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "applicant");
		return;
		/*
		var id = $('.applicant #table_id').val();
		if (id > 0) {
			this.updateApplicantView(event);
			return;
		}
		
		$('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/persons/add';

		var formValues = $('#applicant_form').serialize();
		var find = "Input";
		var regEx = new RegExp(find, 'g');
		formValues = formValues.replace(regEx, '');
		
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
					//add the model to the collection
					self.model.set(formValues);
					self.model.set("id",data.id);
					//applicants.add(self.model);
					
					//insert the id, so we can update right away
					$('.applicant #table_id').val(data.id);
					$('.applicant #uuid').val(data.uuid);

					self.saveSuccessful();
                }
            }
        });
		*/
    }/*,
	
	updateApplicantView:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		
		var self = this;
        $('.alert-error').hide(); // Hide any errors on a new submit

        var url = 'api/persons/update';
		
		var formValues = $('#applicant_form').serialize();
		var find = "Input";
		var regEx = new RegExp(find, 'g');
		formValues = formValues.replace(regEx, '');
		
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
					//update the model, which will update the collection automatically
					//self.model.set(formValues);
					var arrValues = formValues.split("&");
					for(var i =0, len = arrValues.length; i < len; i++) {
						var thevalue = arrValues[i];
						var arrValuePair = thevalue.split("=");
						self.model.set(arrValuePair[0], arrValuePair[1]);
					}
					
					self.saveSuccessful();
                }
            }
        });
    },
	
	saveSuccessful: function() {
		$( ".applicant .edit" ).trigger( "click" );
		$(".applicant .alert-success").addClass("alert-success1");
		$(".applicant .alert-success").fadeIn(function() { 
			setTimeout(function() {
					$(".applicant .alert-success").fadeOut();
				},1500);
		});
	},
	
	saveFailed:  function(text) {
		$('.applicant .alert-error').text(text)
		$(".applicant .alert-error").fadeIn(function() { 
			setTimeout(function() {
					$(".applicant .alert-error").fadeOut();
				},1500);
		});
		
	}*/,
	deleteApplicantView:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		var self = this;
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/kases/delete';
        console.log('Deletin ... ' + $('#table_id').val());
		var formValues = {
            id: $('#table_id').val()
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
	
	toggleApplicantEdit: function(event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
			
		//get all the editing fields, and toggle them back
		$(".applicant_view .editing").toggleClass("hidden");
		$(".applicant_view .span_class").removeClass("editing");
		$(".applicant_view .input_class").removeClass("editing");
		
		$(".applicant_view .span_class").toggleClass("hidden");
		$(".applicant_view .input_class").toggleClass("hidden");
		$(".applicant_view .input_holder").toggleClass("hidden");
		$(".button_row.applicant").toggleClass("hidden");
		$(".edit_row.applicant").toggleClass("hidden");
	},
	
	showCalendar:function(event) {
		event.preventDefault(); // Don't let this button submit the form
		//clean up button
		$("button.calendar").fadeOut(function() {
			$("button.information").fadeIn();
		});
		window.location.hash = "#kases/events/" + $('#table_id').val()
	},
	
	resetApplicantForm: function(event) {
		event.preventDefault();
		this.toggleApplicantEdit(event);
		//this.render();
	},
	
	valueApplicantViewChanged: function(event) {
		event.preventDefault();
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
		$("#" + source + "Span").html(escapeHtml(newval));
	}
});
window.ApplicantSummaryView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
		_.bindAll(this, "toggleApplicantEdit", "resetForm", "saveSuccessful");
    },
	
	 events:{
        "click .delete":		"deleteApplicant",
		"click .save":			"addApplicant",
		"click .save_field":	"saveApplicantField",
		"click .edit": 			"toggleApplicantEdit",
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
		
        //$('#details', this.el).html(new ApplicantSummaryView({model:this.model}).render().el);
		
		this.model.set("editing", false);
		
		if(this.model.id==""){
			//editing mode right away
			this.model.set("editing", true);
			setTimeout('$( ".edit" ).trigger( "click" )', 500);
		}		
        
		setTimeout("gridsterIt(0)", 100);
        return this;
    },
	
	saveApplicantField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		
		//restore the read look
		editField(element);
		
		var element_value = $("#" + element + "Input").val();
		$("#" + element + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		this.addApplicant(event);
	},
	
	addApplicant:function (event) {
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
		
		var id = $('#table_id').val();
		if (id!="") {
			this.updateApplicant(event);
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
	
	updateApplicant:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		
		var self = this;
        $('.alert-error').hide(); // Hide any errors on a new submit

        var url = 'api/kases/update';
        console.log('Updatin ... ');
		 var formValues = {
			id: $('#table_id').val(),
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
	
	deleteApplicant:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		var self = this;
        $('.alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/kases/delete';
        console.log('Deletin ... ' + $('#table_id').val());
		var formValues = {
            id: $('#table_id').val()
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
	
	toggleApplicantEdit: function(e) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
			
		//get all the editing fields, and toggle them back
		$(".applicant.editing").toggleClass("hidden");
		$(".applicant.span_class").removeClass("editing");
		$(".applicant.input_class").removeClass("editing");
		
		$(".applicant.span_class").toggleClass("hidden");
		$(".applicant.input_class").toggleClass("hidden");
		$(".applicant.input_holder").toggleClass("hidden");
		$(".applicant.button_row").toggleClass("hidden");
		$(".applicant.edit").toggleClass("hidden");
	},
	
	showCalendar:function(event) {
		window.location.hash = "#kase/events/" + $('#table_id').val()
	},
	
	resetForm: function(e) {
		this.toggleApplicantEdit(e);
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
		$("#" + source + "Span").html(escapeHtml(newval));
	}
});