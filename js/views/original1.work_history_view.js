var work_history_timeout_id = false;
window.work_history_earnings_view = Backbone.View.extend({
	events:{
		
		"dblclick .work_history .gridster_border": 			"editWorkHistoryEarningsField",
		"click .work_history .save":						"addWorkHistory",
		"click .work_history .save_field":					"saveWorkHistoryEarningsField",
		"click .work_history .edit": 						"scheduleWorkHistoryEarningsEdit",
		"click .work_history .reset": 						"scheduleWorkHistoryEarningsReset",
		"blur .work_history #work_history_dateInput": 		"splitDate",
		"click #work_history_earnings_done":				"doTimeouts"
    },
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "work_history_earnings_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		gridsterById("gridster_work_history_earnings");
		
		//gridsterById("gridster_car_passenger");
		this.model.set("editing", false);
		
		setTimeout(function() {
			if (self.model.id<0) {
				$( ".work_history .edit" ).trigger( "click" );
			}
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.work_history_info);
			//console.log(self.model.work_history_info);
			$("#work_history_earnings_form #table_id").val(self.model.id);
			var model_work_history_info = self.model.get("work_history_info");
			
			//alert(model_work_history_info);
			var work_history_info = JSON.parse(model_work_history_info);
			_.each( work_history_info, function(the_info) {
				$("#" + the_info.name).val(the_info.value);
				the_info.name = the_info.name.replace("Input", "Span");
				$("#" + the_info.name).html(the_info.value);
			});
		}		
	},
	editWorkHistoryEarningsField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".work_history_" + field_name;
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
			master_class = ".work_history_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddWorkHistory:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(work_history_timeout_id);
		work_history_timeout_id = setTimeout(function() {
			self.addWorkHistory(event);
		}, 200);
	},
	addWorkHistory:function (event) {
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
		addForm(event, "work_history");
		//turn off editing altogether
		this.model.set("editing", false);
		return;
		*/
    },
	
	saveWorkHistoryEarningsField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		//console.log("save_function_next");
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		//console.log("save_function_after");
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		//console.log("save_function_model_start");
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
	scheduleWorkHistoryEarningsEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(work_history_timeout_id);
		work_history_timeout_id = setTimeout(function() {
			self.toggleWorkHistoryEarningsEdit(event);
		}, 200);
	},
	toggleWorkHistoryEarningsEdit: function (event) {
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
		toggleFormEdit("work_history_earnings");
		toggleFormEdit("work_history_disability");
		toggleFormEdit("work_history_compensation");
		//$(".work_history_disability .edit").trigger("click");
	},
	scheduleWorkHistoryEarningsReset:function(event) {
		this.model.set("editing", false);
		event.preventDefault();
		var self = this;
		clearTimeout(work_history_timeout_id);
		work_history_timeout_id = setTimeout(function() {
			self.resetWorkHistoryEarningsForm(event);
		}, 200);
	},
	
	resetWorkHistoryEarningsForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleWorkHistoryEarningsEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/workhistory/add";
		var inputArr = $("#work_history_earnings_form .input_class").serializeArray();
		var inputArr2 = $("#work_history_disability_form .input_class").serializeArray();
		var inputArr3 = $("#work_history_compensation_form .input_class").serializeArray();
		inputArr = inputArr.concat(inputArr2, inputArr3);
		
		formValues = "case_id=" + current_case_id;
		var work_history_id = $("#work_history_earnings_form #table_id").val();
		formValues += "&table_id=" + work_history_id;
		formValues += "&work_history_info=" + JSON.stringify(inputArr);
		
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
					$(".work_history_earnings #panel_title").css("color", "green");
					toggleFormEdit("work_history_earnings");
					toggleFormEdit("work_history_disability");
					toggleFormEdit("work_history_compensation");
					
					redrawJsonScreen(formValues);
					
					setTimeout(function(){ 
						
						$(".work_history_earnings #panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});
var work_disability_timeout_id = false;
window.work_history_disability_view = Backbone.View.extend({
	events:{
		"click .work_history_disability .edit": 			"scheduleWorkHistoryDisabilityEdit",
		"dblclick .work_history .gridster_border": 			"editWorkHistoryDisabilityField",
		"click #work_history_disability_done":				"doTimeouts"
    },
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "work_history_disability_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		gridsterById("gridster_work_history_disability");
		
		//gridsterById("gridster_car_passenger");
		this.model.set("editing", false);
		
		setTimeout(function() {
			//no saving, we will save with earnings only
			$( ".work_history_disability .edit" ).hide();
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.work_history_info);
			//console.log(self.model.work_history_info);
			var model_work_history_info = self.model.get("work_history_info");
			
			//alert(model_work_history_info);
			var work_history_info = JSON.parse(model_work_history_info);
			_.each( work_history_info, function(the_info) {
				$("#" + the_info.name).val(the_info.value);
				the_info.name = the_info.name.replace("Input", "Span");
				$("#" + the_info.name).html(the_info.value);
			});
		}		
	},
	editWorkHistoryDisabilityField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".work_history_" + field_name;
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
			master_class = ".work_history_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddWorkHistory:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(work_disability_timeout_id);
		work_disability_timeout_id = setTimeout(function() {
			self.addWorkHistory(event);
		}, 200);
	},
	addWorkHistory:function (event) {
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
		addForm(event, "work_history");
		//turn off editing altogether
		this.model.set("editing", false);
		return;
		*/
    },
	
	saveWorkHistoryDisabilityField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		//console.log("save_function_next");
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		//console.log("save_function_after");
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		//console.log("save_function_model_start");
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
	scheduleWorkHistoryDisabilityEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(work_disability_timeout_id);
		work_disability_timeout_id = setTimeout(function() {
			self.toggleWorkHistoryDisabilityEdit(event);
		}, 200);
	},
	toggleWorkHistoryDisabilityEdit: function (event) {
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
		toggleFormEdit("work_history_disability");
	},
	scheduleWorkHistoryDisabilityReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(work_disability_timeout_id);
		work_disability_timeout_id = setTimeout(function() {
			self.resetWorkHistoryDisabilityForm(event);
		}, 200);
	},
	
	resetWorkHistoryDisabilityForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleWorkHistoryDisabilityEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/workhistory/add";
		var inputArr = $("#work_history_disability_form .input_class").serializeArray();
		
		formValues = "case_id=" + current_case_id;
		var work_history_id = $("#work_history_disability_form #table_id").val();
		formValues += "&table_id=" + work_history_id;
		formValues += "&work_history_info=" + JSON.stringify(inputArr);
		
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
					$("#panel_title").css("color", "green");
					toggleFormEdit("work_history_disability");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});
