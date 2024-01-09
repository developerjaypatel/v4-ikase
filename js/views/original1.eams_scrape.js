window.eams_scrape_view = Backbone.View.extend({
	render: function () {
		var self = this;
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "eams_scrape_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
	},
	events:{
		"click #scrape_button": 					"scrapeEams",
		"click #scrape_reset":						"resetEams",
		"click #scrape_save_button":				"saveEams",
		"click #scrape_update_button":				"updateEams",
		"click #scrape_print":						"printEams",
		"change .role_choice":						"chooseRole",
		"click #eams_scrape_view_all_done":			"doTimeouts"	
	},
	chooseRole: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var counter = arrID[arrID.length - 1];
		$("#partie_role_" + counter).val(element.value);
	},
	updateEams: function(event) {
		//we need to enter values for the drop downs for attorneys
		var attorney_types = $(".attorney_type");
		if (attorney_types.length > 0) {
			var arrayLength = attorney_types.length;
			for (var i = 0; i < arrayLength; i++) {
				var attorney_type = attorney_types[i];
				if (attorney_type.value=="") {
					alert("You must assign all the Law Firms");
					return;
				}
			}
		}
		
		event.preventDefault();
		var formValues = $("#scrape_form").serialize();
		//console.log(formValues);
		
		var url = "api/scrape/update";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						//hide the modal
						$('#myModal4').modal('toggle');
						//refresh
					}
				}
		});
	},
	saveEams: function(event) {
		//we need to enter values for the drop downs for attorneys
		
		var attorney_types = $(".attorney_type");
		if (attorney_types.length > 0) {
			var arrayLength = attorney_types.length;
			for (var i = 0; i < arrayLength; i++) {
				var attorney_type = attorney_types[i];
				if (attorney_type.value=="") {
					alert("You must assign all the Law Firms");
					return;
				}
			}
		}
		$("#scrape_feedback").html("<span class='white_text'>saving...</span>");
		event.preventDefault();
		var formValues = $("#scrape_form").serialize();
		//console.log(formValues);
		
		var url = "api/scrape/save";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						//hide the modal
						$("#scrape_feedback").html("<span class='white_text'>saved&nbsp;<span style='background:green;color:white'>&#10003;</span></span>");
						setTimeout(function() {
							$("#scrape_feedback").html("");
							$('#myModal4').modal('toggle');
							dois.fetch({
								success: function(dois) {
									document.location.href = "#kases/" + data.success;
								}
							});
						}, 1500);
					}
				}
		});
	},
	resetEams: function(event) {
		event.preventDefault();
		$("#scrape_save_button").fadeOut();
		$("#scrape_update_button").fadeOut();
		$("#scrape_button").fadeIn();	
		$("#scrape_reset").hide();	
				
		$('#applicant_scrape_holder').html("");
		$('#hearings_scrape_holder').html("");
		$('#previouscases_scrape_holder').html("");
		$('#bodyparts_scrape_holder').html("");
		$('#parties_scrape_holder').html("");
		$('#events_scrape_holder').html("");
		$("#scrape_adj_number").val("");		 
	},
	scrapeEams: function(event) {
		var self = this;
		var adj_number = $("#scrape_adj_number").val();
		adj_number = adj_number.trim();
		var scrape = new Scrape({adj_number: adj_number});
		$("#applicant_scrape_holder").html("<table align='center'><tr><td align='center' style=''><i class='icon-spin4 animate-spin' style='font-size:6em; color:white'></i><td><tr></table>");
		scrape.fetch({
			error: function (collection, response, options) {
                // you can pass additional options to the event you trigger here as well
                //self.trigger('errorOnFetch');
				$('#applicant_scrape_holder').html("Connection error.");
				return;
            },
			success: function (data) {
				var scrape = data.toJSON();	
				
				if (typeof scrape.error	!= "undefined") {
					$('#applicant_scrape_holder').html(scrape.error);
					return;
				}
				//run this after animation
				var applicant = scrape.applicant;
				var scrape_applicant = new Backbone.Model;
				var case_name = applicant.first_name + " " + applicant.last_name + " ( " + applicant.city + ", " + applicant.zip + ") vs " + applicant.employer;
				case_name = case_name.capitalizeWords();
				scrape_applicant.set("case_name", case_name);
				scrape_applicant.set("case_id", self.model.get("case_id"));
				scrape_applicant.set("injury_id", self.model.get("id"));
				var doi = applicant.start_date;
				if (applicant.end_date!="0000-00-00") {
					doi += " - " + applicant.end_date + " CT";
				}
				scrape_applicant.set("doi", doi);
				var venue = applicant.venue;
				var judge = "";
				if (applicant.judge.trim() != "") {
					judge = applicant.judge.trim();
				}
				scrape_applicant.set("venue", venue);
				scrape_applicant.set("judge", judge);
				
				scrape_applicant.set("first_name", applicant.first_name);
				scrape_applicant.set("last_name", applicant.last_name);
				scrape_applicant.set("employer", applicant.employer);
				scrape_applicant.set("city", applicant.city);
				scrape_applicant.set("zip", applicant.zip);
				scrape_applicant.set("start_date", applicant.start_date);
				scrape_applicant.set("end_date", applicant.end_date);
				scrape_applicant.set("deu", applicant.deu);
				
				$('#applicant_scrape_holder').html(new eams_applicant_view({model: scrape_applicant, collection: scrape.previous_cases}).render().el);
				
				//hearings
				if (scrape.hearings.length > 0) {
					var mymodel = new Backbone.Model();
					mymodel.set("holder", "hearings_scrape_holder");
					$('#hearings_scrape_holder').html(new eams_hearings_view({model: mymodel, collection: scrape.hearings}).render().el);			 
				} else {
					$('#hearings_scrape_holder').html("");
				}
				
				
				//previous cases
				var scrape_save_button = "save";
				//if (scrape.previous_cases.length > 0) {
				var blnFound = false;
				var previous_cases = scrape.previous_cases;
			    _.each( previous_cases, function(previous_case) {
					if (!blnFound) {
						if (previous_case.same_adj_number=="Y") {
							blnFound = true;
						}
					}
				});
				if (blnFound) {
					scrape_save_button = "udpate";
					$('#previouscases_scrape_holder').html(new eams_previouscases_view({collection: scrape.previous_cases}).render().el);			 
				} else {
					$('#previouscases_scrape_holder').html("");
				}
				var mymodel = new Backbone.Model();
				mymodel.set("holder", "bodyparts_scrape_holder");
				//bodyparts
				$('#bodyparts_scrape_holder').html(new eams_bodyparts_view({collection: scrape.bodyparts, model: mymodel}).render().el);
				var parties = scrape.parties;
				var roles = new Backbone.Collection(scrape.roles)
				
				_.each( parties, function(partie) {
					var role = roles.findWhere({name: partie.role});
					partie.count = 0;
					if (typeof role != "undefined") {
						partie.count = role.get("count");
					}
				});
				
				//parties
				$('#parties_scrape_holder').html(new eams_parties_view({collection: parties, model: self.model}).render().el);			 
				//events
				$('#events_scrape_holder').html(new eams_events_view({collection: scrape.events}).render().el);
				//we need a button 			
				$("#scrape_button").fadeOut(function() {				 
					$("#scrape_reset").show();
					
					if (self.model.get("import_or_lookup")=="import") {
						if (blnFound) {
							$("#scrape_update_button").show();
							$("#scrape_save_button").hide();
						} else {
							$("#scrape_update_button").hide();
							$("#scrape_save_button").show();
						}
					}
					if (self.model.get("import_or_lookup")=="lookup") {
						$("#scrape_update_button").hide();
						$("#scrape_save_button").hide();
						$("#scrape_adj_number").hide();
						$("#scrape_reset").hide();
						$("#scrape_adj_numberSpan").show();
					}
				});
			}
		});
	},
	doTimeouts: function(event) {
		var scrape_adj_number = document.getElementById("scrape_adj_number");
		
		//if adj number was passed
		if (this.model.get("adj_number") != "") {
			this.scrapeEams(event);			
		} else {
			scrape_adj_number.value = "ADJ";
			scrape_adj_number.select();
			setTimeout(function() {
				scrape_adj_number.focus();
			}, 500);
		}
	}
});
window.eams_applicant_view = Backbone.View.extend({
	render: function () {
		if (typeof this.template != "function") {
			var view = "eams_applicant_view";
			var extension = "php";
			this.model.set("holder", "applicant_scrape_holder");
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var applicant = this.model.toJSON();
		$(this.el).html(this.template(applicant));
        return this;
	}
});
window.eams_bodyparts_view = Backbone.View.extend({
	render: function () {
		if (typeof this.template != "function") {
			var view = "eams_bodyparts_view";
			var extension = "php";
			this.model.set("holder", "bodyparts_scrape_holder");
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		$(this.el).html(this.template({bodyparts: this.collection}));
        return this;
	}
});
function addADJ(event, adj_number) {
	event.preventDefault();
	var formValues = "table_name=injury&id=" + current_injury_id + "&adj_number=" + adj_number;
	
	var url = "api/injury/update";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					$("#add_adj").html("<span class='white_text'>saved&nbsp;<span style='background:green;color:white'>&#10003;</span></span>");
				}
			}
	});
}
function addDOI(doi) {
	doi = doi.replace(" CT", "");
	arrDOI = doi.split(" - ");
	var start_date = arrDOI[0];
	var end_date = "";
	if (arrDOI.length==2) {
		end_date = arrDOI[1];
	}
	var adj_number = $("#scrape_adj_numberSpan").html();
	
	var formValues = "table_name=injury&id=" + current_injury_id + "&adj_number=" + adj_number + "&start_date=" + encodeURIComponent(start_date) + "&end_date=" + encodeURIComponent(end_date);
	
	var url = "api/injury/update";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					$("#add_doi").html("<span class='white_text'>saved&nbsp;<span style='background:green;color:white'>&#10003;</span></span>");
				}
			}
	});
}
function addEAMSPartie(event, partie, partie_address, partie_street, partie_city, partie_state, partie_zip, case_id, iCounter, type) {
	event.preventDefault();

	var formValues = "case_id=" + case_id + "&company_name=" + encodeURIComponent(partie.replaceAll("~", "'"));
	formValues += "&type=" + encodeURIComponent(type);
	formValues += "&street=" + encodeURIComponent(partie_street.replaceAll("~", "'"));
	formValues += "&city=" + encodeURIComponent(partie_city.replaceAll("~", "'"));
	formValues += "&state=" + encodeURIComponent(partie_state);
	formValues += "&zip=" + partie_zip;
	//console.log(formValues);
	
	var url = "api/corporation/import";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					//hide the modal
					$("#add_" + type + "_" + iCounter).html("<span class='white_text'>saved&nbsp;<span style='background:green;color:white'>&#10003;</span></span>");
				}
			}
	});
}
window.eams_parties_view = Backbone.View.extend({
	event: {
		"change .role_choice":			"chooseRole"
	},
	render: function () {
		var self = this;
		var case_id = this.model.get("case_id");
		var employer = "";
		
		if (!isNaN(case_id) && case_id!="") {
			var kase = kases.findWhere({case_id: case_id});
			var mykase = kase.toJSON();
			employer = mykase.employer;
			var doi = mykase.start_date;
			if (doi=="00/00/0000") {
				doi = "";
			}
			this.model.set("doi", doi);
		}
		$(this.el).html(this.template({parties: this.collection, employer: employer, case_id: case_id}));
		
		setTimeout(function() {
			//does the case have a doi
			if (self.model.get("doi")=="") {
				$("#add_doi").css("display", "inline-block");
			}
			
			if (case_id!="") {
				//does the case have an adj
				var kase = kases.findWhere({case_id: case_id});
				if (kase.get("adj_number")=="") {
					$(".adj_eams_holder").css("display", "inline-block");
				}
				//does the case have a carrier
				var kase_parties = new Parties([], { case_id: case_id, panel_title: "Parties" });
				kase_parties.fetch({
					success: function(data) {
						var carrier_partie = kase_parties.findWhere({"type": "carrier"});
						if (typeof carrier_partie == "undefined") {
							//show the add carrier links
							$(".carrier_eams_holder").fadeIn();
						}
						var employer_partie = kase_parties.findWhere({"type": "employer"});
						if (typeof employer_partie == "undefined") {
							//show the add carrier links
							$(".employer_eams_holder").fadeIn();
						}
					}
				});
			}
		}, 1000);
		
        return this;
	},
	chooseRole: function(event) {
		var element = event.currentTarget;
		var arrID = element.split("_");
		var counter = arrID[arrID.length - 1];
		$("#partie_role_" + counter).val(element.val());
	}
});
window.eams_events_view = Backbone.View.extend({
	render: function () {
		$(this.el).html(this.template({occurences: this.collection}));
		
		setTimeout(function() {
			$(".modal-dialog").css("margin-top","-350px");
		}, 500);
        return this;
	}
});
window.eams_hearings_view = Backbone.View.extend({
	render: function () {
		if (typeof this.template != "function") {
			var view = "eams_hearings_view";
			var extension = "php";
				
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		$(this.el).html(this.template({hearings: this.collection}));
        return this;
	}
});
window.eams_previouscases_view = Backbone.View.extend({
	render: function () {
		$(this.el).html(this.template({previous_cases: this.collection}));
        return this;
	}
});