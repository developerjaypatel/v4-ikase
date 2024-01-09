var personal_injury_timeout_id;
window.personal_injury_view = Backbone.View.extend({
	events:{
		
		"dblclick .personal_injury .gridster_border": 			"editPersonalInjuriesField",
		"click .personal_injury .save":							"addPersonalInjury",
		"click .personal_injury .save_field":					"savePersonalInjuriesField",
		"click .personal_injury .edit": 						"schedulePersonalInjuriesEdit",
		"click .personal_injury .reset": 						"schedulePersonalInjuriesReset",
		"blur .personal_injury #personal_injury_dateInput": 	"splitDate",
		"click #personal_injury_done":							"doTimeouts",
		"click .previous_car .roll_over": 						"changeImage",
		"click #plaintiff_car_information .roll_over": 			"changeImagePlaintiff",
		"click #defendant_car_information .roll_over": 			"changeImageDefendant"
		//"click #switch_party_info_defendant": 					"showDefendantInfo",
		//"click #switch_party_info_plaintiff": 					"showPlaintiffInfo"
    },
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "personal_injury_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		//$(this.el).html(this.template(this.model.toJSON()));
		
		
        return this;
	},
	showDefendantInfo: function(event) {
		$("#plaintiff_car_information").fadeOut(function() {
			$("#defendant_car_information").show();
		});
	},
	showPlaintiffInfo: function(event) {
		$("#defendant_car_information").fadeOut(function() {
			$("#plaintiff_car_information").show();
		});
	},
	changeImage: function(event) {
			if (!blnPiReady) {
				event.preventDefault();
				if (this.model.get("editing") == false && this.model.id > 0) {
					this.model.set("editing", true);
					toggleFormEdit("personal_injury");
				} 
				var element = event.currentTarget;
				//var image_source = $(".roll_over #middle_middle").html();
				var red_seat = $(".red#" + element.id);
				var qualifier = "";
				var file_extension = "";
				if (red_seat.length <= 0) {
					$(" #" + element.id)[0].className = $("#" + element.id)[0].className + " red";
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
					$("#" + element.id)[0].className = "";
					$("#" + element.id)[0].className = "roll_over";
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
				$(".input_class#" + element.id).val("Y");
				$(".roll_over#" + element.id).html("<img name='car_full_" + element.id + "' src='../img/ui/car_" + qualifier + "_" + element.id + "." + file_extension + "' id='' />");
				//console.log(image_source);
			} else {
				
			/*
				event.preventDefault();
			var element = event.currentTarget;
			if (this.model.get("editing") == false && this.model.id > 0) {
				this.model.set("editing", true);
				toggleFormEdit("personal_injury");
			} 
			
			//var image_source = $(".roll_over #middle_middle").html();
			var red_seat = $(".red#" + element.id);
			var qualifier = "";
			var file_extension = "";
			if (red_seat.length <= 0) {
				$(" #" + element.id)[0].className = $("#" + element.id)[0].className + " red";
				qualifier = "full";
				file_extension = "jpg";
				
			} else { 
				$("#" + element.id)[0].className = "";
				$("#" + element.id)[0].className = "roll_over";
				file_extension = "jpg";
				switch(element.id) {
					case "front_left_quarter_panel":
						file_extension = "png";
						break;
					case "rear_left_quarter_panel":
						file_extension = "png";
						break;
					case "outside_rear_left_quarter_panel":
						file_extension = "png";
						break;
					case "front_right_quarter_panel":
						file_extension = "png";
						break;
					case "rear_right_quarter_panel":
						file_extension = "png";
						break;
					case "outside_rear_right_quarter_panel":
						file_extension = "png";
						break;
					case "hood":
						file_extension = "png";
						break;
					case "outside_hood":
						file_extension = "png";
						break;
					case "rear_right_corner":
						file_extension = "png";
						break;
					case "rear_left_corner":
						file_extension = "png";
						break;
					case "rear_bumper":
						file_extension = "png";
						break;
					case "outside_rear_bumper":
						file_extension = "png";
						break;
					case "left_side_mirror":
						file_extension = "png";
						break;
					case "right_side_mirror":
						file_extension = "png";
						break
					case "outside_front_left_door":
						file_extension = "png";
						break;
					case "outside_front_left_quarter_panel":
						file_extension = "png";
						break;
					case "outside_front_right_door":
						file_extension = "png";
						break;
					case "outside_front_right_quarter_panel":
						file_extension = "png";
						break;
					case "outside_middle_left_door":
						file_extension = "png";
						break;
					case "outside_middle_right_door":
						file_extension = "png";
						break;
					case "outside_rear_left_door":
						file_extension = "png";
						break;
					case "outside_rear_right_door":
						file_extension = "png";
						break;
				}
				qualifier = "empty";
			}
			$(".input_class#" + element.id).val("Y");
			$(".roll_over#" + element.id).html("<img name='new_car_full_" + element.id + "' src='../images/new_car_parts/new_car_" + qualifier + "_" + element.id + "." + file_extension + "' id='' />");
			//console.log(image_source);
			*/
			}
	},
	changeImagePlaintiff: function(event) {
			if (customer_id == "1033" || customer_id == "1063") {
			event.preventDefault();
			var element = event.currentTarget;
			if (this.model.get("editing") == false && this.model.id > 0) {
				this.model.set("editing", true);
				toggleFormEdit("personal_injury");
			} 
			
			//var image_source = $(".roll_over #middle_middle").html();
			var red_seat = $("#plaintiff_car_information .red#" + element.id);
			var qualifier = "";
			var file_extension = "";
			if (red_seat.length <= 0) {
				$("#plaintiff_car_information #" + element.id)[0].className = $("#plaintiff_car_information #" + element.id)[0].className + " red";
				qualifier = "full";
				file_extension = "jpg";
				
			} else { 
				$("#plaintiff_car_information td#" + element.id)[0].className = "";
				$("#plaintiff_car_information td#" + element.id)[0].className = "roll_over";
				file_extension = "jpg";
				switch(element.id) {
					case "front_left_quarter_panel":
						file_extension = "png";
						break;
					case "rear_left_quarter_panel":
						file_extension = "png";
						break;
					case "outside_rear_left_quarter_panel":
						file_extension = "png";
						break;
					case "front_right_quarter_panel":
						file_extension = "png";
						break;
					case "rear_right_quarter_panel":
						file_extension = "png";
						break;
					case "outside_rear_right_quarter_panel":
						file_extension = "png";
						break;
					case "hood":
						file_extension = "png";
						break;
					case "outside_hood":
						file_extension = "png";
						break;
					case "rear_right_corner":
						file_extension = "png";
						break;
					case "rear_left_corner":
						file_extension = "png";
						break;
					case "rear_bumper":
						file_extension = "png";
						break;
					case "outside_rear_bumper":
						file_extension = "png";
						break;
					case "left_side_mirror":
						file_extension = "png";
						break;
					case "right_side_mirror":
						file_extension = "png";
						break
					case "outside_front_left_door":
						file_extension = "png";
						break;
					case "outside_front_left_quarter_panel":
						file_extension = "png";
						break;
					case "outside_front_right_door":
						file_extension = "png";
						break;
					case "outside_front_right_quarter_panel":
						file_extension = "png";
						break;
					case "outside_middle_left_door":
						file_extension = "png";
						break;
					case "outside_middle_right_door":
						file_extension = "png";
						break;
					case "outside_rear_left_door":
						file_extension = "png";
						break;
					case "outside_rear_right_door":
						file_extension = "png";
						break;
				}
				qualifier = "empty";
			}
			$("#plaintiff_car_information .input_class#" + element.id).val("Y");
			$("#plaintiff_car_information .roll_over#" + element.id).html("<img name='new_car_full_" + element.id + "' src='../images/new_car_parts/new_car_" + qualifier + "_" + element.id + "." + file_extension + "' id='' />");
			//console.log(image_source);
			}
	},
	changeImageDefendant: function(event) {
		if (customer_id == "1033" || customer_id == "1063") {
			event.preventDefault();
			var element = event.currentTarget;
			var element_id = element.id.replace("defendant_", "");
			//alert(element_id);
			//return;
			if (this.model.get("editing") == false && this.model.id > 0) {
				this.model.set("editing", true);
				toggleFormEdit("personal_injury");
			} 
			
			//var image_source = $(".roll_over #middle_middle").html();
			var red_seat = $("#defendant_car_information .red#" + element.id);
			var qualifier = "";
			var file_extension = "";
			if (red_seat.length <= 0) {
				$("#defendant_car_information #" + element.id)[0].className = $("#defendant_car_information #" + element.id)[0].className + " red";
				qualifier = "full";
				file_extension = "jpg";
				
			} else { 
				$("#defendant_car_information td#" + element.id)[0].className = "";
				$("#defendant_car_information td#" + element.id)[0].className = "roll_over";
				file_extension = "jpg";
				
				switch(element_id) {
					case "front_left_quarter_panel":
						file_extension = "png";
						break;
					case "rear_left_quarter_panel":
						file_extension = "png";
						break;
					case "outside_rear_left_quarter_panel":
						file_extension = "png";
						break;
					case "front_right_quarter_panel":
						file_extension = "png";
						break;
					case "rear_right_quarter_panel":
						file_extension = "png";
						break;
					case "outside_rear_right_quarter_panel":
						file_extension = "png";
						break;
					case "hood":
						file_extension = "png";
						break;
					case "outside_hood":
						file_extension = "png";
						break;
					case "rear_right_corner":
						file_extension = "png";
						break;
					case "rear_left_corner":
						file_extension = "png";
						break;
					case "rear_bumper":
						file_extension = "png";
						break;
					case "outside_rear_bumper":
						file_extension = "png";
						break;
					case "left_side_mirror":
						file_extension = "png";
						break;
					case "right_side_mirror":
						file_extension = "png";
						break
					case "outside_front_left_door":
						file_extension = "png";
						break;
					case "outside_front_left_quarter_panel":
						file_extension = "png";
						break;
					case "outside_front_right_door":
						file_extension = "png";
						break;
					case "outside_front_right_quarter_panel":
						file_extension = "png";
						break;
					case "outside_middle_left_door":
						file_extension = "png";
						break;
					case "outside_middle_right_door":
						file_extension = "png";
						break;
					case "outside_rear_left_door":
						file_extension = "png";
						break;
					case "outside_rear_right_door":
						file_extension = "png";
						break;
				}
				qualifier = "empty";
			}
			
			$("#defendant_car_information .input_class#" + element.id).val("Y");
			$("#defendant_car_information .roll_over#" + element.id).html("<img name='new_car_full_" + element.id + "' src='../images/new_car_parts/new_car_" + qualifier + "_" + element_id + "." + file_extension + "' id='' />");
			//console.log(image_source);
		}
	},
	doTimeouts: function() {
		if (!blnPiReady) {
			var self = this;
			
			//$('#car_holder').html(new car_passenger_pi_view({model: this.model.toJSON()}).render().el);
			
			//we are not in editing mode initially
			gridsterById("gridster_personal_injury");
			gridsterById("gridster_accident");
			
			//gridsterById("gridster_car_passenger");
			this.model.set("editing", false);
			
			setTimeout(function() {
				if (self.model.id<0) {
					$( ".personal_injury .edit" ).trigger( "click" );
				}
			}, 600);
			if (self.model.id > 0) {
				//alert(self.model.personal_injury_info);
				//console.log(self.model.personal_injury_info);
				var model_personal_injury_info = self.model.get("personal_injury_info");
				
				//alert(model_personal_injury_info);
				var personal_injury_info = JSON.parse(model_personal_injury_info);
				_.each( personal_injury_info, function(the_info) {
					$("#" + the_info.name).val(the_info.value);
					the_info.name = the_info.name.replace("Input", "Span");
					$("#" + the_info.name).html(the_info.value);
				});
				
				var model_personal_injury_details = self.model.get("personal_injury_details");
				
				var personal_injury_details = JSON.parse(model_personal_injury_details);
				_.each( personal_injury_details, function(the_details) {
					var personal_injury_form = the_details.form;
					var arrForm = personal_injury_form.split("_");
					
					//we got the class name, now get values for each input
					_.each( the_details.data, function(the_detail) {
						//console.log(the_detail);
						$("#" + the_detail.name).val(the_detail.value);
						the_detail.name = the_detail.name.replace("Input", "Span");
						$("#" + the_detail.name).html(the_detail.value);
						
						if (the_detail.name == "left_outside" || the_detail.name == "hood" || the_detail.name == "trunk" || the_detail.name == "rear_left" ||the_detail.name == "middle_left" || the_detail.name == "front_left" || the_detail.name == "rear_middle" || the_detail.name == "middle_middle" || the_detail.name == "front_middle" || the_detail.name == "rear_right" || the_detail.name == "middle_right" || the_detail.name == "front_right" || the_detail.name == "right_outside" || the_detail.name == "rear_bumper_outside") {
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
									//var input_val = $(".roll_over#" + the_detail.name).html();
									//if (input_val == "Y") {
										seat_image = "<img name='car_full_" + the_detail.name + "' src='../img/ui/car_full_" + the_detail.name + "." + file_extension + "'>";
									//}
							} 
							$(".roll_over#" + the_detail.name).html(seat_image);
						}
						
					});
				});
	
			}
			$(".roll_over").css("cursor", "pointer");
			setTimeout(function() {
				$("#personal_injury_dateInput").datetimepicker();
				initializeGoogleAutocomplete('personal_injury');
			}, 1000);	
			//var session_id = /SESS\w*ID=([^;]+)/i.test(document.cookie) ? RegExp.$1 : false;
			//console.log(session_id);
			//var blnNewCar = true;
		} else {
			var self = this;
		
			//$('#car_holder').html(new car_passenger_pi_view({model: this.model.toJSON()}).render().el);
			
			//we are not in editing mode initially
			gridsterById("gridster_personal_injury");
			gridsterById("gridster_accident");
			
			//gridsterById("gridster_car_passenger");
			this.model.set("editing", false);
			
			setTimeout(function() {
				if (self.model.id<0) {
					$( ".personal_injury .edit" ).trigger( "click" );
				}
			}, 600);
			if (self.model.id > 0) {
				//alert(self.model.personal_injury_info);
				//console.log(self.model.personal_injury_info);
				var model_personal_injury_info = self.model.get("personal_injury_info");
				
				//alert(model_personal_injury_info);
				var personal_injury_info = JSON.parse(model_personal_injury_info);
				_.each( personal_injury_info, function(the_info) {
					$("#" + the_info.name).val(the_info.value);
					the_info.name = the_info.name.replace("Input", "Span");
					$("#" + the_info.name).html(the_info.value);
				});
				
				var model_personal_injury_details = self.model.get("personal_injury_details");
				
				var personal_injury_details = JSON.parse(model_personal_injury_details);
				_.each( personal_injury_details, function(the_details) {
					var personal_injury_form = the_details.form;
					var arrForm = personal_injury_form.split("_");
					
					//we got the class name, now get values for each input
					_.each( the_details.data, function(the_detail) {
						//console.log(the_detail);
						$("#" + the_detail.name).val(the_detail.value);
						the_detail.name = the_detail.name.replace("Input", "Span");
						$("#" + the_detail.name).html(the_detail.value);
						
						if (the_detail.name == "outside_rear_left_quarter_panel" || the_detail.name == "outside_rear_left_door" || the_detail.name == "outside_middle_left_door" || the_detail.name == "outside_front_left_door" ||the_detail.name == "left_side_mirror" || the_detail.name == "outside_front_left_quarter_panel" || the_detail.name == "rear_left_corner" || the_detail.name == "rear_left_quarter_panel" || the_detail.name == "rear_left_door" || the_detail.name == "middle_left_door" || the_detail.name == "front_left_door" || the_detail.name == "front_left_quarter_panel" || the_detail.name == "outside_rear_bumper" || the_detail.name == "rear_bumper" || the_detail.name == "trunk" || the_detail.name == "rear_left_seat" || the_detail.name == "middle_left_seat" || the_detail.name == "front_left_seat" ||the_detail.name == "windshield" || the_detail.name == "hood" || the_detail.name == "outside_hood" || the_detail.name == "rear_middle_seat" || the_detail.name == "middle_middle_seat" || the_detail.name == "front_middle_seat" || the_detail.name == "rear_right_seat" || the_detail.name == "middle_right_seat" || the_detail.name == "front_right_seat" || the_detail.name == "rear_right_corner" || the_detail.name == "rear_right_quarter_panel" || the_detail.name == "rear_right_door" || the_detail.name == "middle_right_door" || the_detail.name == "front_right_door" || the_detail.name == "right_side_mirror" || the_detail.name == "front_right_quarter_panel" ||the_detail.name == "outside_rear_right_quarter_panel" || the_detail.name == "outside_rear_right_door" || the_detail.name == "outside_middle_right_door" || the_detail.name == "outside_front_right_door" || the_detail.name == "outside_front_right_quarter_panel") {
							file_extension = "jpg";
							
							the_detail_name = the_detail.name.replace("defendant_", "");
							
							if (the_detail.value != "Y") {
								switch(the_detail.name) {
									case "front_left_quarter_panel":
										file_extension = "png";
										break;
									case "rear_left_quarter_panel":
										file_extension = "png";
										break;
									case "outside_rear_left_quarter_panel":
										file_extension = "png";
										break;
									case "front_right_quarter_panel":
										file_extension = "png";
										break;
									case "rear_right_quarter_panel":
										file_extension = "png";
										break;
									case "outside_rear_right_quarter_panel":
										file_extension = "png";
										break;
									case "hood":
										file_extension = "png";
										break;
									case "outside_hood":
										file_extension = "png";
										break;
									case "rear_right_corner":
										file_extension = "png";
										break;
									case "rear_left_corner":
										file_extension = "png";
										break;
									case "rear_bumper":
										file_extension = "png";
										break;
									case "outside_rear_bumper":
										file_extension = "png";
										break;
									case "left_side_mirror":
										file_extension = "png";
										break;
									case "right_side_mirror":
										file_extension = "png";
										break
									case "outside_front_left_door":
										file_extension = "png";
										break;
									case "outside_front_left_quarter_panel":
										file_extension = "png";
										break;
									case "outside_front_right_door":
										file_extension = "png";
										break;
									case "outside_front_right_quarter_panel":
										file_extension = "png";
										break;
									case "outside_middle_left_door":
										file_extension = "png";
										break;
									case "outside_middle_right_door":
										file_extension = "png";
										break;
									case "outside_rear_left_door":
										file_extension = "png";
										break;
									case "outside_rear_right_door":
										file_extension = "png";
										break;
								}
							}
							
							if (the_detail.value == "Y") {
								var seat_image = "<img name='car_full_" + the_detail_name + "' src='../images/new_car_parts/new_car_full_" + the_detail_name + "." + file_extension + "'>";
								$(".roll_over#" + the_detail.name).html(seat_image);
								$(".roll_over#" + the_detail.name).addClass("red");
									//}
							} else {
								//empty seat
								var seat_image = "<img name='new_car_full_" + the_detail_name + "' src='../images/new_car_parts/new_car_empty_" + the_detail_name + "." + file_extension + "'>";
								$(".roll_over#" + the_detail.name).html(seat_image);
								$(".roll_over#" + the_detail.name).removeClass("red");
							}
							
							
						}
						
						if (the_detail.name == "defendant_outside_rear_left_quarter_panel" || the_detail.name == "defendant_outside_rear_left_door" || the_detail.name == "defendant_outside_middle_left_door" || the_detail.name == "defendant_outside_front_left_door" ||the_detail.name == "defendant_left_side_mirror" || the_detail.name == "defendant_outside_front_left_quarter_panel" || the_detail.name == "defendant_rear_left_corner" || the_detail.name == "defendant_rear_left_quarter_panel" || the_detail.name == "defendant_rear_left_door" || the_detail.name == "defendant_middle_left_door" || the_detail.name == "defendant_front_left_door" || the_detail.name == "defendant_front_left_quarter_panel" || the_detail.name == "defendant_outside_rear_bumper" || the_detail.name == "defendant_rear_bumper" || the_detail.name == "defendant_trunk" || the_detail.name == "defendant_rear_left_seat" || the_detail.name == "defendant_middle_left_seat" || the_detail.name == "defendant_front_left_seat" ||the_detail.name == "defendant_windshield" || the_detail.name == "defendant_hood" || the_detail.name == "defendant_outside_hood" || the_detail.name == "defendant_rear_middle_seat" || the_detail.name == "defendant_middle_middle_seat" || the_detail.name == "defendant_front_middle_seat" || the_detail.name == "defendant_rear_right_seat" || the_detail.name == "defendant_middle_right_seat" || the_detail.name == "defendant_front_right_seat" || the_detail.name == "defendant_rear_right_corner" || the_detail.name == "defendant_rear_right_quarter_panel" || the_detail.name == "defendant_rear_right_door" || the_detail.name == "defendant_middle_right_door" || the_detail.name == "defendant_front_right_door" || the_detail.name == "defendant_right_side_mirror" || the_detail.name == "defendant_front_right_quarter_panel" ||the_detail.name == "defendant_outside_rear_right_quarter_panel" || the_detail.name == "defendant_outside_rear_right_door" || the_detail.name == "defendant_outside_middle_right_door" || the_detail.name == "defendant_outside_front_right_door" || the_detail.name == "defendant_outside_front_right_quarter_panel") {
							file_extension = "jpg";
							
							the_detail_name = the_detail.name.replace("defendant_", "");
							
							if (the_detail.value != "Y") {
								switch(the_detail_name) {
									case "front_left_quarter_panel":
										file_extension = "png";
										break;
									case "rear_left_quarter_panel":
										file_extension = "png";
										break;
									case "outside_rear_left_quarter_panel":
										file_extension = "png";
										break;
									case "front_right_quarter_panel":
										file_extension = "png";
										break;
									case "rear_right_quarter_panel":
										file_extension = "png";
										break;
									case "outside_rear_right_quarter_panel":
										file_extension = "png";
										break;
									case "hood":
										file_extension = "png";
										break;
									case "outside_hood":
										file_extension = "png";
										break;
									case "rear_right_corner":
										file_extension = "png";
										break;
									case "rear_left_corner":
										file_extension = "png";
										break;
									case "rear_bumper":
										file_extension = "png";
										break;
									case "outside_rear_bumper":
										file_extension = "png";
										break;
									case "left_side_mirror":
										file_extension = "png";
										break;
									case "right_side_mirror":
										file_extension = "png";
										break
									case "outside_front_left_door":
										file_extension = "png";
										break;
									case "outside_front_left_quarter_panel":
										file_extension = "png";
										break;
									case "outside_front_right_door":
										file_extension = "png";
										break;
									case "outside_front_right_quarter_panel":
										file_extension = "png";
										break;
									case "outside_middle_left_door":
										file_extension = "png";
										break;
									case "outside_middle_right_door":
										file_extension = "png";
										break;
									case "outside_rear_left_door":
										file_extension = "png";
										break;
									case "outside_rear_right_door":
										file_extension = "png";
										break;
								}
							}
							
							
							if (the_detail.value == "Y") {
								var seat_image = "<img name='car_full_" + the_detail_name + "' src='../images/new_car_parts/new_car_full_" + the_detail_name + "." + file_extension + "'>";
								$(".roll_over#" + the_detail.name).html(seat_image);
								$(".roll_over#" + the_detail.name).addClass("red");
									//}
							} else {
								//empty seat
								var seat_image = "<img name='new_car_full_" + the_detail_name + "' src='../images/new_car_parts/new_car_empty_" + the_detail_name + "." + file_extension + "'>";
								$(".roll_over#" + the_detail.name).html(seat_image);
								$(".roll_over#" + the_detail.name).removeClass("red");
							}
						}
						
					});
				});
	
			}
			//$("#defendant_car_information").hide();
			$(".roll_over").css("cursor", "pointer");
			setTimeout(function() {
				$("#personal_injury_dateInput").datetimepicker();
				initializeGoogleAutocomplete('personal_injury');
			}, 1000);
		}
		
	},
	splitDate: function() {
		var self = this;
		var arrDate = [];
		var date_full = $("#personal_injury_dateInput").val();
		arrDate = date_full.split(" ");
		var day_part = arrDate[0];
		var time_part = arrDate[1];
		day_part = new Date(day_part)
		day_part = day_part.getDay();
		if (day_part == "0") {
			day_part = "Sunday";
		}
		if (day_part == "1") {
			day_part = "Monday";
		}
		if (day_part == "2") {
			day_part = "Tuesday";
		}
		if (day_part == "3") {
			day_part = "Wednesday";
		}
		if (day_part == "4") {
			day_part = "Thursday";
		}
		if (day_part == "5") {
			day_part = "Friday";
		}
		if (day_part == "6") {
			day_part = "Saturday";
		}
		$("#personal_injury_dayInput").val(day_part);
		$("#personal_injury_timeInput").val(time_part);
		
		$("#personal_injury_daySpan").html(day_part);
		$("#personal_injury_timeSpan").html(time_part);
	},
	editPersonalInjuriesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".personal_injury_" + field_name;
		}
		editField(element, master_class);
	},
	editdialogField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".personal_injury_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddPersonalInjury:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.addPersonalInjury(event);
		}, 200);
	},
	addPersonalInjury:function (event) {
		event.preventDefault();
		var self = this;
		self.saveEverything(event);
		this.model.set("editing", false);
		return;
		/*
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		//turn off editing to toggle
		this.model.set("editing", false);
		addForm(event, "personal_injury");
		//turn off editing altogether
		this.model.set("editing", false);
		return;
		*/
    },
	
	savePersonalInjuriesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		console.log("save_function_next");
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		console.log("save_function_after");
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		console.log("save_function_model_start");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		addForm(event);
	},
	schedulePersonalInjuriesEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.togglePersonalInjuriesEdit(event);
		}, 200);
	},
	togglePersonalInjuriesEdit: function (event) {
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
		toggleFormEdit("personal_injury");
	},
	schedulePersonalInjuriesReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.resetPersonalInjuriesForm(event);

		}, 200);
	},
	
	resetPersonalInjuriesForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.togglePersonalInjuriesEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/personalinjury/add";
		
		var inputArr = $("#personal_injury_panel .input_class").serializeArray();
		if (!blnPiReady) {
			var inputCarPassengerArr = $(".previous_vehicle #car_passenger_form .input_class").serializeArray();
		} else {
			var inputPlaintiffCarPassengerArr = $("#plaintiff_car_information #car_passenger_form .input_class").serializeArray();
			var inputDefendantCarPassengerArr = $("#defendant_car_information #defendant_car_passenger_form .input_class").serializeArray();
		}
		var inputOtherArr = $("#personal_injury_other_form .input_class").serializeArray();
		
		if (!blnPiReady) {
			var inputVehicleArr = $("#vehicle_form .input_class").serializeArray();
		} else {
			var inputPlaintiffVehicleArr = $("#plaintiff_car_information #vehicle_form .input_class").serializeArray();
			var inputDefendantVehicleArr = $("#defendant_car_information #vehicle_form .input_class").serializeArray();
		}
		//var inputCarPassengerArr = $(".defendant #car_passenger_form .input_class").serializeArray();
		
		if (!blnPiReady) { 
			var arrForms = [{"form":"carpassengers", "data":inputCarPassengerArr}, {"form":"personal_injury_other_form", "data":inputOtherArr}, {"form":"vehicle_form", "data":inputVehicleArr}]
		} else {
			var arrForms = [{"form":"carpassengers", "data":inputPlaintiffCarPassengerArr}, {"form":"defendant_carpassengers", "data":inputDefendantCarPassengerArr}, {"form":"vehicle_form", "data":inputPlaintiffVehicleArr}, {"form":"defendant_vehicle_form", "data":inputDefendantVehicleArr}, {"form":"personal_injury_other_form", "data":inputOtherArr}]
		}
		
		var personal_injury_date = $("#personal_injury_dateInput").val();
		formValues = "case_id=" + current_case_id + "&personal_injury_date=" + personal_injury_date;
		var personal_injury_description = $("#personal_injury_accident_descriptionInput").val();
		var personal_injury_other_details = $("#personal_injury_other_detailsInput").val();
		var personal_injury_id = $("#table_id").val();
		formValues += "&personal_injury_description=" + personal_injury_description + "&personal_injury_other_details=" + personal_injury_other_details + "&table_id=" + personal_injury_id;
		formValues += "&personal_injury_info=" + JSON.stringify(inputArr);
		formValues += "&personal_injury_details=" + JSON.stringify(arrForms);
		
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
					$("#panel_title").css("color", "green");
					toggleFormEdit("personal_injury");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});

