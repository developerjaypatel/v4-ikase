window.person_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	events:{
        "click .person .delete":						"deletePersonView",
		"click .person .save":							"confirmApply",
		//"click .person .bill_time":						"billTimeSpent",
		"click #billing_time_dropdownInput":			"billTimeFill",
		//"change:[value] #billing_time_dropdownInput":	"billTimeFilled",
		"click .person .save_field":					"savePersonViewField",
		"click .person .edit":		 					"toggleEditViewPerson",
		"click .person .reset":		 					"resetPersonForm",
		"click .kase .calendar":		 				"showCalendar",
		"click #list_prior_medical":					"gotoPriorMedical",
		"click #list_rx":								"gotoRx",
		"keyup .person .input_class":		 			"valuePersonViewChanged",
		"blur .person .input_class":		 			"autoSave",
		"dblclick .person .gridster_border":		 	"editPersonViewField",
		"dblclick #notesGrid":		 					"editPersonViewNotesField",
		"change #salutationInput":						"changeGender",
		"blur #dobInput":								"displayAge",
		"click .apply_yes":								"applyPerson",
		"click .apply_no":								"cancelApply",
		"keyup .person #full_nameInput":				"checkForToken",
		"keydown .person #full_nameInput":				"checkForToken",
		"keyup #token-input-full_nameInput":			"setFullNameCurrent",
		"keydown #token-input-full_nameInput":			"setFullNameCurrent",
		"blur #token-input-full_nameInput":				"clearCurrent",
		"click #manual_address":						"manualAddress",
		"click .bing_address":							"selectBingAddress",
		"click #lookup_address":						"lookupAddress",
		"keyup .street":								"updateStreet",
		"keyup .suite":									"updateSuite",
		"keyup .city":									"updateCity",
		"keyup .state":									"updateState",
		"keyup .postal_code":							"updateZip",
		"blur .postal_code":							"updateZip",
		"click #all_done":								"doTimeouts",
		"keyup .search_map":							"searchAddress",
		"focus .search_map":							"searchAddress",
		"blur .search_map":								"hideResults",
		"keyup #full_nameGrid":							"showIntakeFields"
    },
	render: function () {
		if (typeof this.template != "function") {
			if (typeof this.model.get("holder") == "undefined") {
				this.model.set("holder", "person_holder");
			}
			var view = "person_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			
			if (this.model.get("intake_screen")) {
				$("#intake_kase").trigger("click");
			}
			return "";
	   }
		var self = this;
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		if (typeof this.model.get("intake_screen") == "undefined") {
			this.model.set("intake_screen", false);		
		}
		if(this.model.get("bln_contact") == true) {
			this.model.set("case_id", "");
			this.model.set("case_uuid", "");
		}
		
		this.model.set("display_address", this.model.get("full_address"));
		//address
		if (this.model.get("street")!="") {
			var arrAddress = [];
			arrAddress.push(this.model.get("street"));
			if (this.model.get("suite")!="") {
				arrAddress.push(this.model.get("suite"));
			}
			var city_state_zip = this.model.get("city") + ", " + this.model.get("state") + " " + this.model.get("zip");
			arrAddress.push(city_state_zip);
			this.model.set("display_address", arrAddress.join("<br>"));
		}
		/*
		if (document.location.hash == "#intake") {
			this.model.set("case_id", current_case_id);
		}
		*/
		mymodel = this.model.toJSON();
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			return;
		}
		
		this.model.set("editing", false);
		
		return this;
    },
	showIntakeFields: function() {
		var self = this;
		
		if (!self.model.get("intake_screen")) {
			return;
		}
		$(".person .gridster_border").show();
	},
	searchAddress: function() {
		searchAddress('person');
	},
	hideResults: function() {
		hideResults();
	},
	gotoPriorMedical: function(event) {
		//get the top offset of the target anchor
        var target_offset = $("#priors").offset();
        var target_top = target_offset.top;
		target_top = target_top - 25;
        //goto that anchor by setting the body scroll top to anchor top
        $('html, body').animate({scrollTop:target_top}, 1100, 'easeInSine');
	},
	gotoRx: function(event) {
		//get the top offset of the target anchor
        var target_offset = $("#rxs").offset();
        var target_top = target_offset.top;
		target_top = target_top - 25;
        //goto that anchor by setting the body scroll top to anchor top
        $('html, body').animate({scrollTop:target_top}, 1100, 'easeInSine');
	},
	manualAddress: function(event) {
		var partie = "person";
		$(".pac-container").hide();
		
		var street = $("#street_" + partie);
		street.val($("#full_addressInput").val());
		$("#full_addressInput").hide();
		street.focus();
		//hide city and state, will be filled out by zip
		$("#city_" + partie).css("visibility", "hidden");
		$("#administrative_area_level_1_" + partie).css("visibility", "hidden");
		
		$("#manual_address").fadeOut(function() {
			$("#lookup_address").fadeIn();
		});
	},
	lookupAddress: function(event) {
		var partie = "person";
		
		$("#street_" + partie).val("");
		
		$("#full_addressSpan").hide();
		var full_address = $("#full_addressInput");
		full_address.show();
		full_address.focus();
		
		//show city and state
		$("#city_" + partie).css("visibility", "visible");
		$("#city_" + partie).val("");
		$("#administrative_area_level_1_" + partie).css("visibility", "visible");
		$("#administrative_area_level_1_" + partie).val("");
		
		$("#lookup_address").fadeOut(function() {
			$("#manual_address").fadeIn();
		});
	},
	updateFullAddress: function() {
		var partie = "person";
		//fill-in full address
		var city = $("#city_" + partie).val();
		var state = $("#administrative_area_level_1_" + partie).val();
		var zip = $("#postal_code_" + partie).val();
		var street = $("#street_" + partie).val();
		var full_address = street;
		var suite = $("#suiteInput").val();
		if (suite!="") {
			if (full_address!="") {
				full_address += ", ";
			}
			full_address += suite;
		}
		if (full_address!="") {
			full_address += ", ";
		}
		full_address += city + ", " + state + " " + zip;
		$("#full_addressInput").val(full_address);
		$("#full_addressSpan").html(full_address);
		$("#full_addressSpan").css("width", "283px");
		$("#full_addressSpan").css("font-size", "0.8em");
	},
	selectBingAddress: function(event) {
		//utilities.js
		var partie = "person";
		selectBingAddress(event, partie, "map_results_holder");
		
		return;
	},
	updateStreet: function(event) {
		var self = this;
		var element = event.currentTarget;
		this.model.set("street", element.value);
		
		this.updateFullAddress();
	},
	updateSuite: function(event) {
		var self = this;
		var element = event.currentTarget;
		this.model.set("suite", element.value);
		
		this.updateFullAddress();
	},
	updateCity: function(event) {
		var self = this;
		var element = event.currentTarget;
		this.model.set("city", element.value);
		
		this.updateFullAddress();
	},
	updateState: function(event) {
		var self = this;
		var element = event.currentTarget;
		this.model.set("state", element.value);
		
		this.updateFullAddress();
	},
	updateZip: function(event) {
		var self = this;
		var element = event.currentTarget;
		this.model.set("zip", element.value);
		
		//lookup
		if (element.value.length > 4) {
			var url = 'api/checkzip/' + element.value;
	
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						if (typeof data.city=="undefined") {
							return;
						}
						var partie = "person";
						$("#city_" + partie).val(data.city);
						$("#city_" + partie).css("visibility", "visible");
						$("#administrative_area_level_1_" + partie).val(data.state_prefix);
						$("#administrative_area_level_1_" + partie).css("visibility", "visible");
						
						self.updateFullAddress();
						
						//$("#full_addressSpan").fadeIn();
						if ($("#full_addressSpan").hasClass("hidden")) {
							$("#full_addressSpan").toggleClass("hidden");
						}
					}
				}
			});
		}
	},
	checkForToken: function(event) {
		if (event.keyCode==16) {
			event.preventDefault();
			return;
		}
		
		this.model.set("current_input", "full_name");
		
		var blnClearByTyping = false;
		var selection_start = document.getElementById("full_nameInput").selectionStart;
		var selection_end = document.getElementById("full_nameInput").selectionEnd;
		var selection_length = document.getElementById("full_nameInput").value.length;
		if ((selection_end - selection_start)==selection_length) {
			blnClearByTyping = true;
			/*
			this.model.set("full_name", "");
			this.model.set("id", -1);
			*/
		}
		
		if ($(".person #full_nameInput").val()=="" || blnClearByTyping) {
			//by backspacing all the way, the user effectively says that they want a brand new record
			var person_name = document.getElementById("full_nameInput").value;
			$(".person .input_class").val("");
			if (!blnClearByTyping) {
				//not meant to clear the company name box
				document.getElementById("full_nameInput").value = person_name;
			}
			$(".person .span_class").html("");
			//clear the id
			$(".person #table_id").val("");
			$(".person #person_id").val("");
			
			personTokenInput();
		}
	},
	doTimeouts: function() {
		var self = this;
		
		gridsterIt(9);
		
		$(".token-input-dropdown-person").css("width", "280px");
		if(this.model.id=="" || this.model.id==-1){
			//editing mode right away
			this.model.set("editing", false);
 
			$(".person .edit").trigger("click"); 
			$(".person .delete").hide();
			$(".person .reset").hide();
			$(".person #table_id").val("");

		}
		
		//look up applicant if no id
		if (self.model.id < 0) {
			setTimeout(function() {
				personTokenInput();
			}, 1112);
		}
		
		//hide some stuff for rolodex
		if (typeof self.model.get("bln_contact") != "undefined") {
			if (self.model.get("bln_contact")) {
				$(".medical_items").hide();
				$("#panel_title").html("Contact");
			}
		}
		$(".person #full_nameInput").css("border", "0px inset rgb(0, 0, 0)");
		//showTheWatch();
		//startTheWatch();
		/*
		$("#billing_time_dropdownInput").editableSelect({
			onSelect: function (element) {
				var billing_time = $("#billing_time_dropdownInput").val();
				$("#billing_time").val(billing_time);
				//alert(billing_time);
			}
		});
		$("#billing_dropdown_holder").hide();
		*/
		
		if (typeof this.model.get("applicant_label") != "undefined") {
			var applicant_label = this.model.get("applicant_label");
			$("#panel_title").html(applicant_label);
			if (applicant_label=="Vehicle Owner") {
				//get the representing from the hash
				var hash = document.location.hash;
				var arrHash = hash.split("/");
				$(".person #representing").val(arrHash[arrHash.length - 1]);
			}
		}
		
		if (self.model.get("intake_screen")) {
			//$("#content").html("<div style='font-weight:2.6em; font-weight:bold'>Intake Screen</div>" + $("#content").html())
			
			//$(".button_row").hide();
			$(".form_label_vert").css("color", "white");
			$(".form_label_vert").css("font-size", "1.1em");
			$(".gridster_border").css("background", "none");
			$(".gridster_border").css("border", "none");
			$(".gridster_border").css("-webkit-box-shadow", "");
			$(".gridster_border").css("box-shadow", "");
			$("#person_form #panel_title").css("font-weight", "normal");
			document.getElementById("panel_title").parentElement.style.fontSize = "1.3em";
			document.getElementById("sub_category_holder_person").parentElement.style.marginTop = "-5px";
			document.getElementById("person_form").parentElement.style.background = "none";
			$("#person_gridster_ul #other_phoneGrid .form_label_vert").html("Phone 2");
			$("#person_gridster_ul #work_emailGrid .form_label_vert").html("Wrk Em");
			
			$(".person .gridster_border").hide();
			$(".person #full_nameGrid").show();
			$(".person #salutationGrid").show();
		}
	},
	editPersonViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".person_" + field_name;
		}
		editField(element, master_class);
	},
	editPersonViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".person_" + field_name;
		}
		//editField(element, master_class);
	},
	
	savePersonViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".person_" + field_name;
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
			if (theclass!="") {
				var arrClass = theclass.split(" ");
				theclass = "." + arrClass[0] + "." + arrClass[1];
			}
			field_name = theclass + " #" + element_id;
			
			//restore the read look
			editField($(field_name + "Grid"));
			
			var element_value = $(field_name + "Input").val();
			$(field_name + "Span").html(escapeHtml(element_value));
		}			
		
		//this should not redraw, it will update if no id
		this.addPersonView(event);
	
	},
	changeGender: function(event) {
		event.preventDefault(); // Don't let this button submit the form
		
		//change gender on addl info screen only we are in dashboard mode
		//brand new person -> addl info is showing
		if(this.model.id > -1){
			return;
		}
		//change the index on gender
		//default to female, get out on dr
		var selectedIndex = 1;
		switch($(".person #salutationInput").val()) {
			case "Mr":
				selectedIndex = 2;
				 break;
			case "Dr":
				selectedIndex = 0;
				break;
		}
		$(".kai #genderInput").prop('selectedIndex', selectedIndex);
		$(".kai #genderSpan").html($(".person #salutationInput").val());
	},
	displayAge: function(event) {
		event.preventDefault(); // Don't let this button submit the form
		
		//change gender on addl info screen only we are in dashboard mode
		//brand new person -> addl info is showing
		var dob = event.currentTarget.value;
		if (dob == "Invalid date") {
			event.currentTarget.value = "";
			dob = "";
		}
		if (dob!="") {
			//var age = moment(dob.value).fromNow(true).replace(" years", "");
			var age = dob.getAge();
		}
		
		$(".kai #ageInput").val(age);
		$(".kai #ageSpan").html(age);
	},
	confirmApply: function(event) {
		//var element = event.currentTarget;
		event.preventDefault();
		//hide any map search
		hideResults();
		if (this.model.id == -1) {
			//stopTheWatch();
			this.savePerson(event);
			return;
		}
		if (this.model.get("person_uuid") == this.model.get("parent_person_uuid")) {	
			$("#confirm_apply").css({display: "none", top: 20, left: 450, position:'absolute'});
			$("#confirm_apply").fadeIn();
			//if they press yes, they will go to savePartie
		} else {
			//stopTheWatch();
			this.savePerson(event);
		}
		//if they press yes, they will go to savePartie
	},
	billTimeSpent: function(event) {
		//var element = event.currentTarget;
		event.preventDefault();
		var billing_time = prompt("Please enter minutes worked", "15");
    
    	if (billing_time != null) {
			$("#billing_time").val(billing_time);
		}
		this.confirmApply(event);
	},
	billTimeFill: function() {
		//$("#billing_time_dropdownInput option").removeAttr();
		//$("#billing_time_dropdownInput").val("");
		//$("#billing_time").val(billing_time);
	},
	billTimeFilled: function() {
		var billing_time = $("#billing_time_dropdownInput").val();
		$("#billing_time").val(billing_time);
		alert(billing_time);
		///$("#billing_time_dropdownInput").blur();
	},
	cancelApply: function(event) {
		event.preventDefault();
		$("#confirm_apply").fadeOut();
		$("#confirm_apply_decide").val("N");
		//stopTheWatch();
		this.savePerson(event);
	},
	applyPerson: function(event) {
		if (confirm("Are you sure you want to apply?") == true) {	
			event.preventDefault();
			$("#confirm_apply").fadeOut();
			$("#confirm_apply_decide").val("Y");
			//stopTheWatch();
			this.savePerson(event);
		} else {
			event.preventDefault();
			$("#confirm_apply").fadeOut();
			$("#confirm_apply_decide").val("N");
			//stopTheWatch();
			this.savePerson(event);
		}
	},
	savePerson:function (event) {
		$("#edit_row").hide();
		$("#gifsave").css("margin-top", "-5px");
		$("#gifsave").show();
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		//rename if autofill on
		if (blnNoAutoFill) {
			var inputs = $("#person_form input");
			var arrLength = inputs.length;
			for (var i = 0; i < arrLength; i++) {
				var inp = inputs[i];
				if (typeof $("#person_form #" + inp.id).attr("name") != "undefined") {
					if (inp.name != inp.id) {
						inp.name = inp.id;
					}
				}
			}
		}
		
		var api_url = "person"
		
		addForm(event, "person", api_url);
		$("#confirm_apply_decide").val("N");
		return;
	},
	addPersonView:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		if ($(".person #table_id").val()=="") {
			$(".person #table_id").val("-1")
		}
		addForm(event, "person");
		return;
    },
	deletePersonView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "person");
		return;
    },
	setFullNameCurrent: function(event) {
		this.model.set("current_input", "full_name");
	},
	clearCurrent: function(event) {
		this.model.set("current_input", "");
	},
	toggleEditViewPerson: function(event) {
		if ($(".person #table_id").val()=="") {
			if (this.model.get("current_input")!="full_name") {
				event.preventDefault();
			} else {
				$(".person #full_nameInput").val($("#token-input-full_nameInput").val());
				$(".person #full_nameInput").css("border", "2px inset rgb(0, 0, 0)");
				$(".person #full_nameInput").show();
				$(".person .token-input-list-person").hide();
				$(".token-input-dropdown-person").hide();
				event.preventDefault();
			}
			return;
		}
		event.preventDefault();
		if ($(".person #table_id").val()=="") {
			return;
		}
		
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			//this.model.set("editing", false);
			//hide dropdown
			//$("#billing_dropdown_holder").hide();
			//$("#applicant_row_links").show();
			//return;
		}
		//$("#address").show();
		//get all the editing fields, and toggle them back
		$(".person_view .editing").toggleClass("hidden");
		$(".person_view .span_class").removeClass("editing");
		$(".person_view .input_class").removeClass("editing");
		$(".person .token-input-list-person").removeClass("editing");
		
		$(".person .token-input-list-person").toggleClass("hidden");
		$(".person_view .span_class").toggleClass("hidden");
		$(".person_view .input_class").toggleClass("hidden");
		$(".person_view .input_holder").toggleClass("hidden");
		$(".button_row.person").toggleClass("hidden");
		$(".edit_row.person").toggleClass("hidden");
		
		if (this.model.get("editing")) {
		//show dropdown
			$("#billing_dropdown_holder").hide();
			$("#applicant_row_links").show();
			this.model.set("editing", false);
		} else { 
			$("#applicant_row_links").hide();
			$("#billing_dropdown_holder").show();
			this.model.set("editing", true);
		}
		
		//manual follows the input
		$("#manual_address").css("display", $("#full_addressInput").css("display"));
	},
	
	showCalendar:function(event) {
		event.preventDefault(); // Don't let this button submit the form
		//clean up button
		$("button.calendar").fadeOut(function() {
			$("button.information").fadeIn();
		});
		window.location.hash = "#kases/events/" + $('#table_id').val()
	},
	
	resetPersonForm: function(event) {
		this.model.set("current_input", "reset_button");
		
		event.preventDefault();
		
		this.toggleEditViewPerson(event);
	},
	autoSave:function(event) {
		//!self.model.get("intake_request")
		if (!blnAutoSave) {
			return;
		}
		if (!this.model.get("intake_screen")) {
			return;
		}
		
		var element = event.currentTarget;
		
		$("#phone_intake_feedback_div").html("Autosaving...");
				
		var fieldname = element.id.replace("Input", "");
		var value = element.value;
		
		var partie = "person";
		var case_id = $("#kase_form #id").val();
		var id = $("#" + partie + "_form #table_id").val();
		var url = "api/person/field/update";
		
		var formValues = "table_name=person&id=" + id + "&case_id=" + case_id + "&fieldname=" + fieldname + "&value=" + encodeURIComponent(value);
		var border = $("#" + partie + "_form #" + element.id).css("border");
		if (id == "" || id=="-1") {
			url = "api/person/add";
			//tack on the full name			
			var full_name = $("#full_nameInput").val();
			var formValues = "table_name=person&case_id=" + case_id + "&" + fieldname + "=" + encodeURIComponent(value);
			if (formValues.indexOf("full_name") < 0) {
				formValues += "&full_name=" + encodeURIComponent(full_name);
			}
		}	
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#" + partie + "_form #person_id").val(data.id);
					$("#" + partie + "_form #table_id").val(data.id);
					$("#" + partie + "_form #" + element.id).css("border", "2px solid lime");
					if (element.id=="full_nameInput") {
						$("#full_nameGrid").css("border", "2px solid lime");
					}
					//address
					if (element.id=="full_addressInput") {
						setTimeout(function() {
							$("#street_" + partie).trigger("blur");
						}, 300);
						setTimeout(function() {
							$("#suite_" + partie).trigger("blur");
						}, 400);
						setTimeout(function() {
							$("#city_" + partie).trigger("blur");
						}, 500);
						setTimeout(function() {
							$("#administrative_area_level_1_" + partie).trigger("blur");
						}, 600);
						setTimeout(function() {
							$("#postal_code_" + partie).trigger("blur");
						}, 600);
					}
					$("#phone_intake_feedback_div").html("Autosaved&nbsp;&#10003;");
					setTimeout(function() {
						$("#" + partie + "_form #" + element.id).css("border", border);
						if (element.id=="full_nameInput") {
							$("#full_nameGrid").css("border", border);
						}
						$("#phone_intake_feedback_div").html("");
					}, 2500);
				}
			}
		});
	},
	valuePersonViewChanged: function(event) {
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
window.PersonSummaryView = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
		_.bindAll(this, "togglePersonEdit", "resetForm", "saveSuccessful");
    },
	
	 events:{
        "click .delete":		"deletePerson",
		"click .save":			"confirmApply",
		"click .save_field":	"savePersonField",
		"click .edit": 			"togglePersonEdit",
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
		
        //$('#details', this.el).html(new PersonSummaryView({model:this.model}).render().el);
		
		this.model.set("editing", false);
		
		if(this.model.id==""){
			//editing mode right away
			this.model.set("editing", true);
			setTimeout('$( ".edit" ).trigger( "click" )', 500);
		}		
        
		setTimeout("gridsterIt(0)", 100);
        return this;
    },
	
	savePersonField: function (event) {
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
		this.addPerson(event);
	},
	confirmApply: function(event) {
		//var element = event.currentTarget;
		event.preventDefault();
		if (this.model.get("person_uuid") == this.model.get("parent_person_uuid")) {
			$("#confirm_apply").css({display: "none", top: 20, left: 350, position:'absolute'});
			$("#confirm_apply").fadeIn();
			//if they press yes, they will go to savePartie
		} else {
			this.savePerson(event);
		}
		//if they press yes, they will go to savePartie
	},
	cancelApply: function(event) {
		event.preventDefault();
		$("#confirm_apply").fadeOut();
		$("#confirm_apply_decide").val("N");
		this.savePerson(event);
	},
	applyPartie: function(event) {
		event.preventDefault();
		$("#confirm_apply").fadeOut();
		$("#confirm_apply_decide").val("Y");
		this.savePerson(event);
	},
	savePerson:function (event) {
		$("#edit_row").hide();
		$("#gifsave").css("margin-top", "-5px");
		$("#gifsave").show();
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		var api_url = "person"
		
		addForm(event, "person", api_url);
		
		return;
	},
	addPerson:function (event) {
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
			this.updatePerson(event);
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
	
	updatePerson:function (event) {
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
	
	deletePerson:function (event) {
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
	
	togglePersonEdit: function(e) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
			
		//get all the editing fields, and toggle them back
		$(".person.editing").toggleClass("hidden");
		$(".person.span_class").removeClass("editing");
		$(".person.input_class").removeClass("editing");
		
		$(".person.span_class").toggleClass("hidden");
		$(".person.input_class").toggleClass("hidden");
		$(".person.input_holder").toggleClass("hidden");
		$(".person.button_row").toggleClass("hidden");
		$(".person.edit").toggleClass("hidden");
	},
	
	showCalendar:function(event) {
		window.location.hash = "#kase/events/" + $('#table_id').val()
	},
	
	resetForm: function(e) {
		this.togglePersonEdit(e);
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
window.person_image = Backbone.View.extend({
    initialize:function () {

    },
    events:{
        "click #upload_it_five": "uploadIt"
    },
    render:function () {
		var self = this;
        $(this.el).html(this.template({"label": this.model.toJSON().applicant_label}));
		setTimeout(function() {
			var timestamp = moment().format('YYYY-MM-DD h:mm:ss a');
			//console.log("timestamp:" + timestamp);
			var token = md5('ikase_system' + timestamp);
			$('#file_upload').uploadifive({
				'auto'             : false,
				'checkScript'      : 'check-exists.php',
				'formData'         : {
									   'timestamp' : timestamp,
									   'token'     : token,
									   'case_id' : self.model.get("case_id")
									 },
				'queueID'          : 'queue',
				'uploadScript'     : 'api/uploadifive.php',
				'onUploadComplete' : function(file, data) { 
					setTimeout(function() {
						self.saveFile(data);
					}, 50);
				}
			});
		}, 500);
		
        return this;
    },
	uploadIt: function(event) {
		event.preventDefault();
		$('#file_upload').uploadifive('upload');
	},
	saveFile: function(filename) {
		var self = this;
		var theattribute = $(".person_image_form #attribute").val();
		var url = 'api/documents/add';
		formValues = "verified=Y&type=&description_html=&description=applicant&document_extension=&document_filename=" + filename + "&document_name=" + filename + "&case_id=" + this.model.get("case_id") + "&case_uuid=" + this.model.get("case_uuid") + "&document_date=" + moment().format("YYYY-MM-DD") + "&parent_document_uuid=&attribute=" + theattribute;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//console.log(data.toJSON);
					$('#picture_holder').html("<img src='uploads/" + self.model.toJSON().customer_id + '/' + current_case_id + '/' + filename + "' class='applicant_img'>");
				}
			}
		});
	}
});

