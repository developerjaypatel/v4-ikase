var checkrequest_search_id = false;
window.checkrequest_listing_view = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
    },
	events: {
		//new button click
		"click .delete_checkrequest":							"confirmdeleteCheckRequest",
		"click .edit_checkrequest":								"editCheckRequest",
		"click #new_checkrequest":								"checkRequest",
		"click #general_checkrequest":							"checkRequestGeneral",
		"click .approve_request":								"confirmApprovalRequest",
		"click .approve_cancel":								"cancelApproval",
		"click .reject_request":								"confirmRejectRequest",
		"click .reject_cancel":									"cancelRejection",
		"click .void_request":									"confirmVoidRequest",
		"click .delete_request":								"confirmDeleteRequest",
		"click .review_request":								"editCheckRequest",
		"click .approve_complete":								"approveRequest",
		"click .reject_complete":								"rejectRequest",
		"change .request_reaction":								"reactToRequest",
		"click .review_books":									"reviewBooks",
		"click #account_register":								"goRegister",
		"click .print_check":									"printCheck",
		"click .add_check":										"newPayment",
		"click #label_search_messages":							"Vivify",
		"click #checkrequests_searchList":						"Vivify",
		"focus #checkrequests_searchList":						"Vivify",
		"blur #checkrequests_searchList":						"unVivify",
		"click #checkrequests_clear_search":					"clearSearch",
		"click .print_copy":									"printCheck",
		"click .unattach":										"detachAccount",
		"click #checkrequest_listing_view_done":				"doTimeouts"
	},
    render:function () {		
		if (typeof this.template != "function") {
			
			var view = "checkrequest_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		var checkrequests = this.collection.toJSON();
		var blnSettlementRequestsPending = false;
		var total_requested = 0;
		var account_id = $("#" + this.model.get("account_type") + "_account_id").val();
		this.model.set("account_id", account_id);
		
		_.each( checkrequests, function(checkrequest) {
			//anything pending
			if (!blnSettlementRequestsPending) {
				if (checkrequest.account_id > 0 && checkrequest.approved=="P") {
					blnSettlementRequestsPending = true;
				}
			}
			if (isDate(checkrequest.request_date)) {
				checkrequest.request_date = moment(checkrequest.request_date).format("MM/DD/YYYY");
			} else {
				checkrequest.request_date = "";
			}
			checkrequest.late = false;
			if (isDate(checkrequest.needed_date) && checkrequest.needed_date!="0000-00-00" && checkrequest.needed_date!="1969-12-31") {
				var d1 = new Date(moment(checkrequest.needed_date).format("MM/DD/YYYY"));
				var d2 = new Date(moment().format("MM/DD/YYYY") + " 00:00:00");
				
				checkrequest.needed_date = moment(checkrequest.needed_date).format("MM/DD/YYYY");
				
				if (d1 < d2 && checkrequest.approved=="P") {
					checkrequest.late = true; 
				}
			} else {
				checkrequest.needed_date = "";
			}
			
			if (checkrequest.payable_to.indexOf("_") > -1) {
				checkrequest.payable_to = checkrequest.payable_to.replaceTout("_", " ").capitalizeWords();
			}
			
			checkrequest.import_indicator = "";
            if (checkrequest.uuid.indexOf("KS") < 0) {
	            checkrequest.import_indicator = "*";
            }
			
			if (checkrequest.case_type == "Personal Injury" || checkrequest.case_type == "NewPI") {
				checkrequest.case_type = "PI";
			}
			
			total_requested += Number(checkrequest.amount);
		});
		
		var page_title = this.model.get("page_title");
		var embedded = this.model.get("embedded");
		if (typeof this.model.get("case_id") != "undefined") {
			this.model.set("blnSettlementRequestsPending", blnSettlementRequestsPending);
		} else {
			//default
			this.model.set("blnSettlementRequestsPending", false);
		}
		
		try {
			/*
			if (checkrequests.length==0) {
				var html = '<div class="white_text" style="font-size:1.2em; margin-bottom:5px">' + this.model.get("page_title") + ' - No Request Found';
				html += '&nbsp;|&nbsp;<button id="new_checkrequest" class="btn btn-sm btn-primary btn_new_checkrequest" title="Click to create a new Check Request" style="margin-top:-5px">Request Check</button></div>';
				
				$(this.el).html(html);
				
				return this;
			}
			*/
			$(this.el).html(this.template({
				checkrequests: checkrequests,
				page_title: page_title,
				total_requested: total_requested,
				embedded: embedded,
				account_id: account_id
			}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
				
		setTimeout(function() {
			tableSortIt();
		}, 100);
		
		
		return this;
    },
	clearSearch: function() {
		$("#checkrequests_searchList").val("");
		$( "#checkrequests_searchList" ).trigger( "keyup" );
		$("#checkrequests_searchList").focus();
	},
	unVivify: function(event) {
		var textbox = $("#checkrequests_searchList");
		var label = $("#label_search_checkrequest");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#checkrequests_searchList");
		var label = $("#label_search_checkrequest");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	newPayment: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		
		var account_type = arrID[1];
		var account_id = arrID[arrID.length - 1];
		
		this.newCheck("IN", account_id, account_type);
	},
	newCheck:function (ledger, account_id, account_type) {
		if (blnNewCheck) {
			return;
		}
		blnNewCheck = true;
		
		composeCheck("new_check_-1", ledger, "", {}, account_id, account_type, -2);
		
		var self = this;
		setTimeout(function() {
			blnNewCheck = false;
		}, 1200);
    },
	printCheck: function(event) {
		var url = "/reports/print_checks.php?id="
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		//console.log(url + id);
		//return;
		window.open(url + id);
		
		setTimeout(function() {
			checkClearedChecks();
		}, 5000);
	},
	detachAccount: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		var url = "api/checkrequest/detach";

		var formValues = "id=" + id;
		
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				//console.log("detached " + id);
				var this_id = id;
				$(".checkrequest_data_row_" + this_id).css("background", "lime");
				setTimeout(function() {
					$(".checkrequest_data_row_" + this_id).fadeOut();
					refreshOutstandingInvoices();
				}, 2500);
			}
		});
	},
	goRegister: function(event) {
		if (typeof this.model.get("account_type")!="undefined") {
			var account_type = this.model.get("account_type");
			window.Router.prototype.listBankAccounts(account_type, true);
			
			window.history.replaceState(null, null, "#bankaccount/list/" + account_type);
			app.navigate("bankaccount/list/" + account_type, {trigger: false});
		}
	},
	reviewBooks: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		document.location.href = "#payments/" + id;
	},
	confirmdeleteCheckRequest: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		composeDelete(id, "checkrequest");
	},
	checkRequest: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id;
		if (document.location.hash.indexOf("#settlement/") == 0) {
			element_id = "request_bulk_-1";
		}
		var blnSettlementRequestsPending = this.model.get("blnSettlementRequestsPending");
		//var blnSettlementRequestsPending = false;
		var case_id = current_case_id;
		if (case_id < 0) {
			case_id = "-2";
		}
		composeCheckRequest(element_id, "-1", case_id, blnSettlementRequestsPending);
	},
	checkRequestGeneral: function(event) {
		if (current_case_id==-1) {
			//catchup
			if (document.location.hash.indexOf("#payments/")==0) {
				current_case_id = document.location.hash.split("/")[1];
			}
		}
		var case_id = current_case_id;
		if (current_case_id==-1) {	
			case_id = -2;
		}
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;
		composeCheckRequest(element_id, "-1", case_id);
	},
	editCheckRequest: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var arrID = element_id.split("_");
		var corp_id = arrID[arrID.length - 1];
		
		var case_id = "";
		var element_id = element.id;
		if (document.location.hash.indexOf("#checkrequests") > -1) {
			if (element_id.indexOf("review") > -1) {
				element_id = "request_review_" + corp_id;
			} else {
				element_id = "request_edit_" + corp_id;
			}
			case_id = $("#request_case_id_" + corp_id).val();
			corp_id = $("#payable_id_" + corp_id).val();
		}
		composeCheckRequest(element_id, corp_id, case_id);
	},
	reactToRequest: function(event) {
		var element = event.currentTarget;
		var reaction = element.value;
		if (reaction=="") {
			return;
		}
		
		var element_id = element.id;
		var arrID = element_id.split("_");
		var request_id = arrID[arrID.length - 1];
		
		$("#" + reaction + "_request_" + request_id).trigger("click");
	},
	confirmDeleteRequest: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[arrID.length - 1];
		
		if (!confirm("Press OK to confirm this Delete request")) {
			return;
		}
		var url = "api/checkrequest/delete";

		var formValues = "id=" + id;
		
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
					//feedback
					var current_background = $(".checkrequest_data_row_" + id).css("background");
					$(".checkrequest_data_row_" + id).css("background", "red");
					$("#approve_complete_holder_" + id).html('&#10003;');
					setTimeout(function() {
						$(".checkrequest_data_row_" + id).css("background", current_background);
						$(".checkrequest_data_row_" + id + " td").css("text-decoration", "line-through");
						$(".checkrequest_data_row_" + id + " td").css("color", "red");
						$("#request_buttons_" + id).html("<span style='color:white;background:red;padding:2px'>DELETED</span>");
					}, 2500);
				}
			}
		});
	},
	confirmVoidRequest: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[arrID.length - 1];
		
		if (!confirm("Press OK to confirm this Void request")) {
			return;
		}
		var url = "api/checkrequest/void";

		var formValues = "id=" + id;
		
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
					//feedback
					var current_background = $(".checkrequest_data_row_" + id).css("background");
					$(".checkrequest_data_row_" + id).css("background", "red");
					$("#approve_complete_holder_" + id).html('&#10003;');
					setTimeout(function() {
						$(".checkrequest_data_row_" + id).css("background", current_background);
						$(".checkrequest_data_row_" + id + " td").css("text-decoration", "line-through");
						$(".checkrequest_data_row_" + id + " td").css("color", "red");
						$("#request_buttons_" + id).html("<span style='color:white;background:red;padding:2px'>VOID</span>");
					}, 2500);
				}
			}
		});
	},
	confirmApprovalRequest: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		$(".reject_dialog_holder").hide();
		$(".approve_dialog_holder").hide();
		$(".approve_buttons").show();
		
		$("#approve_buttons_" + id).fadeOut(function() {
			$("#approve_dialog_holder_" + id).show();
			
			//if the case is associated with an account, get check number there
			var account_type = self.model.get("account_type");
						
			if (account_type=="trust") {
				var case_id = $("#request_case_id_" + id).val();
				var account =  new Account({"case_id": case_id});
			} else {
				var operating_account_id = $("#operating_account_id").val();
				var account =  new Account({"id": operating_account_id});
			}
			account.fetch({
				success: function (data) {
					//now show the drop with accounts, selected if already chosen
					
					if (typeof data.get("case_id") != "undefined" || account_type=="operating") {
						$("#account_id_" + id).val(data.get("account_id"));
						//condition for approval
						var approval_condition = "none";
						if (data.get("account_info")!="") {
							var jdata = JSON.parse(data.get("account_info"));
							var arrLength = jdata.length;
							
							for (var i = 0; i < arrLength; i++) {
								if (jdata[i].name=="current_check_numberInput") {
									$("#check_number_" + id).val(jdata[i].value);
								}
								if (jdata[i].name=="approval_conditionInput") {
									approval_condition = jdata[i].value;
								}
								
							}
						}
						if (data.get("account_type")=="trust") {
							//do we have funds available for this case
							var url = "api/account/balance/" + case_id + "/" + data.get("account_type");
							var account_name = data.get("account_name");
						} else {
							//do we have funds in the operating account
							var url = "api/account/balanceall/" + $("#operating_account_id").val();
							var account_name = "Cost Trust";
						}
						
						$.ajax({
							url:url,
							type:'GET',
							dataType:"json",
							success:function (data) {
								if (account_type=="trust") {
									data.available = Number(data.balance) - Number(data.pendings);
								} else {
									//default 
									//more than zero
									data.available = 1;
										
									if (approval_condition=="balance") {
										//deposits - withdrawals
										data.available = Number(data.balance);
									}
									if (approval_condition=="available") {
										//balance - pendings
										data.available = Number(data.balance) - Number(data.pendings);	
									}
								}
								
								if (data.available <= 0) {
									//can't approve checks, no money
									$("#check_number_holder_" + id).html("<span title='There are insufficient funds in the " + account_name + " account.  Please enter a Deposit so that you can issue checks.'>Deposit Required</span>");
									$("#check_number_holder_" + id).css("background", "red");
									$("#request_reaction_" + id).val("");
									setTimeout(function() {
										$(".approve_dialog_holder").fadeOut();
										$(".approve_buttons").fadeIn();
										$(".request_reaction").fadeIn();
									}, 2500);
								}
							}
						});
					} else {
						//is there a next check number setting
						if (typeof customer_settings.get("check_number") != "undefined") {
							$("#check_number_" + id).val(customer_settings.get("check_number"));
						}
					}
				}
			});
			$("#check_number_" + id).focus();
		});
		
		//hide all the others
		$(".request_reaction").fadeOut();
	},
	cancelApproval: function(event) {
		event.preventDefault();
		var element_id = event.currentTarget.id;
		$(".approve_dialog_holder").hide();
		$(".approve_buttons").show();
		$(".request_reaction").show();
		
		$("#" + element_id.replace("approve_cancel_", "request_reaction_")).val("");
	},
	cancelRejection: function(event) {
		event.preventDefault();
		
		$(".reject_dialog_holder").hide();
		$(".approve_buttons").show();
		$(".request_reaction").show();
		
		$("#" + element_id.replace("reject_cancel_", "request_reaction_")).val("");
	},
	confirmRejectRequest: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		$(".reject_dialog_holder").hide();
		$(".approve_dialog_holder").hide();
		$(".approve_buttons").show();
		
		$("#approve_buttons_" + id).fadeOut(function() {
			$("#reject_dialog_holder_" + id).show();
			$("#reject_reason_" + id).focus();
		});
	},
	approveRequest: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		$("#approve_complete_holder_" + id).html('<i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>');
		
		var case_id = $("#request_case_id_" + id).val();
		var check_number = $("#check_number_" + id).val();
		
		var requested_by = $("#request_nickname_" + id).val();
		var request_date = $("#request_date_" + id).val();
		
		var payable_id = $("#payable_id_" + id).val();
		var payable_table = $("#payable_table_" + id).val();
		var payable_to = $("#payable_to_" + id).val();
		var case_name = $("#request_case_name_" + id).val();
		var amount = $("#request_amount_" + id).val();
		var account_id = $("#account_id_" + id).val();
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
					//save a check
					var check_date = moment().format("YYYY-MM-DD");
					var transaction_date = check_date;
					
					var url = "api/check/add";
					
					var formValues = "table_name=check&table_id=-1&case_id=" + case_id;
					formValues += "&method=check&ledger=OUT&account_id=" + account_id;
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
								$(".approve_dialog_holder").hide();
								$(".approve_buttons").show();
								$(".request_reaction").show();
								
								$("#approve_buttons_" + id).hide();
								$("#approve_dialog_holder_" + id).show();
								
								//offer to print check
								var print_holder = $("#print_holder_" + id);
								var check_id = data.id;
								print_holder.html('&nbsp;<a title="Click to print check" class="print_check white_text" id="print_check_' + check_id + '" style="cursor:pointer"><i class="glyphicon glyphicon-print" style="color:aqua">&nbsp;</i></a>');
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
					formValues += "&status=APPROVED";
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
					
					var url = "api/settings/fresh";
					//refresh the settings, for next check_number
					$.ajax({
						url:url,
						type:'GET',
						dataType:"json",
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							} else {
								customer_settings.set(data);
							}
						}
					});
					
					//feedback
					var current_background = $(".checkrequest_data_row_" + id).css("background");
					$(".checkrequest_data_row_" + id).css("background", "green");
					$("#approve_complete_holder_" + id).html('&#10003;');
					setTimeout(function() {
						//$(".checkrequest_data_row_" + id).fadeOut();
						$(".checkrequest_data_row_" + id).css("background", current_background);
						refreshOutstandingInvoices();
						//window.Router.prototype.listCheckRequests();
					}, 2500);
				}
			}
		});
	},
	rejectRequest: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		var reject_reason = $("#reject_reason_" + id).val();
		if (reject_reason=="") {
			$("#reject_reason_" + id).css("background", "red");
			return;
		}
		
		$("#reject_complete_holder_" + id).html('<i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>');
		
		var case_id = $("#request_case_id_" + id).val();
		var amount = $("#request_amount_" + id).val();
		
		var requested_by = $("#request_nickname_" + id).val();
		var request_date = $("#request_date_" + id).val();
		var payable_id = $("#payable_id_" + id).val();
		var payable_table = $("#payable_table_" + id).val();
		var payable_to = $("#payable_to_" + id).val();
		var case_name = $("#request_case_name_" + id).val();
		
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
					formValues += "&status=DECLINED";
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
					
					//feedback
					$(".checkrequest_data_row_" + id).css("background", "green");
					$("#reject_complete_holder_" + id).html('&#10003;');
					
					setTimeout(function() {
						$(".checkrequest_data_row_" + id).fadeOut();
						refreshOutstandingInvoices();
						window.Router.prototype.listCheckRequests();
					}, 2500);
				}
			}
		});
	},
	doTimeouts: function() {
		$(".checkrequest_listing th").css("font-size", "1em");
		$(".checkrequest_listing").css("font-size", "1.1em");
		
		if (document.location.hash.indexOf("#settlement/") == 0) {
			$(".checkrequest #general_checkrequest").hide();
			//if ($("#settled_date").val()!="") {
				$(".checkrequest #new_checkrequest").show();	
			//}
		}
		
			
		if (typeof this.model.get("case_id") != "undefined") {
			var case_id = this.model.get("case_id");
			var account =  new Account({"case_id": case_id});
			
			account.fetch({
				success: function (data) {
					//now show the drop with accounts, selected if already chosen
					if (typeof data.get("case_id") != "undefined") {
						//do we have funds available
						
						//var url = "api/account/balance/" + case_id + "/" + data.get("account_type");
						var account_type = data.get("account_type");
						if (data.get("account_type")=="trust") {
							//do we have funds available for this case
							var url = "api/account/balance/" + case_id + "/" + data.get("account_type");
							var account_name = data.get("account_name");
						} else {
							//do we have funds in the operating account
							var url = "api/account/balanceall/" + $("#operating_account_id").val();
							var account_name = "Cost Trust";
						}
						
						$.ajax({
							url:url,
							type:'GET',
							dataType:"json",
							success:function (data) {
								//data.available = Number(data.balance) - Number(data.pendings);
								if (account_type=="trust") {
									data.available = Number(data.balance) - Number(data.pendings);
								} else {
									data.available = Number(data.deposits) - Number(data.withdrawals);
								}
								if (account_type=="trust") {
									if (data.available <= 0) {
										//can't approve checks, no money
										$("#checkrequest_feedback").html("<span title='There are insufficient funds in the " + account_name + " account.  Please enter a Deposit so that you can issue checks.'>Deposit Required</span>");
										$("#checkrequest_feedback").css({"background":"red", "color":"white", "padding":"2px"});
									}
								}
							}
						});
					}
				}
			});
		}
		
		if (typeof this.model.get("account_type")!="undefined") {
			var account_type = this.model.get("account_type");
			if (account_type=="operating") {
				account_type = "cost trust";
			}
			$("#account_register").html("Register - " + account_type.capitalizeWords());
			$("#account_register").show();
		}
	}
});

