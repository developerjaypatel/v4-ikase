window.dashboard_rental_view = Backbone.View.extend({
	initialize:function () {
		_.bindAll(this);
    },
	events:{
       "click #dashboard_rental_all_done":							"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "dashboard_rental_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		$(this.el).html(this.template(this.model.toJSON()));
		return this;
    },
	doTimeouts: function() {
		var self = this;
		var mymodel = this.model.clone();
		mymodel.set("accident_partie", this.model.get("representing"));
		mymodel.set("holder", "rental_holder");
		$('#rental_holder').html(new rental_view({model: mymodel}).render().el);
		
		var mymodel2 = this.model.clone();
		mymodel2.set("accident_partie", this.model.get("representing"));
		mymodel2.set("holder", "repair_holder");
		$('#repair_holder').html(new repair_view({model: mymodel2}).render().el);
	}
});
window.rental_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	events:{
        "click .rental_view .delete":						"deleteRentalView",
		"click .rental_view .save":							"saveRental",
		//"click .rental_view .save_field":					"saveRentalViewField",
		"click .rental_view .edit": 						"toggleEditViewRental",
		"click .rental_view .reset": 						"resetRentalForm",
		"keyup .rental_view .input_class": 					"valueRentalViewChanged",
		"dblclick .rental_view .gridster_border": 			"editRentalViewField",
		"keyup .rental_amount":								"calcBalance",
		"click #rental_all_done":							"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "rental_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		$(this.el).html(this.template(this.model.toJSON()));
		return this;
    },
	calcBalance: function() {
		var amount_billed = $("#amount_billedInput").val();
		if (amount_billed=="") {
			amount_billed = 0;
		}
		var rental_payment = $("#rental_paymentInput").val();
		if (rental_payment=="") {
			rental_payment = 0;
		}
		var balance = Number(amount_billed) - Number(rental_payment);
		$("#rental_balanceInput").val(balance);
		$("#rental_balanceSpan").html("$" + formatDollar(balance));
	},
	doTimeouts: function() {
		//gridsterById('gridster_rental');
		this.model.set("editing", false);
		var accident_partie = this.model.get("accident_partie");
		
		$(".rental #panel_title").html(accident_partie.capitalize() + " Rental Car");

		$(".rental_view .edit").trigger("click");
		$(".rental_view .edit").trigger("click");		
		$(".rental_view .delete").hide();
		$(".rental_view .reset").hide();
		
		$(".rental_view .form_label_vert").css("color", "white");
		$(".rental_view .form_label_vert").css("font-size", "1em");
		
		//add data
		var rental_data = this.model.toJSON().rental_info;
		if (rental_data == "") {
			return;
		}
		
		var rental_info = JSON.parse(rental_data)[this.model.get("accident_partie")];
		if (typeof rental_info == "undefined") {
			return;
		}
		Object.keys(rental_info).forEach(function(key) {
		  	var key_name = key;
			if ($("#" + key_name).is(':checkbox')) {
				if (rental_info[key] == "Y") {
					$("#" + key_name).attr("checked", "checked");
				}
			} else {
				$("#" + key_name).val(rental_info[key]);
			}		
			key_name = key_name.replace("Input", "Span");
			$("#" + key_name).html(rental_info[key]);
		})
		
		this.calcBalance();
	},
	editRentalViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".rental_" + field_name;
		}
		editField(element, master_class);
	},
	saveRentalViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".rental_" + field_name;
		}
		element_id = element_id.replace("SaveLink", "");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		if (master_class != "") {
			//hide all the subs
			$(".span_class" + master_class).toggleClass("hidden");
			$(".input_class" + master_class).toggleClass("hidden");
			//$(field_name + "Save").toggleClass("hidden");
			
			$(".span_class" + master_class).addClass("editing");
			$(".input_class" + master_class).addClass("editing");
			$(field_name + "Save").addClass("editing");
		} else {
			//get the parent to get the class
			var theclass = element.parentElement.parentElement.parentElement.parentElement.parentElement.className;
			var arrClass = theclass.split(" ");
			theclass = "." + arrClass[0] + "." + arrClass[1];
			field_name = theclass + " #" + element_id;
			
			//restore the read look
			editField($(field_name + "Grid"));
			
			var element_value = $(field_name + "Input").val();
			$(field_name + "Span").html(escapeHtml(element_value));
		}			
		
		//this should not redraw, it will update if no id
		this.addRentalView(event);
	
	},
	saveRental:function (event) {
		$("#edit_row").hide();
		$("#gifsave").css("margin-top", "-5px");
		$("#gifsave").show();
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		var url = "api/personalinjury/addrental"
		
		formValues = $("#rental_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#gifsave").hide();
					$(".rental_view #panel_title").css("color", "green");
					
					redrawEditScreen(formValues, "rental_view");
					$(".rental_view .edit").trigger("click");
					
					setTimeout(function() {
						$(".rental_view #panel_title").css("color", "white");
					}, 2500);
				}
			}
		});
	},
	addRentalView:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		if ($(".rental #table_id").val()=="") {
			$(".rental #table_id").val("-1")
		}
		addForm(event, "rental");
		return;
    },
	deleteRentalView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "rental");
		return;
    },
	
	toggleEditViewRental: function(event) {
		event.preventDefault();
		
		var accident_partie = this.model.get("accident_partie");
		if ($(".rental #table_id").val()=="") {
			return;
		}
		
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
		$(" ." + accident_partie + " .rental").toggleClass("editing");
		setTimeout(function() {
			$(".rental").toggleClass("editing");
		}, 2500);
		$(" ." + accident_partie + " .rental .span_class").removeClass("editing");
		$(" ." + accident_partie + " .rental .input_class").removeClass("editing");

		$(" ." + accident_partie + " .rental .span_class").toggleClass("hidden");
		$(" ." + accident_partie + " .rental .input_class").toggleClass("hidden");
		$(" ." + accident_partie + " .rental .input_holder").toggleClass("hidden");
		$(" ." + accident_partie + " .button_row.rental").toggleClass("hidden");
		$(" ." + accident_partie + " .edit_row.rental").toggleClass("hidden");
		
	},
	resetRentalForm: function(event) {
		event.preventDefault();
		this.toggleEditViewAccident(event);
		//this.render();
		//$("#address").hide();
	},
	
	valueRentalViewChanged: function(event) {
		event.preventDefault();
		//console.log(arguments[0].currentTarget.id);
		var source = arguments[0].currentTarget.id;
		source = source.replace("Input", "");
		
		var newval = $("#" + source + "Input").val();
		if (newval==""){
			if ($("#" + source + "Input").hasClass("required")) {
			newval = "Please fill me in";	
				$("#" + source + "Span").toggleClass("hidden");
			}
		} else {
			if (!$("#" + source + "Span").hasClass("hidden")) {
				$("#" + source + "Span").addClass("hidden");
			}
		}
		$("#" + source + "Span").html(escapeHtml(newval));
	}
});
window.repair_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	events:{
        "click .repair_view .delete":					"deleteRepairView",
		"click .repair_view .save":						"saveRepair",
		//"click .repair_view .save_field":				"saveRepairViewField",
		"click .repair_view .edit": 					"toggleEditViewRepair",
		"click .repair_view .reset": 					"resetRepairForm",
		"keyup .repair_view .input_class": 				"valueRepairViewChanged",
		"dblclick .repair_view .gridster_border": 		"editRepairViewField",
		"click #repair_all_done":						"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "repair_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		$(this.el).html(this.template(this.model.toJSON()));
		return this;
    },
	doTimeouts: function() {
		gridsterById('gridster_repair');
		this.model.set("editing", false);
		var accident_partie = this.model.get("accident_partie");

		$(".repair_view .edit").trigger("click");
		$(".repair_view .edit").trigger("click");		
		$(".repair_view .delete").hide();
		$(".repair_view .reset").hide();
		
		$('.repair_view #requestedInput').datetimepicker({ 
			validateOnBlur:false, allowTimes:workingWeekTimes,step:30, 
			timepicker:false, 
			format:'m/d/Y',
			mask:false
		});
		$('.repair_view #receivedInput').datetimepicker({ 
			validateOnBlur:false, allowTimes:workingWeekTimes,step:30, 
			timepicker:false, 
			format:'m/d/Y',
			mask:false
		});
		
		$(".repair_view .form_label_vert").css("color", "white");
		$(".repair_view .form_label_vert").css("font-size", "1em");
		
		//add data
		var repair_data = this.model.toJSON().repair_info;
		if (repair_data=="") {
			return;
		}
		var repair_info = JSON.parse(repair_data)[this.model.get("accident_partie")];
		if (typeof repair_info == "undefined") {
			return;
		}
		Object.keys(repair_info).forEach(function(key) {
		  	var key_name = key;
			if ($("#" + key_name).is(':checkbox')) {
				if (repair_info[key] == "Y") {
					$("#" + key_name).attr("checked", "checked");
				}
			} else {
				$("#" + key_name).val(repair_info[key]);
			}		
			key_name = key_name.replace("Input", "Span");
			$("#" + key_name).html(repair_info[key]);
		})
	},
	editRepairViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".repair_" + field_name;
		}
		editField(element, master_class);
	},
	saveRepairViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".repair_" + field_name;
		}
		element_id = element_id.replace("SaveLink", "");
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		if (master_class != "") {
			//hide all the subs
			$(".span_class" + master_class).toggleClass("hidden");
			$(".input_class" + master_class).toggleClass("hidden");
			//$(field_name + "Save").toggleClass("hidden");
			
			$(".span_class" + master_class).addClass("editing");
			$(".input_class" + master_class).addClass("editing");
			$(field_name + "Save").addClass("editing");
		} else {
			//get the parent to get the class
			var theclass = element.parentElement.parentElement.parentElement.parentElement.parentElement.className;
			var arrClass = theclass.split(" ");
			theclass = "." + arrClass[0] + "." + arrClass[1];
			field_name = theclass + " #" + element_id;
			
			//restore the read look
			editField($(field_name + "Grid"));
			
			var element_value = $(field_name + "Input").val();
			$(field_name + "Span").html(escapeHtml(element_value));
		}			
		
		//this should not redraw, it will update if no id
		this.addRepairView(event);
	
	},
	saveRepair:function (event) {
		$("#edit_row").hide();
		$("#repair_gifsave").css("margin-top", "-5px");
		$("#repair_gifsave").show();
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		var url = "api/personalinjury/addrepair"
		
		formValues = $("#repair_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#repair_gifsave").hide();
					$(".repair_view #repair_panel_title").css("color", "green");
					
					redrawEditScreen(formValues, "repair_view");
					$(".repair_view .edit").trigger("click");
					
					setTimeout(function() {
						$(".repair_view #repair_panel_title").css("color", "white");
					}, 2500);
				}
			}
		});
	},
	addRepairView:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		if ($(".repair #table_id").val()=="") {
			$(".repair #table_id").val("-1")
		}
		addForm(event, "repair");
		return;
    },
	deleteRepairView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "repair");
		return;
    },
	
	toggleEditViewRepair: function(event) {
		event.preventDefault();
		
		var accident_partie = this.model.get("accident_partie");
		if ($(".repair #table_id").val()=="") {
			return;
		}
		
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
		$(" ." + accident_partie + " .repair").toggleClass("editing");
		setTimeout(function() {
			$(".repair").toggleClass("editing");
		}, 2500);
		$(" ." + accident_partie + " .repair .span_class").removeClass("editing");
		$(" ." + accident_partie + " .repair .input_class").removeClass("editing");

		$(" ." + accident_partie + " .repair .span_class").toggleClass("hidden");
		$(" ." + accident_partie + " .repair .input_class").toggleClass("hidden");
		$(" ." + accident_partie + " .repair .input_holder").toggleClass("hidden");
		$(" ." + accident_partie + " .button_row.repair").toggleClass("hidden");
		$(" ." + accident_partie + " .edit_row.repair").toggleClass("hidden");
		
		$(" .repair .edit").toggleClass("hidden");
		$(" .repair .save").toggleClass("hidden");
	},
	resetRepairForm: function(event) {
		event.preventDefault();
		this.toggleEditViewAccident(event);
		//this.render();
		//$("#address").hide();
	},
	
	valueRepairViewChanged: function(event) {
		event.preventDefault();
		//console.log(arguments[0].currentTarget.id);
		var source = arguments[0].currentTarget.id;
		source = source.replace("Input", "");
		
		var newval = $("#" + source + "Input").val();
		if (newval==""){
			if ($("#" + source + "Input").hasClass("required")) {
			newval = "Please fill me in";	
				$("#" + source + "Span").toggleClass("hidden");
			}
		} else {
			if (!$("#" + source + "Span").hasClass("hidden")) {
				$("#" + source + "Span").addClass("hidden");
			}
		}
		$("#" + source + "Span").html(escapeHtml(newval));
	}
});