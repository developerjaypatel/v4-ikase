window.interoffice_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	 events:{
        "click .interoffice .delete":				"deleteMessageView",
		"click .interoffice .save":					"addMessageView",
		"click .interoffice .save_field":			"saveMessageViewField",
		"click .interoffice .edit": 					"toggleMessageEdit",
		"click .interoffice .reset": 				"resetInjuryForm",
		"click .kase .calendar": 				"showCalendar",
		"keyup .interoffice .input_class": 			"valueMessageViewChanged",
		"dblclick .interoffice .gridster_border": 	"editMessageViewField"
    },
	
    render: function () {
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		mymodel = this.model.toJSON();
		$(this.el).html(this.template(mymodel));
		//$("#address").hide();
        //$('#details', this.el).html(new InjurySummaryView({model:this.model}).render().el);
		
		this.model.set("editing", false);
		if(this.model.id=="" || this.model.id==-1){
			//editing mode right away
			this.model.set("editing", false);
			
			setTimeout(function() { 
					$(".interoffice .edit").trigger("click"); 
					$(".interoffice .delete").hide();
					$(".interoffice .reset").hide();
				}, 1000);
			
			//setTimeout(function() { $("#occupationInput").focus(); }, 1100);
		}
		//setTimeout("initializeGoogleAutocomplete('message')", 1000);
        return this;
    },
	
	editMessageViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".interoffice_" + field_name;
		}
		editField(element, master_class);
	},
	editMessageViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".interoffice_" + field_name;
		}
		//editField(element, master_class);
	},
	
	saveMessageViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".interoffice_" + field_name;
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
		this.addMessageView(event);
	
	},
	
	addMessageView:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		if(this.model.id==-1){
			var blnValid = $("#bodyparts_form").parsley('validate');
			if (blnValid) {
				setTimeout(function() {
					$(".bodyparts .save").trigger("click");
				}, 1000);
			}
		}
		addForm(event, "interoffice");
		return;
    },
	deleteMessageView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "message");
		return;
    },
	
	toggleMessageEdit: function(event) {
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
		$(".interoffice .editing").toggleClass("hidden");
		$(".interoffice .span_class").removeClass("editing");
		$(".interoffice .input_class").removeClass("editing");
		
		$(".interoffice .span_class").toggleClass("hidden");
		$(".interoffice .input_class").toggleClass("hidden");
		$(".interoffice .input_holder").toggleClass("hidden");
		$(".button_row.interoffice").toggleClass("hidden");
		$(".edit_row.interoffice").toggleClass("hidden");
	},
	
	resetMessageForm: function(event) {
		event.preventDefault();
		this.toggleInjuryEdit(event);
		//this.render();
		//$("#address").hide();
	},
	valueMessageViewChanged: function(event) {
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