window.checkrequest_form = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	 events:{
        "click .checkrequest .delete":								"deleteCheckRequestView",
		"click .checkrequest .save":								"saveCheckRequest",
		"click .checkrequest .save_field":							"saveCheckRequestViewField",
		"click .checkrequest .edit": 								"toggleCheckRequestEdit",
		"click .checkrequest .reset": 								"resetCheckRequestForm",
		"dblclick .checkrequest .gridster_border": 					"editCheckRequestViewField",
		"change #payable_to":										"updateCorporationID",
		"click #rush_request":										"setNeededDate",
		"click .manage_payableto":									"managePayable",
		"change #other_payable_to":									"otherPayable",
		"keyup #check_recipient":									"scheduleSearchRecipient",
		"click .select_recipient":									"selectRecipient",
		"click #use_current_case":									"useCurrentCase",
		"click #checkrequest_all_done":								"doTimeouts"
    },
    render: function () {
		var mymodel = this.model.toJSON();
		var self = this;
		if (typeof this.template != "function") {
			var view = "checkrequest_form";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		if (mymodel.request_date!="") {
			if (isDate(mymodel.request_date)) {
				self.model.set("request_date", moment(mymodel.request_date).format("MM/DD/YYYY"));
			} else {
				self.model.set("request_date", "");
			}
		}
		if (mymodel.needed_date!="" && mymodel.needed_date!="0000-00-00" && mymodel.needed_date!="1969-12-31") {
			if (isDate(mymodel.needed_date)) {
				self.model.set("needed_date", moment(mymodel.needed_date).format("MM/DD/YYYY"));
			}
		} else {
			self.model.set("needed_date", "");
		}
		self.model.set("rush_request", "");
		if (mymodel.permanent_stationary=="Y") {
			self.model.set("rush_request", " CHECKED");
		}
		if (typeof this.model.get("blnSettlementRequestsPending")=="undefined") {
			this.model.set("blnSettlementRequestsPending", false);
		}
		
		if (mymodel.case_id > 0) {
			var kase = kases.findWhere({case_id: mymodel.case_id});
			//alert(kase);
			self.model.set("case_name", "");//kase.get("case_name")
		} else {
			self.model.set("case_name","");
			try {
				$(self.el).html(self.template(self.model.toJSON()));		
			}
			catch(err) {
				alert(err);
				
				return "";
			}
			return this;
		}
		var reason = self.model.get("reason");
		reason = reason.replaceTout("\r\n", "<br>");
		self.model.set("reason", reason);
		
		if (!this.model.get("blnSettlement")) {
			//get all parties
			var corporation_id = mymodel.corp_id;
			var arrParties = [];
			var parties = new Parties([], { case_id: mymodel.case_id, case_uuid: kase.get("uuid") });
			parties.fetch({
				success: function(parties) {
					parties.models = parties.sortBy('company_name');  
					parties.trigger('sort');   
					
					var selected = "";
					if (parties.length==1) {
						selected = " selected";
					}
						
					var partie_json = parties.toJSON();
					_.each(partie_json , function(case_partie) {
						var thecase_partie = case_partie.company_name.toUpperCase();
						var val = case_partie.corporation_id + "|C";
						if (case_partie.corporation_id=="-1") {
							val = case_partie.person_id + "|P";
							thecase_partie = case_partie.full_name;
							
							if (case_partie.person_id == corporation_id || parties.length==1) {
								selected = " selected";
							}
						} else {
							if (case_partie.corporation_id == corporation_id || parties.length==1) {
								selected = " selected";
							}
						}
						var case_partie_type = case_partie.type;
						case_partie_type = case_partie_type.replaceAll("_", " ");
						case_partie_type = case_partie_type.toUpperCase();
						
						thecase_partie += "&nbsp;(" + case_partie_type + ")";
						var selected = "";
						
						
						thecase_partie = "<option value='" + val + "'" + selected + ">" + thecase_partie + "</option>";
						arrParties.push(thecase_partie);
						//console.log(thecase_partie);
						//console.log(arrParties);
					});
					self.model.set("parties", arrParties.join("\r\n"));
					//actuall draw the html
					//console.log(arrParties);
					try {
						$(self.el).html(self.template(self.model.toJSON()));		
					}
					catch(err) {
						alert(err);
						
						return "";
					}
					return self;
				}
			});
		} else {		
			//the firm might request a check
			var arrParties = [];
			
			thecase_partie = "<option value='firm' id='request_firm_option'>" + customer_name + " :: Legal Fees</option>";
			arrParties.push(thecase_partie);
			
			//the plaintiff
			
			this.model.set("parties", arrParties.join("\r\n"));
			//alert(arrParties);
			console.log(arrParties);
			//actuall draw the html
			try {
				$(self.el).html(self.template(self.model.toJSON()));		
			}
			catch(err) {
				alert(err);
				
				return "";
			}
		}
		
		return this;
    },
	setNeededDate: function() {
		if (document.getElementById("rush_request").checked) {
			$("#needed_dateInput").val(moment().format("MM/DD/YYYY"));
		}
		$("#amountInput").focus();
	},
	managePayable: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;
		
		$("#payee_holder").hide();
		
		$('.modal-dialog').animate({width:1150, marginLeft:"-500px"}, 1100, 'easeInSine', 
			function() {
				//run this after animation
				var categories = new CheckRequestCategoryCollection();
				
				categories.fetch({
					success: function (data) {
						var mymodel = new Backbone.Model();
						$('#manage_categories_holder').show();
						mymodel.set("holder", "manage_categories_holder");
						$('#manage_categories_holder').html(new checkrequest_category_listing({collection: data, model: mymodel}).render().el);
					}
				});
			}
		);
	},
	otherPayable: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;
		
		$('.modal-dialog').animate({width:1150, marginLeft:"-500px"}, 1100, 'easeInSine', 
			function() {
				//run this after animation
				$("#payable_to").val("");
				$("#payee_holder").fadeIn();
				$("#check_recipient").focus();
				
				$('#manage_categories_holder').hide();
			}
		);
	},
	useCurrentCase: function() {
		var kase = kases.findWhere({case_id: current_case_id});
		$("#case_nameInput").tokenInput("add", {id: current_case_id, name: kase.get("case_name")});
		$("#use_current_case").fadeOut();
	},
	selectRecipient: function(event) {
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var corp_id = arrID[arrID.length - 1];
		
		$("#check_recipient_id").val(corp_id);
		$("#check_recipient").val($("#select_recipient_" + corp_id).html());
		$("#full_addressInput").val($("#address_recipient_" + corp_id).html());
		
		$("#check_recipient_list").hide();
	},
	scheduleSearchRecipient: function() {
		var self = this;
		clearTimeout(checkrequest_search_id);
		
		$("#check_recipient_list").html("");
		$("#check_recipient_list").hide();
		
		checkrequest_search_id = setTimeout(function() {
			self.searchRecipient();
		}, 1234);
	},
	searchRecipient: function() {
		//check to firm for costs
		var search_term = $("#check_recipient").val().replaceTout(" ", "_");
		search_term = search_term.replaceTout("&", "+");
		var the_type = $("#other_payable_to").val().replaceTout(" ", "_");
		var url = "api/corporation/recipient/" + search_term + "/" + current_case_id + "/" + the_type;
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					var partie_json = data;
					var arrRows = [];
					_.each(partie_json , function(recipient) {
						var row = "<div style='border-bottom:1px solid black; padding-bottom:5px'><div><a id='select_recipient_" + recipient.corporation_id + "' style='cursor:pointer' class='select_recipient'>" + recipient.full_name + "</a></div><div id='address_recipient_" + recipient.corporation_id + "' style='color:black'>" + recipient.full_address + "</div></div>";
						
						arrRows.push(row);
					});
					
					if (arrRows.length > 0) {
						$("#check_recipient_list").html(arrRows.join(""));
						$("#check_recipient_list").show();
					}
				}
			}
		});
	},
	updateCorporationID: function(event) {
		var element = event.currentTarget;
		var arrID = element.value.split("|");
		
		//clear out other
		$(".payable_other").val("");
		$("#other_payable_to").val("");
		
		$(".checkrequest #corp_id").val(arrID[0] + "|" + arrID[1]);
		$(".checkrequest #amountInput").val(arrID[2]);
		
		var current_date = moment().format("YYYY-MM-DD");
		var reminder_interval = 1;
		var formValues = "days=+" + reminder_interval + "&date=" + current_date;
		
		$.ajax({
		  method: "POST",
		  url: "api/calculator_post.php",
		  dataType:"json",
		  data: formValues,
		  success:function (data) {
			  if(data.error) {  // If there is an error, show the error tasks
					alert("error");
			  } else {		  
					$("#needed_dateInput").val(moment(data[0].calculated_date).format("MM/DD/YYYY"));
			  }
		  }
		});
	},
	newCheckRequest: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeCheckRequest(element.id);
	} ,
	toggleCheckRequestEdit: function(event) {
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
		$(".checkrequest .editing").toggleClass("hidden");
		$(".checkrequest .span_class").removeClass("editing");
		$(".checkrequest .input_class").removeClass("editing");
		
		$(".checkrequest .span_class").toggleClass("hidden");
		$(".checkrequest .input_class").toggleClass("hidden");
		$(".checkrequest .input_holder").toggleClass("hidden");
		$(".button_row.checkrequest").toggleClass("hidden");
		$(".edit_row .checkrequest").toggleClass("hidden");
		
		showEndtime();
	},
	editCheckRequestField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".checkrequest_" + field_name;
		}
		editField(element, master_class);
	},
	saveCheckRequest:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		var url = "api/notes/add";
		var thenote = "Check was requested by " + login_nickname + " on " + moment().format("MM/DD/YYYY");
		var formValues = "table_name=notes&table_id=&noteInput=" + thenote + "&table_attribute=check_request&type=check_request&title=Kase%Check%20Request&subject=Kase%Check%20Request";
		formValues += "&case_id=" + current_case_id;
		
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
					//actually do it now
					addForm(event, "checkrequest", "checkrequest");
				}
			}
		});
		
		return;
    },
	resetCheckRequestForm: function(event) {
		event.preventDefault();
		this.toggleSettlementEdit(event);
		//this.render();
		//$("#address").hide();
	},
	doTimeouts: function() {
		var self = this;
		var mymodel = this.model.toJSON();
		
		if(mymodel.id=="" || mymodel.id==-1){	
			//$(".checkrequest .edit").trigger("click"); 
			//$(".checkrequest .delete").hide();
			//$(".checkrequest .reset").hide();
		}
		if (this.model.get("blnBulk")) {
			$("#reasonInput").val("Case Settled on " + moment().format("MM/DD/YYYY"));
		}
		$("#request_dateInput").datetimepicker({ validateOnBlur:false, 
			timepicker: false, 
			format: "m/d/Y", 
			closeOnTimeSelect:true
		});
		$("#needed_dateInput").datetimepicker({ validateOnBlur:false, 
			timepicker: false, 
			format: "m/d/Y", 
			minDate: 0
		});
		
		$("#reasonInput").cleditor({
			width: 440,
			height: 130,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
		
		if (!this.model.get("blnSettlement")) {
			initializeGoogleAutocomplete("payable_other_table");
		}
		if (current_case_id > -1) {		
			//medical summary providers
			//get the medical summary parties to get their checks
			var corporation_id = mymodel.corp_id;
			var arrParties = [];
			var arrPartiesBulk = [];
			var medical_billings = new MedicalSummaryCollection({case_id: current_case_id});
			medical_billings.fetch({
				success: function(parties) {
					var partie_json = parties.toJSON();
					var current_parties = self.model.get("parties");
					
					_.each(partie_json , function(case_partie) {
						//only with balance
						var balance = Number(case_partie.balance);
						if (balance > 0) {
							var thecase_partie = case_partie.company_name.toUpperCase();
							var val = case_partie.corporation_id + "|C|" + balance;
							var selected = "";
							if (case_partie.corporation_id == corporation_id) {
								selected = " selected";
								//clear out default
								current_parties = current_parties.replace(" selected", "");
							}
							
							var the_row = "<tr id='request_row_" + case_partie.corporation_id + "_C'><td><input type='checkbox' class='request_checkbox' checked id='request_medical_" + case_partie.corporation_id + "' value='" + val + "' /></td><td align='left' valign='top'>" + thecase_partie + "</td><td align='right' valign='top' style='text-align:right'>$" + formatDollar(balance) + "</td></tr>";
							arrPartiesBulk.push(the_row);
							
							thecase_partie = "<option value='" + val + "'" + selected + ">" + thecase_partie + "   ($" + formatDollar(balance) + ")</option>";
							arrParties.push(thecase_partie);
							
						}
					});
					
					var all_parties = current_parties + "\r\n" + arrParties.join("\r\n");
					self.model.set("parties", all_parties);
					//console.log(arrPartiesBulk[3]);
					//console.log(arrPartiesBulk[4]);
					//actuall draw the html
					$("#payable_to").html(all_parties);
					if (self.model.get("blnBulk")) {
						setTimeout(function() {
							$("#payable_to").css("height", (document.getElementById("payable_to").options.length * 20) + "px");
						}, 1000);
					}
					var current_bulk_parties = $("#payable_to_rows").html();
					$("#payable_to_rows").html(current_bulk_parties + arrPartiesBulk.join("\r\n"));
					
					if (self.model.get("blnBulk")) {
						$("#payable_to_table").show();
						$("#payable_to").hide();
					}
				}
			});
			
			//subrogation carrier
			var parties = new Parties([], { "case_id": current_case_id, "type": "carrier", "panel_title": "Carriers" });
			parties.fetch({
				success: function(data) {
					var carriers = data.toJSON();
					var arrParties = [];
					_.each( carriers, function(carrier) {	
						
						//get the financials			
						var financial = new Financial({"case_id": current_case_id, "corporation_id": carrier.corporation_id});
						financial.fetch({
							success: function(data) {	
								var current_parties = self.model.get("parties");
								
								
								var subros = data.toJSON();
								if (subros.financial_info=="") {
									return;
								}
								var datum = JSON.parse(subros.financial_info)
								var plaint = datum.plaintiff;
								var arrLength = plaint.length;
								var subro_amount = 0;
								var subro_reduced = 0;
								
								for (var i = 0; i < arrLength; i++) {
									if (plaint[i].name=="financial_subroInput") {
										subro_amount = plaint[i].value;
									}
									if (plaint[i].name=="reducedInput") {
										subro_reduction = plaint[i].value;
									}
								}
								var total_balance = Number(subro_amount) - Number(subro_reduction);
								var thecase_partie = subros.company_name;
								var val = subros.corporation_id + "|C|" + total_balance;
								
								var selected = "";
								if (carrier.corporation_id == corporation_id) {
									selected = " selected";
									//clear out default
									current_parties = current_parties.replace(" selected", "");
								}
								if (total_balance > 0) {
									var the_row = "<tr id='request_row_" + subros.corporation_id + "_C'><td><input type='checkbox' class='request_checkbox' checked id='request_carrier_" + carrier.corporation_id + "' value='" + val + "' /></td><td align='left' valign='top'>" + thecase_partie + "</td><td align='right' valign='top' style='text-align:right'>$" + formatDollar(total_balance) + "</td></tr>";//"<tr id='request_row_" + subros.corporation_id + "_C'><td><input type='checkbox' class='request_checkbox' checked id='request_carrier_" + carrier.corporation_id + "' value='" + val + "' /></td><td align='left' valign='top'>" + thecase_partie + "</td><td align='right' valign='top' style='text-align:right'>$" + formatDollar(total_balance) + "</td></tr>";
									
									var current_bulk_parties = $("#payable_to_rows").html();
									$("#payable_to_rows").html(current_bulk_parties + the_row);
									
									thecase_partie = "<option value='" + val + "'" + selected + ">" + thecase_partie + "   ($" + formatDollar(total_balance) + ")</option>";
									
									var all_parties = current_parties + "\r\n" + thecase_partie;
									self.model.set("parties", all_parties);
									//actuall draw the html
									$("#payable_to").html(all_parties);
									if (self.model.get("blnBulk")) {
										$("#payable_to").css("height", (document.getElementById("payable_to").options.length * 20) + "px");
									}
								}
							}
						});
					});
				}
			});
		}
		//settlement totals for firm or plaintiff
		
		var case_id = "";
		if (this.model.get("case_id") == -1 || this.model.get("case_id") == -2) {
			case_id =  this.model.get("case_id");
		} else {
			case_id = current_case_id;
		}
		if (case_id==-2 && current_case_id!=-1) {
			$("#use_current_case").show();
		}
		if (case_id >  0) {		
			var kase = kases.findWhere({case_id: case_id});
			var settlement = new SettlementSheet({injury_id: kase.id});
			settlement.fetch({
				success: function(sheet) {
					var data = sheet.toJSON().data;
					//$(".legal2").css("visibility", "hidden");
					//$(".legal3").css("visibility", "hidden");
					var firm_totals = 0;
					var plaintiff_total = 0;
					if (data != "") {
						var jdata = JSON.parse(data);
						for (var key in jdata) {
							if (jdata.hasOwnProperty(key)) {
								//if (key.indexOf("gross") > -1 && key.indexOf("grossdesc") < 0) {
								if (key.indexOf("legalfees") > -1) {
									var firm_value = 0;
									if (jdata[key] != "") {
										firm_value = Number(jdata[key]);
									}
									firm_totals += firm_value;
								}
								if (key.indexOf("due") > -1) {
									var firm_value = 0;
									if (jdata[key] != "") {
										firm_value = Number(jdata[key]);
									}
									plaintiff_total = firm_value;
									if (customer_id == "1121") {
										if (case_id == "9233" && kase.id == "9268") {
											plaintiff_total = 8290.40;
										}
									}
								}
							}
						}
					}
					//update the firm option
					//document.getElementById("request_firm_option").value = "firm|F|" + firm_totals;
					var current_parties = self.model.get("parties");
					
					all_parties = current_parties.replace("value='firm'", "value='firm|F|" + firm_totals.toFixed(2) + "'");
					all_parties = all_parties.replace(">" + customer_name + "</option", ">" + customer_name  + " :: Legal Fees  ($" + formatDollar(firm_totals) + ")</option");
					
					var current_bulk_parties = $("#payable_to_rows").html();
					var the_row = "<tr id='request_row_firm_F'><td><input type='checkbox' class='request_checkbox' checked id='request_firm_fees' value='firm|F|" + firm_totals.toFixed(2) + "' /></td><td align='left' valign='top'>" + customer_name  + " :: Legal Fees</td><td align='right' valign='top' style='text-align:right'>$" + formatDollar(firm_totals) + "</td></tr>";
					$("#payable_to_rows").html(the_row + current_bulk_parties);
					
					//plaintiff total
					if (plaintiff_total > 0) {
						if (kase.get("plaintiff_id")!="-1") {
							var val = kase.get("plaintiff_id") + "|C|" + plaintiff_total.toFixed(2);
							var the_row = "<tr id='request_row_" + kase.get("plaintiff_id") + "_C'><td><input type='checkbox' class='request_checkbox' checked id='request_plaintiff_" + kase.get("plaintiff_id") + "' value='" + val + "' /></td><td align='left' valign='top'>" + kase.get("plaintiff") + "</td><td align='right' valign='top' style='text-align:right'>$" + formatDollar(plaintiff_total) + "</td></tr>";
							
							var thecase_partie = "<option value='" + val + "'>" + kase.get("plaintiff")  + "   ($" + formatDollar(plaintiff_total) + ")</option>";
						} else {
							var val = kase.get("applicant_id") + "|P|" + plaintiff_total.toFixed(2);
							var the_row = "<tr id='request_row_" + kase.get("applicant_id") + "_P'><td><input type='checkbox' class='request_checkbox' checked id='request_applicant_" + kase.get("applicant_id") + "' value='" + val + "' /></td><td align='left' valign='top'>" + kase.get("full_name") + "</td><td align='right' valign='top' style='text-align:right'>$" + formatDollar(plaintiff_total) + "</td></tr>";
							var thecase_partie = "<option value='" + val + "'>" + kase.get("full_name") + "   ($" + formatDollar(plaintiff_total) + ")</option>";
						}
						all_parties = thecase_partie + all_parties;
						self.model.set("parties", all_parties);
						//actuall draw the html
						$("#payable_to").html(all_parties);
						
						var current_bulk_parties = $("#payable_to_rows").html();
						$("#payable_to_rows").html(the_row + current_bulk_parties);
					}
				}
			});
			
			
			//check to firm for costs
			var url = "api/medicalbillingsummary/" + case_id;
			
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						//stored values 
						var total_costs = Number(data.costs);
						var val = "firm|X|" + total_costs.toFixed(2);
						var the_row = "<tr id='request_row_firm_X'><td><input type='checkbox' class='request_checkbox' checked id='request_firm' value='" + val + "' /></td><td align='left' valign='top'>" + customer_name + " :: Costs Advanced</td><td align='right' valign='top' style='text-align:right'>$" + formatDollar(total_costs) + "</td></tr>";
						
						var thecase_partie = "<option value='" + val + "'>" + customer_name + " :: Costs Advanced  ($" + formatDollar(total_costs) + ")</option>";
						var current_parties = self.model.get("parties");
						all_parties = current_parties + thecase_partie;
						self.model.set("parties", all_parties);
						//actuall draw the html
						$("#payable_to").html(all_parties);
						
						var current_bulk_parties = $("#payable_to_rows").html();
						$("#payable_to_rows").html(the_row + current_bulk_parties);
					}
				}
			});
		}
		
		if (this.model.get("blnBulk")) {
			var current_date = moment().format("YYYY-MM-DD");
			var reminder_interval = 1;
			var formValues = "days=+" + reminder_interval + "&date=" + current_date;
			
			$.ajax({
			  method: "POST",
			  url: "api/calculator_post.php",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
						alert("error");
				  } else {		  
						$("#needed_dateInput").val(moment(data[0].calculated_date).format("MM/DD/YYYY"));
				  }
			  }
			});
		}
		
		if (this.model.get("id") > -1) {
			//make sure it's not a payable_to
			var other_payable_to = $("#other_payable_to").html();
			if (other_payable_to.indexOf('value="' + this.model.get("payable_to") + '"') > -1) {
				$("#payable_to_row").show();
				$("#other_payable_to").val(this.model.get("payable_to"));
				$("#other_payable_to").trigger("change");
				$("#modal_save_holder").hide();
				
				setTimeout(function() {
					$("#payable_to").val("");
				}, 1000);
				
				//look up the recipient
				var corporation = new Corporation({id: self.model.get("payable_id"), type: "recipient"});
				corporation.fetch({
					success: function (corp) {
						var jdata = corp.toJSON();
						
						$("#check_recipient").val(jdata.full_name);
						$("#full_addressInput").val(jdata.full_address);	
						$("#suiteInput").val(jdata.suite);
					}
				});
			}
			
			if ($("#other_payable_to").val()=="") {
				$("#payable_to_span").html(this.model.get("payable_to"));
				$("#payable_to").hide();
			}
		}
		
		if (case_id == -2) {
			//lookup
			var theme = {
				theme: "event", 
				tokenLimit:1,
				onAdd: function(item) {
					$("#case_id").val(item.id);
					self.model.set("case_id", item.id);
					
					if (blnTrustRequired) {						
						var account_type = "operating";
						if (self.model.get("blnSettlement") && !isWCAB(self.model.get("case_type"))) {
							account_type = "trust";
						}
						var account =  new Account({"account_type": account_type});
						account.fetch({
							success: function (data) {
								$("#account_id").val(account.id);
								if (account_type=="operating") {
									account_type = "Cost Trust";
								}
								$("#account_name").html(account.account_type.capitalizeWords() + " Account");
								$("#account_name_holder").show();
							}
						});
					}
					//reload the drop down
					if (!self.model.get("blnSettlement")) {
						//get all parties
						var corporation_id = "";
						var arrParties = ['<option value="">Select a Partie from List</option>'];
						var parties = new Parties([], { case_id: item.id });
						parties.fetch({
							success: function(parties) {
								parties.models = parties.sortBy('company_name');  
								parties.trigger('sort');   
								
								var selected = "";
								if (parties.length==1) {
									selected = " selected";
								}
									
								var partie_json = parties.toJSON();
								_.each(partie_json , function(case_partie) {
									var thecase_partie = case_partie.company_name.toUpperCase();
									var val = case_partie.corporation_id + "|C";
									if (case_partie.corporation_id=="-1") {
										val = case_partie.person_id + "|P";
										thecase_partie = case_partie.full_name;
									}
									var case_partie_type = case_partie.type;
									case_partie_type = case_partie_type.replaceAll("_", " ");
									case_partie_type = case_partie_type.capitalizeWords();
									
									thecase_partie += "&nbsp;(" + case_partie_type + ")";
									var selected = "";
									
									
									thecase_partie = "<option value='" + val + "'" + selected + ">" + thecase_partie + "</option>";
									arrParties.push(thecase_partie);
								});
								$("#payable_to").html(arrParties.join("\r\n"));
							}
						});
					}
					//now we can save
					$(".checkrequest.save").show();
				}
			}
			$("#case_nameInput").tokenInput("api/kases/tokeninput", theme);
			$(".token-input-list-event").css("width", "440px");
			$("#case_input_holder").show();
			
			//disable the save
			$(".checkrequest.save").hide();
		}
		
		if ($(".modal-title").html().indexOf("read-only") > -1) {
			//disable the save
			$(".checkrequest.save").hide();
		}
		if (blnTrustRequired) {		
			if (self.model.get("case_id") > 0) {
				if (self.model.get("blnSettlement") && !isWCAB(self.model.get("case_type"))) {
					//account
					var account =  new Account({"account_type": "trust"});
					account.fetch({
						success: function (data) {
							$("#account_id").val(account.id);
							$("#account_name").html("Trust Account");
							$("#account_name_holder").show();
						}
					});
				} else {
					var account =  new Account({"account_type": "operating"});
					account.fetch({
						success: function (data) {
							$("#account_id").val(account.id);
							$("#account_id").val(account.id);
							$("#account_name").html("Cost Trust Account");
							$("#account_name_holder").show();
						}
					});
				}
			}
		}
	}
});

