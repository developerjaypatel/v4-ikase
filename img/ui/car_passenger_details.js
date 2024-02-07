window.car_passenger_view = Backbone.View.extend({
	render: function () {
		mymodel = this.model.toJSON();
		
		$(this.el).html(this.template(mymodel));
        return this;
	},
	events:{
		//"mouseover .roll_over": 										"changeImage",
		"click .roll_over": 										"changeImage",
		"dblclick .property_damage .gridster_border": 				"editPropertyDamageViewField",
		"click #property_damage_all_done":							"doTimeouts"
    },
	changeImage: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		//var image_source = $(".roll_over #middle_middle").html();
		if (element.id == "rear_middle") {
			$(".roll_over#rear_middle").html("<img name='car_full_r3_c4' src='../img/ui/car_full_r3_c4.jpg' />");
		}
		if (element.id == "rear_left") {
			$(".roll_over#rear_left").html("<img name='car_full_r2_c4' src='../img/ui/car_full_r2_c4.jpg' />");
		}
		if (element.id == "rear_right") {
			$(".roll_over#rear_right").html("<img name='car_full_r4_c4' src='../img/ui/car_full_r4_c4.jpg' />");
		}
		if (element.id == "middle_middle") {
			$(".roll_over#middle_middle").html("<img name='car_full_r3_c5' src='../img/ui/car_full_r3_c5.jpg' id='' />");
		}
		if (element.id == "middle_right") {
			$(".roll_over#middle_right").html("<img name='car_full_r4_c5' src='../img/ui/car_full_r4_c5.jpg' />");
		}
		if (element.id == "middle_left") {
			$(".roll_over#middle_left").html("<img name='car_full_r3_c4' src='../img/ui/car_full_r2_c5.jpg' />");
		}
		if (element.id == "front_middle") {
			$(".roll_over#front_middle").html("<img name='car_full_r3_c6' src='../img/ui/car_full_r3_c6.jpg'/>");
		}
		if (element.id == "front_right") {
			$(".roll_over#front_right").html("<img name='car_full_r4_c6' src='../img/ui/car_full_r4_c6.jpg' />");
		}
		if (element.id == "front_left") {
			$(".roll_over#front_left").html("<img name='car_full_r2_c6' src='../img/ui/car_full_r2_c6.jpg' />");
		}
		if (element.id == "trunk") {
			$(".roll_over#trunk").html("<img name='car_full_r2_c2' src='../img/ui/car_full_r2_c2.jpg' />");
		}
		if (element.id == "rear_bumper_outside") {
			$(".roll_over#rear_bumper_outside").html("<img name='car_full_r1_c1' src='../img/ui/car_full_r1_c1.png' />");
		}
		if (element.id == "left_outside") {
			$(".roll_over#left_outside").html("<img name='car_full_r1_c5' src='../img/ui/car_full_r1_c5.png'/>");
		}
		if (element.id == "hood") {
			$(".roll_over#hood").html("<img name='car_full_r2_c7' src='../img/ui/car_full_r2_c7.png' />");
		}
		if (element.id == "right_outside") {
			$(".roll_over#right_outside").html("<img name='car_full_r5_c5' src='../img/ui/car_full_r5_c5.png' />");
		}
		//console.log(image_source);
		
		gridsterById('gridster_property_damage');
		this.model.set("editing", false);

		$(".property_damage .edit").trigger("click"); 
		$(".property_damage .delete").hide();
		$(".property_damage .reset").hide();
		
	}
});