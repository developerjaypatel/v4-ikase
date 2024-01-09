window.template_listing_view = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
    events:{
  		"click .save_icon":							"saveTemplate",
		"click #template_upload":					"templateUpload",
		"click .delete_icon":						"confirmdeleteTemplate",
		"click .delete_yes":						"deleteTemplate",
		"click .delete_no":							"canceldeleteTemplate",
		"change .document_input":					"releaseSave",
		"keyup .document_input":					"releaseSave",
		"click .send_icon":							"sendTemplate",
		"click .invoice_items":						"manageInvoiceItems",
		"change .filter_select":					"filterDocs",
		"click .create_letter":						"newLetter",
		"keyup #template_searchList":				"findIt",
		"click #template_clear_search":				"clearSearch",
		"click #label_search_template":				"Vivify",
		"click #template_searchList":				"Vivify",
		"focus #template_searchList":				"Vivify",
		"blur #template_searchList":				"unVivify",
		"click .propagate_link":					"propagateTemplate",
		"click #template_listing_all_done":			"doTimeouts"
	},
	unVivify: function(event) {
		var textbox = $("#template_searchList");
		var label = $("#label_search_template");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#template_searchList");
		var label = $("#label_search_template");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'template_listing', 'template', true);
	},

    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "template_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		this.collection.bind("reset", this.render, this);
		var case_id = this.model.get("case_id");
		var templates = this.collection.toJSON();
		var arrID = [];
		_.each( templates, function(template) {
			if (inArray(template.document_id, arrID)) {
            	blnSkip = true;
          	} else {
            	arrID[arrID.length] = template.document_id;
            	blnSkip = false;
            }
            
			template.skip_me = blnSkip;
			if (blnSkip) {
				self.collection.remove(template);
			}
		});
		var templates = this.collection.toJSON();
		_.each( templates, function(template) {
            if (template.document_names != null && template.document_names!="") {
				var arrDocuments = template.document_names.split("|");
				var arrDocumentDates = [];
				if (template.document_dates != null) {
					var arrDocumentDates = template.document_dates.split("|");
				}
				var arrDocumentIDs = [];
				if (template.document_uuids != null) {
					var arrDocumentIDs = template.document_uuids.split("|");
				}
				var arrDocumentUsers = [];
				if (template.document_users != null) {
					var arrDocumentUsers = template.document_users.split("|");
				} 
				var arrLinks = [];
				//_.each( arrDocuments, function(letter) {
				arrayLength = arrDocuments.length;
				for (var i = 0; i < arrayLength; i++) {
					var letter = arrDocuments[i];
					var document_date = moment(arrDocumentDates[i]).format("MM/DD/YYYY h:mA");
					var theuser = arrDocumentUsers[i];
					if (typeof theuser == "undefined") {
						theuser = "";
					} else {
						theuser = "&nbsp;(" + theuser + ")";
					}
					var arrLetter = letter.split("/");
					var letter_link = "<a href='" + letter + ".docx' title='Click to open letter' class='white_text' target='_blank'>" + document_date + "</a>" + theuser + "";
					//arrLetter[arrLetter.length - 1]
					arrLinks[i] = letter_link;
				}
				//put it all together
				template.document_names = arrLinks.join("<br>");
			} else {
				template.document_names = "";
			}
			template.delete_link = "";
			if (self.model.get("no_uploads")) {
				template.delete_link = "<a>del</a>";
			}
			
			template.propagate_link = "";
			if (customer_id == 1033) {
				template.propagate_link = "<input type='text' value='' placeholder='Enter Cus ID' id='propagate_" + template.id + "' />&nbsp;<a class='propagate_link white_text' id='propagate_link_" + template.id + "' style='cursor:pointer; text-decoration:underline' title='Click to propagate this template to other customers'>Propagate Template to Customer(s)</a>";
			}
		});
		
		//title
		var title = "Letter";
		if (this.model.get("blnInvoices")) {
			title = "Invoice";
		}
		try {
			$(this.el).html(this.template({templates: templates, case_id: case_id, title: title, blnInvoices: this.model.get("blnInvoices")}));
		}
		catch(err) {
			alert(err);
			/*
			var view = "template_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			*/
			return "";
		}
		
		tableSortIt("template_listing");
		
		if (this.model.get("no_uploads")==false) {
			setTimeout(function() {
				$('#upload_documents').html(new template_upload_view({model: self.model}).render().el);
			}, 400);
		} else {
			setTimeout(function(){
				$("#document_form_title").html("Choose from Templates");
			}, 400);
		}
		return this;
    },
	newLetter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeLetter(element.id);
	},
	filterDocs: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		filterIt(element, "template_listing", "kase_template");
	},
	clearSearch: function() {
		$("#template_searchList").val("");
		$( "#template_searchList" ).trigger( "keyup" );
	},
	templateUpload: function(event) {
		template.location = "#upload/" + this.model.document_id + "/kase";
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
	},
	propagateTemplate: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var destination_cus_id = $("#propagate_" + theid).val();
		
		//either a number or all
		if (isNaN(destination_cus_id)) {	
			if (destination_cus_id.toLowerCase()=="all") {
				//continue
			} else {
				alert("Enter either a number or the word ALL");
				return;
			}
		}
		
		var formValues = { 
			document_id: theid,
			destination_cus_id: destination_cus_id
		};
		var url = "api/templates/propagate";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					//mark it all green
					var back_color = $(".template_row_" + theid).css("background");
					$(".template_row_" + theid).css("background", "green");
					//var theid = data.id;
					setTimeout(function() {
						$(".template_row_" + theid).css("background", back_color);
					}, 2500);
				}
			}
		});
	},
	manageInvoiceItems: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		composeInvoice(current_input.id);
	},
	sendTemplate: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		//var arrID = current_input.id.split("_");
		//var theid = arrID[arrID.length - 1];
		
		composeMessage(current_input.id);
	},
	saveTemplate: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var name = $("#document_name_" + theid).val();
		var category = $("#document_category_" + theid).val();
		var description = $("#document_description_" + theid).val();
		var description_html = $("#description_html_" + theid).val();
		var element_name = "source_" + theid;
		var source = "";
		if ($("#letterhead_yes_" + theid).prop("checked")) {
			source = "Y";
		}
		if ($("#letterhead_no_" + theid).prop("checked")) {
			source = "no_letterhead";
		}
		if ($("#letterhead_client_" + theid).prop("checked")) {
			source = "clientname_letterhead";
		}
		var document_id = $("#document_id_" + theid).val();
		var formValues = { 
			document_id: document_id,
			document_name: name, 
			type: "template", 
			document_extension: category, 
			description: description,
			description_html: description_html,
			source: source
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
					//$("#template_save_" + theid).hide();
					$("#document_save_" + theid).fadeOut(function() {
						$("#disabled_save_" + theid).fadeIn();
					});
					//get the color
					var back_color = $(".template_row_" + theid).css("background");
					//mark it all green
					$(".template_row_" + theid).css("background", "green");
					setTimeout(function() {
						//hide the processed row, no longer a batch scan
						//$(".template_row_" + theid).fadeOut();
						$("#thumbnail_" + theid).html(name);
						$(".template_row_" + theid).css("background", back_color);
					}, 2500);
				}
			}
		});
	},
	confirmdeleteTemplate: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element);	
		$("#confirm_delete").css({display: "none", top: arrPosition[0] - 50, left: arrPosition[1] + 50, position:'absolute'});
		$("#confirm_delete").fadeIn();
		
	},
	canceldeleteTemplate: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteTemplate: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		//the "letter" means to remove completely from hard disk, per Terriel 10/23/2015
		var blnDeleted = deleteElement(event, id, "document", "letter");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTemplate(event);
			$(".template_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				//$(".template_row_" + theid).fadeOut();
				$(".template_row_" + id).fadeOut();
			}, 2500);
		}
	},
	doTimeouts: function() {
		//<input id="kinvoice_id_<%=template.document_id%>" name="kinvoice_id_<%=template.document_id%>" type="hidden" class="document_input" value="<%=template.kinvoice_id%>" />
		var self = this;
		
		var templates = this.collection.toJSON();
		_.each( templates, function(template) {
			var document_id = template.document_id;
			
			//get all the invoices created with this template
			var kinvoices = new TemplateKInvoices({document_id: document_id});
			kinvoices.fetch({
				success: function(data) {
					if (data.length > 0) {
						var jdata = data.toJSON();
						var arrButtons = [];
						_.each( jdata, function(datum) {
							var template_name = datum.template_name;
							//show button
							var button = '<div style="margin-bottom:5px"><input id="kinvoice_id_' + datum.kinvoice_id + '" name="kinvoice_id_' + datum.kinvoice_id + '" type="hidden" class="kinvoice_id_input document_input" value="' + datum.kinvoice_id + '" />';
							button += '<button class="invoice_items btn btn-xs btn-success" id="invoice_items_' + document_id + '_' + datum.kinvoice_id + '">' + template_name + '</button></div>';
							arrButtons.push(button);
						});
						$("#template_invoices_holder_" + document_id).html(arrButtons.join(""));
					}
				}
			});
		});
	}
});