var personTokenInput = function() {
	$(".token-input-dropdown-person").css("width", "250px");
	var theme = {
		theme: "person", 
		minChars:3, 
		noResultsText:"None Found", 
		propertyToSearch:"full_name",
		tokenLimit:1,
		onResult: function(results) {
			//console.log(results);
			return results;
		},
		onAdd: function(item) {
			var self = this;
			if (!isNaN(item.id) && item.id!="") {
				//look up the applicant info
				var applicant_info = new Person({ id: item.id });
				applicant_info.fetch({
					success: function(data) {
						//now populate the appropriate fields
						var arrFields = $(".person .input_class");
						var arrayLength = arrFields.length;
						for (var i = 0; i < arrayLength; i++) {
							var theid = arrFields[i].id;
							//special case for ssn
							if (theid == "ssn_div") {
								var ssn = data.get("ssn");
								if (ssn.length == 9) {
									ssn1 = ssn.substr(0, 3);
									ssn2 = ssn.substr(3, 2);
									ssn3 = ssn.substr(5, 4);
									$(".person #ssn1").val(ssn1);
									$(".person #ssn2").val(ssn2);
									$(".person #ssn3").val(ssn3);
								}
								continue;
							}
							//special case for dob
							if (theid == "dobInput") {
								var dob = data.get(theid.replace("Input", ""));
								dob = moment(dob).format("MM/DD/YYYY");
								$(".person #" + theid).val(dob);
								continue;
							}
							if (theid != "full_nameInput") {
								$(".person #" + theid).val(data.get(theid.replace("Input", "")));
							} else {
								$(".person #" + theid).val(item.full_name);
								$(".person #" + theid.replace("Input", "Span")).html(data.get(theid.replace("Input", "")));
							}
						}
						$(".person #person_id").val(item.id);
						
						//kai form
						var arrFields = $(".kai .input_class");
						var arrayLength = arrFields.length;
						for (var i = 0; i < arrayLength; i++) {
							var theid = arrFields[i].id;
							$(".kai #" + theid).val(data.get(theid.replace("Input", "")));
						}
						//update the automatics
						$("#salutationInput").trigger("change"); 
						$("#dobInput").trigger("blur");
						
						
						if (document.location.hash.indexOf("#intake")==0) {
							//trigger a blur to save it
							var context_id = this.context.id;
							setTimeout(function() {
								$("#" + context_id).trigger("blur");
							}, 500);
						}
					}
				});
				
			} 
			setTimeout(function() {
				$("#full_nameInput").val(item.full_name);
				$("#full_nameInput").show();
				$(".token-input-list-person").hide();
				$("#akaInput").focus();
				
				if (document.location.hash.indexOf("#intake")==0) {
					//trigger a blur to save it
					$("#full_nameInput").trigger("blur");
				}
			}, 500);
		}};
	//$("#full_nameInput").tokenInput("api/person/tokeninput", theme);
	//$("#token-input-full_nameInput").focus();
	
	$("#full_nameInput").tokenInput("api/person/tokeninput", theme);
	
	$(".person .token-input-list-facebook").css("margin-left", "70px");
	$(".person .token-input-list-facebook").css("margin-top", "-20px");
	$(".person .token-input-list-facebook").css("width", "383px");
	
	if (blnNoAutoFill) {
		$("#person_form").disableAutoFill();
		//document.getElementById("full_nameInput").name = makeRandomString(10);
	} 
	
	$("#token-input-full_nameInput").focus();
}
window.dob_list_view = Backbone.View.extend({
	events:{
		"click #export_dob_emails":						"exportDOBEmails",
		"click #export_dob_emails2":					"exportDOBEmailsNext",
		"click #next_month_dob":						"listNextMonth",
		"click #dob_list_view_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "dob_list_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		try {
			$(this.el).html(this.template({
					dobs: this.collection, 
					page_title: this.model.get("page_title"),
					blnCurrentMonth: this.model.get("blnCurrentMonth")
			}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		//$(this.el).html(this.template(this.model.toJSON()));
		
        return this;
	},
	listNextMonth: function() {
		var self = this;
		var month = moment().format("M");
		month++;
		if (month > 12) {
			month = 1;
		}
		var url = "api/clientemailsbymonth/" + month;
						
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
				self.collection = data;
				self.model.set("blnCurrentMonth", false);
				self.render();
			}
		});
	},
	exportDOBEmails: function(event) {
		event.preventDefault();
		
		var url = "reports/export_dob_emails.php";
		
		window.open(url);
	},
	exportDOBEmailsNext: function(event) {
		event.preventDefault();
		var month = moment().format("M");
		month++;
		if (month > 12) {
			month = 1;
		}
		var url = "reports/export_dob_emails.php?month=" + month;
		
		window.open(url);
	},
	doTimeouts: function() {
		$("#dob_list_view").css("font-size", "1.1em");
		$("#dob_list_view th").css("font-size", "1.1em");
		var month = moment().format("M");
		month++;
		if (month > 12) {
			month = 1;
		}
		var url = "api/clientemailsbymonth/" + month;
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
				$("#export_dob_emails2").html("Export Next Month DOB Emails (" + data.length + ")");
				$("#next_month_dob").html("Next Month (" + data.length + ")");
			}
		});
	}
});