window.personal_injury_general_view = Backbone.View.extend({
	events:{
		
		"dblclick .personal_injury .gridster_border": 			"editPersonalInjuriesField",
		"click .personal_injury .save":							"addPersonalInjury",
		"click .personal_injury .save_field":					"savePersonalInjuriesField",
		"click .personal_injury .edit": 						"schedulePersonalInjuriesEdit",
		"click .personal_injury .reset": 						"schedulePersonalInjuriesReset",
		"blur .personal_injury #personal_injury_dateInput": 	"splitDate",
		"click #personal_injury_general_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "personal_injury_general_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		gridsterById("gridster_personal_injury");
		gridsterById("gridster_accident");
		
		//gridsterById("gridster_car_passenger");
		this.model.set("editing", false);
		
		setTimeout(function() {
			if (self.model.id<0) {
				$( ".personal_injury .edit" ).trigger( "click" );
			}
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.personal_injury_info);
			//console.log(self.model.personal_injury_info);
			var model_personal_injury_info = self.model.get("personal_injury_info");
			
			//alert(model_personal_injury_info);
			var personal_injury_info = JSON.parse(model_personal_injury_info);
			_.each( personal_injury_info, function(the_info) {
				$("#" + the_info.name).val(the_info.value);
				the_info.name = the_info.name.replace("Input", "Span");
				$("#" + the_info.name).html(the_info.value);
			});
			
			var model_personal_injury_details = self.model.get("personal_injury_details");
			if (model_personal_injury_details.length > 0) {
				var personal_injury_details = JSON.parse(model_personal_injury_details);
				_.each( personal_injury_details, function(the_details) {
					var personal_injury_form = the_details.form;
					var arrForm = personal_injury_form.split("_");
					
					//we got the class name, now get values for each input
					_.each( the_details.data, function(the_detail) {
						//console.log(the_detail);
						$("#" + the_detail.name).val(the_detail.value);
						the_detail.name = the_detail.name.replace("Input", "Span");
						$("#" + the_detail.name).html(the_detail.value);
					});
				});
			}

		}
		setTimeout(function() {
			$("#personal_injury_dateInput").datetimepicker();
			initializeGoogleAutocomplete('personal_injury');
		}, 1000);
		
	},
	splitDate: function() {
		var self = this;
		var arrDate = [];
		var date_full = $("#personal_injury_dateInput").val();
		arrDate = date_full.split(" ");
		var day_part = arrDate[0];
		var time_part = arrDate[1];
		day_part = new Date(day_part)
		day_part = day_part.getDay();
		if (day_part == "0") {
			day_part = "Sunday";
		}
		if (day_part == "1") {
			day_part = "Monday";
		}
		if (day_part == "2") {
			day_part = "Tuesday";
		}
		if (day_part == "3") {
			day_part = "Wednesday";
		}
		if (day_part == "4") {
			day_part = "Thursday";
		}
		if (day_part == "5") {
			day_part = "Friday";
		}
		if (day_part == "6") {
			day_part = "Saturday";
		}
		$("#personal_injury_dayInput").val(day_part);
		$("#personal_injury_timeInput").val(time_part);
		
		$("#personal_injury_daySpan").html(day_part);
		$("#personal_injury_timeSpan").html(time_part);
	},
	editPersonalInjuriesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".personal_injury_" + field_name;
		}
		editField(element, master_class);
	},
	editdialogField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".personal_injury_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddPersonalInjury:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.addPersonalInjury(event);
		}, 200);
	},
	addPersonalInjury:function (event) {
		event.preventDefault();
		var self = this;
		self.saveEverything(event);
		this.model.set("editing", false);
		return;
		/*
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		//turn off editing to toggle
		this.model.set("editing", false);
		addForm(event, "personal_injury");
		//turn off editing altogether
		this.model.set("editing", false);
		return;
		*/
    },
	
	savePersonalInjuriesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		console.log("save_function_next");
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		console.log("save_function_after");
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		console.log("save_function_model_start");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		addForm(event);
	},
	schedulePersonalInjuriesEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.togglePersonalInjuriesEdit(event);
		}, 200);
	},
	togglePersonalInjuriesEdit: function (event) {
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
		toggleFormEdit("personal_injury");
	},
	schedulePersonalInjuriesReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.resetPersonalInjuriesForm(event);
		}, 200);
	},
	
	resetPersonalInjuriesForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.togglePersonalInjuriesEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/personalinjury/add";
		
		var inputArr = $("#personal_injury_panel .input_class").serializeArray();
		
		var personal_injury_date = $("#personal_injury_dateInput").val();
		formValues = "case_id=" + current_case_id + "&personal_injury_date=" + personal_injury_date;
		var personal_injury_description = $("#personal_injury_accident_descriptionInput").val();
		var personal_injury_other_details = $("#personal_injury_other_detailsInput").val();
		var personal_injury_id = $("#table_id").val();
		formValues += "&personal_injury_description=" + personal_injury_description + "&personal_injury_other_details=" + personal_injury_other_details + "&table_id=" + personal_injury_id;
		formValues += "&personal_injury_info=" + JSON.stringify(inputArr);
		formValues += "&personal_injury_details=";
		
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
					$("#panel_title").css("color", "green");
					toggleFormEdit("personal_injury");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});

