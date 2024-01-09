var personal_injury_timeout_id;
window.personal_injury_view = Backbone.View.extend({
	events:{
		
		//"dblclick .personal_injury .gridster_border": 			"editPersonalInjuriesField",
		"click .personal_injury .save":							"addPersonalInjury",
		"click .personal_injury .save_field":					"savePersonalInjuriesField",
		"click .personal_injury .edit": 						"schedulePersonalInjuriesEdit",
		"click .personal_injury .reset": 						"schedulePersonalInjuriesReset",
		"blur .personal_injury #personal_injury_dateInput": 	"splitDate",
		"click #picture_holder":								"expandImage",
		"click #picture_holder_2":								"expandImage2",
		"click #personal_injury_done":							"doTimeouts",
		"click .previous_car .roll_over": 						"changeImage",
		"click #plaintiff_car_information .roll_over": 			"changeImagePlaintiff",
		"click input[type=checkbox]":							"checkboxAdjuster",
		"click #defendant_car_information .roll_over": 			"changeImageDefendant",
		"change #statute_intervalInput":						"setStatuteDate",
		"click #show_accident_map":								"showAccidentMap",
		"click #vehicle_owner":									"editVehicleOwner",
		"click #defendant_vehicle_owner":						"editDefendantOwner",
		"click #vehicle_rental":								"clickVehicleRental",
		"click #defendant_vehicle_rental":						"clickDefendantVehicleRental",
		"click #vehicle_repair":								"clickVehicleRepair",
		"click #defendant_vehicle_repair":						"clickDefendantVehicleRepair",
		"click #witnesses_button":								"showWitnesses"
		//"click #switch_party_info_defendant": 					"showDefendantInfo",
		//"click #switch_party_info_plaintiff": 					"showPlaintiffInfo"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "personal_injury_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var self = this;
		
		//statute
		this.model.set("statute_years", "");
		if (this.model.get("statute_limitation")=="0000-00-00" || this.model.get("statute_limitation")=="1969-12-31") {
			if ((this.model.get("personal_injury_date")=="0000-00-00 00:00:00") || (this.model.get("personal_injury_date")=="" && this.model.get("end_date")=="")) {
				this.model.set("statute_limitation", "");
				this.model.set("statute_years", "");
			} else {
				//give it a 2 year default
				//In California, the statute of limitations for personal injury cases is 2 years from the date of the injury
				this.model.set("statute_limitation", moment(this.model.get("personal_injury_date")).add(2, "years").format("MM/DD/YYYY"));
				
				this.model.set("statute_years", "2");
			}
		} else {
			this.model.set("statute_limitation", moment(this.model.get("statute_limitation")).format("MM/DD/YYYY"));
		}
		
		//rental
		this.model.set("rental_plaintiff", "");
		this.model.set("rental_defendant", "");
			
		var rental_info = this.model.toJSON().rental_info;
		if (rental_info!="") {
			rental_info = JSON.parse(rental_info);
			if (typeof rental_info.plaintiff == "undefined") {
				rental_info.plaintiff = "";
			} else {
				if (rental_info.plaintiff.rentedInput=="N" && rental_info.plaintiff.completedInput=="N" && rental_info.plaintiff.agencyInput=="") {
					//really empty
					rental_info.plaintiff = "";
				}
			}
			if (typeof rental_info.defendant == "undefined") {
				rental_info.defendant = "";
			} else {
				if (rental_info.defendant.rentedInput=="N" && rental_info.defendant.completedInput=="N" && rental_info.defendant.agencyInput=="") {
					//really empty
					rental_info.defendant = "";
				}
			}
			this.model.set("rental_plaintiff", rental_info.plaintiff);
			this.model.set("rental_defendant", rental_info.defendant);
		}
		//repair
		this.model.set("repair_plaintiff", "");
		this.model.set("repair_defendant", "");
		
		var repair_info = this.model.toJSON().repair_info;
		if (repair_info!="") {
			repair_info = JSON.parse(repair_info);
			if (typeof repair_info.plaintiff == "undefined") {
				repair_info.plaintiff = "";
			}
			this.model.set("repair_plaintiff", repair_info.plaintiff);
			if (typeof repair_info.defendant == "undefined") {
				repair_info.defendant = "";
			}
			this.model.set("repair_defendant", repair_info.defendant);
		}
		
		//witness count
		var witness_count = this.model.toJSON().witness_count;
		if (witness_count==null) {
			this.model.set("witness_count", 0);
		}
		
		//id
		var id = this.model.id;
		if (id==null) {
			this.model.set("id", -1);
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
	showDefendantInfo: function(event) {
		$("#plaintiff_car_information").fadeOut(function() {
			$("#defendant_car_information").show();
		});
	},
	checkboxAdjuster: function (event) {
		var element = event.currentTarget;
		//alert(element_clean);
		var element_id = element.id;
		var checkbox = element;
		var checkbox_value = $("#" + element_id).val();
		if (checkbox_value != "Y") {
			$("#" + element_id).val("Y");
		} else {
			$("#" + element_id).val("N");
		}
		
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
			} 
	},
	changeImagePlaintiff: function(event) {
			if (blnPiReady) {
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
		if (blnPiReady) {
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
	editVehicleOwner: function(event) {
		event.preventDefault();
		var owner_id = this.model.get("owner_id");
		
		window.Router.prototype.vehicleOwner(current_case_id, owner_id, "plaintiff");
		window.history.pushState(null, null, "#owner/" + current_case_id + "/" + owner_id + "/plaintiff");
	},
	editDefendantOwner: function(event) {
		event.preventDefault();
		var owner_id = this.model.get("defendant_owner_id");
		
		window.Router.prototype.vehicleOwner(current_case_id, owner_id, "defendant");
		window.history.pushState(null, null, "#owner/" + current_case_id + "/" + owner_id + "/defendant");
	},
	clickVehicleRentalRepair: function(event) {
		event.preventDefault();
		this.goToRentalRepair("plaintiff");
	},
	clickVehicleRental: function(event) {
		event.preventDefault();
		this.goToRental("plaintiff");
	},
	clickVehicleRepair: function(event) {
		event.preventDefault();
		this.goToRepair("plaintiff");
	},
	clickDefendantVehicleRepair: function(event) {
		event.preventDefault();
		this.goToRepair("defendant");
	},
	clickDefendantVehicleRentalRepair: function(event) {
		event.preventDefault();
		this.goToRentalRepair("defendant");
	},
	goToRentalRepair: function(representing) {
		var mymodel = this.model.clone();
		mymodel.set("representing", representing);
		mymodel.set("holder", "kase_content");
		var rental = new dashboard_rental_view({el: $("#kase_content"), model:mymodel}).render();
	},
	clickDefendantVehicleRental: function(event) {
		event.preventDefault();
		this.goToRental("defendant");
	},
	goToRental: function(representing) {
		var mymodel = this.model.clone();
		mymodel.set("representing", representing);
		mymodel.set("accident_partie", representing);
		
		composeRental(mymodel);
	},
	goToRepair: function(representing) {
		var mymodel = this.model.clone();
		mymodel.set("representing", representing);
		mymodel.set("accident_partie", representing);
		
		composeRepair(mymodel);
	},
	showAccidentMap: function(event) {
		var arrAddress = [];
		var location_1 = $("#personal_injury_locationInput").val();
		if (location_1!="") {
			arrAddress.push(location_1);
		}
		var location_2 = $("#personal_injury2_locationInput").val();
		if (location_2!="") {
			arrAddress.push(location_2);
		}
		
		if (arrAddress.length == 2) {
			//let's get the city out of the first address
			var arrFirst = location_1.split(",");
			key = arrFirst.length - 1;
			arrFirst.splice(key, 1);
			key = arrFirst.length - 1;
			arrFirst.splice(key, 1);
			key = arrFirst.length - 1;
			arrFirst.splice(key, 1);
			
			location_1 = arrFirst.join(",");
			
			arrAddress[0] = location_1;
		}
		
		var href = "https://www.google.com/maps/place/" + arrAddress.join("+&+");
		window.open(href);
	},
	showWitnesses: function(event) {
		event.preventDefault();
		document.location.href = "#partielist/" + current_case_id + "/witnesses";
	},
	setStatuteDate: function(event) {
		if (event.target.value == "-99") {
			$("#statute_limitationInput").val("");
			return;
		}
		var days_val = Number(event.target.value) * 366;
		var current_date =  $("#personal_injury_dateInput").val();
		var years_val = Number(event.target.value);
		
		if (current_date=="" || current_date=="0000-00-00") {
			return;
		}
		var formValues = "years=" + years_val + "&days=" + days_val + "&date=" + current_date;
			
		$.ajax({
		  method: "POST",
		  url: "api/calculator_post.php",
		  dataType:"json",
		  data: formValues,
		  success:function (data) {
			  if(data.error) {  // If there is an error, show the error tasks
				  alert("error");
			  } else {
				  $("#statute_limitationInput").val(moment(data[0].calculated_date).format("MM/DD/YYYY"));
				  $("#statute_limitationSpan").html($("#statute_limitationInput").val());
			  }
		  }
		});
	},
	doTimeouts: function() {
		if (!blnPiReady) {
			var self = this;
			gridsterById("gridster_accident");
			//$('#car_holder').html(new car_passenger_pi_view({model: this.model.toJSON()}).render().el);
			gridsterById("gridster_accident_details");
			//we are not in editing mode initially
			gridsterById("gridster_personal_injury");
			
			
			
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
				
				if (model_personal_injury_info!="") {
					//alert(model_personal_injury_info);
					var personal_injury_info = JSON.parse(model_personal_injury_info);
					_.each( personal_injury_info, function(the_info) {
						$("#" + the_info.name).val(the_info.value);
						the_info.name = the_info.name.replace("Input", "Span");
						$("#" + the_info.name).html(the_info.value);
					});
				}
				var model_personal_injury_details = self.model.get("personal_injury_details");
				if (model_personal_injury_details!="") {
					var personal_injury_details = JSON.parse(model_personal_injury_details);
					_.each( personal_injury_details, function(the_details) {
						var personal_injury_form = the_details.form;
						if (typeof personal_injury_form != "undefined") {
							var arrForm = personal_injury_form.split("_");
						}
						if (typeof the_details.data != "undefined") {
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
						} else {
							if (typeof the_details.name != "undefined") {
								$("#" + the_details.name).val(the_details.value);
								the_details.name = the_details.name.replace("Input", "Span");
								$("#" + the_details.name).html(the_details.value);
							}
						}
					});
				}
			}
			$(".roll_over").css("cursor", "pointer");
			setTimeout(function() {
				//maxDate: moment().format("YYYY-MM-DD"),
				$("#personal_injury_dateInput").datetimepicker({
					maxDate: '+1970/01/02',
					onChangeDateTime:function(dp,$input){
						$("#statute_intervalInput").trigger("change");
					}
				});
				$("#personal_injury_loss_dateInput").datetimepicker({
					timepicker: false,
					format:'m/d/Y',
					onChangeDateTime:function(dp,$input){
						
					}
				});
				initializeGoogleAutocomplete('personal_injury');
			}, 1000);
			/*
			if (customer_id == 1033) {
				$("#billing_time_dropdownInput").editableSelect({
					onSelect: function (element) {
						var billing_time = $("#billing_time_dropdownInput").val();
						$("#billing_time").val(billing_time);
						//alert(billing_time);
					}
				});
			}
			*/	
			//var session_id = /SESS\w*ID=([^;]+)/i.test(document.cookie) ? RegExp.$1 : false;
			//console.log(session_id);
			//var blnNewCar = true;
		} else {
			var self = this;
		
			//$('#car_holder').html(new car_passenger_pi_view({model: this.model.toJSON()}).render().el);
			
			//gridsterById("gridster_car_passenger");
			this.model.set("editing", false);
			
			setTimeout(function() {
				if (self.model.id<0) {
					$( ".personal_injury .edit" ).trigger( "click" );
					$(".injury_buttons").hide();
				}
			}, 600);
			if (self.model.id > 0) {
				//alert(self.model.personal_injury_info);
				//console.log(self.model.personal_injury_info);
				var model_personal_injury_info = self.model.get("personal_injury_info");
				var blnShowAccidentMapLink = false;
				//alert(model_personal_injury_info);
				if (model_personal_injury_info!="") {
					var personal_injury_info = JSON.parse(model_personal_injury_info);
					_.each( personal_injury_info, function(the_info) {
						if (the_info.value=="Invalid date") {
							the_info.value = "";
							if (the_info.name=="statute_limitationInput") {
								setTimeout(function() {
									//get the statute date
									$("#statute_intervalInput").trigger("change");
								}, 500);
							}
						}
						if (the_info.name=="statute_limitationInput") {
							the_info.value = moment(the_info.value).format("MM/DD/YYYY");
						}
						$("#" + the_info.name).val(the_info.value);
						the_info.name = the_info.name.replace("Input", "Span");
						if (the_info.name=="personal_injury_accident_descriptionSpan" || the_info.name=="personal_injury_other_detailsSpan") {
							the_info.value = the_info.value.replaceTout("\r\n", "<br>");							
						}
						$("#" + the_info.name).html(the_info.value);
						
						//map?
						if (the_info.name=="personal_injury_locationSpan" || the_info.name=="personal_injury2_locationSpan") {
							if (the_info.value!="") {
								blnShowAccidentMapLink = true;
							}
						}
					});
				}
				
				if (!blnShowAccidentMapLink) {
					$("#show_accident_map").hide();
				}
				var model_personal_injury_details = self.model.get("personal_injury_details");
				if (model_personal_injury_details!="") {
					var personal_injury_details = JSON.parse(model_personal_injury_details);
					_.each( personal_injury_details, function(the_details) {
						var personal_injury_form = the_details.form;
						if (typeof personal_injury_form != "undefined") {
							var arrForm = personal_injury_form.split("_");
						}
						if (typeof the_details.data != "undefined") {
							//we got the class name, now get values for each input
							_.each( the_details.data, function(the_detail) {
								//console.log(the_detail);
								if ($("#" + the_detail.name).is(':checkbox')) {
									if (the_detail.value == "Y") {
										$("#" + the_detail.name).attr("checked", "checked");
									}
								} else {									
									$("#" + the_detail.name).val(the_detail.value);
								}
								
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
						} else {
							if (typeof the_details.name != "undefined") {
								$("#" + the_details.name).val(the_details.value);
								the_details.name = the_details.name.replace("Input", "Span");
								$("#" + the_details.name).html(the_details.value);
							}
						}
					});
				}
			}
			//$("#defendant_car_information").hide();
			$(".roll_over").css("cursor", "pointer");
			setTimeout(function() {
				$("#personal_injury_dateInput").datetimepicker({
					maxDate: '+1970/01/02',
					onChangeDateTime:function(dp,$input){
						$("#statute_intervalInput").trigger("change");
					}
				});
				$("#personal_injury_loss_dateInput").datetimepicker({
					timepicker: false,
					format:'m/d/Y',
					onChangeDateTime:function(dp,$input){
						
					}
				});
				
				/*
				if (!blnBingSearch) {
					initializeGoogleAutocomplete('personal_injury');
					initializeGoogleAutocomplete('personal_injury2');
				} else {
					$("#personal_injury2_locationInput").on("keyup", lookupPIBingMaps);
					$("#personal_injury_locationInput").on("keyup", lookupPIBingMaps);
				}
				*/
				
				$(".form_label_vert").css("color", "white");
				$(".form_label_vert").css("font-size", "1em");
			}, 1000);
			/*
			if (customer_id == 1033) {
				$("#billing_time_dropdownInput").editableSelect({
					onSelect: function (element) {
						var billing_time = $("#billing_time_dropdownInput").val();
						$("#billing_time").val(billing_time);
						//alert(billing_time);
					}
				});
			}
			*/
			//if (customer_id == 1033) { 
		
			var case_id = this.model.get("case_id");
			var kase = kases.findWhere({case_id: case_id});
			
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
			
		}
		var image_model = self.model.clone();
		image_model.set("holder", "image_holder");
		image_model.set("case_uuid", self.model.get("case_uuid"));
		image_model.set("case_id", current_case_id);
		$('#image_holder').html(new personal_injury_image({model: image_model}).render().el);
		$("#queue").css("height", "50px");
		/*
		//let's get any image
		pi_documents = new DocumentCollectionPi([], { case_id: self.model.get("case_id"), attribute: "personal_injury_picture" });
		pi_documents.fetch({
			success: function(data) {
				if (data.toJSON().length > 0) {
					//var customer_id = data.toJSON()[0].customer_id;
					if (typeof data.toJSON()[0].document_filename != "undefined") {
						var document_filename = data.toJSON()[0].document_filename;
						var document_type = data.toJSON()[0].type;
						document_type_norm = document_type.replace("_", " ");
						document_type_norm = document_type_norm.charAt(0).toUpperCase() + document_type_norm.slice(1);
						if (document_filename!="") {
							$('#picture_holder').html("<img src='uploads/" + customer_id + "/" + current_case_id + "/" + document_filename + "' class='personal_injury_img " + document_type + "' id='personal_injury_img'><br><span style='font-size:0.8em; color:white'>" + document_type_norm + "&nbsp;<a id='deleteimage_" + data.toJSON()[0].document_id + "' class='delete_image' style='cursor:pointer'><i class='glyphicon glyphicon-trash' style='color:#FA1616;'></i></span></a>");
							/*
							if (customer_id == "1033") {
								var url = 'api/image_rotate.php';
								console.log('rotating ... ');
								var formValues = "fullpath=uploads/" + customer_id + '/' + current_case_id + '/' + document_filename + "&degrees=90";
								//console.log(formValues);
								//return;
								$.ajax({
									url:url,
									type:'POST',
									dataType:"json",
									data: formValues,
									success:function (data) {
										if(data.error) {  // If there is an error, show the error messages
											console.log(data.error.text);
											self.saveFailed(data.error.text);
										} else { 
											  $('#picture_holder').html("<img src='" + data.full_path + "' class='applicant_img'>");
										}
									}
								});	
							}
							 
						}
					}
					if (typeof data.toJSON()[1].document_filename != "undefined") {
						var document_filename = data.toJSON()[1].document_filename;
						var document_type = data.toJSON()[1].type;
						document_type_norm = document_type.replace("_", " ");
						document_type_norm = document_type_norm.charAt(0).toUpperCase() + document_type_norm.slice(1);
						if (document_filename!="") {
							$('#picture_holder_2').html("<img src='uploads/" + customer_id + "/" + current_case_id + "/" + document_filename + "' class='personal_injury_img_2 " + document_type + "' id='personal_injury_img_2'><br><span style='font-size:0.8em; color:white'>" + document_type_norm + "&nbsp;<a id='deleteimage_" + data.toJSON()[1].document_id + "' class='delete_image' style='cursor:pointer'><i class='glyphicon glyphicon-trash' style='color:#FA1616;'></i></span></a>");
						}
					}
					
					if (typeof data.toJSON()[2].document_filename != "undefined") {
						var document_filename = data.toJSON()[2].document_filename;
						var document_type = data.toJSON()[2].type;
						document_type_norm = document_type.replace("_", " ");
						document_type_norm = document_type_norm.charAt(0).toUpperCase() + document_type_norm.slice(1);
						if (document_filename!="") {
							$('#picture_holder_3').html("<img src='uploads/" + customer_id + "/" + current_case_id + "/" + document_filename + "' class='personal_injury_img_2 " + document_type + "' id='personal_injury_img_2'><br><span style='font-size:0.8em; color:white'>" + document_type_norm + "&nbsp;<a id='deleteimage_" + data.toJSON()[2].document_id + "' class='delete_image' style='cursor:pointer'><i class='glyphicon glyphicon-trash' style='color:#FA1616;'></i></span></a>");
						}
					}
					
				}
			}
		}); */
		gridsterById("gridster_acc_info_details");
		gridsterById("gridster_acc_details");
		//we are not in editing mode initially
		gridsterById("gridster_personal_injury");
		setTimeout(function() {
			gridsterById("gridster_accident");
			gridsterById("gridster_acc_info_details");
			gridsterById("gridster_acc_details");
		}, 500);
		
	},
	expandImage: function(event) {
		/*
		event.preventDefault();
		var element = event.currentTarget;
		*/
		var the_element = $("#personal_injury_img");
		if (the_element.hasClass("personal_injury_large_img")) {
			the_element.removeClass("personal_injury_large_img");
			the_element.addClass("personal_injury_img");
			$('#picture_holder').prop("title", "Click to expand image");
		} else {
			the_element.removeClass("personal_injury_img");
			the_element.addClass("personal_injury_large_img");
			$('#picture_holder').prop("title", "Click to shrink image");
		}
	},
	expandImage2: function(event) {
		var the_element = $("#personal_injury_img_2");
		if (the_element.hasClass("personal_injury_large_img_2")) {
			the_element.removeClass("personal_injury_large_img_2");
			the_element.addClass("personal_injury_img_2");
			$('#picture_holder_2').prop("title", "Click to expand image");
		} else {
			the_element.removeClass("personal_injury_img_2");
			the_element.addClass("personal_injury_large_img_2");
			$('#picture_holder_2').prop("title", "Click to shrink image");
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
		
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		
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
		//alert(current_case_id);
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
		var inputAccInfoArr = $("#personal_injury_info_form .input_class").serializeArray();
		
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
			var arrForms = [{"form":"carpassengers", "data":inputPlaintiffCarPassengerArr}, {"form":"defendant_carpassengers", "data":inputDefendantCarPassengerArr}, {"form":"vehicle_form", "data":inputPlaintiffVehicleArr}, {"form":"defendant_vehicle_form", "data":inputDefendantVehicleArr}, {"form":"personal_injury_other_form", "data":inputOtherArr}, {"form":"personal_injury_info_form", "data":inputAccInfoArr}]
		}
		
		var personal_injury_date = $("#personal_injury_dateInput").val();
		var statute_limitation = $("#statute_limitationInput").val();
		var statute_interval = $("#statute_intervalInput").val();
		var personal_injury_date = $("#personal_injury_dateInput").val();
		var personal_injury_description = $("#personal_injury_accident_descriptionInput").val();
		var personal_injury_other_details = $("#personal_injury_other_detailsInput").val();
		var personal_injury_id = $("#table_id").val();
		var loss_date = "";
		if ($("#personal_injury_loss_dateInput").length > 0) {
			loss_date = $("#personal_injury_loss_dateInput").val();
		}
		
		var formValues = "case_id=" + current_case_id + "&loss_date=" + loss_date + "&personal_injury_date=" + personal_injury_date;
		formValues += "&personal_injury_description=" + encodeURIComponent(personal_injury_description) + "&personal_injury_other_details=" + encodeURIComponent(personal_injury_other_details) + "&statute_limitation=" + statute_limitation + "&statute_interval=" + statute_interval + "&table_id=" + personal_injury_id;
		formValues += "&personal_injury_info=" + encodeURIComponent(JSON.stringify(inputArr));
		formValues += "&personal_injury_details=" + encodeURIComponent(JSON.stringify(arrForms));
		
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
					
					$("#panel_title").css("color", "lime");
					displaySavedInfo(formValues, "personal_injury");
					redrawJsonScreen(formValues);
					toggleFormEdit("personal_injury");
					//show the rental/repair/witness buttons
					$(".injury_buttons").fadeIn();
					setTimeout(function(){ 
						$("#panel_title").css("color", "white");				
					}, 2500);
				}
			}
		});
    }
});

window.personal_injury_general_view = Backbone.View.extend({
	events:{
		
		//"dblclick .personal_injury .gridster_border": 			"editPersonalInjuriesField",
		"click .personal_injury .save":							"addPersonalInjury",
		"click .personal_injury .save_field":					"savePersonalInjuriesField",
		"click .personal_injury .edit": 						"schedulePersonalInjuriesEdit",
		"click .personal_injury .reset": 						"schedulePersonalInjuriesReset",
		"blur .personal_injury #personal_injury_dateInput": 	"splitDate",
		"change #statute_intervalInput":						"setStatuteDate",
		"blur .personal_injury .input_class":		 			"autoSave",
		"click #personal_injury_general_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		
		//statute
		if (this.model.get("statute_limitation")=="0000-00-00") {
			if (this.model.get("personal_injury_date")=="0000-00-00" || this.model.get("personal_injury_date")=="") {
				this.model.set("statute_limitation", "");
				this.model.set("statute_years", "");
			} else {
				//give it a 2 year default per thomas 12/5/2017
				if (this.model.get("personal_injury_date")!="0000-00-00 00:00:00") {
					this.model.set("statute_limitation", moment(this.model.get("personal_injury_date")).add(2, "years").format("MM/DD/YYYY"));
					this.model.set("statute_years", "2");
				} else {
					this.model.set("statute_limitation", "");
					this.model.set("statute_years", "");
				}
			}
		} else {
			this.model.set("statute_limitation", moment(this.model.get("statute_limitation")).format("MM/DD/YYYY"));
		}
		
		if (typeof this.model.get("intake_screen") == "undefined") {
			this.model.set("intake_screen", false);		
		}
		
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
	autoSave:function(event) {
		//!self.model.get("intake_request")
		if (!blnAutoSave) {
			return;
		}
		if (!this.model.get("intake_screen")) {
			return;
		}
		
		var element = event.currentTarget;
		
		$("#phone_intake_feedback_div").html("Autosaving...");
		
		this.saveEverything(event);
		
		return;
				
		var fieldname = element.id.replace("Input", "");
		var value = element.value;
		
		var partie = "personal_injury";
		var case_id = $("#kase_form #id").val();
		var id = $("#" + partie + "_form #table_id").val();
		var url = "api/personalinjury/field/update";
		
		var formValues = "table_name=personal_injury&id=" + id + "&case_id=" + case_id + "&fieldname=" + fieldname + "&value=" + encodeURIComponent(value);
		var border = $("#" + partie + "_form #" + element.id).css("border");
		
		if (id == "" || id=="-1") {
			url = "api/personalinjury/add";
			//tack on the full name			
			var personal_injury_date = $("#personal_injury_dateInput").val();
			var formValues = "table_name=personal_injury&case_id=" + case_id + "&" + fieldname + "=" + encodeURIComponent(value);
			if (formValues.indexOf("personal_injury_date") < 0) {
				formValues += "&personal_injury_date=" + encodeURIComponent(personal_injury_date);
			}
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
					if (url == "api/personalinjury/add") {
						$("#" + partie + "_form #table_id").val(data.id);
					}
					$("#" + partie + "_form #" + element.id).css("border", "2px solid lime");
					
					//special case for statute
					if (fieldname=="personal_injury_dateInput") {
						setTimeout(function() {
							$("#statute_limitationInput").trigger("blur");
						}, 5000);
					}
					$("#phone_intake_feedback_div").html("Autosaved&nbsp;&#10003;");
					setTimeout(function() {
						$("#" + partie + "_form #" + element.id).css("border", border);
						
						$("#phone_intake_feedback_div").html("");
					}, 2500);
				}
			}
		});
	},
	setStatuteDate: function(event) {
		if (event.target.value == "-99") {
			$("#statute_limitationInput").val("");
			return;
		}
		var self = this;
		
		var days_val = Number(event.target.value) * 366;
		var current_date =  $("#personal_injury_dateInput").val();
		var years_val = Number(event.target.value);
		
		if (current_date=="" || current_date=="0000-00-00") {
			return;
		}
		var formValues = "years=" + years_val + "&days=" + days_val + "&date=" + current_date;
			
		$.ajax({
		  method: "POST",
		  url: "api/calculator_post.php",
		  dataType:"json",
		  data: formValues,
		  success:function (data) {
			  if(data.error) {  // If there is an error, show the error tasks
				  alert("error");
			  } else {
				  $("#statute_limitationInput").val(moment(data[0].calculated_date).format("MM/DD/YYYY"));
				  $("#statute_limitationSpan").html($("#statute_limitationInput").val());
				  
				  if (self.model.get("intake_screen")) {
					  //autosave
					  $("#statute_limitationInput").trigger("blur");
				  }
			  }
		  }
		});
	},
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		gridsterById("gridster_personal_injury");
		gridsterById("gridster_accident");
		gridsterById("gridster_accident_details");
		
		//gridsterById("gridster_car_passenger");
		this.model.set("editing", false);
		
		if (self.model.get("intake_screen")) {
			document.getElementById("personal_injury_panel").parentElement.style.background = "none";
			$("#personal_injury_form #panel_title").css("font-weight", "normal");
			$("#personal_injury_form #panel_title").css("font-size", "1em");
			$(".button_row.personal_injury").hide();
			
			$(".personal_injury .gridster_border").css("background", "none");
			$(".personal_injury .gridster_border").css("border", "none");
			$(".personal_injury .gridster_border").css("-webkit-box-shadow", "");
			$(".personal_injury .gridster_border").css("box-shadow", "");
			$(".personal_injury .form_label_vert").css("color", "white");
			$("#personal_injury_form .form_label_vert").css("font-size", "1em");
			$("#personal_injury_accident_descriptionInput").css("height", "290px");
			$("#personal_injury_accident_descriptionInput").css("width", "440px");
			$("#personal_injury_accident_descriptionGrid").attr("data-sizey", "7");
			
			$("#personal_injury_panel").css("margin-left", "-25px");
			
			$("#personal_injury_form  #statute_limitationGrid .form_label_vert").html("Statute");
		}
		
		setTimeout(function() {
			if (self.model.id<0) {
				$( ".personal_injury .edit" ).trigger( "click" );
			}
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.personal_injury_info);
			//console.log(self.model.personal_injury_info);
			var model_personal_injury_info = self.model.get("personal_injury_info");
			
			if (model_personal_injury_info!="") {
				//alert(model_personal_injury_info);
				var personal_injury_info = JSON.parse(model_personal_injury_info);
				_.each( personal_injury_info, function(the_info) {
					console.log(the_info);
					$("#" + the_info.name).val(the_info.value);
					the_info.name = the_info.name.replace("Input", "Span");
					$("#" + the_info.name).html(the_info.value);
				});
			}
			
			var model_personal_injury_details = self.model.get("personal_injury_details");
			if (model_personal_injury_details.length > 0) {
				var personal_injury_details = JSON.parse(model_personal_injury_details);
				_.each( personal_injury_details, function(the_details) {
					var personal_injury_form = the_details.form;
					if (typeof personal_injury_form != "undefined") {
						var arrForm = personal_injury_form.split("_");
					}
					if (typeof the_details.data != "undefined") {
						//we got the class name, now get values for each input
						_.each( the_details.data, function(the_detail) {
							//console.log(the_detail);
							$("#" + the_detail.name).val(the_detail.value);
							the_detail.name = the_detail.name.replace("Input", "Span");
							$("#" + the_detail.name).html(the_detail.value);
						});
					} else {
						if (typeof the_details.name != "undefined") {
							$("#" + the_details.name).val(the_details.value);
							the_details.name = the_details.name.replace("Input", "Span");
							$("#" + the_details.name).html(the_details.value);
						}
					}
				});
			}

		}
		setTimeout(function() {
			$("#personal_injury_dateInput").datetimepicker({
				maxDate: '+1970/01/02',
				onChangeDateTime:function(dp,$input){
					$("#statute_intervalInput").trigger("change");
				}
			});
			$("#personal_injury_loss_dateInput").datetimepicker({
				timepicker: false,
				format:'m/d/Y',
				onChangeDateTime:function(dp,$input){
					
				}
			});
			initializeGoogleAutocomplete('personal_injury');
		}, 1000);
		
		setTimeout(function() {
			self.model.set("hide_upload", true);
			if (!self.model.get("intake_screen")) {
				showKaseAbstract(self.model);
			}
		}, 750);
		
		if (self.model.get("intake_screen")) {
			setTimeout(function() {
				$("#kase_abstract_holder").hide();
			}, 1578);
		}
		$(".form_label_vert").css("color", "white");
		$(".form_label_vert").css("font-size", "1em");
		
		$("#sub_category_holder_personal_injury").css("position", "relative");
		$("#personal_injury_buttons").css({"position":"absolute", "top":"0px", "left":"460px"});
		$("#panel_title").html("Accident Info");
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
		
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		
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
		var statute_limitation = $("#statute_limitationInput").val();
		var statute_interval = $("#statute_intervalInput").val();
		var personal_injury_other_details = $("#personal_injury_other_detailsInput").val();
		var personal_injury_id = $(".personal_injury #table_id").val();
		var loss_date = "";
		if ($("#personal_injury_loss_dateInput").length > 0) {
			loss_date = $("#personal_injury_loss_dateInput").val();
		}
		formValues += "&personal_injury_description=" + personal_injury_description + "&personal_injury_other_details=" + personal_injury_other_details + "&statute_limitation=" + statute_limitation + "&statute_interval=" + statute_interval + "&table_id=" + personal_injury_id + "&loss_date=" + loss_date;
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
					if (self.model.get("intake_screen")) {
						if (blnAutoSave) {
							$(".personal_injury #table_id").val(data.id);
							$("#personal_injury_form #panel_title").css("color", "lime");
							$("#phone_intake_feedback_div").html("Autosaved&nbsp;&#10003;");
							setTimeout(function() {
								$("#personal_injury_form #panel_title").css("color", "white");
								$("#phone_intake_feedback_div").html("");
							}, 2500);
							
							return;
						} else {
							//all done
							checkIntakes()
							blnSaveIntakePartie = false;
							document.location.href = "#kase/" + current_case_id;
							return;
						}
					}
					$("#panel_title").css("color", "lime");
					toggleFormEdit("personal_injury");
					
					displaySavedInfo(formValues, "personal_injury");
					redrawJsonScreen(formValues);
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2500);
				}
			}
		});
    }
});

