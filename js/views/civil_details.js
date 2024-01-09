var civil_timeout_id;
window.civil_view = Backbone.View.extend({
	events:{
		
		"dblclick .civil .gridster_border": 			"editCivilField",
		"click .civil .save":							"addCivil",
		"click .civil .save_field":						"saveCivilsField",
		"click .civil .edit": 							"scheduleCivilsEdit",
		"click .civil .reset": 							"scheduleCivilsReset",
		"blur .civil #civil_dateInput": 				"splitDate",
		"click input[type=checkbox]":					"checkboxAdjuster",
		"click #civil_done":							"doTimeouts"
    },
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "civil_case_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	checkboxAdjuster: function (event) {
		var element = event.currentTarget;
		//alert(element_clean);
		var element_id = element.id;
		var checkbox = element;
		var checkbox_value = $("#" + element_id).val();
		if (checkbox_value != "Y") {
			$("#" + element_id).val("Y");
		} else {
			$("#" + element_id).val("N");
		}
		
	},
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		//gridsterById("gridster_civil");
		gridsterIt(11);
		
		this.model.set("editing", false);
		
		this.toggleFinancialEdit();
		
		setTimeout(function() {
			if (self.model.id<0) {
				$( ".civil .edit" ).trigger( "click" );
			}
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.personal_injury_info);
			//console.log(self.model.personal_injury_info);
			var model_civil_info = self.model.get("civil_info");
			
			var model_civil_defendant = self.model.get("civil_defendant");
			
			//alert(model_personal_injury_info);
			var civil_info = JSON.parse(model_civil_info);
			_.each( civil_info, function(the_info) {
				$("#" + the_info.name).val(the_info.value);
				if (the_info.value == "Y") {
					$("#" + the_info.name).attr("checked", "checked");
				}
				the_info.name = the_info.name.replace("Input", "Span");
				$("#" + the_info.name).html(the_info.value);
			});
			var civil_defendant = JSON.parse(model_civil_defendant);
			_.each( civil_defendant, function(the_defendant) {
				$("#" + the_defendant.name).val(the_defendant.value);
				if (the_defendant.value == "Y") {
					$("#" + the_defendant.name).attr("checked", "checked");
				}
				the_defendant.name = the_defendant.name.replace("Input", "Span");
				$("#" + the_defendant.name).html(the_defendant.value);
			});
		}
		if (customer_id == 1033) { 
		
			var case_id = this.model.get("case_id");
			var kase = kases.findWhere({case_id: case_id});
			console.log(kase.toJSON());
			var case_status = kase.toJSON().case_status;
			var case_substatus = kase.toJSON().case_substatus;
			var attorney = kase.toJSON().attorney;
			var worker = kase.toJSON().worker;
			var rating = kase.toJSON().rating;
			//var kase = kases.findWhere({case_id: this.model.get("case_id")});
			this.model.set("case_status", case_status);
			this.model.set("case_substatus", case_substatus);
			this.model.set("attorney", attorney);
			this.model.set("worker", worker);
			this.model.set("rating", rating);
			
			setTimeout(function() {
				$("#case_number_fill_in").html(kase.toJSON().case_number);
				$("#adj_number_fill_in").html(kase.toJSON().adj_number);
				if (kase.toJSON().adj_number == "") { 
					$("#adj_slot").hide();
				}
				$("#case_status_fill_in").html(kase.toJSON().case_status);
				$("#case_substatus_fill_in").html(kase.toJSON().case_substatus);
				$("#attorney_fill_in").html(kase.toJSON().attorney);
				$("#rating_fill_in").html(kase.toJSON().rating);
				$("#worker_fill_in").html(kase.toJSON().worker);
				$("#case_date_fill_in").html(kase.toJSON().case_date);
				$("#claims_fill_in").html(kase.toJSON().claims);
				if (kase.toJSON().claims == "") { 
					//$("#claims_slot").hide();
				}
				$("#case_type_fill_in").html(kase.toJSON().case_type);
				$("#case_type").val(kase.toJSON().case_type);
				$("#language_fill_in").html(kase.toJSON().language);
				if (kase.toJSON().language == "") { 
					$("#language_slot").hide();
				}
			}, 10);
			
			var parties = new Parties([], { case_id: this.model.get("case_id"), case_uuid: this.model.get("uuid"), panel_title: ""});
			parties.fetch({
				success: function(parties) {
					var claim_number = "";
					var carrier_insurance_type_option = "";
					//now we have to get the adhocs for the carrier
					var carrier_partie = parties.findWhere({"type": "carrier"});
					if (typeof carrier_partie == "undefined") {
						carrier_partie = new Corporation({ case_id: this.model.get("case_id"), type:"carrier" });
						carrier_partie.set("corporation_id", -1);
						carrier_partie.set("partie_type", "Carrier");
						carrier_partie.set("color", "_card_missing");
					}
					carrier_partie.adhocs = new AdhocCollection([], {case_id: case_id, corporation_id: carrier_partie.attributes.corporation_id});
					carrier_partie.adhocs.fetch({
						success:function (adhocs) {
							var adhoc_claim_number = adhocs.findWhere({"adhoc": "claim_number"});
							
							if (typeof adhoc_claim_number != "undefined") {
								claim_number = adhoc_claim_number.get("adhoc_value");
							}
							
							var adhoc_carrier_insurance_type_option = adhocs.findWhere({"adhoc": "insurance_type_option"});
							
							if (typeof adhoc_carrier_insurance_type_option != "undefined") {
								carrier_insurance_type_option = adhoc_carrier_insurance_type_option.get("adhoc_value");
							}
							var arrClaimNumber = [];
							var arrCarrierInsuranceTypeOption = [];
							if (carrier_partie.attributes.claim_number!="" && carrier_partie.attributes.claim_number!=null) {
								//arrClaimNumber.push(partie.claim_number);
								var claim_number = carrier_partie.attributes.claim_number;
								$("#claim_number_fill_in").html(claim_number);
								kase.set("claim_number", claim_number);
							}
						}
					});
				}
			});
		}
		setTimeout(function() {
			//$("#civil_dateInput").datetimepicker();
			//initializeGoogleAutocomplete('civil');
		}, 1000);
		
	},
	editdialogField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".personal_injury_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddPersonalInjury:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.addPersonalInjury(event);
		}, 200);
	},
	addPersonalInjury:function (event) {
		event.preventDefault();
		var self = this;
		self.saveEverything(event);
		this.model.set("editing", false);
		return;
		/*
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		//turn off editing to toggle
		this.model.set("editing", false);
		addForm(event, "personal_injury");
		//turn off editing altogether
		this.model.set("editing", false);
		return;
		*/
    },
	
	savePersonalInjuriesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		console.log("save_function_next");
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		console.log("save_function_after");
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		console.log("save_function_model_start");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		addForm(event);
	},
	schedulePersonalInjuriesEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.toggleFinancialEdit(event);
		}, 200);
	},
	toggleFinancialEdit: function (event) {
		//event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//going forward we are in editing mode until reset or save
		this.model.set("editing", true);
		toggleFormEdit("civil");
	},
	schedulePersonalInjuriesReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.resetPersonalInjuriesForm(event);
		}, 200);
	},
	
	resetPersonalInjuriesForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.togglePersonalInjuriesEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/civil/add";
		
		var inputArr = $("#civil_panel .input_class").serializeArray();
		var inputDefendantArr = $("#defendant_civil_panel .input_class").serializeArray();
		
		//var arrForms = inputArr + inputDefendantArr;
		
		formValues = "case_id=" + current_case_id;
		var civil_id = "";
		if ($("#table_id").val() != "") {
			civil_id = $("#table_id").val();
		} else {
			civil_id = -1;
		}
		formValues += "&table_id=" + civil_id;
		formValues += "&civil_info=" + JSON.stringify(inputArr);
		formValues += "&civil_defendant=" + JSON.stringify(inputDefendantArr);
		formValues += "&customer_id=" + customer_id;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//indicate success
					console.log("success");
					$("#panel_title").css("color", "green");
					toggleFormEdit("civil");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});