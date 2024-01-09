window.thread_listing = Backbone.View.extend({
    initialize:function () {
		this.collection.on("change", this.render, this);
		this.loop();
	},
	loop:function(){
		setInterval(()=>this.collection.on("change", this.render, this),10*1000);
	},
	events: {
		"click #new_thread": 						"newThread",
		"click .thread_action": 					"reactThread",
		"click .delete_thread": 					"confirmdeleteThread",
		"click .unread_thread":						"markUnread",
		"click .delete_yes":						"deleteThread",
		"click .delete_no":							"canceldeleteThread",
		"click .open_thread":						"expandThread",
		"click .thread_data_row":					"expandThread",
		"click #hide_preview_pane":					"shrinkThread",
		"click #unread_threads":					"unreadThreads",
		"click #unread_drafts":						"listDrafts",
		"click .all_messages":						"allThreads",
		"click #refresh_thread":					"refreshWebmail",
		"mouseover .thread_data_row":				"overRow",
		"mouseout .thread_data_row":				"outRow",
		"click .open_threads":						"openDayThreads",
		"click .assign_webmail": 					"assignWebmail",
		"click .assign_threadmail": 				"assignThreadmail",
		"mouseover #thread_preview_panel": 			"freezePreview",
		"mouseover .thread_preview_link": 			"freezePreview",
		"keyup #thread_searchList":					"findIt",
		"click #thread_clear_search":				"clearSearch",
		"click .read_date":							"showReadDates",
		"click #label_search_threads":				"Vivify",
		"click #thread_searchList":					"Vivify",
		"focus #thread_searchList":					"Vivify",
		"blur #thread_searchList":					"unVivify",
		"click #slide_left":						"slideLeft",
		"click #slide_right":						"slideRight",
		"click .btn_assign":						"assignPending",
		"click .btn_review_pending":				"reviewPending",
		"click .btn_accept_pending":				"acceptPending",
		"click .btn_dismiss_pending":				"dismissPending",
		"click .btn_block_pending":					"blockPending",
		"click .approve_complete":					"approveRequest",
		"click .reject_complete":					"rejectRequest",
		
		"click #assign_bulk":						"assignBulk",
		"click #accept_bulk":						"acceptBulk",
		"click #dismiss_bulk":						"dismissBulk",
		"click #block_bulk":						"blockBulk",
		
		"click #activate_email":					"activateEmail",
		"click .check_thread":						"selectThread"
	},
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "thread_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}		
		var self = this;
		
		if (typeof this.model.get("homepage") == "undefined") {
			this.model.set("homepage", false);
		}
		this.model.set("message_link_clicked", false)
		this.model.set("process_thread", false);
		this.collection.bind("reset", this.render, this);
		var  threads = this.collection.toJSON();
		//console.log('threads: ',threads)
		var title = this.model.get("title");
		var blnOutbox = (title=="Outbox");
		var unread_messages_count = 0;
		_.each( threads, function(thread) {
			// Auto Assing to Case logic start
			// console.log("thhhhhhhhhhhhhhhhhh : ",thread.case_id)
			if(thread.case_id == "-1" || thread.case_id == -1)
			{
				// console.log("thread:",thread)
				// console.log("thread.snippet:",thread.snippet)
				var y = thread.snippet.split("// ID ")
				if(y.length > 1 && thread.id !== "6111")
				{
					var case_id = y[1].split(" ")[0];
					var formValues = "id=" + thread.max_message_id + "&case_id=" + case_id + "&type=webmail&table_name=message&table_attribute=webmail&billing_time=0&case_note=&case_attach="+thread.thread_attachments;
					
					// console.log("formmmm",formValues,thread);
					var url = "api/gmail/assign";
					$.ajax({
						url: url,
						type: 'POST',
						dataType: "json",
						data: formValues,
						success: function (data) {
							if (data.error) {  // If there is an error, show the error messages
								console.log(data.error.text);
							} else {
								thread.message_status = ""
								thread.case_name = data.case_name
							}
						}
					});
				}

			}

			// End
       		if (blnOutbox) {
				thread.read_status = "Y";
            }
			var attach_indicator = "none";
			var word_indicator = "none";
			var pdf_indicator = "none";
			var excel_indicator = "none";
            var attach_link = "";
			
			//display time
			if (moment().format("MMDDYY")!=moment(thread.dateandtime).format("MMDDYY")) {
            	thread.display_time = moment(thread.dateandtime).format("MMM D");
            } else {
	            thread.display_time = moment(thread.dateandtime).format("h:mm a");
            }
			
			if (typeof thread.message_status == "undefined") {
				thread.message_status = "";
			}
			//clean snippet
			//but keep the <br>
			var pure_snippet = thread.snippet;
			pure_snippet = pure_snippet.replaceAll("<br>", "~~");
			pure_snippet = removeHtml(pure_snippet);
			pure_snippet = pure_snippet.replaceAll("~~", ", ");
			pure_snippet = pure_snippet.replaceAll(", ; ", "");
			if (pure_snippet.indexOf(", ") == 0) {
				pure_snippet = pure_snippet.substr(2);
			}
			/*
			if (pure_snippet!=thread.snippet) {
				thread.snippet = "";
			}
			*/
			if (pure_snippet.length > 75) {
				//pure_snippet = pure_snippet.substring(0, 75);
				pure_snippet = pure_snippet.getComplete(75) + "&nbsp;&#8230;";
			}
			thread.snippet = pure_snippet;
			//did you read it
			thread.read_indicator = "";
			thread.unread_link = '&nbsp;<a class="list_edit unread_thread white_text" id="unreadthread_' + thread.id + '" style="cursor:pointer; display:none"><i style="font-size:13px; color:#FFF; cursor:pointer" class="glyphicon glyphicon-eye-close" title="Click to mark as Not Read"></i></a>';
			//these are threads so...
			/*
			if (thread.sender == login_username) {
				thread.read_status = "Y";
			}
			*/
			if (thread.read_status=="Y") {
				thread.read_image = "";
				thread.read_status = "background:#4F5669";
				thread.unread_link = '&nbsp;<a class="list_edit unread_thread white_text" id="unreadthread_' + thread.id + '" style="cursor:pointer"><i style="font-size:13px; color:#FFF; cursor:pointer" class="glyphicon glyphicon-eye-close" title="Click to mark as Not Read"></i></a>';
			} else {
				//thread.read_image = '<img src="img/oie_10234757zr1fW7ZB_final.gif" width="15px" height="15px" id="read_image_' + thread.id + '" />';
				thread.read_image = '';
				//thread.read_status = "background:#330033";
				thread.read_status = "background:#337AB7";
				thread.read_indicator = "unread_thread";
				
				unread_messages_count++;
			}
			//sender
			var sender = thread.sender;
			if (sender.indexOf("|") > -1) {
				var arrFrom = sender.split("|");
				//is it an email
				var link_style = "";
				if (arrFrom[1].length > 35) {
					link_style = " style='font-size:0.8em'";
				}
				if (arrFrom[1].indexOf("@") > 0) {
					arrFrom[1] = "<a href='mailto:" + arrFrom[1] + "' class='white_text'" + link_style + ">" + arrFrom[1] + "</a>";
				}
				if (arrFrom[0]=="") {
					thread.sender = arrFrom[1];
				} else {
					thread.sender = arrFrom.join("<br>");
				}
			} else {
				//is it an email
				if (thread.sender.indexOf("@") > 0) {
					var link_text = thread.sender;
					if (thread.sender.length > 35) {
						link_text = thread.sender.replace("@", "@<br>");
					}
					//thread.sender = "<a href='mailto:" + thread.sender + "' class='white_text'>" + link_text + "</a>";
					thread.sender = link_text;
				}
			}
			
			//subject
			if (thread.subject.length > 155) {
				//thread.subject = thread.subject.substring(0, 155);
				thread.subject = thread.subject.getComplete(155);
			}
			thread.subject = '<div style="font-weight:bold" class="dont-break-out" id="thread_subject_<%=thread.id %>">' + thread.subject + '</div>';
			if (thread.case_name==null) {
				thread.case_name = "";
			}
			if (thread.case_name=="") {
				thread.case_name = thread.subject;
			} else {
				thread.case_name = '<a href="?n=#kase/' + thread.case_id + '" title="Click to review kase" class="list-item_kase" style="background:black;color:white" target="_blank">' + thread.case_name.toUpperCase() + '</a>' + thread.subject;
			}
			thread.subject = "";
			var arrAttachment = [];
			
			if (thread.message_attachments!="") {
				thread.thread_attachments = thread.message_attachments;
				//thread.thread_attachments = thread.thread_attachments.replaceAll(",", "|");
			}
            if (thread.thread_attachments!="") {
				thread.thread_attachments = thread.thread_attachments.replaceAll(";", "|");
				thread.thread_attachments = thread.thread_attachments.replaceAll(",", "|");
				//remove trailing pipe
				if (thread.thread_attachments.charAt(thread.thread_attachments.length - 1)=="|") {
					the_thread_attachments = thread.thread_attachments.substring(0, thread.thread_attachments.length - 1);
				} else {
					the_thread_attachments = thread.thread_attachments;
				}
				email_attachment = "";
				var arrAttach = the_thread_attachments.split("|");
				arrAttach = arrAttach.unique();
				var arrAttachedFiles = [];
				var arrayLength = arrAttach.length;
				for (var i = 0; i < arrayLength; i++) {
					var attachment = arrAttach[i];
					if (attachment.trim()=="") {
						continue;
					}
					if (thread.thread_type=="email" && attachment!="") {
						attach_link = "https://www.ikase.xyz/ikase/gmail/ui/" + attachment;
						email_attachment = '<input type="checkbox" value="' + attachment + '" class="kase_attach" id="kase_attach_' + i + '" />&nbsp;';
						var arrLink = attachment.split("/");
						attachment = arrLink[arrLink.length - 1];
					} else {
						attachment = attachment.replace("https:///uploads", "../uploads");
						attachment = attachment.replace("../uploads/" + thread.customer_id + "/" + thread.case_id + "/", "");
						attachment = attachment.replace("../uploads/" + thread.customer_id + "/", "");
						if (thread.case_id=="" || thread.case_id=="-1") {
							//attach_link = "../uploads/" + thread.customer_id + "/" + attachment;
							attach_link = "api/preview_attach.php?file=" +  encodeURIComponent(attachment);
						} else {
							//attach_link = "../uploads/" + thread.customer_id + "/" + thread.case_id + "/" + attachment;
							attach_link = "api/preview_attach.php?case_id=" + thread.case_id + "&file=" + encodeURIComponent(attachment);
						}
					}
					
					//use this going forward
					if (moment(thread.dateandtime) < moment("2016-05-22")) {
						thread.thread_type = "email2016";
					}
					attach_link = email_attachment + '<a id="kase_attach_link_' + i + '" href="' + attach_link + '" target="_blank" title="Click to review ' + attachment + '">' + attachment + '</a>';
					
					if (!inArray(attachment, arrAttachedFiles)) {
						arrAttachment.push(attach_link);
						arrAttachedFiles.push(attachment);
					}
				}
            }
			thread.attach_link = "";
			var pdf_count = 0;
			var word_count = 0;
			var excel_count = 0;
			if (arrAttachment.length > 0) {
				var attachment_list = arrAttachment.join("<br>");
				thread.attach_link = "<div id='attach_link_" + thread.id + "' style='display:none'>" + attachment_list + "</div>";
				attach_indicator = "";
				
				//word indicator
				if (attachment_list.indexOf(".doc") > -1) {
					arrAttachment.forEach(function(element) { 
						if (element.indexOf(".doc") > -1) {
							word_count++;
						}
					});
					word_indicator = "";
				}
				//excel indicator
				if (attachment_list.indexOf(".xls") > -1 || attachment_list.indexOf(".csv") > -1) {
					arrAttachment.forEach(function(element) { 
						if (element.indexOf(".xls") > -1 || element.indexOf(".csv") > -1) {
							excel_count++;
						}
					});
					excel_indicator = "";
				}
				//pdf indicator
				if (attachment_list.indexOf(".pdf") > -1) {
					arrAttachment.forEach(function(element) { 
						if (element.indexOf(".pdf") > -1) {
							pdf_count++;
						}
					});
					pdf_indicator = "";
				}
			}
			if (pdf_count > 1) {
				pdf_count = " (" + pdf_count + ")";
			} else {
				pdf_count = "";
			}
			thread.pdf_count = pdf_count;
			if (word_count > 1) {
				word_count = " (" + word_count + ")";
			} else {
				word_count = "";
			}
			thread.word_count = word_count;
			if (excel_count > 1) {
				excel_count = " (" + excel_count + ")";
			} else {
				excel_count = "";
			}
			thread.excel_count = excel_count;
			thread.attach_indicator = attach_indicator;
			thread.pdf_indicator = pdf_indicator;
			thread.word_indicator = word_indicator;
			thread.excel_indicator = excel_indicator;
			if (thread.priority=="high") {
				thread.subject = "<div style='float:right' class='blink'>HIGH PRIORITY</div>" + thread.subject;
			}
			
			//messages
			if (thread.message_count==1) {
				thread.message_count = "";
			} else {
				thread.message_count = " (" + thread.message_count + ")";
			}
		 });
		
		$(this.el).html(this.template({threads: threads, title: this.model.get("title"), homepage: this.model.get("homepage")}));
		
		setTimeout(function(){
			var size = 100;
			if (self.model.get("homepage")) {
				size = 20;
			}
			tableSortIt("thread_listing", size);
			
			var title = self.model.get("title");
			
			if (title!="Outbox") {
				var unread_drafts = new DraftsCount();
				unread_drafts.fetch({
					success: function(data) {
						$("#unread_drafts").html("Drafts (" + data.toJSON().draft_count + ")");
					}
				});
			} else {
				$("#show_threads").fadeOut();
				$("#show_drafts").fadeOut();
				$("#refresh_webmail").fadeOut();
				
			}
			
			//resize
			$("#thread_list_outer_div").css("height", (window.innerHeight - 140) + "px");
			if (self.model.get("title") == "Pending Emails") {
				$("#new_thread").hide();
			}
			
			if (document.location.hash == "#thread/inboxnew") {
				$("#unread_threads").hide();
				$("#all_threads").show();
			} else {
				$("#unread_threads").html("Unread Messages (" + unread_messages_count + ")");
			}
			
			//sync
			var active_email = new ActiveEmail({user_id: login_user_id});
			active_email.fetch({
				success: function(data) {
					var data_all = data.toJSON();
					console.log(data_all);
					var data_all_len = data_all.insert.length;
					console.log(data_all_len);
					if (data_all_len > 0) {
						$("#refresh_thread").show();
						document.getElementById("refresh_thread").title = "Sync with your " + data.get("email_address") + " account";
					} else {
						$("#activate_email").show();
					}
				}
			});
		}, 100);
		
		setTimeout(function(){
			$(".pager").hide();
		}, 150);
		
		return this;
    },
	slideLeft: function(event) {
		var left_width = parseInt($("#thread_list_outer_div")[0].style.width);
		var right_width = parseInt($("#preview_pane_holder")[0].style.width);
		
		left_width = left_width - 5;
		right_width = right_width + 5;
		
		if (right_width > 70) {
			right_width = 70;
			left_width = 30;
		}
		
		$("#thread_list_outer_div")[0].style.width = left_width + "%";
		$("#preview_pane_holder")[0].style.width = right_width + "%";
		
		writeCookie('left_width', left_width, 24*60*60*1000);
		writeCookie('right_width', right_width, 24*60*60*1000);
		
		cookie_left_width = left_width;
		cookie_right_width = right_width;
	},
	slideRight: function(event) {
		var left_width = parseInt($("#thread_list_outer_div")[0].style.width);
		var right_width = parseInt($("#preview_pane_holder")[0].style.width);
		
		left_width = left_width + 5;
		right_width = right_width - 5;
		
		if (left_width > 70) {
			left_width = 70;
			right_width = 30;
		}
		
		$("#thread_list_outer_div")[0].style.width = left_width + "%";
		$("#preview_pane_holder")[0].style.width = right_width + "%";
		
		writeCookie('left_width', left_width, 24*60*60*1000);
		writeCookie('right_width', right_width, 24*60*60*1000);
		
		cookie_left_width = left_width;
		cookie_right_width = right_width;
	},
	acceptBulk:function(event) {
		var self = this;
		
		event.preventDefault();
		this.model.set("process_thread", true);
		
		//cycle through checkboxes, block them one by one
		var check_threads = document.getElementsByClassName("check_thread");
		var arrLength = check_threads.length;
		var arrThreads = [];
		for (var i = 0; i < arrLength; i++) {
			var element = check_threads[i];
			if (element.checked) {
				var elementArray = element.id.split("_");
				var id = elementArray[elementArray.length - 1];
				
				$("#accept_" + id).trigger("click");
			}
		}
	},
	acceptPending: function(event) {
		var self = this;
		this.model.set("process_thread", true);
		var element = event.currentTarget;
		
		var arrID = element.parentElement.id.split("_");
		var thread_id = arrID[arrID.length - 1];
		
		$("#accept_" + thread_id).fadeOut();
		if (!confirm("This email has not yet been assigned to a Kase.  Please click OK to proceed, or Cancel if you want to assign the email to a Kase.")) {
			//cancelled
			$("#review_" + thread_id).trigger("click");
			return;
		}

		event.preventDefault();
		
		
		//change the status to empty
		var url = "api/thread/confirm_email";
		formValues = "id=" + thread_id;
					
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//it's been processed now
					self.model.set("process_thread", false);
					if ($("#hide_preview_pane").length > 0) {
						$("#hide_preview_pane").trigger("click");
					}
					$(".threads_row_" + thread_id).css("background", "green");
					setTimeout(function() {
						$(".threads_row_" + thread_id).fadeOut();
						
						checkInbox();
					}, 2500);
				}
			}
		});
	},
	dismisstBulk:function(event) {
		var self = this;
		
		event.preventDefault();
		this.model.set("process_thread", true);
		
		//cycle through checkboxes, block them one by one
		var check_threads = document.getElementsByClassName("check_thread");
		var arrLength = check_threads.length;
		var arrThreads = [];
		for (var i = 0; i < arrLength; i++) {
			var element = check_threads[i];
			if (element.checked) {
				var elementArray = element.id.split("_");
				var id = elementArray[elementArray.length - 1];
				
				$("#dismiss_" + id).trigger("click");
			}
		}
	},
	dismissPending: function(event) {
		var self = this;
		
		event.preventDefault();
		this.model.set("process_thread", true);
		var element = event.currentTarget;
		
		var arrID = element.parentElement.id.split("_");
		var thread_id = arrID[arrID.length - 1];
		
		//change the status to empty
		var url = "api/threads/delete";
		formValues = "id=" + thread_id;
					
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//it's been processed now
					self.model.set("process_thread", false);
					//close the preview if any
					if ($("#hide_preview_pane").length > 0) {
						$("#hide_preview_pane").trigger("click");
					}
					$(".threads_row_" + thread_id).css("background", "red");
					setTimeout(function() {
						$(".threads_row_" + thread_id).fadeOut();
						
						checkInbox();
					}, 2500);
				}
			}
		});
	},
	blockBulk: function(event) {
		var self = this;
		
		event.preventDefault();
		this.model.set("process_thread", true);
		
		//cycle through checkboxes, block them one by one
		var check_threads = document.getElementsByClassName("check_thread");
		var arrLength = check_threads.length;
		var arrThreads = [];
		for (var i = 0; i < arrLength; i++) {
			var element = check_threads[i];
			if (element.checked) {
				var elementArray = element.id.split("_");
				var id = elementArray[elementArray.length - 1];
				
				$("#block_" + id).trigger("click");
			}
		}
	},
	blockPending: function(event) {
		var self = this;
		
		event.preventDefault();
		this.model.set("process_thread", true);
		var element = event.currentTarget;
		
		var arrID = element.parentElement.id.split("_");
		var thread_id = arrID[arrID.length - 1];
		
		//change the status to empty
		var url = "api/thread/block_email";
		formValues = "id=" + thread_id;
					
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//it's been processed now
					self.model.set("process_thread", false);
					//close the preview if any
					if ($("#hide_preview_pane").length > 0) {
						$("#hide_preview_pane").trigger("click");
					}
					$(".threads_row_" + thread_id).css("background", "red");
					setTimeout(function() {
						$(".threads_row_" + thread_id).fadeOut();
						
						checkInbox();
					}, 2500);
				}
			}
		});
	},
	confirmApprovalRequest: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		$(".reject_dialog_holder").hide();
		$(".approve_dialog_holder").hide();
		$(".approve_buttons").show();
		
		$("#approve_buttons").fadeOut(function() {
			$("#approve_dialog_holder").show();
			$("#check_number").focus();
		});
	},
	confirmRejectRequest: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		$(".reject_dialog_holder").hide();
		$(".approve_dialog_holder").hide();
		$(".approve_buttons").show();
		
		$("#approve_buttons").fadeOut(function() {
			$("#reject_dialog_holder").show();
			$("#reject_reason").focus();
		});
	},
	approveRequest: function(event) {
		event.preventDefault();
		var id = $("#request_id").val();
		
		$("#approve_complete_holder").html('<i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>');
		
		var case_id = $("#request_case_id").val();
		var check_number = $("#check_number").val();
		
		var requested_by = $("#request_nickname").val();
		var request_date = $("#request_date").val();
		
		var payable_id = $("#payable_id").val();
		var payable_table = $("#payable_table").val();
		var payable_to = $("#payable_to").val();
		var case_name = $("#request_case_name").val();
		var amount = $("#request_amount").val();
		//approve the request, create the check
		var url = "api/checkrequest/approve";

		var formValues = "id=" + id;
		formValues += "&case_id=" + case_id;
		formValues += "&check_number=" + encodeURIComponent(check_number);
		
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
					saveFormFailed(data.error.text);
				} else { 
					$("#approve_complete_holder").html("Accepted&nbsp;&#10003;");
					
					//save a check
					var check_date = moment().format("YYYY-MM-DD");
					var transaction_date = check_date;
					
					var url = "api/check/add";
					var formValues = "table_name=check&table_id=-1&case_id=" + case_id;
					formValues += "&method=check&ledger=OUT";
					formValues += "&transaction_date=" + transaction_date + "&amount_due=" + amount;
					formValues += "&payment=0&check_number=" + check_number;
					formValues += "&balance=0&check_date=" + check_date;
					formValues += "&memo=Check issued to " + encodeURIComponent(payable_to) + "&send_document_id=";
					if (payable_table=="corporation") {
						formValues += "&carrier=" + payable_id;
					}
					if (payable_table=="person") {
						formValues += "&person=" + payable_id;
					}
					formValues += "&checkrequest_id=" + id;
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data:formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							} else {
								
							}
						}
					});
					
					//save a note
					var url = 'api/notes/add';
					formValues = "table_name=notes";
					formValues += "&case_id="  + case_id;
					
					var note = "Check Request APPROVED by "+ login_nickname;
					note += "\r\n";
					note += "Payable To: " + payable_to;
					note += "\r\n";
					note += "Amount: " + amount;
					note += "\r\n";
					note += "Case:" + case_name;
					note += "\r\n";
					note += "Requested By: " + requested_by;
					note += "\r\n";
					note += "Request Date " + request_date;
										
					formValues += "&noteInput=" + encodeURIComponent(note);
					formValues += "&status=approved";
					formValues += "&subject=Check Request Approved";
					formValues += "&table_attribute=" + payable_table;
					formValues += "&partie_id=" + payable_id;
					formValues += "&dateandtime=" + moment().format("MM/DD/YYYY h:mmA");
					//return;
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
							success:function (data) {
								if(data.error) {  // If there is an error, show the error messages
									saveFailed(data.error.text);
								}
							}
						}
					);
					
					//send interoffice
					var formValues = { 
						table_name : "message",
						message_to : requested_by,
						messageInput: note,
						case_id: case_id,
						send_document_id: "",
						subject: "Check Request Approved",
						from: login_username,
						notification: "N"
					};
					var url = "api/messages/add";
					
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							} else { 
							}
						}
					});
					
					//feedback
					setTimeout(function() {
						refreshOutstandingInvoices();
					}, 500);
				}
			}
		});
	},
	rejectRequest: function(event) {
		var id = $("#request_id").val();
		
		var reject_reason = $("#reject_reason").val();
		if (reject_reason=="") {
			$("#reject_reason").css("border", "2px solid red");
			return;
		}
		
		$("#reject_complete_holder").html('<i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>');
		
		var case_id = $("#request_case_id").val();
		var amount = $("#request_amount").val();
		
		var requested_by = $("#request_nickname").val();
		var request_date = $("#request_date").val();
		var payable_id = $("#payable_id").val();
		var payable_table = $("#payable_table").val();
		var payable_to = $("#payable_to").val();
		var case_name = $("#request_case_name").val();
		
		//approve the request, create the check
		var url = "api/checkrequest/reject";

		var formValues = "id=" + id;
		formValues += "&reject_reason=" + encodeURIComponent(reject_reason);
		
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
					saveFormFailed(data.error.text);
				} else { 
					$("#reject_complete_holder").html("Rejected&nbsp;&#10003;");
					//save a note
					var url = 'api/notes/add';
		
					formValues = "table_name=notes";
					/*
					var note = "Check Request to " + payable_to + " for $" + formatDollar(amount) + " RE:" + case_name + " requested by " + requested_by + " on " + request_date + " was DECLINED by "+ login_nickname;
					note += "\r\n";
					note += "Reason: " + reject_reason;
					*/
					var note = "Check Request DECLINED by "+ login_nickname;
					note += "\r\n";
					note += "Payable To: " + payable_to;
					note += "\r\n";
					note += "Amount: " + formatDollar(amount);
					note += "\r\n";
					note += "Case:" + case_name;
					note += "\r\n";
					note += "Requested By: " + requested_by;
					note += "\r\n";
					note += "Request Date " + request_date;
					note += "\r\n";
					note += "Reason: " + reject_reason;
					
					formValues += "&noteInput=" + encodeURIComponent(note);
					formValues += "&status=rejected";
					formValues += "&subject=Check Request Declined";
					formValues += "&table_attribute=" + payable_table;
					formValues += "&partie_id=" + payable_id;
					formValues += "&dateandtime=" + moment().format("MM/DD/YYYY h:mmA");
					
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
							success:function (data) {
								if(data.error) {  // If there is an error, show the error messages
									saveFailed(data.error.text);
								}
								if (data.success) {
									
								}
							}
						}
					);
					
					//send interoffice
					var formValues = { 
						table_name : "message",
						message_to : requested_by,
						messageInput: note,
						case_id: case_id,
						subject: "Check Request Declined",
						from: login_username,
						notification: "N"
					};
					var url = "api/messages/add";
					
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							} else { 
							}
						}
					});
					
					setTimeout(function() {
						refreshOutstandingInvoices();
					}, 500);					
				}
			}
		});
	},
	selectThread:function(event) {
		var self = this;
		this.model.set("process_thread", true);
		var element = event.currentTarget;
		var checked = element.checked;
		
		if ($("#pending_buttons_holder_bulk").css("display")=="none") {
			var check_threads = document.getElementsByClassName("check_thread");
			var arrLength = check_threads.length;
			check_count = 0;
			for (var i = 0; i < arrLength; i++) {
				if (check_threads[i].checked) {
					check_count++;
				}
				if (check_count > 1) {
					$("#pending_buttons_holder_bulk").css("background", "aqua");
					$("#pending_buttons_holder_bulk").fadeIn(function() {
						setTimeout(function() {
							$("#pending_buttons_holder_bulk").css("background", "none");
						}, 2500);
					});
					$(".btn_pending_action").hide();
					break;
				}
			}
		}
		
		setTimeout(function() {
			self.model.set("process_thread", false);
			element.checked = checked;
		}, 300);
	},
	activateEmail: function(event) {
		event.preventDefault();
		document.location.href = "#emailsettings";
		
		setTimeout(function() {
			$("#partie_edit").trigger("click");
			$("#activeInput").val("Y");
		}, 2500);
	},
	unVivify: function(event) {
		var textbox = $("#thread_searchList");
		var label = $("#label_search_threads");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#thread_searchList");
		var label = $("#label_search_threads");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	newThread: function(event) {
		event.preventDefault();
		composeMessage();
	},
	openDayThreads: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var date_class = element.id.split("_")[2];
		if ($(".thread_row_" + date_class).css("display")== "none") {
			$(".thread_row_" + date_class).fadeIn();
			setTimeout(function() { 
				var thread_height = $(".thread_row_" + date_class).height();
				$("#pager").css("margin-top", thread_height + "px");
			}, 200);
		} else {
			$(".thread_row_" + date_class).fadeOut();
		}
	},
	assignWebmail: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		//$("#check_assign_" + id).prop("checked", true);
		composeEmailAssign(id, "webmail");
	},
	assignThreadmail: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		//get all the message_ids for this thread
		var message_ids = $("#thread_message_ids_" + id).val();
		
		//$("#check_assign_" + id).prop("checked", true);
		composeEmailAssign(message_ids, "webmail", id);
	},
	overRow: function(event) {
		if (this.model.get("process_thread")) {
			return;
		}
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		if (id==this.model.get("current_thread_id")) {
			return;
		}
		this.model.set("current_background", element.style.background);
		element.style.background = "#000066";
	},
	outRow: function(event) {
		if (this.model.get("process_thread")) {
			return;
		}
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		if (id==this.model.get("current_thread_id")) {
			return;
		}
		element.style.background = this.model.get("current_background");
	},
	reactThread: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeMessage(element.id);
	},
	listDrafts: function(event) {
		event.preventDefault();
		document.location.href = "#drafts";
	},
	refreshWebmail:function(event) {
		event.preventDefault();
		$("#refresh_thread").html('Loading <img src="img/loader.gif" width="15" height="auto" />');
		
		window.Router.prototype.loginEmail();
		window.Router.prototype.listThreadInbox();
	},
	allThreads: function(event) {
		event.preventDefault();
		if (document.location.hash == "#thread/inboxnew") {
			document.location.href = "#thread/inbox";
			return;
		}
		$(".thread_data_row").fadeIn();
		$(".date_row").show();
		$("#show_threads").css("background", "#337AB7");
		$("#all_threads").hide();
		$("#unread_threads").show();
	},
	unreadThreads: function(event) {
		event.preventDefault();
		$(".thread_data_row").fadeOut(function() {
			var unreads = document.getElementsByClassName("unread_thread");
			arrLength = unreads.length;
			for(var i = 0; i < arrLength; i++) {
				unreads[i].style.display = "";
			}
			$(".date_row").hide();
			$("#unread_threads").hide();
			$("#all_threads").show();
			$("#show_threads").css("background", "#00F");
		});
	},
	shrinkThread: function(event) {
		event.preventDefault();
		$("#preview_pane_holder").fadeOut(function() {
			$("#thread_list_outer_div").css("width", "100%");		
		});
		$("#threadrow_" + this.model.get("current_thread_id")).css("background", "");
		this.model.set("current_thread_id", -1);
	},
	assignBulk: function(event) {
		event.preventDefault();
		
		//gather the thread ids, message ids
		var check_threads = document.getElementsByClassName("check_thread");
		var arrLength = check_threads.length;
		var arrThreads = [];
		for (var i = 0; i < arrLength; i++) {
			var element = check_threads[i];
			if (element.checked) {
				var elementArray = element.id.split("_");
				var id = elementArray[elementArray.length - 1];
				
				//var message_ids = $("#thread_message_ids_" + id).val();
				//arrThreads.push({id: id, message_ids: message_ids});
				arrThreads.push(id);
			}
		}
		
		composeEmailAssignBulk(arrThreads.join("|"));
	},
	assignPending: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		var message_ids = $("#thread_message_ids_" + id).val();
		console.log("id:",id)
		console.log("message_ids:",message_ids)
		assignEmailKase(id, message_ids);
	},
	reviewPending: function(event) {
		this.model.set("process_thread", false);
		this.expandThread(event);
	},
	expandThread: function(event) {
		if (this.model.get("process_thread")) {
			return;
		}
		//console.log("expanding");
		$("#threadrow_" + this.model.get("current_thread_id")).css("background", "");
		this.model.set("current_thread_id", -1);
		
		hidePreview();
		if (this.model.get("message_link_clicked")) {
			this.model.set("message_link_clicked", false);
			return;
		}
		var self = this;
		
		var blnOutbox = (this.model.toJSON().title == "Outbox");
		//event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		this.model.set("current_thread_id", id);
		
		this.model.set("current_background", $("#threadrow_" + id).css("background"));
		$("#threadrow_" + id).css("background", "#F90");
		$(".threads_row_" + id + " #unreadthread_" + id).fadeIn();
		$("#read_image_" + id).hide();
		$("#review_" + id).hide();
		
		if (typeof cookie_left_width == "undefined") {
			$("#thread_list_outer_div").css("width", "55%");
			$("#preview_pane_holder").css("width", "45%");
		} else {
			if (cookie_right_width < 35) {
				//minimum
				cookie_right_width = 35;
				cookie_left_width = 65;
			}
			
			if (cookie_left_width > 70) {
				cookie_left_width = 70;
				cookie_right_width = 30;
				
				writeCookie(cookie_left_width, 60, 24*60*60*1000);
				writeCookie(cookie_right_width, 40, 24*60*60*1000);
			}
			$("#thread_list_outer_div").css("width", cookie_left_width + "%");
			$("#preview_pane_holder").css("width", cookie_right_width + "%");
		}
		$("#preview_block_holder").css("height", (window.innerHeight - 140) + "px");
		$("#preview_block_holder").css("background", "white");
		
		/*
		$("#thread_list_outer_div").css("width", "60%");
		writeCookie('left_width', 60, 24*60*60*1000);
		writeCookie('right_width', 40, 24*60*60*1000);
		*/
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
	
			var thread_bodies = new ThreadMessagesCollection({thread_id: id});
			var arrThreadAttachment = [];
			var originator = "";
			var originator_id = -1;
			var forward_id = -1;
			var arrEmailParties = [];
			if (typeof global_login_email == "undefined") {
				global_login_email = new Email({user_id: login_user_id, email_id: 'all6'});
			}
			
			var login_user_email = global_login_email.toJSON().email_name;
			thread_bodies.fetch({
				success: function(thread_bodies) {
					var message_bodies = thread_bodies.toJSON();
					var message_index = 0;
					var arrBodies = [];
					var arrAttached = [];
					var message_total = message_bodies.length;
					_.each( message_bodies, function(message_body) {
						var message_date = moment(message_body.dateandtime).format("YYYY-MM-DD");
						var time_since = timeSince(moment(message_date));
						var message_date = moment(message_body.dateandtime).format("MMM D") + " <span style='font-size:0.8em;font-style:italic'>(" + time_since + " ago)</span>";
						if (moment().format("MM/DD/YY")==moment(message_body.dateandtime).format("MM/DD/YY")) {
							message_date = moment(message_body.dateandtime).format("h:mm a");
						}
						
						var message_from = message_body.from;
						if (message_from.indexOf("|") > -1) {
							var arrFrom = message_from.split("|");
							message_from = arrFrom[1];
							message_body.from = arrFrom[0];
						}
						var arrEmailDestination = [];
						var message_to = message_body.message_to;
						var message_cc = message_body.message_cc;
						var message_bcc = message_body.message_bcc;
						
						if (message_from!=login_user_email && message_from!=login_username) {
							if (!inArray(message_from, arrEmailParties)) {						
								arrEmailParties.push(message_from);
							}
							if (!inArray(message_cc, arrEmailDestination)) {						
								arrEmailDestination.push(message_to);
							}
						
							if (!inArray(message_cc, arrEmailDestination)) {						
								arrEmailDestination.push(message_cc);
							}
						
							if (message_bcc==login_nickname) {
								if (!inArray(message_bcc, arrEmailDestination)) {						
									arrEmailDestination.push(message_bcc);
								}
							}
						}
						
						if (message_body.to_user_names!="") {
							var arrUsers = message_body.to_user_names.split("|");
							var arrNicks = message_body.to_nicknames.split("|");
							var arrReadDates = message_body.read_dates.split("|");
							var arrReadTypes = message_body.to_types.split("|");
							var arrLength = arrUsers.length;
							var arrReadTitles = [];
							message_body.read_status = "N";
							
							var arrNicksDone = [];
							var arrMessageTo = [];
							var arrMessageCc = [];
							for(var i = 0; i < arrLength; i++) {
								var to_user_name = arrUsers[i];
								var to_nickname = arrNicks[i];
								var to_read_date = arrReadDates[i];
								var to_read_type = arrReadTypes[i];
								
								if (arrNicksDone.indexOf(to_nickname) > -1) {
									continue;
								} else {
									arrNicksDone.push(to_nickname);
								}
								

								if (typeof to_read_date == "undefined") {
									to_read_date = "0000-00-00 00:00:00";
								}
								var display_nickname = to_nickname;
								if (to_read_type!="bcc" || to_nickname == login_nickname) {
									//normal display	
									if (to_read_date=="0000-00-00 00:00:00") {
										var to_read_title = to_nickname;
									} else {
										var to_read_title = "<span title='" + to_user_name + " read the message on " + moment(to_read_date).format("MM/DD/YY h:mma") + "' style='background:green;color:white'>" + to_nickname + "</span>&nbsp;<span style='font-size:0.7em; display:none' class='read_date_" + message_body.id + "'>" + moment(to_read_date).format("MM/DD/YY") + "&nbsp;" + moment(to_read_date).format("h:mma") + "</span>";
										
										if (to_nickname == login_nickname) {
											message_body.read_status = "Y";
										}
									}
									arrReadTitles.push(to_read_title);
									if (to_read_type=="to") {
										arrMessageTo.push(to_read_title);
									}
									if (to_read_type=="cc") {
										arrMessageCc.push(to_read_title);
									}
								} else {
									//check for read status but exclude from list
									if (to_nickname == login_nickname) {
										message_body.read_status = "Y";
									}
								}
							}
							message_body.to_user_names = arrReadTitles.join("; ");
							if (arrMessageTo.length > 0) {
								message_body.to_user_names = arrMessageTo.join("; ");
							}
							if (arrMessageCc.length > 0) {
								message_body.to_user_names += "<br>Cc:&nbsp;" + arrMessageCc.join("; ");
							}
						} else {
							message_body.to_user_names = message_body.message_to;
							if (message_body.message_cc!="") {
								message_body.to_user_names += "<br>Cc:&nbsp;" + message_body.message_cc;
							}
							if (message_body.message_bcc!="" && blnOutbox) {
								message_body.to_user_names += "<br>Bcc:&nbsp;" + message_body.message_bcc;
							}
						}
						
						if (message_body.read_dates=="" || message_body.read_dates=="0000-00-00 00:00:00") {
							//we're reading it now
							var url = 'api/messages/read';
							formValues = "id=" + message_body.message_id;
					
							$.ajax({
								url:url,
								type:'POST',
								dataType:"json",
								data: formValues,
								success:function (data) {
									if(data.error) {  // If there is an error, show the error messages
										saveFailed(data.error.text);
									} else {
										checkInbox();
										//it's been read now
										$("#threads_row_" + id).css("background", "rgb(79, 86, 105)");
									}
								}
							});
							
							if (blnGMailUser && blnGmail && login_read_messages=="Y") {
								var url = "https://www.ikase.xyz/ikase/gmail/ui/read_message.php";
								url += "?user_id=" + login_user_id + "&customer_id=" + customer_id + "&user_name=" + login_username + "&email=" + login_email_name + "&destination=" + login_email_name + "&hash=" + document.location.hash.substr(1) + "&uid=" + message_body.message_uuid;
								
								document.getElementById("check_gmail_messages").src = url;
							}
						}
						//originator
						if (originator_id < 0) {
							if (message_from!=login_user_email && message_from!=login_username) {
								originator = message_from;
								originator_id = message_body.id;
							}
							forward_id = message_body.id;
						}
						var message_destination = message_body.to_user_names;
						//var message_destination = message_body.message_to;
						var display_reply_block = "block";
						if (self.model.get("title") == "Pending Emails") {
							display_reply_block = "none";
						}
						var message_header = "<div><div style='float:right'>" + message_date + "&nbsp;<div id='span_commands_" + message_body.id + "' style='color:#FCC; background:#000; padding:2px; width: 40px; display:" + display_reply_block + "'><a id='reply_" + message_body.id + "' style='cursor:pointer;' class='message_action' title='Reply' onClick='parent.replyMessage(this.id)'><i style='font-size:12px;' class='glyphicon glyphicon-arrow-left' title='Click to Reply'></i></a>&nbsp;<a id='more_options_" + message_body.id + "' style='cursor:pointer' title='More options' onClick='document.getElementById(\"message_options_" + message_body.id + "\").style.display=\"inline-block\"; document.getElementById(\"span_commands_" + message_body.id + "\").style.width=\"70px\"; document.getElementById(\"more_options_" + message_body.id + "\").style.display=\"none\"'>&#10010;</a><div id='message_options_" + message_body.id + "' style='display:none'><a id='replyall_" + message_body.id + "' style='cursor:pointer' class='message_action' title='Reply All' onClick='parent.replyMessage(this.id)'><i style='font-size:13px;color:#FCC' class='glyphicon glyphicon-circle-arrow-left' title='Click to Reply All'></i></a>&nbsp;<a id='forward_" + message_body.id + "' style='cursor:pointer' class='message_action' title='Forward' onClick='parent.replyMessage(this.id)'><i style='font-size:13px;color:#66FF99' class='glyphicon glyphicon-arrow-right' title='Click to Forward'></i></a>&nbsp;<a onClick='parent.composeDelete(" + message_body.id + ", \"messages\");' style='cursor:pointer;color:red' title='Click to delete the message'>&#10008;</a></div></div></div>From: <span style='font-weight:bold'>" + message_from + "</span><br>To: " + message_destination + "&nbsp;Cc:" + message_cc + "&nbsp;Bcc:" + message_bcc + "&nbsp;";
						//assignEmailKase is utilities.js
						if (message_body.message_type=="email" && message_body.case_id < 0) {
							message_header += '<button title="Click to Assign Thread to Kase" id="assign_' + message_body.id + '" class="btn btn-xs btn-primary assign_webmail white_text" style="cursor:pointer;" title="Click to assign this email to kase" onClick="parent.assignEmailKase(' + id + "," + message_body.id + ')">Assign to Kase</button>&nbsp;&nbsp;&nbsp;<button class="btn btn-xs btn-success btn_accept_pending" id="messageaccept_' + message_body.id + '" onClick="parent.acceptEmailKase(' + id + "," + message_body.id + ')">Accept as is</button>';
						}
						message_header += "<hr></div>";
						
						//break out attachments
						var arrAttachment = [];
						if (message_body.message_attachments!="") {
							message_body.attachments = message_body.message_attachments;
							//message_body.attachments = message_body.attachments.replaceAll(",", "|");
						}
						if (message_body.attachments!="") {
							message_body.attachments = message_body.attachments.replaceAll(";", "|");
							message_body.attachments = message_body.attachments.replaceAll(",", "|");
							attach_indicator = "";
							email_attachment = "";
							var arrAttach = message_body.attachments.split("|");
							var arrayLength = arrAttach.length;
							
							for (var i = 0; i < arrayLength; i++) {
								var attachment = arrAttach[i].trim();
								/*
								if (arrAttached.indexOf(attachment) < -1) {
									arrAttached.push(attachment);
								} else {
									continue
								}
								*/
								var preview_img = "";
								if (message_body.message_type=="email") {
									//attach_link = "https://www.ikase.xyz/ikase/gmail/ui/" + attachment;
									var strpos = attachment.indexOf("attachments");
									if (strpos < 0) {
										//element = "attachments/" + customer_id + "/" + login_user_id + "/" + element;
										//var attach_link = "https://www.ikase.org/uploads/" + customer_id + "/webmail_previews/" + login_user_id + "/" + attachment;
										var attach_link = "api/preview_attach.php?file=" +  encodeURIComponent(attachment) + "&case_id=" + message_body.case_id;
									} else {
										var attach_link = "https://www.ikase.xyz/ikase/gmail/ui/" + attachment;
									}
									var bln2016 = false;
									if (moment(message_body.dateandtime) > moment("2016-05-22")) {
										bln2016 = true;
									}
									//thumbnail
									if (bln2016 && (attachment.indexOf(".pdf") > -1 || attachment.indexOf(".jpg") > -1 || attachment.indexOf(".png") > -1)) {
										preview_img = " onmouseover='parent.showImportedPreview(this, \"" + attach_link + "\")' onmouseout='parent.hideThumbnailPreview()'";
									}
									
									email_attachment = '<input type="checkbox" value="' + attachment + '" class="kase_attach" id="kase_attach_' + i + '" />&nbsp;';
									var arrLink = attachment.split("/");
									var attachment_path = attachment;
									attachment = arrLink[arrLink.length - 1];
								} else {
									attachment = attachment.replace("https:///uploads", "../uploads");
									attachment = attachment.replace("../uploads/" + message_body.customer_id + "/" + message_body.case_id + "/", "");
									attachment = attachment.replace("../uploads/" + message_body.customer_id + "/", "");
									if (message_body.case_id=="" || message_body.case_id=="-1") {
										//attach_link = "../uploads/" + message_body.customer_id + "/" + attachment;
										attach_link = "api/preview_attach.php?file=" +  encodeURIComponent(attachment);
									} else {
										//attach_link = "../uploads/" + message_body.customer_id + "/" + message_body.case_id + "/" + attachment;
										attach_link = "api/preview_attach.php?file=" +  encodeURIComponent(attachment) + "&case_id=" + message_body.case_id;
									}
									var attachment_path = attach_link;
								}
								attachment = attachment.replaceAll("%20", " ")
								attachment = attachment.replaceAll(" ", "&nbsp;");
								attachment = attachment.replaceAll("-", "&#8209;");
							
								attach_link = '<a class="kase_attach_preview_link" id="kase_attach_link_' + i + '" href="' + attach_link + '" target="_blank" title="Click to review ' + attachment + '" ' + preview_img + '>' + attachment + '</a>';
								var thread_attachment = attach_link.replace('<a id', '<a class="black_text" id');
								//if (!inArray(thread_attachment, arrThreadAttachment)) {
								if (arrThreadAttachment.toString().indexOf('kase_attach_link_' + i) < 0) {
									arrThreadAttachment.push(thread_attachment);
									//attach_link = email_attachment + attach_link;
									arrAttachment.push(attach_link);
								}
							}
						}
						
						var message_attach_link = "";
						if (arrAttachment.length > 0) {
							//message_attach_link = "<div id='attach_link_" + message_body.id + "'>" + arrAttachment.join("<br>") + "</div>";
						}
						var preview_message = message_body.message;
						//preview_message = decodeURIComponent(preview_message);
						try {
							preview_message = decodeURIComponent(preview_message);
						} catch(err) {
							//do nothing
						}
						var blnLastMessage = (message_index == (message_total - 1));
						if (message_body.snippet!="" && !blnLastMessage) {
							preview_message = "<div style='cursor:pointer;' id='snippet_" + message_body.id + "' onClick='document.getElementById(\"snippet_" + message_body.id + "\").style.display=\"none\";document.getElementById(\"emailmessage_" + message_body.id + "\").style.display=\"\"; document.getElementById(\"emailbody_" + message_body.id + "\").style.background=\"white\"'>" + message_body.snippet + "</div><div id='emailmessage_" + message_body.id + "' style='display:none;cursor:pointer;' onClick='document.getElementById(\"snippet_" + message_body.id + "\").style.display=\"\";document.getElementById(\"emailmessage_" + message_body.id + "\").style.display=\"none\"; document.getElementById(\"emailbody_" + message_body.id + "\").style.background=\"#EDEDED\"'>" + message_body.message + "</div>";
						}
						var body_background = "#EDEDED";
						var body_click = " onClick='' ";
						if (blnLastMessage) {
							body_background = "white";
						}
						arrBodies.push("<div id='emailbody_" + message_body.id + "' style='background:" + body_background + "'>" + message_header + message_attach_link + preview_message + "</div>");
						
						if (message_index == (message_bodies.length - 1)) {
							var message_subject = message_body.subject;
							
							var message_case = $("#thread_case_" + id).html();
							if (message_case.indexOf("<a") < 0) {
								message_case = "";
							}
							if (message_case.indexOf("<a") < 0) {
								message_case = "";
							}
						
							var message_links = '';
							if (originator_id > 0) {
								message_links += '<a id="reply_' + originator_id + '" data-toggle="modal" data-target="#myModal4" style="cursor:pointer" class="thread_action white_text"><button class="btn btn-primary btn-xs" title="Click to Reply">Reply</button></a>&nbsp;&nbsp;<a id="replyall_' + originator_id + '" data-toggle="modal" data-target="#myModal4" style="cursor:pointer" class="thread_action"><button" class="btn btn-success btn-xs" title="Click to Reply All">Reply All</button></a>&nbsp;&nbsp;';
							}
							message_links += '<a id="forward_' + forward_id + '" data-toggle="modal" data-target="#myModal4" style="cursor:pointer" class="thread_action white_text"><button class="btn btn-info btn-xs" title="Click to Forward">Forward</button></a>&nbsp;&nbsp;<a href="report.php#thread/' + id + '" target="_blank"><i style="font-size:15px;color:#CC99CC; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Thread"></i></a>&nbsp;&nbsp;<a class="list_edit unread_thread white_text" id="unreadthread_' + id + '" style="cursor:pointer"><i style="font-size:13px; color:#FFF; cursor:pointer" class="glyphicon glyphicon-eye-close" title="Click to mark as Not Read"></i></a>&nbsp;&nbsp;<a class="list_edit delete_thread white_text" id="deletethread_' + id + '" style="cursor:pointer"><i style="font-size:13px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash" title="Click to delete Thread"></i></a>';
							
							var header_height = 55;
							if (arrThreadAttachment.length > 0) {
								arrThreadAttachment = arrThreadAttachment.unique();
								header_height = 55 + (20 * arrThreadAttachment.length);
							}
							var preview_title = "<div style='width:100%; height:" + header_height + "; background:#CCC; color:black; padding:3px'><div style='float:right; background:black; padding-right:5px; padding-left:5px; height:18px; width:19px'><a id='hide_preview_pane' style='cursor:pointer; color:white; font-size:1.2em; position:absolute; margin-top:-3px' title='Click to close preview pane'>&times;</a></div><div style='font-size:1.3em; font-weight:bold'><div style='width:235px; height:30px; text-align:center; background:black' id='message_links_holder'>" + message_links + "</div>" + message_subject + "</div>";
							if (message_case!="") {
								//remove the subject from the case name
								message_case = message_case.replace('font-weight:bold" class="dont-break-out', 'display:none" class="dont-break-out');
								preview_title += "<div>Kase:" + message_case.replace("color:white", "color:white; background:black") + "</div>";
							}
							var thread_attach_link = "";
							if (arrThreadAttachment.length > 0) {
								thread_attach_link = "<div style='display:inline-block' id='thread_attach_link_" + id + "'>" + arrThreadAttachment.join("<br>") + "</div>";
							}
							if (thread_attach_link!="") {
								thread_attach_link = thread_attach_link.replaceAll("&nbsp;", " ");
								preview_title += "<div style='width:300px'><div style='display:inline-block; vertical-align:top'><i style='font-size:15px;color:#000; display:' class='glyphicon glyphicon-paperclip'></i></div> " + thread_attach_link + "</div>";
							}
							
							preview_title += "</div>";
							
							$("#preview_title").html(preview_title);
						}
						message_index++;
					});
					$("#preview_title").append("<input type='hidden' id='email_parties' value='" + arrEmailParties.join(";") + "' />");
					var $frame = $('<iframe style="width:100%; height:560px;background:white" frameborder="0">');
					$("#preview_pane").html( $frame );
					setTimeout( function() {
						var doc = $frame[0].contentWindow.document;
						var $body = $("body",doc);
						var data = arrBodies.join("<hr>");
						data = data.replaceAll("\r\n", "<br>");
						
						var strpos = data.indexOf('<form id="request_approval_form">');
						if (strpos > -1) {
							var endpos  = data.indexOf('</form>');
							//console.log(data.substring(strpos, endpos));
							var theform = data.substring(strpos, endpos + 7).replaceAll("<br>", "");
							//theform = theform.replaceAll('id=', 'name=');
							//theform = theform.replaceAll('name="request_approval_form"', 'id="request_approval_form"');
							$("#preview_pane").prepend("<div>" + theform + "</div>");
							data = data.substring(0, strpos);
						}
						data = data.replaceAll('background:white','background:#EDEDED');				
						$body.html('<link rel="stylesheet" href="css/bootstrap.3.0.3.min.css"><style>body{padding:5px}</style>' + data);
						
						//pending, have to hide stuff for now
						if (self.model.get("title") == "Pending Emails") {
							$("#message_links_holder").hide();
						}
					}, 1 );
				}
			});
		});
		
		var blnUnRead = (element.className.indexOf("unread_thread") > -1);
		if (blnUnRead) {
			//increment the message count indicator
			var current_count = $("#new_message_indicator").html();
			if (current_count!="") {
				current_count = Number(current_count);
				if (current_count > 0) {
					current_count--;
				}
				if (current_count == 0) {
					$("#new_message_indicator").html("");
					$("#new_message_indicator").fadeOut();
				} else {
					var the_count = maxHundred(current_count);
					$("#new_message_indicator").html(the_count);
				}
			}
		}
	},
	markUnread: function(event) {
		var self = this;
		event.preventDefault();
		
		var element = event.currentTarget;
		//if (element.id.indexOf("unreadthread") < 0) {
		var arrClasses = element.classList;
		if (arrClasses[1]!="unread_thread") {
			return;
		}
		
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		this.model.set("message_link_clicked", true);
		
		var url = "api/threads/unread";
		 formValues = "thread_id=" + id;
	
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#threadrow_" + id).css("background-color", "rgb(51, 122, 183)");
					self.model.set("current_background", "rgb(51, 122, 183)");
					$(".threads_row_" + id + " #unreadthread_" + id).fadeOut();
					$("#read_image_" + id).css("visibility", "hidden");
				}
			}
		});
	},
	confirmdeleteThread: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "threads");
	},
	canceldeleteThread: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteThread: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "threads");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteThread(event);
			$(".thread_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".thread_row_" + id).fadeOut();
			}, 2500);
		}
	},
	showReadDates: function(event) {
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		
		$(".read_date_" + id).fadeIn();
	},
	freezePreview: function() {
		 freezeThreadPreview();
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'thread_listing', 'thread');
	}
});
function confirmApprovalRequest (json) {
	$("#reject_dialog_holder").hide();
	$("#approve_dialog_holder").show();
	$("#check_number").focus();
}
function confirmRejectRequest (json) {
	$("#approve_dialog_holder").hide();
	$("#reject_dialog_holder").show();
	$("#reject_reason").focus();
}