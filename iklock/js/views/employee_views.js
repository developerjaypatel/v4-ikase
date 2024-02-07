window.employee_edit = Backbone.View.extend({
	tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		//don't work when i pass the html as whole, i think because there is no template..
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "employee_edit";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		$(this.el).html(this.model.get("html"));
		
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);

		return this;
    },
	doTimeouts: function() {
		var self = this;
		var user_id = this.model.get("user_id");
		$("#edit_personal").on("click", function(event) {
			self.editPersonal(event);
		});
		$("#save_personal").on("click", function(event) {
			self.savePersonal(event);
		});
		
		$("#edit_contact").on("click", function(event) {
			self.editContact(event);
		});
		$("#save_contact").on("click", function(event) {
			self.saveContact(event);
		});
		
		$("#edit_contractor").on("click", function(event) {
			self.editContractor(event);
		});
		$("#save_contractor").on("click", function(event) {
			self.saveContractor(event);
		});
		
		$("#edit_employment").on("click", function(event) {
			self.editEmployment(event);
		});
		
		$(".department").on("click", function(event) {
			self.engageEmployment(event);
		});
		$(".department").on("change", function(event) {
			self.engageEmployment(event);
		});
		$(".shift").on("click", function(event) {
			self.engageEmployment(event);
		});
		$(".shift").on("change", function(event) {
			self.engageEmployment(event);
		});
		$(".contractor").on("click", function(event) {
			self.engageContractor(event);
		});
		$(".contractor").on("change", function(event) {
			self.engageContractor(event);
		});
		$(".user_type").on("click", function(event) {
			self.engageEmployment(event);
			
			if ($("#type_contractor").prop("checked")) {
				$("#contractor_info_holder").fadeIn();
			} else {
				$("#contractor_info_holder").hide();
			}
		});
		$(".user_type").on("change", function(event) {
			self.engageEmployment(event);
			
			if ($("#type_contractor").prop("checked")) {
				$("#contractor_info_holder").fadeIn();
			} else {
				$("#contractor_info_holder").hide();
			}
		});
		$("#inine_filedField").on("click", function(event) {
			self.engageEmployment(event);
		});
		$("#inine_filedField").on("change", function(event) {
			self.engageEmployment(event);
		});
		$("#save_employment").on("click", function(event) {
			self.saveEmployment(event);
		});
		
		//json stuff starts here
		$("#edit_tax_federal").on("click", function(event) {
			self.editTaxFederal(event);
		});
		$("#save_tax_federal").on("click", function(event) {
			self.saveTaxFederal(event);
		});
		$("#edit_tax_state").on("click", function(event) {
			self.editTaxState(event);
		});
		$("#save_tax_state").on("click", function(event) {
			self.saveTaxState(event);
		});
		
		//notes
		$("#new_notes").on("click", function(event) {
			$("#new_notes_row").fadeIn();
		});
		
		$(".manage_reimbursments").on("click", function(event) {
			self.manageReimbursments(event);
		});
		$(".clear_link").on("click", function(event) {
			self.clearField(event);
		});
		
		//checks
		$(".create_check").on("click", function(event) {
			self.createCheck(event);
		});
		
		$('.employment#hired_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				//jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				//  .addClass('xdsoft_disabled');
			},
			//weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false, 
			maxDate: 0, 
			timepicker: false,
			format: 'm/d/Y',
			//value: event_dateandtime,
			//allowTimes:workingWeekTimes,
			//step:30,
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
				  //self.setupCalculationOptions();
			  }
		});
		
		if (user_id=="new") {
			$("#edit_personal").trigger("click");
			$("#user_nameField").focus();
			return;
		}
		//get the notes and refresh
		var formData = "user_id=" + user_id;
		//fetch the html
		var url = "api/employee_notes_listing.php";
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
					mod.set("holder", "employee_notes_table_holder");
					mod.set("html", data);
					mod.set("user_id", user_id);
					$('#employee_notes_table_holder').html(new employee_notes_listing({model: mod}).render().el);				
				}
			}
		});
	},
	editPersonal: function(event) {
		event.preventDefault();
		
		$(".personal.edit_span").addClass("hide_me");
		$(".personal.edit_field").removeClass("hide_me");
		$("#personal_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_personal").addClass("hide_me");
		$("#save_personal").removeClass("hide_me");
		
		$("#header_personal").css("background", "lightsalmon");
	},
	savePersonal: function(event) {
		var self = this;
		event.preventDefault();
		$("#save_personal").addClass("hide_me");
		
		var url = "api/employee/save";
		var formData = $("#personal_info_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".personal.edit_field").addClass("hide_me");
					$("#personal_info_form .clear_holder").addClass("hide_me");
					$("#header_personal").css("background", "green");
					
					var edit_fields = $(".personal.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						if(span_id!="") {
							$(".personal#" + span_id).html(element.value);
						}
					}
					$("#edit_personal").removeClass("hide_me");	
					$(".personal.edit_span").removeClass("hide_me");
					
					if (self.model.get("user_id")=="new") {
						var new_user_id = data.id;
						setTimeout(function() {
							window.Router.prototype.editEmployee(new_user_id);
						}, 1500);
					} else {
						setTimeout(function() {
							$("#header_personal").css("background", "darkslategray");
						}, 2500);
					}
				}
			}
		});
	},
	editContact: function(event) {
		event.preventDefault();
		
		$(".contact.edit_span").addClass("hide_me");
		$(".contact.edit_field").removeClass("hide_me");
		$("#contact_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_contact").addClass("hide_me");
		$("#save_contact").removeClass("hide_me");
		
		$("#header_contact").css("background", "lightsalmon");
	},
	saveContact: function(event) {
		event.preventDefault();
		$("#save_contact").addClass("hide_me");
		
		var url = "api/employee/savecontact";
		var formData = $("#contact_info_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".contact.edit_field").addClass("hide_me");
					$("#contact_info_form .clear_holder").addClass("hide_me");
					$("#header_contact").css("background", "green");
					
					var edit_fields = $(".contact.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						$(".contact#" + span_id).html(element.value);
					}
					$("#edit_contact").removeClass("hide_me");	
					$(".contact.edit_span").removeClass("hide_me");
					setTimeout(function() {
						$("#header_contact").css("background", "darkslategray");
					}, 2500);
				}
			}
		});
	},
	engageContractor: function(event) {
		var element = event.currentTarget;
		var blnChecked = element.checked;
		setTimeout(function() {
			element.checked = blnChecked;
		}, 100);
		this.editContractor(event);
	},
	editContractor: function(event) {
		event.preventDefault();
		
		$(".contractor.edit_span").addClass("hide_me");
		$(".contractor.edit_field").removeClass("hide_me");
		$("#contractor_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_contractor").addClass("hide_me");
		$("#save_contractor").removeClass("hide_me");
		
		$("#header_contractor").css("background", "lightsalmon");
	},
	saveContractor: function(event) {
		event.preventDefault();
		$("#save_contractor").addClass("hide_me");
		
		var url = "api/employee/savecontractor";
		var formData = $("#contractor_info_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".contractor.edit_field").addClass("hide_me");
					$("#contractor_info_form .clear_holder").addClass("hide_me");
					$("#header_contractor").css("background", "green");
					
					var edit_fields = $(".contractor.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						$(".contractor#" + span_id).html(element.value);
					}
					$("#edit_contractor").removeClass("hide_me");	
					$(".contractor.edit_span").removeClass("hide_me");
					setTimeout(function() {
						$("#header_contractor").css("background", "darkslategray");
					}, 2500);
				}
			}
		});
	},
	engageEmployment: function(event) {
		var element = event.currentTarget;
		var blnChecked = element.checked;
		setTimeout(function() {
			element.checked = blnChecked;
		}, 100);
		this.editEmployment(event);
	},
	editEmployment: function(event) {
		event.preventDefault();
		
		$(".employment.edit_span").addClass("hide_me");
		$(".employment.edit_field").removeClass("hide_me");
		$("#employment_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_employment").addClass("hide_me");
		$("#save_employment").removeClass("hide_me");
		
		$("#header_employment").css("background", "lightsalmon");
	},
	saveEmployment: function(event) {
		event.preventDefault();
		$("#save_employment").addClass("hide_me");
		
		var url = "api/employee/saveemployment";
		var formData = $("#employment_info_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".employment.edit_field").addClass("hide_me");
					$("#employment_info_form .clear_holder").addClass("hide_me");
					$("#header_employment").css("background", "green");
					
					var edit_fields = $(".employment.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						var field = element_id.replace("Field", "");
						var val = element.value;

						switch(field) {
							case "pay_period":
								switch(val) {
									case "H":
										subst = "hourly";
										break;
									case "D":
										subst = "daily";
										break;
									case "M":
										subst = "monthly";
										break;
								}
								break;
							case "pay_schedule":
								switch(val) {
									case "W":
										subst = "weekly";
										break;
									case "D":
										subst = "every day";
										break;
									case "BW":
										subst = "bi-weekly";
										break;
									case "M":
										subst = "monthly";
										break;
									case "TM":
										subst = "twice-a-month";
										break;
								}
								break;
							case "pay_method":
								switch(val) {
									case "DD":
										subst = "via direct deposit";
										break;
									case "CK":
										subst = "by check";
										break;
									case "CS":
										subst = "cash";
										break;
								}
								break;
							default:
								subst = element.value;
						}
						$("#" + span_id).html(subst);
					}
					$("#edit_employment").removeClass("hide_me");	
					$(".employment.edit_span").removeClass("hide_me");
					setTimeout(function() {
						$("#header_employment").css("background", "darkslategray");
					}, 2500);
				}
			}
		});
	},
	editTaxFederal: function(event) {
		event.preventDefault();
		
		$(".tax_federal.edit_span").addClass("hide_me");
		$(".tax_federal.edit_field").removeClass("hide_me");
		$("#tax_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_tax_federal").addClass("hide_me");
		$("#save_tax_federal").removeClass("hide_me");
		
		$("#header_tax_federal").css("background", "lightsalmon");
	},
	saveTaxFederal: function(event) {
		event.preventDefault();
		event.preventDefault();
		$("#save_tax_federal").addClass("hide_me");
		
		var url = "api/employee/savetaxfederal";
		var formData = $("#tax_federal_info_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".tax_federal.edit_field").addClass("hide_me");
					$("#tax_info_form .clear_holder").addClass("hide_me");
					$("#header_tax_federal").css("background", "green");
					var edit_fields = $(".tax_federal.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						var val = element.value;
						var subst = val;
						
						if (span_id=="filing_status_federalSpan") {
							switch(val) {
								case "M":
									subst = "Married";
									break;
								case "D":
									subst = "No Withholding";
									break;
								case "MW":
									subst = "Married / Withhold Higher Rate";
									break;
								case "S":
									subst = "Single";
									break;
							}
						}
						$("#" + span_id).html(subst);
					}
					$("#edit_tax_federal").removeClass("hide_me");	
					$(".tax_federal.edit_span").removeClass("hide_me");
					setTimeout(function() {
						$("#header_tax_federal").css("background", "darkslategray");
					}, 2500);
				}
			}
		});
	},
	editTaxState: function(event) {
		event.preventDefault();
		
		$(".tax_state.edit_span").addClass("hide_me");
		$(".tax_state.edit_field").removeClass("hide_me");
		$("#tax_state_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_tax_state").addClass("hide_me");
		$("#save_tax_state").removeClass("hide_me");
		
		$("#header_tax_state").css("background", "lightsalmon");
	},
	saveTaxState: function(event) {
		event.preventDefault();
		event.preventDefault();
		$("#save_tax_state").addClass("hide_me");
		
		var url = "api/employee/savetaxstate";
		var formData = $("#tax_state_info_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".tax_state.edit_field").addClass("hide_me");
					$("#tax_info_form .clear_holder").addClass("hide_me");
					$("#header_tax_state").css("background", "green");
					var edit_fields = $(".tax_state.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						var val = element.value;
						var subst = val;
						
						if (span_id=="filing_status_stateSpan") {
							switch(val) {
								case "M":
									subst = "Married (1 income)";
									break;
								case "D":
									subst = "No Withholding";
									break;
								case "MS":
									subst = "Married/Single (2+ incomes)";
									break;
								case "H":
									subst = "Head of Household";
									break;
							}
						}
						$("#" + span_id).html(subst);
					}
					$("#edit_tax_state").removeClass("hide_me");	
					$(".tax_state.edit_span").removeClass("hide_me");
					setTimeout(function() {
						$("#header_tax_state").css("background", "darkslategray");
					}, 2500);
				}
			}
		});
	},
	createCheck: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[arrID.length - 1];
		
		document.location.href = "#paycheck/create/" + id;
	},
	manageReimbursments: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[arrID.length - 1];
		
		document.location.href = "#employees/reimbursments/" + id;
	},
	clearField: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var root = arrID[1];
		$("#" + root + "Field").val("");
	}
});

