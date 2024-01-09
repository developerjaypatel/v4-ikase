window.setting_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	initialize:function () {
		_.bindAll(this);
    },
	 events:{
		"click .setting .save":					"addSettingView",
		"change .setting #categoryInput":		"checkAttachment",
		"click #default_valueInput":			"showColorPicker",
		"click #setting_all_done":				"doTimeouts"
    },
    render: function () {
		var self = this;
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		mymodel = this.model.toJSON();
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			var view = "setting_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		
		return this;
    },
	showColorPicker: function(event) {
		$(".cp-color-picker").css("zIndex", "9999");
	},
	doTimeouts: function(event) {	
		var self = this;	 
		$(".setting .edit").trigger("click"); 
		$(".setting .delete").hide();
		$(".setting .reset").hide();
		$('#setting_attachments').html(new setting_attach({model: self.model}).render().el);
	
		$(".setting_attach_form #queue").css("height", "70px");
		$(".setting_attach_form #queue").css("width", "550px");
		$(".setting_attach_form").css("border","0px #000000 solid");
		
		//is this a document send
		if (self.model.get("document_id") != "" && typeof self.model.get("document_id") != "undefined") {
			//look up the document and get it
			var send_document = new Document({case_id:  self.model.case_id});
			send_document.set("id", self.model.get("document_id"));
			send_document.fetch({
				success: function(data) {
					$("#send_document_id").val(data.get("document_id"));
					$("#send_queue").html(data.get("document_filename"));
				}
			});
		}
	
		if ($("#categoryInput").val() == "date") {			
			$('#setting_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30,  timepicker:false});
			$('#default_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30,  timepicker:false});
		}

		if ($("#categoryInput").val() == "date_and_time") {
			$('#setting_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30});
			$('#default_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30});	
		}

		if ($("#categoryInput").val() == "time") {
			$('#setting_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30,  datepicker:false});
			$('#default_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30,  datepicker:false});	
		}

		if ($("#categoryInput").val() == "calendar_colors" || $("#categoryInput").val() == "calendar_type") {
			/*
			$('#setting_valueInput').colorpicker({parts: ['header', 'map', 'bar', 'hex',
			'hsv', 'rgb', 'alpha', 'preview', 'footer']});
			$('#default_valueInput').colorpicker({parts: ['header', 'map', 'bar', 'hex',
			'hsv', 'rgb', 'alpha', 'preview', 'footer']});
			*/
			$('#default_valueInput').colorPicker();
			$('#default_valueInput').css("color", "white");
		}

		var thetext = self.model.get("setting_value");
		self.checkAttachment();
		$("#setting_valueInput").val(thetext);
	},
	selectChanges: function(event) {
		if ($("#categoryInput").val() == "delay") {
			$('#setting_valueInput').unbind().removeData();
			$('#default_valueInput').unbind().removeData();
		}
		if ($("#categoryInput").val() == "date") {
			$('#setting_valueInput').unbind().removeData();
			$('#default_valueInput').unbind().removeData();
			
			$('#setting_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30,  timepicker:false});
			$('#default_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30,  timepicker:false});
		}
		if ($("#categoryInput").val() == "date_and_time") {
			$('#setting_valueInput').unbind().removeData();
			$('#default_valueInput').unbind().removeData();
			
			$('#setting_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30});
			$('#default_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30});
		}
		if ($("#categoryInput").val() == "time") {
			$('#setting_valueInput').unbind().removeData();
			$('#default_valueInput').unbind().removeData();
			$('#setting_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30,  datepicker:false});
			$('#default_valueInput').datetimepicker({ validateOnBlur:false, minDate: 0, defaultTime:'8:00',  step:30,  datepicker:false});
		}
		if ($("#categoryInput").val() == "choice") {
			$('#setting_valueInput').unbind().removeData();
			$('#default_valueInput').unbind().removeData();
			
		}
		if ($("#categoryInput").val() == "calendar_colors" || $("#categoryInput").val() == "calendar_type") {
			$('#setting_valueInput').unbind().removeData();
			$('#default_valueInput').unbind().removeData();
			/* 
			$('#setting_valueInput').colorpicker({parts: ['header', 'map', 'bar', 'hex',
			'hsv', 'rgb', 'alpha', 'preview', 'footer']});
			$('#default_valueInput').colorpicker({parts: ['header', 'map', 'bar', 'hex',
			'hsv', 'rgb', 'alpha', 'preview', 'footer']});
			*/
			$('#default_valueInput').colorPicker();
		}
		if ($("#categoryInput").val() == "autocomplete") {
			$('#setting_valueInput').unbind().removeData();
			$('#default_valueInput').unbind().removeData();
			
		}
	},
	checkAttachment: function (event) {
		if (typeof event != "undefined") {
			event.preventDefault(); // Don't let this button submit the form
		}
		//special cases for now
		if ($("#categoryInput").val() == "calendar_name") {
			$("#setting_label").html("Name:");
			$("#value_label").html("Sort Order:");
			$("#default_label").html("Employee Y/N");
		}
		if ($("#categoryInput").val() == "calendar_type") {
			$("#setting_label").html("Name:");
			$("#setting_default_value_holder").fadeIn();
			$("#setting_value_holder").fadeIn();
		}
		if ($("#categoryInput").val() == "letterhead" || $("#categoryInput").val() == "lettersignature") {
			$("#settingInput").val($("#categoryInput").val());
			$("#setting_attachments").fadeIn();
		}
		if ($("#categoryInput").val() == "email") {
			$("#settingInput").val("footer");
			//change the default value into a textarea
			var theinput = $("#setting_valueInput");
			var thetext = theinput.val();
			var textbox = $(document.createElement('textarea')).attr('style', theinput.style);
			textbox.prop("id", "setting_valueInput");
			textbox.prop("name", "setting_valueInput");
		    theinput.replaceWith(textbox);
			$("#setting_valueInput").css("width", $("#settingInput").css("width"));
			$("#setting_valueInput").css("height", "100px");
			$("#setting_valueInput").val(thetext);
			$("#setting_default_value_holder").fadeOut();
		}
		this.selectChanges(event);
	},
	editSettingViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".setting_" + field_name;
		}
		editField(element, master_class);
	},
	editSettingViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".setting_" + field_name;
		}
		//editField(element, master_class);
	},
	
	saveSettingViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".setting_" + field_name;
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
		this.addSettingView(event);
	
	},
	
	addSettingView:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		if(this.model.id==-1){
			var blnValid = $("#setting").parsley('validate');
			if (blnValid) {
				setTimeout(function() {
					$(".setting .save").trigger("click");
				}, 1000);
			}
		}
		addForm(event, "setting");
		return;
    },
	deleteSettingView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "setting");
		return;
    },
	
	toggleSettingEdit: function(event) {
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
		$(".setting .editing").toggleClass("hidden");
		$(".setting .span_class").removeClass("editing");
		$(".setting .input_class").removeClass("editing");
		
		$(".setting .span_class").toggleClass("hidden");
		$(".setting .input_class").toggleClass("hidden");
		$(".setting .input_holder").toggleClass("hidden");
		$(".button_row.setting").toggleClass("hidden");
		$(".edit_row.setting").toggleClass("hidden");
	},
	
	resetSettingForm: function(event) {
		event.preventDefault();
		this.toggleInjuryEdit(event);
		//this.render();
		//$("#address").hide();
	},
	valueSettingViewChanged: function(event) {
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