window.personal_injury_dogbite_view = Backbone.View.extend({
	events:{
		
		"dblclick .personal_injury .gridster_border": 			"editPersonalInjuriesField",
		"click .personal_injury .save":							"addPersonalInjury",
		"click .personal_injury .save_field":					"savePersonalInjuriesField",
		"click .personal_injury .edit": 						"schedulePersonalInjuriesEdit",
		"click .personal_injury .reset": 						"schedulePersonalInjuriesReset",
		"blur .personal_injury #personal_injury_dateInput": 	"splitDate",
		"click #personal_injury_dogbite_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "personal_injury_dogbite_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		gridsterById("gridster_personal_injury");
		gridsterById("gridster_accident");
		
		//gridsterById("gridster_car_passenger");
		this.model.set("editing", false);
		
		setTimeout(function() {
			if (self.model.id<0) {
				$( ".personal_injury .edit" ).trigger( "click" );
			}
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.personal_injury_info);
			//console.log(self.model.personal_injury_info);
			var model_personal_injury_info = self.model.get("personal_injury_info");
			
			//alert(model_personal_injury_info);
			var personal_injury_info = JSON.parse(model_personal_injury_info);
			_.each( personal_injury_info, function(the_info) {
				$("#" + the_info.name).val(the_info.value);
				the_info.name = the_info.name.replace("Input", "Span");
				$("#" + the_info.name).html(the_info.value);
			});
			
			var model_personal_injury_details = self.model.get("personal_injury_details");
			if (model_personal_injury_details.length > 0) {
				var personal_injury_details = JSON.parse(model_personal_injury_details);
				_.each( personal_injury_details, function(the_details) {
					var personal_injury_form = the_details.form;
					var arrForm = personal_injury_form.split("_");
					
					//we got the class name, now get values for each input
					_.each( the_details.data, function(the_detail) {
						//console.log(the_detail);
						$("#" + the_detail.name).val(the_detail.value);
						the_detail.name = the_detail.name.replace("Input", "Span");
						$("#" + the_detail.name).html(the_detail.value);
					});
				});
			}

		}
		setTimeout(function() {
			$("#personal_injury_dateInput").datetimepicker();
			initializeGoogleAutocomplete('personal_injury');
		}, 1000);
		
	},
	splitDate: function() {
		var self = this;
		var arrDate = [];
		var date_full = $("#personal_injury_dateInput").val();
		arrDate = date_full.split(" ");
		var day_part = arrDate[0];
		var time_part = arrDate[1];
		day_part = new Date(day_part)
		day_part = day_part.getDay();
		if (day_part == "0") {
			day_part = "Sunday";
		}
		if (day_part == "1") {
			day_part = "Monday";
		}
		if (day_part == "2") {
			day_part = "Tuesday";
		}
		if (day_part == "3") {
			day_part = "Wednesday";
		}
		if (day_part == "4") {
			day_part = "Thursday";
		}
		if (day_part == "5") {
			day_part = "Friday";
		}
		if (day_part == "6") {
			day_part = "Saturday";
		}
		$("#personal_injury_dayInput").val(day_part);
		$("#personal_injury_timeInput").val(time_part);
		
		$("#personal_injury_daySpan").html(day_part);
		$("#personal_injury_timeSpan").html(time_part);
	},
	editPersonalInjuriesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".personal_injury_" + field_name;
		}
		editField(element, master_class);
	},
	editdialogField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".personal_injury_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddPersonalInjury:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.addPersonalInjury(event);
		}, 200);
	},
	addPersonalInjury:function (event) {
		event.preventDefault();
		var self = this;
		self.saveEverything(event);
		this.model.set("editing", false);
		return;
		/*
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		//turn off editing to toggle
		this.model.set("editing", false);
		addForm(event, "personal_injury");
		//turn off editing altogether
		this.model.set("editing", false);
		return;
		*/
    },
	
	savePersonalInjuriesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		console.log("save_function_next");
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		console.log("save_function_after");
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		console.log("save_function_model_start");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		addForm(event);
	},
	schedulePersonalInjuriesEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.togglePersonalInjuriesEdit(event);
		}, 200);
	},
	togglePersonalInjuriesEdit: function (event) {
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
		toggleFormEdit("personal_injury");
	},
	schedulePersonalInjuriesReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.resetPersonalInjuriesForm(event);
		}, 200);
	},
	
	resetPersonalInjuriesForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.togglePersonalInjuriesEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/personalinjury/add";
		
		var inputArr = $("#personal_injury_panel .input_class").serializeArray();
		
		var inputDogInfoArr = $("#personal_injury_dogbite_form .input_class").serializeArray();
		
		var personal_injury_date = $("#personal_injury_dateInput").val();
		formValues = "case_id=" + current_case_id + "&personal_injury_date=" + personal_injury_date;
		var personal_injury_description = $("#personal_injury_accident_descriptionInput").val();
		var personal_injury_other_details = $("#personal_injury_other_detailsInput").val();
		var personal_injury_id = $("#table_id").val();
		formValues += "&personal_injury_description=" + personal_injury_description + "&personal_injury_other_details=" + personal_injury_other_details + "&table_id=" + personal_injury_id;
		formValues += "&personal_injury_info=" + JSON.stringify(inputArr);
		formValues += "&personal_injury_details=" + JSON.stringify(inputDogInfoArr);
		
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
					$("#panel_title").css("color", "green");
					toggleFormEdit("personal_injury");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});

