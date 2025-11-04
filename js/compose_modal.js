var blnOverdueTaskWarning = false;
function composeOverdueTasks(overdues) {
	if (blnOverdueTaskWarning) {
		//only once per session
		return;
	}
	
	if ($("#myModal4").css("display")!="none") {
		//retry later, this is not urgent
		setTimeout(function() {
			var updated_overdues = $(".overdue_tasks_indicator")[0].html();
			composeOverdueTasks(updated_overdues)
		}, 150000);
	}
	var html = "<div style='float:right'><button id='ignore_overdue_tasks' class='btn btn-sm btn-warning'>Ignore</button></div>";
	html += "<button id='review_overdue_tasks' class='btn btn-sm btn-primary'>Review</button>";
	$("#myModalBody").html(html);
	$("#myModalLabel").html("You have " + overdues + " overdue tasks");
	
	$(".modal-header").css("background-image", "url('img/glass_edit.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "350px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit.png')");
	$(".modal-header .close").hide();
	

	$("#myModal4").modal("toggle");
	
	//setup events
	$("#review_overdue_tasks").on("click", function(event) {
		event.preventDefault();
		//close the window
		$('#myModal4').modal('toggle');
		
		document.location.href = "#taskoverdue";
	});
	$("#ignore_overdue_tasks").on("click", function(event) {
		event.preventDefault();
		
		//add a note
		var url = 'api/notes/add';
		
		formValues = "table_name=notes";
		
		var note = "You have " + overdues + " overdue tasks";
		formValues += "&noteInput=" + note;
		formValues += "&status=IGNORED";
		formValues += "&subject=Overdue Warning Ignored";
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
				if (data.success) {
					
				}
			}
		});
		//close the window
		$('#myModal4').modal('toggle');
	});
	blnOverdueTaskWarning = true;
}
function composeSurgery(element_id, case_id) {
	var arrId = element_id.split("_");
	//default is new
	surgery_id = arrId[arrId.length - 1];
	
	var surgery = new Surgery({id: surgery_id});
	$("#gifsave").hide();
	if (surgery_id < 1) {
			
		var title = "Add Surgery";
		surgery.set("holder", "#myModalBody");
		surgery.set("case_id", case_id);	
		$("#myModalLabel").html(title);
		surgery.set("title", title);	
		$("#myModalBody").html(new surgery_form({model: surgery}).render().el);
	} else {
		surgery.fetch({
			success: function (surgery) {
				surgery.set("surgery_id", surgery.id);
				surgery.set("holder", "#myModalBody");
				var title = "Edit Surgery Details";
				$("#myModalLabel").html(title);
				
				$("#myModalBody").html(new surgery_form({model: surgery}).render().el);
			}
		});
	}
	
	$("#modal_type").val("surgery");

	$("#modal_save_holder").html('<a title="Save Surgery" class="check save" onClick="saveSurgery(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}
function composeLostIncome(element_id) {
	var title = "Lost Wage";
	$("#myModalLabel").html(title);
	var lostincome_id = -1;
	lostincome_case_id = current_case_id;
	
	var recipient = "";
	if (typeof element_id != "undefined") {
		var arrId = element_id.split("_");
		//default is new
		
		lostincome_id = arrId[arrId.length - 1]; 
	}
	var employer_id = document.location.hash.split("/")[2];
	var lostincome = new LostIncome({id: lostincome_id, corporation_id: employer_id});
	lostincome.set("holder", "#myModalBody");
	
	$("#gifsave").hide();
	if (lostincome_id < 1) {
			
		var title = "Add Lost Wages Entry";
		lostincome.set("case_id", lostincome_case_id);	

		
		$("#myModalLabel").html(title);
		lostincome.set("title", title);	
		$("#myModalBody").html(new lostincome_view({model: lostincome}).render().el);
	} else {
		lostincome.fetch({
			success: function (lostincome) {
				lostincome.set("lostincome_id", lostincome.id);
				title = "Edit Lost Wages Entry";
				$("#myModalLabel").html(title);			
				$("#myModalBody").html(new lostincome_view({model: lostincome}).render().el);
			}
		});
	}
	
	$("#modal_type").val("lostincome");

	$("#modal_save_holder").html('<a title="Save Lost Income" class="lostincome save" onClick="saveLostIncomeModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	//$("#input_for_lostincomebox").html('&nbsp;');
	var theme = {theme: "lostincome"};
	$(".lostincome #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}
function composeVocational(mymodel) {
	var title = "Refer for Vocational Services";
	$("#myModalLabel").html(title);
	getCurrentCaseID();
	
	var kase = kases.findWhere({"case_id": current_case_id});
	var mykase = kase.clone();
	mykase.set("holder", "myModalBody");
	$("#myModalBody").html(new refer_vocational_view({model: mykase}).render().el);
	
	$("#modal_type").val("refer_vocational");

	$("#modal_save_holder").html('');
	$("#modal_save_holder").show();
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "680px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}
function composeRental(mymodel) {
	var title = mymodel.get("representing").capitalize() + " :: Rental";
	$("#myModalLabel").html(title);
	mymodel.set("holder", "myModalBody");
	$("#myModalBody").html(new rental_view({model: mymodel}).render().el);
	
	$("#modal_type").val("rental");

	$("#modal_save_holder").html('<a title="Save Rental" class="rental save" onClick="saveRental(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "480px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}
function composeRepair(mymodel) {
	var title = mymodel.get("representing").capitalize() + " :: Repairs";
	$("#myModalLabel").html(title);
	mymodel.set("holder", "myModalBody");
	$("#myModalBody").html(new repair_view({model: mymodel}).render().el);
	
	$("#modal_type").val("repair");

	$("#modal_save_holder").html('<a title="Save Repair" class="repair save" onClick="saveRepair(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "480px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}
function composeAdjustment(element_id, account_id) {
	var adjustment_id = -1;
	adjustment_account_id = account_id;
	
	var recipient = "";
	if (typeof element_id != "undefined") {
		var arrId = element_id.split("_");
		//default is new
		
		adjustment_id = arrId[arrId.length - 1]; 
	}
	var adjustment = new Adjustment({id: adjustment_id});
	adjustment.set("holder", "#myModalBody");
	adjustment.set("account_id", adjustment_account_id);	
	
	$("#gifsave").hide();
	
	if (adjustment_id < 1) {
			
		var title = "Add Adjustment";
		
		adjustment.set("adjustment_date", moment().format('MM/DD/YYYY'));
		$("#myModalLabel").html(title);
		adjustment.set("title", title);	
		$("#myModalBody").html(new adjustment_form({model: adjustment}).render().el);
	} else {
		adjustment.fetch({
			success: function (adjustment) {
				adjustment.set("adjustment_id", adjustment.id);
				title = "Edit Adjustment";
				$("#myModalLabel").html(title);			
				$("#myModalBody").html(new adjustment_form({model: adjustment}).render().el);
			}
		});
	}
	
	$("#modal_type").val("adjustment");

	$("#modal_save_holder").html('<a title="Save Adjustment" class="adjustment save" onClick="saveAdjustmentModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	//$("#input_for_adjustmentbox").html('&nbsp;');
	var theme = {theme: "adjustment"};
	$(".adjustment #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}
function composeFlatCost(element_id) {
	var deduction_id = -1;
	deduction_case_id = current_case_id;
	
	var recipient = "";
	if (typeof element_id != "undefined") {
		var arrId = element_id.split("_");
		//default is new
		
		deduction_id = arrId[arrId.length - 1]; 
	}
	var deduction = new Deduction({id: deduction_id});
	deduction.set("holder", "#myModalBody");
	deduction.set("case_id", deduction_case_id);	
	
	$("#gifsave").hide();
	
	if (deduction_id < 1) {
			
		var title = "Add Cost";
		
		deduction.set("deduction_date", moment().format('MM/DD/YYYY'));
		$("#myModalLabel").html(title);
		deduction.set("title", title);	
		$("#myModalBody").html(new cost_form({model: deduction}).render().el);
	} else {
		deduction.fetch({
			success: function (deduction) {
				deduction.set("deduction_id", deduction.id);
				title = "Edit Cost";
				$("#myModalLabel").html(title);			
				$("#myModalBody").html(new cost_form({model: deduction}).render().el);
			}
		});
	}
	
	$("#modal_type").val("deduction");
	
	$("#modal_save_holder").html('<a title="Save Deduction" class="deduction save" onClick="saveDeductionModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	//$("#input_for_deductionbox").html('&nbsp;');
	var theme = {theme: "deduction"};
	$(".deduction #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
	setTimeout(function(){
		$("#amountInput").val("150.00");
	}, 1000);
}
function composeDeduction(element_id) {
	var deduction_id = -1;
	deduction_case_id = current_case_id;
	
	var recipient = "";
	if (typeof element_id != "undefined") {
		var arrId = element_id.split("_");
		//default is new
		
		deduction_id = arrId[arrId.length - 1]; 
	}
	var deduction = new Deduction({id: deduction_id});
	deduction.set("holder", "#myModalBody");
	deduction.set("case_id", deduction_case_id);	
	
	$("#gifsave").hide();
	
	if (deduction_id < 1) {
			
		var title = "Add Deduction";
		
		deduction.set("deduction_date", moment().format('MM/DD/YYYY'));
		$("#myModalLabel").html(title);
		deduction.set("title", title);	
		$("#myModalBody").html(new deduction_form({model: deduction}).render().el);
	} else {
		deduction.fetch({
			success: function (deduction) {
				deduction.set("deduction_id", deduction.id);
				title = "Edit Deduction";
				$("#myModalLabel").html(title);			
				$("#myModalBody").html(new deduction_form({model: deduction}).render().el);
			}
		});
	}
	
	$("#modal_type").val("deduction");

	$("#modal_save_holder").html('<a title="Save Deduction" class="deduction save" onClick="saveDeductionModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	//$("#input_for_deductionbox").html('&nbsp;');
	var theme = {theme: "deduction"};
	$(".deduction #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}

function composeNegotiation(element_id, negotiation_type, corporation_id) {
	if (typeof negotiation_type == "") {
		negotiation_type = "";
	}
	var negotiation_id = -1;
	negotiation_case_id = current_case_id;
	
	var recipient = "";
	if (typeof element_id != "undefined") {
		var arrId = element_id.split("_");
		//default is new
		
		negotiation_id = arrId[arrId.length - 1]; 
	}
	var negotiation = new Negotiation({id: negotiation_id});
	negotiation.set("holder", "#myModalBody");
	negotiation.set("case_id", negotiation_case_id);	
	
	$("#gifsave").hide();
	
	if (negotiation_id < 1) {
			
		var title = "Add Negotiation";
		if (typeof corporation_id != "undefined") {
			negotiation.set("corporation_id", corporation_id);
		}
		negotiation.set("negotiation_date", moment().format('MM/DD/YYYY'));
		negotiation.set("negotiation_type", negotiation_type);
		$("#myModalLabel").html(title);
		negotiation.set("title", title);	
		$("#myModalBody").html(new negotiation_form({model: negotiation}).render().el);
	} else {
		negotiation.fetch({
			success: function (negotiation) {
				negotiation.set("negotiation_id", negotiation.id);
				negotiation.set("holder", "#myModalBody");
				negotiation.set("case_id", negotiation_case_id);	
				title = "Edit Negotiation";
				$("#myModalLabel").html(title);			
				$("#myModalBody").html(new negotiation_form({model: negotiation}).render().el);
			}
		});
	}
	
	$("#modal_type").val("negotiation");

	$("#modal_save_holder").html('<a title="Save Negotiation" class="negotiation save" onClick="saveNegotiationModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	//$("#input_for_negotiationbox").html('&nbsp;');
	var theme = {theme: "negotiation"};
	$(".negotiation #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "720px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}

function composeCheck(element_id, ledger, context, jsonInvoice, account_id, account_type, case_id, corp_id) {
	getCurrentCaseID();
	var check_id = -1;
	if (typeof case_id == "undefined") {
		case_id = "";
	}
	if (typeof corp_id == "undefined") {
		corp_id = "";
	}
	if (context=="invoice") {
		check_case_id = jsonInvoice.case_id;
	} else {
		if (case_id == "") {
			check_case_id = current_case_id;
		} else {
			check_case_id = case_id;
		}
	}
	//might be...
	if (typeof jsonInvoice != "undefined") {
		if (typeof jsonInvoice.case_id != "undefined") {
			check_case_id = jsonInvoice.case_id;
		}
	}
	
	if (check_case_id > 0) {	
		var kase = kases.findWhere({case_id: check_case_id});
		if (typeof kase == "undefined") {
			var kase =  new Kase({id: check_case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.remove(kase.id); kases.add(kase);
						setTimeout(function() {
							composeCheck(element_id, ledger, context, jsonInvoice, account_id, account_type, kase.get("case_id"), corp_id);
						}, 100);
						
						return;		
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
	}
	
	if (typeof ledger == "undefined") {
		ledger = "";
	} 
	if (typeof context == "undefined") {
		context = "";
	} 
	if (typeof jsonInvoice == "undefined") {
		jsonInvoice = {};
	}
	if (typeof account_id == "undefined") {
		account_id = "";
	} 
	if (typeof account_type == "undefined") {
		account_type = "";
	} 
	if (element_id=="undefined") {
		element_id = undefined;
	}
	var recipient = "";
	var check_id = -1;
	var payback_id = -1;
	if (typeof element_id != "undefined") {
		var arrId = element_id.split("_");
		//default is new
		check_id = 0;
		if (arrId[0]=="payback") {
			ledger = "IN";
			payback_id =  arrId[1];
		} else {
			if (arrId[0]=="payment") {
				//recipient will mean that this is a settlement payment
				ledger = "IN";
				recipient = arrId[1];
			} else {
				check_id = arrId[2]; 
			}
		}
	}
	if (check_case_id=="null") {
		check_case_id = "";
	}
	var check = new Check({check_id: check_id, case_id: check_case_id});
	check.set("holder", "#myModalBody");
	$("#gifsave").hide();
	if (check_id < 1) {
			
		var title = "New Payment";
		
		if (ledger=="OUT") {
			title = "New Check/Disbursement";
		}
		
		if (account_id!="") {
			title = "New " + account_type.capitalizeWords() + " Account Deposit";
			if (account_type=="operating") {
				title = "New Cost Trust Account Deposit";
			}
			check.set("amount_due", 0);
		} else {
			if (payback_id!="" && payback_id!=-1) {
				var amount_due = $("#check_amount_" + payback_id).val();
				var check_number_title = $("#check_number_" + payback_id).val();
				title = "Reimburse Advanced Payment - #" + check_number_title;
				
				check.set("amount_due", amount_due);
				check.set("payment", payment);
			}
		}
		
		if (recipient!="") {
			title = recipient.capitalize() + " Settlement Payment";
		}
		if (context=="invoice") {
			//transfer invoice info
			if (typeof jsonInvoice.kinvoice_id == "undefined") {
				jsonInvoice.kinvoice_id = "";
			}
			check.set("kinvoice_id", jsonInvoice.kinvoice_id);
			check.set("amount_due", Number(jsonInvoice.total));
			check.set("payments", Number(jsonInvoice.payments));
			check.set("invoice_number", jsonInvoice.invoice_number);
			check.set("invoiced", jsonInvoice.corporation);
			check.set("invoiced_id", jsonInvoice.corporation_id);
			
			title = "Invoice Payment :: Invoice # " + jsonInvoice.invoice_number;
			ledger = "IN";
			recipient = "";
		}
		if (check.get("payments")=="") {
			check.set("payments", 0);
		}
		if (check.get("amount_due")=="") {
			check.set("amount_due", 0);
		}
		check.set("transaction_date", moment().format('MM/DD/YYYY'));
		check.set("case_id", check_case_id);	
		check.set("corp_id", corp_id);	
		check.set("payback_id", payback_id);
		check.set("ledger", ledger);	
		check.set("recipient", recipient);	
		check.set("context", context);
		
		check.set("account_id", account_id);	
		check.set("account_type", account_type);
		
		$("#myModalLabel").html(title);
		check.set("title", title);	
		$("#myModalBody").html(new check_form({model: check}).render().el);
	} else {
		var new_check = new Check({check_id: check_id, case_id: check_case_id});
		new_check.fetch({
			success: function (check) {
				check.set("check_id", check.id);
				var label = "Payment";
				var ledger = check.get("ledger");
				if (ledger=="OUT") {
					label = "Check";
				}
				if (ledger=="DIS") {
					label = "Disbursement";
				}
				if (context=="" || context=="payments") {
					$("#myModalLabel").html("Edit "  + label);
				}
				if (context!="" && context!="payments") {
					recipient = context;
					title = "Edit " + recipient.capitalize() + " "  + label;
					$("#myModalLabel").html(title);
				}
				if (context=="invoice") {
					//transfer invoice info
					check.set("kinvoice_id", jsonInvoice.kinvoice_id);
					check.set("invoice_number", jsonInvoice.kinvoice_number);
					check.set("invoiced", jsonInvoice.corporation);
					check.set("invoiced_id", jsonInvoice.corporation_id);
					
					title = "Edit Invoice Payment :: Invoice # " + jsonInvoice.kinvoice_number;
					recipient = "";
					$("#myModalLabel").html(title);
				}
				check.set("context", context);
				check.set("recipient", recipient);	
				if (account_id!=="" && account_id!=="-1") {
					check.set("account_id", account_id);
				}
				if (account_type!="") {
					check.set("account_type", account_type.toLowerCase());
				}
				check.set("payback_id", "");
				$("#myModalBody").html(new check_form({model: check}).render().el);
			}
		});
	}
	
	$("#input_for_checkbox").hide();
	
	
	$("#modal_type").val("check");

	$("#modal_save_holder").html('<button class="btn btn-sm btn-danger" style="display:none; margin-right:20px" id="void_check" onClick="voidCheck(event)">Void Check</button><a title="Save Check" class="check save" onClick="saveCheckModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	//$("#input_for_checkbox").html('&nbsp;');
	var theme = {theme: "check"};
	$(".check #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "770px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}
function composeNewFee(element_id, settlement_id) {
	var fee_injury_id = document.location.hash.split("/")[2];
	var fee_case_id = current_case_id;
	var action = "";
	if (element_id.indexOf("add") > -1 || element_id.indexOf("pay") > -1) {
		var arrType = element_id.split("_");
		var fee_type = arrType[1];
		var action = arrType[0];
		//var fee_type = element_id.replace("add_", "");
		//fee_type = fee_type.replace("addfee_", "");
		var fee_id = -1;
		if (element_id.indexOf("pay") > -1) {
			fee_id = arrType[2];
		}
	} else {
		var arrType = element_id.split("_");
		var action = arrType[0];
		var fee_type = arrType[1];
		var fee_id = arrType[2];
	}
	var display_fee_type = fee_type.capitalize();
	switch(fee_type) {
		case "deposition":
			fee_type = "depo";
			break;
		case "rehabilitation":
			fee_type = "rehab";
			break;
		case "ss":
			title = "New Social Security Fee";
			break;
	}
	
	if (fee_id < 1) {
		var fee = new Fee({id: fee_id, injury_id: fee_injury_id, type: fee_type, settlement_id: settlement_id});
		
		var title = "New " + display_fee_type + " Fee";	
		fee.set("title", title);
		fee.set("fee_requested", moment().format('YYYY-MM-DD'));
		fee.set("case_id", fee_case_id);	
		fee.set("injury_id", fee_injury_id);
		fee.set("fee_type", fee_type);	
		fee.set("action", action);
		fee.set("holder", "myModalBody");	
		$("#myModalLabel").html(title);
		$("#myModalBody").html(new fee_form({model: fee}).render().el);
	} else {
		var fee = new Fee({id: fee_id});
		fee.fetch({
			success: function(fee) {
				var title = "Edit " + display_fee_type + " Fee";
				if (action=="pay") {
					title = "Pay " + display_fee_type + " Fee";
					fee.set("paid_fee", fee.get("fee_billed"));
					fee.set("fee_billed", 0);
					//remove the id so that we get a brand new entry
					fee.set("parent_table_id", fee.get("id"));
					
					fee.set("fee_id", -1);
					fee.set("id", -1);
					
					fee.set("fee_requested", moment().format("YYYY-MM-DD"));
				}
				fee.set("title", title);
				fee.set("case_id", fee_case_id);	
				fee.set("injury_id", fee_injury_id);
				fee.set("action", action);
				fee.set("holder", "myModalBody");	
				
				if (fee.get("settlement_id")=="0") {
					fee.set("settlement_id", $("#settlement_id").val());
				}
				$("#myModalLabel").html(title);
				$("#myModalBody").html(new fee_form({model: fee}).render().el);
			}
		});
	}
		
	$("#modal_type").val("fee");
	$("#modal_save_holder").html('<a title="Save Fee" class="fee save" onClick="saveFee(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}
function composeCheckRequest(element_id, corp_id, case_id, blnSettlementRequestsPending) {
	//alert("second");
	getCurrentCaseID();
	var checkrequest_id = -1;
	if (typeof case_id == "undefined") {
		case_id = "";
	}
	if (typeof corp_id == "undefined") {
		corp_id = "";
	}
	if (typeof blnSettlementRequestsPending == "undefined") {
		blnSettlementRequestsPending = false;
	}
	if (case_id == "") {
		checkrequest_case_id = current_case_id;
	} else {
		checkrequest_case_id = case_id;
	}
	var case_name = "";
	var case_type = "";
	if (checkrequest_case_id > 0) {
		var kase = kases.findWhere({ case_id: checkrequest_case_id });
		if (typeof kase == "undefined") {
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.remove(kase.id); kases.add(kase);
						composeCheckRequest(element_id, corp_id, case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		case_name = kase.get("case_name");
		case_type = kase.get("case_type");
	}
	var recipient = "";
	var checkrequest_id = -1;
	var blnBulk = false;
	var blnSettlement = true;
	var blnReview = false;
	
	if (typeof element_id != "undefined") {
		var arrId = element_id.split("_");
		var blnBulk = (arrId[arrId.length - 2]=="bulk"); 
		
		if (blnBulk) {
			blnSettlement = true;
		} else {
			blnSettlement = (element_id!="general_checkrequest");
		}
		
		if (blnSettlement) {
			//default is new
			checkrequest_id = arrId[arrId.length - 1]; 
		
		}
		blnReview = (arrId[arrId.length - 2]=="review"); 
	}
	
	var checkrequest = new CheckRequest({checkrequest_id: checkrequest_id, case_id: checkrequest_case_id});
	checkrequest.set("holder", "#myModalBody");
	checkrequest.set("blnBulk", blnBulk);
	checkrequest.set("blnSettlement", blnSettlement);
	checkrequest.set("blnSettlementRequestsPending", blnSettlementRequestsPending);
	
	checkrequest.set("case_name", case_name);//case_name	
	checkrequest.set("case_type", case_type);	
		
	$("#gifsave").hide();
	if (checkrequest_id < 1) {
		//alert("second");	
		var title = "New Check Request";
		
		checkrequest.set("request_date", moment().format('MM/DD/YYYY'));
		checkrequest.set("case_id", checkrequest_case_id);	
		checkrequest.set("corp_id", corp_id);	
		checkrequest.set("holder", "myModalBody");	
		//alert("here");
		$("#myModalLabel").html(title);
		checkrequest.set("title", title);	
		$("#myModalBody").html(new checkrequest_form({model: checkrequest}).render().el);
	} else {
		//alert("there");
		var new_checkrequest = new CheckRequest({checkrequest_id: checkrequest_id, case_id: checkrequest_case_id});
		new_checkrequest.fetch({
			success: function (checkrequest) {
				if (!blnReview) {
					$("#myModalLabel").html("Edit Check Request");
				} else {
					$("#myModalLabel").html("Review Check Request (read-only)");
				}
				checkrequest.set("holder", "myModalBody");	
				checkrequest.set("corp_id", corp_id);	
				checkrequest.set("blnBulk", blnBulk);
				checkrequest.set("blnSettlement", blnSettlement);
				$("#myModalBody").html(new checkrequest_form({model: checkrequest}).render().el);
			}
		});
	}
	
	var save_function = "saveCheckRequestModal";
	if (blnBulk) {
		save_function += "Bulk";
	}
	$("#modal_type").val("checkrequest");
	$("#modal_save_holder").html('<a title="Send Check Request" class="checkrequest save" onClick="' + save_function + '(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	//$("#input_for_checkrequestbox").html('&nbsp;');
	var theme = {theme: "checkrequest"};
	$(".checkrequest #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});

	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}
function composeActivity(element_id, hours, activity_val) {
	var activity_case_id = current_case_id;
	
	//var arrId = element_id.split("_");
	var activity_uuid = element_id; 
	
	var activity = new Activity({activity_uuid: activity_uuid, case_id: activity_case_id});
	activity.fetch({
		success: function (data) {
			data.set("activity_id", data.id);
			data.set("holder", "#myModalBody");
			data.set("hours", hours);
			data.set("activity", activity_val);
			$("#myModalBody").html(new activity_view({model: data}).render().el);
			$("#myModalLabel").html("Edit Activity");
			/*
			$("#hoursInput").val(hours);
			//$("#activityInput").val(activity_val);
			setTimeout(function() {
				$(".iframe_bulk").html(activity_val);
			}, 2000);
			*/
		}
	});
	
	$("#input_for_checkbox").hide();
	
	
	$("#modal_type").val("activity");
	$("#modal_save_holder").html('<a title="Save Activity" class="activity save" id="save_' + activity_uuid + '" onClick="saveActivityModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_check.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_check.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_check.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_check.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_check.png')");
}
function composeNewActivity() {
	var activity_case_id = current_case_id;
	
	$("#input_for_checkbox").hide();
	
	var activity = new Activity({activity_uuid: "", case_id: activity_case_id});
	activity.set("holder", "#myModalBody");
	$("#modal_type").val("activity");
	$("#modal_save_holder").html('<a title="Save Activity" class="activity save" id="save" onClick="saveActivityModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_check.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_check.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_check.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_check.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_check.png')");
	$("#myModalBody").html(new activity_view({model: activity}).render().el);
	$("#myModalLabel").html("Edit Activity");
}
function composeNote(args) {
	var message = new Note({notes_id: -1, case_id: current_case_id});
	//$("#myModal4").css("background", "url(img/glass_modal.png)");
	
	$("#myModalLabel").html("New Message");
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").html('<a title="Send Message" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-send" style="color:#000066; font-size:20px">&nbsp;</i></a>');
	$("#myModalBody").html(new message_view({model: message}).render().el);
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	//$(".modal-body").css("width", "100%"); 
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
}
function composeLetter(element_id, jInvoiceInfo, kase_dois) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	
	if (typeof jInvoiceInfo == "undefined") {
		jInvoiceInfo = {};
	}
	getCurrentCaseID();
	
	if (typeof kase_dois == "undefined") {
		var kase_dois = new KaseInjuryCollection({case_id: current_case_id});
		kase_dois.fetch({
			success: function(kase_dois) {
				composeLetter(element_id, jInvoiceInfo, kase_dois);
			}
		});
		return;
	}
	
	var partieArray = element_id.split("_");
	var partie_array_type = partieArray[1];
	var case_id = current_case_id;
	var template_id = -1;
	var kinvoice_id = -1;
	var account_id = -1;
	if (partie_array_type=="letter") {
		partie_array_type = "";
		if (partieArray.length > 2) {
			case_id = partieArray[2];
		}
		if (partieArray.length > 3) {
			template_id = partieArray[3];
			//get template info
			if (typeof word_templates == "undefined") {
				word_templates = new WordTemplates([]);
				word_templates.fetch({
					success: function(data) {
						blnWordTemplates = true;
						composeLetter(element_id, jInvoiceInfo);
						return;
					}
				});	
				return;
			} 
			var word_template = word_templates.get(template_id);
		}
		if (partieArray.length > 4) {
			kinvoice_id = Number(partieArray[4]);
		}
		if (partieArray.length > 5) {
			account_id = Number(partieArray[5]);
		}
	}
	
	var modal_title = "";
	if (partieArray.length > 2 && partie_array_type != "") {
		modal_title = partieArray[1].capitalize();
	}
	
	//we need to make sure that the doi is in the system
	//var kase_dois = dois.where({case_id: case_id});
	//if not found, go get it
	
	
	$("#input_for_checkbox").hide();
	
	$("#modal_save_holder").html('<input type="checkbox" id="parties_selectall" class="parties_selectall" name="parties_selectall" value="Y" title="Check this box to Select all Parties" onclick="selectAllParties()" /><span class="white_text parties_selectall" style="padding-right:15px">Select all Parties</span>&nbsp;<a title="Save Letter" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px; text-decoration:none">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("letter");
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModalBody").css("width", "100%");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$("#myModal4 .modal-dialog").css("width", "1000px");
	//$("#myModal4").css("top", "10%");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	//var kases = new KaseCollection({ case_id : case_id });
	var kase = kases.findWhere({case_id: case_id});
	var kased = kases.toJSON()[0];
	//alert(kased)
	//always will have a template, at least for now

	var template_letter = new Document({id: template_id, case_id: case_id});
	template_letter.fetch({
		success: function (data) {
			var case_name =  kase.name();
			
			var case_name =  kase.name();
			var file_number = kase.get("file_number");
			if (file_number!="") {
				case_name += " - " + file_number;
			}
			data.set("case_name", case_name);
			data.set("kase_dois", kase_dois);
			data.set("account_id", account_id);
			data.set("kinvoice_id", kinvoice_id);
			data.set("jInvoiceInfo", jInvoiceInfo);
			data.set("attorney_name", "");
			data.set("attorney_full_name", "");
			data.set("supervising_attorney_name", "");
			data.set("supervising_attorney_full_name", "");
			
			if (kase.get("attorney_name")=="" && kase.get("supervising_attorney")=="") {
				data.set("attorney_name", "<span style='font-size:0.7em'>no atty</span>");
			} else {
				data.set("attorney_name", kase.get("attorney_name"));
				data.set("attorney_full_name", kase.get("attorney_full_name"));
				data.set("supervising_attorney_name", kase.get("supervising_attorney_name"));
				data.set("supervising_attorney_full_name", kase.get("supervising_attorney_full_name"));
			}
			if (kase.get("worker_name")=="") {
				data.set("worker_name", "<span style='font-size:0.7em'>no worker</span>");
			} else {
				data.set("worker_name", kase.get("worker_name"));
			}
			data.set("holder", "myModalBody");
			$("#myModalBody").html(new letter_view({model: data}).render().el);
			$("#myModalLabel").html("Create Letter");
			
			if (kinvoice_id > -1) {
				var label = "Edit Invoice";
				if (jInvoiceInfo.template=="Y") {
					label = "Create Invoice";
				}
				$("#myModalLabel").html(label);
				$("#myModal4").modal("toggle");
			}
		}
	});
}
function composeEamsForm(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var partieArray = element_id.split("_");
	var partie_array_type = partieArray[1];
	var eams_form_id = -1
	if (partieArray.length > 1) {
		eams_form_id = partieArray[1]
	}
	
	if (eams_form_id < 0) {
		var new_eams_form = new EAMSForm();
		new_eams_form.set("eams_form_id", eams_form_id);
		new_eams_form.set("name", "");
		new_eams_form.set("display_name", "");
		new_eams_form.set("status", "");
		new_eams_form.set("holder", "myModalBody");
		$("#myModalBody").html(new eams_form_view({model: new_eams_form}).render().el);
		$("#myModalLabel").html("New EAMS Form");
	} else {
		var new_eams_form = new EAMSForm({eams_form_id: eams_form_id});
		new_eams_form.fetch({
			success: function (data) {
				data.set("holder", "myModalBody");
				$("#myModalBody").html(new eams_form_view({model: data}).render().el);
				$("#myModalLabel").html("Edit EAMS Form - ID " + eams_form_id);
			}
		});
	}
	$("#apply_settings").hide();
	
	$("#modal_save_holder").html('<a title="Save EAMS Form" class="setting save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');

	$("#modal_type").val("eams_form");
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
}
function composeEams(element_id, form_name) {
	var partieArray = element_id.split("_");
	var case_id = partieArray[1];
	
	//make sure the dois are up to date
	var kase_dois = dois.where({"case_id": case_id});
	blnRefreshDOI = false;
	//console.log(kase_dois);
	//if not found, go get it
	if (typeof kase_dois == "undefined") {
		blnRefreshDOI = true;
	}
	if (typeof kase_dois == "object") {
		if (kase_dois.length==0) {
			blnRefreshDOI = true;
		}
	}
	if (blnRefreshDOI) {
		var kase_dois = new KaseInjuryCollection({"case_id": case_id});
		kase_dois.fetch({
			success: function(data) {
				
				for(var i = 0; i < data.models.length; i++) {
					var doi = data.models[i].toJSON();
					dois.add(doi);
				}
				
				//dois.add(data);
				composeEams(element_id, form_name);
			}
		});
		return;
	}
		
	$("#gifsave").hide();
	$("#modal_save_holder").show();
		
	var injury_id = "";
	if (partieArray.length==4) {
		injury_id = partieArray[3];
	}
	var kase = kases.findWhere({case_id: case_id});
	if (typeof kase == "undefined") {
		var kase =  new Kase({id: case_id});
		kase.fetch({
			success: function (kase) {
				if (kase.toJSON().uuid!="") {
					kases.remove(kase.id); kases.add(kase);
					composeEams(element_id, form_name);
				} else {
					//case does not exist, get out
					document.location.href = "#";
				}
				return;		
			}
		});
		return;
	}
	
	var modal_title = "";
	/*
	if (partieArray.length > 2) {
		modal_title = partieArray[2].capitalize();
	}
	*/
	if (element_id.indexOf("jetfiler")==0) {
		//from jetfile submissions
		//form_name = partieArray[0].replace("jetfiler", "");
		modal_title = form_name.toUpperCase().replaceAll("_", " ");
	} else {
		//from eams forms listing page
		form_name = $("#" + element_id.replace("eamsforms_", "eamsname_")).val();
		modal_title = $("#" + element_id).html();
	}
	
	$("#myModalLabel").html(modal_title);
	//always will have a template, at least for now

	var data = new Backbone.Model;
	data.set("case_id", case_id);
	data.set("injury_id", injury_id);
	data.set("case_name", kase.name());
	data.set("eams_form_name", form_name);
	data.set("eams_display_name", modal_title);
	data.set("holder", "myModalBody");
	
	$("#myModalBody").html(new eams_view({model: data}).render().el);
	$("#input_for_checkbox").hide();
	if (blnPiReady) { 
			//$("#modal_billing_holder").html(billing_dropdown_template);
	}
	$("#modal_save_holder").html('<a title="Save Eams" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("eams");
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	if (partieArray[2] == "lien") {
		$("#myModal4 .modal-dialog").css("width", "1300px");
		/*
		setTimeout(function() {
			$("#lien_form").css("display", "");
			$('#myModal4 .modal-dialog').css('top', '10%');
			$('#myModal4 .modal-dialog').css('margin-top', '50px');
		}, 700);
		*/
	} else {
		$("#myModal4 .modal-dialog").css("width", "620px");
		$("#lien_form").css("display", "none");
		/*
		setTimeout(function() {
			$('#myModal4 .modal-dialog').css('top', '20%');
			$('#myModal4 .modal-dialog').css('margin-top', '50px');
		}, 700);
		*/
	}
	
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	if (injury_id!="") {
		setTimeout(function() {
			$("#fields_holder #doi").val(injury_id);
		}, 1200);
	}
}
function composeEamsImport(adj_number, import_or_lookup, case_id) {
	if (typeof import_or_lookup == "undefined") {
		import_or_lookup = "import";
	}
	if (typeof adj_number == "undefined") {
		adj_number = "";
	}
	if (typeof case_id == "undefined") {
		case_id = "";
	}
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	
	if (import_or_lookup == "import") {
		$("#myModalLabel").html("Import EAMS Data");
	}
	if (import_or_lookup == "lookup") {
		$("#myModalLabel").html("Lookup EAMS");
		$("#modal_save_holder").html('<span id="scrape_print" onclick="printEams()" style="cursor:pointer"><i class="glyphicon glyphicon-print" style="color:magenta; font-size:1.2em" aria-hidden="true">&nbsp;</i></span>');
	}

	var data = new Backbone.Model;
	data.set("holder", "myModalBody");
	data.set("adj_number", adj_number);
	data.set("case_id", case_id);
	data.set("import_or_lookup", import_or_lookup);
	
	$("#myModalBody").html(new eams_scrape_view({model: data}).render().el);
	$("#input_for_checkbox").hide();
	
	//$("#modal_save_holder").html('<a title="Import Eams" class="eams_import save" onClick="importEams()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("eams_import");
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});

	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4 .modal-dialog").css("width", "1100px");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	$("#myModal4").modal("toggle");
}
function composeCalendar(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var calendarArray = element_id.split("_");
	var calendar_id = -1;
	if (calendarArray.length == 3) {
		calendar_id = calendarArray[2];
	}
	if (calendar_id < 0) {
		var new_calendar = new Backbone.Model({new_calendar_id: calendar_id});
		new_calendar.set("calendar_id", calendar_id);
		new_calendar.set("calendar", "");
		new_calendar.set("active", "Y");
		new_calendar.set("sort_order", "");
		//new_calendar.set("dateandtime", moment().format('MM/DD/YYYY h:mm a'));
		$("#myModalBody").html(new calendar_view({model: new_calendar}).render().el);
		$("#myModalLabel").html("New Calendar");
	} else {
		var new_calendar = new Calendar({calendar_id: calendar_id});
		new_calendar.fetch({
			success: function (data) {
				data.set("calendar_id", data.id);
				$("#myModalBody").html(new calendar_view({model: data}).render().el);
				$("#myModalLabel").html("Edit Calendar");
			}
		});
	}
	
	$("#modal_save_holder").html('<a title="Save Calendar" class="calendar save" onClick="saveCalendarModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("calendar");
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
}
function composeSettlement(element_id) {
	//alert("here");
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var settlementArray = element_id.split("_");
	var settlement_id = settlementArray[22];
	var injury_id = settlementArray[3];
	
	var settlement = new Settlement({settlement_id: settlement_id, injury_id: injury_id});
	$("#input_for_checkbox").hide();
	$("#myModalLabel").html("Edit Settlement");
	$("#modal_save_holder").html('<a title="Save Settlement" class="settlement save" onClick="saveModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#apply_notes").hide();
	settlement.set("holder", "myModalBody");
	$("#myModalBody").html(new settlement_view({model: settlement}).render().el);
	$("#apply_notes").hide();
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");

	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
	$('#myModal4').modal('show');		
}
function composeMessage(object_id, jsonInvoice) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	if (typeof object_id == "undefined") {
		object_id = "";
	}
	if (typeof jsonInvoice == "undefined") {
		jsonInvoice = {};
	}
	//make sure we have the right current case id
	if (current_case_id == -1) {
		var hash = document.location.hash;
		if (hash.indexOf("#kases/") > -1 || hash.indexOf("#parties/") > -1 || hash.indexOf("#notes/") > -1 || hash.indexOf("#tasks/") > -1 || hash.indexOf("#documents/") > -1 || hash.indexOf("#injury/") > -1 || hash.indexOf("#activity/") > -1 || hash.indexOf("#billing/") > -1 || hash.indexOf("#payments/") > -1 || hash.indexOf("#eams_forms/") > -1 || hash.indexOf("#letters/") > -1) {
			var arrHash = hash.split("/");
			current_case_id = arrHash[1];
		}
	}
	//initialize the draft
	draft_id = 0;
	//are we replying, replyingall, forwarding
	var reaction = "";
	var object_action = "New Message";
	var case_id = current_case_id;
	var message = new Message({message_id: -1});
	message.set("source_message_id", object_id);
	message.set("case_id", current_case_id);
	
	//process the root if any
	var blnDraft = false;
	if (object_id != "" && !Array.isArray(object_id)) {
		var arrObject = object_id.split("_");
		reaction = arrObject[0];
		
		//special case
		if (reaction=="messagerow") {
			if (document.location.hash=="#drafts") {
				reaction = "draft";
				arrObject[0] = reaction;
			}
		}
		source_message_id = arrObject[1];
		message.set("source_message_id", source_message_id);
		message.set("document_id", "");
		//not valid for forwarding scans
		if (arrObject[0]!="sendstack") {
			message.set("case_id", current_case_id);
		} else {
			//is there a case assigned to the stack
			case_id = $("#stack_case_id_" + source_message_id).val();
			message.set("case_id", case_id);
		}
		switch (reaction) {
			case "contact":
				object_action = "Compose Message";
				message.set("contact_id", source_message_id);
				break;
			case "compose":
				object_action = "Compose Message";
				case_id = source_message_id;
				message.set("case_id", case_id);
				source_message_id = -1;
				break;
			case "invoice":
				case_id = source_message_id;
				message.set("case_id", case_id);
				source_message_id = -1;
				
				//invoice stuff
				message.set("kinvoice_id", jsonInvoice.kinvoice_id);
				message.set("amount_due", Number(jsonInvoice.total));
				message.set("payments", Number(jsonInvoice.payments));
				message.set("invoice_number", jsonInvoice.invoice_number);
				message.set("invoiced", jsonInvoice.corporation);
				message.set("invoiced_id", jsonInvoice.corporation_id);
				message.set("invoiced_type", jsonInvoice.corporation_type);
				message.set("document_id", jsonInvoice.document_id);
				message.set("attachments", jsonInvoice.filepath);
				var message_text = "To whom it may concern,";
				message_text += "<br>";
				message_text += "<br>";
				message_text += "Please find attached Invoice #" + jsonInvoice.invoice_number;
				message_text += "<br>";
				message_text += "RE:" + jsonInvoice.case_name;
				message_text += "<br>";
				message_text += "Our Case #:" + jsonInvoice.case_number;
				message_text += "<br>";
				message_text += "<br>";
				message_text += "Thank you very much for your prompt attention to this invoice,";
				message_text += "<br>";
				message_text += login_username;
				
				message.set("message", message_text);
				
				var subject = customer_name + " :: Invoice #" + jsonInvoice.invoice_number;
				message.set("subject", subject);
				object_action = "Send Invoice # " + jsonInvoice.invoice_number;
				break;
			case "reply":
				object_action = "Reply to Message";
				break;
			case "replythread":
			case "replyall":
				object_action = "Reply to Message - All";
				break;
			case "forward":
				object_action = "Forward Message";
				break;
			case "draft":
				object_action = "Edit Draft";
				clearTimeout(draft_timeout_id);
				draft_id = source_message_id;
				blnDraft = true;
				break;
			case "senddocument":
				object_action = "Send Document";
				message.set("source_message_id", "");
				message.set("document_id",arrObject[1]);
				break;
			case "sendstack":
				object_action = "Send Scan";
				message.set("source_message_id", "");
				message.set("document_id",arrObject[1]);
				break;
			case "vservice":
				//get the vservice_id
				var vservice_id = source_message_id;
				source_message_id = -1;
				//we will get email, name from form
				object_action = "Send Kase to " + $("#vservice_name_" + vservice_id).val();
				break;
			case "activity":
				//get the vservice_id
				var activity_id = source_message_id;
				source_message_id = -1;
				current_case_id = arrObject[2];
				//we will get email, name from form
				object_action = "Send Activity to Client";
				break;
			case "partie":
				case_id = current_case_id;
				var partie_id = source_message_id;
				source_message_id = -1;
				//we will get email, name from form
				object_action = "Send Email to Partie";
				break;
		}
	}
	
	if (Array.isArray(object_id)) {
		reaction = "senddocument";
		object_action = "Send Document";
		source_message_id = "";
		message.set("document_id",object_id);
	}
	var check_draft = "";
	if (blnDraft) {
		check_draft = " checked";
	}
	message.set("reaction", reaction);
	
	$("#myModalLabel").html(object_action);
	$("#input_for_checkbox").show();
	/*
	if (typeof document.getElementById("apply_notes") != "undefined") {
		document.getElementById("apply_notes").checked = false;
	}
	*/
	$("#modal_type").val("message");
	
	//<a title="Click to send to All Applicants" class="interoffice" onClick="allApplicants()" style="cursor:pointer"><i style="font-size:15px;color:#FFFFFF; text-decoration:none" class="glyphicon glyphicon-user"></i></a>&nbsp;
	//saveDraft is in save_modal.js
	if (blnSaveDraft) {
		$("#modal_save_holder").html('<span id="save_draft_holder" class="white_text" style="display:none"><input type="checkbox" id="save_draft" title="Check this message as Draft, will not be sent" ' + check_draft + '>&nbsp;Saved as Draft</span><span id="apply_notes_holder" class="white_text">&nbsp;|&nbsp;<input type="checkbox" id="apply_notes">&nbsp;Apply to Notes</span>&nbsp;&nbsp;<button title="Send Message" class="btn btn-sm btn-primary interoffice save" onClick="sendMessage(event)">Send</button>&nbsp;');
	} else {
		$("#modal_save_holder").html('<span id="save_draft_holder" class="white_text" style="display:none"><input type="checkbox" id="save_draft" title="Check this message as Draft, will not be sent" onClick="saveDraftObject(this)" ' + check_draft + '>&nbsp;Save as Draft</span><span id="apply_notes_holder" class="white_text">&nbsp;|&nbsp;<input type="checkbox" id="apply_notes">&nbsp;Apply to Notes</span>&nbsp;&nbsp;<a title="Send Message" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-send" style="color:#000066; font-size:20px">&nbsp;</i></a>');
	}
	//$("#input_for_checkbox").html('&nbsp;');
	switch (reaction) {
		case "reply":
		case "replythread":
		case "replyall":
			var source_message = new Message({message_id: source_message_id});
			//get the source message info
			source_message.fetch({
				success: function (data) {
					var from = data.get("from");
					var arrFrom = data.get("from").split("|");
					if (arrFrom.length==2) {
						from = arrFrom[0];
						data.set("from", arrFrom[0] + " <" + arrFrom[1] + ">")
					}
					
					message.set("message", "<br> <br>" + from + " wrote:<br>" + data.get("message"));
					message.set("subject", "Re:" + data.get("subject"));
					message.set("thread_uuid", data.get("message_uuid"));
					message.set("case_id", data.get("case_id"));
					
					// added by mukesh on 9-5-2023 
					message.set("message_cc", data.get("message_cc"));
					message.set("message_bcc", data.get("message_bcc"));

					$("#myModalBody").html(new message_view({model: message}).render().el);
				}
			});	
			break;
		case "forward":
			var source_message = new Message({message_id: source_message_id});
			//get the source message info
			source_message.fetch({
				success: function (data) {
					message.set("message", "<br> <br>" + data.get("from") + " wrote:<br>" + data.get("message"));
					message.set("subject", "Fwd:" + data.get("subject"));
					message.set("thread_uuid", data.get("thread_uuid"));
					message.set("case_id", data.get("case_id"));
					
					$("#myModalBody").html(new message_view({model: message}).render().el);
				}
			});	
			break;
		case "draft":
			var source_message = new Message({message_id: source_message_id});
			//get the source message info
			source_message.fetch({
				success: function (data) {
					message.set("id", data.get("id"));
					message.set("message_id", data.get("message_id"));
					message.set("message", data.get("message"));
					message.set("subject", data.get("subject"));
					message.set("thread_uuid", data.get("message_uuid"));
					message.set("case_id", data.get("case_id"));
					message.set("callback_date", data.get("callback_date"));
					message.set("from", data.get("from"));
					message.set("message_to", data.get("message_to"));
					message.set("message_cc", data.get("message_cc"));
					message.set("message_bcc", data.get("message_bcc"));
					message.set("priority", data.get("priority"));
					message.set("message_type", data.get("message_type"));
					message.set("dateandtime", data.get("dateandtime"));
					message.set("attachments", data.get("attachments"));
					
					$("#myModalBody").html(new message_view({model: message}).render().el);
					
					$("#myModal4").modal("toggle");
					
					setTimeout(function() {
						//saveDraft(document.getElementById("save_draft"));
						
						$(".interoffice #table_id").val(data.get("message_id"));
						//get the user id
						/*
						var message_user = worker_searches.findWhere({nickname: data.get("message_to")});
						if (typeof message_user != "undefined") {
							$("#message_toInput").tokenInput("add", {
								id: message_user.toJSON().user_id, 
								name: message_user.toJSON().user_name
							});
						} else {
							*/


							var message_to = data.get("message_to");
							
							var arrTo = message_to.split(",");
							var arrEmailAddress = [];
							
							for(var i =0; i < arrTo.length; i++) {
								to = arrTo[i];
								//is it a contact
								var blnEmail = (to.indexOf("@") > -1);
								if (blnEmail) {
									$("#message_toInput").tokenInput("add", {
										id: "", 
										name: to
									});
								}
								arrEmailAddress.push(to);
							}							

							$("#emailaddress_toInput").val(arrEmailAddress.join(","));
							
						//}
					}, 1000);
				}
			});	
			break;
		case "activity":
		//get the activity, build the subject
			var activity_id = object_id[1];
			var activity = new Activity();
			activity.set("id", activity_id);
			activity.fetch({
				success: function (data) {
					//need to build a demographics for the injury id
					var subject = "Information regarding your case with " + customer_name;
					message.set("subject", subject);
					
					//var kase = kases.findWhere({case_id: current_case_id});
					var kase =  new Kase({id: current_case_id});
					kase.fetch({
						success: function (kase) {
							if (kase.toJSON().uuid!="") {
								kases.add(kase);
																
								if (kase.get("applicant_salutation")=="") {
									kase.set("applicant_salutation", "Mr or Ms");
								}
								var themessage = "Hello " + kase.get("applicant_salutation") + " " + kase.get("first_name") + " " + kase.get("last_name") + " this email contains information regarding your case with " + customer_name + ". Please contact us at " + customer_phone + " if you have any questions or concerns regarding your case.";
								var activity_category = data.get("activity_category");
								activity_category = activity_category.replace("K", "C") + " Activity";
								themessage += "<br><br>" + activity_category + ":";
								themessage += "<br><br>" + data.get("activity");
			
								message.set("message", themessage);
								message.set("case_id", current_case_id);
								$("#myModalBody").html(new message_view({model: message}).render().el);
								
								$("#myModal4").modal("toggle");
							} else {
								//case does not exist, get out
								document.location.href = "#";
							}
							return;		
						}
					});
				}
			});
			break;
		case "vservice":
			//get the injury, build the subject
			var injury_id = object_id[2];
			var injury = new Injury({case_id: case_id});
			injury.set("id", injury_id);
			
			injury.fetch({
				success: function (data) {
					var data = data.toJSON();
					data.dates = "";
					if (isDate(data.start_date)) {
						data.dates = moment(data.start_date).format("MM/DD/YYYY");
						if (data.end_date!="") {
							if (isDate(data.end_date)) {
								data.dates += "-" + moment(data.end_date).format("MM/DD/YYYY") + " CT";
							}
						}
					}
					//need to build a demographics for the injury id
					var subject = "Referral from " + customer_name;
					message.set("subject", subject);
					
					var kase = kases.findWhere({case_id: current_case_id});
					var themessage = "<p>" + customer_name + "  is referring this case to your office:</p>" + kase.name() + "<br>DOI: " + data.dates + "<br>ADJ #:" + data.adj_number;
					message.set("message", themessage);
					
					$("#myModalBody").html(new message_view({model: message}).render().el);
					
					//because we're a vservice request
					//fetch the specialties, and populate
					var specialties = new SpecialtyCollection();
					specialties.fetch({
						success: function (specialties) {
							$("#follow_up_holder").hide();
							$("#priority_holder").html("Doctor");
							$("#priority_holder").css("background", "red");
							
							$("#priorityInput").hide();
							
							//now add the new select
							$("#medical_specialties_holder").html(new medical_specialties_select({collection: specialties}).render().el);
							$("#priority_holder").css("background", "red");
						}
					});
					
				}
			});
			break;
		case "partie":
			//are we dealing with applicant?
			var partie_type = "partie";
			var theid = "";
			if (source_message_id == "applicant") {
				theid = "-1";
			} else {
				theid = object_id[1];
			}
			//var blnApplicant = $("#" + theid).hasClass("compose_applicant");
			var kase = kases.findWhere({case_id: current_case_id});
			if (object_id[1] < 0) {
				if (customer_id == "1033") {
					var parties = new Parties([], { case_id: current_case_id});
					parties.fetch({
						success: function(data) {
							var arrRecipientInfo = [];
							var parties = data.toJSON();
							_.each( parties, function(partie) {
								if (partie.email!="" && partie.email!=null) {
									arrRecipientInfo.push(partie.email);
								}
							});
							message.set("recipient", arrRecipientInfo.join(";"));
							message.set("subject", kase.name());
							$("#myModalBody").html(new message_view({model: message}).render().el);
							
							$('#myModal4').modal('show');	
						}
					});
				}
			} else {
				message.set("recipient", $("#partie_" + theid).attr("value"));
				message.set("subject", kase.name());
				
				if (theid=="applicant") {
					 message.set("source_message_id", 1)
				}
				$("#myModalBody").html(new message_view({model: message}).render().el);
				
			}
			break;
		default:
			$("#myModalBody").html(new message_view({model: message}).render().el);
			$("#myModal4").modal("toggle");
	}
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	setTimeout(function() {
		//$('#myModal4 .modal-dialog').css('top', '40%');
		$(".token-input-dropdown-facebook").css("width", "380px"); 
		//$(".token-input-dropdown-facebook").parent().css("width", "251px");
		//$(".token-input-dropdown-facebook").parent().parent().css("width", "251px");
		
		$("#token-input-case_fileInput").css("width", "380px"); 
		$("#token-input-case_fileInput").parent().css("width", "380px");
		$("#token-input-case_fileInput").parent().parent().css("width", "380px");
		
		$("#token-input-message_ccInput").css("width", "100%"); 
		$("#token-input-message_ccInput").parent().css("width", "100%");
		$("#token-input-message_ccInput").parent().parent().css("width", "100%");
		
		$("#token-input-message_toInput").css("width", "380px"); 
		$("#token-input-message_toInput").parent().css("width", "380px");
		$("#token-input-message_toInput").parent().parent().css("width", "380px");
		
		$("#token-input-message_bccInput").css("width", "100%"); 
		$("#token-input-message_bccInput").parent().css("width", "100%");
		$("#token-input-message_bccInput").parent().parent().css("width", "100%");
		
		//if it's a vservice
		if (reaction=="vservice") {
			if ($("#vservice_email_" + vservice_id).length > 0) {
				$("#message_toInput").tokenInput("add", {
					id: $("#vservice_email_" + vservice_id).html(), 
					name: $("#vservice_email_" + vservice_id).html(),
					tokenLimit:1
				});
			}
			
		}
		
		if (reaction=="activity") {
			var kase = kases.findWhere({case_id: current_case_id});
			
			$("#message_toInput").tokenInput("add", {
				id: kase.get("applicant_id"), 
				name: kase.get("applicant_email"),
				tokenLimit:1
			});
		}
	}, 700);
	
	// Changes by jay on march-1
	// $("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("width", "750px");

	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
}
function composeExam(object_id, corp_id, case_id, document_id, document_json) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var exam_id = "-1";
	var blnToggle = false;
	
	if (typeof object_id != "undefined") {
		object_id = object_id.split("_");
		if (object_id.length > 2) {
			exam_id = object_id[object_id.length - 2];
		}
		if (object_id.length > 1) {
			corp_id = object_id[1];
		}
	}
	//are we replying, replyingall, forwarding
	var object_action = "New Exam";
	if (typeof case_id == "undefined") {
		case_id = current_case_id;
	} 
	if (typeof document_id == "undefined") {
		document_id = "";
	} else {
		//attaching document, need to toggle
		blnToggle = true;
	}
	if (typeof document_json == "undefined") {
		document_json = "";
	}
	var exam = new Exam({id: exam_id});
	$("#modal_type").val("exam");
	if (exam_id < 0) {
		exam.set("case_id", case_id);
		exam.set("corp_id", corp_id);
		exam.set("document_id", document_id);
		
		if (document_id != "") {
			exam.set("document_name", document_json.document_name);
			exam.set("comments", document_json.description_html);
		}
		exam.set("holder", "myModalBody");
		$("#myModalLabel").html(object_action);
		$("#input_for_checkbox").show();
		if (blnPiReady) { 
			//$("#modal_billing_holder").html(billing_dropdown_template);
		}
		$("#modal_save_holder").html('<a title="Save Exam" class="exam_view save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
		//$("#input_for_checkbox").html('&nbsp;');
		$("#myModalBody").html(new exam_view({model: exam}).render().el);
		
		if (blnToggle) {
			$("#myModal4").modal("toggle");
		}
	} else {
		object_action = "Edit Exam";
		$("#modal_save_holder").html('<a title="Save Exam" class="exam_view save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
		exam.fetch({
			success: function (exam) {
				exam.set("holder", "myModalBody");
				$("#myModalBody").html(new exam_view({model: exam}).render().el);
				$("#myModalLabel").html(object_action);
			}
		});
	}
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
}
function composeClientMessage(object_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	if (typeof object_id == "undefined") {
		object_id = "";
	}
	
	//are we replying, replyingall, forwarding
	var reaction = "";
	var object_action = "New Marketing Message";
	var message = new Message({message_id: -1, case_id: -1});
	message.set("source_message_id", object_id);
	//process the root if any
	if (object_id != "") {
		object_id = object_id.split("_");
		reaction = object_id[0];
		source_message_id = object_id[1];
		message.set("document_id", "");
		switch (reaction) {
			case "reply":
				object_action = "Reply to Message";
				break;
			case "replyall":
				object_action = "Reply to Message - All";
				break;
			case "forward":
				object_action = "Forward Message";
				break;
			case "senddocument":
				object_action = "Send Document";
				message.set("source_message_id", "");
				message.set("document_id",object_id[1]);
				break;
		}
	}
	message.set("reaction", reaction);
	
	$("#myModalLabel").html(object_action);
	$("#input_for_checkbox").show();
	/*
	if (typeof document.getElementById("apply_notes") != "undefined") {
		document.getElementById("apply_notes").checked = false;
	}
	*/
	$("#modal_type").val("message");
	$("#modal_save_holder").html('<a title="Send Message" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-send" style="color:#000066; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#myModalBody").html(new message_view({model: message}).render().el);
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	setTimeout(function() {
		//$('#myModal4 .modal-dialog').css('top', '37%');
		$("#token-input-message_toInput").css("width", "388px"); 
		$("#token-input-message_toInput").parent().css("width", "388px");
		$("#token-input-message_toInput").parent().parent().css("width", "388px");
		$("#select_all_holder").css("margin-right", "50px");
		$("#select_all_holder").show();
		
		$("#token-input-message_ccInput").css("width", "225px"); 
		$("#token-input-message_ccInput").parent().css("width", "225px");
		$("#token-input-message_ccInput").parent().parent().css("width", "225px");
		
		$("#token-input-message_bccInput").css("width", "225px"); 
		$("#token-input-message_bccInput").parent().css("width", "225px");
		$("#token-input-message_bccInput").parent().parent().css("width", "225px");
	}, 700);
	$("#messageInput").cleditor({
		width:550,
		height: 150,
		controls:     // controls to add to the toolbar
				  "bold italic underline | font size " +
				  "style | color highlight"
	});
	
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
}
function composeTask(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var task_id = -1;
	if (typeof element_id == "undefined") {
		task_id = -1;
		task_case_id = current_case_id;
	} else {
		var partieArray = element_id.split("_");
		task_id = partieArray[2]; 
		task_case_id = partieArray[3]; 
	}
	var task = new Task({task_id: task_id, case_id: task_case_id});
	if (task_id < 1) {
		$("#myModalLabel").html("New Task");
		task.set("from", login_username);
		//task.set("task_title", "Task Assigned By " + login_username);
		task.set("task_dateandtime", moment().format('MM/DD/YYYY h:mm a'));
		task.set("case_id", task_case_id);	
		task.set("holder", "myModalBody");	
		$("#myModalBody").html(new task_view({model: task}).render().el);
		
		if (document.location.hash=="#intake") {
			$("#myModal4").modal("toggle");
		}
	} else {
		var new_task = new Task({task_id: task_id, case_id: task_case_id});
		new_task.fetch({
			success: function (data) {
				data.set("task_id", data.id);
				data.set("case_id", "");
				var task_dateandtime = data.get("task_dateandtime");
				if (task_dateandtime!="" && task_dateandtime!="0000-00-00 00:00:00") {
					task_dateandtime = moment(task_dateandtime).format("MM/DD/YYYY h:mma");
				} else {
					task_dateandtime = "";
				}
				data.set("task_dateandtime", task_dateandtime);
				var end_date = data.get("end_date");
				if (end_date!="" && end_date!="0000-00-00 00:00:00") {
					end_date = moment(end_date).format("MM/DD/YYYY");
				} else {
					end_date = "";
				}
				data.set("end_date", end_date);
				
				var callback_date = data.get("callback_date");
				if (callback_date!="" && callback_date!="0000-00-00 00:00:00") {
					callback_date = moment(callback_date).format("MM/DD/YYYY h:mma");
				} else {
					callback_date = "";
				}
				data.set("callback_date", callback_date);
				data.set("holder", "myModalBody");	
				$("#myModalBody").html(new task_view({model: data}).render().el);
				$("#myModalLabel").html("Edit Task - ID " + data.get("task_id"));
			}
		});
	}
	
	$("#input_for_checkbox").hide();
	
	
	$("#modal_type").val("task");
	
	$("#modal_save_holder").html('<a title="Save Task" class="task save" onClick="saveModal()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	var theme = {theme: "task"};
	$(".task #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "1020px");
	
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
}


function composeTaskPane(element_id) {
	var task_id = -1;
	var task_case_id = -1;
	if (typeof element_id == "undefined") {
		task_id = -1;
		task_case_id = current_case_id;
	} else {
		var partieArray = element_id.split("_");
		if (element_id!="compose_task") {
			task_id = partieArray[2]; 
			task_case_id = partieArray[3]; 
		}
	}
	if (task_case_id < 0) {
		//maybe a kase task
		if (document.location.hash.indexOf("#tasks/")==0) {
			var arrHash = document.location.hash.split("/");
			task_case_id = arrHash[arrHash.length - 1];
		}
	}
	var task = new Task({task_id: task_id, case_id: task_case_id});
	var the_container = "preview_pane";
	if (blnUploadDash) {
		if (document.location.hash.indexOf("#kases/")==0 || document.location.hash.indexOf("#kase/")==0 || document.location.hash.indexOf("#parties/")==0) {
			the_container = "dashboard_right_pane";
			$("#bodyparts_warning").hide();
			
			var new_width = "420px";
			$("#dashboard_right_pane").css("width", new_width);
			$("#dashboard_right_pane").fadeIn(function() {
				
			});
		}
	}
	if (task_id < 1) {
		task.set("from", login_username);
		//task.set("task_title", "Task Assigned By " + login_username);
		task.set("task_dateandtime", moment().format('MM/DD/YYYY h:mm a'));
		task.set("case_id", task_case_id);	
		task.set("holder", the_container);	
		
		$("#" + the_container).html(new task_view_pane({model: task}).render().el);
		$("#preview_title").html("<span id='myModalLabel'>New Task</span>");
	} else {
		var new_task = new Task({task_id: task_id, case_id: task_case_id});
		new_task.fetch({
			success: function (data) {
				data.set("task_id", data.id);
				data.set("case_id", "");
				var task_dateandtime = data.get("task_dateandtime");
				if (task_dateandtime!="" && task_dateandtime!="0000-00-00 00:00:00") {
					task_dateandtime = moment(task_dateandtime).format("MM/DD/YYYY");
				} else {
					task_dateandtime = "";
				}
				data.set("task_dateandtime", task_dateandtime);
				var end_date = data.get("end_date");
				if (end_date!="" && end_date!="0000-00-00 00:00:00") {
					end_date = moment(end_date).format("MM/DD/YYYY");
				} else {
					end_date = "";
				}
				data.set("end_date", end_date);
				
				var callback_date = data.get("callback_date");
				if (callback_date!="" && callback_date!="0000-00-00 00:00:00") {
					callback_date = moment(callback_date).format("MM/DD/YYYY h:mma");
				} else {
					callback_date = "";
				}
				data.set("callback_date", callback_date);
				data.set("holder", the_container);	
				$("#" + the_container).html(new task_view_pane({model: data}).render().el);
				$("#preview_title").html("<span id='myModalLabel'>Edit Task</span> - ID " + data.get("task_id"));
			}
		});
	}
	
	$("#input_for_checkbox").hide();
	
	setTimeout(function() {
		var theme = {theme: "task"};
		$(".task #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
		$("#preview_title").prepend('<div style="float:right"><a title="Save Task" class="interoffice save" onClick="saveModal()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a><input type="hidden" id="modal_type" value="task"></div>');
		$("#preview_title").prepend('<div style="float:right"><a onClick="hidePreviewPane()" title="Close Task" class="interoffice white_text" id="close_note" style="cursor:pointer; margin-right:10px">&times;</a></div>');
	}, 1000);
}

function hideDelete() {
	if ($("#deleteModal").hasClass("in")) {
		$("#deleteModal").modal("toggle");
	}
}
function composeDelete(element_id, table_name) {
	$("#deleteModalLabel").html("Confirm Delete Request");	
	$("#deleteModal #delete").html('YES');
	var msg = "Are you sure you want to delete " + table_name.replaceAll("_", " ").capitalizeWords() + " ID " + element_id + "?";
	if (table_name=="kinvoice") {
		var invoice_number = $("#invoice_number_" + element_id).html();
		msg = "Are you sure you want to delete Invoice " + invoice_number + "?<div style='margin-top:15px; margin-bottom:15px; background:black'><span style='font-weight:bold'>Note:</span> All associated Billing Items will return to 'To Invoice' status</div>";
	}
	$("#deleteModalBody").html(msg + "<div style='padding:5px; text-align:center'><a id='delete' href='javascript:deleteElement(event, \"" + element_id + "\", \"" + table_name + "\")' class='delete_yes white_icon' style='cursor:pointer'>YES</a></div><div style='padding:5px; text-align:center'><a href='javascript:hideDelete()' class='delete_no white_icon' style='cursor:pointer'>NO</a></div>");
	
	$("#input_for_checkbox").hide();
	
	
	$("#modal_type").val("delete");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Delete" class="delete" onClick="deleteModal()" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FA1616; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_edit_header_delete.png')");
	$("#deleteModalBody").css("background", "url('img/glass_edit_header_delete.png')");
	$(".modal-footer").css("background", "url('img/glass_edit_header_delete.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#deleteModal .modal-dialog").css("width", "500px");
	$("#deleteModal .modal-dialog").css("background", "url('img/glass_edit_header_delete.png')");
	$(".modal-content").css("background", "url('img/glass_edit_header_delete.png')");
	$("#deleteModal").modal("toggle");
}
function composeImportComplete(element_id, table_name) {
	$("#deleteModalLabel").html("Confirm Complete Request");	
	$("#deleteModal #delete").html('YES');
	var msg = "Are you sure you want to mark " + table_name.replaceAll("_", " ").capitalizeWords() + " ID " + element_id + " as completed?";
	
	$("#deleteModalBody").html(msg + "<div style='padding:5px; text-align:center'><a id='delete' href='javascript:markAsCompleted(\"" + element_id + "\")' class='delete_yes white_icon' style='cursor:pointer'>YES</a></div><div style='padding:5px; text-align:center'><a href='javascript:hideDelete()' class='delete_no white_icon' style='cursor:pointer'>NO</a></div>");
	
	$("#modal_type").val("mark_completed");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_dark.png')");
	$("#deleteModalBody").css("background", "url('img/glass_dark.png')");
	$(".modal-footer").css("background", "url('img/glass_dark.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#deleteModal .modal-dialog").css("width", "500px");
	$("#deleteModal .modal-dialog").css("background", "url('img/glass_dark.png')");
	$(".modal-content").css("background", "url('img/glass_dark.png')");
	$("#deleteModal").modal("toggle");
}
function composeTransferTask(element_id, table_name) {
	$("#myModalLabel").html("Transfer Tasks");	
	var task = new Task({ids: element_id});
	task.set("holder", "myModalBody");
	$("#myModalBody").html(new bulk_task_transfer_view({model: task}).render().el);
	
	$("#input_for_checkbox").hide();
	
	$("#modal_type").val("transfer_task");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveTransferTaskModal()" style="cursor:pointer"><i class="glyphicon glyphicon-ok" style="color:#BFFF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_info.png')");
	$("#myModalBody").css("background", "url('img/glass_info.png')");
	$(".modal-footer").css("background", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "580px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_info.png')");
	$(".modal-content").css("background", "url('img/glass_info.png')");
	$("#myModal4").modal("toggle");
}
function composeTransferKase(element_id, table_name) {
	$("#myModalLabel").html("Transfer Kases");	
	var kase = new Kase({ids: element_id});
	kase.set("holder", "myModalBody");
	$("#myModalBody").html(new bulk_kase_transfer_view({model: kase}).render().el);
	
	$("#modal_type").val("transfer_kase");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveTransferKaseModal()" style="cursor:pointer"><i class="glyphicon glyphicon-ok" style="color:#BFFF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_info.png')");
	$("#myModalBody").css("background", "url('img/glass_info.png')");
	$(".modal-footer").css("background", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "680px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_info.png')");
	$(".modal-content").css("background", "url('img/glass_info.png')");
	$("#myModal4").modal("toggle");
}
function composeDateChange(element_id, table_name) {
	$("#myModalLabel").html("Change Date");	
	var task = new Task({ids: element_id});
	task.set("holder", "myModalBody");
	$("#myModalBody").html(new bulk_date_change_view({model: task}).render().el);
	
	$("#input_for_checkbox").hide();
	
	$("#modal_type").val("date_change");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveBulkDateChangeModal()" style="cursor:pointer"><i class="glyphicon glyphicon-ok" style="color:#BFFF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_info.png')");
	$("#myModalBody").css("background", "url('img/glass_info.png')");
	$(".modal-footer").css("background", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "500px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_info.png')");
	$(".modal-content").css("background", "url('img/glass_info.png')");
	$("#myModal4").modal("toggle");
}
function composeInvoice(element_id) {
	var arrID = element_id.split("_");
	var kinvoice_id = arrID[arrID.length - 1];
	var document_id = arrID[arrID.length - 2];
	$("#myModalBody").html("");
	var kinvoice_items = new KInvoiceItemsCollection({"kinvoice_id": kinvoice_id});	//, "document_id": document_id
	kinvoice_items.fetch({
		success: function (data) {			
			var mymodel = new Backbone.Model();
			mymodel.set("holder", "myModalBody");
			mymodel.set("document_id", document_id);
			
			if (data.length > 0) {
				$("#myModalLabel").html("Edit Invoice Template");
				mymodel.set("template_name", data.toJSON()[0].template_name);
				mymodel.set("hourly_rate", data.toJSON()[0].hourly_rate);
			} else {
				$("#myModalLabel").html("Create Invoice Template");
				mymodel.set("template_name", "");
				mymodel.set("hourly_rate", 0);
			}
			mymodel.set("kinvoice_id", kinvoice_id);
			$("#myModalBody").html(new kinvoice_items_listing({collection: data, model: mymodel}).render().el);
			
			$("#modal_type").val("kinvoice_items");
			var modal_type = $("#modal_type").val();
			//<a title="Save" class="save" onClick="saveInvoiceItemsModal()" style="cursor:pointer"><i class="glyphicon glyphicon-ok" style="color:#BFFF00; font-size:20px">&nbsp;</i></a>
			$("#modal_save_holder").html('');
			//$("#input_for_checkbox").html('&nbsp;');
			$(".modal-header").css("background", "url('img/glass_dark.png')");
			$("#myModalBody").css("background", "url('img/glass_dark.png')");
			$(".modal-footer").css("background", "url('img/glass_dark.png')");
			$(".modal-body").css("overflow-x", "hidden");
			$("#myModal4 .modal-dialog").css("width", "700px");
			$("#myModal4 .modal-dialog").css("background", "url('img/glass_dark.png')");
			$(".modal-content").css("background", "url('img/glass_dark.png')");
			$("#myModal4").modal("toggle");
		}
	});
}
function composeDateChangeEvent(element_id, table_name) {
	$("#myModalLabel").html("Change Date");	
	var occurence = new Occurence({ids: element_id});
	$("#myModalBody").html(new bulk_date_change_view({model: occurence}).render().el);
	
	$("#input_for_checkbox").hide();
	
	$("#modal_type").val("date_change");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveBulkDateChangeEventModal()" style="cursor:pointer"><i class="glyphicon glyphicon-ok" style="color:#BFFF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_info.png')");
	$("#myModalBody").css("background", "url('img/glass_info.png')");
	$(".modal-footer").css("background", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "500px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_info.png')");
	$(".modal-content").css("background", "url('img/glass_info.png')");
	$("#myModal4").modal("toggle");
	//$("#myModal4").attr('data-easein', 'perspectiveUpIn');
	//var open = $("#myModal4").attr('data-easein');
	//$('#myModal4 .modal-dialog').velocity('callout.' + open);
	//$("#myModal4").modal("toggle");

}
function composeEmailAssignBulk(thread_ids) {
	$("#modal_save_holder").show();
	$("#gifsave").hide();
	var webmail = new Webmail({ids: "", thread_id: thread_ids});
	webmail.set("holder", "myModalBody");
	webmail.set("message_attach", "");
	webmail.set("ids", "");
	$("#modal_save_holder").show();
	$("#gifsave").hide();
	
	$("#myModalBody").html(new bulk_webmail_assign_view({model: webmail}).render().el);
	$("#input_for_checkbox").hide();
	$("#modal_type").val("assign");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveBulkEmailAssignModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF66; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_dark.png')");
	$("#myModalBody").css("background", "url('img/glass_dark.png')");
	$(".modal-footer").css("background", "url('img/glass_dark.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_dark.png')");
	$(".modal-content").css("background", "url('img/glass_dark.png')");
	$("#myModal4").modal("toggle");
}
function composeEmailAssign(element_id, table_name, thread_id) {
	if(typeof thread_id == "undefined") {
		thread_id = -1;
	}
	
	var webmail = new Webmail({ids: element_id});
	webmail.set("holder", "myModalBody");
	if (thread_id > 0) {
		$("#myModalLabel").html("Assign Thread " + thread_id);
		var message_attach = $("#attach_link_" + thread_id).html();
		webmail.set("thread_id", thread_id);
	} else {
		$("#myModalLabel").html("Assign Email " + element_id);
		var message_attach = $("#attach_link_" + element_id).html();
	}
	if (typeof message_attach == "undefined") {
		message_attach = "";
	}
	message_attach = message_attach.replaceAll('<a ', '<a style="color:white;text-decoration:underline" ');
	message_attach = message_attach.replaceAll('"kase_attach" ', '"kase_attach_assign"');
	if (message_attach.length > 0) {
		if (message_attach.split("<br>").length > 2) {
			message_attach = "<input type='checkbox' id='select_all_attachments'>&nbsp;Select All<br>" + message_attach;
		}
	}
	webmail.set("message_attach", message_attach);
	
	$("#modal_save_holder").show();
	$("#gifsave").hide();
	
	$("#myModalBody").html(new bulk_webmail_assign_view({model: webmail}).render().el);
	$("#input_for_checkbox").hide();
	$("#modal_type").val("assign");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveBulkEmailAssignModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF66; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_dark.png')");
	$("#myModalBody").css("background", "url('img/glass_dark.png')");
	$(".modal-footer").css("background", "url('img/glass_dark.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_dark.png')");
	$(".modal-content").css("background", "url('img/glass_dark.png')");
	$("#myModal4").modal("toggle");
}
var blnMessageAssigning = false;
function composeMessageAssign(element_id) {
	if (blnMessageAssigning) {
		return;
	}
	blnMessageAssigning = true;
	var webmail = new Message({message_id: element_id});
	webmail.set("holder", "myModalBody");
	$("#myModalLabel").html("Assign Email " + element_id);
	var message_attach = $("#attach_link_" + element_id).html();
	if (typeof message_attach == "undefined") {
		message_attach = "";
	}
	message_attach = message_attach.replaceAll('<a ', '<a style="color:white;text-decoration:underline" ');
	message_attach = message_attach.replaceAll('"kase_attach" ', '"kase_attach_assign"');
	if (message_attach.length > 0) {
		if (message_attach.split("<br>").length > 2) {
			message_attach = "<input type='checkbox' id='select_all_attachments'>&nbsp;Select All<br>" + message_attach;
		}
	}
	webmail.set("message_attach", message_attach);
	
	$("#modal_save_holder").show();
	$("#gifsave").hide();
	
	$("#myModalBody").html(new webmail_assign_view({model: webmail}).render().el);
	$("#input_for_checkbox").hide();
	$("#modal_type").val("assign");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveSingleEmailAssignModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF66; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_dark.png')");
	$("#myModalBody").css("background", "url('img/glass_dark.png')");
	$(".modal-footer").css("background", "url('img/glass_dark.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_dark.png')");
	$(".modal-content").css("background", "url('img/glass_dark.png')");
	$("#myModal4").modal("toggle");
}
function composeImportAssign(element_id, table_name) {
	$("#myModalLabel").html("Assign " + element_id);
	//console.log(element_id);
	var document_import = new Document({ids: element_id});
	document_import.set("holder", "myModalBody");
	$("#myModalBody").html(new bulk_import_assign_view({model: document_import}).render().el);
	//setTimeout(function() {
		//$("#case_idInput").tokenInput("api/kases/tokeninput", theme);
	//}, 2500);
	$("#input_for_checkbox").hide();
	
	$("#modal_type").val("assign");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveBulkImportAssignModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF66; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_edit_header_assign.png')");
	$("#myModalBody").css("background", "url('img/glass_edit_header_assign.png')");
	$(".modal-footer").css("background", "url('img/glass_edit_header_assign.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "650px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_edit_header_assign.png')");
	$(".modal-content").css("background", "url('img/glass_edit_header_assign.png')");
	$("#myModal4").modal("toggle");
}
function composeInvoiceAssign(case_id) {
	if (typeof case_id=="undefined") {
		case_id = "";
	}
	$("#myModalLabel").html("Select Invoice Kase");
	//console.log(element_id);
	var kinvoice = new KInvoice();
	kinvoice.set("case_id", case_id);
	kinvoice.set("ids", "");
	kinvoice.set("invoice", true);
	kinvoice.set("holder", "myModalBody");
	$("#myModalBody").html(new bulk_import_assign_view({model: kinvoice}).render().el);
	//setTimeout(function() {
		//$("#case_idInput").tokenInput("api/kases/tokeninput", theme);
	//}, 2500);
	$("#input_for_checkbox").hide();
	
	$("#modal_type").val("invoice");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveInvoiceAssign()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF66; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_dark.png')");
	$("#myModalBody").css("background", "url('img/glass_dark.png')");
	$(".modal-footer").css("background", "url('img/glass_dark.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "650px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_dark.png')");
	$(".modal-content").css("background", "url('img/glass_dark.png')");
	$("#myModal4").modal("toggle");
}
function composeMedicalBilling(element_id) {
	var medicalbilling_id = -1;
	medicalbilling_case_id = current_case_id;
	if (typeof ledger == "undefined") {
		ledger = "";
	} 
	
	if (typeof element_id == "undefined") {
		medicalbilling_id = -1;
	} else {
		var arrId = element_id.split("_");
		medicalbilling_id = arrId[arrId.length - 1]; 
	}
	var medicalbilling = new MedicalBilling({medicalbilling_id: medicalbilling_id});
	medicalbilling.set("holder", "#myModalBody");
	
	$("#gifsave").hide();
	if (medicalbilling_id < 1) {
		medicalbilling.set("corporation_id", document.location.hash.split("/")[2]);
		//medicalbilling.set("medicalbilling_title", "Task Assigned By " + login_username);
		medicalbilling.set("case_id", medicalbilling_case_id);	
		medicalbilling.set("ledger", ledger);	
		var bill_date = moment().format("YYYY-MM-DD");
		medicalbilling.set("bill_date", bill_date);	
		var title = "Medical Billing";
		$("#myModalLabel").html("New " + title);
		medicalbilling.set("title", title);	
		$("#myModalBody").html(new medical_billing_view({model: medicalbilling}).render().el);
	} else {
		medicalbilling.fetch({
			success: function (data) {
				data.set("medicalbilling_id", data.id);
				$("#myModalBody").html(new medical_billing_view({model: data}).render().el);
				$("#myModalLabel").html("Edit Medical Billing");
			}
		});
	}
	
	$("#modal_type").val("medicalbilling");
	$("#modal_save_holder").html('<a title="Save Billing" class="medicalbilling save" onClick="saveMedicalBillingModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a><span id="billing_gifsave" style="display:none; opacity:50%"><i class="icon-spin4 animate-spin"></i></span>');
	$("#modal_save_holder").show();
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "720px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	$("#myModal4").modal("toggle");
}
function composeNewNote(element_id) {
	getCurrentCaseID();
	
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var partieArray = element_id.split("_");
	var partie_array_type = partieArray[1];
	var case_id = current_case_id;
	var notes_id = -1;
	var injury_id = "";
	/*
	if (partieArray.length == 4 && partieArray[1] != "injurynote" && partieArray[1] != "bodyparts") {
		notes_id = partieArray[3];
	}
	*/
	if ((partieArray[0] == "open" || partieArray[0] == "quick") && partieArray[1] == "note") {
		notes_id = partieArray[3];
		case_id = partieArray[2];
	}
	/*
	if (partie_array_type=="note") {
		partie_array_type = "";
		if (partieArray.length > 2) {
			case_id = partieArray[2];
			if (partieArray.length > 3) {
				notes_id = partieArray[3];
			}
		}
	}
	*/
	var partie_array_id = "";
	var blnPartyNote = false;
	if (partieArray[0] != "compose") {
		if (partie_array_type=="injurynote" || partieArray[1] != "bodyparts") {
			if (partieArray.length > 2) {
				//case_id = partieArray[2];
				injury_id = partieArray[3];
			}
		}
	} else {
		if (element_id != "compose_quick") {
			if (partie_array_type!="injurynote" && partie_array_type!="settlement") {
				partie_array_id = partieArray[partieArray.length - 1];
				//put together the partie_array_type
				var arrPartieType = [];
				for(var i = 1; i < (partieArray.length - 1); i++) {
					arrPartieType.push(partieArray[i]);
				}
				partie_array_type = arrPartieType.join("_");
				blnPartyNote = true;
			} else {
				injury_id = partieArray[partieArray.length - 1];
			}
		}
	}
	
	var modal_title = "";
	if (partieArray.length > 2 && partie_array_type != "" && partie_array_type != "injurynote" && partie_array_type != "bodyparts") {
		//modal_title = partieArray[1].capitalize();
		modal_title = partieArray[1].replace("~", " ").capitalizeWords();
	}
	if (element_id == "compose_quick" || element_id.indexOf("quick_note") > -1) {
		modal_title = "Quick";
	}
	if (partieArray[1] == "settlement") {
		modal_title = "Settlement";
	}
	//new note from parties screen
	var blnPartieNotes = (document.location.hash.indexOf("#parties")==0 ||  document.location.hash.indexOf("#settlement/") > - 1);
	
	if (notes_id < 0) {
		if (element_id != "compose_quick" && element_id.indexOf("quick_note") < 0) {
			if (partie_array_id =="") {
				partie_array_id = partieArray[2];
			}
		}
		partie_array_type = partie_array_type.replace("~", "_");
		var new_note = new Backbone.Model({new_note_id: notes_id, partie_array_type: partie_array_type, partie_array_id: partie_array_id, case_id: case_id});
		new_note.set("dateandtime", moment().format('MM/DD/YYYY h:mm a'));
		new_note.set("entered_by", login_username);
		new_note.set("subject", "");
		new_note.set("case_id", case_id);
		new_note.set("callback_date", "");
		new_note.set("status", "general");
		new_note.set("note", "");
		if (!blnPartyNote) {
			if (injury_id == "") {
				injury_id = partieArray[2];
			}
			if (typeof injury_id=="undefined") {
				injury_id = "";
			}
			new_note.set("partie_array_id", injury_id);
		} else {
			if (typeof partie_array_id=="undefined") {
				partie_array_id = "";
			}
			new_note.set("partie_array_id", partie_array_id);
		}
		var the_container = "myModalBody";
		
		if (blnUseRightPane && !blnChangingKase) {
			if (document.location.hash.indexOf("#kases/")==0 || document.location.hash.indexOf("#kase/")==0 || document.location.hash.indexOf("#parties/")==0) {
				if (!blnPartieNotes) {
					the_container = "dashboard_right_pane";
					$("#bodyparts_warning").hide();
				}
			}
		}
		new_note.set("holder",the_container);
		var label = "New " + modal_title + " Note";
		if (modal_title=="Note") {
			label = "New Note";
		}
		if (blnChangingKase) {
			var kase = kases.findWhere({case_id: case_id});
			var case_number = kase.get("case_number");
			if (case_number=="") {
				case_number = kase.get("file_number");
			}
			label = "Leaving Kase [" + case_number + "] w/o Activity.<br>Do you wish to leave a Note?";
		}
		$("#myModalLabel").html("<span id='form_title_label'>" + label + "</span>");
		
		if (!blnUseRightPane || blnPartieNotes || blnChangingKase) {
			$("#" + the_container).html(new new_note_view({model: new_note}).render().el);
			
			if (blnChangingKase) {
				setTimeout(function() {
					$(".modal-header .close").html("No");
					$(".modal-header .close").css("opacity", 1);
					$(".modal-header .close").css("text-shadow", "none");
					$("#modal_save_holder .save").hide();
					$("#myModalBody").hide();
					$("#modal_save_holder").prepend('<button type="button" id="yes_note" style="background: none;text-shadow: none;color: black;opacity: 1;font-size: 21px;border: none;margin-top: -7px; font-weight: bold" onClick="showNoteModal()" title="Yes, I want to leave a note">Yes</button>&nbsp;&nbsp;&nbsp;&nbsp;');
					
					$(".modal-header .close").on("click", function(event) { 
						//is it no?
						if ($(".modal-header .close").html()=="No") {
							//record failure to write note
							//perform an ajax call to track no_note by current user
							var url = 'api/kase/no_note';
							var case_id = $("#new_note_form #case_id").val();
							
							formValues = "id=" + case_id;
					
							$.ajax({
								url:url,
								type:'POST',
								dataType:"json",
								data: formValues,
								success:function (data) {
									if(data.error) {  // If there is an error, show the error messages
										saveFailed(data.error.text);
									} else {
										//done
									}
								}
							});
						}
					});
				}, 1000);
			}
		}
		console.log("Im here 1 - "+blnUseRightPane+" - "+blnPartieNotes+" - "+blnChangingKase);
		if (blnUseRightPane && !blnPartieNotes && !blnChangingKase) {	
			$("#" + the_container).html(new new_note_pane({model: new_note}).render().el);		
			//set the width
			//var new_width = (window.innerWidth - (275 * 4) - 80);
			var new_width = "420px";
			$("#dashboard_right_pane").css("width", new_width);
			$("#dashboard_right_pane").fadeIn(function() {
				
			});
			
			setTimeout(function() {
				$("#preview_title").html("<span id='form_title_label'>New " + modal_title + " Note</span>");
				$("#preview_title").prepend('<div style="float:right"><a title="Save Note" class="interoffice save" onClick="saveModal()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a><input type="hidden" id="modal_type" value="note"></div>');
				$("#preview_title").prepend('<div style="float:right"><a onClick="hidePreviewPane()" title="Close Note" class="interoffice white_text" id="close_note" style="cursor:pointer; margin-right:10px">&times;</a></div>');
				
				$("#queue").css("width", "390px");
				$("#queue").css("height", "40px");
				$('html, body').animate({scrollTop: 180}, 300);
			}, 1000);
		}
	} else {
		var new_note = new Note({notes_id: notes_id, case_id: case_id});
		new_note.fetch({
			success: function (data) {
				data.set("new_note_id", data.id);
				data.set("partie_array_type", data.get("type"));
				data.set("partie_array_id", "");
				var callback_date = data.get("callback_date");
				if (callback_date!="" && callback_date!="0000-00-00 00:00:00") {
					callback_date = moment(callback_date).format("MM/DD/YY h:mma");
				} else {
					callback_date = "";
				}
				data.set("callback_date", callback_date);
				data.set("holder", "myModalBody");
				$("#myModalBody").html(new new_note_view({model: data}).render().el);
				$("#myModalLabel").html("<span id='form_title_label'>Edit Note - ID " + data.id + '</span>');
			}
		});
	}
	$("#input_for_checkbox").hide();
	if (blnPiReady) { 
		//$("#modal_billing_holder").html(billing_dropdown_template);
	}
	$("#modal_save_holder").html('<a title="Save Note" class="interoffice save" onClick="saveModal()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("note");
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	/*
	setTimeout(function() {
		$('#myModal4 .modal-dialog').css('top', '0px');
		$('#myModal4 .modal-dialog').css('margin-top', '50px')
	}, 700);
	*/
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	
	if (blnPartieNotes) {
		$("#myModal4").modal("toggle");
	}
}
function composeNewMail(element_id){
	var partieArray = element_id.split("_");
	var case_id =  partieArray[2];
	var modalName = '#myModal4';
	$(`${modalName} #gifsave`).hide();
	$(`${modalName} #myModalLabel`).html("<span id='form_title_label'>Drag & Drop Mail</span>");
	$(`${modalName} #modal_type`).val("mail");

	$(`${modalName} .modal-body`).html(`
		<div class="card dropzone-card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Drag & Drop Outlook Email(s)</h5>
                <p style="margin-bottom:10px;">
                    Drop one or more <strong>.msg</strong> or <strong>.eml</strong> files here. They will be saved in database.
                </p>
                <form action="upload.php" class="dropzone" id="emailDropzone" enctype="multipart/form-data">
					<input type='hidden' name='case_id' value="${case_id}">
                    <div class="dz-message" data-dz-message>
                        <span>Drop .msg / .eml files here or click to browse</span>
                    </div>
                </form>
                <div style="margin-top:10px;">
                    <button id="clearAllBtn" class="btn btn-sm btn-outline-secondary">Clear Queue</button>
                </div>
                <div id="outlook-alerts"></div>
            </div>
        </div>	
	`);
	
	$(`${modalName} .modal-header`).css("background-image", "url('img/glass_info.png')");
	$(`${modalName} .modal-header .close`).css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$(`${modalName} #myModalBody`).css("background-image", "url('img/glass_info.png')");
	$(`${modalName} .modal-footer`).css("background-image", "url('img/glass_info.png')");
	$(`${modalName} .modal-body`).css("overflow-x", "hidden"); 
	$(`${modalName} .modal-dialog`).css({"width": "620px", "background-image": "url('img/glass_info.png')"});
	$(`${modalName} .modal-content`).css("background-image", "url('img/glass_info.png')");
	$(`${modalName}`).modal("toggle");

}
function showNoteModal() {
	$("#yes_note").hide();
	$("#modal_save_holder .save").show();
	//$(".modal-header .close").html('<span aria-hidden="true">×</span><span class="sr-only">Close</span>');
	//$(".modal-header .close").css("opacity", "0.2");
	$(".modal-header #form_title_label").html("New Note");
	$("#myModalBody").show();
}
function composeNewNotePane(element_id) {
	if (element_id=="") {
		return;
	}
	//not for modal, for side pane
	var partieArray = element_id.split("_");
	var partie_array_type = partieArray[1];
	if (current_case_id == -1) {
		if (document.location.hash.indexOf("#notes/")==0) {
			var arrHash = document.location.hash.split("/");
			current_case_id = arrHash[arrHash.length - 1];
		}
	}
	var case_id = current_case_id;
	
	var notes_id = -1;
	var injury_id = "";
	
	if (partieArray.length==4) {
		if ((partieArray[0] == "open" || partieArray[0] == "quick") && partieArray[1] == "note") {
			notes_id = partieArray[3];
			case_id = partieArray[2];
		}
	}
	
	var partie_array_id = "";
	var blnPartyNote = false;
	if (partieArray[0] != "compose") {
		if (partie_array_type=="injurynote" || partieArray[1] != "bodyparts") {
			if (partieArray.length > 2) {
				//case_id = partieArray[2];
				injury_id = partieArray[3];
			}
		}
	} else {
		if (partie_array_type!="injurynote") {
			partie_array_id = partieArray[partieArray.length - 1];
			//put together the partie_array_type
			var arrPartieType = [];
			for(var i = 1; i < (partieArray.length - 1); i++) {
				arrPartieType.push(partieArray[i]);
			}
			partie_array_type = arrPartieType.join("_");
			blnPartyNote = true;
		} else {
			injury_id = partieArray[partieArray.length - 1];
		}
	}
	
	var modal_title = "";
	if (partieArray.length > 2 && partie_array_type != "" && partie_array_type != "injurynote" && partie_array_type != "bodyparts") {
		//modal_title = partieArray[1].capitalize();
		modal_title = partieArray[1].replace("~", " ").capitalizeWords();
	}
	
	console.log("Im here 2 - "+notes_id);
	if (notes_id < 0) {
		if (partie_array_id =="") {
			partie_array_id = partieArray[2];
		}
		
		partie_array_type = partie_array_type.replace("~", "_");
		var new_note = new Backbone.Model({new_note_id: notes_id, partie_array_type: partie_array_type, partie_array_id: partie_array_id, case_id: case_id});
		new_note.set("dateandtime", moment().format('MM/DD/YYYY h:mm a'));
		new_note.set("entered_by", login_username);
		new_note.set("subject", "");
		new_note.set("callback_date", "");
		new_note.set("status", "general");
		new_note.set("note", "");
		if (!blnPartyNote) {
			if (injury_id == "") {
				injury_id = partieArray[2];
			}
			new_note.set("partie_array_id", injury_id);
		} else {
			new_note.set("partie_array_id", partie_array_id);
		}
		
		new_note.set("holder", "preview_pane");
		$("#preview_pane").html(new new_note_view({model: new_note}).render().el);
		var preview_title = "New Note";
		if (modal_title!="Note") {
			preview_title = "New " + modal_title + " Note";
		}
		$("#preview_title").html("<span id='form_title_label'>" + preview_title + "</span>");
	} else {
		var new_note = new Note({notes_id: notes_id, case_id: case_id});
		new_note.fetch({
			success: function (data) {
				data.set("new_note_id", data.id);
				data.set("partie_array_type", data.get("type"));
				data.set("partie_array_id", "");
				var callback_date = data.get("callback_date");
				if (callback_date!="" && callback_date!="0000-00-00 00:00:00") {
					callback_date = moment(callback_date).format("MM/DD/YYYY");
				} else {
					callback_date = "";
				}
				data.set("callback_date", callback_date);
				data.set("holder", "preview_pane");
				$("#preview_pane").html(new new_note_view({model: data}).render().el);
				$("#preview_title").html("<span id='form_title_label'>Edit Note - ID " + data.id + "</span>");
			}
		});
	}
	
	setTimeout(function() {
		$("#preview_title").prepend('<div style="float:right" id="save_note_holder"><a title="Save Note" class="interoffice save" onClick="saveModal()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a><span id="note_gifsave" style="display:none; opacity:50%"><i class="icon-spin4 animate-spin"></i></span><input type="hidden" id="modal_type" value="note"></div>');
		$("#preview_title").prepend('<div style="float:right"><a onClick="hidePreviewPane()" title="Close Note" class="interoffice white_text" id="close_note" style="cursor:pointer; margin-right:10px">&times;</a></div>');
	}, 1000);
	/*
	$("#input_for_checkbox").hide();
	if (blnPiReady) { 
		//$("#modal_billing_holder").html(billing_dropdown_template);
	}
	
	$("#modal_save_holder").html('<a title="Save Note" class="interoffice save" onClick="saveModal()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("note");
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	*/
}
function composeNewActivityPane(element_id, blnModal, filters) {
	getCurrentCaseID();
	
	if (typeof blnModal == "undefined") {
		blnModal = false;
	}
	if (typeof filters == "undefined") {
		filters = [];
	}
	if (element_id=="") {
		return;
	}
	var elementArray = element_id.split("_");
	
	var case_id = current_case_id;
	//var activity_id = element_id;
	
	var activity_id = -1;
	if (elementArray.length > 3) {
		activity_id = elementArray[4];
	}
	var activity_uuid = "";
	if (elementArray.length > 2) {
		activity_uuid = elementArray[3];
	}
	
	var holder = "preview_pane";
	if (blnModal) {
		holder = "myModalBody";
	}
	
	var bill = new SingleActivity({ case_id: current_case_id, activity_id: activity_id});
	if (activity_id < 0) {
		
		bill.set("holder", holder);
		bill.set("billing_date", moment().format("MM/DD/YYYY hh:mma"));
		bill.set("filters", filters);
		
		$("#preview_title").html("<span id='myModalLabel'>New Activity</span>");
		
		$("#" + holder).html(new activity_bill_view({model: bill}).render().el);
		
		setTimeout(function() {
			$("#preview_title").prepend('<div style="float:right"><a title="Save Activity" class="interoffice save" onClick="saveActivityBillingFull()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a><input type="hidden" id="modal_type" value=""></div>');
			$("#preview_title").prepend('<div style="float:right"><a onClick="hidePreviewPane()" title="Close Activity" class="interoffice white_text" id="close_activity" style="cursor:pointer; margin-right:10px">&times;</a></div>');
		}, 1000);
		//$("#billing_holder").fadeIn();
	} else {
		bill.fetch({
			success: function(bill) {
				bill.set("holder", holder);
				bill.set("filters", filters);
				//bill = bill.toJSON();
				var billing_date = bill.get("billing_date");
				var activity_date = bill.get("activity_date");
				if (billing_date==null && activity_date!=null && activity_date!="") {
					billing_date = activity_date;
				}
				billing_date = moment(billing_date).format("MM/DD/YYYY hh:mma");
				bill.set("billing_date", billing_date);
				
				var modal_type = bill.get("activity_category");
				/*
				$("#billing_dateInput").val(bill.billing_date);
				//return;
				$("#durationInput").val(bill.duration);
				$("#statusInput").val(bill.billing_status);
				$("#billing_rateInput").val(bill.billing_rate);
				$("#activity_codeInput").val(bill.activity_code);
				$("#descriptionInput").val(bill.description);
				$("#activity_id").val(bill.activity_id);
				$("#table_id").val(bill.activity_id);
				if (bill.activity_id != "") {
					//setTimeout(function() {
						//$("#timekeeperInput").tokenInput("add", {id: bill.timekeeper, name: bill.user_name});
						
					//}, 2000);
				}
				*/
				$("#preview_title").html("<span id='myModalLabel'>Edit Activity</span>");
				$("#" + holder).html(new activity_bill_view({model: bill}).render().el);
				
				setTimeout(function() {
					$("#preview_title").prepend('<div style="float:right"><a title="Save Activity" class="interoffice save" onClick="saveActivityBillingFull()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a><input type="hidden" id="modal_type" value="' + modal_type + '"></div>');
					$("#preview_title").prepend('<div style="float:right"><a onClick="hidePreviewPane()" title="Close Task" class="interoffice white_text" id="close_note" style="cursor:pointer; margin-right:10px">&times;</a></div>');
				}, 1000);
			}
		});
	}
	if (blnModal) {
		$(".modal #myModalLabel").html("Edit Activity");
		
		$(".modal #modal_type").val("activity");
		$("#modal_save_holder").html('<a title="Save Activity" class="check save" onClick="saveActivityBillingFull()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
		$("#modal_save_holder").show();
		
		$(".modal-header").css("background-image", "url('img/glass_edit.png')");
		$("#myModalBody").css("background-image", "url('img/glass_edit.png')");
		$(".modal-footer").css("background-image", "url('img/glass_edit.png')");
		$(".modal-body").css("overflow-x", "hidden");
		
		$("#myModal4 .modal-dialog").css("width", "470px");
		$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit.png')");
		$(".modal-content").css("background-image", "url('img/glass_edit.png')");
		
		$("#myModal4").modal("toggle");
	}
	/*
	$("#input_for_checkbox").hide();
	if (blnPiReady) { 
		//$("#modal_billing_holder").html(billing_dropdown_template);
	}
	
	$("#modal_save_holder").html('<a title="Save Note" class="interoffice save" onClick="saveModal()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a>');

	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("note");
	
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "620px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_info.png')");
	
	$(".modal-content").css("background-image", "url('img/glass_info.png')");
	*/
}
function composeEditDocumentTypes(filter_type) {
	var documentfilters = new DocumentFilters();
	documentfilters.fetch({
		success: function (data) {
			data.set("holder", "myModalBody");
			data.set("filter_type", filter_type);
			$("#modal_save_holder").html('<a onclick="newDocumentType()" style="cursor:pointer" class="white_text">New Filter</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="showHiddenDocumentTypes()" class="white_text" style="cursor:pointer">Show All</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a title="Save Filters" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
			$("#modal_type").val("document_filters");
			$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
			$("#myModalBody").css("background-image", "url('img/glass_info.png')");
			$(".modal-footer").css("background-image", "url('img/glass_info.png')");
			$("#myModalBody").html(new document_filters_listing({model: data}).render().el);
			$("#myModalLabel").html("Manage Document Types");
			$("#myModal4").modal("toggle");
		}
	});
}
function composeEditCaseStatus(status_level) {
	
	var status_indicator = "";
	if (status_level=="") {
		var statusfilters = new StatusFilters();
	} else {
		var statusfilters = new SubStatusFilters();
		status_indicator = "Sub ";
	}
	statusfilters.fetch({
		success: function (data) {
			var mymodel = new Backbone.Model();
			mymodel.set("holder", "myModalBody");
			mymodel.set("status_level", status_level);
			$("#modal_save_holder").html('<a onclick="newKaseStatus()" style="cursor:pointer" class="white_text">Newdd ' + status_indicator + 'Status</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="showHiddenKaseStatuses()" class="white_text" style="cursor:pointer">Show All</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a title="Save Statuses" class="status save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
			$("#modal_type").val("status_filters");
			$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
			$("#myModalBody").css("background-image", "url('img/glass_info.png')");
			$(".modal-footer").css("background-image", "url('img/glass_info.png')");
			$("#myModalBody").html(new kase_status_listing({collection: data, model: mymodel}).render().el);
			$("#myModalLabel").html("Manage " + status_indicator + "Status");
			$("#myModal4").modal("toggle");
		}
	});
}
function composeEditRate(rate_id) {
	
	var status_indicator = "";
	var rate = new KaseRate({id: rate_id});
	
	rate.fetch({
		success: function (mymodel) {
			mymodel.set("holder", "myModalBody");
			//$("#modal_save_holder").html('<a onclick="newFee()" style="cursor:pointer" class="white_text">New Fee</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="showHiddenFee()" class="white_text" style="cursor:pointer">Show All</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a title="Save Fees" class="rate save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
			$("#modal_save_holder").html('');
			$("#modal_type").val("rate");
			$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
			$("#myModalBody").css("background-image", "url('img/glass_info.png')");
			$(".modal-footer").css("background-image", "url('img/glass_info.png')");
			$("#myModalBody").html(new rate_view({model: mymodel}).render().el);
			$("#myModalLabel").html("Manage Schedule of Standard Activity Durations");
			$("#myModal4").modal("toggle");
		}
	});
}
function hidePreviewPane() {
	if ($("#hide_preview_pane").length > 0) {
		$("#hide_preview_pane").trigger("click");
	}
	if ($("#close_activity").length > 0) {
		$("#invoice_activities").show();
		$("#legend_holder").show();
	}
	
	if ($("#note_type_filter_summary").length > 0) {
		setTimeout(function() {
			$("#note_type_filter_summary").fadeIn();
		}, 2000);
	}
}
function showHiddenDocumentTypes() {
	$('.deleted_filter').show();
}
function newDocumentType() {
	//see if there are any new rows alread
	var row_id = $(".document_new_row").length;
	var index = row_id + 1;
	var therow = "<tr style='display:' class='document_new_row'><td class='document_type'><input type='checkbox' value='Y' id='document_type_new_" + index + "' name='document_type_new_" + index + "' checked></td><td class='document_type' style='color:black'><input type='text' placeholder='New Filter' autocomplete='off' id='new_type_" + index + "' name='new_type_" + index + "' /></td></tr>";
	$("#document_filters_table").prepend(therow);
	$("#new_type_" + index).focus();
}
function composeEditNoteTypes() {
	var notefilters = new DocumentFilters();
	notefilters.fetch({
		success: function (data) {
			data.set("holder", "myModalBody");
			$("#modal_save_holder").html('<a onclick="newNoteType()" style="cursor:pointer" class="white_text">New Filter</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="showHiddenNoteTypes()" class="white_text" style="cursor:pointer">Show All</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a title="Save Filters" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
			$("#modal_type").val("note_filters");
			$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
			$("#myModalBody").css("background-image", "url('img/glass_info.png')");
			$(".modal-footer").css("background-image", "url('img/glass_info.png')");
			$("#myModalBody").html(new note_filters_listing({model: data}).render().el);
			$("#myModalLabel").html("Manage Note Filters");
			$("#myModal4").modal("toggle");
		}
	});
}
function composeEditTaskTypes() {
	var task_types = new TaskTypesCollection();
	task_types.fetch({
		success: function (data) {
			var mymodel = new Backbone.Model();
			mymodel.set("holder", "myModalBody");
			
			/*
			$("#modal_save_holder").html('<a onclick="newTaskType()" style="cursor:pointer" class="white_text">New Type</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="showHiddenTaskTypes()" class="white_text" style="cursor:pointer">Show All</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a title="Save Task Types" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
			*/
			$("#modal_save_holder").html('');
			$("#modal_type").val("task_type");
			$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
			$("#myModalBody").css("background-image", "url('img/glass_info.png')");
			$(".modal-footer").css("background-image", "url('img/glass_info.png')");
			$("#myModalBody").html(new task_type_listing({collection: data, model: mymodel}).render().el);
			$(".modal-title#myModalLabel").html("Manage Task Types");
			$("#myModal4").modal("toggle");
		}
	});
}
function showHiddenNoteTypes() {
	$('.deleted_filter').show();
}
function showHiddenTaskTypes() {
	$('.deleted_type').show();
}
function newNoteType() {
	//see if there are any new rows alread
	var row_id = $(".document_new_row").length;
	var index = row_id + 1;
	var therow = "<tr style='display:' class='document_new_row'><td class='document_type'><input type='checkbox' value='Y' id='document_type_new_" + index + "' name='document_type_new_" + index + "' checked></td><td class='document_type' style='color:black'><input type='text' placeholder='New Filter' autocomplete='off' id='new_type_" + index + "' name='new_type_" + index + "' /></td></tr>";
	$("#document_filters_table").prepend(therow);
	$("#new_type_" + index).focus();
}
function newTaskType () {
	//see if there are any new rows alread
	var row_id = $(".active_filter").length;
	var index = row_id + 1;
	var therow = "<tr style='display:' class='task_type_new_row'><td class='task_type'><input type='checkbox' value='Y' id='task_type_new_" + index + "' name='task_type_new_" + index + "' checked></td><td class='task_type' style='color:black'><input type='text' placeholder='New Type' autocomplete='off' id='new_type_" + index + "' name='new_type_" + index + "' /></td></tr>";
	$("#task_type_table").prepend(therow);
	$("#new_type_" + index).focus();
}
function composeNewRx(element_id) {
	var arrID = element_id.split("_");
	var person_id = arrID[arrID.length - 1];
	
	var kase = kases.findWhere({case_id: current_case_id});
	
	var rx_id = -1;
	//edit or new
	if (arrID[0]=="edit") {
		rx_id = arrID[2];
	}
	
	
	$("#modal_save_holder").html('<a title="Save Rx" class="interoffice save" onClick="saveModal()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a>');
	$("#modal_type").val("rx");
	$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
	$("#myModalBody").css("background-image", "url('img/glass_info.png')");
	$(".modal-footer").css("background-image", "url('img/glass_info.png')");
	
	if (rx_id < 0) {
		var mymodel = new Rx();
		mymodel.set("holder", "myModalBody");
		mymodel.set("rx_id", rx_id);
		mymodel.set("person_id", person_id);
		mymodel.set("first_name", kase.get("first_name"));
		mymodel.set("last_name", kase.get("last_name"));
		mymodel.set("full_name", kase.get("full_name"));
		$("#myModalBody").html(new rx_view({model: mymodel}).render().el);
	} else {
		//get the rx details, then load the view
		var rx = new Rx({id: rx_id});
		rx.fetch({
			success: function (mymodel) {
				mymodel.set("holder", "myModalBody");
				mymodel.set("person_id", person_id);
				mymodel.set("first_name", kase.get("first_name"));
				mymodel.set("last_name", kase.get("last_name"));
				mymodel.set("full_name", kase.get("full_name"));
				$("#myModalBody").html(new rx_view({model: mymodel}).render().el);
			}
		});
	}
	$("#myModalLabel").html("New Prescription");
	$("#myModal4 .modal-dialog").css("width", "600px");
	$("#modal_save_holder").show();
	$("#myModal4").modal("toggle");
}
function composeEditCalendarFilters() {
	var calfilters = new CalendarFilters();
	calfilters.fetch({
		success: function (data) {
			var mymodel = new Backbone.Model();
			mymodel.set("holder", "myModalBody");
			//<a onclick="showHiddenCalendarFilters()" class="white_text" style="cursor:pointer">Show All</a>&nbsp;&nbsp;|&nbsp;&nbsp;
			$("#modal_save_holder").html('<a onclick="newCalendarFilter()" style="cursor:pointer" class="white_text">New Filter</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a title="Save Filters" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
			$("#modal_type").val("calendar_filters");
			$(".modal-header").css("background-image", "url('img/glass_info.png')");
	$(".modal-header .close").css({"background": "white", "color":"black", "opacity":"100", "padding-left":"2px", "padding-right":"2px"});
			$("#myModalBody").css("background-image", "url('img/glass_info.png')");
			$(".modal-footer").css("background-image", "url('img/glass_info.png')");
			$("#myModalBody").html(new calendar_filters_listing({collection: data, model: mymodel}).render().el);
			$("#myModalLabel").html("Manage Calendar Event Types");
			$("#myModal4").modal("toggle");
		}
	});
}
function showHiddenCalendarTypes() {
	$('.deleted_filter').show();
}
function newCalendarFilter() {
	//see if there are any new rows alread
	var row_id = $(".document_new_row").length;
	var index = row_id + 1;
	var therow = "<tr style='display:' class='calendar_new_row'><td class='calendar_type'><input type='checkbox' value='Y' id='calendar_type_new_" + index + "' name='calendar_type_new_" + index + "' checked></td><td class='calendar_type' style='color:black'><input type='text' placeholder='New Filter' autocomplete='off' id='new_setting_" + index + "' name='new_setting_" + index + "' /></td><td class='calendar_type' style='color:black'><input type='text' placeholder='Abbr' autocomplete='off' id='new_setting_value_" + index + "' name='new_setting_value_" + index + "' /></td><td class='calendar_type default_value' style='color:black'><input type='text' placeholder='Color' autocomplete='off' id='new_default_value_" + index + "' name='new_default_value_" + index + "' class='new_default_value' /></td></tr>";
	$("#calendar_filters_table").prepend(therow);
	$("#new_type_" + index).focus();
	$('#new_default_value_' + index).colorPicker();
}
function composeSettlementSearch() {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	
	var empty_model = new Backbone.Model;		
	empty_model.set("holder", "myModalBody");
	
	$("#myModalLabel").html("Search Settlements");
	$("#modal_save_holder").html('<a title="Search Settlements" class="kase save" onClick="searchSettlements(event)" style="cursor:pointer"><i class="glyphicon glyphicon-search" style="color:yellow; font-size:20px">&nbsp;</i></a>');
	$("#myModalBody").html(new search_settlement_view({model: empty_model}).render().el);
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	
	$("#myModal4 .modal-dialog").css("width", "700px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");

	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
	$('#myModal4').modal('show');		
}
function composeSessionNotValid() {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	
	var empty_model = new Backbone.Model;		
	empty_model.set("holder", "myModalBody");
	
	$("#myModalLabel").html("New Session Started");
	$("#modal_save_holder").html('');
	$("#myModalBody").html(new session_invalid_view({model: empty_model}).render().el);
	
	$(".modal-header").css("background-image", "url('img/glass_dark.png')");
	$("#myModalBody").css("background-image", "url('img/glass_dark.png')");
	$(".modal-footer").css("background-image", "url('img/glass_dark.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	
	$("#myModal4 .modal-dialog").css("width", "700px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_dark.png')");

	$(".modal-content").css("background-image", "url('img/glass_dark.png')");
	$(".modal-header .close").hide();
	$('#myModal4').modal('show');		
}
function composeCurrentUsers() {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	
	var users = new CurrentUserCollection();
	users.fetch({
		success: function(data) {
			$(document).attr('title', "Users :: iKase");
			var empty_model = new Backbone.Model;		
			empty_model.set("holder", "myModalBody");
			
			$("#myModalLabel").html("Currently Logged in to iKase");
			$("#modal_save_holder").html('');
			$("#myModalBody").html(new currentuser_listing_view({collection: data, model: empty_model}).render().el);
			
			$(".modal-header").css("background-image", "url('img/glass_dark.png')");
			$("#myModalBody").css("background-image", "url('img/glass_dark.png')");
			$(".modal-footer").css("background-image", "url('img/glass_dark.png')");
			$(".modal-body").css("overflow-x", "hidden");
			
			
			$("#myModal4 .modal-dialog").css("width", "700px");
			$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_dark.png')");
		
			$(".modal-content").css("background-image", "url('img/glass_dark.png')");
			$(".modal-header .close").css("color", "white");
			$('#myModal4').modal('show');	
		}
	});		
}
function composeBlockedDates(clickedDate) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	
	var start_date = moment().format("MM/DD/YYYY");	// + " 08:00:00";
	var empty_model = new Backbone.Model;		
	empty_model.set("holder", "myModalBody");
	empty_model.set("start_date", start_date);
	empty_model.set("end_date", start_date);
	empty_model.set("recurring_span", "");
	empty_model.set("recurring_count", 0);
	
	$("#myModalLabel").html("Block Calendar Dates");
	$("#modal_save_holder").html('<a title="Save Blocked Dates" class="blocked save" onClick="saveBlockedDates(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#myModalBody").html(new block_dates_view({model: empty_model}).render().el);
	
	$(".modal-header").css("background-image", "url('img/glass_dark.png')");
	$("#myModalBody").css("background-image", "url('img/glass_dark.png')");
	$(".modal-footer").css("background-image", "url('img/glass_dark.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	
	$("#myModal4 .modal-dialog").css("width", "500px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_dark.png')");

	$(".modal-content").css("background-image", "url('img/glass_dark.png')");
	$(".modal-header .close").css("color", "white");
	$('#myModal4').modal('show');	
}
function composeLossSummary() {
	//get the loss summary
	//display the view
	var empty_model = new Backbone.Model;		
	empty_model.set("holder", "myModalBody");
	
	$("#myModalLabel").html("Financial Losses");
	$("#modal_save_holder").html('');
	$("#myModalBody").html(new losses_view({model: empty_model}).render().el);
	
	$(".modal-header").css("background-image", "url('img/glass_dark.png')");
	$("#myModalBody").css("background-image", "url('img/glass_dark.png')");
	$(".modal-footer").css("background-image", "url('img/glass_dark.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "350px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_dark.png')");

	$(".modal-content").css("background-image", "url('img/glass_dark.png')");
	$(".modal-header .close").css("color", "white");
	$('#myModal4').modal('show');	
}
/* solulab code start - 29-05-2019*/
function composeDCCSetting($customer_id){
		//get the loss summary
	//display the view
	var empty_model = new Backbone.Model;		
	empty_model.set("holder", "myModalBody");
	
	$("#myModalLabel").html("DCC Settings");
	$("#modal_save_holder").html('');
	
	$("#myModalBody").html(new dccsetting_view({model: empty_model}).render().el);
	
	$(".modal-header").css("background-image", "url('img/glass_dark.png')");
	$("#myModalBody").css("background-image", "url('img/glass_dark.png')");
	$(".modal-footer").css("background-image", "url('img/glass_dark.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "700px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_dark.png')");

	$(".modal-content").css("background-image", "url('img/glass_dark.png')");
	$(".modal-header .close").css("color", "white");
	$('#myModal4').modal('show');
}
/* solulab code end - 29-05-2019*/