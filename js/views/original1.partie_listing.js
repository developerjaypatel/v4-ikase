window.partie_listing_view = Backbone.View.extend({

    initialize:function () {
        this.model.on("change", this.render, this);
		this.model.on("add", this.render, this);
    },
    render:function () {		
		var self = this;
		var parties = this.model.toJSON();
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({parties: parties, case_id: this.model.case_id}));
		
		tableSortIt("partie_listing");
		$("#partie_listing").addClass("glass_header_no_padding");
		
		
		return this;
    }

});
window.employers_report = Backbone.View.extend({
    initialize:function () {
        
    },
    render:function () {	
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "employers_report";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}	
		var self = this;
		var parties = this.collection.toJSON();
		$(this.el).html(this.template({parties: parties}));
		
		return this;
	},
	events: {
		"click #send_xl":		"sendXL"
	},
	sendXL: function(event) {
		event.preventDefault();
		var url = "reports/export_employers.php";
		window.open(url);
	}
});
window.partie_listing_choose = Backbone.View.extend({
    initialize:function () {
        this.model.on("change", this.render, this);
		this.model.on("add", this.render, this);
    },
	events: {
		"click .event_partie":				"selectPartie",
		"click .depo_set":					"depoPartie",
		"click .event_any":					"anyPartie",
		"click .compose_new_envelope":		"newEnvelope",
		"click .compose_pdf_envelope":		"newPDFEnvelope"
	},
    render:function () {		
		var self = this;
		var parties = this.collection.toJSON();
		
		_.each( parties, function(partie) {
			partie.blnSkipPartie = false;
			partie.original_type = partie.type;
			partie.original_id = partie.parties_id;
			
			if ($("#eams_parties_list").length > 0 && partie.type=="venue") {
				partie.blnSkipPartie = true;
			}
		
			var thehref = "#parties/" + partie.case_id + "/" + partie.parties_id + "/" + partie.type;
			if (partie.type=="applicant") { 
				thehref = "#kases/" + partie.case_id;
				
			}
			if (partie.corporation_id==-1) {
				partie.partie_id = "P" + partie.person_id;
			}
			if (partie.person_id==-1) {
				partie.partie_id = "C" + partie.corporation_id;
			}
			partie.href = thehref;
			var arrAddress = new Array();
			if (partie.street!="" && partie.street!=null) {
				arrAddress[arrAddress.length] = partie.street;
				//address = partie.street;            
				if (partie.city!="") {
					arrAddress[arrAddress.length] = "<br>" + partie.city;
				}
				if (partie.state!="") {
					arrAddress[arrAddress.length] = partie.state;
				}
				if (partie.zip!="") {
					arrAddress[arrAddress.length] = partie.zip;
				}
			}
			
			//clean up
			partie.type = partie.type.replaceAll("_", " ").capitalizeWords();
			partie.address = arrAddress.join(", ");
		});
		
		$(this.el).html(this.template({parties: parties, letter:this.model.toJSON()}));
		
		return this;
    },
	newEnvelope: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		element_id = element_id.replace("C", "");
		element_id = element_id.replace("P", "");
		
		generateEnvelope(element_id, "html");
	},
	newPDFEnvelope: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		element_id = element_id.replace("C", "");
		element_id = element_id.replace("P", "");
		
		generateEnvelope(element_id, "pdf");
	},
	anyPartie: function (event) {
		var element = event.currentTarget;
		if (element.checked) {
			$("#" + element.id.replace("any", "partie")).prop("checked", false);
		}
		
		//any to:
		//go through the list, find the checked ones
		var checked_parties = "";
		var parties = $(".event_any");
		var array_length = parties.length;
		var arrAnyID = [];
		var arrAnys = [];
		for(var i = 0; i < array_length; i++) {
			var partie = parties[i];
			var id = partie.id.split("_")[2];
			var check = document.getElementById("event_any_" + id);
			if (check.checked) {
				arrAnyID.push(id);
				
				var event_partie_name = document.getElementById("event_partie_name_" + id).innerHTML;
				event_partie_name += " - " + document.getElementById("event_partie_type_" + id).innerHTML;
				arrAnys.push(event_partie_name);
			}
		}
		$("#any_list").html("<div><div style='display:inline-block; font-weight:bold; width:117px'>To:</div><div style='display:inline-block; vertical-align:top'>" + arrAnys.join("<br />")) + "</div></div>";
		$("#any_ids").val(arrAnyID.join("|"));
	},
	depoPartie: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[arrID.length - 1];
		
		var event_partie_name = $("#event_partie_name_" + id).html().trim();
		var event_partie_address = $("#event_partie_address_" + id).html().trim().replaceTout("<br>", "\r\n");
		
		$("#depo_location").val(event_partie_name);
		$("#depo_address").val(event_partie_address);
	},
	selectPartie: function (event) {
		var element = event.currentTarget;
		if (element.checked) {
			$("#" + element.id.replace("partie", "any")).prop("checked", false);
		}
		//go through the list, find the checked ones
		var checked_parties = "";
		var parties = $(".event_partie");
		var array_length = parties.length;
		var arrParties = [];
		var arrPartieID = [];
		for(var i = 0; i < array_length; i++) {
			var partie = parties[i];
			var id = partie.id.split("_")[2];
			var check = document.getElementById("event_partie_" + id);
			if (check.checked) {
				var event_partie_name = document.getElementById("event_partie_name_" + id).innerHTML;
				event_partie_name += " - " + document.getElementById("event_partie_type_" + id).innerHTML;
				arrParties.push(event_partie_name);
				
				arrPartieID.push(id);
			}
		}
		$("#parties_list").html("<div><div style='display:inline-block; font-weight:bold; width:117px'>Parties:</div><div style='display:inline-block; vertical-align:top'>" + arrParties.join("<br />")) + "</div></div>";
		$("#partie_ids").val(arrPartieID.join("|"));
	}
});
window.partie_listing_event = Backbone.View.extend({
    initialize:function () {
        this.model.on("change", this.render, this);
		this.model.on("add", this.render, this);
    },
    render:function () {		
		var self = this;
		var parties = this.collection.toJSON();
		
		_.each( parties, function(partie) {
        	var thehref = "#parties/" + partie.case_id + "/" + partie.parties_id + "/" + partie.type;
            if (partie.type=="applicant") { 
                thehref = "#kases/" + partie.case_id;
				
            }
			if (partie.corporation_id==-1) {
				partie.partie_id = "P" + partie.person_id;
			}
			if (partie.person_id==-1) {
				partie.partie_id = "C" + partie.corporation_id;
			}
			partie.href = thehref;
            var arrAddress = new Array();
            if (partie.street!="" && partie.street!=null) {
            	arrAddress[arrAddress.length] = partie.street;
                //address = partie.street;            
				if (partie.city!="") {
					arrAddress[arrAddress.length] = "<br>" + partie.city;
				}
				if (partie.state!="") {
					arrAddress[arrAddress.length] = partie.state;
				}
				if (partie.zip!="") {
					arrAddress[arrAddress.length] = partie.zip;
				}
			}
			partie.address = arrAddress.join(", ");
		});
		
		try {
			$(this.el).html(this.template({parties: parties, list_title: self.model.get("list_title")}));
		}
		catch(err) {
			var view = "partie_listing_event";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
    },
	events: {
		"click .event_partie":		"selectPartie"
	},
	selectPartie: function (event) {
		var self = this;
		var element = event.currentTarget;
		var theid = element.id.split("_")[2];
		
		var thename = $("#event_partie_name_" + theid).html();
		thename = thename.split("<br>")[0].trim();
		var theaddress = $("#event_partie_address_" + theid).html();
		theaddress = theaddress.replace("<br>", "").trim();
		var event_partie_phone = $("#event_partie_phone_" + theid).html();
		if (self.model.get("event_kind")=="phone_call") {
			$("#description_holder").html("");
			var thedescription = '<textarea name="event_descriptionInput" id="event_descriptionInput" class="modalInput event input_class">' + thename + '<br />' + theaddress;
			if (event_partie_phone!="") {
				thedescription += '<br />Office Phone:&nbsp;' + event_partie_phone;
			}
			thedescription += '</textarea>';
			$("#description_holder").html(thedescription);
			
			$("#event_descriptionInput").cleditor({
				width:540,
				height: 130,
				controls:     // controls to add to the toolbar
						  "bold italic underline | font size " +
						  "style | color highlight"
			});
		}
		//$(".iframe_bulk").html(thename + " - " + theaddress);
		$(".event #full_addressInput").val(thename + " - " + theaddress);
		
		$(".event #full_addressInput").animate(
			{width:445}, 
			700, 
			'easeInSine', 
			function() {
				$('#google_map').css("opacity", 1);
			}
		);
		//in house?
		if (theid == 0) {
			var thecalendar = customer_calendars.findWhere({sort_order: "1"});
			var calendar_id = -1;
			if (typeof thecalendar != "undefined") {
				calendar_id = thecalendar.get("calendar_id");
			}
			if (calendar_id > 0) {
				$(".event #calendar_id").val(calendar_id);
			}
		}
	}
});
window.prior_treatment_listing_view = Backbone.View.extend({

    initialize:function () {
        //this.collection.on("change", this.render, this);
		//this.collection.on("add", this.render, this);
    },
	events: {
		"click .delete_prior_medical": 			"confirmdeleteMessage"
	},
    render:function () {		
		var self = this;
		var parties = this.collection.toJSON();
		var case_id = this.model.get("case_id");
		var person_id = this.model.get("person_id");
		_.each( parties, function(partie) {
			var thehref = "#prior_treatment/" + case_id + "/" + partie.id + "/" + person_id;

			partie.thehref = thehref;
            var arrAddress = new Array();
            if (partie.street!="") {
            	arrAddress[arrAddress.length] = partie.street;
                //address = partie.street;
            }
            if (partie.city!="") {
            	arrAddress[arrAddress.length] = partie.city;
            }
            if (partie.state!="") {
            	arrAddress[arrAddress.length] = partie.state;
            }
			partie.address = arrAddress.join(", ");
			
			//copying instructions
			var arrCopying = partie.copying_instructions.split("|");
			
			partie.records_requested = arrCopying[0];
			partie.other_description = arrCopying[1];
			if (arrCopying[2]=="Y") {
				partie.any_all = "Any and All";
			} else {
				partie.any_all = "";
			}
			
			partie.special_instructions = arrCopying[3];
		});
		//this.model.bind("reset", this.render, this);
		
		try {
			$(this.el).html(this.template({parties: parties, person_id: person_id}));
		}
		catch(err) {
			var view = "prior_treatment_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		tableSortIt("prior_treatment_listing");
		$("#partie_listing").addClass("glass_header_no_padding");
		
		
		return this;
    },
	confirmdeleteMessage: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		composeDelete(id, "prior_medical");
	}
});
