window.injury_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	 events:{
        "click .injury .delete":				"confirmDeleteInjury",
		"click .injury .save":					"saveInjury",
		"click .injury .save_field":			"saveInjuryViewField",
		"click .injury .edit": 					"editInjuryForm",
		"click .injury .reset": 				"resetInjuryForm",
		"click .kase .calendar": 				"showCalendar",
		"click .injury #add_employer_address":	"addEmployerAddress",
		"keyup .injury .input_class": 			"valueInjuryViewChanged",
		"dblclick .injury .gridster_border": 	"editInjuryViewField",
		"dblclick #notesGrid": 					"editInjuryViewNotesField",
		"click #checkCT":						"showEndtime",
		"click .same_as_employer":				"clearInjuryAddress",
		"click .compose_new_note":				"newNotes",
		"keyup #occupationInput":				"checkForOccupationToken",
		"keydown #occupationInput":				"checkForOccupationToken",
		"keyup #token-input-occupationInput":	"clearOccupation",
		"keyup #token-input-occupationInput":	"setCurrent",
		"click .scrape_injury":					"scrapeInjury",
		"change #statute_intervalInput":		"setStatuteDate",
		"click #ratings_link":					"openRatings",
		"focus #start_dateGrid":				"showIntakeFields",
		"click .bing_address":					"selectBingAddress",
		"blur .injury .input_class":		 	"autoSave",
		"click .injury_view#all_done":			"doTimeouts"
    },
    render: function () {
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "injury_view";
			var extension = "php";
			
			if (typeof this.model.get("holder") == "undefined") {
				this.model.set("holder", "injury_holder");
			}
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		
		//statute
		if (this.model.get("statute_limitation")=="0000-00-00") {
			if ((this.model.get("start_date")=="0000-00-00" && this.model.get("end_date")=="0000-00-00") || (this.model.get("start_date")=="" && this.model.get("end_date")=="")) {
				this.model.set("statute_limitation", "");
				this.model.set("statute_years", "");
			} else {
				//give it a 1 year default
				//change from 1 to 5 per thomas, 3/22/2017
				if (this.model.get("end_date")=="0000-00-00") {
					var statute_limitation = moment(this.model.get("start_date")).add(5, "years").format("MM/DD/YYYY");
					this.model.set("statute_limitation", statute_limitation);
				} else {
					var statute_limitation = moment(this.model.get("end_date")).add(5, "years").format("MM/DD/YYYY");
					this.model.set("statute_limitation", statute_limitation);
				}
				this.model.set("statute_years", "5");
				
				var injury_id = this.model.get("id");
				if (injury_id > 0) {
					//update the statute limitation now
					var formValues = "table_name=injury&id=" + injury_id;
					formValues += "&statute_limitation=" + moment(statute_limitation).format("YYYY-MM-DD");
					formValues += "&statute_interval=" +  (5 * 366);
					
					var theaction = "update";
					formValues += "&case_id=" + current_case_id;
					
					var url = "api/injury/" + theaction;
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
							success:function (data) {
								$("#statute_limitationSpan").css("background", "lime");
								$("#statute_limitationSpan").css("color", "black");
								setTimeout(function() {
									$("#statute_limitationSpan").css("background", "none");
									$("#statute_limitationSpan").css("color", "white");
								}, 1500);
							}
					});
			}
			}
		} else {
			this.model.set("statute_limitation", moment(this.model.get("statute_limitation")).format("MM/DD/YYYY"));
		}
		mymodel = this.model.toJSON();
		$(this.el).html(this.template(mymodel));
		//$("#address").hide();
        //$('#details', this.el).html(new InjurySummaryView({model:this.model}).render().el);
		
		this.model.set("editing", false);
		
		return this;
    },
	doTimeouts: function() {
		var self = this;
		
		if (typeof this.model.get("intake_screen") == "undefined") {
			this.model.set("intake_screen", false);		
		}
		if (this.model.get("gridster_me") || this.model.get("grid_it")) {
			gridsterById('gridster_injury');
			$( ".injury .datepicker_1" ).hide();
		}
		
		if (self.model.get("intake_screen")) {
			document.getElementById("injury_form").parentElement.style.background = "none";
			$("#injury_form #panel_title").css("font-weight", "normal");
			$("#injury_form #panel_title").css("font-size", "1em");
			$(".button_row.injury").hide();
			
			$(".injury .gridster_border").css("background", "none");
			$(".injury .gridster_border").css("border", "none");
			$(".injury .gridster_border").css("-webkit-box-shadow", "");
			$(".injury .gridster_border").css("box-shadow", "");
			$(".injury .form_label_vert").css("color", "white");
			$("#injury_form .form_label_vert").css("font-size", "1em");
			
			$("#injury_form  #occupation_groupGrid .form_label_vert").html("Occ. Group");
			
			$("#injury_form  #statute_limitationGrid .form_label_vert").html("Statute");
			$("#injury_form  #full_addressGrid .form_label_vert").html("Location");
			
			$(".injury .gridster_border").hide();
			$(".injury #start_dateGrid").show();
			$(".injury #start_dateGrid").attr("data-row", "1");
			
			setTimeout(function() {
				//reposition the grids
				var injury_grids = $(".injury .gridster_border");
				var arrLength = injury_grids.length;
				var blnReduce = false;
				for (var i = 0; i< arrLength; i++) {
					var grid = injury_grids[i];
					if (grid.id != "start_dateGrid") {
						var current_row = $(".injury #" + grid.id).attr("data-row");
						//if (grid.id=="statute_limitationGrid") {
						if (blnReduce) {
							//break;
							current_row -= 2;
						}
						if (grid.id=="occupation_groupGrid") {
							//hidden
							blnReduce = true;
							continue;
						}
						var next_row = Number(current_row) + 1;
						if (grid.id == "suiteGrid" || grid.id == "explanationGrid") {
							next_row--;
						}
						$(".injury #" + grid.id).attr("data-row", next_row);
					}
				}
				
				$(".injury #occupation_groupGrid").hide();
			}, 1234);
		}
		
		if (typeof this.model.get("case_type") != "undefined") {
			if (this.model.get("case_type")=="immigration") {
				$("#injury_holder #panel_title").html("Immigration Details");
				$(".scrape_injury").hide();
				$("#new_doi_link").hide();
				$("#injury_demographics_link").hide(); 
				$("#adj_number_label").html("Alien Number");
				$(".no_immigration_info").hide();
				$("#injury_explanation_label").html("Case Description");
				$("#explanationGrid").attr("data-row", "5");
				$("#explanationGrid").attr("data-sizey", "6");
				$("#explanationInput").css("height", "240px");
				$(".save").css("margin-top", "");
				$("#partie_edit").css("margin-top", "");
				$("#bodyparts_holder").hide();
				$("#injury_number_holder").hide();
				$("#gridster_additional_case_number").hide();
			}
		}
		if(this.model.id=="" || this.model.id==-1 || this.model.get("start_date")=="" || this.model.get("start_date")=="0000-00-00"){
			//editing mode right away
			this.model.set("editing", false);
			this.model.set("current_input", "");
			var employer_address = self.model.get("employer_address");
			
			$(".injury .edit").trigger("click"); 
			$(".injury .delete").hide();
			$(".injury .reset").hide();
			
			if (this.model.get("adj_number")=="") {
				$("#adj_numberInput").val("Unassigned");
			}
			if (this.model.get("full_address")=="") {
				$("#full_addressInput").val(employer_address);
				$("#add_employer_holder").html("<span style='font-style:italic;color:white'>Autofill from employer address</span>");
			}
			
			//hide all the save buttons
			$(".save").hide();
			//except for the main one
			$(".injury .save").show();
			
			if (self.model.get("case_id")!="") {
				var kase_dois = new KaseInjuryCollection({case_id: self.model.get("case_id")});
				kase_dois.fetch({
					success: function(kase_dois) {
						if (kase_dois.length > 0) {
							var doi = kase_dois.toJSON()[0];
							if (doi.occupation!="") {
								$(".injury #occupationInput").val(doi.occupation);
							}
						}
					}
				});
			}
			$("#adj_numberInput").focus(); 			
		}
		
		var occupation = self.model.get("occupation");

		if(this.model.id=="" || this.model.id==-1 || this.model.get("start_date")=="" || this.model.get("start_date")=="0000-00-00"){
			occupationTokenInput("new", "");
			$(".injury .token-input-list-facebook").toggleClass("hidden");
		} else {
			occupationTokenInput(self.model.get("occupation_id"), self.model.get("occupation"));
		}
		//}
		if (!blnBingSearch) {
			initializeGoogleAutocomplete('injury');
		} else {
			$("#full_addressInput").on("keyup", lookupBingMaps);
		}
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
		
		var partie = "injury";
		var case_id = $("#kase_form #id").val();
		var id = $("#" + partie + "_form #table_id").val();
		var url = "api/injury/field/update";
		
		var formValues = "table_name=injury&id=" + id + "&case_id=" + case_id + "&fieldname=" + fieldname + "&value=" + encodeURIComponent(value);
		var border = $("#" + partie + "_form #" + element.id).css("border");
		/*
		//the add already happened when I saved the case
		if (id == "" || id=="-1") {
			
		}
		*/	
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//no need to save the id since we already have one
					//$("#" + partie + "_form #table_id").val(data.success);
					$("#" + partie + "_form #" + element.id).css("border", "2px solid lime");
					
					//special case for statute
					if (fieldname=="start_dateInput") {
						setTimeout(function() {
							$("#statute_limitationInput").trigger("blur");
						}, 5000);
					}
					$("#phone_intake_feedback_div").html("Autosaved&nbsp;&#10003;");
					setTimeout(function() {
						$("#" + partie + "_form #" + element.id).css("border", border);
						
						$("#phone_intake_feedback_div").html("");
					}, 2500);
				}
			}
		});
	},
	selectBingAddress: function(event) {
		//utilities.js
		selectBingAddress(event, "injury", "bing_results");
	},
	showIntakeFields: function() {
		var self = this;
		
		if (!self.model.get("intake_screen")) {
			return;
		}
		//$(".injury #start_dateGrid").attr("data-row", "4");
		$(".injury .gridster_border").show();
		$(".injury #occupation_groupGrid").hide();
	},
	openRatings: function(event) {
		event.preventDefault();
		window.open("disabilities_rating.pdf");
	},
	setStatuteDate: function(event) {
		if (event.target.value == "-99") {
			$("#statute_limitationInput").val("");
			return;
		}
		var days_val = Number(event.target.value) * 366;
		var years_val = Number(event.target.value);
		var start_date = $("#start_dateInput");
		var end_date = $("#end_dateInput");
		if ( end_date.val() == "") {
			//change the statute of limitation
			var current_date = start_date.val();
		} else {
			var current_date = end_date.val();
		}
		if (current_date=="" || current_date=="0000-00-00") {
			return;
		}
		var formValues = "years=" + years_val + "&days=" + days_val + "&date=" + current_date;
			
		$.ajax({
		  method: "POST",
		  url: "api/calculator_post.php",
		  dataType:"json",
		  data: formValues,
		  success:function (data) {
			  if(data.error) {  // If there is an error, show the error tasks
				  alert("error");
			  } else {
				  calculated_date = moment(data[0].calculated_date).format("MM/DD/YYYY");
				  $("#statute_limitationInput").val(calculated_date);
			  }
		  }
		});
	},
	scrapeInjury: function(event) {
		var self = this;
		
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var injury_id = arrElement[arrElement.length - 1];
		
		var adj_number = $("#adj_numberInput").val();
		if (adj_number=="") {
			adj_number = prompt("Please enter the ADJ", "ADJ");
			if (adj_number == null || adj_number == "" || adj_number.indexOf("ADJ")===false) {
				return;
			}
		}
		
		adj_number = adj_number.trim();
		var scrape = new Scrape({adj_number: adj_number});
		var element_html = $("#" + element.id).html();
		$("#" + element.id).html("scraping...");
		
		scrape.fetch({
			error: function (collection, response, options) {
                // you can pass additional options to the event you trigger here as well
                //self.trigger('errorOnFetch');
				$("#" + element.id).html("Connection error.");
				return;
            },
			success: function (data) {
				var scrape = data.toJSON();	
				
				if (typeof scrape.error	!= "undefined") {
					$("#" + element.id).html(scrape.error);
					return;
				}
				//run this after animation
				var applicant = scrape.applicant;
				var doi = applicant.start_date;
				if (applicant.end_date!="0000-00-00") {
					doi += " - " + applicant.end_date + " CT";
				} else {
					applicant.end_date = "";
				}
				
				var formValues = "table_name=injury&id=" + injury_id;
				formValues += "&adj_number=" + adj_number;
				formValues += "&start_date=" + applicant.start_date + "&end_date=" + applicant.end_date;
				
				var theaction = "update";
				if (injury_id < 0) {
					theaction = "add";
					formValues += "&case_id=" + current_case_id;
				}
				var url = "api/injury/" + theaction;
				$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							} else { // If not
								$("#" + element.id).html("<span style='background:#3F9; color:white'>&#10003;</span>");
								var injury = new Injury({case_id: self.model.get("case_id")});
								if (self.model.get("injury_id") < 0) {
									var injury_id = data.id;
									self.model.set("injury_id", injury_id);
									self.scrapeBodyParts(injury_id, scrape);
								} 
								injury.set("id", self.model.get("injury_id"));
								injury.fetch({
									success: function (data) {
										data.set("gridster_me", true);
										data.set("glass", "card_fade_4");
										data.set("grid_it", true);
										data.set("employer_address", self.model.get("employer_full_address"));
										$('#injury_holder').html(new injury_view({model: data}).render().el);
									}
								});
							}
						}
				});
				if (injury_id > 0) {
					//body parts
					self.scrapeBodyParts(injury_id, scrape);
				}
				
				var judge = scrape.applicant.judge;
				
				if (judge.trim()!="") {
					//we need to get the venue id
					var parties = new Parties([], { case_id: current_case_id});
					parties.fetch({
						success: function(parties) {
							var venue_partie = parties.findWhere({"type": "venue"});
							if (typeof venue_partie != "undefined") {
								var venue = venue_partie.toJSON();
								var corporation_id = venue.corporation_id;
								
								var url = "../api/corporation/update";
								var formValues = "table_name=corporation&case_id=" + current_case_id + "&corporation_id=" + corporation_id;
								formValues += "&full_nameInput=" + judge;
								
								$.ajax({
									url:url,
									type:'POST',
									data: formValues,
									dataType:"json",
									success:function (data) {
										if(data.error) {  // If there is an error, show the error messages
											saveFailed(data.error.text);
										} else {
											//judge saved
											console.log("judge saved");
										}
									}
								});
							}
						}
					});
				}
			}
		});
	},
	scrapeBodyParts: function(injury_id, scrape) {
		var self = this;
		var formValues = "injury_id=" + injury_id + "&scraped=y";
		var bodyparts = scrape.bodyparts;
		var iCounter = 1;
		_.each( bodyparts, function(bodypart) {
			formValues += "&bodypart" + iCounter + "=" + bodypart.name;
			iCounter++;
		});
		//per thomas, update body parts as well 8/31/2015
		var url = "api/bodyparts/add";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						var bodyparts = new BodyPartsCollection([], { injury_id: injury_id, case_id: self.model.get("case_id"), case_uuid: self.model.get("case_uuid") });
						bodyparts.fetch({
							success: function(bodyparts) {
								$('#bodyparts_holder').html("");
								
								var mymodel = new Backbone.Model();
								mymodel.set("case_id", self.model.get("case_id"));
								mymodel.set("case_uuid", "");
								mymodel.set("injury_id", injury_id);
								mymodel.set("holder", "bodyparts_holder");
								
								$('#bodyparts_holder').html(new bodyparts_view({collection: bodyparts, model: mymodel}).render().el);
							}
						});
					}
				}
		});
	},
	addEmployerAddress: function(event) {
		var self = this;
		$("#add_employer_address").fadeOut(function() {
				var employer_address = self.model.get("employer_address");
				$("#full_addressInput").val(employer_address);
				
				if (self.model.get("employer_suite")!="") {
					$("#suiteInput").val(self.model.get("employer_suite"));
					$("#suiteSpan").html(self.model.get("employer_suite"));
				}
				$("#full_addressSpan").html(employer_address);
				$(".injury .edit").trigger("click");
			}
		);
	},
	editInjuryForm: function(event) {
		if (this.model.get("current_input")!="occupation") {
			this.model.set("current_input","edit_button");
		} else {
			//we hit enter in occupation field
		}
		this.toggleInjuryEdit(event);
	},
	setCurrent: function(event) {
		this.model.set("current_input", "occupation");
		//console.log("curr:" + event.keyCode);
		if (event.keyCode==13){
			$("#occupationInput").tokenInput("add", {id: -1, title: $("#token-input-occupationInput").val()});
			event.preventDefault();
		}
	},
	clearCurrent: function(event) {
		this.model.set("current_input", "");
	},
	clearOccupation: function(event) {
		//if someone is typing in the search box, no id no mo
		$("#occupation_title").val("");
		$(".injury .token-input-list-facebook").css("border", "0px");
	},
	checkForOccupationToken: function(event) {
		if (event.keyCode==16) {
			event.preventDefault();
			return;
		}
		this.model.set("current_input", "occupation");
		var blnClearByTyping = false;
		var selection_start = document.getElementById("occupationInput").selectionStart;
		var selection_end = document.getElementById("occupationInput").selectionEnd;
		var selection_length = document.getElementById("occupationInput").value.length;
		if ((selection_end - selection_start)==selection_length) {
			blnClearByTyping = true;
		}
		
		if ($("#occupationInput").val()=="" || blnClearByTyping) {
			//by backspacing all the way, the user effectively says that they want a brand new record
			var person_name = document.getElementById("occupationInput").value;
			if (!blnClearByTyping) {
				//not meant to clear the company name box
				document.getElementById("occupationInput").value = person_name;
			}
			if (document.getElementById("corporation_id") != null) {
				occupationTokenInput(document.getElementById("corporation_id").value, this.model.get("blurb"));
			}
		}
	},
	editInjuryViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".injury_" + field_name;
		}
		editField(element, master_class);
	},
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	},
	editInjuryViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".injury_" + field_name;
		}
		//editField(element, master_class);
	},
	clearInjuryAddress: function (event) {
		$(".injury #full_addressInput").val("");
		$("#street_number_injury").val("");
		$("#route_injury").val("");
		$("#street_injury").val("");
		$("#administrative_area_level_1_injury").val("");
		$("#locality_injury").val("");
		$("#sublocality_injury").val("");
		$("#neighborhood_injury").val("");
		$("#country_injury").val("");
	},
	saveInjuryViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".injury_" + field_name;
		}
		element_id = element_id.replace("SaveLink", "");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		if (master_class != "") {
			//hide all the subs
			$(".span_class" + master_class).toggleClass("hidden");
			$(".input_class" + master_class).toggleClass("hidden");
			$(".injury .token-input-list-facebook").toggleClass("hidden");
			
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
		this.saveInjury(event);
	
	},
	
	saveInjury:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		//valid start date
		if ($("#start_dateInput").val().length != 10) {
			$("#start_dateInput").val("");
		}
		//first check if occupation is in
		/*
		if ($("#occupationInput").val()=="") {
			$(".injury .token-input-list-facebook").css("border", "1px solid red");
			return;
		}
		*/
		if (!self.model.get("intake_screen")) {
			if(this.model.id==-1){
				var blnValid = $("#bodyparts_form").parsley('validate');
				if (blnValid) {
					setTimeout(function() {
						//$(".bodyparts .save").trigger("click");
						//$(".injury_number .save").trigger("click");
						//$(".additional_case_number .save").trigger("click");
					}, 1000);
				}
			}
		}
		addForm(event, "injury");
		return;
    },
	confirmDeleteInjury: function(event) {
		event.preventDefault();
		
		composeDelete(this.model.get("id"), "injury");
	},
	deleteInjuryView:function (event) {
		//obsolete
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "injury");
		return;
    },
	
	toggleInjuryEdit: function(event) {
		if (this.model.get("current_input")!="edit_button" && this.model.get("current_input")!="reset_button") {
			if (this.model.get("current_input") == "occupation") {
				event.preventDefault();
				return;
			}			
		}
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//$("#address").show();
		//get all the editing fields, and toggle them back
		$(".injury_view .editing").toggleClass("hidden");
		$(".injury_view .span_class").removeClass("editing");
		$(".injury_view .input_class").removeClass("editing");
		
		$(".injury_view .span_class").toggleClass("hidden");
		$(".injury_view .input_class").toggleClass("hidden");
		$(".injury_view .input_holder").toggleClass("hidden");
		$(".button_row.injury").toggleClass("hidden");
		$(".edit_row.injury").toggleClass("hidden");
		
		$(".injury .token-input-list-facebook").toggleClass("hidden");
		
		showEndtime();
	},
	showEndtime: function(event) {
		showEndtime();
	},
	resetInjuryForm: function(event) {
		this.model.set("current_input", "reset_button");
		event.preventDefault();
		this.toggleInjuryEdit(event);
	},
	valueInjuryViewChanged: function(event) {
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
function showEndtime() { 
	if (document.getElementById("checkCT").checked) {
		$( ".datepicker_1" ).show();
	} else {
		$( ".datepicker_1" ).hide();
		$( ".datepicker_1" ).val("");
	}
}
function checkStartEnd() {
	var start_date = $(".injury #start_dateInput");
	//is it in the future
	if (moment(start_date.val()) > moment()) {
		alert("The DOI cannot be in the future");
		start_date.val("");
		return;
	}
	var end_date = $(".injury #end_dateInput");
	if (moment(start_date.val()).isValid() && moment(end_date.val()).isValid()) {
		var check_start_date = moment(start_date.val());
		var check_end_date = moment(end_date.val());
		//make sure that end is after start
		if (check_start_date == "") {
			check_start_date = "0000-00-00";
		}
		if (check_start_date > check_end_date) {
			//show warning
			$(".injury.alert-warning").show();
			$(".injury.alert-text").html("The DOI end date must be after start date.");
			//focus end date
			//end_date[0].selectionStart = 0;
			//end_date[0].selectionEnd = end_date.val().length;
			end_date.val("");
		} else {
			$(".injury.alert-warning").hide();
		}
	}
	
	if (start_date.val() == "" && end_date.val() == "") {
		return;
	}
	var days_val = $("#statute_intervalInput").val() * 366;
	var years_val = Number($("#statute_intervalInput").val());
	
	if ( end_date.val() == "") {
		//change the statute of limitation
		var check_date = start_date.val();
	} else {
		var check_date = end_date.val();
	}
	var formValues = "years=" + years_val + "&days=" + days_val + "&date=" + check_date;			
	$.ajax({
	  method: "POST",
	  url: "api/calculator_post.php",
	  dataType:"json",
	  data: formValues,
	  success:function (data) {
		  if(data.error) {  // If there is an error, show the error tasks
			  alert("error");
		  } else {
			  calculated_date = moment(data[0].calculated_date).format("MM/DD/YYYY");
			  $("#statute_limitationInput").val(calculated_date);
		  }
	  }
	});
}
function occupationTokenInput(occupation_id, occupation) {
	//lookup occupation from onet db
	var theme_3 = {
		theme: "facebook", 
		propertyToSearch:"title",
		tokenLimit:1,
		onAdd: function(item) {
			if (isNaN(item.id)) {
				$("#occupationInput").tokenInput("remove", item);
				$("#occupationInput").tokenInput("add", {id: -1, title: item.name});
				return;
			}
			$("#occupationInput").val(item.title);
			$("#occupationSpan").html(item.title);
			$("#occupation_title").val(item.title);
			
		}
	};
	//turn off lookup because of conflict with eams length requirement 4/6/2017 per thomas
	/*
	$("#occupationInput").tokenInput("api/occupation", theme_3);
	
	$(".injury .token-input-list-facebook").css("margin-left", "90px");
	$(".injury .token-input-list-facebook").css("margin-top", "-26px");
	$(".injury .token-input-list-facebook").css("width", "353px");
	*/
	if (!isNaN(occupation_id) && occupation!="") {
		//*$("#occupationInput").val("");
		//*$("#occupationInput").tokenInput("add", {id: occupation_id, title: occupation});	
		$("#occupationInput").val(occupation);	
	}
	$(".injury .token-input-list-facebook").toggleClass("hidden");
}