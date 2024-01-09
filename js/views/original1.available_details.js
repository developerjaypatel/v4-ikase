window.availability_view = Backbone.View.extend({
	render: function () {
		$(this.el).html(this.template(this.model.toJSON()));
		
		//we are not in editing mode initially
		this.model.set("editing", false);

		if (this.model.id<0) {
			$( ".availability .edit" ).trigger( "click" );
		}
        return this;
	},
	
	events:{
		"dblclick .availability .gridster_border": 	"editAvailabilitysField",
		"click .availability .save":					"scheduleAddAvailability",
		"click .availability .save_field":				"saveAvailabilitysField",
		"click .availability .edit": 					"scheduleAvailabilitysEdit",
		"click .availability .reset": 					"scheduleAvailabilitysReset"
    },
	
	editAvailabilitysField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".availability_" + field_name;
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
			master_class = ".availability_" + field_name;
		}
		editField(element, master_class);
	},
	scheduleAddAvailability:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(availability_timeout_id);
		availability_timeout_id = setTimeout(function() {
			self.addAvailability(event);
		}, 200);
	},
	addAvailability:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		//turn off editing to toggle
		this.model.set("editing", false);
		addForm(event);
		//turn off editing altogether
		this.model.set("editing", false);
		return;
    },
	saveAvailabilitysField: function (event) {
		console.log("save_function_start");
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
		$(field_name + "Span").html(element_value);
		
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
	scheduleAvailabilitysEdit:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(availability_timeout_id);
		availability_timeout_id = setTimeout(function() {
			self.toggleAvailabilitysEdit(event);
		}, 200);
	},
	toggleAvailabilitysEdit: function (event) {
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
		toggleFormEdit("availability");
	},
	scheduleAvailabilitysReset:function(event) {
		event.preventDefault();
		var self = this;
		clearTimeout(availability_timeout_id);
		availability_timeout_id = setTimeout(function() {
			self.resetAvailabilitysForm(event);
		}, 200);
	},
	
	resetAvailabilitysForm: function(e) {
		//turn off editing to toggle
		this.model.set("editing", false);
		this.toggleAvailabilitysEdit(e);
		//if we reset, we do not want to edit going forward
		this.model.set("editing", false);
		//this.render();
	}
});

