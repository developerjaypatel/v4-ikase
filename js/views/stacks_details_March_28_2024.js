	window.stack_listing_view = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
    events:{
  		"click .save_icon":						"saveStack",
		"click .complete_icon":					"completeStack",
		"click .check_all": 					"checkAll",
		"click .check_thisone": 				"checkOne",
		"change .kase_input_select": 			"changeMassEmail",
		"keyup .stack_data_row .stack_data":	"nameChanged",
		"click .stack_message":					"newMessage",
		"change .stack_input":					"scheduleReleaseSave",
		"keyup .stack_input":					"scheduleReleaseSave",
		"keyup .document_input":				"scheduleReleaseSave",
		"change .stack_category":				"releaseCategory",
		"click .send_icon":						"sendDocument",
		"click .read_icon":						"unreadScan",
		"click .notify_attorney":				"notifyAttorney",
		"click #import_clear_search":			"clearSearch",
		"click .list_link":						"readScan",
		"click #label_search_imports":			"Vivify",
		"click #import_searchList":				"Vivify",
		"focus #import_searchList":				"Vivify",
		"blur #import_searchList":				"unVivify",
		"click .review_link":					"reviewLink",
		"click .list_link":						"previewDocument",
		"click .select_day":					"selectDay",
		"click .select_status":					"selectStatus",
		"click .select_attached":				"selectAttached",
		"click .confirm_action":				"confirmAction",
		"click .stack_note":					"showApplyNotes",
		"click .notify_stack_link":				"notifyStack",
		"click .stack_medindex":				"changeNoteLabel",
		"click .notification_history":			"listNotifications",
		"click #stack_listing_view_done":		"doTimeouts"
	},
    render:function () {		
		var self = this;
		
		//keep track of stacks already allocated to case
		this.document_case_ids = [];
		var stacks = this.collection.toJSON();
		var the_read_indicator = "";
		var current_indicator = "";
		_.each( stacks, function(stack) {
			var import_folder = "";
			var thumb = stack.document_filename.replace(".pdf", ".jpg");
			stack.thumb_description = stack.document_filename;
			
			if (stack.read_date!="" && stack.read_date!="0000-00-00 00:00:00") {
				stack.read_date = moment(stack.read_date).format("MM/DD/YYYY");
			} else {
				stack.read_date = "";
			}
			stack.read_indicator = "<span id='notread_holder_" + stack.document_id + "'><span style='background:white;color:red;padding:1px'>Not Read</span></span>";
			
			stack.read_status = "not read";
			if (stack.read_date != "") {
				stack.read_status = "read";
				stack.read_indicator = "<span id='notread_holder_" + stack.document_id + "'><a class='read_icon' id='read_" + stack.document_id + "' title='Click to mark this upload as Not-Read.  Not-Read uploads stay on top of the list.'><span style='background:#00FF00;color:black;font-weight:bold;padding:1px;cursor:pointer'>READ</span></a></span>";
			}
			
			the_read_indicator = stack.read_status; 
			stack.separator = false;
            if (current_indicator != the_read_indicator) {
                current_indicator = the_read_indicator;
				stack.separator = true;
			}
			
			stack.notified_by = "";
			if (typeof stack.notifier != "undefined") {
				if (stack.notifier != "" && stack.notifier != login_nickname && stack.notification_date!="") {
					stack.notified_by = "<br><br>Notified By: " + stack.notifier.toUpperCase() + "<br>" + moment(stack.notification_date).format("MM/DD/YY");
				}
			}
			
			stack.uploaded_by = "";
			if (typeof stack.uploader == "undefined") {
				stack.uploader = "";
			}
			if (typeof stack.instructions == "undefined") {
				stack.instructions = "";
			}
		
			if (stack.uploader != "") {
				stack.uploaded_by = "by " + stack.uploader_nickname + " on " + moment(stack.upload_time).format("MM/DD/YY hh:mma");
			}
			
			if (stack.type == "batchscan") {
				import_folder = "imports/";
				
				//put the thumbnail together from the filename
				var arrStackFile = thumb.split("_");
				if (arrStackFile.length > 2) {
					arrStackFile.pop(arrStackFile.length - 1);
					
					var thumb_number = arrStackFile[arrStackFile.length - 1];
					
					if (!isNaN(thumb_number)) {
					//	thumb_number--;
					}
					arrStackFile[arrStackFile.length - 1] = thumb_number;
					thumb = arrStackFile.join("_") + ".png";
					
					stack.thumb_description = stack.filename + " - Pages " + stack.description;
				}
			}
			
			if (stack.type == "batchscan2") {
				
				//put the thumbnail together from the filename
				var arrStackFile = thumb.split("_");
				if (arrStackFile.length > 2) {
					arrStackFile.pop(arrStackFile.length - 1);
					
					var thumb_number = arrStackFile[arrStackFile.length - 1];
					
					if (!isNaN(thumb_number)) {
					//	thumb_number--;
					}
					arrStackFile[arrStackFile.length - 1] = thumb_number;
					thumb = arrStackFile.join("_") + ".jpg";
					
					stack.thumb_description = stack.filename + " - Pages " + stack.description;
				}
			}
			if (stack.type == "batchscan2") {
				var href = "scans/" + customer_id + "/" + stack.thumbnail_folder + "/imports/" + stack.document_name;
				
				stack.thumb_description = stack.document_name + " - Pages " + stack.description;
			} else {
				var href = "D:/uploads/" + customer_id + "/" + stack.case_id + "/" + stack.document_filename.replace("#", "%23");
				blnBatchscan3 = false;
				if (!isNaN(stack.thumbnail_folder) && stack.document_extension!="docx") {
					//final check
					if (stack.parent_document_uuid!="") {
						if (!isNaN(stack.parent_document_uuid)) {
							//that's the original batchid
							blnBatchscan3 = true;
						}
					}
					if (stack.type=="batchscan3" || blnBatchscan3) {
						href = "D:/uploads/" + customer_id + "/imports/" + stack.thumbnail_folder + "/" + stack.document_filename;
					} else {
						href = "D:/uploads/" + customer_id + "/imports/" + stack.document_filename;
					}
				}
			}
			stack.href = href;
			var the_type = stack.type;
			if (stack.source!="") {
				the_type = stack.source;
			}
			stack.preview = "";
			if (stack.document_filename!="") {
				stack.preview = documentThumbnail(stack.document_filename, customer_id, stack.thumbnail_folder, stack.case_id, the_type, stack.document_date, stack.parent_document_uuid);
			}
			if (stack.preview!="") {
				stack.preview = '<a id="thumbnail_' + stack.document_id + '" class="list_link" style="cursor:pointer"><img src="' + stack.preview + '" width="58" height="75" onmouseover="documentPreview(event, \'' + stack.document_filename + '\', ' + stack.customer_id + ', \'' + stack.thumbnail_folder + '\', \'' + the_type + '\', \'' + stack.document_date + '\', \'' + stack.parent_document_uuid + '\')" onmouseout="hidePreview()" />';
			} 

			stack.show_preview = "";
			stack.preview_link = "";
			//console.log(stack.document_filename);
			stack.preview_href = "api/preview.php?case_id=" + stack.case_id + "&id=" + stack.id + "&file=" + encodeURIComponent(stack.document_filename) + "&type=" + stack.type + "&thumbnail_folder=" + stack.thumbnail_folder;
			
			//case id
			stack.case_link = "<div style='float:right; margin-left: 5px' class='stack_complete' id='stack_complete_holder_" + stack.document_id + "'></div>";
			stack.notify_attorney = "none";
			
			//link if they want to check the case
			//href='#kases/" + stack.case_id + "'
			if (stack.case_id!="") {
				stack.case_link = "<div style='float:right; margin-left: 5px' class='stack_complete' id='stack_complete_holder_" + stack.document_id + "'><a id='stack_complete_" + stack.document_id + "' class='complete_icon white_text' style='cursor:pointer'>mark&nbsp;as&nbsp;completed</a></div>&nbsp;<a id='review_link_" + stack.case_id + "' title='Click to review case' class='review_link white_text' style='cursor:pointer'>view case</a>&nbsp;|&nbsp;<a href='#documents/" + stack.case_id + "' title='Click to review documents' class='white_text' target='_blank'>view documents</a>";
				if (self.document_case_ids.indexOf(stack.case_id) < 0) {
					self.document_case_ids.push(stack.case_id);
				}
			} 
			stack.notify_attorney = "";
			
			if (stack.received_date == "00/00/0000 12:00AM" || stack.received_date == "12/31/1969 4:00PM")
			{
				stack.received_date = moment(stack.document_date).format("MM/DD/YYYY hh:mma");
			}
			
			if (stack.type == "unassigned") {
				if (stack.description_html == "") {
					stack.description_html = "Mail Received";
				}
				if (stack.description_html.indexOf("unassigned document uploaded on") > -1) {
					stack.description_html = stack.description_html.replace("unassigned document uploaded", "Mail Received");
				}
			}
			
			var note = stack.description_html;
			var arrNote = note.split("_");
			//batchscan clean up
			var blnShowNote = true;
			if (arrNote.length == 2) {
				if (!isNaN(arrNote[0]) && !isNaN(arrNote[0])) {
					blnShowNote = false;
				}
			}
			if (!blnShowNote) {
				stack.description_html = "";
			}
		});
		var form_title = "Document Notifications - Batchscan";
		if (this.model.get("type")=="unassigned") {
			form_title = "Document Notifications - Unassigned";
		}
		
		var formValues = { 
			ids: self.document_case_ids.join(",")
		};
		//in documents_pack.php
		var url = "api/kases/byids";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					_.each( data, function(datum) {
						var kase = new Backbone.Model();
						kase.set(datum);
						kases.add(kase);
					});
					self.doTimeouts();
				}
			}
		});
		
		$(this.el).html(this.template({stacks: stacks, form_title: form_title}));
		
		//self.doTimeouts();		
		
		return this;
    },
	selectAttached: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var the_status = arrID[arrID.length - 1];
		
		var stack_completeds = $(".row_" + the_status + " .stack_complete");
		var arrLength = stack_completeds.length;
		for(var i = 0; i < arrLength; i++) {
			var stack_completed = stack_completeds[i];
			
			if (stack_completed.innerHTML!="") {
				var stack_id = stack_completed.id;
				var document_id = stack_id.split("_")[3];
				
				$("#check_assign_" + document_id).attr("checked", true);
			}
		}
		
		$("#" + element.id).fadeOut(function() {
			$("#mass_change").css("visibility", "visible");
			$("#" + element.id.replace("select_attached", "select_status")).hide();
			$("#" + element.id.replace("select_attached", "confirm_action")).trigger("click");
		});
	},
	selectStatus: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var the_status = arrID[arrID.length - 1];
		
		$(".check_thisone_" + the_status).attr("checked", true);
		
		$("#" + element.id).fadeOut(function() {
			$("#mass_change").css("visibility", "visible");
			$("#" + element.id.replace("select_status", "select_attached")).hide();
			$("#" + element.id.replace("select_status", "confirm_action")).fadeIn();
		});
	},
	selectDay: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var the_day = arrID[arrID.length - 1];
		
		$(".check_thisone_" + the_day).attr("checked", true);
		
		$("#" + element.id).fadeOut(function() {
			$("#mass_change").css("visibility", "visible");
			$("#" + element.id.replace("select_day", "confirm_action")).fadeIn();
		});
	},
	confirmAction: function(event) {
		event.preventDefault();
		$("#back-to-top").trigger("click");
		$("#mass_change").css("visibility", "visible");
		$("#mass_change").attr("size", "4");
		$("#mass_change").css("border", "5px solid orange");
		$("#mass_change").focus();
	},
	listNotifications: function(event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrID = element_id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var url = "api/notificationslist/" + theid;
						
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					alert(data.error.text);
				} else {
					var arrLength = data.length;
					var arrRows = [];
					for (var i = 0; i < arrLength; i++) {
						var datum = data[i];
						
						if (datum.notification == "completed") {
							var action = "Completed";
							if (datum.notifier=="") {
								action = "Uploaded";
							}
							row = "<div>" + action + " by " + datum.notifiee + " on " + moment(datum.notification_date).format("MM/DD/YY h:mm:a") + "</div>";
						} else {
							var action = "Notification";
							var by ="";
							
							if (datum.notifier=="") {
								if (datum.notifiee!="") {
									by = " by " + datum.notifiee;
								}
								action = "Uploaded";
								row = "<div>" + action + by + " on " + moment(datum.notification_date).format("MM/DD/YY h:mm:a") + "</div>";
							} else {
								if (datum.notifiee!="") {
									by = " by " + datum.notifier;
								}
								row = "<div>" + action + by + " to " + datum.notifiee + " on " + moment(datum.notification_date).format("MM/DD/YY h:mm:a") + "</div>";
							}
							
						}
						arrRows.push(row);
					}
					$("#notifications_list_" + theid).html(arrRows.join(""));
					$("#notifications_list_" + theid).fadeIn();
				}
			}
		});
	},
	changeNoteLabel: function(event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrID = element_id.split("_");
		var theid = arrID[2];
		
		if (element.checked) {
			$("#stack_note_label_" + theid).html("Record Type");
		} else {
			$("#stack_note_label_" + theid).html("Note");
		}
	},
	showApplyNotes: function(event) {
		var element_id = event.currentTarget.id;
		
		var arrID = element_id.split("_");
		var theid = arrID[2];
		
		if ($("#stack_additional_commands_" + theid).css("display")=="none") {
			$("#stack_additional_commands_" + theid).fadeIn();
		}
	},
	notifyStack: function(event) {
		var element = event.currentTarget;
		var element_id = element.id;
		
		var arrID = element_id.split("_");
		var the_user_id = arrID[arrID.length - 2];
		var theid = arrID[arrID.length - 1];
		var user_full_name = element.innerHTML;
		
		if (the_user_id=="all") {
			var notify_stack_links = $(".notify_stack_link_" + theid);
			var arrUserID = [];
			for (var i = 0; i < notify_stack_links.length; i++) {
				var notify_stack_link_id = notify_stack_links[i].id;
				var arrID = notify_stack_link_id.split("_");
				var the_user_id = arrID[arrID.length - 2];
				var user_full_name = $("#" + notify_stack_link_id).html();
				
				if (the_user_id!="all") {
					if (arrUserID.indexOf(the_user_id) < 0) {
						$("#stack_notify_" + theid).tokenInput("add", {id: the_user_id, name: user_full_name});
						arrUserID.push(the_user_id);
					}
				}
			}
		} else {
			$("#stack_notify_" + theid).tokenInput("add", {id: the_user_id, name: user_full_name});
		}
	},
	doTimeouts: function() {
		var self = this;
		
		$("#stack_listing th").css("font-size", "1.2em");
		$(".stack_data_row ").css("font-size", "1.2em");
		
		setTimeout(function(){
			//tableSortIt("stack_listing");
			//stackAutoComplete("stack_listing", "kase_input");
			var theme = {
				theme: "event",
				tokenLimit: 1,
				onAdd: function(item) {
					//get the id
					var theid = this[0].id;
					theid = theid.split("_")[2];
					$("#stack_case_id_" + theid).val(item.id);
					
					$("#stack_info_holder_" + theid).show();
					$("#stack_type_" + theid).fadeIn();
					$("#stack_category_" + theid).fadeIn();
					$("#notify_attorney_" + theid).fadeIn();
					$("#stack_subcategory_" + theid).fadeIn();
					$("#stack_notify_holder_" + theid).fadeIn();
					
					$("#stack_medindex_" + theid).show();
					$("#stack_additional_medindex_" + theid).fadeIn();					
					
					//do we need to add notes
					if ($("#stack_note_" + theid).val()!="") {
						$("#stack_note_" + theid).trigger("click");
					}
					$("#stack_note_holder_" + theid).fadeIn();
					
					if (!self.model.get("loading")) {
						//return;
						self.releaseSave(theid);
					}
					
					//get kase worker and attorney
					var kase = kases.findWhere({case_id: item.id});
					
					if (typeof kase == "undefined") {
						//could be an old kase not in kases collection
						//get it
						var case_id = item.id;
						var kase =  new Kase({id: case_id});
						kase.fetch({
							success: function (kase) {
								kases.add(kase);
								
								kase = kases.findWhere({case_id: item.id});
								//now we can set it
								setStackKase(theid, kase, self.model.get("loading"));
							}
						});
					} else {
						var blnLoading = self.model.get("loading");
						setStackKase(theid, kase, blnLoading);		
					}
				}
			};
			
			$(".stack_listing .kase_input").tokenInput("api/kases/tokeninput", theme);	
			$(".stack_listing .token-input-list-event").css("width", "470px");
			$(".stack_listing .token-input-dropdown-event").css("width", "390px");		
			
			var theme_3 = {
				theme: "kase",
				onAdd: function(item) {
					var theid = this[0].id;
					theid = theid.split("_")[2];
					
					self.releaseSave(theid);
					
					$("#stack_complete_" + theid).fadeOut();
					
					$("#instructions_holder_" + theid).fadeIn();
					$("#stack_instructions_holder_" + theid).fadeOut();				
				}
			};
			$(".stack_notify").tokenInput("api/user", theme_3);
			
			//show the info for the case ids we have
			for (var i = 0; i < self.document_case_ids.length; i++) {
				var theid = self.document_case_ids[i];
				$("#stack_type_" + theid).fadeIn();
				$("#stack_category_" + theid).fadeIn();
				$("#notify_attorney_" + theid).fadeIn();
			}
			
			$('.stack_listing .date_input').datetimepicker({ 
				validateOnBlur:false, 
				minDate: 0, 
				defaultTime:'8:00',  
				step:30,
				onChangeDateTime: function(current_time,$input) {
					//get the current id
				}
			});
			self.model.set("loading", true);
			//maybe already assigned
			var stacks = self.collection.toJSON();
			_.each( stacks, function(stack) {
				if (stack.case_id != "") {		
					var kase = kases.findWhere({case_id: stack.case_id});
					//might not be a valid id...
					if (typeof kase != "undefined") {
						//add the kase
						if (typeof kase.name != "function") {
							case_name = kase.get("name");
						} else {
							case_name = kase.name();
						}
						$("#stack_case_" + stack.id).tokenInput("add", {
							id: stack.case_id, 
							name: case_name,
							tokenLimit:1
						});
						//show the completed link
						$("#stack_complete_holder_" + stack.id).fadeIn();
					} else {
						//get it
						var kase =  new Kase({id: stack.case_id, stack_id: stack.id});
						kase.fetch({
							success: function (kase) {
								if (kase.toJSON().uuid!="") {
									kases.add(kase);
									var stack_id = kase.get("stack_id");
									$("#stack_case_" + stack_id).tokenInput("add", {
										id: kase.get("case_id"), 
										name: kase.name(),
										tokenLimit:1
									});
									//show the completed link
									$("#stack_complete_holder_" + stack_id).fadeIn();
								}
								return;		
							},
							error: function(model, response, options) {
								console.log(response);							
							}
						});
					}
				}
			});	
			setTimeout(function() {			
				self.model.set("loading", false);
			}, 1200);
			/*
			if (!blnSearched) {
				if ($("#content").css("top") == "0px") {
					$("#content").css("top", "60px");
				}
			} else {
				if ($("#content").css("top") == "60px") {
					$("#content").css("top", "0px");
				}
			}
			*/
		
		}, 600);
		
		if (document.location.hash=="#notifications") {
			$(".notification_list_holder").show();
		}
	},
	clearSearch: function() {
		$("#import_searchList").val("");
		$( "#import_searchList" ).trigger( "keyup" );
		$("#import_searchList").focus();
	},
	unreadScan: function(event) {
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		var formValues = { 
			id: id
		};
		//in documents_pack.php
		var url = "api/document/unread";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					//hide the notify button
					$("#notread_holder_" + id).html("<span id='notread_holder_" + id + "'><span style='background:white;color:red;padding:1px'>Not Read</span></span>");
				}
			}
		});
		
	},
	readScan: function(event) {
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		var formValues = { 
			id: id
		};
		//in documents_pack.php
		var url = "api/document/read";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					//hide the notify button
					$("#notread_holder_" + id).html("<span id='notread_holder_" + id + "'><a class='read_icon' id='read_" + id + "' title='Click to mark this upload as Not-Read.  Not-Read uploads stay on top of the list.'><span style='background:#00FF00;color:black;font-weight:bold;padding:1px;cursor:pointer'>READ</span></a></span>");
				}
			}
		});
		
	},
	sendDocument: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		
		composeMessage(current_input.id);
	},
	confirmdeleteWebmail: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		composeDelete(id, "document");
	},
	checkAll: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		if ($('.check_all').prop('checked') == "false") {
			if (element.checked) { // check select status
				$('.check_thisone').prop('checked', true);
			} else {
				$('.check_thisone').prop('checked', false);         
			}
		} else {
			if (element.checked) { // check select status
				
				$('.check_thisone').prop('checked', true);
				$('.check_all').attr('checked', 'checked');	
				
			} else {
				$('.check_thisone').prop('checked', false);
				$('.check_thisone').prop('checked', false);         
			}
		}
		$('.check_all').attr('checked', 'checked');
		$("#mass_change").css("visibility", "visible");
	},
	checkOne: function() {
		$("#mass_change").css("visibility", "visible");
	},
	changeMassEmail: function(event) {
		event.preventDefault();
		var dropdown = event.currentTarget;
		
		var arrCheckBoxes = $('.check_thisone');
		var arrChecked = [];
		var arrLength = arrCheckBoxes.length;
		
		for(var i =0; i < arrLength; i++) {
			var element = arrCheckBoxes[i];
			//var elementsArray = elements.id.split("_");
			if (element.checked) {
				var idsArray = element.id.split("_");
				var the_id = idsArray[2];
				arrChecked.push(the_id);
			}
		}
		
		if (arrChecked.length==0) {
			document.getElementById(dropdown.id).selectedIndex = 0;
			return;
		}
		this.model.set("checked_boxes", arrChecked);
		var ids = arrChecked.join(", ");
		var action = dropdown.value;
		if (action != "" || action != "undefined") {
			console.log(action);
			if (action == "delete_import") {
				composeDelete(ids, "documents_import");
			}
			if (action == "mark_completed") {
				composeImportComplete(ids, "documents");
			}
			if (action == "assign_to_kase") {
				composeImportAssign(ids, "documents");
			}
		} else { 
			console.log("no action");
		}
		//composeDelete(id, "webmail");
	},
	notifyAttorney: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		var arrID = current_input.id.split("_");
		var theid = arrID[2];
		var thedocument = $("#stack_name_" + theid).val();
		var case_id = $("#stack_case_id_" + theid).val();
		var dateandtime = moment().format("MM/DD/YYYY h:m:s");
		//have to look up kase attorney
		var kase = new Kase({"id": case_id});
		kase.fetch({
				success: function(kase) {
					kases.add(kase);
					
					var formValues = { 
						table_name : "message",
						message_to : kase.get("attorney"),
						messageInput: "[" + thedocument + "] imported",
						case_file: kase.get("id"),
						send_document_id: theid,
						subject: "Document Imported",
						from: login_username,
						notification: "Y"
					};
					var url = "api/messages/add";
					
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								console.log(data.error.text);
							} else { 
								//hide the notify button
								$("#notify_attorney_" + theid).html("atty notified&nbsp;<span style='background:green; color:white'>&#10003;</span>");	
								$("#notify_attorney_" + theid).addClass("white_text");
								
								$(".document_row_" + theid).css("background", "green");
								setTimeout(function() {
									$(".document_row_" + theid).fadeOut();
								}, 2500);
								
								//refresh indicator
								checkImports();
							}
						}
					});			
				}
			});
		
	},
	nameChanged: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		this.model.set(current_input.id, "done");
	},
	restrictSave: function(theid) {
		$("#stack_save_" + theid).fadeOut(function() {
			$("#disabled_stack_save_" + theid).fadeIn();
		});
	},
	scheduleReleaseSave: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		this.releaseSave(theid);
	},
	releaseSave: function(theid) {
		$("#disabled_stack_save_" + theid).fadeOut(function() {
			$("#stack_save_" + theid).fadeIn();
		});
	},
	releaseCategory: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		//if empty, get out
		if (current_input.value == "") {
			return;
		}
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		//if the name has not been changed, change it
		if (this.model.get("type") == "batchscan") {
			if (typeof this.model.get("stack_name_" + theid) == "undefined") {
				//can't save yet until name
				$("#stack_name_" + theid).val($("#stack_type_" + theid).val() + "/" + $("#stack_category_" + theid).val());
			}
		}
	},
	unVivify: function(event) {
		var textbox = $("#import_searchList");
		var label = $("#label_search_imports");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#import_searchList");
		var label = $("#label_search_imports");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	reviewLink: function(event) {
		
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var theid = arrID[arrID.length - 1];
		/*
		setTimeout(function() {
			openKasePreviewPanel(theid);
		}, 100);
		*/
		var url = "?n=#kase/" + theid;
		window.open(url);
	},
	previewDocument: function(event) {
		var element = event.currentTarget;
		var element_id = element.id.replace("thumbnail_", "");
		var url = $("#preview_document_" + element_id).val();
		window.open(url);
	},
	newMessage: function(event) {
		var current_input = event.currentTarget;
		event.preventDefault();
		composeMessage();
	},
	completeStack: function(event) {
		var self = this;
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var formValues = { 
			table_name : "notification",
			document_id: theid
		};
		//in api/documents_pack.php
		var url = "api/stacks/complete";		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					//hide the notify button
					$("#notify_attorney_" + theid).html("completed&nbsp;<span style='background:green; color:white'>&#10003;</span>");	
					$("#notify_attorney_" + theid).addClass("white_text");
					$(".document_row_" + theid).css("background", "green");
					setTimeout(function() {
						$(".document_row_" + theid).fadeOut();
					}, 2500);
					//refresh indicator
					checkUnassigneds();
				}
			}
		});
	},
	saveStack: function(event) {
		var self = this;
		var selfevent = event;
		
		var current_input = event.currentTarget;
		event.preventDefault();
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var name = $("#document_name_" + theid).val();
		var source = $("#document_source_" + theid).val();
		var received_date = $("#document_received_" + theid).val();
		
		var name = $("#stack_name_" + theid).val();
		var case_id = $("#stack_case_id_" + theid).val();
		//show the two drop downs and the check box
		var type = $("#stack_type_" + theid).val();
		var category = $("#stack_category_" + theid).val();
		var subcategory = $("#stack_subcategory_" + theid).val();
		var stack_notify = $("#stack_notify_" + theid).val();
		var note = $("#stack_note_" + theid).val();
		
		var formValues = { 
			name: name, 
			source: source, 
			received_date: received_date,
			case_id: case_id, 
			document_id: theid, 
			type: type, 
			category: category, 
			subcategory: subcategory, 
			note: note
		};
		var url = "api/stacks/add";
		if (this.model.get("type")=="unassigned") {
			url = "api/unassigneds/add";
		}
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					checkImports();
					//hide the save button
					$("#stack_save_" + theid).hide();
					//mark it all green
					var original_back = $(".document_row_" + theid).css("background");
					
					//med index
					if (document.getElementById("stack_medindex_" + theid).checked) {
						var doc = new Document({id: theid});
						doc.fetch({
							success:function (data) {
								var json = data.toJSON();
								//bring up new medindex
								var corp_id = "-1";
								var object_id;
								
								//make sure the kase is in kases
								var kase = kases.findWhere({case_id: case_id});
								if (typeof kase == "undefined") {
									var kase =  new Kase({id: case_id});
									kase.fetch({
										success: function (kase) {
											kases.add(kase);
											
											composeExam(object_id, corp_id, case_id, theid, json);
										}
									});
									return;
								}
								//we're good
								composeExam(object_id, corp_id, case_id, theid, json);
							}
						});
					}
					
					if (stack_notify=="") {
						//mark as completed
						//per thomas 03/14/2016
						//when saving case for batchscan, if no one is notified, then save, mark as completed, show green, and then fade away
						
						self.completeStack(selfevent);
						return;
					}
					$(".document_row_" + theid).css("background", "green");
					$("#stack_complete_holder_" + theid).html("&nbsp;|&nbsp;<a id='stack_complete_" + theid + "' class='complete_icon white_text' style='cursor:pointer'>mark&nbsp;as&nbsp;completed</a>");
					$("#stack_complete_holder_" + theid).fadeIn();
				}
			}
		});
		
		//go through notifies
		var arrNotifies = stack_notify.split(",");
		var notify_instructions = $("#notify_instructions_" + theid).val();
		
		arrNotifies.forEach(function(element, index, array) {
			if (element!="") {
				var formValues = { 
					table_name : "notification",
					document_id: theid, 
					message_to : element,
					instructions: notify_instructions,
					notification: "review"
				};
				//in api/message_pack.php
				var url = "api/notification/add";
				
				$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
					success:function (data) {
						if(data.error) {  // If there is an error, show the error messages
							console.log(data.error.text);
						} else { 
							//hide the notify button
							$("#notify_attorney_" + theid).html("notified&nbsp;<span style='background:green; color:white'>&#10003;</span>");	
							$("#notify_attorney_" + theid).addClass("white_text");
							$(".document_row_" + theid).css("background", "green");
							
							//refresh indicator
							checkUnassigneds();
							
							setTimeout(function() {
								$(".document_row_" + theid).fadeOut();
							}, 700);
							
						}
					}
					
				});
			}
		});
		
		if (document.getElementById("stack_attachnote_" + theid).checked) {
			//add the note to case notes, with document id
			var url = 'api/notes/add';
			var formValues = "table_name=notes&table_id=&noteInput=" + encodeURIComponent($("#stack_note_" + theid).val()) + "&table_attribute=scan_note&type=scan_note&title=Batchscan%20Note&subject=Batchscan%20Note";
			formValues += "&case_id=" + case_id;
			//we might have selected existing case documents
			formValues += "&attach_document_id=" + theid;
			
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
					success:function (data) {
						$("#stack_additional_commands_" + theid).html("<span style='background:black; color:lime'>Note&nbsp;Saved&nbsp;&#10003;</span>");
					}
			});
		}
	}
});