window.personal_injury_dogbite_view = Backbone.View.extend({
	events:{
		
		//"dblclick .personal_injury .gridster_border": 			"editPersonalInjuriesField",
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
			
			if (model_personal_injury_info!="") {
				//alert(model_personal_injury_info);
				var personal_injury_info = JSON.parse(model_personal_injury_info);
				_.each( personal_injury_info, function(the_info) {
					$("#" + the_info.name).val(the_info.value);
					the_info.name = the_info.name.replace("Input", "Span");
					$("#" + the_info.name).html(the_info.value);
				});
			}
			var model_personal_injury_details = self.model.get("personal_injury_details");
			if (model_personal_injury_details.length > 0) {
				var personal_injury_details = JSON.parse(model_personal_injury_details);
				_.each( personal_injury_details, function(the_details) {
					var personal_injury_form = the_details.form;
					if (typeof personal_injury_form != "undefined") {
						var arrForm = personal_injury_form.split("_");
					}
					if (typeof the_details.data != "undefined") {
						//we got the class name, now get values for each input
						_.each( the_details.data, function(the_detail) {
							//console.log(the_detail);
							$("#" + the_detail.name).val(the_detail.value);
							the_detail.name = the_detail.name.replace("Input", "Span");
							$("#" + the_detail.name).html(the_detail.value);
						});
					} else {
						if (typeof the_details.name != "undefined") {
							$("#" + the_details.name).val(the_details.value);
							the_details.name = the_details.name.replace("Input", "Span");
							$("#" + the_details.name).html(the_details.value);
						}
					}
				});
			}

		}
		setTimeout(function() {
			$("#personal_injury_dateInput").datetimepicker();
			initializeGoogleAutocomplete('personal_injury');
		}, 1000);
		
		setTimeout(function() {
			self.model.set("hide_upload", true);
			showKaseAbstract(self.model);
		}, 750);
		
		$(".form_label_vert").css("color", "white");
		$(".form_label_vert").css("font-size", "1em")
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
		
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		
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
					
					$("#panel_title").css("color", "lime");
					displaySavedInfo(formValues, "personal_injury");
					redrawJsonScreen(formValues);
					toggleFormEdit("personal_injury");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2500);
				}
			}
		});
    }
});

