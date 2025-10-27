var notes_timeout_id;
window.note_listing_view_mobile = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
		
		if (typeof this.model.get("partie_type")== "undefined"){
			this.model.set("partie_type", "note");
		}
    },
	events: {
		"change #typeFilter":							"filterNotes",
		"click .compose_new_note":						"newNotes",
		"click .edit_note":								"newNotes",
		"click .delete_note":							"confirmdeleteNote",
		"click .delete_yes":							"deleteNote",
		"click .delete_no":								"canceldeleteNote",
		"click #note_clear_search":						"clearSearch",
		"click #label_search_notes":					"Vivify",
		"click #notes_searchList":						"Vivify",
		"focus #notes_searchList":						"Vivify",
		"blur #notes_searchList":						"unVivify",
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
    render:function () {		
		var self = this;
		var notes = this.collection.toJSON();
		var note_filter_options = "";
		var arrTypes = [];
		var new_subject = "";
		_.each( notes, function(note) {
			var attach_indicator = "hidden";
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
			/*
			if (thenote.indexOf("(sent to ") > 0) {
				note.editable = false;
			}
			*/
			var standard_link = '<span onmouseover="showAttachmentPreview(' + String.fromCharCode(39) + 'note' + String.fromCharCode(39) + ', event, ' + String.fromCharCode(39) + note.attachments + String.fromCharCode(39) + ', ' + note.case_id + ', ' + note.customer_id + ')" onmouseout="hideMessagePreview()"><i style="font-size:15px;color:#FFFFFF; cursor:pointer; visibility:' + attach_indicator + '" class="glyphicon glyphicon-paperclip"></i></span>';
							
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
					note.note = "<iframe src='api/webmail_get.php?notes_id=" + note.id + "' style='width:430px; height:300px;background:white' frameborder='0'></iframe>";
				}
			}
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
				note.injury_dates = arrNoteDOIS.join(",<br/>");
				
				new_subject = "<span style='font-size:1em;'>From Related Cases:" + note.injury_dates + "</span>";
				note.subject = note.subject;
			}
		});
		
		$(this.el).html(this.template({notes: notes, case_id: this.model.get("case_id"), display_mode: this.model.get("display"), partie_id: this.model.get("partie_id"), partie_type: this.model.get("partie_type"), note_filter_options: note_filter_options, new_subject: new_subject}));
		
		tableSortIt("note_listing_mobile", 10);
		
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
window.notes_view_mobile = Backbone.View.extend({
	render: function () {
		var kase = kases.findWhere({case_id: current_case_id});
		this.model.set("case_name","");
		console.log('kase',kase.toJSON());
		if (typeof kase != "undefined") {
			var case_name = kase.toJSON().name
			this.model.set("case_name", case_name);
			this.model.set("uuid", kase.toJSON().uuid); // solulab code 22-04-2019
		}
		console.log('Model view',this.model.toJSON());
		$(this.el).html(this.template(this.model.toJSON()));
        return this;
	},
	
	events:{
		"click .notes .save":					"scheduleAddNote",
		"click #notes_all_done":				"doTimeouts"
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
	doTimeouts: function(event) {
		$("#noteInput").cleditor({
			width:530,
			height: 130,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
	}
});