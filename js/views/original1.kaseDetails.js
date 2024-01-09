window.KaseView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
    render: function () {
		//make sure we have an id, even if it's empty
		var self = this;
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		mymodel = this.model.toJSON();
		$(this.el).html(this.template(mymodel));
		
        //$('#details', this.el).html(new KaseSummaryView({model:this.model}).render().el);
		$('#kase_header', this.el).html(new kaseHeaderView({model:this.model}).render().el);
		
		//can i access the subview of the view now?
		var applicant_id = mymodel.applicant_id;
		if (applicant_id > 0) {
			var applicant = new Applicant({id: applicant_id});
			applicant.fetch({
				success: function (data) {
					data.set("case_id", self.model.get("id"));
					data.set("case_uuid", self.model.get("uuid"));
					data.set("host", appHost);
					applicants.add(data);
					$('#kase_content').html(new kaseApplicantView({model: data}).render().el);
				}
			});
		} else {
			//new applicant
			new_applicant.set("id", -1);
			new_applicant.set("case_id", this.model.get("id"));
			new_applicant.set("case_uuid", this.model.get("uuid"));			
			$('#kase_content').html(new kaseApplicantView({model: new_applicant}).render().el);
		
		}
		//turn off edit mode
		this.model.set("editing", false);
		
		if(this.model.id < 0){
			//editing mode right away
			setTimeout(function() { $(".kase .edit").trigger("click"); }, 500);
		}		
		
        setTimeout("gridsterIt(1)", 1);
		
		
        return this;
    }
});

window.KaseSummaryView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
		_.bindAll(this, "toggleEdit", "resetKaseForm", "saveSuccessful");
    },

    render:function () {
        $(this.el).html(this.template(this.model.toJSON()));
		
        return this;
    }
});

window.kaseHeaderView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
    },

    render:function () {
        $(this.el).html(this.template(this.model.toJSON()));
		//setup the datetimepicker
		//datepickIt('#case_dateInput');
		$('#case_dateInput').datetimepicker({
			timepicker:false, 
			format:'m/d/Y h:iA',
			mask:false,
			onChangeDateTime:function(dp,$input){
				//alert($input.val());
			}
		});	
        return this;
    },
	
	events:{
        "click .kase .delete":		"deleteKase",
		"click .kase .save":		"addKase",
		"click .kase .save_field":	"saveKaseField",
		"click .kase .edit": 		"toggleEdit",
		"click .kase .reset": 		"resetKaseForm",
		"click .kase .calendar": 	"showCalendar",
		"click .kase .information": "showInformation",
		"click .kase .upload": 		"uploadKaseDocument",
		"keyup .kase .input_class": "valueChanged",
		"dblclick .kase .glass": 	"editKaseField",
    },
	uploadKaseDocument: function(event) {
		event.preventDefault(); // Don't let this button submit the form
		window.location.hash = "#upload/" + $('#id').val() + "/kase";
	},
	editKaseField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".kase_" + field_name;
		}
		editField(element, master_class);
	},
	
	saveKaseField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(element_value);
		
		this.model.set(element, element_value);
		
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		this.addKase(event);
	},
	
	addKase:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		$blnValid = $("#kase_form").parsley('validate');
		
		//get out if invalid			
		if (!$blnValid) {
			$(".alert-text").html("Please fill in the required fields in the correct format.");
			$(".alert-warning").fadeIn(function() {
				setTimeout(function() {
					$(".alert-warning").fadeOut(); 
				}, 1500);
			});
			return;
		}
		
		$(".alert-warning").hide();
		
		var id = $('#id').val();
		if (Number(id) > 0) {
			this.updateKase(event);
			return;
		}
		
		$('.kase .alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/kases/add';
        console.log('Addin ... ');
        var formValues = {
			case_number: $(".gridster.kase #case_numberInput").val(),
			case_date: $(".gridster.kase #case_dateInput").val(),
			title: $(".gridster.kase #titleInput").val(),
			case_type: $(".gridster.kase #case_typeInput").val(),
			case_status: $(".gridster.kase #case_statusInput").val()
        };
		
		//return;
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
                    self.saveFailed(data.error.text);
                }
                else { // If not, go back to read mode
					//make sure the collection is updated
					self.model.set(formValues);
					self.model.set("id",data.id);
					if (typeof kases != "undefined") {
						kases.add(self.model);
					}
                    $( ".edit" ).trigger( "click" );
					self.saveSuccessful();
                }
            }
        });
    },
	
	updateKase:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		
		var self = this;
        $('.kase .alert-error').hide(); // Hide any errors on a new submit

        var url = 'api/kases/update';
        console.log('Updatin ... ');
		var formValues = $('#kase_form').serialize();
		var find = "Input";
		var regEx = new RegExp(find, 'g');
		formValues = formValues.replace(regEx, '');
		/*
		var formValues = {
			id: $('#id').val(),
			case_number: $(".gridster.kase #case_numberInput").val(),
			case_date: $(".gridster.kase #case_dateInput").val(),
			title: $(".gridster.kase #titleInput").val(),
			case_type: $(".gridster.kase #case_typeInput").val(),
			case_status: $(".gridster.kase #case_statusInput").val()
        };
		*/
		//make sure the collection is updated
		this.model.set(formValues);
		if (typeof kases != "undefined") {
			kases.get(this.model.id).set(formValues);
		}
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
                    $( ".kase.edit" ).trigger( "click" );
					self.saveSuccessful();
                }
            }
        });
		
    },
	
	saveSuccessful: function() {
		$(".kase .alert-success").fadeIn(function() { 
			setTimeout(function() {
					$(".kase .alert-success").fadeOut();
				},1500);
		});
	},
	
	saveFailed:  function(text) {
		$('.kase .alert-error').text(text)
		$(".kase .alert-error").fadeIn(function() { 
			setTimeout(function() {
					$(".kase .alert-error").fadeOut();
				},1500);
		});
		
	},
	
	deleteKase:function (event) {
        event.preventDefault(); // Don't let this button submit the form
		var self = this;
        $('.kase .alert-error').hide(); // Hide any errors on a new submit
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
	
	toggleEdit: function(e) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
			
		//get all the editing fields, and toggle them back
		$(".kase .editing").toggleClass("hidden");
		$(".kase .span_class").removeClass("editing");
		$(".kase .input_class").removeClass("editing");
		
		$(".kase .span_class").toggleClass("hidden");
		$(".kase .input_class").toggleClass("hidden");
		$(".kase .input_holder").toggleClass("hidden");
		
		$(".button_row.kase").toggleClass("hidden");
		$(".edit_row.kase").toggleClass("hidden");
	},
	
	showCalendar:function(event) {
		event.preventDefault(); // Don't let this button submit the form
		//clean up button
		$("button.calendar").fadeOut(function() {
			$("button.information").fadeIn();
		});
		window.location.hash = "#kases/events/" + $('#id').val()
	},
	
	showInformation:function(event) {
		event.preventDefault(); // Don't let this button submit the form
		$("button.information").fadeOut(function() {
			$("button.calendar").fadeIn();
		});
		window.location.hash = "#kases/" + $('#id').val()
	},
	
	resetKaseForm: function(e) {
		this.toggleEdit(e);
		$('#kase_header', this.el).html(new kaseHeaderView({model:this.model}).render().el);
		//need to gridster it
		setTimeout("gridsterIt(1)", 1);
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
		//update the model?
		this.model.set(source, newval);
	}
});