window.checkrequest_category_listing = Backbone.View.extend({
	initialize:function () {
    },
	events: {
		/*"click #select_all_filters":			"selectAll",*/
		"click #new_checkrequest_category_button":		"newCheckRequestCategory",
		"click #save_checkrequest_category":			"saveCheckRequestCategory",
		"click .checkrequest_category_checkbox":		"activateCheckRequestCategory",
		"click .checkrequest_category_cell":			"editCheckRequestCategory",
		"click .checkrequest_category_save":			"updateCheckRequestCategory",
		"click #show_all_category":						"showAllCategory"
	},
    render:function () {	
		if (typeof this.template != "function") {
			this.model.set("holder", "manage_categories_holder");
			var view = "checkrequest_category_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   	
		var self = this;
		
		//what are we looking for?
		
		//let's cycle through the types
		var mymodel = this.model.toJSON();
		var category_level = mymodel.category_level;
		var arrCats = this.collection.toJSON();
		var arrRows = [];
		var current_category = $("#check_typeInput").val();
		
		var arrOptions = [];
		var option_selected = "";
		if (current_category=="") {
			option_selected = " selected";
		}
		var option = '<option value="" class="category_option"' + option_selected + '>Select from List</option>';
		arrOptions.push(option);
		
		//arrCats.forEach(function(element, index, array) {
		
		_.each(arrCats , function(checkrequest_category) {
			if (typeof checkrequest_category.id != "undefined") {
				var index = checkrequest_category.id
				var checked = " checked";
				var row_display = "";
				var cell_display = "color:white";
				var row_class = "active_filter";
				if (checkrequest_category.deleted=="Y") {
					checked = "";
					row_display = "display:none";
					cell_display = "color:red; text-decoration:line-through;";
					row_class = "deleted_filter";
				}
				var input = "<input class='hidden' type='text' id='checkrequest_category_value_" + index + "' value='" + checkrequest_category.checkrequest_type + "' />&nbsp;<button class='btn btn-xs btn-success checkrequest_category_save hidden' id='checkrequest_category_save_" + index + "'>Save</button>";
				var therow = "<tr class='" + row_class + "' style='" + row_display + "'><td class='checkrequest_category'><input type='checkbox' class='checkrequest_category_checkbox hidden' value='Y' title='Uncheck to stop using this category.  Old records currently using this category will not be affected' id='checkrequest_category_" + index + "' name='checkrequest_category_" + index + "'" + checked + "></td><td class='checkrequest_category' style='" + cell_display + "'><span id='checkrequest_category_span_" + index + "' class='checkrequest_category_cell' style='cursor:pointer' title='Click to edit this category'>" + checkrequest_category.checkrequest_type + "</span>" + input + "</td></tr>";
				arrRows.push(therow);
				
				//are we using a deleted category
				var current_category = self.model.get("case_category");
				var blnUsingDeleted = (arrDeletedCheckRequestCategory.indexOf(current_category) > -1);
				
				if (checkrequest_category.deleted!="Y" || blnUsingDeleted) {
					//the drop down has to match
					var option_selected = "";
					if (checkrequest_category.category == current_category) {
						option_selected = " selected";
					}
					var option = '<option value="' + checkrequest_category.checkrequest_type + '" class="wcab_category_option"' + option_selected + '>' + checkrequest_category.checkrequest_type + '</option>';
					arrOptions.push(option);
				}
			}
		});
		var html = "<table id='checkrequest_category_table'>" + arrRows.join("") + "</table>";
		try {
			$(this.el).html(this.template({html: html}));
			$("#check_typeInput").html(arrOptions.join("\r\n"));
		}
		catch(err) {
			alert(err);
			
			return false;
		}
		return this;
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".document_filter_checkbox").prop("checked", element.checked);
	},
	editCheckRequestCategory:function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var casecategory_id = arrID[arrID.length - 1];
		
		//not open nor closed
		var current = $("#checkrequest_category_value_" + casecategory_id).val();
		if (current=="Open" || current=="Closed") {
			return;
		}
		$("#checkrequest_category_span_" + casecategory_id).fadeOut();
		$("#checkrequest_category_value_" + casecategory_id).toggleClass("hidden");
		$("#checkrequest_category_save_" + casecategory_id).toggleClass("hidden");
		$("#checkrequest_category_" + casecategory_id).toggleClass("hidden");
	},
	showAllCategory: function() {
		$("#show_all_category").hide();
		$(".deleted_filter").show();
	},
	newCheckRequestCategory:function(event) {
		event.preventDefault();
		$("#new_checkrequest_category_button").fadeOut();
		$("#new_checkrequest_category_holder").fadeIn(function() {
			$("#new_checkrequest_category").focus();
		});
	},
	saveCheckRequestCategory:function(event) {
		event.preventDefault();
		var url = 'api/checkrequest_type/add';
		var mymodel = this.model.toJSON();
		
		var checkrequest_type = $("#new_checkrequest_category").val();
		var formValues = "checkrequest_type=" + checkrequest_type;
		
		var title = $("#checkrequest_category_title").html();
		$("#checkrequest_category_title").html("Saving...");
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#checkrequest_category_title").html("<span style='color:lime'>Saved&nbsp;&#10003;</span>");
					
					$("#manage_payableto").trigger("click");
					
					//repop the select
					var cells = $(".checkrequest_category_cell");
					var arrLength = cells.length;
					var arrOptions = [];
					var option = '<option value="">Select from List</option>';
					arrOptions.push(option);
					for(var i =0; i < arrLength; i++) {
						var id = cells[i].id.split("_")[3];
						if (cells[i].style.display!="none" || (!$("#checkrequest_category_value_" + id).hasClass("hidden") && document.getElementById("checkrequest_category_" + id).checked)) {
							var option = '<option value="' + cells[i].innerText.toLowerCase() + '">' + cells[i].innerText + '</option>';
							arrOptions.push(option);
						}
					}
					$("#other_payable_to").html(arrOptions.join(""));
					
					setTimeout(function() {
						$("#checkrequest_category_title").html(title);
					}, 2500);
				}
			}
		});
		//
	},
	activateCheckRequestCategory: function(event) {
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var casecategory_id = arrID[arrID.length - 1];
		
		$("#checkrequest_category_save_" + casecategory_id).trigger("click");
	},
	updateCheckRequestCategory:function(event) {
		//return;
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var casecategory_id = arrID[arrID.length - 1];
		var checkbox = document.getElementById("checkrequest_category_" + casecategory_id);
		var deleted = "Y";
		if (checkbox.checked) {
			deleted = "N";
		}
		var casecategory = $("#checkrequest_category_value_" + casecategory_id).val();
		var mymodel = this.model.toJSON();
		
		var url = 'api/checkrequest_type/update';
		var formValues = "checkrequest_type_id=" + casecategory_id + "&checkrequest_type=" + casecategory + "&deleted=" + deleted;
		
		var title = $("#checkrequest_category_title").html();
		$("#checkrequest_category_title").html("Saving...");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#checkrequest_category_title").html("<span style='color:lime'>Saved&nbsp;&#10003;</span>");
					
					$("#manage_payableto").trigger("click");
					
					//repop the select
					var cells = $(".checkrequest_category_cell");
					var arrLength = cells.length;
					var arrOptions = [];
					var option = '<option value="">Select from List</option>';
					arrOptions.push(option);
					for(var i =0; i < arrLength; i++) {
						var id = cells[i].id.split("_")[3];
						if (cells[i].style.display!="none" || (!$("#checkrequest_category_value_" + id).hasClass("hidden") && document.getElementById("checkrequest_category_" + id).checked)) {
							var option = '<option value="' + cells[i].innerText.toLowerCase() + '">' + cells[i].innerText + '</option>';
							arrOptions.push(option);
						}
					}
					$("#other_payable_to").html(arrOptions.join(""));
					
					setTimeout(function() {
						$("#checkrequest_category_title").html(title);
					}, 2500);
				}
			}
		});
	}
});