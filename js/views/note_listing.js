window.note_listing_view = Backbone.View.extend({

    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
	events: {
		"click .compose_new_note": "newNotes",
		"click .open_note":		"editNote",
		"keyup #document_searchList":				"findIt",
		"click #document_clear_search":				"clearSearch"
	},
    render:function () {		
		var self = this;
		
		this.collection.bind("reset", this.render, this);
		var notes = this.collection.toJSON();
		var arrTypes = [];
		_.each( notes, function(note) {
			if (moment(note.dateandtime).format('h:mm a')=="12:00 am") {
				note.time = "";
			} else {
				note.time = moment(note.dateandtime).format('h:mm a');
			}
			note.date = moment(note.dateandtime).format("dddd, MMMM Do YYYY");
			var note_filter_options = "";
			if (note.attribute!="" && note.attribute!="main") {
				if (arrTypes.indexOf(note.attribute) < 0) {
					arrTypes[arrTypes.length] = note.attribute;
					note_filter_options +='\r\n<option value="' + note.attribute + '">' + note.attribute.capitalize() + ' Note</option>';
				}
			}
		});
		
		$(this.el).html(this.template({notes: notes, case_id: this.collection.case_id, display_mode: this.model.get("display"), note_filter_options: note_filter_options}));
		
		tableSortIt("note_listing");
		
		return this;
    },
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'note_listing', 'note');
	},
	clearSearch: function() {
		$("#note_searchList").val("");
		$( "#note_searchList" ).trigger( "keyup" );
		
		$(".filter_select").val();
		$( "#typeFilter" ).trigger( "click" );
	},
	editNote: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	}
});
window.note_listing_pane = Backbone.View.extend({

    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
	events: {
		"click .compose_new_note": "newNotes",
		"click .open_note":		"editNote",
		"keyup #document_searchList":				"findIt",
		"click #document_clear_search":				"clearSearch"
	},
    render:function () {		
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "note_listing_pane";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		this.collection.bind("reset", this.render, this);
		var notes = this.collection.toJSON();
		var arrTypes = [];
		_.each( notes, function(note) {
			if (moment(note.dateandtime).format('h:mm a')=="12:00 am") {
				note.time = "";
			} else {
				note.time = moment(note.dateandtime).format('h:mm a');
			}
			note.date = moment(note.dateandtime).format("dddd, MMMM Do YYYY");
			var note_filter_options = "";
			if (note.attribute!="" && note.attribute!="main") {
				if (arrTypes.indexOf(note.attribute) < 0) {
					arrTypes[arrTypes.length] = note.attribute;
					note_filter_options +='\r\n<option value="' + note.attribute + '">' + note.attribute.capitalize() + ' Note</option>';
				}
			}
		});
		
		$(this.el).html(this.template({notes: notes, case_id: this.collection.case_id, display_mode: this.model.get("display"), note_filter_options: note_filter_options}));
		
		tableSortIt("note_listing");
		
		return this;
    },
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'note_listing', 'note');
	},
	clearSearch: function() {
		$("#note_searchList").val("");
		$( "#note_searchList" ).trigger( "keyup" );
		
		$(".filter_select").val();
		$( "#typeFilter" ).trigger( "click" );
	},
	editNote: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	}
});
