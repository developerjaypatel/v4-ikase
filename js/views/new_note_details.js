var arrLines = [];
var speechRec = "";
window.new_note_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
		arrLines = [];
    },
	 events:{
		"click #view_main": 							"displayMain",
		"click #close_note":							"closeNote",
		"click #save_billing_modal": 					"addBill",
		"click #view_billable":  						"displayBillable",
		"click #cancel_billable":						"cancelBillable",
		"click .open_task":								"openTask",
		"click #recording_button":						"startConverting",
		"click #writing_button":						"writeConversion"
	},
    render: function () {
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "new_note_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		mymodel = this.model.toJSON();
		
		mymodel.note = cleanupNote(mymodel.note);
		mymodel.note = mymodel.note.replaceTout("color: rgb(255, 255, 255);", "");
		
		if (mymodel.partie_array_type=="injurynote" && mymodel.subject=="") {
			mymodel.subject = "Injury Note";
			this.model.set("subject", "Injury Note");
		}
		if (mymodel.partie_array_type=="settlement" && mymodel.subject=="") {
			mymodel.subject = "Settlement Note";
			this.model.set("subject", "Settlement Note");
		}
		if (typeof mymodel.task_id == "undefined") {
			mymodel.task_id = -1;
		}
		if (this.model.get("callback_date")!="" && this.model.get("callback_date")!="0000-00-00 00:00:00") {
			var callback_date = moment(this.model.get("callback_date")).format("MM/DD/YYYY");
			this.model.set("callback_date", callback_date);
			mymodel.callback_date = callback_date;
		}
		var note = mymodel.note;
		//final cleanup
		note = note.replaceTout("\r\n", "<br>");
		note = note.replaceTout("\n", "<br>");
		
		mymodel.note = note;	
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			/*
			var view = "new_note_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			*/
			return "";
		}
		this.model.set("editing", false);
		var fontsizes = "1,2,3,4,5,6,7";
		var controls = "bold italic underline | font size style | color highlight";
		var bodyStyle = "";
		
		setTimeout(function() {
			var type_options = document.getElementById("typeInput").options;
			var blnFoundType = false;
			var arrLength = type_options.length;
			var party_array_type = self.model.get("partie_array_type");
			var selected_found = 0;
			if (party_array_type!="") {
				for(var i = 0; i < arrLength; i++) {
					var val = type_options[i].value;
					
					if (val.toLowerCase()==party_array_type.toLowerCase()) {
						if (!blnFoundType) {
							blnFoundType = true;
							selected_found = i;
							break;
						}
					}
				}
			}
			document.getElementById("typeInput").selectedIndex = selected_found;
			//clear for eventual filling with view
			$("#billing_holder").html("");
						
			if (self.model.id < 1 && self.model.get("subject")=="") {
				$("#subjectInput").val("General Note");
			}
			
			if (!blnFoundType) {
				if (self.model.get("partie_array_type")=="settlement") {
					//make sure the settlement type is available and chosen
					var types = document.getElementById("typeInput").options;
					var blnSettlementType = false;
					for (var i = 0; i < types.length; i++) {
						if (document.getElementById("typeInput").options[i].value=="settlement") {
							blnSettlementType = true;
							break;
						}
					}
					if (!blnSettlementType) {
						$("#typeInput").html($("#typeInput").html() + "<option value='settlement'>Settlement Note</option>");
						$("#typeInput").val('settlement');
					}
				}
			}
			/*	
			//WHAT IS THIS ANGEL?
			var options = $("#typeInput option");
			var arr = options.map(function(_, o) { return { t: $(o).text(), v: o.value }; }).get();
			arr.sort(function(o1, o2) { return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0; });
			options.each(function(i, o) {
			  o.value = arr[i].v;
			  $(o).text(arr[i].t); 
			  
			});
			*/
			/*
			if (partie_array_type!='quick' && partie_array_type!='applicant' && partie_array_type!='') {
			}*/
		}, 100);
		setTimeout(function() { 
				$(".task_form .edit").trigger("click"); 
				$(".task_form .delete").hide();
				$(".task_form .reset").hide();
				
				/*
				$("#noteInput.modal_input").cleditor({
					width:550,
					height: 210,
					controls: controls,
					sizes: fontsizes,
					bodyStyle: bodyStyle
				});
				*/
				$(".new_note #noteInput").cleditor({
					width:550,
					height: 210,
					controls:     // controls to add to the toolbar
							  "bold italic underline | font size " +
							  "style | color highlight"
				});
				
				if (typeof current_case_id == "undefined") {
					current_case_id = self.model.get("case_id");
				}
				/*
				$("#billing_time_dropdownInput").editableSelect({
					onSelect: function (element) {
						var billing_time = $("#billing_time_dropdownInput").val();
						$("#billing_time").val(billing_time);
						//alert(billing_time);
					}
				});
				*/
				//we need to upload attachments
				$('#message_attachments').html(new message_attach({model: self.model}).render().el);
				setTimeout(function() {
					$(".message_attach_form #queue").css("height", "70px");
					$(".message_attach_form #queue").css("width", "550px");
					var theme = {
						theme: "facebook",
						onAdd: function(item) {
							if ($("#message_documents_list").length > 0) {
								var kase = kases.findWhere({case_id: item.id});
								var kase_documents = new MessageAttachments({ case_id: item.id});
								kase_documents.fetch({
									success: function(kase_documents) {
										var thetitle = "(Select from list to attach Documents)";
										kase.set("list_title", thetitle);
										
										$('#message_documents_list').html(new document_listing_message({collection: kase_documents, model: kase}).render().el);
										//don't show it yet, let the user request it
									}
								});
							}
						}
					};
					$("#case_fileInput").tokenInput("api/kases/tokeninput", theme);
					if (self.model.get("case_id") > 0) {
						setTimeout(function() {
							loadNoteCase(self);
						}, 700);
					} 
					//does this note have any attachments
					if (self.model.id > 0) {
						var note_documents = new AttachmentCollection([], { parent_id: self.model.id, parent_table: "notes" });
						note_documents.fetch({
							success: function(data) {
								var arrNoteDocuments = [];
								var arrNoteDocumentsFilename = [];
								_.each( data.toJSON(), function(note_document) {
									arrNoteDocuments[arrNoteDocuments.length] = note_document.document_id;
									var display_filename = note_document.document_filename.replaceAll("%20", "&nbsp;");
									arrNoteDocumentsFilename[arrNoteDocumentsFilename.length] = display_filename;
								});
								$(".new_note #send_document_id").val(arrNoteDocuments.join("|"));
								$(".new_note #send_queue").html(arrNoteDocumentsFilename.join("<br>"));
							}
						});	
					}
				}, 200);
				
				$('#callback_dateInput').datetimepicker(
					{ validateOnBlur:false, 
						timepicker:false, 
						format:'m/d/Y',
						minDate: 0,
						onChangeDateTime:function(dp,$input){
							$("#callback_assignee_row").fadeIn();
						}
					}
				);
				
				var theme_3 = {
					theme: "event",
					onAdd: function(item) {
						//
					}
				};
				$(".new_note #assigneeInput").tokenInput("api/user", theme_3);
				$(".token-input-list-event").css("width", "500px");
				//focus
				//$("#subjectInput").focus();
				
				$('#noteInput').cleditor()[0].focus();
			}, 500);
		
		//from partie_cards.js
		/*
		if (blnQuickNotes) {
			setTimeout(function() {
				$("#typeInput").val("quick");
				$("#typeInput").trigger("change");
			}, 1000);
			blnQuickNotes = false;
		}
		*/
		/*
		setTimeout(function() { 
			if (customer_id == "1033") {
				var bill_note_id = self.model.get('id');
				//console.log("case: " + current_case_id + "action: " + bill_event_id + "type: event");
				//return;
				$("#timekeeperInput").tokenInput("api/user");
				$(".token-input-list").css("width", "310px");
				
				
				var bill = new BillingMain({ case_id: current_case_id, action_id: bill_note_id, action_type: "note"});
				bill.fetch({
					success: function(bill) {
						bill = bill.toJSON();
						bill.billing_date = moment(bill.billing_date).format("MM/DD/YYYY hh:mma");
						$("#billing_dateInput").val(bill.billing_date);
						//return;
						$("#durationInput").val(bill.duration);
						$("#billing_form #statusInput").val(bill.billing_status);
						$("#billing_rateInput").val(bill.billing_rate);
						$("#activity_codeInput").val(bill.activity_code);
						$("#billing_form #descriptionInput").val(bill.description);
						$("#billing_id").val(bill.billing_id);
						$("#billing_form #table_id").val(bill.billing_id);
						
						if (bill.billing_id != "") {
							//setTimeout(function() {
								$("#timekeeperInput").tokenInput("add", {id: bill.timekeeper, name: bill.user_name});
								$(".token-input-list").css("width", "310px");
							//}, 2000);
						}
					}
				});
			}
		}, 1000);	
		*/
		return this;
    },
	endSpeech: function() {
		var r = document.getElementById("speech_result");
		var t = document.getElementById("noteInput");
		var feedback = document.getElementById("speech_feedback");
		var joint = "";
		var new_text = arrLines.join(joint);
		$('.new_note #noteInput').val(new_text).blur();
		//reset the array
		arrLines = [];

		feedback.innerHTML = "done";
		//start listening again
		//startConverting();
		
		document.getElementById("recording_button").style.display = "block";
		document.getElementById("writing_button").style.display = "none";
		r.innerHTML = "";
		r.style.display = "none";
		
		$("#note_input_holder").show();
		$("#note_speech_holder").hide();
		
		setTimeout(function() {
			feedback.innerHTML = "";
		}, 2500);
	},
	writeConversion: function(event) {
		event.preventDefault();
		var r = document.getElementById("speech_result");
		var t = document.getElementById("noteInput");
		var feedback = document.getElementById("speech_feedback");
		
		feedback.innerHTML = "transcribing";
		this.endSpeech();
	},
	startConverting: function(event) {
		var self = this;
		event.preventDefault();
		$("#note_input_holder").hide();
		//clear it out for new text
		var current_text = $('.new_note #noteInput').val();
		$('.new_note #noteInput').val("").blur();
		$("#note_speech_holder").show();
		var r = document.getElementById("speech_result");
		r.style.display = "block";
		var t = document.getElementById("noteInput");
		var feedback = document.getElementById("speech_feedback");
		if ('webkitSpeechRecognition' in window) {
			document.getElementById("recording_button").style.display = "none";
			document.getElementById("writing_button").style.display = "block";
			speechRec = new webkitSpeechRecognition();
			speechRec.continuous = true;
			speechRec.interimResults = true;
			speechRec.lang = 'en-US';
			speechRec.start();
			
			var finalTrans = current_text;
			speechRec.onstart =  function(event) {
				r.innerHTML = "";
				feedback.innerHTML = "listening";
			}
			speechRec.onresult = function(event) {
					var interimTrans = '';
					for (var i = event.resultIndex; i < event.results.length; i++) {
						var transcript = event.results[i][0].transcript;
						if (transcript=="\n") {
							//arrLines.push(transcript);
							interimTrans += "<br />";
						}
						
						if (event.results[i].isFinal) {
							finalTrans += transcript;
							if (transcript!="\n" && transcript!="," && transcript!=".") {
								finalTrans += " ";
							}
							arrLines.push(finalTrans);
							
							self.endSpeech();
						} else {
							interimTrans += transcript;
						}
					}
					r.innerHTML = finalTrans + "<span class='iterim'>" + interimTrans + "</span>";
			};
			speechRec.onend =  function(event) {
				
			}
			speechRec.onerror = function(event) {
				feedback.innerHTML = "error";
			};
		} else {
			alert("Speech Recognition is not available on this browser");
		}
	},
	addBill: function(event) {
		var self = this;
		var element = event.currentTarget;
		
		if (customer_id == 1033) {
			
			var billing_date = $("#billing_dateInput").val();
			var duration = $("#durationInput").val();
			var status = $("#billing_form #statusInput").val();
			var billing_rate = $("#billing_rateInput").val();
			var activity_code = $("#activity_codeInput").val();
			var timekeeper = $("#timekeeperInput").val();
			var description = $("#billing_form #descriptionInput").val();
			var table_id = $("#billing_form #table_id").val();
			var action_id = $("#action_id").val();
			var action_type = $("#action_type").val();
				
			var formValues = "case_id=" + current_case_id + "&billing_date=" + billing_date + "&table_id=" + table_id + "&status=" + status;
				formValues += "&duration=" + duration + "&billing_rate=" + billing_rate + "&activity_code=" + activity_code + "&timekeeper=" + timekeeper + "&description=" + description + "&action_id=" + action_id + "&action_type=" + action_type;
			
			var modal_bg = $(".modal-dialog").css('background-image');
			modal_bg = modal_bg.replace('"', "'");
			modal_bg = modal_bg.replace('"', "'");
			//console.log(modal_bg);
			//alert(modal_bg);
			//return;
			$.ajax({
			  method: "POST",
			  url: "api/billing/add",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
					  alert("error");
				  } else {
					  $("#myModalBody").css('background', "#0C3");
					  //rgb(255, 255, 255)
					  setTimeout(function() {
						  $("#myModalBody").css('background-color', '');
						  $("#myModalBody").css('background-image', modal_bg);
						  setTimeout(function() {
							  //self.displayEvent();
						  }, 700);
					  }, 2000);
				  }
			  }
			});
		}
	},
	displayMain: function(event){
		if (blnShowBilling) {
			$("#form_title_label").html("New Note");
			$(".new_note").fadeIn();
			$("#billing_holder").fadeOut();
			$(".save").show();
			
			//hide the button
			$("#view_main").fadeOut(function() {
				var billing_employee = $("#timekeeperInput").val();
				if (billing_employee!="") {
					$("#view_billable").val("Bill Ready ✓");
					$("#view_billable").fadeIn();
					$("#cancel_billable_holder").css("display", "inline");
				} else {
					$("#view_billable").val("Not Billed");
					$("#view_billable").fadeIn();
					
					setTimeout(function() {
						$("#view_billable").val("Bill This");
					}, 2500);
				}
			});
		}
	},
	displayBillable: function(event){
		//return;
		event.preventDefault(); // Don't let this button submit the form
		var event_id = $("#table_id").val();

		if (blnShowBilling) {
			$("#form_title_label").html("Bill this Note");
			setTimeout(function() {
				$(".new_note").fadeOut();
				$("#cancel_billable_holder").css("display", "none");
				//hide the button
				$("#view_billable").fadeOut(function() {
					$("#view_main").val("Return to Note");
					$("#view_main").fadeIn();
				});
				/*
				$('.modal-dialog').animate({width:460, marginLeft:"-250px"}, 700, 'easeInSine');
				$("#billing_holder").fadeIn();
				$('#billing_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
				*/
				$(".save").hide();
				
				$("#billing_holder").fadeIn();
				if ($("#billing_holder").html().trim() == "") {
					var bill = new BillingMain({ case_id: current_case_id, action_id: -1, action_type: "note"});
					bill.set("holder", "billing_holder");
					bill.set("billing_date", moment().format("MM/DD/YYYY"));
					bill.set("activity_category", "Notes");
					bill.set("activity_id", -1);
					
					bill.set("activity", "Note created");
					
					$("#billing_holder").html(new activity_bill_view({model: bill}).render().el);
				}
				//$("#timekeeperInput").tokenInput("api/user");
			}, 400);
		}
    },
	cancelBillable: function(event){
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		if (blnShowBilling) {
			$("#billing_holder").html("");
			$("#view_billable").val("Bill This");
			$("#cancel_billable_holder").fadeOut();
		}
	},
	openTask: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		composeTask(element.id);
	},
	editTaskViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".task_form_" + field_name;
		}
		editField(element, master_class);
	},
	editTaskViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".task_form_" + field_name;
		}
		//editField(element, master_class);
	},
	
	saveTaskViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".task_form_" + field_name;
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
		this.addTaskView(event);
	
	},
	
	addTaskView:function (event) {
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
		addForm(event, "task_form");
		return;
    },
	deleteTaskView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "task");
		return;
    },
	
	toggleTaskEdit: function(event) {
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
		$(".task_form .editing").toggleClass("hidden");
		$(".task_form .span_class").removeClass("editing");
		$(".task_form .input_class").removeClass("editing");
		
		$(".task_form .span_class").toggleClass("hidden");
		$(".task_form .input_class").toggleClass("hidden");
		$(".task_form .input_holder").toggleClass("hidden");
		$(".button_row.task_form").toggleClass("hidden");
		$(".edit_row.task_form").toggleClass("hidden");
	},
	
	resetTaskForm: function(event) {
		event.preventDefault();
		this.toggleInjuryEdit(event);
		//this.render();
		//$("#address").hide();
	},
	valueTaskViewChanged: function(event) {
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
window.new_note_pane = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	 events:{
		"click #view_note": 							"displayNote",
		"click #close_note":							"closeNote",
		"click #save_billing_modal": 					"addBill",
		"click #view_billable":  						"displayBillable",
		"click #hide_preview_pane":						"shrinkNote",
	},
	addBill: function(event) {
		var self = this;
		var element = event.currentTarget;
		
		if (customer_id == 1033) {
			
			var billing_date = $("#billing_dateInput").val();
			var duration = $("#durationInput").val();
			var status = $("#billing_form #statusInput").val();
			var billing_rate = $("#billing_rateInput").val();
			var activity_code = $("#activity_codeInput").val();
			var timekeeper = $("#timekeeperInput").val();
			var description = $("#billing_form #descriptionInput").val();
			var table_id = $("#billing_form #table_id").val();
			var action_id = $("#action_id").val();
			var action_type = $("#action_type").val();
				
			var formValues = "case_id=" + current_case_id + "&billing_date=" + billing_date + "&table_id=" + table_id + "&status=" + status;
				formValues += "&duration=" + duration + "&billing_rate=" + billing_rate + "&activity_code=" + activity_code + "&timekeeper=" + timekeeper + "&description=" + description + "&action_id=" + action_id + "&action_type=" + action_type;
			
			var modal_bg = $(".modal-dialog").css('background-image');
			modal_bg = modal_bg.replace('"', "'");
			modal_bg = modal_bg.replace('"', "'");
			//console.log(modal_bg);
			//alert(modal_bg);
			//return;
			$.ajax({
			  method: "POST",
			  url: "api/billing/add",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
					  alert("error");
				  } else {
					  $("#myModalBody").css('background', "#0C3");
					  //rgb(255, 255, 255)
					  setTimeout(function() {
						  $("#myModalBody").css('background-color', '');
						  $("#myModalBody").css('background-image', modal_bg);
						  setTimeout(function() {
							  //self.displayEvent();
						  }, 700);
					  }, 2000);
				  }
			  }
			});
		}
	},
	shrinkNote: function(event) {
		event.preventDefault();
		$("#dashboard_right_pane").fadeOut(function() {
			$("#dashboard_right_pane").html("");
			if ($("#bodyparts_warning").html()!="") {
				$("#bodyparts_warning").fadeIn();
			}
		});
	},
	displayNote: function(event){
		if (customer_id == 1033) {
			$(".new_note").fadeIn();
			$("#billing_holder").fadeOut();
			$("#modal_save_holder").fadeIn();
			$('.modal-dialog').animate({width:610, marginLeft:"-350px"}, 1100, 'easeInSine');
		}
	},
	displayBillable: function(event){
		//return;
		event.preventDefault(); // Don't let this button submit the form
		var event_id = $("#table_id").val();

		if (blnShowBilling) {
			setTimeout(function() {
				$(".new_note").fadeOut();
				$('.modal-dialog').animate({width:460, marginLeft:"-250px"}, 700, 'easeInSine');
				$("#billing_holder").fadeIn();
				$('#billing_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
				$("#modal_save_holder").fadeOut();
				
				$("#billing_holder").fadeIn();
				if ($("#billing_holder").html().trim() == "") {
					var bill = new BillingMain({ case_id: current_case_id, action_id: -1, action_type: "note"});
					bill.set("holder", "billing_holder");
					bill.set("billing_date", moment().format("MM/DD/YYYY"));
					bill.set("activity_category", "Notes");
					bill.set("activity_id", -1);
					
					bill.set("activity", "Note created - " + this.model.get("modal_title"));
					
					$("#billing_holder").html(new activity_bill_view({model: bill}).render().el);
				}
				//$("#timekeeperInput").tokenInput("api/user");
			}, 400);
		}
    },
    render: function () {
		if (typeof this.template != "function") {
			var view = "new_note_pane";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		mymodel = this.model.toJSON();
		
		if (mymodel.partie_array_type=="injurynote" && mymodel.subject=="") {
			mymodel.subject = "Injury Note";
		}
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		this.model.set("editing", false);
		var fontsizes = "1,2,3,4,5,6,7";
		var controls = "bold italic underline | font size style | color highlight";
		var bodyStyle = "";
		
		setTimeout(function() {
			var type_options = document.getElementById("typeInput").options;
			var blnFoundType = false;
			var arrLength = type_options.length;
			var party_array_type = self.model.get("partie_array_type");
			
			var selected_found = 0;
			if (party_array_type!="") {
				for(var i = 0; i < arrLength; i++) {
					var val = type_options[i].value;
					
					if (val==party_array_type) {
						if (!blnFoundType) {
							blnFoundType = true;
							selected_found = i;
							break;
						}
					}
					
					if (!blnFoundType) {
						if (party_array_type=="quick") {
							if (val == "Quick Note") {
								blnFoundType = true;
								selected_found = i;
								break;
							}
						}
					}
				}
			}
			document.getElementById("typeInput").selectedIndex = selected_found;
			
			//clear for eventual filling with view
			$("#billing_holder").html("");
			
			if (self.model.id < 1) {
				$("#subjectInput").val("General Note");
			}
			/*	
			//WHAT IS THIS ANGEL?
			var options = $("#typeInput option");
			var arr = options.map(function(_, o) { return { t: $(o).text(), v: o.value }; }).get();
			arr.sort(function(o1, o2) { return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0; });
			options.each(function(i, o) {
			  o.value = arr[i].v;
			  $(o).text(arr[i].t); 
			  
			});
			*/
			/*
			if (partie_array_type!='quick' && partie_array_type!='applicant' && partie_array_type!='') {
			}*/
		}, 777);
		setTimeout(function() { 
			$(".task_form .edit").trigger("click"); 
			$(".task_form .delete").hide();
			$(".task_form .reset").hide();
			
			/*
			$("#noteInput.modal_input").cleditor({
				width:550,
				height: 210,
				controls: controls,
				sizes: fontsizes,
				bodyStyle: bodyStyle
			});
			*/
			$(".new_note #noteInput").cleditor({
				width:390,
				height: 107,
				controls:     // controls to add to the toolbar
						  "bold italic underline | font size " +
						  "style | color highlight"
			});
			
			if (typeof current_case_id == "undefined") {
				current_case_id = self.model.get("case_id");
			}
			/*
			$("#billing_time_dropdownInput").editableSelect({
				onSelect: function (element) {
					var billing_time = $("#billing_time_dropdownInput").val();
					$("#billing_time").val(billing_time);
					//alert(billing_time);
				}
			});
			*/
			//we need to upload attachments
			$('.new_note #message_attachments').html(new message_attach({model: self.model}).render().el);
			setTimeout(function() {
				$(".message_attach_form #queue").css("height", "70px");
				$(".message_attach_form #queue").css("width", "550px");
				var theme = {
					theme: "facebook",
					onAdd: function(item) {
						if ($("#message_documents_list").length > 0) {
							var kase = kases.findWhere({case_id: item.id});
							var kase_documents = new MessageAttachments({ case_id: item.id});
							kase_documents.fetch({
								success: function(kase_documents) {
									var thetitle = "(Select from list to attach Documents)";
									kase.set("list_title", thetitle);
									
									$('#message_documents_list').html(new document_listing_message({collection: kase_documents, model: kase}).render().el);
									//don't show it yet, let the user request it
								}
							});
						}
					}
				};
				$("#case_fileInput").tokenInput("api/kases/tokeninput", theme);
				if (self.model.get("case_id") > 0) {
					setTimeout(function() {
						var casing_file = $("#case_fileInput").val();
						if (casing_file == "") {
							var case_id_get = self.model.get("case_id");
							var kase = kases.findWhere({case_id:case_id_get});
							$("#case_fileInput").tokenInput("add", {id: case_id_get, name: kase.name()});
							$("#case_id_holder .token-input-list-facebook").hide();
							$("#case_idSpan").html(kase.name());
						}
					}, 700);
				} 
				//does this note have any attachments
				if (self.model.id > 0) {
					var note_documents = new AttachmentCollection([], { parent_id: self.model.id, parent_table: "notes" });
					note_documents.fetch({
						success: function(data) {
							var arrNoteDocuments = [];
							var arrNoteDocumentsFilename = [];
							_.each( data.toJSON(), function(note_document) {
								arrNoteDocuments[arrNoteDocuments.length] = note_document.document_id;
								arrNoteDocumentsFilename[arrNoteDocumentsFilename.length] = note_document.document_filename;
							});
							$(".new_note #send_document_id").val(arrNoteDocuments.join("|"));
							$(".new_note #send_queue").html(arrNoteDocumentsFilename.join("; "));
						}
					});	
				}
			}, 999);
			
			$('.new_note #callback_dateInput').datetimepicker(
			{ 
				validateOnBlur:false, 
				timepicker:false, 
				format:'m/d/Y',
				minDate: 0,
				onChangeDateTime:function(dp,$input){
					$(".new_note #callback_assignee_row").fadeIn();
				}
			});
			
			var theme_3 = {
				theme: "event",
				onAdd: function(item) {
					//
				}
			};
			$(".new_note #assigneeInput").tokenInput("api/user", theme_3);
			$(".token-input-list-event").css("width", "320px");
			//cleditor focus
			$('#noteInput').cleditor()[0].focus();
		}, 1111);
		
		//from partie_cards.js
		if (blnQuickNotes) {
			setTimeout(function() {
				if ($("#typeInput").html().indexOf('value="quick"') > -1) {
					$("#typeInput").val("quick");
				} else {
					if ($("#typeInput").html().indexOf('value="Quick Note"') > -1) {
						$("#typeInput").val("Quick Note");
					}
				}
				$("#typeInput").trigger("change");
			}, 1000);
			blnQuickNotes = false;
		}
		/*
		setTimeout(function() { 
			if (customer_id == "1033") {
				var bill_note_id = self.model.get('id');
				//console.log("case: " + current_case_id + "action: " + bill_event_id + "type: event");
				//return;
				$("#timekeeperInput").tokenInput("api/user");
				$(".token-input-list").css("width", "310px");
				
				
				var bill = new BillingMain({ case_id: current_case_id, action_id: bill_note_id, action_type: "note"});
				bill.fetch({
					success: function(bill) {
						bill = bill.toJSON();
						bill.billing_date = moment(bill.billing_date).format("MM/DD/YYYY hh:mma");
						$("#billing_dateInput").val(bill.billing_date);
						//return;
						$("#durationInput").val(bill.duration);
						$("#billing_form #statusInput").val(bill.billing_status);
						$("#billing_rateInput").val(bill.billing_rate);
						$("#activity_codeInput").val(bill.activity_code);
						$("#billing_form #descriptionInput").val(bill.description);
						$("#billing_id").val(bill.billing_id);
						$("#billing_form #table_id").val(bill.billing_id);
						
						if (bill.billing_id != "") {
							//setTimeout(function() {
								$("#timekeeperInput").tokenInput("add", {id: bill.timekeeper, name: bill.user_name});
								$(".token-input-list").css("width", "310px");
							//}, 2000);
						}
					}
				});
			}
		}, 1000);
		*/	
		return this;
    },
	
	editTaskViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".task_form_" + field_name;
		}
		editField(element, master_class);
	},
	editTaskViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".task_form_" + field_name;
		}
		//editField(element, master_class);
	},
	
	saveTaskViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".task_form_" + field_name;
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
		this.addTaskView(event);
	
	},
	
	addTaskView:function (event) {
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
		addForm(event, "task_form");
		return;
    },
	deleteTaskView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "task");
		return;
    },
	
	toggleTaskEdit: function(event) {
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
		$(".task_form .editing").toggleClass("hidden");
		$(".task_form .span_class").removeClass("editing");
		$(".task_form .input_class").removeClass("editing");
		
		$(".task_form .span_class").toggleClass("hidden");
		$(".task_form .input_class").toggleClass("hidden");
		$(".task_form .input_holder").toggleClass("hidden");
		$(".button_row.task_form").toggleClass("hidden");
		$(".edit_row.task_form").toggleClass("hidden");
	},
	
	resetTaskForm: function(event) {
		event.preventDefault();
		this.toggleInjuryEdit(event);
		//this.render();
		//$("#address").hide();
	},
	valueTaskViewChanged: function(event) {
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
window.note_listing_view_mobile = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
		
		if (typeof this.model.get("partie_type")== "undefined"){
			this.model.set("partie_type", "note");
		}
    },
	events: {
		"change #typeFilter":							"filterNotes",
		"click .compose_new_note":						"newNotes",
		"click .edit_note":								"newNotes",
		"click .delete_note":							"confirmdeleteNote",
		"click .delete_yes":							"deleteNote",
		"click .delete_no":								"canceldeleteNote",
		"click #note_clear_search":						"clearSearch",
		"click #label_search_notes":					"Vivify",
		"click #notes_searchList":						"Vivify",
		"focus #notes_searchList":						"Vivify",
		"blur #notes_searchList":						"unVivify",
	},
	unVivify: function(event) {
		var textbox = $("#notes_searchList");
		var label = $("#label_search_notes");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#notes_searchList");
		var label = $("#label_search_notes");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
    render:function () {		
		var self = this;
		var notes = this.collection.toJSON();
		var note_filter_options = "";
		var arrTypes = [];
		_.each( notes, function(note) {
			var attach_indicator = "hidden";
			if (note.attachments!="") {
				attach_indicator = "visible";
				
				//clean up if necessary
				var arrAttach = note.attachments.split("uploads/");
				if (arrAttach.length==2) {
					note.attachments = "uploads/" + arrAttach[1];
				}
			}
			//removing link for edit of inter office added to notes	
			var thenote = note.note;
			note.editable = true;
			/*
			if (thenote.indexOf("(sent to ") > 0) {
				note.editable = false;
			}
			*/
			var standard_link = '<span onmouseover="showAttachmentPreview(' + String.fromCharCode(39) + 'note' + String.fromCharCode(39) + ', event, ' + String.fromCharCode(39) + note.attachments + String.fromCharCode(39) + ', ' + note.case_id + ', ' + note.customer_id + ')" onmouseout="hideMessagePreview()"><i style="font-size:15px;color:#FFFFFF; cursor:pointer; visibility:' + attach_indicator + '" class="glyphicon glyphicon-paperclip"></i></span>';
							
			note.attachment_link = standard_link;
			if (note.type=="document") {
				var arrNote = thenote.split("\r\n");
				
				note.attachment_link = "";
				note.note = arrNote[1].trim();
			}
			
			if (moment(note.dateandtime).format('h:mm a')=="12:00 am") {
				note.time = "";
			} else {
				note.time = moment(note.dateandtime).format('h:mm a');
			}
			note.date = moment(note.dateandtime).format("dddd, MMMM Do YYYY");
			
			if (note.type!="") {
				if (arrTypes.indexOf(note.type) < 0) {
					arrTypes[arrTypes.length] = note.type;
					note_filter_options +='\r\n<option value="' + note.type + '">' + note.type.capitalize() + ' Note</option>';
				}
			}
			
			if (note.status == "IMPORTANT") {
				note.type = "<span style='background:blue;color:white'>IMPORTANT</span>";
			} 
			
			//watch for html
			if (typeof note.attribute != "undefined") {
				if (note.attribute.indexOf("@") > -1) {
					note.type = "Webmail";
					//note.note = "<iframe srcdoc='" + note.note.replaceAll("'", "`") + "' style='width:100%; height:300px;background:white' frameborder='0'></iframe>";
					note.note = "<iframe src='api/webmail_get.php?notes_id=" + note.id + "' style='width:100%; height:300px;background:white' frameborder='0'></iframe>";
				}
			}
			var arrNoteDOIS = [];
			if (typeof note.injury_dates != "undefined") {
				if (note.injury_dates!="") {
					var arrDates = note.injury_dates.split(",");
					arrDates.forEach(function(injury_dates, index, array) {
						var arrDateArray = injury_dates.split("|");
						
						var doi_dates = moment(arrDateArray[1]).format("MM/DD/YYYY");
						if (arrDateArray[2]!="0000-00-00") {
							doi_dates += " - " + moment(arrDateArray[1]).format("MM/DD/YYYY") + " CT";
						}
						var injury_details = "<a href='#injury/" + current_case_id + "/" + arrDateArray[0] + "' class='white_text'>" + doi_dates + "</a>";
						arrNoteDOIS.push("<a href='#kases/" + note.main_case_id + "' class='white_text'>" + arrDateArray[0] + "</a> - DOI:" + injury_details);
					
					});
				}
			}
			if (arrNoteDOIS.length > 0) {
				note.injury_dates = arrNoteDOIS.join("<br>");
				
				var new_subject = "<span style='font-size:0.7em;'>From Related Cases:<br>" + note.injury_dates + "</span>";
				note.subject = note.subject + "<br><br>" + new_subject;
			}
			/*
			//maybe a related case
			if (typeof note.start_date != "undefined") {
				if (note.case_id != current_case_id) {
					var new_subject = "<span style='font-size:0.7em; font-style:italic'>From Related Case <a href='#kases/" + note.case_id + "' class='white_text' style='pointer:cursor'>" + note.main_case_number + "</a> - DOI:&nbsp;" + moment(note.start_date).format("MM/DD/YYYY");
					if (note.end_date!="" && note.end_date!="0000-00-00") {
						new_subject += " - " + moment(note.end_date).format("MM/DD/YYYY") + " CT";
					}
					new_subject += "</span>";
					note.subject = note.subject + "<br><br>" + new_subject;
				}
			}
			*/
		});
		//this.collection.bind("reset", this.render, this);
		
		$(this.el).html(this.template({notes: notes, case_id: this.model.get("case_id"), display_mode: this.model.get("display"), partie_id: this.model.get("partie_id"), partie_type: this.model.get("partie_type"), note_filter_options: note_filter_options}));
		
		tableSortIt("note_listing", 10);
		
		setTimeout(function(){
			$(".pager").hide();
			$(".pager").css("position","absolute");
			$(".pager").css("top","90px");
			$(".pager").css("left","200px");
			$(".pager").show();
		}, 150);
		
		//from partie_cards.js
		if (blnQuickNotes) {
			setTimeout(function() {
				$("#typeFilter").val("quick");
				$("#typeFilter").trigger("change");
			}, 1000);
			blnQuickNotes = false;
		}
		return this;
    },
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	},
	confirmdeleteNote: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "notes");
	},
	clearSearch: function() {
		$("#notes_searchList").val("");
		$( "#notes_searchList" ).trigger( "keyup" );
	},
	canceldeleteNote: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteNote: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "notes");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".note_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".note_data_row_" + id).fadeOut();
			}, 2500);
		}
	},
	filterNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		filterIt(element, "note_listing", "note");
	}
});
function loadNoteCase(self) {
	var casing_file = $("#case_fileInput").val();
	if (casing_file == "") {
		var case_id_get = self.model.get("case_id");
		var kase = kases.findWhere({case_id:case_id_get});
		
		if (typeof kase == "undefined") {
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.remove(kase.id); kases.add(kase);
						loadNoteCase(self);
					} else {
						//case does not exist, get out
						//document.location.href = "#";
					}
					return;		
				}
			});
		} else {
			var prefix = "";
			var list = document.getElementById("myModal4").classList;
			if (inArray("in", list)) {
				prefix = ".modal-body ";
			}
			
			$(prefix + "#case_fileInput").tokenInput("add", {id: case_id_get, name: kase.name()});
			$(prefix + "#case_id_holder .token-input-list-facebook").hide();
			$(prefix + "#case_idSpan").html(kase.name());
			
			var note_injury_id = self.model.get("doi_id");
			//get dois
			var kase_dois = new KaseInjuryCollection({case_id: case_id_get});
			kase_dois.fetch({
				success: function(kase_dois) {
					var dois = kase_dois.toJSON();
					
					var arrID = [];
					var arrOptions = [];
					var blnSelected = false;
					_.each( dois, function(doi) {
						if (arrID.indexOf(doi.injury_id) < 0) {
							arrID.push(doi.injury_id);
							var doi_date = moment(doi.start_date).format("MM/DD/YYYY");
							if (doi.end_date!="0000-00-00") {
								doi_date += "-" +  moment(doi.end_date).format("MM/DD/YYYY") + " CT";
							}
							var selected = "";
							if (note_injury_id==doi.injury_id) {
								selected = " selected";
								blnSelected = true;
							}
							var option = "<option value='" + doi.injury_id + "'" + selected + ">" + doi_date + "</option>";
							arrOptions.push(option);	
						}
					});
					
					if (arrOptions.length > 1) {
						var selected = "";
						if (!blnSelected) {
							selected = " selected";
						}
						var option = "<option value='' " + selected + ">Select DOI from List - optional</option>";
						arrOptions.unshift(option);
						
						$("#doi_id").html(arrOptions.join(""));
						$("#doi_row").show();
					}
				}
			});
		}
	}
}