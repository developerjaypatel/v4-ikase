function saveCalendarModal(event) {
	addForm(event, "calendar");
}
function saveBlockedDates(event) {
	addForm(event, "blocked");

	setTimeout(function () {
		var arrHash = document.location.hash.split("/");
		window.Router.prototype.displayCalendarEvents(arrHash[1], arrHash[0]);
	}, 1000);
}
function saveMedicalBillingModal() {
	$("#billing_gifsave").show();
	$("#medicalbilling save").hide();
	addForm(event, "medicalbilling");
}
function saveOtherBillingModal() {
	$("#billing_gifsave").show();
	$("#otherbilling save").hide();
	addForm(event, "otherbilling");
}
function saveFee() {
	//billed must be positive for now
	if ($("#fee_billed_div").css("display") != "none") {
		var fee_billed = $("#fee_billedInput").val();

		if (fee_billed == 0 || fee_billed == "") {
			$("#fee_billedInput").css("border", "2px solid red");
			("#fee_billedInput").focus();
			return;
		}
	}
	if ($("#fee_payment_div").css("display") != "none") {
		var paid_fee = $("#paid_feeInput").val();
		if (paid_fee == 0 || paid_fee == "") {
			$("#paid_feeInput").css("border", "2px solid red");
			("#paid_feeInput").focus();
			return;
		}
	}

	$("#modal_save_holder").html('<img src="img/loading_spinner_1.gif" width="20" height="20" id="gifsave" class="personal_injury" style="margin-top: -5px;">&nbsp;&nbsp;');
	$("#fee_billedInput").css("border", "none");

	addForm(event, "fee", "fee");
}
function saveSingleEmailAssignModal() {
	//event.preventDefault();
	var ids = $(".webmail_assign #message_id").val();	//only one, loop below is for one only...
	saveBulkEmailAssignModal(ids);
}
function saveMultiEmailAssignModal() {
	var arrThreads = $("#bulk_thread_id").val().split("|");
	var arrLength = arrThreads.length;
	for (var i = 0; i < arrLength; i++) {
		var id = arrThreads[i];
		//event.preventDefault();
		var ids = $("#thread_message_ids_" + id).val();
		saveBulkEmailAssignModal(ids);
	}
}
function saveInvoiceAssign() {
	var case_id = $("#case_idInput").val();
	$("#myModal4").modal("toggle");

	//go to letter section for now
	document.location.href = "#lettersinv/" + case_id;
}
function saveBulkEmailAssignModal(ids) {
	if (typeof ids == "undefined") {
		var ids = $(".bulk_webmail_assign #ids").val();
	}
	var arrIDs = ids.split(",");
	var case_id = $("#case_idInput").val();
	var billing_time = "0";
	$(".modal #billing_time_dropdownInput").val();
	if (case_id == "") {
		alert("You must select a Kase.  Please type in the search box to bring up kases");
		return;
	}
	blnKaseChangesDetected = true;

	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();

	//attachments
	var kase_attaches = $(".kase_attach_assign");
	var arrAttach = [];
	var arrLength = kase_attaches.length;
	for (var i = 0; i < arrLength; i++) {
		the_checkbox = kase_attaches[i];
		if (the_checkbox.checked) {
			arrAttach.push(the_checkbox.value);
		}
	}
	var case_attach = arrAttach.join("|");
	var case_note = $("#notes_webmail").val();
	for (var int = 0; int < arrIDs.length; int++) {
		var theid = arrIDs[int].trim();
		if (int > 0) {
			case_note = "";
			case_attach = "";
		}
		var formValues = "id=" + theid + "&case_id=" + case_id + "&type=webmail&table_name=message&table_attribute=webmail&billing_time=" + billing_time + "&case_note=" + encodeURIComponent(case_note) + "&case_attach=" + encodeURIComponent(case_attach);

		var url = "api/gmail/assign";

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else {
					resetCurrentContent();

					if ($("#bulk_thread_id").length > 0) {
						var web_id = $("#bulk_thread_id").val();
						$("#assignthread_" + web_id).hide();
						var row_class = "threads_row_";
					} else {
						var web_id = data.email_id;
						$("#assign_" + web_id).hide();
						var row_class = "message_row_";
					}
					var case_name = "";
					if (typeof data.case_name != "undefined") {
						case_name = data.case_name;
					}
					//hide the save button
					//$("#webmail_save_" + web_id).hide();

					/*
					$("#webmail_save_" + web_id).fadeOut(function() {
						$("#disabled_webmail_save_" + web_id).fadeIn();
					});
					*/

					//get the color
					//var back_color = $(".kase_webmail_row_" + web_id).css("background");
					var back_color = $("." + row_class + web_id).css("background");
					//mark it all green
					$("." + row_class + web_id).css("background", "green");

					if (case_name != "") {
						var element_root = "#thread_case_";
						if ($(element_root + web_id).length == 0) {
							element_root = "#message_case_";
						}
						$(element_root + web_id).prepend("<span style='background:green'>" + case_name + "&nbsp;&#10003;</span>");
					}
					setTimeout(function () {
						//hide the processed row, no longer a pending email
						$("." + row_class + web_id).fadeOut();
						//$("." + row_class + web_id).css("background", back_color);
						$("#hide_preview_pane").trigger("click");
					}, 2500);

					refreshPendingMessagesIndicator(true);
				}
			}
		});
	}
	$("#myModal4").modal("toggle");
}
function saveTransferKaseModal() {
	var blnTransferByLetter = (document.location.hash == "#kasestransfer");

	if (!blnTransferByLetter) {
		//event.preventDefault();
		$("#input_for_checkbox").hide();
		$("#modal_save_holder").hide();
		$("#gifsave").css("margin-top", "-5px");
		$("#gifsave").show();

		var ids = $(".bulk_kase_transfer #ids").val();
	} else {
		var ids = $("#letter_ids").val();
	}
	var arrIDs = ids.split(", ");
	var assignee = $("#assigneeInput").val();
	var from = $("#user_id").val();
	var transfer_tasks = "N";
	if (document.getElementById("transfer_tasks").checked) {
		transfer_tasks = "Y";
	}
	var transfer_events = "N";
	/*
	if (document.getElementById("transfer_events").checked) {
		transfer_events = "Y";
	}
	*/
	var formValues = {
		ids: ids,
		assignee: assignee,
		from: from,
		type: "transfer_kase",
		table_name: "kase",
		transfer_tasks: transfer_tasks,
		transfer_events: transfer_events
	};
	var url = "api/kases/transfer";

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				console.log(data.error.text);
			} else {
				resetCurrentContent();
				if (!blnTransferByLetter) {
					for (var int = 0; int < arrIDs.length; int++) {
						var theid = arrIDs[int];
						//get the color
						//$("#myModal4").modal("toggle");
						var back_color = $(".kase_data_row_" + theid).css("background");
						//mark it all green
						$(".kase_data_row_" + theid).css("background", "green");
						setTimeout(function () {
							//hide the processed row, no longer a batch scan
							//$(".kase_row_" + theid).fadeOut();
							$(".kase_data_row_" + theid).css("background", back_color);
						}, 1500);
					};
					setTimeout(function () {
						window.Router.prototype.showKaseSummary();
					}, 2000);
				} else {
					$("#transfer_title").html("<span style='color:lime'>Transfer Completed &#10003;</span>");

					setTimeout(function () {
						window.Router.prototype.transferKases();
					}, 2500);
				}
			}
		}
	});
	$("#myModal4").modal("toggle");
}
function saveTransferTaskModal() {
	//event.preventDefault();
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();

	var ids = $(".bulk_task_transfer #ids").val();
	var arrIDs = ids.split(", ");
	var assignee = $("#assigneeInput").val();
	var from = $(".glass_header #user_id").val();

	var formValues = {
		ids: ids,
		assignee: assignee,
		from: from,
		type: "transfer_task",
		table_name: "task"
	};
	var url = "api/tasks/transfer";

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				console.log(data.error.text);
			} else {
				resetCurrentContent();

				for (var int = 0; int < arrIDs.length; int++) {
					var theid = arrIDs[int];
					//get the color
					//$("#myModal4").modal("toggle");
					var back_color = $(".task_row_" + theid).css("background");
					//mark it all green
					$(".task_row_" + theid).css("background", "green");
					setTimeout(function () {
						//hide the processed row, no longer a batch scan
						//$(".task_row_" + theid).fadeOut();
						$(".task_row_" + theid).css("background", back_color);
					}, 1500);
				};
				setTimeout(function () {
					window.Router.prototype.showTaskSummary();
				}, 2000);
			}
		}
	});
	$("#myModal4").modal("toggle");
}
function saveBulkDateChangeModal() {
	//event.preventDefault();
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();

	var ids = $(".bulk_date_change #ids").val();
	var arrIDs = ids.split(", ");
	var dateandtime = $("#task_dateInput").val();
	for (var int = 0; int < arrIDs.length; int++) {
		var theid = arrIDs[int];
		var formValues = {
			id: theid,
			dateandtime: dateandtime,
			type: "date_change",
			table_name: "task"
		};
		var url = "api/task/update/date";

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else {
					resetCurrentContent();

					for (var int = 0; int < arrIDs.length; int++) {
						var theid = data.id;
						//get the color
						//$("#myModal4").modal("toggle");
						var back_color = $(".task_row_" + theid).css("background");
						//mark it all green
						$(".task_row_" + theid).css("background", "green");
						setTimeout(function () {
							//hide the processed row, no longer a batch scan
							//$(".task_row_" + theid).fadeOut();
							$(".task_row_" + theid).css("background", back_color);
						}, 1500);
						location.reload();
					};

				}
			}
		});
	}
	$("#myModal4").modal("toggle");
}
function saveBulkDateChangeEventModal() {
	//event.preventDefault();
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();

	var ids = $(".bulk_date_change #ids").val();
	var arrIDs = ids.split(", ");
	var dateandtime = $("#task_dateInput").val();
	for (var int = 0; int < arrIDs.length; int++) {
		var theid = arrIDs[int];
		var formValues = {
			id: theid,
			dateandtime: dateandtime,
			type: "date_change",
			table_name: "event"
		};
		var url = "api/event/update/date";

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else {
					resetCurrentContent();
					for (var int = 0; int < arrIDs.length; int++) {
						var theid = data.id;
						//get the color
						//$("#myModal4").modal("toggle");
						var back_color = $(".occurence_row_" + theid).css("background");
						//mark it all green
						$(".occurence_row_" + theid).css("background", "green");
						setTimeout(function () {
							//hide the processed row, no longer a batch scan
							//$(".task_row_" + theid).fadeOut();
							$(".occurence_row_" + theid).css("background", back_color);
							location.reload();
						}, 1500);

					};

				}
			}
		});
	}
	$("#myModal4").modal("toggle");
}
function saveBulkImportAssignModal() {
	//event.preventDefault();
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();

	var ids = $(".bulk_import_assign #ids").val();
	var arrIDs = ids.split(", ");
	var case_id = $("#case_idInput").val();
	for (var int = 0; int < arrIDs.length; int++) {
		var theid = arrIDs[int];

		var name = $("#stack_name_" + theid).val();
		//var case_uuid = $("#stack_case_id_" + theid).val();
		//show the two drop downs and the check box
		var type = $("#stack_type_" + theid).val();
		var category = $("#stack_category_" + theid).val();
		var subcategory = $("#stack_subcategory_" + theid).val();
		var stack_notify = $("#stack_notify_" + theid).val();
		var note = $("#stack_note_" + theid).val();

		var formValues = {
			name: name,
			document_id: theid,
			type: type,
			category: category,
			subcategory: subcategory,
			note: note,
			case_id: case_id
		};
		var url = "api/stacks/add";

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else {
					resetCurrentContent();
					//capture the current backcolor, make it green
					var back_color = $(".document_row_" + theid).css("background-color");
					//mark it all green
					//var ids = $(".bulk_import_assign #ids").val();
					var theid = ids.split(", ");
					for (var k = 0; k < theid.length; k++) {
						var row_id = theid[k];
						$(".document_row_" + row_id).css("background", "green");
					}
					setTimeout(function () {
						var theid = ids.split(", ");
						for (var k = 0; k < theid.length; k++) {
							var row_id = theid[k];
							//restore original backcolor
							$(".document_row_" + row_id).css("background", "url(https://"+ location.hostname +"/img/glass_row.png)");
						}
					}, 2500);
				}
			}
		});
	}
	$("#myModal4").modal("toggle");
}
function saveDeductionModal(event) {
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	$("#myModalLabel").html("Saving...");
	$blnValid = $("#deduction_form").parsley('validate');
	if (!$blnValid) {
		$(".parsley-error").css('border', '2px solid red');
		$(".parsley-error").css('z-index', '4205');
		$("#gifsave").hide();
		$("#modal_save_holder").show();

		$(".parsley-error-list").hide();
		return;
	}
	var url = "api/deduction/save";
	var formValues = $("#deduction_form").serialize();

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				resetCurrentContent();

				var deductions = new DeductionCollection({ case_id: current_case_id });
				deductions.fetch({
					success: function (data) {
						$("#myModalLabel").html("Saved&nbsp;&#10003;");
						var dkase = kases.findWhere({ case_id: current_case_id });
						var holder_id = "deduction_holder";
						if ($("#settlement_deduct_holder").length > 0) {
							holder_id = "settlement_deduct_holder";
							var deductions = data.toJSON();
							if (deductions.length > 0) {
								$("#settlement_deduct_button").html("Deductions (" + deductions.length + ")");
							}
						}
						dkase.set("holder", "#" + holder_id);
						dkase.set("page_title", "Deduction");
						dkase.set("embedded", false);

						$('#' + holder_id).html(new deduction_listing_view({ collection: data, model: dkase }).render().el);

						$("#myModalLabel").css("color", "lime");
						$("#gifsave").hide();
						//close
						setTimeout(function () {
							$("#myModal4").modal("toggle");
						}, 2500);
					}
				});
			}
		}
	});
}

