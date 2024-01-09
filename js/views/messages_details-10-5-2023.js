window.message_print_view = Backbone.View.extend({
	render: function(){
		var message = this.model.toJSON(); 
		
		if (typeof message.first_column_label != "undefined") {
			var arrToUserNames = [];
			if (message.first_column_label=="To") {
				//lookup all the user_name
				var arrToUsers = message.message_to.split(";");
				arrayLength = arrToUsers.length;
				for (var i = 0; i < arrayLength; i++) {
					var theworker = worker_searches.findWhere({"nickname": arrToUsers[i]});
					if (typeof theworker != "undefined") {
						arrToUserNames[arrToUserNames.length] = theworker.get("user_name");
					} else {
						arrToUserNames[arrToUserNames.length] = arrToUsers[i];
					}
				}
				message.message_to = arrToUserNames.join(";");
			}
		}
		$(this.el).html(this.template(message));
		
		return this;
	}
});
window.message_print_view1 = Backbone.View.extend({
	render: function(){
		var message = this.model.toJSON(); 
		
		if (typeof message.first_column_label != "undefined") {
			var arrToUserNames = [];
			if (message.first_column_label=="To") {
				//lookup all the user_name
				var arrToUsers = message.message_to.split(";");
				arrayLength = arrToUsers.length;
				for (var i = 0; i < arrayLength; i++) {
					var theworker = worker_searches.findWhere({"nickname": arrToUsers[i]});
					if (typeof theworker != "undefined") {
						arrToUserNames[arrToUserNames.length] = theworker.get("user_name");
					} else {
						arrToUserNames[arrToUserNames.length] = arrToUsers[i];
					}
				}
				message.message_to = arrToUserNames.join(";");
			}
		}
		message.dateandtime = moment(message.dateandtime).format('MM/DD/YY hh:mmA');
		
		$(this.el).html(this.template(message));
		
		
		return this;
	}
});
window.message_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	 events:{
        "click .interoffice .delete":				"deleteMessageView",
		"click .interoffice .save":					"addMessageView",
		"click .interoffice.btn.btn-primary.save":	"addMessageView",
		"click .interoffice .save_field":			"saveMessageViewField",
		"click .interoffice .edit": 				"toggleMessageEdit",
		"click #select_all_clients":				"selectAllClients",
		"click .interoffice .reset": 				"resetInjuryForm",
		"click .kase .calendar": 					"showCalendar",
		"click .show_users": 						"messageUsers",
		"keyup .interoffice .input_class": 			"valueMessageViewChanged",
		"dblclick .interoffice .gridster_border": 	"editMessageViewField",
		"click .event_partie":						"selectPartie",
		"click #send_to_all":						"selectAllUsers",
		"click #signature_offer":					"noSignature",
		"click #lookup_another":					"lookupAnotherCase",
		"click #message_view_all_done":				"doTimeouts"
    },
	
    render: function () {
		var self = this;
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		if (typeof this.model.get("subject") == "undefined") {
			this.model.set("subject", "");		
		}
		if (typeof this.model.get("message") == "undefined") {
			this.model.set("message", "");		
		}
		mymodel = this.model.toJSON();
		
		$(this.el).html(this.template(mymodel));
		//$("#address").hide();
        //$('#details', this.el).html(new InjurySummaryView({model:this.model}).render().el);
		
        return this;
    },
	doTimeouts: function (event) {
		var self = this;
		
		//are we forwarding documents
		var arrDocuments = [];
		if (typeof this.model.get("document_id") != "undefined") {
			var arrDocuments = this.model.get("document_id");
		}
		
		this.model.set("editing", false);
		if(this.model.id=="" || this.model.id==-1){
			//editing mode right away
			this.model.set("editing", false);			
			$(".interoffice .edit").trigger("click"); 
			$(".interoffice .delete").hide();
			$(".interoffice .reset").hide();
		}
		
		var topper = $("#modal_save_holder").html();
		if (topper.indexOf("apply_tasks_holder") < 0) {
			$("#modal_save_holder").prepend('<span id="apply_tasks_holder" class="white_text"><input type="checkbox" id="apply_tasks" onchange="showTaskDateBox()">&nbsp;Save as Task</span>&nbsp;&nbsp;|&nbsp;&nbsp;');
		}
		
		var theme = {
				theme: "facebook",
				hintText: "Search for Employees or Type Email Address and hit `Enter`",
				onAdd: function(item) {
					if ($("#message_holder_table").length==1) {
						var blnEmailAddress = (item.name.indexOf("@") > -1);
						if (blnEmailAddress) {
							//offer to use the signature
							self.showSignature();
						}
					}
					
				},
				onDelete: function(item) {
					var destination = this[0].id.replace("message", "emailaddress");
					var arrEmails = $("#" + destination).val().split(",");
					var arrAfters = [];
					arrEmails.forEach(function(element, index, array) {
						if (element!=item.name) {
							arrAfters.push(element);
						}
					});
					$("#" + destination).val(arrAfters.join(","));
				}
		};
		var theme_2 = {
				theme: "message",
				hintText: "Search for Employees or Type Email Address and hit `Enter`",
				onDelete: function(item) {
					//console.log(item);
					//get the list of emails
					
				}
		};
		var theme_case = {
			theme: "facebook", 
			tokenLimit: 1,
			onAdd: function(item) {
				$("#apply_notes_holder").show();
				$("#apply_notes").prop("checked", true);
				if ($("#message_documents_list").length > 0) {
					//var kase = kases.findWhere({case_id: item.id});
					var case_id = item.id;
					var kase =  new Kase({id: case_id});
					
					kase.fetch({
						success: function (kase) {
							//now we have our kase	
							var kase_documents = new MessageAttachments({ case_id: case_id});
							kase_documents.fetch({
								success: function(kase_documents) {
									if (kase_documents.length > 0) {
										var thetitle = "(Select from list to attach Documents)";
										kase.set("list_title", thetitle);
										
										$('#message_documents_list').html(new document_listing_message({collection: kase_documents, model: kase}).render().el);
										//don't show it yet, let the user request it
										setTimeout(function() {
											$("#select_case_documents").show();
											console.log("I am here!! 185");
											if ($("#case_idSpan").html()=="") {
												$("#select_case_documents").parent().css("top", "60px");
											}
											//unless this is specifically about sending documents
											if (arrDocuments.length > 0) {
												setTimeout(function() {
													if ($("#reaction").val()!="senddocument") {
														$("#select_case_documents").trigger("click");
													} else {
														//hide it.
														$("#select_case_documents").hide();
													}
													
													var message_documents = $(".message_document");
													var arrLength = message_documents.length;
													for (var i = 0; i < arrLength; i++) {
														var doc = message_documents[i];
														var arrDocID = doc.id.split("_");
														var doc_id = arrDocID[arrDocID.length - 1];
														if (arrDocuments.indexOf(doc_id) > -1) {
															doc.checked = true;
														}
													}
												}, 1200);
											}
										}, 1000);
									}
								}
							});
						}
					});
				}
			}
		}
		
		$("#message_toInput").tokenInput("api/usercontact", theme);
		$("#message_ccInput").tokenInput("api/usercontact", theme_2);
		$("#message_bccInput").tokenInput("api/usercontact", theme_2);
		if ($("#task_assigneeInput").length > 0) {
			$("#task_assigneeInput").tokenInput("api/user", theme_2);
			$(".assignee .token-input-list-facebook").css("width", "80px");
		}
		
		$("#case_fileInput").tokenInput("api/kases/tokeninput", theme_case);
		$('#callback_dateInput').datetimepicker({ 
			validateOnBlur:false, 
			minDate: 0, 
			timepicker: false,
			format:'m/d/Y'
		});
		
		//is there a thread
		var source_message_id = self.model.get("source_message_id");
		if (source_message_id > 0 || source_message_id == "-42" || source_message_id == "applicant") {
			if (typeof this.model.get("subject") != "undefined" && typeof this.model.get("thread_uuid") != "undefined") {
				$("#subjectInput").val(this.model.get("subject"));
				$("#thread_uuid").val(this.model.get("thread_uuid"));
			} 
			var reaction = self.model.get("reaction");
			if (reaction=="reply" || reaction=="replyall" || reaction=="replythread") {
				var source_message = new Message({message_id: source_message_id});
				//get the source message info
				source_message.fetch({
					success: function (data) {
						if (data.get("id") > 0) {
							$("#subjectInput").val("Re:" + data.get("subject"));
							$("#thread_uuid").val(data.get("thread_uuid"));
						}
					}
				});	
			}
			if (reaction=="replythread") {
				var email_parties = $("#email_parties").val();
				var arrEmailParties = email_parties.split(";");
				arrEmailParties.forEach(function(element, index, array) {
					$("#message_toInput").tokenInput("add", {id: -1, name: element});		
				});
			}
			if (reaction=="reply" || reaction=="replyall") {
				//recipients for a simple reply
				var from_users = new MessageUsers([], {message_id: source_message_id, type: "from"});
				from_users.fetch({
					success: function (data) {
						if (data.length == 0) {
							self.addMessageFrom();
							return;
						}
						_.each( data.toJSON(), function(message_user) {
							$("#message_toInput").tokenInput("add", {id: message_user.user_id, name: message_user.user_name});		
						});
					}
				});	
				
				if (reaction=="replyall") {
					var cc_users = new MessageUsers([], {message_id: source_message_id, type: "cc"});
					cc_users.fetch({
						success: function (data) {
							_.each( data.toJSON(), function(message_user) {
								$("#message_ccInput").tokenInput("add", {id: message_user.user_id, name: message_user.user_name});		
							});
						}
					});
					
					var to_users = new MessageUsers([], {message_id: source_message_id, type: "to"});
					to_users.fetch({
						success: function (data) {
							_.each( data.toJSON(), function(message_user) {
								if (message_user.user_id != login_user_id) {
									$("#message_toInput").tokenInput("add", {id: message_user.user_id, name: message_user.user_name});		
								}
							});
						}
					});
				}
			}
			
			if (reaction=="draft") {
				var cc_users = new MessageUsers([], {message_id: source_message_id, type: "cc"});
				cc_users.fetch({
					success: function (data) {
						_.each( data.toJSON(), function(message_user) {
							$("#message_ccInput").tokenInput("add", {id: message_user.user_id, name: message_user.user_name});		
						});
					}
				});
				
				var to_users = new MessageUsers([], {message_id: source_message_id, type: "to"});
				to_users.fetch({
					success: function (data) {
						_.each( data.toJSON(), function(message_user) {
							if (message_user.user_id != login_user_id) {
								$("#message_toInput").tokenInput("add", {id: message_user.user_id, name: message_user.user_name});		
							}
						});
					}
				});
								
				var bcc_users = new MessageUsers([], {message_id: source_message_id, type: "bcc"});
				bcc_users.fetch({
					subccess: function (data) {
						_.each( data.toJSON(), function(message_user) {
							$("#message_bccInput").tokenInput("add", {id: message_user.user_id, name: message_user.user_name});		
						});
					}
				});
				
				//attachments
				var arrAttachments = this.model.toJSON().attachments.split("|");
				var arrFiles = [];
				for (var i = 0; i < arrAttachments.length; i++) {
					var attach = arrAttachments[i];
					attach = attach.replace("https://", "");
					var arrAttach = attach.split("/");
					var display_attach = arrAttach[arrAttach.length - 1];
					var file_info = '<div class="uploadifive-queue-item complete" id="uploadifive-file_upload-file-' + i + '"><a class="close" href="#">X</a><div><span class="filename"><a href="' + attach + '" target="_blank" title="Click to review imported document">' + display_attach + '</a></span><span class="fileinfo"> - Uploaded</span></div><div class="progress" style="display: none;"><div class="progress-bar" style="width: 100%;"></div></div></div>';
					var filename = display_attach;
					filename = filename.substring(0, 5) + " ...";
					var str_sep = '';
					if($("#send_queue div.comma_sepr_file_div").length > 0) {
						str_sep = ', ';
					}
					//console.log("DETAILS 345 --> Length -->"+$("#send_queue div.comma_sepr_file_div").length);
					$("#send_queue").append("<div class='comma_sepr_file_div' style='float: left;margin-right: 5px;'>"+str_sep+"<a title='"+kase_document.document_filename +"' href='" + kase_document.preview_href + "' target='_blank' title='Click to review attached document' class='white_text'>" + filename + "</a></div>");
						
					arrFiles.push(file_info);
				}
				if (arrFiles.length > 0) {
					setTimeout(function() {
						$("#queue").hide();
						//$(".message_attach_form").append('<div id="queue" style="height: 100px; width: 240px; border: 0px;">' + arrFiles.join("") + '</div>');
						$("#queue").append(arrFiles.join(""));
					}, 1200);
				}
			}
			
			if (reaction == "partie") {
				var recipient = self.model.get("recipient");
				var arrRecipient = "";
				if (recipient.indexOf(";") > -1) {
					var arrRecipient = recipient.split(";");
				}
				if (arrRecipient.length > 1) {
					for(var i =0; i < arrRecipient.length; i++) {
						$("#message_toInput").tokenInput("add", {
							id: "", 
							name: arrRecipient[i]
						});
						$("#emailaddress_toInput").val(arrRecipient);
					}
				} else {
					$("#message_toInput").tokenInput("add", {id: "", name: recipient});
					$("#emailaddress_toInput").val(self.model.get("recipient"));
				}
			}
		}
		if (self.model.get("case_id") < 0) {
			//maybe we missed it
			//var hash = document.location.hash;
			//if (hash.indexOf("#kases/") == 0) {
			if (current_case_id > 0) {
				/*
				var arrHash = hash.split("/");
				self.model.set("case_id", arrHash[1]);
				*/
				self.model.set("case_id", current_case_id);
			}
		}
		if (self.model.get("case_id") > 0) {
			var casing_file = $("#case_fileInput").val();
			if (casing_file == "") {
				//maybe not the case they want
				$("#auto_case_override").show();
				//hide it in 10 seconds
				setTimeout(function() {
					$("#auto_case_override").fadeOut();
				}, 10000);
				var kase = kases.findWhere({case_id: self.model.get("case_id")});
				if (typeof kase != "undefined") {
					$("#case_fileInput").tokenInput("add", {
						id: self.model.get("case_id"), 
						name: kase.name(),
						tokenLimit:1
					});
					
					$("#case_id_holder .token-input-list-facebook").hide();
					$(".case_input .token-input-list-facebook").hide();
					$("#case_idSpan").html(kase.name());
					console.log("I am here!! 413");
				} else {
					console.log("I am here!! 415");
					var kase = new Kase({case_id: self.model.get("case_id")});
					kase.fetch({
						success: function (kase) {
							//add the kase to kases for faster lookup later on
							kases.add(kase);
							$("#case_fileInput").tokenInput("add", {
								id: self.model.get("case_id"), 
								name: kase.name(),
								tokenLimit:1
							});
							
							$("#case_id_holder .token-input-list-facebook").hide();
							$(".case_input .token-input-list-facebook").hide();
							$("#case_idSpan").html(kase.name());
						}
					});
				}
			}
		}
		if (typeof self.model.get("contact_id") != "undefined") {
			var contact = new PersonalContact({contact_id: self.model.get("contact_id")});
			contact.fetch({
				success: function (data) {
					var email = data.toJSON().email;
					$("#message_toInput").tokenInput("add", {id: "", name: email});
					$("#emailaddress_toInput").val(email);	
				}
			});	
		}
		//we might have a default value
		if (self.model.get("case_id") > 0) {
			$("#apply_notes_holder").show();
			$("#apply_notes").prop("checked", true);
			//var kase = kases.findWhere({case_id:self.model.get("case_id")});
			//$("#case_fileInput").tokenInput("add", {id: kase.case_id, name: kase.name()});
		} else {
			$("#apply_notes_holder").hide();
			$("#apply_notes").prop("checked", false);
		}
		
		$("#token-input-message_toInput").css("border","1px #000000 solid");
		
		//we need to upload attachments
		$('#message_attachments').html(new message_attach({model: self.model}).render().el);
		
		//console.log("hererererere");
		setTimeout(function() {
			$("#messageInput").cleditor({
				width:648,
				height: 270,
				controls:     // controls to add to the toolbar
						  "bold italic underline | font size " +
						  "style | color highlight"
			});
			
			$("#queue").css("border", "0px");
		}, 500);
		
		if (typeof this.model.get("invoiced_id") == "undefined") {
			this.model.set("invoiced_id", "");
			this.model.set("invoiced_type", "");
		}
		if (this.model.get("invoiced_id")!="") {
			var corporation = new Corporation({id: this.model.get("invoiced_id"), case_id: this.model.get("case_id")});
			corporation.fetch({
				success: function (corp) {
					var invoiced = corp.toJSON();
					var invoiced_firm = invoiced.company_name;
					var employee_email = invoiced.employee_email;
					$("#message_toInput").tokenInput("add", {id: "", name: employee_email});
					$("#message_to_td").prepend(invoiced_firm);
					
					$("#kinvoice_invoiced_id").val(self.model.get("invoiced_id"));
					$("#kinvoice_invoiced_type").val(self.model.get("invoiced_type"));
				}
			});
			
			//attach the invoice
			$("#kinvoice_id").val(this.model.get("kinvoice_id"));
			$("#kinvoice_path").val(this.model.get("attachments"));
			$("#kinvoice_document_id").val(this.model.get("document_id"));
			var attach = this.model.get("attachments");
			
			var display_attach = "Invoice " + this.model.get("invoice_number");
			var file_info = '<div class="uploadifive-queue-item complete" id="uploadifive-file_upload-file-1"><a class="close" href="#">X</a><div><span class="filename"><a href="api/preview_invoice.php?file=' + attach + '" target="_blank" title="Click to review invoice">' + display_attach + '</a></span><span class="fileinfo"> - Uploaded</span></div><div class="progress" style="display: none;"><div class="progress-bar" style="width: 100%;"></div></div></div>';
			var filename = display_attach;
			filename = filename.substring(0, 5) + " ...";
			var str_sep = '';
			//console.log("DETAILS 500 --> Length -->"+$("#send_queue div.comma_sepr_file_div").length);
			if($("#send_queue div.comma_sepr_file_div").length > 0) {
				str_sep = ', ';
			}
			$("#send_queue").append("<div class='comma_sepr_file_div' style='float: left;margin-right: 5px;'>"+str_sep+"<a title='"+kase_document.document_filename +"' href='" + kase_document.preview_href + "' target='_blank' title='Click to review attached document' class='white_text'>" + filename + "</a></div>");
			
			setTimeout(function() {
				//console.log("abcd");
				$("#queue").hide();
				//$(".message_attach_form").append('<div id="queue" style="height: 100px; width: 240px; border: 0px;">' + arrFiles.join("") + '</div>');
				$("#queue").append(file_info);
			}, 1200);
		}
		if (blnSaveDraft) {
			$(".modal-header .close").on("click", function(event) { 
				var table_id = $(".interoffice #table_id").val();
				if (draft_id == 0 && table_id=="") {
					event.preventDefault();
					
					//make sure something was typed in
					var blnTyped = false;
					var inputs = $(".interoffice input");
					var arrLength = inputs.length;
					for (var i = 0; i < arrLength; i++) {
						if (inputs[i].value!="") {
							blnTyped = true;
							break;
						}
					}
					if (!blnTyped) {
						var inputs = $(".interoffice textarea");
						var arrLength = inputs.length;
						for (var i = 0; i < arrLength; i++) {
							if (inputs[i].value!="") {
								blnTyped = true;
								break;
							}
						}
					}
					if (blnTyped) {
						//they are closing without having saved and this is not an update
						if (confirm("You did not send this message.  Do you want to save a Draft to send later?")) {
							saveDraft();
						}
					}
					/*
					setTimeout(function() {
						$("#myModal4").modal("toggle");
					}, 1500);
					*/
				} else {
					//clear save draft
					clearTimeout(draft_timeout_id); 
				}
			});
			
			setTimeout(function() {
				saveDraft();
			}, 180000);
		}
	},
	messageUsers: function(event) {
		var element = event.currentTarget;
		var theid = element.innerHTML;
		
		var the_width = Number($('#myModal4 .modal-dialog').css("width").replace("px", ""));
		var the_left = Number($('#myModal4 .modal-dialog').css("margin-left").replace("px", ""));
	
		if ($('#message_users_list').css("display")=="none") {
			the_left -= 80;
			the_width += 420;
		}
		$('#myModal4 .modal-dialog').animate({width:the_width, marginLeft: the_left + "px"}, 1100, 'easeInSine', 
		function() {
			//run this after animation
			$('#message_users_list').fadeIn(function() {
				var mymodel = new Backbone.Model();
				mymodel.set("holder", "message_users_list");
				mymodel.set("source", theid);
				$('#message_users_list').html(new user_listing_message({collection: worker_searches, model: mymodel}).render().el);
			});
		});
	},
	noSignature: function() {
		$("#email_signature").html("");
		$("#signatureInput").val("");
		$("#signature_offer").fadeOut();
	},
	showSignature: function() {
		var signature = new Signature({user_id: login_user_id});
		signature.fetch({
			success: function (signature) {
				var no_signature = '<div id="signature_offer_holder" style="float:right; padding-right:45px"><a id="signature_offer" class="white_text" style="cursor:pointer; color:red; background:white" title="Click if you do not want to add your signature to this email">No Signature</a></div>';
				var signature_json = signature.toJSON();
				//var signature_html = signature_json.signature.replaceAll("\r\n", "<br>");
				//signature_html = signature_html.replaceAll("\n", "<br>")
				
				var signature_html = signature_json.signature.replace(new RegExp(String.fromCharCode(13), 'g'), '\r\n');
                var signature_html = signature_html.replaceAll("\r\n", "<br>");
				signature_html = signature_html.replaceAll("\n", "<br>");
                
                if (signature_html.length > 1199) {
                	signature_html = signature_html.getComplete(1200) + " ...";
                }
				
				
				$("#email_signature").html(no_signature + signature_html);
				$("#signatureInput").val(signature_json.signature);
				//console.log(signature);
			}
		});
	},
	lookupAnotherCase: function(event) {
		event.preventDefault();
		$("#auto_case_override").fadeOut(
			function() {
				$("#case_id_holder .token-input-list-facebook").show();
				$(".case_input .token-input-list-facebook").show();
				$("#case_idSpan").html("");
				console.log("I am here!! 622");
			}
		);
	},
	addMessageFrom: function() {
		var source_message_id = this.model.get("source_message_id");
		if (source_message_id > 0) {
			var source_message = new Message({message_id: source_message_id});
			//get the source message info
			source_message.fetch({
				success: function (data) {
					//might be a compound name
					var from = data.get("from");
					var arrFrom = data.get("from").split("|");
					if (arrFrom.length==2) {
						from = arrFrom[1];
						var from_name = arrFrom[0];
						if (from_name=="") {
							from_name = arrFrom[1];
						}
						data.set("from", from_name + " <" + arrFrom[1] + ">")
					}
					if (from.indexOf("@") > 0) {
						$("#message_toInput").tokenInput("add", {id: "", name: from});
						$("#emailaddress_toInput").val(data.get("from"));
					}
				}
			});
		}
	},
	selectPartie: function (event) {
		var element = event.currentTarget;
		var theid = element.id.split("_")[2];
		
		var thename = $("#event_partie_name_" + theid).html();
		thename = thename.split("<br>")[0].trim();
		var theaddress = $("#event_partie_address_" + theid).html();
		theaddress = theaddress.replace("<br>", "").trim();
		$("#messageInput").val(thename + " - " + theaddress);
	},
	editMessageViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".interoffice_" + field_name;
		}
		editField(element, master_class);
	},
	editMessageViewNotesField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#notesGrid").hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".interoffice_" + field_name;
		}
		//editField(element, master_class);
	},
	
	saveMessageViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		var element_id = event.currentTarget.id;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.parentElement.parentElement.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("SaveLink", "");
			master_class = ".interoffice_" + field_name;
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
		this.addMessageView(event);
	
	},
	deleteMessageView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "message");
		return;
    },
	selectAllUsers: function(event) {
		var arrWorkers = worker_searches.toJSON();
		arrWorkers.forEach(function(element, index, array) {
			$("#message_toInput").tokenInput("add", {id: element.user_id, name: element.nickname});
		});
	},
	selectAllClients: function(event) {
		$("#token-input-message_toInput").val("All");
		$("#message_toInput").val("All");
		$(".cc_row").fadeOut(function(){
			$("#select_all_holder").css("margin-right", "30px");
		});
	},
	toggleMessageEdit: function(event) {
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
		$(".interoffice .editing").toggleClass("hidden");
		$(".interoffice .span_class").removeClass("editing");
		$(".interoffice .input_class").removeClass("editing");
		
		$(".interoffice .span_class").toggleClass("hidden");
		$(".interoffice .input_class").toggleClass("hidden");
		$(".interoffice .input_holder").toggleClass("hidden");
		$(".button_row.interoffice").toggleClass("hidden");
		$(".edit_row.interoffice").toggleClass("hidden");
	},
	
	resetMessageForm: function(event) {
		event.preventDefault();
		this.toggleInjuryEdit(event);
		//this.render();
		//$("#address").hide();
	},
	valueMessageViewChanged: function(event) {
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
function showTaskDateBox() {
	if ($("#apply_tasks").prop("checked")) {
		$("#follow_up_holder").css("background", "orange");
		$("#follow_up_holder").css("font-weight", "bold");
		$("#follow_up_label").html("Task Due Date:");
		
		$("#message_task_assignee").fadeIn();
	} else {
		$("#follow_up_holder").css("background", "none");
		$("#follow_up_holder").css("font-weight", "normal");
		$("#follow_up_label").html("Follow Up Date:");
		
		$("#message_task_assignee").fadeOut();
	}
	$("#follow_up_row").fadeIn();
}