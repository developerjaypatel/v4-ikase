window.check_form = Backbone.View.extend({
	initialize:function () {
		
    },
	 events:{
		"keyup .payment_calc":							"calcBalanceDue",
		"click .ledger":								"labelPayment",
		"click .manage_category":						"manageCategory",
		"change #check_statusInput":					"changeStatus",
		"change #payable_to":							"updateCorporationID",
		"click .manage_payableto":						"managePayable",
		"change #other_payable_to":						"otherPayable",
		"keyup #check_recipient":						"scheduleSearchRecipient",
		"click .select_recipient":						"selectRecipient",
		"click #apply_payment":							"showPaymentAdjustment",
		"click .question_button":						"answerQuestion",
		"click #check_all_done":						"doTimeouts"
    },
    render: function () {
		var self = this;
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "check_form";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		if (typeof this.model.get("corp_id") == "undefined") {
			this.model.set("corp_id", "");		
		}
		if (typeof this.model.get("invoice_number") == "undefined") {
			this.model.set("invoice_number", "");		
		}
		if (typeof this.model.get("kinvoice_id") == "undefined") {
			this.model.set("kinvoice_id", "");		
		}
		if (typeof this.model.get("invoiced_id") == "undefined") {
			this.model.set("invoiced_id", "");		
		}
		if (typeof this.model.get("payback_id") == "undefined") {
			this.model.set("payback_id", "");		
		}
		var check = self.model.toJSON();
		if (typeof check.payments == "undefined") {
			check.payments = 0;
		}
		if (isDate(check.check_date)) {
			check.check_date = moment(check.check_date).format("MM/DD/YYYY")
		} else {
			check.check_date = "";
		}
		if (isDate(check.transaction_date)) {
			check.transaction_date = moment(check.transaction_date).format("MM/DD/YYYY")
		} else {
			check.transaction_date = "";
		}
		if (check.balance == null || check.balance == ""){
			check.balance = 0;
		}
		if (check.balance == 0 && check.amount_due > 0) {
			check.balance = Number(check.amount_due) - Number(check.payments);
		}
		var blnFee = false;
		if (document.location.hash.indexOf("#settlement/")==0) {
			if (check.recipient!="") {
				blnFee = true;
			}
		}
		check.blnFee = blnFee;
		this.model.set("blnFee", blnFee);
		check.blnFee = blnFee;
		check.payment_label = "Amount";
		
		if (check.case_id == -2) {
			//settlement request
			check.carriers = "";
			check.payings = "";
			arrPayables = [];
			thepartie = "<option value='firm|F' selected>" + customer_name + "&nbsp;(Firm)</option>";
			arrPayables.push(thepartie);
			check.parties = arrPayables.join("\r\n");
			
			try {
				$(self.el).html(self.template(check));
			}
			catch(err) {
				alert(err);
				
				return "";
			}
		
			return this;
		}
		
		
		if (this.model.get("invoiced_id")=="" && this.model.get("case_id") > 0) {
			var parties = new Parties([], { case_id: this.model.get("case_id") });
			
			var ledger =  this.model.get("ledger");
			if (ledger=="IN") {
				arrPayables = [];
				thepartie = "<option value='firm|F' selected>" + customer_name + "&nbsp;(Firm)</option>";
				arrPayables.push(thepartie);
				check.parties = arrPayables.join("\r\n");
				
				//from carrier
				var arrFrom = [];
				var parties = new Parties([], { "case_id": this.model.get("case_id"), "type": "carrier" });
				parties.fetch({
					success: function(data) {
						var carriers = data.toJSON();
						var arrParties = [];
						var selected = "";
						_.each( carriers, function(partie) {
							selected = "";
							if (partie.corporation_id == check.from_id) {
								selected = " selected";
							}
							if (carriers.length == 1) {
								selected = " selected";
							}
							thepartie = "<option value='" + partie.corporation_id + "'" + selected + ">" + partie.company_name.toUpperCase() + "</option>";
							arrFrom.push(thepartie);
						});
						check.payings = arrFrom.join("\r\n");
						//$(self.el).html(self.template(check));
						try {
							$(self.el).html(self.template(check));
							
							return this;
						}
						catch(err) {
							alert(err);
							
							return "";
						}
					}
				});
				return this;
			}
			//going to have to select from parties
			parties.fetch({
				success: function(data) {
					//var carriers = parties.where({"type": "carrier"});
					var parties = data.toJSON();
					//var carriers = parties;
					var arrPayables = [];
					var arrExaminers = [];
					var selected = "";
					if (parties.length==1) {
						selected = " selected";
					}
					_.each(parties , function(partie) {
						var thepartie = partie.company_name;
						var theexaminer = "";
						var person_indicator = "C";
						if (partie.type=="carrier") {
							theexaminer = partie.full_name;
						}
						
						if (thepartie=="") {
							thepartie = partie.full_name;
							if (partie.type=="applicant") {
								person_indicator = "P";
								partie.id = partie.person_id;
							}
						} else {
							partie.id = partie.corporation_id;
						}
						var thefax = partie.fax;
						var theemployee_fax = partie.employee_fax;
						if (parties.length > 1) {
							selected = '';
							if (partie.uuid==check.carrier_uuid) {
								selected = ' selected="selected"';
							}
							if (typeof check.payable_id != "undefined") {
								if (partie.id==check.payable_id) {
									selected = ' selected="selected"';
								}
							}
						}
						if (partie.partie_type==null) {
							if (partie.type=="legacy_law_firm") {
								partie.partie_type = "Legacy Firm";
							}
						}
						thepartie = "<option value='" + partie.corporation_id + "|" + person_indicator + "'" + selected + ">" + thepartie.toUpperCase() + "&nbsp;(" + partie.partie_type + ")</option>";
						arrPayables.push(thepartie);
						if (theexaminer!="") {
							theexaminer = "<option value='" + theexaminer + "'" + selected + ">" + theexaminer + "</option>";
							arrExaminers.push(theexaminer);
						}
					});
					check.parties = arrPayables.join("\r\n");
					check.payings = "";
					//$(self.el).html(self.template(check));
					try {
						$(self.el).html(self.template(check));
						return this;
					}
					catch(err) {
						alert(err);
						
						return "";
					}
				}
			});
		} else {
			//we know from the invoice who is invoiced
			thepartie = "<option value='" + this.model.get("invoiced_id") + "' selected>" + this.model.get("invoiced") + "</option>";
			check.carriers = thepartie;
			
			try {
				$(self.el).html(self.template(check));
			}
			catch(err) {
				alert(err);
				
				return "";
			}
		}
		return this;
    },
	calcBalanceDue: function() {
		var paymentInput = $("#paymentInput").val();
		var amount_dueInput = $("#amount_dueInput").val();
		if (amount_dueInput==0 || paymentInput==0) {
			//dont bother
			return;
		}
		
		var balanceInput = Number(amount_dueInput) - Number(paymentInput);
		$("#balanceInput").val(balanceInput);
		$("#balanceSpan").html(balanceInput);
	},
	labelPayment: function(event) {
		var element = event.currentTarget;
		label = "Payment";
		if (element.id=="ledger_in") {
			label = "Disbursement";
		}
		$("#payment_label").html(label);
	},
	manageCategory: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;
		
		var status_level = "";
		if (element.id.indexOf("sub") > -1) {
			status_level = "sub";
		}
		
		$('.modal-dialog').animate({width:1220, marginLeft:"-500px"}, 1100, 'easeInSine', 
			function() {
				//run this after animation
				var categories = new CostCategoryCollection();
				
				categories.fetch({
					success: function (data) {
						var mymodel = new Backbone.Model();
						$('#manage_categories_holder').show();
						mymodel.set("holder", "manage_categories_holder");
						mymodel.set("status_level", status_level);
						$('#manage_categories_holder').html(new cost_category_listing({collection: data, model: mymodel}).render().el);
					}
				});
			}
		);
	},
	changeStatus: function() {
		var check_status = $("#check_statusInput").val();
		switch(check_status) {
			case "":
				$("#check_status_description").html("");
				break;
			case "P":
				$("#check_status_description").html("PENDING status identifies checks that are expected but not yet Received");
				break;
			case "S":
				$("#check_status_description").html("SENT status identifies checks that have been Sent");
				break;
			case "R":
				$("#check_status_description").html("RECEIVED status identifies checks that have been Received");
				break;
		}
		
		setTimeout(function() {
			$("#check_status_description").html("");
		}, 3500);
	},
	updateCorporationID: function(event) {
		var element = event.currentTarget;
		var arrID = element.value.split("|");
		
		//clear out other
		$(".payable_other").val("");
		$("#other_payable_to").val("");
		
		$(".check #corp_id").val(arrID[0] + "|" + arrID[1]);
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
		
		$('.modal-dialog').animate({width:1200, marginLeft:"-500px"}, 1100, 'easeInSine', 
			function() {
				//run this after animation
				$("#payable_to").val("");
				$("#payee_holder").fadeIn();
				$("#check_recipient").focus();
				
				$('#manage_categories_holder').hide();
			}
		);
	},
	answerQuestion: function(event) {
		answerQuestion(event);
	},
	showPaymentAdjustment: function(event) {
		event.preventDefault();
		$("#answer_apply_payment").hide();
		$("#apply_payment_holder").fadeOut(function() {
			$("#check_payment_holder").show();
			$("#check_adjustment_holder").show();
		});
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
	doTimeouts: function(event) {
		var self = this;
		var ledger =  this.model.get("ledger");
		if (ledger!="") {
			var low_ledger = ledger.toLowerCase();
			if (ledger=="DIS") {
				low_ledger = "in";
			}
			$("#ledger_" + low_ledger).attr("checked", true);
			ledger = ledger.toLowerCase();
		}
		
		$('.check #check_dateInput').datetimepicker({ 
			timepicker:false, 
			mask:false,
			validateOnBlur:false, 
			format:'m/d/Y',
			onChangeDateTime:function(dp,$input){
				//alert($input.val());
			}
		});
		$('.check #transaction_dateInput').datetimepicker({ 
			timepicker:false, 
			mask:false,
			validateOnBlur:false, 
			format:'m/d/Y',
			maxDate: '+1970/01/02',
			onChangeDateTime:function(dp,$input){
				//alert($input.val());
			}
		});
		if (self.model.get("case_id") < 0 && ledger=="in" && self.model.get("account_type")=="operating") {
			$("#check_from").val(customer_name);	
		}
		if (self.model.get("case_id") == -2 && self.model.get("account_type")=="trust") {
			//lookup
			var theme = {
				theme: "event", 
				tokenLimit:1,
				onDelete: function() {
					//disable the save
					$(".check.save").hide();
				},
				onAdd: function(item) {
					$("#case_id").val(item.id);
					self.model.set("case_id", item.id);
					//now we can save
					$(".check.save").show();
					/*
					if ($("#carrier").length==0) {
						return;
					}
					*/
					var arrParties = ['<option value="">Select a Partie from List</option>'];
					var parties = new Parties([], { case_id: item.id });
					parties.fetch({
						success: function(parties) {
							var carriers = parties.where({"type": "carrier"});
							var arrCarriers = ['<option value="">Select a Partie from List</option>'];
							var selected = "";
							if (carriers.length==1) {
								selected = " selected";
							}
							_.each(carriers , function(carrier) {
								var thepartie = carrier.get("company_name");
								var theexaminer = carrier.get("full_name");
								var thefax = carrier.get("fax");
								var theemployee_fax = carrier.get("employee_fax");
								thepartie = "<option value='" + carrier.get("corporation_id") + "'" + selected + ">" + thepartie + "</option>";
								theexaminer = "<option value='" + theexaminer + "'" + selected + ">" + theexaminer + "</option>";
								
								if (carriers.length > 1) {
									selected = '';
								}
								arrCarriers[arrCarriers.length] = thepartie;
							});
							$("#carrier").html(arrCarriers.join("\r\n"));
							$("#check_from").html(arrCarriers.join("\r\n"));
							$("#check_from").show();
						}
					});
				}
			}
			$("#case_nameInput").tokenInput("api/kases/tokeninput", theme);
			$(".token-input-list-event").css("width", "540px");
			$("#case_input_holder").css("display", "inline-block");
			$("#check_case_label").css("display", "inline-block");
			//we will reset the from after add
			$("#check_from").hide();
			//disable the save
			//$(".check.save").hide();
		}
		//change the cost type
		$("#check_typeInput").val(this.model.get("check_type"));
		
		var recipient = this.model.get("recipient");
		if (recipient!="" || this.model.get("invoiced_id")!="") {
			//settlement
			$(".ledger").hide();
			$("#type_holder").hide();
			$("#type_span_holder").html("<span style='font-weight:bold'>Type</span>&nbsp;&nbsp;Payment");
			$("#type_span_holder").show();
			$("#payment_label").html("Amount");
			
			if (document.location.hash.indexOf("#settlement/")==0) {
				if (recipient!="") {
					var fee = $("#balance_" + recipient).val();
				
					$("#fee_id").val(Number($("#fee_id_" + recipient).val()));
					$("#amount_dueInput").val(Number(fee));
					$("#amount_dueSpan").html(fee);
					if (this.model.get("id") < 0) {
						$("#paymentInput").val(Number($("#amount_dueInput").val()).toFixed(2));
					}
				}
			}
			/*
			if (this.model.get("context")!="invoice") {
				//the amount due is the recipient fee
				var fee = $("#balance_" + recipient).val();
				
				$("#fee_id").val(Number($("#fee_id_" + recipient).val()));
				$("#amount_dueInput").val(Number(fee));
				$("#amount_dueSpan").html(fee);
				if (this.model.get("id") < 0) {
					$("#paymentInput").val(Number($("#amount_dueInput").val()).toFixed(2));
				}
			} else {
				var fee = this.model.get("amount_due");
				$("#paymentInput").val(Number(fee).toFixed(2));
			}
			*/
			/*
			if (!isNaN(Number(fee))) {
				$("#amount_dueInput").hide();
				$("#amount_dueSpan").show();
			} else {
				$("#amount_dueInput").show();
				$("#amount_dueSpan").hide();
			}
			*/
		}
		if (ledger=="in") {
			//the firm is getting paid
			$("#payable_to_row").hide();
			$("#payable_to_mainrow").hide();
			$("#manage_category").hide();
			if (self.model.get("case_id") > 0) {
				var firm_option = document.getElementById("payable_to").options[1];
				$("#payable_to").html(firm_option.outerHTML);
			}
		}
		if (ledger=="dis" || ledger=="out") {
			//$(".ledger").hide();
			//$("#type_holder").hide();
			//$("#type_span_holder").html("<span style='font-weight:bold'>Type</span>&nbsp;&nbsp;Disbursement");
			//$("#type_span_holder").show();
			$("#ledger_out_label").html("Check");
			$("#ledger_in").val("DIS");
			$("#payment_label").html("Amount");
		}

		if (this.model.get("account_id")!="") {
			var settle_string = "";
			if (this.model.get("account_type")=="trust") {
				settle_string = "Settlement ";
			}
			var account_type = this.model.get("account_type").capitalize();
			if (account_type=="Operating") {
				account_type = "Cost Trust";
			}
			$("#type_span_holder").html("<span style='font-weight:bold'>Type</span>&nbsp;&nbsp;" + account_type + " Deposit");
			$("#check_typeInput").html("<option value='" + account_type + " Deposit' selected>" + settle_string + account_type + " Deposit</option>");
			$("#type_holder").hide();
			$("#check_payment_holder").hide();
			$("#check_adjustment_holder").hide();
			
			if (ledger=="out" && self.model.id < 0) {
				//check number?
				var account =  new Account({"id": this.model.get("account_id")});
				account.fetch({
					success: function (data) {
						if (data.get("account_info")!="") {
							var jdata = JSON.parse(data.get("account_info"));
							var arrLength = jdata.length;
							for (var i = 0; i < arrLength; i++) {
								if (jdata[i].name=="current_check_numberInput") {
									$("#check_numberInput").val(jdata[i].value);
									break;
								}
							}
						}
						
						//do we have funds available
						var case_id = self.model.get("case_id");
						var url = "api/account/balance/" + case_id + "/" + data.get("account_type");
						var account_name = data.get("account_name");
						$.ajax({
							url:url,
							type:'GET',
							dataType:"json",
							success:function (data) {
								data.available = Number(data.balance) - Number(data.pendings);
								
								if (data.available <= 0) {
									//can't write checks, no money
									$(".check.save").hide();
									
									document.getElementById("check_numberInput").parentElement.style.background = "red";
									document.getElementById("check_numberInput").parentElement.innerHTML = "<span title='There are insufficient funds in the " + account_name + " account.  Please enter a Deposit so that you can issue checks.'>Deposit Required</span>";
								}
							}
						});
					}
				});
			}
		} else {
			if (self.model.id < 0) {
				//is there a next check number setting
				if (typeof customer_settings.get("check_number") != "undefined") {
					$("#check_numberInput").val(customer_settings.get("check_number"));
				}
			}
		}
		
		if (this.model.get("invoiced_id")!="") {
			$("#check_form #carrier").attr("size", 1);
			$("#check_typeInput").val("invoice payment");	//invoice payment hard coded
		}
		this.calcBalanceDue();
		
		$('.check #message_attachments').html(new message_attach({model: self.model}).render().el);
		
		//does this note have any attachments
		if (self.model.id > 0) {
			if (this.model.get("ledger")=="OUT") {
				$("#check_payment_holder").show();
				$("#check_adjustment_holder").show();
			}
			$("#apply_payment_holder").hide();
			$("#answer_apply_payment").hide();
			
			var check_documents = new AttachmentCollection([], { parent_id: self.model.id, parent_table: "check" });
			check_documents.fetch({
				success: function(data) {
					var arrCheckDocuments = [];
					var arrCheckDocumentsFilename = [];
					_.each( data.toJSON(), function(check_document) {
						arrCheckDocuments[arrCheckDocuments.length] = check_document.document_id;
						arrCheckDocumentsFilename[arrCheckDocumentsFilename.length] = decodeURIComponent(check_document.document_filename);
					});
					$(".check #send_document_id").val(arrCheckDocuments.join("|"));
					$(".check #send_queue").html(arrCheckDocumentsFilename.join("; "));
				}
			});	
			
			if(self.model.get("check_status")=="V") {
				$("#myModalLabel").html("<span style='background:red;color:white;padding:3px'>VOID</span>")
				$(".check.save").hide();
			} else {
				//you may void the check
				$("#void_check").fadeIn();
			}
		} else {
			//new check
			if (ledger=="dis" || ledger=="out") {
				$("#apply_payment_holder").show();
			}
		}
		
		if (self.model.get("case_id") > 0) {
			var kase = kases.findWhere({case_id: self.model.get("case_id")});
			var case_name = kase.get("case_name");
			if (case_name == "") {
				case_name = kase.get("case_number");
			}
			if (case_name == "") {
				case_name = kase.get("file_number");
			}
			if (customer_id == 1121) {
				//per elizabeth martinez 3/19/2019
				var full_name = kase.get("full_name");
				if (full_name!="") {
					if (case_name.indexOf(kase.get("full_name")) <  0) {
						case_name += " - Plaintiff: " + full_name;
					}
				}
			}
			if (case_name != "") {
				$("#check_case_label").css("display", "inline-block");
				$("#check_case_name").css("font-weight", "bold")
				$("#check_case_name").css("margin-bottom", "5px");
				$("#check_case_name").html(case_name);
			}
		}
		
		if (this.model.get("blnFee")) {
			$("#ledger_in_label").html("Payment");
		}
		$("#check_numberInput").focus();
		
		if (this.model.id > 0) {
			if (this.model.get("request_payable_type")=="F") {
				$("#payable_to_row").hide();
				document.getElementById("payable_to").innerHTML = "<option value='F|" + customer_id + "' selected>" + customer_name + "</option>";
			}
		}
	}
});
var blnNewCheck = false;
window.check_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .check_category":					"filterByCategory",
		"click #checks_clear_search":				"clearSearch",
		"click #new_check_Payment":					"newPayment",
		"click #new_check_Receipt":					"newPayment",
		"click #general_checkrequest":				"checkRequestGeneral",
		"click #new_check_Disbursement":			"newDisbursement",
		"click #print_checks_Payment":				"printPayments",
		"click #print_checks_Disbursement":			"printDisbursements",
		"click .compose_check":						"editCheck",
		"click .delete_check":						"confirmdeleteCheck",
		"click #new_checkrequest":					"checkRequest",
		"click .payback":							"payBack",
		"click .filter_case":						"filterCases",
		"change #filter_payee":						"filterPayee",
		"change #filter_ledger":					"filterLedger",
		"change .print_checkbox":					"showPrintButton",
		"click #print_deposits":					"printBulkDeposits",
		"change #filter_status": 					"filterStatus",
		"change #filter_start_date":				"filterStartDate",
		"change #filter_end_date":					"filterEndDate",
		"keyup #filter_start_number":				"filterStartNumber",
		"keyup #filter_end_number":					"filterEndNumber",	
		"click #clear_filters":						"clearAllFilters",	
		"click .clear_check":						"clearCheck",
		"click .unclear_check":						"unclearCheck",
		"click #print_selected":					"printActualChecks",
		"click .print_check":						"printCheck",
		"click .print_receipt":						"printReceipt",
		"click .print_copy":						"copyCheck",
		"click #select_all_checks":					"selectAll",
		"click .print_checkbox":					"selectCheckBox",
		"click #label_search_check":				"Vivify",
		"click #checks_searchList":					"Vivify",
		"focus #checks_searchList":					"Vivify",
		"blur #checks_searchList":					"unVivify",
		"click #check_listing_all_done":			"doTimeouts"		
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			var view = "check_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		var checks = this.collection.toJSON();
		var mymodel = this.model.toJSON();
		
		var totals_due = 0;
		var totals_payment = 0;
		var totals_adjustment = 0;
		var blnPaid = false;
		var page_title = this.model.get("page_title");
		var embedded = this.model.get("embedded");
		var blnAccountListing = (mymodel.holder.indexOf("account_checks") > -1);
		
		var blnShowInfo = ((page_title!="Disbursement" && page_title!="Receipt") && !embedded); 
		if (document.location.hash.indexOf("#bankaccount/list/")==0) {
			blnShowInfo = true;
		}
		var blnBankAccount = (document.location.hash.indexOf("#bankaccount/list/")==0 || document.location.hash.indexOf("#checks/")==0);
		this.model.set("blnBankAccount", blnBankAccount);
		
		var arrHolder = mymodel.holder.split("_");
		var blnShowMemo = true;
		if (typeof mymodel.blnShowMemo != "undefined") {
			blnShowMemo = mymodel.blnShowMemo;
		}
		
		var blnFeePaid = (typeof this.model.get("fee_paid") != "undefined");
		//this.model.set("blnShowMemo", !(arrHolder[arrHolder.length - 1]=="div"));
		var arrPayees = [];
		
		_.each( checks, function(check) {
			if (check.check_date!="" && check.check_date!="0000-00-00") {
				check.check_date = moment(check.check_date).format("MM/DD/YY")
			} else {
				check.check_date = "";
			}
			
			if (check.print_date!="" && check.print_date!="0000-00-00") {
				check.print_date = moment(check.print_date).format("MM/DD/YY")
			} else {
				check.print_date = "";
			}
			
			var blnRegularPayment = true;
			/*
			if (check.ledger=="OUT") {
				if (check.payment > 0 && check.amount_due == 0 ) {
					check.amount_due = check.payment;
					blnRegularPayment = true;
				}
			}
			
			if (check.ledger=="IN") {
				if (check.check_type.indexOf(" fee") > -1) {
					if (check.payment > 0 && check.amount_due == 0 ) {
						check.amount_due = check.payment;
						blnRegularPayment = true;
					}
				}
			}
			*/
			if (!isNaN(check.amount_due)) {
				if (check.check_status!="V") {
					totals_due += Number(check.amount_due);
				}
            } else {
				check.amount_due = 0;
			}
			if (!isNaN(check.payment)) {
				if (check.check_status!="V") {
					totals_payment += Number(check.payment);
				}
            } else {
				check.payment = 0;
			}
			if (!isNaN(check.adjustment)) {
				if (check.check_status!="V") {
					totals_adjustment += Number(check.adjustment);
				}
            } else {
				check.adjustment = 0;
			}
			
			var check_amount_due = check.amount_due;
			var check_payment = check.payment;
			check.balance = Number(check_amount_due) - Number(check_payment);
			
			if (document.location.hash.indexOf("#bankaccount/list/")<0) {
				//check.ledger = check.ledger.replace("OUT", "CHK");
			}
			check.actual_check_number = check.check_number;
			//if (!blnBankAccount) {
				if (check.amount_due == check.payment && check.amount_due > 0 && !blnRegularPayment) {
					if (check.ledger=="IN") {
						if (check.parent_check_uuid!="") {
							check.check_number += '&nbsp;REIMBURSEMENT';
						}
					} else {
						check.check_number += '&nbsp;REIMBURSED';
					}
				} else { 
					if (check.check_status!="V") {
						if (check.check_number == "") {
							check.check_number = 'Edit Payment';	
						}
						check.check_number = '<a title="Click to edit payment" class="compose_check white_text" id="compose_check_' + check.id + '" data-toggle="modal" data-target="#myModal4" style="cursor:pointer">' + check.check_number + '&nbsp;&nbsp;<i class="glyphicon glyphicon-edit" style="color:#a9bafd">&nbsp;</i></a>';
						
						if (check.ledger=="IN") {
							check.check_number += '&nbsp;|&nbsp;<a title="Click to print deposit receipt" class="print_receipt white_text" id="print_receipt_' + check.id + '" style="cursor:pointer"><i class="glyphicon glyphicon-print" style="color:darkseagreen">&nbsp;</i></a>';
							check.check_number += '&nbsp;<input type="checkbox" id="print_checkbox_' + check.id + '" class="print_checkbox" style="display:none" />';
						}
						if (check.request_payable_type=="F") {
							check.payable_full_name = customer_name;
						}
						if (check.payable_full_name=="") {
							check.payable_full_name = "<span style='font-style:italic;font-size:0.8em;background: orange;color: black;padding: 2px;'>Please Edit to set Payee</span>";
							if (check.ledger=="OUT" && check.check_status!="C" && check.check_status!="") {
								check.check_number += '&nbsp;|&nbsp;<i class="glyphicon glyphicon-print" style="color:red" title="Needs a Payee for Print.  Please edit this check to set the Payee">&nbsp;</i>';
							}
						}
						if (check.ledger=="OUT" && check.check_status!="C" && (check.payable_full_name!="" || check.request_payable_type=="F")) {
							//check we wrote and hasn't cleared yet
							if (check.print_date=="") {
								check.check_number += '&nbsp;|&nbsp;<a title="Click to print check" class="print_check white_text" id="print_check_' + check.id + '" style="cursor:pointer"><i class="glyphicon glyphicon-print" style="color:aqua">&nbsp;</i></a>';
							} else {
								check.check_number += '&nbsp;|&nbsp;<a title="Click to print a copy of check.  Check was originally printed on ' + check.print_date + ' by ' + check.print_by + '" class="print_copy white_text" id="print_copy_' + check.id + '" style="cursor:pointer"><i class="glyphicon glyphicon-print" style="color:antiquewhite">&nbsp;</i></a>';
							}
						}
					}
				}
			//}
			check.ledger_display = "Paid";
			if (check.ledger=="IN") {
				check.ledger_display = "Received";
			}
			
			if (blnBankAccount) {
				if (check.case_name=="") {
					check.case_name = check.case_number;
				}
				if (check.case_name=="") {
					check.case_name = check.file_number;
				}
				if (check.payable_full_name!="") {
					arrPayees.push("<option value='" + check.payable_full_name + "'>" + check.payable_full_name + "</option>");
				}
			}
			var arrAttachment = [];
			check.attach_link = "";

			var attach_indicator = "none";
			var word_indicator = "none";
			var pdf_indicator = "none";
			var excel_indicator = "none";
            var attach_link = "";
			
			var pdf_count = 0;
			var word_count = 0;
			var excel_count = 0;
			
			var blnPaid = (check.balance<=0);
			
            if (check.attachments!="") {
				check.attachments = check.attachments.replaceAll(";", "|");
            	attach_indicator = "";
				email_attachment = "";
				var arrAttach = check.attachments.split("|");
				var arrayLength = arrAttach.length;
				for (var i = 0; i < arrayLength; i++) {
					var attachment = arrAttach[i];
					attachment = attachment.trim();
					
					attachment = attachment.replace("https:///uploads", "../uploads");
					attachment = attachment.replace("D:/uploads/" + check.customer_id + "/" + check.case_id + "/", "");
					attachment = attachment.replace("D:/uploads/" + check.customer_id + "/", "");
					if (check.case_id=="" || check.case_id=="-1") {
						attach_link = "D:/uploads/" + check.customer_id + "/" + attachment;
					} else {
						attach_link = "D:/uploads/" + check.customer_id + "/" + check.case_id + "/" + attachment;
					}
				
					attach_link = '<a id="kase_attach_link_' + i + '" href="' + attach_link + '" target="_blank" title="Click to review ' + attachment + '">' + attachment + '</a>';
					arrAttachment.push(attach_link);
				}
            }
			
			if (arrAttachment.length > 0) {
				var attachment_list = arrAttachment.join("<br>");
				check.attach_link = "<div id='attach_link_" + check.id + "' style='display:none'>" + attachment_list + "</div>";
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
			check.pdf_count = pdf_count;
			if (word_count > 1) {
				word_count = " (" + word_count + ")";
			} else {
				word_count = "";
			}
			check.word_count = word_count;
			if (excel_count > 1) {
				excel_count = " (" + excel_count + ")";
			} else {
				excel_count = "";
			}
			check.excel_count = excel_count;
			check.attach_indicator = attach_indicator;
			check.pdf_indicator = pdf_indicator;
			check.word_indicator = word_indicator;
			check.excel_indicator = excel_indicator;
			
			if (check.ledger=="IN") {
				if(check.thefrom!="") {
					check.payable_full_name = check.thefrom;
				}
			}
			if (typeof check.kinvoice_id == "undefined") {
				check.kinvoice_id = "";
				check.kinvoice_number = "";
				check.company_name = "";
				check.corporation_id = "";
			}
			check.void_indicator = "";
			check.check_status_indicator = check.check_status;
			check.check_status_search = "";
			//check status
			switch(check.check_status) {
				case "C":
					check.check_status_indicator = "&nbsp;<span class='check_cleared' style='background:lime; color:black; padding:2px; margin-right:15px' title='Check has Cleared'>&#10003</span>";
					check.check_status_search = "check_cleared";
				break
					case "P":
					check.check_status_indicator = "&nbsp;<span class='check_pending'  style='background:orange; color:black; padding:2px; margin-right:15px' title='Check Status is Pending'>" + check.check_status + "</span>";
					check.check_status_search = "check_pending";
					break
				case "S":
					check.check_status_indicator = "&nbsp;<span class='check_sent'  style='background:blue; color:white; padding:2px; margin-right:15px' title='Check has been Sent'>" + check.check_status + "</span>";
					check.check_status_search = "check_sent";
					break
				case "R":
					check.check_status_indicator = "&nbsp;<span class='check_received'  style='background:green; color:white; padding:2px; margin-right:15px' title='Check has been Received'>" + check.check_status + "</span>";
					check.check_status_search = "check_received";
					break;
				case "V":
					check.check_status_indicator = "&nbsp;<span class='check_void' n style='background:red; color:white; padding:2px; margin-right:15px' title='Check is Void'>VOID</span>";
					check.void_indicator = "text-decoration: line-through; background:red";
					check.check_status_search = "check_void";
			}
			check.check_number += check.check_status_indicator + "<span style='display:none'>" + check.check_status_search + "</span>";
		});
		
		if (typeof mymodel.embedded == "undefined") {
			this.model.set("embedded", false);
		}
		if (typeof mymodel.kinvoice_id == "undefined") {
			this.model.set("kinvoice_id", "");
		}
		var case_id = "";
		if (typeof mymodel.case_id != "undefined") {
			case_id = mymodel.case_id;
		}
		
		if (blnFeePaid) {
			totals_due = self.model.get("fee_paid");
			var fee_balance = totals_due - totals_payment;
			blnPaid = (fee_balance<=0);
		}
		
		var blnShowBoxes = false;

		if (document.location.hash.indexOf("printed") > -1) {
			blnShowBoxes = true;
		}
		this.model.set("blnPaid", blnPaid);
		this.model.set("blnShowBoxes", blnShowBoxes);
		this.model.set("totals_due", totals_due);
		this.model.set("totals_payment", totals_payment);
		this.model.set("totals_adjustment", totals_adjustment);
		this.model.set("recipient","");
		
		var payee_options = "";
		if (arrPayees.length > 0) {
			arrPayees = arrPayees.unique();
			arrPayees.unshift("<option value='' selected>Select from List</option>");
			payee_options = arrPayees.join("\r\n");
		}
		if (mymodel.holder.indexOf("#payments_holder")==0) {
			var arrHolder = mymodel.holder.split("_");
			this.model.set("recipient", arrHolder[2]);
		}
		var account_type = "";
		var table_width = "";
		if (typeof this.model.get("account_type")!="undefined") {
			account_type = this.model.get("account_type");
			if (blnBankAccount && account_type=="operating") {
				table_width = "width:50%";
			}
		}
		try {
			$(this.el).html(this.template({checks: checks, case_id: case_id, totals_due: totals_due, totals_payment: totals_payment, totals_adjustment: totals_adjustment, page_title: page_title, 
			blnBankAccount: 		blnBankAccount,
			blnShowInfo: 			blnShowInfo,
			blnShowMemo: 			blnShowMemo,
			blnShowBoxes:			blnShowBoxes,
			blnFeePaid:				blnFeePaid,
			blnAccountListing:		blnAccountListing,
			embedded: 				embedded,
			payee_options:			payee_options,
			account_type:			account_type,
			table_width:			table_width
			}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		if (typeof case_id == "undefined") {
			case_id = "";
		}
		if (case_id != "") {
			var kase = kases.findWhere({case_id: case_id});
			
			if (typeof kase == "undefined") {
				//get it
				var kase =  new Kase({id: case_id});
				kase.fetch({
					success: function (kase) {
						if (kase.toJSON().uuid!="") {
							kases.remove(kase.id); kases.add(kase);
							self.render();
						} else {
							//case does not exist, get out
							document.location.href = "#";
						}
						return;		
					}
				});
				return;
			}
			//console.log(kase.toJSON());
			var case_status = kase.toJSON().case_status;
			var case_substatus = kase.toJSON().case_substatus;
			var attorney = kase.toJSON().attorney;
			var worker = kase.toJSON().worker;
			var rating = kase.toJSON().rating;
			//var kase = kases.findWhere({case_id: this.model.get("case_id")});
			this.model.set("case_status", case_status);
			this.model.set("case_substatus", case_substatus);
			this.model.set("attorney", attorney);
			this.model.set("worker", worker);
			this.model.set("rating", rating);
			
			setTimeout(function() {
				$("#case_number_fill_in").html(kase.toJSON().case_number);
				$("#adj_number_fill_in").html(kase.toJSON().adj_number);
				if (kase.toJSON().adj_number == "") { 
					$("#adj_slot").hide();
				}
				$("#case_status_fill_in").html(kase.toJSON().case_status);
				$("#case_substatus_fill_in").html(kase.toJSON().case_substatus);
				$("#attorney_fill_in").html(kase.toJSON().attorney);
				$("#rating_fill_in").html(kase.toJSON().rating);
				$("#worker_fill_in").html(kase.toJSON().worker);
				$("#case_date_fill_in").html(kase.toJSON().case_date);
				$("#claims_fill_in").html(kase.toJSON().claims);
				if (kase.toJSON().claims == "") { 
					//$("#claims_slot").hide();
				}
				$("#case_type_fill_in").html(kase.toJSON().case_type);
				$("#case_type").val(kase.toJSON().case_type);
				$("#language_fill_in").html(kase.toJSON().language);
				if (kase.toJSON().language == "") { 
					$("#language_slot").hide();
				}
			}, 10);
			
			var parties = new Parties([], { case_id: this.model.get("case_id"), case_uuid: this.model.get("uuid"), panel_title: ""});
			parties.fetch({
				success: function(parties) {
					var claim_number = "";
					var carrier_insurance_type_option = "";
					//now we have to get the adhocs for the carrier
					var carrier_partie = parties.findWhere({"type": "carrier"});
					if (typeof carrier_partie == "undefined") {
						carrier_partie = new Corporation({ case_id: self.model.get("case_id"), type:"carrier" });
						carrier_partie.set("corporation_id", -1);
						carrier_partie.set("partie_type", "Carrier");
						carrier_partie.set("color", "_card_missing");
					}
					carrier_partie.adhocs = new AdhocCollection([], {case_id: case_id, corporation_id: carrier_partie.attributes.corporation_id});
					carrier_partie.adhocs.fetch({
						success:function (adhocs) {
							var adhoc_claim_number = adhocs.findWhere({"adhoc": "claim_number"});
							
							if (typeof adhoc_claim_number != "undefined") {
								claim_number = adhoc_claim_number.get("adhoc_value");
							}
							
							var adhoc_carrier_insurance_type_option = adhocs.findWhere({"adhoc": "insurance_type_option"});
							
							if (typeof adhoc_carrier_insurance_type_option != "undefined") {
								carrier_insurance_type_option = adhoc_carrier_insurance_type_option.get("adhoc_value");
							}
							var arrClaimNumber = [];
							var arrCarrierInsuranceTypeOption = [];
							if (carrier_partie.attributes.claim_number!="" && carrier_partie.attributes.claim_number!=null) {
								//arrClaimNumber.push(partie.claim_number);
								var claim_number = carrier_partie.attributes.claim_number;
								$("#claim_number_fill_in").html(claim_number);
								kase.set("claim_number", claim_number);
							}
						}
					});
				}
			});
		}
		if (self.model.get("holder")=="#kase_content") {
			var billing_hours = new ActivitiesBillingCollection({ case_id: case_id });
			billing_hours.fetch({
				success: function(data) {
					kase.set("holder", "#billing_hours_table");
					$('#billing_hours_table').html(new billing_listing_view({collection: data, model: kase}).render().el);
					$("#billing_hours_table").removeClass("glass_header_no_padding");
					hideEditRow();
				}
			});

			setTimeout(function() {
				self.model.set("hide_upload", true);
				showKaseAbstract(self.model);
			}, 750);
		} else {
			setTimeout(function() {
				var recipient = self.model.get("holder").split("_")[2];
				$("#row_payments_holder_" + recipient + " #check_listing_header").hide();
			}, 325);
		}
		
		blnNewCheck = false;
		
		return this;
    },
	doTimeouts: function(event) {
		if (this.collection.length==0) {
			var page_title = this.model.get("page_title");
			$(".check_listing_" + page_title).hide();
			$("#" + page_title.toLowerCase() + "_holder").css("padding-bottom", "15px");
			
		}
		$(".check_listing th").css("font-size", "1em");
		$(".check_listing").css("font-size", "1.1em");
		
		var recipient = this.model.get("recipient");
		
		if (recipient!=""){
			$("#balance_" + recipient).val(this.model.get("totals_due") - this.model.get("totals_payment"));
			var balance = "$" + formatDollar($("#balance_" + recipient).val());
			var due_style = " style='background:orange; color:black; padding:2px'";
			
			if (this.model.get("blnPaid")) {
				$("#payment_" + recipient).hide();
				$("#payment_button_holder_" + recipient).html("<span style='background:lime; color:black; padding:2px'>Paid&nbsp;&#10003;</span>");
				due_style = " style='background:lime; color:black; padding:2px'";
			}
			
			$("#due_" + recipient + "Span").html("<span" + due_style + ">$ " + formatDollar($("#balance_" + recipient).val()) + "</span>");
		}
		
		if (document.location.hash.indexOf("#settlement")==0) {
			//show the new checkrequest button
			$("#general_checkrequest").show();
		}
		
		$(".tablesorter").css("background", "url(../img/glass_dark.png)");
		
		if (this.model.get("blnBankAccount")) {
			var theme = {
				theme: "event", 
				tokenLimit:1,
				onAdd: function(item) {
					var search_element = document.getElementById("case_filter");
					search_element.value = item.case_name;
					findIt(search_element, 'check_listing', 'check');
				},
				onDelete: function() {
					var search_element = document.getElementById("case_filter");
					search_element.value = "";
					findIt(search_element, 'check_listing', 'check');
				}
			}
			$("#case_nameInput").tokenInput("api/kases/tokeninput", theme);
			
			$('#check_listing').tablesorter();
		}
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".print_checkbox").prop("checked", element.checked);
		
		this.verifyChecked();
	},
	selectCheckBox: function(event) {
		this.verifyChecked();
	},
	verifyChecked: function() {
		//if any box is checked, show the button
		var blnChecked = false;
		var print_checks = document.getElementsByClassName("print_checkbox");
		var arrLength = print_checks.length;
		for(var i = 0; i < arrLength; i++) {
			if (print_checks[i].checked) {
				blnChecked = true;
				break;
			}
		}
		
		if (blnChecked) {
			$("#print_selected").fadeIn();
		} else {
			$("#print_selected").fadeOut();
		}
	},
	printCheck: function(event) {
		var url = "/reports/print_checks.php?id="
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		window.open(url + id);
		
		setTimeout(function() {
			checkClearedChecks();
		}, 5000);
	},
	printReceipt: function(event) {
		var url = "/reports/print_deposits.php?id="
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		window.open(url + id);
		
	},
	printActualChecks: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		event.preventDefault;
		
		var print_checks = document.getElementsByClassName("print_checkbox");
		var arrLength = print_checks.length;
		var arrID = [];
		for(var i = 0; i < arrLength; i++) {
			if (print_checks[i].checked) {
				arrID.push(print_checks[i].id.replace("print_checkbox_", ""));
			}
		};
		if (arrID.length == 0) {
			return;
		}
		var ids = arrID.join(",");
		
		var url = "/templates/multi_checks.php"
		params = {"ids": ids};
		postForm(url, params, "post", "_blank");
		
		setTimeout(function() {
			checkClearedChecks();
		}, 5000);
	},
	copyCheck: function(event) {
		var url = "/reports/print_checks.php?copy=&id="
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		window.open(url + id);
	},
	clearCheck: function(event) {
		var url = "api/check/clear"
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		var formValues = "id=" + id;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					element.style.display = "none";
					$("#clear_holder_" + id).html("<span style='background:lime; color:black; padding: 2px'>Cleared&nbsp;&#10003;</span>");
					checkClearedChecks();
				}
			}
		});
	},
	unclearCheck: function(event) {
		var url = "api/check/unclear"
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		var formValues = "id=" + id;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					element.style.display = "none";
					$("#clear_holder_" + id).html("<span style='background:lime; color:black; padding: 2px'>Uncleared&nbsp;&#10003;</span>");
					
					checkClearedChecks();
				}
			}
		});
	},
	confirmdeleteCheck: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "check");
	},
	checkRequest: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id;
		composeCheckRequest(element_id, "-1", current_case_id)
	},
	unVivify: function(event) {
		var textbox = $("#checks_searchList");
		var label = $("#label_search_check");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#checks_searchList");
		var label = $("#label_search_check");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	printPayments: function(event) {
		this.printChecks(event, "payments");
	},
	printDisbursements: function() {
		this.printChecks(event, "receipts");
	},
	printChecks: function(event, inorout) {
		if (blnNewCheck) {
			return;
		}
		blnNewCheck = true;
		
		event.preventDefault();
		var href = "report.php#" + inorout + "/" + current_case_id;
		window.open(href);
		
		setTimeout(function() {
			blnNewCheck = false;
		}, 1200);
	},
	checkRequestGeneral: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;
		composeCheckRequest(element_id, "-1", current_case_id);
	},
	clearAllFilters: function(event) {
		event.preventDefault();
		this.clearFilters("");
		$("#clear_filters").fadeOut();
	},
	clearFilters: function(element_id) {
		var register_filters = $(".register_filter");
		for (var i = 0; i < register_filters.length; i++) {
			var register_filter = register_filters[i];
			
			if (element_id.indexOf("start") < 0 && element_id.indexOf("end") < 0) {
				if (register_filter.id != element_id) {
					register_filter.value = "";
				}
			} else {
				var other_id = element_id.replace("start", "end");
				if (element_id.indexOf("start") < 0) {
					other_id = element_id.replace("end", "start");
				}
				if (register_filter.id != element_id && register_filter.id != other_id) {
					register_filter.value = "";
				}
			}
		}
		if ($("#clear_filters").css("display") == "none") {
			$("#clear_filters").fadeIn();
		}
		
		var $rows = $('.check_listing .check_data_row');
		$rows.show();
	},
	filterPayee: function(event) {
		var element = event.currentTarget;
		this.clearFilters(element.id);
		findIt(element, 'check_listing', 'check');
	},
	filterLedger: function(event) {
		var element = event.currentTarget;
		this.clearFilters(element.id);
		findIt(element, 'check_listing', 'check');
		
		//if IN, show checkboxes and button
		if (element.value=="ledger_in") {
			$(".print_checkbox").show();
			this.showPrintButton();
		} else {
			$(".print_checkbox").hide();
			$("#print_deposits").hide();
		}
	},
	showPrintButton: function() {
		$("#print_deposits").fadeIn();
		
		//however, at min 2
		var print_boxes = $(".print_checkbox");
		var arrLength = print_boxes.length;
		var check_count = 0;
		blnDisabled = true;
		for (var i  = 0; i < arrLength; i++) {
			var print_box = print_boxes[i];
			if (print_box.checked) {
				check_count++;
			}
			if (check_count > 1) {
				blnDisabled = false;
				break;
			}
		}
		
		document.getElementById("print_deposits").disabled = blnDisabled;
	},
	printBulkDeposits: function(event) {
		$("#print_deposits").hide();
		
		//however, at min 2
		var print_boxes = $(".print_checkbox");
		var arrLength = print_boxes.length;
		var check_count = 0;
		var main_id = -1;
		var main_person_corporation = "";
		var arrPrinted = [];
		for (var i  = 0; i < arrLength; i++) {
			var print_box = print_boxes[i];
			if (print_box.checked) {
				check_count++;
				if (check_count > 0) {
					var sub_id = print_box.id.replace("print_checkbox_", "");
					
					arrPrinted.push(sub_id);
				}
			}
		}
		if (arrPrinted.length > 0) {
			var ids = arrPrinted.join("|");
			//console.log(related_json);
			//return;
			var url = 'reports/print_deposits.php?ids=' + ids;
			window.open(url);
		}
		
		$("#print_deposits").fadeIn();
	},
	filterStatus: function(event) {
		var element = event.currentTarget;
		this.clearFilters(element.id);
		findIt(element, 'check_listing', 'check');
	},
	filterCases: function(event) {
		var element = event.currentTarget;
		this.clearFilters(element.id);
		var element_id = element.id.replace("filter_case", "case_name");
		var search_element = document.getElementById(element_id);
		findIt(search_element, 'check_listing', 'check');
	},
	filterStartDate: function(event) {
		var obj = event.currentTarget;
		var namespace = 'check_listing';
		var page = 'check';
		
		var theobj = $("#" + obj.id);
		var val = $(theobj).val();
		var val3 = $("#filter_end_date").val();
		
		var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
		var $rows = $('.' + namespace + ' .' + page + '_data_row');
		
		if (val3!="" && val=="") {
			$("#filter_end_date").trigger("change");
			return;
		}
		if (val3=="" && val=="") {
			$rows.show();
			return;
		}
		var founds = 0;
		$rows.show().filter(function() {
			var text = $( '.check_date', $( this ) ).val ().replace(/\s+/g, ' ').toLowerCase();
			
			//if (text.indexOf(val) > -1) {
			var start_date = new Date(val);
			var consider_date = new Date(text);
			//consider_date.setDate(consider_date.getDate()+1);
			
			var blnFound = false;
			if (val3=="") {
				if (start_date <= consider_date) {	
					blnFound = true;			
					var row_id = this.classList[1];
					$("." + row_id).show();
					founds++;
				}
			} else {
				val3 = val3.replace(/ +/g, ' ').toLowerCase();
				var end_date = new Date(val3);
				
				if (consider_date >= start_date && consider_date <= end_date) {	
					blnFound = true;			
					var row_id = this.classList[1];
					$("." + row_id).show();
					founds++;
				}
			}
			return !blnFound;
		}).hide();
		
		this.clearFilters("filter_start_date");
	},
	filterEndDate: function(event) {
		var obj = event.currentTarget;
		var namespace = 'check_listing';
		var page = 'check';
		
		var theobj = $("#" + obj.id);
		var val = $(theobj).val();
		var val3 = $("#filter_start_date").val();
		
		var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
		var $rows = $('.' + namespace + ' .' + page + '_data_row');
		
		if (val3!="" && val=="") {
			$("#filter_start_date").trigger("change");
			return;
		}
		if (val3=="" && val=="") {
			$rows.show();
			return;
		}
		var founds = 0;
		$rows.show().filter(function() {
			var text = $( '.check_date', $( this ) ).val ().replace(/\s+/g, ' ').toLowerCase();
			
			//if (text.indexOf(val) > -1) {
			var end_date = new Date(val);
			var consider_date = new Date(text);
			
			var blnFound = false;
			if (val3=="") {
				if (end_date >= consider_date) {	
					blnFound = true;			
					var row_id = this.classList[1];
					$("." + row_id).show();
					founds++;
				}
			} else {
				val3 = val3.replace(/ +/g, ' ').toLowerCase();
				var start_date = new Date(val3);
				
				if (consider_date >= start_date && consider_date <= end_date) {	
					blnFound = true;			
					var row_id = this.classList[1];
					$("." + row_id).show();
					founds++;
				}
			}
			return !blnFound;
		}).hide();
		
		this.clearFilters("filter_start_date");
	},
	filterStartNumber: function(event) {
		var namespace = 'check_listing';
		var page = 'check';
		
		var theobj =  $("#filter_start_number");
		var val = $(theobj).val();
		var val3 = $("#filter_end_number").val();
		
		var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
		var $rows = $('.' + namespace + ' .' + page + '_data_row');
		
		if (val3!="" && val=="") {
			$("#filter_end_number").trigger("change");
			return;
		}
		if (val3=="" && val=="") {
			$rows.show();
			return;
		}
		var founds = 0;
		$rows.show().filter(function() {
			var text = $( '.check_number', $( this ) ).val ().replace(/\s+/g, ' ').toLowerCase();
			
			//if (text.indexOf(val) > -1) {
			var start_number = val;
			var consider_number = text;
			
			var blnFound = false;
			var end_number = Number(val);
			var consider_number = Number(text);

			if (isNaN(end_number) || isNaN(consider_number)) {
				blnFound = true;			
				var row_id = this.classList[1];
				$("." + row_id).show();
				blnFound = true;	
			}
			if (!blnFound) {
				if (val3=="") {
					if (start_number <= consider_number) {	
						blnFound = true;			
						var row_id = this.classList[1];
						$("." + row_id).show();
						founds++;
					}
				} else {
					val3 = val3.replace(/ +/g, ' ').toLowerCase();
					var end_number = val3;
					
					if (consider_number >= start_number && consider_number <= end_number) {	
						blnFound = true;			
						var row_id = this.classList[1];
						$("." + row_id).show();
						founds++;
					}
				}
			}
			return !blnFound;
		}).hide();
		
		this.clearFilters("filter_start_number");
	},
	filterEndNumber: function() {
		var namespace = 'check_listing';
		var page = 'check';
		
		var theobj = $("#filter_end_number");
		var val = $(theobj).val();
		var val3 = $("#filter_start_number").val();
		
		var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
		var $rows = $('.' + namespace + ' .' + page + '_data_row');
		
		if (val3!="" && val=="") {
			$("#filter_start_number").trigger("change");
			return;
		}
		if (val3=="" && val=="") {
			$rows.show();
			return;
		}
		var founds = 0;
		$rows.show().filter(function() {
			var text = $( '.check_number', $( this ) ).val ().replace(/\s+/g, ' ').toLowerCase();
			
			//if (text.indexOf(val) > -1) {
			var end_number = Number(val);
			var consider_number = Number(text);
			var blnFound = false;
			if (isNaN(end_number) || isNaN(consider_number)) {
				blnFound = true;			
				var row_id = this.classList[1];
				$("." + row_id).show();
				blnFound = true;	
			}
			if (!blnFound) {
				if (val3=="") {
					if (end_number >= consider_number) {	
						blnFound = true;			
						var row_id = this.classList[1];
						$("." + row_id).show();
						founds++;
					}
				} else {
					val3 = val3.replace(/ +/g, ' ').toLowerCase();
					var start_number = val3;
					
					if (consider_number >= start_number && consider_number <= end_number) {	
						blnFound = true;			
						var row_id = this.classList[1];
						$("." + row_id).show();
						founds++;
					}
				}
			}
			return !blnFound;
		}).hide();
		
		this.clearFilters("filter_end_number");
	},
	payBack: function(event) {
		var element_id = event.currentTarget.id;
		this.newCheck("IN", element_id);
	},
	newPayment: function() {
		this.newCheck("IN");
	},
	newDisbursement: function() {
		this.newCheck("OUT");
	},
	newCheck:function (ledger, payback_id) {
		if (blnNewCheck) {
			return;
		}
		blnNewCheck = true;
		var element_id = "new_check_-1";
		if (typeof payback_id != "undefined") {
			element_id = payback_id;
		}
		composeCheck(element_id, ledger);
		
		var self = this;
		setTimeout(function() {
			blnNewCheck = false;
		}, 1200);
    },
	editCheck:function (event) {
		if (blnNewCheck) {
			return;
		}
		blnNewCheck = true;
		
		var element = event.currentTarget;
		var element_id = element.id;
		//context
		var check_context = "payments";
		if (document.location.hash.indexOf("#settlement")==0) {
			var holder_id = element.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement.id;
			var arrID = holder_id.split("_");
			check_context = arrID[2];
		}
		//console.log(element.id);
		var kinvoiceinput_id = element_id.replace("compose_check_", "kinvoiceid_");
		var checkcase_id = element_id.replace("compose_check_", "caseid_");
		var case_id = $("#" + checkcase_id).val();
		var jdata = {"case_id": case_id};
		if ($("#" + kinvoiceinput_id).length > 0) {
			if ($("#" + kinvoiceinput_id).html()!="") {
				var kinvoice_id = $("#" + kinvoiceinput_id).val();
				var kinvoice_number_id = element_id.replace("compose_check_", "kinvoice_number_");
				var kinvoice_number = $("#" + kinvoice_number_id).val();
				
				var company_id = element_id.replace("compose_check_", "corporation_");
				var corporation = $("#" + company_id).val();
				
				var corp_id = element_id.replace("compose_check_", "corporationid_");
				var corporation_id = $("#" + corp_id).val();
				var blnAllInvoices = (document.location.hash.indexOf("accounts/") > -1);
				jdata = {
					"case_id": case_id,
					"kinvoice_id": kinvoice_id,
					"invoice_number": kinvoice_number,
					"corporation": corporation, 
					"corporation_id": corporation_id,
					"blnAllInvoices": blnAllInvoices
				};
				check_context = "invoice";
			}
		}
		//try promise here
        composeCheck(element.id, "", check_context, jdata);
		
		//then statement
		setTimeout(function() {
			blnNewCheck = false;
		}, 1200);
    },
	filterByCategory:function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var category = element.innerHTML;
		$("#checks_searchList").val(category);
		
		$( "#checks_searchList" ).trigger( "keyup" );
	},
	clearSearch: function() {
		$("#checks_searchList").val("");
		$( "#checks_searchList" ).trigger( "keyup" );
		$("#checks_searchList").focus();
	},
});
window.payments_print_listing = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #check_print_listing_all_done":		"doTimeouts"
	},
    render:function () {		
		var self = this;
		var checks = this.collection.toJSON();
		var totals_due = 0;
		var totals_payment = 0;
		_.each( checks, function(check) {
			if (isDate(check.check_date)) {
				check.check_date = moment(check.check_date).format("MM/DD/YY")
			} else {
				check.check_date = "";
			}
			
			if (check.balance=="") {
				check.balance = "0.00";
			}
			if (!isNaN(check.amount_due)) {
				totals_due += Number(check.amount_due);
            }
			if (!isNaN(check.payment)) {
				totals_payment += Number(check.payment);
            }
			
			//edit
			if (check.check_number == "") {
				check.check_number = 'Edit Payment';	
			}
			//check.check_number = '<a title="Click to edit payment" class="compose_check white_text" id="compose_check_' + check.id + '" data-toggle="modal" data-target="#myModal4" style="cursor:pointer">' + check.check_number  + '</a>';
		});
		$(this.el).html(this.template({checks: checks, title: this.model.get("title"), case_id: this.model.get("case_id"), totals_due: totals_due, totals_payment: totals_payment}));
		
		return this;
    },
	filterByCategory:function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var category = element.innerHTML;
		$("#checks_searchList").val(category);
		
		$( "#checks_searchList" ).trigger( "keyup" );
	},
	clearSearch: function() {
		$("#checks_searchList").val("");
		$( "#checks_searchList" ).trigger( "keyup" );
	},
	doTimeouts: function() {
		var kase = new Kase({id: this.model.get("case_id")});
		kase.fetch({
			success: function(data) {
				$("#case_name").html(data.get("case_name"));
			}
		});
	}
});
window.cost_category_listing = Backbone.View.extend({
	initialize:function () {
    },
	events: {
		/*"click #select_all_filters":			"selectAll",*/
		"click #new_cost_category_button":		"newCostCategory",
		"click #save_cost_category":			"saveCostCategory",
		"click .cost_category_checkbox":		"activateCostCategory",
		"click .cost_category_cell":			"editCostCategory",
		"click .cost_category_save":			"updateCostCategory",
		"click #show_all_category":				"showAllCategory"
	},
    render:function () {	
		if (typeof this.template != "function") {
			this.model.set("holder", "manage_categories_holder");
			var view = "cost_category_listing";
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
		
		_.each(arrCats , function(cost_category) {
			if (typeof cost_category.id != "undefined") {
				var index = cost_category.id
				var checked = " checked";
				var row_display = "";
				var cell_display = "color:white";
				var row_class = "active_filter";
				if (cost_category.deleted=="Y") {
					checked = "";
					row_display = "display:none";
					cell_display = "color:red; text-decoration:line-through;";
					row_class = "deleted_filter";
				}
				var input = "<input class='hidden' type='text' id='cost_category_value_" + index + "' value='" + cost_category.cost_type + "' />&nbsp;<button class='btn btn-xs btn-success cost_category_save hidden' id='cost_category_save_" + index + "'>Save</button>";
				var therow = "<tr class='" + row_class + "' style='" + row_display + "'><td class='cost_category'><input type='checkbox' class='cost_category_checkbox hidden' value='Y' title='Uncheck to stop using this category.  Old records currently using this category will not be affected' id='cost_category_" + index + "' name='cost_category_" + index + "'" + checked + "></td><td class='cost_category' style='" + cell_display + "'><span id='cost_category_span_" + index + "' class='cost_category_cell' style='cursor:pointer' title='Click to edit this category'>" + cost_category.cost_type + "</span>" + input + "</td></tr>";
				arrRows.push(therow);
				
				//are we using a deleted category
				var current_category = self.model.get("case_category");
				var blnUsingDeleted = (arrDeletedCostCategory.indexOf(current_category) > -1);
				
				if (cost_category.deleted!="Y" || blnUsingDeleted) {
					//the drop down has to match
					var option_selected = "";
					if (cost_category.category == current_category) {
						option_selected = " selected";
					}
					var option = '<option value="' + cost_category.cost_type + '" class="wcab_category_option"' + option_selected + '>' + cost_category.cost_type + '</option>';
					arrOptions.push(option);
				}
			}
		});
		var html = "<table id='cost_category_table'>" + arrRows.join("") + "</table>";
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
	editCostCategory:function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var casecategory_id = arrID[arrID.length - 1];
		
		//not open nor closed
		var current = $("#cost_category_value_" + casecategory_id).val();
		if (current=="Open" || current=="Closed") {
			return;
		}
		$("#cost_category_span_" + casecategory_id).fadeOut();
		$("#cost_category_value_" + casecategory_id).toggleClass("hidden");
		$("#cost_category_save_" + casecategory_id).toggleClass("hidden");
		$("#cost_category_" + casecategory_id).toggleClass("hidden");
	},
	showAllCategory: function() {
		$("#show_all_category").hide();
		$(".deleted_filter").show();
	},
	newCostCategory:function(event) {
		event.preventDefault();
		$("#new_cost_category_button").fadeOut();
		$("#new_cost_category_holder").fadeIn(function() {
			$("#new_cost_category").focus();
		});
	},
	saveCostCategory:function(event) {
		event.preventDefault();
		var url = 'api/cost_type/add';
		var mymodel = this.model.toJSON();
		
		var cost_type = $("#new_cost_category").val();
		var formValues = "cost_type=" + cost_type;
		
		var title = $("#cost_category_title").html();
		$("#cost_category_title").html("Saving...");
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#cost_category_title").html("<span style='color:lime'>Saved&nbsp;&#10003;</span>");
					
					$("#manage_category").trigger("click");
					
					//repop the select
					var cells = $(".cost_category_cell");
					var arrLength = cells.length;
					var arrOptions = [];
					var option = '<option value="">Select from List</option>';
					arrOptions.push(option);
					for(var i =0; i < arrLength; i++) {
						var id = cells[i].id.split("_")[3];
						if (cells[i].style.display!="none" || (!$("#cost_category_value_" + id).hasClass("hidden") && document.getElementById("cost_category_" + id).checked)) {
							var option = '<option value="' + cells[i].innerText.toLowerCase() + '">' + cells[i].innerText + '</option>';
							arrOptions.push(option);
						}
					}
					$("#check_typeInput").html(arrOptions.join(""));
					
					setTimeout(function() {
						$("#cost_category_title").html(title);
					}, 2500);
				}
			}
		});
		//
	},
	activateCostCategory: function(event) {
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var casecategory_id = arrID[arrID.length - 1];
		
		$("#cost_category_save_" + casecategory_id).trigger("click");
	},
	updateCostCategory:function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var casecategory_id = arrID[arrID.length - 1];
		var checkbox = document.getElementById("cost_category_" + casecategory_id);
		var deleted = "Y";
		if (checkbox.checked) {
			deleted = "N";
		}
		var casecategory = $("#cost_category_value_" + casecategory_id).val();
		var mymodel = this.model.toJSON();
		
		var url = 'api/cost_type/update';
		var formValues = "cost_type_id=" + casecategory_id + "&cost_type=" + casecategory + "&deleted=" + deleted;
		
		var title = $("#cost_category_title").html();
		$("#cost_category_title").html("Saving...");
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#cost_category_title").html("<span style='color:lime'>Saved&nbsp;&#10003;</span>");
					
					$("#manage_category").trigger("click");
					
					//repop the select
					var cells = $(".cost_category_cell");
					var arrLength = cells.length;
					var arrOptions = [];
					var option = '<option value="">Select from List</option>';
					arrOptions.push(option);
					for(var i =0; i < arrLength; i++) {
						var id = cells[i].id.split("_")[3];
						if (cells[i].style.display!="none" || (!$("#cost_category_value_" + id).hasClass("hidden") && document.getElementById("cost_category_" + id).checked)) {
							var option = '<option value="' + cells[i].innerText.toLowerCase() + '">' + cells[i].innerText + '</option>';
							arrOptions.push(option);
						}
					}
					$("#check_typeInput").html(arrOptions.join(""));
					
					setTimeout(function() {
						$("#cost_category_title").html(title);
					}, 2500);
				}
			}
		});
		//
	}
});