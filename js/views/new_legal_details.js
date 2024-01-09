var new_legal_timeout_id;
window.new_legal_view = Backbone.View.extend({
	events:{
		
		"dblclick .new_legal .gridster_border": 		"editNewLegalsField",
		"click .new_legal .save":						"addNewLegal",
		"click .new_legal .save_field":					"saveNewLegalsField",
		"click .new_legal .edit": 						"scheduleNewLegalsEdit",
		"click .new_legal .reset": 						"scheduleNewLegalsReset",
		"blur .new_legal #new_legal_dateInput": 		"splitDate",
		"click #new_legal_done":						"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "new_legal_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "new_legal_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		gridsterById("gridster_new_legal");
		
		this.model.set("editing", false);
		
		setTimeout(function() {
			if (self.model.id<0) {
				$( ".new_legal .edit" ).trigger( "click" );
			}
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.personal_injury_info);
			//console.log(self.model.personal_injury_info);
			var model_new_legal_info = self.model.get("new_legal_info");
			
			//alert(model_new_legal_info);
			var new_legal_info = JSON.parse(model_new_legal_info);
			_.each( new_legal_info, function(the_info) {
				$("#" + the_info.name).val(the_info.value);
				the_info.name = the_info.name.replace("Input", "Span");
				
				//special case for display
				if (the_info.name=="new_legal_dateSpan") {
					var legal_date = the_info.value;
					//legal_date = moment(legal_date).format("dddd MMMM D, YYYY h:mmA");
					//the_info.value = legal_date;
				}
				$("#" + the_info.name).html(the_info.value);
			});
		}
		setTimeout(function() {
			$("#new_legal_dateInput").datetimepicker();
			$("#overideInput").datetimepicker();
			//initializeGoogleAutocomplete('new_legal');
		}, 1000);
			
		var case_id = this.model.get("case_id");
		var kase = kases.findWhere({case_id: case_id});
		
		var kase_json = kase.toJSON();
		var case_status = kase_json.case_status;
		var file_number = kase_json.file_number;
		var case_substatus = kase_json.case_substatus;
		var attorney = kase_json.attorney;
		var worker = kase_json.worker;
		var rating = kase_json.rating;
		//var kase = kases.findWhere({case_id: this.model.get("case_id")});
		this.model.set("case_status", case_status);
		this.model.set("case_substatus", case_substatus);
		this.model.set("attorney", attorney);
		this.model.set("worker", worker);
		this.model.set("rating", rating);
		this.model.set("file_number", file_number);
		
		setTimeout(function() {
			$("#case_number_fill_in").html(kase_json.case_number);
			$("#adj_number_fill_in").html(kase_json.adj_number);
			if (kase_json.adj_number == "") { 
				$("#adj_slot").hide();
			}
			$("#case_status_fill_in").html(kase_json.case_status);
			$("#case_substatus_fill_in").html(kase_json.case_substatus);
			$("#attorney_fill_in").html(kase_json.attorney);
			$("#rating_fill_in").html(kase_json.rating);
			$("#worker_fill_in").html(kase_json.worker);
			$("#file_numberSpan").html(kase_json.file_number);
			$("#case_date_fill_in").html(kase_json.case_date);
			$("#filing_dateSpan").html(kase_json.case_date);
			$("#claims_fill_in").html(kase_json.claims);
			if (kase_json.claims == "") { 
				//$("#claims_slot").hide();
			}
			$("#case_type_fill_in").html(kase_json.case_type);
			$("#case_typeSpan").html(kase_json.case_type);
			$("#case_type").val(kase_json.case_type);
			$("#language_fill_in").html(kase_json.language);
			if (kase_json.language == "") { 
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
		var new_legal_id = this.model.get("id");
		setTimeout(function() {
			var case_date_law = $("#filing_dateSpan").html();
			var overide = $("#overideInput").val();
			setTimeout(function() {
				if (case_date_law != "") {
					$(".filingdate_column").html(case_date_law);
				} else {
					$(".filingdate_column").html("  /  /    ");
				}
				
				if (overide != "") {
					arrOveride = overide.split(" ");
					$(".overide_column").html(arrOveride[0]);
				} else {
					$(".overide_column").html("  /  /    ");
				}
			}, 400);
			var coas = new AccidentCauseCollection({case_id: case_id, new_legal_id: new_legal_id});
			coas.fetch({
				success: function(data) {
					var empty_model = new Backbone.Model;
					empty_model.set("file_number", file_number);
					empty_model.set("filing_date", case_date_law);
					empty_model.set("overide", overide);
					//empty_model.set("statute", statute);
					
					empty_model.set("case_id", case_id);
					empty_model.set("new_legal_id", new_legal_id);
					empty_model.set("holder", "coa_listing_holder");
					$('#coa_listing_holder').html(new coa_listing_view({collection: data, model: empty_model}).render().el);
					$("#coa_listing_holder").removeClass("glass_header_no_padding");
					//hideEditRow();
				}
			});	
		}, 500);
		
	},
	splitDate: function() {
		return;
		var self = this;
		var arrDate = [];
		var date_full = $("#new_legal_dateInput").val();
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
		$("#new_legal_dayInput").val(day_part);
		$("#new_legal_timeInput").val(time_part);
		
		$("#new_legal_daySpan").html(day_part);
		$("#new_legal_timeSpan").html(time_part);
	},
	editNewLegalsField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".new_legal_" + field_name;
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
			master_class = ".new_legal_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddNewLegal:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(new_legal_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.addNewLegal(event);
		}, 200);
	},
	addNewLegal:function (event) {
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
	
	saveNewLegalsField: function (event) {
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
	scheduleNewLegalsEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(new_legal_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.toggleNewLegalsEdit(event);
		}, 200);
	},
	toggleNewLegalsEdit: function (event) {
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
		toggleFormEdit("new_legal");
	},
	scheduleNewLegalsReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(new_legal_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.resetNewLegalsForm(event);
		}, 200);
	},
	
	resetNewLegalsForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleNewLegalsEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/newlegal/add";
		
		var inputArr = $("#new_legal_panel .input_class").serializeArray();
		
		//var inputSlipAndFallArr = $("#new_legal_form .input_class").serializeArray();
		
		var new_legal_date = $("#new_legal_dateInput").val();
		formValues = "case_id=" + current_case_id + "&new_legal_date=" + new_legal_date;
		var new_legal_description = $("#new_legal_factsInput").val();
		var new_legal_other_details = $("#new_legal_other_detailsInput").val();
		var new_legal_id = $("#table_id").val();
		formValues += "&new_legal_description=" + new_legal_description + "&new_legal_other_details=" + new_legal_other_details + "&table_id=" + new_legal_id;
		formValues += "&new_legal_info=" + JSON.stringify(inputArr);
		//formValues += "&new_legal_details=" + JSON.stringify(inputSlipAndFallArr);
		
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
					toggleFormEdit("new_legal");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});