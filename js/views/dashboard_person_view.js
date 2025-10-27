window.dashboard_person_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
		"click .delete_image":									"confirmdeleteDocument",
		"click #picture_holder":								"expandImage",
  		"click #dashboard_person_all_done":						"doTimeouts"
    },
    render:function () {
		var self = this;
		$(this.el).html(this.template());
		//put applicant info in subview holder
		return this;
	},
	expandImage: function(event) {
		/*
		event.preventDefault();
		var element = event.currentTarget;
		*/
		var the_element = $("#applicant_img");
		if (the_element.hasClass("applicant_large_img")) {
			the_element.removeClass("applicant_large_img");
			the_element.addClass("applicant_img");
			$('#picture_holder').prop("title", "Click to expand image");
		} else {
			the_element.removeClass("applicant_img");
			the_element.addClass("applicant_large_img");
			$('#picture_holder').prop("title", "Click to shrink image");
		}
	},
	confirmdeleteDocument: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		composeDelete(id, "document");
	},
	doTimeouts: function(event) {
		var self = this;
		var person_model = self.model.clone();
		person_model.set("glass", "card_dark_1");
		person_model.set("holder", "person_holder");
		if (typeof this.model.get("injury_id") != "undefined") {
			person_model.set("injury_id", this.model.get("injury_id"));
		}
		$('#person_holder').html(new person_view({model: person_model}).render().el);
		
		
		var kase = kases.findWhere({case_id: self.model.get("case_id")});
		var kase_date = "";
		if (kase) { 
			kase_date = kase.get("case_date");
		}

		var birthday = $("#dobInput").val();
		self.model.set("dash_age", "");
		self.model.set("rating_age", "");
		if (birthday != "" && typeof birthday != "undefined") {
			age = birthday.getAge();
			var dashboard_age = age;
			self.model.set("dash_age", dashboard_age);
			if (kase_date!="") {
				var rating_age = birthday.getAge(new Date(kase_date));
				self.model.set("rating_age", rating_age);
			}
		}
		var kai_model = self.model.clone();
		kai_model.set("glass", "card_dark_1");
		kai_model.set("holder", "kai_holder");
		$('#kai_holder').html(new kai_view({model: kai_model}).render().el);
		
		var kase_type = kase.get("case_type");
		var blnWCAB = isWCAB(kase_type);
		// && (customer_id == 1033 || customer_id == 1057)
		if (blnWCAB) {
			var work_history = new WorkHistory({case_id: self.model.get("case_id")});
			work_history.fetch({
				success: function(work_history) {
						work_history.set("holder", "work_holder");
						work_history.set("glass", "card_dark_1");
						$("#work_holder").html(new work_history_earnings_view({model: work_history}).render().el);
						
						var work_disability = work_history.clone();
						work_disability.set("holder", "disability_holder");
						work_disability.set("glass", "card_dark_1");
						$("#disability_holder").html(new work_history_disability_view({model: work_disability}).render().el);
						
						var work_compensation = work_history.clone();
						work_compensation.set("holder", "compensation_holder");
						work_compensation.set("glass", "card_dark_1");
						$("#compensation_holder").html(new work_history_compensation_view({model: work_compensation}).render().el);
					}
			});
		}
		
		self.model.set("glass", "card_dark_1");
		$('#image_holder').html(new person_image({model: self.model}).render().el);
		$("#queue").css("height", "50px");
		
		//let's get any image
		kase_documents = new DocumentCollection([], { case_id: self.model.get("case_id"), attribute: "applicant_picture" });
		kase_documents.fetch({
			success: function(data) {
				if (data.toJSON().length > 0) {
					//var customer_id = data.toJSON()[0].customer_id;
					if (typeof data.toJSON()[0].document_filename != "undefined") {
						var document_filename = data.toJSON()[0].document_filename;
						if (document_filename!="") {
							$('#picture_holder').html("<img src='D:/uploads/" + customer_id + "/" + current_case_id + "/" + document_filename + "' class='applicant_img' id='applicant_img'><br><span style='font-size:0.8em; color:white'>" + document_filename + "&nbsp;<a id='deleteimage_" + data.toJSON()[0].document_id + "' class='delete_image' style='cursor:pointer'><i class='glyphicon glyphicon-trash' style='color:#FA1616;'></i></span></a>");
							/*
							if (customer_id == "1033") {
								var url = 'api/image_rotate.php';
								console.log('rotating ... ');
								var formValues = "fullpath=uploads/" + customer_id + '/' + current_case_id + '/' + document_filename + "&degrees=90";
								//console.log(formValues);
								//return;
								$.ajax({
									url:url,
									type:'POST',
									dataType:"json",
									data: formValues,
									success:function (data) {
										if(data.error) {  // If there is an error, show the error messages
											console.log(data.error.text);
											self.saveFailed(data.error.text);
										} else { 
											  $('#picture_holder').html("<img src='" + data.full_path + "' class='applicant_img'>");
										}
									}
								});	
							}
							*/
						}
					}
				}
			}
		});
		
		//only show notes/prior treatment for existing applicant
		if (this.model.id > 0) {
			//do we have prior treatment
			var applicant_prior_treatments = new PriorTreatments([], {person_id: self.model.id});
			applicant_prior_treatments.fetch({
				success: function(data) {
					var prior_treatment_model = new Backbone.Model;
					prior_treatment_model.set({person_id: self.model.id});
					prior_treatment_model.set({case_id: self.model.get("case_id")});
					prior_treatment_model.set({"holder": "applicant_prior_treatment"});
					$('#applicant_prior_treatment').html(new prior_treatment_listing_view({collection: data, model:prior_treatment_model}).render().el);	
				}
			});
			
			//do we have any notes
			var applicant_notes = new NotesByType([], {type: "applicant", case_id: current_case_id});
			applicant_notes.fetch({
				success: function(data) {
					var note_list_model = new Backbone.Model;
					note_list_model.set("display", "sub");
					note_list_model.set("partie_type", "applicant");
					note_list_model.set("case_id", current_case_id);
					note_list_model.set("partie_id", self.model.id);
					$('#applicant_notes').html(new note_listing_view({collection: data, model: note_list_model}).render().el);	
					$('#applicant_notes').css("width", "100%");
					$(".tablesorter note_listing").css("width", "100%");
					$(".note_listing").css("border", "1px solid black");
				}
			});
						
			//do we have any rx
			var applicant_rx = new RxCollection({person_id: self.model.id});
			applicant_rx.fetch({
				success: function(data) {
					var rx_list_model = new Backbone.Model;
					rx_list_model.set("holder", "applicant_rx");
					rx_list_model.set("case_id", current_case_id);
					rx_list_model.set("person_id", self.model.id);
					$('#applicant_rx').html(new rx_listing_view({collection: data, model: rx_list_model}).render().el);	
					$('#applicant_rx').css("width", "100%");
					$(".tablesorter rx_listing").css("width", "100%");
					$(".rx_listing").css("border", "1px solid black");
				}
			});
		}
		
				
		self.model.set("hide_upload", true);
		showKaseAbstract(self.model);
    }
});
