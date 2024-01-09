window.accident_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	events:{
        "click .accident .delete":					"deleteAccidentView",
		"click .accident .save":					"confirmApply",
		"click .accident .save_field":				"saveAccidentViewField",
		"click .accident .edit": 					"toggleEditViewAccident",
		"click .accident .reset": 					"resetAccidentForm",
		"keyup .accident .input_class": 			"valueAccidentViewChanged",
		"dblclick .accident .gridster_border": 		"editAccidentViewField",
		"click #accident_all_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "accident_view";
			var extension = "php";
			this.model.set("holder", "accident_holder");
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		mymodel = this.model.toJSON();
		
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			var view = "accident_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		return this;
    },
	doTimeouts: function() {
		var self = this;
		
		gridsterIt(10);
		if(this.model.id=="" || this.model.id==-1){
			//editing mode right away
			this.model.set("editing", false);
 
			$(".accident .edit").trigger("click"); 
			$(".accident .delete").hide();
			$(".accident .reset").hide();
			$(".accident #table_id").val("");

		}
		//initializeGoogleAutocomplete('accident');

		//get data from accident_info, use .each to populate fields
		var accident_info = JSON.parse(mymodel.accident_info);
		_.each( accident_info, function(the_info) {
			$("#" + the_info.name).val(the_info.value);
			the_info.name = the_info.name.replace("Input", "Span");
			$("#" + the_info.name).html(the_info.value);
		});
		
		setTimeout(function() {
			var accident_details = JSON.parse(mymodel.accident_details);
			_.each( accident_details, function(the_details) {
				var accident_form = the_details.form;
				var arrForm = accident_form.split("_");
				var accident_partie = arrForm[0];
				//we got the class name, now get values for each input
				_.each( the_details.data, function(the_detail) {
					//console.log(the_detail);
					$("." + accident_partie + " #" + the_detail.name).val(the_detail.value);
					the_detail.name = the_detail.name.replace("Input", "Span");
					$("." + accident_partie + " #" + the_detail.name).html(the_detail.value);
				});
			});
		}, 1000);
	},
	editAccidentViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".accident_" + field_name;
		}
		editField(element, master_class);
	},
	saveAccidentViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".accident_" + field_name;
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
		this.addAccidentView(event);
	
	},
	saveAccident:function (event) {
		$("#edit_row").hide();
		$("#gifsave").css("margin-top", "-5px");
		$("#gifsave").show();
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		var api_url = "accident"
		
		addForm(event, "accident", api_url);
		
		return;
	},
	addAccidentView:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		if ($(".accident #table_id").val()=="") {
			$(".accident #table_id").val("-1")
		}
		addForm(event, "accident");
		return;
    },
	deleteAccidentView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "accident");
		return;
    },
	toggleEditViewAccident: function(event) {
		event.preventDefault();
		if ($(".accident #table_id").val()=="") {
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
		$(".accident .editing").toggleClass("hidden");
		$(".accident .span_class").removeClass("editing");
		$(".accident .input_class").removeClass("editing");

		$(".accident .span_class").toggleClass("hidden");
		$(".accident .input_class").toggleClass("hidden");
		$(".accident .input_holder").toggleClass("hidden");
		$(".button_row.accident").toggleClass("hidden");
		$(".edit_row.accident").toggleClass("hidden");
		
	},
	resetAccidentForm: function(event) {
		event.preventDefault();
		this.toggleEditViewAccident(event);
		//this.render();
		//$("#address").hide();
	},
	
	valueAccidentViewChanged: function(event) {
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
window.accident_image = Backbone.View.extend({
    initialize:function () {

    },
    events:{
        "click #upload_it_five": "uploadIt"
    },
    render:function () {
		var self = this;
        $(this.el).html(this.template());
		setTimeout(function() {
			var timestamp = moment().format('YYYY-MM-DD h:mm:ss a');
			//console.log("timestamp:" + timestamp);
			var token = md5('ikase_system' + timestamp);
			$('#file_upload').uploadifive({
				'auto'             : false,
				'checkScript'      : 'check-exists.php',
				'formData'         : {
									   'timestamp' : timestamp,
									   'token'     : token,
									   'case_id' : self.model.get("case_id")
									 },
				'queueID'          : 'queue',
				'uploadScript'     : 'api/uploadifive.php',
				'onUploadComplete' : function(file, data) { 
					setTimeout(function() {
						self.saveFile(data);
					}, 50);
				}
			});
		}, 500);
		
        return this;
    },
	uploadIt: function(event) {
		event.preventDefault();
		$('#file_upload').uploadifive('upload');
	},
	saveFile: function(filename) {
		var self = this;
		var theattribute = $(".accident_image_form #attribute").val();
		var url = 'api/documents/add';
		var formValues = "verified=Y&type=&description_html=&description=applicant&document_extension=&document_filename=" + filename + "&document_name=" + filename + "&case_id=" + this.model.get("case_id") + "&case_uuid=" + this.model.get("case_uuid") + "&document_date=" + moment().format("YYYY-MM-DD") + "&parent_document_uuid=&attribute=" + theattribute;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//console.log(data.toJSON);
					$('#picture_holder').html("<img src='uploads/" + self.model.toJSON().customer_id + '/' + current_case_id + '/' + filename + "' class='applicant_img'>");
				}
			}
		});
	}
});
window.disability_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	events:{
        "click .disability .delete":					"deleteDisabilityView",
		"click .disability .save":						"saveDisability",
		/*"click .disability .save_field":				"saveAccidentViewField",*/
		"click .disability .edit": 						"toggleEditViewDisability",
		"click .disability .reset": 					"resetDisabilityForm",
		/*"keyup .disability .input_class": 				"valueAccidentViewChanged",
		"dblclick .disability .gridster_border": 		"editAccidentViewField",*/
		"click #disability_all_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		
		mymodel = this.model.toJSON();
		
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			var view = "disability_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);
		return this;
    },
	saveDisability: function(event) {
		event.preventDefault();
		
		addForm(event, "disability", "disability");
	},
	deleteDisabilityView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "disability");
		return;
    },
	doTimeouts: function() {
		var self = this;
		
		gridsterById("gridster_disability");
		if(this.model.id=="" || this.model.id==-1){
			//editing mode right away
			this.model.set("editing", false);
 
			$(".disability .edit").trigger("click"); 
			$(".disability .delete").hide();
			$(".disability .reset").hide();
			$(".disability #table_id").val("");

		}
		$("#disability_form .form_label_vert").css("color", "white");
		$("#disability_form .form_label_vert").css("font-size", "1em");
	},
	toggleEditViewDisability: function(event) {
		var self = this;
		
		event.preventDefault();
		if ($(".disability #table_id").val()=="") {
			//return;
		}
		
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		this.model.set("editing", true);
		//$("#address").show();
		//get all the editing fields, and toggle them back
		$(".disability .editing").toggleClass("hidden");
		$(".disability .span_class").removeClass("editing");
		$(".disability .input_class").removeClass("editing");

		$(".disability .span_class").toggleClass("hidden");
		$(".disability .input_class").toggleClass("hidden");
		$(".disability .input_holder").toggleClass("hidden");
		$(".button_row.disability").toggleClass("hidden");
		$(".edit_row.disability").toggleClass("hidden");
		
		setTimeout(function() {
			//all done
			self.model.set("editing", false);
		}, 2000);
	},
	resetDisabilityForm: function(event) {
		event.preventDefault();
		this.toggleEditViewDisability(event);
		//this.render();
		//$("#address").hide();	
	}
});
window.disability_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #compose_new_disability":				"newDisability",
		"click .open_disability":						"editDisability",
		"click #disability_listing_all_done":			"doTimeouts"
	},
	render:function() {
		var self = this;
		if (typeof this.template != "function") {
			var view = "disability_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   var self = this;
	   
	   var disabilities = this.collection.toJSON();
	   var case_id = current_case_id;
	   var embedded = this.model.get("embedded");
	   var page_title = this.model.get("page_title");
	   
	   try {
			$(this.el).html(this.template({
				disabilities: 			disabilities, 
				case_id: 				case_id,
				embedded: 				embedded,
				page_title:				page_title
			}));
		} catch(err) {
			alert(err);
			
			return "";
		}
	   return this;
	},
	editDisability: function(event) {
		event.preventDefault();
		
		var arrID = event.currentTarget.id.split("_");
		var case_id = arrID[arrID.length - 2];
		var disability_id = arrID[arrID.length - 1];
		
		document.location.href = "#disability/" + case_id + "/" + disability_id;
	},
	newDisability: function(event) {
		event.preventDefault();
		
		document.location.href = "#disability/" + current_case_id + "/-1";
	},
	doTimeouts: function() {
		$("#disability_listing th").css("font-size", "1.1em");
		
		var claimmodel = new Backbone.Model();
		claimmodel.set("id", -1);
		claimmodel.set("embedded", false);
		claimmodel.set("holder", "claim_holder");
		claimmodel.set("case_id", current_case_id);
		//bring up the claim view form
		$('#claim_holder').html(new claim_view({model: claimmodel}).render().el);
		$('#claim_holder').show();
		
		var surgeries = new SurgeryCollection({ case_id: current_case_id });
		surgeries.fetch({
			success: function(data) {
				if (data.length > 0) {
					var surgery = new Backbone.Model;
					surgery.set("embedded", false);
					surgery.set("page_title", "Surgeries");
					surgery.set("case_id", current_case_id);
					surgery.set("holder", "surgery_holder");
					
					$('#surgery_holder').html(new surgery_listing_view({collection: data, model: surgery}).render().el);
					$("#surgery_holder").show();
				}
			}
		});
	}
});
window.surgery_form = Backbone.View.extend({
	initialize:function () {
		
    },
	events:{
		"click .save":								"saveSurgery",
		"click #surgery_all_done":					"doTimeouts"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "surgery_form";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		var mymodel = this.model.toJSON();
		
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		return this;
    },
	saveSurgery: function(event) {
		event.preventDefault();
		
		saveSurgery();
	},
	doTimeouts: function() {
		$("#surgery_dateInput").datetimepicker({
			timepicker:false, 
			format:"m/d/Y",
			mask:false,
			onChangeDateTime:function(dp,$input){

			}
		});	
	}
});
window.surgery_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #compose_new_surgery":				"newSurgery",
		"click .open_surgery":						"editSurgery",
		"click #surgery_listing_all_done":			"doTimeouts"
	},
	render:function() {
		var self = this;
		if (typeof this.template != "function") {
			var view = "surgery_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   var self = this;
	   
	   var surgeries = this.collection.toJSON();
	   var case_id = current_case_id;
	   var embedded = this.model.get("embedded");
	   var page_title = this.model.get("page_title");
	   
	   try {
			$(this.el).html(this.template({
				surgeries: 				surgeries, 
				case_id: 				case_id,
				embedded: 				embedded,
				page_title:				page_title
			}));
		} catch(err) {
			alert(err);
			
			return "";
		}
	   return this;
	},
	editSurgery: function(event) {
		event.preventDefault();
		
		var arrID = event.currentTarget.id.split("_");
		var case_id = arrID[arrID.length - 2];
		var surgery_id = arrID[arrID.length - 1];
		
		//document.location.href = "#surgery/" + case_id + "/" + surgery_id;
		
		composeSurgery("compose_surgery_" + surgery_id, current_case_id);
	},
	newSurgery: function(event) {
		event.preventDefault();
		
		//document.location.href = "#surgery/" + current_case_id + "/-1";
		composeSurgery("compose_surgery_-1", current_case_id);
		
	},
	doTimeouts: function() {
		$("#surgery_listing th").css("font-size", "1.1em");
	}
});