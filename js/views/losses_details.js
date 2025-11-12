window.losses_view = Backbone.View.extend({
	events:{
		"click #losses_view_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "losses_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		//$(this.el).html(this.template(this.model.toJSON()));
		
        return this;
	},
	doTimeouts: function() {
		var self = this;
		
		//costs
		self.model.set("blnMedicals", false);
		var url = "api/medicalbillingsummary/" + current_case_id;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#medical_amount").html(formatDollar(data.billed_total));
					data.balance = String(data.balance).numbersOnly();
					$("#medical_balance").html(formatDollar(data.balance));
					
					$("#misc_costs_amount").html(formatDollar(data.costs));
					$("#misc_costs_balance").html(formatDollar(data.costs));
				}
				self.model.set("blnMedicals", true);
				self.calculateTotalLosses();
			}
		});
		
		//lost wages
		self.model.set("blnIncome", false);
		var url = "api/kase/lostincometotal/" + current_case_id;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					data.losses = String(data.losses).numbersOnly();
					$("#lost_income_amount").html(formatDollar(data.losses));
					$("#lost_income_balance").html(formatDollar(data.losses));
				}
				self.model.set("blnIncome", true);
				self.calculateTotalLosses();
			}
		});
		
		self.model.set("blnDamages", false);
		var kase = kases.findWhere({case_id: current_case_id});
		
		var case_type = kase.get("case_type");
		var injury_type = kase.get("injury_type");
		var representing = "";
		var arrInjury = injury_type.split("|");
		injury_type = arrInjury[0];
		if (arrInjury.length==2) {
			representing = arrInjury[1];
		}
		if (case_type=="immigration") {
			var personal_injury = new Injury({"id": current_case_id});
		} else {
			var personal_injury = new PersonalInjury({"case_id": current_case_id});
		}
		
		if (injury_type == "carpass" || case_type=="immigration") {
			personal_injury.fetch({
				success: function(personal_injury) {
					var repair_data = personal_injury.toJSON().repair_info;
					if (repair_data=="") {
						return;
					}
					var repair_info = JSON.parse(repair_data)[representing];
					if (typeof repair_info == "undefined") {
						return;
					}
					Object.keys(repair_info).forEach(function(key) {
						var key_name = key;
						
						if (key_name=="blue_bookInput") {
							$("#damages_amount").html(formatDollar(repair_info[key]));
						}
						if (key_name=="balanceInput") {
							$("#damages_balance").html(formatDollar(repair_info[key]));
						}
					})
					self.model.set("blnDamages", true);
					self.calculateTotalLosses();
				}
			});
		} else {
			self.model.set("blnDamages", true);
		}
		
		self.model.set("blnDeductions", false);
		var url = "api/deductionstotal/" + current_case_id;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					data.total_amount = String(data.total_amount).numbersOnly();
					$("#deductions_amount").html(formatDollar(data.total_amount));
					var balance = Number(data.total_amount) - Number(data.total_payment);
					$("#deductions_balance").html(formatDollar(balance));
				}
				self.model.set("blnDeductions", true);
				self.calculateTotalLosses();
			}
		});
	},
	calculateTotalLosses: function() {
		var blnDamages = this.model.get("blnDamages");
		var blnMedicals = this.model.get("blnMedicals");
		var blnIncome = this.model.get("blnIncome");
		var blnDeductions = this.model.get("blnDeductions");
		
		if (!blnDamages || !blnMedicals || !blnIncome || !blnDeductions) {
			return;
		}
		var arrLoss = [
			"damages", 
			"lost_income", 
			"medical",
			"misc_costs",
			"deductions"
		];
		var total_loss = 0;
		var total_due = 0
		arrLoss.forEach(function(index) {
			var loss = Number($("#" + index + "_amount").html().replaceTout(",", ""));
			var due = Number($("#" + index + "_balance").html().replaceTout(",", ""));
			
			total_loss += loss;
			total_due += due;
		});
		
		$("#loss_summary_total").html("$" + formatDollar(total_loss));
		$("#loss_summary_balance").html("$" + formatDollar(total_due));
	}
});

