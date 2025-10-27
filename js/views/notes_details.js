var notes_timeout_id;
window.notes_view = Backbone.View.extend({
	render: function () {
		$(this.el).html(this.template(this.model.toJSON()));
		
		//we are not in editing mode initially
		this.model.set("editing", false);
		
		//gridster the notes tab
		setTimeout("gridsterIt(5)", 10);
		
		if (this.model.id<0) {
			$( ".notes .edit" ).trigger( "click" );
		}
		$("#entered_byInput").val(login_username);
		
        return this;
	},
	
	events:{
		"dblclick .notes .gridster_border": 	"editNotesField",
		"click .notes .save":					"scheduleAddNote",
		"click .notes .save_field":				"saveNotesField",
		"click .notes .edit": 					"scheduleNotesEdit",
		"click .notes .reset": 					"scheduleNotesReset"
    },
	
	editNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".notes_" + field_name;
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
			master_class = ".notes_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddNote:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(notes_timeout_id);
		notes_timeout_id = setTimeout(function() {
			self.addNote(event);
		}, 200);
	},
	addNote:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		//turn off editing to toggle
		this.model.set("editing", false);
		addForm(event);
		//turn off editing altogether
		this.model.set("editing", false);
		return;
    },
	saveNotesField: function (event) {
		console.log("save_function_start");
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
	scheduleNotesEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(notes_timeout_id);
		notes_timeout_id = setTimeout(function() {
			self.toggleNotesEdit(event);
		}, 200);
	},
	toggleNotesEdit: function (event) {
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
		toggleFormEdit("notes");
	},
	scheduleNotesReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(notes_timeout_id);
		notes_timeout_id = setTimeout(function() {
			self.resetNotesForm(event);
		}, 200);
	},
	
	resetNotesForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleNotesEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	}
});
window.note_listing_view = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
		
		if (typeof this.model.get("partie_type")== "undefined"){
			this.model.set("partie_type", "note");
		}
    },
	events: {
		"change #typeFilter":							"filterNotes",
		"change #typeFilter":							"checkManageTypeFilter",
		"click .compose_new_note":						"newNotes",
		"click .edit_note":								"newNotes",
		"click .read_more":								"expandNote",
		"click .hide_note":								"shrinkNote",
		"click .delete_note":							"confirmdeleteNote",
		"click .delete_yes":							"deleteNote",
		"click .delete_no":								"canceldeleteNote",
		"click #note_clear_search":						"clearSearch",
		"click #label_search_notes":					"Vivify",
		"click #notes_searchList":						"Vivify",
		"focus #notes_searchList":						"Vivify",
		"blur #notes_searchList":						"unVivify",
		"scroll .note_list_outer_div":					"hideAttach",
	},
    render:function () {		
		var self = this;
		
		var case_id = this.model.get("case_id");
		var kase = kases.findWhere({"case_id": case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.remove(kase.id); kases.add(kase);
						self.render();
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		
		var notes = this.collection.toJSON();
		var note_filter_options = "";
		var arrTypes = [];
		_.each( notes, function(note) {
			//copy/paste clean up word
			note.note = note.note.replaceAll('<p class="MsoNormal"', '<p');
			note.note = note.note.replaceAll('class="MsoNormal"', '');
			note.note = note.note.replaceAll('class="MsoNormal" style="margin-bottom:0in;margin-bottom:.0001pt;line-height:', '');
			note.note = note.note.replaceAll('style="margin-bottom:0in;margin-bottom:.0001pt;line-height:', '');
			note.note = note.note.replaceAll('normal">', '>');
			var attach_indicator = "hidden";
			if (note.attachments!="") {
				attach_indicator = "visible";
				
				//clean up if necessary
				var arrAttach = note.attachments.split("D:/uploads/");
				if (arrAttach.length==2) {
					//secondary check
					var arrFiles = arrAttach[1].split("/");
					var upload_customer_id = arrFiles[0];
					if (!isNaN(upload_customer_id)) {
						if (upload_customer_id!=customer_id) {
							arrFiles[0] = customer_id;
							arrAttach[1] = arrFiles.join("/");
						}
					}
					note.attachments = "D:/uploads/" + arrAttach[1];
				}
			}
			//length
			note.full_note = "";
			if (note.note.length > 499) {
				note.full_note = "<div id='fullnote_" +  note.id + "' style='display:none'><div style='width:45px; text-align:center; cursor:pointer; background:white; color:black' id='hidenote_" +  note.id + "' class='hide_note white_text' title='Click to shrink note'>close</div>" + note.note.replaceAll("\r\n", "<br>") + "</div>";
				note.note = "<div id='partialnote_" +  note.id + "'>" + note.note.getComplete(500) + "&nbsp;<a id='readmore_" + note.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more</a></div>";
				
			}
			
			//removing link for edit of inter office added to notes	
			var thenote = note.note;
			note.editable = true;
			/*
			if (thenote.indexOf("(sent to ") > 0) {
				note.editable = false;
			}
			*/
			var standard_link = '<span id="preview_link_' + note.id + '" onmouseover="showAttachmentPreview(' + String.fromCharCode(39) + 'note' + String.fromCharCode(39) + ', event, ' + String.fromCharCode(39) + note.attachments + String.fromCharCode(39) + ', ' + note.case_id + ', ' + note.customer_id + ')"><i style="font-size:15px;color:#FFFFFF; cursor:pointer; visibility:' + attach_indicator + '" class="glyphicon glyphicon-paperclip"></i></span>';
							
			note.attachment_link = standard_link;
			if (note.type=="document") {
				var arrNote = thenote.split("\r\n");
				if (arrNote.length > 1) {
					note.attachment_link = "";
					note.note = arrNote[1].trim();
				}
			}
			
			if (moment(note.dateandtime).format('h:mm a')=="12:00 am") {
				note.time = "";
			} else {
				note.time = moment(note.dateandtime).format('h:mma');
			}
			note.date = moment(note.dateandtime).format("dddd, MMMM Do YYYY");
			
			if (note.type!="") {
				if (arrTypes.indexOf(note.type) < 0) {
					arrTypes[arrTypes.length] = note.type;
					note_filter_options +='\r\n<option value="' + note.type + '">' + note.type.capitalize() + ' Note</option>';
				}
			}
			
			if (note.status == "IMPORTANT") {
				note.type = "<span style='background:blue;color:white'>IMPORTANT</span>";
			} 
			
			//watch for html
			/*
			if (typeof note.attribute != "undefined") {
				if (note.attribute.indexOf("@") > -1 || note.attribute=="webmail_note") {
					note.type = "Webmail";
					//note.note = "<iframe srcdoc='" + note.note.replaceAll("'", "`") + "' style='width:100%; height:300px;background:white' frameborder='0'></iframe>";
					note.note = "<iframe src='api/webmail_get.php?notes_id=" + note.id + "' style='width:100%; height:300px;background:white' frameborder='0'></iframe>";
				}
			}
			*/
			var arrNoteDOIS = [];
			if (typeof note.injury_dates != "undefined") {
				if (note.injury_dates!="") {
					var arrDates = note.injury_dates.split(",");
					arrDates.forEach(function(injury_dates, index, array) {
						var arrDateArray = injury_dates.split("|");
						
						var doi_dates = moment(arrDateArray[1]).format("MM/DD/YYYY");
						if (arrDateArray[2]!="0000-00-00") {
							doi_dates += " - " + moment(arrDateArray[1]).format("MM/DD/YYYY") + " CT";
						}
						var injury_details = "<a href='#injury/" + current_case_id + "/" + arrDateArray[0] + "' class='white_text'>" + doi_dates + "</a>";
						arrNoteDOIS.push("<a href='#kases/" + note.main_case_id + "' class='white_text'>" + arrDateArray[0] + "</a> - DOI:" + injury_details);
					
					});
				}
			}
			if (arrNoteDOIS.length > 0) {
				note.injury_dates = arrNoteDOIS.join("<br>");
				
				var new_subject = "<span style='font-size:0.7em;'>From Related Cases:<br>" + note.injury_dates + "</span>";
				note.subject = note.subject + "<br><br>" + new_subject;
			}
			/*
			//maybe a related case
			if (typeof note.start_date != "undefined") {
				if (note.case_id != current_case_id) {
					var new_subject = "<span style='font-size:0.7em; font-style:italic'>From Related Case <a href='#kases/" + note.case_id + "' class='white_text' style='pointer:cursor'>" + note.main_case_number + "</a> - DOI:&nbsp;" + moment(note.start_date).format("MM/DD/YYYY");
					if (note.end_date!="" && note.end_date!="0000-00-00") {
						new_subject += " - " + moment(note.end_date).format("MM/DD/YYYY") + " CT";
					}
					new_subject += "</span>";
					note.subject = note.subject + "<br><br>" + new_subject;
				}
			}
			*/
		});

		var mykase = kase.toJSON();
		var case_status = mykase.case_status;
		var case_substatus = mykase.case_substatus;
		var attorney = mykase.attorney;
		var worker = mykase.worker;
		var rating = mykase.rating;
		//var kase = kases.findWhere({case_id: this.model.get("case_id")});
		this.model.set("case_status", case_status);
		this.model.set("case_substatus", case_substatus);
		this.model.set("attorney", attorney);
		this.model.set("worker", worker);
		this.model.set("rating", rating);
		this.model.set("interpreter_needed", mykase.interpreter_needed);
		
		setTimeout(function() {
			$("#case_number_fill_in").html(mykase.case_number);
			$("#adj_number_fill_in").html(mykase.adj_number);
			if (mykase.adj_number == "") { 
				$("#adj_slot").hide();
			}
			$("#case_status_fill_in").html(mykase.case_status);
			$("#case_substatus_fill_in").html(mykase.case_substatus);
			$("#attorney_fill_in").html(mykase.attorney);
			$("#rating_fill_in").html(mykase.rating);
			$("#worker_fill_in").html(mykase.worker);
			$("#case_date_fill_in").html(mykase.case_date);
			$("#claims_fill_in").html(mykase.claims);
			if (mykase.claims == "") { 
				//$("#claims_slot").hide();
			}
			$("#case_type_fill_in").html(mykase.case_type);
			$("#case_type").val(mykase.case_type);
			$("#language_fill_in").html(mykase.language);
			if (mykase.language == "") { 
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
				carrier_partie.adhocs = new AdhocCollection([], {case_id: self.model.get("case_id"), corporation_id: carrier_partie.attributes.corporation_id});
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
		
		$(this.el).html(this.template({notes: notes, case_id: this.model.get("case_id"), display_mode: this.model.get("display"), partie_id: this.model.get("partie_id"), partie_type: this.model.get("partie_type"), note_filter_options: note_filter_options, case_status: case_status, case_substatus: case_substatus, attorney: attorney, worker: worker, rating: rating}));
		
		tableSortIt("note_listing", 10);
		
		setTimeout(function(){
			$(".pager").hide();
			$(".pager").css("position","absolute");
			$(".pager").css("top","90px");
			$(".pager").css("left","200px");
			$(".pager").show();
		}, 150);
		
		//from partie_cards.js
		if (blnQuickNotes) {
			setTimeout(function() {
				$("#typeFilter").val("quick");
				$("#typeFilter").trigger("change");
			}, 1000);
			blnQuickNotes = false;
		}
		setTimeout(function() {
			$("#note_list_outer_div").css("height", (window.innerHeight - 200) + "px");
		}, 777);
		return this;
    },
	expandNote: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		/*
		$("#partialnote_" + theid).fadeOut(function() {
			$("#fullnote_" + theid).fadeIn();
		});
		*/
		//$("#partialnote_" + theid).html($("#fullnote_" + theid).html())
		$("#full_note_holder_" + theid).css("background", $("#partial_note_holder_" + theid).css("background"));
		$("#partial_note_holder_" + theid).hide();
		$("#full_note_holder_" + theid).show();
		$("#fullnote_" + theid).show();
	},
	shrinkNote: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		/*
		$("#fullnote_" + theid).fadeOut(function() {
			$("#partialnote_" + theid).fadeIn();
		});
		*/
		$("#full_note_holder_" + theid).hide();
		$("#partial_note_holder_" + theid).show();
	},
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	},
	confirmdeleteNote: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "notes");
	},
	clearSearch: function() {
		$("#notes_searchList").val("");
		$( "#notes_searchList" ).trigger( "keyup" );
	},
	canceldeleteNote: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteNote: function(event) {
		//we're making changes, clear the cache
		resetCurrentContent("notes");
		
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "notes");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".note_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".note_data_row_" + id).fadeOut();
			}, 2500);
		}
	},
	filterNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		if (element.value!="new_filter") {
			filterIt(element, "note_listing", "note");
		}
	},
	unVivify: function(event) {
		var textbox = $("#notes_searchList");
		var label = $("#label_search_notes");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#notes_searchList");
		var label = $("#label_search_notes");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	checkManageTypeFilter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		
		if (element.value=="new_filter") {
			composeEditNoteTypes();
		}
	}
});
window.note_print_view = Backbone.View.extend({
	render: function(){
		var mymodel = this.model.toJSON();
		
		mymodel.dateandtime = moment(mymodel.dateandtime).format('MM/DD/YYYY hh:mma');
		mymodel.type = mymodel.type.capitalizeWords();
		//per Thomas 4/21/2017
		mymodel.case_name = mymodel.case_name.toUpperCase();
		mymodel.entered_by = mymodel.entered_by.capitalizeWords();
		
		$(this.el).html(this.template(mymodel));
		
		return this;
	}
});
window.note_print_listing_view = Backbone.View.extend({
	render: function(){
		var notes = this.collection.toJSON();
		/*
		_.each( notes, function(note) {
			note.dateandtime = moment(note.dateandtime).format('MM/DD/YYYY hh:mma');
		});
		*/
		$(this.el).html(this.template({notes: notes, case_id: this.collection.case_id}));
		
		return this;
	}
});
window.red_flag_note_listing_view = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
		
		if (typeof this.model.get("partie_type")== "undefined"){
			this.model.set("partie_type", "note");
		}
    },
	events: {
		"change #typeFilter":					"filterNotes",
		"click .compose_new_note":				"newNotes",
		"click .edit_note":						"newNotes",
		"click .delete_note":					"confirmdeleteNote",
		"click .delete_yes":					"deleteNote",
		"click .delete_no":						"canceldeleteNote",
		"click #note_clear_search":				"clearSearch",
	},
    render:function () {	
		if (typeof this.template != "function") {
			this.model.set("redflag_notes", "content");
			var view = "red_flag_note_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}	
		var self = this;
		var notes = this.collection.toJSON();
		var note_filter_options = "";
		var arrTypes = [];
		_.each( notes, function(note) {
			var attach_indicator = "hidden";
			if (typeof note.attachments == "undefined") {
				note.attachments = "";
			}
			if (note.attachments!="") {
				attach_indicator = "visible";
				
				//clean up if necessary
				var arrAttach = note.attachments.split("D:/uploads/");
				if (arrAttach.length==2) {
					note.attachments = "D:/uploads/" + arrAttach[1];
				}
			}
			//removing link for edit of inter office added to notes	
			var thenote = note.note;
			note.editable = true;
			if (thenote.indexOf("(sent to ") > 0) {
				note.editable = false;
			}
			var standard_link = '<span id="preview_link_' + note.id + '" onmouseover="showAttachmentPreview(' + String.fromCharCode(39) + 'note' + String.fromCharCode(39) + ', event, ' + String.fromCharCode(39) + note.attachments + String.fromCharCode(39) + ', ' + note.case_id + ', ' + note.customer_id + ')"><i style="font-size:15px;color:#FFFFFF; cursor:pointer; visibility:' + attach_indicator + '" class="glyphicon glyphicon-paperclip"></i></span>';
							
			note.attachment_link = standard_link;
			if (note.type=="document") {
				var arrNote = thenote.split("\r\n");
				
				note.attachment_link = "";
				note.note = arrNote[1].trim();
			}
			
			if (moment(note.dateandtime).format('h:mm a')=="12:00 am") {
				note.time = "";
			} else {
				note.time = moment(note.dateandtime).format('h:mm a');
			}
			note.date = moment(note.dateandtime).format("dddd, MMMM Do YYYY");
			
			if (note.type!="") {
				if (arrTypes.indexOf(note.type) < 0) {
					arrTypes[arrTypes.length] = note.type;
					note_filter_options +='\r\n<option value="' + note.type + '">' + note.type.capitalize() + ' Note</option>';
				}
			}
			
			if (note.status == "IMPORTANT") {
				note.type = "<span style='background:blue;color:white'>IMPORTANT</span>";
			} 
			
			//watch for html
			if (typeof note.attribute != "undefined") {
				if (note.attribute.indexOf("@") > -1) {
					note.type = "Webmail";
					//note.note = "<iframe srcdoc='" + note.note.replaceAll("'", "`") + "' style='width:100%; height:300px;background:white' frameborder='0'></iframe>";
					note.note = "<iframe src='api/webmail_get.php?notes_id=" + note.id + "' style='width:100%; height:300px;background:white' frameborder='0'></iframe>";
				}
			}
		});
		//this.collection.bind("reset", this.render, this);
		
		$(this.el).html(this.template({notes: notes, case_id: this.model.get("case_id"), display_mode: this.model.get("display"), partie_id: this.model.get("partie_id"), partie_type: this.model.get("partie_type"), note_filter_options: note_filter_options}));
		
		tableSortIt("note_listing", 10);
		
		setTimeout(function(){
			$(".pager").hide();
			$(".pager").css("position","absolute");
			$(".pager").css("top","90px");
			$(".pager").css("left","200px");
			$(".pager").show();
		}, 150);
		
		return this;
    },
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	},
	confirmdeleteNote: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "notes");
	},
	clearSearch: function() {
		$("#notes_searchList").val("");
		$( "#notes_searchList" ).trigger( "keyup" );
	},
	canceldeleteNote: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteNote: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "notes");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".note_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".note_data_row_" + id).fadeOut();
			}, 2500);
		}
	},
	filterNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		filterIt(element, "note_listing", "note");
	}
});
window.note_filters_listing = Backbone.View.extend({
	initialize:function () {
    },
	events: {
		"click #select_all_filters":		"selectAll",
		"click .manage_element":			"manageElement"
	},
    render:function () {		
		var self = this;
		
		//let's cycle through the types
		var arrTypes = JSON.parse(this.model.toJSON().document_filters).notes;
		var arrRows = [];
		if (typeof arrTypes != "undefined") {
			arrTypes.forEach(function(element, index, array) {
				var checked = " checked";
				var row_display = "";
				var row_class = "active_filter";
				if (element.indexOf("|deleted") > -1) {
					checked = "";
					row_display = "none";
					row_class = "deleted_filter";
				}
				var element_display_name = element.replace("|deleted", "");
				element_display_name = element_display_name.replaceAll("_", " ").toUpperCase();
				
				var therow = "<tr style='display:" + row_display + "' class='" + row_class + "'><td class='document_type'><input type='checkbox' class='document_filter_checkbox' value='Y' id='document_type_" + index + "' name='document_type_" + index + "'" + checked + "></td><td class='document_type' style='color:black' id='element_holder_" + index + "'><a id='manage_element_" + index + "' class='manage_element' style='color:black; cursor:pointer' title='Click to edit this value'>" + element_display_name + "</a></td></tr>";
				arrRows.push(therow);
			});
		}
		var html = "<table id='document_filters_table'>" + arrRows.join("") + "</table>";
		try {
			$(this.el).html(this.template({html: html}));
		}
		catch(err) {
			var view = "note_filters_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		return this;
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".document_filter_checkbox").prop("checked", element.checked);
	},
	manageElement:function(event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var index = element_id.replace("manage_element_", "");
		var element_display_name = $("#manage_element_" + index).html();
		$("#element_holder_" + index).html("<input type='text' placeholder='Edit Filter' autocomplete='off' id='edit_element_" + index + "' name='edit_element_" + index + "' value='" + element_display_name + "' style='display:' />");
		
		/*
		$("#manage_element_" + index).fadeOut(function() {
			$("#edit_element_" + index).show();
		});
		*/
	}
});
function filterNotes(event) {
	var element = event.currentTarget;
	if (typeof element == "undefined") {
		element = event.target
	}
	event.preventDefault();
	if (element.value!="new_filter") {
		filterIt(element, "note_listing", "note");
	}
}
window.note_listing_pane = Backbone.View.extend({
    initialize:function () {
       
    },
	events: {
		"change #typeFilter":							"filterNotes",
		"change #typeFilter":							"checkManageTypeFilter",
		"click .compose_new_note":						"newNotes",
		"click .edit_note":								"newNotes",
		"click .read_more":								"expandNote",
		"click .hide_note":								"shrinkNote",
		"click .open_note":								"expandNotePanel",
		"click #hide_preview_pane":						"shrinkNotePanel",
		"click .delete_note":							"confirmdeleteNote",
		"click .delete_yes":							"deleteNote",
		"click .delete_no":								"canceldeleteNote",
		"click #note_clear_search":						"clearSearch",
		"click #label_search_notes":					"Vivify",
		"click #notes_searchList":						"Vivify",
		"focus #notes_searchList":						"Vivify",
		"blur #notes_searchList":						"unVivify",
		"click .read-more":								"readMore",
		"click .detach_doc":							"detachDocument",
		"click .note_filter_link":						"filterByLink"
	},
    render:function () {		
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "note_listing_pane";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		var self = this;
		var notes = this.collection.toJSON();
		var note_filter_options = "";
		var arrTypes = [];
		if (typeof this.model.get("homepage") == "undefined") {
			this.model.set("homepage", false);
		}
		if (typeof this.model.get("blnNewNote") == "undefined") {
			this.model.set("blnNewNote", false);
		}
		var current_row = "";
		var arrTypeCounts = [];
		_.each( notes, function(note) {
			if (typeof arrTypeCounts[note.type] == "undefined") {
				arrTypeCounts[note.type] = 0;
			}
			arrTypeCounts[note.type]++;
			//copy/paste clean up word
			note.note = cleanupNote(note.note);
			note.note = note.note.replaceAll(",", ", ");
			
			note.blnShow = true;
			row = note.dateandtime + note.note + note.status;
			
			if (current_row!=row) {
				current_row = row;
			} else {
				note.blnShow = false;
			}
			
			var attach_indicator = "hidden";
			if (note.attachments!="") {
				attach_indicator = "visible";
				
				//clean up if necessary
				var arrAttach = note.attachments.split("D:/uploads/");
				if (arrAttach.length==2) {
					//secondary check
					var arrFiles = arrAttach[1].split("/");
					var upload_customer_id = arrFiles[0];
					if (!isNaN(upload_customer_id)) {
						if (upload_customer_id!=customer_id) {
							arrFiles[0] = customer_id;
							arrAttach[1] = arrFiles.join("/");
						}
					}
					note.attachments = "D:/uploads/" + arrAttach[1];
				}
			}
			//length
			note.full_note = "";
			
			if (note.short_note.indexOf("id='readmore_") > 0) {
				note.full_note = "<div id='fullnote_" +  note.id + "' style='display:none'><div style='width:45px; text-align:center; cursor:pointer; background:white; color:black' id='hidenote_" +  note.id + "' class='hide_note white_text' title='Click to shrink note'>close</div>" + note.note.replaceAll("\r\n", "<br>") + "</div>";
				
				note.full_note = note.full_note.replaceAll('<br><br>', '');
				note.full_note = note.full_note.replaceAll('</p><br>', '</p>');
				//note.note = "<div id='partialnote_" +  note.id + "'>" + note.note.getComplete(500) + "&nbsp;<a id='readmore_" + note.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more</a></div>";
				
				note.note = note.short_note;
				note.note = note.note.replaceAll('<br><br>', '');
				note.note = note.note.replaceAll('</p><br>', '</p>');
				note.note = note.note.replaceAll('<p class="MsoNormal">&nbsp;</p>', '');
				//note.note = cleanupNote(note.note);
				note.note = note.note.replaceAll(",", ", ");
			}
			
			//removing link for edit of inter office added to notes	
			var thenote = note.note;
			note.editable = true;
			/*
			if (thenote.indexOf("(sent to ") > 0) {
				note.editable = false;
			}
			*/
			var standard_link = '<span id="preview_link_' + note.id + '" onmouseover="showAttachmentPreview(' + String.fromCharCode(39) + 'note' + String.fromCharCode(39) + ', event, ' + String.fromCharCode(39) + note.attachments + String.fromCharCode(39) + ', ' + note.case_id + ', ' + note.customer_id + ')"><i style="font-size:15px;color:#FFFFFF; cursor:pointer; visibility:' + attach_indicator + '" class="glyphicon glyphicon-paperclip"></i></span>';
							
			note.attachment_link = standard_link;
			if (note.type=="document") {
				var arrNote = thenote.split("\r\n");
				
				note.attachment_link = "";
				if (arrNote.length > 1) {
					note.note = arrNote[1].trim();
				}
			}
			
			if (moment(note.dateandtime).format('h:mm a')=="12:00 am") {
				note.time = "";
			} else {
				note.time = moment(note.dateandtime).format('h:mma');
			}
			note.date = moment(note.dateandtime).format("dddd, MMMM Do YYYY");
			
			if (note.type!="") {
				if (arrTypes.indexOf(note.type) < 0) {
					arrTypes[arrTypes.length] = note.type;
					note_filter_options +='\r\n<option value="' + note.type + '">' + note.type.capitalize() + ' Note</option>';
				}
			}
			
			if (note.status == "IMPORTANT") {
				note.type = "<span style='background:blue;color:white'>IMPORTANT</span>";
			} 
			
			//watch for html
			/*
			if (typeof note.attribute != "undefined") {
				if (note.attribute.indexOf("@") > -1 || note.attribute=="webmail_note") {
					note.type = "Webmail";
					//note.note = "<iframe srcdoc='" + note.note.replaceAll("'", "`") + "' style='width:100%; height:300px;background:white' frameborder='0'></iframe>";
					note.note = "<iframe src='api/webmail_get.php?notes_id=" + note.id + "' style='width:100%; height:300px;background:white' frameborder='0'></iframe>";
				}
			}
			*/
			var arrNoteDOIS = [];
			if (typeof note.injury_dates != "undefined") {
				if (note.injury_dates!="") {
					var arrDates = note.injury_dates.split(",");
					arrDates.forEach(function(injury_dates, index, array) {
						var arrDateArray = injury_dates.split("|");
						
						var doi_dates = moment(arrDateArray[1]).format("MM/DD/YYYY");
						if (arrDateArray[2]!="0000-00-00") {
							doi_dates += " - " + moment(arrDateArray[1]).format("MM/DD/YYYY") + " CT";
						}
						var injury_details = "<a href='#injury/" + current_case_id + "/" + arrDateArray[0] + "' class='white_text'>" + doi_dates + "</a>";
						arrNoteDOIS.push("<a href='#kases/" + note.main_case_id + "' class='white_text'>" + arrDateArray[0] + "</a> - DOI:" + injury_details);
					
					});
				}
			}
			note.note = note.note.replaceTout('color: rgb(34,  34,  34)', '');
			note.note = note.note.replaceTout('color: rgb(25,  25,  25)', '');
			note.note = note.note.replaceTout("\r\n", "<br>");
			note.note = note.note.replaceTout("\n", "<br>");
			if (arrNoteDOIS.length > 0) {
				note.injury_dates = arrNoteDOIS.join("<br>");
				
				var new_subject = "<span style='font-size:0.7em;'>From Related Cases:<br>" + note.injury_dates + "</span>";
				note.subject = note.subject + "<br><br>" + new_subject;
			}
			
			note.doi = "";
			if (note.doi_start!="" && note.doi_start!="0000-00-00") {
				note.doi = moment(note.doi_start).format("MM/DD/YYYY");
				if (note.doi_end!="" && note.doi_end!="0000-00-00") {
					note.doi += "-" + moment(note.doi_end).format("MM/DD/YYYY") + " CT";
				}
			}
			/*
			//maybe a related case
			if (typeof note.start_date != "undefined") {
				if (note.case_id != current_case_id) {
					var new_subject = "<span style='font-size:0.7em; font-style:italic'>From Related Case <a href='#kases/" + note.case_id + "' class='white_text' style='pointer:cursor'>" + note.main_case_number + "</a> - DOI:&nbsp;" + moment(note.start_date).format("MM/DD/YYYY");
					if (note.end_date!="" && note.end_date!="0000-00-00") {
						new_subject += " - " + moment(note.end_date).format("MM/DD/YYYY") + " CT";
					}
					new_subject += "</span>";
					note.subject = note.subject + "<br><br>" + new_subject;
				}
			}
			*/
		});
				
		//this.collection.bind("reset", this.render, this);
		
		//if (customer_id == 1033) { 
		
			var case_id = this.model.get("case_id");
			var kase = kases.findWhere({case_id: case_id});
			
			if (typeof kase == "undefined") {
				//get it
				var kase =  new Kase({id: case_id});
				kase.fetch({
					success: function (kase) {
						if (kase.toJSON().uuid!="") {
							kases.remove(kase.id); kases.add(kase);
							self.render();
						} else {
							//case does not exist, get out
							document.location.href = "#";
						}
						return;		
					}
				});
				return;
			}
			//console.log(kase.toJSON());
			var mykase = kase.toJSON();
			var case_status = mykase.case_status;
			var case_substatus = mykase.case_substatus;
			var attorney = mykase.attorney;
			var worker = mykase.worker;
			var rating = mykase.rating;
			//var kase = kases.findWhere({case_id: this.model.get("case_id")});
			this.model.set("case_status", case_status);
			this.model.set("case_substatus", case_substatus);
			this.model.set("attorney", attorney);
			this.model.set("worker", worker);
			this.model.set("rating", rating);
			this.model.set("interpreter_needed", mykase.interpreter_needed);
			
			setTimeout(function() {
				$("#case_number_fill_in").html(mykase.case_number);
				$("#adj_number_fill_in").html(mykase.adj_number);
				if (mykase.adj_number == "") { 
					$("#adj_slot").hide();
				}
				$("#case_status_fill_in").html(mykase.case_status);
				$("#case_substatus_fill_in").html(mykase.case_substatus);
				$("#attorney_fill_in").html(mykase.attorney);
				$("#rating_fill_in").html(mykase.rating);
				$("#worker_fill_in").html(mykase.worker);
				$("#case_date_fill_in").html(mykase.case_date);
				$("#claims_fill_in").html(mykase.claims);
				if (mykase.claims == "") { 
					//$("#claims_slot").hide();
				}
				$("#case_type_fill_in").html(mykase.case_type);
				$("#case_type").val(mykase.case_type);
				$("#language_fill_in").html(mykase.language);
				if (mykase.language == "") { 
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
					carrier_partie.adhocs = new AdhocCollection([], {case_id: self.model.get("case_id"), corporation_id: carrier_partie.attributes.corporation_id});
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
		
		//if (customer_id == 1033) { 
		$(this.el).html(this.template({notes: notes, case_id: this.model.get("case_id"), display_mode: this.model.get("display"), partie_id: this.model.get("partie_id"), partie_type: this.model.get("partie_type"), note_filter_options: note_filter_options, case_status: case_status, case_substatus: case_substatus, attorney: attorney, worker: worker, rating: rating, homepage: this.model.get("homepage")}));
		//} else {
		//	$(this.el).html(this.template({notes: notes, case_id: this.model.get("case_id"), display_mode: this.model.get("display"), partie_id: this.model.get("partie_id"), partie_type: this.model.get("partie_type"), note_filter_options: note_filter_options}));
		//}
		
		tableSortIt("note_listing", 10);
		
		setTimeout(function(){
			$(".pager").hide();
			$(".pager").css("position","absolute");
			$(".pager").css("top","90px");
			$(".pager").css("left","200px");
			$(".pager").show();
		}, 150);
		
		//from partie_cards.js
		if (blnQuickNotes) {
			setTimeout(function() {
				$("#typeFilter").val("quick");
				$("#typeFilter").trigger("change");
			}, 1000);
			blnQuickNotes = false;
		}
		
		setTimeout(function() {
			$("#note_list_outer_div").css("height", (window.innerHeight - 230) + "px");
			/*
			var maxLength = 300;
			$(".actual_note_holder").each(function(){
				var myStr = $(this).text();
				if($.trim(myStr).length > maxLength){
					var newStr = myStr.substring(0, maxLength);
					var removedStr = myStr.substring(maxLength, $.trim(myStr).length);
					$(this).empty().html(newStr);
					$(this).append(' <a href="javascript:void(0);" class="read-more">read more...</a>');
					$(this).append('<span class="more-text">' + removedStr + '</span>');
				}
			});
			*/
			self.model.set("hide_upload", true);
			showKaseAbstract(self.model);
			
			setTimeout(function() {
				$("#new_note_button_holder").hide();
			}, 778);
			
			if (self.model.get("blnNewNote")) {
				$("#new_note_list_button").trigger("click");
			}
			
			//set type
			var types = document.getElementById("typeFilter").options;
			var arrLength = types.length;
			var arrTypeSummary = [];
			for (var key in arrTypeCounts) {
				if (key!="unique" && key!="insert") {
					if (typeof key!="function") {
						//console.log(key + " - " + arrTypeCounts[key]);
						
						for(var i = 0; i < arrLength; i++) {
							var option_val = types[i].value;
							if (option_val == key) {
								types[i].text += " (" + arrTypeCounts[key] + ")";
								arrTypeSummary.push("<a style='cursor:pointer' class='note_filter_link white_text' id='note_filter_" + key + "'>" + key.replaceTout("_", " ").capitalizeWords() + " (" + arrTypeCounts[key] + ")</a>");
								break;
							}
						}
					}
				}
			}
			if (arrTypeSummary.length  > 0) {
				$("#note_type_filter_summary").html(arrTypeSummary.join("&nbsp;|&nbsp;"));
				$("#note_type_filter_summary").css("display", "inline-block");
			}
			
			$(".outer_div").on('scroll', function(){
				if ($(".note_attachment_holder").length > 0) {
					$(".note_attachment_holder").hide();
				}
			});
			
			return;
		}, 777);
		
		return this;
    },
	readMore: function() {
		$(this).siblings(".more-text").contents().unwrap();
		$(this).remove();
	},
	detachDocument: function(event) {
		if (!confirm("Press OK to confirm you wish to detach this document from the Note")) {
			return;
		}
		//console.log(event);
		var arrID = event.currentTarget.id.split("_");
		var doc = $("#note_attachment_" + arrID[1] + "_" + arrID[2]);
		var file = doc.html().replaceAll("&nbsp;", " ");
		//detach
		var url = 'api/notes/detach';
		formValues = "id=" + arrID[1];
		formValues += "&file=" + encodeURIComponent(file);

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#note_attachment_holder_" + arrID[1] + "_" + arrID[2]).css("background", "red");
					setTimeout(function() {
						$("#note_attachment_holder_" + arrID[1] + "_" + arrID[2]).fadeOut();
					}, 2500);
				}
			}
		});
	},
	hideAttach: function() {
		$(".attach_preview_panel").fadeOut();
	},
	unVivify: function(event) {
		var textbox = $("#notes_searchList");
		var label = $("#label_search_notes");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#notes_searchList");
		var label = $("#label_search_notes");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	expandNote: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		/*
		$("#partialnote_" + theid).fadeOut(function() {
			$("#fullnote_" + theid).fadeIn();
		});
		*/
		//$("#partialnote_" + theid).html($("#fullnote_" + theid).html())
		$("#full_note_holder_" + theid).css("background", $("#partial_note_holder_" + theid).css("background"));
		$("#partial_note_holder_" + theid).hide();
		$("#full_note_holder_" + theid).show();
		$("#fullnote_" + theid).show();
	},
	shrinkNote: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		/*
		$("#fullnote_" + theid).fadeOut(function() {
			$("#partialnote_" + theid).fadeIn();
		});
		*/
		$("#full_note_holder_" + theid).hide();
		$("#partial_note_holder_" + theid).show();
	},
	shrinkNotePanel: function(event) {
		event.preventDefault();
		$("#preview_pane_holder").fadeOut(function() {
			$("#preview_pane").html("");
			$("#note_list_outer_div").css("width", "100%");		
		});
		$("#note_data_row_" + this.model.get("current_note_id")).css("background", "");
		this.model.set("current_note_id", -1);
	},
	expandNotePanel: function(event) {
		$(".note_data_row_" + this.model.get("current_note_id")).css("background", "");
		this.model.set("current_note_id", -1);
		
		hidePreview();
		if (this.model.get("message_link_clicked")) {
			this.model.set("message_link_clicked", false);
			return;
		}
		var self = this;
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[3];
		
		this.model.set("current_note_id", id);
		
		this.model.set("current_background", $(".note_data_row_" + id).css("background"));
		$(".note_data_row_" + id).css("background", "#F90");
		
		var left_width = window.innerWidth - 620;
		$("#preview_pane_holder").css("width", "575px");
		$("#note_list_outer_div").css("width", left_width + "px");
		
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//load the note into the pane
			composeNewNotePane(element.id);
		});
	},
	newNotes: function(event) {
		
		var element = event.currentTarget;
		event.preventDefault();
		//composeNewNotePane(element.id);
		$("#noterow_" + this.model.get("current_note_id")).css("background", "");
		this.model.set("current_note_id", -1);
		
		hidePreview();
		$("#note_type_filter_summary").hide();
		if (this.model.get("message_link_clicked")) {
			this.model.set("message_link_clicked", false);
			return;
		}
		var self = this;
		
		var left_width = window.innerWidth - 620;
		$("#preview_pane_holder").css("width", "575px");
		$("#note_list_outer_div").css("width", left_width + "px");
		
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//load the note into the pane
			composeNewNotePane(element.id);
		});
	},
	confirmdeleteNote: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "notes");
	},
	clearSearch: function() {
		$("#notes_searchList").val("");
		$( "#notes_searchList" ).trigger( "keyup" );
	},
	canceldeleteNote: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteNote: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "notes");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".note_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".note_data_row_" + id).fadeOut();
			}, 2500);
		}
	},
	filterByLink: function(event) {
		var element = event.currentTarget;
		var filter = element.id.replace("note_filter_", "");
		
		$("#typeFilter").val(filter);
		$("#typeFilter").trigger("change");
	},
	filterNotes: function(event) {
		var element = event.currentTarget;
		if (typeof element == "undefined") {
			element = event.target;
		}
		event.preventDefault();
		if (element.value!="new_filter") {
			filterIt(element, "note_listing", "note");
		}
	},
	checkManageTypeFilter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		
		if (element.value=="new_filter") {
			composeEditNoteTypes();
		}
	}
});