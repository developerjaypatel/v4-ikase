var coa_timeout_id;
window.coa_view = Backbone.View.extend({
	events:{
		
		"dblclick .coa .gridster_border": 			"editCOAsField",
		"blur .coa #coa_dateInput": 				"splitDate",
		"click #coa_done":							"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "coa_view";
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
		
        return this;
	},
	
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		//gridsterById("gridster_coa");
		
		//this.model.set("editing", false);
		
		setTimeout(function() {
			if (self.model.id<0) {
				//$( ".coa .edit" ).trigger( "click" );
			}
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.personal_injury_info);
			//console.log(self.model.personal_injury_info);
			var model_coa_info = self.model.get("coa_info");
			
			//alert(model_coa_info);
			var coa_info = JSON.parse(model_coa_info);
			_.each( coa_info, function(the_info) {
				var element = document.getElementById(the_info.name);
				if (element != null) {
					$("#" + the_info.name).val(the_info.value);
					the_info.name = the_info.name.replace("Input", "Span");
					$("#" + the_info.name).html(the_info.value);
				} else {
					//no element, probably a radio button
					var element_id = the_info.name.replace("Input", "_" + the_info.value.toLowerCase() + "Input");
					var radio_element = document.getElementById(element_id);
					if (radio_element != null) {
						radio_element.checked = true;
					}
				}
			});
			/*
			var model_coa_details = self.model.get("coa_details");
			if (model_coa_details.length > 0) {
				var coa_details = JSON.parse(model_coa_details);
				_.each( coa_details, function(the_details) {
					var coa_form = the_details.form;
					var arrForm = coa_form.split("_");
					
					//we got the class name, now get values for each input
					_.each( the_details.data, function(the_detail) {
						//console.log(the_detail);
						$("#" + the_detail.name).val(the_detail.value);
						the_detail.name = the_detail.name.replace("Input", "Span");
						$("#" + the_detail.name).html(the_detail.value);
					});
				});
			}*/

		}
		setTimeout(function() {
			//$("#coa_dateInput").datetimepicker();
			//initializeGoogleAutocomplete('coa');
		}, 1000);
		var case_id = this.model.get("case_id");
			var kase = kases.findWhere({case_id: case_id});
			
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
				$("#case_typeSpan").html(kase.toJSON().case_type);
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
					var case_id = self.model.get("case_id");
					var carrier_insurance_type_option = "";
					//now we have to get the adhocs for the carrier
					var carrier_partie = parties.findWhere({"type": "carrier"});
					if (typeof carrier_partie == "undefined") {
						carrier_partie = new Corporation({ case_id: case_id, type:"carrier" });
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
		
	},
	splitDate: function() {
		var self = this;
		var arrDate = [];
		var date_full = $("#coa_dateInput").val();
		arrDate = date_full.split(" ");
		var day_part = arrDate[0];
		var time_part = arrDate[1];
		day_part = new Date(day_part)
		day_part = day_part.getDay();
		if (day_part == "0") {
			day_part = "Sunday";
		}
		if (day_part == "1") {
			day_part = "Monday";
		}
		if (day_part == "2") {
			day_part = "Tuesday";
		}
		if (day_part == "3") {
			day_part = "Wednesday";
		}
		if (day_part == "4") {
			day_part = "Thursday";
		}
		if (day_part == "5") {
			day_part = "Friday";
		}
		if (day_part == "6") {
			day_part = "Saturday";
		}
		$("#coa_dayInput").val(day_part);
		$("#coa_timeInput").val(time_part);
		
		$("#coa_daySpan").html(day_part);
		$("#coa_timeSpan").html(time_part);
	},
	editCOAsField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".coa_" + field_name;
		}
		editField(element, master_class);
	},
	editdialogField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".coa_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddCOA:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(coa_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.addCOA(event);
		}, 200);
	},
	addCOA:function (event) {
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
	
	saveCOAsField: function (event) {
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
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		
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
	scheduleCOAsEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(coa_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.toggleCOAsEdit(event);
		}, 200);
	},
	toggleCOAsEdit: function (event) {
		event.preventDefault();
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
		toggleFormEdit("coa");
	},
	scheduleCOAsReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(coa_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.resetCOAsForm(event);
		}, 200);
	},
	
	resetCOAsForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleCOAsEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/COA/add";
		
		var inputArr = $("#coa_panel .input_class").serializeArray();
		
		var inputSlipAndFallArr = $("#coa_form .input_class").serializeArray();
		
		var coa_date = $("#coa_dateInput").val();
		formValues = "case_id=" + current_case_id + "&coa_date=" + coa_date;
		var coa_description = $("#coa_descriptionInput").val();
		var coa_other_details = $("#coa_other_detailsInput").val();
		var coa_id = $("#table_id").val();
		formValues += "&coa_description=" + coa_description + "&coa_other_details=" + coa_other_details + "&table_id=" + coa_id;
		formValues += "&coa_info=" + JSON.stringify(inputArr);
		formValues += "&coa_details=" + JSON.stringify(inputSlipAndFallArr);
		
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
					
					$("#panel_title").css("color", "green");
					toggleFormEdit("coa");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});

window.coa_listing_view = Backbone.View.extend({
    initialize:function () {

    },
	events: {
		"click .delete_icon":						"confirmdeleteCOA",
		"click .delete_yes":						"deleteCOA",
		"click .delete_no":							"canceldeleteCOA",
		"click .edit_coa":							"editCOA",
		"click .new_coa":							"newCOA",
		"click #label_search_users":				"Vivify",
		"click #coa_searchList":					"Vivify",
		"focus #coa_searchList":					"Vivify",
		"blur #coa_searchList":						"unVivify"
	},
    render:function () {
		var self = this;
		var coas = this.collection.toJSON();
		var coa_model = this.model.toJSON();

		_.each( coas, function(coa) {
			if (coa.coa_info!="") {
				var coa_details = JSON.parse(coa.coa_info);
				//hard coded
				coa.type = coa_details[0].value;
				coa.type = coa.type.replace("_", " ");
				coa.type = coa.type.toUpperCase();
				coa.disposition = coa_details[1].value;
				coa.disposition_explanation = coa_details[2].value;
				coa.resolution = coa_details[3].value;
				coa.resolution_explanation = coa_details[4].value;
			}
		});

		
		try {
			$(this.el).html(this.template({coas: coas, case_id: coa_model.case_id, id: coa_model.new_legal_id}));
		}
		catch(err) {
			var view = "coa_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		setTimeout(function() {
			tableSortIt("coa_listing");
		}, 700);
		
        return this;		
		
    },
	unVivify: function(event) {
		var textbox = $("#coa_searchList");
		var label = $("#label_search_coa");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#coa_searchList");
		var label = $("#label_search_coa");
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	confirmdeleteCOA: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element, 450);	
		$("#confirm_delete").css({display: "none", top: arrPosition[0] - 50, left: arrPosition[1] + 50, position:'absolute'});
		$("#confirm_delete").fadeIn();
	},
	canceldeleteCOA: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	newCOA:function (event) {
		var element = event.currentTarget;
		composeNewCOA(element.id)
    },
	editCOA:function (event) {
		var element = event.currentTarget;
		composeNewCOA(element.id)
    },
	deleteCOA: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "coa");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteCOA(event);
			$(".coa_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".coa_data_row_" + id).fadeOut();
			}, 2500);
		}
	}
});