window.dialogView = Backbone.View.extend({
	render: function () {
		$(this.el).html(this.template(this.model.toJSON()));
		
		//gridster the kai tab
		setTimeout("gridsterIt(3)", 10);
        return this;
	},
	
	events:{
		
		"dblclick .event_dialog .gridster_border": 	"editDialogField",
		"click .event_dialog .save_field":			"saveDialogViewField"
    },
	
	editDialogField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".event_dialog_" + field_name;
		}
		editField(element, master_class);
	},	
	saveDialogViewField: function (event) {
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
	toggleKAIEdit: function (event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		toggleFormEdit("kai");
	},
	
	resetKAIForm: function(e) {
		this.toggleKAIEdit(e);
		this.render();
	}
});