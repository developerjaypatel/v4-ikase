var confirm_timeout_id = false;
window.letter_listing_view = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
	events: {
		"change #typeFilter":						"filterLetter",
		"click .create_letter":						"newLetter",
		"click .delete_letter":						"confirmdeleteLetter",
		"click .delete_yes":						"deleteLetter",
		"click .delete_no":							"canceldeleteLetter",
		"keyup #letter_searchList":					"findIt",
		"click #show_generated":					"showGenerated",
		"click #show_all":							"showAll",
		"click #letter_clear_search":				"clearSearch",
		"click #label_search_template":				"Vivify",
		"click #letter_searchList":					"Vivify",
		"focus #letter_searchList":					"Vivify",
		"blur #letter_searchList":					"unVivify"
	},
    render:function () {		
		var self = this;
		
		var case_id = this.model.get("case_id");
		var kase = kases.findWhere({case_id: case_id});
		var case_type = kase.case_type;
		var templates = this.collection.toJSON();
		var arrID = [];
		_.each( templates, function(template) {
			if (inArray(template.document_id, arrID)) {
            	blnSkip = true;
          	} else {
            	arrID[arrID.length] = template.document_id;
            	blnSkip = false;
            }
			template.skip_me = blnSkip;
			if (blnSkip) {
				self.collection.remove(template);
			}
		});
		var templates = this.collection.toJSON();
		_.each( templates, function(template) {
            if (template.document_names != null && template.document_names!="") {
				//don't show letter if it's itself
				if (template.document_uuid == template.document_uuids) {
					template.document_names = null;
					template.document_dates = null;
					template.document_users = null;
					template.document_macros = null;
					template.document_uuids = null;
				}
				
				//split the names, dates, uuids
				var arrDocuments = [];
				if (template.document_names != null) {
					var arrDocuments = template.document_names.split("|");
				}
				var arrDocumentDates = [];
				if (template.document_dates != null) {
					var arrDocumentDates = template.document_dates.split("|");
				}
				var arrDocumentUUIDs = [];
				if (template.document_uuids != null) {
					var arrDocumentUUIDs = template.document_uuids.split("|");
				}
				
				var arrDocumentUsers = [];
				if (template.document_users != null) {
					var arrDocumentUsers = template.document_users.split("|");
				} 
				 
				var arrLinks = [];
				//_.each( arrDocuments, function(letter) {
				arrayLength = arrDocuments.length;
				for (var i = 0; i < arrayLength; i++) {
					var letter = arrDocuments[i];
					var document_date = moment(arrDocumentDates[i]).format("MM/DD/YY hh:mmA");
					var theuser = arrDocumentUsers[i];
					if (typeof theuser == "undefined") {
						theuser = "";
					} else {
						theuser = "&nbsp;(" + theuser + ")";
					}
					
					var arrLetter = letter.split("/");
					var letter_link = "<a href='" + letter + ".docx' title='Click to open letter' class='white_text' target='_blank'>" + document_date + "</a>" + theuser;
					
					letter_link += "<br><a href='api/download.php?file=" + letter + ".docx' title='Click to download letter' class='white_text' target='_blank'>download</a>"
					
					//arrLetter[arrLetter.length - 1]
					var buttons = "&nbsp;&nbsp;<i class='glyphicon glyphicon-pencil' style='color:#FF0000;'>&nbsp;</i>&nbsp;&nbsp;<i class='glyphicon glyphicon-trash' style='color:#FF0000;'>&nbsp;</i>";
					letter_link = letter_link + " " + buttons;
					arrLinks[i] = letter_link;
				}
				//put it all together
				
				template.document_names = arrLinks.join("<br>");
				
			} else {
				template.document_names = "";
			}
			template.delete_link = "";
			if (self.model.get("no_uploads")) {
				template.delete_link = "<a>del</a>";
			}
			if (template.source=="Y") {
				template.source = "";
			}
			if (template.source=="no_lettehead") {
				template.source = "No Letterhead";
			}
		});
		try {
			$(this.el).html(this.template({templates: templates, case_id: case_id}));
		}
		catch(err) {
			var view = "letter_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		tableSortIt("letter_listing");
		
		return this;
    },
	unVivify: function(event) {
		var textbox = $("#letter_searchList");
		var label = $("#label_search_template");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#letter_searchList");
		var label = $("#label_search_template");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'letter_listing', 'letter');
	},
	clearSearch: function() {
		$("#letter_searchList").val("");
		$( "#letter_searchList" ).trigger( "keyup" );
	},
	showGenerated: function(event) {
		event.preventDefault();
		var $rows = $('.letter_listing .letter_data_row');
		$rows.show().filter(function() {
			var text = $( '.letter_generated_cell', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
			return (text.trim()=="");
		}).hide();
		$("#show_generated").fadeOut(function() {
			$("#show_all").fadeIn();
		});
	},
	showAll: function(event) {
		event.preventDefault();
		var $rows = $('.letter_listing .letter_data_row');
		$rows.show();
		$("#show_all").fadeOut(function() {
			$("#show_generated").fadeIn();
		});
	},
	newLetter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeLetter(element.id);
	},
	confirmdeleteLetter: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "letter");
		
	},
	canceldeleteLetter: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteLetter: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "document");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteLetter(event);
			$(".letter_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				//$(".document_row_" + theid).fadeOut();
				$(".letter_row_" + $("#confirm_delete_id").val()).fadeOut();
			}, 2500);
		}
	},
	filterLetter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		filterIt(element, "letter_listing", "letter");
	}
});
window.kase_letter_listing_view = Backbone.View.extend({
    initialize:function () {
        /*
		this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
		*/
		//just in case the ftp update was not done right for updated letters in fromclient folder
		var url = "api/documents/processftp?customer_id=" + customer_id;
		var formValues = "";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					//console.log("ftp processed");
				}
			}
		});
    },
	events: {
		"change #typeFilter":						"filterLetter",
		"click .create_letter":						"newLetter",
		"click .delete_letter":						"confirmdeleteLetter",
		"click .delete_yes":						"deleteLetter",
		"click .delete_no":							"canceldeleteLetter",
		"keyup #letter_searchList":					"findIt",
		"click .send_icon":							"sendLetter",
		"click #show_generated":					"showGenerated",
		"click #show_all":							"showAll",
		"click #letter_clear_search":				"clearSearch",
		"click #label_search_letter":				"Vivify",
		"click #letter_searchList":					"Vivify",
		"focus #letter_searchList":					"Vivify",
		"blur #letter_searchList":					"unVivify"
	},
	unVivify: function(event) {
		var textbox = $("#letter_searchList");
		var label = $("#label_search_letter");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#letter_searchList");
		var label = $("#label_search_letter");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'letter_listing', 'letter');
	},
	showGenerated: function(event) {
		event.preventDefault();
		var $rows = $('.letter_listing .letter_data_row');
		$rows.show().filter(function() {
			var text = $( '.letter_generated_cell', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
			return (text.trim()=="");
		}).hide();
		$("#show_generated").fadeOut(function() {
			$("#show_all").fadeIn();
		});
	},
	showAll: function(event) {
		event.preventDefault();
		var $rows = $('.letter_listing .letter_data_row');
		$rows.show();
		$("#show_all").fadeOut(function() {
			$("#show_generated").fadeIn();
		});
	},
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "kase_letter_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}		
		var self = this;
		var case_id = this.model.get("case_id");
		//kase = new Kase({ case_id : case_id });
		var kases = new KaseCollection({ case_id : case_id });
		var kase = kases.findWhere({case_id: case_id});
		//alert(kase.toJSON().case_type);
		//alert(kase.toJSON().case_name);
		var case_type = "";
		if (customer_id == 1109 && case_id == 11) {
			case_type = "PI";
		} else { 
			case_type = kase.toJSON().case_type;
		}
		//alert(kase.toJSON().case_type);
		//alert(kase.toJSON().case_name);
		
		var templates = this.collection.toJSON();
		var arrID = [];
		_.each( templates, function(template) {
			if (inArray(template.document_id, arrID)) {
            	blnSkip = true;
          	} else {
            	arrID[arrID.length] = template.document_id;
            	blnSkip = false;
            }
			//alert(case_type);
			//alert(template.description_html);
            if (case_type == "PI" && template.description_html == "WCAB") {
				blnSkip = true;
			}
			template.skip_me = blnSkip;
			if (blnSkip) {
				self.collection.remove(template);
			}
		});
		var templates = this.collection.toJSON();
		_.each( templates, function(template) {
			//get the letters for the case
			var the_letters = kase_letters.where({parent_document_uuid: template.document_uuid});
			if (the_letters.length > 0) {
				var arrKaseLetters = [];
				var arrKaseLetterDates = [];
				var arrKaseLetterSubjects = [];
				var arrKaseLetterIDs = [];
				var arrKaseLetterUUIDs = [];
				var arrKaseLetterUsers = [];
				var arrKaseLetterMacros = [];
				var subject = "";
				_.each( the_letters, function(a_letter) {
					arrKaseLetters[arrKaseLetters.length] = a_letter.get("document_filename");
					arrKaseLetterDates[arrKaseLetterDates.length] = a_letter.get("document_date");
					var subject = "";
					if (a_letter.get("document_filename")!=a_letter.get("document_name")) {
						subject = a_letter.get("document_name");
					}
					var macro_update = "";
					if (a_letter.get("macro_updates")>0) {
						macro_update = "&nbsp;<span title='Updated via Word on " + moment(a_letter.get("macro_dates")).format("MM/DD/YY h:mma") + "'><i style='font-size:10px; color:pink; cursor:pointer' class='glyphicon glyphicon-asterisk'></i></span>";
					}
					
					if (a_letter.get("description")!="") {
						var description = a_letter.get("description");
						if (description.indexOf("{") > -1) {
							var jdata = JSON.parse(description);
							if (typeof jdata.doi != "undefined") {
								macro_update += "<br>";
								macro_update += "<span style='background:black'>DOI:" + jdata.doi + "</span>";
							}
						}
					}
					arrKaseLetterSubjects[arrKaseLetterSubjects.length] = subject;
					arrKaseLetterIDs[arrKaseLetterIDs.length] = a_letter.get("document_id");
					arrKaseLetterUUIDs[arrKaseLetterUUIDs.length] = a_letter.get("document_uuid");
					arrKaseLetterUsers[arrKaseLetterUsers.length] = a_letter.get("document_user");
					arrKaseLetterMacros[arrKaseLetterMacros.length] = macro_update;
				});
				template.document_names = arrKaseLetters.join("|");
				template.document_dates = arrKaseLetterDates.join("|");
				template.document_subjects = arrKaseLetterSubjects.join("|");
				template.document_uuids = arrKaseLetterUUIDs.join("|");
				template.document_ids = arrKaseLetterIDs.join("|");
				template.document_users = arrKaseLetterUsers.join("|");
				template.document_macros = arrKaseLetterMacros.join("|");
			} else {
				template.document_names = null;
			}
            if (template.document_names != null && template.document_names!="") {
				//don't show letter if it's itself
				if (template.document_uuid == template.document_uuids) {
					template.document_names = null;
					template.document_dates = null;
					template.document_subjects = null;
					template.document_users = null;
					template.document_uuids = null;
					template.document_macros = null;
				}
				
				//split the names, dates, uuids
				var arrDocuments = [];
				if (template.document_names != null) {
					var arrDocuments = template.document_names.split("|");
				}
				var arrDocumentDates = [];
				if (template.document_dates != null) {
					var arrDocumentDates = template.document_dates.split("|");
				}
				var arrDocumentSubjects = [];
				if (template.document_subjects != null) {
					var arrDocumentSubjects = template.document_subjects.split("|");
				}
				var arrDocumentUUIDs = [];
				if (template.document_uuids != null) {
					var arrDocumentUUIDs = template.document_uuids.split("|");
				}
				var arrDocumentIDs = [];
				if (template.document_ids != null) {
					var arrDocumentIDs = template.document_ids.split("|");
				}
				var arrDocumentUsers = [];
				if (template.document_users != null) {
					var arrDocumentUsers = template.document_users.split("|");
				} 
				var arrDocumentMacros = [];
				if (template.document_macros != null) {
					var arrDocumentMacros = template.document_macros.split("|");
				} 
				var arrLinks = [];
				//_.each( arrDocuments, function(letter) {
				arrayLength = arrDocuments.length;
				for (var i = 0; i < arrayLength; i++) {
					var letter = arrDocuments[i];
					var document_date = moment(arrDocumentDates[i]).format("MM/DD/YY hh:mmA");
					var subject = arrDocumentSubjects[i];
					var document_id = arrDocumentIDs[i];
					var theuser = arrDocumentUsers[i];
					if (typeof theuser == "undefined") {
						theuser = "";
					} else {
						theuser = "&nbsp;(" + theuser + ")";
					}
					var themacro = arrDocumentMacros[i];
					if (typeof themacro == "undefined") {
						themacro = "";
					}
					var arrLetter = letter.split("/");
					var letter_link = "<div style='display:inline-block; width:150px'><a href='" + letter.replace("#", "%23") + ".docx' title='Click to open letter' class='white_text' id='letter_" + document_id + "' target='_blank'>";
					
					letter_link += " " + document_date + "</a>" + themacro;
					if (subject.trim()=="-") {
						subject = "";
					}
					subject = subject.replace("<br> - ", "");
					if (subject!="") {
						 letter_link += "<br>" + subject + "<hr style='height: 1px; width: 99%; margin:0 auto;line-height:2px;border:0 none;' />";
					}
					letter_link += "</div><div style='display:inline-block; width:200px; border:0px solid white; vertical-align:top'>" + theuser + "</div>";
					
					var buttons = "<div style='display:inline-block; vertical-align:top'><a class='send_icon' id='senddocument_" + document_id + "' title='Click to send letter' style='cursor:pointer' data-toggle='modal' data-target='#myModal4'><i class='glyphicon glyphicon-pencil' style='color:#00FFFF'>&nbsp;</i></a><a href='api/download.php?file=" + encodeURIComponent(letter).replace("#", "%23") + ".docx' title='Click to download document to your computer' class='white_text' target='_blank' style='cursor:pointer'><i class='glyphicon glyphicon-save' style='color:#FFFFFF;'>&nbsp;</i></a><a title='Click to delete letter' class='delete_letter' id='deleteletter_" + document_id + "' data-toggle='modal' data-target='#deleteModal' style='cursor:pointer'><i style='font-size:10px; color:#FF3737; cursor:pointer' id='delete_letters' class='glyphicon glyphicon-trash delete_occurence'></i></a></div>";
					letter_link = "<span id='letterholder_" + document_id + "'>" + letter_link + " " + buttons + "</span>";
					
					
					arrLinks[i] = letter_link;
				}
				//put it all together
				template.document_names = arrLinks.join("<br>");
			} else {
				template.document_names = "";
			}
			template.delete_link = "";
			if (self.model.get("no_uploads")) {
				template.delete_link = "<a>del</a>";
			}
			
			//file display name
			var theindex = template.document_name.indexOf("(");
			if (theindex > -1) {
				template.display_name = template.document_name.substring(0, theindex);
			} else {
				template.display_name = template.document_name.substring(0, template.document_name.indexOf(".docx"));
				if (template.display_name=="") {
					template.display_name = template.document_name.substring(0, template.document_name.indexOf(".doc"));
				}
				if (template.display_name=="") {
					template.display_name = template.document_name;
				}
			}
		});
		$(this.el).html(this.template({templates: templates, case_id: case_id}));
		
		tableSortIt("letter_listing");
		
		if (current_case_id==-1) {
			current_case_id= this.model.get("case_id");
		}
		var case_id = current_case_id;
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
		//}
		
		setTimeout(function() {
			self.model.set("hide_upload", true);
			showKaseAbstract(self.model);
		}, 1111);
		
		return this;
    },
	deleteLetter: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.split("_");
		var document_id = arrID[1];
		composeDelete(document_id, "letter");
	},
	clearSearch: function() {
		$("#letter_searchList").val("");
		$( "#letter_searchList" ).trigger( "keyup" );
		$("#letter_searchList").focus();
	},
	sendLetter: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		//var arrID = current_input.id.split("_");
		//var theid = arrID[arrID.length - 1];
		
		composeMessage(current_input.id);
	},
	newLetter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeLetter(element.id);
	},
	confirmdeleteLetter: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "document");
		
	},
	canceldeleteLetter: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteLetter: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "document");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteLetter(event);
			$("#letterholder_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				//$(".document_row_" + theid).fadeOut();
				$("#letterholder_" + $("#confirm_delete_id").val()).fadeOut();
			}, 2500);
		}
	},
	filterLetter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		filterIt(element, "letter_listing", "letter");
	}
});
window.letter_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #parties_selectall":						"selectAll",
		"click #event_parties":							"selectAllEventParties",
		"click #view_letter": 							"displayMain",
		"click #save_billing_modal": 					"addBill",
		"blur .depo_date":								"checkDepoDates",
		"click #view_billable":  						"displayBillable",
		"click #cancel_billable":						"cancelBillable",
		"click #search_matrix":							"searchMatrix",
		"keyup .invoice_hours":							"sumInvoiceHours",
		"keyup .invoice_qty":							"sumInvoiceQty",
		"keyup .invoice_amount":						"sumInvoiceAmounts",
		"change .invoice_firm_select":					"selectInvoiceFirm",
		"change .invoiced_firm":						"setInvoiceFirm",
		"click #invoiced_by":							"showInvoiceEmployee",
		"click #multiple_dois":							"multipleDOI",
		"click #single_dois":							"singleDOI",
		"focus .depo_field":							"showSetDepoButtons",
		"click #invoice_link":							"selectTypeInvoice",
		"click #pre_bill_link":							"selectTypePreBill",
		"click #transfer_funds_link":					"showTransferFunds",
		"click .kinvoice_type":							"changeKInvoiceType",
		"click #letter_view_all_done":					"doTimeouts"
	},
    render:function () {	
		if (typeof this.template != "function") {
			var view = "letter_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }	
		var self = this;
		//dois
		var arrDOIs = [];
		if (this.model.get("kase_dois")=="undefined") {
			var kase_dois = dois.where({case_id: this.model.get("case_id")});
		} else {
			var kase_dois = new KaseInjuryCollection({case_id: this.model.get("case_id")});
			kase_doi = dois.where({case_id: this.model.get("case_id")});
			//var kase_dois = new KaseInjuryCollection({case_id: this.model.get("case_id")});
			kase_dois.set(this.model.get("kase_dois").toJSON());
			//make it into array for each
			kase_dois = kase_dois.toArray();
		}
		var kases = new KaseCollection({ case_id : this.model.case_id });
		var kase = kases.findWhere({case_id: this.model.case_id});
		var case_id = this.model.get("case_id");
		var kase_type = kase.get("case_type");
		var venue_abbr = kase.get("venue_abbr");
		var blnWCAB = isWCAB(kase_type);
		this.model.set("venue_abbr", venue_abbr);
		this.model.set("blnWCAB", blnWCAB);
		
		var selected = "";
		if (kase_dois.length==1) {
			selected = " selected";
		}
		var arrADJ = [];
		var arrInjuryID = [];
		_.each(kase_dois , function(doi) {
			var thedoi = moment(doi.get("start_date")).format("MM/DD/YYYY");
			if (thedoi=="Invalid date") {
				thedoi = doi.get("start_date");
			}
			if (thedoi!="Invalid date") {
				if (doi.get("end_date") != "0000-00-00") {
					thedoi += " - " + moment(doi.get("end_date")).format("MM/DD/YYYY") + " CT";
				}
				thedoi = doi.get("adj_number") + " // " + thedoi;
				//store values only
				//alert(doi.get("adj_number"));
				arrInjuryID.push(doi.id);
				arrADJ.push(doi.get("adj_number") + " // " + thedoi);
				
				//get ready for drop down
				thedoi = "<option value='" + doi.id + "'" + selected + ">" + thedoi + "</option>";
				if (customer_id == 1134 && case_id == "5688") {
					thedoi = "<option value='5694'" + selected + ">DOI 03/30/2019</option>";
				}
				arrDOIs.push(thedoi);
			}
		});
		if (customer_id == 1134 && case_id == "5688") {
			thedoi = "<option value='5694'" + selected + ">DOI 03/30/2019</option>";
			arrDOIs.push(thedoi);
		}
		
		if (arrADJ.length > 1) {
			thedoi = "<option value='" + arrInjuryID.join("|") + "'>Select All ADJs</option>";
			arrDOIs.push(thedoi);
		}
		var parties = new Parties([], { case_id: kase.get("case_id"), case_uuid: kase.get("uuid") });
		parties.fetch({
			success: function(parties) {
				//we need a list of all parties in drop down
				var arrParties = [];
				var arrEmployees = [];
				//fax
				var document_name = self.model.get("document_name").toLowerCase();
				var blnFaxForm = (document_name.indexOf("fax") > -1);
				var blnAppointmentForm = (document_name.indexOf("appt") > -1);
				
				var theparties = parties.toJSON();
				_.each(theparties , function(partie) {
					var thepartie = partie.company_name;
					var theemployee = partie.full_name;
					var theid = partie.corporation_id;
					var thefax = partie.fax;
					if (partie.corporation_id==-1){
						thepartie = theemployee;
					} else {
						if (theemployee!="") {
							thepartie += " // " + theemployee;
						} else {
							theemployee = thepartie;
						}
					}
					
					if (blnFaxForm && thefax!="") {
						thepartie += " - fax: " + thefax;
					}
					thepartie = "<option value='" + theemployee + "'>" + thepartie + "</option>";
					theemployee = "<option value='" + theemployee + "'>" + theemployee + "</option>";
					arrParties.push(thepartie);
					arrEmployees.push(theemployee);
				});
				
				var carriers = parties.where({"type": "carrier"});
				var arrCarriers = [];
				var arrExaminers = [];
				var selected = "";
				if (carriers.length==1) {
					selected = " selected";
				}
				_.each(carriers , function(carrier) {
					var thecarrier = carrier.get("company_name");
					var theexaminer = carrier.get("full_name");
					var thefax = carrier.get("fax");
					var theemployee_fax = carrier.get("employee_fax");
					if (blnFaxForm && thefax!="") {
						thecarrier += " - fax: " + thefax;
						theexaminer += " // " + theemployee_fax;
					}
					thecarrier = "<option value='" + carrier.get("corporation_id") + "'" + selected + ">" + thecarrier + "</option>";
					theexaminer = "<option value='" + theexaminer + "'" + selected + ">" + theexaminer + "</option>";
					arrCarriers.push(thecarrier);
					arrExaminers.push(theexaminer);
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
					var thefax = employer.get("fax");
					if (blnFaxForm && thefax!="") {
						theemployer += " - fax: " + thefax;
					}
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
					var thefax = defense.get("fax");
					if (blnFaxForm && thefax!="") {
						thedefense += " - fax: " + thefax;
					}
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
					var thefax = referring.get("fax");
					if (blnFaxForm && thefax!="") {
						thereferring += " - fax: " + thefax;
					}
					thereferring = "<option value='" + referring.get("corporation_id") + "'" + selected + ">" + thereferring + "</option>";
					arrReferrals[arrReferrals.length] = thereferring;
				});
				
				var defendants = parties.where({"type": "defendant"});
				var arrDefendants = [];
				var selected = "";
				if (defendants.length==1) {
					selected = " selected";
				}
				_.each(defendants , function(defendant) {
					var thedefendant = defendant.get("company_name");
					var thefax = defendant.get("fax");
					if (blnFaxForm && thefax!="") {
						thedefendant += " - fax: " + thefax;
					}
					thedefendant = "<option value='" + defendant.get("corporation_id") + "'" + selected + ">" + thedefendant + "</option>";
					arrDefendants[arrDefendants.length] = thedefendant;
				});
				
				//pi
				var arrPolices = [];
				var arrWitnesses = [];
				if (!blnWCAB) {
					var law_enforcements = parties.where({"type": "law_enforcement"});
					
					var selected = "";
					if (law_enforcements.length==1) {
						selected = " selected";
					}
					_.each(law_enforcements , function(law_enforcement) {
						var thelaw_enforcement = law_enforcement.get("company_name");
						thelaw_enforcement = "<option value='" + law_enforcement.get("corporation_id") + "'" + selected + ">" + thelaw_enforcement + "</option>";
						arrPolices[arrPolices.length] = thelaw_enforcement;
					});
				}
				var witnesss = parties.where({"type": "witnesses"});					
				var selected = "";
				if (witnesss.length==1) {
					selected = " selected";
				}
				_.each(witnesss , function(witness) {
					var thewitness = witness.get("full_name");

					thewitness = "<option value='" + witness.get("corporation_id") + "'" + selected + ">" + thewitness + "</option>";
					arrWitnesses[arrWitnesses.length] = thewitness;
				});
				
				var uefs = parties.where({"type": "uef"});
				var arrUEFs = [];
				var selected = "";
				if (uefs.length==1) {
					selected = " selected";
				}
				_.each(uefs , function(uef) {
					var theuef = uef.get("company_name");
					var thefax = uef.get("fax");
					if (blnFaxForm && thefax!="") {
						theuef += " - fax: " + thefax;
					}
					theuef = "<option value='" + uef.get("corporation_id") + "'" + selected + ">" + theuef + "</option>";
					arrUEFs.push(theuef);
				});
				
				var medical_providers = parties.where({"type": "medical_provider"});
				var arrMedicalProviders = [];
				var selected = "";
				if (medical_providers.length==1) {
					selected = " selected";
				}
				_.each(medical_providers , function(medical_provider) {
					var themedical_provider = medical_provider.get("company_name");
					var thefax = medical_provider.get("fax");
					if (blnFaxForm && thefax!="") {
						themedical_provider += " - fax: " + thefax;
					}
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
					var thefax = lien_holder.get("fax");
					if (blnFaxForm && thefax!="") {
						thelien_holder += " - fax: " + thefax;
					}
					thelien_holder = "<option value='" + lien_holder.get("corporation_id") + "'" + selected + ">" + thelien_holder + "</option>";
					arrLienHolders[arrLienHolders.length] = thelien_holder;
				});
				self.model.set("applicant_full_name", kase.get("full_name"));
				self.model.set("dois", arrDOIs.join("\r\n"));
				self.model.set("dois_count", arrDOIs.length);
				self.model.set("parties", arrParties.join("\r\n"));
				self.model.set("carriers", arrCarriers.join("\r\n"));
				self.model.set("examiners", arrExaminers.join("\r\n"));
				self.model.set("employers", arrEmployers.join("\r\n"));
				self.model.set("defenses", arrDefenses.join("\r\n"));
				self.model.set("referrals", arrReferrals.join("\r\n"));
				self.model.set("law_enforcements", arrPolices.join("\r\n"));
				self.model.set("witnesses", arrWitnesses.join("\r\n"));
				self.model.set("defendants", arrDefendants.join("\r\n"));
				self.model.set("uefs", arrUEFs.join("\r\n"));
				self.model.set("medical_providers", arrMedicalProviders.join("\r\n"));
				self.model.set("lien_holders", arrLienHolders.join("\r\n"));
				var template_description = self.model.get("document_name");
				if (self.model.get("description")!="") {
					template_description += " - " + self.model.get("description");
				}
				self.model.set("template_description", template_description);
				if (typeof self.model.get("document_extension")=="undefined") {
					self.model.set("document_extension", "");
				}
				try {
					$(self.el).html(self.template(self.model.toJSON()));
				}
				catch(err) {
					alert(err);
					
					return "";
				}
			}
		});
		
		return this;
    },
	doTimeouts: function(event) {
		var self = this;
		
		$("#letter_form").disableAutoFill();
		
		var document_id = self.model.get("id");
		var document_name = self.model.get("document_name").toLowerCase();
		var blnFaxForm = (document_name.indexOf("fax") > -1);
		var blnAppointmentForm = (document_name.indexOf("appt") > -1);
		//special case for Matrix Request Form
		var blnMatrixForm = (self.model.get("document_name").indexOf("Matrix Request Form") > -1);
		if (blnMatrixForm) {
			$("#parties_selectall").trigger("click");
			$("#last_date_holder").html("Due Date:");
			$("#pages_label_holder").html("Rush:");
			$("#pages").hide();
			$("#rush").show();
			//only in the future
			$('#last_date').datetimepicker({ 
				validateOnBlur:false, 
				minDate: 0,
				timepicker:false,
				format:'m/d/Y'
				});
		}
		
		$('.depo_date').datetimepicker({ 
				validateOnBlur:false,
				minDate: 0, 
				timepicker:true,
				format:'m/d/Y h:iA',
				allowTimes:workingWeekTimes,
				onChangeDateTime:function(dp,$input) {
					
				}
				});
		$('#depo_bill_dated').datetimepicker({ 
				validateOnBlur:false,
				timepicker:false,
				format:'m/d/Y'
				});
		//deposition
		var blnDepoForm = (self.model.get("document_name").indexOf("Depo") > -1);
		if (blnDepoForm) {
			$("#last_date_holder").html("Date and Time:");
			$("#pages_label_holder").html("");
			$("#pages").hide();
			$("#rush").hide();
			$("#deposition_row").show();
			$(".partie_row").hide();
			$("#depo_location_holder").hide();

			$('#last_date').datetimepicker({ 
				validateOnBlur:false, 
				timepicker:true,
				format:'m/d/Y h:iA'
			});
		}
		
		//fax or appointment
		//var document_name = self.model.get("document_name").toLowerCase();
		//var blnFaxForm = (document_name.indexOf("fax") > -1);
		if (blnFaxForm || blnAppointmentForm) {
			$("#last_date_holder").html("Date and Time:");
			$('#last_date').datetimepicker({ 
				validateOnBlur:false, 
				timepicker:true,
				format:'m/d/Y h:iA'
				});
			$('#last_date').val(moment().format("MM/DD/YYYY h:mma"));
		}
		if (!blnMatrixForm && !blnDepoForm && !blnFaxForm) {
			//appt date and time
			$('#last_date').datetimepicker({ 
				validateOnBlur:false, 
				timepicker:true,
				format:'m/d/Y h:iA'
				});
			$('#last_date').val(moment().format("MM/DD/YYYY h:mma"));
		}
		var document_extension = self.model.get("document_extension").toLowerCase();
		if ($("#letter_parties_list").length > 0) {
			//hide the partie rows
			//$(".letter .partie_row").hide();
			var kase = kases.findWhere({case_id: self.model.case_id});
			var parties = new Parties([], { case_id: self.model.case_id, panel_title: "Parties" });
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
					kase.set("letter_category", self.model.get("document_extension"));
					$('#letter_parties_list').html(new partie_listing_choose({collection: parties, model: kase}).render().el);
					//$('.modal-dialog').animate({}, 1100, 'easeInSine');
					$('.modal-dialog').animate({width:1350, marginLeft:"-700px"}, 1100, 'easeInSine', 
					function() {
						//run this after animation
						if (document_extension!="invoice") {
							$('#letter_parties_list_holder').show();
						}
					});
				}
			});
		}
		var current_top = Number($("#myModal4 .modal-dialog").css("top").replace("px", ""));
		
		if (this.model.get("blnWCAB")) {
			//get the judges
			var venue_abbr = this.model.get("venue_abbr");
			if (venue_abbr=="") {
				venue_abbr = "_";
			}
			var url = "api/judges";
			var formValues = "venue_abbr=" + venue_abbr;
			$.ajax({
				url:url,
				type:'POST',
				dataType:"text",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						console.log(data.error.text);
					} else { 
						//put them in a selectable drop down
						$(".judge_cell").html(data);
						setTimeout(function() {
							if ($("#judge_dropdown").length > 0) {
								$("#judge_dropdown").editableSelect({
									onSelect: function (element) {
										
									}
								});
							}
						}, 300);
					}
				}
			});
		} else {
			//applicant is plaintiff
			var case_id = current_case_id;
			
			var plaintiffs = new Parties([], { case_id: case_id, type: "plaintiff"});
			plaintiffs.fetch({
				success: function(plaintiffs) {
					if (plaintiffs.length > 0) {
						var company_name = plaintiffs.toJSON()[0].company_name;
						$("#applicant_holder").html(company_name);
					}
				}
			});
		}
		
		if (blnShowMatrixInfo) {
			//check if already in matrix
			var order =  new MatrixOrder({id: current_case_id});
			order.fetch({
				success: function (data) {
					var order_id = data.get("order_id");
					arrMatrixLinks = [];
					
					arrMatrixLinks.push("<a href='https://www.matrixdocuments.com/dis/pws/quicks/orders/editorder_new.php?id=" + order_id + "' target='_blank' title='Review Order - You must be logged-in to Matrix' class='white_text'>Order</a>");
					
					arrMatrixLinks.push("<a href='https://www.matrixdocuments.com/dis/pws/quicks/orders/location_edit.php?id=" + order_id + "' target='_blank' title='Review Locations - You must be logged-in to Matrix' class='white_text'>Locations</a>");
					
					arrMatrixLinks.push("<a href='https://www.matrixdocuments.com/dis/pws/quicks/orders/location_invoices.php?id=" + order_id + "' target='_blank' title='Review Locations - You must be logged-in to Matrix' class='white_text'>Invoices</a>");
					
					$("#import_matrix_data_holder").html("<span style='background:black; padding:2px'>Matrix Order " + order_id + "</span><br>" + arrMatrixLinks.join("&nbsp;|&nbsp;"));
					
					getMatrixOrderInfo(order_id);
					return;		
				}
			});
		} else {
			$("#import_matrix_data_holder").hide();
		}
		
		//invoice
		if (document_extension=="invoice") {
			$("#view_billable").hide();
			$(".parties_selectall").hide();
			
			//make sure they select firm
			this.setInvoiceFirm();
			
			//get the invoice items for this document
			var url = "api/kinvoicedoc/" + document_id;
			var blnEditKInvoice = false;
			var assigned_to = "";
			var assigned_name = "";
			var corporation_id = "";
			var assigned_name = "";
			var invoiced_firm = "";
			var kinvoice_id = "";
			var kinvoice_document_id = "";
			var mymodel = this.model.toJSON();
			if (typeof mymodel.jInvoiceInfo.document_id != "undefined") {
				blnEditKInvoice = (mymodel.jInvoiceInfo.template == "N");
				if (blnEditKInvoice) {
					assigned_to = mymodel.jInvoiceInfo.assigned_to;
					assigned_name = mymodel.jInvoiceInfo.assigned_name;
					corporation_id = mymodel.jInvoiceInfo.corporation_id;
					invoiced_firm = mymodel.jInvoiceInfo.invoiced_firm;
					kinvoice_number = mymodel.jInvoiceInfo.kinvoice_number;
				}
				//get the items from the original invoice
				kinvoice_id = mymodel.jInvoiceInfo.kinvoice_id;
				kinvoice_document_id = mymodel.jInvoiceInfo.document_id;
				if (kinvoice_id=="") {
					url = "api/kinvoicedoc/" + kinvoice_document_id;
				} else {
					url = "api/kinvoiceitems/" + kinvoice_id;
				}
			}
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						console.log(data.error.text);
					} else { 
						var blnDeposition = false;
						var arrLength = 0;
						if (typeof data.items != "undefined") {
							arrLength = data.items.length;
							var list = data.items;
						} else {
							arrLength = data.length;
							var list = data;
						}
						var hourly_rate = 0;
						var kinvoice_id = 0;
						if (arrLength > 0) {
							hourly_rate = list[0].hourly_rate;
							if (kinvoice_id == "") {
								kinvoice_id = list[0].kinvoice_id;
							}
						}
						var arrRows = [];
						for (var i = 0; i < arrLength; i++){
							var datum = list[i];
							if (datum.deleted == "Y") {
								continue;
							}
							//clean up
							var arrName = datum.item_name.split("    ($");
							datum.item_name = arrName[0];
							if (!blnDeposition) {
								if (datum.item_name.indexOf("Deposition") > -1) {
									blnDeposition = true;
								}
							}
							var blnCost = (arrName.length > 1 && datum.unit!="");
							var qty = "";
							var rate = "";
							var rate_display = "";
							var rateunit = "";
							
							if (blnCost) {
								rate = arrName[1].split(" per")[0];
							}
							var exact = datum.exact;
							var amount = "";
							if (blnCost) {
								qty = datum.minutes;
								amount = datum.amount;
							} else {
								if (Number(datum.amount) > 0) {
									amount = datum.amount;
								}
							}
							var minutes = "";
							if (Number(datum.minutes) < 0) {
								exact = "Y";
							}
							if (Number(datum.minutes) > 0) {
								minutes = datum.minutes;
							}
							var display_minutes = "";
							var display_amount = "disabled";
							var display_qty = "disabled";
							var display_amount_span = "display:none";
							var display_qty_span = "display:block";
							if (exact=="Y") {
								display_minutes = "disabled";
								display_amount = "";
								display_amount_span = "display:none";
							}
							
							if (datum.template=="Y") {
								if (amount > 0) {
									display_minutes = "disabled";
									display_qty = "";
									qty = 0;
									rate = amount;
									rateunit = datum.unit;
									rate_display = "$" + rate + " per " + rateunit;
									amount = "";
								}
							}
							if (blnCost) {
								display_minutes = "disabled";
								minutes = "";
								display_qty = "";
								rateunit = datum.unit;
								rate_display = "$" + rate + " per " + rateunit;
							}
							var rateunitlegend = "";
							if (rateunit!="") {
								 rateunitlegend = "(s)";
							}
							var row = "<tr><th align='left' valign='top' nowrap>" + datum.item_name + "</th><td align='left' valign='top' nowrap><input type='number' class='invoice_hours' id='hours_" + datum.id + "' style='width:60px;' " + display_minutes + " autocomplete='off' value='" + minutes + "' /></td><td align='left' valign='top' nowrap><input type='text' class='invoice_qty' id='qty_" + datum.id + "' style='width:60px;' " + display_qty + " autocomplete='off' value='" + qty + "' " + display_qty + "  />&nbsp;" + rateunit + rateunitlegend + "<input id='rate_" + datum.id + "' type='hidden' value='" + rate + "' /><input id='rateunit_" + datum.id + "' type='hidden' value='" + rateunit + "' /><span id='qty_span_" + datum.id + "' style='" + display_qty_span + "'>" + rate_display + "</span></td><td align='left' valign='top'><input type='text' class='invoice_amount' id='amount_" + datum.id + "' style='width:60px;' " + display_amount + " autocomplete='off' value='" + amount + "' /><span id='amount_span_" + datum.id + "' style='" + display_amount_span + "'></span></td></tr>";
							arrRows.push(row);
						}
						
						if (arrRows.length > 0) {
							var row = "<tr><td align='left' valign='top' colspan='3'>&nbsp;</td></tr>";
							arrRows.push(row);
							var row = "<tr><th align='left' valign='top'>Total:</th><td align='left' valign='top'><span id='invoice_hours_total'></span></td><td align='left' valign='top'>&nbsp;</td><td align='left' valign='top'><span id='invoice_amount_total'></span></td></tr>";
							arrRows.push(row);
							
							$("#invoice_items_table").append(arrRows.join(""));
							$("#kinvoice_id").val(kinvoice_id);
							$("#kinvoice_document_id").val(kinvoice_document_id);
							$("#hourly_rate").html(hourly_rate);
							
							$(".modal-dialog").css("margin-top", "-420px");
						}
						
						var theme = {
							theme: "task", 
							tokenLimit: 1,
							minChars:2, 
							hintText: "Search for Employees",
							onAdd: function(item) {
								//get the rate if any
								if (item.rate!="") {
									$("#hourly_rate").html(item.rate);
								}
								//recalculate
								self.sumInvoiceHours();
							},
							onDelete: function() {
								$("#hourly_rate").html(self.model.get("hourly_rate"));
							}
						};
						
						$("#invoice_items_table #assigneeInput").tokenInput("api/user", theme);
						$(".token-input-list-task").css("display", "inline-block");
						$(".token-input-list-task").css("width", "150px");
						$(".token-input-dropdown-task").css("width", "150px");
						
						if (blnEditKInvoice) {
							$("#invoice_items_table #assigneeInput").tokenInput("add", {
								id: assigned_to, 
								name: assigned_name,
								tokenLimit:1
							});
							document.getElementById("invoiced_firm_" + invoiced_firm).checked = true;
							$("#" + invoiced_firm).val(corporation_id);
							$(".invoiced_firm_span").hide();
							$(".invoiced_firm_" + invoiced_firm).show();
							
							var newhtml = "<span style='font-weight:bold'>Invoice Number:</span><br>" + kinvoice_number;
							$("#invoice_number_holder").html(newhtml);
							$("#kinvoice_number").val(kinvoice_number);
						} else {
							$("#invoice_items_table #assigneeInput").tokenInput("add", {
								id: login_user_id, 
								name: login_username,
								tokenLimit:1
							});
						}
						
						//hide what we don't need
						$(".partie_select_row").hide();
						$("#last_date_holder").html("Invoice Date:");
						//if (!blnDeposition) {
							//no longer needed 2/27/2019
							//$("#deposition_details").hide();
						//}
						$("#middle_section_holder").css("margin-right", "200px");
						$("#letter_text_holder").hide();
					}
				}
			});
			var case_id = self.model.get("case_id");
			//list case invoices for this document
			var document_invoices = new KaseDocumentKInvoices({"case_id": case_id, "document_id": self.model.get("document_id")});
			document_invoices.fetch({
				success: function (data) {
					if (data.length > 0) {
						var mymodel = new Backbone.Model({
							"holder": "invoices_listing_holder",
							"document_invoices": true,
							"case_id": case_id,
							"page_title":"Created Invoices"
						});
						$("#invoices_listing_holder").html(new kinvoice_listing_view({collection: data, model: mymodel}).render().el);
					}
				}
			});
			var account_id = self.model.get("account_id");
			if (account_id > -1) {
				var account =  new Account({"id": account_id}); //"case_id": case_id, "account_type": "trust"
				account.fetch({
					success: function (data) {
						//console.log(data);
						var bank = JSON.parse(data.toJSON().account_info)[0].value;
						$("#transfer_funds").val(data.toJSON().id);
						$("#transfer_trust_funds").show();
					}
				});
			}
		}
	},
	selectTypeInvoice: function() {
		document.getElementById("kinvoice_type_invoice").checked = true;
		this.changeKInvoiceType();
	},
	selectTypePreBill: function() {
		document.getElementById("kinvoice_type_pre").checked = true;
		this.changeKInvoiceType();
	},
	showTransferFunds: function() {
		document.getElementById("transfer_funds").checked = true;
	},
	changeKInvoiceType: function() {
		var kinvoice_type = "I";
		if (document.getElementById("kinvoice_type_pre").checked) {
			kinvoice_type = "P";
		}
		var display = "block";
		if (kinvoice_type == "P") {
			display = "none";
			document.getElementById("transfer_funds").checked = false;
		}
		
		$("#transfer_trust_funds").css("display", display);
	},
	addBill: function(event) {
		var self = this;
		var element = event.currentTarget;
		
		if (blnShowBilling) {
			
			var billing_date = $("#billing_dateInput").val();
			var duration = $("#durationInput").val();
			var status = $("#billing_form #statusInput").val();
			var billing_rate = $("#billing_rateInput").val();
			var activity_code = $("#activity_codeInput").val();
			var timekeeper = $("#timekeeperInput").val();
			var description = $("#billing_form #descriptionInput").val();
			var table_id = $("#billing_form #table_id").val();
			var action_id = $("#action_id").val();
			var action_type = $("#action_type").val();
				
			var formValues = "case_id=" + current_case_id + "&billing_date=" + billing_date + "&table_id=" + table_id + "&status=" + status;
				formValues += "&duration=" + duration + "&billing_rate=" + billing_rate + "&activity_code=" + activity_code + "&timekeeper=" + timekeeper + "&description=" + description + "&action_id=" + action_id + "&action_type=" + action_type;
			
			var modal_bg = $(".modal-dialog").css('background-image');
			modal_bg = modal_bg.replace('"', "'");
			modal_bg = modal_bg.replace('"', "'");
			//console.log(modal_bg);
			//alert(modal_bg);
			//return;
			$.ajax({
			  method: "POST",
			  url: "api/billing/add",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
					  alert("error");
				  } else {
					  $("#myModalBody").css('background', "#0C3");
					  //rgb(255, 255, 255)
					  setTimeout(function() {
						  $("#myModalBody").css('background-color', '');
						  $("#myModalBody").css('background-image', modal_bg);
						  setTimeout(function() {
							  //self.displayEvent();
						  }, 700);
					  }, 2000);
				  }
			  }
			});
		}
	},
	displayMain: function(event){
		//show the letter prep screen
		if (blnShowBilling) {
			$("#myModalLabel").html("Create Letter");
			//hide the button
			$("#view_letter").fadeOut(function() {
				var billing_employee = $("#timekeeperInput").val();
				if (billing_employee!="") {
					$("#view_billable").val("Bill Ready ✓");
					$("#view_billable").fadeIn();
					$("#cancel_billable_holder").css("display", "inline");
				} else {
					$("#view_billable").val("Not Billed");
					$("#view_billable").fadeIn();
					
					setTimeout(function() {
						$("#view_billable").val("Bill This");
					}, 2500);
				}
			});
			$("#letter_div").fadeIn();
			$("#billing_holder").fadeOut();
			$("#modal_save_holder").fadeIn();
			$('.modal-dialog').animate({width:1400, marginLeft:"-650px"}, 1100, 'easeInSine');
		}
	},
	searchMatrix: function(event) {
		event.preventDefault();
		
		var applicant = $("#applicant_holder").html().trim();
		
		var orders = new MatrixOrderCollection({applicant: applicant});
		orders.fetch({
			success:function (orders) {
				//applicant_search_holder
				$("#applicant_search_holder").html("");
				var mymodel = new Backbone.Model();
				mymodel.set("holder", "applicant_search_holder");
				//var inactiveView = new kase_listing_view({el: $("#content"), collection: inactive_cases, model: mymodel}).render();
				$('#applicant_search_holder').html(new matrix_order_listing_view({collection: orders, model: mymodel}).render().el);
			}
		});
	},
	cancelBillable: function(event){
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		if (blnShowBilling) {
			$("#billing_holder").html("");
			$("#cancel_billable_holder").fadeOut();
			$("#view_billable").val("Bill This");
		}
	},
	displayBillable: function(event){
		var self = this;

		event.preventDefault(); // Don't let this button submit the form
		var event_id = $("#table_id").val();

		if (blnShowBilling) {
			$("#myModalLabel").html("Bill this Letter");
			$("#cancel_billable_holder").css("display", "none");

			//hide the button
			$("#view_billable").fadeOut(function() {
				$("#view_letter").val("Return to Letter");
				$("#view_letter").fadeIn();
			});

			$("#letter_div").fadeOut();
			$('.modal-dialog').animate({width:540, marginLeft:"-230px"}, 700, 'easeInSine');
			$("#billing_holder").fadeIn();
			//$('#billing_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
			$("#modal_save_holder").fadeOut();
			//already in?
			if ($("#billing_holder").html().trim() == "") {
				var bill = new BillingMain({ case_id: current_case_id, action_id: -1, action_type: "Letters"});
				bill.set("holder", "billing_holder");
				bill.set("billing_date", moment().format("MM/DD/YYYY"));
				bill.set("activity_category", "letter");
				bill.set("activity_id", -1);
				var template_name = $("#template_name").val().replace(".docx", "");
				bill.set("activity", "Letter created - " + template_name);
				
				//bill = bill.toJSON();
				
				$("#billing_holder").html(new activity_bill_view({model: bill}).render().el);


			}
		}
    },
	sumInvoiceHours: function() {
		var hours_inputs = $(".invoice_hours");
		var arrLength = hours_inputs.length;
		var hourly_rate = Number($("#hourly_rate").html());
		var totals = 0;
		for(var i = 0; i < arrLength; i++) {
			var hour = hours_inputs[i];
			var hour_id = hour.id;
			var arrID = hour_id.split("_");
			var item_id = arrID[arrID.length - 1];
			var val = hour.value;
			if (val=="") {
				val = 0;
			}
			totals += Number(val);
			
			//update amount			
			var hours = val;
			var amount = hours * hourly_rate;
			$("#amount_" + item_id).val(amount.toFixed(2));
			$("#amount_span_" + item_id).html("$" + amount.toFixed(2));
		}
		var total_hours = totals;
		$("#invoice_hours_total").html(totals.toFixed(0) + " hrs");
		
		this.sumInvoiceAmounts();
	},
	sumInvoiceQty: function() {
		var qty_inputs = $(".invoice_qty");
		var arrLength = qty_inputs.length;
		
		for(var i = 0; i < arrLength; i++) {
			var qty = qty_inputs[i];
			var qty_id = qty.id;
			var arrID = qty_id.split("_");
			var item_id = arrID[arrID.length - 1];
			
			var rate = Number($("#rate_" + item_id).val());
			
			var val = qty.value;
			if (val=="") {
				val = 0;
			}
			val = Number(val);
			//update amount
			if (val > 0) {
				var amount = val * rate;
				$("#amount_" + item_id).val(amount.toFixed(2));
				$("#amount_span_" + item_id).html("$" + amount.toFixed(2));
			}
		}		
		this.sumInvoiceAmounts();
	},
	sumInvoiceAmounts: function() {
		var invoice_amounts = $(".invoice_amount");
		var arrLength = invoice_amounts.length;
		var totals = 0;
		for(var i = 0; i < arrLength; i++) {
			var amount = invoice_amounts[i];
			var val = amount.value;
			if (val=="") {
				val = 0;
			}
			totals += Number(val);
		}
		
		$("#invoice_amount_total").html("$" + totals.toFixed(2));
	},
	selectInvoiceFirm: function() {
		if ($("#invoiced_firm_defense").length > 0) {
			this.setInvoiceFirm();
		}
	},
	setInvoiceFirm: function() {
		//reset borders
		$("#defense").css("border", "1px solid rgb(169, 169, 169)");
		$("#carrier").css("border", "1px solid rgb(169, 169, 169)");
		
		var invoice_firm = document.getElementById("invoiced_firm_defense");
		var id = "carrier";
		var other_id = "defense";
		if (invoice_firm.checked) {
			//make sure that the defense drop down is selected
			id = "defense";
			other_id = "carrier";
		}
		$("." + other_id + "_row").hide();
		
		var dropdown_value = $("#" + id).val();
		if (dropdown_value=="") {
			$("#" + id).css("border", "2px solid red");
		}
		$("." + id + "_row").show();
	},
	multipleDOI: function() {
		document.getElementById("doi").multiple = true;
		$("#doi option[value='']").remove();
		document.getElementById("doi").size = document.getElementById("doi").options.length;
		
		$("#multiple_dois").fadeOut(function() {
			$("#single_dois").fadeIn();
			$("#multiple_doi_instructions").fadeIn();
		});
	},
	singleDOI: function() {
		document.getElementById("doi").multiple = false;
		$("#doi").prepend("<option value=''>Select from List</option>");
		document.getElementById("doi").size = 1;
		
		$("#single_dois").fadeOut(function() {
			$("#multiple_dois").fadeIn();
			$("#multiple_doi_instructions").fadeOut();
		});
	},
	showSetDepoButtons: function() {
		$(".depo_partie_holder").show();
	},
	showInvoiceEmployee: function(event) {
		event.preventDefault();
		//obsolete?
		return;
		$("#invoiced_by").fadeOut(function() {
			$(".employee_invoice_holder").css("visibility","visible");
			$("#token-input-assigneeInput").focus();
		});
	},
	selectAll: function(event) {
		$(".parties_option").prop("checked", $("#parties_selectall").prop("checked"));
	},
	selectAllEventParties:function(event) {
		var element = event.currentTarget;
		$(".event_partie").prop("checked", element.checked);
	},
	checkDepoDates: function() {
		var start_date = $(".letter #depo_arrival_time").val();
		var end_date = $(".letter #depo_dateandtime").val();
		if (start_date=="" || end_date=="") {
			return;
		}
		//break it up for processing
		var arrDate = start_date.split(" ");
		var start_date = arrDate[0];
		var start_time = arrDate[1];
		var arrTime = start_time.split(":");
		var blnPM = (arrTime[1].indexOf("PM") > -1);
		//first fix the hour
		if (blnPM) {
			if (arrTime[0].indexOf("0")==0) {
				var clean_hour = arrTime[0].substr(1,1);
				arrTime[0] = Number(clean_hour) + 12;
			}
			arrTime[1] = arrTime[1].replace("PM", ":00");
		} else {
			arrTime[1] = arrTime[1].replace("AM", ":00");
		}
		var d1 =  new Date(start_date);
		//now replace the time
		d1 = String(d1).replace("00:00:00", arrTime.join(":"));
		d1 =  new Date(d1);
		
		//break it up for processing
		var arrDate = end_date.split(" ");
		var end_date = arrDate[0];
		var end_time = arrDate[1];
		var arrTime = end_time.split(":");
		var blnPM = (arrTime[1].indexOf("PM") > -1);
		//first fix the hour
		if (blnPM) {
			if (arrTime[0].indexOf("0")==0) {
				var clean_hour = arrTime[0].substr(1,1);
				arrTime[0] = Number(clean_hour) + 12;
			}
			arrTime[1] = arrTime[1].replace("PM", ":00");
		} else {
			arrTime[1] = arrTime[1].replace("AM", ":00");
		}
		var d2 =  new Date(end_date);
		//now replace the time
		d2 = String(d2).replace("00:00:00", arrTime.join(":"));
		d2 =  new Date(d2);
		
		var diff = d2.getTime() - d1.getTime();
		if (diff < 0) {
			end_date = moment(d2).format("MM/DD/YYYY hh:mA");
			$(".letter #depo_arrival_time").val(end_date);
		}
	}
});
window.matrix_info_table_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #matrix_info_table_all_done": "doTimeouts"
	},
	render:function () {
		if (typeof this.template != "function") {
			
			var view = "matrix_info_table_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   		
		var self = this;
		var info = this.model.toJSON();
		//calculations
		info.penalties = (Number(info.sum_balance_due.replace(",", "")) * .1).toFixed(2);
		info.daily_interest = (Number(info.sum_balance_due.replace(",", "")) * .07 / 360).toFixed(2);
		$(this.el).html(this.template({info: info}));
		
		return this;
	},
	doTimeouts: function() {
		var self = this;
		
		$('.pos_date').datetimepicker({ 
			validateOnBlur:false, 
			maxDate: 0,
			timepicker:false,
			format:'m/d/Y'
		});
	}
});
window.matrix_order_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #close_matrix_order_listing":		"closeLookup",
		"click #link_specific_order_id":			"linkIt",
		"click .assign_order":						"assignLookup",
		"keyup #new_matrix_order_id":				"resetConfirm"
	},
	render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "applicant_search_holder");
			var view = "matrix_order_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   		
		var self = this;
		var matrix_orders = this.collection.toJSON();
		$(this.el).html(this.template({matrix_orders: matrix_orders}));
		
		return this;
	},
	resetConfirm: function() {
		clearTimeout(confirm_timeout_id);
		
		var self = this;
		
		if ($("#link_order_confirmation_row").css("display")!="none") {
			$("#link_order_confirmation_row").fadeOut();
			$("#link_order_feedback").html("Enter Matrix Order ID to link to this Kase");
		}
		confirm_timeout_id = setTimeout(function() {
			self.linkIt()
		}, 1000)
	},
	linkIt: function() {
		var order_id = $("#new_matrix_order_id").val();
		
		if (order_id.length < 4) {
			return;
		}
		//i want the assigned date for this order
		var order_data = new MatrixOrderData({id: order_id, case_id: current_case_id});
		order_data.fetch({
			success: function (data) {
				var assigned_date = data.get("actual_assigned_date");
				var copyon_name = data.get("copyon_name");
				var employer = data.get("employer");
				
				var order_link = '<a id="assign_order_' + order_id + '" class="assign_order" style="cursor:pointer; text-decoration:underline" title="Click to link this Case to Matrix Order ID ' + order_id + '">' + order_id + '</a>';
				$("#order_confirm_order_id").html(order_link);
				
				var assigned_date_content = assigned_date + '<span id="assigned_date_' + order_id + '" style="display:none">' + assigned_date + '</span>';
				$("#order_confirm_assigned_date").html(assigned_date_content);
				
				$("#order_confirm_applicant").html(copyon_name);
				$("#order_confirm_employer").html(employer);
				
				$("#link_order_confirmation_row").fadeIn();
				$("#link_order_feedback").html("Click the Order ID below to confirm");
			}
		});
	},
	closeLookup: function() {
		$("#applicant_search_holder").html("");
	},
	assignLookup: function(event) {
		var element = event.currentTarget;
		var order_id = element.id.replace("assign_order_", "");
		var assigned_date = $("#assigned_date_" + order_id).html().trim();
		var url = "api/kases/matrixlink";
		var formValues = "case_id=" + current_case_id + "&order_id=" + order_id + "&order_date=" + assigned_date;
		
		this.closeLookup();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					$("#import_matrix_data_holder").html("<span style='background:green; color:white; padding:2px'>" + order_id + " Linked &#10003;</span>");
					
					getMatrixOrderInfo(order_id);
				}
			}
		});
	}
});
function allowDrop(ev) {
	ev.preventDefault();
}
function drag_end(ev) {
	$("#focusme").focus();
}
function drag(ev) {
	ev.dataTransfer.setData("Text",ev.target.innerHTML);
}
function drop(ev) {
	ev.preventDefault();
	var data=ev.dataTransfer.getData("Text");
	ev.target.appendChild(document.getElementById(data));
}
function getMatrixOrderInfo (order_id) {
	var order_data = new MatrixOrderData({id: order_id, case_id: current_case_id});
	order_data.fetch({
		success: function (data) {
			data.set("holder", "matrix_info_holder");
			$('#matrix_info_holder').html(new matrix_info_table_view({model: data}).render().el);	
			$(".matrix_info_row").fadeIn();	
		}
	});
	
	return;
}