var work_compensation_timeout_id = false;
window.work_history_compensation_view = Backbone.View.extend({
	events:{
		"click .work_history_compensation .edit": 			"scheduleWorkHistoryCompensationEdit",
		"dblclick .work_history .gridster_border": 			"editWorkHistoryCompensationField",
		"click #work_history_compensation_done":				"doTimeouts"
    },
	render: function () {
		var self = this;
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "work_history_compensation_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	doTimeouts: function() {
		
		var self = this;
		
		//we are not in editing mode initially
		gridsterById("gridster_work_history_compensation");
		
		//gridsterById("gridster_car_passenger");
		this.model.set("editing", false);
		
		setTimeout(function() {
			//no saving, we will save with earnings only
			$( ".work_history_compensation .edit" ).hide();
		}, 600);
		if (self.model.id > 0) {
			//alert(self.model.work_history_info);
			//console.log(self.model.work_history_info);
			var model_work_history_info = self.model.get("work_history_info");
			
			//alert(model_work_history_info);
			var work_history_info = JSON.parse(model_work_history_info);
			_.each( work_history_info, function(the_info) {
				$("#" + the_info.name).val(the_info.value);
				the_info.name = the_info.name.replace("Input", "Span");
				$("#" + the_info.name).html(the_info.value);
			});
		}		
	},
	editWorkHistoryCompensationField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".work_history_" + field_name;
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
			master_class = ".work_history_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddWorkHistory:function(event) {
		var self = this;
		event.preventDefault();
		clearTimeout(work_compensation_timeout_id);
		work_compensation_timeout_id = setTimeout(function() {
			self.addWorkHistory(event);
		}, 200);
	},
	addWorkHistory:function (event) {
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
		addForm(event, "work_history");
		//turn off editing altogether
		this.model.set("editing", false);
		return;
		*/
    },
	
	saveWorkHistoryCompensationField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		//console.log("save_function_next");
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		//console.log("save_function_after");
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(escapeHtml(element_value));
		
		this.model.set(element, element_value);
		//console.log("save_function_model_start");
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
	scheduleWorkHistoryCompensationEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(work_compensation_timeout_id);
		work_compensation_timeout_id = setTimeout(function() {
			self.toggleWorkHistoryCompensationEdit(event);
		}, 200);
	},
	toggleWorkHistoryCompensationEdit: function (event) {
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
		toggleFormEdit("work_history_compensation");
	},
	scheduleWorkHistoryCompensationReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(work_compensation_timeout_id);
		work_compensation_timeout_id = setTimeout(function() {
			self.resetWorkHistoryCompensationForm(event);
		}, 200);
	},
	
	resetWorkHistoryCompensationForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleWorkHistoryCompensationEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	},
	saveEverything: function(event) {
		event.preventDefault();
		var self = this;
		var url = "api/workhistory/add";
		var inputArr = $("#work_history_compensation_form .input_class").serializeArray();
		
		formValues = "case_id=" + current_case_id;
		var work_history_id = $("#work_history_compensation_form #table_id").val();
		formValues += "&table_id=" + work_history_id;
		formValues += "&work_history_info=" + JSON.stringify(inputArr);
		
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
					$("#panel_title").css("color", "green");
					toggleFormEdit("work_history_compensation");
					setTimeout(function(){ 
						
						$("#panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});