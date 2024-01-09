window.injury_number_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	 events:{
        "click .injury_number .delete":					"deleteInjuryView",
		"click .injury_number .save":					"saveInjuryNumber",
		"click .injury_number .save_field":				"saveInjuryViewField",
		"click .injury_number .edit": 					"toggleInjuryEdit",
		"click .injury_number .reset": 					"resetInjuryForm",
		"click .kase .calendar": 						"showCalendar",
		"keyup .injury_number .input_class": 			"valueInjuryViewChanged",
		"dblclick .injury_number .gridster_border": 	"editInjuryViewField",
		"dblclick #notesGrid": 							"editInjuryViewNotesField",
		"click .injury_number#all_done":				"doTimeouts"
    },
	
    render: function () {
		if (typeof this.model.toJSON()[0] == "undefined") {
			mymodel = this.model.toJSON();
		} else {
			mymodel = this.model.toJSON()[0];
		}
		$(this.el).html(this.template(mymodel));		
		
		return this;
    },
	doTimeouts: function() {
		var self = this;
		if (typeof this.model.toJSON()[0] == "undefined") {
			mymodel = this.model.toJSON();
		} else {
			mymodel = this.model.toJSON()[0];
		}
		
		if (mymodel.gridster_me || mymodel.grid_it) {
			gridsterById('gridster_injury_number');
		}
		if(mymodel.id=="" || mymodel.id==-1){	
			$(".injury_number .edit").trigger("click"); 
			$(".injury_number .delete").hide();
			$(".injury_number .reset").hide();
		}
		/*
		if (customer_id == 1033) {
			$("#billing_time_dropdown_inInput").editableSelect({
				onSelect: function (element) {
					var billing_time = $("#billing_time_dropdown_inInput").val();
					$("#billing_time").val(billing_time);
					//alert(billing_time);
				}
			});
		}
		*/
	},
	
	editInjuryViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".injury_number_" + field_name;
		}
		editField(element, master_class);
	},
	editInjuryViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".injury_number_" + field_name;
		}
		//editField(element, master_class);
	},
	
	saveInjuryViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".injury_number_" + field_name;
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
		this.saveInjuryNumber(event);
	
	},
	
	saveInjuryNumber:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "injury_number");
		return;
    },
	deleteInjuryView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "injury_number");
		return;
    },
	
	toggleInjuryEdit: function(event) {
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
		$(".injury_number_view .editing").toggleClass("hidden");
		$(".injury_number_view .span_class").removeClass("editing");
		$(".injury_number_view .input_class").removeClass("editing");
		
		$(".injury_number_view .span_class").toggleClass("hidden");
		$(".injury_number_view .input_class").toggleClass("hidden");
		$(".injury_number_view .input_holder").toggleClass("hidden");
		$(".button_row.injury_number").toggleClass("hidden");
		$(".edit_row.injury_number").toggleClass("hidden");
		
		showEndtime();
	},
	
	resetInjuryForm: function(event) {
		event.preventDefault();
		this.toggleInjuryEdit(event);
		//this.render();
		//$("#address").hide();
	},
	valueInjuryViewChanged: function(event) {
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
function showEndtime() { 
	if (document.getElementById("checkCT") != null) {
		if (document.getElementById("checkCT").checked) {
			$( ".datepicker_1" ).show();
		} else {
			$( ".datepicker_1" ).hide();
			$( ".datepicker_1" ).val("");
		}
	}
}
window.injury_add_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	 events:{
        "click .additional_case_number .delete":				"deleteInjuryView",
		"click .additional_case_number .save":					"saveAdditionalCaseNumber",
		"click .additional_case_number .save_field":			"saveInjuryViewField",
		"click .additional_case_number .edit": 					"toggleInjuryEdit",
		"click .additional_case_number .reset": 				"resetInjuryForm",
		"keyup .additional_case_number .input_class": 			"valueInjuryViewChanged",
		"dblclick .additional_case_number .gridster_border": 	"editInjuryViewField",
		"dblclick #notesGrid": 									"editInjuryViewNotesField",
		"click .additional_case_number#all_done":				"doTimeouts"
    },
	
    render: function () {
		if (typeof this.model.toJSON()[0] == "undefined") {
			mymodel = this.model.toJSON();
		} else {
			mymodel = this.model.toJSON()[0];
		}
		$(this.el).html(this.template(mymodel));
		
		return this;
    },
	doTimeouts: function() {
		var self = this;
		if (typeof this.model.toJSON()[0] == "undefined") {
			mymodel = this.model.toJSON();
		} else {
			mymodel = this.model.toJSON()[0];
		}
		
		if (mymodel.gridster_me || mymodel.grid_it) {
			gridsterById('gridster_additional_case_number');		
		}
		
		if(mymodel.id=="" || mymodel.id==-1){
			$(".additional_case_number .edit").trigger("click"); 
			$(".additional_case_number .delete").hide();
			$(".additional_case_number .reset").hide();
		}
	},
	editInjuryViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".additional_case_number_" + field_name;
		}
		editField(element, master_class);
	},
	saveInjuryViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".additional_case_number_" + field_name;
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
		this.saveAdditionalCaseNumber(event);
	
	},
	saveAdditionalCaseNumber:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "additional_case_number");
		return;
    },
	deleteInjuryView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "additional_case_number");
		return;
    },
	
	toggleInjuryEdit: function(event) {
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
		$(".additional_case_number .editing").toggleClass("hidden");
		$(".additional_case_number .span_class").removeClass("editing");
		$(".additional_case_number .input_class").removeClass("editing");
		
		$(".additional_case_number .span_class").toggleClass("hidden");
		$(".additional_case_number .input_class").toggleClass("hidden");
		$(".additional_case_number .input_holder").toggleClass("hidden");
		$(".button_row.additional_case_number").toggleClass("hidden");
		$(".edit_row.additional_case_number").toggleClass("hidden");
		
		showEndtime();
	},
	
	resetInjuryForm: function(event) {
		event.preventDefault();
		this.toggleInjuryEdit(event);
		//this.render();
		//$("#address").hide();
	},
	valueInjuryViewChanged: function(event) {
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