window.losses_list_view = Backbone.View.extend({
	events:{
		"click #losses_list_view_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "losses_list_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		//$(this.el).html(this.template(this.model.toJSON()));
		
        return this;
	},
	doTimeouts: function() {
		var self = this;
		
		//costs
		self.model.set("blnMedicals", false);
		var url = "api/medicalbillingsummary/" + current_case_id;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#medical_list_amount").html(formatDollar(data.billed_total));
					data.balance = String(data.balance).numbersOnly();
					$("#medical_list_balance").html(formatDollar(data.balance));
					
					$("#misc_costs_list_amount").html(formatDollar(data.costs));
					$("#misc_costs_list_balance").html(formatDollar(data.costs));
				}
				self.model.set("blnMedicals", true);
				self.calculateTotalLosses();
			}
		});
 
		//lost wages
		self.model.set("blnIncome", false);
		var url = "api/kase/lostincometotal/" + current_case_id;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					data.losses = String(data.losses).numbersOnly();
					$("#lost_income_list_amount").html(formatDollar(data.losses));
					$("#lost_income_list_balance").html(formatDollar(data.losses));
				}
				self.model.set("blnIncome", true);
				self.calculateTotalLosses();
			}
		});
		
		self.model.set("blnDamages", false);
		var kase = kases.findWhere({case_id: current_case_id});
		console.log ("find damage case ", kase);
		var case_type = kase.get("case_type");
		var injury_type = kase.get("injury_type");
		var representing = "";
		var arrInjury = injury_type.split("|");
		injury_type = arrInjury[0];
		if (arrInjury.length==2) {
			representing = arrInjury[1];
		}
		if (case_type=="immigration") {
			var personal_injury = new Injury({"id": current_case_id});
		} else {
			var personal_injury = new PersonalInjury({"case_id": current_case_id});
		}
		
		if (injury_type == "carpass" || case_type=="immigration") {
			personal_injury.fetch({
				success: function(personal_injury) {
					var repair_data = personal_injury.toJSON().repair_info;
					if (repair_data=="") {
						return;
					}
					var repair_info = JSON.parse(repair_data)[representing];
					if (typeof repair_info == "undefined") {
						return;
					}
					Object.keys(repair_info).forEach(function(key) {
						var key_name = key;
						
						if (key_name=="blue_bookInput") {
							$("#damages_list_amount").html(formatDollar(repair_info[key]));
						}
						if (key_name=="balanceInput") {
							$("#damages_list_balance").html(formatDollar(repair_info[key]));
						}
					})
					self.model.set("blnDamages", true);
					self.calculateTotalLosses();
				}
			});
		} else {
			self.model.set("blnDamages", true);
		}
		
		self.model.set("blnDeductions", false);
		var url = "api/deductionstotal/" + current_case_id;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					data.total_amount = String(data.total_amount).numbersOnly();
					$("#deductions_list_amount").html(formatDollar(data.total_amount));
					var balance = Number(data.total_amount) - Number(data.total_payment) - Math.abs(Number(data.total_adjustment));
					$("#deductions_list_balance").html(formatDollar(balance));
				}
				self.model.set("blnDeductions", true);
				self.calculateTotalLosses();
			}
		});

		// other bill
		self.model.set("blnOthers", false);
		var url = "api/otherbillingsummary/" + current_case_id;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#other_list_amount").html(formatDollar(data.billed_total));
					data.balance = String(data.balance).numbersOnly();
					$("#other_list_balance").html(formatDollar(data.balance));
					
					$("#misc_costs_list_amount").html(formatDollar(data.costs));
					$("#misc_costs_list_balance").html(formatDollar(data.costs));
				}
				self.model.set("blnMedicals", true);
				self.calculateTotalLosses();
			}
		});
	},
	calculateTotalLosses: function() {
		/*
		var blnDamages = this.model.get("blnDamages");
		var blnMedicals = this.model.get("blnMedicals");
		var blnIncome = this.model.get("blnIncome");
		var blnDeductions = this.model.get("blnDeductions");
		
		if (!blnDamages || !blnMedicals || !blnIncome || !blnDeductions) {
			return;
		}
		*/
		var arrLoss = [
			"damages", 
			"lost_income", 
			"medical",
			"misc_costs",
			"deductions",
			'other'
		];
		var total_loss = 0;
		var total_due = 0
		arrLoss.forEach(function(index) {
			var loss = Number($("#" + index + "_list_amount").html().replaceTout(",", ""));
			var due = Number($("#" + index + "_list_balance").html().replaceTout(",", ""));
			
			total_loss += loss;
			total_due += due;
		});
		
		$("#loss_summary_list_total").html("$" + formatDollar(total_loss));
		$("#loss_summary_list_balance").html("$" + formatDollar(total_due));
	}
});