function stackAutoComplete(form_name, obj_selector) {
	new AutoCompleteKaseView({
		input: $("." + form_name + " ." + obj_selector),
		form_name:form_name,
		model: kases,
		onSelect: function (model) {

		}
	}).render();
}
function setStackKase(theid, kase, loading) {
	var arrWorkers = [];
	var the_supervising_attorney_full_name = kase.get("supervising_attorney_full_name")
	if (the_supervising_attorney_full_name=="") {
		var the_supervising_attorney = worker_searches.findWhere({nickname:kase.get("supervising_attorney")});
		if (typeof the_supervising_attorney != "undefined") {
			the_supervising_attorney = the_supervising_attorney.toJSON();
			the_supervising_attorney_id = the_supervising_attorney.id;
			the_supervising_attorney_full_name = the_supervising_attorney.user_name.toLowerCase().capitalizeWords();
			kase.set("supervising_attorney", the_supervising_attorney.user_id);
			kase.set("supervising_attorney_full_name", the_supervising_attorney_full_name);
		}
	} 
	if (the_supervising_attorney_full_name.trim()!="") {
		if (typeof the_supervising_attorney_id == "undefined") {
			if (isNaN(kase.get("supervising_attorney"))) {
				var the_worker = worker_searches.findWhere({nickname:kase.get("supervising_attorney")});
			} else {
				var the_worker = worker_searches.findWhere({id:kase.get("supervising_attorney")});
			}
			if (typeof the_worker != "undefined") {
				the_worker = the_worker.toJSON();
				the_supervising_attorney_id = the_worker.id;
			}
		}
		if (blnDoNotAutoNotify) {
			the_supervising_attorney_full_name = "<a class='notify_stack_link white_text' id='notify_stack_" + the_supervising_attorney_id + "_" + theid + "' title='Click to notify the supervising attorney' style='cursor:pointer'>" + the_supervising_attorney_full_name + "</a>";
		}
		arrWorkers.push("SATTY:&nbsp;" + the_supervising_attorney_full_name);
	}
	
	var the_attorney_full_name = kase.get("attorney_full_name");
	var the_attorney_id = "";
	if (the_attorney_full_name=="") {
		var the_attorney = worker_searches.findWhere({nickname:kase.get("attorney")});
		if (typeof the_attorney != "undefined") {
			the_attorney = the_attorney.toJSON();
			the_attorney_id = the_attorney.id;
			the_attorney_full_name = the_attorney.user_name.toLowerCase().capitalizeWords();
			kase.set("attorney", the_attorney.user_id);
			kase.set("attorney_full_name", the_attorney_full_name);
		}
	} 
	if (the_attorney_full_name.trim()!="") {
		if (typeof the_attorney_id == "undefined") {
			if (isNaN(kase.get("supervising_attorney"))) {
				var the_worker = worker_searches.findWhere({nickname:kase.get("attorney")});
			} else {
				var the_worker = worker_searches.findWhere({id:kase.get("attorney")});
			}
			if (typeof the_worker != "undefined") {
				the_worker = the_worker.toJSON();
				the_attorney_id = the_worker.id;
			}
		}
		if (blnDoNotAutoNotify) {
			the_attorney_full_name = "<a class='notify_stack_link notify_stack_link_" + theid + " white_text' id='notify_stack_" + the_attorney_id + "_" + theid + "' title='Click to notify the case attorney' style='cursor:pointer'>" + the_attorney_full_name + "</a>";
		}
		arrWorkers.push("ATTY:&nbsp;" + the_attorney_full_name);
	}
	
	if (!loading && !blnDoNotAutoNotify) {
		var attorney_full_name = kase.get("attorney_full_name");
		if (attorney_full_name.trim()!="") {
			$("#stack_notify_" + theid).tokenInput("add", {id: kase.get("attorney"), name: attorney_full_name});
		}
	}
	
	var the_worker_full_name = kase.get("worker_full_name");
	if (the_worker_full_name=="") {
		var the_worker = worker_searches.findWhere({nickname:kase.get("worker")});
		if (typeof the_worker != "undefined") {
			the_worker = the_worker.toJSON();
			the_worker_id = the_worker.id;
			the_worker_full_name = the_worker.user_name.toLowerCase().capitalizeWords();
			kase.set("worker", the_worker.user_id);
			kase.set("worker_full_name", the_worker_full_name);
		}
	} 
	if (the_worker_full_name.trim()!="") {
		if (typeof the_worker_id == "undefined") {
			if (isNaN(kase.get("worker"))) {
				var the_worker = worker_searches.findWhere({nickname:kase.get("worker")});
			} else {
				var the_worker = worker_searches.findWhere({id:kase.get("worker")});
			}
			if (typeof the_worker != "undefined") {
				the_worker = the_worker.toJSON();
				the_worker_id = the_worker.id;
			}
		}
		if (typeof the_worker_id != "undefined") {
			if (blnDoNotAutoNotify) {
				the_worker_full_name = "<a class='notify_stack_link notify_stack_link_" + theid + " white_text' id='notify_stack_" + the_worker_id + "_" + theid + "' title='Click to notify the case coordinator' style='cursor:pointer'>" + the_worker_full_name + "</a>";
			}
			arrWorkers.push("COORD:&nbsp;" + the_worker_full_name);
		}
	}
	
	if (!loading && !blnDoNotAutoNotify) {
		var worker_full_name = kase.get("worker_full_name");
		if (worker_full_name.trim()!="") {
			$("#stack_notify_" + theid).tokenInput("add", {id: kase.get("worker"), name: worker_full_name});
		}
	}
	if (arrWorkers.length > 0) {
		if (blnDoNotAutoNotify) {
			var all_workers = "<a class='notify_stack_link notify_stack_link_" + theid + " white_text' id='notify_stack_all_" + theid + "' title='Click to notify all the case employees' style='cursor:pointer'>ALL</a>";
			arrWorkers.push(all_workers);
		}
		$("#notify_employees_" + theid).html(arrWorkers.join("&nbsp;|&nbsp;"));
	}
}