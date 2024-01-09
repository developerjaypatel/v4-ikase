window.prior_referral_view = Backbone.View.extend({
    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	initialize:function () {
		_.bindAll(this);
    },
	 events:{
        "click .prior_referral .delete":					"deletePriorRefView",
		"click .prior_referral .save":						"savePriorRef",
		"click .prior_referral .save_field":				"savePriorRefViewField",
		"click .prior_referral .edit": 						"togglePriorRefEdit",
		"click .prior_referral .reset": 					"resetPriorRefForm",
		"keyup .prior_referral .input_class": 				"valuePriorRefViewChanged",
		"dblclick .prior_referral .gridster_border": 		"editPriorRefViewField",
		"click #prior_referral_all_done":					"doTimeouts"
    },
    render: function () {
		var self = this;
		var mymodel = this.model.toJSON();
		if (isDate(mymodel.fee_date)) {
			mymodel.fee_date = moment(mymodel.fee_date).format("MM/DD/YYYY");
		}
		
		try {
			$(this.el).html(this.template(mymodel));		
		}
		catch(err) {
			var view = "prior_referral_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
    },
	togglePriorRefEdit: function(event) {
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
		$(".prior_referral_view .editing").toggleClass("hidden");
		$(".prior_referral_view .span_class").removeClass("editing");
		$(".prior_referral_view .input_class").removeClass("editing");
		
		$(".prior_referral_view .span_class").toggleClass("hidden");
		$(".prior_referral_view .input_class").toggleClass("hidden");
		$(".prior_referral_view .input_holder").toggleClass("hidden");
		$(".button_row.prior_referral").toggleClass("hidden");
		$(".edit_row.prior_referral").toggleClass("hidden");
	},
	editPriorRefField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".prior_referral_view_" + field_name;
		}
		editField(element, master_class);
	},
	savePriorRef:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "prior_referral", "fee");
		return;
    },
	resetPriorRefForm: function(event) {
		event.preventDefault();
		this.togglePriorRefEdit(event);
		//this.render();
		//$("#address").hide();
	},
	doTimeouts: function() {
		var self = this;
		var mymodel = this.model.toJSON();
		gridsterById('gridster_prior_referral');
		
		if(mymodel.id=="" || mymodel.id==-1){	
			$(".prior_referral .edit").trigger("click"); 
			$(".prior_referral .delete").hide();
			$(".prior_referral .reset").hide();
		}
		$('#fee_dateInput').datetimepicker({
				timepicker:false, 
				format:'m/d/Y',
				mask:false,
				onChangeDateTime:function(dp,$input){
					//alert($input.val());
			}
		});
		if(this.model.get("fee_date")=="" || this.model.get("fee_date")=="0000-00-00"){
			//editing mode right away
			this.model.set("editing", false);
			this.model.set("current_input", "");
			
			$(".prior_referral .edit").trigger("click"); 
			$(".prior_referral .delete").hide();
			$(".prior_referral .reset").hide();
			$("#fee_dateInput").focus(); 
		} else {
			//$(".prior_referral .token-input-list-facebook").toggleClass("hidden");
		}
	}
});
window.fee_form = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"change #hoursInput":			"calculateBilled",
		"keyup #hoursInput":			"calculateBilled",
		"blur #hoursInput":				"calculateBilled",
		"click #bill_paid":				"payBill",
		"click #fee_all_done":			"doTimeouts"
	},
	render:function() {
		var self = this;
		if (typeof this.template != "function") {
			var view = "fee_form";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   var self = this;
	   
	   var kase = kases.findWhere({case_id: current_case_id});
	   this.model.set("case_name", kase.get("case_name"));
	   this.model.set("case_number", kase.get("case_number"));
	   if (this.model.get("case_number")=="") {
		   this.model.set("case_number", kase.get("file_number"));
	   }
	   
		if (this.model.get("fee_requested")=="" || this.model.get("fee_requested")=="0000-00-00") {
			this.model.set("fee_requested", this.model.get("fee_date"));
		}
	   try {
			$(this.el).html(this.template(this.model.toJSON()));
		} catch(err) {
			alert(err);
			
			return "";
		}
	   return this;
	},
	payBill: function() {
		$("#paid_feeInput").val($("#fee_billedInput").val());
	},
	calculateBilled: function() {
		if ($("#fee_billed_div").css("display") == "none" && $("#fee_form #table_id").val()==-1) {
			return;
		}
		var hourly_rate = $("#hourly_rateInput").val();
		var hours = $("#hoursInput").val();
		
		if (hourly_rate > 0 && hours > 0) {
			var billed = Number(hourly_rate) * Number(hours);
			$("#fee_billedInput").val(billed);
		}
	},
	doTimeouts: function() {
		var self = this;
		var theme = {
				theme: "facebook",
				hintText: "Search for Employees",
				onAdd: function(item) {
					$(".token-input-input-token-facebook").hide();	
					if (item.rate!="") {
						$("#hourly_rateInput").val(item.rate);
					}
					//recalculate
					self.calculateBilled();
				},
				onDelete: function(item) {	
					$(".token-input-input-token-facebook").show();				
				}
		};
		//lookup employees
		$("#fee_byInput").tokenInput("api/user", theme);
		$(".token-input-list-facebook").css("width", "175px");
		
		if (this.model.get("fee_id") < 0) {
			if (this.model.get("settlement_id") < 1) {
				$("#fee_check_number").val("ORD-" + this.model.get("case_number").replaceTout("*", "") + "-1")
				$("#fee_invoice_number").html($("#fee_check_number").val());
			} else {
				//get the fee count for invoice number
				var url = "api/settlement_feecount/" + this.model.get("settlement_id");
				
				$.ajax({
					url:url,
					type:'GET',
					dataType:"json",
					data: "",
					success:function (data) {
						if(data.error) {  // If there is an error, show the error messages
							saveFailed(data.error.text);
						} else {
							var invoice_number = Number(data.fee_count)+ 1;
							$("#fee_check_number").val("ORD-" + self.model.get("case_number").replaceTout("*", "") + "-" + invoice_number);
							$("#fee_invoice_number").html($("#fee_check_number").val());
						}
					}
				});
			}
			var kase = kases.findWhere({case_id: current_case_id});
			if (kase.get("attorney_id")==null) {
				kase.set("attorney_id", "");
			}
			if (kase.get("attorney_id")!="") {
				var supervising_attorney = kase.get("attorney_id");
			} else {
				var supervising_attorney = kase.get("attorney");
			}
			if (supervising_attorney != "") {
				if (!isNaN(supervising_attorney)) {
					var user_options = {user_id: supervising_attorney};						
				} else {
					var user_options = {nickname: supervising_attorney};						
				}
			} else {
				//current user
				var user_options = {user_id: login_user_id};	
			}
		} else {
			var user_options = "";
			if (this.model.get("fee_by")!="") {
				var user_options = {nickname: this.model.get("fee_by")};	
			}
		}
		if (user_options!="") {
			var user = new User(user_options);
			user.fetch({
				success: function (data) {
					var user_data = data.toJSON();
					$("#fee_byInput").tokenInput("add", {
						id: user_data.id, 
						name: user_data.user_name,
						rate: user_data.rate
					});
				}
			});
			
			$(".token-input-input-token-facebook").hide();
		}
		
		//add or paying
		if (typeof this.model.get("action") != "undefined") {
			if (this.model.get("action")=="add") {
				$("#fee_payment_div").hide();
			} else {
				$("#fee_billed_div").hide();
			}
		}
		if (this.model.get("fee_id") > 0) {
			//offer both
			$("#fee_billed_div").show();
			$("#fee_payment_div").show();
		}
		//depo hours
		if (this.model.get("fee_type")=="depo") {
			$("#depo_rate_hours_holder").show();
		}
		
		//doctor for attorney
		if (this.model.get("fee_type")=="attorney") {
			var kase_parties = new Parties([], { case_id: current_case_id });
			var doctor_id = self.model.get("fee_doctor_id");
			kase_parties.fetch({
				success: function(data) {
					var doctor_parties = kase_parties.where({"type": "medical_provider"});
					if (doctor_parties.length==0) {
						$("#doctor_attorney").hide();
						//self.getFees();
						return;
					}
					var arrOptions = ["<option value='-1'>Select Doctor</option>"];
					for(var i = 0; i < doctor_parties.length; i++) {
						var doctor = doctor_parties[i].toJSON();
						
						var selected = "";
						if (doctor.corporation_id == doctor_id) {
							selected = " selected";
						}
						var option = "<option value='" + doctor.corporation_id + "'" + selected + ">" + doctor.company_name + "</option>";
						arrOptions.push(option);
					}
					
					$("#doctor_attorney").html(arrOptions.join("\r\n"));
					$("#doctor_select_holder").show();
				}
			});
		} else {
			$("#doctor_select_holder").remove();
		}
	}
});
window.fee_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .edit_fee":						"editFee",
		"click .pay_settlement_fee":			"newFeeAction",
		"click .delete_fee":					"confirmDeleteFee",
		"click #fee_listing_all_done":			"doTimeouts"
	},
	render:function() {
		var self = this;
		if (typeof this.template != "function") {
			var view = "fee_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   var self = this;
	   
	   var fees = this.collection.toJSON();
	   var case_id = current_case_id;
	   var embedded = this.model.get("embedded");
	   var page_title = this.model.get("page_title");
	   var fee_type = this.model.get("fee_type");
	   try {
			$(this.el).html(this.template({
				fees: 					fees, 
				case_id: 				case_id,
				embedded: 				embedded,
				page_title:				page_title,
				fee_type:				fee_type
			}));
		} catch(err) {
			alert(err);
			
			return "";
		}
	   return this;
	},
	editFee: function(event) {
		var element = event.currentTarget;
		var settlement_id = $(".settlement #settlement_id").val();
		
		composeNewFee(element.id, settlement_id);
	},
	newFeeAction: function(event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var settlement_id = $(".settlement #settlement_id").val();
		
		composeNewFee(element.id, settlement_id);
    },
	confirmDeleteFee: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		composeDelete(id, "fee");
	},
	doTimeouts: function() {
		$("#fee_listing th").css("font-size", "1.1em");
	}
});