window.kinvoice_items_listing = Backbone.View.extend({

    initialize:function () {
	},
    events: {
		"click #new_kinvoice_items_button":				"newInvoiceItem",
		"click #save_kinvoice_item":					"saveInvoiceItem",
		"click .invoice_item_checkbox":					"activateInvoiceItem",
		"click .invoice_item_cell":						"editKInvoiceItem",
		"click .invoice_item_save":						"updateInvoiceItem",
		"keyup .required":								"restoreRequired",
		"click .save_kinvoice":							"saveKInvoiceHeader",
		"click #new_kinvoice_cost":						"showCostFields",
		"click #kinvoice_items_listing_all_done":		"doTimeouts"
	},
	render:function () {	
		if (typeof this.template != "function") {
			var view = "kinvoice_items_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}	
		var self = this;
		
		//are we returning from a saved
		if (typeof this.model.get("saved")=="undefined") {
			this.model.set("saved", "");
		}
		var arrItems = this.collection.toJSON();
		var arrRows = [];
		_.each(arrItems , function(invoice_item) {
			if (typeof invoice_item.id != "undefined") {
				var index = invoice_item.id
				var checked = " checked";
				var exact_checked = " checked";
				var exact_checked_display = "Y";
				var row_display = "color:black";
				var row_class = "active_filter";
				
				var costchecked = "checked";
				var costrow_display = "";
				if (invoice_item.amount == 0) {
					costchecked = "";
					costrow_display = "display:none";
				}
				if (invoice_item.deleted=="Y") {
					checked = "";
					row_display = "color:red; text-decoration:line-through";
					row_class = "deleted_item";
				}
				if (invoice_item.exact=="N") {
					exact_checked = "";
					exact_checked_display = "N";
				}
				var exact_check = "<input type='checkbox' id='invoice_exact_" + index + "' value='Y' class='hidden'" + exact_checked + " /><span id='invoice_exact_span_" + index + "'>" + exact_checked_display + "</span>";
				
				
				var input = "<button class='btn btn-xs btn-success invoice_item_save hidden' id='invoice_item_save_" + index + "'>Save</button>";
				var therow = "<tr class='" + row_class + "'><td class='invoice_item' align='left' valign='top'><input type='checkbox' class='invoice_item_checkbox hidden' value='Y' title='Uncheck to stop using this item.  Old invoices currently using this item will not be affected' id='invoice_item_" + index + "' name='invoice_item_" + index + "'" + checked + "></td><td class='invoice_item' style='" + row_display + "' align='left' valign='top'><span id='invoice_item_span_" + index + "' class='invoice_item_cell white_text' style='cursor:pointer; text-decoration:underline' title='Click to edit this item'>" + invoice_item.item_name + "</span><input class='hidden' type='text' id='invoice_item_value_" + index + "' value='" + invoice_item.item_name + "' /></td><td align='left' valign='top'><span id='invoice_item_description_span_" + index + "' class='invoice_item_cell white_text'>"  + invoice_item.item_description + "</span><textarea class='hidden' id='invoice_item_description_" + index + "' style='width:257px'>"  + invoice_item.item_description + "</textarea></td><td align='left' valign='top'>" + exact_check + "</td><td align='right' valign='top'>" + input + "</td></tr>";
				arrRows.push(therow);
				
				costrow = "<tr id='costrow_" + index + "' style='" + costrow_display + "'><td>&nbsp;</td><td align='left' valign='top'><div id='costinput_holder_" + index + "' class='hidden'><input type='number' id='kinvoice_rate_" + index + "' placeholder='Rate' class='required' autocomplete='off' tabindex='2' style='width:55px' value='" + invoice_item.amount + "' />$&nbsp;per&nbsp;<input type='text' id='kinvoice_rateunit_" + index + "' placeholder='Unit' class='required' autocomplete='off' tabindex='3' style='width:148px' value='" + invoice_item.unit + "' /></div><div id='costspan_holder_" + index + "'>$" + invoice_item.amount + " per " + invoice_item.unit + "</div></td></tr>";
				arrRows.push(costrow);
				/*
				//are we using a deleted status
				var current_status = self.model.get("case_status");
				var blnUsingDeleted = (arrDeletedInvoiceItem.indexOf(current_status) > -1);
				
				if (invoice_item.deleted!="Y" || blnUsingDeleted) {
					//the drop down has to match
					var option_selected = "";
					if (invoice_item.status == current_status) {
						option_selected = " selected";
					}
					var option = '<option value="' + invoice_item.status + '" class="wcab_status_option"' + option_selected + '>' + invoice_item.status + '</option>';
					arrOptions.push(option);
				}
				*/
			}
		});
		var html = "";
		var labels = "<tr><th align='left'>&nbsp;</th><th align='left'>Item</th><th align='left'>Description</th><th align='left' title='Hours or Hours'>Amount&nbsp;Only</th><th align='left'>&nbsp;</th></tr>";
		if (arrRows.length > 0) {
			html = "<table id='invoice_item_table' width='100%'>" + labels + arrRows.join("") + "</table>";
		}
		
		try {
			$(self.el).html(self.template(
				{
					"items": this.collection.toJSON(), 
					"html": html, 
					"kinvoice_id": this.model.get("kinvoice_id"), 
					"hourly_rate": this.model.get("hourly_rate"), 
					"template_name": this.model.get("template_name"), 
					"document_id": this.model.get("document_id")
				}
			));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		return this;
	},
	restoreRequired:function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;
		
		$("#" + element_id).css("border", "1px inset rgb(0, 0, 0)");
		
		this.releaseItems();
		
		//are we updating
		var kinvoice_id = $("#kinvoice_id").val();
		if (kinvoice_id != "" && kinvoice_id != "0") {
			var fieldname = "";
			if (element_id=="hourly_rate") {
				fieldname = "rate";
			}
			if (element_id=="template_name") {
				fieldname = "template";
			}
			if (fieldname != "") {
				$("#save_kinvoice_" + fieldname).show();
			}
		}
	},
	editKInvoiceItem:function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var item_id = arrID[arrID.length - 1];
		
		//not open nor closed
		var current = $("#invoice_item_value_" + item_id).val();
		if (current=="Open" || current=="Closed") {
			return;
		}
		$("#invoice_item_span_" + item_id).fadeOut();
		$("#invoice_exact_span_" + item_id).fadeOut();
		$("#invoice_item_value_" + item_id).toggleClass("hidden");
		$("#invoice_item_save_" + item_id).toggleClass("hidden");
		$("#invoice_item_" + item_id).toggleClass("hidden");
		
		$("#invoice_item_description_span_" + item_id).toggleClass("hidden");
		$("#invoice_item_description_" + item_id).toggleClass("hidden");
		$("#invoice_exact_" + item_id).toggleClass("hidden");
		$("#costinput_holder_" + item_id).toggleClass("hidden");
		$("#costspan_holder_" + item_id).toggleClass("hidden");
		
		$("#costrow_" + item_id).css("display", "");
	},
	newInvoiceItem:function(event) {
		event.preventDefault();
		$("#new_kinvoice_items_button").fadeOut();
		$("#invoice_item_table").fadeOut();
		$("#new_kinvoice_items_holder").fadeIn(function() {
			$("#new_kinvoice_item").focus();
		});
	},
	showCostFields: function(event) {
		var element = event.currentTarget;
		if (document.getElementById(element.id).checked) {
			$("#kinvoice_cost_holder").fadeIn();
		} else {
			$("#kinvoice_cost_holder").fadeOut();
			$("#new_kinvoice_rate").val("");
			$("#new_kinvoice_rateunit").val("");
		}
	},
	saveKInvoiceHeader: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var fieldname = arrID[arrID.length - 1];
		
		$("#save_kinvoice_" + fieldname).hide();
		
		if (fieldname=="rate") {
			fieldname = "hourly_rate";
		}
		if (fieldname=="template") {
			fieldname = "template_name";
		}
		var value = $("#" + fieldname).val();
		var kinvoice_id = $("#kinvoice_id").val();
		var url = "api/kinvoice/update";
		var formValues = "kinvoice_id=" + kinvoice_id + "&fieldname=" + fieldname;
		formValues += "&value=" + value;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#label_" + fieldname).css("color", "lime");
					setTimeout(function() {
						$("#label_" + fieldname).css("color", "white");
					}, 1500);
				}
			}
		});
	},
	saveInvoiceItem:function(event) {
		var self = this;
		event.preventDefault();
		var url = 'api/kinvoiceitem/add';
			
		var kinvoice_id = $("#kinvoice_id").val();
		var template_name = $("#template_name").val();
		if (template_name == "") {
			$("#template_name").css("border", "2px solid red");
			return;
		}
		this.model.set("template_name", template_name)
		var hourly_rate = $("#hourly_rate").val();
		if (isNaN(hourly_rate)) {
			hourly_rate = 0;
		}
		if (hourly_rate < 1) {
			$("#hourly_rate").css("border", "2px solid red");
			return;
		}
		this.model.set("hourly_rate", hourly_rate)
		var item_name = $("#new_kinvoice_item").val();
		if (item_name=="") {
			$("#new_kinvoice_item").css("border", "2px solid red");
			return;
		}
		
		//cost
		var amount = 0;
		var rateunit = "";
		var costbox = document.getElementById("new_kinvoice_cost");
		if (costbox.checked) {
			amount = document.getElementById("new_kinvoice_rate").value;
			rateunit = document.getElementById("new_kinvoice_rateunit").value;
			
			if (amount==0) {
				$("#new_kinvoice_rate").css("border", "2px solid red");
				return;
			}
			if (rateunit=="") {
				$("#new_kinvoice_rateunit").css("border", "2px solid red");
				return;
			}
		}
		
		var checkbox = document.getElementById("new_kinvoice_exact");
		var exact = "N";
		if (checkbox.checked) {
			exact = "Y";
		}
		
		var item_description = $("#new_kinvoice_description").val();
		var document_id = $("#document_id").val();
		
		var formValues = "item_name=" + encodeURIComponent(item_name);
		formValues += "&exact=" + exact;
		formValues += "&amount=" + amount;
		formValues += "&unit=" + rateunit;
		formValues += "&kinvoice_id=" + kinvoice_id;
		formValues += "&hourly_rate=" + hourly_rate;
		formValues += "&template_name=" + encodeURIComponent(template_name);
		formValues += "&document_id=" + document_id;
		formValues += "&item_description=" + encodeURIComponent(item_description);
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//save the kinvoice_id
					$("#kinvoice_id_" + document_id).val(data.kinvoice_id);
					self.model.set("kinvoice_id", data.kinvoice_id);
					self.model.set("saved", data.kinvoiceitem_id);
					var kinvoice_items = new KInvoiceItemsCollection({"kinvoice_id": data.kinvoice_id});	//, "document_id": document_id
					kinvoice_items.fetch({
						success: function (data) {			
							self.collection = data;
							self.render();
						}
					});
				}
			}
		});
		//
	},
	activateInvoiceItem: function(event) {
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var item_id = arrID[arrID.length - 1];
		
		$("#invoice_item_save_" + item_id).trigger("click");
	},
	updateInvoiceItem:function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var item_id = arrID[arrID.length - 1];
		var checkbox = document.getElementById("invoice_item_" + item_id);
		var deleted = "Y";
		if (checkbox.checked) {
			deleted = "N";
		}
		var amount = document.getElementById("kinvoice_rate_" + item_id).value;
		var rateunit = document.getElementById("kinvoice_rateunit_" + item_id).value;
		
		if ($("#costrow_" + item_id).css("display")!="none") {
			if (amount==0) {
				$("#kinvoice_rate_" + item_id).css("border", "2px solid red");
				return;
			}
			if (rateunit=="") {
				$("#kinvoice_rateunit_" + item_id).css("border", "2px solid red");
				return;
			}
		} else {
			amount = 0;
			rateunit = "";
		}
		var checkbox = document.getElementById("invoice_exact_" + item_id);
		var exact = "N";
		if (checkbox.checked) {
			exact = "Y";
		}
		var item_name = $("#invoice_item_value_" + item_id).val();
		var item_description = $("#invoice_item_description_" + item_id).val();
		var mymodel = this.model.toJSON();
		
		var url = 'api/kinvoiceitem/update';
		var formValues = "item_id=" + item_id + "&item_name=" + item_name + "&deleted=" + deleted + "&exact=" + exact;
		formValues += "&item_description=" + encodeURIComponent(item_description);
		formValues += "&amount=" + amount;
		formValues += "&unit=" + rateunit;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//get the kinvoice_id
					var kinvoice_id = $("#kinvoice_id").val();
					var kinvoice_items = new KInvoiceItemsCollection({"kinvoice_id": kinvoice_id});
					kinvoice_items.fetch({
						success: function (data) {			
							self.collection = data;
							self.model.set("saved", item_id);
							self.render();
						}
					});
				}
			}
		});
		//
	},
	doTimeouts: function() {
		var item_id = this.model.get("saved");
		if (item_id!="") {
			$("#invoice_item_span_" + item_id).css("color", "lime");
			setTimeout(function() {
				$("#invoice_item_span_" + item_id).css("color", "white");
			}, 2500);
		}
		this.model.set("saved", "");
		$(".modal-header .close").css("color", "white");
		
		$(".modal-header .close").on("click", function(event) { 
			window.Router.prototype.listInvoiceTemplates();
		});
		this.releaseItems();
	},
	releaseItems: function() {
		var template_name = $("#template_name").val();
		var hourly_rate = $("#hourly_rate").val();
		if (template_name != "" && hourly_rate > 0) {
			$("#kinvoiceitems_holder").fadeIn();
		}
	}
});
window.kinvoice_listing_view = Backbone.View.extend({
	initialize:function () {
    },
	events: {
		"click #new_kinvoice":						"newKInvoice",
		//"click #invoice_edit":					"editKInvoice",
		"click .edit_invoice_full":					"editKInvoiceFull",
		"click .delete_invoice": 					"confirmdeleteKInvoice",
		"click .review_transactions":				"reviewTransactions",
		"click .change_invoice":					"changeInvoiceType",
		"click .pay_invoice_full":					"newInvoicePayment",
		"click .transfer_invoice":					"transferFunds",
		"click .compose_invoice":					"newMessage",
		"click .compose_pdf_envelope":				"newPDFEnvelope",
		"click #kase_billables":					"reviewBillables",
		"click #kinvoice_listing_view_done":		"doTimeouts"
	},
	render: function(){
		if (typeof this.template != "function") {
			var view = "kinvoice_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		var kinvoices = this.collection.toJSON();
		var mymodel = this.model.toJSON();
		mymodel.div_witdh = "";
		if (typeof mymodel.blnAllInvoices == "undefined") {
			mymodel.blnAllInvoices = false;
		}
		if (mymodel.blnAllInvoices) {
			mymodel.div_witdh = "width:90vw";
		}
		$(this.el).html(this.template({
			kinvoices: kinvoices, 
			"page_title": mymodel.page_title, 
			"div_witdh": mymodel.div_witdh, 
			blnDocumentInvoices: mymodel.document_invoices,
			blnAllInvoices: mymodel.blnAllInvoices
		}));
		
		return this;
	},
	doTimeouts: function() {
		var self = this;
		if (this.model.get("document_invoices")) {
			return;
		}
		var kinvoices = this.collection.toJSON();
		_.each( kinvoices, function(kinvoice) {
			
			var kase_checks = new ChecksCollection([], { case_id: "", kinvoice_id: kinvoice.kinvoice_id, kinvoice_number: kinvoice.kinvoice_number});
			kase_checks.fetch({
				success: function(data) {
					if (data.length > 0) {
						var datum = data.toJSON()[0];
						var newkase = new Backbone.Model();
						var case_id = datum.case_id;
						var kinvoice_id = datum.kinvoice_id;
						var kinvoice_number = datum.kinvoice_number;
						
						newkase.set("case_id", case_id);
						newkase.set("holder", "payments_cell_" + kinvoice_id);
						newkase.set("kinvoice_id", kinvoice_id);
						newkase.set("kinvoice_number", kinvoice_number);
						newkase.set("page_title", "Payment");
						newkase.set("embedded", true);
						$("#payments_cell_" + kinvoice_id).html(new check_listing_view({collection: data, model: newkase}).render().el);
					}
				}
			});
		});
		$(".kinvoice_listing th").css("font-size", "1em");
		$(".kinvoice_listing").css("font-size", "1.1em");
	},
	newPDFEnvelope: function(event) {
		var element = event.currentTarget;
		//return;
		event.preventDefault();
		generateEnvelope(element.id, "pdf");
		
		//add the notification_date
		var value = moment().format("YYYY-MM-DD hh:mm:ss");
		var display_value = moment().format("MM/DD/YYYY");
		var kinvoice_id = element.classList[1].split("_")[1];
		var case_id = element.classList[1].split("_")[2];
		var invoice_number = $("#kinvoice_number_" + kinvoice_id).html();
		
		var url = "api/kinvoice/update";
		var formValues = "kinvoice_id=" + kinvoice_id + "&fieldname=notification_date";
		formValues += "&value=" + value;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					var parent = element.parentElement;
					if (element.parentElement.innerHTML.indexOf("Sent on")==-1) {
						element.parentElement.innerHTML += '<span style="background:lime; padding:2px; color:black" title="Sent on ' + display_value + '">Sent&nbsp;&#10003;</span>';
					}
				}
			}
		});
		
		url = "../api/activity/insert_activity";
		var activityVal = $("#activityInput").val();
		var formValues = "activity=Invoice " + invoice_number + "Mailed&case_id=" + this_case_id;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data:formValues,
			success:function (data) {
				blnSaving = false;
					if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					console.log("invoice activity");
				}
			}
		});
	},
	newKInvoice: function(event) {
		event.preventDefault();
		composeInvoiceAssign(this.model.get("case_id"));
		return;
		/*
		if (document.location.hash.indexOf("#payments")==0) {
			document.location.href = "#lettersinv/" + this.model.get("case_id");
		} else {
			composeInvoiceAssign();
		}
		*/
	},
	reviewBillables: function(event) {
		event.preventDefault();
		var case_id = current_case_id;
		
		document.location.href = "#kasebillables/" + case_id;
	},
	reviewTransactions: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var kinvoice_id = element.id.split("_")[2];
		
		$("#payments_row_" + kinvoice_id).fadeIn();
		$("#" + element.id).fadeOut();
	},
	confirmdeleteKInvoice: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		if (element.id=="") {
			element = element.parentElement;
		}
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		//alert(id);
		//return;
		
		composeDelete(id, "kinvoice");
	},
	canceldeleteKInvoice: function(event) {
		event.preventDefault();
		$("#confirm_delete_kinvoice").fadeOut();
	},
	deleteKInvoice: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "kinvoice");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteKInvoice(event);
			$(".kinvoice_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".kinvoice_row_" + id).fadeOut();
			}, 2500);
		}
	},
	editKInvoice: function(event){
		var element = event.currentTarget;
		event.preventDefault();
		
		var arrClasses = element.className.split("_");
		
		var kinvoice_id = arrClasses[2];
		
		//alert(arrClasses);
		//return;
		
		var kase = kases.findWhere({case_id: current_case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: current_case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.remove(kase.id); kases.add(kase);
						self.kaseActivity(current_case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		
	},
	editKInvoiceFull: function(event){
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var arrClasses = element_id.split("_");
		
		var kinvoice_id = arrClasses[1];
		/*
		//get the case from the link
		var invoice_number = $("#" + element_id).html();
		var arrInv = invoice_number.split("-");
		var case_id = arrInv[0];
		*/
		//get a kinvoice object, pass the info to the composeLetter function
		var kinvoice = new KInvoice({id: kinvoice_id});
		kinvoice.fetch({
			success: function (data) {
				var jdata = data.toJSON();
				//then compose letter, with kinvoice id in there somewhere
				var element_id = "edit_letter_" + jdata.case_id + "_" + jdata.parent_id + "_" + kinvoice_id;
				composeLetter(element_id, jdata);
			}
		});
	},
	changeInvoiceType: function(event) {
		var self = this;
		
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var kinvoice_id = element_id.split("_")[1];
		var hourly_rate = $("#hourly_rate_" + kinvoice_id).val();
		var case_id = $("#case_id_" + kinvoice_id).val();
			
		if (hourly_rate==-1) {
			//it's a activity invoice, go change it in billing
			window.Router.prototype.kaseBilling(case_id, kinvoice_id, true);
			window.history.replaceState(null, null, "#billing/" + case_id + "/" + kinvoice_id);
			app.navigate("billing/" + case_id + "/" + kinvoice_id, {trigger: false});
			
			//document.location.href = "#billing/" + case_id + "/" + kinvoice_id;
			return;
		} else {
			//it's a regular invoice, just change the type
			$("#" + element_id).fadeOut();
			$("#prebill_" + kinvoice_id).html("Saving...");
			var url = "api/kinvoice/update";
			var formValues = "kinvoice_id=" + kinvoice_id;
			formValues += "&fieldname=kinvoice_type";
			formValues += "&value=I";
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						$("#prebill_" + kinvoice_id).html("Saved&nbsp;&#10003;");
						$("#prebill_" + kinvoice_id).css("color", "lime");
						setTimeout(function() {
							//refresh render
							var case_invoices = new KInvoiceCollection({"case_id": case_id});
							case_invoices.fetch({
								success: function (data) {
									self.collection = data;
									self.render();
								}
							});
						}, 1500);
					}
				}
			});
		}
	},
	transferFunds: function(event) {
		var self = this;
		
		var element = event.currentTarget;
		event.preventDefault();
		
		if (!confirm("Press OK to confirm this Transfer from Trust")) {
			return;
		}
		var element_id = element.id;
		var arrID = element_id.split("_");
		var kinvoice_id = arrID[arrID.length - 1];
		var case_id = $("#case_id_" + kinvoice_id).val();
		var balance = $("#balance_" + kinvoice_id).val();
		
		var url = "api/kinvoice/transfer";
		var formValues = "kinvoice_id=" + kinvoice_id + "&case_id=" + case_id + "&invoice_total=" + balance;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					document.getElementById("transfer_" + kinvoice_id).outerHTML = "<span style='background:lime; color:black; padding:2px' id='feedback_" + kinvoice_id + "'>Transferred&nbsp;&#10003;</span>";
					
					var invoice_total = balance;
					var account_id = data.account_id;
					var invoice_number = $("#kinvoice_number_" + kinvoice_id).html().trim();
					
					//add a disbursement against the account
					var d = new Date();
					var check_number = "TRNSFR:" + moment(d).format("YYMMDDHHmmss");
					var check_date = moment(d).format("YYYY-MM-DD");
					var transaction_date = moment().format("YYYY-MM-DD");
					var account_type = "trust";
					
					var url = "api/check/add";
					var formValues = "table_name=check&table_id=-1&case_id=" + case_id + "&account_id=" + account_id;
					formValues += "&fee_id=&recipient=&kinvoice_id=" + kinvoice_id + "&invoice_number=" + invoice_number + "&ledger=OUT";
					formValues += "&transaction_date=" + transaction_date + "&payment=" + invoice_total;
					formValues += "&check_type=" + account_type.capitalize() + "+Withdrawal&method=transfer";
					formValues += "&amount_due=0&check_number=" + check_number;
					formValues += "&balance=0&check_date=" + check_date;
					formValues += "&memo=Invoice Transfer for " + invoice_number + "&send_document_id=";
					
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data:formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							} else {
								var kase_disbursments = new ChecksCollection([], { case_id: case_id, ledger: "OUT" });
								kase_disbursments.fetch({
									success: function(data) {
										//refresh the checks
										var newkase = self.model.clone();
										newkase.set("holder", "#disbursement_holder");
										newkase.set("page_title", "Disbursement");
										newkase.set("blnShowMemo", false);
										$('#disbursement_holder').html(new check_listing_view({collection: data, model: newkase}).render().el);
										
										//refresh the selection
										var acctmodel = new Backbone.Model({
											"holder": "accounts_holder",
											"case_id": case_id,
											"page_title":"Accounts"
										});
										$("#accounts_holder").html(new account_selection_view({model: acctmodel}).render().el);
									}
								});
							}
						}
					});

					setTimeout(function() {
						//refresh render
						var case_invoices = new KInvoiceCollection({"case_id": case_id});
						case_invoices.fetch({
							success: function (data) {
								self.collection = data;
								self.render();
							}
						});
					}, 2500);
				}
			}
		});
	},
	newInvoicePayment: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var total_id = element_id.replace("payment_", "kinvoicetotal_");
		var total = $("#" + total_id).html();
		total = total.replaceAll(",", "");
		
		var kinv_id = element_id.replace("payment_", "kinvoice_id_");
		var kinvoice_id = $("#" + kinv_id).val();

		var payments_id = element_id.replace("payment_", "kinvoicepayments_");
		var payments = $("#" + payments_id).html();
		payments = payments.replaceAll(",", "");
		
		var invoice_number_id = element_id.replace("payment_", "kinvoice_number_");
		var invoice_number = $("#" + invoice_number_id).html();
		var arrInvoiceNumber = invoice_number.split("-");
		var case_id = arrInvoiceNumber[0];
		
		var company_id = element_id.replace("payment_", "corporation_");
		var corporation = $("#" + company_id).html();
		
		var corporationtype_id = element_id.replace("payment_", "corporationtype_");
		var corporationtype = $("#" + corporationtype_id).val();
		
		var corp_id = element_id.replace("payment_", "corporationid_");
		var corporation_id = $("#" + corp_id).val();

		var jdata = {
			"case_id": case_id,
			"kinvoice_id": kinvoice_id,
			"invoice_number": invoice_number, 
			"total": total, "payments": payments, 
			"corporation": corporation, 
			"corporation_id": corporation_id,
			"corporation_type": corporationtype,
			"blnAllInvoices": this.model.get("blnAllInvoices")
		};
		composeCheck(element.id, "IN", "invoice", jdata);
	},
	newMessage: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		
		var element_id = element.id;
		var arrID = element_id.split("_");
		var case_id = arrID[1];
		var fprefix = "invoice_" + case_id + "_";
		var total_id = element_id.replace(fprefix, "kinvoicetotal_");
		var total = $("#" + total_id).html();
		
		var kinv_id = element_id.replace(fprefix, "kinvoice_id_");
		var kinvoice_id = $("#" + kinv_id).val();

		var payments_id = element_id.replace(fprefix, "kinvoicepayments_");
		var payments = $("#" + payments_id).html();
		
		var document_el_id = element_id.replace(fprefix, "document_id_");
		var document_id = $("#" + document_el_id).html();

		var invoice_number_id = element_id.replace(fprefix, "invoice_number_");
		var invoice_number = $("#" + invoice_number_id).html();
		var arrInvoiceNumber = invoice_number.split("-");
		var case_id = arrInvoiceNumber[0];
		
		var href = $("#" + invoice_number_id).attr("href");
		var arrRef = href.split("file=");
		var filepath = arrRef[1];
		
		var company_id = element_id.replace(fprefix, "corporation_");
		var corporation = $("#" + company_id).html();
		
		var corporationtype_id = element_id.replace(fprefix, "corporationtype_");
		var corporationtype = $("#" + corporationtype_id).val();
		
		var corp_id = element_id.replace(fprefix, "corporationid_");
		var corporation_id = $("#" + corp_id).val();

		var case_name_id = element_id.replace(fprefix, "case_name_");
		var case_name = $("#" + case_name_id).val();
		
		var case_number_id = element_id.replace(fprefix, "case_number_");
		var case_number = $("#" + case_number_id).val();
		
		var jdata = {
			"case_id": case_id,
			"kinvoice_id": kinvoice_id,
			"invoice_number": invoice_number, 
			"document_id": document_id,
			"filepath": filepath,
			"total": total, "payments": payments, 
			"corporation": corporation, 
			"corporation_id": corporation_id,
			"corporation_type": corporationtype,
			"case_name": case_name, 
			"case_number": case_number,
			"blnAllInvoices": this.model.get("blnAllInvoices")
		};
		
		composeMessage("invoice_" + case_id, jdata);
	}
});
window.dashboard_accounting_view = Backbone.View.extend({
	initialize:function () {
        
    },
	events: {
		"click #dashboard_accounting_all_done":			"doTimeouts"
	},
	render:function () {		
		var self = this;
		console.log(self);
		
		if (typeof this.template != "function") {
			var view = "dashboard_accounting_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		try {
			$(this.el).html(this.template());
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		return this;
	},
	newKInvoice: function(event) {
		event.preventDefault();
		//composeInvoiceAssign();
		composeInvoiceAssign(this.model.get("case_id"));
	},
	checkDistributed: function() {
		var self = this;
		
		if (typeof self.model.get("distributed")!="undefined") {
			
			if (self.model.get("distributed")) {
				$(".glyphicon-edit").hide();
				$(".glyphicon-trash").hide();
				$(".btn-primary").hide();
			}
			
			return;
		}

		var settlement = new SettlementSheet({injury_id: this.model.get("id")});
		settlement.fetch({
			success: function(data) {
				var data_info = data.toJSON().data;
				if (data_info!="") {
					var datum = JSON.parse(data_info);
					//if (datum.date_settled=="" || datum.date_settled=="0000-00-00") {
						$("#new_checkrequest").show();
					//}
					//distributed, lock down
					if (datum.distrib!="" && datum.distrib!="0000-00-00") {
						//settlement distributed		
						if (document.getElementById("abstract_status_holder").innerHTML.indexOf("DISTRIBUTED") < 0) {		
							document.getElementById("abstract_status_holder").innerHTML += "&nbsp;|&nbsp;<span style='color:lime'>DISTRIBUTED&nbsp;&#10003;</span>&nbsp;(No Changes Allowed)";
						}
						self.model.set("distributed", true);
						
						$(".glyphicon-edit").hide();
						$(".glyphicon-trash").hide();
						$(".btn-primary").hide();
					
					} else {
						self.model.set("distributed", false);
					}
				}
			}
		});
	},
	doTimeouts: function() {
		console.log('doTimeouts');
		
		var self = this;
		self.model.set("hide_upload", true);
		console.log(this.model.get("settlement_id"));
		showKaseAbstract(self.model);
		
		//distrubted?
		
		var case_id = this.model.get("case_id");
		var case_type = this.model.get("case_type");
		
		//losses
		var empty_model = new Backbone.Model;		
		empty_model.set("holder", "losses_holder");
		$("#losses_holder").html(new losses_list_view({model: empty_model}).render().el);
		
		var acctmodel = new Backbone.Model({
			"holder": "accounts_holder",
			"case_id": case_id,
			"case_type": case_type,
			"page_title":"Accounts"
		});
		$("#accounts_holder").html(new account_selection_view({model: acctmodel}).render().el);
		
		var case_invoices = new KInvoiceCollection({"case_id": case_id});
		case_invoices.fetch({
			success: function (data) {
				//if (data.length > 0) {
					var mymodel = new Backbone.Model({
						"holder": "kinvoices_holder",
						"document_invoices": false,
						"case_id": case_id,
						"page_title":"Invoices"
					});
					$("#kinvoices_holder").html(new kinvoice_listing_view({collection: data, model: mymodel}).render().el);
					
					if (typeof self.model.get("distributed") == "undefined") {
						self.checkDistributed()
					}
					if (self.model.get("distributed")) {
						$(".glyphicon-edit").hide();
						$(".glyphicon-trash").hide();
						$(".btn-primary").hide();
					}
				//}
			}
		});
		
		var kase_disbursments = new ChecksCollection([], { case_id: case_id, ledger: "OUT" });
		kase_disbursments.fetch({
			success: function(data) {
				var newkase = self.model.clone();
				newkase.set("holder", "#disbursement_holder");
				newkase.set("page_title", "Disbursement");
				newkase.set("blnShowMemo", false);
				//newkase.set("embedded", true);
				$('#disbursement_holder').html(new check_listing_view({collection: data, model: newkase}).render().el);
				
				if (typeof self.model.get("distributed") == "undefined") {
					self.checkDistributed()
				}
				if (self.model.get("distributed")) {
					$(".glyphicon-edit").hide();
					$(".glyphicon-trash").hide();
					$(".btn-primary").hide();
				}
			}
		});
		
		var kase_checks = new ChecksCollection([], { case_id: case_id, ledger: "IN" });
		kase_checks.fetch({
			success: function(data) {
				var otherkase = self.model.clone();
				otherkase.set("holder", "#receipt_holder");
				otherkase.set("page_title", "Receipt");
				otherkase.set("blnShowMemo", false);
				$('#receipt_holder').html(new check_listing_view({collection: data, model: otherkase}).render().el);
				
				if (typeof self.model.get("distributed") == "undefined") {
					self.checkDistributed()
				}
				
				if (self.model.get("distributed")) {
					$(".glyphicon-edit").hide();
					$(".glyphicon-trash").hide();
					$(".btn-primary").hide();
				}
			}
		});
		
		var checkrequests = new CheckRequestsCollection({case_id: case_id});
		checkrequests.fetch({
			success: function(data) {
				var reqkase = self.model.clone();
				reqkase.set("holder", "#checkrequest_holder");
				reqkase.set("page_title", "Check Requests");
				reqkase.set("embedded", true);
				$('#checkrequest_holder').html(new checkrequest_listing_view({collection: data, model: reqkase}).render().el);
				
				if (typeof self.model.get("distributed") == "undefined") {
					self.checkDistributed()
				}
				
				if (self.model.get("distributed")) {
					$(".glyphicon-edit").hide();
					$(".glyphicon-trash").hide();
					$(".btn-primary").hide();
				}
			}
		});	
		
		var settlement_fees = new SettlementFeesCollection({settlement_id: this.model.get("settlement_id"), case_type: this.model.get("case_type")});
		settlement_fees.fetch({
				success: function(data) {
					if (data.toJSON().length==0) {
						$("#fees_holder").hide();
						return;
					}
					var feekase = self.model.clone();
					feekase.set("holder", "#fees_holder");
					feekase.set("page_title", "Settlement Fees");
					feekase.set("embedded", false);
					$('#fees_holder').html(new fee_listing_view({collection: data, model: feekase}).render().el);
					
					if (typeof self.model.get("distributed") == "undefined") {
						self.checkDistributed()
					}
					
					if (self.model.get("distributed")) {
						$(".glyphicon-edit").hide();
						$(".glyphicon-trash").hide();
						$(".btn-primary").hide();
					}
				}
		});
		
		var deductions = new DeductionCollection({case_id: case_id});
		deductions.fetch({
				success: function(data) {
					var dkase = self.model.clone();
					dkase.set("holder", "#deduction_holder");
					dkase.set("page_title", "Deduction");
					dkase.set("embedded", false);
					$('#deduction_holder').html(new deduction_listing_view({collection: data, model: dkase}).render().el);
					
					if (typeof self.model.get("distributed") == "undefined") {
						self.checkDistributed()
					}
					
					if (self.model.get("distributed")) {
						$(".glyphicon-edit").hide();
						$(".glyphicon-trash").hide();
						$(".btn-primary").hide();
					}
				}
		});
		
		setTimeout(function() {
			if (typeof self.model.get("distributed") == "undefined") {
				self.checkDistributed()
			}
		}, 3000);
	}
});