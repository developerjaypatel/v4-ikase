window.webmail_listing_view = Backbone.View.extend({

    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
	events: {
		"click .save_icon":						"saveWebmail",
		"click .message_action": 				"reactMessage",
		"click .expand_webmail": 				"expandWebmail",
		"click .shrink_webmail": 				"shrinkWebmail",
		"click .check_all": 					"checkAll",
		"click .delete_webmail": 				"confirmdeleteWebmail",
		"click .assign_webmail": 				"assignWebmail",
		"change .kase_input_select": 			"changeMassEmail",
		"click .email_attach_link":				"previewAttachment",
		"click #refresh_webmail":				"refreshWebmail",
		"click #webmail_listing_all_done":		"doTimeouts"
	},
    render:function () {		
		var self = this;
		
		this.collection.bind("reset", this.render, this);
		
		var webmails = this.collection.toJSON();
		_.each( webmails, function(webmail) {
			if (webmail.case_id != "") {
				return;
		    }
			if (webmail.attachments > 0) {
				webmail.attachments = "<span title='" + webmail.attachments + " Attachment(s)'>&#128206;</span>"
			} else {
				webmail.attachments = "&nbsp;";
			}
			webmail.timeofday = "";
			if (isDate(webmail.date)) {
				webmail.timeofday = moment(webmail.date).format("hh:mma");
			}
			/*
			if (webmail.subject.length > 60) {
				webmail.subject = webmail.subject.substr(0, 60) + " ...";
			}
			*/
			
			if (webmail.attach_files!="") {
				webmail.attach_files = "<span style='font-size:1.5em; font-weight:bold'>Attachments:&nbsp;</span>" + webmail.attach_files;
			}
		});
		
		try {
			$(this.el).html(this.template({webmails: webmails}));
		}
		catch(err) {
			var view = "webmail_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		tableSortIt("webmail_listing");
		
		return this;
    },
	expandWebmail: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		//find the message_id		
		//$(".webmail_listing #expand_" + id).hide();
		//$(".webmail_listing #shrink_" + id).show();
		

		$("#preview_pane").html(loading_image);
		//then go get the actual body
		var url = 'api/webmail/read/' + id;	//webmail.get("message_id");
		$.ajax({
			url:url,
			type:'GET',
			dataType:"text",
			data: "",
				success:function (data) {
					//$("#webmail_body_" + id).html(data);
					var $frame = $('<iframe style="width:100%; height:600px;background:white" frameborder="0">');
					//$("#webmail_body_" + id).html( $frame );
					var webmail_subject = $("#webmail_subject_" + id).html();
					var webmail_from = $("#webmail_from_" + id).html();
					var webmail_date = $("#webmail_date_" + id).val();
					var webmail_attachments = $("#webmail_attachments_" + id).html();
					var webmail_links = '<a id="reply_' + id + '" data-toggle="modal" data-target="#myModal4" style="cursor:pointer" class="message_action"><i style="font-size:12px;color:#FCC" class="glyphicon glyphicon-arrow-left" title="Click to Reply"></i>&nbsp;Reply</a><br><a id="forward_' + id + '" data-toggle="modal" data-target="#myModal4" style="cursor:pointer" class="message_action"><i style="font-size:13px;color:#66FF99" class="glyphicon glyphicon-arrow-right" title="Click to Forward"></i>&nbsp;Forward</a><br><a title="Click to delete Email.  Warning: this will permanently delete the email both on iKase and on your mail server" class="list_edit delete_webmail" id="deletewebmail_' + id + '" style="cursor:pointer"><i style="font-size:13px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash list_edit delete_webmail" title="Click to delete Email.  Warning: this will permanently delete the email both on iKase and on your mail server"></i>&nbsp;Delete</a>';
					var the_preview_title = "<div style='width:100%; background:#CCC; color:black; padding:3px'><div style='float:right; width:70px; text-align:left'>" + webmail_links + "</div><div style='font-size:1.3em; font-weight:bold'>" + webmail_subject + "</div><div>From:" + webmail_from + "</div><div>Date:" + webmail_date + "</div>";
					if (webmail_attachments!="") {
						webmail_attachments = webmail_attachments.replaceAll('white_text" style="', 'black_text" style="font-size:0.7em;');
						webmail_attachments = webmail_attachments.replaceAll("1.5em", "1em");
						//webmail_attachments = webmail_attachments.replaceAll("<a ", "<a style='font-size:1.em' ");
						the_preview_title += "<div style='font-size:1.em'>" + webmail_attachments + "</div>";
					}
					the_preview_title += "</div>";
					$("#preview_title").html(the_preview_title);
					$("#preview_pane").html( $frame );
					setTimeout( function() {
						var doc = $frame[0].contentWindow.document;
						var $body = $("body",doc);
						$body.html(data);
					}, 1 );
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) { 
					$("#webmail_body_" + id).html("Error: " + errorThrown); 
				} 
			});
	},
	shrinkWebmail: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$(".webmail_listing #shrink_" + id).hide();
		$(".webmail_listing #expand_" + id).show();
		$(".webmail_listing .webmail_row_" + id + "_details").fadeOut();
		
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
	},
	refreshWebmail: function(event) {
		console.log("here is a refresh..........");
		refreshWebmail();
	},
	confirmdeleteWebmail: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		composeDelete(id, "webmail");
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
			if (action == "delete_webmail") {
				composeDelete(ids, "webmail");
			}
			if (action == "assign_to_kase") {
				composeEmailAssign(ids, "webmail");
			}
		} else { 
			console.log("no action");
		}
		//composeDelete(id, "webmail");
	},
	assignWebmail: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		$("#check_assign_" + id).prop("checked", true);
		composeEmailAssign(id, "webmail");
	},
	previewAttachment: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		//then go get the actual document
		var filename = element.innerHTML;
		
		window.open("http://173.58.194.150/webattach/" + customer_id + "/" + filename.trim());
		return;

		var data = "id=" + id + "&name=" + filename;
		var url = 'api/webmail/preview';	//webmail.get("message_id");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"text",
			data: data,
				success:function (data) {
					window.open("D:/uploads/" + customer_id + "/webmail_previews/" + filename);
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) { 
					$("#webmail_body_" + id).html("Error: " + errorThrown); 
				} 
			});
	},
	releaseSave: function(theid) {
		$("#disabled_webmail_save_" + theid).fadeOut(function() {
			$("#webmail_save_" + theid).fadeIn();
			if (customer_id == 1033) {
				$("#notes_webmail_case_id_" + theid).fadeIn();
			}
		});
	},
	doTimeouts: function(event) {
		//kaseAutoComplete("webmail_listing", "kase_input");
		var self = this;
		var theme = {
			theme: "event",
			tokenLimit: 1,
			onAdd: function(item) {
				//get the id
				var theid = this[0].id;
				theid = theid.split("_")[2];
				$("#webmail_case_id_" + theid).val(item.id);
				
				self.releaseSave(theid);
				/*
				$("#disabled_webmail_save_" + theid).fadeOut(function() {
					$("#webmail_save_" + theid).fadeIn();
				});
				*/
			}
		};
		
		$(".webmail_listing .kase_input").tokenInput("api/kases/tokeninput", theme);	
		$(".webmail_listing .token-input-list-event").css("width", "270px");
		$(".webmail_listing .token-input-dropdown-event").css("width", "290px");		
		
		//maybe already assigned
		var webmails = this.collection.toJSON();
		_.each( webmails, function(webmail) {
			if (webmail.case_id != "") {		
				var kase = kases.findWhere({case_id: webmail.case_id});
				//might not be a valid id...
				if (typeof kase != "undefined") {
					//add the kase
					$("#webmail_case_" + webmail.id).tokenInput("add", {
						id: webmail.case_id, 
						name: kase.name(),
						tokenLimit:1
					});

				}
			}
		});		
	},
	reactMessage: function(event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrID = element_id.split("_");
		this.saveWebmail(event, arrID[0])
		//save the email as message for inbox
		//then compose reply
	},
	saveWebmail: function(event, post_action) {
		if (typeof post_action == "undefined") {
			post_action = "";
		}
		var current_input = event.currentTarget;
		
		//get the current id
		var arrID = current_input.id.split("_");
		var theid = arrID[arrID.length - 1];
		
		var case_id = $("#webmail_case_id_" + theid).val();
		var webmail_message_id = $("#webmail_message_id_" + theid).val();
		var note = $("#notes_webmail_" + theid).val();
		
		var back_color = $(".kase_webmail_row_" + theid).css("background");
		
		var formValues = { 
			id: theid,
			message_id: webmail_message_id,
			case_id: case_id,
			type:"webmail",
			table_name:"message",
			table_attribute:"webmail"
		};
		var url = "api/webmail/assign";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else {
					if (post_action!="") {
						composeMessage(post_action + "_" + data.message_id);
						return;
					}
					//hide the save button
					//$("#webmail_save_" + theid).hide();
					$("#webmail_save_" + theid).fadeOut(function() {
						$("#disabled_webmail_save_" + theid).fadeIn();
					});
					
				}
			}
		});
		var formValuesNotes = "note=" + note + "&case_id=" +  case_id + "&type=general&table_name=notes";
		var urlNotes = "api/notes/add";
		
		$.ajax({
			url:urlNotes,
			type:'POST',
			dataType:"json",
			data: formValuesNotes,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else {
					
					$(".kase_webmail_row_" + theid).css("background", "green");
					setTimeout(function() {
						//hide the processed row, no longer an outstanding email
						$(".webmail_row_" + theid).fadeOut();
						//$(".kase_webmail_row_" + theid).css("background", back_color);
					}, 2500);
				}
			}
		});
	},
	saveBulkWebmail: function(element_id) {
		//get the current id
		var arrIDs = element_id.split(", ");
		
		var theid = arrID[arrID.length - 1];
		
		var case_id = $("#webmail_case_id_" + theid).val();
		var webmail_message_id = $("#webmail_message_id_" + theid).val();
		var formValues = { 
			id: theid,
			message_id: webmail_message_id,
			case_id: case_id,
			type:"webmail",
			table_name:"message",
			table_attribute:"webmail"
		};
		var url = "api/webmail/assign";
		
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
					//$("#webmail_save_" + theid).hide();
					$("#webmail_save_" + theid).fadeOut(function() {
						$("#disabled_webmail_save_" + theid).fadeIn();
					});
					//get the color
					var back_color = $(".kase_webmail_row_" + theid).css("background");
					//mark it all green
					$(".kase_webmail_row_" + theid).css("background", "green");
					setTimeout(function() {
						//hide the processed row, no longer a batch scan
						//$(".webmail_row_" + theid).fadeOut();
						$(".kase_webmail_row_" + theid).css("background", back_color);
					}, 2500);
				}
			}
		});
	}
});