window.personal_injury_slipandfall_view = Backbone.View.extend({
	events:{
		
		//"dblclick .personal_injury .gridster_border": 			"editPersonalInjuriesField",
		"click .personal_injury .save":							"addPersonalInjury",
		"click .personal_injury .save_field":					"savePersonalInjuriesField",
		"click .personal_injury .edit": 						"schedulePersonalInjuriesEdit",
		"click .personal_injury .reset": 						"schedulePersonalInjuriesReset",
		"blur .personal_injury #personal_injury_dateInput": 	"splitDate",
		"click #personal_injury_slipandfall_done":				"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "personal_injury_slipandfall_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
        return this;
	},
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		gridsterById("gridster_personal_injury");
		gridsterById("gridster_accident");
		gridsterById("gridster_accident_details");
		
		
		
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
			
			if (model_personal_injury_info!="") {
				//alert(model_personal_injury_info);
				var personal_injury_info = JSON.parse(model_personal_injury_info);
				_.each( personal_injury_info, function(the_info) {
					$("#" + the_info.name).val(the_info.value);
					the_info.name = the_info.name.replace("Input", "Span");
					$("#" + the_info.name).html(the_info.value);
				});
			}
			var model_personal_injury_details = self.model.get("personal_injury_details");
			if (model_personal_injury_details.length > 0) {
				var personal_injury_details = JSON.parse(model_personal_injury_details);
				_.each( personal_injury_details, function(the_details) {
					var personal_injury_form = the_details.form;
					if (typeof personal_injury_form != "undefined") {
						var arrForm = personal_injury_form.split("_");
					}
					if (typeof the_details.data != "undefined") {
						//we got the class name, now get values for each input
						_.each( the_details.data, function(the_detail) {
							//console.log(the_detail);
							$("#" + the_detail.name).val(the_detail.value);
							the_detail.name = the_detail.name.replace("Input", "Span");
							$("#" + the_detail.name).html(the_detail.value);
						});
					} else {
						if (typeof the_details.name != "undefined") {
							$("#" + the_details.name).val(the_details.value);
							the_details.name = the_details.name.replace("Input", "Span");
							$("#" + the_details.name).html(the_details.value);
						}
					}
				});
			}

		}
		setTimeout(function() {
			$("#personal_injury_dateInput").datetimepicker();
			initializeGoogleAutocomplete('personal_injury');
		}, 1000);
		
		setTimeout(function() {
			self.model.set("hide_upload", true);
			showKaseAbstract(self.model);
		}, 750);
		
		$(".form_label_vert").css("color", "white");
		$(".form_label_vert").css("font-size", "1em");
		
		$("#sub_category_holder_personal_injury").css("position", "relative");
		$("#personal_injury_buttons").css({"position":"absolute", "top":"0px", "left":"460px"});
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
		
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		
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
					
					$("#panel_title").css("color", "lime");
					displaySavedInfo(formValues, "personal_injury");
					redrawJsonScreen(formValues);
					toggleFormEdit("personal_injury");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2500);
				}
			}
		});
    }
});
window.personal_injury_disability_view = Backbone.View.extend({
	events:{
		
		//"dblclick .personal_injury .gridster_border": 			"editPersonalInjuriesField",
		"click .personal_injury .save":							"addPersonalInjury",
		"click .personal_injury .save_field":					"savePersonalInjuriesField",
		"click .personal_injury .edit": 						"schedulePersonalInjuriesEdit",
		"click .personal_injury .reset": 						"schedulePersonalInjuriesReset",
		"blur .personal_injury #personal_injury_dateInput": 	"splitDate",
		"click #personal_injury_disability_done":				"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "personal_injury_disability_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
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
			
			if (model_personal_injury_info!="") {
				//alert(model_personal_injury_info);
				var personal_injury_info = JSON.parse(model_personal_injury_info);
				_.each( personal_injury_info, function(the_info) {
					$("#" + the_info.name).val(the_info.value);
					the_info.name = the_info.name.replace("Input", "Span");
					$("#" + the_info.name).html(the_info.value);
				});
			}
			var model_personal_injury_details = self.model.get("personal_injury_details");
			if (model_personal_injury_details.length > 0) {
				var personal_injury_details = JSON.parse(model_personal_injury_details);
				_.each( personal_injury_details, function(the_details) {
					var personal_injury_form = the_details.form;
					if (typeof personal_injury_form != "undefined") {
						var arrForm = personal_injury_form.split("_");
					}
					if (typeof the_details.data != "undefined") {
						//we got the class name, now get values for each input
						_.each( the_details.data, function(the_detail) {
							//console.log(the_detail);
							$("#" + the_detail.name).val(the_detail.value);
							the_detail.name = the_detail.name.replace("Input", "Span");
							$("#" + the_detail.name).html(the_detail.value);
						});
					} else {
						if (typeof the_details.name != "undefined") {
							$("#" + the_details.name).val(the_details.value);
							the_details.name = the_details.name.replace("Input", "Span");
							$("#" + the_details.name).html(the_details.value);
						}
					}
				});
			}

		}
		setTimeout(function() {
			$("#personal_injury_dateInput").datetimepicker();
			initializeGoogleAutocomplete('personal_injury');
		}, 1000);
		
		setTimeout(function() {
			self.model.set("hide_upload", true);
			showKaseAbstract(self.model);
		}, 750);
		
		$(".form_label_vert").css("color", "white");
		$(".form_label_vert").css("font-size", "1em")
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
    },
	
	savePersonalInjuriesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		
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
		
		var inputsDisabilityArr = $("#personal_injury_disability_form .input_class").serializeArray();
		
		var personal_injury_date = $("#personal_injury_dateInput").val();
		formValues = "case_id=" + current_case_id + "&personal_injury_date=" + personal_injury_date;
		var personal_injury_description = $("#personal_injury_accident_descriptionInput").val();
		var personal_injury_other_details = $("#personal_injury_other_detailsInput").val();
		var personal_injury_id = $("#table_id").val();
		formValues += "&personal_injury_description=" + personal_injury_description + "&personal_injury_other_details=" + personal_injury_other_details + "&table_id=" + personal_injury_id;
		formValues += "&personal_injury_info=" + JSON.stringify(inputArr);
		formValues += "&personal_injury_details=" + JSON.stringify(inputsDisabilityArr);
		
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
					
					$("#panel_title").css("color", "lime");
					displaySavedInfo(formValues, "personal_injury");
					redrawJsonScreen(formValues);
					toggleFormEdit("personal_injury");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2500);
				}
			}
		});
    }
});