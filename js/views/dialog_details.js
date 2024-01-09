window.dialog_view = Backbone.View.extend({
	render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "dialog_view";
			var extension = "php";
			this.model.set("holder", "kase_content");
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		$(this.el).html(this.template(this.model.toJSON()));
		
		setTimeout("initializeGoogleAutocomplete('event_dialog')", 1000);
		$('#event_dateandtimeInput').datetimepicker({ validateOnBlur:false, minDate: 0, step:30});
		$('#end_dateInput').datetimepicker();
		$('#completed_dateInput').datetimepicker();
		$('#callback_dateInput').datetimepicker();
		
		if (this.model.id<0) {
			//$( ".parties .edit" ).trigger( "click" );
			setTimeout(function() {
				self.triggerEdit();
				if (self.model.get("event_kind")=="phone_call") {
					setTimeout(function() {
						$(".event_dialog #panel_title").html("Phone Call");
						$(".event_dialog #assigneeInput").focus();
					}, 100);
				}
			}, 500);
		}
		
			setTimeout(function(){
				workerEventComplete("event_dialog", "assigneeInput");
				//move the autocomplete
				$(".event_dialog ul.autocomplete_worker_event").css("left", "62px");
				$(".event_dialog ul.autocomplete_worker_event").css("margin-top", "-125px");
				$(".event_dialog ul.autocomplete_worker_event").css("z-index", "9999");
				$(".event_dialog ul.autocomplete_worker_event").css("width", "150px");
				
			}, 600);
			
        return this;
	},
	triggerEdit:function() {
		$(".event_dialog .edit" ).trigger( "click" );
		
	},
	events:{
		"click .event_dialog .edit": 				"toggleDialogEdit",
		"click .event_dialog .save": 				"addEvent",
		"click .event_dialog .reset": 				"resetDialogForm",
		"dblclick .event_dialog .gridster_border": 	"editDialogField",
		"click .event_dialog .save_field":			"saveDialogViewField"
    },
	
	addEvent:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "event_dialog", "events");
		return;
    },
	deleteEvent:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "event_dialog");
		return;
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
	},
	toggleDialogEdit: function(event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//get all the editing fields, and toggle them back
		$(".event_dialog .editing").toggleClass("hidden");
		$(".event_dialog .span_class").removeClass("editing");
		$(".event_dialog .input_class").removeClass("editing");
		
		$(".event_dialog .span_class").toggleClass("hidden");
		$(".event_dialog .input_class").toggleClass("hidden");
		$(".event_dialog .input_holder").toggleClass("hidden");
		$(".button_row.event_dialog").toggleClass("hidden");
		$(".edit_row.event_dialog").toggleClass("hidden");
	},
	resetDialogForm: function(event) {
		//this.toggleDialogEdit(e);
		event.preventDefault();
		this.toggleDialogEdit(event);
	}
});