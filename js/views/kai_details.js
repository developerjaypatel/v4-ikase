window.kai_view = Backbone.View.extend({
	render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "kai_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		
		this.model.set("holder", "kai_holder");
		//this.model.set("template_loaded", "");
		mymodel = this.model.toJSON();
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		this.model.set("editing", false);
		
		if(this.model.id=="" || this.model.id==-1){
			//editing mode right away
			this.model.set("editing", false);
			//setTimeout('$(".person .edit" ).trigger( "click" )', 500);
			setTimeout(function() { 
					$("#kai_buttons").hide();
					$(".kai .edit").trigger("click"); 
				}, 1000);
			//setTimeout(function() { $("#occupationInput").focus(); }, 1100);
		}
		setTimeout(function() { 
			$('#rating_ageInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
		}, 700);
        return this;
	},
	
	events:{
		
		"dblclick .kai .gridster_holder": 	"editKAIField",
		"click .kai .save":					"addKAI",
		"click .kai .save_field":			"saveKAIField",
		"click .kai .edit": 				"toggleKAIEdit",
		"click .kai .reset": 				"resetKAIForm"
    },
	
	editKAIField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".applicant_" + field_name;
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
			master_class = ".applicant_" + field_name;
		}
		editField(element, master_class);
	},
	addKAI:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "kai", "person");
		return;
    },
	saveKAIField: function (event) {
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
		$(field_name + "Span").html(element_value);
		
		this.model.set(element, element_value);
		
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		this.addKase(event);
	},
	toggleKAIEdit: function(event) {
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
		$(".kai_view .editing").toggleClass("hidden");
		$(".kai_view .span_class").removeClass("editing");
		$(".kai_view .input_class").removeClass("editing");
		
		$(".kai_view .span_class").toggleClass("hidden");
		$(".kai_view .input_class").toggleClass("hidden");
		$(".kai_view .input_holder").toggleClass("hidden");
		$(".button_row.kai").toggleClass("hidden");
		$(".edit_row.kai").toggleClass("hidden");
	},
	
	resetKAIForm: function(e) {
		this.toggleKAIEdit(e);
	}
});

window.partie_kai_view = Backbone.View.extend({
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kai_holder");
			var view = "partie_kai_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}	
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		var self = this;
		this.model.set("holder", "kai_holder");
		//this.model.set("template_loaded", "");
		mymodel = this.model.toJSON();
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		this.model.set("editing", false);
		
		if(this.model.id=="" || this.model.id==-1){
			//editing mode right away
			this.model.set("editing", false);
			//setTimeout('$(".person .edit" ).trigger( "click" )', 500);
			setTimeout(function() { 
					$("#kai_buttons").hide();
					$(".kai .edit").trigger("click"); 
				}, 1000);
			//setTimeout(function() { $("#occupationInput").focus(); }, 1100);
		}
		setTimeout(function() { 
			$('#rating_ageInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
		}, 700);
		setTimeout(function() {
			var model_kai_info = self.model.get("kai_info");
			
			if (typeof model_kai_info == "undefined") {
				return;
			}
			//alert(model_personal_injury_info);
			var kai_info = JSON.parse(model_kai_info);
			_.each( kai_info, function(the_info) {
				$("#" + the_info.name).val(the_info.value);
				the_info.name = the_info.name.replace("Input", "Span");
				$("#" + the_info.name).html(the_info.value);
			});
		}, 500);
        return this;
	},
	
	events:{
		
		"dblclick .partie_kai .gridster_holder": 	"editPartieKAIField",
		"click .partie_kai .save":					"addPartieKAI",
		"click .partie_kai .save_field":			"savePartieKAIField",
		"click .partie_kai .edit": 					"togglePartieKAIEdit",
		"click .partie_kai .reset": 				"resetPartieKAIForm"
    },
	
	editPartieKAIField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".applicant_" + field_name;
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
			master_class = ".applicant_" + field_name;
		}
		editField(element, master_class);
	},
	addPartieKAI:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		this.savePartieKai(event);
		//addForm(event, "kai", "person");
		return;
    },
	savePartieKAIField: function (event) {
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
		$(field_name + "Span").html(element_value);
		
		this.model.set(element, element_value);
		
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		this.addKase(event);
	},
	togglePartieKAIEdit: function(event) {
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
		$(".partie_kai .editing").toggleClass("hidden");
		$(".partie_kai .span_class").removeClass("editing");
		$(".partie_kai .input_class").removeClass("editing");
		
		$(".partie_kai .span_class").toggleClass("hidden");
		$(".partie_kai .input_class").toggleClass("hidden");
		$(".partie_kai .input_holder").toggleClass("hidden");
		$(".button_row.partie_kai").toggleClass("hidden");
		$(".edit_row.partie_kai").toggleClass("hidden");
	},
	
	resetPartieKAIForm: function(e) {
		this.togglePartieKAIEdit(e);
	},
	savePartieKai: function(event) {
		//alert(current_case_id);
		event.preventDefault();
		var self = this;
		var url = "api/corporationkai/update";
		
		var inputArr = $("#partie_kai_form .input_class").serializeArray();
		var partie_id = $("#id").val();
		//var table_name = $("#table_name").val();
		formValues = "case_id=" + current_case_id + "&partie_id=" + partie_id;
		formValues += "&partie_kai_info=" + JSON.stringify(inputArr);
		
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
					
					$("#partie_kai_form #panel_title").css("color", "green");
					self.togglePartieKAIEdit(event);
					setTimeout(function(){ 
						
						$("#partie_kai_form #panel_title").css("color", "white");
						
					}, 2000);
				}
			}
		});
    }
});