function saveAdjustmentModal(event) {
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	$("#myModalLabel").html("Saving...");
	$blnValid = $("#adjustment_form").parsley('validate');
	if (!$blnValid) {
		$(".parsley-error").css('border', '2px solid red');
		$(".parsley-error").css('z-index', '4205');
		$("#gifsave").hide();
		$("#modal_save_holder").show();

		$(".parsley-error-list").hide();
		return;
	}
	var url = "api/adjustment/save";
	var formValues = $("#adjustment_form").serialize();

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				resetCurrentContent();

				$("#myModalLabel").html("Saved&nbsp;&#10003;");

				$("#myModalLabel").css("color", "lime");
				$("#gifsave").hide();
				//close
				setTimeout(function () {
					//was the amount edited
					var amountOriginal = $("#amountOriginalInput").val();
					var amount = $("#amountInput").val();

					$("#myModal4").modal("toggle");

					if (amountOriginal != amount) {
						//relist it all
						var account_type = document.location.hash.split("/")[2];
						window.Router.prototype.listBankAccounts(account_type);
					} else {
						//relist only adjustments
						$("#" + $(".review_adjustments")[0].id).trigger("click");
					}

				}, 2500);
			}
		}
	});
}

function saveNegotiationModal(event) {
	//billed must be positive for now
	var amountInput = $("#amountInput").val();
	if (amountInput == 0 || amountInput == "") {
		$("#amountInput").css("border", "2px solid red");
		$("#amountInput").focus();
		return;
	}

	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	$("#myModalLabel").html("Saving...");
	$blnValid = $("#negotiation_form").parsley('validate');
	if (!$blnValid) {
		$(".parsley-error").css('border', '2px solid red');
		$(".parsley-error").css('z-index', '4205');
		$("#gifsave").hide();
		$("#modal_save_holder").show();

		$(".parsley-error-list").hide();
		return;
	}
	var url = "api/negotiation/save";
	var formValues = $("#negotiation_form").serialize();

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				resetCurrentContent();

				var negotiations = new NegotiationCollection({ case_id: current_case_id });
				negotiations.fetch({
					success: function (data) {
						$("#myModalLabel").html("Saved&nbsp;&#10003;");
						var dkase = kases.findWhere({ case_id: current_case_id });
						var holder_id = "negotiation_holder";
						if ($("#settlement_negotiation_holder").length > 0) {
							holder_id = "settlement_negotiation_holder";
							var negotiations = data.toJSON();
							if (negotiations.length > 0) {
								$("#settlement_negotiation_button").html("Negotiations (" + negotiations.length + ")");
							}
						}
						if ($("#carrier_neg").length > 0) {
							//negotiation
							var corporation_id = document.location.hash.split("/")[2];
							var negotiations = new NegotiationCollection({ case_id: current_case_id, "corporation_id": corporation_id });
							negotiations.fetch({
								success: function (data) {
									var nkase = new Backbone.Model();
									nkase.set("holder", "#carrier_neg");
									nkase.set("page_title", "Negotiation");
									nkase.set("embedded", true);
									$('#carrier_neg').html(new negotiation_listing_view({ collection: data, model: nkase }).render().el);
								}
							});
						}
						dkase.set("holder", "#" + holder_id);
						dkase.set("page_title", "Negotiation");
						dkase.set("embedded", false);

						$('#' + holder_id).html(new negotiation_listing_view({ collection: data, model: dkase }).render().el);

						$("#myModalLabel").css("color", "lime");
						$("#gifsave").hide();
						//close
						setTimeout(function () {
							$("#myModal4").modal("toggle");
						}, 2500);
					}
				});
			}
		}
	});
}

function saveLostIncomeModal(event) {
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	$blnValid = $("#lostincome_form").parsley('validate');
	if (!$blnValid) {
		$(".parsley-error").css('border', '2px solid red');
		$(".parsley-error").css('z-index', '4205');
		$("#gifsave").hide();
		$("#modal_save_holder").show();

		$(".parsley-error-list").hide();
		return;
	}
	var url = "api/lostincome/save";
	var formValues = $("#lostincome_form").serialize();

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				resetCurrentContent();

				//lime
				$("#myModalLabel").css("color", "lime");
				$("#gifsave").hide();
				//close
				setTimeout(function () {
					$("#myModal4").modal("toggle");
				}, 2500);

				var lostincomes = new LostIncomeCollection({ "case_id": current_case_id });	//, "corporation_id": this.model.get("id")
				lostincomes.fetch({
					success: function (lostincomes) {
						var mymodel = new Backbone.Model();
						mymodel.set("holder", "#employer_lostincome");
						mymodel.set("glass", "card_dark_7");
						$("#employer_lostincome").html(new lostincome_listing_view({ collection: lostincomes, model: mymodel }).render().el);
						//now show 
						$('#employer_lostincome').fadeIn(function () {
							$('#employer_lostincome').css("width", "50%");
						});
					}
				});
			}
		}
	});
}
function saveCheckModal(event) {
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	$blnValid = $("#check_form").parsley('validate');

	if ($("#check_dateInput").val() == "") {
		$("#check_dateInput").val($("#transaction_dateInput").val());
	}
	if (!$blnValid) {
		$(".parsley-error").css('border', '2px solid red');
		$(".parsley-error").css('z-index', '4205');
		$("#gifsave").hide();
		$("#modal_save_holder").show();
		$("#apply_notes").hide();
		$(".parsley-error-list").hide();
		return;
	}
	if (blnSavePayableTo) {
		if ($("#other_payable_to").val() != "") {
			//require a recipient
			if ($("#check_recipient").val() == "") {
				$("#check_recipient").css("border", "2px solid red");
				$("#check_recipient").focus();
				return;
			}
		}

		if ($("#payable_to").val() == "" && $("#other_payable_to").val() == "") {
			$("#payable_to").css("border", "2px solid red");
			$("#payable_to").focus();
			return;
		}
	}

	//amount
	if ($("#check_payment_holder").css("display") != "none") {
		//all three cannot be zero
		if (Number($("#amount_dueInput").val()) == 0 && Number($("#paymentInput").val()) == 0 && Number($("#adjustmentInput").val()) == 0) {
			$("#amount_dueInput").css("border", "2px solid red");
			$("#paymentInput").css("border", "2px solid red");
			$("#adjustmentInput").css("border", "2px solid red");
			$("#amount_dueInput").focus();
			return;
		}
	} else {
		//just the amount
		if (Number($("#amount_dueInput").val()) == 0) {
			$("#amount_dueInput").css("border", "2px solid red");
			$("#amount_dueInput").focus();
			return;
		}
	}

	if ($("#check_recipient_id").val() == "") {
		if ($("#other_payable_to").val() != "") {
			//save the recipient as corporation
			formValues = "table_name=corporation&type=recipient";
			formValues += "&additional_partie=y";
			formValues += "&adhoc_fields=&case_id=" + current_case_id + "&full_name=" + encodeURIComponent($("#check_recipient").val());
			formValues += "&company_name=" + encodeURIComponent($("#other_payable_to").val());
			formValues += "&street=" + encodeURIComponent($("#street_payable_other_table").val());
			formValues += "&suite=" + encodeURIComponent($("#suiteInput").val());
			formValues += "&city=" + encodeURIComponent($("#city_payable_other_table").val());
			formValues += "&state=" + encodeURIComponent($("#administrative_area_level_1_payable_other_table").val());
			formValues += "&zip=" + encodeURIComponent($("#postal_code_payable_other_table").val());
			formValues += "&full_address=" + encodeURIComponent($("#full_addressInput").val());;

			var url = "api/corporation/add";

			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					blnSaving = false;
					if (data.error) {  // If there is an error, show the error messages
						console.log(data.error.text);
					} else {
						$("#corp_id").val(data.id);
						addForm(event, "check");
					}
				}
			});

			return;
		}
	} else {
		//the corp was a lookup
		$("#corp_id").val($("#check_recipient_id").val());
	}

	//is this a operating deposit (add check_from to memo)
	if ($("#account_type").length > 0) {
		var account_type = $("#account_type").val();
		if (account_type == "operating") {
			var check_from = $("#check_from").val();
			var memo = $("#memoInput").val();
			if (check_from != "") {
				if (memo != "") {
					memo += "\r\n\r\nDeposit From: " + check_from;
				} else {
					memo = check_from;
				}
				//record the from in the memo 
				$("#memoInput").val(memo);
				$("#check_from").val("");
			}
		}
	}
	addForm(event, "check");
}
function saveCheckRequestModalBulk(event) {
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	var save_case_id = current_case_id;
	var reason = $("#reasonInput").val();
	var rush_request = "N";
	if (document.getElementById("rush_request").checked) {
		rush_request = "Y";
	}

	var account_id = "";
	if ($("#account_id").length > 0) {
		account_id = $("#account_id").val();
	}

	var checkrequests = document.getElementsByClassName("request_checkbox");
	var arrLength = checkrequests.length;

	for (var i = 0; i < arrLength; i++) {
		if (checkrequests[i].checked) {
			var formValues = "case_id=" + save_case_id;
			var arrVal = checkrequests[i].value.split("|");
			formValues += "&payable_to=" + arrVal[0] + "|" + encodeURIComponent(arrVal[1]);
			formValues += "&amount=" + arrVal[2];
			formValues += "&account_id=" + account_id;
			formValues += "&table_name=checkrequest";
			formValues += "&reason=" + encodeURIComponent(reason);
			formValues += "&rush_request=" + rush_request;
			formValues += "&request_date=" + $("#request_dateInput").val();
			formValues += "&needed_date=" + $("#needed_dateInput").val();

			//continue;

			var url = "api/checkrequest/add";

			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						console.log(data.error.text);
					} else {
						resetCurrentContent();

						$("#request_row_" + data.payable_to).css("background", "green");
						closeCheckRequestBulk();
					}
				}
			});
		}
	}
}
function closeCheckRequestBulk() {
	var checkrequests = document.getElementsByClassName("request_checkbox");
	var arrLength = checkrequests.length;
	var intCompleted = 0;

	for (var i = 0; i < arrLength; i++) {
		if (checkrequests[i].checked) {
			var arrVal = checkrequests[i].value.split("|");
			var row_id = "request_row_" + arrVal[0] + "_" + arrVal[1];
			var blnCompleted = ($("#" + row_id).css("background") == "green") || ($("#" + row_id).css("background-color") == "rgb(0, 128, 0)");
			if (blnCompleted) {
				intCompleted++;
			}
		}
	}

	if (intCompleted == arrLength) {
		setTimeout(function () {
			$("#myModal4").modal("toggle");

			if (document.location.hash.indexOf("#settlement/") == 0) {
				var arrID = document.location.hash.split("/");
				var case_id = arrID[1];
				var injury_id = arrID[2];
				window.Router.prototype.showSettlement(case_id, injury_id);
			}
			refreshOutstandingInvoices();
		}, 2500);
	}
}
function saveCheckRequestModal(event) {
	/*
	$blnValid = $("#checkrequest_form").parsley('validate');
	if (!$blnValid) {
		$(".parsley-error").css('border', '2px solid red');
		$(".parsley-error").css('z-index', '4205');
		$("#gifsave").hide();
		$("#modal_save_holder").show();
		$(".parsley-error-list").hide();
		return;
	}
	*/
	var amountInput = $("#amountInput").val();
	if (amountInput == 0 || amountInput == "") {
		$("#amountInput").css("border", "2px solid red");
		$("#amountInput").focus();
		return;
	}
	if ($("#request_dateInput").val() == "") {
		$("#request_dateInput").css("border", "2px solid red");
		$("#request_dateInput").focus();
		return;
	}
	//if ($("#check_recipient_id").val() == "") {
	if ($("#payable_to").val() == "" && $("#payable_to_span").html() == "") {
		$("#payable_to").css("border", "2px solid red");
		$("#payable_to").focus();
		return;
	}
	//}
	if ($("#other_payable_to").val() != "") {
		//require a recipient
		if ($("#check_recipient").val() == "") {
			$("#check_recipient").css("border", "2px solid red");
			$("#check_recipient").focus();
			return;
		}
	}
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();

	if ($("#check_recipient_id").val() == "") {
		if ($("#other_payable_to").val() != "") {
			//save the recipient as corporation
			formValues = "table_name=corporation&type=recipient";
			formValues += "&additional_partie=y";
			formValues += "&adhoc_fields=&case_id=" + current_case_id + "&full_name=" + encodeURIComponent($("#check_recipient").val());
			formValues += "&company_name=" + encodeURIComponent($("#other_payable_to").val());
			formValues += "&street=" + encodeURIComponent($("#street_payable_other_table").val());
			formValues += "&suite=" + encodeURIComponent($("#suiteInput").val());
			formValues += "&city=" + encodeURIComponent($("#city_payable_other_table").val());
			formValues += "&state=" + encodeURIComponent($("#administrative_area_level_1_payable_other_table").val());
			formValues += "&zip=" + encodeURIComponent($("#postal_code_payable_other_table").val());
			formValues += "&full_address=" + encodeURIComponent($("#full_addressInput").val());;

			var url = "api/corporation/add";

			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					blnSaving = false;
					if (data.error) {  // If there is an error, show the error messages
						console.log(data.error.text);
					} else {
						$("#corp_id").val(data.id);
						addForm(event, "checkrequest");
					}
				}
			});

			return;
		}
	} else {
		//the corp was a lookup
		$("#corp_id").val($("#check_recipient_id").val());
	}

	addForm(event, "checkrequest");
}