window.personal_injury_slipandfall_view = Backbone.View.extend({
	events:{
		
		"dblclick .personal_injury .gridster_border": 			"editPersonalInjuriesField",
		"click .personal_injury .save":							"addPersonalInjury",
		"click .personal_injury .save_field":					"savePersonalInjuriesField",
		"click .personal_injury .edit": 						"schedulePersonalInjuriesEdit",
		"click .personal_injury .reset": 						"schedulePersonalInjuriesReset",
		"blur .personal_injury #personal_injury_dateInput": 	"splitDate",
		"click #personal_injury_slipandfall_done":				"doTimeouts"
    },
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "personal_injury_slipandfall_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		gridsterById("gridster_personal_injury");
		gridsterById("gridster_accident");
		
		//gridsterById("gridster_car_passenger");
		this.model.set("editing", false);
		
		setTimeout(function() {
			if (self.model.id<0) {
				$( ".personal_injury .edit" ).trigger( "click" );
			}
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.personal_injury_info);
			//console.log(self.model.personal_injury_info);
			var model_personal_injury_info = self.model.get("personal_injury_info");
			
			//alert(model_personal_injury_info);
			var personal_injury_info = JSON.parse(model_personal_injury_info);
			_.each( personal_injury_info, function(the_info) {
				$("#" + the_info.name).val(the_info.value);
				the_info.name = the_info.name.replace("Input", "Span");
				$("#" + the_info.name).html(the_info.value);
			});
			
			var model_personal_injury_details = self.model.get("personal_injury_details");
			if (model_personal_injury_details.length > 0) {
				var personal_injury_details = JSON.parse(model_personal_injury_details);
				_.each( personal_injury_details, function(the_details) {
					var personal_injury_form = the_details.form;
					var arrForm = personal_injury_form.split("_");
					
					//we got the class name, now get values for each input
					_.each( the_details.data, function(the_detail) {
						//console.log(the_detail);
						$("#" + the_detail.name).val(the_detail.value);
						the_detail.name = the_detail.name.replace("Input", "Span");
						$("#" + the_detail.name).html(the_detail.value);
					});
				});
			}

		}
		setTimeout(function() {
			$("#personal_injury_dateInput").datetimepicker();
			initializeGoogleAutocomplete('personal_injury');
		}, 1000);
		
	},
	splitDate: function() {
		var self = this;
		var arrDate = [];
		var date_full = $("#personal_injury_dateInput").val();
		arrDate = date_full.split(" ");
		var day_part = arrDate[0];
		var time_part = arrDate[1];
		day_part = new Date(day_part)
		day_part = day_part.getDay();
		if (day_part == "0") {
			day_part = "Sunday";
		}
		if (day_part == "1") {
			day_part = "Monday";
		}
		if (day_part == "2") {
			day_part = "Tuesday";
		}
		if (day_part == "3") {
			day_part = "Wednesday";
		}
		if (day_part == "4") {
			day_part = "Thursday";
		}
		if (day_part == "5") {
			day_part = "Friday";
		}
		if (day_part == "6") {
			day_part = "Saturday";
		}
		$("#personal_injury_dayInput").val(day_part);
		$("#personal_injury_timeInput").val(time_part);
		
		$("#personal_injury_daySpan").html(day_part);
		$("#personal_injury_timeSpan").html(time_part);
	},
	editPersonalInjuriesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".personal_injury_" + field_name;
		}
		editField(element, master_class);
	},
	editdialogField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".personal_injury_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddPersonalInjury:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.addPersonalInjury(event);
		}, 200);
	},
	addPersonalInjury:function (event) {
		event.preventDefault();
		var self = this;
		self.saveEverything(event);
		this.model.set("editing", false);
		return;
		/*
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		//turn off editing to toggle
		this.model.set("editing", false);
		addForm(event, "personal_injury");
		//turn off editing altogether
		this.model.set("editing", false);
		return;
		*/
    },
	
	savePersonalInjuriesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		console.log("save_function_next");
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		console.log("save_function_after");
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		console.log("save_function_model_start");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		addForm(event);
	},
	schedulePersonalInjuriesEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.togglePersonalInjuriesEdit(event);
		}, 200);
	},
	togglePersonalInjuriesEdit: function (event) {
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
		toggleFormEdit("personal_injury");
	},
	schedulePersonalInjuriesReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(personal_injury_timeout_id);
		user_timeout_id = setTimeout(function() {
			self.resetPersonalInjuriesForm(event);
		}, 200);
	},
	
	resetPersonalInjuriesForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.togglePersonalInjuriesEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/personalinjury/add";
		
		var inputArr = $("#personal_injury_panel .input_class").serializeArray();
		
		var inputSlipAndFallArr = $("#personal_injury_slipandfall_form .input_class").serializeArray();
		
		var personal_injury_date = $("#personal_injury_dateInput").val();
		formValues = "case_id=" + current_case_id + "&personal_injury_date=" + personal_injury_date;
		var personal_injury_description = $("#personal_injury_accident_descriptionInput").val();
		var personal_injury_other_details = $("#personal_injury_other_detailsInput").val();
		var personal_injury_id = $("#table_id").val();
		formValues += "&personal_injury_description=" + personal_injury_description + "&personal_injury_other_details=" + personal_injury_other_details + "&table_id=" + personal_injury_id;
		formValues += "&personal_injury_info=" + JSON.stringify(inputArr);
		formValues += "&personal_injury_details=" + JSON.stringify(inputSlipAndFallArr);
		
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
					$("#panel_title").css("color", "green");
					toggleFormEdit("personal_injury");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});