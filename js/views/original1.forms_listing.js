window.form_listing = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
    },
	events: {
		"click .form_action": 					"reactform",
		"click .delete_form": 					"confirmdeleteform",
		"click .delete_yes":					"deleteform",
		"click .delete_no":						"canceldeleteform",
		"click .read_holders":					"readform",
		"click .open_form":						"openform",
		"click .open_forms":					"openDayforms",
		"mouseover #form_preview_panel": 		"freezePreview",
		"mouseover .form_preview_link": 		"freezePreview",
		"click .create_eams": 					"newPDF",
		"click #forms_clear_search":			"clearSearch",
		"click #label_search_notes":			"Vivify",
		"click #forms_searchList":				"Vivify",
		"focus #forms_searchList":				"Vivify",
		"blur #forms_searchList":				"unVivify"
	},
	unVivify: function(event) {
		var textbox = $("#forms_searchList");
		var label = $("#label_search_forms");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#forms_searchList");
		var label = $("#label_search_forms");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#forms_searchList').focus();
		}
	},
	clearSearch: function() {
		$("#forms_searchList").val("");
		$( "#forms_searchList" ).trigger( "keyup" );
	},
	newPDF: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEams(element.id);
	},
	openform: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var id = element.id.split("_")[1];
		if ($(".form_listing #form_row_" + id).css("display")== "none") {
			$(".form_listing #form_row_" + id).fadeIn();
			setTimeout(function() { 
				var form_height = $(".form_listing #form_row_" + id).height();
				$("#pager").css("margin-top", form_height + "px");
			}, 200);
		} else {
			$(".form_listing #form_row_" + id).fadeOut();
		}
	},
	openDayforms: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var date_class = element.id.split("_")[2];
		if ($(".form_row_" + date_class).css("display")== "none") {
			$(".form_row_" + date_class).fadeIn();
			setTimeout(function() { 
				var form_height = $(".form_row_" + date_class).height();
				$("#pager").css("margin-top", form_height + "px");
			}, 200);
		} else {
			$(".form_row_" + date_class).fadeOut();
		}
	},
	reactform: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeform(element.id);
	},
	readform: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var id = element.id.split("_")[1];
		$("#read_holder_" + id).fadeOut(
			function() {
				$("#form_row_" + id).fadeIn();
				$("#action_holder_" + id).fadeIn();
				//mark the row as read
				var url = 'api/forms/read';
				formValues = "id=" + id;
		
				$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
					success:function (data) {
						if(data.error) {  // If there is an error, show the error forms
							saveFailed(data.error.text);
						} else {
							//console.log(data);
							//refresh the new form indicator
							checkInbox();
						}
					}
				});
			}
		);
	},
	confirmdeleteform: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[1];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element);	
		$("#confirm_delete").css({display: "none", top: arrPosition[0] - 20, left: arrPosition[1] - 350, position:'absolute'});
		$("#confirm_delete").fadeIn();
		
	},
	canceldeleteform: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteform: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "forms");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteform(event);
			$(".form_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".form_row_" + id).fadeOut();
			}, 2500);
		}
		/*
		var self = this;
		var element = event.target;
		var id = element.id.split("_")[1];
		event.preventDefault();
		
		var url = "api/forms/delete";
		formValues = "id=" + id + "&table_name=forms";

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                if(data.error) {  // If there is an error, show the error forms
                    saveFailed(data.error.text);
                }
                else { // If not, send them back to the home page
					self.collection.remove(kases.get(id));
					//mark the row as red
					$(".form_row_" + id).css("background", "red");
					//remove it
					setTimeout(function(){
						$(".form_row_" + id).fadeOut();
					}, 1500);
                }
            }
        });
		*/
	},
	freezePreview: function() {
		 freezeformPreview();
	},
    render:function () {		
		var self = this;
		
		if (typeof this.model.get("homepage") == "undefined") {
			this.model.set("homepage", false);
		}
		
		this.collection.bind("reset", this.render, this);
		
		try {
			$(this.el).html(this.template({forms: this.collection.toJSON(), case_id: this.model.get("case_id"), title: this.model.get("title"), first_column_label: this.model.get("first_column_label"), receive_label: this.model.get("receive_label"), homepage: this.model.get("homepage")}));

		}
		catch(err) {
			var view = "form_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}		
		setTimeout(function(){
			var size = 100;
			if (self.model.get("homepage")) {
				size = 20;
			}
			tableSortIt("form_listing", size);
		}, 100);
		
		setTimeout(function(){
			$(".pager").hide();
			/*
			$(".pager").css("position","absolute");
			$(".pager").css("top","-10px");
			$(".pager").css("left","200px");
			$(".pager").show();
			*/
		}, 150);
		//if (customer_id == 1033) { 
		
			//var case_id = this.model.get("case_id");
			var case_id = current_case_id;
			var kase = kases.findWhere({case_id: case_id});
			console.log(kase.toJSON());
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
    }

});