function voidCheck(event) {
	event.preventDefault();

	$("#void_check").hide();
	$(".check.save").hide();

	var url = 'api/check/void';
	var formValues = "id=" + $("#check_form #table_id").val();
	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				$("#myModalLabel").html("Check Voided&nbsp;&#10003;");
				$("#myModalLabel").css("color", "lime");

				setTimeout(function () {
					$(".modal-header .close").trigger("click");
				}, 2500);
			}
		}
	});
}
function saveActivityModal(event) {
	//alert("will save tomorrow");
	//return;
	var element = event.currentTarget;
	var arrUUID = element.id.split("_");
	//alert(element.id);
	//return;
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	$blnValid = $("#activity_form").parsley('validate');
	if (!$blnValid) {
		$(".parsley-error").css('border', '2px solid red');
		$(".parsley-error").css('z-index', '4205');
		$("#gifsave").hide();
		$("#modal_save_holder").show();
		$("#apply_notes").hide();
		return;
	}
	//addForm(event, "check");

	var self = this;
	var url = ""
	if (arrUUID[1] != "" && typeof arrUUID[1] != "undefined" && arrUUID[1] != "save") {
		url = "../api/activity/update_activity";
	} else {
		url = "../api/activity/insert_activity";
	}
	var this_case_id = current_case_id;
	blnKaseChangesDetected = true;

	var hours = $("#hoursInput").val();
	var activityVal = $("#activityInput").val();

	var formValues = "activity_uuid=" + arrUUID[1] + "&activity=" + activityVal + "&hours=" + hours + "&case_id=" + this_case_id;

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				resetCurrentContent();

				//$("#activity_hours_edit_" + arrClasses[3]).css("display", "none");
				$("#activity_hours_input_activator.activity_hours_uuid_" + arrUUID[1]).html(hours);
				$("#activity_input_activator.activity_uuid_" + arrUUID[1]).html(activityVal);
				var bg_color = $(".activity_data_row_" + arrUUID[1]).css('background');
				$(".activity_data_row_" + arrUUID[1]).css("background", "#32CD32");
				$("#myModal4").modal("toggle");
				setTimeout(function () {
					$(".activity_data_row_" + arrUUID[1]).css("background", bg_color);
				}, 2000);
			}
		}
	});
}
function saveNewActivityModal(event) {
	var element = event.currentTarget;
	var arrUUID = element.id.split("_");
	//alert(element.id);
	//return;
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	$blnValid = $("#activity_form").parsley('validate');
	if (!$blnValid) {
		$(".parsley-error").css('border', '2px solid red');
		$(".parsley-error").css('z-index', '4205');
		$("#gifsave").hide();
		$("#modal_save_holder").show();
		$("#apply_notes").hide();
		return;
	}
	//addForm(event, "check");

	var self = this;
	var url = "../api/activity/update_activity";
	var this_case_id = current_case_id;
	blnKaseChangesDetected = true;

	var hours = $("#hoursInput").val();
	var activityVal = $("#activityInput").val();

	var formValues = "activity_uuid=" + arrUUID[1] + "&activity=" + activityVal + "&hours=" + hours;

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				resetCurrentContent();

				//$("#activity_hours_edit_" + arrClasses[3]).css("display", "none");
				$("#activity_hours_input_activator.activity_hours_uuid_" + arrUUID[1]).html(hours);
				$("#activity_input_activator.activity_uuid_" + arrUUID[1]).html(activityVal);
				var bg_color = $(".activity_data_row_" + arrUUID[1]).css('background');
				$(".activity_data_row_" + arrUUID[1]).css("background", "#32CD32");
				$("#myModal4").modal("toggle");
				setTimeout(function () {
					$(".activity_data_row_" + arrUUID[1]).css("background", bg_color);
				}, 2000);
			}
		}
	});
}
var draft_id = 0;
var draft_timeout_id = false;
function saveDraft() {
	if (!blnSaveDraft) {
		return;
	}
	//clear out any other call
	clearTimeout(draft_timeout_id);
	if ($("#myModal4").css("display") == "none") {
		return;
	}
	var label_holder = $("#myModalLabel").html();
	var blnReturn = false
	if ($(".interoffice #specialty").length > 0) {
		if ($(".interoffice #specialty").val() == "") {
			blnReturn = true;
		}
	}
	var to_token_input = $("#message_toInput").val();

	if ($("#emailaddress_toInput").length > 0 && (to_token_input == "" || to_token_input.replaceAll(",", "") == "")) {
		to_token_input = $("#emailaddress_toInput").val();
	}

	/*
	if (to_token_input == "") {
		blnReturn = true;
	}
	
	if (blnReturn) {
		// && ($("#myModalLabel").html()=="New Message" || $("#myModalLabel").html()=="Edit Draft")
		if ($("#myModal4").css("display")=="block") {
			//we are still in edit mode
			draft_timeout_id = setTimeout(function() {
				saveDraft();
			}, 180000);
		} else {
			clearTimeout(draft_timeout_id);
		}
		return;
	}
	*/

	$("#myModalLabel").html("Saving draft...");
	var url = 'api/messages/add';
	if (draft_id > 0) {
		//draft has been saved before
		url = 'api/messages/update';
	}
	if ($("#emailaddress_toInput").length > 0) {
		var to_value = $("#message_toInput").val();
		var arrTo = to_value.split(",");
		var email_value = $("#emailaddress_toInput").val();
		var arrEmailTo = email_value.split(",");
		arrTo = arrTo.concat(arrEmailTo);
		$("#message_toInput").val(arrTo.join(","));
	}
	console.log("cc Length: ",$("#emailaddress_ccInput").length)
	if ($("#emailaddress_ccInput").length > 0) {
		var to_value = $("#message_ccInput").val();
		console.log('to_value :',to_value)
		var arrTo = to_value.split(",");
		console.log('arrTo :',arrTo)
		var email_value = $("#emailaddress_ccInput").val();
		console.log('email_value :',email_value)
		var arrEmailTo = email_value.split(",");
		console.log('arrEmailTo :',arrEmailTo)
		arrTo = arrTo.concat(arrEmailTo);
		console.log('arrTo :',arrTo)
		$("#message_ccInput").val(arrTo.join(","));
		console.log('arrTo.join :',arrTo.join(","))

	}
	if ($("#emailaddress_bccInput").length > 0) {
		var to_value = $("#message_bccInput").val();
		var arrTo = to_value.split(",");
		var email_value = $("#emailaddress_bccInput").val();
		var arrEmailTo = email_value.split(",");
		arrTo = arrTo.concat(arrEmailTo);
		$("#message_bccInput").val(arrTo.join(","));
	}

	var formValues = $("#interoffice_form").serialize();

	//attachments
	var arrAttach = [];
	var arrAttachments = $("#queue .filename").children();
	var arrayLength = arrAttachments.length;
	for (var i = 0; i < arrayLength; i++) {
		arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
	}

	//we might have selected existing case documents
	var case_documents = $(".message_document");
	var attach_document_id = "";
	if (case_documents.length > 0) {
		var arrayLength = case_documents.length;
		var arrAttachCaseDocument = [];
		for (var i = 0; i < arrayLength; i++) {
			var case_document = case_documents[i];
			if (case_document.checked) {
				arrAttachCaseDocument.push(case_document.value);
			}
		}
		attach_document_id = arrAttachCaseDocument.join("|");
		formValues += "&attach_document_id=" + attach_document_id;
	}
	formValues += "&attachments=" + arrAttach.join("|");
	formValues += "&deleted=D";
	if (draft_id > 0) {
		formValues += "&draft_id=" + draft_id;
	}
	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				draft_id = data.id;
				$("#table_id").val(draft_id);
				$("#myModalLabel").html("<span style='color:green'>Draft Saved</span>");
				$("#save_draft_holder").show();
				$("#save_draft").attr("checked", true);

				setTimeout(function () {
					//return to normal label the modal
					$("#myModalLabel").html(label_holder);
				}, 1500);

				//we are still in edit mode
				draft_timeout_id = setTimeout(function () {
					saveDraft();
				}, 180000);

			}
		}
	});
}
function sendMessage(event) {
	event.preventDefault();
	//no more drafts
	clearTimeout(draft_timeout_id);

	saveModal();
}
var blnSaving = false;
var savemodal_timeout_id = false;
function saveModal() {
	if (blnSaving) {
		return;
	}
	clearTimeout(savemodal_timeout_id);
	blnSaving = true;
	$("#gifsave").show();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").css("padding-right", "20px");

	//give it a little delay to let the above show itself
	savemodal_timeout_id = setTimeout(function () {
		saveModalActual();
	}, 300);
}
function saveModalActual() {
	var modal_type = $("#modal_type").val();

	if (current_case_id == -1) {
		if (document.location.hash.indexOf("#notes/") == 0 || document.location.hash.indexOf("#letters/") == 0 || document.location.hash.indexOf("#eams_forms/") == 0 || document.location.hash.indexOf("#payments/") == 0 || document.location.hash.indexOf("#billing/") == 0 || document.location.hash.indexOf("#activity/") == 0 || document.location.hash.indexOf("#tasks/") == 0 || document.location.hash.indexOf("#kalendar/") == 0 || document.location.hash.indexOf("#documents/") == 0 || document.location.hash.indexOf("#parties/") == 0 || document.location.hash.indexOf("#kase/") == 0) {
			var arrHash = document.location.hash.split("/");
			current_case_id = arrHash[arrHash.length - 1];
		}
	}
	blnKaseChangesDetected = true;

	if (blnActivityPane) {
		//alert(modal_type);
		//return;
	}
	if (modal_type == "rx") {
		var url = "api/rx/update";
		var formValues = $("#rx_form").serialize();
		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					resetCurrentContent();

					//all don
					$("#gifsave").hide();
					$("#myModal4").modal("toggle");

					if ($("#rx_listing").length > 0) {
						var person_id = $("#person_id").val();
						var applicant_rx = new RxCollection({ person_id: person_id });
						applicant_rx.fetch({
							success: function (data) {

								var rx_list_model = new Backbone.Model;
								rx_list_model.set("holder", "applicant_rx");
								rx_list_model.set("case_id", current_case_id);
								rx_list_model.set("person_id", person_id);
								$('#applicant_rx').html(new rx_listing_view({ collection: data, model: rx_list_model }).render().el);
								$('#applicant_rx').css("width", "100%");
								$(".tablesorter rx_listing").css("width", "100%");
								$(".rx_listing").css("border", "1px solid black");
							}
						});
					}
				}
			}
		});
	}
	if (modal_type == "calendar_filters") {
		var url = "api/calendarfilters/update";
		var formValues = $("#calendarfilters_form").serialize();
		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					resetCurrentContent();
					//all don
					$("#gifsave").hide();
					$("#myModal4").modal("toggle");

					if ($(".calendar_title").length > 0 || $("#occurence_listing").length > 0) {
						location.reload(true);
					}
				}
			}
		});
	}
	if (modal_type == "task_type") {
		//obsolete
		return;

		var url = 'api/task_type/add';
		var mymodel = this.model.toJSON();

		var task_type = $("#new_task_type").val();
		var formValues = "task_type=" + task_type;

		var title = $(".modal-title#myModalLabel").html();
		$(".modal-title#myModalLabel").html("Saving...");

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					resetCurrentContent();
					//all don
					$("#gifsave").hide();


					$(".modal-title#myModalLabel").html("<span style='color:lime'>Saved&nbsp;&#10003;</span>");

					//repop the select
					var cells = $(".task_type_cell");
					var arrLength = cells.length;
					var arrOptions = [];
					var option = '<option value="">Select from List</option>';
					arrOptions.push(option);
					for (var i = 0; i < arrLength; i++) {
						var id = cells[i].id.split("_")[3];
						if (cells[i].style.display != "none" || (!$("#task_category_value_" + id).hasClass("hidden") && document.getElementById("task_category_" + id).checked)) {
							var option = '<option value="' + cells[i].innerText.toLowerCase() + '">' + cells[i].innerText + '</option>';
							arrOptions.push(option);
						}
					}
					$("#type_of_taskInput").html(arrOptions.join(""));

					setTimeout(function () {
						$("#myModal4").modal("toggle");
					}, 2500);
				}
			}
		});
	}
	if (modal_type == "note_filters") {
		var url = "api/notefilters/update";
		var formValues = $("#notefilters_form").serialize();
		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					resetCurrentContent();

					//all don
					$("#gifsave").hide();
					$("#myModal4").modal("toggle");

					if ($("#note_listing").length > 0) {
						location.reload(true);
					}
				}
			}
		});
	}
	if (modal_type == "document_filters") {
		var url = "api/documentfilters/update";
		var formValues = $("#documentfilters_form").serialize();
		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					resetCurrentContent();

					//all don
					$("#gifsave").hide();
					$("#myModal4").modal("toggle");

					if ($("#document_listing").length > 0) {
						location.reload(true);
					}
				}
			}
		});
	}
	if (modal_type == "letter") {

		//reset the form from disable autofill
		var inputs = $("#letter_form input");
		var arrLength = inputs.length;
		for (var i = 0; i < arrLength; i++) {
			var inp = inputs[i];
			if (inp.id != "") {
				if (inp.id.indexOf("event_partie_") < 0) {
					if (typeof $("#letter_form #" + inp.id).attr("name") != "undefined") {
						if (inp.name != inp.id) {
							inp.name = inp.id;
						}
					}
				}
			}
		}

		var url = 'api/letter/create';
		var formValues = $("#letter_form").serialize();
		var arrFormValues = [];
		//we need to handle multiple dois
		var arrValues = formValues.split("&");
		var arrLength = arrValues.length;
		var arrDOIs = [];
		for (var i = 0; i < arrLength; i++) {
			var arrVar = arrValues[i].split("=");
			if (arrVar[0] == "doi") {
				arrDOIs.push(arrVar[1]);
			} else {
				//skip the doi
				arrFormValues.push(arrValues[i]);
			}
		}
		arrFormValues.push("doi=" + arrDOIs.join("|"));
		formValues = arrFormValues.join("&");

		var any_ids = $("#any_ids").val();
		if (any_ids == "") {
			var arrAny = [""];
		} else {
			var arrAny = any_ids.split("|");
		}

		//do we have an invoice?
		var hours_inputs = $(".invoice_hours");
		var arrLength = hours_inputs.length;

		if (arrLength > 0) {
			//make sure we have the destination
			var invoice_firm = document.getElementById("invoiced_firm_defense");
			var id = "carrier";
			if (invoice_firm.checked) {
				//make sure that the defense drop down is selected
				id = "defense";
			}
			var dropdown_value = $("#" + id).val();
			if (dropdown_value == "") {
				$("#" + id).css("border", "2px solid red");
				$("#gifsave").hide();
				$("#modal_save_holder").show();
				blnSaving = false;
				return;
			}
			var hourly_rate = $("#hourly_rate").html();
			var kinvoice_id = $("#kinvoice_id").val();
			var kinvoice_number = $("#kinvoice_number").val();
			var kinvoice_document_id = $("#kinvoice_document_id").val();
			var transfer_funds = "N";
			if (document.getElementById("transfer_funds").checked) {
				transfer_funds = "Y";
			}
			var kinvoice_type_invoice = "I";
			if (document.getElementById("kinvoice_type_pre").checked) {
				kinvoice_type_invoice = "P";
			}
			//could be a template
			if (kinvoice_number == "") {
				kinvoice_document_id = "";
			}
			var employee_id = $("#invoice_items_table #assigneeInput").val();
			var arrValues = [];
			//send everything
			for (var i = 0; i < arrLength; i++) {
				var element = hours_inputs[i];
				var arrID = element.id.split("_");
				var item_id = arrID[arrID.length - 1];

				//hours
				var fieldname = "hours_" + item_id;
				var value = element.value;
				arrValues.push("kinv_" + fieldname + "=" + value);

				//qty
				var fieldname = "qty_" + item_id;
				var value = document.getElementById(fieldname).value;
				arrValues.push("kinv_" + fieldname + "=" + value);

				//rate
				var fieldname = "rate_" + item_id;
				var value = document.getElementById(fieldname).value;
				arrValues.push("kinv_" + fieldname + "=" + value);

				//rateunit
				var fieldname = "rateunit_" + item_id;
				var value = document.getElementById(fieldname).value;
				arrValues.push("kinv_" + fieldname + "=" + value);

				//amount
				var fieldname = "amount_" + item_id;
				var value = document.getElementById(fieldname).value;
				arrValues.push("kinv_" + fieldname + "=" + value);
			}
			formValues += "&kinv_kinvoice_id=" + kinvoice_id + "&kinv_kinvoice_number=" + kinvoice_number;
			formValues += "&transfer_funds=" + transfer_funds + "&kinvoice_type=" + kinvoice_type_invoice;
			formValues += "&kinv_kinvoice_document_id=" + kinvoice_document_id + "&kinv_hourly_rate=" + hourly_rate + "&" + arrValues.join("&");
		}
		arrAny.forEach(function (any_id, index, array) {
			form_post_values = formValues + "&any_id=" + any_id;
			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: form_post_values,
				success: function (data) {
					blnSaving = false;
					if (data.error) {  // If there is an error, show the error messages

						saveFailed(data.error.text);
					} else { // If not
						resetCurrentContent();

						if (blnShowBilling) {
							if ($("#billing_holder").length > 0) {
								if ($("#billing_holder").html().trim() != "") {
									//update the acitivity id
									$("#billing_form #table_id").val(data.activity_id);
									//is there billing
									saveActivityBilling();
								}
							}
						}
						var case_id = $(".letter #case_id").val();
						var template_name = $(".letter #template_name").val();

						//store the search element if any
						var current_search;
						if ($("#letter_searchList").val() != "") {
							current_search = $("#letter_searchList").val();
						}

						//refresh letters list
						var hash = document.location.hash;

						if (hash.indexOf("#letters") == 0) {
							kase_letters = new KaseLetters([], { case_id: case_id });
							kase_letters.fetch({
								success: function (data) {
									var kase = kases.findWhere({ case_id: case_id });
									kase.set("no_uploads", true);
									$('#kase_content').html(new kase_letter_listing_view({ collection: word_templates, model: kase }).render().el);
									$("#kase_content").removeClass("glass_header_no_padding");

									setTimeout(function () {
										$("#letter_searchList").val(current_search);
										$("#letter_searchList").trigger("keyup");
									}, 500);
								}
							});
						}
						if (data.success.indexOf("invoices/")) {
							//invoice created, refresh indicators
							refreshOutstandingInvoices();
						}
						if (hash.indexOf("#payments/") == 0) {
							var account_id = "";
							if (document.getElementById("transfer_funds").checked) {
								account_id = $("#transfer_funds").val();
							}
							if (account_id != "") {
								var invoice_total = data.invoice_total;
								var invoice_number = data.invoice_number;
								invoice_number = invoice_number.replace('\\nDRAFT', '');
								//add a disbursement against the account
								var d = new Date();
								var check_number = "TRNSFR:" + moment(d).format("YYMMDDHHmmss");
								var check_date = moment(d).format("YYYY-MM-DD");
								var transaction_date = moment().format("YYYY-MM-DD");
								var account_type = "trust";

								var url = "api/check/add";
								var formValues = "table_name=check&table_id=-1&case_id=" + current_case_id + "&account_id=" + account_id;
								formValues += "&fee_id=&recipient=&kinvoice_id=&invoice_number=&ledger=OUT";
								formValues += "&transaction_date=" + transaction_date + "&payment=" + invoice_total;
								formValues += "&check_type=" + account_type.capitalize() + "+Withdrawal&method=transfer";
								formValues += "&amount_due=0&check_number=" + check_number;
								formValues += "&balance=0&check_date=" + check_date;
								formValues += "&memo=Invoice Transfer for " + invoice_number + "&send_document_id=";

								$.ajax({
									url: url,
									type: 'POST',
									dataType: "json",
									data: formValues,
									success: function (data) {
										if (data.error) {  // If there is an error, show the error messages
											saveFailed(data.error.text);
										} else {
											resetCurrentContent();
										}
									}
								});
							}
							window.Router.prototype.kaseChecks(current_case_id);
						}
						if (hash.indexOf("#accounts/") == 0) {
							var arrHash = hash.split("/");
							window.Router.prototype.listAccounts(arrHash[1]);
						}

						if (index == array.length - 1) {
							$('#myModal4').modal('toggle');
						}
					}
				}
			});
		});
	}
	if (modal_type == "eams") {
		//doi required
		var doi_id = $("#fields_holder #doi").val();
		if (doi_id == "") {
			$("#gifsave").hide();
			$("#modal_save_holder").show();

			$(".doi_cell").css("background", "red");
			blnSaving = false;
			return;
		}
		$(".eams #fields_holder").hide();
		$(".eams #loading").show();
		var url = 'api/pdf/create';
		var formValues = $("#eams_form").serialize();
		formValues += "&nopublish=y";
		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) { console.log(data);
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					resetCurrentContent();

					$("#gifsave").hide();
					if (blnShowBilling) {
						if ($("#billing_holder").length > 0) {
							if ($("#billing_holder").html().trim() != "") {
								//update the acitivity id
								if (data.activity_id != "") {
									$("#billing_form #table_id").val(data.activity_id);
									//is there billing
									saveActivityBilling();
								}
							}
						}
					}
					blnSaving = false;
					var case_id = $(".eams #case_id").val();
					var eams_form_name = $(".eams #eams_form_name").val();
					/*
					//add a note
					var url = 'api/notes/add';
					
					formValues = "table_name=notes";
					
					var note = "EAMS Form " + String.fromCharCode(34) + eams_form_name + String.fromCharCode(34) + " created by " + login_username + " on " + moment().format("MM/DD/YYYY h:mA");
					formValues += "&noteInput=" + note;
					formValues += "&case_id=" + case_id;
					formValues += "&subject=EAMS Form Filled";
					formValues += "&dateandtime=" + moment().format("MM/DD/YYYY h:mmA");
					
					//return;
					$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
						success:function (data) {
							blnSaving = false;
				if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							}
						}
					});
					*/
					$(".eams #fields_holder").show();
					$(".eams #loading").hide();
					var arrFile = data.file.split("/");
					var fullPath = data.file;
					var fileName = fullPath.split(/[\\/]/).pop(); 
					//arrFile[arrFile.length - 1]
					$("#eams_form_name_holder").html("&#8658;&nbsp;<a href='api/preview.php?case_id="+ case_id +"&file=" + encodeURIComponent(fileName) + "' title='Click to preview PDF in your browser.  You cannot make changes to the document' class='white_text' target='_blank'>Preview PDF</a>&nbsp;&#8656;&nbsp;&nbsp;&nbsp;&nbsp;&#8658;&nbsp;<a href='api/download.php?file=" + encodeURIComponent(data.file).replace("#", "%23") + "' title='Click to download document to your computer' class='white_text' download style='cursor:pointer'>Download PDF</a>&nbsp;&#8656;&nbsp;&nbsp;&nbsp;&nbsp;&#8658;&nbsp;<a href='" + data.file + "' title='Click to send document to your docusents' class='white_text sent_to_docusents' id='casefile_"+arrFile[3]+"_appfullpdf_"+ arrFile[2] + "' style='cursor:pointer'>Send to Docucents</a>&nbsp;&#8656;");

					$("#eams_form_name_holder").css("border", "2px solid white");
					$("#eams_form_name_holder").css("background", "green");
				}
			}
		});
	}
	if (modal_type == "message") {
		var blnProceed = true;
		var emailValidate = false;
		var ccEmailValidate = false;
		var bccEmailValidate = false;
		var duplicateToEmail = false;
		var mailformat = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

		//check on required fields
		var to_token_input = $("#message_toInput").val();
	

		var arrInputs = $("#message_holder_table input");
		var arrLength = arrInputs.length;
		for (var i = 0; i < arrLength; i++) {
			var element = arrInputs[i];
			// console.log(element.id + " => "+element.value)
			if (arrInputs[i].required && (arrInputs[i].value == "" || (!arrInputs[i].value.trim().length))) {
				blnProceed = false;
				arrInputs[i].style.border = "1px solid red";
				$("#myModalLabel").html("<span style='background:red; padding:1px'>Please fill out required fields</span>");
			}

			if(element.id === 'callback_dateInput' && element.value !== '')
			{
				var dateArr = element.value.split("/") 
				if(dateArr.length !== 3)
				{
					blnProceed = false;
					arrInputs[i].style.border = "1px solid red";
					$("#myModalLabel").html("<span style='background:red; padding:1px'>Please Enter valid follow up date.</span>");
				}else if(parseInt(dateArr[0]) > 12){
					blnProceed = false;
					arrInputs[i].style.border = "1px solid red";
					$("#myModalLabel").html("<span style='background:red; padding:1px'>Please Enter valid Month value in follow up date.</span>");
				}else if(parseInt(dateArr[1]) > 31){
					blnProceed = false;
					arrInputs[i].style.border = "1px solid red";
					$("#myModalLabel").html("<span style='background:red; padding:1px'>Please Enter valid Day value in follow up date.</span>");
				}
				var myDate = new Date(element.value);
				var today = new Date();
				if ( myDate < today ) { 
					blnProceed = false;
					arrInputs[i].style.border = "1px solid red";
					$("#myModalLabel").html("<span style='background:red; padding:1px'>Follow up date should be future date only.</span>");
				}
			}
		}

		if (!blnProceed) {
			$("#gifsave").hide();
			$("#modal_save_holder").show();
			blnSaving = false;
			return;
		}
		var label_holder = $("#myModalLabel").html();

		clearTimeout(draft_timeout_id);
		$("#myModalLabel").html("Sending...");
		var blnReturn = false;
		if ($(".interoffice #specialty").length > 0) {
			if ($(".interoffice #specialty").val() == "") {
				$(".interoffice #specialty").css("border", "2px red solid");
				blnReturn = true;
			}
		}

		if ($("#apply_tasks").length > 0) {
			if ($("#apply_tasks").prop("checked")) {
				if ($(".interoffice #callback_dateInput").val() == "") {
					$(".interoffice #callback_dateInput").css("border", "2px red solid");
					$(".interoffice #follow_up_holder").css("background", "red");
					blnReturn = true;
				}
				if ($("#task_assigneeInput").val() == "") {
					$(".interoffice #task_assigneeInput").css("border", "2px red solid");
					$(".interoffice #task_assignee_holder").css("background", "red");
					blnReturn = true;
				}
			}
		}

		var to_token_input = $("#message_toInput").val();

		if ($("#emailaddress_toInput").length > 0 && (to_token_input == "" || to_token_input.replaceAll(",", "") == "")) {
			to_token_input = $("#emailaddress_toInput").val();
		}

		if (to_token_input == "") {
			$("#message_to_td .token-input-list-facebook").css("border", "2px red solid");
			blnReturn = true;
		}

		if (blnReturn) {
			$("#gifsave").hide();
			$("#modal_save_holder").show();
			$("#myModalLabel").html("<span style='background:red; padding:1px'>Please fill out required fields</span>");
			blnSaving = false;
			return;
		}
		/*
		const toEmail = Array.from(document.querySelectorAll('#message_to_td>ul>li'));
		var toEmailArr =[];
		for (var i = 0; i < toEmail.length-1; i++) 
		{
			
			var content = toEmail[i].textContent.replaceAll('×','');
			if(!content.match(mailformat))
			{
				blnReturn = true;
				emailValidate = true;
				break;
			}
			console.log('Content : ',content)
			console.log('condition:',toEmailArr.indexOf(content) === -1)
			if(toEmailArr.indexOf(content) === -1){
				toEmailArr.push(content)
			}else{
				blnReturn = true;
				duplicateToEmail = true;
				break;
			}
			console.log('toEmailArr:',toEmailArr)

		}

		const ccEmail = Array.from(document.querySelectorAll('#message_cc_td>ul>li'));
		for (var i = 0; i < ccEmail.length-1; i++) 
		{
			var content = ccEmail[i].textContent.replaceAll('×','');
			if(!content.match(mailformat))
			{
				blnReturn = true;
				ccEmailValidate = true;
				break;
			}
			console.log('Content : ',content)
			console.log('condition:',toEmailArr.indexOf(content) === -1)
			if(toEmailArr.indexOf(content) === -1){
				toEmailArr.push(content)
			}else{
				blnReturn = true;
				duplicateToEmail = true;
				break;
			}
			console.log('toEmailArr:',toEmailArr)
		}

		const bccEmail = Array.from(document.querySelectorAll('#message_cc_td3>ul>li'));
		for (var i = 0; i < bccEmail.length-1; i++) 
		{
			var content = bccEmail[i].textContent.replaceAll('×','');
			if(!content.match(mailformat))
			{
				blnReturn = true;
				bccEmailValidate = true;
				break;
			}
			console.log('Content : ',content)
			console.log('condition:',toEmailArr.indexOf(content) === -1)
			if(toEmailArr.indexOf(content) === -1){
				toEmailArr.push(content)
			}else{
				blnReturn = true;
				duplicateToEmail = true;
				break;
			}
			console.log('toEmailArr:',toEmailArr)
		}

		if (blnReturn) {
			$("#gifsave").hide();
			$("#modal_save_holder").show();
			if(emailValidate)			{
				$("#myModalLabel").html("<span style='background:red; padding:1px'>Please Enter valid email.</span>");
			}else if(duplicateToEmail)			{
				$("#myModalLabel").html("<span style='background:red; padding:1px'>Don't enter duplicate email.</span>");
			}else if(ccEmailValidate)			{
				$("#myModalLabel").html("<span style='background:red; padding:1px'>Please Enter valid email in CC.</span>");
			}else if(bccEmailValidate)			{
				$("#myModalLabel").html("<span style='background:red; padding:1px'>Please Enter valid email in BCC.</span>");
			}else{
				$("#myModalLabel").html("<span style='background:red; padding:1px'>Please fill out required fields</span>");
			}
			blnSaving = false;
			return;
		}
		*/
	
		if ($("#emailaddress_toInput").length > 0) {
			var to_value = $("#message_toInput").val();
			var arrTo = to_value.split(",");
			var email_value = $("#emailaddress_toInput").val();
			var arrEmailTo = email_value.split(",");
			arrTo = arrTo.concat(arrEmailTo);
			//console.log("=====",arrTo)
			$("#message_toInput").val(arrTo.join(","));
		}
		if ($("#emailaddress_ccInput").length > 0) {
			var to_value = $("#message_ccInput").val();
			var arrTo = to_value.split(",");
			var email_value = $("#emailaddress_ccInput").val();
			var arrEmailTo = email_value.split(",");
			arrTo = arrTo.concat(arrEmailTo);
			$("#message_ccInput").val(arrTo.join(","));
		}
		if ($("#emailaddress_bccInput").length > 0) {
			var to_value = $("#message_bccInput").val();
			var arrTo = to_value.split(",");
			var email_value = $("#emailaddress_bccInput").val();
			var arrEmailTo = email_value.split(",");
			arrTo = arrTo.concat(arrEmailTo);
			$("#message_bccInput").val(arrTo.join(","));
		}

		//maybe we have been saving drafts
		var url = 'api/messages/add';
		//console.log("blnSaveDraft:",blnSaveDraft);
		//clear the draft if any
	//	window.location.hash=="#thread/inbox";
		//console.log("here it",window.location.hash);
		

		if (blnSaveDraft) {
			//console.log("in");
			/*
			if (draft_id > 0) {
				$("#table_id").val(draft_id);
			} else {
			*/
			if ($("#table_id").val() != "" && $("#table_id").val() != "-1") {
				url = 'api/messages/update';
			}
			if(window.location.hash.includes("#injury/")){
				url = 'api/messages/add';
			}
			//}
		}

		//signature
		var formValues = $("#interoffice_form").serialize();
		if ($("#signatureInput").val() != "") {
			formValues += "&signature=" + encodeURIComponent($("#signatureInput").val());
		}
		if (blnSaveDraft) {
			if (draft_id > 0) {
				formValues += "&deleted=N";
			}
		}

		if ($("#kinvoice_id").val() != "") {
			//we are sending an invoice
			formValues += "&attachments=D:/uploads/" + customer_id + "/invoices/kase_bill__" + $("#kinvoice_path").val() + ".pdf";
			formValues += "&attach_document_id=" + $("#kinvoice_document_id").val();
			formValues += "&kinvoice_id=" + $("#kinvoice_id").val();
		} else {
			//attachments
			var arrAttach = [];
			var arrAttachments = $("#queue .filename").children();
			var arrayLength = arrAttachments.length;
			for (var i = 0; i < arrayLength; i++) {
				var the_attachment = arrAttachments[i].href.replace(hrefHost, "");
				the_attachment = the_attachment.replace("https:///", "");
				var arrFilePath = the_attachment.split("/");
				the_attachment = arrFilePath[arrFilePath.length - 1];
				arrAttach[i] = encodeURIComponent(the_attachment);
			}

			//we might have selected existing case documents
			var case_documents = $(".message_document");
			var attach_document_id = "";
			if (case_documents.length > 0) {
				var arrayLength = case_documents.length;
				var arrAttachCaseDocument = [];
				for (var i = 0; i < arrayLength; i++) {
					var case_document = case_documents[i];
					if (case_document.checked) {
						arrAttachCaseDocument.push(case_document.value);
					}
				}
				attach_document_id = arrAttachCaseDocument.join("|");
				formValues += "&attach_document_id=" + attach_document_id;
			}
			formValues += "&attachments=" + arrAttach.join("|");
		}
		if (document.getElementById("apply_notes").checked) {

			var notes_url = 'api/notes/add';
			notesformValues = formValues.replace("table_name=message", "table_name=notes");
			notesformValues = notesformValues.replace("messageInput=", "noteInput=");
			notesformValues = notesformValues.replace("case_fileInput=", "case_id=");
			//if it's an invoice, who did we invoice?
			if ($("#kinvoice_id").val() != "") {
				notesformValues += "&partie_id=" + $("#kinvoice_invoiced_id").val();
				notesformValues += "&table_attribute=" + $("#kinvoice_invoiced_type").val();
			}
			$.ajax({
				url: notes_url,
				type: 'POST',
				dataType: "json",
				data: notesformValues,
				success: function (data) {
					blnSaving = false;
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						resetCurrentContent();

						//maybe we are in notes
						if ($("#kase_content #note_listing").length > 0) {
							notes = new NoteCollection([], { case_id: current_case_id });
							$("#kase_content").html(loading_image);
							notes.fetch({
								success: function (data) {
									var note_list_model = new Backbone.Model;
									note_list_model.set("display", "full");
									note_list_model.set("partie_type", "note");
									note_list_model.set("partie_id", -1);
									note_list_model.set("case_id", current_case_id);
									$('#kase_content').html(new note_listing_view({ collection: data, model: note_list_model }).render().el);
								}
							});
						}
					}
				}
			});
		}

		if (document.getElementById("apply_tasks").checked) {

			var tasks_url = 'api/task/add';
			var tasksformValues = formValues.replace("table_name=message", "table_name=tasks");

			tasksformValues = tasksformValues.replace("messageInput=", "task_descriptionInput=");
			tasksformValues = tasksformValues.replace("subjectInput=", "task_titleInput=");
			if ($("#task_assigneeInput").length == 0) {
				tasksformValues = tasksformValues.replace("message_toInput=", "assigneeInput=");
			} else {
				tasksformValues = tasksformValues.replace("message_toInput=", "ignore_meInput=");
				tasksformValues = tasksformValues.replace("task_assigneeInput=", "assigneeInput=");
			}
			tasksformValues = tasksformValues.replace("callback_dateInput=", "task_dateandtimeInput=");
			tasksformValues = tasksformValues.replace("attach_document_id=", "send_document_id=");
			tasksformValues = tasksformValues.replace("case_fileInput=", "case_id=");

			//tasksformValues += "&task_fromInput=" + login_username;

			$.ajax({
				url: tasks_url,
				type: 'POST',
				dataType: "json",
				data: tasksformValues,
				success: function (data) {
					blnSaving = false;
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					}
					resetCurrentContent();
				}
			});
		}

		if (document.getElementById("select_all_clients").checked) {
			formValues += "&select_all_clients=Y";
		}
		/*
		if (document.getElementById("save_draft").checked) {
			formValues += "&deleted=D";
		} else {
			formValues += "&deleted=N";
		}
		*/
		/*
		if ($("#billing_time_dropdownInput").length > 0) {
			var billing_time = $("#billing_time_dropdownInput").val();
			if (!isNaN(billing_time)) {
				formValues += "&billing_time=" + billing_time;
			}
		}
		*/
		console.log('form data before api call',formValues,'Current value of URL',url);
		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				console.log('on success',data);
				
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					resetCurrentContent();

					$("#myModalLabel").html("Message Sent&nbsp;<span style='color:white;background:green'>&#10003;</span>");
					setTimeout(function () {
						//hide the modal
						$('#myModal4').modal('toggle');
					}, 1500);
					emptyBuffer();

					if ($("#thread_listing").length > 0) {
						if ($("#thread_title_holder").html() == "Outbox") {
							window.Router.prototype.listThreadOutbox();
						} else {
							if ($("#show_threads").css("display") != "none") {
								window.Router.prototype.listThreadInbox();
							}
						}
					}
				}

				//draft?
				if ($("#message_listing").length > 0) {
					if ($("#message_listing_title").html() == "Drafts") {
						var message_id = draft_id;
						$(".message_row_" + message_id).css("background", "lime");

						setTimeout(function () {
							$(".message_row_" + message_id).fadeOut();
						}, 2500);
					}
				}

				//clear the draft if any
				draft_id = 0;

				getOutbox();
			}
		}
		);
	}
	if (modal_type == "eams_form") {
		var url = 'api/forms/add';
		var theid = $(".eams_form #table_id").val();
		if (theid > 0) {
			url = 'api/forms/update';
		}
		var formValues = $("#eams_form_form").serialize();

		//attachments
		var arrAttach = [];
		var arrAttachments = $("#queue .filename").children();
		var arrayLength = arrAttachments.length;
		for (var i = 0; i < arrayLength; i++) {
			arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
		}
		formValues += "&attachments=" + arrAttach.join("|");

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					resetCurrentContent();
					//hide the modal
					$('#myModal4').modal('toggle');
					eamss = new EAMSFormCollection();
					eamss.fetch({
						success: function (data) {
							$(document).attr('title', "Manage EAMS Forms :: iKase");
							$('#content').html(new eams_form_listing({ collection: data }).render().el);
							$("#content").removeClass("glass_header_no_padding");
							hideEditRow();
						}
					});
				}
			}
		});
	}
	if (modal_type == "exam") {
		var url = 'api/exams/add';
		if ($("#exam_form #table_id").val() != "" && $("#exam_form #table_id").val() != "-1") {
			url = 'api/exams/update';
		}
		var formValues = $("#exam_form").serialize();

		if ($("#queue .filename").length > 0) {
			//attachments
			var arrAttach = [];
			var arrAttachments = $("#queue .filename").children();
			var arrayLength = arrAttachments.length;
			for (var i = 0; i < arrayLength; i++) {
				arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
			}

			formValues += "&attachments=" + encodeURIComponent(arrAttach.join("|"));
		}
		//attachments
		//var arrAttach = [];
		//var arrAttachments = $("#queue .filename").children();
		//var arrayLength = arrAttachments.length;
		//for (var i = 0; i < arrayLength; i++) {
		//arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
		//}
		//formValues += "&attachments=" + arrAttach.join("|");

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					resetCurrentContent();
					//hide the modal
					$('#myModal4').modal('toggle');

					//refresh the screen
					if (current_case_id != -1) {
						exams = new ExamCollection({ case_id: current_case_id });
						exams.fetch({
							success: function (data) {
								var exam_info = new Backbone.Model;
								exam_info.set("case_id", current_case_id);
								exam_info.set("holder", "kase_content");
								$('#kase_content').html(new exam_listing({ collection: data, model: exam_info }).render().el);
								$("#kase_content").removeClass("glass_header_no_padding");
							}
						});
					}
				}
			}
		}
		);
	}
	if (modal_type == "Activity") {
		/*
		if (blnActivityPane && $("#activity_list_outer_div").length > 0) {
			$("#activity_list_outer_div").html(loading_image);
		}
		//var element = event.currentTarget;
		//var arrUUID = element.id.split("_"); 
		//$blnValid = $("#activity_form").parsley('validate');
		if (!$blnValid) {
			$(".parsley-error").css('border', '2px solid red');
			$(".parsley-error").css('z-index', '4205');
			$("#gifsave").hide();
			$("#modal_save_holder").show();
			$("#apply_notes").hide();
			return;
		}
		//addForm(event, "check");
		
		var self = this;
		
		var this_case_id = current_case_id;
		
		var billing_date = $("#billing_dateInput").val();
		var hours = $("#durationInput").val();
		//convert to hours
		hours = hours / 60;
		var status = $("#statusInput").val();
		var billing_rate = $("#billing_rateInput").val();
		var category = $("#activity_codeInput").val();
		var timekeeper = $("#timekeeperInput").val();
		var activityVal = $("#descriptionInput").val();
		var table_id = $("#table_id").val();
		var url = "../api/activity/insert_activity";
		if (table_id > 0) {
			url = "../api/activity/update_activity";
		}
		var formValues = "activity=" + activityVal + "&hours=" + hours + "&case_id=" + this_case_id + "&billing_date=" + billing_date + "&table_id=" + table_id + "&status=" + status;
			formValues += "&billing_rate=" + billing_rate + "&category=" + category + "&timekeeper=" + timekeeper;
		
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
					//update the display
					window.Router.prototype.kaseActivity(current_case_id);
				}
			}
		});
		*/
		//saveActivityBilling();
	}
	if (modal_type == "task") {
		//we need a subject or title at least
		var blnSave = true;
		//reset borders
		var regular_border = "1px solid rgb(169, 169, 169)";
		$("#task_form #task_titleInput").css("border", regular_border);
		$(".cleditorMain").css("border", regular_border);
		$("#task_form .token-input-list-facebook")[1].style.border = regular_border;

		if ($("#task_form #task_titleInput").val() == "" && $("#task_form #task_descriptionInput").val() == "") {
			blnSave = false;
		}
		if (!blnSave) {
			alert("Please enter a Subject or a Description for a new Task");
			$("#task_form #task_titleInput").css("border", "2px solid red");
			$(".cleditorMain").css("border", "2px solid red");
			blnSaving = false;
		}
		if (blnSave) {
			if ($("#task_form #assigneeInput").val() == "") {
				alert("Please select an employee to assign to this Task");
				$("#task_form .token-input-list-facebook")[1].style.border = "2px solid red";
				blnSaving = false;
				blnSave = false;
			}
		}
		if (!blnSave) {
			$("#gifsave").hide();
			$("#modal_save_holder").show();
			return;
		}
		if (blnTaskPane && $("#task_list_outer_div").length > 0) {
			$("#task_list_outer_div").html(loading_image);
		}
		var id = $(".task #table_id").val();
		var url = 'api/task/add';
		if (id > 0) {
			url = 'api/task/update';
		}

		var formValues = $("#task_form").serialize();

		//attachments
		var arrAttach = [];
		var arrAttachments = $("#queue .filename").children();
		var arrayLength = arrAttachments.length;
		for (var i = 0; i < arrayLength; i++) {
			arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
		}
		formValues += "&attachments=" + arrAttach.join("|");


		if (document.getElementById("doi_id") != null) {
			if ($("#doi_id").val() != "") {
				if (formValues.indexOf("injury_id=") < 0) {
					formValues += "&injury_id=" + $("#doi_id").val();
				}
			}
		}

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;

				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					resetCurrentContent();
					//hide the modal
					emptyBuffer();
					
					if (id < 0) {
						if (blnShowBilling) {
							if ($("#billing_holder").length > 0) {
								if ($("#billing_holder").html().trim() != "") {
									//update the acitivity id
									$("#billing_form #table_id").val(data.activity_id);
									//is there billing
									saveActivityBilling();
								}
							}
						}
					}

					console.log($(document).attr('title'));

					if($(document).attr('title') == "Task Inbox - All Employees 03/25/2022 :: iKase"){
					//if ($(document).attr('title') == "Task Inbox :: iKase") {
						//fetch all tasks
						console.log("Task  inbox");
						var tasks = new TaskInboxCollection();
						tasks.fetch({
							success: function (data) {
								var task_listing_info = new Backbone.Model;
								task_listing_info.set("title", "Task Inbox");
								task_listing_info.set("receive_label", "Due Date");
								task_listing_info.set("holder", "content");
								$('#content').html(new task_listing_pane({ collection: data, model: task_listing_info }).render().el);
							}
						});
					}
					if ($(document).attr('title') == "Task Outbox :: iKase") {
						//fetch all tasks
						var tasks = new TaskOutboxCollection();
						tasks.fetch({
							success: function (data) {
								var task_listing_info = new Backbone.Model;
								task_listing_info.set("title", "Task Outbox");
								task_listing_info.set("receive_label", "Due Date");
								task_listing_info.set("holder", "content");
								$('#content').html(new task_listing_pane({ collection: data, model: task_listing_info }).render().el);
							}
						});
					}

					if ($(document).attr('title') == "Overdue Tasks :: iKase") {
						window.Router.prototype.listTaskOverdue();
					}
					if ($(document).attr('title') == "Overdue Firm Tasks :: iKase") {
						window.Router.prototype.listTaskFirmOverdue();
					}
					//kase task
					if ($(document).attr('title').indexOf("Tasks for") == 0) {
						
						window.Router.prototype.listCaseTasks(current_case_id);
						//fetch all tasks
						/*
						tasks = new TaskInboxCollection({case_id: current_case_id});
						tasks.fetch({
							success: function (data) {
								var task_listing_info = new Backbone.Model;
								task_listing_info.set("title", "Kase Tasks");
								task_listing_info.set("receive_label", "Due Date");
								task_listing_info.set("holder", "kase_content");
								$('#kase_content').html(new task_listing_pane({collection: data, model: task_listing_info}).render().el);
								$("#kase_content").removeClass("glass_header_no_padding");
							}
						});
						*/
					}

					//might be in note edit mode
					if ($("#callback_dateSpan").length > 0) {
						var task_date = $("#task_dateandtimeInput").val().split(" ")[0];
						$("#callback_dateSpan").html(task_date);
					}
					if ($('#myModal4').css("display") != "none") {
						$('#myModal4').modal('toggle');
					}
				}
			}
		});
	}
	if (modal_type == "note") {
		if (current_case_id == -1) {
			if (document.location.hash.indexOf("#notes/") == 0) {
				var arrHash = document.location.hash.split("/");
				current_case_id = arrHash[arrHash.length - 1];
			}
		}

		$(".save").hide();
		$("#note_gifsave").show();

		var id = $(".new_note #table_id").val();
		var url = 'api/notes/add';
		var formValues = $("#new_note_form").serialize();

		if (document.getElementById("doi_id") != null) {
			if ($("#doi_id").val() != "") {
				if (formValues.indexOf("injury_id=") < 0) {
					formValues += "&injury_id=" + $("#doi_id").val();
				}
			}
		}
		if (id > 0) {
			url = 'api/notes/update';
		}
		if (blnNotePane) {
			$("#note_list_outer_div").html(loading_image);
		}
		//attachments
		var arrAttach = [];
		var arrAttachments = $("#queue .filename").children();
		var arrayLength = arrAttachments.length;
		for (var i = 0; i < arrayLength; i++) {
			arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
		}
		//we might have selected existing case documents
		var case_documents = $(".message_document");
		var attach_document_id = "";
		if (case_documents.length > 0) {
			var arrayLength = case_documents.length;
			var arrAttachCaseDocument = [];
			for (var i = 0; i < arrayLength; i++) {
				var case_document = case_documents[i];
				if (case_document.checked) {
					arrAttachCaseDocument.push(case_document.value);
				}
			}
			attach_document_id = arrAttachCaseDocument.join("|");
			formValues += "&attach_document_id=" + attach_document_id;
		}
		formValues += "&attachments=" + encodeURIComponent(arrAttach.join("|"));

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				blnSaving = false;
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					resetCurrentContent();
					//hide the grid containing the element
					if ($('#myModal4').css("display") != "none") {
						$('#myModal4').modal('toggle');
					}
					if (blnShowBilling) {
						if ($("#billing_holder").length > 0) {
							if ($("#billing_holder").html().trim() != "") {
								//update the activity id
								$("#billing_form #table_id").val(data.activity_id);
								//is there billing
								saveActivityBilling();
							}
						}
					}

					var notes_id = data.id;
					var task_id = $("#new_note_form #task_id").val();
					//if (id < 0) {
					//do we have a task?
					//	if (blnNotePane) {
					if (task_id == -1) {
						var task_date = $("#callback_dateInput").val();
						console.log('task_date :',task_date)
						if (task_date != "") {
							if ($(".new_note #assigneeInput").val() == "") {
								$(".token-input-list-event").css("border", "2px solid red");
								return;
							}
							saveNoteTask(formValues, notes_id);
						}
					}
					//	}
					//}
					if ($("#note_list_outer_div").length > 0) {
						var kase = kases.findWhere({ case_id: current_case_id });
						var notes = new NoteCollection([], { case_id: current_case_id });
						notes.fetch({
							success: function (data) {
								var note_list_model = new Backbone.Model;
								note_list_model.set("display", "full");
								note_list_model.set("partie_type", "note");
								note_list_model.set("partie_id", -1);
								note_list_model.set("case_id", current_case_id);
								note_list_model.set("homepage", false);

								$('#kase_content').html(new note_listing_pane({ collection: data, model: note_list_model, kase: kase }).render().el);

								$("#kase_content").removeClass("glass_header_no_padding");
								hideEditRow();
							}
						});
					}
					var hash = document.location.hash;
					var arrHash = hash.split("/");

					if (hash.indexOf("#settlement/") > -1) {
						var settlement_notes = new NotesByType([], { type: 'settlement', case_id: current_case_id });
						settlement_notes.fetch({
							success: function (data) {
								var note_list_model = new Backbone.Model;
								note_list_model.set("display", "sub");
								note_list_model.set("case_id", current_case_id);
								note_list_model.set("embedded", true);
								note_list_model.set("partie_type", "settlement");
								note_list_model.set("party_array_type", "settlement");
								$('#settlement_notes_holder').html(new note_listing_view({ collection: data, model: note_list_model }).render().el);
							}
						});
						return;
					}
					// && (document.location.hash.indexOf("#kase/")<0 && document.location.hash.indexOf("#parties/") < 0)
					if ($("#note_listing").length > 0) {
						if ($("#partie_notes").length == 0) {
							$("#gifsave").hide();
							//refresh quick notes

							if (hash.indexOf("#injury") == 0) {
								var injury_id = $("#injury_id").val();
								notes = new InjuryNotesByType([], { type: "injury", injury_id: injury_id });
								display_mode = "homepage";
								$("#gifsave").hide();
							} else if (hash.indexOf("#applicant") == 0) {
								notes = new NotesByType([], { type: "applicant", case_id: current_case_id });
								display_mode = "full";
							} else if (hash.indexOf("#parties") == 0 && arrHash.length == 4) {
								var type = arrHash[arrHash.length - 1];
								notes = new NotesByType([], { type: type, case_id: current_case_id });
								display_mode = "full";
							} else if (hash.indexOf("#kase/") == 0) {
								//dash kase notes
								var type = arrHash[arrHash.length - 1];
								notes = new NoteCollectionDash([], { case_id: current_case_id });
								display_mode = "full";
							} else {
								notes = new NoteCollection([], { case_id: current_case_id });
								display_mode = "full";
							}
							notes.fetch({
								success: function (data) {
									var note_list_model = new Backbone.Model;
									note_list_model.set("display", display_mode);
									note_list_model.set("partie_type", "note");
									note_list_model.set("partie_id", -1);
									note_list_model.set("case_id", current_case_id);
									//$("#note_listing").parent().parent().parent()
									//$('#kase_content').html(new note_listing_view({collection: data, model: note_list_model}).render().el);
									if (document.location.hash.indexOf("#kases/") == 0 || document.location.hash.indexOf("#kase/") == 0) {
										//saving from dash
										note_list_model.set("display", "homepage");
										var content_id = "kase_notes";
										$('#' + content_id).html(new note_listing_view({ collection: data, model: note_list_model }).render().el);
									} else {
										//saving from note listing
										if (blnNotePane) {
											var content_id = $("#note_listing").parent().parent().parent().parent()[0].id;
											$('#' + content_id).html(new note_listing_pane({ collection: data, model: note_list_model }).render().el);
										} else {
											var content_id = $("#note_listing").parent().parent().parent()[0].id;
											$('#' + content_id).html(new note_listing_view({ collection: data, model: note_list_model }).render().el);
										}
									}
								}
							});
						} else {
							$("#gifsave").hide();
							var hash = document.location.hash;
							var partie_type = document.getElementById("partie_notes").classList[0];
							var partie_notes = new NotesByType([], { type: partie_type, case_id: current_case_id });
							partie_notes.fetch({
								success: function (data) {
									var note_list_model = new Backbone.Model;
									note_list_model.set("display", "sub");
									note_list_model.set("partie_id", $("#table_id").val());
									note_list_model.set("partie_type", partie_type);
									note_list_model.set("case_id", current_case_id);
									$('#partie_notes').html(new note_listing_view({ collection: data, model: note_list_model }).render().el);
								}
							});
						}
					}
					if ($("#noteSpan").length > 0) {
						//refresh quick notes
						var quick_notes = new NotesByType([], { type: "quick", case_id: current_case_id });
						quick_notes.fetch({
							success: function (data) {
								var notes = data.toJSON();
								var arrNotes = [];
								_.each(notes, function (quicknote) {
									arrNotes[arrNotes.length] = "<div class='quicknote_row' id='quicknote_" + quicknote.notes_id + "'><div style='font-size:0.8em'>" + moment(quicknote.dateandtime).format("MM/DD/YY h:mm a") + " by <span style='font-style:italic;'" + quicknote.entered_by + "</span></div><div style='font-size:1.5em'>" + quicknote.note + "</div></div>";
								});
								quick_note = arrNotes.join("\r\n");
								$("#noteSpan").html(quick_note);
							}
						});
					}
					//partie list
					if ($("#partie_notes").length > 0) {
						var partie_type = $("#partie_notes").attr("class");
						var partie_notes = new NotesByType([], { type: partie_type, case_id: current_case_id });
						partie_notes.fetch({
							success: function (data) {
								var note_list_model = new Backbone.Model;
								note_list_model.set("display", "sub");
								note_list_model.set("partie_type", partie_type);
								note_list_model.set("partie_id", $("." + partie_type + " #table_id").val());
								note_list_model.set("case_id", current_case_id);
								$('.' + partie_type + '#partie_notes').html(new note_listing_view({ collection: data, model: note_list_model }).render().el);
								$('.' + partie_type + '#partie_notes').fadeIn();
							}
						});
					}

					//dashboard
					if (blnUseRightPane) {
						if (document.location.hash.indexOf("#kases/") == 0 || document.location.hash.indexOf("#kase/") == 0 || document.location.hash.indexOf("#parties/") == 0) {
							//show success
							$("#new_note_table_holder").html("<span style='color:white; font-size:1.2em'>Note was saved successfully &#10003;</span>");
							setTimeout(function () {
								$("#dashboard_right_pane").fadeOut(function () {
									$("#dashboard_right_pane").html("");
									if ($("#bodyparts_warning").html() != "") {
										$("#bodyparts_warning").fadeIn();
									}
								});
							}, 2500);
						}
					}
				}
			}
		});
	}
	/*
	$("#modal_save_holder").show();
	$("#gifsave").hide();
	$("#modal_billing_holder").hide();
	*/
	blnSaving = false;
}
function saveActivityBillingFull() {
	if (blnActivityPane && $("#activity_list_outer_div").length > 0) {
		$("#activity_list_outer_div").html(loading_image);
	}
	if (typeof current_case_id == "undefined") {
		current_case_id = -1
	}
	if (current_case_id > -1) {
		var this_case_id = current_case_id;
	} else {
		var this_case_id = $(".activity_bill #case_id").val();
		current_case_id = this_case_id;
	}
	if (current_case_id == -1) {
		//catchup
		if (document.location.hash.indexOf("#activity/") == 0) {
			current_case_id = document.location.hash.split("/")[1];
		}
	}
	blnKaseChangesDetected = true;

	var billing_date = $("#billing_form #billing_dateInput").val();
	if ($("#statusInput").val() == "Hourly") {
		var hours = $("#billing_form #durationInput").val();
	} else {
		var hours = $("#billing_form #unitsInput").val();
	}
	var status = $("#billing_form #statusInput").val();

	if (status == "Cost") {
		hours = 0;

		billing_amount = $("#unitsInput").val();
		billing_rate = $("#billing_rateInput").val();
		billing_unit = $("#unit_nameInput").val();
	}
	/*
	var billing_rate = "";
	if ($("#statusInput").val()=="Hourly") {
		billing_rate = $("#billing_form #billing_rateInput").val();
		billing_rate += "|" + $("#billing_form #unit_nameInput").val();
	}
	*/

	var category = $("#billing_form #activity_codeInput").val();
	var timekeeper = $("#billing_form #timekeeperInput").val();
	console.log(timekeeper);
	
	var activityVal = $("#billing_form #descriptionInput").val();
	var table_id = $("#billing_form #table_id").val();
	var url = "../api/activity/insert_activity";
	if (table_id > 0) {
		url = "../api/activity/update_activity";
	}
	var formValues = "activity=" + encodeURIComponent(activityVal) + "&hours=" + hours + "&case_id=" + this_case_id + "&billing_date=" + billing_date + "&table_id=" + table_id + "&status=" + status;
	formValues += "&category=" + category + "&timekeeper=" + timekeeper;
	if (status == "Cost") {
		formValues += "&billing_rate=" + billing_rate;
		formValues += "&billing_amount=" + billing_amount;
		formValues += "&billing_rate=" + billing_rate;
		formValues += "&billing_unit=" + billing_unit;
	}

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				resetCurrentContent();
				if ($("#myModal4").hasClass("in")) {
					$("#myModal4").modal("toggle");
				}
				if (blnActivityPane && $("#activity_list_outer_div").length > 0) {
					//update the display
					if (document.location.hash.indexOf("#activity/") == 0) {
						window.Router.prototype.kaseActivity(current_case_id);
					}
					if (document.location.hash.indexOf("#billing/") == 0) {
						window.Router.prototype.kaseBilling(current_case_id);
					}
				}
			}
		}
	});
}
function saveActivityBilling() {
	var table_id = $("#billing_form #table_id").val();
	if (table_id == "" || table_id == "-1") {
		//we need the id to update activity
		return;
	}
	if (blnActivityPane && $("#activity_list_outer_div").length > 0) {
		$("#activity_list_outer_div").html(loading_image);
	}
	if (typeof current_case_id == "undefined") {
		current_case_id = -1
	}
	if (current_case_id > -1) {
		var this_case_id = current_case_id;
	} else {
		var this_case_id = $(".activity_bill #case_id").val();
	}
	blnKaseChangesDetected = true;
	var hours = $("#billing_form #durationInput").val();
	var units = "";
	var billing_rate = "";

	//is is a cost
	var activity_type = $("#statusInput");
	if (activity_type == "Cost") {
		hours = 0;

		billing_amount = $("#unitsInput").val();
		billing_rate = $("#billing_rateInput").val();
		billing_unit = $("#unit_nameInput").val();
	}
	var timekeeper = $("#billing_form #timekeeperInput").val();
	var url = "../api/activity/update_activity";

	var formValues = "hours=" + hours + "&case_id=" + this_case_id + "&table_id=" + table_id;
	formValues += "&timekeeper=" + timekeeper;
	if (activity_type == "Cost") {
		formValues += "&billing_amount=" + billing_amount;
		formValues += "&billing_rate=" + billing_rate;
		formValues += "&billing_unit=" + billing_unit;
	}
	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				resetCurrentContent();

				if (blnActivityPane && $("#activity_list_outer_div").length > 0) {
					/*
					//update the display
					if (document.location.hash.indexOf("#activity/") > 0) {
						window.Router.prototype.kaseActivity(current_case_id);
					}
					if (document.location.hash.indexOf("#billing/") > 0) {
						window.Router.prototype.kaseBilling(current_case_id);
					}
					*/
				}
			}
		}
	});
}
function saveNoteTask(tasksformValues, notes_id) {
	var tasks_url = 'api/task/add';

	tasksformValues = tasksformValues.replace("table_name=notes", "table_name=task")
	tasksformValues = tasksformValues.replaceAll("note", "task");
	tasksformValues = tasksformValues.replace("dateandtimeInput=", "ignore_me=");
	tasksformValues = tasksformValues.replace("callback_dateInput=", "task_dateandtimeInput=");
	tasksformValues = tasksformValues.replace("subjectInput=", "task_titleInput=");
	tasksformValues = tasksformValues.replace("taskInput=", "task_descriptionInput=");

	tasksformValues += "&task_type=open";
	tasksformValues += "&notes_id=" + notes_id;
	//return;
	$.ajax({
		url: tasks_url,
		type: 'POST',
		dataType: "json",
		data: tasksformValues,
		success: function (data) {
			blnSaving = false;
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			}
			resetCurrentContent();
		}
	});
}
function searchSettlements() {
	/*
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	*/

	//search by doctor
	var doctor_ids = $("#company_nameInput").val();
	var bodyparts = "";
	if ($("#bodypartSearchInput").val() != null) {
		bodyparts = "'" + $("#bodypartSearchInput").val().join("','") + "'";
	}
	var path = "templates/settlements_by_doctor_report.php";
	var params = [];
	params = { "doctor_ids": doctor_ids, "bodyparts": bodyparts };
	postForm(path, params, "post", "_blank");
}
function saveRental(event) {
	$("#modal_save_holder").html('<img src="img/loading_spinner_1.gif" width="20" height="20" id="gifsave" class="personal_injury" style="margin-top: -5px;">&nbsp;&nbsp;');

	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	var url = "api/personalinjury/addrental"

	formValues = $("#rental_form").serialize();

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				$("#gifsave").hide();
				$("#myModalLabel").css("color", "lime");
				window.Router.prototype.kasePersonalInjury(current_case_id);

				setTimeout(function () {
					$("#myModal4").modal("toggle");
				}, 1500);
			}
		}
	});
}
function saveRepair(event) {
	$("#modal_save_holder").html('<img src="img/loading_spinner_1.gif" width="20" height="20" id="gifsave" class="personal_injury" style="margin-top: -5px;">&nbsp;&nbsp;');

	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	var url = "api/personalinjury/addrepair"

	var formValues = $("#repair_form").serialize();

	$.ajax({
		url: url,
		type: 'POST',
		dataType: "json",
		data: formValues,
		success: function (data) {
			if (data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				resetCurrentContent();

				$("#gifsave").hide();
				$("#myModalLabel").css("color", "lime");
				window.Router.prototype.kasePersonalInjury(current_case_id);

				setTimeout(function () {
					$("#myModal4").modal("toggle");
				}, 1500);
			}
		}
	});
}
//solulab code
$(document).on('click', '.sent_to_docusents', function (e) {
	e.preventDefault();
	var element = $(this).attr('id');
	var elementArray = element.split("_");
		var case_id = elementArray[1];
		var jetfile_id = elementArray[2];
		var custo_id = elementArray[3];
		var fileDetails = {
			jetfile_case_id : case_id,
			jetfile_id : jetfile_id,
			file:"C:/inetpub/wwwroot/ikase.org/uploads/"+custo_id+"/"+case_id+"/eams_forms/app_cover_final.pdf",
			customer_id:custo_id
		};
		$.ajax({
			url:'/api/docucents/filesubmission',
			type:'POST',
			data:fileDetails,
			dataType:"json",
			success:function(data){
				if(data.status == "200"){
				alert(data.message);
				$('.close').trigger('click');
				var newFragment = Backbone.history.getFragment($(this).attr('href'));
				if (Backbone.history.fragment == newFragment) {
					// need to null out Backbone.history.fragement because 
					// navigate method will ignore when it is the same as newFragment
					Backbone.history.fragment = null;
					Backbone.history.navigate(newFragment, true);
				}
		} else{
			alert(data.message);
			$('.close').trigger('click');
		}
	}
		});
});