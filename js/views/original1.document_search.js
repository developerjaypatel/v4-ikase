window.document_listing_search = Backbone.View.extend({
	render: function(){
		$(this.el).html(this.template({kustomer_documents:  this.collection.toJSON()}));
		
		return this;
	},
	events: {
		"click .save_icon":							"saveDocument",
		"change .document_input":					"releaseSave",
		"keyup .document_input":					"releaseSave",
		"change .filter_select":					"filterDocs",
		"click #document_list_search_done":			"doTimeouts"
	},
	doTimeouts: function(event) {
		$('#ikase_loading').html('');
		$("#document_list_search").show();
	},
	saveDocument: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var name = $("#document_name_" + theid).val();
		var source = $("#document_source_" + theid).val();
		var received_date = $("#document_received_" + theid).val();
		
		//show the two drop downs and the check box
		var type = $("#document_type_" + theid).val();
		var category = $("#document_category_" + theid).val();
		var subcategory = $("#document_subcategory_" + theid).val();
		var note = $("#document_note_" + theid).val();
		var document_id = $("#document_id_" + theid).val();
		var formValues = { 
			document_id: document_id,
			document_name: name, 
			source: source, 
			received_date: received_date, 
			type: type, 
			document_extension: category, 
			description: subcategory, 
			description_html: note
		};
		var url = "api/documents/categorize";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					//hide the save button
					//$("#document_save_" + theid).hide();
					$("#document_save_" + theid).fadeOut(function() {
						$("#disabled_save_" + theid).fadeIn();
					});
					//get the color
					var back_color = $(".kase_document_row_" + theid).css("background");
					//mark it all green
					$(".kase_document_row_" + theid).css("background", "green");
					setTimeout(function() {
						//hide the processed row, no longer a batch scan
						//$(".document_row_" + theid).fadeOut();
						$(".kase_document_row_" + theid).css("background", back_color);
					}, 2500);
				}
			}
		});
	},
	filterDocs: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		filterIt(element, "document_listing", "kustomer_document");
	},
	releaseSave: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		$("#disabled_save_" + theid).fadeOut(function() {
			$("#document_save_" + theid).fadeIn();
		});
	}
}),
window.document_search = Backbone.View.extend({
	render: function(){
		var self = this;
		try {
			$(this.el).html(this.template());
		}
		catch(err) {
			var view = "document_search";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
	},
	events: {
		"click #document_search_button":		"searchDocuments",
		"click #close_search": 					"closeSearch",
		"click #document_search_done":			"doTimeouts"
	},
	doTimeouts: function(event) {
		/*
		$("#document_start_date").datetimepicker({ validateOnBlur:false, allowTimes:workingWeekTimes, maxDate:'0', timepicker:false, format:'m/d/Y'});
		$("#document_end_date").datetimepicker({ validateOnBlur:false, allowTimes:workingWeekTimes, maxDate:'0', timepicker:false, format:'m/d/Y'});
		*/
		
		$("#myModal4").modal("toggle");
	},
	searchDocuments: function(event) {
		event.preventDefault();
		var self = this;
		//get search variables
		var document_name = $(".document_search #document_name").val();
		if (document_name=="") { 
			document_name = "~"; 
		};
		document_name = document_name.replaceAll(" ", "_");
		var document_start_date = $(".document_search #document_start_date").val();
		if (document_start_date=="") { 
			document_start_date = "~"; 
		} else {
			document_start_date = moment(document_start_date).format("YYYY-MM-DD")
		}
		var document_end_date = $(".document_search #document_end_date").val();
		if (document_end_date=="") { 
			document_end_date = "~"; 
		} else {
			document_end_date = moment(document_end_date).format("YYYY-MM-DD")
		}
		if (document_end_date=="") { 
			document_end_date = "~"; 
		};
		var document_type = $(".document_search #document_type").val();
		if (document_type=="") { document_type = "~"; };
		
		//must search for something
		var document_search = document_name + document_type + document_start_date + document_end_date;
		document_search = document_search.trim();
		if (document_search == "~~~~") {
			alert("You must enter a search term");
			return;
		}
		var customer_documents = new DocumentSearchCollection([], { document_name: document_name, document_start_date: document_start_date, document_end_date: document_end_date, document_type: document_type });
		
		$('#ikase_loading').html('<div id="document_listing_loading" style="display:"><i class="icon-spin4 animate-spin" style="font-size:6em; color:white"></i></div>');
		customer_documents.fetch({
			success: function(data) {
				self.closeSearch();
				Backbone.history.navigate('founddocuments/');
				$('#content').html(new document_listing_search({collection: data}).render().el);
			}
		});
	},
	closeSearch: function(event) {
		$("#myModal4").html("");
		$("#myModal4").modal("toggle");
	}
});