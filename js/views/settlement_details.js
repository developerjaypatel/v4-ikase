window.settlement_view = Backbone.View.extend({
    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	initialize:function () {
		_.bindAll(this);
    },
	 events:{
        "click .settlement .delete":					"deleteSettlementView",
		"click .settlement .save":						"saveSettlement",
		"click .settlement .save_field":				"saveSettlementViewField",
		"click .settlement .edit": 						"toggleSettlementEdit",
		"click .settlement .reset": 					"resetSettlementForm",
		"click .kase .calendar": 						"showCalendar",
		"keyup .settlement .input_class": 				"valueSettlementViewChanged",
		"dblclick .settlement .gridster_border": 		"editSettlementViewField",
		"dblclick #notesGrid": 							"editSettlementViewNotesField",
		"click #settlement_all_done":					"doTimeouts"
    },
	
    render: function () {
		var self = this;
		console.log(this);
		console.log(' new this.model data');
		var mymodel = this.model.toJSON();
		if (isDate(mymodel.date_settled) && mymodel.date_settled!="0000-00-00") {
			mymodel.date_settled = moment(mymodel.date_settled).format("MM/DD/YYYY");
		} else {
			mymodel.date_settled = "";
		}
		if (isDate(mymodel.date_approved) && mymodel.date_approved!="0000-00-00") {
			mymodel.date_approved = moment(mymodel.date_approved).format("MM/DD/YYYY");
		} else {
			mymodel.date_approved = "";
		}
		if (isDate(mymodel.date_fee_received) && mymodel.date_fee_received!="0000-00-00") {
			mymodel.date_fee_received = moment(mymodel.date_fee_received).format("MM/DD/YYYY");
		} else {
			mymodel.date_fee_received = "";
		}
		//goats
		try {
			$(this.el).html(this.template(mymodel));		
		}
		catch(err) {
			var view = "settlement_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
    },
	toggleSettlementEdit: function(event) {
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
		$(".settlement_view .editing").toggleClass("hidden");
		$(".settlement_view .span_class").removeClass("editing");
		$(".settlement_view .input_class").removeClass("editing");
		
		$(".settlement_view .span_class").toggleClass("hidden");
		$(".settlement_view .input_class").toggleClass("hidden");
		$(".settlement_view .input_holder").toggleClass("hidden");
		$(".button_row.settlement").toggleClass("hidden");
		$(".settlement .token-input-list-facebook").toggleClass("hidden");
		
		$(".edit_row.settlement").toggleClass("hidden");
	},
	editSettlementField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".settlement_view_" + field_name;
		}
		editField(element, master_class);
	},
	saveSettlement:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "settlement", "settlement");
		return;
    },
	resetSettlementForm: function(event) {
		event.preventDefault();
		this.toggleSettlementEdit(event);
		//this.render();
		//$("#address").hide();
	},
	doTimeouts: function() {
		var self = this;
		var mymodel = this.model.toJSON();
		gridsterById('gridster_settlement');
		
		/*
		if(mymodel.id=="" || mymodel.id==-1){	
			$(".settlement .edit").trigger("click"); 
			$(".settlement .delete").hide();
			$(".settlement .reset").hide();
		}
		*/
		$('#date_settledInput').datetimepicker({
				timepicker:false, 
				format:'m/d/Y',
				mask:false,
				onChangeDateTime:function(dp,$input){
					self.changeInputs();
			}
		});
		$('#date_approvedInput').datetimepicker({
				timepicker:false, 
				format:'m/d/Y',
				mask:false,
				onChangeDateTime:function(dp,$input){
					//alert($input.val());
					self.changeInputs();
			}
		});
		$('#date_fees_receivedInput').datetimepicker({
				timepicker:false, 
				format:'m/d/Y',
				mask:false,
				onChangeDateTime:function(dp,$input){
					//alert($input.val());
			}
		});
		
		var theme_3 = {
			theme: "facebook", 
			tokenLimit: 1,
			onAdd: function(item) {
				$("#attorney_full_name").val(item.name);
				
			}
		};
		$("#attorneyInput").tokenInput("api/user", theme_3);
		if (self.model.get("attorney")!="") {
			if (self.model.get("attorney_full_name")==null) {
				self.model.set("attorney_full_name", self.model.get("attorney"));
			}
			$("#attorneyInput").tokenInput("add", {id: self.model.get("attorney"), name: self.model.get("attorney_full_name")});		
		}
		$(".settlement .token-input-list-facebook").css("margin-left", "90px");
		$(".settlement .token-input-list-facebook").css("margin-top", "-25px");
		$(".settlement .token-input-list-facebook").css("width", "105px");
		$(".settlement .token-input-dropdown-facebook").css("width", "150px");
		
		
		if(this.model.get("date_settled")=="" || this.model.get("date_settled")=="0000-00-00"){
			//editing mode right away
			this.model.set("editing", false);
			this.model.set("current_input", "");
			
			$(".settlement .edit").trigger("click"); 
			$(".settlement .delete").hide();
			$(".settlement .reset").hide();
			if ($(".settlement .token-input-list-facebook").hasClass("hidden")) {
				$(".settlement .token-input-list-facebook").toggleClass("hidden");
			}
			//$("#date_settledInput").focus(); 
		} else {
			if (!$(".settlement .token-input-list-facebook").hasClass("hidden")) {
				$(".settlement .token-input-list-facebook").toggleClass("hidden");
			}
			
			//do we have any notes
			var settlement_notes = new InjuryNotesByType([], {type: "settlement", injury_id: self.model.get("injury_id")});
			settlement_notes.fetch({
				success: function(data) {
					var note_list_model = new Backbone.Model;
					note_list_model.set("display", "sub");
					note_list_model.set("case_id", self.model.get("case_id"));
					note_list_model.set("partie_id", self.model.get("injury_id"));
					note_list_model.set("partie_type", "settlement");
					$('#settlement_notes_holder').html(new note_listing_view({collection: data, model: note_list_model}).render().el);	
					/*
					$('#settlement_notes').fadeIn(function() {
						$('#settlement_notes').css("width", "50%");
					});
					*/			
				}
			});
			
			//do we have any notes
			var negotiations_notes = new InjuryNotesByType([], {type: "negotiations", injury_id: self.model.get("injury_id")});
			negotiations_notes.fetch({
				success: function(data) {
					var note_list_model = new Backbone.Model;
					note_list_model.set("display", "sub");
					note_list_model.set("case_id", self.model.get("case_id"));
					note_list_model.set("partie_id", self.model.get("injury_id"));
					note_list_model.set("partie_type", "negotiations");
					$('#settlement_negotiation_notes_holder').html(new note_listing_view({collection: data, model: note_list_model}).render().el);	
					/*
					$('#settlement_notes').fadeIn(function() {
						$('#settlement_notes').css("width", "50%");
					});
					*/			
				}
			});
		}
		
		//indicate which injury got clicked
		this.showChosenInjury(mymodel.injury_id);
	},
	showChosenInjury: function(injury_id) {
		$(".injury_summaries").css("border-left", "");
		$("#injury_summary_" + injury_id).css("border-left", "2px solid white");
	}
});
window.settlement_list_view = Backbone.View.extend({
	initialize:function () {
	
	},
	events:{
		"click .settlement .save":							"saveSettlement",
		"click .settlement_fees .save":						"saveFees",
		"click .add_fee":									"newFeeAction",
		"click .pay_fee":									"newFeeAction",
		"click .fee_save":									"saveFees",	//will save everything, but it looks more intuitive
		"click .fee_delete":								"deleteFee",
		"click .settlement .edit": 							"toggleSettlementEdit",
		"click .settlement .reset": 						"resetSettlementForm",
		"focus .settlement_list_fees .input_class":			"changeInputs",
		"change #doctor_attorney":							"changeInputs",
		"click .payment":									"newPayment",
		"click .settlement_button":							"showDiv",
		
		"click #settlement_list_all_done":					"doTimeouts"
	},
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "settlement_list_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;
		
		//convert the attorney if any
		this.model.set("attorney_full_name", "");
		if (this.model.id > 0) {
			if (this.model.get("attorney")!="") {
				var the_atty = worker_searches.findWhere({user_id:this.model.get("attorney")});
				if (typeof the_atty == "undefined") {
					this.model.set("attorney_full_name", "");
					this.model.set("attorney", "");
					this.model.set("attorney_id", "");
				} else {
					this.model.set("attorney_full_name", the_atty.get("user_name"));
				}
			}
		}
		
		var date_submitted = this.model.get("date_submitted");
		if (date_submitted!="0000-00-00" && date_submitted!="") {
			this.model.set("date_submitted", moment(date_submitted).format("MM/DD/YYYY"));
		}
		if (date_submitted=="0000-00-00") {
			this.model.set("date_submitted", "");
		}
		var date_approved = this.model.get("date_approved");
		if (date_approved!="0000-00-00" && date_approved!="") {
			this.model.set("date_approved", moment(date_approved).format("MM/DD/YYYY"));
		}
		if (date_approved=="0000-00-00") {
			this.model.set("date_approved", "");
		}
		
		//clean up numbers
		if (this.model.get("amount_of_settlement").indexOf("+") < 0) {
			this.model.set("amount_of_settlement", Number(this.model.get("amount_of_settlement").replace("$", "").replace(",", "")));
			this.model.set("amount_of_settlement_span", "$" + formatDollar(this.model.get("amount_of_settlement")));
		} else {
			this.model.set("amount_of_settlement_span", this.model.get("amount_of_settlement"));
		}
		if (this.model.get("amount_of_fee").indexOf("+") < 0) {
			this.model.set("amount_of_fee", Number(this.model.get("amount_of_fee").replace("$", "").replace(",", "")));
			
			this.model.set("amount_of_fee_span", "$" + formatDollar(this.model.get("amount_of_fee")));
		} else {
			this.model.set("amount_of_fee_span", this.model.get("amount_of_fee"));
		}
		
		$(this.el).html(this.template(this.model.toJSON()));
		
		return this;
	},
	saveSettlement:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		var referral_partie = $("#referral_partie").val();
		if (referral_partie!="") {
			//we're adding a partie
			//save the recipient as corporation
			formValues = "table_name=corporation&type=referring";
			formValues += "&additional_partie=y";
			formValues += "&adhoc_fields=&case_id=" + current_case_id;
			formValues += "&company_name=" + encodeURIComponent(referral_partie);
				
			var url = "api/corporation/add";
			
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					blnSaving = false;
					if(data.error) {  // If there is an error, show the error messages
						console.log(data.error.text);
					} else { 
						$("#referral_id").val(data.id);
						//reset
						$("#referral_partie").val("");
						
						//now save settlement
						$(".settlement .save").trigger("click");
					}
				}
			});
			
			return;
		}
	
		//fee_referral
		if ($("#referral_id").val()!="") {
			//we have referral info
			var referral_id = $("#referral_id").val();
			var fee = $("#referral_source_fee").val();
			$("#referral_source_feeSpan").html(formatDollar($("#referral_source_fee").val()));
			var fee_date = $("#referral_source_date").val();
			 $("#referral_source_dateSpan").html( moment($("#referral_source_date").val()).format("MM/DD/YYYY"));
			var arrRef = {referral_id: referral_id, fee: fee, date: fee_date};
			var fee_referral = JSON.stringify(arrRef);
			$("#referral_info").val(fee_referral);
		}
	
		addForm(event, "settlement", "settlement");
		return;
    },
	saveFee:function (event) {
		/*
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		var element_id = element.id;
		var arrID = element_id.split("_");
		var recipient = arrID[arrID.length - 1];
		var fee_id = $("#fee_id_" + recipient).val();
		var injury_id = $("#injury_id").val();
		
		var url = "api/fee/update";
		if (fee_id=="" || fee_id < 1) {
			url = "api/fee/add";
		}
		
		formValues = "fee_id=" + fee_id;
	
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					self.recentKases();
				}
			}
		});
		*/
    },
	newFeeAction: function(event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var settlement_id = $(".settlement #settlement_id").val();
		
		composeNewFee(element.id, settlement_id);
    },
	deleteFee:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		if (!confirm("Press OK to confirm this deletion")) {
			return;
		}
		
		var element = event.currentTarget;
		var fee_type = element.id.replace("delete_", "");
		var id = $("#fee_id_" + fee_type).val();
		
		var blnDeleted = deleteElement(event, id, "fee", "");
		return;
    },
	saveFees:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "settlement_fees", "fees");
		return;
    },
	toggleSettlementEdit: function(event) {
		event.preventDefault();
		var self = this;
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
		$(".settlement_list_view .editing").toggleClass("hidden");
		$(".settlement_list_view .span_class").removeClass("editing");
		$(".settlement_list_view .input_class").removeClass("editing");
		
		$(".settlement_list_view .span_class").toggleClass("hidden");
		$(".settlement_list_view .input_class").toggleClass("hidden");
		$(".settlement_list_view .input_holder").toggleClass("hidden");
		$(".button_row.settlement_list").toggleClass("hidden");
		$(".settlement_list_view .token-input-list-facebook").toggleClass("hidden");
		
		$(".edit_row.settlement_list").toggleClass("hidden");
		
		this.toggleFees()
		
		var theme = {
			theme: "facebook",
			tokenLimit: 1,
			hintText: "Search for Attorneys",
			onAdd: function(item) {
			},
			onDelete: function(item) {					
				}
		};
		
		if ($(".token-input-list-facebook").length==0) {
			$("#attorneyInput").tokenInput("api/attorney", theme);
			$(".token-input-list-facebook").css("width", "127px");
			$(".token-input-list-facebook").css("margin-top", "-27px");
			$(".token-input-list-facebook").css("margin-left", "87px");
			
			if (this.model.get("attorney_full_name")!="") {
				$("#attorneyInput").tokenInput("add", {
					id: self.model.get("attorney"), 
					name: self.model.get("attorney_full_name")
				});
			}
		}
		
	},
	toggleFees: function() {
		return;
		$(".settlement_list_fees .editing").toggleClass("hidden");
		$(".settlement_list_fees .span_class").removeClass("editing");
		$(".settlement_list_fees .input_class").removeClass("editing");
		
		$(".settlement_list_fees .input_button").toggleClass("hidden");
		
		$(".settlement_list_fees .span_class").toggleClass("hidden");
		$(".settlement_list_fees .input_class").toggleClass("hidden");
		$(".settlement_list_fees .input_holder").toggleClass("hidden");
	},
	toggleFeestEdit: function() {
		if ($(".settlement_fees #partie_edit").css("display")!="none") {
			$(".settlement_fees #partie_edit").hide();
			$(".button_row.settlement_fees").toggleClass("hidden");
		}
	},
	/*saveSettlement:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "settlement", "settlement");
		
		//this.showFees();
    },
	*/
	resetSettlementForm: function(event) {
		event.preventDefault();
		this.toggleSettlementEdit(event);
	},
	newPayment: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var corp_id = "-1";
		composeCheck(element.id, corp_id);
	},
	editExam: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var arrID = element_id.split("_");
		var corp_id = arrID[arrID.length - 1];
		composeCheck(element.id, corp_id);
	},
	showDiv: function(event) {
		event.preventDefault();
		$(".holders").hide();
		$("#holders_holder").show();
		var element_id = event.currentTarget.id;
		
		var div_id = element_id.replace("button", "holder");
		$("#" + div_id).fadeIn();
		if (div_id=="settlement_negotiation_holder") {
			$("#settlement_negotiation_notes_holder").fadeIn();
		}
		
		$(".settlement_button").removeClass("btn-primary");
		$("#" + element_id).addClass("btn-primary");
		
		$(".tablesorter").css("background", "url(../img/glass_dark.png)");
		
		if ($("#distributed_date").val()!="") {
			$(".glyphicon-edit").hide();
			$(".glyphicon-trash").hide();
			$("#new_check_Disbursement").hide();
			$("#medicalbilling_button").hide();
			$("#new_deduction").hide();
		}
	},
	doTimeouts: function() {
		var self = this;
		
		gridsterById('gridster_settlement_list');
		datepickIt(".date_input", false);
		
		//adjust size
		if (zoomLevel == 100) {
			$("#holders_holder").css("width", "45vw");
		}
		if (zoomLevel > 100) {
			$("#holders_holder").css("width", "40vw");
		}
		
		$(".settlement_list_fees").css("color", "white")

		//add a fee drop down
		var feeOptions = "<option value=''>Select Type</option><option value='C_R'>C & R</option><option value='F_A'>F & A</option><option value='STIP'>STIP</option>";
		var selectFeeOptions = "<span style='color:white;padding-right:5px'>Type:&nbsp;</span><select id='fee_optionsInput' name='fee_optionsInput' class='input_class hidden'>" + feeOptions + "</select>";
		var feeSpan = "<span id='fee_optionsSpan' class='white_text span_class'></span>";
		$("#sub_category_holder_settlement_list").append("<div style='float:right'>" + selectFeeOptions + feeSpan + "</div>")
		
		//what is the option value?
		var cr = this.model.get("c_and_r");
		var fa = this.model.get("f_and_a");
		var stip = this.model.get("stip");
		var optionValue = "";
		if (cr!="" && cr!="N") {
			optionValue = "C_R";
		}
		if (fa!="" && fa!="N") {
			optionValue = "F_A";
		}
		if (stip!=""  && stip!="N") {
			optionValue = "STIP";
		}
		$("#fee_optionsInput").val(optionValue);
		if (optionValue!="") {
			$("#fee_optionsSpan").html(optionValue.replace("_", " & "));
		} else {
			$("#fee_optionsSpan").html("TBD");
		}
		
		var kase = kases.findWhere({case_id: this.model.get("case_id")});
		var kase_type = kase.get("case_type");
		var blnWCAB = isWCAB(kase_type);
		var blnSS = (kase_type.indexOf("social_security") == 0 || kase_type=="SS");
		
		if (blnSS) {
			$(".non_ssi_boxes").hide();
			$("#amount_of_settlementGrid").attr("data-col", "2");
			$("#amount_of_settlementGrid").attr("data-row", "1");
			$("#amount_of_feeGrid").attr("data-col", "3");
			$("#amount_of_feeGrid").attr("data-row", "1");
			$("#settlement_form ul").css("height", "54px");
			$("#panel_title").html("SSI Settlement");
		}
		this.model.set("blnWCAB", blnWCAB);
		this.model.set("blnSS", blnSS);
		
		//get the referral, prior attorney
		var kase_parties = new Parties([], { case_id: current_case_id, panel_title: "" });
		kase_parties.fetch({
			success: function(data) {
				var prior_partie = kase_parties.findWhere({"type": "prior_attorney"});
				if (typeof prior_partie == "undefined") {
					//hide the prior attorney fee
					$(".referring_settlement_row").hide();
				}
				var referring_partie = kase_parties.findWhere({"type": "referring"});
				if (typeof referring_partie == "undefined") {
					//indicate that there is no referring
					$("#referral_sourceSpan").html("No Referral");
				} else {
					var referral_id = referring_partie.get("corporation_id");
					$("#referral_sourceSpan").html("<a href='#parties/" + current_case_id + "/" + referral_id + "/referring' title='Click to review Referral Source' class='white_text'>" + referring_partie.get("company_name") + "</a>");
					$("#referral_id").val(referral_id);
					$("#referral_partie").css("display", "none");
					$("#referral_sourceSpan").removeClass("span_class");
					//do we have a referral fee
					var info = self.model.get("referral_info");
					if (info!="" && info!="[]") {
						var jdata = JSON.parse(info);
						$("#referral_source_fee").val(jdata.fee);
						$("#referral_source_feeSpan").html(jdata.fee);
						$("#referral_source_date").val(jdata.date);
						$("#referral_source_dateSpan").html(moment(jdata.date).format("MM/DD/YYYY"));
					}
					
				}
				
				self.showFees();
			}
		});
		
		//do we have any notes
		var settlement_notes = new InjuryNotesByType([], {type: "settlement", injury_id: self.model.get("injury_id")});
		settlement_notes.fetch({
			success: function(data) {
				var note_list_model = new Backbone.Model;
				note_list_model.set("display", "sub");
				note_list_model.set("case_id", self.model.get("case_id"));
				note_list_model.set("partie_id", self.model.get("injury_id"));
				note_list_model.set("partie_type", "settlement");
				$('#settlement_notes_holder').html(new note_listing_view({collection: data, model: note_list_model}).render().el);	
				/*
				$('#settlement_notes').fadeIn(function() {
					$('#settlement_notes').css("width", "50%");
				});
				*/			
			}
		});
		
		//do we have any notes
		var negotiations_notes = new InjuryNotesByType([], {type: "negotiations", injury_id: self.model.get("injury_id")});
		negotiations_notes.fetch({
			success: function(data) {
				var note_list_model = new Backbone.Model;
				note_list_model.set("display", "sub");
				note_list_model.set("case_id", self.model.get("case_id"));
				note_list_model.set("partie_id", self.model.get("injury_id"));
				note_list_model.set("partie_type", "negotiations");
				$('#settlement_negotiation_notes_holder').html(new note_listing_view({collection: data, model: note_list_model}).render().el);	
				/*
				$('#settlement_notes').fadeIn(function() {
					$('#settlement_notes').css("width", "50%");
				});
				*/			
			}
		});
		
		//deductions/specials
		var deductions = new DeductionCollection({case_id: current_case_id});
		deductions.fetch({
				success: function(data) {
					var deductions = data.toJSON();
					if (deductions.length > 0) {
						setTimeout(function() {
							$("#settlement_deduct_button").html("Deductions (" + deductions.length + ")");
						}, 2000);
					}
					
					var dkase = self.model.clone();
					dkase.set("holder", "#settlement_deduct_holder");
					dkase.set("page_title", "Deduction");
					dkase.set("embedded", false);
					$('#settlement_deduct_holder').html(new deduction_listing_view({collection: data, model: dkase}).render().el);
				}
		});
		
		//negotiation
		var negotiations = new NegotiationCollection({case_id: current_case_id});
		negotiations.fetch({
				success: function(data) {
					var negotiations = data.toJSON();
					if (negotiations.length > 0) {
						setTimeout(function() {
							$("#settlement_negotiation_button").html("Negotiations (" + negotiations.length + ")");
						}, 2000);
					}
					var nkase = self.model.clone();
					nkase.set("holder", "#settlement_negotiation_holder");
					nkase.set("page_title", "Negotiation");
					nkase.set("embedded", false);
					$('#settlement_negotiation_holder').html(new negotiation_listing_view({collection: data, model: nkase}).render().el);
				}
		});
		
		//check requests
		var checkrequests = new CheckRequestsCollection({case_id: current_case_id});
		checkrequests.fetch({
			success: function(data) {
				var checkreqs = data.toJSON();
				if (checkreqs.length > 0) {
					setTimeout(function() {
						$("#settlement_checkrequests_button").html("Check Requests (" + checkreqs.length + ")");
					}, 2000);
				}
				var kase = kases.findWhere({case_id: current_case_id});
				var reqkase = kase.clone();
				reqkase.set("holder", "#settlement_checkrequests_holder");
				reqkase.set("page_title", "Check Requests");
				reqkase.set("embedded", true);
				$('#settlement_checkrequests_holder').html(new checkrequest_listing_view({collection: data, model: reqkase}).render().el);
			}
		});	
		
		//costs
		var kase_disbursments = new ChecksCollection([], { case_id: current_case_id, ledger: "OUT" });
		kase_disbursments.fetch({
			success: function(data) {
				var datas = data.toJSON();
				if (datas.length > 0) {
					setTimeout(function() {
						$("#settlement_costs_button").html("Costs (" + datas.length + ")");
					}, 2000);
				}
				var newkase = self.model.clone();
				newkase.set("holder", "#settlement_costs_holder");
				newkase.set("page_title", "Disbursement");
				newkase.set("blnShowMemo", false);
				//newkase.set("embedded", true);
				$('#settlement_costs_holder').html(new check_listing_view({collection: data, model: newkase}).render().el);
			}
		})
		
		if (blnMedicalBilling) {
			//do we have any medical billings
			var medical_billings = new MedicalSummaryCollection({case_id: current_case_id});
			medical_billings.fetch({
				success: function(data) {
					var datas = data.toJSON();
					if (datas.length > 0) {
						setTimeout(function() {
							$("#settlement_med_button").html("Medical Summary (" + datas.length + ")");
						}, 2000);
					}
					var my_model = new Backbone.Model;
					my_model.set("holder", "settlement_med_holder");
					my_model.set("case_id", current_case_id);
					
					$('#settlement_med_holder').html(new medical_summary_listing_view({collection: data, model: my_model}).render().el);	
				}
			});
		}
		
		//losses
		var empty_model = new Backbone.Model;		
		empty_model.set("holder", "settlement_losses_holder");
		$("#settlement_losses_holder").html(new losses_list_view({model: empty_model}).render().el);
		
		$(".form_label_vert").css("color", "white");
		$(".form_label_vert").css("font-size", "1em");
		
		//if (login_user_id=="1315") {
			//are there other dois, if yes, show header
			var kase_dois = new KaseInjuryCollection({case_id: current_case_id});
			var injury_id = self.model.get("injury_id");
			kase_dois.fetch({
				success: function(data) {
					var kase_dois = data.toJSON();
					var arrStartDates = [];
					var arrDOIS = [];
					_.each(kase_dois , function(doi) {
						if (arrDOIS.indexOf(doi.id) < 0) {
							arrDOIS.push(doi.id);
							if (doi.id!=injury_id) {
								$("#settlement_header_holder").append("<div id='list_header_" + doi.id + "'><span style='color:white; font-size:1.2em'>Loading Related Settlement ... </span></div>");
								var settlement = new Settlement({injury_id: doi.id});
								settlement.fetch({
									success: function(data) {
										var mymodel = data.clone();
										mymodel.set("doi_id", doi.id);
										var start_date =  "No Injury Info";
										if (doi.start_date!="" && doi.start_date!="0000-00-00") {
											start_date = moment(doi.start_date).format("MM/DD/YYYY");
										}
										thedoi = 'DOI:&nbsp;' + start_date;
										if (doi.end_date != "0000-00-00") {
											thedoi += "&nbsp;-&nbsp;" + moment(doi.end_date).format("MM/DD/YYYY") + "&nbsp;CT";
										}
										var adj_number = doi.adj_number.toUpperCase();
										thedoi = adj_number + "&nbsp;|&nbsp;" + thedoi;
										mymodel.set("adj_number", adj_number);
										mymodel.set("doi", thedoi);
										mymodel.set("holder", "list_header_" + doi.id);
										
										var kase = kases.findWhere({case_id: current_case_id});
										$("#list_header_" + doi.id).html(new settlement_list_header({model: mymodel, kase: kase}).render().el);
										
										self.gridsterSettlements(doi.id);
									}
								});
							}
						}
					});
				}
			});
			//<%= settlement_id %>
			//$("#settlement_header_holder").html(new settlement_list_header({collection: this.collection, model: this.model}).render().el);
			//return;
		//}
	},
	gridsterSettlements: function(doi_id) {
		var rand = getRandomArbitrary(1500, 2220);
		setTimeout(function() {
			gridsterById('gridster_settlement_list_' + doi_id);
		}, rand);
	},
	changeInputs: function(event) {
		//this.toggleFeestEdit();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var recipient = arrID[arrID.length - 1];
		
		$("#payment_" + recipient).css("display", "none");
		$("#delete_" + recipient).css("display", "none");
		$("#save_" + recipient).css("display", "block");
	},
	getFees: function() {
		var self = this;
		var kase = self.model;
		console.log(self.model);
		//individual fees
		var arrTypes = ["attorney", "rehab", "ss", "other", "depo"];
		for (var i = 0; i < 5; i++) {
			var fee_type = arrTypes[i];
			console.log(self.model);
			var settlement_id = self.model.get("settlement_id");
			var settlement_fees = new SettlementWCFeesCollection({settlement_id: self.model.get("settlement_id"), "fee_type": fee_type});
			switch (fee_type) {
				case "depo":
					fee_type = "deposition";
					break;
				case "rehab":
					fee_type = "rehabilitation";
					break;
			}
			$("#row_info_holder_" + fee_type).hide();
			$("#row_fees_holder_" + fee_type).show();
			console.log(settlement_id);
			if (settlement_id!="") {
				settlement_fees.fetch({
						success: function(data) {
							console.log(data);
							if (data.length > 0) {
								var the_type = data.toJSON()[0].fee_type.toLowerCase();
								switch (the_type) {
									case "depo":
										the_type = "deposition";
										break;
									case "soc sec":
										the_type = "ss";
										break;
									case "rehab":
										the_type = "rehabilitation";
										break;
								}
								$("#row_info_holder_" + the_type).hide();
								$("#row_fees_holder_" + the_type).show();
								var feekase = kase.clone();
								var holder = "#fees_holder_" + the_type;
								feekase.set("holder", holder);
								feekase.set("fee_type", the_type);
								feekase.set("page_title", "Settlement Fees - " + the_type.capitalizeWords());
								feekase.set("embedded", true);
								$(holder).html(new fee_listing_view({collection: data, model: feekase}).render().el);
							}
							var blnSS = self.model.get("blnSS");
							if (blnSS) {
								$(".non_ssi_boxes").hide();
							}
						}
				});
			} else {
				var blnSS = self.model.get("blnSS");
				if (blnSS) {
					$(".non_ssi_boxes").hide();
				}
			}
		}
	},
	showFees: function() {
		var self = this;
		$("#settlement_fees").fadeIn();
		console.log(self);
		self.getFees();
		/*
		var kase_parties = new Parties([], { case_id: current_case_id });
		kase_parties.fetch({
			success: function(data) {
				var doctor_parties = kase_parties.where({"type": "medical_provider"});
				if (doctor_parties.length==0) {
					$("#doctor_attorney").hide();
					self.getFees();
					return;
				}
				var arrOptions = ["<option value=''>Select Doctor</option>"];
				for(var i = 0; i < doctor_parties.length; i++) {
					var doctor = doctor_parties[i].toJSON();
					var option = "<option value='" + doctor.corporation_id + "'>" + doctor.company_name + "</option>";
					arrOptions.push(option);
				}
				
				$("#doctor_attorney").html(arrOptions.join("\r\n"));
				
				self.getFees();
			}
		});
		*/
	}
});
window.settlement_list_header = Backbone.View.extend({
	initialize:function () {
	
	},
	events:{
		"click .settlement_hide":										"hideMe",
		"click .settlement_show":										"showMe",
		"click #settlement_list_header_all_done":						"doTimeouts"
	},
	render: function () {
		if (typeof this.template != "function") {
			var view = "settlement_list_header";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;
		
		this.model.set("the_doi_id", this.model.get("doi_id"));
		
		//convert the attorney if any
		this.model.set("attorney_full_name", "");
		if (this.model.id > 0) {
			if (this.model.get("attorney")!="") {
				var the_atty = worker_searches.findWhere({user_id:this.model.get("attorney")});
				if (typeof the_atty == "undefined") {
					this.model.set("attorney_full_name", "");
					this.model.set("attorney", "");
					this.model.set("attorney_id", "");
				} else {
					this.model.set("attorney_full_name", the_atty.get("user_name"));
				}
			}
		}
		
		var date_submitted = this.model.get("date_submitted");
		if (date_submitted!="0000-00-00" && date_submitted!="") {
			this.model.set("date_submitted", moment(date_submitted).format("MM/DD/YYYY"));
		}
		if (date_submitted=="0000-00-00") {
			this.model.set("date_submitted", "");
		}
		var date_approved = this.model.get("date_approved");
		if (date_approved!="0000-00-00" && date_approved!="") {
			this.model.set("date_approved", moment(date_approved).format("MM/DD/YYYY"));
		}
		if (date_approved=="0000-00-00") {
			this.model.set("date_approved", "");
		}
		
		//clean up numbers
		if (this.model.get("amount_of_settlement").indexOf("+") < 0) {
			this.model.set("amount_of_settlement", Number(this.model.get("amount_of_settlement").replace("$", "").replace(",", "")));
			this.model.set("amount_of_settlement_span", "$" + formatDollar(this.model.get("amount_of_settlement")));
		} else {
			this.model.set("amount_of_settlement_span", this.model.get("amount_of_settlement"));
		}
		if (this.model.get("amount_of_fee").indexOf("+") < 0) {
			this.model.set("amount_of_fee", Number(this.model.get("amount_of_fee").replace("$", "").replace(",", "")));
			
			this.model.set("amount_of_fee_span", "$" + formatDollar(this.model.get("amount_of_fee")));
		} else {
			this.model.set("amount_of_fee_span", this.model.get("amount_of_fee"));
		}
		
		$(this.el).html(self.template(this.model.toJSON()));
		
		return this;
	},
	hideMe: function(event) {
		event.preventDefault();
		var element_id = event.currentTarget.id.replace("settlement_hide_", "");
		$("#settlement_form_" + element_id).fadeOut();
		$("#settlement_show_" + element_id).fadeIn();
	},
	showMe: function(event) {
		event.preventDefault();
		event.currentTarget.style.display = "none";
		var element_id = event.currentTarget.id.replace("settlement_show_", "");
		$("#settlement_form_" + element_id).fadeIn();
	},
	doTimeouts: function() {
		var self = this;
		
		gridsterById('gridster_settlement_list_' + this.model.get("the_doi_id"));
		datepickIt(".date_input", false);
		
		//add a fee drop down
		var feeOptions = "<option value=''>Select Type</option><option value='C_R'>C & R</option><option value='F_A'>F & A</option><option value='STIP'>STIP</option>";
		var selectFeeOptions = "<span style='color:white;padding-right:5px'>Type:&nbsp;</span><select id='fee_optionsInput' name='fee_optionsInput' class='input_class hidden'>" + feeOptions + "</select>";
		var feeSpan = "<span id='fee_optionsSpan' class='white_text span_class'></span>";
		$("#sub_category_holder_settlement_list").append("<div style='float:right'>" + selectFeeOptions + feeSpan + "</div>")
		
		//what is the option value?
		var cr = this.model.get("c_and_r");
		var fa = this.model.get("f_and_a");
		var stip = this.model.get("stip");
		var optionValue = "";
		if (cr!="" && cr!="N") {
			optionValue = "C_R";
		}
		if (fa!="" && fa!="N") {
			optionValue = "F_A";
		}
		if (stip!=""  && stip!="N") {
			optionValue = "STIP";
		}
		$("#fee_optionsInput").val(optionValue);
		if (optionValue!="") {
			$("#fee_optionsSpan").html(optionValue.replace("_", " & "));
		} else {
			$("#fee_optionsSpan").html("TBD");
		}
		
		var kase = kases.findWhere({case_id: this.model.get("case_id")});
		var kase_type = kase.get("case_type");
		var blnWCAB = isWCAB(kase_type);
		var blnSS = (kase_type.indexOf("social_security") == 0 || kase_type=="SS");
		
		if (blnSS) {
			$(".non_ssi_boxes").hide();
			$("#amount_of_settlementGrid").attr("data-col", "2");
			$("#amount_of_settlementGrid").attr("data-row", "1");
			$("#amount_of_feeGrid").attr("data-col", "3");
			$("#amount_of_feeGrid").attr("data-row", "1");
			$("#settlement_form ul").css("height", "54px");
			$("#panel_title").html("SSI Settlement");
		}
		this.model.set("blnWCAB", blnWCAB);
		this.model.set("blnSS", blnSS);
		
		$(".form_label_vert").css("color", "white");
		$(".form_label_vert").css("font-size", "1em");
	}
});
window.search_settlement_view = Backbone.View.extend({
	initialize:function () {
	
	},
	events:{
		
	},
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "search_settlement_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;
		
		$(this.el).html(self.template(this.model.toJSON()));
		
		setTimeout(function() {
			var theme = {
				theme: "facebook", 
				minChars:3, 
				noResultsText:"Not Found...", 
				onAdd: function(item) {
				}
			}
			var thetype = "medical_provider";
			$("#company_nameInput").tokenInput("api/corporation/tokeninput/" + thetype, theme);
			$(".settlement .token-input-list-facebook").css("width", "278px");
			$(".token-input-dropdown-facebook").css("width","278px");
		}, 1110);
		return this;
	}
});
window.settlementsheet_view = Backbone.View.extend({
	initialize:function () {
	
	},
	events:{
		"keyup .column_percent":								"addGross",
		"change .column_percent":								"addGross",
		"keyup .column_gross":									"addGross",
		"change .column_gross":									"addGross",
		"keyup .column_fee":									"addFee",
		"change .column_fee":									"addFee",
		//"keyup .column_medical":								"addMedical",
		"keyup .column_subtotal":								"addTotalDue",
		"change .column_subtotal":								"addTotalDue",
		
		"click .save":											"saveSettlement",
		
		"click #cost_label":									"showCosts",
		"click #medsumm_label":									"showMedSumm",
		"click #subro_label":									"showSubro",
		"click #deduct_label":									"showDeductions",
		"click #checkrequest_label":							"showCheckRequests",
		"click .settlement_button":								"showDiv",
		
		"click #deductionSpan":									"overDeduction",
		"click #deduction_override_checkbox":					"overDeduction",
		"dblclick #deduction":									"showActualDeduction",
		"click #deduction_link":								"showActualDeduction",
		"keyup #deduction":										"limitDeductionOverride",
		
		"click #costSpan":										"overCost",
		"click #cost_override_checkbox":						"overCost",
		"dblclick #cost":										"showActualCost",
		"click #cost_link":										"showActualCost",
		"keyup #cost":											"limitCostOverride",
		
		"click #settlement_medical_expenses":					"overMedicalExpense",
		"click #medical_expense_override_indicator":			"overMedicalExpense",
		/*
		"dblclick #total_medical_val":							"showActualMedicalExpense",
		"click #medical_link":									"showActualMedicalExpense",
		*/
		"keyup #total_medical_expenses":						"limitMedicalExpenseOverride",
		
		"click #settlement_medical_payments":					"overMedicalPayment",
		"click #medical_payment_override_checkbox":				"overMedicalPayment",
		"keyup #total_medical_payments":						"limitMedicalPaymentOverride",
		
		"click #settlement_medical_adjustments":				"overMedicalAdjustment",
		"click #medical_adjustment_override_checkbox":			"overMedicalAdjustment",
		"keyup #total_medical_adjustments":						"limitMedicalAdjustmentOverride",
		
		"keyup .medical_expense":								"calcMedical",
		
		"click #subrogationSpan":								"overSubro",
		"click #subrogation_override_checkbox":					"overSubro",
		"dblclick #total_subrogation_val":						"showActualSubro",
		"click #subrogation_link":								"showActualSubro",
		"keyup #total_subrogation_val":							"limitSubroOverride",
		
		/*"click #settlement_losses_button":						"showLossSummary",*/
		"change .settlement_date":								"limitDates",
		"click #add_trust_check":								"newTrustCheck",
		"click #bulk_check_request":							"newCheckRequest",
		"click #settlement_second_button":						"secondSettlement",
		"click #settlement_first_button":						"firstSettlement",
		"click #settlement_main_button":						"mainSettlement",
		"click #settlement_statement":							"printStatement",
		"click #settlementsheet_view_done":						"doTimeouts"
	},
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "settlementsheet_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
		
		this.model.set("total_gross", 0);
		this.model.set("total_fee", 0);
		this.model.set("total_medical", 0);
		this.model.set("total_due", 0);
		this.model.set("total_subro", 0);
		
		this.model.set("initialize", true);
		
		var self = this;
		
		$(this.el).html(self.template(this.model.toJSON()));
		
		setTimeout(function() {
			self.getSubro();
		}, 1110);
		return this;
	},
	doTimeouts: function() {
		var self = this;
		var data = this.model.toJSON().data;
		//$(".legal2").css("visibility", "hidden");
		//$(".legal3").css("visibility", "hidden");
		
		//adjust size
		zoomLevel = Number((screen.availWidth / document.documentElement.clientWidth).toFixed(1).replace(".", "") + "0");
		var new_width = -(zoomLevel/ 2) + 95;
		$("#holders_holder").css("width", new_width + "vw");
		/*
		if (zoomLevel == 100) {
			$("#holders_holder").css("width", "45vw");
		}
		if (zoomLevel > 100) {
			$("#holders_holder").css("width", "40vw");
		}
		*/
		//alert(data + " - cost")
		if (isNaN(data.costs)) {
			//$("#costSpan").html((0.00).toFixed(2));
		}
		if (document.location.hash.indexOf("#settlementsheet")==0) {
			//we know this is the first, show a button for 2nd
			$("#settlement_main_holder").show();	
		} else {
			self.model.set("blnFirst", false);
			self.model.set("first_id", -1);
				//check if there is a 1st settlement
			var url = "api/settlementfirst/" + this.model.get("injury_id");
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				success:function (data) {
					if (typeof data.settlementsheet_id != "undefined") {
						self.model.set("blnFirst", true);
						//only one extra for now					
						$("#settlement_first_holder").show();
						self.model.set("first_id", data.settlementsheet_id);
					} else {
						if (self.model.get("status")=="D") {
							$("#settlement_second_holder").show();
						} 
						
						if (self.model.id < 0) {
							$("#settlement_dates_holder").fadeOut();
						}
					}
				}
			});
		}
		
		if (data != "") {
			var jdata = JSON.parse(this.model.toJSON().data);
			var blnMoreLegal = false;
			var referral_fee_value = 0;
			for (var key in jdata) {
				if (jdata.hasOwnProperty(key)) {
					//console.log(key + " -> " + p[key]);
					if (key=="distrib") {
						if (jdata[key]=="") {
							if (self.model.get("status")=="D") {
								//pending tho
								self.model.set("status", "P");
							}
						}
					}
					var element = document.getElementsByName(key);
					if (element.length > 0) {
						if (element[0].id == "medical_override_checkbox") {
							element[0] = document.getElementById("medical_expense_override_checkbox");
						}
						if (key=="referral_fee") {
							referral_fee_value = jdata[key];
						}
						if (key=="referral_info") {
							var info = jdata[key];
							if (info!="" && info!="[]") {
								var rdata = JSON.parse(info);
								$("#referral_id").val(rdata.referral_id);
								if ($("#referral_source_fee").val()=="" || $("#referral_source_fee").val()=="0.00") {
									if (referral_fee_value!= 0) {
										rdata.fee = referral_fee_value;
									}
									//remove any old value
									$("#referral_fee").val("");
									$("#referral_source_fee").val(rdata.fee);
								}
								$("#referral_source_date").val(rdata.date);
								
								var arrRef = {referral_id: rdata.referral_id, fee: rdata.fee, date: rdata.fee_date};
								var fee_referral = JSON.stringify(arrRef);
								$("#referral_info").val(info);
							}
						}
						//date
						if (jdata[key]!="0000-00-00") {
							/*
							if (!isNaN(jdata[key])) {
								if (key.indexOf("column_percent") > -1) {
									jdata[key] = Number(jdata[key]).toFixed(5);
								} else {
									jdata[key] = Number(jdata[key]).toFixed(2);
								}
							}
							*/
							if (key.indexOf("grossdesc") > -1) {
								var fee_number = key.replace("grossdesc", "");
								
								if (jdata[key]==0) {
									jdata[key] = "";
								}
								if (fee_number == "2" || fee_number == "3") {
									if (jdata[key] != "") {								
										$(".legal" + fee_number).css("visibility", "visible");
									} else {
										if (!blnMoreLegal) {
											//show the next showme link
											//$("#show_level_" + fee_number).show();
											blnMoreLegal = true;
										}
									}
								}
							}
							if (element[0].type=="date") {
								if (jdata[key] == "0.00" || jdata[key]=="0000-00-00") {
									jdata[key] = "";
								}
							} 
							if (key!="medtoto" && key!="medpayo" && key!="medadjo" && key!="costo" && key!="subroo" && key!="othero") {
								if (key!="table_id") {
									if (jdata[key]!="") {
										if (!isNaN(jdata[key])) {
											if (key.indexOf("pct") > -1) {
												jdata[key] = Number(jdata[key]).toFixed(5);
											} else {
												jdata[key] = Number(jdata[key]).toFixed(2);
											}
										}
									}
									element[0].value = jdata[key];
								}
							} else {
								if (jdata[key]==1) {
									element[0].checked = true;
								}
							}
						}
					}
				}
			}
		}
		
		//referral		
		var kase_parties = new Parties([], { case_id: current_case_id, panel_title: "" });
		kase_parties.fetch({
			success: function(data) {
				var referring_partie = kase_parties.findWhere({"type": "referring"});
				if (typeof referring_partie == "undefined") {
					//indicate that there is no referring
					$("#referral_sourceSpan").html("No Referral");
				} else {
					$("#referral_partie").toggleClass("hidden");
					//$("#referral_sourceSpan").html(referring_partie.get("company_name"));
					
					var referral_id = referring_partie.get("corporation_id");
					$("#referral_sourceSpan").html("<a href='#parties/" + current_case_id + "/" + referral_id + "/referring' title='Click to review Referral Source' class='white_text'>" + referring_partie.get("company_name") + "</a>");
					$("#referral_id").val(referral_id);
					$("#referral_partie").css("display", "none");
					$("#referral_sourceSpan").removeClass("span_class");
					$("#referral_sourceSpan").toggleClass("hidden");
				}
			}
		});
		
		//settlement_notes
		var settlement_notes = new NotesByType([], {type: 'settlement', case_id: current_case_id});
		settlement_notes.fetch({
			success: function(data) {
				var datas = data.toJSON();
				if (datas.length > 0) {
					setTimeout(function() {
						$("#settlement_notes_button").html("Notes (" + datas.length + ")");
					}, 2000);
				}
				var note_list_model = new Backbone.Model;
				note_list_model.set("display", "sub");
				note_list_model.set("case_id", current_case_id);
				note_list_model.set("embedded", true);
				note_list_model.set("partie_type", "settlement");
				note_list_model.set("party_array_type", "settlement");
				$('#settlement_notes_holder').html(new note_listing_view({collection: data, model: note_list_model}).render().el);
			}
		});
		
		//costs
		var kase_disbursments = new ChecksCollection([], { case_id: current_case_id, ledger: "OUT" });
		kase_disbursments.fetch({
			success: function(data) {
				var datas = data.toJSON();
				if (datas.length > 0) {
					setTimeout(function() {
						$("#settlement_costs_button").html("Costs (" + datas.length + ")");
					}, 2000);
				}
				var newkase = self.model.clone();
				newkase.set("holder", "#settlement_costs_holder");
				newkase.set("page_title", "Disbursement");
				newkase.set("blnShowMemo", false);
				//newkase.set("embedded", true);
				$('#settlement_costs_holder').html(new check_listing_view({collection: data, model: newkase}).render().el);
			}
		});
		
		if (blnMedicalBilling) {
			//do we have any medical billings
			var medical_billings = new MedicalSummaryCollection({case_id: current_case_id});
			medical_billings.fetch({
				success: function(data) {
					var datas = data.toJSON();
					if (datas.length > 0) {
						setTimeout(function() {
							$("#settlement_med_button").html("Medical Summary (" + datas.length + ")");
						}, 2000);
					}
					var my_model = new Backbone.Model;
					my_model.set("holder", "settlement_med_holder");
					my_model.set("case_id", current_case_id);
					
					$('#settlement_med_holder').html(new medical_summary_listing_view({collection: data, model: my_model}).render().el);	
				}
			});
		}
		
		//ok, subro
		var parties = new Parties([], { "case_id": current_case_id, "type": "carrier", "panel_title": "Carriers" });
		var arrOptions = [];
		
		parties.fetch({
			success: function(data) {
				var carriers = data.toJSON();
		
				// Always show subro holder
				$("#settlement_subro_holder").show().empty();
		
				// Update the button count (even if zero)
				$("#settlement_subro_button").html("Subrogation (" + carriers.length + ")");
		
				if (carriers.length > 0) {
					// Loop through each carrier
					_.each(carriers, function(carrier) {
						// Create a div for each carrier
						$("#settlement_subro_holder").append("<div id='carrier_financial_" + carrier.corporation_id + "'></div>");
		
						// Get financials
						var financial = new Financial({
							case_id: current_case_id,
							corporation_id: carrier.corporation_id
						});
		
						var corp_claim_number = carrier.claim_number;
						financial.set("glass", "card_dark_7");
						financial.set("trust_options", arrOptions);
						financial.set("company_name", carrier.company_name);
		
						financial.fetch({
							success: function(datum) {
								var holder_id = "#carrier_financial_" + datum.get("corporation_id");
								financial.set("holder", holder_id);
		
								var section_title = datum.get("company_name");
								section_title = '<div style="float:right"><button id="partie_edit_' + datum.get("corporation_id") + '" class="partie_edit Carrier btn btn-transparent border-blue" style="border:0px solid; width:20px" title="Click to Edit ' + section_title + ' financials"><i class="glyphicon glyphicon-edit" style="color:#0033FF">&nbsp;</i></button></div>' + section_title;
		
								if (corp_claim_number !== "") {
									section_title += " :: Claim # " + corp_claim_number;
								}
		
								datum.set("section_title", section_title);
		
								$(holder_id).html(new carrier_financial_view({ model: datum }).render().el);
								$(holder_id).fadeIn(function() {
									$(holder_id).css("width", "50%");
								});
							}
						});
					});
				} else {
					// Handle the case when there are no carriers
					$("#settlement_subro_holder").html(
						"<div class='no-carriers text-muted' style='padding: 10px; color: #fff;text-align: center;   font-size: 15px;'>No carriers found for this case.</div>"
					);
				}
			},
		
			error: function() {
				$("#settlement_subro_holder").html(
					"<div class='error text-danger' style='padding:10px;'>Error fetching carrier data.</div>"
				);
			}
		});
		/*
		//subro, kept in financials
		var financial = new Financial({"case_id": current_case_id});
		financial.fetch({
		success: function(data) {
				var subros = data.toJSON();
				var datum = JSON.parse(subros.financial_info)
				var plaint = datum.plaintiff;
				var arrLength = plaint.length;
				var subro_amount = 0;
				var subro_reduced = 0;
				
				for (var i = 0; i < arrLength; i++) {
					if (plaint.plaintiff[i].name=="financial_subroInput") {
						subro_amount = plaint.plaintiff[i].value;
					}
					if (plaint.plaintiff[i].name=="reducedInput") {
						subro_reduction = plaint.plaintiff[i].value;
					}
				}
				
				var total_subros = 0;
				total_subros += Number(subro_amount) - Number(subro_reduction);	
			}
		});
		*/
		//deductions/specials
		var deductions = new DeductionCollection({case_id: current_case_id});
		deductions.fetch({
				success: function(data) {
					var deductions = data.toJSON();
					if (deductions.length > 0) {
						setTimeout(function() {
							$("#settlement_deduct_button").html("Deductions (" + deductions.length + ")");
						}, 2000);
					}
					var total_deductions = 0;
					_.each( deductions, function(deduction) {	
						total_deductions += Number(deduction.amount) - Number(deduction.payment) - Math.abs(Number(deduction.adjustment));
					});	
					//stored values 
					$("#deduction_calc").val(total_deductions);
					var deduction = $("#deduction").val();
					if (document.getElementById("deduction_override_checkbox").checked) {
						$("#deductionSpan").html(formatDollar(deduction));
						$("#deduction").val(Number(deduction).toFixed(2));
						$("#deduction_override_indicator").show();
						
						document.getElementById("deductionSpan").title = "Click to Remove Override";
					} else {
						$("#deductionSpan").html(formatDollar(total_deductions));
						$("#deduction").val(total_deductions.toFixed(2));
					}
					
					var dkase = self.model.clone();
					dkase.set("holder", "#settlement_deduct_holder");
					dkase.set("page_title", "Deduction");
					dkase.set("embedded", false);
					$('#settlement_deduct_holder').html(new deduction_listing_view({collection: data, model: dkase}).render().el);
				}
		});
		
		//negotiation
		var negotiations = new NegotiationCollection({case_id: current_case_id});
		negotiations.fetch({
				success: function(data) {
					var negotiations = data.toJSON();
					if (negotiations.length > 0) {
						setTimeout(function() {
							$("#settlement_negotiation_button").html("Negotiations (" + negotiations.length + ")");
						}, 2000);
					}
					var nkase = self.model.clone();
					nkase.set("holder", "#settlement_negotiation_holder");
					nkase.set("page_title", "Negotiation");
					nkase.set("embedded", false);
					$('#settlement_negotiation_holder').html(new negotiation_listing_view({collection: data, model: nkase}).render().el);
				}
		});
		
		//check requests
		var checkrequests = new CheckRequestsCollection({case_id: current_case_id});
		checkrequests.fetch({
			success: function(data) {
				var checkreqs = data.toJSON();
				if (checkreqs.length > 0) {
					var blnSettlementRequestsPending = false;
					_.each( checkreqs, function(checkrequest) {
						if (!blnSettlementRequestsPending) {
							if (checkrequest.account_id > 0 && checkrequest.approved=="P") {
								blnSettlementRequestsPending = true;
							}
						}
					});
					
					self.model.set("blnSettlementRequestsPending", blnSettlementRequestsPending);
					setTimeout(function() {
						$("#settlement_checkrequests_button").html("Check Requests (" + checkreqs.length + ")");
					}, 2000);
				}
				var kase = kases.findWhere({case_id: current_case_id});
				var reqkase = kase.clone();
				reqkase.set("holder", "#settlement_checkrequests_holder");
				reqkase.set("page_title", "Check Requests");
				reqkase.set("embedded", true);
				$('#settlement_checkrequests_holder').html(new checkrequest_listing_view({collection: data, model: reqkase}).render().el);
			}
		});	
		
		//losses
		var empty_model = new Backbone.Model;		
		empty_model.set("holder", "settlement_losses_holder");
		$("#settlement_losses_holder").html(new losses_list_view({model: empty_model}).render().el);
		
		$(".button_row").toggleClass("hidden");
		
		//for now
		$("#partie_edit").hide();
		$(".button_row .delete").hide();
		$(".button_row .reset").hide();
		
		//can we change anything
		if ($("#distributed_date").val()!="") {
			$(".button_row.settlement_sheet").html("<div style='color:lime; font-size:1.2em'>DISTRIBUTED&nbsp;&#10003;</div><div style='color:white; font-style:italic'>No Changes Allowed</div>");
			$(".button_row.settlement_sheet").css("margin-top", "30px");
		}
		//is it settled?
		/*
		if ($("#settled_date").val()=="") {
			$("#settlement_checkrequests_button").hide();	
		} else {
		*/
			//do we have check requests
			var checkrequests = new CheckRequestsCollection({case_id: current_case_id});
			checkrequests.fetch({
				success: function(data) {
					var checkrequests = data.toJSON();
					var overall_count = checkrequests.length;
					var approved_count = 0;
					var denied_count = 0;
					_.each( checkrequests, function(checkrequest) {
						if (checkrequest.approved == "Y") {
							approved_count++;
						}
						if (checkrequest.approved == "N") {
							denied_count++;
						}
					});
					var summary = "Check Requests:" + overall_count;
					if (overall_count > 0) {
						if (approved_count > 0) {
							summary += "<br>Approved:&nbsp;" + approved_count;
						}
						if (denied_count > 0) {
							summary += "<br>Denied:&nbsp;" + denied_count;
						}
						if (approved_count == 0 && denied_count == 0) {
							summary += " - All Pending";
						}
					} 
					
					//ONLY FOR PI?
					//trust account check
					self.checkTrustBalance(true);

					$("#checkrequest_label").show();
					$("#checkrequest_feedback_cell").html(summary);
				}
			});
		//}
		
		//align buttons
		/*
		var top = window.scrollY + document.querySelector('#panel_title').getBoundingClientRect().top;
		$("#settlement_buttons_holder").css("top", top + "px");
		*/
		setTimeout(function() {
			if (customer_id == "1121" && current_case_id == "9214") {
				$("#total_due").html("3,247.91");
				$("#subrogationSpan").html("5,504.78");
			}
		}, 5000);
	},
	showMedSumm: function() {
		$("#settlement_med_button").trigger("click");
		if ($("#distributed_date").val()!="") {
			$(".glyphicon-edit").hide();
			$("#new_check_Disbursement").hide();
			$("#medicalbilling_button").hide();
			$("#new_deduction").hide();
		}
	},
	showCosts: function() {
		$("#settlement_costs_button").trigger("click");
	},
	showSubro: function() {
		$("#settlement_subro_button").trigger("click");
	},
	showDeductions: function() {
		$("#settlement_deduct_button").trigger("click");
	},
	showCheckRequests: function() {
		$("#settlement_checkrequests_button").trigger("click");
	},
	showLossSummary: function(event) {
		event.preventDefault();
		
		composeLossSummary();
	},
	showDiv: function(event) {
		event.preventDefault();
		$(".holders").hide();
		$("#holders_holder").show();
		var element_id = event.currentTarget.id;
		
		var div_id = element_id.replace("button", "holder");
		$("#" + div_id).fadeIn();
		if (div_id=="settlement_negotiation_holder") {
			$("#settlement_negotiation_notes_holder").fadeIn();
		}
		
		$(".settlement_button").removeClass("btn-primary");
		$("#" + element_id).addClass("btn-primary");
		
		$(".tablesorter").css("background", "url(../img/glass_dark.png)");
		
		if ($("#distributed_date").val()!="") {
			$(".glyphicon-edit").hide();
			$(".glyphicon-trash").hide();
			$("#new_check_Disbursement").hide();
			$("#medicalbilling_button").hide();
			$("#new_deduction").hide();
		}
	},
	overDeduction: function() {
		var self = this;
		
		//if we're locked, no go
		if (this.model.get("status")=="D") {
			alert("No changes are allowed because this settlement has already been distributed.");
			return;
		}
		
		$("#deductionSpan").fadeOut(function() {
			$("#deduction_override_indicator").hide();
			document.getElementById("deduction_override_checkbox").checked = false;
			$("#deduction").show();
			/*
			var total_deductions = $("#deduction_calc").val();
			var deduction = $("#deduction").val();
			if (deduction < total_deductions) {
				$("#deduction_instructions").show();
			}
			*/
			self.showActualDeduction();
		});
	},
	showActualDeduction: function() {
		$("#deduction").val(Number($("#deduction_calc").val()).toFixed(2));
		//$("#deduction_instructions").hide();
		
		this.addTotalDue();
	},
	limitDeductionOverride: function() {
		var total_deductions = $("#deduction_calc").val();
		var deduction = $("#deduction").val();
		if (deduction > total_deductions) {
			$("#deduction").val($("#deduction_calc").val());
		}
		document.getElementById("deduction_override_checkbox").checked = true;
		this.addTotalDue();
	},
	overCost: function() {
		var self = this;
		
		//if we're locked, no go
		if (this.model.get("status")=="D") {
			alert("No changes are allowed because this settlement has already been distributed.");
			return;
		}
		
		$("#costSpan").fadeOut(function() {
			$("#cost_override_indicator").hide();
			document.getElementById("cost_override_checkbox").checked = false;
			$("#cost").show();
			/*
			var total_costs = $("#cost_calc").val();
			var cost = $("#cost").val();
			if (cost < total_costs) {
				$("#cost_instructions").show();
			}
			*/
			self.showActualCost();
		});
	},
	showActualCost: function() {
		$("#cost").val(Number($("#cost_calc").val()).toFixed(2));
		//$("#cost_instructions").hide();
		
		this.addTotalDue();
	},
	limitCostOverride: function() {
		var total_costs = $("#cost_calc").val();
		var cost = $("#cost").val();
		if (cost > total_costs) {
			$("#cost").val($("#cost_calc").val());
		}
		document.getElementById("cost_override_checkbox").checked = true;
		
		this.addTotalDue();
	},
	overMedicalExpense: function() {
		var self = this;
		
		//if we're locked, no go
		if (this.model.get("status")=="D") {
			alert("No changes are allowed because this settlement has already been distributed.");
			return;
		}
		
		$("#settlement_medical_expenses").fadeOut(function() {
			$("#medical_expense_override_indicator").hide();
			document.getElementById("medical_expense_override_indicator").checked = false;
			$("#total_medical_expenses").show();
			/*
			var total_medicals = $("#medical_calc").val();
			var medical = $("#total_medical_val").val();
			if (medical < total_medicals) {
				$("#medical_instructions").show();
			}
			*/
			self.showActualMedicalExpense();
		});
	},
	showActualMedicalExpense: function() {
		$("#total_medical_expenses").val(Number($("#medical_expense_calc").val()).toFixed(2));
		//$("#medical_instructions").hide();
		
		this.calcMedical();
	},
	limitMedicalExpenseOverride: function() {
		/*
		var total_medicals = $("#medical_calc").val();
		var medical = $("#total_medical_val").val();
		if (medical > total_medicals) {
			$("#total_medical_val").val($("#medical_calc").val());
		}
		*/
		
		var total_medicals = $("#medical_expense_calc").val();
		var medical = $("#total_medical_expenses").val();
		if (medical > total_medicals) {
			$("#total_medical_expenses").val($("#medical_calc").val());
		}
		
		document.getElementById("medical_expense_override_indicator").checked = true;
		this.addTotalDue();
	},
	overMedicalPayment: function() {
		var self = this;
		
		//if we're locked, no go
		if (this.model.get("status")=="D") {
			alert("No changes are allowed because this settlement has already been distributed.");
			return;
		}
		
		$("#settlement_medical_payments").fadeOut(function() {
			$("#medical_payment_override_indicator").hide();
			document.getElementById("medical_payment_override_checkbox").checked = false;
			$("#total_medical_payments").show();
			
			self.showActualMedicalPayment();
		});
	},
	showActualMedicalPayment: function() {
		$("#total_medical_payments").val(Number($("#medical_payment_calc").val()).toFixed(2));
		
		this.calcMedical();
	},
	limitMedicalPaymentOverride: function() {
		var total_medicals = $("#medical_payment_calc").val();
		var medical = $("#total_medical_payments").val();
		if (medical > total_medicals) {
			$("#total_medical_payments").val($("#medical_payment_calc").val());
		}
		
		document.getElementById("medical_payment_override_checkbox").checked = true;
		this.addTotalDue();
	},
	overMedicalAdjustment: function() {
		var self = this;
		//if we're locked, no go
		if (this.model.get("status")=="D") {
			alert("No changes are allowed because this settlement has already been distributed.");
			return;
		}
		
		$("#settlement_medical_adjustments").fadeOut(function() {
			$("#medical_adjustment_override_indicator").hide();
			document.getElementById("medical_adjustment_override_checkbox").checked = false;
			$("#total_medical_adjustments").show();
			
			self.showActualMedicalAdjustment();
		});
	},
	showActualMedicalAdjustment: function() {
		$("#total_medical_adjustments").val(Number($("#medical_adjustment_calc").val()).toFixed(2));
		
		this.calcMedical();
	},
	limitMedicalAdjustmentOverride: function() {
		var total_medicals = $("#medical_adjustment_calc").val();
		var medical = $("#total_medical_adjustments").val();
		if (medical > total_medicals) {
			$("#total_medical_adjustments").val($("#medical_adjustment_calc").val());
		}
		
		document.getElementById("medical_adjustment_override_checkbox").checked = true;
		this.addTotalDue();
	},
	overSubro: function() {
		var self = this;
		
		//if we're locked, no go
		if (this.model.get("status")=="D") {
			alert("No changes are allowed because this settlement has already been distributed.");
			return;
		}
		
		$("#subrogationSpan").fadeOut(function() {
			$("#subrogation_override_indicator").hide();
			document.getElementById("subrogation_override_checkbox").checked = false;
			$("#subrogation").show();
			/*
			var total_subrogations = $("#subrogation_calc").val();
			var subrogation = $("#subrogation").val();
			if (subrogation < total_subrogations) {
				$("#subrogation_instructions").show();
			}
			*/
			self.showActualSubro();
		});
	},
	showActualSubro: function() {
		$("#subrogation").val(Number($("#subrogation_calc").val()).toFixed(2));
		//$("#subrogation_instructions").hide();
		alert("here");
		this.addTotalDue();
	},
	limitSubroOverride: function() {
		var total_subrogations = $("#subrogation_calc").val();
		var subrogation = $("#subrogation").val();
		if (subrogation > total_subrogations) {
			$("#subrogation").val($("#subrogation_calc").val());
		}
		document.getElementById("subrogation_override_checkbox").checked = true;
		this.addTotalDue();
	},
	newTrustCheck:function (event) {
		event.preventDefault();
		composeCheck("new_check_-1", "IN", "", {}, this.model.get("account_id"), "trust", current_case_id);
    },
	checkTrustBalance: function(blnInitial) {
		if (!blnTrustRequired) {
			return;
		}
		var kase = kases.findWhere({case_id: current_case_id});
		if (isWCAB(kase.get("case_type"))) {
			return;
		}
		
		if (typeof blnInitial == "undefined") {
			blnInitial = false;
		}
		var self = this;
		
		var account =  new Account({"case_id": current_case_id, "account_type": "trust"});
		
		account.fetch({
			success: function (data) {
				//now show the drop with accounts, selected if already chosen
				var account = data.toJSON();
				$("#checkrequest_label").html("Trust Account");
				$("#checkrequest_label").css("color", "white");
				$("#checkrequest_label").css("text-decoration", "none");
				//$("#checkrequest_label").fadeIn();
				var current_feedback = $("#checkrequest_feedback_cell").html();
				if (account.id==-1) {
					var account_report = "<div style='background:red; color:white; padding:2px'>No Trust Account... Attaching Case</div>";
					$("#checkrequest_feedback_cell").html(current_feedback + account_report);
					
					//do we have a trust account?
					if ($("#trust_account_id").length > 0) {
						var trust_account_id = $("#trust_account_id").val();
						var url = "api/account/attach";
						var formValues = "case_id=" + current_case_id;
						formValues += "&account_id=" + trust_account_id;
						formValues += "&account_type=trust";
						$.ajax({
							url:url,
							type:'POST',
							dataType:"json",
							data: formValues,
							success:function (data) {
								if(data.error) {  // If there is an error, show the error messages
									saveFailed(data.error.text);
								} else {
									self.checkTrustBalance(false);
								}
							}
						});
					}
					return;
				}
				
				var account_name = account.account_name;
				var account_id = account.id;
				self.model.set("account_id", account_id)
				var url = "api/account/balance/" + current_case_id + "/trust";
				$.ajax({
					url:url,
					type:'GET',
					dataType:"json",
					success:function (data) {
						var trust_available = Number(data.balance) - Number(data.pendings);
						var account_style = "background:black; color:white; padding:2px";
						var total_due = Number($("#total_due_input").val());
						if (trust_available < total_due) {
							account_style = "background:red; color:white; padding:2px";
						}
						account_style += ";position: absolute;margin-top: 40px;";
						var account_report = "<div style='" + account_style + "'>" + account_name + " >> $" + formatDollar(trust_available) + "</div>";
						if (trust_available < total_due) {
							account_report += "<div style='font-style:italic; margin-bottom:5px'>Insufficient Funds</div>";
							account_report += '<button id="add_trust_check" class="btn btn-sm btn-primary add_check" title="Click to add a Check to Trust Account" style="margin-top:-5px;">Deposit</button>';
						} else {
							if (blnInitial) {
								account_report += '<div style="margin-top:5px;position: absolute"><button id="bulk_check_request" class="btn btn-sm btn-primary add_checkrequest" title="Click to request Checks from Trust Account" style="margin-top:-5px;">Request Settlement Checks</button></div>';
							}
						}
						var current_feedback = $("#checkrequest_feedback_cell").html();
						$("#checkrequest_feedback_cell").html(current_feedback + account_report);
					}
				});
			}
		});
	},
	newCheckRequest: function(event) {
		event.preventDefault();
		var blnSettlementRequestsPending = this.model.get("blnSettlementRequestsPending");
		//alert("first");
		composeCheckRequest("request_bulk_-1", "", current_case_id, blnSettlementRequestsPending);
	},
	printStatement: function(event) {
		event.preventDefault();
		
		$("#statement_feedback").html('Generating...');
		
		var url = 'api/letter/create';
		formValues = "table_name=letter&table_id=settlement&case_id=" + current_case_id;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				//self.reviewPDF(data.success);
				$("#statement_feedback").html('<div style="margin-top:10px"><a href="' + data.success + '.docx" title="Click to open Settlement Statement" class="white_text" target="_blank">View</a>&nbsp;|&nbsp;<a href="api/download.php?file=' + data.success + '.docx" title="Click to download document to your computer" class="white_text" target="_blank" style="cursor:pointer">Download</a></div>');
			}
		});
	},
	mainSettlement: function(event) {
		event.preventDefault();
		document.location.href = "#settlement/" + current_case_id + "/" + this.model.get("injury_id");
	},
	firstSettlement: function(event) {
		event.preventDefault();
		document.location.href = "#settlementsheet/" + this.model.get("first_id") + "/" + this.model.get("injury_id");
	},
	secondSettlement: function(event) {
		event.preventDefault();
		
		var formValues = "id=" + this.model.get("settlementsheet_id") + "&injury_id=" + this.model.get("injury_id");
		//mark injury_settlement as settlement_1
		var url = "api/settlementsheet/freeze";
		//create new main
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				$("#panel_title").css("background", "green");
				//this will have create a new settlement, so we can just refresh
				setTimeout(function() {
					var arrHash = document.location.hash.split("/");
					window.Router.prototype.showSettlement(arrHash[1], arrHash[2]);
				}, 1500);
			}
		});
	},
	limitDates: function(event) {
		var self = this;
		var settled_date = $("#settled_date").val();
		var draft_date = $("#draft_approved_date").val();
		
		if (settled_date=="") {
			$(".settlement_date").val("");
			return;
		}
		
		var element = event.currentTarget;
		if (element.id=="settled_date") {
			//check on trust account for check request
			//can't do anything if the trust fund does not have enough money
			self.checkTrustBalance();
		}
		
		var draft_received_date = $("#draft_received_date").val();
		var d1 = new Date(draft_date);
		var d2 = new Date(draft_received_date);
		
		if (d1 > d2) {
			$("#draft_received_date").val($("#draft_approved_date").val());
		}
		
		var release_received_date = $("#release_received_date").val();
		var d1 = new Date(draft_received_date);
		var d2 = new Date(release_received_date);
		
		if (d1 > d2) {
			$("#release_received_date").val($("#draft_received_date").val());
		}
		
		var release_returned_date = $("#release_returned_date").val();
		var d1 = new Date(release_received_date);
		var d2 = new Date(release_returned_date);
		
		if (d1 > d2) {
			$("#release_returned_date").val($("#release_received_date").val());
		}
		/*
		var approved_distribution_date = $("#approved_distribution_date").val();
		var d1 = new Date(release_returned_date);
		var d2 = new Date(approved_distribution_date);
		
		if (d1 > d2) {
			$("#approved_distribution_date").val($("#release_returned_date").val());
		}
		*/
		var estimated_distribution_date = $("#estimated_distribution_date").val();
		var d1 = new Date(approved_distribution_date);
		var d2 = new Date(estimated_distribution_date);
		
		if (d1 > d2) {
			$("#estimated_distribution_date").val($("#approved_distribution_date").val());
		}
		
		var distributed_date = $("#distributed_date").val();
		var d1 = new Date(estimated_distribution_date);
		var d2 = new Date(distributed_date);
		
		if (d1 > d2) {
			$("#distributed_date").val($("#estimated_distribution_date").val());
		}
	},
	saveSettlement: function(event) {
		event.preventDefault();
		
		if (current_case_id==-1) {
			//catchup
			if (document.location.hash.indexOf("#settlement/")==0) {
				current_case_id = document.location.hash.split("/")[1];
			}
		}
		
		//if (login_user_id=="1315") {
			var referral_id = $("#referral_id").val();
			var referral_partie = $("#referral_partie").val();
			if (referral_partie!="" && referral_id=="") {
				//we're adding a partie
				//save the recipient as corporation
				formValues = "table_name=corporation&type=referring";
				formValues += "&additional_partie=y";
				formValues += "&adhoc_fields=&case_id=" + current_case_id;
				formValues += "&company_name=" + encodeURIComponent(referral_partie);
				
				var url = "api/corporation/add";
				
				$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
					success:function (data) {
						blnSaving = false;
						if(data.error) {  // If there is an error, show the error messages
							console.log(data.error.text);
						} else { 
							$("#referral_id").val(data.id);
							//reset
							$("#referral_partie").val("");
							
							//now save settlement
							$(".save").trigger("click");
						}
					}
				});
				
				return;
			}
		
			//fee_referral
			if ($("#referral_id").val()!="") {
				//we have referral info
				var fee = $("#referral_source_fee").val();
				var fee_date = $("#referral_source_date").val();
				var arrRef = {referral_id: referral_id, fee: fee, date: fee_date};
				var fee_referral = JSON.stringify(arrRef);
				$("#referral_info").val(fee_referral);
			}
		//}
		
		var self = this;
		var arrID = document.location.hash.split("/");
		var formValues = $("#settementsheet_form").serialize();
		formValues += "&case_id=" + arrID[1];
		formValues += "&injury_id=" + arrID[2];
		
		var id = $("#table_id").val();
		var url = "api/settlementsheet/add";
		if (id > 0) {
			url = "api/settlementsheet/update";
		}
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#panel_title").css("background", "green");
					$("#table_id").val(data.id);
					setTimeout(function() {
						//$("#panel_title").css("background", "none");
						var settlement = new SettlementSheet({injury_id: self.model.get("injury_id")});
						
						//has the settlement date changed
						if (self.model.get("date_settled")=="0000-00-00") {
							if ($("#settled_date").val()!="0000-00-00" && $("#settled_date").val()!="") {
								//do we have enough funds
								if ($("#checkrequest_feedback_cell").html().indexOf("Insufficient") > -1) {
									composeCheckRequest("request_bulk_-1", "", current_case_id);
								}
							}
						}
						settlement.fetch({
							success: function(data) {
								//get the values from the hash
								//redraw
								var arrHash = document.location.hash.split("/");
								window.Router.prototype.showSettlement(arrHash[1], arrHash[2]);
								//var kase = kases.findWhere({case_id: current_case_id});
								//settlement.set("holder", "kase_content");
								//$('#kase_content').html(new settlementsheet_view({model: settlement, kase: kase}).render().el);
							}
						});
					}, 2500);
				}
			}
		});
	},
	getSubro: function() {
		var self = this;
		var url = "api/financialtotals/" + current_case_id;
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					data.balance = Number(data.subrogation) - Number(data.reduced);
					//stored values 
					var total_subrogation = data.balance;	//after reduction from data.subrogation;
					//alert(total_subrogation);
					$("#subrogation_calc").val(total_subrogation);
					var subrogation = $("#subrogation").val();
					if (document.getElementById("subrogation_override_checkbox").checked) {
						$("#subrogationSpan").html(formatDollar(subrogation));
						$("#subrogation").val(Number(subrogation).toFixed(2));
						$("#subrogation_override_indicator").show();
						
						document.getElementById("subrogationSpan").title = "Click to Remove Override";
					} else {
						$("#subrogationSpan").html(formatDollar(total_subrogation));
						$("#subrogation").val(total_subrogation.toFixed(2));
						document.getElementById("subrogation_override_checkbox").checked = false;
					}
					/*
					$("#subrogationSpan").html(formatDollar(Number(data.subrogation) - Number(data.subrogation_override)));
					
					if (Number(data.subrogation_override) > 0) {
						$("#subrogation_override_indicator").fadeIn();
						data.subrogation = 0;
					}
					$("#subrogation").val(data.subrogation);
					*/
					
					self.addMedical();
				}
			}
		});
	},
	addMedical: function() {
		var self = this;
		/*
		var medicals = $(".column_medical");
		var arrLength = medicals.length;
		var total = 0;
		for(var i = 0; i < arrLength; i++) {
			var val = medicals[i].value;
			val = ((val=="") ?  0 : val);
			if (i==1) {
				total -= Number(val);
			} else {
				total += Number(val);
			}
		}
		$("#total_medical").html(total.toFixed(2));
		this.model.set("total_medical", total);
		if (!this.model.get("initialize")) {
			this.addTotalDue();
		}
		*/
		
		var url = "api/medicalbillingsummary/" + current_case_id;
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#settlement_medical_expenses").html(formatDollar(data.billed_total));
					$("#settlement_medical_payments").html(formatDollar(data.paid_total));
					data.adjusted_total = Math.abs(Number(data.adjusted_total));
					$("#settlement_medical_adjustments").html(formatDollar(data.adjusted_total));
					
					$("#medical_expense_calc").val(data.billed_total);
					$("#medical_payment_calc").val(data.paid_total);
					$("#medical_adjustment_calc").val(data.adjusted_total);
					
					data.balance = String(data.balance).numbersOnly();
					$("#medical_calc").val(data.balance);
					var total_medical = formatDollar(data.balance);
					$("#medicalSpan").html(total_medical);
					
					//stored values 
					var total_medical_expenses = Number(data.billed_total);
					var medical_expenses = $("#total_medical_expenses").val();
					if (document.getElementById("medical_expense_override_checkbox").checked) {
						$("#settlement_medical_expenses").html(formatDollar(medical_expenses));
						$("#total_medical_expenses").val(Number(medical_expenses).toFixed(2));
						$("#medical_expense_override_indicator").show();
						
						document.getElementById("settlement_medical_expenses").title = "Click to Remove Override";
					} else {
						$("#settlement_medical_expenses").html(formatDollar(total_medical_expenses));
						$("#total_medical_expenses").val(total_medical_expenses.toFixed(2));
					}
					
					var total_medical_payments = Number(data.paid_total);
					var medical_payments = $("#total_medical_payments").val();
					if (document.getElementById("medical_payment_override_checkbox").checked) {
						$("#settlement_medical_payments").html(formatDollar(medical_payments));
						$("#total_medical_payments").val(Number(medical_payments).toFixed(2));
						$("#medical_payment_override_indicator").show();
						
						document.getElementById("settlement_medical_payments").title = "Click to Remove Override";
					} else {
						$("#settlement_medical_payments").html(formatDollar(total_medical_payments));
						$("#total_medical_payments").val(total_medical_payments.toFixed(2));
					}
					
					var total_medical_adjustments = Number(data.adjusted_total);
					var medical_adjustments = $("#total_medical_adjustments").val();
					if (document.getElementById("medical_adjustment_override_checkbox").checked) {
						$("#settlement_medical_adjustments").html(formatDollar(medical_adjustments));
						$("#total_medical_adjustments").val(Number(medical_adjustments).toFixed(2));
						$("#medical_adjustment_override_indicator").show();
						
						document.getElementById("settlement_medical_adjustments").title = "Click to Remove Override";
					} else {
						$("#settlement_medical_adjustments").html(formatDollar(total_medical_adjustments));
						$("#total_medical_adjustments").val(total_medical_adjustments.toFixed(2));
					}
					
					//stored values 
					var total_costs = Number(data.costs);
					$("#cost_calc").val(total_costs);
					
					if (document.getElementById("cost_override_checkbox").checked) {
						//$("#costSpan").html(formatDollar(cost));
						//alert(cost.value);
						$("#costSpan").html(Number(formatDollar(cost.value)).toFixed(2));
						$("#cost").val(Number(cost).toFixed(2));
						if (isNaN(Number(cost).toFixed(2))) {
							$("#cost").html(Number(cost.value).toFixed(2));
						}
						$("#cost_override_indicator").show();
						
						document.getElementById("costSpan").title = "Click to Remove Override";
					} else {
						$("#costSpan").html(formatDollar(total_costs));
						if (isNaN(formatDollar(total_costs))) {
							$("#costSpan").html((total_costs).toFixed(2));
						}
						$("#cost").val(total_costs.toFixed(2));
						if (isNaN(Number((total_costs).toFixed(2)))) {
							$("#cost").val((total_costs).toFixed(2));
						}
					}
					if (self.model.get("blnFirst")) {
						//zero out everything
						$("#settlement_medical_expenses").html("$0.00");
						$("#total_medical_expenses").val(0.00);
						$("#settlement_medical_payments").html("$0.00");
						$("#total_medical_payments").val(0.00);
						$("#settlement_medical_adjustments").html("$0.00");
						$("#total_medical_adjustments").val(0.00);		
						
						$("#refund").val(0.00);
						$("#costSpan").html("$0.00");
						$("#costSpan").html("0.00");
						$("#cost").val(0);
						$("#subrogationSpan").html("0.00");
						$("#subrogation").val(0);
						$("#deductionSpan").html("0.00");
						$("#deduction").val(0);
						
					}
					/*
					$("#costSpan").html(formatDollar(data.costs));
					$("#cost").val(data.costs);
					*/
					self.addGross();
					self.addFee();
					self.model.set("initialize", false);
					
					self.calcMedical();
				}
			}
		});
	},
	calcMedical: function() {
		var total_medical_expenses = $("#total_medical_expenses").val();
		if (document.getElementById("medical_expense_override_indicator").checked) {
			total_medical_expenses = $("#settlement_medical_expenses").html();
		}
		var total_medical_payments = $("#total_medical_payments").val();
		if (document.getElementById("medical_payment_override_indicator").checked) {
			total_medical_payments = $("#settlement_medical_payments").html();
		}
		var total_medical_adjustments = $("#total_medical_adjustments").val();
		if (document.getElementById("medical_adjustment_override_indicator").checked) {
			total_medical_payments = $("#settlement_medical_adjustments").html();
		}
		var balance = Number(total_medical_expenses) - Number(total_medical_payments) - Math.abs(Number(total_medical_adjustments));
		$("#medical_calc").val(balance);
		var total_medical = formatDollar(balance);
		$("#medicalSpan").html(total_medical);
		
		this.addTotalDue();
	},
	addGross: function() {
		var grosses = $(".column_gross");
		var fees = $(".column_fee");
		var percents = $(".column_percent");
		
		var arrLength = grosses.length;
		var total = 0;
		for(var i = 0; i < arrLength; i++) {
			var val = grosses[i].value;
			var checkval = val.numbersOnly();
			if (val!=checkval) {
				val = checkval;
				grosses[i].value = val;
			}
			val = ((val=="") ?  0 : val);
			total += Number(val);
			
			//calculate fee
			var fee = fees[i];
			var percent = percents[i];
			
			//either or, not both
			var blnCalculated = false;
			if (percent.value=="0.00000" && fee.value!="0.00000" && fee.value!="0.00" && fee.value!="") {
				//calculate the percent value
				percent.value = Number(fee.value) / Number(val);
				blnCalculated = true;
			} else {
				if (percent.value!="") {
					fee.value = (Number(val) * Number(percent.value) / 100).toFixed(2);
					blnCalculated = true;
				}
			}
			if (!blnCalculated) {
				if (fee.value!="") {
					if (isNaN(Number(fee.value) / Number(val) * 100)) {
					} else {
						percent.value = (Number(fee.value) / Number(val) * 100).toFixed(2);
					}
				}
			}
		}
		$("#total_gross").html(formatDollar(total));
		this.model.set("total_gross", total);
		
		if (!this.model.get("initialize")) {
			this.addFee();
			this.addTotalDue();
		}
	},
	addFee: function() {
		var fees = $(".column_fee");
		var arrLength = fees.length;
		var total = 0;
		for(var i = 0; i < arrLength; i++) {
			var val = fees[i].value;
			var checkval = val.numbersOnly();
			if (val!=checkval) {
				val = checkval;
				fees[i].value = val;
			}
			val = ((val=="") ?  0 : val);
			total += Number(val);
		}
		$("#total_fee").html(formatDollar(total));
		this.model.set("total_fee", total);
		if (!this.model.get("initialize")) {
			this.addTotalDue();
		}
	},
	addTotalDue: function() {
		var gross = this.model.get("total_gross");
		var fee = this.model.get("total_fee");
		//var medical = this.model.get("total_medical");
		var val = $("#medical_calc").val();
		if (val.match(/[a-z]/i)) {
			val = 0;
		}
		var medical = ((val=="") ?  0 : val);
		var val = $("#refund").val();
		var refund = ((val=="") ?  0 : val);
		var val = $("#cost").val();
		var cost = ((val=="") ?  0 : val);
		var val = $("#subrogation").val();
		var subrogation = ((val=="") ?  0 : val);
		var val = $("#deduction").val();
		var deduction = ((val=="") ?  0 : val);
		
		var total = Number(gross) - Number(fee) + Number(refund) - Number(medical) - Number(cost) - Number(deduction) - Number(subrogation);
		
		if(customer_id == "1121") {
			total = Number(gross) - Number(fee) + Number(refund) - Number(medical) - Number(cost) - Number(deduction) - Number(subrogation);
		}
		
		$("#total_due").html(formatDollar(total));
		$("#total_due_input").val(total);
		this.model.set("total_due", total);
	}
});