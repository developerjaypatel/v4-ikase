window.search_qme = Backbone.View.extend({
    initialize:function () {
        
    },

    events:{
       "click #qme_button":			"searchQME"
    },
    render:function () {
		var self = this;
        try {
			var zip = this.model.get("zip");
			if (zip == "new") {
				this.model.set("zip", "");
			}
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "search_qme";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
    },
	searchQME: function(event) {
		var scode = $("#scode").val();
		var radius = $("#radius").val();
		var zip = $(".qme #zip").val();
		zip = zip.trim();
		
		var scrape = new QMECollection({scode: scode, radius: radius, zip: zip});
		$("#qme_list").html("<table align='center'><tr><td align='center' style=''><i class='icon-spin4 animate-spin' style='font-size:6em; color:white'></i><td><tr></table>");
		scrape.fetch({
			error: function (collection, response, options) {
                // you can pass additional options to the event you trigger here as well
                //self.trigger('errorOnFetch');
				$('#qme_list').html("Connection error.");
				return;
            },
			success: function (data) {
				var mymodel = new Backbone.Model();
				mymodel.set("holder", "qme_list");
				$('#qme_list').html(new search_qme_list({collection: data, model: mymodel}).render().el);
				
			}
		});
	}
});
window.search_qme_list = Backbone.View.extend({
    initialize:function () {
        
    },

    events:{
       "click .qme_name":				"selectEAMS"
    },
    render:function () {
		var self = this;
		var qmes = this.collection.toJSON();
		try {
			$(this.el).html(this.template({qmes: qmes}));
		}
		catch(err) {
			var view = "search_qme_list";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	addEAMS: function(corporation_id, name, address, phone) {
		var url = "api/corporation/add";
		var formValues = "case_id=" + current_case_id + "&corporation_id=" + corporation_id + "&company_name=" + name + "&phone=" + phone;
		formValues += "&full_address=" + address;
		formValues += "&type=medical_provider&street=&city=&state=&zip=&adhoc_fields=doctor_type&doctor_type=EAMS";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
				} else {
					document.location.href = "#parties/" + current_case_id + "/" + data.id + "/medical_provider";
					return;
				}
			}
		});
	},
	selectEAMS: function(event) {
		if (current_case_id=="") {
			return false;
		}
		var self = this;
		var element = event.target;
		var element_id = element.id;
		var arrID = element_id.split("_");
		var thenumb = arrID[arrID.length - 1];
		
		var name = element.innerHTML;
		name = name.replaceAll("&nbsp;", " ");
		var address = $("#address_" + thenumb).html();
		address = removeHtml(address).trim().replaceAll("&nbsp;", " ");
		var phone = $("#phone_" + thenumb).html();
		
		var url = "api/qme_check";
		var formValues = "name=" + name + "&phone=" + phone;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
				} else {
					var corporation_id = "";
					if (typeof data[0] != "undefined") {
						corporation_id = data[0].corporation_id;
					}
					self.addEAMS(corporation_id, name, address, phone);
				}
			}
		});
		//search qme first
		//next add qme to corps with proper id
		//then open the screen with the id
		
		/*
		var corporation = new Corporation({id: -1, case_id: current_case_id, type: "medical_provider"});
		corporation.set("corporation_id", -1);
		corporation.set("company_name", name);
		corporation.set("full_name", name);
		corporation.set("full_address", address);
		corporation.set("employee_phone", phone);
		corporation.set("partie", "Medical_provider");
		corporation.set("gridster_me", true);
		var adhocs = new AdhocCollection([], {case_id: current_case_id, corporation_id: -1});
		//now pass this to the new partie screen
		$("#kase_content").html(new partie_view({model: corporation, collection: adhocs}).render().el);
		*/
	}
});
var search_eams_injury_id = "";
window.eams_firms_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
    events:{
		"keyup #eams_firms_searchList":				"scheduleEAMSFirmSearch",
		"click #eams_firms_listing_all_done":		"doTimeouts"
	},
    render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "eams_firms_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		var eams_firms = self.collection.toJSON();
		try {
			$(self.el).html(self.template({eams_firms: eams_firms}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		return this;
    },
	scheduleEAMSFirmSearch: function() {
		clearTimeout(search_eams_injury_id);
		
		var self = this;
		var search_term = $("#eams_firms_searchList").val();
		if (search_term.length < 3) {
			return;
		}
		
		search_eams_injury_id = setTimeout(function() {
			self.doEAMSFirmSearch(search_term);
		}, 999);
	},
	doEAMSFirmSearch: function(search_term) {
		document.location.href = "#search_eams_firms/" + encodeURIComponent(search_term);
	},
	doTimeouts: function() {
		document.getElementById("eams_firms_searchList").focus();
	}
});

