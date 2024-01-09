window.property_damage_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	events:{
        "click .property_damage .delete":							"deletePropertyDamageView",
		"click .property_damage .save":								"confirmApply",
		"click .property_damage .save_field":						"savePropertyDamageViewField",
		"click .property_damage .edit": 							"toggleEditViewPropertyDamage",
		"click .property_damage .reset": 							"resetPropertyDamageForm",
		"keyup .property_damage .input_class": 						"valuePropertyDamageViewChanged",
		"dblclick .property_damage .gridster_border": 				"editPropertyDamageViewField",
		"click #property_damage_all_done":							"doTimeouts"
    },
	render: function () {
		var self = this;
		$(this.el).html(this.template(this.model.toJSON()));
		
		setTimeout(function() {
			//self.doTimeouts();
		}, 1800);
		return this;
    },
	doTimeouts: function() {
		gridsterById("gridster_property_damage");
		//this.model.set("editing", false);
		var accident_partie = this.model.get("accident_partie");
		
		$("." + accident_partie + " .property_damage .edit").trigger("click");
		//$("." + accident_partie + " .property_damage .reset").trigger("click");
		$("." + accident_partie + " .property_damage .delete").hide();
		$("." + accident_partie + " .property_damage .reset").hide();
		//$("." + accident_partie + " .property_damage .edit").hide();
	
		
	},
	editPropertyDamageViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = "." + this.model.get("accident_partie") + " .property_damage_" + field_name;
		}
		editField(element, master_class);
	},
	savePropertyDamageViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = "." + this.model.get("accident_partie") + " .property_damage_" + field_name;
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
		this.addPropertyDamageView(event);
	
	},
	savePropertyDamage:function (event) {
		$("#edit_row").hide();
		$("#gifsave").css("margin-top", "-5px");
		$("#gifsave").show();
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		var api_url = "property_damage"
		
		addForm(event, "property_damage", api_url);
		
		return;
	},
	addPropertyDamageView:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		if ($("." + this.model.get("accident_partie") + " .property_damage #table_id").val()=="") {
			$("." + this.model.get("accident_partie") + " .property_damage #table_id").val("-1")
		}
		addForm(event, "property_damage");
		return;
    },
	deletePropertyDamageView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "property_damage");
		return;
    },
	
	toggleEditViewPropertyDamage: function(event) {
		event.preventDefault();
		var accident_partie = this.model.get("accident_partie");
		if ($(" ." + accident_partie + " .property_damage #table_id").val()=="") {
			return;
		}
		
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		//$("#address").show();
		//get all the editing fields, and toggle them back
		
		$(" ." + accident_partie + " .property_damage .editing").toggleClass("hidden");
		$(".plaintiff .property_damage .span_class").toggleClass("hidden");
		$(".plaintiff .property_damage .input_class").toggleClass("hidden");
		$(" ." + accident_partie + " .property_damage .span_class").removeClass("editing");
		$(" ." + accident_partie + " .property_damage .input_class").removeClass("editing");

		$(" ." + accident_partie + " .property_damage .span_class").toggleClass("hidden");
		$(" ." + accident_partie + " .property_damage .input_class").toggleClass("hidden");
		$(" ." + accident_partie + " .property_damage .input_holder").toggleClass("hidden");
		$(" ." + accident_partie + " .button_row.property_damage").toggleClass("hidden");
		$(" ." + accident_partie + " .edit_row.property_damage").toggleClass("hidden");
		
	},
	resetPropertyDamageForm: function(event) {
		event.preventDefault();
		this.toggleEditViewAccident(event);
		//this.render();
		//$("#address").hide();
	},
	
	valuePropertyDamageViewChanged: function(event) {
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