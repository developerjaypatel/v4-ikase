var financial_timeout_id;
window.financial_view = Backbone.View.extend({
	events:{
		
		"dblclick .financial .gridster_border": 			"editFinancialsField",
		"click .financial .save":							"addFinancial",
		"click .financial .save_field":						"saveFinancialsField",
		"click .financial .edit": 							"scheduleFinancialsEdit",
		"click .financial .reset": 							"scheduleFinancialsReset",
		"blur .financial #financial_dateInput": 			"splitDate",
		"click input[type=checkbox]":						"checkboxAdjuster",
		"change #statute_limitationInput":					"showStatute",
		"click #financial_done":							"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "financial_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		setTimeout(function() {
			self.model.set("hide_upload", true);
			showKaseAbstract(self.model);
		}, 750);
		
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
		//gridsterById("gridster_financial");
		gridsterIt(11);
		
		this.model.set("editing", false);
		
		//this.toggleFinancialEdit();
		
		setTimeout(function() {
			if (self.model.id<0) {
				toggleFormEdit("financial");
			}
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.personal_injury_info);
			//console.log(self.model.personal_injury_info);
			var model_financial_info = self.model.get("financial_info");
			
			var model_financial_defendant = self.model.get("financial_defendant");
			
			//alert(model_personal_injury_info);
			var arrInfo = JSON.parse(model_financial_info);
			financial_info = arrInfo.plaintiff;
			_.each( financial_info, function(the_info) {
				if (the_info.value == "Y" || the_info.value == "N") {
					$("#" + the_info.name).attr("checked", "checked");
				} else {
					$("#" + the_info.name).val(the_info.value);
				}
				the_info.name = the_info.name.replace("Input", "Span");
				
				$("#" + the_info.name).html(the_info.value);
			});
			//var financial_defendant = JSON.parse(model_financial_defendant);
			financial_defendant = arrInfo.defendant;
			_.each( financial_defendant, function(the_defendant) {
				if (the_defendant.value == "Y" || the_defendant.value == "N") {
					$("#" + the_defendant.name).attr("checked", "checked");
				} else {
					$("#" + the_defendant.name).val(the_defendant.value);
				}
				the_defendant.name = the_defendant.name.replace("Input", "Span");
				
				$("#" + the_defendant.name).html(the_defendant.value);
			});
			financial_escrow = arrInfo.escrow;
			_.each( financial_escrow, function(the_escrow) {
				if ($("#" + the_escrow.name).length > 0) {
					$("#" + the_escrow.name).val(the_escrow.value);
					
					//is it a select
					if (the_escrow.name.indexOf("_select") > -1) {
						the_escrow.value = $("#" + the_escrow.name + " option:selected").text();
					} 
					//special case
					if (the_escrow.name=="statute_dateInput") {
						var statute_limitation = $("#statute_limitationInput").val();
						if (statute_limitation == "Expire on") {
							$("#statute_dateGrid").show();
						} else {
							$("#statute_dateGrid").hide();
						}
					}
					the_escrow.name = the_escrow.name.replace("Input", "Span");
					$("#" + the_escrow.name).html(the_escrow.value);
				} else {
					//might be a radio
					var radio_id = the_escrow.value.toLowerCase().replace(" ", "");
					if ($("#" + the_escrow.name.replace("Input", "_" + radio_id)).length > 0) {
						$("#" + the_escrow.name.replace("Input", "_" + radio_id)).attr("checked", "checked");
						
						the_escrow.name = the_escrow.name.replace("Input", "Span");
						$("#" + the_escrow.name).html(the_escrow.value);
					}
				}
			});
		}
		/*
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
						carrier_partie = new Corporation({ case_id: self.model.get("case_id"), type:"carrier" });
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
		*/
		
		$("#statute_dateInput").datetimepicker({validateOnBlur:false, minDate: 0, timepicker:false, format:'m/d/Y'});
		
		$(".form_label_vert").css("color", "white");
		$(".form_label_vert").css("font-size", "1em")
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
	scheduleAddFinancial:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.addFinancial(event);
		}, 200);
	},
	addFinancial:function (event) {
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
	showStatute: function(event) {
		var element = event.currentTarget;
		
		if (element.value == "Expire on") {
			$("#statute_dateGrid").show();
		} else {
			$("#statute_dateGrid").hide();
		}
	},
	saveFinancialsField: function (event) {
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
	scheduleFinancialsEdit:function(event) {
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
		toggleFormEdit("financial");
	},
	scheduleFinancialsReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.resetFinancialsForm(event);
		}, 200);
	},
	
	resetFinancialsForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		toggleFormEdit("financial");
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/financial/add";
		
		var arrPlaintiffFinancial = $("#financial_panel .input_class").serializeArray();
		var arrDefendantFinancial = $("#defendant_financial_panel .input_class").serializeArray();
		var arrEscrowFinancial = $("#escrow_panel .input_class").serializeArray();
		
		//var arrForms = arrPlaintiffFinancial + arrDefendantFinancial;
		
		formValues = "case_id=" + current_case_id;
		var financial_id = "";
		if ($("#table_id").val() != "") {
			financial_id = $("#table_id").val();
		} else {
			financial_id = -1;
		}
		formValues += "&table_id=" + financial_id;
		formValues += "&financial_info=" + JSON.stringify(arrPlaintiffFinancial);
		formValues += "&financial_defendant=" + JSON.stringify(arrDefendantFinancial);
		formValues += "&financial_escrow=" + JSON.stringify(arrEscrowFinancial);
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
					$(".button_row").html("<span style='background:green; color:white; padding:2px'>Saved &#10003;</span>");
					//toggleFormEdit("financial");
					setTimeout(function(){ 
						
						//$("#panel_title").css("color", "white");
						window.Router.prototype.kaseFinancial(self.model.get("case_id"));
						
					}, 2000);
				}
			}
		});
    }
});
window.carrier_financial_view = Backbone.View.extend({
	events:{
		"click #financial_form .save":								"saveFinancial",
		"keyup .financial_subro":									"setSubro",
		"keyup .financial_subro_override":							"setSubroOverride",
		"click .partie_edit":										"editPartie",
		"change #financial_subro_select_Input":						"resetSubro",
		"change #financial_subro_override_select_Input":			"resetSubroOverride",
		"click #carrier_financial_done":							"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "carrier_financial_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var self = this;
		
		var prefix = "";
		if (self.model.get("holder").indexOf("#carrier_financial")==0) {
			prefix = self.model.get("holder") + " ";
		}
		this.model.set("prefix", prefix);
		
		if (typeof this.model.get("section_title") == "undefined") {
			this.model.set("section_title", "");
		}
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		if (prefix != "") {
			setTimeout(function() {
				self.doTimeouts();
			}, 777);
		}
		
        return this;
	},
	editPartie: function(event) {
		event.preventDefault();
		var prefix = this.model.get("prefix");
		
		var element_id = event.currentTarget.id;
		var arrID = element_id.split("_");
		var carrier_id = arrID[arrID.length - 1];
		document.location.href = "#parties/" + current_case_id + "/" + carrier_id + "/carrier";
		
		setTimeout(function() {
			$(prefix + "#partie_edit").trigger("click");
		}, 2500);
	},
	setSubro: function() {
		var prefix = this.model.get("prefix");
		
		$(prefix + "#financial_subro_select_Input").val("Yes");
		
		//calculate balance
		this.calcSubro();
	},
	resetSubro: function(event) {
		if (event.currentTarget.value=="No") {
			$("#financial_subroInput").val(0);
			$("#reducedInput").val(0);
			$("#financial_subro_overrideInput").val(0);
			$("#financial_subro_override_select_Input").val("No");
			
			this.calcSubro();
		}
	},
	setSubroOverride: function() {
		var prefix = this.model.get("prefix");
		
		$(prefix + "#financial_subro_override_select_Input").val("Yes");
		
		//cannot be more than subro
		var subro = $(prefix + "#financial_subroInput").val();
		var override = $(prefix + "#financial_subro_overrideInput").val();
		
		if (Number(subro) < Number(override)) {
			$(prefix + "#financial_subro_overrideInput").val($(prefix + "#financial_subroInput").val());
		}
		
		$("#override_indicator").fadeIn();
	},
	resetSubroOverride: function(event) {
		if (event.currentTarget.value=="No") {
			$("#financial_subro_overrideInput").val(0);
			$("#override_indicator").fadeOut();
		}
	},
	calcSubro: function() {
		var prefix = this.model.get("prefix");
		
		var subro = $(prefix + "#financial_subroInput").val();
		if (subro=="") {
			subro = 0;
			$(prefix + "#financial_subroInput").val(subro);
		}
		var reduced = $(prefix + "#reducedInput").val();
		if (reduced=="") {
			reduced = 0;
			$(prefix + "#reducedInput").val(reduced)
		}
		var balance = Number(subro) - Number(reduced);
		
		$(prefix + "#balanceInput").val(balance);
		$(prefix + "#balanceSpan").html("$ " + formatDollar(balance));
	},
	saveFinancial: function(event) {
		event.preventDefault();
		
		var self = this;
		var url = "api/financialcarrier/add";
		var prefix = this.model.get("prefix");
		
		var arrPlaintiffFinancial = $(prefix + "#financial_form .input_class").serializeArray();
		
		formValues = "case_id=" + current_case_id;
		var corporation_id = $("#corporation_id").val();
		var financial_id = "";
		if ($(prefix + "#financial_id").val() != "") {
			financial_id = $(prefix + "#financial_id").val();
		} else {
			financial_id = -1;
		}
		formValues += "&table_id=" + financial_id;
		formValues += "&financial_info=" + JSON.stringify(arrPlaintiffFinancial);
		formValues += "&financial_defendant=";
		formValues += "&financial_escrow=";
		formValues += "&corporation_id=" + corporation_id;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(prefix + "#financial_id").val(data.id);
					var holder = self.model.get("holder");
					var financial = new Financial({"case_id": current_case_id, "corporation_id": corporation_id});
					financial.fetch({
					success: function(financial) {
							self.model = financial;
							self.model.set("holder", holder);
							self.render();
						}
					});
				}
			}
		});
	},
	doTimeouts: function() {
		var self = this;
		gridsterIt(11);
		
		var prefix = self.model.get("prefix");
		
		$(prefix + "#financial_form .form_label_vert").css("color", "white");
		$(prefix + "#financial_form .form_label_vert").css("font-size", "1em");
		
		if (self.model.id > 0) {
			$(prefix + "#financial_id").val(self.model.id);
			//alert(self.model.personal_injury_info);
			//console.log(self.model.personal_injury_info);
			var model_financial_info = self.model.get("financial_info");
			//alert(model_personal_injury_info);
			var arrInfo = JSON.parse(model_financial_info);
			financial_info = arrInfo.plaintiff;
			_.each( financial_info, function(the_info) {
				if (the_info.value == "Y" || the_info.value == "N") {
					$(prefix + "#" + the_info.name).attr("checked", "checked");
				} else {
					$(prefix + "#" + the_info.name).val(the_info.value);
				}
				the_info.name = the_info.name.replace("Input", "Span");
				if (the_info.name=="financial_subro_overrideSpan" && Number(the_info.value) > 0) {
					$("#override_indicator").fadeIn();
				}
				if (the_info.name=="reducedSpan" || the_info.name=="financial_subroSpan" || the_info.name=="financial_subro_overrideSpan") {
					the_info.value = "$" + formatDollar(the_info.value);
				}
				
				$(prefix + "#" + the_info.name).html(the_info.value);
			});
		}
		
		if (this.model.get("section_title") != "") {
			$(prefix + "#section_title_" + this.model.get("corporation_id")).html(this.model.get("section_title"));
		}
		
		this.calcSubro();
	}
});