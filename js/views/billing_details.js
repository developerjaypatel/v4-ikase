var billing_timeout_id;
window.billing_view = Backbone.View.extend({
	events:{
		"click #save_billing_modal":				"scheduleAddBilling",
		"click .billing .edit": 					"scheduleBillingEdit",
		"click .billing .reset": 					"scheduleBillingReset",
		"click #billing_view_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "billing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		//$(this.el).html(this.template(this.model.toJSON()));
		
        return this;
	},
	doTimeouts: function() {
		var self = this;
		//we are not in editing mode initially
		this.model.set("editing", false);

		$("#timekeeperInput").tokenInput("api/user");
		if (this.model.get("billing_date") != "") {
			//setTimeout(function() {
				$("#timekeeperInput").tokenInput("add", {id: this.model.get("timekeeper"), name: this.model.get("user_name")});
				$(".token-input-list").css("width", "310px");
			//}, 2000);
		} else {
			$(".token-input-list").css("width", "310px");
		}
		
	},
	scheduleAddBilling:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(billing_timeout_id);
		billing_timeout_id = setTimeout(function() {
			self.addBilling(event);
		}, 200);
	},
	addBilling:function (event) {
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
	scheduleBillingEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(billing_timeout_id);
		billing_timeout_id = setTimeout(function() {
			self.toggleBillingEdit(event);
		}, 200);
	},
	toggleBillingEdit: function (event) {
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
		toggleFormBilling("billing");
	},
	scheduleUsersReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(billing_timeout_id);
		billing_timeout_id = setTimeout(function() {
			self.resetBillingForm(event);
		}, 200);
	},
	
	resetBillingForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleBillingEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	}
});
window.billing_listing_main_view = Backbone.View.extend({
    initialize:function () {
        this.model.on("change", this.render, this);
		this.model.on("add", this.render, this);
    },
	events: {
		"click .delete_icon":						"confirmdeleteBilling",
		"click .delete_yes":						"deleteBilling",
		"click .delete_no":							"canceldeleteBilling",
		"click #print_billings":					"printBillings",
		"click #label_search_billings":				"Vivify",
		"click #billings_searchList":				"Vivify",
		"focus #billings_searchList":				"Vivify",
		"blur #billings_searchList":				"unVivify"
	},
    render:function () {		
		var self = this;
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({billings: this.model.toJSON()}));
		
		tableSortIt("user_listing");
		
		return this;
    },
	printBillings: function(event) {
		event.preventDefault;
		window.open("report.php#billings");
	},
	unVivify: function(event) {
		var textbox = $("#billings_searchList");
		var label = $("#label_search_billings");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#billings_searchList");
		var label = $("#label_search_billings");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	confirmdeleteBilling: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element, 450);	
		$("#confirm_delete").css({display: "none", top: arrPosition[0] - 50, left: arrPosition[1] + 50, position:'absolute'});
		$("#confirm_delete").fadeIn();
	},
	canceldeleteBilling: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteBilling: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "billing");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteBilling(event);
			$(".billing_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".billing_data_row_" + id).fadeOut();
			}, 2500);
		}
	}
});
window.billing_listing_print_view = Backbone.View.extend({

    initialize:function () {
        this.model.on("change", this.render, this);
		this.model.on("add", this.render, this);
    },

    render:function () {		
		var self = this;
		
		$(this.el).html(this.template({billings: this.model.toJSON()}));
		
		
		return this;
    }

});
window.medical_billing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"keyup .balance_field":					"calculateBalance",
		"keyup #overrideInput":					"setOverride",
		"change .balance_field":				"calculateBalance",
		"click #medical_billing_view_done":		"doTimeouts"
	},
    render:function () {	
		if (typeof this.template != "function") {
			var view = "medical_billing_view";
			var extension = "php";
			this.model.set("holder", "myModalBody");
			loadTemplate(view, extension, this);
			return "";
	   	}		
		var self = this;
		
		var hash = document.location.hash;
		//var arrHash = hash.split("/");
		//this.model.set("corporation_id", arrHash[2]);		
		//finalized
		if (this.model.get("finalized")!="0000-00-00") {
			this.model.set("finalized", moment(this.model.get("finalized")).format("YYYY-MM-DD"));
		}
		$(this.el).html(this.template(this.model.toJSON()));
		
		return this;
	},
	calculateBalance: function() {
		var billed = $("#billedInput").val();
		var paid = $("#paidInput").val();
		var adjusted = $("#adjustedInput").val();
		
		var balance = Number(billed) - Number(paid) - Number(adjusted);
		
		$("#balanceInput").val(balance);
	},
	setOverride: function() {
		var override = $("#overrideInput").val();
		var balance = $("#balanceInput").val();
		
		if (Number(override) > Number(balance)) {
			$("#overrideInput").val(balance);
			override = balance;
		}
		if (override!="" && override!=0) {
			$("#override_indicator").fadeIn();
		} else {
			$("#override_indicator").fadeOut();
		}
	},
	doTimeouts: function() {
		this.setOverride();
	}
});