window.search_eams = Backbone.View.extend({
    initialize:function () {
        
    },

    events:{
		"keyup .eams_required":			"releaseSave",
		"click .retrieve_adj":			"scrapeInjury",
		"click .import_adj":			"importADJ",
		"click .kase_adj_number":		"lookupADJ"	,
       	"click #eams_button":			"searchEAMS"
    },
    render:function () {
		var self = this;
		
		var self = this;
		if (typeof this.template != "function") {
			var view = "search_eams";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		if (typeof this.model.get("injury_id")=="undefined") {
			this.model.set("injury_id", "");
		}
		search_eams_injury_id = this.model.get("injury_id");
		if (typeof this.model.get("first_name")=="undefined") {
			this.model.set("first_name", "");
			this.model.set("last_name", "");
			this.model.set("dob", "");
			this.model.set("applicant_full_address", "");
			this.model.set("start_date", "");
			this.model.set("end_date", "");
		}
		
		if (typeof this.model.get("app_dob") == "undefined") {
			this.model.set("app_dob", "");
		}
		
		if (this.model.get("dob")=="" && this.model.get("app_dob")!="") {
			this.model.set("dob", this.model.get("app_dob"));
		}
        try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		setTimeout(function() {
			$(".eams_required").trigger("keyup");
			
			if (!$("#eams_button").prop("disabled")) {
				$("#eams_button").trigger("click");
			}
		}, 1000);
		
        return this;
    },
	releaseSave: function(event) {
		var first_name = $(".eams #first_name").val();
		var last_name = $(".eams #last_name").val();
		
		var blnOK = (first_name != "" && last_name != "");
		if (blnOK){
			$("#eams_button").val("Search");
		} else {
			$("#eams_button").val("Must Fill Required");
		}
		$("#eams_button").prop("disabled", !blnOK);
		
	},
	searchEAMS: function(event) {
		var self = this;
		var first_name = $(".eams #first_name").val();
		var last_name = $(".eams #last_name").val();
		var dob = $(".eams #dob").val();
		$("#eams_list").html("<table align='center'><tr><td align='center' style=''><i class='icon-spin4 animate-spin' style='font-size:6em; color:white'></i><td><tr></table>");
		
		var url = "api/scrape/search";
		var formValues = "first_name=" + first_name + "&last_name=" + last_name + "&dob=" + dob;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					//$("#eams_search_not_found").fadeIn();
					$("#eams_list").html('<div id="eams_search_not_found" style="display:; padding-top:10px; color:black; background:orange; padding:2px">There are no matching records in EAMS</div>');
					setTimeout(function() {
						$("#eams_list").html("");
					}, 2500)
				} else {
					var scrape = new EAMSCollection();
					var mymodel = new Backbone.Model();
					if (typeof data.results != "undefined") {
						scrape.reset(data.results);
						mymodel.set("results", true);
					} else {
						var arrLength = data.length;
						var arrPartyID =  [];
						for (var i = 0; i < arrLength; i++) {
							arrPartyID.push(data[i].party_id);
						}
						self.model.set("party_ids", arrPartyID);
						
						self.scrapePartyIDs();
						return;
						
						scrape.reset(data);
						mymodel.set("results", false);
					}
					if (self.model.get("first_name")== "") {
						mymodel.set("search_case_id", "");
						mymodel.set("injury_id", "");
					} else {
						mymodel.set("search_case_id", self.model.get("case_id"));
						mymodel.set("injury_id", self.model.get("id"));
						mymodel.set("start_date", self.model.get("start_date"));
						mymodel.set("end_date", self.model.get("end_date"));
					}
					mymodel.set("holder", "eams_list");
					$('#eams_list').html(new search_eams_list({collection: scrape, model: mymodel}).render().el);
				}
			}
		});
	},
	scrapePartyIDs: function() {
		var self = this;
		var mymodel = this.model;
		$("#eams_list").html('<div id="content" class="col-md-12 kase_content" style="margin-top: 65px;"><div><div class="eams white_text glass_header_no_padding" style="border:1px solid white; padding:15px; margin-top:-50px; margin-left:-10px;" id="eams_results_listing"></div></div>');
		$("#eams_results_listing").append("<table width='750px'><tr><th align='left' valign='top' style='font-size:1em; width:190px; text-align:left; padding-right:10px' nowrap>ADJ</th><th align='left' valign='top' style='font-size:1em; width:190px; text-align:left; padding-right:10px' nowrap>Employer</th><th align='left' valign='top' nowrap>DOI</th></tr></table>");
		var arrPartyID = self.model.get("party_ids");
		
		var start_date = self.model.get("start_date");
		var end_date = self.model.get("end_date");
		
		//if (!this.model.get("results")) {
		arrPartyID.forEach(function(currentValue, index, array) {
			var party_id = currentValue;
			
			var first_name = $(".eams #first_name").val();
			var last_name = $(".eams #last_name").val();
			var dob = $(".eams #dob").val();
			
			
			var url = "api/scrape/search";
			var formValues = "first_name=" + first_name + "&last_name=" + last_name + "&dob=" + dob + "&party_id=" + party_id;
			
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
					} else {
						var scrape = new EAMSCollection();
						var mymodel = new Backbone.Model();
						if (typeof data.results != "undefined") {
							var arrLength = data.results.length;
							var arrRows = [];
							for(var i = 0; i < arrLength; i++) {
								var result = data.results[i];
								var doi_color = "";
								var doi = start_date;
								if (end_date!="" && end_date!="00/00/0000") {
									doi += " - " + end_date;
								}
								if (doi==result.doi) {
									doi_color = "green";
								}
								var display_update = "inline-block";
								if (search_eams_injury_id=="") {
									display_update = "none";
								}
								
								//for now
								display_update = "none";
								//&nbsp;|&nbsp;<a id='eams_import_" + result.adj_number + "' title='Click to import a new Kase from EAMS' style='cursor:pointer; text-decoration:underline' class='white_text import_adj'>Import</a>
								var row = "<tr><td align='left' valign='top' style='font-size:1em; width:340px; text-align:left; padding:right:10px; border-right:1px solid white' nowrap><span id='adj_number_" + result.adj_number + "' style='width:100px;display:inline-block; text-decoration:underline; cursor:pointer' class='kase_adj_number'>" + result.adj_number + "</span><span style='display:" + display_update + "'>&nbsp;|&nbsp;<a id='eams_link_" + result.adj_number + "' title='Click to import EAMS Injury Info' style='cursor:pointer; text-decoration:underline; display:inline-block' class='white_text retrieve_adj'>Update&nbsp;ADJ</a></span></td><td align='left' valign='top' style='font-size:1em; width:290px; text-align:left; padding-left:5px; padding-right:10px; border-right:1px solid white' nowrap>" + result.employer + "</td><td align='left' valign='top' style='padding-left:5px' nowrap><span style='background:" + doi_color + "; padding:2px;'>" + result.doi + "</span></td></tr>";
								arrRows.push(row);
							}
							$("#eams_results_listing").append("<table width='750px'>" + arrRows.join("") + "</table>");
						}
					}
				}
			});
		});
	},
	lookupADJ: function(event) {
		var element = event.currentTarget;
		var adj_number = element.innerHTML;
		current_injury_id = this.model.get("injury_id");
		composeEamsImport(adj_number, "lookup", this.model.get("case_id"));
	},
	importADJ: function(event) {
		var self = this;
		var element = event.target;
		var element_id = element.id;
		var arrID = element_id.split("_");
		
		var adj_number = $("#adj_number_" + arrID[arrID.length-1]).html();
		adj_number = adj_number.trim();
		
		composeEamsImport(adj_number, "import", this.model.get("case_id"))
	},
	scrapeInjury: function() {
		var self = this;
		var element = event.target;
		var element_id = element.id;
		var arrID = element_id.split("_");
		var party_id = arrID[arrID.length - 1];
			
		var adj_number = document.getElementById("adj_number_" + party_id).innerHTML;
		adj_number = adj_number.trim();
		var injury_id = this.model.get("id");
		
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
								//$("#" + element.id).html("<span style='background:#3F9; color:white'>&#10003;</span>&nbsp;|&nbsp;<a id='eams_import_" + party_id + "' title='Click to import a new Kase from EAMS' style='cursor:pointer; text-decoration:underline' class='white_text import_adj'>Import</a>");
								if (document.getElementById(element.id)!=null) {
									document.getElementById(element.id).outerHTML = "<span style='background:#3F9; color:white'>&#10003;</span>&nbsp;|&nbsp;<a id='eams_import_" + party_id + "' title='Click to import a new Kase from EAMS' style='cursor:pointer; text-decoration:underline' class='white_text import_adj'>Import</a>";
								}
								var injury = new Injury({case_id: self.model.get("case_id")});
								if (self.model.get("injury_id") < 0) {
									var injury_id = data.id;
									self.model.set("injury_id", injury_id);
									self.scrapeBodyParts(injury_id, scrape);
								} 
							}
						}
				});
				if (injury_id > 0) {
					//body parts
					self.scrapeBodyParts(injury_id, scrape);
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
						setTimeout(function() {
							//window.close();
						}, 2500);
					}
				}
		});
	}
});
window.search_eams_list = Backbone.View.extend({
    initialize:function () {
        
    },

    events:{
       "click .import_eams_fromlist":				"selectEAMS",
	   "click .import_adj_fromlist":				"importADJFromList",
	   "click .retrieve_adj_fromlist":				"scrapeInjuryFromList",
	   "click .kase_adj_number_fromlist":					"lookupADJFromList"				
    },
    render:function () {
		var self = this;
		var eamss = this.collection.toJSON();
		var results = this.model.get("results");
		var search_case_id = this.model.get("search_case_id");
		var injury_id = this.model.get("injury_id");
		var start_date = this.model.get("start_date");
		var end_date = this.model.get("end_date");
		try {
			$(this.el).html(this.template({eamss: eamss, results: results, search_case_id: search_case_id, injury_id: injury_id, start_date: start_date, end_date: end_date}));
		}
		catch(err) {
			var view = "search_eams_list";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	lookupADJFromList: function(event) {
		var element = event.currentTarget;
		var adj_number = element.innerHTML;
		current_injury_id = this.model.get("injury_id");
		composeEamsImport(adj_number, "lookup", this.model.get("search_case_id"));
	},
	scrapeInjuryFromList: function() {
		var self = this;
		var element = event.target;
		var element_id = element.id;
		var arrID = element_id.split("_");
		var party_id = arrID[arrID.length - 1];
			
		var adj_number = document.getElementById("adj_number_" + party_id).innerHTML;
		adj_number = adj_number.trim();
		var injury_id = this.model.get("injury_id");
		
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
								//$("#" + element.id).html("<span style='background:#3F9; color:white'>&#10003;</span>&nbsp;|&nbsp;<a id='eams_import_" + party_id + "' title='Click to import a new Kase from EAMS' style='cursor:pointer; text-decoration:underline' class='white_text import_adj'>Import</a>");
								document.getElementById(element.id).outerHTML = "<span style='background:#3F9; color:white'>&#10003;</span>&nbsp;|&nbsp;<a id='eams_import_" + party_id + "' title='Click to import a new Kase from EAMS' style='cursor:pointer; text-decoration:underline' class='white_text import_adj'>Import</a>";
								var injury = new Injury({case_id: self.model.get("case_id")});
								if (self.model.get("injury_id") < 0) {
									var injury_id = data.id;
									self.model.set("injury_id", injury_id);
									self.scrapeBodyParts(injury_id, scrape);
								} 
							}
						}
				});
				if (injury_id > 0) {
					//body parts
					self.scrapeBodyParts(injury_id, scrape);
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
						setTimeout(function() {
							//window.close();
						}, 2500);
					}
				}
		});
	},
	importADJFromList: function(event) {
		var self = this;
		var element = event.target;
		var element_id = element.id;
		var arrID = element_id.split("_");
		
		var adj_number = $("#adj_number_" + arrID[arrID.length-1]).html();
		adj_number = adj_number.trim();
		
		composeEamsImport(adj_number, "import")
	},
	selectEAMS: function(event) {
		var self = this;
		var element = event.target;
		var element_id = element.id;
		var arrID = element_id.split("_");
		
		//if (this.model.get("search_case_id")=="") {
		if (!this.model.get("results")) {
			var party_id = arrID[arrID.length - 1];
			
			var first_name = $(".eams #first_name").val();
			var last_name = $(".eams #last_name").val();
			var dob = $(".eams #dob").val();
			
			
			var url = "api/scrape/search";
			var formValues = "first_name=" + first_name + "&last_name=" + last_name + "&dob=" + dob + "&party_id=" + party_id;
			
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
					} else {
						var scrape = new EAMSCollection();
						var mymodel = new Backbone.Model();
						if (typeof data.results != "undefined") {
							scrape.reset(data.results);
							mymodel.set("results", true);
						} else {
							scrape.reset(data);
							mymodel.set("results", false);
						}
						mymodel.set("search_case_id", self.model.get("search_case_id"));
						mymodel.set("injury_id", self.model.get("injury_id"));
						mymodel.set("start_date", self.model.get("start_date"));
						mymodel.set("end_date", self.model.get("end_date"));
						mymodel.set("holder", "eams_list");
						$('#eams_list').html(new search_eams_list({collection: scrape, model: mymodel}).render().el);
					}
				}
			});
		} else {
			this.scrapeInjuryFromList(adj_number);
		}
	}
});