window.employee_list = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{

    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "employee_list";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		$(this.el).html(this.model.get("html"));
		
		return this;
    },
	doTimeouts: function() {
		var self = this;
		
		$(".new_employee").on("click", function(event) {
			self.newEmployee(event);
		});
		
		//checks
		$(".create_check").on("click", function(event) {
			self.createCheck(event);
		});
		
		$(".list_checks").on("click", function(event) {
			self.listChecks(event);
		});
		$(".manage_reimbursments").on("click", function(event) {
			self.manageReimbursments(event);
		});
	},
	newEmployee: function(event) {
		event.preventDefault();
		
		document.location.href = "#employees/new";
	},
	listChecks: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[arrID.length - 1];
		
		document.location.href = "#employees/checks/" + id;
	},
	createCheck: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[arrID.length - 1];
		
		document.location.href = "#paycheck/create/" + id;
	},
	manageReimbursments: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[arrID.length - 1];
		
		document.location.href = "#employees/reimbursments/" + id;
	}
});
window.employee_notes_listing = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{

    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "employee_notes_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		$(this.el).html(this.model.get("html"));
		
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);
		
		return this;
    },
	saveNote: function(event) {
		event.preventDefault();
		var user_id = this.model.get("user_id");
		var url = "api/employee/savenote";
		var formData = "notesField=" + encodeURIComponent($("#employee_notes_table #notesField").val()) + "&user_id=" + user_id;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#header_notes").css("background", "green");
					
					//get the notes and refresh
					var formData = "user_id=" + user_id;
					//fetch the html
					var url = "api/employee_notes_listing.php";
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
								mod.set("holder", "employee_notes_table_holder");
								mod.set("html", data);
								mod.set("user_id", user_id);
								$('#employee_notes_table_holder').html(new employee_notes_listing({model: mod}).render().el);				
							}
						}
					});
					
					setTimeout(function() {
						$("#header_employment").css("background", "darkslategray");
					}, 2500);
				}
			}
		});
	},
	doTimeouts: function() {
		var self = this;
		$("#save_new_notes").on("click", function(event) {
			self.saveNote(event);
		});
	}
});
