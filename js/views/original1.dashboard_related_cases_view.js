window.dashboard_related_cases_view = Backbone.View.extend({

    initialize:function () {
        //this.collection.on("change", this.render, this);
		//this.collection.on("add", this.render, this);
    },
	events: {
		"click .delete_icon":			"confirmdeletePartie",
		"click .compose_new_exam": 		"composeExam",
		"click #add_case":				"composeRelated",
		"click .unrelate":				"unRelate",
		"click .delete_yes":			"deletePartie",
		"click .delete_no":				"canceldeletePartie",
		"click #edit_quicknote":		"editQuickNote",
		"click #save_quicknote":		"saveQuickNote",
		"click .compose_new_note": 		"newNotes",
		"click .compose_new_envelope":	"newEnvelope",
		"click .compose_pdf_envelope":	"newPDFEnvelope",
		"click #related_list_all_done":	"doTimeouts"
	},
    render:function () {		
		var self = this;
		var injuries = this.collection.toJSON();
		try {
			$(this.el).html(this.template({injuries: injuries, case_id: this.model.get("case_id"), case_number: this.model.get("case_number")}));
		}
		catch(err) {
			var view = "dashboard_related_cases_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		var kase = kases.findWhere({case_id: self.model.get("case_id")});
		var case_number = kase.get("case_number");
		this.model.set("case_number", case_number);
		
		
		setTimeout(function() {
			self.model.set("hide_upload", true);
			showKaseAbstract(self.model);
		}, 750);
		
		return this;
	},
	composeRelated: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeRelated(element.id);
	},
	unRelate: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var injury_id = element.id.split("_")[1];
		var url = "api/kase/unrelate";
		var formValues = "case_id=" + current_case_id + "&injury_id=" + injury_id;
		
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
					saveFormFailed(data.error.text);
				} else { 
					$("#relatedGrid_" + injury_id).fadeOut();
					$("#related_row_" + injury_id).fadeOut();
				}
			}
		});
	},
	doTimeouts: function() {
		var self = this;
		
		gridsterById("gridster_related_cases");
		
		var injuries = this.collection.toJSON();
		_.each( injuries, function(injury) {
			
			var bodyparts = new BodyPartsCollection([], { case_id: injury.main_case_id, injury_id: injury.injury_id });
			bodyparts.fetch({
				success: function(bodyparts) {
					var arrBodyPartsName = [];
					_.each( bodyparts.toJSON(), function(bodypart) {
						arrBodyPartsName.push(bodypart.description);
					});
					$('#body_parts_related_' + injury.injury_id).html(arrBodyPartsName.join(", "));
				}
			});
			var parties = new Parties([], { case_id: injury.main_case_id, case_uuid: injury.main_case_uuid });
			parties.fetch({
				success: function(parties) {
					var carriers = parties.where({"type": "carrier"});
					var arrCarriers = [];
					var arrExaminers = [];
					_.each(carriers , function(carrier) {
						var thecarrier = carrier.get("company_name");
						var theexaminer = carrier.get("full_name");
						arrCarriers.push(thecarrier);
						arrExaminers.push(theexaminer);
					});
					$('#carrier_related_' + injury.injury_id).html(arrCarriers.join(", "));
					$('#examiner_related_' + injury.injury_id).html(arrExaminers.join(", "));
				}
			});
			
		});
	},
	confirmdeletePartie: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#data_list_" + id).fadeOut("slow", function() {
			$("#confirm_delete_" + id).fadeIn();
		})
	},
	canceldeletePartie: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_" + id).fadeOut("slow", function() {
			$("#data_list_" + id).fadeIn();
		})
	},
	deletePartie: function(event) {
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		deleteElement(event, id, "corporation");
	},
	editQuickNote: function(event) {
		event.preventDefault();
		$("#edit_quicknote").fadeOut(function() {
			$("#save_quicknote").fadeIn();
		});
		$("#noteSpan").fadeOut(function() {
			$("#noteInput").fadeIn();
		});
	},
	saveQuickNote: function(event) {
		event.preventDefault();
		var case_id = $("#case_id").val();
		var case_uuid = $("#case_uuid").val();
		var notes_id = $("#notes_id").val();
		var url = "api/notes/add";
		var formValues = "table_name=notes&table_id=" + notes_id + "&noteInput=" + encodeURIComponent($("#noteInput").val()) + "&table_attribute=quick&type=quick&title=Kase%20Quick%20Note";
		if (notes_id!="") {
			url = "api/notes/update";
		} else {
			formValues += "&case_uuid=" + case_uuid + "&case_id=" + case_id;
		}
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
					saveFormFailed(data.error.text);
				} else { 
					//hide the text box and save button, show the note in span
					//show the edit button
					$("#save_quicknote").fadeOut(function() {
						$("#edit_quicknote").fadeIn();
					});
					$("#noteSpan").html($("#noteInput").val());
					$("#noteInput").fadeOut(function() {
						$("#noteSpan").fadeIn();
					});
				}
			}
		});
	}
});