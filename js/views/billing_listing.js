window.billing_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .billing_category":					"filterByCategory",
		"click #activities_clear_search":			"clearSearch",
		"click .compose_billing":					"sendbilling",
		"click .edit_event":						"newEvent",
		"click .edit_task":							"newTask",
		"click .check_all":							"checkAll",
		"click .check_thisone":						"checkSome",
		"click .restore_archives":					"restoreArchives",
		"click .read_more":							"expandbilling",
		"click .hide_billing":						"shrinkbilling",
		"change #mass_change":						"massChange",
		"click #label_search_billing":				"Vivify",
		"click #activities_searchList":				"Vivify",
		"focus #activities_searchList":				"Vivify",
		"blur #activities_searchList":				"unVivify"
	},
    render:function () {	
		if (typeof this.template != "function") {
			var view = "billing_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}		
		var self = this;

		var billings = this.collection.toJSON();
		var arrUserNickNames = [];
		var blnArchives = false;
		_.each( billings, function(billing) {
			 //archives?
			 if (customer_id == 1049) {
				 if (current_case_id < 19545) {
					if (!blnArchives) {
						var kase = kases.findWhere({case_id: current_case_id});
						var cpointer = kase.toJSON().cpointer;
						blnArchives = (billing.billing_uuid.indexOf(cpointer) == 0);
					}
				 }
			 }
			 var billing_hours = billing.hours;
			 
		 });
		 
		this.model.set("blnArchives", blnArchives);
		if (typeof this.model.get("report") == "undefined") {
			this.model.set("report", false);
		}
		if (typeof this.model.get("start_date") == "undefined") {
			this.model.set("start_date", "");
		}
		if (typeof this.model.get("end_date") == "undefined") {
			this.model.set("end_date", "");
		}
		if (this.model.get("start_date") == "00/00/0000") {
			this.model.set("start_date", moment().format("MM/DD/YYYY"));
		}
		if (this.model.get("end_date") == "00/00/0000") {
			this.model.set("end_date", moment().format("MM/DD/YYYY"));
		}
		this.model.set("user_name", "");
		if (typeof this.model.get("user_id") == "undefined") {
			this.model.set("user_id", "");
		} else {
			var theworker = worker_searches.findWhere({"user_id": this.model.get("user_id")});
			if (typeof theworker != "undefined") { 
				var the_nickname = theworker.get("nickname").toUpperCase();
				var the_username = theworker.get("user_name").toUpperCase();
				this.model.set("nickname", the_nickname);
				this.model.set("user_name", the_username);
			}
		}
		try {
			$(this.el).html(this.template({billings: billings, case_id: current_case_id}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		tableSortIt("billing_listing");
		
		$("#billing_listing").addClass("glass_header_no_padding");
		
		
		//if (customer_id == 1033) { 
		
			var case_id = this.model.get("case_id");
			var kase = kases.findWhere({case_id: case_id});
			//console.log(kase.toJSON());
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
		return this;
    },
	expandbilling: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		$("#partialbilling_" + theid).fadeOut(function() {
			$("#fullbilling_" + theid).fadeIn();
		});
	},
	shrinkbilling: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		$("#fullbilling_" + theid).fadeOut(function() {
			$("#partialbilling_" + theid).fadeIn();
		});
	},
	unVivify: function(event) {
		var textbox = $("#activities_searchList");
		var label = $("#label_search_billing");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#activities_searchList");
		var label = $("#label_search_billing");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	restoreArchives: function(event) {
		var self = this;
		var url = "../api/billing/archive/" + current_case_id;
		var this_case_id = current_case_id;
		$("#kase_content").html(loading_image + "<div class='white_text' style='text-align:center'><br>Please be patient, it may take a couple minutes to retrieve archives, because there can be up to 5000 entries per case.<br><br>Restoring activities from the Archive only needs to be done once.<br><br><span style='background:orange;color:black'>Because this action takes place on the server, you don't have to stay on this screen.  When you return in a few minutes, the archives will be there.</span></div>");
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				//refresh the page if we are still here
				if (document.location.hash.indexOf("#billing") == 0) {
					//get the activities, there are there waiting
					var activities = new ActivitiesCollection([], {case_id: current_case_id});
					activities.fetch({
						success: function(data) {
							self.model.set("holder", "kase_content");
							$('#kase_content').html(new billing_listing_view({collection: activities, model: self.model}).render().el);
							$("#kase_content").removeClass("glass_header_no_padding");
							hideEditRow();
						}
					});
				} else {
					alert("Your 'Restore from Archive' request has completed for Case ID " + this_case_id);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				// report error
				console.log(errorThrown);
			}
		});
	},
	newEvent: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEvent(element.id);
	},
	checkSome: function(event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var element_id = $(element).attr('id');
		var arrElement = element_id.split("_");
		//var billing_uuid = arrElement[2];
		
		if($('#mass_change').is(":visible")) {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		} else {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		}
		return;
		
	},
	checkAll: function(event) {
		$('.check_thisone').prop('checked', element.checked);
		if($('#mass_change').is(":visible")) {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		} else {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		}
		return;
		
	},
	massChange: function(event) {
		//alert("Hey, I'm working.");
		//return;
		var dropdown = event.currentTarget;
		var arrCheckBoxes = $('.check_thisone');
		var arrChecked = [];
		var arrLength = arrCheckBoxes.length;
		
		for(var i =0; i < arrLength; i++) {
			var element = arrCheckBoxes[i];
			//var elementsArray = elements.id.split("_");
			if (element.checked) {
				var checkbox_element = element.id;
				var arrCheckbox =  checkbox_element.split("_");
				var billing_uuid = arrCheckbox[2];
				arrChecked.push(billing_uuid);
			}
		}
		
		if (arrChecked.length==0) {
			document.getElementById(dropdown.id).selectedIndex = 0;
			return;
		}
		this.model.set("checked_boxes", arrChecked);
		var ids = arrChecked.join(", ");
		var action = dropdown.value;
		if (action != "" || action != "undefined") {
			console.log(action);
			if (action == "change_date") {
				composeDateChange(ids, "task");
			}
		} else { 
			console.log("no action");
			
		}
		//composeDelete(id, "webmail");
	},
	newTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeTask(element.id);
	},
	sendbilling: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeMessage(element.id);
	},
	filterByCategory:function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var category = element.innerHTML;
		$("#activities_searchList").val(category);
		
		$( "#activities_searchList" ).trigger( "keyup" );
	},
	clearSearch: function() {
		$("#activities_searchList").val("");
		$( "#activities_searchList" ).trigger( "keyup" );
		$("#activities_searchList").focus();
	},
});
window.billing_print_summary_view = Backbone.View.extend({
	initialize:function () {
    },
	events: {
		"click .custom_dtp_height_indicator":						"heightChange",
		"click .custom_dtp_height_indicator_right":					"heightChangeRight"
	},
	heightChange: function(){
		setTimeout(function(){
			$('.custom_dtp_height').css("marginTop", "220px");
		}, 1);
	},
	heightChangeRight: function(){
		setTimeout(function(){
			$('.custom_dtp_height').css("marginTop", "250px");
		}, 1);
	},
	render: function(){
		var activities = this.collection.toJSON();
		$(this.el).html(this.template({activities: activities, start_date: this.model.get("start_date"), end_date: this.model.get("end_date")}));
		
		setTimeout(function(){
			$('#start_dateInput').datetimepicker(
				{
					 onGenerate:function( ct ){
						//$(".custom_dtp_height").css("top", "135px");
					  },
					timepicker:false, 
					format:'m/d/Y',
					mask:false,
					onChangeDateTime:function(dp,$input){
						var start_date = $("#start_dateInput").val();
						var end_date = $("#end_dateInput").val();
						var d1 =  new Date(moment(start_date));
						var d2 =  new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$("#end_dateInput").val(end_date);
						}
						if (start_date=="" || end_date=="") {
							alert("You need both dates filled out");
							return;
						}
						document.location.href = "#activities/summary/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
					}
				}
			);
			$('#end_dateInput').datetimepicker(
				{
					 onGenerate:function( ct ){
						//$(".custom_dtp_height").css("top", "135px");
					  },
					timepicker:false, 
					format:'m/d/Y',
					mask:false,
					onChangeDateTime:function(dp,$input){
						var start_date = $("#start_dateInput").val();
						var end_date = $("#end_dateInput").val();
						var d1 =  new Date(moment(start_date));
						var d2 =  new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$("#end_dateInput").val(end_date);
						}
						if (start_date=="" || end_date=="") {
							alert("You need both dates filled out");
							return;
						}
						document.location.href = "#activities/summary/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
					}
				}
			);
			/*
			$('.range_dates').datetimepicker(
				{
					 onGenerate:function( ct ){
						//$(".custom_dtp_height").css("top", "135px");
					  },
					timepicker:false, 
					format:'m/d/Y',
					mask:false,
					onChangeDateTime:function(dp,$input){
						var start_date = $("#start_dateInput").val();
						var end_date = $("#end_dateInput").val();
						var d1 =  new Date(moment(start_date));
						var d2 =  new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$("#end_dateInput").val(end_date);
						}
						if (start_date=="" || end_date=="") {
							alert("You need both dates filled out");
							return;
						}
						document.location.href = "#activities/summary/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
					}
				}
			);
			*/
		}, 700);
		
		return this;
	}
});
window.billing_print_listing_view = Backbone.View.extend({
	initialize:function () {
    },
	render: function(){
		var self = this;
		var activities = this.collection.toJSON();
		var arrUserNickNames = [];
		var arrbillingNickname = [];
		var arrUserNames = [];
		var billing_nickname = "";
		var billing_user_name = "";
		var min_date = "";
		var max_date = "";
		
		_.each( activities, function(billing) {
			 if (!isNaN(billing.billing_user_id)) {
				if (typeof arrUserNickNames[billing.billing_user_id] == "undefined") {
					var theworker = worker_searches.findWhere({"user_id": billing.billing_user_id});
					if (typeof theworker != "undefined") { 
						var the_nickname = theworker.get("nickname").toUpperCase();
						var the_username = theworker.get("user_name").toUpperCase();
						arrUserNickNames[billing.billing_user_id] = the_nickname;
						billing.billing_user_id = the_nickname;
						
						if (arrbillingNickname.indexOf(billing.billing_user_id) < 0) {
							 arrbillingNickname.push(billing.billing_user_id);
							 arrUserNames.push(the_username);
						 }
					}
				} else {
					billing.billing_user_id = arrUserNickNames[billing.billing_user_id];
				}
			 }
			 
			if (max_date=="") {
				max_date = billing.billing_date;
			}
			min_date = billing.billing_date;
		});
		var case_id = "";
		var case_name = "";
		if (typeof self.model != "undefined") {
			if (typeof self.model.get("case_id") != "undefined") {
				case_id = self.model.get("case_id");
				case_name = self.model.get("name");
			}
			if (typeof self.model.get("start_date") != "undefined") {
				min_date = self.model.get("start_date");
			}
			if (typeof self.model.get("end_date") != "undefined") {
				max_date = self.model.get("end_date");
			}
			if (arrbillingNickname.length == 1) {
				billing_nickname = arrbillingNickname[0];
				billing_user_name = arrUserNames[0];
			}
			min_date = moment(min_date).format("MM/DD/YY");
			max_date = moment(max_date).format("MM/DD/YY");
		} else {
			min_date = moment().format("MM/DD/YY");
			max_date = moment().format("MM/DD/YY");
		}
		
		$(this.el).html(this.template({activities: activities, case_id: this.collection.case_id, billing_nickname: billing_nickname,  billing_user_name: billing_user_name, billing_start: min_date, billing_end: max_date, case_id: case_id, case_name: case_name}));
		
		setTimeout(function(){
			$('.range_dates').datetimepicker(
				{
					timepicker:false, 
					format:'m/d/Y',
					mask:false,
					onChangeDateTime:function(dp,$input){
						var start_date = $("#start_dateInput").val();
						var end_date = $("#end_dateInput").val();
						var d1 =  new Date(moment(start_date));
						var d2 =  new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$("#end_dateInput").val(end_date);
						}
						if (start_date=="" || end_date=="") {
							alert("You need both dates filled out");
							return;
						}
						document.location.href = "#billing_list/" + self.model.get("user_id") + "/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
					}
				}
			);
		}, 700);
		
		return this;
	}
});
window.medical_billing_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .edit_medicalbilling":				"editMedicalBilling",
		"click .delete_medicalbilling":				"confirmdeleteBilling",
		"click #medicalbilling_button":				"newMedicalBilling"					
	},
    render:function () {	
		if (typeof this.template != "function") {
			var view = "medical_billing_listing_view";
			var extension = "php";
			loadTemplate(view, extension, this);
			return "";
	   	}		
		var self = this;
		var total_billed = 0;
        var total_paid = 0;
        var total_adjusted = 0;
		var embedded = this.model.get("embedded");
		
		var medical_billings = this.collection.toJSON();
		 _.each( medical_billings, function(medical_billing) {
			var finalized = medical_billing.finalized;
            if (finalized!="0000-00-00") {
                finalized = moment(medical_billing.finalized).format("MM/DD/YY");
            } else {
                finalized = "";
            }
			medical_billing.finalized = finalized;
			
			var bill_date = medical_billing.bill_date;
            if (bill_date!="0000-00-00") {
                bill_date = moment(medical_billing.bill_date).format("MM/DD/YY");
            } else {
                bill_date = "";
            }
			
			medical_billing.bill_date = bill_date;
			
			
			//override
			if (medical_billing.override!="") {
				medical_billing.override = "<span style='background:red; color:white; padding:2px'>$" + formatDollar(medical_billing.override) + "</span>";
			}
            total_billed += Number(medical_billing.billed);
            total_paid += Number(medical_billing.paid);
            total_adjusted += Number(medical_billing.adjusted);
		 });
		 
		$(this.el).html(this.template({
				medical_billings: medical_billings, 
				case_id: self.model.get("case_id"), 
				partie_id: self.model.get("partie_id"),
				total_billed: total_billed,
				total_paid: total_paid,
				total_adjusted: total_adjusted,
				embedded: embedded
			}));
		
		setTimeout(function() {
			$("#medical_billing_listing").css("font-size", "1.1em");
			
			if (document.location.hash.indexOf("#medicalsummary/")==0) {
				$(".glass_header").hide();
				$("#medical_billing_totals_row").hide();
				$("#medical_billing_listing").css("width", "100%");
			}
		}, 700)
		return this;
	},
	newMedicalBilling:function(event) {
		event.preventDefault();
		composeMedicalBilling();
	},
	editMedicalBilling:function(event) {
		event.preventDefault();
		var element_id = event.currentTarget.id;
		composeMedicalBilling(element_id);
	},
	confirmdeleteBilling: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[2];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "medicalbilling");
	}
});
window.other_billing_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .edit_otherbilling":		"editOtherBilling",
		"click .delete_otherbilling":	"confirmdeleteBilling",
		"click #otherbilling_button":	"newOtherBilling"					
	},
    render:function () {	
		if (typeof this.template != "function") {
			var view = "other_billing_listing_view";
			var extension = "php";
			loadTemplate(view, extension, this);
			return "";
	   	}		
		var self = this;
		var total_billed = 0;
        var total_paid = 0;
        var total_adjusted = 0;
		var embedded = this.model.get("embedded");
		
		var other_billings = this.collection.toJSON();
		 _.each( other_billings, function(other_billing) {
			var finalized = other_billing.finalized;
            if (finalized!="0000-00-00") {
                finalized = moment(other_billing.finalized).format("MM/DD/YY");
            } else {
                finalized = "";
            }
			other_billing.finalized = finalized;
			
			var bill_date = other_billing.bill_date;
            if (bill_date!="0000-00-00") {
                bill_date = moment(other_billing.bill_date).format("MM/DD/YY");
            } else {
                bill_date = "";
            }
			
			other_billing.bill_date = bill_date;
			
			
			//override
			if (other_billing.override!="") {
				other_billing.override = "<span style='background:red; color:white; padding:2px'>$" + formatDollar(other_billing.override) + "</span>";
			}
            total_billed += Number(other_billing.billed);
            total_paid += Number(other_billing.paid);
            total_adjusted += Number(other_billing.adjusted);
		 });
		 
		$(this.el).html(this.template({
			other_billings: other_billings, 
			case_id: self.model.get("case_id"), 
			partie_id: self.model.get("partie_id"),
			total_billed: total_billed,
			total_paid: total_paid,
			total_adjusted: total_adjusted,
			embedded: embedded
		}));
		
		setTimeout(function() {
			$("#other_billing_listing").css("font-size", "1.1em");
			
			if (document.location.hash.indexOf("#othersummary/")==0) {
				$(".glass_header").hide();
				$("#other_billing_totals_row").hide();
				$("#other_billing_listing").css("width", "100%");
			}
		}, 700)
		return this;
	},
	newOtherBilling:function(event) {
		event.preventDefault();
		composeOtherBilling();
	},
	editOtherBilling:function(event) {
		event.preventDefault();
		var element_id = event.currentTarget.id;
		composeOtherBilling(element_id);
	},
	confirmdeleteBilling: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[2];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "otherbilling");
	}
});
window.medical_summary_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #print_medical_summary":					"printMedicalSummary"			
	},
    render:function () {	
		if (typeof this.template != "function") {
			var view = "medical_summary_listing_view";
			var extension = "php";
			loadTemplate(view, extension, this);
			return "";
	   	}		
		var self = this;
		
		var medical_summarys = this.collection.toJSON();
		 
		$(this.el).html(this.template({
				medical_summarys: medical_summarys, 
				case_id: self.model.get("case_id")
			}));
		
		setTimeout(function() {
			$("#medical_summary_listing").css("font-size", "1.1em");
		}, 700)
		return this;
	},
	printMedicalSummary: function() {
		var url = "report.php#medicalsummary/" + current_case_id;
		window.open(url);
	}
});
window.medical_summary_listing_print = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .provider_listing":			"listProviderBilling"	
	},
    render:function () {	
		if (typeof this.template != "function") {
			var view = "medical_summary_listing_print";
			var extension = "php";
			loadTemplate(view, extension, this);
			return "";
	   	}		
		var self = this;
		
		var medical_summarys = this.collection.toJSON();
		 
		$(this.el).html(this.template({
				medical_summarys: medical_summarys, 
				case_id: self.model.get("case_id"),
				case_name: self.model.get("case_name"),
			}));
		
		return this;
	},
	listProviderBilling: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var corporation_id = arrID[arrID.length - 2];
		var summary_id = arrID[arrID.length - 1];
		var case_id = this.model.get("case_id");
		var medical_billings = new MedicalBillingCollection({corporation_id: corporation_id, case_id: case_id});
		medical_billings.fetch({
			success: function(data) {
				var my_model = new Backbone.Model;
				my_model.set("holder", 'medical_billings_' + summary_id);
				my_model.set("case_id", case_id);
				my_model.set("partie_id", corporation_id);
				my_model.set("embedded", false);
				$('#medical_billings_' + summary_id).html(new medical_billing_listing_view({collection: data, model: my_model}).render().el);	
				//now show 
				$("#medical_bills_holder_"	 + summary_id).fadeIn();		
			}
		});
	}
});