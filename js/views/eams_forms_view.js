window.eams_listing_view = Backbone.View.extend({
    initialize:function () {  
    },
    events:{
       "click .create_eams": 					"newPDF",
    },
    render:function () {
		var self = this;
		
		var case_id = this.model.get("case_id");
		$(this.el).html(this.template({case_id: case_id}));
        return this;
    },
	newPDF: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEams(element.id);
	}
});
window.eams_form_listing = Backbone.View.extend({
    initialize:function () {  
    },
    events:{
		"click .edit_eams": 				"editEAMS",
		"click #composeform":				"newEAMS",
		"click .delete_icon":				"confirmdeleteEAMS",
		"click .delete_yes":				"deleteEAMS",
		"click .delete_no":					"canceldeleteEAMS",
		"keyup .eams_category":				"showSave",
		"click .save":						"saveCategory",
		"click #form_clear_search":			"clearSearch",
		"click #label_search_notes":		"Vivify",
		"click #form_searchList":			"Vivify",
		"focus #form_searchList":			"Vivify",
		"blur #form_searchList":			"unVivify"
    },
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "eams_form_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var self = this;
		
		$(this.el).html(this.template({forms: this.collection.toJSON()}));
        return this;
    },
	unVivify: function(event) {
		var textbox = $("#form_searchList");
		var label = $("#label_search_form");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#form_searchList");
		var label = $("#label_search_form");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#form_searchList').focus();
		}
	},
	clearSearch: function() {
		$("#form_searchList").val("");
		$( "#form_searchList" ).trigger( "keyup" );
	},
	showSave: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var id = element.id.split("_")[2];
		
		$("#eams_category_button_" + id).css("visibility", "visible");
	},
	saveCategory: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var id = element.id.split("_")[3];
		
		$("#eams_category_button_" + id).css("visibility", "hidden");
		
		var self = this;
		var url = 'api/forms/update';
		formValues = "table_name=eams_form&id=" + id + "&categoryInput=" + encodeURIComponent($("#eams_category_" + id).val());
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#eams_category_button_" + id).css("visibility", "visible");
					$("#eams_category_button_" + id).css("background", "green");
					$("#eams_category_button_" + id).html($("#eams_category_button_" + id).html() + "&nbsp;&#10003;");
					
					setTimeout(function() {
						$("#eams_category_button_" + id).css("background", " none");
						var thehtml = $("#eams_category_button_" + id).html();
						thehtml = thehtml.replace("&nbsp;&#10003;", "");
						$("#eams_category_button_" + id).html(thehtml);
					}, 2500);
				}
			}
		});
	},
	editEAMS: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEamsForm(element.id);
	},
	newEAMS: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEamsForm(element.id);
	},
	confirmdeleteEAMS: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element);	
		$("#confirm_delete_form").css({display: "none", top: arrPosition[0] - 50, left: arrPosition[1] + 50, position:'absolute'});
		$("#confirm_delete_form").fadeIn();
		
	},
	canceldeleteEAMS: function(event) {
		event.preventDefault();
		$("#confirm_delete_form").fadeOut();
	},
	deleteEAMS: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "forms");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteEAMS(event);
			$(".form_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				//$(".document_row_" + theid).fadeOut();
				$(".form_row_" + id).fadeOut();
			}, 2500);
		}
	}
});
window.eams_form_view = Backbone.View.extend({
	initialize:function () {
        
    },
	events: {
		"blur #nameInput":						"validEAMSName",
		"click #eams_form_view_done":			"doTimeouts"
	},
	validEAMSName: function(event) {
		//$("#nameInput").val(validEAMSName($("#nameInput").val()));
		$("#nameInput").val($("#nameInput").val().replace(/[^\w\s\][^._-]/gi, ''));
	},
	render: function() {
		var self = this;
		
		try {
			$(self.el).html(self.template(self.model.toJSON()));
		}
		catch(err) {
			var view = "eams_form_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		setTimeout(function() { 
			
		}, 600);
		
		return this;
	},
	doTimeouts: function() {
		var self = this;
		
		$(".eams_form .edit").trigger("click"); 
		$(".eams_form .delete").hide();
		$(".eams_form .reset").hide();
		$('#eams_form_attachments').html(new eams_form_attach({model: self.model}).render().el);
	
		$(".eams_form_attach_form #queue").css("height", "70px");
		$(".eams_form_attach_form #queue").css("width", "550px");
		$(".eams_form_attach_form").css("border","0px #000000 solid");
		$('#eams_form_attachments').fadeIn();
		//is this a document send
		if (self.model.get("document_id") != "" && typeof self.model.get("document_id") != "undefined") {
			//look up the document and get it
			var send_document = new Document({case_id:  self.model.case_id});
			send_document.set("id", self.model.get("document_id"));
			send_document.fetch({
				success: function(data) {
					$("#send_document_id").val(data.get("document_id"));
					$("#send_queue").html(data.get("document_filename"));
				}
			});
		}
	}
});
window.eams_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #form_parties":							"selectAllParties",
		"change #appointment_date":						"setDefaultTime",
		"click #eams_view_all_done":					"doTimeouts",
		"click #view_main": 							"displayMain",
		"click #view_billable":  						"displayBillable",
		"click #cancel_billable":						"cancelBillable"
	},
    render:function () {		
		if (typeof this.template != "function") {
			var view = "eams_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}	
		var self = this;
		//dois
		var arrDOIs = [];
		var kase_dois = dois.where({case_id: this.model.get("case_id")}); 
		var kase = kases.findWhere({case_id: this.model.get("case_id")});
		
		var selected = "";
		//console.log("eams_length 244 "+kase_dois.length);
		if (kase_dois.length==1) {
			selected = " selected";
		}
		_.each(kase_dois , function(doi) { 
			var start_date = doi.get("start_date"); //console.log("kase_start_date => " + start_date);
			if (start_date!="0000-00-00") {
				var thedoi = moment(start_date).format("MM/DD/YYYY");
				if (doi.get("end_date") != "0000-00-00") {
					thedoi += " - " + moment(doi.get("end_date")).format("MM/DD/YYYY") + " CT";
				}
				var theadj = doi.get("adj_number");
				if (theadj!="") {
					thedoi += " :: " + theadj; 
				}
				thedoi = "<option value='" + doi.id + "'" + selected + ">" + thedoi + "</option>";
				console.log("eams 260"+thedoi);
				arrDOIs[arrDOIs.length] = thedoi;
			}
		});
		if (arrDOIs.length > 0) {
			setTimeout(function() {
				$("#show_all_adjs_holder").show();
			}, 987);
		}
		
		var parties = new Parties([], { case_id: kase.get("case_id"), case_uuid: kase.get("uuid") });
		parties.fetch({
			success: function(parties) {
				var carriers = parties.where({"type": "carrier"});
				var arrCarriers = [];
				var arrParties = [];
				var arrExaminers = [];
				var selected = "";
				if (carriers.length==1) {
					selected = " selected";
				}
				_.each(carriers , function(carrier) {
					var thecarrier = carrier.get("company_name");
					var theexaminer = carrier.get("full_name");
					thecarrier = "<option value='" + carrier.get("corporation_id") + "'" + selected + ">" + thecarrier + "</option>";
					theexaminer = "<option value='" + theexaminer + "'" + selected + ">" + theexaminer + "</option>";
					arrCarriers[arrCarriers.length] = thecarrier;
					arrExaminers[arrExaminers.length] = theexaminer;
				});
				
				var employers = parties.where({"type": "employer"});
				var arrEmployers = [];
				var selected = "";
				if (employers.length==1) {
					selected = " selected";
				}
				_.each(employers , function(employer) {
					var theemployer = employer.get("company_name");
					theemployer = "<option value='" + employer.get("corporation_id") + "'" + selected + ">" + theemployer + "</option>";
					arrEmployers[arrEmployers.length] = theemployer;
				});
				
				var defenses = parties.where({"type": "defense"});
				var arrDefenses = [];
				var selected = "";
				if (defenses.length==1) {
					selected = " selected";
				}
				_.each(defenses , function(defense) {
					var thedefense = defense.get("company_name");
					thedefense = "<option value='" + defense.get("corporation_id") + "'" + selected + ">" + thedefense + "</option>";
					arrDefenses[arrDefenses.length] = thedefense;
				});
				
				var referrings = parties.where({"type": "referring"});
				var arrReferrals = [];
				var selected = "";
				if (referrings.length==1) {
					selected = " selected";
				}
				_.each(referrings , function(referring) {
					var thereferring = referring.get("company_name");
					thereferring = "<option value='" + referring.get("corporation_id") + "'" + selected + ">" + thereferring + "</option>";
					arrReferrals[arrReferrals.length] = thereferring;
				});
				
				var medical_providers = parties.where({"type": "medical_provider"});
				var arrMedicalProviders = [];
				var selected = "";
				if (medical_providers.length==1) {
					selected = " selected";
				}
				_.each(medical_providers , function(medical_provider) {
					var themedical_provider = medical_provider.get("company_name");
					themedical_provider = "<option value='" + medical_provider.get("corporation_id") + "'" + selected + ">" + themedical_provider + "</option>";
					arrMedicalProviders[arrMedicalProviders.length] = themedical_provider;
				});
				
				var lien_holders = parties.where({"type": "lien_holder"});
				var arrLienHolders = [];
				var selected = "";
				if (lien_holders.length==1) {
					selected = " selected";
				}
				_.each(lien_holders , function(lien_holder) {
					var thelien_holder = lien_holder.get("company_name");
					thelien_holder = "<option value='" + lien_holder.get("corporation_id") + "'" + selected + ">" + thelien_holder + "</option>";
					arrLienHolders[arrLienHolders.length] = thelien_holder;
				});
				
				var theparties = parties.toJSON();
				_.each(theparties , function(partie) {
					var thepartie = partie.company_name;
					arrParties.push(thepartie);
				});
				self.model.set("dois", arrDOIs.join("\r\n"));
				self.model.set("carriers", arrCarriers.join("\r\n"));
				self.model.set("parties", arrParties.join("\r\n"));
				self.model.set("examiners", arrExaminers.join("\r\n"));
				self.model.set("employers", arrEmployers.join("\r\n"));
				self.model.set("defenses", arrDefenses.join("\r\n"));
				self.model.set("referrals", arrReferrals.join("\r\n"));
				self.model.set("medical_providers", arrMedicalProviders.join("\r\n"));
				self.model.set("lien_holders", arrLienHolders.join("\r\n"));
				
				try {
					$(self.el).html(self.template(self.model.toJSON()));
				}
				catch(err) {
					var view = "eams_view";
					var extension = "php";
					
					loadTemplate(view, extension, self);
					
					return "";
				}
				
				setTimeout(function() {
					//separator is hidden if not needed, required if it is
					if (self.model.get("eams_form_name")!="separator") {
						$("#separator_holder").hide();
					}
					
					/*
					$("#eamsInput").cleditor({
						width:415,
						height: 150,
						controls:     // controls to add to the toolbar
								  "bold italic underline | font size " +
								  "style | color highlight"
					});
					*/
				}, 600);
			}
		});
		
		return this;
    },
	doTimeouts: function(event) {
		var self = this;
		//list parties		
		if ($("#eams_parties_list").length > 0) {
			//hide the partie rows
			//$(".letter .partie_row").hide();
			var kase = kases.findWhere({case_id: self.model.get("case_id")});
			var parties = new Parties([], { case_id: self.model.get("case_id"), panel_title: "Parties" });
			parties.comparator = 'type';
			parties.fetch({
				success: function(data) {
					//add the customer to the list
					parties.add({
						company_name: customer_name,
						type: "in_house",
						address: customer_address,
						partie_id:	0
					}, {at: 0});
					$('#eams_parties_list').html(new partie_listing_choose({collection: parties, model: kase}).render().el);
					//$('.modal-dialog').animate({}, 1100, 'easeInSine');
					$('.modal-dialog').animate({width:1050, marginLeft:"-500px"}, 1100, 'easeInSine', 
					function() {
						//run this after animation
						$('#eams_parties_list_holder').show();
					});
				}
			});
		}
	},
	selectAllParties:function(event) {
		var element = event.currentTarget;
		$(".event_partie").prop("checked", element.checked);
	},
	setDefaultTime: function() {
		if ($("#appointment_time").val()=="") {
			$("#appointment_time").val("08:00:00");
			$("#appointment_time").focus();
		}
	},
	displayMain: function(event){
		if (blnShowBilling) {
			$("#myModalLabel").html(this.model.get("modal_title"));
			
			//hide the button
			$("#view_main").fadeOut(function() {
				$("#view_billable").val("Bill Ready ✓");
				$("#view_billable").fadeIn();
				$("#cancel_billable_holder").css("display", "inline");
			});
			$("#eams_parties_list_holder").show();
			$("#fields_holder").show();
			$("#billing_holder").fadeOut();
			$("#modal_save_holder").fadeIn();
			$('.modal-dialog').animate({width:1050, marginLeft:"-500px"}, 1100, 'easeInSine');
		}
	},
	cancelBillable: function(event){
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		if (blnShowBilling) {
			$("#view_billable").val("Bill This");
			$("#billing_holder").html("");
			$("#cancel_billable_holder").fadeOut();
		}
	},
	displayBillable: function(event){
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		var event_id = $("#table_id").val();
			
		if (blnShowBilling) {
			this.model.set("modal_title", $("#myModalLabel").html());
			$("#eams_parties_list_holder").hide();
			$("#fields_holder").hide();
			$("#myModalLabel").html("Bill this Form");
			$("#cancel_billable_holder").css("display", "none");
			//hide the button
			$("#view_billable").fadeOut(function() {
				$("#view_main").val("Return to Form");
				$("#view_main").fadeIn();
			});
			
			$("#letter_div").fadeOut();
			$('.modal-dialog').animate({width:540, marginLeft:"-230px"}, 700, 'easeInSine');
			$("#billing_holder").fadeIn();
			//$('#billing_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
			$("#modal_save_holder").fadeOut();
			//already in?
			if ($("#billing_holder").html().trim() == "") {
				var bill = new BillingMain({ case_id: current_case_id, action_id: -1, action_type: "forms"});
				bill.set("holder", "billing_holder");
				bill.set("billing_date", moment().format("MM/DD/YYYY"));
				bill.set("activity_category", "Forms");
				bill.set("activity_id", -1);
				bill.set("hours", 0.33);
				
				bill.set("activity", "Form created - " + this.model.get("modal_title"));
				
				$("#billing_holder").html(new activity_bill_view({model: bill}).render().el);
			}
		}
    }
});