window.bodyparts_view = Backbone.View.extend({
	initialize:function () {
	},
	events:{
		"click .bodyparts .save":				"saveBodyParts",
		"click .bodyparts_status":				"changeStatus",
		"click .bodyparts .save_field":			"saveBodyPartsViewField",
		"click .bodyparts .edit": 				"toggleBodyPartsEdit",
		"click .bodyparts .reset": 				"resetBodyPartsForm",
		"click .scrape_bodyparts":				"scrapeBodyParts",
		"click .compose_new_note":				"newNotes",
		"click .bodyparts#all_done":			"doTimeouts"
	},
	render: function () {
		var self = this;
		var bodyparts = this.collection.toJSON();
		
		_.each( bodyparts, function(bodypart) {
			if (bodypart.bodyparts_status=="Y") {
				bodypart.checked = " checked";
				bodypart.bodyparts_status = "<span style='color:green'>&#128077;</span>";
			} else {
				bodypart.checked = "";
				bodypart.bodyparts_status = "<span style='color:red'>&#128078;</span>";
			}
		});
		
		try {
			$(this.el).html(this.template({bodyparts: bodyparts, case_id: this.model.get("case_id"), case_uuid: "", id: -1, injury_id: this.model.get("injury_id")}));
		}
		catch(err) {
			var view = "bodyparts_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
    },
	doTimeouts: function() {
		var self = this;
		
		gridsterById('gridster_bodyparts');
		
		if(this.collection.length==0){
			//editing mode right away			
			$(".bodyparts .edit").trigger("click"); 
			
			$(".bodyparts .delete").hide();
			$(".bodyparts .reset").hide();
		}
		/*
		if (customer_id == 1033) {
			$("#billing_time_dropdown_bpInput").editableSelect({
				onSelect: function (element) {
					var billing_time = $("#billing_time_dropdown_bpInput").val();
					$("#billing_time").val(billing_time);
					//alert(billing_time);
				}
			});
		}
		*/
	},
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	},
	scrapeBodyParts: function(event) {
		var self = this;
		
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var injury_id = arrElement[arrElement.length - 1];
		
		var adj_number = $("#adj_numberInput").val();
		if (adj_number=="") {
			adj_number = prompt("Please enter the ADJ", "ADJ");
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
				
				//body parts
				var formValues = "injury_id=" + injury_id + "&scraped=y";
				var bodyparts = scrape.bodyparts;
				var iCounter = 1;
			    _.each( bodyparts, function(bodypart) {
					formValues += "&bodypart" + iCounter + "=" + bodypart.name;
					iCounter++;
				});
				
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
			}
		});
	},
	changeStatus:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form

		var element_id = event.currentTarget.id;
		var id = element_id.split("_")[2];
		
		var new_status = "Y";
		if ($("#bodyparts_status_" + id).html().indexOf("color:green") > -1) {
			new_status = "N";
		}
		var formValues = "status=" + new_status + "&id=" + id;
		var url = "api/bodyparts/updatestatus";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						$("#bodyparts_status_" + id).fadeOut(function() {
							if (new_status == "N") {
								$("#bodyparts_status_" + id).html("<span style='color:red'>&#128078;</span>");
							} else {
								$("#bodyparts_status_" + id).html("<span style='color:green'>&#128077;</span>");
							}
							$("#bodyparts_status_" + id).fadeIn();
						});
					}
				}
		});
	},
	saveBodyParts:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "bodyparts");
		return;
    },
	toggleBodyPartsEdit: function(event) {
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
		$(".bodyparts_view .editing").toggleClass("hidden");
		$(".bodyparts_view .span_class").removeClass("editing");
		$(".bodyparts_view .input_class").removeClass("editing");
		
		$(".bodyparts_view .span_class").toggleClass("hidden");
		$(".bodyparts_view .input_class").toggleClass("hidden");
		$(".bodyparts_view .input_holder").toggleClass("hidden");
		$(".button_row.bodyparts").toggleClass("hidden");
		$(".edit_row.bodyparts").toggleClass("hidden");
	},
	
	resetBodyPartsForm: function(event) {
		event.preventDefault();
		this.toggleBodyPartsEdit(event);
		//this.render();
		//$("#address").hide();
	}
});
var checkBodyDoubles = function(obj) {
	//you cannot you cannot pick the same body parts numbers
	var obj_value = obj.value;
	var obj_id = obj.id;
	var bodyparts = $('.bodypart');
	
	for(element in bodyparts) {
		//check value, match
		if (bodyparts[element].id!= obj_id) {
			if (bodyparts[element].value != "") {
				if (bodyparts[element].value == obj_value) {
					var diplay_value = bodyparts[element].options[bodyparts[element].selectedIndex].text;
					alert("The Body Part Code [" +  diplay_value + "] has already been selected.");
					obj.value = "";
					return;
				}
			}
		}
	}
}