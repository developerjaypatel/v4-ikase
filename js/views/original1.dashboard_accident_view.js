window.dashboard_accident_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
		"click .save_accident_link":				"saveAccident"
    },
    render:function () {
		var self = this;
		try {
			$(this.el).html(this.template());
		}
		catch(err) {
			var view = "dashboard_accident_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		//put applicant info in subview holder
		setTimeout(function() {
			self.doTimeouts();
		}, 2000);
		return this;
	},
	doTimeouts: function(event) {
		var self = this;
		$('#accident_holder').html(new accident_view({model: this.model}).render().el);
			
		//plaintiff
		self.model.set("accident_partie", "plaintiff");
		self.model.set("holder", "rental_holder");
		$('#rental_holder').html(new rental_view({model: self.model}).render().el);
		$('#property_damage_holder').html(new property_damage_view({model: self.model}).render().el);
		
		var car_model = self.model.clone();
		car_model.set("holder", "car_holder");
		$('#car_holder').html(new car_passenger_view({model: car_model}).render().el);
		
		self.model.set("holder", "priors_holder");
		self.model.set("accident_partie", "plaintiff");
		$('#priors_holder').html(new priors_view({model: self.model}).render().el);
		
		var defendant_model = self.model.clone();
		defendant_model.set("accident_partie", "defendant");
		defendant_model.set("holder", "defendant_rental_holder");
		$('#defendant_rental_holder').html(new rental_view({model: defendant_model}).render().el);
		$('#defendant_property_damage_holder').html(new property_damage_view({model: defendant_model}).render().el);
		
		var defendant_car_model = defendant_model.clone();
		defendant_car_model.set("holder", "defendant_car_holder");
		$('#defendant_car_holder').html(new car_passenger_view({model: defendant_car_model}).render().el);
		
		var defendant_priors_model = self.model.clone();
		defendant_priors_model.set("holder", "defendant_priors_holder");
		$('#defendant_priors_holder').html(new priors_view({model: defendant_priors_model}).render().el);
		
    },
	saveAccident: function(event) {
		var self = this;
		var url = "api/accident/add";
		var inputArr = $("#accident_form .input_class").serializeArray();
		var inputPlaintiffAddCostArr = $(".plaintiff #rental_form .input_class").serializeArray();
		var inputDefendantAddCostArr = $(".defendant #rental_form .input_class").serializeArray();
		var inputPlaintiffPropDamageArr = $(".plaintiff #property_damage_form .input_class").serializeArray();
		var inputDefendantPropDamageArr = $(".defendant #property_damage_form .input_class").serializeArray();
		var inputPlaintiffPriorsArr = $(".plaintiff #priors_form .input_class").serializeArray();
		var inputDefendantPriorsArr = $(".defendant #priors_form .input_class").serializeArray();
		var inputPlaintiffCarPassengerArr = $(".plaintiff #car_passenger_form .input_class").serializeArray();
		var inputDefendantCarPassengerArr = $(".defendant #car_passenger_form .input_class").serializeArray();
		
		var arrForms = [{"form":"plaintiff_rental", "data":inputPlaintiffAddCostArr}, {"form":"defendant_rental", "data":inputDefendantAddCostArr}, {"form":"plaintiff_property_damage", "data":inputPlaintiffPropDamageArr}, {"form":"defendant_property_damage", "data":inputDefendantPropDamageArr}, {"form":"plaintiff_priors", "data":inputPlaintiffPriorsArr}, {"form":"defendant_priors", "data":inputDefendantPriorsArr}, {"form":"plaintiff_carpassengers", "data":inputPlaintiffCarPassengerArr}, {"form":"defendant_carpassengers", "data":inputDefendantCarPassengerArr}]
		
		//var inputBothAddCostArr = inputBothAddCostArr.merge(inputPlaintiffAddCostArr, inputDefendantAddCostArr);
		//var inputBothAddCostArr = [{"name":"plaintiff", "data":inputPlaintiffAddCostArr}, {"name":"defendant", "data":inputDefendantAddCostArr}];
		//var inputArr = $("#accident_form .input_class").serializeArray();
		var accident_date = $("#accident_dateInput").val();
		formValues = "case_id=" + current_case_id + "&accident_date=" + accident_date;
		var accident_description = $("#accident_descriptionInput").val();
		formValues += "&accident_description=" + accident_description;
		formValues += "&accident_info=" + JSON.stringify(inputArr);
		formValues += "&accident_details=" + JSON.stringify(arrForms);
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//indicate success
					console.log("success");
				}
			}
		});
    }
});
window.dashboard_slipandfall_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
  		"click #dashboard_slipandfall_all_done":						"doTimeouts"
    },
    render:function () {
		var self = this;
		try {
			$(this.el).html(this.template());
		}
		catch(err) {
			var view = "dashboard_slipandfall_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		$('#accident_holder').html("Slip and Fall View coming soon.");
		//put applicant info in subview holder
		return this;
	}
});
window.dashboard_motorcycle_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
  		"click #dashboard_motorcycle_all_done":						"doTimeouts"
    },
    render:function () {
		var self = this;
		try {
			$(this.el).html(this.template());
		}
		catch(err) {
			var view = "dashboard_motorcycle_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		$('#accident_holder').html("Motorcycle View coming soon.");
		//put applicant info in subview holder
		return this;
	}
});
window.dashboard_naturalcause_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
  		"click #dashboard_naturalcause_all_done":						"doTimeouts"
    },
    render:function () {
		var self = this;
		try {
			$(this.el).html(this.template());
		}
		catch(err) {
			var view = "dashboard_naturalcause_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		$('#accident_holder').html("Natural Cause View coming soon.");
		//put applicant info in subview holder
		return this;
	}
});
