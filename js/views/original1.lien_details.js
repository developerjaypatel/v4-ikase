window.lien_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	 events:{
        "click .lien .delete":					"deleteLienView",
		"click .lien .save":					"saveLien",
		"click .lien .save_field":				"saveLienViewField",
		"click .lien .edit": 					"toggleLienEdit",
		"click .lien .reset": 					"resetLienForm",
		"click .lien #new_appearance":			"newAppearance",
		"keyup .lien .input_class": 			"valueLienViewChanged",
		"dblclick .lien .gridster_border": 		"editLienViewField",
		"dblclick #notesGrid": 					"editLienViewNotesField",
		"click #lien_all_done":					"doTimeouts"
    },
	
    render: function () {
		var mymodel = this.model.toJSON();
		if (isDate(mymodel.date_filed)) {
			mymodel.date_filed = moment(mymodel.date_filed).format("MM/DD/YYYY");
		} else {
			mymodel.date_filed = "";
		}
		if (isDate(mymodel.date_paid)) {
			mymodel.date_paid = moment(mymodel.date_paid).format("MM/DD/YYYY");
		} else {
			mymodel.date_paid = "";
		}
		if (mymodel.start_date!="" && mymodel.start_date!="0000-00-00") {
			mymodel.start_date = "DOI:&nbsp;" + moment(mymodel.start_date).format("MM/DD/YYYY");
		}
		
		try {
			$(this.el).html(this.template(mymodel));		
		}
		catch(err) {
			var view = "lien_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
    },
	toggleLienEdit: function(event) {
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
		$(".lien_view .editing").toggleClass("hidden");
		$(".lien_view .span_class").removeClass("editing");
		$(".lien_view .input_class").removeClass("editing");
		
		$(".lien_view .span_class").toggleClass("hidden");
		$(".lien_view .input_class").toggleClass("hidden");
		$(".lien_view .input_holder").toggleClass("hidden");
		$(".button_row.lien").toggleClass("hidden");
		$(".edit_row.lien").toggleClass("hidden");
		$(".lien .token-input-list-facebook").toggleClass("hidden");
	},
	newAppearance: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		var mymodel = this.model.toJSON();
		
		var element_id = "-1_" + mymodel.case_id + "__" + mymodel.injury_id + "_appearance"; 
		composeEvent(element_id);
	},
	editLienField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".lien_view_" + field_name;
		}
		editField(element, master_class);
	},
	saveLien:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "lien", "lien");
		this.showNotes();			
		return;
    },
	resetLienForm: function(event) {
		event.preventDefault();
		this.toggleLienEdit(event);
		//this.render();
		//$("#address").hide();
	},
	doTimeouts: function() {
		var self = this;
		$(".lien .delete").show();
		var mymodel = this.model.toJSON();
		gridsterById('gridster_lien');
		
		$('#date_filedInput').datetimepicker({
				timepicker:false, 
				format:'m/d/Y',
				mask:false,
				onChangeDateTime:function(dp,$input){
					//alert($input.val());
			}
		});
		$('#date_paidInput').datetimepicker({
				timepicker:false, 
				format:'m/d/Y',
				mask:false,
				onChangeDateTime:function(dp,$input){
					//alert($input.val());
			}
		});
		var theme_3 = {
			theme: "facebook", 
			tokenLimit: 1,
			onAdd: function(item) {
				$("#occupationSpan").html(item.name);
				$("#worker_full_name").val(item.name);
				
			}
		};
		$("#workerInput").tokenInput("api/user", theme_3);
		if (self.model.get("worker")!="") {
			if (self.model.get("worker_full_name")==null) {
				self.model.set("worker_full_name", self.model.get("worker"));
			}
			$("#workerInput").tokenInput("add", {id: self.model.get("worker"), name: self.model.get("worker_full_name")});		
		}
		$(".lien .token-input-list-facebook").css("margin-left", "70px");
		$(".lien .token-input-list-facebook").css("margin-top", "-25px");
		$(".lien .token-input-list-facebook").css("width", "105px");
		$(".lien .token-input-dropdown-facebook").css("width", "150px");
		
		if(this.model.get("date_filed")=="" || this.model.get("date_filed")=="0000-00-00"){
			//editing mode right away
			this.model.set("editing", false);
			this.model.set("current_input", "");
			
			$(".lien .edit").trigger("click"); 
			$(".lien .reset").hide();
			
			if ($(".lien .token-input-list-facebook").hasClass("hidden")) {
				$(".lien .token-input-list-facebook").toggleClass("hidden");
			}
		} else {
			if (!$(".lien .token-input-list-facebook").hasClass("hidden")) {
				$(".lien .token-input-list-facebook").toggleClass("hidden");
			}
			this.showNotes();
			this.showAppearances();			
		}
		//indicate who got clicked
		this.showChosenInjury(mymodel.injury_id);
	},
	showChosenInjury: function(injury_id) {
		$(".injury_summaries").css("border-left", "");
		$("#injury_summary_" + injury_id).css("border-left", "2px solid white");
	},
	showNotes: function() {
		var self = this;
		//do we have any notes
		var injury_notes = new InjuryNotesByType([], {type: "lien", injury_id: self.model.get("injury_id")});
		injury_notes.fetch({
			success: function(data) {
				var note_list_model = new Backbone.Model;
				note_list_model.set("display", "sub");
				note_list_model.set("case_id", self.model.get("case_id"));
				note_list_model.set("partie_id", self.model.get("injury_id"));
				note_list_model.set("partie_type", "lien");
				$('#lien_notes').html(new note_listing_view({collection: data, model: note_list_model}).render().el);	
				$('#lien_notes').fadeIn(function() {
					$('#lien_notes').css("width", "50%");
				});			
			}
		});
	},
	showAppearances: function() {
		var self = this;
		//do we have any notes
		var lien_events = new InjuryAppearances({injury_id: self.model.get("injury_id")});
		lien_events.fetch({
			success: function(data) {
				var note_list_model = new Backbone.Model;
				note_list_model.set("display", "sub");
				note_list_model.set("case_id", self.model.get("case_id"));
				note_list_model.set("partie_id", self.model.get("injury_id"));
				note_list_model.set("title", "Appearances");
				$('#lien_events').html(new event_listing({collection: data, model: note_list_model}).render().el);	
				$('#lien_events').fadeIn(function() {
					$('#lien_events').css("width", "50%");
				});			
			}
		});
	}
});