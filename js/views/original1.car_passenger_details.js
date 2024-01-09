window.car_passenger_view = Backbone.View.extend({
	render: function () {
		if (typeof this.template != "function") {
			var view = "car_passenger_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var self = this;
		var mymodel = this.model.toJSON();
		
		$(this.el).html(this.template(mymodel));
		
		setTimeout(function() {
			var accident_details = JSON.parse(mymodel.accident_details);
			_.each( accident_details, function(the_details) {
				var accident_form = the_details.form;
				var arrForm = accident_form.split("_");
				var accident_partie = arrForm[0];
				
				//because accident details has ALL of the data, make sure to only look at if form matches model form
				if (accident_partie == mymodel.accident_partie) {
					var form_name = arrForm[1];
					//we got the class name, now get values for each input
					_.each( the_details.data, function(the_detail) {
						//console.log(the_detail);
						if (form_name != "carpassengers") {
							$("." + accident_partie + " #" + the_detail.name).val(the_detail.value);
						} else {
							file_extension = "jpg";
							switch(the_detail.name) {
								case "rear_bumper_outside":
									file_extension = "png";
									break;
								case "left_outside":
									file_extension = "png";
									break;
								case "right_outside":
									file_extension = "png";
									break;
								case "hood":
									file_extension = "png";
									break;
							}
							
							
							//empty seat
							var seat_image = "<img name='car_full_" + the_detail.name + "' src='../img/ui/car_empty_" + the_detail.name + "." + file_extension + "'>";
							if (the_detail.value == "Y") {
									var input_val = $("." + mymodel.accident_partie + " .roll_over#" + the_detail.name).html();
									if (input_val == "Y") {
										seat_image = "<img name='car_full_" + the_detail.name + "' src='../img/ui/car_full_" + the_detail.name + "." + file_extension + "'>";
									}
							} 
							$("." + mymodel.accident_partie + " .roll_over#" + the_detail.name).html(seat_image);
						}
					});
				}
			});
		}, 2000);
		
		return this;
	},
	events:{
		"click .roll_over": 										"changeImage",
		"dblclick .property_damage .gridster_border": 				"editPropertyDamageViewField",
		"click #car_passenger_all_done":							"doTimeouts"
    },
	changeImage: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		//var image_source = $(".roll_over #middle_middle").html();
		var red_seat = $("." + this.model.get("accident_partie") + " .red#" + element.id);
		var qualifier = "";
		var file_extension = "";
		if (red_seat.length <= 0) {
			$("." + this.model.get("accident_partie") + " #" + element.id)[0].className = $("." + this.model.get("accident_partie") + " #" + element.id)[0].className + " red";
			qualifier = "full";
			file_extension = "jpg";
			switch(element.id) {
				case "rear_bumper_outside":
					file_extension = "png";
					break;
				case "left_outside":
					file_extension = "png";
					break;
				case "right_outside":
					file_extension = "png";
					break;
				case "hood":
					file_extension = "png";
					break;
			}
		} else { 
			$("." + this.model.get("accident_partie") + " #" + element.id)[0].className = "";
			$("." + this.model.get("accident_partie") + " #" + element.id)[0].className = "roll_over";
			file_extension = "jpg";
			switch(element.id) {
				case "rear_bumper_outside":
					file_extension = "png";
					break;
				case "left_outside":
					file_extension = "png";
					break;
				case "right_outside":
					file_extension = "png";
					break;
				case "hood":
					file_extension = "png";
					break;
			}
			qualifier = "empty";
		}
		$("." + this.model.get("accident_partie") + " .input_class#" + element.id).val("Y");
		$("." + this.model.get("accident_partie") + " .roll_over#" + element.id).html("<img name='car_full_" + element.id + "' src='../img/ui/car_" + qualifier + "_" + element.id + "." + file_extension + "' id='' />");
		//console.log(image_source);
	},
	doTimeouts: function(){
		gridsterById('gridster_car_passenger');
		this.model.set("editing", false);

		$(".property_damage .edit").trigger("click"); 
		$(".property_damage .delete").hide();
		$(".property_damage .reset").hide();
	}
});